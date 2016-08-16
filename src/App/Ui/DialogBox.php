<?php
namespace App\Ui;

use Dom\Template;
use Tk\Request;

/**
 * This class uses the bootstrap dialog box model
 * @link http://getbootstrap.com/javascript/#modals
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class DialogBox extends \Dom\Renderer\Renderer
{

    /**
     * @var array
     */
    protected $buttonList = array();

    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var string
     */
    protected $body = '';


    /**
     * DialogBox constructor.
     * @param $title
     * @param string $body
     */
    public function __construct($title, $body = '')
    {
        $this->setTitle($title);
        $this->setBody($body);
    }

    /**
     * @return string
     */
    public function getId()
    {
        return 'fid-' . preg_replace('/[^a-z0-9]/i', '_', $this->title);
    }

    /**
     * @param $title
     * @return $this
     */
    public function setTitle($title) {
        $this->title = $title;
        return $this;
    }

    /**
     * @param $html
     * @return $this
     */
    public function setBody($html) {
        $this->body = $html;
        return $this;
    }

    /**
     * Add Button
     *
     * @param string $name
     * @param array $attributes
     * @param string $icon
     * @return $this
     */
    public function addButton($name, $attributes = array(), $icon = '')
    {
        if (strtolower($name) == 'close' || strtolower($name) == 'cancel') {
            $attributes['data-dismiss'] = 'modal';
        }
        $attributes['name'] = $name;
        $attributes['id'] = 'fid-' . preg_replace('/[^a-z0-9]/i', '_', $name);

        $this->buttonList[] = array(
            'name' => $name,
            'attributes' => $attributes,
            'icon' => $icon
        );
        return $this;
    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = $this->getTemplate();
        $template->insertText('title', $this->title);
        $template->insertHtml('body', $this->body);

        foreach ($this->buttonList as $btn) {
            $row = $template->getRepeat('btn');
            $row->insertText('name', $btn['name']);
            if ($btn['icon']) {
                $row->setChoice('icon');
                $row->addClass('icon', $btn['icon']);
            }
            foreach ($btn['attributes'] as $k => $v) {
                $row->setAttr('btn', strip_tags($k), $v);
            }
            $row->appendRepeat();
        }

        $template->setAttr('dialog', 'id', $this->getId());
        $template->setAttr('dialog', 'aria-labelledby', $this->getId().'Label');
        $template->setAttr('title', 'id', $this->getId().'Label');

        return $template;
    }


    /**
     * DomTemplate magic method
     *
     * @return Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<XHTML
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" var="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="exampleModalLabel" var="title">New message</h4>
      </div>
      <div class="modal-body" var="body">
        
      </div>
      <div class="modal-footer" var="footer">
        <button type="button" class="btn btn-default" repeat="btn" var="btn"><i var="icon" choice="icon"></i> <span var="name"></span></button>
      </div>
    </div>
  </div>
</div>
XHTML;

        return \Dom\Loader::load($xhtml);
    }
}
