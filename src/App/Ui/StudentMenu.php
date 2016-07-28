<?php
namespace App\Ui;


use Dom\Template;

/**
 * Class StudentMenu
 */
class StudentMenu extends \Dom\Renderer\Renderer
{


    /**
     * StudentMenu constructor.
     */
    public function __construct()
    {

    }

    /**
     * @return StudentMenu
     */
    static function create()
    {
        return new static();
    }

    /**
     * @return \Tk\Config
     */
    public function getConfig()
    {
        return \App\Factory::getConfig();
    }

    /**
     * @return \App\Db\User
     */
    public function getUser()
    {
        return $this->getConfig()->getUser();
    }

    /**
     *
     * @return \Dom\Template
     */
    public function show()
    {
        $template = $this->getTemplate();

        $template->insertText('username', $this->getUser()->name);

        return $template;
    }


    /**
     * DomTemplate magic method
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<XHTML
<nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">

    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="/student/index.html" var="siteTitle">Tk2Uni v2.0 Template</a>
    </div>
    <!-- /.navbar-header -->

    <ul class="nav navbar-top-links navbar-right">
      <li class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
          <i class="fa fa-user fa-fw"></i> <span var="username">Admin</span> <i class="fa fa-caret-down"></i>
        </a>
        <ul class="dropdown-menu dropdown-user">
          <li><a href="/student/profile.html"><i class="fa fa-user fa-fw"></i> My Profile</a></li>
          <li class="divider"></li>
          <li><a href="/logout.html"><i class="fa fa-sign-out fa-fw"></i> Logout</a></li>
        </ul>
      </li>
    </ul>

    <div class="navbar-default sidebar" role="navigation">
      <div class="sidebar-nav navbar-collapse">
        <ul class="nav" id="side-menu">
          <li><a href="/student/index.html"><i class="fa fa-dashboard fa-fw"></i> Dashboard</a></li>
          <li><a href="/student/courseManager.html"><i class="fa fa-university fa-fw"></i> Courses</a></li>
        </ul>
      </div>
    </div>
    
</nav>
XHTML;

        return \Dom\Loader::load($xhtml);
    }

}