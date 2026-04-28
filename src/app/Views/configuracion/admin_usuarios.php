<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Gestión de Usuarios — SistemaPagos</title>
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
          <a class="dropdown-item active" href="<?= base_url('configuracion/usuarios') ?>">
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
        <div class="d-flex justify-content-between align-items-center">
          <h1 class="m-0">Gestión de Usuarios</h1>
          <button class="btn btn-primary" data-toggle="modal" data-target="#modalCrear">
            <i class="fas fa-user-plus mr-1"></i> Nuevo Usuario
          </button>
        </div>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid">

        <?php if (! empty($flash['success'])): ?>
        <div class="alert alert-success alert-dismissible">
          <button type="button" class="close" data-dismiss="alert">&times;</button>
          <i class="fas fa-check-circle mr-1"></i> <?= esc($flash['success']) ?>
        </div>
        <?php endif; ?>

        <?php if (! empty($flash['error'])): ?>
        <div class="alert alert-danger alert-dismissible">
          <button type="button" class="close" data-dismiss="alert">&times;</button>
          <i class="fas fa-exclamation-triangle mr-1"></i> <?= esc($flash['error']) ?>
        </div>
        <?php endif; ?>

        <div class="card">
          <div class="card-body p-0">
            <table id="tablaUsuarios" class="table table-bordered table-striped table-hover mb-0">
              <thead class="thead-dark">
                <tr>
                  <th>#</th>
                  <th>Nombre</th>
                  <th>Usuario</th>
                  <th>CONTRASEÑA/RFC</th>
                  <th>Correo</th>
                  <th>Rol</th>
                  <th>Estado</th>
                  <th class="text-center">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($usuarios as $i => $u): ?>
                <tr>
                  <td><?= $i + 1 ?></td>
                  <td><?= esc($u['nombre']) ?></td>
                  <td><code><?= esc($u['usuario']) ?></code></td>
                  <td><?= esc($u['rfc'] ?? '—') ?></td>
                  <td><?= esc($u['correo'] ?? '—') ?></td>
                  <td>
                    <?php if ($u['rol'] === 'admin'): ?>
                      <span class="badge badge-warning">Admin</span>
                    <?php else: ?>
                      <span class="badge badge-info">Cajero</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-center">
                    <?php if (($u['status'] ?? 1) == 1): ?>
                      <span class="badge badge-success">Activo</span>
                    <?php else: ?>
                      <span class="badge badge-secondary">Inactivo</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-center text-nowrap">
                    <!-- Editar -->
                    <button class="btn btn-sm btn-info btn-editar"
                            data-id="<?= $u['id'] ?>"
                            data-nombre="<?= esc($u['nombre']) ?>"
                            data-usuario="<?= esc($u['usuario']) ?>"
                            data-rfc="<?= esc($u['rfc'] ?? '') ?>"
                            data-correo="<?= esc($u['correo'] ?? '') ?>"
                            data-rol="<?= esc($u['rol']) ?>"
                            title="Editar usuario">
                      <i class="fas fa-pencil-alt"></i>
                    </button>
                    <!-- Restablecer contraseña -->
                    <button class="btn btn-sm btn-warning btn-reset"
                            data-id="<?= $u['id'] ?>"
                            data-nombre="<?= esc($u['nombre']) ?>"
                            data-rfc="<?= esc($u['rfc'] ?? '') ?>"
                            title="Restablecer contraseña a RFC">
                      <i class="fas fa-undo"></i>
                    </button>
                    <!-- Activar / Desactivar -->
                    <?php $esMismaCuenta = ($u['id'] == service('session')->get('id_usuario')); ?>
                    <button class="btn btn-sm <?= ($u['status'] ?? 1) == 1 ? 'btn-danger' : 'btn-success' ?> btn-toggle"
                            data-id="<?= $u['id'] ?>"
                            data-nombre="<?= esc($u['nombre']) ?>"
                            data-activo="<?= ($u['status'] ?? 1) ?>"
                            <?= $esMismaCuenta ? 'disabled title="No puedes deshabilitarte a ti mismo"' : '' ?>>
                      <i class="fas <?= ($u['status'] ?? 1) == 1 ? 'fa-user-slash' : 'fa-user-check' ?>"></i>
                      <?= ($u['status'] ?? 1) == 1 ? 'Deshabilitar' : 'Habilitar' ?>
                    </button>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </section>
  </div>

  <footer class="main-footer text-center text-muted">
    SistemaPagos &copy; <?= date('Y') ?>
  </footer>
</div>

<!-- Modal: Crear Usuario -->
<div class="modal fade" id="modalCrear" tabindex="-1">
  <div class="modal-dialog">
    <form action="<?= base_url('configuracion/usuarios/crear') ?>" method="POST">
      <?= csrf_field() ?>
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title"><i class="fas fa-user-plus mr-2"></i>Nuevo Usuario</h5>
          <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">

          <div class="form-group">
            <label>Nombre completo <span class="text-danger">*</span></label>
            <input type="text" name="nombre" class="form-control" placeholder="Ej. Juan Pérez" required>
          </div>

          <div class="form-group">
            <label>RFC <span class="text-danger">*</span></label>
            <input type="text" name="rfc" class="form-control" placeholder="Ej. PECJ900101ABC"
                   maxlength="13" style="text-transform:uppercase" required>
            <small class="text-muted">Se usará como contraseña inicial.</small>
          </div>

          <div class="form-group">
            <label>Nombre de usuario (login) <span class="text-danger">*</span></label>
            <input type="text" name="usuario" class="form-control" placeholder="Ej. jperez" required>
          </div>

          <div class="form-group">
            <label>Rol <span class="text-danger">*</span></label>
            <select name="rol" class="form-control" required>
              <option value="">— Seleccionar —</option>
              <option value="cajero">Cajero</option>
              <option value="admin">Administrador</option>
            </select>
          </div>

          <div class="form-group">
            <label>Correo electrónico</label>
            <input type="email" name="correo" class="form-control" placeholder="usuario@ejemplo.com">
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save mr-1"></i> Crear usuario
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal: Editar Usuario -->
<div class="modal fade" id="modalEditar" tabindex="-1">
  <div class="modal-dialog">
    <form id="formEditar" action="" method="POST">
      <?= csrf_field() ?>
      <div class="modal-content">
        <div class="modal-header bg-info text-white">
          <h5 class="modal-title"><i class="fas fa-pencil-alt mr-2"></i>Editar Usuario</h5>
          <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">

          <div class="form-group">
            <label>Nombre completo <span class="text-danger">*</span></label>
            <input type="text" name="nombre" id="edit_nombre" class="form-control" required>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Usuario (login) <span class="text-danger">*</span></label>
                <input type="text" name="usuario" id="edit_usuario" class="form-control" required>
                <small class="text-muted">Respeta mayúsculas/minúsculas.</small>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>RFC</label>
                <input type="text" name="rfc" id="edit_rfc" class="form-control"
                       maxlength="13" style="text-transform:uppercase">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Correo electrónico</label>
                <input type="email" name="correo" id="edit_correo" class="form-control">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Rol <span class="text-danger">*</span></label>
                <select name="rol" id="edit_rol" class="form-control" required>
                  <option value="cajero">Cajero</option>
                  <option value="admin">Administrador</option>
                </select>
              </div>
            </div>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-info">
            <i class="fas fa-save mr-1"></i> Guardar cambios
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Formulario oculto para restablecer contraseña (SweetAlert2 lo dispara) -->
<form id="formReset" action="" method="POST" style="display:none">
  <?= csrf_field() ?>
</form>

<!-- Modal: Confirmar Toggle Estado -->
<div class="modal fade" id="modalToggle" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <form id="formToggle" action="" method="POST">
      <?= csrf_field() ?>
      <div class="modal-content">
        <div class="modal-header" id="toggleHeader">
          <h5 class="modal-title" id="toggleTitulo"></h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body text-center">
          <p id="toggleMensaje"></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-sm" id="toggleConfirmar">Confirmar</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net@1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net-bs4@1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script>
$(function () {
  $('#tablaUsuarios').DataTable({
    language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
    order: [[0, 'asc']],
    columnDefs: [{ orderable: false, targets: [7] }]
  });

  // Abrir modal de edición (solo nombre, usuario, RFC)
  $(document).on('click', '.btn-editar', function () {
    var btn = $(this);
    $('#edit_nombre').val(btn.data('nombre'));
    $('#edit_usuario').val(btn.data('usuario'));
    $('#edit_rfc').val(btn.data('rfc'));
    $('#edit_correo').val(btn.data('correo'));
    $('#edit_rol').val(btn.data('rol'));
    $('#formEditar').attr('action', '<?= base_url('configuracion/usuarios/') ?>' + btn.data('id') + '/actualizar');
    $('#modalEditar').modal('show');
  });

  // RFC a mayúsculas (crear y editar)
  $(document).on('input', 'input[name="rfc"]', function () {
    this.value = this.value.toUpperCase();
  });

  // Restablecer contraseña con SweetAlert2
  $(document).on('click', '.btn-reset', function () {
    var id     = $(this).data('id');
    var nombre = $(this).data('nombre');
    var rfc    = $(this).data('rfc');

    if (! rfc) {
      Swal.fire({
        title: 'Sin RFC registrado',
        text: 'Este usuario no tiene RFC. No es posible restablecer la contraseña.',
        icon: 'warning',
        confirmButtonColor: '#f0ad4e'
      });
      return;
    }

    Swal.fire({
      title: '¿Restablecer contraseña?',
      html: 'La nueva clave de <strong>' + nombre + '</strong> será su RFC:<br><code class="text-dark">' + rfc + '</code>',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#f0ad4e',
      cancelButtonColor: '#6c757d',
      cancelButtonText: 'Cancelar',
      confirmButtonText: 'Sí, restablecer'
    }).then(function (result) {
      if (result.isConfirmed) {
        $('#formReset')
          .attr('action', '<?= base_url('configuracion/usuarios/') ?>' + id + '/reset')
          .submit();
      }
    });
  });

  // Toggle estado con modal Bootstrap
  $(document).on('click', '.btn-toggle', function () {
    var id     = $(this).data('id');
    var nombre = $(this).data('nombre');
    var activo = parseInt($(this).data('activo'));

    if (activo === 1) {
      $('#toggleHeader').removeClass('bg-success').addClass('bg-danger');
      $('#toggleTitulo').text('Deshabilitar Usuario');
      $('#toggleMensaje').html('¿Deshabilitar la cuenta de <strong>' + nombre + '</strong>? No podrá iniciar sesión.');
      $('#toggleConfirmar').removeClass('btn-success').addClass('btn-danger').text('Deshabilitar');
    } else {
      $('#toggleHeader').removeClass('bg-danger').addClass('bg-success');
      $('#toggleTitulo').text('Habilitar Usuario');
      $('#toggleMensaje').html('¿Habilitar la cuenta de <strong>' + nombre + '</strong>?');
      $('#toggleConfirmar').removeClass('btn-danger').addClass('btn-success').text('Habilitar');
    }

    $('#formToggle').attr('action', '<?= base_url('configuracion/usuarios/') ?>' + id + '/toggle');
    $('#modalToggle').modal('show');
  });
});
</script>
</body>
</html>
