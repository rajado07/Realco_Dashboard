function translateStatusTask(status, rowId) {
  const statusMap = {
    1: '<span class="remixicon ri-checkbox-circle-line text-secondary"></span> Ready',
    2: '<span class="remixicon ri-time-line text-info"></span> Wait For Running',
    3: '<span class="remixicon ri-play-circle-line text-info"></span> Running',
    4: `<span style="display: flex; align-items: center; cursor: pointer;" onclick="showExceptionModal(${rowId});">
          <i class="remixicon ri-error-warning-line text-warning" style="font-size: 1.5em; margin-right: 0.5em;"></i> 
          Exception
        </span>`,
    5: '<span class="remixicon ri-checkbox-circle-line text-success"></span> Completed',
    6: '<span class="remixicon ri-checkbox-circle-line text-warning"></span> Partial Completed',
    7: '<span class="remixicon ri-checkbox-circle-line text-danger"></span> Partial Failed',
    8: '<span class="remixicon ri-close-circle-line text-danger"></span> Failed',
    9: '<span class="remixicon ri-checkbox-circle-line text-info"></span> All Skipped',
  };

  // Convert status to integer if it's not already
  const statusInt = parseInt(status, 10);

  // Check if the status exists in the map, otherwise return the default 'Unknown' status
  return statusMap[statusInt] || '<span class="remixicon ri-question-line text-dark"></span> Unknown';
}
function translateStatusTaskGenerator(status) {
  const statusMap = {
    1: '<i class="remixicon ri-checkbox-circle-line text-success"></i> Active',
    2: '<i class="remixicon ri-close-circle-line text-secondary"></i> Deactive',
  };
  return statusMap[parseInt(status, 10)] || '<i class="remixicon ri-question-line text-dark"></i> Unknown';
}

function translateStatusRawData(status) {
  const statusMap = {
    1: '<i class="remixicon ri-checkbox-circle-line text-secondary"></i> Ready',
    2: '<i class="remixicon ri-database-2-line text-success"></i> Data Moved',
    3: '<i class="remixicon ri-database-2-line text-warning"></i> Partial Moved',
    4: '<i class="remixicon ri-database-2-line text-danger"></i> Partial Failed',
    5: '<i class="remixicon ri-close-circle-line text-danger"></i> Failed',
    6: '<i class="remixicon ri-database-2-line text-info"></i> All Skipped',
  };
  return statusMap[parseInt(status, 10)] || '<i class="remixicon ri-question-line text-dark"></i> Unknown';
}

function translateStatusLog(status) {
  const statusMap = {
    INFO: '<span class="badge bg-info rounded-pill">INFO</span>',
    DANGER: '<span class="badge bg-danger rounded-pill">DANGER</span>',
  };
  return statusMap[status] || '<span class="badge bg-dark rounded-pill">Unknown</span>';
}

function translateBrand(brand) {
  const brandMap = {
    1: '<span class="badge rounded-pill text-light" style="background-color: #20a3a9;">Realfood</span>',
    2: '<span class="badge rounded-pill text-dark" style="background-color: #f1e5b3;">Skindoze</span>',
    3: '<span class="badge rounded-pill text-light" style="background-color: #9e8ad6;">Elora</span>',
    4: '<span class="badge rounded-pill text-light" style="background-color: #a24688;">Odoo</span>',
  };
  return brandMap[parseInt(brand, 10)] || '<span class="badge bg-dark rounded-pill">Unknown</span>';
}

function translateMarketPlace(brand) {
  const brandMap = {
    1: '<i class="bi bi-stripe"></i><span> Shopee</span>',
    2: '<i class="ri-tiktok-line"></i><span> Tiktok</span>',
    3: '<i class="ri-shopping-cart-line"></i><span> Tokopedia</span>',
    4: '<i class="ri-shopping-bag-3-line"></i><span> Lazada</span>',
    5: '<i class="ri-shopping-bag-fill"></i><span> Odoo</span>',
    6: '<i class="ri-whatsapp-line"></i><span> Whatsapp</span>',
  };
  return brandMap[parseInt(brand, 10)] || '<span class="badge bg-secondary rounded-pill">No Market Place</span>';
}

function shortenText(data, maxLength = 50) {
  if (data?.length > maxLength) {
    // Mengembalikan string HTML dengan data-tipsy attribute
    return `<span data-tipsy="${data}">${data.substr(0, maxLength)}...</span>`;
  } else {
    return data;
  }
}

function formatSchedule(schedule) {
  try {
    const date = new Date(schedule);
    if (isNaN(date.getTime())) throw new Error('Invalid date');

    // Format tanggal sebagai YYYY-MM-DD
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    // Format waktu sebagai HH:MM
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');

    // Gabungkan tanggal dan waktu
    const formattedDate = `${year}-${month}-${day} ${hours}:${minutes}`;

    // Ikon Remix untuk jam dan kalender
    const clockIcon = '<i class="ri-time-line"></i>';
    const calendarIcon = '<i class="ri-calendar-line"></i>';

    return `${calendarIcon} ${year}-${month}-${day} ${clockIcon} ${hours}:${minutes}`;
  } catch (error) {
    console.error(error);
    return "Invalid date";
  }
}


function formatCurrencyWithUnits(value) {
  let unit = '';
  if (value >= 1e12) {
    value = (value / 1e12).toFixed(2);
    unit = 'T';
  } else if (value >= 1e9) {
    value = (value / 1e9).toFixed(2);
    unit = 'B';
  } else if (value >= 1e6) {
    value = (value / 1e6).toFixed(2);
    unit = 'M';
  } else if (value >= 1e3) {
    value = (value / 1e3).toFixed(2);
    unit = 'K';
  }
  return { value, unit };
}

function formatNumber(value) {
  // Memformat angka dengan pemisah ribuan dan dua digit desimal jika ada nilai desimal
  const options = value % 1 !== 0 ? { minimumFractionDigits: 2, maximumFractionDigits: 2 } : {};
  return new Intl.NumberFormat('en-US', options).format(value);
}

function formatFloat(number) {
  return number.toFixed(2);
}

function currency(data) {
  const formattedCurrency = new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0 // Sesuaikan desimal sesuai kebutuhan
  }).format(data);

  // Gunakan format mata uang IDR dengan K, M, B, T
  const { value, unit } = formatCurrencyWithUnits(data);

  return `<span class="badge bg-success rounded-pill">${formattedCurrency} ${unit}</span>`;
}

function columnWitheNowPreviousChange(data, isCurrency = false, showChange = true) {
  const now = data.now;
  const previous = data.previous || ''; // Default value if previous is undefined
  const change = data.change;
  let badgeColor = 'bg-secondary'; // Default color for no change
  let changeSymbol = '';

  if (change > 0) {
    badgeColor = 'bg-success';
    changeSymbol = '+';
  } else if (change < 0) {
    badgeColor = 'bg-danger';
  }

  // Format current value
  let formattedNow = '';
  if (typeof now === 'number') {
    if (isCurrency) {
      const { value, unit } = formatCurrencyWithUnits(now);
      formattedNow = `${value} ${unit}`;
    } else {
      formattedNow = formatNumber(now);
    }
  } else {
    formattedNow = now;
  }

  // Format previous value
  let formattedPrevious = '';
  if (typeof previous === 'number') {
    if (isCurrency) {
      const { value, unit } = formatCurrencyWithUnits(previous);
      formattedPrevious = `${value} ${unit}`;
    } else {
      formattedPrevious = formatNumber(previous);
    }
  } else {
    formattedPrevious = previous;
  }

  // Jika tidak ingin menampilkan perubahan persentase, kembalikan hanya nilai sekarang dengan tooltip
  if (!showChange) {
    return `
      <span data-tipsy="Previous: ${formattedPrevious}" class="has-tooltip">
        ${formattedNow}
      </span>
    `;
  }

  // Tampilkan nilai sekarang, perubahan persentase, dan tooltip untuk nilai sebelumnya
  return `
      <span data-tipsy="Previous: ${formattedPrevious}" class="has-tooltip">
        ${formattedNow}
      </span>
      <span class="badge ${badgeColor}">${changeSymbol}${change.toFixed(2)}%</span>
  `;
}

function columnSummary(data, type) {
  let formattedNow = '';
  let badgeColor = 'bg-secondary'; // Default color for no change

  switch (type) {
    case 'percentage':
      // Memastikan data adalah string dengan simbol '%'
      const percentageValue = parseFloat(data);
      formattedNow = data;
      if (percentageValue > 0) {
        badgeColor = 'bg-success';
      } else if (percentageValue < 0) {
        badgeColor = 'bg-danger';
      }
      return `<span class="badge ${badgeColor}">${formattedNow}</span>`;
    case 'integer':
      formattedNow = formatNumber(data);
      break;
    case 'float':
      formattedNow = formatFloat(data);
      break;
    case 'currency':
      const { value, unit } = formatCurrencyWithUnits(data);
      formattedNow = `${value} ${unit}`;
      break;
    default:
      formattedNow = data; // Default behavior jika tipe tidak dikenali
  }

  return `<span>${formattedNow}</span>`;
}

function columnSummaryV2(data, type = 'integer') {
  if (!data) return '';

  // Format hanya untuk tipe currency
  const formatValue = (value, type) => {
    if (type === 'currency') {
      return `Rp${parseFloat(value).toLocaleString('id-ID')}`;
    }
    return value; // Jika bukan currency, kembalikan nilai aslinya
  };

  // Determine the appropriate class and icon for growth
  const growthClass = data.growth.includes('-')
    ? 'text-danger' // Red for negative growth
    : data.growth === '0.00%'
      ? 'text-muted' // Gray for zero growth
      : 'text-success'; // Green for positive growth

  const growthIcon = data.growth.includes('-')
    ? 'bi-arrow-down-circle-fill' // Down arrow for negative growth
    : data.growth === '0.00%'
      ? 'bi-dash-circle-fill' // Neutral icon for zero growth
      : 'bi-arrow-up-circle-fill'; // Up arrow for positive growth

  return `
    <div class="d-flex justify-content-center align-items-center">
      <div class="current-value fs-4 me-2">${formatValue(data.first_period, type)}</div>
      <div class="d-flex align-items-center">
          <div class="previous-value me-2 text-muted fs-6">${formatValue(data.second_period, type)}</div>
          <div class="change-value d-flex align-items-center">
              <i class="bi ${growthIcon} ${growthClass} me-1"></i>
              <span class="${growthClass}">${data.growth}</span>
          </div>
      </div>
    </div>
  `;
}

function columnSummaryV3(data, type = 'integer') {
  if (!data) return '';

  // Format hanya untuk tipe currency
  const formatValue = (value, type) => {
    if (type === 'currency') {
      return `Rp${parseFloat(value).toLocaleString('id-ID')}`;
    }
    return value; // Jika bukan currency, kembalikan nilai aslinya
  };

  // Tambahkan tanda + atau - pada growth jika bukan 0.00%
  const formattedGrowth = data.growth === '0.00%' ? data.growth : (data.growth.startsWith('-') ? data.growth : `+${data.growth}`);

  // Tentukan kelas untuk pertumbuhan
  const growthClass = data.growth.includes('-')
    ? 'text-danger' // Merah untuk pertumbuhan negatif
    : data.growth === '0.00%'
      ? 'text-muted' // Abu-abu untuk pertumbuhan nol
      : 'text-success'; // Hijau untuk pertumbuhan positif

  return `
    <div class="d-flex align-items-center summary-container" data-tipsy="${formatValue(data.second_period, type)}">
      <div class="current-value me-2">${formatValue(data.first_period, type)}</div>
      <span class="${growthClass}">${formattedGrowth}</span>
    </div>
  `;
}




function checkAll() {
  $(document).on('change', '#checkAll', function () {
    $('.rowCheckbox').prop('checked', $(this).prop('checked')).change();
  });
}

function shiftSelection() {
  let lastChecked = null;

  $(document).on('click', '.rowCheckbox', function (e) {
    if (!lastChecked) {
      lastChecked = this;
      return;
    }

    if (e.shiftKey) {
      const start = $('.rowCheckbox').index(this);
      const end = $('.rowCheckbox').index(lastChecked);

      $('.rowCheckbox').slice(Math.min(start, end), Math.max(start, end) + 1)
        .prop('checked', lastChecked.checked);
    }

    lastChecked = this;
  });
}

function toggleSettings() {
  $('#basic-datatable').on('change', '.rowCheckbox, #checkAll', function () {
    var anyRowChecked = $('#basic-datatable .rowCheckbox:checked').length > 0;

    if (anyRowChecked) {
      $('#settings').fadeIn(500);
    } else {
      $('#settings').fadeOut(500);
    }

    if ($(this).is('#checkAll')) {
      $('.rowCheckbox').prop('checked', this.checked);
    }
  });
}

export default {
  translateStatusTask,
  translateStatusTaskGenerator,
  translateStatusLog,
  translateStatusRawData,
  translateBrand,
  translateMarketPlace,
  columnWitheNowPreviousChange,
  columnSummary,
  columnSummaryV2,
  columnSummaryV3,
  shortenText,
  formatSchedule,
  checkAll,
  shiftSelection,
  toggleSettings,
  currency,
};
