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
                    url: '/task/read',
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
                    { title: "Created At", data: "created_at" },
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

    function periodicallyUpdateAllDataTable() {
        setInterval(() => {
            fetch('/task/read')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(newData => {
                    let isChanged = false;
                    const newDataIds = newData.map(item => item.id);

                    // Fade out rows that no longer exist in the new data
                    dataTableInstance.rows().every(function () {
                        const oldRowId = this.data().id;
                        if (!newDataIds.includes(oldRowId)) {
                            $(this.node()).fadeOut(500, () => {
                                this.remove();
                                dataTableInstance.draw(false);
                            });
                            isChanged = true;
                        }
                    });

                    // Update or add rows with blink effect
                    newData.forEach(newRow => {
                        const currentRow = dataTableInstance.row((idx, data) => data.id === newRow.id);
                        if (currentRow.length) {
                            if (JSON.stringify(currentRow.data()) !== JSON.stringify(newRow)) {
                                currentRow.data(newRow).invalidate(); // Prepare data for redraw
                                const node = $(currentRow.node());
                                // Start blink animation
                                node.fadeTo(100, 0.5).fadeTo(100, 1.0).fadeTo(100, 0.5).fadeTo(100, 1.0);
                                isChanged = true;
                            }
                        } else {
                            // Fade in new rows
                            const node = $(dataTableInstance.row.add(newRow).draw(false).node());
                            node.hide().fadeIn(1000);
                            isChanged = true;
                        }
                    });

                    // Redraw the table once if there are changes
                    if (isChanged) {
                        dataTableInstance.draw(false);
                    }
                })
                .catch(error => {
                    console.error('Error updating data table:', error);
                });
        }, 3000);
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


    let previousCountStatusData = {};
    function getTaskStatusCount() {
        fetch('/task/status-count')
            .then(response => response.json())
            .then(newData => {
                updateCounterIfChanged('#ready', newData[1] || 0);
                updateCounterIfChanged('#wait_for_running', newData[2] || 0);
                updateCounterIfChanged('#running', newData[3] || 0);
                updateCounterIfChanged('#exception', newData[4] || 0);
                updateCounterIfChanged('#completed', newData['completed'] || 0);
                updateCounterIfChanged('#failed', newData['failed'] || 0);
            })
            .catch(error => console.error('Error updating status:', error));
    }

    // Function to check if the value has changed and update the counter
    function updateCounterIfChanged(selector, newValue) {
        const currentValue = previousCountStatusData[selector] || 0;

        if (currentValue !== newValue) {
            initialize.animateCounter($(selector), newValue);
            previousCountStatusData[selector] = newValue; // Store the new value
        }
    }

    function periodicallyUpdateTaskStatusCount() {
        getTaskStatusCount(); // Memperbarui status segera
        setInterval(getTaskStatusCount, 3000); // Memperbarui status setiap 3 detik
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

    function markedAsCompleted() {
        $(document).on('click', '#markedAsCompleted', function () {
            updateStatus(selectedData, 'marked_as_completed', 5);
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
        periodicallyUpdateAllDataTable();
        getSelectedData();
        periodicallyUpdateTaskStatusCount();

        // Action
        runSelected();
        archivedSelected();
        markedAsCompleted();

    }
    init();
});
