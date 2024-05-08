<link href="{{ asset('assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}" rel="stylesheet"
    type="text/css">
<div class="modal fade" id="modalUser" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" role="dialog"
    aria-labelledby="modalUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered-scrollable modal-lg " role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalUserLabel"> User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formUser" action="{{ route('users.save') }}" method="POST" class="needs-validation" novalidate>
                <div class="modal-body">
                    @csrf
                    <div class="col-md-12 text-center loadingcls">
                        <button type="button" class="btn btn-light waves-effect">
                            <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading
                        </button>
                    </div>
                    <input type="hidden" name="user_id" id="user_id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="user_first_name" class="form-label">First name <code
                                        class="highlighter-rouge">*</code></label>
                                <input type="text" class="form-control" id="user_first_name" name="user_first_name"
                                    placeholder="First Name" value="" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="user_last_name" class="form-label">Last name <code
                                        class="highlighter-rouge">*</code></label>
                                <input type="text" class="form-control" id="user_last_name" name="user_last_name"
                                    placeholder="Last Name" value="" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="user_email" class="form-label">Email <code
                                        class="highlighter-rouge">*</code></label>
                                <input type="email" class="form-control" id="user_email" name="user_email"
                                    placeholder="Email" value="" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="insert_phone_number" class="form-label">Phone number <code
                                        class="highlighter-rouge">*</code></label>
                                <div class="input-group">
                                    <div class="input-group-text">
                                        +91
                                    </div>
                                    <input type="number" class="form-control" id="user_phone_number"
                                        name="user_phone_number" placeholder="Phone number" value="" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="user_ctc" class="form-label">CTC </label>
                                <input type="number" class="form-control" id="user_ctc" name="user_ctc"
                                    placeholder="CTC" value="" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="user_ctc" class="form-label">Joining Date</label>
                                <div class="input-group" id="user_joining_date">
                                    <input autocomplete="off" type="text" class="form-control"
                                        placeholder="YYYY-MM-DD" data-date-format="yyyy-mm-dd"
                                        data-date-container='#user_joining_date' data-provide="datepicker"
                                        data-date-autoclose="true" required id="user_joining_date_value" name="user_joining_date" value="">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="user_country_id" class="form-label">Country <code
                                        class="highlighter-rouge">*</code></label>
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
                                <select class="form-control select2-ajax" id="user_state_id" name="user_state_id"
                                    required>
                                </select>
                                <div class="invalid-feedback">
                                    Please select state.
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                <label class="form-label">City <code class="highlighter-rouge">*</code></label>
                                <select class="form-control select2-ajax" id="user_city_id" name="user_city_id"
                                    required>
                                </select>
                                <div class="invalid-feedback">
                                    Please select state.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="user_pincode" class="form-label">Pincode <code
                                        class="highlighter-rouge">*</code></label>
                                <input type="text" class="form-control" id="user_pincode" name="user_pincode"
                                    placeholder="Pincode" value="" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="user_address_line1" class="form-label">Address line 1 <code
                                        class="highlighter-rouge">*</code></label>
                                <input type="text" class="form-control" id="user_address_line1"
                                    name="user_address_line1" placeholder="Address line 1" value="" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="user_address_line2" class="form-label">Address line 2</label>
                                <input type="text" class="form-control" id="user_address_line2"
                                    name="user_address_line2" placeholder="Address line 2" value="">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="user_status" class="form-label">Status <code
                                        class="highlighter-rouge">*</code></label>
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


                    <div class="row">

                        <div class="col-lg-12" id="div_user_type">
                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                <label class="form-label">User Type <code class="highlighter-rouge">*</code></label>
                                <select class="form-control select2-ajax" id="user_type" name="user_type" required>




                                    @php
                                        $accessTypes = getUsersAccess(Auth::user()->type);
                                    @endphp
                                    @if (count($accessTypes) > 0)

                                        @foreach ($accessTypes as $key => $value)
                                            <option value="{{ $value['id'] }}">{{ $value['name'] }}</option>
                                        @endforeach

                                    @endif

                                </select>

                            </div>

                        </div>




                    </div>




                    <div class="row sec_user_purchase_person">
                        <div class="col-md-12">


                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                <label class="form-label">Purchase Person Type <code
                                        class="highlighter-rouge">*</code></label>
                                <select class="form-control select2-ajax " id="purchase_person_type"
                                    name="purchase_person_type">

                                </select>
                                <div class="invalid-feedback">
                                    Please select purchase person type.
                                </div>

                            </div>






                        </div>

                    </div>

                    <div class="row sec_user_purchase_person">
                        <div class="col-md-12">


                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                <label class="form-label">Purchase Person Reporing Manager <code
                                        class="highlighter-rouge">*</code></label>
                                <select class="form-control select2-ajax " id="purchase_person_reporting_manager"
                                    name="purchase_person_reporting_manager">

                                </select>
                                <div class="invalid-feedback">
                                    Please select reporting manager
                                </div>

                            </div>






                        </div>

                    </div>


                    <div class="row sec_user_sale_person">
                        <div class="col-md-12">


                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                <label class="form-label">Sales Person Type <code
                                        class="highlighter-rouge">*</code></label>
                                <select class="form-control select2-ajax " id="sale_person_type"
                                    name="sale_person_type">

                                </select>
                                <div class="invalid-feedback">
                                    Please select sales person type.
                                </div>

                            </div>






                        </div>

                    </div>

                    <div class="row sec_user_sale_person">
                        <div class="col-md-12">


                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                <label class="form-label">Sale Person Reporing Manager <code
                                        class="highlighter-rouge">*</code></label>
                                <select class="form-control select2-ajax " id="sale_person_reporting_manager"
                                    name="sale_person_reporting_manager">

                                </select>
                                <div class="invalid-feedback">
                                    Please select reporting manager
                                </div>

                            </div>






                        </div>

                    </div>


                    <div class="row sec_user_sale_person">
                        <div class="col-md-12">


                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                <label class="form-label">Sale Person State <code
                                        class="highlighter-rouge">*</code></label>
                                <select multiple="multiple" class="form-control select2-ajax select2-multiple"
                                    id="sale_person_state" name="sale_person_state[]">

                                </select>
                                <div class="invalid-feedback">
                                    Please select state
                                </div>

                            </div>






                        </div>

                    </div>

                    <div class="row sec_user_sale_person">
                        <div class="col-md-12">


                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                <label class="form-label">Sale Person City <code
                                        class="highlighter-rouge">*</code></label>
                                <select multiple="multiple" class="form-control select2-ajax select2-multiple"
                                    id="sale_person_city" name="sale_person_city[]">

                                </select>
                                <div class="invalid-feedback">
                                    Please select city
                                </div>

                            </div>






                        </div>

                    </div>

                    <!-- AXONE WORK START -->
                    <div class="row sec_user_service_executive">
                        <div class="col-md-12">
                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                <label class="form-label">Service Executive Type <code
                                        class="highlighter-rouge">*</code></label>
                                <select class="form-control select2-ajax " id="service_executive_type"
                                    name="service_executive_type">
                                </select>
                                <div class="invalid-feedback">
                                    Please select service executive type.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row sec_user_service_executive">
                        <div class="col-md-12">
                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                <label class="form-label">Service Executive Reporing Manager <code
                                        class="highlighter-rouge">*</code></label>
                                <select class="form-control select2-ajax " id="service_executive_reporting_manager"
                                    name="service_executive_reporting_manager">
                                </select>
                                <div class="invalid-feedback">
                                    Please select reporting manager
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row sec_user_service_executive">
                        <div class="col-md-12">
                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                <label class="form-label">Service Executive State <code
                                        class="highlighter-rouge">*</code></label>
                                <select multiple="multiple" class="form-control select2-ajax select2-multiple"
                                    id="service_executive_state" name="service_executive_state[]">
                                </select>
                                <div class="invalid-feedback">
                                    Please select state
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row sec_user_service_executive">
                        <div class="col-md-12">
                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                <label class="form-label">Service Executive City <code
                                        class="highlighter-rouge">*</code></label>
                                <select multiple="multiple" class="form-control select2-ajax select2-multiple"
                                    id="service_executive_city" name="service_executive_city[]">
                                </select>
                                <div class="invalid-feedback">
                                    Please select city
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- AXONE WORK END -->


                    <div class="row sec_user_purchase_person">
                        <div class="col-md-12">


                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                <label class="form-label">Purchase Person State <code
                                        class="highlighter-rouge">*</code></label>
                                <select multiple="multiple" class="form-control select2-ajax select2-multiple"
                                    id="purchase_person_state" name="purchase_person_state[]">

                                </select>
                                <div class="invalid-feedback">
                                    Please select state
                                </div>

                            </div>






                        </div>

                    </div>

                    <div class="row sec_user_purchase_person">
                        <div class="col-md-12">


                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                <label class="form-label">Purchase Person City <code
                                        class="highlighter-rouge">*</code></label>
                                <select multiple="multiple" class="form-control select2-ajax select2-multiple"
                                    id="purchase_person_city" name="purchase_person_city[]">

                                </select>
                                <div class="invalid-feedback">
                                    Please select city
                                </div>

                            </div>






                        </div>

                    </div>



                    <div class="row sec_user_tele_sales">
                        <div class="col-md-12">


                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                <label class="form-label">Tele Sales State <code
                                        class="highlighter-rouge">*</code></label>
                                <select multiple="multiple" class="form-control select2-ajax select2-multiple"
                                    id="tele_sales_state" name="tele_sales_state[]">

                                </select>
                                <div class="invalid-feedback">
                                    Please select state
                                </div>

                            </div>






                        </div>

                    </div>

                    <div class="row sec_user_tele_sales">
                        <div class="col-md-12">


                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                <label class="form-label">Tele Sales City <code
                                        class="highlighter-rouge">*</code></label>
                                <select multiple="multiple" class="form-control select2-ajax select2-multiple"
                                    id="tele_sales_city" name="tele_sales_city[]">

                                </select>
                                <div class="invalid-feedback">
                                    Please select city
                                </div>

                            </div>






                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button id="btnSave" type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="{{ asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
<script type="text/javascript">
    var ajaxURLSearchState = "{{ route('users.search.state') }}";
    var ajaxURLSearchCity = "{{ route('users.search.city') }}";
    var ajaxURLSearchCompany = "{{ route('users.search.company') }}";
    var ajaxURLSearchStateCities = "{{ route('users.search.state.cities') }}";
    var ajaxURLSearchSalePersonType = "{{ route('users.search.saleperson.type') }}";
    var ajaxURLSearchPurchasePersonType = "{{ route('users.search.purcheperson.type') }}";
    var ajaxURLSearchSalePersonReportingManager = "{{ route('users.reporting.manager') }}";
    var ajaxURLSearchPurchasePersonReportingManager = "{{ route('users.reporting.manager.purchase') }}";
    var ajaxURLUserDetail = "{{ route('users.detail') }}";
    var ajaxURLStateCities = "{{ route('users.state.cities') }}";
    var isChannelPartner = "{{ isChannelPartner(Auth::user()->type) }}"

    var ajaxURLSearchServiceExecutiveType = '{{ route('users.search.service.executive.type') }}';
    var ajaxURLSearchServiceExecutiveReportingManager =
        '{{ route('users.search.service.executive.reporting.manager') }}';

    $("#user_type").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#modalUser .modal-body")

    });

    $("#user_country_id").select2({
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

    $("#sale_person_type").select2({
        ajax: {
            url: ajaxURLSearchSalePersonType,
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
        placeholder: 'Search for a sale person type',
        dropdownParent: $("#modalUser .modal-body")
    });

    $("#purchase_person_type").select2({
        ajax: {
            url: ajaxURLSearchPurchasePersonType,
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
        placeholder: 'Search for a purchase person type',
        dropdownParent: $("#modalUser .modal-body")
    });

    $("#sale_person_reporting_manager").select2({
        ajax: {
            url: ajaxURLSearchSalePersonReportingManager,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    "sale_person_type": function() {
                        return $("#sale_person_type").val()
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
                if ($("#sale_person_type").val() == null) {
                    toastr["error"]("Please select sale person type first");
                }
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
        placeholder: 'Search for a reporting manager',
        dropdownParent: $("#modalUser .modal-body")
    });

    $("#purchase_person_reporting_manager").select2({
        ajax: {
            url: ajaxURLSearchPurchasePersonReportingManager,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    "purchase_person_type": function() {
                        return $("#purchase_person_type").val()
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
                if ($("#purchase_person_type").val() == null) {
                    toastr["error"]("Please select sale person type first");
                }
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
        placeholder: 'Search for a reporting manager',
        dropdownParent: $("#modalUser .modal-body")
    });

    $("#sale_person_state").select2({
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
        minimumInputLength: 1,
        dropdownParent: $("#modalUser .modal-body")
    });


    $("#purchase_person_state").select2({
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
        minimumInputLength: 1,
        dropdownParent: $("#modalUser .modal-body")
    });

    $("#tele_sales_state").select2({
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
        minimumInputLength: 1,
        dropdownParent: $("#modalUser .modal-body")
    });


    $("#sale_person_city").select2({

        ajax: {
            url: ajaxURLSearchStateCities,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    "sale_person_state": function() {
                        return $("#sale_person_state").val()
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
        minimumResultsForSearch: -1,
        dropdownParent: $("#modalUser .modal-body")

    });

    $("#purchase_person_city").select2({

        ajax: {
            url: ajaxURLSearchStateCities,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    "sale_person_state": function() {
                        return $("#purchase_person_state").val()
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
        minimumResultsForSearch: -1,
        dropdownParent: $("#modalUser .modal-body")

    });


    // AXONE WORK START
    $("#service_executive_type").select2({
        ajax: {
            url: ajaxURLSearchServiceExecutiveType,
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
        placeholder: 'Search for a service executive type',
        dropdownParent: $("#modalUser .modal-body")
    });

    $("#service_executive_reporting_manager").select2({
        ajax: {
            url: ajaxURLSearchServiceExecutiveReportingManager,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    "service_executive_type": function() {
                        return $("#service_executive_type").val()
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



                if ($("#service_executive_type").val() == null) {

                    toastr["error"]("Please select service user type first");

                }
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
        placeholder: 'Search for a reporting manager',
        dropdownParent: $("#modalUser .modal-body")
    });

    $("#service_executive_state").select2({
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
        minimumInputLength: 1,
        dropdownParent: $("#modalUser .modal-body")
    });

    $("#service_executive_city").select2({

        ajax: {
            url: ajaxURLSearchStateCities,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    "service_executive_state": function() {
                        return $("#service_executive_state").val()
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
        minimumResultsForSearch: -1,
        dropdownParent: $("#modalUser .modal-body")

    });


    $('#service_executive_state').on('change', function() {

        var selectedServiceState = $(this).val();
        if (selectedServiceState.length > 0 && editModeLoading == 0) {

            if (selectedServiceState.length > previousselectedServiceState.length) {


                // console.log("Add");
                // console.log(previousselectedServiceState);
                // console.log(selectedServiceState);

                var difference = [];

                for (i = 0; i < selectedServiceState.length; i++) {

                    if (!previousselectedServiceState.includes(selectedServiceState[i])) {

                        difference.push(selectedServiceState[i]);
                    }

                }


                // var difference = selectedServiceState.filter(x => previousselectedServiceState.indexOf(x) === -1);




                previousselectedServiceState = selectedServiceState;


                $.ajax({
                    url: ajaxURLStateCities + '?state_ids=' + difference.join(),
                    type: 'GET',
                    success: function(res) {
                        // $("#service_executive_city").empty().trigger('change');
                        var selectedSaleCities = [];

                        for (var i = 0; i < res['data'].length; i++) {

                            selectedSaleCities.push(res['data'][i]['id']);


                            var newOption = new Option(res['data'][i]['text'], res['data'][i]['id'],
                                false, false);
                            $('#service_executive_city').append(newOption).trigger('change');
                        }

                        var previousSelectedCites = $("#service_executive_city").val();


                        var selectedSaleCitiesALL = previousSelectedCites.concat(
                            selectedSaleCities);

                        $("#service_executive_city").val(selectedSaleCitiesALL).change();
                    }
                });
            } else if (selectedServiceState.length < previousselectedServiceState.length) {




                // var difference = previousselectedServiceState.filter(x => selectedServiceState.indexOf(x) === -1);

                var difference = [];

                for (i = 0; i < previousselectedServiceState.length; i++) {

                    if (!selectedServiceState.includes(previousselectedServiceState[i])) {

                        difference.push(previousselectedServiceState[i]);
                    }

                }





                previousselectedServiceState = selectedServiceState;


                $.ajax({
                    url: ajaxURLStateCities + '?state_ids=' + difference.join(),
                    type: 'GET',
                    success: function(res) {
                        // $("#service_executive_city").empty().trigger('change');
                        var notSelectedSaleCities = [];

                        for (var i = 0; i < res['data'].length; i++) {

                            notSelectedSaleCities.push(res['data'][i]['id']);
                        }


                        var previousSelectedCites = $("#service_executive_city").val();

                        for (i = 0; i < notSelectedSaleCities.length; i++) {

                            if (!previousSelectedCites.includes(notSelectedSaleCities[i])) {
                                var index = previousSelectedCites.indexOf('' +
                                    notSelectedSaleCities[i] + '');
                                if (index !== -1) {
                                    previousSelectedCites.splice(index, 1);
                                    $("#service_executive_city option[value='" +
                                        notSelectedSaleCities[i] + "']").remove();
                                }

                            }

                        }





                        $("#service_executive_city").val(previousSelectedCites).change();
                    }
                });




            }

        } else {

            previousselectedServiceState = [];

            $("#service_executive_city").empty().trigger('change');
            $("#service_executive_city").val([]).change();

        }





    });

    // AXONE WORK END
    $("#tele_sales_city").select2({

        ajax: {
            url: ajaxURLSearchStateCities,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    "sale_person_state": function() {
                        return $("#tele_sales_state").val()
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
        minimumResultsForSearch: -1,
        multiple: true,
        dropdownParent: $("#modalUser .modal-body")

    });

    $("#user_status").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#modalUser .modal-body")
    });


    $(document).ready(function() {


        //




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
        $("#btnSave").prop("disabled", true);
        return true;
    }

    // post-submit callback
    function showResponse(resultData, statusText, xhr, $form) {

        $("#btnSave").html("Save");
        $("#btnSave").prop("disabled", false);


        if (resultData['status'] == 1) {
            toastr["success"](resultData['msg']);
            $("#btnSave").html("Save");
            $("#btnSave").prop("disabled", false);
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

            $("#btnSave").html("Save");
            $("#btnSave").prop("disabled", false);

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

    $("#addBtnUser").click(function() {

        resetInputForm();

        $("#modalUserLabel").html("Add User");
        $("#user_id").val(0);
        $(".loadingcls").hide();
        $("#formUser .row").show();
        $("#modalUser .modal-footer").show();
        setTimeout(function() {
            $("#user_type").select2("val", "" + $("#user_type").val() + "");
            changeUserType($("#user_type").val());
        }, 100)


    });

    function resetInputForm() {

        $('#formUser').trigger("reset");
        $("#user_status").select2("val", "1");
        $("#user_country_id").select2("val", "1");
        $("#user_state_id").empty().trigger('change');
        $("#user_city_id").empty().trigger('change');
        // $("#user_company_id").empty().trigger('change');
        $("#sale_person_type").empty().trigger('change');
        $("#purchase_person_type").empty().trigger('change');
        $("#sale_person_reporting_manager").empty().trigger('change');
        $("#purchase_person_reporting_manager").empty().trigger('change');
        $("#sale_person_state").empty().trigger('change');
        $("#purchase_person_state").empty().trigger('change');
        $("#sale_person_city").empty().trigger('change');
        $("#purchase_person_city").empty().trigger('change');

        //AXONE WORK START
        $("#service_executive_type").empty().trigger('change');
        $("#service_executive_reporting_manager").empty().trigger('change');
        $("#service_executive_state").empty().trigger('change');
        $("#service_executive_city").empty().trigger('change');
        //AXONE WORK END

        $("#formUser").removeClass('was-validated');
        $('#div_user_type').show();
        $("#btnSave").html("Save");
        $("#btnSave").prop("disabled", false);
        previousselectedSaleState = [];



    }

    var editModeLoading = 0;
    var previousselectedSaleState = [];
    var previousselectedPurchaseState = [];

    function editView(id) {
        editModeLoading = 1;

        resetInputForm();

        $("#modalUser").modal('show');
        $("#modalUserLabel").html("Edit User #" + id);
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
                    $("#user_joining_date_value").val(resultData['data']['joining_date']);
                    // console.log(resultData['data']['status']);


                    $("#user_type").val(resultData['data']['type']);

                    $("#user_type").trigger("change");

                    $('#div_user_type').hide();

                    $("#user_status").select2("val", "" + resultData['data']['status'] + "");


                    if (typeof resultData['data']['country']['id'] !== "undefined") {
                        $("#user_country_id").select2("val", "" + resultData['data']['country']['id'] + "");

                    }


                    if (typeof resultData['data']['state']['id'] !== "undefined") {
                        $("#user_state_id").empty().trigger('change');
                        var newOption = new Option(resultData['data']['state']['name'], resultData['data'][
                            'state'
                        ]['id'], false, false);
                        $('#user_state_id').append(newOption).trigger('change');

                    }

                    if (typeof resultData['data']['city']['id'] !== "undefined") {
                        $("#user_city_id").empty().trigger('change');
                        var newOption = new Option(resultData['data']['city']['name'], resultData['data'][
                            'city'
                        ]['id'], false, false);
                        $('#user_city_id').append(newOption).trigger('change');

                    }






                    $(".loadingcls").hide();
                    $("#formUser .row").show();
                    $("#modalUser .modal-footer").show();
                    changeUserType(resultData['data']['type']);

                    if (resultData['data']['type'] == "0") {

                    } else if (resultData['data']['type'] == "1") {




                    } else if (resultData['data']['type'] == "2") {
                        if (typeof resultData['data']['sale_person']['type'] !== "undefined" && resultData[
                                'data']['sale_person']['type'] !== null && typeof resultData['data'][
                                'sale_person'
                            ]['type'] !== "undefined" && resultData['data']['sale_person']['type']['id']) {
                            $("#sale_person_type").empty().trigger('change');
                            var newOption = new Option(resultData['data']['sale_person']['type']['name'],
                                resultData['data']['sale_person']['type']['id'], false, false);
                            $('#sale_person_type').append(newOption).trigger('change');

                        }

                        if (typeof resultData['data']['sale_person']['reporting_manager'] !== "undefined" &&
                            resultData['data']['sale_person']['reporting_manager'] !== null &&
                            typeof resultData['data']['sale_person']['reporting_manager']['id'] !==
                            "undefined") {
                            $("#sale_person_reporting_manager").empty().trigger('change');
                            var newOption = new Option(resultData['data']['sale_person'][
                                'reporting_manager'
                            ]['text'], resultData['data']['sale_person'][
                                'reporting_manager'
                            ]['id'], false, false);
                            $('#sale_person_reporting_manager').append(newOption).trigger('change');

                        }

                        if (typeof resultData['data']['sale_person']['states'] !== "undefined") {

                            $("#sale_person_state").empty().trigger('change');
                            var selectedSaleState = [];

                            for (var i = 0; i < resultData['data']['sale_person']['states'].length; i++) {

                                selectedSaleState.push('' + resultData['data']['sale_person']['states'][i][
                                    'id'
                                ] + '');


                                var newOption = new Option(resultData['data']['sale_person']['states'][i][
                                        'text'
                                    ], resultData['data']['sale_person']['states'][i]['id'], false,
                                    false);
                                $('#sale_person_state').append(newOption).trigger('change');
                            }
                            $("#sale_person_state").val(selectedSaleState).change();
                            previousselectedSaleState = selectedSaleState;

                        }

                        if (typeof resultData['data']['sale_person']['cities'] !== "undefined") {

                            $("#sale_person_city").empty().trigger('change');
                            var selectedSaleCities = [];

                            for (var i = 0; i < resultData['data']['sale_person']['cities'].length; i++) {

                                selectedSaleCities.push('' + resultData['data']['sale_person']['cities'][i][
                                    'id'
                                ] + '');


                                var newOption = new Option(resultData['data']['sale_person']['cities'][i][
                                        'text'
                                    ], resultData['data']['sale_person']['cities'][i]['id'], false,
                                    false);
                                $('#sale_person_city').append(newOption).trigger('change');
                            }
                            $("#sale_person_city").val(selectedSaleCities).change();

                        }

                    } else if (resultData['data']['type'] == "3") {

                    } else if (resultData['data']['type'] == "4") {

                    } else if (resultData['data']['type'] == "5") {

                    } else if (resultData['data']['type'] == 9) {

                        if (typeof resultData['data']['tele_sales']['states'] !== "undefined") {

                            $("#tele_sales_state").empty().trigger('change');

                            var selectedSalestates = [];
                            for (var i = 0; i < resultData['data']['tele_sales']['states'].length; i++) {

                                selectedSalestates.push('' + resultData['data']['tele_sales']['states'][i][
                                    'id'
                                ] + '');
                                $('#tele_sales_state').append(
                                    `<option value="${resultData['data']['tele_sales']['states'][i]['id']}" selected>${resultData['data']['tele_sales']['states'][i]['text']}</option>`
                                );
                            }
                            $('#tele_sales_state').trigger('change');
                            $("#tele_sales_state").val(selectedSalestates).change();


                            // previousselectedSaleState = selectedSaleState;

                        }

                        if (typeof resultData['data']['tele_sales']['cities'] !== "undefined") {

                            $("#tele_sales_city").empty().trigger('change');

                            var selectedSaleCities = [];
                            for (var i = 0; i < resultData['data']['tele_sales']['cities'].length; i++) {

                                selectedSaleCities.push('' + resultData['data']['tele_sales']['cities'][i][
                                    'id'
                                ] + '');
                                $('#tele_sales_city').append(
                                    `<option value="${resultData['data']['tele_sales']['cities'][i]['id']}" selected>${resultData['data']['tele_sales']['cities'][i]['text']}</option>`
                                );
                            }
                            $('#tele_sales_city').trigger('change');
                            $("#tele_sales_city").val(selectedSaleCities).change();

                        }


                    } else if (resultData['data']['type'] == 10) {

                        if (typeof resultData['data']['purchase_person']['type'] !== "undefined" &&
                            resultData['data']['purchase_person']['type'] !== null && typeof resultData[
                                'data']['purchase_person']['type'] !== "undefined" && resultData['data'][
                                'purchase_person'
                            ]['type']['id']) {
                            $("#purchase_person_type").empty().trigger('change');
                            var newOption = new Option(resultData['data']['purchase_person']['type'][
                                    'name'
                                ], resultData['data']['purchase_person']['type']['id'], false,
                                false);
                            $('#purchase_person_type').append(newOption).trigger('change');

                            if (typeof resultData['data']['purchase_person']['reporting_manager'] !==
                                "undefined" && resultData['data']['purchase_person'][
                                    'reporting_manager'
                                ] !== null && typeof resultData['data'][
                                    'purchase_person'
                                ]['reporting_manager']['id'] !== "undefined") {
                                $("#purchase_person_reporting_manager").empty().trigger('change');
                                var newOption = new Option(resultData['data']['purchase_person'][
                                    'reporting_manager'
                                ]['text'], resultData['data']['purchase_person'][
                                    'reporting_manager'
                                ]['id'], false, false);
                                $('#purchase_person_reporting_manager').append(newOption).trigger('change');

                            }

                            if (typeof resultData['data']['purchase_person']['states'] !== "undefined") {

                                $("#purchase_person_state").empty().trigger('change');
                                var selectedSaleState = [];

                                for (var i = 0; i < resultData['data']['purchase_person']['states']
                                    .length; i++) {

                                    selectedSaleState.push('' + resultData['data']['purchase_person'][
                                        'states'
                                    ][i]['id'] + '');


                                    var newOption = new Option(resultData['data']['purchase_person'][
                                            'states'
                                        ][i]['text'], resultData['data']['purchase_person']['states'][i]
                                        ['id'], false, false);
                                    $('#purchase_person_state').append(newOption).trigger('change');
                                }
                                $("#purchase_person_state").val(selectedSaleState).change();
                                previousselectedSaleState = selectedSaleState;

                            }

                            if (typeof resultData['data']['purchase_person']['cities'] !== "undefined") {

                                $("#purchase_person_city").empty().trigger('change');
                                var selectedSaleCities = [];

                                for (var i = 0; i < resultData['data']['purchase_person']['cities']
                                    .length; i++) {

                                    selectedSaleCities.push('' + resultData['data']['purchase_person'][
                                        'cities'
                                    ][i]['id'] + '');


                                    var newOption = new Option(resultData['data']['purchase_person'][
                                            'cities'
                                        ][i]['text'], resultData['data']['purchase_person']['cities'][i]
                                        ['id'], false, false);
                                    $('#purchase_person_city').append(newOption).trigger('change');
                                }
                                $("#purchase_person_city").val(selectedSaleCities).change();

                            }

                        }

                    }
                    // START AXONE WORK
                    else if (resultData['data']['type'] == 11) {






                        if (typeof resultData['data']['service_person']['type'] !== "undefined" &&
                            resultData['data']['service_person']['type'] !== null && typeof resultData[
                                'data']['service_person']['type'] !== "undefined" && resultData['data'][
                                'service_person'
                            ]['type']['id']) {
                            $("#service_executive_type").empty().trigger('change');
                            var newOption = new Option(resultData['data']['service_person']['type']['name'],
                                resultData['data']['service_person']['type']['id'], false, false);
                            $('#service_executive_type').append(newOption).trigger('change');

                        }



                        if (typeof resultData['data']['service_person']['reporting_manager'] !==
                            "undefined" && resultData['data']['service_person']['reporting_manager'] !==
                            null && typeof resultData['data']['service_person']['reporting_manager'][
                                'id'
                            ] !== "undefined") {
                            $("#service_executive_reporting_manager").empty().trigger('change');
                            var newOption = new Option(resultData['data']['service_person'][
                                'reporting_manager'
                            ]['text'], resultData['data']['service_person']['reporting_manager'][
                                'id'
                            ], false, false);
                            $('#service_executive_reporting_manager').append(newOption).trigger('change');

                        }


                        if (typeof resultData['data']['service_person']['states'] !== "undefined") {

                            $("#service_executive_state").empty().trigger('change');
                            var selectedSaleState = [];

                            for (var i = 0; i < resultData['data']['service_person']['states']
                                .length; i++) {

                                selectedSaleState.push('' + resultData['data']['service_person']['states'][
                                    i
                                ]['id'] + '');


                                var newOption = new Option(resultData['data']['service_person']['states'][i]
                                    ['text'], resultData['data']['service_person']['states'][i]['id'],
                                    false, false);
                                $('#service_executive_state').append(newOption).trigger('change');
                            }
                            $("#service_executive_state").val(selectedSaleState).change();
                            previousselectedSaleState = selectedSaleState;

                        }

                        if (typeof resultData['data']['service_person']['cities'] !== "undefined") {

                            $("#service_executive_city").empty().trigger('change');
                            var selectedSaleCities = [];

                            for (var i = 0; i < resultData['data']['service_person']['cities']
                                .length; i++) {

                                selectedSaleCities.push('' + resultData['data']['service_person']['cities'][
                                    i
                                ]['id'] + '');


                                var newOption = new Option(resultData['data']['service_person']['cities'][i]
                                    ['text'], resultData['data']['service_person']['cities'][i]['id'],
                                    false, false);
                                $('#service_executive_city').append(newOption).trigger('change');
                            }
                            $("#service_executive_city").val(selectedSaleCities).change();

                        }

                    } else if (resultData['data']['type'] == "12") {

                    } else if (resultData['data']['type'] == "13") {

                    }
                    // END AXONE WORK


                    editModeLoading = 0;


                } else {

                    toastr["error"](resultData['msg']);

                }

            }
        });

    }

    $('#user_country_id').on('change', function() {

        $("#user_state_id").empty().trigger('change');
        $("#user_city_id").empty().trigger('change');

    });

    $('#user_state_id').on('change', function() {

        $("#user_city_id").empty().trigger('change');

    });


    $('#sale_person_state').on('change', function() {

        var selectedSaleState = $(this).val();
        if (selectedSaleState.length > 0 && editModeLoading == 0) {

            if (selectedSaleState.length > previousselectedSaleState.length) {


                // console.log("Add");
                // console.log(previousselectedSaleState);
                // console.log(selectedSaleState);

                var difference = [];

                for (i = 0; i < selectedSaleState.length; i++) {

                    if (!previousselectedSaleState.includes(selectedSaleState[i])) {

                        difference.push(selectedSaleState[i]);
                    }

                }


                // var difference = selectedSaleState.filter(x => previousselectedSaleState.indexOf(x) === -1);




                previousselectedSaleState = selectedSaleState;


                $.ajax({
                    url: ajaxURLStateCities + '?state_ids=' + difference.join(),
                    type: 'GET',
                    success: function(res) {
                        // $("#sale_person_city").empty().trigger('change');
                        var selectedSaleCities = [];

                        for (var i = 0; i < res['data'].length; i++) {

                            selectedSaleCities.push(res['data'][i]['id']);


                            $('#tele_sales_city').append(`<option value="${res['data'][i]['id']}" selected>
                                       ${res['data'][i]['text']}
                                  </option>`);
                        }
                        $('#sale_person_city').trigger('change');

                        var previousSelectedCites = $("#sale_person_city").val();


                        var selectedSaleCitiesALL = previousSelectedCites.concat(
                            selectedSaleCities);

                        $("#sale_person_city").val(selectedSaleCitiesALL).change();
                    }
                });
            } else if (selectedSaleState.length < previousselectedSaleState.length) {




                // var difference = previousselectedSaleState.filter(x => selectedSaleState.indexOf(x) === -1);

                var difference = [];

                for (i = 0; i < previousselectedSaleState.length; i++) {

                    if (!selectedSaleState.includes(previousselectedSaleState[i])) {

                        difference.push(previousselectedSaleState[i]);
                    }

                }





                previousselectedSaleState = selectedSaleState;


                $.ajax({
                    url: ajaxURLStateCities + '?state_ids=' + difference.join(),
                    type: 'GET',
                    success: function(res) {
                        // $("#sale_person_city").empty().trigger('change');
                        var notSelectedSaleCities = [];

                        for (var i = 0; i < res['data'].length; i++) {

                            notSelectedSaleCities.push(res['data'][i]['id']);
                        }


                        var previousSelectedCites = $("#sale_person_city").val();

                        for (i = 0; i < notSelectedSaleCities.length; i++) {

                            if (!previousSelectedCites.includes(notSelectedSaleCities[i])) {
                                var index = previousSelectedCites.indexOf('' +
                                    notSelectedSaleCities[i] + '');
                                if (index !== -1) {
                                    previousSelectedCites.splice(index, 1);
                                    $("#sale_person_city option[value='" + notSelectedSaleCities[
                                        i] + "']").remove();
                                }

                            }

                        }




                        $("#sale_person_city").val(previousSelectedCites).change();
                    }
                });




            }

        } else {

            previousselectedSaleState = [];

            $("#sale_person_city").empty().trigger('change');
            $("#sale_person_city").val([]).change();


        }





    });



    $('#purchase_person_state').on('change', function() {

        var selectedPurchaseState = $(this).val();
        if (selectedPurchaseState.length > 0 && editModeLoading == 0) {

            if (selectedPurchaseState.length > previousselectedPurchaseState.length) {


                // console.log("Add");
                // console.log(previousselectedSaleState);
                // console.log(selectedSaleState);

                var difference = [];

                for (i = 0; i < selectedPurchaseState.length; i++) {

                    if (!previousselectedPurchaseState.includes(selectedPurchaseState[i])) {

                        difference.push(selectedPurchaseState[i]);
                    }

                }


                // var difference = selectedSaleState.filter(x => previousselectedSaleState.indexOf(x) === -1);




                previousselectedPurchaseState = selectedPurchaseState;


                $.ajax({
                    url: ajaxURLStateCities + '?state_ids=' + difference.join(),
                    type: 'GET',
                    success: function(res) {
                        // $("#sale_person_city").empty().trigger('change');
                        var selectedPurchaseCities = [];

                        for (var i = 0; i < res['data'].length; i++) {

                            selectedPurchaseCities.push(res['data'][i]['id']);


                            $('#tele_sales_city').append(`<option value="${res['data'][i]['id']}" selected>
                                       ${res['data'][i]['text']}
                                  </option>`);
                        }
                        $('#purchase_person_city').trigger('change');

                        var previousSelectedCites = $("#purchase_person_city").val();


                        var selectedPurchaseCitiesALL = previousSelectedCites.concat(
                            selectedPurchaseCities);

                        $("#purchase_person_city").val(selectedPurchaseCitiesALL).change();
                    }
                });
            } else if (selectedPurchaseState.length < previousselectedPurchaseState.length) {




                // var difference = previousselectedSaleState.filter(x => selectedSaleState.indexOf(x) === -1);

                var difference = [];

                for (i = 0; i < previousselectedPurchaseState.length; i++) {

                    if (!selectedSaleState.includes(previousselectedPurchaseState[i])) {

                        difference.push(previousselectedPurchaseState[i]);
                    }

                }





                previousselectedPurchaseState = selectedPurchaseState;


                $.ajax({
                    url: ajaxURLStateCities + '?state_ids=' + difference.join(),
                    type: 'GET',
                    success: function(res) {
                        // $("#sale_person_city").empty().trigger('change');
                        var notSelectedSaleCities = [];

                        for (var i = 0; i < res['data'].length; i++) {

                            notSelectedPurchaseCities.push(res['data'][i]['id']);
                        }


                        var previousSelectedCites = $("#purchase_person_city").val();

                        for (i = 0; i < notSelectedPurchaseCities.length; i++) {

                            if (!previousSelectedCites.includes(notSelectedPurchaseCities[i])) {
                                var index = previousSelectedCites.indexOf('' +
                                    notSelectedPurchaseCities[i] + '');
                                if (index !== -1) {
                                    previousSelectedCites.splice(index, 1);
                                    $("#purchase_person_city option[value='" +
                                        notSelectedPurchaseCities[i] + "']").remove();
                                }

                            }

                        }




                        $("#purchase_person_city").val(previousSelectedCites).change();
                    }
                });




            }

        } else {

            previousselectedPurchaseState = [];

            $("#purchase_person_city").empty().trigger('change');
            $("#purchase_person_city").val([]).change();


        }





    });







    $('#tele_sales_state').on('change', function() {
        var selectedSaleState = $(this).val();
        if (selectedSaleState.length > 0 && editModeLoading == 0) {
            if (selectedSaleState.length > previousselectedSaleState.length) {
                console.log("Add");
                // console.log(previousselectedSaleState);
                // console.log(selectedSaleState);
                var difference = [];
                for (i = 0; i < selectedSaleState.length; i++) {
                    if (!previousselectedSaleState.includes(selectedSaleState[i])) {
                        difference.push(selectedSaleState[i]);
                    }
                }
                // var difference = selectedSaleState.filter(x => previousselectedSaleState.indexOf(x) === -1);
                previousselectedSaleState = selectedSaleState;
                $.ajax({
                    url: ajaxURLStateCities + '?state_ids=' + difference.join(),
                    type: 'GET',
                    success: function(res) {
                        // $("#sale_person_city").empty().trigger('change');
                        var selectedSaleCities = [];
                        for (var i = 0; i < res['data'].length; i++) {
                            selectedSaleCities.push(res['data'][i]['id']);
                            $('#tele_sales_city').append(`<option value="${res['data'][i]['id']}" selected>
                                       ${res['data'][i]['text']}
                                  </option>`);
                        }
                        $('#tele_sales_city').trigger('change');
                        var previousSelectedCites = $("#tele_sales_city").val();

                        var selectedSaleCitiesALL = previousSelectedCites.concat(
                            selectedSaleCities);
                        $("#tele_sales_city").val(selectedSaleCitiesALL).change();
                    }
                });
            } else if (selectedSaleState.length < previousselectedSaleState.length) {
                // var difference = previousselectedSaleState.filter(x => selectedSaleState.indexOf(x) === -1);
                var difference = [];
                for (i = 0; i < previousselectedSaleState.length; i++) {
                    if (!selectedSaleState.includes(previousselectedSaleState[i])) {
                        difference.push(previousselectedSaleState[i]);
                    }
                }
                previousselectedSaleState = selectedSaleState;
                $.ajax({
                    url: ajaxURLStateCities + '?state_ids=' + difference.join(),
                    type: 'GET',
                    success: function(res) {
                        // $("#sale_person_city").empty().trigger('change');
                        var notSelectedSaleCities = [];
                        for (var i = 0; i < res['data'].length; i++) {
                            notSelectedSaleCities.push(res['data'][i]['id']);
                        }
                        var previousSelectedCites = $("#tele_sales_city").val();
                        for (i = 0; i < notSelectedSaleCities.length; i++) {

                            if (!previousSelectedCites.includes(notSelectedSaleCities[i])) {
                                var index = previousSelectedCites.indexOf('' +
                                    notSelectedSaleCities[i] + '');
                                if (index !== -1) {
                                    previousSelectedCites.splice(index, 1);
                                    $("#tele_sales_city option[value='" + notSelectedSaleCities[i] +
                                        "']").remove();
                                }
                            }
                        }
                        $("#tele_sales_city").val(previousSelectedCites).change();
                    }
                });
            }
        } else {
            previousselectedSaleState = [];
            $("#tele_sales_city").empty().trigger('change');
            $("#tele_sales_city").val([]).change();
        }
    });

    $('#user_type').on('change', function() {

        changeUserType($(this).val());


    });

    function changeUserType(userType) {

        if (userType == "0") {



            $(".sec_user_sale_person").hide();
            $(".sec_user_purchase_person").hide();
            $("#sale_person_type").removeAttr('required');
            $("#sale_person_state").removeAttr('required');
            $("#sale_person_city").removeAttr('required');
            $("#sale_person_reporting_manager").removeAttr('required');


            $("#purchase_person_type").removeAttr('required');
            $("#purchase_person_state").removeAttr('required');
            $("#purchase_person_city").removeAttr('required');
            $("#purchase_person_reporting_manager").removeAttr('required');

            // START AXONE WORK
            $(".sec_user_service_executive").hide();
            $("#service_executive_type").removeAttr('required');
            $("#service_executive_state").removeAttr('required');
            $("#service_executive_city").removeAttr('required');
            $("#service_executive_reporting_manager").removeAttr('required');
            // END AXONE WORK

            $(".sec_user_tele_sales").hide();
            $("#tele_sales_state").removeAttr('required');
            $("#tele_sales_city").removeAttr('required');

        } else if (userType == "1") {


            $(".sec_user_sale_person").hide();
            $(".sec_user_purchase_person").hide();
            $("#sale_person_type").removeAttr('required');
            $("#sale_person_state").removeAttr('required');
            $("#sale_person_city").removeAttr('required');
            $("#sale_person_reporting_manager").removeAttr('required');

            $("#purchase_person_type").removeAttr('required');
            $("#purchase_person_state").removeAttr('required');
            $("#purchase_person_city").removeAttr('required');
            $("#purchase_person_reporting_manager").removeAttr('required');

            // START AXONE WORK
            $(".sec_user_service_executive").hide();
            $("#service_executive_type").removeAttr('required');
            $("#service_executive_state").removeAttr('required');
            $("#service_executive_city").removeAttr('required');
            $("#service_executive_reporting_manager").removeAttr('required');
            // END AXONE WORK

            $(".sec_user_tele_sales").hide();
            $("#tele_sales_state").removeAttr('required');
            $("#tele_sales_city").removeAttr('required');


        } else if (userType == "2") {


            $(".sec_user_sale_person").show();
            $(".sec_user_purchase_person").hide();

            $("#sale_person_type").attr('required', true);
            $("#sale_person_state").attr('required', true);
            $("#sale_person_city").attr('required', true);
            $("#sale_person_reporting_manager").attr('required', true);


            $("#purchase_person_type").removeAttr('required');
            $("#purchase_person_state").removeAttr('required');
            $("#purchase_person_city").removeAttr('required');
            $("#purchase_person_reporting_manager").removeAttr('required');

            // START AXONE WORK
            $(".sec_user_service_executive").hide();
            $("#service_executive_type").removeAttr('required');
            $("#service_executive_state").removeAttr('required');
            $("#service_executive_city").removeAttr('required');
            $("#service_executive_reporting_manager").removeAttr('required');
            // END AXONE WORK

            $(".sec_user_tele_sales").hide();
            $("#tele_sales_state").removeAttr('required');
            $("#tele_sales_city").removeAttr('required');

        } else if (userType == "3") {

            if (isChannelPartner == 0) {




                $(".sec_user_sale_person").hide();
                $(".sec_user_purchase_person").hide();
                $("#sale_person_type").removeAttr('required');
                $("#sale_person_state").removeAttr('required');
                $("#sale_person_city").removeAttr('required');
                $("#sale_person_reporting_manager").removeAttr('required');

                $("#purchase_person_type").removeAttr('required');
                $("#purchase_person_state").removeAttr('required');
                $("#purchase_person_city").removeAttr('required');
                $("#purchase_person_reporting_manager").removeAttr('required');

                // START AXONE WORK
                $(".sec_user_service_executive").hide();
                $("#service_executive_type").removeAttr('required');
                $("#service_executive_state").removeAttr('required');
                $("#service_executive_city").removeAttr('required');
                $("#service_executive_reporting_manager").removeAttr('required');
                // END AXONE WORK

                $(".sec_user_tele_sales").hide();
                $("#tele_sales_state").removeAttr('required');
                $("#tele_sales_city").removeAttr('required');

            } else {


                $(".sec_user_sale_person").hide();
                $(".sec_user_purchase_person").hide();
                $("#sale_person_type").removeAttr('required');
                $("#sale_person_state").removeAttr('required');
                $("#sale_person_city").removeAttr('required');
                $("#sale_person_reporting_manager").removeAttr('required');

                $("#purchase_person_type").removeAttr('required');
                $("#purchase_person_state").removeAttr('required');
                $("#purchase_person_city").removeAttr('required');
                $("#purchase_person_reporting_manager").removeAttr('required');

                // START AXONE WORK
                $(".sec_user_service_executive").hide();
                $("#service_executive_type").removeAttr('required');
                $("#service_executive_state").removeAttr('required');
                $("#service_executive_city").removeAttr('required');
                $("#service_executive_reporting_manager").removeAttr('required');
                // END AXONE WORK

                $(".sec_user_tele_sales").hide();
                $("#tele_sales_state").removeAttr('required');
                $("#tele_sales_city").removeAttr('required');

            }



        } else if (userType == "4") {

            if (isChannelPartner == 0) {


                $(".sec_user_sale_person").hide();
                $(".sec_user_purchase_person").hide();
                $("#sale_person_type").removeAttr('required');
                $("#sale_person_state").removeAttr('required');
                $("#sale_person_city").removeAttr('required');
                $("#sale_person_reporting_manager").removeAttr('required');

                $("#purchase_person_type").removeAttr('required');
                $("#purchase_person_state").removeAttr('required');
                $("#purchase_person_city").removeAttr('required');
                $("#purchase_person_reporting_manager").removeAttr('required');

                // START AXONE WORK
                $(".sec_user_service_executive").hide();
                $("#service_executive_type").removeAttr('required');
                $("#service_executive_state").removeAttr('required');
                $("#service_executive_city").removeAttr('required');
                $("#service_executive_reporting_manager").removeAttr('required');
                // END AXONE WORK


                $(".sec_user_tele_sales").hide();
                $("#tele_sales_state").removeAttr('required');
                $("#tele_sales_city").removeAttr('required');
            } else {


                $(".sec_user_sale_person").hide();
                $(".sec_user_purchase_person").hide();
                $("#sale_person_type").removeAttr('required');
                $("#sale_person_state").removeAttr('required');
                $("#sale_person_city").removeAttr('required');
                $("#sale_person_reporting_manager").removeAttr('required');

                $("#purchase_person_type").removeAttr('required');
                $("#purchase_person_state").removeAttr('required');
                $("#purchase_person_city").removeAttr('required');
                $("#purchase_person_reporting_manager").removeAttr('required');

                // START AXONE WORK
                $(".sec_user_service_executive").hide();
                $("#service_executive_type").removeAttr('required');
                $("#service_executive_state").removeAttr('required');
                $("#service_executive_city").removeAttr('required');
                $("#service_executive_reporting_manager").removeAttr('required');
                // END AXONE WORK


                $(".sec_user_tele_sales").hide();
                $("#tele_sales_state").removeAttr('required');
                $("#tele_sales_city").removeAttr('required');

            }


        } else if (userType == "5") {




            $(".sec_user_sale_person").hide();
            $(".sec_user_purchase_person").hide();
            $("#sale_person_type").removeAttr('required');
            $("#sale_person_state").removeAttr('required');
            $("#sale_person_city").removeAttr('required');
            $("#sale_person_reporting_manager").removeAttr('required');

            $("#purchase_person_type").removeAttr('required');
            $("#purchase_person_state").removeAttr('required');
            $("#purchase_person_city").removeAttr('required');
            $("#purchase_person_reporting_manager").removeAttr('required');

            // START AXONE WORK
            $(".sec_user_service_executive").hide();
            $("#service_executive_type").removeAttr('required');
            $("#service_executive_state").removeAttr('required');
            $("#service_executive_city").removeAttr('required');
            $("#service_executive_reporting_manager").removeAttr('required');
            // END AXONE WORK


            $(".sec_user_tele_sales").hide();
            $("#tele_sales_state").removeAttr('required');
            $("#tele_sales_city").removeAttr('required');


        } else if (userType == "6") {

            $(".sec_user_sale_person").hide();
            $(".sec_user_purchase_person").hide();
            $("#sale_person_type").removeAttr('required');
            $("#sale_person_state").removeAttr('required');
            $("#sale_person_city").removeAttr('required');
            $("#sale_person_reporting_manager").removeAttr('required');

            $("#purchase_person_type").removeAttr('required');
            $("#purchase_person_state").removeAttr('required');
            $("#purchase_person_city").removeAttr('required');
            $("#purchase_person_reporting_manager").removeAttr('required');

            // START AXONE WORK
            $(".sec_user_service_executive").hide();
            $("#service_executive_type").removeAttr('required');
            $("#service_executive_state").removeAttr('required');
            $("#service_executive_city").removeAttr('required');
            $("#service_executive_reporting_manager").removeAttr('required');
            // END AXONE WORK


            $(".sec_user_tele_sales").hide();
            $("#tele_sales_state").removeAttr('required');
            $("#tele_sales_city").removeAttr('required');

        } else if (userType == "7") {

            $(".sec_user_sale_person").hide();
            $(".sec_user_purchase_person").hide();
            $("#sale_person_type").removeAttr('required');
            $("#sale_person_state").removeAttr('required');
            $("#sale_person_city").removeAttr('required');
            $("#sale_person_reporting_manager").removeAttr('required');

            $("#purchase_person_type").removeAttr('required');
            $("#purchase_person_state").removeAttr('required');
            $("#purchase_person_city").removeAttr('required');
            $("#purchase_person_reporting_manager").removeAttr('required');

            // START AXONE WORK
            $(".sec_user_service_executive").hide();
            $("#service_executive_type").removeAttr('required');
            $("#service_executive_state").removeAttr('required');
            $("#service_executive_city").removeAttr('required');
            $("#service_executive_reporting_manager").removeAttr('required');
            // END AXONE WORK

            $(".sec_user_tele_sales").hide();
            $("#tele_sales_state").removeAttr('required');
            $("#tele_sales_city").removeAttr('required');

        } else if (userType == "8") {

            $(".sec_user_sale_person").hide();
            $(".sec_user_purchase_person").hide();
            $("#sale_person_type").removeAttr('required');
            $("#sale_person_state").removeAttr('required');
            $("#sale_person_city").removeAttr('required');
            $("#sale_person_reporting_manager").removeAttr('required');

            $("#purchase_person_type").removeAttr('required');
            $("#purchase_person_state").removeAttr('required');
            $("#purchase_person_city").removeAttr('required');
            $("#purchase_person_reporting_manager").removeAttr('required');

            // START AXONE WORK
            $(".sec_user_service_executive").hide();
            $("#service_executive_type").removeAttr('required');
            $("#service_executive_state").removeAttr('required');
            $("#service_executive_city").removeAttr('required');
            $("#service_executive_reporting_manager").removeAttr('required');
            // END AXONE WORK

            $(".sec_user_tele_sales").hide();
            $("#tele_sales_state").removeAttr('required');
            $("#tele_sales_city").removeAttr('required');

        } else if (userType == "9") {

            $(".sec_user_sale_person").hide();
            $(".sec_user_purchase_person").hide();
            $("#sale_person_type").removeAttr('required');
            $("#sale_person_state").removeAttr('required');
            $("#sale_person_city").removeAttr('required');
            $("#sale_person_reporting_manager").removeAttr('required');

            $("#purchase_person_type").removeAttr('required');
            $("#purchase_person_state").removeAttr('required');
            $("#purchase_person_city").removeAttr('required');
            $("#purchase_person_reporting_manager").removeAttr('required');

            // START AXONE WORK
            $(".sec_user_service_executive").hide();
            $("#service_executive_type").removeAttr('required');
            $("#service_executive_state").removeAttr('required');
            $("#service_executive_city").removeAttr('required');
            $("#service_executive_reporting_manager").removeAttr('required');
            // END AXONE WORK

            $(".sec_user_tele_sales").show();
            $("#tele_sales_state").attr('required', true);
            $("#tele_sales_city").attr('required', true);

        } else if (userType == "10") {

            $(".sec_user_purchase_person").show();
            $(".sec_user_sale_person").hide();

            $("#purchase_person_type").attr('required', true);
            $("#purchase_person_state").attr('required', true);
            $("#purchase_person_city").attr('required', true);
            $("#purchase_person_reporting_manager").attr('required', true);


            $("#purchase_person_type").attr('required', true);
            $("#purchase_person_state").attr('required', true);
            $("#purchase_person_city").attr('required', true);
            $("#purchase_person_reporting_manager").attr('required', true);

            // START AXONE WORK
            $(".sec_user_service_executive").hide();
            $("#service_executive_type").removeAttr('required');
            $("#service_executive_state").removeAttr('required');
            $("#service_executive_city").removeAttr('required');
            $("#service_executive_reporting_manager").removeAttr('required');
            // END AXONE WORK

            $(".sec_user_tele_sales").hide();
            $("#tele_sales_state").removeAttr('required');
            $("#tele_sales_city").removeAttr('required');

        }
        // START AXONE WORK
        else if (userType == "11") {


            $(".sec_user_sale_person").hide();
            $(".sec_user_purchase_person").hide();

            $("#sale_person_type").removeAttr('required');
            $("#sale_person_state").removeAttr('required');
            $("#sale_person_city").removeAttr('required');
            $("#sale_person_reporting_manager").removeAttr('required');

            $("#service_executive_type").attr('required', true);
            $(".sec_user_service_executive").show();
            $("#service_executive_state").attr('required', true);
            $("#service_executive_city").attr('required', true);
            $("#service_executive_reporting_manager").attr('required', true);

            $("#purchase_person_type").removeAttr('required');
            $("#purchase_person_state").removeAttr('required');
            $("#purchase_person_city").removeAttr('required');
            $("#purchase_person_reporting_manager").removeAttr('required');

            $(".sec_user_tele_sales").hide();
            $("#tele_sales_state").removeAttr('required');
            $("#tele_sales_city").removeAttr('required');

        } else if (userType == "12") {

            $(".sec_user_sale_person").hide();
            $(".sec_user_purchase_person").hide();
            $("#sale_person_type").removeAttr('required');
            $("#sale_person_state").removeAttr('required');
            $("#sale_person_city").removeAttr('required');
            $("#sale_person_reporting_manager").removeAttr('required');


            $("#purchase_person_type").removeAttr('required');
            $("#purchase_person_state").removeAttr('required');
            $("#purchase_person_city").removeAttr('required');
            $("#purchase_person_reporting_manager").removeAttr('required');

            // START AXONE WORK
            $(".sec_user_service_executive").hide();
            $("#service_executive_type").removeAttr('required');
            $("#service_executive_state").removeAttr('required');
            $("#service_executive_city").removeAttr('required');
            $("#service_executive_reporting_manager").removeAttr('required');
            // END AXONE WORK

            $(".sec_user_tele_sales").hide();
            $("#tele_sales_state").removeAttr('required');
            $("#tele_sales_city").removeAttr('required');

        } else if (userType == "13") {

            $(".sec_user_sale_person").hide();
            $(".sec_user_purchase_person").hide();
            $("#sale_person_type").removeAttr('required');
            $("#sale_person_state").removeAttr('required');
            $("#sale_person_city").removeAttr('required');
            $("#sale_person_reporting_manager").removeAttr('required');


            $("#purchase_person_type").removeAttr('required');
            $("#purchase_person_state").removeAttr('required');
            $("#purchase_person_city").removeAttr('required');
            $("#purchase_person_reporting_manager").removeAttr('required');

            // START AXONE WORK
            $(".sec_user_service_executive").hide();
            $("#service_executive_type").removeAttr('required');
            $("#service_executive_state").removeAttr('required');
            $("#service_executive_city").removeAttr('required');
            $("#service_executive_reporting_manager").removeAttr('required');
            // END AXONE WORK

            $(".sec_user_tele_sales").hide();
            $("#tele_sales_state").removeAttr('required');
            $("#tele_sales_city").removeAttr('required');

        }

        // END AXONE WORK

    }
</script>
