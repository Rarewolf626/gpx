<?php

namespace GPX\Model;

use WP_User;
use stdClass;
use Illuminate\Support\Str;

/**
 * @property-read int $id
 * @property string $Mobile
 * @property string $DayPhone
 * @property string $FirstName1
 * @property string $LastName1
 * @property ?int $DAEMemberNo
 * @property ?string $GP_Preferred
 */
class UserMeta {
    private int $id;
    private stdClass $data;
    private ?WP_User $user = null;

    public function __construct(int $id = null, \stdClass $usermeta = null) {
        $this->id = $id;
        $this->data = $usermeta ?? new \stdClass();
    }

    public static function load(int $cid = null): UserMeta {
        $data = $cid ? gpx_get_usermeta($cid) : null;

        return new static($cid, $data);
    }

    public function getUser(): ?WP_User {
        if (!$this->user) {
            $this->user = get_userdata($this->id) ?: null;
        }

        return $this->user;
    }

    public function getPhone(): string {
        return $this->getDayPhone();
    }

    public function getDayPhone(): string {
        return $this->getValue($this->data->DayPhone ?? '');
    }

    public function getMobile(): string {
        return $this->getValue($this->data->Mobile1 ?? $this->data->Mobile ?? '');
    }

    public function getFirstName(): string {
        if (!isset($this->data->FirstName1, $this->data->first_name, $this->data->SPI_First_Name__c)) {
            $first_name = $this->getUser()?->first_name;
            if ($first_name) $this->data->first_name = $first_name;
        }

        return $this->getValue($this->data->FirstName1 ?? $this->data->first_name ?? $this->data->SPI_First_Name__c ?? '');
    }

    public function getLastName(): string {
        if (!isset($this->data->LastName1, $this->data->last_name, $this->data->SPI_Last_Name__c)) {
            $last_name = $this->getUser()?->last_name;
            if($last_name) $this->data->last_name = $last_name;
        }

        return $this->getValue($this->data->LastName1 ?? $this->data->last_name ?? $this->data->SPI_Last_Name__c ?? '');
    }

    public function getSecondaryName(): string {
        $first = $this->getValue($this->data->FirstName2 ?? '');
        $last = $this->getValue($this->data->LastName2 ?? '');
        return trim($first . ' ' . $last);
    }

    public function getAddress(): string {
        return $this->getValue($this->data->Address1 ?? $this->data->address ?? '');
    }

    public function getCity(): string {
        return $this->getValue($this->data->Address3 ?? $this->data->city ?? '');
    }

    public function getState(): string {
        return $this->getValue($this->data->Address4 ?? $this->data->state ?? '');
    }

    public function getPostalCode(): string {
        return $this->getValue($this->data->Address5 ?? $this->data->PostCode ?? $this->data->zip ?? '');
    }

    public function getCountry(): string {
        $country = $this->data->country ?? '';
        if (in_array($country, ['US', 'USA', 'UNITED STATES OF AMERICA', 'United States'])) return 'US';

        return $country;
    }

    public function getName(): string {
        return $this->getFirstName() . ' ' . $this->getLastName();
    }

    public function getEmailAddress(): string {
        if (!isset($this->data->owner_email)) {
            $meta = $this->data->Email ?? $this->data->email ?? $this->data->user_email ?? $this->data->Email1;
            if ($meta) {
                $this->data->owner_email = $meta;

                return $meta;
            }

            global $wpdb;
            $sql = $wpdb->prepare("SELECT `SPI_Email__c` FROM `wp_GPR_Owner_ID__c` WHERE `user_id` = %d", $this->id);
            $data = $wpdb->get_var($sql);
            if ($data) {
                $this->data->owner_email = $data;

                return $data;
            }

            if (isset($this->data->SPI_Email__c)) {
                $this->data->owner_email = $this->data->SPI_Email__c;

                return $this->data->SPI_Email__c;
            }

            $user = get_userdata($this->id);
            $this->data->owner_email = $user->user_email ?? '';
        }

        return $this->data->owner_email;
    }

    private function getValue(string $value = null): string {
        $value = $value ?? '';

        return str_contains($value, 'stdClass') ? '' : $value;
    }

    public function __get($name) {
        if ($name === 'id') {
            return $this->id;
        }
        $method = 'get' . Str::studly($name);
        if (method_exists($this, $method)) {
            return $this->$method();
        }

        return $this->getValue($this->data->$name ?? '');
    }

    public function __set($name, $value) {
        if ($name === 'id') {
            throw new \InvalidArgumentException('Cannot set user id');
        }
        $method = 'set' . Str::studly($name);
        if (method_exists($this, $method)) {
            $this->$method($value);

            return;
        }
        $this->data->$name = $value;
    }

    public function __isset($name): bool {
        if ($name === 'id') {
            return true;
        }

        return isset($this->data->$name);
    }

    public function __unset($name) {
        if ($name === 'id') {
            throw new \InvalidArgumentException('Cannot unset user id');
        }
        unset($this->data->$name);
    }

    public function __call($method, $arguments) {
        return call_user_func_array([$this->data, $method], $arguments);
    }
}
