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
                                    <h4 class="mb-sm-0 font-size-18">Gift Products</h4>

                                    <div class="page-title-right">
                                       @include('../crm/architect/comman/btn_cart')
                                    </div>


                                </div>


                            </div>
                        </div>
                        <!-- end page title -->
                        <!-- start row -->

                        <div id="cartDetailView"  ></div>



                        <!-- end row -->
                    </div>
                    <!-- container-fluid -->
                </div>
                <!-- End Page-content -->


<div class="modal fade" id="modalChangeAddress" data-bs-backdrop="static"  tabindex="-1" role="dialog" aria-labelledby="modalChangeAddressLabel" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="modalChangeAddressLabel"> Change Address?</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                <form id="formOrder" method="POST" action="{{route('architect.gift.products.preview.order')}}">
                                                    @csrf
                                                         <div class="modal-body">



                                     <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="d_country_id" class="form-label">Country</label>
                                                <select class="form-select" id="d_country_id" name="d_country_id" required >

                                                </select>
                                                <div class="invalid-feedback">
                                                    Please select country.
                                                </div>

                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                        <label class="form-label">State </label>
                                                        <select class="form-control select2-ajax" id="d_state_id" name="d_state_id" required >

                                                        </select>
                                                <div class="invalid-feedback">
                                                    Please select state.
                                                </div>

                                                    </div>



                                        </div>

                                         <div class="col-md-4">
                                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                        <label class="form-label">City </label>
                                                        <select  class="form-control select2-ajax" id="d_city_id" name="d_city_id" required >

                                                        </select>
                                                <div class="invalid-feedback">
                                                    Please select city.
                                                </div>

                                                    </div>



                                        </div>


                                    </div>

                                    <div class="row">

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="d_pincode" class="form-label">Pincode</label>
                                                <input type="text" class="form-control" id="d_pincode" name="d_pincode"
                                                    placeholder="Pincode" value="{{ Auth::user()->pincode }}" required>


                                            </div>
                                        </div>


                                    </div>


                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="d_address_line1" class="form-label">Address line 1</label>
                                                <input type="text" class="form-control" id="d_address_line1" name="d_address_line1"
                                                    placeholder="Address line 1" value="{{ Auth::user()->address_line1 }}" required>


                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="d_address_line2" class="form-label">Address line 2</label>
                                                <input type="text" class="form-control" id="d_address_line2" name="d_address_line2"
                                                    placeholder="Address line 2" value="{{ Auth::user()->address_line2 }}" >


                                            </div>
                                        </div>
                                    </div>










                                                         </div>

                                                         <div class="modal-footer">
                                                            <button type="button" class="btn btn-primary " data-bs-dismiss="modal">Change</button>

                                                        </div>
                                                    </form>


                                                    </div>
                                                </div>
</div>



<div class="modal fade" id="modalOrderPreviw" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" role="dialog" aria-labelledby="modalOrderLabel" aria-hidden="true">
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

@endsection('content')
@section('custom-scripts')
 <script src="{{ asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js') }}"></script>
  <script src="{{ asset('assets/js/pages/jquery.form.js') }}"></script>
  <script src="{{ asset('assets/js/order.js') }}"></script>
@include('../crm/architect/comman/cart_script')
<script type="text/javascript">
getCartDetail();

 var ajaxURLSearchCountry='{{route('search.country')}}';
 var ajaxURLSearchState='{{route('search.state.from.country')}}';
 var ajaxURLSearchCity='{{route('search.city.from.state')}}';
 var ajaxURLPreviewOrder='{{route('architect.gift.products.preview.order')}}';
 var ajaxURLPlaceOrder='{{route('architect.gift.products.place.order')}}';
var csrfToken=$("[name=_token").val();
var reditectURL='{{route('architect.orders')}}';



 var selectedCountryId={{ Auth::user()->country_id }};
 var selectedStateId={{ Auth::user()->state_id }};
 var selectedCityId={{ Auth::user()->city_id }};
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
    $('#formOrder').ajaxForm(options);
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

         $("#modalOrderPreviw").modal('show');
         $("#modalOrderPreviw .modal-body").html(responseText['preview']);



    } else if (responseText['status'] == 0) {



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


function previewOrder(){
    $("#modalOrderPreviw .modal-body").html('');
    $("#formOrder").submit();


}

function placeOrder(){
    var requestData={
        'd_country_id':$("#d_country_id").val(),
        'd_state_id':$("#d_state_id").val(),
        'd_city_id':$("#d_state_id").val(),
        'd_pincode':$("#d_pincode").val(),
        'd_address_line1':$("#d_address_line1").val(),
        'd_address_line2':$("#d_address_line2").val()

    };


var formData = new FormData();
var has_aadhar_card=$("#has_aadhar_card").val();
var total_amount=$("#total_amount").val();
var is_preview=$("#is_preview").val();

if(is_preview==1 && total_amount>0){


    var payment_mode_1 = $("#payment_mode_1:checked").length;
    if(payment_mode_1!=0){
       requestData['payment_mode']=1;
    }

    var payment_mode_2 = $("#payment_mode_2:checked").length;
    if(payment_mode_2!=0){
       requestData['payment_mode']=2;
    }

      requestData['bank_detail_account']=$("#bank_detail_account").val();
      requestData['bank_detail_ifsc']=$("#bank_detail_ifsc").val();
      requestData['bank_detail_upi']=$("#bank_detail_upi").val();


}
if(has_aadhar_card==0){
  formData.append('architect_aadhar_card', $('#aadhar_card')[0].files[0]);
}

    formData.append('request_data', JSON.stringify(requestData));




    $.ajax({
            headers: {'X-CSRF-TOKEN': csrfToken},
            type: 'POST',
            url: ajaxURLPlaceOrder,
            data:formData,
            cache: false,
            processData: false,
            contentType: false,
            success: function(resultData) {

               $("#modalOrderTbody").html('');
                if(resultData['status']==1){
                    getCartDetail();
              toastr["success"](resultData['msg']);
                     $("#modalOrderPreviw").modal('hide');
        setTimeout(function(){
            window.location=reditectURL;

        },2000);
                } else if (resultData['status'] == 0) {


        if(typeof resultData['data'] !== "undefined"){

            var size = Object.keys(resultData['data']).length;
            if(size>0){

              for (var [key, value] of Object.entries(resultData['data'])) {

                  toastr["error"](value);
               }

            }

         }else{
            toastr["error"](resultData['msg']);
         }


    }

            }
        });

}


$("#d_country_id").select2({

     ajax: {
        url: ajaxURLSearchCountry,
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
    placeholder: 'Search for a country',
    dropdownParent: $("#modalChangeAddress .modal-body")


});


function getCountryObject(){

   $.ajax({
            type: 'GET',
            url: ajaxURLSearchCountry + "?id="+selectedCountryId,
            success: function(resultData) {
                if(resultData['results'].length>0){

                    $("#d_country_id").empty().trigger('change');
   var newOption = new Option(resultData['results'][0]['text'],resultData['results'][0]['id'], false, false);
 $('#d_country_id').append(newOption).trigger('change');

                }

            }
        });
}



$("#d_state_id").select2({
    ajax: {
        url: ajaxURLSearchState,
        dataType: 'json',
        delay: 0,
        data: function(params) {
            return {
                "country_id": function() {
                    return $("#d_country_id").val()
                },
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
    placeholder: 'Search for a state',
    dropdownParent: $("#modalChangeAddress .modal-body")
});


function getStateObject(){

   $.ajax({
            type: 'GET',
            url: ajaxURLSearchState + "?id="+selectedStateId,
            success: function(resultData) {
                if(resultData['results'].length>0){

                    $("#d_state_id").empty().trigger('change');
   var newOption = new Option(resultData['results'][0]['text'],resultData['results'][0]['id'], false, false);
 $('#d_state_id').append(newOption).trigger('change');

                }

            }
        });
}
getStateObject();

$("#d_city_id").select2({
    ajax: {
        url: ajaxURLSearchCity,
        dataType: 'json',
        delay: 0,
        data: function(params) {
            return {
                "state_id": function() {
                    return $("#d_state_id").val()
                },
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
    placeholder: 'Search for a city',
    dropdownParent: $("#modalChangeAddress .modal-body")
});

function getCityObject(){

   $.ajax({
            type: 'GET',
            url: ajaxURLSearchCity + "?id="+selectedCityId,
            success: function(resultData) {
                if(resultData['results'].length>0){

                    $("#d_city_id").empty().trigger('change');
   var newOption = new Option(resultData['results'][0]['text'],resultData['results'][0]['id'], false, false);
 $('#d_city_id').append(newOption).trigger('change');

                }

            }
        });
}


$('#d_country_id').on('change', function() {

     $("#d_state_id").empty().trigger('change');
     $("#d_city_id").empty().trigger('change');

});

$('#d_state_id').on('change', function() {

     $("#d_city_id").empty().trigger('change');

});

setTimeout(function(){
getCountryObject();
}, 100);
setTimeout(function(){
getStateObject();
}, 200);
setTimeout(function(){
getCityObject();
}, 300);

</script>
@endsection
