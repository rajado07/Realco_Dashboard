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
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Shopee</a></li>
                            <li class="breadcrumb-item">Brand Portal</a></li>
                            <li class="breadcrumb-item active">Shop</a></li>
                        </ol>
                    </div>
                    <h4 class="page-title">Shopee Portal Shop</h4>
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
            <div class="col-auto">
                <!-- Button -->
                <button type="button" id="btn-refresh" class="btn btn-primary"><i class="ri-refresh-line"></i></button>
            </div>
        </div>
        

         <!-- start dashboar card -->
        <div class="row row-cols-1 row-cols-xxl-5 row-cols-lg-3 row-cols-md-2">
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="text-muted fw-normal mb-2" title="Gross Sales">Gross Sales</h5>
                                <h3 class="mb-1" id="gross-sales">0</h3>
                                <div>
                                    <span id="gross-sales-change-percentage" class="badge bg-success me-1">
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
                                <h5 class="text-muted fw-normal mb-2" title="Gross Order">Gross Order</h5>
                                <h3 class="mb-1" id="gross-order">0</h3>
                                <div>
                                    <span id="gross-order-change-percentage" class="badge bg-success me-1">
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
                                <h5 class="text-muted fw-normal mb-2" title="Gross Unit Sold">Gross Unit Sold</h5>
                                <h3 class="mb-1" id="gross-unit-sold">0</h3>
                                <div>
                                    <span id="gross-units-sold-change-percentage" class="badge bg-success me-1">
                                        <i class="ri-arrow-up-line"></i> 1 %
                                    </span>
                                </div>
                            </div>
                            <div>
                                <!-- Optional icon or image can be placed here if needed -->
                                <i class="ri-draft-line text-light" style="font-size: 3rem;"></i>
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
                                <h5 class="text-muted fw-normal mb-2" title="Average Basket Size">Average Basket Size</h5>
                                <h3 class="mb-1" id="average-basket-size">0</h3>
                                <div>
                                    <span id="average-basket-size-change-percentage" class="badge bg-success me-1">
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
                                <h5 class="text-muted fw-normal mb-2" title="Average Selling Price">Average Selling Price</h5>
                                <h3 class="mb-1" id="average-selling-price">0</h3>
                                <div>
                                    <span id="average-selling-price-change-percentage" class="badge bg-success me-1">
                                        <i class="ri-arrow-up-line"></i> 1 %
                                    </span>
                                </div>
                            </div>
                            <div>
                                <!-- Optional icon or image can be placed here if needed -->
                                <i class="ri-coupon-line text-light" style="font-size: 3rem;"></i>
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
    @vite(['resources/js/pages/shopee/brand-portal-shop.js'])
@endsection
