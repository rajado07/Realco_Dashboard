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
                            <li class="breadcrumb-item active">Summary</a></li>
                        </ol>
                    </div>
                    <h4 class="page-title">Shopee Summary</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row justify-content-end mb-2">

            <div class="col-auto mt-1" style="margin-right: auto;">
                <div class="form-check form-switch">
                    <input type="checkbox" class="form-check-input" id="advanced-comparisons">
                    <label class="form-check-label" for="customSwitch1">Advanced Comparisons</label>
                </div>
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
            <div class="col-auto" id="datePickerContainer" style="display: none;">
                <!-- Date Picker -->
                <div class="form-group">
                    <div id="reportrange" class="form-control d-flex justify-content-between align-items-center" data-toggle="date-picker-range" data-target-display="#selectedDate2" data-cancel-class="btn-light">
                        <i class="ri-calendar-2-line"></i>
                        <span id="selectedDate2">Select date</span>
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
        
        <!-- Start Data Table-->
        <div class="row">
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <table id="shopee-datatable" class="table table-responsive-sm w-100">
                            <thead>
                                <tr class="header-main">
                                    <th rowspan="2" class="text-center align-middle">Data Group Name</th>
                                    <th colspan="3" class="text-center">Product Views</th>
                                    <th colspan="3" class="text-center">Conversion</th>
                                    <th colspan="3" class="text-center">GMV</th>
                                </tr>
                                <tr class="header-sub">
                                    <th class="text-center">This Week</th>
                                    <th class="text-center">Previous Week</th>
                                    <th class="text-center">Growth</th>
                                    <th class="text-center">This Week</th>
                                    <th class="text-center">Previous Week</th>
                                    <th class="text-center">Growth</th>
                                    <th class="text-center">This Week</th>
                                    <th class="text-center">Previous Week</th>
                                    <th class="text-center">Growth</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div> 
            </div>
        <!-- End Data Table-->
    </div> <!-- container -->

    <!-- Start Data Table-->
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <table id="meta-cpas-datatable" class="table table-responsive-sm w-100">
                        <thead>
                            <tr class="header-main">
                                <th rowspan="2" class="text-center align-middle">Data Group Name</th>
                                <th colspan="3" class="text-center">Amount Spent</th>
                                <th colspan="3" class="text-center">GMV</th>
                                <th colspan="3" class="text-center">ROAS</th>
                            </tr>
                            <tr class="header-sub">
                                <th class="text-center">This Week</th>
                                <th class="text-center">Previous Week</th>
                                <th class="text-center">Growth</th>
                                <th class="text-center">This Week</th>
                                <th class="text-center">Previous Week</th>
                                <th class="text-center">Growth</th>
                                <th class="text-center">This Week</th>
                                <th class="text-center">Previous Week</th>
                                <th class="text-center">Growth</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div> 
        </div>
    <!-- End Data Table-->
</div> <!-- container -->

<!-- Start Data Table-->
<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <table id="shopee-ads-datatable" class="table table-responsive-sm w-100">
                    <thead>
                        <tr class="header-main">
                            <th colspan="3" class="text-center">Amount Spent</th>
                            <th colspan="3" class="text-center">GMV</th>
                            <th colspan="3" class="text-center">ROAS</th>
                        </tr>
                        <tr class="header-sub">
                            <th class="text-center">This Week</th>
                            <th class="text-center">Previous Week</th>
                            <th class="text-center">Growth</th>
                            <th class="text-center">This Week</th>
                            <th class="text-center">Previous Week</th>
                            <th class="text-center">Growth</th>
                            <th class="text-center">This Week</th>
                            <th class="text-center">Previous Week</th>
                            <th class="text-center">Growth</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div> 
    </div>
<!-- End Data Table-->
</div> <!-- container -->


<!-- Start Data Table-->
<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <table id="shopee-live-datatable" class="table table-responsive-sm w-100">
                    <thead>
                        <tr class="header-main">
                            <th colspan="3" class="text-center">Sales</th>
                            <th colspan="3" class="text-center">Duration</th>
                            <th colspan="3" class="text-center">GMV / Hour </th>
                        </tr>
                        <tr class="header-sub">
                            <th class="text-center">This Week</th>
                            <th class="text-center">Previous Week</th>
                            <th class="text-center">Growth</th>
                            <th class="text-center">This Week</th>
                            <th class="text-center">Previous Week</th>
                            <th class="text-center">Growth</th>
                            <th class="text-center">This Week</th>
                            <th class="text-center">Previous Week</th>
                            <th class="text-center">Growth</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div> 
    </div>
<!-- End Data Table-->
</div> <!-- container -->

@endsection

@section('script')
    @vite(['resources/js/pages/shopee/summary.js'])
@endsection
