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
                        <h4 class="mb-sm-0 font-size-18">Product Warranty Register</h4>
                        <div class="modal fade" id="modalMainMaster" data-bs-backdrop="static" tabindex="-1" role="dialog"
                            aria-labelledby="modalMainMasterLable" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalMainMasterLable"></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>

                                    <div class="modal-body">
                                        <div class="col-md-12 text-center loadingcls">
                                            <button type="button" class="btn btn-light waves-effect">
                                                <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i>
                                                Loading...
                                            </button>
                                        </div>
                                        <form id="formWarrantyMaster" enctype="multipart/form-data"
                                            class="custom-validation" action="{{ route('warranty.registration.save') }}"
                                            method="POST">
                                            @csrf
                                            <input type="hidden" name="q_warranty_id" id="q_warranty_id">

                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="mb-3">
                                                        <label for="q_warranty_fullname" class="form-label">Full
                                                            Name</label>
                                                        <input type="text" class="form-control" id="q_warranty_fullname"
                                                            name="q_warranty_fullname" placeholder="Name" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="mb-3">
                                                        <label for="q_warranty_mobile" class="form-label">Mobile No.</label>
                                                        <input type="text" class="form-control" id="q_warranty_mobile"
                                                            name="q_warranty_mobile" placeholder="Mobile" readonly>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="mb-3">
                                                        <label for="q_warranty_email" class="form-label">Email</label>
                                                        <input type="text" class="form-control" id="q_warranty_email"
                                                            name="q_warranty_email" placeholder="Email" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                            <label class="form-label">Address</label>
                                            <div class="row">
                                                <div class="col-3">
                                                    <div class="mb-3">
                                                        <input type="text" class="form-control" id="q_warranty_houseno"
                                                            name="q_warranty_houseno" placeholder="House No" readonly>
                                                    </div>
                                                </div>
                                                <div class="col-9">
                                                    <div class="mb-3">
                                                        <input type="text" class="form-control"
                                                            id="q_warranty_buildingsoc" name="q_warranty_buildingsoc"
                                                            placeholder="Building/Society Name" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="mb-3">
                                                        <input type="text" class="form-control" id="q_warranty_area"
                                                            name="q_warranty_area" placeholder="Area" readonly>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="mb-3">
                                                        <input type="text" class="form-control" id="q_warranty_city"
                                                            name="q_warranty_city" placeholder="City" readonly>
                                                    </div>
                                                </div>
                                            </div>

                                            <div id="warranty_invoice_div">
                                                <a href="{{route('quot.itemprice.master.export')}}" id="q_warranty_invoice" target="_blank" class="btn btn-link mb-2" type="button"><i class="bx bx-download font-size-16 align-middle me-2"></i>Uploaded Invoice File</a>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="q_warranty_start_date" class="form-label">Warranty Start Date<code class="highlighter-rouge">*</code></label>
                                                        <div class="input-group" id="div_warranty_start_date">
                                                            <input type="text" class="form-control"
                                                                value="{{ date('d-m-Y') }}" placeholder="dd-mm-yyyy"
                                                                data-date-format="dd-mm-yyyy"
                                                                data-date-container='#div_warranty_start_date'
                                                                data-provide="datepicker" data-date-autoclose="true"
                                                                required name="q_warranty_start_date"
                                                                id="q_warranty_start_date">
                                                        </div>
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
                                        <th>Customer Detail</th>
                                        <th>Address</th>
                                        <th>Warranty Start</th>
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
    <script src="{{ asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
    <script type="text/javascript">
        var ajaxWarrantyRegisterMasterDataURL = '{{ route('warranty.registration.ajax') }}';
        var ajaxWarrantyRegisterDetailDataURL = '{{ route('warranty.registration.detail') }}';

        var csrfToken = $("[name=_token").val();

        var mainMasterPageLength = getCookie('mainMasterPageLength') !== undefined ? getCookie('mainMasterPageLength') : 10;
        var table = $('#datatable').DataTable({
            "aoColumnDefs": [{
                "bSortable": false,
                "aTargets": [3]
            }],
            "order": [
                [0, 'desc']
            ],
            "processing": true,
            "serverSide": true,
            "pagingType": "full_numbers",
            "pageLength": mainMasterPageLength,
            "ajax": {
                "url": ajaxWarrantyRegisterMasterDataURL,
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
                    "mData": "address"
                },
                {
                    "mData": "warranty_start_date"
                },
                {
                    "mData": "action"
                }
            ]
        });

        function reloadTable() {
            table.ajax.reload(null, false);
        }

        // function isNumber(evt) {
        //     evt = (evt) ? evt : window.event;
        //     var charCode = (evt.which) ? evt.which : evt.keyCode;
        //     if (charCode > 31 && (charCode < 48 || charCode > 57)) {
        //         return false;
        //     }
        //     return true;
        // }

        $('#datatable').on('length.dt', function(e, settings, len) {
            setCookie('mainMasterPageLength', len, 100);
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
            $('#formWarrantyMaster').ajaxForm(options);
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
                $("#modalMainMaster").modal('hide');

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


        function resetInputForm() {
            $("#formWarrantyMaster").removeClass('was-validated');
            $('#formWarrantyMaster').trigger("reset");
            $("#q_warranty_id").val(0);
        }

        function showdetail(id) {

            resetInputForm();

            $("#modalMainMaster").modal('show');
            $("#modalMainMasterLable").html("Warranty Register Detail #" + id);
            $("#formWarrantyMaster").hide();
            $(".loadingcls").show();
            $('#warranty_invoice_div').hide();

            $.ajax({
                type: 'GET',
                url: ajaxWarrantyRegisterDetailDataURL + "?id=" + id,
                success: function(resultData) {
                    if (resultData['status'] == 1) {

                        $("#q_warranty_id").val(resultData['data']['id']);
                        $("#q_warranty_fullname").val(resultData['data']['fullname']);
                        $("#q_warranty_mobile").val(resultData['data']['mobile']);
                        $("#q_warranty_email").val(resultData['data']['email']);
                        $("#q_warranty_houseno").val(resultData['data']['address_houseno']);
                        $("#q_warranty_buildingsoc").val(resultData['data']['address_society']);
                        $("#q_warranty_area").val(resultData['data']['address_area']);
                        $("#q_warranty_city").val(resultData['data']['address_city']);

                        if(resultData['data']['invoice_image'] != null && resultData['data']['invoice_image'] != ''){
                            $('#warranty_invoice_div').show();
                            $('#q_warranty_invoice').attr('href', resultData['data']['invoice_image']);
                        }else{
                            $('#warranty_invoice_div').hide();
                        }

                        $(".loadingcls").hide();
                        $("#formWarrantyMaster").show();


                    } else {

                        toastr["error"](resultData['msg']);

                    }

                }
            });

        }
    </script>
@endsection
