/* Shared UI helpers for PenakeTik. */
(function () {
  'use strict';

  var timer = null;

  window.pkToast = function (message, isError) {
    var el = document.getElementById('toast');
    if (!el) {
      return;
    }

    el.textContent = message;
    el.classList.toggle('error', Boolean(isError));
    el.classList.add('show');

    window.clearTimeout(timer);
    timer = window.setTimeout(function () {
      el.classList.remove('show');
    }, 3200);
  };

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
      form.addEventListener('submit', function (event) {
        if (!window.confirm(form.getAttribute('data-confirm'))) {
          event.preventDefault();
        }
      });
    });
  });
})();
