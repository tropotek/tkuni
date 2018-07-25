<?php
namespace App\Ui\Menu;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2018 Michael Mifsud
 */
class StudentMenu extends Iface
{


    /**
     * @return \Dom\Template
     * @throws \Exception
     */
    public function show()
    {
        $template = parent::show();

//        if($this->getConfig()->isSubjectUrl()) {
//            $subject = $this->getConfig()->getSubject();
//            $template->setAttr('subject-dashboard', 'href', \Uni\Uri::createSubjectUrl('/index.html', $subject));
//            $template->setText('subject-name', $subject->code);
//            $template->setChoice('subject');
//        }

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
      <a class="navbar-brand" href="/student/index.html" var="site-title">Student</a>
    </div>
    <!-- /.navbar-header -->

    <ul class="nav navbar-top-links navbar-right">
      <li class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
          <i class="fa fa-fw fa-user"></i> <span var="username">Admin</span> <i class="fa fa-caret-down"></i>
        </a>
        <ul class="dropdown-menu dropdown-user">
          <li><a href="/student/profile.html"><i class="fa fa-fw fa-user"></i> My Profile</a></li>
          <li class="divider"></li>
          <li><a href="/logout.html"><i class="fa fa-fw fa-sign-out"></i> Logout</a></li>
        </ul>
      </li>
    </ul>

    <div class="navbar-default sidebar" role="navigation">
      <div class="sidebar-nav navbar-collapse">
        <ul class="nav" id="side-menu" var="side-menu">
          <li><a href="/student/index.html"><i class="fa fa-fw fa-dashboard"></i> Dashboard</a></li>
        
          <li choice="subject"><a href="#"><i class="fa fa-cogs fa-fw"></i> <span var="subject-name">Subject</span> <span class="fa arrow"></span></a>
            <ul class="nav nav-second-level" var="subject-menu">
              <li><a href="/index.html" var="subject-dashboard"><i class="fa fa-fw fa-dashboard"></i> Subject Dashboard</a></li>
            </ul>
          </li>
        
        </ul>
      </div>
    </div>
    
</nav>
XHTML;

        return \Dom\Loader::load($xhtml);
    }

}