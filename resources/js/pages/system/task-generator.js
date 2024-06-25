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
                    url: '/task-generator/read',
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
                    { title: "Brand", data: "brand_id" },
                    { title: "Makrket Place", data: "market_place_id" },
                    { title: "Type", data: "type" },
                    { title: "Link", data: "link" },
                    { title: "Frequency", data: "frequency" },
                    { title: "Run At", data: "run_at" },
                    { title: "Status", data: "status" },
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

    function periodicallyUpdateAllDataTable() {
        setInterval(() => {
            fetch('/task-generator/read')
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
