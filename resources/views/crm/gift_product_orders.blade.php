@extends('layouts.main')
@section('title', $data['title'])
@section('content')
    <link href="{{ asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css') }}" rel="stylesheet" />
    <style>
        .edit-name {
            border-radius: 0.25rem;
            display: block;
            text-align: center;
        }

        .y-color {
            color: #c8c911;
            background-color: #e1e12f33;
            font-size: 11px!important;
        }

        .g-color {
            color: #005a0a;
            background-color: #8bd59e;
            font-size: 11px!important;
        }

        .r-color {
            color: #ff0000;
            background-color: #ff000017;
            font-size: 11px!important;
        }

        .o-color {
            color: #ff6700;
            background-color: #ff760033;
            font-size: 11px!important;
        }

        .b-color {
            color: #0046ff;
            background-color: #97adff63;
            font-size: 11px!important;
        }

        .br-color {
            color: #ae502c;
            background-color: #dd8d6e57;
            font-size: 11px!important;
        }

        .selector {
            position: relative;
            width: 50%;
            background-color: #fff;
            height: 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 9999px;
            box-shadow: 0 0 16px rgb(153 151 151 / 20%);
        }

        .selecotr-item {
            position: relative;
            flex-basis: calc(70% / 3);
            height: 30px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .selector-item_radio {
            appearance: none;
            display: none;
        }

        .selector-item_radio:checked+.selector-item_label {
            background-color: #4169e1;
            color: #fff;
            box-shadow: 0 0 16px rgb(153 151 151 / 20%);
            /* transform: translateY(-2px); */
        }

        .selector-item_label {
            position: relative;
            height: 30px;
            width: 90px;
            text-align: center;
            border-radius: 9999px;
            line-height: 30px;
            font-weight: 900;
            transition-duration: .5s;
            transition-property: transform, color, box-shadow;
            transform: none;
        }
    </style>

    <div class="page-content">
        <div class="container-fluid">

            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">Orders

                        </h4>


                        <div class="page-title-right">

                            <button class="btn btn-info" type="button"><i
                                    class="bx bx bx-money font-size-16 align-middle me-2"></i>TOTAL CASHBACK: <span
                                    id="total_amount_cashback"></span> </button> +
                            <button class="btn btn-info" type="button"><i
                                    class="bx bx bx-money font-size-16 align-middle me-2"></i>TOTAL CASH: <span
                                    id="total_amount_cash"></span> </button>
                            =
                            <button class="btn btn-info" type="button"><i
                                    class="bx bx bx-money font-size-16 align-middle me-2"></i>TOTAL : <span
                                    id="total_amount"></span> </button>
                            ,
                            <button class="btn btn-info" type="button"><i
                                    class="bx bx bx-money font-size-16 align-middle me-2"></i>TOTAL PRICE : <span
                                    id="total_price"></span> </button>



                        </div>
                    </div>


                </div>
            </div>
            <!-- end page title -->




            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">

                            @php
                                $accessTypes = CRMUserType();
                            @endphp
                            <div class="d-flex flex-wrap gap-2 userscomman">

                                @foreach ($accessTypes as $key => $value)
                                    <a href="{{ route('gift.product.orders') }}?type={{ $value['id'] }}"
                                        class="btn btn-outline-primary waves-effect waves-light">{{ $value['another_name'] }}</a>
                                @endforeach
                            </div>
                            <br>



                            <div class="table-responsive">
                                <table id="datatable" class="table align-middle table-nowrap mb-0 dt-responsive">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#Id</th>
                                            <th>Date & Time</th>
                                            <th>
                                                @if ($data['type'] == 202)
                                                    Architect
                                                @elseif($data['type'] == 302)
                                                    Elecrician
                                                @endif
                                            </th>
                                            <th>Assigend</th>
                                            <th>Total Point</th>
                                            <th>Gift</th>
                                            <th>Cash</th>
                                            <th>Cashback</th>
                                            <th>Total Amount</th>
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


    <div class="offcanvas offcanvas-end" tabindex="-1" id="canvasDispatchOrder" aria-labelledby="canvasDispatchOrderLable">
        <div class="offcanvas-header">
            <h5 id="canvasDispatchOrderLable">Dispatch</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <form enctype="multipart/form-data" id="formOrderDispatch" class="needs-validation" action="{{ route('gift.product.order.markasdispatch') }}" method="POST" novalidate>
                @csrf
                <input type="hidden" name="order_id" id="order_id">
                <input type="hidden" name="order_type" id="order_type">

                <div class="row" id="order_track_div">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="order_track_id" class="form-label">Track Id</label>
                            <input type="text" class="form-control" id="order_track_id" name="order_track_id" placeholder="Track Id">
                        </div>
                    </div>

                </div>

                <div class="row" id="order_transaction_id_div">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="order_transaction_id" class="form-label">Transaction Id</label>
                            <input type="text" class="form-control" id="order_transaction_id" name="order_transaction_id" placeholder="Transaction Id">
                        </div>
                    </div>
                </div>
                <div class="row" id="order_courier_service_div">
                    <div class="col-lg-12">
                        <div class="mb-3 ajax-select mt-3 mt-lg-0">
                            <label class="form-label">Courier Service </label>
                            <select class="form-control select2-ajax" id="order_courier_service_id" name="order_courier_service_id"></select>
                            <div class="invalid-feedback"> Please select site stage </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="invoice_dispatch_detail" class="form-label" id="invoice_dispatch_detail">Dispatch Detail</label>
                            <input type="file"
                                accept="@php $fileTypes=acceptFileTypes('gift.order.dispatch.detail','client'); echo implode(',',$fileTypes); @endphp"
                                class="form-control" id="order_dispatch_detail" name="order_dispatch_detail"
                                placeholder="Dispatch Detail" value="" required>
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary waves-effect waves-light" id="BtnMarkAsDispatch"> MARK
                        AS DISPATCH
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalOrderPreviw" data-bs-backdrop="static" tabindex="-1" role="dialog"
        aria-labelledby="modalOrderLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"> </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                </div>
            </div>
        </div>
    </div>

    {{-- <div class="modal fade" id="bankDetailrecieve" data-bs-backdrop="static" tabindex="-1" role="dialog"
        aria-labelledby="bankDetailrecieveLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"> </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form enctype="multipart/form-data" action="{{ route('gift.product.order.savebankbetail') }}"
                        method="POST" class="needs-validation" novalidate="" id="bankupidetailsave">
                        @csrf
                        <input type="hidden" name="order_hidden_id" id="order_hidden_id" value="1">
                        <input type="hidden" name="order_hidden_type" id="order_hidden_type" value="">
                        <div class="row justify-content-center" id="radio_button">
                            <div class="selector">
                                <div class="selecotr-item">
                                    <input type="radio" id="radio1" name="BankAndUpi" class="selector-item_radio"
                                        checked="" value="1">
                                    <label for="radio1" class="selector-item_label m-0">Bank</label>
                                </div>
                                <div class="selecotr-item">
                                    <input type="radio" id="radio3" name="BankAndUpi" class="selector-item_radio"
                                        value="2">
                                    <label for="radio3" class="selector-item_label m-0">Upi</label>
                                </div>
                            </div>

                            {{-- <div class="col-6 text-center">
                            <label for="">Bank:- </label>
                            <input type="radio" name="BankAndUpi" value="1" checked>
                        </div>
                        <div class="col-6 text-center">
                            <label for="">Upi:-</label>
                            <input type="radio" name="BankAndUpi" value="2">
                        </div> 
                            <div class="row mb-1 align-items-center" id="bank_details">
                                <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Account
                                    Number</label>
                                <div class="col-sm-7 py-2 d-inline-block">
                                    <input class="form-control" id="account_number" name="account_number"
                                        placeholder="Account Number" value="">
                                </div>


                                <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">IFSC
                                    Number</label>
                                <div class="col-sm-7 py-2 d-inline-block">
                                    <input class="form-control" id="ifsc_number" name="ifsc_number"
                                        placeholder="IFSC Number" value="">
                                </div>
                            </div>
                            <div class="row mb-1 d-none" id="upi_details">
                                <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">UPI Id :-</label>
                                <div class="col-sm-8 py-2 d-inline-block">
                                    <input class="form-control" id="upi_id" name="upi_id" placeholder="UPI ID"
                                        value="">
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary" style="float: right;">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div> --}}

    <div class="modal fade" id="modalAcceptAndReject" data-bs-backdrop="static" tabindex="-1" role="dialog"
        aria-labelledby="modalAcceptAndRejectLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"> </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="order_hidden_id" id="order_hidden_id">
                    <input type="hidden" name="order_hidden_type" id="order_hidden_type">
                    <div class="row">
                        <div class="col-6">
                            <label for=""> Accept:- </label>
                            <input type="radio" name="AcceptAndReject" value="1" selected>
                        </div>
                        <div class="col-6">
                            <label for="">Reject:-</label>
                            <input type="radio" name="AcceptAndReject" value="2">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-sm btn-primary" onclick="SubmitAcceptAndReject()">Submit</button>
                </div>
            </div>
        </div>
    </div>



    <div class="modal fade" id="modalOrderLog" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1"
        role="dialog" aria-labelledby="modalInquiryLogLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalOrderLogLabel"> Order Log</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="min-height:100%;">


                    <table id="giftOrderLogTable" class="table align-middle table-nowrap mb-0 w-100">
                        <thead>
                            <tr>


                                <th>Event Name </th>
                                <th>Description</th>
                                <th>Time</th>


                            </tr>
                        </thead>


                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @csrf
@endsection('content')
@section('custom-scripts')


    <script src="{{ asset('assets/libs/parsleyjs/parsley.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/form-validation.init.js') }}"></script>
    <script src="{{ asset('assets/js/pages/jquery.form.js') }}"></script>
    <script type="text/javascript">
        var ajaxURL = '{{ route('gift.product.orders.ajax') }}';
        var ajaxOrderDetail = '{{ route('gift.product.order.detail') }}';
        var ajaxSearchCourier = '{{ route('search.courier') }}';
        var ajaxMarkeAsAccept = '{{ route('gift.product.order.markasaccept') }}';
        var ajaxMarkeAsReject = '{{ route('gift.product.order.markasreject') }}';
        var ajaxMarkeAsDeliever = '{{ route('gift.product.order.markasdeliever') }}';
        var ajaxMarkeAsReciever = '{{ route('gift.product.order.markasrecieve') }}';
        var ajaxOrderLog = '{{ route('gift.product.orders.log.ajax') }}';


        var csrfToken = $("[name=_token").val();
        var CRMType = '{{ $data['type'] }}';


        $("#order_courier_service_id").select2({
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
            dropdownParent: $("#canvasDispatchOrder")

        });

        function showAcceptAndRejectModal(id, type) {
            $('#order_hidden_type').val(type)
            $('#order_hidden_id').val(id);
            $("#modalAcceptAndReject").modal('show');
        }

        function SubmitAcceptAndReject() {
            status_value = $('input[name="AcceptAndReject"]:checked').val();
            id = $('#order_hidden_id').val();
            type = $('#order_hidden_type').val()
            if (status_value == 1) {
                doMarkAsAccept(id, type);
            } else if (status_value == 2) {
                doMarkAsReject(id, type);
            }
            $("#modalAcceptAndReject").modal('hide');
        }

        var giftProductOrdersPageLength = getCookie('giftProductOrdersPageLength') !== undefined ? getCookie(
            'giftProductOrdersPageLength') : 10;
        var table = $('#datatable').DataTable({
            "aoColumnDefs": [{
                "bSortable": false,
                "aTargets": [7]
            }],
            "order": [
                [0, 'desc']
            ],
            "processing": true,
            "serverSide": true,
            "pageLength": giftProductOrdersPageLength,
            "ajax": {
                "url": ajaxURL + '?type=' + CRMType,
                "type": "POST",
                "data": {
                    "_token": csrfToken,
                }


            },
            "aoColumns": [

                {
                    "mData": "id"
                },
                {
                    "mData": "created_at"
                },
                {
                    "mData": "name"
                },
                {
                    "mData": "assign_to"
                },
                {
                    "mData": "total_point_value"
                },
                {
                    "mData": "gift"
                },
                {
                    "mData": "total_cash"
                },
                {
                    "mData": "total_cashback"
                },
                {
                    "mData": "total_amount"
                },
                // {
                //     "mData": "status"
                // },
                {
                    "mData": "action"
                }

            ],
            "drawCallback": function() {

                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                });

            }
        });

        var giftOrderId = 0;
        var table2 = $('#giftOrderLogTable').DataTable({
            "aoColumnDefs": [{
                "bSortable": false,
            }],
            "order": [
                [0, 'desc']
            ],
            "processing": true,
            "serverSide": true,
            "pageLength": 10,
            "ajax": {
                "url": ajaxOrderLog,
                "type": "POST",
                "data": {
                    "_token": csrfToken,
                    "order_id": function() {
                        return giftOrderId;
                    },
                }


            },
            "aoColumns": [{
                    "mData": "name"
                },
                {
                    "mData": "description"
                },
                {
                    "mData": "created_at"
                }


            ],
            "drawCallback": function() {

                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                });

            }
        });

        function getGiftOrderLog(id) {
            giftOrderId = id;
            table2.ajax.reload();
            $("#modalOrderLog").modal('show');
            $("#modalOrderLogLabel").html("Order #" + id + " Log");

        }

        $('#datatable').on('length.dt', function(e, settings, len) {

            setCookie('giftProductOrdersPageLength', len, 100);


        });

        table.on('xhr', function() {
            var responseData = table.ajax.json();


            $("#total_amount_cashback").html(responseData['overview']['total_cashback']);
            $("#total_amount_cash").html(responseData['overview']['total_cash']);
            $("#total_amount").html(responseData['overview']['total']);
            $("#total_price").html(responseData['overview']['total_price']);

            //$("#totalQuotationAmount").html(responseData['quotationAmount']);

        });

        function ViewOrder(id) {

            $.ajax({
                type: 'GET',
                url: ajaxOrderDetail + "?id=" + id,
                success: function(responseText) {
                    if (responseText['status'] == 1) {

                        $("#modalOrderPreviw").modal('show');
                        $("#modalOrderPreviw .modal-body").html(responseText['preview']);


                    } else {
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

                }
            });

        }

        function doMarkAsDispatch(id, type) {
            $("#canvasDispatchOrder").offcanvas('show');
            $('#formOrderDispatch').trigger("reset");
            $('#formOrderDispatch').removeClass('was-validated');
            if (type == 'gift') {
                $("#order_transaction_id").prop("required", false);
                $("#order_courier_service_id").prop("required", true);
                $("#order_track_id").prop("required", true);
                $("#order_transaction_id_div").hide();
                $("#order_courier_service_div").show();
                $("#order_track_div").show();
                $('#invoice_dispatch_detail').text('Dispatch Detail');
            } else if (type == 'cash' || type == 'cashback') {
                $("#order_courier_service_id").prop("required", false);
                $("#order_track_id").prop("required", false);
                $("#order_transaction_id").prop("required", true);
                $("#order_transaction_id_div").show();
                $("#order_courier_service_div").hide();
                $("#order_track_div").hide();
                $('#invoice_dispatch_detail').text('Dispatch Document');
            }
            $("#order_id").val(id);
            $("#order_type").val(type);
            $("#canvasDispatchOrderLable").html("Dispatch for order id #" + id);
        }

        function doMarkAsAccept(id, type) {
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: !0,
                confirmButtonText: "Yes, accept !",
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
                            url: ajaxMarkeAsAccept + "?id=" + id,
                            data: {
                                type: type,
                            },
                            success: function(resultData) {

                                if (resultData['status'] == 1) {

                                    table.ajax.reload(null, false);;
                                    t()



                                }




                            }
                        });



                    })
                },
            }).then(function(t) {

                if (t.value === true) {



                    Swal.fire({
                        title: "Mark as packed!",
                        text: "Your record has been updated.",
                        icon: "success"
                    });


                }

            });



        }

        function doMarkAsDeliever(id, type) {

            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: !0,
                confirmButtonText: "Yes, deliever !",
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
                            url: ajaxMarkeAsDeliever + "?id=" + id,
                            data: {
                                type: type
                            },
                            success: function(resultData) {

                                if (resultData['status'] == 1) {

                                    table.ajax.reload(null, false);;
                                    t()

                                }

                            }
                        });
                    })
                },
            }).then(function(t) {

                if (t.value === true) {



                    Swal.fire({
                        title: "Mark as delivered!",
                        text: "Your record has been updated.",
                        icon: "success"
                    });


                }

            });



        }

        function doMarkAsRecieve(id, type) {

            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: !0,
                confirmButtonText: "Yes, recieve !",
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
                            data: {
                                type: type
                            },
                            url: ajaxMarkeAsReciever + "?id=" + id,
                            success: function(resultData) {
                                if (resultData['status'] == 1) {
                                    table.ajax.reload(null, false);;
                                    t();
                                    // if (type == "cash") {
                                    //     $('#bankDetailrecieve').modal('show');
                                    // }
                                }

                            }
                        });
                    })
                },
            }).then(function(t) {
                if (t.value === true) {
                    Swal.fire({
                        title: "Mark as recieved!",
                        text: "Your record has been updated.",
                        icon: "success"
                    }).then(function(okay) {
                        if (okay) {
                            // if (type == "cash") {
                            //     $('#bankDetailrecieve').modal('show');
                            // }
                        }
                    })
                }
            });



        }

        function doMarkAsReject(id, type) {
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: !0,
                confirmButtonText: "Yes, reject !",
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
                            url: ajaxMarkeAsReject + "?id=" + id,
                            data: {
                                type: type
                            },
                            success: function(resultData) {

                                if (resultData['status'] == 1) {

                                    table.ajax.reload(null, false);;
                                    t()
                                }




                            }
                        });



                    })
                },
            }).then(function(t) {

                if (t.value === true) {



                    Swal.fire({
                        title: "Mark as packed!",
                        text: "Your record has been updated.",
                        icon: "success"
                    });


                }

            });



        }

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
            $('#formOrderDispatch').ajaxForm(options);
            $('#bankupidetailsave').ajaxForm(options);
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
            $("#BtnMarkAsDispatch").prop("disabled", true);
            $("#BtnMarkAsDispatch").html("MARK AS DISPATCH...");
            return true;
        }

        // post-submit callback
        function showResponse(responseText, statusText, xhr, $form) {
            $("#BtnMarkAsDispatch").prop("disabled", false);
            $("#BtnMarkAsDispatch").html("MARK AS DISPATCH");

            if (responseText['status'] == 1) {
                toastr["success"](responseText['msg']);
                table.ajax.reload(null, false);
                $("#canvasDispatchOrder").offcanvas('hide');
                // $('#bankDetailrecieve').modal('hide')


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


        $('#radio_button').change(function() {
            selected_value = $("input[name='BankAndUpi']:checked").val();
            if (selected_value == 1) {

                $('#order_hidden_type').val(selected_value);
                $('#bank_details').addClass('d-block');
                $('#bank_details').removeClass('d-none');
                $('#upi_details').removeClass('d-block');
                $('#upi_details').addClass('d-none');
            } else if (selected_value == 2) {
                $('#order_hidden_type').val(selected_value);
                $('#bank_details').removeClass('d-block');
                $('#bank_details').addClass('d-none');
                $('#upi_details').addClass('d-block');
                $('#upi_details').removeClass('d-none');

            }
        });

        var currentURL = window.location.href;
        var loadedURLLink = $('.userscomman a[href="' + currentURL + '"]');
        $(loadedURLLink).removeClass('btn-outline-primary');
        $(loadedURLLink).addClass('btn-primary');
    </script>
@endsection
