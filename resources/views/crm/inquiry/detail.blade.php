@if ($data['ui_type'] == 'inquiry_update')

    <div class="row">
        <div class="col-md-12">
            <div class="mb-3">
                <label class="form-label">Write an update</label>
                <textarea id="inquiry_update_message_0" class="form-control inquiry_update_message" rows="3"></textarea>
            </div>

        </div>
    </div>
    <button class="btn btn-outline-primary waves-effect waves-light"
        onclick="saveInquiryUpdate({{ $data['inquiry_id'] }},0)" id="inquiry_update-save-0">Update</button>
    <br>
    <br>

    <ul class="list-unstyled chat-list" id="inquiry_update_ul" data-simplebar>

        @foreach ($data['update'] as $keyU => $valueU)
            <li>
                <div class="inquiry-update-box">
                    <div class="d-flex">

                        <div class="avatar-xs inquiry-avatar-xs me-3">
                            <span class="avatar-title rounded-circle bg-primary bg-soft text-primary">
                                @php
                                    $valueU->first_name = trim($valueU->first_name);
                                    $valueU->last_name = trim($valueU->last_name);
                                    
                                    $firstLetterA = strtoupper(substr($valueU->first_name, 0, 1));
                                    
                                    $firstLetterB = strtoupper(substr($valueU->last_name, 0, 1));
                                    
                                @endphp
                                {{ $firstLetterA }}{{ $firstLetterB }}
                            </span>
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <h5 class="text-truncate font-size-14 mb-1">{{ $valueU->first_name }}
                                {{ $valueU->last_name }}</h5>
                            <p class="inquiry-update-text mb-2">{!! $valueU->message !!}</p>
                        </div>
                        <div class="font-size-11 inquiry-update-time">{{ convertDateTime($valueU->created_at) }}
                            <br>




                            <button id="seen-btn-{{ $valueU->id }}" type="button" class="btn seen-btn"
                                data-bs-toggle="tooltip" data-bs-html="true"
                                title="<i class='bx bx-loader bx-spin font-size-16 align-middle me-2'></i>">
                                <i class="font-size-20 mdi mdi-eye"></i>
                            </button>







                            <button class="btn btn-primary btn-sm"
                                onclick="openReplyBox({{ $valueU->id }})">Reply</button>

                        </div>

                    </div>
                    <br>

                    <ul class="list-unstyled chat-list" data-simplebar>

                        @foreach ($valueU['reply'] as $keyUR => $valueUR)
                            <li>
                                <div class="inquiry-update-reply-box">

                                    <div class="d-flex reply-content">
                                        <div class="flex-shrink-0  me-3">
                                            <i class="mdi mdi-reply-all font-size-20"></i>
                                        </div>

                                        <div class="avatar-xs inquiry-avatar-xs me-3">
                                            <span class="avatar-title rounded-circle bg-primary bg-soft text-primary">
                                                @php
                                                    $valueU->first_name = trim($valueUR->first_name);
                                                    $valueU->last_name = trim($valueUR->last_name);
                                                    
                                                    $firstLetterA = strtoupper(substr($valueUR->first_name, 0, 1));
                                                    
                                                    $firstLetterB = strtoupper(substr($valueUR->last_name, 0, 1));
                                                    
                                                @endphp
                                                {{ $firstLetterA }}{{ $firstLetterB }}
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden">
                                            <h5 class="text-truncate font-size-14 mb-1">{{ $valueUR->first_name }}
                                                {{ $valueUR->last_name }}</h5>
                                            <p class="inquiry-update-text mb-2">{!! $valueUR->message !!}</p>


                                        </div>
                                        <div class="font-size-11 inquiry-update-time">
                                            {{ convertDateTime($valueU->created_at) }}

                                            <br>
                                            <button id="seen-btn-{{ $valueUR->id }}" type="button"
                                                class="btn seen-btn" data-bs-toggle="tooltip" data-bs-html="true"
                                                title="<i class='bx bx-loader bx-spin font-size-16 align-middle me-2'></i>">
                                                <i class="font-size-20 mdi mdi-eye"></i>
                                            </button>






                                        </div>



                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    <br>
                    <div class="reply-box" id="reply-box-{{ $valueU->id }}">
                        <textarea id="inquiry_update_message_{{ $valueU->id }}" class="form-control inquiry_update_message" rows="3"></textarea>
                        <br>
                        <button class="btn btn-outline-primary waves-effect waves-light"
                            onclick="saveInquiryUpdate({{ $data['inquiry_id'] }},{{ $valueU->id }})"
                            id="inquiry_update-save-{{ $valueU->id }}">Reply</button>
                    </div>
                </div>
            </li>
        @endforeach


    </ul>



@endif

@if ($data['ui_type'] == 'inquiry_files')

    @if (count($data['files']) == 0)
        <div class="alert alert-secondary alert-dismissible fade show" role="alert">
            No Files Found
        </div>
    @endif

    @if (count($data['files']) > 0)
        <table class="table table-bordered table-responsive">
            @foreach ($data['files'] as $file)
                <tr>
                    <td>Quotation ({{ convertDateTime($file->updated_at) }})</td>
                    <td>

                        <a target="_blank" href="{{ getSpaceFilePath($file->answer) }}"
                            class="btn btn-sm btn-dark waves-effect waves-light">
                            <i class="mdi mdi-download font-size-16 align-middle "></i>
                        </a>
                    </td>
                </tr>
            @endforeach
        </table>
    @endif
@endif
@if ($data['ui_type'] == 'inquiry_log')

    <ul class="verti-timeline list-unstyled">
        @foreach ($data['log'] as $keyl => $log)
            <li class="event-list @if ($keyl == 0) active @endif">
                <div class="event-timeline-dot">
                    <i class="bx bx-right-arrow-circle font-size-18"></i>
                </div>
                <div class="d-flex">
                    <div class="flex-shrink-0 me-3">
                        <h5 class="font-size-14">{{ convertDateTime($log->created_at) }} <i
                                class="bx bx-right-arrow-alt font-size-16 text-primary align-middle ms-2"></i></h5>
                    </div>
                    <div class="flex-shrink-0 me-3">
                        <h5 class="font-size-14">{{ $log->first_name }} {{ $log->last_name }}<i
                                class="bx bx-right-arrow-alt font-size-16 text-primary align-middle ms-2"></i></h5>
                    </div>
                    <div class="flex-grow-1">
                        <div>
                            {{ $log->description }}
                        </div>
                    </div>
                </div>
            </li>
        @endforeach


    </ul>
@endif

@if ($data['ui_type'] == 'inquiry_answer')

    @foreach ($data['answer'] as $keyQ => $valueQ)
        <div class="row">
            <div class="col-md-12">
                <div class="mb-3">
                    <label for="inquiry_questions_{{ $valueQ->id }}" class="form-label">{{ $keyQ + 1 }}.
                        {{ $valueQ->question }} @if ($valueQ->is_required == 1)
                            <code class="highlighter-rouge">*</code>
                        @endif </label>

                    @if ($valueQ->question_type == 2 || $valueQ->question_type == 7)
                        <p><b>Answer</b>: {!! $valueQ->answer !!}</p>
                    @else
                        <p><b>Answer</b>: {{ $valueQ->answer }}</p>
                    @endif


                </div>
            </div>
        </div>
        <div class="div-end-line"></div>
    @endforeach


@endif
