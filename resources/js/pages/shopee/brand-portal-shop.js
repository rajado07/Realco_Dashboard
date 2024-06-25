import "datatables.net-bs5";
import DataTable from "datatables.net";
import initialize from "../helper/initialize";
import dataTableHelper from "../helper/dataTableHelper";

$.fn.dataTable = DataTable;

const csrfToken = $('meta[name="csrf-token"]').attr('content');

$(document).ready(() => {
    let dataTableInstance;
    function initializeOrUpdateDataTable() {
        if (!dataTableInstance) {
            dataTableInstance = $('#basic-datatable').DataTable({
                ajax: {
                    url: '/shopee/brand-portal-shop/aggregate',
                    type: 'GET',
                    dataSrc: function (json) {
                        return json.map(group => ({
                            group_id: group.group_id,
                            group_name: group.group_name,
                            total_gross_sales: group.total_gross_sales,
                            total_gross_orders: group.total_gross_order,
                            total_gross_units_sold: group.total_gross_units_sold,
                            total_product_views: group.total_product_views,
                            total_product_visitors: group.total_product_visitors,
                            brand_id: group.brand_id,
                            average_basket_size: group.average_basket_size,
                            average_selling_price: group.average_selling_price,
                            details: group.details,
                        }));
                    }
                },
                stateSave: true,
                pageLength: 25,
                deferLoading: true,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                columns: [
                    { title: "Group Name", data: "group_name" },
                    { title: "Total Gross Sales", data: "total_gross_sales" },
                    { title: "Total Gross Orders", data: "total_gross_orders" },
                    { title: "Total Gross Units Sold", data: "total_gross_units_sold" },
                    { title: "Total Product Views", data: "total_product_views" },
                    { title: "Total Product Visitors", data: "total_product_visitors" },
                    { title: "Average Basket Size", data: "average_basket_size" },
                    { title: "Average Selling Price", data: "average_selling_price" },
                    { title: "Brand ID", data: "brand_id" },
                    { title: "Action", defaultContent: '' }
                ],
                columnDefs: [
                    {
                        targets: [1,6,7],
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
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
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

            // Add event listener for opening and closing details
            $('#basic-datatable tbody').on('click', 'td:not(:last-child)', function () {
                var tr = $(this).closest('tr');
                var row = dataTableInstance.row(tr);

                if (row.child.isShown()) {
                    // This row is already open - close it
                    row.child.hide();
                    tr.removeClass('shown');
                } else {
                    // Open this row
                    row.child(formatGroup(row.data())).show();
                    tr.addClass('shown');
                }
            });

        } else {
            dataTableInstance.ajax.reload();
        }
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
        let table = `<table id="${tableId}" class="table table-striped table-sm"><thead><tr>`;

        // Add table headers based on the keys of the first object
        Object.keys(data[0]).forEach(function (key) {
            table += '<th>' + key + '</th>';
        });

        table += '</tr></thead><tbody>';

        // Add table rows based on the values of each object
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
            }); // Initialize DataTable for nested table with no pagination, search, or info
        }, 0);

        return table;
    }

    function init() {
        // Initialise External Helper
        initialize.dateTimePicker();
        initialize.toolTip();

        // Data Table
        initializeOrUpdateDataTable();

    }
    init();
});
