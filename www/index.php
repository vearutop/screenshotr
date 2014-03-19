<?php



$db = new mysqli('localhost', 'scrn', 'scrn', 'scrn');

function imageFileName($url, $optionsJson = '') {
    return preg_replace('/[^a-zA-Z0-9_]/', '_', $url) . '_' . md5($url . $optionsJson) . '.png';
}

if (isset($_GET['url'])) {

    if (!isset($_GET['options'])) {
        $_GET['options'] = '';
    }
    elseif (!json_decode($_GET['options'], 1)) {
        var_dump($_GET['options']);
        die('���� ����� ��������� � ������ JSON ������� :(');
    }

    $db->query("INSERT INTO shots (url, options) VALUES('"
    . $db->escape_string($_GET['url']) . "', '"
    . $db->escape_string($_GET['options']) . "')");


    echo '���� ������ �������. �������� � ������� ������.<br>';
    echo '��������: ' . $_GET['url'] . '<br>';
    echo '�����������: <a href="/shots/' . imageFileName($_GET['url'], $_GET['options']) . '">����� ���</a><br>';
}
else {
    echo '������!';
}



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