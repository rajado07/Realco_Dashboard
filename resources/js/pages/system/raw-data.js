import "datatables.net-bs5";
import DataTable from "datatables.net";
import initialize from "../helper/initialize";
import dataTableHelper from "../helper/dataTableHelper";

$.fn.dataTable = DataTable;

const csrfToken = $('meta[name="csrf-token"]').attr('content');

$(document).ready(() => {
    // Data Table
    let dataTableInstance;
    function initializeOrUpdateDataTable() {
        if (!dataTableInstance) {
            dataTableInstance = $('#basic-datatable').DataTable({
                ajax: {
                    url: '/raw-data/read',
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
                    { title: "Retrieved At", data: "retrieved_at" },
                    { title: "Data Date", data: "data_date" },
                    { title: "File Name", data: "file_name" },
                    { title: "Market Place", data: "market_place_id" },
                    { title: "Brand", data: "brand_id" },
                    { title: "status", data: "status" },
                    { title: "Action", defaultContent: '' }
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
                        targets: 5,
                        render: function (data, type, row) {
                            return type === 'display' ? dataTableHelper.shortenText(data) : data;
                        }
                    },
                    {
                        targets: 8,
                        render: function (data, type, row) {
                            return type === 'display' ? dataTableHelper.translateStatusRawData(data) : data;
                        }
                    },
                    {
                        targets: 9, // Updated target index for actions
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row) {
                            return `
                                <div class="dropdown">
                                    <a class="text-reset fs-16 px-1" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="ri-settings-4-line"></i>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-animated">
                                        <li><a class="dropdown-item action-edit" data-id="${row.id}" data-bs-toggle="modal" data-bs-target="#editModal" href="#" onclick="getAccounts('editModal');"><i class="ri-settings-3-line"></i> Edit</a></li>
                                        <li><a class="dropdown-item action-delete" data-id="${row.id}" href="#"><i class="ri-delete-bin-2-line"></i> Delete</a></li>
                                    </ul>
                                </div>
                            `;
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

    // Function to format the child row
    function format(d) {
        let dataTable = `
        <div class="accordion accordion-flush" id="accordion-${d.id}">
    `;

        // Section for Data
        if (d.data) {
            let jsonData = JSON.parse(d.data);
            dataTable += `
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading-data-${d.id}">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#data-section-${d.id}" aria-expanded="false" aria-controls="data-section-${d.id}">
                        Data
                    </button>
                </h2>
                <div id="data-section-${d.id}" class="accordion-collapse collapse" aria-labelledby="heading-data-${d.id}">
                    <div class="accordion-body">
                        ${createTable(jsonData)}
                    </div>
                </div>
            </div>
        `;
        }

        // Section for Message
        if (d.message) {
            let messageData = JSON.parse(d.message);
            dataTable += `
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading-message-${d.id}">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#message-section-${d.id}" aria-expanded="false" aria-controls="message-section-${d.id}">
                        Message
                    </button>
                </h2>
                <div id="message-section-${d.id}" class="accordion-collapse collapse" aria-labelledby="heading-message-${d.id}">
                    <div class="accordion-body">
                        <p>Total Entries: ${messageData.total_entries}</p>
                        <p>Successful: ${messageData.successful}</p>
                        <p>Skipped: ${messageData.skipped}</p>
                        <p>Failed: ${messageData.failed}</p>
                        ${messageData.skipped_details && messageData.skipped_details.length > 0 ? `
                            <div class="accordion accordion-flush" id="accordion-skipped-${d.id}">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading-skipped-${d.id}">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#skipped-details-section-${d.id}" aria-expanded="false" aria-controls="skipped-details-section-${d.id}">
                                            Skipped Details
                                        </button>
                                    </h2>
                                    <div id="skipped-details-section-${d.id}" class="accordion-collapse collapse" aria-labelledby="heading-skipped-${d.id}">
                                        <div class="accordion-body">
                                            ${createTable(messageData.skipped_details)}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ` : ''}
                        ${messageData.failed_details && messageData.failed_details.length > 0 ? `
                            <div class="accordion accordion-flush" id="accordion-error-${d.id}">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading-error-${d.id}">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#failed-details-section-${d.id}" aria-expanded="false" aria-controls="error-details-section-${d.id}">
                                            Failed Details
                                        </button>
                                    </h2>
                                    <div id="failed-details-section-${d.id}" class="accordion-collapse collapse" aria-labelledby="heading-error-${d.id}">
                                        <div class="accordion-body">
                                            ${createTable(messageData.failed_details)}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ` : ''}
                        ${messageData.errors && messageData.errors.length > 0 ? `
                            <div class="accordion accordion-flush" id="accordion-error-${d.id}">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading-error-${d.id}">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#error-details-section-${d.id}" aria-expanded="false" aria-controls="error-details-section-${d.id}">
                                            Error Reason
                                        </button>
                                    </h2>
                                    <div id="error-details-section-${d.id}" class="accordion-collapse collapse" aria-labelledby="heading-error-${d.id}">
                                        <div class="accordion-body">
                                            ${createTable(messageData.errors)}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ` : ''}
                    </div>
                </div>
            </div>
        `;
        }

        dataTable += `</div>`;
        return dataTable;
    }
    
    function createTable(data) {
        let table = '<table class="table table-striped table-sm"><thead><tr>';

        // Add table headers based on the keys of the first object
        Object.keys(data[0]).forEach(function (key) {
            table += '<th>' + key + '</th>';
        });

        table += '</tr></thead><tbody>';

        // Add table rows based on the values of each object
        data.forEach(function (item) {
            table += '<tr>';
            Object.values(item).forEach(function (value) {
                table += '<td>' + value + '</td>';
            });
            table += '</tr>';
        });

        table += '</tbody></table>';
        return table;
    }

    function periodicallyUpdateAllDataTable() {
        setInterval(() => {
            fetch('/raw-data/read')
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

    // Add event listener for opening and closing details on row click
    $('#basic-datatable tbody').on('click', 'tr', function () {
        let row = dataTableInstance.row(this);

        if (row.child.isShown()) {
            row.child.hide();
            $(this).removeClass('shown');
        } else {
            row.child(format(row.data())).show();
            $(this).addClass('shown');
        }
    });

    // Add event listener for arrow icon toggle
    $(document).on('click', '.toggle-section', function () {
        let icon = $(this).find('.arrow-icon');
        icon.toggleClass('bi-chevron-right bi-chevron-down');
    });

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

    }
    init();
});
