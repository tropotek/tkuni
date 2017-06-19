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
     * @var string
     */
    const CONTROLLER_POST_RENDER = 'controller.render.post';

    /**
     * Called at the end the Page/Iface::pageInit() method
     * Use this modify the main page template
     *
     * @event \Tk\Event\Event
     * @var string
     */
    const PAGE_POST_RENDER = 'page.render.post';




}