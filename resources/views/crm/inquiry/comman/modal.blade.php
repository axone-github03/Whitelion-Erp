<div class="modal fade" id="modalInquiryLog" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" role="dialog" aria-labelledby="modalPointLogLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPointLogLabel"> Inquiry Log</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="min-height:100%;">

                <div class="row text-center mb-3">
                    <div class="col-3">
                        <h5 class="mb-0" id="totalInquiry">0</h5>
                        <button class="btn btn-primary btn-sm inquiry-log-active" id="btnInquiryLogTotal">Total Inquiry</button>
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

                <table id="InquiryLogTable" class="table align-middle table-nowrap mb-0 w-100">
                    <thead>

                        <tr>

                            <th>#Id</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Quotation Amount</th>
                            <th>Architect</th>
                            <th>Electrician</th>

                        </tr>




                    </thead>


                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalInquiry" data-bs-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="modalInquiryLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalInquiryLabel">Inquiry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formInquiry" action="{{route('inquiry.save')}}" method="POST" class="needs-validation" novalidate>
                <div class="modal-body">
                    @csrf

                    <input type="hidden" name="new_inquiry_id" id="new_inquiry_id">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="inquiry_first_name" class="form-label">First name <code class="highlighter-rouge">*</code></label>
                                <input type="text" class="form-control" id="inquiry_first_name" name="inquiry_first_name" placeholder="First name" value="" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="inquiry_last_name" class="form-label">Last name <code class="highlighter-rouge">*</code></label>
                                <input type="text" class="form-control" id="inquiry_last_name" name="inquiry_last_name" placeholder="Last name" value="" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="inquiry_phone_number" class="form-label">Phone number <code class="highlighter-rouge" id="inquiry_phone_number_error">*</code></label>
                                <div class="input-group">
                                    <div class="input-group-text">
                                        +91


                                    </div>
                                    <input type="number" class="form-control" id="inquiry_phone_number" name="inquiry_phone_number" placeholder="Phone number" value="" required>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="inquiry_phone_number2" class="form-label">Other Phone number <code class="highlighter-rouge"></code></label>
                                <div class="input-group">
                                    <div class="input-group-text">
                                        +91


                                    </div>
                                    <input type="number" class="form-control" id="inquiry_phone_number2" name="inquiry_phone_number2" placeholder="Phone number" value="">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="inquiry_pincode" class="form-label">Pincode <code class="highlighter-rouge"></code></label>
                                <input type="text" class="form-control" id="inquiry_pincode" name="inquiry_pincode" placeholder="Pincode" value="">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                <label class="form-label">City <code class="highlighter-rouge">*</code></label>
                                <select class="form-control select2-ajax" id="inquiry_city_id" name="inquiry_city_id" required>
                                </select>
                                <div class="invalid-feedback">
                                    Please select city.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="inquiry_house_no" class="form-label">House No <code class="highlighter-rouge">*</code></label>
                                <input type="text" class="form-control" id="inquiry_house_no" name="inquiry_house_no" placeholder="House No" value="" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="inquiry_society_name" class="form-label">Building/Society name <code class="highlighter-rouge">*</code></label>
                                <input type="text" class="form-control" id="inquiry_society_name" name="inquiry_society_name" placeholder="Building/Society name" value="" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="inquiry_area" class="form-label">Area <code class="highlighter-rouge">*</code></label>
                                <input type="text" class="form-control" id="inquiry_area" name="inquiry_area" placeholder="Area" value="" required>
                            </div>
                        </div>
                    </div>

                    <div class="row" id="row_assigned_to">
                        <div class="col-md-6">

                            <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                <label class="form-label">Assigned To <code class="highlighter-rouge">*</code></label>
                                <select class="form-control select2-ajax" id="inquiry_assigned_to" name="inquiry_assigned_to">
                                </select>

                                <div class="invalid-feedback">
                                    Please select assigned to.
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="row " id="row-more_answer_7">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="pre_inquiry_questions_7" class="form-label">Current stage of site <code class="highlighter-rouge" id="pre_inquiry_questions_7_require">*</code></label>
                                <select id="pre_inquiry_questions_7" name="pre_inquiry_questions_7" class="form-select pre-select2-apply">

                                    <option value="">Select Option</option>
                                    @foreach($data['stage_of_site'] as $OptK=>$OptV)
                                    <option value="{{$OptV->id}}">{{$OptV->option}} </option>
                                    @endforeach
                                </select>

                            </div>
                        </div>
                    </div>





                    <div class="row" id="div_source">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="inquiry_source_type" class="form-label">Source Type <code class="highlighter-rouge">*</code></label>
                                <select class="form-control select2-ajax" id="inquiry_source_type" name="inquiry_source_type" required>
                                    @if(count($data['source_types'])>0)
                                    @foreach($data['source_types'] as $key=>$value)
                                    <option value="{{$value['type']}}-{{$value['id']}}">{{$value['lable']}}</option>
                                    @endforeach
                                    @endif
                                </select>
                                <div class="invalid-feedback">
                                    Please select source type.
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6" id="div_source_user">
                            <div class="mb-3">
                                <label for="inquiry_source_user" class="form-label">Source <code class="highlighter-rouge">*</code></label>
                                <select class="form-control select2-ajax" id="inquiry_source_user" name="inquiry_source_user">
                                </select>
                                <div class="invalid-feedback">
                                    Please select source.
                                </div>
                            </div>

                        </div>

                        <div class="col-md-6" id="div_source_text">
                            <div class="mb-3">
                                <label for="inquiry_source_text" class="form-label">Source <code class="highlighter-rouge" id="inquiry_source_text_required">*</code></label>
                                <input type="text" class="form-control" id="inquiry_source_text" name="inquiry_source_text" placeholder="Source" value="">

                                <div class="invalid-feedback">
                                    Please enter source.
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6" id="div_source_exhibition">
                            <div class="mb-3">
                                <label for="inquiry_source_exhibition" class="form-label">Exhibition <code class="highlighter-rouge">*</code></label>
                                <select class="form-control select2-ajax" id="inquiry_source_exhibition" name="inquiry_source_exhibition">
                                </select>
                                <div class="invalid-feedback">
                                    Please select exhibition.
                                </div>
                            </div>

                        </div>

                    </div>

                    <button type="button" id="addMoreSource" class="btn btn-primary waves-effect waves-light"><i class="bx bx-plus font-size-16 align-middle me-2"></i>Add More Source</button>

                    @if(isAdminOrCompanyAdmin()==1)

                    <button type="button" id="removeSource" class="btn btn-danger waves-effect waves-light"><i class="bx bx-minus font-size-16 align-middle me-2"></i>Remove Source</button>
                    @endif


                    <br>
                    <br>


                    <div class="row" id="div_source_1">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="inquiry_source_type_1" class="form-label">Source Type <code class="highlighter-rouge"></code></label>
                                @php



                                @endphp
                                <select class="form-control select2-ajax" id="inquiry_source_type_1" name="inquiry_source_type_1">
                                    @if(count($data['source_types'])>0)
                                    @foreach($data['source_types'] as $key=>$value)
                                    <option value="{{$value['type']}}-{{$value['id']}}">{{$value['lable']}}</option>
                                    @endforeach
                                    @endif
                                </select>
                                <div class="invalid-feedback">
                                    Please select source type.
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6" id="div_source_user_1">
                            <div class="mb-3">
                                <label for="inquiry_source_user_1" class="form-label">Source <code class="highlighter-rouge"></code></label>
                                <select class="form-control select2-ajax" id="inquiry_source_user_1" name="inquiry_source_user_1">
                                </select>
                                <div class="invalid-feedback">
                                    Please select source.
                                </div>
                            </div>

                        </div>

                        <div class="col-md-6" id="div_source_text_1">
                            <div class="mb-3">
                                <label for="inquiry_source_text_1" class="form-label">Source <code class="highlighter-rouge" id="inquiry_source_1_text_required">*</code></label>
                                <input type="text" class="form-control" id="inquiry_source_text_1" name="inquiry_source_text_1" placeholder="Source" value="">

                                <div class="invalid-feedback">
                                    Please enter source.
                                </div>
                            </div>
                        </div>






                    </div>


                    <div class="row" id="div_source_2">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="inquiry_source_type_2" class="form-label">Source Type <code class="highlighter-rouge"></code></label>
                                @php



                                @endphp
                                <select class="form-control select2-ajax" id="inquiry_source_type_2" name="inquiry_source_type_2">
                                    @if(count($data['source_types'])>0)
                                    @foreach($data['source_types'] as $key=>$value)
                                    <option value="{{$value['type']}}-{{$value['id']}}">{{$value['lable']}}</option>
                                    @endforeach
                                    @endif
                                </select>
                                <div class="invalid-feedback">
                                    Please select source type.
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6" id="div_source_user_2">
                            <div class="mb-3">
                                <label for="inquiry_source_user_2" class="form-label">Source <code class="highlighter-rouge"></code></label>
                                <select class="form-control select2-ajax" id="inquiry_source_user_2" name="inquiry_source_user_2">
                                </select>
                                <div class="invalid-feedback">
                                    Please select source.
                                </div>
                            </div>

                        </div>

                        <div class="col-md-6" id="div_source_text_2">
                            <div class="mb-3">
                                <label for="inquiry_source_text_2" class="form-label">Source <code class="highlighter-rouge" id="inquiry_source_2_text_required">*</code></label>
                                <input type="text" class="form-control" id="inquiry_source_text_2" name="inquiry_source_text_2" placeholder="Source" value="">

                                <div class="invalid-feedback">
                                    Please enter source.
                                </div>
                            </div>
                        </div>






                    </div>


                    <div class="row" id="div_source_3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="inquiry_source_type_3" class="form-label">Source Type <code class="highlighter-rouge"></code></label>
                                @php



                                @endphp
                                <select class="form-control select2-ajax" id="inquiry_source_type_3" name="inquiry_source_type_3">
                                    @if(count($data['source_types'])>0)
                                    @foreach($data['source_types'] as $key=>$value)
                                    <option value="{{$value['type']}}-{{$value['id']}}">{{$value['lable']}}</option>
                                    @endforeach
                                    @endif
                                </select>
                                <div class="invalid-feedback">
                                    Please select source type.
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6" id="div_source_user_3">
                            <div class="mb-3">
                                <label for="inquiry_source_user_3" class="form-label">Source <code class="highlighter-rouge"></code></label>
                                <select class="form-control select2-ajax" id="inquiry_source_user_3" name="inquiry_source_user_3">
                                </select>
                                <div class="invalid-feedback">
                                    Please select source.
                                </div>
                            </div>

                        </div>

                        <div class="col-md-6" id="div_source_text_3">
                            <div class="mb-3">
                                <label for="inquiry_source_text_3" class="form-label">Source <code class="highlighter-rouge" id="inquiry_source_3_text_required">*</code></label>
                                <input type="text" class="form-control" id="inquiry_source_text_3" name="inquiry_source_text_3" placeholder="Source" value="">

                                <div class="invalid-feedback">
                                    Please enter source.
                                </div>
                            </div>
                        </div>






                    </div>


                    <div class="row" id="div_source_4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="inquiry_source_type_4" class="form-label">Source Type <code class="highlighter-rouge"></code></label>
                                @php



                                @endphp
                                <select class="form-control select2-ajax" id="inquiry_source_type_4" name="inquiry_source_type_4">
                                    @if(count($data['source_types'])>0)
                                    @foreach($data['source_types'] as $key=>$value)
                                    <option value="{{$value['type']}}-{{$value['id']}}">{{$value['lable']}}</option>
                                    @endforeach
                                    @endif
                                </select>
                                <div class="invalid-feedback">
                                    Please select source type.
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6" id="div_source_user_4">
                            <div class="mb-3">
                                <label for="inquiry_source_user_4" class="form-label">Source <code class="highlighter-rouge"></code></label>
                                <select class="form-control select2-ajax" id="inquiry_source_user_4" name="inquiry_source_user_4">
                                </select>
                                <div class="invalid-feedback">
                                    Please select source.
                                </div>
                            </div>

                        </div>

                        <div class="col-md-6" id="div_source_text_4">
                            <div class="mb-3">
                                <label for="inquiry_source_text_4" class="form-label">Source <code class="highlighter-rouge" id="inquiry_source_4_text_required">*</code></label>
                                <input type="text" class="form-control" id="inquiry_source_text_4" name="inquiry_source_text_4" placeholder="Source" value="">

                                <div class="invalid-feedback">
                                    Please enter source.
                                </div>
                            </div>
                        </div>






                    </div>







                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">

                                <button id="moreDetailBtn" type="button" class="btn btn-primary waves-effect waves-light">More Detail</button>
                                <button id="lessDetailBtn" type="button" class="btn btn-primary waves-effect waves-light">Less Detail</button>
                            </div>

                        </div>

                    </div>

                    <div class="row row-more-detail" id="row_architect">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="inquiry_architect" class="form-label">Architect name </label>

                                <select class="form-control select2-ajax" id="inquiry_architect" name="inquiry_architect" placeholder="Architect name">
                                </select>



                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="inquiry_architect_phone_number" class="form-label">Architect phone number</label>

                                <div class="input-group">
                                    <div class="input-group-text">
                                        +91


                                    </div>
                                    <input type="number" class="form-control" id="inquiry_architect_phone_number" name="inquiry_architect_phone_number" placeholder="Architect phone number" value="" disabled>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="row row-more-detail" id="row_electician">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="inquiry_electrician" class="form-label">Electrician name</label>

                                <select class="form-control select2-ajax" id="inquiry_electrician" name="inquiry_electrician" placeholder="Architect name">
                                </select>



                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="inquiry_electrician_phone_number" class="form-label">Electrician phone number</label>

                                <div class="input-group">
                                    <div class="input-group-text">
                                        +91


                                    </div>
                                    <input type="number" class="form-control" id="inquiry_electrician_phone_number" name="inquiry_electrician_phone_number" placeholder="Electrician phone number" value="" disabled>
                                </div>
                            </div>
                        </div>
                    </div>


                    @include('../crm/inquiry/pre_question')


                    <div class="row" id="row_next_followup">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="inquiry_follow_up_type" class="form-label">Next Follow up type <code class="highlighter-rouge">*</code></label>

                                <select class="form-control" id="inquiry_follow_up_type" name="inquiry_follow_up_type" required>
                                    <option value="Meeting">Meeting</option>
                                    <option value="Call">Call</option>

                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="inquiry_follow_up_date_time" class="form-label"> Date & Time of Follow up <code class="highlighter-rouge">*</code></label>







                                <div class="input-group" id="inquiry_follow_up_date_time">
                                    <input type="text" class="form-control" value="{{date('d-m-Y')}}" placeholder="dd-mm-yyyy" data-date-format="dd-mm-yyyy" data-date-container='#inquiry_follow_up_date_time' data-provide="datepicker" data-date-autoclose="true" required name="inquiry_follow_up_date" id="inquiry_follow_up_date">

                                    <div style="width:50%;">

                                        <select class="form-control" id="inquiry_follow_up_time" name="inquiry_follow_up_time">

                                            @foreach($data['timeSlot'] as $timeSlot)
                                            <option value="{{$timeSlot}}">{{$timeSlot}} </option>
                                            @endforeach
                                        </select>
                                    </div>


                                </div>
                                <br>
                                <br>
                                <br>
                                <br>
                                <br>






                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    @if($data['isChannelPartner']!=0)
                    <button type="submit" class="btn btn-primary">Request for verify</button>
                    @else

                    <button type="submit" class="btn btn-primary">Save</button>

                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
<!-- end model popup-->
<script type="text/javascript">
    var ajaxURLSearchCity = "{{route('search.city')}}";
    var ajaxURLSearchUser = "{{route('inquiry.search.user')}}";
    var ajaxURLSearchExhibition = "{{route('inquiry.search.exhibition')}}";

    var ajaxURLSearchArchitect = "{{route('inquiry.search.architect')}}";
    var ajaxURLSearchElectrician = "{{route('inquiry.search.electrician')}}";
    var ajaxURLSearchChannelPartner = "{{route('inquiry.search.channelpartner')}}";
    var listArchitectsNonPrime = "{{route('architects.prime')}}";
    var listArchitectsPrime = "{{route('architects.prime')}}";
    var listElectriciansNonPrime = "{{route('electricians.prime')}}";
    var listElectriciansPrime = "{{route('electricians.prime')}}";
    var listChannelPartnerAD = "{{route('channel.partners.ad')}}";
    var ajaxPointLog = "{{route('inquiry.point.log')}}";

    $("#removeSource").click(function() {


        var IsourceType = $("#inquiry_source_type").val();

        var compareIarray = ["user-201", "user-202", "user-301", "user-302"]
        var inquirySourceUser = $("#inquiry_source_user").val();
        if (compareIarray.includes(IsourceType) && inquirySourceUser != null) {

            $("#inquiry_source_user").empty().trigger('change');
            $("#inquiry_source_user").removeAttr('required');


        } else {


            var IsourceType1 = $("#inquiry_source_type_1").val();
            var inquirySourceUser1 = $("#inquiry_source_user_1").val();;
            if (compareIarray.includes(IsourceType1) && inquirySourceUser1 != null) {

                $("#inquiry_source_user_1").empty().trigger('change');
                $("#inquiry_source_user_1").removeAttr('required');


            } else {

                var IsourceType2 = $("#inquiry_source_type_2").val();
                var inquirySourceUser2 = $("#inquiry_source_user_1").val();;
                if (compareIarray.includes(IsourceType2) && inquirySourceUser2 == null) {

                    $("#inquiry_source_user_2").empty().trigger('change');
                    $("#inquiry_source_user_2").removeAttr('required');


                } else {



                    var IsourceType3 = $("#inquiry_source_type_3").val();
                    var inquirySourceUser3 = $("#inquiry_source_user_3").val();;
                    if (compareIarray.includes(IsourceType3) && inquirySourceUser3 == null) {

                        $("#inquiry_source_user_3").empty().trigger('change');
                        $("#inquiry_source_user_3").removeAttr('required');


                    } else {

                        var IsourceType4 = $("#inquiry_source_type_4").val();
                        var inquirySourceUser4 = $("#inquiry_source_user_4").val();;
                        if (compareIarray.includes(IsourceType4)) {

                            $("#inquiry_source_user_4").empty().trigger('change');
                            $("#inquiry_source_user_4").removeAttr('required');


                        }

                    }

                }

            }




        }





        $("#inquiry_architect").empty().trigger('change');
        $("#inquiry_architect_phone_number").val('');









    });


    //open when add new inquiry
    $("#addBtnInquiry").click(function() {
        resetInputForm();

        $("#modalInquiryLabel").html("Add Inquiry");
        $(".loadingcls").hide();
        $("#modalInquiry .modal-footer").show();
        $(".row-more-detail").hide();
        $("#lessDetailBtn").hide();
        $("#moreDetailBtn").show()
        $("#div_source_text").hide();
        $("#div_source_exhibition").hide();
        $("#div_source_user").hide();
        $("#div_source_1").hide();
        $("#div_source_2").hide();
        $("#div_source_3").hide();
        $("#div_source_4").hide();
        $("#div_source_text_1").hide();
        $("#div_source_user_1").hide();
        $("#div_source_text_2").hide();
        $("#div_source_user_2").hide();
        $("#div_source_text_3").hide();
        $("#div_source_user_3").hide();
        $("#div_source_text_4").hide();
        $("#div_source_user_4").hide();





    });

    $("#addMoreSource").click(function() {

        if (isThirdPartyUser == 1) {

        } else {



            var isSource1IsVisible = $("#div_source_1").is(':visible');
            if (!isSource1IsVisible) {
                $("#div_source_1").show();
                $("#inquiry_source_type_1").trigger('change');
                return;
            }

            var isSource2IsVisible = $("#div_source_2").is(':visible');
            if (!isSource2IsVisible) {
                $("#div_source_2").show();
                $("#inquiry_source_type_2").trigger('change');
                return;
            }

            var isSource3IsVisible = $("#div_source_3").is(':visible');
            if (!isSource3IsVisible) {
                $("#div_source_3").show();
                $("#inquiry_source_type_3").trigger('change');
                return;
            }

            var isSource4IsVisible = $("#div_source_4").is(':visible');
            if (!isSource4IsVisible) {
                $("#div_source_4").show();
                $("#inquiry_source_type_4").trigger('change');
                return;
            }






        }





    });




    $("#moreDetailBtn").click(function() {


        $(".row-more-detail").show();
        $("#lessDetailBtn").show();
        $("#moreDetailBtn").hide();
        $('#inquiry_city_id').select2('open');
        $('#inquiry_city_id').select2('close');

        if (isChannelPartner != 0) {
            $("#row_architect").show();
            $("#row_electician").show();

        }


        if (isArchitect == 1 || isElectrician == 1) {
            $("#row_assigned_to").hide();
            $("#inquiry_assigned_to").removeAttr('required');
            $("#row_next_followup").hide();
            $("#inquiry_follow_up_type").removeAttr('required');
            $("#inquiry_follow_up_date_time").removeAttr('required')

        } else {
            $("#row_assigned_to").show();
            $("#inquiry_assigned_to").prop('required', true);
            $("#row_next_followup").show();
            $("#inquiry_follow_up_type").prop('required', true);
            $("#inquiry_follow_up_date_time").prop('required', true);

        }


    });

    $("#lessDetailBtn").click(function() {

        $(".row-more-detail").hide();
        $("#lessDetailBtn").hide();
        $("#moreDetailBtn").show();
        if (isArchitect == 1 || isElectrician == 1) {
            $("#row_assigned_to").hide();
            $("#inquiry_assigned_to").removeAttr('required');
            $("#row_next_followup").hide();
            $("#inquiry_follow_up_type").removeAttr('required');
            $("#inquiry_follow_up_date_time").removeAttr('required')

        } else {
            $("#row_assigned_to").show();
            $("#inquiry_assigned_to").prop('required', true);
            $("#row_next_followup").show();
            $("#inquiry_follow_up_type").prop('required', true);
            $("#inquiry_follow_up_date_time").prop('required', true);

        }

    });


    //reset all the element in form when open popup
    function resetInputForm() {
        $('#formInquiry').trigger("reset");

        // $("#inquiry_source_type").select2("val", "0");
        $("#inquiry_source_user").empty().trigger('change');
        $("#inquiry_city_id").empty().trigger('change');
        $("#formInquiry").removeClass('was-validated');

        $("#inquiry_architect").empty().trigger('change');
        $("#inquiry_electrician").empty().trigger('change');
        $("#pre_inquiry_questions_7").prop('required', true);
        $("#pre_inquiry_questions_7_require").html("*");



        if (isArchitect == 1 || isElectrician == 1) {

            $("#div_source").hide();
            $("#inquiry_source_type").removeAttr('required');
            $("#inquiry_source_user").removeAttr('required');
            // $("#inquiry_source_text").removeAttr('required');
            $("#row_assigned_to").hide();
            $("#inquiry_assigned_to").removeAttr('required');
            $("#row_next_followup").hide();
            $("#inquiry_follow_up_type").removeAttr('required');
            $("#inquiry_follow_up_date_time").removeAttr('required');
            $("#addMoreSource").hide();


        } else if (isChannelPartner != 0) {

            $("#inquiry_source_type").removeAttr('required');
            $("#inquiry_source_user").removeAttr('required');
            $("#div_source").hide();
            $("#addMoreSource").hide();
            $("#inquiry_source_type").removeAttr('required');
            $("#row_next_followup").show();
            $("#inquiry_follow_up_type").prop('required', true);
            $("#inquiry_follow_up_date_time").prop('required', true);

        } else if (isThirdPartyUser == 1) {

            $("#inquiry_source_type").removeAttr('required');
            $("#inquiry_source_user").removeAttr('required');
            $("#div_source").hide();
            $("#addMoreSource").hide();
            $("#inquiry_source_type").removeAttr('required');
            $("#row_next_followup").hide();
            $("#inquiry_follow_up_type").removeAttr('required');
            $("#inquiry_follow_up_date_time").removeAttr('required');
            $("#row_assigned_to").hide();
            $("#inquiry_assigned_to").removeAttr('required');
            $("#pre_inquiry_questions_7").removeAttr('required');



        } else {
            $("#div_source").show();
            $("#inquiry_source_type").prop('required', true);
            $("#row_assigned_to").show();
            $("#inquiry_assigned_to").prop('required', true);
            $("#row_next_followup").show();
            $("#inquiry_follow_up_type").prop('required', true);
            $("#inquiry_follow_up_date_time").prop('required', true);
        }


        $("#new_inquiry_id").val(0);

        setTimeout(function() {
            $("#inquiry_source_type").trigger('change');
        }, 200)


    }




    $("#inquiry_follow_up_time").select2({
        dropdownParent: $("#modalInquiry .modal-content")
    });



    $("#inquiry_city_id").select2({
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
        dropdownParent: $("#modalInquiry .modal-content")
    });

    $("#inquiry_source_type").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#modalInquiry .modal-content")
    });
    
    $("#inquiry_source_type_1").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#modalInquiry .modal-content")
    });

    $("#inquiry_source_type_2").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#modalInquiry .modal-content")
    });


    $("#inquiry_source_type_3").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#modalInquiry .modal-content")
    });

    $("#inquiry_source_type_4").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#modalInquiry .modal-content")
    });



    $("#inquiry_follow_up_type").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#modalInquiry .modal-content")
    });


    $('#inquiry_source_type').on('change', function() {
        $("#inquiry_source_user").empty().trigger('change');
        $("#inquiry_source_text").val('');

        if ($("#inquiry_source_type").val() != null && isChannelPartner == 0 && isArchitect == 0 && isElectrician == 0) {

            var pieces_inquiry_source_type = $("#inquiry_source_type").val().split("-");
            $("#inquiry_source_text").removeAttr('required');
            $("#inquiry_source_user").removeAttr('required');
            $("#inquiry_source_text_required").html("");

            if (pieces_inquiry_source_type[0] == "user") {
                $("#div_source_text").hide();
                $("#div_source_exhibition").hide();
                $("#div_source_user").show();
                $("#inquiry_source_user").prop('required', true);
                if (isArchitect == 0 && isElectrician == 0) {

                    $("#sourceAddBtn").remove();

                    if (pieces_inquiry_source_type[1] == 201) {

                        $("#div_source_user").append('<a target="_blank" href="' + listArchitectsPrime + '?add=1" id="sourceAddBtn"  class="btn btn-sm btn-primary float-end">Add</a>');

                    } else if (pieces_inquiry_source_type[1] == 202) {

                        $("#div_source_user").append('<a target="_blank" href="' + listArchitectsNonPrime + '?add=1" id="sourceAddBtn"  class="btn btn-sm btn-primary float-end">Add</a>')

                    } else if (pieces_inquiry_source_type[1] == 301) {

                        $("#div_source_user").append('<a target="_blank" href="' + listElectriciansPrime + '?add=1" id="sourceAddBtn"  class="btn btn-sm btn-primary float-end">Add</a>')


                    } else if (pieces_inquiry_source_type[1] == 302) {

                        $("#div_source_user").append('<a target="_blank" href="' + listElectriciansNonPrime + '?add=1" id="sourceAddBtn"  class="btn btn-sm btn-primary float-end">Add</a>')


                    } else if (pieces_inquiry_source_type[1] == 104) {

                        $("#div_source_user").append('<a target="_blank" href="' + listChannelPartnerAD + '?add=1" id="sourceAddBtn"  class="btn btn-sm btn-primary float-end">Add</a>')



                    } else {
                        $("#sourceAddBtn").remove();

                    }
                }


            } else if (pieces_inquiry_source_type[0] == "textrequired" || pieces_inquiry_source_type[0] == "textnotrequired") {

                $("#div_source_text").show();
                $("#div_source_user").hide();
                $("#div_source_exhibition").hide();
                if (pieces_inquiry_source_type[0] == "textrequired") {
                    $("#inquiry_source_text").prop('required', true);
                    $("#inquiry_source_text_required").html("*");
                } else {
                    $("#inquiry_source_text").removeAttr('required');
                    $("#inquiry_source_text_required").html("");

                }


            }else if(pieces_inquiry_source_type[0] == "exhibition"){
                $("#div_source_text").hide();
                $("#div_source_user").hide();
                $("#div_source_exhibition").show();

            } else {
                $("#div_source_text").hide();
                $("#div_source_user").hide();
                $("#div_source_exhibition").hide();


            }
        }


        $("#pre_inquiry_questions_7").prop('required', true);


        if (isArchitect == 1 || isElectrician == 1) {

            $("#div_source").hide();
            $("#inquiry_source_type").removeAttr('required');
            $("#inquiry_source_user").removeAttr('required');
            // $("#inquiry_source_text").removeAttr('required');
            $("#row_assigned_to").hide();
            $("#inquiry_assigned_to").removeAttr('required');
            $("#row_next_followup").hide();
            $("#inquiry_follow_up_type").removeAttr('required');
            $("#inquiry_follow_up_date_time");
            $("#addMoreSource").hide();


        } else if (isChannelPartner != 0) {

            $("#inquiry_source_type").removeAttr('required');
            $("#inquiry_source_user").removeAttr('required');
            $("#div_source").hide();
            $("#addMoreSource").hide();
            $("#inquiry_source_type").removeAttr('required');
            $("#row_next_followup").show();
            $("#inquiry_follow_up_type").prop('required', true);
            $("#inquiry_follow_up_date_time").prop('required', true);

        } else if (isThirdPartyUser == 1) {

            $("#inquiry_source_type").removeAttr('required');
            $("#inquiry_source_user").removeAttr('required');
            $("#div_source").hide();
            $("#addMoreSource").hide();
            $("#inquiry_source_type").removeAttr('required');
            $("#row_next_followup").hide();
            $("#inquiry_follow_up_type").removeAttr('required');
            $("#inquiry_follow_up_date_time").removeAttr('required');
            $("#row_assigned_to").hide();
            $("#inquiry_assigned_to").removeAttr('required');

            $("#pre_inquiry_questions_7").removeAttr('required');

        } else {
            $("#div_source").show();
            $("#inquiry_source_type").prop('required', true);
            $("#row_assigned_to").show();
            $("#inquiry_assigned_to").prop('required', true);
            $("#row_next_followup").show();
            $("#inquiry_follow_up_type").prop('required', true);
            $("#inquiry_follow_up_date_time").prop('required', true);
        }



    });


    $('#inquiry_source_type_1').on('change', function() {
        $("#inquiry_source_user_1").empty().trigger('change');
        $("#inquiry_source_text_1").val('');

        if ($("#inquiry_source_type_1").val() != null) {

            var pieces_inquiry_source_type = $("#inquiry_source_type_1").val().split("-");
            // $("#inquiry_source_text_1").removeAttr('required');
            // $("#inquiry_source_user_1").removeAttr('required');
            // $("#inquiry_source_1_text_required").html("");


            if (pieces_inquiry_source_type[0] == "user") {
                $("#div_source_text_1").hide();
                $("#div_source_user_1").show();
                // $("#inquiry_source_user_1").prop('required',true);
                if (isArchitect == 0 && isElectrician == 0) {

                    // $("#sourceAddBtn").remove();

                    if (pieces_inquiry_source_type[1] == 201) {

                        // $("#div_source_user").append('<a target="_blank" href="'+listArchitectsPrime+'?add=1" id="sourceAddBtn"  class="btn btn-sm btn-primary float-end">Add</a>');

                    } else if (pieces_inquiry_source_type[1] == 202) {

                        //$("#div_source_user").append('<a target="_blank" href="'+listArchitectsNonPrime+'?add=1" id="sourceAddBtn"  class="btn btn-sm btn-primary float-end">Add</a>')

                    } else if (pieces_inquiry_source_type[1] == 301) {

                        //$("#div_source_user").append('<a target="_blank" href="'+listElectriciansPrime+'?add=1" id="sourceAddBtn"  class="btn btn-sm btn-primary float-end">Add</a>')


                    } else if (pieces_inquiry_source_type[1] == 302) {

                        //$("#div_source_user").append('<a target="_blank" href="'+listElectriciansNonPrime+'?add=1" id="sourceAddBtn"  class="btn btn-sm btn-primary float-end">Add</a>')


                    } else if (pieces_inquiry_source_type[1] == 104) {

                        //$("#div_source_user").append('<a target="_blank" href="'+listChannelPartnerAD+'?add=1" id="sourceAddBtn"  class="btn btn-sm btn-primary float-end">Add</a>')



                    } else {
                        // $("#sourceAddBtn").remove();

                    }
                }


            } else if (pieces_inquiry_source_type[0] == "textrequired" || pieces_inquiry_source_type[0] == "textnotrequired") {

                $("#div_source_text_1").show();
                $("#div_source_user_1").hide();
                if (pieces_inquiry_source_type[0] == "textrequired") {
                    // $("#inquiry_source_text_1").prop('required',true);
                    //  $("#inquiry_source_1_text_required").html("*");
                } else {
                    // $("#inquiry_source_text").removeAttr('required');
                    //  $("#inquiry_source_1_text_required").html("");

                }


            } else {
                $("#div_source_text_1").hide();
                $("#div_source_user_1").hide();


            }
        }


    });


    $('#inquiry_source_type_2').on('change', function() {
        $("#inquiry_source_user_2").empty().trigger('change');
        $("#inquiry_source_text_2").val('');

        if ($("#inquiry_source_type_2").val() != null) {

            var pieces_inquiry_source_type = $("#inquiry_source_type_2").val().split("-");
            // $("#inquiry_source_text_1").removeAttr('required');
            // $("#inquiry_source_user_1").removeAttr('required');
            // $("#inquiry_source_1_text_required").html("");


            if (pieces_inquiry_source_type[0] == "user") {
                $("#div_source_text_2").hide();
                $("#div_source_user_2").show();
                // $("#inquiry_source_user_1").prop('required',true);
                if (isArchitect == 0 && isElectrician == 0) {

                    // $("#sourceAddBtn").remove();

                    if (pieces_inquiry_source_type[1] == 201) {

                        // $("#div_source_user").append('<a target="_blank" href="'+listArchitectsPrime+'?add=1" id="sourceAddBtn"  class="btn btn-sm btn-primary float-end">Add</a>');

                    } else if (pieces_inquiry_source_type[1] == 202) {

                        //$("#div_source_user").append('<a target="_blank" href="'+listArchitectsNonPrime+'?add=1" id="sourceAddBtn"  class="btn btn-sm btn-primary float-end">Add</a>')

                    } else if (pieces_inquiry_source_type[1] == 301) {

                        //$("#div_source_user").append('<a target="_blank" href="'+listElectriciansPrime+'?add=1" id="sourceAddBtn"  class="btn btn-sm btn-primary float-end">Add</a>')


                    } else if (pieces_inquiry_source_type[1] == 302) {

                        //$("#div_source_user").append('<a target="_blank" href="'+listElectriciansNonPrime+'?add=1" id="sourceAddBtn"  class="btn btn-sm btn-primary float-end">Add</a>')


                    } else if (pieces_inquiry_source_type[1] == 104) {

                        //$("#div_source_user").append('<a target="_blank" href="'+listChannelPartnerAD+'?add=1" id="sourceAddBtn"  class="btn btn-sm btn-primary float-end">Add</a>')



                    } else {
                        // $("#sourceAddBtn").remove();

                    }
                }


            } else if (pieces_inquiry_source_type[0] == "textrequired" || pieces_inquiry_source_type[0] == "textnotrequired") {

                $("#div_source_text_2").show();
                $("#div_source_user_2").hide();
                if (pieces_inquiry_source_type[0] == "textrequired") {
                    // $("#inquiry_source_text_1").prop('required',true);
                    //  $("#inquiry_source_1_text_required").html("*");
                } else {
                    // $("#inquiry_source_text").removeAttr('required');
                    //  $("#inquiry_source_1_text_required").html("");

                }


            } else {
                $("#div_source_text_2").hide();
                $("#div_source_user_2").hide();


            }
        }


    });


    $('#inquiry_source_type_3').on('change', function() {
        $("#inquiry_source_user_3").empty().trigger('change');
        $("#inquiry_source_text_3").val('');

        if ($("#inquiry_source_type_3").val() != null) {

            var pieces_inquiry_source_type = $("#inquiry_source_type_3").val().split("-");
            // $("#inquiry_source_text_1").removeAttr('required');
            // $("#inquiry_source_user_1").removeAttr('required');
            // $("#inquiry_source_3_text_required").html("");


            if (pieces_inquiry_source_type[0] == "user") {
                $("#div_source_text_3").hide();
                $("#div_source_user_3").show();
                // $("#inquiry_source_user_1").prop('required',true);
                if (isArchitect == 0 && isElectrician == 0) {

                    // $("#sourceAddBtn").remove();

                    if (pieces_inquiry_source_type[1] == 201) {

                        // $("#div_source_user").append('<a target="_blank" href="'+listArchitectsPrime+'?add=1" id="sourceAddBtn"  class="btn btn-sm btn-primary float-end">Add</a>');

                    } else if (pieces_inquiry_source_type[1] == 202) {

                        //$("#div_source_user").append('<a target="_blank" href="'+listArchitectsNonPrime+'?add=1" id="sourceAddBtn"  class="btn btn-sm btn-primary float-end">Add</a>')

                    } else if (pieces_inquiry_source_type[1] == 301) {

                        //$("#div_source_user").append('<a target="_blank" href="'+listElectriciansPrime+'?add=1" id="sourceAddBtn"  class="btn btn-sm btn-primary float-end">Add</a>')


                    } else if (pieces_inquiry_source_type[1] == 302) {

                        //$("#div_source_user").append('<a target="_blank" href="'+listElectriciansNonPrime+'?add=1" id="sourceAddBtn"  class="btn btn-sm btn-primary float-end">Add</a>')


                    } else if (pieces_inquiry_source_type[1] == 104) {

                        //$("#div_source_user").append('<a target="_blank" href="'+listChannelPartnerAD+'?add=1" id="sourceAddBtn"  class="btn btn-sm btn-primary float-end">Add</a>')



                    } else {
                        // $("#sourceAddBtn").remove();

                    }
                }


            } else if (pieces_inquiry_source_type[0] == "textrequired" || pieces_inquiry_source_type[0] == "textnotrequired") {

                $("#div_source_text_3").show();
                $("#div_source_user_3").hide();
                if (pieces_inquiry_source_type[0] == "textrequired") {
                    // $("#inquiry_source_text_1").prop('required',true);
                    //  $("#inquiry_source_1_text_required").html("*");
                } else {
                    // $("#inquiry_source_text").removeAttr('required');
                    //  $("#inquiry_source_1_text_required").html("");

                }


            } else {
                $("#div_source_text_3").hide();
                $("#div_source_user_3").hide();


            }
        }


    });


    $('#inquiry_source_type_4').on('change', function() {
        $("#inquiry_source_user_4").empty().trigger('change');
        $("#inquiry_source_text_4").val('');

        if ($("#inquiry_source_type_4").val() != null) {

            var pieces_inquiry_source_type = $("#inquiry_source_type_4").val().split("-");
            // $("#inquiry_source_text_1").removeAttr('required');
            // $("#inquiry_source_user_1").removeAttr('required');
            // $("#inquiry_source_4_text_required").html("");


            if (pieces_inquiry_source_type[0] == "user") {
                $("#div_source_text_4").hide();
                $("#div_source_user_4").show();
                // $("#inquiry_source_user_1").prop('required',true);
                if (isArchitect == 0 && isElectrician == 0) {

                    // $("#sourceAddBtn").remove();

                    if (pieces_inquiry_source_type[1] == 201) {

                        // $("#div_source_user").append('<a target="_blank" href="'+listArchitectsPrime+'?add=1" id="sourceAddBtn"  class="btn btn-sm btn-primary float-end">Add</a>');

                    } else if (pieces_inquiry_source_type[1] == 202) {

                        //$("#div_source_user").append('<a target="_blank" href="'+listArchitectsNonPrime+'?add=1" id="sourceAddBtn"  class="btn btn-sm btn-primary float-end">Add</a>')

                    } else if (pieces_inquiry_source_type[1] == 301) {

                        //$("#div_source_user").append('<a target="_blank" href="'+listElectriciansPrime+'?add=1" id="sourceAddBtn"  class="btn btn-sm btn-primary float-end">Add</a>')


                    } else if (pieces_inquiry_source_type[1] == 302) {

                        //$("#div_source_user").append('<a target="_blank" href="'+listElectriciansNonPrime+'?add=1" id="sourceAddBtn"  class="btn btn-sm btn-primary float-end">Add</a>')


                    } else if (pieces_inquiry_source_type[1] == 104) {

                        //$("#div_source_user").append('<a target="_blank" href="'+listChannelPartnerAD+'?add=1" id="sourceAddBtn"  class="btn btn-sm btn-primary float-end">Add</a>')



                    } else {
                        // $("#sourceAddBtn").remove();

                    }
                }


            } else if (pieces_inquiry_source_type[0] == "textrequired" || pieces_inquiry_source_type[0] == "textnotrequired") {

                $("#div_source_text_4").show();
                $("#div_source_user_4").hide();
                if (pieces_inquiry_source_type[0] == "textrequired") {
                    // $("#inquiry_source_text_1").prop('required',true);
                    //  $("#inquiry_source_1_text_required").html("*");
                } else {
                    // $("#inquiry_source_text").removeAttr('required');
                    //  $("#inquiry_source_1_text_required").html("");

                }


            } else {
                $("#div_source_text_4").hide();
                $("#div_source_user_4").hide();


            }
        }


    });

    $('#inquiry_source_user').on('change', function() {




        if ($("#inquiry_source_type").val() == "user-201" || $("#inquiry_source_type").val() == "user-202") {

            $.ajax({
                type: 'GET',
                url: ajaxURLSearchUser + "?user_id=" + $(this).val(),
                success: function(resultData) {

                    if (resultData['results'].length > 0) {

                        $("#inquiry_architect").empty().trigger('change');
                        var newOption = new Option(resultData['results'][0]['first_name'] + " " + resultData['results'][0]['last_name'], resultData['results'][0]['id'], false, false);
                        $('#inquiry_architect').append(newOption).trigger('change');



                    }

                }
            });


        } else if ($("#inquiry_source_type").val() == "user-301" || $("#inquiry_source_type").val() == "user-302") {

            $.ajax({
                type: 'GET',
                url: ajaxURLSearchUser + "?user_id=" + $(this).val(),
                success: function(resultData) {

                    if (resultData['results'].length > 0) {


                        $("#inquiry_electrician").empty().trigger('change');
                        var newOption = new Option(resultData['results'][0]['first_name'] + " " + resultData['results'][0]['last_name'], resultData['results'][0]['id'], false, false);
                        $('#inquiry_electrician').append(newOption).trigger('change');

                        // $("#inquiry_electrician_name").val(resultData['results'][0]['first_name']+" "+resultData['results'][0]['last_name']);
                        // $("#inquiry_electrician_phone_number").val(resultData['results'][0]['phone_number']);

                    }

                }
            });



        }

    });


    $('#inquiry_source_user_1').on('change', function() {




        if ($("#inquiry_source_type_1").val() == "user-201" || $("#inquiry_source_type_1").val() == "user-202") {

            $.ajax({
                type: 'GET',
                url: ajaxURLSearchUser + "?user_id=" + $(this).val(),
                success: function(resultData) {

                    if (resultData['results'].length > 0) {

                        $("#inquiry_architect").empty().trigger('change');
                        var newOption = new Option(resultData['results'][0]['first_name'] + " " + resultData['results'][0]['last_name'], resultData['results'][0]['id'], false, false);
                        $('#inquiry_architect').append(newOption).trigger('change');



                    }

                }
            });


        } else if ($("#inquiry_source_type_1").val() == "user-301" || $("#inquiry_source_type_1").val() == "user-302") {

            $.ajax({
                type: 'GET',
                url: ajaxURLSearchUser + "?user_id=" + $(this).val(),
                success: function(resultData) {

                    if (resultData['results'].length > 0) {


                        $("#inquiry_electrician").empty().trigger('change');
                        var newOption = new Option(resultData['results'][0]['first_name'] + " " + resultData['results'][0]['last_name'], resultData['results'][0]['id'], false, false);
                        $('#inquiry_electrician').append(newOption).trigger('change');

                        // $("#inquiry_electrician_name").val(resultData['results'][0]['first_name']+" "+resultData['results'][0]['last_name']);
                        // $("#inquiry_electrician_phone_number").val(resultData['results'][0]['phone_number']);

                    }

                }
            });



        }

    });


    $('#inquiry_source_user_2').on('change', function() {






        if ($("#inquiry_source_type_2").val() == "user-201" || $("#inquiry_source_type_2").val() == "user-202") {

            $.ajax({
                type: 'GET',
                url: ajaxURLSearchUser + "?user_id=" + $(this).val(),
                success: function(resultData) {

                    if (resultData['results'].length > 0) {

                        $("#inquiry_architect").empty().trigger('change');
                        var newOption = new Option(resultData['results'][0]['first_name'] + " " + resultData['results'][0]['last_name'], resultData['results'][0]['id'], false, false);
                        $('#inquiry_architect').append(newOption).trigger('change');



                    }

                }
            });


        } else if ($("#inquiry_source_type_2").val() == "user-301" || $("#inquiry_source_type_2").val() == "user-302") {

            $.ajax({
                type: 'GET',
                url: ajaxURLSearchUser + "?user_id=" + $(this).val(),
                success: function(resultData) {

                    if (resultData['results'].length > 0) {


                        $("#inquiry_electrician").empty().trigger('change');
                        var newOption = new Option(resultData['results'][0]['first_name'] + " " + resultData['results'][0]['last_name'], resultData['results'][0]['id'], false, false);
                        $('#inquiry_electrician').append(newOption).trigger('change');

                        // $("#inquiry_electrician_name").val(resultData['results'][0]['first_name']+" "+resultData['results'][0]['last_name']);
                        // $("#inquiry_electrician_phone_number").val(resultData['results'][0]['phone_number']);

                    }

                }
            });



        }

    });





    $('#inquiry_source_user_3').on('change', function() {




        if ($("#inquiry_source_type_3").val() == "user-201" || $("#inquiry_source_type_3").val() == "user-202") {

            $.ajax({
                type: 'GET',
                url: ajaxURLSearchUser + "?user_id=" + $(this).val(),
                success: function(resultData) {

                    if (resultData['results'].length > 0) {

                        $("#inquiry_architect").empty().trigger('change');
                        var newOption = new Option(resultData['results'][0]['first_name'] + " " + resultData['results'][0]['last_name'], resultData['results'][0]['id'], false, false);
                        $('#inquiry_architect').append(newOption).trigger('change');



                    }

                }
            });


        } else if ($("#inquiry_source_type_3").val() == "user-301" || $("#inquiry_source_type_3").val() == "user-302") {

            $.ajax({
                type: 'GET',
                url: ajaxURLSearchUser + "?user_id=" + $(this).val(),
                success: function(resultData) {

                    if (resultData['results'].length > 0) {


                        $("#inquiry_electrician").empty().trigger('change');
                        var newOption = new Option(resultData['results'][0]['first_name'] + " " + resultData['results'][0]['last_name'], resultData['results'][0]['id'], false, false);
                        $('#inquiry_electrician').append(newOption).trigger('change');

                        // $("#inquiry_electrician_name").val(resultData['results'][0]['first_name']+" "+resultData['results'][0]['last_name']);
                        // $("#inquiry_electrician_phone_number").val(resultData['results'][0]['phone_number']);

                    }

                }
            });



        }

    });



    $('#inquiry_source_user_4').on('change', function() {




        if ($("#inquiry_source_type_4").val() == "user-201" || $("#inquiry_source_type_4").val() == "user-202") {

            $.ajax({
                type: 'GET',
                url: ajaxURLSearchUser + "?user_id=" + $(this).val(),
                success: function(resultData) {

                    if (resultData['results'].length > 0) {

                        $("#inquiry_architect").empty().trigger('change');
                        var newOption = new Option(resultData['results'][0]['first_name'] + " " + resultData['results'][0]['last_name'], resultData['results'][0]['id'], false, false);
                        $('#inquiry_architect').append(newOption).trigger('change');



                    }

                }
            });


        } else if ($("#inquiry_source_type_4").val() == "user-301" || $("#inquiry_source_type_4").val() == "user-302") {

            $.ajax({
                type: 'GET',
                url: ajaxURLSearchUser + "?user_id=" + $(this).val(),
                success: function(resultData) {

                    if (resultData['results'].length > 0) {


                        $("#inquiry_electrician").empty().trigger('change');
                        var newOption = new Option(resultData['results'][0]['first_name'] + " " + resultData['results'][0]['last_name'], resultData['results'][0]['id'], false, false);
                        $('#inquiry_electrician').append(newOption).trigger('change');

                        // $("#inquiry_electrician_name").val(resultData['results'][0]['first_name']+" "+resultData['results'][0]['last_name']);
                        // $("#inquiry_electrician_phone_number").val(resultData['results'][0]['phone_number']);

                    }

                }
            });



        }

    });


        $("#inquiry_source_exhibition").select2({
        ajax: {
            url: ajaxURLSearchExhibition,
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
        placeholder: 'Search for a exhibition',
        dropdownParent: $("#modalInquiry .modal-content")
    });



    $("#inquiry_source_user").select2({
        ajax: {
            url: ajaxURLSearchUser,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    "source_type": function() {
                        var pieces_inquiry_source_type = $("#inquiry_source_type").val().split("-");
                        return pieces_inquiry_source_type[1]
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
        placeholder: 'Search for a user',
        dropdownParent: $("#modalInquiry .modal-content")
    });


    $("#inquiry_source_user_1").select2({
        ajax: {
            url: ajaxURLSearchUser,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    "source_type": function() {
                        var pieces_inquiry_source_type = $("#inquiry_source_type_1").val().split("-");
                        return pieces_inquiry_source_type[1]
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
        placeholder: 'Search for a user',
        dropdownParent: $("#modalInquiry .modal-content")
    });



    $("#inquiry_source_user_2").select2({
        ajax: {
            url: ajaxURLSearchUser,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    "source_type": function() {
                        var pieces_inquiry_source_type = $("#inquiry_source_type_2").val().split("-");
                        return pieces_inquiry_source_type[1]
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
        placeholder: 'Search for a user',
        dropdownParent: $("#modalInquiry .modal-content")
    });

    $("#inquiry_source_user_3").select2({
        ajax: {
            url: ajaxURLSearchUser,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    "source_type": function() {
                        var pieces_inquiry_source_type = $("#inquiry_source_type_3").val().split("-");
                        return pieces_inquiry_source_type[1]
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
        placeholder: 'Search for a user',
        dropdownParent: $("#modalInquiry .modal-content")
    });

    $("#inquiry_source_user_4").select2({
        ajax: {
            url: ajaxURLSearchUser,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    "source_type": function() {
                        var pieces_inquiry_source_type = $("#inquiry_source_type_4").val().split("-");
                        return pieces_inquiry_source_type[1]
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
        placeholder: 'Search for a user',
        dropdownParent: $("#modalInquiry .modal-content")
    });




    $("#inquiry_architect").select2({
        ajax: {
            url: ajaxURLSearchArchitect,
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
        placeholder: 'Search for architect',
        dropdownParent: $("#modalInquiry .modal-content")
    });

    $('#inquiry_architect').on('change', function() {


        $.ajax({
            type: 'GET',
            url: ajaxURLSearchArchitect + "?user_id=" + $(this).val(),
            success: function(resultData) {

                if (resultData['results'].length > 0) {

                    $("#inquiry_architect_phone_number").val(resultData['results'][0]['phone_number']);

                } else {
                    $("#inquiry_architect_phone_number").val("");
                }

            }
        });



    });




    $("#inquiry_electrician").select2({
        ajax: {
            url: ajaxURLSearchElectrician,
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
        placeholder: 'Search for electrician',
        dropdownParent: $("#modalInquiry .modal-content")
    });

    $('#inquiry_electrician').on('change', function() {


        $.ajax({
            type: 'GET',
            url: ajaxURLSearchElectrician + "?user_id=" + $(this).val(),
            success: function(resultData) {

                if (resultData['results'].length > 0) {


                    $("#inquiry_electrician_phone_number").val(resultData['results'][0]['phone_number']);

                } else {
                    $("#inquiry_electrician_phone_number").val("");
                }

            }
        });



    });


    $('#inquiry_electrician').on('change', function() {


        $.ajax({
            type: 'GET',
            url: ajaxURLSearchElectrician + "?user_id=" + $(this).val(),
            success: function(resultData) {

                if (resultData['results'].length > 0) {


                    $("#inquiry_electrician_phone_number").val(resultData['results'][0]['phone_number']);

                } else {
                    $("#inquiry_electrician_phone_number").val("");
                }

            }
        });



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
        $('#formInquiry').ajaxForm(options);
        $('#formInquiryStatusChange').ajaxForm(options);
        $('#formAssignedTo').ajaxForm(options);
        $('#formQuotation').ajaxForm(options);
        $('#formBilling').ajaxForm(options);
        $('#formUpdateTM').ajaxForm(options);


    });

    function showRequest(formData, jqForm, options) {

        $("#formInquiryStatusChangeSave").html("Saving...");
        $("#formInquiryStatusChangeSave").prop("disabled", true);
        $("#submitUpdateTM").html("Saving...");
        $("#submitUpdateTM").prop("disabled", true);




        // if(jqForm[0].id=="formInquiryStatusChange"){



        //         var requiredCheckBoxIsValidate=1;

        //         $.each($('.checkbox-question'), function(i, val) {

        //   var checkBoxQuestionId = (this.id.split("-"))[3];



        // var anyOptionSelected= $(".checkbox-option-id-"+checkBoxQuestionId+":checked").val();

        // if(anyOptionSelected===undefined){
        // requiredCheckBoxIsValidate=0;
        // }





        //           });


        //         if(requiredCheckBoxIsValidate==0){

        //             toastr["error"]("Required at least one checkbox should checked in multi checkbox questions");
        //             return false;
        //         }else{



        //                    return true;





        //         }



        // }else{



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

        //}
    }

    // post-submit callback
    function showResponse(responseText, statusText, xhr, $form) {

        $("#formInquiryStatusChangeSave").html("Save");
        $("#formInquiryStatusChangeSave").prop("disabled", false);
        $("#submitUpdateTM").html("Save");
        $("#submitUpdateTM").prop("disabled", false);

        if (responseText['status'] == 1) {
            if ($form[0].id == "formInquiry" || $form[0].id == "formBilling" || $form[0].id == "formQuotation" || $form[0].id == "formAssignedTo" || $form[0].id == "formInquiryStatusChange" || $form[0].id == "formUpdateTM") {

                scrollTopHeightDataTable = $('.dataTables_scrollBody').prop('scrollTop');


            } else {
                scrollTopHeightDataTable = 0;
            }
            toastr["success"](responseText['msg']);
            reloadTable();
            resetInputForm();
            $("#modalInquiry").modal('hide');
            $("#modalStatusChange").modal('hide');
            $("#modalAssignedToChange").modal('hide');
            $("#modalQuotation").modal('hide');
            $("#modalBilling").modal('hide');
            $("#modalUpdateTM").modal('hide');





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


    var csrfToken = $("[name=_token").val();
    var inquiryLogUserId = 0;
    var inquiryLogType = 0;

    var inquiryLogTable = $('#InquiryLogTable').DataTable({
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
                    return inquiryLogUserId
                },
                "type": function() {
                    return inquiryLogType
                },
            }
        },
        "aoColumns": [

            {
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
        if (userId != 0) {
            inquiryLogUserId = userId;
            $("#modalInquiryLog").modal('show');
            inquiryLogType = 0;
            inquiryLogTable.ajax.reload();


        }


    }

    inquiryLogTable.on('xhr', function() {
        if (isArchitect == 0 && isElectrician == 0) {
            var responseData = inquiryLogTable.ajax.json();
            $(inquiryLogTable.column(4).header()).text(responseData['column4']);
            $(inquiryLogTable.column(5).header()).text(responseData['column5']);
            $("#totalInquiry").html(responseData['overview']['total_inquiry']);
            $("#totalRunningInquiry").html(responseData['overview']['total_running']);
            $("#totalWonInquiry").html(responseData['overview']['total_won']);
            $("#totalRejectedInquiry").html(responseData['overview']['total_rejected']);
            $("#totalInquiryLogQuotationAmount").html(responseData['quotationAmount']);
            $("#modalPointLogLabel").html(responseData['title']);
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