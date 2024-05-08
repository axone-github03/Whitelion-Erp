<style>
    /* input[type="radio"]:checked::before {
        content: none !important;
    } */
</style>

<div class="modal fade" id="modalLeadLog" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" role="dialog" aria-labelledby="modalInquiryLogLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLeadLogLabel">Lead List</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="min-height:100%;">
                <div class="col-12 align-self-center d-flex justify-content-around">
                    <input class="form-control" type="hidden" name="data_view_type_val" id="data_view_type_val" value="0">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="data_view_type"
                            id="distribute_all" checked>
                        <label class="col-form-label" style="padding-top: 0px;" for="distribute_all">All</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="data_view_type"
                            id="distribute_last_month" value="1">
                        <label class="col-form-label" style="padding-top: 0px;" for="distribute_last_month">Last Month</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="data_view_type"
                            id="distribute_this_month" value="2">
                        <label class="col-form-label" style="padding-top: 0px;" for="distribute_this_month">This Month</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="data_view_type"
                            id="distribute_last_year" value="3">
                        <label class="col-form-label" style="padding-top: 0px;" for="distribute_last_year">Last Year</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="data_view_type"
                            id="distribute_this_year" value="4">
                        <label class="col-form-label" style="padding-top: 0px;" for="distribute_this_year">This Year</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="data_view_type"
                            id="distribute_custom_date" value="5">
                        <label class="col-form-label" style="padding-top: 0px;" for="distribute_custom_date">Custom Date</label>
                    </div>
                </div>
                <div style="margin-bottom: 20px;"></div>
                <div class="row mb-1">
                    <div class="col-6">
                        <div class="mb-3">
                            <div class="input-daterange input-group" id="div_start_end_datepicker" data-date-format="dd-mm-yyyy"
                                data-date-autoclose="true" data-provide="datepicker"
                                data-date-container='#div_start_end_datepicker'>
                                <input type="text" class="form-control" name="start_date" id="start_date"
                                    value="@php echo date('01-m-Y'); @endphp" placeholder="Start Date" />
                                <input type="text" class="form-control" name="end_date" id="end_date" placeholder="End Date"
                                    value="@php echo date('t-m-Y'); @endphp" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row text-center mb-3">
                    <div class="col-3">
                        <h5 class="mb-0" id="totalLead">0</h5>
                        <button class="btn btn-primary btn-sm inquiry-log-active" id="btnLeadLogTotal">Total
                            Lead</button>
                    </div>
                    <div class="col-3">
                        <h5 class="mb-0" id="totalRunningLead">0</h5>
                        <button class="btn btn-primary btn-sm" id="btnLeadLogRunning">Running Lead</button>
                    </div>
                    <div class="col-3">
                        <h5 class="mb-0" id="totalWonLead">0</h5>
                        <button class="btn btn-primary btn-sm" id="btnLeadLogWon">Won Lead</button>
                    </div>
                    <div class="col-3">
                        <h5 class="mb-0" id="totalRejectedLead">0</h5>
                        <button class="btn btn-primary btn-sm" id="btnLeadLogLost">Lost Lead</button>
                    </div>
                </div>
                <div class="float-end">
                    <button type="button" class="btn-sm btn btn-outline-dark waves-effect waves-light float-end"
                        aria-haspopup="true" aria-expanded="false">Quotation Amount: <span
                            id="totalLeadLogQuotationAmount"></span></button>
                </div>

                <table id="LeadLogTable" class="table align-middle table-nowrap mb-0 w-100">
                    <thead>
                        <tr>
                            <th>#Id</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Quotation Amount</th>
                            <th id="user_type_column"></th>
                            <th id="user_type_column1"></th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">
    $(document).ready(function() {
        var data_view_type_value = $("#data_view_type_val").val();
        $('input[type=radio][name=data_view_type]').change(function() {
            $("#data_view_type_val").val(this.value);
            var selectedValue = $(this).val();
            if (selectedValue !== "5") {
                $('.col-6').hide();
            } else {
                $('.col-6').show();
            }
        });
        $('.col-6').hide();
    });
</script>
