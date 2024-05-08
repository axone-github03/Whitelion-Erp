<div class="card-header bg-transparent border-bottom">
    <b> Notes</b>
    <div class="lds-spinner" id="note_loader" style="display: none;">
        <div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div>
      </div>
    <button onclick="viewAllLeadUpdates({{ $data['lead_id']}})"
        class="btn btn-sm btn-light btn-header-right waves-effect waves-light float-end "
        type="button" data-bs-toggle="offcanvas" data-bs-target="#canvasGiftCategory"
        aria-controls="canvasGiftCategory">See All </button>
</div>
<div class="card-body mb-2">
    <div id="leadUpdateTBody">
        @foreach ($data['updates'] as $update)
            <div class="d-flex align-items-center mb-3">
                <div>
                    <i class='bx bxs-user-circle' style="color: #dbdbdb;font-size: 3rem;"></i>
                </div>
                <div class="ms-2">
                    <p class="mb-0 d-flex justify-content-between" style="font-weight: 600;">
                        {!! $update['message'] !!}<span style="font-size: 12px;color: #5a5a5a94;"
                            class="ms-5"></span></p>
                    <span class="mb-0" style="font-weight: 600;">{{ $update['task'] }} -
                    </span><span class="text-primary"
                        style="font-weight: 600;">{{ $update['task_title'] }}</span>
                    <span class="mb-0 ms-5">{{ $update['date'] }}, {{ $update['time'] }} By
                        {{ $update['first_name'] }} {{ $update['last_name'] }}</span>
                </div>
            </div>
        @endforeach
    </div>
    {{-- @if(Auth::user()->type != 9) --}}
    <form>
        <div class="d-flex align-items-center">
            <div class="col-5">
                <textarea type="text" class="form-control add_new_note" id="lead_update" placeholder="Add Note" rows="2"></textarea>
            </div>
            <div class="ps-3">
                <button type="button" class="btn btn-sm btn-primary  save-btn"
                    onclick="saveUpdate({{ $data['lead_id'] }})">Save</button>
            </div>
        </div>
    </form>
    {{-- @endif --}}
</div>