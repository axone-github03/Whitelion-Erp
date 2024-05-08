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
        width: 40px;
    }

    #div_q_master_image {
        width: 100px;
        height: 100px;
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
                    <h4 class="mb-sm-0 font-size-18">Review Master</h4>
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
                                        <form id="formMainMaster" enctype="multipart/form-data" class="custom-validation" action="{{route('review.master.save')}}" method="POST">
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

                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="q_master_designation" class="form-label">Designation <code class="highlighter-rouge">*</code></label>
                                                        <input type="text" class="form-control" id="q_master_designation" name="q_master_designation" placeholder="Designation" value="" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    
                                                    <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                        <label for="q_master_city_id" class="form-label">Coutry/State/City <code class="highlighter-rouge">*</code></label>
                                                        <select class="form-control select2-ajax" id="q_master_city_id" name="q_master_city_id" required>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            Please select City.
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label for="q_master_review" class="form-label">Review </label>
                                                        <textarea class="form-control" id="q_master_review" name="q_master_review" rows="4" placeholder="Enter Remark"></textarea>
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
                                                <div class="col-3 d-flex align-items-center">
                                                        <label class="form-label" for="q_master_isfix">
                                                            <input id="q_master_isfix" name="q_master_isfix" type="checkbox">
                                                            <span class="text-danger">Is Fix</span>
                                                        </label>
                                                </div>
                                            </div>

                                            <div class="d-flex flex-wrap mt-2 gap-2">
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
                                    <th>Image</th>
                                    <th>Name/Designation/Review</th>
                                    <th>City</th>
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
    var ajaxMainMasterDataURL = '{{route("review.master.ajax")}}';
    var ajaxMainMasterDetailURL = '{{route("review.master.detail")}}';
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
                "mData": "image"
            },
            {
                "mData": "review"
            },
            {
                "mData": "city"
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

    }

    
    $("#addBtnMainMaster").click(function() {
        $("#canvasMainMasterLable").html("Add Item");
        $("#formMainMaster").show();
        $(".loadingcls").hide();
        resetInputForm();
    });


    function resetInputForm() {
        $("#formMainMaster").removeClass('was-validated');
        $('#formMainMaster').trigger("reset");
        $("#q_master_id").val(0);
        $("#q_master_status").select2("val", "1");
        $("#q_master_city_id").empty().trigger('change');
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
                    $("#q_master_designation").val(MainMaster['designation']);

                    $("#q_master_city_id").empty().trigger('change');
                    var newOption = new Option(MainMaster['city_text'], MainMaster['city_id'], false, false);
                    $('#q_master_city_id').append(newOption).trigger('change');

                    $("#q_master_review").val(MainMaster['review']);
                    $("#q_master_status").select2("val", "" + MainMaster['status'] + "");
                    if(MainMaster['status'] == 1){
                        document.getElementById("q_master_isfix").checked = true;
                    }else{
                        document.getElementById("q_master_isfix").checked = false;
                    }

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