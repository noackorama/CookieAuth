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
        } else {
            $this->cookie_login_user = null;
        }
        if (strpos($_SERVER['REQUEST_URI'], 'dispatch.php/settings/general') !== false) {
            if ($_POST['forced_language'] !== null) {
                $url_parts = parse_url($GLOBALS['ABSOLUTE_URI_STUDIP']);
                if (Request::get('cookie_auth_token')) {
                    $token = md5(uniqid($this->cookie_name,1));
                    UserConfig::get($GLOBALS['user']->id)->store('COOKIE_AUTH_TOKEN', $token);
                    setcookie($this->cookie_name, $token, strtotime('+1 year'), $url_parts['path'], $url_parts['host'], $_SERVER['HTTPS'] === 'On', true);
                } else {
                    UserConfig::get($GLOBALS['user']->id)->delete('COOKIE_AUTH_TOKEN');
                    setcookie($this->cookie_name, '', 0, $url_parts['path'], $url_parts['host'], $_SERVER['HTTPS'] === 'On', true);
                }
            }
            $snippet = '
            <tr>
                <td>
                    <label for="cookie_auth_token">
                        Immer angemeldet bleiben<br>
                        <dfn id="cookie_auth_token_description">
                            Mit dieser Einstellung können Sie einen dauerhaften cookie in Ihrem Browser setzen, mit dem Sie automatisch angemeldet werden können.  
                        </dfn>
                    </label>
                </td>
                <td>
                    <input type="checkbox" value="1" aria-describedby="cookie_auth_token" id="cookie_auth_token" name="cookie_auth_token" ' . (UserConfig::get($GLOBALS['user']->id)->COOKIE_AUTH_TOKEN ? 'checked' : '') .'>
                </td>
            </tr>';
            
             $snippet = jsready($snippet, 'script-double');
             PageLayout::addHeadElement('script', array('type' => 'text/javascript'),"jQuery(function (\$) {\$('#main_content tbody tr').first().after('$snippet');});");

        }
    }

    function show_action()
    {
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
            page_close();
            header("Location:" . URLHelper::getURL("index.php"));
            die();
        } else {
            page_close();
            header("Location:" . URLHelper::getURL("index.php"));
            die();
        }
    }

    public static function onEnable($plugin_id)
    {
        //allow for nobody
        $rp = new RolePersistence();
        $rp->assignPluginRoles($plugin_id, range(1,7));

    }
}

?>
