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
          url: '/target/brand-target/read',
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
          { title: "Sub Brand Name", data: "sub_brand_name" },
          { title: "Target NMV", data: "target_nmv" },
          { title: "Target Ads To NMV", data: "target_ads_to_nmv" },
          { title: "Composition CPAS", data: "composition_cpas" },
          { title: "Composition Iklanku", data: "composition_iklanku" },
          { title: "Date", data: "data_date" },
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
            targets: 3,
            render: function (data, type, row) {
              return type === 'display' ? dataTableHelper.currency(data) : data;
            }
          },
          {
            targets: 8,
            render: function (data, type, row) {
              return type === 'display' ? dataTableHelper.translateBrand(data) : data;
            }
          },
          {
            targets: 9,
            orderable: false,
            searchable: false,
            render: function (data, type, row) {
              return `
                    <div class="dropdown">
                        <a class="text-reset fs-16 px-1" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ri-settings-4-line"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-animated">
                            <li><a class="dropdown-item action-edit" data-id="${row.id}" data-bs-toggle="modal" data-bs-target="#editModal" href="#"><i class="ri-settings-3-line"></i> Edit</a></li>
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
      fetch('/target/brand-target/read')
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
      // console.log("Data Terpilih: ", selectedData);
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
      // Store the row ID in the hidden input field
      $('#editRowId').val(rowId);
      editRow(rowId);
    });
  }

  // Action
  async function deleteRow(id) {
    try {
      const response = await fetch(`/target/brand-target/destroy/${id}`, {
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
    fetch(`/target/brand-target/edit/${id}`)
      .then(response => response.json())
      .then(data => {
        $('#sub_brand_name').val(data.sub_brand_name || '');
        $('#target_nmv').val(data.target_nmv || '');
        $('#target_ads_to_nmv').val(data.target_ads_to_nmv || '');
        $('#composition_cpas').val(data.composition_cpas || '');
        $('#composition_iklanku').val(data.composition_iklanku || '');
        $('#data_date').val(data.data_date || '');
        $('#brand_id').val(data.brand_id || '');
      })
      .catch(error => console.error('Failed to fetch data:', error));
  }

  window.submitEditForm = function () {
    const form = document.getElementById('edit');
    const formData = new FormData(form);

    fetch('/target/brand-target/update', {
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

  function init() {
    // Initialise External Helper
    dataTableHelper.checkAll();
    dataTableHelper.shiftSelection();
    dataTableHelper.toggleSettings();

    // Data Table
    initializeOrUpdateDataTable();
    periodicallyUpdateAllDataTable()
    getSelectedData();

    // Action
    deleteAction();
    editAction();

  }
  init();

});


