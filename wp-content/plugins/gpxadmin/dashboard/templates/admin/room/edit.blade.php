@extends('admin::layout', ['title' => 'Edit Room', 'active' => 'inventory'])
@php
    /**
     * @var GPX\Model\Week $week
     * @var ?string $message
     * @var Illuminate\Support\MessageBag $errors
     * @var Illuminate\Support\Collection $resorts
     * @var string $status
     * @var bool $is_booked
     */
@endphp

@section('actions')
    <div class="col-md-5 col-sm-5 col-xs-12 form-group pull-right top_search">
        @if(!$is_booked)
            <div id="gpxadmin-room-delete" data-props="{{ json_encode(['week_id' => $week->record_id]) }}"></div>
        @endif
        <div class="well">
            <ul>
                <li class="red">Room Status: {{ $status }}</li>
                <li>Added: {{ $week->create_date->format('m/d/Y') }}</li>
                <li>By: Jeffrey Shaikh</li>
                <li><a href="#" class="fulldetails" data-toggle="modal" data-target="#updateDets">See History</a></li>
            </ul>
            <div id="updateDets" class="modal fade" role="dialog">
                <div class="modal-dialog">
                    <!-- Modal content-->
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">×</button>
                            <h4 class="modal-title">Room Update History</h4>
                        </div>
                        <div class="modal-body">
                            <ul>
                                @foreach($history as $action)
                                <li>
                                    <a href="#" class="show-history">{{ $action['user'] }} on {{ $action['date']->format('m/d/Y g:i A') }}</a>
                                    <div class="room-history">
                                        <div><strong>Item</strong></div>
                                        <div><strong>Old</strong></div>
                                        <div><strong>New</strong></div>
                                        @foreach($action['details'] as $field => $update)
                                            <div>{{ $field }}</div>
                                            @unless($field === 'room_archived')
                                                <div>{{ $update['old'] }}</div>
                                                <div>{{ $update['new'] }}</div>
                                            @endunless
                                        @endforeach
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')

    <div id="gpxadmin-room-edit" data-props="{{ json_encode([
        'id' => $week->record_id,
        'disabled' => !$can_edit,
        'room' => [
            'resort_confirmation_number' => $week->resort_confirmation_number,
            'check_in_date' => $week->check_in_date?->format('Y-m-d'),
            'check_out_date' => $week->check_out_date?->format('Y-m-d'),
            'resort' => $week->resort,
            'unit_type' => $week->unit_type,
            'source_num' => $week->source_num,
            'source_partner_id' => $week->source_partner_id ?: null,
            'source_partner' => $week->partner?->name,
            'active' => $week->active,
            'active_type' => $week->active_type ?: null,
            'active_specific_date' => $week->active_specific_date?->format('Y-m-d'),
            'active_week_month' => $week->active_week_month,
            'availability' => $week->availability,
            'available_to_partner_id' => $week->available_to_partner_id ?: null,
            'available_partner' => $week->available_partner?->name,
            'type' => $week->type,
            'price' => $week->price,
            'min_price' => (float)get_option('gpx_min_rental_fee', 0.00),
            'active_rental_push_date' => $week->active_rental_push_date?->format('Y-m-d'),
            'note' => $week->note,
            'status' => $status,
        ],
        'resorts' => $resorts->map(fn($resort) => [
            'id' => $resort->id,
            'name' => $resort->ResortName,
        ]),
        'unit_types' => $unit_types->map(fn($type) => [
            'id' => $type->record_id,
            'name' => $type->name,
        ]),
    ]) }}"></div>


    <div class="panel panel-default">
        <div class="panel-heading">
            <h4>Transactions</h4>
        </div>
        <div class="panel-body">
            <div id="gpxadmin-room-transactions" data-props="{{ json_encode([ 'id' => $week->record_id, ]) }}"></div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h4>Holds</h4>
        </div>
        <div class="panel-body">
            <div id="gpxadmin-room-holds" data-props="{{ json_encode([ 'id' => $week->record_id, ]) }}"></div>
        </div>
    </div>
@endsection
