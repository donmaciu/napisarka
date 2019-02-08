<?php

require_once __DIR__.'/../classes/functions.php';

$prefix = '/api/';

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

    error_log($params->id);

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
        $sinceDate = intval($_GET['sinceDate']);
    }

    if(isset($_GET['toDate']) && is_numeric($_GET['sinceDate'])) {
        $toDate = intval($_GET['toDate']);
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