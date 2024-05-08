<div class="modal fade" id="modalPointLog" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" role="dialog" aria-labelledby="modalPointLogLabel" aria-hidden="true">
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


<div class="modal fade" id="modalInquiryLog" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" role="dialog" aria-labelledby="modalInquiryLogLabel" aria-hidden="true">
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

                    <button type="button" class="btn-sm btn btn-outline-dark waves-effect waves-light float-end" aria-haspopup="true" aria-expanded="false">Quotation Amount: <span id="totalInquiryLogQuotationAmount"></span></button>
                </div>
                <table id="InquiryTable" class="table align-middle table-nowrap mb-0 w-100">
                    <thead>
                        <tr>

                            <th>#Id</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Quotation Amount</th>
                            <th>Electrician</th>
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






<div class="modal fade" id="modalUser" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" role="dialog" aria-labelledby="modalUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalUserLabel"> User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formUser" action="{{route('architect.save')}}" method="POST" class="needs-validation" novalidate>
                <div class="modal-body" style="min-height:100%;">

                    @csrf


                    <div class="col-md-12 text-center loadingcls">






                        <button type="button" class="btn btn-light waves-effect">
                            <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading
                        </button>


                    </div>

                    <input type="hidden" name="user_id" id="user_id">




                    <div class="row">





                        <!-- <div class="col-md-3">
                            <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                                <a class="nav-link mb-2 active" id="v-pills-user-detail-tab" data-bs-toggle="pill" href="#v-pills-user-detail" role="tab" aria-controls="v-pills-user-detail" aria-selected="true">1. User Detail</a>


                                <a class="nav-link mb-2" id="v-pills-user-extra-detail-tab" data-bs-toggle="pill" href="#v-pills-architect-detail" role="tab" aria-controls="v-pills-architect-detail" aria-selected="false">2. Architect Detail</a>

                            </div>
                        </div> -->

                        <div class="col-md-12">
                            <div class="tab-content text-muted mt-4 mt-md-0" id="v-pills-tabContent">
                                <!-- <div class="tab-pane fade show active" id="v-pills-user-detail" role="tabpanel" aria-labelledby="v-pills-user-detail-tab"> -->

                                <div class="card-body section_user_detail">

                                    <div class="row" id="div_user_type">
                                        <div class="col-md-12">

                                            <div class="mb-3">
                                                <label for="user_status" class="form-label">User Type <code class="highlighter-rouge">*</code></label>
                                                <select class="form-select" id="user_type" name="user_type" required>

                                                    @php
                                                    $accessTypes=getArchitects();
                                                    @endphp
                                                    @if(count($accessTypes)>0)
                                                    @foreach($accessTypes as $key=>$value)
                                                    <option value="{{$value['id']}}">{{$value['name']}} </option>
                                                    @endforeach
                                                    @endif

                                                </select>
                                                <div class="invalid-feedback">
                                                    Please select user type.
                                                </div>

                                            </div>

                                        </div>

                                    </div>






                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="user_first_name" class="form-label">First name <code class="highlighter-rouge">*</code></label>
                                                <input type="text" class="form-control" id="user_first_name" name="user_first_name" placeholder="First Name" value="" required>


                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="user_last_name" class="form-label">Last name <code class="highlighter-rouge">*</code></label>
                                                <input type="text" class="form-control" id="user_last_name" name="user_last_name" placeholder="Last Name" value="" required>


                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6" id="row_email">
                                            <div class="mb-3">
                                                <label for="user_email" class="form-label">Email <code class="highlighter-rouge">*</code></label>
                                                <input type="email" class="form-control" id="user_email" name="user_email" placeholder="Email" value="" required>


                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="insert_phone_number" class="form-label">Phone number <code class="highlighter-rouge">*</code></label>

                                                <div class="input-group">
                                                    <div class="input-group-text">
                                                        +91


                                                    </div>
                                                    <input type="number" class="form-control" id="user_phone_number" name="user_phone_number" placeholder="Phone number" value="" required>
                                                </div>





                                            </div>
                                        </div>
                                    </div>





                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="user_country_id" class="form-label">Country <code class="highlighter-rouge">*</code></label>
                                                <select class="form-select" id="user_country_id" name="user_country_id" required>
                                                    <option selected value="1">India</option>

                                                </select>
                                                <div class="invalid-feedback">
                                                    Please select country.
                                                </div>

                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                <label class="form-label">State <code class="highlighter-rouge">*</code></label>
                                                <select class="form-control select2-ajax" id="user_state_id" name="user_state_id" required>

                                                </select>
                                                <div class="invalid-feedback">
                                                    Please select state.
                                                </div>

                                            </div>



                                        </div>

                                        <div class="col-md-4">
                                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                <label class="form-label">City <code class="highlighter-rouge">*</code></label>
                                                <select class="form-control select2-ajax" id="user_city_id" name="user_city_id" required>

                                                </select>
                                                <div class="invalid-feedback">
                                                    Please select city.
                                                </div>

                                            </div>



                                        </div>


                                    </div>

                                    <div class="row">

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="user_pincode" class="form-label">Pincode <code class="highlighter-rouge" id="user_pincode_mandatory">*</code></label>
                                                <input type="text" class="form-control" id="user_pincode" name="user_pincode" placeholder="Pincode" value="" required>


                                            </div>
                                        </div>


                                    </div>


                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="user_address_line1" class="form-label">Address line 1 <code class="highlighter-rouge" id="user_address_line1_mandatory">*</code></label>
                                                <input type="text" class="form-control" id="user_address_line1" name="user_address_line1" placeholder="Address line 1" value="" required>


                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="user_address_line2" class="form-label">Address line 2</label>
                                                <input type="text" class="form-control" id="user_address_line2" name="user_address_line2" placeholder="Address line 2" value="">


                                            </div>
                                        </div>
                                    </div>




                                    <div class="row">
                                        <div class="col-md-12" id="row_user_status">

                                            <div class="mb-3">
                                                <label for="user_status" class="form-label">Status <code class="highlighter-rouge">*</code></label>
                                                <select class="form-select" id="user_status" name="user_status" required>
                                                    <option selected value="1">Active</option>
                                                    <option value="0">Inactive</option>
                                                    <option value="2">Pending</option>

                                                </select>
                                                <div class="invalid-feedback">
                                                    Please select status.
                                                </div>

                                            </div>

                                        </div>

                                    </div>







                                </div>

                                <!-- </div> -->
                                <!-- <div class="tab-pane fade " id="v-pills-architect-detail" role="tabpanel" aria-labelledby="v-pills-user-extra-detail-tab"> -->


                                <div class="card-body section_user_extra_detail">






                                    <h3 class="card-title div-start-line mb-3">1. Architect Details</h3>

                                    <div class="row ">


                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="architect_firm_name" class="form-label">Firm Name <code class="highlighter-rouge">*</code></label>
                                                <input type="text" class="form-control" id="architect_firm_name" name="architect_firm_name" placeholder="Firm Name" value="" required>


                                            </div>
                                        </div>


                                        <div class="col-md-6 " id="section_sale_person">
                                            <div class="mb-3">
                                                <label for="architect_firm_name" class="form-label">Sale Person <code class="highlighter-rouge">*</code></label>



                                                <select class="form-control select2-ajax" id="architect_sale_person_id" name="architect_sale_person_id" required>

                                                </select>
                                                <div class="invalid-feedback">
                                                    Please select sale person.
                                                </div>

                                            </div>





                                        </div>
                                    </div>

                                    <div class="row ">


                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="architect_birth_date" class="form-label">Birth Date</label>

                                                <div class="input-group" id="div_architect_birth_date">
                                                    <input type="text" class="form-control" value="" placeholder="yyyy-mm-dd" data-date-format="yyyy-mm-dd" data-date-container='#div_architect_birth_date' data-provide="datepicker" data-date-autoclose="true" name="architect_birth_date" id="architect_birth_date">


                                                </div>




                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="architect_anniversary_date" class="form-label">Anniversary Date</label>

                                                <div class="input-group" id="div_architect_anniversary_date">
                                                    <input type="text" class="form-control" value="" placeholder="yyyy-mm-dd" data-date-format="yyyy-mm-dd" data-date-container='#div_architect_anniversary_date' data-provide="datepicker" data-date-autoclose="true" name="architect_anniversary_date" id="architect_anniversary_date">


                                                </div>




                                            </div>
                                        </div>



                                    </div>

                                    @php
                                    $isChannelPartner = isChannelPartner(Auth::user()->type);
                                    @endphp

                                    @if($isChannelPartner==0)



                                    <div class="row" id="div_source">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="architect_source_type" class="form-label">Source Type <code class="highlighter-rouge">*</code></label>
                                                @php
                                                    $is_required = "";
                                                    if(isMarketingUser() == 0)
                                                    {
                                                        $is_required = "required";
                                                    }
                                                @endphp
                                                <select class="form-control select2-ajax" id="architect_source_type" name="architect_source_type" {{$is_required}}>
                                                    @if(count($data['source_types'])>0)
                                                    @foreach($data['source_types'] as $key=>$value)
                                                    <option value="{{$value['type']}}-{{$value['id']}}">{{$value['lable']}}</option>
                                                    @endforeach
                                                    @endif
                                                </select>
                                                <div class="invalid-feedback">
                                                    Please select source type.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6" id="div_source_user">
                                            <div class="mb-3">
                                                <label for="architect_source_user" class="form-label">Source <code class="highlighter-rouge">*</code></label>
                                                <select class="form-control select2-ajax" id="architect_source_user" name="architect_source_user">
                                                </select>
                                                <div class="invalid-feedback">
                                                    Please select source.
                                                </div>
                                            </div>

                                        </div>

                                        <div class="col-md-6" id="div_source_text">
                                            <div class="mb-3">
                                                <label for="architect_source_text" class="form-label">Source <code class="highlighter-rouge" id="architect_source_text_required">*</code></label>
                                                <input type="text" class="form-control" id="architect_source_text" name="architect_source_text" placeholder="Source" value="">

                                                <div class="invalid-feedback">
                                                    Please enter source.
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    @endif

                                    <div class="row " id="row_principal_architect">




                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="architect_principal_architect_name" class="form-label">Principal Architect Name</label>
                                                <input type="text" class="form-control" id="architect_principal_architect_name" name="architect_principal_architect_name" placeholder="Principal Architect Name" value="">


                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="instagram_link" class="form-label">Instagram Link</label>
                                                <input type="text" class="form-control" id="architect_instagram_link" name="architect_instagram_link" placeholder="Instagram Link" value="">


                                            </div>
                                        </div>






                                    </div>

                                    @php

                                    $isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
                                    $isMarketingDispatcherUser = isMarketingDispatcherUser();
                                    $isTaleSalesUser = isTaleSalesUser();


                                    @endphp

                                    @if($isAdminOrCompanyAdmin==1 || $isMarketingDispatcherUser==1 || $isTaleSalesUser==1)

                                    <div class="row " id="row_verified">

                                        <div class="col-md-6">
                                            <div class="form-check ">
                                                <input class="form-check-input" type="checkbox" name="architect_data_verified" id="architect_data_verified" value="1">
                                                <label class="form-check-label" for="architect_data_verified">
                                                    Is Data Verified?
                                                </label>
                                            </div>

                                            <div class="form-check ">
                                                <input class="form-check-input" type="checkbox" name="architect_data_not_verified" id="architect_data_not_verified" value="1">
                                                <label class="form-check-label" for="architect_data_not_verified">
                                                    Is Data Not Verified?
                                                </label>
                                            </div>

                                            <div class="form-check ">
                                                <input class="form-check-input" type="checkbox" name="architect_missing_data" id="architect_missing_data" value="1">
                                                <label class="form-check-label" for="architect_missing_data">
                                                    Is Missing Data?
                                                </label>
                                            </div>
                                        </div>


                                        <div class="col-md-6">
                                            <div class="form-check ">
                                                <input class="form-check-input" type="checkbox" name="architect_tele_verified" id="architect_tele_verified" value="1">
                                                <label class="form-check-label" for="architect_tele_verified">
                                                    Is Tele Verified?
                                                </label>
                                            </div>
                                            <div class="form-check ">
                                                <input class="form-check-input" type="checkbox" name="architect_tele_not_verified" id="architect_tele_not_verified" value="1">
                                                <label class="form-check-label" for="architect_tele_not_verified">
                                                    Is Tele Not Verified?
                                                </label>
                                            </div>
                                        </div>


                                        <!-- <div class="col-md-12">
                                                    <div class="form-check ">
                                                        <input class="form-check-input" type="checkbox"  name="architect_tele_verified" id="architect_tele_verified" value="1" >
                                                        <label class="form-check-label" for="architect_tele_verified">
                                                           Is Tele Verified
                                                        </label>
                                                    </div>
                                                </div> -->


                                    </div>
                                    @endif




                                    <!-- <h3 class="card-title div-start-line mb-3 for-prime-architect">2. Work Profile</h3>
                                    <div class="row mb-3 for-prime-architect">


                                        <h4 class="font-size-14"><i class="mdi mdi-arrow-right text-primary me-1"></i> Project Type <code class="highlighter-rouge">*</code><span id="project_type_error" class="alert-danger"></span>
                                        </h4>

                                        <div class="row ">
                                            <div class="col-md-12">
                                                <div class="form-check ">
                                                    <input class="form-check-input" type="checkbox" name="architect_is_residential" id="architect_is_residential" value="1">
                                                    <label class="form-check-label" for="architect_is_residential">
                                                        Residential
                                                    </label>
                                                </div>

                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="architect_is_commercial_or_office_space" id="architect_is_commercial_or_office_space" value="1">
                                                    <label class="form-check-label" for="architect_is_commercial_or_office_space">
                                                        Commercial / Office Space
                                                    </label>
                                                </div>
                                            </div>
                                        </div>


                                    </div>

                                    <div class="row mb-3 for-prime-architect">


                                        <h4 class="font-size-14"><i class="mdi mdi-arrow-right text-primary"></i> Design Type <code class="highlighter-rouge">*</code><span id="design_type_error" class="alert-danger"></span>
                                        </h4>
                                        <div class="row ">
                                            <div class="col-md-12">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="architect_interior" id="architect_interior" value="1">
                                                    <label class="form-check-label" for="architect_interior">
                                                        Interior
                                                    </label>
                                                </div>

                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="architect_exterior" id="architect_exterior" value="1">
                                                    <label class="form-check-label" for="architect_exterior">
                                                        Exterior
                                                    </label>
                                                </div>


                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="architect_structural_design" id="architect_structural_design" value="1">
                                                    <label class="form-check-label" for="architect_structural_design">
                                                        Structural Design
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-3 for-prime-architect">


                                        <h4 class="font-size-14"><i class="mdi mdi-arrow-right text-primary"></i> How long have you been practicing ? <code class="highlighter-rouge">*</code><span id="practicing_error" class="alert-danger"></span></h4>
                                        <div class="row ">
                                            <div class="col-md-12">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="architect_practicing" id="architect_practicing_1" value="1">
                                                    <label class="form-check-label" for="architect_practicing_1">
                                                        1 - 3 years
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="architect_practicing" id="architect_practicing_2" value="2">
                                                    <label class="form-check-label" for="architect_practicing_2">
                                                        3 - 5 years
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="architect_practicing" id="architect_practicing_3" value="3">
                                                    <label class="form-check-label" for="architect_practicing_3">
                                                        5 - 10 years
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="architect_practicing" id="architect_practicing_4" value="4">
                                                    <label class="form-check-label" for="architect_practicing_4">
                                                        10 - 15 years
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="architect_practicing" id="architect_practicing_5" value="5">
                                                    <label class="form-check-label" for="architect_practicing_5">
                                                        More than 15 years
                                                    </label>
                                                </div>
                                            </div>
                                        </div>





                                    </div> -->


                                    <h4 class="card-title div-start-line mb-3 for-prime-architect"> 2. Legal Verification</h4>

                                    <div class="row for-prime-architect">
                                        <div class="col-md-6">
                                            <label for="architect_visiting_card" class="col-form-label">Visiting Card <code class="highlighter-rouge"></code><span id="architect_visiting_card_file"></span> </label>

                                            <input class="form-control" type="file" value="" id="architect_visiting_card" name="architect_visiting_card">

                                        </div>


                                        <div class="col-md-6">
                                            <label for="architect_aadhar_card" class="col-form-label">Aadhar Card <span id="architect_aadhar_card_file"></span></label>

                                            <input class="form-control" type="file" value="" id="architect_aadhar_card" name="architect_aadhar_card" required>

                                        </div>
                                    </div>
                                    <br>


                                    <!-- <h4 class="card-title div-start-line mb-3 for-prime-architect"> 4. Quick Feedback from Architect</h4>

                                    <div class="row for-prime-architect">
                                        <label for="architect_brand_using_for_switch" class="col-md-4 col-form-label">Which brand are you using for switches ? <code class="highlighter-rouge">*</code> </label>
                                        <div class="col-md-8">
                                            <input class="form-control" type="text" value="" id="architect_brand_using_for_switch" name="architect_brand_using_for_switch" required>
                                        </div>
                                    </div>

                                    <div class="row for-prime-architect">
                                        <label for="architect_brand_used_before_home_automation" class="col-md-4 col-form-label">Which brand have you used before for Home Automation ? <code class="highlighter-rouge">*</code> </label>
                                        <div class="col-md-8">
                                            <input class="form-control" type="text" value="" id="architect_brand_used_before_home_automation" name="architect_brand_used_before_home_automation" required>
                                        </div>
                                    </div>

                                    <div class="row for-prime-architect">
                                        <div class="col-md-12">

                                            <h5 class="font-size-14">Have you used Whitelion Smart Switches before ? <code class="highlighter-rouge">*</code><span id="architect_whitelion_smart_switches_before_error" class="alert-danger"></span> </h5>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="architect_whitelion_smart_switches_before" id="architect_whitelion_smart_switches_before_1" value="1">
                                                <label class="form-check-label" for="architect_whitelion_smart_switches_before_1">
                                                    Yes
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="architect_whitelion_smart_switches_before" id="architect_whitelion_smart_switches_before_0" value="0" checked>
                                                <label class="form-check-label" for="architect_whitelion_smart_switches_before_0">
                                                    No
                                                </label>
                                            </div>
                                        </div>






                                    </div>

                                    <div class="row architect_whitelion_smart_switches_before_yes ">
                                        <label for="example-text-input" class="col-md-4 col-form-label">In how many projects have you used Whitelion Smart Switches ? </label>
                                        <div class="col-md-8">
                                            <input class="form-control" type="text" value="" id="architect_how_many_projects_used_whitelion_smart_switches" name="architect_how_many_projects_used_whitelion_smart_switches">
                                        </div>
                                    </div>

                                    <div class="row architect_whitelion_smart_switches_before_yes ">
                                        <div class="mt-4">
                                            <h5 class="font-size-14">How was your experience with Whitelion ? </h5>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="architect_experience_with_whitelion" id="architect_experience_with_whitelion_5" value="5" checked>
                                                <label class="form-check-label" for="architect_experience_with_whitelion_5">
                                                    Excellent
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="architect_experience_with_whitelion" id="architect_experience_with_whitelion_4" value="4">
                                                <label class="form-check-label" for="architect_experience_with_whitelion_4">
                                                    Good
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="architect_experience_with_whitelion" id="architect_experience_with_whitelion_3" value="3">
                                                <label class="form-check-label" for="architect_experience_with_whitelion_3">
                                                    Average
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="architect_experience_with_whitelion" id="architect_experience_with_whitelion_2" value="2">
                                                <label class="form-check-label" for="architect_experience_with_whitelion_2">
                                                    Poor
                                                </label>
                                            </div>

                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="architect_experience_with_whitelion" id="architect_experience_with_whitelion_1" value="1">
                                                <label class="form-check-label" for="architect_experience_with_whitelion_1">
                                                    Very Poor
                                                </label>
                                            </div>


                                        </div>
                                    </div>


                                    <div class="row architect_whitelion_smart_switches_before_yes ">
                                        <label for="architect_suggestion" class="col-md-4 col-form-label">Would you like to give any architect suggestion specifically related to Whitelion Products ? </label>
                                        <div class="col-md-8">
                                            <input class="form-control" type="text" value="" id="architect_suggestion" name="architect_suggestion" required>
                                        </div>
                                    </div> -->

                                </div>
















                                <!-- </div> -->

                            </div>

                        </div>
                    </div>





                    @if($data['viewMode']==0)
                    <div class="modal-footer" id="divFooter">

                        <!-- <button id="btnNext" type="button" class="btn btn-primary">Next</button> -->

                        <div id="btnSave">


                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button id="btnSaveFinal" type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </div>
                    @endif


            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    var ajaxURLSearchState = "{{route('search.state.from.country')}}";
    var ajaxURLSearchCity = "{{route('search.city.from.state')}}";
    var ajaxURLSearchSalePerson = "{{route('architects.search.sale.person')}}";
    var ajaxURLUserDetail = "{{route('architect.detail')}}";
    var listElectriciansNonPrime = "{{route('electricians.prime')}}";
    var listElectriciansPrime = "{{route('electricians.prime')}}";
    var listChannelPartnerAD = "{{route('channel.partners.ad')}}";
    var ajaxURLSearchUser = "{{route('architect.search.user')}}";
    var ajaxPointLog = "{{route('architect.point.log')}}";
    var ajaxInquiryLog = "{{route('architect.inquiry.log')}}";
    var csrfToken = $("[name=_token").val();

    $("#user_country_id").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#modalUser .modal-body")

    });


    $("#user_state_id").select2({
        ajax: {
            url: ajaxURLSearchState,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    "country_id": function() {
                        return $("#user_country_id").val()
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
        placeholder: 'Search for a state',
        dropdownParent: $("#modalUser .modal-body")
    });


    $("#user_city_id").select2({
        ajax: {
            url: ajaxURLSearchCity,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    "state_id": function() {
                        return $("#user_state_id").val()
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
        placeholder: 'Search for a city',
        dropdownParent: $("#modalUser .modal-body")
    });


    $("#user_status").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#modalUser .modal-body")
    });

    $("#user_type").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#modalUser .modal-body")

    });




    $("#architect_sale_person_id").select2({
        ajax: {
            url: ajaxURLSearchSalePerson,
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
        placeholder: 'Search for a sale person',
        dropdownParent: $("#modalUser .modal-body")
    });




    $(document).ready(function() {


        if (isSalePerson == 1) {

            $("#section_sale_person").hide();
            $("#architect_sale_person_id").removeAttr('required');
            $("#row_user_status").hide();
            $("#user_status").removeAttr('required');

        }




        var options = {
            beforeSubmit: showRequest, // pre-submit callback
            success: showResponse // post-submit callback

            // other available options:
            //url:       url         // override for form's 'action' attribute
            //type:      type        // 'get' or 'post', override for form's 'method' attribute
            //dataType:  null        // 'xml', 'script', or 'json' (expected server response type)
            //clearForm: true        // clear all form fields after successful submit
            //resetForm: true        // reset the form after successful submit

            // $.ajax options can be used here too, for example:
            //timeout:   3000
        };

        // bind form using 'ajaxForm'
        $('#formUser').ajaxForm(options);
    });

    function showRequest(formData, jqForm, options) {





        // formData is an array; here we use $.param to convert it to a string to display it
        // but the form plugin does this for you automatically when it submits the data
        var queryString = $.param(formData);

        // jqForm is a jQuery object encapsulating the form element.  To access the
        // DOM element for the form do this:
        // var formElement = jqForm[0];

        // alert('About to submit: \n\n' + queryString);

        // here we could return false to prevent the form from being submitted;
        // returning anything other than false will allow the form submit to continue
        var formElement = jqForm[0];
        if (formElement.id == "formUser") {


            // if ($(".was-validated .form-control:invalid").length > 0) {
            //      return false;
            //  }



            var userType = $("#user_type").val();
            if (userType == "202") {



                // var isValidateForm = 1;

                // var isResidential = $("#architect_is_residential").prop('checked');
                // var isCommercialOfficeSpace = $("#architect_is_commercial_or_office_space").prop('checked');
                // if (isResidential === false && isCommercialOfficeSpace === false) {
                //     $("#project_type_error").html("Choose your option(s).");
                //     isValidateForm = 0;
                // } else {
                //     $("#project_type_error").html("");

                // }

                // var interior = $("#architect_interior").prop('checked');
                // var exterior = $("#architect_exterior").prop('checked');
                // var structuralDesign = $("#architect_structural_design").prop('checked');


                // if (interior == false && exterior === false && structuralDesign == false) {

                //     $("#design_type_error").html("Choose your option(s).");
                //     isValidateForm = 0;
                // } else {
                //     $("#design_type_error").html('');
                // }

                // var practicing = $("[name=architect_practicing]:checked").val();





                // if (typeof practicing === "undefined") {
                //     isValidateForm = false;
                //     $("#practicing_error").html("Choose your option.");
                // } else {
                //     $("#practicing_error").html("");
                // }

                // var whitelion_smart_switches_before = $("[name=architect_whitelion_smart_switches_before]:checked").val();
                // if (typeof whitelion_smart_switches_before === "undefined") {
                //     isValidateForm = false;
                //     $("#whitelion_smart_switches_before_error").html("Choose your option.");
                // } else {
                //     $("#whitelion_smart_switches_before_error").html("");
                // }

                // if (isValidateForm == 0) {
                //     //  return false;
                // }




            }

        }

        $("#btnSaveFinal").html("Saving...");

        $("#btnSaveFinal").prop('disabled', true);
        return true;

    }

    // post-submit callback
    function showResponse(resultData, statusText, xhr, $form) {
        $("#btnSaveFinal").prop('disabled', false);
        $("#btnSaveFinal").html("Save");


        if (resultData['status'] == 1) {
            toastr["success"](resultData['msg']);
            reloadTable();
            resetInputForm();
            $("#modalUser").modal('hide');


        } else if (resultData['status'] == 0) {


            if (typeof resultData['data'] !== "undefined") {

                var size = Object.keys(resultData['data']).length;
                if (size > 0) {

                    for (var [key, value] of Object.entries(resultData['data'])) {

                        toastr["error"](value);
                    }

                }

            } else {
                toastr["error"](resultData['msg']);
            }


        }

        // for normal html responses, the first argument to the success callback
        // is the XMLHttpRequest object's responseText property

        // if the ajaxForm method was passed an Options Object with the dataType
        // property set to 'xml' then the first argument to the success callback
        // is the XMLHttpRequest object's responseXML property

        // if the ajaxForm method was passed an Options Object with the dataType
        // property set to 'json' then the first argument to the success callback
        // is the json data object returned by the server

        // alert('status: ' + statusText + '\n\nresponseText: \n' + responseText +
        //     '\n\nThe output div should have already been updated with the responseText.');
    }


    $('#architect_birth_date').click(function() {

        $('#user_country_id').select2('open');
        $('#user_country_id').select2('close');


    });

    $('#architect_anniversary_date').click(function() {

        $('#user_country_id').select2('open');
        $('#user_country_id').select2('close');


    });


    // $('.nav a').click(function() {
    //     if ($(this).attr('id') == "v-pills-user-extra-detail-tab") {


    //         // $("#btnNext").hide();
    //         // $("#btnSave").show();


    //         /// MODAL SCROLL ISSUE
    //         $('#architect_source_type').select2('open');
    //         $('#architect_source_type').select2('close');
    //         $('#user_country_id').select2('open');
    //         $('#user_country_id').select2('close');
    //         // MODAL SCROLL ISSUE


    //     } else {




    //         // $("#btnNext").show();
    //         // $("#btnSave").hide();
    //         /// MODAL SCROLL ISSUE
    //         $('#architect_source_type').select2('open');
    //         $('#architect_source_type').select2('close');
    //         $('#user_country_id').select2('open');
    //         $('#user_country_id').select2('close');
    //         /// MODAL SCROLL ISSUE

    //     }
    // });

    // $("#btnNext").click(function() {

    //     $("#v-pills-user-detail-tab").removeClass('active');
    //     $("#v-pills-user-extra-detail-tab").addClass('active');
    //     $("#v-pills-user-detail").removeClass('active');
    //     $("#v-pills-user-detail").removeClass('show');
    //     $("#v-pills-architect-detail").addClass('active');
    //     $("#v-pills-architect-detail").addClass('show');
    //     $("#btnNext").hide();
    //     $("#btnSave").show();
    //     /// MODAL SCROLL ISSUE
    //     $('#architect_source_type').select2('open');
    //     $('#architect_source_type').select2('close');
    //     $('#user_country_id').select2('open');
    //     $('#user_country_id').select2('close');
    //     /// MODAL SCROLL ISSUE
    // });




    $("#addBtnUser").click(function() {



        resetInputForm();
        $("#modalUserLabel").html("Add Architect");
        $("#user_id").val(0);
        $(".loadingcls").hide();
        $("#formUser .row").show();
        $("#div_source_text").hide();
        $("#div_source_user").hide();
        $("#architect_source_type").trigger('change');
        // console.log($("#user_type").val());
        setTimeout(function() {
            $("#user_type").val($("#user_type").val());
            $('#user_type').trigger('change');
            changeUserType($("#user_type").val());

            if (isSalePerson == 1) {

                $("#formUser input").prop('disabled', false);
                $('#formUser select').select2("enable");
                $("#divFooter").show();

            }



        }, 100);



    });

    function resetInputForm() {

        $("#formUser").removeClass('was-validated');
        $('#formUser').trigger("reset");
        // $("#btnNext").show();
        // $("#btnSave").hide();

        // $('#v-pills-tab.nav a:first').tab('show');
        // $("#btnNext").show();
        // $("#btnSave ").hide();



        $('.nav a:first').tab('show');
        $("#user_status").select2("val", "1");
        $("#user_country_id").select2("val", "1");
        $("#user_state_id").empty().trigger('change');
        $("#user_city_id").empty().trigger('change');
        $("#architect_sale_person_id").empty().trigger('change');
        $("#architect_visiting_card_file").html("");
        $("#architect_aadhar_card_file").html("");

        if (viewMode == 1 || isSalePerson == 1) {
            $("#formUser input:not([type=hidden]").prop('disabled', true);
            $('#formUser select').select2("enable", false);
            $("#divFooter").hide();


        }



    }



    var editModeLoading = 0;

    function editView(id) {

        editModeLoading = 1;
        resetInputForm();

        $("#modalUser").modal('show');
        $("#modalUserLabel").html("Edit Architect #" + id);
        $("#formUser .row").hide();
        $(".loadingcls").show();
        // $("#btnNext").show();
        // $("#btnSave").hide();

        $.ajax({
            type: 'GET',
            url: ajaxURLUserDetail + "?id=" + id,
            success: function(resultData) {
                if (resultData['status'] == 1) {











                    $("#user_id").val(resultData['data']['id']);
                    $("#user_first_name").val(resultData['data']['first_name']);
                    $("#user_last_name").val(resultData['data']['last_name']);
                    $("#user_phone_number").val(resultData['data']['phone_number']);
                    $("#user_email").val(resultData['data']['email']);
                    $("#user_ctc").val(resultData['data']['ctc']);
                    $("#user_pincode").val(resultData['data']['pincode']);
                    $("#user_address_line1").val(resultData['data']['address_line1']);
                    $("#user_address_line2").val(resultData['data']['address_line2']);
                    $("#user_type").val(resultData['data']['type']);
                    $("#user_status").val(resultData['data']['status']);
                    $("#architect_source_type").val("" + resultData['data']['architect']['source_type']);
                    $("#architect_source_type").trigger('change');
                    var pieces_architect_source_type = resultData['data']['architect']['source_type'].split("-");
                    if (pieces_architect_source_type[0] == "user") {

                        setTimeout(function() {

                            $("#architect_source_user").empty().trigger('change');

                            var newOption = new Option(resultData['data']['architect']['source']['text'], resultData['data']['architect']['source']['id'], false, false);
                            $('#architect_source_user').append(newOption).trigger('change');

                        }, 200);



                    } else if (pieces_architect_source_type[0] == "textrequired" || pieces_architect_source_type[0] == "textnotrequired") {

                        setTimeout(function() {
                            $("#architect_source_text").val(resultData['data']['architect']['source_type_value']);

                        }, 200);





                    }



                    if (resultData['data']['type'] == 201) {

                        $("#user_email").val('');

                    }

                    if(resultData['data']['login_user_type'] == 6)
                    {
                        $('.row input').attr('readonly', true);
                        $('.row input[type="file"]').addClass('pe-none');
                        $('.row .select2-container').addClass('pe-none');
                        $('#architect_principal_architect_name').attr('readonly', false);
                        $('#architect_instagram_link').attr('readonly', false);
                        $('.row .select2-container .select2-selection--single').css('background-color', '#eff2f7');
                        $('#datatable_filter input').attr('readonly', false);
                    }




                    if (typeof resultData['data']['country']['id'] !== "undefined") {

                        $("#user_country_id").val("" + resultData['data']['country']['id'] + "");
                        $('#user_country_id').trigger('change');

                    }


                    if (typeof resultData['data']['state']['id'] !== "undefined") {

                        var newOption = new Option(resultData['data']['state']['name'], resultData['data']['state']['id'], false, false);
                        $('#user_state_id').append(newOption).trigger('change');
                        $("#user_state_id").val("" + resultData['data']['state']['id'] + "");
                        $('#user_state_id').trigger('change');


                    }

                    if (typeof resultData['data']['city']['id'] !== "undefined") {


                        var newOption = new Option(resultData['data']['city']['name'], resultData['data']['city']['id'], false, false);
                        $('#user_city_id').append(newOption).trigger('change');
                        $("#user_city_id").val("" + resultData['data']['city']['id'] + "");
                        $('#user_city_id').trigger('change');


                    }






                    $(".loadingcls").hide();
                    $("#formUser .row").show();
                    $('#user_type').trigger('change');
                    $('#user_status').trigger('change');


                    $("#architect_firm_name").val(resultData['data']['architect']['firm_name']);


                    if (typeof resultData['data']['architect']['sale_person'] !== "undefined") {




                        var newOption = new Option(resultData['data']['architect']['sale_person']['text'], resultData['data']['architect']['sale_person']['id'], false, false);
                        $('#architect_sale_person_id').append(newOption).trigger('change');
                        $("#architect_sale_person_id").val("" + resultData['data']['architect']['sale_person']['id'] + "");
                        $('#architect_sale_person_id').trigger('change');


                    }


                    if (typeof resultData['data']['architect']['birth_date'] !== "undefined" && resultData['data']['architect']['birth_date'] != null) {

                        $('#architect_birth_date').datepicker('setDate', resultData['data']['architect']['birth_date']);


                    }


                    if (typeof resultData['data']['architect']['anniversary_date'] !== "undefined" && resultData['data']['architect']['anniversary_date'] != null) {

                        $("#architect_anniversary_date").val(resultData['data']['architect']['anniversary_date']);

                    }

                    if (resultData['data']['type'] == 202) {

                        if (resultData['data']['architect']['is_residential'] == 1) {
                            $("#architect_is_residential").prop('checked', true);
                        } else {
                            $("#architect_is_residential").prop('checked', false);
                        }

                        if (resultData['data']['architect']['is_commercial_or_office_space'] == 1) {
                            $("#architect_is_commercial_or_office_space").prop('checked', true);
                        } else {
                            $("#architect_is_commercial_or_office_space").prop('checked', false);
                        }

                        if (resultData['data']['architect']['interior'] == 1) {
                            $("#architect_interior").prop('checked', true);
                        } else {
                            $("#architect_interior").prop('checked', false);
                        }

                        if (resultData['data']['architect']['exterior'] == 1) {
                            $("#architect_exterior").prop('checked', true);
                        } else {
                            $("#architect_exterior").prop('checked', false);
                        }

                        if (resultData['data']['architect']['structural_design'] == 1) {
                            $("#architect_structural_design").prop('checked', true);
                        } else {
                            $("#architect_structural_design").prop('checked', false);
                        }

                        if (resultData['data']['architect']['practicing'] != 0) {

                            $("#architect_practicing_" + resultData['data']['architect']['practicing']).prop('checked', true);
                        } else {
                            $("#architect_practicing_1").prop('checked', false);
                            $("#architect_practicing_2").prop('checked', false);
                            $("#architect_practicing_3").prop('checked', false);
                            $("#architect_practicing_4").prop('checked', false);
                            $("#architect_practicing_5").prop('checked', false);
                        }

                        if (resultData['data']['architect']['visiting_card'] != "") {

                            $("#architect_visiting_card_file").html(resultData['data']['architect']['visiting_card']);

                        }

                        if (resultData['data']['architect']['aadhar_card'] != "") {

                            $("#architect_aadhar_card_file").html(resultData['data']['architect']['aadhar_card']);

                        }

                        // $("#architect_brand_using_for_switch").val(resultData['data']['architect']['brand_using_for_switch']);
                        // $("#architect_brand_used_before_home_automation").val(resultData['data']['architect']['brand_used_before_home_automation']);
                        // if (resultData['data']['architect']['whitelion_smart_switches_before'] == 1) {

                        //     $("#architect_whitelion_smart_switches_before_1").prop('checked', true);

                        // } else {
                        //     $("#architect_whitelion_smart_switches_before_0").prop('checked', true);

                        // }
                        //setViewAsPerWhitelionSmartSwitchesBefore(resultData['data']['architect']['whitelion_smart_switches_before']);

                        // $("#architect_how_many_projects_used_whitelion_smart_switches").val(resultData['data']['architect']['how_many_projects_used_whitelion_smart_switches']);

                        // $("#architect_experience_with_whitelion_" + resultData['data']['architect']['experience_with_whitelion']).prop('checked', true);


                        // $("#architect_suggestion").val(resultData['data']['architect']['suggestion']);
                        $("#architect_principal_architect_name").val(resultData['data']['architect']['principal_architect_name']);
                        $("#architect_instagram_link").val(resultData['data']['architect']['instagram_link']);

                        if (resultData['data']['architect']['data_verified'] == 1) {

                            $("#architect_data_verified").prop('checked', true);

                        } else {
                            $("#architect_data_verified").prop('checked', false);

                        }

                        if (resultData['data']['architect']['data_not_verified'] == 1) {

                            $("#architect_data_not_verified").prop('checked', true);

                        } else {
                            $("#architect_data_not_verified").prop('checked', false);

                        }

                        if (resultData['data']['architect']['missing_data'] == 1) {

                            $("#architect_missing_data").prop('checked', true);

                        } else {

                            $("#architect_missing_data").prop('checked', false);

                        }

                        if (resultData['data']['architect']['tele_verified'] == 1) {

                            $("#architect_tele_verified").prop('checked', true);

                        } else {

                            $("#architect_tele_verified").prop('checked', false);

                        }

                        if (resultData['data']['architect']['tele_not_verified'] == 1) {

                            $("#architect_tele_not_verified").prop('checked', true);

                        } else {

                            $("#architect_tele_not_verified").prop('checked', false);

                        }














                    }




                    if (isSalePerson == 1) {



                        if (resultData['data']['type'] == 201) {
                            $("#user_type").select2("enable");



                        }

                    }

                    editModeLoading = 0;




                } else {

                    toastr["error"](resultData['msg']);

                }

            }
        });

    }

    $('#user_country_id').on('change', function() {

        if (editModeLoading == 0) {

            $("#user_state_id").empty().trigger('change');
            $("#user_city_id").empty().trigger('change');
        }

    });

    $('#user_state_id').on('change', function() {

        if (editModeLoading == 0) {
            $("#user_city_id").empty().trigger('change');
        }

    });




    $('#user_type').on('change', function() {

        $('#user_country_id').select2('open');
        $('#user_country_id').select2('close');


        changeUserType($(this).val());
        //setViewAsPerWhitelionSmartSwitchesBefore(0);

    });




    function changeUserType(userType) {

        /// MODAL SCROLL ISSUE
        // $('#architect_source_type').select2('open');
        // $('#architect_source_type').select2('close');
        // MODAL SCROLL ISSUE


        userType = userType + "";


        if (userType == "201") {

            $("#row_email").hide();
            $("#user_email").removeAttr('required');
            $(".for-prime-architect").hide();
            // $("#architect_visiting_card").removeAttr('required');
            $("#architect_aadhar_card").removeAttr('required');
            // $("#architect_brand_using_for_switch").removeAttr('required');
            // $("#architect_brand_used_before_home_automation").removeAttr('required');
            // $("#architect_suggestion").removeAttr('required');
            $("#user_address_line1").removeAttr('required');
            $("#user_pincode").removeAttr('required');
            $("#user_pincode_mandatory").html('');
            $("#user_address_line1_mandatory").html('');
            $("#row_principal_architect").hide();
            $("#row_verified").hide();







        } else if (userType == "202") {

            $("#row_email").show();
            $("#user_email").prop('required', true);
            $("#user_pincode_mandatory").html('*');
            $("#user_address_line1_mandatory").html('*')


            $("#user_address_line1").prop('required', true);
            $("#user_pincode").prop('required', true);


            $(".for-prime-architect").show();
            if (editModeLoading == 0) {
                //$("#architect_visiting_card").prop('required', true);
                //$("#architect_aadhar_card").prop('required', true);
                $("#architect_aadhar_card").removeAttr('required');
            } else {
                // $("#architect_visiting_card").removeAttr('required');
                $("#architect_aadhar_card").removeAttr('required');

            }
            // $("#architect_brand_using_for_switch").prop('required', true);
            // $("#architect_brand_used_before_home_automation").prop('required', true);
            // $("#architect_suggestion").removeAttr('required');

            $("#row_principal_architect").show();
            $("#row_verified").show();

        }

        // $('#user_country_id').select2('open');
        // $('#user_country_id').select2('close');
        if (userType == "202") {
            if (isSalePerson == 1) {

                if ($("#user_pincode").val() == "") {
                    $("#user_pincode").removeAttr('disabled');

                }
                if ($("#user_email").val() == "") {
                    $("#user_email").removeAttr('disabled');

                }
                if ($("#user_address_line1").val() == "") {
                    $("#user_address_line1").removeAttr('disabled');
                }
                $("#divFooter").show();

            }
        } else {
            if (isSalePerson == 1) {
                $("#divFooter").hide();
                $("#user_pincode").attr('disabled', true);
                $("#user_email").attr('disabled', true);
                $("#user_address_line1").attr('disabled', true);
            }
        }




    }

    // $('[name=architect_whitelion_smart_switches_before]').on('change', function() {
    //     setViewAsPerWhitelionSmartSwitchesBefore(this.value);
    // });




    // function setViewAsPerWhitelionSmartSwitchesBefore(whitelion_smart_switches_before) {


    //     if (whitelion_smart_switches_before == 1) {
    //         $(".architect_whitelion_smart_switches_before_yes").show();

    //     } else {
    //         $(".architect_whitelion_smart_switches_before_yes").hide();



    //     }

    // }

    $("#architect_source_type").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#modalUser .modal-body")
    });





    $('#architect_tele_verified').on('change', function() {

        var isChecked = $(this).is(':checked');

        if (isChecked) {

            $("#architect_tele_not_verified").prop("checked", false);
            //$("#architect_missing_data").prop("checked",false);

        }

    });

    $('#architect_tele_not_verified').on('change', function() {

        var isChecked = $(this).is(':checked');
        if (isChecked) {

            $("#architect_tele_verified").prop("checked", false);
            // $("#architect_missing_data").prop("checked",false);


        }

    });

    $('#architect_data_verified').on('change', function() {

        var isChecked = $(this).is(':checked');

        if (isChecked) {

            $("#architect_data_not_verified").prop("checked", false);
            //$("#architect_missing_data").prop("checked",false);

        }

    });

    $('#architect_data_not_verified').on('change', function() {

        var isChecked = $(this).is(':checked');
        if (isChecked) {

            $("#architect_data_verified").prop("checked", false);
            // $("#architect_missing_data").prop("checked",false);


        }

    });

    $('#architect_missing_data').on('change', function() {

        var isChecked = $(this).is(':checked');
        if (isChecked) {

            // $("#architect_data_not_verified").prop("checked",false);
            //  $("#architect_data_verified").prop("checked",false);


        }

    });

    $('#architect_source_type').on('change', function() {
        $("#architect_source_user").empty().trigger('change');
        $("#architect_source_text").val('');
        if ($("#architect_source_type").val() != null) {

            var pieces_architect_source_type = $("#architect_source_type").val().split("-");
            $("#architect_source_text").removeAttr('required');
            $("#architect_source_user").removeAttr('required');
            $("#architect_source_text_required").html("");

            if (pieces_architect_source_type[0] == "user") {
                $("#div_source_text").hide();
                $("#div_source_user").show();
                $("#architect_source_user").prop('required', true);


                $("#sourceAddBtn").remove();

                if (pieces_architect_source_type[1] == 301) {

                    $("#div_source_user").append('<a target="_blank" href="' + listElectriciansPrime + '?add=1" id="sourceAddBtn"  class="btn btn-primary">Add</a>')


                } else if (pieces_architect_source_type[1] == 302) {

                    $("#div_source_user").append('<a target="_blank" href="' + listElectriciansNonPrime + '?add=1" id="sourceAddBtn"  class="btn btn-primary">Add</a>')


                } else if (pieces_architect_source_type[1] == 104) {

                    $("#div_source_user").append('<a target="_blank" href="' + listChannelPartnerAD + '?add=1" id="sourceAddBtn"  class="btn btn-primary">Add</a>')



                } else {
                    $("#sourceAddBtn").remove();

                }



            } else if (pieces_architect_source_type[0] == "textrequired" || pieces_architect_source_type[0] == "textnotrequired") {

                $("#div_source_text").show();
                $("#div_source_user").hide();
                if (pieces_architect_source_type[0] == "textrequired") {
                    $("#architect_source_text").prop('required', true);
                    $("#architect_source_text_required").html("*");
                } else {
                    $("#architect_source_text").removeAttr('required');
                    $("#architect_source_text_required").html("");

                }


            } else {
                $("#div_source_text").hide();
                $("#div_source_user").hide();


            }
        }
        @if(isMarketingUser() == 1)
            $("#architect_source_text").removeAttr('required');
            $("#architect_source_user").removeAttr('required');
        @endif

    });


    $("#architect_source_user").select2({
        ajax: {
            url: ajaxURLSearchUser,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    "source_type": function() {
                        var pieces_architect_source_type = $("#architect_source_type").val().split("-");
                        return pieces_architect_source_type[1]
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
        placeholder: 'Search for a user',
        dropdownParent: $("#modalUser .modal-content")
    });

    var csrfToken = $("[name=_token").val();
    var architectLogUserId = 0;


    var pointLogTable = $('#pointLogTable').DataTable({
        "aoColumnDefs": [{
            "bSortable": false,
            "aTargets": [0]
        }],
        "order": [
            [0, 'desc']
        ],
        "processing": true,
        "serverSide": true,
        "pageLength": 10,
        "ajax": {
            "url": ajaxPointLog,
            "type": "POST",
            "data": {
                "_token": csrfToken,
                "user_id": function() {
                    return architectLogUserId
                },
            }
        },
        "aoColumns": [{
            "mData": "log"
        }]
    });

    function pointLogs(userId) {
        architectLogUserId = userId;
        $("#modalPointLog").modal('show');

        pointLogTable.ajax.reload(null, false);

    }

    var inquiryLogType = 0;
    var inquiryLogTable = $('#InquiryTable').DataTable({
        "aoColumnDefs": [{
            "bSortable": false,
            "aTargets": []
        }],
        "order": [
            [0, 'desc']
        ],
        "processing": true,
        "serverSide": true,
        "pageLength": 10,
        "ajax": {
            "url": ajaxInquiryLog,
            "type": "POST",
            "data": {
                "_token": csrfToken,
                "user_id": function() {
                    return architectLogUserId
                },
                "type": function() {
                    return inquiryLogType
                },
            }
        },
        "aoColumns": [{
                "mData": "id"
            },
            {
                "mData": "name"
            },
            {
                "mData": "status"
            },
            {
                "mData": "quotation_amount"
            },
            {
                "mData": "column4"
            },
            {
                "mData": "column5"
            },
        ]
    });

    function inquiryLogs(userId) {
        architectLogUserId = userId;
        $("#modalInquiryLog").modal('show');
        inquiryLogType = 0;
        inquiryLogTable.ajax.reload();

    }




    inquiryLogTable.on('xhr', function() {

        var responseData = inquiryLogTable.ajax.json();
        $("#totalInquiry").html(responseData['overview']['total_inquiry']);
        $("#totalRunningInquiry").html(responseData['overview']['total_running']);
        $("#totalWonInquiry").html(responseData['overview']['total_won']);
        $("#totalRejectedInquiry").html(responseData['overview']['total_rejected']);
        $("#totalInquiryLogQuotationAmount").html(responseData['quotationAmount']);
        $("#modalInquiryLogLabel").html(responseData['title']);

        $(".inquiry-log-active").removeClass("inquiry-log-active");

        if (responseData['type'] == "0") {
            $("#btnInquiryLogTotal").addClass('inquiry-log-active');
        } else if (responseData['type'] == "1") {
            $("#btnInquiryLogRunning").addClass('inquiry-log-active');
        } else if (responseData['type'] == "2") {
            $("#btnInquiryLogWon").addClass('inquiry-log-active');
        } else if (responseData['type'] == "3") {
            $("#btnInquiryLogLost").addClass('inquiry-log-active');
        }






    });

    $("#btnInquiryLogTotal").click(function() {

        inquiryLogType = 0;
        inquiryLogTable.ajax.reload();

    });

    $("#btnInquiryLogRunning").click(function() {
        inquiryLogType = 1;
        inquiryLogTable.ajax.reload();

    });

    $("#btnInquiryLogWon").click(function() {
        inquiryLogType = 2;
        inquiryLogTable.ajax.reload();

    });

    $("#btnInquiryLogLost").click(function() {
        inquiryLogType = 3;
        inquiryLogTable.ajax.reload();

    });
</script>