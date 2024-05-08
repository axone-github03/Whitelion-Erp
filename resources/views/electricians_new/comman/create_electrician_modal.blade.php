<div class="modal fade" id="modalPointLog" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" role="dialog"
    aria-labelledby="modalPointLogLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPointLogLabel"> Point Log</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="min-height:100%;">


                <table id="pointLogTable" class="table align-middle table-nowrap mb-0 w-100">
                    <thead>
                        <tr>

                            <th>Log</th>




                        </tr>
                    </thead>


                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalInquiryLog" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1"
    role="dialog" aria-labelledby="modalInquiryLogLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalInquiryLogLabel"> Inquiry List</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="min-height:100%;">

                <div class="row text-center mb-3">
                    <div class="col-3">
                        <h5 class="mb-0" id="totalInquiry">0</h5>
                        <button class="btn btn-primary btn-sm" id="btnInquiryLogTotal">Total Inquiry</button>
                    </div>
                    <div class="col-3">
                        <h5 class="mb-0" id="totalRunningInquiry">0</h5>
                        <button class="btn btn-primary btn-sm" id="btnInquiryLogRunning">Running Inquiry</button>
                    </div>
                    <div class="col-3">
                        <h5 class="mb-0" id="totalWonInquiry">0</h5>
                        <button class="btn btn-primary btn-sm" id="btnInquiryLogWon">Won Inquiry</button>
                    </div>
                    <div class="col-3">
                        <h5 class="mb-0" id="totalRejectedInquiry">0</h5>
                        <button class="btn btn-primary btn-sm" id="btnInquiryLogLost">Lost Inquiry</button>
                    </div>
                </div>
                <div class="float-end">

                    <button type="button" class="btn-sm btn btn-outline-dark waves-effect waves-light float-end"
                        aria-haspopup="true" aria-expanded="false">Quotation Amount: <span
                            id="totalInquiryLogQuotationAmount"></span></button>
                </div>

                <table id="InquiryTable" class="table align-middle table-nowrap mb-0 w-100">
                    <thead>
                        <tr>

                            <th>#Id</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Quotation Amount</th>
                            <th>Architect</th>
                            <th>Channel Partner</th>

                        </tr>
                    </thead>


                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modalElectricianUser" data-bs-backdrop="static" tabindex="-1" role="dialog"
    aria-labelledby="modalUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalUserLabel"> User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="col-md-12 text-center loadingcls">
                <button type="button" class="btn btn-light waves-effect">
                    <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading...
                </button>
            </div>

            <form id="formElectricianUser" action="{{ route('new.electricians.save') }}" method="POST"
                class="needs-validation" novalidate>
                @csrf
                <input type="hidden" name="user_id" id="user_id">
                <div class="modal-body" style="min-height:100%;">
                    <div class="row">
                        <div style="display: none;" id="phone_no_error_dialog">
                            <div class="col-6 text-center d-flex justify-content-center m-auto"
                                style="height: 60px; line-height: 60px;">
                                <div class="phone_error danger">
                                    <svg xmlns="http://www.w3.org/2000/svg" version="1.1"
                                        xmlns:xlink="http://www.w3.org/1999/xlink"
                                        xmlns:svgjs="http://svgjs.com/svgjs" width="512" height="512" x="0"
                                        y="0" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512"
                                        xml:space="preserve" class="">
                                        <g>
                                            <g data-name="Layer 2">
                                                <circle cx="256" cy="256" r="256" fill="#ffffff"
                                                    data-original="#ff2147" opacity="1" class=""></circle>
                                                <g fill="#fff">
                                                    <path
                                                        d="M256 307.2a35.89 35.89 0 0 1-35.86-34.46l-4.73-119.44a35.89 35.89 0 0 1 35.86-37.3h9.46a35.89 35.89 0 0 1 35.86 37.3l-4.73 119.44A35.89 35.89 0 0 1 256 307.2z"
                                                        fill="#bd3630" data-original="#ffffff" class=""
                                                        opacity="1"></path>
                                                    <rect width="71.66" height="71.66" x="220.17" y="324.34"
                                                        rx="35.83" fill="#bd3630" data-original="#ffffff"
                                                        class="" opacity="1"></rect>
                                                </g>
                                            </g>
                                        </g>
                                    </svg>
                                    <span id="error_text">This Phone Number Is Alredy Register</span>
                                    <i class="bx bx-x-circle ms-2" id="close_phone_no_error_dialog"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row mb-3 mt-3">
                                <div class="form-check col-6 ps-3" id="flexRadioDefaultDiv1">
                                    <input class="form-check-input ms-0 me-2" type="radio" checked name="user_type"
                                        id="flexRadioDefault1" required value="302">
                                    <label class="form-check-label" for="flexRadioDefault1">Prime</label>
                                </div>
                                <div class="form-check col-6 ps-3" id="flexRadioDefaultDiv2">
                                    <input class="form-check-input ms-0 me-2" type="radio" name="user_type"
                                        id="flexRadioDefault2" required value="301">
                                    <label class="form-check-label" for="flexRadioDefault2">Non Prime</label>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="insert_phone_number" class="form-label col-sm-4">Phone number <code
                                        class="highlighter-rouge">*</code></label>
                                <div class="col-sm-8 input-group w-0" style="width: 66.66667% !important">
                                    <div class="input-group-text">+91</div>
                                    <input type="number" class="form-control" id="user_phone_number"
                                        name="user_phone_number" placeholder="Phone number" value="" required
                                        maxlength="10"
                                        oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);">
                                    <div class="col-12 text-danger" id="phone_no_validation" style="display: none;">
                                        This Phone Number Is Alredy Register</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="user_email" class="form-label col-sm-4">Email</label>
                                <div class="col-sm-8">
                                    <input type="email" class="form-control" id="user_email" name="user_email"
                                        placeholder="Email" value="">
                                    <div class="col-12 text-danger" id="email_id_validation" style="display: none;">
                                        This Email Is Alredy Register</div>
                                </div>
                            </div>

                            <div class="row mb-3 disable">
                                <label for="user_first_name" class="form-label col-sm-4">First name <code
                                        class="highlighter-rouge">*</code></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="user_first_name"
                                        name="user_first_name" placeholder="First Name" value="" required>
                                </div>
                            </div>
                            <div class="row mb-3 disable">
                                <label for="user_last_name" class="form-label col-sm-4">Last name <code
                                        class="highlighter-rouge">*</code></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="user_last_name"
                                        name="user_last_name" placeholder="Last Name" value="" required>
                                </div>
                            </div>

                            <div class="row mb-1 disable">
                                <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Address <code id="address_validation " class="highlighter-rouge">*</code></label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <div class="input-group-text" style="padding: 0;">
                                            <input type="text" style="width: 80px;" class="form-control" id="user_house_no" name="user_house_no" placeholder="H.No" value="" required>
                                        </div>
                                        <input class="form-control" id="user_address_line1" name="user_address_line1" placeholder="Building/Society Name" value="" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-1 disable  d-none">
                                <label for="user_address_line2" class="col-sm-4 col-form-label"></label>
                                <div class="col-sm-8">
                                    <input class="form-control" id="user_address_line2" name="user_address_line2" placeholder="Land Mark/ Road" value="" required>
                                </div>
                            </div>

                            <div class="row mb-1 disable">
                                <label for="horizontal-firstname-input" class="col-sm-4 col-form-label"></label>
                                <div class="col-sm-8">
                                    <input class="form-control" id="user_area" name="user_area" placeholder="Area" value="" required>
                                </div>
                            </div>

                            <div class="row mb-1 disable">
                                <label for="user_pincode" class="col-sm-4 col-form-label"></label>
                                <div class="col-sm-8">
                                    <input type="number" class="form-control" id="user_pincode" name="user_pincode" placeholder="Pincode" value="" required>
                                </div>
                            </div>

                            <div class="row mb-1 disable d-none">
                                <label for="user_address_line2" class="col-sm-4 col-form-label"></label>
                                <div class="col-sm-8">
                                    <input class="form-control" id="user_address_line2" name="user_address_line2" placeholder="Land Mark/ Road" value="" required>
                                </div>
                            </div>

                            <div class="row mb-1 disable change_color" id="user_city_id_div">
                                <label for="user_city_id" class="col-sm-4 col-form-label"></label>
                                <div class="col-sm-8">
                                    <select class="form-control select2-ajax" id="user_city_id" name="user_city_id" required></select>
                                    <div class="invalid-feedback">Please select city</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            {{-- <div class="row mb-1 disable">
                                <label for="user_country_id" class="col-sm-4 form-label">Country <code
                                        class="highlighter-rouge">*</code></label>
                                <div class="col-sm-8">
                                    <select class="form-select" id="user_country_id" name="user_country_id" required>
                                        <option selected value="1">India</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        Please select country.
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-1 disable">
                                <label class="form-label col-sm-4">State <code
                                        class="highlighter-rouge">*</code></label>
                                <div class="col-sm-8">
                                    <select class="form-control select2-ajax" id="user_state_id" name="user_state_id"
                                        required></select>
                                    <div class="invalid-feedback">Please select state.</div>
                                </div>
                            </div>

                            <div class="row mb-1 disable">
                                <label class="form-label col-sm-4">City <code
                                        class="highlighter-rouge">*</code></label>
                                <div class="col-sm-8">
                                    <select class="form-control select2-ajax" id="user_city_id" name="user_city_id"
                                        required></select>
                                    <div class="invalid-feedback">Please select city.</div>
                                </div>
                            </div> --}}

                            <div class="row mb-1 disable" id="electrician_status_div">
                                <label for="user_status" class="form-label col-sm-4">Status <code
                                        class="highlighter-rouge">*</code></label>
                                <div class="col-sm-8">
                                    <select class="form-select col-sm-8" id="user_status" name="user_status"
                                        required>
                                    </select>
                                    <div class="invalid-feedback">Please select status.</div>
                                </div>
                            </div>
                            <div class="row mb-1" id="duplicate_from_div">
                                <label for="duplicate_from" class="form-label col-sm-4">User<code
                                        class="highlighter-rouge">*</code></label>
                                <div class="col-sm-8">
                                    <select class="form-select col-sm-8" id="duplicate_from" name="duplicate_from"
                                        required>
                                    </select>
                                    <div class="invalid-feedback">Please select electrician.</div>
                                </div>
                            </div>

                            <div class="row mb-1 disable">
                                <label for="architect_firm_name" class="form-label col-sm-4">Sale Person*</label>
                                <div class="col-sm-8">
                                    <select class="form-control select2-ajax" id="electrician_sale_person_id"
                                        name="electrician_sale_person_id" required></select>
                                    <div class="invalid-feedback">Please select sale person.</div>
                                </div>
                            </div>
                            <div class="row mb-1" id="electrician_pan_card_div">
                                <label for="electrician_pan_card" class="col-sm-4 col-form-label">Pan Card
                                    <br>
                                    <span id="electrician_pan_card_file"></span></label>
                                <div class="col-sm-8 disable" id="electrician_pan_card_input_div">
                                    <input class="form-control" type="file" value=""
                                        id="electrician_pan_card" name="electrician_pan_card">
                                </div>
                            </div>
                            <div class="row mb-1" style="display: none;" id="electrician_note_div">
                                <label for="electrician_note" class="col-sm-4 col-form-label">Notes  <code class="highlighter-rouge" id="notes_required_sign">*</code></label>
                                <div class="col-sm-8">
                                    <input class="form-control" id="electrician_note" name="electrician_note" placeholder="Notes" value="">
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                @if ($data['viewMode'] == 0)
                    <div class="modal-footer">
                        <div id="btnSave">
                            <button type="button" class="btn btn-light"
                                data-bs-dismiss="modal">Close</button>
                            <button id="btnSaveFinal" class="btn btn-primary">Save</button>
                        </div>
                    </div>
                @endif
            </form>
        </div>
    </div>
</div>
