<?php
namespace App\Ui\Dialog;


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
 * 
 * @todo: Review: use the AjaxSelectDialog object as a subclass or in full
 */
class FindUser extends Iface
{

    /**
     * @var array
     */
    protected $filter = array();

    /**
     * @var null|callable
     */
    protected $onSelect = null;


    /**
     * DialogBox constructor.
     *
     * @param $title
     * @param array $filter
     * @throws \Tk\Exception
     */
    public function __construct($title, $filter = array())
    {
        parent::__construct($title);
        $this->filter = $filter;

        $this->addButton('Close');
    }

    /**
     * @param callable $callable
     * @return $this
     */
    public function setOnSelect($callable)
    {
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
            $data = array();
            $data['userHash'] = $request->get('userHash');
            
            // TODO: Populate the data with the submitted user
            if (is_callable($this->onSelect)) {
                call_user_func_array($this->onSelect, array($this, $data));
            }
            
            \Tk\Uri::create()->remove($this->getSelectButtonId())->remove('userHash')->redirect();
        }
        
    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = $this->makeBodyTemplate();
        $ajaxUrl = \Uni\Uri::create('/ajax/user/findFiltered.html')->toString();
        $actionUrl = \Uni\Uri::create()->set($this->getSelectButtonId())->toString();

        
        $json = json_encode($this->filter);
        $dialogId = $this->getId(); 
        
        $js = <<<JS
jQuery(function($) {
  
  var dialog = $('#$dialogId');
  var actionUrl = '$actionUrl';
  processing(false);
  
  dialog.find('.btn-search').click(function(e) {
    processing(true);
    var data = $json;
    data.keywords = dialog.find('.input-search').val(); 
    $.get('$ajaxUrl', data, function (data) {
      var table = buildTable(data);
      dialog.find('.dialog-table').empty().append(table);
      
      processing(false);
    });
  });
  
  function buildTable(data) {
    
    if (data.length == 0) {
      return $('<p class="text-center" style="margin-top: 10px;font-weight: bold;font-style: italic;">No Users Found!</p>');
    }
    
    var table = $('<table class="table" style="margin-top: 10px;"><tr><th>Name</th><th>Email</th><th>UID</th></tr> <tr class="data-tpl"><td class="name"><a href="javascript:;" class="nameUrl"></a></td><td class="email"></td><td class="uid"></td></tr> </table>');
    
    $.each(data, function (i, user) {
      //console.log(user);
      var row = table.find('tr.data-tpl').clone();
      row.removeClass('data-tpl').addClass('data');
      row.find('.name .nameUrl').text(user.name).attr('href', actionUrl+'&userHash='+user.hash);
      row.find('.email').text(user.email);
      row.find('.uid').text(user.uid);
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
    } else {
      dialog.find('.form-control-feedback').hide();
      dialog.find('.input-search').removeAttr('disabled');
      dialog.find('.btn-search').removeAttr('disabled');
    }
  }
  
  // Some focus and key logic
  dialog.on('shown.bs.modal', function (e) {
    dialog.find('.input-search').val('').focus();
  });
  dialog.find('.input-search').on('keyup', function(e) {
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code == 13) { //Enter keycode
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
    <div class="input-group has-feedback has-feedback-left">
      <input type="text" placeholder="Search by username, email, name" class="form-control input-sm input-search"/>
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
