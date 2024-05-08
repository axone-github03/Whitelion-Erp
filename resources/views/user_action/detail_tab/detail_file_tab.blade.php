<div class="card-header bg-transparent border-bottom">
    <b>Files</b><i id="contact_loader" class="bx bx-loader bx-spin font-size-16 align-middle me-2" style="display: none;"></i>
    @if(isCreUser() == 0) 
        <button onclick="addLeadFileModal({{ $data['user']['id'] }})" class="btn btn-sm btn-light btn-header-right waves-effect waves-light float-end mr-2" type="button" style="margin-left:3px;"><i class="bx bx-plus font-size-16 align-middle "></i> </button>
    @endif
    <button onclick="viewAllLeadFiles({{ $data['user']['id'] }})" class="btn btn-sm btn-light btn-header-right waves-effect waves-light float-end " type="button">See All </button>
</div>
<div class="card-body mb-2">
    <table class="table table-sm table-striped  mb-0">
        <thead>
            <tr>
                <th>File Name</th>
                <th>File Tag</th>
                <th>Uploaded by</th>
                <th>Date attached</th>
                <th>Size</th>
            </tr>
        </thead>
        <tbody id="leadFileTBody">

            @foreach ($data['files'] as $files)
                <tr id="tr_file_{{ $files['id'] }}">
                    <td><a target="_blank" href="{{ $files['download'] }}">{{ $files['name'] }}</a>
                    </td>
                    <td>{{ $files['tag_name'] }} </td>
                    <td>{{ $files['first_name'] }} {{ $files['last_name'] }}</td>
                    <td>{{ date('d/m/Y h:i A', strtotime($files['created_at'])) }}</td>
                    <td>{{ formatbBytes($files['file_size']) }}</td>
                    <td>
                        <a class="btn btn-outline-secondary btn-sm edit" title="Delete" onclick="deleteLeadFile({{$files['id']}})">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
            @endforeach

        </tbody>
    </table>
</div>
