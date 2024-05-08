@extends('layouts.main')
@section('title', $data['title'])
@section('content')
    <link href="{{ asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css') }}" rel="stylesheet" />


    <div class="page-content">
        <div class="container-fluid">

            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">Challan Packed

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
                        <div class="card-body">


                            <div class="table-responsive">
                                <table id="datatable" class="table align-middle table-nowrap dt-responsive mb-0 w-100">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Detail</th>

                                            <th>Channel Partner</th>
                                            <th>Sale Persons</th>
                                            <th>Payment Detail</th>
                                            <th>Challan Status</th>
                                            <th>Mark Dispatch</th>

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

    <div class="modal fade" id="modalDispatch" data-bs-backdrop="static" tabindex="-1" role="dialog"
        aria-labelledby="modalInvoiceLabel" aria-hidden="true">
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
                                    <div class="invoice-title">
                                        <h4 class="float-end font-size-16" id="modalDispatchLabel"></h4>

                                        <div class="mb-4">
                                            <img src="{{ asset('assets/images/order-detail-logo.png') }}" alt="logo"
                                                height="50">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form enctype="multipart/form-data" id="formInvoiceDispatch" class="custom-validation"
                        action="{{ route('marketing.orders.delivery.challan.markasdispatch') }}" method="POST">

                        @csrf

                        <input type="hidden" name="invoice_id" id="invoice_id">

                        <div class="table-responsive">
                            <table class="table align-middle table-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 70px;">SR<br>No.</th>
                                        <th>Product<br>Image</th>
                                        <th>Product<br>Group</th>
                                        <th>Product<br>Type</th>

                                        <th>Specific Code</th>





                                    </tr>
                                </thead>
                                <tbody id="modalDispatchTbody">

                                </tbody>
                            </table>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="invoice_track_id" class="form-label">Track Id</label>
                                    <input type="text" class="form-control" id="invoice_track_id" name="invoice_track_id"
                                        placeholder="Track Id" value="" required>


                                </div>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="invoice_box_number" class="form-label">Number Of Boxes</label>
                                    <input type="number" class="form-control" id="invoice_box_number"
                                        name="invoice_box_number" placeholder="Number Of Boxes" value="" required>


                                </div>
                            </div>

                        </div>




                        <div class="row">

                            <div class="col-lg-12">
                                <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                    <label class="form-label">Courier Service </label>
                                    <select class="form-control select2-ajax" id="invoice_courier_service_id"
                                        name="invoice_courier_service_id" required>

                                    </select>

                                </div>

                            </div>




                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="invoice_dispatch_detail" class="form-label">Dispatch Detail</label>
                                    <input type="file"
                                        accept="@php
$fileTypes=acceptFileTypes('order.dispatch.detail','client');
                                                    echo implode(',',$fileTypes); @endphp"
                                        class="form-control" id="invoice_dispatch_detail" name="invoice_dispatch_detail[]"
                                        placeholder="Dispatch Detail" value="" multiple>


                                </div>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="invoice_eway_bill" class="form-label">Eway Bill</label>
                                    <input type="file"
                                        accept="@php
$fileTypes=acceptFileTypes('order.eway.bill','client');
                                                    echo implode(',',$fileTypes); @endphp"
                                        class="form-control" class="form-control" id="invoice_eway_bill"
                                        name="invoice_eway_bill" placeholder="Eway Bills" value="">


                                </div>
                            </div>

                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary waves-effect waves-light">
                                MARK AS DISPATCH
                            </button>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <div class="offcanvas offcanvas-end" tabindex="-1" id="canvasDispatchInvoice"
        aria-labelledby="canvasDispatchInvoiceLable">
        <div class="offcanvas-header">
            <h5 id="canvasDispatchInvoiceLable">Dispatch</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">

        </div>
    </div>

    @include('../marketing/invoice/comman/detail')



    @csrf
@endsection('content')
@section('custom-scripts')

    <script src="{{ asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js') }}"></script>
    <script src="{{ asset('assets/libs/parsleyjs/parsley.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/form-validation.init.js') }}"></script>
    <script src="{{ asset('assets/js/pages/jquery.form.js') }}"></script>
    <script type="text/javascript">
        var ajaxURL = '{{ route('marketing.orders.delivery.challan.packed.ajax') }}';
        var ajaxInvoiceDetail = '{{ route('marketing.orders.delivery.challan.detail') }}';
        var ajaxInvoiceMarkAsPacked = '{{ route('marketing.orders.delivery.challan.markasdispatch') }}';
        var ajaxSearchCourier = '{{ route('marketing.orders.delivery.challan.search.courier') }}';
        var ajaxIsStockAvailable = '{{ route('marketing.request.is.stock.available') }}';
        var csrfToken = $("[name=_token").val();

        var invoicePageLength = getCookie('invoicePageLength') !== undefined ? getCookie('invoicePageLength') : 10;

        var table = $('#datatable').DataTable({
            "aoColumnDefs": [{
                "bSortable": false,
                "aTargets": [3, 4, 5, 6]
            }],
            "order": [
                [0, 'desc']
            ],
            "processing": true,
            "serverSide": true,
            "pageLength": invoicePageLength,
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
                    "mData": "action_mark_dispatch"
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

            setCookie('invoicePageLength', len, 100);


        });

        // function isAvailableStock(id) {

        // }

        function doMarkAsDispatch(id) {

            $.ajax({
                type: 'GET',
                url: ajaxIsStockAvailable,
                data: {
                    "invoice_id": id,
                },
                success: function(resultData) {
                    if (resultData['data'] == 0) {
                        toastr["error"](resultData['msg'])
                    } else {
                        $("#modalDispatch").modal('show');
                        $("#formInvoiceDispatch").trigger('reset');
                        $("#invoice_id").val(id);


                        $.ajax({
                            type: 'GET',
                            url: ajaxInvoiceDetail + "?invoice_id=" + id,
                            success: function(resultData) {
                                if (resultData['status'] == 1) {

                                    // console.log(resultData);

                                    // $("#modalInvoice").modal('show');
                                    $("#modalDispatch .modal-title").html("#" + resultData['data'][
                                        'invoice_number'
                                    ]);
                                    // $("#modalInvoiceDateTimeLabel").html(resultData['data']['display_date_time']);

                                    // $("#modalInvoiceChannelPartnerEmailLabel").html(resultData['data']['channel_partner_email']);
                                    // $("#modalInvoiceChannelPartnerPhoneLabel").html(resultData['data']['channel_partner_dialing_code']+' '+resultData['data']['channel_partner_phone_number']);

                                    // $("#modalInvoiceChannelPartnerFirmName").html(resultData['data']['channel_partner_firm_name']);

                                    // $("#modalInvoiceChannelPartnerName").html(resultData['data']['channel_partner_first_name']+' '+resultData['data']['channel_partner_last_name']);

                                    // $("#modalInvoiceChannelPartnerType").html(resultData['data']['channel_partner_type_name']);

                                    // $("#modalInvoiceChannelPartnerGSTNumber").html(resultData['data']['gst_number']);

                                    // $("#modalInvoiceChannelPartnerPaymentMode").html(resultData['data']['payment_mode_lable']);

                                    //   if(resultData['data']['payment_mode']==2){

                                    //    $("#divModalInvoiceChannelPartnerCreditDays").show();
                                    //    $("#divModalInvoiceChannelPartnerCreditLimit").show();
                                    //    $("#divModalInvoiceChannelPartnerCreditPending").show();

                                    // }else{

                                    //    $("#divModalInvoiceChannelPartnerCreditDays").hide();
                                    //    $("#divModalInvoiceChannelPartnerCreditLimit").hide();
                                    //    $("#divModalInvoiceChannelPartnerCreditPending").hide();

                                    // }

                                    // var billAddress=resultData['data']['bill_address_line1'];
                                    // if(resultData['data']['bill_address_line2']!=""){
                                    //    billAddress+="<br>"+resultData['data']['bill_address_line2'];
                                    // }
                                    // billAddress+="<br>"+resultData['data']['d_pincode'];
                                    // billAddress+="<br>"+resultData['data']['d_city_name']+", "+resultData['data']['d_state_name']+", "+resultData['data']['d_country_name'];

                                    // $("#modalInvoiceChannelPartnerBillAddress").html(billAddress);


                                    //  var dAddress=resultData['data']['d_address_line1'];
                                    // if(resultData['data']['d_address_line2']!=""){
                                    //    dAddress+="<br>"+resultData['data']['d_address_line2'];
                                    // }
                                    // dAddress+="<br>"+resultData['data']['d_pincode'];
                                    // dAddress+="<br>"+resultData['data']['d_city_name']+", "+resultData['data']['d_state_name']+", "+resultData['data']['d_country_name'];

                                    // $("#modalInvoiceChannelPartnerDAddress").html(dAddress);

                                    var tBody = "";
                                    $("#modalDispatchTbody").html(tBody);
                                    for (var i = 0; i < resultData['data']['items'].length; i++) {
                                        tBody += "<tr>";

                                        tBody += "<td>";
                                        tBody += "" + (i + 1) + "";
                                        tBody += "</td>";

                                        tBody += "<td>";

                                        tBody += '<img src="' + getSpaceFilePath(resultData['data'][
                                            'items'
                                        ][i][
                                            'product_image'
                                        ]) + '" alt="logo" height="50">';

                                        tBody += "</td>";

                                        tBody += "<td>";
                                        tBody += "" + resultData['data']['items'][i][
                                            'product_group_name'
                                        ] + "";
                                        tBody += "</td>";

                                        tBody += "<td>";
                                        tBody += "" + resultData['data']['items'][i][
                                            'product_code_name'
                                        ] + "";
                                        tBody += "</td>";

                                        // tBody+="<td>";
                                        // tBody+="<i class='fas fa-rupee-sign'></i>"+numberWithCommas(resultData['data']['items'][i]['total_mrp'])+"";
                                        // tBody+="</td>";

                                        // tBody+="<td>";
                                        // tBody+=""+resultData['data']['items'][i]['qty']+"";
                                        // tBody+="</td>";

                                        if (resultData['data']['items'][i]['has_specific_code'] ==
                                            1) {

                                            tBody += "<td>";
                                            tBody +=
                                                "<input type='text' class='form-control' name='has_specific_code[" +
                                                resultData['data']['items'][i]['id'] +
                                                "]' required />";
                                            tBody += "</td>";


                                        } else {
                                            tBody += "<td>";
                                            tBody += "-";
                                            tBody += "</td>";

                                        }








                                        tBody += "</tr>";

                                    }




                                    $("#modalDispatchTbody").html(tBody);

                                    // var attachmentDiv="<table class='table table-bordered text-center'>";
                                    //  attachmentDiv+="<tbody>";


                                    //  if(resultData['data']['eway_bill']!=""){

                                    //     attachmentDiv+="<tr>";
                                    //     attachmentDiv+="<td>Eway Bill</td>";
                                    //     attachmentDiv+="<td><a target='_blank' href='"+getSpaceFilePath(resultData['data']['eway_bill'])+"' title='PDF'><i class='bx font-size-20 bxs-file-pdf'></i></a></td>";
                                    //     attachmentDiv+="</tr>";

                                    //  }

                                    //  if(resultData['data']['dispatch_detail'].length>0){

                                    //     for (var i = 0; i < resultData['data']['dispatch_detail'].length; i++) {

                                    //         if(resultData['data']['dispatch_detail'][i]!=""){

                                    //  attachmentDiv+="<tr>";
                                    //  attachmentDiv+="<td>Dispatch Detail</td>";
                                    //  attachmentDiv+="<td><a target='_blank' href='"+getSpaceFilePath(resultData['data']['dispatch_detail'][i])+"' title='PDF'><i class='bx font-size-20 bxs-file-pdf'></i></a></td>";
                                    //  attachmentDiv+="</tr>";

                                    //         }



                                    //     }


                                    //  }








                                    //  attachmentDiv+="</tbody>";
                                    //  attachmentDiv+="</table>";
                                    // $("#attachmentDiv").html(attachmentDiv);




                                    //  $("#modalInvoiceSalePersons").html(resultData['data']['sale_persons']);
                                    //  $("#modalInvoiceMRP").html(numberWithCommas(resultData['data']['total_mrp']));
                                    //  $("#modalInvoiceMRPMinusDiscount").html(numberWithCommas(resultData['data']['total_mrp_minus_disocunt']));
                                    //  //$("#modalInvoiceGSTPecentage").html(resultData['data']['gst_percentage']);
                                    //  $("#modalInvoiceGSTValue").html(numberWithCommas(resultData['data']['gst_tax']));
                                    // // $("#modalInvoiceDelievery").html(numberWithCommas(resultData['data']['delievery_charge']));
                                    //  $("#modalInvoiceTotalPayable").html(numberWithCommas(resultData['data']['total_payable']));



                                    //   $("#modalInvoiceChannelPartnerCreditDays").html(resultData['data']['channel_partner_credit_days']);

                                    //   $("#modalInvoiceChannelPartnerCreditLimit").html(numberWithCommas(resultData['data']['channel_partner_credit_limit']));
                                    //  $("#modalInvoiceChannelPartnerCreditPending").html(numberWithCommas(resultData['data']['channel_partner_pending_credit']));







                                } else {
                                    toastr["error"](resultData['msg']);
                                }

                            }
                        });
                    }
                }
            })





            // $("#invoice_box_number").val(noOFBox);

        }

        $("#invoice_courier_service_id").select2({
            ajax: {
                url: ajaxSearchCourier,
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
            placeholder: 'Search for a courier service ',
            dropdownParent: $("#modalDispatch")

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
            $('#formInvoiceDispatch').ajaxForm(options);
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
                if (responseText['dispatch_pdf'] != "") {
                    window.open(responseText['dispatch_pdf'], '_blank');
                }


                table.ajax.reload(null, false);
                $("#modalDispatch").modal('hide');


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
    </script>
    @include('../marketing/invoice/comman/script')

@endsection
