@extends('layouts.main')
@section('title', $data['title'])
@section('content')



                <div class="page-content">
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0 font-size-18">Users</h4>

<div class="page-title-right">
@include('../users/comman/btn')
</div>


                                </div>


                            </div>
                        </div>
                        <!-- end page title -->
                        <!-- start row -->


                        <div class="row">

                                <div class="card">
                                    <div class="card-body">

@include('../users/comman/tab')
                                        <br>
                                        <div class="table-responsive">
<table id="datatable" class="table align-middle table-nowrap table-hover table-striped dt-responsive nowrap w-100">
                                            <thead>
                                            <tr>
                                                <th>Id</th>
                                                <th>Name / Type</th>
                                                <th>Email / Phone</th>
                                                <th>Company</th>
                                                <th>Status</th>
                                                <th>Action</th>



                                            </tr>
                                            </thead>


                                            <tbody>

                                            </tbody>
                                        </table>

                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>



                        <!-- end row -->
                    </div>
                    <!-- container-fluid -->
                </div>
                <!-- End Page-content -->







@endsection('content')
@section('custom-scripts')


<script src="{{ asset('assets/libs/parsleyjs/parsley.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/form-validation.init.js') }}"></script>
<script src="{{ asset('assets/js/pages/jquery.form.js') }}"></script>
@include('../users/comman/modal')



<script type="text/javascript">
    var ajaxURL='{{route('users.company.admin.ajax')}}';
    var csrfToken=$("[name=_token").val();

     var usersPageLength= getCookie('usersPageLength')!==undefined?getCookie('usersPageLength'):10;

var table=$('#datatable').DataTable({
  "aoColumnDefs": [{ "bSortable": false, "aTargets": [] }],
  "order":[[ 0, 'desc' ]],
  "processing": true,
  "serverSide": true,
  "pageLength": usersPageLength,
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
    {"mData" : "email"},
    {"mData" : "company"},
    {"mData" : "status"},
    {"mData" : "action"},

  ]
});


$('#datatable').on( 'length.dt', function ( e, settings, len ) {

    setCookie('usersPageLength',len,100);


});
</script>

@include('../users/comman/script')
@endsection
