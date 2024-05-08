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
<link href="{{ asset('assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css')}}" rel="stylesheet" type="text/css">

<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Item Price Master</h4>
                    <div class="page-title-right">
                        <button id="updateItemFlowData" class="btn btn-primary waves-effect waves-light" role="button"><i class="bx bx-plus font-size-16 align-middle me-2"></i>Flow Update</button>
                        <button id="updateItemPriceData" class="btn btn-primary waves-effect waves-light" role="button"><i class="bx bx-plus font-size-16 align-middle me-2"></i>Price Update</button>
                        <button id="addItemPriceExcel" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#modalItemPriceExcelUpload" role="button"><i class="bx bx-plus font-size-16 align-middle me-2"></i>Excel Upload</button>
                        <button id="addBtnMainMaster" class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#canvasMainMaster" aria-controls="canvasMainMaster"><i class="bx bx-plus font-size-16 align-middle me-2"></i>Add Item Price</button>
                        <div class="offcanvas offcanvas-end" tabindex="-1" id="canvasMainMaster" aria-labelledby="canvasMainMasterLable">
                            <div class="offcanvas-header">
                                <h5 id="canvasMainMasterLable">Add Item Price</h5>
                                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                            </div>

                            <div class="offcanvas-body">
                                <div class="col-md-12 text-center loadingcls">
                                    <button type="button" class="btn btn-light waves-effect">
                                        <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading...
                                    </button>
                                </div>

                                <form id="formMainMaster" class="custom-validation" action="{{route('quot.itemprice.master.save')}}" method="POST">
                                    @csrf
                                    <input type="hidden" name="q_price_master_id" id="q_price_master_id">

                                    <input type="file" name="q_price_item_image" id="q_price_item_image" accept="image/*" style="display:none" />
                                    <div class="row" id="row_q_price_item_image">
                                        <div class="col-lg-12">
                                            <div id="div_q_price_item_image">
                                                <img id="imgPreview" src="item_image/placeholder.png" alt="" class="img-thumbnail">
                                            </div>
                                        </div>
                                    </div>


                                    <div class="col-md-12">
                                        <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                            <label for="q_price_company_id" class="form-label">Company <code class="highlighter-rouge">*</code></label>
                                            <select class="form-control select2-ajax" id="q_price_company_id" name="q_price_company_id" required>
                                            </select>
                                            <div class="invalid-feedback">
                                                Please select Company.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                            <label for="q_price_group_id" class="form-label">Item Group <code class="highlighter-rouge">*</code></label>
                                            <select class="form-control select2-ajax" id="q_price_group_id" name="q_price_group_id" required>
                                            </select>
                                            <div class="invalid-feedback">
                                                Please select ItemGroup.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                            <label for="q_price_subgroup_id" class="form-label">Item SubGroup </label>
                                            <select class="form-control select2-ajax" id="q_price_subgroup_id" name="q_price_subgroup_id">
                                            </select>
                                            <div class="invalid-feedback">
                                                Please select ItemSubGroup.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                            <label for="q_price_item_id" class="form-label">Item <code class="highlighter-rouge">*</code></label>
                                            <select class="form-control select2-ajax" id="q_price_item_id" name="q_price_item_id" required>
                                            </select>
                                            <div class="invalid-feedback">
                                                Please select Item.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                            <label for="q_price_item_type" class="form-label">Item Type <code class="highlighter-rouge">*</code></label>
                                            <select class="form-control select2-ajax select2-multiple" multiple="multiple" id="q_price_item_type" name="q_price_item_type[]" required>
                                            </select>
                                            <div class="invalid-feedback">
                                                Please select Item Type.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                            <label for="q_price_item_flow" class="form-label">Discount Flow</label>
                                            <select class="form-control select2-ajax select2-multiple" multiple="multiple" id="q_price_item_flow" name="q_price_item_flow[]">
                                            </select>
                                            <div class="invalid-feedback">
                                                Please select Discount Flow.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="q_price_discount" class="form-label">Discount </label>
                                                <input type="number" step="0.01" class="form-control" id="q_price_discount" name="q_price_discount" placeholder="Discount" value="00.00" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="q_price_channel_partners_discount" class="form-label">Channel Partners Dis. </label>
                                                <input type="number" step="0.01" class="form-control" id="q_price_channel_partners_discount" name="q_price_channel_partners_discount" placeholder="Discount" value="00.00" required>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="q_price_code" class="form-label">Item Code <code class="highlighter-rouge">*</code></label>
                                                <input type="text" class="form-control" id="q_price_code" name="q_price_code" placeholder="Code" value="" required>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="q_price_mrp" class="form-label">MRP <code class="highlighter-rouge">*</code></label>
                                                <input type="number" step="0.01" class="form-control" id="q_price_mrp" name="q_price_mrp" placeholder="MRP" value="" required>
                                            </div>
                                        </div>



                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="q_price_status" class="form-label">Is Active </label>

                                                <select id="q_price_status" name="q_price_status" class="form-control select2-apply" required>
                                                    <option value="1">Active</option>
                                                    <option value="0">Inactive</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="q_price_effectivedate" class="form-label">Effective Date <code class="highlighter-rouge">*</code></label>
                                                <div class="input-group" id="div_itemprice_effective_date">
                                                    <input type="text" class="form-control" value="{{date('d-m-Y')}}" placeholder="dd-mm-yyyy" data-date-format="dd-mm-yyyy" data-date-container='#div_itemprice_effective_date' data-provide="datepicker" data-date-autoclose="true" required name="q_price_effectivedate" id="q_price_effectivedate">
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="q_price_remark" class="form-label">Remark </label>
                                            <textarea class="form-control" id="q_price_remark" name="q_price_remark" rows="2" placeholder="Enter Remark"></textarea>
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
                    <div class="card-body" id="itemprice_listview">
                        <!-- <div class="col-md-2">
                            <div class="mb-3">
                                <label for="q_item_price_filter_subgroup" class="form-label">Brand </label>
                                <select id="q_item_price_filter_subgroup" name="q_item_price_filter_subgroup" class="form-control select2-apply">
                                </select>
                            </div>
                        </div> -->
                        <div class="col-md-2">
                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                <label for="q_item_price_filter_subgroup" class="form-label">Brand </label>
                                <select class="form-control select2-ajax" id="q_item_price_filter_subgroup" name="q_item_price_filter_subgroup">
                                </select>
                                <div class="invalid-feedback">
                                    Please select ItemSubGroup.
                                </div>
                            </div>
                        </div>

                        <table id="datatable" class="table table-striped dt-responsive  nowrap w-100">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Image</th>
                                    <th>Item Name / Company</th>
                                    <th>Item Group / SubGroup</th>
                                    <th>Code</th>
                                    <th>Mrp</th>
                                    <!-- <th>Discount</th> -->
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
@include('../quotation/master/itemprice/comman/modal_add_itemprice')

<script src="{{ asset('assets/libs/parsleyjs/parsley.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/form-validation.init.js') }}"></script>
<script src="{{ asset('assets/js/pages/jquery.form.js') }}"></script>

<script src="{{ asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js') }}"></script>
<script src="{{ asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
<script type="text/javascript">
    // quot.itemprice.master.save
    var ajaxPriceMasterDataURL = '{{route("quot.itemprice.master.ajax")}}';
    var ajaxPriceMasterDeleteURL = '{{route("quot.itemprice.master.delete")}}';
    var ajaxPriceMasterDetailURL = '{{route("quot.itemprice.master.detail")}}';
    var ajaxURLSearchCompany = '{{route("quot.itemprice.search.company")}}';
    var ajaxURLSearchItemGroup = '{{route("quot.itemprice.search.itemgroup")}}';
    var ajaxURLSearchItemSubGroup = '{{route("quot.itemprice.search.itemsubgroup")}}';
    var ajaxURLSearchItem = '{{route("quot.itemprice.search.item")}}';
    var ajaxURLSearchCategoryType = '{{route("quot.itemprice.search.category.type")}}';
    var ajaxURLSearchMultiFlow = '{{route("quot.itemprice.search.multi.flow")}}';

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
        "pagingType": "full_numbers",
        "serverSide": true,
        "info": true,
        "pageLength": mainMasterPageLength,
        "ajax": {
            "url": ajaxPriceMasterDataURL,
            "type": "POST",
            "data": {
                "_token": csrfToken,
                "item_subgroup": function() {
                    return $("#q_item_price_filter_subgroup").val()
                },
            }
        },
        "aoColumns": [{
                "mData": "id"
            },
            {
                "mData": "image"
            },
            {
                "mData": "item_name_company"
            },
            {
                "mData": "item_group_subgroup"
            },
            {
                "mData": "code"
            },
            {
                "mData": "mrp"
            },

            // {
            //     "mData": "discount"
            // },
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


    $('#q_item_price_filter_subgroup').on('change', function() {
        // alert(this.value);
        reloadTable();
    });

    // function generateHierarchyCode(dInput) {
    //     dInput = dInput.replace(/[_\W]+/g, "_")
    //     dInput = dInput.toUpperCase();
    //     $("#q_subgroup_master_code").val(dInput)
    // }

    // $("#q_subgroup_master_name").keyup(function() {
    //     generateHierarchyCode(this.value);
    // });

    // $("#q_subgroup_master_name").change(function() {
    //     generateHierarchyCode(this.value);
    // });

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
        $('#formItemPriceExcelUpdate').ajaxForm(options);
    });

    function showRequest(formData, jqForm, options) {
        // generateHierarchyCode($("#q_subgroup_master_name").val());
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
            resetItemPriceUpdateExcel();
            $("#canvasMainMaster").offcanvas('hide');
            $("#modalItemPriceExcelUpload").modal('hide');

        } else if (responseText['status'] == 0) {

            toastr["error"](responseText['msg']);
            $(".UpdatePriceThrewExcel").html("Save");

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


    $("#updateItemPriceData").click(function() {
        showPriceUpdateDialog();
    });

    $("#updateItemFlowData").click(function() {
        showFlowUpdateDialog();
    });

    $("#addItemPriceExcel").click(function() {
        $(".UpdatePriceThrewExcel").html("Save");
        resetItemPriceUpdateExcel();
    });

    $("#addBtnMainMaster").click(function() {
        $("#canvasMainMasterLable").html("Add Item Price");
        $("#formMainMaster").show();
        $(".loadingcls").hide();
        resetInputForm();
    });

    function resetItemPriceUpdateExcel() {
        $('#formItemPriceExcelUpdate').trigger("reset");
        $(".UpdatePriceThrewExcel").html("Save");
    }

    function resetInputForm() {
        $('#formMainMaster').trigger("reset");
        $("#q_price_company_id").empty().trigger('change');
        $("#q_price_group_id").empty().trigger('change');
        $("#q_price_subgroup_id").empty().trigger('change');
        $("#q_price_item_id").empty().trigger('change');
        $("#q_price_item_type").empty().trigger('change');
        $("#q_price_item_flow").empty().trigger('change');
        $("#q_price_master_id").val(0);
        $("#q_price_status").select2("val", "1");
        $("#imgPreview").attr('src', 'item_image/placeholder.png');
    }

    function editView(id) {

        resetInputForm();

        $("#canvasMainMaster").offcanvas('show');
        $("#canvasMainMasterLable").html("Edit Item Price #" + id);
        $("#formMainMaster").hide();
        $(".loadingcls").show();

        $.ajax({
            type: 'GET',
            url: ajaxPriceMasterDetailURL + "?id=" + id,
            success: function(resultData) {
                if (resultData['status'] == 1) {

                    $("#q_price_master_id").val(resultData['data']['id']);

                    $("#q_price_mrp").val(resultData['data']['mrp']);
                    $("#q_price_code").val(resultData['data']['code']);
                    $("#q_price_effectivedate").val(resultData['data']['effectivedate']);
                    $("#q_price_remark").val(resultData['data']['remark']);
                    $("#q_price_discount").val(resultData['data']['discount']);
                    $("#q_price_channel_partners_discount").val(resultData['data']['channel_partners_discount']);
                    $("#q_price_status").select2("val", "" + resultData['data']['isactive'] + "");

                    $("#imgPreview").attr('src', "" + resultData['data']['image'] + "");

                    // if (resultData['data']['item_type'] == 'POSH') {
                    //     $('.ItemType option:eq(0)').prop('selected', true)
                    // } else if (resultData['data']['item_type'] == 'QUARTZ') {
                    //     $('.ItemType option:eq(1)').prop('selected', true)
                    // }

                    if (resultData['data']['item_type'] != null) {
                        $("#q_price_item_type").empty().trigger('change');
                        var selectedSalePersons = [];
                        var cat_type_arr = resultData['data']['item_type'].split(',');
                        for (var i = 0; i < cat_type_arr.length; i++) {
                            selectedSalePersons.push('' + cat_type_arr[i] + '');
                            var newOption = new Option(cat_type_arr[i].toLowerCase().charAt(0).toUpperCase() + cat_type_arr[i].toLowerCase().slice(1), cat_type_arr[i], false, false);
                            $('#q_price_item_type').append(newOption).trigger('change');
                        }
                        $("#q_price_item_type").val(selectedSalePersons).change();
                    }

                    if (resultData['data']['flows'] != null) {
                        $("#q_price_item_flow").empty().trigger('change');
                        var selectedFlows = [];
                        for (var i = 0; i < resultData['data']['flows'].length; i++) {
                            selectedFlows.push(resultData['data']['flows'][i]['id']);
                            var newOption = new Option(resultData['data']['flows'][i]['name'], resultData['data']['flows'][i]['id'], false, false);
                            $('#q_price_item_flow').append(newOption).trigger('change');
                        }
                        $("#q_price_item_flow").val(selectedFlows).change();
                    }

                    if (resultData['data']['company'] !== null) {
                        $("#q_price_company_id").empty().trigger('change');
                        var newOption = new Option(resultData['data']['company']['companyname'], resultData['data']['company']['id'], false, false);
                        $('#q_price_company_id').append(newOption).trigger('change');
                    }

                    if (resultData['data']['itemgroup'] !== null) {
                        $("#q_price_group_id").empty().trigger('change');
                        var newOption = new Option(resultData['data']['itemgroup']['itemgroupname'], resultData['data']['itemgroup']['id'], false, false);
                        $('#q_price_group_id').append(newOption).trigger('change');
                    }

                    if (resultData['data']['itemsubgroup'] !== null) {
                        $("#q_price_subgroup_id").empty().trigger('change');
                        var newOption = new Option(resultData['data']['itemsubgroup']['itemsubgroupname'], resultData['data']['itemsubgroup']['id'], false, false);
                        $('#q_price_subgroup_id').append(newOption).trigger('change');
                    }

                    if (resultData['data']['item'] !== null) {
                        $("#q_price_item_id").empty().trigger('change');
                        var newOption = new Option(resultData['data']['item']['itemname'], resultData['data']['item']['id'], false, false);
                        $('#q_price_item_id').append(newOption).trigger('change');
                    }

                    $(".loadingcls").hide();
                    $("#formMainMaster").show();

                } else {

                    toastr["error"](resultData['msg']);

                }

            }
        });
    }

    $("#q_price_item_type").select2({
        ajax: {
            url: ajaxURLSearchCategoryType,
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
        placeholder: 'Search Item Type',
        dropdownParent: $("#canvasMainMaster"),
    });

    $("#q_price_item_flow").select2({
        ajax: {
            url: ajaxURLSearchMultiFlow,
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
        placeholder: 'Search Multiple Flow',
        dropdownParent: $("#canvasMainMaster"),
    });

    $('#div_q_price_item_image').click(function() {
        $('#q_price_item_image').trigger('click');
    });

    $(document).ready(() => {
        $('#q_price_item_image').change(function() {
            const file = this.files[0];
            console.log(file);
            if (file) {
                let reader = new FileReader();
                reader.onload = function(event) {
                    $('#imgPreview').attr('src', event.target.result);
                }
                reader.readAsDataURL(file);
            }
        });
    });


    $("#q_price_company_id").select2({
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
        placeholder: 'Select Item Company',
        dropdownParent: $("#canvasMainMaster"),
    }).on('change', function(e) {
        $("#q_price_group_id").empty().trigger('change');
        $("#q_price_subgroup_id").empty().trigger('change');
    });

    $("#q_price_group_id").select2({
        ajax: {
            url: ajaxURLSearchItemGroup,
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
        placeholder: 'Select Item Group',
        dropdownParent: $("#canvasMainMaster"),
    }).on('change', function(e) {
        $("#q_price_subgroup_id").empty().trigger('change');
    });

    $("#q_price_subgroup_id").select2({
        ajax: {
            url: ajaxURLSearchItemSubGroup,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    "group_id": function() {
                        return $("#q_price_group_id").val()
                    },
                    "company_id": function() {
                        return $("#q_price_company_id").val()
                    },
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
        placeholder: 'Select Item SubGroup',
        dropdownParent: $("#canvasMainMaster"),
    });
    $("#q_item_price_filter_subgroup").select2({
        ajax: {
            url: ajaxURLSearchItemSubGroup,
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
        placeholder: 'Select Item SubGroup',
        dropdownParent: $("#itemprice_listview"),
    });

    $("#q_price_item_id").select2({
        ajax: {
            url: ajaxURLSearchItem,
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
        placeholder: 'Select Item',
        dropdownParent: $("#canvasMainMaster"),
    });

    $("#q_price_status").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#canvasMainMaster"),
    });

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
                        url: ajaxPriceMasterDeleteURL + "?id=" + id,
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