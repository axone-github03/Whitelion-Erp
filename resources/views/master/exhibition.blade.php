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
                    <h4 class="mb-sm-0 font-size-18">Exhibition

                    </h4>

                    <div class="page-title-right">


                        <button id="addBtnExhibition" class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#canvasExhibition" aria-controls="canvasExhibition"><i class="bx bx-plus font-size-16 align-middle me-2"></i>Exhibition </button>


                        <div class="offcanvas offcanvas-end" tabindex="-1" id="canvasExhibition" aria-labelledby="canvasExhibitionLable">
                            <div class="offcanvas-header">
                                <h5 id="canvasExhibitionLable">Exhibition</h5>
                                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                            </div>
                            <div class="offcanvas-body">

                                <div class="col-md-12 text-center loadingcls">






                                    <button type="button" class="btn btn-light waves-effect">
                                        <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading
                                    </button>


                                </div>






                                <form id="formExhibition" class="custom-validation" action="{{route('exhibition.save')}}" method="POST">

                                    @csrf

                                    <input type="hidden" name="exhibition_id" id="exhibition_id" value="0">



                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="main_master_name" class="form-label">Name</label>
                                                <input type="text" class="form-control" id="exhibition_name" name="exhibition_name" placeholder="Name" value="" required>


                                            </div>
                                        </div>

                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">


                                                <label class="form-label">City <code class="highlighter-rouge">*</code></label>
                                                <select class="form-control select2-ajax" id="exhibition_city_id" name="exhibition_city_id" required>
                                                </select>
                                                <div class="invalid-feedback">
                                                    Please select city.
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                    <label class="form-label">Users <code class="highlighter-rouge">*</code></label>
                                                    <select multiple="multiple" class="form-control select2-ajax select2-multiple" id="exhibition_sale_persons" name="exhibition_sale_persons[]" required>

                                                    </select>
                                                    <div class="invalid-feedback">
                                                        Please select sale person
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>



                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="main_master_status" class="form-label">Status</label>

                                                <select id="exhibition_status" name="exhibition_status" class="form-control select2-apply">
                                                    <option value="1">Live/ Upcomming</option>
                                                    <option value="0">Completed</option>



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
                                    <th>Users</th>
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
    var ajaxExhibitionDataURL = "{{route('exhibition.ajax')}}";
    var ajaxExhibitionDetailURL = "{{route('exhibition.detail')}}";
    var ajaxURLSearchCity = "{{route('search.city')}}";
    var ajaxSearchSalePerson = "{{route('exhibition.search.sales')}}";



    $("#exhibition_status").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#canvasExhibition")
    });
    var csrfToken = $("[name=_token").val();

    var exhibitionPageLength = getCookie('exhibitionPageLength') !== undefined ? getCookie('exhibitionPageLength') : 10;
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
        "pageLength": exhibitionPageLength,
        "ajax": {
            "url": ajaxExhibitionDataURL,
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
                "mData": "sale_persons"
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

        setCookie('exhibitionPageLength', len, 100);


    });


    $("#exhibition_city_id").select2({
        ajax: {
            url: ajaxURLSearchCity,
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
        placeholder: 'Search for a city',
        dropdownParent: $("#canvasExhibition")
    });



    $("#exhibition_sale_persons").select2({
        ajax: {
            url: ajaxSearchSalePerson,
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
        placeholder: 'Search for a sale persons',
        dropdownParent: $("#canvasExhibition")
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
        $('#formExhibition').ajaxForm(options);
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
            resetInputForm();
            $("#canvasExhibition").offcanvas('hide');

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


    $("#addBtnExhibition").click(function() {

        $("#canvasExhibitionLable").html("Add Exhibition");
        $("#formExhibition").show();
        $(".loadingcls").hide();
        resetInputForm();






    });


    function resetInputForm() {

        $('#formExhibition').trigger("reset");
        $("#exhibition_id").val(0);
        $("#exhibition_status").select2("val", "1");

    }

    function editView(id) {

        resetInputForm();

        $("#canvasExhibition").offcanvas('show');
        $("#canvasExhibitionLable").html("Edit Main Master #" + id);
        $("#formExhibition").hide();
        $(".loadingcls").show();

        $.ajax({
            type: 'GET',
            url: ajaxExhibitionDetailURL + "?id=" + id,
            success: function(resultData) {
                if (resultData['status'] == 1) {

                    $("#exhibition_id").val(resultData['data']['id']);
                    $("#exhibition_name").val(resultData['data']['name']);
                    $("#exhibition_status").select2("val", "" + resultData['data']['status'] + "");

                    if (typeof resultData['data']['city']['id'] !== "undefined") {
                        $("#exhibition_city_id").empty().trigger('change');
                        var newOption = new Option(resultData['data']['city']['text'], resultData['data']['city']['id'], false, false);
                        $('#exhibition_city_id').append(newOption).trigger('change');

                    }


                    if (resultData['data']['sale_persons'].length > 0) {
                        $("#exhibition_sale_persons").empty().trigger('change');
                        var selectedSalePersons = [];

                        for (var i = 0; i < resultData['data']['sale_persons'].length; i++) {

                            selectedSalePersons.push('' + resultData['data']['sale_persons'][i]['id'] + '');

                            var newOption = new Option(resultData['data']['sale_persons'][i]['text'], resultData['data']['sale_persons'][i]['id'], false, false);
                            $('#exhibition_sale_persons').append(newOption).trigger('change');


                        }
                        $("#exhibition_sale_persons").val(selectedSalePersons).change();

                    }








                    $(".loadingcls").hide();
                    $("#formExhibition").show();


                } else {

                    toastr["error"](resultData['msg']);

                }

            }
        });

    }
</script>
@endsection