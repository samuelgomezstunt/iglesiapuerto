/**
 * Iglesia · Próximos Eventos - Admin JS
 * Media frame multiselección y repetidor de botones
 */
jQuery(document).ready(function($) {
  'use strict';

  // Sortable para la galería
  if ($('#ige_gallery_container').length) {
    $('#ige_gallery_container').sortable({
      update: function() {
        updateGalleryInput();
      }
    });
  }

  function updateGalleryInput() {
    const ids = [];
    $('#ige_gallery_container .ige-thumb-item').each(function() {
      const id = $(this).data('id');
      if (id) ids.push(id);
    });
    $('#ige_gallery_input').val(ids.join(','));
  }

  // Quitar imagen
  $(document).on('click', '.ige-remove-img', function(e) {
    e.preventDefault();
    $(this).closest('.ige-thumb-item').fadeOut(200, function() {
      $(this).remove();
      updateGalleryInput();
    });
  });

  // Selector wp.media multiselección
  let mediaFrame;
  $('#ige_add_gallery_btn').on('click', function(e) {
    e.preventDefault();

    if (mediaFrame) {
      mediaFrame.open();
      return;
    }

    mediaFrame = wp.media({
      title: 'Seleccionar imágenes para el evento',
      button: { text: 'Añadir a la galería' },
      multiple: true
    });

    mediaFrame.on('select', function() {
      const selection = mediaFrame.state().get('selection');
      selection.map(function(attachment) {
        attachment = attachment.toJSON();
        const id = attachment.id;
        const url = (attachment.sizes && attachment.sizes.thumbnail) ? attachment.sizes.thumbnail.url : attachment.url;

        // Evitar duplicados
        if ($('#ige_gallery_container .ige-thumb-item[data-id="' + id + '"]').length === 0) {
          const html = '<div class="ige-thumb-item" data-id="' + id + '">' +
            '<img src="' + url + '" alt="" />' +
            '<button type="button" class="ige-remove-img" title="Eliminar imagen">×</button>' +
            '</div>';
          $('#ige_gallery_container').append(html);
        }
      });
      updateGalleryInput();
    });

    mediaFrame.open();
  });

  // Repetidor de botones
  function updateButtonsJson() {
    const rows = [];
    $('#ige_buttons_table tbody tr').each(function() {
      const text = $(this).find('.btn-text').val().trim();
      const url = $(this).find('.btn-url').val().trim();
      const newTab = $(this).find('.btn-newtab').is(':checked');
      const style = $(this).find('.btn-style').val();

      if (text || url) {
        rows.push({
          text: text,
          url: url,
          new_tab: newTab,
          style: style
        });
      }
    });
    $('#ige_buttons_json').val(JSON.stringify(rows));
  }

  $('#ige_add_button_row').on('click', function(e) {
    e.preventDefault();
    const newRow = '<tr>' +
      '<td><input type="text" class="widefat btn-text" placeholder="Ej: Inscribirme" /></td>' +
      '<td><input type="url" class="widefat btn-url" placeholder="https://..." /></td>' +
      '<td style="text-align: center;"><input type="checkbox" class="btn-newtab" checked /></td>' +
      '<td>' +
        '<select class="widefat btn-style">' +
          '<option value="primary">Principal</option>' +
          '<option value="secondary">Secundario</option>' +
        '</select>' +
      '</td>' +
      '<td><button type="button" class="button ige-remove-btn">✕</button></td>' +
    '</tr>';
    $('#ige_buttons_table tbody').append(newRow);
  });

  $(document).on('click', '.ige-remove-btn', function(e) {
    e.preventDefault();
    $(this).closest('tr').remove();
    updateButtonsJson();
  });

  $(document).on('input change', '#ige_buttons_table input, #ige_buttons_table select', function() {
    updateButtonsJson();
  });
});
