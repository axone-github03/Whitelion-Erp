<ul class="verti-timeline list-unstyled">
    @foreach ($data['timeline'] as $timeline)
        @if ($timeline['date'] != 0)
            <li class="event-list period">
                <div class="timeline-info"></div>
                <div class="timeline-marker"></div>
                <p class="timeline-title">{{ $timeline['date'] }}</p>
            </li>
        @endif
        <li class="event-list">
            <div class="timeline-info"><span>{{ $timeline['time'] }}</span> </div>
            <div class="timeline-marker"></div>
            <div class="timeline-content">
                <p class="">{{ $timeline['description'] }}</p><span>by {{ $timeline['first_name'] }} {{ $timeline['last_name'] }}</span>
            </div>
        </li>
    @endforeach
</ul>