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
                                    <h4 class="mb-sm-0 font-size-18">

                                        Gift Order Queries

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
                                                <th>Order Id</th>
                                                <th>Status</th>
                                                <th>Need to respond</th>
                                                <th>Detail</th>



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


    @csrf
@endsection('content')
@section('custom-scripts')


<script src="{{ asset('assets/libs/parsleyjs/parsley.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/form-validation.init.js') }}"></script>
<script src="{{ asset('assets/js/pages/jquery.form.js') }}"></script>
<script type="text/javascript">


    var ajaxURL='{{route('gift.product.orders.query.ajax')}}';
    var ajaxOrderDetail='{{route('gift.product.order.detail')}}';
    var ajaxSearchCourier='{{route('search.courier')}}';
    var csrfToken=$("[name=_token").val();



    $("#order_courier_service_id").select2({
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
    dropdownParent: $("#canvasDispatchOrder")

});


var giftProductOrdersPageLength= getCookie('giftProductOrdersPageLength')!==undefined?getCookie('giftProductOrdersPageLength'):10;


var table=$('#datatable').DataTable({
  "aoColumnDefs": [{ "bSortable": false, "aTargets": [4,5] }],
  "order":[[ 0, 'desc' ]],
  "processing": true,
  "serverSide": true,
  "pageLength": giftProductOrdersPageLength,
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
    {"mData" : "gift_product_order_id"},
    {"mData" : "status"},
    {"mData" : "need_to_respond"},
    {"mData" : "action"},


  ],
  "drawCallback":function(){

    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
          return new bootstrap.Tooltip(tooltipTriggerEl)
        });

  }
});


$('#datatable').on( 'length.dt', function ( e, settings, len ) {

    setCookie('giftProductOrdersPageLength',len,100);


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


function doMarkAsDispatch(id) {

 $("#canvasDispatchOrder").offcanvas('show');
 $("#formOrderDispatch").trigger('reset');
 $("#order_id").val(id);
 $("#canvasDispatchOrderLable").html("Dispatch for order id #"+id);



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
    $('#formOrderDispatch').ajaxForm(options);
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
       $("#canvasDispatchOrder").offcanvas('hide');


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


var currentURL=window.location.href;
var loadedURLLink = $('.userscomman a[href="'+currentURL+'"]');
$(loadedURLLink).removeClass('btn-outline-primary');
$(loadedURLLink).addClass('btn-primary');
</script>
@endsection