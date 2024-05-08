$("#order_sales_channel_partner_type").select2({})

function getOrderCount() {

    $("#ordersCount").html("Loading...");
    $("#ordersAmount").html("Loading...");
    $("#ordersDispatchedAmount").html("Loading...");

    $.ajax({
        type: 'POST',
        url: ajaxSaleOrderCount,
        data: {
            "_token": csrfToken,
            "start_date": $("#order_sales_start_date").val(),
            "end_date": $("#order_sales_end_date").val(),
            "channel_partner_type": $("#order_sales_channel_partner_type").val(),


        },
        success: function(resultData) {



            if (resultData['status'] == 1) {

                $("#ordersCount").html(resultData['order_count']);
                $("#ordersAmount").html(resultData['order_total_amount']);
                $("#ordersDispatchedAmount").html(resultData['order_dispateched_amount']);


            } else {

                toastr["error"](resultData['msg']);

            }
        }
    });

}

$('#order_sales_start_date,#order_sales_end_date,#order_sales_channel_partner_type').on('change', function() {
    getOrderCount();

});





getOrderCount();