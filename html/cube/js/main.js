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

  // dropdown menu
  $('.tk-ui-menu.nav-dropdown').each(function () {
    $(this).addClass('dropdown-menu dropdown-menu-right');
    $(this).find('.divider').addClass('dropdown-divider');
  });
  // Side menu
  $('.tk-ui-menu.nav-side').each(function () {
    $(this).prepend('<li class="nav-header nav-header-first d-none d-lg-block">Navigation</li>');
    $(this).addClass('nav navbar-nav nav-pills nav-stacked').find('ul').addClass('submenu');
    $(this).find('li.submenu > a').addClass('dropdown-toggle dropdown-nocaret').append('<i class="fa fa-angle-right drop-icon"></i>');
    $(this).find('li.submenu').removeClass('submenu');
  });
  $('.tk-ui-menu').css('visibility', 'visible');




  // Activate the appropriate side nav for this url, expands any sub-nav items
  function activateItem(a) {
    if (!a) return;
    a.addClass('active');
    var parent = a.closest('ul');
    do {
      parent.closest('li').addClass('active');
      parent = parent.closest('li').closest('ul');
    } while (parent && parent.hasClass('submenu'));
  }
  // First check the page URL for a match
  $('#nav-col a').removeClass('active').each(function () {
    var uri = location.href;
    var href = $(this).attr('href');
    if (uri === href) {
      activateItem($(this));
    }
  });
  // Check breadcrumbs if no menu item active
  if (!$('#nav-col a.active').length) {
    $($('.breadcrumb a').get().reverse()).each(function () {
      var linkHref = $(this).attr('href');
      var a = $('#nav-col a[href="' + linkHref + '"]');
      if (a.length) {
        console.log(linkHref);
        activateItem(a);
        return false;
      }
    });
  }



});
