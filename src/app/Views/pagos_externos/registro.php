<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pagos Externos — SistemaPagos</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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
    <a href="<?= base_url('pagos') ?>" class="brand-link px-3">
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
            <a href="<?= base_url('pagos-externos') ?>" class="nav-link active">
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
          <?php endif; ?>
          <?php if (session()->get('rol') === 'admin' || session()->get('rol') === 'cajero'): ?>
          <li class="nav-item">
            <a href="<?= base_url('admin/estado-cuenta') ?>" class="nav-link">
              <i class="nav-icon fas fa-search-dollar"></i>
              <p>Estado de Cuenta</p>
            </a>
          </li>
          <?php endif; ?>
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
        <h1 class="m-0">Pagos Externos / Aspirantes</h1>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid">

        <div class="row justify-content-center">
          <div class="col-lg-8 col-xl-7">

            <div class="card card-primary card-outline">
              <div class="card-header">
                <h3 class="card-title">
                  <i class="fas fa-user-tag mr-2"></i>
                  Registrar Pago Externo
                </h3>
                <div class="card-tools">
                  <span class="badge badge-info">No requiere número de control</span>
                </div>
              </div>

              <form id="form-externo" autocomplete="off">
                <?= csrf_field() ?>

                <div class="card-body">

                  <!-- Nombre del cliente -->
                  <div class="form-group">
                    <label for="nombre_cliente">
                      Nombre del Aspirante / Cliente <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="nombre_cliente" id="nombre_cliente"
                           class="form-control" placeholder="Nombre completo" required>
                  </div>

                  <div class="row">
                    <!-- Nivel -->
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="nivel">Nivel <span class="text-danger">*</span></label>
                        <select name="nivel" id="nivel" class="form-control" required>
                          <option value="">— Selecciona —</option>
                          <option value="prepa">Preparatoria</option>
                          <option value="uni">Universidad</option>
                          <option value="posgrado">Posgrado</option>
                        </select>
                      </div>
                    </div>
                    <!-- Modalidad -->
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="modalidad">Modalidad</label>
                        <select name="modalidad" id="modalidad" class="form-control">
                          <option value="">— Opcional —</option>
                          <option value="Escolarizado">Escolarizado</option>
                          <option value="Sabatino">Sabatino</option>
                          <option value="En línea">En línea</option>
                          <option value="Mixto">Mixto</option>
                          <option value="Ejecutivo">Ejecutivo</option>
                        </select>
                      </div>
                    </div>
                  </div>

                  <!-- Concepto -->
                  <div class="form-group">
                    <label for="concepto">Concepto <span class="text-danger">*</span></label>
                    <select name="concepto" id="concepto" class="form-control" required>
                      <option value="">— Selecciona —</option>
                      <option value="Ficha de Admisión">Ficha de Admisión</option>
                      <option value="Examen de Admisión">Examen de Admisión</option>
                      <option value="Adeudo Histórico">Adeudo Histórico</option>
                      <option value="Constancia Externa">Constancia Externa</option>
                      <option value="Credencial / Gafete">Credencial / Gafete</option>
                      <option value="otro">Otro (especificar)…</option>
                    </select>
                  </div>

                  <!-- Concepto libre (visible solo si "Otro") -->
                  <div class="form-group" id="grupo-concepto-otro" style="display:none">
                    <label for="concepto_otro">Especifica el concepto <span class="text-danger">*</span></label>
                    <input type="text" name="concepto_otro" id="concepto_otro"
                           class="form-control" placeholder="Ej: Derecho de examen extraordinario">
                  </div>

                  <div class="row">
                    <!-- Monto -->
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="monto">Monto <span class="text-danger">*</span></label>
                        <div class="input-group">
                          <div class="input-group-prepend">
                            <span class="input-group-text">$</span>
                          </div>
                          <input type="number" name="monto" id="monto"
                                 class="form-control" step="0.01" min="1"
                                 placeholder="0.00" required>
                        </div>
                      </div>
                    </div>
                    <!-- Método de pago -->
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="metodo_pago">Método de Pago</label>
                        <select name="metodo_pago" id="metodo_pago" class="form-control">
                          <option value="Efectivo">Efectivo</option>
                          <option value="Transferencia">Transferencia</option>
                        </select>
                      </div>
                    </div>
                  </div>

                  <!-- Observaciones -->
                  <div class="form-group">
                    <label for="observaciones">Observaciones</label>
                    <textarea name="observaciones" id="observaciones"
                              class="form-control" rows="2"
                              placeholder="Notas adicionales (opcional)"></textarea>
                  </div>

                </div><!-- card-body -->

                <div class="card-footer">
                  <button type="submit" id="btn-registrar" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i> Registrar y Generar Recibo
                  </button>
                  <button type="reset" class="btn btn-default ml-2">
                    <i class="fas fa-undo mr-1"></i> Limpiar
                  </button>
                </div>

              </form>
            </div><!-- card -->

          </div>
        </div>

      </div>
    </section>
  </div><!-- content-wrapper -->

  <footer class="main-footer text-sm text-muted">
    <strong>SistemaPagos</strong> &copy; <?= date('Y') ?>
  </footer>
</div><!-- wrapper -->

<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script>
const BASE_URL = '<?= base_url() ?>';

// Mostrar/ocultar campo de concepto libre
$('#concepto').on('change', function () {
  const esOtro = $(this).val() === 'otro';
  $('#grupo-concepto-otro').toggle(esOtro);
  $('#concepto_otro').prop('required', esOtro);
  if (!esOtro) $('#concepto_otro').val('');
});

// Submit AJAX
$('#form-externo').on('submit', function (e) {
  e.preventDefault();

  const nombre  = $('#nombre_cliente').val().trim();
  const nivel   = $('#nivel').val();
  const concepto = $('#concepto').val();
  const conceptoOtro = $('#concepto_otro').val().trim();
  const monto   = $('#monto').val();

  if (!nombre || !nivel || !concepto || !monto) {
    Swal.fire('Campos incompletos', 'Completa todos los campos obligatorios.', 'warning');
    return;
  }
  if (concepto === 'otro' && !conceptoOtro) {
    Swal.fire('Especifica el concepto', 'Escribe el concepto del pago en el campo de texto.', 'warning');
    $('#concepto_otro').focus();
    return;
  }

  const $btn = $('#btn-registrar');
  $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Guardando…');

  const formData = $(this).serialize();

  $.ajax({
    url : BASE_URL + 'pagos-externos/registrar',
    type: 'POST',
    data: formData,
    success: function (res) {
      if (res.success) {
        // Actualizar CSRF
        $('input[name="' + res.csrf_name + '"]').val(res.csrf_hash);

        Swal.fire({
          icon : 'success',
          title: '¡Pago registrado!',
          html : 'Folio: <strong>' + res.folio_digital + '</strong>',
          confirmButtonText: '<i class="fas fa-file-pdf mr-1"></i> Ver Recibo PDF',
          showCancelButton : true,
          cancelButtonText : 'Registrar otro',
        }).then(function (result) {
          if (result.isConfirmed) {
            window.open(res.pdf_url, '_blank');
          }
          $('#form-externo')[0].reset();
          $('#grupo-concepto-otro').hide();
        });
      } else {
        Swal.fire('Error', res.message || 'No se pudo guardar el pago.', 'error');
      }
    },
    error: function (xhr) {
      const msg = xhr.responseJSON && xhr.responseJSON.message
                  ? xhr.responseJSON.message
                  : 'Error al comunicarse con el servidor.';
      Swal.fire('Error', msg, 'error');
    },
    complete: function () {
      $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Registrar y Generar Recibo');
    },
  });
});
</script>
</body>
</html>
