@foreach ($data['updates'] as $update)
    <div class="d-flex align-items-center mb-3">
        <div>
            <i class='bx bxs-user-circle' style="color: #dbdbdb;font-size: 3rem;"></i>
        </div>
        <div class="ms-2">
            <p class="mb-0 d-flex justify-content-between" style="font-weight: 600;">
                {!! $update['message'] !!}<span style="font-size: 12px;color: #5a5a5a94;" class="ms-5"><i
                        class='bx bxs-pencil'></i><i class='bx bx-trash ms-2'></i></span></p>
            <span class="mb-0" style="font-weight: 600;">{{ $update['task'] }} -
            </span><span class="text-primary" style="font-weight: 600;">{{ $update['task_title'] }}</span>
            <span class="mb-0 ms-5">{{ $update['date'] }}, {{ $update['time'] }} By
                {{ $update['first_name'] }} {{ $update['last_name'] }}
           
        </span>
        </div>
    </div>
@endforeach
