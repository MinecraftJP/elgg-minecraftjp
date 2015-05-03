<div class="elgg-module elgg-module-info">
    <div class="elgg-head">
        <h3>MinecraftJP</h3>
    </div>
    <div class="elgg-body">
        <?php
        $userId = _elgg_services()->session->getLoggedInUserGuid();
        $username = elgg_get_plugin_user_setting('username', $userId, 'minecraftjp');
        if (!empty($username)) { // Linked
            $url = elgg_get_site_url() . 'minecraftjp/unlink?token=' . ElggMinecraftJP\Security::generateToken();
            $title = elgg_echo('minecraftjp:unlink_account');
            printf('<p><label>%s: <code>%s</code></label>', elgg_echo('minecraftjp:minecraft_username'), $username);
        } else { // not linked
            $url = elgg_get_site_url() . 'minecraftjp/login?type=link';
            $title = elgg_echo('minecraftjp:link_account');
        }

        printf('<div><a href="%s" class="elgg-button elgg-button-action">%s</a></div>', $url, $title);
        ?>
    </div>
</div>