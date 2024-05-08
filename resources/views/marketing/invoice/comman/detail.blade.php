<div class="modal fade" id="modalInvoice" data-bs-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="modalInvoiceLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"> </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="">
                            <div class="">
                                <div class="invoice-title">
                                    <h4 class="float-end font-size-16" id="modalInvoiceIdLabel"></h4>

                                    <div class="mb-4">
                                        <img src="{{asset('assets/images/order-detail-logo.png')}}" alt="logo" height="50">
                                    </div>
                                </div>
                                <hr>

                                <div class="row">
                                    <div class="col-sm-6 mt-3">
                                        <address>
                                            <strong>Channel Partner Details</strong><br>
                                            <table>
                                                <tbody>
                                                    <tr>
                                                        <td>
                                                            <span class="font-weight-bolder">
                                                                <b><i class="bx bx-envelope"></i>
                                                                    <span id="modalInvoiceChannelPartnerEmailLabel"> </span></b>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <span class="font-weight-bolder">
                                                                <b><i class="bx bx-phone"></i>
                                                                    <span id="modalInvoiceChannelPartnerPhoneLabel"> </span></b>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="pr-1 pt-1">Company Name:</td>
                                                        <td class="pt-1"><b><span class="font-weight-bolder" id="modalInvoiceChannelPartnerFirmName"></span></b></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="pr-1">Name:</td>
                                                        <td><b><span class="font-weight-bolder" id="modalInvoiceChannelPartnerName"></span></b></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="pr-1">Type:</td>
                                                        <td><b><span class="font-weight-bolder" id="modalInvoiceChannelPartnerType"></span></b></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="pr-1">GST Number:</td>
                                                        <td><b><span class="font-weight-bolder" id="modalInvoiceChannelPartnerGSTNumber"></span></b></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="pr-1">Payment Mode:</td>
                                                        <td><b><span class="font-weight-bolder" id="modalInvoiceChannelPartnerPaymentMode"></span></b></td>
                                                    </tr>
                                                    <tr id="divModalInvoiceChannelPartnerCreditDays">
                                                        <td class="pr-1">Credit Days:</td>
                                                        <td><b><span class="font-weight-bolder" id="modalInvoiceChannelPartnerCreditDays"></span></b></td>
                                                    </tr>
                                                    <tr id="divModalInvoiceChannelPartnerCreditLimit">
                                                        <td class="pr-1">Credit Limit:</td>
                                                        <td><b><i class="fas fa-rupee-sign"></i><span class="font-weight-bolder" id="modalInvoiceChannelPartnerCreditLimit"></span></b></td>
                                                    </tr>
                                                    <tr id="divModalInvoiceChannelPartnerCreditPending">
                                                        <td class="pr-1">Credit Pending:</td>
                                                        <td><b><i class="fas fa-rupee-sign"></i><span class="font-weight-bolder" id="modalInvoiceChannelPartnerCreditPending"></span></b></td>
                                                    </tr>

                                                </tbody>
                                            </table>
                                        </address>
                                    </div>
                                    <div class="col-sm-6 mt-3 text-sm-end">
                                        <address>
                                            <strong>Challan Date</strong><br>
                                            <span id="modalInvoiceDateTimeLabel"></span><br><br>
                                        </address>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <address>
                                            <strong>Billed To</strong><br>
                                            <p id="modalInvoiceChannelPartnerBillAddress"></p>

                                        </address>
                                    </div>
                                    <div class="col-sm-6 text-sm-end">
                                        <address class="mt-2 mt-sm-0">
                                            <strong>Shipped To</strong><br>
                                            <p id="modalInvoiceChannelPartnerDAddress"></p>
                                        </address>
                                    </div>
                                </div>

                                <div class="py-2 mt-3">
                                    <h3 class="font-size-15 fw-bold">Delievery Challan summary</h3>
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-middle table-nowrap mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 70px;">SR<br>No.</th>
                                                <th>Product<br>Image</th>

                                                <th>Product<br>Type</th>
                                                <th>Total<br>Amount</th>
                                                <th>QTY</th>




                                            </tr>
                                        </thead>
                                        <tbody id="modalInvoiceTbody">

                                        </tbody>
                                    </table>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <address>
                                            <br>
                                            <strong>Sales Persons:</strong>
                                            <p id="modalInvoiceSalePersons"></p>
                                            <br>
                                            <strong>Remark:</strong>
                                            <p id="modalInvoiceRemark"></p>




                                        </address>

                                        <div id="attachmentDiv">


                                        </div>


                                    </div>
                                    <div class="col-sm-6 text-sm-end">

                                        <table class="float-end">
                                            <tbody>
                                                <tr>
                                                    <td class="pr-1 pt-1">Total MRP: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                                    <td class="pt-1"><b><i class="fas fa-rupee-sign"></i><span class="font-weight-bolder" id="modalInvoiceMRP"></span></b></td>
                                                </tr>
                                                <tr>
                                                    <td class="pr-1">Ex. GST (Challan value): &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                                    <td><b><i class="fas fa-rupee-sign"></i><span class="font-weight-bolder" id="modalInvoiceMRPMinusDiscount"></span></b></td>
                                                </tr>
                                                <tr>
                                                    <td class="pr-1">Estimated Tax (GST) &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                                    <td><b><i class="fas fa-rupee-sign"></i><span class="font-weight-bolder" id="modalInvoiceGSTValue"></span></b></td>
                                                </tr>

                                                <tr>
                                                    <td class="pr-1">Total: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                                    <td><b><i class="fas fa-rupee-sign"></i><span class="font-weight-bolder" id="modalInvoiceTotalPayable"></span></b></td>
                                                </tr>



                                            </tbody>
                                        </table>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>
</div>