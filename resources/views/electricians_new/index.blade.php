@extends('layouts.main')
@section('title', $data['title'])
@section('content')

    <link href="{{ asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css') }}" rel="stylesheet" />

    <link href="{{ asset('assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}" rel="stylesheet"
        type="text/css">
    <style type="text/css">
        .user-comments-icon {
            font-size: 20px;
        }

        .user-update-badge {
            color: white;
            background: #495057;
            border: 0.5px solid white;
            padding: 2px 8px;
            font-size: 12px;
        }


        .note-editor.note-airframe .note-statusbar.locked .note-resizebar,
        .note-editor.note-frame .note-statusbar.locked .note-resizebar {
            display: none;
        }

        .reply-box {
            display: none;
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


        .section_user_extra_detail {
            border: 1px solid #ced4da;
        }

        /* .section_user_detail {
                                                border: 1px solid #ced4da;
                
                                            } */

        .nav-pills .nav-link {
            border: 1px solid gainsboro;
        }

        .div-start-line {
            border-top: 1px solid #ced4da;
            padding-top: 12px;
        }

        .filter_architect_category_div {
            width: 200px;
        }

        .stats {
            height: 30px;
            width: 15%;
            float: right;
            margin-right: 3%;
            position: relative;
            text-align: center;
            text-indent: 25px;
            line-height: 30px;
            font-size: 14px;
            background: #2d5171;
            color: #ffffff;
        }

        .stats:first-child {
            margin-right: 3.99%;
        }

        .stats:before,
        .stats:after {
            position: absolute;
            content: '';
            width: 0px;
            height: 0;
            top: 50%;
            margin: -15px 0 0;
            border: 15px solid transparent;
            border-left-color: #f8f8fb;
        }

        .stats:after {
            left: 0%;
        }

        .stats:before {
            left: 100%;
        }

        .stats:before {
            border-left-color: #2d5171;
        }

        @media (min-width: 1200px) {
            .chat-leftsidebar {
                min-width: 330px;
            }
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

        .chat-list li a {
            padding: 8px 13px;
            border-radius: 0px;
        }

        .chat-list li.active a {
            background-color: rgb(141, 226, 255);
            border-color: transparent;
            -webkit-box-shadow: 0 0.75rem 1.5rem rgba(18, 38, 63, .03);
            box-shadow: 0 0.75rem 1.5rem rgba(18, 38, 63, .03);
        }

        .chat-list li a:hover {
            background-color: rgb(141, 226, 255);
            border-color: transparent;
            -webkit-box-shadow: 0 0.75rem 1.5rem rgba(18, 38, 63, .03);
            box-shadow: 0 0.75rem 1.5rem rgba(18, 38, 63, .03);
        }
    </style>

    <style>
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

        .lead-detail .form-control,
        .input-group-text {
            /* padding: 2px 11px; */
            border: none;
        }

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

        .chat-list li a {
            padding: 8px 13px;
            border-radius: 0px;
        }

        .chat-list li.active a {
            background-color: rgb(141, 226, 255);
            border-color: transparent;
            -webkit-box-shadow: 0 0.75rem 1.5rem rgba(18, 38, 63, .03);
            box-shadow: 0 0.75rem 1.5rem rgba(18, 38, 63, .03);
        }

        .chat-list li a:hover {
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
            background-color: #11dc11;
            color: #ffffff !important
        }

        .next-status-active-class {
            background-color: #556ee6;
            color: #ffffff !important
        }

        /* .border-bottom {
                                    border-bottom: 1px solid #d1d1d1 !important;
                                } */

        .card-body {
            -webkit-box-flex: 1;
            -ms-flex: 1 1 auto;
            flex: 1 1 auto;
            padding: 1.25rem 1.25rem rem;
            padding-top: 2px !important;
        }

        .border-none {
            border: none !important;
        }

        #modalCall .select2-selection.select2-selection--single {
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
        }

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

        #datatable_wrapper:nth-child(3) {
            position: fixed;
        }

        .active_lead {
            background-color: rgb(141, 226, 255);
            border-color: transparent;
            -webkit-box-shadow: 0 0.75rem 1.5rem rgba(18, 38, 63, .03);
            box-shadow: 0 0.75rem 1.5rem rgba(18, 38, 63, .03);
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



        <!-- end row -->
    </div>
    <!-- container-fluid -->
    </div>
    <!-- End Page-content -->
    @include('user_action/action_modal')
    @include('electricians_new/comman/create_electrician_modal')







@endsection('content')
@section('custom-scripts')


    <script src="{{ asset('assets/libs/parsleyjs/parsley.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/form-validation.init.js') }}"></script>
    <script src="{{ asset('assets/js/pages/jquery.form.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>

    @include('../electricians_new/comman/modal')
    <script type="text/javascript">
        var selectedUserType = "{{ $data['type'] }}";
        var viewMode = "{{ $data['viewMode'] }}";
        var isSalePerson = "{{ $data['isSalePerson'] }}";
        var ajaxURL = "{{ route('new.electricians.ajax') }}";
        var csrfToken = $("[name=_token").val();
        var addView = "{{ $data['addView'] }}";
        var seachUserId = "{{ $data['searchUserId'] }}";
        var electricianPageLength = getCookie('electricianPageLength') !== undefined ? getCookie('electricianPageLength') :
            10;

        var ajaxURLDataDatail = "{{ route('new.electricians.get.detail') }}";
        var ajaxURLDataList = "{{ route('new.electricians.detail.list') }}";
        var ajaxURLUpdateSave = "{{ route('user.action.update.save') }}";
        var ajaxURLDataListAjax = "{{ route('new.electricians.detail.list.ajax') }}";
        var ajaxURLPointAjax = "{{ route('crm.lead.point.ajax') }}";

        var viewLeadId = "{{ $data['id'] }}";

        $("#filter_electrician_advance").select2({
            minimumResultsForSearch: Infinity

        });

        // var table = $('#datatable').DataTable({
        //     "aoColumnDefs": [{
        //         "bSortable": false,
        //         "aTargets": [7]
        //     }],
        //     "order": [
        //         [0, 'desc']
        //     ],
        //     "processing": true,
        //     "serverSide": true,
        //     "pageLength": electricianPageLength,
        //     "ajax": {
        //         "url": ajaxURL + '?type=' + selectedUserType,
        //         "type": "POST",
        //         "data": {
        //             "_token": csrfToken,
        //             "view_mode": function() {
        //                 return viewMode;
        //             },
        //             'search_user_id': function() {
        //                 return seachUserId;
        //             },
        //             "filter_electrician_advance": function() {
        //                 return $("#filter_electrician_advance").val();
        //             },

        //         }
        //     },
        //     "aoColumns": [{
        //             "mData": "name"
        //         },
        //         {
        //             "mData": "call"
        //         },
        //         {
        //             "mData": "type"
        //         },
        //         {
        //             "mData": "status"
        //         },
        //         {
        //             "mData": "joining_date"
        //         },
        //         {
        //             "mData": "account_owner"
        //         },
        //         {
        //             "mData": "created_by"
        //         },
        //         {
        //             "mData": "action"
        //         },
        //     ],
        //     "drawCallback": function() {

        //         seachUserId = "";

        //         var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        //         var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {

        //             return new bootstrap.Tooltip(tooltipTriggerEl)
        //         })

        //     }
        // });


        // $('#filter_electrician_advance').on('change', function() {
        //     table.ajax.reload();
        // });

        $('#datatable').on('length.dt', function(e, settings, len) {
            setCookie('electricianPageLength', len, 100);
        });

        $(document).ready(function() {
            if (addView == 1) {
                $("#addBtnElectricianUser").click();
            }

            var isdetailload = 0;
            // getList("", );
            // reloadEleList($("#arc_active_status").val());
            // getDataDetail({{ $data['id'] }})
        });

        function getDataDetail(id) {

            isdetailload = 0;
            $("#lead_" + id).parent().parent().addClass('active_lead');
            $.ajax({
                type: 'GET',
                url: ajaxURLDataDatail + "?id=" + id,
                success: function(resultData) {
                    if (resultData['status'] == 1) {
                        // $(".lead_li.active").removeClass('active');
                        // $("#lead_" + id).addClass('active');
                        $("#lead_detail").html(resultData['view']);
                        
                        $('#ele_lifetime_point').text(resultData['data']['user']['lifetime_point_lable'])
                        $('#ele_redeemed_point').text(resultData['data']['user']['redeemed_point'])
                        $('#ele_available_point').text(resultData['data']['user']['available_point'])

                        var newOption = new Option(resultData['data']['user']['sale_person']['text'], resultData['data']['user']['sale_person']['id'], false, false);
                        $('#user_owner').append(newOption).trigger('change');
                        $("#user_owner").val("" + resultData['data']['user']['sale_person']['id'] + "");
                        $('#user_owner').trigger('change');

                        var data = resultData['data']['user']['tag'];
                        if (data != null) {
                            if (data.length > 0) {
                                $("#user_tag_id").empty().trigger('change');
                                var selectedSalePersons = [];
                                for (var i = 0; i < data.length; i++) {
                                    selectedSalePersons.push('' + data[i]['id'] + '');
                                    var newOption = new Option(data[i]['text'], data[i]['id'], false, false);
                                    $('#user_tag_id').append(newOption).trigger('change');
                                }
                                $("#user_tag_id").val(selectedSalePersons).change();
                            }
                        }

                        $(".lead_li").parent().parent().removeClass('active_lead');
                        $("#lead_" + id).parent().parent().addClass('active_lead');

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
            reloadEleList(status_id);
            // getList($("#input_search").val(), status_id)
        }

        // $("#input_search").keyup(function() {
        //     getList($(this).val(), $("#arc_active_status").val());
        // });

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


        $("#saveAdvanceFilter").on('click', function(event) {
            var isValid = true;

            var selectColumn = $("#selectAdvanceFilterColumn_0");
            if (!selectColumn.val() || selectColumn.val() == "0") {
                isValid = false;
            }

            var selectCondition = $("#selectAdvanceFilterCondtion_0");
            if (!selectCondition.val() || selectCondition.val() == "0") {
                isValid = false;
            }

            var selectValue = $("#lead_filter_select_value_0");
            if (!selectValue.val() || selectValue.val().length === 0) {
                isValid = false;
            }

            if (isValid) {
                status = $('.userscomman .active').attr('data-id');
                $("#saveAdvanceFilter").html(
                    '<i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i> <span  >Saving...</span>'
                );
                reloadEleList(0, 1);
                ischeckFilter();
            };

        });

        // $("#saveAdvanceFilter").on('click', function(event) {
        //     status = $('.userscomman .active').attr('data-id');
        //     $("#saveAdvanceFilter").html(
        //         '<i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i> <span  >Saving...</span>');
        //     reloadEleList(0, 1);
        //     // getList("", status, 1)

        // });

        $('#btnClearAdvanceFilter').on('click', function(event) {
            status = $('.userscomman .active').attr('data-id');
            reloadEleList(status, 0);
            // getList("", status, 0)
        })


        $('#hidden_status').val($("#arc_active_status").val());
        let advanceFilterList = '';
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
                '<div><i id="list_data_loader" class="bx bx-loader bx-spin font-size-16 align-middle me-2" style=""></i><b>{{ $data['title'] }}</b></div>'
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
            $('.lead_status_filter_remove').removeClass("next-status-active-class");
            $('.lead_status_filter_' + status).addClass("next-status-active-class");

            $('#deal_count').html(responseData['count']);


            if (viewLeadId == null || viewLeadId == "" || viewLeadId == undefined || viewLeadId == 0) {
                getDataDetail(responseData['FirstPageLeadId']);
            } else {
                getDataDetail(viewLeadId);
            }

            $('#list_data_loader').hide();

        });

        function reloadEleList(status = 0, isAdvanceFilter = 0) {

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
            $('#ele_lead_and_deal_total_count').text(total_count);
        }

        $(document).ready(function() {
            adjustContainerHeight();
            $(window).on('resize', adjustContainerHeight);
        });

        function adjustContainerHeight() {
            var windowHeight = $(window).height() - 165;
            var windowWidth = $(window).width();
            if(windowWidth <= 1440){
                $('body').addClass('vertical-collpsed');
            }
            max_height = windowHeight - 180;
            $('#datatable').parent().css('max-height', max_height + 'px');
            $('#datatable').parent().css('height', max_height + 'px');
            $('#home').parent().css('max-height', max_height + 'px');
            $('#custom_height').css('height', windowHeight + 'px');
        }
    </script>
    @include('../users/comman/script')
    @include('user_action.action_script')
    @include('electricians_new/comman/create_electrician_script')
@endsection
