<?php

namespace GPX\Model\ValueObject\Admin\Transaction;

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
 * @property-read string $type
 * @property-read string $user
 * @property-read string $owner
 * @property-read ?int $owner_id
 * @property-read ?int $parent_id
 * @property-read ?int $adults
 * @property-read ?int $children
 * @property-read ?float $upgrade
 * @property-read string $cpo
 * @property-read ?float $cpo_fee
 * @property-read ?string $resort
 * @property-read ?string $room
 * @property-read ?string $week
 * @property-read ?string $deposit
 * @property-read ?string $week_type
 * @property-read ?float $balance
 * @property-read string $resort_id
 * @property-read ?int $sleeps
 * @property-read string $bedrooms
 * @property-read ?int $nights
 * @property-read ?Carbon $checkin
 * @property-read ?float $paid
 * @property-read string $processed
 * @property-read string $promo
 * @property-read ?float $discount
 * @property-read ?float $coupon
 * @property-read string $occoupon
 * @property-read ?float $ocdiscount
 * @property-read ?Carbon $date
 * @property-read ?bool $cancelled
 */
class TransactionSearch implements JsonSerializable {

    public static array $sortable = [
        'id', 'type', 'user', 'member', 'owner', 'guest', 'adults', 'children', 'upgrade', 'cpo', 'cpo_fee',
        'resort', 'room', 'week_type', 'balance', 'resort_id', 'week', 'deposit', 'sleeps', 'bedrooms', 'nights',
        'checkin', 'paid', 'processed', 'promo', 'discount', 'coupon', 'occoupon', 'ocdiscount', 'date', 'cancelled',
    ];

    private int $page = 1;
    private int $limit = 20;
    private string $sort = 'id';
    private string $dir = 'desc';
    private string $id = '';
    private string $type = '';
    private string $user = '';
    private string $owner = '';
    private ?int $owner_id = null;
    private ?int $parent_id = null;
    private ?int $adults = null;
    private ?int $children = null;
    private ?float $upgrade = null;
    private string $cpo = '';
    private ?float $cpo_fee = null;
    private string $resort = '';
    private string $room = '';
    private string $week = '';
    private string $deposit = '';
    private ?string $week_type = null;
    private ?float $balance = null;
    private string $resort_id = '';
    private ?int $sleeps = null;
    private string $bedrooms = '';
    private ?int $nights = null;
    private ?Carbon $checkin = null;
    private ?float $paid = null;
    private string $processed = '';
    private string $promo = '';
    private ?float $discount = null;
    private ?float $coupon = null;
    private string $occoupon = '';
    private ?float $ocdiscount = null;
    private ?Carbon $date = null;
    private ?string $cancelled = null;

    public function __construct(array $data) {
        $this->page = (int) max(1, $data['pg'] ?? $data['paged'] ?? $data['page'] ?? 1);
        $this->limit = in_array($data['limit'] ?? 20, [10, 20, 50, 100]) ? (int) $data['limit'] : 20;
        $this->sort = in_array(mb_strtolower(trim($data['sort'] ?? 'id')), static::$sortable) ? mb_strtolower(trim($data['sort'])) : 'id';
        $this->dir = in_array(mb_strtolower(trim($data['dir'] ?? 'desc')), [
            'asc',
            'desc',
        ]) ? mb_strtolower(trim($data['dir'])) : 'desc';
        $this->id = trim($data['id'] ?? '');
        $this->type = in_array(mb_strtolower(trim($data['type'] ?? null)), [
            'booking',
            'credit_donation',
            'credit_transfer',
            'deposit',
            'extension',
            'pay_debit',
            'guest',
        ]) ? mb_strtolower(trim($data['type'])) : '';
        $this->user = trim($data['user'] ?? '');
        $this->owner = trim($data['owner'] ?? '');
        $this->owner_id = ($data['owner_id'] ?? null) !== null ? (int) $data['owner_id'] : null;
        $this->parent_id = ($data['parent_id'] ?? null) !== null ? (int) $data['parent_id'] : null;
        $this->adults = ($data['adults'] ?? null) !== null ? (int) $data['adults'] : null;
        $this->children = ($data['children'] ?? null) !== null ? (int) $data['children'] : null;
        $this->upgrade = ($data['upgrade'] ?? null) !== null ? (float) $data['upgrade'] : null;
        $this->cpo = in_array(mb_strtolower(trim($data['cpo'] ?? null)), ['taken', 'nottaken','na']) ? mb_strtolower(trim($data['cpo'])) : '';
        $this->cpo_fee = ($data['cpo_fee'] ?? null) !== null ? (float) $data['cpo_fee'] : null;
        $this->resort = trim($data['resort'] ?? '');
        $this->deposit = trim($data['deposit'] ?? '');
        $this->room = trim($data['room'] ?? '');
        $this->week = trim($data['week'] ?? '');
        $this->week_type = in_array(mb_strtolower(trim($data['week_type'] ?? null)), [
            'rental',
            'exchange',
        ]) ? mb_strtolower(trim($data['week_type'])) : null;
        $this->balance = ($data['balance'] ?? null) !== null ? (float) $data['balance'] : null;
        $this->resort_id = trim($data['resort_id'] ?? '');
        $this->sleeps = ($data['sleeps'] ?? null) !== null ? (int) $data['sleeps'] : null;
        $this->bedrooms = trim($data['bedrooms'] ?? '');
        $this->nights = ($data['nights'] ?? null) !== null ? (int) $data['nights'] : null;
        $this->checkin = trim($data['checkin'] ?? null) ? Carbon::createFromFormat('Y-m-d', $data['checkin'])->startOfDay() : null;
        $this->paid = ($data['paid'] ?? null) !== null ? (float) $data['paid'] : null;
        $this->processed = trim($data['processed'] ?? '');
        $this->promo = trim($data['promo'] ?? '');
        $this->discount =($data['discount'] ?? null) !== null ? (float) $data['discount'] : null;
        $this->coupon =($data['coupon'] ?? null) !== null ? (float) $data['coupon'] : null;
        $this->occoupon = trim($data['occoupon'] ?? '');
        $this->ocdiscount =($data['ocdiscount'] ?? null) !== null ? (float) $data['ocdiscount'] : null;
        $this->date = trim($data['date'] ?? null) ? Carbon::createFromFormat('Y-m-d', $data['date'])->startOfDay() : null;
        $this->cancelled = in_array(mb_strtolower(trim($data['cancelled'] ?? null)), [
            'yes',
            'no',
        ]) ? mb_strtolower(trim($data['cancelled'])) : null;
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
            'user' => $this->user,
            'owner' => $this->owner,
            'owner_id' => $this->owner_id,
            'parent_id' => $this->parent_id,
            'adults' => $this->adults,
            'children' => $this->children,
            'upgrade' => $this->upgrade,
            'cpo' => $this->cpo,
            'cpo_fee' => $this->cpo_fee,
            'resort' => $this->resort,
            'room' => $this->room,
            'week_type' => $this->week_type,
            'balance' => $this->balance,
            'resort_id' => $this->resort_id,
            'week' => $this->week,
            'deposit' => $this->deposit,
            'sleeps' => $this->sleeps,
            'bedrooms' => $this->bedrooms,
            'nights' => $this->nights,
            'checkin' => $this->checkin,
            'paid' => $this->paid,
            'processed' => $this->processed,
            'promo' => $this->promo,
            'discount' => $this->discount,
            'coupon' => $this->coupon,
            'occoupon' => $this->occoupon,
            'ocdiscount' => $this->ocdiscount,
            'date' => $this->date,
            'cancelled' => $this->cancelled,
        ];
    }

    public function jsonSerialize(): array {
        return $this->toArray();
    }
}
