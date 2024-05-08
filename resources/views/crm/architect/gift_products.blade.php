@extends('layouts.main')
@section('title', $data['title'])
@section('content')



                <div class="page-content">
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0 font-size-18">Gift Products / {{$data['category_name']}}</h4>

                                    <div class="page-title-right">
                                       @include('../crm/architect/comman/btn_cart')
                                    </div>


                                </div>


                            </div>
                        </div>
                        <!-- end page title -->
                        <!-- start row -->

                        <div class="row">
                            <div class="col-lg-3">
                                <div class="card">
                                    <div class="card-body">

                                        <div>
                                            <h5 class="font-size-14 mb-3">Category</h5>
                                            <ul class="list-unstyled product-list">
                                                <li><a href="{{route('architect.gift.products')}}?category=0"><i class="mdi mdi-chevron-right me-1"></i> All</a></li>

                                                @foreach($data['category'] as $category)


                                                <li><a href="{{route('architect.gift.products')}}?category={{$category->id}}"><i class="mdi mdi-chevron-right me-1"></i> {{$category->name}}</a></li>

                                                @endforeach

                                                <li><a href="{{route('architect.gift.products.cash')}}"><i class="mdi mdi-chevron-right me-1"></i> Cash </a></li>

                                            </ul>
                                        </div>







                                    </div>
                                </div>

                            </div>
                            <div class="col-lg-9">


                                <div class="row">

                                    @foreach($data['products'] as $product)
                                    <div class="col-xl-4 col-sm-6">
                                        <div class="card">
                                            <div class="card-body">
                                                <a href="{{route('architect.gift.product.detail')}}?product={{$product->id}}">
                                                <div class="product-img position-relative">

                                                    <img src="{{ getSpaceFilePath($product->image) }}" alt="" class="img-fluid mx-auto d-block">
                                                </div>
                                            </a>
                                                <div class="mt-4 text-center">
                                                    <h5 class="mb-3 text-truncate"><a href="{{route('architect.gift.product.detail')}}?product={{$product->id}}" class="text-dark">{{$product->name}} </a></h5>


                                                    <h5 class="my-0"><span class="text-muted me-2"></span> <b>{{$product->point_value}} Point</b></h5>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach

                                </div>
                                <!-- end row -->


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
