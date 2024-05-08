<script type="text/javascript">
    var ajaxURLSalesUserMasterSearchAjax = "{{ route('master.search.sales.user.ajax') }}";
    var ajaxURLArcMasterSearchAjax = "{{ route('master.search.arc.ajax') }}";
    var ajaxURLEleMasterSearchAjax = "{{ route('master.search.ele.ajax') }}";
    var ajaxURLLeadMasterSearchAjax = "{{ route('master.search.lead.ajax') }}";
    var ajaxURLDealMasterSearchAjax = "{{ route('master.search.deal.ajax') }}";
    var csrfToken = $("[name=_token").val();
    var SalesUserTable = null;
    var ArchitectTable = null;
    var ElectricianTable = null;
    var LeadTable = null;
    var DealTable = null;
    var activeTabs = [];
    $("#master_search_input").keyup(function(event) {
        if (event.keyCode === 13) {
            $("#mst_search_button").click();
        }
    });

    $(document).ready(function() {
        $("#master_search_button").click(function() {
            $("#modalMasterSearch").modal('show');
            $('#master_search_input').val("");
            $('#li_sales').hide();
            $('#li_architect').hide();
            $('#li_electrician').hide();
            $('#li_lead').hide();
            $('#li_deal').hide();
            $('#li_quotation').hide();
            loadSalesUserTable();
            loadArchitectTable();
            loadElectricianTable();
            loadLeadTable();
            loadDealTable();
        });

        $('.navigationbtn').on('click', function() {
            const tabId = $(this).attr('href');
            const index = activeTabs.indexOf(tabId);

            if (index === -1) {
                activeTabs.push(tabId);
            } else {
                activeTabs.splice(index, 1);
            }
            updateActiveTabs();
        });

        $('#mst_search_button').on('click', function() {
            if (SalesUserTable != null) {
                SalesUserTable.ajax.reload(null, false);
            }
            if (ArchitectTable != null) {
                ArchitectTable.ajax.reload(null, false);
            }
            if (ElectricianTable != null) {
                ElectricianTable.ajax.reload(null, false);
            }
            if (LeadTable != null) {
                LeadTable.ajax.reload(null, false);
            }
            if (DealTable != null) {
                DealTable.ajax.reload(null, false);
            }
        });

        $('#mst_search_clear').on('click', function() {
            $('#master_search_input').val("");
            $('#li_sales').hide();
            $('#li_architect').hide();
            $('#li_electrician').hide();
            $('#li_lead').hide();
            $('#li_deal').hide();
            $('#li_quotation').hide();
            if (SalesUserTable != null) {
                SalesUserTable.ajax.reload(null, false);
            }
            if (ArchitectTable != null) {
                ArchitectTable.ajax.reload(null, false);
            }
            if (ElectricianTable != null) {
                ElectricianTable.ajax.reload(null, false);
            }
            if (LeadTable != null) {
                LeadTable.ajax.reload(null, false);
            }
            if (DealTable != null) {
                DealTable.ajax.reload(null, false);
            }
        });
    });

    function updateActiveTabs() {
        $('.navigationbtn').removeClass('active');
        activeTabs.forEach(tabId => {
            const tabLink = $(`.navigationbtn[href="${tabId}"]`);
            tabLink.addClass('active');
            $(`[id="${tabId.replace('#', '')}"]`).addClass('active');
            $(`[id="${tabId.replace('#', '')}_table"]`).DataTable().draw();
        });
        $('.navigationbtn').each(function() {
            const tabId = $(this).attr('href');
            if (!activeTabs.includes(tabId)) {
                $(`[id="${tabId.replace('#', '')}"]`).removeClass('active');
                $(`[id="${tabId.replace('#', '')}_table"]`).DataTable().clear().draw();
            }
        });
        $('.nav-item').toggleClass('active-tab', activeTabs.length > 0);
    }


    function loadSalesUserTable() {
        $('#sales_user_table').DataTable().destroy();
        SalesUserTable = $('#sales_user_table').DataTable({
            "aoColumnDefs": [{
                "bSortable": false,
                "aTargets": [0]
            }],
            "order": [
                [0, 'desc']
            ],
            "processing": true,
            "pagingType": "full_numbers",
            "serverSide": true,
            "pageLength": 10,
            "bFilter": false,
            "bInfo": false,
            "bLengthChange": false,
            "ajax": {
                "url": ajaxURLSalesUserMasterSearchAjax,
                "type": "POST",
                "data": {
                    "_token": csrfToken,
                    "search_value": function() {
                        return $('#master_search_input').val();
                    }
                }
            },
            "aoColumns": [{
                    "mData": "id"
                },
                {
                    "mData": "name"
                },
                {
                    "mData": "email"
                },
                {
                    "mData": "mobile"
                },
                {
                    "mData": "address"
                }
            ]
        });
        SalesUserTable.on('xhr', function() {
            var responseData = SalesUserTable.ajax.json();
            if (responseData['count'] != 0) {
                $('#li_sales').show();
                $('#sales_count').text(responseData['count']);
            } else {
                $('#li_sales').hide();
            }
        });

    }

    function loadArchitectTable() {
        $('#architect_table').DataTable().destroy();
        ArchitectTable = $('#architect_table').DataTable({
            "aoColumnDefs": [{
                "bSortable": false,
                "aTargets": [0]
            }],
            "order": [
                [0, 'desc']
            ],
            "processing": true,
            "pagingType": "full_numbers",
            "serverSide": true,
            "pageLength": 10,
            "bFilter": false,
            "bInfo": false,
            "bLengthChange": false,
            "ajax": {
                "url": ajaxURLArcMasterSearchAjax,
                "type": "POST",
                "data": {
                    "_token": csrfToken,
                    "search_value": function() {
                        return $('#master_search_input').val();
                    }
                }
            },
            "aoColumns": [{
                    "mData": "id"
                },
                {
                    "mData": "name"
                },
                {
                    "mData": "mobile"
                },
                {
                    "mData": "address"
                },
                {
                    "mData": "firm_name"
                },
                {
                    "mData": "city_id"
                },
                {
                    "mData": "total_point"
                },
                {
                    "mData": "total_point_current"
                },
                {
                    "mData": "instagram_link"
                }
            ]
        });
        ArchitectTable.on('xhr', function() {
            var responseData = ArchitectTable.ajax.json();
            if (responseData['count'] != 0) {
                $('#li_architect').show();
                $('#architect_count').text(responseData['count']);
            } else {
                $('#li_architect').hide();
            }
        });
    }

    function loadElectricianTable() {
        $('#electrician_table').DataTable().destroy();
        ElectricianTable = $('#electrician_table').DataTable({
            "aoColumnDefs": [{
                "bSortable": false,
                "aTargets": [0]
            }],
            "order": [
                [0, 'desc']
            ],
            "processing": true,
            "pagingType": "full_numbers",
            "serverSide": true,
            "pageLength": 10,
            "bFilter": false,
            "bInfo": false,
            "bLengthChange": false,
            "ajax": {
                "url": ajaxURLEleMasterSearchAjax,
                "type": "POST",
                "data": {
                    "_token": csrfToken,
                    "search_value": function() {
                        return $('#master_search_input').val();
                    }
                }
            },
            "aoColumns": [{
                    "mData": "id"
                },
                {
                    "mData": "name"
                },
                {
                    "mData": "mobile"
                },
                {
                    "mData": "address"
                },
                {
                    "mData": "city_id"
                },
                {
                    "mData": "total_point"
                },
                {
                    "mData": "total_point_current"
                }
            ]
        });
        ElectricianTable.on('xhr', function() {
            var responseData = ElectricianTable.ajax.json();
            if (responseData['count'] != 0) {
                $('#li_electrician').show();
                $('#electrician_count').text(responseData['count']);
            } else {
                $('#li_electrician').hide();
            }
        });
    }

    function loadLeadTable() {
        $('#lead_table').DataTable().destroy();

        LeadTable = $('#lead_table').DataTable({
            "aoColumnDefs": [{
                "bSortable": false,
                "aTargets": [0]
            }],
            "order": [
                [0, 'desc']
            ],
            "processing": true,
            "pagingType": "full_numbers",
            "serverSide": true,
            "pageLength": 10,
            "bFilter": false,
            "bInfo": false,
            "bLengthChange": false,
            "ajax": {
                "url": ajaxURLLeadMasterSearchAjax,
                "type": "POST",
                "data": {
                    "_token": csrfToken,
                    "search_value": function() {
                        return $('#master_search_input').val();
                    }
                }
            },
            "aoColumns": [{
                    "mData": "id"
                },
                {
                    "mData": "name"
                },
                {
                    "mData": "status"
                },
                {
                    "mData": "city_id"
                },
                {
                    "mData": "mobile"
                },
                {
                    "mData": "address"
                },
                {
                    "mData": "site_stage"
                },
                {
                    "mData": "source_type"
                },
                {
                    "mData": "source"
                }
            ]
        });
        LeadTable.on('xhr', function() {
            var responseData = LeadTable.ajax.json();
            if (responseData['count'] != 0) {
                $('#li_lead').show();
                $('#lead_count').text(responseData['count']);
            } else {
                $('#li_lead').hide();
            }
        });
    }

    function loadDealTable() {
        $('#deal_table').DataTable().destroy();
        DealTable = $('#deal_table').DataTable({
            "aoColumnDefs": [{
                "bSortable": false,
                "aTargets": [0]
            }],
            "order": [
                [0, 'desc']
            ],
            "processing": true,
            "pagingType": "full_numbers",
            "serverSide": true,
            "pageLength": 10,
            "bFilter": false,
            "bInfo": false,
            "bLengthChange": false,
            "ajax": {
                "url": ajaxURLDealMasterSearchAjax,
                "type": "POST",
                "data": {
                    "_token": csrfToken,
                    "search_value": function() {
                        return $('#master_search_input').val();
                    }
                }
            },
            "aoColumns": [{
                    "mData": "id"
                },
                {
                    "mData": "name"
                },
                {
                    "mData": "status"
                },
                {
                    "mData": "city_id"
                },
                {
                    "mData": "mobile"
                },
                {
                    "mData": "address"
                },
                {
                    "mData": "site_stage"
                },
                {
                    "mData": "source_type"
                },
                {
                    "mData": "source"
                }
            ]
        });
        DealTable.on('xhr', function() {
            var responseData = DealTable.ajax.json();
            if (responseData['count'] != 0) {
                $('#li_deal').show();
                $('#deal_count').text(responseData['count']);
            } else {
                $('#li_deal').hide();
            }
        });
    }
</script>
