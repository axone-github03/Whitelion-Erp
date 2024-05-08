<div class="card-header bg-transparent border-bottom">
    <b>Service Details</b>
    @if(isCreUser() == 0) 
        <button onclick=""
            class="btn btn-sm btn-light btn-header-right waves-effect waves-light float-end mr-2 d-none"
            type="button" style="margin-left:3px;"><i class="bx bx-plus font-size-16 align-middle "></i>
        </button>
    @endif
    <button onclick=""
        class="btn btn-sm btn-light btn-header-right waves-effect waves-light float-end d-none"
        type="button">See
        All </button>

</div>
<div class="card-body mb-2 p-3">
    <table class="table table-sm table-striped  mb-0">
        <thead>
            <tr>
                <th>Service No</th>
                <th>Account Name</th>
                <th>Status</th>
                <th>Sub Status</th>
                <th>Scheduled Date</th>
                <th>Assigned to</th>
            </tr>
        </thead>
    </table>
</div>