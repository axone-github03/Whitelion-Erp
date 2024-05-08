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
                                    <h4 class="mb-sm-0 font-size-18">Parameter

                                    </h4>

                                     <div class="page-title-right">





<div class="offcanvas offcanvas-end" tabindex="-1" id="canvasParameter" aria-labelledby="canvasParameterLable">
                                            <div class="offcanvas-header">
                                              <h5 id="canvasParameterLable">Main Master</h5>
                                              <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                            </div>
                                            <div class="offcanvas-body">

                                                <div class="col-md-12 text-center loadingcls">






                                            <button type="button" class="btn btn-light waves-effect">
                                                <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading
                                            </button>


                                               </div>






                                                 <form id="formParamter" class="custom-validation" action="{{route('parameter.save')}}" method="POST"  >

                                              @csrf

                                              <input type="hidden" name="parameter_id" id="parameter_id" >



                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="parameter_name" class="form-label">Name</label>
                                                <input type="text" class="form-control" id="parameter_name" name="parameter_name"
                                                    placeholder="Name" value="" disabled>


                                            </div>
                                        </div>

                                    </div>

                                      <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="parameter_description" class="form-label">Description</label>
                                                <textarea class="form-control" id="parameter_description" name="parameter_description"
                                                    placeholder="Description" value="" ></textarea>


                                            </div>
                                        </div>

                                    </div>

                                      <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="parameter_name" class="form-label">Value</label>
                                                <input type="text" class="form-control" id="parameter_name_value" name="parameter_name_value"
                                                    placeholder="Value" value="" required>


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


                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">



                                        <table id="datatable" class="table table-striped dt-responsive  nowrap w-100">
                                            <thead>
                                            <tr>

                                                <th>Name</th>
                                                <th>Description</th>
                                                <th>Value</th>
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

    var ajaxMainMasterDataURL='{{route('parameter.ajax')}}';
    var ajaxMainMasterDetailURL='{{route('parameter.detail')}}';



var csrfToken = $("[name=_token").val();


var table = $('#datatable').DataTable({
    "aoColumnDefs": [{
        "bSortable": false,
        "aTargets": [3]
    }],
    "order":[[ 0, 'desc' ]],
    "processing": true,
    "serverSide": true,
    "ajax": {
        "url": ajaxMainMasterDataURL,
        "type": "POST",
        "data": {
            "_token": csrfToken,
        }


    },
    "aoColumns": [
        {
            "mData": "name"
        },
        {
            "mData": "description"
        },
        {
            "mData": "name_value"
        },
        {
            "mData": "action"
        }



    ]
});

function reloadTable() {
    table.ajax.reload( null, false );
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
    $('#formParamter').ajaxForm(options);
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
        $("#canvasParameter").offcanvas('hide');

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





function resetInputForm(){

    $('#formParamter').trigger("reset");
    $("#parameter_id").val(0);


}

function editView(id) {

     resetInputForm();

    $("#canvasParameter").offcanvas('show');
    $("#canvasParameterLable").html("Edit Parameter #" + id);
    $("#formParamter").hide();
    $(".loadingcls").show();

    $.ajax({
        type: 'GET',
        url: ajaxMainMasterDetailURL + "?id=" + id,
        success: function(resultData) {
            if (resultData['status'] == 1) {

                $("#parameter_id").val(resultData['data']['id']);
                $("#parameter_name").val(resultData['data']['name']);
                $("#parameter_description").val(resultData['data']['description']);
                if(resultData['data']['type']=="number"){
                    $("#parameter_name_value").prop("type","number");

                }else if(resultData['data']['type']=="string"){
                    $("#parameter_name_value").prop("type","text");
                }


                $("#parameter_name_value").val(resultData['data']['name_value']);
                $(".loadingcls").hide();
                $("#formParamter").show();


            } else {

                toastr["error"](resultData['msg']);

            }

        }
    });

}


</script>
@endsection