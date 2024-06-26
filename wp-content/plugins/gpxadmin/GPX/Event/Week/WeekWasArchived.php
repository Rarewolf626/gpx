<?php

namespace GPX\Event\Week;

use GPX\Model\Week;

class WeekWasArchived {
    public function __construct(public Week $week) {}
}
