<?php

namespace GPX\Repository;

use Illuminate\Database\Eloquent\Model;

class CustomRequestRepository extends Model
{

    protected $table = 'wp_gpxCustomRequest';
    protected $primaryKey = 'id';
    protected $guarded = [];

    protected $casts = [
        'id' => 'integer',
        'userID' => 'integer',
        'datetime' => 'datetime',
        'checkIn' => 'date',
        'checkIn2' => 'date',
    ];
    const CREATED_AT = 'datetime';

    public static function get_custom_requests(int $emsid, int $userid) {
/*
        SELECT * FROM wp_gpxCustomRequest
                    WHERE active=1 AND (emsID=%s OR userID=%d)
                    AND who='Owner'", [$usermeta->DAEMemberNo, $cid]);
*/

        return \DB::select('SELECT * from wp_gpxCustomRequest WHERE actuve=1 AND who='Owner' AND (emsID=? OR userID=?)' ,[$emsid, $userid]);

    }

}
