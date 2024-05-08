<style>
    .source_box {
        padding: 8px 0px;
        border-top: 1px solid gainsboro;
        border-bottom: 1px solid gainsboro;

    }
</style>
<div class="modal fade" id="modalLead" data-bs-backdrop="static" tabindex="-1" role="dialog"
    aria-labelledby="modalLeadLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLeadLabel"> Lead</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formLead" action="{{ route('crm.lead.save') }}" method="POST" class="needs-validation"
                novalidate>
                <div class="modal-body">
                    @csrf
                    <div class="col-md-12 text-center loadingcls">
                        <button type="button" class="btn btn-light waves-effect">
                            <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading
                        </button>
                    </div>
                    <input type="hidden" name="lead_id" id="lead_id" value="">
                    <input type="hidden" name="no_of_source" id="no_of_source" value="1">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card-body section_user_detail">
                                <div class="row">
                                    <div style="display: none;" id="phone_no_error_dialog">
                                        <div class="col-12 text-center d-flex justify-content-center mb-3"
                                            style="height: 60px; line-height: 60px;">
                                            <div class="phone_error danger">
                                                <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:svgjs="http://svgjs.com/svgjs" width="512" height="512" x="0" y="0" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><g data-name="Layer 2"><circle cx="256" cy="256" r="256" fill="#ffffff" data-original="#ff2147" opacity="1" class=""></circle><g fill="#fff"><path d="M256 307.2a35.89 35.89 0 0 1-35.86-34.46l-4.73-119.44a35.89 35.89 0 0 1 35.86-37.3h9.46a35.89 35.89 0 0 1 35.86 37.3l-4.73 119.44A35.89 35.89 0 0 1 256 307.2z" fill="#bd3630" data-original="#ffffff" class="" opacity="1"></path><rect width="71.66" height="71.66" x="220.17" y="324.34" rx="35.83" fill="#bd3630" data-original="#ffffff" class="" opacity="1"></rect></g></g></g></svg>
                                                <span id="error_text"></span>
                                                <i class="bx bx-x-circle ms-2" id="close_phone_no_error_dialog"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">

                                        <div class="row mb-1">
                                            <label for="horizontal-firstname-input"
                                                class="col-sm-3 col-form-label">Phone number <code
                                                    class="highlighter-rouge">*</code></label>
                                            <div class="col-sm-9">
                                                <div class="input-group">
                                                    <div class="input-group-text">+91</div>
                                                    <input type="number" class="form-control" id="lead_phone_number" name="lead_phone_number" placeholder="Phone number" value="" required maxlength="10" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);">
                                                    <div class="col-12 text-danger" id="phone_no_validation" style="display: none;">This Phone Number Is Alredy Register</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mb-1 disable">
                                            <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Lead
                                                Owner <code class="highlighter-rouge">*</code></label>
                                            <div class="col-sm-9">
                                                <select class="form-control select2-ajax" id="lead_assign_to"
                                                    name="lead_assign_to" required>
                                                </select>
                                                <div class="invalid-feedback">
                                                    Please select Lead Owner
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mb-1 disable">
                                            <label for="horizontal-firstname-input"
                                            class="col-sm-3 col-form-label">Client Name <code
                                            class="highlighter-rouge">*</code></label>
                                            <div class="row col-sm-9 pe-0">
                                                <div class="col-sm-6 pe-0">
                                                    <input class="form-control" id="lead_first_name" name="lead_first_name"
                                                        placeholder="First Name" value="" required>
                                                </div>
                                                <div class="col-sm-6 pe-0">
                                                    <input class="form-control" id="lead_last_name" name="lead_last_name"
                                                        placeholder="Last Name" value="" required>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- <div class="row mb-1">
                                            <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Last
                                                name <code class="highlighter-rouge">*</code></label>
                                            <div class="col-sm-9">
                                                <input class="form-control" id="lead_last_name" name="lead_last_name"
                                                    placeholder="Last Name" value="" required>
                                            </div>
                                        </div> --}}

                                        

                                        <div class="row mb-1 disable">
                                            <label for="horizontal-firstname-input"
                                                class="col-sm-3 col-form-label">Email</label>
                                            <div class="col-sm-9">
                                                <input class="form-control" id="lead_email" name="lead_email"
                                                    placeholder="Email" value="">
                                            </div>
                                        </div>

                                        <div class="row mb-1 disable">
                                            <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Site
                                                Address <code class="highlighter-rouge">*</code></label>
                                            <div class="col-sm-9">
                                                <div class="input-group">
                                                    <div class="input-group-text" style="padding: 0;">
                                                        <input type="text" style="width: 80px;" class="form-control"
                                                            id="lead_house_no" name="lead_house_no" placeholder="H.No"
                                                            value="" required>
                                                    </div>
                                                    <input class="form-control" id="lead_addressline1"
                                                        name="lead_addressline1" placeholder="Building/Society Name"
                                                        value="" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mb-1 d-none">
                                            <label for="horizontal-firstname-input"
                                                class="col-sm-3 col-form-label"></label>
                                            <div class="col-sm-9">
                                                <input class="form-control" id="lead_addressline2"
                                                    name="lead_addressline2" placeholder="Land Mark/ Road"
                                                    value="">
                                            </div>
                                        </div>

                                        <div class="row mb-1 disable">
                                            <label for="horizontal-firstname-input"
                                                class="col-sm-3 col-form-label"></label>
                                            <div class="col-sm-9">
                                                <input class="form-control" id="lead_area" name="lead_area"
                                                    placeholder="Area" value="" required>
                                            </div>
                                        </div>

                                        <div class="row mb-1 disable">
                                            <label for="horizontal-firstname-input"
                                                class="col-sm-3 col-form-label"></label>
                                            <div class="col-sm-9">
                                                <input type="number" class="form-control" id="lead_pincode"
                                                    name="lead_pincode" placeholder="Pincode" value="">
                                            </div>
                                        </div>

                                        <div class="row mb-1 disable">
                                            <label for="horizontal-firstname-input"
                                                class="col-sm-3 col-form-label"></label>
                                            <div class="col-sm-9">
                                                <select class="form-control select2-ajax" id="lead_city_id"
                                                    name="lead_city_id" required>

                                                </select>
                                                <div class="invalid-feedback">
                                                    Please select city
                                                </div>
                                            </div>
                                        </div>

                                        <div style="border-top: 1px solid gainsboro; padding-top: 8px;" class="pb-2" id>
                                            <div class="row mb-1 disable" id="source_type_div">
                                                <label for="lead_source_type" class="col-sm-3 col-form-label">Source Type <code class="highlighter-rouge">*</code></label>
                                                <div class="col-sm-9">
                                                    <select class="form-control select2-ajax" id="lead_source_type"
                                                        name="lead_source_type" required>
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        Please select source type.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mb-1 disable" id="source_div">
                                                <label for="lead_source" class="col-sm-3 col-form-label">Source <code
                                                        class="highlighter-rouge">*</code></label>
                                                <div class="col-sm-9">
                                                    <div id="div_lead_source">
                                                        <select class="form-control select2-ajax" id="lead_source"
                                                            name="lead_source" required>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            Please select Source
                                                        </div>
                                                    </div>
                                                    <input type="text" class="form-control" id="lead_source_text"
                                                        name="lead_source_text" placeholder="Please enter source"
                                                        value="">
                                                    <div class="invalid-feedback">
                                                        Please select source.
                                                    </div>
                                                </div>

                                            </div>

                                        </div>

                                        <div id="moreSourceDiv"></div>

                                        <div class="mb-2" style="padding: 8px 0px; border-bottom: 1px solid gainsboro;">

                                            <button type="button" id="addMoreSource"
                                                class="btn btn-sm btn-primary waves-effect waves-light "><i
                                                    class="bx bx-plus font-size-16 align-middle me-2"></i>Add More
                                                Source</button>
                                            <button type="button" id="removeMoreSource"
                                                class="btn btn-sm btn-danger waves-effect waves-light "
                                                style="display: none;"><i
                                                    class="bx bx-minus font-size-16 align-middle me-2"></i>Remove
                                                Source</button>
                                        </div>

                                        <div class="row mb-1 disable">
                                            @if(isAdminOrCompanyAdmin() == 1)
                                                <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Architect </label>
                                                <div class="col-sm-8">
                                                    <select class="form-control select2-ajax" id="lead_architect" name="lead_architect"></select>
                                                </div>
                                                <label for="horizontal-firstname-input" class="col-sm-1 col-form-label"><i class='bx bx-x' style='color:#ff0000' onclick="emptyArcField()"></i> </label>
                                            @else
                                                <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Architect </label>
                                                <div class="col-sm-9">
                                                    <select class="form-control select2-ajax" id="lead_architect" name="lead_architect"></select>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="row mb-1 disable">
                                            @if(isAdminOrCompanyAdmin() == 1)
                                                <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Electrician</label>
                                                <div class="col-sm-8">
                                                    <select class="form-control select2-ajax" id="lead_electrician" name="lead_electrician"></select>
                                                </div>
                                                <label for="horizontal-firstname-input" class="col-sm-1 col-form-label"><i class='bx bx-x' style='color:#ff0000' onclick="emptyEleField()"></i></label>
                                            @else
                                                <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Electrician</label>
                                                <div class="col-sm-9">
                                                    <select class="form-control select2-ajax" id="lead_electrician" name="lead_electrician"></select>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="row mb-1 d-none">
                                            <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">
                                                Meeting Address <code class="highlighter-rouge">*</code>
                                            </label>
                                            <div class="col-sm-9">
                                                <div class="input-group">
                                                    <div class="input-group-text" style="padding: 0;">
                                                        <input type="text" style="width: 80px;"
                                                            class="form-control" id="lead_meeting_house_no"
                                                            name="lead_meeting_house_no" placeholder="H.No"
                                                            value="" required>
                                                    </div>
                                                    <input class="form-control" id="lead_meeting_addressline1"
                                                        name="lead_meeting_addressline1"
                                                        placeholder="Building/Society Name" value="" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mb-1 d-none">
                                            <div class="col-sm-9">
                                                <input class="form-control" id="lead_meeting_addressline2"
                                                    name="lead_meeting_addressline2" placeholder="Land Mark/ Road"
                                                    value="">
                                            </div>
                                        </div>

                                        <div class="row mb-1 d-none">
                                            {{-- <label for="horizontal-firstname-input"
                                                class="col-sm-3 col-form-label"></label> --}}
                                            <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">
                                                <button class="btn btn-primary btn-sm" type="button"
                                                    id="leadSameAsAboveBtn">Same as above</button>
                                            </label>
                                            <div class="col-sm-9">
                                                <input class="form-control" id="lead_meeting_area"
                                                    name="lead_meeting_area" placeholder="Area" value=""
                                                    required>
                                            </div>
                                        </div>

                                        <div class="row mb-1 d-none">
                                            <label for="horizontal-firstname-input"
                                                class="col-sm-3 col-form-label"></label>
                                            <div class="col-sm-9">
                                                <input type="number" class="form-control" id="lead_meeting_pincode"
                                                    name="lead_meeting_pincode" placeholder="Pincode" value="">
                                            </div>
                                        </div>

                                        <div class="row mb-1 d-none">
                                            <label for="horizontal-firstname-input"
                                                class="col-sm-3 col-form-label"></label>
                                            <div class="col-sm-9">
                                                <select class="form-control select2-ajax" id="lead_meeting_city_id"
                                                    name="lead_meeting_city_id" required>

                                                </select>
                                                <div class="invalid-feedback">
                                                    Please select city
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="col-md-6">

                                        <div class="row mb-1 disable" id="div_lead_closing_date">
                                            <label for="horizontal-firstname-input"
                                                class="col-sm-3 col-form-label">Closing Date</label>
                                            <div class="col-sm-9">

                                                <div class="input-group" id="lead_closing_date_time">
                                                    <input autocomplete="off" type="text" class="form-control"
                                                        value="" placeholder="DD-MM-YYYY"
                                                        data-date-format="dd-mm-yyyy"
                                                        data-date-container='#lead_closing_date_time'
                                                        data-provide="datepicker" data-date-autoclose="true"
                                                        name="lead_closing_date_time">
                                                </div>

                                            </div>
                                        </div>

                                        <div class="row mb-1 disable">
                                            <label for="horizontal-firstname-input"
                                                class="col-sm-3 col-form-label">Site Stage <code
                                                    class="highlighter-rouge">*</code></label>
                                            <div class="col-sm-9">
                                                <select class="form-control select2-ajax" id="lead_site_stage"
                                                    name="lead_site_stage" required>

                                                </select>
                                                <div class="invalid-feedback">
                                                    Please select site stage
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mb-1 disable">
                                            <label for="horizontal-firstname-input"
                                                class="col-sm-3 col-form-label">Site Type <code
                                                    class="highlighter-rouge">*</code></label>
                                            <div class="col-sm-9">
                                                <select class="form-control select2-ajax" id="lead_site_type"
                                                    name="lead_site_type" required>

                                                </select>
                                                <div class="invalid-feedback">
                                                    Please select site type
                                                </div>
                                            </div>
                                        </div>
                                        

                                        <div class="row mb-1 disable">
                                            <label for="horizontal-firstname-input"
                                                class="col-sm-3 col-form-label">BHK <code
                                                    class="highlighter-rouge">*</code></label>
                                            <div class="col-sm-9">
                                                <select class="form-control select2-ajax" id="lead_bhk"
                                                    name="lead_bhk" required>

                                                </select>
                                                <div class="invalid-feedback">
                                                    Please select bhk
                                                </div>
                                            </div>
                                        </div>


                                        <div class="row mb-1 disable">
                                            <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">SQ
                                                FT</label>
                                            <div class="col-sm-9">
                                                <input type="number" class="form-control" id="lead_sq_foot"
                                                    name="lead_sq_foot" placeholder="SQ FT" value="">
                                            </div>
                                        </div>

                                        <div class="row mb-1 disable">
                                            <label for="horizontal-firstname-input"
                                                class="col-sm-3 col-form-label">Want to cover<code
                                                    class="highlighter-rouge">*</code></label>
                                            <div class="col-sm-9">
                                                <select class="form-control select2-ajax select2-multiple"
                                                    multiple="multiple" id="lead_want_to_cover"
                                                    name="lead_want_to_cover[]" required></select>
                                                <div class="invalid-feedback">
                                                    Please select want to cover
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mb-1 disable">
                                            <label for="horizontal-firstname-input"
                                                class="col-sm-3 col-form-label">Competitors</label>
                                            <div class="col-sm-9">
                                                <select class="form-control select2-ajax select2-multiple"
                                                    multiple="multiple" id="lead_competitor"
                                                    name="lead_competitor[]">

                                                </select>
                                                <div class="invalid-feedback">
                                                    Please select competitor
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mb-1 disable">
                                            <label for="horizontal-firstname-input"
                                                class="col-sm-3 col-form-label">Budget</label>
                                            <div class="col-sm-9">
                                                <input type="number" class="form-control" id="lead_budget"
                                                    name="lead_budget" placeholder="Budget" value="">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <!-- <button id="btnNext" type="button" class="btn btn-primary">Next</button> -->
                                <div>
                                    <button type="button" class="btn btn-light"
                                        data-bs-dismiss="modal">Close</button>
                                    <button id="btnSaveFinal" onclick="savelead();" type=""
                                        class="btn btn-primary save-btn">Save</button>
                                </div>
                            </div>
            </form>
        </div>
    </div>
</div>
