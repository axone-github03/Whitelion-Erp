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
                    <h4 class="mb-sm-0 font-size-18" onclick="companyClick()">Application Setting</h4>
                    <div class="page-title-right">

                        <div class="offcanvas offcanvas-end" tabindex="-1" id="canvasMainMaster" aria-labelledby="canvasMainMasterLable">
                            <div class="offcanvas-header">
                                <h5 id="canvasMainMasterLable">App Setting</h5>
                                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                            </div>
                            <div class="offcanvas-body">
                                <div class="col-md-12 text-center loadingcls">
                                    <button type="button" class="btn btn-light waves-effect">
                                        <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading
                                    </button>
                                </div>
                                <form id="formMainMaster" class="custom-validation" action="{{route('quot.app.master.save')}}" method="POST">
                                    @csrf
                                    <input type="hidden" name="q_app_master_id" id="q_app_master_id">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3 AppSource">
                                                <label for="q_app_master_source" class="form-label">Source </label>

                                                <select id="q_app_master_source" name="q_app_master_source" class="form-control select">
                                                    <option value="ANDROID">Android</option>
                                                    <option value="IPHONE">Iphone</option>
                                                    <option value="WEB">Web</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="q_app_master_version" class="form-label">Version </label>
                                                <input type="text" class="form-control" id="q_app_master_version" name="q_app_master_version" placeholder="Enter Version" value="">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="q_app_master_remark" class="form-label">Remark </label>
                                                <textarea class="form-control" id="q_app_master_remark" name="q_app_master_remark" rows="2" placeholder="Enter Remark"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="q_app_master_isactive" class="form-label">Is Active </label>

                                                <select id="q_app_master_isactive" name="q_app_master_isactive" class="form-control select2-apply">
                                                    <option value="1">Active</option>
                                                    <option value="0">Inactive</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">
                                            Save
                                        </button>
                                        <button type="reset" class="btn btn-secondary waves-effect">
                                            Reset
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="offcanvas offcanvas-end" tabindex="-1" id="canvasYearMaster" aria-labelledby="canvasYearMasterLable">
                            <div class="offcanvas-header">
                                <h5 id="canvasYearMasterLable">Year Master</h5>
                                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                            </div>
                            <div class="offcanvas-body">
                                <div class="col-md-12 text-center loadingcls">
                                    <button type="button" class="btn btn-light waves-effect">
                                        <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading
                                    </button>
                                </div>
                                <form id="formYearMaster" class="custom-validation" action="{{route('financial.year.save')}}" method="POST">
                                    @csrf
                                    <input type="hidden" name="q_year_master_id" id="q_year_master_id" value="0">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="q_year_master_name" class="form-label">Financial Year </label>
                                                <input type="text" class="form-control" id="q_year_master_name" name="q_year_master_name" placeholder="Enter Financial Year" value="">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">
                                            Save
                                        </button>
                                        <button type="reset" class="btn btn-secondary waves-effect">
                                            Reset
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="offcanvas offcanvas-end" tabindex="-1" id="canvasQuotTypeMaster" aria-labelledby="canvasQuotTypeMasterLable">
                            <div class="offcanvas-header">
                                <h5 id="canvasQuotTypeMasterLable">Quot Type Master</h5>
                                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                            </div>
                            <div class="offcanvas-body">
                                <div class="col-md-12 text-center loadingcls">
                                    <button type="button" class="btn btn-light waves-effect">
                                        <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading
                                    </button>
                                </div>
                                <form id="formQuotTypeMaster" class="custom-validation" action="{{route('quot.type.save')}}" method="POST">
                                    @csrf
                                    <input type="hidden" name="q_quottype_master_id" id="q_quottype_master_id">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="q_quottype_master_name" class="form-label">Financial Year </label>
                                                <input type="text" class="form-control" id="q_quottype_master_name" name="q_quottype_master_name" placeholder="Enter Quotation Type Year" value="">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">
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
        <!-- end page title -->
        <div class="row">
            <div class="col-6">
                <div class="card">
                    <div class="card-header d-flex bd-highlight align-items-center" style="background: antiquewhite;border-top-left-radius: 5px;border-top-right-radius: 5px;">
                        <h5 class="card-title m-0 me-auto bd-highlight ml-1">App Version List</h5>
                        <a href="javascript:void(0)" target="_blank" type="button" id="addBtnMainMaster" data-bs-toggle="offcanvas" data-bs-target="#canvasMainMaster" aria-controls="canvasMainMaster"><i class="bx bx-plus font-size-16 align-middle me-2"></i>Add Version </a>
                    </div>
                    <div class="card-body">
                        <table id="datatable" class="table table-striped dt-responsive  nowrap w-100">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Source</th>
                                    <th>Version</th>
                                    <th>Is Active</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card">
                    <div class="card-header d-flex bd-highlight align-items-center" style="background: antiquewhite;border-top-left-radius: 5px;border-top-right-radius: 5px;">
                        <h5 class="card-title m-0 me-auto bd-highlight ml-1">Send Notification</h5>
                    </div>
                    <div class="card-body">
                        <form id="formNotification" class="custom-validation" action="{{route('quot.app.send.notification')}}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="q_app_noti_token" class="form-label">Token </label>
                                        <input type="text" class="form-control" id="q_app_noti_token" name="q_app_noti_token" placeholder="Enter Token" value="">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="q_app_noti_title" class="form-label">Title </label>
                                        <input type="text" class="form-control" id="q_app_noti_title" name="q_app_noti_title" placeholder="Enter Title" value="">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="q_app_noti_message" class="form-label">Message </label>
                                        <textarea class="form-control" id="q_app_noti_message" name="q_app_noti_message" rows="2" placeholder="Enter Remark"></textarea>
                                    </div>
                                </div>
                            </div>
                            <!-- <div class="row d-none">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <input type="file" name="q_app_noti_image" id="q_app_noti_image" accept="image/*" style="display:none" />
                                        <div class="row" id="div_noti_image">
                                            <img id="noti_img_Preview" src="item_image/placeholder.png" alt="" class="img-thumbnail" style="width: 20%;">
                                        </div>
                                    </div>
                                </div>
                            </div> -->
                            <div class="d-flex flex-wrap gap-2">
                                <button type="submit" class="btn btn-primary waves-effect waves-light">
                                    Save
                                </button>
                                <button type="reset" id="send_noti_form_reset" class="btn btn-secondary waves-effect">
                                    Reset
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div> <!-- end row -->
        <div class="row">

            <div class="col-6">
                <div class="card">
                    <div class="card-header d-flex bd-highlight align-items-center" style="background: antiquewhite;border-top-left-radius: 5px;border-top-right-radius: 5px;">
                        <h5 class="card-title m-0 me-auto bd-highlight ml-1">financial Year List</h5>
                        <a href="javascript:void(0)" target="_blank" type="button" id="addBtnYearMaster" data-bs-toggle="offcanvas" data-bs-target="#canvasYearMaster" aria-controls="canvasYearMaster"><i class="bx bx-plus font-size-16 align-middle me-2"></i>Add Financial Year </a>
                    </div>
                    <div class="card-body">
                        <table id="yearTable" class="table table-striped dt-responsive  nowrap w-100">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Name</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card">
                    <div class="card-header d-flex bd-highlight align-items-center" style="background: antiquewhite;border-top-left-radius: 5px;border-top-right-radius: 5px;">
                        <h5 class="card-title m-0 me-auto bd-highlight ml-1">Quotation Type List</h5>
                        <a href="javascript:void(0)" target="_blank" type="button" id="addBtnQuotTypeMaster" data-bs-toggle="offcanvas" data-bs-target="#canvasQuotTypeMaster" aria-controls="canvasQuotTypeMaster"><i class="bx bx-plus font-size-16 align-middle me-2"></i>Add Quotation Type </a>
                    </div>
                    <div class="card-body">
                        <table id="quottypeTable" class="table table-striped dt-responsive  nowrap w-100">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Name</th>
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


        <div class="row" id="whatsappmodel">
            <div class="col-6">
                <div class="card">
                    <div class="card-header d-flex bd-highlight align-items-center" style="background: antiquewhite;border-top-left-radius: 5px;border-top-right-radius: 5px;">
                        <h5 class="card-title m-0 me-auto bd-highlight ml-1">Send Whatsapp Message</h5>
                    </div>
                    <div class="card-body">
                        <form id="formSendWhatsappMessage" class="custom-validation" action="{{route('send.whatsapp.template.message')}}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="q_whatsapp_massage_mobileno" class="form-label">Mobile No</label>
                                        <input type="text" class="form-control" id="q_whatsapp_massage_mobileno" name="q_whatsapp_massage_mobileno" placeholder="Enter Mobile No" value="" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="q_whatsapp_massage_template" class="form-label">Template Name </label>
                                        <select id="q_whatsapp_massage_template" name="q_whatsapp_massage_template" class="form-control select2-ajax select2-multiple" required>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="q_broadcast_name" class="form-label">Name </label>
                                        <input type="text" class="form-control" id="q_broadcast_name" name="q_broadcast_name" rows="2" placeholder="Enter Name">
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="submit" class="btn btn-primary waves-effect waves-light">
                                    Save
                                </button>
                                <button type="reset" id="send_noti_form_reset" class="btn btn-secondary waves-effect">
                                    Reset
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @if(isAdminOrCompanyAdmin()==1)
            <div class="col-6">
                <div class="card">
                    <div class="card-header d-flex bd-highlight align-items-center" style="background: antiquewhite;border-top-left-radius: 5px;border-top-right-radius: 5px;">
                        <h5 class="card-title m-0 me-auto bd-highlight ml-1">Excel Download</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="input-daterange input-group" id="div_start_end_datepicker" data-date-format="dd-mm-yyyy" data-date-autoclose="true" data-provide="datepicker" data-date-container='#div_start_end_datepicker'>
                                    <input type="text" class="form-control" name="start_date" id="start_date" value="@php echo date('01-m-Y'); @endphp" placeholder="Start Date" />
                                    <input type="text" class="form-control" name="end_date" id="end_date" placeholder="End Date" value="@php echo date('t-m-Y'); @endphp" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <a href="javascript:void(0)" onclick="exportData('{{route('won.deal.export')}}')" class="btn btn-info" type="button"><i class="bx bx-export font-size-16 align-middle me-2"></i>Won Deal Export</a>
                            </div>

                            <div class="col-md-6 mb-3">
                                <a href="javascript:void(0)" onclick="exportData('{{route('lost.lead.and.deal.export')}}')" class="btn btn-info" type="button"><i class="bx bx-export font-size-16 align-middle me-2"></i>Lost Lead & Deal Export</a>
                            </div>

                            <div class="col-md-6 mb-3">
                                <a href="javascript:void(0)" onclick="exportData('{{route('prediction.deal.list.export')}}')" class="btn btn-info" type="button"><i class="bx bx-export font-size-16 align-middle me-2"></i>Pridiction Export</a>
                            </div>

                            <div class="col-md-6 mb-3">
                                <a href="javascript:void(0)" onclick="exportData('{{route('channelpartner.list.export')}}')" class="btn btn-info" type="button"><i class="bx bx-export font-size-16 align-middle me-2"></i>Channel Partner Export</a>
                            </div>

                            <div class="col-md-6 mb-3">
                                <a href="javascript:void(0)" onclick="exportData('{{route('architect.list.export')}}')" class="btn btn-info" type="button"><i class="bx bx-export font-size-16 align-middle me-2"></i>Architect Export</a>
                            </div>

                            <div class="col-md-6 mb-3">
                                <a href="javascript:void(0)" onclick="exportData('{{route('electrician.list.export')}}')" class="btn btn-info" type="button"><i class="bx bx-export font-size-16 align-middle me-2"></i>Electrician Export</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
    <!-- container-fluid -->
</div>
<!-- End Page-content -->
@csrf
@endsection('content')

@section('custom-scripts')
@include('../quotation/master/appsetting/comman/modal_add_master_data_excel')

<script src="{{ asset('assets/libs/moment/min/moment.min.js') }}"></script>
<script src="{{ asset('assets/libs/parsleyjs/parsley.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/form-validation.init.js') }}"></script>
<script src="{{ asset('assets/js/pages/jquery.form.js') }}"></script>
<script src="{{ asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>

<script type="text/javascript">
    var ajaxAppSettingMasterDataURL = '{{route("quot.app.master.ajax")}}';
    var ajaxAppSettingMasterDetailURL = '{{route("quot.app.master.detail")}}';
    var ajaxAppSettingMasterDeleteURL = '{{route("quot.app.master.delete")}}';
    var ImageProductPlaceHolder = 'item_image/placeholder.png';


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
            "url": ajaxAppSettingMasterDataURL,
            "type": "POST",
            "data": {
                "_token": csrfToken,
            }
        },
        "aoColumns": [{
                "mData": "id"
            },
            {
                "mData": "source"
            },
            {
                "mData": "version"
            },
            {
                "mData": "isactive"
            },
            {
                "mData": "action"
            }
        ]
    });

    function reloadTable() {
        table.ajax.reload(null, false);
    }

    $('#noti_img_Preview').click(function() {
        $('#q_app_noti_image').trigger('click');
    });


    $(document).ready(() => {
        $('#q_app_noti_image').change(function() {
            const file = this.files[0];
            console.log(file);
            if (file) {
                let reader = new FileReader();
                reader.onload = function(event) {
                    console.log(event.target.result);
                    $('#noti_img_Preview').attr('src', event.target.result);
                }
                reader.readAsDataURL(file);
            }
        });
    });

    $('#datatable').on('length.dt', function(e, settings, len) {
        setCookie('mainMasterPageLength', len, 100);
    });


    $("#q_app_master_isactive").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#canvasMainMaster")
    });

    $(document).ready(function() {
        var options = {
            beforeSubmit: showRequest, // pre-submit callback
            success: showResponse // post-submit callback
        };

        $('#formMainMaster').ajaxForm(options);
        $('#formExcelDataAdd').ajaxForm(options);
        $('#formNotification').ajaxForm(options);
        $('#formYearMaster').ajaxForm(options);
        $('#formQuotTypeMaster').ajaxForm(options);
        $('#formSendWhatsappMessage').ajaxForm(options);
    });

    function showRequest(formData, jqForm, options) {
        var queryString = $.param(formData);
        return true;
    }

    // post-submit callback
    function showResponse(responseText, statusText, xhr, $form) {

        if (responseText['status'] == 1) {
            toastr["success"](responseText['msg']);
            reloadTable();
            resetInputFormNotification();
            yearTableReload();
            quotTypeTableReload();
            $("#canvasYearMaster").offcanvas('hide');
            $("#canvasQuotTypeMaster").offcanvas('hide');
        } else if (responseText['status'] == 0) {
            toastr["error"](responseText['msg']);
            resetInputFormNotification();
        }
    }


    $("#addBtnMainMaster").click(function() {
        $("#canvasMainMasterLable").html("Add App Version");
        $("#formMainMaster").show();
        $(".loadingcls").hide();
        resetInputForm();
    });


    $('#send_noti_form_reset').click(function() {
        resetInputFormNotification();
    });


    function resetInputFormNotification() {
        $('#formNotification').trigger("reset");
    }

    function resetInputForm() {
        $('#formMainMaster').trigger("reset");
        $("#q_app_master_id").val(0);
        $("#q_app_master_version").val('');
        $("#q_app_master_remark").val('');
        $('.AppSource option:eq(0)').prop('selected', true)
        $("#q_app_master_isactive").select2("val", "1");
    }



    function editView(id) {

        resetInputForm();

        $("#canvasMainMaster").offcanvas('show');
        $("#canvasMainMasterLable").html("Edit App Version #" + id);
        $("#formMainMaster").hide();
        $(".loadingcls").show();

        $.ajax({
            type: 'GET',
            url: ajaxAppSettingMasterDetailURL + "?id=" + id,
            success: function(resultData) {
                if (resultData['status'] == 1) {

                    $("#q_app_master_id").val(resultData['data']['id']);
                    $("#q_app_master_version").val(resultData['data']['version']);
                    $("#q_app_master_remark").val(resultData['data']['remark']);

                    if (resultData['data']['source'] == 'ANDROID') {
                        $('.AppSource option:eq(0)').prop('selected', true)
                    } else {
                        $('.AppSource option:eq(1)').prop('selected', true)
                    }
                    $("#q_app_master_isactive").select2("val", "" + resultData['data']['isactive'] + "");

                    $(".loadingcls").hide();
                    $("#formMainMaster").show();

                } else {

                    toastr["error"](resultData['msg']);

                }

            }
        });

    }

    function deleteWarning(id) {
        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: !0,
            confirmButtonText: "Yes, delete it!",
            cancelButtonText: "No, cancel!",
            confirmButtonClass: "btn btn-success mt-2",
            cancelButtonClass: "btn btn-danger ms-2 mt-2",
            loaderHtml: "<i class='bx bx-hourglass bx-spin font-size-16 align-middle me-2'></i> Loading",
            customClass: {
                confirmButton: 'btn btn-primary btn-lg',
                cancelButton: 'btn btn-danger btn-lg',
                loader: 'custom-loader'
            },
            buttonsStyling: !1,
            preConfirm: function(n) {
                return new Promise(function(t, e) {
                    Swal.showLoading()
                    $.ajax({
                        type: 'GET',
                        url: ajaxAppSettingMasterDeleteURL + "?id=" + id,
                        success: function(resultData) {
                            if (resultData['status'] == 1) {

                                reloadTable();
                                t()
                            }
                        }
                    });
                })
            },
        }).then(function(t) {
            if (t.value === true) {
                Swal.fire({
                    title: "Deleted!",
                    text: "Your record has been deleted.",
                    icon: "success"
                });
            }
        });
    }
    // TODO--------------- YEAR MASTER WORK START ---------------

    var ajaxFinancialYearDataURL = '{{route("financial.year.ajax")}}';
    var ajaxYearMasterDetailURL = '{{route("financial.year.detail")}}';
    var ajaxFinancialYearDeleteURL = '{{route("financial.year.delete")}}';
    var csrfToken = $("[name=_token").val();
    var yearTable = $('#yearTable').DataTable({
        "aoColumnDefs": [{
            "bSortable": false,
            "aTargets": [0]
        }],
        "order": [
            [0, 'desc']
        ],
        "processing": true,
        "pagingType": "full_numbers",
        "serverSide": true,
        "pageLength": 10,
        "ajax": {
            "url": ajaxFinancialYearDataURL,
            "type": "POST",
            "data": {
                "_token": csrfToken
            }
        },
        "aoColumns": [{
                "mData": "id"
            },
            {
                "mData": "name"
            },
            {
                "mData": "action"
            }
        ]
    });

    function yearTableReload(userId) {
        yearTable.ajax.reload(null, false);
    }

    $("#addBtnYearMaster").click(function() {
        $("#canvasYearMasterLable").html("Add Financial Year");
        $("#formYearMaster").show();
        $(".loadingcls").hide();
        resetYearForm();
    });

    function resetYearForm() {
        $('#formYearMaster').trigger("reset");
        $("#q_year_master_id").val(0);
        $("#q_year_master_name").val('');
    }


    function editYearView(id) {

        resetYearForm();

        $("#canvasYearMaster").offcanvas('show');
        $("#canvasYearMasterLable").html("Edit Financial Year #" + id);
        $("#formYearMaster").hide();
        $(".loadingcls").show();

        $.ajax({
            type: 'GET',
            url: ajaxYearMasterDetailURL + "?id=" + id,
            success: function(resultData) {
                if (resultData['status'] == 1) {

                    $("#q_year_master_id").val(resultData['data']['id']);
                    $("#q_year_master_name").val(resultData['data']['name']);


                    $(".loadingcls").hide();
                    $("#formYearMaster").show();

                } else {

                    toastr["error"](resultData['msg']);

                }

            }
        });

    }

    function deleteFinWarning(id) {
        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: !0,
            confirmButtonText: "Yes, delete it!",
            cancelButtonText: "No, cancel!",
            confirmButtonClass: "btn btn-success mt-2",
            cancelButtonClass: "btn btn-danger ms-2 mt-2",
            loaderHtml: "<i class='bx bx-hourglass bx-spin font-size-16 align-middle me-2'></i> Loading",
            customClass: {
                confirmButton: 'btn btn-primary btn-lg',
                cancelButton: 'btn btn-danger btn-lg',
                loader: 'custom-loader'
            },
            buttonsStyling: !1,
            preConfirm: function(n) {
                return new Promise(function(t, e) {
                    Swal.showLoading()
                    $.ajax({
                        type: 'GET',
                        url: ajaxFinancialYearDeleteURL + "?id=" + id,
                        success: function(resultData) {
                            if (resultData['status'] == 1) {

                                yearTableReload();
                                t()
                            }
                        }
                    });
                })
            },
        }).then(function(t) {
            if (t.value === true) {
                Swal.fire({
                    title: "Deleted!",
                    text: "Your record has been deleted.",
                    icon: "success"
                });
            }
        });
    }
    // TODO--------------- YEAR MASTER WORK END ---------------


    // TODO--------------- QUOT TYPE MASTER WORK START ---------------
    var ajaxQuotTypeDataURL = '{{route("quot.type.ajax")}}';
    var ajaxQuotTypeDetailURL = '{{route("quot.type.detail")}}';
    var ajaxQuotTypeDeleteURL = '{{route("quot.type.delete")}}';
    var csrfToken = $("[name=_token").val();
    var quotTypeTable = $('#quottypeTable').DataTable({
        "aoColumnDefs": [{
            "bSortable": false,
            "aTargets": [0]
        }],
        "order": [
            [0, 'desc']
        ],
        "processing": true,
        "pagingType": "full_numbers",
        "serverSide": true,
        "pageLength": 10,
        "ajax": {
            "url": ajaxQuotTypeDataURL,
            "type": "POST",
            "data": {
                "_token": csrfToken
            }
        },
        "aoColumns": [{
                "mData": "id"
            },
            {
                "mData": "name"
            },
            {
                "mData": "action"
            }
        ]
    });

    function quotTypeTableReload(userId) {
        quotTypeTable.ajax.reload(null, false);
    }

    $("#addBtnQuotTypeMaster").click(function() {
        $("#canvasQuotTypeMasterLable").html("Add Quot Type");
        $("#formQuotTypeMaster").show();
        $(".loadingcls").hide();
        resetYearForm();
    });

    function resetYearForm() {
        $('#formQuotTypeMaster').trigger("reset");
        $("#q_quottype_master_id").val(0);
    }

    function editQuotTypeView(id) {

        resetYearForm();

        $("#canvasQuotTypeMaster").offcanvas('show');
        $("#canvasQuotTypeMasterLable").html("Edit Quot Type #" + id);
        $("#formQuotTypeMaster").hide();
        $(".loadingcls").show();

        $.ajax({
            type: 'GET',
            url: ajaxQuotTypeDetailURL + "?id=" + id,
            success: function(resultData) {
                if (resultData['status'] == 1) {

                    $("#q_quottype_master_id").val(resultData['data']['id']);
                    $("#q_quottype_master_name").val(resultData['data']['name']);


                    $(".loadingcls").hide();
                    $("#formQuotTypeMaster").show();

                } else {

                    toastr["error"](resultData['msg']);

                }

            }
        });

    }

    function deleteQuotTypeWarning(id) {
        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: !0,
            confirmButtonText: "Yes, delete it!",
            cancelButtonText: "No, cancel!",
            confirmButtonClass: "btn btn-success mt-2",
            cancelButtonClass: "btn btn-danger ms-2 mt-2",
            loaderHtml: "<i class='bx bx-hourglass bx-spin font-size-16 align-middle me-2'></i> Loading",
            customClass: {
                confirmButton: 'btn btn-primary btn-lg',
                cancelButton: 'btn btn-danger btn-lg',
                loader: 'custom-loader'
            },
            buttonsStyling: !1,
            preConfirm: function(n) {
                return new Promise(function(t, e) {
                    Swal.showLoading()
                    $.ajax({
                        type: 'GET',
                        url: ajaxQuotTypeDeleteURL + "?id=" + id,
                        success: function(resultData) {
                            if (resultData['status'] == 1) {

                                quotTypeTableReload();
                                t()
                            }
                        }
                    });
                })
            },
        }).then(function(t) {
            if (t.value === true) {
                Swal.fire({
                    title: "Deleted!",
                    text: "Your record has been deleted.",
                    icon: "success"
                });
            }
        });
    }
    // TODO--------------- YEAR MASTER WORK END ---------------

    // TODO--------------- WHATSAPP MESSAGE SEND WORK START ---------------

    var ajaxWhatsappTemplateDataURL = '{{route("search.whatsapp.template")}}';
    

    $.ajax({
        url: ajaxWhatsappTemplateDataURL,
        cache: false,
        success: function(data){
			$.each(data.results, function(key, value) {
					$("#q_whatsapp_massage_template").append(`<option value="${value['text']}" id="${value['text']}">${value['text']}</option>`);
			});
            $("#q_whatsapp_massage_template").select2({});
        }
    })

    function exportData(route) {
        var startDate = $('#start_date').val();
        var endDate = $('#end_date').val();

        // Construct the URL with input values
        var url = route + '?start_date=' + startDate + '&end_date=' + endDate;

        // Open the URL in a new tab
        window.open(url, '_blank');
    }

    // TODO--------------- Whatsapp MESSAGE SEND WORK END ---------------
</script>
@endsection