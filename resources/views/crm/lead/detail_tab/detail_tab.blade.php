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
        @if(isMarketingUser() == 0 )
            @if(!in_array($data['current_status'], array(103,104,105)) || isAdminOrCompanyAdmin() == 1 || isChannelPartner(Auth::user()->type) != 0 || isCreUser() == 1 )
                {{-- @if(isCreUser() == 0) --}}
                    <button onclick="editLead({{ $data['lead']['id'] }})" class="btn btn-sm btn-light btn-header-right waves-effect waves-light float-end ms-2 mt-1" type="button" style="margin-left:3px;"><i class="fas fa-pencil-alt font-size-16 align-middle "></i></button>
                {{-- @endif --}}
            @endif
        @endif
        <button type="button" class="btn btn-primary waves-effect waves-light float-end d-none" id="SaveEditField" onclick="saveDetailUpdate({{ $data['lead']['id'] }})">Save</button>
    </div>
</div>
<div class="card-body" id="lead_detail">
    <form style="margin-top: 20px;">
        <div class="row">
            <div class="col-md-6">
                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Lead
                        Owner</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="lead_detail_assigned_to"
                            name="lead_detail_assigned_to" value="{{ ucwords(strtolower($data['lead']['assigned'])) }}" required disabled>
                    </div>
                </div>

                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Client
                        Name</label>
                        <div class="row col-sm-9 pe-0">
                            <div class="col-sm-6 pe-0">
                                <input class="form-control" id="lead_detail_first_name" name="lead_detail_first_name"
                                    placeholder="First Name" value="{{ ucwords(strtolower($data['lead']['first_name'])) }}" disabled >
                            </div>
                            <div class="col-sm-6 pe-0">
                                <input class="form-control" id="lead_detail_last_name" name="lead_detail_last_name"
                                    placeholder="Last Name" value="{{ trim(ucwords(strtolower($data['lead']['last_name']))) }}" disabled>
                            </div>
                        </div>
                </div>

                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Mo.
                        number</label>
                    <div class="col-sm-9">
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
                    <div class="col-sm-9">
                        <input class="form-control" id="lead_detail_email" name="lead_detail_email" placeholder="Email"
                            value="{{ $data['lead']['email'] }}" disabled>
                    </div>
                </div>

                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Site
                        Address</label>
                    <div class="col-sm-9">
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
                    <div class="col-sm-9">
                        <input class="form-control" id="lead_detail_addressline2" name="lead_detail_addressline2"
                            placeholder="Addressline 2" value="{{ $data['lead']['addressline2'] }}" disabled>
                    </div>
                </div>

                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label"></label>
                    <div class="col-sm-9">
                        <input class="form-control" id="lead_detail_area" name="lead_detail_area" placeholder="Area"
                            value="{{ $data['lead']['area'] }}" disabled>

                    </div>
                </div>

                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label"></label>
                    <div class="col-sm-9">
                        <input type="number" class="form-control" id="lead_detail_pincode" name="lead_detail_pincode"
                            placeholder="Pincode" value="{{ $data['lead']['pincode'] }}" disabled>
                    </div>
                </div>

                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label"></label>
                    <div class="col-sm-9">
                        @if(isset($data['lead']['city']))
                            <input class="form-control" id="lead_detail_city_id" name="lead_detail_city_id" placeholder="Area" value="{{ $data['lead']['city'] }}" disabled>
                        @else
                            <input class="form-control" id="lead_detail_city_id" name="lead_detail_city_id" placeholder="Area" value="-" disabled>
                        @endif
                    </div>
                </div>

                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Created
                        by</label>
                    <div class="input-group" style="width: 75% !important;">
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
                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Updated
                        by</label>
                    <div class="input-group" style="width: 75% !important;">
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
                    <div class="col-sm-4 pe-0">
                        {{-- @if((!in_array($data['current_status'], array(103,104,105)) || isAdminOrCompanyAdmin() == 1) && (isCreUser() == 0)&& (isMarketingUser() == 0))
                            <select class="form-control select2-ajax" id="lead_detail_source_type" name="lead_detail_source_type" required> </select>
                        @else  --}}
                            <select class="form-control select2-ajax" id="lead_detail_source_type" name="lead_detail_source_type" required disabled> </select>
                        {{-- @endif --}}
                        <div class="invalid-feedback" id="lead_detail_source_type_error">Please select source type
                        </div>
                    </div>
                    <div class="col-sm-5">
                        <div id="div_lead_detail_source">
                            {{-- @if((!in_array($data['current_status'], array(103,104,105)) || isAdminOrCompanyAdmin() == 1) && (isCreUser() == 0)&& (isMarketingUser() == 0))
                                <select class="form-control select2-ajax" id="lead_detail_source" name="lead_detail_source" required onchange="saveDetailUpdate({{ $data['lead']['id'] }})"></select>
                            @else  --}}
                                <select class="form-control select2-ajax" id="lead_detail_source" name="lead_detail_source" required disabled></select>
                            {{-- @endif --}}
                            <div class="invalid-feedback" id="lead_detail_source_error">Please select source</div>
                        </div>
                        <input type="text" class="form-control" style="border: 1px solid #ced4da;border-radius: 4px;" id="lead_detail_source_text" name="lead_detail_source_text" placeholder="Please enter source" value="" style="display: none;" onkeyup="saveDetailUpdate({{ $data['lead']['id'] }})">
                    </div>
                </div>

                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Architect</label>
                    <div class="col-sm-9">
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
                    <div class="col-sm-9">
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
                @if ($data['lead']['is_deal'] == 1)
                    <div class="row mb-1">
                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">
                            @if (isArchitect() == 1)
                            @elseif (isElectrician() == 1)
                            @else
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
                            @endif
                            Closing Date
                        </label>

                        
                        <div class="col-sm-9">
                            <div class="input-group" id="lead_detail_closing_date_time" style="border: 1px solid #ced4da;border-radius: 4px;">
                                @if((!in_array($data['current_status'], array(103,104,105)) || isAdminOrCompanyAdmin() == 1) && (isCreUser() == 0))
                                    <input autocomplete="off" type="text" class="form-control" placeholder="DD-MM-YYYY"
                                        data-date-format="dd-mm-yyyy" data-date-container='#lead_detail_closing_date_time'
                                        data-provide="datepicker" data-date-autoclose="true" required
                                        name="lead_detail_closing_date_time" value="{{ $data['lead']['closing_date_time'] }}"
                                        id="detail_closing_date_time" onchange="saveDetailUpdate({{ $data['lead']['id'] }})">
                                @else
                                    <input autocomplete="off" type="text" class="form-control" placeholder="DD-MM-YYYY"
                                        data-date-format="dd-mm-yyyy" data-date-container='#lead_detail_closing_date_time'
                                        data-provide="datepicker" data-date-autoclose="true" required
                                        name="lead_detail_closing_date_time" value="{{ $data['lead']['closing_date_time'] }}"
                                        id="detail_closing_date_time" disabled>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">
                        Status</label>
                    <div class="col-sm-3">
                        @if((!in_array($data['current_status'], array(103,104,105)) || isAdminOrCompanyAdmin() == 1) && (isCreUser() == 0) && (isMarketingUser() == 0))
                            <select class="form-control lead_status_field bg-light"
                                id="lead_status_{{ $data['lead']['id'] }}"
                                onchange="saveDetailUpdate({{ $data['lead']['id'] }}, 0)">
                        
                        @else 
                            <select class="form-control lead_status_field bg-light" id="lead_status_{{ $data['lead']['id'] }}" disabled>
                        @endif
                            @foreach ($data['lead_status'] as $lead_status)
                                @if ($data['lead']['is_deal'] == 0)
                                    @if ($lead_status['type'] == 0)
                                        @if ($data['lead']['status'] == $lead_status['id'])
                                            <option value="{{ $lead_status['id'] }}" selected>
                                                {{ $lead_status['name'] }}
                                            </option>
                                        @else
                                            <option value="{{ $lead_status['id'] }}">{{ $lead_status['name'] }}
                                            </option>
                                        @endif
                                    @endif
                                @endif
                                @if ($data['lead']['is_deal'] == 1)
                                    @if ($lead_status['type'] == 1)
                                        @if ($data['lead']['status'] == $lead_status['id'])
                                            <option value="{{ $lead_status['id'] }}" selected>
                                                {{ $lead_status['name'] }}
                                            </option>
                                        @else
                                            <option value="{{ $lead_status['id'] }}">{{ $lead_status['name'] }}
                                            </option>
                                        @endif
                                    @endif
                                @endif
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="lead_status_error">Please select status</div>
                    </div>
                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">
                        Suggest Step</label>
                    <div class="col-sm-3">
                       
                        <input class="form-control" id="lead_detail_suggest_step" name="lead_detail_suggest_step" placeholder="Suggest Step" value="{{ $data['lead']['suggest_step'] }}" readonly>
                        
                    </div>
                </div>

                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Sub
                        Status</label>
                    <div class="col-sm-9">
                        @if((!in_array($data['current_status'], array(103,104,105)) || isAdminOrCompanyAdmin() == 1) && (isCreUser() == 0)&& (isMarketingUser() == 0))
                            <select class="form-control select2-ajax" id="lead_detail_sub_status" name="lead_detail_sub_status" onchange="saveDetailUpdate({{ $data['lead']['id'] }})" required></select>
                        @else 
                            <select class="form-control select2-ajax" id="lead_detail_sub_status" name="lead_detail_sub_status" disabled required></select>
                        @endif
                    </div>
                </div>

                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Site
                        Stage</label>
                    <div class="col-sm-9">
                        @if((!in_array($data['current_status'], array(103,104,105)) || isAdminOrCompanyAdmin() == 1) && (isCreUser() == 0)&& (isMarketingUser() == 0))
                            <select class="form-control select2-ajax" id="lead_detail_site_stage" name="lead_detail_site_stage" onchange="saveDetailUpdate({{ $data['lead']['id'] }})" required></select>
                        @else 
                            <select class="form-control select2-ajax" id="lead_detail_site_stage" name="lead_detail_site_stage" disabled required></select>
                        @endif
                        <div class="invalid-feedback" id="lead_detail_site_stage_error">Please select site stage</div>
                    </div>
                </div>

                <div class="row mb-1 mt-1">
                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Home
                        Details</label>
                    <div class="col-sm-3 pe-0">
                        {{-- @if((!in_array($data['current_status'], array(103,104,105)) || isAdminOrCompanyAdmin() == 1) && (isCreUser() == 0)&& (isMarketingUser() == 0))
                            <select class="form-control select2-ajax" id="lead_detail_site_type"
                            name="lead_detail_site_type" onchange="saveDetailUpdate({{ $data['lead']['id'] }})"
                            required> </select>
                        @else  --}}
                            <select class="form-control select2-ajax" id="lead_detail_site_type"
                            name="lead_detail_site_type" disabled
                            required> </select>
                        {{-- @endif --}}
                        <div class="invalid-feedback" id="lead_detail_site_type_error">Please select Home Detail</div>
                    </div>
                    <div class="col-sm-3 pe-0">
                        <input type="number" class="form-control" id="lead_detail_sq_foot"
                            name="lead_detail_sq_foot" placeholder="SQ FT" value="{{ $data['lead']['sq_foot'] }}"
                            required disabled>
                    </div>
                    <div class="col-sm-3">
                        <input class="form-control" id="lead_detail_bhk" name="lead_detail_bhk"
                            value="{{ $data['lead']['bhk'] }}" disabled>
                    </div>
                </div>

                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Want to
                        cover</label>
                    <div class="col-sm-9">

                        <input type="text" class="form-control" id="lead_detail_want_to_cover" name="lead_detail_want_to_cover" value="{{ $data['lead']['want_to_cover'] }}" required disabled>
                    </div>
                </div>

                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Competitors</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="lead_detail_competitor"
                            name="lead_detail_competitor" value="{{ $data['lead']['competitor'] }}" required
                            disabled>
                    </div>
                </div>

                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Budget</label>
                    <div class="col-sm-9">
                        <input type="number" class="form-control" id="lead_detail_budget" name="lead_detail_budget" placeholder="Budget" value="{{ $data['lead']['budget'] }}" required disabled>
                    </div>
                </div>
                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Material Sent By</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" placeholder="Material Sent By" value="{{ $data['lead']['material_sent_by'] }}" disabled>
                    </div>
                </div>

                <div class="row mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Tag</label>
                    <div class="col-sm-9">
                        @if((!in_array($data['current_status'], array(103,104,105)) || isAdminOrCompanyAdmin() == 1) && (isCreUser() == 0)&& (isMarketingUser() == 0))
                            <select class="form-control select2-ajax" id="lead_detail_tag" name="lead_detail_tag[]" onchange="saveDetailUpdate({{ $data['lead']['id'] }})" multiple></select>
                        @else 
                            <select class="form-control select2-ajax" id="lead_detail_tag" name="lead_detail_tag[]" disabled multiple></select>
                        @endif
                    </div>
                </div>

                {{-- MEEING ADDRESS --}}
                <div class="row mb-1 d-none">
                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">
                        Meeting Address
                    </label>
                    <div class="col-sm-9">
                        <div class="input-group">
                            <div class="input-group-text me-2" style="padding: 0;">
                                <input type="text" style="width: 80px;" class="form-control"
                                    id="lead_detail_meeting_house_no" name="lead_detail_meeting_house_no"
                                    placeholder="No" value="" disabled>
                            </div>
                            <input class="form-control" id="lead_detail_meeting_addressline1"
                                name="lead_detail_meeting_addressline1" placeholder="Addressline 1" value=""
                                disabled>
                        </div>
                    </div>
                </div>
                <div class="row mb-1 d-none">
                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label"></label>
                    <div class="col-sm-9">
                        <input class="form-control" id="lead_detail_meeting_addressline2"
                            name="lead_detail_meeting_addressline2" placeholder="Addressline 2" value=""
                            disabled>
                    </div>
                </div>
                <div class="row mb-1 d-none">
                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label"></label>
                    <div class="col-sm-9">
                        <input class="form-control" id="lead_detail_meeting_area" name="lead_detail_meeting_area"
                            placeholder="Area" value="" disabled>
                    </div>
                </div>
                <div class="row mb-1 d-none">
                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label"></label>
                    <div class="col-sm-9">
                        <input type="number" class="form-control" id="lead_detail_meeting_pincode"
                            name="lead_detail_meeting_pincode" placeholder="Pincode" value="" disabled>
                    </div>
                </div>
                <div class="row mb-1 d-none">
                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label"></label>
                    <div class="col-sm-9">
                        <input class="form-control" id="lead_detail_meeting_city_id"
                            name="lead_detail_meeting_city_id" placeholder="Area" value="" disabled>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
