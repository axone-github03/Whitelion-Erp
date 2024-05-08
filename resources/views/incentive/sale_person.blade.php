@extends('layouts.main')
@section('title', $data['title'])
@section('content')



                <div class="page-content">
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0 font-size-18">Incentive - Sale Person</h4>

                                                     <div class="page-title-right">


<button id="addBtnSalesHierarchy" class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#canvasSalesHierarchy" aria-controls="canvasSalesHierarchy"><i class="bx bx-plus font-size-16 align-middle me-2"></i>Incentive - Sale Person </button>


<div class="offcanvas offcanvas-end" tabindex="-1" id="canvasSalesHierarchy" aria-labelledby="canvasSalesHierarchyLabel">
                                            <div class="offcanvas-header">
                                              <h5 id="canvasSalesHierarchyLabel">Incentive - Sale Person</h5>
                                              <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                            </div>
                                            <div class="offcanvas-body">

                                                <div class="col-md-12 text-center loadingcls">






                                            <button type="button" class="btn btn-light waves-effect">
                                                <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading
                                            </button>


                                               </div>






                                                 <form id="formSalesHierarch" class="custom-validation" action="{{route('sales.hierarchy.save.process')}}" method="POST"  >

                                              @csrf

                                              <input type="hidden" name="sales_hierarchy_id" id="sales_hierarchy_id" >



                                                <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="sales_hierarchy_name" class="form-label">Name</label>
                                                <input type="text" class="form-control" id="sales_hierarchy_name" name="sales_hierarchy_name"
                                                    placeholder="Name" value="" required>


                                            </div>
                                        </div>

                                    </div>

                                      <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="sales_hierarchy_code" class="form-label">Code</label>
                                                <input type="text" class="form-control" id="sales_hierarchy_code_d" name="sales_hierarchy_code_d"
                                                    placeholder="" value="" disabled >
                                            <input type="hidden" name="sales_hierarchy_code" id="sales_hierarchy_code" >


                                            </div>
                                        </div>

                                    </div>



                                     <div class="row">

                                         <div class="col-lg-12">
                                                    <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                                        <label class="form-label">Parent </label>
                                                        <select  multiple="multiple" class="form-control select2-ajax select2-multiple" id="sales_hierarchy_parent_id" name="sales_hierarchy_parent_id" >

                                                        </select>

                                                    </div>

                                                </div>




                                    </div>


                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="sales_hierarchy_status" class="form-label">Status</label>

                                                <select id="sales_hierarchy_status" name="sales_hierarchy_status" class="form-control select2-apply" >
                                                    <option value="1">Active</option>
                                                    <option value="0">Inactive</option>
                                                    <option value="2">Blocked</option>


                                                </select>



                                            </div>
                                        </div>

                                    </div>


                                    <div class="d-flex flex-wrap gap-2">
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">
                                            Save
                                        </button>
                                        <button type="reset" class="btn btn-secondary waves-effect">
                                            Reset
                                        </button>
                                    </div>
                                </form>





                                            </div>
                                        </div>
                                    </div>


                                </div>


                            </div>
                        </div>
                        <!-- end page title -->
                        <!-- start row -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">



                                        <table id="datatable" class="table table-striped dt-responsive  nowrap w-100">
                                            <thead>
                                            <tr>
                                                <th>Id</th>
                                                <th>Monthspan code</th>
                                                <th>Percentage</th>
                                                <th>Incentive</th>
                                                <th>Status</th>
                                                <th>Action</th>


                                            </tr>
                                            </thead>


                                            <tbody>

                                            </tbody>
                                        </table>

                                    </div>
                                </div>
                            </div> <!-- end col -->
                        </div> <!-- end row -->

                        <!-- end row -->
                    </div>
                    <!-- container-fluid -->
                </div>
                <!-- End Page-content -->







@endsection('content')
@section('custom-scripts')

<script type="text/javascript">


</script>
@endsection
