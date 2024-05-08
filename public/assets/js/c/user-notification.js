$("#page-header-notifications-user-request").click(function() {

    window.location.replace(ajaxChannelPartnerRequest);

});

var notificationBadge = 0;
// setInterval(function() {
//     getUserNotificationBadge();
// }, 5000);

getUserNotificationBadge();
var justUpdateNotificationBadge = 0;

function getUserNotificationBadge() {


    $.ajax({
        url: ajaxGetUserNotificationBadge,
        type: 'GET',
        success: function(res) {

            if (res['status'] == 1) {



                if ($("#side_tab_no_of_marketing_pending").length > 0) {
                    if (res['no_of_marketing_pending'] == 0) {
                        res['no_of_marketing_pending'] = "";
                    }
                    $("#side_tab_no_of_marketing_pending").html(res['no_of_marketing_pending']);
                }

                if ($("#side_tab_no_of_marketing_challan_pending").length > 0) {
                    if (res['no_of_marketing_challan_pending'] == 0) {
                        res['no_of_marketing_challan_pending'] = "";
                    }
                    $("#side_tab_no_of_marketing_challan_pending").html(res['no_of_marketing_challan_pending']);
                }

                if ($("#side_tab_no_of_marketing_challan_raised").length > 0) {

                    if (res['no_of_marketing_challan_raised'] == 0) {
                        res['no_of_marketing_challan_raised'] = "";
                    }
                    $("#side_tab_no_of_marketing_challan_raised").html(res['no_of_marketing_challan_raised']);
                }


                if ($("#side_tab_no_of_marketing_challan_packed").length > 0) {

                    if (res['no_of_marketing_challan_packed'] == 0) {
                        res['no_of_marketing_challan_packed'] = "";
                    }
                    $("#side_tab_no_of_marketing_challan_packed").html(res['no_of_marketing_challan_packed']);
                }

                if ($("#side_tab_no_of_gift_order").length > 0) {

                    if (res['no_of_gift_order'] == 0) {
                        res['no_of_gift_order'] = "";
                    }
                    $("#side_tab_no_of_gift_order").html(res['no_of_gift_order']);
                }

                if ($("#side_tab_no_of_order").length > 0) {

                    if (res['no_of_order'] == 0) {
                        res['no_of_order'] = "";
                    }
                    $("#side_tab_no_of_order").html(res['no_of_order']);
                }









                if (res['unread'] == 0) {
                    $("#notification-badge-count").html('');
                } else {
                    $("#notification-badge-count").html(res['unread']);
                }

                if (res['has_request'] == 1) {
                    if (res['pending_request'] == 0) {
                        res['pending_request'] = "";

                    }
                    $("#notification-badge-pending-request").html(res['pending_request']);

                }

                if (justUpdateNotificationBadge == 0) {

                    if (notificationBadge != res['unread']) {
                        getNotificationContent();
                        notificationBadge = res['unread'];

                    }

                } else {
                    justUpdateNotificationBadge = 0;

                }




            }

        }
    });


}

$(".notification-tab").click(function() {
    getNotificationContent();
});

function getNotificationContent() {

    var actionTab = $(".notification-tab.active").attr('href');
    actionTab = actionTab.substring(1);
    $("#" + actionTab).html("Loading...");

    $.ajax({
        url: ajaxGetUserNotificationContent + "?activetab=" + actionTab,
        type: 'GET',
        success: function(res) {

            if (res['status'] == 1) {

                $("#" + actionTab).html(res['view']);


            }

        }
    });
}

function getNotificationContentHistory() {

    var actionTab = $(".notification-tab.active").attr('href');
    actionTab = actionTab.substring(1);
    var notificationID = $("#" + actionTab + " .notification-item").last().attr('notification-id');
    notificationID = notificationID.split('-');
    notificationID = notificationID[2];


    $.ajax({
        url: ajaxGetUserNotificationContent + "?activetab=" + actionTab + "&get_history=1&&notification_id=" + notificationID,
        type: 'GET',
        success: function(res) {

            if (res['status'] == 1) {

                $("#" + actionTab).append(res['view']);

            }

        }
    });

}

function unreadNotification(id) {
    event.preventDefault();
    var actionTab = $(".notification-tab.active").attr('href');
    actionTab = actionTab.substring(1);

    $.ajax({
        url: ajaxUnReadNotification + "?notification_id=" + id,
        type: 'GET',
        success: function(res) {

            if (res['status'] == 1) {

                $("#btn-notification-read-" + actionTab + "-" + id).attr("onclick", 'readNotification(' + id + ')');
                $("#btn-notification-read-" + actionTab + "-" + id).html('<i class="mdi mdi-circle-outline user-notication-btm"></i><span> Mark as read</span>');
                $("#btn-notification-read-" + actionTab + "-" + id).removeClass('btn-primary');
                $("#btn-notification-read-" + actionTab + "-" + id).addClass('btn-outline-primary');
                justUpdateNotificationBadge = 1;

            }

        }
    });

}

function readNotification(id) {
    event.preventDefault();

    var actionTab = $(".notification-tab.active").attr('href');
    actionTab = actionTab.substring(1);

    $.ajax({
        url: ajaxReadNotification + "?notification_id=" + id,
        type: 'GET',
        success: function(res) {

            if (res['status'] == 1) {

                $("#btn-notification-read-" + actionTab + "-" + id).attr("onclick", 'unreadNotification(' + id + ')');
                $("#btn-notification-read-" + actionTab + "-" + id).html('<i class="mdi mdi-circle-outline user-notication-btm"></i><span> Mark as unread</span>');
                $("#btn-notification-read-" + actionTab + "-" + id).removeClass('btn-outline-primary');
                $("#btn-notification-read-" + actionTab + "-" + id).addClass('btn-primary');

                justUpdateNotificationBadge = 1;

            }

        }
    });

}



function removeFromFavouriteNotification(id) {
    event.preventDefault();

    var actionTab = $(".notification-tab.active").attr('href');
    actionTab = actionTab.substring(1);

    $.ajax({
        url: ajaxFavouriteRemoveNotification + "?notification_id=" + id,
        type: 'GET',
        success: function(res) {

            if (res['status'] == 1) {

                $("#btn-notification-favourite-" + actionTab + "-" + id).attr("onclick", 'favouriteNotification(' + id + ')');
                $("#btn-notification-favourite-" + actionTab + "-" + id).html('<i class="mdi mdi-star user-notication-btm"></i><span> Favourite</span>');
                $("#btn-notification-favourite-" + actionTab + "-" + id).removeClass('btn-primary');
                $("#btn-notification-favourite-" + actionTab + "-" + id).addClass('btn-outline-primary');



            }

        }
    });

}

function favouriteNotification(id) {
    event.preventDefault();

    var actionTab = $(".notification-tab.active").attr('href');
    actionTab = actionTab.substring(1);

    $.ajax({
        url: ajaxFavouriteNotification + "?notification_id=" + id,
        type: 'GET',
        success: function(res) {

            if (res['status'] == 1) {

                $("#btn-notification-favourite-" + actionTab + "-" + id).attr("onclick", 'removeFromFavouriteNotification(' + id + ')');
                $("#btn-notification-favourite-" + actionTab + "-" + id).html('<i class="mdi mdi-star user-notication-btm"></i><span> Favourite</span>');
                $("#btn-notification-favourite-" + actionTab + "-" + id).removeClass('btn-outline-primary');
                $("#btn-notification-favourite-" + actionTab + "-" + id).addClass('btn-primary');




            }

        }
    });

}


$(document).ready(function() {
    $("#notification-content .simplebar-content-wrapper").scroll(function() {

        if ($(this).scrollTop() + $(this).innerHeight() >= $(this)[0].scrollHeight) {
            getNotificationContentHistory();
        } else {
            // console.log("Test2");
        }

    });
});