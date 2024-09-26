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
                        <li class="breadcrumb-item active"> Raw Data</a></li>
                    </ol>
                </div>
                <h4 class="page-title">Raw Data</h4>
            </div>
        </div>
    </div>
    <!-- end page title -->

    <!-- Start row Status RawData-->
    <div class="row row-cols-1 row-cols-md-3 row-cols-lg-6">

        <div class="col">
            <div class="card widget-flat">
                <div class="card-body">
                    <div class="float-end mt-1">
                        <i class="ri-flag-line widget-icon text-bg-secondary rounded-circle fs-24"></i>
                    </div>
                    <h5 class="text-muted fw-normal mt-0" title="ready">Ready</h5>
                    <h4 class="my-0 counter" id="ready">0</h4>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card widget-flat">
                <div class="card-body">
                    <div class="float-end mt-1">
                        <i class="ri-database-2-line widget-icon text-bg-success rounded-circle fs-24"></i>
                    </div>
                    <h5 class="text-muted fw-normal mt-0" title="Data Moved">Data Moved</h5>
                    <h4 class="my-0 counter" id="data_moved">0</h4>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card widget-flat">
                <div class="card-body">
                    <div class="float-end mt-1">
                        <i class="ri-database-2-line widget-icon text-bg-info rounded-circle fs-24"></i>
                    </div>
                    <h5 class="text-muted fw-normal mt-0" title="All Skipped">All Skippped</h5>
                    <h4 class="my-0 counter" id="all_skipped">0</h4>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card widget-flat">
                <div class="card-body">
                    <div class="float-end mt-1">
                        <i class="ri-database-2-line widget-icon text-bg-warning rounded-circle fs-24"></i>
                    </div>
                    <h5 class="text-muted fw-normal mt-0" title="Partial Moved">Partial Moved</h5>
                    <h4 class="my-0 counter" id="partial_moved">0</h4>
                </div>
            </div>
        </div>

        <div class="col" style="cursor: pointer;" onclick="window.location.href='/uploads/complete'">
            <div class="card widget-flat">
                <div class="card-body">
                    <div class="float-end mt-1">
                        <i class="ri-database-2-line widget-icon text-bg-danger rounded-circle fs-24"></i>
                    </div>
                    <h5 class="text-muted fw-normal mt-0" title="Partial Failed">Partial Failed</h5>
                    <h4 class="my-0 counter" id="partial_failed">0</h4>
                </div>
            </div>
        </div>

        <div class="col" style="cursor: pointer;" onclick="window.location.href='/uploads/exception'">
            <div class="card widget-flat">
                <div class="card-body">
                    <div class="float-end mt-1">
                        <i class="ri-close-circle-line widget-icon text-bg-danger rounded-circle fs-24"></i>
                    </div>
                    <h5 class="text-muted fw-normal mt-0" title="Failed">All Failed</h5>
                    <h4 class="my-0 counter" id="failed">0</h4>
                </div>
            </div>
        </div>

    </div>
    <!-- end row Status RawData-->

    <!-- Start Data Table-->
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex flex-wrap gap-2 justify-content-between">
                        <div class="d-flex flex-wrap">
                            <!-- Grup Tombol Kiri -->
                            <button type="button" id="exportExcel" onclick="exportPDF()"
                                class="btn btn-outline-seconday"><i class="ri-file-excel-2-line me-1"></i>
                                Export</button>
                            <button type="button" id="exportPDF" onclick="exportDataTableToPDF()"
                                class="btn btn-outline-scondary"><i class="bi bi-file-pdf"></i> PDF</button>
                            <button type="button" id="copyClipboard" onclick="copyClipboard()"
                                class="btn btn-outline-scondary"><i class="ri-file-copy-line me-1"></i> Copy</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="basic-datatable" class="table table-responsive-sm nowrap w-100">
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
    @vite(['resources/js/pages/system/raw-data.js'])
    @endsection