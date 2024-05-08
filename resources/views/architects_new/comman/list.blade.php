@foreach ($data['architect'] as $architect)
    <li class="lead_li" id="lead_{{ $architect['id'] }}" onclick="getDataDetail('{{ $architect['id'] }}')">
        <a href="javascript: void(0);">
            <div class="d-flex">
                <div class="flex-grow-1 overflow-hidden">
                    @if($architect['type'] == 201)
                        <h5 class="text-truncate font-size-14 mb-1">#{{ $architect['id'] }} - Non Prime</h5>
                    @elseif($architect['type'] == 202)
                        <h5 class="text-truncate font-size-14 mb-1">#{{ $architect['id'] }} -  Prime</h5>
                    @endif
                    <p class="text-truncate mb-0">{{ $architect['first_name'] }} {{ $architect['last_name'] }}</p>
                </div>
                <div class="d-flex justify-content-end font-size-16">
                    <span class="badge badge-pill badge badge-soft-info font-size-11" style="height: fit-content;" id="{{ $architect['id'] }}_lead_list_status">{{ getArchitectsStatus()[$architect['status']]['header_code'] }}</span>
                    {{-- <i class="bx bxs-phone-call"></i>&nbsp;&nbsp;&nbsp;
                    <i class="bx bx-envelope"></i> --}}
                </div>
            </div>
        </a>
    </li>
@endforeach
