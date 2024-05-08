

    var csrfToken = $("[name=_token").val();
    var ordersPageLength = getCookie('ordersPageLength') !== undefined ? getCookie('ordersPageLength') : 10;

    var report_table = $('#datatable').DataTable({
        "aoColumnDefs": [{
            "bSortable": false,
            "aTargets": []
        }],
        "order": [
            [0, 'desc']
        ],
        "processing": true,
        "serverSide": true,
        "pageLength": ordersPageLength,
        "ajax": {
            "url": ajaxURLDashboardOrderReport,
            "type": "POST",
            "data": {
                "_token": csrfToken,
                "data_ids": function() {
                    return ajaxReportIds;
                },
                "data_type": function() {
                    return ajaxReportType;
                },
                "start_date": function() {
                    return $("#start_date").val();
                },
                "end_date": function() {
                    return $("#end_date").val();
                },
                "user_id": function() {
                    return $("#sales_user_id").val();
                },
                "channel_partner_type": function() {
                    return $("#channel_partner_type").val();
                },
                "channel_partner_user_id": function() {
                    return $("#channel_partner_user_id").val();
                }
            }
        },
        "aoColumns": [
            {
                "mData": "col_1"
            },
            {
                "mData": "col_2"
            },
            {
                "mData": "col_3"
            },
            {
                "mData": "col_4"
            },
            {
                "mData": "col_5"
            },
            {
                "mData": "col_7"
            },
            {
                "mData": "col_6"
            }
        ],
        "drawCallback": function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })
        }
    });

    $('#datatable').on('length.dt', function(e, settings, len) {
        setCookie('ordersPageLength', len, 100);
    });

    function reloadTable() {
        report_table.ajax.reload();
    }

    report_table.on('xhr', function() {
        var responseData = report_table.ajax.json();
        $("#report_qutation_amount").html(responseData['report_total_amount']);
        $("#report_billing_amount").html(responseData['report_billing_amount']);
    });

    function getDashboardCount() {

        $("#order_count").html("<i class='bx bx-loader-circle bx-spin bx-rotate-90' ></i>");
        $("#order_place_amount").html("<i class='bx bx-loader-circle bx-spin bx-rotate-90' ></i>");
        $("#order_dispateched_amount").html("<i class='bx bx-loader-circle bx-spin bx-rotate-90' ></i>");
        $("#architects_count").html("<i class='bx bx-loader-circle bx-spin bx-rotate-90' ></i>");
        $("#new_architects_count").html("<i class='bx bx-loader-circle bx-spin bx-rotate-90' ></i>");
        $("#active_architects_count").html("<i class='bx bx-loader-circle bx-spin bx-rotate-90' ></i>");
        $("#electricians_count").html("<i class='bx bx-loader-circle bx-spin bx-rotate-90' ></i>");
        $("#new_electricians_count").html("<i class='bx bx-loader-circle bx-spin bx-rotate-90' ></i>");
        $("#active_electricians_count").html("<i class='bx bx-loader-circle bx-spin bx-rotate-90' ></i>");
        $("#architects_reward_count").html("<i class='bx bx-loader-circle bx-spin bx-rotate-90' ></i>");
        $("#electricians_reward_count").html("<i class='bx bx-loader-circle bx-spin bx-rotate-90' ></i>");

        $("#pridiction_count").html("<i class='bx bx-loader-circle bx-spin bx-rotate-90' ></i>");
        $("#pridiction_total_amount").html("<i class='bx bx-loader-circle bx-spin bx-rotate-90' ></i>");
        $("#pridiction_wl_amount").html("<i class='bx bx-loader-circle bx-spin bx-rotate-90' ></i>");
        $("#pridiction_billing_amount").html("<i class='bx bx-loader-circle bx-spin bx-rotate-90' ></i>");

        $("#lead_total_count").html("<i class='bx bx-loader-circle bx-spin bx-rotate-90' ></i>");
        $("#deal_convert_count").html("<i class='bx bx-loader-circle bx-spin bx-rotate-90' ></i>");
        $("#lead_won_count").html("<i class='bx bx-loader-circle bx-spin bx-rotate-90' ></i>");
        $("#lead_lost_count").html("<i class='bx bx-loader-circle bx-spin bx-rotate-90' ></i>");
        $("#lead_cold_count").html("<i class='bx bx-loader-circle bx-spin bx-rotate-90' ></i>");
        $("#lead_runing_count").html("<i class='bx bx-loader-circle bx-spin bx-rotate-90' ></i>");

        $("#executives_count").html("<i class='bx bx-loader-circle bx-spin bx-rotate-90' ></i>");
        $("#new_executives_count").html("<i class='bx bx-loader-circle bx-spin bx-rotate-90' ></i>");
        $("#active_executives_count").html("<i class='bx bx-loader-circle bx-spin bx-rotate-90' ></i>");

        $("#adm_count").html("<i class='bx bx-loader-circle bx-spin bx-rotate-90' ></i>");
        $("#new_adm_count").html("<i class='bx bx-loader-circle bx-spin bx-rotate-90' ></i>");
        $("#active_adm_count").html("<i class='bx bx-loader-circle bx-spin bx-rotate-90' ></i>");

        $("#ad_count").html("<i class='bx bx-loader-circle bx-spin bx-rotate-90' ></i>");
        $("#new_ad_count").html("<i class='bx bx-loader-circle bx-spin bx-rotate-90' ></i>");
        $("#active_ad_count").html("<i class='bx bx-loader-circle bx-spin bx-rotate-90' ></i>");

        $("#target_amount").html("<i class='bx bx-loader-circle bx-spin bx-rotate-90' ></i>");
        
        $("#report_qutation_amount").html("<i class='bx bx-loader-circle bx-spin bx-rotate-90' ></i>");
        $("#report_billing_amount").html("<i class='bx bx-loader-circle bx-spin bx-rotate-90' ></i>");

        $("#report_amount").hide();
        $.ajax({
            type: 'POST',
            url: ajaxDashboardCountData,
            data: {
                "_token": csrfToken,
                "is_first_load": $("#is_first_load").val(),
                "start_date": $("#start_date").val(),
                "end_date": $("#end_date").val(),
                "user_id": $("#sales_user_id").val(),
                "channel_partner_type": $("#channel_partner_type").val(),
                "channel_partner_user_id": $("#channel_partner_user_id").val(),
                "state_id": $("#state").val(),
                "city_id": $("#city").val()
            },
            success: function(resultData) {

                if (resultData['status'] == 1) {
                    
                    $("#pridiction_ids").val(resultData['pridiction_ids']);
                    $("#pridiction_count").html(resultData['pridiction_count']);
                    $("#pridiction_total_amount").html(resultData['pridiction_total_amount']);
                    $("#pridiction_wl_amount").html(resultData['pridiction_wl_amount']);
                    $("#pridiction_billing_amount").html(resultData['pridiction_billing_amount']);

                    $("#order_ids").val(resultData['order_ids']);
                    $("#order_count").html(resultData['order_count']);
                    
                    $("#order_place_ids").val(resultData['order_place_ids']);
                    $("#order_place_amount").html(resultData['order_place_amount']);
                    
                    $("#order_dispateched_ids").val(resultData['order_dispateched_ids']);
                    $("#order_dispateched_amount").html(resultData['order_dispateched_amount']);
                    
                    $("#architects_ids").val(resultData['architects_ids']);
                    $("#architects_count").html(resultData['architects_count']);

                    $("#new_architects_ids").val(resultData['new_architects_ids']);
                    $("#new_architects_count").html(resultData['new_architects_count']);

                    $("#active_architects_ids").val(resultData['active_architects_ids']);
                    $("#active_architects_count").html(resultData['active_architects_count']);
                    
                    $("#electricians_ids").val(resultData['electricians_ids']);
                    $("#electricians_count").html(resultData['electricians_count']);

                    $("#new_electricians_ids").val(resultData['new_electricians_ids']);
                    $("#new_electricians_count").html(resultData['new_electricians_count']);

                    $("#active_electricians_ids").val(resultData['active_electricians_ids']);
                    $("#active_electricians_count").html(resultData['active_electricians_count']);

                    $("#architects_reward_ids").val(resultData['architects_reward_ids']);
                    $("#architects_reward_count").html(resultData['architects_reward_count']);

                    $("#electricians_reward_ids").val(resultData['electricians_reward_ids']);
                    $("#electricians_reward_count").html(resultData['electricians_reward_count']);
                    
                    $("#lead_total_ids").val(resultData['lead_total_ids']);
                    $("#lead_total_count").html(resultData['lead_total_count']);
                    
                    $("#deal_convertion_ids").val(resultData['deal_convert_ids']);
                    $("#deal_convert_count").html(resultData['deal_convert_count']);

                    $("#lead_won_ids").val(resultData['lead_won_ids']);
                    $("#lead_won_count").html(resultData['lead_won_count']);
                    
                    $("#lead_lost_ids").val(resultData['lead_lost_ids']);
                    $("#lead_lost_count").html(resultData['lead_lost_count']);
                    
                    $("#lead_cold_ids").val(resultData['lead_cold_ids']);
                    $("#lead_cold_count").html(resultData['lead_cold_count']);

                    $("#lead_runing_ids").val(resultData['lead_runing_ids']);
                    $("#lead_runing_count").html(resultData['lead_runing_count']);
                    
                    $("#target_ids").val(resultData['target_ids']);
                    $("#target_amount").html(resultData['target_amount']);

                    $("#executives_ids").val(resultData['executives_ids']);
                    $("#executives_count").html(resultData['executives_count']);

                    $("#new_executives_ids").val(resultData['new_executives_ids']);
                    $("#new_executives_count").html(resultData['new_executives_count']);
                    
                    $("#active_executives_ids").val(resultData['active_executives_ids']);
                    $("#active_executives_count").html(resultData['active_executives_count']);

                    $("#adm_ids").val(resultData['adm_ids']);
                    $("#adm_count").html(resultData['adm_count']);

                    $("#new_adm_ids").val(resultData['new_adm_ids']);
                    $("#new_adm_count").html(resultData['new_adm_count']);

                    $("#active_adm_ids").val(resultData['active_adm_ids']);
                    $("#active_adm_count").html(resultData['active_adm_count']);

                    $("#ad_ids").val(resultData['ad_ids']);
                    $("#ad_count").html(resultData['ad_count']);

                    $("#new_ad_ids").val(resultData['new_ad_ids']);
                    $("#new_ad_count").html(resultData['new_ad_count']);

                    $("#active_ad_ids").val(resultData['active_ad_ids']);
                    $("#active_ad_count").html(resultData['active_ad_count']);

                    $(".highlight-card").trigger( "click" );
                    
                } else {
                    toastr["error"](resultData['msg']);
                }
            }
        });

    }

    $('#start_date').on('change', function() {
        getDashboardCount();
        $("#is_first_load").val('0');
    });

    $('#end_date').on('change', function() {
        getDashboardCount();
        $("#is_first_load").val('0');
    });

    $('#sales_user_id').on('change', function() {
        getDashboardCount();
    });

    $('#channel_partner_type').on('change', function() {
        getDashboardCount();
    });

    $('#channel_partner_user_id').on('change', function() {
        getDashboardCount();
    });

    $('#channel_partner_type').on('change', function() {

        $("#channel_partner_user_id").empty().trigger('change');
        $("#sales_user_id").empty().trigger('change');

    });

    $('#city').on('change', function() {
        getDashboardCount();
    });

    $('#state').on('change', function() {
        $("#city").empty().trigger("change");
        getDashboardCount();
    });


    $("#channel_partner_type").select2({
        minimumResultsForSearch: Infinity
    });

    $("#channel_partner_user_id").select2({
        ajax: {
            url: ajaxSearchChannelPartner,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    "type": function() {
                        return $("#channel_partner_type").val();
                    },
                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function(data, params) {
                // parse the results into the format expected by Select2
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data, except to indicate that infinite
                // scrolling can be used
                params.page = params.page || 1;
                return {
                    results: data.results,
                    pagination: {
                        more: (params.page * 30) < data.total_count
                    }
                };
            },
            cache: false
        },
        placeholder: 'Search for channel partner',

    });

    $("#sales_user_id").select2({
        ajax: {
            url: ajaxSearchSalesUser,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {

                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function(data, params) {
                // parse the results into the format expected by Select2
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data, except to indicate that infinite
                // scrolling can be used
                params.page = params.page || 1;
                return {
                    results: data.results,
                    pagination: {
                        more: (params.page * 30) < data.total_count
                    }
                };
            },
            cache: false
        },
        placeholder: 'Search for user',

    });
    
    getDashboardCount();


