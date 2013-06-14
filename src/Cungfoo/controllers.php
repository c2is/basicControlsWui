<?php
/**
 * Created by JetBrains PhpStorm.
 * User: mbarber
 * Date: 11/06/13
 * Time: 15:24
 * To change this template use File | Settings | File Templates.
 */

$app->get('/', function() use ($app) {
    return $app->render('index.html.twig');
});


//$app->mount('/edito', new \Cungfoo\Controller\EditoController());