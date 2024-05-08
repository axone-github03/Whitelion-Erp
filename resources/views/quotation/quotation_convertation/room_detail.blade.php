@foreach ($data as $key => $roomName)
    <a href="javascript:void(0)" onclick="boardDetail('{{ $roomName['room_no'] }}', {{$key + 1}})" id="room_{{$key + 1}}" class="btn btn-sm waves-effect waves-light filter-btn room_button" data-board="{{ $roomName['room_no'] }}"> {{ $roomName['room_name'] }}</a>
@endforeach