$("#inquiry_source_type").select2({});
$("#inquiry_status").select2({});
$("#inquiry_filter_type").select2({});
$("#inquiry_client_product").select2({});


var datatableInquiry = $('#datatableInquiry').DataTable({
    "aoColumnDefs": [{
        "bSortable": false,
        "aTargets": []
    }],
    "order": [
        [0, 'desc']
    ],
    "scrollX": true,
    "processing": true,
    "serverSide": true,
    "pageLength": 10,
    "ajax": {
        "url": ajaxInquiryList,
        "type": "POST",
        "data": {
            "_token": csrfToken,
            "start_date": function() {
                return $("#inquiry_source_wise_start_date").val();
            },
            "end_date": function() {
                return $("#inquiry_source_wise_end_date").val();
            },
            "sales_user_id": function() {
                return $("#inquiry_report_sales_user_id").val();
            },
            "source_type": function() {
                return $("#inquiry_source_type").val();
            },
            "source_user": function() {
                return $("#inquiry_source_user").val();
            },
            "status": function() {
                return $("#inquiry_status").val();
            },
            "type": function() {
                return $("#inquiry_filter_type").val();
            },
            "client_product": function() {
                return $("#inquiry_client_product").val();
            }


        }


    },
    "aoColumns": [

        {
            "mData": "id"
        }, {
            "mData": "name"
        }, {
            "mData": "phone_number"
        }, {
            "mData": "address"
        }, {
            "mData": "status"
        }, {
            "mData": "source_type"
        }, {
            "mData": "source"
        }, {
            "mData": "quotation_amount"
        }

    ],
    "drawCallback": function() {

        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })

    }
});


datatableInquiry.on('xhr', function() {

    var responseData = datatableInquiry.ajax.json();
    $("#totalQuotationAmount").html(responseData['quotationAmount']);





});



$("#inquiry_report_sales_user_id").select2({
    ajax: {
        url: ajaxSearchSalePerson,
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


$("#inquiry_source_user").select2({
    ajax: {
        url: ajaxSearchSource,
        dataType: 'json',
        delay: 0,
        data: function(params) {
            return {
                "source_type": function() {
                    return $("#inquiry_source_type").val();
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
    placeholder: 'Search for a user',

});
var datatableSourceWiseSalePerson = $('#datatableSourceWiseSalePerson').DataTable({
    "aoColumnDefs": [{
        "bSortable": false,
        "aTargets": []
    }],
    "ordering": false,
    "processing": true,
    "serverSide": true,
    "bPaginate": false,
    "pageLength": 100,
    "ajax": {
        "url": ajaxSourceWiseSalePerson,
        "type": "POST",
        "data": {
            "_token": csrfToken,
            "start_date": function() {
                return $("#inquiry_source_wise_start_date").val();
            },
            "end_date": function() {
                return $("#inquiry_source_wise_end_date").val();
            },
            "source_type": function() {
                return $("#inquiry_source_type").val();
            },
            "source_user": function() {
                return $("#inquiry_source_user").val();
            },
            "status": function() {
                return $("#inquiry_status").val();
            },
            "type": function() {
                return $("#inquiry_filter_type").val();
            },
            "client_product": function() {
                return $("#inquiry_client_product").val();
            }

        }


    },
    "aoColumns": [

        {
            "mData": "sale_persons"
        }, {
            "mData": "inquiry_count"
        }

    ],
    "drawCallback": function() {

        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })

    }
});

datatableSourceWiseSalePerson.on('xhr', function() {

    var responseData = datatableSourceWiseSalePerson.ajax.json();


});




var datatableSourceWiseSource = $('#datatableSourceWiseSource').DataTable({
    "aoColumnDefs": [{
        "bSortable": false,
        "aTargets": []
    }],
    "ordering": false,
    "processing": true,
    "serverSide": true,
    "bPaginate": false,
    "pageLength": 100,
    "ajax": {
        "url": ajaxSourceWiseSource,
        "type": "POST",
        "data": {
            "_token": csrfToken,
            "start_date": function() {
                return $("#inquiry_source_wise_start_date").val()
            },
            "end_date": function() {
                return $("#inquiry_source_wise_end_date").val();
            },
            "sales_user_id": function() {
                return $("#inquiry_report_sales_user_id").val();
            },
            "source_type": function() {
                return $("#inquiry_source_type").val();
            },
            "source_user": function() {
                return $("#inquiry_source_user").val()
            },
            "status": function() {
                return $("#inquiry_status").val();
            },
            "type": function() {
                return $("#inquiry_filter_type").val();
            },
            "client_product": function() {
                return $("#inquiry_client_product").val();
            }

        }


    },
    "aoColumns": [

        {
            "mData": "source"
        }, {
            "mData": "inquiry_count"
        }

    ],
    "drawCallback": function() {

        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })

    }
});



$("#inquiry_report_sales_user_id").change(function() {

    $("#inquiry_source_type").val("0-0");
    $("#inquiry_source_type").trigger("change");
    $("#inquiry_source_user").empty().trigger('change');
    loadSourceWiseSource();
    datatableInquiry.ajax.reload();



});

$("#inquiry_source_type").change(function() {



    $("#inquiry_source_user").empty().trigger('change');
    loadSourceWiseSource();
    datatableInquiry.ajax.reload();



});

$("#inquiry_status").change(function() {

    var inquiry_status = $("#inquiry_status").val();
    if (inquiry_status == 102) {

        $("#div_client_product").show();

    } else {
        $("#div_client_product").hide();
    }



});
$("#inquiry_status").trigger("change");

$("#inquiry_source_wise_start_date,#inquiry_source_wise_end_date,#inquiry_source_user,#inquiry_status,#inquiry_client_product").change(function() {

    var salePerson = $("#inquiry_report_sales_user_id").val();
    var sourceType = $("#inquiry_source_type").val();
    var sourceUser = $("#inquiry_source_user").val();
    $(".source-wise-table").hide();

    if (salePerson == 0) {
        loadSourceWiseSalePerson();
        $("#divSourceWiseSalePerson").show();
        datatableInquiry.ajax.reload();

    } else if (salePerson != 0 && sourceType != "0-0" && sourceUser != 0) {

        $("#divSourceWiseSource").show();
        loadSourceWiseSource();
        datatableInquiry.ajax.reload();

    } else if (salePerson != 0 && sourceType != "0-0") {
        $("#divSourceWiseSource").show();
        loadSourceWiseSource();
        datatableInquiry.ajax.reload();

    } else if (salePerson != 0) {
        loadSourceWiseSourceTypes();
        datatableInquiry.ajax.reload();
        $("#divSourceWiseSourceType").show();

    }
    var inquiry_status = $("#inquiry_status").val();
    if (inquiry_status == "1,2,3,4,5,6,7,8" || inquiry_status == "!10") {

        $("#inquiry_source_wise_start_date").prop('disabled', true);
        $("#inquiry_source_wise_end_date").prop('disabled', true);

    } else {
        $("#inquiry_source_wise_start_date").prop('disabled', false);
        $("#inquiry_source_wise_end_date").prop('disabled', false);

    }



});

function loadSourceWiseSalePerson() {
    datatableSourceWiseSalePerson.ajax.reload(null, false);
}

function loadSourceWiseSourceTypes() {



    $.ajax({
        type: 'POST',
        url: ajaxSourceWiseSourceTypes,
        data: {
            "_token": csrfToken,
            "start_date": function() {
                return $("#inquiry_source_wise_start_date").val()
            },
            "end_date": function() {
                return $("#inquiry_source_wise_end_date").val();
            },
            "sales_user_id": function() {
                return $("#inquiry_report_sales_user_id").val();
            },
            "status": function() {
                return $("#inquiry_status").val();
            },
            "type": function() {
                return $("#inquiry_filter_type").val();
            },
            "client_product": function() {
                return $("#inquiry_client_product").val();
            }


        },
        success: function(resultData) {


            if (resultData['status'] == 1) {

                $("#datatableSourceWiseSourceType tbody").html(resultData['view']);

            } else {

                toastr["error"](resultData['msg']);

            }
        }
    });
}

function loadSourceWiseSource() {
    datatableSourceWiseSource.ajax.reload(null, false);
}

$(".source-wise-table").hide();
loadSourceWiseSourceTypes();
$("#divSourceWiseSalePerson").show();

$("#inquiryDownload").click(function() {

    var start_date = $("#inquiry_source_wise_start_date").val();
    var end_date = $("#inquiry_source_wise_end_date").val();
    var sales_user_id = $("#inquiry_report_sales_user_id").val();
    var source_type = $("#inquiry_source_type").val();
    var source_user = $("#inquiry_source_user").val();
    var status = $("#inquiry_status").val();

    var downloadURL = ajaxInquiryDownload + "?start_date=" + start_date + "&end_date=" + end_date + "&sales_user_id=" + sales_user_id + "&source_type=" + source_type + "&source_user=" + source_user + "&status=" + status;
    window.open(downloadURL, '_blank');



});