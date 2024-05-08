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
            eventOrder:"description",
            header: {
                left: "prev,next today",
                center: "title",
                right: "dayGridMonth,timeGridWeek,timeGridDay,listMonth,listWeek"
            },
            eventClick: function(e) {
                var eventObj = e.event;
                if (eventObj.id) {
                    window.open(ajaxInquiryCalendarinquiryList + "?status=0&inquiry_id=" + eventObj.id, "_blank")
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
            },
            eventOrder:"index",
            eventRender: function(event, element, view) {
                
                // console.log(event);
                // return $('<div>' + event.title + '</div>');
            },
        

        });
        m.render()
    }, g.CalendarPage = new e, g.CalendarPage.Constructor = e
}(window.jQuery),
function() {
    "use strict";
    window.jQuery.CalendarPage.init()
}();

$("#inquiry_calender_user_id").select2({
    ajax: {
        url: ajaxInquiryCalenderSearchUser,
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
    placeholder: 'Search for a user',

});