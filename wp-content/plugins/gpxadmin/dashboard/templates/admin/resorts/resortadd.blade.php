@extends('admin::layout', ['title' => 'Add Resort', 'active' => 'resorts'])
@php
    /**
     * @var ?string $message
     * @var GPX\Model\Resort $resort
     * @var Illuminate\Support\MessageBag $errors
     */
@endphp

@section('content')
    @if($message)
        <div class="alert {{ $errors->isNotEmpty() ? 'alert-danger' : 'alert-success' }}">{{ $message }}</div>
    @endif
    <form id="resort-add" class="form-horizontal form-label-left usage_exclude"
          method="POST" action="{{ gpx_admin_route('resorts_add') }}" novalidate>
        <div id="usage-add" class="usage_exclude" data-type="usage">
            <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="ResortID">
                    Resort ID
                    <span class="required">*</span>
                </label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="ResortID" name="ResortID"
                           class="form-control col-md-7 col-xs-12 {{ $errors->has('ResortID') ? 'parsley-error' : '' }}"
                           maxlength="255" required
                           value="@attr($resort->ResortID)"
                    />
                    @if( $errors->has( 'ResortID' ) )
                        <div class="form-error">{{ $errors->first( 'ResortID' ) }}</div>
                    @endif
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="ResortName">
                    Resort Name
                    <span class="required">*</span>
                </label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="ResortName" name="ResortName"
                           class="form-control col-md-7 col-xs-12 {{ $errors->has('ResortName') ? 'parsley-error' : '' }}"
                           maxlength="255" required
                           value="@attr($resort->ResortName)"
                    />
                    @if ( $errors->has( 'ResortName' ) )
                        <div class="form-error">{{ $errors->first( 'ResortName' ) }}</div>
                    @endif
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Website">
                    Website
                </label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="url" id="Website" name="Website"
                           class="form-control col-md-7 col-xs-12 {{ $errors->has('Website') ? 'parsley-error' : '' }}"
                           maxlength="255"
                           value="@attr($resort->Website)"
                    />
                    @if ( $errors->has( 'Website' ) )
                        <div class="form-error">{{ $errors->first( 'Website' ) }}</div>
                    @endif
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Address1">
                    Address 1
                </label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="Address1" name="Address1"
                           class="form-control col-md-7 col-xs-12 {{ $errors->has('Address1') ? 'parsley-error' : '' }}"
                           value="@attr($resort->Address1)"
                    />
                    @if ( $errors->has( 'Address1' ) )
                        <div class="form-error">{{ $errors->first( 'Address1' ) }}</div>
                    @endif
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Address2">
                    Address 2
                </label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="Address2" name="Address2"
                           class="form-control col-md-7 col-xs-12 {{ $errors->has('Address2') ? 'parsley-error' : '' }}"
                           value="@attr($resort->Address2)"
                    />
                    @if ( $errors->has( 'Address2' ) )
                        <div class="form-error">{{ $errors->first( 'Address2' ) }}</div>
                    @endif
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Town">
                    City
                </label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="Town" name="Town"
                           class="form-control col-md-7 col-xs-12 {{ $errors->has('Town') ? 'parsley-error' : '' }}"
                           maxlength="255"
                           value="@attr($resort->Town)"
                    />
                    @if ( $errors->has( 'Town' ) )
                        <div class="form-error">{{ $errors->first( 'Town' ) }}</div>
                    @endif
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Region">
                    State
                </label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="Region" name="Region"
                           class="form-control col-md-7 col-xs-12 {{ $errors->has('Region') ? 'parsley-error' : '' }}"
                           maxlength="255"
                           value="@attr($resort->Region)"
                    />
                    @if ( $errors->has( 'Region' ) )
                        <div class="form-error">{{ $errors->first( 'Region' ) }}</div>
                    @endif
                </div>
            </div>


            <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="PostCode">
                    ZIP
                </label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="PostCode" name="PostCode"
                           class="form-control col-md-7 col-xs-12 {{ $errors->has('PostCode') ? 'parsley-error' : '' }}"
                           maxlength="255"
                           value="@attr($resort->PostCode)"
                    />
                    @if ( $errors->has( 'PostCode' ) )
                        <div class="form-error">{{ $errors->first( 'PostCode' ) }}</div>
                    @endif
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Country">
                    Country
                </label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="Country" name="Country"
                           class="form-control col-md-7 col-xs-12 {{ $errors->has('Country') ? 'parsley-error' : '' }}"
                           maxlength="255"
                           value="@attr($resort->Country)"
                    />
                    @if ( $errors->has( 'Country' ) )
                        <div class="form-error">{{ $errors->first( 'Country' ) }}</div>
                    @endif
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Phone">
                    Phone
                </label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="tel" id="Phone" name="Phone"
                           class="form-control col-md-7 col-xs-12 {{ $errors->has('Phone') ? 'parsley-error' : '' }}"
                           maxlength="255"
                           value="@attr($resort->Phone)"
                    />
                    @if ( $errors->has( 'Phone' ) )
                        <div class="form-error">{{ $errors->first( 'Phone' ) }}</div>
                    @endif
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Fax">
                    Fax
                </label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="tel" id="Fax" name="Fax"
                           class="form-control col-md-7 col-xs-12 {{ $errors->has('Fax') ? 'parsley-error' : '' }}"
                           maxlength="255"
                           value="@attr($resort->Fax)"
                    />
                    @if ( $errors->has( 'Fax' ) )
                        <div class="form-error">{{ $errors->first( 'Fax' ) }}</div>
                    @endif
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Email">
                    Email
                </label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="email" id="Email" name="Email"
                           class="form-control col-md-7 col-xs-12 {{ $errors->has('Email') ? 'parsley-error' : '' }}"
                           maxlength="255"
                           value="@attr($resort->Email)"
                    />
                    @if ( $errors->has( 'Email' ) )
                        <div class="form-error">{{ $errors->first( 'Email' ) }}</div>
                    @endif
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="CheckInDays">
                    Check In Days
                </label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="CheckInDays" name="CheckInDays"
                           class="form-control col-md-7 col-xs-12 {{ $errors->has('CheckInDays') ? 'parsley-error' : '' }}"
                           maxlength="255"
                           value="@attr($resort->CheckInDays)"
                    />
                    @if ( $errors->has( 'CheckInDays' ) )
                        <div class="form-error">{{ $errors->first( 'CheckInDays' ) }}</div>
                    @endif
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="CheckInEarliest">
                    Check In Time
                </label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="CheckInEarliest" name="CheckInEarliest"
                           class="form-control col-md-7 col-xs-12 {{ $errors->has('CheckInEarliest') ? 'parsley-error' : '' }}"
                           maxlength="255"
                           value="@attr($resort->CheckInEarliest)"
                    />
                    @if ( $errors->has( 'CheckInEarliest' ) )
                        <div class="form-error">{{ $errors->first( 'CheckInEarliest' ) }}</div>
                    @endif
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="CheckOutLatest">
                    Check Out
                    Time
                </label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="CheckOutLatest" name="CheckOutLatest"
                           class="form-control col-md-7 col-xs-12 {{ $errors->has('CheckOutLatest') ? 'parsley-error' : '' }}"
                           maxlength="255"
                           value="@attr($resort->CheckOutLatest)"
                    />
                    @if ( $errors->has( 'CheckOutLatest' ) )
                        <div class="form-error">{{ $errors->first( 'CheckOutLatest' ) }}</div>
                    @endif
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Airport">
                    Airport
                </label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="Airport" name="Airport"
                           class="form-control col-md-7 col-xs-12 {{ $errors->has('Airport') ? 'parsley-error' : '' }}"
                           value="@attr($resort->Airport)"
                    />
                    @if ( $errors->has( 'Airport' ) )
                        <div class="form-error">{{ $errors->first( 'Airport' ) }}</div>
                    @endif
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Directions">
                    Directions
                </label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="Directions" name="Directions"
                           class="form-control col-md-7 col-xs-12 {{ $errors->has('Directions') ? 'parsley-error' : '' }}"
                           value="@attr($resort->Directions)"
                    />
                    @if ( $errors->has( 'Directions' ) )
                        <div class="form-error">{{ $errors->first( 'Directions' ) }}</div>
                    @endif
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Description">
                    Description
                </label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="Description" name="Description"
                           class="form-control col-md-7 col-xs-12 {{ $errors->has('Description') ? 'parsley-error' : '' }}"
                           value="@attr($resort->Description)"
                    />
                    @if ( $errors->has( 'Description' ) )
                        <div class="form-error">{{ $errors->first( 'Description' ) }}</div>
                    @endif
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="AdditionalInfo">
                    Additional Info
                </label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="AdditionalInfo" name="AdditionalInfo"
                           class="form-control col-md-7 col-xs-12 {{ $errors->has('AdditionalInfo') ? 'parsley-error' : '' }}"
                           value="@attr($resort->AdditionalInfo)"
                    />
                    @if ( $errors->has( 'AdditionalInfo' ) )
                        <div class="form-error">{{ $errors->first( 'AdditionalInfo' ) }}</div>
                    @endif
                </div>
            </div>

            <div class="form-group">
                <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                    <button id="submit-button" type="submit" class="btn btn-success">
                        Submit
                        <i id="loading-spinner" class="fa fa-circle-o-notch fa-spin fa-fw"
                           style="display: none;"></i>
                    </button>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        document.getElementById('resort-add').addEventListener('submit', function () {
            document.getElementById('loading-spinner').style.display = 'inline-block';
            document.getElementById('submit-button').setAttribute('disabled', 'disabled');
        });
    </script>
@endpush
