<div class="modal fade show" id="modalWarehouse" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" role="dialog" aria-labelledby="modalWarehouseLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalWarehouseLabel">WareHouse</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formWarehouse" action="{{ route('service.warehouse.master.save') }}" method="POST" class="needs-validation" novalidate>
                <div class="col-md-12 text-center loadingcls">
                    <button type="button" class="btn btn-light waves-effect">
                        <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading
                    </button>
                </div>
                <div class="modal-body" id="formwerehouse_field">

                    @csrf
                    <input type="hidden" name="warehouse_id" id="warehouse_id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="warehouse_name" class="form-label">Warehouse name<code class="highlighter-rouge">*</code></label>
                                <input type="text" class="form-control" id="warehouse_name" name="warehouse_name" placeholder="First Name" value="" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="warehouse_address_line1" class="form-label">Address line 1<code class="highlighter-rouge">*</code></label>
                                <input type="text" class="form-control" id="warehouse_address_line1" name="warehouse_address_line1" placeholder="Address line 1" value="" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="warehouse_address_line2" class="form-label">Address line 2</label>
                                <input type="text" class="form-control" id="warehouse_address_line2" name="warehouse_address_line2" placeholder="Address line 2" value="">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                <label for="warehouse_country_id" class="form-label">Country <code class="highlighter-rouge">*</code></label>
                                <select class="form-control select2-ajax" id="warehouse_country_id" name="warehouse_country_id" required>
                                </select>
                                <div class="invalid-feedback">
                                    Please select country.
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                <label class="form-label">State <code class="highlighter-rouge">*</code></label>
                                <select class="form-control select2-ajax" id="warehouse_state_id" name="warehouse_state_id" required>
                                </select>
                                <div class="invalid-feedback">
                                    Please select state.
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                <label class="form-label">City <code class="highlighter-rouge">*</code></label>
                                <select class="form-control select2-ajax" id="warehouse_city_id" name="warehouse_city_id" required>
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
                                <label for="warehouse_pincode" class="form-label">Pincode <code class="highlighter-rouge">*</code></label>
                                <input type="text" class="form-control" id="warehouse_pincode" name="warehouse_pincode" placeholder="Pincode" value="" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="warehouse_status" class="form-label">Status</label>
                                <select class="form-select" id="warehouse_status" name="warehouse_status">
                                    <option selected value="1">Active</option>
                                    <option value="0">Inactive</option>
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
                                <label for="warehouse_remark" class="form-label">Remark </label>
                                <textarea name="warehouse_remark" id="warehouse_remark" rows="3" class="form-control" required></textarea>
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

<script type="text/javascript">
    var ajaxURLSearchCountry = '{{route("service.warehouse.search.country") }}';
    var ajaxURLSearchState = '{{ route("service.warehouse.search.state") }}';
    var ajaxURLSearchCity = '{{ route("service.warehouse.search.city") }}';

    $("#warehouse_country_id").select2({
        ajax: {
            url: ajaxURLSearchCountry,
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
        placeholder: 'Search for a country',
        dropdownParent: $("#modalWarehouse .modal-body"),
    });

    $("#warehouse_city_id").select2({
        ajax: {
            url: ajaxURLSearchCity,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    "country_id": function() {
                        return $("#warehouse_country_id").val()
                    },
                    "state_id": function() {
                        return $("#warehouse_state_id").val()
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
        dropdownParent: $("#modalWarehouse .modal-body")
    });

    $("#warehouse_state_id").select2({
        ajax: {
            url: ajaxURLSearchState,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    "country_id": function() {
                        return $("#warehouse_country_id").val()
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
        dropdownParent: $("#modalWarehouse .modal-body")
    });

    $("#warehouse_status").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#modalWarehouse .modal-body")
    });

    $("#addBtnWareHouseMaster").click(function() {
        resetInputForm();
        $("#modalWarehouseLabel").html("Add Warehouse");
        $(".loadingcls").hide();
        $('#modalWarehouse').modal("show");
    });

    function resetInputForm() {

        $('#formWarehouse').trigger("reset");
        $("#warehouse_id").val(0);
        $("#warehouse_country_id").select2("val", "1");
        $("#warehouse_state_id").select2("val", "1");
        $("#warehouse_city_id").select2("val", "1");
        $("#warehouse_status").select2("val", "1");
    }

    $('#warehouse_country_id').on('change', function() {
        $("#user_state_id").empty().trigger('change');
        $("#user_city_id").empty().trigger('change');
    });

    $('#warehouse_state_id').on('change', function() {
        $("#user_city_id").empty().trigger('change');
    });
</script>