<?php
/**
 * Promotion class
 *
 * data from this object comes from the tables - wp_specials
 *
 */
class Promotion
{

    public string $name;
    public string $slug;

/*
{
    "promoType":"Dollar Off",
    "transactionType":["BonusWeek"],
    "usage":"customer",
    "exclusions":"",
    "exclusiveWeeks":"",
    "stacking":"No",
    "bookStartDate":"04\/26\/17",
    "bookEndDate":"2017-07-31 23:59:59",
    "travelStartDate":"04\/26\/17",
    "travelEndDate":"2017-07-31 23:59:59",
    "leadTimeMin":"",
    "leadTimeMax":"",
    "terms":"",
    "minWeekPrice":"199",
    "maxValue":"",
    "useExc":"",
    "availability":"",
    "slash":"",
    "highlight":"",
    "blackout":[{"start":"2020-04-09 00:00:00","end":"2020-04-17 23:59:59"}],
    "beforeLogin":"No",
    "GACode":"","icon":"",
    "desc":"",
    "specificCustomer":"[\"82687\"]",
    "metaCustomerResortSpecific":"No"
}
*/
/*
{
    "promoType":"Set Amt",
    "transactionType":["any"],
    "usage":"customer,resort",
    "exclusions":"",
    "exclusiveWeeks":"",
    "stacking":"No",
    "bookStartDate":"07\/12\/17",
    "bookEndDate":"2019-01-31 23:59:59",
    "travelStartDate":"07\/12\/17",
    "travelEndDate":"2019-08-31 23:59:59",
    "leadTimeMin":"","leadTimeMax":"",
    "terms":"",
    "minWeekPrice":"350",
    "maxValue":"",
    "useExc":"    ",
    "availability":"",
    "beforeLogin":"Yes",
    "GACode":"",
    "icon":"fa-anchor",
    "desc":"Testing the minimum amount required to apply promo",
    "specificCustomer":"[\"82687\",\"4\"]",
    "metaCustomerResortSpecific":"No",
    "usage_resort":["3025"]
}
*/

/*
{"promoType":"Pct Off",
    "transactionType":"any",
    "usage":"resort",
    "exclusions":"",
    "stacking":"No",
    "bookStartDate":"11\/01\/17",
    "bookEndDate":"2017-12-31 23:59:59",
    "travelStartDate":"11\/01\/17",
    "travelEndDate":"2017-12-31 23:59:59",
    "leadTimeMin":"",
    "leadTimeMax":"",
    "terms":"",
    "minWeekPrice":"",
    "maxValue":"",
    "useExc":"",
    "availability":"",
    "beforeLogin":"No",
    "GACode":"",
    "icon":"",
    "desc":"November and December Travel Jewels",
    "usage_resort":["3019","26","28","2994","1334"]
}
*/


/*

## Abstract

    ~~Master Special ~~ Doesn't Work / Not Used. Remove / Ignore
    Booking Funnel:  Yes/No  // this is essentially Landing Page (yes) / Coupon (no)
    Name:  name of the coupon / landing page
    Hide Before Login:   Yes, No
    ~~Google Analytics ID~~
    Slash Through Icon:
    Promo Tagging:
    Card Highlighting: highlighting / prevent
    Slash Through
    ~~Show on Index~~
    Promo Availability: Site Wide / Landing Page
    Coupon/Promo Type:
                    * Pct Off
                    * Set Amount
                    * Dollar Off
    ~~Auto Create Coupon : ~~
    Coupon/Promo Amount:
    Week Minimum Cost:
    Max Value:
    Transaction Type:
            * Any
            * Exchange
            * Rental / Bonus


Object
    Usage:
             * Any
             * Region
             * Resort
             * Customer

Object
    Exclusions:



    Allow Stacking Discount

    Start Date (Available for viewing)
    End Date (available for viewing)

    Flash Sale Start time
    Flash Sale End time

    Book Start Date
    Book End Date

    Travel Start Date
    Travel End Date

    Blackout Start Date
    Blackout End Date

    Lead Time Min Days
    Lead Time Max Days

    Active

## Booking Funnel (Yes / Landing Page)

    Slug:

## Booking Funnel (No / Coupon)

    Coupon Code:

    Single User Per Owner
    Max Number of Coupons

    Terms & Conditions

*/






    public function __construct(){}


}
