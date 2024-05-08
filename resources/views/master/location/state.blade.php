@extends('layouts.main')
@section('title', $data['title'])
@section('content')



                <div class="page-content">
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0 font-size-18">State List

                                    </h4>


                                <div class="page-title-right">
                                    <select class="form-select" id="country_id" >

                                        @foreach($data['country_list'] as $key=>$value)
                                        <option value="{{$value->id}}" > {{$value->name   }} ({{$value->code}}) </option>
                                        @endforeach
                                    </select>
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
                                                <th>State Name</th>
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


    var ajaxURL='{{route('statelist.ajax')}}';
    var csrfToken=$("[name=_token").val();

     var locationPageLength= getCookie('locationPageLength')!==undefined?getCookie('locationPageLength'):10;


var table=$('#datatable').DataTable({
  "aoColumnDefs": [{ "bSortable": false, "aTargets": [] }],
  "order":[[ 0, 'desc' ]],
  "processing": true,
  "serverSide": true,
   "pageLength": locationPageLength,
  "ajax": {
    "url": ajaxURL,
    "type": "POST",
     "data": {
        "_token": csrfToken,
          "country_id":  function() { return $("#country_id").val() }
        }


  },
  "aoColumns" : [
    {"mData" : "id"},
    {"mData" : "name"},
    {"mData" : "created_at"},



  ]
});



$('#datatable').on( 'length.dt', function ( e, settings, len ) {

    setCookie('locationPageLength',len,100);


});


function reloadTable()
{
  table.ajax.reload( null, false );
}
function applyFilter() {
    reloadTable();
}
$('#country_id').on('change', function() {
  applyFilter();
});

</script>
@endsection