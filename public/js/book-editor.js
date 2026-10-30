/* Drag and drop chapter ordering for the book editor. */
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    var list = document.getElementById('chapter-list');

    if (!list || typeof window.Sortable === 'undefined') {
      return;
    }

    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var csrf = csrfMeta ? csrfMeta.getAttribute('content') : '';
    var reorderUrl = list.getAttribute('data-reorder-url');

    function renumber() {
      list.querySelectorAll('.chapter-manage-item').forEach(function (row, index) {
        var position = row.querySelector('.chapter-pos');
        if (position) {
          position.textContent = index + 1;
        }
      });
    }

    function save() {
      var ids = Array.prototype.map.call(
        list.querySelectorAll('[data-chapter-id]'),
        function (row) {
          return Number(row.getAttribute('data-chapter-id'));
        }
      );

      fetch(reorderUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf,
          'Accept': 'application/json'
        },
        body: JSON.stringify({ ids: ids })
      })
        .then(function (response) {
          return response.json().then(function (body) {
            return { ok: response.ok, body: body };
          });
        })
        .then(function (result) {
          window.pkToast && window.pkToast(result.body.message || 'Saved.', !result.ok);
          if (result.ok) {
            renumber();
          }
        })
        .catch(function () {
          window.pkToast && window.pkToast('Could not save the new chapter order. Reload and try again.', true);
        });
    }

    window.Sortable.create(list, {
      handle: '.drag-handle',
      animation: 150,
      onEnd: save
    });
  });
})();
