<?php
namespace App\Ui\Dialog;

use Dom\Template;
use Tk\Request;

/**
 * This class uses the bootstrap dialog box model
 * @link http://getbootstrap.com/javascript/#modals
 *
 *
 * <code>
 * // doDefault()
 * $this->dialog = new \App\Ui\Dialog\FindUser('Enroll Student');
 * $this->dialog->execute($request);
 *
 * ...
 * // show()
 * $template->insertTemplate('dialog', $this->dialog->show());
 * $template->setAttr('modelBtn', 'data-target', '#'.$this->dialog->getId());
 *
 * </code>
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class AjaxSelect extends Iface
{
    /**
     * @var null|callable
     */
    protected $onSelect = null;

    /**
     * @var \Tk\Uri
     */
    protected $ajaxUrl = null;

    /**
     * @var array
     */
    protected $ajaxParams = array();

    /**
     * @var array
     */
    protected $data = array();

    /**
     * @var string
     */
    protected $notes = '';


    /**
     * DialogBox constructor.
     *
     * @param $title
     * @param \Tk\Uri $ajaxUrl
     * @throws \Tk\Exception
     */
    public function __construct($title, $onSelect, $ajaxUrl = null)
    {
        parent::__construct($title);
        $this->ajaxUrl = $ajaxUrl;
        $this->setOnSelect($onSelect);
        
        $this->addButton('Close');
    }

    /**
     * @param $params
     * @return $this
     */
    public function setAjaxParams($params)
    {
        $this->ajaxParams = $params;
        return $this;
    }

    /**
     * @param callable $callable
     * @return $this
     * @throws \Tk\Exception
     */
    public function setOnSelect($callable)
    {
        if (!is_callable($callable)) {
            throw new \Tk\Exception('Must pass a callable object.');
        }
        $this->onSelect = $callable;
        return $this;
    }

    /**
     * @return string
     */
    public function getSelectButtonId()
    {
        return $this->getId().'-enroll';
    }

    /**
     * @param $notes
     * @return $this
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;
        return $this;
    }
    
    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
    
    /**
     * Process the enrolments as submitted from the dialog
     *
     * @param Request $request
     * @throws \Tk\Exception
     */
    public function execute(Request $request)
    {
        $eventId = $this->getSelectButtonId();
        // Fire the callback if set
        if ($request->has($eventId)) {
            $this->data = $request->all();
            if (is_callable($this->onSelect)) {
                call_user_func_array($this->onSelect, array($this->data));
            }
            \Tk\Uri::create()->remove($this->getSelectButtonId())->remove('selectedId')->redirect();
        }
    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        /** @var \Dom\Template $template */
        $template = $this->makeBodyTemplate();
        if ($this->notes) {
            $template->insertHtml('notes', $this->notes);
            $template->setChoice('notes');
        }
        
        $ajaxUrl = $this->ajaxUrl->toString();
        $actionUrl = \App\Uri::create()->set($this->getSelectButtonId())->toString();
        $jsonAjaxParams = json_encode($this->ajaxParams,\JSON_FORCE_OBJECT);
        $dialogId = $this->getId();
        
        $js = <<<JS
jQuery(function($) {
  var dialog = $('#$dialogId');
  var actionUrl = '$actionUrl';
  var params = $jsonAjaxParams;
  processing(false);
  
  dialog.find('.btn-search').click(function(e) {
    processing(true);
    params.keywords = dialog.find('.input-search').val(); 
    $.get('$ajaxUrl', params, function (data) {
      var panel = dialog.find('.dialog-table').empty();
      var table = buildTable(data);
      panel.append(table);
      processing(false);
    });
  });
  
  function buildTable(data) {
    if (data.length === 0) {
      return $('<p class="text-center" style="margin-top: 10px;font-weight: bold;font-style: italic;">No Data Found!</p>');
    }
    var table = $('<table class="table" style="margin-top: 10px;"><tr><th>ID</th><th>Name</th></tr> <tr class="data-tpl"><td class="cell-id"></td><td class="cell-name"><a href="javascript:;" class="cell-name-url"></a></td></tr> </table>');
    
    $.each(data, function (i, obj) {
      var row = table.find('tr.data-tpl').clone();
      row.removeClass('data-tpl').addClass('data');
      // TODO: the action url may need a `?` not a `&` at the start???
      row.find('.cell-name-url').text(obj.name).attr('href', actionUrl+'&selectedId='+obj.id+'&'+$.param(params)).on('click', function (e) {
        $(this).on('click', function() {return false;});
      });
      
      row.find('.cell-id').text(obj.id);
      table.find('tr.data-tpl').after(row);
    });
    table.find('tr.data-tpl').remove();
    
    return table;
  }
  
  function processing(bool) {
    if (bool) {
      dialog.find('.form-control-feedback').show();
      dialog.find('.input-search').attr('disabled', 'disabled');
      dialog.find('.btn-search').attr('disabled', 'disabled');
      dialog.find('.cell-name-url').addClass('disabled');
    } else {
      dialog.find('.form-control-feedback').hide();
      dialog.find('.input-search').removeAttr('disabled');
      dialog.find('.btn-search').removeAttr('disabled');
      dialog.find('.cell-name-url').removeClass('disabled');
    }
  }
  
  // Some focus and key logic
  dialog.on('shown.bs.modal', function (e) {
    dialog.find('.input-search').val('').focus();
    var d = $(e.relatedTarget).data();
    delete d.target;
    delete d.toggle;
    $.extend(params, d);
    dialog.find('.btn-search').click();
  });
  dialog.find('.input-search').on('keyup', function(e) {
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code === 13) { //Enter keycode
        dialog.find('.btn-search').click();
    }    
  });
  
});
JS;
        $template->appendJs($js);
        
        
        $this->setBody($template);
        return parent::show();
    }

    /**
     * DomTemplate magic method
     *
     * @return string
     */
    public function makeBodyTemplate()
    {
        $xhtml = <<<HTML
<div class="row">

  <div class="col-md-12">
    <p var="notes" choice="notes"></p>
    <div class="input-group has-feedback has-feedback-left">
      <input type="text" placeholder="Search by keyword ..." class="form-control input-sm input-search"/>
      <div class="form-control-feedback" style="">
        <i class="fa fa-spinner fa-spin"></i>
      </div>
      <span class="input-group-btn">
        <button type="button" class="btn btn-default btn-sm btn-search">Go!</button>
      </span>
    </div><!-- /input-group -->
  </div>
  
  <div class="col-md-12" >
    <div class="dialog-table" style="min-height: 100px;"></div>
  </div>
  
</div>
HTML;
        return \Dom\Loader::load($xhtml);
    }
}
