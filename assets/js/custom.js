jQuery(function ($) {

    // Do stuff here

}); // jQuery End

/**
 * Full-Screen Search Overlay Toggle
 */
jQuery(function ($) {
  const $searchTrigger = $('#search-overlay-trigger');
  const $searchOverlay = $('#search-overlay');
  const $searchClose = $('.search-overlay-close');
  const $searchInput = $('#search-overlay-input');

  $searchTrigger.on('click', function (e) {
    e.preventDefault();
    $searchOverlay.addClass('active');
    setTimeout(() => {
      $searchInput.focus();
    }, 100);
    $('body').css('overflow', 'hidden'); // Prevent scroll
  });

  $searchClose.on('click', function () {
    $searchOverlay.removeClass('active');
    $('body').css('overflow', ''); // Normal scroll
  });

  // Close on ESC key
  $(document).keyup(function (e) {
    if (e.key === "Escape") {
      $searchOverlay.removeClass('active');
      $('body').css('overflow', '');
    }
  });
});
