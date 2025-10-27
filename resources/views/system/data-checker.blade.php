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
                        <li class="breadcrumb-item"><a href="javascript: void(0);">System</a></li>
                        <li class="breadcrumb-item active">Data Checker</a></li>
                    </ol>
                </div>
                <h4 class="page-title">Data Checker</h4>
            </div>
        </div>
    </div>
    <!-- end page title -->

    <div class="row justify-content-end mb-2">
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
        <div class="col-auto">
            <!-- Button -->
            <button type="button" id="btn-refresh" class="btn btn-primary"><i class="ri-refresh-line"></i></button>
        </div>
    </div>

    <!-- Start Data Table-->
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex flex-wrap gap-2 justify-content-between">
                        <div class="d-flex flex-wrap">
                            <!-- Grup Tombol Kiri -->
                            <button type="button" class="btn btn-outline-seconday" id="exportButton"><i
                                    class="ri-file-excel-2-line me-1"></i> Export</button>
                            <button type="button" class="btn btn-outline-scondary"><i class="bi bi-file-pdf"></i>
                                PDF</button>
                            <button type="button" class="btn btn-outline-scondary"><i
                                    class="ri-file-copy-line me-1"></i> Copy</button>
                        </div>
                        <div class="d-flex flex-wrap">
                            <!-- Grup Tombol Kanan -->
                            <button type="button" id="settings" class="btn btn-outline-seconday"
                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                style="display: none;"><i class="ri-settings-5-line"></i> Settings</button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" id="runSelected" href="#">Run Selected</a>
                                <a class="dropdown-item" id="stopSelected" href="#">Stop Selected</a>
                                {{-- <a class="dropdown-item" id="deleteSelected" href="#">Delete Selected Task</a> --}}
                                <a class="dropdown-item" id="forceStopSelected" href="#">Force Stop Selected Task</a>
                            </div>
                            {{-- <button type="button" id="addNew" class="btn btn-outline-scondary"
                                data-bs-toggle="modal" data-bs-target="#addModal"><i
                                    class="ri-play-list-add-line me-1"></i> Add New</button> --}}
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="basic-datatable" class="table table-hover table-responsive-sm nowrap w-100">
                            <thead>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Data Table-->


    </div> <!-- container -->
    @endsection

    @section('script')
    @vite(['resources/js/pages/system/data-checker.js'])
    @endsection