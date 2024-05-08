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
                        <h4 class="mb-sm-0 font-size-18">Reports</h4>



                    </div>
                </div>
            </div>
            <!-- end page title -->

            <div class="row">

                <div class="col-12">

                    <div class="row">


                        <div class="col-lg-4">





                            <div class="mb-3">

                                <label for="inquiry_source_type" class="form-label">Date Range<code
                                        class="highlighter-rouge"></code></label>

                                <div class="input-daterange input-group" id="inquiry_datepicker"
                                    data-date-format="dd-mm-yyyy" data-date-autoclose="true" data-provide="datepicker"
                                    data-date-container='#inquiry_datepicker'>


                                    <input type="text" class="form-control" name="channel_partners_start_date"
                                        id="channel_partners_start_date" value="@php echo date('01-m-Y'); @endphp"
                                        placeholder="Start Date" />
                                    <input type="text" class="form-control" name="channel_partners_end_date"
                                        id="channel_partners_end_date" placeholder="End Date"
                                        value="@php echo date('t-m-Y'); @endphp" />
                                </div>
                            </div>


                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                <label class="form-label">sale person <code class="highlighter-rouge"></code></label>
                                <select class="form-control select2-ajax" id="channel_partner_sales_user_id"
                                    name="channel_partner_sales_user_id" required>
                                    <option value="0">All</option>

                                </select>

                            </div>
                        </div>

                        <div class="col-lg-8">

                            <div class="row source-wise-table" id="divSourceWiseSalePerson">

                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-body">

                                            <table id="datatableChannelPartnerType"
                                                class="table table-striped dt-responsive  nowrap w-100 ">
                                                <thead>
                                                    <tr>
                                                        <th>Channel Partner Type</th>
                                                        <th> Count</th>
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











                    </div> <!-- end col -->



                </div>
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">


                            <table id="datatableChannelPartners" class="table table-striped nowrap w-100">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Name / Type</th>
                                        <th>Email / Phone /GST</th>
                                        <th>Invoice From</th>
                                        <th>Sale Persons</th>

                                        <th>Status</th>




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

    </div> <!-- container-fluid -->
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    </div>









    @csrf

@endsection('content')

@section('custom-scripts')


    <script src="{{ asset('assets/libs/moment/min/moment.min.js') }}"></script>
    <script src="{{ asset('assets/libs/jquery-ui-dist/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('assets/libs/%40fullcalendar/core/main.min.js') }}"></script>
    <script src="{{ asset('assets/libs/%40fullcalendar/bootstrap/main.min.js') }}"></script>
    <script src="{{ asset('assets/libs/%40fullcalendar/daygrid/main.min.js') }}"></script>
    <script src="{{ asset('assets/libs/%40fullcalendar/timegrid/main.min.js') }}"></script>
    <script src="{{ asset('assets/libs/%40fullcalendar/interaction/main.min.js') }}"></script>

    <script src="{{ asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>


    <script type="text/javascript">
        var csrfToken = $("[name=_token").val();
        var ajaxSearchSalePerson = '{{ route('channel.partners.reports.search.sale.person') }}';
        var ajaxChannelPartnerType = '{{ route('channel.partners.reports.list.type') }}';
        var ajaxReportList = '{{ route('channel.partners.reports.list') }}';
    </script>




    <script type="text/javascript">
        var datatableChannelPartners = $('#datatableChannelPartners').DataTable({
            "aoColumnDefs": [{
                "bSortable": false,
                "aTargets": [5]
            }],
            "order": [
                [0, 'desc']
            ],
            "processing": true,
            "serverSide": true,
            "pageLength": 10,
            "ajax": {
                "url": ajaxReportList,
                "type": "POST",
                "data": {
                    "_token": csrfToken,
                    "start_date": function() {
                        return $("#channel_partners_start_date").val()
                    },
                    "end_date": function() {
                        return $("#channel_partners_end_date").val();
                    },
                    "sales_user_id": function() {
                        return $("#channel_partner_sales_user_id").val();
                    }
                }
            },
            "aoColumns": [{
                    "mData": "id"
                },
                {
                    "mData": "name"
                },
                {
                    "mData": "email"
                },
                {
                    "mData": "invoice_from"
                },
                {
                    "mData": "sale_persons"
                },
                {
                    "mData": "status"
                },


            ]
        });

        $("#channel_partner_sales_user_id").select2({
            ajax: {
                url: ajaxSearchSalePerson,
                dataType: 'json',
                delay: 0,
                data: function(params) {
                    return {
                        q: params.term, // search term
                        page: params.page,

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
            placeholder: 'Search for a user',

        });


        function loadChannelPartnerTypes() {




            $.ajax({
                type: 'POST',
                url: ajaxChannelPartnerType,
                data: {
                    "_token": csrfToken,
                    "start_date": function() {
                        return $("#channel_partners_start_date").val()
                    },
                    "end_date": function() {
                        return $("#channel_partners_end_date").val();
                    },
                    "sales_user_id": function() {
                        return $("#channel_partner_sales_user_id").val();
                    }

                },
                success: function(resultData) {


                    if (resultData['status'] == 1) {

                        $("#datatableChannelPartnerType tbody").html(resultData['view']);

                    } else {

                        toastr["error"](resultData['msg']);

                    }
                }
            });

        }
        loadChannelPartnerTypes();









        $("#channel_partner_sales_user_id").change(function() {


            datatableChannelPartners.ajax.reload();
            loadChannelPartnerTypes();




        });





        $("#channel_partners_start_date,#channel_partners_end_date").change(function() {

            datatableChannelPartners.ajax.reload();
            loadChannelPartnerTypes();
        });
    </script>



@endsection
