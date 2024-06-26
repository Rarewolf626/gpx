<?php

namespace GPX\Model\Checkout\Item;

use GPX\Model\Credit;
use GPX\Model\Interval;
use GPX\Model\UnitType;
use GPX\Model\Checkout\Exchange;
use GPX\Model\DepositOnExchange;
use GPX\Repository\CreditRepository;
use GPX\Repository\IntervalRepository;

class ExchangeWeek extends BaseItem implements CartItem {
    protected ?Credit $credit = null;
    protected ?Interval $interval = null;
    protected ?Credit $exchange_credit = null;
    protected ?DepositOnExchange $exchange_deposit = null;

    public function getType(): ?string {
        return 'ExchangeWeek';
    }

    public function isExchange(): bool {
        return true;
    }

    public function setExchangeInfo(array|Exchange $exchange): static {
        $this->exchange = $exchange instanceof Exchange ? $exchange : new Exchange($exchange);
        if ($this->exchange->isDeposit()) {
            $this->credit = null;
            $this->interval = $this->exchange->deposit ? IntervalRepository::instance()->get_member_interval($this->cid, $this->exchange->deposit) : $this->exchange->deposit;
            $interval = IntervalRepository::instance()->getIntervalFromSalesforce($this->interval?->contractID);
            if ($interval && $this->interval) {
                $this->interval->fill([
                    'Room_Type__c' => $interval->Room_Type__c,
                    'Delinquent__c' => $interval->Delinquent__c,
                    'Week_Type__c' => $interval->Week_Type__c,
                    'Contract_ID__c' => $interval->Contract_ID__c,
                    'RIOD_Key_Full' => $interval->ROID_Key_Full__c,
                    'Property_Owner__c' => $interval->Property_Owner__c,
                    'Account_Name__c' => $interval->Property_Owner__c,
                ]);
            }
        }
        if ($this->exchange->isCredit()) {
            $this->interval = null;
            $this->credit = $this->exchange->credit ? CreditRepository::instance()->getOwnerCredit($this->cid, $this->exchange->credit, $this->week->check_in_date) : null;
        }
        $this->calculateTotals();

        return $this;
    }

    public function calculateUpgradeFee(string $unit_type = null): float {
        $unit_type = $unit_type ?? $this->exchange->unit_type ?? $this->credit?->unit_type ?? $this->interval?->Room_Type__c ?? null;
        if (!$this->week || !$unit_type) {
            return 0.00;
        }

        $this->week->loadMissing(['unit', 'theresort']);
        $beds = UnitType::getNumberOfBedrooms($this->week->unit->number_of_bedrooms);

        $resort = $this->week->theresort->ResortName;
        if (in_array($resort, [
            'Channel Island Shores',
            'Hilton Grand Vacations Club at MarBrisa',
            'RiverPointe Napa Valley',
        ])) {
            $fees = match ($beds) {
                'st', 'studio' => ['studio' => 0.00, '1' => 0.00, '2' => 0.00, '3' => 0.00,],
                '1' => ['studio' => 85.00, '1' => 0.00, '2' => 0.00, '3' => 0.00,],
                '2' => ['studio' => 185.00, '1' => 185.00, '2' => 0.00, '3' => 0.00,],
                default => ['studio' => 0.00, '1' => 0.00, '2' => 0.00, '3' => 0.00,],
            };

            return $fees[$unit_type] ?? 0.00;
        }

        return (float) Credit::calculateUpgradeFee($this->week->unit->number_of_bedrooms, $unit_type, $this->week->resort);
    }

    public function calculateLateFee(string|\DateTimeInterface $checkin = null): float {
        if (!$this->exchange->fee) return 0.00;
        $checkin = $checkin ?? $this->exchange->date ?? null;

        return gpx_calculate_late_fee($checkin);
    }

    public function getThirdPartyDepositFee(): float {
        if (!$this->interval?->third_party_deposit_fee_enabled) {
            return 0.00;
        }
        if ($this->exchange->waive_tp_fee) {
            return 0.00;
        }

        return $this->tp_deposit_fee;
    }

    public function getCheckinDate(): ?string {
        return $this->exchange->date ?? null;
    }

    public function setCredit(int $credit_id = null): static {
        $this->credit = CreditRepository::instance()->getOwnerCredit($this->cid, $credit_id, $this->week->checkIn);

        return $this;
    }

    public function isInterval(): bool {
        return $this->exchange->isDeposit();
    }

    public function hasInterval(): bool {
        return $this->interval instanceof Interval;
    }

    public function getInterval(): ?Interval {
        return $this->interval;
    }

    public function isCredit(): bool {
        return $this->exchange->isCredit();
    }

    public function hasCredit(): bool {
        return $this->credit instanceof Credit;
    }

    public function getCredit(): ?Credit {
        return $this->credit;
    }

    public function getUnitType(): ?string {
        return $this->exchange->unit_type ?: $this->interval?->Room_Type__c;
    }

    public function getPrice(): float {
        if (!$this->exchange_same_resort_fee || !$this->interval) return $this->price;
        if ($this->interval->resort_id === $this->week->resort) return min($this->exchange_same_resort_fee, $this->price);

        return $this->price;
    }

    public function getExtensionFee(): float {
        if ($this->exchange->isCredit()) {
            if ($this->credit->isExpired($this->week->check_in_date)) {
                // The credit has expired or expires before the check-in date
                return $this->extension_fee;
            }
        }

        return 0.00;
    }

    public function canAddFlex(): bool {
        return $this->week->check_in_date->clone()->endOfDay()->subDays(45)->isFuture();
    }

    public function setFlex(bool $flex = true): static {
        if (!$this->canAddFlex()) {
            $flex = false;
        }
        $this->flex = $flex;
        $this->calculateTotals();

        return $this;
    }

    public function setDepositOnExchange(int|Credit $credit = null, DepositOnExchange $deposit = null): static {
        $this->exchange_credit = $credit instanceof Credit && $credit->isDepositOnExchange() ? $credit : Credit::doe()->find($credit);
        if ($this->exchange_credit) {
            if ($deposit && $deposit->creditID === $this->exchange_credit->id) {
                $this->exchange_deposit = $deposit;
            } elseif ($credit->relationLoaded('deposit') && $credit->deposit) {
                $this->exchange_deposit = $credit->deposit;
            } else {
                $this->exchange_deposit = DepositOnExchange::where('creditID', '=', $this->exchange_credit->id)->first();
            }

        } else {
            $this->exchange_deposit = null;
        }

        return $this;
    }

    public function getExchangeCredit(): ?Credit {
        return $this->exchange_credit;
    }

    public function getExchangeDeposit(): ?DepositOnExchange {
        return $this->exchange_deposit;
    }
}
