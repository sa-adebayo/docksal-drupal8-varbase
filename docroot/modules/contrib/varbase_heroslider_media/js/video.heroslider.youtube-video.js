/**
 * @file
 * Behaviors of Varbase hero slider media for Youtube video scripts.
 */

(function ($, _, Drupal, drupalSettings) {
  "use strict";
  Drupal.behaviors.varbaseHeroSliderMedia_youtube = {
    attach: function (context, settings) {
      $(window).on('load', function () {

        // Youtube API.
        var yotubePlayer;
        yotubePlayer = new YT.Player('youtubeVideo', {
          events: {
            'onStateChange': onPlayerStateChange,
            'onReady': onPlayerReady
          }
        });

        // Play youtube video on ready.
        function onPlayerReady() {
          var firstSlideVideo = $('.slick--view--varbase-heroslider-media .slick__slider .slick-active').find('.media-video').length !== 0;
          if (firstSlideVideo) {
            $('.slick--view--varbase-heroslider-media .slick__slider').slick('slickPause');
            yotubePlayer.playVideo();
          } else {// if hero slider has one Slide
            var onlySlide = $('.slick--view--varbase-heroslider-media .slick').find('.media-video').length !== 0;
            if (onlySlide) {
              yotubePlayer.playVideo();
            }
          }
        }

        // Video status.
        function onPlayerStateChange(event) {
          if (event.data === 0) { // On finish
            $('.slick--view--varbase-heroslider-media .slick__slider').slick('slickPlay');
          } else if (event.data === 1) { // On playing
            $('.slick--view--varbase-heroslider-media .slick__slider').slick('slickPause');
          } else if (event.data === 2) { // Onpause
            $('.slick--view--varbase-heroslider-media .slick__slider').slick('slickPause');
          }
        }

        $('.slick--view--varbase-heroslider-media .slick__slider', context).on('beforeChange', function (event, slick, currentSlide, nextSlide) {
          var currentSlideObject = $('.slick--view--varbase-heroslider-media .slide--' + currentSlide + '.slick-active');
          var nextSlideObject = $('.slick--view--varbase-heroslider-media .slide--' + nextSlide);
          var currentIframe = currentSlideObject.find('.varbase-video-player #youtubeVideo', context);
          var nextIframe = nextSlideObject.find('.varbase-video-player #youtubeVideo', context);

          if (currentIframe.length !== 0) {
            yotubePlayer.a = currentIframe.get(0);
            yotubePlayer.pauseVideo();
          }

          if (nextIframe.length !== 0) {
            yotubePlayer.a = nextIframe.get(0);
            yotubePlayer.playVideo();
          }
        });

        // When first slide has a video (Pause the slider and play the video).
        $('.slick--view--varbase-heroslider-media .varbase-video-player #youtubeVide').on("load", function () {
          var firstSlideVideo = $('.slick--view--varbase-heroslider-media .slick__slider .slick-active').find('.media-video').length !== 0;
          if (firstSlideVideo) {
            $('.slick--view--varbase-heroslider-media .slick__slider').slick('slickPause');
            yotubePlayer.playVideo();
          }
        });
      });
    }
  };

})(window.jQuery, window._, window.Drupal, window.drupalSettings);
