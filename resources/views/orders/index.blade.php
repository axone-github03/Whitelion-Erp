@extends('layouts.main')
@section('title', $data['title'])
@section('content')
<style type="text/css">
    .orderProductImageTd {
        text-align: center;
    }

    #modalOrderRemark {
        white-space: break-spaces;
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
                    <h4 class="mb-sm-0 font-size-18">Orders

                    </h4>


                    <div class="page-title-right">

                        <button id="addBtnCanvasExportOrder" class="btn btn-info" type="button" data-bs-toggle="offcanvas" data-bs-target="#canvasExportOrder" aria-controls="canvasExportOrder"><i class="bx bx-export font-size-16 align-middle me-2"></i>Export Report </button>
                        @if(isCreUser() == 1)
                            <a href="{{ route('order.add') }}" class="btn btn-primary"><i class="bx bx-plus"></i>Order</a>
                        @endif


                        <div class="offcanvas offcanvas-end" tabindex="-1" id="canvasExportOrder" aria-labelledby="canvasExportOrderLabel">
                            <div class="offcanvas-header">
                                <h5 id="canvasExportOrderLabel">Export Report</h5>
                                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                            </div>
                            <div class="offcanvas-body">
                                <form id="formExportReport" class="custom-validation" action="{{route('order.export')}}" method="POST">
                                    @csrf
                                    <input type="hidden" name="filter_type" value="1" />

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
                                                    <option value="3">Cancled</option>
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

                        @if(isCreUser() == 0 && isAccountUser() == 0)
                            <a class="btn btn-primary waves-effect waves-light" href="{{route('order.add')}}" role="button"><i class="bx bx-plus font-size-16 align-middle me-2"></i>Order</a>
                        @endif
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


            <div class="modal-body">

                <div class="py-2 mt-3" id="orderDetailLoading">
                    <div class="col-md-12 text-center loadingcls">






                        <button type="button" class="btn btn-light waves-effect">
                            <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading
                        </button>


                    </div>
                </div>
                <div class="row" id="orderDetailBody">
                    <div class="col-lg-12">
                        <div class="">
                            <div class="">
                                <div class="modal-print">

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


                                    <div class="table-responsive ">
                                        <table class="table align-middle table-nowrap mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 70px;">SR<br>No.</th>
                                                    <th class="orderProductImageTd">Product<br>Image</th>
                                                    <th>Product<br>Brand</th>
                                                    <th>Product<br>Type</th>
                                                    <th>Product<br>Amount</th>
                                                    <th>QTY</th>
                                                    <th>Pending<br>QTY</th>
                                                    @php
                                                    $isAdminOrCompanyAdmin=isAdminOrCompanyAdmin();
                                                    @endphp
                                                    @if( $isAdminOrCompanyAdmin==1)

                                                    <th>Stock</th>

                                                    @endif

                                                </tr>
                                            </thead>
                                            <tbody id="modalOrderTbody">

                                            </tbody>
                                        </table>
                                    </div>
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
                                                <tr>
                                                    <td class="pr-1">Pending Amount: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                                    <td><b><i class="fas fa-rupee-sign"></i><span class="font-weight-bolder" id="modalOrderPendingTotalPayable"></span></b></td>
                                                </tr>



                                            </tbody>
                                        </table>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


            </div>

            <div class="modal-footer">
                <button class="btn btn-primary" onclick="printOrder()">Download</button>

            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="modalOrderDate" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" role="dialog" aria-labelledby="modalOrderDateLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalOrderDateLabel">Quotation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formOrderDate" action="{{route('order.created.save')}}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                @csrf

                <input type="hidden" name="order_date_time_id" id="order_date_time_id">

                <div class="col-md-12 text-center loadingcls">






                    <button type="button" class="btn btn-light waves-effect">
                        <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading
                    </button>


                </div>

                <div class="modal-body">


                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="order_date_time" class="form-label">Order Date <code class="highlighter-rouge"></code></label>
                                <input type="datetime-local" class="form-control" id="order_date_time" name="order_date_time" value="">
                            </div>
                        </div>
                    </div>




                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button id="submit" type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>


        </div>
    </div>
</div>

@csrf
@endsection('content')
@section('custom-scripts')

<script src="{{ asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js') }}"></script>
<script src="{{ asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>

<script src="{{ asset('assets/libs/kendo/kendo.all.min.js') }}"></script>


<script type="text/javascript">
    var isAdminOrCompanyAdmin = "{{$isAdminOrCompanyAdmin}}";
    var ajaxURL = "{{route('orders.ajax')}}";
    var ajaxOrderDetail = "{{route('order.detail')}}";
    var ajaxOrderCancel = "{{route('order.cancel')}}";
    var ajaxSearchChannelPartner = "{{route('order.search.channel.partner')}}";
    var ajaxSearchProduct = "{{route('order.search.product')}}";
    var productList = JSON.parse("{{$data['product']}}".replace(/&quot;/g, '"'));
    var channelPartnerList = JSON.parse("{{$data['channel_partner']}}".replace(/&quot;/g, '"'));

    console.log("productList : "+productList);
    console.log("channelPartnerList : "+channelPartnerList);



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



    function printOrder() {
        kendo.drawing.drawDOM($("#modalOrder .modal-print"))
            .then(function(group) {
                return kendo.drawing.exportPDF(group, {
                    paperSize: "auto",
                    margin: {
                        left: "1cm",
                        top: "1cm",
                        right: "1cm",
                        bottom: "1cm"
                    }
                });
            })
            .done(function(data) {

                var downloadFileName = $("#modalOrderIdLabel").html();
                downloadFileName = downloadFileName.replace("#", "");
                downloadFileName = downloadFileName.replace(" ", "-");


                kendo.saveAs({
                    dataURI: data,
                    fileName: downloadFileName + ".pdf",
                    // proxyURL: "https://demos.telerik.com/kendo-ui/service/export"
                });
            });
    }


    $('#datatable').on('length.dt', function(e, settings, len) {
        setCookie('ordersPageLength', len, 100);
    });


    function CancelOrder(id) {

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
                        url: ajaxOrderCancel + "?id=" + id,
                        success: function(resultData) {

                            if (resultData['status'] == 1) {

                                table.ajax.reload(null, false);
                                t()



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

    }


    function ViewOrder(id) {

        $("#modalOrder").modal('show');
        $("#modalOrderIdLabel").html("Order #" + id);
        $("#orderDetailLoading").show();
        $("#orderDetailBody").hide();



        $.ajax({
            type: 'GET',
            url: ajaxOrderDetail + "?order_id=" + id,
            success: function(resultData) {
                if (resultData['status'] == 1) {

                    $("#orderDetailLoading").hide();
                    $("#orderDetailBody").show();




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



                    $("#modalOrderChannelPartnerDAddress").html(dAddress);

                    var tBody = "";
                    $("#modalOrderTbody").html(tBody);
                    for (var i = 0; i < resultData['data']['items'].length; i++) {
                        tBody += "<tr>";

                        tBody += "<td>";
                        tBody += "" + (i + 1) + "";
                        tBody += "</td>";

                        tBody += "<td class='orderProductImageTd'>";
                        //getSpaceFilePath

                        tBody += '<img src="' + getSpaceFilePath(resultData['data']['items'][i]['product_image']) + '" alt="logo" height="50" onload="loadBase64Data(this)" >';


                        tBody += "</td>";

                        tBody += "<td>";
                        tBody += "" + resultData['data']['items'][i]['product_brand_name'] + "";
                        tBody += "</td>";

                        tBody += "<td>";
                        tBody += "" + resultData['data']['items'][i]['product_code_name'] + "";
                        tBody += "</td>";

                        tBody += "<td>";
                        tBody += "<i class='fas fa-rupee-sign'></i>" + numberWithCommas(resultData['data']['items'][i]['total_mrp']) + "";
                        tBody += "</td>";

                        tBody += "<td>";
                        tBody += "" + resultData['data']['items'][i]['qty'] + "";
                        tBody += "</td>";

                        tBody += "<td>";
                        tBody += "" + resultData['data']['items'][i]['pending_qty'] + "";
                        tBody += "</td>";

                        if (isAdminOrCompanyAdmin == 1) {

                            tBody += "<td>";
                            tBody += "" + resultData['data']['items'][i]['product_stock'] + "";
                            tBody += "</td>";

                        }









                        tBody += "</tr>";

                    }






                    $("#modalOrderTbody").html(tBody);

                    $(".input_product_id").TouchSpin({
                        min: 0,
                        max: 1000,

                    });


                    $("#modalOrderSalePersons").html(resultData['data']['sale_persons']);
                    $("#modalOrderRemark").html(resultData['data']['remark'])
                    $("#modalOrderMRP").html(numberWithCommas(resultData['data']['total_mrp']));
                    $("#modalOrderMRPMinusDiscount").html(numberWithCommas(resultData['data']['total_mrp_minus_disocunt']));
                    $("#modalOrderGSTPecentage").html(resultData['data']['gst_percentage']);
                    $("#modalOrderGSTValue").html(numberWithCommas(resultData['data']['gst_tax']));
                    $("#modalOrderDelievery").html(numberWithCommas(resultData['data']['delievery_charge']));
                    $("#modalOrderTotalPayable").html(numberWithCommas(resultData['data']['total_payable']));
                    $("#modalOrderPendingTotalPayable").html(numberWithCommas(resultData['data']['pending_total_payable']));
                    $("#modalOrderChannelPartnerCreditDays").html(resultData['data']['channel_partner_credit_days']);
                    $("#modalOrderChannelPartnerCreditLimit").html(numberWithCommas(resultData['data']['channel_partner_credit_limit']));
                    $("#modalOrderChannelPartnerCreditPending").html(numberWithCommas(resultData['data']['channel_partner_pending_credit']));






                } else {
                    toastr["error"](resultData['msg']);
                }

            }
        });

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

    $(document).ready(function() {
        // $("#channel_partner_user_id").empty().trigger('change');
        // var selectedChannelPartner = [];
        // for (var i = 0; i < channelPartnerList.length; i++) {
        //     selectedChannelPartner.push('' + channelPartnerList[i]['id'] + '');
        //     var newOption = new Option(channelPartnerList[i]['text'], channelPartnerList[i]['id'], false,false);
        //     $('#channel_partner_user_id').append(newOption).trigger('change');
        // }
        // $("#channel_partner_user_id").val(selectedChannelPartner).change();
        
        // $("#product_inventory_id").empty().trigger('change');
        // var selectedChannelPartner = [];
        // for (var i = 0; i < productList.length; i++) {
        //     selectedChannelPartner.push('' + productList[i]['id'] + '');
        //     var newOption = new Option(productList[i]['text'], productList[i]['id'], false,false);
        //     $('#product_inventory_id').append(newOption).trigger('change');
        // }
        // $("#product_inventory_id").val(selectedChannelPartner).change();
    });

    function loadBase64Data(this1) {


        var srcURL = $(this1).attr('src');

        var image = new Image();
        image.crossOrigin = 'Anonymous';
        image.onload = function() {
            var canvas = document.createElement('canvas');
            var context = canvas.getContext('2d');
            canvas.height = this.naturalHeight;
            canvas.width = this.naturalWidth;
            context.drawImage(this, 0, 0);
            var dataURL = canvas.toDataURL('image/jpeg');
            console.log(dataURL);
            // callback(dataURL);
        };

        image.src = srcURL;
    }


    function changeOrderDate(id) {

        $('#formOrderDate').trigger("reset");
        $("#modalOrderDate").modal('show');
        $.ajax({
            type: 'GET',
            url: ajaxOrderDetail + "?order_id=" + id,
            success: function(resultData) {
                if (resultData['status'] == 1) {

                    $("#modalOrderDateLabel").html("Edit Order Created #" + resultData['data']['id']);
                    $(".loadingcls").hide();
                    // $("#order_date_time").val(resultData['data']['created_at']);

                    var now = new Date(resultData['data']['created_at_timestamp'] * 1000);
                    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
                    document.getElementById('order_date_time').value = now.toISOString().slice(0, 16);


                    $("#order_date_time_id").val(resultData['data']['id']);

                }
            }
        });


    }
</script>
@endsection