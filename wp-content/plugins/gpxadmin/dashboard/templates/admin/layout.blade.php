@stack('styles')
@include('admin::header', ['active' => $active ?? 'dashboard'])
<div class="right_col" role="main">
    <div class="">
        @section('header')
            @if($title)
                <div class="page-title">
                    <div class="title_left">
                        <h3>{{ $title }}</h3>
                    </div>
                </div>
            @endif
        @show
        <div class="title_right">@yield('actions')</div>
        <div class="clearfix"></div>
        <div class="row">
            <div class="col-md-12">
                <div class="x_content">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
</div>
@stack('scripts')
@include('admin::footer')
