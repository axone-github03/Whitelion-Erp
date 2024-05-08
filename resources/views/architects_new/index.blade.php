@extends('layouts.main')
@section('title', $data['title'])
@section('content')

    <link href="{{ asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}" rel="stylesheet"
        type="text/css">
    <link href="{{ asset('assets/libs/summernote/summernote.min.css') }}" rel="stylesheet">


    <style type="text/css">
        .page-content {
            padding: calc(70px + 24px) calc(24px / 2) 0px calc(24px / 2) !important;
        }

        .lead-status-btn {
            background: #808080;
            color: white !important;
            margin-top: 5px;
            margin-bottom: 5px
        }

        .lead-status-btn-white {
            background: #ffffff;
            color: rgb(0, 0, 0) !important;
            margin-top: 5px;
            margin-bottom: 5px
        }

        .btn-arrow-right,
        .btn-arrow-left {
            position: relative;
            padding-left: 18px;
            padding-right: 18px;
        }

        .btn-arrow-right {
            padding-left: 23px;
            margin-right: 0px;
        }

        .btn-arrow-left {
            padding-right: 36px;
        }

        .btn-arrow-right:before,
        .btn-arrow-right:after,
        .btn-arrow-left:before,
        .btn-arrow-left:after {
            /* make two squares (before and after), looking similar to the button */
            content: "";
            position: absolute;
            top: 3px;
            /* move it down because of rounded corners */
            width: 19px;
            /* same as height */
            height: 19px;
            /* button_outer_height / sqrt(2) */
            background: inherit;
            /* use parent background */
            border: inherit;
            /* use parent border */
            border-left-color: transparent;
            /* hide left border */
            border-bottom-color: transparent;
            /* hide bottom border */
            border-radius: 0px 4px 0px 0px;
            /* round arrow corner, the shorthand property doesn't accept "inherit" so it is set to 4px */
            -webkit-border-radius: 0px 4px 0px 0px;
            -moz-border-radius: 0px 4px 0px 0px;
        }

        .btn-arrow-right:before,
        .btn-arrow-right:after {
            transform: rotate(45deg);
            /* rotate right arrow squares 45 deg to point right */
            -webkit-transform: rotate(45deg);
            -moz-transform: rotate(45deg);
            -o-transform: rotate(45deg);
            -ms-transform: rotate(45deg);
        }

        .btn-arrow-left:before,
        .btn-arrow-left:after {
            transform: rotate(225deg);
            /* rotate left arrow squares 225 deg to point left */
            -webkit-transform: rotate(225deg);
            -moz-transform: rotate(225deg);
            -o-transform: rotate(225deg);
            -ms-transform: rotate(225deg);
        }

        .btn-arrow-right:before,
        .btn-arrow-left:before {
            /* align the "before" square to the left */
            left: -9px;
        }

        .btn-arrow-right:after,
        .btn-arrow-left:after {
            /* align the "after" square to the right */
            right: -9px;
        }

        .btn-arrow-right:after,
        .btn-arrow-left:before {
            /* bring arrow pointers to front */
            z-index: 1;
        }

        .btn-arrow-right:before,
        .btn-arrow-left:after {
            /* hide arrow tails background */
            background-color: rgb(247, 247, 250);
        }

        .lead-detail .btn-arrow-right:before,
        .btn-arrow-left:after {
            /* hide arrow tails background */
            background-color: white;
        }

        .lead-search-form {
            width: 100%;
            display: inline-block !important;
            padding: 0;
            margin-top: 5px;
        }

        .lead-search-form .form-control {
            background: white;
            border-radius: 2px;
            border: 1px solid gainsboro;
        }

        .col-form-label {
            /* padding: 2px 11px; */
        }

        /* .lead-detail .form-control, */
        /* .input-group-text { */
        /* padding: 2px 11px; */
        /* border: none;
                                                                } */

        @media (min-width: 1200px) {
            .chat-leftsidebar {
                min-width: 330px;
            }
        }

        .card-header {
            /* background: #000000c2 !important; */
            border-radius: 5px;
            font-weight: 300;
            color: black;
            font-size: 12px;
            font-weight: 400;
        }

        .btn-header-right {
            margin-top: -3px;
        }

        .nav-pills>li>a,
        .nav-tabs>li>a,
        .nav-tabs>li>a span {
            font-weight: 400;
        }

        .active_lead {
            background-color: rgb(141, 226, 255);
            border-color: transparent;
            -webkit-box-shadow: 0 0.75rem 1.5rem rgba(18, 38, 63, .03);
            box-shadow: 0 0.75rem 1.5rem rgba(18, 38, 63, .03);
        }

        .static_hover tr:hover {
            background-color: rgb(141, 226, 255);
            border-color: transparent;
            -webkit-box-shadow: 0 0.75rem 1.5rem rgba(18, 38, 63, .03);
            box-shadow: 0 0.75rem 1.5rem rgba(18, 38, 63, .03);
        }

        .reminder_checkbox {
            width: 15px;
        }

        .lead-detail,
        .lead-list {
            /* box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px; */
            box-shadow: 0px 3px 3px 0px rgba(0, 0, 0, 0.05);
        }

        .status-active-class {
            background-color: #556ee6 !important;
            color: #ffffff !important
        }

        .status-active-class:before {
            border-left-color: #556ee6 !important;
            color: #fff;
        }

        .next-status-active-class {
            background-color: #556ee6;
            color: #ffffff !important
        }

        .border-bottom {
            /* border-bottom: 1px solid #d1d1d1 !important; */
        }

        .border-none {
            border: none !important;
        }

        /* #modalCall .select2-selection.select2-selection--single {
                                                                    border: none !important;
                                                                }

                                                                #modalMeeting .select2-selection.select2-selection--single {
                                                                    border: none !important;
                                                                }

                                                                #modalMeeting .select2-selection.select2-selection--multiple {
                                                                    border: none !important;
                                                                }

                                                                #modalTask .select2-selection.select2-selection--single {
                                                                    border: none !important;
                                                                } */

        .bx.bx-check-circle.text-success {
            cursor: pointer;
        }

        .select2-search__field {
            width: 100% !important;
        }

        span.closing-badge3 {
            border-radius: 0.25rem !important;
            background: rgb(239 242 247);
            padding: 5px 4px;
        }
        span.closing-badge4 {
            border-radius: 0.25rem !important;
            background: rgb(239 242 247);
            padding: 5px 4px;
        }
        div.div_tip3,
        div.div_tip4 {
            /* min-width: 100%; */
            display: none;
            background: #bbbefcf0;
            position: absolute;
            /* z-index: -1; */
            border-radius: 5px;
            -moz-border-radius: 5px;
            box-shadow: 0px 1px 2px #888888;
            -moz-box-shadow: 0px 1px 2px #888888;
        }

        div.div_tip3:hover,
        div.div_tip4:hover {
            /* z-index: 1; */
            display: block;
        }

        .closing-badge3:hover+.div_tip3,
        .closing-badge4:hover+.div_tip4 {
            display: block !important;
            z-index: 999;
        }

        div.div_tip3 .tip_arrow3,
        div.div_tip4 .tip_arrow4 {
            position: absolute;
            /*top: 100%;*/
            /*left: 50%;*/
            border: solid transparent;
            height: 0;
            width: 0;
            pointer-events: none;
        }

        div.div_tip3 .tip_arrow3,
        div.div_tip4 .tip_arrow4 {
            /*border-color: rgba(62, 83, 97, 0);*/
            /*border-top-color: #3e5361;*/
            border-width: 10px;
            /*margin-left: -10px; */
        }

        .lds-spinner {
            display: inline-block;
            position: relative;
            width: 34px;
            height: 15px;
        }

        .lds-spinner div {
            transform-origin: 31px 10px;
            animation: lds-spinner 1.2s linear infinite;
        }

        .lds-spinner div:after {
            content: " ";
            display: block;
            position: absolute;
            top: 0px;
            left: 30px;
            width: 2px;
            height: 7px;
            border-radius: 20%;
            background: #000;
        }

        .lds-spinner div:nth-child(1) {
            transform: rotate(0deg);
            animation-delay: -1.1s;
        }

        .lds-spinner div:nth-child(2) {
            transform: rotate(30deg);
            animation-delay: -1s;
        }

        .lds-spinner div:nth-child(3) {
            transform: rotate(60deg);
            animation-delay: -0.9s;
        }

        .lds-spinner div:nth-child(4) {
            transform: rotate(90deg);
            animation-delay: -0.8s;
        }

        .lds-spinner div:nth-child(5) {
            transform: rotate(120deg);
            animation-delay: -0.7s;
        }

        .lds-spinner div:nth-child(6) {
            transform: rotate(150deg);
            animation-delay: -0.6s;
        }

        .lds-spinner div:nth-child(7) {
            transform: rotate(180deg);
            animation-delay: -0.5s;
        }

        .lds-spinner div:nth-child(8) {
            transform: rotate(210deg);
            animation-delay: -0.4s;
        }

        .lds-spinner div:nth-child(9) {
            transform: rotate(240deg);
            animation-delay: -0.3s;
        }

        .lds-spinner div:nth-child(10) {
            transform: rotate(270deg);
            animation-delay: -0.2s;
        }

        .lds-spinner div:nth-child(11) {
            transform: rotate(300deg);
            animation-delay: -0.1s;
        }

        .lds-spinner div:nth-child(12) {
            transform: rotate(330deg);
            animation-delay: 0s;
        }

        @keyframes lds-spinner {
            0% {
                opacity: 1;
            }

            100% {
                opacity: 0;
            }
        }

        .dropdown-menu-advancefilter {
            width: 700px !important;
        }

        input[type="radio"] {
            appearance: none;
            border: 1px solid #d3d3d3;
            width: 20px;
            height: 20px;
            content: none;
            outline: none;
            margin: 0;
            /* box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px; */
        }

        input[type="radio"]:checked {
            appearance: none;
            outline: none;
            padding: 0;
            content: none;
            border: none;
        }

        input[type="radio"]:checked::before {
            position: absolute;
            color: green !important;
            content: "\00A0\2713\00A0" !important;
            /* border: 1px solid #d3d3d3; */
            font-weight: bolder;
            font-size: 21px;
            /* width: 20px;
                                                                                                                height: 20px; */
        }

        .star-radio {
            display: inline-block;
            margin-right: 5px;
            margin-bottom: 0px;
        }

        .star-radio input[type="radio"] {
            display: none;
        }

        .star-radio .star {
            display: inline-block;
            width: 16px;
            height: 16px;
            background-image: url({{ asset('assets/images/star.png') }});
            background-repeat: no-repeat;
            background-position: center;
            background-size: cover;
            cursor: pointer;
        }

        .star-radio input[type="radio"]:checked+.star {
            display: inline-block;
            width: 16px;
            height: 16px;
            background-image: url({{ asset('assets/images/star_fill.png') }});
            background-repeat: no-repeat;
            background-position: center;
            background-size: cover;
            /* margin-right: 10px; */
            cursor: pointer;
        }

        #datatable_wrapper:nth-child(3) {
            position: fixed;
        }

        /* .dataTables_filter {
                                position: relative;
                            } */

        /* .dataTables_filter input {
                                width: 250px;
                                height: 32px;
                                background: #fcfcfc;
                                border: 1px solid #000000;
                                border-radius: 5px;
                                text-indent: 10px;
                            } */

        /* .dataTables_filter .fa-search {
                                position: absolute;
                                top: 10px;
                                left: auto;
                                right: -0%;
                            } */

        .hidden_text {
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }


        @media (max-width: 1440px) and (min-width: 400px) {
            body {
                font-size: 10px !important;
            }

            .funnel {
                font-size: 10px !important;
            }

            .funnel1 {
                font-size: 10px !important;
            }
        }

        .nav-tabs .nav-item.show .nav-link,
        .nav-tabs .nav-link.active {
            color: #495057;
            background-color: #d2e3ff;
            border-color: #ced4da #ced4da #fff;
        }

        .timeline-marker-web {
            top: 0;
            bottom: 0;
            left: 0;
            width: 15px;
            display: table-cell;
            position: relative;
            vertical-align: top;
        }

        .timeline-marker-web:before {
            border: 3px solid transparent;
            border-radius: 100%;
            content: "";
            display: block;
            height: 15px;
            position: absolute;
            top: 0px;
            left: 0;
            width: 15px;
            transition: background 0.3s ease-in-out, border 0.3s ease-in-out;
            background-position: center;
            background-image: url({{ asset('assets/images/timeline/web.svg') }});
        }

        .timeline-marker-web:after {
            content: "";
            width: 1px;
            background: #000000;
            display: block;
            position: absolute;
            top: 15px;
            bottom: 0;
            left: 7px;
        }

        .timeline-marker-android {
            top: 0;
            bottom: 0;
            left: 0;
            width: 15px;
            display: table-cell;
            position: relative;
            vertical-align: top;
        }

        .timeline-marker-android:before {
            background: #d3d3d3;
            border: 3px solid transparent;
            border-radius: 100%;
            content: "";
            display: block;
            height: 15px;
            position: absolute;
            top: 0px;
            left: 0;
            width: 15px;
            transition: background 0.3s ease-in-out, border 0.3s ease-in-out;
            background-position: center;
            background-image: url({{ asset('assets/images/timeline/android.svg') }});
        }

        .timeline-marker-android:after {
            content: "";
            width: 1px;
            background: #000000;
            display: block;
            position: absolute;
            top: 15px;
            bottom: 0;
            left: 7px;
        }

        .timeline-marker-iphone {
            top: 0;
            bottom: 0;
            left: 0;
            width: 15px;
            display: table-cell;
            position: relative;
            vertical-align: top;
        }

        .timeline-marker-iphone:before {
            background: #d3d3d3;
            border: 3px solid transparent;
            border-radius: 100%;
            content: "";
            display: block;
            height: 15px;
            position: absolute;
            top: 0px;
            left: 0;
            width: 15px;
            transition: background 0.3s ease-in-out, border 0.3s ease-in-out;
            background-position: center;
            background-image: url({{ asset('assets/images/timeline/ios.svg') }});
        }

        .timeline-marker-iphone:after {
            content: "";
            width: 1px;
            background: #000000;
            display: block;
            position: absolute;
            top: 15px;
            bottom: 0;
            left: 7px;
        }
        
    </style>



    <div class="page-content">
        <div class="container-fluid">
            <div class="row ms-1">
                <div class="d-lg-flex" id="custom_height" style="">
                    <div class="chat-leftsidebar me-lg-3 col-3">
                        <div class="tab-content py-1">
                            <input type="hidden" name="" value="0" id="hidden_status">
                            <input type="hidden" name="" value="0" id="hidden_is_advancefilter">
                            <div class="tab-pane show active lead-list" id="chat">
                                <table id="datatable" class="table static_hover dt-responsive nowrap w-100" style="">
                                    <thead class="d-none">
                                        <tr>
                                            <th>data</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-9" id="lead_detail" style="">

                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- container-fluid -->
    <div class="modal fade" id="modalDetail" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" role="dialog"
        aria-labelledby="modalDetailLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetailLabel">Detail </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <input type="hidden" name="detail_user_id" id="detail_user_id">





                <div class="modal-body" id="modelBodyDetail">

                    <ul class="nav nav-tabs nav-tabs-custom nav-justified" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#user_update"
                                onclick="loadDetail('inquiry_update')" role="tab">
                                <span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
                                <span class="d-none d-sm-block">Update</span>
                            </a>
                        </li>

                    </ul>

                    <div class="tab-content p-3 text-muted">
                        <div class="tab-pane active" id="user_update" role="tabpanel">

                        </div>

                    </div>



                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>

                </div>


            </div>
        </div>
    </div>


    @include('architects_new/comman/create_architects_modal');

    @include('user_action.action_modal')


@endsection('content')
@section('custom-scripts')


    <script src="{{ asset('assets/libs/parsleyjs/parsley.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/form-validation.init.js') }}"></script>
    <script src="{{ asset('assets/js/pages/jquery.form.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
    <script src="{{ asset('assets/libs/summernote/summernote.min.js') }}"></script>
    



    <script type="text/javascript">
        var selectedUserType = "{{ $data['type'] }}";
        var isSalePerson = "{{ $data['isSalePerson'] }}";
        var ajaxURL = "{{ route('new.architects.ajax') }}";
        var ajaxChangeCategory = "{{ route('new.architects.change.category') }}";
        var ajaxURLUserUpdateDetail = "{{ route('users.update.detail') }}";
        var ajaxURLUpdateSave = "{{ route('user.action.update.save') }}";
        var ajaxURLUserUpdateSeen = "{{ route('users.update.seen') }}";
        var ajaxURLDataDatail = "{{ route('new.architects.get.detail') }}";
        var ajaxURLDataList = "{{ route('new.architects.detail.list') }}";
        var ajaxURLDataListAjax = "{{ route('new.architects.detail.list.ajax') }}";
        var ajaxURLPointAjax = "{{ route('crm.lead.point.ajax') }}";


        var viewLeadId = "{{ $data['id'] }}";



        $("#filter_architect_advance").select2({
            minimumResultsForSearch: Infinity
        });


        var seachUserId = "{{ $data['searchUserId'] }}";
        var addView = "{{ $data['addView'] }}";


        var csrfToken = $("[name=_token]").val();
        var architectPageLength = getCookie('architectPageLength') !== undefined ? getCookie('architectPageLength') : 10;



        $(document).ready(function() {
            if (addView == 1) {
                $("#addBtnUser").click();
            }
            var isdetailload = 0;
            // reloadArcList($("#arc_active_status").val())
        });

        var scrollTopHeightDataTable = 0;
        var scrollTopHeightModalDetail = 0;

        function openReplyBox(id) {



            $("#reply-box-" + id).show(300);

        }

        function getDataDetail(id) {
            isdetailload = 0;
            $("#lead_" + id).parent().parent().addClass('active_lead');
            $.ajax({
                type: 'GET',
                url: ajaxURLDataDatail + "?id=" + id,
                success: function(resultData) {
                    if (resultData['status'] == 1) {
                        var data = resultData['data']['user'];
                        var tag_data = resultData['data']['user']['tag'];
                        $("#lead_detail").html(resultData['view']);
                        
                        
                        $('#arc_lifetime_point').text(data['lifetime_point_lable']);
                        $('#arc_redeemed_point').text(data['redeemed_point']);
                        $('#arc_available_point').text(data['available_point']);
                        
                        var newOption = new Option(data['sale_person']['text'], data['sale_person']['id'], false, false);
                        $('#user_owner').append(newOption).trigger('change');
                        $("#user_owner").val("" + data['sale_person']['id'] + "");
                        $('#user_owner').trigger('change');


                        if (tag_data != null) {
                            if (tag_data.length > 0) {
                                $("#user_tag_id").empty().trigger('change');
                                var selectedSalePersons = [];
                                for (var i = 0; i < tag_data.length; i++) {
                                    selectedSalePersons.push('' + tag_data[i]['id'] + '');
                                    var newOption = new Option(tag_data[i]['text'], tag_data[i]['id'], false, false);
                                    $('#user_tag_id').append(newOption).trigger('change');
                                }
                                $("#user_tag_id").val(selectedSalePersons).change();
                            }
                        }
                        $(".lead_li").parent().parent().removeClass('active_lead');
                        $("#lead_" + id).parent().parent().addClass('active_lead');
                        Load_Tooltip_Action();

                        isdetailload = 1;
                    } else if (resultData['status'] == 0) {
                        //     toastr["error"](resultData['msg']);
                        //     $("#lead_detail").html("");

                    }
                }
            });
        }

        function ShowSelectedStatusData(status_id) {
            $('.userscomman .funnel').removeClass('active');
            $('#arc_funnel_' + status_id).addClass('active');
            $('#arc_active_status').val(status_id);
            reloadArcList(status_id);
            // getList($("#input_search").val(), status_id)
        }

        function getList(searchValue = "", status = 0, isAdvanceFilter = 0) {

            let advanceFilterList = [];
            if (isAdvanceFilter == 1) {
                advanceFilterList.push({
                    clause: $('#selectAdvanceFilterClause_0').val(),
                    column: $('#selectAdvanceFilterColumn_0').val(),
                    condtion: $('#selectAdvanceFilterCondtion_0').val(),
                    value_text: $('#lead_filter_value_0').val(),
                    value_source_type: $('#lead_filter_source_type_value_0').val(),
                    value_select: $('#lead_filter_select_value_0').val(),
                    value_multi_select: $('#lead_filter_select_value_multi_0').val(),
                    value_date: $('#lead_filter_date_picker_value_0').val(),
                    value_from_date: $('#lead_filter_from_date_picker_value_0').val(),
                    value_to_date: $('#lead_filter_to_date_picker_value_0').val(),
                });

                $('#advanceFilterRows input[name="multi_filter_loop"]').each(function(ind) {
                    let filtValId = $(this).attr("filt_id");

                    advanceFilterList.push({
                        clause: $('#selectAdvanceFilterClause_' + filtValId).val(),
                        column: $('#selectAdvanceFilterColumn_' + filtValId).val(),
                        condtion: $('#selectAdvanceFilterCondtion_' + filtValId).val(),
                        value_text: $('#lead_filter_value_' + filtValId).val(),
                        value_source_type: $('#lead_filter_source_type_value_' + filtValId).val(),
                        value_select: $('#lead_filter_select_value_' + filtValId).val(),
                        value_multi_select: $('#lead_filter_select_value_multi_' + filtValId).val(),
                        value_date: $('#lead_filter_date_picker_value_' + filtValId).val(),
                        value_from_date: $('#lead_filter_from_date_picker_value_' + filtValId).val(),
                        value_to_date: $('#lead_filter_to_date_picker_value_' + filtValId).val(),
                    });
                });
            }

            $.ajax({
                type: 'GET',
                url: ajaxURLDataList,
                data: {
                    '_token': $("[name=_token]").val(),
                    'search': searchValue,
                    'status': status,
                    "AdvanceData": advanceFilterList,
                    "isAdvanceFilter": isAdvanceFilter,
                },
                success: function(resultData) {
                    if (resultData['status'] == 1) {
                        $("#sideBarUI .simplebar-content").html(resultData['view']);
                        $("#saveAdvanceFilter").html('<span>Save</span>');
                    } else if (resultData['status'] == 0) {
                        toastr["error"](resultData['msg']);
                    }
                }
            });
        }

        function changeAddActionType(id) {
            var addActionType = $("#add_action_type").val();
            $("#add_action_type").val('0');
            if (addActionType == "1") {
                $('#call_notes_label').text('Call Notes')
                $('#call_description').attr('placeholder', 'Call Notes');

                $('#call_closing_note_div').addClass('d-none');
                $('#call_reminder_div').removeClass('d-none');
                $('#call_purpose_div').removeClass('d-none');
                $('#call_call_outcome_div').addClass('d-none');

                $('#formUserCall').trigger("reset");
                $('#call_type_id').empty().trigger('change');
                $('#call_contact_name').empty().trigger('change');
                $('#call_call_outcome').empty().trigger('change');

                $("#modalCall").modal('show');
                $("#formUserCall .loadingcls").hide();
                $("#call_user_id").val(id);
                $("#call_id").val(0);
                $('#callFooter1 .save-btn').show();
                $('#callFooter1 .save-btn').removeClass('d-none');
                $("#call_move_to_close_btn").hide();
                $('#modalCallLabel').text('Call');
                $("#call_move_to_close").val(0);

                $('#call_type_div, #call_contact_name_div, #call_call_schedule_div, #call_reminder_div, #call_purpose_div, #call_notes_div, #select2-call_type_id-container, #call_call_schedule, #select2-call_contact_name-container, #call_description, #call_purpose, #call_reminder, #call_schedule_date, #select2-call_schedule_time-container, #select2-call_reminder_date_time-container')
                    .removeClass('bg-light')
                $('#call_call_schedule, #call_reminder, #call_description').attr('readonly', false);
                $('#pointer_event_call_type, #pointer_event_call_contact_name, #call_call_schedule_div, #call_reminder_div')
                    .removeClass('pe-none');

            } else if (addActionType == "2") {

                $('#meeting_description_label').text('Meeting Notes');
                $('#meeting_description').attr('placeholder', 'Meeting Notes');
                $('#meeting_is_notification_div').removeClass('d-none');

                $('#formUserMeeting').trigger("reset");
                $('#meeting_title_id').empty().trigger('change');
                $('#meeting_participants').empty().trigger('change');
                $('#meeting_type_id').empty().trigger('change');
                $('#meeting_meeting_outcome').empty().trigger('change');
                $('#meeting_status').empty().trigger('change');

                $("#modalMeeting").modal('show');
                $("#formUserMeeting .loadingcls").hide();
                $("#meeting_user_id").val(id);
                $("#meeting_id").val(0);
                $("#meeting_move_to_close_btn").hide();
                $('#modalMeetingLabel').text('Set Up Meeting');
                $('#meetingFooter1 .save-btn').show();
                $('#meetingFooter1 .save-btn').removeClass('d-none');
                $("#meeting_move_to_close").val(0);

                $('#meeting_closing_note_div').addClass('d-none');
                $('#meeting_outcome_div').addClass('d-none');
                $('#meeting_status_div').addClass('d-none');
                $('#meeting_title_div, #meeting_type_div, #meeting_location_div, #meeting_date_time_div, #meeting_is_notification_div, #meeting_participants_div, #meeting_note_div, #select2-meeting_title_id-container, #select2-meeting_type_id-container, #meeting_location, #meeting_meeting_date_time, #meeting_reminder_id, #meeting_description, #select2-meeting_reminder_date_time-container, #meeting_date, #select2-meeting_time-container')
                    .removeClass('bg-light')
                $('#meeting_participants_div .select2-selection--multiple').removeClass('bg-light');
                $('#meeting_location, #meeting_meeting_date_time, #meeting_reminder_id, #meeting_description')
                    .attr('readonly', false);
                $('#pointer_event_meeting_participants, #pointer_event_meeting_title, #pointer_event_meeting_type, #meeting_date_time_div, #meeting_is_notification_div')
                    .removeClass('pe-none');

            } else if (addActionType == "3") {

                $('#formUserTask').trigger("reset");
                $("#task_assign_to").empty().trigger('change');
                $('#task_outcome').empty().trigger('change');
                $('#task_status').empty().trigger('change');


                $('#status_div').addClass('d-none');
                $('#closing_note_div').addClass('d-none');
                $('#task_outcome_div').addClass('d-none');

                $("#modalTask").modal('show');
                $("#formUserTask .loadingcls").hide();
                $("#task_user_id").val(id);
                $("#task_id").val(0);
                $("#task_move_to_close_btn").hide();
                $('#modalTaskLabel').text('Schedule Task');
                $('#taskfooter1 .save-btn').show();
                $('#taskfooter1 .save-btn').removeClass('d-none');
                $("#task_move_to_close").val(0);

                var newOption = new Option("SELF", "0", false, false);
                $('#task_assign_to').append(newOption).trigger('change');
                $("#task_assign_to").val("" + "0" + "");
                $('#task_assign_to').trigger('change');
                $('#task_assign_to_div, #task_div, #task_due_date_time_div, #task_reminder_div, #task_description_div, #select2-task_assign_to-container, #user_task, #task_due_date, #select2-task_reminder_date_time-container, #task_description, #select2-task_due_time-container')
                    .removeClass('bg-light');
                $('#user_task, #task_due_date_time, #task_reminder_id, #task_description').attr(
                    'readonly', false);
                $('#pointer_event_assign_to, #task_due_date_time_div, #task_reminder_div').removeClass('pe-none');
            }
        }

        function saveUpdate() {

            var user_notes = $("#user_notes").val();
            $("#note_loader").show();

            $.ajax({
                type: 'POST',
                url: ajaxURLUpdateSave,
                data: {
                    'user_id': $("#user_main_detail_id").val(),
                    'note': user_notes,
                    '_token': $("[name=_token]").val()
                },
                success: function(responseText) {
                    if (responseText['status'] == 1) {
                        $("#user_notes").val('');
                        // $("#detail_user_id").val('');
                        $("#tab_notes").html(responseText['data']['view']);
                        $("#note_loader").hide();
                        toastr["success"](responseText['msg']);

                    } else {

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
                        $("#note_loader").hide();

                    }
                }
            });

        }

        window.smoothScroll = function(target) {
            var scrollContainer = target;


            do { //find scroll container
                scrollContainer = scrollContainer.parentNode.parentNode;
                if (!scrollContainer) return;
                scrollContainer.scrollTop += 1;
            } while (scrollContainer.scrollTop == 0);

            var targetY = 0;
            do { //find the top of target relatively to the container
                if (target == scrollContainer) break;
                targetY += target.offsetTop;
            } while (target = target.offsetParent);

            scroll = function(c, a, b, i) {
                i++;
                if (i > 30) return;
                c.scrollTop = a + (b - a) / 30 * i;
                setTimeout(function() {
                    scroll(c, a, b, i);
                }, 20);
            }

            targetY = targetY - 300
            scroll(scrollContainer, scrollContainer.scrollTop, targetY, 0);
        }

        // $("#saveAdvanceFilter").on('click', function(event) {

        //     var isValid = true;

        //     // Check selectAdvanceFilterColumn_0
        //     var selectColumn = $("#selectAdvanceFilterColumn_0");
        //     if (!selectColumn.val() || selectColumn.val() == "0") {

        //         isValid = false;
        //     }

        //     // Check selectAdvanceFilterCondtion_0
        //     var selectCondition = $("#selectAdvanceFilterCondtion_0");
        //     if (!selectCondition.val() || selectCondition.val() == "0") {

        //         isValid = false;
        //     }

        //     // Check lead_filter_select_value_0 (assuming it's a select)
        //     var selectValue = $("#lead_filter_select_value_0");
        //     var selectmultiValue = $("#lead_filter_select_value_multi_0");
        //     var selectFromDateValue = $("#lead_filter_from_date_picker_value_0");
        //     var selectToDateValue = $("#lead_filter_to_date_picker_value_0");
        //     var selecttextValue = $("#lead_filter_value_0");
        //     if (!selectValue.val() || selectValue.val().length == 0 || 
        //     !selectmultiValue.val() || selectmultiValue.val().length == 0 ||
        //     !selectFromDateValue.val() || selectFromDateValue.val().length == 0 ||
        //     !selecttextValue.val() || selecttextValue.val().length == 0) {

        //         isValid = false;
        //     }


        //     if (isValid) {
        //         status = $('.userscomman .active').attr('data-id');
        //         $("#saveAdvanceFilter").html(
        //             '<i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i> <span  >Saving...</span>'
        //         );
        //         // getList("", status, 1)
        //         reloadArcList(0, 1);
        //         ischeckFilter();
        //     };
        // });

        $("#saveAdvanceFilter").on('click', function(event) {

            // var isValid = true;
            // var isfiter = 1;
            // var selectColumn = $("#selectAdvanceFilterColumn_0");
            // if (!selectColumn.val() || selectColumn.val() == "0") {
            //     isValid = false;
            // }

            // var selectCondition = $("#selectAdvanceFilterCondtion_0");
            // if (!selectCondition.val() || selectCondition.val() == "0") {
            //     isValid = false;
            // }

            // var selectValue = $("#lead_filter_select_value_0");
            // var selectmultiValue = $("#lead_filter_select_value_multi_0");
            // var selectFromDateValue = $("#lead_filter_from_date_picker_value_0");
            // var selectToDateValue = $("#lead_filter_to_date_picker_value_0");
            // var selecttextValue = $("#lead_filter_value_0");
            // if (!selectValue.val() || selectValue.val().length == 0 || 
            // !selectmultiValue.val() || selectmultiValue.val().length == 0 ||
            // !selectFromDateValue.val() || selectFromDateValue.val().length == 0 ||
            // !selecttextValue.val() || selecttextValue.val().length == 0) {

            //     isValid = false;
            //     isfiter = 0;
            // }


            // if (isValid) {
            status = $('.userscomman .active').attr('data-id');
            $("#saveAdvanceFilter").html(
                '<i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i> <span  >Saving...</span>'
            );
            reloadArcList(0, 1);
            // };
        });

        // $("#saveAdvanceFilter").on('click', function(event) {
        //     status = $('.userscomman .active').attr('data-id');
        //     $("#saveAdvanceFilter").html(
        //         '<i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i> <span  >Saving...</span>');
        //     // getList("", status, 1)
        //     reloadArcList(0, 1);
        // });

        $('#btnClearAdvanceFilter').on('click', function(event) {
            status = $('.userscomman .active').attr('data-id');
            reloadArcList(status, 0)

            // getList("", status, 0)
        })



        let advanceFilterList = '';
        $('#hidden_status').val($("#arc_active_status").val());
        var table = $('#datatable').DataTable({
            "aoColumnDefs": [{
                "bSortable": true,
                "aTargets": [0]
            }],
            "pageLength": 10,
            "scrollX": false,
            "scrollY": 600,
            "order": [
                [0, 'desc']
            ],
            "processing": true,
            "serverSide": true,
            "bInfo": false,
            "ajax": {
                "url": ajaxURLDataListAjax,
                "type": "POST",
                "data": {
                    "_token": csrfToken,
                    'isAdvanceFilter': function() {
                        return $('#hidden_is_advancefilter').val();
                    },
                    'AdvanceData': function() {
                        return advanceFilterList;
                    },
                    "status": function() {
                        return $('#hidden_status').val();
                    }
                }
            },
            "aoColumns": [{
                "mData": "view"
            }, ],
            "pagingType": "full_numbers",
            "language": {
                "search": "",
                "sLengthMenu": "_MENU_",
                "paginate": {
                    "previous": "<",
                    "next": ">",
                    "first": "|<",
                    "last": ">|"
                }
            },
        });

        $.fn.DataTable.ext.pager.numbers_length = 5;
        $('#datatable_length').each(function() {
            $(this).before(
                '<div><i id="list_data_loader" class="bx bx-loader bx-spin font-size-16 align-middle me-2" style=""></i><b>' +
                '{{ $data['title'] }}' + '</b></div>'
            );
        });

        $(document).ready(function() {
            $('#datatable_length').parent().removeClass().addClass(
                'col-11 d-flex justify-content-between align-items-center card-header py-2 px-2');
            $('#datatable_length').parent().parent().addClass('justify-content-center');
            $('#datatable_length label').addClass('m-0');
            $('#datatable_filter').parent().removeClass().addClass('col-12');
            $('#datatable_filter label').addClass('input-group position-relative mb-0');
            $('#datatable_paginate').parent().removeClass().addClass('col-12 d-flex justify-content-center');
        });

        table.on('xhr', function() {
            var status = $('#hidden_status').val();
            var responseData = table.ajax.json();
            if (responseData['status'] == 0) {
                toastr['error'] = responseData['msg'];
            } else {
                $('.lead_status_filter_remove').removeClass("next-status-active-class");
                $('.lead_status_filter_' + status).addClass("next-status-active-class");

                if (viewLeadId == null || viewLeadId == "" || viewLeadId == undefined || viewLeadId == 0) {
                    getDataDetail(responseData['FirstPageLeadId']);
                } else {
                    getDataDetail(viewLeadId);
                }
                $("#saveAdvanceFilter").html('<span>Save</span>');
                $('#list_data_loader').hide();
            }
        });

        function reloadArcList(status = 0, isAdvanceFilter = 0) {

            if (status != 0) {
                clearAllFilter(0);
                isLeadAmountSummaryRefresh = 0;
                $('#hidden_status').attr('value', status);
            }



            let tempadvanceFilterList = [];
            if (isAdvanceFilter == 1) {
                isLeadAmountSummaryRefresh = 0;

                $('#hidden_status').attr('value', 0);
                $('#hidden_is_advancefilter').attr('value', isAdvanceFilter);

                tempadvanceFilterList.push({
                    clause: $('#selectAdvanceFilterClause_0').val(),
                    column: $('#selectAdvanceFilterColumn_0').val(),
                    condtion: $('#selectAdvanceFilterCondtion_0').val(),
                    value_text: $('#lead_filter_value_0').val(),
                    value_source_type: $('#lead_filter_source_type_value_0').val(),
                    value_select: $('#lead_filter_select_value_0').val(),
                    value_multi_select: $('#lead_filter_select_value_multi_0').val(),
                    value_date: $('#lead_filter_date_picker_value_0').val(),
                    value_from_date: $('#lead_filter_from_date_picker_value_0').val(),
                    value_to_date: $('#lead_filter_to_date_picker_value_0').val(),
                });

                $('#advanceFilterRows input[name="multi_filter_loop"]').each(function(ind) {
                    let filtValId = $(this).attr("filt_id");
                    tempadvanceFilterList.push({
                        clause: $('#selectAdvanceFilterClause_' + filtValId).val(),
                        column: $('#selectAdvanceFilterColumn_' + filtValId).val(),
                        condtion: $('#selectAdvanceFilterCondtion_' + filtValId).val(),
                        value_text: $('#lead_filter_value_' + filtValId).val(),
                        value_source_type: $('#lead_filter_source_type_value_' + filtValId).val(),
                        value_select: $('#lead_filter_select_value_' + filtValId).val(),
                        value_multi_select: $('#lead_filter_select_value_multi_' + filtValId).val(),
                        value_date: $('#lead_filter_date_picker_value_' + filtValId).val(),
                        value_from_date: $('#lead_filter_from_date_picker_value_' + filtValId).val(),
                        value_to_date: $('#lead_filter_to_date_picker_value_' + filtValId).val(),
                    });
                });
            }

            advanceFilterList = JSON.stringify(tempadvanceFilterList)
            table.ajax.reload();
        }

        function OpenClaimRewardModal(id) {

            $('#modalRewardPoint').modal('show');

            var RewardPointTable = $('#RewardPoint').DataTable({
                "aoColumnDefs": [{
                    "bSortable": false,
                    "aTargets": [0, 1, 2, 3]
                }, ],
                "sDom": "lrtip",
                "bInfo": false,
                "order": [
                    [0, 'desc']
                ],
                "processing": true,
                "serverSide": true,
                "bDestroy": true,
                "pageLength": 10,
                "ajax": {
                    "url": ajaxURLPointAjax,
                    "type": "POST",
                    "data": {
                        "_token": csrfToken,
                        "lead_id": function() {
                            return id;
                        },
                        "arc_ele": function() {
                            return 1;
                        }
                    }
                },
                "aoColumns": [{
                        "mData": "bill_attached",
                        "sWidth": "20%",
                    },
                    {
                        "mData": "bill_amount",
                        "sWidth": "20%",
                    },
                    {
                        "mData": "point",
                        "sWidth": "20%",
                    },
                    {
                        "mData": "query",
                        "sWidth": "10%",
                    },
                    {
                        "mData": "lapsed",
                        "sWidth": "10%",
                    },
                    {
                        "mData": "action",
                        "sWidth": "10%",
                    },
                    {
                        "mData": "hod_approved",
                        "sWidth": "10%",
                    }
                ],
            });
        }

        function LeadAndDealCount(leadCount, dealCount) {
            total_count = parseInt(leadCount) + parseInt(dealCount);
            $('#arc_lead_and_deal_total_count').text(total_count);
        }

        $(document).ready(function() {
            adjustContainerHeight();
            $(window).on('resize', adjustContainerHeight);
        });

        function adjustContainerHeight() {
            var windowHeight = $(window).height() - 135;
            var windowWidth = $(window).width();
            if (windowWidth <= 1440) {
                $('body').addClass('vertical-collpsed');
            }
            max_height = windowHeight - 180;
            $('#datatable').parent().css('max-height', max_height + 'px');
            $('#datatable').parent().css('height', max_height + 'px');
            $('#home').parent().css('max-height', max_height + 'px');
            $('#custom_height').css('height', windowHeight + 'px');
        }

        function Load_Tooltip_Action() {
            $(".closing-badge").mouseover(function(e) {
                var $tip = $(this).next();
                var $arrow = $(this).next().find(".tip_arrow");

                $tip.css("display", "block");
                $tip.css("left", $(this).position().left - 9 + "px");
                $tip.css("top", $(this).position().top + this.offsetHeight + 14 + "px");
                $arrow.css("top", 0);
                $arrow.css("left", this.offsetWidth * 1 / 2 + 2 + "px");
                $arrow.css({
                    "border-bottom-color": "#bbbefcf0",
                    "margin-top": "-20px",
                    "border-top-color": "transparent"
                });
            });
            $(".closing-badge").mouseleave(function(e) {
                var $tip = $(this).next();
                $tip.css("display", "none");
            });
        }
    </script>
    @include('user_action.action_script')
    @include('architects_new/comman/create_architects_script');
@endsection
