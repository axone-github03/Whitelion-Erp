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
                                    <h4 class="mb-sm-0 font-size-18">Log

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
                                                <th>Process Name</th>
                                                <th>Description</th>
                                                <th>Action By</th>
                                                <th>Date & Time</th>


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
<script type="text/javascript">


    var ajaxURL='{{route('debug.log.ajax')}}';
    var csrfToken=$("[name=_token").val();
var logPageLength= getCookie('logPageLength')!==undefined?getCookie('logPageLength'):10;

var table=$('#datatable').DataTable({
  "aoColumnDefs": [{ "bSortable": false, "aTargets": [] }],
  "order":[[ 0, 'desc' ]],
  "processing": true,
  "serverSide": true,
   "pageLength": logPageLength,
  "ajax": {
    "url": ajaxURL,
    "type": "POST",
     "data": {
        "_token": csrfToken,
        }


  },
  "aoColumns" : [
    {"mData" : "id"},
    {"mData" : "name"},
    {"mData" : "description"},
    {"mData" : "process_by"},
    {"mData" : "created_at"}



  ]
});

$('#datatable').on( 'length.dt', function ( e, settings, len ) {

    setCookie('logPageLength',len,100);


});


</script>
@endsection