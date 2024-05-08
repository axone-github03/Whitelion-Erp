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
                        <h4 class="mb-sm-0 font-size-18">Service Hierarchy

                        </h4>

                        <div class="page-title-right">


                            <button id="addBtnServiceHierarchy" class="btn btn-primary" type="button"
                                data-bs-toggle="offcanvas" data-bs-target="#canvasServiceHierarchy"
                                aria-controls="canvasServiceHierarchy"><i
                                    class="bx bx-plus font-size-16 align-middle me-2"></i>Service Hierarchy </button>


                            <div class="offcanvas offcanvas-end" tabindex="-1" id="canvasServiceHierarchy"
                                aria-labelledby="canvasServiceHierarchyLabel">
                                <div class="offcanvas-header">
                                    <h5 id="canvasServiceHierarchyLabel">Service Hierarchy</h5>
                                    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                                        aria-label="Close"></button>
                                </div>
                                <div class="offcanvas-body">

                                    <div class="col-md-12 text-center loadingcls">






                                        <button type="button" class="btn btn-light waves-effect">
                                            <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading
                                        </button>


                                    </div>






                                    <form id="formServiceHierarch" class="custom-validation"
                                        action="{{ route('service.hierarchy.save') }}" method="POST">

                                        @csrf

                                        <input type="hidden" name="service_hierarchy_id" id="service_hierarchy_id">



                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label for="service_hierarchy_name" class="form-label">Name</label>
                                                    <input type="text" class="form-control" id="service_hierarchy_name"
                                                        name="service_hierarchy_name" placeholder="Name" value=""
                                                        required>


                                                </div>
                                            </div>

                                        </div>

                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label for="service_hierarchy_code" class="form-label">Code</label>
                                                    <input type="text" class="form-control" id="service_hierarchy_code_d"
                                                        name="service_hierarchy_code_d" placeholder="" value=""
                                                        disabled>
                                                    <input type="hidden" name="service_hierarchy_code"
                                                        id="service_hierarchy_code">


                                                </div>
                                            </div>

                                        </div>



                                        <div class="row">

                                            <div class="col-lg-12">
                                                <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                    <label class="form-label">Parent </label>
                                                    <select multiple="multiple"
                                                        class="form-control select2-ajax select2-multiple"
                                                        id="service_hierarchy_parent_id" name="service_hierarchy_parent_id">

                                                    </select>

                                                </div>

                                            </div>




                                        </div>


                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label for="service_hierarchy_status" class="form-label">Status</label>

                                                    <select id="service_hierarchy_status" name="service_hierarchy_status"
                                                        class="form-control select2-apply">
                                                        <option value="1">Active</option>
                                                        <option value="0">Inactive</option>
                                                        <option value="2">Blocked</option>
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
                                        <th>Name</th>
                                        <th>Code</th>
                                        <th>Parent</th>
                                        <th>Status</th>
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
        var ajaxServiceHierarchyDataURL = '{{ route('service.hierarchy.ajax') }}';
        var ajaxServiceHierarchyDetailURL = '{{ route('service.hierarchy.detail') }}';
        var ajaxServiceHierarchySearchURL = '{{ route('service.hierarchy.search') }}';
        var ajaxServiceHierarchyDeleteURL = '{{ route('service.hierarchy.delete') }}';


        var csrfToken = $("[name=_token").val();

        var saleHierarchyPageLength = getCookie('saleHierarchyPageLength') !== undefined ? getCookie(
            'saleHierarchyPageLength') : 10;


        var table = $('#datatable').DataTable({
            "aoColumnDefs": [{
                "bSortable": false,
                "aTargets": [5]
            }],
            "order": [
                [0, 'desc']
            ],
            "processing": true,
            "serverSide": true,
            "pageLength": saleHierarchyPageLength,
            "ajax": {
                "url": ajaxServiceHierarchyDataURL,
                "type": "POST",
                "data": {
                    "_token": csrfToken,
                }


            },
            "aoColumns": [{
                    "mData": "id"
                },
                {
                    "mData": "name"
                },
                {
                    "mData": "code"
                },
                {
                    "mData": "parent"
                },
                {
                    "mData": "status"
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

            setCookie('saleHierarchyPageLength', len, 100);


        });


        $("#service_hierarchy_parent_id").select2({
            ajax: {
                url: ajaxServiceHierarchySearchURL,
                dataType: 'json',
                delay: 0,
                data: function(params) {
                    return {
                        id: function() {
                            return $("#service_hierarchy_id").val()
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
            placeholder: 'Search for a parent service hierarchy',
            allowClear: true,
            maximumSelectionLength: 1,
            dropdownParent: $("#canvasServiceHierarchy")
        });


        $("#service_hierarchy_status").select2({
            minimumResultsForSearch: Infinity,
            dropdownParent: $("#canvasServiceHierarchy")
        });



        function generateHierarchyCode(dInput) {

            dInput = dInput.replace(/[_\W]+/g, "_")
            dInput = dInput.toUpperCase();
            $("#service_hierarchy_code_d").val(dInput);
            $("#service_hierarchy_code").val(dInput)


        }



        $("#service_hierarchy_name").keypress(function() {
            generateHierarchyCode(this.value);
        });

        $("#service_hierarchy_name").change(function() {
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
            $('#formServiceHierarch').ajaxForm(options);
        });

        function showRequest(formData, jqForm, options) {
            generateHierarchyCode($("#service_hierarchy_name").val());
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
                $("#canvasServiceHierarchy").offcanvas('hide');

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


        $("#addBtnServiceHierarchy").click(function() {

            $("#canvasServiceHierarchyLabel").html("Add Service Hierarchy");
            $("#formServiceHierarch").show();
            $(".loadingcls").hide();
            resetInputForm();
        });


        function resetInputForm() {

            $('#formServiceHierarch').trigger("reset");
            $("#service_hierarchy_parent_id").empty().trigger('change');
            $("#service_hierarchy_id").val(0);
            $("#service_hierarchy_status").select2("val", "1");

        }

        function editView(id) {

            resetInputForm();

            $("#canvasServiceHierarchy").offcanvas('show');
            $("#canvasServiceHierarchyLabel").html("Edit Service Hierarchy #" + id);
            $("#formServiceHierarch").hide();
            $(".loadingcls").show();

            $.ajax({
                type: 'GET',
                url: ajaxServiceHierarchyDetailURL + "?id=" + id,
                success: function(resultData) {
                    if (resultData['status'] == 1) {

                        $("#service_hierarchy_id").val(resultData['data']['id']);
                        $("#service_hierarchy_name").val(resultData['data']['name']);
                        $("#service_hierarchy_code").val(resultData['data']['code']);
                        $("#service_hierarchy_code_d").val(resultData['data']['code']);
                        $("#service_hierarchy_status").select2("val", "" + resultData['data']['status'] + "");



                        if (resultData['is_parent'] == 1) {

                            $("#service_hierarchy_parent_id").empty().trigger('change');
                            var newOption = new Option(resultData['parent']['name'], resultData['parent']['id'],
                                false, false);

                            $('#service_hierarchy_parent_id').append(newOption).trigger('change');
                            $("#service_hierarchy_parent_id").val([resultData['parent']['id']]).change();


                        } else {
                            $("#service_hierarchy_parent_id").empty().trigger('change');
                        }




                        $(".loadingcls").hide();
                        $("#formServiceHierarch").show();


                    } else {

                        toastr["error"](resultData['msg']);

                    }

                }
            });

        }

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
                            url: ajaxServiceHierarchyDeleteURL + "?id=" + id,
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
