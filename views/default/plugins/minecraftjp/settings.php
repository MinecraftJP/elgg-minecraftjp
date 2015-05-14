<fieldset class="elgg-fieldset">
    <legend><?php echo elgg_echo('minecraftjp:application_settings'); ?></legend>

    <p><?php echo elgg_echo('minecraftjp:application_register_explain'); ?></p>

    <div>
        <label>
            <?php echo elgg_echo('minecraftjp:application_type'); ?>: <code><?php echo elgg_echo('minecraftjp:service_account'); ?></code>
        </label>
    </div>
    <div>
        <label>
            <?php echo elgg_echo('minecraftjp:client_id'); ?>: <?php echo elgg_view('input/text', array('name' => 'params[client_id]', 'value' => $vars['entity']->client_id)); ?>
        </label>
    </div>
    <div>
        <label>
            <?php echo elgg_echo('minecraftjp:client_secret'); ?>: <?php echo elgg_view('input/text', array('name' => 'params[client_secret]', 'value' => $vars['entity']->client_secret)); ?>
        </label>
    </div>
    <div>
        <label>
            <?php echo elgg_echo('minecraftjp:callback_uri'); ?>: <code><?php echo elgg_get_site_url(); ?>minecraftjp/doLogin</code>
        </label>
    </div>
</fieldset>

<fieldset class="elgg-fieldset">
    <legend><?php echo elgg_echo('minecraftjp:user_settings'); ?></legend>

    <div>
        <?php
        echo elgg_view("input/checkbox", array(
            'label' => elgg_echo('minecraftjp:force_users_can_register'),
            'name' => 'params[force_users_can_register]',
            'checked' => (bool)$vars['entity']->force_users_can_register,
        ));
        ?>
    </div>

    <div>
        <label>
            <?php echo elgg_echo('minecraftjp:username_suffix'); ?>: <?php echo elgg_view('input/text', array('name' => 'params[username_suffix]', 'value' => $vars['entity']->username_suffix)); ?>
        </label>
        <p class="elgg-text-help"><?php echo elgg_echo('minecraftjp:username_suffix_explain'); ?></p>
    </div>
</fieldset>

<fieldset class="elgg-fieldset">
    <legend><?php echo elgg_echo('minecraftjp:for_developer'); ?></legend>

<pre>$userId = _elgg_services()->session->getLoggedInUserGuid();
echo 'My Minecraft username is ' .  elgg_get_plugin_user_setting('username', $userId, 'minecraftjp');
echo 'My Minecraft UUID is ' . elgg_get_plugin_user_setting('uuid', $userId, 'minecraftjp');</pre>
</fieldset>