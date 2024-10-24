<?php

namespace GPX\GPXAdmin\Controller\Transactions;

use DB;
use SObject;
use Shiftfour;
use Money\Money;
use GPX\Model\Credit;
use GPX\Model\UserMeta;
use GPX\Model\Transaction;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use GPX\Model\OwnerCreditCoupon;
use GPX\Api\Salesforce\Salesforce;
use Illuminate\Contracts\View\View;
use GPX\Model\OwnerCreditCouponActivity;
use GPX\Repository\TransactionRepository;
use GPX\DataObject\Transaction\RefundResult;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use GPX\Form\Admin\Transaction\SearchTransactionsForm;

class TransactionsController {
    public function index(SearchTransactionsForm $form): View {
        $search = $form->search();

        return gpx_render_blade('admin::transactions.index', compact('search'), false);
    }

    public function search(SearchTransactionsForm $form): void {
        $search = $form->search();
        $transactions = Transaction::with('resort')->adminSearch($search)->paginate($search->limit, ['*'], 'pg', $search->page)->setPageName('pg');

        wp_send_json([
            'pagination' => [
                'page' => $transactions->currentPage(),
                'total' => $transactions->total(),
                'first' => $transactions->firstItem(),
                'last' => $transactions->lastItem(),
                'limit' => $transactions->perPage(),
                'pages' => $transactions->lastPage(),
                'prev' => $transactions->previousPageUrl(),
                'next' => $transactions->nextPageUrl(),
                'elements' => gpx_pagination_window($transactions),
            ],
            'transactions' => $transactions->map(function (Transaction $transaction) {
                $data = $transaction->data;

                return [
                    'id' => $transaction->id,
                    'view' => gpx_admin_route('transactions_view', ['id' => $transaction->id]),
                    'is_booking' => $transaction->isBooking(),
                    'type' => ucwords(str_replace('_', ' ', $transaction->transactionType)),
                    'user' => $transaction->userID,
                    'member' => $data['MemberName'] ?? null,
                    'owner' => $data['OwnerName'] ?? $data['Owner'] ?? null,
                    'owner_id' => $transaction->userID,
                    'guest' => $data['GuestName'] ?? null,
                    'adults' => $data['Adults'] ?? null,
                    'children' => $data['Children'] ?? null,
                    'upgrade' => gpx_currency($data['UpgradeFee'] ?? null, false, false),
                    'cpo' => $data['CPO'] ?? null,
                    'cpo_fee' => gpx_currency($data['CPOFee'] ?? null, false, false),
                    'resort' => $data['ResortName'] ?? $transaction->resort?->ResortName ?? null,
                    'room' => $data['Size'] ?? null,
                    'week_type' => $data['WeekType'] ?? null,
                    'balance' => gpx_currency($data['Balance'] ?? null, false, false),
                    'resort_id' => $transaction->resortID,
                    'week' => $transaction->weekId,
                    'deposit' => $transaction->depositID,
                    'sleeps' => $data['sleeps'] ?? null,
                    'bedrooms' => $data['bedrooms'] ?? null,
                    'nights' => $data['noNights'] ?? null,
                    'checkin' => $transaction->check_in_date?->format('m/d/Y'),
                    'paid' => gpx_currency($data['Paid'] ?? null, false, false),
                    'processed' => $data['processedBy'] ?? null,
                    'promo' => $data['promoName'] ?? null,
                    'discount' => gpx_currency($data['discount'] ?? null, false, false),
                    'coupon' => gpx_currency(str_replace('$', '', $data['couponDiscount'] ?? null), false, false),
                    'occoupon' => $data['ownerCreditCouponID'] ?? null,
                    'ocdiscount' => gpx_currency($data['ownerCreditCouponAmount'] ?? null, false, false),
                    'date' => $transaction->datetime?->format('m/d/Y') ?? null,
                    'cancelled' => $transaction->cancelled,
                ];
            }),
        ]);
    }

    public function show(int $id): View {
        $transaction = Transaction::with(['user', 'partner'])->find($id);
        if (!$transaction) {
            gpx_show_404('Transaction not found.');
        }
        $details = $transaction->cancelledData ?? [];
        ksort($details, SORT_NUMERIC);
        $cancelled = Arr::last($details);

        $deposit = null;
        if ($transaction->data['creditweekID'] ?? null) {
            $deposit = Credit::with('interval')->find($transaction->data['creditweekID']);
        }

        $owner = UserMeta::load($transaction->userID);

        $refunds = collect($transaction->cancelledData)->map(fn($details, $time) => [
            'time' => $time,
            'date' => date('m/d/Y', $time),
            'type' => $details['type'] ?? null,
            'action' => $details['action'] ?? null,
            'amount' => $details['amount'] ?? 0.00,
            'coupon' => $details['coupon'] ?? null,
            'agent' => $details['agent_name'] ?? null,
        ])->sortBy('time')->values();

        return gpx_render_blade('admin::transactions.details', compact('transaction', 'cancelled', 'deposit', 'owner', 'refunds'), false);
    }

    public function details(): void {
        $id = gpx_request('transaction');
        if (!$id) {
            wp_send_json(['success' => false, 'message' => 'No transaction ID provided.']);
        }
        $transaction = Transaction::with(['user', 'partner'])->withCount('related')->find($id);
        if (!$transaction) {
            wp_send_json(['success' => false, 'message' => 'Transaction not found.']);
        }
        $details = $transaction->cancelledData ?? [];
        ksort($details, SORT_NUMERIC);
        $details = Arr::last($details, fn($refund) => $refund['type'] === 'cancelled');

    // cancellation user role
        $cancellation_detail_user_role = 'unknown';
        if ($details['userid'] != 'system' and is_numeric($details['userid']) and $details['userid'] != 0) { // there is a numeric user id

            $cancellation_detail_user = get_userdata($details['userid']);
            // make $cancellation_detail_user->roles is an array
            $cancellation_detail_user->roles = is_array($cancellation_detail_user->roles) ? $cancellation_detail_user->roles : [];
            $cancellation_detail_user_role = !empty(array_intersect(['administrator','administrator_plus','gpxadmin'],$cancellation_detail_user->roles )) ? 'admin' : 'member';
        }
    // $cancellation_detail_origin

        if (isset($details['origin'])) {
            // if it's set then use that setting
            $cancellation_detail_origin = $details['origin'];
        } elseif ($cancellation_detail_user_role == 'member') {
            // if it's not set and the user is a member then it's from the frontend
                $cancellation_detail_origin = 'frontend';
        } else {
            // otherwise it's unknown / not recorded
            $cancellation_detail_origin = 'unknown';
        }

        $deposit = null;
        if ($transaction->data['creditweekID'] ?? null) {
            $deposit = Credit::with('interval')->find($transaction->data['creditweekID']);
        }

        $owner = UserMeta::load($transaction->userID);

        $refunds = collect($transaction->cancelledData)->map(fn($details, $time) => [
            'time' => $time,
            'date' => date('m/d/Y', $time),
            'type' => $details['type'] ?? null,
            'action' => $details['action'] ?? null,
            'amount' => $details['amount'] ?? 0.00,
            'coupon' => $details['coupon'] ?? null,
            'agent' => $details['agent_name'] ?? null,
        ])->sortBy('time')->values();

        wp_send_json([
            'success' => true,
            'transaction' => [
                'id' => $transaction->id,
                'is_booking' => $transaction->isBooking(),
                'is_guest' => $transaction->transactionType === 'guest',
                'has_guest' => $transaction->isBooking() || $transaction->transactionType === 'guest',
                'is_extension' => $transaction->transactionType === 'extension',
                'related_transaction_count' => $transaction->related_count,
                'parent_transaction' => $transaction->parent_id ?? $transaction->data['transactionID'] ?? null,
                'parent_transaction_url' => ($transaction->parent_id ?? $transaction->data['transactionID'] ?? null) ? gpx_admin_route('transactions_view', ['id' => $transaction->parent_id ?? $transaction->data['transactionID']]) : null,
                'date' => $transaction->datetime?->format('m/d/Y h:i a') ?? null,
                'checkin' => $transaction->check_in_date?->format('m/d/Y') ?? null,
                'week' => $transaction->weekId ?? null,
                'resort' => $transaction->data['resortName'] ?? null,
                'size' => $transaction->data['Size'] ?? null,
                'nights' => (int) ($transaction->data['noNights'] ?? 0),
                'user_id' => $transaction->userID,
                'member' => $owner?->getName() ?? $transaction->data['MemberName'] ?? null,
                'guest' => $transaction->data['GuestName'] ?? null,
                'adults' => (int) ($transaction->data['Adults'] ?? 1),
                'children' => (int) ($transaction->data['Children'] ?? 0),
                'special_request' => $transaction->data['specialRequest'] ?? null,
                'cancelled' => $transaction->cancelled,
                'cancelled_data' => $transaction->cancelled ? [
                    'coupon' => $details['coupon'] ?? null,
                    'origin' => $cancellation_detail_origin,
                    'date' => ($details['date'] ?? null) ? date('m/d/Y', strtotime($details['date'])) : null,
                    'name' => $details['name'] ?? null,
                    'type' => $details['type'] ?? null,
                    'role' => $cancellation_detail_user_role,
                    'action' => $details['action'] ?? null,
                    'amount' => $details['amount'] ?? 0.00,
                ] : null,
                'refunds' => $refunds->isEmpty() ? null : [
                    'credits' => $refunds->where('action', '==', 'credit')->sum('amount'),
                    'refunds' => $refunds->where('action', '!=', 'credit')->sum('amount'),
                ],
                'has_flex' => $transaction->canBeRefunded(),
                'can_refund' => ($refunds->sum('amount') ?? 0) < ($transaction->data['Paid'] ?? 0.00) && $transaction->canBeRefunded() && !$transaction->partner && gpx_is_administrator(false),
                'cancelled_date' => $transaction->cancelledDate?->format('m/d/Y') ?? null,
                'cancelled_by' => $details['name'] ?? null,
                'deposit' => $deposit ? [
                    'id' => $deposit->id,
                    'resort' => $deposit->resort_name ?? null,
                    'year' => $deposit->deposit_year ?? null,
                    'unit' => $deposit?->interval?->unitweek ?? null,
                ] : null,
                'fees' => [
                    'booking' => [
                        'type' => 'booking',
                        'original' => round($transaction->data['actWeekPrice'] ?? 0.00, 2),
                        'amount' => round($refunds->where('type', '==', 'erFee')->sum('amount'), 2),
                        'balance' => round(($transaction->data['actWeekPrice'] ?? 0.00) - $refunds->where('type', '==', 'erFee')->sum('amount'), 2),
                    ],
                    'cpo' => [
                        'type' => 'cpo',
                        'original' => round($transaction->data['actcpoFee'] ?? 0.00, 2),
                        'amount' => round($refunds->where('type', '==', 'cpofee')->sum('amount'), 2),
                        'balance' => round(($transaction->data['actcpoFee'] ?? 0.00) - $refunds->where('type', '==', 'cpofee')->sum('amount'), 2),
                    ],
                    'upgrade' => [
                        'type' => 'upgrade',
                        'original' => round($transaction->data['actupgradeFee'] ?? 0.00, 2),
                        'amount' => round($refunds->where('type', '==', 'upgradefee')->sum('amount'), 2),
                        'balance' => round(($transaction->data['actupgradeFee'] ?? 0.00) - $refunds->where('type', '==', 'upgradefee')->sum('amount'), 2),
                    ],
                    'guest' => [
                        'type' => 'guest',
                        'original' => round($transaction->data['actguestFee'] ?? $transaction->data['GuestFeeAmount'] ?? 0.00, 2),
                        'amount' => round($refunds->where('type', '==', 'guestfeeamount')->sum('amount'), 2),
                        'balance' => round(($transaction->data['actguestFee'] ?? $transaction->data['GuestFeeAmount'] ?? 0.00) - $refunds->where('type', '==', 'guestfeeamount')->sum('amount'), 2),
                    ],
                    'late' => [
                        'type' => 'late',
                        'original' => round($transaction->data['lateDepositFee'] ?? 0.00, 2),
                        'amount' => round($refunds->where('type', '==', 'latedepositfee')->sum('amount'), 2),
                        'balance' => round(($transaction->data['lateDepositFee'] ?? 0.00) - $refunds->where('type', '==', 'latedepositfee')->sum('amount'), 2),
                    ],
                    'third_party' => [
                        'type' => 'third_party',
                        'original' => round($transaction->data['thirdPartyDepositFee'] ?? 0.00, 2),
                        'amount' => round($refunds->where('type', '==', 'thirdpartydepositfee')->sum('amount'), 2),
                        'balance' => round(($transaction->data['thirdPartyDepositFee'] ?? 0.00) - $refunds->where('type', '==', 'thirdpartydepositfee')->sum('amount'), 2),
                    ],
                    'extension' => [
                        'type' => 'extension',
                        'original' => round($transaction->data['actextensionFee'] ?? 0.00, 2),
                        'amount' => round($refunds->where('type', '==', 'creditextensionfee')->sum('amount'), 2),
                        'balance' => round(($transaction->data['actextensionFee'] ?? 0.00) - $refunds->where('type', '==', 'creditextensionfee')->sum('amount'), 2),
                    ],
                    'tax' => [
                        'type' => 'tax',
                        'original' => round($transaction->data['acttax'] ?? 0.00, 2),
                        'amount' => round($refunds->where('type', '==', 'tax')->sum('amount'), 2),
                        'balance' => round(($transaction->data['acttax'] ?? 0.00) - $refunds->where('type', '==', 'tax')->sum('amount'), 2),
                    ],
                    'coupon' => (float) ($transaction->data['couponDiscount'] ?? 0.00),
                    'occoupon' => (float) ($transaction->data['ownerCreditCouponAmount'] ?? 0.00),
                    'paid' => (float) ($transaction->data['Paid'] ?? 0.00),
                    'total' => round(($transaction->data['Paid'] ?? 0.00) + ($transaction->data['ownerCreditCouponAmount'] ?? 0.00), 2),
                    'refunded' => round($refunds->sum('amount'), 2),
                    'balance' => round(($transaction->data['Paid'] ?? 0.00) + ($transaction->data['ownerCreditCouponAmount'] ?? 0.00) - $refunds->sum('amount'), 2),
                    'max_refund' => min(round(($transaction->data['Paid'] ?? 0.00) - $refunds->where('action', '!=', 'credit')->sum('amount'), 2), round(($transaction->data['Paid'] ?? 0.00) + ($transaction->data['ownerCreditCouponAmount'] ?? 0.00) - $refunds->sum('amount'), 2)),
                    'refunds' => [
                        'booking' => round($refunds->where('type', '==', 'erFee')->sum('amount'), 2),
                        'cpo' => round($refunds->where('type', '==', 'cpofee')->sum('amount'), 2),
                        'upgrade' => round($refunds->where('type', '==', 'upgradefee')->sum('amount'), 2),
                        'guest' => round($refunds->where('type', '==', 'guestfeeamount')->sum('amount'), 2),
                        'late' => round($refunds->where('type', '==', 'latedepositfee')->sum('amount'), 2),
                        'extension' => round($refunds->where('type', '==', 'creditextensionfee')->sum('amount'), 2),
                        'tax' => round($refunds->where('type', '==', 'tax')->sum('amount'), 2),
                        'other' => round($refunds->where(fn($refund) => !in_array($refund['type'] ?? '', ['erFee', 'cpofee', 'upgradefee', 'guestfeeamount', 'latedepositfee', 'thirdpartydepositfee', 'creditextensionfee', 'tax']))->sum('amount'), 2),
                        'refund' => round($refunds->sum('amount'), 2),
                    ],
                ],
                'is_partner' => (bool) $transaction->partner,
                'is_admin' => gpx_is_administrator(false),
            ],
        ]);
    }
}
