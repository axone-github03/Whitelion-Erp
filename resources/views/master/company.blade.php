@extends('layouts.main')
@section('title', $data['title'])
@section('content')



                <div class="page-content">
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0 font-size-18">Company Master

                                    </h4>

                                     <div class="page-title-right">







<div class="modal fade" id="modalCompany" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" role="dialog" aria-labelledby="modalCompanyLabel" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="modalCompanyLabel"> Company</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>




                                                        <form  id="formCompany" action="{{route('company.save')}}" method="POST"  class="needs-validation" novalidate>
                                                    <div class="modal-body">

                                                                @csrf


                                                                   <div class="col-md-12 text-center loadingcls">






                                            <button type="button" class="btn btn-light waves-effect">
                                                <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading
                                            </button>


                                               </div>

                                                            <input type="hidden" name="company_id" id="company_id"  >




  <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="company_name" class="form-label">Company name</label>
                                                <input type="text" class="form-control" id="company_name" name="company_name"
                                                    placeholder="Company name" value="" required >


                                            </div>
                                        </div>

                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="company_first_name" class="form-label">First name</label>
                                                <input type="text" class="form-control" id="company_first_name" name="company_first_name"
                                                    placeholder="First name" value="" required >


                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="company_last_name" class="form-label">Last name</label>
                                                <input type="text" class="form-control" id="company_last_name" name="company_last_name"
                                                    placeholder="Last name" value="" required>


                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="company_email" class="form-label">Email</label>
                                                <input type="email" class="form-control" id="company_email" name="company_email"
                                                    placeholder="Email" value="" required>


                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="insert_phone_number" class="form-label">Phone number</label>

                                                <div class="input-group">

                                                    <input type="number" class="form-control" id="company_phone_number" name="company_phone_number"
                                                    placeholder="Phone number" value="" required>
                                                </div>





                                            </div>
                                        </div>
                                    </div>





                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="company_country_id" class="form-label">Country</label>
                                                <select class="form-select" id="company_country_id" name="company_country_id" required >
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
                                                        <select class="form-control select2-ajax select2-state" id="company_state_id" name="company_state_id" required >

                                                        </select>
                                                <div class="invalid-feedback">
                                                    Please select state.
                                                </div>

                                                    </div>



                                        </div>

                                         <div class="col-md-4">
                                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                        <label class="form-label">State </label>
                                                        <select class="form-control select2-ajax select2-state" id="company_city_id" name="company_city_id" required >

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
                                                <label for="company_pincode" class="form-label">Pincode</label>
                                                <input type="text" class="form-control" id="company_pincode" name="company_pincode"
                                                    placeholder="Pincode" value="" required>


                                            </div>
                                        </div>

                                    </div>

                                     <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="company_address_line1" class="form-label">Address line 1</label>
                                                <input type="text" class="form-control" id="company_address_line1" name="company_address_line1"
                                                    placeholder="Address line 1" value="" required>


                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="company_address_line2" class="form-label">Address line 2</label>
                                                <input type="text" class="form-control" id="company_address_line2" name="company_address_line2"
                                                    placeholder="Address line 2" value="" >


                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">

                                            <div class="mb-3">
                                                <label for="company_status" class="form-label">Status</label>
                                                <select class="form-select" id="company_status" name="company_status" required >
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









                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                                            <button type="submit" class="btn btn-primary">Save</button>
                                                        </div>
                                                        </form>
                                                    </div>
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



                                        <table id="datatable" class="table table-striped dt-responsive  nowrap w-100">
                                            <thead>
                                            <tr>
                                                <th>Id</th>
                                                <th>Name</th>
                                                <th>Email/Phone</th>
                                                <th>Addres</th>
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

    var ajaxSalesHierarchyDataURL='{{route('companies.ajax')}}';
    var ajaxSalesHierarchyDetailURL='{{route('sales.hierarchy.detail')}}';
    var ajaxSalesHierarchyDeleteURL='{{route('sales.hierarchy.delete')}}';


var csrfToken = $("[name=_token").val();


var table = $('#datatable').DataTable({
    "aoColumnDefs": [{
        "bSortable": false,
        "aTargets": [4]
    }],
    "order": [
        [0, 'desc']
    ],
    "processing": true,
    "serverSide": true,
    "ajax": {
        "url": ajaxSalesHierarchyDataURL,
        "type": "POST",
        "data": {
            "_token": csrfToken,
        }


    },
    "aoColumns": [{
            "mData": "id"
        },
        {
            "mData": "name"
        },
        {
            "mData": "email"
        },
        {
            "mData": "address"
        },
        {
            "mData": "status"
        },
        {
            "mData": "action"
        }



    ]
});

function reloadTable() {
    table.ajax.reload( null, false );
}








 var ajaxURLSearchState='{{route('users.search.state')}}';
 var ajaxURLSearchCity='{{route('users.search.city')}}';
 var ajaxURLUserDetail='{{route('company.detail')}}';



$("#company_type").select2({
    minimumResultsForSearch: Infinity,
    dropdownParent: $("#modalCompany .modal-body")

});

$("#company_country_id").select2({
    minimumResultsForSearch: Infinity,
    dropdownParent: $("#modalCompany .modal-body")

});

 $("#company_city_id").select2({
  ajax: {
    url: ajaxURLSearchCity,
    dataType: 'json',
    delay: 0,
    data: function (params) {
      return {
        "country_id":  function() { return $("#company_country_id").val()},
        "state_id":  function() { return $("#company_state_id").val()},
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
  dropdownParent: $("#modalCompany .modal-body")
});


 $("#company_state_id").select2({
  ajax: {
    url: ajaxURLSearchState,
    dataType: 'json',
    delay: 0,
    data: function (params) {
      return {
        "country_id":  function() { return $("#company_country_id").val()},
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
  dropdownParent: $("#modalCompany .modal-body")
});

 $("#company_state_id").select2({
  ajax: {
    url: ajaxURLSearchState,
    dataType: 'json',
    delay: 0,
    data: function (params) {
      return {
        "country_id":  function() { return $("#company_country_id").val()},
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
  dropdownParent: $("#modalCompany .modal-body")
});


$("#company_status").select2({
    minimumResultsForSearch: Infinity,
    dropdownParent: $("#modalCompany .modal-body")
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
    $('#formCompany').ajaxForm(options);
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
        $("#modalCompany").modal('hide');

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


$("#addBtnCompany").click(function() {

    resetInputForm();

     $("#modalCompanyLabel").html("Add Company");
     $("#company_id").val(0);
     $(".loadingcls").hide();
     $("#formCompany .row").show();
     $("#modalCompany .modal-footer").show();

});


function resetInputForm(){

     $('#modalCompany').trigger("reset");
     $("#company_status").select2("val", "1");
     $("#company_country_id").select2("val", "1");
     $("#company_state_id").empty().trigger('change');
     $("#company_city_id").empty().trigger('change');
     $("#formCompany").removeClass('was-validated');

}

function editView(id) {

     resetInputForm();

    $("#modalCompany").modal('show');
    $("#modalCompanyLabel").html("Edit Company  #" + id);
    $("#formCompany .row").hide();
    $(".loadingcls").show();


    $.ajax({
        type: 'GET',
        url: ajaxURLUserDetail + "?id=" + id,
        success: function(resultData) {
            if (resultData['status'] == 1) {


                $("#company_id").val(resultData['data']['id']);
                $("#company_name").val(resultData['data']['name']);
                $("#company_first_name").val(resultData['data']['first_name']);
                $("#company_last_name").val(resultData['data']['last_name']);
                $("#company_phone_number").val(resultData['data']['phone_number']);
                $("#company_email").val(resultData['data']['email']);

                $("#company_pincode").val(resultData['data']['pincode']);
                $("#company_address_line1").val(resultData['data']['address_line1']);
                $("#company_address_line2").val(resultData['data']['address_line2']);
                // console.log(resultData['data']['status']);
                $("#company_status").select2("val", ""+resultData['data']['status']+"");


if(typeof resultData['data']['country']['id'] !== "undefined")
{
 $("#company_country_id").select2("val", ""+resultData['data']['country']['id']+"");

}


if(typeof resultData['data']['state']['id'] !== "undefined")
{
  $("#company_state_id").empty().trigger('change');
   var newOption = new Option(resultData['data']['state']['name'], resultData['data']['state']['id'], false, false);
 $('#company_state_id').append(newOption).trigger('change');

}

if(typeof resultData['data']['city']['id'] !== "undefined")
{
  $("#company_city_id").empty().trigger('change');
   var newOption = new Option(resultData['data']['city']['name'], resultData['data']['city']['id'], false, false);
 $('#company_city_id').append(newOption).trigger('change');

}






                  $(".loadingcls").hide();
                  $("#formCompany .row").show();
                  $("#modalCompany .modal-footer").show();








            } else {

                toastr["error"](resultData['msg']);

            }

        }
    });

}

$('#company_country_id').on('change', function() {

     $("#company_state_id").empty().trigger('change');
     $("#company_city_id").empty().trigger('change');

});

 $('#company_state_id').on('change', function() {

    $("#company_city_id").empty().trigger('change');

});



</script>
@endsection