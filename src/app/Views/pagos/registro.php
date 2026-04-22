<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registro de Pago — SistemaPagos</title>
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
          <?php if (service('session')->get('rol') === 'admin'): ?>
          <li class="nav-item">
            <a href="<?= base_url('dashboard') ?>" class="nav-link">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Dashboard</p>
            </a>
          </li>
          <?php endif; ?>
          <li class="nav-item">
            <a href="<?= base_url('pagos') ?>" class="nav-link active">
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
          <?php endif; ?>
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

                <!-- Solo visible cuando concepto = tramite (opciones cargadas por AJAX) -->
                <div class="col-md-4" id="grupo-tramite" style="display:none">
                  <div class="form-group">
                    <label for="detalle_tramite">Tipo de Trámite <span class="text-danger">*</span></label>
                    <select name="detalle_tramite" id="detalle_tramite" class="form-control">
                      <option value="">— Selecciona nivel primero —</option>
                    </select>
                    <small id="msg-tramites" class="text-muted" style="display:none">
                      <i class="fas fa-spinner fa-spin mr-1"></i> Cargando trámites…
                    </small>
                  </div>
                </div>

                <!-- Visible según nivel + concepto (mensualidad → mes; inscripción/reinscripción → periodo semestral/cuatrimestral) -->
                <div class="col-md-3" id="grupo-periodo" style="display:none">
                  <div class="form-group">
                    <label for="periodo_pago" id="label-periodo">Periodo <span class="text-danger">*</span></label>
                    <select name="periodo_pago" id="periodo_pago" class="form-control">
                      <option value="">— Selecciona —</option>
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

<!-- Modal de Confirmación de Pago -->
<div class="modal fade" id="modal-confirmar" tabindex="-1" role="dialog" aria-labelledby="modal-confirmar-label" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 class="modal-title text-white" id="modal-confirmar-label">
          <i class="fas fa-file-invoice-dollar mr-2"></i>Confirmar Registro de Pago
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p class="text-muted mb-3">¿Confirmas el registro del siguiente pago?</p>
        <table class="table table-sm table-borderless mb-0">
          <tbody>
            <tr>
              <th class="text-right text-muted pr-3" style="width:35%">Alumno</th>
              <td id="modal-nombre" class="font-weight-bold"></td>
            </tr>
            <tr>
              <th class="text-right text-muted pr-3">Nivel</th>
              <td id="modal-nivel"></td>
            </tr>
            <tr>
              <th class="text-right text-muted pr-3">Concepto</th>
              <td id="modal-concepto"></td>
            </tr>
            <tr id="fila-modal-periodo" style="display:none">
              <th class="text-right text-muted pr-3">Periodo</th>
              <td id="modal-periodo"></td>
            </tr>
            <tr class="table-success">
              <th class="text-right text-muted pr-3">Monto</th>
              <td id="modal-monto" class="font-weight-bold text-success" style="font-size:1.15rem"></td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">
          <i class="fas fa-times mr-1"></i> Cancelar
        </button>
        <button type="button" class="btn btn-primary" id="btn-confirmar">
          <i class="fas fa-check mr-1"></i> Confirmar
        </button>
      </div>
    </div>
  </div>
</div>
</div><!-- /.wrapper -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script>
const BASE_URL = '<?= base_url() ?>';

$(function () {

  // ── Nivel change ────────────────────────────────────────────────
  $('#nivel').on('change', function () {
    const nivel = $(this).val();

    resetAlumnoFields();

    const esUni      = nivel === 'uni';
    const esPosgrado = nivel === 'posgrado';

    $('#nombre_alumno').prop('readonly', !esPosgrado);

    $('#grupo-modalidad-sel').toggle(esPosgrado);
    $('#grupo-carrera').toggle(esUni);
    $('#grupo-modalidad-txt').toggle(esUni);

    $('#sel-modalidad').val('');
    $('#modalidad_val').val('');
    actualizarPeriodo();
    if ($('#concepto').val() === 'tramite') {
      cargarTramites();
    }
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

  // ── Concepto → mostrar/ocultar trámite + periodo ───────────────
  $('#concepto').on('change', function () {
    const esTramite = $(this).val() === 'tramite';
    $('#grupo-tramite').toggle(esTramite);
    if (esTramite) {
      cargarTramites();
      $('#monto').val('');
    } else {
      $('#detalle_tramite').val('');
      $('#monto').val('');
    }
    actualizarPeriodo();
  });

  // ── Detalle trámite → sugerir precio ───────────────────────────
  $('#detalle_tramite').on('change', function () {
    const precio = $(this).find(':selected').data('precio');
    if (precio) $('#monto').val(parseFloat(precio).toFixed(2));
  });

  // ── Reset form ──────────────────────────────────────────────────
  $('#form-pago').on('reset', function () {
    setTimeout(function () {
      resetAlumnoFields();
      $('#grupo-modalidad-sel, #grupo-carrera, #grupo-modalidad-txt, #grupo-tramite, #grupo-periodo').hide();
      $('#nombre_alumno').prop('readonly', true).removeClass('is-valid is-invalid');
      $('#periodo_pago').val('').prop('required', false);
    }, 10);
  });

  // ── Interceptar submit → Modal de confirmación ─────────────────
  $('#form-pago').on('submit', function (e) {
    e.preventDefault();

    const nombre  = $('#nombre_alumno').val().trim();
    const nivel   = $('#nivel option:selected').text().trim();
    const concepto = $('#concepto option:selected').text().trim();
    const tramite  = $('#detalle_tramite option:selected').text().trim();
    const periodo  = $('#periodo_pago option:selected').text().trim();
    const montoRaw = parseFloat($('#monto').val()) || 0;
    const monto    = montoRaw.toLocaleString('es-MX', { style: 'currency', currency: 'MXN' });

    let conceptoDisplay = concepto;
    if ($('#concepto').val() === 'tramite' && tramite) {
      conceptoDisplay = tramite;
    }

    const tienePeriodo = $('#grupo-periodo').is(':visible') && periodo && periodo !== '— Selecciona —';
    $('#modal-nombre').text(nombre   || '—');
    $('#modal-nivel').text(nivel     || '—');
    $('#modal-concepto').text(conceptoDisplay || '—');
    $('#fila-modal-periodo').toggle(tienePeriodo);
    $('#modal-periodo').text(tienePeriodo ? periodo : '');
    $('#modal-monto').text(monto);

    $('#modal-confirmar').modal('show');
  });

  // ── Confirmar → AJAX ────────────────────────────────────────────
  $('#btn-confirmar').on('click', function () {
    const $btn = $(this);
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Procesando...');

    $.ajax({
      url    : BASE_URL + 'pagos/registrar',
      method : 'POST',
      data   : $('#form-pago').serialize(),
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      success: function (res) {
        // Renovar token CSRF para el siguiente envío
        $('input[name="' + res.csrf_name + '"]').val(res.csrf_hash);

        $('#modal-confirmar').modal('hide');
        $('#form-pago').trigger('reset');

        Swal.fire({
          icon             : 'success',
          title            : '¡Pago registrado!',
          html             : 'Folio: <strong>' + res.folio_digital + '</strong><br>Se abrirá el comprobante en una nueva pestaña.',
          timer            : 3000,
          timerProgressBar : true,
          showConfirmButton: false,
        }).then(function () {
          window.open(res.pdf_url, '_blank');
          $('#num_control').focus();
        });
      },
      error: function (xhr) {
        const msg = xhr.responseJSON?.message ?? 'Error al registrar el pago. Intente de nuevo.';
        $('#modal-confirmar').modal('hide');
        Swal.fire({ icon: 'error', title: 'Error', text: msg });
      },
      complete: function () {
        $btn.prop('disabled', false).html('<i class="fas fa-check mr-1"></i> Confirmar');
      },
    });
  });

  // ── Carga dinámica de trámites ──────────────────────────────────
  function cargarTramites() {
    const nivel   = $('#nivel').val();
    const $select = $('#detalle_tramite');
    const $msg    = $('#msg-tramites');

    $select.empty().append('<option value="">— Cargando… —</option>').prop('disabled', true);
    $msg.show();

    $.get(BASE_URL + 'pagos/tramites-disponibles', { nivel: nivel })
      .done(function (tramites) {
        $select.empty().append('<option value="">— Selecciona trámite —</option>');
        if (tramites.length === 0) {
          $select.append('<option value="" disabled>Sin trámites disponibles para este nivel</option>');
        } else {
          tramites.forEach(function (t) {
            const precio  = parseFloat(t.precio_sugerido).toFixed(2);
            const label   = t.nombre_tramite + ' — $' + precio;
            $select.append('<option value="' + t.nombre_tramite + '" data-precio="' + precio + '">' + label + '</option>');
          });
        }
        $select.prop('disabled', false);
      })
      .fail(function () {
        $select.empty().append('<option value="">Error al cargar trámites</option>').prop('disabled', false);
      })
      .always(function () { $msg.hide(); });
  }

  // ── Periodo de pago ─────────────────────────────────────────────
  function actualizarPeriodo() {
    const nivel    = $('#nivel').val();
    const concepto = $('#concepto').val();
    const $grupo   = $('#grupo-periodo');
    const $select  = $('#periodo_pago');

    if (!nivel || !concepto || concepto === 'tramite' || nivel === 'posgrado') {
      $grupo.hide();
      $select.val('').prop('required', false);
      return;
    }

    $select.find('option:not(:first)').remove();

    const anio = new Date().getFullYear();

    if (concepto === 'mensualidad') {
      $('#label-periodo').text('Mes a pagar *');
      ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
       'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre']
        .forEach(function (m) {
          const v = m + ' ' + anio;
          $select.append('<option value="' + v + '">' + v + '</option>');
        });
    } else {
      $('#label-periodo').text('Periodo *');
      if (nivel === 'prepa') {
        ['Agosto - Diciembre', 'Enero - Julio']
          .forEach(function (p) {
            const v = p + ' ' + anio;
            $select.append('<option value="' + v + '">' + v + '</option>');
          });
      } else {
        ['Enero - Abril', 'Mayo - Agosto', 'Septiembre - Diciembre']
          .forEach(function (p) {
            const v = p + ' ' + anio;
            $select.append('<option value="' + v + '">' + v + '</option>');
          });
      }
    }

    $grupo.show();
    $select.prop('required', true);
  }

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
