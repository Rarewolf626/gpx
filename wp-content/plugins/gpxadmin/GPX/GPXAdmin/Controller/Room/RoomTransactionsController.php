<?php

namespace GPX\GPXAdmin\Controller\Room;

use GPX\Model\Transaction;

class RoomTransactionsController {
    public function index(int $id) {
        $transactions = Transaction::with('resort')->where('weekId', $id)->orderBy('datetime')->get();
        wp_send_json(
            $transactions->map(function(Transaction $transaction){
                $data = $transaction->data;
                return [
                    'id' => $transaction->id,
                    'view' => gpx_admin_route('transactions_view', ['id' => $transaction->id]),
                    'is_booking' => $transaction->isBooking(),
                    'type' => ucwords(str_replace('_', ' ', $transaction->transactionType)),
                    'user' => $transaction->userID,
                    'member' =>$data['MemberName'] ?? null,
                    'owner' =>$data['OwnerName'] ?? $data['Owner'] ?? null,
                    'guest' => $data['GuestName'] ?? null,
                    'adults' =>$data['Adults'] ?? null,
                    'children' =>$data['Children'] ?? null,
                    'upgrade' => gpx_currency($transaction->data['UpgradeFee'] ?? null, false, false),
                    'cpo' =>$data['CPO'] ?? null,
                    'cpo_fee' => gpx_currency($transaction->data['CPOFee'] ?? null, false, false),
                    'resort' =>$data['ResortName'] ?? $transaction->resort?->ResortName ?? null,
                    'room' =>$data['Size'] ?? null,
                    'week_type' =>$data['WeekType'] ?? null,
                    'balance' => gpx_currency($transaction->data['Balance'] ?? null, false, false),
                    'resort_id' => $transaction->resortID,
                    'week' => $transaction->weekId,
                    'sleeps' =>$data['sleeps'] ?? null,
                    'bedrooms' =>$data['bedrooms'] ?? null,
                    'nights' =>$data['noNights'] ?? null,
                    'checkin' => $transaction->check_in_date?->format('m/d/Y'),
                    'paid' => gpx_currency($transaction->data['Paid'] ?? null, false, false),
                    'processed' =>$data['processedBy'] ?? null,
                    'promo' =>$data['promoName'] ?? null,
                    'discount' => gpx_currency($transaction->data['discount'] ?? null, false, false),
                    'coupon' => gpx_currency(str_replace('$', '',$data['couponDiscount'] ?? null), false, false),
                    'occoupon' =>$data['ownerCreditCouponID'] ?? null,
                    'ocdiscount' => gpx_currency($transaction->data['ownerCreditCouponAmount'] ?? null, false, false),
                    'date' => $transaction->datetime?->format('m/d/Y') ?? null,
                    'cancelled' => $transaction->cancelled,
                ];
            })->toArray()
        );
    }
}
