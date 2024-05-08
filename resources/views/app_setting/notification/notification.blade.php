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
        width: 40%;
    }

    #div_q_master_image {
        width: 200px;
        height: 170px;
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
                    <h4 class="mb-sm-0 font-size-18">Notification Master</h4>
                   
                </div>
            </div>
        </div>
        <!-- end page title -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body" id="canvasMainMaster">
                        <form id="formMainMaster" enctype="multipart/form-data" class="custom-validation" action="{{route('notification.master.save')}}" method="POST">
                            @csrf

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
                                    
                                    <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                        <label for="q_master_user_type_id" class="form-label">UserType <code class="highlighter-rouge">*</code></label>
                                        <select class="form-control select2-ajax select2-multiple" multiple="multiple" id="q_master_user_type_id" name="q_master_user_type_id[]" required>
                                        </select>
                                        <div class="invalid-feedback">
                                            Please select UserType.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="q_master_title" class="form-label">Title <code class="highlighter-rouge">*</code></label>
                                        <input type="text" class="form-control" id="q_master_title" name="q_master_title" placeholder="Name" value="" required>
                                    </div>
                                </div>
                                
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="q_master_message" class="form-label">Message </label>
                                        <textarea class="form-control" id="q_master_message" name="q_master_message" rows="4" placeholder="Enter Remark"></textarea>
                                    </div>
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
    var ajaxURLSearchUserType = '{{route("search.notification.usertype")}}';

    var csrfToken = $("[name=_token").val();

    
    $(document).ready(function() {
        var options = {
            beforeSubmit: showRequest, // pre-submit callback
            success: showResponse // post-submit callback

        };

        $('#formMainMaster').ajaxForm(options);
    });

   
    function showRequest(formData, jqForm, options) {
        var queryString = $.param(formData);

        return true;
    }

    // post-submit callback
    function showResponse(responseText, statusText, xhr, $form) {


        if (responseText['status'] == 1) {
            toastr["success"](responseText['msg']);
            resetInputForm();

        } else {
            toastr["error"](responseText['msg']);
        }

    }


    function resetInputForm() {
        $("#formMainMaster").removeClass('was-validated');
        $('#formMainMaster').trigger("reset");
        $("#q_master_user_type_id").empty().trigger('change');
        $("#imgPreview").attr('src', 'https://erp.whitelion.in/assets/images/favicon.ico');
    }


    $("#q_master_user_type_id").select2({
        ajax: {
            url: ajaxURLSearchUserType,
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
        placeholder: 'Search UserType',
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
        resetInputForm();
    });

    $('#div_q_master_image').click(function() {
        $('#q_master_image').trigger('click');
    });
</script>
@endsection