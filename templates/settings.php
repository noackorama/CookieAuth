<tr>
    <td>
        <label for="cookie_auth_token">
            <?= _('Immer angemeldet bleiben') ?><br>
            <dfn id="cookie_auth_token_description">
                <?= _('Mit dieser Einstellung können Sie einen dauerhaften Cookie in Ihrem Browser setzen, '
                     .'mit dem Sie automatisch angemeldet werden können.') ?>
            </dfn>
        </label>
    </td>
    <td>
        <input type="checkbox" value="1" name="cookie_auth_token" id="cookie_auth_token" 
               aria-describedby="cookie_auth_token"  <? if ($checked) echo 'checked'; ?>>
    </td>
</tr>
