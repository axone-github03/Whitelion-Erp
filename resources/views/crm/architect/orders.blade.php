@extends('layouts.main')
@section('title', $data['title'])
@section('content')
 <link href="{{ asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css') }}" rel="stylesheet" />
 <style type="text/css">

     .raise-query-btn{
        color: white !important;
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

                                                <th>#Id</th>
                                                <th>Date & Time</th>
                                                <th>Total Point</th>

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



<div class="modal fade" id="modalOrderPreviw" data-bs-backdrop="static"  tabindex="-1" role="dialog" aria-labelledby="modalOrderLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
   <div class="modal-content">
   <div class="modal-header">
    <h5 class="modal-title" > </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
 <div class="modal-body">
 </div>
</div>
</div>
</div>
<div class="modal fade" id="modalRaiseQuery" data-bs-backdrop="static"  tabindex="-1" role="dialog" aria-labelledby="modalRaiseQueryLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
   <div class="modal-content">
   <div class="modal-header">
    <h5 class="modal-title" > Raise Query </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
 <div class="modal-body">

       <form id="formSendQuery" class="custom-validation" action="{{route('crm.user.send.query')}}" method="POST" enctype="multipart/form-data"  >

   @csrf
        <input type="hidden" name="gift_product_order_query_order_id" id="gift_product_order_query_order_id">

           <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="gift_product_order_query_title" class="form-label">Title</label>
                                                <input type="text" class="form-control" id="gift_product_order_query_title" name="gift_product_order_query_title"
                                                    placeholder="Title" value="" required>


                                            </div>
                                        </div>

                                    </div>

                                        <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="gift_product_order_query_description" class="form-label">Description</label>
                                                <textarea type="text" class="form-control" id="gift_product_order_query_description" name="gift_product_order_query_description"
                                                    placeholder="Description" value="" required></textarea>


                                            </div>
                                        </div>

                                    </div>

                                      <div class="d-flex flex-wrap gap-2">
                                        <button  id="QuerySubmitBtn" type="submit" class="btn btn-primary waves-effect waves-light">
                                            Send
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



    @csrf
@endsection('content')



@section('custom-scripts')
<script src="{{ asset('assets/js/pages/form-validation.init.js') }}"></script>
<script src="{{ asset('assets/js/pages/jquery.form.js') }}"></script>
 <script src="{{ asset('assets/libs/parsleyjs/parsley.min.js') }}"></script>
<script src="{{ asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js') }}"></script>
<script type="text/javascript">


    var ajaxURL='{{route('architect.orders.ajax')}}';
    var ajaxOrderDetail='{{route('architect.order.detail')}}';
    var csrfToken=$("[name=_token").val();


var table=$('#datatable').DataTable({
  "aoColumnDefs": [{ "bSortable": false, "aTargets": [] }],
  "order":[[ 0, 'desc' ]],
  "processing": true,
  "serverSide": true,
  "ajax": {
    "url": ajaxURL,
    "type": "POST",
     "data": {
        "_token": csrfToken,
        }


  },
  "aoColumns" : [

    {"mData" : "id"},
    {"mData" : "created_at"},
    {"mData" : "total_point_value"},
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


function ViewOrder(id){

 $.ajax({
            type: 'GET',
            url: ajaxOrderDetail + "?id=" + id,
            success: function(responseText) {
                if(responseText['status']==1){

          $("#modalOrderPreviw").modal('show');
         $("#modalOrderPreviw .modal-body").html(responseText['preview']);


                }else{
                     if(typeof responseText['data'] !== "undefined"){

            var size = Object.keys(responseText['data']).length;
            if(size>0){

                for (var [key, value] of Object.entries(responseText['data'])) {

                  toastr["error"](value);
               }

            }

         }else{
            toastr["error"](responseText['msg']);
         }
                }

            }
        });

}

function RaiseQuery(id){
     $("#gift_product_order_query_order_id").val(id);
     $("#modalRaiseQuery").modal('show');
     $("#modalRaiseQuery .modal-title").html("Raise Query of order #"+id);


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
    $('#formSendQuery').ajaxForm(options);
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
    $("#QuerySubmitBtn").html('Sending...');
    $("#QuerySubmitBtn").prop('disabled',true);
    return true;
}

// post-submit callback
function showResponse(responseText, statusText, xhr, $form) {

    $("#QuerySubmitBtn").prop('disabled',false);


    if (responseText['status'] == 1) {
        toastr["success"](responseText['msg']);
        table.ajax.reload( null, false );
        $("#modalRaiseQuery").modal('hide');

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