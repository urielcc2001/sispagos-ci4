<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Editar Pago Externo — SistemaPagos</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button">
          <i class="fas fa-bars"></i>
        </a>
      </li>
    </ul>
    <ul class="navbar-nav ml-auto">
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" aria-expanded="false">
          <i class="fas fa-user-shield mr-1"></i>
          <?= esc(service('session')->get('nombre') ?? '') ?>
          <span class="badge badge-warning ml-1">Admin</span>
        </a>
        <div class="dropdown-menu dropdown-menu-right shadow">
          <a class="dropdown-item" href="<?= base_url('configuracion/usuarios') ?>">
            <i class="fas fa-users-cog mr-2 text-primary"></i> Gestión de Usuarios
          </a>
          <a class="dropdown-item" href="<?= base_url('configuracion/password') ?>">
            <i class="fas fa-key mr-2 text-secondary"></i> Cambiar Contraseña
          </a>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item text-danger" href="<?= base_url('auth/login') ?>">
            <i class="fas fa-sign-out-alt mr-2"></i> Salir
          </a>
        </div>
      </li>
    </ul>
  </nav>

  <!-- Sidebar -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="<?= base_url('dashboard') ?>" class="brand-link px-3">
      <span class="brand-text font-weight-light"><b>Sistema</b>Pagos</span>
    </a>
    <div class="sidebar">
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
          <li class="nav-item">
            <a href="<?= base_url('dashboard') ?>" class="nav-link">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Dashboard</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?= base_url('pagos') ?>" class="nav-link">
              <i class="nav-icon fas fa-money-bill-wave"></i>
              <p>Registrar Pago</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?= base_url('pagos-externos') ?>" class="nav-link">
              <i class="nav-icon fas fa-user-tag"></i>
              <p>Pagos Externos</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?= base_url('admin/reportes') ?>" class="nav-link">
              <i class="nav-icon fas fa-chart-bar"></i>
              <p>Reportes</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?= base_url('admin/conceptos') ?>" class="nav-link">
              <i class="nav-icon fas fa-cogs"></i>
              <p>Conceptos</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?= base_url('admin/estado-cuenta') ?>" class="nav-link">
              <i class="nav-icon fas fa-search-dollar"></i>
              <p>Estado de Cuenta</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?= base_url('admin/morosos') ?>" class="nav-link">
              <i class="nav-icon fas fa-user-clock"></i>
              <p>Morosos</p>
            </a>
          </li>
        </ul>
      </nav>
    </div>
  </aside>

  <!-- Content Wrapper -->
  <div class="content-wrapper">

    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">
              <i class="fas fa-edit mr-2 text-warning"></i>Editar Pago Externo
            </h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
              <li class="breadcrumb-item active">Editar Externo</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-lg-8">

            <!-- Datos de solo lectura -->
            <div class="card card-outline card-secondary">
              <div class="card-header">
                <h3 class="card-title text-muted">
                  <i class="fas fa-lock mr-2"></i>Datos del Recibo (solo lectura)
                </h3>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-5">
                    <label class="text-xs text-uppercase text-muted font-weight-bold">Folio Digital</label>
                    <p class="font-weight-bold text-success"><?= esc($pago['folio_digital'] ?? '—') ?></p>
                  </div>
                  <div class="col-md-4">
                    <label class="text-xs text-uppercase text-muted font-weight-bold">Fecha de Registro</label>
                    <p><?= date('d/m/Y H:i', strtotime($pago['created_at'])) ?></p>
                  </div>
                  <div class="col-md-3">
                    <label class="text-xs text-uppercase text-muted font-weight-bold">Cajero</label>
                    <p><?= esc($pago['nombre_cajero'] ?? 'N/D') ?></p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Formulario de edición -->
            <div class="card card-outline card-warning">
              <div class="card-header">
                <h3 class="card-title">
                  <i class="fas fa-pencil-alt mr-2"></i>Campos Editables
                </h3>
              </div>
              <form action="<?= base_url('admin/pagos-externos/' . $pago['id'] . '/actualizar') ?>" method="post">
                <?= csrf_field() ?>
                <div class="card-body">

                  <!-- Nombre del cliente -->
                  <div class="form-group">
                    <label for="nombre_cliente">Cliente / Aspirante <span class="text-danger">*</span></label>
                    <input type="text" name="nombre_cliente" id="nombre_cliente"
                           class="form-control" required
                           value="<?= esc($pago['nombre_cliente']) ?>">
                  </div>

                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="nivel">Nivel <span class="text-danger">*</span></label>
                        <select name="nivel" id="nivel" class="form-control" required>
                          <option value="prepa"    <?= $pago['nivel'] === 'prepa'    ? 'selected' : '' ?>>Preparatoria</option>
                          <option value="uni"      <?= $pago['nivel'] === 'uni'      ? 'selected' : '' ?>>Universidad</option>
                          <option value="posgrado" <?= $pago['nivel'] === 'posgrado' ? 'selected' : '' ?>>Posgrado</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="modalidad">Modalidad</label>
                        <select name="modalidad" id="modalidad" class="form-control">
                          <option value="">— Ninguna —</option>
                          <?php foreach (['Escolarizado','Sabatino','En línea','Mixto','Ejecutivo'] as $mod): ?>
                            <option value="<?= $mod ?>" <?= ($pago['modalidad'] ?? '') === $mod ? 'selected' : '' ?>>
                              <?= $mod ?>
                            </option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-8">
                      <div class="form-group">
                        <label for="concepto">Concepto <span class="text-danger">*</span></label>
                        <input type="text" name="concepto" id="concepto"
                               class="form-control" required
                               value="<?= esc($pago['concepto']) ?>">
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="monto">Monto <span class="text-danger">*</span></label>
                        <div class="input-group">
                          <div class="input-group-prepend">
                            <span class="input-group-text">$</span>
                          </div>
                          <input type="number" name="monto" id="monto"
                                 class="form-control" step="0.01" min="0.01" required
                                 value="<?= esc($pago['monto']) ?>">
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-4 px-0">
                    <div class="form-group">
                      <label for="metodo_pago">Método de Pago</label>
                      <select name="metodo_pago" id="metodo_pago" class="form-control">
                        <option value="Efectivo"      <?= ($pago['metodo_pago'] ?? '') === 'Efectivo'      ? 'selected' : '' ?>>Efectivo</option>
                        <option value="Transferencia" <?= ($pago['metodo_pago'] ?? '') === 'Transferencia' ? 'selected' : '' ?>>Transferencia</option>
                      </select>
                    </div>
                  </div>

                  <div class="callout callout-warning mt-2 mb-0">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    El sello digital del recibo original <strong>no se regenera</strong> al editar.
                  </div>

                </div>
                <div class="card-footer">
                  <button type="submit" class="btn btn-warning">
                    <i class="fas fa-save mr-1"></i> Guardar Cambios
                  </button>
                  <a href="<?= base_url('dashboard') ?>" class="btn btn-default ml-2">
                    <i class="fas fa-times mr-1"></i> Cancelar
                  </a>
                </div>
              </form>
            </div>

          </div>
        </div>
      </div>
    </section>
  </div>

  <footer class="main-footer text-sm">
    <strong>SistemaPagos</strong> &copy; <?= date('Y') ?>
  </footer>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>
