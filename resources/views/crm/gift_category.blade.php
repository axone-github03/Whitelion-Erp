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
                                    <h4 class="mb-sm-0 font-size-18">Gift Category

                                    </h4>

                                     <div class="page-title-right">


<button id="addBtnGiftCategory" class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#canvasGiftCategory" aria-controls="canvasGiftCategory"><i class="bx bx-plus font-size-16 align-middle me-2"></i>Gift Category </button>


<div class="offcanvas offcanvas-end" tabindex="-1" id="canvasGiftCategory" aria-labelledby="canvasGiftCategoryLable">
                                            <div class="offcanvas-header">
                                              <h5 id="canvasGiftCategoryLable"></h5>
                                              <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                            </div>
                                            <div class="offcanvas-body">

                                                <div class="col-md-12 text-center loadingcls">






                                            <button type="button" class="btn btn-light waves-effect">
                                                <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading
                                            </button>


                                               </div>






                                                 <form id="formGiftCategory" class="custom-validation" action="{{route('gift.category.save')}}" method="POST"  >

                                              @csrf

                                              <input type="hidden" name="gift_category_id" id="gift_category_id" >


                                               <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="gift_category_type" class="form-label">Type</label>

                                                <select id="gift_category_type" name="gift_category_type" class="form-control select2-apply" >

                                            @foreach($data['gift_category_type'] as $giftCategoryType)

                                              <option value="{{$giftCategoryType['id']}}">{{$giftCategoryType['another_name']}}</option>

                                            @endforeach



                                                </select>



                                            </div>
                                        </div>

                                    </div>





                                                <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="gift_category_name" class="form-label">Name</label>
                                                <input type="text" class="form-control" id="gift_category_name" name="gift_category_name"
                                                    placeholder="Name" value="" required>


                                            </div>
                                        </div>

                                    </div>








                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="gift_category_status" class="form-label">Status</label>

                                                <select id="gift_category_status" name="gift_category_status" class="form-control select2-apply" >
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
                                                <th>Name</th>
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

    var ajaxCategoryURL='{{route('gift.category.ajax')}}';
    var ajaxCategoryDetailURL='{{route('gift.category.detail')}}';

  $("#gift_category_type").select2({
    minimumResultsForSearch: -1,
        dropdownParent: $("#canvasGiftCategory")
  });

var csrfToken = $("[name=_token").val();
var giftCategoryPageLength= getCookie('giftCategoryPageLength')!==undefined?getCookie('giftCategoryPageLength'):10;

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
     "pageLength": giftCategoryPageLength,
    "ajax": {
        "url": ajaxCategoryURL,
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

    setCookie('giftCategoryPageLength',len,100);


});






$("#gift_category_status").select2({
    minimumResultsForSearch: Infinity,
    dropdownParent: $("#canvasGiftCategory")
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
    $('#formGiftCategory').ajaxForm(options);
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
        $("#canvasGiftCategory").offcanvas('hide');

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

    $("#canvasGiftCategoryLable").html("Add Gift Category");
    $("#formGiftCategory").show();
    $(".loadingcls").hide();
    resetInputForm();






});


function resetInputForm(){

    $('#formGiftCategory').trigger("reset");
    $("#gift_category_id").val(0);
    $("#gift_category_status").select2("val", "1");

}

function editView(id) {

     resetInputForm();

    $("#canvasGiftCategory").offcanvas('show');
    $("#canvasGiftCategoryLable").html("Edit Gift Category #" + id);
    $("#formGiftCategory").hide();
    $(".loadingcls").show();

    $.ajax({
        type: 'GET',
        url: ajaxCategoryDetailURL + "?id=" + id,
        success: function(resultData) {
            if (resultData['status'] == 1) {

                $("#gift_category_id").val(resultData['data']['id']);
                $("#gift_category_name").val(resultData['data']['name']);
                $
                $("#gift_category_status").select2("val", ""+resultData['data']['status']+"");
                $("#gift_category_type").val(resultData['data']['type']);
                $("#gift_category_type").trigger('change');








                $(".loadingcls").hide();
                $("#formGiftCategory").show();


            } else {

                toastr["error"](resultData['msg']);

            }

        }
    });

}


</script>
@endsection