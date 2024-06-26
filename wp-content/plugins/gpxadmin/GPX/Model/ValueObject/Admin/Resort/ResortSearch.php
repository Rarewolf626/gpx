<?php

namespace GPX\Model\ValueObject\Admin\Resort;

use JsonSerializable;
use Illuminate\Support\Carbon;

/**
 * @property-read int $page
 * @property-read int $paged
 * @property-read int $pg
 * @property-read int $limit
 * @property-read string $sort
 * @property-read string $dir
 * @property-read string $id
 * @property-read string $resort
 * @property-read string $city
 * @property-read string $region
 * @property-read string $country
 * @property-read ?bool $ai
 * @property-read string $trip_advisor
 * @property-read ?bool $active
 */
class ResortSearch implements JsonSerializable {

    public static array $sortable = [
        'id', 'resort', 'city', 'region', 'country', 'trip_advisor', 'ai', 'active',
    ];

    public static string $default_sort = 'resort';
    public static string $default_dir = 'asc';

    private int $page = 1;
    private int $limit = 20;
    private string $sort = 'resort';
    private string $dir = 'asc';
    private string $id = '';
    private string $resort = '';
    private string $city = '';
    private string $region = '';
    private string $country = '';
    private string $trip_advisor = '';
    private ?string $ai = null;
    private ?string $active = null;

    public function __construct(array $data) {
        $this->page = (int) max(1, $data['pg'] ?? $data['paged'] ?? $data['page'] ?? 1);
        $this->limit = in_array($data['limit'] ?? 20, [10, 20, 50, 100]) ? (int) $data['limit'] : 20;
        $this->sort = in_array(mb_strtolower(trim($data['sort'] ?? static::$default_sort)), static::$sortable) ? mb_strtolower(trim($data['sort'])) : static::$default_sort;
        $this->dir = in_array(mb_strtolower(trim($data['dir'] ?? static::$default_dir)), [
            'asc',
            'desc',
        ]) ? mb_strtolower(trim($data['dir'])) : static::$default_dir;
        $this->id = trim($data['id'] ?? '');
        $this->resort = trim($data['resort'] ?? '');
        $this->city = trim($data['city'] ?? '');
        $this->region = trim($data['region'] ?? '');
        $this->country = trim($data['country'] ?? '');
        $this->trip_advisor = trim($data['trip_advisor'] ?? '');
        $this->ai = in_array(mb_strtolower(trim($data['ai'] ?? null)), [
            'yes',
            'no',
        ]) ? mb_strtolower(trim($data['ai'])) : null;
        $this->active = in_array(mb_strtolower(trim($data['active'] ?? null)), [
            'yes',
            'no',
        ]) ? mb_strtolower(trim($data['active'])) : null;
    }

    public function __get(string $name) {
        if (in_array($name, ['pg', 'page', 'paged'])) {
            return $this->page;
        }
        if (!property_exists($this, $name)) {
            throw new \InvalidArgumentException("Property $name does not exist");
        }

        return $this->$name;
    }

    public function toArray(): array {
        return [
            'pg' => $this->page,
            'limit' => $this->limit,
            'sort' => $this->sort,
            'dir' => $this->dir,
            'id' => $this->id,
            'resort' => $this->resort,
            'city' => $this->city,
            'region' => $this->region,
            'country' => $this->country,
            'ai' => $this->ai,
            'active' => $this->active,
            'trip_advisor' => $this->trip_advisor,
        ];
    }

    public function jsonSerialize(): array {
        return $this->toArray();
    }
}
