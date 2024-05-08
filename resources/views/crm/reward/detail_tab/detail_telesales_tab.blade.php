<div class="card-header bg-transparent border-bottom">
    <b class="me-2">TeleSales Approved</b>{!! $data['telesales_status'] !!}
    <div class="lds-spinner" id="contact_loader" style="display: none;">
        <div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div>
    </div>
</div>

<div class="card-body">
    <table class="table table-striped table-sm mb-0">
        <thead>
            <tr>
                <th>Type</th>
                <th>Title</th>
                <th>Status</th>
                <th>User</th>
                <th>Architect</th>
                <th>Electrician</th>
            </tr>
        </thead>
        <tbody id="leadContactTBody">
            @foreach ($data['telesales_task'] as $value)
                <tr style="vertical-align: middle;">
                    <td class="col-1" style="font-weight: 600;"><span class="badge badge-pill badge-soft-primary font-size-11">Task</span></td>
                    <td class="col-3">{{$value['task']}}</td>
                    @if($value['is_closed'] == 0)
                        <td class="col-1 text-success" style="font-weight: 600;">Open</td>
                    @else 
                        <td class="col-1 text-danger" style="font-weight: 600;">Close</td>
                    @endif
                    <td class="col-2"><i class="bx bxs-user me-1"></i>{{$value['first_name'] .' '. $value['last_name']}}<br>{{$value['created_at']}}</td>
                    <td class="col-2">{{$value['architect_name']}}</td>
                    <td class="col-2">{{$value['electrician_name']}}</td>
                    <td class="col-1" style="font-size: x-large;"><i class="bx bxs-show" onclick="TaskDetail({{ $value['id'] }})"></i></td>
                </tr>
            @endforeach
            @if($data['telesales_call'] != [] && $data['telesales_call'] != null && $data['telesales_call'] != '')
                @foreach ($data['telesales_call'] as $call_value)
                    <tr style="vertical-align: middle;">
                        <td class="col-1" style="font-weight: 600;"><span class="badge badge-pill badge-soft-info font-size-11">Call</span></td>
                        <td class="col-3">{{$call_value['purpose']}}</td>
                        @if($call_value['is_closed'] == 0)
                            <td class="col-1 text-success" style="font-weight: 600;">Open</td>
                        @else 
                            <td class="col-1 text-danger" style="font-weight: 600;">Close</td>
                        @endif
                        <td class="col-2"><i class="bx bxs-user me-1"></i>{{$call_value['first_name'] .' '. $call_value['last_name']}}<br>{{$call_value['created_at']}}</td>
                        <td class="col-2">{{$call_value['architect_name']}}</td>
                        <td class="col-2">{{$call_value['electrician_name']}}</td>
                        <td class="col-1" style="font-size: x-large;"><i class="bx bxs-show" onclick="CallDetail({{ $call_value['id'] }})"></i></td>
                    </tr>
                @endforeach
            @endif
            
        </tbody>
    </table>
</div>