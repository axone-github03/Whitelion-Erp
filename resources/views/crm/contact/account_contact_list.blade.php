@foreach( $data['users'] as $user)
<li class="user_li" id="user_{{$user['id']}}" onclick="getDataDetail('{{$user['id']}}')">
    <a href="javascript: void(0);">
        <div class="d-flex">
            <div class="flex-grow-1 overflow-hidden">
                <h5 class="text-truncate font-size-14 mb-1">#{{$user['id']}}</h5>
                <p class="text-truncate mb-0">{{$user['first_name']}} {{$user['last_name']}}</p>
            </div>
            <div class="d-flex justify-content-end font-size-16">
                <i class="bx bxs-phone-call"></i>&nbsp;&nbsp;&nbsp;
                <i class="bx bx-envelope"></i>
            </div>
        </div>
    </a>
</li>
@endforeach