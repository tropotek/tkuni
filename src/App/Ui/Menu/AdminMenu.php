<?php
namespace App\Ui\Menu;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2018 Michael Mifsud
 */
class AdminMenu extends Iface
{


    /**
     *
     * @return \Dom\Template
     */
    public function show()
    {
        $template = parent::show();




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
      <a class="navbar-brand" href="/admin/index.html" var="siteTitle">System Administration</a>
    </div>
    <!-- /.navbar-header -->

    <ul class="nav navbar-top-links navbar-right">
      <li class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
          <i class="fa fa-user fa-fw"></i> <span var="username">Admin</span> <i class="fa fa-caret-down"></i>
        </a>
        <ul class="dropdown-menu dropdown-user">
          <li><a href="/index.html"><i class="fa fa-home fa-fw"></i> Site Home</a></li>
          <li><a href="/admin/profile.html"><i class="fa fa-user fa-fw"></i> My Profile</a></li>
          <li><a href="/admin/settings.html"><i class="fa fa-gear fa-fw"></i> Settings</a></li>
          <li class="divider"></li>
          <li><a href="/logout.html"><i class="fa fa-sign-out fa-fw"></i> Logout</a></li>
        </ul>
      </li>
    </ul>

    <div class="navbar-default sidebar" role="navigation">
      <div class="sidebar-nav navbar-collapse">
        <ul class="nav" id="side-menu">
          <li><a href="/admin/index.html"><i class="fa fa-dashboard fa-fw"></i> Dashboard</a></li>
          
          <li><a href="#"><i class="fa fa-cogs fa-fw"></i> System<span class="fa arrow"></span></a>
            <ul class="nav nav-second-level" var="system-menu">
              <li><a href="/admin/settings.html"><i class="fa fa-cog fa-fw"></i> Settings</a></li>
              <li><a href="/admin/institutionManager.html"><i class="fa fa-university fa-fw"></i> Institutions</a></li>
            </ul>
          </li>
          
          <li choice="debug"><a href="#"><i class="fa fa-bug fa-fw"></i> Development<span class="fa arrow"></span></a>
            <ul class="nav nav-second-level">
              <li choice="debug"><a href="/admin/dev/events.html"><i class="fa fa-empire fa-fw"></i> Events</a></li>
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