<div class="row">
   <div class="col-lg-12">
      <div class="">
         <div class="">
            <div class="invoice-title">
               <h4 class="float-end font-size-16" id="previwOrderIdLabel">#{{$data['order']['id']}}</h4>
               <div class="mb-4">
                  <img src="{{asset('assets/images/order-detail-logo.png')}}" alt="logo" height="50">
               </div>
               <hr>
               <div class="row">
                  <div class="col-sm-6 mt-3">
                     <address>
                        <strong>{{$data['name']}}</strong><br>
                        <table>
                           <tbody>
                              <tr>
                                 <td>
                                    <span class="font-weight-bolder">
                                       <b><i class="bx bx-envelope"></i>
                                          <span id="previwOrderChannelPartnerEmailLabel">{{$data['email']}}</span></b>
                                    </span>
                                 </td>
                              </tr>
                              <tr>
                                 <td>
                                    <span class="font-weight-bolder">
                                       <b><i class="bx bx-phone"></i>
                                          <span id="previwOrderChannelPartnerPhoneLabel">{{$data['phone_number']}}</span></b>
                                    </span>
                                 </td>
                              </tr>
                              <tr>
                                 <td class="pr-1 pt-1">{{$data['d_address_line1']}}</td>
                                 <td class="pt-1"><b><span class="font-weight-bolder" id=""></span></b></td>
                              </tr>
                              @if($data['d_address_line2']!="")

                              <tr>
                                 <td class="pr-1 pt-1">{{$data['d_address_line2']}}</td>
                                 <td class="pt-1"><b><span class="font-weight-bolder" id=""></span></b></td>
                              </tr>

                              @endif

                              <tr>
                                 <td class="pr-1 pt-1">{{$data['d_pincode']}}</td>
                                 <td class="pt-1"><b><span class="font-weight-bolder" id=""></span></b></td>
                              </tr>

                              <tr>
                                 <td class="pr-1 pt-1">{{$data['d_city']}}, {{$data['d_state']}}, {{$data['d_country']}}</td>
                                 <td class="pt-1"><b><span class="font-weight-bolder" id=""></span></b></td>
                              </tr>



                           </tbody>
                        </table>
                     </address>
                  </div>
                  <div class="col-sm-6 mt-3 text-sm-end">
                     <address>
                        <strong>Order Date</strong><br>
                        <span id="previwOrderDateTimeLabel">{{convertOrderDateTime($data['order']['created_at'],"date")}}</span><br><br>
                     </address>
                  </div>
               </div>

               <div class="py-2 mt-3">
                  <h3 class="font-size-15 fw-bold">Order summary</h3>
               </div>
               <div class="table-responsive">
                  <table class="table align-middle table-nowrap mb-0">
                     <thead class="table-light vertical-align-middle">
                        <tr>
                           <th style="width: 70px;">SR No.</th>
                           <th>Product Image</th>
                           <th>Product</th>
                           <th>Point</th>
                           <th>QTY</th>
                           <th>Total Point</th>


                        </tr>
                     </thead>
                     <tbody id="previewOrderPreviwTbody">
                        @php

                        $key2=0;

                        @endphp

                        @foreach($data['order']['items'] as $key=>$value)

                        @php
                        $key2=$key2+1;

                        @endphp
                        <tr>
                           <td style="width: 70px;">{{$key2}}</td>
                           <td>

                              <img src="{{getSpaceFilePath($value['info']['image'])}}" alt="logo" height="75">


                           </td>
                           <td>{{$value['info']['name']}}</td>
                           <td> {{(int)$value['point_value']}}</td>
                           <td>{{$value['qty']}}</td>
                           <td>{{(int)$value['total_point_value']}}</td>

                        </tr>

                        @endforeach

                        @if($data['total_cash_pv']!=0)

                        @if(count($data['order']['items'])==0)

                        @php $key2=$key2+1; @endphp

                        @endif

                        <tr>
                           <td style="width: 70px;">{{$key2}}</td>
                           <td>

                              <img src="{{ asset('assets/images/currency.jpeg') }}" style="width:75px">

                           </td>
                           <td>Cash</td>
                           <td> {{(int)$data['total_cash_pv']}}</td>
                           <td> 1</td>
                           <td> {{(int)$data['total_cash_pv']}}</td>

                        </tr>

                        @endif
                     </tbody>
                  </table>
               </div>

               <div class="row">
                  <div class="col-sm-6">

                  </div>
                  <div class="col-sm-6 text-sm-end">
                     <table class="float-end">
                        <tbody>
                           <tr>
                              <td class="pr-1 pt-1">Order Redeem Total Point :&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                              <td class="pt-1"><b>Point <span class="font-weight-bolder" id="previewOrderMRP">{{$data['order']['total_product_pv']+$data['total_cash_pv']}}</span></b></td>
                           </tr>
                           <tr>
                              <td><span class="badge badge-pill badge-soft-success font-size-13">Total Cashback You will recieve :</span></td>
                              <td class=""><span class="badge badge-pill badge-soft-success font-size-13">{{$data['order']['total_cashback_value'] }} Rs.</span></td>
                           </tr>


                           <tr>
                              <td><span class="badge badge-pill badge-soft-success font-size-13">Total Cash You will recieve :</span></td>
                              <td><span class="badge badge-pill badge-soft-success font-size-13">{{$data['total_cash'] }} Rs.</span></td>
                           </tr>

                           <tr>
                              <td><span class="badge badge-pill badge-soft-primary font-size-13">Total Amount You recieve : </span></td>
                              <td><span class="badge badge-pill badge-soft-primary font-size-13">{{$data['total_cash']+$data['order']['total_cashback_value'] }} Rs.</span></td>
                           </tr>


                        </tbody>
                     </table>
                  </div>
               </div>
               <input type="hidden" name="is_preview" id="is_preview" value="{{$data['preview']}}">
               <input type="hidden" name="total_amount" id="total_amount" value="{{$data['total_cash']+$data['order']['total_cashback_value'] }}">

               @if($data['preview']==1)
               <br>

               @if(($data['total_cash']+$data['order']['total_cashback_value'])!=0)

               <div class="card">
                  <h5 class="card-header bg-transparent border-bottom text-uppercase">AMOUNT RECIEVE PAYMENT METHOD</h5>
                  <div class="card-body">



                     <div class="row">
                        <div class="col-sm-2">
                           <div class="form-check mb-3">
                              <input class="form-check-input" type="radio" name="payment_mode" id="payment_mode_1">
                              <label class="form-check-label" for="payment_mode_1" value="1">
                                 BANK DETAIL
                              </label>
                           </div>
                           <div class="form-check">
                              <input class="form-check-input" type="radio" name="payment_mode" id="payment_mode_2">
                              <label class="form-check-label" for="payment_mode_2" value="1">
                                 UPI ID
                              </label>
                           </div>

                        </div>
                        <div class="col-sm-4">
                           <div class="">

                              <input type="text" class="form-control col-sm-2" id="bank_detail_account" name="bank_detail_account" placeholder="ACCOUNT NO" value="{{$data['payment_mode']['bank_detail_account']}}" style="width:60%;float: left;margin-right: 4px;padding: 4px 4px;margin-bottom: 4px;">


                              <input type="text" class="form-control col-sm-2" id="bank_detail_ifsc" name="bank_detail_ifsc" placeholder="IFSC CODE" value="{{$data['payment_mode']['bank_detail_ifsc']}}" style="width:35%;float: left;padding: 4px 4px;">
                           </div>




                           <input type="text" class="form-control col-sm-2" value="{{$data['payment_mode']['bank_detail_upi']}}" id="bank_detail_upi" name="bank_detail_upi" placeholder="UPI ID" value="" style="padding: 4px 4px;">



                        </div>
                     </div>
                  </div>
               </div>
            </div>


            @endif
            <div class="row">
               <div class="col-sm-6">


                  <input type="hidden" name="has_aadhar_card" id="has_aadhar_card" value="{{$data['has_aadhar_card']}}">
                  @if($data['has_aadhar_card']==0)



                  <div class="col-md-6">
                     <div class="mb-3">
                        <label for="aadhar_card" class="form-label">Aadhar card <code class="highlighter-rouge">*</code></label>
                        <input type="file" class="form-control" id="aadhar_card" name="aadhar_card" placeholder="Last Name" value="" required>


                     </div>
                  </div>




                  @endif
               </div>
               <div class="col-sm-6 text-sm-end">
                  <button id="btnPlaceOrder" type="button" class="btn btn-success waves-effect waves-light" onclick="placeOrder()">Place Order</button>

                  <button data-bs-dismiss="modal" aria-label="Close" type="button" class="btn btn-warning waves-effect waves-light">Cancel</button>
               </div>
            </div>
            @endif

            @if($data['preview']==0)

            @if(($data['order']['total_cash']+$data['order']['total_cashback_value'])!=0)
            <br>

            <div class="card border border-primary">
               <div class="card-header bg-transparent border-primary">
                  <h5 class="my-0 text-primary"><i class="mdi mdi-bullseye-arrow me-3"></i>AMOUNT RECIEVE PAYMENT METHOD</h5>
               </div>



               @php
               $paymentMethod="";
               @endphp

               @if($data['order']['payment_mode']==1)
               @php
               $paymentMethod=" BANK DETAIL";
               @endphp
               @endif


               @if($data['order']['payment_mode']==2)
               @php
               $paymentMethod="UPI ID";
               @endphp
               @endif



               <div class="card-body">
                  <h5 class="card-title">PAYMENT METHOD:<b>{{$paymentMethod}}</b></h5>







                  @if($data['order']['payment_mode']==1)
                  <p class="card-text"> BANK ACCOUNT : <b>{{$data['order']['bank_detail_account']}}</b></p>
                  <p class="card-text"> BANK IFSC CODE: <b>{{$data['order']['bank_detail_ifsc']}}</b></p>

                  @endif

                  @if($data['order']['payment_mode']==2)
                  <p class="card-text"> UPI ID : <b>{{$data['order']['bank_detail_upi']}}</b></p>


                  @endif


               </div>
            </div>

            @endif
            @endif
         </div>
      </div>
   </div>
</div>