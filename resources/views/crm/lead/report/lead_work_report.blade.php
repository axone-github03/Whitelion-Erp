@extends('layouts.main')
@section('title', $data['title'])
@section('content')

    <style type="text/css">
        td p {
            max-width: 100%;
            white-space: break-spaces;
            word-break: break-all;
        }
    </style>
    <div class="page-content">
        <div class="container-fluid">
            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">Lead Work Report</h4>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form class="custom-validation" action="{{ route('crm.lead.report.export') }}" method="POST">

                                @csrf
                            <div class="row">
                                <div class="col-4">
                                    <div class="mb-3">
                                        <div class="input-daterange input-group" id="div_start_end_datepicker"
                                            data-date-format="dd-mm-yyyy" data-date-autoclose="true"
                                            data-provide="datepicker" data-date-container='#div_start_end_datepicker'>
                                            <input type="text" class="form-control" name="start_date" id="start_date"
                                                value="@php echo date('01-m-Y'); @endphp" placeholder="Start Date" />
                                            <input type="text" class="form-control" name="end_date" id="end_date"
                                                placeholder="End Date" value="@php echo date('t-m-Y'); @endphp" />
                                        </div>
                                    </div>
                                </div>
                                @if (isTaleSalesUser() == 0)
                                <div class="col-6">
                                    <div class="mb-3">
                                        <select class="form-select select2-ajax select2-multiple" multiple="multiple"
                                            name="user_id" id="user_id">
                                        </select>
                                    </div>
                                </div>
                                @endif
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    @if (isAdminOrCompanyAdmin() == 1)
                                        <div class="d-flex flex-wrap gap-2">
                                            <button type="submit" class="btn btn-primary waves-effect waves-light">
                                                <i class="bx bx-export font-size-16 align-middle me-2"></i>Export
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end page title -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <table id="datatable" class="table table-striped dt-responsive  nowrap w-100">
                                <thead>
                                    <tr>
                                        <th>Lead Id</th>
                                        <th>Lead Name </th>
                                        <th>Site Stage /</br>Closing Date</th>
                                        <th>Lead Owner /</br>Source</th>
                                        <th>Status</th>
                                        <th>Title /</br>Description</th>
                                        <th>Created By</th>
                                        <th>Created At</th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div> <!-- end col -->
            </div> <!-- end row -->

        </div>
        <!-- container-fluid -->
    </div>
    <!-- End Page-content -->
    @csrf
@endsection('content')


@section('custom-scripts')


    <script src="{{ asset('assets/libs/parsleyjs/parsley.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/form-validation.init.js') }}"></script>
    <script src="{{ asset('assets/js/pages/jquery.form.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>

    <script type="text/javascript">
        var ajaxLeadReportDataURL = '{{ route('crm.lead.report.ajax') }}';
        var ajaxSearchUser = '{{ route('report.export.search.user') }}';

        var csrfToken = $("[name=_token").val();

        var mainMasterPageLength = getCookie('mainMasterPageLength') !== undefined ? getCookie('mainMasterPageLength') : 10;
        var table = $('#datatable').DataTable({
            "aoColumnDefs": [{
                "bSortable": false,
                "aTargets": [4]
            }],
            "order": [
                [0, 'desc']
            ],
            "processing": true,
            "serverSide": true,
            "pagingType": "full_numbers",
            "pageLength": mainMasterPageLength,
            "ajax": {
                "url": ajaxLeadReportDataURL,
                "type": "POST",
                "data": {
                    "start_date": function() {
                        return $("#start_date").val()
                    },
                    "end_date": function() {
                        return $("#end_date").val()
                    },
                    "user_id": function() {
                        return $("#user_id").val()
                    },
                    "_token": csrfToken,
                }
            },
            "aoColumns": [{
                    "mData": "col_1"
                },
                {
                    "mData": "col_2"
                },
                {
                    "mData": "col_3"
                },
                {
                    "mData": "col_4"
                },
                {
                    "mData": "col_5"
                },
                {
                    "mData": "col_6"
                },
                {
                    "mData": "col_7"
                },
                {
                    "mData": "col_8"
                }
            ]
        });

        function reloadTable() {
            table.ajax.reload(null, false);
        }

        $('#start_date,#end_date,#user_id').on('change', function() {
            reloadTable();
        });

        $('#datatable').on('length.dt', function(e, settings, len) {
            setCookie('mainMasterPageLength', len, 100);
        });

        $("#user_id").select2({
            ajax: {
                url: ajaxSearchUser,
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
            placeholder: 'Search For User',

        });
    </script>
@endsection
