<style>
    .source_box {
        padding: 8px 0px;
        border-top: 1px solid gainsboro;
        border-bottom: 1px solid gainsboro;

    }
</style>
<div class="modal fade show" id="modalDiscount" data-bs-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="modalDiscountLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            
            <div class="modal-header">
                <h5 class="modal-title" id="modalDiscountLabel">Add Discount</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="col-md-12 text-center loadingcls">
                    <button type="button" class="btn btn-light waves-effect">
                        <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading...
                    </button>
                </div>
                <form id="formDiscount" class="custom-validation" method="POST">
                    @csrf
                    <div class="container-fluid">
                        <input type="hidden" name="dis_flow_id" id="dis_flow_id" >
                        <input type="hidden" name="id" id="id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="discount_name" class="form-label">Name<code class="highlighter-rouge">*</code></label>
                                    <input type="text" class="form-control" id="discount_name" name="discount_name" placeholder="Name" value="" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="discount_status" class="form-label">Status<code class="highlighter-rouge">*</code></label>
                                    <select class="form-select" id="discount_status" name="discount_status">
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
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="discount_default_dis" class="form-label">Default Disc.<code class="highlighter-rouge">*</code></label>
                                    <input type="number" step="0.01" class="form-control" id="discount_default_dis" name="discount_default_dis" placeholder="Discount" value="00.00" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="user_default_dis" class="form-label">User Disc.<code class="highlighter-rouge">*</code></label>
                                    <input type="number" step="0.01" class="form-control" id="user_default_dis" name="user_default_dis" placeholder="Discount" value="00.00" required>
                                </div>
                            </div>
                        </div>
                        <div class="row" style="border-top: 1px solid gainsboro; padding-top: 8px;" id="discount_source_container">
                            <div class="col-md-4">
                                <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                    <label for="discount_user_type_id" class="form-label">User Type <code class="highlighter-rouge">*</code></label>
                                    <select class="form-control select2-ajax" id="discount_user_type_id" name="discount_user_type_id" required></select>
                                    <div class="invalid-feedback">Please Select User Type.</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                    <label for="discount_user_id" class="form-label">User <code class="highlighter-rouge">*</code></label>
                                    <select class="form-control select2-ajax select2-multiple" multiple="multiple" id="discount_user_id" name="discount_user_id[]" required></select>
                                    <div class="invalid-feedback">Please Select User.</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                    <label for="discount_dis" class="form-label">Discount<code class="highlighter-rouge">*</code></label>
                                    <input type="number" step="0.01" class="form-control" id="discount_dis" name="discount_dis" placeholder="Discount" value="00.00" required>
                                </div>
                            </div>
                        </div>

                        <div id="advanceFilterRows">

                        </div>

                        <div class="mb-2" style="padding: 8px 0px;">
                            <a id="btnAddAdvanceFilter" class="" style="cursor: pointer;">+ Add New Filter</a>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="saveAdvanceFilter">Save</button>
                    </div>
                <form>
            </div>
        </div>
    </div>
</div>

