@extends('layouts.main')
@section('title', $data['title'])
@section('content')


    <style type="text/css">
        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            margin: 0;
        }


        .card-2 {

            padding: 10px;
            width: 350px;
            height: 100px;
            bottom: -50px;
            left: 20px;
            position: absolute;
            border-radius: 5px;
        }

        .card-2 .content {
            margin-top: 50px;
        }
    </style>
    <div class="page-content">
        <div class="container-fluid">

            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">Change Password</h4>


                    </div>


                </div>
            </div>
            <!-- end page title -->

            @if (Auth::user()->is_changed_password == 0)
                <div class="alert alert-warning" role="alert">
                    Please change your password and access the website for security reasons.
                </div>
            @endif






            <div class="row" id="non-prime-div">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">





                            <form id="formPassword" class="custom-validation" action="{{ route('do.changepassword') }}"
                                method="POST" enctype="multipart/form-data">



                                @csrf







                                <div id="password_div">

                                    @if (Auth::user()->is_changed_password == 1)
                                        <div class="mb-3 row">
                                            <label for="old_password" class="col-md-2 col-form-label">Old Password</label>
                                            <div class="col-md-10">
                                                <input class="form-control" type="password" value="" id="old_password"
                                                    name="old_password" required>
                                            </div>
                                    @endif
                                </div>

                                <div class="mb-3 row">
                                    <label for="new_password" class="col-md-2 col-form-label">New Password</label>
                                    <div class="col-md-10">
                                        <input class="form-control" type="password" value="" id="new_password"
                                            name="new_password" required>
                                    </div>
                                </div>

                                <div class="mb-3 row">
                                    <label for="confirm_password" class="col-md-2 col-form-label">Confirm Password </label>
                                    <div class="col-md-10">
                                        <input class="form-control" type="password" value="" id="confirm_password"
                                            name="confirm_password" required>
                                    </div>
                                </div>







                                <div class="d-flex flex-wrap gap-2">
                                    <button type="submit" class="btn btn-primary waves-effect waves-light">
                                        Update
                                    </button>
                                    <button type="reset" class="btn btn-secondary waves-effect">
                                        Reset
                                    </button>
                                </div>
                        </div>



                    </div>
                </div>
            </div> <!-- end col -->
            </form>
            <!-- end col -->
        </div>
        <!-- end row -->
    </div>
    <!-- container-fluid -->
    </div>
    <!-- End Page-content -->







@endsection('content')
@section('custom-scripts')
    <script src="{{ asset('assets/libs/parsleyjs/parsley.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/form-validation.init.js') }}"></script>
    <script src="{{ asset('assets/js/pages/jquery.form.js') }}"></script>
    <script type="text/javascript">
        var ajaxChangePasswordOTP = '{{ route('changepassword.otp') }}';

        document.addEventListener("DOMContentLoaded", function(event) {

            function OTPInput() {
                const inputs = document.querySelectorAll('#otp > *[id]');
                for (let i = 0; i < inputs.length; i++) {
                    inputs[i].addEventListener('keydown', function(event) {
                        if (event.key === "Backspace") {
                            inputs[i].value = '';
                            if (i !== 0) inputs[i - 1].focus();
                        } else {
                            if (i === inputs.length - 1 && inputs[i].value !== '') {
                                return true;
                            } else if (event.keyCode > 47 && event.keyCode < 58) {
                                inputs[i].value = event.key;
                                if (i !== inputs.length - 1) inputs[i + 1].focus();
                                event.preventDefault();
                            } else if (event.keyCode > 64 && event.keyCode < 91) {
                                inputs[i].value = String.fromCharCode(event.keyCode);
                                if (i !== inputs.length - 1) inputs[i + 1].focus();
                                event.preventDefault();
                            }
                        }
                    });
                }
            }
            OTPInput();


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
            $('#formPassword').ajaxForm(options);
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
                var form_type = $("#form_type").val();

                toastr["success"](responseText['msg']);

                if (form_type == "form_otp") {
                    $("#form_type").val("form_change_password");
                    $("#div_verify_otp").attr("style", "display: none !important");
                    $("#password_div").show();


                } else {

                    setTimeout(function() {
                        window.location.href = "{{ route('dashboard') }}";
                    }, 1000);

                }





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

        $("#sendOTP, #resendOTP").click(function() {

            $("#sendOTP").html("Sending...");
            $("#resendOTP").html("Sending...");
            $("#sendOTP").attr('disabled', true);
            $("#resendOTP").attr('disabled', true);

            $.ajax({
                type: 'GET',
                url: ajaxChangePasswordOTP,
                success: function(resultData) {
                    if (resultData['status'] == 1) {

                        toastr["success"](resultData['msg']);
                        $("#recieverLable").html(resultData['reciever_lable']);
                        $("#sendOTP").html("Send");
                        $("#resendOTP").html("Resend");
                        $("#sendOTP").attr('disabled', false);
                        $("#resendOTP").attr('disabled', false);


                        $("#first").val("");
                        $("#second").val("");
                        $("#third").val("");
                        $("#fourth").val("");

                        $("#div_send_otp").attr("style", "display: none !important");
                        $("#div_verify_otp").show();

                    } else {
                        toastr["error"](resultData['msg']);
                    }

                }
            });





        });
    </script>
@endsection
