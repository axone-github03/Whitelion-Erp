<div class="card-header bg-transparent border-bottom align-items-center row">
    <div class="col-3">
        <b>Deal Details</b>
    </div>
    <div class="userscomman row col-9 text-start align-items-center justify-content-end" id="funnel_status_bar">
        <a href="javascript:void(0)" onclick="exportData('{{route('won.deal.bill.pending.channelpartner.export')}}')" class="btn btn-sm btn-primary me-4" style="width: fit-content;" type="button"><i class="bx bx-export font-size-16 align-middle me-2"></i>Bill Pending Export</a>
        <a href="javascript:void(0)" class="funnel deal_status_filter_remove deal_status_filter_1" data-id=""
            id="arc_funnel" onclick="ReloadDealList(1)">Running (<span class="deal_running_count">0</span>)</a>
        <a href="javascript:void(0)" class="funnel deal_status_filter_remove deal_status_filter_2" data-id=""
            id="arc_funnel" onclick="ReloadDealList(2)">Won (<span class="deal_won_count">0</span>)</a>
        <a href="javascript:void(0)" class="funnel deal_status_filter_remove deal_status_filter_3" data-id=""
            id="arc_funnel" onclick="ReloadDealList(3)">Lost (<span class="deal_lost_count">0</span>)</a>
        <a href="javascript:void(0)" class="funnel deal_status_filter_remove deal_status_filter_4" data-id=""
            id="arc_funnel" onclick="ReloadDealList(4)">Cold (<span class="deal_cold_count">0</span>)</a>
        <a href="javascript:void(0)" class="funnel deal_status_filter_remove deal_status_filter_0" data-id=""
            id="arc_funnel" onclick="ReloadDealList(0)">Total (<span class="deal_total_count">0</span>)</a>
    </div>
    <button onclick="" class="btn btn-sm btn-light btn-header-right waves-effect waves-light float-end mr-2 d-none"
        type="button" style="margin-left:3px;"><i class="bx bx-plus font-size-16 align-middle "></i>
    </button>
    <button onclick="" class="btn btn-sm btn-light btn-header-right waves-effect waves-light float-end d-none"
        type="button">See
        All </button>

</div>
<div class="card-body mb-2 p-3">
    <input type="hidden" name="hidden_deal_status_id" id="hidden_deal_status_id">
    <table class="table table-sm table-striped mb-0 dt-responsive" id="user_deal_datatable">
        <thead>
            <tr>
                <th>Client Name</th>
                <th>Phone No.</th>
                <th>Address</th>
                <th>Closing Date</th>
                <th>Architect</th>
                <th>Electrician</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody id="leadDealTBody">


        </tbody>
    </table>
</div>

<script>
    var ajaxURLUserDealDataList = "{{ route('user.view.channel.partner.deal.ajax') }}";

    var user_deal_datatable = $('#user_deal_datatable').DataTable({
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
            "url": ajaxURLUserDealDataList,
            "type": "POST",
            "data": {
                "_token": csrfToken,
                'id': function() {
                    return $('#user_main_detail_id').val();
                },
                "status": function() {
                    return $('#hidden_deal_status_id').val();
                },
                "is_arc": function(){
                    return $('#hidden_is_arc').val();
                }
            }
        },
        "aoColumns": [{
                "mData": "name"
            },
            {
                "mData": "phone_number"
            },
            {
                "mData": "address"
            },
            {
                "mData": "closing_date"
            },
            {
                "mData": "main_architect_name"
            },
            {
                "mData": "main_electrician_name"
            }, 
            {
                "mData": "status"
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


    user_deal_datatable.on('xhr', function() {
        var responseData = user_deal_datatable.ajax.json();
        if(responseData["Running_deal"] != null){
            $('.deal_running_count').text(responseData["Running_deal"])
        } else {
            $('.deal_running_count').text(0)
        } 

        if(responseData["Won_deal"]) {
            $('.deal_won_count').text(responseData["Won_deal"])
        } else {
            $('.deal_won_count').text(0)
        } 

        if(responseData["Lost_deal"]){
            $('.deal_lost_count').text(responseData["Lost_deal"])
        } else {
            $('.deal_lost_count').text(0)
        } 

        if(responseData["Cold_deal"]) {
            $('.deal_cold_count').text(responseData["Cold_deal"])
        } else {
            $('.deal_cold_count').text(0)
        }

        $('.deal_total_count').text(responseData["Total_deal"])
        $('.total_deal_count').text(responseData["Total_deal"])

        
        LeadAndDealCount($('.total_lead_count').text(), responseData["Total_deal"]);
       
    });

    // $('#user_deal_datatable_length').each(function() {
    //     $(this).before('<div class="col-6 card-header bg-transparent px-2"><b>Deal Details</b></div>');
    // });

    $(document).ready(function() {
        // $('#user_deal_datatable_filter').parent().addClass('d-none');
        // $('#user_deal_datatable_length').parent().addClass('d-none');
    });

    function exportData(route) {
        var user_id = $('#user_main_detail_id').val();

        // Construct the URL with input values
        var url = route + '?user_id=' + user_id;

        // Open the URL in a new tab
        window.open(url, '_blank');
    }
    function ReloadDealList(status) {
        $('#hidden_deal_status_id').val(status);
        $('.deal_status_filter_remove').removeClass('active')
        $('.deal_status_filter_' + status).addClass('active');
        user_deal_datatable.ajax.reload(null, false);
    }
</script>
