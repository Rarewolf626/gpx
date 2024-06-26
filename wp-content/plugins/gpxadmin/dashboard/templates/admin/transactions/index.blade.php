@extends('admin::layout', ['title' => 'All Transactions', 'active' => 'transactions'])
@php
    /**
     * @var \GPX\Model\ValueObject\Admin\Transaction\TransactionSearch $search
     */
@endphp

@section('content')

    <div class="panel panel-default">
        <div class="panel-body">
            <div
                id="gpxadmin-transactions-table"
                data-props="{{ json_encode([
                    'initalSearch' => $search->toArray(),
                ]) }}"
            ></div>
        </div>
    </div>

@endsection
