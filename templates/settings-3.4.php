<label>
    <input type="checkbox" value="1" name="cookie_auth_token" 
           <? if ($checked) echo 'checked'; ?>>
    <?= _('Immer angemeldet bleiben') ?>
    <?= tooltipIcon(_('Mit dieser Einstellung k�nnen Sie einen dauerhaften Cookie '
                    . 'in Ihrem Browser setzen, mit dem Sie automatisch angemeldet '
                    . 'werden k�nnen.')) ?>
</label>
