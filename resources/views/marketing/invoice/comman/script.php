<script type="text/javascript">
   function ViewInvoice(id) {

      $.ajax({
         type: 'GET',
         url: ajaxInvoiceDetail + "?invoice_id=" + id,
         success: function(resultData) {
            if (resultData['status'] == 1) {

               $("#modalInvoice").modal('show');
               $("#modalInvoiceIdLabel").html("#" + resultData['data']['invoice_number']);
               $("#modalInvoiceDateTimeLabel").html(resultData['data']['display_date_time']);

               $("#modalInvoiceChannelPartnerEmailLabel").html(resultData['data']['channel_partner_email']);
               $("#modalInvoiceChannelPartnerPhoneLabel").html(resultData['data']['channel_partner_dialing_code'] + ' ' + resultData['data']['channel_partner_phone_number']);

               $("#modalInvoiceChannelPartnerFirmName").html(resultData['data']['channel_partner_firm_name']);

               $("#modalInvoiceChannelPartnerName").html(resultData['data']['channel_partner_first_name'] + ' ' + resultData['data']['channel_partner_last_name']);

               $("#modalInvoiceChannelPartnerType").html(resultData['data']['channel_partner_type_name']);

               $("#modalInvoiceChannelPartnerGSTNumber").html(resultData['data']['gst_number']);

               $("#modalInvoiceChannelPartnerPaymentMode").html(resultData['data']['payment_mode_lable']);

               if (resultData['data']['payment_mode'] == 2) {

                  $("#divModalInvoiceChannelPartnerCreditDays").show();
                  $("#divModalInvoiceChannelPartnerCreditLimit").show();
                  $("#divModalInvoiceChannelPartnerCreditPending").show();

               } else {

                  $("#divModalInvoiceChannelPartnerCreditDays").hide();
                  $("#divModalInvoiceChannelPartnerCreditLimit").hide();
                  $("#divModalInvoiceChannelPartnerCreditPending").hide();

               }

               var billAddress = resultData['data']['bill_address_line1'];
               if (resultData['data']['bill_address_line2'] != "") {
                  billAddress += "<br>" + resultData['data']['bill_address_line2'];
               }
               billAddress += "<br>" + resultData['data']['d_pincode'];
               billAddress += "<br>" + resultData['data']['d_city_name'] + ", " + resultData['data']['d_state_name'] + ", " + resultData['data']['d_country_name'];

               $("#modalInvoiceChannelPartnerBillAddress").html(billAddress);


               var dAddress = resultData['data']['d_address_line1'];
               if (resultData['data']['d_address_line2'] != "") {
                  dAddress += "<br>" + resultData['data']['d_address_line2'];
               }
               dAddress += "<br>" + resultData['data']['d_pincode'];
               dAddress += "<br>" + resultData['data']['d_city_name'] + ", " + resultData['data']['d_state_name'] + ", " + resultData['data']['d_country_name'];

               $("#modalInvoiceChannelPartnerDAddress").html(dAddress);

               var tBody = "";
               $("#modalInvoiceTbody").html(tBody);
               for (var i = 0; i < resultData['data']['items'].length; i++) {
                  tBody += "<tr>";

                  tBody += "<td>";
                  tBody += "" + (i + 1) + "";
                  tBody += "</td>";

                  tBody += "<td>";

                  tBody += '<img src="' + getSpaceFilePath(resultData['data']['items'][i]['product_image']) + '" alt="logo" height="50">';

                  tBody += "</td>";

                  // tBody += "<td>";
                  // tBody += "" + resultData['data']['items'][i]['product_group_name'] + "";
                  // tBody += "</td>";

                  tBody += "<td>";
                  tBody += "" + resultData['data']['items'][i]['product_code_name'] + "";
                  tBody += "</td>";

                  tBody += "<td>";
                  tBody += "<i class='fas fa-rupee-sign'></i>" + numberWithCommas(resultData['data']['items'][i]['total_mrp']) + "";
                  tBody += "</td>";

                  tBody += "<td>";
                  tBody += "" + resultData['data']['items'][i]['qty'] + "";
                  tBody += "</td>";








                  tBody += "</tr>";

               }




               $("#modalInvoiceTbody").html(tBody);

               var attachmentDiv = "<table class='table table-bordered text-center'>";
               attachmentDiv += "<tbody>";


               if (resultData['data']['eway_bill'] != "") {

                  attachmentDiv += "<tr>";
                  attachmentDiv += "<td>Eway Bill</td>";
                  attachmentDiv += "<td><a target='_blank' href='" + getSpaceFilePath(resultData['data']['eway_bill']) + "' title='PDF'><i class='bx font-size-20 bxs-file-pdf'></i></a></td>";
                  attachmentDiv += "</tr>";

               }

               if (resultData['data']['dispatch_detail'].length > 0) {

                  for (var i = 0; i < resultData['data']['dispatch_detail'].length; i++) {

                     if (resultData['data']['dispatch_detail'][i] != "") {

                        attachmentDiv += "<tr>";
                        attachmentDiv += "<td>Dispatch Detail</td>";
                        attachmentDiv += "<td><a target='_blank' href='" + getSpaceFilePath(resultData['data']['dispatch_detail'][i]) + "' title='PDF'><i class='bx font-size-20 bxs-file-pdf'></i></a></td>";
                        attachmentDiv += "</tr>";

                     }



                  }


               }








               attachmentDiv += "</tbody>";
               attachmentDiv += "</table>";
               $("#attachmentDiv").html(attachmentDiv);




               $("#modalInvoiceSalePersons").html(resultData['data']['sale_persons']);
               $("#modalInvoiceMRP").html(numberWithCommas(resultData['data']['total_mrp']));
               $("#modalInvoiceMRPMinusDiscount").html(numberWithCommas(resultData['data']['total_mrp_minus_disocunt']));
               //$("#modalInvoiceGSTPecentage").html(resultData['data']['gst_percentage']);
               $("#modalInvoiceGSTValue").html(numberWithCommas(resultData['data']['gst_tax']));
               // $("#modalInvoiceDelievery").html(numberWithCommas(resultData['data']['delievery_charge']));
               $("#modalInvoiceTotalPayable").html(numberWithCommas(resultData['data']['total_payable']));



               $("#modalInvoiceChannelPartnerCreditDays").html(resultData['data']['channel_partner_credit_days']);

               $("#modalInvoiceChannelPartnerCreditLimit").html(numberWithCommas(resultData['data']['channel_partner_credit_limit']));
               $("#modalInvoiceChannelPartnerCreditPending").html(numberWithCommas(resultData['data']['channel_partner_pending_credit']));
               $("#modalInvoiceRemark").html(resultData['data']['remark'])







            } else {
               toastr["error"](resultData['msg']);
            }

         }
      });

   }
</script>