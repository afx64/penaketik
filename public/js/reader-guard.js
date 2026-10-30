/*
 * Reader guard for PenakeTik.
 *
 * Honest scope: this raises the cost of casual copying (right click, text
 * selection, Ctrl+U, printing, F12-style shortcuts, devtools sniffing) and
 * stamps every view with a per-reader watermark. No website can stop a
 * determined person from taking a screenshot or reading the HTML they were
 * served; the watermark is what makes a leaked copy traceable.
 */
(function () {
  'use strict';

  var root = document.querySelector('[data-protected]');
  if (!root) {
    return;
  }

  var veil = document.getElementById('protection-veil');
  var toast = document.getElementById('protection-toast');
  var toastTimer = null;

  function warn(message) {
    if (!toast) {
      return;
    }

    toast.textContent = message;
    toast.classList.add('show');
    window.clearTimeout(toastTimer);
    toastTimer = window.setTimeout(function () {
      toast.classList.remove('show');
    }, 2800);
  }

  function showVeil(message) {
    if (!veil) {
      return;
    }

    var target = veil.querySelector('[data-veil-message]');
    if (message && target) {
      target.textContent = message;
    }

    veil.hidden = false;
    document.body.style.overflow = 'hidden';
  }

  function hideVeil() {
    if (!veil) {
      return;
    }

    veil.hidden = true;
    document.body.style.overflow = '';
  }

  /* A tiled watermark of who this copy was served to. */
  var watermark = root.getAttribute('data-watermark') || '';

  if (watermark) {
    var safe = watermark.replace(/[<>&"']/g, '');
    var svg = '<svg xmlns="http://www.w3.org/2000/svg" width="440" height="280">'
      + '<text x="12" y="150" fill="rgba(27,25,23,0.08)" font-family="Georgia, serif" font-size="15"'
      + ' transform="rotate(-22 220 140)">' + safe + '</text></svg>';

    var layer = document.createElement('div');
    layer.className = 'reader-watermark';
    layer.setAttribute('aria-hidden', 'true');
    layer.style.backgroundImage = 'url("data:image/svg+xml;charset=utf-8,' + encodeURIComponent(svg) + '")';
    root.appendChild(layer);
  }

  /* Block the usual copy gestures. Form controls stay usable. */
  ['contextmenu', 'copy', 'cut', 'dragstart', 'selectstart'].forEach(function (type) {
    document.addEventListener(type, function (event) {
      if (event.target && event.target.closest && event.target.closest('input, textarea, select')) {
        return;
      }

      event.preventDefault();

      if (type === 'contextmenu' || type === 'copy' || type === 'cut') {
        warn('Copying is disabled. This copy is licensed to you.');
      }

      if (type === 'dragstart' && event.dataTransfer) {
        event.dataTransfer.setData('text/plain', '');
      }
    }, true);
  });

  function clearClipboard() {
    try {
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(' ').catch(function () {});
      }
    } catch (error) {
      /* Clipboard access can be denied; nothing else to do. */
    }
  }

  /* Block devtools and "save / print / view source" shortcuts. */
  document.addEventListener('keydown', function (event) {
    var key = (event.key || '').toLowerCase();
    var combo = event.ctrlKey || event.metaKey;
    var blocked = key === 'f12'
      || (combo && event.shiftKey && ['i', 'j', 'c', 'k'].indexOf(key) !== -1)
      || (combo && ['u', 's', 'p', 'a'].indexOf(key) !== -1);

    if (blocked) {
      event.preventDefault();
      event.stopPropagation();
      warn('That shortcut is disabled on protected chapters.');
      return;
    }

    if (key === 'printscreen' || event.code === 'PrintScreen') {
      clearClipboard();
      warn('Screenshots are not permitted. This page is watermarked for you.');
    }
  }, true);

  window.addEventListener('beforeprint', function () {
    showVeil('Printing is disabled on protected chapters.');
  });

  window.addEventListener('afterprint', hideVeil);

  /*
   * Devtools detection by window gap. Deliberately conservative: it needs two
   * consecutive detections, ignores small screens (where outer sizes are not
   * meaningful) and lifts itself as soon as the gap closes.
   */
  var hits = 0;
  var open = false;

  function checkDevtools() {
    if (!window.outerWidth || window.innerWidth < 768) {
      return;
    }

    var wide = window.outerWidth - window.innerWidth > 200;
    var tall = window.outerHeight - window.innerHeight > 220;
    hits = wide || tall ? hits + 1 : 0;

    if (hits >= 2 && !open) {
      open = true;
      showVeil('Developer tools detected. Close them and reload to continue reading.');
    } else if (hits === 0 && open) {
      open = false;
      hideVeil();
    }
  }

  window.setInterval(checkDevtools, 1200);
  window.addEventListener('resize', checkDevtools);
})();
