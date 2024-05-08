<div class="card-header bg-transparent border-bottom">
    <b>Quotation</b>
    @if(isCreUser() == 0) 
        <button onclick=""
            class="btn btn-sm btn-light btn-header-right waves-effect waves-light float-end mr-2"
            type="button" style="margin-left:3px;"><i class="bx bx-plus font-size-16 align-middle "></i>
        </button>
        @endif
    <button onclick=""
        class="btn btn-sm btn-light btn-header-right waves-effect waves-light float-end "
        type="button">See
        All </button>

</div>
<style>
    .badge-soft-running {
        color: #5e5e5e;
        background-color: #ffeb007a;
    }

    .badge-soft-change-request {
        color: #ff0000;
        background-color: #ff00001f;
    }

    .badge-soft-confirm {
        color: #ffffff;
        background-color: #418107;
    }

    .badge-soft-sent-quotation {
        color: #ffffff;
        background-color: #ff7c7c;
    }

    .tooltip1 {
        position: relative;
        display: inline-block;
    }

    .tooltip1 .tooltiptext {
        visibility: hidden;
        width: 120px;
        background-color: black;
        color: #fff;
        text-align: center;
        border-radius: 6px;
        padding: 5px 0;
        position: absolute;
        z-index: 1;
        top: 150%;
        left: 50%;
        margin-left: -60px;
    }

    .tooltip1 .tooltiptext::after {
        content: "";
        position: absolute;
        bottom: 100%;
        left: 50%;
        margin-left: -5px;
        border-width: 5px;
        border-style: solid;
        border-color: transparent transparent black transparent;
    }

    .tooltip1:hover .tooltiptext {
        visibility: visible;
    }

</style>
<div class="card-body mb-2 p-3">
    <table class="table table-sm table-striped  mb-0">

        <thead>
            <tr>
                <th>No.</th>
                <th>Quote Ver.</th>
                <th>Quote Date</th>
                <th>Closing Date</th>
                <th>Whitelion Amount</th>
                <th>Billing Amount</th>
                <th>Other Amount</th>
                <th>Total Amount</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="leadQuotationTBody">
            @foreach ($data['quotation'] as $key => $item)
                <tr id="">
                    <td class="bg-white align-middle">
                        @if ($data['lead']['is_deal'] == 1)
                            @if ($item['isfinal'] >= 0)
                                @if (isArchitect() == 1)
                                @elseif (isElectrician() == 1)
                                @elseif (isMarketingUser() == 1)
                                @elseif(isCreUser() == 0)
                                    <input type="radio" onchange="changeFinalQuotation({{ $item['quot_id'] }},{{ $item['quot_groupid'] }});" name="final_quot" id="selected_final_quot_id" @if ($item['isfinal'] == 1) checked @endif > 
                                @else
                                    <input type="radio" onchange="changeFinalQuotation({{ $item['quot_id'] }},{{ $item['quot_groupid'] }});" name="final_quot" id="selected_final_quot_id" @if ($item['isfinal'] == 1) checked @endif > 
                                @endif
                            @endif
                        @endif
                        {{ $key + 1 }}
                    </td>
                    <td class="bg-white align-middle">{{ $item['quot_version'] }}</td>
                    <td class="bg-white align-middle">
                        @if ($item['quot_date'] != '' || $item['quot_date'] != null)
                            <div class="tooltip1">{{ date('d/m/Y', strtotime($item['quot_date'])) }}
                                <span class="tooltiptext">{{ date('H:i:s', strtotime($item['created_at'])) }}</span>
                          </div>
                            
                        @endif
                    </td>
                    <td class="bg-white">
                        <div class="row mb-1">
                            <label for="horizontal-firstname-input" class="col-12 col-form-label">
                                @if (isArchitect() == 1)
                                @elseif (isElectrician() == 1)
                                @elseif (isMarketingUser() == 1)
                                @else
                                @if ($data['closing_date_count'] >= 2)
                                    <span class="closing-badge"
                                        style="float:left;margin-right: 5px;">{{ $data['closing_date_count'] }}</span>

                                    <div class="div_tip" style="display: none; left: 3px; top: 55px;">
                                        <div class="tip_arrow"
                                            style="transparent transparent rgb(191 194 252); margin: -20px 0px 0px; top: 0px; left: 3px;">
                                        </div>
                                        <div class="p-1">
                                            @foreach ($data['closing_date'] as $clsDate)
                                                <div class="tip_name">
                                                    <span class="name"><a class="text-dark"
                                                            href="javascript:void(0)">{{ date('d/m/Y', strtotime($clsDate['closing_date'])) }}</a></span>
                                                </div>
                                            @endforeach

                                        </div>
                                    </div>
                                @endif
                                @endif
                                {{ $data['lead']['closing_date_time'] }}
                            </label>
                        </div>
                    </td>
                    <td class="bg-white align-middle">INR {{ $item['whitelion_amount'] }}/-</td>
                    <td class="bg-white align-middle">INR {{ $item['billing_amount'] }}/-</td>
                    <td class="bg-white align-middle">INR {{ $item['other_amount'] }}/-</td>
                    <td class="bg-white align-middle">INR {{ $item['total_amount'] }}/-</td>
                    <td class="bg-white align-middle">{!! getQuotationMasterStatusLable($item['status']) !!}</td>
                    <td class="bg-white align-middle">
                        @if ($item['quot_id'] == 0)
                        @else
                            @if (isArchitect() == 1)
                            @elseif (isElectrician() == 1)
                            @elseif (isMarketingUser() == 1)
                            @else
                                @if ($item['quottype_id'] == 4)
                                    @if ($item['quotation_file'] != '' && $item['quotation_file'] != null)
                                        @foreach (explode(",", $item['quotation_file']) as $quotKey => $quotItem)
                                            <a class="ms-1" target="_blank" href="{{ getSpaceFilePath($quotItem) }}"><i class="bx bxs-file-pdf"></i></a>
                                        @endforeach
                                    @endif
                                @else
                                    <a class="btn-sm edit" title="Pdf"
                                        onclick="ItemWisePrint({{ $item['quot_id'] }}, {{ $item['quot_groupid'] }})"
                                        style="color: #74788d;"><i class="bx bxs-file-pdf"></i>
                                    </a>
                                    @if(isCreUser() == 0) 
                                        <a class="btn-sm edit" title="Edit"
                                            href="{{ route('quot.itemquotedetail') }}?quotno={{ $item['quot_id'] }}"
                                            target="_blank" style="color: #74788d;"><i
                                            class="fas fa-pencil-alt"></i>
                                        </a>
                                    @endif
                                @endif
                            @endif
                        @endif

                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>