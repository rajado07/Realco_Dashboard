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
                    url: '/group/read',
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
                    { title: "name", data: "name" },
                    { title: "type", data: "type" },
                    { title: "id_mapping", data: "id_mapping" },
                    { title: "Market Place", data: "market_place_id" },
                    { title: "Brand", data: "brand_id" },
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
                        targets: 4,
                        render: function (data, type, row) {
                            return type === 'display' ? dataTableHelper.shortenText(data) : data;
                        }
                    },
                    {
                        targets: 5,
                        render: function (data, type, row) {
                            return type === 'display' ? dataTableHelper.translateMarketPlace(data) : data;
                        }
                    },
                    {
                        targets: 6,
                        render: function (data, type, row) {
                            return type === 'display' ? dataTableHelper.translateBrand(data) : data;
                        }
                    },
                    {
                        targets: 7, // Updated target index for actions
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
            fetch('/group/read')
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

    window.getBrands = function(modalId) {
        fetch('/brand/read')
        .then(response => response.json())
        .then(data => {
            const $modal = $(`#${modalId} .modal-body`);
            const $select = $modal.find('#selectBrand');
            $select.empty(); 
  
            const defaultOption = new Option('Select Brand', '', true, true);
            defaultOption.disabled = true;
            $select.append(defaultOption);
  
            data.forEach(brand => {
                const option = new Option(brand.name, brand.id);
                $select.append(option); 
            });
            $select.select2({
                dropdownParent: $modal,
            }).trigger('change');
        })
        .catch(error => console.error('Error:', error));
    }

    window.getMarketPlaces = function(modalId) {
        fetch('/market-place/read')
        .then(response => response.json())
        .then(data => {
            const $modal = $(`#${modalId} .modal-body`);
            const $select = $modal.find('#selectMarketPlace');
            $select.empty(); 
  
            const defaultOption = new Option('Select Market Place', '', true, true);
            defaultOption.disabled = true;
            $select.append(defaultOption);
  
            data.forEach(marketPlace => {
                const option = new Option(marketPlace.name, marketPlace.id);
                $select.append(option); 
            });
            $select.select2({
                dropdownParent: $modal,
            }).trigger('change');
        })
        .catch(error => console.error('Error:', error));
    }

    window.getType = function(modalId) {
        fetch('/group/type')
        .then(response => response.json())
        .then(data => {
            const $modal = $(`#${modalId} .modal-body`);
            const $select = $modal.find('#selectType');
            $select.empty(); 
    
            // Create a default option and disable it
            const defaultOption = new Option('Select Type', '', true, true);
            defaultOption.disabled = true;
            $select.append(defaultOption);
    
            // Iterate over the data array and append each type as an option
            data.forEach(type => {
                const option = new Option(type, type); // Here we assume each type is a string
                $select.append(option); 
            });
    
            // Initialize select2 for the dropdown and trigger change event
            $select.select2({
                dropdownParent: $modal,
            }).trigger('change');
        })
        .catch(error => console.error('Error:', error));
    }

    window.getGroupByType = function(modalId, type = null) {
        let url = '/group/bytype';
        if (type) {
            url += '/' + type;
        }
    
        fetch(url)
        .then(response => response.json())
        .then(data => {
            const $modal = $(`#${modalId} .modal-body`);
            const $select = $modal.find('#selectIdMapping');
            $select.empty(); 
    
            if (data.error) {
                // Display the error message
                const errorOption = new Option(data.error, '', true, true);
                errorOption.disabled = true;
                $select.append(errorOption);
            } else {
                // Create a default option and disable it
                const defaultOption = new Option('Select Type', '');
                defaultOption.disabled = true;
                $select.append(defaultOption);
    
                // Filter and append options where data_group_id is null
                data.filter(item => item.data_group_id === null).forEach(item => {
                    const option = new Option(`${item.name} (${item.id_mapping})`, item.id_mapping);
                    $select.append(option); 
                });
            }
    
            // Initialize select2 for the dropdown with multi-select enabled and trigger change event
            $select.select2({
                multiple: true,
                // closeOnSelect: false,
                dropdownParent: $modal,
            }).trigger('change');
        })
        .catch(error => console.error('Error:', error));
    }    

    function handleSelectTypeChange() {
        $(document).on('change', '#selectType', function() {
            const selectedType = $(this).val();
            const modalId = $(this).closest('.modal').attr('id'); // Assuming the select is inside a modal
            getGroupByType(modalId, selectedType);
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
        periodicallyUpdateAllDataTable();
        getSelectedData();

         // Initialize event listener for 'selectType' dropdown change
        handleSelectTypeChange();

    }
    init();
});
