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

    #div_q_item_image {
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
                    <h4 class="mb-sm-0 font-size-18">Item Master</h4>
                    <div class="page-title-right">
                        @if(isAdminOrCompanyAdmin()==1)
                        <a href="{{route('quot.item.master.export')}}" target="_blank" class="btn btn-info" type="button"><i class="bx bx-export font-size-16 align-middle me-2"></i>Export </a>
                        @endif
                        <button id="addBtnMainMaster" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#canvasMainMaster" role="button" type="button"><i class="bx bx-plus font-size-16 align-middle me-2"></i>Add Item</button>

                        <div class="modal fade" id="canvasMainMaster" data-bs-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="canvasMainMasterLable" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="canvasMainMasterLable">Upload Price Excel</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>

                                    <div class="modal-body">
                                        <div class="col-md-12 text-center loadingcls">
                                            <button type="button" class="btn btn-light waves-effect">
                                                <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading...
                                            </button>
                                        </div>
                                        <form id="formMainMaster" enctype="multipart/form-data" class="custom-validation" action="{{route('quot.item.master.save')}}" method="POST">
                                            @csrf
                                            <input type="hidden" name="q_item_master_id" id="q_item_master_id">

                                            <input type="file" name="q_item_image" id="q_item_image" accept="image/*" style="display:none" />
                                            <div class="row" id="row_q_item_image">
                                                <div class="col-lg-12">
                                                    <div id="div_q_item_image">
                                                        <img id="imgPreview" src="item_image/placeholder.png" alt="" class="img-thumbnail">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label for="q_item_master_name" class="form-label">Item Name <code class="highlighter-rouge">*</code></label>
                                                        <input type="text" class="form-control" id="q_item_master_name" name="q_item_master_name" placeholder="Name" value="" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label for="q_item_master_code" class="form-label">Short Name </label>
                                                        <input type="text" class="form-control" id="q_item_master_code" name="q_item_master_code" placeholder="" value="" readonly>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label for="q_item_master_app_display_name" class="form-label">App Display Name </label>
                                                        <input type="text" class="form-control" id="q_item_master_app_display_name" name="q_item_master_app_display_name" placeholder="Name" value="">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label for="q_item_master_module" class="form-label">Module <code class="highlighter-rouge">*</code></label>
                                                        <input type="number" class="form-control" id="q_item_master_module" name="q_item_master_module" onkeypress="return isNumber(event);" placeholder="00" value="" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label for="q_item_master_max_module" class="form-label">Max Module <code class="highlighter-rouge">*</code></label>
                                                        <input type="number" class="form-control" id="q_item_master_max_module" name="q_item_master_max_module" onkeypress="return isNumber(event);" placeholder="00" value="" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label for="q_item_master_sequence" class="form-label">Sequence </label>
                                                        <input type="number" class="form-control" id="q_item_master_sequence" name="q_item_master_sequence" onkeypress="return isNumber(event);" placeholder="" value="0" required>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                    <label for="q_item_master_category_id" class="form-label">Category <code class="highlighter-rouge">*</code></label>
                                                    <select class="form-control select2-ajax select2-multiple" multiple="multiple" id="q_item_master_category_id" name="q_item_master_category_id[]" required>
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        Please select Category.
                                                    </div>
                                                </div>
                                            </div>

                                            
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label for="q_item_master_cgst" class="form-label">CGST (%)</label>
                                                        <input type="number" step="0.01" class="form-control" id="q_item_master_cgst" name="q_item_master_cgst" placeholder="IGST" value="9">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label for="q_item_master_sgst" class="form-label">SGST (%)</label>
                                                        <input type="number" step="0.01" class="form-control" id="q_item_master_sgst" name="q_item_master_sgst" placeholder="IGST" value="9">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label for="q_item_master_igst" class="form-label">IGST (%)</label>
                                                        <input type="number" step="0.01" class="form-control" id="q_item_master_igst" name="q_item_master_igst" placeholder="IGST" value="18">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label for="q_item_master_status" class="form-label">Is Active </label>

                                                        <select id="q_item_master_status" name="q_item_master_status" class="form-control select2-apply">
                                                            <option value="1">Active</option>
                                                            <option value="0">Inactive</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>


                                            <table border="1" class="summary_table mb-3 table table-bordered table-sm">
                                                <thead>
                                                    <tr>
                                                        <th colspan="6" style="text-align: center;">Excel Summary Table</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <th rowspan="2" style="text-align: center;">Whitelion</th>
                                                        <td>Touch on/off</td>
                                                        <td>Touch Fan Regulator</td>
                                                        <td>Plug</td>
                                                        <td>Special</td>
                                                        <td>Wl Accessories</td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <input type="number" class="form-control" id="q_item_master_touch_on_off" name="q_item_master_touch_on_off" placeholder="Touch on/off" min="0" max="100" value="0" required>
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control" id="q_item_master_touch_fan_regulator" name="q_item_master_touch_fan_regulator" placeholder="Touch Fan Regulator"  min="0" max="100" value="0" required>
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control" id="q_item_master_wl_plug" name="q_item_master_wl_plug" placeholder="Wl Plug"  min="0" max="100" value="0" required>
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control" id="q_item_master_special" name="q_item_master_special" placeholder="Special"  min="0" max="100" value="0" required>
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control" id="q_item_master_wl_accessories" name="q_item_master_wl_accessories" placeholder="Wl_Accessories"  min="0" max="100" value="0" required>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th rowspan="2" style="text-align: center;">Others</th>
                                                        <td>Normal Switch</td>
                                                        <td>Normal Fan Regulator</td>
                                                        <td>Plug</td>
                                                        <td>Other</td>
                                                        <td rowspan="2"></td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <input type="number" class="form-control" id="q_item_master_normal_switch" name="q_item_master_normal_switch" placeholder="Normal Switch"  min="0" max="100" value="0" required>
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control" id="q_item_master_normal_fan_regulator" name="q_item_master_normal_fan_regulator" placeholder="Normal Fan Regulator"  min="0" max="100" value="0" required>
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control" id="q_item_master_other_plug" name="q_item_master_other_plug" placeholder="Other Plug"  min="0" max="100" value="0" required>
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control" id="q_item_master_other" name="q_item_master_other" placeholder="Others"  min="0" max="100" value="0" required>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="q_item_master_remark" class="form-label">Remark </label>
                                                        <textarea class="form-control" id="q_item_master_remark" name="q_item_master_remark" rows="4" placeholder="Enter Remark"></textarea>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="col-md-12">
                                                        <label class="form-label" for="q_item_master_isspecial">
                                                            <input id="q_item_master_isspecial" name="q_item_master_isspecial" type="checkbox">
                                                            <span class="text-danger">Is Special</span>
                                                            <input type="hidden" name="q_item_master_isspecial_value" id="q_item_master_isspecial_value" value="0">
                                                        </label>
                                                    </div>
                                                    <div class="col-md-12 additional_remark">
                                                        <div class="mb-3">
                                                            <label for="q_item_master_additional_remark" class="form-label">Additional Remark </label>
                                                            <textarea class="form-control" id="q_item_master_additional_remark" name="q_item_master_additional_remark" rows="2" placeholder="Enter Remark"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <textarea class="form-control" id="additional_info" name="additional_info"></textarea>
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
                                    <th>Item / Category Name</th>
                                    <th>Module</th>
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

<script src="{{ asset('assets/ckeditor5/build/ckeditor.js') }}"></script>

<script src="{{ asset('assets/libs/parsleyjs/parsley.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/form-validation.init.js') }}"></script>
<script src="{{ asset('assets/js/pages/jquery.form.js') }}"></script>
<script type="text/javascript">
    var ajaxMainMasterDataURL = '{{route("quot.item.master.ajax")}}';
    var ajaxMainMasterDetailURL = '{{route("quot.item.master.detail")}}';
    var ajaxURLSearchCategory = '{{route("quot.item.search.category")}}';
    var ajaxItemMasterDeleteURL = '{{route("quot.item.master.delete")}}';

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
                "mData": "itemname"
            },
            {
                "mData": "module"
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

    $(".additional_remark").hide();
    $("#q_item_master_isspecial").change(function() {
        if ($("#q_item_master_isspecial").is(':checked')) {
            $(".additional_remark").show(); // checked
            $("#q_item_master_additional_remark").prop('required', true);
            $("#q_item_master_isspecial_value").val('1');
        } else {
            $(".additional_remark").hide();
            $("#q_item_master_additional_remark").prop('required', false);
            $("#q_item_master_isspecial_value").val('0');
        }
    });


    $('#datatable').on('length.dt', function(e, settings, len) {
        setCookie('mainMasterPageLength', len, 100);
    });

    $("#q_item_master_status").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#canvasMainMaster")
    });

    function generateHierarchyCode(dInput) {
        dInput = dInput.replace(/[_\W]+/g, "_")
        dInput = dInput.toUpperCase();
        $("#q_item_master_code").val(dInput)
    }

    $("#q_item_master_name").keyup(function() {
        generateHierarchyCode(this.value);
    });

    $("#q_item_master_name").change(function() {
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

    function isNumber(evt) {
        evt = (evt) ? evt : window.event;
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            return false;
        }
        return true;
    }

    function showRequest(formData, jqForm, options) {
        generateHierarchyCode($("#q_item_master_name").val());
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
            $("#canvasMainMaster").modal('hide');

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

    const watchdog = new CKSource.EditorWatchdog();
    let theEditor;
    class MyUploadAdapter {
        constructor(loader) {
            this.loader = loader;
        }

        upload() {
            return this.loader.file
                .then(file => new Promise((resolve, reject) => {
                    this._initRequest();
                    this._initListeners(resolve, reject, file);
                    this._sendRequest(file);
                }));
        }

        abort() {
            if (this.xhr) {
                this.xhr.abort();
            }
        }

        _initRequest() {
            const xhr = this.xhr = new XMLHttpRequest();

            xhr.open('POST', "{{route('quot.item.master.upload-image-additional-info', ['_token' => csrf_token() ])}}", true);
            xhr.responseType = 'json';
        }

        _initListeners(resolve, reject, file) {
            const xhr = this.xhr;
            const loader = this.loader;
            const genericErrorText = `Couldn't upload file: ${ file.name }.`;

            xhr.addEventListener('error', () => reject(genericErrorText));
            xhr.addEventListener('abort', () => reject());
            xhr.addEventListener('load', () => {
                const response = xhr.response;

                if (!response || response.error) {
                    return reject(response && response.error ? response.error.message : genericErrorText);
                }

                resolve(response);
            });

            if (xhr.upload) {
                xhr.upload.addEventListener('progress', evt => {
                    if (evt.lengthComputable) {
                        loader.uploadTotal = evt.total;
                        loader.uploaded = evt.loaded;
                    }
                });
            }
        }

        _sendRequest(file) {
            const data = new FormData();

            data.append('upload', file);

            this.xhr.send(data);
        }
    }

    function MyCustomUploadAdapterPlugin(editor) {
        editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
            return new MyUploadAdapter(loader);
        };
    }

    watchdog.create(document.querySelector('#additional_info'), {
            extraPlugins: [MyCustomUploadAdapterPlugin],
        })
        .catch(error => {
            console.error(error);
        });


    window.watchdog = watchdog;

    watchdog.setCreator((element, config) => {
        return CKSource.Editor
            .create(element, config)
            .then(editor => {
                theEditor = editor;
                // return editor;
            })
    });



    $("#addBtnMainMaster").click(function() {
        $("#canvasMainMasterLable").html("Add Item");
        $("#formMainMaster").show();
        $(".loadingcls").hide();
        resetInputForm();
    });


    function resetInputForm() {
        $("#formMainMaster").removeClass('was-validated');
        $('#formMainMaster').trigger("reset");
        $("#q_item_master_id").val(0);
        $("#q_item_master_status").select2("val", "1");
        $("#q_item_master_category_id").empty().trigger('change');
        theEditor.setData('');
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
                    var ItemDetails = resultData['data']['item_details'];
                    $("#q_item_master_id").val(MainMaster['id']);
                    $("#q_item_master_name").val(MainMaster['itemname']);
                    $("#q_item_master_sequence").val(MainMaster['app_sequence']);
                    $("#q_item_master_app_display_name").val(MainMaster['app_display_name']);
                    $("#q_item_master_code").val(MainMaster['shortname']);
                    $("#q_item_master_module").val(MainMaster['module']);
                    $("#q_item_master_max_module").val(MainMaster['max_module']);
                    $("#q_item_master_igst").val(MainMaster['igst_per']);
                    $("#q_item_master_cgst").val(MainMaster['cgst_per']);
                    $("#q_item_master_sgst").val(MainMaster['sgst_per']);
                    $("#imgPreview").attr('src', "" + MainMaster['image'] + "");
                    if (MainMaster['additional_info'] != null) {
                        theEditor.setData(MainMaster['additional_info']);
                    }


                    if (MainMaster['category'].length > 0) {
                        $("#q_item_master_category_id").empty().trigger('change');
                        var selectedSalePersons = [];
                        for (var i = 0; i < MainMaster['category'].length; i++) {
                            selectedSalePersons.push('' + MainMaster['category'][i]['id'] + '');
                            var newOption = new Option(MainMaster['category'][i]['text'], MainMaster['category'][i]['id'], false, false);
                            $('#q_item_master_category_id').append(newOption).trigger('change');
                        }
                        $("#q_item_master_category_id").val(selectedSalePersons).change();
                    }

                    $("#q_item_master_remark").val(MainMaster['remark']);
                    $("#q_item_master_status").select2("val", "" + MainMaster['isactive'] + "");

                    if (MainMaster['is_special'] == 1) {
                        $(".additional_remark").show();
                        $("#q_item_master_additional_remark").prop('required', true);
                        $("#q_item_master_isspecial_value").val('1');
                        document.getElementById("q_item_master_isspecial").checked = true;
                        $("#q_item_master_additional_remark").val(resultData['data']['additional_remark']);
                    }
                    if(ItemDetails == 0)
                    {
                        $('#q_item_master_touch_on_off').val(0);
                        $('#q_item_master_touch_fan_regulator').val(0);
                        $('#q_item_master_wl_plug').val(0);
                        $('#q_item_master_special').val(0);
                        $('#q_item_master_wl_accessories').val(0);
                        // $('#q_item_master_rc2').val(0);
                        $('#q_item_master_normal_switch').val(0);
                        $('#q_item_master_normal_fan_regulator').val(0);
                        $('#q_item_master_other_plug').val(0);
                        $('#q_item_master_other').val(0);
                    }
                    else
                    {
                        $('#q_item_master_touch_on_off').val(ItemDetails['touch_on_off']);
                        $('#q_item_master_touch_fan_regulator').val(ItemDetails['touch_fan_regulator']);
                        $('#q_item_master_wl_plug').val(ItemDetails['wl_plug']);
                        $('#q_item_master_special').val(ItemDetails['special']);
                        $('#q_item_master_wl_accessories').val(ItemDetails['wl_accessories']);
                        // $('#q_item_master_rc2').val(ItemDetails['rc2']);
                        $('#q_item_master_normal_switch').val(ItemDetails['normal_switch']);
                        $('#q_item_master_normal_fan_regulator').val(ItemDetails['normal_fan_regulator']);
                        $('#q_item_master_other_plug').val(ItemDetails['other_plug' ]);
                        $('#q_item_master_other').val(ItemDetails['other']);
                    }
                    
                    // $('#').val(ItemDetails['item_id']);
                    $(".loadingcls").hide();
                    $("#formMainMaster").show();


                } else {

                    toastr["error"](resultData['msg']);

                }

            }
        });

    }


    $("#q_item_master_category_id").select2({
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
        placeholder: 'Search for a Category',
        dropdownParent: $("#canvasMainMaster")
        // dropdownParent: $("#WlmstCompany .modal-body")
    });


    // $("#q_group_master_company_id").change(function() {
    //     alert($('#q_group_master_company_id').val());
    // });
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
                        url: ajaxItemMasterDeleteURL + "?id=" + id,
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

    $(document).ready(() => {
        $('#q_item_image').change(function() {
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

    $('#div_q_item_image').click(function() {
        $('#q_item_image').trigger('click');
    });
</script>
@endsection