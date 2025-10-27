import "datatables.net-bs5";
import DataTable from "datatables.net";
import initialize from "../helper/initialize";
import dataTableHelper from "../helper/dataTableHelper";
import 'daterangepicker/daterangepicker.js';

$.fn.dataTable = DataTable;

const csrfToken = $('meta[name="csrf-token"]').attr('content');

$(document).ready(() => {
    let dataTableInstance;

    function initializeOrUpdateDataTable(startDate, endDate, brandId, marketPlaceId) {
        if (dataTableInstance) {
            dataTableInstance.destroy();
            $('#basic-datatable').empty();
        }

        dataTableInstance = $('#basic-datatable').DataTable({
            ajax: {
                url: '/meta/cpas/read',
                type: 'GET',
                data: function (d) {
                    if (startDate) d.startDate = startDate;
                    if (endDate) d.endDate = endDate;
                    if (brandId) d.brand_id = brandId;
                    if (marketPlaceId) d.market_place_id = marketPlaceId;
                },
                dataSrc: function (json) {
                    console.log("Received data:", json); // Log received data for debugging
                    return json; // Data is already grouped and formatted from the backend
                },
                error: function (xhr, error, code) {
                    console.error("AJAX error:", error); // Log AJAX errors
                }
            },
            stateSave: true,
            pageLength: 25,
            deferLoading: true,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            columns: [
                { title: "Brand", data: "data_group_name" },
                { title: "Amount Spent", data: "amount_spent" },
                { title: "Content Views", data: "content_views_with_shared_items" },
                { title: "Adds to Cart", data: "adds_to_cart_with_shared_items" },
                { title: "Purchases", data: "purchases_with_shared_items" },
                { title: "Purchase Value Conversion", data: "purchases_conversion_value_for_shared_items_only" },
                { title: "Total Impressions", data: "impressions" },
                { title: "Return on Ad Spend", data: "return_on_ad_spend" }
            ],
            columnDefs: [
                {
                    targets: [2, 3, 4,6, 7],
                    render: function (data, type, row) {
                        return dataTableHelper.columnWitheNowPreviousChange(data,);
                    }
                },
                {
                    targets: [1 ,5],
                    render: function (data, type, row) {
                        return dataTableHelper.columnWitheNowPreviousChange(data,true);
                    }
                }
            ],
            language: {
                loadingRecords: ` <div class="spinner-border avatar-sm text-secondary m-2" role="status"></div>`,
                paginate: {
                    previous: "<i class='ri-arrow-left-s-line'></i>",
                    next: "<i='ri-arrow-right-s-line'></i>"
                }
            },
            drawCallback: function () {
                $('#basic-datatable_paginate').addClass('pagination-rounded');
                initialize.toolTip();
            },
            rowCallback: function (row, data) {
                $(row).off('click').on('click', function () {
                    let tr = $(this).closest('tr');
                    let row = dataTableInstance.row(tr);

                    if (row.child.isShown()) {
                        row.child.hide();
                        tr.removeClass('shown');
                    } else {
                        row.child(formatDetails(data.details)).show();
                        tr.addClass('shown');
                    }
                });
            }
        });
    }

    function formatDetails(details) {
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

    function fetchMarketPlaces() {
        fetch('/market-place/read')
            .then(response => response.json())
            .then(data => {
                const selectMarketPlace = document.getElementById('selectedMarketPlace');
                selectMarketPlace.innerHTML = '';
                const defaultOption = new Option('All Market Places', '0', true, true);
                selectMarketPlace.add(defaultOption);

                data.forEach(marketPlace => {
                    const option = new Option(marketPlace.name, marketPlace.id);
                    selectMarketPlace.add(option);
                });

                $(selectMarketPlace).trigger('change');
            })
            .catch(error => console.error('Error loading Market Places:', error));
    }

    function fetchSummaryData(startDate, endDate, brandId, marketPlaceId) {
        const data = {};
        if (startDate) data.start_date = startDate;
        if (endDate) data.end_date = endDate;
        if (brandId) data.brand_id = brandId;
        if (marketPlaceId) data.market_place_id = marketPlaceId;

        $.ajax({
            url: '/meta/cpas/summary',
            type: 'GET',
            data: data,
            success: function (response) {
                initialize.animateCounter($('#amount-spent'), response.current.total_amount_spent, true);
                initialize.animateCounter($('#content-views'), response.current.total_content_views);
                initialize.animateCounter($('#impressions'), response.current.total_impressions);
                initialize.animateCounter($('#adds-to-cart'), response.current.total_adds_to_cart);
                initialize.animateCounter($('#purchases'), response.current.total_purchases);
                initialize.animateCounter($('#purchases-conversion-value'), response.current.total_purchases_conversion_value, true);

                initialize.updatePercentageChange($('#amount-spent-change-percentage'), response.changes.total_amount_spent_change_percentage, startDate, endDate);
                initialize.updatePercentageChange($('#content-views-change-percentage'), response.changes.total_content_views_change_percentage, startDate, endDate);
                initialize.updatePercentageChange($('#impressions-change-percentage'), response.changes.total_impressions_change_percentage, startDate, endDate);
                initialize.updatePercentageChange($('#adds-to-cart-change-percentage'), response.changes.total_adds_to_cart_change_percentage, startDate, endDate);
                initialize.updatePercentageChange($('#purchases-change-percentage'), response.changes.total_purchases_change_percentage, startDate, endDate);
                initialize.updatePercentageChange($('#purchases-conversion-value-change-percentage'), response.changes.total_purchases_conversion_value_change_percentage, startDate, endDate);
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
            var selectedMarketPlace = $('#selectedMarketPlace').val();

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
            console.log(`Selected Market Place: ${selectedMarketPlace}`);

            fetchSummaryData(startDate, endDate, selectedBrand, selectedMarketPlace);
            initializeOrUpdateDataTable(startDate, endDate, selectedBrand, selectedMarketPlace);

            if (!startDate || !endDate) {
                console.log('Tanggal tidak tersedia, menggunakan hanya brand untuk filter.');
            }
        });
    }

    function fetchLatestRetrievedData() {
        $.ajax({
            url: '/meta/cpas/latest-data',
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
        fetchMarketPlaces();
        fetchSummaryData();

        // Data Table
        initializeOrUpdateDataTable();
        initializeRefreshButton();
    }

    init();
});

