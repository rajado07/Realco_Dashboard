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
                    { title: "Type", data: "type" },
                    { title: "Link", data: "link" },
                    { title: "Frequency", data: "frequency" },
                    { title: "Run At", data: "run_at" },
                    { title: "Market Place", data: "market_place_id" },
                    { title: "Brand", data: "brand_id" },
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
                        targets: 8,
                        render: function (data, type, row) {
                            return dataTableHelper.translateStatusTaskGenerator(data);
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
                                        <li><a class="dropdown-item action-generate-task" data-id="${row.id}"><i class="ri-code-view"></i> Generate</a></li>
                                        <li><a class="dropdown-item action-edit" data-id="${row.id}"><i class="ri-settings-3-line"></i> Edit</a></li>
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

    // Click Action

    function generateAction() {
        $(document).on('click', '.action-generate-task', function () {
            const id = $(this).data('id');
            $('#taskGeneratorRowId').val(id);
            $('#taskGeneratorModal').modal('show');
        });
    }

    function deleteAction() {
        $(document).on('click', '.action-delete', function () {
            const id = $(this).data('id');
            deleteRow(id);
        });
    }

    function editAction() {
        $(document).on('click', '.action-edit', function () {
            const rowId = $(this).data('id');

            // Store the row ID in the hidden input field
            $('#editRowId').val(rowId);

            // Call all necessary functions and wait for them to complete
            Promise.all([
                window.getScripts('editModal'),
                window.getMarketPlaces('editModal'),
                window.getBrands('editModal')
            ]).then(() => {
                // console.log('All data loaded, now calling editRow');
                editRow(rowId);

                // After all data is loaded and editRow is done, show the modal
                $('#editModal').modal('show');
            }).catch((error) => {
                console.error('Failed to load data:', error);
                // Optionally handle the error (e.g., show a notification)
            });
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

    // Action
    async function deleteRow(id) {
        try {
            const response = await fetch(`/task-generator/destroy/${id}`, {
                method: "DELETE",
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken,
                },
            });

            const data = await response.json();
            if (response.ok) {
                dataTableInstance.ajax.reload(() => {
                    initialize.toast(data);
                });

            } else {
                initialize.toast(data);
            }
        } catch (error) {
            initialize.toast(error);
        }
    }

    function editRow(id) {
        fetch(`/task-generator/edit/${id}`)
            .then(response => response.json())
            .then(data => {
                $('#selectBrand').val(data.brand_id || '').trigger('change');
                $('#selectType').val(data.type || '').trigger('change');
                $('#selectMarketPlace').val(data.market_place_id || '').trigger('change');
                $('#editFrequency').val(data.frequency || '').trigger('change');
                $('#editLink').val(data.link || '');
                $('#editRunAt').val(data.run_at || '');
                ;
            })
            .catch(error => console.error('Failed to fetch data:', error));
    }

    function updateStatus(ids, type, status) {
        fetch(`/uploads/task/update/status`, {
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

    window.submitAddForm = function () {
        const form = document.getElementById('add');
        const formData = new FormData(form);

        fetch('/task-generator/store', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-Token': csrfToken,
            },
        })
            .then(response => response.json())
            .then(data => {
                $('#addNewFormSubmit').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true">').prop('disabled', true);
                // Reload dataTable dan tunggu hingga selesai
                dataTableInstance.ajax.reload(() => {
                    $('#addNewFormSubmit').html('Save changes').prop('disabled', false);
                    initialize.toast(data);
                    $('#addModal').modal('hide');
                    form.reset();
                });
            })
            .catch(error => {
                console.error('Error:', error);
                initialize.toast(data);
            });
    }

    window.submitEditForm = function () {
        const form = document.getElementById('edit');
        const formData = new FormData(form);

        fetch('/task-generator/update', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-Token': csrfToken,
            },
        })
            .then(response => response.json())
            .then(data => {
                $('#editFormSubmit').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true">').prop('disabled', true);
                // Reload dataTable dan tunggu hingga selesai
                dataTableInstance.ajax.reload(() => {
                    $('#editFormSubmit').html('Save changes').prop('disabled', false);
                    initialize.toast(data);
                    $('#editModal').modal('hide');
                    form.reset();
                });
            })
            .catch(error => {
                console.error('Error:', error);
                initialize.toast(data);
            });
    }

    window.submitTaskGeneratorForm = async function () {
        const form = document.getElementById('taskGenerator');
        const formData = new FormData(form);
        const importButton = document.getElementById('taskGeneratorFormSubmit');

        // console.log("Isi FormData:");
        // formData.forEach((value, key) => {
        //     console.log(`${key}:`, value);
        // });
    
        // Ubah tombol menjadi loading dan nonaktifkan
        importButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Generating...';
        importButton.disabled = true;
    
        try {
          const response = await fetch('/task-generator/generate', {
            method: 'POST',
            headers: {
              'X-CSRF-Token': csrfToken, // CSRF token Laravel
            },
            body: formData
          });
    
          const data = await response.json();
          initialize.toast(data);
          $('#taskGeneratorModal').modal('hide');
        } catch (error) {
          console.error('Error:', error);
          initialize.toast(error);
        } finally {
          importButton.innerHTML = 'Generate Task';
          importButton.disabled = false;
        }
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

        // Action
        generateAction();
        deleteAction();
        editAction();
        runSelected();
        archivedSelected();

    }
    init();
});
