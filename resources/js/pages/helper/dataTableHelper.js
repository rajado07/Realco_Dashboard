function translateStatusTask(status) {
  const statusMap = {
    1: '<span class="badge bg-secondary text-light rounded-pill">Ready</span>',
    2: '<span class="badge bg-info rounded-pill">Wait For Running</span>',
    3: '<span class="badge bg-info rounded-pill">Running</span>',
    4: '<span class="badge bg-warning rounded-pill">Exeption</span>',
    5: '<span class="badge bg-success rounded-pill">Completed</span>',
  };
  return statusMap[parseInt(status, 10)] || '<span class="badge bg-dark rounded-pill">Unknown</span>';
}

function translateStatusTaskGenerator(status) {
  const statusMap = {
    1: '<span class="badge bg-success text-light rounded-pill">Active</span>',
    2: '<span class="badge bg-secondary text-light rounded-pill">Deactive</span>',
  };
  return statusMap[parseInt(status, 10)] || '<span class="badge bg-dark rounded-pill">Unknown</span>';
}

function translateStatusRawData(status) {
  const statusMap = {
    1: '<span class="badge bg-secondary text-light rounded-pill">Ready</span>',
    2: '<span class="badge bg-success rounded-pill">Data Moved</span>',
    3: '<span class="badge bg-success rounded-pill">Partial Moved</span>',
    4: '<span class="badge bg-warning rounded-pill">Partial Failed</span>',
    5: '<span class="badge bg-danger rounded-pill">Failed</span>',
  };
  return statusMap[parseInt(status, 10)] || '<span class="badge bg-dark rounded-pill">Unknown</span>';
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
  };
  return brandMap[parseInt(brand, 10)] || '<span class="badge bg-dark rounded-pill">Unknown</span>';
}

function translateMarketPlace(brand) {
  const brandMap = {
    1: '<i class="bi bi-stripe"></i><span> Shopee</span>',
    2: '<i class="ri-tiktok-line"></i><span> Tiktok</span>',
    3: '<i class="ri-shopping-cart-line"></i><span> Tokopedia</span>',
    4: '<i class="ri-shopping-bag-3-line"></i><span> Lazada</span>',
  };
  return brandMap[parseInt(brand, 10)] || '<span class="badge bg-dark rounded-pill">Unknown</span>';
}

function shortenText(data, maxLength = 50) {
  if (data?.length > maxLength) {
    // Mengembalikan string HTML dengan data-tipsy attribute
    return `<span data-tipsy="${data}">${data.substr(0, maxLength)}...</span>`;
  } else {
    return data;
  }
}

function formatSchedule(schedule, locale = 'en-US') {
  try {
    const date = new Date(schedule);
    if (isNaN(date.getTime())) throw new Error('Invalid date');
    return `${new Intl.DateTimeFormat(locale, { hour: '2-digit', minute: '2-digit', hour12: false }).format(date)} ${new Intl.DateTimeFormat(locale, { year: 'numeric', month: 'long', day: 'numeric' }).format(date)}`;
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
  const change = data.change;
  let badgeColor = 'bg-secondary'; // Default color for no change
  let changeSymbol = '';

  if (change > 0) {
    badgeColor = 'bg-success';
    changeSymbol = '+';
  } else if (change < 0) {
    badgeColor = 'bg-danger';
  }

  // Memformat nilai sekarang berdasarkan apakah ini mata uang atau tidak
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

  // Jika tidak ingin menampilkan perubahan persentase, kembalikan hanya nilai sekarang
  if (!showChange) {
    return `<span>${formattedNow}</span>`;
  }

  return `
      <span>${formattedNow}</span>
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
  shortenText,
  formatSchedule,
  checkAll,
  shiftSelection,
  toggleSettings,
  currency,
};
