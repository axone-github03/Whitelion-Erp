@extends('layouts.main')
@section('title', $data['title'])
@section('content')

<style type="text/css">
    td p{
    max-width: 100%;
    white-space: break-spaces;
        word-break: break-all;
    }
    td{
        vertical-align: middle;
    }
</style>

                <div class="page-content">
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0 font-size-18"> Database Master - Move Assignee

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

                                                      <form  id="formMoveAssignee" action="{{route('database.master.move.assignee.save')}}" method="POST"  class="needs-validation" novalidate>
                                                    <div class="modal-body">

                                                                @csrf

                                                                <div class="row">

                                         <div class="col-lg-12">
                                                    <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                        <label class="form-label">User Type</label>
                                                        <select  class="form-control select2-ajax" id="user_type" name="user_type" >
                                                            <option value="201,202">Architect</option>
                                                            <option value="301,302">Electrician</option>


                                                        </select>

                                                    </div>

                                                </div>




                                    </div>





  <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="from_assignee" class="form-label">From Assignee</label>


                                                 <select id="from_assignee" name="from_assignee" class="form-control select2-ajax" required  >
                                                        </select>
                                                        <div class="invalid-feedback">
                                                    Please select from assignee.
                                                </div>


                                            </div>
                                        </div>

                                    </div>

                                     <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="to_assignee" class="form-label">To Assignee</label>

<select id="to_assignee" name="to_assignee" class="form-control select2-ajax" required  >
                                                        </select>
                                                          <div class="invalid-feedback">
                                                    Please select to assignee.
                                                </div>


                                            </div>
                                        </div>

                                    </div>









                                                        </div>
                                                        <div class="modal-footer">

                                                            <button type="submit" class="btn btn-primary">Move</button>
                                                        </div>
                                                        </form>





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

var ajaxSearchAssigneee='{{route('database.master.move.assignee.search.assigned.user')}}';


$("#user_type").select2();


$("#from_assignee").select2({
    ajax: {
        url: ajaxSearchAssigneee,
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
    placeholder: 'Search for from assignee',



});

$("#to_assignee").select2({
    ajax: {
        url: ajaxSearchAssigneee,
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
    placeholder: 'Search for to assignee',



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
    $('#formMoveAssignee').ajaxForm(options);
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
         $('#formMoveAssignee').trigger("reset");
         $("#from_assignee").empty().trigger('change');
         $("#to_assignee").empty().trigger('change');
         $("#formMoveAssignee").removeClass('was-validated');


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