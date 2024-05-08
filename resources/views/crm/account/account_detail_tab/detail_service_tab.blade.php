<div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
    <b>Service Details <div class="lds-spinner" id="detail_loader" style="display: none;">
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
        </div></b>
</div>
<div class="card-body border-bottom" id="lead_detail">
    <table class="table table-sm table-striped  mb-0">

        <thead>
            <tr>
                <th>Service No</th>
                <th>Contact Person</th>
                <th>Status</th>
                <th>Sub Status</th>
                <th>Scheduled Date</th>
                <th>Assigned to</th>
            </tr>
        </thead>
        <tbody id="dealTBody">
            @foreach($data['deals'] as $deal)
                    <tr id="tr_deal_{{$deal['id']}}">
                        <td><a target="_blank" href="{{$deal['url']}}"> {{$deal['first_name']}} {{$deal['last_name']}}</a></td>
                        <td>{{$deal['quotation']}}</td>
                        <td>{{$deal['status']}}</td>
                        <td>{{$deal['sub_status']}}</td>
                        <td>{{$deal['closing_date_time']}} </td>
                        <td>{{$deal['site_stage']}}</td>
                    </tr>
                    @endforeach

        </tbody>
    </table>
</div>