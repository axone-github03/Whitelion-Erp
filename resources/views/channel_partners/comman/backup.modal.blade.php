<div class="modal fade" id="modalUser" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="modalUserLabel" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="modalUserLabel"> User</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>




                                                        <form  id="formUser" action="{{route('channel.partners.save')}}" method="POST"  class="needs-validation" novalidate>
                                                    <div class="modal-body">

                                                                @csrf


                                        <div class="col-md-12 text-center loadingcls">






                                            <button type="button" class="btn btn-light waves-effect">
                                                <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading
                                            </button>


                                               </div>

                                      <input type="hidden" name="user_id" id="user_id"  >


                          <!--   <div class="row"> -->




                                   <!--  <div class="col-md-3">
                                                <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                                                <a class="nav-link mb-2 active" id="v-pills-user-detail-tab" data-bs-toggle="pill" href="#v-pills-user-detail" role="tab" aria-controls="v-pills-user-detail" aria-selected="true">1. User Detail</a>


                                                <a class="nav-link mb-2" id="v-pills-channel-partner-detail-tab" data-bs-toggle="pill" href="#v-pills-channel-partner-detail" role="tab" aria-controls="v-pills-channel-partner-detail" aria-selected="false">2. Channel Partner Detail</a>

                                                </div>
                                            </div>
 -->
                                            <!-- <div class="col-md-9">
                                                <div class="tab-content text-muted mt-4 mt-md-0" id="v-pills-tabContent"> -->
                                  <!--   <div class="tab-pane fade show active" id="v-pills-user-detail" role="tabpanel" aria-labelledby="v-pills-user-detail-tab"> -->

                                                        <!--   <div class="card-body section_user_detail"> -->






                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="user_first_name" class="form-label">First name</label>
                                                <input type="text" class="form-control" id="user_first_name" name="user_first_name"
                                                    placeholder="First Name" value="" required >


                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="user_last_name" class="form-label">Last name</label>
                                                <input type="text" class="form-control" id="user_last_name" name="user_last_name"
                                                    placeholder="Last Name" value="" required>


                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="user_email" class="form-label">Email</label>
                                                <input type="email" class="form-control" id="user_email" name="user_email"
                                                    placeholder="Email" value="" required>


                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="insert_phone_number" class="form-label">Phone number</label>

                                                <div class="input-group">
                                                    <div class="input-group-text">
                                                        +91


                                                    </div>
                                                    <input type="number" class="form-control" id="user_phone_number" name="user_phone_number"
                                                    placeholder="Phone number" value="" required>
                                                </div>





                                            </div>
                                        </div>
                                    </div>





                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="user_country_id" class="form-label">Country</label>
                                                <select class="form-select" id="user_country_id" name="user_country_id" required >
                                                    <option selected value="1">India</option>

                                                </select>
                                                <div class="invalid-feedback">
                                                    Please select country.
                                                </div>

                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                        <label class="form-label">State </label>
                                                        <select class="form-control select2-ajax select2-state" id="user_state_id" name="user_state_id" required >

                                                        </select>
                                                <div class="invalid-feedback">
                                                    Please select state.
                                                </div>

                                                    </div>



                                        </div>

                                         <div class="col-md-4">
                                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                        <label class="form-label">State </label>
                                                        <select  class="form-control select2-ajax select2-state" id="user_city_id" name="user_city_id" required >

                                                        </select>
                                                <div class="invalid-feedback">
                                                    Please select state.
                                                </div>

                                                    </div>



                                        </div>


                                    </div>

                                    <div class="row">

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="user_pincode" class="form-label">Pincode</label>
                                                <input type="text" class="form-control" id="user_pincode" name="user_pincode"
                                                    placeholder="Pincode" value="" required>


                                            </div>
                                        </div>


                                    </div>


                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="user_address_line1" class="form-label">Address line 1</label>
                                                <input type="text" class="form-control" id="user_address_line1" name="user_address_line1"
                                                    placeholder="Address line 1" value="" required>


                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="user_address_line2" class="form-label">Address line 2</label>
                                                <input type="text" class="form-control" id="user_address_line2" name="user_address_line2"
                                                    placeholder="Address line 2" value="" >


                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">

                                            <div class="mb-3">
                                                <label for="user_status" class="form-label">Status</label>
                                                <select class="form-select" id="user_status" name="user_status" required >
                                                    <option selected value="1">Active</option>
                                                    <option  value="0">Inactive</option>
                                                    <option  value="2">Blocked</option>

                                                </select>
                                                <div class="invalid-feedback">
                                                    Please select status.
                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                     <div class="row">
                                        <div class="col-md-12">

                                            <div class="mb-3">
                                                <label for="user_status" class="form-label">User Type </label>
                                                <select class="form-select" id="user_type" name="user_type" required >

                                                   @php
$accessTypes=getChannelPartners();
 @endphp
 @if(count($accessTypes)>0)
   @foreach($accessTypes as $key=>$value)
   <option value="{{$value['id']}}" >{{$value['name']}} </option>
   @endforeach
@endif

                                                </select>
                                                <div class="invalid-feedback">
                                                    Please select user type.
                                                </div>

                                            </div>

                                        </div>

                                    </div>



                             <!--    </div>

                                                    </div> -->
                                                 <!--    <div class="tab-pane fade" id="v-pills-channel-partner-detail" role="tabpanel" aria-labelledby="v-pills-channel-partner-detail-tab"> -->


                                                           <!--  <div class="card-body section_channel_partner"> -->




                                    <div class="row ">


                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="channel_partner_firm_name" class="form-label">Firm Name</label>
                                                <input type="text" class="form-control" id="channel_partner_firm_name" name="channel_partner_firm_name"
                                                    placeholder="Firm Name" value="" required>


                                            </div>
                                        </div>

                                    </div>



                                    <div class="row"   >
                                        <div class="col-md-6">


                                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                        <label class="form-label">Reporting Manager </label>
                                                        <select class="form-control select2-ajax " id="channel_partner_reporting" name="channel_partner_reporting" required  >

                                                        </select>
                                                <div class="invalid-feedback">
                                                    Please select Reporting Manager.
                                                </div>

                                                    </div>






                                        </div>


                                        <div class="col-md-6">


                                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                        <label class="form-label">Assign Sales Persons </label>
                                                        <select multiple="multiple" class="form-control select2-ajax select2-multiple" id="channel_partner_sale_persons" name="channel_partner_sale_persons[]" required >

                                                        </select>
                                                <div class="invalid-feedback">
                                                    Please select sale person type.
                                                </div>

                                                    </div>






                                        </div>

                                    </div>

                                        <div class="row"   >
                                        <div class="col-md-6">


                                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                        <label class="form-label"> Payment Mode </label>
                                                        <select class="form-control select2-ajax " id="channel_partner_payment_mode" name="channel_partner_payment_mode"  >

                                                            <option value="0">PDC</option>
                                                            <option value="1">Advance</option>
                                                            <option value="2">Credit</option>

                                                        </select>
                                                <div class="invalid-feedback">
                                                    Please select payment type
                                                </div>

                                                    </div>






                                        </div>

                                    </div>

                                        <div class="row channel_partner_payment_mode2">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="channel_partner_credit_days" class="form-label">Credit Limit</label>
                                                <input type="number" class="form-control" id="channel_partner_credit_limit" name="channel_partner_credit_limit"
                                                    placeholder="Credit Limit" value="" required >


                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="channel_partner_credit_days" class="form-label">Credit Days</label>
                                                <input type="number" class="form-control" id="channel_partner_credit_days" name="channel_partner_credit_days"
                                                    placeholder="Credit Days" value="" required>


                                            </div>
                                        </div>
                                    </div>


                                      <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="channel_partner_gst_number" class="form-label">GST Number</label>
                                                <input type="text" class="form-control" id="channel_partner_gst_number" name="channel_partner_gst_number"
                                                    placeholder="GST Number" value="" required >


                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="channel_partner_shipping_limit" class="form-label">Shipping Limit</label>
                                                <input type="number" class="form-control" id="channel_partner_shipping_limit" name="channel_partner_shipping_limit"
                                                    placeholder="Shipping Limit" value="" required>


                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="channel_partner_shipping_cost" class="form-label">Shipping Cost (per KG.)</label>
                                                <input type="number" class="form-control" id="channel_partner_shipping_cost" name="channel_partner_shipping_cost"
                                                    placeholder="Shipping Cost" value="" required>


                                            </div>
                                        </div>
                                    </div>
   <div class="row">
     <div class="col-md-12">

                                            <div class="mb-3">
                                                 <h4 class="card-title col-md-6 col-xl-3">Delivery Address </h4>
                                                  <button type="button" class="btn btn-sm btn-primary right" id="copyAddressBtn" >Delivery Address same as user detail address?</button>


                                                    </div>
                                                    </div>
                                                    </div>



                                     <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="channel_partner_d_country_id" class="form-label">Country</label>
                                                <select class="form-select" id="channel_partner_d_country_id" name="channel_partner_d_country_id" required >
                                                    <option selected value="1">India</option>

                                                </select>
                                                <div class="invalid-feedback">
                                                    Please select country.
                                                </div>

                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                        <label class="form-label">State </label>
                                                        <select class="form-control select2-ajax select2-state" id="channel_partner_d_state_id" name="channel_partner_d_state_id" required >

                                                        </select>
                                                <div class="invalid-feedback">
                                                    Please select state.
                                                </div>

                                                    </div>



                                        </div>

                                         <div class="col-md-4">
                                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                        <label class="form-label">State </label>
                                                        <select  class="form-control select2-ajax select2-state" id="channel_partner_d_city_id" name="channel_partner_d_city_id" required >

                                                        </select>
                                                <div class="invalid-feedback">
                                                    Please select state.
                                                </div>

                                                    </div>



                                        </div>


                                    </div>

                                    <div class="row">

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="channel_partner_d_pincode" class="form-label">Pincode</label>
                                                <input type="text" class="form-control" id="channel_partner_d_pincode" name="channel_partner_d_pincode"
                                                    placeholder="Pincode" value="" required>


                                            </div>
                                        </div>


                                    </div>


                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="channel_partner_d_address_line1" class="form-label">Address line 1</label>
                                                <input type="text" class="form-control" id="channel_partner_d_address_line1" name="channel_partner_d_address_line1"
                                                    placeholder="Address line 1" value="" required>


                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="user_address_line2" class="form-label">Address line 2</label>
                                                <input type="text" class="form-control" id="channel_partner_d_address_line2" name="channel_partner_d_address_line2"
                                                    placeholder="Address line 2" value="" >


                                            </div>
                                        </div>
                                    </div>
                                <!-- </div> -->

                                               <!--      </div> -->

                                                <!-- </div>
                                            </div> -->

                                   <!--  </div> -->






















                                                        </div>





                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                                            <button type="submit" class="btn btn-primary">Save</button>
                                                        </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

 <script type="text/javascript">
 var ajaxURLSearchState='{{route('channel.partners.search.state')}}';
 var ajaxURLSearchCity='{{route('channel.partners.search.city')}}';
 var ajaxSearchReportingManager='{{route('channel.partners.search.reporting.manager')}}';
 var ajaxSearchSalePerson= '{{route('channel.partners.search.sale.person')}}';
 var ajaxURLUserDetail='{{route('channel.partners.detail')}}';
 var ajaxCityDetail='{{route('channel.partners.city.detail')}}';






$("#user_country_id").select2({
    minimumResultsForSearch: Infinity,
    dropdownParent: $("#modalUser .modal-body")

});


$("#channel_partner_d_country_id").select2({
    minimumResultsForSearch: Infinity,
    dropdownParent: $("#modalUser .modal-body")

});


$("#channel_partner_payment_mode").select2({
    minimumResultsForSearch: Infinity,
    dropdownParent: $("#modalUser .modal-body")

});

 $("#user_city_id").select2({
  ajax: {
    url: ajaxURLSearchCity,
    dataType: 'json',
    delay: 0,
    data: function (params) {
      return {
        "country_id":  function() { return $("#user_country_id").val()},
        "state_id":  function() { return $("#user_state_id").val()},
        q: params.term, // search term
        page: params.page
      };
    },
    processResults: function (data, params) {
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
  minimumInputLength: 1,
  dropdownParent: $("#modalUser .modal-body")
});


 $("#channel_partner_d_city_id").select2({
  ajax: {
    url: ajaxURLSearchCity,
    dataType: 'json',
    delay: 0,
    data: function (params) {
      return {
        "country_id":  function() { return $("#channel_partner_d_country_id").val()},
        "state_id":  function() { return $("#channel_partner_d_state_id").val()},
        q: params.term, // search term
        page: params.page
      };
    },
    processResults: function (data, params) {
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
  minimumInputLength: 1,
  dropdownParent: $("#modalUser .modal-body")
});

 $("#user_state_id").select2({
  ajax: {
    url: ajaxURLSearchState,
    dataType: 'json',
    delay: 0,
    data: function (params) {
      return {
        "country_id":  function() { return $("#user_country_id").val()},
        q: params.term, // search term
        page: params.page
      };
    },
    processResults: function (data, params) {
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
  minimumInputLength: 1,
  dropdownParent: $("#modalUser .modal-body")
});


 $("#channel_partner_d_state_id").select2({
  ajax: {
    url: ajaxURLSearchState,
    dataType: 'json',
    delay: 0,
    data: function (params) {
      return {
        "country_id":  function() { return $("#channel_partner_d_country_id").val()},
        q: params.term, // search term
        page: params.page
      };
    },
    processResults: function (data, params) {
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
  minimumInputLength: 1,
  dropdownParent: $("#modalUser .modal-body")
});


 $("#user_state_id").select2({
  ajax: {
    url: ajaxURLSearchState,
    dataType: 'json',
    delay: 0,
    data: function (params) {
      return {
        "country_id":  function() { return $("#user_country_id").val()},
        q: params.term, // search term
        page: params.page
      };
    },
    processResults: function (data, params) {
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
  minimumInputLength: 1,
  dropdownParent: $("#modalUser .modal-body")
});


  $("#channel_partner_reporting").select2({
  ajax: {
    url: ajaxSearchReportingManager,
    dataType: 'json',
    delay: 0,
    data: function (params) {
      return {
        "user_type":  function() { return $("#user_type").val()},
        "user_id":  function() { return $("#user_id").val()},
        q: params.term, // search term
        page: params.page
      };
    },
    processResults: function (data, params) {
      // parse the results into the format expected by Select2
      // since we are using custom formatting functions we do not need to
      // alter the remote JSON data, except to indicate that infinite
      // scrolling can be used
      params.page = params.page || 1;
       if($("#user_type").val()==null){
        toastr["error"]("Please select user type first");

      }

      return {
        results: data.results,
        pagination: {
          more: (params.page * 30) < data.total_count
        }
      };
    },
    cache: false
  },
  placeholder: 'Search for a reporting manager',
  dropdownParent: $("#modalUser .modal-body ")
});



  $("#channel_partner_sale_persons").select2({
  ajax: {
    url: ajaxSearchSalePerson,
    dataType: 'json',
    delay: 0,
    data: function (params) {
      return {
        "user_type":  function() { return $("#user_type").val()},
        "user_id":  function() { return $("#user_id").val()},
        "channel_partner_reporting":  function() { return $("#channel_partner_reporting").val()},
        q: params.term, // search term
        page: params.page
      };
    },
    processResults: function (data, params) {
      // parse the results into the format expected by Select2
      // since we are using custom formatting functions we do not need to
      // alter the remote JSON data, except to indicate that infinite
      // scrolling can be used
      params.page = params.page || 1;
       if($("#user_type").val()==null){
        toastr["error"]("Please select user type first");

      }
    if($("#channel_partner_reporting").val()==null){
        toastr["error"]("Please select reporting manager first");

      }



      return {
        results: data.results,
        pagination: {
          more: (params.page * 30) < data.total_count
        }
      };
    },
    cache: false
  },
  placeholder: 'Search for a reporting manager',
  dropdownParent: $("#modalUser .modal-body")
});






$("#user_status").select2({
    minimumResultsForSearch: Infinity,
    dropdownParent: $("#modalUser .modal-body")
});


 $(document).ready(function() {


//

// $("#user_type").select2({
//     minimumResultsForSearch: Infinity,
//     dropdownParent: $("#modalUser .modal-body")

// });



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
    $('#formUser').ajaxForm(options);
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
        reloadTable();
        resetInputForm();
        $("#modalUser").modal('hide');


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


$('.nav a').click(function(){
  if($(this).attr('id')=="v-pills-channel-partner-detail-tab"){



    $("#modalUser .modal-footer").show();
    $("#channel_partner_payment_mode").select2("val", "0");
     changePaymetMode(0);
     $("#channel_partner_credit_days").removeAttr('required')
     $("#channel_partner_credit_limit").removeAttr('required');

  }else{
    $("#modalUser .modal-footer").hide();
  }
})




$("#copyAddressBtn").click(function() {


     if($("#user_city_id").val()==null){
        toastr["error"]("Please select city first");

      }else{


    $.ajax({
        type: 'GET',
        url: ajaxCityDetail + "?city_id="+$("#user_city_id").val(),
        success: function(resultData) {


            $("#channel_partner_d_country_id").select2("val", "1");

            if(typeof resultData['data']['state']['id'] !== "undefined" &&  resultData['data']['state']['id'] !== null && typeof resultData['data']['state']['id'] !== "undefined" &&  resultData['data']['state']['id']){

  $("#channel_partner_d_state_id").empty().trigger('change');
   var newOption = new Option(resultData['data']['state']['text'],resultData['data']['state']['id'], false, false);
 $('#channel_partner_d_state_id').append(newOption).trigger('change');

}



if(typeof resultData['data']['id'] !== "undefined" &&  resultData['data']['id'] !== null && typeof resultData['data']['id'] !== "undefined" &&  resultData['data']['id']){

  $("#channel_partner_d_city_id").empty().trigger('change');
   var newOption = new Option(resultData['data']['text'],resultData['data']['id'], false, false);
 $('#channel_partner_d_city_id').append(newOption).trigger('change');

}







           $("#channel_partner_d_pincode").val($("#user_pincode").val());
           $("#channel_partner_d_address_line1").val($("#user_address_line1").val());
           $("#channel_partner_d_address_line2").val($("#user_address_line2").val());

        }
    });

      }





});

$("#addBtnUser").click(function() {



     resetInputForm();
     $("#modalUserLabel").html("Add Channel Partner");
     $("#user_id").val(0);
     $(".loadingcls").hide();
     $("#formUser .row").show();
     console.log($("#user_type").val());
     setTimeout(function(){
       // $("#user_type").select2("val", $("#user_type").val());
         changeUserType($("#user_type").val());
     },100);



});

function resetInputForm(){

     $('.nav a:first').tab('show');
     $("#modalUser .modal-footer").hide();
    $(".channel_partner_payment_mode2").css('display','none');


     $('#formUser').trigger("reset");
     $("#user_status").select2("val", "1");
     $("#user_country_id").select2("val", "1");
     $("#user_state_id").empty().trigger('change');
     $("#user_city_id").empty().trigger('change');


     $("#channel_partner_sale_persons").empty().trigger('change');
     $("#channel_partner_reporting_manager").empty().trigger('change');
     $("#formUser").removeClass('was-validated');
     previousselectedSaleState=[];



}



function editView(id) {


     resetInputForm();

    $("#modalUser").modal('show');
    $("#modalUserLabel").html("Edit Channel Partner #" + id);
    $("#formUser .row").hide();
    $(".loadingcls").show();
    $("#modalUser .modal-footer").hide();

    $.ajax({
        type: 'GET',
        url: ajaxURLUserDetail + "?id=" + id,
        success: function(resultData) {
            if (resultData['status'] == 1) {



                $("#user_id").val(resultData['data']['id']);


                $("#user_first_name").val(resultData['data']['first_name']);
                $("#user_last_name").val(resultData['data']['last_name']);
                $("#user_phone_number").val(resultData['data']['phone_number']);
                $("#user_email").val(resultData['data']['email']);
                $("#user_ctc").val(resultData['data']['ctc']);
                $("#user_pincode").val(resultData['data']['pincode']);
                $("#user_address_line1").val(resultData['data']['address_line1']);
                $("#user_address_line2").val(resultData['data']['address_line2']);
                // console.log(resultData['data']['status']);


              //  $("#user_type").select2("val", ""+resultData['data']['type']+"");

              $("#user_type").val(resultData['data']['type']);

                $("#user_status").select2("val", ""+resultData['data']['status']+"");


if(typeof resultData['data']['country']['id'] !== "undefined")
{
 $("#user_country_id").select2("val", ""+resultData['data']['country']['id']+"");

}


if(typeof resultData['data']['state']['id'] !== "undefined")
{
  $("#user_state_id").empty().trigger('change');
   var newOption = new Option(resultData['data']['state']['name'], resultData['data']['state']['id'], false, false);
 $('#user_state_id').append(newOption).trigger('change');

}

if(typeof resultData['data']['city']['id'] !== "undefined")
{
  $("#user_city_id").empty().trigger('change');
   var newOption = new Option(resultData['data']['city']['name'], resultData['data']['city']['id'], false, false);
 $('#user_city_id').append(newOption).trigger('change');

}

 $("#channel_partner_firm_name").val(resultData['data']['channel_partner']['firm_name']);



if(typeof resultData['data']['channel_partner']['reporting_manager']['id'] !== "undefined")
{
    ///not working

 //     console.log(resultData['data']['channel_partner']['reporting_manager']['id']);
 // console.log(resultData['data']['channel_partner']['reporting_manager']['text']);
 //  $("#channel_partner_reporting_manager").empty().trigger('change');
 //   var newOption = new Option(resultData['data']['channel_partner']['reporting_manager']['text'],resultData['data']['channel_partner']['reporting_manager']['id'], false, false);
 // $('#channel_partner_reporting_manager').append(newOption).trigger('change');

}
// console.log(resultData['data']['channel_partner']['sale_persons']);
if(resultData['data']['channel_partner']['sale_persons'].length>0)
{
 $("#channel_partner_sale_persons").empty().trigger('change');
        var selectedSalePersons=[];

        for (var i = 0; i < resultData['data']['channel_partner']['sale_persons'].length; i++) {

            selectedSalePersons.push(''+resultData['data']['channel_partner']['sale_persons'][i]['id']+'');


            var newOption = new Option(resultData['data']['channel_partner']['sale_persons'][i]['text'],resultData['data']['channel_partner']['sale_persons'][i]['id'], false, false);
              $('#channel_partner_sale_persons').append(newOption).trigger('change');
        }
        $("#channel_partner_sale_persons").val(selectedSalePersons).change();

    }


if(typeof resultData['data']['country']['id'] !== "undefined")
{
 $("#channel_partner_d_country_id").select2("val", ""+resultData['data']['country']['id']+"");

}


if(typeof resultData['data']['state']['id'] !== "undefined")
{
  $("#user_state_id").empty().trigger('change');
   var newOption = new Option(resultData['data']['state']['name'], resultData['data']['state']['id'], false, false);
 $('#user_state_id').append(newOption).trigger('change');

}

if(typeof resultData['data']['city']['id'] !== "undefined")
{
  $("#user_city_id").empty().trigger('change');
   var newOption = new Option(resultData['data']['city']['name'], resultData['data']['city']['id'], false, false);
 $('#user_city_id').append(newOption).trigger('change');

}






                  $(".loadingcls").hide();
                  $("#formUser .row").show();
                  $("#modalUser .modal-footer").show();
                  changeUserType(resultData['data']['type']);






            } else {

                toastr["error"](resultData['msg']);

            }

        }
    });

}

$('#user_country_id').on('change', function() {

     $("#user_state_id").empty().trigger('change');
     $("#user_city_id").empty().trigger('change');

});

 $('#user_state_id').on('change', function() {

    $("#user_city_id").empty().trigger('change');

});

 $('#channel_partner_d_country_id').on('change', function() {

     $("#channel_partner_d_state_id").empty().trigger('change');
     $("#channel_partner_d_city_id").empty().trigger('change');

});

 $('#channel_partner_d_state_id').on('change', function() {
    $("#channel_partner_d_city_id").empty().trigger('change');
});



  $('#user_type').on('change', function() {

   changeUserType($(this).val());

});


  $('#channel_partner_payment_mode').on('change', function() {


  changePaymetMode($(this).val());

});


  function changeUserType(userType){

     $("#channel_partner_reporting").empty().trigger('change');
     $("#channel_partner_sale_persons").empty().trigger('change');

  }

  function changePaymetMode(paymentMode){

     if(paymentMode=='2'){
    $(".channel_partner_payment_mode2").show();
     $("#channel_partner_credit_days").attr('required',true);
     $("#channel_partner_credit_limit").attr('required',true);
  }else{
      $(".channel_partner_payment_mode2").css('display','none');
      $("#channel_partner_credit_days").removeAttr('required')
     $("#channel_partner_credit_limit").removeAttr('required');


  }

  }





 </script>
