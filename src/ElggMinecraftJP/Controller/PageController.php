<?php
namespace ElggMinecraftJP\Controller;

class PageController extends Controller {
    private $avatarSizes = array(
        'topbar' => array(16, 16, true),
        'tiny' => array(25, 25, true),
        'small' => array(40, 40, true),
        'medium' => array(100, 100, true),
        'large' => array(200, 200, false),
        'master' => array(550, 550, false),
    );

    public function login() {
        $minecraftjp = $this->getMinecraftJP();

        $_SESSION['auth_type'] = !empty($_GET['type']) ? $_GET['type'] : 'login';
        if (!empty($_GET['redirect_to'])) {
            $_SESSION['redirect_to'] = $_GET['redirect_to'];
        }

        $minecraftjp->logout();
        $url = $minecraftjp->getLoginUrl(array(
            'scope' => 'openid profile email',
        ));

        forward($url);
    }

    public function doLogin() {
        $minecraftjp = $this->getMinecraftJP();

        $authType = !empty($_SESSION['auth_type']) ? $_SESSION['auth_type'] : 'login';
        $redirectTo = !empty($_SESSION['redirect_to']) ? $_SESSION['redirect_to'] : '';


        if ($authType == 'link') {
            try {
                $mcjpUser = $minecraftjp->getUser();
            } catch (\Exception $e) {
                system_messages($e->getMessage(), 'error');
                forward('/');
                exit;
            }

            if (!empty($mcjpUser)) {
                $user = _elgg_services()->session->getLoggedInUser();
                $existsUser = $this->getUserBySub($mcjpUser['sub']);
                if (!empty($existsUser) && $existsUser->guid != $user->guid) {
                    system_messages(elgg_echo('minecraftjp:already'), 'error');
                } else {
                    elgg_set_plugin_user_setting('sub', $mcjpUser['sub'], $user->guid, 'minecraftjp');
                    elgg_set_plugin_user_setting('uuid', $mcjpUser['uuid'], $user->guid, 'minecraftjp');
                    elgg_set_plugin_user_setting('username', $mcjpUser['preferred_username'], $user->guid, 'minecraftjp');

                    // update avatar
                    $this->updateAvatar($user, $mcjpUser);

                    system_messages(elgg_echo('minecraftjp:link_success'), 'success');
                }
                forward('/settings/user/' . $user->username);
            } else {
                system_messages(elgg_echo('minecraftjp:authorization_denied'), 'error');
            }
            forward('/');
        } else {
            try {
                $mcjpUser = $minecraftjp->getUser();
            } catch (\Exception $e) {
                system_messages($e->getMessage(), 'error');
                forward('/login');
                exit;
            }

            if (!empty($mcjpUser)) {
                $result = $this->getUserBySub($mcjpUser['sub']);
                if (!empty($result)) {
                    $user = $result[0];
                } else {
                    if (!elgg_get_config('allow_registration') && !elgg_get_plugin_setting('force_users_can_register', 'minecraftjp')) {
                        system_messages(elgg_echo('registerdisabled'), 'error');
                        forward('/login');
                        exit;
                    }
                    $password = generate_random_cleartext_password();
                    $username = $mcjpUser['preferred_username'];
                    $suffix = elgg_get_plugin_setting('username_suffix', 'minecraftjp');
                    if (!empty($suffix)) {
                        $username .= $suffix;
                    }
                    $result = null;
                    try {
                        $result = register_user($username,  $password, $mcjpUser['preferred_username'], $mcjpUser['email']);
                    } catch (\Exception $e) {
                        system_messages($e->getMessage(), 'error');
                        forward('/login');
                        exit;
                    }

                    if (!$result) {
                        system_message(elgg_echo('minecraftjp:username_or_email_already'), 'error');
                        forward('/login');
                        exit;
                    }
                    $userId = $result;
                    elgg_set_plugin_user_setting('sub', $mcjpUser['sub'], $userId, 'minecraftjp');
                    elgg_set_plugin_user_setting('uuid', $mcjpUser['uuid'], $userId, 'minecraftjp');

                    $user = get_entity($userId);

                    // send password notification
                    $site = elgg_get_site_entity();
                    $subject = _elgg_services()->translator->translate('minecraftjp:email:welcome:subject', array($site->getDisplayName()), $user->language);
                    $message = _elgg_services()->translator->translate('minecraftjp:email:welcome:body', array($user->username, $password, $site->getURL()), $user->language);
                    notify_user($user->guid, $site->guid, $subject, $message, array(), 'email');
                }

                // update username
                elgg_set_plugin_user_setting('username', $mcjpUser['preferred_username'], $userId, 'minecraftjp');

                // update avatar
                $this->updateAvatar($user, $mcjpUser);

                login($user);
                forward();
                exit;
            } else {
                system_messages(elgg_echo('minecraftjp:authorization_denied'), 'error');
                forward('/login');
                exit;
            }
        }
    }

    public function unlink() {
        // Validate token
        $token = _elgg_services()->request->query->get('token');
        if (!Security::validateToken($token)) {
            system_messages(elgg_echo('actionunauthorized'), 'error');
            forward('/');
            exit;
        }

        $user = _elgg_services()->session->getLoggedInUser();
        if (!empty($user)) {
            elgg_unset_plugin_user_setting('sub', $user->guid, 'minecraftjp');
            elgg_unset_plugin_user_setting('uuid', $user->guid, 'minecraftjp');
            elgg_unset_plugin_user_setting('username', $user->guid, 'minecraftjp');
            system_messages(elgg_echo('minecraftjp:unlink_success'), 'success');
            forward('/settings/user/' . $user->username);
        } else {
            forward('/');
        }
    }

    private function getMinecraftJP() {
        $clientId = elgg_get_plugin_setting('client_id', 'minecraftjp');
        $clientSecret = elgg_get_plugin_setting('client_secret', 'minecraftjp');

        if (empty($clientId) || empty($clientSecret)) {
            echo 'Not configured.';
            exit;
        }

        return new \MinecraftJP(array(
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
            'redirectUri' => elgg_get_site_url() . 'minecraftjp/doLogin',
        ));
    }

    private function getUserBySub($sub) {
        return _elgg_services()->plugins->getEntitiesFromUserSettings(array(
            'type' => 'user',
            'plugin_id' => 'minecraftjp',
            'plugin_user_setting_name_value_pairs' => array(
                'sub' => $sub,
            ),
            'plugin_user_setting_name_value_pairs_operator' => ' AND ',
        ));
    }

    private function updateAvatar($elggUser, $mcjpUser) {
        $avatarUrl = 'https://avatar.minecraft.jp/' . $mcjpUser['preferred_username'] . '/minecraft/550.png';
        $file = new \ElggFile();
        $file->owner_guid = $elggUser->guid;
        foreach ($this->avatarSizes as $size => $dimensions) {
            $image = get_resized_image_from_existing_file($avatarUrl, $dimensions[0], $dimensions[1], $dimensions[2]);

            $file->setFilename(sprintf('profile/%s%s.jpg', $elggUser->guid, $size));
            $file->open('write');
            $file->write($image);
            $file->close();
        }

        $elggUser->icontime = time();
    }
}