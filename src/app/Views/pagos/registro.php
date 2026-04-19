<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registro de Pago — SistemaPagos</title>
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
      <li class="nav-item">
        <span class="nav-link">
          <i class="fas fa-user-circle mr-1"></i>
          <?= esc(service('session')->get('nombre') ?? '') ?>
        </span>
      </li>
      <li class="nav-item">
        <a href="<?= base_url('auth/login') ?>" class="nav-link text-danger">
          <i class="fas fa-sign-out-alt"></i> Salir
        </a>
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
          <li class="nav-item">
            <a href="<?= base_url('pagos') ?>" class="nav-link active">
              <i class="nav-icon fas fa-money-bill-wave"></i>
              <p>Registrar Pago</p>
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
        <h1 class="m-0">Registro de Pago</h1>
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

        <?php if (isset($error)): ?>
          <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <?= esc($error) ?>
          </div>
        <?php endif; ?>

        <div class="card card-primary">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-user-graduate mr-2"></i>Datos del Alumno
            </h3>
          </div>

          <form action="<?= base_url('pagos/registrar') ?>" method="post" id="form-pago">
            <?= csrf_field() ?>

            <div class="card-body">

              <!-- Fila 1: Nivel + Modalidad Posgrado + Núm. Control + Nombre + Carrera + Modalidad Uni -->
              <div class="row align-items-end">

                <div class="col-md-2">
                  <div class="form-group">
                    <label for="nivel">Nivel <span class="text-danger">*</span></label>
                    <select name="nivel" id="nivel" class="form-control" required>
                      <option value="">— Selecciona —</option>
                      <option value="prepa">Bachillerato / Prepa</option>
                      <option value="uni">Universidad</option>
                      <option value="posgrado">Posgrado</option>
                    </select>
                  </div>
                </div>

                <!-- Solo visible cuando nivel = posgrado -->
                <div class="col-md-2" id="grupo-modalidad-sel" style="display:none">
                  <div class="form-group">
                    <label for="sel-modalidad">Modalidad</label>
                    <select id="sel-modalidad" class="form-control">
                      <option value="">— Selecciona —</option>
                      <option value="Maestría">Maestría</option>
                      <option value="Doctorado">Doctorado</option>
                    </select>
                  </div>
                </div>

                <div class="col-md-2">
                  <div class="form-group">
                    <label for="num_control">Núm. de Control <span class="text-danger">*</span></label>
                    <div class="input-group">
                      <input type="text" name="num_control" id="num_control"
                             class="form-control" placeholder="Ej. 20230001" required>
                      <div class="input-group-append">
                        <span class="input-group-text" id="spinner-buscar" style="display:none">
                          <i class="fas fa-spinner fa-spin"></i>
                        </span>
                      </div>
                    </div>
                    <small id="msg-error-alumno" class="text-danger" style="display:none">
                      <i class="fas fa-exclamation-circle"></i> Alumno no encontrado.
                    </small>
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="form-group">
                    <label for="nombre_alumno">Nombre Completo <span class="text-danger">*</span></label>
                    <input type="text" name="nombre_alumno" id="nombre_alumno"
                           class="form-control bg-light"
                           placeholder="Se completa automáticamente..."
                           readonly required>
                  </div>
                </div>

                <!-- Solo visible cuando nivel = uni -->
                <div class="col-md-4" id="grupo-carrera" style="display:none">
                  <div class="form-group">
                    <label>Carrera / Licenciatura</label>
                    <input type="text" name="carrera" id="carrera"
                           class="form-control bg-light" readonly>
                  </div>
                </div>

                <!-- Solo visible cuando nivel = uni (display only, sin name) -->
                <div class="col-md-2" id="grupo-modalidad-txt" style="display:none">
                  <div class="form-group">
                    <label>Modalidad</label>
                    <input type="text" id="txt-modalidad" class="form-control bg-light" readonly>
                  </div>
                </div>

              </div>

              <!-- Campo oculto que realmente se envía como modalidad -->
              <input type="hidden" name="modalidad" id="modalidad_val">

              <hr>

              <!-- Fila 2: Concepto + Detalle Trámite + Monto -->
              <h6 class="text-muted text-uppercase font-weight-bold mb-3">
                <i class="fas fa-file-invoice-dollar mr-1"></i> Concepto de Pago
              </h6>

              <div class="row align-items-end">

                <div class="col-md-3">
                  <div class="form-group">
                    <label for="concepto">Concepto <span class="text-danger">*</span></label>
                    <select name="concepto" id="concepto" class="form-control" required>
                      <option value="">— Selecciona —</option>
                      <option value="inscripcion">Inscripción</option>
                      <option value="reinscripcion">Reinscripción</option>
                      <option value="mensualidad">Mensualidad</option>
                      <option value="tramite">Trámite</option>
                    </select>
                  </div>
                </div>

                <!-- Solo visible cuando concepto = tramite -->
                <div class="col-md-4" id="grupo-tramite" style="display:none">
                  <div class="form-group">
                    <label for="detalle_tramite">Tipo de Trámite <span class="text-danger">*</span></label>
                    <select name="detalle_tramite" id="detalle_tramite" class="form-control">
                      <option value="">— Selecciona —</option>
                      <option value="constancia"    data-monto="150.00">Constancia Escolar — $150</option>
                      <option value="constancia_ext" data-monto="50.00">Constancia Extranjero — $50</option>
                      <option value="historial"     data-monto="150.00">Historial de Calificaciones — $150</option>
                      <option value="gafete"        data-monto="30.00" >Gafete — $30</option>
                    </select>
                  </div>
                </div>

                <div class="col-md-2">
                  <div class="form-group">
                    <label for="monto">Monto <span class="text-danger">*</span></label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text">$</span>
                      </div>
                      <input type="number" name="monto" id="monto"
                             class="form-control" step="0.01" min="0.01"
                             placeholder="0.00" required>
                    </div>
                  </div>
                </div>

              </div>
            </div><!-- /.card-body -->

            <div class="card-footer">
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> Registrar Pago
              </button>
              <button type="reset" class="btn btn-default ml-2">
                <i class="fas fa-undo mr-1"></i> Limpiar
              </button>
            </div>
          </form>
        </div><!-- /.card -->

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
<script>
const BASE_URL = '<?= base_url() ?>';

$(function () {

  // ── Nivel change ────────────────────────────────────────────────
  $('#nivel').on('change', function () {
    const nivel = $(this).val();

    resetAlumnoFields();

    const esUni      = nivel === 'uni';
    const esPosgrado = nivel === 'posgrado';

    // Nombre: editable solo para posgrado (sin AJAX disponible)
    $('#nombre_alumno').prop('readonly', !esPosgrado);

    $('#grupo-modalidad-sel').toggle(esPosgrado);
    $('#grupo-carrera').toggle(esUni);
    $('#grupo-modalidad-txt').toggle(esUni);

    $('#sel-modalidad').val('');
    $('#modalidad_val').val('');
  });

  // ── Modalidad posgrado → campo oculto ──────────────────────────
  $('#sel-modalidad').on('change', function () {
    $('#modalidad_val').val($(this).val());
  });

  // ── Buscar alumno por AJAX ──────────────────────────────────────
  $('#num_control').on('blur', function () {
    const numControl = $(this).val().trim();
    const nivel      = $('#nivel').val();

    if (!numControl || !nivel || nivel === 'posgrado') return;

    $('#spinner-buscar').show();
    $('#msg-error-alumno').hide();
    $('#nombre_alumno').val('').removeClass('is-valid is-invalid');

    $.get(BASE_URL + 'pagos/buscar-alumno', { num_control: numControl, nivel: nivel })
      .done(function (res) {
        if (res.found) {
          $('#nombre_alumno').val(res.nombre).addClass('is-valid');
          $('#carrera').val(res.carrera  ?? '');
          $('#txt-modalidad').val(res.modalidad ?? '');
          $('#modalidad_val').val(res.modalidad ?? '');
        } else {
          $('#nombre_alumno').addClass('is-invalid');
          $('#msg-error-alumno').show();
        }
      })
      .fail(function () {
        $('#msg-error-alumno').text('Error de conexión al buscar alumno.').show();
      })
      .always(function () {
        $('#spinner-buscar').hide();
      });
  });

  // ── Concepto → mostrar/ocultar trámite ─────────────────────────
  $('#concepto').on('change', function () {
    const esTramite = $(this).val() === 'tramite';
    $('#grupo-tramite').toggle(esTramite);
    if (!esTramite) {
      $('#detalle_tramite').val('');
      $('#monto').val('');
    }
  });

  // ── Detalle trámite → sugerir monto ────────────────────────────
  $('#detalle_tramite').on('change', function () {
    const monto = $(this).find(':selected').data('monto');
    if (monto) $('#monto').val(monto);
  });

  // ── Reset form ──────────────────────────────────────────────────
  $('#form-pago').on('reset', function () {
    setTimeout(function () {
      resetAlumnoFields();
      $('#grupo-modalidad-sel, #grupo-carrera, #grupo-modalidad-txt, #grupo-tramite').hide();
      $('#nombre_alumno').prop('readonly', true).removeClass('is-valid is-invalid');
    }, 10);
  });

  // ── Helpers ─────────────────────────────────────────────────────
  function resetAlumnoFields() {
    $('#num_control, #nombre_alumno, #carrera, #txt-modalidad, #modalidad_val').val('');
    $('#msg-error-alumno').hide();
    $('#nombre_alumno').removeClass('is-valid is-invalid');
  }
});
</script>
</body>
</html>
