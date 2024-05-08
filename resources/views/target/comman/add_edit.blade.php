<div class="modal fade" tabindex="-1" id="modalAddEditTarget" data-bs-backdrop="static"
    aria-labelledby="canvasTargetMasterLable" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="canvasTargetMasterLable">Add Target</h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="col-md-12 text-center loadingcls">
                    <button type="button" class="btn btn-light waves-effect">
                        <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading
                    </button>
                </div>
                <form id="formTargetMaster" class="needs-validation" action="{{ route('target.achievement.save') }}"
                    method="POST" novalidate="">
                    @csrf
                    <input class="form-control" type="hidden" name="q_target_achievement_id"
                        id="q_target_achievement_id" value="">
                    <div class="row mb-3">
                        <div class="col-6 ajax-select">
                            <label for="q_employee_id" class="form-label">Select Employee <code
                                    class="highlighter-rouge">*</code></label>
                            <select class="form-control select2-ajax" id="q_employee_id" name="q_employee_id" required>
                            </select>
                            <div class="invalid-feedback">
                                Please select Employee.
                            </div>
                        </div>
                        <div class="col-6">
                            <label for="q_joining_date" class="form-label">Joining Date <code
                                    class="highlighter-rouge">*</code></label>
                            <div class="input-group" id="div_joining_date">
                                <input type="text" class="form-control" placeholder="dd-mm-yyyy"
                                    data-date-format="dd-mm-yyyy" data-date-container='#div_joining_date'
                                    data-provide="datepicker" data-date-autoclose="true" required name="q_joining_date"
                                    id="q_joining_date" autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4 ajax-select">
                            <label for="q_fy_id" class="form-label">Select FY <code
                                    class="highlighter-rouge">*</code></label>
                            <select class="form-control select2-ajax" id="q_fy_id" name="q_fy_id" required>
                            </select>
                            <div class="invalid-feedback">
                                Please select FY.
                            </div>
                        </div>
                        <div class="col-4">
                            <label for="q_min_achievement" class="form-label">Min % achievment <code
                                    class="highlighter-rouge">*</code></label>
                            <input type="number" class="form-control" id="q_min_achievement" name="q_min_achievement"
                                placeholder="Min Achievement (%)" value="" required>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-3">
                            <label for="q_total_target" class="form-label">Enter Total Target <code
                                    class="highlighter-rouge">*</code></label>
                            <input class="form-control" type="number" id="q_total_target" name="q_total_target"
                                placeholder="Total Target" value="" required>
                        </div>
                        <div class="col-6 align-self-center d-flex justify-content-around">
                            <input class="form-control" type="hidden" name="distribute_type_value"
                                id="distribute_type_value" value="1">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="distribute_type"
                                    id="distribute_equally" value="1" checked>
                                <label class="form-check-label" for="distribute_equally">Distribute Equally</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="distribute_type"
                                    id="distribute_manually" value="2">
                                <label class="form-check-label" for="distribute_manually">Manually</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="distribute_type"
                                    id="distribute_incremental" value="3">
                                <label class="form-check-label" for="distribute_incremental">Distribute
                                    Incremental</label>
                            </div>
                        </div>
                        <div class="col-3">
                            <label for="q_incremental_per" class="form-label">Incremental % <code
                                    class="highlighter-rouge">*</code></label>
                            <input type="number" class="form-control" min="0" max="100"
                                id="q_incremental_per" name="q_incremental_per" placeholder="Incremental (%)"
                                value="" readonly required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-2 mb-3">
                            <input class="form-control" type="hidden" name="april_td_id" id="april_td_id"
                                value="">
                            <label for="q_april_target" class="form-label">April</label>
                            <input type="text " class="form-control monthly_dist"
                                onkeypress="return isNumber(event);isRemaining(this.form.q_total_target.value);"
                                onchange="isRemaining(this.form.q_total_target.value);"
                                onkeyup="isRemaining(this.form.q_total_target.value);" no-month="04"
                                id="q_april_target" name="q_april_target" placeholder="Target" readonly required>
                        </div>
                        <div class="col-2 mb-3">
                            <input class="form-control" type="hidden" name="may_td_id" id="may_td_id"
                                value="">
                            <label for="q_may_target" class="form-label">May</label>
                            <input type="text" class="form-control monthly_dist"
                                onkeypress="return isNumber(event);isRemaining(this.form.q_total_target.value);"
                                onchange="isRemaining(this.form.q_total_target.value);"
                                onkeyup="isRemaining(this.form.q_total_target.value);" no-month="05"
                                id="q_may_target" name="q_may_target" placeholder="Target" readonly required>
                        </div>
                        <div class="col-2 mb-3">
                            <input class="form-control" type="hidden" name="june_td_id" id="june_td_id"
                                value="">
                            <label for="q_june_target" class="form-label">June</label>
                            <input type="text" class="form-control monthly_dist"
                                onkeypress="return isNumber(event);isRemaining(this.form.q_total_target.value);"
                                onchange="isRemaining(this.form.q_total_target.value);"
                                onkeyup="isRemaining(this.form.q_total_target.value);" o-month="06"
                                id="q_june_target" name="q_june_target" placeholder="Target" readonly required>
                        </div>
                        <div class="col-2 mb-3">
                            <input class="form-control" type="hidden" name="july_td_id" id="july_td_id"
                                value="">
                            <label for="q_july_target" class="form-label">July</label>
                            <input type="text" class="form-control monthly_dist"
                                onkeypress="return isNumber(event);isRemaining(this.form.q_total_target.value);"
                                onchange="isRemaining(this.form.q_total_target.value);"
                                onkeyup="isRemaining(this.form.q_total_target.value);" no-month="07"
                                id="q_july_target" name="q_july_target" placeholder="Target" readonly required>
                        </div>
                        <div class="col-2 mb-3">
                            <input class="form-control" type="hidden" name="august_td_id" id="august_td_id"
                                value="">
                            <label for="q_august_target" class="form-label">August</label>
                            <input type="text" class="form-control monthly_dist"
                                onkeypress="return isNumber(event);isRemaining(this.form.q_total_target.value);"
                                onchange="isRemaining(this.form.q_total_target.value);"
                                onkeyup="isRemaining(this.form.q_total_target.value);" no-month="08"
                                id="q_august_target" name="q_august_target" placeholder="Target" readonly required>
                        </div>
                        <div class="col-2 mb-3">
                            <input class="form-control" type="hidden" name="september_td_id" id="september_td_id"
                                value="">
                            <label for="q_september_target" class="form-label">September</label>
                            <input type="text" class="form-control monthly_dist"
                                onkeypress="return isNumber(event);isRemaining(this.form.q_total_target.value);"
                                onchange="isRemaining(this.form.q_total_target.value);"
                                onkeyup="isRemaining(this.form.q_total_target.value);" no-month="09"
                                id="q_september_target" name="q_september_target" placeholder="Target" readonly
                                required>
                        </div>
                        <div class="col-2">
                            <input class="form-control" type="hidden" name="octomber_td_id" id="octomber_td_id"
                                value="">
                            <label for="q_october_target" class="form-label">October</label>
                            <input type="text" class="form-control monthly_dist"
                                onkeypress="return isNumber(event);isRemaining(this.form.q_total_target.value);"
                                onchange="isRemaining(this.form.q_total_target.value);"
                                onkeyup="isRemaining(this.form.q_total_target.value);" no-month="10"
                                id="q_october_target" name="q_october_target" placeholder="Target" readonly required>
                        </div>
                        <div class="col-2">
                            <input class="form-control" type="hidden" name="november_td_id" id="november_td_id"
                                value="">
                            <label for="q_november_target" class="form-label">November</label>
                            <input type="text" class="form-control monthly_dist"
                                onkeypress="return isNumber(event);isRemaining(this.form.q_total_target.value);"
                                onchange="isRemaining(this.form.q_total_target.value);"
                                onkeyup="isRemaining(this.form.q_total_target.value);" no-month="11"
                                id="q_november_target" name="q_november_target" placeholder="Target" readonly
                                required>
                        </div>
                        <div class="col-2">
                            <input class="form-control" type="hidden" name="december_td_id" id="december_td_id"
                                value="">
                            <label for="q_december_target" class="form-label">December</label>
                            <input type="text" class="form-control monthly_dist"
                                onkeypress="return isNumber(event);isRemaining(this.form.q_total_target.value);"
                                onchange="isRemaining(this.form.q_total_target.value);"
                                onkeyup="isRemaining(this.form.q_total_target.value);" no-month="12"
                                id="q_december_target" name="q_december_target" placeholder="Target" readonly
                                required>
                        </div>
                        <div class="col-2">
                            <input class="form-control" type="hidden" name="january_td_id" id="january_td_id"
                                value="">
                            <label for="q_january_target" class="form-label">January</label>
                            <input type="text" class="form-control monthly_dist"
                                onkeypress="return isNumber(event);isRemaining(this.form.q_total_target.value);"
                                onchange="isRemaining(this.form.q_total_target.value);"
                                onkeyup="isRemaining(this.form.q_total_target.value);" no-month="01"
                                id="q_january_target" name="q_january_target" placeholder="Target" readonly required>
                        </div>
                        <div class="col-2">
                            <input class="form-control" type="hidden" name="february_td_id" id="february_td_id"
                                value="">
                            <label for="q_february_target" class="form-label">February</label>
                            <input type="text" class="form-control monthly_dist"
                                onkeypress="return isNumber(event);isRemaining(this.form.q_total_target.value);"
                                onchange="isRemaining(this.form.q_total_target.value);"
                                onkeyup="isRemaining(this.form.q_total_target.value);" no-month="02"
                                id="q_february_target" name="q_february_target" placeholder="Target" readonly
                                required>
                        </div>
                        <div class="col-2">
                            <input class="form-control" type="hidden" name="march_td_id" id="march_td_id"
                                value="">
                            <label for="q_march_target" class="form-label">March</label>
                            <input type="text" class="form-control monthly_dist"
                                onkeypress="return isNumber(event);isRemaining(this.form.q_total_target.value);"
                                onchange="isRemaining(this.form.q_total_target.value);"
                                onkeyup="isRemaining(this.form.q_total_target.value);" no-month="03"
                                id="q_march_target" name="q_march_target" placeholder="Target" readonly required>
                        </div>

                    </div>
                    <div class="d-flex flex-wrap justify-content-end gap-2">
                        <label for="remaining" style="width: 25%;"
                            class="form-label text-end align-self-center text-danger">Remaining : </label>
                        <input type="text " class="form-control text-danger" style="width: 25%;"
                            id="remaining_value" name="remaining_value" value="00" placeholder="Remaining"
                            readonly required>
                        <button type="reset" class="btn btn-secondary waves-effect">Reset</button>
                        <button type="submit"
                            class="btn btn-primary waves-effect waves-light save_target">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal fade m-auto" tabindex="-1" id="modalTargetView" data-bs-backdrop="static"
    aria-labelledby="canvasmodalTargetViewLable" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl mw-100" style="width: 88%;" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="canvasmodalTargetViewLable">Target View</h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-3">
                        <div class="mb-3">
                            <input type="text" class="form-control" placeholder="Search..."
                                name="search_sales_user" id="search_sales_user">
                        </div>

                        <input class="form-control" type="hidden" name="sales_user_id" id="sales_user_id"
                            value="0">
                        <div class="btn-group-vertical col-12" role="group"
                            aria-label="Vertical radio toggle button group" id="q_sales_user_list">
                        </div>

                    </div> <!-- end col -->
                    <div class="col-9">
                        <div class="mb-5 row">
                            <div class="col-3 ajax-select">
                                <label for="q_tv_fy_id" class="form-label">Select FY <code
                                        class="highlighter-rouge">*</code></label>
                                <select class="form-control select2-ajax" id="q_tv_fy_id" name="q_tv_fy_id" required>
                                </select>
                                <div class="invalid-feedback">
                                    Please select FY.
                                </div>
                            </div>

                            <div class="col-6 align-self-center d-flex justify-content-around">
                                @if(Auth::user()->type == 0 || Auth::user()->type == 1)
                                    <input class="form-control" type="hidden" name="data_view_type_val" id="data_view_type_val" value="1">
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label" for="order_data_view">Ordered</label>
                                        <input class="form-check-input" type="radio" name="data_view_type" id="order_data_view" value="1" checked>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label" for="dispatched_data_view">Dispatched</label>
                                        <input class="form-check-input" type="radio" name="data_view_type" id="dispatched_data_view" value="2">
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label" for="freezed_data_view">Freezed</label>
                                        <input class="form-check-input" type="radio" name="data_view_type" id="freezed_data_view" value="3">
                                    </div>
                                @else
                                    <input class="form-control" type="hidden" name="data_view_type_val" id="data_view_type_val" value="3">
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label" for="freezed_data_view">Freezed</label>
                                        <input class="form-check-input" type="radio" name="data_view_type" id="freezed_data_view" value="3" checked>
                                    </div>
                                @endif
                                
                            </div>

                        </div>


                        <div class="col-12 row">
                            <div class="col-6">
                                <div class="row text-center text-dark font-size-14">
                                    <div class="col-2 text-start">
                                        <label class="form-label">Month</label>
                                    </div>
                                    <div class="col-3">
                                        <label class="form-label">Target</label>
                                    </div>
                                    <div class="col-3">
                                        <label class="form-label">Achieved</label>
                                    </div>
                                    <div class="col-2">
                                        <label class="form-label">%</label>
                                    </div>
                                </div>
                                <div id="monthly_report">

                                </div>
                            </div>
                            <div class="col-6 ">
                                <div class="row text-center text-dark font-size-14">
                                    <div class="col-2">
                                        <label class="form-label">Quarterly</label>
                                    </div>
                                    <div class="col-3">
                                        <label class="form-label">Target</label>
                                    </div>
                                    <div class="col-3">
                                        <label class="form-label">Achieved</label>
                                    </div>
                                    <div class="col-2">
                                        <label class="form-label">%</label>
                                    </div>
                                </div>
                                <div id="quterly_report">

                                </div>
                                <div class="row text-center text-dark font-size-14">
                                    <div class="col-2">
                                        <label class="form-label text-danger">Year</label>
                                    </div>
                                    <div class="col-3">
                                        <label class="form-label text-danger">Target</label>
                                    </div>
                                    <div class="col-3">
                                        <label class="form-label text-danger">Achieved</label>
                                    </div>
                                    <div class="col-2">
                                        <label class="form-label text-danger">%</label>
                                    </div>
                                </div>
                                <div id="yearly_report">

                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{{ asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
<script type="text/javascript">
    function targetView(targetid, sales_user_id, finncial_year_id, financial_year) {

        $("#modalTargetView").modal('show');
        // $("#q_tv_fy_id").empty().trigger('change');
        if (financial_year != 'new') {
            var newOption = new Option(financial_year, finncial_year_id, false, false);
            $('#q_tv_fy_id').append(newOption).trigger('change');
        }
        reset_data();

        $(document).ready(function(){
            var data_view_type_value = $("#data_view_type_val").val();
            var monthly_achivement_array = document.getElementsByClassName('monthly_achivement');
            var freeze_button_array = document.getElementsByClassName('freezbutton');

            // if (data_view_type_value != null) {
            //     var start_year = $("#q_tv_fy_id").select2('data')[0].text.split("-")[0];
            //     var end_year = $("#q_tv_fy_id").select2('data')[0].text.split("-")[1];
            //     var start_date = '01-04-' + start_year;
            //     var end_date = '31-03-' + end_year;

            //     setTargetViewData($("#sales_user_id").val(), start_date, end_date, $("#q_tv_fy_id").val(), this
            //         .value);
            // }

            if (data_view_type_value == 1) { //Ordered
                monthly_achivement_array.forEach(element => {
                    element.setAttribute("readonly", "");
                    var element_id = $(element).attr('id');
                });
                freeze_button_array.forEach(element => {
                    var element_id = $(element).attr('id');
                    $('#' + element_id).hide();
                });

            } else if (data_view_type_value == 2) { //Dispatched

                monthly_achivement_array.forEach(element => {
                    element.setAttribute("readonly", "");
                    var element_id = $(element).attr('id');
                });
                freeze_button_array.forEach(element => {
                    var element_id = $(element).attr('id');
                    $('#' + element_id).hide();
                });

            } else if (data_view_type_value == 3) { //Freeze
                monthly_achivement_array.forEach(element => {
                    element.removeAttribute("readonly");
                    var element_id = $(element).attr('id');
                });
                freeze_button_array.forEach(element => {
                    var element_id = $(element).attr('id');
                    $('#' + element_id).show();
                });
            }
        })

        $('input[type=radio][name=data_view_type]').change(function() {

            $("#data_view_type_val").val(this.value);

            var monthly_achivement_array = document.getElementsByClassName('monthly_achivement');
            var freeze_button_array = document.getElementsByClassName('freezbutton');

            if (this.value != null) {
                var start_year = $("#q_tv_fy_id").select2('data')[0].text.split("-")[0];
                var end_year = $("#q_tv_fy_id").select2('data')[0].text.split("-")[1];
                var start_date = '01-04-' + start_year;
                var end_date = '31-03-' + end_year;

                setTargetViewData($("#sales_user_id").val(), start_date, end_date, $("#q_tv_fy_id").val(), this
                    .value);
            }

            if (this.value == 1) { //Ordered
                monthly_achivement_array.forEach(element => {
                    element.setAttribute("readonly", "");
                    var element_id = $(element).attr('id');
                });
                freeze_button_array.forEach(element => {
                    var element_id = $(element).attr('id');
                    $('#' + element_id).hide();
                });

            } else if (this.value == 2) { //Dispatched

                monthly_achivement_array.forEach(element => {
                    element.setAttribute("readonly", "");
                    var element_id = $(element).attr('id');
                });
                freeze_button_array.forEach(element => {
                    var element_id = $(element).attr('id');
                    $('#' + element_id).hide();
                });

            } else if (this.value == 3) { //Freeze
                monthly_achivement_array.forEach(element => {
                    element.removeAttribute("readonly");
                    var element_id = $(element).attr('id');
                });
                freeze_button_array.forEach(element => {
                    var element_id = $(element).attr('id');
                    $('#' + element_id).show();
                });
            }
        });

        var ajaxURLSearchTVSalesUser = '{{ route('search.sales.user.target.view') }}';
        $('#search_sales_user').on("keyup change", function(e) {
            $.ajax({
                type: 'GET',
                url: ajaxURLSearchTVSalesUser,
                data: {
                    "q": $('#search_sales_user').val()
                },

                success: function(resultData) {
                    if (resultData['status'] == 1) {

                        sales_user_list = document.getElementById('q_sales_user_list');
                        sales_user_list.innerHTML = '';

                        let newSalesUsermap = new Map();
                        const arrUnique = resultData['data'].filter(el => {
                            const val = newSalesUsermap.get(el.id);
                            if (val) {
                                if (el.id < val) {
                                    newSalesUsermap.delete(el.name);
                                    newSalesUsermap.set(el.id, el.text, el.fynancial_year,
                                        el.fynancial_year_name);
                                    return true;
                                } else {
                                    return false;
                                }
                            }
                            newSalesUsermap.set(el.id, el.text, el.fynancial_year, el
                                .fynancial_year_name);
                            return true;
                        });

                        arrUnique.forEach((item, index) => {
                            var selected_user = index == 0 ? 'checked' : '';
                            sales_user_list.innerHTML +=
                                ` <input type="radio" class="btn-check shadow-none" name="q_tv_sales_user" id="sales_user_${item['text']}" value="${item['id']}" fy_id="${item['fynancial_year']}" fy_name="${item['fynancial_year_name']}" autocomplete="off" ${selected_user}>
                                    <label class="btn btn-outline-primary shadow-none" for="sales_user_${item['text']}">${item['text']}</label>`;
                        });

                        $("#sales_user_id").val($('input[name=q_tv_sales_user]:checked').val());


                        var fy_id = $('input[name=q_tv_sales_user]:checked').attr('fy_id');
                        var fy_name = $('input[name=q_tv_sales_user]:checked').attr('fy_name');
                        // $("#q_tv_fy_id").empty().trigger('change');
                        var newOption = new Option(fy_name, fy_id, false, false);
                        $('#q_tv_fy_id').append(newOption).trigger('change');


                        $('input[type=radio][name=q_tv_sales_user]').change(function() {

                            var fy_id = $(this).attr('fy_id');
                            var fy_name = $(this).attr('fy_name');
                            $("#sales_user_id").val($('input[name=q_tv_sales_user]:checked')
                                .val());
                            // $("#q_tv_fy_id").empty().trigger('change');
                            var newOption = new Option(fy_name, fy_id, false, false);
                            $('#q_tv_fy_id').append(newOption).trigger('change');
                            // reset_data();

                            // var start_year = fy_name.split("-")[0];
                            // var end_year = fy_name.split("-")[1];
                            // var start_date = '01-04-' + start_year;
                            // var end_date = '31-03-' + end_year;

                            // setTargetViewData($("#sales_user_id").val(), start_date, end_date, fy_id, $("#data_view_type_val").val());
                        });

                    } else {
                        toastr["error"](resultData['msg']);

                    }
                }
            });
        });
        $.ajax({
            type: 'GET',
            url: ajaxURLSearchTVSalesUser,
            data: {},

            success: function(resultData) {
                if (resultData['status'] == 1) {

                    sales_user_list = document.getElementById('q_sales_user_list');
                    sales_user_list.innerHTML = '';

                    let newSalesUsermap = new Map();
                    const arrUnique = resultData['data'].filter(el => {
                        const val = newSalesUsermap.get(el.id);
                        if (val) {
                            if (el.id < val) {
                                newSalesUsermap.delete(el.name);
                                newSalesUsermap.set(el.id, el.text, el.fynancial_year, el
                                    .fynancial_year_name);
                                return true;
                            } else {
                                return false;
                            }
                        }
                        newSalesUsermap.set(el.id, el.text, el.fynancial_year, el
                            .fynancial_year_name);
                        return true;
                    });

                    arrUnique.forEach((item, index) => {
                        var selected_user = (sales_user_id != 0) ? (item['id'] == sales_user_id) ?
                            'checked' : '' : index == 0 ? 'checked' : '';
                        sales_user_list.innerHTML +=
                            `
                        <input type="radio" class="btn-check shadow-none" name="q_tv_sales_user" id="sales_user_${item['text']}" value="${item['id']}" fy_id="${item['fynancial_year']}" fy_name="${item['fynancial_year_name']}" autocomplete="off" ${selected_user}>
                        <label class="btn btn-outline-primary shadow-none" for="sales_user_${item['text']}">${item['text']}</label>`;
                    });

                    $("#sales_user_id").val($('input[name=q_tv_sales_user]:checked').val());


                    var fy_id = $('input[name=q_tv_sales_user]:checked').attr('fy_id');
                    var fy_name = $('input[name=q_tv_sales_user]:checked').attr('fy_name');
                    // $("#q_tv_fy_id").empty().trigger('change');
                    var newOption = new Option(fy_name, fy_id, false, false);
                    $('#q_tv_fy_id').append(newOption).trigger('change');


                    $('input[type=radio][name=q_tv_sales_user]').change(function() {

                        var fy_id = $(this).attr('fy_id');
                        var fy_name = $(this).attr('fy_name');
                        $("#sales_user_id").val($('input[name=q_tv_sales_user]:checked').val());
                        // $("#q_tv_fy_id").empty().trigger('change');
                        var newOption = new Option(fy_name, fy_id, false, false);
                        $('#q_tv_fy_id').append(newOption).trigger('change');
                        // reset_data();

                        // var start_year = fy_name.split("-")[0];
                        // var end_year = fy_name.split("-")[1];
                        // var start_date = '01-04-' + start_year;
                        // var end_date = '31-03-' + end_year;

                        // setTargetViewData($("#sales_user_id").val(), start_date, end_date, fy_id, $("#data_view_type_val").val());
                    });

                } else {
                    toastr["error"](resultData['msg']);

                }
            }
        });


        var ajaxURLSearchTVFY = '{{ route('search.financial.year.target.view') }}';
        $("#q_tv_fy_id").select2({
            ajax: {
                url: ajaxURLSearchTVFY,
                dataType: 'json',
                delay: 0,
                data: function(params) {
                    return {
                        target_customer: $("#sales_user_id").val(),
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
            dropdownParent: $("#modalTargetView"),
        }).on('change', function(e) {
            if (this.value != '') {
                var start_year = $("#q_tv_fy_id").select2('data')[0].text.split("-")[0];
                var end_year = $("#q_tv_fy_id").select2('data')[0].text.split("-")[1];
                var start_date = '01-04-' + start_year;
                var end_date = '31-03-' + end_year;

                setTargetViewData($("#sales_user_id").val(), start_date, end_date, $("#q_tv_fy_id").val(), $(
                    "#data_view_type_val").val());
            }
        });
    }

    function setTargetViewData(sales_user_id, start_date, end_date, financial_year_id, data_view_type) {
        var ajaxURLTargetViewData = '{{ route('target.view.data') }}';
        $.ajax({
            type: 'POST',
            url: ajaxURLTargetViewData,
            data: {
                "sales_user_id": sales_user_id,
                "start_date": start_date,
                "end_date": end_date,
                "financial_year_id": financial_year_id,
                "data_view_type": data_view_type,
                "_token": csrfToken,
            },

            success: function(resultData) {
                if (resultData['status'] == 1) {
                    // toastr["success"](resultData['msg']);

                    monthly_report = document.getElementById('monthly_report');
                    monthly_report.innerHTML = resultData['monthly_detail'];

                    quterly_report = document.getElementById('quterly_report');
                    quterly_report.innerHTML = resultData['quterly_detail'];

                    yearly_report = document.getElementById('yearly_report');
                    yearly_report.innerHTML = resultData['yearly_detail'];

                } else {
                    toastr["error"](resultData['msg']);
                    reset_data();

                }
            }
        });
    }

    function saveTargetFreez(target_detail_id, month_number) {

        var free_btn_id = $('#' + month_number + '_btn_freeze');
        var achive_amt_id = $('#' + month_number + '_achieved_amt');
        var isfreez = $('#' + month_number + '_btn_freeze').attr('data-isfreeze');


        if (isfreez == 1) {
            // _achieved_amt
            $(free_btn_id).html(
                '<i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i> <span>Un Freez</span>');

            var ajaxURLTargetFreezSave = '{{ route('target.view.freez.save') }}';
            $.ajax({
                type: 'POST',
                url: ajaxURLTargetFreezSave,
                data: {
                    "type": 'UNFREEZE',
                    "target_detail_id": target_detail_id,
                    "freez_amount": 0,
                    "_token": csrfToken,
                },

                success: function(resultData) {
                    if (resultData['status'] == 1) {
                        toastr["success"](resultData['msg']);

                        var start_year = $("#q_tv_fy_id").select2('data')[0].text.split("-")[0];
                        var end_year = $("#q_tv_fy_id").select2('data')[0].text.split("-")[1];
                        var start_date = '01-04-' + start_year;
                        var end_date = '31-03-' + end_year;

                        setTargetViewData($("#sales_user_id").val(), start_date, end_date, $("#q_tv_fy_id")
                            .val(), 3);
                    } else {
                        toastr["error"](resultData['msg']);

                    }
                }
            });


        } else if (isfreez == 0) {
            $(free_btn_id).html(
                '<i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i> <span>Freez</span>');

            var freesamount = $('#' + month_number + '_achieved_amt').val();
            var ajaxURLTargetFreezSave = '{{ route('target.view.freez.save') }}';
            $.ajax({
                type: 'POST',
                url: ajaxURLTargetFreezSave,
                data: {
                    "type": 'FREEZE',
                    "target_detail_id": target_detail_id,
                    "freez_amount": freesamount,
                    "_token": csrfToken,
                },

                success: function(resultData) {
                    if (resultData['status'] == 1) {
                        toastr["success"](resultData['msg']);

                        var start_year = $("#q_tv_fy_id").select2('data')[0].text.split("-")[0];
                        var end_year = $("#q_tv_fy_id").select2('data')[0].text.split("-")[1];
                        var start_date = '01-04-' + start_year;
                        var end_date = '31-03-' + end_year;

                        setTargetViewData($("#sales_user_id").val(), start_date, end_date, $("#q_tv_fy_id")
                            .val(), 3);
                    } else {
                        toastr["error"](resultData['msg']);

                    }
                }
            });
        } else {
            toastr["error"]('please contact to admin');
        }

    }

    function reset_data() {

        monthly_report = document.getElementById('monthly_report');
        monthly_report.innerHTML = '';

        var months_list = ["April", "May", "june", "july", "August", "September", "Octomber", "November", "December",
            "January", "February", "March"
        ];

        months_list.forEach((item, index) => {
            monthly_report.innerHTML += `
            <div class="row mb-2">
                <div class="col-2 p-1 align-self-center text-left">
                    <label class="form-label text-dark">${item}</label>
                </div>
                <div class="col-3 p-1">
                    <input type="text" class="form-control monthlytarget" id="${item}_target" placeholder="₹" name="${item}_target" readonly>
                </div>
                <div class="col-3 p-1">
                    <input type="text" class="form-control monthly_achivement" onkeypress="return isNumber(event); monthlyachieved" id="${item}_achieved_amt" placeholder="₹" name="${item}_achieved_amt" readonly>
                    </div>
                    <div class="col-2 p-1">
                    <input type="text" class="form-control monthlyper" id="${item}_achieved_per" placeholder="%" name="${item}_achieved_per" readonly>
                    </div>
                    <div class="col-1 p-1">
                    <button class="btn btn-primary waves-effect waves-light freezbutton" style="display:none;" id="${item}_btn_freeze" type="">Freeze</button>
                </div>
            </div>`;
        });


        quterly_report = document.getElementById('quterly_report');
        quterly_report.innerHTML = '';

        var quterly_list = ["Q1", "Q2", "Q3", "Q4"];

        quterly_list.forEach((item, index) => {
            quterly_report.innerHTML += `
            <div class="row mb-2 text-center">
            <div class="col-2 p-1 align-self-center">
            <label class="form-label text-dark">${item}</label>
                </div>
                <div class="col-3 p-1">
                    <input type="text" class="form-control" placeholder="₹" readonly>
                </div>
                <div class="col-3 p-1">
                <input type="text" class="form-control" onkeypress="return isNumber(event);" placeholder="₹" readonly>
                </div>
                <div class="col-2 p-1">
                <input type="text" class="form-control" placeholder="%" readonly>
                </div>
                </div>`;
        });

    }

    function isNumber(evt) {
        evt = (evt) ? evt : window.event;
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            return false;
        }
        return true;
    }

    function isRemaining(total_target) {
        var Monthly_Target_Input_array = document.getElementsByClassName('monthly_dist');
        var remaining_amount = 00;
        Monthly_Target_Input_array.forEach(element => {
            var element_id = $(element).attr('id');

            remaining_amount += parseFloat($("#" + element_id).val());
        });
        remaining_amount = parseFloat(total_target - remaining_amount).toFixed(2);
        $('#remaining_value').val(remaining_amount);
    }
</script>

<script type="text/javascript">
    var ajaxURLSearchSalesUser = '{{ route('search.salesperson.ajax') }}';
    var ajaxURLSearchUserJoiningDate = '{{ route('search.joining.date.ajax') }}';
    $("#q_employee_id").select2({
        ajax: {
            url: ajaxURLSearchSalesUser,
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
        placeholder: 'Select Employee',
        dropdownParent: $("#modalAddEditTarget"),
    }).on('change', function(e) {
        var employee_id = this.value;
        ajaxURLSearchUserJoiningDate
        $.ajax({
            type: 'POST',
            url: ajaxURLSearchUserJoiningDate,
            data: {
                "_token": csrfToken,
                "employee_id": employee_id,
            },
            success: function(resultData) {
                if (resultData['status'] == 1) {
                    $('#q_joining_date').val(resultData['data']['joining_date']);
                    if ($('#q_joining_date').val() != '') {
                        $('#q_joining_date').attr('readonly', '');
                        $('#q_joining_date').prop('disabled', true);
                    }

                } else {
                    if ($('#q_joining_date').val() != '') {
                        $('#q_joining_date').attr('readonly', '');
                        $('#q_joining_date').prop('disabled', true);
                    }
                }
            }
        });
    });

    var ajaxURLSearchFY = '{{ route('search.financial.year.ajax') }}';
    $("#q_fy_id").select2({
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
        dropdownParent: $("#modalAddEditTarget"),
    }).on('change', function(e) {

        if (this.value != '' && $('#q_joining_date').val() != '') {

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
        }
    });

    $("#q_joining_date").on("keyup change", function(e) {
        if (this.value != '' && $('#q_fy_id').val() != null) {
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
        }
    });

    function joining_date_validation(joining_date, financial_year) {
        console.log('Joining Date : ' + joining_date);
        console.log('Financial Year : ' + financial_year);
        var isavailable = dateisavailableOnFinancialYear(joining_date, financial_year);
        if (isavailable) {

        }
    }

    function dateisavailableOnFinancialYear(joining_date, financial_year) {
        var fy_start_date = getDateFromFinancialYear(financial_year, 0);
        var fy_end_date = getDateFromFinancialYear(financial_year, 1);

        var start_date = fy_start_date.split("-");
        var end_date = fy_end_date.split("-");
        var check_date = joining_date.split("-");

        var from = new Date(start_date[2], parseInt(start_date[1]) - 1, start_date[
            0]); // -1 because months are from 0 to 11
        var to = new Date(end_date[2], parseInt(end_date[1]) - 1, end_date[0]);
        var check = new Date(check_date[2], parseInt(check_date[1]) - 1, check_date[0]);

        // console.log('FY Start : ' + fy_start_date);
        // console.log('FY End : ' + fy_end_date);
        // console.log('From : ' + from);
        // console.log('To : ' + to);
        // console.log('Check : ' + check);
        // console.log(check >= from && check <= to);
        return check >= from && check <= to;
    }

    function getDateFromFinancialYear(financialyear, type) {
        var start_year = financialyear.split("-")[0];
        var end_year = financialyear.split("-")[1];

        var start_date = '01-04-' + start_year;
        var end_date = '31-03-' + end_year;

        return (type == 0) ? start_date : end_date;
    }

    function addtarget() {

        resetInputForm();

        $("#canvasTargetMasterLable").html("Add New Target");
        $("#formTargetMaster").hide();
        $(".loadingcls").show();

        $('#modalAddEditTarget').modal("show");
        $("#formTargetMaster").show();
        $(".loadingcls").hide();


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

        });

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


        $('input[type=radio][name=distribute_type]').change(function() {

            $("#distribute_type_value").val(this.value);

            var Monthly_Target_Input_array = document.getElementsByClassName('monthly_dist');
            var Incremental_Per = document.getElementById('q_incremental_per');
            var Distribute_Type = this.value;
            var Total_target = $("#q_total_target").val();
            var Total_Month = 12;
            var Joining_Date = $('#q_joining_date').val();
            var FY_Id = $('#q_fy_id').val();
            var Fy_Text = $('#q_fy_id').select2('data')[0].text;

            distribute_target(Monthly_Target_Input_array, Incremental_Per, Distribute_Type, Total_target,
                Total_Month, Joining_Date, FY_Id, Fy_Text);
        });

    }

    function distribute_target(Monthly_Target_Input_array, Incremental_Per, Distribute_Type, Total_target, Total_Month,
        Joining_Date, FY_Id, Fy_Text) {

        if (Joining_Date != '' && FY_Id != null) {

            var isavailable = dateisavailableOnFinancialYear(Joining_Date, Fy_Text);
            if (isavailable) {
                var check_date = Joining_Date.split("-");
                var check = new Date(check_date[2], parseInt(check_date[1]) - 1, check_date[0]);

                if (Total_target != '') {
                    if (Distribute_Type == 1) { //Distribute Equally
                        var isedit = 0;
                        var month_divide = 0;
                        Monthly_Target_Input_array.forEach(element => {
                            element.removeAttribute("isactive");
                            element.removeAttribute("est-target");
                        });
                        Monthly_Target_Input_array.forEach(element => {
                            var element_id = $(element).attr('id');
                            if (parseInt(check_date[1]) == $(element).attr('no-month')) {
                                isedit = 1;
                            }
                            element.setAttribute("isactive", "0");
                            if (isedit == 1) {
                                element.setAttribute("isactive", "1");
                                // element.removeAttribute("readonly");
                                month_divide++;
                            }
                            element.setAttribute("readonly", "");
                            // $("#" + element_id).val('0');
                            $("#q_incremental_per").val('0');
                        });
                        Monthly_Target_Input_array.forEach(element => {
                            var element_id = $(element).attr('id');
                            var isactive = $(element).attr('isactive');
                            $("#" + element_id).val(0);
                            if (isactive == 1) {

                                $("#" + element_id).val((parseFloat(Total_target) / parseFloat(month_divide))
                                    .toFixed(3));
                            }
                            element.setAttribute("readonly", "");
                            // $("#" + element_id).val(Math.round(parseFloat(this.value) / parseFloat(Total_Month)));
                            $("#q_incremental_per").val('0');
                        });

                    } else if (Distribute_Type == 2) { //Distribute Manually

                        Incremental_Per.setAttribute("readonly", "");
                        Incremental_Per.required = false;
                        var isedit = 0;
                        Monthly_Target_Input_array.forEach(element => {
                            element.removeAttribute("isactive");
                            element.removeAttribute("est-target");
                        });

                        Monthly_Target_Input_array.forEach(element => {
                            var element_id = $(element).attr('id');
                            if (parseInt(check_date[1]) == $(element).attr('no-month')) {
                                isedit = 1;
                            }
                            if (isedit == 1) {
                                element.removeAttribute("readonly");
                            } else {
                                element.setAttribute("readonly", "");
                                $("#" + element_id).val('0');
                            }
                        });
                        $("#q_incremental_per").val('0');


                    } else if (Distribute_Type == 3) { //Distribute Incremental


                        Incremental_Per.removeAttribute("readonly");
                        Incremental_Per.required = true;

                        Monthly_Target_Input_array.forEach(element => {
                            element.removeAttribute("isactive");
                            element.removeAttribute("est-target");
                        });

                        var isedit = 0;
                        var isactive_count = 0;
                        Monthly_Target_Input_array.forEach((element, index) => {
                            var element_id = $(element).attr('id');
                            if (parseInt(check_date[1]) == $(element).attr('no-month')) {
                                isedit = 1;
                            }
                            element.setAttribute("isactive", "0");
                            if (isedit == 1) {
                                isactive_count++;
                                element.setAttribute("isactive", isactive_count);
                            }
                            element.setAttribute("readonly", "");
                            // $("#" + element_id).val('0');
                        });

                        var pre_est_target = 0;
                        var pre_est_target_total = 0;
                        Monthly_Target_Input_array.forEach((element, index) => {
                            var element_id = $(element).attr('id');
                            var element_isactive = $(element).attr('isactive');
                            $("#" + element_id).val('0');
                            element.setAttribute("readonly", "");

                            if (element_isactive > 0) {
                                if (element_isactive == 1) {
                                    element.setAttribute("est-target", "100");
                                    pre_est_target_total = 100;
                                } else {
                                    var est_target = ((parseFloat(pre_est_target) * parseFloat($(
                                        '#q_incremental_per').val()) / 100) + parseFloat(
                                        pre_est_target)).toFixed(5);
                                    element.setAttribute("est-target", est_target);
                                    pre_est_target_total += parseFloat(est_target);
                                }


                                if (element_isactive == 1) {
                                    pre_est_target = 100;
                                } else {
                                    pre_est_target = ((parseFloat(pre_est_target) * parseFloat($(
                                        '#q_incremental_per').val()) / 100) + parseFloat(
                                        pre_est_target)).toFixed(5);
                                }
                            }
                        });

                        var target_sum = 0;
                        Monthly_Target_Input_array.forEach((element, index) => {
                            var element_id = $(element).attr('id');
                            var element_isactive = $(element).attr('isactive');
                            element.setAttribute("readonly", "");

                            if (element_isactive > 0) {
                                var est_target = $(element).attr('est-target');
                                console.log("Total Target : " + Total_target);
                                console.log("Est Target : " + est_target);
                                console.log("Est Total Target : " + pre_est_target_total);
                                var month_target = (parseFloat(Total_target) * parseFloat(est_target) /
                                    parseFloat(pre_est_target_total).toFixed(4)).toFixed(4);
                                $("#" + element_id).val(month_target);

                                target_sum += parseFloat(month_target).toFixed(4);
                            }

                        });
                        console.log("Target SUM  : " + target_sum)
                    }
                }
            } else {
                if (Total_target != '') {
                    if (Distribute_Type == 1) { //Distribute Equally
                        Incremental_Per.setAttribute("readonly", "");
                        Incremental_Per.required = false;
                        Monthly_Target_Input_array.forEach(element => {
                            element.setAttribute("readonly", "");
                            var element_id = $(element).attr('id');
                            // $("#" + element_id).val(Math.round(parseFloat(Total_target) / parseFloat(Total_Month)));
                            $("#" + element_id).val((parseFloat(Total_target) / parseFloat(Total_Month))
                                .toFixed(3));
                        });
                        $("#q_incremental_per").val('0');

                    } else if (Distribute_Type == 2) { //Distribute Manually
                        Incremental_Per.setAttribute("readonly", "");
                        Incremental_Per.required = false;
                        Monthly_Target_Input_array.forEach(element => {
                            element.removeAttribute("readonly");
                            var element_id = $(element).attr('id');
                            // $("#" + element_id).val('0');
                        });
                        $("#q_incremental_per").val('0');

                    } else if (Distribute_Type == 3) { //Distribute Incremental
                        Incremental_Per.removeAttribute("readonly");
                        Incremental_Per.required = true;

                        var pre_est_target = 0;
                        var pre_est_target_total = 0;
                        Monthly_Target_Input_array.forEach((element, index) => {
                            var element_id = $(element).attr('id');
                            $("#" + element_id).val('0');
                            element.setAttribute("readonly", "");
                            if (index == 0) {
                                element.setAttribute("est-target", "100");
                                pre_est_target_total = 100;
                            } else {
                                var est_target = ((parseFloat(pre_est_target) * parseFloat($(
                                        '#q_incremental_per').val()) / 100) + parseFloat(pre_est_target))
                                    .toFixed(5);
                                element.setAttribute("est-target", est_target);
                                pre_est_target_total += parseFloat(est_target);
                            }


                            if (index == 0) {
                                pre_est_target = 100;
                            } else {
                                pre_est_target = ((parseFloat(pre_est_target) * parseFloat($(
                                        '#q_incremental_per').val()) / 100) + parseFloat(pre_est_target))
                                    .toFixed(5);
                            }
                        });

                        var target_sum = 0;
                        Monthly_Target_Input_array.forEach((element, index) => {
                            var element_id = $(element).attr('id');
                            $("#" + element_id).val('0');
                            element.setAttribute("readonly", "");

                            var est_target = $(element).attr('est-target');
                            // console.log("Total Target : " + Total_target);
                            // console.log("Est Target : " + est_target);
                            // console.log("Est Total Target : " + pre_est_target_total);
                            var month_target = (parseFloat(Total_target) * parseFloat(est_target) / parseFloat(
                                pre_est_target_total).toFixed(4)).toFixed(4);
                            $("#" + element_id).val(month_target);

                            target_sum += parseFloat(month_target).toFixed(4);

                        });
                        // console.log("Target SUM  : " + target_sum);


                    }
                }
            }
        }
    }
</script>
