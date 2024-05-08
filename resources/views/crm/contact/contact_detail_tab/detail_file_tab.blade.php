<div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
    <b>File Details <div class="lds-spinner" id="detail_loader" style="display: none;">
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
        </div></b>

    <div>
        <button onclick="addLeadContactModal(154)" class="btn btn-sm btn-light btn-header-right waves-effect waves-light float-end mr-2" type="button" style="margin-left:3px;"><i class="bx bx-plus font-size-16 align-middle "></i></button>
        <button onclick="viewAllLeadUpdates()" class="btn btn-sm btn-light btn-header-right waves-effect waves-light float-end " type="button" data-bs-toggle="offcanvas" data-bs-target="#canvasGiftCategory" aria-controls="canvasGiftCategory">See All </button>
    </div>
</div>
<div class="card-body border-bottom" id="lead_detail">
    <table class="table table-sm table-striped  mb-0">

        <thead>
            <tr>
                <th>File Name</th>
                <th>File Tag</th>
                <th>Uploaded by</th>
                <th>Date Attached</th>
                <th>Size</th>
            </tr>
        </thead>
        <tbody id="fileTBody">
            <tr>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
            </tr>
        </tbody>
    </table>
</div>