@extends('layouts.main')
@section('title', $data['title'])
@section('content')
 <link href="{{ asset('assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css')}}" rel="stylesheet" type="text/css">

      <link href="{{ asset('assets/libs/%40fullcalendar/core/main.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/libs/%40fullcalendar/daygrid/main.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/libs/%40fullcalendar/bootstrap/main.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/libs/%40fullcalendar/timegrid/main.min.css')}}" rel="stylesheet" type="text/css" />
        <style type="text/css">
    .fc-time-grid-container{
    display:none;
}
.fc-divider{
display: none;
}
.fc-event{
    cursor: pointer;
}

#datatableSourceWiseSalePerson_info{
    display: none;
}
</style>


                <div class="page-content">
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0 font-size-18">Reverse Reports</h4>


                               <div class="page-title-right">

                                    <a href="{{route('inquiry.reports')}}" class="btn btn-primary" type="button" >  Reports </a>
                                        <a href="{{route('inquiry.reports.predication')}}" class="btn btn-primary" type="button" > Predication Reports </a>
                                    </div>



                                </div>
                            </div>
                        </div>
                        <!-- end page title -->

                        <div class="row">

                            <div class="col-12">

                                <div class="row">


                                    <div class="col-lg-4">





<div class="mb-3">

                                                  <label for="inquiry_source_type" class="form-label">Date Range<code class="highlighter-rouge"></code></label>

                                                 <div class="input-daterange input-group" id="inquiry_datepicker" data-date-format="dd-mm-yyyy" data-date-autoclose="true" data-provide="datepicker" data-date-container='#inquiry_datepicker'>


                                                    <input type="text" class="form-control" name="inquiry_source_wise_start_date" id="inquiry_source_wise_start_date" value="@php echo date('01-m-Y'); @endphp" placeholder="Start Date"  />
                                                    <input type="text" class="form-control" name="inquiry_source_wise_end_date" id="inquiry_source_wise_end_date" placeholder="End Date" value="@php echo date('t-m-Y'); @endphp" />
                                                </div>
                                                </div>



                                                <div class="mb-3">
                                                                        <label for="inquiry_source_type" class="form-label">Source Type <code class="highlighter-rouge"></code></label>
                                                                        @php



                                                                        @endphp
                                                                        <select class="form-control select2-ajax" id="inquiry_source_type" name="inquiry_source_type"  >
                                                                            <option  value="0-0">All</option>
                                                                        @if(count($data['source_types'])>0)
                                                                            @foreach($data['source_types'] as $key=>$value)
                                                                            @if($value['type']=='user')
                                                                                <option  value="{{$value['type']}}-{{$value['id']}}">{{$value['lable']}}</option>
                                                                            @endif
                                                                            @endforeach
                                                                        @endif
                                                                        </select>
                                                                            <div class="invalid-feedback">
                                                    Please select source type.
                                                               </div>
                                                                    </div>















<div class="mb-3">
    <button id="inquiryDownload" type="button" class="btn btn-dark waves-effect waves-light">
                                                <i class="mdi mdi-download font-size-16 align-middle me-2"></i> Download
                                            </button>
</div>









                                    </div>

                                     <div class="col-lg-8">

                                           <div class="row source-wise-table" id="divSourceWiseSalePerson"  >

                                     <div class="col-lg-12">



                                        <div class="card">
                                            <div class="card-body">

                                        <table id="datatableSourceWiseSalePerson" class="table table-striped dt-responsive  nowrap w-100 ">
                                            <thead>
                                            <tr>
                                                <th>Id</th>
                                                <th>Source</th>
                                                <th>Type</th>

                                            </tr>
                                            </thead>


                                            <tbody>

                                            </tbody>
                                        </table>
                                        </div>
                                    </div>
                                </div>
                            </div>












                                    </div> <!-- end col -->



                                </div>








                            </div>
                        </div>

                    </div> <!-- container-fluid -->

                </div>









  @csrf

@endsection('content')

@section('custom-scripts')

        <!-- JAVASCRIPT -->

        <!-- plugin js -->
        <script src="{{ asset('assets/libs/moment/min/moment.min.js') }}"></script>
        <script src="{{ asset('assets/libs/jquery-ui-dist/jquery-ui.min.js') }}"></script>
        <script src="{{ asset('assets/libs/%40fullcalendar/core/main.min.js') }}"></script>
        <script src="{{ asset('assets/libs/%40fullcalendar/bootstrap/main.min.js') }}"></script>
        <script src="{{ asset('assets/libs/%40fullcalendar/daygrid/main.min.js') }}"></script>
        <script src="{{ asset('assets/libs/%40fullcalendar/timegrid/main.min.js') }}"></script>
        <script src="{{ asset('assets/libs/%40fullcalendar/interaction/main.min.js') }}"></script>

    <script src="{{ asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>


        <script type="text/javascript">
          var csrfToken=$("[name=_token").val();
        </script>

     <script src="{{ asset('assets/js/c/inquiry-reports-reverse.js')}}?v=6"></script>
     <script type="text/javascript">


              var ajaxSourceWiseSalePerson='{{route('inquiry.reports.reverse.sale.person')}}';
              var ajaxInquiryDownload='{{route('inquiry.reports.reverse.download')}}';

        $("#inquiry_source_type").select2({});

        var datatableSourceWiseSalePerson = $('#datatableSourceWiseSalePerson').DataTable({
    "aoColumnDefs": [{
        "bSortable": false,
        "aTargets": []
    }],
    "processing": true,
    "serverSide": true,
    "pageLength": 10,
    "ajax": {
        "url": ajaxSourceWiseSalePerson,
        "type": "POST",
        "data": {
            "_token": csrfToken,
            "start_date": function() {
                return $("#inquiry_source_wise_start_date").val();
            },
            "end_date": function() {
                return $("#inquiry_source_wise_end_date").val();
            },"source_type": function() {
                return $("#inquiry_source_type").val();
            }
        }


    },
    "aoColumns": [

      {"mData" : "id"},
    {"mData" : "source"},
    {"mData" : "type"},


    ],
    "drawCallback": function() {

        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })

    }
});

datatableSourceWiseSalePerson.on('xhr', function() {

    var responseData = datatableSourceWiseSalePerson.ajax.json();


});

$("#inquiry_source_wise_start_date,#inquiry_source_wise_end_date,#inquiry_source_type").change(function() {


        $("#divSourceWiseSalePerson").show();
        datatableSourceWiseSalePerson.ajax.reload();





});


$("#inquiryDownload").click(function() {


    var start_date = $("#inquiry_source_wise_start_date").val();
    var end_date = $("#inquiry_source_wise_end_date").val();

    var source_type=$("#inquiry_source_type").val();

    var downloadURL = ajaxInquiryDownload + "?start_date=" + start_date + "&end_date=" + end_date+"&source_type="+source_type;
    window.open(downloadURL, '_blank');



});


     </script>







@endsection