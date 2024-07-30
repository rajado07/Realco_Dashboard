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
                url: '/shopee/seller-center-coin/read',
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
                { title: "Date", data: "data_date" },
                { title: "Coins Used", data: "total_coins_amount", render: data => {
                    return new Intl.NumberFormat().format(data);
                }},
                { title: "Sales", data: "total_sales" , render: data => dataTableHelper.currency(data) },
                { title: "Return On Investment", data: "roi" },
                { title: "Brand", data: "brand_id", render: data => dataTableHelper.translateBrand(data) },
                {
                    title: "Details",
                    data: "details",
                    visible: false,
                    render: function (data, type, row) {
                        return data ? formatGroup(data) : 'No details available';
                    }
                }
            ],
            columnDefs: [{
                targets: -1, // Last column for Details
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
        let tableHtml = '<table class="table table-sm"><thead><tr>';

        // Determine keys from the first detail item for table headers
        if (details.length > 0) {
            Object.keys(details[0]).forEach(key => {
                tableHtml += `<th>${key}</th>`;
            });
        }

        tableHtml += '</tr></thead><tbody>';
        details.forEach(detail => {
            tableHtml += '<tr>';
            Object.keys(detail).forEach(key => {
                tableHtml += `<td>${detail[key]}</td>`;
            });
            tableHtml += '</tr>';
        });
        tableHtml += '</tbody></table>';

        return tableHtml;
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
            url: '/shopee/seller-center-coin/summary',
            type: 'GET',
            data: data,
            success: function (response) {
                initialize.animateCounter($('#coins'), response.current.total_coins_amount);
                initialize.animateCounter($('#sales'), response.current.total_sales,true);
                initialize.animateCounter($('#roi'), response.current.average_roi);

                initialize.updatePercentageChange($('#coins-change-percentage'), response.changes.total_coins_amount_change_percentage, startDate, endDate);
                initialize.updatePercentageChange($('#sales-change-percentage'), response.changes.total_sales_change_percentage, startDate, endDate);
                initialize.updatePercentageChange($('#roi-change-percentage'), response.changes.average_roi_change_percentage, startDate, endDate);
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
