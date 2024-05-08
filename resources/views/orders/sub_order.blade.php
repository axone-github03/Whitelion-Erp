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


<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Sub Orders

                    </h4>


                    <div class="page-title-right">



                    </div>
                </div>


            </div>
        </div>
        <!-- end page title -->




        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body" id="listview">

                        <div class="col-md-2">
                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                <label for="q_channel_partener_type" class="form-label">Channel Partener </label>
                                <select class="form-control select2-ajax" id="q_channel_partener_type" name="q_channel_partener_type">
                                </select>
                                <div class="invalid-feedback">
                                    Please select channel Partener Type.
                                </div>
                            </div>
                        </div>

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
                <div class="row">
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
                                    <div class="table-responsive">
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
                                                    <!--  <th>Dispatch</th> -->
                                                    <th>Stock</th>

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


@csrf
@endsection('content')
@section('custom-scripts')

<script src="{{ asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js') }}"></script>

<script src="{{ asset('assets/libs/kendo/kendo.all.min.js') }}"></script>


<script type="text/javascript">
    var ajaxOrderCancelDetail = "{{route('orders.sales.cancel.detail')}}";
    var ajaxOrderCancel = "{{route('order.partial.cancel')}}";
    var ajaxURL = "{{route('orders.sub.ajax')}}";
    var ajaxOrderDetail = "{{route('order.sub.detail')}}";
    var ajaxURLSearchChannelPartenerType = "{{route('search.channel.partenertype')}}";
    var csrfToken = $("[name=_token").val();
    var ordersPageLength = getCookie('ordersPageLength') !== undefined ? getCookie('ordersPageLength') : 10;

    $("#q_channel_partener_type").empty().trigger('change');
    var newOption = new Option('All', 0, false, false);
    $('#q_channel_partener_type').append(newOption).trigger('change');

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
                "type": function() {
                    return $("#q_channel_partener_type").val()
                },
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

    $('#q_channel_partener_type').on('change', function() {
        table.ajax.reload(null, false);
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

    $("#q_channel_partener_type").select2({
        ajax: {
            url: ajaxURLSearchChannelPartenerType,
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
        placeholder: 'Select channel Partener Type',
        dropdownParent: $("#listview"),
    });

    function ViewOrder(id) {

        $.ajax({
            type: 'GET',
            url: ajaxOrderDetail + "?order_id=" + id,
            success: function(resultData) {
                if (resultData['status'] == 1) {

                    $("#modalOrder").modal('show');
                    $("#modalOrderIdLabel").html("Order #" + id);
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

                        tBody += "<td class='orderProductImageTd' >";

                        tBody += '<img src="' + getSpaceFilePath(resultData['data']['items'][i]['product_image']) + '" alt="logo" height="50">';

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

                        // tBody+="<td>";
                        // tBody+='<div class="me-3" style="width: 120px"><input type="text" class="input_product_id"  value="0" id="input_product_id_'+resultData['data']['items'][i]['id']+'" name="input_qty[]" ></div>';
                        // tBody+="</td>";
                        tBody += "<td>";
                        tBody += "" + resultData['data']['items'][i]['product_stock'] + "";
                        tBody += "</td>";






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