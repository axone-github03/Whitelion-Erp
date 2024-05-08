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

    var ajaxProductInventoryURL='{{route('product.inventory.ajax.view')}}';
    var ajaxProductInventoryDetailURL='{{route('product.inventory.detail')}}';

    var ImageProductPlaceHolder='{{ asset('s/product/placeholder.png') }}';



var csrfToken = $("[name=_token").val();


var table = $('#datatable').DataTable({
    "aoColumnDefs": [{
        "bSortable": false,
        "aTargets": []
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
        }



    ]
});

function reloadTable() {
    table.ajax.reload( null, false );
}





</script>
@endsection