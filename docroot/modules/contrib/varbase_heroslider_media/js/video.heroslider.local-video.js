/**
 * @file
 * Behaviors of Varbase hero slider media for local video scripts.
 */

(function ($, _, Drupal, drupalSettings) {
  "use strict";

  Drupal.behaviors.varbaseHeroSliderMedia_local_video = {
    attach: function (context, settings) {
      $(window).on('load', function () {
        // On before slide change.
        $('.slick--view--varbase-heroslider-media .slick__slider', context).on('beforeChange', function (event, slick, currentSlide, nextSlide) {
          var currentSlideObject = $('.slide--' + currentSlide + '.slick-active');
          var nextSlideObject = $('.slide--' + nextSlide);
          var currentVideo = currentSlideObject.find('.varbase-video-player video', context);
          var nextVideo = nextSlideObject.find('.varbase-video-player video', context);

          if (currentVideo.length !== 0) {
            var currentPlayer = currentVideo.get(0);
            currentPlayer.pause();
          }

          if (nextVideo.length !== 0) {
            var nextPlayer = nextVideo.get(0);
            nextPlayer.onpause = onPause();
            nextPlayer.onended = onFinish();
            nextPlayer.onplay = onPlayProgress();
            nextPlayer.play();
          }
        });

        // When first slide has a video (Pause the slider and play the video).
        $('.slick--view--varbase-heroslider-media .varbase-video-player video').on("load", function () {
          var firstSlideVideo = $('.slick__slider .slick-active').find('.media-video').length !== 0;
          if (firstSlideVideo) {
             $('.slick--view--varbase-heroslider-media .slick__slider').slick('slickPause');
            var player = $('.slick--view--varbase-heroslider-media .varbase-video-player video').get(0);
            player.play();
          }
        });

        // Local Video variable.
        if ($('.slick--view--varbase-heroslider-media .varbase-video-player video').length > 0) {
          var player = $('.slick--view--varbase-heroslider-media .varbase-video-player video').get(0);

          // When the player is ready, add listeners for pause, finish,
          // and playProgress.
            player.onpause = onPause();
            player.onended = onFinish();
            player.onplay = onPlayProgress();
        }

        // Play when paused.
        function onPause() {
          $('.slick__slider').slick('slickPlay');
        }

        // Play when finished.
        function onFinish() {
          $('.slick__slider').slick('slickPlay');
        }

        // Pause on play prgress.
        function onPlayProgress() {
          $('.slick__slider').slick('slickPause');
          }
      });
    }
  };
})(window.jQuery, window._, window.Drupal, window.drupalSettings);
