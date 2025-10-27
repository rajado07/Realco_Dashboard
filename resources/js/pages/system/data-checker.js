import "datatables.net-bs5";
import DataTable from "datatables.net";
import initialize from "../helper/initialize";
import dataTableHelper from "../helper/dataTableHelper";
import 'daterangepicker/daterangepicker.js';

$.fn.dataTable = DataTable;

const csrfToken = $('meta[name="csrf-token"]').attr('content'); // Mengambil CSRF token

$(document).ready(() => {
  let dataTableInstance;

  function initializeOrUpdateDataTable(startDate = null, endDate = null) {
    // Jika instance DataTable sudah ada, hancurkan terlebih dahulu
    if (dataTableInstance) {
      dataTableInstance.destroy();  // Hancurkan DataTable yang lama
      $('#basic-datatable').empty(); // Kosongkan tabel sebelum menginisialisasi ulang
    }

    // Inisialisasi ulang DataTable
    dataTableInstance = $('#basic-datatable').DataTable({
      ajax: {
        url: '/data-checker/check-dates',
        type: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrfToken
        },
        data: function (d) {
          // Kirim startDate dan endDate ke server jika tersedia
          if (startDate && endDate) {
            d.start_date = startDate;  // Kirimkan start date tanpa waktu
            d.end_date = endDate;  // Kirimkan end date tanpa waktu
          }
          return d;
        },
        dataSrc: '',
      },
      stateSave: true,
      pageLength: 25,
      deferLoading: true,
      lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
      columns: [
        { title: "No", data: "no" },
        { title: "Type", data: "type" },
        { title: "Missing Date", data: "missing_date" },
        { title: "Brand", data: "brand_id" },
        { title: "Market Place", data: "market_place_id" },
      ],
      columnDefs: [
        {
          targets: 4,
          render: function (data, type, row) {
            return dataTableHelper.translateMarketPlace(data);
          }
        },
        {
          targets: 3,
          render: function (data, type, row) {
            return dataTableHelper.translateBrand(data);
          }
        },
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
  }

  // Fungsi untuk menangani tombol refresh
  function initializeRefreshButton() {
    $('#btn-refresh').click(function () {
      var selectedDate = $('#selectedDate').text(); // Ambil tanggal dari elemen HTML

      // Mengecek apakah ada tanggal yang dipilih
      const dates = selectedDate.split(' - '); // Asumsi formatnya: 'start_date - end_date'
      let startDate = null, endDate = null;

      if (dates.length === 2) {
        startDate = initialize.formatDate(dates[0]); // Format tanggal dengan helper
        endDate = initialize.formatDate(dates[1]); // Format tanggal dengan helper
      }

      // Debug: Lihat apakah tanggal sudah terdeteksi
      console.log(`Start Date: ${startDate}`);
      console.log(`End Date: ${endDate}`);

      // Panggil fungsi untuk reload DataTable dengan startDate dan endDate baru
      initializeOrUpdateDataTable(startDate, endDate);

      if (!startDate || !endDate) {
        console.log('Tanggal tidak tersedia, menggunakan hanya brand untuk filter.');
      }
    });
  }

  // Fungsi utama untuk inisialisasi
  function init() {
    // Initialise External Helper
    dataTableHelper.checkAll();
    dataTableHelper.shiftSelection();
    dataTableHelper.toggleSettings();

    // Data Table
    initializeOrUpdateDataTable(); // Panggil tanpa tanggal pada inisialisasi pertama
    initializeRefreshButton(); // Inisialisasi tombol refresh
  }
  init();
});
