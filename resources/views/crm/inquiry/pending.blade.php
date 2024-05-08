@extends('layouts.main')
@section('title', $data['title'])
@section('content')
 @php
    $inquiryStatus=getInquiryStatus();
    @endphp



 <link href="{{ asset('assets/libs/summernote/summernote.min.css')}}" rel="stylesheet">


<link href="{{ asset('assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css')}}" rel="stylesheet" type="text/css">
<style type="text/css">

#datatable_info {
  color: black;
  font-size: 14px;
  font-weight: 500;
}

#datatable_length {
  margin-top: 10px;
  float: left;
}
#datatable_paginate {
  margin-top: 10px;
}

.note-hint-popover .popover-content .note-hint-group .note-hint-item {
  padding: 2px 4px !important;
  font-weight: 500;
  color: #535353;
}
.note-statusbar {
  display: none;
}
.extrasmallfont {
  font-size: 17px !important;
  vertical-align: middle;
}
.div-end-line {
  padding: 5px 0px;
  border-top: 1px solid #a2a2a2;
}

#inquiry_filter_following_date_time,
#inquiry_filter_search_type,
#inquiry_filter_search_value,
#inquiry_filter_stage_of_site,
#inquiry_filter_material_sent_type {
  padding: 0px 5px;
  border: 1px solid gainsboro;
  margin-bottom: 12px;
  margin-top: 12px;
}
.pdiv-202 {
  color: white !important;
  background-color: #e86bfa !important;
  opacity: 1;
  background-image: linear-gradient(
    to right,
    #e687de,
    #e687de 2px,
    #e86bfa 2px,
    #e86bfa
  ) !important;
  background-size: 4px 100% !important;
}
.update-message-text {
  white-space: pre-wrap;
}
.border-box {
  border: 0.1px solid #fefefe !important;
  border-radius: 6px;
  margin-top: 2px;
  padding-top: 2px;
  padding-left: 2px;
  background: white;
  padding: 1px 5px;
}
.btn-quotation {
  padding: 1px 3px !important;
  margin-bottom: 2px;
  text-transform: uppercase;
}
.input-followup-date-time {
  margin-bottom: 8px;
  background: #c9e5fe;
  padding: 0px 5px;
  border: 1px solid gainsboro;
}
.select-change-status {
  margin-bottom: 2px;
  background: #090a09;
  border: 0;
  padding: 0 5px;
  color: white;
  font-size: 14px;
  width: 84%;
}
.btn-detail {
  padding: 1px 3px !important;
  text-transform: uppercase;
}

.btn-edit-detail {
  padding: 1px 3px !important;
  text-transform: uppercase;
}

.btn-change-assigned {
  padding: 1px 3px !important;
  text-transform: uppercase;
}
table.dataTable.nowrap td {
  background: #f1f1f1;
}
tbody,
td,
tfoot,
tr {
  border-color: black;
}
.lable-inquiry-id {
  background: black;
  color: white;
  padding: 0 6px;
  border-radius: 5px;
}

.lable-inquiry-phone {
  background: white;
  color: black;
}

.lable-inquiry-quotation {
  background: white;
  color: black;
}

.table > :not(caption) > * > * {
  padding: 5px 6px !important;
}

body {
  /* Set "my-sec-counter" to 0 */
  counter-reset: inquiry-question-counter;
}

.inquiry-questions-lable::before {
  counter-increment: inquiry-question-counter;
  content: "" counter(inquiry-question-counter) ". ";
}
.has-no-followupdatetime {
  color: white;

  background-color: #ff8f8f;
  opacity: 1;
  background-size: 4px 4px;
  background-image: repeating-linear-gradient(
    45deg,
    #f74545 0,
    #f74545 0.4px,
    #ff8f8f 0,
    #ff8f8f 50%
  );
}
.expired-followupdatetime {
  color: white;

  background-color: #ff8f8f;
  opacity: 1;
  background-size: 4px 4px;
  background-image: repeating-linear-gradient(
    45deg,
    #f74545 0,
    #f74545 0.4px,
    #ff8f8f 0,
    #ff8f8f 50%
  );
}

#datatable
  .select2-container
  .select2-selection--single
  .select2-selection__rendered {
  line-height: 22px;
}

#datatable .select2-container .select2-selection--single {
  height: 22px;
}
#datatable
  .select2-container--default
  .select2-selection--single
  .select2-selection__arrow
  b {
  margin-top: -9px;
}
.createdicon {
  float: right;
  color: black;
}
.inquiry-status-btn {
  border: 0 !important;
}
.inquiry-status-btn:hover {
  color: white !important;
}

.status-tab-active {
  text-decoration: underline !important;
  font-size: 15px;
  border: 2px solid black !important;
}
.inquiry-comments-icon {
  font-size: 20px;
}
.inquiry-update-badge {
  color: white;
  background: #495057;
  border: 0.5px solid white;
}
.inquiry-update-text {
  overflow: hidden;
  text-overflow: ellipsis;
  /* white-space: nowrap; */
  max-width: 88%;
  white-space: pre-wrap;
}
.inquiry-update-time {
  width: 10%;
}

.inquiry-update-box {
  display: block;
  padding: 14px 16px;
  color: #74788d;
  -webkit-transition: all 0.4s;
  transition: all 0.4s;
  border-top: 1px solid #eff2f7;
  border-radius: 4px;
}
.inquiry-update-reply-box {
  display: block;
  padding: 14px 16px;
  color: #74788d;
  -webkit-transition: all 0.4s;
  transition: all 0.4s;
  border-top: 1px solid #eff2f7;
  border-radius: 4px;
}
.inquiry-avatar-xs {
  min-width: 2rem !important;
}
.hightlight-update {
  color: #50a5f1;
}
.hightlight-update > .inquiry-update-badge {
  background-color: #50a5f1;
}
.reply-box {
  display: none;
}

.seen-ul {
  padding: 0;
}

ul.list-unstyled.chat-list.seen-ul a {
  padding: 4px 10px;
}
.seen-ul li {
  background: white !important;
  padding: 0;
  font-size: 10px;
}
.tooltip-inner .seen-avatar {
  background-color: rgba(85, 110, 230, 0.25) !important;
}
#datatable_processing {
  background: black;

  z-index: 9;
  top: 200px;
}
div.dataTables_wrapper div.dataTables_processing {
  width: auto;
  padding: 0;
}

#datatable_filter{
  display: none;
}


  @foreach($inquiryStatus as $key=>$value)



  .inquiry-status-lable-color-{{$value['id']}}{
    background: {{$value['background']}};
    color:{{$value['color']}};
   }

  @endforeach


</style>

                <div class="page-content">
                    <div class="container-fluid">
                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0 font-size-18">{{$data['is_verified_lable']}} Inquiry </h4>







                                    <div class="page-title-right">


                                      <a href="{{route('inquiry')}}" class="btn btn-dark waves-effect waves-light">Inquiry</a>

                                      @if($data['isChannelPartner']==0)
                                        <a href="{{route('inquiry.pending')}}?is_verified=0" class="btn btn-primary waves-effect waves-light"  role="button">Pending Request</a>


                                        <a href="{{route('inquiry.pending')}}?is_verified=2"  class="btn btn-primary waves-effect waves-light"  role="button">Rejected Request</a>

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














                                        <table id="datatable" class="table dt-responsive  nowrap w-100">
                                            <thead>
                                            <tr>

                                                <th>Id - Name/Mobile/Address</th>
                                                <th>
@if($data['isArchitect']==1)
                                                CreatedBy/Source/Architect
@else
  CreatedBy/Source/Architect/Electrician
@endif
                                            </th>
                                                <th>
                                                    @if($data['isArchitect']==1)
                                                     Stage of site
                                                    @else
                                                     Stage of site/Quotation/Follow Up
                                                    @endif

                                              </th>
                                                <th>
                                                    @if($data['isArchitect']==1)
                                                    Status
                                                   @else
                                                      Assigned/Status/Detail

                                                   @endif
                                                </th>

                                            </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>  <!-- end col -->
                        </div><!--̧̧end row-->


                    </div>
                    <!-- container-fluid -->
                </div>
                <!-- End Page-content -->


                <div class="modal fade" id="modalDetail" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" role="dialog" aria-labelledby="modalDetailLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalDetailLabel" >Detail </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>

                                    <input type="hidden" name="detail_inquiry_id" id="detail_inquiry_id">





                                        <div class="modal-body" id="modelBodyDetail" >

                                               <ul class="nav nav-tabs nav-tabs-custom nav-justified" role="tablist">
                                            <li class="nav-item">
                                                <a class="nav-link active" data-bs-toggle="tab" href="#inquiry_update" onclick="loadDetail('inquiry_update')" role="tab">
                                                    <span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
                                                    <span class="d-none d-sm-block">Update</span>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" data-bs-toggle="tab" href="#inquiry_files" onclick="loadDetail('inquiry_files')" role="tab">
                                                    <span class="d-block d-sm-none"><i class="far fa-user"></i></span>
                                                    <span class="d-none d-sm-block">Files</span>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" data-bs-toggle="tab" href="#inquiry_log" onclick="loadDetail('inquiry_log')" role="tab">
                                                    <span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
                                                    <span class="d-none d-sm-block">Activity Log</span>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" data-bs-toggle="tab" href="#inquiry_answer" onclick="loadDetail('inquiry_answer')" role="tab">
                                                    <span class="d-block d-sm-none"><i class="fas fa-cog"></i></span>
                                                    <span class="d-none d-sm-block">Answer</span>
                                                </a>
                                            </li>
                                        </ul>

                                             <div class="tab-content p-3 text-muted">
                                            <div class="tab-pane active" id="inquiry_update"  role="tabpanel">

                                            </div>
                                            <div class="tab-pane" id="inquiry_files" role="tabpanel">

                                            </div>
                                            <div class="tab-pane" id="inquiry_log" role="tabpanel">

                                            </div>
                                            <div class="tab-pane" id="inquiry_answer" role="tabpanel">

                                            </div>
                                        </div>



                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>

                                        </div>


                                </div>
                            </div>
                        </div>


                                         <!-- start inquiry status change model-->
                        <div class="modal fade" id="modalStatusChange" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" role="dialog" aria-labelledby="modalStatusChangeLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" > Change Inquiry Status </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>

                                        <form  id="formInquiryStatusChange" action="{{route('inquiry.answer.save')}}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate >
                                        @csrf

                                         <div class="col-md-12 text-center loadingcls">






                                            <button type="button" class="btn btn-light waves-effect">
                                                <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading
                                            </button>


                                               </div>

                                        <div class="modal-body" id="inquiry_question_body">
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                            <button id="submit" type="submit" class="btn btn-primary">Save</button>
                                        </div>
                                    </form>


                                </div>
                            </div>
                        </div>


                         <div class="modal fade" id="modalAssignedToChange" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" role="dialog" aria-labelledby="modalAssignedToChangeLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" > Assigned To </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>

                                        <form  id="formAssignedTo" action="{{route('inquiry.assignedto.save')}}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate >
                                        @csrf

                                        <input type="hidden" name="assigned_to_inquiry_id" id="assigned_to_inquiry_id" >

                                         <div class="col-md-12 text-center loadingcls">






                                            <button type="button" class="btn btn-light waves-effect">
                                                <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading
                                            </button>


                                        </div>

                                        <div class="modal-body" >

                                               <div class="row">
                                                                <div class="col-md-12">

                                                                    <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                                        <label class="form-label">Assigned To </label>
                                                                        <select class="form-control select2-ajax" id="inquiry_change_assigned_to" name="inquiry_change_assigned_to" required >
                                                                        </select>
                                                                        <div class="invalid-feedback">
                                                                            Please select assigned to.
                                                                        </div>
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

                          <div class="modal fade" id="modalQuotation" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" role="dialog" aria-labelledby="modalQuotationLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalQuotationLabel" >Quotation</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>

                                        <form  id="formQuotation" action="{{route('inquiry.quotation.save')}}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate >
                                        @csrf

                                        <input type="hidden" name="quotation_inquiry_id" id="quotation_inquiry_id" >

                                         <div class="col-md-12 text-center loadingcls">






                                            <button type="button" class="btn btn-light waves-effect">
                                                <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading
                                            </button>


                                        </div>

                                        <div class="modal-body" >


                                              <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="mb-3">
                                                                        <label for="inquiry_quotation" class="form-label">Quotation <code class="highlighter-rouge"></code></label>
                                                                        <input type="file" class="form-control" id="inquiry_quotation" name="inquiry_quotation"
                                                                              value=""  >
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="mb-3">
                                                                        <label for="inquiry_quotation_amount" class="form-label">Quotation Amount <code class="highlighter-rouge">*</code></label>
                                                                        <input type="number" class="form-control" id="inquiry_quotation_amount" name="inquiry_quotation_amount"
                                                                               placeholder="Quotation Amount" value="" required>
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

                            <div class="modal fade" id="modalBillingInvoice" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" role="dialog" aria-labelledby="modalBillingInvoiceLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalBillingInvoiceLabel" >Billing Invoice</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body" id="modalBillingInvoiceBody" >

                                    </div>
                                </div>
                            </div>
                        </div>




                        <div class="modal fade" id="modalBilling" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" role="dialog" aria-labelledby="modalBillingLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalBillingLabel" >Billing</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>

                                        <form  id="formBilling" action="{{route('inquiry.billing.save')}}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate >
                                        @csrf

                                        <input type="hidden" name="billing_inquiry_id" id="billing_inquiry_id" >

                                         <div class="col-md-12 text-center loadingcls">






                                            <button type="button" class="btn btn-light waves-effect">
                                                <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading
                                            </button>


                                        </div>

                                        <div class="modal-body" >


                                              <div class="row">
                                                                <div class="col-md-12">
                                                                    <div class="mb-3">
                                                                        <label for="inquiry_billing_invoice" class="form-label">Billing Invoice <code class="highlighter-rouge"></code></label>
                                                                        <input multiple type="file" class="form-control" id="inquiry_billing_invoice" name="inquiry_billing_invoice[]"
                                                                              value=""   >
                                                                    </div>
                                                                </div>

                                                                @if($data['isAdminOrCompanyAdmin']==1)

                                                                  <div class="col-md-12" id="div_inquiry_billing_amount" >
                                                                    <div class="mb-3">
                                                                        <label for="inquiry_quotation_amount" class="form-label">Billing Amount <code class="highlighter-rouge">*</code></label>
                                                                        <input type="number" class="form-control" id="inquiry_billing_amount" name="inquiry_billing_amount"
                                                                               placeholder="Billing Amount" value="" required>
                                                                    </div>
                                                                </div>
                                                                @endif

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
<script src="{{ asset('assets/libs/parsleyjs/parsley.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/form-validation.init.js') }}"></script>
<script src="{{ asset('assets/js/pages/jquery.form.js') }}"></script>
 <script src="{{ asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>

     <!-- include summernote css/js -->

<script src="{{ asset('assets/libs/summernote/summernote.min.js')}}"></script>


<script type="text/javascript">

var ajaxURL='{{route('inquiry.pending.ajax')}}';
var ajaxURLAcceptReject='{{route('inquiry.pending.accept.reject')}}';

var csrfToken=$("[name=_token").val();
var inquiryPageLength= getCookie('inquiryPageLength')!==undefined?getCookie('inquiryPageLength'):10;

var isVerified='{{$data['is_verified']}}';

var table = $('#datatable').DataTable({
    //  "aoColumnDefs": [{ "bSortable": false, "aTargets": [5] }],
    "dom": '<"top"i>rt<"bottom"flp><"clear">',
    "scrollY": function() {
        return $(window).height() - 400;
    },
    "searching": false,
    "scrollCollapse": true,
    "order": [
        [0, 'desc']
    ],
    "processing": true,
    "serverSide": true,
    "language": {
        "processing": '<button type="button" class="btn btn-dark waves-effect waves-light"><i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i> Processing...</button>'
    },
    "pageLength": parseInt(inquiryPageLength),
    "ajax": {
        "url": ajaxURL,
        "type": "POST",
        "data": {
            "_token": csrfToken,
            "is_verified": isVerified
        }



    },
    "aoColumns": [{
            "mData": "first_name"
        },
        {
            "mData": "created_by"
        },
        {
            "mData": "follow_up"
        },
        {
            "mData": "status"
        }
    ],
    "drawCallback": function() {



        $(".input-followup-time").select2();




        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });




    }
});

function verifyInquiry(inquiryId,type){
  var alretLableName="";
  var alretLableSuccessName="";
  if(type==1){
       alretLableName="Yes, accept it !"
       alretLableSuccessName="Inquiry Verified";
    }else if(type==2){
       alretLableName="Yes, reject it !"
         alretLableSuccessName="Inquiry Rejected";
    }



   Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: !0,
        confirmButtonText: alretLableName,
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
            type: 'POST',
            url: ajaxURLAcceptReject,
            data: {
                "_token": csrfToken,
                "inquiry_id": inquiryId,
                "type": type,


            },
            success: function(resultData) {
                if (resultData['status'] == 1) {

                  table.ajax.reload(null, false);
                   t()



                } else {

                    toastr["error"](resultData['msg']);

                }
            }
        });





            })
        },
    }).then(function(t) {

        if (t.value === true) {



            Swal.fire({
                title: alretLableSuccessName,
                text: "Your record has been updated.",
                icon: "success"
            });


        }

    });





}
</script>
@endsection