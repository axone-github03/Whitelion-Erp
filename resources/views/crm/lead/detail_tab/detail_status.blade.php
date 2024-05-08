<style>
    .funnel1 {
        height: 30px;
        width: auto;
        float: left;
        margin-right: 0.50%;
        position: relative;
        text-align: center;
        text-indent: 16px;
        line-height: 30px;
        font-size: 14px;
        background: #A9A9A9;
        color: #ffffff;
        /* box-shadow: inset 0px 20px 20px 20px rgb(0 0 0 / 15%); */
    }

    .funnel1.active {
        background: #556ee6;
        color: #fff;
    }

    .funnel1.active:before {
        border-left-color: #556ee6 !important;
        z-index: 999 !important;
    }

    .funnel1.active:before,
    .funnel1.active:after {
        position: absolute !important;
        content: '' !important;
        z-index: 1;
        width: 0px !important;
        height: 0 !important;
        top: 50% !important;
        margin: -15px 0 0 !important;
        border: 15px solid transparent;
        border-left-color: #fff;
    }

    .funnel1:before,
    .funnel1:after {
        position: absolute;
        content: '';
        z-index: 1;
        width: 0px;
        height: 0;
        top: 50%;
        margin: -15px 0 0;
        border: 15px solid transparent;
        border-left-color: #f8f8fb;
    }

    .funnel1:after {
        left: 0%;
    }

    .funnel1:before {
        left: 100%;
        z-index: 99;
    }

    .funnel1:before {
        border-left-color: #A9A9A9;
    }

    .funnel1:hover{
        color: white !important;
    }
</style>
@foreach ($data['lead_status'] as $lead_status)

@php
    $activeClass = '';
@endphp

@if ($data['lead']['status'] == $lead_status['id'])
    @php
        $activeClass = 'status-active-class';
    @endphp
@endif

@if ($data['lead']['is_deal'] == 0)
    @if ($lead_status['type'] == 0)
        {{-- <a href="#"
            class="btn btn-sm lead-status-btn-white btn-arrow-right {{ $activeClass }}">{{ $lead_status['name'] }}
        </a> --}}
        <a href="javascript:void(0)" class="funnel1 lead_status_filter_remove {{ $activeClass }}">{{ $lead_status['name'] }}</a>
    @endif
@else
    @if ($lead_status['type'] == 1)
        <a href="javascript:void(0)" class="funnel1 lead_status_filter_remove {{ $activeClass }}">{{ $lead_status['name'] }}</a>
        {{-- <a href="#"
            class="btn btn-sm  lead-status-btn-white btn-arrow-right {{ $activeClass }}">{{ $lead_status['name'] }}
        </a> --}}
    @endif
@endif
@endforeach
