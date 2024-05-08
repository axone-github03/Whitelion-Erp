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
            width: 4px;
            height: 171px;
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
            width: 4px;
            height: 171px;
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
            left: 88%;
        }


        .avatar-sm {
            height: 2.5rem;
            width: 2.5rem;
        }

        .shine {
            -webkit-mask-image: linear-gradient(45deg, #000 25%, rgb(0 0 0 / 67%) 50%, #000 75%);
            mask-image: linear-gradient(45deg, #000 25%, rgb(0 0 0 / 67%) 50%, #000 75%);
            -webkit-mask-size: 800%;
            mask-size: 800%;
            -webkit-mask-position: 0;
            mask-position: 0;
        }

        .shine:hover {
            transition: mask-position 2s ease, -webkit-mask-position 2s ease;
            -webkit-mask-position: 120%;
            mask-position: 120%;
            opacity: 1;
        }

    </style>

    <div class="page-content">
        <div class="container-fluid">

            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">Dashboard</h4>
                    </div>
                </div>
            </div>
            <!-- end page title -->

            <div class="row">
                <div class="col-md-3">
                    <div class="card mini-stats-wid">
                            <div class="mini-stat-icon card-body rounded-3 p-3 shine" style="background-color: #7A80F0;">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 align-self-center me-3">
                                        <div class="avatar-sm">
                                            <span class="avatar-title rounded-3 bg-white">
                                                <i class='bx bx-spreadsheet font-size-24 text-dark'></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <a href="{{ route('crm.lead') }}" target="_blank" class="text-decoration-none">
                                            <p class="text-white fw-medium mb-1">Total Lead & Deal</p>
                                            <h4 class="text-white mb-0">{{ $data['architect']['total_lead_deal'] }}</h4>
                                        </a>
                                    </div>

                                </div>
                            </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card mini-stats-wid">
                        <div class="mini-stat-icon card-body rounded-3 p-3 shine" style="background-color: #BCFFDB;">
                            <div class="d-flex">
                                <div class="flex-shrink-0 align-self-center me-3">
                                    <div class="avatar-sm">
                                        <span class="avatar-title rounded-3 bg-white">
                                            <i class='bx bx-dollar-circle font-size-24 text-dark'></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <a href="{{ route('architect.log') }}" target="_blank" class="text-decoration-none">
                                        <p class="text-dark fw-medium mb-1">Lifetime Points</p>
                                        <h4 class="text-dark mb-0">{{ $data['architect']->lifetime_point }}</h4>
                                    </a>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card mini-stats-wid">
                        <div class="mini-stat-icon card-body rounded-3 p-3 shine" style="background-color: #FFEFB7;">
                            <div class="d-flex">
                                <div class="flex-shrink-0 align-self-center me-3">
                                    <div class="avatar-sm">
                                        <span class="avatar-title rounded-3 bg-white">
                                            <i class='bx bx-shopping-bag font-size-24 text-dark'></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <a href="{{ route('architect.orders') }}" target="_blank" class="text-decoration-none">
                                        <p class="text-dark fw-medium mb-1">Redeemed Points</p>
                                        <h4 class="text-dark mb-0">{{ $data['architect']->redeemed_point }}</h4>
                                    </a>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card mini-stats-wid">
                        <div class="mini-stat-icon card-body rounded-3 p-3 shine" style="background-color: #f8bebe;">
                            <div class="d-flex">
                                <div class="flex-shrink-0 align-self-center me-3">
                                    <div class="avatar-sm">
                                        <span class="avatar-title rounded-3 bg-white">
                                            <i class='bx bx-shopping-bag font-size-24 text-dark'></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <a href="{{ route('architect.gift.products') }}" target="_blank" class="text-decoration-none">
                                        <p class="text-dark fw-medium mb-1">Available Points</p>
                                        <h4 class="text-dark mb-0">{{ $data['architect']->available_point }}</h4>
                                    </a>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- container-fluid -->
    </div>
    <!-- End Page-content -->

    <!-- Transaction Modal -->



@endsection('content')
@section('custom-scripts')

    <script type="text/javascript"></script>
@endsection
