<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Alumnos con Adeudos — SistemaPagos</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/datatables.net-bs4@1.13.6/css/dataTables.bootstrap4.min.css">
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
            <a href="<?= base_url('admin/morosos') ?>" class="nav-link active">
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
            <h1 class="m-0"><i class="fas fa-user-clock mr-2 text-danger"></i>Alumnos con Adeudos</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
              <li class="breadcrumb-item active">Morosos</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid">

        <!-- Filtro de nivel -->
        <div class="card card-outline card-danger mb-3">
          <div class="card-body py-2">
            <form method="GET" action="<?= base_url('admin/morosos') ?>" class="form-inline">
              <label class="mr-2 font-weight-bold">Filtrar por nivel:</label>
              <div class="btn-group btn-group-sm mr-3">
                <a href="<?= base_url('admin/morosos') ?>"
                   class="btn <?= ! $nivel ? 'btn-danger' : 'btn-outline-danger' ?>">Todos</a>
                <a href="?nivel=prepa"
                   class="btn <?= $nivel === 'prepa' ? 'btn-danger' : 'btn-outline-danger' ?>">Bachillerato</a>
                <a href="?nivel=uni"
                   class="btn <?= $nivel === 'uni' ? 'btn-danger' : 'btn-outline-danger' ?>">Universidad</a>
                <a href="?nivel=posgrado"
                   class="btn <?= $nivel === 'posgrado' ? 'btn-danger' : 'btn-outline-danger' ?>">Posgrado</a>
              </div>
              <span class="text-muted">
                Mostrando adeudos de mensualidades del año
                <strong><?= date('Y') ?></strong>
              </span>
            </form>
          </div>
        </div>

        <!-- Tabla de morosos -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-list mr-2"></i>
              <?= count($morosos) ?> alumno<?= count($morosos) !== 1 ? 's' : '' ?> con pagos pendientes
            </h3>
          </div>
          <div class="card-body p-0">
            <?php if (empty($morosos)): ?>
              <div class="text-center py-5 text-muted">
                <i class="fas fa-check-circle fa-3x text-success mb-3 d-block"></i>
                <h5>¡Sin adeudos detectados!</h5>
                <p>Todos los alumnos están al corriente en sus mensualidades de <?= date('Y') ?>.</p>
              </div>
            <?php else: ?>
              <?php
                $nivelLabels = ['uni' => 'Universidad', 'prepa' => 'Bachillerato', 'posgrado' => 'Posgrado'];
              ?>
              <table id="tablamorosos" class="table table-bordered table-striped table-hover mb-0">
                <thead class="thead-dark">
                  <tr>
                    <th>No. Control</th>
                    <th>Alumno</th>
                    <th>Nivel</th>
                    <th class="text-center">Meses Pendientes</th>
                    <th>Periodos con Adeudo</th>
                    <th class="text-center">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($morosos as $m): ?>
                  <tr>
                    <td><code><?= esc($m['num_control']) ?></code></td>
                    <td><?= esc($m['nombre_alumno']) ?></td>
                    <td>
                      <?php
                        $badge = match($m['nivel']) {
                            'uni'      => 'primary',
                            'prepa'    => 'warning',
                            'posgrado' => 'info',
                            default    => 'secondary',
                        };
                      ?>
                      <span class="badge badge-<?= $badge ?>">
                        <?= esc($nivelLabels[$m['nivel']] ?? $m['nivel']) ?>
                      </span>
                    </td>
                    <td class="text-center">
                      <span class="badge badge-danger badge-pill" style="font-size:.9rem; padding:.4em .7em">
                        <?= $m['total_adeudos'] ?>
                      </span>
                    </td>
                    <td>
                      <?php foreach ($m['adeudos'] as $adeudo): ?>
                        <span class="badge badge-light border border-danger text-danger mr-1 mb-1">
                          <i class="fas fa-times-circle mr-1"></i><?= esc($adeudo) ?>
                        </span>
                      <?php endforeach; ?>
                    </td>
                    <td class="text-center text-nowrap">
                      <a href="<?= base_url('admin/estado-cuenta') ?>?num_control=<?= urlencode($m['num_control']) ?>&nivel=<?= urlencode($m['nivel']) ?>"
                         class="btn btn-sm btn-outline-primary"
                         title="Ver estado de cuenta completo">
                        <i class="fas fa-search-dollar"></i>
                      </a>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php endif; ?>
          </div>
        </div>

      </div>
    </section>
  </div><!-- /.content-wrapper -->

  <footer class="main-footer text-sm">
    <strong>SistemaPagos</strong> &copy; <?= date('Y') ?>
  </footer>

</div><!-- /.wrapper -->

<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net@1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net-bs4@1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script>
$(function () {
  <?php if (! empty($morosos)): ?>
  $('#tablamorosos').DataTable({
    language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
    order: [[3, 'desc']],
    columnDefs: [{ orderable: false, targets: [4, 5] }]
  });
  <?php endif; ?>
});
</script>
</body>
</html>
