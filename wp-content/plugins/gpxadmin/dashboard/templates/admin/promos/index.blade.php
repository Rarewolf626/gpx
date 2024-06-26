@extends('admin::layout', ['title' => 'All Specials', 'active' => 'promos'])
@php
    /**
     * @var \GPX\Model\ValueObject\Admin\Transaction\TransactionSearch $search
     */
@endphp

@section('content')

    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Promos/Coupons</h3>
        </div>
        <div class="panel-body">
            <div
                id="gpxadmin-specials-table"
                data-props="{{ json_encode([
                    'initalSearch' => $search->toArray(),
                ]) }}"
            ></div>
        </div>
    </div>

@endsection
