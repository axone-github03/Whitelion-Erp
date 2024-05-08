@php
    
    if ($data['lead']['is_deal'] == 0) {
        $prifix = 'L';
    } elseif ($data['lead']['is_deal'] == 1) {
        $prifix = 'D';
    }
@endphp
@if($data['lead']['status'] == 103)
    <div class="row mb-3 align-items-center">
@else
    <div class="row mb-3 align-items-center justify-content-between">
@endif
    <div class="w-auto">
        <h2 class="">#{{ $prifix . $data['lead']['id'] }} {{ ucwords(strtolower($data['lead']['first_name'])) }} {{ ucwords(strtolower($data['lead']['last_name'])) }} {!! $data['lead']['tag_lable'] !!}</h2>
    </div>
    @if($data['lead']['status'] == 103)
        <div class="col-5 ms-1">
            <div class="bg-light p-1 border-info" style="width: fit-content;cursor: pointer;" onclick="getRewardBillStatus({{$data['lead']['id']}})">
                <div class="d-flex" style="height: auto;">
                    <div style="border-right: 1px solid #c2ccff;" class="pe-2">
                        <span class="text-success">Claimed Pt. :- <span class="" id="point_claimed_count"></span></span>
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
    @endif
    <div class="col-4">
        @if ($data['lead']['is_deal'] == 0)
            @if ($data['lead']['is_deal'] == 0 &&  isCreUser() == 0 && (isMarketingUser() == 0))
                <span class="float-end"><button onclick="uploadQuotation({{ $data['lead']['id'] }})" class="btn btn-sm btn-primary">Add Quotation</button> </span>
            @endif
        @endif
    </div>
</div>
{{-- <h2 class="px-3 pb-3">
    
        @if (isAdminOrCompanyAdmin() == 1)
            <span style="display: none;" id="bill_attached_btn"><button onclick="" class="btn btn-sm"
                    style="background-color: #6F8FFF; color: #fff;"><a href="" target="_blank"
                        class="text-light">Bill attached</a></button> </span>
            <span class="float-end ps-2" id="reward_btn" style="display: none;">
                <button onclick="RewardPoint()" class="btn btn-sm" style="background-color: #FF9A03; color: #fff;">
                    Claimed
                </button>
            </span>
        @endif
    @endif

    @if($data['lead']['status'] == 103)
    <span><button onclick="" class="btn btn-sm"
        style="background-color: #6F8FFF; color: #fff;"><a href="javascript:void(0)"
            class="text-light">Claimed ( <span class="" style="font-weight: bold;" id="point_claimed_count">0</span> )</a></button> </span>
            <span><button onclick="" class="btn btn-sm"
                style="background-color: #6F8FFF; color: #fff;"><a href="javascript:void(0)"
                    class="text-light">Query ( <span class="" style="font-weight: bold;" id="point_query_count">0</span> )</a></button> </span>
                    <span><button onclick="" class="btn btn-sm"
                        style="background-color: #6F8FFF; color: #fff;"><a href="javascript:void(0)"
                            class="text-light">Lapsed ( <span class="" style="font-weight: bold;" id="point_lapsed_count">0</span> )</a></button> </span>

                        @endif
</h2> --}}

<div class="row">
    <div class="col-3 col-lg-6 col-xl-3">
        <ul class="nav nav-tabs border-0 rounded-pill p-0" role="tablist" style="background: #f1f1f1;">
            <li class="nav-item w-50">
                <a class="nav-link border-0 text-center rounded-pill active" data-bs-toggle="tab" href="#home" role="tab" aria-selected="true">
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
            @include('crm.lead.detail_tab.detail_status')

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
        <i class="bx bx bxs-contact font-size-16 align-middle me-2"></i> Contact Person
    </button>
    <button type="button" class="btn btn-sm waves-effect waves-light bg-white"
        onclick="smoothScroll(document.getElementById('tab_files'))">
        <i class="bx bx bxs-file-blank font-size-16 align-middle me-2"></i> Files
    </button>
    <button type="button" class="btn btn-sm waves-effect waves-light bg-white"
        onclick="smoothScroll(document.getElementById('tab_notes'))">
        <i class="bx bx bx-note font-size-16 align-middle me-2"></i> Notes
    </button>
    <button type="button" class="btn btn-sm waves-effect waves-light bg-white"
        onclick="smoothScroll(document.getElementById('tab_action'))">
        <i class="bx bx bx-list-ul font-size-16 align-middle me-2"></i> Action
    </button>
</div>

<div class="tab-content py-2 text-muted" style="overflow: scroll;">
    <div class="tab-pane active" id="home" role="tabpanel">

        <input type="hidden" name="lead_hidden_id" id="lead_hidden_id" value="{{ $data['lead']['id'] }}">
        @if ($data['lead']['is_deal'] == 0)
            <div class="card lead-detail" style="border-radius: 10px;" id="tab_detail">
                @include('crm.lead.detail_tab.detail_tab')
            </div>

            <div class="card lead-detail" style="border-radius: 10px;" id="tab_contact">
                @include('crm.lead.detail_tab.detail_contact_tab')
            </div>

            <div class="card lead-detail" style="border-radius: 10px;" id="tab_files">
                @include('crm.lead.detail_tab.detail_file_tab')
            </div>

            <div class="card lead-detail" style="border-radius: 10px;" id="tab_quotation">
                @include('crm.lead.detail_tab.detail_quotation_tab')
            </div>

            <div class="card lead-detail" style="border-radius: 10px;" id="tab_notes">
                @include('crm.lead.detail_tab.detail_notes_tab')
            </div>

            @if (isArchitect() == 1 || isElectrician() == 1)
            <div class="card lead-detail d-none" style="border-radius: 10px;" id="tab_action">
            @else
            <div class="card lead-detail" style="border-radius: 10px;" id="tab_action">
            @endif
                @include('crm.lead.detail_tab.detail_open_action_tab')
            </div>

            @if (isArchitect() == 1 || isElectrician() == 1)
            <div class="card lead-detail d-none" style="border-radius: 10px;" id="tab_close_action">
            @else
            <div class="card lead-detail" style="border-radius: 10px;" id="tab_close_action">
            @endif
                @include('crm.lead.detail_tab.detail_close_action_tab')
            </div>
        @elseif($data['lead']['is_deal'] == 1)
            <div class="card lead-detail" style="border-radius: 10px;" id="tab_quotation">
                @include('crm.lead.detail_tab.detail_quotation_tab')
            </div>

            <div class="card lead-detail" style="border-radius: 10px;" id="tab_contact">
                @include('crm.lead.detail_tab.detail_contact_tab')
            </div>

            <div class="card lead-detail" style="border-radius: 10px;" id="tab_notes">
                @include('crm.lead.detail_tab.detail_notes_tab')
            </div>

            <div class="card lead-detail" style="border-radius: 10px;" id="tab_detail">
                @include('crm.lead.detail_tab.detail_tab')
            </div>

            <div class="card lead-detail" style="border-radius: 10px;" id="tab_files">
                @include('crm.lead.detail_tab.detail_file_tab')
            </div>

            @if (isArchitect() == 1 || isElectrician() == 1)
            <div class="card lead-detail d-none" style="border-radius: 10px;" id="tab_action">
            @else
            <div class="card lead-detail" style="border-radius: 10px;" id="tab_action">
            @endif
                @include('crm.lead.detail_tab.detail_open_action_tab')
            </div>

            @if (isArchitect() == 1 || isElectrician() == 1)
            <div class="card lead-detail d-none" style="border-radius: 10px;" id="tab_close_action">
            @else
            <div class="card lead-detail" style="border-radius: 10px;" id="tab_close_action">
            @endif
                @include('crm.lead.detail_tab.detail_close_action_tab')
            </div>
        @endif
    </div>
    <div class="card lead-detail tab-pane" style="border-radius: 10px;" id="profile" role="tabpanel">
        <div class="card-body">
            <div class="col-3 col-lg-6 col-xl-3">
                <ul class="nav nav-tabs border-0 rounded-pill p-0" role="tablist" style="background: #f1f1f1;">
                    <li class="nav-item w-50">
                        <a class="nav-link border-0 text-center rounded-pill active" data-bs-toggle="tab" href="#question"
                            role="tab" aria-selected="true">
                            <span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
                            <span class="d-none d-sm-block">Question</span>
                        </a>
                    </li>
                    <li class="nav-item w-50">
                        <a class="nav-link border-0 text-center rounded-pill" data-bs-toggle="tab" href="#timeline"
                            role="tab" aria-selected="false">
                            <span class="d-block d-sm-none"><i class="far fa-user"></i></span>
                            <span class="d-none d-sm-block">Timeline</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="tab-content py-2 text-muted" style="overflow: scroll;">
                <div class="card lead-detail tab-pane active" style="border-radius: 10px;" id="question" role="tabpanel">
                    <div class="card-body">
                        <div class="col-12 col-lg-12 col-xl-12">
                            <ul class="verti-timeline list-unstyled">
                                @foreach ($data['question'] as $timeline)
                                    @if ($timeline['date'] != 0)
                                        <li class="event-list period">
                                            <div class="timeline-info"></div>
                                            <div class="timeline-marker"></div>
                                            <p class="timeline-title">{{ $timeline['date'] }}</p>
                                        </li>
                                    @endif
                                    <li class="event-list">
                                        <div class="timeline-info">
                                            <span>{{ $timeline['time'] }}</span>
                                        </div>
                                        <div class="timeline-marker-web"></div>
                                        <div class="timeline-content">
                                            <p class="">{{ $timeline['question'] }}</p><span>{!! $timeline['option'] !!}</span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card lead-detail tab-pane" style="border-radius: 10px;" id="timeline" role="tabpanel">
                    <div class="card-body">
                        <div class="col-12 col-lg-12 col-xl-12">
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
                                        @if($timeline['source'] == "WEB")
                                            <div class="timeline-marker-web"></div>
                                        @elseif($timeline['source'] == "ANDROID")
                                            <div class="timeline-marker-android"></div>
                                        @elseif($timeline['source'] == "IPHONE")
                                            <div class="timeline-marker-iphone"></div>
                                        @else
                                            <div class="timeline-marker"></div>
                                        @endif
                                        <div class="timeline-content">
                                            <p class="">{{ $timeline['description'] }}</p><span>by {{ $timeline['first_name'] }} {{ $timeline['last_name'] }}</span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>

    <!-- Start PDF Filter Modal-->
    <div class="modal fade" id="filtermodal" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalCenterTitle" aria-hidden="true" style="background-color: #0000003b;">
        <div class="modal-dialog modal-dialog-centered justify-content-center" role="document">
            <div class="modal-content" style="background-color: #efefef;width: auto !important;">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Quotation Pdf Filter</h5>
                    <button type="button" class="close btn-close" data-dismiss="modal" aria-label="Close">
                    </button>
                </div>
                <div class="modal-body">
                    <div class="d-flex flex-column mb-2 align-items-center">
                        <div class="titlecheckbox">
                            <input type="checkbox" name="areatitle" id="areatitle"
                                class="vh areasummarytitle"><label class="label-text" for="areatitle">Summary</label>
                        </div>
                        <div class="areasummary" style="display: none">
                            <div class="button type">
                                <input type="checkbox" id="area">
                                <span class="btn btn-default" for="a5">Roomwise</span>
                            </div>
                            <div class="button type">
                                <input type="checkbox" id="product">
                                <span class="btn btn-default" for="a5">Count</span>
                            </div>
                        </div>
                    </div>

                    <div style="border-bottom: 2px solid #ababab"></div>

                    <div class="d-flex flex-column mt-2 mb-2 align-items-center">
                        <div class="titlecheckbox">
                            <input type="checkbox" name="areadetailtitle" id="areadetailtitle"
                                class="vh areadetailtitle"><label class="label-text"
                                for="areadetailtitle">Roomwise</label>
                        </div>
                        <div class="areadetailsummary" style="display: none">
                            <div class="button type">
                                <input type="checkbox" id="areagst" class="allgst" />
                                <span class="btn btn-default" for="a5">GST</span>
                            </div>
                            <div class="button type">
                                <input type="checkbox" id="areadiscount" class="alldiscount" />
                                <span class="btn btn-default" for="a5">Discount</span>
                            </div>
                            <div class="button type">
                                <input type="checkbox" id="arearate" class="allnetamount" />
                                <span class="btn btn-default" for="a5">Net</span>
                            </div>
                        </div>
                    </div>

                    <div style="border-bottom: 2px solid #ababab"></div>

                    <div class="d-flex flex-column  mt-2 mb-2 align-items-center">
                        <div class="titlecheckbox">
                            <input type="checkbox" name="producttitle" id="producttitle"
                                class="vh producttitle"><label class="label-text"
                                for="producttitle">Boardwise</label>
                        </div>
                        <div class="productdetailsummary" style="display: none">
                            <div class="button type">
                                <input type="checkbox" id="productgst" class="allgst" />
                                <span class="btn btn-default" for="a5">GST</span>
                            </div>
                            <div class="button type">
                                <input type="checkbox" id="productdiscount" class="alldiscount" />
                                <span class="btn btn-default" for="a5">Discount</span>
                            </div>
                            <div class="button type">
                                <input type="checkbox" id="productrate" class="allnetamount" />
                                <span class="btn btn-default" for="a5">Net</span>
                            </div>
                        </div>
                    </div>

                    <div style="border-bottom: 2px solid #ababab"></div>

                    <div class="d-flex flex-column mt-2 align-items-center">
                        <div class="titlecheckbox">
                            <input type="checkbox" name="whiteliontitle" id="whiteliontitle"
                                class="vh whiteliontitle"><label class="label-text" for="whiteliontitle">Whitelion
                                And Other</label>
                        </div>
                        <div class="wltandotherdetailsummary" style="display: none">
                            <div class="button type">
                                <input type="checkbox" id="wltandotherproductgst" class="allgst" />
                                <span class="btn btn-default" for="a5">GST</span>
                            </div>
                            <div class="button type">
                                <input type="checkbox" id="wltandotherdiscount" class="alldiscount" />
                                <span class="btn btn-default" for="a5">Discount</span>
                            </div>
                            <div class="button type">
                                <input type="checkbox" id="wltandothernet" class="allnetamount" />
                                <span class="btn btn-default" for="a5">Net</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close" data-dismiss="modal">Close</button>
                    <a href="" class="btn btn-primary" id="itempdfdownload" target="_blank">Download</a>
                    {{-- <button type="button" class="btn btn-primary" id="pdfpreviewshow">View</button> --}}
                </div>
                <input type="hidden" name="" id="Quot_id">
                <input type="hidden" name="" id="Quotgroup_id">
            </div>
        </div>
    </div>
    <!-- End PDF Filter Modal-->
    @include('crm.lead.modal.modal')
</div>

<style>
    * {
        -webkit-text-size-adjust: none;
    }

    .switch {
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

<script>
    var ajaxURLSearchSourceType = "{{ route('crm.lead.search.source.type') }}";
    var ajaxURLSearchSource = "{{ route('crm.lead.search.source') }}";
    var ajaxURLSearchSiteStage = "{{ route('crm.lead.search.site.stage') }}";
    var ajaxURLSearchSiteType = "{{ route('crm.lead.search.site.type') }}";
    var ajaxURLSearchSubStatus = "{{ route('crm.lead.search.sub.status') }}";
    var ajaxURLLeadDetail = "{{ route('crm.lead.detail') }}";
    var ajaxURLUpdateLeadDetail = "{{ route('crm.lead.updatedetail') }}";
    var ajaxURLSearchLeadAndDealTag = "{{ route('crm.lead.search.lead.and.deal.tag') }}";
    var ajaxURLLeadSearchQuestion = "{{ route('crm.lead.search.question') }}";
    

    var PrintDownloadUrl = "{{ route('quot.master.itemwiseprint.download') }}";

    $(".lead_status_field").select2();
    $("#lead_detail_source_type").select2({
        ajax: {
            url: ajaxURLSearchSourceType,
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
        placeholder: 'Search for source type',
        dropdownParent: $("#lead_detail")
    }).on('change', function(e) {
        last_index = this.id.split('_').pop();
        $("#lead_detail_source").empty().trigger('change');
        $("#lead_detail_source_text").val('');
        // $("#lead_detail_source_text").removeAttr('readonly');

        if (this.value.split("-")[0] == "textrequired") {
            $("#lead_detail_source_text").show();
            $("#div_lead_detail_source").hide();

            $("#lead_detail_source_text").prop('required', true);

            $("#lead_detail_source").removeAttr('required');

        } else if (this.value.split("-")[0] == "textnotrequired") {
            $("#lead_detail_source_text").show();
            $("#div_lead_detail_source").hide();

            $("#lead_detail_source_text").removeAttr('required');
            $("#lead_detail_source").removeAttr('required');
        } else if (this.value.split("-")[0] == "fix") {
            $("#lead_detail_source_text").show();
            $("#div_lead_detail_source").hide();

            $("#lead_detail_source_text").prop('readonly', true);
            $("#lead_detail_source_text").val('-');

            $("#lead_detail_source_text").removeAttr('required');
            $("#lead_detail_source").removeAttr('required');
        } else {
            $("#lead_detail_source_text").hide();
            $("#div_lead_detail_source").show();

            $("#lead_detail_source").prop('required', true);

            $("#lead_detail_source_text").removeAttr('required');
        }
    });

    $("#lead_detail_source").select2({
        ajax: {
            url: ajaxURLSearchSource,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    q: params.term, // search term
                    page: params.page,
                    source_type: function() {
                        return $("#lead_detail_source_type").val();
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
        placeholder: 'Search for source',
        dropdownParent: $("#lead_detail")
    })

    $("#lead_detail_site_stage").select2({
        ajax: {
            url: ajaxURLSearchSiteStage,
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
        placeholder: 'Search for site stage',
        dropdownParent: $("#lead_detail")
    });

    $("#lead_detail_site_type").select2({
        ajax: {
            url: ajaxURLSearchSiteType,
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
        placeholder: 'Search for site type',
        dropdownParent: $("#lead_detail")
    });

    $("#lead_detail_sub_status").select2({
        ajax: {
            url: ajaxURLSearchSubStatus,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    q: params.term, // search term
                    page: params.page,
                    lead_status: function() {
                        return $("#lead_status_{{ $data['lead']['id'] }}").val();
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
        placeholder: 'Search for Sub Status',
        dropdownParent: $("#lead_detail")
    });

    function saveDetailUpdate(id, is_detail_update = 1, answer_ids = "") {
        if (isdetailload == 1) {
            if(is_detail_update == 0){
                if($('#lead_status_' + id).val() == 102 || $('#lead_status_' + id).val() == 103 || $('#lead_status_' + id).val() == 104){
                    is_detail_update = 0;
                    $.ajax({
                        type: 'GET',
                        data: {
                            "lead_id": id,
                            "status": $('#lead_status_' + id).val(),
                        },
                        url: ajaxURLLeadSearchQuestion,
                        success: function(responseText) {
                            if (responseText['status'] == 1) {
                                $('#modalStatusMove').modal('show');
                                $('#StatusMoveBody').html(responseText['view']);

                                $(".select2-apply").select2({
                                    minimumResultsForSearch: Infinity,
                                    dropdownParent: $("#modalStatusMove .modal-body")
                                });

                                $(".select2-multi-apply").select2({
                                    minimumResultsForSearch: Infinity,
                                    allowClear: true,
                                    dropdownParent: $("#modalStatusMove .modal-body")
                                });
                            } else {
                                toastr["error"](responseText['msg']);
                            }
                        }
                    });
                } else {
                    is_detail_update = 1;
                    answer_ids = "";
                }
            } else {
                is_detail_update = 1;
            }
            

            if(is_detail_update == 1){
                $('#detail_loader').show();
                $.ajax({
                    type: 'GET',
                    url: ajaxURLUpdateLeadDetail + "?id=" + id,
                    data: {
                        "lead_id": id,
                        "lead_site_stage": $('#lead_detail_site_stage').val(),
                        // "lead_site_type": $('#lead_detail_site_type').val(),
                        "lead_source_type": $('#lead_detail_source_type').val(),
                        "lead_source": $('#lead_detail_source').val(),
                        "lead_closing_date_time": $('#detail_closing_date_time').val(),
                        "lead_status": $('#lead_status_' + id).val(),
                        "lead_source_text": $('#lead_detail_source_text').val(),
                        "sub_status": $('#lead_detail_sub_status').val(),
                        "lead_deal_tag": $('#lead_detail_tag').val(),
                        "answer_ids": answer_ids,
                    },
                    success: function(responseText) {
                        if (responseText['status'] == 1) {
                            $('#detail_loader').hide();
                            toastr["success"](responseText['msg']);
                            getDataDetail(id);
                        } else {
                            toastr["error"](responseText['msg']);
                            $('#detail_loader').hide();
                            if ("lead_site_stage" in responseText['data']) {
                                $('#lead_detail_site_stage_error').show();
                            }

                            if ("lead_site_type" in responseText['data']) {
                                $('#lead_detail_site_type_error').show();
                            }

                            if ("lead_source" in responseText['data']) {
                                $('#lead_detail_source_error').show();
                            }

                            if ("lead_source_type" in responseText['data']) {
                                $('#lead_detail_source_type_error').show();
                            }

                            if ("lead_source_text" in responseText['data']) {
                                $('#lead_source_text').addClass('error-border');
                            }

                            if ("lead_status" in responseText['data']) {
                                $('#lead_status_error').show();
                            }
                        }
                    }
                });
            }
        }
    }

    $('#itempdfdownload').on('click', function() {
        var quot_id = $('#Quot_id').val();
        var quotgroup_id = $('#Quotgroup_id').val();
        var area_page_visible = $('#areatitle').prop("checked") ? 1 : 0;
        var area_summary_visible = $('#area').prop("checked") ? 1 : 0;
        var product_summary_visible = $('#product').prop("checked") ? 1 : 0;
        var product_summary_visible = $('#product').prop("checked") ? 1 : 0;

        var area_detailed_summary_visible = $('#areadetailtitle').prop("checked") ? 1 : 0;
        var area_detailed_gst_visible = $('#areagst').prop("checked") ? 1 : 0;
        var area_detailed_discount_visible = $('#areadiscount').prop("checked") ? 1 : 0;
        var area_detailed_rate_total_visible = $('#arearate').prop("checked") ? 1 : 0;

        var product_detailed_summary_visible = $('#producttitle').prop("checked") ? 1 : 0;
        var product_detailed_gst_visible = $('#productgst').prop("checked") ? 1 : 0;
        var product_detailed_discount_visible = $('#productdiscount').prop("checked") ? 1 : 0;
        var product_detailed_rate_total_visible = $('#productrate').prop("checked") ? 1 : 0;

        var wlt_and_others_detailed_summary_visible = $('#whiteliontitle').prop("checked") ? 1 : 0;
        var wlt_and_others_detailed_gst_visible = $('#wltandotherproductgst').prop("checked") ? 1 : 0;
        var wlt_and_others_detailed_discount_visible = $('#wltandotherdiscount').prop("checked") ? 1 : 0;
        var wlt_and_others_detailed_rate_total_visible = $('#wltandothernet').prop("checked") ? 1 : 0;

        let arry = [];
        arry.push({
            area_page_visible: area_page_visible,
            area_summary_visible: area_summary_visible,
            product_summary_visible: product_summary_visible,
            area_detailed_summary_visible: area_detailed_summary_visible,
            area_detailed_gst_visible: area_detailed_gst_visible,
            area_detailed_discount_visible: area_detailed_discount_visible,
            area_detailed_rate_total_visible: area_detailed_rate_total_visible,
            product_detailed_summary_visible: product_detailed_summary_visible,
            product_detailed_gst_visible: product_detailed_gst_visible,
            product_detailed_discount_visible: product_detailed_discount_visible,
            product_detailed_rate_total_visible: product_detailed_rate_total_visible,
            wlt_and_others_detailed_summary_visible: wlt_and_others_detailed_summary_visible,
            wlt_and_others_detailed_gst_visible: wlt_and_others_detailed_gst_visible,
            wlt_and_others_detailed_discount_visible: wlt_and_others_detailed_discount_visible,
            wlt_and_others_detailed_rate_total_visible: wlt_and_others_detailed_rate_total_visible,
        });

        var arr = JSON.stringify(arry);

        $('#itempdfdownload').attr('href', PrintDownloadUrl + '?quot_id=' + quot_id + '&quotgroup_id=' + quotgroup_id + '&visible_array=' + arr + '');
    })

    // // NEW UPDATE START 
    function ItemWisePrint(id, quotgroup_id) {

        $('#Quot_id').val(id);
        $('#Quotgroup_id').val(quotgroup_id);
        // $('#itempdfdownload').attr('href', PrintDownloadUrl + '?quot_id=' + id + '&quotgroup_id=' + quotgroup_id + '&area_page_visible='+ area_page_visible +'');
        $("#filtermodal").modal('show');
        $('#areatitle').prop('checked', false);
        $('#area').prop('checked', false);
        $('#product').prop('checked', false);
        $('#areadiscount').prop('checked', false);
        $('#areagst').prop('checked', false);
        $('#arearate').prop('checked', false);
        $('#areadetailtitle').prop('checked', false);
        $('#arearate').prop('checked', false);
        $('#productrate').prop('checked', false);
        $('#productdiscount').prop('checked', false);
        $('#productgst').prop('checked', false);
        $('#producttitle').prop('checked', false);
        $('#whiteliontitle').prop('checked', false);
        $('#wltandotherproductgst').prop('checked', false);
        $('#wltandotherdiscount').prop('checked', false);
        $('#wltandothernet').prop('checked', false);
        $('#wltandothernet').prop('checked', false);
        $('.wltandotherdetailsummary').fadeOut(300);
        $('.productdetailsummary').fadeOut(300);
        $('.areadetailsummary').fadeOut(300);
        $('.areasummary').fadeOut(300);
        //     $('#roomandproductsubfilter').hide();
        //     $('.a5').prop('checked', false);
        //     $('#roomsubfilter').hide();
        //     $('.a6').prop('checked', false);
        //     $('#wltandotherssubfilter').hide();
        //     $('.a7').prop('checked', false);
        $('.close').on('click', function() {
            $("#filtermodal").modal('hide');
        })
        //     // $("#modalItemWisePrint").modal('show');
        //     $(".itemwise_print_loader").show();
        //     $(".itemwise_print_download").hide();

        // $('#itempdfdownload').attr('href', PrintDownloadUrl + '?quot_id=' + id + '&quotgroup_id=' + quotgroup_id + '');

    }
    // //NEW UPDATE END 

    $('input:checkbox.allgst').change(function() {
        $('.allnetamount').prop('checked', false);
        if ($(this).prop('checked')) {
            $('.allgst').prop('checked', true);
        } else {
            $('.allgst').prop('checked', false);
        }
    });

    $('input:checkbox.alldiscount').change(function() {
        $('.allnetamount').prop('checked', false);
        if ($(this).prop("checked")) {
            $('.alldiscount').prop('checked', true);
        } else {
            $('.alldiscount').prop('checked', false);
        }
    });

    $('input:checkbox.allnetamount').change(function() {
        $('.allgst').prop('checked', false);
        $('.alldiscount').prop('checked', false);
        if ($(this).prop('checked')) {
            $('.allnetamount').prop('checked', true);
        } else {
            $('.allnetamount').prop('checked', false);
        }
    });

    $("input:checkbox.areasummarytitle").click(function() {
        if ($(this).is(":checked")) {
            $('.areasummary').fadeIn(300);
        } else {
            $('.areasummary').fadeOut(300);
            $('#area').prop('checked', false);
            $('#product').prop('checked', false);
        }
    });

    $("input:checkbox.areadetailtitle").click(function() {
        if ($(this).is(":checked")) {
            $('.areadetailsummary').fadeIn(300);
        } else {
            $('.areadetailsummary').fadeOut(300);
            // $('#areagst').prop('checked', false);
            // $('#areadiscount').prop('checked', false);
            // $('#arearate').prop('checked', false);
        }
    });

    $("input:checkbox.producttitle").click(function() {
        if ($(this).is(":checked")) {
            $('.productdetailsummary').fadeIn(300);
        } else {
            $('.productdetailsummary').fadeOut(300);
            // $('#productgst').prop('checked', false);
            // $('#productdiscount').prop('checked', false);
            // $('#productrate').prop('checked', false);
        }
    });

    $("input:checkbox.whiteliontitle").click(function() {
        if ($(this).is(":checked")) {
            $('.wltandotherdetailsummary').fadeIn(300);
        } else {
            $('.wltandotherdetailsummary').fadeOut(300);
            // $('#wltandotherproductgst').prop('checked', false);
            // $('#wltandotherdiscount').prop('checked', false);
            // $('#wltandothernet').prop('checked', false);
        }
    });


    $("#lead_detail_tag").select2({
        ajax: {
            url: ajaxURLSearchLeadAndDealTag,
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
        placeholder: 'Search for tag',
        dropdownParent: $("#lead_detail")
    });


    $(document).ready(function() {
        adjustContainerHeight();
        $(window).on('resize', adjustContainerHeight);
    });

    function adjustContainerHeight() {
        var windowWidth = $(window).width();
                if(windowWidth <= 1440){
                    $('body').addClass('vertical-collpsed');
                }
        if(is_deal == 1) {
            var windowHeight = $(window).height() - 150;
            max_height = windowHeight - 180;
            $('#datatable').parent().css('height', max_height + 'px');
            $('#datatable').parent().css('max-height', max_height + 'px');
            $('#home').parent().css('max-height', max_height + 'px');
        } else {
            var windowHeight = $(window).height() - 100;
            max_height = windowHeight - 180;
            $('#datatable').parent().css('max-height', max_height + 'px');
            $('#datatable').parent().css('height', max_height + 'px');
            $('#home').parent().css('max-height', max_height + 'px');
        }
        $('#custom_height').css('height', windowHeight + 'px');
    }
</script>
