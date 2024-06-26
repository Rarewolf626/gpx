@extends('admin::layout', ['title' => 'Resorts', 'active' => 'resorts'])
@php
    /**
     * @var \GPX\Model\ValueObject\Admin\Transaction\TransactionSearch $search
     */
@endphp

@section('content')

    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">All Resorts</h3>
        </div>
        <div class="panel-body">
            <div
                id="gpxadmin-resorts-table"
                data-props="{{ json_encode([
                    'initalSearch' => $search->toArray(),
                ]) }}"
            ></div>
        </div>
    </div>

@endsection
