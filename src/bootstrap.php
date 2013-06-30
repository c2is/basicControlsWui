<?php

/*
 * This file is part of the c2is/bootstrap.
 *
 * (c) Morgan Brunot <m.brunot@c2is.fr>
 */

require_once __DIR__.'/../vendor/autoload.php';

use C2is\Core\Application;
use C2is\Core\Service as CoreService;
use C2is\Theme\Backend\Theme as BackendTheme;

use Silex\Provider\FormServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\TranslationServiceProvider;

use SilexAssetic\AsseticServiceProvider;

use Knp\Provider\ConsoleServiceProvider;

use Propel\Silex\PropelServiceProvider;



$app = new Application(__DIR__.'/..', 'cungfoo');

$app['debug'] = $app['config_core']['debug'];

$app->register(new FormServiceProvider());
$app->register(new Silex\Provider\ValidatorServiceProvider());
$app->register(new UrlGeneratorServiceProvider());
$app->register(new TranslationServiceProvider());
$app->register(new TwigServiceProvider(), array(
    'twig.path'    => glob(__DIR__.'/*/Resources/views/'),
    'twig.options' => array(
        'cache' => __DIR__ . '/../cache/twig'
    ),
    'twig.form.templates' => array('@backend/form/form_custom_layout.html.twig'),
));

$app->register(new AsseticServiceProvider(), array(
    'assetic.options'     => array(
        'debug'            => $app['debug'],
        'auto_dump_assets' => $app['debug'],
    ),
    'assetic.path_to_web' => __DIR__.'/../web',
));

$app->register(new ConsoleServiceProvider(), array(
    'console.name'              => 'cungfoo',
    'console.version'           => '0',
    'console.project_directory' => __DIR__.'/..',
));

// disabled propel if config file not exist
if (file_exists($propelConfigFile = __DIR__.'/Resources/propel/generated/cungfoo-conf.php')) {
    $app->register(new PropelServiceProvider(), array(
        'propel.config_file' => $propelConfigFile,
        'propel.model_path'  => __DIR__,
    ));
}

// enabled backend theme
$app->register(new BackendTheme());


// enabled core service
$app->register(new CoreService());
