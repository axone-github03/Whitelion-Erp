@extends('layouts.main')
@section('title', $data['title'])
@section('content')
<link href="{{ asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css') }}" rel="stylesheet" />


<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Invoice Packed

                    </h4>


                    <div class="page-title-right">


                    </div>
                </div>


            </div>
        </div>
        <!-- end page title -->




        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">


                        <div class="table-responsive">
                            <table id="datatable" class="table align-middle table-nowrap dt-responsive mb-0 w-100">
                                <thead class="table-light">
                                    <tr>
                                        <th>Detail</th>

                                        <th>Channel Partner</th>
                                        <th>Sale Persons</th>
                                        <th>Payment Detail</th>
                                        <th>Invoice Status</th>
                                        <th>Mark Dispatch</th>
                                        <th>Packed Sticker</th>

                                        <th>Action</th>


                                    </tr>
                                </thead>


                                <tbody>

                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div> <!-- end col -->
        </div> <!-- end row -->

    </div>
    <!-- container-fluid -->
</div>
<!-- End Page-content -->


<div class="offcanvas offcanvas-end" tabindex="-1" id="canvasDispatchInvoice" aria-labelledby="canvasDispatchInvoiceLable">
    <div class="offcanvas-header">
        <h5 id="canvasDispatchInvoiceLable">Dispatch</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form enctype="multipart/form-data" id="formInvoiceDispatch" class="custom-validation" action="{{route('invoice.markasdispatch')}}" method="POST">

            @csrf

            <input type="hidden" name="invoice_id" id="invoice_id">



            <div class="row">
                <div class="col-md-12">
                    <div class="mb-3">
                        <label for="invoice_box_number" class="form-label">Number Of Boxes</label>
                        <input type="number" class="form-control" id="invoice_box_number" name="invoice_box_number" placeholder="Number Of Boxes" value="" required>


                    </div>
                </div>

            </div>





            <div class="d-flex flex-wrap gap-2">
                <button type="submit" id="formInvoiceDispatchSubmit" class="btn btn-primary waves-effect waves-light">
                    MARK AS DISPATCH
                </button>

            </div>
        </form>
    </div>
</div>

@include('../invoice/comman/detail')



@csrf
@endsection('content')
@section('custom-scripts')

<script src="{{ asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js') }}"></script>
<script src="{{ asset('assets/libs/parsleyjs/parsley.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/form-validation.init.js') }}"></script>
<script src="{{ asset('assets/js/pages/jquery.form.js') }}"></script>
<script type="text/javascript">
    var ajaxURL = "{{route('invoice.packed.ajax')}}";
    var ajaxInvoiceDetail = "{{route('invoice.detail.dispatcher')}}";
    var ajaxInvoiceMarkAsPacked = "{{route('invoice.markaspacked')}}";
    var ajaxSearchCourier = "{{route('invoice.search.courier')}}";
    var csrfToken = $("[name=_token").val();

    var invoicePageLength = getCookie('invoicePageLength') !== undefined ? getCookie('invoicePageLength') : 10;

    var table = $('#datatable').DataTable({
        "aoColumnDefs": [{
            "bSortable": false,
            "aTargets": [3, 4, 5, 6, 7]
        }],
        "order": [
            [0, 'desc']
        ],
        "processing": true,
        "serverSide": true,
        "pageLength": invoicePageLength,
        "ajax": {
            "url": ajaxURL,
            "type": "POST",
            "data": {
                "_token": csrfToken,
            }


        },
        "aoColumns": [

            {
                "mData": "detail"
            },
            {
                "mData": "channel_partner"
            },
            {
                "mData": "sale_persons"
            },
            {
                "mData": "payment_detail"
            },
            {
                "mData": "status"
            },
            {
                "mData": "action_mark_dispatch"
            },
            {
                "mData": "packed_sticker"
            },
            {
                "mData": "action"
            }

        ],
        "drawCallback": function() {

            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })

        }
    });


    $('#datatable').on('length.dt', function(e, settings, len) {

        setCookie('invoicePageLength', len, 100);


    });


    function doMarkAsDispatch(id, noOFBox) {
        $("#canvasDispatchInvoice").offcanvas('show');
        $("#formInvoiceDispatch").trigger('reset');
        $("#invoice_id").val(id);
        $("#invoice_box_number").val(noOFBox);

    }

    $("#invoice_courier_service_id").select2({
        ajax: {
            url: ajaxSearchCourier,
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
        placeholder: 'Search for a courier service ',
        dropdownParent: $("#canvasDispatchInvoice")

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
        $('#formInvoiceDispatch').ajaxForm(options);
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


        $("#formInvoiceDispatchSubmit").html("Processing...");
        $("#formInvoiceDispatchSubmit").prop('disabled', true);

        return true;
    }

    // post-submit callback
    function showResponse(responseText, statusText, xhr, $form) {

        $("#formInvoiceDispatchSubmit").html("MARK AS DISPATCH");
        $("#formInvoiceDispatchSubmit").prop('disabled', false);


        if (responseText['status'] == 1) {
            toastr["success"](responseText['msg']);
            if (responseText['dispatch_pdf'] != "") {
                window.open(responseText['dispatch_pdf'], '_blank');
            }


            table.ajax.reload(null, false);
            $("#canvasDispatchInvoice").offcanvas('hide');


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
</script>
@include('../invoice/comman/script')

@endsection