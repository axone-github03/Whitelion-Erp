@extends('layouts.main')
@section('title', $data['title'])
@section('content')

<style type="text/css">
    td p{
    max-width: 100%;
    white-space: break-spaces;
        word-break: break-all;
    }
    #imgPreview{
        width: 100% !important;
        height: 100% !important;
    }
    #div_product_inventory_image{
width:100px;
height: 100px;
padding: 4px;
margin: 0 auto;
cursor: pointer;
    }

</style>



                <div class="page-content">
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0 font-size-18">Product Inventory

                                    </h4>

                                     <div class="page-title-right">







<div class="offcanvas offcanvas-end" tabindex="-1" id="canvasProductInventory" aria-labelledby="canvasProductInventoryLable">
                                            <div class="offcanvas-header">
                                              <h5 id="canvasProductInventoryLable">Product Inventory</h5>
                                              <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                            </div>
                                            <div class="offcanvas-body">

                                                <div class="col-md-12 text-center loadingcls">






                                            <button type="button" class="btn btn-light waves-effect">
                                                <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading
                                            </button>


                                               </div>






                                                 <form enctype="multipart/form-data" id="formProductInventory" class="custom-validation" action="{{route('product.inventory.save.stock')}}" method="POST"  >
 <input type="file" name="product_inventory_image"  id="product_inventory_image"  accept="image/*" style="display:none"/>

                                      <div class="row" id="row_product_inventory_image" >
                            <div class="col-lg-12">







                                             <div class="" style="" id="div_product_inventory_image" >



                                                    <img id="imgPreview" src="{{ asset('s/product/placeholder.png') }}" alt="" class="img-thumbnail">

                                                </div>









                            </div>
                        </div>

                                              @csrf

                                              <input type="hidden" name="product_inventory_id" id="product_inventory_id" >

                                <input type="hidden" name="product_inventory_type_process" id="product_inventory_type_process" >


                                        <div class="row">

                                         <div class="col-lg-12">
                                                    <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                        <label class="form-label">Product Brand </label>
                                                        <select  class="form-control select2-ajax" id="product_brand_id" name="product_brand_id" required >

                                                        </select>

                                                    </div>

                                                </div>




                                    </div>

                                      <div class="row">

                                         <div class="col-lg-12">
                                                    <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                        <label class="form-label">Product Code </label>
                                                        <select  class="form-control select2-ajax" id="product_code_id" name="product_code_id" required >

                                                        </select>

                                                    </div>

                                                </div>




                                    </div>



                                <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="product_inventory_description" class="form-label">Description</label>
                                                <textarea type="text" class="form-control" id="product_inventory_description" name="product_inventory_description"
                                                    placeholder="Description" value="" required ></textarea>


                                            </div>
                                        </div>

                                    </div>


                                     <div class="row ">

                                            <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="product_inventory_quantity" class="form-label">Quantity</label>
                                                <input type="number" class="form-control" id="product_inventory_quantity" name="product_inventory_quantity"
                                                    placeholder="Quantity" value="" required>


                                            </div>
                                        </div>

                                           <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="product_inventory_price" class="form-label">Price (<i class="fas fa-rupee-sign"></i>)</label>
                                                <input type="number" class="form-control" id="product_inventory_price" name="product_inventory_price"
                                                    placeholder="Price" value="" required>


                                            </div>
                                        </div>
                                         <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="product_inventory_weight" class="form-label">Weight(gm)</label>
                                                <input type="number" class="form-control" id="product_inventory_weight" name="product_inventory_weight"
                                                    placeholder="Weight" value="" required>


                                            </div>
                                        </div>





                                    </div>

                                      <div class="row" id="row_quantity_plus" >

                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="product_inventory_quantity_plus" class="form-label">Add Quantity</label>
                                                <input type="number" class="form-control" id="product_inventory_quantity_plus" name="product_inventory_quantity_plus"
                                                    placeholder="Add Quantity" value="" required>


                                            </div>
                                        </div>
                                    </div>

                                     <div class="row" id="row_quantity_minus" >

                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="product_inventory_quantity_minus" class="form-label">Remove Quantity</label>
                                                <input type="number" class="form-control" id="product_inventory_quantity_minus" name="product_inventory_quantity_minus"
                                                    placeholder="Remove Quantity" value="" required>


                                            </div>
                                        </div>
                                    </div>

                                    <div class="row" id="row_quantity_purpose"  >

                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="product_inventory_purpose" class="form-label">Purpose</label>

                                                <textarea class="form-control" id="product_inventory_purpose" name="product_inventory_purpose"
                                                    placeholder="Purpose" value="" ></textarea>



                                            </div>
                                        </div>
                                    </div>









                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="product_inventory_status" class="form-label">Status</label>

                                                <select id="product_inventory_status" name="product_inventory_status" class="form-control select2-apply" >
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


                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">



                                        <table id="datatable" class="table table-striped dt-responsive  nowrap w-100">
                                            <thead>
                                            <tr>
                                                <th>Id</th>
                                                <th>Product Brand</th>
                                                <th>Product Code</th>
                                                <th>Description</th>
                                                <th>Price</th>
                                                <th>Weight</th>
                                                <th>Quantity</th>
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

    var ajaxProductInventoryURL='{{route('product.inventory.ajax.stock')}}';
    var ajaxProductInventoryDetailURL='{{route('product.inventory.detail.stock')}}';
    var ajaxProductInventorySearchBrandURL='{{route('product.inventory.search.brand')}}';
    var ajaxProductInventorySearchCodeURL='{{route('product.inventory.search.code')}}';

    var ImageProductPlaceHolder='{{ asset('s/product/placeholder.png') }}';
    var ajaxProductInventoryDiscount='{{route('product.inventory.discount.ajax')}}';
    var ajaxProductInventoryDiscountSave='{{route('product.inventory.discount.save')}}';
    var ajaxProductInventoryDiscountSaveAll='{{route('product.inventory.discount.save.all')}}';



var csrfToken = $("[name=_token").val();


var table = $('#datatable').DataTable({
    "aoColumnDefs": [{
        "bSortable": false,
        "aTargets": [7]
    }],
    "order": [
        [0, 'desc']
    ],
    "processing": true,
    "serverSide": true,
    "ajax": {
        "url": ajaxProductInventoryURL,
        "type": "POST",
        "data": {
            "_token": csrfToken,
        }


    },
    "aoColumns": [{
            "mData": "id"
        },
        {
            "mData": "product_brand"
        },
        {
            "mData": "product_code"
        },
        {
            "mData": "description"
        },
        {
            "mData": "price"
        },
        {
            "mData": "weight"
        },
        {
            "mData": "quantity"
        },
        {
            "mData": "action"
        }



    ]
});

function reloadTable() {
    table.ajax.reload( null, false );
}







$("#product_inventory_status").select2({
    minimumResultsForSearch: Infinity,
    dropdownParent: $("#canvasProductInventory")
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
    $('#formProductInventory').ajaxForm(options);
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
        $("#canvasProductInventory").offcanvas('hide');

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



$("#product_brand_id").select2({
    ajax: {
        url: ajaxProductInventorySearchBrandURL,
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
    placeholder: 'Search for a product brand',
    dropdownParent: $("#canvasProductInventory")

});


$("#product_code_id").select2({
    ajax: {
        url: ajaxProductInventorySearchCodeURL,
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
    placeholder: 'Search for a product code',
    dropdownParent: $("#canvasProductInventory")

});


$("#addBtnProductInventory").click(function() {

    $("#canvasProductInventoryLable").html("Add Product Inventory");
    $("#formProductInventory").show();
    $(".loadingcls").hide();
    resetInputForm();
    $("#row_product_inventory_image").show();










});


function resetInputForm(){



     $("#product_brand_id").prop('disabled',false);
     $("#product_code_id").prop('disabled',false);
     $("#product_inventory_description").prop('disabled',false);
     $("#product_inventory_quantity").prop('disabled',false);
     $("#product_inventory_price").prop('disabled',false);
     $("#product_inventory_weight").prop('disabled',false);

    $('#formProductInventory').trigger("reset");
    $("#product_inventory_id").val(0);
    $("#product_inventory_status").select2("val", "1");
    $("#product_brand_id").empty().trigger('change');
    $("#product_code_id").empty().trigger('change');

    $("#row_quantity_plus").hide();
    $("#row_quantity_minus").hide();
    $("#row_quantity_purpose").hide();
    $("#product_inventory_quantity_minus").removeAttr('required');
    $("#product_inventory_quantity_plus").removeAttr('required');
    $("#product_inventory_purpose").removeAttr('required');
    $("#imgPreview").attr('src', ImageProductPlaceHolder);

}

function editView(id,typeOfProcess) {

     resetInputForm();
    $("#product_inventory_type_process").val(typeOfProcess);
    $("#canvasProductInventory").offcanvas('show');








     $("#row_product_inventory_image").hide();

      $("#row_quantity_purpose").show();
     $("#product_inventory_purpose").attr('required',true);

    if(typeOfProcess=="plus"){
         $("#row_quantity_plus").show();
         $("#row_quantity_minus").hide();
         $("#canvasProductInventoryLable").html("Add Stocks #" + id);
         $("#row_quantity_plus").attr('required',true);


    }else if(typeOfProcess=="minus"){
         $("#row_quantity_minus").show();
         $("#row_quantity_plus").hide();
         $("#product_inventory_quantity_minus").attr('required',true);
         $("#canvasProductInventoryLable").html("Remove Stocks #" + id);
    }

    $("#formProductInventory").hide();
    $(".loadingcls").show();

    $.ajax({
        type: 'GET',
        url: ajaxProductInventoryDetailURL + "?id=" + id,
        success: function(resultData) {
            if (resultData['status'] == 1) {

                $("#product_inventory_id").val(resultData['data']['id']);

                if(typeof resultData['data']['product_brand']['id'] !== "undefined"){
  $("#product_brand_id").empty().trigger('change');
   var newOption = new Option(resultData['data']['product_brand']['name'], resultData['data']['product_brand']['id'], false, false);
 $('#product_brand_id').append(newOption).trigger('change');

}

  $("#product_brand_id").prop('disabled',true);

  if(typeof resultData['data']['product_code']['id'] !== "undefined"){
  $("#product_code_id").empty().trigger('change');
   var newOption = new Option(resultData['data']['product_code']['name'], resultData['data']['product_code']['id'], false, false);
 $('#product_code_id').append(newOption).trigger('change');

}

  $("#product_code_id").prop('disabled',true);

      $("#product_inventory_description").val(resultData['data']['description']);

        $("#product_inventory_description").prop('disabled',true);

        $("#product_inventory_quantity").val(resultData['data']['quantity']);
        $("#product_inventory_quantity").prop('disabled',true);
        $("#product_inventory_weight").val(resultData['data']['weight']);
        $("#product_inventory_weight").prop('disabled',true);
        $("#product_inventory_price").val(resultData['data']['price']);
        $("#product_inventory_price").prop('disabled',true);





                $("#product_inventory_status").select2("val", ""+resultData['data']['status']+"");
                $(".loadingcls").hide();
                $("#formProductInventory").show();


            } else {

                toastr["error"](resultData['msg']);

            }

        }
    });

}

$('#div_product_inventory_image').click(function(){ $('#product_inventory_image').trigger('click'); });


$(document).ready(()=>{
      $('#product_inventory_image').change(function(){
        const file = this.files[0];
        console.log(file);
        if (file){
          let reader = new FileReader();
          reader.onload = function(event){
            console.log(event.target.result);
            $('#imgPreview').attr('src', event.target.result);
          }
          reader.readAsDataURL(file);
        }
      });
    });





var isLoadDiscountTable=0;


    var DiscountTable=$('#datatableDiscount').DataTable({
  "aoColumnDefs": [{ "bSortable": false, "aTargets": [2,3] }],
  "order":[[ 0, 'desc' ]],
  "processing": true,
  "serverSide": true,
  "ajax": {
    "url": ajaxProductInventoryDiscount,
    "type": "POST",
     "data": {
        "_token": $("[name=_token").val(),
        "product_inventory_id":function (){return $("#discount_product_inventory_id").val()},
        "user_type":function(){return $("#discount_user_type").val()  },
        "isLoadDiscountTable":function(){return isLoadDiscountTable}
        }
  },
  "aoColumns" : [
    {"mData" : "name"},
    {"mData" : "email"},
    {"mData" : "discount_percentage"},
    {"mData" : "new_discount_percentage"},

  ]
});
function editDiscount(id) {
    $("#discount_product_inventory_id").val(id);
    if(isLoadDiscountTable==0){
        isLoadDiscountTable=1;
    }
$("#modalDiscount").modal('show');
$("#modalDiscountLabel").html("Edit Discount Product #" + id);

DiscountTable.ajax.reload( null, false );
$("#discountSync").html("Saved");
$("#discount_all_discount").val(0);



}

// function debounce(callback, wait) {
//   let timeout;
//   return (...args) => {
//       clearTimeout(timeout);
//       timeout = setTimeout(function () { callback.apply(this, args); }, wait);
//   };
// }


$(document).delegate('.valid-discount','keyup', function(){
var max = parseInt($(this).attr('max'));
          var min = parseInt($(this).attr('min'));
          if ($(this).val() > max)
          {
              $(this).val(max);
          }
          else if ($(this).val() < min)
          {
              $(this).val(min);
          }
});


$("#saveAllDiscount").click(function() {
$("#discountSync").html('<i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i> <span  >Saving...</span>');


        $.ajax({
            type: 'POST',
            url: ajaxProductInventoryDiscountSaveAll,
            data:{
            "product_inventory_id":function (){return $("#discount_product_inventory_id").val()},
            "_token": $("[name=_token").val(),
            "user_type":function(){ return $("#discount_user_type").val();  },
            "discount_percentage":function(){ return $("#discount_all_discount").val() },
    },

            success: function(resultData) {
                 $("#discount_all_discount").val(0);
                     $("#discountSync").html("Saved");
                     DiscountTable.ajax.reload( null, false );

            }
    });

});

$(document).delegate('.new-discount-cls','change', function(){


$("#discountSync").html('<i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i> <span  >Saving...</span>');
          $.ajax({ type: 'POST',
            url: ajaxProductInventoryDiscountSave,
            data:{
                "discount_percentage":$(this).val(),
                "id":$(this).attr('id'),
                "_token": $("[name=_token").val(),
            },
            success: function(resultData) {

               $("#discountSync").html("Saved");

                     DiscountTable.ajax.reload( null, false );

            }});


});


$('#discount_user_type').on('change', function() {


    $("#discount_all_discount").val(0);

        DiscountTable.ajax.reload( null, false );

});



$(function () {
       $( ".new-discount-cls" ).change(function() {
          var max = parseInt($(this).attr('max'));
          var min = parseInt($(this).attr('min'));
          if ($(this).val() > max)
          {
              $(this).val(max);
          }
          else if ($(this).val() < min)
          {
              $(this).val(min);
          }
        });
    });

</script>
@endsection