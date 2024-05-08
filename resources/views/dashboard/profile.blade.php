@extends('layouts.main')
@section('title', $data['title'])
@section('content')



                <div class="page-content">
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0 font-size-18">Profile

                                    </h4>



                                </div>


                            </div>
                        </div>
                        <!-- end page title -->




                        <div class="row">
                            <div class="col-xl-6">
                                <div class="card overflow-hidden">
                                    <div class="bg-primary bg-soft">
                                        <div class="row">
                                            <div class="col-7">
                                                <div class="text-primary p-3">
                                                    <h5 class="text-primary">Welcome to Whitelion !</h5>
                                                    <p>It will seem like simplified</p>
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


                                        </div>
                                    </div>
                                </div>
                                <!-- end card -->

                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="card-title mb-4">Personal Information</h4>

                                        <p class="text-muted mb-4"></p>
                                        <div class="table-responsive">
                                            <table class="table table-nowrap mb-0">
                                                <tbody>
                                                    <tr>
                                                        <th scope="row">Full Name :</th>
                                                        <td>{{ Auth::user()->first_name}} {{ Auth::user()->last_name}}</td>
                                                    </tr>
                                                     <tr>
                                                        <th scope="row">Type :</th>
                                                        <td>{{getUserTypeName(Auth::user()->type)}}</td>
                                                    </tr>
                                                    <tr>
                                                        <th scope="row">Mobile :</th>
                                                        <td>{{ Auth::user()->dialing_code }} {{ Auth::user()->phone_number }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th scope="row">E-mail :</th>
                                                        <td>{{ Auth::user()->email }}</td>
                                                    </tr>
                                                     <tr>
                                                        <th scope="row">Location :</th>
                                                        <td>{{ Auth::user()->address_line1 }} </td>
                                                    </tr>

                                                    <tr>
                                                        <th scope="row"></th>
                                                        <td>{{ Auth::user()->address_line2 }}

                                                        @if(Auth::user()->address_line2!=""),
                                                        @endif
                                                        {{ Auth::user()->pincode }}</td>
                                                    </tr>


                                                    <tr>
                                                        <th scope="row"></th>
                                                        <td> {{getCityName(Auth::user()->city_id)}}, {{getStateName(Auth::user()->state_id)}}, {{getCountryName(Auth::user()->country_id)}} </td>
                                                    </tr>



                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <!-- end card -->


                                <!-- end card -->
                            </div>


                        </div>
                        <!-- end row -->




                    </div>
                    <!-- container-fluid -->
                </div>
                <!-- End Page-content -->






    @csrf
@endsection('content')
@section('custom-scripts')
        <script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
        <script src="{{ asset('assets/js/pages/profile.init.js') }} "></script>
@endsection