<?php

namespace GPX\Rule;

use GPX\Model\Interval;
use GPX\Repository\IntervalRepository;
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Contracts\Validation\DataAwareRule;


class ReservationRequiredForNonGprResortsRule implements DataAwareRule, InvokableRule {

    protected array $data = [];
    protected int $cid;

    public function __construct(int $cid = null) {
        $this->cid = $cid ?? gpx_get_switch_user_cookie();
    }

    public function __invoke($attribute, $value, $fail): void {
        if (empty($this->data['inventory'] ?? null)) {
            return;
        }
        global $wpdb;
        $sql = $wpdb->prepare("
            SELECT wp_owner_interval.id, wp_resorts.gpr
            FROM wp_owner_interval
            INNER JOIN wp_resorts wp_resorts ON wp_resorts.gprID LIKE CONCAT(BINARY wp_owner_interval.resortID, '%%')
            WHERE wp_owner_interval.id = %d AND wp_owner_interval.userID = %d
        ", [(int) $this->data['inventory'], $this->cid]);
        $ownership = (bool) $wpdb->get_row($sql);
        if ($ownership && !$ownership->gpr && empty($value)) {
            $fail('Reservation number is required');
        }
    }

    public function setData($data): static {
        $this->data = $data;

        return $this;
    }
}
