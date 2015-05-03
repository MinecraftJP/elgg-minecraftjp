<?php
namespace ElggMinecraftJP;

use ElggMinecraftJP\Controller\PageController;

class App {
    const NAME = 'minecraftjp';

    public static function init() {
        elgg_extend_view('forms/login', 'plugins/minecraftjp/login');
        elgg_extend_view('forms/register', 'plugins/minecraftjp/login');
        elgg_extend_view('core/settings/account', 'plugins/minecraftjp/account');

        elgg_register_page_handler('minecraftjp', array('ElggMinecraftJP\App', 'dispatchPageHandler'));
    }

    public function dispatchPageHandler($params) {
        $controller = new PageController();
        if (!$controller->dispatch($params[0])) {
            header('Content-type: application/json');
            echo json_encode(array('error' => 'Unknown endpoint'));
        }
        exit;
    }
}