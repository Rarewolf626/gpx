<?php

namespace GPX\DataObject\Transaction;

class RefundRequest implements \JsonSerializable {
    public bool $cancel = true;
    public float $amount = 0.00;
    public bool $booking = false;
    public float $booking_amount = 0.00;
    public bool $cpo = false;
    public float $cpo_amount = 0.00;
    public bool $upgrade = false;
    public float $upgrade_amount = 0.00;
    public bool $guest = false;
    public float $guest_amount = 0.00;
    public bool $late = false;
    public float $late_amount = 0.00;
    public bool $third_party = false;
    public float $third_party_amount = 0.00;
    public bool $extension = false;
    public float $extension_amount = 0.00;
    public bool $tax = false;
    public float $tax_amount = 0.00;

    public function __construct(array $data = []) {
        $this->cancel = (bool) ($data['cancel'] ?? true);
        $this->amount = (float) ($data['amount'] ?? 0.00);
        $this->booking = (bool) ($data['booking'] ?? false);
        $this->booking_amount = (float) ($data['booking_amount'] ?? 0.00);
        $this->cpo = (bool) ($data['cpo'] ?? false);
        $this->cpo_amount = (float) ($data['cpo_amount'] ?? 0.00);
        $this->upgrade = (bool) ($data['upgrade'] ?? false);
        $this->upgrade_amount = (float) ($data['upgrade_amount'] ?? 0.00);
        $this->guest = (bool) ($data['guest'] ?? false);
        $this->guest_amount = (float) ($data['guest_amount'] ?? 0.00);
        $this->late = (bool) ($data['late'] ?? false);
        $this->late_amount = (float) ($data['late_amount'] ?? 0.00);
        $this->third_party = (bool) ($data['third_party'] ?? false);
        $this->third_party_amount = (float) ($data['third_party_amount'] ?? 0.00);
        $this->extension = (bool) ($data['extension'] ?? false);
        $this->extension_amount = (float) ($data['extension_amount'] ?? 0.00);
        $this->tax = (bool) ($data['tax'] ?? false);
        $this->tax_amount = (float) ($data['tax_amount'] ?? 0.00);
    }

    public function total(): float {
        return round(($this->booking ? $this->booking_amount : 0.00)
                     + ($this->cpo ? $this->cpo_amount : 0.00)
                     + ($this->upgrade ? $this->upgrade_amount : 0.00)
                     + ($this->guest ? $this->guest_amount : 0.00)
                     + ($this->late ? $this->late_amount : 0.00)
                     + ($this->third_party ? $this->third_party_amount : 0.00)
                     + ($this->extension ? $this->extension_amount : 0.00)
                     + ($this->tax ? $this->tax_amount : 0.00), 2);
    }

    public function toArray(): array {
        return [
            'cancel' => $this->cancel,
            'amount' => $this->amount,
            'booking' => $this->booking,
            'booking_amount' => $this->booking_amount,
            'cpo' => $this->cpo,
            'cpo_amount' => $this->cpo_amount,
            'upgrade' => $this->upgrade,
            'upgrade_amount' => $this->upgrade_amount,
            'guest' => $this->guest,
            'guest_amount' => $this->guest_amount,
            'late' => $this->late,
            'late_amount' => $this->late_amount,
            'third_party' => $this->third_party,
            'third_party_amount' => $this->third_party_amount,
            'extension' => $this->extension,
            'extension_amount' => $this->extension_amount,
            'tax' => $this->tax,
            'tax_amount' => $this->tax_amount,
        ];
    }

    public function jsonSerialize(): array {
        return $this->toArray();
    }
}
