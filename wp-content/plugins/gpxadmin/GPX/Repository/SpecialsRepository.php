<?php

namespace GPX\Repository;

use GPX\Model\Special;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class SpecialsRepository {
    public static function instance(): SpecialsRepository {
        return gpx( SpecialsRepository::class );
    }

    /**
     * @param bool|null $active If null all promos will be returned
     *
     * @return Collection
     */
    public function get_gpx_promos( ?bool $active = null ): Collection {
        return Special::query()
                      ->when( $active !== null, fn($query) => $query->active($active) )
                      ->get()
                      ->map( function ( Special $promo ) {
                          return [
                              'edit'            => '<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=promos_edit&id=' . $promo->id . '"><i class="fa fa-pencil" aria-hidden="true"></i></a>',
                              'Type'            => ucfirst( $promo->Type ),
                              'id'              => $promo->id,
                              'Name'            => stripslashes( $promo->Name ),
                              'Slug'            => '<a href="' . get_permalink( '229' ) . $promo->Slug . '" target="_blank">' . $promo->Slug . '</a>',
                              'TransactionType' => ucfirst($promo->transactionType),
                              'Availability'    => ucfirst( $promo->Properties['availability'] ?? '' ),
                              'TravelStartDate' => $promo->TravelStartDate?->format( 'm/d/Y' ),
                              'TravelEndDate'   => $promo->TravelEndDate?->format( 'm/d/Y' ),
                              'Redeemed'        => $promo->Type == 'coupon' ? $promo->redeemed : 'NA',
                              'Active'          => $promo->Active ? 'Yes' : 'No',
                          ];
                      } );
    }
}
