import Swiper from "swiper";
import { Navigation, Pagination } from "swiper/modules";

(function (Drupal) {
  Drupal.behaviors.edgeSwiper = {
    attach(context) {
      // Get all swiper elements
      const swiperElements = context.querySelectorAll(".js-swiper");

      // If no elements found, exit early
      if (!swiperElements || swiperElements.length === 0) return;

      // Convert NodeList to Array to ensure forEach works reliably
      Array.from(swiperElements).forEach((element) => {
        // Skip already initialized sliders
        if (element.dataset.swiperAttached === "true") return;
        // Count the number of slides
        const slideCount = element.querySelectorAll(".swiper-slide").length;

        // Initialize Swiper
        new Swiper(element, {
          modules: [Navigation, Pagination],
          slidesPerView: 1,
          autoHeight: true,
          loop: slideCount >= 2,
          navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
          },
          pagination: {
            el: ".swiper-pagination",
            clickable: true,
          },
        });

        // Mark as initialized
        element.dataset.swiperAttached = "true";
      });
    },
  };
})(Drupal);
