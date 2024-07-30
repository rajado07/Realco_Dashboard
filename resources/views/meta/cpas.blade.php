@extends('layouts.vertical', ['page_title' => 'Starter Page', 'mode' => $mode ?? '', 'demo' => $demo ?? ''])

@section('css')
    @vite([
        'node_modules/datatables.net-bs5/css/dataTables.bootstrap5.min.css',
        'node_modules/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css',
        'node_modules/datatables.net-fixedcolumns-bs5/css/fixedColumns.bootstrap5.min.css',
        'node_modules/datatables.net-fixedheader-bs5/css/fixedHeader.bootstrap5.min.css',
        'node_modules/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css',
        'node_modules/datatables.net-select-bs5/css/select.bootstrap5.min.css',
        'node_modules/select2/dist/css/select2.min.css',
        'node_modules/flatpickr/dist/flatpickr.min.css',
        'node_modules/daterangepicker/daterangepicker.css',
    ])
@endsection

@section('content')
    <!-- Start Content-->
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Meta</a></li>
                            <li class="breadcrumb-item active">CPAS</a></li>
                        </ol>
                    </div>
                    <h4 class="page-title">Meta CPAS</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row justify-content-end mb-2">
            <div class="col-auto">
                <span class="form-control">
                    Latest Retrieved Data At : <span id="latestRetrievedData">N/A</span>
                </span>
            </div>
            <div class="col-auto">
                <!-- Date Picker -->
                <div class="form-group">
                    <div id="reportrange" class="form-control d-flex justify-content-between align-items-center" data-toggle="date-picker-range" data-target-display="#selectedDate" data-cancel-class="btn-light">
                        <i class="ri-calendar-2-line"></i>
                        <span id="selectedDate">Select date</span>
                        <i class="ri-arrow-down-s-line"></i>
                    </div>
                </div>
            </div>
            <div class="col-sm-1">
                <!-- Select Brand -->
                <div class="form-group">
                    <select id="selectedBrand" name="brand" class="form-control select2" data-toggle="select2" required>
                        <!-- Options will be dynamically populated here -->
                    </select>
                </div>
            </div>
            <div class="col-sm-1">
                <!-- Select Market Place -->
                <div class="form-group">
                    <select id="selectedMarketPlace" name="brand" class="form-control select2" data-toggle="select2" required>
                        <!-- Options will be dynamically populated here -->
                    </select>
                </div>
            </div>
            <div class="col-auto">
                <!-- Button -->
                <button type="button" id="btn-refresh" class="btn btn-primary"><i class="ri-refresh-line"></i></button>
            </div>
        </div>
        

        <!-- start dashboar card -->
        <div class="row">
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="text-muted fw-normal mb-2" title="Amount Spent">Amount Spent</h5>
                                <h3 class="mb-1" id="amount-spent">0</h3>
                                <div>
                                    <span id="amount-spent-change-percentage" class="badge bg-success me-1">
                                        <i class="ri-arrow-up-line"></i> 1 %
                                    </span>
                                </div>
                            </div>
                            <div>
                                <!-- Optional icon or image can be placed here if needed -->
                                <i class="ri-money-dollar-circle-line text-light" style="font-size: 3rem;"></i>
                            </div>
                        </div>
                    </div> 
                </div> 
            </div>

            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="text-muted fw-normal mb-2" title="Content Views">Content Views</h5>
                                <h3 class="mb-1" id="content-views">0</h3>
                                <div>
                                    <span id="content-views-change-percentage" class="badge bg-success me-1">
                                        <i class="ri-arrow-up-line"></i> 1 %
                                    </span>
                                </div>
                            </div>
                            <div>
                                <!-- Optional icon or image can be placed here if needed -->
                                <i class="ri-eye-line text-light" style="font-size: 3rem;"></i>
                            </div>
                        </div>
                    </div> 
                </div> 
            </div>

            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="text-muted fw-normal mb-2" title="Impressions">Impressions</h5>
                                <h3 class="mb-1" id="impressions">0</h3>
                                <div>
                                    <span id="impressions-change-percentage" class="badge bg-success me-1">
                                        <i class="ri-arrow-up-line"></i> 1 %
                                    </span>
                                </div>
                            </div>
                            <div>
                                <!-- Optional icon or image can be placed here if needed -->
                                <i class=" ri-picture-in-picture-line text-light" style="font-size: 3rem;"></i>
                            </div>
                        </div>
                    </div> 
                </div> 
            </div>

            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="text-muted fw-normal mb-2" title="Adds To Cart">Adds To Cart</h5>
                                <h3 class="mb-1" id="adds-to-cart">0</h3>
                                <div>
                                    <span id="adds-to-cart-change-percentage" class="badge bg-success me-1">
                                        <i class="ri-arrow-up-line"></i> 1 %
                                    </span>
                                </div>
                            </div>
                            <div>
                                <!-- Optional icon or image can be placed here if needed -->
                                <i class="ri-shopping-basket-line text-light" style="font-size: 3rem;"></i>
                            </div>
                        </div>
                    </div> 
                </div> 
            </div> 

            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="text-muted fw-normal mb-2" title="Purchases">Purchases</h5>
                                <h3 class="mb-1" id="purchases">0</h3>
                                <div>
                                    <span id="purchases-change-percentage" class="badge bg-success me-1">
                                        <i class="ri-arrow-up-line"></i> 1 %
                                    </span>
                                </div>
                            </div>
                            <div>
                                <!-- Optional icon or image can be placed here if needed -->
                                <i class="ri-shopping-bag-line text-light" style="font-size: 3rem;"></i>
                            </div>
                        </div>
                    </div> 
                </div> 
            </div>
            
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="text-muted fw-normal mb-2" title="Purchases Conversion Value">Purchases Conversion Value</h5>
                                <h3 class="mb-1" id="purchases-conversion-value">0</h3>
                                <div>
                                    <span id="purchases-conversion-value-change-percentage" class="badge bg-success me-1">
                                        <i class="ri-arrow-up-line"></i> 1 %
                                    </span>
                                </div>
                            </div>
                            <div>
                                <!-- Optional icon or image can be placed here if needed -->
                                <i class="ri-swap-box-line text-light" style="font-size: 3rem;"></i>
                            </div>
                        </div>
                    </div> 
                </div> 
            </div> 

        </div> <!-- end row -->
         <!-- End dashboar card -->

        <!-- Start Data Table-->
        <div class="row">
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <table id="basic-datatable" class="table table-responsive-sm nowrap w-100">
                            <thead>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div> 
            </div>
        <!-- End Data Table-->

    </div> <!-- container -->

@endsection

@section('script')
    @vite(['resources/js/pages/meta/cpas.js'])
@endsection
