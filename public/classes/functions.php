<?php
function route($httpMethods, $route, $callback, $exit = true)
{
    static $path = null;
    if ($path === null) {
        $path = parse_url($_SERVER['REQUEST_URI'])['path'];
        $scriptName = dirname(dirname($_SERVER['SCRIPT_NAME']));
        $scriptName = str_replace('\\', '/', $scriptName);
        $len = strlen($scriptName);
        if ($len > 0 && $scriptName !== '/') {
            $path = substr($path, $len);
        }
    }
    if (!in_array($_SERVER['REQUEST_METHOD'], (array) $httpMethods)) {
        return;
    }
    $matches = null;
    $regex = '/' . str_replace('/', '\/', $route) . '/';
    if (!preg_match_all($regex, $path, $matches)) {
        return;
    }
    if (empty($matches)) {
        $callback();
    } else {
        $params = array();
        foreach ($matches as $k => $v) {
            if (!is_numeric($k) && !isset($v[1])) {
                $params[$k] = $v[0];
            }
        }
        $callback($params);
    }
    if ($exit) {
        exit;
    }
}

function newUnique($pref = "item_") {
    return str_replace(".", "-", uniqid( $pref, TRUE ));
}

function getItemsMeta($name = 'napisy') {
    $filepath = __DIR__."/../storage/".$name.'.json';

    if(!file_exists($filepath)) {
        file_put_contents($filepath, '[]');
    }

    $indexStr = file_get_contents($filepath);

    $indexes = json_decode($indexStr);

    return $indexes;
}

function getItems($name = 'napisy', $sinceDate = null, $toDate = null) {
    // pobierz dane według ram czasowych
}

function putItem($id, $item, $name = 'napisy') {
    // wstaw dane według Id
    $dirpath = __DIR__.'/../storage/'.$name;
    $filepath = $dirpath.'/'.$name.'.json';
    $meta_filepath = __DIR__."/../storage/".$name.'.json';

    if(!file_exists($dirpath)) {
        mkdir($dirpath);
    }

    file_put_contents($filepath, json_encode($item));

    $metas = getItemsMeta($name);

    $changed = false;

    foreach($metas as $key => $value) {
        if($value->id == $id) {
            changeItem($item, $value);

            $changed = true;
        }
    }

    if(!$changed) {
        array_push($metas, changeItem($item, NULL));
    }

    file_put_contents($meta_filepath, json_encode($metas));

    $changedItemStr = file_get_contents($filepath);

    return json_decode($changedItemStr);
}

function changeItem($item, $found = NULL) {
    $res = $found;
    if($found == NULL) {
        $res = json_decode('{"sinceDate": 0, "toDate": 0, "updatedAt": 0}');
    }

    $res->sinceDate = $item != NULL && $item->sinceDate != NULL ? $item->sinceDate : 0;
    $res->toDate = $item != NULL && $item->toDate != NULL ? $item->toDate : 0;
    $res->updatedAt = microtime(false);

    return $res;
}

function headerJson() {
    header('Content-Type: application/json; charset=utf-8;');
}

function throwError($status = 500, $body = "Internal Server Error") {
    http_response_code($status);

    echo $body;
}