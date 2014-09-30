<?php
/*
 * CookieAuth.php
 * Copyright (c) 2013  André Noack <noack@data-quest.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

class CookieAuth extends StudipPlugin implements SystemPlugin
{

    private $cookie_login_user;
    private $cookie_name;

    /**
     * Initialize a new instance of the plugin.
     */
    function __construct()
    {
        parent::__construct();
        $this->cookie_name = md5($GLOBALS['STUDIP_INSTALLATION_ID']) . get_class($this);
        if ($GLOBALS['user']->id && $GLOBALS['user']->id === 'nobody') {
            $cookie_token = $_COOKIE[$this->cookie_name];
            if ($cookie_token) {
                $user_config_entry = array_pop(UserConfigEntry::findBySQL("field = ? AND value = ?", array('COOKIE_AUTH_TOKEN', $cookie_token)));
                $this->cookie_login_user = User::find($user_config_entry->user_id);
            }
        }
        if ($this->cookie_login_user && !$this->cookie_login_user->locked) {
            $navigation = new Navigation(_('Automatischer Login'), URLHelper::getUrl('plugins.php/' . __CLASS__, array('cid' => null,'cancel_login' => 1)));
            $navigation->setDescription(sprintf(_('für Nutzer: %s'), $this->cookie_login_user->username));
            Navigation::insertItem('/login/remote_user',$navigation,'login');
            
            $this->inject_js('table.index_box.logintable td div a', 'login.php', array(
                'username' => $this->cookie_login_user->username,
                'url'      => URLHelper::getUrl('plugins.php/' . __CLASS__, array('cid' => null, 'cancel_login' => 1, 'return_to' => $_SERVER['REQUEST_URI'])),
            ));
        } else {
            $this->cookie_login_user = null;
        }

        if (strpos($_SERVER['REQUEST_URI'], 'dispatch.php/settings/general') !== false) {
            $url_parts = parse_url($GLOBALS['ABSOLUTE_URI_STUDIP']);
            if (UserConfig::get($GLOBALS['user']->id)->COOKIE_AUTH_TOKEN && !$this->cookie_login_user) {
                setcookie($this->cookie_name, UserConfig::get($GLOBALS['user']->id)->COOKIE_AUTH_TOKEN, strtotime('+1 year'), $url_parts['path'], $url_parts['host'], $_SERVER['HTTPS'] === 'On', true);
                $this->cookie_login_user = $GLOBALS['user'];
            }
            if ($_POST['forced_language'] !== null) {
                if (Request::get('cookie_auth_token')) {
                    $token = UserConfig::get($GLOBALS['user']->id)->COOKIE_AUTH_TOKEN ?: md5(uniqid($this->cookie_name,1));
                    UserConfig::get($GLOBALS['user']->id)->store('COOKIE_AUTH_TOKEN', $token);
                    setcookie($this->cookie_name, $token, strtotime('+1 year'), $url_parts['path'], $url_parts['host'], $_SERVER['HTTPS'] === 'On', true);
                } else {
                    UserConfig::get($GLOBALS['user']->id)->delete('COOKIE_AUTH_TOKEN');
                    setcookie($this->cookie_name, '', 0, $url_parts['path'], $url_parts['host'], $_SERVER['HTTPS'] === 'On', true);
                }
            }
            
            $this->inject_js('#main_content tbody tr', 'settings.php', array('checked' => $this->cookie_login_user));
        }
    }

    private function inject_js($selector, $template, $variables)
    {
        $factory = new Flexi_TemplateFactory(__DIR__ . '/templates');
        $snippet = $factory->render($template, $variables);
        $snippet = str_replace("\n", "\\\n", $snippet);
        
        $js = $factory->render('js.php', compact('selector', 'snippet'));

        PageLayout::addHeadElement('script', array('type' => 'text/javascript'), $js);
    }

    function show_action()
    {
        $redirect = Request::get('return_to', 'index.php');
        
        global $auth, $sess, $user;
        if ($this->cookie_login_user && $this->cookie_login_user->id !== $user->id) {
            $sess->regenerate_session_id(array('auth'));
            $auth->unauth();
            $auth->auth["jscript"] = true;
            $auth->auth["perm"]  = $this->cookie_login_user["perms"];
            $auth->auth["uname"] = $this->cookie_login_user["username"];
            $auth->auth["auth_plugin"]  = $this->cookie_login_user["auth_plugin"];
            $auth->auth_set_user_settings($this->cookie_login_user->id);
            $auth->auth["uid"] = $this->cookie_login_user->id;
            $auth->auth["exp"] = time() + (60 * $auth->lifetime);
            $auth->auth["refresh"] = time() + (60 * $auth->refresh);
        }
        page_close();
        header("Location:" . URLHelper::getURL($redirect));
        die();
    }

    public static function onEnable($plugin_id)
    {
        //allow for nobody
        $rp = new RolePersistence();
        $rp->assignPluginRoles($plugin_id, range(1,7));

    }
}
