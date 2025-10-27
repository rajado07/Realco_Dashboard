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
    function initializeOrUpdateDataTable(startDate, endDate, brandId) {
        // Jika instance DataTable sudah ada, destroy terlebih dahulu sebelum membuat yang baru
        if (dataTableInstance) {
            dataTableInstance.destroy();
            $('#basic-datatable').empty();
        }

        dataTableInstance = $('#basic-datatable').DataTable({
            ajax: {
                url: '/shopee/brand-portal-ads/read',
                type: 'GET',
                data: function (d) {
                    // Menambahkan parameter startDate dan endDate jika ada
                    if (startDate) d.startDate = startDate;
                    if (endDate) d.endDate = endDate;
                    if (brandId) d.brand_id = brandId;
                },
                dataSrc: '',
            },
            stateSave: true,
            pageLength: 25,
            deferLoading: true,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            columns: [
                { title: "ID", data: "id" },
                { title: "Shop ID", data: "shop_id" },
                { title: "Shop Name", data: "shop_name" },
                { title: "Impressions", data: "impressions" },
                { title: "Orders", data: "orders" },
                { title: "Gross Sales", data: "gross_sales" },
                { title: "Ads Spend", data: "ads_spend" },
                { title: "Units Sold", data: "units_sold" },
                { title: "Return On Ads Spend", data: "return_on_ads_spend" },
                { title: "Date", data: "data_date" },
                { title: "Brand", data: "brand_id" },
                { title: "Action", defaultContent: '' }
            ],
            columnDefs: [
                {
                    targets: 10,
                    render: function (data, type, row) {
                        return type === 'display' ? dataTableHelper.translateBrand(data) : data;
                    }
                },
                {
                    targets: [5, 6],
                    render: function (data, type, row) {
                        return type === 'display' ? dataTableHelper.currency(data) : data;
                    }
                },
                {
                    targets: 11,
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
    }

    function fetchBrands() {
        fetch('/brand/read')
            .then(response => response.json())
            .then(data => {
                const selectBrand = document.getElementById('selectedBrand');
                selectBrand.innerHTML = '';
                const defaultOption = new Option('All Brands', '0', true, true);
                selectBrand.add(defaultOption);

                data.forEach(brand => {
                    const option = new Option(brand.name, brand.id);
                    selectBrand.add(option);
                });

                $(selectBrand).trigger('change');
            })
            .catch(error => console.error('Error loading brands:', error));
    }

    function fetchSummaryData(startDate, endDate, brandId) {
        const data = {};
        if (startDate) data.start_date = startDate;
        if (endDate) data.end_date = endDate;
        if (brandId) data.brand_id = brandId;  // Memasukkan brandId ke dalam data yang dikirim

        $.ajax({
            url: '/shopee/brand-portal-ads/summary',
            type: 'GET',
            data: data,
            success: function (response) {
                initialize.animateCounter($('#impressions'), response.current.total_impressions);
                initialize.animateCounter($('#orders'), response.current.total_orders);
                initialize.animateCounter($('#gross-sales'), response.current.total_gross_sales, true);
                initialize.animateCounter($('#ads-spends'), response.current.total_ads_spend, true);
                initialize.animateCounter($('#units-sold'), response.current.total_units_sold);
                initialize.animateCounter($('#return-on-ads-spend'), response.current.average_roas, false, 1000, 2);

                initialize.updatePercentageChange($('#impressions-change-percentage'), response.changes.total_impressions_change_percentage, startDate, endDate);
                initialize.updatePercentageChange($('#orders-change-percentage'), response.changes.total_orders_change_percentage, startDate, endDate);
                initialize.updatePercentageChange($('#gross-sales-change-percentage'), response.changes.total_gross_sales_change_percentage, startDate, endDate);
                initialize.updatePercentageChange($('#ads-spends-change-percentage'), response.changes.total_ads_spend_change_percentage, startDate, endDate);
                initialize.updatePercentageChange($('#units-sold-change-percentage'), response.changes.total_units_sold_change_percentage, startDate, endDate);
                initialize.updatePercentageChange($('#return-on-ads-spend-change-percentage'), response.changes.average_roas_change, startDate, endDate);
            },
            error: function (error) {
                console.error('Error fetching summary data:', error);
            }
        });
    }

    function initializeRefreshButton() {
        $('#btn-refresh').click(function () {
            var selectedDate = $('#selectedDate').text();
            var selectedBrand = $('#selectedBrand').val();

            // Mengecek apakah ada tanggal yang dipilih
            const dates = selectedDate.split(' - ');
            let startDate, endDate;

            if (dates.length === 2) {
                startDate = initialize.formatDate(dates[0]);
                endDate = initialize.formatDate(dates[1]);
                console.log(`Start Date: ${startDate}`);
                console.log(`End Date: ${endDate}`);
            }

            console.log(`Nilai input berubah menjadi: ${selectedDate}`);
            console.log(`Selected Brand: ${selectedBrand}`);

            fetchSummaryData(startDate, endDate, selectedBrand);
            initializeOrUpdateDataTable(startDate, endDate, selectedBrand);

            if (!startDate || !endDate) {
                console.log('Tanggal tidak tersedia, menggunakan hanya brand untuk filter.');
            }
        });
    }

    function fetchLatestRetrievedData() {
        $.ajax({
            url: '/shopee/brand-portal-ads/latest-data',
            type: 'GET',
            success: function (response) {
                // Directly update the text of the label with the received data
                $('#latestRetrievedData').text(response);
            },
            error: function (error) {
                console.error('Error fetching latest retrieved data:', error);
            }
        });
    }


    function init() {

        fetchLatestRetrievedData();
        fetchBrands();
        fetchSummaryData();

        // Data Table
        initializeOrUpdateDataTable();
        initializeRefreshButton();

        
    }
    init();
});
