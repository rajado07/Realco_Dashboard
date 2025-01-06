import "datatables.net-bs5";
import DataTable from "datatables.net";
import initialize from "../helper/initialize";
import dataTableHelper from "../helper/dataTableHelper";

$.fn.dataTable = DataTable;

const csrfToken = $('meta[name="csrf-token"]').attr('content');

$(document).ready(() => {

    let dataTableInstance;

    function initializeOrUpdateDataTable(startDate = null, endDate = null, brandId = null) {
        const ajaxUrl = '/group/read';
        const ajaxData = {};

        if (startDate && endDate) {
            ajaxData.start_date = startDate;
            ajaxData.end_date = endDate;
        }

        if (brandId) {
            ajaxData.brand_id = brandId;
        }

        if (dataTableInstance) {
            dataTableInstance.clear().destroy(); // Properly destroy the existing DataTable instance
        }

        dataTableInstance = $('#basic-datatable').DataTable({
            ajax: {
                url: ajaxUrl,
                type: 'GET',
                data: ajaxData,
                dataSrc: '',
            },
            stateSave: true,
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            columns: [
                { title: "Type", data: "type" },
                { title: "Brand", data: "brand_id" },
                { title: "Market Place", data: "market_place_id" },
            ],
            columnDefs: [
                {
                    targets: 2,
                    render: function (data, type, row) {
                        return dataTableHelper.translateMarketPlace(data);
                    }
                },
                {
                    targets: 1,
                    render: function (data, type, row) {
                        return dataTableHelper.translateBrand(data);
                    }
                }
            ],
            drawCallback: function () {
                $('#basic-datatable_paginate').addClass('pagination-rounded');
            },
        });

        // Add click event listener for opening and closing details
        $('#basic-datatable tbody').off('click', 'td');
        $('#basic-datatable tbody').on('click', 'td', function () {
            const tr = $(this).closest('tr');
            const row = dataTableInstance.row(tr);

            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
            } else {
                row.child(formatGroup(row.data())).show();
                tr.addClass('shown');
            }
        });
    }

    function formatGroup(group) {
        let groupHtml = `
            <div class="accordion accordion-flush" id="accordion-group-${group.id}">
        `;

        // Jika grup punya sub-group
        if (group.groups && group.groups.length > 0) {
            group.groups.forEach(subGroup => {
                const childrenCount = subGroup.children && subGroup.children.length > 0 ? subGroup.children.length : 0;

                // Jika subGroup masih punya children
                if (childrenCount > 0) {
                    groupHtml += `
                        <div class="accordion-item">
                            <h2 class="accordion-header d-flex justify-content-between align-items-center" id="heading-group-${subGroup.id}">
                                <button 
                                    class="accordion-button collapsed flex-grow-1 text-start" 
                                    type="button"
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#group-section-${subGroup.id}" 
                                    aria-expanded="false" 
                                    aria-controls="group-section-${subGroup.id}"
                                >
                                    <!-- ICON FOLDER (berisi sub) -->
                                    <i class="ri-folders-line me-2"></i> 
                                    ${subGroup.name}
                                    <!-- BADGE JUMLAH SUB GROUP -->
                                    <span class="badge border border-secondary text-secondary ms-2">
                                        <i class="ri-group-line"></i> ${childrenCount} Sub Group
                                    </span>
                                </button>
                                <!-- ACTION DROPDOWN -->
                                <div class="group-actions ms-3">
                                    <div class="dropdown">
                                        <a class="text-reset fs-16 px-2" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="ri-settings-4-line"></i>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-animated">
                                            <li>
                                              <a class="dropdown-item action-edit" data-id="${subGroup.id}" data-bs-toggle="modal" data-bs-target="#editModal">
                                                <i class="ri-settings-3-line"></i> Edit
                                              </a>
                                            </li>
                                            <li>
                                              <a class="dropdown-item action-delete" data-id="${subGroup.id}" href="#">
                                                <i class="ri-delete-bin-2-line"></i> Delete
                                              </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </h2>
                            <div 
                                id="group-section-${subGroup.id}" 
                                class="accordion-collapse collapse"
                                aria-labelledby="heading-group-${subGroup.id}"
                            >
                                <div class="accordion-body">
                                    ${formatChildren(subGroup.children)}
                                </div>
                            </div>
                        </div>
                    `;
                }
                // Jika subGroup tidak punya children
                else {
                    groupHtml += `
                        <div class="accordion-item">
                            <h2 class="accordion-header d-flex justify-content-between align-items-center" id="heading-group-${subGroup.id}">
                                <button 
                                    class="accordion-button collapsed flex-grow-1 text-start" 
                                    type="button"
                                    style="cursor: default;">
                                    <!-- ICON FOLDER (tanpa sub) -->
                                    <i class="ri-file-list-3-line me-2"></i> 
                                    ${subGroup.name}
                                    ${subGroup.keyword
                            ? `
                                                <span class="badge border border-success text-success ms-2">
                                                    <i class="ri-key-line"></i> ${subGroup.keyword}
                                                </span>
                                            `
                            : `
                                                <span class="badge border border-secondary text-secondary ms-2">
                                                    No keyword
                                                </span>
                                            `
                        }
                                </button>
                                <!-- ACTION DROPDOWN -->
                                <div class="group-actions ms-3">
                                    <div class="dropdown">
                                        <a class="text-reset fs-16 px-2" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="ri-settings-4-line"></i>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-animated">
                                            <li>
                                              <a class="dropdown-item action-edit" data-id="${subGroup.id}" data-bs-toggle="modal" data-bs-target="#editModal">
                                                <i class="ri-settings-3-line"></i> Edit
                                              </a>
                                            </li>
                                            <li>
                                              <a class="dropdown-item action-delete" data-id="${subGroup.id}" href="#">
                                                <i class="ri-delete-bin-2-line"></i> Delete
                                              </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </h2>
                        </div>
                    `;
                }
            });
        }
        // Jika grup utama tidak punya sub-group, langsung tampilkan tanpa collapsible
        else {
            groupHtml += `
                <div class="accordion-item">
                    <div class="accordion-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center flex-grow-1">
                            <i class="ri-key-line me-2"></i>
                            <span>${group.name}</span>
                        </div>
                        <div class="d-flex align-items-center ms-auto">
                            ${group.keyword
                    ? `
                                        <span class="badge border border-success text-success ms-2">
                                            <i class="ri-key-line"></i> ${group.keyword}
                                        </span>
                                    `
                    : `
                                        <span class="badge border border-secondary text-secondary ms-2">
                                            No keyword available
                                        </span>
                                    `
                }
                        </div>
                    </div>
                </div>
            `;
        }

        groupHtml += `</div>`;
        return groupHtml;
    }

    function formatChildren(children) {
        // Tetap gunakan ID unik untuk wrapper accordion jika dibutuhkan
        let childrenHtml = `
            <div class="accordion" id="accordion-children">
        `;

        children.forEach((child, index) => {
            // headingId opsional: bisa dihilangkan jika tidak diperlukan
            const headingId = `heading-child-${child.id || index}`;

            childrenHtml += `
                <div class="accordion-item">
                    <h2 class="accordion-header d-flex justify-content-between align-items-center" id="${headingId}">
                        <button
                            class="accordion-button collapsed flex-grow-1 text-start"
                            type="button"
                            style="cursor: default;"
                        >
                            <!-- ICON CHILDREN -->
                            <i class="ri-file-list-3-line me-2"></i>
                            ${child.name}
                            ${child.keyword
                    ? `
                                        <span class="badge border border-success text-success ms-2">
                                            <i class="ri-key-line"></i> ${child.keyword}
                                        </span>
                                    `
                    : `
                                        <span class="badge border border-secondary text-secondary ms-2">
                                            No keyword
                                        </span>
                                    `
                }
                        </button>
                    </h2>
                </div>
            `;
        });

        childrenHtml += `
            </div> <!-- End .accordion -->
        `;

        return childrenHtml;
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
    function deleteAction() {
        $(document).on('click', '.action-delete', function () {
            const id = $(this).data('id');
            deleteRow(id);
        });
    }

    function editAction() {
        $(document).on('click', '.action-edit', function () {
            const rowId = $(this).data('id');

            $('#editRowId').val(rowId);

            Promise.all([
                window.getBrands('editModal'),
                window.getMarketPlaces('editModal'),
                window.getType('editModal')
            ]).then(() => {
                console.log('All data loaded, now calling editRow');
                editRow(rowId);

                $('#editModal').modal('show');
            }).catch((error) => {
                console.error('Failed to fetch data:', error);
            });
        });
    }

    // Action
    async function deleteRow(id) {
        try {
            const response = await fetch(`/group/destroy/${id}`, {
                method: "DELETE",
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken,
                },
            });

            const data = await response.json();
            if (response.ok) {
                initialize.toast(data);
                dataTableInstance?.row($(`a.action-delete[data-id="${id}"]`).closest('tr')).remove().draw();
            } else {
                initialize.toast(data);
            }
        } catch (error) {
            initialize.toast(error);
        }
    }

    function editRow(id) {
        fetch(`/group/edit/${id}`)
            .then(response => response.json())
            .then(data => {
                // Isi data parent ke form
                $('#name').val(data.name || '');
                $('#selectType').val(data.type || '').trigger('change');
                $('#selectMarketPlace').val(data.market_place_id || '').trigger('change');
                $('#selectBrand').val(data.brand_id || '').trigger('change');
                $('#keyword').val(data.keyword || '');

                // Load children into the repeater
                loadFormRepeater('editSubGroup', data.children.map(child => ({
                    id: child.id, // Tambahkan ID child
                    name: child.name,
                    keyword: child.keyword,
                })));
            })
            .catch(error => console.error('Failed to fetch data:', error));
    }

    window.submitAddForm = function () {
        const form = document.getElementById('add');
        const formData = new FormData(form);

        // Ambil data parent
        const parentData = {
            name: formData.get('name'),
            type: formData.get('type'),
            market_place_id: formData.get('market_place_id'),
            brand_id: formData.get('brand_id'),
            keyword: formData.get('keyword'),
        };

        // Ambil data children dari repeater
        const childrenContainer = document.getElementById('addSubGroup');
        const childrenGroups = childrenContainer.querySelectorAll('.input-group');
        const childrenData = [];

        childrenGroups.forEach(group => {
            const childName = group.querySelector('input[name="children[][name]"]')?.value || null;
            const childKeyword = group.querySelector('input[name="children[][keyword]"]')?.value || null;

            if (childName || childKeyword) {
                childrenData.push({
                    name: childName,
                    keyword: childKeyword,
                });
            }
        });

        // Gabungkan data parent dan children
        const payload = {
            ...parentData,
            children: childrenData,
        };

        fetch('/group/store', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken,
            },
            body: JSON.stringify(payload), // Kirim sebagai JSON
        })
            .then(response => response.json())
            .then(data => {
                $('#addNewFormSubmit').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>').prop('disabled', true);
                dataTableInstance.ajax.reload(() => {
                    $('#addNewFormSubmit').html('Save changes').prop('disabled', false);
                    initialize.toast(data);
                    $('#addModal').modal('hide');
                    form.reset();
                });
            })
            .catch(error => {
                console.error('Error:', error);
                initialize.toast({ message: 'An error occurred. Please try again.', type: 'error' });
            });
    };

    window.submitEditForm = function () {
        const form = document.getElementById('edit');
        const formData = new FormData(form);

        // Ambil data parent
        const parentData = {
            id: formData.get('id'),
            name: formData.get('name'),
            type: formData.get('type'),
            market_place_id: formData.get('market_place_id'),
            brand_id: formData.get('brand_id'),
            keyword: formData.get('keyword'),
        };

        // Ambil data children dari repeater
        const childrenContainer = document.getElementById('editSubGroup');
        const childrenGroups = childrenContainer.querySelectorAll('.input-group');
        const childrenData = [];

        childrenGroups.forEach(group => {
            const childId = group.querySelector('input[name="children[][id]"]')?.value || null;
            const childName = group.querySelector('input[name="children[][name]"]')?.value || null;
            const childKeyword = group.querySelector('input[name="children[][keyword]"]')?.value || null;

            if (childName || childKeyword) {
                childrenData.push({
                    id: childId, // ID child untuk update/delete (null jika child baru)
                    name: childName,
                    keyword: childKeyword,
                });
            }
        });

        // Gabungkan data parent dan children
        const payload = {
            ...parentData,
            children: childrenData,
        };

        fetch('/group/update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken,
            },
            body: JSON.stringify(payload), // Kirim sebagai JSON
        })
            .then(response => response.json())
            .then(data => {
                $('#addNewFormSubmit').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>').prop('disabled', true);
                dataTableInstance.ajax.reload(() => {
                    $('#addNewFormSubmit').html('Save changes').prop('disabled', false);
                    initialize.toast(data);
                    $('#editModal').modal('hide');
                    form.reset();
                });
            })
            .catch(error => {
                console.error('Error:', error);
                initialize.toast({ message: 'An error occurred. Please try again.', type: 'error' });
            });
    };

    // Form Repeater

    function loadFormRepeater(containerId, children = []) {
        var container = $(`#${containerId}`);
        container.empty(); // Clear the container first

        if (children.length === 0) {
            addChildField(container); // Add a blank field if no data provided
        } else {
            children.forEach(function (child) {
                addChildField(container, child);
            });
        }

        updateChildButtons(containerId); // Update button appearances
    }

    function addChildField(container, child = { id: null, name: '', keyword: '' }) {
        var inputGroup = $('<div>').addClass('input-group mb-3').hide(); // Start hidden for animation

        // Hidden input for child ID
        var idInput = $('<input>').attr({
            type: 'hidden',
            name: 'children[][id]'
        }).val(child.id || '');

        var nameInput = $('<input>').attr({
            type: 'text',
            name: 'children[][name]',
            placeholder: 'Enter Sub Group Name'
        }).addClass('form-control').val(child.name);

        var keywordInput = $('<input>').attr({
            type: 'text',
            name: 'children[][keyword]',
            placeholder: 'Enter Keyword'
        }).addClass('form-control ms-2').val(child.keyword);

        var button = $('<button>').attr('type', 'button').addClass('btn btn-outline-primary')
            .html('<i class="ri-add-line"></i>').on('click', function () {
                addChildField(container);
                updateChildButtons(container.attr('id'));
            });

        inputGroup.append(idInput).append(nameInput).append(keywordInput).append(button);
        container.append(inputGroup);
        inputGroup.slideDown(); // Animate slide down on add
    }

    function updateChildButtons(containerId) {
        var container = $(`#${containerId}`);
        var groups = container.find('.input-group');

        groups.each(function (index) {
            var button = $(this).find('button');
            button.off('click');

            if (index === groups.length - 1) {
                // Last input field, show add button
                button.addClass('btn-outline-primary').removeClass('btn-outline-danger')
                    .html('<i class="ri-add-line"></i>').on('click', function () {
                        addChildField(container);
                        updateChildButtons(container.attr('id'));
                    });
            } else {
                // Other input fields, show remove button
                button.addClass('btn-outline-danger').removeClass('btn-outline-primary')
                    .html('<i class="ri-close-line"></i>').on('click', function () {
                        $(this).parent().slideUp(400, function () {
                            $(this).remove();
                            updateChildButtons(container.attr('id'));
                        });
                    });
            }
        });
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
        deleteAction();
        editAction();

        loadFormRepeater('addSubGroup');

    }
    init();
});
