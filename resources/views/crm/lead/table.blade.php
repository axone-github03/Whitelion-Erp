@extends('layouts.main')
@section('title', $data['title'])
@section('content')

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
                    <h4 class="mb-sm-0 font-size-18"> {{$data['title']}}</h4>
                    <div class="page-title-right">
                        @if($data['title'] == "Lead")
                            <button type="button" class="btn btn-primary waves-effect waves-light" id="addLeadBtn">Add Lead</button>
                        @endif
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
                                    <th>Task</th>
                                    <th>{{ $data['title'] }}</th>
                                    <th>Name</th>
                                    <th></th>
                                    <th>Status</th>
                                    <th>Site Stage</th>
                                    <th>Closing Date</th>
                                    <th>Source</th>
                                    <th>Lead Owner</th>
                                    <th>Created By</th>




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





@include('crm.lead.create_lead_modal');
@csrf
@endsection('content')
@section('custom-scripts')

<script src="{{ asset('assets/libs/parsleyjs/parsley.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/form-validation.init.js') }}"></script>
<script src="{{ asset('assets/js/pages/jquery.form.js') }}"></script>
<script src="{{ asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
<script type="text/javascript">
    var ajaxMainMasterDataURL = "{{route('crm.lead.table.ajax')}}";
    var is_deal = "{{$data['is_deal']}}";





    var csrfToken = $("[name=_token").val();

    var helpDocumentPageLength = getCookie('helpDocumentPageLength') !== undefined ? getCookie('helpDocumentPageLength') : 10
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
        "pageLength": helpDocumentPageLength,
        "ajax": {
            "url": ajaxMainMasterDataURL,
            "type": "POST",
            "data": {
                "_token": csrfToken,
                "is_deal": is_deal
            }


        },
        "aoColumns": [{
                "mData": "task"
            },
            {
                "mData": "id"
            },
            {
                "mData": "name"
            },
            {
                "mData": "phone_email"
            },
            {
                "mData": "status"
            },
            {
                "mData": "site_stage"
            },
            {
                "mData": "closing_date"
            },
            {
                "mData": "source"
            },
            {
                "mData": "lead_owner"
            },
            {
                "mData": "created_by"
            }



        ]
    });

    function reloadTable() {
        table.ajax.reload(null, false);
    }

    $('#datatable').on('length.dt', function(e, settings, len) {

        setCookie('helpDocumentPageLength', len, 100);


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
        $('#formLead').ajaxForm(options);











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

        $(".save-btn").html("Saving...");
        $(".save-btn").prop("disabled", true);
        return true;
    }

    // post-submit callback
    function showResponse(responseText, statusText, xhr, $form) {

        $(".save-btn").html("Save");
        $(".save-btn").prop("disabled", false);

        if ($form[0]['id'] == "formLead") {


            if (responseText['status'] == 1) {
                toastr["success"](responseText['msg']);
                $('#formLead').trigger("reset");
                $("#modalLead").modal('hide');
                table.ajax.reload(null, false);




            } else if (responseText['status'] == 0) {

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

@include('crm.lead.create_lead_script');
@endsection