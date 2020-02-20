

jQuery(function ($) {
  setTimeout(function() {
    $('.notice').fadeOut(2000);
  }, 3000);

  $('.date').datepicker({
    dateFormat: config.jquery.dateFormat,
    maxDate: 0
  });
});






