<style>
    ul.pager>li {
        list-style: none
    }
</style>
<ul class="pager p-0 d-flex justify-content-between text-center">

    @foreach ($data['lead']['links'] as $page => $url)
        @if ($url['label'] == $data['lead']['current_page'])
        <li class="paginate_button page-item active p-0 col">
            <a href="javascript:void(0)" aria-controls="datatable" data-dt-idx="1" tabindex="0" class="page-link">{!! str_replace('Next', ' ', str_replace('Previous', ' ', $url['label'])) !!}</a>
        </li>
        @else
            @if($url['url'] == null)
                <li class="paginate_button page-item p-0 col">
                    <a href="javascript:void(0)" aria-controls="datatable" data-dt-idx="1" tabindex="0" class="page-link">{!! str_replace('Next', ' ', str_replace('Previous', ' ', $url['label'])) !!}</a>
                </li>
            @else
                <li class="paginate_button page-item p-0 col">
                    <a href="{{ $url['url'] }}" aria-controls="datatable" data-dt-idx="1" tabindex="0" class="page-link">{!! str_replace('Next', ' ', str_replace('Previous', ' ', $url['label'])) !!}</a>
                </li>
            @endif
        @endif
    @endforeach
</ul>