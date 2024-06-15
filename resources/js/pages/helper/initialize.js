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
        return reference.getAttribute('data-tipsy');
      },
      theme: 'light',
      animation: 'scale',
      allowHTML: true,
      interactive: true, // Membuat tooltip bisa diklik
      delay: [750, 0],
    });
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

  function animateCounter($element, endValue, duration = 1000) {
    if (!$element.length) return; 

    let startValue = parseInt($element.text().replace(/,/g, '') || '0', 10); // Mulai dari nilai saat ini
    const startTimestamp = performance.now();
    
    const step = (currentTimestamp) => {
        const progress = Math.min((currentTimestamp - startTimestamp) / duration, 1);
        const currentValue = Math.floor(progress * (endValue - startValue) + startValue);
        $element.text(currentValue.toLocaleString('en'));
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
  }

  const initialise = {
    dateTimePicker,
    toolTip,
    toast, 
    animateCounter,

  };

  $.initialise = initialise;
  
  return initialise;

})(jQuery);
