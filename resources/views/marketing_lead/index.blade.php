@extends('layouts.main')
@section('title', $data['title'])
@section('content')

    <style type="text/css">
        td p {
            max-width: 100%;
            white-space: break-spaces;
            word-break: break-all;
        }

        #imgPreview {
            width: 100% !important;
            height: 100% !important;
        }

        #div_q_price_item_image {
            width: 100px;
            height: 100px;
            padding: 4px;
            margin: 0 auto;
            cursor: pointer;
        }
    </style>
    <link href="{{ asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}" rel="stylesheet"
        type="text/css">

    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <form id="formItemPriceExcelUpdate" action="{{ route('crm.marketing.lead.save') }}" method="POST" class="needs-validation" novalidate enctype="multipart/form-data">
                            <div class="card-body">
                                @csrf
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3 d-flex flex-column">
                                            <label class="form-label inquiry-questions-lable"
                                                style="font-size: larger;font-weight: bold;">Instructions : </label>
                                            <label class="form-label inquiry-questions-lable"> 1. Download the format file and fill it with proper data.</label>
                                            <label class="form-label inquiry-questions-lable">2. Platform :- 1.fb | 2.ig | 3.googleads</label>
                                            <label class="form-label inquiry-questions-lable">3. Assign To :- 1.Yamee | 2.Farhad</label>
                                            <a href="{{asset('assets/sample_file/marketing_lead_sync.xlsx')}}" target="_blank" class="btn btn-light" type="button"><i class="bx bx-export font-size-16 align-middle me-2"></i>Download Sample Excel</a>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="mb-3">
                                            <label for="q_marketing_lead_excel" class="form-label inquiry-questions-lable"
                                                style="font-size: larger;font-weight: bold;">Upload Lead Excel</label>
                                            <input class="form-control" type="file" value="" id="q_marketing_lead_excel" name="q_marketing_lead_excel" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
                                        </div>
                                        <div id="btnSave">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                            <button id="btnSaveFinal" type="submit" class="btn btn-primary UpdatePriceThrewExcel">Save</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <table id="datatable" class="table table-striped dt-responsive nowrap w-100">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Date</th>
                                        <th>Full Name</th>
                                        <th>Email</th>
                                        <th>City</th>
                                        <th>Phone Number</th>
                                        <th>Platform</th>
                                        <th>Source Type</th>
                                        <th>Sub Source</th>
                                        <th>Source</th>
                                        <th>Assign To</th>
                                        <th>Lead Id</th>
                                        <th>Status</th>
                                        <th>Remark</th>
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
        <!-- container-fluid -->
    </div>
    <!-- End Page-content -->

    @csrf
@endsection('content')

@section('custom-scripts')
    <script src="{{ asset('assets/libs/parsleyjs/parsley.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/form-validation.init.js') }}"></script>
    <script src="{{ asset('assets/js/pages/jquery.form.js') }}"></script>

    <script src="{{ asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
    <script type="text/javascript">

        $(".UpdatePriceThrewExcel").click(function() {
            $(".UpdatePriceThrewExcel").html('<i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i> <span>Saving...</span>');
        });

        var ajaxMarketingLead = '{{route("crm.marketing.lead.ajax")}}';

        var csrfToken = $("[name=_token").val();
        var mainMasterPageLength = getCookie('mainMasterPageLength') !== undefined ? getCookie('mainMasterPageLength') : 10;
       
        var table = $('#datatable').DataTable({
            "aoColumnDefs": [{
                "bSortable": false,
                "aTargets": [13]
            }],
            "order": [
                [0, 'desc']
            ],
            "processing": true,
            "pagingType": "full_numbers",
            "serverSide": true,
            "info": true,
            "pageLength": mainMasterPageLength,
            "ajax": {
                "url": ajaxMarketingLead,
                "type": "POST",
                "data": {
                    "_token": csrfToken,
                }
            },
            "aoColumns": [
                {
                    "mData": "id"
                },
                {
                    "mData": "date"
                },
                {
                    "mData": "full_name"
                },
                {
                    "mData": "email"
                },
                {
                    "mData": "city"
                },
                {
                    "mData": "phone_number"
                },
                {
                    "mData": "platform"
                },
                {
                    "mData": "source_type"
                },
                {
                    "mData": "sub_source"
                },
                {
                    "mData": "source"
                },
                {
                    "mData": "assign_to"
                },
                {
                    "mData": "lead_id"
                },
                {
                    "mData": "status"
                },
                {
                    "mData": "remark"
                },
            ]
        });

        function reloadTable() {
            table.ajax.reload(null, false);
        }

        $('#datatable').on('length.dt', function(e, settings, len) {
            setCookie('mainMasterPageLength', len, 100);
        });

    </script>
@endsection
