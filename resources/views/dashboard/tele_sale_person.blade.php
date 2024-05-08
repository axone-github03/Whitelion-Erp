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

                <div class="col-12">

                    <div class="row">

                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title mb-4">Inquiry Calendar</h4>
                                    <select class="form-select" name="inquiry_calender_user_id"
                                        id="inquiry_calender_user_id">
                                    </select>
                                    <br>
                                    <br>
                                    <div id="calendar"></div>
                                </div>
                            </div>
                        </div> <!-- end col -->

                        <div>
                        </div>
                    </div>
                </div>
            </div>

        </div> <!-- container-fluid -->
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

    <script src="{{ asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>


    <script type="text/javascript">
        var ajaxInquiryCalendarinquiryList = '{{ route('inquiry') }}';
        var ajaxInquiryCalenderSearchUser = '{{ route('dashboard.inquiry.calender.search.user') }}';
        var ajaxInquiryCalendarData = '{{ route('dashboard.inquiry.calender.data') }}';

        var csrfToken = $("[name=_token").val();
    </script>


    <!-- Calendar init -->
    <script src="{{ asset('assets/js/c/dashboard-inquiry-calendar.js') }}?v=3"></script>
    <script src="{{ asset('assets/js/c/dashboard-sales-order-count.js') }}?v=4"></script>
    <script src="{{ asset('assets/js/c/dashboard-inquiry-architects-count.js?v=3') }}"></script>




@endsection
