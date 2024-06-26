<?php

namespace GPX\GPXAdmin\Controller\Transactions;

use DB;
use SObject;
use Shiftfour;
use Exception;
use GPX\Model\UserMeta;
use GPX\Model\Transaction;
use Illuminate\Support\Carbon;
use GPX\Model\OwnerCreditCoupon;
use GPX\Api\Salesforce\Salesforce;
use GPX\Model\OwnerCreditCouponActivity;
use GPX\Repository\TransactionRepository;
use GPX\DataObject\Transaction\RefundResult;
use GPX\DataObject\Transaction\RefundRequest;

class TransactionFeeController {
    public function __invoke(): void {
        $id = gpx_request('transaction');
        if (!$id) {
            wp_send_json(['success' => false, 'message' => 'No transaction ID provided.']);
        }
        $transaction = Transaction::with('user')->find($id);
        if (!$transaction) {
            wp_send_json(['success' => false, 'message' => 'Transaction not found.']);
        }
        /** @var string $type */
        $type = gpx_request('type');
        if (!in_array($type, ['booking', 'cpo', 'upgrade', 'guest', 'late', 'third_party', 'extension', 'tax'])) {
            wp_send_json(['success' => false, 'message' => 'Invalid fee type.']);
        }
        $amount = round((float) gpx_request('amount', 0.00), 2);
        if ($amount <= 0) {
            wp_send_json(['success' => false, 'message' => 'Cannot refund $0.00.']);
        }

        $refunds = collect($transaction->cancelledData)->map(fn($details, $time) => [
            'time' => $time,
            'date' => date('m/d/Y', $time),
            'type' => $details['type'] ?? null,
            'action' => $details['action'] ?? null,
            'amount' => round($details['amount'] ?? 0.00, 2),
            'coupon' => $details['coupon'] ?? null,
            'agent' => $details['agent_name'] ?? null,
        ])->sortBy('time')->values();
        $paid = round($transaction->data['Paid'] ?? 0.00, 2);
        $credited = round($transaction->data['ownerCreditCouponAmount'] ?? 0.00, 2);
        $total = round($paid + $credited, 2);
        if ($total <= 0) {
            wp_send_json(['success' => false, 'message' => 'Cannot refund a transaction with a total amount of $0.00.']);
        }
        $balance = round($total - $refunds->sum('amount'), 2);
        if ($balance <= 0) {
            wp_send_json(['success' => false, 'message' => 'Transaction has already been fully refunded.']);
        }
        if ($amount > $balance) {
            wp_send_json([
                'success' => false,
                'message' => sprintf('Cannot refund more than %s.', gpx_currency($balance)),
            ]);
        }

        $fee = round((float) match ($type) {
            'booking' => $transaction->data['actWeekPrice'] ?? 0.00,
            'cpo' => $transaction->data['actcpoFee'] ?? 0.00,
            'upgrade' => $transaction->data['actupgradeFee'] ?? 0.00,
            'guest' => $transaction->data['actguestFee'] ?? $transaction->data['GuestFeeAmount'] ?? 0.00,
            'late' => $transaction->data['lateDepositFee'] ?? 0.00,
            'third_party' => $transaction->data['thirdPartyDepositFee'] ?? 0.00,
            'extension' => $transaction->data['actextensionFee'] ?? 0.00,
            'tax' => $transaction->data['acttax'] ?? 0.00,
        }, 2);
        if ($amount > $fee) {
            wp_send_json([
                'success' => false,
                'message' => sprintf('Cannot refund more than the fee amount of %s.', gpx_currency($fee)),
            ]);

        }

        $key = match ($type) {
            'booking' => 'erFee',
            'cpo' => 'cpofee',
            'upgrade' => 'upgradefee',
            'guest' => 'guestfeeamount',
            'late' => 'latedepositfee',
            'third_party' => 'thirdpartydepositfee',
            'extension' => 'creditextensionfee',
            'tax' => 'tax',
            default => throw new Exception('Unexpected match value'),
        };

        $refunded = round($refunds->where('type', '==', $key)->sum('amount'), 2);

        if ($amount > $fee - $refunded) {
            wp_send_json([
                'success' => false,
                'message' => sprintf('Cannot refund more than %s.', gpx_currency($fee - $refunded)),
            ]);

        }
        $action = gpx_request('action', 'credit');
        if(!in_array($action, ['credit','refund'])){
            wp_send_json(['success' => false, 'message' => 'Invalid action.']);
        }

        if ($action === 'refund') {
            if (!gpx_is_administrator(false)) {
                wp_send_json([
                    'success' => false,
                    'message' => 'You must be an administrator to refund a transaction to a credit card.',
                ], 403);
            }
            $max = round($paid - $refunds->where('action', '==', 'refund')->sum('amount'), 2);
            if ($amount > $max) {
                wp_send_json([
                    'success' => false,
                    'message' => sprintf('Cannot refund more than %s to credit card.', gpx_currency($max)),
                ]);
            }
        }

        $request = new RefundRequest([
            'cancel' => false,
            'amount' => $action === 'refund' ? $amount : 0.00,
            $type => true,
            $type . '_amount' => $amount,
        ]);
        $agent = UserMeta::load(get_current_user_id());
        $repository = TransactionRepository::instance();
        $result = $repository->refundTransaction($transaction, $request, $agent);

        wp_send_json([
            'success' => $result->success,
            'message' => $result->message,
            'type' => $type,
            'action' => $action,
            'fee' => $fee,
            'amount' => $amount,
            'balance' => round($fee - $refunded - $amount, 2),
            'refunded' => round($refunded + $amount, 2),
        ]);
    }
}
