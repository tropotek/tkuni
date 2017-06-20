<?php
namespace App;


/**
 * Class AppEvents
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class AppEvents
{

    /**
     * Called after the controller Controller/Iface::show() method has been called
     * Use this to modify the controller content.
     *
     * You will need to check what the controller class is to know where you are.
     *
     * <code>
     *     if ($event->get('controller') instanceof \App\Controller\Index) { ... }
     * </code>
     *
     * @event \Tk\Event\Event
     */
    const SHOW = 'controller.show';

    /**
     * Called at the end the Page/Iface::doPageInit() method
     * Use this modify the main page template before the controller is rendered to it
     *
     * @event \Tk\Event\Event
     */
    const PAGE_INIT = 'page.init';


}