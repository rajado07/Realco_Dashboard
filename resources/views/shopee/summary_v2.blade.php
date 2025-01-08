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
                <div id="reportrange" class="form-control d-flex justify-content-between align-items-center"
                    data-toggle="date-picker-range" data-target-display="#selectedDate" data-cancel-class="btn-light">
                    <i class="ri-calendar-2-line"></i>
                    <span id="selectedDate">Select date</span>
                    <i class="ri-arrow-down-s-line"></i>
                </div>
            </div>
        </div>
        <div class="col-auto" id="datePickerContainer" style="display: none;">
            <!-- Date Picker -->
            <div class="form-group">
                <div id="reportrange" class="form-control d-flex justify-content-between align-items-center"
                    data-toggle="date-picker-range" data-target-display="#selectedDate2" data-cancel-class="btn-light">
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

        <div class="col-auto">
            <div class="card">
                <div class="card-body">
                    <table id="shopee-datatable" class="table table-sm table-responsive-sm w-100">
                        <thead>
                            <tr class="header-main">
                                <th class="text-left">Brand</th>
                                <th class="text-center">Product Views</th>
                                <th class="text-center">Conversion</th>
                                <th class="text-center">GMV</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- <tr>
                                <td class="text-center-left">Appletox</td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center align-items-center">
                                        <div class="current-value fs-4 me-2">1,200</div>
                                        <div class="d-flex align-items-center">
                                            <div class="previous-value me-2 text-muted fs-6">1,000</div>
                                            <div class="change-value d-flex align-items-center">
                                                <i class="bi bi-arrow-up-circle-fill text-success me-1"></i>
                                                <span class="text-success">+20%</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center align-items-center">
                                        <div class="current-value fs-4 me-2">5 %</div>
                                        <div class="d-flex align-items-center">
                                            <div class="previous-value me-2 text-muted fs-6">10 %</div>
                                            <div class="change-value d-flex align-items-center">
                                                <i class="bi bi-arrow-down-circle-fill text-danger me-1"></i>
                                                <span class="text-danger">-20%</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">$10,000</td>
                            </tr>
                            <tr>
                                <td class="text-left">Forever Bright</td>
                                <td class="text-center">950</td>
                                <td class="text-center">4.5%</td>
                                <td class="text-center">$8,500</td>
                            </tr>
                            <!-- Tambahkan baris data lainnya sesuai kebutuhan --> --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- End Data Table-->

    </div> <!-- container -->

    @endsection

    @section('script')
    @vite(['resources/js/pages/shopee/summary_v2.js'])
    @endsection