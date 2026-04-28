<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Conceptos de Trámites — SistemaPagos</title>
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
            <a href="<?= base_url('admin/conceptos') ?>" class="nav-link active">
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
            <h1 class="m-0"><i class="fas fa-cogs mr-2 text-secondary"></i>Conceptos de Trámites</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
              <li class="breadcrumb-item active">Conceptos</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid">

        <?php if (session()->getFlashdata('success')): ?>
          <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle mr-1"></i>
            <?= esc(session()->getFlashdata('success')) ?>
          </div>
        <?php endif; ?>

        <div class="card card-outline card-primary">
          <div class="card-header">
            <h3 class="card-title"><i class="fas fa-list mr-2"></i>Trámites Configurados</h3>
            <div class="card-tools">
              <button type="button" class="btn btn-primary btn-sm" id="btn-agregar">
                <i class="fas fa-plus mr-1"></i> Agregar Trámite
              </button>
            </div>
          </div>
          <div class="card-body p-0">
            <table class="table table-striped table-hover table-sm mb-0">
              <thead class="thead-light">
                <tr>
                  <th>Nombre del Trámite</th>
                  <th class="text-right">Precio Sugerido</th>
                  <th class="text-center">Nivel</th>
                  <th class="text-center">Estatus</th>
                  <th class="text-center">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($conceptos)): ?>
                  <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                      <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                      No hay trámites configurados aún.
                    </td>
                  </tr>
                <?php else: ?>
                  <?php
                  $nivelLabels = [
                      'bachillerato' => ['label' => 'Bachillerato', 'badge' => 'warning'],
                      'universidad'  => ['label' => 'Universidad',  'badge' => 'primary'],
                      'ambos'        => ['label' => 'Ambos',        'badge' => 'secondary'],
                  ];
                  ?>
                  <?php foreach ($conceptos as $c): ?>
                  <tr>
                    <td class="font-weight-bold"><?= esc($c['nombre_tramite']) ?></td>
                    <td class="text-right">$<?= number_format((float) $c['precio_sugerido'], 2) ?></td>
                    <td class="text-center">
                      <?php $nv = $nivelLabels[$c['nivel']] ?? ['label' => $c['nivel'], 'badge' => 'secondary']; ?>
                      <span class="badge badge-<?= $nv['badge'] ?>"><?= $nv['label'] ?></span>
                    </td>
                    <td class="text-center">
                      <?php if ($c['estatus'] === 'activo'): ?>
                        <span class="badge badge-success">Activo</span>
                      <?php else: ?>
                        <span class="badge badge-danger">Inactivo</span>
                      <?php endif; ?>
                    </td>
                    <td class="text-center text-nowrap">
                      <button type="button"
                              class="btn btn-xs btn-outline-warning btn-editar"
                              data-id="<?= $c['id'] ?>"
                              data-nombre="<?= esc($c['nombre_tramite']) ?>"
                              data-precio="<?= $c['precio_sugerido'] ?>"
                              data-nivel="<?= $c['nivel'] ?>"
                              data-estatus="<?= $c['estatus'] ?>"
                              title="Editar">
                        <i class="fas fa-pencil-alt"></i>
                      </button>
                      <form method="post"
                            action="<?= base_url('admin/conceptos/' . $c['id'] . '/toggle') ?>"
                            style="display:inline">
                        <?= csrf_field() ?>
                        <button type="submit"
                                class="btn btn-xs <?= $c['estatus'] === 'activo' ? 'btn-outline-secondary' : 'btn-outline-success' ?>"
                                title="<?= $c['estatus'] === 'activo' ? 'Desactivar' : 'Activar' ?>">
                          <i class="fas <?= $c['estatus'] === 'activo' ? 'fa-toggle-on' : 'fa-toggle-off' ?>"></i>
                        </button>
                      </form>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </section>
  </div><!-- /.content-wrapper -->

  <footer class="main-footer text-sm">
    <strong>SistemaPagos</strong> &copy; <?= date('Y') ?>
  </footer>

</div><!-- /.wrapper -->

<!-- ── Modal Agregar / Editar Trámite ─────────────────────── -->
<div class="modal fade" id="modal-tramite" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 class="modal-title text-white" id="modal-tramite-titulo">
          <i class="fas fa-cogs mr-2"></i>Trámite
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="form-tramite" method="post" action="<?= base_url('admin/conceptos/guardar') ?>">
        <?= csrf_field() ?>
        <div class="modal-body">

          <div class="form-group">
            <label for="nombre_tramite">Nombre del Trámite <span class="text-danger">*</span></label>
            <input type="text" name="nombre_tramite" id="nombre_tramite"
                   class="form-control" placeholder="Ej. Carta de Buena Conducta" required>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="precio_sugerido">Precio Sugerido ($) <span class="text-danger">*</span></label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">$</span>
                  </div>
                  <input type="number" name="precio_sugerido" id="precio_sugerido"
                         class="form-control" step="0.01" min="0" required>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="nivel">Aplica para <span class="text-danger">*</span></label>
                <select name="nivel" id="nivel" class="form-control" required>
                  <option value="ambos">Ambos niveles</option>
                  <option value="bachillerato">Solo Bachillerato</option>
                  <option value="universidad">Solo Universidad</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Solo visible en modo edición -->
          <div class="form-group" id="grupo-estatus" style="display:none">
            <label for="estatus">Estatus</label>
            <select name="estatus" id="estatus" class="form-control">
              <option value="activo">Activo</option>
              <option value="inactivo">Inactivo</option>
            </select>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i> Cancelar
          </button>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save mr-1"></i> Guardar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script>
const BASE_URL = '<?= base_url() ?>';

$(function () {
  // ── Modo AGREGAR ────────────────────────────────────────────────
  $('#btn-agregar').on('click', function () {
    $('#modal-tramite-titulo').html('<i class="fas fa-plus mr-2"></i>Agregar Trámite');
    $('#form-tramite').attr('action', BASE_URL + 'admin/conceptos/guardar');
    $('#nombre_tramite').val('');
    $('#precio_sugerido').val('');
    $('#nivel').val('ambos');
    $('#grupo-estatus').hide();
    $('#modal-tramite').modal('show');
  });

  // ── Modo EDITAR ─────────────────────────────────────────────────
  $(document).on('click', '.btn-editar', function () {
    const $btn = $(this);
    $('#modal-tramite-titulo').html('<i class="fas fa-pencil-alt mr-2"></i>Editar Trámite');
    $('#form-tramite').attr('action', BASE_URL + 'admin/conceptos/' + $btn.data('id') + '/actualizar');
    $('#nombre_tramite').val($btn.data('nombre'));
    $('#precio_sugerido').val(parseFloat($btn.data('precio')).toFixed(2));
    $('#nivel').val($btn.data('nivel'));
    $('#estatus').val($btn.data('estatus'));
    $('#grupo-estatus').show();
    $('#modal-tramite').modal('show');
  });
});
</script>
</body>
</html>
