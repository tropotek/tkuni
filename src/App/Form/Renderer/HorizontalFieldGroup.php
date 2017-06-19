<?php
namespace App\Form\Renderer;

use Tk\Form\Field;
use Tk\Form\Event;
use Tk\Form\Element;

/**
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class HorizontalFieldGroup extends \Tk\Form\Renderer\FieldGroup
{


    /**
     * __construct
     *
     *
     * @param Field\Iface $field
     */
    public function __construct($field)
    {
        parent::__construct($field);
    }


    /**
     * Render
     */
    public function show()
    {
        $t = $this->getTemplate();
        parent::show();


        
        return $t;
    }

    /**
     * makeTemplate
     *
     * @return string
     */
    protected function __makeTemplate()
    {
        $xhtml = <<<XHTML
<div class="form-group has-feedback" var="field-group">
  <span class="col-md-offset-2 col-md-10 help-block " choice="errorText"><span class="glyphicon glyphicon-ban-circle"></span> <span var="errorText"></span></span>
  <label class="col-md-2 control-label" var="label" choice="label">&nbsp;</label>
  <div class="col-md-10">
    <div var="element" class="controls"></div>
    <!--<div class="form-control-feedback" choice="errorText">-->
      <!--<i class="fa fa-times"></i>-->
    <!--</div>-->
    <span class="help-block help-text" var="notes" choice="notes"></span>
  </div>
</div>
XHTML;

        return \Dom\Loader::load($xhtml);
    }

}
