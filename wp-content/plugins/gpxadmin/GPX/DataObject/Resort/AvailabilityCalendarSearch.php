<?php

namespace GPX\DataObject\Resort;

use Illuminate\Support\Carbon;

/**
 * @property-read int|null $resort
 * @property-read string $WeekType
 * @property-read string $bedrooms
 * @property-read int $year
 * @property-read int $month
 * @property-read string $date
 * @property-read Carbon $start
 * @property-read Carbon $end
 */
class AvailabilityCalendarSearch implements \JsonSerializable
{
    private ?int $resort;
    private string $week_type;
    private string $bedrooms;
    private int $year;
    private int $month;
    private Carbon $start;
    private Carbon $end;

    public function __construct(array $search = [])
    {
        $this->resort = ((int)$search['resort'] ?? 0) ?: null;
        $this->week_type = in_array($search['WeekType'] ?? 'All', ['RentalWeek', 'ExchangeWeek']) ? $search['WeekType'] : 'All';
        $this->bedrooms = $search['bedrooms'] ?? 'Any';
        $this->year = (int)($search['year'] ?? date('Y'));
        $this->month = (int)($search['month'] ?? date('m'));
        if (empty($search['start'])) {
            $this->start = Carbon::createFromDate($this->year, $this->month, 1)->startOfMonth();
        } else {
            $this->start = Carbon::createFromFormat('Y-m-d', $search['start'])->startOFDay();
        }
        if (empty($search['end'])) {
            $this->end = Carbon::createFromDate($this->year, $this->month, 1)->endOfMonth();
        } else {
            $this->end = Carbon::createFromFormat('Y-m-d', $search['end'])->endofday();
        }
    }

    public function __get(string $name)
    {
        return match ($name) {
            'resort' => $this->resort,
            'week_type', 'WeekType', 'type' => $this->week_type,
            'bedrooms' => $this->bedrooms,
            'year' => $this->year,
            'month' => $this->month,
            'date' => $this->year . '-' . $this->month,
            'start' => $this->start,
            'end' => $this->end,
        };
    }

    public function hasResort(): bool
    {
        return !!$this->resort;
    }

    public function isRental(): bool
    {
        return $this->week_type === 'RentalWeek';
    }

    public function toArray(): array
    {
        return [
            'resort' => $this->resort,
            'WeekType' => $this->week_type,
            'bedrooms' => $this->bedrooms,
            'year' => $this->year,
            'month' => $this->month,
            'date' => $this->year . '-' . str_pad($this->month, 2, '0', STR_PAD_LEFT) . '-01',
            'start' => $this->start->format('Y-m-d'),
            'end' => $this->end->format('Y-m-d'),
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function hasWeekType(): bool
    {
        return $this->week_type !== 'All';
    }
}
