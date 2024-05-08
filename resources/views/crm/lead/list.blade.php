@foreach ($data['lead']['data'] as $lead)
    <li class="lead_li" id="lead_{{ $lead['id'] }}" onclick="getDataDetail('{{ $lead['id'] }}')">
        <a href="javascript: void(0);">
            <div class="d-flex">
                <div class="flex-grow-1 overflow-hidden">
                    @php
                        if ($lead['inquiry_id'] != 0) {
                            $inquiry_id = ' - ' . $lead['inquiry_id'];
                        } else {
                            $inquiry_id = '';
                        }
                        
                        if ($lead['is_deal'] == 0) {
                            $prifix = 'L';
                        } elseif ($lead['is_deal'] == 1) {
                            $prifix = 'D';
                        }
                    @endphp

                    <h5 class="text-truncate font-size-14 mb-1">
                        #{{ $prifix }}{{ $lead['id'] }}{{ $inquiry_id }}</h5>
                    <p class="text-truncate mb-0">{{ $lead['first_name'] }} {{ $lead['last_name'] }}</p>
                </div>
                <div class="d-flex justify-content-end font-size-16">
                    <span class="badge badge-pill badge badge-soft-info font-size-11" style="height: fit-content;" id="{{ $lead['id'] }}_lead_list_status">{{ getLeadStatus()[$lead['status']]['name'] }}</span>
                    {{-- <span class="badge badge-pill badge badge-soft-info font-size-11" style="height: fit-content;">{{ $lead['lead_owner_name'] }}</span> --}}
                </div>
            </div>
        </a>
    </li>
@endforeach
