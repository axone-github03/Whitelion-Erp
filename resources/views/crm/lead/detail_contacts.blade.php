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

    @if ($contact['contact_tag_id'] == 0)
    <td></td>
    @else
    <td><a class="btn btn-outline-secondary btn-sm edit" title="Edit"
            onclick="editLeadContact({{ $contact['id'] }})">
            <i class="fas fa-pencil-alt"></i>
        </a></td>
    @endif
</tr>
@endforeach