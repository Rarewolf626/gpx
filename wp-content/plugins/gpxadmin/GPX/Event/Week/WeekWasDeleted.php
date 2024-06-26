<?php

namespace GPX\Event\Week;

use GPX\Model\Week;

class WeekWasDeleted {
    public function __construct(public Week $week) {}
}
