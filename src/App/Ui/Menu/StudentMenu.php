<?php
namespace App\Ui\Menu;


/**
 * Class StudentMenu
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class StudentMenu extends Iface
{


    /**
     * @return \Dom\Template
     * @throws \Exception
     */
    public function show()
    {
        $template = $this->getTemplate();

        $template->insertText('username', $this->getUser()->getName());
        $subject = $this->getConfig()->getSubject();

        if($subject) {
            $template->setAttr('subject-dashboard', 'href', \Uni\Uri::createSubjectUrl('/index.html', $subject));
            $template->setChoice('subject');

        } else {
            $list = $this->getConfig()->getSubjectMapper()->findFiltered(array(
                'userId' => $this->getUser()->getId(),
                'active' => true
            ), \Tk\Db\Tool::create('date_start DESC', 10));
            if ($list->count()) {
                foreach ($list as $i => $subject) {
                    $url = \Uni\Uri::createSubjectUrl('/index.html', $subject);
                    $r = $template->getRepeat('subject-item');
                    $r->insertText('text', $subject->name);
                    $r->setAttr('link', 'href', $url);
                    $r->appendRepeat();
                }
                $template->setChoice('subject-list');
            }
        }





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
      <a class="navbar-brand" href="/student/index.html" var="siteTitle">Tk2Uni v2.0</a>
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
          <li choice="subject"><a href="/student/index.html" var="subject-dashboard"><i class="fa fa-fw fa-dashboard"></i> Dashboard</a></li>
          
          <li choice="subject"><a href="#"><i class="fa fa-fw fa-file "></i> Subject Item</a></li>
          <li choice="subject"><a href="#"><i class="fa fa-fw fa-file "></i> Subject Item</a></li>
          <li choice="subject"><a href="#"><i class="fa fa-fw fa-file "></i> Subject Item</a></li>
          
          <!-- Subject list -->
          <li var="subject-item" repeat="subject-item"><a href="#" var="link"><i class="fa fa-file-text-o"></i> <span var="text"></span></a></li>
          <li class="text-center active" var="subject-list-all" choice="subject-list"><a href="/student/subjectManager.html" var="managerUrl">All Subjects</a></li>
        </ul>
      </div>
    </div>
    
</nav>
XHTML;

        return \Dom\Loader::load($xhtml);
    }

}