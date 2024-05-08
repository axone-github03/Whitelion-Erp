<div class="card-header bg-transparent border-bottom">
    <b> Notes </b> <i id="note_loader" class="bx bx-loader bx-spin font-size-16 align-middle me-2" style="display: none;"></i>
    
</div>
<div class="card-body mb-2">
    <div id="userNotesBody">
        @foreach ($data['updates'] as $update)
            <div class="d-flex align-items-center mb-3">
                <div>
                    <i class='bx bxs-user-circle' style="color: #dbdbdb;font-size: 3rem;"></i>
                </div>
                <div class="ms-2">
                    <p class="mb-0 d-flex justify-content-between" style="font-weight: 600;">
                        {!! $update['message'] !!}<span style="font-size: 12px;color: #5a5a5a94;"
                            class="ms-5">
                            {{-- <i class='bx bxs-pencil'></i>
                            <i class='bx bx-trash ms-2'></i> --}}
                        </span></p>
                    <span class="mb-0" style="font-weight: 600;">{{ $update['note_type'] }} -
                    </span><span class="text-primary"
                        style="font-weight: 600;">{{ $update['note_title'] }}</span>
                    <span class="mb-0 ms-5">{{ $update['created_at'] }} By
                        {{ $update['first_name'] }} {{ $update['last_name'] }}</span>
                </div>
            </div>
        @endforeach
    </div>
    @if(isCreUser() == 0) 
        <div class="d-flex align-items-center border-top pt-2">
            <div class="col-5">
                <textarea type="text" class="form-control add_new_note" id="user_notes" placeholder="Add Note" rows="2"></textarea>
            </div>
            <div class="ps-3">
                <button type="button" class="btn btn-sm btn-primary  save-btn"
                    onclick="saveUpdate()">Save</button>
            </div>
        </div>
    @endif
</div>
