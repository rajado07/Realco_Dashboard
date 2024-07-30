import "datatables.net-bs5";
import DataTable from "datatables.net";
import initialize from "../helper/initialize";
import dataTableHelper from "../helper/dataTableHelper";
import 'daterangepicker/daterangepicker.js';

$.fn.dataTable = DataTable;

const csrfToken = $('meta[name="csrf-token"]').attr('content');

$(document).ready(() => {
    let dataTableInstance;

    function initializeOrUpdateDataTable(startDate = null, endDate = null, brandId = null) {
        const ajaxUrl = '/shopee/brand-portal-shop/read';
        const ajaxData = {};

        if (startDate && endDate) {
            ajaxData.start_date = startDate;
            ajaxData.end_date = endDate;
        }

        if (brandId) {
            ajaxData.brand_id = brandId;
        }

        if (dataTableInstance) {
            dataTableInstance.clear().destroy(); // Properly destroy the existing DataTable instance
        }

        dataTableInstance = $('#basic-datatable').DataTable({
            ajax: {
                url: ajaxUrl,
                type: 'GET',
                data: ajaxData,
                dataSrc: function (json) {
                    return json; // No re-mapping needed
                }
            },
            stateSave: true,
            pageLength: 25,
            deferLoading: true,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            columns: [
                { title: "Brand", data: "group_name" },
                { title: "Gross Sales", data: "gross_sales" },
                { title: "Gross Orders", data: "gross_orders" },
                { title: "Gross Units Sold", data: "gross_units_sold" },
                { title: "Product Views", data: "product_views" },
                { title: "Product Visitors", data: "product_visitors" },
                { title: "Average Basket Size", data: "average_basket_size" },
                { title: "Average Selling Price", data: "average_selling_price" },
                { title: "Conversion", data: "conversion" },
                { title: "Action", defaultContent: '' }
            ],
            columnDefs: [
                {
                    targets: [2, 3, 4, 5],
                    render: function (data, type, row) {
                        return dataTableHelper.columnWitheNowPreviousChange(data,false);
                    }
                },
                {
                    targets: [1, 6, 7],
                    render: function (data, type, row) {
                        return dataTableHelper.columnWitheNowPreviousChange(data,true);
                    }
                },
                {
                    targets: 8,
                    render: function (data, type, row) {
                        return dataTableHelper.columnWitheNowPreviousChange(data,false,false);
                    }
                },
                {
                    targets: 9,
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                        return `
                            <div class="dropdown">
                                <a class="text-reset fs-16 px-1" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ri-settings-4-line"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-animated">
                                    <li><a class="dropdown-item action-edit" data-id="${row.group_id}" data-bs-toggle="modal" data-bs-target="#editModal" href="#" onclick="getAccounts('editModal');"><i class="ri-settings-3-line"></i> Edit</a></li>
                                    <li><a class="dropdown-item action-delete" data-id="${row.group_id}" href="#"><i class="ri-delete-bin-2-line"></i> Delete</a></li>
                                </ul>
                            </div>
                        `;
                    }
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
                $('#basic-datatable_paginate').addClass('pagination-rounded');
                initialize.toolTip();
            },
        });

        // Add event listener for opening and closing details
        $('#basic-datatable tbody').off('click', 'td:not(:last-child)');
        $('#basic-datatable tbody').on('click', 'td:not(:last-child)', function () {
            var tr = $(this).closest('tr');
            var row = dataTableInstance.row(tr);

            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
            } else {
                row.child(formatGroup(row.data())).show();
                tr.addClass('shown');
            }
        });
    }

    function formatGroup(group) {
        let dataTable = `
        <div class="accordion accordion-flush" id="accordion-group-${group.group_id}">
        `;

        if (group.details && group.details.length > 0) {
            group.details.forEach(product => {
                dataTable += `
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading-product-${product.product_id}">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#product-section-${product.product_id}" aria-expanded="false" aria-controls="product-section-${product.product_id}">
                            ${product.product_name} (Product ID: ${product.product_id})
                        </button>
                    </h2>
                    <div id="product-section-${product.product_id}" class="accordion-collapse collapse" aria-labelledby="heading-product-${product.product_id}">
                        <div class="accordion-body">
                            ${createProductTable(product.historical_data)}
                        </div>
                    </div>
                </div>
                `;
            });
        }

        dataTable += `</div>`;
        return dataTable;
    }

    function createProductTable(data) {
        let tableId = 'product-table-' + Math.random().toString(36).substr(2, 9);
        let table = `<table id="${tableId}" class="table table-sm"><thead><tr>`;

        Object.keys(data[0]).forEach(function (key) {
            table += '<th>' + key + '</th>';
        });

        table += '</tr></thead><tbody>';

        data.forEach(function (item) {
            table += '<tr>';
            Object.values(item).forEach(function (value) {
                table += '<td>' + value + '</td>';
            });
            table += '</tr>';
        });

        table += '</tbody></table>';

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
            url: '/shopee/brand-portal-shop/summary',
            type: 'GET',
            data: data,
            success: function (response) {
                initialize.animateCounter($('#gross-sales'), response.total_gross_sales, true);
                initialize.animateCounter($('#gross-order'), response.total_gross_orders);
                initialize.animateCounter($('#gross-unit-sold'), response.total_gross_units_sold);
                initialize.animateCounter($('#average-basket-size'), response.average_basket_size, true);
                initialize.animateCounter($('#average-selling-price'), response.average_selling_price, true);
                initialize.updatePercentageChange($('#gross-sales-change-percentage'), response.gross_sales_change_percentage, startDate, endDate);
                initialize.updatePercentageChange($('#gross-order-change-percentage'), response.gross_orders_change_percentage, startDate, endDate);
                initialize.updatePercentageChange($('#gross-units-sold-change-percentage'), response.gross_units_sold_change_percentage, startDate, endDate);
                initialize.updatePercentageChange($('#average-basket-size-change-percentage'), response.average_basket_size_change_percentage, startDate, endDate);
                initialize.updatePercentageChange($('#average-selling-price-change-percentage'), response.average_selling_price_change_percentage, startDate, endDate);
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

            // Check if dates are selected
            const dates = selectedDate.split(' - ');
            let startDate, endDate;

            if (dates.length === 2) {
                startDate = initialize.formatDate(dates[0]);
                endDate = initialize.formatDate(dates[1]);
                console.log(`Start Date: ${startDate}`);
                console.log(`End Date: ${endDate}`);
            }

            console.log(`Input value changed to: ${selectedDate}`);
            console.log(`Selected Brand: ${selectedBrand}`);

            fetchSummaryData(startDate, endDate, selectedBrand);
            initializeOrUpdateDataTable(startDate, endDate, selectedBrand);

            if (!startDate || !endDate) {
                console.log('No dates available, using only brand filter.');
            }
        });
    }

    function fetchLatestRetrievedData() {
        $.ajax({
            url: '/shopee/brand-portal-shop/latest-data',
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
        initialize.dateTimePicker();
        initialize.toolTip();

        fetchBrands();
        fetchSummaryData();
        fetchLatestRetrievedData();

        initializeOrUpdateDataTable();
        initializeRefreshButton();
    }

    init();
});
