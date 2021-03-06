<?php
// web/index.php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Srcery\UrlService\InstanceControllerProvider;
use Srcery\UrlService\ImageControllerProvider;
use Srcery\UrlService\DerivativeControllerProvider;

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

// Debug helper provided by Silex
$app['debug'] = TRUE;

// Extension registers.
// SessionServiceProvider
$app->register(new Silex\Provider\SessionServiceProvider());

// Mongo
// @todo - move these off to env specific files
$app->register(new SilexExtension\MongoDbExtension(), array(
  'mongodb.class_path' => __DIR__ . '/../vendor/mongodb/lib',
  'mongodb.connection' => array(
    'server' => 'mongodb://srcery_db_user:srcery_db_pass9889@localhost/srcery_mongodb',
    'options' => array(),
    'eventmanager' => function($eventmanager) {},
  ),
));

$app->register(new Srcery\Server\SrceryServiceProvider(), array(
  'srcery.folder' => __DIR__ . '/../web/images',
  'srcery.place_holder' => 'placeholder.png',
  'srcery.mongodb_name' => 'srcery_mongodb',
  'srcery.resource_collection_name' => 'resources',
));

// Mount the controllers.
$instanceController = new Srcery\UrlService\InstanceControllerProvider();
$app->mount('/' . $instanceController->resource_path, new Srcery\UrlService\InstanceControllerProvider());

$imageController = new Srcery\UrlService\ImageControllerProvider();
$app->mount('/' . $imageController->resource_path, $imageController);

$derivativeController = new Srcery\UrlService\DerivativeControllerProvider();
$app->mount('/' . $derivativeController->resource_path, $derivativeController);

$app->get('/mongotest', function() use ($app) {
  $coll = $app['mongodb']->selectDatabase('srcery_mongodb')->selectCollection('test_collection');
  $result = $coll->find()->toArray();
  $response = new Response();
  $response->headers->set('Content-type', 'application/json');
  $response->setContent(json_encode($result));
  return $response;
});

return $app;