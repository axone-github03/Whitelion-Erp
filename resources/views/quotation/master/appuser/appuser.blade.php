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

        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">App User Master</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-2" style="text-align: center;border-right: solid #dbdbdb 1px;">
                                <div class="d-flex flex-column ms-2 pe-2">
                                    <div class="text-capitalize" style="font-size: 18px;"><span style="font-weight: bold;">Admin (12) </span> </div>
                                    <div class="text-capitalize"><span style="font-weight: 600;">Iphone : 6 </span></div>
                                    <div class="text-capitalize"><span style="font-weight: 600;">Android : 6 </span></div>
                                </div>
                            </div>
                            <div class="col-2" style="text-align: center;border-right: solid #dbdbdb 1px;">
                                <div class="d-flex flex-column ms-2 pe-2">
                                    <div class="text-capitalize" style="font-size: 18px;"><span style="font-weight: bold;">Company Admin (18) </span> </div>
                                    <div class="text-capitalize"><span style="font-weight: 600;">Iphone : 6 </span></div>
                                    <div class="text-capitalize"><span style="font-weight: 600;">Android : 6 </span></div>
                                </div>
                            </div>
                            <div class="col-2" style="text-align: center;border-right: solid #dbdbdb 1px;">
                                <div class="d-flex flex-column ms-2 pe-2">
                                    <div class="text-capitalize" style="font-size: 18px;"><span style="font-weight: bold;">Sales (10)</span> </div>
                                    <div class="text-capitalize"><span style="font-weight: 600;">Iphone : 6 </span></div>
                                    <div class="text-capitalize"><span style="font-weight: 600;">Android : 6 </span></div>
                                </div>
                            </div>
                            <div class="col-2" style="text-align: center;border-right: solid #dbdbdb 1px;">
                                <div class="d-flex flex-column ms-2 pe-2">
                                    <div class="text-capitalize" style="font-size: 18px;"><span style="font-weight: bold;">Electrician (10)</span> </div>
                                    <div class="text-capitalize"><span style="font-weight: 600;">Iphone : 6 </span></div>
                                    <div class="text-capitalize"><span style="font-weight: 600;">Android : 6 </span></div>
                                </div>
                            </div>
                            <div class="col-2" style="text-align: center;border-right: solid #dbdbdb 1px;">
                                <div class="d-flex flex-column ms-2 pe-2">
                                    <div class="text-capitalize" style="font-size: 18px;"><span style="font-weight: bold;">Architect (10)</span> </div>
                                    <div class="text-capitalize"><span style="font-weight: 600;">Iphone : 6 </span></div>
                                    <div class="text-capitalize"><span style="font-weight: 600;">Android : 6 </span></div>
                                </div>
                            </div>
                            <div class="col-2" style="text-align: center;">
                                <div class="d-flex flex-column ms-2 pe-2">
                                    <div class="text-capitalize" style="font-size: 18px;"><span style="font-weight: bold;">Channel Partener (10)</span> </div>
                                    <div class="text-capitalize"><span style="font-weight: 600;">Iphone : 6 </span></div>
                                    <div class="text-capitalize"><span style="font-weight: 600;">Android : 6 </span></div>
                                </div>
                            </div>
                        </div>
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
                                    <th>Id</th>
                                    <th>Name</th>
                                    <th>User Type</th>
                                    <th>description</th>
                                    <th>Source</th>
                                    <th>login date</th>
                                    <th>Action</th>
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
<script type="text/javascript">
    var ajaxAppUserMasterDataURL = '{{route("quot.app.user.master.ajax")}}';

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
            "url": ajaxAppUserMasterDataURL,
            "type": "POST",
            "data": {
                "_token": csrfToken,
            }
        },
        "aoColumns": [{
                "mData": "id"
            },
            {
                "mData": "name"
            },
            {
                "mData": "user_type"
            },
            {
                "mData": "description"
            },
            {
                "mData": "source"
            },
            {
                "mData": "login_date"
            },
            {
                "mData": "action"
            }
        ]
    });

    function reloadTable() {
        table.ajax.reload(null, false);
    }

    // function isNumber(evt) {
    //     evt = (evt) ? evt : window.event;
    //     var charCode = (evt.which) ? evt.which : evt.keyCode;
    //     if (charCode > 31 && (charCode < 48 || charCode > 57)) {
    //         return false;
    //     }
    //     return true;
    // }

    $('#datatable').on('length.dt', function(e, settings, len) {

        setCookie('mainMasterPageLength', len, 100);


    });
</script>
@endsection