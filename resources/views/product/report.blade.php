@extends('layouts.main')
@section('title', $data['title'])
@section('content')

<style type="text/css">
    td p{
    max-width: 100%;
    white-space: break-spaces;
        word-break: break-all;
    }
</style>



                <div class="page-content">
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0 font-size-18">Report

                                    </h4>





                                </div>


                            </div>
                        </div>
                        <!-- end page title -->

                          <!-- start row -->






                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body"><div class="table-responsive">
                                        <table id="datatable2" class="table table-striped table-bordered table-nowrap mb-0">

                                               <thead class="table-light">

                                                  @foreach($data['table_header_data'] as $key=>$value)

                                                        <tr>
                                                            @php
                                                            $filedArray=array_keys($value);
                                                            @endphp

                                                                @foreach($filedArray as $keyF=>$valueF)
                                                        <th>{{$value[$valueF]}}</th>

                                                              @endforeach

                                                        </tr>






                                                @endforeach
                                               </thead>



                                            <tbody>
                                                @foreach($data['table_data'] as $key=>$value)

                                                        <tr>
                                                            @php
                                                            $filedArray=array_keys($value);
                                                            @endphp

                                                                @foreach($filedArray as $keyF=>$valueF)
                                                        <td>{{$value[$valueF]}}</td>

                                                              @endforeach

                                                        </tr>






                                                @endforeach

                                            </tbody>
                                        </table>
                                        </div>

                                    </div>
                                </div>
                            </div> <!-- end col -->
                        </div> <!-- end row -->

                    </div>
                    <!-- container-fluid -->
                </div>
                <!-- End Page-content -->






    @csrf
@endsection('content')
@section('custom-scripts')
<script type="text/javascript">


</script>
@endsection