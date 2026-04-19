<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Auth::index');
$routes->get('auth/login', 'Auth::index');
$routes->post('auth/login', 'Auth::login');

$routes->get('pagos', 'PagosController::index');
$routes->get('pagos/buscar-alumno', 'PagosController::buscarAlumno');
$routes->post('pagos/registrar', 'PagosController::registrar');
