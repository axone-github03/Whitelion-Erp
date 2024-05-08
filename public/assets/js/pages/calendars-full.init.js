! function(g) {
    "use strict";

    function e() {}
    e.prototype.init = function() {

        var v = (document.getElementById("calendar"));
        $("#inquiry_calender_user_id").change(function() {
            m.refetchEvents();

        });
        var m = new FullCalendar.Calendar(v, {

            allDaySlot: true,
            plugins: ["bootstrap", "interaction", "dayGrid", "timeGrid"],
            defaultView: "timeGridDay",
            themeSystem: "bootstrap",
            header: {
                left: "prev,next today",
                center: "title",
                right: "dayGridMonth,timeGridWeek,timeGridDay,listMonth,listWeek"
            },
            eventClick: function(e) {
                var eventObj = e.event;
                if (eventObj.id) {


                    //callInquiryDetail(eventObj.id)


                }
            },
            events: {
                url: ajaxInquiryCalendarData,
                extraParams: function() { // a function that returns an object
                    return {
                        user_id: $("#inquiry_calender_user_id").val()
                    };
                }
            }

        });
        m.render()
    }, g.CalendarPage = new e, g.CalendarPage.Constructor = e
}(window.jQuery),
function() {
    "use strict";
    window.jQuery.CalendarPage.init()
}();