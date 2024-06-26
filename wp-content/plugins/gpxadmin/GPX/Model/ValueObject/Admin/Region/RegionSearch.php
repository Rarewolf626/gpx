<?php

namespace GPX\Model\ValueObject\Admin\Region;

use JsonSerializable;
use Illuminate\Support\Carbon;

/**
 * @property-read int $page
 * @property-read int $paged
 * @property-read int $pg
 * @property-read int $limit
 * @property-read string $sort
 * @property-read string $dir
 * @property-read string $gpx
 * @property-read string $region
 * @property-read string $display
 * @property-read string $parent
 */
class RegionSearch implements JsonSerializable {

    public static array $sortable = [
        'id', 'lft', 'gpx', 'region', 'display', 'parent',
    ];

    private int $page = 1;
    private int $limit = 20;
    private string $sort = 'lft';
    private string $dir = 'desc';
    private string $id = '';
    private string $gpx = '';
    private string $region = '';
    private string $display = '';
    private string $parent = '';

    public function __construct(array $data) {
        $this->page = (int) max(1, $data['pg'] ?? $data['paged'] ?? $data['page'] ?? 1);
        $this->limit = in_array($data['limit'] ?? 20, [10, 20, 50, 100]) ? (int) $data['limit'] : 20;
        $this->sort = in_array(mb_strtolower(trim($data['sort'] ?? 'lft')), static::$sortable) ? mb_strtolower(trim($data['sort'])) : 'lft';
        $this->dir = in_array(mb_strtolower(trim($data['dir'] ?? 'desc')), [
            'asc',
            'desc',
        ]) ? mb_strtolower(trim($data['dir'])) : 'desc';
        $this->gpx = in_array(mb_strtolower(trim($data['gpx'] ?? null)), [
            'yes',
            'no',
        ]) ? mb_strtolower(trim($data['gpx'])) : '';
        $this->id = trim($data['id'] ?? '');
        $this->region = trim($data['region'] ?? '');
        $this->display = trim($data['display'] ?? '');
        $this->parent = trim($data['parent'] ?? '');
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
            'gpx' => $this->gpx,
            'region' => $this->region,
            'display' => $this->display,
            'parent' => $this->parent,
        ];
    }

    public function jsonSerialize(): array {
        return $this->toArray();
    }
}
