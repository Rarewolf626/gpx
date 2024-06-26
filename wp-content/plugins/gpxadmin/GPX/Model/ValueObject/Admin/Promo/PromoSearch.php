<?php

namespace GPX\Model\ValueObject\Admin\Promo;

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
 * @property-read ?string $active
 */
class PromoSearch implements JsonSerializable {

    public static array $sortable = [
        'id', 'type', 'name', 'slug', 'availability', 'travel_start', 'travel_end', 'active', 'coupon',
    ];

    public static string $default_sort = 'id';
    public static string $default_dir = 'asc';

    private int $page = 1;
    private int $limit = 20;
    private string $sort = 'id';
    private string $dir = 'asc';
    private string $id = '';
    private ?string $type = null;
    private string $name = '';
    private string $slug = '';
    private ?string $availability = null;
    private ?string $travel = null;
    private ?string $active = null;
    private string $coupon = '';

    public function __construct(array $data) {
        $this->page = (int) max(1, $data['pg'] ?? $data['paged'] ?? $data['page'] ?? 1);
        $this->limit = in_array($data['limit'] ?? 20, [10, 20, 50, 100]) ? (int) $data['limit'] : 20;
        $this->sort = in_array(mb_strtolower(trim($data['sort'] ?? static::$default_sort)), static::$sortable) ? mb_strtolower(trim($data['sort'])) : static::$default_sort;
        $this->dir = in_array(mb_strtolower(trim($data['dir'] ?? static::$default_dir)), [
            'asc',
            'desc',
        ]) ? mb_strtolower(trim($data['dir'])) : static::$default_dir;
        $this->id = trim($data['id'] ?? '');
        $this->slug = trim($data['slug'] ?? '');
        $this->name = trim($data['pname'] ?? '');
        $this->travel = trim($data['travel'] ?? '');
        $this->coupon = trim($data['coupon'] ?? '');
        $this->type = in_array(mb_strtolower(trim($data['type'] ?? null)), [
            'coupon',
            'promo',
        ]) ? mb_strtolower(trim($data['type'])) : null;
        $this->availability = in_array(mb_strtolower(trim($data['availability'] ?? null)), [
            'landing',
            'site',
        ]) ? mb_strtolower(trim($data['availability'])) : null;
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
            'type' => $this->type,
            'pname' => $this->name,
            'slug' => $this->slug,
            'availability' => $this->availability,
            'travel' => $this->travel,
            'active' => $this->active,
            'coupon' => $this->coupon,
        ];
    }

    public function jsonSerialize(): array {
        return $this->toArray();
    }
}
