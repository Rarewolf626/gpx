<?php

namespace GPXTest\LateFees;

use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

class LateFeeCalculationTest extends TestCase {

    public function setUp(): void {
        Carbon::setTestNow(Carbon::parse('2023-03-15 04:32:54'));
    }

    public function test_that_no_late_fee_is_charged_if_checkin_is_exactly_X_days_away() {
        $settings = gpx_get_late_fee_settings();
        $checkin = Carbon::now()->addDays($settings['days']);
        $fee = gpx_calculate_late_fee($checkin);
        $this->assertEquals(0, $fee, sprintf("No late fee should be charged if checkin is %d days away", $settings['days']));
    }

    public function test_that_no_late_fee_is_charged_if_checkin_is_more_than_X_days_away() {
        $settings = gpx_get_late_fee_settings();
        $checkin = Carbon::now()->addDays($settings['days'] + 1);
        $fee = gpx_calculate_late_fee($checkin);
        $this->assertEquals(0, $fee, sprintf("No late fee should be charged if checkin is %d days away", $settings['days'] + 1));
    }

    public function test_that_late_fee_is_charged_if_checkin_is_13_days_away() {
        $settings = gpx_get_late_fee_settings();
        $checkin = Carbon::now()->addDays($settings['days'] - 1);
        $expected = (float) get_option('gpx_late_deposit_fee');
        $fee = gpx_calculate_late_fee($checkin);
        $this->assertEquals($expected, $fee, sprintf('Late fee of %s should be charged if checkin is %d days away', gpx_currency($expected), $settings['days'] - 1));
    }

    public function test_that_larger_late_fee_is_charged_if_checkin_is_less_than_X_days_away() {
        $settings = gpx_get_late_fee_settings();
        $checkin = Carbon::now()->addDays($settings['extra_days'] - 1);
        $expected = (float) get_option('gpx_late_deposit_fee_within');
        $fee = gpx_calculate_late_fee($checkin);
        $this->assertEquals($expected, $fee, sprintf('Late fee of %s should be charged if checkin is %d days away', gpx_currency($expected), $settings['extra_days'] + 1));
    }

    public function test_that_smaller_late_fee_is_charged_if_checkin_is_exactly_X_days_away() {
        $settings = gpx_get_late_fee_settings();
        $checkin = Carbon::now()->addDays($settings['extra_days']);
        $expected = (float) get_option('gpx_late_deposit_fee');
        $fee = gpx_calculate_late_fee($checkin);
        $this->assertEquals($expected, $fee, sprintf('Late fee of %s should be charged if checkin is %d days away', gpx_currency($expected), $settings['extra_days'] - 1));
    }
}
