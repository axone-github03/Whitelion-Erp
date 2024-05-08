<div class="card-header bg-transparent border-bottom">
    <b class="me-2">HOD Approved</b>{!! $data['hod_status'] !!}
    <div class="lds-spinner" id="contact_loader" style="display: none;">
        <div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div>
    </div>
</div>

<div class="card-body">
    <table class="table table-striped table-sm mb-0">
        <thead>
            <tr>
                <th>Tag</th>
                <th>Name</th>
                <th>Mobile Number</th>
            </tr>
        </thead>
        <tbody id="leadContactTBody">
           
        </tbody>
    </table>
</div>