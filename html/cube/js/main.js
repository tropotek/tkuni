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

  // Side menu
  $('.tk-ui-menu.nav-side').each(function () {
    $(this).prepend('<li class="nav-header nav-header-first d-none d-lg-block">Navigation</li>');
    $(this).addClass('nav navbar-nav nav-pills nav-stacked').find('ul').addClass('submenu');
    $(this).find('li.submenu > a').addClass('dropdown-toggle dropdown-nocaret').append('<i class="fa fa-angle-right drop-icon"></i>');
    $(this).find('li.submenu').removeClass('submenu');
  });
  // dropdown menu
  $('.tk-ui-menu.nav-dropdown').each(function () {
    $(this).addClass('dropdown-menu dropdown-menu-right');
    $(this).find('.divider').addClass('dropdown-divider');
  });

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
