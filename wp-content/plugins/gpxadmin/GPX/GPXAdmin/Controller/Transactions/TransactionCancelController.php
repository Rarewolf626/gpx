<?php

namespace GPX\GPXAdmin\Controller\Transactions;

use GPX\Model\Partner;
use GPX\Model\UserMeta;
use GPX\Model\Transaction;
use Illuminate\Support\Arr;
use GPX\Repository\TransactionRepository;
use GPX\DataObject\Transaction\RefundResult;
use GPX\Form\Admin\Transaction\CancelTransactionForm;
use function Symfony\Component\String\s;

class TransactionCancelController {
    public function index(): void {
        $id = gpx_request('transaction');
        if (!$id) {
            wp_send_json(['success' => false, 'message' => 'No transaction ID provided.']);
        }
        $transaction = Transaction::find($id);
        if (!$transaction) {
            wp_send_json(['success' => false, 'message' => 'Transaction not found.']);
        }
        if (!$transaction->cancelled) {
            wp_send_json(['success' => false, 'message' => 'This transaction has not been cancelled.']);
        }
        $details = $transaction->cancelledData ?? [];
        ksort($details, SORT_NUMERIC);
        $details = Arr::last($details);

        wp_send_json([
            'success' => true,
            'details' => [
                'date' => ($details['date'] ?? null) ? date('m/d/Y', strtotime($details['date'])) : null,
                'name' => $details['name'] ?? null,
                'action' => $details['action'] ?? null,
                'amount' => gpx_currency($details['amount'] ?? null, false, true),
            ],
        ]);
    }

    public function details(): void {
        $id = gpx_request('transaction');
        if (!$id) {
            wp_send_json(['success' => false, 'message' => 'No transaction ID provided.']);
        }
        $transaction = Transaction::find($id);
        if (!$transaction) {
            wp_send_json(['success' => false, 'message' => 'Transaction not found.']);
        }
        if ($transaction->cancelled) {
            wp_send_json(['success' => false, 'message' => 'Transaction is already cancelled.']);
        }
        $partner = Partner::where('user_id', $transaction->userID)->first();

        $paid = (float) ($transaction->data['Paid'] ?? 0.00);
        $cancellations = collect(array_values($transaction->cancelledData ?? []));
        // the already refunded amounts
        $refunded = $cancellations->sum('amount');

        wp_send_json([
            'success' => true,
            'is_partner' => $partner !== null,
            'paid' => $paid,
            'refunded' => $refunded,
        ]);
    }

    public function cancel(): void {
        $id = gpx_request('transaction');
        if (!$id) {
            wp_send_json(['success' => false, 'message' => 'No transaction ID provided.']);
        }
        $transaction = Transaction::with(['user', 'partner'])->find($id);
        if (!$transaction) {
            wp_send_json(['success' => false, 'message' => 'Transaction not found.']);
        }

        /** @var CancelTransactionForm $form */
        $form = gpx(CancelTransactionForm::class);
        $refund = $form->getRefundRequest(gpx_request('refund', []));

        if ($transaction->cancelled) {
            $refund->cancel = false;
        }

        if (!$refund->cancel && $refund->total() <= 0) {
            wp_send_json(['success' => false, 'message' => 'Cannot refund $0.00 without cancelling.']);
        }

        $agent = $agent ?? UserMeta::load(get_current_user_id());
        $repository = TransactionRepository::instance();
        $refunded = $repository->refundTransaction($transaction, $refund, $agent);
        if ($refunded->success && $refund->cancel) {
            $transaction->refresh();
            $repository->cancelTransaction($transaction,'admin');
        }

        wp_send_json([
            'success' => $refunded->success,
            'refunded' => $refunded,
            'credit' => $refunded->credit(),
            'refund' => $refunded->card(),
            'message' => $refunded->message,
            'coupon' => $refunded->coupon?->id,
        ]);
    }
}
