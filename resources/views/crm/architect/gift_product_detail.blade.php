@extends('layouts.main')
@section('title', $data['title'])
@section('content')



                <div class="page-content">
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0 font-size-18">{{$data['title']}}</h4>
                                     <div class="page-title-right">
                                       @include('../crm/architect/comman/btn_cart')
                                    </div>


                                </div>


                            </div>
                        </div>
                        <!-- end page title -->
                        <!-- start row -->

                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-xl-6">
                                                <div class="product-detai-imgs">
                                                    <div class="row">
                                                        <div class="col-md-2 col-sm-3 col-4">
                                                            <div class="nav flex-column nav-pills " id="v-pills-tab" role="tablist" aria-orientation="vertical">
                         <a class="nav-link active" id="product-0-tab" data-bs-toggle="pill" href="#product-0" role="tab" aria-controls="product-1" aria-selected="true">
                       <img src="{{ getSpaceFilePath($data['product']->image) }}" alt="" class="img-fluid mx-auto d-block rounded">
                     </a>

                      @if($data['product']->image2!="")

                                                                @php

                                                                $image2=explode(",",$data['product']->image2);

                                                                @endphp

                                                                @foreach($image2 as $key=>$value)
                                        <a class="nav-link" id="product-{{$key+1}}-tab" data-bs-toggle="pill" href="#product-{{$key+1}}" role="tab" aria-controls="product-{{$key+1}}" aria-selected="false">
                                                                    <img src="{{getSpaceFilePath($value)}}" alt="" class="img-fluid mx-auto d-block rounded">
                                                                </a>
                                                               @endforeach

                                                                @endif

                                                            </div>
                                                        </div>
                                                        <div class="col-md-7 offset-md-1 col-sm-9 col-8">
                                                            <div class="tab-content" id="v-pills-tabContent">
                                                                <div class="tab-pane fade show active" id="product-0" role="tabpanel" aria-labelledby="product-0-tab">
                                                                    <div>
                                                                        <img src="{{getSpaceFilePath($data['product']->image)}}" alt="" class="img-fluid mx-auto d-block">
                                                                    </div>
                                                                </div>

                                                                @if($data['product']->image2!="")

                                                                @php

                                                                $image2=explode(",",$data['product']->image2);

                                                                @endphp

                                                                @foreach($image2 as $key=>$value)




                                                                <div class="tab-pane fade" id="product-{{$key+1}}" role="tabpanel" aria-labelledby="product-{{$key+1}}-tab">
                                                                    <div>
                                                                        <img src="{{getSpaceFilePath($value)}}" alt="" class="img-fluid mx-auto d-block">
                                                                    </div>
                                                                </div>
                                                                @endforeach

                                                                @endif

                                                            </div>


                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xl-6">
                                                <div class="mt-4 mt-xl-3">
                                                    <a href="{{route('architect.gift.products')}}?category={{$data['category']->id}}" class="text-primary">{{$data['category']->name}}</a>
                                                    <h4 class="mt-1 mb-3">{{$data['product']->name}}</h4>




                                                    <h5 class="mb-4">Price : <span class="text-muted me-2"></span> <b>{{$data['product']->point_value}} Point</b></h5>


                                                        <button onclick="setCartItem({{$data['product']->id}})" type="button" class="btn btn-primary waves-effect waves-light mt-2 me-1 mb-1">
                                                                    <i class="bx bx-cart me-2"></i> Add to cart
                                                        </button>

                                                        @if($data['product']->has_cashback==1 && $data['product']->cashback!=0)


                                                        <h3>
                                                        <span class="badge badge-pill badge-soft-success font-size-13">CASHBACK Rs. {{$data['product']->cashback}} </span></h3>


                                                        @endif



                                                    <p class="text-muted mb-4">{{$data['product']->description}}</p>




                                                </div>
                                            </div>
                                        </div>
                                        <!-- end row -->
                                    </div>
                                </div>
                                <!-- end card -->
                            </div>
                        </div>

                        <!-- end row -->
                    </div>
                    <!-- container-fluid -->
                </div>
                <!-- End Page-content -->







@endsection('content')
@section('custom-scripts')
@include('../crm/architect/comman/cart_script')
<script type="text/javascript">


</script>
@endsection
