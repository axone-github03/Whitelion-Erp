@extends('layouts.main')
@section('title', $data['title'])
@section('content')
<link href="{{ asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css') }}" rel="stylesheet" />

<link href="{{ asset('assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}" rel="stylesheet" type="text/css">



<style type="text/css">
    .section_invoice {
        border-top: 1px solid gainsboro;
        padding: 12px;
        margin-top: 14px;

    }

    #modalOrderRemark {
        white-space: break-spaces;
    }
</style>


<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Orders

                    </h4>


                    <div class="page-title-right">

                        <button id="addBtnCanvasExportOrder" class="btn btn-info" type="button" data-bs-toggle="offcanvas" data-bs-target="#canvasExportOrder" aria-controls="canvasExportOrder"><i class="bx bx-export font-size-16 align-middle me-2"></i>Export Report </button>


                        <div class="offcanvas offcanvas-end" tabindex="-1" id="canvasExportOrder" aria-labelledby="canvasExportOrderLabel">
                            <div class="offcanvas-header">
                                <h5 id="canvasExportOrderLabel">Export Report</h5>
                                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                            </div>
                            <div class="offcanvas-body">







                                <form id="formExportReport" class="custom-validation" action="{{route('order.export')}}" method="POST">

                                    @csrf

                                    <input type="hidden" name="filter_type" value="2" />


                                    <div class="row">
                                        <div class="col-lg-12">

                                            <div class="mb-3">



                                                <div class="input-daterange input-group" id="order_datepicker" data-date-format="dd-mm-yyyy" data-date-autoclose="true" data-provide="datepicker" data-date-container='#order_datepicker'>


                                                    <input type="text" class="form-control" name="start_date" id="start_date" value="@php echo date('01-m-Y'); @endphp" placeholder="Start Date" />
                                                    <input type="text" class="form-control" name="end_date" id="end_date" placeholder="End Date" value="@php echo date('t-m-Y'); @endphp" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>











                                    <div class="row">

                                        <div class="col-lg-12">
                                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                <label class="form-label">Channel partner </label>
                                                <select multiple="multiple" class="form-control select2-ajax select2-multiple" id="channel_partner_user_id" name="channel_partner_user_id[]">

                                                </select>

                                            </div>

                                        </div>




                                    </div>

                                    <div class="row">

                                        <div class="col-lg-12">
                                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                <label class="form-label">Product</label>
                                                <select multiple="multiple" class="form-control select2-ajax select2-multiple" id="product_inventory_id" name="product_inventory_id[]">

                                                </select>

                                            </div>

                                        </div>




                                    </div>

                                    <div class="row">

                                        <div class="col-lg-12">
                                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                <label class="form-label">Export Type</label>
                                                <select class="form-control select2-ajax" id="export_type" name="export_type">
                                                    <option value="0">Placed</option>
                                                    <option value="1">Pending</option>
                                                    <option value="2">Order List</option>

                                                </select>

                                            </div>

                                        </div>




                                    </div>





                                    <div class="d-flex flex-wrap gap-2">
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">
                                            Save
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


                        <div class="table-responsive">
                            <table id="datatable" class="table align-middle table-nowrap mb-0 dt-responsive">

                                <thead class="table-light">
                                    <tr>
                                        <th>Detail</th>
                                        <th>Order By</th>
                                        <th class="text-center">Channel Partner</th>
                                        <th>Sales</th>
                                        <th>Payment Detail</th>
                                        <th>Status</th>
                                        <th>Action</th>


                                    </tr>
                                </thead>


                                <tbody>

                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div> <!-- end col -->
        </div> <!-- end row -->

    </div>
    <!-- container-fluid -->
</div>
<!-- End Page-content -->


<div class="modal fade" id="modalOrder" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" role="dialog" aria-labelledby="modalOrderLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"> </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formInvoice" action="{{route('orders.sales.invoice.save')}}" method="POST" class="needs-validation" novalidate>
                @csrf
                <input type="hidden" name="invoice_order_id" id="invoice_order_id" />
                <input type="hidden" name="verify_payable_total" id="verify_payable_total">
                <div class="modal-body">


                    <div class="row">
                        <div class="col-lg-12">
                            <div class="">
                                <div class="">
                                    <div class="invoice-title">
                                        <h4 class="float-end font-size-16" id="modalOrderIdLabel"></h4>

                                        <div class="mb-4">
                                            <img src="{{asset('assets/images/order-detail-logo.png')}}" alt="logo" height="50">
                                        </div>
                                    </div>
                                    <hr>

                                    <div class="row">
                                        <div class="col-sm-6 mt-3">
                                            <address>
                                                <strong>Channel Partner Details</strong><br>
                                                <table>
                                                    <tbody>
                                                        <tr>
                                                            <td>
                                                                <span class="font-weight-bolder">
                                                                    <b><i class="bx bx-envelope"></i>
                                                                        <span id="modalOrderChannelPartnerEmailLabel"> </span></b>
                                                                </span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <span class="font-weight-bolder">
                                                                    <b><i class="bx bx-phone"></i>
                                                                        <span id="modalOrderChannelPartnerPhoneLabel"> </span></b>
                                                                </span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="pr-1 pt-1">Company Name:</td>
                                                            <td class="pt-1"><b><span class="font-weight-bolder" id="modalOrderChannelPartnerFirmName"></span></b></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="pr-1">Name:</td>
                                                            <td><b><span class="font-weight-bolder" id="modalOrderChannelPartnerName"></span></b></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="pr-1">Type:</td>
                                                            <td><b><span class="font-weight-bolder" id="modalOrderChannelPartnerType"></span></b></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="pr-1">GST Number:</td>
                                                            <td><b><span class="font-weight-bolder" id="modalOrderChannelPartnerGSTNumber"></span></b></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="pr-1">Payment Mode:</td>
                                                            <td><b><span class="font-weight-bolder" id="modalOrderChannelPartnerPaymentMode"></span></b></td>
                                                        </tr>
                                                        <tr id="divModalOrderChannelPartnerCreditDays">
                                                            <td class="pr-1">Credit Days:</td>
                                                            <td><b><span class="font-weight-bolder" id="modalOrderChannelPartnerCreditDays"></span></b></td>
                                                        </tr>
                                                        <tr id="divModalOrderChannelPartnerCreditLimit">
                                                            <td class="pr-1">Credit Limit:</td>
                                                            <td><b><i class="fas fa-rupee-sign"></i><span class="font-weight-bolder" id="modalOrderChannelPartnerCreditLimit"></span></b></td>
                                                        </tr>
                                                        <tr id="divModalOrderChannelPartnerCreditPending">
                                                            <td class="pr-1">Credit Pending:</td>
                                                            <td><b><i class="fas fa-rupee-sign"></i><span class="font-weight-bolder" id="modalOrderChannelPartnerCreditPending"></span></b></td>
                                                        </tr>


                                                    </tbody>
                                                </table>
                                            </address>
                                        </div>
                                        <div class="col-sm-6 mt-3 text-sm-end">
                                            <address>
                                                <strong>Order Date</strong><br>
                                                <span id="modalOrderDateTimeLabel"></span><br><br>
                                            </address>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <address>
                                                <strong>Billed To</strong><br>
                                                <p id="modalOrderChannelPartnerBillAddress"></p>

                                            </address>
                                        </div>
                                        <div class="col-sm-6 text-sm-end">
                                            <address class="mt-2 mt-sm-0">
                                                <strong>Shipped To</strong><br>
                                                <p id="modalOrderChannelPartnerDAddress"></p>
                                            </address>
                                        </div>
                                    </div>

                                    <div class="py-2 mt-3">
                                        <h3 class="font-size-15 fw-bold">Order summary</h3>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table align-middle table-nowrap mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 70px;">SR<br>No.</th>
                                                    <th>Product<br>Image</th>

                                                    <th>Product Brand<br>Product Type</th>
                                                    <th>MRP</th>
                                                    <th>QTY</th>
                                                    <th>Pending<br>QTY</th>
                                                    <th>Discount</th>
                                                    <th>Dispatch <br> QTY </th>
                                                    <th>Stock</th>
                                                    <th>Total <br> MRP</th>

                                                </tr>
                                            </thead>
                                            <tbody id="modalOrderTbody">

                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <address>
                                                <br>
                                                <strong>Sales Persons:</strong>
                                                <p id="modalOrderSalePersons"></p>
                                                <br>
                                                <strong>Remark:</strong>
                                                <p id="modalOrderRemark"></p>





                                            </address>
                                        </div>
                                        <div class="col-sm-6 text-sm-end">

                                            <table class="float-end">
                                                <tbody>
                                                    <tr>
                                                        <td class="pr-1 pt-1">Total MRP: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                                        <td class="pt-1"><b><i class="fas fa-rupee-sign"></i><span class="font-weight-bolder" id="modalOrderMRP"></span></b></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="pr-1">Ex. GST (Order value): &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                                        <td><b><i class="fas fa-rupee-sign"></i><span class="font-weight-bolder" id="modalOrderMRPMinusDiscount"></span></b></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="pr-1">Estimated Tax (GST) - (<span id="modalOrderGSTPecentage"></span>%): &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                                        <td><b><i class="fas fa-rupee-sign"></i><span class="font-weight-bolder" id="modalOrderGSTValue"></span></b></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="pr-1">Delivery Charges: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                                        <td><b><i class="fas fa-rupee-sign"></i><span class="font-weight-bolder" id="modalOrderDelievery"></span></b></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="pr-1">Total: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                                        <td><b><i class="fas fa-rupee-sign"></i><span class="font-weight-bolder" id="modalOrderTotalPayable"></span></b></td>
                                                    </tr>




                                                </tbody>
                                            </table>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="row section_invoice">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="invoice_date" class="form-label">Invoice Date</label>

                                <div class="input-group" id="invoice_date">
                                    <input type="text" class="form-control" value="{{date('Y-m-d')}}" placeholder="yyyy-mm-dd" data-date-format="yyyy-mm-dd" data-date-container='#invoice_date' data-provide="datepicker" data-date-autoclose="true" required name="invoice_date">


                                </div>





                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="invoice_number" class="form-label">Invoice Number</label>
                                <input type="text" class="form-control" id="invoice_number" name="invoice_number" placeholder="Invoice Number" value="" required>


                            </div>
                        </div>



                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="invoice_file" class="form-label">Invoice PDF/Doc/Image</label>
                                <input type="file" class="form-control" id="invoice_file" name="invoice_file" placeholder="Invoice PDF" value="" required accept="@php
                                                    $fileTypes=acceptFileTypes('order.invoice','client');
                                                    echo implode(',',$fileTypes); @endphp">


                            </div>
                        </div>
                    </div>


                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button id="submitInvoice" type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>



<div class="modal fade" id="modalOrderCancel" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" role="dialog" aria-labelledby="modalOrderCancelLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"> </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form>
                @csrf

                <div class="modal-body">


                    <div class="row">
                        <div class="col-lg-12">
                            <div class="">
                                <div class="">
                                    <div class="invoice-title">
                                        <h4 class="float-end font-size-16" id="modalOrderCancelLabel"></h4>

                                        <div class="mb-4">
                                            <img src="{{asset('assets/images/order-detail-logo.png')}}" alt="logo" height="50">
                                        </div>
                                    </div>
                                    <hr>


                                    <div class="py-2 mt-3">
                                        <h3 class="font-size-15 fw-bold">Order summary</h3>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table align-middle table-nowrap mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 70px;">SR<br>No.</th>
                                                    <th>Product<br>Image</th>
                                                    <th>Product Brand<br>Product Type</th>
                                                    <th>Total QTY</th>
                                                    <th>Cancel<br>QTY</th>


                                                </tr>
                                            </thead>
                                            <tbody id="modalOrderCancelTbody">

                                            </tbody>
                                        </table>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>





                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button id="submitCancelOrder" type="button" class="btn btn-primary">Cancel Order</button>
                </div>
            </form>
        </div>
    </div>
</div>





@endsection('content')
@section('custom-scripts')

<script src="{{ asset('assets/js/pages/form-validation.init.js') }}"></script>
<script src="{{ asset('assets/js/pages/jquery.form.js') }}"></script>

<script src="{{ asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js') }}"></script>



<script src="{{ asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>


<script src="{{ asset('assets/libs/parsleyjs/parsley.min.js') }}"></script>

<script src="{{ asset('assets/js/order.js') }}"></script>



<script type="text/javascript">
    $("#order_status").select2({
        minimumResultsForSearch: Infinity,
    });



    var ajaxURL = "{{route('orders.sales.ajax')}}";
    var ajaxOrderDetail = "{{route('orders.sales.detail')}}";
    var ajaxOrderCancelDetail = "{{route('orders.sales.cancel.detail')}}";
    var ajaxInvoiceCalculation = "{{route('orders.sales.invoice.calculation')}}";
    var ajaxSearchChannelPartner = "{{route('order.search.channel.partner')}}";
    var ajaxSearchProduct = "{{route('order.search.product')}}";
    var ajaxOrderCancel = "{{route('order.partial.cancel')}}";

    var csrfToken = $("[name=_token").val();
    var ordersPageLength = getCookie('ordersPageLength') !== undefined ? getCookie('ordersPageLength') : 10;



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
        "pageLength": ordersPageLength,
        "ajax": {
            "url": ajaxURL,
            "type": "POST",
            "data": {
                "_token": csrfToken,
            }


        },
        "aoColumns": [

            {
                "mData": "detail"
            },
            {
                "mData": "order_by"
            },
            {
                "mData": "channel_partner"
            },
            {
                "mData": "sale_persons"
            },
            {
                "mData": "payment_detail"
            },
            {
                "mData": "status"
            },
            {
                "mData": "action"
            }

        ],
        "drawCallback": function() {

            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })

        }
    });

    $('#datatable').on('length.dt', function(e, settings, len) {

        setCookie('ordersPageLength', len, 100);


    });




    function resetInputForm() {

        $('#formInvoice').trigger("reset");
        $("#formInvoice").removeClass('was-validated');
        $("#submitInvoice").prop('disabled', false);




    }



    function ViewOrder(id) {
        resetInputForm();

        $.ajax({
            type: 'GET',
            url: ajaxOrderDetail + "?order_id=" + id,
            success: function(resultData) {
                if (resultData['status'] == 1) {

                    $("#modalOrder").modal('show');
                    $("#modalOrderIdLabel").html("Order #" + id);
                    $("#invoice_order_id").val(id);
                    $("#modalOrderDateTimeLabel").html(resultData['data']['display_date_time']);

                    $("#modalOrderChannelPartnerEmailLabel").html(resultData['data']['channel_partner_email']);
                    $("#modalOrderChannelPartnerPhoneLabel").html(resultData['data']['channel_partner_dialing_code'] + ' ' + resultData['data']['channel_partner_phone_number']);

                    $("#modalOrderChannelPartnerFirmName").html(resultData['data']['channel_partner_firm_name']);

                    $("#modalOrderChannelPartnerName").html(resultData['data']['channel_partner_first_name'] + ' ' + resultData['data']['channel_partner_last_name']);

                    $("#modalOrderChannelPartnerType").html(resultData['data']['channel_partner_type_name']);

                    $("#modalOrderChannelPartnerGSTNumber").html(resultData['data']['gst_number']);

                    $("#modalOrderChannelPartnerPaymentMode").html(resultData['data']['payment_mode_lable']);

                    if (resultData['data']['payment_mode'] == 2) {

                        $("#divModalOrderChannelPartnerCreditDays").show();
                        $("#divModalOrderChannelPartnerCreditLimit").show();
                        $("#divModalOrderChannelPartnerCreditPending").show();

                    } else {

                        $("#divModalOrderChannelPartnerCreditDays").hide();
                        $("#divModalOrderChannelPartnerCreditLimit").hide();
                        $("#divModalOrderChannelPartnerCreditPending").hide();

                    }

                    var billAddress = resultData['data']['bill_address_line1'];
                    if (resultData['data']['bill_address_line2'] != "") {
                        billAddress += "<br>" + resultData['data']['bill_address_line2'];
                    }
                    billAddress += "<br>" + resultData['data']['d_pincode'];
                    billAddress += "<br>" + resultData['data']['d_city_name'] + ", " + resultData['data']['d_state_name'] + ", " + resultData['data']['d_country_name'];

                    $("#modalOrderChannelPartnerBillAddress").html(billAddress);


                    var dAddress = resultData['data']['d_address_line1'];
                    if (resultData['data']['d_address_line2'] != "") {
                        dAddress += "<br>" + resultData['data']['d_address_line2'];
                    }
                    dAddress += "<br>" + resultData['data']['d_pincode'];
                    dAddress += "<br>" + resultData['data']['d_city_name'] + ", " + resultData['data']['d_state_name'] + ", " + resultData['data']['d_country_name'];

                    GSTPercentage = parseFloat(resultData['data']['gst_percentage']);
                    shippingCost = parseFloat(resultData['data']['shipping_cost']);

                    $("#modalOrderChannelPartnerDAddress").html(dAddress);
                    $("#modalOrderSalePersons").html(resultData['data']['sale_persons']);
                    $("#modalOrderRemark").html(resultData['data']['remark'])
                    $("#modalOrderChannelPartnerCreditDays").html(resultData['data']['channel_partner_credit_days']);
                    $("#modalOrderChannelPartnerCreditLimit").html(numberWithCommas(resultData['data']['channel_partner_credit_limit']));
                    $("#modalOrderChannelPartnerCreditPending").html(numberWithCommas(resultData['data']['channel_partner_pending_credit']));


                    orderItems = resultData['data']['items'];
                    for (var i = 0; i < orderItems.length; i++) {
                        orderItems[i]['updated_qty'] = orderItems[i]['pending_qty'];
                    }
                    invoiceCalculationProcess();













                } else {
                    toastr["error"](resultData['msg']);
                }

            }
        });

    }

    $(document).delegate('.input_order_item_id', 'change', function() {


        var newQty = $(this).val();
        var selectedProductId = $(this).attr("id");
        updateCartQty(newQty, selectedProductId)




    });

    $(document).delegate('.input_order_item_id', 'keyup', function() {


        var newQty = $(this).val();
        var selectedProductId = $(this).attr("id");
        updateCartQty(newQty, selectedProductId)



    });

    function updateCartQty(newQty, selectedProductId) {

        var selectedProductIdPieces = selectedProductId.split("_");
        var changeProductId = selectedProductIdPieces[selectedProductIdPieces.length - 1];


        for (var i = 0; i < orderItems.length; i++) {
            if (orderItems[i]['id'] == changeProductId) {


                orderItems[i]['updated_qty'] = parseFloat(newQty);
                break;

            }

        }

        invoiceCalculationProcess();

    }

    function invoiceCalculationProcess() {



        var requestData = {
            'shipping_cost': shippingCost,
            'gst_percentage': GSTPercentage,
            'order_items': orderItems,
        }

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                "content-type": "application/json"
            },
            type: 'POST',
            url: ajaxInvoiceCalculation,
            data: JSON.stringify(requestData),
            success: function(resultData) {

                $("#modalOrderTbody").html('');
                if (resultData['status'] == 1) {





                    for (var i = 0; i < resultData['order']['items'].length; i++) {


                        var htmlTR = "";
                        htmlTR += "<tr>";

                        htmlTR += "<td>";
                        htmlTR += "" + (i + 1) + "";
                        htmlTR += "</td>";

                        htmlTR += "<td>";

                        htmlTR += '<img src="' + getSpaceFilePath(resultData['order']['items'][i]['info']['product_image']) + '" alt="logo" height="50">';




                        htmlTR += "</td>";

                        htmlTR += "<td>";
                        htmlTR += "" + resultData['order']['items'][i]['info']['product_brand_name'] + "<br>";

                        htmlTR += "" + resultData['order']['items'][i]['info']['product_code_name'] + "";
                        htmlTR += "</td>";



                        htmlTR += "<td>";
                        htmlTR += "<i class='fas fa-rupee-sign'></i>" + numberWithCommas(resultData['order']['items'][i]['mrp']) + "";
                        htmlTR += "</td>";

                        htmlTR += "<td>";
                        htmlTR += "" + resultData['order']['items'][i]['info']['orignal_qty'] + "";
                        htmlTR += "</td>";

                        htmlTR += "<td>";
                        htmlTR += "" + resultData['order']['items'][i]['info']['pending_qty'] + "";
                        htmlTR += "</td>";

                        htmlTR += "<td>";
                        htmlTR += "" + resultData['order']['items'][i]['discount_percentage'] + "%";
                        htmlTR += "</td>";

                        htmlTR += "<td>";
                        htmlTR += '<input type="hidden" name="input_order_item_id[]" value="' + resultData['order']['items'][i]['id'] + '" /> <div class="me-3" style="width: 200px"><input type="text" class="input_order_item_id"  value="' + resultData['order']['items'][i]['qty'] + '"  id="input_order_item_id_' + resultData['order']['items'][i]['id'] + '" name="input_qty[]" ></div>';
                        htmlTR += "</td>";
                        htmlTR += "<td>";
                        htmlTR += "" + resultData['order']['items'][i]['info']['product_stock'] + "";
                        htmlTR += "</td>";

                        htmlTR += "<td>";
                        htmlTR += "<i class='fas fa-rupee-sign'></i>" + numberWithCommas(resultData['order']['items'][i]['total_mrp']) + "";
                        htmlTR += "</td>";






                        htmlTR += "</tr>";



                        $("#modalOrderTbody").append(htmlTR);


                        $(".input_order_item_id").TouchSpin({
                            min: 0,
                            max: resultData['order']['items'][i]['info']['pending_qty'],

                        });
                    }


                    $("#modalOrderMRP").html(numberWithCommas(resultData['order']['total_mrp']));
                    $("#modalOrderMRPMinusDiscount").html(numberWithCommas(resultData['order']['total_mrp_minus_disocunt']));

                    $("#modalOrderGSTPecentage").html(resultData['order']['gst_percentage']);
                    $("#modalOrderGSTValue").html(numberWithCommas(resultData['order']['gst_tax']));
                    $("#modalOrderDelievery").html(numberWithCommas(resultData['order']['delievery_charge']));
                    $("#modalOrderTotalPayable").html(numberWithCommas(resultData['order']['total_payable']));
                    $("#verify_payable_total").val(resultData['order']['total_payable']);


                } else {

                    toastr["error"](resultData['msg']);

                }
            }
        });










    }






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
        $('#formInvoice').ajaxForm(options);
    });

    function showRequest(formData, jqForm, options) {

        // formData is an array; here we use $.param to convert it to a string to display it
        // but the form plugin does this for you automatically when it submits the data
        var queryString = $.param(formData);
        $("#submitInvoice").prop('disabled', true);

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
            resetInputForm();

            //ViewOrder($("#invoice_order_id").val());
            table.ajax.reload(null, false);
            $("#modalOrder").modal('hide');
            $("#submitInvoice").prop('disabled', false);




        } else if (responseText['status'] == 0) {

            toastr["error"](responseText['msg']);
            $("#submitInvoice").prop('disabled', false);

        }


    }


    $("#export_type").select2({
        dropdownParent: $("#canvasExportOrder")
    });


    $("#channel_partner_user_id").select2({
        ajax: {
            url: ajaxSearchChannelPartner,
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
        placeholder: 'Search for channel partner',
        dropdownParent: $("#canvasExportOrder")
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
        placeholder: 'Search for product',
        dropdownParent: $("#canvasExportOrder")
    });

    var CancelOrderId = 0;

    function CancelOrder(id) {


        $.ajax({
            type: 'GET',
            url: ajaxOrderCancelDetail + "?order_id=" + id,
            success: function(resultData) {

                $("#modalOrderCancelTbody").html('');
                CancelOrderId = 0;
                if (resultData['status'] == 1) {

                    CancelOrderId = resultData['data']['id'];


                    $("#modalOrderCancel").modal('show');
                    $("#modalOrderCancelLabel").html("Order #" + id);
                    for (var i = 0; i < resultData['data']['items'].length; i++) {


                        var htmlTR = "";
                        htmlTR += "<tr>";

                        htmlTR += "<td>";
                        htmlTR += "" + (i + 1) + "";
                        htmlTR += "</td>";

                        htmlTR += "<td>";

                        htmlTR += '<img src="' + getSpaceFilePath(resultData['data']['items'][i]['product_image']) + '" alt="logo" height="50">';




                        htmlTR += "</td>";

                        htmlTR += "<td>";
                        htmlTR += "" + resultData['data']['items'][i]['product_brand_name'] + "<br>";

                        htmlTR += "" + resultData['data']['items'][i]['product_code_name'] + "";
                        htmlTR += "</td>";



                        htmlTR += "<td>";
                        htmlTR += "" + resultData['data']['items'][i]['qty'] + "";
                        htmlTR += "</td>";

                        htmlTR += "<td>";
                        htmlTR += "" + resultData['data']['items'][i]['cancelled_qty'] + "";
                        htmlTR += "</td>";








                        htmlTR += "</tr>";



                        $("#modalOrderCancelTbody").append(htmlTR);



                    }




                } else {

                    toastr["error"](resultData['msg']);

                }




            }
        });

    }

    $("#submitCancelOrder").click(function() {
        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: !0,
            confirmButtonText: "Yes, mark as canceled !",
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
                        url: ajaxOrderCancel + "?id=" + CancelOrderId,
                        success: function(resultData) {

                            if (resultData['status'] == 1) {

                                $("#modalOrderCancel").modal('hide');

                                table.ajax.reload(null, false);
                                t();



                            }




                        }
                    });



                })
            },
        }).then(function(t) {

            if (t.value === true) {



                Swal.fire({
                    title: "Mark as canceled!",
                    text: "Your record has been updated.",
                    icon: "success"
                });


            }

        });
    });
</script>
@endsection