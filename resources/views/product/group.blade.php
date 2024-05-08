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
                                    <h4 class="mb-sm-0 font-size-18">Product Group

                                    </h4>

                                     <div class="page-title-right">


<button id="addBtnProductGroup" class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#canvasProductGroup" aria-controls="canvasProductGroup"><i class="bx bx-plus font-size-16 align-middle me-2"></i>Product Group</button>


<div class="offcanvas offcanvas-end" tabindex="-1" id="canvasProductGroup" aria-labelledby="canvasProductGroupLabel">
                                            <div class="offcanvas-header">
                                              <h5 id="canvasProductGroupLabel">Product Group</h5>
                                              <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                            </div>
                                            <div class="offcanvas-body">

                                                <div class="col-md-12 text-center loadingcls">






                                            <button type="button" class="btn btn-light waves-effect">
                                                <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading
                                            </button>


                                               </div>






                                                 <form id="formProductGroup" class="custom-validation" action="{{route('product.group.save')}}" method="POST"  >

                                              @csrf

                                              <input type="hidden" name="product_group_id" id="product_group_id" >



                                                <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="product_group_name" class="form-label">Name</label>
                                                <input type="text" class="form-control" id="product_group_name" name="product_group_name"
                                                    placeholder="Name" value="" required>


                                            </div>
                                        </div>

                                    </div>




                                     <div class="row">

                                         <div class="col-lg-12">
                                                    <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                        <label class="form-label">Product Brand </label>
                                                        <select  multiple="multiple" class="form-control select2-ajax select2-multiple" id="product_brand" name="product_brand[]" >

                                                        </select>

                                                    </div>

                                                </div>




                                    </div>


                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="product_group_status" class="form-label">Status</label>

                                                <select id="product_group_status" name="product_group_status" class="form-control select2-apply" >
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
                                                <th>Product Brand</th>
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

var ajaxProductGroupDataURL='{{route('product.group.ajax')}}';
var ajaxProductGroupURL='{{route('product.group.detail')}}';
var ajaxProductGroupSearchBrandURL='{{route('product.group.search.brand')}}';
var ajaxProductGroupDeleteURL='{{route('product.group.delete')}}';


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
        "url": ajaxProductGroupDataURL,
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
            "mData": "product_brand"
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




$("#product_brand").select2({
    ajax: {
        url: ajaxProductGroupSearchBrandURL,
        dataType: 'json',
        delay: 0,
        data: function(params) {
            return {
                id:  function() { return $("#product_group_id").val() },
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
    placeholder: 'Search for a parent sales hierarchy',
    allowClear: true,
    dropdownParent: $("#canvasProductGroup")
});


$("#product_group_status").select2({
    minimumResultsForSearch: Infinity,
    dropdownParent: $("#canvasProductGroup")
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
    $('#formProductGroup').ajaxForm(options);
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
        $("#canvasProductGroup").offcanvas('hide');

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


$("#addBtnProductGroup").click(function() {

    $("#canvasProductGroupLabel").html("Add Product Group");
    $("#formProductGroup").show();
    $(".loadingcls").hide();
    resetInputForm();

});


function resetInputForm(){

    $('#formProductGroup').trigger("reset");
    $("#product_brand").empty().trigger('change');
    $("#product_group_id").val(0);
    $("#product_group_status").select2("val", "1");

}

function editView(id) {

     resetInputForm();

    $("#canvasProductGroup").offcanvas('show');
    $("#canvasProductGroupLabel").html("Edit Product Group #" + id);
    $("#formProductGroup").hide();
    $(".loadingcls").show();

    $.ajax({
        type: 'GET',
        url: ajaxProductGroupURL + "?id=" + id,
        success: function(resultData) {
            if (resultData['status'] == 1) {

                $("#product_group_id").val(resultData['data']['id']);
                $("#product_group_name").val(resultData['data']['name']);
                $("#product_group_status").select2("val", ""+resultData['data']['status']+"");



                if (resultData['product_brand'].length>0) {



                   $("#product_brand").empty().trigger('change');

                   var selectProductBrand=[];


        for (var i = 0; i < resultData['product_brand'].length; i++) {

            selectProductBrand.push(''+resultData['product_brand'][i]['id']+'');


            var newOption = new Option(resultData['product_brand'][i]['text'],resultData['product_brand'][i]['id'], false, false);
              $('#product_brand').append(newOption).trigger('change');
        }
        $("#product_brand").val(selectProductBrand).change();

                } else {
                    $("#product_brand").empty().trigger('change');
                }




                $(".loadingcls").hide();
                $("#formProductGroup").show();


            } else {

                toastr["error"](resultData['msg']);

            }

        }
    });

}

function deleteWarning(id) {


    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: !0,
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "No, cancel!",
        confirmButtonClass: "btn btn-success mt-2",
        cancelButtonClass: "btn btn-danger ms-2 mt-2",
        loaderHtml: "<i class='bx bx-hourglass bx-spin font-size-16 align-middle me-2'></i> Loading",
        customClass: {
            confirmButton: 'btn btn-primary btn-lg',
            cancelButton: 'btn btn-danger btn-lg',
            loader: 'custom-loader'
        },
        buttonsStyling: !1,
        preConfirm: function(n) {
            return new Promise(function(t, e) {

                Swal.showLoading()


                $.ajax({
                    type: 'GET',
                    url: ajaxProductGroupDeleteURL + "?id=" + id,
                    success: function(resultData) {

                        if (resultData['status'] == 1) {

                            reloadTable();
                            t()



                        }




                    }
                });



            })
        },
    }).then(function(t) {

        if (t.value === true) {



            Swal.fire({
                title: "Deleted!",
                text: "Your record has been deleted.",
                icon: "success"
            });


        }

    });



}
</script>
@endsection