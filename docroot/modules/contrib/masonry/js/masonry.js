/**
 * @file
 * Masonry script.
 *
 * Sponsored by: www.freelance-drupal.com
 */

(function ($, Drupal, drupalSettings) {

  Drupal.behaviors.masonry = {
    attach: function(context, settings) {

      // Apply Masonry on the page.
      applyMasonry(false);

      // Hack for tabs: when the tab is open, it takes to reload Masonry.
      // @todo: what is the effect of this on performance ?
      $('a[data-toggle="tab"]').on('shown.bs.tab', function () {
        applyMasonry(true);
      });

      /**
       * Apply Masonry
       * @param forceInit (boolean)
       *   Force the initialisation of Masonry display (necessary hack for tabs).
       */
      function applyMasonry(forceInit) {

        // Iterate through all Masonry instances
        $.each(drupalSettings.masonry, function (container, settings) {
          // Set container
          var $container = $(container);
          $(container).addClass('masonry-layout');

          // Set options
          var $options = new Object();

          // Sets the item selector.
          if (settings.item_selector) {
            $options.itemSelector = settings.item_selector;
            // Add custom class to all items.
            $(settings.item_selector).addClass('masonry-item');
          }

          // Apply column width units accordingly.
          if (settings.column_width) {
            if (settings.column_width_units == 'px') {
              $options.columnWidth = parseInt(settings.column_width);
            }
            else if (settings.column_width_units == '%') {
              $options.columnWidth = ($container.width() * (settings.column_width / 100)) - settings.gutter_width ;
            }
            else {
              $options.columnWidth = settings.column_width;
            }
          }

          // Add stamped selector.
          if (settings.stamp) {
            $options.stamp = settings.stamp;
          }

          // Add the various options.
          $options.gutter = settings.gutter_width;
          $options.isResizeBound = settings.resizable;
          $options.isFitWidth = settings.fit_width;
          if (settings.rtl) {
            $options.isOriginLeft = false;
          }
          if (settings.animated) {
            $options.transitionDuration = settings.animation_duration + 'ms';
          }
          else {
            $options.transitionDuration = 0;
          }
          if(settings.percent_position){
            $options.percentPosition = true;
          }

          /**
           * Apply Masonry to container
           */

          // Load images first if necessary.
          if (settings.images_first) {
            $container.imagesLoaded(function () {
              if (forceInit) {
                $container.masonry($options);
              }
              else if ($container.hasClass('masonry-processed')) {
                $container.masonry('reloadItems').masonry('layout');
              }
              else {
                $container.once('masonry').masonry($options);
              }
            });
          }

          // Apply without loading images first otherwise.
          else {
            if (forceInit) {
              $container.masonry($options);
            }
            else if (!forceInit && $container.hasClass('masonry-processed')) {
              $container.masonry('reloadItems').masonry('layout');
            }
            else {
              $container.once('masonry').masonry($options);
            }
          }

        });
      }
    }
  };
})(jQuery, Drupal, drupalSettings);