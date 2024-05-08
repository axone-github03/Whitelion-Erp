<div class="modal fade" id="modalPointLog" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" role="dialog" aria-labelledby="modalPointLogLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPointLogLabel"> Point Log</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="min-height:100%;">


                <table id="pointLogTable" class="table align-middle table-nowrap mb-0 w-100">
                    <thead>
                        <tr>

                            <th>Log</th>




                        </tr>
                    </thead>


                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalInquiryLog" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" role="dialog" aria-labelledby="modalInquiryLogLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalInquiryLogLabel"> Inquiry List</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="min-height:100%;">

                <div class="row text-center mb-3">
                    <div class="col-3">
                        <h5 class="mb-0" id="totalInquiry">0</h5>
                        <button class="btn btn-primary btn-sm" id="btnInquiryLogTotal">Total Inquiry</button>
                    </div>
                    <div class="col-3">
                        <h5 class="mb-0" id="totalRunningInquiry">0</h5>
                        <button class="btn btn-primary btn-sm" id="btnInquiryLogRunning">Running Inquiry</button>
                    </div>
                    <div class="col-3">
                        <h5 class="mb-0" id="totalWonInquiry">0</h5>
                        <button class="btn btn-primary btn-sm" id="btnInquiryLogWon">Won Inquiry</button>
                    </div>
                    <div class="col-3">
                        <h5 class="mb-0" id="totalRejectedInquiry">0</h5>
                        <button class="btn btn-primary btn-sm" id="btnInquiryLogLost">Lost Inquiry</button>
                    </div>
                </div>
                <div class="float-end">

                    <button type="button" class="btn-sm btn btn-outline-dark waves-effect waves-light float-end" aria-haspopup="true" aria-expanded="false">Quotation Amount: <span id="totalInquiryLogQuotationAmount"></span></button>
                </div>

                <table id="InquiryTable" class="table align-middle table-nowrap mb-0 w-100">
                    <thead>
                        <tr>

                            <th>#Id</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Quotation Amount</th>
                            <th>Architect</th>
                            <th>Channel Partner</th>

                        </tr>
                    </thead>


                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>