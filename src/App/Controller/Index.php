<?php
namespace App\Controller;

use Tk\Request;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Index extends \Uni\Controller\Index
{
    /**
     * @var null|\Tk\Table
     */
    protected $table = null;
    
    /**
     * @param Request $request
     */
    public function doDefault(Request $request)
    {
        parent::doDefault($request);

        $this->table = $this->getConfig()->createTable('institution-list');
        $this->table = $this->getConfig()->createTable('institution-list');
        $this->table->setRenderer($this->getConfig()->createTableRenderer($this->table));

        $actionsCell = new \Tk\Table\Cell\Actions();
        $actionsCell->addButton(\Tk\Table\Cell\ActionButton::create('Login', \Tk\Uri::create(), 'fa  fa-sign-in', 'button-small soft')->setAttr('title', 'Institution Login'))
            ->addOnShow(function ($cell, $obj, $button) {
                /* @var $obj \Uni\Db\Institution */
                /* @var $button \Tk\Table\Cell\ActionButton */
                $button->setUrl($obj->getLoginUrl());
            });

        $this->table->appendCell($actionsCell);
        $this->table->appendCell(new \Tk\Table\Cell\Text('name'))->setUrl(\Tk\Uri::create('/institutionEdit.html'))
            ->addOnPropertyValue(function ($cell, $obj, $value) {
                /* @var $obj \Uni\Db\Institution */
                /* @var $cell \Tk\Table\Cell\Text */
                $cell->setUrl($obj->getLoginUrl());
                return $value;
            });
        $this->table->appendCell(new \Tk\Table\Cell\Text('description'))->addCss('key')->setCharacterLimit(150);

        $filter = $this->table->getFilterValues();
        $filter['active'] = true;
        $list = $this->getConfig()->getInstitutionMapper()->findFiltered($filter, $this->table->getTool());
        $this->table->setList($list);
    }


    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = parent::show();

        $template->insertText('site-title', $this->getConfig()->get('site.title'));

        $template->appendTemplate('table', $this->table->getRenderer()->show());

        if ($this->getConfig()->getInstitutionMapper()->findActive()->count() > 1) {
            $template->setVisible("multiInstitutions");
        }

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

  <div class="tabbed-nav" data-tabbed="" id="nav">
    <div class="full-width">
      <nav class="desktop-nav" role="tablist">
        <a href="#welcome" role="tab">Welcome</a>
        <a href="#institutions" role="tab" choice="multiInstitutions">Institutions</a>
        <a href="#contact" role="tab">Contact</a>
        <a href="/login.html" var="institution-login" role="tab" choice1="login">Login</a>
      </nav>
    </div>

    <div class="tab" id="welcome" role="tabpanel">
      <section class="with-figure">
        <h2 class="title" var="site-title">FVAS VOCE</h2>
        <div>
          <h1>The Project</h1>
          <p>
            Welcome to the <span var="site-title">Project</span>.<br/>
            This project has been developed by FVAS staff to provide academics and students with an
            tool where students can upload videos and have them peer reviewed by others within their class.<br/>
            The system was also developed to upload the videos to an external repository such as YouTube or VIMEO
            thus taking the large bandwidth stress and compression issues away from our local servers.
          </p>
          <h1>Access Via LMS</h1>
          <p>
            The application is accessed as an <a href="https://www.imsglobal.org/activity/learning-tools-interoperability" target="_blank">LTI</a>
            Web Link within the LMS.
          </p>
          <p>
            If you wish to use this LTI Tool in your course all you need to do is add a web link into your
            LMS course content. Contact our team today if you want to find out more.
          </p>
        </div>
      </section>
      <hr class="spacer" />
      <p class="center"><a class="button-hero" href="https://app.lms.unimelb.edu.au/">Access The LMS</a></p>
    </div>

    <div class="tab" id="institutions" role="tabpanel" choice="multiInstitutions">
      <section var="table"></section>
    </div>

    <div class="tab" id="contact" role="tabpanel">
      <section>
        <div class="contact-box">
          <h1>Werribee Campus</h1>
          <dl>
            <dt>Address</dt>
            <dd>250 Princes Highway<br />Werribee Victoria 3030</dd>
            <dt>Email</dt>
            <dd><a href="mailto: fvas-elearning@unimelb.edu.au">fvas-elearning@unimelb.edu.au</a></dd>
          </dl>
        </div>
      </section>
      <section class="fullwidth">
        <div class="gmap__canvas" data-grayscale="" data-latlng="-37.889149, 144.693133" data-pin="-37.889149, 144.693133"></div>
      </section>
    </div>


  </div>

</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }
}