/* Rich text editor for chapters (Quill 2 + server image upload). */
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    var holder = document.getElementById('editor');

    if (!holder || typeof window.Quill === 'undefined') {
      return;
    }

    var input = document.getElementById('content-input');
    var form = holder.closest('form');
    var uploadUrl = holder.getAttribute('data-upload-url');
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var csrf = csrfMeta ? csrfMeta.getAttribute('content') : '';

    function uploadImage() {
      var picker = document.createElement('input');
      picker.type = 'file';
      picker.accept = 'image/png,image/jpeg,image/webp,image/gif';

      picker.addEventListener('change', function () {
        var file = picker.files && picker.files[0];

        if (!file) {
          return;
        }

        var data = new FormData();
        data.append('image', file);

        quill.enable(false);
        window.pkToast && window.pkToast('Uploading image…');

        fetch(uploadUrl, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json'
          },
          body: data
        })
          .then(function (response) {
            return response.json().then(function (body) {
              return { ok: response.ok, body: body };
            });
          })
          .then(function (result) {
            if (!result.ok || !result.body.url) {
              throw new Error((result.body && result.body.message) || 'Upload failed.');
            }

            var range = quill.getSelection(true);
            var index = range ? range.index : quill.getLength();
            quill.insertEmbed(index, 'image', result.body.url, 'user');
            quill.setSelection(index + 1, 0);
          })
          .catch(function (error) {
            window.pkToast && window.pkToast(error.message || 'Could not upload that image.', true);
          })
          .finally(function () {
            quill.enable(true);
          });
      });

      picker.click();
    }

    var quill = new window.Quill(holder, {
      theme: 'snow',
      placeholder: 'Write the chapter. Use the toolbar for headings, quotes, lists, links and images…',
      modules: {
        toolbar: {
          container: [
            [{ header: [1, 2, 3, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ list: 'ordered' }, { list: 'bullet' }],
            ['blockquote', 'code-block'],
            [{ align: [] }],
            ['link', 'image'],
            ['clean']
          ],
          handlers: { image: uploadImage }
        }
      }
    });

    if (input && input.value.trim() !== '') {
      if (quill.clipboard && typeof quill.clipboard.dangerouslyPasteHTML === 'function') {
        quill.clipboard.dangerouslyPasteHTML(input.value);
      } else {
        quill.root.innerHTML = input.value;
      }
    }

    function sync() {
      if (input) {
        input.value = quill.root.innerHTML;
      }
    }

    quill.on('text-change', sync);

    if (form) {
      form.addEventListener('submit', sync);
    }
  });
})();
