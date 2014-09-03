<?php

class Scrn {
    public static $hostname = 'scrn.tk';
    public static $shotsUrl;

    public static function imageFileName($url, $optionsJson = '') {
        return preg_replace('/[^a-zA-Z0-9_]/', '_', $url) . '_' . md5($url . $optionsJson) . '.png';
    }

    public static function addJob($url, $options) {
        $db = App::db();

        $db->query("INSERT INTO shots (url, options) VALUES(?, ?)", $url, $options);
    }

}
Scrn::$shotsUrl = 'http://shot.' . Scrn::$hostname;