
/**
 * Title: fileinput
 * Version 1.0, Jul 14th, 2016
 * by Metal Mick
 *
 * This plugin was created from the code snippet: http://bootsnipp.com/snippets/featured/input-file-popover-preview-image
 *
 * <code>
 *   $(document).ready(function() {
 *     // attach the plugin to an element
 *     $('#element').fileinput({'foo': 'bar'});
 *
 *     // call a public method
 *     $('#element').data('fileinput').foo_public_method();
 *
 *     // get the value of a property
 *     $('#element').data('fileinput').settings.foo;
 *
 *   });
 * </code>
 *
 *
 */
(function($) {

  var fileinput = function(element, options) {

    // Default options
    var defaults = {
      dataUrl: '',
      delClassAppend: '-del',    // For Tk File Field only
      onFoo: function() {}
    };

    // To avoid confusions, use "plugin" to reference the current instance of the object
    var plugin = this;

    plugin.settings = {};

    var $element = $(element);

    // the "constructor" method that gets called when the object is created
    plugin.init = function() {
      plugin.settings = $.extend({}, defaults, options);

      initInput();

    };

    var template = $(
      '<div class="input-group image-preview">' +
        '<span class="input-group-btn">' +
          '<!-- image-preview-input -->' +
          '<div class="btn btn-default image-preview-input" title="Select an image">' +
            '<span class="glyphicon glyphicon-folder-open"></span>' +
            '<span class="image-preview-input-title"></span>' +
            //'<input type="file" accept="image/png, image/jpeg, image/gif" name="input-file-preview"/> <!-- rename it -->' +
          '</div>' +
          '<!-- image-preview-clear button -->' +
          '<button type="button" class="btn btn-default image-preview-clear" title="Remove image">' +
            '<span class="glyphicon glyphicon-trash"></span>' +
          '</button>' +
          '<button type="button" class="btn btn-default image-preview-thumb" title="" style="display: none;">' +
            '<img class="thumb-img" src="#" alt="" style="max-height: 16px;max-width: 100px;display: inline;"/>' +
          '</button>' +
        '</span>' +
        '<input type="text" class="form-control image-preview-filename" disabled="disabled"> <!-- dont give a name === doesnt send on POST/GET -->' +
      '</div>'
    );


    // -- private methods

    /**
     * init the input element`
     */
    var initInput = function() {
      var parent = $element.parent();

      // Tk2 File field only
      var delCb = parent.find('#'+$element.attr('id')+plugin.settings.delClassAppend);
      if (delCb.length) {
        parent.find('.'+$element.attr('id')+plugin.settings.delClassAppend+'-wrap').hide();
      }

      // Check bootstral element sizes
      if ($element.parents('.form-group-sm').length || $element.hasClass('input-sm')) {
        template.find('.image-preview-input').addClass('btn-sm');
        template.find('.image-preview-clear').addClass('btn-sm');
        template.find('.image-preview-thumb').addClass('btn-sm');
      } else if ($element.parents('.form-group-lg').length || $element.hasClass('input-lg')) {
        template.find('.image-preview-input').addClass('btn-lg');
        template.find('.image-preview-clear').addClass('btn-lg');
        template.find('.image-preview-thumb').addClass('btn-lg');
      }



      $element.detach();
      parent.prepend(template);
      template.find('.image-preview-input').append($element);

      var img = template.find('.image-preview-thumb img');
      template.find('.image-preview-thumb').popover({
        trigger: 'manual',
        html: true,
        //title: '<strong>Preview</strong> ',
        content: '',
        placement: 'top'
      }).css({textAlign: 'center'});

      // Hover before close the preview
      template.find('.image-preview-thumb').hover(
        function (e) {
          $(this).popover('show');
        },
        function (e) {
          $(this).popover('hide');
        }
      ).click(function(e) {
        $(this).popover('show').blur();
      });

      if ($element.attr('value')) {
        template.find('input.image-preview-filename').val($element.attr('value').replace(/\\/g,'/').replace( /.*\//, '' ));
        // show thumb
        if (isImage($element.attr('value'))) {
          template.find('.image-preview-thumb img').attr('src', plugin.settings.dataUrl + $element.attr('value')).show();
          template.find('.image-preview-thumb').show();
          template.find('.image-preview-thumb').attr('data-content', copyImageHtml(img));
        } else {
          template.find('.image-preview-thumb').hide();
          template.find('.image-preview-thumb').attr('data-content', '');
        }
      } else {
        template.find('.image-preview-clear').hide();
      }

      template.find('.image-preview-clear').on('click', function(e) {
        var prev = $(this).parents('.image-preview');
        prev.attr('data-content', '').popover('hide');
        prev.find('.image-preview-filename').val('');
        prev.find('.image-preview-clear').hide();
        prev.find('.image-preview-input input:file').val('');
        //prev.find('.image-preview-input-title').text(' Browse');

        prev.find('.image-preview-thumb').hide();
        prev.find('.image-preview-thumb').attr('data-content', 'No Image');
        delCb.prop('checked', true);
        $element.attr('value', '');
      });

      template.find('.image-preview-input input:file').on('change', function(e) {
        var prev = $(this).parents('.image-preview');
        delCb.prop('checked', false);
        prev.find('.image-preview-thumb').addClass('disabled');

        var file = this.files[0];
        if (typeof file !== 'undefined') {
          var reader = new FileReader();
          reader.onload = function (e) {
            //prev.find('.image-preview-input-title').text(' Change');
            prev.find('.image-preview-clear').show();
            prev.find('.image-preview-filename').val(file.name);
            if (isImage(file.name)) {
              prev.find('.image-preview-thumb').show();
              prev.find('.image-preview-thumb img').attr('src', e.target.result);
              prev.find('.image-preview-thumb').attr('data-content', copyImageHtml(img));
              prev.find('.image-preview-thumb').removeClass('disabled');
            }

            if ($element.attr('data-maxsize') && file.size) {
              var maxSize = parseInt($element.attr('data-maxsize'), 10);
              if (file.size > maxSize) {
                alert('File is to large for upload, please check your file size is below ' + formatBytes($element.attr('data-maxsize')));
              }
            }
          };
          reader.readAsDataURL(file);



        }
      });

    };

    /**
     *
     * @param filename
     */
    var isImage = function(filename) {
      var ext = getExtension(basename(filename)).toLowerCase();
      switch (ext) {
        case 'jpg':
        case 'jpeg':
        case 'gif':
        case 'png':
          return true;
      }
      return false;
    };

    /**
     *
     * @param path
     * @returns {XML|string}
     */
    var basename = function(path) {
      return path.replace(/\\/g,'/').replace( /.*\//, '' );
    };

    /**
     *
     * @param file
     */
    var getExtension = function(file) {
      var pos = file.lastIndexOf('.');
      if (pos > -1) {
        return file.substring(pos + 1);
      }
      return '';
    };

    /**
     *
     * @param img
     * @returns {*|string}
     */
    var copyImageHtml = function (img) {
      var cpy = $(img).clone();
      cpy.attr('style', '').css({maxWidth: 250, height: 'auto'});
      return cpy[0].outerHTML;
    };

    /**
     *
     * @param bytes
     * @param decimals
     * @returns {*}
     */
    var formatBytes = function (bytes,decimals) {
      if(bytes == 0) return '0 Byte';
      var k = 1000; // or 1024 for binary
      var dm = decimals + 1 || 3;
      var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
      var i = Math.floor(Math.log(bytes) / Math.log(k));
      return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    };


    // -- public methods

    /**
     * a public method. for demonstration purposes only - remove it!
     */
    // plugin.foo_public_method = function() {
    //
    //   // code goes here
    //
    // };


    // call the "constructor" method
    plugin.init();
  };


  // add the plugin to the jQuery.fn object
  $.fn.fileinput = function(options) {
    return this.each(function() {
      if (undefined == $(this).data('fileinput')) {
        var plugin = new fileinput(this, options);
        $(this).data('fileinput', plugin);
      }
    });
  }

})(jQuery);














