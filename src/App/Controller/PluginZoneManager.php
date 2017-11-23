<?php
namespace App\Controller;

use Tk\Request;
use Dom\Template;

/**
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class PluginZoneManager extends Iface
{

    /**
     * @var \Tk\Table
     */
    protected $table = null;

    /**
     * @var string
     */
    protected $zoneName = '';

    /**
     * @var int
     */
    protected $zoneId = 0;

    
    /**
     *
     * @param Request $request
     * @throws \Tk\Exception
     */
    public function doDefault(Request $request, $zoneName, $zoneId)
    {
        $this->setPageTitle('Plugin Manager');

        $this->zoneName = $zoneName;
        $this->zoneId = $zoneId;
        if (!$this->zoneName || !$this->zoneId) {
            throw new \Tk\Exception('Invalid zone plugin information?');
        }

        $this->pluginFactory = \App\Factory::getPluginFactory();
        // Plugin manager table
        $this->table = \App\Factory::createTable('PluginList');
        $this->table->setRenderer(\App\Factory::createTableRenderer($this->table));

        $this->table->addCell(new IconCell('icon'))->setLabel('');
        $this->table->addCell(new ActionsCell($this->zoneName, $this->zoneId));
        $this->table->addCell(new \Tk\Table\Cell\Text('name'))->addCss('key')->setOrderProperty('');
        $this->table->addCell(new \Tk\Table\Cell\Text('version'))->setOrderProperty('');
        $this->table->addCell(new \Tk\Table\Cell\Date('time'))->setLabel('Created')->setOrderProperty('');
        
        $this->table->setList($this->getPluginList());

    }

    /**
     * @return array
     */
    private function getPluginList()
    {
        $pluginFactory = \App\Factory::getPluginFactory();
        $plugins = $pluginFactory->getZonePluginList($this->zoneName);
        $list = array();
        /** @var \Tk\Plugin\Iface $plugin */
        foreach ($plugins as $plugin) {
            $info = $plugin->getInfo();
            $info->name = str_replace('ttek-plg/', '', $info->name);
            $list[] = $info;
        }
        return $list;
    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = parent::show();

        // render Table
        $template->replaceTemplate('PluginList', $this->table->getRenderer()->show());

        $template->insertText('zone', $this->makeTitleFromZone($this->zoneName));

        return $template;
    }

    /**
     * @param $str
     * @return string
     */
    protected function makeTitleFromZone($str)
    {
        $str = preg_replace('/[A-Z]/', ' $0', $str);
        $str = preg_replace('/[^a-z0-9]/i', ' ', $str);
        return ucwords($str);
    }


    /**
     * DomTemplate magic method
     *
     * @return Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div>

  <div class="panel panel-default panel-shortcut">
    <div class="panel-heading">
      <h4 class="panel-title"><i class="fa fa-cogs"></i> Actions</h4>
    </div>
    <div class="panel-body">
      <a href="javascript: window.history.back();" class="btn btn-default btn-once back" var="back"><i class="fa fa-arrow-left"></i> <span>Back</span></a>
    </div>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h4 class="panel-title"><i class="glyphicon glyphicon-compressed"></i> Available <span var="zone"></span> Plugins</h4>
        </div>
        <div class="panel-body">
          <div class="pluginList" var="PluginList"></div>
        </div>
      </div>
    </div>
  </div>

</div>
HTML;
        return \Dom\Loader::load($xhtml);
    }

}

class IconCell extends \Tk\Table\Cell\Text
{

    /**
     * OwnerCell constructor.
     *
     * @param string $property
     * @param null $label
     */
    public function __construct($property, $label = null)
    {
        parent::__construct($property, $label);
        $this->setOrderProperty('');
    }

    /**
     * @param \StdClass $info
     * @param int|null $rowIdx The current row being rendered (0-n) If null no rowIdx available.
     * @return string|\Dom\Template
     */
    public function getCellHtml($info, $rowIdx = null)
    {
        $template = $this->__makeTemplate();

        $pluginName = \App\Factory::getPluginFactory()->cleanPluginName($info->name);
        if (is_file(\Tk\Config::getInstance()->getPluginPath() . '/' . $pluginName . '/icon.png')) {
            $template->setAttr('icon', 'src', \Tk\Config::getInstance()->getPluginUrl() . '/' . $pluginName . '/icon.png');
            $template->setChoice('icon');
        }

        return $template;
    }

    /**
     * makeTemplate
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $html = <<<HTML
<div>
  <img class="media-object" src="#" var="icon" style="width: 20px;" choice="icon"/>
</div>
HTML;
        return \Dom\Loader::load($html);
    }
}

class ActionsCell extends \Tk\Table\Cell\Text
{

    /**
     * @var string
     */
    protected $zoneName = '';

    /**
     * @var int
     */
    protected $zoneId = 0;


    /**
     * ActionsCell constructor.
     * @param string $zoneName
     * @param int $zoneId
     */
    public function __construct($zoneName, $zoneId)
    {
        parent::__construct('actions');
        $this->setOrderProperty('');
        $this->zoneName = $zoneName;
        $this->zoneId = $zoneId;
    }

    /**
     * Called when the Table::execute is called
     */
    public function execute() {
        /** @var \Tk\Request $request */
        $request = \Tk\Config::getInstance()->getRequest();

        if ($request->has('enable')) {
            $this->doEnablePlugin($request);
        } else if ($request->has('disable')) {
            $this->doDisablePlugin($request);
        }

    }

    /**
     * @param \StdClass $info
     * @param int|null $rowIdx The current row being rendered (0-n) If null no rowIdx available.
     * @return string|\Dom\Template
     */
    public function getCellHtml($info, $rowIdx = null)
    {
        $template = $this->__makeTemplate();
        $pluginFactory = \App\Factory::getPluginFactory();
        $pluginName = $pluginFactory->cleanPluginName($info->name);
        /** @var \Tk\Plugin\Iface $plugin */
        $plugin = $pluginFactory->getPlugin($pluginName);

        if ($plugin->isZonePluginEnabled($this->zoneName, $this->zoneId)) {
            $this->getRow()->addCss('plugin-active');
            $template->setChoice('active');
            $template->setAttr('disable', 'href', \Tk\Uri::create()->set('disable', $plugin->getName()));

            $template->setAttr('title', 'href', $plugin->getZoneSettingsUrl($this->zoneName));
            $template->setAttr('setup', 'href', $plugin->getZoneSettingsUrl($this->zoneName)->set('zoneId', $this->zoneId));
        } else {
            $this->getRow()->addCss('plugin-inactive');
            $template->setChoice('inactive');
            $template->setAttr('enable', 'href', \Tk\Uri::create()->set('enable', $plugin->getName()));
        }

        $js = <<<JS
jQuery(function ($) {
    $('a.act').click(function (e) {
        return confirm('Are you sure you want to enable this plugin?');
    });
    $('a.deact').click(function (e) {
        return confirm('Are you sure you want to disable this plugin?');
    });
});
JS;
        $template->appendJs($js);

        $css = <<<CSS
#PluginList .plugin-inactive td {
  opacity: 0.5;
}
#PluginList .plugin-inactive td.mActions {
  opacity: 1;  
}
CSS;
        $template->appendCss($css);

        return $template;
    }

    /**
     * makeTemplate
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $html = <<<HTML
<div class="text-right">
  <a href="#" class="btn btn-success btn-xs noblock act" choice="inactive" var="enable" title="Enable Plugin"><i class="glyphicon glyphicon-log-in"></i></a>
  <a href="#" class="btn btn-primary btn-xs noblock setup" choice="active" var="setup" title="Configure Plugin"><i class="glyphicon glyphicon-cog"></i></a>
  <a href="#" class="btn btn-danger btn-xs noblock deact" choice="active" var="disable" title="Disable Plugin"><i class="glyphicon glyphicon-remove"></i></a>
</div>
HTML;
        return \Dom\Loader::load($html);
    }

    protected function doEnablePlugin(Request $request)
    {
        $pluginName = strip_tags(trim($request->get('enable')));
        if (!$pluginName) {
            \Tk\Alert::addWarning('Cannot locate Plugin: ' . $pluginName);
            return;
        }
        try {
            \App\Factory::getPluginFactory()->enableZonePlugin($pluginName, $this->zoneName, $this->zoneId);
            \Tk\Alert::addSuccess('Plugin `' . $pluginName . '` enabled.');
        }catch (\Exception $e) {
            \Tk\Alert::addError('Plugin `' . $pluginName . '` cannot be enabled.');
        }
        \Tk\Uri::create()->remove('enable')->redirect();
    }

    protected function doDisablePlugin(Request $request)
    {
        $pluginName = strip_tags(trim($request->get('disable')));
        if (!$pluginName) {
            \Tk\Alert::addWarning('Cannot locate Plugin: ' . $pluginName);
            return;
        }
        \App\Factory::getPluginFactory()->disableZonePlugin($pluginName, $this->zoneName, $this->zoneId);
        \Tk\Alert::addSuccess('Plugin `' . $pluginName . '` disabled');
        \Tk\Uri::create()->remove('disable')->redirect();
    }

}




