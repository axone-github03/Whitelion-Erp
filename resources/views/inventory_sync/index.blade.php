@extends('layouts.main')
@section('title', $data['title'])
@section('content')

    <style type="text/css">
        td p {
            max-width: 100%;
            white-space: break-spaces;
            word-break: break-all;
        }

        .active_lead {
            background-color: rgb(141, 226, 255);
            border-color: transparent;
            -webkit-box-shadow: 0 0.75rem 1.5rem rgba(18, 38, 63, .03);
            box-shadow: 0 0.75rem 1.5rem rgba(18, 38, 63, .03);
        }
    </style>
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">Inventory Sync</h4>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <table id="datatable" class="table table-striped dt-responsive  nowrap w-100">
                                <thead class="text-center">
                                    <tr>
                                        <th>Production Inventory</th>
                                        <th>Quotation Inventory</th>
                                        <th>Sales Inventory</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div> <!-- end col -->
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
        var ajaxInventorySyncDataURL = '{{ route('inventory.sync.ajax') }}';
        var ajaxInventorySyncSaveURL = '{{ route('inventory.sync.save') }}';

        var csrfToken = $("[name=_token").val();
        var mainMasterPageLength = getCookie('mainMasterPageLength') !== undefined ? getCookie('mainMasterPageLength') : 10;
        
        var table = $('#datatable').DataTable({
            "aoColumnDefs": [{
                "bSortable": true,
            }],
            "order": [
                [0, 'desc']
            ],
            "processing": true,
            "serverSide": true,
            "pageLength": mainMasterPageLength,
            "ajax": {
                "url": ajaxInventorySyncDataURL,
                "type": "POST",
                "data": {
                    "_token": csrfToken,
                }
            },
            "aoColumns": [{
                    "mData": "prod_itemname"
                },
                {
                    "mData": "quot_itemname"
                },
                {
                    "mData": "so_itemname"
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

        function saveInventorySync(prod_composite_item_id, count) {
            $.ajax({
                type: 'POST',
                url: ajaxInventorySyncSaveURL, 
                data: {
                    "_token": csrfToken,
                    "prod_composite_item_id" : prod_composite_item_id,
                    "quot_item_price_id" : $('#quot_item_id_'+count).val(),
                    "so_item_id" : $('#so_item_id_'+count).val()
                },
                success: function(resultData) {
                    if (resultData['status'] == 1) {
                        toastr["success"](resultData['msg']);
                    } else {
                        toastr["error"](resultData['msg']);
                    }
                }
            })
        }
    </script>
@endsection
