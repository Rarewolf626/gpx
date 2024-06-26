<?php

namespace GPX\Command\Partner\Week;

use WP_User;
use GPX\Model\Week;
use GPX\Model\Partner;
use GPX\Model\PreHold;
use GPX\Model\TaxRate;
use GPX\Model\Transaction;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Collection;

class PartnerBookWeeks {
    private Partner $partner;
    /**
     * @var Collection<Week>
     */
    private Collection $weeks;
    private WP_User $user;
    /**
     * @var 'ExchangeWeek'|'RentalWeek'
     */
    private string $type;

    public function __construct(int|Partner $partner, array|int $weeks, string $type = 'RentalWeek', int|WP_User $user = null) {
        $this->partner = $partner instanceof Partner ? $partner : Partner::where('user_id', $partner)->firstOrFail();
        $this->weeks = Week::with(['theresort'])->whereIn('record_id', Arr::wrap($weeks))->get();
        $this->user = $user instanceof WP_User ? $user : get_userdata($user ?? get_current_user_id());
        $this->type = $type;
    }

    public function handle() {
        $this->weeks->each(fn($week) => $this->book_week($week));
    }

    private function book_week(Week $week) {
        if (!$this->is_week_available($week)) {
            $week->update(['active' => false]);

            return;
        }

        $hold = PreHold::forUser($this->partner->user_id)->forWeek($week->record_id)->released(false)->first();
        if ($hold) {
            $holdDets = $hold->data ?? [];

            $holdDets[time()] = [
                'action' => 'released',
                'by' => $this->user->first_name . " " . $this->user->last_name,
            ];
            $hold->update([
                'data' => json_encode($holdDets),
                'released' => true,
            ]);
        }
        $values = array_merge([
            'adults' => 1,
            'children' => 0,
            'user_type' => 'Agent',
            'user' => $this->partner->user_id,
            'FirstName1' => $this->partner->name,
            'Email' => $this->partner->email,
            'HomePhone' => $this->partner->phone,
            'propertyID' => $week->record_id,
            'weekId' => $week->record_id,
            'cartID' => $this->partner->user_id . "_" . $week->record_id,
            'weekType' => $this->type,
        ], $this->get_fees($week));

        $cart  = gpx_create_cart($this->partner->user_id);
        $item = $cart->createItem($this->type, $week->record_id);
        $cart->setItem($item);
        $cart->setAgent(true);

        if ($this->isExchange()) {
            $cart->item->setFlex(false);
        }
        $cart->item->setGuestInfo([
            'has_guest' => false,
            'owner' => $this->partner->user_id,
            'adults' => 1,
            'children' => 0,
            'email' => $this->partner->email,
            'fee' => false,
            'first_name' => $this->partner->first_name,
            'last_name' => $this->partner->last_name,
            'phone' => $this->partner->phone,
            'special_request' => null,
        ]);

        $transaction = gpx_save_transaction($cart);

        if ($this->isExchange()) {
            $this->partner->update([
                'trade_balance' => \DB::raw('trade_balance - 1'),
                'no_of_rooms_received_taken' => \DB::raw('no_of_rooms_received_taken + 1'),
            ]);
        }
    }

    private function is_week_available(Week $week): bool {

        return Transaction::where('weekId', $week->record_id)
                          ->cancelled(false)
                          ->doesntExist();
    }

    private function get_fees(Week $week): array {
        if ($this->isExchange()) {
            return [
                'price' => gpx_get_exchange_fee($this->partner->user_id, $week),
                'paid' => 0,
                'balance' => 0,
                'taxes' => [],
            ];
        }

        $price = $week->price;
        $taxes = [];
        $taxAmount = 0;
        //add the tax
        if (get_option('gpx_tax_transaction_bonus', false)) {
            $tax = TaxRate::find($week->theresort->taxID);

            $taxes = [
                'taxID' => $tax->ID,
                'type' => 'add',
                'taxPercent' => $tax->total_percent,
                'flatTax' => $tax->total_flat,
                'taxAmount' => $tax->amount($price),
            ];

        }

        $paid = $price + $taxAmount;
        $balance = $paid;

        return [
            'price' => $price,
            'paid' => $paid,
            'balance' => $balance,
            'taxes' => $taxes,
        ];
    }

    private function isExchange(): bool {
        return $this->type === 'ExchangeWeek';
    }
}
