<?php
namespace App\Controller\Admin\Dev;

use Tk\Request;
use Dom\Template;
use App\Controller\Iface;

/**
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class SystemEvents extends Iface
{

    /**
     * @var \Tk\Table
     */
    protected $table = null;

    
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     *
     * @param Request $request
     */
    public function doDefault(Request $request)
    {
        $this->setPageTitle('Available Events');

        $this->table = \App\Factory::createTable(\Tk\Object::basename($this).'PluEventList');
        $this->table->setParam('renderer', \App\Factory::createTableRenderer($this->table));

        $this->table->addCell(new \Tk\Table\Cell\Text('name'));
        $this->table->addCell(new \Tk\Table\Cell\Text('value'));
        $this->table->addCell(new \Tk\Table\Cell\Text('eventClass'));
        $this->table->addCell(new \Tk\Table\Cell\Html('doc'))->addCss('key');

        $this->table->addAction(\Tk\Table\Action\Csv::create());

        $list = $this->convertEventData(\App\Factory::getEventDispatcher()->getAvailableEvents(\App\Factory::getConfig()->getSitePath()));
        $this->table->setList($list);
        
    }

    /**
     * Convert the event data to a format suitable for the Table renderer.
     * @param $eventData
     * @return array
     */
    protected function convertEventData($eventData)
    {
        $data = array();
        foreach ($eventData as $className => $eventArray) {

            foreach ($eventArray['const'] as $consName => $constData) {
                $data[] = array(
                    'name' => '\\'.$className . '::' . $consName,
                    'value' => $constData['value'],
                    'eventClass' => '\\'.$constData['event'],
                    'doc' => nl2br($constData['doc'])
                );
            }
        }
        return $data;
    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = parent::show();

        $template->replaceTemplate('table', $this->table->getParam('renderer')->show());

        return $template;
    }


    /**
     * DomTemplate magic method
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div class="">

  <div class="panel panel-default">
      <div class="panel-heading">
        <i class="fa fa-cogs fa-fw"></i> Actions
      </div>
      <div class="panel-body">
            <a href="javascript: window.history.back();" class="btn btn-default"><i class="fa fa-arrow-left"></i> <span>Back</span></a>
      </div>
    </div>
  
    <div class="panel panel-default">
      <div class="panel-heading">
        <i class="fa fa-empire fa-fw"></i> Available Events
      </div>
      <div class="panel-body">
        <p>The events are available for use with plugins or when adding to the system codebase.</p>
        <div var="table"></div>
      </div>
    </div>
    
</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }

}