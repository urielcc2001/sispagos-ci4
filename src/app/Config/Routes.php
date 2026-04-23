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
$routes->get('pagos/ultimo-pago', 'PagosController::ultimoPago');
$routes->get('pagos/tramites-disponibles', 'PagosController::tramitesDisponibles');
$routes->post('pagos/registrar', 'PagosController::registrar');
$routes->get('pagos/comprobante/(:segment)', 'PagosController::comprobante/$1');

$routes->get('dashboard', 'AdminController::dashboard');
$routes->get('admin/reportes', 'AdminController::reportes');
$routes->get('admin/exportar/csv', 'AdminController::exportarCSV');
$routes->get('admin/exportar/pdf', 'AdminController::exportarPDF');
$routes->get('admin/pagos/(:num)/editar', 'AdminController::editarPago/$1');
$routes->post('admin/pagos/(:num)/actualizar', 'AdminController::actualizarPago/$1');
$routes->post('admin/pagos/(:num)/eliminar', 'AdminController::eliminarPago/$1');
$routes->get('validar-pago/(:any)', 'ValidacionController::validar/$1');

$routes->get('admin/conceptos', 'ConceptosController::index');
$routes->post('admin/conceptos/guardar', 'ConceptosController::guardar');
$routes->post('admin/conceptos/(:num)/actualizar', 'ConceptosController::actualizar/$1');
$routes->post('admin/conceptos/(:num)/toggle', 'ConceptosController::toggle/$1');
