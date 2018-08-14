/**
 * main.js
 */

config.tkPanel = {
  template:
  '<div class="main-box">\n' +
  '  <header class="main-box-header clearfix"><h2 class="float-left"><i class="tp-icon"></i> <span class="tp-title"></span></h2></header>\n' +
  '  <div class="tp-body main-box-body clearfix"></div>\n' +
  '</div>'
};

jQuery(function ($) {



  // Activate the appropriate side nav for this url, expands any sub-nav items
  $('#nav-col a').removeClass('active').each(function () {
    var uri = location.href;
    var a = $(this).attr('href');
    if (uri === a) {
      $(this).addClass('active');
      var parent = $(this).closest('ul');
      do {
          parent.closest('li').addClass('active');
          parent = parent.closest('li').closest('ul');
      } while (parent && parent.hasClass('submenu'));
    }
  });



});
