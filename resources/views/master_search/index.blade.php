<style>
    .title-text {
        background-color: #d3d3d354;
        font-weight: 500;
        font-size: 14px;
        color: darkslateblue;
        background-color: #d3d3d354;
        font-weight: 500;
        font-size: 14px;
        color: darkslateblue;
    }

    .highlight {
        background-color: #ffeb00;
        padding-right: 2px;
        padding-left: 2px;
    }

    .nav-item-master-search {
        margin-right: 10px;
        margin-bottom: 10px;
        font-size: 14px;
        border-radius: 5px;
    }

    .nav-item-master-search a.nav-link {
        color: black;
        transition: background-color 0.3s, color 0.3s;
    }

    .nav-item-master-search a.nav-link:hover {
        background-color: white;
        color: blue;
    }

    .nav-item-master-search a.nav-link.active {
        color: #001aff !important;
        background-color: white !important;
        box-shadow: 0 0.1rem 0.5rem rgba(18, 50, 63, 0.35) !important;
        border: 1px solid blue !important;
        border-radius: 5px;
    }
</style>

<div class="modal fade" id="modalMasterSearch" data-bs-backdrop="static" tabindex="-1" role="dialog"
    aria-labelledby="modalMasterLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen p-5" role="document">
        <div class="modal-content rounded">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="modalMasterLabel">Master Search</h5>
                </div>
                <div class="input-group" style="width: 30% !important;">
                    <input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search"
                        id="master_search_input" name="master_search_input">
                    <div class="input-group-append">
                        <button class="btn btn-outline-success" type="button" id="mst_search_button">Search</button>
                    </div>
                    <div class="input-group-append">
                        <button class="btn btn-light ms-3 border-dark" type="button"
                            id="mst_search_clear">Clear</button>
                    </div>
                </div>

                <div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-0" style="background-color: #d3d3d354;" id="master_search_body">
                <div class="h-100">
                    <div class="col-12 p-2">
                        <ul class="nav" role="tablist">
                            <li class="nav-item-master-search mb-1" style="margin-left: 1%" id="li_sales">
                                <a class="nav-link navigationbtn" data-bs-toggle="tab" href="#sales-tab" role="tab">
                                    <span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
                                    <span class="d-none d-sm-block text-black">Sales (<span
                                            id="sales_count">0</span>)</span>
                                </a>
                            </li>
                            <li class="nav-item-master-search mb-1" style="margin-left: 2%" id="li_architect">
                                <a class="nav-link navigationbtn" data-bs-toggle="tab" href="#architect-tab" role="tab">
                                    <span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
                                    <span class="d-none d-sm-block text-black">Architect (<span
                                            id="architect_count">0</span>)</span>
                                </a>
                            </li>
                            <li class="nav-item-master-search mb-1" style="margin-left: 2%" id="li_electrician">
                                <a class="nav-link navigationbtn" data-bs-toggle="tab" href="#electrician-tab" role="tab">
                                    <span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
                                    <span class="d-none d-sm-block text-black">Electrician (<span
                                            id="electrician_count">0</span>)</span>
                                </a>
                            </li>
                            <li class="nav-item-master-search mb-1" style="margin-left: 2%" id="li_lead">
                                <a class="nav-link navigationbtn" data-bs-toggle="tab" href="#lead-tab" role="tab">
                                    <span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
                                    <span class="d-none d-sm-block text-black">Lead (<span
                                            id="lead_count">0</span>)</span>
                                </a>
                            </li>
                            <li class="nav-item-master-search mb-1" style="margin-left: 2%" id="li_deal">
                                <a class="nav-link navigationbtn" data-bs-toggle="tab" href="#deal-tab" role="tab">
                                    <span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
                                    <span class="d-none d-sm-block text-black">Deal (<span
                                            id="deal_count">0</span>)</span>
                                </a>
                            </li>
                            <li class="nav-item-master-search mb-1" style="margin-left: 2%" id="li_quotation">
                                <a class="nav-link navigationbtn" data-bs-toggle="tab" href="#quotation-tab" role="tab">
                                    <span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
                                    <span class="d-none d-sm-block text-black">Quotation (<span
                                            id="quotation_count">0</span>)</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="col-12 p-3" style="background-color: white;">
                        <div class="tab-content text-muted">
                            <div class="tab-pane" id="sales-tab" role="tabpanel">
                                <div class="p-2 title-text">Sales User</div>
                                <table id="sales_user_table"
                                    class="table table-striped dt-responsive nowrap w-100 p-2">
                                    <thead>
                                        <tr>
                                            <th>Id</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Mobile No.</th>
                                            <th>Address</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                            </div>
                            <div class="tab-pane" id="architect-tab" role="tabpanel">
                                <div class="p-2 title-text">Architect</div>
                                <table id="architect_table"
                                    class="table table-striped dt-responsive nowrap w-100 p-2">
                                    <thead>
                                        <tr>
                                            <th>Id</th>
                                            <th>Name</th>
                                            <th>Mobile No.</th>
                                            <th>Address</th>
                                            <th>Firm Name</th>
                                            <th>City</th>
                                            <th>Life Points</th>
                                            <th>Available Points</th>
                                            <th>Instagram Link</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                            </div>
                            <div class="tab-pane" id="electrician-tab" role="tabpanel">
                                <div class="p-2 title-text">Electrician</div>
                                <table id="electrician_table"
                                    class="table table-striped dt-responsive nowrap w-100 p-2">
                                    <thead>
                                        <tr>
                                            <th>Id</th>
                                            <th>Name</th>
                                            <th>Mobile No.</th>
                                            <th>Address</th>
                                            <th>City</th>
                                            <th>Life Points</th>
                                            <th>Available Points</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                            </div>
                            <div class="tab-pane" id="lead-tab" role="tabpanel">
                                <div class="p-2 title-text">Lead</div>
                                <table id="lead_table" class="table table-striped dt-responsive nowrap w-100 p-2">
                                    <thead>
                                        <tr>
                                            <th>Id</th>
                                            <th>Name</th>
                                            <th>Status</th>
                                            <th>City</th>
                                            <th>Mobile No.</th>
                                            <th>Address</th>
                                            <th>Site Stage</th>
                                            <th>Source Type</th>
                                            <th>Source</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>

                            </div>
                            <div class="tab-pane" id="deal-tab" role="tabpanel">
                                <div class="p-2 title-text">Deal</div>
                                <table id="deal_table" class="table table-striped dt-responsive nowrap w-100 p-2">
                                    <thead>
                                        <tr>
                                            <th>Id</th>
                                            <th>Name</th>
                                            <th>Status</th>
                                            <th>City</th>
                                            <th>Mobile No.</th>
                                            <th>Address</th>
                                            <th>Site Stage</th>
                                            <th>Source Type</th>
                                            <th>Source</th>
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
    </div>
</div>
