<?php

/**
 * Registra las 5 rutas REST estandar (index, detail, create, update, delete)
 * para un CrudController sobre $basePath.
 */
function register_crud($router, $basePath, $controllerClass)
{
    $controller = new $controllerClass();

    $router->add('GET', $basePath, function ($request) use ($controller) {
        return $controller->index($request);
    });

    $router->add('GET', $basePath . '/detail', function ($request) use ($controller) {
        return $controller->detail($request);
    });

    $router->add('POST', $basePath, function ($request) use ($controller) {
        return $controller->create($request);
    });

    $router->add('PUT', $basePath, function ($request) use ($controller) {
        return $controller->update($request);
    });

    $router->add('DELETE', $basePath, function ($request) use ($controller) {
        return $controller->delete($request);
    });
}

// ---------------------------------------------------------------------
// Auth
// ---------------------------------------------------------------------
$authController = new AuthController();

$router->add('POST', '/api/v1/auth/login', function ($request) use ($authController) {
    return $authController->login($request);
});

$router->add('POST', '/api/v1/auth/register', function ($request) use ($authController) {
    return $authController->register($request);
});

$router->add('POST', '/api/v1/auth/validate', function ($request) use ($authController) {
    return $authController->validate($request);
});

$router->add('POST', '/api/v1/auth/logout', function ($request) use ($authController) {
    return $authController->logout($request);
});

$router->add('POST', '/api/v1/auth/forgot-password', function ($request) use ($authController) {
    return $authController->forgotPassword($request);
});

$router->add('POST', '/api/v1/auth/validate-reset-token', function ($request) use ($authController) {
    return $authController->validateResetToken($request);
});

$router->add('POST', '/api/v1/auth/reset-password', function ($request) use ($authController) {
    return $authController->resetPassword($request);
});

// ---------------------------------------------------------------------
// Usuarios (gestion de cuentas; la creacion se hace via /auth/register)
// ---------------------------------------------------------------------
$usuarioController = new UsuarioController();

$router->add('GET', '/api/v1/usuarios/profile', function ($request) use ($usuarioController) {
    return $usuarioController->profile($request);
});

register_crud($router, '/api/v1/usuarios', 'UsuarioController');

// ---------------------------------------------------------------------
// Catalogo / tenant
// ---------------------------------------------------------------------
register_crud($router, '/api/v1/comunidades', 'ComunidadController');
register_crud($router, '/api/v1/roles', 'RolController');
register_crud($router, '/api/v1/barrios', 'BarrioController');
register_crud($router, '/api/v1/secciones', 'SeccionController');

// ---------------------------------------------------------------------
// Personas y hogares
// ---------------------------------------------------------------------
register_crud($router, '/api/v1/personas', 'PersonaController');
register_crud($router, '/api/v1/hogares', 'HogarController');
register_crud($router, '/api/v1/hogar-personas', 'HogarPersonaController');

// ---------------------------------------------------------------------
// Cargos, conceptos y obras
// ---------------------------------------------------------------------
register_crud($router, '/api/v1/conceptos-cargo', 'ConceptoCargoController');
register_crud($router, '/api/v1/obras', 'ObraController');
register_crud($router, '/api/v1/cargos', 'CargoController');
register_crud($router, '/api/v1/persona-obligaciones', 'PersonaObligacionController');

// ---------------------------------------------------------------------
// Pagos
// ---------------------------------------------------------------------
$pagoController = new PagoController();

$router->add('POST', '/api/v1/pagos/registrar', function ($request) use ($pagoController) {
    return $pagoController->registrar($request);
});

register_crud($router, '/api/v1/pagos', 'PagoController');
register_crud($router, '/api/v1/pago-detalle', 'PagoDetalleController');

// ---------------------------------------------------------------------
// Faenas y reuniones
// ---------------------------------------------------------------------
register_crud($router, '/api/v1/faenas', 'FaenaController');
register_crud($router, '/api/v1/faena-participantes', 'FaenaParticipanteController');
register_crud($router, '/api/v1/reuniones', 'ReunionController');
register_crud($router, '/api/v1/reunion-participantes', 'ReunionParticipanteController');

// ---------------------------------------------------------------------
// Auditoria (solo lectura)
// ---------------------------------------------------------------------
register_crud($router, '/api/v1/auditoria', 'AuditoriaController');

// ---------------------------------------------------------------------
// Expediente (busqueda + consulta consolidada de una persona, todos los roles)
// ---------------------------------------------------------------------
$expedienteController = new ExpedienteController();

$router->add('GET', '/api/v1/expediente/buscar', function ($request) use ($expedienteController) {
    return $expedienteController->buscar($request);
});

$router->add('GET', '/api/v1/expediente/detalle', function ($request) use ($expedienteController) {
    return $expedienteController->detalle($request);
});

$router->add('GET', '/api/v1/expediente/tarjeta', function ($request) use ($expedienteController) {
    return $expedienteController->tarjeta($request);
});
