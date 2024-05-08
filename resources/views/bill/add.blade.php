@extends('layouts.main')
@section('title', $data['title'])
@section('content')
    <link href="{{ asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css') }}" rel="stylesheet" />



    <div class="page-content">
        <div class="container-fluid">

            <!-- start page title -->

            <!-- end page title -->


            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">


                            <form id="formOrder" method="POST" action="{{ route('bill.save') }}">

                                @csrf
                                <div class="row">

                                    <div class="col-lg-4">


                                        <div class="mb-3">

                                            <label class="form-label">Type</label>

                                            <select class="form-select" id="channel_partner_type"
                                                name="channel_partner_type">
                                                @php
                                                    $accessTypes = $data['type'];
                                                @endphp
                                                @if (count($accessTypes) > 0)
                                                    @foreach ($accessTypes as $key => $value)
                                                        <option value="{{ $value['id'] }}">{{ $value['name'] }} </option>
                                                    @endforeach
                                                @endif



                                            </select>
                                        </div>

                                    </div>

                                    <div class="col-lg-8">
                                        <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                            <label class="form-label">Channel Partner </label>
                                            <select class="form-control select2-ajax" id="channel_partner_user_id"
                                                name="channel_partner_user_id">

                                            </select>

                                        </div>

                                    </div>


                                    <!--  <div class="col-lg-4">
                                                        <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                            <label class="form-label">City </label>
                                                            <select class="form-control select2-ajax" id="channel_partner_city_id" >

                                                            </select>

                                                        </div>

                                                    </div> -->

                                    <!--   <div class="col-lg-4">
                                                        <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                            <label class="form-label">Channel Partner </label>
                                                            <select class="form-control select2-ajax" id="channel_partner_user_id" name="channel_partner_user_id" >

                                                            </select>

                                                        </div>

                                                    </div> -->
                                </div>

                                <input type="hidden" name="d_address_line1" id="d_address_line1">
                                <input type="hidden" name="d_address_line2" id="d_address_line2">
                                <input type="hidden" name="d_pincode" id="d_pincode">
                                <input type="hidden" name="d_country_id" id="d_country_id">
                                <input type="hidden" name="d_state_id" id="d_state_id">
                                <input type="hidden" name="d_city_id" id="d_city_id">
                                <textarea id="remark" name="remark" style="display: none;"></textarea>


                                <div class="row" id="seaction_credit">
                                    <ul class="list-inline mb-0">
                                        <li class="list-inline-item me-3">
                                            <h5 class="font-size-14" data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="" data-bs-original-title="Amount">Credit Limit: <i
                                                    class="fas fa-rupee-sign"></i><span id="credit_limit"></span></h5>
                                        </li>
                                        <li class="list-inline-item me-3">
                                            <h5 class="font-size-14" data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="" data-bs-original-title="Amount">Credit Pending: <i
                                                    class="fas fa-rupee-sign"></i><span id="credit_pending"></span></h5>
                                        </li>
                                        <li class="list-inline-item">
                                            <h5 class="font-size-14" data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="" data-bs-original-title="Due Date">Credit Days: <span
                                                    id="credit_days"></span></h5>
                                        </li>
                                    </ul>
                                </div>

                                <div class="row" id="seaction_order_items">
                                    <div class="col-xl-8">
                                        <div class="card">
                                            <div class="card-body">


                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="mb-3 ajax-select">

                                                            <select class="form-control select2-ajax"
                                                                id="product_inventory_id">

                                                            </select>


                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-3">

                                                            <input type="hidden" class="form-control product-qty-cls"
                                                                id="product_qty" name="product_qty" placeholder="QTY"
                                                                value="0">


                                                        </div>
                                                    </div>
                                                    <!-- <div class="col-md-3 ">
                                                                <div class="mb-3">

                                                                    <a href="javascript: void(0);" class="btn btn-primary" id="btnAddCart" >Add</a>
                                                                </div>
                                             </div> -->
                                                </div>





                                                <div class="table-responsive w-100">
                                                    <table class="table align-middle mb-0 table-nowrap">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>Product/Desc</th>
                                                                <th>Price</th>
                                                                <th>Discount</th>
                                                                <th>QTY</th>
                                                                <th>Total MRP</th>
                                                                <th>Payable Amt </th>
                                                                <th></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="cartTbody">


                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div class="row mt-4">
                                                    <div class="col-sm-6">

                                                    </div> <!-- end col -->
                                                    <div class="col-sm-6">
                                                        <div class="text-sm-end mt-2 mt-sm-0">
                                                            <button type="button" disabled id="btnCheckOut"
                                                                class="btn btn-success">
                                                                <i class="mdi mdi-cart-arrow-right me-1"></i> Checkout
                                                            </button>
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row-->

                                            </div>

                                        </div>
                                    </div>

                                    <div class="col-xl-4">

                                        <div class="card">
                                            <div class="card-body">
                                                <h4 class="card-title mb-3">Order Summary</h4>

                                                <div class="table-responsive">
                                                    <table class="table mb-0">
                                                        <tbody>
                                                            <tr>
                                                                <td>Total MRP :</td>
                                                                <td><i class="fas fa-rupee-sign"></i> <span
                                                                        id="order_summary_order_total_mrp"></span></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Total Discount : - </td>
                                                                <td class="text-danger"><i class="fas fa-rupee-sign"></i>
                                                                    <span id="order_summary_order_total_discount"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Ex. GST (Order value) :</td>
                                                                <td><i class="fas fa-rupee-sign"></i> <span
                                                                        id="order_summary_order_value"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Estimated Tax (GST) - <span
                                                                        id="order_summary_order_gst_percentage"></span>% :
                                                                </td>
                                                                <td><i class="fas fa-rupee-sign"></i> <span
                                                                        id="order_summary_order_gst_value"></td>
                                                            </tr>


                                                            <tr>
                                                                <td>Delivery Charges :</td>
                                                                <td><i class="fas fa-rupee-sign"></i> <span
                                                                        id="order_summary_order_delivery_charge"></span>
                                                                </td>
                                                            </tr>
                                                            <tr>

                                                                <th>Total:</th>
                                                                <th><i class="fas fa-rupee-sign"></i> <span
                                                                        id="order_summary_order_payable_total"></span></th>
                                                                <input type="hidden" id="verify_payable_total"
                                                                    name="verify_payable_total" accept="">
                                                            </tr>

                                                            <tr>
                                                                <td><button type="button"
                                                                        class="btn btn-outline-primary btn-sm waves-effect waves-light"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#modalChangeAddress"
                                                                        role="button">Change Delivery Adddress ?</button>
                                                                </td>
                                                                <td></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <!-- end table-responsive -->
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </form>




                        </div>
                    </div>
                </div> <!-- end col -->
            </div> <!-- end row -->

        </div>
        <!-- container-fluid -->
    </div>
    <!-- End Page-content -->

    <div class="modal fade" id="modalOrderPreviw" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1"
        role="dialog" aria-labelledby="modalOrderLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"> </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalQty" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="product_lable" class="form-label">Product</label>
                                <input type="text" class="form-control" id="product_lable" name="product_lable"
                                    placeholder="Product" value="" disabled>


                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="product_qty_lable" class="form-label">Quantity</label>
                                <input type="number" class="form-control product-qty-cls" id="product_qty_lable"
                                    name="product_lable" placeholder="Qty" value="0">


                            </div>
                        </div>
                        <div id="productWarning" class="alert alert-warning">
                        </div>


                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-primary" id="btnAddCart">Add</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->


    <div class="modal fade" id="modalChangeAddress" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1"
        role="dialog" aria-labelledby="modalChangeAddressLabel" aria-hidden="true">


        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalChangeAddressLabel"> Delivery Adddress</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>





                <div class="modal-body">



                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="channel_partner_d_country_id" class="form-label">Country <code
                                        class="highlighter-rouge">*</code></label>
                                <select class="form-select" id="channel_partner_d_country_id"
                                    name="channel_partner_d_country_id" required>
                                    <option selected value="1">India</option>

                                </select>
                                <div class="invalid-feedback">
                                    Please select country.
                                </div>

                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                <label class="form-label">State <code class="highlighter-rouge">*</code></label>
                                <select class="form-control select2-ajax select2-state" id="channel_partner_d_state_id"
                                    name="channel_partner_d_state_id" required>

                                </select>
                                <div class="invalid-feedback">
                                    Please select state.
                                </div>

                            </div>



                        </div>

                        <div class="col-md-4">
                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                <label class="form-label">City <code class="highlighter-rouge">*</code></label>
                                <select class="form-control select2-ajax" id="channel_partner_d_city_id"
                                    name="channel_partner_d_city_id" required>

                                </select>
                                <div class="invalid-feedback">
                                    Please select city.
                                </div>

                            </div>



                        </div>


                    </div>

                    <div class="row">

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="channel_partner_d_pincode" class="form-label">Pincode <code
                                        class="highlighter-rouge">*</code></label>
                                <input type="text" class="form-control" id="channel_partner_d_pincode"
                                    name="channel_partner_d_pincode" placeholder="Pincode" value="" required>


                            </div>
                        </div>


                    </div>


                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="channel_partner_d_address_line1" class="form-label">Address line 1 <code
                                        class="highlighter-rouge">*</code></label>
                                <input type="text" class="form-control" id="channel_partner_d_address_line1"
                                    name="channel_partner_d_address_line1" placeholder="Address line 1" value=""
                                    required>


                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="user_address_line2" class="form-label">Address line 2</label>
                                <input type="text" class="form-control" id="channel_partner_d_address_line2"
                                    name="channel_partner_d_address_line2" placeholder="Address line 2" value="">


                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">



                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>


                    <button type="button" id="validateDeliveryBtn" class="btn btn-primary">Change</button>



                </div>

            </div>
        </div>
    </div>





@endsection('content')
@section('custom-scripts')
    <script src="{{ asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/jquery.form.js') }}"></script>
    <script src="{{ asset('assets/js/order.js') }}"></script>
    <script type="text/javascript">
        var csrfToken = $("[name=_token").val();
        var ajaxSearchChannelPartner = '{{ route('bill.search.channel.partner') }}';
        var ajaxChannelPartnerDetail = '{{ route('bill.channel.partner.detail') }}';
        var ajaxSearchProduct = '{{ route('bill.search.product') }}';
        var ajaxProductDetail = '{{ route('bill.product.detail') }}';
        var ajaxOrderCalculation = '{{ route('bill.calculation') }}';
        var reditectURL = '{{ route('bill') }}';
        var ajaxURLSearchState = '{{ route('search.state.from.country') }}';
        var ajaxURLSearchCity = '{{ route('search.city.from.state') }}';





        $("#seaction_credit").hide();
        $("#seaction_order_items").hide();
        $("#product_qty").val(1);
        var channelPartnerObject = {};

        var currentProduct = {};
        var orderItems = [];
        var productIds = [];
        var shippingCost = 0;



        $("#channel_partner_type").select2({
            placeholder: 'Search for a type',
            minimumResultsForSearch: Infinity,
        });



        $("#channel_partner_user_id").select2({
            ajax: {
                url: ajaxSearchChannelPartner,
                dataType: 'json',
                delay: 0,
                data: function(params) {
                    return {
                        "channel_partner_type": function() {
                            return $("#channel_partner_type").val()
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
            placeholder: 'Search for a channel partner or city',


        });

        $('#channel_partner_type').on('change', function() {


            $("#channel_partner_user_id").empty().trigger('change');
            orderItems = [];
            productIds = [];

            orderCalculationProcess();


        });



        $('#product_inventory_id').on('change', function() {

            var productInventoryId = $(this).val();
            if (productInventoryId != null) {





                $.ajax({
                    type: 'GET',
                    url: ajaxProductDetail + "?product_inventory_id=" + productInventoryId +
                        "&channel_partner_user_id=" + $("#channel_partner_user_id").val(),
                    success: function(resultData) {
                        if (resultData['status'] == 1) {
                            currentProduct = resultData['data'];
                            $("#product_lable").val(resultData['data']['product_brand']['name'] + " " +
                                resultData['data']['product_code']['name']);
                            $("#product_qty_lable").val(0);
                            $("#modalQty").modal('show');
                            if (currentProduct['has_warning'] == 1) {
                                $("#productWarning").show();
                                $("#productWarning").html(currentProduct['warning']);

                                //                         Swal.fire(
                                //   'Warning',
                                //   currentProduct['warning'],
                                //   'warning'
                                // );
                            } else {
                                $("#productWarning").html('');
                                $("#productWarning").hide();
                            }


                        } else {
                            toastr["error"](resultData['msg']);
                        }

                    }
                });

            }

        });

        $('#modalQty').on('shown.bs.modal', function(e) {
            $("#product_qty_lable").focus();
            $("#product_qty_lable").val('').val(0);
        })


        $(document).on('hide.bs.modal', '#modalQty', function() {
            $("#product_inventory_id").focus();

        });


        $('#channel_partner_user_id').on('change', function() {

            var userId = $(this).val();
            if (userId != null) {
                $.ajax({
                    type: 'GET',
                    url: ajaxChannelPartnerDetail + "?channel_partner_user_id=" + userId,
                    success: function(resultData) {
                        if (resultData['status'] == 1) {

                            channelPartnerObject = resultData['data'];



                            $("#channel_partner_d_address_line1").val(resultData['data'][
                                'd_address_line1'
                            ]);
                            $("#channel_partner_d_address_line2").val(resultData['data'][
                                'd_address_line2'
                            ]);
                            $("#channel_partner_d_pincode").val(resultData['data']['d_pincode']);


                            $("#channel_partner_d_country_id").select2("val", "1");

                            if (typeof resultData['data']['d_state']['id'] !== "undefined" &&
                                resultData['data']['d_state']['id'] !== null) {

                                // console.log(resultData['data']['d_state']['id']);
                                // console.log(resultData['data']['d_state']['text']);

                                $("#channel_partner_d_state_id").empty().trigger('change');
                                var newOption = new Option(resultData['data']['d_state']['name'],
                                    resultData['data']['d_state']['id'], false, false);
                                $('#channel_partner_d_state_id').append(newOption).trigger('change');

                            }



                            if (typeof resultData['data']['d_city']['id'] !== "undefined" && resultData[
                                    'data']['d_city']['id'] !== null) {

                                $("#channel_partner_d_city_id").empty().trigger('change');
                                var newOption = new Option(resultData['data']['d_city']['name'],
                                    resultData['data']['d_city']['id'], false, false);
                                $('#channel_partner_d_city_id').append(newOption).trigger('change');

                            }





                            if (resultData['data']['payment_mode'] == 2) {
                                $("#seaction_credit").show();
                                $("#credit_limit").html(resultData['data']['credit_limit']);
                                $("#credit_pending").html(resultData['data']['pending_credit']);
                                $("#credit_days").html(resultData['data']['credit_days']);







                            } else {
                                $("#seaction_credit").hide();

                            }
                            shippingCost = parseFloat(resultData['data']['shipping_cost']);
                            orderItems = [];
                            productIds = [];
                            orderCalculationProcess();
                            $("#seaction_order_items").show();

                        } else {
                            toastr["error"](resultData['msg']);
                        }

                    }
                });
            } else {
                $("#seaction_credit").hide();
                $("#seaction_order_items").hide();
            }


        });


        $(document).on('focus', '.select2-selection.select2-selection--single', function(e) {
            $(this).closest(".select2-container").siblings('select:enabled').select2('open');
        });

        // steal focus during close - only capture once and stop propogation
        $('select.select2').on('select2:closing', function(e) {
            $(e.target).data("select2").$selection.one('focus focusin', function(e) {
                e.stopPropagation();
            });
        });



        $("#product_inventory_id").select2({
            ajax: {
                url: ajaxSearchProduct,
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
            placeholder: 'Search for a product',

        });

        function processAddCart() {

            $("#modalQty").modal('hide');
            $("#product_qty").val($("#product_qty_lable").val());



            currentProduct['order_qty'] = parseFloat($("#product_qty").val());
            if (currentProduct['order_qty'] != 0) {

                if ($("#product_inventory_id").val() != null && $("#product_inventory_id").val() != "") {

                    if (!productIds.includes(currentProduct['id'])) {

                        orderItems.push(currentProduct);
                        productIds.push(currentProduct['id']);
                        $("#product_inventory_id").empty().trigger('change');
                        $("#product_qty").val(1);
                        orderCalculationProcess();

                    } else {
                        toastr["error"]("Product already in cart");

                    }



                } else {
                    toastr["error"]("Invalid Product");;
                }






            } else {

                toastr["error"]("Invalid QTY");

            }


        }

        $("#btnAddCart").click(function() {



            processAddCart();


        });



        $("#validateDeliveryBtn").click(function() {


            console.log("");
            var hasValidationError = 0;

            if ($("#channel_partner_d_address_line1").val().trim() == "") {

                toastr["error"]("Please enter address line1");
                hasValidationError = 1;

            }


            if ($("#channel_partner_d_pincode").val().trim() == "") {

                toastr["error"]("Please enter pincode");
                hasValidationError = 1;

            }

            if (hasValidationError == 0) {
                $("#modalChangeAddress").modal('hide');
            }





        });



        function orderCalculationProcess() {


            if (orderItems.length > 0) {
                $("#btnCheckOut").prop('disabled', false);
            } else {
                $("#btnCheckOut").prop('disabled', true);
            }


            $("#d_address_line1").val($('#channel_partner_d_address_line1').val());
            $("#d_address_line2").val($('#channel_partner_d_address_line2').val());
            $("#d_pincode").val($('#channel_partner_d_pincode').val());
            $("#d_country_id").val($('#channel_partner_d_country_id').val());
            $("#d_state_id").val($('#channel_partner_d_state_id').val());
            $("#d_city_id").val($('#channel_partner_d_city_id').val());

            var d_country = "";

            if (typeof $('#channel_partner_d_country_id').select2('data') !== "undefined" && $(
                    '#channel_partner_d_country_id').select2('data').length > 0) {
                d_country = $('#channel_partner_d_country_id').select2('data')[0]['text'];
            }

            var d_state = "";

            if (typeof $('#channel_partner_d_state_id').select2('data') !== "undefined" && $('#channel_partner_d_state_id')
                .select2('data').length > 0) {
                d_state = $('#channel_partner_d_state_id').select2('data')[0]['text'];
            }

            var d_city = "";

            if (typeof $('#channel_partner_d_city_id').select2('data') !== "undefined" && $('#channel_partner_d_city_id')
                .select2('data').length > 0) {
                d_city = $('#channel_partner_d_city_id').select2('data')[0]['text'];
            }


            var requestData = {
                'shipping_cost': shippingCost,
                'order_items': orderItems,
                'channel_partner_user_id': $("#channel_partner_user_id").val(),
                'd_address_line1': $('#channel_partner_d_address_line1').val(),
                'd_address_line2': $('#channel_partner_d_address_line2').val(),
                'd_pincode': $('#channel_partner_d_pincode').val(),
                'd_country': d_country,
                'd_state': d_state,
                'd_city': d_city,
            }

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    "content-type": "application/json"
                },
                type: 'POST',
                url: ajaxOrderCalculation,
                data: JSON.stringify(requestData),
                success: function(resultData) {

                    $("#cartTbody").html('');
                    if (resultData['status'] == 1) {



                        $("#modalOrderPreviw .modal-body").html(resultData['preview']);


                        for (var i = 0; i < resultData['order']['items'].length; i++) {




                            var htmlTR = '<tr>';
                            htmlTR += '<td>';
                            htmlTR += '<h5 class="font-size-14 text-truncate">';
                            htmlTR += '<a href="javascript:void(0)" class="text-dark">' + resultData['order'][
                                'items'
                            ][i]['info']['product_brand']['name'] + ' ' + resultData['order']['items'][i][
                                'info'
                            ]['product_code']['name'] + '</a>';
                            htmlTR += '</h5>';
                            htmlTR += '<p class="mb-0"><span class="fw-medium">' + resultData['order']['items'][
                                i
                            ]['info']['description'] + '</span></p>';
                            htmlTR += '</td>';
                            htmlTR += '<td>';
                            htmlTR += '<i class="fas fa-rupee-sign"></i>' + numberWithCommas(resultData['order']
                                ['items'][i]['mrp']);
                            htmlTR += '</td>';
                            htmlTR += '<td>';
                            htmlTR += (resultData['order']['items'][i]['discount_percentage']) + '%';
                            htmlTR += '</td>';
                            htmlTR += '<td>';
                            htmlTR += '<div class="me-3" style="width: 120px">';
                            htmlTR += '<input type="hidden" value="' + resultData['order']['items'][i]['id'] +
                                '" name="input_product_id[]" ><input type="text" class="input_product_id"  value="' +
                                resultData['order']['items'][i]['qty'] + '" id="input_product_id_' + resultData[
                                    'order']['items'][i]['id'] + '" name="input_qty[]">';
                            htmlTR += '</div>';
                            htmlTR += '</td>';
                            htmlTR += '<td>';
                            htmlTR += '<i class="fas fa-rupee-sign"></i>' + numberWithCommas(resultData['order']
                                ['items'][i]['total_mrp']);
                            htmlTR += '</td>';
                            htmlTR += '<td>';
                            htmlTR += '<i class="fas fa-rupee-sign"></i>' + numberWithCommas(resultData['order']
                                ['items'][i]['mrp_minus_disocunt']);
                            htmlTR += '</td>';
                            htmlTR += '<td>';
                            htmlTR += '<a href="javascript:void(0);" onclick="removeProduct(' + resultData[
                                    'order']['items'][i]['id'] +
                                ')" class="action-icon text-danger"> <i class="mdi mdi-trash-can font-size-18"></i></a>';
                            htmlTR += '</td>';
                            htmlTR += '</tr>';
                            $("#cartTbody").append(htmlTR);

                        }

                        $(".input_product_id").TouchSpin({
                            min: 1,
                            max: 1000,

                        });




                        $("#order_summary_order_total_mrp").html(numberWithCommas(resultData['order'][
                            'total_mrp'
                        ]));
                        $("#order_summary_order_total_discount").html(numberWithCommas(resultData['order'][
                            'total_discount'
                        ]));
                        $("#order_summary_order_value").html(numberWithCommas(resultData['order'][
                            'total_mrp_minus_disocunt'
                        ]));
                        $("#order_summary_order_gst_percentage").html(numberWithCommas(resultData['order'][
                            'gst_percentage'
                        ]));
                        $("#order_summary_order_gst_value").html(numberWithCommas(resultData['order'][
                            'gst_tax']));
                        $("#order_summary_order_delivery_charge").html(numberWithCommas(resultData['order'][
                            'delievery_charge'
                        ]));
                        $("#order_summary_order_payable_total").html(numberWithCommas(resultData['order'][
                            'total_payable'
                        ]));
                        $("#verify_payable_total").val(resultData['order']['total_payable']);






                    } else {
                        toastr["error"](resultData['msg']);
                    }

                }
            });









        }






        $(function() {
            $(".product-qty-cls").change(function() {

                if ($(this).val() > 1000) {
                    $(this).val(1000);
                } else if ($(this).val() < 1) {
                    $(this).val(1);
                }
            });
        });

        $(document).delegate('.input_product_id', 'change', function() {


            var newQty = $(this).val();
            var selectedProductId = $(this).attr("id");
            updateCartQty(newQty, selectedProductId)



        });

        $(document).delegate('.input_product_id', 'keyup', function() {


            var newQty = $(this).val();
            var selectedProductId = $(this).attr("id");
            updateCartQty(newQty, selectedProductId)



        });


        function updateCartQty(newQty, selectedProductId) {

            var selectedProductIdPieces = selectedProductId.split("_");
            var changeProductId = selectedProductIdPieces[selectedProductIdPieces.length - 1];

            for (var i = 0; i < orderItems.length; i++) {
                if (orderItems[i]['id'] == changeProductId) {

                    orderItems[i]['order_qty'] = parseFloat(newQty);
                    break;

                }

            }

            orderCalculationProcess();

        }








        function removeProduct(productId) {

            for (var i = 0; i < orderItems.length; i++) {
                if (orderItems[i]['id'] == productId) {

                    orderItems.splice(i, 1);
                    productIds.splice(i, 1);
                    break;



                }

            }

            orderCalculationProcess();


        }






        $(document).delegate('#btnPlaceOrder', 'click', function() {

            $("#remark").val($("#previewRemark").val());

            $("#btnPlaceOrder").prop('disabled', true);
            $("#btnPlaceOrder").html("Place Order...");
            $("#btnPlaceOrderCancel").prop('disabled', true);


            $("#formOrder").submit();

        });

        $("#btnCheckOut").click(function() {

            orderCalculationProcess();

            $("#modalOrderPreviw").modal('show');

            // if(orderItems.length>0){
            //      orderCalculationProcess();

            //       Swal.fire({
            //             title: "Are you sure?",
            //             text: "You won't be able to revert this!",
            //             icon: "warning",
            //             showCancelButton: !0,
            //             confirmButtonColor: "#34c38f",
            //             cancelButtonColor: "#f46a6a",
            //             confirmButtonText: "Yes, checkout"
            //         }).then(function(t) {
            //             if(t.value){
            //                 $("#formOrder").submit();
            //             }
            //         })


            // }else{
            //     toastr["error"]("You can't proceed without order item");

            // }


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
            $('#formOrder').ajaxForm(options);
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
                orderItems = [];
                productIds = [];
                orderCalculationProcess();
                $("#modalOrderPreviw").modal('hide');
                setTimeout(function() {
                    window.location = reditectURL

                }, 2000);


            } else if (responseText['status'] == 0) {

                toastr["error"](responseText['msg']);

            }

            $("#btnPlaceOrder").prop('disabled', false);
            $("#btnPlaceOrder").html("Place Order...");
            $("#btnPlaceOrderCancel").prop('disabled', false);


        }


        $("#product_qty_lable").keydown(function(event) {
            if (event.keyCode == 13) {
                // do something here
                processAddCart();

            }
        });






        $("#channel_partner_d_country_id").select2({
            minimumResultsForSearch: Infinity,
            dropdownParent: $("#modalChangeAddress .modal-body")


        });

        $("#channel_partner_d_city_id").select2({
            ajax: {
                url: ajaxURLSearchCity,
                dataType: 'json',
                delay: 0,
                data: function(params) {
                    return {
                        "country_id": function() {
                            return $("#channel_partner_d_country_id").val()
                        },
                        "state_id": function() {
                            return $("#channel_partner_d_state_id").val()
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
            placeholder: 'Search for a city',
            dropdownParent: $("#modalChangeAddress .modal-body")

        });

        $("#channel_partner_d_state_id").select2({
            ajax: {
                url: ajaxURLSearchState,
                dataType: 'json',
                delay: 0,
                data: function(params) {
                    return {
                        "country_id": function() {
                            return $("#channel_partner_d_country_id").val()
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
            placeholder: 'Search for a state',
            dropdownParent: $("#modalChangeAddress .modal-body")

        });
    </script>
@endsection
