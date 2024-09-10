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

    function getTaskStatusCount() {
        fetch('/task/status-count')
            .then(response => response.json())
            .then(newData => {
                // Gunakan animateCounter dengan jQuery untuk memperbarui nilai elemen
                initialize.animateCounter($('#ready'), newData[1] || 0);
                initialize.animateCounter($('#wait_for_running'), newData[2] || 0);
                initialize.animateCounter($('#running'), newData[3] || 0);
                initialize.animateCounter($('#exception'), newData[4] || 0);
                initialize.animateCounter($('#completed'), newData[5] || 0);
            })
            .catch(error => console.error('Error updating status:', error));
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

    window.showExceptionModal = function (rowId) {
        const url = `/task/exception-details`;

        // Prepare the request data
        const formData = new FormData();
        formData.append('id', rowId);

        // Show loading spinner and hide content sections
        document.getElementById('loadingSpinner').style.display = 'block';
        document.getElementById('imageSection').style.display = 'none';
        document.getElementById('messageSection').style.display = 'none';

        // Fetch the data from the backend using POST method
        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-Token': csrfToken,  // Assuming csrfToken is available globally
            },
        })
            .then(response => response.json())
            .then(data => {
                const exceptionImage = document.getElementById('exceptionImage');
                const exceptionDetails = document.getElementById('exceptionDetails');

                // Set the image source and exception details
                exceptionImage.src = data.imageUrl;
                exceptionDetails.textContent = data.exceptionMessage;

                // Add an error handler for the image in case it fails to load
                exceptionImage.onerror = function () {
                    exceptionImage.src = 'https://via.placeholder.com/400x300?text=Image+Not+Found';
                    exceptionImage.alt = 'Image not found';
                };

                // Hide loading spinner and show content sections
                document.getElementById('loadingSpinner').style.display = 'none';
                document.getElementById('imageSection').style.display = 'block';
                document.getElementById('messageSection').style.display = 'block';

                // Show the modal
                var exceptionModal = new bootstrap.Modal(document.getElementById('exceptionModal'));
                exceptionModal.show();
            })
            .catch(error => {
                console.error('Error fetching exception details:', error);
                // Optionally, display an error message in the modal or a toast notification
                document.getElementById('exceptionDetails').textContent = 'Failed to load exception details. Please try again.';
                // Hide the spinner and display the error message
                document.getElementById('loadingSpinner').style.display = 'none';
                document.getElementById('messageSection').style.display = 'block';
            });
    };

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
        getTaskStatusCount();

        // Action
        runSelected();
        archivedSelected();

    }
    init();
});
