<tr>
    <td>
        <label for="cookie_auth_token">
            <?= _('Immer angemeldet bleiben') ?><br>
            <dfn id="cookie_auth_token_description">
                <?= _('Mit dieser Einstellung k�nnen Sie einen dauerhaften Cookie in Ihrem Browser setzen, '
                     .'mit dem Sie automatisch angemeldet werden k�nnen.') ?>
            </dfn>
        </label>
    </td>
    <td>
        <input type="checkbox" value="1" name="cookie_auth_token" id="cookie_auth_token" 
               aria-describedby="cookie_auth_token"  <? if ($checked) echo 'checked'; ?>>
    </td>
</tr>
