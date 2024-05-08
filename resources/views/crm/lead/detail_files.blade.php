@foreach($data['files'] as $files)
<tr id="tr_file_{{$files['id']}}">
    <td><a target="_blank" href="{{ $files['download']}}">{{ $files['name']}}</a></td>
    <td>{{ $files['tag_name']}} </td>
    <td>{{ $files['first_name']}} {{ $files['last_name']}}</td>
    <td>{{ $files['created_at']}}</td>
    <td>{{ formatbBytes($files['file_size']) }}</td>
    <td><a class="btn btn-outline-secondary btn-sm edit" title="Delete" onclick="deleteLeadFile({{$files['id']}})">
            <i class="fas fa-trash"></i>
        </a></td>
</tr>
@endforeach