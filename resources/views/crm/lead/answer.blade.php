<input type="hidden" name="lead_id" id="lead_id" value='{{ $data['lead_id'] }}'>
<input type="hidden" name="lead_status" id="lead_status" value='{{ $data['lead_status'] }}'>


@foreach ($data['question'] as $keyQ => $valueQ)
    @if ($valueQ->type == 0)
        <div class="row" id="row_answer_{{ $valueQ->id }}">
            <div class="col-md-12">
                <div class="mb-3">
                    <label for="lead_questions_{{ $valueQ->id }}"
                        class="form-label inquiry-questions-lable">{{ $valueQ->question }} @if ($valueQ->is_required == 1)
                            <code class="highlighter-rouge">*</code>
                        @endif
                    </label>
                    <textarea id="lead_questions_{{ $valueQ->id }}" name="lead_questions_{{ $valueQ->id }}" class="form-control"
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
                    <label for="lead_questions_{{ $valueQ->id }}"
                        class="form-label inquiry-questions-lable">{{ $valueQ->question }} @if ($valueQ->is_required == 1)
                            <code class="highlighter-rouge">*</code>
                        @endif
                    </label>
                    <select id="lead_questions_{{ $valueQ->id }}" name="lead_questions_{{ $valueQ->id }}" class="form-select select2-apply" @if ($valueQ->is_required == 1) required @endif>
                        <option value="">Select Option</option>
                        @foreach ($valueQ['options'] as $OptK => $OptV)
                            @if($OptV->is_database_side == 1)
                                @foreach($valueQ['user_list'] as $OptChKey => $OptChValue)
                                    <option value="{{ $OptChValue->id }}">{{ $OptChValue->text }} </option>
                                @endforeach
                            @else
                                <option value="{{ $OptV->id }}">{{ $OptV->option }} </option>
                            @endif
                        @endforeach
                    </select>
                    <div class="invalid-feedback">Please select option</div>
                </div>
            </div>
            <span class="div-end-line"></span>
        </div>
        
    @endif
    @if ($valueQ->type == 4)
        <div class="row" id="row_answer_{{ $valueQ->id }}">
            <div class="col-md-12">
                <div class="mb-3">
                    <label for="lead_questions_{{ $valueQ->id }}"
                        class="form-label inquiry-questions-lable">{{ $valueQ->question }} @if ($valueQ->is_required == 1)
                            <code class="highlighter-rouge">*</code>
                        @endif
                    </label>
                    <select multiple="multiple" id="lead_questions_{{ $valueQ->id }}"
                        name="lead_questions_{{ $valueQ->id }}[]" class="form-select select2-multi-apply"
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
                    <label for="lead_questions_{{ $valueQ->id }}"
                        class="form-label inquiry-questions-lable">{{ $valueQ->question }} @if ($valueQ->is_required == 1)
                            <code class="highlighter-rouge">*</code>
                        @endif
                    </label>
                    <input type="number" id="lead_questions_{{ $valueQ->id }}"
                        name="lead_questions_{{ $valueQ->id }}" class="form-control"
                        @if ($valueQ->is_required == 1) required @endif />
                </div>
            </div>
            <span class="div-end-line"></span>
        </div>
    @endif
    @if ($valueQ->type == 8)
        <div class="row" id="row_answer_{{ $valueQ->id }}">
            <div class="col-md-12">
                <div class="mb-3">
                    <label for="lead_questions_{{ $valueQ->id }}"
                        class="form-label inquiry-questions-lable">{{ $valueQ->question }} @if ($valueQ->is_required == 1)
                            <code class="highlighter-rouge">*</code>
                        @endif
                    </label>
                    <input type="text" id="lead_questions_{{ $valueQ->id }}"
                        name="lead_questions_{{ $valueQ->id }}" class="form-control"
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
                    1.&nbsp; <label for="lead_questions_{{ $valueQ->id }}"
                        class="form-label lead-questions-lable">{{ $valueQ->question }}
                        @if ($valueQ->is_required == 1)
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
                                name="lead_questions_{{ $valueQ->id }}[{{ $OptV->id }}]">
                            <label class="form-check-label"
                                for="checkbox_option_{{ $OptV->id }}">{{ $OptV->option }} </label>
                        </div>
                    @endforeach
                </div>
            </div>
            <span class="div-end-line">

            </span>
        </div>
    @endif
    @if ($valueQ->type == 7)
        <div class="row" id="row_answer_{{ $valueQ->id }}">
            <div class="col-md-12">
                <div class="mb-3">
                    <label for="lead_questions_{{ $valueQ->id }}"
                        class="form-label inquiry-questions-lable">{{ $valueQ->question }} @if ($valueQ->is_required == 1)
                            <code class="highlighter-rouge">*</code>
                        @endif <span id="answer-value-{{ $valueQ->id }}"></span></label>
                    <input class="form-control" type="file" value=""
                        id="lead_questions_{{ $valueQ->id }}" name="lead_questions_{{ $valueQ->id }}[]"
                        multiple @if ($valueQ->is_required == 1) required @endif>

                </div>
            </div>
            <span class="div-end-line"></span>
        </div>
    @endif
    @if ($valueQ->is_depend_on_answer == 1)
        <script type="text/javascript">
            @if ($valueQ['depended_question']->type == 1)

                $('#lead_questions_{{ $valueQ['depended_question']->id }}').on('change', function() {
                    if ($(this).val() == "{{ $valueQ->depended_question_answer }}") {
                        $("#row_answer_{{ $valueQ->id }}").show();
                        $(this).attr('required', true);

                    } else {
                        $("#row_answer_{{ $valueQ->id }}").hide();
                        $(this).removeAttr('required');
                    }
                });
            @endif

            @if ($valueQ['depended_question']->type == 4)

                $('#lead_questions_{{ $valueQ['depended_question']->id }}').change(function() {


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
                $('#lead_questions_{{ $valueQ['depended_question']->id }}').trigger("change")
            @endif

            @if ($valueQ['depended_question']->type == 4)
                $('#lead_questions_{{ $valueQ['depended_question']->id }}').trigger("change")
            @endif

            @if ($valueQ['depended_question']->type == 6)
                $('#checkbox_option_{{ $valueQ->depended_question_answer }}').trigger("change")
            @endif
        @endif
    @endforeach

    $(document).ready(function() {
        $('#lead_questions_6').select2({
            minimumResultsForSearch: 5,
            dropdownParent: $('#row_answer_6')
        });
        $('#lead_questions_19').select2({
            minimumResultsForSearch: 5,
            dropdownParent: $('#row_answer_19')
        });
    })
</script>
