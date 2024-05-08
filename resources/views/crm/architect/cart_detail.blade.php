<div class="row">
                            <div class="col-xl-8">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table align-middle mb-0 table-nowrap">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Product</th>
                                                        <th>Product Desc</th>
                                                        <th>Quantity</th>
                                                        <th colspan="2">Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody>

                                                    @foreach($data['products'] as $product)


                                                    <tr>
                                                        <td>
                                                            <img src="{{getSpaceFilePath($product->image)}}" title="product-img" class="avatar-md">
                                                        </td>
                                                        <td>
                                                            <h5 class="font-size-14 text-truncate"><a href="{{route('architect.gift.product.detail')}}?product={{$product->id}}" class="text-dark">{{$product->name}}</a></h5>
                                                            <p class="mb-0"><span class="fw-medium">{{$product->category->name}}</span></p>
                                                        </td>

                                                        <td>
                                                            1
                                                        </td>
                                                        <td>
                                                            {{$product->point_value}} Point
                                                        </td>
                                                        <td>
                                                            <a href="javascript:void(0);" onclick="removeFromCart({{$product->id}})" class="action-icon text-danger"> <i class="mdi mdi-trash-can font-size-18"></i></a>
                                                        </td>
                                                    </tr>

                                                    @endforeach

                                                    @if($data['user_total_cash_pv']!=0)

                                                    <tr>
                                                        <td>

                                                            <div class="">
                                               <img src="{{ asset('assets/images/currency.jpeg') }}" style="width:75px">
                                        </div>
                                                        </td>
                                                        <td>
                                                            <h5 class="font-size-14 text-truncate"><a href="{{route('architect.gift.products.cash')}}" class="text-dark">Cash</a></h5>
                                                            <p class="mb-0"><span class="fw-medium"></span></p>
                                                        </td>

                                                        <td>
                                                            1
                                                        </td>
                                                        <td>
                                                            {{$data['user_total_cash_pv']}} Point
                                                        </td>
                                                        <td>
                                                            <a href="#" onclick="removeFromCart('cash')" class="action-icon text-danger"> <i class="mdi mdi-trash-can font-size-18"></i></a>
                                                        </td>
                                                    </tr>

                                                    @endif

                                                </tbody>
                                            </table>
                                        </div>



                                        <div class="row mt-4">
                                            <div class="col-sm-6">
                                                <a href="{{route('architect.gift.products')}}" class="btn btn-secondary">
                                                    <i class="mdi mdi-arrow-left me-1"></i> Continue Shopping </a>
                                            </div> <!-- end col -->
                                            <div class="col-sm-6">
                                                <div class="text-sm-end mt-2 mt-sm-0">
                                                    <a href="#" onclick="previewOrder()"  class="btn btn-success @if($data['checkout_btn_visible']==0)
                                                    disabled
                                                    @endif
                                                    " >
                                                        <i class="mdi mdi-cart-arrow-right me-1"></i> Place Order </a>
                                                </div>
                                            </div> <!-- end col -->
                                        </div> <!-- end row-->
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4">

                                 <div class="card">
                                    <div class="card-body">
                                        <h4 class="card-title mb-3">Order Summary</h4>

                                        <div class="table-responsive">
                                            <table class="table mb-0">
                                                <tbody>


                                                    <tr>
                                                        <td>Your Total Point</td>
                                                        <td>{{$data['user_total_pv']}} Point</td>
                                                    </tr>




                                                    <tr>
                                                        <td>Order Redeem Total Point :</td>
                                                        <td><b>{{$data['total_pv']}} Point</b></td>
                                                    </tr>

                                                      <tr>
                                                        <td><span class="badge badge-pill badge-soft-success font-size-13">Total Cashback You will recieve :</span></td>
                                                        <td class=""><span class="badge badge-pill badge-soft-success font-size-13">{{$data['user_total_cashback'] }} Rs.</span></td>
                                                      </tr>


                                                      <tr>
                                                        <td><span class="badge badge-pill badge-soft-success font-size-13">Total Cash You will recieve :</span></td>
                                                        <td><span class="badge badge-pill badge-soft-success font-size-13">{{$data['user_total_cash'] }} Rs.</span></td>
                                                      </tr>

                                                        <tr>
                                                        <td><span class="badge badge-pill badge-soft-primary font-size-13">Total Amount You recieve : </span></td>
                                                        <td><span class="badge badge-pill badge-soft-primary font-size-13">{{$data['user_total_cash']+$data['user_total_cashback'] }} Rs.</span></td>
                                                      </tr>




                                                    <tr>

                                                        <td colspan="2">
                                                            <button  data-bs-toggle="modal" data-bs-target="#modalChangeAddress" role="button" class="btn  btn-sm p-0 text-decoration-underline" >Change Address?</button>


                                                    </td>

                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <!-- end table-responsive -->
                                    </div>
                                </div>
                                <!-- end card -->
                            </div>
    </div>

