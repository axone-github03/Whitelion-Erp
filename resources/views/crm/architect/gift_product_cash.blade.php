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
                                                <div class="product-detai-imgs">
                                                    <div class="row">

                                                          <div class="col-xl-3">
                                                <div class="mt-4 mt-xl-3">


                                                     <div class="col-md-2 col-sm-3 col-4">
                                                     </div>

                                                      <div class="col-md-3 offset-md-4 col-sm-9 col-8">
                                                            <div class="tab-content" id="v-pills-tabContent">
                                                                <div class="tab-pane fade show active" id="product-0" role="tabpanel" aria-labelledby="product-0-tab">
                                                                    <div class="">

                                                                        <img src="{{ asset('assets/images/currency.jpeg') }}" style="width:200px">


                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>



</div>
                                            </div>
                                             <div class="col-xl-6">

                                                  <p class="text-muted mb-4">1 Point = {{$data['point_value']}} Rs.</p>



                                                    <h5 class="mb-4">Point Reedem <input type="number" min="0" id="point_count" name="point_count"  placeholder="Enter Points" value="0" /> = <span id="pointValueAmount">0</span> Rs.</h5>


                                                           <button onclick="setCartItem('cash-0')" id="AddtoCartBtn" type="button" class="btn btn-primary waves-effect waves-light mt-2 me-1 mb-1">
                                                                    <i class="bx bx-cart me-2"></i> Add to cart
                                                        </button>

                                                        <input type="hidden" id="point_value_hidden" name="point_value_hidden" value="{{$data['point_value']}}" >














                                                    </div>
                                                </div>
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

$('#point_count').on('change', function(){

    changePointValue();

});

$('#point_count').on('keydown', function(){

    changePointValue();

});

$('#point_count').on('keyup', function(){

    changePointValue();

});
function changePointValue(){


    var pointCount=$("#point_count").val();
    var pointValue=$("#point_value_hidden").val();
    var totalAmount=pointCount*pointValue;
    $("#pointValueAmount").html(totalAmount);
    $("#AddtoCartBtn").attr("onclick",'setCartItem(\'cash-'+pointCount+'\')')
}


</script>
@endsection
