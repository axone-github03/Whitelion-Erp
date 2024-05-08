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
                                    <h4 class="mb-sm-0 font-size-18">Product Log

                                    </h4>





                                </div>


                            </div>
                        </div>
                        <!-- end page title -->

                          <!-- start row -->






                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">

                                          <div class="col-lg-6">
                                                    <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                        <label class="form-label">Product </label>
                                                        <select class="form-control select2-ajax" id="product_inventory_id" >
                                                            <option value="0">ALL</option>
                                                        </select>

                                                    </div>

                                                </div>


<div class="table-responsive">
                                        <table id="datatable" class="table align-middle table-nowrap mb-0">
                                            <thead class="table-light">
                                            <tr>
                                                <th>Id</th>


                                                <th>Product</th>
                                                <th>Description</th>
                                                <th>+/- QT</th>
                                                <th>Final QT</th>
                                                <th>Action By</th>
                                                <th>Date & Time</th>



                                            </tr>
                                            </thead>


                                            <tbody>

                                            </tbody>
                                        </table>
                                        </div>

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
<script type="text/javascript">


    var ajaxURL='{{route('marketing.product.log.ajax')}}';
    var ajaxURLSearchProduct='{{route('marketing.product.log.search.product')}}';
    var csrfToken=$("[name=_token").val();

     var inventoryLogPageLength= getCookie('inventoryLogPageLength')!==undefined?getCookie('inventoryLogPageLength'):10;


var table=$('#datatable').DataTable({
  "aoColumnDefs": [{ "bSortable": false, "aTargets": [] }],
  "order":[[ 0, 'desc' ]],
  "processing": true,
  "serverSide": true,
  "pageLength": inventoryLogPageLength,
  "ajax": {
    "url": ajaxURL,
    "type": "POST",
     "data": {
        "product_inventory_id":  function() { return $("#product_inventory_id").val(); },
        "_token": csrfToken,
        }


  },
  "aoColumns" : [
    {"mData" : "id"},
    {"mData" : "product"},
    {"mData" : "description"},
    {"mData" : "request_quantity"},
    {"mData" : "quantity"},
    {"mData" : "process_by"},
    {"mData" : "created_at"},




  ]
});


$('#datatable').on( 'length.dt', function ( e, settings, len ) {

    setCookie('inventoryLogPageLength',len,100);


});


$("#product_inventory_id").select2({
  ajax: {
    url: ajaxURLSearchProduct,
    dataType: 'json',
    delay: 0,
    data: function (params) {
      return {

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
  placeholder: 'Search for a product',

});


function reloadTable()
{
  table.ajax.reload( null, false );
}
$('#product_inventory_id').on('change', function() {
  reloadTable();
});
</script>
@endsection