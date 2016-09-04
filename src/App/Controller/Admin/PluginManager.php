<?php
namespace App\Controller\Admin;

use Tk\Request;
use Dom\Template;
use App\Controller\Iface;
use Tk\Plugin\Factory;
use Tk\Form;
use Tk\Form\Field;
use Tk\Form\Event;

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
     * @var Form
     */
    protected $form = null;

    /**
     * @var Factory
     */
    protected $pluginFactory = null;

    /**
     * @var \Tk\EventDispatcher\EventDispatcher
     */
    private $dispatcher = null;
    

    /**
     *
     */
    public function __construct()
    {
        parent::__construct('Plugin Manager');
        $this->dispatcher = $this->getConfig()->getEventDispatcher();
    }

    /**
     *
     * @param Request $request
     * @return \App\Page\Iface
     */
    public function doDefault(Request $request)
    {
        $this->pluginFactory = Factory::getInstance($this->getConfig());

        if ($request->has('act')) {
            $this->doActivatePlugin($request);
        } else if ($request->has('del')) {
            $this->doDeletePlugin($request);
        } else if ($request->has('deact')) {
            $this->doDeactivatePlugin($request);
        }

        $this->form = new Form('formEdit');
        $this->form->addField(new Field\File('package', $request))->setRequired(true);
        $this->form->addField(new Event\Button('upload', array($this, 'doUpload')))->addCssClass('btn-primary');

        $this->form->execute();



        return $this->show();
    }

    /**
     * @param \Tk\Form $form
     */
    public function doUpload($form)
    {
        /** @var Field\File $package */
        $package = $form->getField('package');
        if (!$package->isValid()) {
            return;
        }
        if (!preg_match('/\.(zip|gz|tgz)$/i', $package->getUploadedFile()->getFilename())) {
            $form->addFieldError('package', 'Please Select a valid plugin file. (zip/tar.gz/tgz only)');
        }

        $dest = $this->getConfig()->getPluginPath() . '/' . $package->getUploadedFile()->getFilename();
        //vd($dest, str_replace(array('.zip', '.tgz', '.tar.gz'), '', $dest));
        if (is_dir(str_replace(array('.zip', '.tgz', '.tar.gz'), '', $dest))) {
            $form->addFieldError('package', 'A plugin with that name already exists');
        }

        if ($form->hasErrors()) {
            return;
        }

        $package->moveTo($dest);
        $cmd = '';
        vd(\Tk\File::getExtension($dest));
        if (\Tk\File::getExtension($dest) == 'zip') {
            $cmd  = sprintf('cd %s && unzip %s', escapeshellarg(dirname($dest)), escapeshellarg(basename($dest)));
        } else if (\Tk\File::getExtension($dest) == 'gz' || \Tk\File::getExtension($dest) == 'tgz') {
            $cmd  = sprintf('cd %s && tar zxf %s', escapeshellarg(dirname($dest)), escapeshellarg(basename($dest)));
        }
        if ($cmd) {
            $msg = exec($cmd);
            vd($msg);
        }

        \Ts\Alert::addSuccess('Plugin sucessfully uploaded.');
        \Tk\Uri::create()->reset()->redirect();
    }


    protected function doActivatePlugin(Request $request)
    {
        $pluginName = strip_tags(trim($request->get('act')));
        if (!$pluginName) {
            \Ts\Alert::addWarning('Cannot locate Plugin: ' . $pluginName);
            return;
        }
        $this->pluginFactory->activatePlugin($pluginName);
        \Ts\Alert::addSuccess('Plugin `' . $pluginName . '` activated successfully');
        \Tk\Url::create()->reset()->redirect();
    }

    protected function doDeactivatePlugin(Request $request)
    {
        $pluginName = strip_tags(trim($request->get('deact')));
        if (!$pluginName) {
            \Ts\Alert::addWarning('Cannot locate Plugin: ' . $pluginName);
            return;
        }
        $this->pluginFactory->deactivatePlugin($pluginName);
        \Ts\Alert::addSuccess('Plugin `' . $pluginName . '` deactivated successfully');

        \Tk\Url::create()->reset()->redirect();
    }

    protected function doDeletePlugin(Request $request)
    {
        $pluginName = strip_tags(trim($request->get('del')));
        if (!$pluginName) {
            \Ts\Alert::addWarning('Cannot locate Plugin: ' . $pluginName);
            return;
        }
        $pluginPath = $this->pluginFactory->makePluginPath($pluginName);

        if (!is_dir($pluginPath)) {
            \Ts\Alert::addWarning('Plugin `' . $pluginName . '` path not found');
            return;
        }

        // So when we install plugins the archive must be left in the main plugin folder
        if ((!is_file($pluginPath.'.zip') && !is_file($pluginPath.'.tar.gz') && !is_file($pluginPath.'.tgz'))) {
            \Ts\Alert::addWarning('Plugin is protected and must be deleted manually.');
            return;
        }

        \Tk\File::rmdir($pluginPath);
        if (is_file($pluginPath.'.zip'))  unlink($pluginPath.'.zip');
        if (is_file($pluginPath.'.tar.gz'))  unlink($pluginPath.'.tar.gz');
        if (is_file($pluginPath.'.tgz'))  unlink($pluginPath.'.tgz');
        \Ts\Alert::addSuccess('Plugin `' . $pluginName . '` deleted successfully');

        \Tk\Url::create()->reset()->redirect();
    }


    /**
     * @return \App\Page\Iface
     */
    public function show()
    {
        $template = $this->getTemplate();

        // Render the form
        $fren = new \Tk\Form\Renderer\Dom($this->form);
        $template->insertTemplate($this->form->getId(), $fren->show()->getTemplate());

        $list = $this->pluginFactory->getAvailablePlugins();
        foreach ($list as $pluginName) {
            $repeat = $template->getRepeat('row');
            $repeat->insertText('title', ucfirst($pluginName));
            if (is_file($this->getConfig()->getPluginPath().'/'.$pluginName.'/icon.png')) {
                $repeat->setAttr('icon', 'src', $this->getConfig()->getPluginUrl() . '/' . $pluginName . '/icon.png');
                $repeat->setChoice('icon');
            }

            if ($this->pluginFactory->isActive($pluginName)) {
                $repeat->setChoice('active');
                $repeat->setAttr('deact', 'href', \Tk\Url::create()->reset()->set('deact', $pluginName));
            } else {
                $repeat->setChoice('inactive');
                $repeat->setAttr('act', 'href', \Tk\Url::create()->reset()->set('act', $pluginName));
                $repeat->setAttr('del', 'href', \Tk\Url::create()->reset()->set('del', $pluginName));
            }

            $info = $this->pluginFactory->getPluginInfo($pluginName);
            if ($info) {
                $repeat->insertText('name', substr($info->name, strrpos($info->name, '/')+1) );
                $repeat->insertText('package', $info->name);
                if (!empty($info->version)) {
                    $repeat->insertText('version', $info->version);
                    $repeat->setChoice('version');
                }
                if (!empty($info->description)) {
                    $repeat->insertText('desc', $info->description);
                    $repeat->setChoice('desc');
                }
                if (!empty($info->authors)) {
                    $repeat->insertText('author', $info->authors[0]->name);
                    $repeat->setChoice('author');
                }
                if (!empty($info->homepage)) {
                    $repeat->setAttr('www', 'href', $info->homepage);
                    $repeat->insertText('www', $info->homepage);
                    $repeat->setChoice('www');
                }
                $repeat->setChoice('info');
            } else {
                $repeat->insertText('desc', 'Err: No metadata file found!');
            }

            $repeat->appendRepeat();
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
        return confirm('Are you sure you want to uninstall this plugin?');
    });
});
JS;
        $template->appendJs($js);

        return $this->getPage()->setPageContent($template);
    }

    /**
     * DomTemplate magic method
     *
     * @return Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<XHTML
<div class="row">
  <div class="col-md-8 col-sm-12">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="glyphicon glyphicon-compressed"></i> Available Plugins</h3>
      </div>
      <div class="panel-body">

        <ul class="list-group">
          <li class="list-group-item" repeat="row">
            <div class="row">
              <div class="col-xs-2 col-md-1">
                <img class="media-object" src="#" var="icon" style="width: 100%; " choice="icon"/>
              </div>
              <div class="col-xs-10 col-md-11">
                <div>
                  <h4><a href="#" var="title"></a></h4>
                  <p choice="info">
                    <span><strong>Name:</strong> <span var="name"></span></span> <br/>
                    <span><strong>Package:</strong> <span var="package"></span></span> <br/>
                    <span choice="version"><strong>Version:</strong> <span var="version"></span></span> <br choice="version" />
                    <span choice="author"><strong>Author:</strong> <span var="author"></span></span> <br />
                    <span choice="www"><strong>Homepage:</strong> <a href="#" var="www" target="_blank">View Website</a></span>
                  </p>
                </div>
                <p class="comment-text" var="desc" choice="desc"></p>
                <div class="action pull-right">
                  
                  <!-- a href="#" class="btn btn-success btn-xs" choice="active" var="cfg"><i class="glyphicon glyphicon-edit"></i> Config</a -->
                  <span var="pluginHtml" choice="pluginHtml"></span>
                  <a href="#" class="btn btn-primary btn-xs noblock act" choice="inactive" var="act"><i class="glyphicon glyphicon-log-in"></i> Install</a>
                  <a href="#" class="btn btn-danger btn-xs noblock del" choice="inactive" var="del"><i class="glyphicon glyphicon-remove-circle"></i> Delete</a>
                  <a href="#" class="btn btn-warning btn-xs noblock deact" choice="active" var="deact"><i class="glyphicon glyphicon-log-out"></i> Uninstall</a>
                  
                </div>
              </div>
            </div>
          </li>
        </ul>

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
XHTML;

        return \Dom\Loader::load($xhtml);
    }


}