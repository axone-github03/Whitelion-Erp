<div class="modal fade" id="modalUser" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" role="dialog"
    aria-labelledby="modalUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalUserLabel"> User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>




            <form id="formUser" action="{{ route('channel.partners.save') }}" method="POST" class="needs-validation"
                novalidate>
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


                                <a class="nav-link mb-2" id="v-pills-channel-partner-detail-tab" data-bs-toggle="pill"
                                    href="#v-pills-channel-partner-detail" role="tab"
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
                                                    <label for="user_first_name" class="form-label">First name</label>
                                                    <input type="text" class="form-control" id="user_first_name"
                                                        name="user_first_name" placeholder="First Name" value=""
                                                        required disabled>


                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="user_last_name" class="form-label">Last name</label>
                                                    <input type="text" class="form-control" id="user_last_name"
                                                        name="user_last_name" placeholder="Last Name" value=""
                                                        required disabled>


                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="user_email" class="form-label">Email</label>
                                                    <input type="email" class="form-control" id="user_email"
                                                        name="user_email" placeholder="Email" value="" required
                                                        disabled>


                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="insert_phone_number" class="form-label">Phone
                                                        number</label>

                                                    <div class="input-group">
                                                        <div class="input-group-text">
                                                            +91


                                                        </div>
                                                        <input type="number" class="form-control"
                                                            id="user_phone_number" name="user_phone_number"
                                                            placeholder="Phone number" value="" required
                                                            disabled>
                                                    </div>





                                                </div>
                                            </div>
                                        </div>





                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="user_country_id" class="form-label">Country</label>
                                                    <select class="form-select" id="user_country_id"
                                                        name="user_country_id" required disabled>
                                                        <option selected value="1">India</option>

                                                    </select>
                                                    <div class="invalid-feedback">
                                                        Please select country.
                                                    </div>

                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                    <label class="form-label">State </label>
                                                    <select class="form-control select2-ajax" id="user_state_id"
                                                        name="user_state_id" required disabled>

                                                    </select>
                                                    <div class="invalid-feedback">
                                                        Please select state.
                                                    </div>

                                                </div>



                                            </div>

                                            <div class="col-md-4">
                                                <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                    <label class="form-label">City </label>
                                                    <select class="form-control select2-ajax" id="user_city_id"
                                                        name="user_city_id" required disabled>

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
                                                    <label for="user_pincode" class="form-label">Pincode</label>
                                                    <input type="text" class="form-control" id="user_pincode"
                                                        name="user_pincode" placeholder="Pincode" value=""
                                                        required disabled>


                                                </div>
                                            </div>


                                        </div>


                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="user_address_line1" class="form-label">Address line
                                                        1</label>
                                                    <input type="text" class="form-control"
                                                        id="user_address_line1" name="user_address_line1"
                                                        placeholder="Address line 1" value="" required disabled>


                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="user_address_line2" class="form-label">Address line
                                                        2</label>
                                                    <input type="text" class="form-control"
                                                        id="user_address_line2" name="user_address_line2"
                                                        placeholder="Address line 2" value="" disabled>


                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-12">

                                                <div class="mb-3">
                                                    <label for="user_status" class="form-label">Status</label>
                                                    <select class="form-select" id="user_status" name="user_status"
                                                        required disabled>
                                                        <option selected value="1">Active</option>
                                                        <option value="0">Inactive</option>
                                                        <option value="2">Blocked</option>

                                                    </select>
                                                    <div class="invalid-feedback">
                                                        Please select status.
                                                    </div>

                                                </div>

                                            </div>

                                        </div>

                                        <div class="row">
                                            <div class="col-md-12">

                                                <div class="mb-3">
                                                    <label for="user_status" class="form-label">User Type </label>
                                                    <select class="form-select" id="user_type" name="user_type"
                                                        required disabled>

                                                        @php
                                                            $accessTypes = getChannelPartners();
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



                                    </div>

                                </div>
                                <div class="tab-pane fade " id="v-pills-channel-partner-detail" role="tabpanel"
                                    aria-labelledby="v-pills-channel-partner-detail-tab">


                                    <div class="card-body section_channel_partner">




                                        <div class="row ">


                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="channel_partner_firm_name" class="form-label">Firm
                                                        Name</label>
                                                    <input type="text" class="form-control"
                                                        id="channel_partner_firm_name"
                                                        name="channel_partner_firm_name" placeholder="Firm Name"
                                                        value="" required disabled>


                                                </div>
                                            </div>

                                        </div>



                                        <div class="row">
                                            <div class="col-md-6">


                                                <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                    <label class="form-label">Reporting Manager </label>
                                                    <select class="form-control select2-ajax "
                                                        id="channel_partner_reporting_manager"
                                                        name="channel_partner_reporting_manager" required disabled>

                                                    </select>
                                                    <div class="invalid-feedback">
                                                        Please select Reporting Manager.
                                                    </div>

                                                </div>






                                            </div>


                                            <div class="col-md-6">


                                                <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                    <label class="form-label">Assign Sales Persons </label>
                                                    <select multiple="multiple"
                                                        class="form-control select2-ajax select2-multiple"
                                                        id="channel_partner_sale_persons"
                                                        name="channel_partner_sale_persons[]" required disabled>

                                                    </select>
                                                    <div class="invalid-feedback">
                                                        Please select sale person type.
                                                    </div>

                                                </div>






                                            </div>

                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">


                                                <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                    <label class="form-label"> Payment Mode </label>
                                                    <select class="form-control select2-ajax "
                                                        id="channel_partner_payment_mode"
                                                        name="channel_partner_payment_mode" disabled>

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
                                                        class="form-label">Credit Limit</label>
                                                    <input type="number" class="form-control"
                                                        id="channel_partner_credit_limit"
                                                        name="channel_partner_credit_limit" placeholder="Credit Limit"
                                                        value="" required>


                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="channel_partner_credit_days" class="form-label">Credit
                                                        Days</label>
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
                                                        Number</label>
                                                    <input type="text" class="form-control"
                                                        id="channel_partner_gst_number"
                                                        name="channel_partner_gst_number" placeholder="GST Number"
                                                        value="" required disabled>


                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="channel_partner_shipping_limit"
                                                        class="form-label">Shipping Limit</label>
                                                    <input type="number" class="form-control"
                                                        id="channel_partner_shipping_limit"
                                                        name="channel_partner_shipping_limit"
                                                        placeholder="Shipping Limit" value="" required disabled>


                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="channel_partner_shipping_cost"
                                                        class="form-label">Shipping Cost (per KG.)</label>
                                                    <input type="number" class="form-control"
                                                        id="channel_partner_shipping_cost"
                                                        name="channel_partner_shipping_cost"
                                                        placeholder="Shipping Cost" value="" required disabled>


                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">

                                                <div class="mb-3">
                                                    <h4 class="card-title col-md-6 col-xl-3">Delivery Address </h4>



                                                </div>
                                            </div>
                                        </div>



                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="channel_partner_d_country_id"
                                                        class="form-label">Country</label>
                                                    <select class="form-select" id="channel_partner_d_country_id"
                                                        name="channel_partner_d_country_id" required disabled>
                                                        <option selected value="1">India</option>

                                                    </select>
                                                    <div class="invalid-feedback">
                                                        Please select country.
                                                    </div>

                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                    <label class="form-label">State </label>
                                                    <select class="form-control select2-ajax select2-state"
                                                        id="channel_partner_d_state_id"
                                                        name="channel_partner_d_state_id" required disabled>

                                                    </select>
                                                    <div class="invalid-feedback">
                                                        Please select state.
                                                    </div>

                                                </div>



                                            </div>

                                            <div class="col-md-4">
                                                <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                    <label class="form-label">City </label>
                                                    <select class="form-control select2-ajax"
                                                        id="channel_partner_d_city_id"
                                                        name="channel_partner_d_city_id" required disabled>

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
                                                    <label for="channel_partner_d_pincode"
                                                        class="form-label">Pincode</label>
                                                    <input type="text" class="form-control"
                                                        id="channel_partner_d_pincode"
                                                        name="channel_partner_d_pincode" placeholder="Pincode"
                                                        value="" required disabled>


                                                </div>
                                            </div>


                                        </div>


                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="channel_partner_d_address_line1"
                                                        class="form-label">Address line 1</label>
                                                    <input type="text" class="form-control"
                                                        id="channel_partner_d_address_line1"
                                                        name="channel_partner_d_address_line1"
                                                        placeholder="Address line 1" value="" required disabled>


                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="user_address_line2" class="form-label">Address line
                                                        2</label>
                                                    <input type="text" class="form-control"
                                                        id="channel_partner_d_address_line2"
                                                        name="channel_partner_d_address_line2"
                                                        placeholder="Address line 2" value="" disabled>


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


                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    var ajaxURLUserDetail = '{{ route('channel.partners.detail.view') }}';







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
        placeholder: 'Search for a city',
        minimumInputLength: 1,
        dropdownParent: $("#modalUser .modal-body")
    });


    $("#channel_partner_d_city_id").select2({
        placeholder: 'Search for a city',
        minimumInputLength: 1,
        dropdownParent: $("#modalUser .modal-body")
    });

    $("#user_state_id").select2({
        placeholder: 'Search for a state',
        minimumInputLength: 1,
        dropdownParent: $("#modalUser .modal-body")
    });


    $("#channel_partner_d_state_id").select2({

        placeholder: 'Search for a state',
        minimumInputLength: 1,
        dropdownParent: $("#modalUser .modal-body")
    });




    $("#channel_partner_reporting_manager").select2({

        placeholder: 'Search for a reporting manager',
        dropdownParent: $("#modalUser .modal-body ")
    });




    $("#channel_partner_sale_persons").select2({

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
        return true;
    }

    // post-submit callback
    function showResponse(responseText, statusText, xhr, $form) {


        if (responseText['status'] == 1) {
            toastr["success"](responseText['msg']);
            reloadTable();
            resetInputForm();
            $("#modalUser").modal('hide');


        } else if (responseText['status'] == 0) {

            toastr["error"](responseText['msg']);

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


    $('.nav a').click(function() {
        if ($(this).attr('id') == "v-pills-channel-partner-detail-tab") {



            $("#modalUser .modal-footer").show();
            // $("#channel_partner_payment_mode").select2("val", "0");
            //  changePaymetMode(0);
            //  $("#channel_partner_credit_days").removeAttr('required')
            //  $("#channel_partner_credit_limit").removeAttr('required');

        } else {
            $("#modalUser .modal-footer").hide();
        }
    })








    function resetInputForm() {

        $('.nav a:first').tab('show');
        $("#modalUser .modal-footer").hide();
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








    }

    var editModeLoading = 0;

    function userView(id) {

        editModeLoading = 1;


        resetInputForm();

        $("#modalUser").modal('show');
        $("#modalUserLabel").html("Edit Channel Partner #" + id);
        $("#formUser .row").hide();
        $(".loadingcls").show();
        $("#modalUser .modal-footer").hide();

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
                        $("#user_city_id").val("" + resultData['data']['channel_partner']['d_city']['id'] +
                            "");
                        $('#user_city_id').trigger('change');


                    }

                    $("#channel_partner_firm_name").val(resultData['data']['channel_partner']['firm_name']);



                    if (typeof resultData['data']['channel_partner']['reporting_manager']['id'] !==
                        "undefined") {



                        $("#channel_partner_reporting_manager").empty().trigger('change');
                        var newOption = new Option(resultData['data']['channel_partner'][
                            'reporting_manager']['text'], resultData['data']['channel_partner'][
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
                        'gst_number']);
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
</script>
