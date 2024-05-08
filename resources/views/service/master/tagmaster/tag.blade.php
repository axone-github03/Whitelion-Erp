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
                    <h4 class="mb-sm-0 font-size-18">Item Tag Master</h4>
                    <div class="page-title-right">
                        <button id="addBtnMainMaster" class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#canvasMainMaster" aria-controls="canvasMainMaster"><i class="bx bx-plus font-size-16 align-middle me-2"></i>Add Item Tag</button>
                        <div class="offcanvas offcanvas-end" tabindex="-1" id="canvasMainMaster" aria-labelledby="canvasMainMasterLable">
                            <div class="offcanvas-header">
                                <h5 id="canvasMainMasterLable">Item Tag Master</h5>
                                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                            </div>
                            <div class="offcanvas-body">
                                <div class="col-md-12 text-center loadingcls">
                                    <button type="button" class="btn btn-light waves-effect">
                                        <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading
                                    </button>
                                </div>
                                <form id="formMainMaster" class="custom-validation" action="{{route('service.product.tag.master.save')}}" method="POST">
                                    @csrf
                                    <input type="hidden" name="tag_master_id" id="tag_master_id">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="tag_master_name" class="form-label">Tag Name <code class="highlighter-rouge">*</code></label>
                                                <input type="text" class="form-control" id="tag_master_name" name="tag_master_name" placeholder="Name" value="" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="tag_master_code" class="form-label">Short Name </label>
                                                <input type="text" class="form-control" id="tag_master_code" name="tag_master_code" placeholder="" value="" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="tag_master_remark" class="form-label">Remark </label>
                                                <textarea class="form-control" id="tag_master_remark" name="tag_master_remark" rows="2" placeholder="Enter Remark"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="tag_master_status" class="form-label">Is Active </label>

                                                <select id="tag_master_status" name="tag_master_status" class="form-control select2-apply">
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
                                    <th>Item Tag Name</th>
                                    <th>Short Name</th>
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


<script src="{{ asset('assets/libs/parsleyjs/parsley.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/form-validation.init.js') }}"></script>
<script src="{{ asset('assets/js/pages/jquery.form.js') }}"></script>
<script type="text/javascript">
    var ajaxProductTagMasterDataURL = '{{route("service.product.tag.master.ajax")}}';
    var ajaxProductTagMasterDetailURL = '{{route("service.product.tag.master.detail")}}';
    var ajaxProductTagMasterDeleteURL = '{{route("service.product.tag.master.delete")}}';

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
            "url": ajaxProductTagMasterDataURL,
            "type": "POST",
            "data": {
                "_token": csrfToken,
            }
        },
        "aoColumns": [{
                "mData": "id"
            },
            {
                "mData": "tagname"
            },
            {
                "mData": "shortname"
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


    $("#tag_master_status").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#canvasMainMaster")
    });

    function generateHierarchyCode(dInput) {

        dInput = dInput.replace(/[_\W]+/g, "_")
        dInput = dInput.toUpperCase();
        $("#tag_master_code").val(dInput)
    }

    $("#tag_master_name").keyup(function() {
        generateHierarchyCode(this.value);
    });

    $("#tag_master_name").change(function() {
        generateHierarchyCode(this.value);
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
        $('#formMainMaster').ajaxForm(options);

    });

    function showRequest(formData, jqForm, options) {
        generateHierarchyCode($("#tag_master_name").val());
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
            $("#canvasMainMaster").offcanvas('hide');
            toastr["success"](responseText['msg']);

        } else if (responseText['status'] == 0) {
            toastr["error"](responseText['msg']);

        }

    }


    $("#addBtnMainMaster").click(function() {
        $("#canvasMainMasterLable").html("Add Item Tag");
        $("#formMainMaster").show();
        $(".loadingcls").hide();
        resetInputForm();
    });


    function resetInputForm() {

        $('#formMainMaster').trigger("reset");
        $("#tag_master_id").val(0);
        $("#tag_master_status").select2("val", "1");

    }

    function editView(id) {

        resetInputForm();

        $("#canvasMainMaster").offcanvas('show');
        $("#canvasMainMasterLable").html("Edit Tag #" + id);
        $("#formMainMaster").hide();
        $(".loadingcls").show();

        $.ajax({
            type: 'GET',
            url: ajaxProductTagMasterDetailURL + "?id=" + id,
            success: function(resultData) {
                if (resultData['status'] == 1) {
                    $("#tag_master_id").val(resultData['data']['id']);
                    $("#tag_master_name").val(resultData['data']['tagname']);
                    $("#tag_master_code").val(resultData['data']['shortname']);
                    $("#tag_master_remark").val(resultData['data']['remark']);
                    $("#tag_master_status").select2("val", "" + resultData['data']['isactive'] + "");
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
                        url: ajaxProductTagMasterDeleteURL + "?id=" + id,
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