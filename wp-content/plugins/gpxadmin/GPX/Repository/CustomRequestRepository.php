<?php

namespace GPX\Repository;

use GPX\Model\CustomRequest;
use Illuminate\Database\Eloquent\Model;

class CustomRequestRepository extends Model {

    public static function get_custom_requests( int $emsid, int $userid ) {
        return CustomRequest::active()
                            ->owner()
                            ->byUser( $emsid, $userid )
                            ->get();
    }

    public function count_custom_requests( int $emsid, int $userid ) {
        return CustomRequest::active()
                            ->owner()
                            ->byUser( $emsid, $userid )
                            ->count();
    }

}
