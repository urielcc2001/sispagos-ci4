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

// Adeudos y estado de cuenta
$routes->get('admin/estado-cuenta', 'AdminController::estadoCuenta');
$routes->get('admin/morosos', 'AdminController::morosos');
$routes->get('pagos/verificar-adeudos', 'PagosController::verificarAdeudos');
$routes->get('pagos/estado-mensualidades', 'PagosController::estadoMensualidades');

// Configuración de usuario
$routes->get('configuracion', 'ConfiguracionController::index');
$routes->get('configuracion/password', 'ConfiguracionController::cambiarPassword');
$routes->post('configuracion/password', 'ConfiguracionController::cambiarPassword');
$routes->get('configuracion/usuarios', 'ConfiguracionController::adminUsuarios');
$routes->post('configuracion/usuarios/crear', 'ConfiguracionController::crearUsuario');
$routes->post('configuracion/usuarios/(:num)/toggle', 'ConfiguracionController::toggleUsuario/$1');
$routes->post('configuracion/usuarios/(:num)/reset', 'ConfiguracionController::resetearPassword/$1');
$routes->get('configuracion/usuarios/(:num)/editar', 'ConfiguracionController::editarUsuario/$1');
$routes->post('configuracion/usuarios/(:num)/actualizar', 'ConfiguracionController::actualizarUsuario/$1');

// Pagos Externos / Aspirantes
$routes->get('pagos-externos', 'PagosExternosController::index');
$routes->post('pagos-externos/registrar', 'PagosExternosController::registrar');
$routes->get('pagos-externos/comprobante/(:segment)', 'PagosExternosController::comprobante/$1');
$routes->get('pagos-externos/validar/(:any)', 'PagosExternosController::validar/$1');
$routes->get('admin/pagos-externos/(:num)/editar', 'PagosExternosController::editar/$1');
$routes->post('admin/pagos-externos/(:num)/actualizar', 'PagosExternosController::actualizar/$1');
$routes->post('admin/pagos-externos/(:num)/eliminar', 'PagosExternosController::eliminar/$1');
