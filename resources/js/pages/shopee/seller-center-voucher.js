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
                { title: "Claims", data: "total_claims" },
                { title: "Orders", data: "total_orders" },
                { title: "Sales", data: "total_sales" },
                { title: "Costs", data: "total_costs" },
                { title: "Units Sold", data: "total_units_sold" },
                { title: "Buyers", data: "total_buyers" },
                { title: "Sales per Buyer", data: "average_sales_per_buyer" },
                { title: "ROI", data: "average_roi", render: function(data) {
                    return parseFloat(data).toFixed(2); // This will format the number to two decimal places
                }},
                { title: "Usage Rate", data: "average_usage_rate", render: function(data) {
                    return parseFloat(data).toFixed(2); // This will format the number to two decimal places
                }},
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
            },
            {
                targets: [3, 4,7],
                render: function (data, type, row) {
                    return dataTableHelper.currency(data);
                }
            },
        ],
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
            url: '/shopee/seller-center-voucher/summary',
            type: 'GET',
            data: data,
            success: function (response) {
                initialize.animateCounter($('#claim'), response.current.total_claims);
                initialize.animateCounter($('#sales'), response.current.total_sales, true);
                initialize.animateCounter($('#cost'), response.current.total_costs, true);
                initialize.animateCounter($('#usage-rate'), response.current.average_usage_rate);
                initialize.animateCounter($('#return-on-investment'), response.current.average_roi);

                initialize.updatePercentageChange($('#claim-change-percentage'), response.changes.total_claims_change_percentage, startDate, endDate);
                initialize.updatePercentageChange($('#sales-change-percentage'), response.changes.total_sales_change_percentage, startDate, endDate);
                initialize.updatePercentageChange($('#cost-change-percentage'), response.changes.total_costs_change_percentage, startDate, endDate);
                initialize.updatePercentageChange($('#usage-rate-change-percentage'), response.changes.average_usage_rate_change_percentage, startDate, endDate);
                initialize.updatePercentageChange($('#return-on-investment-change-percentage'), response.changes.average_roi_change_percentage, startDate, endDate);
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
