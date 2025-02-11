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

    function initializeOrUpdateShopeeDataTable(startDate1, endDate1, startDate2, endDate2, brandId) {
        // Destroy existing instance if it exists
        if (shopeeDataTableInstance) {
            shopeeDataTableInstance.destroy();
            $('#shopee-datatable').empty();
        }

        // Build the URL with parameters
        let url = '/shopee/summary/brand-performance/read';
        let params = [];

        if (startDate1) params.push(`startDate1=${encodeURIComponent(startDate1)}`);
        if (endDate1) params.push(`endDate1=${encodeURIComponent(endDate1)}`);
        if (startDate2) params.push(`startDate2=${encodeURIComponent(startDate2)}`);
        if (endDate2) params.push(`endDate2=${encodeURIComponent(endDate2)}`);
        if (brandId) params.push(`brandId=${encodeURIComponent(brandId)}`);

        if (params.length > 0) {
            url += '?' + params.join('&');
        }

        shopeeDataTableInstance = $('#shopee-datatable').DataTable({
            ajax: {
                url: url,
                type: 'GET',
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
                { title: "Now", data: "product_views.first_period" },
                { title: "Previous", data: "product_views.second_period" },
                { title: "Growth", data: "product_views.growth" },
                { title: "Now", data: "conversion.first_period" },
                { title: "Previous", data: "conversion.second_period" },
                { title: "Growth", data: "conversion.growth" },
                { title: "Now", data: "gmv.first_period" },
                { title: "Previous", data: "gmv.second_period" },
                { title: "Growth", data: "gmv.growth" }
            ],
            columnDefs: [
                {
                    targets: [1, 2, 3, 4, 5, 6, 7, 8, 9],
                    className: 'text-center'
                },
                {
                    targets: [1, 2],
                    render: function (data) {
                        return dataTableHelper.columnSummary(data, 'integer');
                    }
                },
                {
                    targets: [3, 4, 5, 6, 9],
                    render: function (data) {
                        return dataTableHelper.columnSummary(data, 'percentage');
                    }
                },
                {
                    targets: [7, 8],
                    render: function (data) {
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

        // Add click event listener for opening and closing details
        $('#shopee-datatable tbody').off('click', 'td');
        $('#shopee-datatable tbody').on('click', 'td', function () {
            const tr = $(this).closest('tr');
            const row = shopeeDataTableInstance.row(tr);

            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
            } else {
                row.child(formatNestedTable(row.data())).show();
                tr.addClass('shown');
            }
        });
    }

    // Format nested table for children
    function formatNestedTable(group) {
        if (!group.children || group.children.length === 0) {
            return '<div class="text-center">No children available</div>';
        }

        let tableHtml = `
        <table class="table table table-responsive-sm">
            <thead>
                <tr>
                    <th>Sub-Brand</th>
                    <th>Now</th>
                    <th>Previous</th>
                    <th>Growth</th>
                    <th>Now</th>
                    <th>Previous</th>
                    <th>Growth</th>
                    <th>Now</th>
                    <th>Previous</th>
                    <th>Growth</th>
                </tr>
            </thead>
            <tbody>
    `;

        group.children.forEach(child => {
            tableHtml += `
            <tr>
                <td>${child.data_group_name}</td>
                <td>${dataTableHelper.columnSummary(child.product_views.first_period, 'integer')}</td>
                <td>${dataTableHelper.columnSummary(child.product_views.second_period, 'integer')}</td>
                <td>${dataTableHelper.columnSummary(child.product_views.growth, 'percentage')}</td>
                <td>${dataTableHelper.columnSummary(child.conversion.first_period, 'percentage')}</td>
                <td>${dataTableHelper.columnSummary(child.conversion.second_period, 'percentage')}</td>
                <td>${dataTableHelper.columnSummary(child.conversion.growth, 'percentage')}</td>
                <td>${dataTableHelper.columnSummary(child.gmv.first_period, 'currency')}</td>
                <td>${dataTableHelper.columnSummary(child.gmv.second_period, 'currency')}</td>
                <td>${dataTableHelper.columnSummary(child.gmv.growth, 'percentage')}</td>
            </tr>
        `;
        });

        tableHtml += `
            </tbody>
        </table>
    `;

        return tableHtml;
    }


    // Data Table for MetaCpas Data
    let metaCpasDataTableInstance;
    function initializeOrUpdateMetaCpasDataTable(startDate1, endDate1, startDate2, endDate2, brandId) {
        // Destroy existing instance if it exists
        if (metaCpasDataTableInstance) {
            metaCpasDataTableInstance.destroy();
            $('#meta-cpas-datatable').empty();
        }

        // Build the URL with parameters
        let url = '/shopee/summary/cpas/read';
        let params = [];

        if (startDate1) params.push(`startDate1=${encodeURIComponent(startDate1)}`);
        if (endDate1) params.push(`endDate1=${encodeURIComponent(endDate1)}`);
        if (startDate2) params.push(`startDate2=${encodeURIComponent(startDate2)}`);
        if (endDate2) params.push(`endDate2=${encodeURIComponent(endDate2)}`);
        if (brandId) params.push(`brandId=${encodeURIComponent(brandId)}`);

        if (params.length > 0) {
            url += '?' + params.join('&');
        }

        metaCpasDataTableInstance = $('#meta-cpas-datatable').DataTable({
            ajax: {
                url: url,
                type: 'GET',
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
                { title: "Now", data: "amount_spent.first_period" },
                { title: "Previous", data: "amount_spent.second_period" },
                { title: "Growth", data: "amount_spent.growth" },
                { title: "Now", data: "purchases_conversion_value.first_period" },
                { title: "Previous", data: "purchases_conversion_value.second_period" },
                { title: "Growth", data: "purchases_conversion_value.growth" },
                { title: "Now", data: "roas.first_period" },
                { title: "Previous", data: "roas.second_period" },
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
                loadingRecords: '<div class="spinner-border avatar-sm text-secondary m-2" role="status"></div>',
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
    function initializeOrUpdateShopeeAdsDataTable(startDate1, endDate1, startDate2, endDate2, brandId) {
        // Destroy existing instance if it exists
        if (shopeeAdsDataTableInstance) {
            shopeeAdsDataTableInstance.destroy();
            $('#shopee-ads-datatable').empty();
        }

        // Build the URL with parameters
        let url = '/shopee/summary/ads/read';
        let params = [];

        if (startDate1) params.push(`startDate1=${encodeURIComponent(startDate1)}`);
        if (endDate1) params.push(`endDate1=${encodeURIComponent(endDate1)}`);
        if (startDate2) params.push(`startDate2=${encodeURIComponent(startDate2)}`);
        if (endDate2) params.push(`endDate2=${encodeURIComponent(endDate2)}`);
        if (brandId) params.push(`brandId=${encodeURIComponent(brandId)}`);

        if (params.length > 0) {
            url += '?' + params.join('&');
        }

        shopeeAdsDataTableInstance = $('#shopee-ads-datatable').DataTable({
            ajax: {
                url: url,
                type: 'GET',
                dataSrc: function (json) {
                    // Transform the response into an array of objects for DataTables
                    return [{
                        ads_spend: json.ads_spend,
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
                { title: "Now", data: "ads_spend.first_period" },
                { title: "Previous", data: "ads_spend.second_period" },
                { title: "Growth", data: "ads_spend.growth" },
                { title: "Now", data: "gross_sales.first_period" },
                { title: "Previous", data: "gross_sales.second_period" },
                { title: "Growth", data: "gross_sales.growth" },
                { title: "ROAS", data: "roas.first_period" },
                { title: "Previous", data: "roas.second_period" },
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
    function initializeOrUpdateShopeeLiveDataTable(startDate1, endDate1, startDate2, endDate2, brandId) {
        // Destroy existing instance if it exists
        if (shopeeLiveDataTableInstance) {
            shopeeLiveDataTableInstance.destroy();
            $('#shopee-live-datatable').empty();
        }

        // Build the URL with parameters
        let url = '/shopee/summary/live-stream/read';
        let params = [];

        if (startDate1) params.push(`startDate1=${encodeURIComponent(startDate1)}`);
        if (endDate1) params.push(`endDate1=${encodeURIComponent(endDate1)}`);
        if (startDate2) params.push(`startDate2=${encodeURIComponent(startDate2)}`);
        if (endDate2) params.push(`endDate2=${encodeURIComponent(endDate2)}`);
        if (brandId) params.push(`brandId=${encodeURIComponent(brandId)}`);

        if (params.length > 0) {
            url += '?' + params.join('&');
        }

        shopeeLiveDataTableInstance = $('#shopee-live-datatable').DataTable({
            ajax: {
                url: url,
                type: 'GET',
                dataSrc: function (json) {
                    // Transform the response into an array of objects for DataTables
                    return [{
                        total_sales_first_period: json.total_sales.first_period,
                        total_sales_second_period: json.total_sales.second_period,
                        total_sales_growth: json.total_sales.growth,
                        total_duration_first_period: json.total_duration.first_period,
                        total_duration_second_period: json.total_duration.second_period,
                        total_duration_growth: json.total_duration.growth,
                        gmv_per_hour_first_period: json.gmv_per_hour.first_period,
                        gmv_per_hour_second_period: json.gmv_per_hour.second_period,
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
                { title: "First Period", data: "total_sales_first_period" },
                { title: "Second Period", data: "total_sales_second_period" },
                { title: "Growth", data: "total_sales_growth" },
                { title: "First Period", data: "total_duration_first_period" },
                { title: "Second Period", data: "total_duration_second_period" },
                { title: "Growth", data: "total_duration_growth" },
                { title: "First Period", data: "gmv_per_hour_first_period" },
                { title: "Second Period", data: "gmv_per_hour_second_period" },
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
            var selectedDate2 = $('#selectedDate2').text();
            var selectedBrand = $('#selectedBrand').val();

            // Helper function to parse date ranges
            function parseDateRange(dateStr) {
                if (dateStr && dateStr !== 'Select date') {
                    const dates = dateStr.split(' - ');
                    if (dates.length === 2) {
                        return {
                            startDate: initialize.formatDate(dates[0]),
                            endDate: initialize.formatDate(dates[1]),
                        };
                    }
                }
                return { startDate: null, endDate: null };
            }

            const { startDate: startDate1, endDate: endDate1 } = parseDateRange(selectedDate);
            const { startDate: startDate2, endDate: endDate2 } = parseDateRange(selectedDate2);

            console.log(`Start Date 1: ${startDate1}`);
            console.log(`End Date 1: ${endDate1}`);
            console.log(`Start Date 2: ${startDate2}`);
            console.log(`End Date 2: ${endDate2}`);
            console.log(`Selected Brand: ${selectedBrand}`);

            // Call functions with the parsed dates and selected brand
            initializeOrUpdateShopeeDataTable(startDate1, endDate1, startDate2, endDate2, selectedBrand);
            initializeOrUpdateMetaCpasDataTable(startDate1, endDate1, startDate2, endDate2, selectedBrand);
            initializeOrUpdateShopeeAdsDataTable(startDate1, endDate1, startDate2, endDate2, selectedBrand);
            initializeOrUpdateShopeeLiveDataTable(startDate1, endDate1, startDate2, endDate2, selectedBrand);

            if (!startDate1 || !endDate1) {
                console.log('Tanggal tidak tersedia, menggunakan hanya brand untuk filter.');
            }
        });
    }

    function toggleDatePickerAdvancedComparisonsVisibility() {
        $('#advanced-comparisons').on('change', function () {
            if ($(this).is(':checked')) {
                $('#datePickerContainer').slideDown(400); // Tampilkan elemen dengan efek slide
            } else {
                $('#datePickerContainer').slideUp(400); // Sembunyikan elemen dengan efek slide
            }
        });
    }

    function init() {
        fetchBrands();
        toggleDatePickerAdvancedComparisonsVisibility();

        // Initialize Data Tables
        initializeOrUpdateShopeeDataTable();
        initializeOrUpdateMetaCpasDataTable();
        initializeOrUpdateShopeeAdsDataTable();
        initializeOrUpdateShopeeLiveDataTable();
        initializeRefreshButton();
    }

    init();
});
