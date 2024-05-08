<input type="hidden" name="inquiry_id" id="inquiry_id" value='{{ $data['inquiry_id'] }}'>
<input type="hidden" name="inquiry_status" id="inquiry_status" value='{{ $data['inquiry_status'] }}'>


@foreach ($data['question'] as $keyQ => $valueQ)
    @if ($valueQ->type == 0)
        <div class="row" id="row_answer_{{ $valueQ->id }}">
            <div class="col-md-12">
                <div class="mb-3">
                    <label for="inquiry_questions_{{ $valueQ->id }}"
                        class="form-label inquiry-questions-lable">{{ $valueQ->question }} @if ($valueQ->is_required == 1)
                            <code class="highlighter-rouge">*</code>
                        @endif
                    </label>
                    <textarea id="inquiry_questions_{{ $valueQ->id }}" name="inquiry_questions_{{ $valueQ->id }}" class="form-control"
                        rows="3" @if ($valueQ->is_required == 1) required @endif></textarea>
                </div>
            </div>

            <span class="div-end-line"></span>
        </div>
    @endif

    @if ($valueQ->type == 1)
        <div class="row" id="row_answer_{{ $valueQ->id }}">
            <div class="col-md-12">
                <div class="mb-3">
                    <label for="inquiry_questions_{{ $valueQ->id }}"
                        class="form-label inquiry-questions-lable">{{ $valueQ->question }} @if ($valueQ->is_required == 1)
                            <code class="highlighter-rouge">*</code>
                        @endif
                    </label>
                    <select id="inquiry_questions_{{ $valueQ->id }}" name="inquiry_questions_{{ $valueQ->id }}"
                        class="form-select select2-apply" @if ($valueQ->is_required == 1) required @endif>

                        <option value="">Select Option</option>
                        @foreach ($valueQ['options'] as $OptK => $OptV)
                            <option value="{{ $OptV->id }}">{{ $OptV->option }} </option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback">
                        Please select option
                    </div>
                </div>
            </div>
            <span class="div-end-line"></span>

        </div>
    @endif

    @if ($valueQ->type == 2)
        <div class="row" id="row_answer_{{ $valueQ->id }}">
            <div class="col-md-12">
                <div class="mb-3">
                    <label for="inquiry_questions_{{ $valueQ->id }}"
                        class="form-label inquiry-questions-lable">{{ $valueQ->question }} @if ($valueQ->is_required == 1)
                            <code class="highlighter-rouge">*</code>
                        @endif <span id="answer-value-{{ $valueQ->id }}"></span></label>
                    <input class="form-control" type="file" value=""
                        id="inquiry_questions_{{ $valueQ->id }}"
                        name="inquiry_questions_{{ $valueQ->id }}"@if ($valueQ->is_required == 1) required @endif>

                </div>
            </div>
            <span class="div-end-line"></span>
        </div>
    @endif

    @if ($valueQ->type == 3)
        <div class="row" id="row_answer_{{ $valueQ->id }}">
            <div class="col-md-12">
                <div class="form-check form-check-primary mb-3">




                    <input id="inquiry_questions_{{ $valueQ->id }}" name="inquiry_questions_{{ $valueQ->id }}"
                        class="form-check-input" type="checkbox" @if ($valueQ->is_required == 1) required @endif>
                    <label class="form-check-label inquiry-questions-lable"
                        for="inquiry_questions_{{ $valueQ->id }}">
                        {{ $valueQ->question }} @if ($valueQ->is_required == 1)
                            <code class="highlighter-rouge">*</code>
                        @endif
                    </label>
                </div>
            </div>
            <span class="div-end-line"></span>
        </div>
    @endif

    @if ($valueQ->type == 4)
        <div class="row" id="row_answer_{{ $valueQ->id }}">
            <div class="col-md-12">
                <div class="mb-3">
                    <label for="inquiry_questions_{{ $valueQ->id }}"
                        class="form-label inquiry-questions-lable">{{ $valueQ->question }} @if ($valueQ->is_required == 1)
                            <code class="highlighter-rouge">*</code>
                        @endif
                    </label>
                    <select multiple="multiple" id="inquiry_questions_{{ $valueQ->id }}"
                        name="inquiry_questions_{{ $valueQ->id }}[]" class="form-select select2-multi-apply"
                        @if ($valueQ->is_required == 1) required @endif>


                        @foreach ($valueQ['options'] as $OptK => $OptV)
                            <option value="{{ $OptV->id }}">{{ $OptV->option }} </option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback">
                        Please select option
                    </div>
                </div>
            </div>
            <span class="div-end-line"></span>
        </div>
    @endif


    @if ($valueQ->type == 5)
        <div class="row" id="row_answer_{{ $valueQ->id }}">
            <div class="col-md-12">
                <div class="mb-3">
                    <label for="inquiry_questions_{{ $valueQ->id }}"
                        class="form-label inquiry-questions-lable">{{ $valueQ->question }} @if ($valueQ->is_required == 1)
                            <code class="highlighter-rouge">*</code>
                        @endif
                    </label>
                    <input type="number" id="inquiry_questions_{{ $valueQ->id }}"
                        name="inquiry_questions_{{ $valueQ->id }}" class="form-control"
                        @if ($valueQ->is_required == 1) required @endif />
                </div>
            </div>
            <span class="div-end-line"></span>
        </div>
    @endif


    @if ($valueQ->type == 6)
        <div class="row" id="row_answer_{{ $valueQ->id }}">
            <div class="col-md-12">
                <div class="mb-3">
                    <label for="inquiry_questions_{{ $valueQ->id }}"
                        class="form-label inquiry-questions-lable">{{ $valueQ->question }} @if ($valueQ->is_required == 1)
                            <code class="highlighter-rouge">*</code>
                        @endif
                    </label>











                    @if ($valueQ->is_required == 1)
                        <input type="hidden" id="checkbox-question-id-{{ $valueQ->id }}" class="checkbox-question">
                    @endif
                    @foreach ($valueQ['options'] as $OptK => $OptV)
                        <div class="form-check form-check-primary mb-3">
                            <input class="form-check-input checkbox-option-id-{{ $valueQ->id }}" type="checkbox"
                                id="checkbox_option_{{ $OptV->id }}"
                                name="inquiry_questions_{{ $valueQ->id }}[{{ $OptV->id }}]">
                            <label class="form-check-label " for="checkbox_option_{{ $OptV->id }}">
                                {{ $OptV->option }}
                            </label>
                        </div>
                    @endforeach


                </div>
            </div>
            <span class="div-end-line"></span>
        </div>
    @endif

    @if ($valueQ->type == 7)
        <div class="row" id="row_answer_{{ $valueQ->id }}">
            <div class="col-md-12">
                <div class="mb-3">
                    <label for="inquiry_questions_{{ $valueQ->id }}"
                        class="form-label inquiry-questions-lable">{{ $valueQ->question }} @if ($valueQ->is_required == 1)
                            <code class="highlighter-rouge">*</code>
                        @endif <span id="answer-value-{{ $valueQ->id }}"></span></label>
                    <input class="form-control" type="file" value=""
                        id="inquiry_questions_{{ $valueQ->id }}" name="inquiry_questions_{{ $valueQ->id }}[]"
                        multiple @if ($valueQ->is_required == 1) required @endif>

                </div>
            </div>
            <span class="div-end-line"></span>
        </div>
    @endif


    @if ($valueQ->is_depend_on_answer == 1)
        <script type="text/javascript">
            @if ($valueQ['depended_question']->type == 1)

                $('#inquiry_questions_{{ $valueQ['depended_question']->id }}').change(function() {
                    if ($(this).val() == "{{ $valueQ->depended_question_answer }}") {
                        $("#row_answer_{{ $valueQ->id }}").show();
                        $(this).attr('required', true);

                    } else {
                        $("#row_answer_{{ $valueQ->id }}").hide();
                        $(this).removeAttr('required');
                    }


                });
            @endif

            @if ($valueQ['depended_question']->type == 3)

                $('#inquiry_questions_{{ $valueQ['depended_question']->id }}').change(function() {


                    @if ($valueQ->depended_question_answer == '1')

                        if ($(this).is(':checked')) {
                            $("#row_answer_{{ $valueQ->id }}").show();
                            $(this).attr('required', true);
                        } else {
                            $("#row_answer_{{ $valueQ->id }}").hide();
                            $(this).removeAttr('required');
                        }
                    @endif

                    @if ($valueQ->depended_question_answer == '0')

                        if (!$(this).is(':checked')) {
                            $("#row_answer_{{ $valueQ->id }}").show();
                            $(this).attr('required', true);

                        } else {
                            $("#row_answer_{{ $valueQ->id }}").hide();
                            $(this).removeAttr('required');
                        }
                    @endif





                });
            @endif


            @if ($valueQ['depended_question']->type == 4)

                $('#inquiry_questions_{{ $valueQ['depended_question']->id }}').change(function() {


                    if ($(this).val().includes("{{ $valueQ->depended_question_answer }}")) {
                        $("#row_answer_{{ $valueQ->id }}").show();
                        $(this).attr('required', true);
                    } else {
                        $("#row_answer_{{ $valueQ->id }}").hide();
                        $(this).removeAttr('required');
                    }


                });
            @endif

            @if ($valueQ['depended_question']->type == 6)

                $('#checkbox_option_{{ $valueQ->depended_question_answer }}').change(function() {



                    if ($(this).is(':checked')) {
                        $("#row_answer_{{ $valueQ->id }}").show();
                        $(this).attr('required', true);
                    } else {
                        $("#row_answer_{{ $valueQ->id }}").hide();
                        $(this).removeAttr('required');
                    }




                });
            @endif
        </script>
    @endif
@endforeach
<script type="text/javascript">
    @foreach ($data['question'] as $keyQ => $valueQ)
        @if ($valueQ->is_depend_on_answer == 1)
            @if ($valueQ['depended_question']->type == 1)
                $('#inquiry_questions_{{ $valueQ['depended_question']->id }}').trigger("change")
            @endif
            @if ($valueQ['depended_question']->type == 3)
                $('#inquiry_questions_{{ $valueQ['depended_question']->id }}').trigger("change")
            @endif
            @if ($valueQ['depended_question']->type == 4)
                $('#inquiry_questions_{{ $valueQ['depended_question']->id }}').trigger("change")
            @endif
            @if ($valueQ['depended_question']->type == 6)
                $('#checkbox_option_{{ $valueQ->depended_question_answer }}').trigger("change")
            @endif
        @endif
    @endforeach
</script>

@php
    $isChannelPartner = isChannelPartner(Auth::user()->type);
@endphp



@if (in_array(4, $data['questionStatusList']) && $isChannelPartner == 0)
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="answer_architect" class="form-label">Architect name </label>

                <select class="form-control select2-ajax" id="answer_architect" name="answer_architect"
                    placeholder="Architect name">
                </select>



            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label for="answer_architect_phone_number" class="form-label">Architect phone number</label>

                <div class="input-group">
                    <div class="input-group-text">
                        +91


                    </div>
                    <input type="number" class="form-control" id="answer_architect_phone_number"
                        name="answer_architect_phone_number" value="" disabled>
                </div>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="answer_electrician" class="form-label">Electrician name</label>

                <select class="form-control select2-ajax" id="answer_electrician" name="answer_electrician">
                </select>



            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label for="answer_electrician_phone_number" class="form-label">Electrician phone number</label>

                <div class="input-group">
                    <div class="input-group-text">
                        +91


                    </div>
                    <input type="number" class="form-control" id="answer_electrician_phone_number"
                        name="answer_electrician_phone_number" value="" disabled>
                </div>
            </div>
        </div>
    </div>
@endif

@if (in_array(9, $data['questionStatusList']) || in_array(11, $data['questionStatusList']))
    <div class="row">
        <div class="col-md-12">
            <div class="mb-3">
                <label for="answer_material_send_channel_partner" class="form-label">Which Channel partner Through A Material Sent on Site?</label>
                <select required class="form-control select2-ajax" id="answer_material_send_channel_partner"
                    name="answer_material_send_channel_partner">
                </select>
            </div>
        </div>

    </div>
@endif

@if ($data['need_followup'] == 1)

    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="answer_follow_up_type" class="form-label">Next Follow up type</label>

                <select class="form-select select2-apply" id="answer_follow_up_type" name="answer_follow_up_type"
                    required>
                    <option value="Meeting">Meeting</option>
                    <option value="Call">Call</option>

                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label for="answer_follow_up_date_time" class="form-label"> Date & Time of Follow up</label>






                <div class="input-group" id="answer_follow_up_date_time">
                    <input type="text" class="form-control" value="" placeholder="dd-mm-yyyy"
                        data-date-format="dd-mm-yyyy" data-date-container='#answer_follow_up_date_time'
                        data-provide="datepicker" data-date-autoclose="true" required name="answer_follow_up_date"
                        id="answer_follow_up_date">

                    <div style="width:50%;">

                        <select class="form-control" id="answer_follow_up_time" name="answer_follow_up_time">

                            @foreach ($data['timeSlot'] as $timeSlot)
                                <option value="{{ $timeSlot }}">{{ $timeSlot }} </option>
                            @endforeach
                        </select>
                    </div>


                </div>





            </div>
        </div>
    </div>

@endif
