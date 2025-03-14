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
                        <li class="breadcrumb-item active">Input Data</a></li>
                    </ol>
                </div>
                <h4 class="page-title">Input Data</h4>
            </div>
        </div>
    </div>
    <!-- end page title -->

    <!-- Start Form Modal Import -->
    <div id="importModal" class="modal fade">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="standard-modalLabel">Import</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="import" method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <select id="type" name="type" class="form-control select2"
                                data-toggle="select2">
                                <option disabled selected>Select Type</option>
                                <option value="odoo_target">Odoo Target</option>
                                <option value="brand_target">Brand Target</option>
                                <option value="fs_boosting">FS Boosting</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <input type="file" name="file" id="example-fileinput" class="form-control"
                                onchange="analyzeFile(this)">
                        </div>
                        <div id="allert-placeholder" class="mb-3"></div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <i class="ri-file-list-3-line ri-xl text-secondary"></i>
                                <div class="ms-2">
                                    <span class="fw-bold">Total Rows:</span>
                                    <span id="total-rows" class="badge bg-secondary rounded-pill ms-1">0</span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="ri-table-line ri-xl text-secondary"></i>
                                <div class="ms-2">
                                    <span class="fw-bold">Total Worksheets:</span>
                                    <span id="total-worksheets" class="badge bg-secondary rounded-pill ms-1">0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="submitImportForm()" id="importFormSubmit"
                            disabled>Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End Form Modal Import -->


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
                            <button type="button" id="import" class="btn btn-outline-seconday" data-bs-toggle="modal"
                                data-bs-target="#importModal"><i class="bi bi-upload"></i> Import</button>
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
    @vite(['resources/js/pages/system/input-data.js'])
    @endsection