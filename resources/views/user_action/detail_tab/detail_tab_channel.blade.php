<div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
    <b>Details 
    </b>
    <div>
        @if (isAdminOrCompanyAdmin() == 1 && $data['is_architect'] == 1)
            <button onclick="editDetailArchitect({{ $data['user']['id'] }})" class="btn btn-sm btn-light btn-header-right waves-effect waves-light float-end ms-2 mt-1" type="button" style="margin-left:3px;"><i class="fas fa-pencil-alt font-size-16 align-middle "></i></button>
        @endif
        @if (isSalePerson() == 1 && $data['is_architect'] == 1 && $data['user']['type'] == 201)
            <button onclick="editDetailArchitect({{ $data['user']['id'] }})" class="btn btn-sm btn-light btn-header-right waves-effect waves-light float-end ms-2 mt-1" type="button" style="margin-left:3px;"><i class="fas fa-pencil-alt font-size-16 align-middle "></i></button>
        @endif
        @if (isTaleSalesUser() == 1 && $data['is_architect'] == 1 && $data['user']['type'] == 202)
            <button onclick="editDetailArchitect({{ $data['user']['id'] }})"
                class="btn btn-sm btn-light btn-header-right waves-effect waves-light float-end ms-2 mt-1" type="button"
                style="margin-left:3px;"><i class="fas fa-pencil-alt font-size-16 align-middle "></i></button>
        @endif
        @if (isTaleSalesUser() == 1 && $data['is_architect'] == 1 && $data['user']['type'] == 201)
            <button onclick="editDetailArchitect({{ $data['user']['id'] }})"
                class="btn btn-sm btn-light btn-header-right waves-effect waves-light float-end ms-2 mt-1" type="button"
                style="margin-left:3px;"><i class="fas fa-pencil-alt font-size-16 align-middle "></i></button>
        @endif

        @if (isAdminOrCompanyAdmin() == 1 && $data['is_electrician'] == 1)
            <button onclick="editView({{ $data['user']['id'] }})" class="btn btn-sm btn-light btn-header-right waves-effect waves-light float-end ms-2 mt-1" type="button" style="margin-left:3px;"><i class="fas fa-pencil-alt font-size-16 align-middle "></i></button>
        @endif
        @if (isSalePerson() == 1 && $data['is_electrician'] == 1 && $data['user']['type'] == 301)
            <button onclick="editView({{ $data['user']['id'] }})" class="btn btn-sm btn-light btn-header-right waves-effect waves-light float-end ms-2 mt-1" type="button" style="margin-left:3px;"><i class="fas fa-pencil-alt font-size-16 align-middle "></i></button>
        @endif
        @if (isTaleSalesUser() == 1 && $data['is_electrician'] == 1 && $data['user']['type'] == 302)
            <button onclick="editView({{ $data['user']['id'] }})" class="btn btn-sm btn-light btn-header-right waves-effect waves-light float-end ms-2 mt-1" type="button" style="margin-left:3px;"><i class="fas fa-pencil-alt font-size-16 align-middle "></i></button>
        @endif
        @if (isTaleSalesUser() == 1 && $data['is_electrician'] == 1 && $data['user']['type'] == 301)
            <button onclick="editView({{ $data['user']['id'] }})" class="btn btn-sm btn-light btn-header-right waves-effect waves-light float-end ms-2 mt-1" type="button" style="margin-left:3px;"><i class="fas fa-pencil-alt font-size-16 align-middle "></i></button>
        @endif
    </div>
</div>
<div class="card-body" id="lead_detail">
    <div class="row">
        <div class="col-md-6">
            <div class="row mb-1 align-items-center">
                <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Account Owner</label>
                <div class="col-sm-8">
                    @if (isAdminOrCompanyAdmin() == 1 || isSalePerson() == 1)
                        <select class="form-control select2-ajax" id="user_owner" name="user_owner" onchange="saveDetailUpdate({{ $data['user']['id'] }})"></select>
                    @else
                        <input class="form-control" id="lead_detail_first_name" name="lead_detail_first_name" placeholder="Account Owner" value="{{ ucwords(strtolower($data['user']['account_owner'])) }}" disabled>
                    @endif
                </div>
            </div>

            @if($data['is_architect'] == 1)
                <div class="row mb-1 align-items-center">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Firm Name</label>
                    <div class="col-sm-8">
                        <input class="form-control" id="" name="" placeholder="Firm Name" value="{{ ucwords(strtolower($data['user']['firm_name'])) }}" disabled>
                    </div>
                </div>
            @endif

            <div class="row mb-1 align-items-center">
                <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Mobile Number</label>
                <div class="col-sm-8">
                    <input class="form-control" id="lead_detail_first_name" name="lead_detail_first_name" placeholder="Mobile Number" value="{{ $data['user']['phone_number'] }}" disabled>
                </div>
            </div>

            <div class="row mb-1 align-items-center">
                <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Email Id</label>
                <div class="col-sm-8">
                    <input class="form-control" id="lead_detail_first_name" name="lead_detail_first_name"
                        placeholder="Email Id" value="{{ $data['user']['email'] }}" disabled>
                </div>
            </div>

            <div class="row mb-1 align-items-center">
                <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Created By</label>
                <div class="input-group" style="width: 66.66667% !important;">
                    <input class="form-control" id="lead_detail_first_name" name="lead_detail_first_name"
                        placeholder="Created By" value="{{ $data['user']['created_by'] }}" disabled
                        style="border-top-right-radius: 0px;border-bottom-right-radius: 0px;">

                        <button class="btn btn-light closing-badge3" type="button" id="password-addon"><i
                            class='bx bxs-calendar' style="font-size: 20px;"></i></button>
                    <div class="div_tip3 col-3 rounded" style="display: none; left: 78.5%; top: 37px;">
                        <div class="tip_arrow3"
                            style="border-bottom-color: rgb(191 194 252);border-top-color: transparent; margin: -20px 0px 0px; top: 0px; left: 36%;">
                        </div>
                        <div class="p-1">
                            <div class="tip_name3">
                                <span class="name"><a class="text-dark"
                                        href="javascript:void(0)">{{ $data['user']['created_at1'] }}</a></span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>


            @if (
                $data['user']['status'] == 5 &&  $data['user']['duplicate_from'] != "")
                <div class="row mb-1 align-items-center">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Duplicate From</label>
                    <div class="col-sm-8">
                        <input class="form-control" id="lead_detail_first_name" name="lead_detail_first_name"
                            placeholder="duplicate From" value="{{ $data['user']['duplicate_from']['first_name'] }}  {{ $data['user']['duplicate_from']['last_name'] }}"
                            disabled>
                    </div>
                </div>
            @endif






        </div>
        <div class="col-md-6">
            <div class="row mb-1 align-items-center">
                <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Account Name</label>
                <div class="col-sm-8">
                    <input class="form-control" id="lead_detail_first_name" name="lead_detail_first_name"
                        placeholder="Account Name" value="{{ ucwords(strtolower($data['user']['account_name'])) }}"
                        disabled>
                </div>
            </div>

            <div class="row mb-1 align-items-center">
                <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Address</label>
                <div class="col-sm-8">
                    <div class="input-group">
                        <div class="input-group-text me-2" style="padding: 0;">
                            <input type="text" style="width: 80px;" class="form-control"
                                id="lead_detail_house_no" name="lead_detail_house_no" placeholder="No"
                                value="{{ $data['user']['house_no'] }}" disabled>
                        </div>
                        <input class="form-control" id="lead_detail_addressline1" name="lead_detail_addressline1"
                            placeholder="Addressline 1" value="{{ $data['user']['address_line1'] }}" disabled>
                    </div>
                </div>
            </div>

            <div class="row mb-1 align-items-center  d-none">
                <label for="horizontal-firstname-input" class="col-sm-4 col-form-label"></label>
                <div class="col-sm-8">
                    <input class="form-control" id="lead_detail_addressline2" name="lead_detail_addressline2"
                        placeholder="Addressline 2" value="{{ $data['user']['address_line2'] }}" disabled>
                </div>
            </div>

            <div class="row mb-1 align-items-center">
                <label for="horizontal-firstname-input" class="col-sm-4 col-form-label"></label>
                <div class="col-sm-8">
                    <input class="form-control" id="lead_detail_area" name="lead_detail_area" placeholder="Area"
                        value="{{ $data['user']['area'] }}" disabled>
                </div>
            </div>

            <div class="row mb-1 align-items-center">
                <label for="horizontal-firstname-input" class="col-sm-4 col-form-label"></label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="lead_detail_pincode" name="lead_detail_pincode"
                        placeholder="City" value="{{ $data['user']['city_name'] }}" disabled>
                </div>
            </div>

            <div class="row mb-1 align-items-center">
                <label for="horizontal-firstname-input" class="col-sm-4 col-form-label"></label>
                <div class="col-sm-8 row pe-0">
                    <div class="col-sm-6 pe-0">
                        <input class="form-control" id="lead_detail_city_id" name="lead_detail_city_id"
                            placeholder="State" value="{{ $data['user']['state_name'] }}" disabled>
                    </div>
                    <div class="col-sm-6 pe-0">
                        <input class="form-control" id="lead_detail_city_id" name="lead_detail_city_id"
                            placeholder="Country" value="{{ $data['user']['country_name'] }}" disabled>
                    </div>
                </div>
            </div>

          
            <div class="row mb-1">
                @if(isCreUser() == 0)
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Tag</label>
                    <div class="col-sm-8">
                        <select class="form-control select2-ajax" id="user_tag_id" name="user_tag_id[]" onchange="saveDetailUpdate({{ $data['user']['id'] }})" multiple></select>
                    </div>
                @endif
                </div>
            </div>
    </div>
</div>
