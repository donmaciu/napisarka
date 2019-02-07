<?php

require_once __DIR__.'/../classes/functions.php';

route('GET', '^/api/lista$', function() {
    echo json_encode(getItems());
});

route('POST', '^/api/lista$', function() {
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
        $item->id = getUnique();
    }

    echo json_encode(putItem($item->id, $item, 'napisy'));
});

header("HTTP/1.0 404 Not Found");
echo '404 Not Found';

?>