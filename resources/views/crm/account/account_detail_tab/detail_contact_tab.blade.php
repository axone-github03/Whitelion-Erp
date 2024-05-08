<div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
    <b>Contact Details <div class="lds-spinner" id="detail_loader" style="display: none;">
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
        <button onclick="addLeadContactModal()" class="btn btn-sm btn-light btn-header-right waves-effect waves-light float-end mr-2" type="button" style="margin-left:3px;"><i class="bx bx-plus font-size-16 align-middle "></i></button>
        <button onclick="viewAllLeadUpdates()" class="btn btn-sm btn-light btn-header-right waves-effect waves-light float-end " type="button" data-bs-toggle="offcanvas" data-bs-target="#canvasGiftCategory" aria-controls="canvasGiftCategory">See All </button>
    </div>
</div>
<div class="card-body border-bottom" id="lead_detail">
    <table class="table table-sm table-striped  mb-0">

        <thead>
            <tr>
                <th class="col-4">Role</th>
                <th class="col-4">Name</th>
                <th class="col-4">Mobile Number</th>
                {{-- <th>Alternate Number</th>
                <th>Email id</th> --}}
            </tr>
        </thead>
        <tbody id="contactTBody">
            @foreach($data['contacts'] as $contact)
            <tr id="tr_contact_{{$contact['id']}}">
                <td>{{$contact['tag_name']}}</td>
                <td>{{$contact['first_name']}} {{$contact['last_name']}}</td>
                <td>{{$contact['phone_number']}} </td>
                {{-- <td>{{$contact['alernate_phone_number']}}</td> --}}
                {{-- <td>{{$contact['email']}}</td> --}}
            </tr>
            @endforeach

        </tbody>
    </table>
</div>