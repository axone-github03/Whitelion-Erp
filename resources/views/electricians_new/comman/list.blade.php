@foreach ($data['electrician'] as $electrician)
    <li class="lead_li" id="lead_{{ $electrician['id'] }}" onclick="getDataDetail('{{ $electrician['id'] }}')">
        <a href="javascript: void(0);">
            <div class="d-flex">
                <div class="flex-grow-1 overflow-hidden">
                    @if($electrician['type'] == 301)
                        <h5 class="text-truncate font-size-14 mb-1">#{{ $electrician['id'] }} - Non Prime</h5>
                    @elseif($electrician['type'] == 302)
                        <h5 class="text-truncate font-size-14 mb-1">#{{ $electrician['id'] }} -  Prime</h5>
                    @endif
                    <p class="text-truncate mb-0">{{ $electrician['first_name'] }} {{ $electrician['last_name'] }}</p>
                </div>
                <div class="d-flex justify-content-end font-size-16">
                    <span class="badge badge-pill badge badge-soft-info font-size-11" style="height: fit-content;" id="{{ $electrician['id'] }}_lead_list_status">{{ getArchitectsStatus()[$electrician['status']]['header_code'] }}</span>
                    {{-- <i class="bx bxs-phone-call"></i>&nbsp;&nbsp;&nbsp;
                    <i class="bx bx-envelope"></i> --}}
                </div>
            </div>
        </a>
    </li>
@endforeach
