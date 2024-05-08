@extends('layouts.main')
@section('title', $data['title'])
@section('content')

    <link href="{{ asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css') }}" rel="stylesheet" />

    <link href="{{ asset('assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}" rel="stylesheet"
        type="text/css">
    <style type="text/css">
        .section_user_extra_detail {
            border: 1px solid #ced4da;
        }

        .section_user_detail {
            border: 1px solid #ced4da;

        }

        .nav-pills .nav-link {
            border: 1px solid gainsboro;
        }

        .div-start-line {
            border-top: 1px solid #ced4da;
            padding-top: 12px;
        }
    </style>



    <div class="page-content">
        <div class="container-fluid">

            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">Electricians </h4>
                        <div class="page-title-right">
                            @include('../electricians/comman/btn')
                        </div>
                    </div>
                </div>
            </div>
            <!-- end page title -->
            <!-- start row -->

            <div class="row">
                <div class="card">
                    <div class="card-body">
                        @include('../electricians/comman/tab')
                        <br>
                        <div class="table-responsive">
                            <table id="datatable"
                                class="table align-middle table-nowrap table-hover table-striped dt-responsive nowrap w-100">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Name / Type</th>
                                        <th>Phone </th>
                                        <th>Sale Person</th>
                                        @if ($data['type'] == 302)
                                            <th>Points <br> Current</th>
                                            <th>Points <br> Lifetime</th>
                                        @endif
                                        <th>Status</th>
                                        <th>Created By</th>
                                        <th>Action</th>
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
        <!-- end row -->
    </div>
    <!-- container-fluid -->
    </div>
    <!-- End Page-content -->

@endsection('content')
@section('custom-scripts')

    <script src="{{ asset('assets/libs/parsleyjs/parsley.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/form-validation.init.js') }}"></script>
    <script src="{{ asset('assets/js/pages/jquery.form.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>

    @include('../electricians/comman/modal')
    <script type="text/javascript">
        var selectedUserType = "{{ $data['type'] }}";
        var viewMode = "{{ $data['viewMode'] }}";
        var isSalePerson = "{{ $data['isSalePerson'] }}";
        var ajaxURL = "{{ route('electricians.ajax') }}";
        var csrfToken = $("[name=_token").val();
        var addView = "{{ $data['addView'] }}";
        var seachUserId = "{{ $data['searchUserId'] }}";
        var electricianPageLength = getCookie('electricianPageLength') !== undefined ? getCookie('electricianPageLength') : 10;

        $("#filter_electrician_advance").select2({
            minimumResultsForSearch: Infinity
        });

        if (selectedUserType == 302) {

            var table = $('#datatable').DataTable({
                "aoColumnDefs": [{
                    "bSortable": false,
                    "aTargets": [8]
                }],
                "order": [
                    [0, 'desc']
                ],
                "processing": true,
                "serverSide": true,
                "pageLength": electricianPageLength,
                "ajax": {
                    "url": ajaxURL + '?type=' + selectedUserType,
                    "type": "POST",
                    "data": {
                        "_token": csrfToken,
                        "view_mode": function() {
                            return viewMode;
                        },
                        'search_user_id': function() {
                            return seachUserId;
                        },
                        "filter_electrician_advance": function() {
                            return $("#filter_electrician_advance").val();
                        },

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
                        "mData": "sale_person"
                    },
                    {
                        "mData": "total_point_current"
                    },
                    {
                        "mData": "total_point"
                    },
                    {
                        "mData": "status"
                    },
                    {
                        "mData": "created_by"
                    },
                    {
                        "mData": "action"
                    },

                ],
                "drawCallback": function() {

                    seachUserId = "";

                    var tooltipTriggerList = [].slice.call(document.querySelectorAll(
                        '[data-bs-toggle="tooltip"]'))
                    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {

                        return new bootstrap.Tooltip(tooltipTriggerEl)
                    })

                }
            });


        } else {
            var table = $('#datatable').DataTable({
                "aoColumnDefs": [{
                    "bSortable": false,
                    "aTargets": [6]
                }],
                "order": [
                    [0, 'desc']
                ],
                "processing": true,
                "serverSide": true,
                "pageLength": electricianPageLength,
                "ajax": {
                    "url": ajaxURL + '?type=' + selectedUserType,
                    "type": "POST",
                    "data": {
                        "_token": csrfToken,
                        "view_mode": function() {
                            return viewMode;
                        },
                        'search_user_id': function() {
                            return seachUserId;
                        },
                        "filter_electrician_advance": function() {
                            return $("#filter_electrician_advance").val();
                        },

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
                        "mData": "sale_person"
                    },
                    {
                        "mData": "status"
                    },
                    {
                        "mData": "created_by"
                    },
                    {
                        "mData": "action"
                    },

                ],
                "drawCallback": function() {

                    seachUserId = "";

                    var tooltipTriggerList = [].slice.call(document.querySelectorAll(
                        '[data-bs-toggle="tooltip"]'))
                    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {

                        return new bootstrap.Tooltip(tooltipTriggerEl)
                    })

                }
            });

        }


        $('#filter_electrician_advance').on('change', function() {
            table.ajax.reload();


        });

        $('#datatable').on('length.dt', function(e, settings, len) {
            setCookie('electricianPageLength', len, 100);
        });

        $(document).ready(function() {

            if (addView == 1) {

                $("#addBtnUser").click();

            }

        });
    </script>
    @include('../users/comman/script')
@endsection
