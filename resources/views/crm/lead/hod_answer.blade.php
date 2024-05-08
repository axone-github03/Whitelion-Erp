<input type="hidden" name="lead_id" id="lead_id" value='{{ $data['lead_id'] }}'>
<input type="hidden" name="lead_status" id="lead_status" value='{{ $data['lead_status'] }}'>


@foreach ($data['question'] as $keyQ => $valueQ)
    @if ($valueQ->type == 6)
        <div class="row" id="row_answer_{{ $valueQ->id }}">
            <div class="col-md-12">
                <div class="mb-3">
                    1.&nbsp; <label for="lead_questions_{{ $valueQ->id }}" class="form-label lead-questions-lable mt-3">{{ $valueQ->question }}
                        @if ($valueQ->is_required == 1)
                            <code class="highlighter-rouge">*</code>
                        @endif
                    </label>
                    @if ($valueQ->is_required == 1)
                        <input type="hidden" id="question-id-{{ $valueQ->id }}" class="-question">
                    @endif
                    @foreach ($valueQ['options'] as $OptK => $OptV)
                        <div class="form-check form-check-primary mb-3">
                            {{-- <input class="option-id-{{ $valueQ->id }}" type="" id="checkbox_option_{{ $OptV->id }}" name="lead_questions_{{ $valueQ->id }}[{{ $OptV->id }}]"> --}}
                            <label class="form-check-label " for="checkbox_option_{{ $OptV->id }}">{{ $OptV->option }} </label>
                        </div>
                    @endforeach
                </div>
            </div>
            <span class="div-end-line">

            </span>
        </div>
    @endif
@endforeach