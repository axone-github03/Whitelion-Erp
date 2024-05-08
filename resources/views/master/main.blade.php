@extends('layouts.main')
@section('title', $data['title'])
@section('content')

<style type="text/css">
    td p{
    max-width: 100%;
    white-space: break-spaces;
        word-break: break-all;
    }
</style>

                <div class="page-content">
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0 font-size-18">Main Master

                                    </h4>

                                     <div class="page-title-right">


<button id="addBtnMainMaster" class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#canvasMainMaster" aria-controls="canvasMainMaster"><i class="bx bx-plus font-size-16 align-middle me-2"></i>Main Master </button>


<div class="offcanvas offcanvas-end" tabindex="-1" id="canvasMainMaster" aria-labelledby="canvasMainMasterLable">
                                            <div class="offcanvas-header">
                                              <h5 id="canvasMainMasterLable">Main Master</h5>
                                              <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                            </div>
                                            <div class="offcanvas-body">

                                                <div class="col-md-12 text-center loadingcls">






                                            <button type="button" class="btn btn-light waves-effect">
                                                <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading
                                            </button>


                                               </div>






                                                 <form id="formMainMaster" class="custom-validation" action="{{route('main.master.save')}}" method="POST"  >

                                              @csrf

                                              <input type="hidden" name="main_master_id" id="main_master_id" >



                                                <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="main_master_name" class="form-label">Name</label>
                                                <input type="text" class="form-control" id="main_master_name" name="main_master_name"
                                                    placeholder="Name" value="" required>


                                            </div>
                                        </div>

                                    </div>

                                      <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="main_master_code" class="form-label">Code</label>
                                                <input type="text" class="form-control" id="main_master_code_d" name="main_master_code_d"
                                                    placeholder="" value="" disabled >
                                            <input type="hidden" name="main_master_code" id="main_master_code" >


                                            </div>
                                        </div>

                                    </div>






                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="main_master_status" class="form-label">Status</label>

                                                <select id="main_master_status" name="main_master_status" class="form-control select2-apply" >
                                                    <option value="1">Active</option>
                                                    <option value="0">Inactive</option>
                                                    <option value="2">Blocked</option>


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
                                                <th>Name</th>
                                                <th>Code</th>
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

    var ajaxMainMasterDataURL='{{route('main.master.ajax')}}';
    var ajaxMainMasterDetailURL='{{route('main.master.detail')}}';



var csrfToken = $("[name=_token").val();

var mainMasterPageLength= getCookie('mainMasterPageLength')!==undefined?getCookie('mainMasterPageLength'):10;
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
    "pageLength": mainMasterPageLength,
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
            "mData": "name"
        },
        {
            "mData": "code"
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

    setCookie('mainMasterPageLength',len,100);


});




$("#main_master_status").select2({
    minimumResultsForSearch: Infinity,
    dropdownParent: $("#canvasMainMaster")
});



function generateHierarchyCode(dInput) {

    dInput = dInput.replace(/[_\W]+/g, "_")
    dInput = dInput.toUpperCase();
    $("#main_master_code_d").val(dInput);
    $("#main_master_code").val(dInput)


}



$("#main_master_name").keypress(function() {
    generateHierarchyCode(this.value);
});

$("#main_master_name").change(function() {
    generateHierarchyCode(this.value);
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
    $('#formMainMaster').ajaxForm(options);
});

function showRequest(formData, jqForm, options) {
    generateHierarchyCode($("#main_master_name").val());
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
        $("#canvasMainMaster").offcanvas('hide');

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


$("#addBtnMainMaster").click(function() {

    $("#canvasMainMasterLable").html("Add Main Master");
    $("#formMainMaster").show();
    $(".loadingcls").hide();
    resetInputForm();






});


function resetInputForm(){

    $('#formMainMaster').trigger("reset");
    $("#main_master_id").val(0);
    $("#main_master_status").select2("val", "1");

}

function editView(id) {

     resetInputForm();

    $("#canvasMainMaster").offcanvas('show');
    $("#canvasMainMasterLable").html("Edit Main Master #" + id);
    $("#formMainMaster").hide();
    $(".loadingcls").show();

    $.ajax({
        type: 'GET',
        url: ajaxMainMasterDetailURL + "?id=" + id,
        success: function(resultData) {
            if (resultData['status'] == 1) {

                $("#main_master_id").val(resultData['data']['id']);
                $("#main_master_name").val(resultData['data']['name']);
                $("#main_master_code").val(resultData['data']['code']);
                $("#main_master_code_d").val(resultData['data']['code']);
                $("#main_master_status").select2("val", ""+resultData['data']['status']+"");








                $(".loadingcls").hide();
                $("#formMainMaster").show();


            } else {

                toastr["error"](resultData['msg']);

            }

        }
    });

}


</script>
@endsection