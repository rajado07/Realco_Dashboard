import "datatables.net-bs5";
import DataTable from "datatables.net";
import initialize from "../helper/initialize";
import dataTableHelper from "../helper/dataTableHelper";

$.fn.dataTable = DataTable;

const csrfToken = $('meta[name="csrf-token"]').attr('content');

$(document).ready(() => {
  let dataTableInstance;
  let updateInterval;
  let autoload = true;

  function destroyDataTable() {
    if (dataTableInstance) {
      dataTableInstance.destroy();
      $('#basic-datatable').empty();
      dataTableInstance = null;
    }
    if (updateInterval) {
      clearInterval(updateInterval);
      updateInterval = null;
    }
  }

  function initializeOrUpdateDataTable() {
    destroyDataTable();
    dataTableInstance = $('#basic-datatable').DataTable({
      ajax: {
        url: `/logs/read`,
        type: 'GET',
        dataSrc: '',
      },
      stateSave: true,
      pageLength: 25,
      deferLoading: true,
      lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
      order: [[0, 'desc']], // Sort by the first column (timestamp) in descending order
      columns: [
        { title: "Time Stamp", data: "time" },
        { title: "Type", data: "type" },
        { title: "Message", data: "message" },
      ],
      columnDefs: [
        {
          targets: 0,
          width: "50px",
          render: function (data, type, row) {
            return data;
          }
        },
        {
          targets: 1,
          width: "50px",
          render: function (data, type, row) {
            return dataTableHelper.translateStatusLog(data);
          }
        },
        {
          targets: 2,
          render: function (data, type, row) {
            return type === 'display' ? dataTableHelper.shortenText(data, 200) : data;
          }
        },
      ],
      language: {
        loadingRecords: `<div class="spinner-border avatar-sm text-secondary m-2" role="status"></div>`,
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
  }

  function periodicallyUpdateDataTable() {
    updateInterval = setInterval(() => {
      if (autoload) {
        dataTableInstance.ajax.reload(null, false); // Reload data without resetting pagination
      }
    }, 5000); // Update every 5 seconds
  }

  $('#autoloadLog').on('change', function () {
    autoload = this.checked;
    if (autoload) {
      periodicallyUpdateDataTable();
    } else {
      clearInterval(updateInterval);
    }
  });

  function init() {
    initializeOrUpdateDataTable();
    periodicallyUpdateDataTable();
  }
  init();
});
