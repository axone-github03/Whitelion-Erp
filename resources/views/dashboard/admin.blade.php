@extends('layouts.main')
@section('title', $data['title'])
@section('content')

    <link href="{{ asset('assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}" rel="stylesheet"
        type="text/css">

    <link href="{{ asset('assets/libs/%40fullcalendar/core/main.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/%40fullcalendar/daygrid/main.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/%40fullcalendar/bootstrap/main.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/%40fullcalendar/timegrid/main.min.css') }}" rel="stylesheet" type="text/css" />
    <style type="text/css">
        .fc-time-grid-container {
            display: none;
        }

        .fc-divider {
            display: none;
        }

        .fc-event {
            cursor: pointer;
        }

        .mini-stats-wid .mini-stat-icon:after,
        .mini-stats-wid .mini-stat-icon:before {
            content: "";
            position: absolute;
            width: 2px;
            height: 135px;
            background-color: rgb(255 255 255 / 11%);
            left: 100%;
            -webkit-transform: rotate(142deg);
            transform: rotate(142deg);
            top: -41px;
            -webkit-transition: all .4s;
            transition: all .4s;
            /* right: 0; */
        }

        .mini-stats-wid .mini-stat-icon:after,
        .mini-stats-wid .mini-stat-icon:before {
            content: "";
            position: absolute;
            width: 2px;
            height: 135px;
            background-color: rgb(255 255 255, .1);
            left: 100%;
            -webkit-transform: rotate(142deg);
            transform: rotate(142deg);
            top: -41px;
            -webkit-transition: all .4s;
            transition: all .4s;
            /* right: 0; */
        }

        .mini-stats-wid:hover .mini-stat-icon::after {
            left: 24%;
        }


        .avatar-sm {
            height: 2rem;
            width: 2rem;
        }

        .shine {
            -webkit-mask-image: linear-gradient(45deg, #000 25%, rgb(0 0 0 / 67%) 50%, #000 75%);
            mask-image: linear-gradient(45deg, #000 25%, rgb(0 0 0 / 67%) 50%, #000 75%);
            -webkit-mask-size: 800%;
            mask-size: 800%;
            -webkit-mask-position: 0;
            mask-position: 0;
        }

        .mini-stats-wid:hover .shine {
            transition: mask-position 2s ease, -webkit-mask-position 2s ease;
            -webkit-mask-position: 120%;
            mask-position: 120%;
            opacity: 1;
        }

        .highlight-card {
            border: 1px solid #556ee6;
        }

        p.active_text {
            color: #556ee6 !important;
        }

        .compare_badge {
            width: max-content;
            height: 20px;
            color: #009c27;
            background-color: rgb(15 255 60 / 28%);
            font-size: 14px;
            text-align: center;
            line-height: 20px;
            border-radius: 10px;
            padding: 0px 40px;
            border-radius: 28px;
        }

        .compare_badge1 {
            width: max-content;
            height: 20px;
            color: #f80606;
            background-color: rgb(255 15 15 / 18%);
            font-size: 14px;
            text-align: center;
            line-height: 20px;
            border-radius: 10px;
            padding: 0px 40px;
            border-radius: 28px;
        }
    </style>


    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-4">
                    <input type="hidden" name="is_first_load" id="is_first_load" value="{{ $data['is_first_load'] }}">
                    <div class="mb-3">
                        <div class="input-daterange input-group" id="div_start_end_datepicker" data-date-format="dd-mm-yyyy"
                            data-date-autoclose="true" data-provide="datepicker"
                            data-date-container='#div_start_end_datepicker'>
                            <input type="text" class="form-control" name="start_date" id="start_date"
                                value="@php echo date('01-m-Y'); @endphp" placeholder="Start Date" />
                            <input type="text" class="form-control" name="end_date" id="end_date" placeholder="End Date"
                                value="@php echo date('t-m-Y'); @endphp" />
                        </div>
                    </div>
                </div>
                <div class="col-2">
                    <div class="mb-3">
                        <select class="form-select select2-ajax" name="channel_partner_type" id="channel_partner_type">
                            <option value="0">All</option>
                            @php $ChannelPartners= getChannelPartners(); @endphp
                            @foreach ($ChannelPartners as $key => $value)
                                <option value="{{ $value['id'] }}">{{ $value['short_name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-3">
                    <div class="mb-3">
                        <select class="form-select select2-ajax select2-multiple" multiple="multiple"
                            name="channel_partner_user_id" id="channel_partner_user_id">
                        </select>
                    </div>
                </div>
                <div class="col-3">
                    <div class="mb-3">
                        <select class="form-select select2-ajax select2-multiple" multiple="multiple" name="sales_user_id"
                            id="sales_user_id">
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-2">
                        <div class="mb-3">
                            <select class="form-select select2-ajax" name="state" id="state">

                            </select>
                        </div>
                    </div>
                    <div class="col-2">
                        <div class="mb-3">
                            <select class="form-select select2-ajax" name="city" id="city">

                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- <div class="row">
                <div class="col-2">
                    <div class="card shadow-sm mini-stats-wid count_card" onclick='ViewbarChart("TARGET")' id="TARGET">
                        <input type="hidden" name="target_ids" id="target_ids">
                        <div class="card-body rounded-3 p-2">
                            <div class="d-flex">
                                <div class="mini-stat-icon shine flex-shrink-0 rounded-3 align-self-center me-2">
                                    <div class="avatar-sm">
                                        <span class="avatar-title">
                                            <i class='bx bx-spreadsheet font-size-18 text-white'></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="text-dark fw-medium mb-0 font-size-12">Target</p>
                                    <p class="text-dark mb-0 fw-bold font-size-12" id="target_amount">0</p>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-2 d-none">
                    <div class="card shadow-sm mini-stats-wid count_card" onclick='ViewbarChart("ORDER")' id="ORDER">
                        <input type="hidden" name="order_ids" id="order_ids">
                        <div class="card-body rounded-3 p-2">
                            <div class="d-flex">
                                <div class="mini-stat-icon shine flex-shrink-0 rounded-3 align-self-center me-2">
                                    <div class="avatar-sm">
                                        <span class="avatar-title">
                                            <i class='bx bx-shopping-bag font-size-18 text-white'></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="text-dark fw-medium mb-0 font-size-12">Order</p>
                                    <p class="text-dark mb-0 fw-bold font-size-12" id="order_count">0</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-2">
                    <div class="card shadow-sm mini-stats-wid count_card" onclick='ViewbarChart("PRIDICTION")'
                        id="PRIDICTION">
                        <input type="hidden" name="pridiction_ids" id="pridiction_ids">
                        <div class="card-body rounded-3 p-2">
                            <div class="d-flex">
                                <div class="mini-stat-icon shine flex-shrink-0 rounded-3 align-self-center me-2">
                                    <div class="avatar-sm">
                                        <span class="avatar-title">
                                            <i class='bx bx-shopping-bag font-size-18 text-white'></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="text-dark fw-medium mb-0 font-size-12">Prediction : <span class="text-dark mb-0 fw-bold" id="pridiction_count">0</span></p>
                                    <p class="text-dark fw-medium mb-0 font-size-12">Total Amt. : <span class="text-dark mb-0 fw-bold" id="pridiction_total_amount">0</span></p>
                                    <p class="text-dark fw-medium mb-0 font-size-12 d-none">Wl Amt. : <span class="text-dark mb-0 fw-bold" id="pridiction_wl_amount">0</span></p>
                                    <p class="text-dark fw-medium mb-0 font-size-12">Billing Amt. : <span class="text-dark mb-0 fw-bold" id="pridiction_billing_amount">0</span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-2">
                    <div class="card shadow-sm mini-stats-wid count_card" onclick='ViewbarChart("PLACED")'
                        id="PLACED">
                        <input type="hidden" name="order_place_ids" id="order_place_ids">
                        <div class="card-body rounded-3 p-2">
                            <div class="d-flex">
                                <div class="mini-stat-icon shine flex-shrink-0 rounded-3 align-self-center me-2">
                                    <div class="avatar-sm">
                                        <span class="avatar-title">
                                            <i class='bx bx-check-double font-size-18 text-white'></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="text-dark fw-medium mb-0 font-size-12">Placed</p>
                                    <p class="text-dark mb-0 fw-bold font-size-12" id="order_place_amount">0</p>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-2">
                    <div class="card shadow-sm mini-stats-wid count_card" onclick='ViewbarChart("DISPATCHED")'
                        id="DISPATCHED">
                        <input type="hidden" name="order_dispateched_ids" id="order_dispateched_ids">
                        <div class="card-body rounded-3 p-2">
                            <div class="d-flex">
                                <div class="mini-stat-icon shine flex-shrink-0 rounded-3 align-self-center me-2">
                                    <div class="avatar-sm">
                                        <span class="avatar-title">
                                            <i class='bx bx-archive-out font-size-18 text-white'></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="text-dark fw-medium mb-0 font-size-12">Dispatched</p>
                                    <p class="text-dark mb-0 fw-bold font-size-12" id="order_dispateched_amount">0</p>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-2">
                    <div class="card shadow-sm mini-stats-wid count_card" id="ARCHITECT">
                        <input type="hidden" name="architects_ids" id="architects_ids">
                        <input type="hidden" name="architects_reward_ids" id="architects_reward_ids">
                        <div class="card-body rounded-3 p-2">
                            <div class="d-flex">
                                <div class="mini-stat-icon shine flex-shrink-0 rounded-3 align-self-center me-2">
                                    <div class="avatar-sm">
                                        <span class="avatar-title">
                                            <i class="bx bx-purchase-tag-alt font-size-18 text-white"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <a href="javascript:void(0)" onclick="ViewbarChart('ARCHITECT');"><p class="hightlight_text text-dark fw-medium mb-0 font-size-12" id="ARCHITECT">New Architect : <span class="text-dark mb-0 fw-bold" id="architects_count">0</span></p></a>
                                    <a href="javascript:void(0)" onclick="ViewbarChart('REWARD_ARCHITECT');"><p class="hightlight_text text-dark fw-medium mb-0 font-size-12" id="REWARD_ARCHITECT">Reward Order : <span class="text-dark mb-0 fw-bold" id="architects_reward_count">0</span></p></a>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-2">
                    <div class="card shadow-sm mini-stats-wid count_card" id="ELECTRICIAN">
                        <input type="hidden" name="electricians_ids" id="electricians_ids">
                        <input type="hidden" name="electricians_reward_ids" id="electricians_reward_ids">
                        <div class="card-body rounded-3 p-2">
                            <div class="d-flex">
                                <div class="mini-stat-icon shine flex-shrink-0 rounded-3 align-self-center me-2">
                                    <div class="avatar-sm">
                                        <span class="avatar-title">
                                            <i class="bx bx-purchase-tag-alt font-size-18 text-white"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <a href="javascript:void(0)" onclick="ViewbarChart('ELECTRICIAN');"><p class="hightlight_text text-dark fw-medium mb-0 font-size-12" id="ELECTRICIAN">New Elecrician : <span class="text-dark mb-0 fw-bold" id="electricians_count">0</span></p></a>
                                    <a href="javascript:void(0)" onclick="ViewbarChart('REWARD_ELECTRICIAN');"><p class="hightlight_text text-dark fw-medium mb-0 font-size-12" id="REWARD_ELECTRICIAN">Reward Order : <span class="text-dark mb-0 fw-bold" id="electricians_reward_count">0</span></p></a>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div> --}}

            <div class="row">
                <div style="width: 20% !important">
                    <div class="card shadow-sm mini-stats-wid count_card" onclick='ViewbarChart("TARGET")' id="TARGET">
                        <input type="hidden" name="target_ids" id="target_ids">
                        <div class="card-body rounded-3 p-2">
                            <div class="d-flex">
                                <div class="mini-stat-icon shine flex-shrink-0 rounded-3 align-self-center me-2">
                                    <div class="avatar-sm">
                                        <span class="avatar-title">
                                            <i class='bx bx-spreadsheet font-size-18 text-white'></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="text-dark fw-medium mb-0 font-size-12">Target</p>
                                    <p class="text-dark mb-0 fw-bold font-size-12" id="target_amount">0</p>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-2 d-none">
                    <div class="card shadow-sm mini-stats-wid count_card" onclick='ViewbarChart("ORDER")' id="ORDER">
                        <input type="hidden" name="order_ids" id="order_ids">
                        <div class="card-body rounded-3 p-2">
                            <div class="d-flex">
                                <div class="mini-stat-icon shine flex-shrink-0 rounded-3 align-self-center me-2">
                                    <div class="avatar-sm">
                                        <span class="avatar-title">
                                            <i class='bx bx-shopping-bag font-size-18 text-white'></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="text-dark fw-medium mb-0 font-size-12">Order</p>
                                    <p class="text-dark mb-0 fw-bold font-size-12" id="order_count">0</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div style="width: 20% !important">
                    <div class="card shadow-sm mini-stats-wid count_card" onclick='ViewbarChart("PRIDICTION")'
                        id="PRIDICTION">
                        <input type="hidden" name="pridiction_ids" id="pridiction_ids">
                        <div class="card-body rounded-3 p-2">
                            <div class="d-flex">
                                <div class="mini-stat-icon shine flex-shrink-0 rounded-3 align-self-center me-2">
                                    <div class="avatar-sm">
                                        <span class="avatar-title">
                                            <i class='bx bx-shopping-bag font-size-18 text-white'></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="text-dark fw-medium mb-0 font-size-12">Prediction : <span
                                            class="text-dark mb-0 fw-bold" id="pridiction_count">0</span></p>
                                    <p class="text-dark fw-medium mb-0 font-size-12">Total Amt. : <span
                                            class="text-dark mb-0 fw-bold" id="pridiction_total_amount">0</span></p>
                                    <p class="text-dark fw-medium mb-0 font-size-12 d-none">Wl Amt. : <span
                                            class="text-dark mb-0 fw-bold" id="pridiction_wl_amount">0</span></p>
                                    <p class="text-dark fw-medium mb-0 font-size-12">Billing Amt. : <span
                                            class="text-dark mb-0 fw-bold" id="pridiction_billing_amount">0</span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div style="width: 20% !important">
                    <div class="card shadow-sm mini-stats-wid count_card" onclick='ViewbarChart("DEALCONVERSION")'
                        id="DEALCONVERSION">
                        <input type="hidden" name="deal_convertion_ids" id="deal_convertion_ids">
                        <div class="card-body rounded-3 p-2">
                            <div class="d-flex">
                                <div class="mini-stat-icon shine flex-shrink-0 rounded-3 align-self-center me-2">
                                    <div class="avatar-sm">
                                        <span class="avatar-title">
                                            <i class="bx bx-copy-alt font-size-18 text-white"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="text-dark fw-medium mb-0 font-size-12">Deal Conversion</p>
                                    <p class="text-dark mb-0 fw-bold font-size-12" id="deal_convert_count">0</p>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div style="width: 20% !important">
                    <div class="card shadow-sm mini-stats-wid count_card" onclick='ViewbarChart("PLACED")'
                        id="PLACED">
                        <input type="hidden" name="order_place_ids" id="order_place_ids">
                        <div class="card-body rounded-3 p-2">
                            <div class="d-flex">
                                <div class="mini-stat-icon shine flex-shrink-0 rounded-3 align-self-center me-2">
                                    <div class="avatar-sm">
                                        <span class="avatar-title">
                                            <i class='bx bx-check-double font-size-18 text-white'></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="text-dark fw-medium mb-0 font-size-12">Placed</p>
                                    <p class="text-dark mb-0 fw-bold font-size-12" id="order_place_amount">0</p>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div style="width: 20% !important">
                    <div class="card shadow-sm mini-stats-wid count_card" onclick='ViewbarChart("DISPATCHED")'
                        id="DISPATCHED">
                        <input type="hidden" name="order_dispateched_ids" id="order_dispateched_ids">
                        <div class="card-body rounded-3 p-2">
                            <div class="d-flex">
                                <div class="mini-stat-icon shine flex-shrink-0 rounded-3 align-self-center me-2">
                                    <div class="avatar-sm">
                                        <span class="avatar-title">
                                            <i class='bx bx-archive-out font-size-18 text-white'></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="text-dark fw-medium mb-0 font-size-12">Dispatched</p>
                                    <p class="text-dark mb-0 fw-bold font-size-12" id="order_dispateched_amount">0</p>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div style="width: 20% !important">
                    <div class="card shadow-sm mini-stats-wid count_card" onclick='ViewbarChart("OFFLINELEAD")'
                        id="OFFLINELEAD">
                        <input type="hidden" name="lead_offline_ids" id="lead_offline_ids">
                        <div class="card-body rounded-3 p-2">
                            <div class="d-flex">
                                <div class="mini-stat-icon shine flex-shrink-0 rounded-3 align-self-center me-2">
                                    <div class="avatar-sm">
                                        <span class="avatar-title">
                                            <i class='bx bx-archive-out font-size-18 text-white'></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="text-dark fw-medium mb-0 font-size-12">New Offline Lead</p>
                                    <p class="text-dark mb-0 fw-bold font-size-12" id="lead_offline_count">2326</p>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div style="width: 20% !important">
                    <div class="card shadow-sm mini-stats-wid count_card" onclick='ViewbarChart("MARKETINGLEAD")'
                        id="MARKETINGLEAD">
                        <input type="hidden" name="lead_marketing_ids" id="lead_marketing_ids">
                        <div class="card-body rounded-3 p-2">
                            <div class="d-flex">
                                <div class="mini-stat-icon shine flex-shrink-0 rounded-3 align-self-center me-2">
                                    <div class="avatar-sm">
                                        <span class="avatar-title">
                                            <i class='bx bx-archive-out font-size-18 text-white'></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="text-dark fw-medium mb-0 font-size-12">New Marketing Lead</p>
                                    <p class="text-dark mb-0 fw-bold font-size-12" id="lead_marketing_count">168</p>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div style="width: 20% !important">
                    <div class="card shadow-sm mini-stats-wid count_card" onclick='ViewbarChart("DEMOMEETINGDONELEAD")'
                        id="DEMOMEETINGDONELEAD">
                        <input type="hidden" name="lead_demo_meeting_done_ids" id="lead_demo_meeting_done_ids">
                        <div class="card-body rounded-3 p-2">
                            <div class="d-flex">
                                <div class="mini-stat-icon shine flex-shrink-0 rounded-3 align-self-center me-2">
                                    <div class="avatar-sm">
                                        <span class="avatar-title">
                                            <i class='bx bx-archive-out font-size-18 text-white'></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="text-dark fw-medium mb-0 font-size-12">Demo Meeting Done Lead</p>
                                    <p class="text-dark mb-0 fw-bold font-size-12" id="lead_demo_meeting_done_count">258</p>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div style="width: 20% !important" class="d-none">
                    <div class="card shadow-sm mini-stats-wid count_card" id="ALLEXECUTIVES">
                        <input type="hidden" name="executives_ids" id="executives_ids">
                        <input type="hidden" name="new_executives_ids" id="new_executives_ids">
                        <input type="hidden" name="active_executives_ids" id="active_executives_ids">
                        <div class="card-body rounded-3 p-2">
                            <div class="d-flex">
                                <div class="mini-stat-icon shine flex-shrink-0 rounded-3 align-self-center me-2">
                                    <div class="avatar-sm">
                                        <span class="avatar-title">
                                            <i class='bx bx-shopping-bag font-size-18 text-white'></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <a href="javascript:void(0)" onclick="ViewbarChart('EXECUTIVES');">
                                        <p class="hightlight_text text-dark fw-medium mb-0 font-size-12" id="EXECUTIVES">
                                            Executives : <span class=" text-dark mb-0 fw-bold"
                                                id="executives_count">0</span></p>
                                    </a>
                                    <a href="javascript:void(0)" onclick="ViewbarChart('NEWEXECUTIVES');">
                                        <p class="hightlight_text text-dark fw-medium mb-0 font-size-12"
                                            id="NEWEXECUTIVES">New Executives : <span class=" text-dark mb-0 fw-bold"
                                                id="new_executives_count">0</span></p>
                                    </a>
                                    <a href="javascript:void(0)" onclick="ViewbarChart('ACTIVEEXECUTIVES');">
                                        <p class="hightlight_text text-dark fw-medium mb-0 font-size-12"
                                            id="ACTIVEEXECUTIVES">Active Executives : <span
                                                class=" text-dark mb-0 fw-bold" id="active_executives_count">0</span></p>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div style="width: 20% !important" class="d-none">
                    <div class="card shadow-sm mini-stats-wid count_card" id="ALLADM">
                        <input type="hidden" name="adm_ids" id="adm_ids">
                        <input type="hidden" name="new_adm_ids" id="new_adm_ids">
                        <input type="hidden" name="active_adm_ids" id="active_adm_ids">
                        <div class="card-body rounded-3 p-2">
                            <div class="d-flex">
                                <div class="mini-stat-icon shine flex-shrink-0 rounded-3 align-self-center me-2">
                                    <div class="avatar-sm">
                                        <span class="avatar-title">
                                            <i class='bx bx-shopping-bag font-size-18 text-white'></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <a href="javascript:void(0)" onclick="ViewbarChart('ADM');">
                                        <p class="hightlight_text text-dark fw-medium mb-0 font-size-12" id="ADM">
                                            ADM : <span class="text-dark mb-0 fw-bold" id="adm_count">0</span></p>
                                    </a>
                                    <a href="javascript:void(0)" onclick="ViewbarChart('NEWADM');">
                                        <p class="hightlight_text text-dark fw-medium mb-0 font-size-12" id="NEWADM">
                                            New ADM : <span class="text-dark mb-0 fw-bold" id="new_adm_count">0</span></p>
                                    </a>
                                    <a href="javascript:void(0)" onclick="ViewbarChart('ACTIVEADM');">
                                        <p class="hightlight_text text-dark fw-medium mb-0 font-size-12" id="ACTIVEADM">
                                            Active ADM : <span class="text-dark mb-0 fw-bold"
                                                id="active_adm_count">0</span></p>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div style="width: 20% !important" class="d-none">
                    <div class="card shadow-sm mini-stats-wid count_card" id="ALLAD">
                        <input type="hidden" name="ad_ids" id="ad_ids">
                        <input type="hidden" name="new_ad_ids" id="new_ad_ids">
                        <input type="hidden" name="active_ad_ids" id="active_ad_ids">
                        <div class="card-body rounded-3 p-2">
                            <div class="d-flex">
                                <div class="mini-stat-icon shine flex-shrink-0 rounded-3 align-self-center me-2">
                                    <div class="avatar-sm">
                                        <span class="avatar-title">
                                            <i class='bx bx-shopping-bag font-size-18 text-white'></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <a href="javascript:void(0)" onclick="ViewbarChart('AD');">
                                        <p class="hightlight_text text-dark fw-medium mb-0 font-size-12" id="AD">AD
                                            : <span class=" text-dark mb-0 fw-bold" id="ad_count">0</span></p>
                                    </a>
                                    <a href="javascript:void(0)" onclick="ViewbarChart('NEWAD');">
                                        <p class="hightlight_text text-dark fw-medium mb-0 font-size-12" id="NEWAD">
                                            New AD : <span class=" text-dark mb-0 fw-bold" id="new_ad_count">0</span></p>
                                    </a>
                                    <a href="javascript:void(0)" onclick="ViewbarChart('ACTIVEAD');">
                                        <p class="hightlight_text text-dark fw-medium mb-0 font-size-12" id="ACTIVEAD">
                                            Active AD : <span class=" text-dark mb-0 fw-bold"
                                                id="active_ad_count">0</span></p>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div style="width: 20% !important">
                    <div class="card shadow-sm mini-stats-wid count_card" id="ARCHITECT">
                        <input type="hidden" name="architects_ids" id="architects_ids">
                        <input type="hidden" name="new_architects_ids" id="new_architects_ids">
                        <input type="hidden" name="active_architects_ids" id="active_architects_ids">
                        <div class="card-body rounded-3 p-2">
                            <div class="d-flex">
                                <div class="mini-stat-icon shine flex-shrink-0 rounded-3 align-self-center me-2">
                                    <div class="avatar-sm">
                                        <span class="avatar-title">
                                            <i class="bx bx-purchase-tag-alt font-size-18 text-white"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <a href="javascript:void(0)" class="d-none" onclick="ViewbarChart('ARCHITECT');">
                                        <p class="hightlight_text text-dark fw-medium mb-0 font-size-12" id="ARCHITECT">
                                            Architect </br><span class="text-dark mb-0 fw-bold"
                                                id="architects_count">0</span></p>
                                    </a>
                                    <a href="javascript:void(0)" onclick="ViewbarChart('NEWARCHITECT');">
                                        <p class="hightlight_text text-dark fw-medium mb-0 font-size-12"
                                            id="NEWARCHITECT">New Architect </br> <span class="text-dark mb-0 fw-bold"
                                                id="new_architects_count">0</span></p>
                                    </a>
                                    <a href="javascript:void(0)" class="d-none" onclick="ViewbarChart('ACTIVEARCHITECT');">
                                        <p class="hightlight_text text-dark fw-medium mb-0 font-size-12"
                                            id="ACTIVEARCHITECT">Active Architect : <span class="text-dark mb-0 fw-bold"
                                                id="active_architects_count">0</span></p>
                                    </a>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div style="width: 20% !important">
                    <div class="card shadow-sm mini-stats-wid count_card" id="ELECTRICIAN">
                        <input type="hidden" name="electricians_ids" id="electricians_ids">
                        <input type="hidden" name="new_electricians_ids" id="new_electricians_ids">
                        <input type="hidden" name="active_electricians_ids" id="active_electricians_ids">
                        <div class="card-body rounded-3 p-2">
                            <div class="d-flex">
                                <div class="mini-stat-icon shine flex-shrink-0 rounded-3 align-self-center me-2">
                                    <div class="avatar-sm">
                                        <span class="avatar-title">
                                            <i class="bx bx-purchase-tag-alt font-size-18 text-white"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <a href="javascript:void(0)" class="d-none" onclick="ViewbarChart('ELECTRICIAN');">
                                        <p class="hightlight_text text-dark fw-medium mb-0 font-size-12" id="ELECTRICIAN">
                                            New Elecrician </br><span class="text-dark mb-0 fw-bold"
                                                id="electricians_count">0</span></p>
                                    </a>
                                    <a href="javascript:void(0)"  onclick="ViewbarChart('NEWELECTRICIAN');">
                                        <p class="hightlight_text text-dark fw-medium mb-0 font-size-12"
                                            id="NEWELECTRICIAN">New Elecrician </br> <span class="text-dark mb-0 fw-bold"
                                                id="new_electricians_count">0</span></p>
                                    </a>
                                    <a href="javascript:void(0)" class="d-none" onclick="ViewbarChart('ACTIVEELECTRICIAN');">
                                        <p class="hightlight_text text-dark fw-medium mb-0 font-size-12"
                                            id="ACTIVEELECTRICIAN">Active Elecrician : <span
                                                class="text-dark mb-0 fw-bold" id="active_electricians_count">0</span></p>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row justify-content-between">
                <div class="col-2">
                    <div class="card shadow-sm mini-stats-wid count_card" onclick='ViewbarChart("LEAD")' id="LEAD">
                        <input type="hidden" name="lead_total_ids" id="lead_total_ids">
                        <div class="card-body rounded-3 p-2">
                            <div class="d-flex">
                                <div class="mini-stat-icon shine flex-shrink-0 rounded-3 align-self-center me-2">
                                    <div class="avatar-sm">
                                        <span class="avatar-title">
                                            <i class="bx bx-copy-alt font-size-18 text-white"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="text-dark fw-medium mb-0 font-size-12">New Lead</p>
                                    <p class="text-dark mb-0 fw-bold font-size-12" id="lead_total_count">0</p>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-2">
                    <div class="card shadow-sm mini-stats-wid count_card" onclick='ViewbarChart("RUNNING")'
                        id="RUNNING">
                        <input type="hidden" name="lead_runing_ids" id="lead_runing_ids">
                        <div class="card-body rounded-3 p-2">
                            <div class="d-flex">
                                <div class="mini-stat-icon shine flex-shrink-0 rounded-3 align-self-center me-2">
                                    <div class="avatar-sm">
                                        <span class="avatar-title">
                                            <i class="bx bx-purchase-tag-alt font-size-18 text-white"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="text-dark fw-medium mb-0 font-size-12">Running Lead & Deal</p>
                                    <p class="text-dark mb-0 fw-bold font-size-12" id="lead_runing_count">0</p>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-2">
                    <div class="card shadow-sm mini-stats-wid count_card" onclick='ViewbarChart("WON")' id="WON">
                        <input type="hidden" name="lead_won_ids" id="lead_won_ids">
                        <div class="card-body rounded-3 p-2">
                            <div class="d-flex">
                                <div class="mini-stat-icon shine flex-shrink-0 rounded-3 align-self-center me-2">
                                    <div class="avatar-sm">
                                        <span class="avatar-title">
                                            <i class="bx bx-copy-alt font-size-18 text-white"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="text-dark fw-medium mb-0 font-size-12">Won Deal</p>
                                    <p class="text-dark mb-0 fw-bold font-size-12" id="lead_won_count">0</p>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-2">
                    <div class="card shadow-sm mini-stats-wid count_card" onclick='ViewbarChart("LOST")' id="LOST">
                        <input type="hidden" name="lead_lost_ids" id="lead_lost_ids">
                        <div class="card-body rounded-3 p-2">
                            <div class="d-flex">
                                <div class="mini-stat-icon shine flex-shrink-0 rounded-3 align-self-center me-2">
                                    <div class="avatar-sm">
                                        <span class="avatar-title">
                                            <i class="bx bx-archive-in font-size-18 text-white"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="text-dark fw-medium mb-0 font-size-12">Lost Lead & Deal</p>
                                    <p class="text-dark mb-0 fw-bold font-size-12" id="lead_lost_count">0</p>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-2">
                    <div class="card shadow-sm mini-stats-wid count_card" onclick='ViewbarChart("COLD")' id="COLD">
                        <input type="hidden" name="lead_cold_ids" id="lead_cold_ids">
                        <div class="card-body rounded-3 p-2">
                            <div class="d-flex">
                                <div class="mini-stat-icon shine flex-shrink-0 rounded-3 align-self-center me-2">
                                    <div class="avatar-sm">
                                        <span class="avatar-title">
                                            <i class="bx bx-copy-alt font-size-18 text-white"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="text-dark fw-medium mb-0 font-size-12">Cold Lead & Deal</p>
                                    <p class="text-dark mb-0 fw-bold font-size-12" id="lead_cold_count">0</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row d-none">
                <div class="card">
                    <div class="card-body">
                        <div class="d-sm-flex flex-wrap">
                            <h4 class="card-title mb-4" id="">Sales Overview</h4>
                        </div>
                        <div class="d-flex">
                            <div class="col-4">
                                <table id="datatable1" class="table align-middle table-nowrap mb-0 w-100">
                                    <thead class="table-light">
                                        <tr>
                                            <th id="sale_1">Sales Per Entity</th>
                                            <th id="sale_2" class="text-center">Sales From Last Month</th>
                                            <th id="sale_3"> Total Sales</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Table 1 content goes here -->
                                    </tbody>
                                </table>
                            </div>

                            <div class="col-8" style="padding-left: 5%;">
                                <table id="datatable2" class="table align-middle table-nowrap mb-0 w-100">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center">Executive Name</th>
                                            <th class="col-3 text-center">Target</th>
                                            <th class="text-center">Sales From Last Month</th>
                                            <th>Total Sales</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Table 2 content goes here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-sm-flex flex-wrap">
                                <h4 class="card-title mb-4" id="chart_title">Overview Statistics</h4>
                                <div class="ms-auto">
                                    <ul class="nav nav-pills" id="chart_filter">
                                        <li class="">
                                            <a class="nav-link" href="javascript:void(0);"
                                                onclick="ViewBarChartFilterWise('WEEK')" id="WEEK">Week</a>
                                        </li>
                                        <li class="">
                                            <a class="nav-link active" href="javascript:void(0);"
                                                onclick="ViewBarChartFilterWise('MONTH')" id="MONTH">Month</a>
                                        </li>
                                        <li class="">
                                            <a class="nav-link" href="javascript:void(0);"
                                                onclick="ViewBarChartFilterWise('QUARTER')" id="QUARTER">Quarter</a>
                                        </li>
                                        <li class="">
                                            <a class="nav-link " href="javascript:void(0);"
                                                onclick="ViewBarChartFilterWise('YEAR')" id="YEAR">Year</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div id="bar-chart-apex-dubble"></div>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-sm-flex flex-wrap">
                                <h4 class="card-title mb-4" id="chart_title">Lead & Deal</h4>
                                {{-- <div class="ms-auto">
                                    <ul class="nav nav-pills" id="chart_filter">
                                        <li class="nav-item">
                                            <a class="nav-link active" href="javascript:void(0);"
                                                onclick="ViewBarChartFilterInquery('MONTH')" id="MONTH">Month</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link " href="javascript:void(0);"
                                                onclick="ViewBarChartFilterInquery('YEAR')" id="YEAR">Year</a>
                                        </li>
                                    </ul>
                                </div> --}}
                            </div>
                            <div id="lead_deail_inquery"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <div class="float-end ms-2" id="report_amount">
                                    <button type="button"
                                        class=" ms-2 btn-sm btn btn-outline-dark waves-effect waves-light float-end"
                                        aria-haspopup="true" aria-expanded="false">Quotation Amount: <span
                                            id="report_qutation_amount">0.00</span></button>
                                    <button type="button"
                                        class="btn-sm btn btn-outline-dark waves-effect waves-light float-end"
                                        aria-haspopup="true" aria-expanded="false">Billing Amount: <span
                                            id="report_billing_amount">0.00</span></button>
                                </div>

                                <table id="datatable" class="table align-middle table-nowrap mb-0 dt-responsive">
                                    <thead class="table-light">
                                        <tr>
                                            <th id="col_1">Detail</th>
                                            <th id="col_2">Order By</th>
                                            <th id="col_3">Channel Partner</th>
                                            <th id="col_4">Sales</th>
                                            <th id="col_5">Payment Detail</th>
                                            <th id="col_7">Quotation Amt.</th>
                                            <th id="col_6">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalQuotation" data-bs-backdrop="static" tabindex="-1" role="dialog"
            aria-labelledby="modalQuotationLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-s" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalQuotationLabel">Quotation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalSales" data-bs-backdrop="static" tabindex="-1" role="dialog"
            aria-labelledby="modalQuotationLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalQuotationLabel">Sales Overview</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">

                            <div class="float-end ms-2" id="report_amount_lead">
                                <button type="button"
                                    class=" ms-2 btn-sm btn btn-outline-dark waves-effect waves-light float-end"
                                    aria-haspopup="true" aria-expanded="false">Quotation Amount: <span
                                        id="report_qutation_amount_lead">0.00</span></button>
                                <button type="button"
                                    class="btn-sm btn btn-outline-dark waves-effect waves-light float-end"
                                    aria-haspopup="true" aria-expanded="false">Billing Amount: <span
                                        id="report_billing_amount_lead">0.00</span></button>
                            </div>
                            <table id="datatable3" class="table align-middle table-nowrap mb-0 dt-responsive"
                                style="width: none !important;">
                                <thead class="table-light">
                                    <tr>
                                        <th id="col_11">Detail</th>
                                        <th id="col_12">Order By</th>
                                        <th id="col_13">Channel Partner</th>
                                        <th id="col_14">Sales</th>
                                        <th id="col_15">Payment Detail</th>
                                        <th id="col_17">Quotation Amt.</th>
                                        <th id="col_16">Status</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @csrf

@endsection('content')

@section('custom-scripts')

    <!-- JAVASCRIPT -->

    <!-- plugin js -->
    <script src="{{ asset('assets/libs/moment/min/moment.min.js') }}"></script>
    <script src="{{ asset('assets/libs/jquery-ui-dist/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('assets/libs/%40fullcalendar/core/main.min.js') }}"></script>
    <script src="{{ asset('assets/libs/%40fullcalendar/bootstrap/main.min.js') }}"></script>
    <script src="{{ asset('assets/libs/%40fullcalendar/daygrid/main.min.js') }}"></script>
    <script src="{{ asset('assets/libs/%40fullcalendar/timegrid/main.min.js') }}"></script>
    <script src="{{ asset('assets/libs/%40fullcalendar/interaction/main.min.js') }}"></script>
    <script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>


    <script type="text/javascript">
        var ajaxSearchChannelPartner = "{{ route('dashboard.search.channel.partner') }}";
        var ajaxSearchSalesUser = "{{ route('dashboard.search.sale.user') }}";

        var ajaxDashboardCountData = "{{ route('dashboard.data.count') }}";
        var ajaxURLGetBarChartCount = "{{ route('dashboard.get.bar.chart.count') }}";
        var ajaxURLGetBarChartLead = "{{ route('dashboard.get.bar.chart.lead') }}";
        var ajaxURLDashboardOrderReport = "{{ route('dashboard.order.report.ajax') }}";
        var ajaxURLDashboardSaleExecutiveReport = "{{ route('dashboard.sale.executive.report.ajax') }}";
        var ajaxURLDashboardSaleOverviewEntityReport = "{{ route('dashboard.sale.overview.per.entity.ajax') }}";
        var ajaxURLDashboardSaleOverview = "{{ route('dashboard.sale.overview.ajax') }}";
        var ajaxURLSearchCity = "{{ route('dashboard.search.city') }}";
        var ajaxURLSearchState = "{{ route('dashboard.search.state') }}";
        var ajaxReportIds = "";
        var ajaxReportType = "";
        var ajaxOrderReportIds = "";
        var ajaxOrderReportType = "";
        var ajaxOrderReportTitle = "";

        var csrfToken = $("[name=_token").val();

        function ViewBarChartFilterWise(filter_type) {
            var Type = $('.highlight-card').attr('id');
            if (Type == 'ARCHITECT') {
                var textType = $('.active_text').attr('id');
                ViewbarChart(textType, filter_type)
            } else if (Type == 'ELECTRICIAN') {
                var textType = $('.active_text').attr('id');
                ViewbarChart(textType, filter_type)
            } else if (Type == 'ALLEXECUTIVES') {
                var textType = $('.active_text').attr('id');
                ViewbarChart(textType, filter_type)
            } else if (Type == 'ALLADM') {
                var textType = $('.active_text').attr('id');
                ViewbarChart(textType, filter_type)
            } else if (Type == 'ALLAD') {
                var textType = $('.active_text').attr('id');
                ViewbarChart(textType, filter_type)
            } else {
                ViewbarChart(Type, filter_type)
            }
        }

        function ViewbarChart(type, filter_type = "") {
            ajaxReportIds = "";
            ajaxReportType = "";

            if (filter_type == "") {
                $("#report_amount").hide();
                $('.count_card').removeClass('highlight-card');
                $('.hightlight_text').removeClass('active_text');
            }

            $("#chart_title").html("<i class='bx bx-loader-circle bx-spin bx-rotate-90' ></i>");
            $.ajax({
                type: 'GET',
                url: ajaxURLGetBarChartCount,
                data: {
                    "type": type,
                    "filter_type": filter_type,
                    "start_date": $("#start_date").val(),
                    "end_date": $("#end_date").val(),
                    "user_id": $("#sales_user_id").val(),
                    "channel_partner_type": $("#channel_partner_type").val(),
                    "channel_partner_user_id": $("#channel_partner_user_id").val(),
                    "city": $("#city").val(),
                    "state": $("#state").val(),
                },
                success: function(resultData) {
                    if (resultData['status'] == 1) {
                        $("#chart_title").html(resultData['chart_title']);
                        if (resultData['type'] == 1) {
                            PreviewBarChart(resultData['CountArr'], resultData['MonthArr'], resultData[
                            'title']);
                        } else {
                            PreviewDubbleBarChart(resultData['CountArr'], resultData['MonthArr'], resultData[
                                'title']);
                        }
                    }
                    if (filter_type == "") {
                        $('.nav-link').removeClass('active');
                        $('#MONTH').addClass('active');
                        if (type == "ORDER") {
                            $('#order_ids').parent().addClass('highlight-card');
                            $('#col_1').html('Detail');
                            $('#col_2').html('Order By');
                            $('#col_3').html('Channel Partner');
                            $('#col_4').html('Sales');
                            $('#col_5').html('Payment Detail');
                            $('#col_7').html('');
                            $('#col_6').html('Status');
                            ajaxReportIds = $('#order_ids').val();
                            ajaxReportType = type;
                            reloadTable();
                            reloadExecutiveTable();
                        } else if (type == "PLACED") {
                            $('#order_place_ids').parent().addClass('highlight-card');
                            $('#col_1').html('Detail');
                            $('#col_2').html('Order By');
                            $('#col_3').html('Channel Partner');
                            $('#col_4').html('Sales');
                            $('#col_5').html('Payment Detail');
                            $('#col_7').html('');
                            $('#col_6').html('Status');
                            ajaxReportIds = $('#order_place_ids').val();
                            ajaxReportType = type;
                            reloadTable();
                            reloadExecutiveTable();
                        } else if (type == "DISPATCHED") {
                            $('#order_dispateched_ids').parent().addClass('highlight-card');
                            $('#col_1').html('Detail');
                            $('#col_2').html('Order By');
                            $('#col_3').html('Channel Partner');
                            $('#col_4').html('Sales');
                            $('#col_5').html('Payment Detail');
                            $('#col_6').html('Status');
                            $('#col_7').html('');
                            ajaxReportIds = $('#order_dispateched_ids').val();
                            ajaxReportType = type;
                            reloadTable();
                            reloadExecutiveTable();
                        } else if (type == "ARCHITECT") {
                            $('#architects_ids').parent().addClass('highlight-card');
                            $('#architects_count').parent().addClass('active_text');
                            $('#col_1').html('id');
                            $('#col_2').html('Name / Type');
                            $('#col_3').html('Email / Phone');
                            $('#col_4').html('Sales Person');
                            $('#col_5').html('Point');
                            $('#col_6').html('Status');
                            $('#col_7').html('');
                            ajaxReportIds = $('#architects_ids').val();
                            ajaxReportType = type;
                            reloadTable();
                            reloadExecutiveTable();
                        } else if (type == "NEWARCHITECT") {
                            $('#new_architects_ids').parent().addClass('highlight-card');
                            $('#new_architects_count').parent().addClass('active_text');
                            $('#col_1').html('id');
                            $('#col_2').html('Name / Type');
                            $('#col_3').html('Email / Phone');
                            $('#col_4').html('Sales Person');
                            $('#col_5').html('Point');
                            $('#col_6').html('Status');
                            $('#col_7').html('');
                            ajaxReportIds = $('#new_architects_ids').val();
                            ajaxReportType = type;
                            reloadTable();
                            reloadExecutiveTable();
                        } else if (type == "ACTIVEARCHITECT") {
                            $('#active_architects_ids').parent().addClass('highlight-card');
                            $('#active_architects_count').parent().addClass('active_text');
                            $('#col_1').html('id');
                            $('#col_2').html('Name / Type');
                            $('#col_3').html('Email / Phone');
                            $('#col_4').html('Sales Person');
                            $('#col_5').html('Point');
                            $('#col_6').html('Status');
                            $('#col_7').html('');
                            ajaxReportIds = $('#active_architects_ids').val();
                            ajaxReportType = type;
                            reloadTable();
                            reloadExecutiveTable();
                        } else if (type == "ELECTRICIAN") {
                            $('#electricians_ids').parent().addClass('highlight-card');
                            $('#electricians_count').parent().addClass('active_text');
                            $('#col_1').html('id');
                            $('#col_2').html('Name / Type');
                            $('#col_3').html('Email / Phone');
                            $('#col_4').html('Sales Person');
                            $('#col_5').html('Point');
                            $('#col_6').html('Status');
                            $('#col_7').html('');
                            ajaxReportIds = $('#electricians_ids').val();
                            ajaxReportType = type;
                            reloadTable();
                            reloadExecutiveTable();
                        } else if (type == "NEWELECTRICIAN") {
                            $('#new_electricians_ids').parent().addClass('highlight-card');
                            $('#new_electricians_count').parent().addClass('active_text');
                            $('#col_1').html('id');
                            $('#col_2').html('Name / Type');
                            $('#col_3').html('Email / Phone');
                            $('#col_4').html('Sales Person');
                            $('#col_5').html('Point');
                            $('#col_6').html('Status');
                            $('#col_7').html('');
                            ajaxReportIds = $('#new_electricians_ids').val();
                            ajaxReportType = type;
                            reloadTable();
                            reloadExecutiveTable();
                        } else if (type == "ACTIVEELECTRICIAN") {
                            $('#active_electricians_ids').parent().addClass('highlight-card');
                            $('#active_electricians_count').parent().addClass('active_text');
                            $('#col_1').html('id');
                            $('#col_2').html('Name / Type');
                            $('#col_3').html('Email / Phone');
                            $('#col_4').html('Sales Person');
                            $('#col_5').html('Point');
                            $('#col_6').html('Status');
                            $('#col_7').html('');
                            ajaxReportIds = $('#active_electricians_ids').val();
                            ajaxReportType = type;
                            reloadTable();
                            reloadExecutiveTable();
                        } else if (type == "LEAD") {
                            $("#report_amount").show();
                            $('#lead_total_ids').parent().addClass('highlight-card');
                            $('#col_1').html('id');
                            $('#col_2').html('Name');
                            $('#col_3').html('Site Stage');
                            $('#col_4').html('Closing Date');
                            $('#col_5').html('Lead Owner / Source');
                            $('#col_6').html('Status');
                            $('#col_7').html('Quotation Amt.');
                            ajaxReportIds = $('#lead_total_ids').val();
                            ajaxReportType = type;
                            reloadTable();
                            reloadExecutiveTable();
                        } else if (type == "RUNNING") {
                            $("#report_amount").show();
                            $('#lead_runing_ids').parent().addClass('highlight-card');
                            $('#col_1').html('id');
                            $('#col_2').html('Name');
                            $('#col_3').html('Site Stage');
                            $('#col_4').html('Closing Date');
                            $('#col_5').html('Lead Owner / Source');
                            $('#col_6').html('Status');
                            $('#col_7').html('Quotation Amt.');
                            ajaxReportIds = $('#lead_runing_ids').val();
                            ajaxReportType = type;
                            reloadTable();
                            reloadExecutiveTable();
                        } else if (type == "DEALCONVERSION") {
                            $("#report_amount").show();
                            $('#deal_convertion_ids').parent().addClass('highlight-card');
                            $('#col_1').html('id');
                            $('#col_2').html('Name');
                            $('#col_3').html('Site Stage');
                            $('#col_4').html('Closing Date');
                            $('#col_5').html('Lead Owner / Source');
                            $('#col_6').html('Status');
                            $('#col_7').html('Quotation Amt.');
                            ajaxReportIds = $('#deal_convertion_ids').val();
                            ajaxReportType = type;
                            reloadTable();
                            reloadExecutiveTable();
                        } else if (type == "WON") {
                            $("#report_amount").show();
                            $('#lead_won_ids').parent().addClass('highlight-card');
                            $('#col_1').html('id');
                            $('#col_2').html('Name');
                            $('#col_3').html('Site Stage');
                            $('#col_4').html('Closing Date');
                            $('#col_5').html('Lead Owner / Source');
                            $('#col_6').html('Status');
                            $('#col_7').html('Quotation Amt.');
                            ajaxReportIds = $('#lead_won_ids').val();
                            ajaxReportType = type;
                            reloadTable();
                            reloadExecutiveTable();
                        } else if (type == "LOST") {
                            $("#report_amount").show();
                            $('#lead_lost_ids').parent().addClass('highlight-card');
                            $('#col_1').html('id');
                            $('#col_2').html('Name');
                            $('#col_3').html('Site Stage');
                            $('#col_4').html('Closing Date');
                            $('#col_5').html('Lead Owner / Source');
                            $('#col_6').html('Status');
                            $('#col_7').html('Quotation Amt.');
                            ajaxReportIds = $('#lead_lost_ids').val();
                            ajaxReportType = type;
                            reloadTable();
                            reloadExecutiveTable();
                        } else if (type == "COLD") {
                            $("#report_amount").show();
                            $('#lead_cold_ids').parent().addClass('highlight-card');
                            $('#col_1').html('id');
                            $('#col_2').html('Name');
                            $('#col_3').html('Site Stage');
                            $('#col_4').html('Closing Date');
                            $('#col_5').html('Lead Owner / Source');
                            $('#col_6').html('Status');
                            $('#col_7').html('Quotation Amt.');
                            ajaxReportIds = $('#lead_cold_ids').val();
                            ajaxReportType = type;
                            reloadTable();
                            reloadExecutiveTable();
                        } else if (type == "TARGET") {
                            $('#target_ids').parent().addClass('highlight-card');
                            $('#col_1').html('id');
                            $('#col_2').html('Name');
                            $('#col_3').html('Total Target');
                            $('#col_4').html('Achieved Target');
                            $('#col_5').html('%Achieved');
                            $('#col_6').html('');
                            $('#col_7').html('');
                            ajaxReportIds = $('#target_ids').val();
                            ajaxReportType = type;
                            reloadTable();
                            reloadExecutiveTable();
                            reloadPerEntityTable();
                        } else if (type == "PRIDICTION") {
                            $("#report_amount").show();
                            $('#pridiction_ids').parent().addClass('highlight-card');
                            $('#col_1').html('id');
                            $('#col_2').html('Name');
                            $('#col_3').html('Site Stage');
                            $('#col_4').html('Closing Date');
                            $('#col_5').html('Lead Owner / Source');
                            $('#col_6').html('Status');
                            $('#col_7').html('Quotation Amt.');
                            ajaxReportIds = $('#pridiction_ids').val();
                            ajaxReportType = type;
                            reloadTable();
                            reloadExecutiveTable();
                        } else if (type == "REWARD_ARCHITECT") {
                            $('#architects_reward_ids').parent().addClass('highlight-card');
                            $('#architects_reward_count').parent().addClass('active_text');
                            $('#col_1').html('id');
                            $('#col_2').html('Date & Time');
                            $('#col_3').html('Architect');
                            $('#col_4').html('Assigned');
                            $('#col_5').html('Total Point');
                            $('#col_6').html('Total Amount');
                            $('#col_7').html('');
                            ajaxReportIds = $('#architects_reward_ids').val();
                            ajaxReportType = type;
                            reloadTable();
                            reloadExecutiveTable();
                        } else if (type == "REWARD_ELECTRICIAN") {
                            $('#electricians_reward_ids').parent().addClass('highlight-card');
                            $('#electricians_reward_count').parent().addClass('active_text');
                            $('#col_1').html('id');
                            $('#col_2').html('Date & Time');
                            $('#col_3').html('Electrician');
                            $('#col_4').html('Assigned');
                            $('#col_5').html('Total Point');
                            $('#col_6').html('Total Amount');
                            $('#col_7').html('');
                            ajaxReportIds = $('#electricians_reward_ids').val();
                            ajaxReportType = type;
                            reloadTable();
                            reloadExecutiveTable();
                        } else if (type == "EXECUTIVES") {
                            $('#executives_ids').parent().addClass('highlight-card');
                            $('#executives_count').parent().addClass('active_text');
                            $('#col_1').html('id');
                            $('#col_2').html('Name');
                            $('#col_3').html('Email');
                            $('#col_4').html('Phone');
                            $('#col_5').html('Status');
                            $('#col_6').html('');
                            $('#col_7').html('');
                            ajaxReportIds = $('#executives_ids').val();
                            ajaxReportType = type;
                            reloadTable();
                            reloadExecutiveTable();
                        } else if (type == "NEWEXECUTIVES") {
                            $('#new_executives_ids').parent().addClass('highlight-card');
                            $('#new_executives_count').parent().addClass('active_text');
                            $('#col_1').html('id');
                            $('#col_2').html('Name');
                            $('#col_3').html('Email');
                            $('#col_4').html('Phone');
                            $('#col_5').html('Status');
                            $('#col_6').html('');
                            $('#col_7').html('');
                            ajaxReportIds = $('#new_executives_ids').val();
                            ajaxReportType = type;
                            reloadTable();
                            reloadExecutiveTable();
                        } else if (type == "ACTIVEEXECUTIVES") {
                            $('#active_executives_ids').parent().addClass('highlight-card');
                            $('#active_executives_count').parent().addClass('active_text');
                            $('#col_1').html('id');
                            $('#col_2').html('Name');
                            $('#col_3').html('Email');
                            $('#col_4').html('Phone');
                            $('#col_5').html('Status');
                            $('#col_6').html('');
                            $('#col_7').html('');
                            ajaxReportIds = $('#active_executives_ids').val();
                            ajaxReportType = type;
                            reloadTable();
                            reloadExecutiveTable();
                        } else if (type == "ADM") {
                            $('#adm_ids').parent().addClass('highlight-card');
                            $('#adm_count').parent().addClass('active_text');
                            $('#col_1').html('id');
                            $('#col_2').html('Name');
                            $('#col_3').html('Email / Phone');
                            $('#col_4').html('Firm Name');
                            $('#col_5').html('Sale Person');
                            $('#col_6').html('Status');
                            $('#col_7').html('');
                            ajaxReportIds = $('#adm_ids').val();
                            ajaxReportType = type;
                            reloadTable();
                            reloadExecutiveTable();
                        } else if (type == "NEWADM") {
                            $('#new_adm_ids').parent().addClass('highlight-card');
                            $('#new_adm_count').parent().addClass('active_text');
                            $('#col_1').html('id');
                            $('#col_2').html('Name');
                            $('#col_3').html('Email / Phone');
                            $('#col_4').html('Firm Name');
                            $('#col_5').html('Sale Person');
                            $('#col_6').html('Status');
                            $('#col_7').html('');
                            ajaxReportIds = $('#new_adm_ids').val();
                            ajaxReportType = type;
                            reloadTable();
                            reloadExecutiveTable();
                        } else if (type == "ACTIVEADM") {
                            $('#active_adm_ids').parent().addClass('highlight-card');
                            $('#active_adm_count').parent().addClass('active_text');
                            $('#col_1').html('id');
                            $('#col_2').html('Name');
                            $('#col_3').html('Email / Phone');
                            $('#col_4').html('Firm Name');
                            $('#col_5').html('Sale Person');
                            $('#col_6').html('Status');
                            $('#col_7').html('');
                            ajaxReportIds = $('#active_adm_ids').val();
                            ajaxReportType = type;
                            reloadTable();
                            reloadExecutiveTable();
                        } else if (type == "AD") {
                            $('#ad_ids').parent().addClass('highlight-card');
                            $('#ad_count').parent().addClass('active_text');
                            $('#col_1').html('id');
                            $('#col_2').html('Name');
                            $('#col_3').html('Email / Phone');
                            $('#col_4').html('Firm Name');
                            $('#col_5').html('Sale Person');
                            $('#col_6').html('Status');
                            $('#col_7').html('');
                            ajaxReportIds = $('#ad_ids').val();
                            ajaxReportType = type;
                            reloadTable();
                            reloadExecutiveTable();
                        } else if (type == "NEWAD") {
                            $('#new_ad_ids').parent().addClass('highlight-card');
                            $('#new_ad_count').parent().addClass('active_text');
                            $('#col_1').html('id');
                            $('#col_2').html('Name');
                            $('#col_3').html('Email / Phone');
                            $('#col_4').html('Firm Name');
                            $('#col_5').html('Sale Person');
                            $('#col_6').html('Status');
                            $('#col_7').html('');
                            ajaxReportIds = $('#new_ad_ids').val();
                            ajaxReportType = type;
                            reloadTable();
                            reloadExecutiveTable();
                        } else if (type == "ACTIVEAD") {
                            $('#active_ad_ids').parent().addClass('highlight-card');
                            $('#active_ad_count').parent().addClass('active_text');
                            $('#col_1').html('id');
                            $('#col_2').html('Name');
                            $('#col_3').html('Email / Phone');
                            $('#col_4').html('Firm Name');
                            $('#col_5').html('Sale Person');
                            $('#col_6').html('Status');
                            $('#col_7').html('');
                            ajaxReportIds = $('#active_ad_ids').val();
                            ajaxReportType = type;
                            reloadTable();
                            reloadExecutiveTable();
                        } else if (type == "OFFLINELEAD") {
                            $("#report_amount").show();
                            $('#lead_offline_ids').parent().addClass('highlight-card');
                            $('#col_1').html('id');
                            $('#col_2').html('Name');
                            $('#col_3').html('Site Stage');
                            $('#col_4').html('Closing Date');
                            $('#col_5').html('Lead Owner / Source');
                            $('#col_6').html('Status');
                            $('#col_7').html('Quotation Amt.');
                            ajaxReportIds = $('#lead_offline_ids').val();
                            ajaxReportType = type;
                            reloadTable();
                            reloadExecutiveTable();
                        } else if (type == "MARKETINGLEAD") {
                            $("#report_amount").show();
                            $('#lead_marketing_ids').parent().addClass('highlight-card');
                            $('#col_1').html('id');
                            $('#col_2').html('Name');
                            $('#col_3').html('Site Stage');
                            $('#col_4').html('Closing Date');
                            $('#col_5').html('Lead Owner / Source');
                            $('#col_6').html('Status');
                            $('#col_7').html('Quotation Amt.');
                            ajaxReportIds = $('#lead_marketing_ids').val();
                            ajaxReportType = type;
                            reloadTable();
                            reloadExecutiveTable();
                        } else if (type == "DEMOMEETINGDONELEAD") {
                            $("#report_amount").show();
                            $('#lead_demo_meeting_done_ids').parent().addClass('highlight-card');
                            $('#col_1').html('id');
                            $('#col_2').html('Name');
                            $('#col_3').html('Site Stage');
                            $('#col_4').html('Closing Date');
                            $('#col_5').html('Lead Owner / Source');
                            $('#col_6').html('Status');
                            $('#col_7').html('Quotation Amt.');
                            ajaxReportIds = $('#lead_demo_meeting_done_ids').val();
                            ajaxReportType = type;
                            reloadTable();
                            reloadExecutiveTable();
                        }
                    } else {
                        $('.nav-link').removeClass('active');
                        $('#' + filter_type).addClass('active');
                    }
                }
            });

        }

        function ViewSales(ids, type, title) {
            if (type == "ORDER") {
                $("#report_amount_lead").hide();
                $('#col_11').html('Detail');
                $('#col_12').html('Order By');
                $('#col_13').html('Channel Partner');
                $('#col_14').html(title);
                $('#col_15').html('Payment Detail');
                $('#col_17').html('');
                $('#col_16').html('Status');
                ajaxOrderReportIds = ids;
                ajaxOrderReportType = type;
                ajaxOrderReportTitle = title;
                reloadOrderReport()
            } else if (type == "LEAD") {
                $("#report_amount_lead").show();
                $('#col_11').html('id');
                $('#col_12').html('Name');
                $('#col_13').html('Site Stage');
                $('#col_14').html('Closing Date');
                $('#col_15').html('Lead Owner / ' + (title === 'Customer' ? 'Source' : title));
                $('#col_16').html('Status');
                $('#col_17').html('Quotation Amt.');
                ajaxOrderReportIds = ids;
                ajaxOrderReportType = type;
                ajaxOrderReportTitle = title;
                reloadOrderReport()
            }
            $("#modalSales").modal('show');
            $('#datatable3').removeAttr('style');
        }

        function PreviewBarChart(CountArr, MonthArr, Title) {
            var optionsBarChart123 = {
                chart: {
                    height: 360,
                    type: "bar",
                    stacked: !0,
                    toolbar: {
                        show: !1
                    },
                    zoom: {
                        enabled: !2
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: !1,
                        columnWidth: "8%",
                        endingShape: "rounded",
                        startingShape: "rounded",
                        radiusOnLastStackedBar: true,
                        colors: {
                            backgroundBarColors: ['#F2F4F6', '#F2F4F6', '#F2F4F6', '#F2F4F6', '#F2F4F6', '#F2F4F6',
                                '#F2F4F6', '#F2F4F6', '#F2F4F6', '#F2F4F6'
                            ],
                            backgroundBarRadius: 10,
                        },
                    }
                },
                dataLabels: {
                    enabled: !1
                },
                series: [{
                    name: Title,
                    data: CountArr
                }],
                xaxis: {
                    categories: MonthArr
                },
                colors: ['#556ee6', '#34c38f'],
                legend: {
                    position: "bottom"
                },
                fill: {
                    opacity: 1
                },
            };

            var barChartEl123 = document.getElementById('bar-chart-apex-dubble');
            if (barChartEl123) {
                $('#bar-chart-apex-dubble').html('');
                var barChart123 = new ApexCharts(barChartEl123, optionsBarChart123);
                barChart123.render();
            }
        }

        function PreviewDubbleBarChart(CountArr, MonthArr, Title) {
            var optionsBarChart123 = {
                chart: {
                    height: 360,
                    type: "bar",
                    stacked: !0,
                    toolbar: {
                        show: !1
                    },
                    zoom: {
                        enabled: !0
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: !1,
                        columnWidth: "15%",
                        endingShape: "rounded",
                        startingShape: "rounded",
                        radiusOnLastStackedBar: true,
                        colors: {
                            backgroundBarColors: ['#F2F4F6', '#F2F4F6', '#F2F4F6', '#F2F4F6', '#F2F4F6', '#F2F4F6',
                                '#F2F4F6', '#F2F4F6', '#F2F4F6', '#F2F4F6'
                            ],
                            backgroundBarRadius: 5,
                        },
                    }
                },
                dataLabels: {
                    enabled: !1
                },
                series: CountArr,
                xaxis: {
                    categories: MonthArr
                },
                colors: ['#556ee6', '#34c38f'],
                legend: {
                    position: "bottom"
                },
                fill: {
                    opacity: 1
                },
            };

            var barChartEl123 = document.getElementById('bar-chart-apex-dubble');
            if (barChartEl123) {
                $('#bar-chart-apex-dubble').html('');
                var barChart123 = new ApexCharts(barChartEl123, optionsBarChart123);
                barChart123.render();
            }
        }

        var options = {
            series: [76, 67, 61],
            chart: {
                height: 390,
                type: 'radialBar',
            },
            plotOptions: {
                radialBar: {
                    offsetY: 0,
                    startAngle: 0,
                    endAngle: 270,
                    hollow: {
                        margin: 5,
                        size: '30%',
                        background: 'transparent',
                        image: undefined,
                    },
                    dataLabels: {
                        name: {
                            show: false,
                        },
                        value: {
                            show: false,
                        }
                    }
                }
            },
            colors: ['#1ab7ea', '#0084ff', '#39539E'],
            labels: ['Orders', 'Placed', 'Dispatched'],
            legend: {
                show: true,
                floating: true,
                fontSize: '16px',
                position: 'left',
                offsetX: 160,
                offsetY: 15,
                labels: {
                    useSeriesColors: true,
                },
                markers: {
                    size: 0
                },
                formatter: function(seriesName, opts) {
                    return seriesName + ":  " + opts.w.globals.series[opts.seriesIndex]
                },
                itemMargin: {
                    vertical: 3
                }
            },
            plotOptions: {
                radialBar: {
                    offsetY: -30
                }
            },
            legend: {
                show: true,
                position: 'left',
                containerMargin: {
                    right: 0
                }
            },
            theme: {
                monochrome: {
                    enabled: false
                }
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    legend: {
                        show: false
                    }
                }
            }]
        };

        $("#city").select2({
            ajax: {
                url: ajaxURLSearchCity,
                dataType: 'json',
                delay: 0,
                data: function(params) {
                    return {

                        "state_id": function() {
                            return $("#state").val()
                        },
                        q: params.term, // search term
                        page: params.page
                    };
                },
                processResults: function(data, params) {
                    // parse the results into the format expected by Select2
                    // since we are using custom formatting functions we do not need to
                    // alter the remote JSON data, except to indicate that infinite
                    // scrolling can be used
                    params.page = params.page || 1;

                    return {
                        results: data.results,
                        pagination: {
                            more: (params.page * 30) < data.total_count
                        }
                    };
                },
                cache: false
            },
            placeholder: 'Search for a city',
        });

        $("#state").select2({
            ajax: {
                url: ajaxURLSearchState,
                dataType: 'json',
                delay: 0,
                data: function(params) {
                    return {

                        q: params.term, // search term
                        page: params.page
                    };
                },
                processResults: function(data, params) {
                    // parse the results into the format expected by Select2
                    // since we are using custom formatting functions we do not need to
                    // alter the remote JSON data, except to indicate that infinite
                    // scrolling can be used
                    params.page = params.page || 1;

                    return {
                        results: data.results,
                        pagination: {
                            more: (params.page * 30) < data.total_count
                        }
                    };
                },
                cache: false
            },
            placeholder: 'Search for a state',
        }).on('change', function() {});

        function ViewBarChartFilterInquery(filter_type) {
            var Type = 'RUNNING';
            ViewbarChartIquery(Type, filter_type)
        }

        function ViewbarChartIquery(type, filter_type) {
            ajaxReportIds = "";
            ajaxReportType = "";

            if (filter_type == "") {
                $("#report_amount").hide();
                $('.count_card').removeClass('highlight-card');
                $('.hightlight_text').removeClass('active_text');
            }

            $.ajax({
                type: 'GET',
                url: ajaxURLGetBarChartLead,
                data: {
                    "type": type,
                    "filter_type": filter_type,
                    "start_date": $("#start_date").val(),
                    "end_date": $("#end_date").val(),
                    "user_id": $("#sales_user_id").val(),
                    "channel_partner_type": $("#channel_partner_type").val(),
                    "channel_partner_user_id": $("#channel_partner_user_id").val(),
                    "city_id": $("#city").val(),
                    "state_id": $("#state").val(),
                },
                success: function(resultData) {
                    if (resultData['status'] == 1) {
                        $("#chart_title").html(resultData['chart_title']);
                        if (resultData['type'] == 1) {
                            PreviewBarChartLead(resultData['LeadCountArr'],resultData['CountTitle']);
                        }
                    }
                }
            });
        }

        function PreviewBarChartLead(LeadCountArr,CountTitle) {
            var data = Object.values(LeadCountArr);
            var optionsBarChart123 = {
                chart: {
                    height: 360,
                    type: "bar",
                    stacked: !0,
                    toolbar: {
                        show: !1
                    },
                    zoom: {
                        enabled: !2
                    }

                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        columnWidth: "20%",
                        endingShape: "rounded",
                        startingShape: "rounded",
                        radiusOnLastStackedBar: true,
                        colors: {
                            backgroundBarRadius: 10,
                        },
                    }
                },
                dataLabels: {
                    enabled: false
                },
                series: [{
                    name: "Count",
                    data: data
                }],
                xaxis: {
                    categories: CountTitle,
                    labels: {
                        rotate: -45,
                    }
                },
                colors: ['#556ee6'],
                legend: {
                    position: "top",
                    horizontalAlign: "left",
                    offsetX: -10,
                    offsetY: -10,
                    markers: {
                        radius: 12,
                    },
                },
                fill: {
                    opacity: 1
                },
            };

            var barChartEl123 = document.getElementById('lead_deail_inquery');
            if (barChartEl123) {
                $('#lead_deail_inquery').html('');
                var barChart123 = new ApexCharts(barChartEl123, optionsBarChart123);
                barChart123.render();
            }
        }

        var chart = new ApexCharts(document.querySelector("#radial-bar-chart"), options);
        chart.render();
    </script>

    <!-- Calendar init -->
    <script src="{{ asset('assets/js/c/dashboard-count.js') }}?v={{ date('YmdHis') }}"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            ViewbarChart("TARGET");
            ViewbarChartIquery("RUNNING");
        })
    </script>
@endsection
