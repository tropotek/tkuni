/**
 * Created by mifsudm on 19/07/16.
 */

$(document).ready(function() {

  $('input[type=file].fileinput').fileinput({dataUrl: config.dataUrl});

  $('a[role=tab]').click(function() { $(this).blur(); });

  $('input.date').datepicker({
    dateFormat: 'dd/mm/yy'
  });

  $('select.tk-dualSelect').DualListBox();

});