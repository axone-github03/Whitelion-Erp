@extends('layouts.main')
@section('title', $data['title'])
@section('content')

<style type="text/css">
    td p {
        max-width: 100%;
        white-space: break-spaces;
        word-break: break-all;
    }
    thead th{
        padding: 8px;
        font-size: 1rem;
        text-align: center;
    }
    .summary_table td, .summary_table th{
        vertical-align: middle !important;
    }
    .summary_table thead{
        background-color: #eff2f7;
    }
    .summary_table tbody, .summary_table td, .summary_table tfoot .summary_table th, .summary_table thead, .summary_table tr{
        border-color: #eff2f7;
        border-width: 1px !important;
    }
    #imgPreview {
        width: 100% !important;
        height: 100% !important;
    }
    .product-img{
        width: 20rem;
    }

    #div_q_master_image {
        width: 250px;
        height: 155px;
        padding: 4px;
        margin: 0 auto;
        cursor: pointer;
    }
</style>
<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Banner Master</h4>
                    <div class="page-title-right">
                        
                        <button id="addBtnMainMaster" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#canvasMainMaster" role="button" type="button"><i class="bx bx-plus font-size-16 align-middle me-2"></i>Add Review</button>

                        <div class="modal fade" id="canvasMainMaster" data-bs-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="canvasMainMasterLable" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="canvasMainMasterLable"></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>

                                    <div class="modal-body">
                                        <div class="col-md-12 text-center loadingcls">
                                            <button type="button" class="btn btn-light waves-effect">
                                                <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading...
                                            </button>
                                        </div>
                                        <form id="formMainMaster" enctype="multipart/form-data" class="custom-validation" action="{{route('banner.master.save')}}" method="POST">
                                            @csrf
                                            <input type="hidden" name="q_master_id" id="q_master_id">

                                            <input type="file" name="q_master_image" id="q_master_image" accept="image/*" style="display:none" />
                                            <div class="row" id="row_q_master_image">
                                                <div class="col-lg-12">
                                                    <div id="div_q_master_image">
                                                        <img id="imgPreview" src="item_image/placeholder.png" alt="" class="img-thumbnail">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label for="q_master_name" class="form-label">Name <code class="highlighter-rouge">*</code></label>
                                                        <input type="text" class="form-control" id="q_master_name" name="q_master_name" placeholder="Name" value="" required>
                                                    </div>
                                                </div>
                                            </div>

                                            
                                            
                                            <div class="row">
                                                <div class="col-3">
                                                    <label for="q_master_status" class="form-label">Is Active </label>
                                                    <select id="q_master_status" name="q_master_status" class="form-control select2-apply">
                                                        <option value="1">Active</option>
                                                        <option value="0">Inactive</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="d-flex flex-wrap mt-2 gap-2">
                                                <button type="submit" id="save_banner" class="btn btn-primary waves-effect waves-light save_banner">
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
                                    <th>Image</th>
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
</div>
@csrf
@endsection('content')


@section('custom-scripts')

<script src="{{ asset('assets/ckeditor5/build/ckeditor.js') }}"></script>

<script src="{{ asset('assets/libs/parsleyjs/parsley.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/form-validation.init.js') }}"></script>
<script src="{{ asset('assets/js/pages/jquery.form.js') }}"></script>
<script type="text/javascript">
    var ajaxMainMasterDataURL = '{{route("banner.master.ajax")}}';
    var ajaxMainMasterDetailURL = '{{route("banner.master.detail")}}';
    var ajaxURLSearchCategory = '{{route("search.city.state.country")}}';

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
            "url": ajaxMainMasterDataURL,
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
                "mData": "image"
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

    $("#q_master_status").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#canvasMainMaster")
    });


    $(document).ready(function() {
        var options = {
            beforeSubmit: showRequest, // pre-submit callback
            success: showResponse // post-submit callback

        };

        $('#formMainMaster').ajaxForm(options);
    });

    function isNumber(evt) {
        evt = (evt) ? evt : window.event;
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            return false;
        }
        return true;
    }

    function showRequest(formData, jqForm, options) {
        var queryString = $.param(formData);

        return true;
    }

    // post-submit callback
    function showResponse(responseText, statusText, xhr, $form) {


        if (responseText['status'] == 1) {
            toastr["success"](responseText['msg']);
            reloadTable();
            resetInputForm();
            $("#canvasMainMaster").modal('hide');

        } else if (responseText['status'] == 0) {

            toastr["error"](responseText['msg']);

        }
        $("#save_banner").html('Save');
        $("#save_banner").removeAttr('disabled');

    }
    // if ($("formMainMaster").valid()){
    //     $("#save_banner").html('Save');
    //     $("#save_banner").removeAttr('disabled');
    // }
    $("#save_banner").click(function() {
        $("#save_banner").html('<i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i> <span  >Saving...</span>');
        // $("#save_banner").prop('disabled', true);
    });
    
    $("#addBtnMainMaster").click(function() {
        $("#canvasMainMasterLable").html("Add Item");
        $("#formMainMaster").show();
        $(".loadingcls").hide();
        $("#save_banner").html('Save');
        $("#save_banner").removeAttr('disabled');
        resetInputForm();
    });


    function resetInputForm() {
        $("#formMainMaster").removeClass('was-validated');
        $('#formMainMaster').trigger("reset");
        $("#q_master_id").val(0);
        $("#q_master_status").select2("val", "1");
        $("#imgPreview").attr('src', 'https://erp.whitelion.in/assets/images/favicon.ico');
    }

    function editView(id) {

        resetInputForm();

        $("#canvasMainMaster").modal('show');
        $("#canvasMainMasterLable").html("Edit Main Master #" + id);
        $("#formMainMaster").hide();
        $(".loadingcls").show();

        $.ajax({
            type: 'GET',
            url: ajaxMainMasterDetailURL + "?id=" + id,
            success: function(resultData) {
                if (resultData['status'] == 1) {
                    var MainMaster = resultData['data']['MainMaster'];
                    $("#q_master_id").val(MainMaster['id']);
                    $("#q_master_name").val(MainMaster['name']);
                    $("#q_master_status").select2("val", "" + MainMaster['status'] + "");

                    $("#imgPreview").attr('src', MainMaster['image']);

                    $(".loadingcls").hide();
                    $("#formMainMaster").show();


                } else {

                    toastr["error"](resultData['msg']);

                }

            }
        });

    }


    $("#q_master_city_id").select2({
        ajax: {
            url: ajaxURLSearchCategory,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function(data, params) {
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
        placeholder: 'Search City',
        dropdownParent: $("#canvasMainMaster")
    });


    $(document).ready(() => {
        $('#q_master_image').change(function() {
            const file = this.files[0];
            if (file) {
                let reader = new FileReader();
                reader.onload = function(event) {
                    $('#imgPreview').attr('src', event.target.result);
                }
                reader.readAsDataURL(file);
            }
        });
    });

    $('#div_q_master_image').click(function() {
        $('#q_master_image').trigger('click');
    });
</script>
@endsection