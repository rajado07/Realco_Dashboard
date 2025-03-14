import "datatables.net-bs5";
import DataTable from "datatables.net";
import initialize from "../helper/initialize";
import dataTableHelper from "../helper/dataTableHelper";
import * as XLSX from 'xlsx';


$.fn.dataTable = DataTable;

const csrfToken = $('meta[name="csrf-token"]').attr('content');

$(document).ready(() => {
  // Data Table
  let dataTableInstance;
  function initializeOrUpdateDataTable() {
    if (!dataTableInstance) {
      dataTableInstance = $('#basic-datatable').DataTable({
        ajax: {
          url: '/import/read',
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
          { title: "File Name", data: "file_name" },
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
            targets: 4,
            render: function (data, type, row) {
              return type === 'display' ? dataTableHelper.shortenText(data) : data;
            }
          },
          {
            targets: 5,
            render: function (data, type, row) {
              return type === 'display' ? dataTableHelper.translateStatusRawData(data) : data;
            }
          },
          {
            targets: 6,
            orderable: false,
            searchable: false,
            render: function (data, type, row) {
              return `
                    <div class="dropdown">
                        <a class="text-reset fs-16 px-1" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ri-settings-4-line"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-animated">
                            <li><a class="dropdown-item view-data" data-id="${row.id}" data-bs-toggle="modal" data-bs-target="#editModal" href="#"><i class="ri-eye-line"></i> View Data</a></li>
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
      fetch('/import/read')
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
  function viewDataAction() {
    $(document).on('click', '.view-data', function () {
      const id = $(this).data('id');
      deleteRow(id);
    });
  }

  window.submitImportForm = async function () {
    const form = document.getElementById('import');
    const formData = new FormData(form);
    const file = formData.get('file');

    if (!file) {
      alert("No file selected!");
      return;
    }

    formData.append('file', file); // Kirim file tanpa konversi

    // Kirim ke backend sebagai FormData
    fetch('/import/data', {
      method: 'POST',
      headers: {
        'X-CSRF-Token': csrfToken, // CSRF token Laravel
      },
      body: formData
    })
      .then(response => response.json())
      .then(data => {
        initialize.toast(data);
        $('#importModal').modal('hide');
        form.reset();
      })
      .catch(error => {
        console.error('Error:', error);
        initialize.toast(error);
      });
  };

  window.analyzeFile = function (input) {
    const file = input.files[0];
    const alertPlaceholder = document.getElementById('allert-placeholder');
    const importButton = document.getElementById('importFormSubmit');
    alertPlaceholder.innerHTML = ''; // Bersihkan pesan sebelumnya
    importButton.disabled = true; // Pastikan tombol import dimatikan awalnya

    if (file) {
      const reader = new FileReader();

      reader.onload = function (e) {
        const data = new Uint8Array(e.target.result);
        const workbook = XLSX.read(data, { type: 'array' });

        // Hitung total worksheet
        const totalWorksheets = workbook.SheetNames.length;
        document.getElementById('total-worksheets').innerText = totalWorksheets;

        // Hitung total baris (tanpa header) dan ambil header dari setiap sheet
        let totalRows = 0;
        let headersInfo = [];

        workbook.SheetNames.forEach(function (sheetName) {
          const worksheet = workbook.Sheets[sheetName];
          const range = XLSX.utils.decode_range(worksheet['!ref']);

          if (range.e.r >= range.s.r) { // Pastikan ada data di sheet
            // Ambil header dari baris pertama (row 0)
            let headers = [];
            for (let colNum = range.s.c; colNum <= range.e.c; colNum++) {
              const cell = worksheet[XLSX.utils.encode_cell({ r: range.s.r, c: colNum })];
              headers.push(cell ? cell.v : ""); // Jika kosong, tampilkan string kosong
            }

            headersInfo.push(`<strong>${sheetName}:</strong> ${headers.join(', ')}`);

            // Hitung total baris (tanpa header)
            const rowCount = range.e.r - range.s.r; // Tidak menghitung header
            totalRows += rowCount;
          }
        });

        document.getElementById('total-rows').innerText = totalRows;

        // Tampilkan sukses dan header yang diambil
        const successHtml = `
                <div class="alert alert-success d-flex align-items-center" role="alert">
                    <i class="ri-check-line ri-lg me-2"></i>
                    <div>
                        <strong>Success!</strong> A total of ${totalRows} Data from ${totalWorksheets} sheets are ready to be imported.<br>
                        <strong>Headers found:</strong><br>
                        ${headersInfo.join('<br>')}
                    </div>
                </div>`;
        alertPlaceholder.innerHTML = successHtml;
        importButton.disabled = false; // Aktifkan tombol import
      };

      reader.readAsArrayBuffer(file);
    } else {
      importButton.disabled = true; // Nonaktifkan tombol jika tidak ada file yang diunggah
    }
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
    viewDataAction();

  }
  init();

});


