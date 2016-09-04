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
     * You will need to check what the controller class is to know where you are
     *   EG:
     *     if ($event->get('controller') instanceof \App\Controller\Index) { ... }
     *
     * @event \Tk\EventDispatcher\Event
     * @var string
     */
    const CONTROLLER_RENDER_POST = 'controller.render.post';

    /**
     * Called at the end the Page/Iface::pageInit() method
     * Use this modify the main page template
     *
     * @event \Tk\EventDispatcher\Event
     * @var string
     */
    const PAGE_INIT_POST = 'page.init.post';




}