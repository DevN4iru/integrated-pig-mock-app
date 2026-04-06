<?php

require_once __DIR__ . '/controllers/DashboardController.php';
require_once __DIR__ . '/controllers/PenController.php';
require_once __DIR__ . '/controllers/PigController.php';

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$dashboardController = new DashboardController();
$penController = new PenController();
$pigController = new PigController();

if ($path === '/' || $path === '/dashboard') {
    $dashboardController->index();
    exit;
}

if ($path === '/pens/create' && $method === 'GET') {
    $penController->create();
    exit;
}

if ($path === '/pens/store' && $method === 'POST') {
    $penController->store();
    exit;
}

if ($path === '/pens/delete' && $method === 'POST') {
    $penController->delete();
    exit;
}

if ($path === '/pigs/create' && $method === 'GET') {
    $pigController->create();
    exit;
}

if ($path === '/pigs/store' && $method === 'POST') {
    $pigController->store();
    exit;
}

if ($path === '/pigs/delete' && $method === 'POST') {
    $pigController->delete();
    exit;
}

if ($path === '/pigs/update' && $method === 'POST') {
    $pigController->update();
    exit;
}

if ($path === '/health/store' && $method === 'POST') {
    $pigController->storeHealth();
    exit;
}

if ($path === '/vaccinations/store' && $method === 'POST') {
    $pigController->storeVaccination();
    exit;
}

if ($path === '/mortality/store' && $method === 'POST') {
    $pigController->storeMortality();
    exit;
}

if ($path === '/medications/store' && $method === 'POST') {
    $pigController->storeMedication();
    exit;
}

if (preg_match('#^/pigs/edit/(\d+)$#', $path, $matches)) {
    $pigController->edit((int) $matches[1]);
    exit;
}

if (preg_match('#^/pigs/(\d+)/health/create$#', $path, $matches) && $method === 'GET') {
    $pigController->createHealth((int) $matches[1]);
    exit;
}

if (preg_match('#^/pigs/(\d+)/vaccinations/create$#', $path, $matches) && $method === 'GET') {
    $pigController->createVaccination((int) $matches[1]);
    exit;
}

if (preg_match('#^/pigs/(\d+)/mortality/create$#', $path, $matches) && $method === 'GET') {
    $pigController->createMortality((int) $matches[1]);
    exit;
}

if (preg_match('#^/pigs/(\d+)/medications/create$#', $path, $matches) && $method === 'GET') {
    $pigController->createMedication((int) $matches[1]);
    exit;
}

if (preg_match('#^/pens/(.+)$#', $path, $matches)) {
    $penController->show($matches[1]);
    exit;
}

if (preg_match('#^/pigs/(\d+)$#', $path, $matches)) {
    $pigController->show((int) $matches[1]);
    exit;
}

http_response_code(404);
echo '404 Not Found';
