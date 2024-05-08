@extends('layouts.main')
@section('title', $data['title'])
@section('content')
<style type="text/css">
    .section_channel_partner{

             border:1px solid #ced4da;


    }
    .section_user_detail{
        border:1px solid #ced4da;

    }
    .nav-pills .nav-link{
        border: 1px solid gainsboro;
    }
</style>



                <div class="page-content">
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0 font-size-18">Channel Partners</h4>

 <div class="page-title-right">


@include('../channel_partners/comman/btn_view')
                                    </div>


                                </div>


                            </div>
                        </div>
                        <!-- end page title -->
                        <!-- start row -->


                        <div class="row">

                                <div class="card">
                                    <div class="card-body">

@include('../channel_partners/comman/tab_view')
                                        <br>
                                        <div class="table-responsive">
<table id="datatable" class="table align-middle table-nowrap table-hover table-striped dt-responsive nowrap w-100">
                                            <thead>
                                            <tr>
                                                <th>Id</th>
                                                <th>Name / Type</th>
                                                <th>Email / Phone /GST</th>
                                                <th>Invoice From</th>
                                                <th>Sale Persons</th>
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
@include('../channel_partners/comman/modal_view')
<script type="text/javascript">

    var selectedUserType={{$data['type']}};
    var ajaxURL='{{route('channel.partners.ajax.view')}}';
    var csrfToken=$("[name=_token").val();

     var channelPartnerPageLength= getCookie('channelPartnerPageLength')!==undefined?getCookie('channelPartnerPageLength'):10;



var table=$('#datatable').DataTable({
  "aoColumnDefs": [{ "bSortable": false, "aTargets": [5,6] }],
  "order":[[ 0, 'desc' ]],
  "processing": true,
  "serverSide": true,
  "pageLength": channelPartnerPageLength,
  "ajax": {
    "url": ajaxURL+'?type='+selectedUserType,
    "type": "POST",
     "data": {
        "_token": csrfToken,
        }
  },
  "aoColumns" : [
    {"mData" : "id"},
    {"mData" : "name"},
    {"mData" : "email"},
    {"mData" : "invoice_from"},
    {"mData" : "sale_persons"},
    {"mData" : "status"},
    {"mData" : "action"},

  ]
});


$('#datatable').on( 'length.dt', function ( e, settings, len ) {

    setCookie('channelPartnerPageLength',len,100);


});

</script>
@include('../users/comman/script')
@endsection
