<?php
namespace App\Auth;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
final class AuthEvents
{

    /**
     * Called when a user wants to login.
     * All Authentication should take place here.
     *
     * @event \App\Event\AuthEvent
     * @var string
     */
    const LOGIN = 'auth.onLogin';

    /**
     * Called when a user successfully logs in
     *
     * @event \App\Event\AuthEvent
     * @var string
     */
    const LOGIN_SUCCESS = 'auth.onLoginSuccess';

    /**
     * Called when a user logs out of the system
     *
     * @event \App\Event\AuthEvent
     * @var string
     */
    const LOGOUT = 'auth.onLogout';

    /**
     * Called when a user wants to recover their account password
     *
     * @event \Tk\EventDispatcher\Event
     * @var string
     */
    const RECOVER = 'auth.onRecover';

    /**
     * Called when a new user submits a registration request
     *
     * @event \Tk\EventDispatcher\Event
     * @var string
     */
    const REGISTER = 'auth.onRegister';

    /**
     * Called when a user triggers the registration confirmation request
     *
     * @event \Tk\EventDispatcher\Event
     * @var string
     */
    const REGISTER_CONFIRM = 'auth.onRegisterConfirm';

}