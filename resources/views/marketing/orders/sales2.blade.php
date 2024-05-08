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
                    <h4 class="mb-sm-0 font-size-18">Approved Request

                    </h4>


                    <div class="page-title-right">

                        <!-- <button id="addBtnCanvasExportOrder" class="btn btn-info" type="button" data-bs-toggle="offcanvas" data-bs-target="#canvasExportOrder" aria-controls="canvasExportOrder"><i class="bx bx-export font-size-16 align-middle me-2"></i>Export Report </button> -->


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

            <form id="formInvoice" action="{{route('marketing.orders.sales2.invoice.save')}}" method="POST" class="needs-validation" novalidate>
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
                                                <strong>Request Date</strong><br>
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
                                        <h3 class="font-size-15 fw-bold">Marketing request summary</h3>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table align-middle table-nowrap mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 70px;">SR<br>No.</th>
                                                    <th>Product - Image</th>
                                                    <th>Product Code</th>
                                                    <th>QTY</th>
                                                    <th>Box Image</th>
                                                    <th>Sample Image</th>


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
                                                        <td class="pr-1 pt-1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                                        <td class="pt-1"><b><span class="font-weight-bolder" id="modalOrderMRP"></span></b></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="pr-1"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                                        <td><b><span class="font-weight-bolder" id="modalOrderMRPMinusDiscount"></span></b></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="pr-1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                                        <td><b><span class="font-weight-bolder" id="modalOrderGSTValue"></span></b></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="pr-1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                                        <td><b><span class="font-weight-bolder" id="modalOrderDelievery"></span></b></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="pr-1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                                        <td><b><span class="font-weight-bolder" id="modalOrderTotalPayable"></span></b></td>
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
                                <label for="invoice_number" class="form-label">Challan Number</label>
                                <input type="text" class="form-control" id="invoice_number" name="invoice_number" placeholder="Challan Number" value="" required>


                            </div>
                        </div>





                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="invoice_file" class="form-label">Challan PDF/Doc/Image</label>
                                <input type="file" class="form-control" id="invoice_file" name="invoice_file" placeholder="Invoice PDF" value="" required accept="@php
                       $fileTypes=acceptFileTypes('marketing.challan','client');
                       echo implode(',',$fileTypes); @endphp">


                            </div>
                        </div>


                    </div>

                </div>




                <div class="modal-footer" id="modalFooter">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>

                    <button id="submitInvoice" type="submit" class="btn btn-primary">UPLOAD</button>
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

    var ajaxURL = "{{route('marketing.orders.sales2.ajax')}}";
    var ajaxOrderDetail = "{{route('marketing.orders.sales2.detail')}}";
    var ajaxInvoiceCalculation = "{{route('marketing.orders.sales.invoice.calculation')}}";
    var ajaxOrderCancel = "{{route('marketing.order.cancel')}}";
    var ajaxSearchChannelPartner = "{{route('order.search.channel.partner')}}";
    var ajaxSearchProduct = "{{route('order.search.product')}}";
    var csrfToken = $("[name=_token").val();
    var ordersPageLength = getCookie('ordersPageLength') !== undefined ? getCookie('ordersPageLength') : 10;




    var table = $('#datatable').DataTable({
        "aoColumnDefs": [{
            "bSortable": false,
            "aTargets": [5]
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

    function ViewMarketingChallan(id) {

        $("#modalOrder").modal('show');
        // $("#modalOrderIdLabel").html("#DC"+id);
        $("#orderDetailLoading").show();
        $("#orderDetailBody").hide();



        $.ajax({
            type: 'GET',
            url: ajaxOrderDetail + "?invoice_id=" + id,
            success: function(resultData) {
                if (resultData['status'] == 1) {

                    $("#orderDetailLoading").hide();
                    $("#orderDetailBody").show();
                    $("#invoice_order_id").val(id);

                    $("#modalOrderIdLabel").html("#" + resultData['data']['invoice_number']);




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

                        tBody += '<img src="' + getSpaceFilePath(resultData['data']['items'][i]['product_image']) + '" alt="logo" height="50" >';


                        tBody += "</td>";

                        tBody += "<td>";
                        tBody += "" + resultData['data']['items'][i]['product_code_name'] + "";
                        tBody += "</td>";

                        // tBody += "<td>";
                        // tBody += "" + resultData['data']['items'][i]['product_group_name'] + "";
                        // tBody += "</td>";


                        tBody += "<td>";
                        tBody += "" + resultData['data']['items'][i]['qty'] + "";
                        tBody += "</td>";

                        if(resultData['data']['items'][i]['is_custome'] == 1)
                        {
                            tBody += "<td>";
                            tBody += "<a href="+getSpaceFilePath(resultData['data']['items'][i]['box_image'])+" target='_blank'><img src="+getSpaceFilePath(resultData['data']['items'][i]['box_image'])+" height="+25+"></a>";
                            tBody += "</td>";

                            tBody += "<td>";
                            tBody += "<a href="+getSpaceFilePath(resultData['data']['items'][i]['sample_image'])+" target='_blank'><img src="+getSpaceFilePath(resultData['data']['items'][i]['sample_image'])+" height="+25+"></a>";
                            tBody += "</td>";
                        }
                        else{
                            tBody += "<td>";
                            tBody += "-";
                            tBody += "</td>";

                            tBody += "<td>";
                            tBody += "-";
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
                    // $("#modalOrderMRP").html(numberWithCommas(resultData['data']['total_mrp']));
                    // $("#modalOrderMRPMinusDiscount").html(numberWithCommas(resultData['data']['total_mrp_minus_disocunt']));
                    // $("#modalOrderGSTPecentage").html(resultData['data']['gst_percentage']);
                    // $("#modalOrderGSTValue").html(numberWithCommas(resultData['data']['gst_tax']));
                    // $("#modalOrderDelievery").html(numberWithCommas(resultData['data']['delievery_charge']));
                    // $("#modalOrderTotalPayable").html(numberWithCommas(resultData['data']['total_payable']));
                    // $("#modalOrderPendingTotalPayable").html(numberWithCommas(resultData['data']['pending_total_payable']));
                    //  $("#modalOrderChannelPartnerCreditDays").html(resultData['data']['channel_partner_credit_days']);
                    //  $("#modalOrderChannelPartnerCreditLimit").html(numberWithCommas(resultData['data']['channel_partner_credit_limit']));
                    // $("#modalOrderChannelPartnerCreditPending").html(numberWithCommas(resultData['data']['channel_partner_pending_credit']));






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
</script>
@endsection