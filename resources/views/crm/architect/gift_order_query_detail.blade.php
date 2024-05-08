@extends('layouts.main')
@section('title', $data['title'])
@section('content')



                <div class="page-content">
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0 font-size-18">Query Ticket #{{$data['gift_product_order_query']->id}}

                                        @if($data['gift_product_order_query']->status==1)

                               <span class="badge badge-pill badge-soft-success font-size-15">CLOSED</span>


                                        @else

                                        <span class="badge badge-pill badge-soft-warning font-size-15">OPEN</span>


                                        @endif


</h4>
                                     <div class="page-title-right">



                                         <h4 > Order #{{$data['gift_product_order']->id}}</h4>

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
                                           <h4 class="card-title">{{$data['gift_product_order_query']->title}}</h4>
                                        <p class="card-title-desc">{{$data['gift_product_order_query']->description}}</p>
                                        <!-- end row -->
                                    </div>
                                </div>
                                <!-- end card -->
                            </div>
                        </div>
                        <div class="row">
                             <div class="chat-conversation p-3">
                                            <ul class="list-unstyled mb-0" data-simplebar >
                                                <li>
                                                    <div class="chat-day-title">
                                                        <span class="title">Conversion</span>
                                                    </div>
                                                </li>

                                                @foreach($data['gift_product_orders_query_conversion'] as $keyC=>$valueC)

                                                @if($valueC['from_user_id']!=Auth::user()->id)
                                                <li>
                                                    <div class="conversation-list">

                                                        <div class="ctext-wrap">
                                                            <div class="conversation-name">Whitelion Systems</div>
                                                            <p>
                                                      {!!nl2br($valueC['message'])!!}
                                                            </p>
                                                            <p class="chat-time mb-0"><i class="bx bx-time-five align-middle me-1"></i>{{$valueC['created_at']}}</p>
                                                        </div>

                                                    </div>
                                                </li>
                                                @endif

                                                @if($valueC['from_user_id']==Auth::user()->id)

                                                <li class="right">
                                                    <div class="conversation-list">

                                                        <div class="ctext-wrap">
                                                            <div class="conversation-name">{{Auth::user()->first_name}} {{Auth::user()->last_name}}</div>
                                                            <p>

 {!!nl2br($valueC['message'])!!}

                                                            </p>

                                                            <p class="chat-time mb-0"><i class="bx bx-time-five align-middle me-1"></i>{{$valueC['created_at']}}</p>
                                                        </div>
                                                    </div>
                                                </li>
                                                 @endif


                                                @endforeach


                                            </ul>
                                        </div>

                                         @if($data['gift_product_order_query']->status==0)

                                        <form action="{{route('crm.user.order.query.conversion.save')}}" method="POST" >

                                               @csrf

                                               <input type="hidden" id="conversion_query_id" name="conversion_query_id" value="{{$data['gift_product_order_query']->id}}"  >

                                        <div class="p-3 chat-input-section">
                                            <div class="row">
                                                <div class="col">
                                                    <div class="position-relative">
                                                        <textarea rows="5"  name="message" id="message" class="form-control" placeholder="Enter Message..."></textarea>

                                                    </div>
                                                </div>
                                                <div class="col-auto">
                                                    <button type="submit" class="btn btn-primary btn-rounded chat-send w-md waves-effect waves-light"><span class="d-none d-sm-inline-block me-2">Send</span> <i class="mdi mdi-send"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                        </form>
                                        @endif
                        </div>


                        <!-- end row -->
                    </div>
                    <!-- container-fluid -->
                </div>
                <!-- End Page-content -->







@endsection('content')
@section('custom-scripts')

<script src="{{ asset('assets/js/pages/form-validation.init.js') }}"></script>
<script src="{{ asset('assets/js/pages/jquery.form.js') }}"></script>
<script src="{{ asset('assets/libs/parsleyjs/parsley.min.js') }}"></script>
<script type="text/javascript">


</script>
@endsection
