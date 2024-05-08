@extends('layouts.main')
@section('title', $data['title'])
@section('content')
<link href="{{ asset('assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css')}}" rel="stylesheet" type="text/css">

<link href="{{ asset('assets/libs/%40fullcalendar/core/main.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/libs/%40fullcalendar/daygrid/main.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/libs/%40fullcalendar/bootstrap/main.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/libs/%40fullcalendar/timegrid/main.min.css')}}" rel="stylesheet" type="text/css" />
<style type="text/css">
    .fc-time-grid-container {
        display: none;
    }

    .fc-divider {
        display: none;
    }

    .fc-event {
        cursor: pointer;
    }

    #datatableSourceWiseSalePerson_info {
        display: none;
    }
</style>


<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Reports</h4>

                    <div class="page-title-right">

                        <a href="{{route('inquiry.reports.reverse')}}" class="btn btn-primary" type="button"> Reverse Reports </a>

                        <a href="{{route('inquiry.reports.predication')}}" class="btn btn-primary" type="button"> Predication Reports </a>

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


                                <input type="text" class="form-control" name="inquiry_source_wise_start_date" id="inquiry_source_wise_start_date" value="@php echo date('01-m-Y'); @endphp" placeholder="Start Date" />
                                <input type="text" class="form-control" name="inquiry_source_wise_end_date" id="inquiry_source_wise_end_date" placeholder="End Date" value="@php echo date('t-m-Y'); @endphp" />
                            </div>
                        </div>


                        <div class="mb-3 ajax-select mt-3 mt-lg-0">
                            <label class="form-label">sale person <code class="highlighter-rouge"></code></label>
                            <select class="form-control select2-ajax" id="inquiry_report_sales_user_id" name="inquiry_report_sales_user_id" required>
                                <option value="0">All</option>

                            </select>

                        </div>

                        <div class="mb-3">
                            <label for="inquiry_source_type" class="form-label">Source Type <code class="highlighter-rouge"></code></label>
                            @php



                            @endphp
                            <select class="form-control select2-ajax" id="inquiry_source_type" name="inquiry_source_type">
                                <option value="0-0">All</option>
                                @if(count($data['source_types'])>0)
                                @foreach($data['source_types'] as $key=>$value)
                                <option value="{{$value['type']}}-{{$value['id']}}">{{$value['lable']}}</option>
                                @endforeach
                                @endif
                            </select>
                            <div class="invalid-feedback">
                                Please select source type.
                            </div>
                        </div>



                        <div class="mb-3">
                            <label for="inquiry_source_user" class="form-label">Source <code class="highlighter-rouge"></code></label>
                            <select class="form-control select2-ajax" id="inquiry_source_user" name="inquiry_source_user">
                                <option value="0">All</option>
                            </select>
                            <div class="invalid-feedback">
                                Please select source.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="inquiry_status" class="form-label">Status <code class="highlighter-rouge"></code></label>
                            <select class="form-control select2-ajax" id="inquiry_status" name="inquiry_status">
                                <option value="0">Inquiry Generation</option>
                                <option value="1,2,3,4,5,6,7,8">Running</option>
                                <option value="9,10,11">Material Sent</option>
                                <option value="102">Rejected</option>
                                <option value="101">Non Potential</option>
                                <option value="!10"> Non Claim</option>
                            </select>
                            <div class="invalid-feedback">
                                Please select status.
                            </div>
                        </div>

                        <div class="mb-3" id="div_client_product">
                            <label for="inquiry_client_product" class="form-label">{{$data['client_product']->question}} <code class="highlighter-rouge"></code></label>
                            <select class="form-control select2-ajax" id="inquiry_client_product" name="inquiry_client_product">
                                <option value="0">All</option>
                                @foreach($data['client_product']['options'] as $keyO=>$valueP)
                                <option value="{{$valueP->id}}">{{$valueP->option}}</option>
                                @endforeach


                            </select>
                            <div class="invalid-feedback">
                                Please select client product.
                            </div>
                        </div>






                        <div class="mb-3">
                            @if(isSalePerson() != 1)
                                <button id="inquiryDownload" type="button" class="btn btn-dark waves-effect waves-light">
                                    <i class="mdi mdi-download font-size-16 align-middle me-2"></i> Download
                                </button>
                            @endif
                        </div>









                    </div>

                    <div class="col-lg-8">

                        <div class="row source-wise-table" id="divSourceWiseSalePerson">

                            <div class="col-lg-12">



                                <div class="card">
                                    <div class="card-body">

                                        <table id="datatableSourceWiseSalePerson" class="table table-striped dt-responsive  nowrap w-100 ">
                                            <thead>
                                                <tr>
                                                    <th>Sales person</th>
                                                    <th>Inquiry Count</th>
                                                </tr>
                                            </thead>


                                            <tbody>

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row source-wise-table" id="divSourceWiseSourceType">

                            <div class="col-lg-12">



                                <div class="card">
                                    <div class="card-body">


                                        <table id="datatableSourceWiseSourceType" class="table table-striped dt-responsive  nowrap w-100">
                                            <thead>
                                                <tr>
                                                    <th>Source Types</th>
                                                    <th>Inquiry Count</th>
                                                </tr>
                                            </thead>


                                            <tbody>

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row source-wise-table" id="divSourceWiseSource">

                            <div class="col-lg-12">



                                <div class="card">
                                    <div class="card-body">

                                        <table id="datatableSourceWiseSource" class="table table-striped dt-responsive  nowrap w-100">
                                            <thead>
                                                <tr>
                                                    <th>Source </th>
                                                    <th>Inquiry Count</th>
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











                </div> <!-- end col -->



            </div>
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <button type="button" id="inquiry_quotation_amount_lable" class="btn-sm btn btn-outline-dark waves-effect waves-light float-end" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Quotation Amount: <span id="totalQuotationAmount">0</span></button>


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

</div> <!-- container-fluid -->
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
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
    var csrfToken = $("[name=_token").val();
    var ajaxSourceWiseSalePerson = "{{route('inquiry.reports.sale.person')}}";
    var ajaxSourceWiseSourceTypes = "{{route('inquiry.reports.source.type')}}";
    var ajaxSourceWiseSource = "{{route('inquiry.reports.source')}}";
    var ajaxSearchSource = "{{route('inquiry.reports.search.source')}}";
    var ajaxSearchSalePerson = "{{route('inquiry.reports.search.sale.person')}}";
    var ajaxInquiryDownload = "{{route('inquiry.reports.download')}}";
    var ajaxInquiryList = "{{route('inquiry.reports.list')}}";
</script>

<script src="{{ asset('assets/js/c/inquiry-reports-source-wise.js')}}?v=10"></script>


<!-- Calendar init -->




@endsection