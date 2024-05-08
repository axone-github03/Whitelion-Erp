<div class="modal fade" id="modalChannelPartenerTypeDiscount" data-bs-backdrop="static" data-bs-keyboard="true"
    tabindex="-1" role="dialog" aria-labelledby="modalChannelPartenerTypeDiscountLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalChannelPartenerTypeDiscountLabel"> Edit Discount Channel Partener Type
                    Wise</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-2">
                    <div class="float-start col-md-3">
                        <select class="form-select" id="cpt_discount_channel_partners"
                            name="cpt_discount_channel_partners" required>

                            @php
                                $accessTypes = getChannelPartnersAccess(Auth::user()->type);
                            @endphp
                            @if (count($accessTypes) > 0)
                                @foreach ($accessTypes as $key => $value)
                                    <option value="{{ $value['id'] }}">
                                        {{ $value['name'] }} </option>
                                @endforeach
                            @endif

                        </select>
                        <div class="invalid-feedback">
                            Please select user type.
                        </div>
                    </div>
                    <div class="float-start col-md-3">
                        <select class="form-select " id="cpt_discount_product_group_id"
                            name="cpt_discount_product_group_id">
                            <option value="0">All</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <input type='number' min='0' max='100' step='1'
                            class="form-control w-25 float-start valid-discount w-100" id="cpt_discount_all_discount"
                            name="cpt_discount_all_discount" placeholder="Discount" value="" />
                    </div>

                    <div class="col-md-2">
                        <button id="saveAllCptDiscount" type="button"
                            class="btn btn-primary waves-effect waves-light float-start">Save For All Products</button>
                    </div>

                    <div class="col-md-2">
                        <button type="button" class="btn btn-dark waves-effect waves-light float-end"
                            id="cptdiscountSync">

                        </button>
                    </div>
                </div>



                <table id="cpt_datatableDiscount" class="table align-middle w-100 ">
                    <thead>
                        <tr>
                            <th>Product Brand</th>
                            <th>Product Code</th>
                            <th>Product Description</th>
                            <th>New Discount(%)</th>
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

<div class="modal fade" id="modalDiscount" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1"
    role="dialog" aria-labelledby="modalDiscountLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDiscountLabel"> Discount</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="discount_user_id" id="discount_user_id" value="0">
                <p class="">

                <div class="float-start w-25">
                    <select class="form-select " id="discount_product_group_id" name="discount_product_group_id">
                        <option value="0">All</option>
                    </select>
                </div>
                <span class="float-start"> &nbsp;
                    &nbsp;</span>
                &nbsp; &nbsp;

                <input type='number' min='0' max='100' step='1'
                    class="form-control w-25 float-start valid-discount" id="discount_all_discount"
                    name="discount_all_discount" placeholder="Discount" value="" />

                <button id="saveAllDiscount" type="button"
                    class="btn btn-primary waves-effect waves-light float-start">Save For All Products</button>

                <button type="button" class="btn btn-dark waves-effect waves-light float-end" id="discountSync">

                </button>

                </p>


                <br>


                <table id="datatableDiscount" class="table align-middle table-nowrap mb-0 w-100">
                    <thead>
                        <tr>
                            <th>Product Brand</th>
                            <th>Product Code</th>
                            <th>Product Description</th>
                            <th>Discount(%)</th>
                            <th>New Discount(%)</th>
                        </tr>
                    </thead>


                    <tbody>

                    </tbody>
                </table>



            </div>
        </div>
    </div>
</div>



<div class="modal fade" id="modalUser" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1"
    role="dialog" aria-labelledby="modalUserLabel" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalUserLabel"> User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>




            <form id="formUser" action="{{ route('channel.partners.save') }}" method="POST"
                class="needs-validation" novalidate>
                <div class="modal-body">

                    @csrf


                    <div class="col-md-12 text-center loadingcls">






                        <button type="button" class="btn btn-light waves-effect">
                            <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading
                        </button>


                    </div>

                    <input type="hidden" name="user_id" id="user_id">


                    <div class="row">




                        <div class="col-md-3">
                            <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist"
                                aria-orientation="vertical">
                                <a class="nav-link mb-2 active" id="v-pills-user-detail-tab" data-bs-toggle="pill"
                                    href="#v-pills-user-detail" role="tab" aria-controls="v-pills-user-detail"
                                    aria-selected="true">1. User Detail</a>


                                <a class="nav-link mb-2" id="v-pills-channel-partner-detail-tab"
                                    data-bs-toggle="pill" href="#v-pills-channel-partner-detail" role="tab"
                                    aria-controls="v-pills-channel-partner-detail" aria-selected="false">2. Channel
                                    Partner Detail</a>

                            </div>
                        </div>

                        <div class="col-md-9">
                            <div class="tab-content text-muted mt-4 mt-md-0" id="v-pills-tabContent">
                                <div class="tab-pane fade show active" id="v-pills-user-detail" role="tabpanel"
                                    aria-labelledby="v-pills-user-detail-tab">

                                    <div class="card-body section_user_detail">






                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="user_first_name" class="form-label">First name <code
                                                            class="highlighter-rouge">*</code></label>
                                                    <input type="text" class="form-control" id="user_first_name"
                                                        name="user_first_name" placeholder="First Name"
                                                        value="" required>


                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="user_last_name" class="form-label">Last name <code
                                                            class="highlighter-rouge">*</code></label>
                                                    <input type="text" class="form-control" id="user_last_name"
                                                        name="user_last_name" placeholder="Last Name" value=""
                                                        required>


                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="user_email" class="form-label">Email <code
                                                            class="highlighter-rouge">*</code></label>
                                                    <input type="email" class="form-control" id="user_email"
                                                        name="user_email" placeholder="Email" value=""
                                                        required>


                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="insert_phone_number" class="form-label">Phone number
                                                        <code class="highlighter-rouge">*</code></label>

                                                    <div class="input-group">
                                                        <div class="input-group-text">
                                                            +91


                                                        </div>
                                                        <input type="number" class="form-control"
                                                            id="user_phone_number" name="user_phone_number"
                                                            placeholder="Phone number" value="" required>
                                                    </div>





                                                </div>
                                            </div>
                                        </div>





                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="user_country_id" class="form-label">Country <code
                                                            class="highlighter-rouge">*</code></label>
                                                    <select class="form-select" id="user_country_id"
                                                        name="user_country_id" required>
                                                        <option selected value="1">India</option>

                                                    </select>
                                                    <div class="invalid-feedback">
                                                        Please select country.
                                                    </div>

                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                    <label class="form-label">State <code
                                                            class="highlighter-rouge">*</code></label>
                                                    <select class="form-control select2-ajax" id="user_state_id"
                                                        name="user_state_id" required>

                                                    </select>
                                                    <div class="invalid-feedback">
                                                        Please select state.
                                                    </div>

                                                </div>



                                            </div>

                                            <div class="col-md-4">
                                                <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                    <label class="form-label">City <code
                                                            class="highlighter-rouge">*</code></label>
                                                    <select class="form-control select2-ajax" id="user_city_id"
                                                        name="user_city_id" required>

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
                                                    <label for="user_pincode" class="form-label">Pincode <code
                                                            class="highlighter-rouge">*</code></label>
                                                    <input type="text" class="form-control" id="user_pincode"
                                                        name="user_pincode" placeholder="Pincode" value=""
                                                        required>


                                                </div>
                                            </div>


                                        </div>


                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="user_address_line1" class="form-label">Address line 1
                                                        <code class="highlighter-rouge">*</code></label>
                                                    <input type="text" class="form-control"
                                                        id="user_address_line1" name="user_address_line1"
                                                        placeholder="Address line 1" value="" required>


                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="user_address_line2" class="form-label">Address line
                                                        2</label>
                                                    <input type="text" class="form-control"
                                                        id="user_address_line2" name="user_address_line2"
                                                        placeholder="Address line 2" value="">


                                                </div>
                                            </div>
                                        </div>

                                        @if ($data['isSalePerson'] == 0)
                                            <div class="row">
                                                <div class="col-md-12">

                                                    <div class="mb-3">
                                                        <label for="user_status" class="form-label">Status <code
                                                                class="highlighter-rouge">*</code></label>
                                                        <select class="form-select" id="user_status"
                                                            name="user_status" required>
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
                                        @endif

                                        <div class="row" id="div_user_type">
                                            <div class="col-md-12">

                                                <div class="mb-3">
                                                    <label for="user_status" class="form-label">User Type <code
                                                            class="highlighter-rouge">*</code></label>
                                                    <select class="form-select" id="user_type" name="user_type"
                                                        required>

                                                        @php
                                                            $accessTypes = getChannelPartnersAccess(Auth::user()->type);
                                                        @endphp
                                                        @if (count($accessTypes) > 0)
                                                            @foreach ($accessTypes as $key => $value)
                                                                <option value="{{ $value['id'] }}">
                                                                    {{ $value['name'] }} </option>
                                                            @endforeach
                                                        @endif

                                                    </select>
                                                    <div class="invalid-feedback">
                                                        Please select user type.
                                                    </div>

                                                </div>

                                            </div>

                                        </div>
                                        <br>
                                        <br>



                                    </div>

                                </div>
                                <div class="tab-pane fade " id="v-pills-channel-partner-detail" role="tabpanel"
                                    aria-labelledby="v-pills-channel-partner-detail-tab">


                                    <div class="card-body section_channel_partner">




                                        <div class="row ">


                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="channel_partner_firm_name" class="form-label">Firm
                                                        Name <code class="highlighter-rouge">*</code></label>
                                                    <input type="text" class="form-control"
                                                        id="channel_partner_firm_name"
                                                        name="channel_partner_firm_name" placeholder="Firm Name"
                                                        value="" required>


                                                </div>
                                            </div>

                                        </div>



                                        <div class="row">
                                            <div class="col-md-6">


                                                <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                    <label class="form-label">Bill From <code
                                                            class="highlighter-rouge">*</code></label>
                                                    <select class="form-control select2-ajax "
                                                        id="channel_partner_reporting_manager"
                                                        name="channel_partner_reporting_manager" required>

                                                    </select>
                                                    <div class="invalid-feedback">
                                                        Please select Reporting Manager.
                                                    </div>

                                                </div>






                                            </div>


                                            <div class="col-md-6" id="row_channel_partner_sale_persons">


                                                <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                    <label class="form-label">Assign Sales Persons <code
                                                            class="highlighter-rouge">*</code></label>
                                                    <select multiple="multiple"
                                                        class="form-control select2-ajax select2-multiple"
                                                        id="channel_partner_sale_persons"
                                                        name="channel_partner_sale_persons[]" required>

                                                    </select>
                                                    <div class="invalid-feedback">
                                                        Please select sale person type.
                                                    </div>

                                                </div>






                                            </div>

                                        </div>

                                        <div class="row d-none" id="row_verified">

                                            <div class="col-md-6">
                                                <div class="form-check ">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="channel_partner_data_verified"
                                                        id="channel_partner_data_verified" value="1">
                                                    <label class="form-check-label"
                                                        for="channel_partner_data_verified">
                                                        Is Data Verified?
                                                    </label>
                                                </div>

                                                <div class="form-check ">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="channel_partner_data_not_verified"
                                                        id="channel_partner_data_not_verified" value="1">
                                                    <label class="form-check-label"
                                                        for="channel_partner_data_not_verified">
                                                        Is Data Not Verified?
                                                    </label>
                                                </div>

                                                <div class="form-check ">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="channel_partner_missing_data"
                                                        id="channel_partner_missing_data" value="1">
                                                    <label class="form-check-label"
                                                        for="channel_partner_missing_data">
                                                        Is Missing Data?
                                                    </label>
                                                </div>
                                            </div>


                                            <div class="col-md-6">
                                                <div class="form-check ">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="channel_partner_tele_verified"
                                                        id="channel_partner_tele_verified" value="1">
                                                    <label class="form-check-label"
                                                        for="channel_partner_tele_verified">
                                                        Is Tele Verified?
                                                    </label>
                                                </div>
                                                <div class="form-check ">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="channel_partner_tele_not_verified"
                                                        id="channel_partner_tele_not_verified" value="1">
                                                    <label class="form-check-label"
                                                        for="channel_partner_tele_not_verified">
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



                                        <div class="row">
                                            <div class="col-md-6">


                                                <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                    <label class="form-label"> Payment Mode <code
                                                            class="highlighter-rouge">*</code></label>
                                                    <select class="form-control select2-ajax "
                                                        id="channel_partner_payment_mode"
                                                        name="channel_partner_payment_mode">

                                                        <option value="0">PDC</option>
                                                        <option value="1">Advance</option>
                                                        <option value="2">Credit</option>

                                                    </select>
                                                    <div class="invalid-feedback">
                                                        Please select payment type
                                                    </div>

                                                </div>








                                            </div>
                                            <div class="col-md-6" id="div_channel_partner_pending_credit">
                                                <div class="mb-3">
                                                    <label for="channel_partner_pending_credit"
                                                        class="form-label">Pending Credit</label>
                                                    <input type="number" class="form-control"
                                                        id="channel_partner_pending_credit"
                                                        name="channel_partner_pending_credit"
                                                        placeholder="Pending Credit" value="" disabled>


                                                </div>
                                            </div>

                                        </div>

                                        <div class="row channel_partner_payment_mode2">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="channel_partner_credit_limit"
                                                        class="form-label">Credit Limit <code
                                                            class="highlighter-rouge">*</code></label>
                                                    <input type="number" class="form-control"
                                                        id="channel_partner_credit_limit"
                                                        name="channel_partner_credit_limit" placeholder="Credit Limit"
                                                        value="" required>


                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="channel_partner_credit_days" class="form-label">Credit
                                                        Days <code class="highlighter-rouge">*</code></label>
                                                    <input type="number" class="form-control"
                                                        id="channel_partner_credit_days"
                                                        name="channel_partner_credit_days" placeholder="Credit Days"
                                                        value="" required>


                                                </div>
                                            </div>
                                        </div>


                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="channel_partner_gst_number" class="form-label">GST
                                                        Number <code class="highlighter-rouge"
                                                            id="channel_partner_gst_number_mandatary">*</code></label>
                                                    <input type="text" class="form-control"
                                                        id="channel_partner_gst_number"
                                                        name="channel_partner_gst_number" placeholder="GST Number"
                                                        value="" required>


                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="channel_partner_shipping_limit"
                                                        class="form-label">Shipping Limit <code
                                                            class="highlighter-rouge">*</code></label>
                                                    <input type="number" class="form-control"
                                                        id="channel_partner_shipping_limit"
                                                        name="channel_partner_shipping_limit"
                                                        placeholder="Shipping Limit" value="" required>


                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="channel_partner_shipping_cost"
                                                        class="form-label">Shipping Cost (per KG.) <code
                                                            class="highlighter-rouge">*</code></label>
                                                    <input type="number" class="form-control"
                                                        id="channel_partner_shipping_cost"
                                                        name="channel_partner_shipping_cost"
                                                        placeholder="Shipping Cost" value="" required>


                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">

                                                <div class="mb-3">
                                                    <h4 class="card-title col-md-6 col-xl-3">Delivery Address </h4>
                                                    <button type="button" class="btn btn-sm btn-primary right"
                                                        id="copyAddressBtn">Delivery Address same as user detail
                                                        address?</button>


                                                </div>
                                            </div>
                                        </div>



                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="channel_partner_d_country_id"
                                                        class="form-label">Country <code
                                                            class="highlighter-rouge">*</code></label>
                                                    <select class="form-select" id="channel_partner_d_country_id"
                                                        name="channel_partner_d_country_id" required>
                                                        <option selected value="1">India</option>

                                                    </select>
                                                    <div class="invalid-feedback">
                                                        Please select country.
                                                    </div>

                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                    <label class="form-label">State <code
                                                            class="highlighter-rouge">*</code></label>
                                                    <select class="form-control select2-ajax select2-state"
                                                        id="channel_partner_d_state_id"
                                                        name="channel_partner_d_state_id" required>

                                                    </select>
                                                    <div class="invalid-feedback">
                                                        Please select state.
                                                    </div>

                                                </div>



                                            </div>

                                            <div class="col-md-4">
                                                <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                    <label class="form-label">City <code
                                                            class="highlighter-rouge">*</code></label>
                                                    <select class="form-control select2-ajax"
                                                        id="channel_partner_d_city_id"
                                                        name="channel_partner_d_city_id" required>

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
                                                    <label for="channel_partner_d_pincode" class="form-label">Pincode
                                                        <code class="highlighter-rouge">*</code></label>
                                                    <input type="text" class="form-control"
                                                        id="channel_partner_d_pincode"
                                                        name="channel_partner_d_pincode" placeholder="Pincode"
                                                        value="" required>


                                                </div>
                                            </div>


                                        </div>


                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="channel_partner_d_address_line1"
                                                        class="form-label">Address line 1 <code
                                                            class="highlighter-rouge">*</code></label>
                                                    <input type="text" class="form-control"
                                                        id="channel_partner_d_address_line1"
                                                        name="channel_partner_d_address_line1"
                                                        placeholder="Address line 1" value="" required>


                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="user_address_line2" class="form-label">Address line
                                                        2</label>
                                                    <input type="text" class="form-control"
                                                        id="channel_partner_d_address_line2"
                                                        name="channel_partner_d_address_line2"
                                                        placeholder="Address line 2" value="">


                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                            </div>
                        </div>

                    </div>






















                </div>





                <div class="modal-footer">

                    <button id="btnNext" type="button" class="btn btn-primary">Next</button>



                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>


                    <button id="btnSave" type="submit" class="btn btn-primary">Save</button>



                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    var ajaxURLChannelPartnerDiscount = '{{ route('channel.partners.discount.ajax') }}';
    var ajaxURLChannelPartnerTypeDiscount = '{{ route('channel.partners.type.discount.ajax') }}';
    var ajaxURLSearchState = '{{ route('search.state.from.country') }}';
    var ajaxURLSearchCity = '{{ route('search.city.from.state') }}';
    var ajaxSearchReportingManager = '{{ route('channel.partners.search.reporting.manager') }}';
    var ajaxSearchSalePerson = '{{ route('channel.partners.search.sale.person') }}';
    var ajaxURLUserDetail = '{{ route('channel.partners.detail') }}';
    var ajaxCityDetail = '{{ route('channel.partners.city.detail') }}';
    var ajaxURLChannelPartnerDiscountSave = '{{ route('channel.partners.discount.save') }}';
    var ajaxURLChannelPartnerDiscountSaveAll = '{{ route('channel.partners.discount.save.all') }}';
    var ajaxURLChannelPartnerDiscountSearchProductGroup ='{{ route('channel.partners.discount.search.product.group') }}';
    var ajaxURLChannelPartnerDiscountCptSaveAll = '{{ route('channel.partners.discount.cpt.save.all') }}';






    $("#user_type").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#modalUser .modal-body")

    });


    $("#user_country_id").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#modalUser .modal-body")

    });


    $("#channel_partner_d_country_id").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#modalUser .modal-body")

    });


    $("#channel_partner_payment_mode").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#modalUser .modal-body")

    });

    $("#user_city_id").select2({
        ajax: {
            url: ajaxURLSearchCity,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    "country_id": function() {
                        return $("#user_country_id").val()
                    },
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


    $("#channel_partner_d_city_id").select2({
        ajax: {
            url: ajaxURLSearchCity,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    "country_id": function() {
                        return $("#channel_partner_d_country_id").val()
                    },
                    "state_id": function() {
                        return $("#channel_partner_d_state_id").val()
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


    $("#channel_partner_d_state_id").select2({
        ajax: {
            url: ajaxURLSearchState,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    "country_id": function() {
                        return $("#channel_partner_d_country_id").val()
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




    $("#channel_partner_reporting_manager").select2({
        ajax: {
            url: ajaxSearchReportingManager,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    "user_type": function() {
                        return $("#user_type").val()
                    },
                    "user_id": function() {
                        return $("#user_id").val()
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
                if ($("#user_type").val() == null) {
                    toastr["error"]("Please select user type first");

                }

                return {
                    results: data.results,
                    pagination: {
                        more: (params.page * 30) < data.total_count
                    }
                };
            },
            cache: false
        },
        placeholder: 'Search for a reporting manager',
        dropdownParent: $("#modalUser .modal-body ")
    });




    $("#channel_partner_sale_persons").select2({
        ajax: {
            url: ajaxSearchSalePerson,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    "user_type": function() {
                        return $("#user_type").val()
                    },
                    "user_id": function() {
                        return $("#user_id").val()
                    },
                    "channel_partner_reporting_manager": function() {
                        return $("#channel_partner_reporting_manager").val()
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
                if ($("#user_type").val() == null) {
                    toastr["error"]("Please select user type first");

                }
                if ($("#channel_partner_reporting_manager").val() == null) {
                    toastr["error"]("Please select reporting manager first");

                }



                return {
                    results: data.results,
                    pagination: {
                        more: (params.page * 30) < data.total_count
                    }
                };
            },
            cache: false
        },
        placeholder: 'Search for a sale persons',
        dropdownParent: $("#modalUser .modal-body")
    });




    $("#user_status").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#modalUser .modal-body")
    });


    $(document).ready(function() {


        //

        // $("#user_type").select2({
        //     minimumResultsForSearch: Infinity,
        //     dropdownParent: $("#modalUser .modal-body")

        // });


        // if(isSalePerson==1){

        //  $("#row_channel_partner_sale_persons").hide();
        //  $("#channel_partner_sale_persons").removeAttr('required');

        //  }else{

        //  $("#row_channel_partner_sale_persons").show();
        //  $("#channel_partner_sale_persons").prop('required',true);

        //  }


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

        $("#btnSave").html("Saving...");
        $("#btnSave").prop('disabled', true);
        return true;
    }

    // post-submit callback
    function showResponse(responseText, statusText, xhr, $form) {

        $("#btnSave").html("Save");
        $("#btnSave").prop('disabled', false);


        if (responseText['status'] == 1) {
            toastr["success"](responseText['msg']);
            reloadTable();
            resetInputForm();
            $("#modalUser").modal('hide');


        } else if (responseText['status'] == 0) {

            if (typeof responseText['data'] !== "undefined") {

                var size = Object.keys(responseText['data']).length;
                if (size > 0) {

                    for (var [key, value] of Object.entries(responseText['data'])) {

                        toastr["error"](value);
                    }

                }

            } else {
                toastr["error"](responseText['msg']);
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


    $('#v-pills-tab.nav a').click(function() {
        if ($(this).attr('id') == "v-pills-channel-partner-detail-tab") {



            $("#btnSave ").show();
            $("#btnNext").hide();


        } else {
            $("#btnNext").show();
            $("#btnSave ").hide();
        }
    })


    $("#btnNext").click(function() {

        $("#v-pills-user-detail-tab").removeClass('active');
        $("#v-pills-channel-partner-detail-tab").addClass('active');
        $("#v-pills-user-detail").removeClass('active');
        $("#v-pills-user-detail").removeClass('show');
        $("#v-pills-channel-partner-detail").addClass('active');
        $("#v-pills-channel-partner-detail").addClass('show');
        $("#btnNext").hide();
        $("#btnSave").show();
        $('#user_country_id').select2('open');
        $('#user_country_id').select2('close');

    });


    $("#copyAddressBtn").click(function() {


        if ($("#user_city_id").val() == null) {
            toastr["error"]("Please select city first");

        } else {


            $.ajax({
                type: 'GET',
                url: ajaxCityDetail + "?city_id=" + $("#user_city_id").val(),
                success: function(resultData) {


                    $("#channel_partner_d_country_id").select2("val", "1");

                    if (typeof resultData['data']['state']['id'] !== "undefined" && resultData[
                            'data']['state']['id'] !== null && typeof resultData['data']['state'][
                            'id'
                        ] !== "undefined" && resultData['data']['state']['id']) {

                        $("#channel_partner_d_state_id").empty().trigger('change');
                        var newOption = new Option(resultData['data']['state']['text'], resultData[
                            'data']['state']['id'], false, false);
                        $('#channel_partner_d_state_id').append(newOption).trigger('change');

                    }



                    if (typeof resultData['data']['id'] !== "undefined" && resultData['data'][
                            'id'
                        ] !== null && typeof resultData['data']['id'] !== "undefined" &&
                        resultData['data']['id']) {

                        $("#channel_partner_d_city_id").empty().trigger('change');
                        var newOption = new Option(resultData['data']['text'], resultData['data'][
                            'id'
                        ], false, false);
                        $('#channel_partner_d_city_id').append(newOption).trigger('change');

                    }




                    $("#channel_partner_d_pincode").val($("#user_pincode").val());
                    $("#channel_partner_d_address_line1").val($("#user_address_line1").val());
                    $("#channel_partner_d_address_line2").val($("#user_address_line2").val());

                }
            });

        }




    });

    $("#addBtnUser").click(function() {



        resetInputForm();
        $("#modalUserLabel").html("Add Channel Partner");
        $("#user_id").val(0);
        $(".loadingcls").hide();
        $("#formUser .row").show();
        // console.log($("#user_type").val());
        setTimeout(function() {
            $("#user_type").val($("#user_type").val());
            $('#user_type').trigger('change');
            // $("#user_type").select2("val", $("#user_type").val());
            changeUserType($("#user_type").val());

            $("#channel_partner_payment_mode").val('0');
            $('#channel_partner_payment_mode').trigger('change');
            $("#div_channel_partner_pending_credit").hide();


        }, 100);



    });

    function resetInputForm() {

        $('#v-pills-tab.nav a:first').tab('show');
        $("#btnNext").show();
        $("#btnSave ").hide();


        $(".channel_partner_payment_mode2").css('display', 'none');


        $('#formUser').trigger("reset");
        $("#user_status").select2("val", "1");
        $("#user_country_id").select2("val", "1");
        $("#user_state_id").empty().trigger('change');
        $("#user_city_id").empty().trigger('change');
        $("#channel_partner_reporting_manager").empty().trigger('change');
        $("#channel_partner_sale_persons").empty().trigger('change');


        $("#channel_partner_d_country_id").select2("val", "1");
        $("#channel_partner_d_state_id").empty().trigger('change');
        $("#channel_partner_d_city_id").empty().trigger('change');
        $("#formUser").removeClass('was-validated');
        $("#channel_partner_credit_limit").prop('disabled', false);
        $("#div_user_type").show();


        $('#formUser input*,#formUser select*,#formUser button*').removeAttr('disabled');








    }

    var editModeLoading = 0;

    function editView(id, typeOfProcess) {

        editModeLoading = 1;
        resetInputForm();

        $("#modalUser").modal('show');
        if (typeOfProcess == 'view') {
            $("#modalUserLabel").html("View Channel Partner #" + id);
        } else {
            $("#modalUserLabel").html("Edit Channel Partner #" + id);
        }
        $("#formUser .row").hide();
        $(".loadingcls").show();
        $("#btnSave ").hide();

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
                    $('#user_type').trigger('change');


                    $("#user_status").val(resultData['data']['status']);
                    $('#user_status').trigger('change');

                    if (resultData['data']['type'] == 104) {
                        $("#channel_partner_gst_number").removeAttr('required');

                    } else {
                        $("#channel_partner_gst_number").prop('required', true);
                    }




                    if (typeof resultData['data']['country']['id'] !== "undefined") {

                        $("#user_country_id").val("" + resultData['data']['country']['id'] + "");
                        $('#user_country_id').trigger('change');
                    }


                    if (typeof resultData['data']['state']['id'] !== "undefined") {
                        $("#user_state_id").empty().trigger('change');
                        var newOption = new Option(resultData['data']['state']['name'], resultData['data'][
                            'state'
                        ]['id'], false, false);
                        $('#user_state_id').append(newOption).trigger('change');
                        $("#user_state_id").val("" + resultData['data']['state']['id'] + "");
                        $('#user_state_id').trigger('change');


                    }

                    if (typeof resultData['data']['city']['id'] !== "undefined") {
                        $("#user_city_id").empty().trigger('change');
                        var newOption = new Option(resultData['data']['city']['name'], resultData['data'][
                            'city'
                        ]['id'], false, false);
                        $('#user_city_id').append(newOption).trigger('change');
                        $("#user_city_id").val("" + resultData['data']['city']['id'] + "");
                        $('#user_city_id').trigger('change');


                    }

                    $("#channel_partner_firm_name").val(resultData['data']['channel_partner']['firm_name']);



                    if (typeof resultData['data']['channel_partner']['reporting_manager']['id'] !==
                        "undefined") {



                        $("#channel_partner_reporting_manager").empty().trigger('change');
                        var newOption = new Option(resultData['data']['channel_partner'][
                            'reporting_manager'
                        ]['text'], resultData['data']['channel_partner'][
                            'reporting_manager'
                        ]['id'], false, false);
                        $('#channel_partner_reporting_manager').append(newOption).trigger('change');
                        $('#channel_partner_reporting_manager').val('' + resultData['data'][
                            'channel_partner'
                        ]['reporting_manager']['id']);
                        $('#channel_partner_reporting_manager').trigger('change');

                    }
                    // console.log(resultData['data']['channel_partner']['sale_persons']);
                    if (resultData['data']['channel_partner']['sale_persons'].length > 0) {
                        $("#channel_partner_sale_persons").empty().trigger('change');
                        var selectedSalePersons = [];

                        for (var i = 0; i < resultData['data']['channel_partner']['sale_persons']
                            .length; i++) {

                            selectedSalePersons.push('' + resultData['data']['channel_partner'][
                                'sale_persons'
                            ][i]['id'] + '');

                            var newOption = new Option(resultData['data']['channel_partner']['sale_persons']
                                [i]['text'], resultData['data']['channel_partner']['sale_persons'][i][
                                    'id'
                                ], false, false);
                            $('#channel_partner_sale_persons').append(newOption).trigger('change');


                        }
                        $("#channel_partner_sale_persons").val(selectedSalePersons).change();

                    }

                    setTimeout(function() {

                        $("#channel_partner_payment_mode").val('' + resultData['data'][
                            'channel_partner'
                        ]['payment_mode'] + '');
                        $('#channel_partner_payment_mode').trigger('change');
                    }, 100);
                    $("#channel_partner_credit_limit").val(resultData['data']['channel_partner'][
                        'credit_limit'
                    ]);
                    $("#channel_partner_credit_days").val(resultData['data']['channel_partner'][
                        'credit_days'
                    ]);
                    $("#channel_partner_gst_number").val(resultData['data']['channel_partner'][
                        'gst_number'
                    ]);
                    $("#channel_partner_shipping_limit").val(resultData['data']['channel_partner'][
                        'shipping_limit'
                    ]);
                    $("#channel_partner_shipping_cost").val(resultData['data']['channel_partner'][
                        'shipping_cost'
                    ]);



                    if (typeof resultData['data']['channel_partner']['d_country']['id'] !== "undefined") {

                        $("#channel_partner_d_country_id").val("" + resultData['data']['channel_partner'][
                            'd_country'
                        ]['id'] + "");
                        $('#channel_partner_d_country_id').trigger('change')



                    }




                    if (typeof resultData['data']['channel_partner']['d_state']['id'] !== "undefined") {
                        $("#channel_partner_d_state_id").empty().trigger('change');
                        var newOption = new Option(resultData['data']['channel_partner']['d_state']['name'],
                            resultData['data']['channel_partner']['d_state']['id'], false, false);
                        $('#channel_partner_d_state_id').append(newOption).trigger('change');
                        $("#channel_partner_d_state_id").val("" + resultData['data']['channel_partner'][
                            'd_state'
                        ]['id'] + "");
                        $('#channel_partner_d_state_id').trigger('change');


                    }

                    if (typeof resultData['data']['channel_partner']['d_city']['id'] !== "undefined") {
                        $("#channel_partner_d_city_id").empty().trigger('change');
                        var newOption = new Option(resultData['data']['channel_partner']['d_city']['name'],
                            resultData['data']['channel_partner']['d_city']['id'], false, false);
                        $('#channel_partner_d_city_id').append(newOption).trigger('change');
                        $("#channel_partner_d_city_id").val("" + resultData['data']['channel_partner'][
                            'd_city'
                        ]['id'] + "");
                        $('#channel_partner_d_city_id').trigger('change');


                    }

                    $("#channel_partner_d_pincode").val(resultData['data']['channel_partner']['d_pincode']);
                    $("#channel_partner_d_address_line1").val(resultData['data']['channel_partner'][
                        'd_address_line1'
                    ]);
                    $("#channel_partner_d_address_line2").val(resultData['data']['channel_partner'][
                        'd_address_line2'
                    ]);




                    $(".loadingcls").hide();
                    $("#formUser .row").show();
                    $("#div_user_type").hide();

                    changeUserType(resultData['data']['type']);

                    if (resultData['data']['channel_partner']['payment_mode'] == 2) {

                        $("#div_channel_partner_pending_credit").show();
                        $("#channel_partner_pending_credit").val(resultData['data']['channel_partner'][
                            'pending_credit'
                        ]);
                        $("#channel_partner_credit_limit").prop('disabled', true);
                    } else {
                        $("#div_channel_partner_pending_credit").hide();
                        $("#channel_partner_credit_limit").prop('disabled', true);
                    }

                    // $("#channel_partner_data_not_verified").prop("checked",false);
                    // $("#channel_partner_data_verified").prop("checked",false);
                    // $("#channel_partner_missing_data").prop("checked",false);

                    // if(resultData['data']['channel_partner']['data_verified_status']==1){
                    //     $("#channel_partner_data_verified").prop("checked",true);
                    // }else if(resultData['data']['channel_partner']['data_verified_status']==2){
                    //    $("#channel_partner_data_not_verified").prop("checked",true);
                    // }else if(resultData['data']['channel_partner']['data_verified_status']==3){
                    //    $("#channel_partner_missing_data").prop("checked",true);
                    // }


                    if (resultData['data']['channel_partner']['data_verified'] == 1) {

                        $("#channel_partner_data_verified").prop('checked', true);

                    } else {
                        $("#channel_partner_data_verified").prop('checked', false);

                    }

                    if (resultData['data']['channel_partner']['data_not_verified'] == 1) {

                        $("#channel_partner_data_not_verified").prop('checked', true);

                    } else {
                        $("#channel_partner_data_not_verified").prop('checked', false);

                    }

                    if (resultData['data']['channel_partner']['missing_data'] == 1) {

                        $("#channel_partner_missing_data").prop('checked', true);

                    } else {

                        $("#channel_partner_missing_data").prop('checked', false);

                    }

                    if (resultData['data']['channel_partner']['tele_verified'] == 1) {

                        $("#channel_partner_tele_verified").prop('checked', true);

                    } else {

                        $("#channel_partner_tele_verified").prop('checked', false);

                    }

                    if (resultData['data']['channel_partner']['tele_not_verified'] == 1) {

                        $("#channel_partner_tele_not_verified").prop('checked', true);

                    } else {

                        $("#channel_partner_tele_not_verified").prop('checked', false);

                    }




                    editModeLoading = 0;
                    if (typeOfProcess == 'view') {

                        $('#formUser input*,#formUser select*,#formUser button*').attr('disabled',
                            'disabled');
                        $("#btnSave ").hide();

                    } else {
                        $('#v-pills-tab.nav a:first').tab('show');
                    }








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

    $('#channel_partner_d_country_id').on('change', function() {

        if (editModeLoading == 0) {


            $("#channel_partner_d_state_id").empty().trigger('change');
            $("#channel_partner_d_city_id").empty().trigger('change');
        }

    });

    $('#channel_partner_d_state_id').on('change', function() {
        if (editModeLoading == 0) {
            $("#channel_partner_d_city_id").empty().trigger('change');
        }
    });



    $('#user_type').on('change', function() {

        changeUserType($(this).val());

    });


    $('#channel_partner_payment_mode').on('change', function() {


        changePaymetMode($(this).val());

    });


    function changeUserType(userType) {

        if (editModeLoading == 0) {
            $("#channel_partner_reporting_manager").empty().trigger('change');
            $("#channel_partner_sale_persons").empty().trigger('change');


        }

        if (userType == 104) {
            $("#channel_partner_gst_number").removeAttr('required');
            $("#channel_partner_gst_number_mandatary").html("");


        } else {
            $("#channel_partner_gst_number").prop('required', true);
            $("#channel_partner_gst_number_mandatary").html("*");

        }


    }

    function changePaymetMode(paymentMode) {





        if (paymentMode == 2) {
            $(".channel_partner_payment_mode2").show();
            $("#channel_partner_credit_days").attr('required', true);
            $("#channel_partner_credit_limit").attr('required', true);
        } else {
            $(".channel_partner_payment_mode2").hide();
            $("#channel_partner_credit_days").removeAttr('required')
            $("#channel_partner_credit_limit").removeAttr('required');


        }


    }



    var isLoadDiscountTable = 0;


    var DiscountTable = $('#datatableDiscount').DataTable({
        "aoColumnDefs": [{
            "bSortable": false,
            "aTargets": [3, 4]
        }],
        "order": [
            [0, 'desc']
        ],
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": ajaxURLChannelPartnerDiscount,
            "type": "POST",
            "data": {
                "_token": $("[name=_token").val(),
                "user_id": function() {
                    return $("#discount_user_id").val();
                },
                "isLoadDiscountTable": function() {
                    return isLoadDiscountTable
                },
                "product_group_id": function() {
                    return $("#discount_product_group_id").val()
                },
            }
        },
        "aoColumns": [{
                "mData": "product_brand"
            },
            {
                "mData": "product_code"
            },
            {
                "mData": "description"
            },
            {
                "mData": "discount_percentage"
            },
            {
                "mData": "new_discount_percentage"
            },

        ]
    });
    var cptDiscountTable = $('#cpt_datatableDiscount').DataTable({
        "aoColumnDefs": [{
            "bSortable": false,
            "aTargets": [3]
        }],
        "order": [
            [0, 'desc']
        ],
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": ajaxURLChannelPartnerTypeDiscount,
            "type": "POST",
            "data": {
                "_token": $("[name=_token").val(),
                "user_type_id": '',
                "product_group_id": function() {
                    return $("#cpt_discount_product_group_id").val()
                },
            }
        },
        "aoColumns": [{
                "mData": "product_brand"
            },
            {
                "mData": "product_code"
            },
            {
                "mData": "description"
            },
            {
                "mData": "new_discount_percentage"
            },

        ]
    });






    function editDiscount(id) {
        $("#discount_user_id").val(id);
        if (isLoadDiscountTable == 0) {
            isLoadDiscountTable = 1;
        }
        $("#modalDiscount").modal('show');
        $("#modalDiscountLabel").html("Edit Discount Channel Partner #" + id);

        DiscountTable.ajax.reload(null, false);
        $("#discountSync").html("Saved");
        $("#discount_all_discount").val(0);




    }

    // function debounce(callback, wait) {
    //   let timeout;
    //   return (...args) => {
    //       clearTimeout(timeout);
    //       timeout = setTimeout(function () { callback.apply(this, args); }, wait);
    //   };
    // }


    $(document).delegate('.valid-discount', 'keyup', function() {
        var max = parseInt($(this).attr('max'));
        var min = parseInt($(this).attr('min'));
        if ($(this).val() > max) {
            $(this).val(max);
        } else if ($(this).val() < min) {
            $(this).val(min);
        }
    });


    $(document).delegate('.new-discount-cls', 'change', function() {


        $("#discountSync").html(
            '<i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i> <span  >Saving...</span>');
        $.ajax({
            type: 'POST',
            url: ajaxURLChannelPartnerDiscountSave,
            data: {
                "discount_percentage": $(this).val(),
                "id": $(this).attr('id'),
                "_token": $("[name=_token").val(),
            },
            success: function(resultData) {

                $("#discountSync").html("Saved");

                DiscountTable.ajax.reload(null, false);;

            }
        });


    });


    // $(document).delegate('.new-discount-cls','keyup', debounce( ()=>{


    //



    // },500));


    $(function() {
        $(".new-discount-cls").change(function() {
            var max = parseInt($(this).attr('max'));
            var min = parseInt($(this).attr('min'));
            if ($(this).val() > max) {
                $(this).val(max);
            } else if ($(this).val() < min) {
                $(this).val(min);
            }
        });
    });


    //ajaxURLChannelPartnerDiscountSearchProductGroup
    $("#discount_product_group_id").select2({
        ajax: {
            url: ajaxURLChannelPartnerDiscountSearchProductGroup,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    "user_type": function() {
                        return $("#user_type").val()
                    },
                    "user_id": function() {
                        return $("#user_id").val()
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
                if ($("#user_type").val() == null) {
                    toastr["error"]("Please select user type first");

                }

                return {
                    results: data.results,
                    pagination: {
                        more: (params.page * 30) < data.total_count
                    }
                };
            },
            cache: false
        },
        placeholder: 'Search for a product group',
        dropdownParent: $("#modalDiscount .modal-body")
    });

    $("#cpt_discount_product_group_id").select2({
        ajax: {
            url: ajaxURLChannelPartnerDiscountSearchProductGroup,
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
        placeholder: 'Search product group',
        dropdownParent: $("#modalChannelPartenerTypeDiscount .modal-body")
    });

    $("#cpt_discount_channel_partners").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#modalChannelPartenerTypeDiscount .modal-body")

    });

    // $(".discount_channel_partners").select2();


    $("#saveAllDiscount").click(function() {
        $("#discountSync").html(
            '<i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i> <span  >Saving...</span>');


        $.ajax({
            type: 'POST',
            url: ajaxURLChannelPartnerDiscountSaveAll,
            data: {
                "product_group_id": function() {
                    return $("#discount_product_group_id").val()
                },
                "_token": $("[name=_token").val(),
                "user_id": function() {
                    return $("#discount_user_id").val();
                },
                "discount_percentage": function() {
                    return $("#discount_all_discount").val()
                },
            },

            success: function(resultData) {
                $("#discount_all_discount").val(0);
                $("#discountSync").html("Saved");
                DiscountTable.ajax.reload(null, false);

            }
        });

    });





    $('#channel_partner_tele_verified').on('change', function() {

        var isChecked = $(this).is(':checked');

        if (isChecked) {

            $("#channel_partner_tele_not_verified").prop("checked", false);
            //$("#architect_missing_data").prop("checked",false);

        }

    });

    $('#channel_partner_tele_not_verified').on('change', function() {

        var isChecked = $(this).is(':checked');
        if (isChecked) {

            $("#channel_partner_tele_verified").prop("checked", false);
            // $("#architect_missing_data").prop("checked",false);


        }

    });

    $('#channel_partner_data_verified').on('change', function() {

        var isChecked = $(this).is(':checked');

        if (isChecked) {

            $("#channel_partner_data_not_verified").prop("checked", false);
            //$("#architect_missing_data").prop("checked",false);

        }

    });

    $('#channel_partner_data_not_verified').on('change', function() {

        var isChecked = $(this).is(':checked');
        if (isChecked) {

            $("#channel_partner_data_verified").prop("checked", false);
            // $("#architect_missing_data").prop("checked",false);


        }

    });

    $('#channel_partner_missing_data').on('change', function() {

        var isChecked = $(this).is(':checked');
        if (isChecked) {

            // $("#architect_data_not_verified").prop("checked",false);
            //  $("#architect_data_verified").prop("checked",false);


        }

    });





    $("#saveAllCptDiscount").click(function() {
        $("#cptdiscountSync").html('<i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i> <span  >Saving...</span>');

        $.ajax({
            type: 'POST',
            url: ajaxURLChannelPartnerDiscountCptSaveAll,
            data: {
                "product_group_id": function() {
                    return $("#cpt_discount_product_group_id").val()
                },
                "_token": $("[name=_token").val(),
                "channel_partner_type_id": function() {
                    return $("#cpt_discount_channel_partners").val();
                },
                "discount_percentage": function() {
                    return $("#cpt_discount_all_discount").val();
                },
            },

            success: function(resultData) {
                $("#cpt_discount_all_discount").val(0);
                $("#cptdiscountSync").html("Saved");
                DiscountTable.ajax.reload(null, false);
                toastr["success"](resultData['msg']);
            }
        });

    });


    $('#channel_partner_tele_verified').on('change', function() {

        var isChecked = $(this).is(':checked');

        if (isChecked) {

            $("#channel_partner_tele_not_verified").prop("checked", false);
            //$("#architect_missing_data").prop("checked",false);

        }

    });

    $('#channel_partner_tele_not_verified').on('change', function() {

        var isChecked = $(this).is(':checked');
        if (isChecked) {

            $("#channel_partner_tele_verified").prop("checked", false);
            // $("#architect_missing_data").prop("checked",false);


        }

    });

    $('#channel_partner_data_verified').on('change', function() {

        var isChecked = $(this).is(':checked');

        if (isChecked) {

            $("#channel_partner_data_not_verified").prop("checked", false);
            //$("#architect_missing_data").prop("checked",false);

        }

    });

    $('#channel_partner_data_not_verified').on('change', function() {

        var isChecked = $(this).is(':checked');
        if (isChecked) {

            $("#channel_partner_data_verified").prop("checked", false);
            // $("#architect_missing_data").prop("checked",false);


        }

    });

    $('#channel_partner_missing_data').on('change', function() {

        var isChecked = $(this).is(':checked');
        if (isChecked) {

            // $("#architect_data_not_verified").prop("checked",false);
            //  $("#architect_data_verified").prop("checked",false);


        }

    });





    $('#discount_product_group_id').on('change', function() {


        $("#discount_all_discount").val(0);

        DiscountTable.ajax.reload(null, false);

    });

    $('#cpt_discount_product_group_id').on('change', function() {


        $("#cpt_discount_all_discount").val(0);

        cptDiscountTable.ajax.reload(null, false);

    });
</script>
