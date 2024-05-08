<div class="card-header bg-transparent border-bottom align-items-center row">
    <div class="col-3">
        <b>Lead Details</b>
    </div>
    <div class="userscomman row col-9 text-start align-items-center justify-content-end" id="funnel_status_bar">
        <a href="javascript:void(0)" class="funnel lead_status_filter_remove lead_status_filter_1" data-id="" id="arc_funnel" onclick="ReloadLeadList(1)">Running (<span class="lead_running_count">0</span>)</a>
        <a href="javascript:void(0)" class="funnel lead_status_filter_remove lead_status_filter_2" data-id="" id="arc_funnel" onclick="ReloadLeadList(2)">Lost (<span class="lead_lost_count">0</span>)</a>
        <a href="javascript:void(0)" class="funnel lead_status_filter_remove lead_status_filter_3" data-id="" id="arc_funnel" onclick="ReloadLeadList(3)">Cold (<span class="lead_cold_count">0</span>)</a>
        <a href="javascript:void(0)" class="funnel lead_status_filter_remove lead_status_filter_4" data-id="" id="arc_funnel" onclick="ReloadLeadList(0)">Total (<span class="lead_total_count">0</span>)</a>
    </div>
    <button onclick="" class="btn btn-sm btn-light btn-header-right waves-effect waves-light float-end mr-2 d-none" type="button" style="margin-left:3px;"><i class="bx bx-plus font-size-16 align-middle "></i> </button>
    <button onclick="" class="btn btn-sm btn-light btn-header-right waves-effect waves-light float-end d-none" type="button">See All</button>
</div>
<div class="card-body p-3">
    <input type="hidden" name="hidden_lead_status_id" id="hidden_lead_status_id" value="0">
    <table class="table table-sm table-striped mb-0 dt-responsive" id="user_lead_datatable">
        <thead>
            <tr>
                <th>Client Name</th>
                <th>Status</th>
                <th>Stage</th>
                @if (isset($data['is_architect']) && $data['is_architect'] == 1)
                    <th>Electrician</th>
                @elseif(isset($data['is_architect']) && $data['is_architect'] == 0)
                    <th>Architect</th>
                @endif
                <th>Channel Partner</th>
            </tr>
        </thead>
        <tbody id="leadLeadTBody">



        </tbody>
    </table>
</div>

<script>
    var ajaxURLUserLeadDataList = "{{ route('user.view.lead.data') }}";
    
    var user_lead_datatable = $('#user_lead_datatable').DataTable({
        "aoColumnDefs": [{
            "bSortable": true,
            "aTargets": [0, 1, 2, 3, 4]
        }],
        "pageLength": 10,
        "order": [
            [0, 'desc']
        ],
        "processing": true,
        "serverSide": true,
        "bInfo": true,
        "ajax": {
            "url": ajaxURLUserLeadDataList,
            "type": "POST",
            "data": {
                "_token": csrfToken,
                'id': function() {
                    return $('#user_main_detail_id').val();
                },
                "status": function() {
                    return $('#hidden_lead_status_id').val();
                },
                "is_arc": function(){
                    return $('#hidden_is_arc').val();
                }
            }
        },
        "aoColumns": [{
                "mData": "name"
            }, {
                "mData": "status"
            },
            {
                "mData": "site_stage"
            },
            {
                "mData": "arc_and_ele"
            }, {
                "mData": "channel_partner"
            },
        ],
        "pagingType": "full_numbers",
        "language": {
            "search": "search",
            "searchPlaceholder": "Search...",
            "sLengthMenu": "Display _MENU_ records",
            "paginate": {
                "previous": "<",
                "next": ">",
                "first": "|<",
                "last": ">|"
            }
        },
    });


    user_lead_datatable.on('xhr', function() {
        var responseData = user_lead_datatable.ajax.json();
        if(responseData["Running_lead"] != null){
            $('.lead_running_count').text(responseData["Running_lead"])
        } 
        else {
            $('.lead_running_count').text(0)
        }

        if(responseData["Lost_lead"] != null){
            $('.lead_lost_count').text(responseData["Lost_lead"])

        } else{
            $('.lead_lost_count').text(0)
        }

        if(responseData["Cold_lead"] != null)
        {
            $('.lead_cold_count').text(responseData["Cold_lead"])
        } else {
            $('.lead_cold_count').text(0);
        }
        $('.lead_total_count').text(responseData["Total_lead"])
        $('.total_lead_count').text(responseData["Total_lead"])

        LeadAndDealCount(responseData["Total_lead"],  $('.total_deal_count').text());
        
    });

    $(document).ready(function() {
        // $('#user_lead_datatable_filter').parent().addClass('d-none');
        // $('#user_lead_datatable_length').parent().addClass('d-none');
    });

    function ReloadLeadList(status) {
        $('#hidden_lead_status_id').val(status);
        $('.lead_status_filter_remove').removeClass('active')
        $('.lead_status_filter_'+status).addClass('active');
        user_lead_datatable.ajax.reload(null, false);
    }
</script>
