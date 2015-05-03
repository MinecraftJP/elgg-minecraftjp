<?php
namespace ElggMinecraftJP;

class Security {
    public static function generateToken($ts = null) {
        if (empty($ts)) {
            $ts = time();
        }
        return $ts . '.' . hash_hmac('sha256', join('.', array($ts, _elgg_services()->session->getId())), _elgg_services()->siteSecret->get(true));
    }

    public static function validateToken($token) {
        list($ts,) = explode('.', $token, 2);
        return $token == self::generateToken($ts) && $ts > time() - 7200;
    }
}