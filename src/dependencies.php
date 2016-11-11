<?php
// DIC configuration

$container = $app->getContainer();

// view renderer
$container['renderer'] = function (\Interop\Container\ContainerInterface $c): \Slim\Views\PhpRenderer {
	$settings = $c->get('settings')['renderer'];
	return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function (\Interop\Container\ContainerInterface $c): \Monolog\Logger {
	$settings = $c->get('settings')['logger'];
	$logger = new \Monolog\Logger($settings['name']);
	$logger->pushProcessor(new \Monolog\Processor\UidProcessor());
	$logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
	return $logger;
};

// Service factory for the ORM
$container['db'] = function (\Interop\Container\ContainerInterface $container): \Illuminate\Database\Capsule\Manager {
	$capsule = new \Illuminate\Database\Capsule\Manager;
	$capsule->addConnection($container['settings']['db']);

	$capsule->setAsGlobal();
	$capsule->bootEloquent();

	return $capsule;
};

$container['errorHandler'] = function (\Interop\Container\ContainerInterface $c): Callable {
	return function ($request, $response, $exception) use ($c): Psr\Http\Message\ResponseInterface {
		$reflect = new ReflectionClass($exception);
		return $c['response']->withStatus(500)
							 ->withJson(['error' => $reflect->getShortName(), 'message' => $exception->getMessage()]);
	};
};

// How a new ChannelController should be made
$container[\FLAPI\ChannelController::class] = function (\Interop\Container\ContainerInterface $c): \FLAPI\ChannelController {
	$db = $c->get('db');
	return new \FLAPI\ChannelController($c, $db);
};

// How a new ShowController should be made
$container[\FLAPI\ShowController::class] = function (\Interop\Container\ContainerInterface $c): \FLAPI\ShowController {
	$db = $c->get('db');
	return new \FLAPI\ShowController($c, $db);
};
