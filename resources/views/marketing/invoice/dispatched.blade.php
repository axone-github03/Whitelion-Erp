@extends('layouts.main')
@section('title', $data['title'])
@section('content')
 <link href="{{ asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css') }}" rel="stylesheet" />


                <div class="page-content">
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0 font-size-18">Challan Dispatched

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


  <div class="table-responsive">
                                         <table id="datatable" class="table align-middle table-nowrap dt-responsive mb-0 w-100">
                                            <thead class="table-light">
                                            <tr>
                                                <th>Detail</th>

                                                <th>Channel Partner</th>
                                                <th>Sale Persons</th>
                                                <th>Payment Detail</th>
                                                <th>Challan Status</th>


                                                <th>Action</th>


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

@include('../marketing/invoice/comman/detail')



    @csrf
@endsection('content')
@section('custom-scripts')

<script src="{{ asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js') }}"></script>
<script type="text/javascript">


    var ajaxURL='{{route('marketing.orders.delivery.challan.dispatched.ajax')}}';
    var ajaxInvoiceDetail='{{route('marketing.orders.delivery.challan.detail')}}';

    var csrfToken=$("[name=_token").val();

var invoicePageLength= getCookie('invoicePageLength')!==undefined?getCookie('invoicePageLength'):10;

var table=$('#datatable').DataTable({
  "aoColumnDefs": [{ "bSortable": false, "aTargets": [3,4,5] }],
  "order":[[ 0, 'desc' ]],
  "processing": true,
  "serverSide": true,
  "pageLength": invoicePageLength,
  "ajax": {
    "url": ajaxURL,
    "type": "POST",
     "data": {
        "_token": csrfToken,
        }


  },
  "aoColumns" : [

    {"mData" : "detail"},
    {"mData" : "channel_partner"},
    {"mData" : "sale_persons"},
    {"mData" : "payment_detail"},
    {"mData" : "status"},
    {"mData" : "action"}

  ],
  "drawCallback":function(){

    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
  return new bootstrap.Tooltip(tooltipTriggerEl)
})

  }
});


$('#datatable').on( 'length.dt', function ( e, settings, len ) {

    setCookie('invoicePageLength',len,100);


});


function doMarkAsRecieved(id) {

     Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: !0,
        confirmButtonText: "Yes, mark as recieved !",
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
                    url: ajaxInvoiceMarkAsRecieved + "?id=" + id,
                    success: function(resultData) {

                        if (resultData['status'] == 1) {

                            table.ajax.reload( null, false );
                            t()



                        }




                    }
                });



            })
        },
    }).then(function(t) {

        if (t.value === true) {



            Swal.fire({
                title: "Mark as recieved!",
                text: "Your record has been updated.",
                icon: "success"
            });


        }

    });

}


</script>
@include('../marketing/invoice/comman/script')

@endsection