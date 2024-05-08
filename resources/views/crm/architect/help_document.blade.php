@extends('layouts.main')
@section('title', $data['title'])
@section('content')

<style type="text/css">
    td p{
    max-width: 100%;
    white-space: break-spaces;
        word-break: break-all;
    }
    td{
        vertical-align: middle;
    }
</style>

                <div class="page-content">
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0 font-size-18">Help Document</h4></div>


                            </div>
                        </div>
                        <!-- end page title -->


                        <div class="row">

                            @foreach($data['help_document_list'] as $helpDocument)
                            <div class="col-xl-4 col-sm-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">


                                            <div class="col-lg-12">
                                                <div>
                                                    <a target="_blank" href="{{getSpaceFilePath($helpDocument->file_name)}}" class="d-block text-primary text-decoration-underline mb-2">Download</a>
                                                    <h5 class="text-truncate">{{$helpDocument->title}}</h5>
                                                    <ul class="list-inline mb-0">

                                                        <li class="list-inline-item">
                                                            <h5 class="font-size-14" data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="Publish Date"><i class="bx bx-calendar me-1 text-muted"></i>{{convertDateTime($helpDocument->publish_date_time)}}</h5>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            @endforeach






                        </div>

                    </div>
                    <!-- container-fluid -->
                </div>
                <!-- End Page-content -->






    @csrf
@endsection('content')
@section('custom-scripts')

@endsection