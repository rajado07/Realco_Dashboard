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
        // Jika instance DataTable sudah ada, destroy terlebih dahulu sebelum membuat yang baru
        if (dataTableInstance) {
            dataTableInstance.destroy();
            $('#basic-datatable').empty();
        }

        dataTableInstance = $('#basic-datatable').DataTable({
            ajax: {
                url: '/shopee/seller-center-live-streaming/read',
                type: 'GET',
                data: function (d) {
                    // Menambahkan parameter startDate dan endDate jika ada
                    if (startDate) d.startDate = startDate;
                    if (endDate) d.endDate = endDate;
                    if (brandId) d.brand_id = brandId;
                },
                dataSrc: function (json) {
                    summaryData(json);
                    return json;
                },
            },
            stateSave: true,
            pageLength: 25,
            deferLoading: true,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            columns: [
                { title: "Date", data: "data_date" },
                { title: "Duration", data: "duration" },
                { title: "Viewers", data: "unique_viewers" },
                { title: "Peak Viewers", data: "peak_viewers" },
                { title: "Average Watch Time", data: "avg_watch_time" },
                { title: "Orders", data: "orders" },
                { title: "Sales", data: "sales" },
                { title: "GMV / Hour", data: "sales_per_hour" },
                { title: "Brand", data: "brand_id" },
                { title: "Action", defaultContent: '' }
            ],
            columnDefs: [
                {
                    targets: [1, 2, 5],
                    render: function (data, type, row) {
                        return dataTableHelper.columnWitheNowPreviousChange(data);
                    }
                },
                {
                    targets: [3, 4],
                    render: function (data, type, row) {
                        return dataTableHelper.columnWitheNowPreviousChange(data, false, false);
                    }
                },
                {
                    targets: [6, 7],
                    render: function (data, type, row) {
                        return dataTableHelper.columnWitheNowPreviousChange(data, true);
                    }
                },

                {
                    targets: 8,
                    render: function (data, type, row) {
                        return type === 'display' ? dataTableHelper.translateBrand(data) : data;
                    }
                },
                {
                    targets: 9, // Action
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

        $('#basic-datatable tbody').on('click', 'tr', function () {
            var tr = $(this).closest('tr');
            var row = dataTableInstance.row(tr);

            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
            } else {
                row.child(formatGroup(row.data().details)).show();
                tr.addClass('shown');
            }
        });
    }

    function formatGroup(details) {
        let tableId = 'nested-table-' + Math.random().toString(36).substr(2, 9);
        let table = `<table id="${tableId}" class="table table-sm"><thead><tr>`;

        // Get keys from the first detail item for table headers
        if (details.length > 0) {
            Object.keys(details[0]).forEach(function (key) {
                if (key !== 'brand_id' && key !== 'market_place_id') { // Exclude brand_id and market_place_id
                    table += '<th>' + key + '</th>';
                }
            });
        }

        table += `</tr></thead><tbody>`;

        details.forEach(function (item) {
            table += `<tr>`;
            Object.entries(item).forEach(function ([key, value]) {
                if (key !== 'brand_id' && key !== 'market_place_id') { // Exclude brand_id and market_place_id
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

    function summaryData(data) {
        let totalDurationSeconds = 0;
        let totalUniqueViewers = 0;
        let totalOrders = 0;
        let totalSales = 0;
        let totalSalesPerHour = 0;
        let totalDurationChange = 0;
        let totalUniqueViewersChange = 0;
        let totalOrdersChange = 0;
        let totalSalesChange = 0;
        let totalSalesPerHourChange = 0;
        let itemCount = data.length;
    
        data.forEach(item => {
            // Convert duration to seconds
            let [hours, minutes] = item.duration.now.split('H').map(part => part.trim());
            minutes = minutes.split('M')[0].trim();
            let durationSeconds = (parseInt(hours) * 3600) + (parseInt(minutes) * 60);
            totalDurationSeconds += durationSeconds;
    
            totalDurationChange += item.duration.change;
            totalUniqueViewers += item.unique_viewers.now;
            totalUniqueViewersChange += item.unique_viewers.change;
            totalOrders += item.orders.now;
            totalOrdersChange += item.orders.change;
            totalSales += item.sales.now;
            totalSalesChange += item.sales.change;
            totalSalesPerHour += item.sales_per_hour.now;
            totalSalesPerHourChange += item.sales_per_hour.change;
        });
    
        // Calculate averages
        let averageDurationChange = totalDurationChange / itemCount;
        let averageUniqueViewersChange = totalUniqueViewersChange / itemCount;
        let averageOrdersChange = totalOrdersChange / itemCount;
        let averageSalesChange = totalSalesChange / itemCount;
        let averageSalesPerHourChange = totalSalesPerHourChange / itemCount;
    
        // Convert total duration seconds back to HH MM format
        let totalHours = Math.floor(totalDurationSeconds / 3600);
        let totalMinutes = Math.floor((totalDurationSeconds % 3600) / 60);
        let totalDuration = `${totalHours}H ${totalMinutes}M`;
    
        // Create summary object
        let summary = {
            totalDuration,
            totalDurationSeconds,  // Added to include total duration in seconds
            totalUniqueViewers,
            totalOrders,
            totalSales,
            totalSalesPerHour,
            averageDurationChange,
            averageUniqueViewersChange,
            averageOrdersChange,
            averageSalesChange,
            averageSalesPerHourChange
        };
    
        // If you have animated counters, you can use the initialize.animateCounter method
        initialize.animateCounterDuration($('#duration'), totalDurationSeconds);
        initialize.animateCounter($('#sales'), totalSales, true);
        initialize.animateCounter($('#sales-per-hour'), totalSalesPerHour, true);
        initialize.animateCounter($('#viewers'), totalUniqueViewers);
        initialize.animateCounter($('#order'), totalOrders);

        initialize.updatePercentageChangeLiveSteraming($('#duration-change-percentage'), averageDurationChange);
        initialize.updatePercentageChangeLiveSteraming($('#sales-change-percentage'), averageDurationChange);
        initialize.updatePercentageChangeLiveSteraming($('#sales-per-hour-change-percentage'), averageSalesPerHourChange);
        initialize.updatePercentageChangeLiveSteraming($('#viewers-change-percentage'), averageUniqueViewersChange);
        initialize.updatePercentageChangeLiveSteraming($('#order-change-percentage'), averageOrdersChange);



        console.log(summary)
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
        initializeOrUpdateDataTable();
        initializeRefreshButton();
    }

    init();
});
