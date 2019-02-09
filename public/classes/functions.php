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

        //error_log($_SERVER['REQUEST_METHOD']);
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

        $params = json_decode(json_encode($params));

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


function getItems($name = 'napisy', $sinceDate = NULL, $toDate = NULL) {
    // pobierz dane według ram czasowych
    $metas = getItemsMeta($name);
    $dirpath = __DIR__.'/../storage/'.$name;

    // filter metas by time

    //$filtered = $metas;


    $filtered = [];

    foreach($metas as $key=>$value) {
        $add = true;
        
        if($sinceDate != NULL && $value->sinceDate < $sinceDate && $value->toDate < $sinceDate) {
            $add = false;
            //error_log('sd: ' . $sinceDate . ' ::: ' . $value->sinceDate . ' - ' . $value->toDate .  ' bad');
        }

        if($toDate != NULL && $value->sinceDate > $toDate && $value->toDate > $toDate) {
            $add = false;
            //error_log('td: ' . $toDate . ' ::: ' . $value->sinceDate . ' - ' . $value->toDate .  ' bad');
        }

        if($add) {
            array_push($filtered, $value);
        }
    }

    // get items

    $resArray = [];

    foreach($filtered as $meta) {
        $filepath = $dirpath.'/'.$meta->id.'.json';
        if(file_exists($filepath)) {
            array_push($resArray, json_decode(file_get_contents($filepath)));
        }
    }

    return $resArray;
}

function getItem($id, $name = 'napisy') {
    $dirpath = __DIR__.'/../storage/'.$name;

    $filepath = $dirpath.'/'.$id.'.json';

    if(!file_exists($filepath)) {
        return NULL;
    }

    return json_decode(file_get_contents($filepath));
}

function deleteItem($id, $name = 'napisy') {
    $dirpath = __DIR__.'/../storage/'.$name;

    $filepath = $dirpath.'/'.$id.'.json';

    if(!file_exists($filepath)) {
        return NULL;
    }

    unlink($filepath);

    return json_decode('{"result": "success"}');
}

function putItem($id, $item, $name = 'napisy') {
    // wstaw dane według Id
    $dirpath = __DIR__.'/../storage/'.$name;
    $filepath = $dirpath.'/'.$id.'.json';
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
    $res->updatedAt = microtime(true) * 1000;
    $res->id = $item->id;

    return $res;
}

function getInfo($name = 'napisy') {
    $now = microtime(true) * 1000;

    $metas = getItemsMeta();

    $dirpath = __DIR__.'/../storage/'.$name;

    $items = [];

    foreach($metas as $key => $value) {
        if($value->sinceDate > 0 && $value->toDate > 0) {
            if($now >= $value->sinceDate && $now <= $value->toDate) {
                $filepath = $dirpath.'/'.$value->id.'.json';

                if(file_exists($filepath)) {
                    array_push($items, json_decode(file_get_contents($filepath)));
                }
                
            }
        }
    }

    return $items;
}

function getDefaultGreeting() {
    return "SERDECZNIE WITAMY I ŻYCZYMY UDANYCH ZAKUPÓW!";
}

function headerJson() {
    header('Content-Type: application/json; charset=utf-8;');
}

function throwError($status = 500, $body = "Internal Server Error") {
    http_response_code($status);

    echo $body;
}