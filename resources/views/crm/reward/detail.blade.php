<div class="row mb-3 align-items-center">
    <div class="w-auto">
        <h2 class="">#{{ 'D' . $data['lead']['id'] }} {{ ucwords(strtolower($data['lead']['first_name'])) }}
            {{ ucwords(strtolower($data['lead']['last_name'])) }}</h2>
    </div>
    <div class="col-5 ms-1">
        <div class="bg-light p-1 border-info" style="width: fit-content;"
            onclick="getRewardBillStatus({{ $data['lead']['id'] }})">
            <div class="d-flex" style="height: auto;">
                <div style="border-right: 1px solid #c2ccff;" class="pe-2">
                    <span class="text-success">Claimed Pt. :- <span class=""
                            id="point_claimed_count"></span></span>
                </div>
                <div style="border-right: 1px solid #c2ccff;" class="px-2">
                    <span class="text-warning">Query Pt. :- <span class="" id="point_query_count"></span></span>
                </div>
                <div style="" class="px-2">
                    <span class="text-danger">Lapsed Pt. :- <span class="" id="point_lapsed_count"></span></span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-3 col-lg-6 col-xl-3">
        <ul class="nav nav-tabs border-0 rounded-pill p-0" role="tablist" style="background: #f1f1f1;">

            <li class="nav-item w-50">
                <a class="nav-link border-0 text-center rounded-pill active" data-bs-toggle="tab" href="#home"
                    role="tab" aria-selected="true">
                    <span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
                    <span class="d-none d-sm-block">Overview</span>
                </a>
            </li>
            <li class="nav-item w-50">
                <a class="nav-link border-0 text-center rounded-pill" data-bs-toggle="tab" href="#profile"
                    role="tab" aria-selected="false">
                    <span class="d-block d-sm-none"><i class="far fa-user"></i></span>
                    <span class="d-none d-sm-block">Timeline</span>
                </a>
            </li>

        </ul>
    </div>
    <div class="col-9 col-lg-12 col-xl-9">

        <div class="userscomman row text-start align-items-center ps-3">
            @include('crm.reward.detail_tab.detail_status')
        </div>
    </div>
</div>

<div class="d-flex flex-wrap py-2">
    <button type="button" class="btn btn-sm waves-effect waves-light bg-white"
        onclick="smoothScroll(document.getElementById('tab_detail'))">
        <i class="bx bx-detail font-size-16 align-middle me-2"></i> Detail
    </button>
    <button type="button" class="btn btn-sm waves-effect waves-light bg-white"
        onclick="smoothScroll(document.getElementById('tab_contact'))">
        <i class="bx bx-detail font-size-16 align-middle me-2"></i> Contact
    </button>
    <button type="button" class="btn btn-sm waves-effect waves-light bg-white"
        onclick="smoothScroll(document.getElementById('tab_quotation_detail'))">
        <i class="bx bx-detail font-size-16 align-middle me-2"></i> Quotation
    </button>
    <button type="button" class="btn btn-sm waves-effect waves-light bg-white"
        onclick="smoothScroll(document.getElementById('tab_files_detail'))">
        <i class="bx bx-detail font-size-16 align-middle me-2"></i> Files
    </button>
    <button type="button" class="btn btn-sm waves-effect waves-light bg-white"
        onclick="smoothScroll(document.getElementById('tab_notes'))">
        <i class="bx bx bx-note font-size-16 align-middle me-2"></i> Notes
    </button>
    <button type="button" class="btn btn-sm waves-effect waves-light bg-white"
        onclick="smoothScroll(document.getElementById('tele_sales_tab_detail'))">
        <i class="bx bx-detail font-size-16 align-middle me-2"></i> TeleSales Approved
    </button>
    <button type="button" class="btn btn-sm waves-effect waves-light bg-white"
        onclick="smoothScroll(document.getElementById('service_user_tab_detail'))">
        <i class="bx bx-detail font-size-16 align-middle me-2"></i> Service User Approved
    </button>
    <button type="button" class="btn btn-sm waves-effect waves-light bg-white"
        onclick="smoothScroll(document.getElementById('company_admin_tab_detail'))">
        <i class="bx bx-detail font-size-16 align-middle me-2"></i> Company Admin Approved
    </button>
    <button type="button" class="btn btn-sm waves-effect waves-light bg-white"
        onclick="smoothScroll(document.getElementById('bill_tab_detail'))">
        <i class="bx bx-detail font-size-16 align-middle me-2"></i>Bill
    </button>
    {{-- <button type="button" class="btn btn-sm waves-effect waves-light bg-white"
        onclick="smoothScroll(document.getElementById('tab_action'))">
        <i class="bx bx-detail font-size-16 align-middle me-2"></i>Open Action
    </button>
    <button type="button" class="btn btn-sm waves-effect waves-light bg-white"
        onclick="smoothScroll(document.getElementById('tab_close_action'))">
        <i class="bx bx-detail font-size-16 align-middle me-2"></i>Close Action
    </button> --}}
</div>

<div class="tab-content py-2 text-muted" style="overflow-y: scroll;overflow-x: hidden;">
    <div class="tab-pane active" id="home" role="tabpanel">
        <input type="hidden" name="lead_hidden_id" id="lead_hidden_id" value="{{ $data['lead']['id'] }}">
        <div class="card lead-detail" style="border-radius: 10px;" id="tab_detail">
            @include('crm.reward.detail_tab.detail_tab')
        </div>
        <div class="card lead-detail" style="border-radius: 10px;" id="tab_contact">
            @include('crm.reward.detail_tab.detail_contact_tab')
        </div>
        <div class="card lead-detail" style="border-radius: 10px;" id="tab_quotation_detail">
            @include('crm.reward.detail_tab.detail_quotation_tab')
        </div>
        <div class="card lead-detail" style="border-radius: 10px;" id="tab_files_detail">
            @include('crm.reward.detail_tab.detail_file_tab')
        </div>
        <div class="card lead-detail" style="border-radius: 10px;" id="tab_notes">
            @include('crm.reward.detail_tab.detail_notes_tab')
        </div>
        <div class="card lead-detail" style="border-radius: 10px;" id="tele_sales_tab_detail">
            @include('crm.reward.detail_tab.detail_telesales_tab')
        </div>
        <div class="card lead-detail" style="border-radius: 10px;" id="service_user_tab_detail">
            @include('crm.reward.detail_tab.detail_service_tab')
        </div>
        <div class="card lead-detail" style="border-radius: 10px;" id="company_admin_tab_detail">
            @include('crm.reward.detail_tab.detail_company_admin_tab')
        </div>
        <div class="card lead-detail" style="border-radius: 10px;" id="bill_tab_detail">
            @include('crm.reward.detail_tab.detail_bill_tab')
        </div>
        {{-- <div class="card lead-detail" style="border-radius: 10px;" id="tab_action">
            @include('crm.reward.detail_tab.detail_open_action_tab')
        </div>

        <div class="card lead-detail" style="border-radius: 10px;" id="tab_close_action">
            @include('crm.reward.detail_tab.detail_close_action_tab')
        </div> --}}
        {{-- <div class="card lead-detail" style="border-radius: 10px;" id="hod_tab_detail">
            @include('crm.reward.detail_tab.detail_hod_tab')
        </div> --}}
    </div>
    <div class="card lead-detail tab-pane" style="border-radius: 10px;" id="profile" role="tabpanel">
        <div class="card-body">
            <ul class="verti-timeline list-unstyled">
                @foreach ($data['timeline'] as $timeline)
                    @if ($timeline['date'] != 0)
                        <li class="event-list period">
                            <div class="timeline-info"></div>
                            <div class="timeline-marker"></div>
                            <p class="timeline-title">{{ $timeline['date'] }}</p>
                        </li>
                    @endif
                    <li class="event-list">
                        <div class="timeline-info"> <span>{{ $timeline['time'] }}</span> </div>
                        @if ($timeline['source'] == 'WEB')
                            <div class="timeline-marker-web"></div>
                        @elseif($timeline['source'] == 'ANDROID')
                            <div class="timeline-marker-android"></div>
                        @elseif($timeline['source'] == 'IPHONE')
                            <div class="timeline-marker-iphone"></div>
                        @else
                            <div class="timeline-marker"></div>
                        @endif
                        <div class="timeline-content">
                            <p class="">{{ $timeline['description'] }}</p><span>by {{ $timeline['first_name'] }}
                                {{ $timeline['last_name'] }}</span>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTaskView" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1"
    role="dialog" aria-labelledby="modalInquiryLogLabel" aria-hidden="true" style="z-index: 1600;">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLeadLogLabel">Task Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="min-height:100%;">
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Task Title</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="task_title" name="" value="-"
                            readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Task Status</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="task_status" name="" value="-"
                            readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Lead Detail</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="task_lead_detail" name=""
                            value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Created By</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="task_created_by" name=""
                            value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Created At</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="task_created_at" name=""
                            value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Due Date & Time</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="task_due_date_time" name=""
                            value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Description</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="task_description" name=""
                            value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Close Date & Time</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="task_close_date_time" name=""
                            value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Close Note</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="task_close_note" name=""
                            value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Outcome Type</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="task_outcome_type" name=""
                            value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Task Architect</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="task_architect" name=""
                            value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Task Electrician</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="task_electrician" name=""
                            value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Task Additional
                        Info</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="task_additional_info" name=""
                            value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Task Additional Info
                        Text</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="task_additional_info_text" name=""
                            value="-" readonly="readonly">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCallView" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1"
    role="dialog" aria-labelledby="modalInquiryLogLabel" aria-hidden="true" style="z-index: 1600;">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLeadLogLabel">Call Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="min-height:100%;">
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Lead Detail</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="call_lead_detail" name=""
                            value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Call Status</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="call_status" name="" value="-"
                            readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Call Contact</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="call_contact" name="" value="-"
                            readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Call Purpose</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="call_purpose" name="" value="-"
                            readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Call Schedule</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="call_schedule" name="" value="-"
                            readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Description</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="call_description" name=""
                            value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Close Note</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="call_close_note" name=""
                            value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Close Date & Time</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="call_close_date_time" name=""
                            value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Outcome Type</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="call_outcome_type" name=""
                            value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Call Reference</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="call_reference" name=""
                            value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Call Architect</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="call_architect" name=""
                            value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Call Electrician</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="call_electrician" name=""
                            value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Call Additional
                        Info</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="call_additional_info" name=""
                            value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Call Additional Info
                        Text</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="call_additional_info_text" name=""
                            value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Created By</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="call_created_by" name=""
                            value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Created At</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="call_created_at" name=""
                            value="-" readonly="readonly">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    * {
        -webkit-text-size-adjust: none;
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

    .add_new_note {
        background: #b5b5b521;
        border-radius: 5px;
    }

    .add_new_note::-webkit-input-placeholder {
        line-height: 25px;
        color: rgb(79, 79, 79) !important;
    }

    .table-striped>#leadQuotationTBody>tr:nth-of-type(odd)>* {
        --bs-table-accent-bg: white !important
    }

    .button {
        float: left;
        margin: 0 5px 0 0;
        width: 110px;
        height: 40px;
        position: relative;
    }

    .button span,
    .button input {
        display: block;
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
    }

    .button input[type="checkbox"] {
        opacity: 0.011;
        z-index: 100;
        height: 35px;
    }

    .type input[type="checkbox"]:checked+span {
        background: #1f64bae3;
        color: white;
        border-radius: 4px;
    }

    .titlecheckbox input[type="checkbox"]:checked+label {
        color: #3673c0;
    }

    .button span {
        cursor: pointer;
        z-index: 90;
        color: #878787;
        font-weight: 700;
        line-height: 1.5em;
        background-color: #fff;
    }

    .c-white {
        color: white !important;
    }

    .appendixmark {
        font-size: 8pt;
        border: 1px solid #bb6161;
        border-radius: 15px;
        background-color: #bb6161;
        position: relative;
        top: 0px;
        padding: 0px 4px 0px 4px;
        color: white;
    }

    .checkbox {
        margin-right: 10px;
    }

    .label-text {
        font-size: 1rem;
    }

    .input-checkbox {
        width: 10px;
    }

    .vh {
        position: absolute !important;
        clip: rect(1px, 1px, 1px, 1px);
        padding: 0 !important;
        border: 0 !important;
        height: 1px !important;
        width: 1px !important;
        overflow: hidden;
    }

    input[type="checkbox"]:checked~label:before {
        vertical-align: middle;
        background: #3673c0 no-repeat center;
        background-size: 9px 9px;
        background-image: url(data:image/svg+xml;utf8;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pgo8IS0tIEdlbmVyYXRvcjogQWRvYmUgSWxsdXN0cmF0b3IgMTYuMC4wLCBTVkcgRXhwb3J0IFBsdWctSW4gLiBTVkcgVmVyc2lvbjogNi4wMCBCdWlsZCAwKSAgLS0+CjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+CjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4PSIwcHgiIHk9IjBweCIgd2lkdGg9IjE2cHgiIGhlaWdodD0iMTZweCIgdmlld0JveD0iMCAwIDQ1LjcwMSA0NS43IiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCA0NS43MDEgNDUuNzsiIHhtbDpzcGFjZT0icHJlc2VydmUiPgo8Zz4KCTxnPgoJCTxwYXRoIGQ9Ik0yMC42ODcsMzguMzMyYy0yLjA3MiwyLjA3Mi01LjQzNCwyLjA3Mi03LjUwNSwwTDEuNTU0LDI2LjcwNGMtMi4wNzItMi4wNzEtMi4wNzItNS40MzMsMC03LjUwNCAgICBjMi4wNzEtMi4wNzIsNS40MzMtMi4wNzIsNy41MDUsMGw2LjkyOCw2LjkyN2MwLjUyMywwLjUyMiwxLjM3MiwwLjUyMiwxLjg5NiwwTDM2LjY0Miw3LjM2OGMyLjA3MS0yLjA3Miw1LjQzMy0yLjA3Miw3LjUwNSwwICAgIGMwLjk5NSwwLjk5NSwxLjU1NCwyLjM0NSwxLjU1NCwzLjc1MmMwLDEuNDA3LTAuNTU5LDIuNzU3LTEuNTU0LDMuNzUyTDIwLjY4NywzOC4zMzJ6IiBmaWxsPSIjRkZGRkZGIi8+Cgk8L2c+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPC9zdmc+Cg==);
    }

    .label-text::before {
        content: '';
        width: 15px;
        height: 15px;
        background: #f2f2f2;
        border: 1px solid rgba(75, 101, 132, 0.3);
        display: inline-block;
        margin-right: 10px;
    }

    .error-border {
        border: 1px solid #ffb1b1 !important;
    }
</style>

<script type="text/javascript">
    var ajaxURLTaskDetail = "{{ route('reward.task.detail') }}";
    var ajaxURLCallDetail = "{{ route('reward.call.detail') }}";

    function TaskDetail(id) {
        $('#modalTaskView').modal('show');
        $.ajax({
            type: 'GET',
            url: ajaxURLTaskDetail + "?id=" + id,
            success: function(resultData) {
                if (resultData['status'] == 1) {
                    var data = resultData['data'];
                    $('#task_title').val(data['task']);

                    if (data['is_closed'] == 0) {
                        $('#task_status').val('Open');
                    } else {
                        $('#task_status').val('Close');
                    }

                    $('#task_lead_detail').val(data['lead_detail']);
                    $('#task_created_by').val(data['created_by']);
                    $('#task_created_at').val(data['created_at']);
                    $('#task_due_date_time').val(data['due_date_time']);
                    $('#task_description').val(data['description']);
                    $('#task_close_date_time').val(data['closed_date_time']);
                    $('#task_close_note').val(data['close_note']);
                    $('#task_outcome_type').val(data['outcome_type']);
                    $('#task_architect').val(data['architect_name']);
                    $('#task_electrician').val(data['electrician_name']);
                    $('#task_additional_info').val(data['additional_info']);
                    $('#task_additional_info_text').val(data['additional_info_text']);
                }
            }
        });
    }

    function CallDetail(id) {
        $('#modalCallView').modal('show');
        $.ajax({
            type: 'GET',
            url: ajaxURLCallDetail + "?id=" + id,
            success: function(resultData) {
                if (resultData['status'] == 1) {
                    var data = resultData['data'];

                    if (data['is_closed'] == 0) {
                        $('#call_status').val('Open');
                    } else {
                        $('#call_status').val('Close');
                    }

                    $('#call_contact').val(data['contact_name']);
                    $('#call_purpose').val(data['purpose']);
                    $('#call_schedule').val(data['call_schedule']);
                    $('#call_lead_detail').val(data['lead_detail']);
                    $('#call_created_by').val(data['created_by']);
                    $('#call_created_at').val(data['created_at']);
                    $('#call_description').val(data['description']);
                    $('#call_close_date_time').val(data['closed_date_time']);
                    $('#call_close_note').val(data['close_note']);
                    $('#call_outcome_type').val(data['outcome_type']);
                    $('#call_reference').val(data['reference_type']);
                    $('#call_architect').val(data['architect_name']);
                    $('#call_electrician').val(data['electrician_name']);
                    $('#call_additional_info').val(data['additional_info']);
                    $('#call_additional_info_text').val(data['additional_info_text']);
                }
            }
        });
    }

    $(document).ready(function() {
        adjustContainerHeight();
        $(window).on('resize', adjustContainerHeight);
    });

    function adjustContainerHeight() {
        var windowWidth = $(window).width();
        if (windowWidth <= 1440) {
            $('body').addClass('vertical-collpsed');
        }
        var windowHeight = $(window).height() - 100;
        max_height = windowHeight - 180;
        $('#datatable').parent().css('max-height', max_height + 'px');
        $('#datatable').parent().css('height', max_height + 'px');
        $('#home').parent().css('max-height', max_height + 'px');
        $('#custom_height').css('height', windowHeight + 'px');
    }
</script>
