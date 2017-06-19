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

  $('input.date').datepicker({
    dateFormat: 'dd/mm/yy'
  });

  $('select.tk-dualSelect').DualListBox();

});