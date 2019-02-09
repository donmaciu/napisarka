<?php

require_once __DIR__.'/../classes/functions.php';

$prefix = '/api/';

route('GET', '^'.$prefix.'lista/rss$', function() {

    $items = getInfo();

    header("Content-Type: application/rss+xml; charset=utf-8");

    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<rss version="2.0">';
    echo '<channel>';
    echo '<title>Napisy</title>';

    $titles = [];

    $text = '';

    if(sizeof($items) == 0) {
        $text = getDefaultGreeting();

        array_push($titles, $text);

    } else {

        foreach($items as $item) {
            array_push($titles, $item->napis);
        }

    }

    

    // foreach($titles as $title) {
    //     echo '<item><title>'.$title.'</title></item>';
    // }

    echo '<item><title>'.implode('                    ', $titles).'</title></item>';
    

    echo '</channel>';
    echo '</rss>';
});

route('GET', '^'.$prefix.'lista/info$', function() {

    $inter = 0;

    if(isset($_GET['int']) && is_numeric($_GET['int'])) {
        $inter = intval($_GET['int']);
    }

    $items = getInfo();

    if($inter == 0) {
        headerJson();
        echo json_encode($items);
    } else {

        header('Content-Type: text/plain; charset=utf-8;');


        if(sizeof($items) == 0) {
            echo getDefaultGreeting();
        } else {

            $intStr = str_repeat(' ', $inter);

            $nap_list = [];

            foreach($items as $item) {
                array_push($nap_list, $item->napis);
            }

            echo implode($intStr, $nap_list);

        }
    }
    
});

route('GET', '^'.$prefix.'lista/(?<id>[0-9a-zA-Z\-\_]*)$', function($params) {
    headerJson();

    

    $res = getItem($params->id, 'napisy');

    if($res == NULL) {
        return throwError(404, '{"error": "not_found"}');
    }

    echo json_encode($res);
});

route("DELETE", '^'.$prefix.'lista/(?<id>[0-9a-zA-Z\-\_]*)$', function($params) {
    headerJson();

    //error_log($params->id);

    $res = deleteItem($params->id, 'napisy');

    if($res == NULL) {
        return throwError(404, '{"error": "not_found"}');
    }

    echo json_encode($res);
});


route('GET', '^'.$prefix.'lista$', function() {
    headerJson();

    $sinceDate = NULL;
    $toDate = NULL;

    if(isset($_GET['sinceDate']) && is_numeric($_GET['sinceDate'])) {
        $sinceDate = $_GET['sinceDate'];
    }

    if(isset($_GET['toDate']) && is_numeric($_GET['sinceDate'])) {
        $toDate = $_GET['toDate'];
    }


    echo json_encode(getItems('napisy', $sinceDate, $toDate));
});

function postItemHandler($params) {
    headerJson();
    $entityBody = file_get_contents('php://input');

    $item = json_decode($entityBody);

    if($item == NULL) {
        return throwError(400, '{"error": "no_body"}');
    }

    if($item->sinceDate == NULL) {
        $item->sinceDate = 0;
    }

    if($item->toDate == NULL) {
        $item->toDate = 0;
    }

    if($item->id == null) {

        if($params->id != NULL) {
            $item->id = $params->id;
        } else {
            $item->id = newUnique();
        }
    }

    echo json_encode(putItem($item->id, $item, 'napisy'));
}

route('POST', '^'.$prefix.'lista$', 'postItemHandler');

route('POST', '^'.$prefix.'lista/(?<id>[0-9a-zA-Z\-\_]*)$', 'postItemHandler');

header("HTTP/1.0 404 Not Found");
echo '404 Not Found';

?>