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
                        <li class="breadcrumb-item active">Logs</a></li>
                    </ol>
                </div>
                {{-- <h4 class="page-title">Log</h4> --}}
            </div>
        </div>
    </div>
    <!-- end page title -->

    <!-- Start Data Table-->
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                        <div class="d-flex flex-wrap gap-2">
                            <!-- Grup Tombol Kiri -->
                            <button type="button" class="btn btn-outline">
                                <i class="ri-file-excel-2-line me-1"></i> Export
                            </button>
                            <button type="button" class="btn btn-outline">
                                <i class="bi bi-file-pdf"></i> PDF
                            </button>
                            <button type="button" class="btn btn-outline">
                                <i class="ri-file-copy-line me-1"></i> Copy
                            </button>
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <!-- Grup Tombol Kanan -->
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="autoloadLog" checked>
                                <label class="form-check-label" for="autoloadLog">Auto Load</label>
                            </div>
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
    @vite(['resources/js/pages/system/log.js'])
    @endsection