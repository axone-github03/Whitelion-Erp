@extends('layouts.main')
@section('title', $data['title'])
@section('content')
<link href="{{ asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}" rel="stylesheet"
    type="text/css">
<link href="{{ asset('assets/libs/summernote/summernote.min.css') }}" rel="stylesheet">


<style type="text/css">
    .section_channel_partner {

    border: 1px solid #ced4da;


    }

    .section_user_detail {
    border: 1px solid #ced4da;

    }

    .nav-pills .nav-link {
    border: 1px solid gainsboro;
    }
    
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
                            <input type="hidden" id="hidden_channel_partner_type" value="101">
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
    <!-- container-fluid -->
    </div>
    <!-- End Page-content -->


    
    
    

    
    @endsection('content')
    @section('custom-scripts')
    
    
    <script src="{{ asset('assets/libs/parsleyjs/parsley.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/form-validation.init.js') }}"></script>
    <script src="{{ asset('assets/js/pages/jquery.form.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
    <script src="{{ asset('assets/libs/summernote/summernote.min.js') }}"></script>
    
    @include('../channel_partners_new/comman/modal')
    @include('user_action.action_modal')

    <script type="text/javascript">
        var selectedUserType = {{ $data['type'] }};
        var csrfToken = $("[name=_token").val();
        var isSalePerson = {{ $data['isSalePerson'] }};
        var addView = {{ $data['addView'] }};   
        var viewLeadId = "{{ $data['id'] }}";
        var ajaxURLDataList = "{{ route('new.architects.detail.list') }}";
        var ajaxURLDataListAjax = "{{ route('new.channel.partners.detail.list.ajax') }}";
        var ajaxURLUserDetail = "{{ route('new.channel.partners.get.detail') }}";
        var ajaxURLUpdateSave = "{{ route('user.action.update.save') }}";
        var viewMode = "{{ $data['viewMode'] }}";

        var channelPartnerPageLength = getCookie('usersPageLength') !== undefined ? getCookie('usersPageLength') : 10;
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
                    "view_mode": function() {
                        return viewMode;
                    },
                    "type": function() {
                        return $('#hidden_channel_partner_type').val();
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
        
        $('#datatable').on('length.dt', function(e, settings, len) {

            setCookie('channelPartnerPageLength', len, 100);


        });

        function getDataDetail(id) {
            isdetailload = 0;
            $("#lead_" + id).parent().parent().addClass('active_lead');
            $.ajax({
            type: 'GET',
            url: ajaxURLUserDetail + "?id=" + id,
            success: function(resultData) {
                if (resultData['status'] == 1) {
                        var data = resultData['data']['user'];
                        var tag_data = resultData['data']['user']['tag'];
                        $("#lead_detail").html(resultData['view']);
                        
                        $('#arc_lifetime_point').text(data['lifetime_point']);
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


        $(document).ready(function() {

            if (addView == 1) {

                $("#addBtnUser").click();

            }
            var isdetailload = 0;

        });

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

        function LeadAndDealCount(leadCount, dealCount) {
            total_count = parseInt(leadCount) + parseInt(dealCount);
            $('#arc_lead_and_deal_total_count').text(total_count);
        }

        $('#addBtnDiscount').click(function(){
            $("#cptdiscountSync").html("Saved");
            $("#modalChannelPartenerTypeDiscount").modal('show');
        });

        function changeChannelPartnerType(type) {
            $('.userscomman .funnel').removeClass('active');
            $('#channel_partner_type_' + type).addClass('active');
            $('#hidden_channel_partner_type').val(type);
            table.ajax.reload();

            var exportLink = document.getElementById('exportLink');
            var exportURL = "{{ route('new.channel.partners.export') }}?type=" + type;
            exportLink.setAttribute('href', exportURL);
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
    @include('channel_partners_new/comman/create_channels_script');
@endsection
