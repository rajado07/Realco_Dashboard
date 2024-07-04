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
                url: '/meta/cpas/read',
                type: 'GET',
                data: function (d) {
                    if (startDate) d.startDate = startDate;
                    if (endDate) d.endDate = endDate;
                    if (brandId) d.brand_id = brandId;
                },
                dataSrc: function (json) {
                    return groupDataByAdSetId(json);
                }
            },
            stateSave: true,
            pageLength: 25,
            deferLoading: true,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            columns: [
                { title: "ID", data: "ad_set_id" },
                { title: "Ad Set Name", data: "ad_set_name" },
                { title: "Amount Spent", data: "total_amount_spent" },
                { title: "Content Views", data: "total_content_views" },
                { title: "Adds to Cart", data: "total_adds_to_cart" },
                { title: "Purchases", data: "total_purchases" },
                { title: "Purchase Value Conversion", data: "total_purchase_value" },
                { title: "Total Impressions", data: "total_impressions" }, // New column for Total Impressions
                { title: "Brand", data: "brand_id" },
                { title: "Marketplace", data: "market_place_id" },
                { title: "Action", defaultContent: '' }
            ],
            columnDefs: [
                {
                    targets: [2, 6],
                    render: function (data, type, row) {
                        return type === 'display' ? dataTableHelper.currency(data) : data;
                    }
                },
                {
                    targets: 8,
                    render: function (data, type, row) {
                        return type === 'display' ? dataTableHelper.translateBrand(data) : data;
                    }
                },
                {
                    targets: 9,
                    render: function (data, type, row) {
                        return type === 'display' ? dataTableHelper.translateMarketPlace(data) : data;
                    }
                },
                {
                    targets: 10,
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                        return `
                            <div class="dropdown">
                                <a class="text-reset fs-16 px-1" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ri-settings-4-line"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-animated">
                                    <li><a class="dropdown-item action-edit" data-id="${row.ad_set_id}" data-bs-toggle="modal" data-bs-target="#editModal" href="#" onclick="getAccounts('editModal');"><i class="ri-settings-3-line"></i> Edit</a></li>
                                    <li><a class="dropdown-item action-delete" data-id="${row.ad_set_id}" href="#"><i class="ri-delete-bin-2-line"></i> Delete</a></li>
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
            rowCallback: function (row, data) {
                // Ensure the row is always expandable
                $(row).find('td:not(:last-child)').off('click').on('click', function () {
                    let tr = $(this).closest('tr');
                    let row = dataTableInstance.row(tr);

                    if (row.child.isShown()) {
                        row.child.hide();
                        tr.removeClass('shown');
                    } else {
                        row.child(formatGroup(data.details)).show();
                        tr.addClass('shown');
                    }
                });
            }
        });
    }

    function groupDataByAdSetId(data) {
        const grouped = {};
        data.forEach(item => {
            if (!grouped[item.ad_set_id]) {
                grouped[item.ad_set_id] = {
                    ad_set_id: item.ad_set_id,
                    ad_set_name: item.ad_set_name,
                    total_amount_spent: 0,
                    total_content_views: 0,
                    total_adds_to_cart: 0,
                    total_purchases: 0,
                    total_purchase_value: 0,
                    total_impressions: 0, // Initialize total impressions
                    brand_id: item.brand_id,
                    market_place_id: item.market_place_id,
                    details: []
                };
            }
            grouped[item.ad_set_id].total_amount_spent += item.amount_spent;
            grouped[item.ad_set_id].total_content_views += item.content_views_with_shared_items;
            grouped[item.ad_set_id].total_adds_to_cart += item.adds_to_cart_with_shared_items;
            grouped[item.ad_set_id].total_purchases += item.purchases_with_shared_items;
            grouped[item.ad_set_id].total_purchase_value += item.purchases_conversion_value_for_shared_items_only;
            grouped[item.ad_set_id].total_impressions += item.impressions; // Aggregate impressions
            grouped[item.ad_set_id].details.push(item);
        });
        return Object.values(grouped);
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

    function fetchSummaryData(startDate, endDate, brandId) {
        const data = {};
        if (startDate) data.start_date = startDate;
        if (endDate) data.end_date = endDate;
        if (brandId) data.brand_id = brandId;

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
        fetchSummaryData();

        // Data Table
        initializeOrUpdateDataTable();
        initializeRefreshButton();
    }

    init();
});
