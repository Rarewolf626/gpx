@extends('admin::layout', ['title' => 'Edit Region', 'active' => 'regions'])
@php
    /**
     * @var GPX\Model\Region $region
     * @var ?string $message
     * @var Illuminate\Support\MessageBag $errors
     * @var Illuminate\Support\Collection $countries
     */
@endphp

@section('actions')
    <?php if (null === $region->RegionID): ?>
    <div class="col-md-5 col-sm-5 col-xs-12 form-group text-right top_search">
        <div id="gpxadmin-region-delete" data-props="{{ json_encode([
    'region' => [
        'id' => $region->id,
        'name' => $region->name,
        'featured' => $region->featured,
        'hidden' => $region->ddHidden,
    ],
]) }}"></div>
    </div>
    <?php endif; ?>
@endsection

@section('content')

    <?php if (null !== $region->RegionID): ?>

    <div class="alert alert-info">This is a base Region. It cannot be edited, you can only make it a featured
        region.
    </div>

    <div id="gpxadmin-region-featured" data-props="{{ json_encode([
    'region' => [
        'id' => $region->id,
        'name' => $region->name,
        'featured' => $region->featured,
        'hidden' => $region->ddHidden,
    ],
]) }}"></div>
    </div>

    <?php else: ?>


    <div id="gpxadmin-region-edit" data-props="{{ json_encode([
        'id' => $region->id,
        'region' => [
            'id' => $region->id,
            'parent' => $region->parent,
            'CountryID' => $region->ancestors->first(fn($r) => $r->parent == 1)?->CountryID,
            'featured' => $region->featured,
            'name' => $region->name,
            'displayName' => $region->displayName,
            'ddHidden' => $region->ddHidden,
            'show_resort_fees' => $region->show_resort_fees,
            'ancestors' => $region->ancestors->map(fn($ancestor) => [
                'id' => $ancestor->id,
                'name' => $ancestor->name,
                'CountryID' => $ancestor->CountryID,
                'parent' => $ancestor->parent,
                'lft' => $ancestor->lft,
            ]),
        ],
        'countries' => $countries->map(fn($country) => [
            'id' => $country->CountryID,
            'name' => $country->country,
        ]),
        'regions' => $regions->map(fn($r) => [
            'id' => $r->id,
            'parent' => $r->parent,
            'CountryID' => $r->CountryID,
            'name' => $r->name,
            'displayName' => $r->displayName,
        ]),
    ]) }}"></div>

    <?php endif; ?>

@endsection
