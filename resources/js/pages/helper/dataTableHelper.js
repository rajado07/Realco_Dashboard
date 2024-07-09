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

function currency(data) {
  const formattedCurrency = new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0 // Sesuaikan desimal sesuai kebutuhan
  }).format(data);

  return `<span class="badge bg-success rounded-pill">${formattedCurrency}</span>`;
}

function columnWithChangePercentage(data, isCurrency = false) {
  const now = data.now;
  const change = data.change;
  const badgeColor = change >= 0 ? 'bg-success' : 'bg-danger';
  const changeSymbol = change >= 0 ? '+' : '';

  // Fungsi untuk format mata uang IDR dengan K, M, B, T
  function formatCurrency(value) {
    if (value >= 1e12) return (value / 1e12).toFixed(2) + 'T';
    if (value >= 1e9) return (value / 1e9).toFixed(2) + 'B';
    if (value >= 1e6) return (value / 1e6).toFixed(2) + 'M';
    if (value >= 1e3) return (value / 1e3).toFixed(2) + 'K';
    return value.toFixed(2);
  }

  // Fungsi untuk memastikan angka memiliki dua digit di belakang koma
  function formatNumber(value) {
    return parseFloat(value).toFixed(2);
  }

  // Memformat nilai sekarang berdasarkan apakah ini mata uang atau tidak
  let formattedNow = '';
  if (typeof now === 'number') {
    if (isCurrency) {
      formattedNow = formatCurrency(now);
    } else {
      formattedNow = formatNumber(now);
    }
  } else {
    formattedNow = now;
  }

  return `
      <span class="badge ${badgeColor}">${formattedNow}</span>
      <span>${changeSymbol}${change.toFixed(2)}%</span>
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
  columnWithChangePercentage,
  shortenText,
  formatSchedule,
  checkAll,
  shiftSelection,
  toggleSettings,
  currency,
};
