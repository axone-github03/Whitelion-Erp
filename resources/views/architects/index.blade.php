@extends('layouts.main')
@section('title', $data['title'])
@section('content')

    <link href="{{ asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}" rel="stylesheet"
        type="text/css">
    <link href="{{ asset('assets/libs/summernote/summernote.min.css') }}" rel="stylesheet">


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

        .section_user_detail {
            border: 1px solid #ced4da;

        }

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
    </style>



    <div class="page-content">
        <div class="container-fluid">

            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">Architects </h4>
                        @if ($data['type'] == 202)
                            <div class="filter_architect_category_div">
                                <select id="filter_architect_category">
                                    <option value="-1">All</option>
                                    <option value="0">-NOT SELECTED-</option>
                                    @foreach ($data['architect_categories'] as $architectCategory)
                                        <option value="{{ $architectCategory->id }}">{{ $architectCategory->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="page-title-right">



                            @include('../architects/comman/btn')
                        </div>


                    </div>


                </div>
            </div>
            <!-- end page title -->
            <!-- start row -->


            <div class="row">

                <div class="card">
                    <div class="card-body">

                        @include('../architects/comman/tab')
                        <br>
                        <div class="">
                            <table id="datatable"
                                class="table align-middle table-nowrap table-hover table-striped   nowrap w-100">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Name / Type</th>
                                        <th>Email / Phone </th>
                                        <th>Sale Person</th>
                                        @if ($data['type'] == 202)
                                            <th>Points <br> Current</th>
                                            <th>Points <br> Lifetime</th>
                                        @endif
                                        <th>Status</th>
                                        @if ($data['type'] == 202)
                                            <th>Category</th>
                                            <th>Principal Architect</th>
                                        @endif
                                        <th>Created By</th>
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







@endsection('content')
@section('custom-scripts')


    <script src="{{ asset('assets/libs/parsleyjs/parsley.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/form-validation.init.js') }}"></script>
    <script src="{{ asset('assets/js/pages/jquery.form.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
    <script src="{{ asset('assets/libs/summernote/summernote.min.js') }}"></script>



    @include('../architects/comman/modal')
    <script type="text/javascript">
        var selectedUserType = "{{ $data['type'] }}";
        var viewMode = "{{ $data['viewMode'] }}";
        var isSalePerson = "{{ $data['isSalePerson'] }}";
        var ajaxURL = "{{ route('architects.ajax') }}";
        var ajaxChangeCategory = "{{ route('architect.change.category') }}";
        var ajaxURLUserUpdateDetail = "{{ route('users.update.detail') }}";
        var ajaxURLSaveUpdate = "{{ route('users.update.save') }}";
        var ajaxURLUserUpdateSeen = "{{ route('users.update.seen') }}";




        $("#filter_architect_advance").select2({
            minimumResultsForSearch: Infinity

        });


        var seachUserId = "{{ $data['searchUserId'] }}";
        var addView = "{{ $data['addView'] }}";


        var csrfToken = $("[name=_token").val();

        var architectPageLength = getCookie('architectPageLength') !== undefined ? getCookie('architectPageLength') : 10;


        if (selectedUserType == 201) {
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
                        "view_mode": function() {
                            return viewMode;
                        },
                        'search_user_id': function() {
                            return seachUserId;
                        },
                        "filter_architect_advance": function() {
                            return $("#filter_architect_advance").val();
                        },
                    }
                },
                "aoColumns": [{
                        "mData": "id"
                    },
                    {
                        "mData": "name"
                    },
                    {
                        "mData": "email"
                    },
                    {
                        "mData": "sale_person"
                    },
                    {
                        "mData": "status"
                    },
                    {
                        "mData": "created_by"
                    },
                    {
                        "mData": "action"
                    },

                ],
                "drawCallback": function() {

                    seachUserId = "";

                    var tooltipTriggerList = [].slice.call(document.querySelectorAll(
                        '[data-bs-toggle="tooltip"]'))
                    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {

                        return new bootstrap.Tooltip(tooltipTriggerEl)
                    })



                }
            });

        } else if (selectedUserType == 202) {

            $("#filter_architect_category").select2();

            var table = $('#datatable').DataTable({
                scrollX: true,
                "aoColumnDefs": [{
                        "bSortable": false,
                        "aTargets": [10]
                    },
                    @if (isMarketingUser() == 1)
                                            {
                                                "targets": [4],
                                                "visible": false,
                                                "searchable": true
                                            },
                                            {
                                                "targets": [5],
                                                "visible": false,
                                                "searchable": true
                                            },
                                            {
                                                "targets": [7],
                                                "visible": false,
                                                "searchable": true
                                            }
                    @endif
                ],
                "order": [
                    [0, 'desc']
                ],
                "processing": true,
                "serverSide": true,
                "pageLength": architectPageLength,
                "ajax": {
                    "url": ajaxURL + '?type=' + selectedUserType + "&search_user_id=" + seachUserId,
                    "type": "POST",
                    "data": {
                        "_token": csrfToken,
                        'category_id': function() {
                            return $("#filter_architect_category").val();
                        },
                        'search_user_id': function() {
                            return seachUserId;
                        },
                        "view_mode": function() {
                            return viewMode;
                        },
                        "filter_architect_advance": function() {
                            return $("#filter_architect_advance").val();
                        },
                    }
                },
                "aoColumns": [{
                        "mData": "id"
                    },
                    {
                        "mData": "name"
                    },
                    {
                        "mData": "email"
                    },
                    {
                        "mData": "sale_person"
                    },
                    {
                        "mData": "total_point_current"
                    },
                    {
                        "mData": "total_point"
                    },
                    {
                        "mData": "status"
                    },
                    {
                        "mData": "category"
                    },
                    {
                        "mData": "principal_architect_name"
                    },
                    {
                        "mData": "created_by"
                    },
                    {
                        "mData": "action"
                    },

                ],
                "drawCallback": function() {

                    seachUserId = "";


                    if (viewMode == 1) {

                        $(".select-category").select2({
                            minimumResultsForSearch: Infinity,

                        });

                        $('.select-category').select2("enable", false);







                    } else {
                        $(".select-category").select2({
                            minimumResultsForSearch: Infinity

                        });

                    }

                    var tooltipTriggerList = [].slice.call(document.querySelectorAll(
                        '[data-bs-toggle="tooltip"]'))
                    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {

                        return new bootstrap.Tooltip(tooltipTriggerEl)
                    })

                }
            });



            $('#filter_architect_category').on('change', function() {
                table.ajax.reload();


            });

        }

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

        var scrollTopHeightDataTable = 0;
        var scrollTopHeightModalDetail = 0;

        function openReplyBox(id) {



            $("#reply-box-" + id).show(300);

        }

        function getDetail(userId) {

            $("#modalDetail").modal('show');
            $("#detail_inquiry_id").val(userId);
            OpenuserId = userId;


            var UIType = 'user_update';
            var previousUIType = '';
            $("#" + UIType).html(
                '<p class="mb-0"><div class="col-md-12 text-center loadingcls"><button type="button" class="btn btn-light waves-effect"><i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading</button></div></p>'
                );





            $.ajax({
                type: 'GET',
                url: ajaxURLUserUpdateDetail + "?user_id=" + userId + '&ui_type=' + UIType,
                success: function(resultData) {

                    previousUIType = UIType;

                    $("#modalDetail .loadingcls").hide();
                    if (resultData['status'] == 1) {

                        $("#" + UIType).html(resultData['view']);

                        $('.user_update_message').summernote({
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

        $("body").delegate(".seen-btn", "mouseenter", function() {


            var UserUpdateId = this.id;
            var piecesOfUserUpdateId = UserUpdateId.split('-');




            $("#" + UserUpdateId).tooltip('dispose').attr('title',
                "<i class='bx bx-loader bx-spin font-size-16 align-middle me-2'></i>");
            $("#" + UserUpdateId).tooltip('show');




            $.ajax({
                type: 'GET',
                url: ajaxURLUserUpdateSeen + "?update_id=" + piecesOfUserUpdateId[2],
                success: function(resultData) {


                    if (resultData['status'] == 1) {

                        $("#" + UserUpdateId).tooltip('dispose').attr('title', resultData['data']);
                        $("#" + UserUpdateId).tooltip('show');

                    }
                }
            });



        });
    </script>
    @include('../users/comman/script')
@endsection
