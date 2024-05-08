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
                                    <h4 class="mb-sm-0 font-size-18">Help Document

                                    </h4>

                                     <div class="page-title-right">


<button id="addBtnGiftCategory" class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#canvasHelpDocument" aria-controls="canvasHelpDocument"><i class="bx bx-plus font-size-16 align-middle me-2"></i>Help Document </button>


<div class="offcanvas offcanvas-end" tabindex="-1" id="canvasHelpDocument" aria-labelledby="canvasHelpDocumentLable">
                                            <div class="offcanvas-header">
                                              <h5 id="canvasHelpDocumentLable"></h5>
                                              <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                            </div>
                                            <div class="offcanvas-body">

                                                <div class="col-md-12 text-center loadingcls">






                                            <button type="button" class="btn btn-light waves-effect">
                                                <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading
                                            </button>


                                               </div>






                                                 <form id="formCRMHelpDocument" class="custom-validation" action="{{route('crm.help.document.save')}}" method="POST"  >

                                              @csrf

                                              <input type="hidden" name="crm_help_document_id" id="crm_help_document_id" >


                                                      <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="crm_help_document_type" class="form-label">Type</label>

                                                <select id="crm_help_document_type" name="crm_help_document_type" class="form-control select2-apply" >

                                            @foreach($data['crm_user_type'] as $crmUserType)

                                              <option value="{{$crmUserType['id']}}">{{$crmUserType['another_name']}}</option>

                                            @endforeach



                                                </select>



                                            </div>
                                        </div>

                                    </div>

                                                   <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="crm_help_document_publish_date_time" class="form-label">Publish Date</label>
                                                <input type="datetime-local" class="form-control" id="crm_help_document_publish_date_time" name="crm_help_document_publish_date_time"
                                                    placeholder="Title" value="" required>


                                            </div>
                                        </div>

                                    </div>



                                                <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="crm_help_document_title" class="form-label">Title</label>
                                                <input type="text" class="form-control" id="crm_help_document_title" name="crm_help_document_title"
                                                    placeholder="Title" value="" required>


                                            </div>
                                        </div>

                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="crm_help_document_file" class="form-label"><span id="crm_help_document_file_lable"></span></label>
                                                <input type="file" class="form-control" id="crm_help_document_file" name="crm_help_document_file"
                                                    placeholder="File" value="" required>


                                            </div>
                                        </div>

                                    </div>








                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="crm_help_document_status" class="form-label">Status</label>

                                                <select id="crm_help_document_status" name="crm_help_document_status" class="form-control select2-apply" >
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
                                        <button type="reset" class="btn btn-secondary waves-effect">
                                            Reset
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
                                                <th>Id</th>
                                                <th>Publish Date & Time</th>
                                                <th>Title</th>
                                                <th>Download</th>
                                                <th>Type</th>
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

    var ajaxMainMasterDataURL='{{route('crm.help.document.ajax')}}';
    var ajaxMainMasterDetailURL='{{route('crm.help.document.detail')}}';



  $("#crm_help_document_type").select2({
    minimumResultsForSearch: -1,
     dropdownParent: $("#canvasHelpDocument")
  });


var csrfToken = $("[name=_token").val();

var helpDocumentPageLength= getCookie('helpDocumentPageLength')!==undefined?getCookie('helpDocumentPageLength'):10
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
    "pageLength": helpDocumentPageLength,
    "ajax": {
        "url": ajaxMainMasterDataURL,
        "type": "POST",
        "data": {
            "_token": csrfToken,
        }


    },
    "aoColumns": [{
            "mData": "id"
        },
        {
            "mData": "publish_date_time"
        },
        {
            "mData": "title"
        },
        {
            "mData": "download"
        },
        {
            "mData": "type"
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

$('#datatable').on( 'length.dt', function ( e, settings, len ) {

    setCookie('helpDocumentPageLength',len,100);


});







$("#crm_help_document_status").select2({
    minimumResultsForSearch: Infinity,
    dropdownParent: $("#canvasHelpDocument")
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
    $('#formCRMHelpDocument').ajaxForm(options);
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
        $("#canvasHelpDocument").offcanvas('hide');

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


$("#addBtnGiftCategory").click(function() {

    $("#canvasHelpDocumentLable").html("Add Help Document");
    $("#formCRMHelpDocument").show();
    $(".loadingcls").hide();
    resetInputForm();






});


function resetInputForm(){

    $('#formCRMHelpDocument').trigger("reset");
    $("#crm_help_document_id").val(0);
    $("#crm_help_document_status").select2("val", "1");
    $("#crm_help_document_file").prop('required',true);
    $("#crm_help_document_file_lable").html("File");

}

function editView(id) {

     resetInputForm();

    $("#canvasHelpDocument").offcanvas('show');
    $("#canvasHelpDocumentLable").html("Edit Help Document #" + id);
    $("#formCRMHelpDocument").hide();
    $(".loadingcls").show();

     $("#crm_help_document_file").removeAttr('required');
     $("#crm_help_document_file_lable").html("Replace File");

    $.ajax({
        type: 'GET',
        url: ajaxMainMasterDetailURL + "?id=" + id,
        success: function(resultData) {
            if (resultData['status'] == 1) {

                $("#crm_help_document_id").val(resultData['data']['id']);
                $("#crm_help_document_title").val(resultData['data']['title']);
                $("#crm_help_document_publish_date_time").val(resultData['data']['publish_date_time']);
                $("#crm_help_document_status").select2("val", ""+resultData['data']['status']+"");


                $("#crm_help_document_type").val(resultData['data']['type']);
                $("#crm_help_document_type").trigger('change');


                $(".loadingcls").hide();
                $("#formCRMHelpDocument").show();


            } else {

                toastr["error"](resultData['msg']);

            }

        }
    });

}


</script>
@endsection