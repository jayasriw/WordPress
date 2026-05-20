var ISF = ISF || {};

(function ($) {
  "use strict";

  var ajax_url = civi_template_vars.ajax_url;

  /******************* Refresh after live preview *********************/

  var Widget_Reload_Carousel = function ($scope, $) {
    var carousel_elem = $scope.find(".elementor-carousel");
    carousel_elem.each(function () {
      var settings = carousel_elem.data("slider_options");
      if (settings["isslick"] == "false") {
        alert(settings["isslick"]);
        carousel_elem.unslick();
      } else {
        carousel_elem.not(".slick-initialized").slick(settings);
      }
    });
  };

  $(window).on("elementor/frontend/init", function () {
    elementorFrontend.hooks.addAction(
      "frontend/element_ready/civi-companies.default",
      Widget_Reload_Carousel
    );
    elementorFrontend.hooks.addAction(
      "frontend/element_ready/civi-jobs.default",
      Widget_Reload_Carousel
    );
    elementorFrontend.hooks.addAction(
      "frontend/element_ready/civi-service.default",
      Widget_Reload_Carousel
    );
    elementorFrontend.hooks.addAction(
      "frontend/element_ready/civi-service-category.default",
      Widget_Reload_Carousel
    );
    elementorFrontend.hooks.addAction(
      "frontend/element_ready/civi-jobs-category.default",
      Widget_Reload_Carousel
    );
    elementorFrontend.hooks.addAction(
      "frontend/element_ready/civi-companies-category.default",
      Widget_Reload_Carousel
    );
    elementorFrontend.hooks.addAction(
      "frontend/element_ready/civi-jobs-location.default",
      Widget_Reload_Carousel
    );
    elementorFrontend.hooks.addAction(
      "frontend/element_ready/civi-candidate-box.default",
      Widget_Reload_Carousel
    );
  });
})(jQuery);
