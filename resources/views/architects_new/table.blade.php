@extends('layouts.main')
@section('title', $data['title'])
@section('content')
    <style type="text/css">
        td p {
            max-width: 100%;
            white-space: break-spaces;
            word-break: break-all;
        }

        td {
            vertical-align: middle;
        }

        p .closing-badge {
            border-radius: 0.25rem !important;
            background: rgb(239 242 247);
            padding: 5px 4px;
        }



        div.div_tip {
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

        div.div_tip:hover, {
            display: block;
        }

        .closing-badge:hover+.div_tip {
            display: block !important;
            z-index: 999;
        }

        div.div_tip .tip_arrow{
            position: absolute;
            /*top: 100%;*/
            /*left: 50%;*/
            border: solid transparent;
            height: 0;
            width: 0;
            pointer-events: none;
        }

        div.div_tip .tip_arrow {
            /*border-color: rgba(62, 83, 97, 0);*/
            /*border-top-color: #3e5361;*/
            border-width: 10px;
            /*margin-left: -10px; */
        }
    </style>

    <div class="page-content">
        <div class="container-fluid">

            <div id="table_list">
                {{-- <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">Architects </h4>
                            <div class="page-title-right">
                                @include('../architects_new/comman/btn')
                            </div>
                        </div>
                    </div>
                </div> --}}
                <!-- end page title -->
                <!-- start row -->

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                @include('../architects_new/comman/tab')
                                <table id="datatable" class="table table-striped dt-responsive nowrap w-100">
                                    <thead>
                                        <tr>
                                            <th>Name/Firm Name</th>
                                            <th>City</th>
                                            <th>Points</th>
                                            <th>Won</th>
                                            <th>Lost</th>
                                            <th>Running</th>
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

        </div>
        <!-- container-fluid -->
    </div>
    <!-- End Page-content -->

    @csrf
    @include('architects_new/comman/create_architects_modal');
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
        var ajaxURLSaveUpdate = "{{ route('users.update.save') }}";
        var ajaxURLUserUpdateSeen = "{{ route('users.update.seen') }}";
        var ajaxURLDataDatail = "{{ route('new.architects.get.detail') }}";




        $("#filter_architect_advance").select2({
            minimumResultsForSearch: Infinity
        });


        var seachUserId = "{{ $data['searchUserId'] }}";
        var addView = "{{ $data['addView'] }}";


        var csrfToken = $("[name=_token]").val();
                var architectPageLength = getCookie('architectPageLength') !== undefined ? getCookie('architectPageLength') : 10;
        // if (selectedUserType == 201) {
        let advanceFilterList = [];
        var isAdvanceFilter = 0;

        $('#btnClearAdvanceFilter').on('click', function(event){
            status = $('.userscomman .active').attr('data-id');
            advanceFilterList = [];
            table.ajax.reload(null, false);
        })

        $("#saveAdvanceFilter").on('click', function(event) {

            // advanceFilterListinner = [];
            advanceFilterList = [];
            isAdvanceFilter = 1;
            $("#saveAdvanceFilter").html('<i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i> <span  >Saving...</span>');

            var selectValue = $("#lead_filter_select_value_0");
            var selectmultiValue = $("#lead_filter_select_value_multi_0");
            var selectFromDateValue = $("#lead_filter_from_date_picker_value_0");
            var selectToDateValue = $("#lead_filter_to_date_picker_value_0");
            var selecttextValue = $("#lead_filter_value_0");

            advanceFilterList.push({
                clause: $('#selectAdvanceFilterClause_0').val(),
                column: $('#selectAdvanceFilterColumn_0').val(),
                condtion: $('#selectAdvanceFilterCondtion_0').val(),
                value_text: $('#lead_filter_value_0').val(),
                value_select: $('#lead_filter_select_value_0').val(),
                value_source_type: $('#lead_filter_source_type_value_0').val(),
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
                    value_select: $('#lead_filter_select_value_' + filtValId).val(),
                    value_source_type: $('#lead_filter_source_type_value_' + filtValId).val(),
                    value_multi_select: $('#lead_filter_select_value_multi_' + filtValId).val(),
                    value_date: $('#lead_filter_date_picker_value_' + filtValId).val(),
                    value_from_date: $('#lead_filter_from_date_picker_value_' + filtValId).val(),
                    value_to_date: $('#lead_filter_to_date_picker_value_' + filtValId).val(),
                });
            });

            // advanceFilterList.push(advanceFilterListinner)
            
            table.ajax.reload(null, false);
            ischeckFilter();
        });

        var table = $('#datatable').DataTable({
            scrollX: true,
            "aoColumnDefs": [{
                "bSortable": false,
                "aTargets": [6]
            }],
            "order": [
                [0, 'desc']
            ],
            "processing": true,
            "serverSide": true,
            "pageLength": architectPageLength,
            "ajax": {
                "url": ajaxURL + '?type=' + selectedUserType,
                "type": "POST",
                "data": {
                    "_token": csrfToken,
                    'search_user_id': function() {
                        return seachUserId;
                    },
                    "filter_architect_advance": function() {
                        return $("#filter_architect_advance").val();
                    },
                    'status': function() {
                        return $('#arc_active_status').val();;
                    },
                     'isAdvanceFilter': function(){
                        return isAdvanceFilter;
                    },
                    'AdvanceData': function() {
                        return JSON.stringify(advanceFilterList);
                    },
                }
            },
            "aoColumns": [{
                    "mData": "name"
                },
                {
                    "mData": "city_name"
                },
                {
                    "mData": "points"
                },
                {
                    "mData": "won_lead"
                },
                {
                    "mData": "lost_lead"
                },
                {
                    "mData": "running_lead"
                },
                {
                    "mData": "action"
                },

            ],
            "drawCallback": function() {

                $(".select-category").select2({
                    minimumResultsForSearch: Infinity
                });

                seachUserId = "";

                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {

                    return new bootstrap.Tooltip(tooltipTriggerEl)
                })



            }
        });

        table.on('xhr', function() {
            $("#saveAdvanceFilter").html('<span>Save</span>');
        });
        
        $('#filter_architect_advance').on('change', function() {
            table.ajax.reload();
        });


        $("#datatable").delegate(".select-category", "change", function() {
            var splitId = this.id.split("_");
            var categoryId = $(this).val();

            $.ajax({
                type: 'POST',
                url: ajaxChangeCategory,
                data: {
                    "_token": csrfToken,
                    "architect_id": splitId[1],
                    "category_id": categoryId,
                },
                success: function(resultData) {
                    if (resultData['status'] == 1) {
                        toastr["success"](resultData['msg']);
                    } else {
                        toastr["error"](resultData['msg']);
                    }
                }
            });
        });

        $('#datatable').on('length.dt', function(e, settings, len) {
            setCookie('architectPageLength', len, 100);
        });


        $(document).ready(function() {

            if (addView == 1) {

                $("#addBtnUser").click();


            }

        });

        $(document).ready(function() {
            var options = {
                beforeSubmit: showRequest,
                success: showResponse
            };

            $('#formUser').ajaxForm(options);
           
        });

        function showRequest(formData, jqForm, options) {

            var queryString = $.param(formData);

            $(".save-btn").html("Saving...");
            $(".save-btn").prop("disabled", true);

            $("#btnSaveFinal").html("Saving...");
            $("#btnSaveFinal").prop('disabled', true);
            return true;
        }

        // post-submit callback
        function showResponse(responseText, statusText, xhr, $form) {

            $(".save-btn").html("Save");
            $(".save-btn").prop("disabled", false);
            console.log($form[0]['id']);
            $("#btnSaveFinal").prop('disabled', false);
            $("#btnSaveFinal").html("Save");

            if ($form[0]['id'] == "formUser") {
                if (responseText['status'] == 1) {
                    toastr["success"](responseText['msg']);
                    reloadTable();
                    resetInputForm();
                    // getDataDetail(responseText['user_id']);
                    $("#modalUser").modal('hide');
                    
                }else{
                    $('#phone_no_error_dialog').show();
                    $('#error_text').text(responseText['msg']);
                    
                }

            }
        }
        var scrollTopHeightDataTable = 0;
        var scrollTopHeightModalDetail = 0;

        function openReplyBox(id) {



            $("#reply-box-" + id).show(300);

        }

        function saveUserUpdate(id, updateID) {

            if ($("#user_update_message_" + updateID).summernote('code').trim() != "") {

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
                        "for_user_id": id,
                        "user_update_id": updateID,
                        "message": $("#user_update_message_" + updateID).summernote('code'),

                    },
                    success: function(resultData) {

                        $("#user_update_message").val('');
                        scrollTopHeightDataTable = $('.dataTables_scrollBody').prop('scrollTop');


                        if (resultData['status'] == 1) {

                            toastr["success"](resultData['msg']);
                            getDetail(id);
                            table.ajax.reload(null, false);
                            if (updateID == 0) {
                                var updateSaveBtnLable = "Update"

                            } else {
                                var updateSaveBtnLable = "Reply"
                            }
                            $("#inquiry_update-save-" + updateID).html(updateSaveBtnLable);
                            $("#inquiry_update-save-" + updateID).prop('disabled', false);
                            $("#user_update_message_" + updateID).val('');

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

        function reloadTable() {
            table.ajax.reload();
        }

        function ShowSelectedStatusData(status_id) {
            $('.userscomman .funnel').removeClass('active');
            $('#arc_funnel_' + status_id).addClass('active');
            $('#arc_active_status').val(status_id);
            table.ajax.reload();
        }
    </script>
    @include('architects_new/comman/create_architects_script');
@endsection
