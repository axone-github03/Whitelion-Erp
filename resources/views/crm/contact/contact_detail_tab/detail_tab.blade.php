<div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
    <b>Details <div class="lds-spinner" id="detail_loader" style="display: none;">
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
        </div></b>

    <div>
        <button onclick="editLead()"
            class="btn btn-sm btn-light btn-header-right waves-effect waves-light float-end ms-2 mt-1" type="button"
            style="margin-left:3px;"><i class="fas fa-pencil-alt font-size-16 align-middle "></i>
        </button>
        <button type="button" class="btn btn-primary waves-effect waves-light float-end" id="SaveEditField"
            onclick="saveDetailUpdate()">Save</button>
    </div>
</div>
<div class="card-body border-bottom" id="lead_detail">
    <form style="margin-top: 20px;">
        <div class="row">
            <div class="col-md-6">
                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Contact Owner</label>
                    <div class="col-sm-8">
                        <input class="form-control" id="lead_detail_first_name" name="lead_detail_first_name" placeholder="Account Owner" value="" disabled>
                    </div>
                </div>
                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Mobile Number</label>
                    <div class="col-sm-8">
                        <input class="form-control" id="lead_detail_first_name" name="lead_detail_first_name" placeholder="Mobile Number" value="" disabled>
                    </div>
                </div>
                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Email id</label>
                    <div class="col-sm-8">
                        <input class="form-control" id="lead_detail_first_name" name="lead_detail_first_name" placeholder="Email id" value="" disabled>
                    </div>
                </div>

                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Created By</label>
                    <div class="col-sm-8">
                        <input class="form-control" id="lead_detail_first_name" name="lead_detail_first_name" placeholder="Architect" value="" disabled>
                    </div>
                </div>

                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Last Modifyed By</label>
                    <div class="col-sm-8">
                        <input class="form-control" id="lead_detail_first_name" name="lead_detail_first_name" placeholder="Electrician" value="" disabled>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Account</label>
                    <div class="col-sm-8">
                        <input class="form-control" id="lead_detail_first_name" name="lead_detail_first_name" placeholder="Account" value="   " disabled>
                    </div>
                </div>
                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Site Address</label>
                    <div class="col-sm-3">
                        <input class="form-control" id="lead_detail_first_name" name="lead_detail_first_name" placeholder="No" value="" disabled>
                    </div>
                    <div class="col-sm-5">
                        <input class="form-control" id="lead_detail_first_name" name="lead_detail_first_name" placeholder="Address Line 1" value="" disabled>
                    </div>
                </div>
                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label"></label>
                    <div class="col-sm-8">
                        <input class="form-control" id="lead_detail_first_name" name="lead_detail_first_name" placeholder="Address Line 2" value="" disabled>
                    </div>
                </div>

                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label"></label>
                    <div class="col-sm-8">
                        <input class="form-control" id="lead_detail_first_name" name="lead_detail_first_name" placeholder="Area" value="" disabled>
                    </div>
                </div>

                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label"></label>
                    <div class="col-sm-8">
                        <input class="form-control" id="lead_detail_first_name" name="lead_detail_first_name" placeholder="City" value="" disabled>
                    </div>
                </div>

                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label"></label>
                    <div class="col-sm-4">
                        <input class="form-control" id="lead_detail_first_name" name="lead_detail_first_name" placeholder="State" value="" disabled>
                    </div>
                    <div class="col-sm-4">
                        <input class="form-control" id="lead_detail_first_name" name="lead_detail_first_name" placeholder="Country" value="" disabled>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>