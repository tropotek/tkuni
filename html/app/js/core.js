/**
 * The core javascript object for this site
 *
 */

var project_core = function () {
  "use strict";

  /**
   * Dual select list box renderer
   */
  var initDualListBox = function () {
    if ($.fn.DualListBox === undefined) {
      console.warn('DualListBox plugin not available.');
      return;
    }

    $('select.tk-dualSelect').DualListBox();

  };

  /**
   * init the file field renderer
   */
  var initTkFileInput = function () {
    if ($.fn.tkFileInput === undefined) {
      console.warn('tkFileInput plugin not available.');
      return;
    }
    $('.tk-imageinput').tkImageInput({dataUrl: config.dataUrl});
    $('.tk-multiinput').tkMultiInput({dataUrl: config.dataUrl});
    $('.tk-fileinput:not(.tk-imageinput)').tkFileInput({});

  };

  /**
   * Init the datetime plugin
   * for single dates and date range fields
   * `.date` = single date text field
   * `.input-datetimerange` = 2 text box range field group
   */
  var initDatetimePicker = function () {
    if ($.fn.datetimepicker === undefined) {
      console.warn('datetimepicker plugin not available.');
      return;
    }

    if (!config.datepickerFormat)
      config.datepickerFormat = 'dd/mm/yyyy';

    console.log(config);
    // single date
    $('.date').datetimepicker({
      format: config.datepickerFormat,
      autoclose: true,
      todayBtn: true,
      todayHighlight: true,
      initialDate: new Date(),
      minView: 2,
      maxView: 2
    });

    $('.input-daterange').each(function () {
      // TODO we need to fix the initialDate bug when the date format has the time.
      var inputGroup = $(this);
      var start = inputGroup.find('input').first();
      var end = inputGroup.find('input').last();
      start.datetimepicker({
        todayHighlight: true,
        format: config.datepickerFormat,
        autoclose: true,
        todayBtn: true,
        //initialDate: new Date(),
        initialDate: start.val(),
        minView: 2,
        maxView: 2
      });
      end.datetimepicker({
        todayHighlight: true,
        format: config.datepickerFormat,
        autoclose: true,
        todayBtn: true,
        //initialDate: new Date(),
        initialDate: end.val(),
        minView: 2,
        maxView: 2
      });

      start.datetimepicker().on('changeDate', function (e) {
        //end.datetimepicker('setStartDate', e.date);
        var startDate = start.datetimepicker('getDate');
        var endDate = end.datetimepicker('getDate');
        if (startDate > endDate) {
          end.datetimepicker('setDate', startDate);
        }
      });
      end.datetimepicker().on('changeDate', function (e) {
        //start.datetimepicker('setEndDate', e.date);
        var startDate = start.datetimepicker('getDate');
        var endDate = end.datetimepicker('getDate');
        if (endDate < startDate) {
          start.datetimepicker('setDate', endDate);
        }
      });
    });


    $('.input-datetimerange').each(function () {
      var inputGroup = $(this);
      var start = inputGroup.find('input').first();
      var end = inputGroup.find('input').last();
      start.datetimepicker({
        todayHighlight: true,
        format: config.datepickerFormat + ' hh:ii',
        autoclose: true,
        todayBtn: true,
        //startDate: new Date(),
        minuteStep: 5,
        initialDate: start.val()
      });
      end.datetimepicker({
        todayHighlight: true,
        format: 'dd/mm/yyyy hh:ii',
        autoclose: true,
        todayBtn: true,
        //startDate: new Date(),
        minuteStep: 5,
        initialDate: end.val()
      });

      start.datetimepicker().on('changeDate', function (e) {
        //end.datetimepicker('setStartDate', e.date);
        var startDate = start.datetimepicker('getDate');
        var endDate = end.datetimepicker('getDate');
        if (startDate > endDate) {
          end.datetimepicker('setDate', startDate);
        }
      });
      end.datetimepicker().on('changeDate', function (e) {
        //start.datetimepicker('setEndDate', e.date);
        var startDate = start.datetimepicker('getDate');
        var endDate = end.datetimepicker('getDate');
        if (endDate < startDate) {
          start.datetimepicker('setDate', endDate);
        }
      });
    });

  };

  /**
   * Tiny MCE setup
   */
  var initTinymce = function () {
    if ($.fn.tinymce === undefined) {
      console.warn('tinymce plugin not available.');
      return;
    }
    var mceOpts = {
      theme: 'modern',
      plugins: [
        'advlist autolink autosave link image lists charmap print preview hr anchor',
        'searchreplace code fullscreen insertdatetime media nonbreaking codesample',
        'table directionality emoticons template paste textcolor colorpicker textpattern visualchars visualblocks'
      ],
      toolbar1: 'undo redo | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | styleselect | bullist numlist | outdent indent',
      toolbar2: 'cut copy paste searchreplace | link unlink anchor image media | hr subscript superscript | forecolor backcolor blockquote',
      toolbar3: 'table | visualchars visualblocks ltr rtl | nonbreaking insertdatetime | charmap emoticons | print preview | removeformat fullscreen code codesample',
      content_css: [
        '//fonts.googleapis.com/css?family=Lato:300,300i,400,400i'
        , '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css'
      ],
      menubar: false,
      toolbar_items_size: 'small',
      image_advtab: true,
      content_style: 'body {padding: 10px}',
      convert_urls: false,
      browser_spellcheck: true,
      file_picker_callback: _elFinderPickerCallback
    };
    $('textarea.mce').each(function () {
      var el = $(this);
      var opts = $.extend({}, mceOpts, {});
      if (el.hasClass('.mce-min')) {
        opts = $.extend({}, opts, {
          plugins: ['advlist autolink autosave link image lists charmap hr anchor code textcolor colorpicker textpattern'],
          toolbar1: 'bold italic underline strikethrough | forecolor backcolor | alignleft aligncenter alignright ' +
          'alignjustify | bullist numlist | link unlink | removeformat code charmap',
          toolbar2: '',
          toolbar3: ''
        });
      } else if (el.hasClass('.mce-med')) {
        opts.height = el.data('height') ? el.data('height') : 250;
      } else {
        opts.height = el.data('height') ? el.data('height') : 400;
      }
      el.tinymce(opts);
    });
  };


  /**
   * private elFinder callback function
   * @returns {boolean}
   * @private
   */
  var _elFinderPickerCallback = function () {
    tinymce.activeEditor.windowManager.open({
      file: config.templateUrl + '/app/js/elFinder/elfinder.html', // use an absolute path!
      title: 'File Manager',
      width: 900,
      height: 350,
      resizable: false,
      config: config
    }, {
      oninsert: function (file, fm) {
        var url, reg, info;
        // URL normalization
        url = fm.convAbsUrl(file.url);
        // Make file info
        info = file.name;
        // Provide file and text for the link dialog
        if (meta.filetype === 'file') {
          callback(url, {text: info, title: info});
        }
        // Provide image and alt text for the image dialog
        if (meta.filetype === 'image') {
          callback(url, {alt: info});
        }
        // Provide alternative source and posted for the media dialog
        if (meta.filetype === 'media') {
          callback(url);
        }
      }
    });
    return false;
  };


  /**
   * remove focus on menu links
   */
  var initLinkBlur = function () {
    $('body').on('click', 'a[role=tab]', function () {
      $(this).blur();
    });
    //$('a[role=tab]').click(function() { $(this).blur(); });
  };

  /**
   *
   */
  var initMasqueradeConfirm = function () {
    $('body').on('click', '.tk-msq, .tk-masquerade', function () {
      return confirm('You are about to masquerade as the selected user?');
    });
  };

  /**
   *
   */
  var initTableDeleteConfirm = function () {
    $('body').on('click', '.tk-remove', function () {
      return confirm('Are you sure you want to remove this item?');
    });
  };

  //**
  var initGrowLikeAlerts = function () {
    // Growl like alert messages that fade out.
    $('.tk-alert-container .alert').each(function () {
      var a = $(this);
      setTimeout(function () {
        a.fadeOut(1000);
      }, 5000);
    });
  };


  return {
    initDatetimePicker: initDatetimePicker
    , initLinkBlur: initLinkBlur
    , initTkFileInput: initTkFileInput
    , initDualListBox: initDualListBox
    , initTinymce: initTinymce
    , initMasqueradeConfirm: initMasqueradeConfirm
    , initTableDeleteConfirm: initTableDeleteConfirm
    , initGrowLikeAlerts: initGrowLikeAlerts
  }

}();

