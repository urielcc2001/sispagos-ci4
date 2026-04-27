<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Estado de Cuenta — SistemaPagos</title>
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
          <?php if (service('session')->get('rol') === 'admin'): ?>
          <li class="nav-item">
            <a href="<?= base_url('dashboard') ?>" class="nav-link">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Dashboard</p>
            </a>
          </li>
          <?php endif; ?>
          <li class="nav-item">
            <a href="<?= base_url('pagos') ?>" class="nav-link">
              <i class="nav-icon fas fa-money-bill-wave"></i>
              <p>Registrar Pago</p>
            </a>
          </li>
          <?php if (service('session')->get('rol') === 'admin'): ?>
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
          <?php endif; ?>
          <li class="nav-item">
            <a href="<?= base_url('admin/estado-cuenta') ?>" class="nav-link active">
              <i class="nav-icon fas fa-search-dollar"></i>
              <p>Estado de Cuenta</p>
            </a>
          </li>
          <?php if (service('session')->get('rol') === 'admin'): ?>
          <li class="nav-item">
            <a href="<?= base_url('admin/morosos') ?>" class="nav-link">
              <i class="nav-icon fas fa-user-clock"></i>
              <p>Morosos</p>
            </a>
          </li>
          <?php endif; ?>
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
            <h1 class="m-0"><i class="fas fa-search-dollar mr-2 text-primary"></i>Estado de Cuenta</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
              <li class="breadcrumb-item active">Estado de Cuenta</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid">

        <!-- ── Formulario de búsqueda ──────────────────────────────── -->
        <div class="card card-outline card-primary">
          <div class="card-header">
            <h3 class="card-title"><i class="fas fa-search mr-2"></i>Consultar Mensualidades</h3>
          </div>
          <div class="card-body">
            <form method="GET" action="<?= base_url('admin/estado-cuenta') ?>">
              <div class="row align-items-end">
                <div class="col-md-3">
                  <div class="form-group mb-0">
                    <label>No. de Control <span class="text-danger">*</span></label>
                    <input type="text" name="num_control" class="form-control"
                           value="<?= esc($num_control) ?>"
                           placeholder="Ej. 20230001" required>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group mb-0">
                    <label>Nivel <span class="text-danger">*</span></label>
                    <select name="nivel" class="form-control" required>
                      <option value="">— Seleccionar —</option>
                      <option value="prepa"    <?= $nivel === 'prepa'    ? 'selected' : '' ?>>Bachillerato</option>
                      <option value="uni"      <?= $nivel === 'uni'      ? 'selected' : '' ?>>Universidad</option>
                      <option value="posgrado" <?= $nivel === 'posgrado' ? 'selected' : '' ?>>Posgrado</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group mb-0">
                    <label>Año</label>
                    <select name="anio" class="form-control">
                      <?php for ($y = (int) date('Y'); $y >= (int) date('Y') - 4; $y--): ?>
                        <option value="<?= $y ?>" <?= $anio == $y ? 'selected' : '' ?>><?= $y ?></option>
                      <?php endfor; ?>
                    </select>
                  </div>
                </div>
                <div class="col-md-auto">
                  <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search mr-1"></i> Consultar
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>

        <?php if ($num_control && $nivel): ?>

          <?php
            $nivelLabels = ['uni' => 'Universidad', 'prepa' => 'Bachillerato', 'posgrado' => 'Posgrado'];
            $pagados    = count(array_filter($estado, fn($e) => $e['status'] === 'pagado'));
            $pendientes = count(array_filter($estado, fn($e) => $e['status'] === 'pendiente'));
          ?>

          <!-- ── Tarjeta de resumen del alumno ──────────────────────── -->
          <div class="card card-outline <?= $pendientes > 0 ? 'card-danger' : 'card-success' ?>">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-user-graduate mr-2"></i>
                <?php if ($info_alumno): ?>
                  <?= esc($info_alumno['nombre_alumno']) ?>
                  <small class="text-muted ml-2"><?= esc($num_control) ?></small>
                <?php else: ?>
                  No. Control: <?= esc($num_control) ?>
                <?php endif; ?>
              </h3>
              <div class="card-tools">
                <span class="badge badge-info p-2 mr-1"><?= esc($nivelLabels[$nivel] ?? $nivel) ?></span>
                <span class="badge badge-success p-2 mr-1">
                  <i class="fas fa-check mr-1"></i><?= $pagados ?> pagados
                </span>
                <?php if ($pendientes > 0): ?>
                <span class="badge badge-danger p-2">
                  <i class="fas fa-times mr-1"></i><?= $pendientes ?> pendientes
                </span>
                <?php endif; ?>
              </div>
            </div>
            <div class="card-body">

              <!-- Selector de año -->
              <?php if (! empty($anios)): ?>
              <div class="mb-4">
                <label class="text-muted text-uppercase font-weight-bold" style="font-size:.75rem">Año consultado</label>
                <div class="d-flex flex-wrap gap-1">
                  <?php foreach ($anios as $y): ?>
                    <a href="?num_control=<?= urlencode($num_control) ?>&nivel=<?= urlencode($nivel) ?>&anio=<?= $y ?>"
                       class="btn btn-sm <?= $y == $anio ? 'btn-primary' : 'btn-outline-secondary' ?> mr-1 mb-1">
                      <?= $y ?>
                    </a>
                  <?php endforeach; ?>
                </div>
              </div>
              <?php endif; ?>

              <!-- Grid de 12 meses -->
              <div class="row">
                <?php foreach ($estado as $e): ?>
                  <?php
                    switch ($e['status']) {
                        case 'pagado':
                            $cardClass = 'bg-success text-white';
                            $icon      = '<i class="fas fa-check-circle fa-2x"></i>';
                            break;
                        case 'pendiente':
                            $cardClass = 'bg-danger text-white';
                            $icon      = '<i class="fas fa-times-circle fa-2x"></i>';
                            break;
                        case 'na':
                            $cardClass = 'bg-white text-muted border';
                            $icon      = '<i class="fas fa-minus fa-2x text-muted" style="opacity:.35"></i>';
                            break;
                        default: // futuro
                            $cardClass = 'bg-light text-muted';
                            $icon      = '<i class="fas fa-clock fa-2x text-muted"></i>';
                    }
                  ?>
                  <div class="col-md-2 col-sm-3 col-4 mb-3">
                    <div class="card mb-0 shadow-sm <?= $cardClass ?>" style="border-radius:8px">
                      <div class="card-body text-center py-3 px-2">
                        <p class="mb-2 font-weight-bold" style="font-size:.85rem; letter-spacing:.03rem">
                          <?= $e['nombre'] ?>
                        </p>
                        <?= $icon ?>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>

              <!-- Leyenda -->
              <div class="mt-3 pt-3 border-top">
                <span class="badge badge-success p-2 mr-2">
                  <i class="fas fa-check-circle mr-1"></i>Pagado
                </span>
                <span class="badge badge-danger p-2 mr-2">
                  <i class="fas fa-times-circle mr-1"></i>Pendiente
                </span>
                <span class="badge badge-secondary p-2 mr-2">
                  <i class="fas fa-clock mr-1"></i>Próximo
                </span>
                <span class="badge badge-light border p-2 text-muted">
                  <i class="fas fa-minus mr-1"></i>Antes de inscripción
                </span>
              </div>

            </div><!-- /.card-body -->
          </div>

        <?php endif; ?>

      </div>
    </section>
  </div><!-- /.content-wrapper -->

  <footer class="main-footer text-sm">
    <strong>SistemaPagos</strong> &copy; <?= date('Y') ?>
  </footer>

</div><!-- /.wrapper -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>
