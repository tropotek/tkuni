<?php
namespace App\Controller\Admin;

use Tk\Request;
use Dom\Template;
use Tk\Form;
use Tk\Form\Field;
use Tk\Form\Event;
use App\Controller\Iface;

/**
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class PluginManager extends Iface
{

    /**
     * @var \Tk\Table
     */
    protected $table = null;

    /**
     * @var Form
     */
    protected $form = null;


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
        $this->setPageTitle('Plugin Manager');

        $this->pluginFactory = \App\Factory::getPluginFactory();

        // Upload plugin
        $this->form = \App\Factory::createForm('pluginEdit');
        $this->form->setParam('renderer', \App\Factory::createFormRenderer($this->form));
        $this->form->addField(new Field\File('package', '', $this->getConfig()->getPluginPath()))->addCss('tk-fileinput');
        $this->form->addField(new Event\Button('upload', array($this, 'doUpload')))->addCss('btn-primary');
        $this->form->execute();

        // Plugin manager table
        $this->table = \App\Factory::createTable('PluginList');
        $this->table->setParam('renderer', \App\Factory::createTableRenderer($this->table));

        $this->table->addCell(new IconCell('icon'))->setLabel('');
        $this->table->addCell(new ActionsCell('actions'));
        $this->table->addCell(new \Tk\Table\Cell\Text('name'))->addCss('key')->setOrderProperty('');
        $this->table->addCell(new \Tk\Table\Cell\Text('access'))->setOrderProperty('');
        $this->table->addCell(new \Tk\Table\Cell\Text('version'))->setOrderProperty('');
        $this->table->addCell(new \Tk\Table\Cell\Date('time'))->setFormat(\Tk\Date::FORMAT_MED_DATE)->setLabel('Created')->setOrderProperty('');

        $this->table->setList($this->getPluginList());
        
    }

    /**
     * @return array
     */
    private function getPluginList()
    {
        $pluginFactory = \App\Factory::getPluginFactory();
        $list = array();
        $names = $pluginFactory->getAvailablePlugins();
        foreach ($names as $pluginName) {
            $info = $pluginFactory->getPluginInfo($pluginName);
            $info->name = str_replace('ttek-plg/', '', $info->name);
            if (empty($info->access)) {
                $info->access = 'admin';
            }
            $info->time = \Tk\Date::create($info->time);
            $list[$pluginName] = $info;
        }
        return $list;
    }

    /**
     * @param \Tk\Form $form
     */
    public function doUpload($form)
    {
        /* @var Field\File $package */
        $package = $form->getField('package');

        if (!preg_match('/\.(zip|gz|tgz)$/i', $package->getValue())) {
            $form->addFieldError('package', 'Please Select a valid plugin file. (zip/tar.gz/tgz only)');
        }
        $dest = $this->getConfig()->getPluginPath() . $package->getValue();
        if (is_dir(str_replace(array('.zip', '.tgz', '.tar.gz'), '', $dest))) {
            $form->addFieldError('package', 'A plugin with that name already exists');
        }

        if ($form->hasErrors()) {
            return;
        }

        $package->saveFile();

        $cmd = '';
        if (\Tk\File::getExtension($dest) == 'zip') {
            $cmd  = sprintf('cd %s && unzip %s', escapeshellarg(dirname($dest)), escapeshellarg(basename($dest)));
        } else if (\Tk\File::getExtension($dest) == 'gz' || \Tk\File::getExtension($dest) == 'tgz') {
            $cmd  = sprintf('cd %s && tar zxf %s', escapeshellarg(dirname($dest)), escapeshellarg(basename($dest)));
        }
        if ($cmd) {
            exec($cmd, $output);
        }

        // TODO: check the plugin is a valid Tk plugin, if not remove the archive and files and throw an error
        // Look for a Plugin.php file and Class maybe????

        \Tk\Alert::addSuccess('Plugin successfully uploaded.');
        \Tk\Uri::create()->reset()->redirect();
    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = parent::show();

        // Render the form
        $template->insertTemplate('form', $this->form->getParam('renderer')->show()->getTemplate());

        // render Table
        $template->replaceTemplate('PluginList', $this->table->getParam('renderer')->show());

        return $template;
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
      <a href="javascript: window.history.back();" class="btn btn-default back" var="back"><i class="fa fa-arrow-left"></i> <span>Back</span></a>
    </div>
  </div>

  <div class="row">
    <div class="col-md-8 col-sm-12">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h4 class="panel-title"><i class="glyphicon glyphicon-compressed"></i> Available Plugins</h4>
        </div>
        <div class="panel-body">
          <div class="pluginList" var="PluginList"></div>
        </div>
      </div>
    </div>

    <div class="col-md-4 col-sm-12">
      <div class="panel panel-default" id="uploadForm">
        <div class="panel-heading">
          <h3 class="panel-title"><span class="glyphicon glyphicon-log-out"></span> Upload Plugin</h3>
        </div>
        <div class="panel-body">
          <p>Select A zip/tgz plugin package to upload.</p>
          <div var="formEdit"></div>
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

        if (is_file(\Tk\Config::getInstance()->getPluginPath().'/'.$pluginName.'/icon.png')) {
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
     * Called when the Table::execute is called
     */
    public function execute() {
        /** @var \Tk\Request $request */
        $request = \Tk\Config::getInstance()->getRequest();

        if ($request->has('act')) {
            $this->doActivatePlugin($request);
        } else if ($request->has('del')) {
            $this->doDeletePlugin($request);
        } else if ($request->has('deact')) {
            $this->doDeactivatePlugin($request);
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

        if ($pluginFactory->isActive($pluginName)) {
            $plugin = $pluginFactory->getPlugin($pluginName);
            $template->setChoice('active');
            $template->setAttr('deact', 'href', \Tk\Uri::create()->reset()->set('deact', $pluginName));
            $this->getRow()->addCss('plugin-active');

            if ($plugin->getSettingsUrl()) {
                $template->setAttr('setup', 'href', $plugin->getSettingsUrl());
            } else {
                $template->addCss('setup', 'disabled');
            }
        } else {
            $template->setChoice('inactive');
            $template->setAttr('act', 'href', \Tk\Uri::create()->reset()->set('act', $pluginName));
            $this->getRow()->addCss('plugin-inactive');

            if (!\Tk\Plugin\Factory::isComposer($pluginName, \Tk\Config::getInstance()->getComposer())) {
                $template->setAttr('del', 'href', \Tk\Uri::create()->reset()->set('del', $pluginName));
            } else {
                $template->addCss('del', 'disabled');
                $template->setAttr('del', 'title', 'Cannot delete a composer plugin. See site administrator.');
            }

        }

        $js = <<<JS
jQuery(function ($) {
    $('.act').click(function (e) {
        return confirm('Are you sure you want to install this plugin?');
    });
    $('.del').click(function (e) {
        return confirm('Are you sure you want to delete this plugin?');
    });
    $('.deact').click(function (e) {
        return confirm('Are you sure you want to uninstall this plugin?\\nThis will delete all data relating to the plugin.');
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
<a href="#" class="btn btn-success btn-xs noblock act" choice="inactive" var="act" title="Activate Plugin"><i class="glyphicon glyphicon-log-in"></i></a>
<a href="#" class="btn btn-danger btn-xs noblock del" choice="inactive" var="del" title="Delete Plugin Files"><i class="glyphicon glyphicon-remove-circle"></i></a>
<a href="#" class="btn btn-primary btn-xs noblock setup" choice="active" var="setup" title="Configure Plugin"><i class="glyphicon glyphicon-cog"></i></a>
<a href="#" class="btn btn-danger btn-xs noblock deact" choice="active" var="deact" title="Deactivate Plugin"><i class="glyphicon glyphicon-off"></i></a>
</div>
HTML;
        return \Dom\Loader::load($html);
    }

    protected function doActivatePlugin(Request $request)
    {
        $pluginFactory = \App\Factory::getPluginFactory();
        $pluginName = strip_tags(trim($request->get('act')));
        if (!$pluginName) {
            \Tk\Alert::addWarning('Cannot locate Plugin: ' . $pluginName);
            return;
        }
        try {
            $pluginFactory->activatePlugin($pluginName);
            \Tk\Alert::addSuccess('Plugin `' . $pluginName . '` activated successfully');
        }catch (\Exception $e) {
            \Tk\Alert::addError('Activate Failed: ' . $e->getMessage());
        }
        \Tk\Uri::create()->reset()->redirect();
    }

    protected function doDeactivatePlugin(Request $request)
    {
        $pluginName = strip_tags(trim($request->get('deact')));
        if (!$pluginName) {
            \Tk\Alert::addWarning('Cannot locate Plugin: ' . $pluginName);
            return;
        }
        try {
            \App\Factory::getPluginFactory()->deactivatePlugin($pluginName);
            \Tk\Alert::addSuccess('Plugin `' . $pluginName . '` deactivated successfully');
        }catch (\Exception $e) {
            \Tk\Alert::addError('Deactivate Failed: ' . $e->getMessage());
        }
        \Tk\Uri::create()->reset()->redirect();
    }

    protected function doDeletePlugin(Request $request)
    {
        $pluginName = strip_tags(trim($request->get('del')));
        if (!$pluginName) {
            \Tk\Alert::addWarning('Cannot locate Plugin: ' . $pluginName);
            return;
        }
        $pluginPath = \App\Factory::getPluginFactory()->getPluginPath($pluginName);

        if (!is_dir($pluginPath)) {
            \Tk\Alert::addWarning('Plugin `' . $pluginName . '` path not found');
            return;
        }

        // So when we install plugins the archive must be left in the main plugin folder
        if ((!is_file($pluginPath.'.zip') && !is_file($pluginPath.'.tar.gz') && !is_file($pluginPath.'.tgz'))) {
            \Tk\Alert::addWarning('Plugin is protected and must be deleted manually.');
            return;
        }

        \Tk\File::rmdir($pluginPath);
        if (is_file($pluginPath.'.zip'))  unlink($pluginPath.'.zip');
        if (is_file($pluginPath.'.tar.gz'))  unlink($pluginPath.'.tar.gz');
        if (is_file($pluginPath.'.tgz'))  unlink($pluginPath.'.tgz');
        \Tk\Alert::addSuccess('Plugin `' . $pluginName . '` deleted successfully');

        \Tk\Uri::create()->reset()->redirect();
    }

}

