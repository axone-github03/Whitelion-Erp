
@extends('layouts.main')
@section('title', $data['title'])
@section('content')



                <div class="page-content">
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0 font-size-18">Dashboard</h4>
                                </div>
                            </div>
                        </div>
                        <!-- end page title -->

                        <div class="row">
                            <div class="col-xl-7">
                                <div class="card overflow-hidden">
                                    <div class="bg-primary bg-soft">
                                        <div class="row">
                                            <div class="col-7">
                                                <div class="text-primary p-3">
                                                    <h5 class="text-primary">Welcome Back !</h5>
                                                    <p>Whitelion Systems Private Limited</p>
                                                </div>
                                            </div>
                                            <div class="col-5 align-self-end">
                                                <img src="assets/images/profile-img.png" alt="" class="img-fluid">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body pt-0">
                                        <div class="row">
                                            <div class="col-sm-4">
                                                <div class="avatar-md profile-user-wid mb-4">
                                                    <img src="{{ Auth::user()->avatar}}" alt="" class="img-thumbnail rounded-circle">
                                                </div>
                                                <h5 class="font-size-15 text-truncate">{{ Auth::user()->first_name}} {{ Auth::user()->last_name}}</h5>
                                                <p class="text-muted mb-0 text-truncate">{{getUserTypeName(Auth::user()->type)}}</p>
                                            </div>

                                            <div class="col-sm-8">
                                                <div class="pt-4">

                                                    <div class="row">
                                                        <div class="col-3">

                                                            <p class="text-muted mb-0 text-center">Loyalty</p>
                                                        </div>
                                                        <div class="col-3">

                                                            <p class="text-muted mb-0 text-center">Greatness</p>
                                                        </div>
                                                        <div class="col-3">

                                                            <p class="text-muted mb-0 text-center">Relationship</p>
                                                        </div>
                                                        <div class="col-3">

                                                            <p class="text-muted mb-0 text-center">Clarity</p>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </div>
                        <!-- end row -->




                    </div>
                    <!-- container-fluid -->
                </div>
                <!-- End Page-content -->

                <!-- Transaction Modal -->






@endsection('content')
@section('custom-scripts')
<script type="text/javascript"></script>
@endsection