<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Cambiar Contraseña — SistemaPagos</title>
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
          <?php if (service('session')->get('rol') === 'admin'): ?>
            <i class="fas fa-user-shield mr-1"></i>
            <?= esc(service('session')->get('nombre') ?? '') ?>
            <span class="badge badge-warning ml-1">Admin</span>
          <?php else: ?>
            <i class="fas fa-user-circle mr-1"></i>
            <?= esc(service('session')->get('nombre') ?? '') ?>
          <?php endif; ?>
        </a>
        <div class="dropdown-menu dropdown-menu-right shadow">
          <?php if (service('session')->get('rol') === 'admin'): ?>
          <a class="dropdown-item" href="<?= base_url('configuracion/usuarios') ?>">
            <i class="fas fa-users-cog mr-2 text-primary"></i> Gestión de Usuarios
          </a>
          <?php endif; ?>
          <a class="dropdown-item active" href="<?= base_url('configuracion/password') ?>">
            <i class="fas fa-key mr-2"></i> Cambiar Contraseña
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
    <a href="<?= base_url(service('session')->get('rol') === 'admin' ? 'dashboard' : 'pagos') ?>" class="brand-link px-3">
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
          <li class="nav-item">
            <a href="<?= base_url('pagos-externos') ?>" class="nav-link">
              <i class="nav-icon fas fa-user-tag"></i>
              <p>Pagos Externos</p>
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
          <?php endif; ?>
        </ul>
      </nav>
    </div>
  </aside>

  <!-- Content Wrapper -->
  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <h1 class="m-0">Cambiar Contraseña</h1>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid">
        <div class="row justify-content-center">
          <div class="col-md-6">
            <div class="card card-primary card-outline">
              <div class="card-header">
                <h3 class="card-title"><i class="fas fa-lock mr-2"></i>Actualizar contraseña</h3>
              </div>

              <?php if ($error): ?>
              <div class="alert alert-danger alert-dismissible mx-3 mt-3 mb-0">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fas fa-exclamation-triangle mr-1"></i> <?= esc($error) ?>
              </div>
              <?php endif; ?>

              <?php if ($success): ?>
              <div class="alert alert-success alert-dismissible mx-3 mt-3 mb-0">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fas fa-check-circle mr-1"></i> <?= esc($success) ?>
              </div>
              <?php endif; ?>

              <form action="<?= base_url('configuracion/password') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="card-body">

                  <div class="form-group">
                    <label for="password_actual">Contraseña actual</label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                      </div>
                      <input type="password" id="password_actual" name="password_actual"
                             class="form-control" placeholder="Contraseña actual" required>
                      <div class="input-group-append">
                        <button type="button" class="btn btn-outline-secondary toggle-pwd" data-target="password_actual">
                          <i class="fas fa-eye"></i>
                        </button>
                      </div>
                    </div>
                  </div>

                  <div class="form-group">
                    <label for="password_nueva">Nueva contraseña</label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                      </div>
                      <input type="password" id="password_nueva" name="password_nueva"
                             class="form-control" placeholder="Mínimo 6 caracteres" required minlength="6">
                      <div class="input-group-append">
                        <button type="button" class="btn btn-outline-secondary toggle-pwd" data-target="password_nueva">
                          <i class="fas fa-eye"></i>
                        </button>
                      </div>
                    </div>
                  </div>

                  <div class="form-group">
                    <label for="password_confirmar">Confirmar nueva contraseña</label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                      </div>
                      <input type="password" id="password_confirmar" name="password_confirmar"
                             class="form-control" placeholder="Repite la nueva contraseña" required minlength="6">
                      <div class="input-group-append">
                        <button type="button" class="btn btn-outline-secondary toggle-pwd" data-target="password_confirmar">
                          <i class="fas fa-eye"></i>
                        </button>
                      </div>
                    </div>
                  </div>

                </div>
                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i> Actualizar contraseña
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <footer class="main-footer text-center text-muted">
    SistemaPagos &copy; <?= date('Y') ?>
  </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script>
  $(document).on('click', '.toggle-pwd', function () {
    var target = $('#' + $(this).data('target'));
    var icon   = $(this).find('i');
    if (target.attr('type') === 'password') {
      target.attr('type', 'text');
      icon.removeClass('fa-eye').addClass('fa-eye-slash');
    } else {
      target.attr('type', 'password');
      icon.removeClass('fa-eye-slash').addClass('fa-eye');
    }
  });
</script>
</body>
</html>
