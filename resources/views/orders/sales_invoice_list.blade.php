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
                                    <h4 class="mb-sm-0 font-size-18">Invoice (order id #{{$data['order_id']}})

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
                                                <th>Invoice Status</th>

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


                <div class="offcanvas offcanvas-end" tabindex="-1" id="canvasEditInvoice" aria-labelledby="canvasEditInvoiceLable">
                                            <div class="offcanvas-header">
                                              <h5 id="canvasEditInvoiceLable">Update Invoice File</h5>
                                              <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                            </div>
                                            <div class="offcanvas-body">
                                                 <form enctype="multipart/form-data" id="formInvoice" class="custom-validation" action="{{route('orders.sales.invoice.file.update')}}" method="POST"  >

                                                      @csrf

                            <input type="hidden" name="invoice_id" id="invoice_id"  >


                                <div class="row">

                                         <div class="col-lg-12">
                                                    <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                        <label class="form-label">

                                                            New Invoice File (PDF/Doc/Image)
                                                        </label>
                                                        <input type="file" accept="@php
                                                    $fileTypes=acceptFileTypes('order.invoice','server');
                                                    echo implode(',',$fileTypes); @endphp"  class="form-control"  id="invoice_file" name="invoice_file" required >



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


<div class="modal fade" id="modalInvoice" data-bs-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="modalInvoiceLabel" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" > </h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
 <div class="modal-body">
    <div class="row">
                            <div class="col-lg-12">
                                <div class="">
                                    <div class="">
                                        <div class="invoice-title">
                                            <h4 class="float-end font-size-16" id="modalInvoiceIdLabel" ></h4>

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
               <span id="modalInvoiceChannelPartnerEmailLabel" > </span></b>
            </span>
         </td>
      </tr>
      <tr>
         <td>
            <span class="font-weight-bolder">
               <b><i class="bx bx-phone"></i>
                <span id="modalInvoiceChannelPartnerPhoneLabel" > </span></b>
            </span>
         </td>
      </tr>
      <tr>
         <td class="pr-1 pt-1">Company Name:</td>
         <td class="pt-1"><b><span class="font-weight-bolder" id="modalInvoiceChannelPartnerFirmName" ></span></b></td>
      </tr>
      <tr>
         <td class="pr-1">Name:</td>
         <td><b><span class="font-weight-bolder" id="modalInvoiceChannelPartnerName"></span></b></td>
      </tr>
      <tr>
         <td class="pr-1">Type:</td>
         <td><b><span class="font-weight-bolder" id="modalInvoiceChannelPartnerType" ></span></b></td>
      </tr>
      <tr>
         <td class="pr-1">GST Number:</td>
         <td><b><span class="font-weight-bolder" id="modalInvoiceChannelPartnerGSTNumber" ></span></b></td>
      </tr>
      <tr>
         <td class="pr-1">Payment Mode:</td>
         <td><b><span class="font-weight-bolder" id="modalInvoiceChannelPartnerPaymentMode" ></span></b></td>
      </tr>
      <tr id="divModalInvoiceChannelPartnerCreditDays" >
         <td class="pr-1">Credit Days:</td>
         <td><b><span class="font-weight-bolder" id="modalInvoiceChannelPartnerCreditDays" ></span></b></td>
      </tr>
      <tr id="divModalInvoiceChannelPartnerCreditLimit" >
         <td class="pr-1">Credit Limit:</td>
         <td><b><i class="fas fa-rupee-sign"></i><span class="font-weight-bolder" id="modalInvoiceChannelPartnerCreditLimit"  ></span></b></td>
      </tr>
      <tr id="divModalInvoiceChannelPartnerCreditPending" >
         <td class="pr-1">Credit Pending:</td>
         <td><b><i class="fas fa-rupee-sign"></i><span class="font-weight-bolder" id="modalInvoiceChannelPartnerCreditPending" ></span></b></td>
      </tr>

   </tbody>
</table>
                                                </address>
                                            </div>
                                            <div class="col-sm-6 mt-3 text-sm-end">
                                                <address>
                                                    <strong>Invoice Date</strong><br>
                                                    <span id="modalInvoiceDateTimeLabel"></span><br><br>
                                                </address>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <address>
                                                    <strong>Billed To</strong><br>
                                                     <p id="modalInvoiceChannelPartnerBillAddress"></p>

                                                </address>
                                            </div>
                                            <div class="col-sm-6 text-sm-end">
                                                <address class="mt-2 mt-sm-0">
                                                    <strong>Shipped To</strong><br>
                                                     <p id="modalInvoiceChannelPartnerDAddress"></p>
                                                </address>
                                            </div>
                                        </div>

                                        <div class="py-2 mt-3">
                                            <h3 class="font-size-15 fw-bold">Invoice summary</h3>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table align-middle table-nowrap mb-0">
                                                <thead  class="table-light">
                                                    <tr>
                                                        <th style="width: 70px;">SR<br>No.</th>
                                                        <th>Product<br>Image</th>
                                                        <th>Product<br>Brand</th>
                                                        <th>Product<br>Type</th>
                                                        <th>Total<br>Amount</th>
                                                        <th>QTY</th>




                                                    </tr>
                                                </thead>
                                                <tbody id="modalInvoiceTbody" >

                                                </tbody>
                                            </table>
                                        </div>
                                      <div class="row">
                                            <div class="col-sm-6">
                                                <address>
                                                    <br>
                                                    <strong>Sales Persons:</strong>
                                                    <p id="modalInvoiceSalePersons" ></p>




                                                </address>
                                                <div id="attachmentDiv">


                                                </div>
                                            </div>
                                            <div class="col-sm-6 text-sm-end">

                                                  <table class="float-end">
   <tbody>
      <tr>
         <td class="pr-1 pt-1">Total MRP: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
         <td class="pt-1"><b><i class="fas fa-rupee-sign"></i><span class="font-weight-bolder" id="modalInvoiceMRP"  ></span></b></td>
      </tr>
      <tr>
         <td class="pr-1">Ex. GST (Invoice value): &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
         <td><b><i class="fas fa-rupee-sign"></i><span class="font-weight-bolder" id="modalInvoiceMRPMinusDiscount"  ></span></b></td>
      </tr>
      <tr>
         <td class="pr-1">Estimated Tax (GST) - (<span id="modalInvoiceGSTPecentage"  ></span>%): &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
         <td><b><i class="fas fa-rupee-sign"></i><span class="font-weight-bolder" id="modalInvoiceGSTValue" ></span></b></td>
      </tr>
      <tr>
         <td class="pr-1">Delivery Charges: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
         <td><b><i class="fas fa-rupee-sign"></i><span class="font-weight-bolder" id="modalInvoiceDelievery" ></span></b></td>
      </tr>
      <tr>
         <td class="pr-1">Total: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
         <td><b><i class="fas fa-rupee-sign"></i><span class="font-weight-bolder" id="modalInvoiceTotalPayable"  ></span></b></td>
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
                                                      </div>
                                                </div>
</div>




    @csrf
@endsection('content')
@section('custom-scripts')

<script src="{{ asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js') }}"></script>

<script src="{{ asset('assets/libs/parsleyjs/parsley.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/form-validation.init.js') }}"></script>
<script src="{{ asset('assets/js/pages/jquery.form.js') }}"></script>


<script type="text/javascript">


    var ajaxURL='{{route('orders.sales.invoice.list.ajax')}}';
    var ajaxInvoiceDetail='{{route('orders.sales.invoice.detail')}}';
    var csrfToken=$("[name=_token").val();
    var orderId={{$data['order_id']}};


var table=$('#datatable').DataTable({
  "aoColumnDefs": [{ "bSortable": false, "aTargets": [5] }],
  "order":[[ 0, 'desc' ]],
  "processing": true,
  "serverSide": true,
  "ajax": {
    "url": ajaxURL+"?order_id="+orderId,
    "type": "POST",
     "data": {
        "_token": csrfToken,
        }


  },
  "aoColumns" : [

    {"mData" : "detail"},
    {"mData" : "channel_partner"},
    {"mData" : "sale_persons"},
    {"mData" : "payment_detail"},
    {"mData" : "status"},
    {"mData" : "action"}

  ],
  "drawCallback":function(){

    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
  return new bootstrap.Tooltip(tooltipTriggerEl)
})

  }
});

function ViewInvoice(id){

 $.ajax({
            type: 'GET',
            url: ajaxInvoiceDetail + "?invoice_id=" + id,
            success: function(resultData) {
                if(resultData['status']==1){

                 $("#modalInvoice").modal('show');
                 $("#modalInvoiceIdLabel").html("Invoice #"+id);
                 $("#modalInvoiceDateTimeLabel").html(resultData['data']['display_date_time']);

                 $("#modalInvoiceChannelPartnerEmailLabel").html(resultData['data']['channel_partner_email']);
                 $("#modalInvoiceChannelPartnerPhoneLabel").html(resultData['data']['channel_partner_dialing_code']+' '+resultData['data']['channel_partner_phone_number']);

                 $("#modalInvoiceChannelPartnerFirmName").html(resultData['data']['channel_partner_firm_name']);

                 $("#modalInvoiceChannelPartnerName").html(resultData['data']['channel_partner_first_name']+' '+resultData['data']['channel_partner_last_name']);

                 $("#modalInvoiceChannelPartnerType").html(resultData['data']['channel_partner_type_name']);

                 $("#modalInvoiceChannelPartnerGSTNumber").html(resultData['data']['gst_number']);

                 $("#modalInvoiceChannelPartnerPaymentMode").html(resultData['data']['payment_mode_lable']);

                  if(resultData['data']['payment_mode']==2){

                    $("#divModalInvoiceChannelPartnerCreditDays").show();
                    $("#divModalInvoiceChannelPartnerCreditLimit").show();
                    $("#divModalInvoiceChannelPartnerCreditPending").show();

                 }else{

                    $("#divModalInvoiceChannelPartnerCreditDays").hide();
                    $("#divModalInvoiceChannelPartnerCreditLimit").hide();
                    $("#divModalInvoiceChannelPartnerCreditPending").hide();

                 }

                 var billAddress=resultData['data']['bill_address_line1'];
                 if(resultData['data']['bill_address_line2']!=""){
                    billAddress+="<br>"+resultData['data']['bill_address_line2'];
                 }
                 billAddress+="<br>"+resultData['data']['d_pincode'];
                 billAddress+="<br>"+resultData['data']['d_city_name']+", "+resultData['data']['d_state_name']+", "+resultData['data']['d_country_name'];

                 $("#modalInvoiceChannelPartnerBillAddress").html(billAddress);


                  var dAddress=resultData['data']['d_address_line1'];
                 if(resultData['data']['d_address_line2']!=""){
                    dAddress+="<br>"+resultData['data']['d_address_line2'];
                 }
                 dAddress+="<br>"+resultData['data']['d_pincode'];
                 dAddress+="<br>"+resultData['data']['d_city_name']+", "+resultData['data']['d_state_name']+", "+resultData['data']['d_country_name'];

                 $("#modalInvoiceChannelPartnerDAddress").html(dAddress);

                 var tBody="";
                $("#modalInvoiceTbody").html(tBody);
                 for(var i=0;i<resultData['data']['items'].length;i++){
                    tBody+="<tr>";

                    tBody+="<td>";
                    tBody+=""+(i+1)+"";
                    tBody+="</td>";

                    tBody+="<td>";

                      tBody+='<img src="'+getSpaceFilePath(resultData['data']['items'][i]['product_image'])+'" alt="logo" height="50">';

                    tBody+="</td>";

                      tBody+="<td>";
                    tBody+=""+resultData['data']['items'][i]['product_brand_name']+"";
                    tBody+="</td>";

                    tBody+="<td>";
                    tBody+=""+resultData['data']['items'][i]['product_code_name']+"";
                    tBody+="</td>";

                    tBody+="<td>";
                    tBody+="<i class='fas fa-rupee-sign'></i>"+numberWithCommas(resultData['data']['items'][i]['total_mrp'])+"";
                    tBody+="</td>";

                    tBody+="<td>";
                    tBody+=""+resultData['data']['items'][i]['qty']+"";
                    tBody+="</td>";








                    tBody+="</tr>";

                 }




                 $("#modalInvoiceTbody").html(tBody);

                 var attachmentDiv="<table class='table table-bordered text-center'>";
                 attachmentDiv+="<tbody>";


                 if(resultData['data']['eway_bill']!=""){

                    attachmentDiv+="<tr>";
                    attachmentDiv+="<td>Eway Bill</td>";
                    attachmentDiv+="<td><a target='_blank' href='"+getSpaceFilePath(resultData['data']['eway_bill'])+"' title='PDF'><i class='bx font-size-20 bxs-file-pdf'></i></a></td>";
                    attachmentDiv+="</tr>";

                 }

                 if(resultData['data']['dispatch_detail'].length>0){

                    for (var i = 0; i < resultData['data']['dispatch_detail'].length; i++) {

                        if(resultData['data']['dispatch_detail'][i]!=""){

                 attachmentDiv+="<tr>";
                 attachmentDiv+="<td>Dispatch Detail</td>";
                 attachmentDiv+="<td><a target='_blank' href='"+getSpaceFilePath(resultData['data']['dispatch_detail'][i])+"' title='PDF'><i class='bx font-size-20 bxs-file-pdf'></i></a></td>";
                 attachmentDiv+="</tr>";

                        }



                    }


                 }








                 attachmentDiv+="</tbody>";
                 attachmentDiv+="</table>";
                $("#attachmentDiv").html(attachmentDiv);




                 $("#modalInvoiceSalePersons").html(resultData['data']['sale_persons']);
                 $("#modalInvoiceMRP").html(numberWithCommas(resultData['data']['total_mrp']));
                 $("#modalInvoiceMRPMinusDiscount").html(numberWithCommas(resultData['data']['total_mrp_minus_disocunt']));
                 $("#modalInvoiceGSTPecentage").html(resultData['data']['gst_percentage']);
                 $("#modalInvoiceGSTValue").html(numberWithCommas(resultData['data']['gst_tax']));
                 $("#modalInvoiceDelievery").html(numberWithCommas(resultData['data']['delievery_charge']));
                 $("#modalInvoiceTotalPayable").html(numberWithCommas(resultData['data']['total_payable']));



                  $("#modalInvoiceChannelPartnerCreditDays").html(resultData['data']['channel_partner_credit_days']);
                  $("#modalInvoiceChannelPartnerCreditLimit").html(numberWithCommas(resultData['data']['channel_partner_credit_limit']));
                 $("#modalInvoiceChannelPartnerCreditPending").html(numberWithCommas(resultData['data']['channel_partner_pending_credit']));







                }else{
                     toastr["error"](resultData['msg']);
                }

            }
        });

}

function EditInvoice(id) {
  $("#canvasEditInvoice").offcanvas('show');
  $("#formInvoice").trigger('reset');
  $("#invoice_id").val(id);
  $("#formInvoice").removeClass('was-validated');
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
         table.ajax.reload( null, false );
       $("#canvasEditInvoice").offcanvas('hide');


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
@endsection