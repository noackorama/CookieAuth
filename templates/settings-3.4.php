<label>
    <input type="checkbox" value="1" name="cookie_auth_token" 
           <? if ($checked) echo 'checked'; ?>>
    <?= _('Immer angemeldet bleiben') ?>
    <?= tooltipIcon(_('Mit dieser Einstellung können Sie einen dauerhaften Cookie '
                    . 'in Ihrem Browser setzen, mit dem Sie automatisch angemeldet '
                    . 'werden können.')) ?>
</label>
