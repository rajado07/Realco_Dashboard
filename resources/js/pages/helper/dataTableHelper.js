function translateStatus(status) {
    const statusMap = {
        1: '<span class="badge bg-secondary text-light rounded-pill">Ready</span>',
        2: '<span class="badge bg-info rounded-pill">Wait For Running</span>',
        3: '<span class="badge bg-success rounded-pill">Running</span>',
        4: '<span class="badge bg-warning rounded-pill">Exeption</span>',
        5: '<span class="badge bg-info rounded-pill">Wait For Stopped</span>',
        6: '<span class="badge bg-danger rounded-pill">Stopped</span>',
        7: '<span class="badge bg-info rounded-pill">Wait For Reboot</span>',
        8: '<span class="badge bg-info rounded-pill">Rebooting</span>',

        93: '<span class="badge bg-danger rounded-pill">Offline</span>',
        94: '<span class="badge bg-success rounded-pill">Online</span>',
        95: '<span class="badge bg-warning rounded-pill">Appium</span>',
        96: '<span class="badge bg-warning rounded-pill">Script</span>',
        97: '<span class="badge bg-warning text-dark rounded-pill">Maintenance</span>',
        98: '<span class="badge bg-dark rounded-pill">Not Found</span>',
        99: '<span class="badge bg-light text-dark rounded-pill">Completed</span>',
        100: '<span class="badge bg-dark rounded-pill">Archived</span>',
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


function checkAll() {
  $(document).on('change', '#checkAll', function() {
      $('.rowCheckbox').prop('checked', $(this).prop('checked')).change(); 
  });
}

function shiftSelection() {
    let lastChecked = null;
    
    $(document).on('click', '.rowCheckbox', function(e) {
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
  $('#basic-datatable').on('change', '.rowCheckbox, #checkAll', function() {
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
    translateStatus,
    translateStatusLog,
    translateStatusRawData,
    shortenText,
    formatSchedule,
    checkAll,
    shiftSelection,
    toggleSettings,
    currency,
};
