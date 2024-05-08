@extends('layouts.main')
@section('title', $data['title'])
@section('content')


    <style type="text/css">
        td p {
            max-width: 100%;
            white-space: break-spaces;
            word-break: break-all;
        }

        .badge-soft-running {
            color: #5e5e5e;
            background-color: #ffeb007a;
        }

        .badge-soft-change-request {
            color: #ff0000;
            background-color: #ff00001f;
        }

        .badge-soft-confirm {
            color: #ffffff;
            background-color: #418107;
        }

        .badge-soft-sent-quotation {
            color: #ffffff;
            background-color: #ff7c7c;
        }

        .switch {
            margin-top: 15px;
            position: relative;
            display: inline-block;
            width: 34px;
            height: 17px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ff000047;
            -webkit-transition: .4s;
            transition: .4s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 15px;
            width: 15px;
            left: 1px;
            bottom: 1px;
            background-color: #ffffff;
            -webkit-transition: .4s;
            transition: .4s;
        }


        input:checked+.slider {
            background-color: #07cd1266;
        }

        input:focus+.slider {
            box-shadow: 0 0 1px #2196F3;
        }

        input:checked+.slider:before {
            -webkit-transform: translateX(17px);
            -ms-transform: translateX(17px);
            transform: translateX(17px);
        }

        /* Rounded sliders */
        .slider.round {
            border-radius: 34px;
        }

        .slider.round:before {
            border-radius: 50%;
        }

        .custom-table {
            margin-bottom: 0;
            /* Adjust margin as needed */
        }

        .custom-table td {
            padding: 8px;
            /* Adjust padding as needed */
        }

        .form-control {
            border-radius: 10px;
            /* Adjust the border-radius value as needed */
        }
        .select2-container--default .select2-selection--single {
            border-radius: 10px !important;
        }

        .icon-small {
            font-size: 1.5em;
            /* Adjust the size as needed */
        }

        .btn:active {
            background-color: white;
            /* Add any other styles you want for the selected button */
        }

        .item-description {
            margin-top: 10%;
            /* Adjust the value as needed */
        }

        .custom-table td {
            padding: 2px;
            /* Adjust the value as needed */
        }

        .custom-table {
            border-spacing: 0;
        }

        .d-flex.flex-wrap.py-2 {
            display: flex;
            flex-wrap: wrap;
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }

        .btn.btn-sm.waves-effect.waves-light {
            margin-right: 10px;
            margin-bottom: 10px;
            font-size: 14px;
            border-radius: 5px;
            color: black;
            transition: background-color 0.3s, color 0.3s;
        }

        .btn.btn-sm.waves-effect.waves-light:active,
        .btn.btn-sm.waves-effect.waves-light:focus {
            background-color: white;
            color: blue;
        }

        .btn.btn-sm.waves-effect.waves-light:focus~.btn.btn-sm.waves-effect.waves-light,
        .btn.btn-sm.waves-effect.waves-light:active~.btn.btn-sm.waves-effect.waves-light {
            background-color: white;
            color: black;
        }

        .red-icon {
            color: #ff0000
        }

        .active_room {
            border: none !important;
            color: #2196F3 !important;
            background-color: white !important;
            box-shadow: 0 0.1rem 0.5rem rgb(18 50 63 / 35%) !important;
        }
    </style>
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">Quotation Item Change Request</h4>
                        <input type="hidden" name="quot_id" id="quot_id" value="{{ $data['quot_id'] }}">
                        <input type="hidden" name="quotgroup_id" id="quotgroup_id" value="{{ $data['quotgroup_id'] }}">
                    </div>
                </div>
            </div>

            <div class="d-flex flex-wrap py-2" id="RoomList">
                    
            </div>

            <div class="w-100 user-chat py-1">       
                <div class="card lead-detail" style="border-radius: 10px;">
                    <div class="card-body" id="lead_detail">
                        <div id="RoomWiseBoardList">

                        </div>
                    </div>
                </div>       
            </div>
        </div>
        <!-- container-fluid -->
    </div>


    @csrf


@endsection('content')
@section('custom-scripts')

    <script src="{{ asset('assets/libs/parsleyjs/parsley.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/form-validation.init.js') }}"></script>
    <script src="{{ asset('assets/js/pages/jquery.form.js') }}"></script>

    <script type="text/javascript">
        var ajaxURLRoomList = '{{ route('quotation.room.list') }}'
        var ajaxURLRoomWiseBoardList = '{{ route('quotation.room.wise.board.list') }}'
        var ajaxURLRoomAndBoardSave = '{{ route('quotation.room.and.board.save') }}'

        var csrfToken = $("[name=_token").val();

        function boardDetail(room_no = "1", board_count) {
            $('.room_button').removeClass('active_room');
            $.ajax({
                type: 'POST',
                url: ajaxURLRoomWiseBoardList,
                data: {
                    "_token": csrfToken,
                    "quot_id": $('#quot_id').val(),
                    "quotgroup_id": $('#quotgroup_id').val(),
                    "room_id": room_no,
                },
                success: function(resultData) {
                    $('#RoomWiseBoardList').html(resultData['view']);
                    $('#room_'+board_count).addClass('active_room');
                }
            });
        }

        $(document).ready(function() {
            
            $.ajax({
                type: 'POST',
                url: ajaxURLRoomList,
                data: {
                    "_token": csrfToken,
                    "quot_id": $('#quot_id').val(),
                    "quotgroup_id": $('#quotgroup_id').val(),
                },
                success: function(resultData) {
                    $('#RoomList').html(resultData['view']);
                    boardDetail("1", "1");
                }
            });
        });

        var plate_filter_count = 1;
        function addNewPlate(room_no, board_no, item_type, board_range){
            var addAdvanceFilterRows = '<tr class="PlateNewItem_'+room_no+'_'+board_no+'" data-id="'+plate_filter_count+'">';
            addAdvanceFilterRows += '<td class="w-75">';
            addAdvanceFilterRows += '<select class="form-control select2-ajax" id="plate_'+room_no+'_'+board_no+'_'+plate_filter_count+'" aria-label="Disabled select example" name="plate_'+room_no+'_'+board_no+'_'+plate_filter_count+'" required></select>';
            addAdvanceFilterRows += '</td>';
            addAdvanceFilterRows += '<td class="w-25">';
            addAdvanceFilterRows += '<input type="number" class="form-control" id="plate_qty_'+room_no+'_'+board_no+'_'+plate_filter_count+'" name="plate_qty_'+room_no+'_'+board_no+'_'+plate_filter_count+'">';
            addAdvanceFilterRows += '<i class="bx bx-x-circle remove"></i>';
            addAdvanceFilterRows += '</td>';
            addAdvanceFilterRows += '</tr>';

            $('#PlateTbody_'+room_no+'_'+board_no).append(addAdvanceFilterRows)
            var new_plate_filter_count = plate_filter_count;

            $('#plate_'+room_no+'_'+board_no+'_'+new_plate_filter_count+'').select2({
                ajax: {
                    url: ajaxURLSearchPlate,
                    dataType: 'json',
                    delay: 0,
                    data: function(params) {
                        return {
                            q: params.term, // search term
                            page: params.page,
                            "quot_id" : function() {
                                return $('#quot_id').val();
                            },
                            "quotgroup_id" : function() {
                                return $('#quotgroup_id').val();
                            },
                            "type" : function() {
                                return item_type;
                            },
                            "range_subgroup" : function() {
                                return board_range;
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
                placeholder: 'Search for plate',
                dropdownParent: $('#PlateTbody_'+room_no+'_'+board_no+'')
            });

            plate_filter_count++;

            $('#PlateTbody_'+room_no+'_'+board_no).on('click', '.remove', function(e) {
                e.preventDefault();
                $(this).parent().parent().remove();
                if (plate_filter_count == 1) {
                    plate_filter_count = 1;
                } else {
                    plate_filter_count--;
                }
            });
        }

        var acc_filter_count = 1;
        function addNewAccessories(room_no, board_no, item_type, board_range){
            var addAdvanceFilterRows = '<tr class="AccessoriesNewItem_'+room_no+'_'+board_no+'" data-id="'+acc_filter_count+'">';
            addAdvanceFilterRows += '<td class="w-75">';
            addAdvanceFilterRows += '<select class="form-control select2-ajax" id="accessories_'+room_no+'_'+board_no+'_'+acc_filter_count+'" aria-label="Disabled select example" name="accessories_'+room_no+'_'+board_no+'_'+acc_filter_count+'" required></select>';
            addAdvanceFilterRows += '</td>';
            addAdvanceFilterRows += '<td class="w-25">';
            addAdvanceFilterRows += '<input type="number" class="form-control" id="accessories_qty_'+room_no+'_'+board_no+'_'+acc_filter_count+'" name="accessories_qty_'+room_no+'_'+board_no+'_'+acc_filter_count+'">';
            addAdvanceFilterRows += '<i class="bx bx-x-circle remove"></i>';
            addAdvanceFilterRows += '</td>';
            addAdvanceFilterRows += '</tr>';

            $('#AccessoriesTbody_'+room_no+'_'+board_no).append(addAdvanceFilterRows)
            var new_acc_filter_count = acc_filter_count;

            $('#accessories_'+room_no+'_'+board_no+'_'+new_acc_filter_count+'').select2({
                ajax: {
                    url: ajaxURLSearchAccessories,
                    dataType: 'json',
                    delay: 0,
                    data: function(params) {
                        return {
                            q: params.term, // search term
                            page: params.page,
                            "quot_id" : function() {
                                return $('#quot_id').val();
                            },
                            "quotgroup_id" : function() {
                                return $('#quotgroup_id').val();
                            },
                            "sub_group" : function() {
                                return $('input[name="subgroup_checkbox_'+room_no+'_'+board_no+'"]:checked').val();
                            },
                            "type" : function() {
                                return item_type;
                            },
                            "range_subgroup" : function() {
                                return board_range;
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
                placeholder: 'Search for accessories',
                dropdownParent: $('#AccessoriesTbody_'+room_no+'_'+board_no+'')
            });

            acc_filter_count++;

            $('#AccessoriesTbody_'+room_no+'_'+board_no).on('click', '.remove', function(e) {
                e.preventDefault();
                $(this).parent().parent().remove();
                if (acc_filter_count == 1) {
                    acc_filter_count = 1;
                } else {
                    acc_filter_count--;
                }
            });
        }

        var wl_model_filter_count = 1;
        function addNewWlModel(room_no, board_no, item_type, board_range){
            var addAdvanceFilterRows = '<tr class="WhitelioModeNewItem_'+room_no+'_'+board_no+'" data-id="'+wl_model_filter_count+'">';
            addAdvanceFilterRows += '<td class="w-75">';
            addAdvanceFilterRows += '<select class="form-control select2-ajax" id="whiteliom_model_'+room_no+'_'+board_no+'_'+wl_model_filter_count+'" aria-label="Disabled select example" name="whiteliom_model_'+room_no+'_'+board_no+'_'+wl_model_filter_count+'" required></select>';
            addAdvanceFilterRows += '</td>';
            addAdvanceFilterRows += '<td class="w-25">';
            addAdvanceFilterRows += '<input type="number" class="form-control" id="whiteliom_model_qty_'+room_no+'_'+board_no+'_'+wl_model_filter_count+'" name="whiteliom_model_qty_'+room_no+'_'+board_no+'_'+wl_model_filter_count+'">';
            addAdvanceFilterRows += '<i class="bx bx-x-circle remove"></i>';
            addAdvanceFilterRows += '</td>';
            addAdvanceFilterRows += '</tr>';

            $('#WhitelioModelTbody_'+room_no+'_'+board_no).append(addAdvanceFilterRows)
            var new_wl_model_filter_count = wl_model_filter_count;

            $('#whiteliom_model_'+room_no+'_'+board_no+'_'+new_wl_model_filter_count+'').select2({
                ajax: {
                    url: ajaxURLSearchWhitelionModel,
                    dataType: 'json',
                    delay: 0,
                    data: function(params) {
                        return {
                            q: params.term, // search term
                            page: params.page,
                            "quot_id" : function() {
                                return $('#quot_id').val();
                            },
                            "quotgroup_id" : function() {
                                return $('#quotgroup_id').val();
                            },
                            "sub_group" : function() {
                                return $('input[name="subgroup_checkbox_'+room_no+'_'+board_no+'"]:checked').val();
                            },
                            "type" : function() {
                                return item_type;
                            },
                            "range_subgroup" : function() {
                                return board_range;
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
                placeholder: 'Search for whitelion model',
                dropdownParent: $('#WhitelioModelTbody_'+room_no+'_'+board_no+'')
            });

            wl_model_filter_count++;

            $('#WhitelioModelTbody_'+room_no+'_'+board_no).on('click', '.remove', function(e) {
                e.preventDefault();
                $(this).parent().parent().remove();
                if (wl_model_filter_count == 1) {
                    wl_model_filter_count = 1;
                } else {
                    wl_model_filter_count--;
                }
            });
        }

        var addon_filter_count = 1;
        function addNewAddon(room_no, board_no, item_type, board_range){
            var addAdvanceFilterRows = '<tr class="AddOnNewItem_'+room_no+'_'+board_no+'" data-id="'+addon_filter_count+'">';
            addAdvanceFilterRows += '<td class="w-75" >';
            addAdvanceFilterRows += '<select class="form-control select2-ajax" id="addon_'+room_no+'_'+board_no+'_'+addon_filter_count+'" aria-label="Disabled select example" name="addon_'+room_no+'_'+board_no+'_'+addon_filter_count+'" required></select>';
            addAdvanceFilterRows += '</td>';
            addAdvanceFilterRows += '<td class="w-25">';
            addAdvanceFilterRows += '<input type="number" class="form-control" id="addon_qty_'+room_no+'_'+board_no+'_'+addon_filter_count+'" name="addon_qty_'+room_no+'_'+board_no+'_'+addon_filter_count+'">';
            addAdvanceFilterRows += '<i class="bx bx-x-circle remove"></i>';
            addAdvanceFilterRows += '</td>';
            addAdvanceFilterRows += '</tr>';

            $('#AddOnTBody_'+room_no+'_'+board_no).append(addAdvanceFilterRows)
            var new_addon_filter_count = addon_filter_count;

            $('#addon_'+room_no+'_'+board_no+'_'+new_addon_filter_count+'').select2({
                ajax: {
                    url: ajaxURLSearchAddon,
                    dataType: 'json',
                    delay: 0,
                    data: function(params) {
                        return {
                            q: params.term, // search term
                            page: params.page,
                            "quot_id" : function() {
                                return $('#quot_id').val();
                            },
                            "quotgroup_id" : function() {
                                return $('#quotgroup_id').val();
                            },
                            "type" : function() {
                                return item_type;
                            },
                            "range_subgroup" : function() {
                                return board_range;
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
                placeholder: 'Search for add on',
                dropdownParent: $('#AddOnTBody_'+room_no+'_'+board_no+'')
            });

            addon_filter_count++;

            $('#AddOnTBody_'+room_no+'_'+board_no).on('click', '.remove', function(e) {
                e.preventDefault();
                $(this).parent().parent().remove();
                if (addon_filter_count == 1) {
                    addon_filter_count = 1;
                } else {
                    addon_filter_count--;
                }
            });
        }

        function delete_board_Warning(quot_id, quotgroup_id, room_no, board_no) {

            var ajaxQuotBoardDeleteURL = '{{ route('quot.board.delete') }}';
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: !0,
                confirmButtonText: "Yes, delete it!",
                cancelButtonText: "No, cancel!",
                confirmButtonClass: "btn btn-success mt-2",
                cancelButtonClass: "btn btn-danger ms-2 mt-2",
                loaderHtml: "<i class='bx bx-hourglass bx-spin font-size-16 align-middle me-2'></i> Loading",
                customClass: {
                    confirmButton: 'btn btn-primary btn-lg',
                    cancelButton: 'btn btn-danger btn-lg',
                    loader: 'custom-loader'
                },
                buttonsStyling: !1,
                preConfirm: function(n) {
                    return new Promise(function(t, e) {
                        Swal.showLoading()
                        $.ajax({
                            type: 'GET',
                            url: ajaxQuotBoardDeleteURL + "?quot_id=" + quot_id +
                                "&quotgroup_id=" + quotgroup_id + "&room_no=" + room_no +
                                "&board_no=" + board_no,
                            success: function(resultData) {
                                if (resultData['status'] == 1) {
                                    boardDetail('1', 1);
                                    t()
                                }
                            }
                        });
                    })
                },
            }).then(function(t) {
                if (t.value === true) {
                    Swal.fire({
                        title: "Deleted!",
                        text: "Your record has been deleted.",
                        icon: "success"
                    });
                }
            });
        }

        function quotation_board_status(val, quot_id, quotgroup_id, room_no, board_no, type) {
            var ajaxURLQuotationBoardStaus = '{{ route('quot.doard.status.data') }}';
            var board_status = val.checked ? 1 : 0;

            $.ajax({
                type: 'GET',
                url: ajaxURLQuotationBoardStaus,
                data: {
                    "quot_id": quot_id,
                    "quotgroup_id": quotgroup_id,
                    "room_no": room_no,
                    "board_no": board_no,
                    "status": board_status,
                    "type": "BOARD",
                },

                success: function(resultData) {
                    if (resultData['status'] == 1) {
                        toastr["success"](resultData['msg'] + ' ✅');
                        boardDetail('1', 1);
                    } else {
                        toastr["error"](resultData['msg']);
                    }
                }
            });
        }

        function SaveSingleBoard(quot_id, quotgroup_id, room_no, board_no, item_type) {
            Room = [{
                "id": "0",
                "room_no": room_no,
                "quot_id": quot_id,
                "quotgroup_id": quotgroup_id,
                "board_no": board_no,
                "item_type": item_type,
            }];

            Items = [];
            $('.PlateNewItem_'+room_no+'_'+board_no+'').each(function(ind) {
                var count = $(this).attr('data-id');
                if($('#plate_'+room_no+'_'+board_no+'_'+count+'').val() != null && $('#plate_qty_'+room_no+'_'+board_no+'_'+count+'').val() != null && $('#plate_qty_'+room_no+'_'+board_no+'_'+count+'').val() != "" && $('#plate_qty_'+room_no+'_'+board_no+'_'+count+'').val() != 0) {
                    Items.push({
                        "item_price_id" : $('#plate_'+room_no+'_'+board_no+'_'+count+'').val(),
                        "qty" : $('#plate_qty_'+room_no+'_'+board_no+'_'+count+'').val()
                    })
                }
            })

            $('.AccessoriesNewItem_'+room_no+'_'+board_no+'').each(function(ind) {
                var count = $(this).attr('data-id');
                if($('#accessories_'+room_no+'_'+board_no+'_'+count+'').val() != null && $('#accessories_qty_'+room_no+'_'+board_no+'_'+count+'').val() != null && $('#accessories_qty_'+room_no+'_'+board_no+'_'+count+'').val() != "" && $('#accessories_qty_'+room_no+'_'+board_no+'_'+count+'').val() != 0) {
                    Items.push({
                        "item_price_id" : $('#accessories_'+room_no+'_'+board_no+'_'+count+'').val(),
                        "qty" : $('#accessories_qty_'+room_no+'_'+board_no+'_'+count+'').val()
                    })
                }
            })

            $('.WhitelioModeNewItem_'+room_no+'_'+board_no+'').each(function(ind) {
                var count = $(this).attr('data-id');
                if($('#whiteliom_model_'+room_no+'_'+board_no+'_'+count+'').val() != null && $('#whiteliom_model_qty_'+room_no+'_'+board_no+'_'+count+'').val() != null && $('#whiteliom_model_qty_'+room_no+'_'+board_no+'_'+count+'').val() != "" && $('#whiteliom_model_qty_'+room_no+'_'+board_no+'_'+count+'').val() != 0) {
                    Items.push({
                        "item_price_id" : $('#whiteliom_model_'+room_no+'_'+board_no+'_'+count+'').val(),
                        "qty" : $('#whiteliom_model_qty_'+room_no+'_'+board_no+'_'+count+'').val()
                    })
                }
            })

            $('.AddOnNewItem_'+room_no+'_'+board_no+'').each(function(ind) {
                var count = $(this).attr('data-id');
                if($('#addon_'+room_no+'_'+board_no+'_'+count+'').val() != null && $('#addon_qty_'+room_no+'_'+board_no+'_'+count+'').val() != null && $('#addon_qty_'+room_no+'_'+board_no+'_'+count+'').val() != "" && $('#addon_qty_'+room_no+'_'+board_no+'_'+count+'').val() != 0) {
                    Items.push({
                        "item_price_id" : $('#addon_'+room_no+'_'+board_no+'_'+count+'').val(),
                        "qty" : $('#addon_qty_'+room_no+'_'+board_no+'_'+count+'').val()
                    })
                }
            })

            $.ajax({
                type: 'POST',
                url: ajaxURLRoomAndBoardSave,
                data: {
                    "_token": csrfToken,
                    "room": Room,
                    "item": Items,
                    "notes" : $('#board_notes_'+room_no+'_'+board_no+'').val(),
                },
                success: function(resultData) {
                    if(resultData['status'] == 1) {
                        toastr["success"](resultData['msg']);
                        boardDetail("1", "1");
                    } else {
                        toastr["error"](resultData['msg']);
                    }
                }
            });
        }
    </script>
@endsection
