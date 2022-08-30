<?php

namespace GPX\Model;
class Address
{
    protected string $address1;
    protected string $address2;
    protected string $town;
    protected string $region;
    protected string $country;
    protected string $postCode;
    protected string $phone;
    protected string $fax;
    private array $required = array('address1', 'town', 'region', 'country');
    private string $error = "";

    /**
     * @throws Exception when required address fields are missing
     */
    public function __construct(array $address)
    {

        if ($this->validate($address)) {
            $this->address1 = $address['address1'];
            $this->address2 = $address['address2'] ?? '';
            $this->town = $address['town'];
            $this->region = $address['region'] ?? '';
            $this->country = $address['country'];
            $this->postCode = $address['postCode'] ?? '';
            $this->phone = $address['phone'] ?? '';
            $this->fax = $address['fax'] ?? '';
        } else {
            throw new Exception("Required address field missing:" . $this->error);
        }

    }

    private function validate(array $address): bool
    {
        foreach ($this->required as $param) {
            if (!isset($address[$param])) {
                $this->error = $param;
                return false;
            }
        }
        return true;
    }

    /*
     * create as static method
     */
    /**
     * @throws Exception
     */
    public static function create(array ...$parameters)
    {
        return new static(...$parameters);
    }

    /*
     * getter methods
     */
    public function get_address1()
    {
        return $this->address1;
    }

    public function get_address2()
    {
        return $this->address2;
    }

    public function get_town()
    {
        return $this->town;
    }

    public function get_region()
    {
        return $this->region;
    }

    public function get_country()
    {
        return $this->country;
    }

    public function get_postCode()
    {
        return $this->postCode;
    }

    public function get_phone()
    {
        return $this->phone;
    }

    public function get_fax()
    {
        return $this->fax;
    }
}
