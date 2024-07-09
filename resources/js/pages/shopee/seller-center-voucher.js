import "datatables.net-bs5";
import DataTable from "datatables.net";
import initialize from "../helper/initialize";
import dataTableHelper from "../helper/dataTableHelper";
import 'daterangepicker/daterangepicker.js';

$.fn.dataTable = DataTable;

const csrfToken = $('meta[name="csrf-token"]').attr('content');

$(document).ready(() => {
    let dataTableInstance;

    function initializeOrUpdateDataTable(startDate, endDate, brandId) {
        if (dataTableInstance) {
            dataTableInstance.destroy();
            $('#basic-datatable').empty();
        }

        dataTableInstance = $('#basic-datatable').DataTable({
            ajax: {
                url: '/shopee/seller-center-voucher/read',
                type: 'GET',
                data: {
                    startDate: startDate,
                    endDate: endDate,
                    brand_id: brandId
                },
                dataSrc: ''
            },
            stateSave: true,
            pageLength: 25,
            deferLoading: true,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            columns: [
                { title: "Voucher Name", data: "voucher_name" },
                { title: "Total Claims", data: "total_claims" },
                { title: "Total Orders", data: "total_orders" },
                { title: "Total Sales", data: "total_sales" },
                { title: "Total Costs", data: "total_costs" },
                { title: "Total Units Sold", data: "total_units_sold" },
                { title: "Total Buyers", data: "total_buyers" },
                { title: "Avg. Sales per Buyer", data: "average_sales_per_buyer" },
                { title: "Avg. ROI", data: "average_roi" },
                { title: "Brand", data: "brand_id", render: data => dataTableHelper.translateBrand(data) },
                {
                    title: "Details",
                    data: "details",
                    visible: false,
                    render: data => formatGroup(data)
                }
            ],
            columnDefs: [{
                targets: -1, // Details column
                searchable: false,
                orderable: false
            }],
            drawCallback: function () {
                $('#basic-datatable_paginate').addClass('pagination-rounded');
                initialize.toolTip();
            },
        });

        $('#basic-datatable tbody').on('click', 'tr', function () {
            var tr = $(this).closest('tr');
            var row = dataTableInstance.row(tr);

            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
            } else {
                row.child(row.data().details ? formatGroup(row.data().details) : 'No details available').show();
                tr.addClass('shown');
            }
        });
    }

    function formatGroup(details) {
        if (!details || !details.length) return 'No details available';

        let tableId = 'nested-table-' + Math.random().toString(36).substr(2, 9);
        let table = `<table id="${tableId}" class="table table-sm"><thead><tr>`;

        if (details.length > 0) {
            Object.keys(details[0]).forEach(key => {
                if (key !== 'brand_id' && key !== 'market_place_id') {
                    table += '<th>' + key + '</th>';
                }
            });
        }

        table += `</tr></thead><tbody>`;
        details.forEach(item => {
            table += `<tr>`;
            Object.entries(item).forEach(([key, value]) => {
                if (key !== 'brand_id' && key !== 'market_place_id') {
                    table += `<td>${value}</td>`;
                }
            });
            table += `</tr>`;
        });

        table += `</tbody></table>`;

        setTimeout(() => {
            $(`#${tableId}`).DataTable({
                paging: false,
                searching: false,
                info: false
            });
        }, 0);

        return table;
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
        if (brandId) data.brand_id = brandId;

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
        initializeOrUpdateDataTable();
        initializeRefreshButton();
    }

    init();
});
