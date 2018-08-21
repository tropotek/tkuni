/**
 * admin-dashboard.js
 */

jQuery(function ($) {

  // TODO: We need to get this data from the database (AJAX????)


  var ordersTpl = $('<div class="xs-graph float-left">\n' +
    '  <div class="graph-label" title="Tasks Completed This Financial Year"><b><i class="fa fa-shopping-cart"></i> 838</b> Tasks</div>\n' +
    '  <div class="graph-content spark-orders"></div>\n' +
    '</div>');
  //$('.content-header-right').append(ordersTpl);

  var orderValues = [10,8,5,7,4,4,3,8,0,7,10,6];
  ordersTpl.find('.spark-orders').sparkline(orderValues, {
    type: 'bar',
    barColor: '#ced9e2',
    height: 25,
    barWidth: 6
  });

  var revsTpl = $('<div class="xs-graph float-left mrg-l-lg mrg-r-sm">\n' +
    '  <div class="graph-label" title="2017-2018 Financial Year"><b>$12.338</b> Revenue</div>\n' +
    '  <div class="graph-content spark-revenues"></div>\n' +
    '</div>');
  //$('.content-header-right').append(revsTpl);
  var revenuesValues = [8,3,2,6,4,9,1,10,8,2,5,8];
  revsTpl.find('.spark-revenues').sparkline(revenuesValues, {
    type: 'bar',
    barColor: '#ced9e2',
    height: 25,
    barWidth: 6
  });

});
