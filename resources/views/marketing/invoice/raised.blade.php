@extends('layouts.main')
@section('title', $data['title'])
@section('content')

<link href="{{ asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css') }}" rel="stylesheet" />

<link href="{{ asset('assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}" rel="stylesheet" type="text/css">
<style type="text/css">
    .section_pack {
        border: 1px solid gainsboro;
        padding: 18px;
    }
</style>


<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Challan Raised

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
                                        <th>Delievery Challan Status</th>

                                        <th>Mark Packed</th>


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


<div class="modal fade" id="modalOrder" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" role="dialog" aria-labelledby="modalOrderLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"> </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formInvoice" action="{{route('invoice.markaspacked')}}" method="POST" class="needs-validation" novalidate>
                @csrf
                <input type="hidden" name="invoice_packed_invoice_id" id="invoice_packed_invoice_id" />

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

                                                    <th>QTY</th>
                                                    <th>Pending<br>QTY</th>

                                                    <th>Packed <br> QTY </th>


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

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="section_pack">
                        <div class="row ">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="invoice_number" class="form-label">Department Name</label>
                                    <input type="text" class="form-control" id="invoice_packed_department_name" name="invoice_packed_department_name" placeholder="" value="">


                                </div>
                            </div>

                        </div>


                        <div class="row section_invoice">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="invoice_packed_packed_date" class="form-label">Packed Date</label>

                                    <div class="input-group" id="packed_date">
                                        <input type="text" class="form-control" value="{{date('Y-m-d')}}" placeholder="yyyy-mm-dd" data-date-format="yyyy-mm-dd" data-date-container='#invoice_packed_packed_date' data-provide="datepicker" data-date-autoclose="true" required name="invoice_packed_packed_date">


                                    </div><!-- input-group -->





                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label for="invoice_number" class="form-label">Box No</label>
                                    <input type="text" class="form-control" id="invoice_packed_sticker_box_no" name="invoice_packed_sticker_box_no" placeholder="" value="" required readonly>


                                </div>
                            </div>



                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label for="invoice_number" class="form-label">Weight(in gm)</label>
                                    <input type="number" class="form-control" id="invoice_packed_total_weight" name="invoice_packed_total_weight" placeholder="" value="">


                                </div>
                            </div>




                        </div>


                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button id="submitInvoice" type="submit" class="btn btn-primary">Pack</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- End Page-content -->

@include('../marketing/invoice/comman/detail')



@csrf
@endsection('content')
@section('custom-scripts')

<script src="{{ asset('assets/js/pages/form-validation.init.js') }}"></script>
<script src="{{ asset('assets/js/pages/jquery.form.js') }}"></script>

<script src="{{ asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js') }}"></script>



<script src="{{ asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>


<script src="{{ asset('assets/libs/parsleyjs/parsley.min.js') }}"></script>
<script type="text/javascript">
    var ajaxURL = "{{route('marketing.orders.delivery.challan.raised.ajax')}}";
    var ajaxInvoiceDetail = "{{route('marketing.orders.delivery.challan.detail')}}";
    var ajaxInvoiceMarkAsPacked = "{{route('marketing.orders.delivery.challan.markaspacked')}}";

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
                "mData": "action_mark_packed"
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


    function resetInputForm() {

        $('#formInvoice').trigger("reset");
        $("#formInvoice").removeClass('was-validated');
        $("#submitInvoice").prop('disabled', false);


    }





    function doMarkAsPacked(id) {

        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: !0,
            confirmButtonText: "Yes, mark as packed !",
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
                        url: ajaxInvoiceMarkAsPacked + "?id=" + id,
                        success: function(resultData) {

                            if (resultData['status'] == 1) {

                                table.ajax.reload(null, false);;
                                t()



                            }




                        }
                    });



                })
            },
        }).then(function(t) {

            if (t.value === true) {



                Swal.fire({
                    title: "Mark as packed!",
                    text: "Your record has been updated.",
                    icon: "success"
                });


            }

        });

    }









    $(document).ready(function() {
        var options = {
            beforeSubmit: showRequest1, // pre-submit callback
            success: showResponse1 // post-submit callback

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

    function showRequest1(formData, jqForm, options) {

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
    function showResponse1(responseText, statusText, xhr, $form) {


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
</script>
@include('../marketing/invoice/comman/script')

@endsection