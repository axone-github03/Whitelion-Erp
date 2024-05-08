<style type="text/css">
    
    #show_quot_boarditem_table>thead>tr th:nth-child(1) {
        width: 5% !important;
    }

    #show_quot_boarditem_table>thead>tr th:nth-child(2) {
        width: 30% !important;
    }

    #show_quot_boarditem_table>thead>tr th:nth-child(3) {
        width: 20% !important;
    }

    #show_quot_boarditem_table>thead>tr th:nth-child(4) {
        width: 6% !important;
    }

    #show_quot_boarditem_table>thead>tr th:nth-child(5) {
        width: 15% !important;
    }

    #show_quot_boarditem_table>thead>tr th:nth-child(6) {
        width: 6% !important;
    }

    #show_quot_boarditem_table>thead>tr th:nth-child(7) {
        width: 6% !important;
    }

    #show_quot_boarditem_table>thead>tr th:nth-child(8) {
        width: 6% !important;
    }

    #show_quot_boarditem_table>thead>tr th:nth-child(9) {
        width: 6% !important;
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
    .titlecheckbox input[type="checkbox"]:checked+label{
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
    .checkbox{
        margin-right: 10px;
    }
    .label-text{
        font-size: 1rem;
    }
    .input-checkbox{
        width: 10px;
    }
    .vh{
        position: absolute !important;
        clip: rect(1px, 1px, 1px, 1px);
        padding: 0 !important;
        border: 0 !important;
        height: 1px !important;
        width: 1px !important;
        overflow: hidden;
    }
    input[type="checkbox"]:checked ~ label:before{
        vertical-align: middle;
        background: #3673c0 no-repeat center;
        background-size: 9px 9px;
        background-image: url(data:image/svg+xml;utf8;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pgo8IS0tIEdlbmVyYXRvcjogQWRvYmUgSWxsdXN0cmF0b3IgMTYuMC4wLCBTVkcgRXhwb3J0IFBsdWctSW4gLiBTVkcgVmVyc2lvbjogNi4wMCBCdWlsZCAwKSAgLS0+CjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+CjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4PSIwcHgiIHk9IjBweCIgd2lkdGg9IjE2cHgiIGhlaWdodD0iMTZweCIgdmlld0JveD0iMCAwIDQ1LjcwMSA0NS43IiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCA0NS43MDEgNDUuNzsiIHhtbDpzcGFjZT0icHJlc2VydmUiPgo8Zz4KCTxnPgoJCTxwYXRoIGQ9Ik0yMC42ODcsMzguMzMyYy0yLjA3MiwyLjA3Mi01LjQzNCwyLjA3Mi03LjUwNSwwTDEuNTU0LDI2LjcwNGMtMi4wNzItMi4wNzEtMi4wNzItNS40MzMsMC03LjUwNCAgICBjMi4wNzEtMi4wNzIsNS40MzMtMi4wNzIsNy41MDUsMGw2LjkyOCw2LjkyN2MwLjUyMywwLjUyMiwxLjM3MiwwLjUyMiwxLjg5NiwwTDM2LjY0Miw3LjM2OGMyLjA3MS0yLjA3Miw1LjQzMy0yLjA3Miw3LjUwNSwwICAgIGMwLjk5NSwwLjk5NSwxLjU1NCwyLjM0NSwxLjU1NCwzLjc1MmMwLDEuNDA3LTAuNTU5LDIuNzU3LTEuNTU0LDMuNzUyTDIwLjY4NywzOC4zMzJ6IiBmaWxsPSIjRkZGRkZGIi8+Cgk8L2c+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPC9zdmc+Cg==);
    }
    .label-text::before{
        content: '';
        width: 15px;
        height: 15px;
        background: #f2f2f2;
        border: 1px solid rgba(75, 101, 132, 0.3);
        display: inline-block;
        margin-right: 10px;
    }
</style>

<div class="modal fade" id="modalShowHistory" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" role="dialog"
    aria-labelledby="modalShowHistoryLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalShowHistoryLabel">Show Quotation History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <input type="hidden" name="quot_history_quotgroup" id="quot_history_quotgroup" value="0">
            </div>

            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row">
                        <table id="show_quot_history_table" class="table table-striped dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Quot No.</th>
                                    <th>Party Name</th>
                                    <th>Version</th>
                                    <th>Entry By</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Action</th>
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




<div class="modal fade" id="modalQuotBoardDetail" data-bs-backdrop="static" tabindex="-1" role="dialog"
    aria-labelledby="modalquotboarddetail" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title mr-4" style="margin-right: 4rem;" id="MQBD_Label">Board Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body p-1">
                                <div class="d-flex" style="width: fit-content;">
                                    <div class="d-flex flex-column ms-2 pe-2"
                                        style="border-right: solid #dbdbdb 1px;">
                                        <img id="bd_board_image" src="" height="80">
                                    </div>
                                    <div class="d-flex flex-column ms-2 pe-2"
                                        style="border-right: solid #dbdbdb 1px;">
                                        <div class="text-capitalize"><span style="font-weight: bold;">Room : </span>
                                            <span id="bd_room_name">-</span>
                                        </div>
                                        <div class="text-capitalize"><span style="font-weight: bold;">Board : </span>
                                            <span id="bd_board_name">-</span>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column ms-2 pe-2"
                                        style="border-right: solid #dbdbdb 1px;">
                                        <div class="text-capitalize"><span style="font-weight: bold;">Plate : </span>
                                            <span id="bd_range_plate">-</span>
                                        </div>
                                        <div class="text-capitalize"><span style="font-weight: bold;">Accessories :
                                            </span> <span id="bd_range_accessories">-</span> </div>
                                        <div class="text-capitalize"><span style="font-weight: bold;">Whitelion :
                                            </span> <span id="bd_range_whitelion">-</span> </div>
                                    </div>
                                    <div class="d-flex flex-column ms-2 pe-2">
                                        <div class="text-capitalize"><span style="font-weight: bold;">Gross Amount(₹)
                                                : </span> <span id="bd_amt_gross_amount">-</span> </div>
                                        <div class="text-capitalize"><span style="font-weight: bold;">CGST(₹) :
                                            </span> <span id="bd_amt_cgst_amount">-</span> </div>
                                        <div class="text-capitalize"><span style="font-weight: bold;">SGST(₹) :
                                            </span> <span id="bd_amt_sgst_amount">-</span> </div>
                                        <div class="text-capitalize"><span style="font-weight: bold;">Final Amount(₹)
                                                : </span> <span id="bd_amt_net_amount">-</span> </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <ul class="nav nav-tabs nav-tabs-custom nav-justified" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#board_detail_data" role="tab">
                            <span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
                            <span class="d-none d-sm-block">Board Item Detail</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#board_range_change" role="tab">
                            <span class="d-block d-sm-none"><i class="far fa-user"></i></span>
                            <span class="d-none d-sm-block">Board Range Detail</span>
                        </a>
                    </li>
                </ul>

                <div class="tab-content p-3 text-muted">
                    <div class="tab-pane active" id="board_detail_data" role="tabpanel">
                        <div class="row">
                            <div class="col-md-12 mb-3 text-end">
                                <button id="add_board_addon" class="btn btn-primary" type="button"><i
                                        class="bx bx-plus font-size-16 align-middle me-2"></i>Add Addon</button>
                            </div>
                        </div>
                        <table id="show_quot_boarditem_table" class="table w-100">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">No.</th>
                                    <th style="width: 30%;">Item</th>
                                    <th style="width: 20%;">Brand</th>
                                    <th style="width: 6%;" class="text-center">Module</th>
                                    <th style="width: 15%;" class="text-center"
                                        style="border-right: 1px solid #eff2f7;">Qty</th>
                                    <th style="width: 6%;" class="text-center">Rate</th>
                                    <th style="width: 6%;" class="text-center">Gross</th>
                                    <th style="width: 6%;" class="text-center">GST</th>
                                    <th style="width: 6%;" class="text-center">Final</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                        <div class="row">
                            <div class="col-md-12 mt-3 text-end">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary waves-effect waves-light"
                                    id="BoardSaveAllSync">Save All</button>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane" id="board_range_change" role="tabpanel">
                        <div class="col-md-12">
                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                <label for="q_board_subgroup_plte_id" class="form-label">Plate <code
                                        class="highlighter-rouge">*</code></label>
                                <select class="form-control select2-ajax" id="q_board_subgroup_plte_id"
                                    name="q_board_subgroup_plte_id" required>
                                </select>
                                <div class="invalid-feedback">
                                    Please select Plate.
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                <label for="q_board_subgroup_access_id" class="form-label">Accessories <code
                                        class="highlighter-rouge">*</code></label>
                                <select class="form-control select2-ajax" id="q_board_subgroup_access_id"
                                    name="q_board_subgroup_access_id">
                                </select>
                                <div class="invalid-feedback">
                                    Please select Accessories.
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                <label for="q_board_subgroup_whitelion_id" class="form-label">Whitelion <code
                                        class="highlighter-rouge">*</code></label>
                                <select class="form-control select2-ajax" id="q_board_subgroup_whitelion_id"
                                    name="q_board_subgroup_whitelion_id">
                                </select>
                                <div class="invalid-feedback">
                                    Please select Whitelion.
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary BoardRangeChangeSave">Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalQuotBoardErrorDetail" data-bs-backdrop="static" tabindex="-1" role="dialog"
    aria-labelledby="modalquotboarderrordetail" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title mr-4" style="margin-right: 4rem;" id="MQBD_Label">Board Error Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <table id="show_quot_board_error_item_table" class="table table-striped w-100">
                        <thead>
                            <tr>
                                <th class="text-center" colspan="5"
                                    style="border-width: 1px;padding: 0.25rem 0.25rem !important;">Old Item Detail
                                </th>
                                <th class="text-center" colspan="2"
                                    style="border-width: 1px;padding: 0.25rem 0.25rem !important;">New Item Detail
                                </th>
                            </tr>
                            <tr>
                                <th class="w-5">No.</th>
                                <th class="text-center w-25">Item</th>
                                <th class="text-center w-25">Brand</th>
                                <th class="text-center w-5">Module</th>
                                <th class="text-center w-5" style="border-right: 1px solid #eff2f7;">Rate</th>
                                <th class="text-center w-25">New Brand</th>
                                <th class="text-center w-5">Action</th>
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

<div class="modal fade" id="modalChangeRange" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1"
    role="dialog" aria-labelledby="modalChangeRangeLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalChangeRangeLabel">Change Range </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body" id="modelBodyDetail">

                <!-- <form id="formMainMaster" class="custom-validation" action="{{ route('quot.itemsubgroup.master.save') }}" method="POST"> -->
                <!-- @csrf -->
                <div class="col-md-12">
                    <div class="mb-3 ajax-select mt-3 mt-lg-0">
                        <label for="q_subgroup_plte_id" class="form-label">Plate <code
                                class="highlighter-rouge">*</code></label>
                        <select class="form-control select2-ajax" id="q_subgroup_plte_id" name="q_subgroup_plte_id"
                            required>
                        </select>
                        <div class="invalid-feedback">
                            Please select Plate.
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="mb-3 ajax-select mt-3 mt-lg-0">
                        <label for="q_subgroup_access_id" class="form-label">Accessories <code
                                class="highlighter-rouge">*</code></label>
                        <select class="form-control select2-ajax" id="q_subgroup_access_id"
                            name="q_subgroup_access_id">
                        </select>
                        <div class="invalid-feedback">
                            Please select Accessories.
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="mb-3 ajax-select mt-3 mt-lg-0">
                        <label for="q_subgroup_whitelion_id" class="form-label">Whitelion <code
                                class="highlighter-rouge">*</code></label>
                        <select class="form-control select2-ajax" id="q_subgroup_whitelion_id"
                            name="q_subgroup_whitelion_id">
                        </select>
                        <div class="invalid-feedback">
                            Please select Whitelion.
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="button" onclick="quot_change_range_save();" class="btn btn-primary">Save</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade bd-example-modal-xl" id="modalQuotItemDiscountDetail" data-bs-backdrop="static"
    tabindex="-1" role="dialog" aria-labelledby="modalquotitemdiscountdetail" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="MQIDD_Label">Item Discount Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body p-1">
                                    <div class="d-flex" style="width: fit-content;">
                                        <div class="d-flex flex-column ms-2 pe-2"
                                            style="border-right: solid #dbdbdb 1px;">
                                            <div class="text-capitalize"><span style="font-weight: bold;">Quote no :
                                                </span> <span id="su_disc_quot_no">-</span> </div>
                                            <div class="text-capitalize"><span style="font-weight: bold;">Quote date
                                                    :
                                                </span> <span id="su_disc_quot_date">-</span> </div>
                                            <div class="text-capitalize"><span style="font-weight: bold;">Quote Type
                                                    :
                                                </span> <span id="su_disc_quot_type">-</span> </div>
                                            <div class="text-capitalize"><span style="font-weight: bold;">Quote
                                                    version : </span> <span id="su_disc_quot_version">-</span> </div>
                                        </div>
                                        <div class="d-flex flex-column ms-2 pe-2"
                                            style="border-right: solid #dbdbdb 1px;">
                                            <div class="text-capitalize"><span style="font-weight: bold;">Site Name
                                                    :
                                                </span> <span id="su_disc_quot_site_name">-</span> </div>
                                            <div class="text-capitalize"><span style="font-weight: bold;">Site Add.
                                                    :
                                                </span> <span id="su_disc_quot_site_address">-</span> </div>
                                            <div class="text-capitalize"><span style="font-weight: bold;">Customer
                                                    Name : </span> <span id="su_disc_quot_cust_name">-</span> </div>
                                            <div class="text-capitalize"><span style="font-weight: bold;">Customer
                                                    Mobile : </span> <span id="su_disc_quot_cust_mobile">-</span>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-column ms-2 pe-2"
                                            style="border-right: solid #dbdbdb 1px;">
                                            <div class="text-capitalize"><span style="font-weight: bold;">Plate :
                                                </span> <span id="su_disc_range_plate">-</span> </div>
                                            <div class="text-capitalize"><span
                                                    style="font-weight: bold;">Accessories
                                                    : </span> <span id="su_disc_range_accessories">-</span> </div>
                                            <div class="text-capitalize"><span style="font-weight: bold;">Whitelion
                                                    :
                                                </span> <span id="su_disc_range_whitelion">-</span> </div>
                                            <div class="text-capitalize"><span style="font-weight: bold;">Currunt
                                                    Status : </span> <span id="su_disc_quot_Status">-</span> </div>
                                        </div>
                                        <div class="d-flex flex-column ms-2 pe-2">
                                            <div class="text-capitalize"><span style="font-weight: bold;">Gross
                                                    Amount(₹) : </span> <span
                                                    id="su_disc_quote_total_gross_amt">-</span> </div>
                                            <div class="text-capitalize"><span style="font-weight: bold;">CGST(₹) :
                                                </span> <span id="su_disc_quote_total_cgst_amt">-</span> </div>
                                            <div class="text-capitalize"><span style="font-weight: bold;">SGST(₹) :
                                                </span> <span id="su_disc_quote_total_sgst_amt">-</span> </div>
                                            <div class="text-capitalize"><span style="font-weight: bold;">Final
                                                    Amount(₹) : </span> <span
                                                    id="su_disc_quote_total_net_amt">-</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-3">
                            <div class="d-flex" style="text-align: center;">
                                <input class="form-check-input" type="hidden" name="discount_type"
                                    id="discount_type" value="ALL">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="radioDiscountFilter"
                                        value="ALL" id="all" checked>
                                    <label class="form-check-label " for="all">All</label>
                                </div>

                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="radioDiscountFilter"
                                        id="itemwise" value="ITEMWISE">
                                    <label class="form-check-label" for="itemwise">item wise</label>
                                </div>

                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="radioDiscountFilter"
                                        value="BRANDWISE" id="brandwise">
                                    <label class="form-check-label " for="brandwise">brand wise</label>
                                </div>
                            </div>
                        </div>
                        <div class="ajax-select col-3 ItemWiseDiscount">
                            <select class="form-control float-start select2-ajax " id="discount_item_select"
                                name="discount_item_select">
                            </select>
                        </div>
                        <div class="ajax-select col-3 BrandWiseDiscount">
                            <select class="form-control float-start select2-ajax " id="discount_brand_select"
                                name="discount_brand_select">
                            </select>
                        </div>
                        <div class="col-3">
                            <input type="number" min="0" max="100" step="1"
                                class="form-control w-50 float-start valid-discount"
                                id="discount_brandwise_discount" name="discount_brandwise_discount"
                                placeholder="Discount" value="">
                            <button id="saveBrandWiseDiscount" type="button"
                                class="btn btn-primary waves-effect waves-light float-end ms-2">
                                Apply All
                            </button>
                        </div>
                        <div class="col-3">
                            <button type="button" class="btn btn-dark waves-effect waves-light float-end"
                                id="discountSync">Save All</button>
                        </div>
                    </div>
                    <div class="row">
                        <table id="quot_changediscount_table" class="table table-striped">
                            <thead>
                                <tr>
                                    <th class="w-4">No.</th>
                                    <th class="w-32">Item</th>
                                    <th class="w-15">Brand</th>
                                    <th class="w-4 text-center">Module</th>
                                    <th class="w-3 text-center" style="border-right: 1px solid #eff2f7;">Qty</th>
                                    <th class="w-8 text-center">Rate</th>
                                    <th class="w-10 text-center">Disc.</th>
                                    <th class="w-8 text-center">Gross</th>
                                    <th class="w-8 text-center">GST</th>
                                    <th class="w-8 text-center">Final</th>
                                </tr>
                            </thead>
                            <tbody id="MQIDD_item_detail_tbody">

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer d-none">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save</button>
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
                        <input type="checkbox" name="areatitle" id="areatitle" class="vh areasummarytitle"><label class="label-text" for="areatitle">Summary</label>
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
                        <input type="checkbox" name="areadetailtitle" id="areadetailtitle" class="vh areadetailtitle"><label class="label-text" for="areadetailtitle">Roomwise</label>
                    </div>
                    <div class="areadetailsummary" style="display: none">
                        <div class="button type">
                            <input type="checkbox" id="areagst"  class="allgst"/>
                            <span class="btn btn-default" for="a5">GST</span>
                        </div>
                        <div class="button type">
                            <input type="checkbox" id="areadiscount"  class="alldiscount"/>
                            <span class="btn btn-default" for="a5">Discount</span>
                        </div>
                        <div class="button type">
                            <input type="checkbox" id="arearate" class="allnetamount"/>
                            <span class="btn btn-default" for="a5">Net</span>
                        </div>
                    </div>
                </div>

                <div style="border-bottom: 2px solid #ababab"></div>

                <div class="d-flex flex-column  mt-2 mb-2 align-items-center">
                    <div class="titlecheckbox">
                        <input type="checkbox" name="producttitle" id="producttitle" class="vh producttitle"><label class="label-text" for="producttitle">Boardwise</label>
                    </div>
                    <div class="productdetailsummary" style="display: none">
                        <div class="button type">
                            <input type="checkbox" id="productgst"  class="allgst"/>
                            <span class="btn btn-default" for="a5">GST</span>
                        </div>
                        <div class="button type">
                            <input type="checkbox" id="productdiscount"  class="alldiscount"/>
                            <span class="btn btn-default" for="a5">Discount</span>
                        </div>
                        <div class="button type">
                            <input type="checkbox" id="productrate" class="allnetamount"/>
                            <span class="btn btn-default" for="a5">Net</span>
                        </div>
                    </div>
                </div>

                <div style="border-bottom: 2px solid #ababab"></div>

                <div class="d-flex flex-column mt-2 align-items-center">
                    <div class="titlecheckbox">
                        <input type="checkbox" name="whiteliontitle" id="whiteliontitle" class="vh whiteliontitle"><label class="label-text" for="whiteliontitle">Whitelion And Other</label>
                    </div>
                    <div class="wltandotherdetailsummary" style="display: none">
                        <div class="button type">
                            <input type="checkbox" id="wltandotherproductgst"  class="allgst"/>
                            <span class="btn btn-default" for="a5">GST</span>
                        </div>
                        <div class="button type">
                            <input type="checkbox" id="wltandotherdiscount"  class="alldiscount"/>
                            <span class="btn btn-default" for="a5">Discount</span>
                        </div>
                        <div class="button type">
                            <input type="checkbox" id="wltandothernet" class="allnetamount"/>
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


{{-- // NEW UPDATE START --}}
<div class="modal fade" id="modalQuatBoardAddAddons" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" style="background-color: #0000003b;">
    <div class="modal-dialog modal-dialog-centered justify-content-center" role="document">
        <div class="modal-content" style="background-color: #efefef;width: auto !important;">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Add Addon</h5>
                <button type="button" class="close btn-close" data-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <form action="" method="post" id="addboardaddonsform">
                    <div class="row mb-4">
                        <div class="col-12">
                            <label for="" class="form-label">Select Item</label>
                            <div class="ajax-select">
                                <select name="q_board_addon_select" id="q_board_addons_select_id"
                                    class="form-control select2-ajax">
                                </select>
                            </div>

                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="">Enter Item Qty</label>
                            <input type="number" class="form-control" name="q_board_addon_qty"
                                id="q_board_addon_qty">
                        </div>
                        <div class="col-md-6">
                            <label for="">Item Rate</label>
                            <input type="text" class="form-control" readonly id="readonlyitemprice">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary close" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="BoardAddonSave">Save</button>
            </div>
        </div>
    </div>
</div>
{{-- NEW UPDATE END --}}

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script type="text/javascript">
    var ajaxQuotHistoryDataURL = '{{ route('quot.history.ajax.data') }}';

    var QuotBoardItemEditTable = '';
    var QuotItemDiscountTable = '';
    var QuotBoardErrorItemTable = '';

   

    function ShowHistory(id, quotgroup_id) {
        var csrfToken = $("[name=_token").val()
        $("#modalShowHistory").modal('show');
        $("#quot_history_quotgroup").val(quotgroup_id);
        var QuotHistoryTable = $('#show_quot_history_table').DataTable({
            "aoColumnDefs": [{
                "bSortable": false,
                "aTargets": [5, 6]
            }],
            "order": [
                [0, 'desc']
            ],
            "processing": true,
            "serverSide": true,
            "bDestroy": true,
            "pageLength": 10,
            "ajax": {
                "url": ajaxQuotHistoryDataURL,
                "type": "POST",
                "data": {
                    "_token": csrfToken,
                    "quotgroup_id": quotgroup_id
                }
            },
            "aoColumns": [{
                    "mData": "id"
                },
                {
                    "mData": "quot_no"
                },
                {
                    "mData": "partyname"
                },
                {
                    "mData": "version"
                },
                {
                    "mData": "entryby"
                },
                {
                    "mData": "status"
                },
                {
                    "mData": "action"
                }
            ]
        });
    }

    function quot_board_detail(quot_id, quot_groupid, quot_strno, quot_rommno, quot_boardno, board_range) {
        var csrfToken = $("[name=_token").val();
        // modalquotation
        $("#modalQuotBoardDetail").modal('show');
        // alert("\nquot id :- " + quot_id +
        //     "\nquot group :- " + quot_groupid +
        //     "\nquot str :- " + quot_strno +
        //     "\nquot room :- " + quot_rommno +
        //     "\nquot board :- " + quot_boardno);
        var ajaxQuotBoardDetailDataURL = '{{ route('quot.boarddetail.data') }}';

        QuotBoardItemEditTable = $('#show_quot_boarditem_table').DataTable({
            "aoColumnDefs": [{
                "bSortable": false,
                "aTargets": [0, 2, 3, 4, 5, 6, 7, 8]
            }],
            "order": [
                [0, 'desc']
            ],
            "processing": true,
            "serverSide": true,
            "bDestroy": true,
            "pageLength": 100,
            "pagingType": "full_numbers",
            "ajax": {
                "url": ajaxQuotBoardDetailDataURL,
                "type": "POST",
                "data": {
                    "_token": csrfToken,
                    "quot_id": quot_id,
                    "quot_groupid": quot_groupid,
                    "quot_strno": quot_strno,
                    "quot_rommno": quot_rommno,
                    "quot_boardno": quot_boardno
                }
            },
            "aoColumns": [{
                    "mData": "id"
                },
                {
                    "mData": "item"
                },
                {
                    "mData": "brand"
                },
                {
                    "mData": "module"
                },
                {
                    "sClass": "qtyRightBorder",
                    "mData": "qty"
                },
                {
                    "mData": "rate"
                },
                {
                    "mData": "grossamount"
                },
                {
                    "mData": "gst"
                },
                {
                    "mData": "net_amount"
                }
            ],
            drawCallback: function() {}
        });

        quotation_board_summary(csrfToken, board_range, quot_id, quot_groupid, quot_rommno, quot_boardno);

        // input_qty_text
        $('#BoardSaveAllSync').click(function() {
            // alert();
            let qty_list = [];
            let discount = $('#show_quot_boarditem_table input[name="input_qty_text"]').each(function(ind) {
                let id = $(this).attr("id");
                let itemNPriceNSubgroup = $(this).attr("data-select2id");
                let item_id = $("#"+itemNPriceNSubgroup).val();
                let val = $(this).val();
                qty_list.push({
                    val: val,
                    id: id,
                    itemprice_id: item_id,
                    selectid: itemNPriceNSubgroup
                });
            });
            $("#BoardSaveAllSync").html(
                '<i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i> <span  >Saving...</span>'
            );

    
            var ajaxURLQuotItemQtySave = '{{ route('quot.quot.item.newqty.save') }}';
            $.ajax({
                type: 'GET',
                url: ajaxURLQuotItemQtySave,
                data: {
                    "arry_qty": qty_list,
                    "_token": $("[name=_token").val(),
                },
                success: function(resultData) {
                    toastr["success"](resultData['msg']);
                    $("#BoardSaveAllSync").html("Save All");
                    quotation_board_summary(csrfToken, board_range, quot_id, quot_groupid,
                        quot_rommno, quot_boardno);
                    reloadTable();
                    QuotBoardItemEditTable.ajax.reload(null, false);
                }
            });

        });

        $('.BoardRangeChangeSave').click(function() {
            quot_board_change_range_save(quot_id, quot_groupid, quot_rommno, quot_boardno, board_range);
        });

        // NEW UPDATE START

        $('#add_board_addon').on('click', function() {
            $('#addboardaddonsform')[0].reset();
            $('#q_board_addons_select_id').val('').trigger('change');
            $('#modalQuatBoardAddAddons').modal('show');
            $('.close').on('click', function() {
                $('#modalQuatBoardAddAddons').modal('hide');
            })
        });

        $("#q_board_addons_select_id").select2({
            ajax: {
                url: ajaxURLBoardAddons,
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
            placeholder: 'Select Item Board Addons',
            dropdownParent: $("#modalQuatBoardAddAddons"),
        }).on('change', function(e) {
            $.ajax({
                url: ajaxURLGetItemPrice,
                data: {
                    "item_price_id": this.value
                },
                success: function(data) {
                    $('#readonlyitemprice').val(data['results']['mrp'])
                }
            })
        });

        $('#BoardAddonSave').click(function() {

            $("#BoardAddonSave").html(
                '<i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i> <span  >Saving...</span>'
            );
            id = $("#q_board_addons_select_id").val();
            qty = $('#q_board_addon_qty').val();
            price = $('#readonlyitemprice').val();

            if (id == '') {
                toastr["error"]('Please Select Item');
            } else if (qty == '') {
                toastr["error"]('Please Enter Item Qty');
            } else if (price == '') {
                toastr["error"]('Please Select Item Again');
            } else {
                $.ajax({
                    url: ajaxURLSaveBoardAddon,
                    type: "POST",
                    data: {
                        "_token": csrfToken,
                        "item_price_id": id,
                        "item_qty": qty,
                        "item_price": price,
                        "room_no": quot_rommno,
                        "board_no": quot_boardno,
                        "quot_id": quot_id,
                        "quot_groupid": quot_groupid,
                    },
                    success: function(data) {
                        if (data['status'] == 1) {
                            toastr["success"](data['msg'] + ' ✅');
                            $("#BoardAddonSave").html("Save");
                            QuotBoardItemEditTable.ajax.reload(null, false);
                            quotation_board_summary(csrfToken, board_range, quot_id, quot_groupid,
                                quot_rommno, quot_boardno);
                            $('#modalQuatBoardAddAddons').modal('hide');
                        } else {
                            toastr["error"](data['msg']);
                        }
                    },
                })
            }

        })

        // NEW UPDATE END
    }

    function quot_board_error_detail(quot_id, quot_groupid, quot_rommno, quot_boardno) {
        var csrfToken = $("[name=_token").val()
        $("#modalQuotBoardErrorDetail").modal('show');
        $('#show_quot_board_error_item_table').DataTable().destroy();
        // $('#show_quot_board_error_item_table').empty();
        var ajaxQuotBoardErrorDetailDataURL = '{{ route('quot.board.error.detail.data') }}';

        QuotBoardErrorItemTable = $('#show_quot_board_error_item_table').DataTable({
            "aoColumnDefs": [{
                "bSortable": false,
                "aTargets": [0, 2, 3, 4, 5]
            }],
            "order": [
                [0, 'desc']
            ],
            "processing": true,
            "serverSide": true,
            "bDestroy": true,
            "pagingType": "full_numbers",
            "pageLength": 100,
            "ajax": {
                "url": ajaxQuotBoardErrorDetailDataURL,
                "type": "POST",
                "data": {
                    "_token": csrfToken,
                    "quot_id": quot_id,
                    "quot_groupid": quot_groupid,
                    "quot_rommno": quot_rommno,
                    "quot_boardno": quot_boardno
                }
            },
            "aoColumns": [{
                    "mData": "id"
                },
                {
                    "mData": "item"
                },
                {
                    "mData": "brand"
                },
                {
                    "mData": "module"
                },
                {
                    "sClass": "qtyRightBorder",
                    "mData": "rate"
                },
                {
                    "mData": "new_brand"
                },
                {
                    "mData": "action"
                }
            ]
        });
    }

    function delete_board_error_Warning(id) {

        var ajaxQuotBoardErrorDeleteURL = '{{ route('quot.board.error.delete') }}';
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
                        url: ajaxQuotBoardErrorDeleteURL + "?id=" + id,
                        success: function(resultData) {
                            if (resultData['status'] == 1) {
                                QuotBoardErrorItemTable.ajax.reload(null, false);
                                reloadTable();
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
                                reloadTable();
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

    function quotation_board_summary(csrfToken, board_range, quot_id, quot_groupid, quot_rommno, quot_boardno) {
        var ajaxShowRangeDataURL = '{{ route('quot.show.range.data.ajax') }}';
        $.ajax({
            type: 'POST',
            url: ajaxShowRangeDataURL,
            data: {
                "_token": csrfToken,
                "range_type": 'BOARDRANGE',
                "range": board_range,
                "quot_id": quot_id,
                "quot_groupid": quot_groupid,
                "quot_rommno": quot_rommno,
                "quot_boardno": quot_boardno
            },
            success: function(resultData) {
                if (resultData['status'] == 1) {

                    resultData['data'].forEach(optionvalue);

                    function optionvalue(value, key, arr) {
                        // QUOTE PLATE OPTION
                        if (key == 0) {
                            // alert(value);
                            $("#q_board_subgroup_plte_id").empty().trigger('change');
                            var newOption = new Option(value['text'], value['id'], false, false);
                            $('#q_board_subgroup_plte_id').append(newOption).trigger('change');
                            $('#bd_range_plate').text(value['text']);

                        }
                        // QUOTE ACCESSORIES OPTION
                        if (key == 1) {
                            // alert(key);
                            $("#q_board_subgroup_access_id").empty().trigger('change');
                            var newOption = new Option(value['text'], value['id'], false, false);
                            $('#q_board_subgroup_access_id').append(newOption).trigger('change');
                            $('#bd_range_accessories').text(value['text']);

                        }
                        // QUOTE WHITELION OPTION
                        if (key == 2) {
                            // alert(arr);
                            $("#q_board_subgroup_whitelion_id").empty().trigger('change');
                            var newOption = new Option(value['text'], value['id'], false, false);
                            $('#q_board_subgroup_whitelion_id').append(newOption).trigger('change');
                            $('#bd_range_whitelion').text(value['text']);
                        }
                    }

                    resultData['board_detail'].forEach(board_detail_value);

                    function board_detail_value(value, key, arr) {
                        // QUOTE BOARD DETAIL SHOW
                        if (key == 0) {
                            // alert(value);

                            $("#bd_board_image").attr('src', value['board_image']);
                            $('#bd_room_name').text(value['room_name'] + ' [' + value['room_no'] + ']');
                            $('#bd_board_name').text(value['board_name'] + ' [' + value['board_no'] + ']');
                            $('#bd_amt_gross_amount').text(value['gross_amount']);
                            $('#bd_amt_cgst_amount').text(value['cgst_amount']);
                            $('#bd_amt_sgst_amount').text(value['sgst_amount']);
                            $('#bd_amt_net_amount').text(value['net_amount']);

                        }
                    }

                    // toastr["success"](resultData['msg']+' ✅');
                } else {
                    toastr["error"](resultData['msg']);
                }
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
                                reloadTable();
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

    // --------- QUOTATION RANGE CHANGE ** START ** ---------
    function changerange() {
        var csrfToken = $("[name=_token").val()
        // modalquotation
        $("#modalChangeRange").modal('show');

        var oldrange = $('#quotation_range').val();
        var ajaxShowRangeDataURL = '{{ route('quot.show.range.data.ajax') }}';

        $.ajax({
            type: 'POST',
            url: ajaxShowRangeDataURL,
            data: {
                "_token": csrfToken,
                "range_type": 'QUOTATIONRANGE',
                "range": oldrange,
                "quot_id": '',
                "quot_groupid": '',
                "quot_rommno": '',
                "quot_boardno": ''
            },
            success: function(resultData) {
                if (resultData['status'] == 1) {

                    resultData['data'].forEach(optionvalue);

                    function optionvalue(value, key, arr) {
                        // QUOTE PLATE OPTION
                        if (key == 0) {
                            // alert(value);
                            $("#q_subgroup_plte_id").empty().trigger('change');
                            var newOption = new Option(value['text'], value['id'], false, false);
                            $('#q_subgroup_plte_id').append(newOption).trigger('change');
                        }
                        // QUOTE ACCESSORIES OPTION
                        if (key == 1) {
                            // alert(key);
                            $("#q_subgroup_access_id").empty().trigger('change');
                            var newOption = new Option(value['text'], value['id'], false, false);
                            $('#q_subgroup_access_id').append(newOption).trigger('change');
                        }
                        // QUOTE WHITELION OPTION
                        if (key == 2) {
                            // alert(arr);
                            $("#q_subgroup_whitelion_id").empty().trigger('change');
                            var newOption = new Option(value['text'], value['id'], false, false);
                            $('#q_subgroup_whitelion_id').append(newOption).trigger('change');
                        }
                    }

                    // toastr["success"](resultData['msg']+' ✅');
                } else {
                    toastr["error"](resultData['msg']);
                }
            }
        });
    }

    function quot_change_range_save() {
        var csrfToken = $("[name=_token").val()
        var plate = $('#q_subgroup_plte_id').val();
        var accessories = $('#q_subgroup_access_id').val();
        var whitelion = $('#q_subgroup_whitelion_id').val();

        var ajaxChangeQuotRangeURL = '{{ route('quot.range.change.ajax') }}';
        var quotid = $('#quotation_id').val();
        var quotgroupid = $('#quotation_group_id').val();
        var newrange = plate + ',' + accessories + ',' + whitelion;

        if ($('#q_subgroup_plte_id option[value]:selected').text() == '') {
            toastr["error"]('Please Select Accessories ⛔️');
        } else if ($('#q_subgroup_access_id option[value]:selected').text() == '') {
            toastr["error"]('Please Select Accessories ⛔️');
        } else if ($('#q_subgroup_whitelion_id option[value]:selected').text() == '') {
            toastr["error"]('Please Select Accessories ⛔️');
        } else {
            $.ajax({
                type: 'POST',
                url: ajaxChangeQuotRangeURL,
                data: {
                    "_token": csrfToken,
                    "quot_id": quotid,
                    "quotgroup_id": quotgroupid,
                    "old_range": $('#quotation_range').val(),
                    "range": newrange,
                    "room_no": 0,
                    "board_no": 0,
                    "type": 'FULL'
                },
                success: function(resultData) {
                    if (resultData['status'] == 1) {
                        toastr["success"](resultData['msg'], ' ✅');
                        $("#modalChangeRange").modal('hide');
                        reloadTable();
                    } else {
                        toastr["error"](resultData['msg']);
                    }

                }
            });
        }
    }

    function quot_board_change_range_save(quot_id, quot_groupid, quot_rommno, quot_boardno, board_range) {
        var csrfToken = $("[name=_token").val()
        var plate = $('#q_board_subgroup_plte_id').val();
        var accessories = $('#q_board_subgroup_access_id').val();
        var whitelion = $('#q_board_subgroup_whitelion_id').val();

        var ajaxChangeQuotRangeURL = '{{ route('quot.range.change.ajax') }}';
        // var quotid = $('#quotation_id').val();
        // var quotgroupid = $('#quotation_group_id').val();
        var newrange = plate + ',' + accessories + ',' + whitelion;

        if ($('#q_board_subgroup_plte_id option[value]:selected').text() == '') {
            toastr["error"]('Please Select Accessories ⛔️');
        } else if ($('#q_board_subgroup_access_id option[value]:selected').text() == '') {
            toastr["error"]('Please Select Accessories ⛔️');
        } else if ($('#q_board_subgroup_whitelion_id option[value]:selected').text() == '') {
            toastr["error"]('Please Select Accessories ⛔️');
        } else {
            $.ajax({
                type: 'POST',
                url: ajaxChangeQuotRangeURL,
                data: {
                    "_token": csrfToken,
                    "quot_id": quot_id,
                    "quotgroup_id": quot_groupid,
                    "old_range": board_range,
                    "range": newrange,
                    "room_no": quot_rommno,
                    "board_no": quot_boardno,
                    "type": 'BOARD'
                },
                success: function(resultData) {
                    if (resultData['status'] == 1) {
                        toastr["success"](resultData['msg'], ' ✅');
                        reloadTable();
                    } else {
                        toastr["error"](resultData['msg']);
                    }

                }
            });
        }
    }
    // --------- QUOTATION RANGE CHANGE ** END ** ---------

    function add_discount_model(quot_id) {
        $("#modalQuotItemDiscountDetail").modal('show');

        $(".ItemWiseDiscount").hide();
        $(".BrandWiseDiscount").hide();
        $('input[type=radio][name=radioDiscountFilter]').change(function() {
            $('#discount_type').val(this.value);
            if (this.value == 'ALL') {
                $(".ItemWiseDiscount").hide();
                $(".BrandWiseDiscount").hide();
                reloadDiscountTable();
            } else if (this.value == 'ITEMWISE') {
                $(".ItemWiseDiscount").show();
                $(".BrandWiseDiscount").hide();
            } else if (this.value == 'BRANDWISE') {
                $(".ItemWiseDiscount").hide();
                $(".BrandWiseDiscount").show();
            }
        });

        $("#discount_item_select").select2({
            ajax: {
                url: ajaxURLSearchItem,
                dataType: 'json',
                delay: 0,
                data: function(params) {
                    return {
                        "quot_id": function() {
                            return $('#quotation_id').val();
                        },
                        "quot_groupid": function() {
                            return $("#quotation_group_id").val();
                        },
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
            placeholder: 'Select Item',
            dropdownParent: $("#modalQuotItemDiscountDetail"),
        }).on('change', function(e) {
            QuotItemDiscountTable.ajax.reload(null, false);
        });

        $("#discount_brand_select").select2({
            ajax: {
                url: ajaxURLSearchItemBrand,
                dataType: 'json',
                delay: 0,
                data: function(params) {
                    return {
                        "quot_id": function() {
                            return $('#quotation_id').val();
                        },
                        "quot_groupid": function() {
                            return $("#quotation_group_id").val();
                        },
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
            placeholder: 'Select Item Brand',
            dropdownParent: $("#modalQuotItemDiscountDetail"),
        }).on('change', function(e) {
            QuotItemDiscountTable.ajax.reload(null, false);

        });


        $('#saveBrandWiseDiscount').click(function() {
            $("#discountSync").html(
                '<i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i> <span  >Saving...</span>'
            );
            var ajaxURLQuotBrandWiseDiscountSave = '{{ route('quot.quot.item.discount.brandwise.save') }}';
            if ($('#discount_brandwise_discount').val() == '') {
                toastr["error"]('Please Enter Discount ⛔️');
                $("#discountSync").html("Save All");
            } else if ($('#discount_type').val() == 'ALL') {
                $.ajax({
                    type: 'GET',
                    url: ajaxURLQuotBrandWiseDiscountSave,
                    data: {
                        "quot_id": $('#quotation_id').val(),
                        "quot_group_id": $("#quotation_group_id").val(),
                        "discount_type": $('#discount_type').val(),
                        "item": '0',
                        "brand": '0',
                        "discount": $('#discount_brandwise_discount').val(),
                        "_token": $("[name=_token").val(),
                    },
                    success: function(resultData) {

                        $("#discountSync").html("Save All");

                        toastr["success"](resultData['msg']);
                        reloadTable();
                        reloadDiscountTable();
                    }
                });

            } else if ($('#discount_type').val() == 'ITEMWISE') {
                $.ajax({
                    type: 'GET',
                    url: ajaxURLQuotBrandWiseDiscountSave,
                    data: {
                        "quot_id": $('#quotation_id').val(),
                        "quot_group_id": $("#quotation_group_id").val(),
                        "discount_type": $('#discount_type').val(),
                        "item": $('#discount_item_select').val(),
                        "brand": '0',
                        "discount": $('#discount_brandwise_discount').val(),
                        "_token": $("[name=_token").val(),
                    },
                    success: function(resultData) {

                        $("#discountSync").html("Save All");

                        toastr["success"](resultData['msg']);
                        reloadTable();
                        reloadDiscountTable();
                    }
                });
            } else if ($('#discount_type').val() == 'BRANDWISE') {
                $.ajax({
                    type: 'GET',
                    url: ajaxURLQuotBrandWiseDiscountSave,
                    data: {
                        "quot_id": $('#quotation_id').val(),
                        "quot_group_id": $("#quotation_group_id").val(),
                        "discount_type": $('#discount_type').val(),
                        "item": '0',
                        "brand": $('#discount_brand_select').val(),
                        "discount": $('#discount_brandwise_discount').val(),
                        "_token": $("[name=_token").val(),
                    },
                    success: function(resultData) {

                        $("#discountSync").html("Save All");

                        toastr["success"](resultData['msg']);
                        reloadTable();
                        reloadDiscountTable();
                    }
                });
            }


        });


        $('#discountSync').click(function() {
            // alert();
            let list = [];
            let discount = $('#quot_changediscount_table input[name="input_discount_text"]').each(function(
                ind) {
                let id = $(this).attr("id");
                let val = $(this).val();
                let company = $(this).attr("data-company");
                let group = $(this).attr("data-group");
                let subgroup = $(this).attr("data-subgroup");
                let item_id = $(this).attr("data-item_id");
                let item_price_id = $(this).attr("data-item_price_id");
                list.push({
                    id: id,
                    val: val,
                    company: company,
                    group: group,
                    subgroup: subgroup,
                    item_id: item_id,
                    item_price_id: item_price_id,
                });
            });
            $("#discountSync").html(
                '<i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i> <span  >Saving...</span>'
            );
            var ajaxURLQuotItemDiscountSave = '{{ route('quot.quot.item.discount.save') }}';

            $.ajax({
                type: 'GET',
                url: ajaxURLQuotItemDiscountSave,
                data: {
                    "change_type": 'SAVEALL',
                    "quot_id": $('#quotation_id').val(),
                    "quot_group_id": $("#quotation_group_id").val(),
                    "discount": list,
                    "id": '0',
                    "_token": $("[name=_token").val(),
                },
                success: function(resultData) {

                    $("#discountSync").html("Save All");

                    toastr["success"](resultData['msg']);
                    reloadTable();
                    reloadDiscountTable();
                }
            });

        });


        var ajaxQuotItemDisDetailDataURL = '{{ route('quot.quot.item.discountdetail.data') }}';

        QuotItemDiscountTable = $('#quot_changediscount_table').DataTable({
            "aoColumnDefs": [{
                "bSortable": false,
                "aTargets": [0, 2, 3, 4, 5, 6, 7, 8, 9]
            }],
            // "paging": false,
            // "bPaginate": false,
            "processing": true,
            "serverSide": true,
            "bDestroy": true,
            "pagingType": "full_numbers",
            "pageLength": 10,
            "ajax": {
                "url": ajaxQuotItemDisDetailDataURL,
                "type": "GET",
                "data": {
                    "_token": csrfToken,
                    "quot_id": quot_id,
                    "discount_type": function() {
                        return $('#discount_type').val()
                    },
                    "list_filter": function() {
                        return $('#discount_type').val() == 'ITEMWISE' ? $('#discount_item_select').val() :
                            $('#discount_type').val() == 'BRANDWISE' ? $('#discount_brand_select').val() :
                            $('#discount_type').val() == 'ALL' ? 'ALL' : '0'
                    }
                }
            },
            "aoColumns": [{
                    "mData": "id"
                },
                {
                    "mData": "item"
                },
                {
                    "mData": "brand"
                },
                {
                    "mData": "module"
                },
                {
                    "mData": "qty"
                },
                {
                    "mData": "rate"
                },
                {
                    "mData": "discount"
                },
                {
                    "mData": "grossamount"
                },
                {
                    "mData": "gst"
                },
                {
                    "mData": "netamount"
                }
            ]
        });
        discount_dialog_summary();
    }

    function reloadDiscountTable() {
        QuotItemDiscountTable.ajax.reload(null, false);
        discount_dialog_summary();
    }

    function discount_dialog_summary() {
        $.ajax({
            type: 'GET',
            url: ajaxURLQuotationSummary,
            data: {
                "quot_id": $('#quotation_id').val(),
            },
            success: function(resultData) {
                if (resultData['status'] == 1) {
                    // toastr["success"](resultData['msg']); 
                    var quotation_detail_summary = resultData['quotation_detail_summary'];
                    var quotation_amount_summary = resultData['quotation_amount_summary'];
                    $('#su_disc_quot_no').text('Q'+quotation_detail_summary['quotno']);
                    $('#su_disc_quot_date').text(quotation_detail_summary['quot_date']);
                    $('#su_disc_quot_type').text(quotation_detail_summary['type_name']);
                    $('#su_disc_quot_version').text(quotation_detail_summary['quot_no_str']);
                    $('#su_disc_quot_site_name').text(quotation_detail_summary['site_name']);
                    $('#su_disc_quot_site_address').text(quotation_detail_summary['siteaddress']);
                    $('#su_disc_quot_cust_name').text(quotation_detail_summary['customer_name']);
                    $('#su_disc_quot_cust_mobile').text(quotation_detail_summary['customer_contact_no']);

                    $('#su_disc_range_plate').text(resultData['quot_range_plate']);
                    $('#su_disc_range_accessories').text(resultData['quot_range_acc']);
                    $('#su_disc_range_whitelion').text(resultData['quot_range_whitelion']);
                    $('#su_disc_quot_Status').html(resultData['quot_status']);

                    $('#su_disc_quote_total_gross_amt').text(Math.round(quotation_amount_summary[
                        'gross_amount']).toFixed(2));
                    $('#su_disc_quote_total_cgst_amt').text(Math.round(quotation_amount_summary[
                        'cgst_amount']).toFixed(2));
                    $('#su_disc_quote_total_sgst_amt').text(Math.round(quotation_amount_summary[
                        'sgst_amount']).toFixed(2));
                    $('#su_disc_quote_total_net_amt').text(Math.round(quotation_amount_summary[
                        'net_amount']).toFixed(2));

                } else {
                    toastr["error"]('Please Refresh Page');
                }

            }
        });

    }

    function changeqty(id) {
        var qty = $('#' + id).val();
        var rate = $('#' + id).closest('tr').find('.rate').text();
        var discount = $('#' + id).attr("data-discount");
        var grossamount = $('#' + id).closest('tr').find('.grossamount').text();
        var igst = $('#' + id).attr("data-igstper");
        var cgst = $('#' + id).attr("data-cgstper");
        var sgst = $('#' + id).attr("data-sgstper");
        var gst = $('#' + id).closest('tr').find('.gst').text();
        var netamount = $('#' + id).closest('tr').find('.netamount').text();

        var new_sub_total = (parseFloat(qty) * parseFloat(rate)).toFixed(2);

        var new_discount_amount = (parseFloat(new_sub_total) * parseFloat(discount) / 100).toFixed(2);

        var new_gross_amount = (parseFloat(new_sub_total) - parseFloat(new_discount_amount)).toFixed(2);
        $('#' + id).closest('tr').find('.grossamount').text(new_gross_amount);

        var new_gst_amount = ((parseFloat(new_gross_amount) * parseFloat(igst) / 100) + (parseFloat(new_gross_amount) *
            parseFloat(cgst) / 100) + (parseFloat(new_gross_amount) * parseFloat(sgst) / 100)).toFixed(2);
        $('#' + id).closest('tr').find('.gst').text(new_gst_amount);

        var new_net_amount = (parseFloat(new_gross_amount) + parseFloat(new_gst_amount)).toFixed(2);
        $('#' + id).closest('tr').find('.netamount').text(new_net_amount);

    }

    function changediscount(id) {
        var qty = $('#' + id).closest('tr').find('.qty').text();
        var rate = $('#' + id).closest('tr').find('.rate').text();
        var discount = $('#' + id).val();
        var grossamount = $('#' + id).closest('tr').find('.grossamount').text();
        var igst = $('#' + id).attr(" er");
        var cgst = $('#' + id).attr("data-cgstper");
        var sgst = $('#' + id).attr("data-sgstper");
        var gst = $('#' + id).closest('tr').find('.gst').text();
        var netamount = $('#' + id).closest('tr').find('.netamount').text();

        var new_sub_total = (parseFloat(qty) * parseFloat(rate)).toFixed(2);

        var new_discount_amount = (parseFloat(new_sub_total) * parseFloat(discount) / 100).toFixed(2);

        var new_gross_amount = (parseFloat(new_sub_total) - parseFloat(new_discount_amount)).toFixed(2);
        $('#' + id).closest('tr').find('.grossamount').text(new_gross_amount);

        var new_gst_amount = ((parseFloat(new_gross_amount) * parseFloat(igst) / 100) + (parseFloat(new_gross_amount) *
            parseFloat(cgst) / 100) + (parseFloat(new_gross_amount) * parseFloat(sgst) / 100)).toFixed(2);
        $('#' + id).closest('tr').find('.gst').text(new_gst_amount);

        var new_net_amount = (parseFloat(new_gross_amount) + parseFloat(new_gst_amount)).toFixed(2);
        $('#' + id).closest('tr').find('.netamount').text(new_net_amount);

    }
</script>
