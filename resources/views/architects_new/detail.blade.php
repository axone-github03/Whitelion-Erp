<h2 class="px-3 pb-3">#{{ $data['user']['id'] }} {{ ucwords(strtolower($data['user']['account_name'])) }}
    {!! $data['user']['user_type_lable'] !!} {!! $data['user']['user_status_lable'] !!}
    @if (isAdminOrCompanyAdmin() == 1)
        <label class="switch mb-0" style="vertical-align: sub">
            @if ($data['user']['status'] == 1)
                <input type="checkbox" checked="" onchange="UserStatusChange({{ $data['user']['id'] }}, 1)">
            @else
                <input type="checkbox" onchange="UserStatusChange({{ $data['user']['id'] }}, 0)">
            @endif
            <small></small>
        </label>
    @endif
    @if ($data['user']['tag'] != '' && $data['user']['tag'] != null)

        @foreach ($data['user']['tag'] as $tag)
            <span class="badge badge-pill badge badge-soft-warning font-size-14">{{ $tag->text }}</span>
        @endforeach
    @endif
</h2>

<div class="row ps-2 flex-nowrap align-items-center">
    <div class="col-3 col-lg-6 col-xl-3">
        <input type="hidden" name="user_main_detail_id" id="user_main_detail_id" value="{{ $data['user']['id'] }}">
        <input type="hidden" name="hidden_is_arc" id="hidden_is_arc" value="1">
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
    <div class="row ms-1">
        <div class="bg-light border-info" style="width: fit-content;">
            <div class="d-flex flex-column ms-3 pe-3 text-primary"
                style="border-right: 1px solid #c2ccff;height: auto; margin-top: 10px; margin-bottom: 10px;">
                <b>Lifetime Pt.</b>
                <span class="" style="font-weight: bold;" id="arc_lifetime_point">0</span>
            </div>
        </div>
        <div class="bg-light border-info" style="width: fit-content;">
            <div class="d-flex flex-column pe-3 text-danger"
                style="border-right: 1px solid #c2ccff;height: auto; margin-top: 10px; margin-bottom: 10px;">
                <b>Redeemed Pt.</b>
                <span class="" style="font-weight: bold;" id="arc_redeemed_point">0</span>
            </div>
        </div>
        <div class="bg-light border-info" style="width: fit-content;">
            <div class="d-flex flex-column pe-3 text-success"
                style="border-right: 1px solid #c2ccff;height: auto; margin-top: 10px; margin-bottom: 10px;">
                <b>Available Pt.</b>
                <span class="" style="font-weight: bold;" id="arc_available_point">0</span>
            </div>
        </div>
        <div class="bg-light" style="width: fit-content;">
            <div class="d-flex flex-column pe-3 text-primary"
                style="height: auto; margin-top: 10px; margin-bottom: 10px;">
                <b>Lead & Deal</b>
                <span class="" style="font-weight: bold;" id="arc_lead_and_deal_total_count">0</span>
            </div>
        </div>
    </div>
</div>

<div class="d-flex flex-wrap p-2">
    <button type="button" class="btn btn-sm waves-effect waves-light bg-white"
        onclick="smoothScroll(document.getElementById('tab_detail'))">
        <i class="bx bx-detail font-size-16 align-middle me-2"></i> Detail
    </button>
    <button type="button" class="btn btn-sm waves-effect waves-light bg-white"
        onclick="smoothScroll(document.getElementById('tab_lead'))">
        <i class="bx bx bxs-contact font-size-16 align-middle me-2"></i>Lead (<span class="total_lead_count"></span>)
    </button>
    <button type="button" class="btn btn-sm waves-effect waves-light bg-white"
        onclick="smoothScroll(document.getElementById('tab_deal'))">
        <i class="bx bx bxs-contact font-size-16 align-middle me-2"></i>Deal (<span class="total_deal_count"></span>)
    </button>
    <button type="button" class="btn btn-sm waves-effect waves-light bg-white"
        onclick="smoothScroll(document.getElementById('tab_service'))">
        <i class="bx bx bxs-contact font-size-16 align-middle me-2"></i>Service
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

<div class="tab-content p-2 text-muted" style="overflow: hidden scroll;">
    <div class="tab-pane active" id="home" role="tabpanel">
        <div class="card lead-detail" style="border-radius: 10px;" id="tab_detail">
            @include('user_action.detail_tab.detail_tab')
        </div>

        <div class="card lead-detail" style="border-radius: 10px;" id="tab_lead">
            @include('user_action.detail_tab.detail_lead_tab')
        </div>

        <div class="card lead-detail" style="border-radius: 10px;" id="tab_deal">
            @include('user_action.detail_tab.detail_deal_tab')
        </div>

        <div class="card lead-detail" style="border-radius: 10px;" id="tab_service">
            @include('user_action.detail_tab.detail_service_tab')
        </div>

        <div class="card lead-detail" style="border-radius: 10px;" id="tab_contact">
            @include('user_action.detail_tab.detail_contact_tab')
        </div>

        <div class="card lead-detail" style="border-radius: 10px;" id="tab_files">
            @include('user_action.detail_tab.detail_file_tab')
        </div>

        <div class="card lead-detail" style="border-radius: 10px;" id="tab_notes">
            @include('user_action.detail_tab.detail_notes_tab')
        </div>

        <div class="card lead-detail" style="border-radius: 10px;" id="tab_action">
            @include('user_action.detail_tab.detail_open_action_tab')
        </div>

        <div class="card lead-detail" style="border-radius: 10px;" id="tab_close_action">
            @include('user_action.detail_tab.detail_close_action_tab')
        </div>
    </div>

    <div class="card lead-detail tab-pane" style="border-radius: 10px;" id="profile" role="tabpanel">
        <div class="card-body">
            <ul class="nav nav-tabs border-0 rounded-pill p-0 w-25" role="tablist" style="background: #f1f1f1;">

                <li class="nav-item w-50">
                    <a class="nav-link border-0 text-center rounded-pill active" data-bs-toggle="tab"
                        href="#user_log" role="tab" aria-selected="true">
                        <span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
                        <span class="d-none d-sm-block">User</span>
                    </a>
                </li>
                <li class="nav-item w-50">
                    <a class="nav-link border-0 text-center rounded-pill" data-bs-toggle="tab" href="#point_log"
                        role="tab" aria-selected="false">
                        <span class="d-block d-sm-none"><i class="far fa-user"></i></span>
                        <span class="d-none d-sm-block">Point</span>
                    </a>
                </li>
            </ul>
            <div class="tab-content p-2 text-muted" style="overflow: hidden scroll;">
                <div class="tab-pane active" id="user_log" role="tabpanel">
                    {!! $data['user_log'] !!}
                </div>
                <div class="tab-pane" id="point_log" role="tabpanel">
                    {!! $data['point_log'] !!}
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
        display: inline-block;
    }

    .switch input {
        display: none;
    }

    .switch small {
        display: inline-block;
        width: 92px;
        height: 25px;
        background: #ff6464;
        border-radius: 30px;
        position: relative;
        cursor: pointer;
    }

    .switch small:after {
        content: "Inactive";
        position: absolute;
        color: #fff;
        font-size: 13px;
        font-weight: 600;
        width: 95%;
        left: 0px;
        text-align: right;
        padding: 0 6px;
        box-sizing: border-box;
        line-height: 25px;
    }

    .switch small:before {
        content: "";
        position: absolute;
        width: 19px;
        height: 18px;
        background: #fff;
        border-radius: 50%;
        top: 3px;
        left: 5px;
        transition: .3s;
        box-shadow: -3px 0 3px rgba(0, 0, 0, 0.1);
    }

    .switch input:checked~small {
        background: #34c38f;
        transition: .3s;
    }

    .switch input:checked~small:before {
        transform: translate(25px, 0px);
        transition: .3s;
        left: 44px !important;
    }

    .switch input:checked~small:after {
        content: "Active";
        text-align: center;
        left: -7px !important;
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
    var ajaxURLSearchUserTag = "{{ route('search.user.tag') }}";
    var ajaxURLUpdateUserDetail = "{{ route('save.user.detail') }}";
    var ajaxURLSearchSalePerson = "{{ route('new.architects.search.sale.person') }}";

    var csrfToken = $("[name=_token").val();

    $("#user_owner").select2({
        ajax: {
            url: ajaxURLSearchSalePerson,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    q: params.term, // search term
                    page: params.page,
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
        placeholder: 'Search for Sale Person',
        dropdownParent: $("#lead_detail")
    });

    $("#user_tag_id").select2({
        ajax: {
            url: ajaxURLSearchUserTag,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    q: params.term, // search term
                    page: params.page,
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

    function saveDetailUpdate(id, is_detail_update = 1) {
        if (isdetailload == 1) {
            $('#detail_loader').show();
            $.ajax({
                type: 'POST',
                url: ajaxURLUpdateUserDetail,
                data: {
                    "id": id,
                    "user_owner": $('#user_owner').val(),
                    "user_tag": $('#user_tag_id').val(),
                    '_token': $("[name=_token]").val()
                },
                success: function(responseText) {
                    if (responseText['status'] == 1) {
                        $('#detail_loader').hide();
                        toastr["success"](responseText['msg']);
                        // getDataDetail(id)
                    } else {
                        $('#detail_loader').hide();
                    }
                }
            })
        }
    }

    $(document).ready(function() {
        adjustContainerHeight();
        $(window).on('resize', adjustContainerHeight);
    });

    function adjustContainerHeight() {
        var windowHeight = $(window).height() - 135;
        var windowWidth = $(window).width();
        if (windowWidth <= 1440) {
            $('body').addClass('vertical-collpsed');
        }
        max_height = windowHeight - 180;
        $('#datatable').parent().css('max-height', max_height + 'px');
        $('#datatable').parent().css('height', max_height + 'px');
        $('#home').parent().css('max-height', max_height + 'px');
        $('#custom_height').css('height', windowHeight + 'px');
    }
</script>
