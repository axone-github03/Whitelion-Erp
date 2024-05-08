

function getInquiryCount() {

    $("#inquiryCount").html("<i class='bx bx-loader-circle bx-spin bx-rotate-90' ></i>");
    $("#nonPrimeArchitectsCount").html("<i class='bx bx-loader-circle bx-spin bx-rotate-90' ></i>");
    $("#primeArchitectsCount").html("<i class='bx bx-loader-circle bx-spin bx-rotate-90' ></i>");
    $("#inquiryConversionRatio").html("<i class='bx bx-loader-circle bx-spin bx-rotate-90' ></i>");
    $("#nonPrimeElecriciansCount").html("<i class='bx bx-loader-circle bx-spin bx-rotate-90' ></i>");
    $("#primeElecriciansCount").html("<i class='bx bx-loader-circle bx-spin bx-rotate-90' ></i>");
    $("#inquiryCountMateialSent").html("<i class='bx bx-loader-circle bx-spin bx-rotate-90' ></i>");
    $("#inquiryCountRejected").html("<i class='bx bx-loader-circle bx-spin bx-rotate-90' ></i>");
    $("#inquiryCountNonPotential").html("<i class='bx bx-loader-circle bx-spin bx-rotate-90' ></i>");

    $.ajax({
        type: 'POST',
        url: ajaxInquiryArchitectsCountData,
        data: {
            "_token": csrfToken,
            "start_date": $("#start_date").val(),
            "end_date": $("#end_date").val(),
            "user_id": $("#sales_user_id").val(),
        },
        success: function(resultData) {

            if (resultData['status'] == 1) {

                $("#inquiryCount").html(resultData['inquiry_count']);
                $("#nonPrimeArchitectsCount").html(resultData['non_prime_architects_count']);
                $("#primeArchitectsCount").html(resultData['prime_architects_count']);

                $("#nonPrimeElecriciansCount").html(resultData['non_prime_electricians_count']);
                $("#primeElecriciansCount").html(resultData['prime_electricians_count']);

                $("#inquiryCountMateialSent").html(resultData['inquiry_material_sent']);
                $("#inquiryCountRejected").html(resultData['inquiry_rejected']);
                $("#inquiryCountNonPotential").html(resultData['inquiry_non_potential']);
                // $("#inquiryConversionRatio").html(resultData['conversion_ratio']);
                $("#inquiryConversionRatio").html(resultData['runing_lead']);


            } else {

                toastr["error"](resultData['msg']);

            }
        }
    });

}

$('#start_date,#end_date,#sales_user_id').on('change', function() {
    getInquiryCount();

});
getInquiryCount();