@extends('layouts.main')
@section('title', $data['title'])
@section('content')


                <div class="page-content">
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0 font-size-18">City List</h4>

                                      <div class="page-title-right">


<button id="addBtnCity" class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#canvasCity" aria-controls="canvasCity"><i class="bx bx-plus font-size-16 align-middle me-2"></i>City</button>

<div class="offcanvas offcanvas-end" tabindex="-1" id="canvasCity" aria-labelledby="canvasCityLabel">
                                            <div class="offcanvas-header">
                                              <h5 id="canvasCityLabel"></h5>
                                              <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                            </div>
                                            <div class="offcanvas-body">

                                                <div class="col-md-12 text-center loadingcls">






                                            <button type="button" class="btn btn-light waves-effect">
                                                <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading
                                            </button>


                                               </div>






                                                 <form id="formCity" class="needs-validation custom-validation" action="{{route('citylist.save')}}" method="POST"  >

                                              @csrf

                                              <input type="hidden" name="city_id" id="city_id" value="0" >



                                                <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="city_name" class="form-label">City Name</label>
                                                <input type="text" class="form-control" id="city_name" name="city_name"
                                                    placeholder="Name" value="" required>


                                            </div>
                                        </div>

                                    </div>


                                <div class="row">

                                         <div class="col-lg-12">
                                                    <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                        <label class="form-label">Country </label>
                                                        <select  class="form-control select2-ajax" id="city_country_id" name="city_country_id"  required >

                                                        </select>

                                                    </div>

                                                </div>




                                    </div>


                                    <div class="row">

                                         <div class="col-lg-12">
                                                    <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                        <label class="form-label">State </label>
                                                        <select  class="form-control select2-ajax" id="city_state_id" name="city_state_id" required >

                                                        </select>

                                                    </div>

                                                </div>




                                    </div>








                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="city_status" class="form-label">Status</label>

                                                <select id="city_status" name="city_status" class="form-control select2-apply" >
                                                    <option value="1">Active</option>
                                                    <option value="0">Inactive</option>


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
                        <!-- start row -->



                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-body">


                                        <form>
                                            <div class="row">

                                                <div class="col-lg-6">


                                                    <div class="mb-3">

                                                          <label class="form-label">Country</label>

                                                      <select class="form-select" id="country_id" >

                                                        <option value="0">ALL</option>

                                        @foreach($data['country_list'] as $key=>$value)
                                        <option value="{{$value->id}}" > {{$value->name   }} ({{$value->code}}) </option>
                                        @endforeach

                                    </select>
                                </div>

                                                </div>


                                                <div class="col-lg-6">
                                                    <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                        <label class="form-label">State </label>
                                                        <select class="form-control select2-ajax select2-state" id="state_id" >
                                                            <option value="0">ALL</option>
                                                        </select>

                                                    </div>

                                                </div>
                                            </div>

                                        </form>

                                    </div>
                                </div>
                                <!-- end select2 -->

                            </div>


                        </div>
                        <!-- end row -->

                         <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">



                                        <table id="datatable" class="table table-striped dt-responsive  nowrap w-100">
                                            <thead>
                                            <tr>
                                                <th>Id</th>
                                                <th>City Name</th>
                                                <th>State Name</th>
                                                <th>Status</th>
                                                <th>Action</th>


                                            </tr>
                                            </thead>


                                            <tbody>

                                            </tbody>
                                        </table>

                                    </div>
                                </div>
                            </div> <!-- end col -->
                        </div> <!-- end row -->
                    </div>
                    <!-- container-fluid -->
                </div>
                <!-- End Page-content -->






 @csrf
@endsection('content')
@section('custom-scripts')
<script src="{{ asset('assets/libs/parsleyjs/parsley.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/form-validation.init.js') }}"></script>
<script src="{{ asset('assets/js/pages/jquery.form.js') }}"></script>

<script type="text/javascript">



     var ajaxURLSearchState='{{route('citylist.search.state')}}';
     var ajaxURLSearchCountry='{{route('citylist.search.country')}}';
     var ajaxURLCityList='{{route('citylist.ajax')}}';
     var ajaxURLCityDetail='{{route('citylist.detail')}}';


     var csrfToken=$("[name=_token").val();




$("#country_id").select2();


$("#city_status").select2({
    minimumResultsForSearch: Infinity,
    dropdownParent: $("#canvasCity")
});




$("#city_country_id").select2({
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
    dropdownParent: $("#canvasCity")
});

$("#city_state_id").select2({
    ajax: {
        url: ajaxURLSearchState,
        dataType: 'json',
        delay: 0,
        data: function(params) {
            return {
                "country_id":  function() { return $("#city_country_id").val() },
                "need_all":0,
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
    dropdownParent: $("#canvasCity")
});




     $(".select2-state").select2({
  ajax: {
    url: ajaxURLSearchState,
    dataType: 'json',
    delay: 0,
    data: function (params) {
      return {
        "country_id":  function() { return $("#country_id").val() },
         "need_all":1,
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


});




 var locationPageLength= getCookie('locationPageLength')!==undefined?getCookie('locationPageLength'):10;

var table=$('#datatable').DataTable({
  "aoColumnDefs": [{ "bSortable": false, "aTargets": [] }],
  "order":[[ 0, 'desc' ]],
  "processing": true,
  "serverSide": true,
   "pageLength": locationPageLength,
  "ajax": {
    "url": ajaxURLCityList,
    "type": "POST",
     "data": {
        "_token": csrfToken,
          "country_id":  function() { return $("#country_id").val() },
          "state_id":  function() { return $("#state_id").val() }
        }


  },
  "aoColumns" : [
    {"mData" : "id"},
    {"mData" : "name"},
    {"mData" : "state_name"},
    {"mData" : "status"},
    {"mData" : "action"},



  ]
});



$('#datatable').on( 'length.dt', function ( e, settings, len ) {

    setCookie('locationPageLength',len,100);


});


function reloadTable()
{
  table.ajax.reload( null, false );
}
function applyFilter() {
    reloadTable();
}
$('#country_id').on('change', function() {
  applyFilter();
});
$('#state_id').on('change', function() {
  applyFilter();
});


$('#city_country_id').on('change', function() {
 $("#city_state_id").empty().trigger('change');
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
    $('#formCity').ajaxForm(options);
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
         $("#canvasCity").offcanvas('hide');


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

    $("#addBtnCity").click(function() {


    $("#canvasCityLabel").html("Add City");
    $("#formCity").show();
    $(".loadingcls").hide();
    resetInputForm();
    $("#city_id").val(0);

});


function resetInputForm(){

    $('#formCity').trigger("reset");
    $("#city_country_id").empty().trigger('change');
    $("#city_state_id").empty().trigger('change');
    $("#city_status").select2("val", "1");
    $("#formCity").removeClass('was-validated');

}

function editView(id) {

     resetInputForm();

    $("#canvasCity").offcanvas('show');
    $("#modalCompanyLabel").html("Edit City  #" + id);
    $("#formCity .row").hide();
    $(".loadingcls").show();


    $.ajax({
        type: 'GET',
        url: ajaxURLCityDetail + "?id=" + id,
        success: function(resultData) {

                if (resultData['status'] == 1) {

                 $("#city_id").val(resultData['data']['id']);
                 $("#city_name").val(resultData['data']['name']);
                 $("#city_status").select2("val", ""+resultData['data']['status']+"");

                 if(typeof resultData['data']['country'] !== "undefined"  &&  typeof resultData['data']['country']['id'] !== "undefined"){


          $("#city_country_id").empty().trigger('change');
          var newOption = new Option(resultData['data']['country']['text'], resultData['data']['country']['id'], false, false);
          $('#city_country_id').append(newOption).trigger('change');

}

            if(typeof resultData['data']['state'] !== "undefined"  &&  typeof resultData['data']['state']['id'] !== "undefined"){


          $("#city_state_id").empty().trigger('change');
          var newOption = new Option(resultData['data']['state']['text'], resultData['data']['state']['id'], false, false);
          $('#city_state_id').append(newOption).trigger('change');

}


                 $(".loadingcls").hide();
                 $("#formCity .row").show();


                }
            }
        });
}



</script>
@endsection
