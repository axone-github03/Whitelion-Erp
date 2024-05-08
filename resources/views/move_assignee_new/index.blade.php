@extends('layouts.main')
@section('title', $data['title'])
@section('content')
    <style>
        .form-control {
            border-radius: 10px;
        }

        .custom-height {
            height: 35px;
            /* Adjust the height as needed */
        }
    </style>
    <div class="page-content">
        <div class="container-fluid">
            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h2 class="mb-sm-0 font-size-18 text-black">Move Assignee</h2>
                    </div>
                </div>
            </div>
            <!-- end page title -->

            <div class="row">
                <div class="d-flex col">
                    {{-- <div class="form-check">
                        <input class="form-check-input checkbox" type="checkbox" value="0" id="selectAllType" onclick="detailFrom(0)">
                        <label class="form-check-label" for="selectAllType" >
                            Select All
                        </label>
                    </div> --}}
                    <div class="form-check ms-3">
                        <input class="form-check-input checkbox" type="checkbox" value="1" id="lead" onclick="detailFrom(1)">
                        <label class="form-check-label" for="lead">Lead</label>
                    </div>

                    <div class="form-check ms-3">
                        <input class="form-check-input checkbox" type="checkbox" value="2" id="deal" onclick="detailFrom(2)">
                        <label class="form-check-label" for="deal">Deal</label>
                    </div>
                    <div class="form-check ms-3">
                        <input class="form-check-input checkbox" type="checkbox" value="3" id="architect" onclick="detailFrom(3)">
                        <label class="form-check-label" for="architect">Architect</label>
                    </div>

                    <div class="form-check ms-3">
                        <input class="form-check-input checkbox" type="checkbox" value="4" id="electrician" onclick="detailFrom(4)">
                        <label class="form-check-label" for="electrician">Electrician</label>
                    </div>
                    <div class="form-check ms-3">
                        <input class="form-check-input checkbox" type="checkbox" value="5" id="channelPartner" onclick="detailFrom(5)">
                        <label class="form-check-label" for="channelPartner">Channel Partner</label>
                    </div>
                </div>
            </div>


            <div class="row d-flex mt-5">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="From_Assignee" class="form-label" style="color: black;">From Assignee</label>
                        <select class="form-control select2-ajax" id="from_assignee" aria-label="Disabled select example"
                            name="from_assignee" required>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="to_assignee" class="form-label" style="color: black;">To Assignee</label>
                        <select class="form-control select2-ajax" id="to_assignee" aria-label="Disabled select example"
                            name="to_assignee" required>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row d-flex mt-2">
                <div class="col-md-6">
                    <div class="mb-3" id="status_detail_div" style="display: none;">
                        <select class="form-control select2-ajax" id="status_detail" aria-label="Disabled select example" name="status_detail" required></select>
                    </div>
                </div>


            </div>


            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <table
                                class="table align-middle table-nowrap table-hover table-striped dt-responsive nowrap w-100"
                                id="datatable">
                                <thead>
                                    <tr>
                                        <td class="col-1">
                                            <input class="form-check-input" type="checkbox" name="selectAllDatatableCheckbox" id="selectAllDatatableCheckbox" checked>
                                        </td>
                                        <td class="col-2">ID</td>
                                        <td class="col-2">Type</td>
                                        <td class="col-3">Name</td>
                                        <td class="col-2">Mobile Number</td>
                                        <td>Status</td>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>

                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary waves-effect waves-light float-end mt-2 item" onclick="saveMoveAssign()" id="moveAssignSave">Save</button>
                </div>
            </div>

        </div>
    </div>
    @csrf
@endsection('content')
@section('custom-scripts')

    <script src="{{ asset('assets/libs/parsleyjs/parsley.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/form-validation.init.js') }}"></script>
    <script src="{{ asset('assets/js/pages/jquery.form.js') }}"></script>
    <script type="text/javascript">
        var ajaxURLSalseUserDetail = "{{ route('new.search.from.assignee.user') }}";
        var ajaxURLAjax = "{{ route('new.move.assignee.ajax') }}";
        var ajaxURLStatusDetail = "{{ route('new.move.assignee.status') }}";
        var ajaxURLSaveMoveAssign = "{{ route('new.move.assignee.save') }}";
        var csrfToken = $("[name=_token").val();

        var SelectedType = [];

        $("#to_assignee").select2({
            ajax: {
                url: ajaxURLSalseUserDetail,
                dataType: 'json',
                delay: 0,
                data: function(params) {
                    return {
                        q: params.term, // search term
                        page: params.page,
                        user_id: function() {
                            return $("#from_assignee").val();
                        },
                        is_channel_partner: function() {
                            return $("#check_feature_4").prop("checked");
                        }
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
            placeholder: 'Search for to assignee',



        });

        $("#from_assignee").select2({
            ajax: {
                url: ajaxURLSalseUserDetail,
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
            placeholder: 'Search for from assignee',
        }).on('change', function() {
            reloadTable();
            $("#to_assignee").empty().trigger('change');
        });

        var table = $('#datatable').DataTable({
            "aoColumnDefs": [{
                "bSortable": false,
            }],
            "ajax": {
                "url" : ajaxURLAjax,
                "type" : "POST",
                "data" : {
                    "_token": csrfToken,
                    "type": function() {
                        return SelectedType;
                    },
                    "from_assign": function() {
                        return $("#from_assignee").val();
                    },
                    "status": function() {
                        return $("#status_detail").val();
                    }
                }
            },
            "aoColumns": [{
                    "mData": "switch"
                },
                {
                    "mData": "id"
                },
                {
                    "mData": "type"
                },
                {
                    "mData": "first_name"
                },
                {
                    "mData": "phone_number"
                },
                {
                    "mData": "status"
                }
            ],
        });

        $(document).ready(function() {
            $('#selectAllDatatableCheckbox').on('click', function(){
                var rows = table.rows({ 'search': 'applied' }).nodes();
                $('.dataTableCheckBox', rows).prop('checked', this.checked);
            });
        });

        function reloadTable() {
            SelectedType = [];
            $(".checkbox:checked").each(function(){
                SelectedType.push($(this).val());
            });
            table.ajax.reload();
        }

        $('.checkbox').on('change', function() {
            if ($(this).prop('checked')) {
                $('.checkbox').not(this).prop('checked', false);
            }
        });

        $('#selectAllType').on('change', function() {
            $('.checkbox').prop('checked', $(this).prop('checked'));
            $('#status_detail_div').hide();
            reloadTable();
        })

        function detailFrom(id) {
            $("#status_detail").empty().trigger('change');
            if(id != 0) {
                $("#status_detail_div").show();
            }
            $.ajax({
                url: ajaxURLStatusDetail,
                type: "get",
                data: {
                    'type': id,
                },
                success: function(data) {
                    initializeSelect2(data.results);
                }
            });
        }

        function initializeSelect2(data) {
            $("#status_detail").select2({
                placeholder: 'Select the Status',
                data: data,
            }).append('<option value="" selected>Select the Status</option>').trigger('change');
        }

        $("#status_detail").on('change', function() {
            reloadTable();
        });

        
        function saveMoveAssign() {

            $('#moveAssignSave').prop("disabled", true)
            $('#moveAssignSave').text('Saving...');
            var data = []
            table.$('.dataTableCheckBox').each(function(){
                if(this.checked){
                    data.push({
                        "id" : $(this).attr('data-id'),
                        "type" : $(this).attr('data-type'),
                    })
                }
            });

            if(data != [] && data != null && data != "") {
                $.ajax({
                    url: ajaxURLSaveMoveAssign,
                    type: "post",
                    data: {
                        "_token" : csrfToken,
                        'data': data,
                        'from_assign': $('#from_assignee').val(),
                        'to_assign': $('#to_assignee').val(),
                    },
                    success: function(data) {
                        if(data['status'] == 1) {
                            toastr["success"](data['msg']);
                            reloadTable();
                        } else {
                            toastr["error"](data['msg']);
                        }
                        $('#moveAssignSave').prop("disabled", false);
                        $('#moveAssignSave').text('Save');
                    }
                });
            } else {
                toastr["error"]("Please select data");
            }
        }

    </script>
@endsection
