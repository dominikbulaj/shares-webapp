<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Connections;
use Symfony\Component\Yaml\Yaml;

$app = new Silex\Application();

// dev - debug ON
if (strpos($_SERVER['HTTP_HOST'], '.vagrant') !== false) {
    $app['debug'] = true;
}

$app['config'] = $app->share(function (Silex\Application $app) {

    if ($app['debug']) {
        $configFile = 'config.dev.yml';
    } else {
        $configFile = 'config.yml';
    }

    return Yaml::parse($configFile);
});

$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => sprintf('%s/log/%s.log', __DIR__, date('Y-m-d')),
    'monolog.level'   => \Monolog\Logger::NOTICE,
));

$app->register(new Silex\Provider\DoctrineServiceProvider(), $app['config']['mysql']);

$app->register(new Silex\Provider\HttpCacheServiceProvider(), array(
    'http_cache.cache_dir' => __DIR__ . '/cache/',
    'http_cache.esi'       => null,
));


$app->before(function (Request $request) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());
    }
});

$app['dbDomains'] = $app->share(function (\Silex\Application $app) {
    return new \Webit\Domains($app['db']);
});
$app['dbUrls'] = $app->share(function (\Silex\Application $app) {
    return new \Webit\Urls($app['db']);
});

// log request & response
$app->finish(function (Request $request, \Symfony\Component\HttpFoundation\Response $response, \Silex\Application $app) {

    $logData = sprintf("%d %s %s", $response->getStatusCode(), $request->getMethod(), $request->getRequestUri());

    $app['monolog']->addNotice($logData, $request->request->all());
});

// ---------------------------------------------------------------------------------------------------------------------
// GET
// ---------------------------------------------------------------------------------------------------------------------
$app->get('/networks', function () use ($app) {
    $networks = (new \SharesCounter\Networks())->getAvailableNetworks(true);
    return $app->json(array('status' => 'OK', 'result' => $networks), 200, array('Cache-Control' => 's-maxage=3600, public'));
});

$app->get('/shares', function (Request $request) use ($app) {

    $url = $request->query->get('url');
    $networks = $request->query->get('networks');

    // validate url
    if (trim($url) == '') {
        return $app->json(array('status' => 'ERROR', 'result' => 'URL address is missing'), 500);
    }
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return $app->json(array('status' => 'ERROR', 'result' => 'Invalid URL address'), 500);
    }

    $userNetworks = [];
    if (!empty($networks)) {
        $userNetworks = array_map(function ($network) {
            return trim($network);
        }, explode(',', $networks));

        if (count($userNetworks) > 0) {
            $availableNetworks = (new \SharesCounter\Networks())->getAvailableNetworks();
            $diff = array_diff($userNetworks, $availableNetworks);
            if (count($diff) > 0) {
                return $app->json(array('status' => 'ERROR', 'result' => 'Unknown network(s): ' . join(', ', $diff) . '. Get available networks at: GET /api/networks'), 500);
            }
        }
    }


    $counts = $app['dbUrls']->getShares($url);
    if (!$counts) {

        $counts = (new \SharesCounter\SharesCounter($url))->getShares($userNetworks);

        // save in db
        $domainId = $app['dbDomains']->getDomainId($url);
        $app['dbUrls']->addUrl($url, $domainId, $counts);
    }

    // return always numeric values (needed for sorting)
    array_walk($counts, function (&$value) {
        $value = intval($value);
    });

    return $app->json(array('status' => 'OK', 'result' => $counts), 200, array('Cache-Control' => 's-maxage=300, public'));
});

$app->get('/most_searched', function () use ($app) {
    return $app->json(array('status' => 'OK', 'result' => $app['dbDomains']->getMostSearched(10)), 200, array('Cache-Control' => 's-maxage=30, public'));
});
$app->get('/last_searched', function () use ($app) {
    return $app->json(array('status' => 'OK', 'result' => $app['dbUrls']->getLastSearched(10)), 200, array('Cache-Control' => 's-maxage=10, public'));
});

// ---------------------------------------------------------------------------------------------------------------------
// POST
// ---------------------------------------------------------------------------------------------------------------------
$app->post('/count', function (Request $request) use ($app) {

    $url = $request->request->get('url');

    // validate url
    if (trim($url) == '') {
        return $app->json(array('status' => 'ERROR', 'result' => 'URL address is missing'), 500);
    }
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return $app->json(array('status' => 'ERROR', 'result' => 'Invalid URL address'), 500);
    }

    $app['dbUrls']->countUrl($url);

    return $app->json(array('status' => 'OK', 'result' => []), 200);
});

// ---------------------------------------------------------------------------------------------------------------------
// 404 redirects
// ---------------------------------------------------------------------------------------------------------------------
$app->error(function (\Exception $e, $code) use ($app) {
    if (404 == $code) {
        if (strpos($_SERVER['REQUEST_URI'], '/api') !== false) {
            return $app->redirect('/api-docs'); // redirect to api docs for not found api url
        } else {
            return $app->redirect('/'); // redirect to homepage for other not found urls
        }
    }
});
// ---------------------------------------------------------------------------------------------------------------------
if ($app['debug']) {
    $app->run();
} else {
    $app['http_cache']->run();
}