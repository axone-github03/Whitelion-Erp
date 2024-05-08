<div class="card-header bg-transparent border-bottom">
    <b> Notes</b>
    <div class="lds-spinner" id="note_loader" style="display: none;">
        <div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div>
      </div>
    <button onclick="viewAllLeadUpdates()" class="btn btn-sm btn-light btn-header-right waves-effect waves-light float-end " type="button" data-bs-toggle="offcanvas" data-bs-target="#canvasGiftCategory" aria-controls="canvasGiftCategory">See All </button>
</div>
<div class="card-body mb-2 border-bottom">
    <div id="leadUpdateTBody">
    
    </div>
    <form>
        <div class="d-flex align-items-center">
            <div class="col-5">
                <textarea type="text" class="form-control add_new_note" id="lead_update" placeholder="Add Note" rows="2"></textarea>
            </div>
            <div class="ps-3">
                <button type="button" class="btn btn-sm btn-primary  save-btn" onclick="">Save</button>
            </div>
        </div>
    </form>
</div>