@extends('layouts.main')
@section('title', $data['title'])
@section('content')
    @php
        $inquiryStatus = getInquiryStatus();
    @endphp
    <style type="text/css">
        td p {
            max-width: 100%;
            white-space: break-spaces;
            word-break: break-all;
        }

        .inquiry-status-lable-color-202 {
            background: #0d0d0d;
            color: #ffffff;
        }

        .inquiry-status-lable-color-201 {
            background: #0d0d0d;
            color: #ffffff;
        }

        .inquiry-status-lable-color-1 {
            background: #0d0d0d;
            color: #ffffff;
        }

        .inquiry-status-lable-color-2 {
            background: #f19e06;
            color: #ffffff;
        }

        .inquiry-status-lable-color-3 {
            background: #f5b3be;
            color: #ffffff;
        }

        .inquiry-status-lable-color-4 {
            background: #b12d2d;
            color: #ffffff;
        }

        .inquiry-status-lable-color-5 {
            background: #750375;
            color: #ffffff;
        }

        .inquiry-status-lable-color-6 {
            background: #e70e0e;
            color: #ffffff;
        }

        .inquiry-status-lable-color-7 {
            background: #418107;
            color: #ffffff;
        }

        .inquiry-status-lable-color-8 {
            background: #e70e0e;
            color: #ffffff;
        }

        .inquiry-status-lable-color-13 {
            background: #e70e0e;
            color: #ffffff;
        }

        .inquiry-status-lable-color-9 {
            background: #e70e0e;
            color: #ffffff;
        }

        .inquiry-status-lable-color-11 {
            background: #e70e0e;
            color: #ffffff;
        }

        .inquiry-status-lable-color-14 {
            background: #e70e0e;
            color: #ffffff;
        }

        .inquiry-status-lable-color-12 {
            background: #e70e0e;
            color: #ffffff;
        }

        .inquiry-status-lable-color-10 {
            background: #e70e0e;
            color: #ffffff;
        }

        .inquiry-status-lable-color-101 {
            background: #88cbe6;
            color: #ffffff;
        }

        .inquiry-status-lable-color-102 {
            background: #88cbe6;
            color: #ffffff;
        }

        .inquiry-status-lable-color-0 {
            background: #0d0d0d;
            color: #ffffff;
        }

        .status-tab-active {
            text-decoration: underline !important;
            font-size: 15px;
            border: 2px solid black !important;
        }

        .section-lable {

            border: 1px solid gainsboro;
            padding: 2px 6px;
            border-radius: 4px;
            background: aliceblue;
        }

        .inquiry-update-box {
            display: block;
            padding: 14px 16px;
            color: #74788d;
            -webkit-transition: all 0.4s;
            transition: all 0.4s;
            border-top: 1px solid #eff2f7;
            border-radius: 4px;
        }

        .inquiry-update-reply-box {
            display: block;
            padding: 14px 16px;
            color: #74788d;
            -webkit-transition: all 0.4s;
            transition: all 0.4s;
            border-top: 1px solid #eff2f7;
            border-radius: 4px;
        }

        .inquiry-avatar-xs {
            min-width: 2rem !important;
        }

        .hightlight-update {
            color: #50a5f1;
        }

        .hightlight-update>.inquiry-update-badge {
            background-color: #50a5f1;
        }

        .reply-box {
            display: none;
        }

        .seen-ul {
            padding: 0;
        }

        ul.list-unstyled.chat-list.seen-ul a {
            padding: 4px 10px;
        }

        .seen-ul li {
            background: white !important;
            padding: 0;
            font-size: 10px;
        }

        .tooltip-inner .seen-avatar {
            background-color: rgba(85, 110, 230, 0.25) !important;
        }

        .inquiry-comments-icon {
            font-size: 20px;
        }
    </style>
    <link href="{{ asset('assets/libs/summernote/summernote.min.css') }}" rel="stylesheet">



    <div class="page-content">
        <div class="container-fluid">

            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">Exhibition

                        </h4>

                        <div class="page-title-right">


                            <div class="userscomman">

                                @if ($data['isTaleSalesUser'] == 1 || $data['isAdminOrCompanyAdmin'] == 1)
                                    <a href="{{ route('inquiry.exhibition') }}"
                                        class=" btn btn-sm inquiry-status-btn inquiry-status-lable-color-0 status-tab-active">Exhibition</a>
                                @endif

                                @foreach ($inquiryStatus as $key => $value)
                                    @if ($data['isArchitect'] == 1 && $value['can_display_on_inquiry_architect'] == 1)
                                        <a href="{{ route('inquiry') }}?status={{ $value['id'] }}"
                                            class="btn btn-sm   inquiry-status-btn inquiry-status-lable-color-{{ $value['id'] }}">{{ $value['name'] }}</a>
                                    @elseif($data['isElectrician'] == 1 && $value['can_display_on_inquiry_electrician'] == 1)
                                        <a href="{{ route('inquiry') }}?status={{ $value['id'] }}"
                                            class="btn btn-sm   inquiry-status-btn inquiry-status-lable-color-{{ $value['id'] }}">{{ $value['name'] }}</a>
                                    @elseif($data['isSalePerson'] == 1 && $value['can_display_on_inquiry_sales_person'] == 1)
                                        <a href="{{ route('inquiry') }}?status={{ $value['id'] }}"
                                            class="btn btn-sm  inquiry-status-btn inquiry-status-lable-color-{{ $value['id'] }}">{{ $value['name'] }}</a>
                                    @elseif($data['isAdminOrCompanyAdmin'] == 1 && $value['can_display_on_inquiry_user'] == 1)
                                        <a href="{{ route('inquiry') }}?status={{ $value['id'] }}"
                                            class="btn btn-sm  inquiry-status-btn inquiry-status-lable-color-{{ $value['id'] }}">{{ $value['name'] }}</a>
                                    @elseif($data['isChannelPartner'] != 0 && $value['can_display_on_inquiry_channel_partner'] == 1)
                                        <a href="{{ route('inquiry') }}?status={{ $value['id'] }}"
                                            class="btn btn-sm  inquiry-status-btn inquiry-status-lable-color-{{ $value['id'] }}">{{ $value['name'] }}</a>
                                    @elseif($data['isThirdPartyUser'] == 1 && $value['can_display_on_inquiry_third_party'] == 1)
                                        <a href="{{ route('inquiry') }}?status={{ $value['id'] }}"
                                            class="btn btn-sm  inquiry-status-btn inquiry-status-lable-color-{{ $value['id'] }}">{{ $value['name'] }}</a>
                                    @elseif($data['isTaleSalesUser'] == 1 && $value['can_display_on_inquiry_tele_sales'] == 1)
                                        <a href="{{ route('inquiry') }}?status={{ $value['id'] }}"
                                            class="btn btn-sm  inquiry-status-btn inquiry-status-lable-color-{{ $value['id'] }}">{{ $value['name'] }}</a>
                                    @endif
                                @endforeach





                            </div>




                        </div>


                    </div>
                </div>
            </div>

            <!-- end page title -->
            <div class="modal fade" id="modalDetail" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1"
                role="dialog" aria-labelledby="modalDetailLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalDetailLabel">Detail </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <input type="hidden" name="detail_inquiry_id" id="detail_inquiry_id">





                        <div class="modal-body" id="modelBodyDetail">

                            <ul class="nav nav-tabs nav-tabs-custom nav-justified" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#inquiry_update"
                                        onclick="loadDetail('inquiry_update')" role="tab">
                                        <span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
                                        <span class="d-none d-sm-block">Update</span>
                                    </a>
                                </li>

                            </ul>

                            <div class="tab-content p-3 text-muted">
                                <div class="tab-pane active" id="inquiry_update" role="tabpanel">

                                </div>
                                <div class="tab-pane" id="inquiry_files" role="tabpanel">

                                </div>
                                <div class="tab-pane" id="inquiry_log" role="tabpanel">

                                </div>
                                <div class="tab-pane" id="inquiry_answer" role="tabpanel">

                                </div>
                            </div>



                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>

                        </div>


                    </div>
                </div>
            </div>


            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body" id="exhibition_list">

                            <div class="row">
                                <div class="col-md-2">
                                    <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                        <label for="q_exhibition_filter" class="form-label">Exhibition </label>
                                        <select class="form-control select2-ajax" id="q_exhibition_filter"
                                            name="q_exhibition_filter">
                                        </select>
                                        <div class="invalid-feedback">
                                            Please select exhibition.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                        <label for="q_usertype_filter" class="form-label">User Type </label>
                                        <select class="form-control select2-ajax" id="q_usertype_filter"
                                            name="q_usertype_filter">
                                        </select>
                                        <div class="invalid-feedback">
                                            Please select type.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                        <label for="q_isconvertinquiry_filter" class="form-label">Is Inquiry </label>
                                        <select class="form-control select2-ajax" id="q_isconvertinquiry_filter"
                                            name="q_isconvertinquiry_filter">
                                        </select>
                                        <div class="invalid-feedback">
                                            Please select type.
                                        </div>
                                    </div>
                                </div>
                                @if(isAdminOrCompanyAdmin() == 1)
                                <div class="col-md-2 align-self-center">
                                    <button class="btn btn-primary" onclick="DownloadExhibitionReport();">Download Excel</button>
                                </div>
                                @endif
                            </div>

                            <table id="datatable" class="table-responsive table table-striped nowrap w-100">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Exhibition Name</th>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Stage Of Site</th>
                                        <th>Phone number</th>
                                        <th>Firm Name</th>
                                        <th>City</th>
                                        <th>Created By</th>
                                        <th>Action</th>
                                        <th>Remark</th>


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


    <div class="modal fade" id="modalInquiry" data-bs-backdrop="static" tabindex="-1" role="dialog"
        aria-labelledby="modalInquiryLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalInquiryLable">Inquiry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formExhibition" action="{{ route('inquiry.exhibition.save') }}" method="POST"
                    class="needs-validation" novalidate>
                    <div class="modal-body">
                        @csrf

                        <input type="hidden" name="exhibition_inquiry_id" id="exhibition_inquiry_id">






                        <h4 class="card-title mb-2 section-lable">Exhibition Inquiry</h4>


                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="inquiry_type" class="form-label">Type <code
                                            class="highlighter-rouge"></code></label>
                                    <input type="text" class="form-control" id="inquiry_type" name="inquiry_type"
                                        placeholder="Type" value="" disabled>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="inquiry_email" class="form-label">Email <code
                                            class="highlighter-rouge"></code></label>
                                    <input type="text" class="form-control" id="inquiry_email" name="inquiry_email"
                                        placeholder="Email" value="" disabled>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="inquiry_plan_type" class="form-label">Plan Type <code
                                            class="highlighter-rouge"></code></label>
                                    <input type="text" class="form-control" id="inquiry_plan_type"
                                        name="inquiry_plan_type" placeholder="Plan Type" value="" disabled>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="inquiry_source" class="form-label">Source <code
                                            class="highlighter-rouge"></code></label>
                                    <input type="text" class="form-control" id="inquiry_source" name="inquiry_source"
                                        placeholder="Source" value="" disabled>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="inquiry_remark" class="form-label">Remark <code
                                            class="highlighter-rouge"></code></label>
                                    <input type="text" class="form-control" id="inquiry_remark" name="inquiry_remark"
                                        placeholder="Remark" value="" disabled>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="inquiry_firm_name" class="form-label">Firm Name <code
                                            class="highlighter-rouge"></code></label>
                                    <input type="text" class="form-control" id="inquiry_firm_name"
                                        name="inquiry_firm_name" placeholder="Firm Name" value="" disabled>
                                </div>
                            </div>
                        </div>




                        <h4 class="card-title mb-2 section-lable">Inquiry</h4>


                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="inquiry_first_name" class="form-label">First name <code
                                            class="highlighter-rouge">*</code></label>
                                    <input type="text" class="form-control" id="inquiry_first_name"
                                        name="inquiry_first_name" placeholder="First name" value="" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="inquiry_last_name" class="form-label">Last name <code
                                            class="highlighter-rouge">*</code></label>
                                    <input type="text" class="form-control" id="inquiry_last_name"
                                        name="inquiry_last_name" placeholder="Last name" value="" required>
                                </div>
                            </div>
                        </div>




                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="inquiry_phone_number" class="form-label">Phone number <code
                                            class="highlighter-rouge" id="inquiry_phone_number_error">*</code></label>
                                    <div class="input-group">
                                        <div class="input-group-text">
                                            +91


                                        </div>
                                        <input type="number" class="form-control" id="inquiry_phone_number"
                                            name="inquiry_phone_number" placeholder="Phone number" value=""
                                            required>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="inquiry_phone_number2" class="form-label">Other Phone number <code
                                            class="highlighter-rouge"></code></label>
                                    <div class="input-group">
                                        <div class="input-group-text">
                                            +91


                                        </div>
                                        <input type="number" class="form-control" id="inquiry_phone_number2"
                                            name="inquiry_phone_number2" placeholder="Phone number" value="">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="inquiry_pincode" class="form-label">Pincode <code
                                            class="highlighter-rouge"></code></label>
                                    <input type="text" class="form-control" id="inquiry_pincode"
                                        name="inquiry_pincode" placeholder="Pincode" value="">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                    <label class="form-label">City <code class="highlighter-rouge">*</code></label>
                                    <select class="form-control select2-ajax" id="inquiry_city_id" name="inquiry_city_id"
                                        required>
                                    </select>
                                    <div class="invalid-feedback">
                                        Please select city.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="inquiry_house_no" class="form-label">House No <code
                                            class="highlighter-rouge">*</code></label>
                                    <input type="text" class="form-control" id="inquiry_house_no"
                                        name="inquiry_house_no" placeholder="House No" value="" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="inquiry_society_name" class="form-label">Building/Society name <code
                                            class="highlighter-rouge">*</code></label>
                                    <input type="text" class="form-control" id="inquiry_society_name"
                                        name="inquiry_society_name" placeholder="Building/Society name" value=""
                                        required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="inquiry_area" class="form-label">Area <code
                                            class="highlighter-rouge">*</code></label>
                                    <input type="text" class="form-control" id="inquiry_area" name="inquiry_area"
                                        placeholder="Area" value="" required>
                                </div>
                            </div>
                        </div>

                        <div class="row" id="row_assigned_to">
                            <div class="col-md-6">

                                <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                    <label class="form-label">Assigned To <code class="highlighter-rouge">*</code></label>
                                    <select class="form-control select2-ajax" id="inquiry_assigned_to"
                                        name="inquiry_assigned_to">
                                    </select>

                                    <div class="invalid-feedback">
                                        Please select assigned to.
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="row " id="row-more_answer_7">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="pre_inquiry_questions_7" class="form-label">Current stage of site <code
                                            class="highlighter-rouge"
                                            id="pre_inquiry_questions_7_require">*</code></label>
                                    <select id="pre_inquiry_questions_7" name="pre_inquiry_questions_7"
                                        class="form-select pre-select2-apply">

                                        <option value="">Select Option</option>
                                        @foreach ($data['stage_of_site'] as $OptK => $OptV)
                                            <option value="{{ $OptV->id }}">{{ $OptV->option }} </option>
                                        @endforeach

                                    </select>

                                </div>
                            </div>
                        </div>

                        <div class="row" id="row_next_followup">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="inquiry_follow_up_type" class="form-label">Next Follow up type <code
                                            class="highlighter-rouge">*</code></label>

                                    <select class="form-control" id="inquiry_follow_up_type"
                                        name="inquiry_follow_up_type" required>
                                        <option value="Meeting">Meeting</option>
                                        <option value="Call">Call</option>

                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="inquiry_follow_up_date_time" class="form-label"> Date & Time of Follow up
                                        <code class="highlighter-rouge">*</code></label>







                                    <div class="input-group" id="inquiry_follow_up_date_time">
                                        <input type="text" class="form-control" value="{{ date('d-m-Y') }}"
                                            placeholder="dd-mm-yyyy" data-date-format="dd-mm-yyyy"
                                            data-date-container='#inquiry_follow_up_date_time' data-provide="datepicker"
                                            data-date-autoclose="true" required name="inquiry_follow_up_date"
                                            id="inquiry_follow_up_date">

                                        <div style="width:50%;">

                                            <select class="form-control" id="inquiry_follow_up_time"
                                                name="inquiry_follow_up_time">

                                                @foreach ($data['timeSlot'] as $timeSlot)
                                                    <option value="{{ $timeSlot }}">{{ $timeSlot }} </option>
                                                @endforeach
                                            </select>
                                        </div>


                                    </div>
                                    <br>
                                    <br>
                                    <br>
                                    <br>
                                    <br>






                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">


                        <button type="submit" class="btn btn-primary">Convert to inquiry</button>


                    </div>
                </form>
            </div>
        </div>
    </div>



    @csrf
@endsection('content')
@section('custom-scripts')

    <script src="{{ asset('assets/libs/parsleyjs/parsley.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/form-validation.init.js') }}"></script>
    <script src="{{ asset('assets/js/pages/jquery.form.js') }}"></script>
    <script src="{{ asset('assets/libs/summernote/summernote.min.js') }}"></script>

    <script src="{{ asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
    <script type="text/javascript">
        var ajaxExhibitionDataURL = "{{ route('inquiry.exhibition.ajax') }}";
        var ajaxExhibitionDetailURL = "{{ route('inquiry.exhibition.detail') }}";
        var ajaxURLInquiryDetail = "{{ route('inquiry.exhibition.detail2') }}";
        var ajaxURLSearchInquiryAssignedTo = "{{ route('inquiry.exhibition..search.assigned.user') }}";
        var ajaxURLSearchCity = "{{ route('search.city') }}";
        var ajaxURLSaveUpdate = "{{ route('inquiry.exhibition.update.save') }}";
        var ajaxURLDownloadExhibitionReport = "{{ route('inquiry.exhibition.report.download.filter') }}";
        var ajaxURLConvertExhibitionUser = "{{ route('inquiry.exhibition.convert.user') }}";


        var scrollTopHeightDataTable = 0;
        var scrollTopHeightModalDetail = 0;
        $("#exhibition_status").select2({
            minimumResultsForSearch: Infinity,
            dropdownParent: $("#modalInquiry")
        });
        var csrfToken = $("[name=_token").val();

        var exhibitionPageLength = getCookie('exhibitionPageLength') !== undefined ? getCookie('exhibitionPageLength') : 10;
        var table = $('#datatable').DataTable({
            "aoColumnDefs": [{
                "bSortable": false,
                "aTargets": [5]
            }],
            "order": [
                [0, 'desc']
            ],
            "processing": true,
            "serverSide": true,
            "responsive": true,
            "pageLength": exhibitionPageLength,
            "ajax": {
                "url": ajaxExhibitionDataURL,
                "type": "POST",
                "data": {
                    "_token": csrfToken,
                    "exhibition_filter": function() {
                        return $("#q_exhibition_filter").val()
                    },
                    "usertype_filter": function() {
                        return $("#q_usertype_filter").val()
                    },
                    "isconvertinquiry_filter": function() {
                        return $("#q_isconvertinquiry_filter").val()
                    },
                }


            },
            "aoColumns": [{
                    "mData": "id"
                },
                {
                    "mData": "exhibition_name"
                },
                {
                    "mData": "name"
                },
                {
                    "mData": "type"
                },
                {
                    "mData": "stage_of_site"
                },
                {
                    "mData": "phone_number"
                },
                {
                    "mData": "firm_name"
                },
                {
                    "mData": "city_name"
                },
                {
                    "mData": "created_by"
                },
                {
                    "mData": "action"
                },
                {
                    "mData": "remark"
                }



            ],
            "drawCallback": function() {

                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {

                    return new bootstrap.Tooltip(tooltipTriggerEl)
                })

            }
        });

        function reloadTable() {
            table.ajax.reload(null, false);
        }


        $('#datatable').on('length.dt', function(e, settings, len) {

            setCookie('exhibitionPageLength', len, 100);


        });


        var newOption = new Option('ALL', '0', false, false);
        $('#q_exhibition_filter').append(newOption).trigger('change');
        var ajaxURLSearchExhibition = '{{ route('inquiry.search.exhibition.filter') }}';
        $("#q_exhibition_filter").select2({
            ajax: {
                url: ajaxURLSearchExhibition,
                dataType: 'json',
                delay: 0,
                data: function(params) {
                    return {

                        q: params.term, // search term
                        page: params.page
                    };
                },
                processResults: function(data, params) {
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
            placeholder: 'Select FY',
            dropdownParent: $("#exhibition_list"),
        }).on('change', function(e) {
            reloadTable();
        });

        var newOption = new Option('ALL', 'All', false, false);
        $('#q_usertype_filter').append(newOption).trigger('change');
        var ajaxURLSearchExhibitionUserType = '{{ route('inquiry.search.exhibition.user.type.filter') }}';
        $("#q_usertype_filter").select2({
            ajax: {
                url: ajaxURLSearchExhibitionUserType,
                dataType: 'json',
                delay: 0,
                data: function(params) {
                    return {
                        q: params.term, // search term
                        page: params.page
                    };
                },
                processResults: function(data, params) {
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
            placeholder: 'Select FY',
            dropdownParent: $("#exhibition_list"),
        }).on('change', function(e) {
            reloadTable();
        });

        var newOption = new Option('ALL', '0', false, false);
        $('#q_isconvertinquiry_filter').append(newOption).trigger('change');
        var ajaxURLSearchExhibitionInquiryConverted = '{{ route('inquiry.search.exhibition.inquiry.convert.filter') }}';
        $("#q_isconvertinquiry_filter").select2({
            ajax: {
                url: ajaxURLSearchExhibitionInquiryConverted,
                dataType: 'json',
                delay: 0,
                data: function(params) {
                    return {
                        q: params.term, // search term
                        page: params.page
                    };
                },
                processResults: function(data, params) {
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
            placeholder: 'Select FY',
            dropdownParent: $("#exhibition_list"),
        }).on('change', function(e) {
            reloadTable();
        });






        $(document).ready(function() {
            var options = {
                beforeSubmit: showRequest, // pre-submit callback
                success: showResponse // post-submit callback

                // other available options:
                //url:       url         // override for form's 'action' attribute
                //type:      type        // 'get' or 'post', override for form's 'method' attribute
                //dataType:  null        // 'xml', 'script', or 'json' (expected server response type)
                //clearForm: true        // clear all form fields after successful submit
                //resetForm: true        // reset the form after successful submit

                // $.ajax options can be used here too, for example:
                //timeout:   3000
            };

            // bind form using 'ajaxForm'
            $('#formExhibition').ajaxForm(options);
        });

        function showRequest(formData, jqForm, options) {

            // formData is an array; here we use $.param to convert it to a string to display it
            // but the form plugin does this for you automatically when it submits the data
            var queryString = $.param(formData);

            // jqForm is a jQuery object encapsulating the form element.  To access the
            // DOM element for the form do this:
            // var formElement = jqForm[0];

            // alert('About to submit: \n\n' + queryString);

            // here we could return false to prevent the form from being submitted;
            // returning anything other than false will allow the form submit to continue
            return true;
        }

        // post-submit callback
        function showResponse(responseText, statusText, xhr, $form) {


            if (responseText['status'] == 1) {
                toastr["success"](responseText['msg']);
                reloadTable();
                resetInputForm();
                $("#modalInquiry").modal('hide');

            } else if (responseText['status'] == 0) {
                if (typeof responseText['data'] !== "undefined") {

                    var size = Object.keys(responseText['data']).length;
                    if (size > 0) {

                        for (var [key, value] of Object.entries(responseText['data'])) {

                            toastr["error"](value);
                        }

                    }

                } else {
                    toastr["error"](responseText['msg']);
                }
            }

            // for normal html responses, the first argument to the success callback
            // is the XMLHttpRequest object's responseText property

            // if the ajaxForm method was passed an Options Object with the dataType
            // property set to 'xml' then the first argument to the success callback
            // is the XMLHttpRequest object's responseXML property

            // if the ajaxForm method was passed an Options Object with the dataType
            // property set to 'json' then the first argument to the success callback
            // is the json data object returned by the server

            // alert('status: ' + statusText + '\n\nresponseText: \n' + responseText +
            //     '\n\nThe output div should have already been updated with the responseText.');
        }




        function resetInputForm() {

            $('#formExhibition').trigger("reset");
            // $("#pre_inquiry_questions_7").empty().trigger('change');
            $("#inquiry_city_id").empty().trigger('change');
            $("#inquiry_assigned_to").empty().trigger('change');



        }


        $("#pre_inquiry_questions_7").select2({

            placeholder: 'Search stage of site',
            dropdownParent: $("#modalInquiry .modal-body")
        });



        $("#inquiry_follow_up_time").select2({
            dropdownParent: $("#modalInquiry .modal-content")
        });

        $("#inquiry_follow_up_type").select2({
            dropdownParent: $("#modalInquiry .modal-content")
        });



        function editView(id) {

            resetInputForm();

            $("#modalInquiry").modal('show');
            $("#modalInquiryLable").html("#" + id + " Convert to inquiry");
            $("#formExhibition").hide();
            $(".loadingcls").show();

            $.ajax({
                type: 'GET',
                url: ajaxExhibitionDetailURL + "?id=" + id,
                success: function(resultData) {
                    if (resultData['status'] == 1) {

                        $("#exhibition_inquiry_id").val(resultData['data']['id']);
                        $("#inquiry_first_name").val(resultData['data']['first_name']);
                        $("#inquiry_last_name").val(resultData['data']['last_name']);

                        $("#inquiry_type").val(resultData['data']['type']);
                        $("#inquiry_email").val(resultData['data']['email']);


                        $("#inquiry_phone_number").val(resultData['data']['phone_number']);
                        $("#inquiry_phone_number2").val("");
                        $("#inquiry_society_name").val(resultData['data']['address_line1']);
                        $("#inquiry_area").val(resultData['data']['address_line2']);


                        $("#inquiry_plan_type").val(resultData['data']['plan_type']);
                        $("#inquiry_source").val(resultData['data']['source']);
                        $("#inquiry_remark").val(resultData['data']['remark']);
                        $("#inquiry_firm_name").val(resultData['data']['firm_name']);



                        if (typeof resultData['data']['city']['id'] !== "undefined") {
                            $("#inquiry_city_id").empty().trigger('change');
                            var newOption = new Option(resultData['data']['city']['text'], resultData['data'][
                                'city'
                            ]['id'], false, false);
                            $('#inquiry_city_id').append(newOption).trigger('change');

                        }


                        if (resultData['data']['assigned'] !== 0) {
                            $("#inquiry_assigned_to").empty().trigger('change');
                            var newOption = new Option(resultData['data']['assigned_to']['text'], resultData[
                                'data']['assigned_to']['id'], false, false);
                            $('#inquiry_assigned_to').append(newOption).trigger('change');

                        } else {
                            $("#inquiry_assigned_to").empty().trigger('change');
                        }

                        if (resultData['data']['stage_of_site_id'] != 0) {

                            $("#pre_inquiry_questions_7").val("" + resultData['data']['stage_of_site_id']);
                            $("#pre_inquiry_questions_7").trigger('change');



                        } else {
                            $("#pre_inquiry_questions_7").val("");
                            $("#pre_inquiry_questions_7").trigger('change');
                        }


                        // $("#exhibition_status").select2("val", "" + resultData['data']['status'] + "");

                        // if (typeof resultData['data']['city']['id'] !== "undefined") {
                        //     $("#exhibition_city_id").empty().trigger('change');
                        //     var newOption = new Option(resultData['data']['city']['text'], resultData['data']['city']['id'], false, false);
                        //     $('#exhibition_city_id').append(newOption).trigger('change');

                        // }


                        // if (resultData['data']['sale_persons'].length > 0) {
                        //     $("#exhibition_sale_persons").empty().trigger('change');
                        //     var selectedSalePersons = [];

                        //     for (var i = 0; i < resultData['data']['sale_persons'].length; i++) {

                        //         selectedSalePersons.push('' + resultData['data']['sale_persons'][i]['id'] + '');

                        //         var newOption = new Option(resultData['data']['sale_persons'][i]['text'], resultData['data']['sale_persons'][i]['id'], false, false);
                        //         $('#exhibition_sale_persons').append(newOption).trigger('change');


                        //     }
                        //     $("#exhibition_sale_persons").val(selectedSalePersons).change();

                        // }








                        $(".loadingcls").hide();
                        $("#formExhibition").show();


                    } else {

                        toastr["error"](resultData['msg']);

                    }

                }
            });

        }

        $("#inquiry_assigned_to").select2({
            ajax: {
                url: ajaxURLSearchInquiryAssignedTo,
                dataType: 'json',
                delay: 0,
                data: function(params) {
                    return {
                        q: params.term, // search term
                        page: params.page,
                        city_id: function() {
                            return $("#inquiry_city_id").val();
                        }
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
            placeholder: 'Search for a assigned to',
            dropdownParent: $("#modalInquiry .modal-body")
        });


        $("#inquiry_city_id").select2({
            ajax: {
                url: ajaxURLSearchCity,
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
            placeholder: 'Search for a city',
            dropdownParent: $("#modalInquiry .modal-content")
        });

        var OpeninquiryId = 0;

        function getDetail(inquiryId) {

            $("#modalDetail").modal('show');
            $("#detail_inquiry_id").val(inquiryId);
            OpeninquiryId = inquiryId;


            var UIType = 'inquiry_update';
            var previousUIType = '';
            $("#" + UIType).html(
                '<p class="mb-0"><div class="col-md-12 text-center loadingcls"><button type="button" class="btn btn-light waves-effect"><i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading</button></div></p>'
            );





            $.ajax({
                type: 'GET',
                url: ajaxURLInquiryDetail + "?inquiry_id=" + inquiryId + '&ui_type=' + UIType,
                success: function(resultData) {

                    previousUIType = UIType;

                    $("#modalDetail .loadingcls").hide();
                    if (resultData['status'] == 1) {

                        $("#" + UIType).html(resultData['view']);

                        $('.inquiry_update_message').summernote({
                            disableResizeEditor: true,

                            toolbar: false,
                            height: 150,
                            hint: {
                                match: /\B@(\w*)$/,
                                users: function(keyword, callback) {
                                    $.ajax({
                                        url: ajaxURLSearchMentionUsers + "?q=" + keyword,
                                        type: 'get',
                                        async: true //This works but freezes the UI
                                    }).done(callback);
                                },
                                search: function(keyword, callback) {
                                    this.users(keyword, callback); //callback must be an array
                                },
                                content: function(item) {

                                    return '@' + item;
                                }
                            }
                        });

                        setTimeout(function() {




                            var tooltipTriggerList = [].slice.call(document.querySelectorAll(
                                '[data-bs-toggle="tooltip"]'))
                            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                                return new bootstrap.Tooltip(tooltipTriggerEl)
                            });


                        }, 500)


                        if (scrollTopHeightModalDetail != 0) {
                            $("#modalDetail").animate({
                                scrollTop: scrollTopHeightDataTable
                            }, 10);

                            scrollTopHeightModalDetail = 0;




                        }




                    } else {

                        toastr["error"](resultData['msg']);

                    }
                }
            });

        }

        function saveInquiryUpdate(id, updateID) {

            if ($("#inquiry_update_message_" + updateID).summernote('code').trim() != "") {

                scrollTopHeightModalDetail = $('#modalDetail').prop('scrollTop');
                // console.log(scrollTopHeightModalDetail);
                if (updateID == 0) {
                    var updateSaveBtnLable = "Updating...";
                } else {
                    var updateSaveBtnLable = "Replying...";
                }
                $("#inquiry_update-save-" + updateID).html(updateSaveBtnLable);
                $("#inquiry_update-save-" + updateID).prop('disabled', true);

                $.ajax({
                    type: 'POST',
                    url: ajaxURLSaveUpdate,
                    data: {
                        "_token": csrfToken,
                        "inquiry_id": id,
                        "inquiry_update_id": updateID,
                        "message": $("#inquiry_update_message_" + updateID).summernote('code'),

                    },
                    success: function(resultData) {

                        $("#inquiry_update_message").val('');
                        scrollTopHeightDataTable = $('.dataTables_scrollBody').prop('scrollTop');


                        if (resultData['status'] == 1) {

                            toastr["success"](resultData['msg']);
                            getDetail(OpeninquiryId);;
                            table.ajax.reload(null, false);
                            if (updateID == 0) {
                                var updateSaveBtnLable = "Update"

                            } else {
                                var updateSaveBtnLable = "Reply"
                            }
                            $("#inquiry_update-save-" + updateID).html(updateSaveBtnLable);
                            $("#inquiry_update-save-" + updateID).prop('disabled', false);
                            $("#inquiry_update_message_" + updateID).val('');

                        } else {

                            toastr["error"](resultData['msg']);
                            if (updateID == 0) {
                                var updateSaveBtnLable = "Update"

                            } else {
                                var updateSaveBtnLable = "Reply"
                            }
                            $("#inquiry_update-save-" + updateID).html(updateSaveBtnLable);

                        }
                    }
                });
            } else {

                toastr["error"]("Please enter text before save");

            }

        }

        function openReplyBox(id) {



            $("#reply-box-" + id).show(300);

        }

        function saveInquiryUpdate(id, updateID) {

            if ($("#inquiry_update_message_" + updateID).summernote('code').trim() != "") {

                scrollTopHeightModalDetail = $('#modalDetail').prop('scrollTop');
                // console.log(scrollTopHeightModalDetail);
                if (updateID == 0) {
                    var updateSaveBtnLable = "Updating...";
                } else {
                    var updateSaveBtnLable = "Replying...";
                }
                $("#inquiry_update-save-" + updateID).html(updateSaveBtnLable);
                $("#inquiry_update-save-" + updateID).prop('disabled', true);

                $.ajax({
                    type: 'POST',
                    url: ajaxURLSaveUpdate,
                    data: {
                        "_token": csrfToken,
                        "inquiry_id": id,
                        "inquiry_update_id": updateID,
                        "message": $("#inquiry_update_message_" + updateID).summernote('code'),

                    },
                    success: function(resultData) {

                        $("#inquiry_update_message").val('');
                        scrollTopHeightDataTable = $('.dataTables_scrollBody').prop('scrollTop');


                        if (resultData['status'] == 1) {

                            toastr["success"](resultData['msg']);
                            getDetail(OpeninquiryId);
                            table.ajax.reload(null, false);
                            if (updateID == 0) {
                                var updateSaveBtnLable = "Update"

                            } else {
                                var updateSaveBtnLable = "Reply"
                            }
                            $("#inquiry_update-save-" + updateID).html(updateSaveBtnLable);
                            $("#inquiry_update-save-" + updateID).prop('disabled', false);
                            $("#inquiry_update_message_" + updateID).val('');

                        } else {

                            toastr["error"](resultData['msg']);
                            if (updateID == 0) {
                                var updateSaveBtnLable = "Update"

                            } else {
                                var updateSaveBtnLable = "Reply"
                            }
                            $("#inquiry_update-save-" + updateID).html(updateSaveBtnLable);

                        }
                    }
                });
            } else {

                toastr["error"]("Please enter text before save");

            }

        }
        function convertToUser(id) {
            $('#'+id).html('<i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i> <span  >Converting...</span>');
                $.ajax({
                    type: 'GET',
                    url: ajaxURLConvertExhibitionUser,
                    data: {
                        "id": id,
                    },
                    success: function(resultData) {

                        if (resultData['status'] == 1) {

                            toastr["success"](resultData['msg']);
                            table.ajax.reload(null, false);
                            $('#'+id).html('Convert to User');
                        } else {
                            $('#'+id).html('Convert to User');
                            toastr["error"](resultData['msg']);
                        }
                    }
                });
            

        }

        function DownloadExhibitionReport() {
            url = ajaxURLDownloadExhibitionReport+"?exhibition_filter="+$("#q_exhibition_filter").val()+"&usertype_filter="+$("#q_usertype_filter").val()+"&isconvertinquiry_filter="+$("#q_isconvertinquiry_filter").val();

            var win = window.open(url, '_blank');
            if (win) {
                //Browser has allowed it to be opened
                win.focus();
            } else {
                //Browser has blocked it
                alert('Please allow popups for this website');
            }

        }
    </script>
@endsection
