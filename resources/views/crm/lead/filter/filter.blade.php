<div class="dropdown d-inline-block">
    <button data-bs-auto-close="outside" type="button" class="btn btn-outline-secondary waves-effect advance-filter-btn"
        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="bx bx-filter-alt "></i> Filter
        <span class="badge bg-danger rounded-pill" id="isfiltercount" style="display: none;"></span>
    </button>
    <div class="dropdown-menu dropdown-menu-end dropdown-menu-advancefilter p-0" id="filterdropdownmodel">
        <div class="card-header d-flex align-items-center justify-content-between bg-white">
            <div class="text-center">
                <h4 class="card-title mb-0">Advanced filters
                    <span style="color: gray;font-style: normal;font-size: 11px;" id="advanceFilterInfo">(no.of filter :
                        1)</span>

                </h4>
            </div>
            <div>
                <button type="button" class="btn btn-sm btn-primary ms-2" id="saveviewfilter">Save As View</button>
                <button type="button" id="btnClearAdvanceFilter" class="btn btn-sm btn-danger">Clear All</button>
                <button type="button" class="btn btn-sm btn-primary" id="saveAdvanceFilter">Save</button>
            </div>
        </div>
        <div class="card mb-0">
            <div class="card-body py-0" style="max-height: 400px;overflow-y: auto;">
                <div class="d-flex align-items-center">

                    <div class="row flex-nowrap align-items-center flex-fill">
                        <div class="col-2 ps-0">
                            <div class="mb-1 mt-lg-0 pe-none">
                                <select class="form-control " id="selectAdvanceFilterClause_0"
                                    name="selectAdvanceFilterClause_0" required>
                                    <option value="0">WHERE</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-3 ps-0">
                            <div class="mb-1 mt-lg-0">
                                <select class="form-control" id="selectAdvanceFilterColumn_0"
                                    name="selectAdvanceFilterColumn_0" required>
                                    @foreach (getFilterColumnCRM() as $filt)
                                        <option value="{{ $filt['id'] }}">{{ $filt['name'] }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">
                                    Please select Column.
                                </div>
                            </div>
                        </div>
                        <div class="col-3 ps-0">
                            <div class="mb-1 mt-lg-0">
                                <select class="form-control" id="selectAdvanceFilterCondtion_0"
                                    name="selectAdvanceFilterCondtion_0" required>
                                </select>
                                <div class="invalid-feedback">
                                    Please select Condtion.
                                </div>
                            </div>
                        </div>
                        <div class="col-4 ps-0">
                            <div class="mb-1 mt-lg-0" id="lead_filter_div_source_type_0" style="display: none;">
                                <div class="col-md-12">
                                    <div class="ajax-select mt-lg-0">
                                        <select class="form-control select2-ajax"
                                            id="lead_filter_source_type_value_0"
                                            name="lead_filter_source_type_value_0">
                                        </select>
                                        <div class="invalid-feedback">
                                            Please select type.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-1 mt-lg-0" id="lead_filter_div_0" style="display: none;">
                                <div class="col-md-12">
                                    <div class="ajax-select mt-lg-0">
                                        <select class="form-control select2-ajax" id="lead_filter_select_value_0"
                                            name="lead_filter_select_value_0">
                                        </select>
                                        <div class="invalid-feedback">
                                            Please select value.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-1 mt-lg-0" id="lead_filter_multi_div_0" style="display: none;">
                                <div class="col-md-12">
                                    <div class="ajax-select mt-lg-0">
                                        <select class="form-control select2-ajax select2-multiple" multiple="multiple"
                                            id="lead_filter_select_value_multi_0"
                                            name="lead_filter_select_value_multi_0[]" required>
                                        </select>
                                        <div class="invalid-feedback">
                                            Please select value.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-1 mt-lg-0" id="lead_filter_text_field_div_0" style="display: none;">
                                <div class="col-md-12">
                                    <input type="text" class="form-control" id="lead_filter_value_0"
                                        name="lead_filter_value_0" placeholder="Value" value="" required>
                                </div>
                            </div>
                            <div class="mb-1 mt-lg-0" id="lead_filter_date_picker_div_0" style="display: none;">
                                <div class="col-md-12">
                                    <div class="ajax-select mt-lg-0">
                                        <select class="form-control select2-ajax" id="lead_filter_date_picker_value_0"
                                            name="lead_filter_date_picker_value_0">
                                        </select>
                                        <div class="invalid-feedback">
                                            Please select value.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- <div class="mb-1 mt-lg-0" id="lead_filter_date_picker_div_0" style="display: none;">
                                <div class="col-md-12">
                                    <input autocomplete="off" type="text" class="form-control"
                                        data-date-format="dd-mm-yyyy" data-date-container='#filterdropdownmodel'
                                        data-provide="datepicker" data-date-autoclose="true" required
                                        id="lead_filter_date_picker_value_0" name="lead_filter_date_picker_value_0"
                                        placeholder="Select Date" value="{{ date('d-m-Y') }}">
                                </div>
                            </div> --}}
                            <div class="row mb-1 mt-lg-0" id="lead_filter_fromto_date_picker_div_0"
                                style="display: none;">
                                <div class="col-md-6 pe-0">
                                    <input autocomplete="off" type="text" class="form-control"
                                        data-date-format="dd-mm-yyyy" data-date-container='#filterdropdownmodel'
                                        data-provide="datepicker" data-date-autoclose="true" required
                                        id="lead_filter_from_date_picker_value_0"
                                        name="lead_filter_from_date_picker_value_0" placeholder="Select Date"
                                        value="{{ date('d-m-Y') }}">
                                </div>
                                <div class="col-md-6 ps-1">
                                    <input autocomplete="off" type="text" class="form-control"
                                        data-date-format="dd-mm-yyyy" data-date-container='#filterdropdownmodel'
                                        data-provide="datepicker" data-date-autoclose="true" required
                                        id="lead_filter_to_date_picker_value_0"
                                        name="lead_filter_to_date_picker_value_0" placeholder="Select Date"
                                        value="{{ date('d-m-Y') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="p-0 d-flex justify-content-end"
                        style="cursor: pointer;width:30px;visibility: hidden;">
                        <i class="bx bx-x-circle" style="font-size: large;"></i>
                    </div>
                </div>
                <div id="advanceFilterRows">

                </div>

            </div>
            <div class="card-footer bg-white">
                {{-- <button type="button"  class="btn btn-sm btn-primary  float-end ms-2"><i class="bx bx-plus "></i> Add</button> --}}
                <a id="btnAddAdvanceFilter" class="" style="cursor: pointer;">+ Add New Filter</a>
            </div>
        </div>
    </div>
</div>
