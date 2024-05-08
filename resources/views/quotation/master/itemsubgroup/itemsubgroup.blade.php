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
                    <h4 class="mb-sm-0 font-size-18">Item SubGroup Master</h4>
                    <div class="page-title-right">
                        @if(isAdminOrCompanyAdmin()==1)
                        <a href="{{route('quot.itemsubgroup.export')}}" target="_blank" class="btn btn-info" type="button"><i class="bx bx-export font-size-16 align-middle me-2"></i>Export </a>
                        @endif
                        <button id="addBtnMainMaster" class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#canvasMainMaster" aria-controls="canvasMainMaster"><i class="bx bx-plus font-size-16 align-middle me-2"></i>Add SubGroup</button>
                        <div class="offcanvas offcanvas-end" tabindex="-1" id="canvasMainMaster" aria-labelledby="canvasMainMasterLable">
                            <div class="offcanvas-header">
                                <h5 id="canvasMainMasterLable">Item SubGroup Master</h5>
                                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                            </div>
                            <div class="offcanvas-body">
                                <div class="col-md-12 text-center loadingcls">
                                    <button type="button" class="btn btn-light waves-effect">
                                        <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading...
                                    </button>
                                </div>
                                <form id="formMainMaster" class="custom-validation" action="{{route('quot.itemsubgroup.master.save')}}" method="POST">
                                    @csrf
                                    <input type="hidden" name="q_subgroup_master_id" id="q_subgroup_master_id">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="q_subgroup_master_name" class="form-label">SubGroup Name <code class="highlighter-rouge">*</code></label>
                                                <input type="text" class="form-control" id="q_subgroup_master_name" name="q_subgroup_master_name" placeholder="Name" value="" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="q_subgroup_master_code" class="form-label">Short Name </label>
                                                <input type="text" class="form-control" id="q_subgroup_master_code" name="q_subgroup_master_code" placeholder="" value="" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                            <label for="q_subgroup_master_company_id" class="form-label">Company <code class="highlighter-rouge">*</code></label>
                                            <select class="form-control select2-ajax select2-multiple" multiple="multiple" id="q_subgroup_master_company_id" name="q_subgroup_master_company_id[]" required>
                                            </select>
                                            <div class="invalid-feedback">
                                                Please select Company.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                            <label for="q_subgroup_master_group_id" class="form-label">ItemGroup </label>
                                            <select class="form-control select2-ajax" id="q_subgroup_master_group_id" name="q_subgroup_master_group_id">
                                            </select>
                                            <div class="invalid-feedback">
                                                Please select ItemGroup.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="q_subgroup_master_default_dis" class="form-label">Default Disc.</label>
                                                <input type="number" step="0.01" class="form-control" id="q_subgroup_master_default_dis" name="q_subgroup_master_default_dis" placeholder="Discount" value="00.00" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="q_subgroup_master_user_dis" class="form-label">User Disc.</label>
                                                <input type="number" step="0.01" class="form-control" id="q_subgroup_master_user_dis" name="q_subgroup_master_user_dis" placeholder="Discount" value="00.00" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="q_subgroup_master_manager_disc" class="form-label">Manager Disc.</label>
                                                <input type="number" step="0.01" class="form-control" id="q_subgroup_master_manager_disc" name="q_subgroup_master_manager_disc" placeholder="Discount" value="00.00" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                <label for="q_subgroup_master_manager_ids" class="form-label">Manager <code class="highlighter-rouge">*</code></label>
                                                <select class="form-control select2-ajax" id="q_subgroup_master_manager_ids" name="q_subgroup_master_manager_ids[]" required multiple></select>
                                                <div class="invalid-feedback">
                                                    Please select Manager.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="q_subgroup_master_channel_partner_disc" class="form-label">Channel Partner Disc.</label>
                                                <input type="number" step="0.01" class="form-control" id="q_subgroup_master_channel_partner_disc" name="q_subgroup_master_channel_partner_disc" placeholder="Discount" value="00.00" required>
                                            </div>
                                            
                                        </div>
                                        <div class="col-md-6 d-none">
                                            <div class="mb-3">
                                                <label for="q_subgroup_master_admin_dis" class="form-label">Company Admin Disc.</label>
                                                <input type="number" step="0.01" class="form-control" id="q_subgroup_master_admin_dis" name="q_subgroup_master_admin_dis" placeholder="Discount" value="00.00">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="q_subgroup_master_status" class="form-label">Is Active </label>
                                                
                                                <select id="q_subgroup_master_status" name="q_subgroup_master_status" class="form-control select2-apply">
                                                    <option value="1">Active</option>
                                                    <option value="0">Inactive</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="q_subgroup_master_remark" class="form-label">Remark </label>
                                                <textarea class="form-control" id="q_subgroup_master_remark" name="q_subgroup_master_remark" rows="2" placeholder="Enter Remark"></textarea>
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
                                    <th>SubGroup / Company / Group Name</th>
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
    var ajaxSubGroupMasterDataURL = '{{route("quot.itemsubgroup.master.ajax")}}';
    var ajaxSubGroupMasterDetailURL = '{{route("quot.itemsubgroup.master.detail")}}';
    var ajaxURLSearchCompany = '{{route("quot.itemsubgroup.search.company")}}';
    var ajaxURLSearchGroup = '{{route("quot.itemsubgroup.search.group")}}';
    var ajaxSubGroupMasterDeleteURL = '{{route("quot.itemsubgroup.master.delete")}}';
    var ajaxURLSearchManager = '{{route("quot.itemsubgroup.search.manager")}}';

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
            "url": ajaxSubGroupMasterDataURL,
            "type": "POST",
            "data": {
                "_token": csrfToken,

            }
        },
        "aoColumns": [{
                "mData": "id"
            },
            {
                "mData": "itemsubgroupname"
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


    $('#datatable').on('length.dt', function(e, settings, len) {
        setCookie('mainMasterPageLength', len, 100);
    });




    $("#q_subgroup_master_status").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#canvasMainMaster")
    });



    function generateHierarchyCode(dInput) {

        dInput = dInput.replace(/[_\W]+/g, "_")
        dInput = dInput.toUpperCase();
        $("#q_subgroup_master_code").val(dInput)


    }



    $("#q_subgroup_master_name").keyup(function() {
        generateHierarchyCode(this.value);
    });

    $("#q_subgroup_master_name").change(function() {
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
        generateHierarchyCode($("#q_subgroup_master_name").val());
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
            toastr["success"](responseText['msg']);
            reloadTable();
            resetInputForm();
            $("#canvasMainMaster").offcanvas('hide');

        } else if (responseText['status'] == 0) {

            toastr["error"](responseText['msg']);

        }

        // for normal html responses, the first argument to the success callback
        // is the XMLHttpRequest object's responseText property

        // if the ajaxForm method was passed an Options Object with the dataType
        // property set to 'xml' then the first argument to the success callback
        // is the XMLHttpRequest object's responseXML property

        // if the ajaxForm method was passed an Options Object with the dataType
        // property set to 'json' then the first argument to the success callback
        // is the json data object returned by the server

        // alert('status: ' + statusText + '\n\nresponseText: \n' + responseText +
        //     '\n\nThe output div should have already been updated with the responseText.');
    }


    $("#addBtnMainMaster").click(function() {
        $("#canvasMainMasterLable").html("Add Item Subgroup");
        $("#formMainMaster").show();
        $(".loadingcls").hide();
        resetInputForm();
    });


    function resetInputForm() {

        $('#formMainMaster').trigger("reset");
        $("#q_subgroup_master_id").val(0);
        $("#q_subgroup_master_status").select2("val", "1");
        // $("#q_subgroup_master_company_id").empty().trigger('change');
        $("#q_subgroup_master_group_id").empty().trigger('change');

    }

    function editView(id) {

        resetInputForm();

        $("#canvasMainMaster").offcanvas('show');
        $("#canvasMainMasterLable").html("Edit Item Subgroup #" + id);
        $("#formMainMaster").hide();
        $(".loadingcls").show();

        $.ajax({
            type: 'GET',
            url: ajaxSubGroupMasterDetailURL + "?id=" + id,
            success: function(resultData) {
                if (resultData['status'] == 1) {

                    $("#q_subgroup_master_id").val(resultData['data']['id']);
                    $("#q_subgroup_master_name").val(resultData['data']['itemsubgroupname']);
                    $("#q_subgroup_master_code").val(resultData['data']['shortname']);

                    if (resultData['data']['company'].length > 0) {
                        $("#q_subgroup_master_company_id").empty().trigger('change');
                        var selectedSalePersons = [];
                        for (var i = 0; i < resultData['data']['company'].length; i++) {
                            selectedSalePersons.push('' + resultData['data']['company'][i]['id'] + '');
                            var newOption = new Option(resultData['data']['company'][i]['text'], resultData['data']['company'][i]['id'], false, false);
                            $('#q_subgroup_master_company_id').append(newOption).trigger('change');
                        }
                        $("#q_subgroup_master_company_id").val(selectedSalePersons).change();
                    }

                    $("#q_subgroup_master_default_dis").val(resultData['data']['default_disc']);
                    $("#q_subgroup_master_user_dis").val(resultData['data']['maxdisc']);
                    $("#q_subgroup_master_channel_partner_disc").val(resultData['data']['channel_partner_maxdisc']);
                    $("#q_subgroup_master_manager_disc").val(resultData['data']['manager_maxdisc']);
                    // $("#q_subgroup_master_admin_dis").val(resultData['data']['company_admin_maxdisc']);

                    if (resultData['data']['manager'].length > 0) {
                        $("#q_subgroup_master_manager_ids").empty().trigger('change');
                        var selectedSalePersons = [];
                        for (var i = 0; i < resultData['data']['manager'].length; i++) {
                            selectedSalePersons.push('' + resultData['data']['manager'][i]['id'] + '');
                            var newOption = new Option(resultData['data']['manager'][i]['text'], resultData['data']['manager'][i]['id'], false, false);
                            $('#q_subgroup_master_manager_ids').append(newOption).trigger('change');
                        }
                        $("#q_subgroup_master_manager_ids").val(selectedSalePersons).change();
                    }

                    if (resultData['data']['itemgroup_id'] !== null) {
                        $("#q_subgroup_master_group_id").empty().trigger('change');
                        var newOption = new Option(resultData['data']['itemgroup']['itemgroupname'], resultData['data']['itemgroup']['id'], false, false);
                        $('#q_subgroup_master_group_id').append(newOption).trigger('change');
                    }

                    $("#q_subgroup_master_remark").val(resultData['data']['remark']);
                    $("#q_subgroup_master_status").select2("val", "" + resultData['data']['isactive'] + "");

                    $(".loadingcls").hide();
                    $("#formMainMaster").show();


                } else {

                    toastr["error"](resultData['msg']);

                }

            }
        });

    }


    $("#q_subgroup_master_company_id").select2({
        ajax: {
            url: ajaxURLSearchCompany,
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
        placeholder: 'Search for a Company',
        dropdownParent: $("#canvasMainMaster"),
    });

    $("#q_subgroup_master_manager_ids").select2({
        ajax: {
            url: ajaxURLSearchManager,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    // "company_id":  function() { return $("#q_subgroup_master_company_id").val()},
                    "q": params.term, // search term
                    "page": params.page
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
        placeholder: 'Search for a Manager',
        dropdownParent: $("#canvasMainMaster"),
    });


    $("#q_subgroup_master_group_id").select2({
        ajax: {
            url: ajaxURLSearchGroup,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    // "company_id":  function() { return $("#q_subgroup_master_company_id").val()},
                    "q": params.term, // search term
                    "page": params.page
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
        placeholder: 'Search for a Group',
        dropdownParent: $("#canvasMainMaster"),
    });

    // $("#q_group_master_company_id").change(function() {
    //     alert($('#q_group_master_company_id').val());
    // });

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
                        url: ajaxSubGroupMasterDeleteURL + "?id=" + id,
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