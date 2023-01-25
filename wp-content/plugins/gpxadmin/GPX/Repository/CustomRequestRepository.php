<?php

namespace GPX\Repository;

use GPX\Model\CustomRequest;

class CustomRequestRepository {

    public static function instance(): CustomRequestRepository {
        return gpx( CustomRequestRepository::class );
    }

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

    public function count_open_requests( int $emsid, int $cid ): int {
        return CustomRequest::active()
                            ->enabled()
                            ->open()
                            ->owner()
                            ->byUser( $emsid, $cid )
                            ->count();
    }

}
