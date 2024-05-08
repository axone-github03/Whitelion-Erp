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
                                    <h4 class="mb-sm-0 font-size-18">Predication Reports</h4>


                               <div class="page-title-right">

                                    <a href="{{route('inquiry.reports')}}" class="btn btn-primary" type="button" >  Reports </a>
                                      <a href="{{route('inquiry.reports.reverse')}}" class="btn btn-primary" type="button" > Reverse Reports </a>

                                    </div>



                                </div>
                            </div>
                        </div>
                        <!-- end page title -->

                        <div class="row">

                            <div class="col-12">

                                <div class="row">


                                    <div class="col-lg-4">

  <div class="row">
                                                                <div class="col-md-6">
<div class="mb-3">
  <label class="form-label">Year <code class="highlighter-rouge"></code></label>
    <select class="form-control " id="inquiry_year" name="inquiry_year" required >

        @foreach($data['year_array'] as $year)

                                                            <option @if($data['current_year']==$year) selected @endif value="{{$year}}">{{$year}}</option>
        @endforeach

                                                        </select>
                                                    </div>
                                                </div>
                                                                                           <div class="col-md-6">
<div class="mb-3">
     <label class="form-label">Month <code class="highlighter-rouge"></code></label>

                                                         <select class="form-control " id="inquiry_month" name="inquiry_month" required >
                                                    @foreach($data['month_array'] as $month)

                                                            <option  @if($data['current_month']==$month) selected @endif  value="{{$month}}">{{$month}}</option>
        @endforeach

                                                        </select>
</div>
</div>
</div>





                                                 <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                        <label class="form-label">sale person <code class="highlighter-rouge"></code></label>
                                                        <select class="form-control select2-ajax" id="inquiry_report_sales_user_id" name="inquiry_report_sales_user_id" required >
                                                            <option  value="0">All</option>

                                                        </select>

                                            </div>

                                                 <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                        <label class="form-label">Report Type<code class="highlighter-rouge"></code></label>
                                                        <select class="form-control select2-ajax" id="inquiry_report_type" name="inquiry_report_sales_user_id" required >
                                                            @foreach($data['report_type'] as $rk=>$vk)
                                                            <option  value="{{$rk}}">{{$vk}}</option>
                                                            @endforeach

                                                        </select>

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
                                                <th>Type</th>
                                                <th>No Of Inquiry</th>

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

                             <div class="col-12">
                                <div class="card">
                                            <div class="card-body">
                                                 <button type="button" id="inquiry_quotation_amount_lable" class="btn-sm btn btn-outline-dark waves-effect waves-light float-end"   data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Quotation Amount: <span id="totalQuotationAmount" >0</span></button>


                             <table id="datatableInquiry" class="table table-striped nowrap w-100">
                                            <thead>
                                            <tr>
                                                <th>#ID </th>
                                                <th>Name</th>
                                                <th>Phone Number</th>
                                                <th>Address</th>
                                                <th>Status</th>
                                                <th>Source Type</th>
                                                <th>Source </th>
                                                <th>Quotation Amt </th>
                                                <th>Detail </th>

                                            </tr>
                                            </thead>


                                            <tbody>

                                            </tbody>
                         </table>
                     </div>
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


         var ajaxSourceWiseSalePerson='{{route('inquiry.reports.predication.sale.person')}}';
         var ajaxInquiryDownload='{{route('inquiry.reports.predication.download')}}';

         var ajaxSearchSalePerson='{{route('inquiry.predication.search.sale.person')}}';
           var ajaxInquiryList='{{route('inquiry.reports.predication.list')}}';

       $("#inquiry_year").select2({});
       $("#inquiry_month").select2({});

        $("#inquiry_source_type").select2({});
        $("#inquiry_report_type").select2({});


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
            "inquiry_month": function() {
                return $("#inquiry_month").val();
            },
            "inquiry_year": function() {
                return $("#inquiry_year").val();
            },"sales_user_id": function() {
                return $("#inquiry_report_sales_user_id").val();
            }
        }


    },
    "aoColumns": [

      {"mData" : "type"},
    {"mData" : "type_count"},


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

$("#inquiry_month,#inquiry_year,#inquiry_report_sales_user_id,#inquiry_report_type").change(function() {


        $("#divSourceWiseSalePerson").show();
        datatableSourceWiseSalePerson.ajax.reload();
        datatableInquiry.ajax.reload();





});


$("#inquiryDownload").click(function() {


    var start_date = $("#inquiry_source_wise_start_date").val();
    var end_date = $("#inquiry_source_wise_end_date").val();

    var source_type=$("#inquiry_source_type").val();

    var downloadURL = ajaxInquiryDownload + "?start_date=" + start_date + "&end_date=" + end_date+"&source_type="+source_type;
    window.open(downloadURL, '_blank');



});

$("#inquiry_report_sales_user_id").select2({
    ajax: {
        url: ajaxSearchSalePerson,
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
    placeholder: 'Search for a user',

});



var datatableInquiry = $('#datatableInquiry').DataTable({
    "aoColumnDefs": [{
        "bSortable": false,
        "aTargets": []
    }],
    "order": [
        [0, 'desc']
    ],
    "scrollX": true,
    "processing": true,
    "serverSide": true,
    "pageLength": 10,
    "ajax": {
        "url": ajaxInquiryList,
        "type": "POST",
        "data": {
            "_token": csrfToken,
             "inquiry_month": function() {
                return $("#inquiry_month").val();
            },
            "inquiry_year": function() {
                return $("#inquiry_year").val();
            },"sales_user_id": function() {
                return $("#inquiry_report_sales_user_id").val();
            },"report_type": function() {
                return $("#inquiry_report_type").val();
            }




        }


    },
    "aoColumns": [

        {
            "mData": "id"
        }, {
            "mData": "name"
        }, {
            "mData": "phone_number"
        }, {
            "mData": "address"
        }, {
            "mData": "status"
        }, {
            "mData": "source_type"
        }, {
            "mData": "source"
        }, {
            "mData": "quotation_amount"
        }, {
            "mData": "detail"
        }

    ],
    "drawCallback": function() {

        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })

    }
});


datatableInquiry.on('xhr', function() {

    var responseData = datatableInquiry.ajax.json();
    $("#totalQuotationAmount").html(responseData['quotationAmount']);





});
     </script>







@endsection