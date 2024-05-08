@extends('layouts.main')
@section('title', $data['title'])
@section('content')

<style type="text/css">
    td p {
        max-width: 100%;
        white-space: break-spaces;
        word-break: break-all;
        vertical-align: middle;
    }

    .product-img {
        width: 50px;
    }

    td {
        vertical-align: middle;
    }
</style>

<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Gift Products

                    </h4>

                    <div class="page-title-right">


                        <button id="addBtnGiftCategory" class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#canvasGiftProduct" aria-controls="canvasGiftProduct"><i class="bx bx-plus font-size-16 align-middle me-2"></i>Gift Product </button>


                        <div class="offcanvas offcanvas-end" tabindex="-1" id="canvasGiftProduct" aria-labelledby="canvasGiftProductLable">
                            <div class="offcanvas-header">
                                <h5 id="canvasGiftProductLable"></h5>
                                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                            </div>
                            <div class="offcanvas-body">

                                <div class="col-md-12 text-center loadingcls">






                                    <button type="button" class="btn btn-light waves-effect">
                                        <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading
                                    </button>


                                </div>






                                <form id="formGiftProduct" class="custom-validation" action="{{route('gift.product.save')}}" method="POST" enctype="multipart/form-data">

                                    @csrf

                                    <input type="hidden" name="gift_product_id" id="gift_product_id">

                                    <div class="row">

                                        <div class="col-lg-12">
                                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                <label class="form-label">Gift Category </label>
                                                <select class="form-control select2-ajax" id="gift_category_id" name="gift_category_id" required>

                                                </select>
                                                <div class="invalid-feedback">
                                                    Please select category.
                                                </div>

                                            </div>

                                        </div>




                                    </div>



                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="gift_product_name" class="form-label">Name</label>
                                                <input type="text" class="form-control" id="gift_product_name" name="gift_product_name" placeholder="Name" value="" required>


                                            </div>
                                        </div>

                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="gift_product_description" class="form-label">Description</label>
                                                <textarea type="text" class="form-control" id="gift_product_description" name="gift_product_description" placeholder="Description" value=""></textarea>


                                            </div>
                                        </div>

                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="gift_product_image" class="form-label"><span id="gift_product_image_lable">Image</span> (800×800)</label>
                                                <input type="file" class="form-control" id="gift_product_image" name="gift_product_image" value="" required>


                                            </div>
                                        </div>

                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="gift_product_image2" class="form-label"><span id="gift_product_image2_lable">Image</span> (800×800)</label>
                                                <input type="file" class="form-control" id="gift_product_image2" multiple name="gift_product_image2[]" value="">


                                            </div>
                                        </div>

                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="gift_product_name" class="form-label">Point</label>
                                                <input type="number" class="form-control" id="gift_product_point_value" name="gift_product_point_value" value="0" required>


                                            </div>
                                        </div>

                                    </div>








                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="gift_product_status" class="form-label">Status</label>

                                                <select id="gift_product_status" name="gift_product_status" class="form-control select2-apply">
                                                    <option value="1">Active</option>
                                                    <option value="0">Inactive</option>


                                                </select>



                                            </div>
                                        </div>

                                    </div>



                                    <div class="form-check form-check-primary mb-3">
                                        <input class="form-check-input" type="checkbox" id="gift_product_has_cashback" name="gift_product_has_cashback">
                                        <label class="form-check-label" for="gift_product_has_cashback">
                                            Has Cashback?
                                        </label>
                                    </div>

                                    <div class="row" id="div_cashback">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="gift_product_cashback" class="form-label">Cashback</label>
                                                <input type="number" class="form-control" id="gift_product_cashback" name="gift_product_cashback" value="0" required>


                                            </div>
                                        </div>

                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="gift_product_price" class="form-label">Price</label>
                                                <input type="number" class="form-control" id="gift_product_price" name="gift_product_price" value="0" required>


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

                        @php
                        $accessTypes=CRMUserType();
                        @endphp
                        <div class="d-flex flex-wrap gap-2 userscomman">

                            @foreach($accessTypes as $key=>$value)
                            <a href="{{route('gift.products')}}?type={{$value['id']}}" class="btn btn-outline-primary waves-effect waves-light">{{$value['another_name']}}</a>
                            @endforeach

                        </div>
                        <br>



                        <table id="datatable" class="table table-striped dt-responsive  nowrap w-100">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Name</th>
                                    <th>Primary/Secondary</th>
                                    <th>Point </th>
                                    <th>Price </th>
                                    <th>Status</th>
                                    <th>Category</th>
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
    var ajaxGiftProductDataURL = "{{route('gift.products.ajax')}}";
    var ajaxGiftProductDetailURL = "{{route('gift.product.detail')}}";
    var ajaxGiftCategoryURL = "{{route('gift.product.category')}}";
    var CRMType = "{{$data['type']}}";
    var csrfToken = $("[name=_token").val();



    var giftProductPageLength = getCookie('giftProductPageLength') !== undefined ? getCookie('giftProductPageLength') : 10;
    var table = $('#datatable').DataTable({
        "aoColumnDefs": [{
            "bSortable": false,
            "aTargets": [3]
        }],
        "order": [
            [0, 'desc']
        ],
        "processing": true,
        "serverSide": true,
        "pageLength": giftProductPageLength,
        "ajax": {
            "url": ajaxGiftProductDataURL + "?type=" + CRMType,
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
                "mData": "image"
            },
            {
                "mData": "point_value"
            },
            {
                "mData": "price"
            },
            {
                "mData": "status"
            },
            {
                "mData": "category_name"
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

        setCookie('giftProductPageLength', len, 100);


    });


    $("#gift_product_status").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#canvasGiftProduct")
    });




    $("#gift_category_id").select2({
        ajax: {
            url: ajaxGiftCategoryURL,
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
        placeholder: 'Search for gift category',
        dropdownParent: $("#canvasGiftProduct")
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
        $('#formGiftProduct').ajaxForm(options);
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
            toastr["success"](responseText['msg']);
            reloadTable();
            resetInputForm();
            $("#canvasGiftProduct").offcanvas('hide');

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


    $("#addBtnGiftCategory").click(function() {

        $("#canvasGiftProductLable").html("Add Gift Category");
        $("#formGiftProduct").show();
        $(".loadingcls").hide();
        resetInputForm();






    });


    function resetInputForm() {

        $('#formGiftProduct').trigger("reset");
        $("#gift_product_id").val(0);
        $("#gift_product_status").select2("val", "1");
        $("#gift_product_image").prop('required', true);
        $("#gift_product_image_lable").html("Primary Image");
        $("#gift_product_image2_lable").html("Secondary Images");
        $("#gift_category_id").empty().trigger('change');
        $("#gift_product_is_cash").trigger('change');
        $("#gift_product_has_cashback").trigger('change');


    }

    function editView(id) {

        resetInputForm();
        $("#gift_product_image").removeAttr('required');
        $("#gift_product_image_lable").html("Replace Primary Image");
        $("#gift_product_image2_lable").html("Replace Secondary Images");

        $("#canvasGiftProduct").offcanvas('show');
        $("#canvasGiftProductLable").html("Edit Gift Product #" + id);
        $("#formGiftProduct").hide();
        $(".loadingcls").show();

        $.ajax({
            type: 'GET',
            url: ajaxGiftProductDetailURL + "?id=" + id,
            success: function(resultData) {
                if (resultData['status'] == 1) {

                    $("#gift_product_id").val(resultData['data']['id']);
                    $("#gift_product_name").val(resultData['data']['name']);
                    $("#gift_product_description").val(resultData['data']['description']);

                    $("#gift_product_status").select2("val", "" + resultData['data']['status'] + "");
                    $("#gift_product_point_value").val(resultData['data']['point_value']);
                    $("#gift_product_price").val(resultData['data']['price']);

                    if (typeof resultData['data']['category'] !== "undefined") {
                        $("#gift_category_id").empty().trigger('change');
                        var newOption = new Option(resultData['data']['category']['text'], resultData['data']['category']['id'], false, false);
                        $('#gift_category_id').append(newOption).trigger('change');

                    }


                    // if(resultData['data']['is_cash']==1){

                    //             $("#gift_product_is_cash").prop("checked",true);
                    //             $("#div_cash").show();
                    //             $("#gift_product_cash").prop('required',true);
                    //              $("#gift_product_cash").val(resultData['data']['cash']);


                    //     }else if(resultData['data']['is_cash']==0){

                    //            $("#gift_product_is_cash").prop("checked",false);
                    //             $("#div_cash").hide();
                    //             $("#gift_product_cash").prop('required',false);
                    //              $("#gift_product_cash").val(0);

                    //     }


                    if (resultData['data']['has_cashback'] == 1) {

                        $("#gift_product_has_cashback").prop("checked", true);
                        $("#div_cashback").show();
                        $("#gift_product_cashback").prop('required', true);
                        $("#gift_product_cashback").val(resultData['data']['cashback']);


                    } else if (resultData['data']['has_cashback'] == 0) {

                        $("#gift_product_has_cashback").prop("checked", false);
                        $("#div_cashback").hide();
                        $("#gift_product_cashback").prop('required', false);
                        $("#gift_product_cashback").val(0);

                    }









                    $(".loadingcls").hide();
                    $("#formGiftProduct").show();


                } else {

                    toastr["error"](resultData['msg']);

                }

            }
        });

    }

    // $('#gift_product_is_cash').on('change', function() {


    // var isChecked = $(this).is(':checked');
    // if(isChecked){

    //     $("#div_cash").show();
    //     $("#gift_product_cash").prop('required',true);

    // }else{
    //       $("#div_cash").hide();
    //       $("#gift_product_cash").removeAttr('required');
    // }


    // });

    $('#gift_product_has_cashback').on('change', function() {


        var isChecked = $(this).is(':checked');
        if (isChecked) {

            $("#div_cashback").show();
            $("#gift_product_cashback").prop('required', true);

        } else {
            $("#div_cashback").hide();
            $("#gift_product_cashback").removeAttr('required');
        }


    });
    var currentURL = window.location.href;
    var loadedURLLink = $('.userscomman a[href="' + currentURL + '"]');
    $(loadedURLLink).removeClass('btn-outline-primary');
    $(loadedURLLink).addClass('btn-primary');
</script>
@endsection