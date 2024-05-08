@extends('layouts.main')
@section('title', $data['title'])
@section('content')
<style type="text/css">
    .div-end-line {

        padding: 5px 0px;
        border-top: 1px solid #f1f1f1;

    }
</style>

<div class="page-content">
    <div class="container-fluid">
        @php
        $inquiryStatus=getInquiryStatus();
        @endphp
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Inquiry Question</h4>
                    <div class="page-title-right">
                        <button id="addBtnInquiryQuestion" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#modalInquiry" role="button"><i class="bx bx-plus font-size-16 align-middle me-2"></i>Inquiry Question</button>
                        <div class="modal fade" id="modalInquiry" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" role="dialog" aria-labelledby="modalInquiryLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalInquiryLabel">Inquiry Question</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form id="formInquiryQuestion" action="{{route('inquiry.question.save')}}" method="POST" class="needs-validation" novalidate>
                                        <div class="modal-body">
                                            @csrf
                                            <div class="col-md-12 text-center loadingcls">
                                                <button type="button" class="btn btn-light waves-effect">
                                                    <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading
                                                </button>
                                            </div>
                                            <input type="hidden" name="inquiry_questions_id" id="inquiry_questions_id">

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label for="inquiry_questions_status" class="form-label">Inquiry Status</label>
                                                        <select class="form-select select2-apply" id="inquiry_questions_status" name="inquiry_questions_status" required>
                                                            @foreach($inquiryStatus as $key=>$value)
                                                            @if($value['has_question'] != 0)
                                                            <option value="{{$value['id']}}">{{$value['name']}}</option>
                                                            @endif
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">

                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label for="is_depend_on_answer" class="form-label">is depend on prevous question answer?</label>
                                                        <select class="form-control select2-ajax" id="is_depend_on_answer" name="is_depend_on_answer">
                                                            <option value="0">No</option>
                                                            <option value="1">Yes</option>

                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row row-depend">

                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label for="depended_question_id" class="form-label">Depended Question ( Option/Multi Option/Checkbox/Multi Checkbox - Previous sequence or status )</label>
                                                        <select class="form-control select2-ajax" id="depended_question_id" name="depended_question_id">


                                                        </select>

                                                        <div class="invalid-feedback">
                                                            Please select depended question.
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mb-3 row-depend">

                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label for="inquiry_first_name" class="form-label">Depended Question Answer</label>
                                                        <select class="form-control select2-ajax" id="depended_question_answer" name="depended_question_answer">


                                                        </select>

                                                        <div class="invalid-feedback">
                                                            Please select depended answer.
                                                        </div>

                                                    </div>
                                                </div>




                                            </div>


                                            <div class="row" id="sec_inquiry_questions_question">
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label for="inquiry_questions" class="form-label">Question</label>
                                                        <textarea id="inquiry_questions_question" name="inquiry_questions_question" class="form-control" rows="3" required></textarea>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label for="inquiry_questions_type" class="form-label">Question Type</label>
                                                        <select class="form-select select2-apply" id="inquiry_questions_type" name="inquiry_questions_type" required>
                                                            <option value="0">Text</option>
                                                            <option value="5">Number</option>
                                                            <option value="1">Option</option>
                                                            <option value="4">Multi Option</option>
                                                            <option value="2">File</option>
                                                            <option value="7">Multi File</option>
                                                            <option value="3">Checkbox</option>
                                                            <option value="6">Multi checkbox</option>

                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row" id="sec_inquiry_questions_options">
                                                <div class="col-md-12">
                                                    <label>Question Option:</label>

                                                    <div class="inner-dynamic">
                                                    </div>
                                                    <div class="mb-3">
                                                        <input type="button" class="btn btn-success" id="addMoreOption" value="Add New Option" />
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label for="inquiry_questions_is_static" class="form-label">Answer Type</label>
                                                        <select class="form-select select2-apply" id="inquiry_questions_is_static" name="inquiry_questions_is_static">
                                                            <option value="0">Dynamic</option>
                                                            <option value="1">Static</option>


                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label for="inquiry_questions_is_required" class="form-label">Answer Required</label>
                                                        <select class="form-control select2-apply" id="inquiry_questions_is_required" name="inquiry_questions_is_required">
                                                            <option value="1">Yes</option>
                                                            <option value="0">No</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary">Save</button>
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



        <!-- start datatable section -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <input type="hidden" name="selected_status" id="selected_status" value="{{$data['status']}}">

                        @php

                        @endphp
                        <div class="d-flex flex-wrap gap-2 userscomman">

                            @foreach($inquiryStatus as $key=>$value)

                            @if($value['has_question'] != 0)

                            <a href="{{route('inquery.question')}}?status={{$value['id']}}" class="btn btn-sm btn-outline-primary waves-effect waves-light">{{$value['name']}}</a>
                            @endif

                            @endforeach
                        </div>
                        <br>
                        <table id="datatable" class="table table-striped dt-responsive  nowrap w-100">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Sequence</th>
                                    <th>Type</th>
                                    <th>Question</th>
                                    <th>Is Static</th>
                                    <th>Required</th>
                                    <th>Is Depended</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div> <!-- end col -->
        </div> <!-- end datatable section -->

    </div>
    <!-- container-fluid -->
</div>
<!-- End Page-content -->

@csrf
@endsection('content')
@section('custom-scripts')
<style>
    .isDisabled {
        color: currentColor;
        opacity: 0.5;
        text-decoration: none;
        pointer-events: none;
    }
</style>
<script src="{{ asset('assets/libs/parsleyjs/parsley.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/form-validation.init.js') }}"></script>
<script src="{{ asset('assets/js/pages/jquery.form.js') }}"></script>
<script src="{{ asset('assets/libs/trsorting/trsorting.js') }}"></script>
<!-- form repeater js -->

<script type="text/javascript">
    var ajaxURL = "{{route('inquiry.question.ajax')}}";
    var ajaxURLInquiryQuestionDetail = "{{route('inquiry.question.detail')}}";
    var ajaxInquiryQuestionDeleteURL = "{{route('inquiry.question.delete')}}";
    var ajaxInquiryQuestionOrderChange = "{{route('inquiry.question.order.change')}}";
    var ajaxInquiryDependedQuestion = "{{route('inquiry.question.depended.question')}}";
    var ajaxInquiryDependedQuestionAnswer = "{{route('inquiry.question.depended.question.answer')}}";
    var csrfToken = $("[name=_token").val();

    $("#addBtnInquiryQuestion").click(function() {
        resetInputForm();
        $('.inner-dynamic').html('');
        optionIndex = 0;
        $("#modalInquiryLabel").html("Add Inquiry Question");
        $(".loadingcls").hide();
        $("#inquiry_questions_id").val(0);


    });

    function resetInputForm() {
        $('#formInquiryQuestion').trigger("reset");
        $("#inquiry_questions_status").select2("val", "1");
        $("#is_depend_on_answer").select2("val", "0");
        $("#inquiry_questions_type").select2("val", "0");
        $("#inquiry_questions_is_static").select2("val", "0");
        $('#inquiry_questions_is_static').prop('disabled', true);
        $("#inquiry_questions_is_required").select2("val", "1");
        $("#formInquiryQuestion").removeClass('was-validated');
        $("#inquiry_questions_type").prop("disabled", false);

    }

    //select2-apply

    $(".row-depend").hide();


    $("#is_depend_on_answer").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#modalInquiry .modal-body")
    });

    $("#is_depend_on_answer").change(function() {

        if ($(this).val() == 1) {
            $(".row-depend").show();

            $("#depended_question_id").attr('required', true);
            $("#depended_question_answer").attr('required', true);

        } else {
            $(".row-depend").hide();
            $("#depended_question_id").removeAttr('required');
            $("#depended_question_answer").removeAttr('required');
        }

    });

    $('#depended_question_id').on('change', function() {
        $("#depended_question_answer").empty().trigger('change');
    });
    $('#inquiry_questions_status').on('change', function() {
        $("#depended_question_id").empty().trigger('change');
        $("#depended_question_answer").empty().trigger('change');
    });




    $("#depended_question_id").select2({
        ajax: {
            url: ajaxInquiryDependedQuestion,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    'inquiry_questions_id': function() {
                        return $("#inquiry_questions_id").val()
                    },
                    'status': function() {
                        return $("#inquiry_questions_status").val()
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
        placeholder: 'Search for parent sequence question',
        dropdownParent: $("#modalInquiry .modal-body")
    });

    $("#depended_question_answer").select2({
        ajax: {
            url: ajaxInquiryDependedQuestionAnswer,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    'inquiry_questions_id': function() {
                        return $("#depended_question_id").val()
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
        placeholder: 'Search for depended question answer',
        dropdownParent: $("#modalInquiry .modal-body")
    });



    $("#inquiry_questions_status").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#modalInquiry .modal-body")
    });
    $("#inquiry_questions_type").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#modalInquiry .modal-body")
    });
    $("#inquiry_questions_is_static").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#modalInquiry .modal-body")
    });
    $("#inquiry_questions_is_required").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#modalInquiry .modal-body")
    });

    $("#sec_inquiry_questions_options").hide();



    $('#inquiry_questions_type').on('change', function() {






        if ($(this).val() == 1 || $(this).val() == 4 || $(this).val() == 6) {
            if (optionIndex == 0) {
                addMoreOption();
            }

            $("#sec_inquiry_questions_options").show();
            $('#question_option_id_0').prop('required', true);


        } else {
            $("#sec_inquiry_questions_options").hide();
            $('#question_option_id_0').prop('required', false);
            //$("#question_option_1").attr('required');
        }




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
        $('#formInquiryQuestion').ajaxForm(options);
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
            $("#modalInquiry").modal('hide');
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

    var table = $('#datatable').DataTable({
        "aoColumnDefs": [{
            "bSortable": false,
            "aTargets": [0, 1, 2, 3, 4, 5, 6, 7]
        }],
        "order": [
            [1, 'asc']
        ],
        "processing": true,
        "serverSide": true,
        "aLengthMenu": [100],
        "ajax": {
            "url": ajaxURL,
            "type": "POST",
            "data": {
                "_token": csrfToken,
                "status": function() {
                    return $("#selected_status").val()
                },
            }
        },
        "aoColumns": [{
                "mData": "id"
            },
            {
                "mData": "sequence"
            },
            {
                "mData": "type"
            },
            {
                "mData": "question"
            },
            {
                "mData": "is_static"
            },
            {
                "mData": "is_required"
            },
            {
                "mData": "is_depend_on_answer"
            },
            {
                "mData": "action"
            }
        ]
    });


    $("#datatable").rowSorter({
        handler: "a.sort-handler",
        onDrop: function(tbody, row, index, oldIndex) {

            $.ajax({
                type: 'GET',
                url: ajaxInquiryQuestionOrderChange + "?old_index=" + oldIndex + "&index=" + index + "&status=" + $("#selected_status").val(),
                success: function(resultData) {

                    reloadTable();

                }




            });
        }
    });



    function reloadTable() {
        table.ajax.reload(null, false);
    }

    function applyFilter() {
        reloadTable();
    }

    $('#status_id').on('change', function() {
        applyFilter();
    });

    function editView(id) {
        editModeLoading = 1;
        resetInputForm();
        $("#modalInquiry").modal('show');
        $("#modalInquiryLabel").html("Edit Inquiry Question #" + id);
        $("#formInquiryQuestion .row").hide();
        $(".loadingcls").show();
        $("#modalInquiry .modal-footer").hide();
        $.ajax({
            type: 'GET',
            url: ajaxURLInquiryQuestionDetail + "?id=" + id,
            success: function(resultData) {

                if (resultData['status'] == 1) {
                    $("#inquiry_questions_id").val(resultData['data']['id']);
                    $("#inquiry_questions_question").val(resultData['data']['question']);
                    $("#inquiry_questions_status").select2("val", "" + resultData['data']['status'] + "");
                    $("#inquiry_questions_type").select2("val", "" + resultData['data']['type'] + "");
                    $("#inquiry_questions_is_static").select2("val", "" + resultData['data']['is_static'] + "");

                    $("#inquiry_questions_is_static").prop("disabled", true);
                    // $("#inquiry_questions_type").prop("disabled", true);


                    $("#inquiry_questions_is_required").select2("val", "" + resultData['data']['is_required'] + "");
                    $(".loadingcls").hide();
                    $("#formInquiryQuestion .row").show();
                    $("#modalInquiry .modal-footer").show();

                    setTimeout(function() {

                        $("#is_depend_on_answer").select2("val", "" + resultData['data']['is_depend_on_answer'] + "");

                    }, 100);

                    setTimeout(function() {

                        if (resultData['data']['is_depend_on_answer'] == 1) {

                            $("#depended_question_id").empty().trigger('change');
                            var newOption = new Option(resultData['data']['depended_question']['text'], resultData['data']['depended_question']['id'], false, false);
                            $('#depended_question_id').append(newOption).trigger('change');

                        }

                    }, 200);

                    setTimeout(function() {

                        if (resultData['data']['has_depended_question_answer'] == 1) {

                            $("#depended_question_answer").empty().trigger('change');
                            var newOption = new Option(resultData['data']['depended_question_answer']['text'], resultData['data']['depended_question_answer']['id'], false, false);
                            $('#depended_question_answer').append(newOption).trigger('change');

                        }

                    }, 300);










                    editModeLoading = 0;
                    if (resultData['data']['type'] != 1 && resultData['data']['type'] != 4 && resultData['data']['type'] != 6) {
                        $("#sec_inquiry_questions_options").hide();
                    } else {

                        //assign value to repetor element
                        $('.inner-dynamic').html('');
                        optionIndex = 0;
                        $.each(resultData['data']['inquiry_question_option'], function(key, value) {
                            //first time set value in default html
                            // if(key == 0){
                            //     $('#question_option_1').val(value['option']);
                            //     $('#question_option_id_1').val(value['id']);
                            //     $('#question_option_1').attr("data-id",value['id']);
                            // }else{




                            var option_row = '';
                            // var keyIndex = key+1;
                            // var nextindex = key+1;

                            option_row += '<div class="element mb-3 row" id="e_option_' + value['id'] + '">'
                            option_row += '<div class="col-md-10 col-8">'
                            option_row += '<input type="text" name="edit_option[' + value['id'] + ']" id="edit_option_' + value['id'] + '" class="form-control" value="' + value['option'] + '" data-id="' + value['id'] + '"/>'

                            option_row += '</div>'
                            option_row += '<div class="col-md-2 col-4">'
                            option_row += '<div class="d-grid">'
                            option_row += '<input type="button" class="btn btn-primary edit_remove" id="edit_remove_' + value['id'] + '" value="Delete"/>'
                            option_row += '</div>'
                            option_row += '</div>'
                            option_row += '</div>'
                            $('.inner-dynamic').append(option_row);

                        });
                    }
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
                        url: ajaxInquiryQuestionDeleteURL + "?id=" + id,
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

    var optionIndex = 0;

    $("#addMoreOption").click(function() {
        // Finding total number of elements added
        // var total_element = $(".element").length;
        //   var lastid = $(".element:last").attr("id");
        //   var split_id = lastid.split("_");

        addMoreOption();


    });

    function addMoreOption() {

        var option_row = '';
        option_row += '<div class="element mb-3 row" id="option_' + optionIndex + '">'
        option_row += '<div class="col-md-10 col-8">'
        option_row += '<input id="question_option_id_' + optionIndex + '" type="text" name="question_option[' + optionIndex + ']" class="form-control"/>'
        option_row += ''
        option_row += '</div>'
        option_row += '<div class="col-md-2 col-4">'
        option_row += '<div class="d-grid">'
        option_row += '<input type="button" class="btn btn-primary remove" id="remove_' + optionIndex + '" value="Delete"/>'
        option_row += '</div>'
        option_row += '</div>'
        option_row += '</div>'

        optionIndex++;

        $('.inner-dynamic').append(option_row);

    }

    $(document).on('click', '.remove', function() {

        var id = this.id;
        var split_id = id.split("_");
        var deleteindex = split_id[1];
        $("#option_" + deleteindex).remove();


    });
    $(document).on('click', '.edit_remove', function() {

        var id = this.id;
        var split_id = id.split("_");
        var deleteindex = split_id[2];

        $("#e_option_" + deleteindex).remove();


    });


    var currentURL = window.location.href;
    var loadedURLLink = $('.userscomman a[href="' + currentURL + '"]');
    $(loadedURLLink).removeClass('btn-outline-primary');
    $(loadedURLLink).addClass('btn-primary');
</script>
@endsection