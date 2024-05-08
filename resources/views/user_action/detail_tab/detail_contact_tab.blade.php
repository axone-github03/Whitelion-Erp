<div class="card-header bg-transparent border-bottom">
    <b>Contact Person</b><i id="contact_loader" class="bx bx-loader bx-spin font-size-16 align-middle me-2" style="display: none;"></i>

    @if(isCreUser() == 0) 
        <button onclick="addUserContactModal({{ $data['user']['id'] }})"
            class="btn btn-sm btn-light btn-header-right waves-effect waves-light float-end mr-2"
            type="button" style="margin-left:3px;"><i class="bx bx-plus font-size-16 align-middle "></i>
        </button>
    @endif

    {{-- <button 
    onclick="viewAllLeadContact({{ $data['lead_id'] }})"
        class="btn btn-sm btn-light btn-header-right waves-effect waves-light float-end "
        type="button">See
        All </button> --}}

</div>
<div class="card-body">
    <table class="table table-sm table-striped  mb-0">
        <thead>
            <tr>
                <th>Role</th>
                <th>Name</th>
                <th>Mobile Number</th>
                <th>Alternate Number</th>
                <th>Email id</th>
            </tr>
        </thead>
        <tbody id="leadContactTBody">
            @foreach ($data['contacts'] as $contact)
            <tr id="tr_contact_{{ $contact['id'] }}">
                @if ($contact['contact_tag_id'] == 0)
                    <td>{{ ucwords(strtolower(getUserTypeNameForLeadTag($contact['type']))) }}</td>
                @else
                    <td>{{ $contact['tag_name'] }}</td>
                @endif
                <td>{{ $contact['first_name'] }} {{ $contact['last_name'] }}</td>
                <td>{{ $contact['phone_number'] }} </td>
                <td>{{ $contact['alernate_phone_number'] }}</td>
                <td>{{ $contact['email'] }}</td>
                @if ($contact['contact_tag_id'] == 0 || $contact['type'] == 0)
                    <td></td>
                @else
                    <td>
                        <a class="btn btn-outline-secondary btn-sm edit" title="Edit" onclick="editLeadContact({{ $contact['id'] }})">
                            <i class="fas fa-pencil-alt"></i>
                        </a>
                    </td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
