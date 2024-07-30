import "datatables.net-bs5";
import DataTable from "datatables.net";
import initialize from "../helper/initialize";
import dataTableHelper from "../helper/dataTableHelper";
import 'daterangepicker/daterangepicker.js';

$.fn.dataTable = DataTable;

const csrfToken = $('meta[name="csrf-token"]').attr('content');

$(document).ready(() => {
    // Data Table for Shopee Summary Data
    let shopeeDataTableInstance;
    function initializeOrUpdateShopeeDataTable(startDate, endDate, brandId) {
        // Destroy existing instance if it exists
        if (shopeeDataTableInstance) {
            shopeeDataTableInstance.destroy();
            $('#shopee-datatable').empty();
        }

        shopeeDataTableInstance = $('#shopee-datatable').DataTable({
            ajax: {
                url: '/shopee/summary/brand-performance/read',
                type: 'GET',
                data: function (d) {
                    if (startDate) d.startDate = startDate;
                    if (endDate) d.endDate = endDate;
                    if (brandId) d.brand_id = brandId;
                },
                dataSrc: '',
            },
            stateSave: true,
            pageLength: -1,
            lengthMenu: [[-1], ["All"]],
            searching: false,
            paging: false,
            info: false,
            deferLoading: true,
            columns: [
                { title: "Brand", data: "data_group_name" },
                { title: "This Week", data: "product_views.this_week" },
                { title: "Previous Week", data: "product_views.previous_week" },
                { title: "Growth", data: "product_views.growth" },
                { title: "This Week", data: "conversion.this_week" },
                { title: "Previous Week", data: "conversion.previous_week" },
                { title: "Growth", data: "conversion.growth" },
                { title: "This Week", data: "GMV.this_week" },
                { title: "Previous Week", data: "GMV.previous_week" },
                { title: "Growth", data: "GMV.growth" }
            ],
            columnDefs: [
                {
                    targets: [1, 2, 3, 4, 5, 6, 7, 8, 9],
                    className: 'text-center'
                },
                {
                    targets: [1, 2],
                    render: function (data, type, row) {
                        return dataTableHelper.columnSummary(data, 'integer');
                    }
                },
                {
                    targets: [3, 4, 5, 6, 9],
                    render: function (data, type, row) {
                        return dataTableHelper.columnSummary(data, 'percentage');
                    }
                },
                {
                    targets: [7, 8],
                    render: function (data, type, row) {
                        return dataTableHelper.columnSummary(data, 'currency');
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
                $('#shopee-datatable_paginate').addClass('pagination-rounded');
                initialize.toolTip();
            },
        });
    }

    // Data Table for MetaCpas Data
    let metaCpasDataTableInstance;
    function initializeOrUpdateMetaCpasDataTable(startDate, endDate, brandId) {
        // Destroy existing instance if it exists
        if (metaCpasDataTableInstance) {
            metaCpasDataTableInstance.destroy();
            $('#meta-cpas-datatable').empty();
        }

        metaCpasDataTableInstance = $('#meta-cpas-datatable').DataTable({
            ajax: {
                url: '/shopee/summary/cpas/read',
                type: 'GET',
                data: function (d) {
                    if (startDate) d.startDate = startDate;
                    if (endDate) d.endDate = endDate;
                    if (brandId) d.brand_id = brandId;
                },
                dataSrc: '',
            },
            stateSave: true,
            pageLength: -1,
            lengthMenu: [[-1], ["All"]],
            searching: false,
            paging: false,
            info: false,
            deferLoading: true,
            columns: [
                { title: "Brand", data: "data_group_name" },
                { title: "This Week", data: "amount_spent.this_week" },
                { title: "Previous Week", data: "amount_spent.previous_week" },
                { title: "Growth", data: "amount_spent.growth" },
                { title: "This Week", data: "purchases_conversion_value.this_week" },
                { title: "Previous Week", data: "purchases_conversion_value.previous_week" },
                { title: "Growth", data: "purchases_conversion_value.growth" },
                { title: "This Week", data: "roas.this_week" },
                { title: "Previous Week", data: "roas.previous_week" },
                { title: "Growth", data: "roas.growth" }
            ],
            columnDefs: [
                {
                    targets: [1, 2, 3, 4, 5, 6, 7, 8, 9],
                    className: 'text-center'
                },
                {
                    targets: [1, 2],
                    render: function (data, type, row) {
                        return dataTableHelper.columnSummary(data, 'currency');
                    }
                },
                {
                    targets: [3, 6, 9],
                    render: function (data, type, row) {
                        return dataTableHelper.columnSummary(data, 'percentage');
                    }
                },
                {
                    targets: [4, 5],
                    render: function (data, type, row) {
                        return dataTableHelper.columnSummary(data, 'currency');
                    }
                },
                {
                    targets: [7, 8],
                    render: function (data, type, row) {
                        return dataTableHelper.columnSummary(data, 'decimal');
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
                $('#meta-cpas-datatable_paginate').addClass('pagination-rounded');
                initialize.toolTip();
            },
        });
    }

    // Data Table for Shopee Ads Data
    let shopeeAdsDataTableInstance;
    function initializeOrUpdateShopeeAdsDataTable(startDate, endDate, brandId) {
        // Destroy existing instance if it exists
        if (shopeeAdsDataTableInstance) {
            shopeeAdsDataTableInstance.destroy();
            $('#shopee-ads-datatable').empty();
        }

        shopeeAdsDataTableInstance = $('#shopee-ads-datatable').DataTable({
            ajax: {
                url: '/shopee/summary/ads/read',
                type: 'GET',
                data: function (d) {
                    if (startDate) d.startDate = startDate;
                    if (endDate) d.endDate = endDate;
                    if (brandId) d.brand_id = brandId;
                },
                dataSrc: function (json) {
                    // Transform the response into an array of objects for DataTables
                    return [{
                        ads_spent: json.ads_spent,
                        gross_sales: json.gross_sales,
                        roas: json.roas
                    }];
                }
            },
            stateSave: true,
            pageLength: -1,
            lengthMenu: [[-1], ["All"]],
            searching: false,
            paging: false,
            info: false,
            deferLoading: true,
            columns: [
                { title: "This Week", data: "ads_spent.this_week" },
                { title: "Previous Week", data: "ads_spent.previous_week" },
                { title: "Growth", data: "ads_spent.growth" },
                { title: "This Week", data: "gross_sales.this_week" },
                { title: "Previous Week", data: "gross_sales.previous_week" },
                { title: "Growth", data: "gross_sales.growth" },
                { title: "ROAS", data: "roas.this_week" },
                { title: "Previous Week", data: "roas.previous_week" },
                { title: "Growth", data: "roas.growth" }
            ],
            columnDefs: [
                {
                    targets: [0, 1, 3, 4, 6, 7],
                    render: function (data, type, row) {
                        return dataTableHelper.columnSummary(data, 'currency');
                    },
                    className: 'text-center'
                },
                {
                    targets: [2, 5, 8],
                    render: function (data, type, row) {
                        return dataTableHelper.columnSummary(data, 'percentage');
                    },
                    className: 'text-center'
                }
            ],
            language: {
                loadingRecords: `<div class="spinner-border avatar-sm text-secondary m-2" role="status"></div>`,
                paginate: {
                    previous: "<i class='ri-arrow-left-s-line'></i>",
                    next: "<i class='ri-arrow-right-s-line'></i>"
                }
            },
            drawCallback: function () {
                $('#shopee-ads-datatable_paginate').addClass('pagination-rounded');
                initialize.toolTip();
            },
        });
    }

    let shopeeLiveDataTableInstance;
    function initializeOrUpdateShopeeLiveDataTable(startDate, endDate, brandId) {
        // Destroy existing instance if it exists
        if (shopeeLiveDataTableInstance) {
            shopeeLiveDataTableInstance.destroy();
            $('#shopee-live-datatable').empty();
        }

        shopeeLiveDataTableInstance = $('#shopee-live-datatable').DataTable({
            ajax: {
                url: '/shopee/summary/live-stream/read',
                type: 'GET',
                data: function (d) {
                    if (startDate) d.startDate = startDate;
                    if (endDate) d.endDate = endDate;
                    if (brandId) d.brand_id = brandId;
                },
                dataSrc: function (json) {
                    // Transform the response into an array of objects for DataTables
                    return [{
                        total_sales_this_week: json.total_sales.may,
                        total_sales_previous_week: json.total_sales.june,
                        total_sales_growth: json.total_sales.growth,
                        total_duration_this_week: json.total_duration.may,
                        total_duration_previous_week: json.total_duration.june,
                        total_duration_growth: json.total_duration.growth,
                        gmv_per_hour_this_week: json.gmv_per_hour.may,
                        gmv_per_hour_previous_week: json.gmv_per_hour.june,
                        gmv_per_hour_growth: json.gmv_per_hour.growth
                    }];
                }
            },
            stateSave: true,
            pageLength: -1,
            lengthMenu: [[-1], ["All"]],
            searching: false,
            paging: false,
            info: false,
            deferLoading: true,
            columns: [
                { title: "This Week", data: "total_sales_this_week" },
                { title: "Previous Week", data: "total_sales_previous_week" },
                { title: "Growth", data: "total_sales_growth" },
                { title: "This Week", data: "total_duration_this_week" },
                { title: "Previous Week", data: "total_duration_previous_week" },
                { title: "Growth", data: "total_duration_growth" },
                { title: "This Week", data: "gmv_per_hour_this_week" },
                { title: "Previous Week", data: "gmv_per_hour_previous_week" },
                { title: "Growth", data: "gmv_per_hour_growth" }
            ],
            columnDefs: [
                {
                    targets: [0, 1, 3, 4, 6, 7],
                    render: function (data, type, row) {
                        return dataTableHelper.columnSummary(data, 'currency');
                    },
                    className: 'text-center'
                },
                {
                    targets: [2, 5, 8],
                    render: function (data, type, row) {
                        return dataTableHelper.columnSummary(data, 'percentage');
                    },
                    className: 'text-center'
                }
            ],
            language: {
                loadingRecords: `<div class="spinner-border avatar-sm text-secondary m-2" role="status"></div>`,
                paginate: {
                    previous: "<i class='ri-arrow-left-s-line'></i>",
                    next: "<i class='ri-arrow-right-s-line'></i>"
                }
            },
            drawCallback: function () {
                $('#shopee-live-datatable_paginate').addClass('pagination-rounded');
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

    function initializeRefreshButton() {
        $('#btn-refresh').click(function () {
            var selectedDate = $('#selectedDate').text();
            var selectedBrand = $('#selectedBrand').val();

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

            initializeOrUpdateShopeeDataTable(startDate, endDate, selectedBrand);
            initializeOrUpdateMetaCpasDataTable(startDate, endDate, selectedBrand);
            initializeOrUpdateShopeeAdsDataTable(startDate, endDate, selectedBrand);

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

        // Initialize Data Tables
        initializeOrUpdateShopeeDataTable();
        initializeOrUpdateMetaCpasDataTable();
        initializeOrUpdateShopeeAdsDataTable();
        initializeOrUpdateShopeeLiveDataTable();
        initializeRefreshButton();
    }

    init();
});
