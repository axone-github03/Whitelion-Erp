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
            <button onclick="editLead({{ $data['lead']['id'] }})" class="btn btn-sm btn-light btn-header-right waves-effect waves-light float-end ms-2 mt-1" type="button" style="margin-left:3px;"><i class="fas fa-pencil-alt font-size-16 align-middle "></i></button>
        </div>
</div>
<div class="card-body" id="lead_detail">
    <form style="margin-top: 20px;">
        <div class="row">
            <div class="col-md-6">
                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Lead Owner</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="lead_detail_assigned_to"
                            name="lead_detail_assigned_to" value="{{ ucwords(strtolower($data['lead']['assigned'])) }}"
                            required disabled>
                    </div>
                </div>

                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Client Name</label>
                        <div class="row col-sm-9 pe-0">
                            <div class="col-sm-6 pe-0">
                                <input class="form-control" id="lead_detail_first_name" name="lead_detail_first_name"
                                    placeholder="First Name" value="{{ ucwords(strtolower($data['lead']['first_name'])) }}" disabled>
                            </div>
                            <div class="col-sm-6 pe-0">
                                <input class="form-control" id="lead_detail_last_name" name="lead_detail_last_name"
                                    placeholder="Last Name" value="{{ trim(ucwords(strtolower($data['lead']['last_name']))) }}" disabled>
                            </div>
                        </div>
                </div>

                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Mo.number</label>
                    <div class="col-sm-8">
                        <div class="input-group">
                            <div class="input-group-text">+91 </div>
                            <input type="number" class="form-control" id="lead_detail_phone_number"
                                name="lead_detail_phone_number" placeholder="Phone number"
                                value="{{ $data['lead']['phone_number'] }}" disabled>
                        </div>
                    </div>
                </div>

                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Email</label>
                    <div class="col-sm-8">
                        <input class="form-control" id="lead_detail_email" name="lead_detail_email" placeholder="Email"
                            value="{{ $data['lead']['email'] }}" disabled>
                    </div>
                </div>

                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Site Address</label>
                    <div class="col-sm-8">
                        <div class="input-group">
                            <div class="input-group-text me-2" style="padding: 0;">
                                <input type="text" style="width: 80px;" class="form-control"
                                    id="lead_detail_house_no" name="lead_detail_house_no" placeholder="No"
                                    value="{{ $data['lead']['house_no'] }}" disabled>
                            </div>
                            <input class="form-control" id="lead_detail_addressline1" name="lead_detail_addressline1"
                                placeholder="Addressline 1" value="{{ $data['lead']['addressline1'] }}" disabled>
                        </div>
                    </div>
                </div>

                <div class="row mb-1 d-none">
                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label"></label>
                    <div class="col-sm-8">
                        <input class="form-control" id="lead_detail_addressline2" name="lead_detail_addressline2"
                            placeholder="Addressline 2" value="{{ $data['lead']['addressline2'] }}" disabled>
                    </div>
                </div>

                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label"></label>
                    <div class="col-sm-8">
                        <input class="form-control" id="lead_detail_area" name="lead_detail_area" placeholder="Area"
                            value="{{ $data['lead']['area'] }}" disabled>
                    </div>
                </div>

                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label"></label>
                    <div class="col-sm-8">
                        <input type="number" class="form-control" id="lead_detail_pincode" name="lead_detail_pincode"
                            placeholder="Pincode" value="{{ $data['lead']['pincode'] }}" disabled>
                    </div>
                </div>

                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label"></label>
                    <div class="col-sm-8">
                        <input class="form-control" id="lead_detail_city_id" name="lead_detail_city_id"
                            placeholder="Area" value="{{ $data['lead']['city'] }}" disabled>
                    </div>
                </div>

                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Created by</label>
                    <div class="input-group" style="width: 67% !important;">
                        <input class="form-control" id="lead_detail_created_by" name="lead_detail_created_by"
                            placeholder="Created By" value="{{ $data['lead']['created_by'] }}" disabled
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
                                            href="javascript:void(0)">{{ $data['lead']['created_at'] }}</a></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Updated by</label>
                    <div class="input-group" style="width: 67% !important;">
                        <input class="form-control" id="lead_detail_updated_by" name="lead_detail_updated_by"
                            placeholder="Created By" value="{{ $data['lead']['updated_by'] }}" disabled
                            style="border-top-right-radius: 0px;border-bottom-right-radius: 0px;">
                        <button class="btn btn-light closing-badge4" type="button" id="password-addon"><i
                                class='bx bxs-calendar' style="font-size: 20px;"></i></button>
                        <div class="div_tip4 col-3 rounded" style="display: none; left: 78.5%; top: 37px;">
                            <div class="tip_arrow4"
                                style="border-bottom-color: rgb(191 194 252);border-top-color: transparent; margin: -20px 0px 0px; top: 0px; left: 36%;">
                            </div>
                            <div class="p-1">
                                <div class="tip_name4">
                                    <span class="name"><a class="text-dark"
                                            href="javascript:void(0)">{{ $data['lead']['updated_at'] }}</a></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Source</label>
                    <div class="col-sm-4">
                        <input class="form-control" id="lead_detail_email" name="lead_detail_email"
                            value="{{ $data['lead']['source_type'] }}" disabled>
                    </div>
                    <div class="col-sm-4">
                        @if(isset($data['lead']['source_type_id']) && ($data['lead']['source_type_id'] == 'textrequired-5' || $data['lead']['source_type_id'] == 'textrequired-1' || $data['lead']['source_type_id'] == 'textnotrequired-2' || $data['lead']['source_type_id'] == 'textnotrequired-6'))
                            <input class="form-control" id="" name="" value="{{ $data['lead']['source']['text'] }}" disabled>
                        @else 
                            <input class="form-control" id="" name="" value="{{ $data['lead']['source'] }}" disabled>
                        @endif
                    </div>

                </div>

                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Architect</label>
                    <div class="col-sm-8">
                        <input type="hidden" type="hidden" class="form-control" id="lead_detail_architect"
                            name="lead_detail_architect" value="{{ $data['lead']['architect'] }}" required disabled>
                        <div class="form-control" id="lead_detail_architect_div" style="background: #eff2f7;">
                            {{ $data['lead']['architect'] }}<i
                                class="bx bxs-phone"></i>{{ $data['lead']['architect_mobile'] }}
                        </div>
                    </div>
                </div>

                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Electrician</label>
                    <div class="col-sm-8">
                        <input type="hidden" class="form-control" id="lead_detail_electrician"
                            name="lead_detail_electrician" value="{{ $data['lead']['electrician'] }}" required
                            disabled>
                        <div class="form-control" id="lead_detail_electrician_div" style="background: #eff2f7;">
                            {{ $data['lead']['electrician'] }}<i
                                class="bx bxs-phone"></i>{{ $data['lead']['electrician_mobile'] }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">

                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">
                        @if ($data['closing_date_count'] >= 2)
                            <span class="closing-badge1"
                                style="float:left;margin-right: 5px;">{{ $data['closing_date_count'] }}</span>

                            <div class="div_tip1" style="display: none; left: 3px; top: 55px;">
                                <div class="tip_arrow1"
                                    style="transparent transparent rgb(191 194 252); margin: -20px 0px 0px; top: 0px; left: 3px;">
                                </div>
                                <div class="p-1">
                                    @foreach ($data['closing_date'] as $clsDate)
                                        <div class="tip_name">
                                            <span class="name"><a class="text-dark" href="javascript:void(0)">{{ date('d/m/Y', strtotime($clsDate['closing_date'])) }}</a></span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        Closing Date
                    </label>
                    <div class="col-sm-8">
                        <input class="form-control" id="lead_detail_email" name="lead_detail_email"
                            value="{{ $data['lead']['closing_date_time'] }}" disabled>
                    </div>

                </div>

                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Status</label>
                    <div class="col-sm-3">
                        <input class="form-control" id="lead_detail_email" name="lead_detail_email"
                            value="{{ $data['lead']['status_label'] }}" disabled>
                    </div>

                    <label for="horizontal-firstname-input" class="col-sm-2 col-form-label"> Suggest Step</label>
                    <div class="col-sm-3">
                        <input class="form-control" id="lead_detail_suggest_step" name="lead_detail_suggest_step"
                            placeholder="Suggest Step" value="{{ $data['lead']['suggest_step'] }}" readonly>
                    </div>
                </div>

                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Sub Status</label>
                    <div class="col-sm-8">
                        <input class="form-control" id="lead_detail_email" name="lead_detail_email"
                            value="{{ $data['lead']['sub_status'] }}" disabled>
                    </div>

                </div>

                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Site Stage</label>
                    <div class="col-sm-8">
                        <input class="form-control" id="lead_detail_email" name="lead_detail_email"
                            value="{{ $data['lead']['site_stage'] }}" disabled>
                    </div>

                </div>

                <div class="row mb-1 mt-1">
                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Home Details</label>
                    <div class="col-sm-3">
                        <input class="form-control" id="lead_detail_email" name="lead_detail_email"
                            value="{{ $data['lead']['site_type'] }}" disabled>
                    </div>

                    <div class="col-sm-3 pe-0">
                        <input type="number" class="form-control" id="lead_detail_sq_foot"
                            name="lead_detail_sq_foot" placeholder="SQ FT" value="{{ $data['lead']['sq_foot'] }}"
                            required disabled>
                    </div>
                    <div class="col-sm-2">
                        <input class="form-control" id="lead_detail_bhk" name="lead_detail_bhk"
                            value="{{ $data['lead']['bhk'] }}" disabled>
                    </div>
                </div>

                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Want to cover</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="lead_detail_want_to_cover"
                            name="lead_detail_want_to_cover" value="{{ $data['lead']['want_to_cover'] }}" required
                            disabled>
                    </div>
                </div>

                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Competitors</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="lead_detail_competitor"
                            name="lead_detail_competitor" value="{{ $data['lead']['competitor'] }}" required
                            disabled>
                    </div>
                </div>

                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Budget</label>
                    <div class="col-sm-8">
                        <input type="number" class="form-control" id="lead_detail_budget" name="lead_detail_budget"
                            placeholder="Budget" value="{{ $data['lead']['budget'] }}" required disabled>
                    </div>
                </div>

                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Tag</label>
                    <div class="col-sm-8">
                        <input class="form-control" id="lead_detail_email" name="lead_detail_email"
                            value="{{ $data['lead']['email'] }}" disabled>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
