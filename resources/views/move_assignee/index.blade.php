@extends('layouts.main')
@section('title', $data['title'])
@section('content')

    @php
        $inquiryStatus = getInquiryStatus();
    @endphp

    <style type="text/css">
        td p {
            max-width: 100%;
            white-space: break-spaces;
            word-break: break-all;
        }

        td {
            vertical-align: middle;
        }
    </style>

    <div class="page-content">
        <div class="container-fluid">

            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">Move Assignee

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

                            <form id="formMoveAssignee" action="{{ route('move.assignee.save') }}" method="POST"
                                class="needs-validation" novalidate>
                                <div class="modal-body">

                                    @csrf

                                    <div class="row">
                                        <div class="col-md-12">


                                            @foreach ($data['feature_list'] as $key => $value)
                                                <div class="form-check form-check-primary mb-3">
                                                    <input class="form-check-input feature-checkbox" type="checkbox"
                                                        id="check_feature_{{ $value['id'] }}"
                                                        name="check_feature_{{ $value['id'] }}" checked>
                                                    <label class="form-check-label" for="check_feature_{{ $value['id'] }}">
                                                        {{ $value['name'] }}
                                                    </label>
                                                </div>
                                            @endforeach




                                        </div>
                                    </div>






                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="from_assignee" class="form-label">From Assignee</label>


                                                <select id="from_assignee" name="from_assignee"
                                                    class="form-control select2-ajax" required>
                                                </select>
                                                <div class="invalid-feedback">
                                                    Please select from assignee.
                                                </div>


                                            </div>
                                        </div>

                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="to_assignee" class="form-label">To Assignee</label>

                                                <select id="to_assignee" name="to_assignee"
                                                    class="form-control select2-ajax" required>
                                                </select>
                                                <div class="invalid-feedback">
                                                    Please select to assignee.
                                                </div>


                                            </div>
                                        </div>

                                    </div>









                                </div>


                                <div class="row">
                                    <div class="col-md-6">



                                        <div class="form-check form-check-primary mb-3" id="allInquryRow">
                                            <input class="form-check-input" type="checkbox" id="check_feature_all_inqury"
                                                name="check_feature_all_inqury" checked>
                                            <label class="form-check-label" for="check_feature_all_inqury">
                                                All Inquiry Has
                                            </label>






                                            <label for="inquiry_status" class="form-label"> Status</label>
                                            <select class="select2-apply" id="inquiry_status" name="inquiry_status">
                                                <option value="0">All</option>
                                                @foreach ($inquiryStatus as $key => $value)
                                                    @if ($value['has_question'] != 0)
                                                        <option value="{{ $value['id'] }}">{{ $value['name'] }}</option>
                                                    @endif
                                                @endforeach
                                            </select>

                                        </div>
                                    </div>

                                </div>








                                <div class="row" id="inquiryTableRow">

                                    <table id="inquiryTable" class="table align-middle table-nowrap mb-0 w-100">
                                        <thead>
                                            <tr>

                                                <th></th>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Phone Number</th>
                                                <th>Status</th>




                                            </tr>
                                        </thead>


                                        <tbody>

                                        </tbody>
                                    </table>


                                </div>



                                <div class="modal-footer">

                                    <button type="submit" class="btn btn-primary">Move</button>
                                </div>
                            </form>





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
        var ajaxSearchAssigneee = '{{ route('move.assignee.search.assigned.user') }}';
        var ajaxInquiry = '{{ route('move.assignee.inquiry.ajax') }}';
        var csrfToken = $("[name=_token").val();

        $("#from_assignee").select2({
            ajax: {
                url: ajaxSearchAssigneee,
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
            placeholder: 'Search for from assignee',



        });

        $("#to_assignee").select2({
            ajax: {
                url: ajaxSearchAssigneee,
                dataType: 'json',
                delay: 0,
                data: function(params) {
                    return {
                        q: params.term, // search term
                        page: params.page,
                        user_id: function() {
                            return $("#from_assignee").val();
                        },
                        is_channel_partner: function() {
                            return $("#check_feature_4").prop("checked");
                        }
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
            placeholder: 'Search for to assignee',



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
            $('#formMoveAssignee').ajaxForm(options);
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
                $('#formMoveAssignee').trigger("reset");
                $("#from_assignee").empty().trigger('change');
                $("#to_assignee").empty().trigger('change');
                $("#formMoveAssignee").removeClass('was-validated');


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

        $('#from_assignee').on('change', function() {

            $("#to_assignee").empty().trigger('change');


        });

        var table = $('#inquiryTable').DataTable({
            "aoColumnDefs": [{
                "bSortable": false,
                "aTargets": [0]
            }],
            "order": [
                [1, 'desc']
            ],
            "processing": true,
            "serverSide": true,
            "pageLength": 10,
            "ajax": {
                "url": ajaxInquiry,
                "type": "POST",
                "data": {
                    "_token": csrfToken,
                    "assigned_to": function() {
                        return $("#from_assignee").val();
                    },
                    "inquiry_status": function() {
                        return $("#inquiry_status").val();
                    }
                }


            },
            "aoColumns": [{
                    "mData": "checkbox"
                }, {
                    "mData": "id"
                },
                {
                    "mData": "name"
                },
                {
                    "mData": "phone_number"
                },
                {
                    "mData": "status"
                }



            ],
            "drawCallback": function() {

                for (var i = 0; i < checkBoxIds.length; i++) {
                    $("#" + checkBoxIds[i]).prop("checked", true);
                }



            }

        });

        var checkBoxIds = [];
        function displayInquiryTable() {

            var isInquiryChecked = $("#check_feature_1").prop("checked");
            var fromAssignee = $("#from_assignee").val();
            if (isInquiryChecked == true && fromAssignee != null) {

                checkBoxIds = [];
                table.ajax.reload();
                $("#inquiryTableRow").show();
                $("#allInquryRow").show();
                $("#check_feature_all_inqury").trigger("change");



            } else {

                $("#inquiryTableRow").hide();
                $("#allInquryRow").hide();

            }


        }

        $('.feature-checkbox').on('change', function() {
            displayInquiryTable();
        });

        $('#from_assignee').on('change', function() {
            displayInquiryTable();
        });

        $('#inquiry_status').on('change', function() {
            displayInquiryTable();
        });

        $('#check_feature_all_inqury').on('change', function() {
            var checkAllInqury = $("#check_feature_all_inqury").prop("checked");
            if (checkAllInqury == true) {
                $("#inquiryTableRow").hide();
            } else {
                $("#inquiryTableRow").show();
            }





        });

        $("#inquiryTableRow").delegate(".checkbox_inquiry", "change", function() {

            if ($("#" + this.id).prop("checked")) {
                checkBoxIds.push(this.id)
            } else {


                var checkBoxIdIndex = checkBoxIds.indexOf(this.id);

                checkBoxIds.splice(checkBoxIdIndex, 1);
            }


        });

        displayInquiryTable();
    </script>
@endsection
