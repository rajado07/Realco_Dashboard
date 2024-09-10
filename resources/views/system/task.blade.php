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
                        <li class="breadcrumb-item active">Task List</a></li>
                    </ol>
                </div>
                <h4 class="page-title">Task List</h4>
            </div>
        </div>
    </div>
    <!-- end page title -->

    <!-- Start row Status RawData-->
    <div class="row row-cols-1 row-cols-xxl-5 row-cols-lg-3 row-cols-md-2">

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
                        <i class="ri-timer-line widget-icon text-bg-info rounded-circle fs-24"></i>
                    </div>
                    <h5 class="text-muted fw-normal mt-0" title="Wait For Running">Wait For Running</h5>
                    <h4 class="my-0 counter" id="wait_for_running">0</h4>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card widget-flat">
                <div class="card-body">
                    <div class="float-end mt-1">
                        <i class="ri-loader-4-line widget-icon text-bg-success rounded-circle fs-24"></i>
                    </div>
                    <h5 class="text-muted fw-normal mt-0" title="Running">Running</h5>
                    <h4 class="my-0 counter" id="running">0</h4>
                </div>
            </div>
        </div>

        <div class="col" style="cursor: pointer;" onclick="window.location.href='/uploads/complete'">
            <div class="card widget-flat">
                <div class="card-body">
                    <div class="float-end mt-1">
                        <i class="ri-error-warning-line widget-icon text-bg-warning rounded-circle fs-24"></i>
                    </div>
                    <h5 class="text-muted fw-normal mt-0" title="Exception">Exception</h5>
                    <h4 class="my-0 counter" id="exception">0</h4>
                </div>
            </div>
        </div>

        <div class="col" style="cursor: pointer;" onclick="window.location.href='/uploads/exception'">
            <div class="card widget-flat">
                <div class="card-body">
                    <div class="float-end mt-1">
                        <i class="ri-check-line text-success widget-icon text-bg-light rounded-circle fs-24"></i>
                    </div>
                    <h5 class="text-muted fw-normal mt-0" title="Completed">Completed</h5>
                    <h4 class="my-0 counter" id="completed">0</h4>
                </div>
            </div>
        </div>

    </div>
    <!-- end row Status RawData-->

    <!-- Start Modal Exception -->
    <div id="exceptionModal" class="modal fade">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h4 class="modal-title" id="exception-modalLabel">
                        <i class="ri-error-warning-line me-2"></i> Exception Details
                    </h4>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{-- Loading Spinner --}}
                    <div id="loadingSpinner" class="text-center mb-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    {{-- Image section --}}
                    <div id="imageSection" class="text-center mb-4" style="display: none;">
                        <img id="exceptionImage" src="" alt="Exception Screenshot" class="img-fluid rounded shadow-sm"
                            style="max-width: 100%; max-height: 400px;">
                    </div>
                    {{-- Message section --}}
                    <div id="messageSection" class="mb-3" style="display: none;">
                        <h5 class="fw-bold"><i class="ri-information-line me-2"></i> Message:</h5>
                        <p id="exceptionDetails" class="form-control-plaintext border p-3 rounded bg-light"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-dark" data-bs-dismiss="modal">
                        <i class="ri-close-line me-2"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Modal Exception -->

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
                        <div class="d-flex flex-wrap">
                            <!-- Grup Tombol Kanan -->
                            <button type="button" id="settings" class="btn btn-outline-seconday"
                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                style="display: none;"><i class="ri-settings-5-line"></i> Settings</button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="#" id="runSelected">Run Selected Task</a>
                                {{-- <a class="dropdown-item" href="#" id="deleteSelected">Delete Selected Task</a> --}}
                                <a class="dropdown-item" href="#" id="archivedSelected">Archived Selected Task</a>
                                {{-- <a class="dropdown-item" href="#" id="editSelected">Edit Selected Task</a> --}}
                            </div>
                            {{-- <button type="button" id="import" class="btn btn-outline-seconday"
                                data-bs-toggle="modal" data-bs-target="#importModal"><i class="bi bi-upload"></i>
                                Import</button>
                            <button type="button" id="addNew" onclick="getAccounts('addModal');"
                                class="btn btn-outline-scondary" data-bs-toggle="modal" data-bs-target="#addModal"><i
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
    @vite(['resources/js/pages/system/task.js'])
    @endsection