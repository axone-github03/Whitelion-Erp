@extends('layouts.main')
@section('title')
@section('content')

    <style type="text/css">
        td p {
            max-width: 100%;
            white-space: break-spaces;
            word-break: break-all;
        }

        thead th {
            padding: 8px;
            font-size: 1rem;
            text-align: center;
        }

        .summary_table td,
        .summary_table th {
            vertical-align: middle !important;
        }

        .summary_table thead {
            background-color: #eff2f7;
        }

        .summary_table tbody,
        .summary_table td,
        .summary_table tfoot .summary_table th,
        .summary_table thead,
        .summary_table tr {
            border-color: #eff2f7;
            border-width: 1px !important;
        }

        #imgPreview {
            width: 100% !important;
            height: 100% !important;
        }

        #div_q_item_image {
            width: 100px;
            height: 100px;
            padding: 4px;
            margin: 0 auto;
            cursor: pointer;
        }
    </style>

    <div class="page-content">
        <div class="container-fluid">

            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">WAREHOSE MASTER</h4>
                        <div class="page-title-right">
                            {{-- @if (isAdminOrCompanyAdmin() == 1)
                        <a href="{{route('quot.item.master.export')}}" target="_blank" class="btn btn-info" type="button"><i class="bx bx-export font-size-16 align-middle me-2"></i>Export </a>
                        @endif --}}
                            <button id="addBtnMainMaster" class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#canvasMainMaster" role="button" type="button"><i
                                    class="bx bx-plus font-size-16 align-middle me-2"></i>Add WAREHOSE</button>

                            <div class="modal fade" id="canvasMainMaster" data-bs-backdrop="static" tabindex="-1"
                                role="dialog" aria-labelledby="canvasMainMasterLable" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="canvasMainMasterLable">Add WareHouse Master</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>

                                        <div class="modal-body">
                                            <div class="col-md-12 text-center loadingcls" style="display: none;">
                                                <button type="button" class="btn btn-light waves-effect">
                                                    <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i>
                                                    Loading...
                                                </button>
                                            </div>
                                            <form id="formMainMaster" enctype="multipart/form-data"
                                                class="custom-validation" action="{{ route('wearhouse.save') }}"
                                                method="POST">
                                                @csrf
                                                <input type="hidden" name="warehouse_id" id="warehouse_id">

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="q_item_master_name" class="form-label">Name <code
                                                                    class="highlighter-rouge">*</code></label>
                                                            <input type="text" class="form-control" id="name"
                                                                name="name" placeholder="Name" value="" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="q_item_master_app_display_name"
                                                                class="form-label">Short Name </label>
                                                            <input type="text" class="form-control" id="shortname"
                                                                name="shortname" placeholder="Short Name" value="">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="q_item_master_app_display_name"
                                                                class="form-label">FIRST ADDRESS </label>
                                                            <input type="text" class="form-control" id="address_line_1"
                                                                name="address_line_1" placeholder="First Address"
                                                                value="">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="q_item_master_module" class="form-label">SECOND
                                                                EDDRESS <code class="highlighter-rouge">*</code></label>
                                                            <input type="text" class="form-control" id="second_eddress"
                                                                name="second_eddress" onkeypress=""
                                                                placeholder="Second Eddress" value="" required>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="q_item_master_max_module" class="form-label">Pincode
                                                                <code class="highlighter-rouge">*</code></label>
                                                            <input type="number" class="form-control" id="pincode"
                                                                name="pincode" placeholder="00" value="" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="q_item_master_sequence" class="form-label">Area
                                                            </label>
                                                            <input type="text" class="form-control" id="area"
                                                                name="area" placeholder="" value="0" required>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="q_item_master_city" class="form-label">City</label>
                                                    <select class="form-select" aria-label="Disabled select example"
                                                        name="city">
                                                        <option selected>select city</option>
                                                        <option>surat</option>
                                                        <option>ahmdabad</option>
                                                    </select>
                                                </div><br>
                                                <div class="d-flex flex-wrap gap-2">
                                                    <button type="submit"
                                                        class="btn btn-primary waves-effect waves-light">
                                                        Save
                                                    </button>
                                                    <button type="reset" class="btn btn-secondary waves-effect">
                                                        Reset
                                                    </button>
                                                </div>
                                            </form>

                                        </div>
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
                            <table id="datatable" class="table table-striped dt-responsive nowrap w-100">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Name</th>
                                        <th>Address</th>
                                        <th>city</th>
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
    <!-- End Page-co    ntent -->
    @csrf
@endsection('content')


@section('custom-scripts')

    <script src="{{ asset('assets/ckeditor5/build/ckeditor.js') }}"></script>

    <script src="{{ asset('assets/libs/parsleyjs/parsley.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/form-validation.init.js') }}"></script>
    <script src="{{ asset('assets/js/pages/jquery.form.js') }}"></script>
    <script class="text/javascript">
        var ajaxURL = '{{ route('wearhouse.ajax') }}';
        var ajaxURLDetail = '{{ route('wearhouse.detail') }}';
        var csrfToken = $("[name=_token]").val();
        var usersPageLength = getCookie('usersPageLength') !== undefined ? getCookie('usersPageLength') : 10;

        var table = $('#datatable').DataTable({
            "aoColumnDefs": [{
                "bSortable": false,
                "aTargets": []
            }],
            "order": [
                [0, 'desc']
            ],
            "processing": true,
            "serverSide": true,
            "pageLength": usersPageLength,
            "ajax": {
                "url": ajaxURL,
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
                    "mData": "address_line_1"
                },
                {
                    "mData": "city"
                },
                {
                    "mData": "action"
                },
            ]
        });
        $('#datatable').on('lenght.dt', function(e, settings, len) {
            setCookie('usersPageLength', len, 100);
        });

        function reloadTable() {
            table.ajax.reload(null, false);
        }

        $(document).ready(function() {
            $("#s_main_master_id").val($("#main_master_id").val());
            var option = {
                beforeSubmit: showRequest,
                success: showResponse
            };
            $("#formMainMaster").ajaxForm(option);
        });

        function showRequest(FormData, jqForm, option) {
            // generateHierarchyCode($("#main_master_name").val());
            var queryString = $.param(FormData);
            return true;
        }

        function showResponse(reshponsText, statusText, xhr, $form) {
            if (reshponsText['status'] == 1) {
                toastr['success'](reshponsText['msg']);
                reloadTable();
                resetInputForm();
                $("#canvasMainMaster").modal('hide');

            } else if (reshponsText['status'] == 0) {
                toastr['error'](reshponsText['msg']);
            }
        }

        function resetInputForm() {

            $('#formMainMaster').trigger("reset");
            $("#main_master_id").val(0);

        }

        function editView(id) {
            // resetInpitForm();

            $("#canvasMainMaster").modal('show');
            $('#canvasMainMasterLable').html("Edite Main WearHouse" + id);
            $('#formMainMaster').show();
            $(".loadingcls").hide();

            $.ajax({
                type: 'get',
                url: ajaxURLDetail,
                data: {
                    "id": id,
                },
                success: function(resultData) {
                    if (resultData['status'] == 1) {
                        $("#warehouse_id").val(resultData['data']['id']);
                        $("#name").val(resultData['data']['name']);
                        $("#shortname").val(resultData['data']['shortname']);
                        $("#address_line_1").val(resultData['data']['address_line_1']);
                        $("#second_eddress").val(resultData['data']['address_line_2']);
                        $("#pincode").val(resultData['data']['pincode']);
                        $("#area").val(resultData['data']['area']);
                        $("#city").val(resultData['data']['city']);

                        $(".loadingcls").hide();
                        $("#formMainMaster").show();


                    } else {

                        toastr["error"](resultData['msg']);

                    }
                }
            })
        }
    </script>


@endsection
