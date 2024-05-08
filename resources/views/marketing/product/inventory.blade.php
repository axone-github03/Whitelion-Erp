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

    #div_marketing_product_inventory_image {
        width: 100px;
        height: 100px;
        padding: 4px;
        margin: 0 auto;
        cursor: pointer;
    }

    .feature-table td {

        vertical-align: middle !important;
        text-align: center;

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
                    <h4 class="mb-sm-0 font-size-18">Marketing Product Inventory
                    </h4>

                    @php
                    $isMarketingUser = isMarketingUser();
                    @endphp


                    <button id="addBtnProductInventory" class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#canvasProductInventory" aria-controls="canvasProductInventory"><i class="bx bx-plus font-size-16 align-middle me-2"></i>Marketing Product Inventory </button>

                    <div class="offcanvas offcanvas-end" tabindex="-1" id="canvasProductInventory" aria-labelledby="canvasProductInventoryLable">
                        <div class="offcanvas-header">
                            <h5 id="canvasProductInventoryLable">Markeing Marketing Product Inventory</h5>
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">

                            <div class="col-md-12 text-center loadingcls">






                                <button type="button" class="btn btn-light waves-effect">
                                    <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading
                                </button>


                            </div>






                            <form enctype="multipart/form-data" id="formProductInventory" class="custom-validation" action="{{route('marketing.product.inventory.save')}}" method="POST">
                                <input type="file" name="marketing_product_inventory_image" id="marketing_product_inventory_image" accept="image/*" style="display:none" />

                                <div class="row" id="row_marketing_product_inventory_image">
                                    <div class="col-lg-12">







                                        <div class="" style="" id="div_marketing_product_inventory_image">



                                            <img id="imgPreview" src="{{ asset('s/marketing-product/placeholder.png') }}" alt="" class="img-thumbnail">

                                        </div>









                                    </div>
                                </div>

                                @csrf

                                <input type="hidden" name="marketing_product_inventory_id" id="marketing_product_inventory_id">

                                <input type="hidden" name="marketing_product_inventory_type_process" id="marketing_product_inventory_type_process">




                                <div class="row">

                                    <div class="col-lg-12">
                                        <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                            <label class="form-label">Product Code </label>
                                            <select class="form-control select2-ajax" id="marketing_product_code_id" name="marketing_product_code_id" required>

                                            </select>

                                        </div>

                                    </div>




                                </div>

                                <div class="row ">

                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="marketing_product_inventory_hsn" class="form-label">HSN</label>
                                            <input type="text" class="form-control" id="marketing_product_inventory_hsn" name="marketing_product_inventory_hsn" placeholder="HSN" value="" required>


                                        </div>
                                    </div>
                                </div>



                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="marketing_product_inventory_description" class="form-label">Description</label>
                                            <textarea type="text" class="form-control" id="marketing_product_inventory_description" name="marketing_product_inventory_description" placeholder="Description" value="" required></textarea>


                                        </div>
                                    </div>

                                </div>

                                <div class="row ">

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="marketing_product_inventory_purchase_price" class="form-label">Purchase Price (<i class="fas fa-rupee-sign"></i>)</label>
                                            <input type="number" class="form-control" id="marketing_product_inventory_purchase_price" name="marketing_product_inventory_purchase_price" placeholder="Price" value="" required>


                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="marketing_product_inventory_sale_price" class="form-label">Sale Price (<i class="fas fa-rupee-sign"></i>)</label>
                                            <input type="number" class="form-control" id="marketing_product_inventory_sale_price" name="marketing_product_inventory_sale_price" placeholder="Price" value="" required>


                                        </div>
                                    </div>
                                </div>


                                <div class="row ">

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="marketing_product_inventory_quantity" class="form-label">Quantity</label>
                                            <input type="number" class="form-control" id="marketing_product_inventory_quantity" name="marketing_product_inventory_quantity" placeholder="Quantity" value="" required>


                                        </div>
                                    </div>


                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="marketing_product_inventory_weight" class="form-label">Weight(gm)</label>
                                            <input type="number" class="form-control" id="marketing_product_inventory_weight" name="marketing_product_inventory_weight" placeholder="Weight" value="" required>


                                        </div>
                                    </div>





                                </div>


                                <div class="row" id="row_quantity_plus">

                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="marketing_product_inventory_quantity_plus" class="form-label">Add Quantity</label>
                                            <input type="number" class="form-control" id="marketing_product_inventory_quantity_plus" name="marketing_product_inventory_quantity_plus" placeholder="Add Quantity" value="" required>


                                        </div>
                                    </div>
                                </div>

                                <div class="row" id="row_quantity_minus">

                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="marketing_product_inventory_quantity_minus" class="form-label">Remove Quantity</label>
                                            <input type="number" class="form-control" id="marketing_product_inventory_quantity_minus" name="marketing_product_inventory_quantity_minus" placeholder="Remove Quantity" value="" required>


                                        </div>
                                    </div>
                                </div>

                                <div class="row" id="row_quantity_purpose">

                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="marketing_product_inventory_purpose" class="form-label">Purpose</label>

                                            <textarea class="form-control" id="marketing_product_inventory_purpose" name="marketing_product_inventory_purpose" placeholder="Purpose" value=""></textarea>



                                        </div>
                                    </div>
                                </div>









                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="marketing_product_inventory_status" class="form-label">Status</label>

                                            <select id="marketing_product_inventory_status" name="marketing_product_inventory_status" class="form-control select2-apply">
                                                <option value="1">Active</option>
                                                <option value="0">Inactive</option>



                                            </select>



                                        </div>
                                    </div>

                                </div>

                                <div id="row_has">


                                    <div class="form-check form-check-primary mb-3">
                                        <input class="form-check-input" type="checkbox" id="marketing_product_inventory_has_specific_code" name="marketing_product_inventory_has_specific_code">
                                        <label class="form-check-label" for="marketing_product_inventory_has_specific_code">
                                            Has Specific Code?
                                        </label>
                                    </div>


                                    <div class="form-check form-check-primary mb-3">
                                        <input class="form-check-input" type="checkbox" id="marketing_product_inventory_has_warning" name="marketing_product_inventory_has_warning">
                                        <label class="form-check-label" for="marketing_product_inventory_has_warning">
                                            Has warning?
                                        </label>
                                    </div>

                                    <div class="row" id="div_warning">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="marketing_product_inventory_warning" class="form-label">Warning</label>
                                                <textarea type="text" class="form-control" id="marketing_product_inventory_warning" name="marketing_product_inventory_warning" placeholder="Warning" value=""></textarea>


                                            </div>
                                        </div>

                                    </div>


                                    <div class="form-check form-check-primary mb-3">
                                        <input class="form-check-input" type="checkbox" id="marketing_product_inventory_has_special_item" name="marketing_product_inventory_has_special_item">
                                        <label class="form-check-label" for="marketing_product_inventory_has_special_item">
                                            Has Special Item?
                                        </label>
                                    </div>


                                    <div class="form-check form-check-primary mb-3">
                                        <input class="form-check-input" type="checkbox" id="marketing_product_inventory_notify_when_order" name="marketing_product_inventory_notify_when_order">
                                        <label class="form-check-label" for="marketing_product_inventory_notify_when_order">
                                            Notify When Order?
                                        </label>
                                    </div>

                                    <div class="row" id="div_notify_emails">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="marketing_product_inventory_notify_emails" class="form-label">Nofify emails</label>
                                                <textarea type="text" class="form-control" id="marketing_product_inventory_notify_emails" name="marketing_product_inventory_notify_emails" placeholder="Nofify emails" value=""></textarea>


                                            </div>
                                        </div>

                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="marketing_product_inventory_gst_percentage" class="form-label">GST TAX</label>

                                                <select id="marketing_product_inventory_gst_percentage" name="marketing_product_inventory_gst_percentage" class="form-control select2-apply">
                                                    <option value="0.00">0%</option>
                                                    <option value="5.00">5%</option>
                                                    <option value="12.00">12%</option>
                                                    <option value="18.00">18%</option>
                                                    <option value="28.00">28%</option>



                                                </select>



                                            </div>
                                        </div>

                                    </div>


                                </div>
                                @if($data['viewMode']==0)

                                <div class="d-flex flex-wrap gap-2">
                                    <button id="saveBtn" type="submit" class="btn btn-primary waves-effect waves-light">
                                        Save
                                    </button>

                                </div>

                                @endif
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

                                <th>Product Code</th>
                                <th>Description</th>
                                <th>Purchase Price</th>
                                <th>Sale Price</th>
                                <th>Weight</th>
                                <th>Quantity</th>
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

<script src="{{ asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js') }}"></script>
<script src="{{ asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
<script type="text/javascript">
    var ajaxProductInventoryURL = "{{route('marketing.product.inventory.ajax')}}";
    var ajaxProductInventoryDetailURL = "{{route('marketing.product.inventory.detail')}}";
    var ajaxProductInventorySearchGroupURL = "{{route('marketing.product.inventory.search.group')}}";
    var ajaxProductInventorySearchCodeURL = "{{route('marketing.product.inventory.search.code')}}";

    var viewMode = "{{$data['viewMode']}}";
    var ImageProductPlaceHolder = "{{ asset('s/product/placeholder.png') }}";




    var csrfToken = $("[name=_token").val();
    var inventoryPageLength = getCookie('inventoryPageLength') !== undefined ? getCookie('inventoryPageLength') : 10;
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
        "pageLength": inventoryPageLength,
        "ajax": {
            "url": ajaxProductInventoryURL,
            "type": "POST",
            "data": {
                "_token": csrfToken,
            }


        },
        "aoColumns": [{
                "mData": "id"
            },
            {
                "mData": "marketing_product_code"
            },
            {
                "mData": "description"
            },
            {
                "mData": "purchase_price"
            },
            {
                "mData": "sale_price"
            },
            {
                "mData": "weight"
            },
            {
                "mData": "quantity"
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
        setCookie('inventoryPageLength', len, 100);
    });



    $("#marketing_product_inventory_status").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#canvasProductInventory")
    });


    $("#marketing_product_inventory_gst_percentage").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#canvasProductInventory")
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
        $('#formProductInventory').ajaxForm(options);
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
        $("#saveBtn").prop('disabled', true);
        $("#saveBtn").html("Saving...");

        return true;
    }

    // post-submit callback
    function showResponse(responseText, statusText, xhr, $form) {



        $("#saveBtn").prop('disabled', false);
        $("#saveBtn").html("Save");


        if (responseText['status'] == 1) {
            toastr["success"](responseText['msg']);
            reloadTable();
            resetInputForm();
            $("#canvasProductInventory").offcanvas('hide');

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



    // $("#marketing_product_group_id").select2({
    //     ajax: {
    //         url: ajaxProductInventorySearchGroupURL,
    //         dataType: 'json',
    //         delay: 0,
    //         data: function(params) {
    //             return {
    //                 q: params.term, // search term
    //                 page: params.page
    //             };
    //         },
    //         processResults: function(data, params) {
    //             // parse the results into the format expected by Select2
    //             // since we are using custom formatting functions we do not need to
    //             // alter the remote JSON data, except to indicate that infinite
    //             // scrolling can be used
    //             params.page = params.page || 1;

    //             return {
    //                 results: data.results,
    //                 pagination: {
    //                     more: (params.page * 30) < data.total_count
    //                 }
    //             };
    //         },
    //         cache: false
    //     },
    //     placeholder: 'Search for marketing product group',
    //     dropdownParent: $("#canvasProductInventory")

    // });


    $("#marketing_product_code_id").select2({
        ajax: {
            url: ajaxProductInventorySearchCodeURL,
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
        placeholder: 'Search for marketing product code',
        dropdownParent: $("#canvasProductInventory")

    });


    $("#addBtnProductInventory").click(function() {

        $("#canvasProductInventoryLable").html("Add Marketing Product Inventory");
        $("#formProductInventory").show();
        $(".loadingcls").hide();
        resetInputForm();
        $("#row_marketing_product_inventory_image").show();


    });


    function resetInputForm() {



        // $("#marketing_product_group_id").prop('disabled', false);
        $("#marketing_product_code_id").prop('disabled', false);
        $("#marketing_product_inventory_description").prop('disabled', false);
        $("#product_inventory_quantity").prop('disabled', false);
        $("#marketing_product_inventory_purchase_price").prop('disabled', false);
        $("#marketing_product_inventory_sale_price").prop('disabled', false);
        $("#marketing_product_inventory_weight").prop('disabled', false);
        $("#marketing_product_inventory_hsn").prop('disabled', false);



        $('#formProductInventory').trigger("reset");
        $("#marketing_product_inventory_id").val(0);
        $("#marketing_product_inventory_status").select2("val", "1");
        // $("#marketing_product_group_id").empty().trigger('change');
        $("#marketing_product_code_id").empty().trigger('change');

        $("#row_quantity_plus").hide();
        $("#row_quantity_minus").hide();
        $("#row_quantity_purpose").hide();
        $("#row_has").show();


        $("#marketing_product_inventory_quantity_minus").removeAttr('required');
        $("#marketing_product_inventory_quantity_plus").removeAttr('required');
        $("#marketing_product_inventory_purpose").removeAttr('required');
        $("#marketing_product_inventory_quantity").prop('disabled', false);

        $("#imgPreview").attr('src', ImageProductPlaceHolder);
        $("#marketing_product_inventory_has_warning").prop("checked", false);
        $("#marketing_product_inventory_has_warning").trigger('change');

        if (viewMode == 1) {
            $("#formProductInventory input").prop('disabled', true);
            $("#formProductInventory textarea").prop('disabled', true);
            $('#formProductInventory select').select2("enable", false);


        }

    }

    function editView(id, typeOfProcess) {

        resetInputForm();
        $("#marketing_product_inventory_type_process").val(typeOfProcess);
        $("#canvasProductInventory").offcanvas('show');
        $("#row_marketing_product_inventory_image").hide();
        $("#row_quantity_purpose").show();
        $("#marketing_product_inventory_purpose").attr('required', true);


        if (typeOfProcess == "plus") {
            $("#row_quantity_plus").show();
            $("#row_quantity_minus").hide();
            $("#canvasProductInventoryLable").html("Add Stocks #" + id);
            $("#row_quantity_plus").attr('required', true);
            $("#row_has").hide();



        } else if (typeOfProcess == "minus") {
            $("#row_quantity_minus").show();
            $("#row_quantity_plus").hide();
            $("#marketing_product_inventory_quantity_minus").attr('required', true);
            $("#canvasProductInventoryLable").html("Remove Stocks #" + id);
            $("#row_has").hide();

        } else if (typeOfProcess == "all") {

            $("#row_marketing_product_inventory_image").show();
            $("#row_quantity_minus").hide();
            $("#row_quantity_plus").hide();
            $("#canvasProductInventoryLable").html("Edit Product #" + id);
            $("#row_quantity_purpose").hide();
            $("#marketing_product_inventory_purpose").removeAttr('required');



        }

        $("#formProductInventory").hide();
        $(".loadingcls").show();

        $.ajax({
            type: 'GET',
            url: ajaxProductInventoryDetailURL + "?id=" + id,
            success: function(resultData) {
                if (resultData['status'] == 1) {

                    $("#marketing_product_inventory_id").val(resultData['data']['id']);

                    // if (typeof resultData['data']['product_group']['id'] !== "undefined") {
                    //     $("#marketing_product_group_id").empty().trigger('change');
                    //     var newOption = new Option(resultData['data']['product_group']['name'], resultData['data']['product_group']['id'], false, false);
                    //     $('#marketing_product_group_id').append(newOption).trigger('change');

                    // }
                    if (typeOfProcess != "all") {
                        //   $("#marketing_product_group_id").prop('disabled', true);
                    } else {
                        $('#imgPreview').attr('src', resultData['data']['thumb']);
                    }

                    if (typeof resultData['data']['product_code']['id'] !== "undefined") {
                        $("#marketing_product_code_id").empty().trigger('change');
                        var newOption = new Option(resultData['data']['product_code']['name'], resultData['data']['product_code']['id'], false, false);
                        $('#marketing_product_code_id').append(newOption).trigger('change');

                    }
                    if (typeOfProcess != "all") {
                        $("#marketing_product_code_id").prop('disabled', true);
                    }

                    $("#marketing_product_inventory_description").val(resultData['data']['description']);
                    if (typeOfProcess != "all") {
                        $("#marketing_product_inventory_description").prop('disabled', true);
                    }

                    $("#marketing_product_inventory_quantity").val(resultData['data']['quantity']);
                    $("#marketing_product_inventory_quantity").prop('disabled', true);
                    $("#marketing_product_inventory_weight").val(resultData['data']['weight']);
                    $("#marketing_product_inventory_hsn").val(resultData['data']['hsn']);
                    if (typeOfProcess != "all") {
                        $("#marketing_product_inventory_weight").prop('disabled', true);
                        $("#marketing_product_inventory_hsn").prop('disabled', true);
                    }

                    $("#marketing_product_inventory_purchase_price").val(resultData['data']['purchase_price']);
                    $("#marketing_product_inventory_sale_price").val(resultData['data']['sale_price']);
                    if (typeOfProcess != "all") {
                        $("#marketing_product_inventory_purchase_price").prop('disabled', true);
                        $("#marketing_product_inventory_sale_price").prop('disabled', true);
                    }


                    if (resultData['data']['has_warning'] == 1) {

                        $("#marketing_product_inventory_has_warning").prop("checked", true);
                        $("#div_warning").show();
                        $("#marketing_product_inventory_warning").prop('required', true);
                        $("#marketing_product_inventory_warning").val(resultData['data']['warning']);
                    } else if (resultData['data']['has_warning'] == 0) {
                        $("#marketing_product_inventory_has_warning").prop("checked", false);
                        $("#div_warning").hide();
                        $("#marketing_product_inventory_warning").removeAttr('required');

                    }

                    if (resultData['data']['has_specific_code'] == 1) {
                        $("#marketing_product_inventory_has_specific_code").prop("checked", true);
                    } else if (resultData['data']['has_specific_code'] == 0) {
                        $("#marketing_product_inventory_has_specific_code").prop("checked", false);


                    }

                    if (resultData['data']['is_custome'] == 1) {
                        $("#marketing_product_inventory_has_special_item").prop("checked", true);
                    } else if (resultData['data']['has_specific_code'] == 0) {
                        $("#marketing_product_inventory_has_special_item").prop("checked", false);


                    }
                    
                    if (resultData['data']['notify_when_order'] == 1) {

                        $("#marketing_product_inventory_notify_when_order").prop("checked", true);
                        $("#div_notify_emails").show();
                        $("#marketing_product_inventory_notify_emails").prop('required', true);
                        $("#marketing_product_inventory_notify_emails").val(resultData['data']['notify_emails']);
                    } else if (resultData['data']['has_warning'] == 0) {
                        $("#marketing_product_inventory_notify_when_order").prop("checked", false);
                        $("#div_notify_emails").hide();
                        $("#marketing_product_inventory_notify_emails").removeAttr('required');

                    }





                    $("#marketing_product_inventory_status").select2("val", "" + resultData['data']['status'] + "");

                    $("#marketing_product_inventory_gst_percentage").val(resultData['data']['gst_percentage']);
                    $("#marketing_product_inventory_gst_percentage").trigger('change');
                    $(".loadingcls").hide();
                    $("#formProductInventory").show();


                } else {

                    toastr["error"](resultData['msg']);

                }

            }
        });

    }

    $('#div_marketing_product_inventory_image').click(function() {
        $('#marketing_product_inventory_image').trigger('click');
    });


    $(document).ready(() => {
        $('#marketing_product_inventory_image').change(function() {
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





    $('#marketing_product_inventory_has_warning').on('change', function() {


        var isChecked = $(this).is(':checked');
        if (isChecked) {

            $("#div_warning").show();
            $("#marketing_product_inventory_warning").prop('required', true);

        } else {
            $("#div_warning").hide();
            $("#marketing_product_inventory_warning").removeAttr('required');
        }


    });

    $("#marketing_product_inventory_has_warning").trigger('change');
    $('#marketing_product_inventory_notify_when_order').on('change', function() {


        var isChecked = $(this).is(':checked');
        if (isChecked) {

            $("#div_notify_emails").show();
            $("#marketing_product_inventory_notify_emails").prop('required', true);

        } else {
            $("#div_notify_emails").hide();
            $("#marketing_product_inventory_notify_emails").removeAttr('required');
        }


    });




    $("#marketing_product_inventory_notify_when_order").trigger('change');
</script>
@endsection