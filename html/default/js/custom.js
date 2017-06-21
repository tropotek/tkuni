/**
 * Created by mifsudm on 19/07/16.
 */

$(document).ready(function() {

  // Standard file input
  if ($.fn.tkFileInput !== undefined) {

    $('.tk-imageinput').tkImageInput({
      dataUrl: config.dataUrl
    });

    $('.tk-multiinput').tkMultiInput({
      dataUrl: config.dataUrl
    });

    $('.tk-fileinput:not(.tk-imageinput)').tkFileInput({});
  }



  $('a[role=tab]').click(function() { $(this).blur(); });


  $('select.tk-dualSelect').DualListBox();


  // setup tab pain input checkbox trigger
  $('.tk-form input.tk-input-toggle').tkTabCheckboxToggle();



  $('input.date').datepicker({
    dateFormat: 'dd/mm/yy'
  });

  if ($.fn.datepicker !== undefined) {
    if(!config.datepickerFormat)
      config.datepickerFormat = 'dd/mm/yyyy';

    // single date
    $('.date').datepicker({
      format: config.datepickerFormat
    });

    // date-range
    $('.input-daterange').datepicker({
      format: config.datepickerFormat,
      todayBtn: 'linked'
    });
    // $('.input-daterange input').each(function() {
    //   //$(this).datepicker('clearDates');
    // });

  }

});






// This plugin will toggle all the input elements in a tabPane to disabled or enabled
// Plugin: tkTabCheckboxToggle
(function($) {
  var tkTabCheckboxToggle = function(checkbox, options) {
    // plugin vars
    var defaults = {
      tabPaneSelector: '.tab-pane, .tk-form-fields',
      inputSelector: 'input, textarea, select',
      disableAttr: 'disabled',
      disableCss: 'disabled',
      disableOnSelected: false,
      onToggle: function(tabPane, list) {}
    };
    var plugin = this;
    plugin.settings = {};

    // constructor method
    plugin.init = function() {
      plugin.settings = $.extend({}, defaults, options);
      checkbox = $(checkbox);

      // setup events
      checkbox.on('change', function(e) {
        toggle.apply(checkbox);
      });
      toggle.apply(checkbox);

    };  // END init()

    // private methods
    var toggle = function() {
      var name = $(this).get(0).name;
      var tabPane = $(this).closest(plugin.settings.tabPaneSelector);
      var list = tabPane.find(plugin.settings.inputSelector).not('input[name="'+name+'"]');
      if (!list.length) return;
      if ($(this).prop('checked')) {
        if (plugin.settings.disableAttr)
          list.removeAttr(plugin.settings.disableAttr, plugin.settings.disableAttr);
        if (plugin.settings.disableCss)
          list.removeClass(plugin.settings.disableCss);
      } else {
        if (plugin.settings.disableAttr)
          list.attr(plugin.settings.disableAttr, plugin.settings.disableAttr);
        if (plugin.settings.disableCss)
          list.addClass(plugin.settings.disableCss);
      }
      if (undefined !== plugin.settings.onToggle) plugin.settings.onToggle.apply($(this), [tabPane, list])
    };

    plugin.init();
  };
  $.fn.tkTabCheckboxToggle = function(options) {
    return this.each(function() {
      if (undefined === $(this).data('tkTabCheckboxToggle')) {
        var plugin = new tkTabCheckboxToggle(this, options);
        $(this).data('tkTabCheckboxToggle', plugin);
      }
    });
  }
})(jQuery);




