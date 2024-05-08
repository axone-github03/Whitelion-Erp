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
                                    <h4 class="mb-sm-0 font-size-18">Roll Master

                                    </h4>

                                     <div class="page-title-right">

                                        <a onclick="editView('0')" class="btn btn-primary" href="javascript: void(0);" title="Edit"><i class="bx bx-shield-quarter"></i></a>





</div>






                                    </div>



                                </div>


                            </div>
                        </div>
                        <!-- end page title -->

                        <div class="modal fade" id="modalUser" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" role="dialog" aria-labelledby="modalUserLabel" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="modalUserLabel"> User</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>




                                                        <form  id="formUser" action="{{route('roll.master.save')}}" method="POST"  class="" novalidate>
                                                    <div class="modal-body">

                                                                @csrf


                                                                   <div class="col-md-12 text-center loadingcls">






                                            <button type="button" class="btn btn-light waves-effect">
                                                <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading
                                            </button>


                                               </div>

                                                <input type="hidden" name="user_id" id="user_id"  >
                                                <input type="hidden" name="user_type" id="user_type">

                                                @foreach($data['privilege'] as $privilege)

                                                 <div class="form-check form-switch mb-3">
                                                <input class="form-check-input privilege-checkbox" type="checkbox" name="privilege_{{$privilege->privilege->code}}" id="privilege_{{$privilege->privilege->code}}" >
                                                        <label class="form-check-label" for="privilege_{{$privilege->privilege->code}}">{{$privilege->privilege->name}}</label>
                                                </div>

                                                @endforeach















                                                    </div>





                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                                            <button id="btnSave" type="submit" class="btn btn-primary">Save</button>
                                                        </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>


                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">

                                          @php
$accessTypes=getAllUserTypes();
 @endphp
                                          <div class="d-flex flex-wrap gap-2 userscomman">

                                           @foreach($accessTypes as $key=>$value)

                                           @if($value['can_login']==1)
                                            <a href="{{route('roll.master')}}?type={{$value['id']}}" class="btn btn-outline-primary waves-effect waves-light">{{$value['name']}}</a>

                                            @endif
                                          @endforeach

</div>
<br>






                                        <table id="datatable" class="table table-striped dt-responsive  nowrap w-100">
                                            <thead>
                                            <tr>
                                                <th>Id</th>
                                                <th>First Name</th>
                                                <th>Last Name</th>
                                                <th>Email</th>
                                                <th>Phone Number</th>
                                                <th>Type</th>
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

    var ajaxRollMasterDataURL='{{route('roll.master.ajax')}}';
    var ajaxRollMasterDetailURL='{{route('roll.master.detail')}}';
    var userType={{$data['type']}};
    var userTypeName='{{$data['type_name']}}';
    var privilegeJSON={!!json_encode($data['privilege'])!!};




var csrfToken = $("[name=_token").val();

var rollMasterPageLength= getCookie('rollMasterPageLength')!==undefined?getCookie('rollMasterPageLength'):10;
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
    "pageLength": rollMasterPageLength,
    "ajax": {
        "url": ajaxRollMasterDataURL,
        "type": "POST",
        "data": {
            "_token": csrfToken,
            "type":userType,

        }


    },
    "aoColumns": [{
            "mData": "id"
        },
        {
            "mData": "first_name"
        },
        {
            "mData": "last_name"
        },
        {
            "mData": "email"
        },
        {
            "mData": "phone_number"
        },
        {
            "mData": "type"
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

    setCookie('rollMasterPageLength',len,100);


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
    $("#btnSave").html("Saving...");
    $("#btnSave").prop("disabled",true);
    return true;
}

// post-submit callback
function showResponse(responseText, statusText, xhr, $form) {

     $("#btnSave").html("Save");
    $("#btnSave").prop("disabled",false);


    if (responseText['status'] == 1) {
        toastr["success"](responseText['msg']);

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






function editView(id) {

    $("#modalUser").modal('show');

   if(id==0){
    $("#modalUserLabel").html("Edit for all "+userTypeName);
   }else{
    $("#modalUserLabel").html("Edit User #" + id);
   }



    $("#formUser .form-check ").hide();
    $(".loadingcls").show();
    $("#modalUser .modal-footer").hide();
    $(".privilege-checkbox").prop("checked",false);


 if(id!=0){

     $.ajax({
        type: 'GET',
        url: ajaxRollMasterDetailURL + "?id=" + id,
        success: function(resultData) {
            if (resultData['status'] == 1) {

                   $("#user_id").val(resultData['data']['id']);
                   $("#formUser .form-check").show();
                   $(".loadingcls").hide();
                   $("#modalUser .modal-footer").show();
                   for (var i = 0; i < privilegeJSON.length; i++) {


                    if(typeof resultData['data']['privilege'][privilegeJSON[i]['privilege']['code']] !== "undefined" && resultData['data']['privilege'][privilegeJSON[i]['privilege']['code']]==1){


                        $("#privilege_"+privilegeJSON[i]['privilege']['code']).prop("checked",true);


                   }

                    // console.log(resultData['data']['privilege'][privilegeJSON[i]['privilege']['code']])
                       // console.log(privilegeJSON[i]['privilege']['code']);
                   }

            }


        }
    });
 }else{

                  $("#user_id").val(0);
                  $("#user_type").val(userType);
                   $("#formUser .form-check").show();
                   $(".loadingcls").hide();
                   $("#modalUser .modal-footer").show();

 }






}

var currentURL=window.location.href;
var loadedURLLink = $('.userscomman a[href="'+currentURL+'"]');
$(loadedURLLink).removeClass('btn-outline-primary');
$(loadedURLLink).addClass('btn-primary');


</script>
@endsection