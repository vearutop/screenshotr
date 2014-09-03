<?php

class Controller_Main {

    public static function indexPage() {
        Layout::create()->setBody(new View_JobForm())->render();
    }


    public static function process() {
        $p = new Scrn_Processor();
        $p->process();
    }

    public static function queueJob() {
        try {
            if (empty($_GET['url'])) {
                throw new Exception('Адрес страницы не указан');
            }

            $url = $_GET['url'];


            if (!isset($_GET['options'])) {
                $_GET['options'] = '';
                $options = '';
            }
            elseif (!json_decode($_GET['options'], 1)) {
                var_dump($_GET['options']);
                throw new Exception('Ваши опции ожидаются в верной JSON нотации :(');
            }
            else {
                $options = $_GET['options'];
            }

            Scrn::addJob($url, $options);
            $imageFileName = Scrn::imageFileName($url, $options);
            $shotsUrl = 'http://shot.' . Scrn::$hostname;

            $result =
                <<<HTML
                Ваша заявка принята. Ожидайте в комнате отдыха.<br>
                Страница: $url<br>
                Изображение: <a href="$shotsUrl/$imageFileName">будет тут</a><br>
HTML;

            exec('/usr/bin/php ~/scrn/cli.php /process > /dev/null 2> /dev/null &');
        }
        catch (Exception $e) {
            $result = $e->getMessage();
        }

        Layout::create()
            ->setBody(
                View_Raw::create($result)
            )
            ->render();




        /*

        CREATE TABLE  `screenshot`.`shots` (
        `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
        `url` VARCHAR( 255 ) NOT NULL ,
        `requested` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
        `processed` TIMESTAMP NOT NULL ,
        `build_time` FLOAT NOT NULL ,
        `built` TINYINT NOT NULL,
        `options` TEXT DEFAULT NULL,
        ) ENGINE = MYISAM ;

        */
    }


    public static function notFoundPage() {
        header("HTTP/1.x 404 Not Found");
        header("Status: 404 Not Found");

        App::view()->setBody(View_Raw::create('Not found :('));
        App::view()->render();
    }

    public static function dev() {
        Http_Auth::getInstance('dev')->demand(isset($_GET['logout']), '/');
        App::redirect('/');
    }

}