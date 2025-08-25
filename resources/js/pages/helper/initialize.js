import 'flatpickr/dist/flatpickr.js';

import toastr from 'toastr';

import tippy from 'tippy.js';
import 'tippy.js/dist/tippy.css'; // optional for styling

import jsPDF from 'jspdf';
import 'jspdf-autotable';

export default (function ($) {
  "use strict";

  function dateTimePicker() {
    $('.datetime-datepicker').flatpickr({
      enableTime: true,
      dateFormat: "Y-m-d H:i:S", // Format tanggal dan waktu
      time_24hr: true // Format 24 jam
    });
  }

  function toolTip() {
    tippy('[data-tipsy]', {
      content(reference) {
        // Ambil teks full, decode dari URI component
        const encoded = reference.getAttribute('data-tipsy') || '';
        const full = decodeURIComponent(encoded);

        // Ganti newline menjadi <br> agar terlihat semua baris
        const html = full.replace(/\r\n|\r|\n/g, '<br>');
        return `<div class="tippy-content">${html}</div>`;
      },
      allowHTML: true,
      theme: 'light',
      animation: 'scale',
      interactive: true,
      delay: [750, 0],
      maxWidth: 1200, // boleh disesuaikan
      onShow(instance) {
        const tooltipElement = instance.popper;
        // Hindari multiple listener: bersihkan dulu jika perlu
        tooltipElement.addEventListener('click', function () {
          const encoded = instance.reference.getAttribute('data-tipsy') || '';
          const textToCopy = decodeURIComponent(encoded);
          copyToClipboard(textToCopy);
          toast({ type: 'success', message: 'Copied To Clipboard' });
        }, { once: true });
      }
    });
  }


  function copyToClipboard(text) {
    const textArea = document.createElement("textarea");
    textArea.style.position = "fixed";
    textArea.style.opacity = "0";
    textArea.value = text;
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    try {
      document.execCommand('copy');
    } catch (err) {
      console.error('Unable to copy', err);
    }
    document.body.removeChild(textArea);
  }


  function formatDate(dateString) {
    const date = new Date(dateString);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0'); // Bulan dimulai dari 0
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  }


  function toast(response) {
    toastr.options = {
      "closeButton": true,
      "debug": false,
      "newestOnTop": false,
      "progressBar": true,
      "positionClass": "toast-bottom-center",
      "preventDuplicates": false,
      "onclick": null,
      "showDuration": "300",
      "hideDuration": "1000",
      "timeOut": "5000",
      "extendedTimeOut": "1000",
      "showEasing": "swing",
      "hideEasing": "linear",
      "showMethod": "fadeIn",
      "hideMethod": "fadeOut"
    }

    const messageType = response.type || 'warning';

    switch (messageType) {
      case 'success':
        toastr.success(response.message);
        break;
      case 'error':
        toastr.error(response.message);
        break;
      case 'warning':
        toastr.warning(response.message);
        break;
      case 'info':
        toastr.info(response.message);
        break;
      default:
        toastr.warning(response.message);
        break;
    }
  }

  function animateCounter($element, endValue, isCurrency = false, duration = 1000, decimals = 0) {
    if (!$element.length) return;

    let startValue = parseFloat($element.text().replace(/[^0-9.-]+/g, '') || '0');
    const startTimestamp = performance.now();
    const numberFormatOptions = isCurrency ? {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
      maximumFractionDigits: 2
    } : {
      minimumFractionDigits: 0,
      maximumFractionDigits: decimals
    };
    const numberFormatter = new Intl.NumberFormat('id-ID', numberFormatOptions);

    const getScale = (value) => {
      if (value >= 1e12) return 'T'; // Trillions
      if (value >= 1e9) return 'B'; // Billions
      if (value >= 1e6) return 'M'; // Millions
      if (value >= 1e3) return 'K'; // Thousands
      return ''; // No scale
    };

    const step = (currentTimestamp) => {
      const progress = Math.min((currentTimestamp - startTimestamp) / duration, 1);
      const currentValue = progress * (endValue - startValue) + startValue;
      let displayValue = numberFormatter.format(currentValue);
      let scale = '';

      if (isCurrency) {
        scale = getScale(currentValue);
      }

      $element.text(displayValue);
      if (scale) {
        $element.append(`<span class="scale"> ${scale}</span>`);
      }

      if (progress < 1) {
        window.requestAnimationFrame(step);
      }
    };
    window.requestAnimationFrame(step);
  }

  function animateCounterDuration($element, endValue, duration = 1000) {
    if (!$element.length) return;

    let startValue = parseFloat($element.text().replace(/[^0-9.-]+/g, '') || '0');
    const startTimestamp = performance.now();

    const convertSecondsToHHMM = (seconds) => {
      const totalHours = Math.floor(seconds / 3600);
      const totalMinutes = Math.floor((seconds % 3600) / 60);
      return `${totalHours}H ${totalMinutes}M`;
    };

    const step = (currentTimestamp) => {
      const progress = Math.min((currentTimestamp - startTimestamp) / duration, 1);
      const currentValue = progress * (endValue - startValue) + startValue;
      const displayValue = convertSecondsToHHMM(currentValue);

      $element.text(displayValue);

      if (progress < 1) {
        window.requestAnimationFrame(step);
      }
    };
    window.requestAnimationFrame(step);
  }


  function updatePercentageChange(element, changePercentage, startDate, endDate) {
    element.empty(); // Clear any existing content

    // Set class and icon based on the value
    let iconHtml = '';
    if (changePercentage > 0) {
      element.removeClass('bg-danger bg-secondary').addClass('bg-success');
      iconHtml = '<i class="ri-arrow-up-line"></i> ';
    } else if (changePercentage < 0) {
      element.removeClass('bg-success bg-secondary').addClass('bg-danger');
      iconHtml = '<i class="ri-arrow-down-line"></i> ';
    } else {
      element.removeClass('bg-success bg-danger').addClass('bg-secondary');
    }

    // Append the icon and percentage change
    element.html(iconHtml + `${Math.abs(changePercentage).toFixed(2)}%`);

    // Determine the interval description
    let intervalDescription = 'Since last month';  // Default value

    if (startDate && endDate) {
      const intervalDays = Math.ceil((new Date(endDate) - new Date(startDate)) / (1000 * 60 * 60 * 24)) + 1;

      if (intervalDays === 1) {
        intervalDescription = 'Since yesterday';
      } else if (intervalDays === 0) {
        intervalDescription = 'Since today';
      } else if (intervalDays % 7 === 0) {
        const weeks = intervalDays / 7;
        intervalDescription = weeks > 1 ? `Since last ${weeks} weeks` : 'Since last week';
      } else if (intervalDays % 30 === 0) {
        const months = intervalDays / 30;
        intervalDescription = months > 1 ? `Since last ${months} months` : 'Since last month';
      } else {
        intervalDescription = `Since last ${intervalDays} days`;
      }
    }

    // Remove any existing interval description element
    element.next('.interval-description').remove();

    // Create a new span element for the interval description
    const intervalElement = $('<span>').addClass('text-muted interval-description').text(intervalDescription);

    // Append the interval description span after the main element
    element.after(intervalElement);
  }

  function updatePercentageChangeLiveSteraming(element, changePercentage) {
    element.empty(); // Clear any existing content

    // Set class and icon based on the value
    let iconHtml = '';
    if (changePercentage > 0) {
      element.removeClass('bg-danger bg-secondary').addClass('bg-success');
      iconHtml = '<i class="ri-arrow-up-line"></i> ';
    } else if (changePercentage < 0) {
      element.removeClass('bg-success bg-secondary').addClass('bg-danger');
      iconHtml = '<i class="ri-arrow-down-line"></i> ';
    } else {
      element.removeClass('bg-success bg-danger').addClass('bg-secondary');
    }

    // Append the icon and percentage change
    element.html(iconHtml + `${Math.abs(changePercentage).toFixed(2)}%`);

    // Remove any existing interval description element
    element.next('.interval-description').remove();

    // Create a new span element for the average change description
    const intervalElement = $('<span>').addClass('text-muted interval-description').text('Average change');

    // Append the average change description span after the main element
    element.after(intervalElement);
  }


  const initialise = {
    dateTimePicker,
    toolTip,
    toast,
    animateCounter,
    animateCounterDuration,
    formatDate,
    updatePercentageChange,
    updatePercentageChangeLiveSteraming,


  };

  $.initialise = initialise;

  return initialise;

})(jQuery);
