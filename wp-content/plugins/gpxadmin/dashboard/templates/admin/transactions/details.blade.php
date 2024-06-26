@php
    /**
     * @var \GPX\Model\Transaction $transaction
     * @var ?array $cancelled
     * @var \Illuminate\Support\Collection $refunds
     * @var \GPX\Model\UserMeta $owner
     * @var ?\GPX\Model\Credit $deposit
     */
@endphp
@extends('admin::layout', ['title' => 'Transaction Details', 'active' => 'transactions'])

@section('content')
    <div
        id="gpxadmin-transaction-details"
        data-props="{{ json_encode([
            'transaction_id' => $transaction->id,
        ]) }}"
    ></div>
@endsection
