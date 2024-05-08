<div class="modal fade" id="modalPointLog" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" role="dialog" aria-labelledby="modalPointLogLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPointLogLabel"> Point Log</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="min-height:100%;">


                <table id="pointLogTable" class="table align-middle table-nowrap mb-0 w-100">
                    <thead>
                        <tr>

                            <th>Log</th>




                        </tr>
                    </thead>


                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalInquiryLog" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" role="dialog" aria-labelledby="modalInquiryLogLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalInquiryLogLabel"> Inquiry List</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="min-height:100%;">

                <div class="row text-center mb-3">
                    <div class="col-3">
                        <h5 class="mb-0" id="totalInquiry">0</h5>
                        <button class="btn btn-primary btn-sm" id="btnInquiryLogTotal">Total Inquiry</button>
                    </div>
                    <div class="col-3">
                        <h5 class="mb-0" id="totalRunningInquiry">0</h5>
                        <button class="btn btn-primary btn-sm" id="btnInquiryLogRunning">Running Inquiry</button>
                    </div>
                    <div class="col-3">
                        <h5 class="mb-0" id="totalWonInquiry">0</h5>
                        <button class="btn btn-primary btn-sm" id="btnInquiryLogWon">Won Inquiry</button>
                    </div>
                    <div class="col-3">
                        <h5 class="mb-0" id="totalRejectedInquiry">0</h5>
                        <button class="btn btn-primary btn-sm" id="btnInquiryLogLost">Lost Inquiry</button>
                    </div>
                </div>
                <div class="float-end">

                    <button type="button" class="btn-sm btn btn-outline-dark waves-effect waves-light float-end" aria-haspopup="true" aria-expanded="false">Quotation Amount: <span id="totalInquiryLogQuotationAmount"></span></button>
                </div>

                <table id="InquiryTable" class="table align-middle table-nowrap mb-0 w-100">
                    <thead>
                        <tr>

                            <th>#Id</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Quotation Amount</th>
                            <th>Architect</th>
                            <th>Channel Partner</th>

                        </tr>
                    </thead>


                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalUser" data-bs-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="modalUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalUserLabel"> User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formUser" action="{{route('electrician.save')}}" method="POST" class="needs-validation" novalidate>
                <div class="modal-body">

                    @csrf


                    <div class="col-md-12 text-center loadingcls">






                        <button type="button" class="btn btn-light waves-effect">
                            <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading
                        </button>


                    </div>

                    <input type="hidden" name="user_id" id="user_id">


                    <div class="row">




                        <!-- <div class="col-md-3">
                                                <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                                                <a class="nav-link mb-2 active" id="v-pills-user-detail-tab" data-bs-toggle="pill" href="#v-pills-user-detail" role="tab" aria-controls="v-pills-user-detail" aria-selected="true">1. User Detail</a>


                                                <a class="nav-link mb-2" id="v-pills-user-extra-detail-tab" data-bs-toggle="pill" href="#v-pills-electrician-detail" role="tab" aria-controls="v-pills-electrician-detail" aria-selected="false">2. Electrician Detail</a>

                                                </div>
                                            </div> -->

                        <div class="col-md-12">
                            <!-- <div class="tab-content text-muted mt-4 mt-md-0" id="v-pills-tabContent"> -->
                            <div class="tab-pane fade show active" id="v-pills-user-detail" role="tabpanel" aria-labelledby="v-pills-user-detail-tab">

                                <div class="card-body section_user_detail">



                                    <div class="row" id="div_user_type">
                                        <div class="col-md-12">

                                            <div class="mb-3">
                                                <label for="user_status" class="form-label">User Type * </label>
                                                <select class="form-select" id="user_type" name="user_type" required>

                                                    @php
                                                    $accessTypes=getElectricians();
                                                    @endphp
                                                    @if(count($accessTypes)>0)
                                                    @foreach($accessTypes as $key=>$value)
                                                    <option value="{{$value['id']}}">{{$value['name']}} </option>
                                                    @endforeach
                                                    @endif

                                                </select>
                                                <div class="invalid-feedback">
                                                    Please select user type.
                                                </div>

                                            </div>

                                        </div>

                                    </div>






                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="user_first_name" class="form-label">First name <code class="highlighter-rouge">*</code></label>
                                                <input type="text" class="form-control" id="user_first_name" name="user_first_name" placeholder="First Name" value="" required>


                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="user_last_name" class="form-label">Last name <code class="highlighter-rouge">*</code></label>
                                                <input type="text" class="form-control" id="user_last_name" name="user_last_name" placeholder="Last Name" value="" required>


                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6" id="row_email">
                                            <div class="mb-3">
                                                <label for="user_email" class="form-label">Email <code class="highlighter-rouge">*</code></label>
                                                <input type="email" class="form-control" id="user_email" name="user_email" placeholder="Email" value="" required>


                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="insert_phone_number" class="form-label">Phone number <code class="highlighter-rouge">*</code></label>

                                                <div class="input-group">
                                                    <div class="input-group-text">
                                                        +91


                                                    </div>
                                                    <input type="number" class="form-control" id="user_phone_number" name="user_phone_number" placeholder="Phone number" value="" required>
                                                </div>





                                            </div>
                                        </div>
                                    </div>





                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="user_country_id" class="form-label">Country <code class="highlighter-rouge">*</code></label>
                                                <select class="form-select" id="user_country_id" name="user_country_id" required>
                                                    <option selected value="1">India</option>

                                                </select>
                                                <div class="invalid-feedback">
                                                    Please select country.
                                                </div>

                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                <label class="form-label">State <code class="highlighter-rouge">*</code></label>
                                                <select class="form-control select2-ajax" id="user_state_id" name="user_state_id" required>

                                                </select>
                                                <div class="invalid-feedback">
                                                    Please select state.
                                                </div>

                                            </div>



                                        </div>

                                        <div class="col-md-4">
                                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                <label class="form-label">City <code class="highlighter-rouge">*</code></label>
                                                <select class="form-control select2-ajax" id="user_city_id" name="user_city_id" required>

                                                </select>
                                                <div class="invalid-feedback">
                                                    Please select city.
                                                </div>

                                            </div>



                                        </div>


                                    </div>

                                    <div class="row">

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="user_pincode" class="form-label">Pincode <code class="highlighter-rouge" id="user_pincode_mandatory"></code></label>
                                                <input type="text" class="form-control" id="user_pincode" name="user_pincode" placeholder="Pincode" value="">


                                            </div>
                                        </div>


                                    </div>


                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="user_address_line1" class="form-label">Address line 1 <code class="highlighter-rouge" id="user_address_line1_mandatory">*</code></label>
                                                <input type="text" class="form-control" id="user_address_line1" name="user_address_line1" placeholder="Address line 1" value="" required>


                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="user_address_line2" class="form-label">Address line 2</label>
                                                <input type="text" class="form-control" id="user_address_line2" name="user_address_line2" placeholder="Address line 2" value="">


                                            </div>
                                        </div>
                                    </div>




                                    <div class="row">
                                        <div class="col-md-12" id="row_user_status">

                                            <div class="mb-3">
                                                <label for="user_status" class="form-label">Status <code class="highlighter-rouge">*</code></label>
                                                <select class="form-select" id="user_status" name="user_status" required>
                                                    <option selected value="1">Active</option>
                                                    <option value="0">Inactive</option>
                                                    <option value="2">Pending</option>

                                                </select>
                                                <div class="invalid-feedback">
                                                    Please select status.
                                                </div>

                                            </div>

                                        </div>

                                    </div>







                                </div>

                                <!-- </div> -->
                                <!-- <div class="tab-pane fade " id="v-pills-electrician-detail" role="tabpanel" aria-labelledby="v-pills-user-extra-detail-tab"> -->


                                <div class="card-body section_user_extra_detail">







                                    <div class="row ">




                                        <div class="col-md-12 " id="section_sale_person">
                                            <div class="mb-3">
                                                <label for="architect_firm_name" class="form-label">Sale Person *</label>



                                                <select class="form-control select2-ajax" id="electrician_sale_person_id" name="electrician_sale_person_id" required>

                                                </select>
                                                <div class="invalid-feedback">
                                                    Please select sale person.
                                                </div>

                                            </div>





                                        </div>
                                    </div>









                                </div>
















                                <!-- </div> -->

                            </div>

                        </div>
                    </div>





                    @if($data['viewMode']==0)
                    <div class="modal-footer">

                        <!-- <button id="btnNext" type="button" class="btn btn-primary">Next</button> -->

                        <div id="btnSave">


                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button id="btnSaveFinal" type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </div>
                    @endif
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    var ajaxURLSearchState = "{{route('search.state.from.country')}}";
    var ajaxURLSearchCity = "{{route('search.city.from.state')}}";
    var ajaxURLSearchSalePerson = "{{route('electricians.search.sale.person')}}";
    var ajaxURLUserDetail = "{{route('electrician.detail')}}";
    var ajaxPointLog = "{{route('electricians.point.log')}}";
    var ajaxInquiryLog = "{{route('electricians.inquiry.log')}}";
    var csrfToken = $("[name=_token").val();

    $("#user_country_id").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#modalUser .modal-body")

    });


    $("#user_state_id").select2({
        ajax: {
            url: ajaxURLSearchState,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    "country_id": function() {
                        return $("#user_country_id").val()
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
        placeholder: 'Search for a state',
        dropdownParent: $("#modalUser .modal-body")
    });


    $("#user_city_id").select2({
        ajax: {
            url: ajaxURLSearchCity,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    "state_id": function() {
                        return $("#user_state_id").val()
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
        placeholder: 'Search for a city',
        dropdownParent: $("#modalUser .modal-body")
    });


    $("#user_status").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#modalUser .modal-body")
    });

    $("#user_type").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#modalUser .modal-body")

    });




    $("#electrician_sale_person_id").select2({
        ajax: {
            url: ajaxURLSearchSalePerson,
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
        placeholder: 'Search for a sale person',
        dropdownParent: $("#modalUser .modal-body")
    });




    $(document).ready(function() {


        if (isSalePerson == 1) {

            $("#section_sale_person").hide();
            $("#electrician_sale_person_id").removeAttr('required');
            $("#row_user_status").hide();
            $("#user_status").removeAttr('required');

        }




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
        $('#formUser').ajaxForm(options);
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
        var formElement = jqForm[0];
        if (formElement.id == "formUser") {


            // if ($(".was-validated .form-control:invalid").length > 0) {
            //      return false;
            //  }


        }

        $("#btnSaveFinal").html("Saving...");

        $("#btnSaveFinal").prop('disabled', true);
        return true;

    }

    // post-submit callback
    function showResponse(responseText, statusText, xhr, $form) {
        $("#btnSaveFinal").prop('disabled', false);
        $("#btnSaveFinal").html("Save");


        if (responseText['status'] == 1) {
            toastr["success"](responseText['msg']);
            reloadTable();
            resetInputForm();
            $("#modalUser").modal('hide');


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




    // $('.nav a').click(function() {
    //     if ($(this).attr('id') == "v-pills-user-extra-detail-tab") {


    //         $("#btnNext").hide();
    //         $("#btnSave").show();
    //         $('#user_country_id').select2('open');
    //         $('#user_country_id').select2('close');

    //     } else {




    //         $("#btnNext").show();
    //         $("#btnSave").hide();
    //         $('#user_country_id').select2('open');
    //         $('#user_country_id').select2('close');

    //     }
    // });

    // $("#btnNext").click(function() {

    //     $("#v-pills-user-detail-tab").removeClass('active');
    //     $("#v-pills-user-extra-detail-tab").addClass('active');
    //     $("#v-pills-user-detail").removeClass('active');
    //     $("#v-pills-user-detail").removeClass('show');
    //     $("#v-pills-electrician-detail").addClass('active');
    //     $("#v-pills-electrician-detail").addClass('show');
    //     $("#btnNext").hide();
    //     $("#btnSave").show();
    //     $('#user_country_id').select2('open');
    //     $('#user_country_id').select2('close');

    // });




    $("#addBtnUser").click(function() {



        resetInputForm();
        $("#modalUserLabel").html("Add Electrician");
        $("#user_id").val(0);
        $(".loadingcls").hide();
        $("#formUser .row").show();
        // console.log($("#user_type").val());
        setTimeout(function() {
            $("#user_type").val($("#user_type").val());
            $('#user_type').trigger('change');
            changeUserType($("#user_type").val());

            if (isSalePerson == 1) {

                $("#formUser input").prop('disabled', false);
                $('#formUser select').select2("enable");

            }



        }, 100);



    });

    function resetInputForm() {

        $("#formUser").removeClass('was-validated');
        $('#formUser').trigger("reset");
        $('#v-pills-tab.nav a:first').tab('show');
        // $("#btnNext").show();
        // $("#btnSave ").hide();

        $("#user_status").select2("val", "1");
        $("#user_country_id").select2("val", "1");
        $("#user_state_id").empty().trigger('change');
        $("#user_city_id").empty().trigger('change');

        if (viewMode == 1 || isSalePerson == 1) {
            $("#formUser input:not([type=hidden]").prop('disabled', true);
            $('#formUser select').select2("enable", false);


        }




    }



    var editModeLoading = 0;

    function editView(id) {

        editModeLoading = 1;
        resetInputForm();
        $("#modalUser").modal('show');
        $("#modalUserLabel").html("Edit Electrician #" + id);
        $("#formUser .row").hide();
        $(".loadingcls").show();
        // $("#btnNext").show();
        // $("#btnSave").hide();

        $.ajax({
            type: 'GET',
            url: ajaxURLUserDetail + "?id=" + id,
            success: function(resultData) {
                if (resultData['status'] == 1) {





                    $("#user_id").val(resultData['data']['id']);
                    $("#user_first_name").val(resultData['data']['first_name']);
                    $("#user_last_name").val(resultData['data']['last_name']);
                    $("#user_phone_number").val(resultData['data']['phone_number']);
                    $("#user_email").val(resultData['data']['email']);
                    $("#user_ctc").val(resultData['data']['ctc']);
                    $("#user_pincode").val(resultData['data']['pincode']);
                    $("#user_address_line1").val(resultData['data']['address_line1']);
                    $("#user_address_line2").val(resultData['data']['address_line2']);
                    $("#user_type").val(resultData['data']['type']);
                    $("#user_status").val(resultData['data']['status']);

                    if (resultData['data']['type'] == 301) {

                        $("#user_email").val('');

                    }



                    if (typeof resultData['data']['country']['id'] !== "undefined") {

                        $("#user_country_id").val("" + resultData['data']['country']['id'] + "");
                        $('#user_country_id').trigger('change');

                    }


                    if (typeof resultData['data']['state']['id'] !== "undefined") {

                        var newOption = new Option(resultData['data']['state']['name'], resultData['data']['state']['id'], false, false);
                        $('#user_state_id').append(newOption).trigger('change');
                        $("#user_state_id").val("" + resultData['data']['state']['id'] + "");
                        $('#user_state_id').trigger('change');


                    }

                    if (typeof resultData['data']['city']['id'] !== "undefined") {


                        var newOption = new Option(resultData['data']['city']['name'], resultData['data']['city']['id'], false, false);
                        $('#user_city_id').append(newOption).trigger('change');
                        $("#user_city_id").val("" + resultData['data']['city']['id'] + "");
                        $('#user_city_id').trigger('change');


                    }






                    $(".loadingcls").hide();
                    $("#formUser .row").show();
                    $('#user_type').trigger('change');
                    $('#user_status').trigger('change');





                    if (typeof resultData['data']['electrician']['sale_person'] !== "undefined") {




                        var newOption = new Option(resultData['data']['electrician']['sale_person']['text'], resultData['data']['electrician']['sale_person']['id'], false, false);
                        $('#electrician_sale_person_id').append(newOption).trigger('change');
                        $("#electrician_sale_person_id").val("" + resultData['data']['electrician']['sale_person']['id'] + "");
                        $('#electrician_sale_person_id').trigger('change');


                    }

                    if (isSalePerson == 1) {



                        if (resultData['data']['type'] == 301) {
                            $("#user_type").select2("enable");
                        }

                    }


                    editModeLoading = 0;
                    $("#v-pills-user-detail-tab").trigger('click');




                } else {

                    toastr["error"](resultData['msg']);

                }

            }
        });

    }

    $('#user_country_id').on('change', function() {

        if (editModeLoading == 0) {

            $("#user_state_id").empty().trigger('change');
            $("#user_city_id").empty().trigger('change');
        }

    });

    $('#user_state_id').on('change', function() {

        if (editModeLoading == 0) {
            $("#user_city_id").empty().trigger('change');
        }

    });




    $('#user_type').on('change', function() {

        changeUserType($(this).val());


    });




    function changeUserType(userType) {
        userType = userType + "";




        $('#user_country_id').select2('open');
        $('#user_country_id').select2('close');

        if (userType == "301") {



            $("#row_email").hide();
            $("#user_email").removeAttr('required');
            //$("#user_pincode").removeAttr('required');
            $("#user_address_line1").removeAttr('required');
            $("#user_pincode_mandatory").html("");
            $("#user_address_line1_mandatory").html("");



        } else {

            $("#row_email").hide();
            $("#user_email").removeAttr('required');
            //  $("#user_pincode").prop('required',true);
            $("#user_address_line1").prop('required', true);
            //$("#user_pincode_mandatory").html("*");
            $("#user_address_line1_mandatory").html("*");

        }

        if (userType == "302") {
            if (isSalePerson == 1) {


                if ($("#user_address_line1").val() == "") {
                    $("#user_address_line1").removeAttr('disabled');
                }
                $("#divFooter").show();

            }
        } else {
            if (isSalePerson == 1) {
                $("#divFooter").hide();
                $("#user_address_line1").attr('disabled', true);
            }
        }



    }


    var electricianLogUserId = 0;

    function pointLogs(userId) {

        electricianLogUserId = userId;
        $("#modalPointLog").modal('show');
        pointLogTable.ajax.reload(null, false);

    }
    var pointLogTable = $('#pointLogTable').DataTable({
        "aoColumnDefs": [{
            "bSortable": false,
            "aTargets": [0]
        }],
        "order": [
            [0, 'desc']
        ],
        "processing": true,
        "serverSide": true,
        "pageLength": 10,
        "ajax": {
            "url": ajaxPointLog,
            "type": "POST",
            "data": {
                "_token": csrfToken,
                "user_id": function() {
                    return electricianLogUserId
                },
            }
        },
        "aoColumns": [{
            "mData": "log"
        }]
    });

    var inquiryLogType = 0;

    var inquiryLogTable = $('#InquiryTable').DataTable({
        "aoColumnDefs": [{
            "bSortable": false,
            "aTargets": []
        }],
        "order": [
            [0, 'desc']
        ],
        "processing": true,
        "serverSide": true,
        "pageLength": 10,
        "ajax": {
            "url": ajaxInquiryLog,
            "type": "POST",
            "data": {
                "_token": csrfToken,
                "user_id": function() {
                    return electricianLogUserId
                },
                "type": function() {
                    return inquiryLogType
                },
            }
        },
        "aoColumns": [{
                "mData": "id"
            },
            {
                "mData": "name"
            },
            {
                "mData": "status"
            },
            {
                "mData": "quotation_amount"
            },
            {
                "mData": "column4"
            },
            {
                "mData": "column5"
            },
        ]
    });

    function inquiryLogs(userId) {
        electricianLogUserId = userId;

        $("#modalInquiryLog").modal('show');
        inquiryLogType = 0;

        inquiryLogTable.ajax.reload();

    }

    inquiryLogTable.on('xhr', function() {

        var responseData = inquiryLogTable.ajax.json();
        $("#totalInquiry").html(responseData['overview']['total_inquiry']);
        $("#totalRunningInquiry").html(responseData['overview']['total_running']);
        $("#totalWonInquiry").html(responseData['overview']['total_won']);
        $("#totalRejectedInquiry").html(responseData['overview']['total_rejected']);
        $("#totalInquiryLogQuotationAmount").html(responseData['quotationAmount']);
        $("#modalInquiryLogLabel").html(responseData['title']);

        $(".inquiry-log-active").removeClass("inquiry-log-active");

        if (responseData['type'] == "0") {
            $("#btnInquiryLogTotal").addClass('inquiry-log-active');
        } else if (responseData['type'] == "1") {
            $("#btnInquiryLogRunning").addClass('inquiry-log-active');
        } else if (responseData['type'] == "2") {
            $("#btnInquiryLogWon").addClass('inquiry-log-active');
        } else if (responseData['type'] == "3") {
            $("#btnInquiryLogLost").addClass('inquiry-log-active');
        }
    });


    $("#btnInquiryLogTotal").click(function() {

        inquiryLogType = 0;
        inquiryLogTable.ajax.reload();

    });

    $("#btnInquiryLogRunning").click(function() {
        inquiryLogType = 1;
        inquiryLogTable.ajax.reload();

    });

    $("#btnInquiryLogWon").click(function() {
        inquiryLogType = 2;
        inquiryLogTable.ajax.reload();

    });

    $("#btnInquiryLogLost").click(function() {
        inquiryLogType = 3;
        inquiryLogTable.ajax.reload();

    });
</script>