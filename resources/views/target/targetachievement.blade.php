@extends('layouts.main')
@section('title', $data['title'])
@section('content')

    <style type="text/css">
        td p {
            max-width: 100%;
            white-space: break-spaces;
            word-break: break-all;
        }
    </style>
    <div class="page-content">
        <div class="container-fluid">
            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">Target Achievement</h4>
                        <div class="page-title-right">
                            <button onclick="targetView(0,0,0,'new')" class="btn btn-primary" type="button"><i
                                    class="bx bx-list-ul font-size-16 align-middle me-2"></i>Target View</button>
                            @if (isAdminOrCompanyAdmin() == 1 || isAccountUser() == 1)
                                <button onclick="addtarget()" class="btn btn-primary" type="button"><i
                                        class="bx bx-plus font-size-16 align-middle me-2"></i>Add Target</button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <!-- end page title -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body" id="card_targetview">
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                        <label for="q_financial_year_filter" class="form-label">Financial Year </label>
                                        <select class="form-control select2-ajax" id="q_financial_year_filter"
                                            name="q_financial_year_filter">
                                        </select>
                                        <div class="invalid-feedback">
                                            Please select view type.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                        <label for="q_target_view_type" class="form-label">View Type </label>
                                        <select class="form-control select2-ajax" id="q_target_view_type"
                                            name="q_target_view_type">
                                        </select>
                                        <div class="invalid-feedback">
                                            Please select view type.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <table id="datatable" class="table table-striped dt-responsive  nowrap w-100">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Name</th>
                                        <th>Total Target</th>
                                        <th>Achieved Target</th>
                                        <th>% Achieved</th>
                                        <th>Action</th>
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
    @csrf
@endsection('content')

@section('custom-scripts')
    @include('../target/comman/add_edit')

    <script src="{{ asset('assets/libs/parsleyjs/parsley.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/form-validation.init.js') }}"></script>
    <script src="{{ asset('assets/js/pages/jquery.form.js') }}"></script>
    <script type="text/javascript">
        var ajaxTargetAchievementDataUrl = '{{ route('target-achievement.ajax') }}';
        var ajaxTargetAchievementDetailURL = '{{ route('target.achievement.detail') }}';
        var ajaxCompanyMasterDeleteURL = '{{ route('quot.company.master.delete') }}';
        var ajaxSearchTargetViewTypeURL = '{{ route('search.target.view.type') }}';
        var ajaxCurruntFinancialYearURL = '{{ route('currunt.financial.year.ajax') }}';

        var csrfToken = $("[name=_token").val();

        var mainMasterPageLength = getCookie('mainMasterPageLength') !== undefined ? getCookie('mainMasterPageLength') : 10;
        var table = $('#datatable').DataTable({
            "aoColumnDefs": [{
                "bSortable": false,
                "aTargets": [4]
            }],
            "order": [
                [0, 'desc']
            ],
            "processing": true,
            "serverSide": true,
            "pagingType": "full_numbers",
            "pageLength": mainMasterPageLength,
            "ajax": {
                "url": ajaxTargetAchievementDataUrl,
                "type": "POST",
                "data": {
                    "_token": csrfToken,
                    "view_type": function() {
                        return $("#q_target_view_type").val()
                    },
                    "financial_year": function() {
                        return $("#q_financial_year_filter").val()
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
                    "mData": "total_target"
                },
                {
                    "mData": "achived_target"
                },
                {
                    "mData": "achived_per"
                },
                {
                    "mData": "action"
                }
            ],
            "drawCallback": function() {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                });
                // tooltipTriggerList.forEach(element => {

                // console.log(element);
                // let btn_tooltip = $('#' + element.getAttribute('id'));

                // var title = 'Create Date & Time ' + element.getAttribute('created_at');
                // Change Tooltip Text on mouse enter
                // btn_tooltip.mouseenter(function() {
                //     btn_tooltip.attr('title', title).tooltip('dispose');
                //     btn_tooltip.tooltip('show');
                // });

                // // Update Tooltip Text on click
                // btn_tooltip.click(function() {
                //     btn_tooltip.attr('title', 'Modified Tooltip').tooltip('dispose');
                //     btn_tooltip.tooltip('show');
                // });
                // });
            }
        });

        function reloadTable() {
            table.ajax.reload(null, false);
        }


        $('#datatable').on('length.dt', function(e, settings, len) {
            setCookie('mainMasterPageLength', len, 100);

        });

        $(document).ready(function() {
            $.ajax({
                type: 'GET',
                url: ajaxCurruntFinancialYearURL,
                success: function(resultData) {
                    if (resultData.currunt_fy_name != 0) {
                        var newOption = new Option(resultData.currunt_fy_name, resultData.currunt_fy_id,
                            false, false);
                        $('#q_financial_year_filter').append(newOption).trigger('change');
                    }
                }
            });
            // var newOption = new Option('FULL YEAR', '0', false, false);

            const month = ["non","JANUARY", "FEBRUARY", "MARCH", "APRIL", "MAY", "JUNE", "JULY", "AUGUST", "SEPTEMBER","OCTOMBER", "NOVEMBER", "DECEMBER"];
            const d = new Date();
            let month_no = (d.getMonth()+1);
            let month_name = month[month_no];
            console.log("MONTH NO : " + month_no);
            console.log("MONTH NAME : " + month_name);

            var newOption = new Option(month_name, month_no, false, false);
            $('#q_target_view_type').append(newOption).trigger('change');

            var options = {
                beforeSubmit: showRequest, // pre-submit callback
                success: showResponse // post-submit callback
            };
            // bind form using 'ajaxForm'
            $('#formTargetMaster').ajaxForm(options);

        });

        $('#q_target_view_type').on('change', function() {
            // alert(this.value);
            reloadTable();
        });

        function showRequest(formData, jqForm, options) {
            var queryString = $.param(formData);
            return true;
        }

        // post-submit callback
        function showResponse(responseText, statusText, xhr, $form) {
            if (responseText['status'] == 1) {
                toastr["success"](responseText['msg']);
                $("#modalAddEditTarget").modal('hide');
                reloadTable();
                $(".save_target").html("Save");
            } else if (responseText['status'] == 0) {
                toastr["error"](responseText['msg']);
                reloadTable();
                $(".save_target").html("Save");
            }
        }

        var ajaxURLSearchFY = '{{ route('search.financial.year.ajax') }}';
        $("#q_financial_year_filter").select2({
            ajax: {
                url: ajaxURLSearchFY,
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
                    if (data.currunt_fy_name != 0) {
                        var newOption = new Option(data.currunt_fy_name, data.currunt_fy_id, false, false);
                        $('#q_financial_year_filter').append(newOption).trigger('change');
                    }
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
            dropdownParent: $("#card_targetview"),
        }).on('change', function(e) {
            reloadTable();
        });

        $("#q_target_view_type").select2({
            ajax: {
                url: ajaxSearchTargetViewTypeURL,
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
            placeholder: 'Select Item View Type',
            dropdownParent: $("#card_targetview"),
        });

        $("#addBtnMainMaster").click(function() {
            $("#canvasTargetMasterLable").html("Add Company");
            $("#formTargetMaster").show();
            $(".loadingcls").hide();
            $(".save_target").html("Save");
            resetInputForm();
        });
        $(".save_target").click(function() {
            $(".save_target").html(
                '<i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i> <span  >Saving...</span>');
        });

        function resetInputForm() {
            $("#formTargetMaster").removeClass('was-validated');
            $('#formTargetMaster').trigger("reset");
            $("#q_target_achievement_id").val(0);
            $("#q_employee_id").empty().trigger('change');
            $("#q_fy_id").empty().trigger('change');

            $("#q_target_achievement_id").val(0);

            $('#q_joining_date').val('');
            $("#april_td_id").val(0);
            $("#may_td_id").val(0);
            $("#june_td_id").val(0);
            $("#july_td_id").val(0);
            $("#august_td_id").val(0);
            $("#september_td_id").val(0);
            $("#octomber_td_id").val(0);
            $("#november_td_id").val(0);
            $("#december_td_id").val(0);
            $("#january_td_id").val(0);
            $("#february_td_id").val(0);
            $("#march_td_id").val(0);

            $(".save_target").html("Save");
        }

        function editView(id) {

            resetInputForm();

            $("#modalAddEditTarget").modal('show');
            $("#canvasTargetMasterLable").html("Edit Target #" + id);
            $("#formTargetMaster").hide();
            $(".loadingcls").show();

            $("#q_incremental_per").on("keyup change", function(e) {
                var max = parseInt($(this).attr('max'));
                var min = parseInt($(this).attr('min'));
                if ($(this).val() > max) {
                    $(this).val(max);
                } else if ($(this).val() < min) {
                    $(this).val(min);
                }

                var Monthly_Target_Input_array = document.getElementsByClassName('monthly_dist');
                var Incremental_Per = document.getElementById('q_incremental_per');
                var Distribute_Type = $("#distribute_type_value").val();
                var Total_target = $("#q_total_target").val();
                var Total_Month = 12;
                var Joining_Date = $('#q_joining_date').val();
                var FY_Id = $('#q_fy_id').val();
                var Fy_Text = $('#q_fy_id').select2('data')[0].text;

                distribute_target(Monthly_Target_Input_array, Incremental_Per, Distribute_Type, Total_target,
                    Total_Month, Joining_Date, FY_Id, Fy_Text);

            });

            $("#q_total_target").on("keyup change", function(e) {
                var Monthly_Target_Input_array = document.getElementsByClassName('monthly_dist');
                var Incremental_Per = document.getElementById('q_incremental_per');
                var Distribute_Type = $("#distribute_type_value").val();
                var Total_target = this.value;
                var Total_Month = 12;
                var Joining_Date = $('#q_joining_date').val();
                var FY_Id = $('#q_fy_id').val();
                var Fy_Text = $('#q_fy_id').select2('data')[0].text;

                distribute_target(Monthly_Target_Input_array, Incremental_Per, Distribute_Type, Total_target,
                    Total_Month, Joining_Date, FY_Id, Fy_Text);
            })


            $('input[type=radio][name=distribute_type]').change(function() {

                $("#distribute_type_value").val(this.value);

                var Monthly_Target_Input_array = document.getElementsByClassName('monthly_dist');
                var Incremental_Per = document.getElementById('q_incremental_per');
                var Distribute_Type = $("#distribute_type_value").val();
                var Total_target = $("#q_total_target").val();
                var Total_Month = 12;
                var Joining_Date = $('#q_joining_date').val();
                var FY_Id = $('#q_fy_id').val();
                var Fy_Text = $('#q_fy_id').select2('data')[0].text;

                distribute_target(Monthly_Target_Input_array, Incremental_Per, Distribute_Type, Total_target,
                    Total_Month, Joining_Date, FY_Id, Fy_Text);
            });

            $.ajax({
                type: 'GET',
                url: ajaxTargetAchievementDetailURL + "?id=" + id,
                success: function(resultData) {
                    if (resultData['status'] == 1) {

                        $("#q_target_achievement_id").val(resultData['data']['basic']['id']);
                        $("#q_joining_date").val(resultData['data']['basic']['joining_date']);

                        if (resultData['data']['basic']['employeee_id'] !== null) {
                            $("#q_employee_id").empty().trigger('change');
                            var newOption = new Option(resultData['data']['basic']['sales_person_name'],
                                resultData['data']['basic']['employeee_id'], false, false);
                            $('#q_employee_id').append(newOption).trigger('change');
                        }
                        if (resultData['data']['basic']['finyear_id'] !== null) {
                            $("#q_fy_id").empty().trigger('change');
                            var newOption = new Option(resultData['data']['basic']['financial_year'],
                                resultData['data']['basic']['finyear_id'], false, false);
                            $('#q_fy_id').append(newOption).trigger('change');
                        }

                        $("#q_min_achievement").val(resultData['data']['basic']['minachivement']);
                        $("#q_total_target").val(resultData['data']['basic']['total_target']);
                        $("#distribute_type_value").val(+resultData['data']['basic']['distribute_type']);


                        $('input:radio[name="distribute_type"]').filter('[value="' + resultData['data']['basic']
                            ['distribute_type'] + '"]').attr('checked', true);
                        if (resultData['data']['basic']['distribute_type'] == 3) {
                            document.getElementById('q_incremental_per').removeAttribute("readonly");
                            document.getElementById('q_incremental_per').required = true;
                            $("#q_incremental_per").val(resultData['data']['basic']['incremental_per']);
                        }

                        $.each(resultData['data']['detail'], function(index, value) {

                            if (value['month_number'] == 4) {
                                $("#april_td_id").val(value['id']);
                                $("#q_april_target").val(value['target_amount']);

                            } else if (value['month_number'] == 5) {
                                $("#may_td_id").val(value['id']);
                                $("#q_may_target").val(value['target_amount']);

                            } else if (value['month_number'] == 6) {
                                $("#june_td_id").val(value['id']);
                                $("#q_june_target").val(value['target_amount']);

                            } else if (value['month_number'] == 7) {
                                $("#july_td_id").val(value['id']);
                                $("#q_july_target").val(value['target_amount']);

                            } else if (value['month_number'] == 8) {
                                $("#august_td_id").val(value['id']);
                                $("#q_august_target").val(value['target_amount']);

                            } else if (value['month_number'] == 9) {
                                $("#september_td_id").val(value['id']);
                                $("#q_september_target").val(value['target_amount']);

                            } else if (value['month_number'] == 10) {
                                $("#octomber_td_id").val(value['id']);
                                $("#q_october_target").val(value['target_amount']);

                            } else if (value['month_number'] == 11) {
                                $("#november_td_id").val(value['id']);
                                $("#q_november_target").val(value['target_amount']);

                            } else if (value['month_number'] == 12) {
                                $("#december_td_id").val(value['id']);
                                $("#q_december_target").val(value['target_amount']);

                            } else if (value['month_number'] == 1) {
                                $("#january_td_id").val(value['id']);
                                $("#q_january_target").val(value['target_amount']);

                            } else if (value['month_number'] == 2) {
                                $("#february_td_id").val(value['id']);
                                $("#q_february_target").val(value['target_amount']);

                            } else if (value['month_number'] == 3) {
                                $("#march_td_id").val(value['id']);
                                $("#q_march_target").val(value['target_amount']);

                            }

                        });

                        $(".loadingcls").hide();
                        $("#formTargetMaster").show();


                    } else {

                        toastr["error"](resultData['msg']);

                    }

                }
            });

        }
    </script>
@endsection
