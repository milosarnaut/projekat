<?php
    require_once 'vendor/autoload.php';
    require_once 'Configuration.php';

    $dbConfig = new App\Core\DatabaseConfiguration(
        Configuration::DATABASE_HOST,
        Configuration::DATABASE_USER,
        Configuration::DATABASE_PASS,
        Configuration::DATABASE_NAME
    );
    $dbCon = new App\Core\DatabaseConnection($dbConfig);

    $url = strval(filter_input(INPUT_GET, 'URL'));
    $httpMethod = filter_input(INPUT_SERVER, 'REQUEST_METHOD');

    $router = new \App\Core\Router();
    $routes = require_once 'Routes.php';
    foreach($routes as $route){
        $router->add($route);
    }

    $route = $router->find($httpMethod, $url);
    $arguments = $route->extractArguments($url);

    $fullControllerName = '\\App\\Controllers\\' . $route->getControllerName() . 'Controller';
    $controller = new $fullControllerName($dbCon);

    $fingerprintProviderFactoryClass  = Configuration::FINGERPRINT_PROVIDER_FACTORY;
	$fingerprintProviderFactoryMethod = Configuration::FINGERPRINT_PROVIDER_METHOD;
	$fingerprintProviderFactoryArgs   = Configuration::FINGERPRINT_PROVIDER_ARGS;
	$fingerprintProviderFactory = new $fingerprintProviderFactoryClass;
	$fingerprintProvider = $fingerprintProviderFactory->$fingerprintProviderFactoryMethod(...$fingerprintProviderFactoryArgs);

	$sessionStorageClassName = Configuration::SESSION_STORAGE;
    $sessionStorageConstructorArguments = Configuration::SESSION_STORAGE_DATA;
    #raspakivanje argumenata, navoditi ih u nizu koliko god da ih ima
	$sessionStorage = new $sessionStorageClassName(...$sessionStorageConstructorArguments);

	$session = new \App\Core\Session\Session($sessionStorage, Configuration::SESSION_LIFETIME);
    $session->setFingerprintProvider($fingerprintProvider);

	$controller->setSession($session);
    $controller->getSession()->reload();

    #izvrsava se pre zeljenog metoda, ako on odluci da redirektuje usera nece doci do sledece f-je ni do view generatora
	$controller->__pre();
    call_user_func_array([$controller, $route->getMethodName()], $arguments);
    $controller->getSession()->save();
    
    $data = $controller->getData();

    if ($controller instanceof \App\Core\ApiController) {
		ob_clean();
		header('Content-type: application/json; charset=utf-8');
		header('Access-Control-Allow-Origin: *');
		echo json_encode($data);
		exit;
	}

    $loader = new \Twig\Loader\FilesystemLoader("./views");
    $twig = new \Twig\Environment($loader, [
        "auto-reload" => true
    ]);

    $data['BASE'] = Configuration::BASE;

    echo $twig->render(
            $route->getControllerName() . '/' . $route->getMethodName() . '.html',
            $data
    );
