<?php

require_once __DIR__ . '/php-yaoi/modules/Yaoi.php';

class App extends Yaoi {

    public function route($path = null, $host = null) {
        if (null === $path) {
            $path = $this->path;
        }

        if (null === $host) {
            $host = $this->host;
        }

        switch (true) {
            /**
             * index
             */
            case '/' === $path:
                Controller_Main::indexPage();
                break;

            case !empty($_GET['url']):
                Controller_Main::queueJob();
                break;

            /**
             * some cli action (cron job)
             */
            case (self::MODE_CLI === $this->mode) && '/process' === $path:
                Controller_Main::process();
                break;


            /**
             * dev login
             */
            case $path === '/dev' || $path === '/dev?logout':
                Controller_Main::dev();
                break;


            /** @noinspection PhpMissingBreakStatementInspection */
            case String_Utils::starts($path, '/dev-con'):
                if (DevCon_Controller::create('/dev-con')
                    ->setAuth(Http_Auth::getInstance('dev'))
                    ->route($path)) {
                    break;
                }

            /**
             * 404
             */
            default:
                Controller_Main::notFoundPage();
                break;
        }
    }


    private static $resources = array();



    static function view() {
        $resource = &self::$resources['view'];
        if (!isset($resource)) {
            $resource = new Layout();
        }
        return $resource;
    }


}

App::init(function(){
    $conf = new Yaoi_Conf();
    $conf->errorLogPath = __DIR__ . '/logs/';
    return $conf;
});
require_once __DIR__ . '/conf/app.php';
