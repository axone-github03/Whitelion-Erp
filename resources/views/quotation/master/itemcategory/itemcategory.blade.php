@extends('layouts.main')
@section('title', $data['title'])
@section('content')

    <style type="text/css">
        td p {
            max-width: 100%;
            white-space: break-spaces;
            word-break: break-all;
        }
    </style>
    <div class="page-content">
        <div class="container-fluid">
            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">Item Category Master</h4>
                        <div class="page-title-right">
                            @if (isAdminOrCompanyAdmin() == 1)
                                <a href="{{ route('quot.category.export') }}" target="_blank" class="btn btn-info"
                                    type="button"><i class="bx bx-export font-size-16 align-middle me-2"></i>Export </a>
                            @endif
                            <button id="addBtnMainMaster" class="btn btn-primary" type="button" data-bs-toggle="offcanvas"
                                data-bs-target="#canvasMainMaster" aria-controls="canvasMainMaster"><i
                                    class="bx bx-plus font-size-16 align-middle me-2"></i>Add Category</button>
                            <div class="offcanvas offcanvas-end" tabindex="-1" id="canvasMainMaster"
                                aria-labelledby="canvasMainMasterLable">
                                <div class="offcanvas-header">
                                    <h5 id="canvasMainMasterLable">Item Category Master</h5>
                                    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                                        aria-label="Close"></button>
                                </div>
                                <div class="offcanvas-body">
                                    <div class="col-md-12 text-center loadingcls">
                                        <button type="button" class="btn btn-light waves-effect">
                                            <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i>
                                            Loading...
                                        </button>
                                    </div>
                                    <form id="formMainMaster" class="custom-validation"
                                        action="{{ route('quot.itemcategory.master.save') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="q_category_master_id" id="q_category_master_id">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label for="q_category_master_name" class="form-label">Category Name
                                                        <code class="highlighter-rouge">*</code></label>
                                                    <input type="text" class="form-control" id="q_category_master_name"
                                                        name="q_category_master_name" placeholder="Name" value=""
                                                        required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label for="q_category_master_code" class="form-label">Short Name
                                                    </label>
                                                    <input type="text" class="form-control" id="q_category_master_code"
                                                        name="q_category_master_code" placeholder="" value=""
                                                        readonly>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label for="q_category_master_display_group" class="form-label">Display
                                                        Group </label>
                                                    <input type="number" class="form-control"
                                                        id="q_category_master_display_group"
                                                        name="q_category_master_display_group" placeholder=""
                                                        value="0">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label for="q_category_master_sequence" class="form-label">Sequence </label>
                                                    <input type="number" class="form-control" id="q_category_master_sequence"
                                                        name="q_category_master_sequence" onkeypress="return isNumber(event);"
                                                        placeholder="" value="0" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                <label for="q_category_master_type" class="form-label">Category Type</label>
                                                <select class="form-control select2-ajax select2-multiple"
                                                    multiple="multiple" id="q_category_master_type"
                                                    name="q_category_master_type[]">
                                                </select>
                                                <div class="invalid-feedback">
                                                    Please select Category Type.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label for="q_category_master_remark" class="form-label">Remark
                                                    </label>
                                                    <textarea class="form-control" id="q_category_master_remark" name="q_category_master_remark" rows="2"
                                                        placeholder="Enter Remark"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label for="q_category_master_status" class="form-label">Is Active
                                                    </label>

                                                    <select id="q_category_master_status" name="q_category_master_status"
                                                        class="form-control select2-apply">
                                                        <option value="1">Active</option>
                                                        <option value="0">Inactive</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-wrap gap-2">
                                            <button type="submit" class="btn btn-primary waves-effect waves-light">
                                                Save
                                            </button>
                                            <button type="reset" class="btn btn-secondary waves-effect">
                                                Reset
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end page title -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <table id="datatable" class="table table-striped dt-responsive  nowrap w-100">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Category Name / Type</th>
                                        <th>Short Name</th>
                                        <th>Is Active</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div> <!-- end col -->
            </div> <!-- end row -->

        </div>
        <!-- container-fluid -->
    </div>
    <!-- End Page-content -->
    @csrf
@endsection('content')


@section('custom-scripts')

    <script src="{{ asset('assets/libs/parsleyjs/parsley.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/form-validation.init.js') }}"></script>
    <script src="{{ asset('assets/js/pages/jquery.form.js') }}"></script>
    <script type="text/javascript">
        var ajaxItemCategoryMasterDataURL = '{{ route('quot.itemcategory.master.ajax') }}';
        var ajaxItemCategoryMasterDetailURL = '{{ route('quot.itemcategory.master.detail') }}';
        var ajaxItemCategoryMasterDeleteURL = '{{ route('quot.itemcategory.master.delete') }}';
        var ajaxURLSearchCategoryType = '{{ route('quot.itemcategory.search.category.type') }}';

        var csrfToken = $("[name=_token").val();

        var mainMasterPageLength = getCookie('mainMasterPageLength') !== undefined ? getCookie('mainMasterPageLength') : 10;
        var table = $('#datatable').DataTable({
            "aoColumnDefs": [{
                "bSortable": false,
                "aTargets": [4]
            }],
            "order": [
                [0, 'desc']
            ],
            "processing": true,
            "serverSide": true,
            "pagingType": "full_numbers",
            "pageLength": mainMasterPageLength,
            "ajax": {
                "url": ajaxItemCategoryMasterDataURL,
                "type": "POST",
                "data": {
                    "_token": csrfToken,
                }
            },
            "aoColumns": [{
                    "mData": "id"
                },
                {
                    "mData": "itemcategoryname"
                },
                {
                    "mData": "shortname"
                },
                {
                    "mData": "isactive"
                },
                {
                    "mData": "action"
                }
            ]
        });

        function reloadTable() {
            table.ajax.reload(null, false);
        }

        $('#datatable').on('length.dt', function(e, settings, len) {
            setCookie('mainMasterPageLength', len, 100);
        });

        $("#q_category_master_status").select2({
            minimumResultsForSearch: Infinity,
            dropdownParent: $("#canvasMainMaster")
        });

        function isNumber(evt) {
            evt = (evt) ? evt : window.event;
            var charCode = (evt.which) ? evt.which : evt.keyCode;
            if (charCode > 31 && (charCode < 48 || charCode > 57)) {
                return false;
            }
            return true;
        }

        function generateHierarchyCode(dInput) {
            dInput = dInput.replace(/[_\W]+/g, "_")
            dInput = dInput.toUpperCase();
            $("#q_category_master_code").val(dInput)
        }

        $("#q_category_master_name").keyup(function() {
            generateHierarchyCode(this.value);
        });

        $("#q_category_master_name").change(function() {
            generateHierarchyCode(this.value);
        });


        $(document).ready(function() {
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
            $('#formMainMaster').ajaxForm(options);
        });

        function showRequest(formData, jqForm, options) {
            generateHierarchyCode($("#q_category_master_name").val());
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
                $("#canvasMainMaster").offcanvas('hide');

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

        $("#addBtnMainMaster").click(function() {
            $("#canvasMainMasterLable").html("Add Item Category");
            $("#formMainMaster").show();
            $(".loadingcls").hide();
            resetInputForm();
        });


        function resetInputForm() {

            $('#formMainMaster').trigger("reset");
            $("#q_category_master_id").val(0);
            $("#q_category_master_status").select2("val", "1");
            $("#q_category_master_type").empty().trigger('change');
        }

        function editView(id) {

            resetInputForm();

            $("#canvasMainMaster").offcanvas('show');
            $("#canvasMainMasterLable").html("Edit Item Category #" + id);
            $("#formMainMaster").hide();
            $(".loadingcls").show();

            $.ajax({
                type: 'GET',
                url: ajaxItemCategoryMasterDetailURL + "?id=" + id,
                success: function(resultData) {
                    if (resultData['status'] == 1) {

                        $("#q_category_master_id").val(resultData['data']['id']);
                        $("#q_category_master_name").val(resultData['data']['itemcategoryname']);
                        $("#q_category_master_code").val(resultData['data']['shortname']);
                        $("#q_category_master_display_group").val(resultData['data']['display_group']);
                        $("#q_category_master_remark").val(resultData['data']['remark']);
                        $("#q_category_master_sequence").val(resultData['data']['app_sequence']);

                        if (resultData['data']['cat_type'] != null) {
                            $("#q_category_master_type").empty().trigger('change');
                            var selectedSalePersons = [];
                            var cat_type_arr = resultData['data']['cat_type'].split(',');
                            for (var i = 0; i < cat_type_arr.length; i++) {
                                selectedSalePersons.push('' + cat_type_arr[i] + '');
                                var newOption = new Option(cat_type_arr[i].toLowerCase().charAt(0)
                                    .toUpperCase() + cat_type_arr[i].toLowerCase().slice(1), cat_type_arr[
                                    i],
                                    false, false);
                                $('#q_category_master_type').append(newOption).trigger('change');
                            }
                            $("#q_category_master_type").val(selectedSalePersons).change();
                        }

                        $("#q_category_master_status").select2("val", "" + resultData['data']['isactive'] + "");

                        $(".loadingcls").hide();
                        $("#formMainMaster").show();

                    } else {

                        toastr["error"](resultData['msg']);

                    }

                }
            });

        }

        $("#q_category_master_type").select2({
            ajax: {
                url: ajaxURLSearchCategoryType,
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
            placeholder: 'Search Category Type',
            dropdownParent: $("#canvasMainMaster"),
        });

        function deleteWarning(id) {
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: !0,
                confirmButtonText: "Yes, delete it!",
                cancelButtonText: "No, cancel!",
                confirmButtonClass: "btn btn-success mt-2",
                cancelButtonClass: "btn btn-danger ms-2 mt-2",
                loaderHtml: "<i class='bx bx-hourglass bx-spin font-size-16 align-middle me-2'></i> Loading",
                customClass: {
                    confirmButton: 'btn btn-primary btn-lg',
                    cancelButton: 'btn btn-danger btn-lg',
                    loader: 'custom-loader'
                },
                buttonsStyling: !1,
                preConfirm: function(n) {
                    return new Promise(function(t, e) {
                        Swal.showLoading()
                        $.ajax({
                            type: 'GET',
                            url: ajaxItemCategoryMasterDeleteURL + "?id=" + id,
                            success: function(resultData) {
                                if (resultData['status'] == 1) {

                                    reloadTable();
                                    t()
                                }
                            }
                        });
                    })
                },
            }).then(function(t) {
                if (t.value === true) {
                    Swal.fire({
                        title: "Deleted!",
                        text: "Your record has been deleted.",
                        icon: "success"
                    });
                }
            });
        }
    </script>
@endsection
