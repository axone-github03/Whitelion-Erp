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
                    <h4 class="mb-sm-0 font-size-18">Product Tag Master</h4>
                    <div class="page-title-right">
                        @if (isAdminOrCompanyAdmin() == 1)
                        <a href="{{ route('service.warehouse.master.export') }}" target="_blank" class="btn btn-info" type="button"><i class="bx bx-export font-size-16 align-middle me-2"></i>Export </a>
                        @endif
                        <button id="addBtnWareHouseMaster" class="btn btn-primary" type="button"><i class="bx bx-plus font-size-16 align-middle me-2"></i>Add Company</button>
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
                                    <th>Warehouse Name</th>
                                    <th>Short Name</th>
                                    <th>Address</th>
                                    <th>Is Active</th>
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
@include('../service/master/warehousemaster/comman/modal')





<script src="{{ asset('assets/libs/parsleyjs/parsley.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/form-validation.init.js') }}"></script>
<script src="{{ asset('assets/js/pages/jquery.form.js') }}"></script>


<script type="text/javascript">
    var ajaxWarehouseMasterDataURL = '{{ route("service.warehouse.master.ajax") }}';
    var ajaxWarehouseMasterDetailURL = '{{ route("service.warehouse.master.detail") }}';
    var ajaxWarehouseMasterDeleteURL = '{{ route("service.warehouse.master.delete") }}';

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
            "url": ajaxWarehouseMasterDataURL,
            "type": "POST",
            "data": {
                "_token": csrfToken,
            }
        },
        "aoColumns": [{
                "mData": "id"
            },
            {
                "mData": "warehousename"
            },
            {
                "mData": "shortname"
            },
            {
                "mData": "address1"
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

    $(document).ready(function() {
        var options = {
            beforeSubmit: showRequest, // pre-submit callback
            success: showResponse // post-submit callback

            // other available options:
            //url:       url         // override for form's 'action' attribute
            //type:      type        // 'get' or 'post', override for form's 'method' attribute
            //dataType:  null        // 'xml', 'script', or 'json' (expected server response type)
            //clearForm: true        // clear all form fields after successful submit
            //resetForm: true        // reset the form after successful submit

            // $.ajax options can be used here too, for example:
            //timeout:   3000
        };

        // bind form using 'ajaxForm'
        $('#formWarehouse').ajaxForm(options);

    });


    function showRequest(formData, jqForm, options) {
        // formData is an array; here we use $.param to convert it to a string to display it
        // but the form plugin does this for you automatically when it submits the data
        var queryString = $.param(formData);

        // jqForm is a jQuery object encapsulating the form element.  To access the
        // DOM element for the form do this:
        // var formElement = jqForm[0];

        // alert('About to submit: \n\n' + queryString);

        // here we could return false to prevent the form from being submitted;
        // returning anything other than false will allow the form submit to continue
        return true;
    }

    // post-submit callback
    function showResponse(responseText, statusText, xhr, $form) {


        if (responseText['status'] == 1) {
            reloadTable();
            $('#modalWarehouse').modal("hide");
            toastr["success"](responseText['msg']);

        } else if (responseText['status'] == 0) {
            toastr["error"](responseText['msg']);
        }

    }

    function resetInputForm() {
        $('#formWarehouse').trigger("reset");
        $("#warehouse_id").val(0);
        $("#warehouse_country_id").select2("val", "");
        $("#warehouse_state_id").select2("val", "");
        $("#warehouse_city_id").select2("val", "");
        $("#warehouse_status").select2("val", "1");

    }

    function editView(id) {

        resetInputForm();
        $("#modalWarehouseLabel").html("Edit Warehouse #" + id);
        $(".loadingcls").show();
        $("#formwerehouse_field").hide();
        $('#modalWarehouse').modal("show");

        $.ajax({
            type: 'GET',
            url: ajaxWarehouseMasterDetailURL + "?id=" + id,
            success: function(resultData) {
                if (resultData['status'] == 1) {


                    $("#warehouse_id").val(resultData['data']['id']);
                    $("#warehouse_name").val(resultData['data']['warehousename']);
                    $("#warehouse_address_line1").val(resultData['data']['address1']);
                    $("#warehouse_address_line2").val(resultData['data']['address2']);


                    if (typeof resultData['data']['country'] !== "undefined") {
                        $("#warehouse_country_id").empty().trigger('change');
                        var newOption = new Option(resultData['data']['country_name'], resultData['data']['country'], false, false);
                        $('#warehouse_country_id').append(newOption).trigger('change');
                    }


                    if (typeof resultData['data']['state'] !== "undefined") {
                        $("#warehouse_state_id").empty().trigger('change');
                        var newOption = new Option(resultData['data']['state_name'], resultData['data']['state'], false, false);
                        $('#warehouse_state_id').append(newOption).trigger('change');
                    }

                    if (typeof resultData['data']['city'] !== "undefined") {
                        $("#warehouse_city_id").empty().trigger('change');
                        var newOption = new Option(resultData['data']['city_name'], resultData['data']['city'], false, false);
                        $('#warehouse_city_id').append(newOption).trigger('change');
                    }

                    $("#warehouse_pincode").val(resultData['data']['pincode']);
                    $("#warehouse_remark").val(resultData['data']['remark']);
                    $("#warehouse_status").select2("val", "" + resultData['data']['isactive'] + "");

                    $(".loadingcls").hide();
                    $("#formMainMaster").show();

                } else {

                    toastr["error"](resultData['msg']);

                }
                $(".loadingcls").hide();
                $("#formwerehouse_field").show();

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
                        url: ajaxWarehouseMasterDeleteURL + "?id=" + id,
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
</script>
@endsection