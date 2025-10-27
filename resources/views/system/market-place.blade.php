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
                        <li class="breadcrumb-item active">Market Places</a></li>
                    </ol>
                </div>
                <h4 class="page-title">Market Places</h4>
            </div>
        </div>
    </div>
    <!-- end page title -->

    <!-- Start Form Modal Edit -->
    <div id="editModal" class="modal fade">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-lg">
                <div class="modal-header">
                    <h4 class="modal-title" id="standard-modalLabel">Edit</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="edit">
                        <input type="hidden" id="editRowId" name="id">
                        <div class="mb-3">
                            <input class="form-control" name="name" placeholder="Enter Name" id="name" required>
                        </div>
                        <div class="mb-3">
                            <textarea class="form-control" name="description" id="editDescription"
                                placeholder="Descriptions" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="submitEditForm()" id="editFormSubmit">Save
                        changes</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Form Modal Edit -->

    <!-- Start Form Modal Add -->
    <div id="addModal" class="modal fade">
        <div class="modal-dialog  modal-dialog-centered">
            <div class="modal-content modal-lg">
                <div class="modal-header">
                    <h4 class="modal-title" id="standard-modalLabel">Add New</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="add">
                        <div class="mb-3">
                            <input class="form-control" name="name" placeholder="Enter Name" required>
                        </div>
                        <div class="mb-3">
                            <textarea class="form-control" name="description" id="addDescription"
                                placeholder="Descriptions" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="submitAddForm()" id="addNewFormSubmit">Save
                        changes</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Form Modal Add -->


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
                            <button type="button" id="addNew" class="btn btn-outline-scondary" data-bs-toggle="modal"
                                data-bs-target="#addModal"><i class="ri-play-list-add-line me-1"></i> Add New</button>
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
    @vite(['resources/js/pages/system/market-place.js'])
    @endsection