<?php

/*
 * This file is part of the c2is/bootstrap.
 *
 * (c) Morgan Brunot <m.brunot@c2is.fr>
 */

require_once __DIR__.'/../src/bootstrap.php';

require_once __DIR__.'/../src/Cungfoo/controllers.php';

$app->match("/testff",function () {
    $walker = new \Walker\Walker("http://www.c2is.fr");
    echo count($walker->getStats());
    echo "<br>";
    echo count($walker->getLinks());
    foreach($walker->getStats() as $infos){
        echo "<br>".$infos[0]. " : ".$infos[1]. " : ".$infos[2];
    }

    foreach($walker->getLinks() as $infos){
        echo "<br>".$infos;
    }

    return "";
});

$app->run();


