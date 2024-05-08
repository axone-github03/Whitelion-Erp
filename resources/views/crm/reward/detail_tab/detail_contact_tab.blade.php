<div class="card-header bg-transparent border-bottom">
    <b>Contact Person</b>
    <div class="lds-spinner" id="contact_loader" style="display: none;">
        <div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div>
      </div>
    {{-- @if(isCreUser() == 0) 
        <button onclick="addLeadContactModal({{ $data['lead_id'] }})"
            class="btn btn-sm btn-light btn-header-right waves-effect waves-light float-end mr-2"
            type="button" style="margin-left:3px;"><i class="bx bx-plus font-size-16 align-middle "></i>
        </button>
    @endif --}}

    <button onclick="viewAllLeadContact({{ $data['lead_id'] }})"
        class="btn btn-sm btn-light btn-header-right waves-effect waves-light float-end "
        type="button">See
        All </button>

</div>
<div class="card-body">
    <table class="table table-striped table-sm mb-0">

        <thead>
            <tr>
                <th>Tag</th>
                <th>Name</th>
                <th>Firm name</th>
                <th>Mobile Number</th>
                {{-- <th>Alernate Number</th> --}}
                {{-- <th>Email </th> --}}
                <th></th>
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
                    <td><span class="text-primary">{{ $contact['firm_name'] }}</span></td>
                    <td>{{ $contact['phone_number'] }} </td>
                    {{-- <td>{{ $contact['alernate_phone_number'] }}</td> --}}
                    {{-- <td>{{ $contact['email'] }}</td> --}}

                    @if ($contact['contact_tag_id'] == 0 || $contact['type'] == 0)
                        @if(isset(explode('-', $contact['type_detail'])[2]))
                            @if(in_array(explode('-', $contact['type_detail'])[1], ['201', '202', '301', '302', '101', '102', '103', '104', '105']))
                            {{-- @if(Auth::user()->type != 9) --}}
                            <td class="text-center">
                                <a href="javascript:void(0)" class="btn btn-primary btn-sm edit" title="Edit" onclick="LeadViewLog({{ explode('-', $contact['type_detail'])[2] }}, {{ explode('-', $contact['type_detail'])[1] }})">
                                    View Log
                                </a>
                            </td>
                            {{-- @endif --}}
                            @else
                                <td></td>
                            @endif
                        @else
                            <td></td>
                        @endif
                    @else
                    {{-- @if(Auth::user()->type != 9) --}}
                        <td>
                            <a class="btn btn-outline-secondary btn-sm edit" title="Edit" onclick="editLeadContact({{ $contact['id'] }})">
                                <i class="fas fa-pencil-alt"></i>
                            </a>
                        </td>
                        {{-- @endif --}}
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</div>