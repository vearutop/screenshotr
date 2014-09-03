<?php

class Scrn_Processor {
    const LOCK_FILE = 'screen-shot.lock';
    const JS_FILE = 'screen-shot.js';
    const IMAGES_PATH = '/home/scrn/shots/';
    const LOG_FILE = 'logs/processed.log';
    const IMAGES_URI_PATH = 'http://shot.scrn.tk/';


    const RETRIES = 5;
    const FLAG_IMAGE_BUILT = 1;
    const FLAG_RESIZE_PERFORMED = 2;
    const FLAG_CALLBACK_PERFORMED = 4;
    const FLAG_READY = 8;

    public function __construct() {
        set_time_limit(0);
        chdir($_SERVER['HOME']);
        if (file_exists(self::LOCK_FILE) && file_exists("/proc/" . file_get_contents(self::LOCK_FILE))) {
            throw new Exception('Locked' . "\n");
        }

        $pid = posix_getpid();
        file_put_contents(self::LOCK_FILE, $pid);
    }

    public function __destruct() {
        unlink(self::LOCK_FILE);
    }


    private function getNonProcessed() {
        $db = App::db();
        $r = $db->query("SELECT * FROM shots WHERE built <= ?  AND tries < ? ORDER BY id ASC LIMIT 1",
            self::FLAG_READY, self::RETRIES)->fetchRow();
        $db->disconnect();
        return $r;
    }

    private function updateNonProcessed($r) {
        $db = App::db();
        $q = $db->query("UPDATE shots SET processed = NOW(), build_time = build_time + ?, built = ?, tries = ? WHERE id = ?",
            $r['build_time'], $r['built'], $r['tries'], $r['id']);
        Log::getInstance('update')->push($q->build());
        $q->execute();
        $db->disconnect();
    }


    public function imageFileName($url, $optionsJson = '') {
        return preg_replace('/[^a-zA-Z0-9_]/', '_', $url) . '_' . md5($url . $optionsJson) . '.png';
    }

    public function process() {
        while ($r = $this->getNonProcessed()) {
            $imageFileName = Scrn::imageFileName($r['url'], $r['options']);
            $out = self::IMAGES_PATH . $imageFileName;
            $imageUri = self::IMAGES_URI_PATH . $imageFileName;
            $r['url'] = addslashes($r['url']);
            $start = microtime(1);

            $log = Log::getInstance('processing');
            $log->push(date('Y-m-d H:i:s') . ' Processing ' . $r['url']);
            $log->push($imageUri);

            $options = new Scrn_Options();
            if ($r['options']) {
                foreach (json_decode($r['options'], 1) as $key => $value) {
                    $options->$key = $value;
                }
            }

            $built = $r['built'] < 0 ? 0 : $r['built'];

            while (1) {
                //print_r($r);
                if (!($built & self::FLAG_IMAGE_BUILT)) {
                    $url = $r['url'];

                    $c =
                        <<<JS
var page = require('webpage').create();
page.settings.userAgent = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.116 Safari/537.36';
page.viewportSize = {
    width: '$options->viewPortWidth',
    height: '$options->viewPortHeight'
};
page.open('$url', function () {
    window.setTimeout(function(){
        page.render('$out');
        phantom.exit();
    }, $options->delay);
});
JS;

                    $log->push("Building screenshto!");
                    file_put_contents(self::JS_FILE, $c);
                    exec('/usr/bin/phantomjs ' . self::JS_FILE);
                    unlink(self::JS_FILE);


                    if (filesize($out) < 10000) {
                        $log->push("Building failed!");
                        Log::getInstance('error')->push($r['url'] . ' image building failed');
                        break;
                    }

                    $built |= self::FLAG_IMAGE_BUILT;
                }


                if (($options->resizeWidth || $options->resizeHeight) && !($built & self::FLAG_RESIZE_PERFORMED)) {
                    echo "Resizing\n";
                    $resize = $options->resizeWidth . 'x' . $options->resizeHeight;
                    exec('/usr/bin/convert "' . $out . '" -filter Lanczos -thumbnail ' . $resize . ' "' . $out . '"', $tmp, $return_var);
                    if ($return_var) {
                        echo "Resizing failed\n";
                        file_put_contents(self::LOG_FILE, $r['url'] . ' resizing failed' . "\n", FILE_APPEND);
                        break;
                    }
                    $built |= self::FLAG_RESIZE_PERFORMED;
                }

                if ($options->callbackUri && !($built & self::FLAG_CALLBACK_PERFORMED)) {
                    $opts = array('http' =>
                        array(
                            'method'  => 'HEAD',
                        )
                    );
                    $log->push("Calling back");

                    $context = stream_context_create($opts);
                    $callbackUri = str_replace('__IMAGE_URI__', urlencode($imageUri), $options->callbackUri);
                    if (false === file_get_contents($callbackUri, false, $context)) {
                        $log->push("Callback failed");
                        Log::getInstance('error')->push($r['url'] . ' callback failed');
                        break;
                    }
                    else {
                        $built |= self::FLAG_CALLBACK_PERFORMED;
                    }
                }

                $built |= self::FLAG_READY;
                break;
            }


            $r['built'] = $built;
            $r['tries']++;
            $r['build_time'] = microtime(1) - $start;

            $this->updateNonProcessed($r);
            Log::getInstance('update')->push($r['url'] . ' ' . $r['built'] . ' ' . $r['build_time']);
        }
    }
}
