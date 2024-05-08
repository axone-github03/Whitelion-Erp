<div class="d-flex justify-content-between align-items-center card-header bg-transparent border-bottom">
    <div>
        <b>Files</b>
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
        <div>
            <button onclick="viewAllLeadFiles({{ $data['lead_id'] }})" class="btn btn-sm btn-light waves-effect waves-light float-end" type="button">See All</button>
        </div>
        @if(isMarketingUser() == 0 )
        <div>
            <button onclick="addLeadFileModal({{ $data['lead_id'] }})" class="btn btn-sm btn-light waves-effect waves-light float-end mr-2" type="button" style="margin-left:3px;"><i class="bx bx-plus font-size-16 align-middle "></i></button>
        </div>
        @endif
    </div>

</div>
<div class="card-body mb-2">
    <table class="table table-sm table-striped  mb-0">

        <thead>
            <tr>
                <th>Id  </th>
                <th>File Name</th>
                <th>File Tag</th>
                <th>Uploaded By</th>
                <th>Date Attached</th>
                <th>Size</th>
                <th>Point</th>
                <th></th>
            </tr>
        </thead>
        <tbody id="leadFileTBody">

            @foreach ($data['files'] as $files)
                <tr id="tr_file_{{ $files['id'] }}">
                    <td>#{{ $files['id'] }}  </td>
                    <td>{!! $files['download'] !!}</td>
                    <td>{{ $files['tag_name'] }} </td>
                    <td>{{ $files['first_name'] }} {{ $files['last_name'] }}</td>
                    <td>{{ $files['created_at'] }}</td>
                    <td>{{ $files['file_size'] == '' ? '' : formatbBytes($files['file_size']) }}</td>
                    <td>{{ $files['point'] }}</td>
                    <td>
                        @if(Auth::user()->type == 0)
                            @if($files['is_active'] == 1)
                                <label class="switch m-0"><input type="checkbox" onchange="FileStatusChange({{$files['id']}}, 1)" checked="">
                                    <span class="slider round"></span>
                                </label>
                            @else
                                <label class="switch m-0"><input type="checkbox" onchange="FileStatusChange({{$files['id']}}, 0)" >
                                    <span class="slider round"></span>
                                </label>
                            @endif
                        @endif                        
                    </td>
                </tr>
            @endforeach

        </tbody>
    </table>
</div>