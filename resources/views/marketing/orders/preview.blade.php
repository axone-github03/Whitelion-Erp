<div class="row">
    <div class="col-lg-12">
        <div class="">
            <div class="">
                <div class="invoice-title">
                    <h4 class="float-end font-size-16" id="previwOrderIdLabel">#marketingRequestId</h4>
                    <div class="mb-4">
                        <img src="{{ asset('assets/images/order-detail-logo.png') }}" alt="logo" height="50">
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
                                                        <span
                                                            id="previwOrderChannelPartnerEmailLabel">{{ $data['channel_partner']['email'] }}</span></b>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span class="font-weight-bolder">
                                                    <b><i class="bx bx-phone"></i>
                                                        <span
                                                            id="previwOrderChannelPartnerPhoneLabel">{{ $data['channel_partner']['dialing_code'] }}
                                                            {{ $data['channel_partner']['phone_number'] }}</span></b>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="pr-1 pt-1">Company Name:</td>
                                            <td class="pt-1"><b><span class="font-weight-bolder"
                                                        id="previwOrderChannelPartnerFirmName">{{ $data['channel_partner']['firm_name'] }}</span></b>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="pr-1">Name:</td>
                                            <td><b><span class="font-weight-bolder"
                                                        id="previwOrderChannelPartnerName">{{ $data['channel_partner']['first_name'] }}
                                                        {{ $data['channel_partner']['last_name'] }}</span></b></td>
                                        </tr>
                                        <tr>
                                            <td class="pr-1">Type:</td>
                                            <td><b><span class="font-weight-bolder"
                                                        id="previwOrderChannelPartnerType">{{ $data['channel_partner']['short_type_name'] }}
                                                    </span></b></td>
                                        </tr>
                                        <tr>
                                            <td class="pr-1">GST Number:</td>
                                            <td><b><span class="font-weight-bolder"
                                                        id="previwOrderChannelPartnerGSTNumber">{{ $data['channel_partner']['gst_number'] }}</span></b>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="pr-1">Payment Mode:</td>
                                            <td><b><span class="font-weight-bolder"
                                                        id="previwOrderChannelPartnerPaymentMode">{{ getPaymentModeName($data['channel_partner']['payment_mode']) }}</span></b>
                                            </td>
                                        </tr>
                                        @if ($data['channel_partner']['payment_mode'] == 2)
                                            <tr id="previwOrderChannelPartnerCreditDaysDiv">
                                                <td class="pr-1">Credit Days:</td>
                                                <td><b><span class="font-weight-bolder"
                                                            id="previwOrderChannelPartnerCreditDays">{{ $data['channel_partner']['credit_days'] }}</span></b>
                                                </td>
                                            </tr>
                                            <tr id="divpreviwOrderChannelPartnerCreditLimit">
                                                <td class="pr-1">Credit Limit:</td>
                                                <td><b><i class="fas fa-rupee-sign"></i><span class="font-weight-bolder"
                                                            id="previwOrderChannelPartnerCreditLimit">{{ $data['channel_partner']['credit_limit'] }}</span></b>
                                                </td>
                                            </tr>
                                            <tr id="divpreviwOrderChannelPartnerCreditPending">
                                                <td class="pr-1">Credit Pending:</td>
                                                <td><b><i class="fas fa-rupee-sign"></i><span class="font-weight-bolder"
                                                            id="previwOrderChannelPartnerCreditPending">{{ $data['channel_partner']['pending_credit'] }}</span></b>
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </address>
                        </div>
                        <div class="col-sm-6 mt-3 text-sm-end">
                            <address>
                                <strong>Request Date</strong><br>
                                <span
                                    id="previwOrderDateTimeLabel">{{ convertOrderDateTime($data['order']['created_dt'], 'date') }}</span><br><br>
                            </address>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <address>
                                <strong>Billed To</strong><br>
                                <p id="previwOrderChannelPartnerBillAddress">
                                    {{ $data['channel_partner']['address_line1'] }}
                                    @if ($data['channel_partner']['address_line2'] != '')
                                        <br>
                                        {{ $data['channel_partner']['address_line2'] }}
                                    @endif
                                    <br>
                                    {{ $data['channel_partner']['pincode'] }}
                                    <br>
                                    {{ getCityName($data['channel_partner']['city_id']) }},
                                    {{ getStateName($data['channel_partner']['state_id']) }},
                                    {{ getCountryName($data['channel_partner']['country_id']) }}



                                </p>

                            </address>
                        </div>
                        <div class="col-sm-6 text-sm-end">
                            <address class="mt-2 mt-sm-0">
                                <strong>Shipped To</strong><br>
                                <p id="previwOrderChannelPartnerDAddress">
                                    {{ $data['d_address_line1'] }}
                                    @if ($data['d_address_line2'] != '')
                                        <br>
                                        {{ $data['d_address_line2'] }}
                                    @endif
                                    <br>
                                    {{ $data['d_pincode'] }}
                                    <br>
                                    {{ $data['d_country'] }}, {{ $data['d_state'] }}, {{ $data['d_city'] }}

                                </p>
                            </address>
                        </div>
                    </div>

                    <div class="py-2 mt-3">
                        <h3 class="font-size-15 fw-bold">Marketing request summary</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle table-nowrap mb-0">
                            <thead class="table-light vertical-align-middle">
                                <tr>
                                    <th style="width: 70px;">SR No.</th>
                                    <th>Product Image</th>
                                    <th>Product</th>
                                    <th>QTY</th>
                                    <th>Width</th>
                                    <th>Height</th>
                                    <th>Box Image</th>
                                    <th>Sample Image</th>
                                </tr>
                            </thead>
                            <tbody id="previewOrderPreviwTbody">
                                @foreach ($data['order']['items'] as $key => $value)
                                    <tr>
                                        <td style="width: 70px;">{{ $key + 1 }}</td>
                                        <td>

                                            <img src="{{ $value['info']['thumb'] }}" alt="logo" height="75">


                                        </td>
                                        <td>{{ $value['info']['product_code']['name'] }}</td>


                                        <td>{{ $value['qty'] }}</td>
                                        @if ($value['is_custom'] == 1)
                                            <td>{{ $value['width'] }}</td>
                                            <td>{{ $value['height'] }}</td>
                                            <td><img src="{{ $value['box_image'] }}" alt="" srcset=""
                                                    height="25"
                                                    onclick="openbase64image('{{ $value['box_image'] }}');"></td>
                                            <td><img src="{{ $value['sample_image'] }}" alt="" srcset=""
                                                    height="25"
                                                    onclick="openbase64image('{{ $value['sample_image'] }}');"></td>
                                        @else
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                        @endif
                                    </tr>
                                @endforeach


                            </tbody>
                        </table>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <address>
                                <br>
                                <strong>Sales Persons:</strong>
                                <p id="previewOrderSalePersons">
                                    @foreach ($data['salePerson'] as $key => $value)
                                        {{ $value['first_name'] }} {{ $value['last_name'] }}
                                    @endforeach
                                </p>



                            </address>
                            <div class="col-sm-6">

                                <label class="form-label">Remark</label>
                                <textarea class="form-control" id="previewRemark"></textarea>
                            </div>
                        </div>
                        <div class="col-sm-6 text-sm-end">

                            <table class="float-end">
                                <tbody>





                                </tbody>
                            </table>




                        </div>


                    </div>
                    <br>
                    <br>
                    <div class="row">
                        <div class="col-sm-6"></div>
                        <div class="col-sm-6 text-sm-end">
                            <button id="btnPlaceOrder" type="button"
                                class="btn btn-success waves-effect waves-light">Place Request</button>

                            <button id="btnPlaceOrderCancel" data-bs-dismiss="modal" aria-label="Close" type="button"
                                class="btn btn-warning waves-effect waves-light">Cancel</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

