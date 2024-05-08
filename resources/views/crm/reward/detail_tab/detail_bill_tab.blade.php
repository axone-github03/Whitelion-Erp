<div class="card-header bg-transparent border-bottom">
    <div class="d-flex justify-content-between align-items-center bg-transparent">
        <div>
            <b>Bill</b>
            <div class="lds-spinner" id="file_loader" style="display: none;">
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
            </div>
        </div>
        <div class="d-flex align-items-center">
            @if($data['is_bill_upload'] == 1)
                <button onclick="addLeadFileModal({{ $data['lead_id'] }})" class="btn btn-sm btn-light waves-effect waves-light float-end mr-2" type="button" style="margin-left:3px;"><i class="bx bx-plus font-size-16 align-middle "></i></button>
            @endif
        </div>
    </div>
</div>

<div class="card-body">
    <table id="RewardPoint" class="table align-middle table-nowrap mb-0 w-100">
        <thead>
            <tr>
                <th>Bill Attached</th>
                <th>Bill Amount</th>
                <th>Point</th>
                <th>Query</th>
                <th>Lapsed</th>
                <th>Claim</th>
                <th>HOD Approved</th>
            </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
</div>
