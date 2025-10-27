import "datatables.net-bs5";
import DataTable from "datatables.net";
import initialize from "../helper/initialize";
import dataTableHelper from "../helper/dataTableHelper";
import 'daterangepicker/daterangepicker.js';

$.fn.dataTable = DataTable;

const csrfToken = $('meta[name="csrf-token"]').attr('content');

$(document).ready(() => {
    // Data Table
    let dataTableInstance;
    function initializeOrUpdateDataTable() {
        if (!dataTableInstance) {
            dataTableInstance = $('#basic-datatable').DataTable({
                ajax: {
                    url: '/task/read/completed',
                    type: 'GET',
                    dataSrc: '',
                },
                stateSave: true,
                pageLength: 25,
                deferLoading: true,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                columns: [
                    { title: '<input type="checkbox" id="checkAll"/>', defaultContent: '<input type="checkbox" class="rowCheckbox"/>' },
                    { title: "ID", data: "id" },
                    { title: "Type", data: "type" },
                    { title: "Link", data: "link" },
                    { title: "Scheduled To Run", data: "scheduled_to_run" },
                    { title: "Success At", data: "updated_at" },
                    { title: "Market Place", data: "market_place_id" },
                    { title: "Brand", data: "brand_id" },
                    { title: "Status", data: "status" },
                ],
                columnDefs: [
                    {
                        targets: 0,
                        searchable: false,
                        orderable: false,
                        width: "15px",
                        className: 'dt-body-center',
                        render: function (data, type, row) {
                            return '<input name="checklist[]" type="checkbox" class="rowCheckbox" value="' + row.id + '"/>';
                        }
                    },
                    {
                        targets: 3,
                        render: function (data, type, row) {
                            return dataTableHelper.shortenText(data);
                        }
                    },
                    {
                        targets: 6,
                        render: function (data, type, row) {
                            return dataTableHelper.translateMarketPlace(data);
                        }
                    },
                    {
                        targets: 7,
                        render: function (data, type, row) {
                            return dataTableHelper.translateBrand(data);
                        }
                    },
                    {
                        targets: 8, // Target kolom status
                        render: function (data, type, row) {
                            return dataTableHelper.translateStatusTask(data, row.id);
                        }
                    },
                    {
                        targets: [5, 4],
                        render: function (data, type, row) {
                            return dataTableHelper.formatSchedule(data);
                        }
                    }
                ],
                language: {
                    loadingRecords: ` <div class="spinner-border avatar-sm text-secondary m-2" role="status"></div>`,
                    paginate: {
                        previous: "<i class='ri-arrow-left-s-line'></i>",
                        next: "<i class='ri-arrow-right-s-line'></i>"
                    }
                },
                drawCallback: function () {
                    $('#basic-datatable_paginate').addClass('pagination-rounded');
                    initialize.toolTip();
                },
            });
        } else {
            dataTableInstance.ajax.reload();
        }
    }

    let selectedData = [];
    function getSelectedData() {
        $(document).on('change', '.rowCheckbox, #checkAll', function () {
            selectedData = [];
            if ($('#checkAll').is(':checked')) {
                $('.rowCheckbox').each(function () {
                    $(this).prop('checked', true);
                    selectedData.push($(this).val());
                });
            } else {
                $('.rowCheckbox:checked').each(function () {
                    selectedData.push($(this).val());
                });
            }
        });
    }

    function runSelected() {
        $(document).on('click', '#runSelected', function () {
            updateStatus(selectedData, 'start', 2);
        });
    }

    function archivedSelected() {
        $(document).on('click', '#archivedSelected', function () {
            updateStatus(selectedData, 'archived', 100);
        });
    }

    function markedAsQueue() {
        $(document).on('click', '#markedAsQueue', function () {
            updateStatus(selectedData, 'marked_as_queue', 10);
        });
    }

    function updateStatus(ids, type, status) {
        fetch(`/task/update/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                ids: ids,
                type: type,
                status: status
            })
        })
            .then(response => response.json())
            .then(data => {
                dataTableInstance.ajax.reload(() => {
                    initialize.toast(data);
                });

            })
            .catch(error => console.error('Failed to fetch data:', error));

    }

    function init() {
        // Initialise External Helper
        initialize.dateTimePicker();
        initialize.toolTip();
        dataTableHelper.checkAll();
        dataTableHelper.shiftSelection();
        dataTableHelper.toggleSettings();

        // Data Table
        initializeOrUpdateDataTable();
        getSelectedData();

        // Action
        runSelected();
        archivedSelected();
        markedAsQueue();

    }
    init();
});
