<div class="row">
    <div class="col-md-12">
        <div class="mb-3">
            <label class="form-label">Write an update</label>
            <textarea id="user_update_message_0" class="form-control user_update_message" rows="3"></textarea>
        </div>

    </div>
</div>
<button class="btn btn-outline-primary waves-effect waves-light" onclick="saveUserUpdate({{$data['for_user_id']}},0)" id="inquiry_update-save-0">Update</button>
<br>
<br>

<ul class="list-unstyled chat-list" id="inquiry_update_ul" data-simplebar>

    @foreach($data['update'] as $keyU=>$valueU)
    <li>
        <div class="inquiry-update-box">
            <div class="d-flex">

                <div class="avatar-xs inquiry-avatar-xs me-3">
                    <span class="avatar-title rounded-circle bg-primary bg-soft text-primary">
                        @php
                        $valueU->first_name=trim($valueU->first_name);
                        $valueU->last_name=trim($valueU->last_name);


                        $firstLetterA = strtoupper(substr($valueU->first_name,0,1));

                        $firstLetterB = strtoupper(substr($valueU->last_name,0,1));


                        @endphp
                        {{$firstLetterA}}{{$firstLetterB}}
                    </span>
                </div>
                <div class="flex-grow-1 overflow-hidden">
                    <h5 class="text-truncate font-size-14 mb-1">{{$valueU->first_name}} {{$valueU->last_name}}</h5>
                    <p class="user-update-text mb-2">{!! $valueU->message !!}</p>
                </div>
                <div class="font-size-11 user-update-time">{{convertDateTime($valueU->created_at)}}
                    <br>




                    <button id="seen-btn-{{$valueU->id}}" type="button" class="btn seen-btn" data-bs-toggle="tooltip" data-bs-html="true" title="<i class='bx bx-loader bx-spin font-size-16 align-middle me-2'></i>">
                        <i class="font-size-20 mdi mdi-eye"></i>
                    </button>







                    <button class="btn btn-primary btn-sm" onclick="openReplyBox({{$valueU->id}})">Reply</button>

                </div>

            </div>
            <br>

            <ul class="list-unstyled chat-list" data-simplebar>

                @foreach($valueU['reply'] as $keyUR=>$valueUR)


                <li>
                    <div class="user-update-reply-box">

                        <div class="d-flex reply-content">
                            <div class="flex-shrink-0  me-3">
                                <i class="mdi mdi-reply-all font-size-20"></i>
                            </div>

                            <div class="avatar-xs inquiry-avatar-xs me-3">
                                <span class="avatar-title rounded-circle bg-primary bg-soft text-primary">
                                    @php
                                    $valueU->first_name=trim($valueUR->first_name);
                                    $valueU->last_name=trim($valueUR->last_name);


                                    $firstLetterA = strtoupper(substr($valueUR->first_name,0,1));

                                    $firstLetterB = strtoupper(substr($valueUR->last_name,0,1));


                                    @endphp
                                    {{$firstLetterA}}{{$firstLetterB}}
                                </span>
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <h5 class="text-truncate font-size-14 mb-1">{{$valueUR->first_name}} {{$valueUR->last_name}}</h5>
                                <p class="user-update-text mb-2">{!! $valueUR->message !!}</p>


                            </div>
                            <div class="font-size-11 user-update-time">{{convertDateTime($valueU->created_at)}}

                                <br>
                                <button id="seen-btn-{{$valueUR->id}}" type="button" class="btn seen-btn" data-bs-toggle="tooltip" data-bs-html="true" title="<i class='bx bx-loader bx-spin font-size-16 align-middle me-2'></i>">
                                    <i class="font-size-20 mdi mdi-eye"></i>
                                </button>






                            </div>



                        </div>
                    </div>
                </li>
                @endforeach
            </ul>
            <br>
            <div class="reply-box" id="reply-box-{{$valueU->id}}">
                <textarea id="user_update_message_{{$valueU->id}}" class="form-control user_update_message" rows="3"></textarea>
                <br>
                <button class="btn btn-outline-primary waves-effect waves-light" onclick="saveUserUpdate({{$data['for_user_id']}},{{$valueU->id}})" id="inquiry_update-save-{{$valueU->id}}">Reply</button>
            </div>
        </div>
    </li>
    @endforeach


</ul>