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
          <li class="nav-item">
            <a href="<?= base_url('admin/conceptos') ?>" class="nav-link">
              <i class="nav-icon fas fa-cogs"></i>
              <p>Conceptos</p>
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

              <!-- ── Periodo de pago (dinámico por concepto) ──────────────── -->
              <div id="grupo-dinamico" class="mt-3" style="display:none">

                <div class="row align-items-end">

                  <!-- Fecha real de pago (solo mensualidad) -->
                  <div class="col-md-3" id="grupo-fecha-pago" style="display:none">
                    <div class="form-group">
                      <label>Fecha de Pago <span class="text-danger">*</span></label>
                      <input type="date" name="fecha_pago_real" id="fecha_pago_real" class="form-control">
                      <small class="text-muted">Para cálculo de recargos</small>
                    </div>
                  </div>

                  <!-- Normal / Inter (inscripción y reinscripción) -->
                  <div class="col-md-auto" id="grupo-tipo-periodo" style="display:none">
                    <div class="form-group">
                      <label class="d-block">Tipo de Periodo <span class="text-danger">*</span></label>
                      <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-primary btn-tipo-periodo" data-val="Normal">Normal</button>
                        <button type="button" class="btn btn-outline-primary btn-tipo-periodo" data-val="Inter">Inter</button>
                      </div>
                    </div>
                  </div>

                  <!-- Semestre / Cuatrimestre (solo bachillerato + reinscripción) -->
                  <div class="col-md-auto" id="grupo-bach-tipo" style="display:none">
                    <div class="form-group">
                      <label class="d-block">Modalidad de Periodo</label>
                      <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-secondary btn-bach-tipo active" data-val="Semestre">Semestre</button>
                        <button type="button" class="btn btn-outline-secondary btn-bach-tipo" data-val="Cuatrimestre">Cuatrimestre</button>
                      </div>
                    </div>
                  </div>

                </div>

                <!-- Grid de números de periodo -->
                <div class="row mt-2" id="grupo-selector-periodo" style="display:none">
                  <div class="col-12">
                    <label id="label-selector-periodo" class="font-weight-bold text-muted mb-2 d-block">
                      Periodo <span class="text-danger">*</span>
                    </label>
                    <div id="btn-periodo-grid"></div>
                  </div>
                </div>

              </div><!-- /grupo-dinamico -->

              <input type="hidden" name="periodo_pago" id="periodo_pago_val">
              <input type="hidden" name="tipo_periodo" id="tipo_periodo_val">

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
          $('#carrera').val(res.carrera   ?? '');
          $('#txt-modalidad').val(res.modalidad ?? '');
          $('#modalidad_val').val(res.modalidad ?? '');
          sugerirPeriodo();
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
      $('#grupo-modalidad-sel, #grupo-carrera, #grupo-modalidad-txt, #grupo-tramite').hide();
      $('#grupo-dinamico, #grupo-fecha-pago, #grupo-tipo-periodo, #grupo-bach-tipo, #grupo-selector-periodo').hide();
      $('#nombre_alumno').prop('readonly', true).removeClass('is-valid is-invalid');
      $('#periodo_pago_val, #tipo_periodo_val').val('');
      $('#btn-periodo-grid').empty();
      $('.btn-tipo-periodo').removeClass('btn-primary active').addClass('btn-outline-primary');
    }, 10);
  });

  // ── Interceptar submit → Modal de confirmación ─────────────────
  $('#form-pago').on('submit', function (e) {
    e.preventDefault();

    const conceptoVal = $('#concepto').val();

    if (conceptoVal && conceptoVal !== 'tramite') {
      if (!$('#periodo_pago_val').val()) {
        Swal.fire({ icon: 'warning', title: 'Falta el periodo', text: 'Selecciona el número de periodo o mes.' });
        return;
      }
      if ((conceptoVal === 'inscripcion' || conceptoVal === 'reinscripcion') && !$('#tipo_periodo_val').val()) {
        Swal.fire({ icon: 'warning', title: 'Falta el tipo de periodo', text: 'Selecciona Normal o Inter.' });
        return;
      }
      if (conceptoVal === 'mensualidad' && !$('#fecha_pago_real').val()) {
        Swal.fire({ icon: 'warning', title: 'Falta la fecha', text: 'Selecciona la fecha de pago.' });
        return;
      }
    }

    const nombre   = $('#nombre_alumno').val().trim();
    const nivel    = $('#nivel option:selected').text().trim();
    const concepto = $('#concepto option:selected').text().trim();
    const tramite  = $('#detalle_tramite option:selected').text().trim();
    const montoRaw = parseFloat($('#monto').val()) || 0;
    const monto    = montoRaw.toLocaleString('es-MX', { style: 'currency', currency: 'MXN' });

    let conceptoDisplay = concepto;
    if (conceptoVal === 'tramite' && tramite) {
      conceptoDisplay = tramite;
    }

    const periodoDisplay = construirPeriodoDisplay();
    $('#modal-nombre').text(nombre || '—');
    $('#modal-nivel').text(nivel   || '—');
    $('#modal-concepto').text(conceptoDisplay || '—');
    $('#fila-modal-periodo').toggle(!!periodoDisplay);
    $('#modal-periodo').text(periodoDisplay);
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

  // ── Constantes ──────────────────────────────────────────────────
  const MESES = ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
                 'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

  // ── Periodo de pago ─────────────────────────────────────────────
  function actualizarPeriodo() {
    const nivel    = $('#nivel').val();
    const concepto = $('#concepto').val();

    $('#grupo-dinamico').hide();
    $('#grupo-fecha-pago, #grupo-tipo-periodo, #grupo-bach-tipo, #grupo-selector-periodo').hide();
    $('#periodo_pago_val, #tipo_periodo_val').val('');
    $('#btn-periodo-grid').empty();
    $('.btn-tipo-periodo').removeClass('btn-primary active').addClass('btn-outline-primary');

    if (!nivel || !concepto || concepto === 'tramite') return;

    $('#grupo-dinamico').show();

    if (concepto === 'mensualidad') {
      const hoy = new Date().toISOString().split('T')[0];
      $('#fecha_pago_real').val(hoy);
      $('#periodo_pago_val').val(new Date().getMonth() + 1);
      $('#grupo-fecha-pago').show();
      sugerirPeriodo();

    } else if (concepto === 'inscripcion') {
      $('#grupo-tipo-periodo').show();

      if (nivel === 'prepa') {
        $('.btn-bach-tipo').first().addClass('active');
        $('#grupo-bach-tipo').show();
        $('#modalidad_val').val('Semestre');
        $('#label-selector-periodo').html('Semestre <span class="text-danger">*</span>');
      } else {
        $('#label-selector-periodo').html('Periodo <span class="text-danger">*</span>');
      }

      generarBotonesGrid(1, 1, 'num');
      $('#grupo-selector-periodo').show();

    } else if (concepto === 'reinscripcion') {
      $('#grupo-tipo-periodo').show();

      if (nivel === 'prepa') {
        $('.btn-bach-tipo').first().addClass('active');
        $('#grupo-bach-tipo').show();
        $('#modalidad_val').val('Semestre');
        $('#label-selector-periodo').html('Semestre <span class="text-danger">*</span>');
        generarBotonesGrid(2, 6, 'num');
      } else {
        $('#label-selector-periodo').html('Periodo <span class="text-danger">*</span>');
        generarBotonesGrid(2, 10, 'num');
      }
      $('#grupo-selector-periodo').show();
      sugerirPeriodo();
    }
  }

  function generarBotonesGrid(desde, hasta, tipo) {
    const $grid    = $('#btn-periodo-grid');
    const bloquear = (desde === hasta);
    $grid.empty();

    for (let i = desde; i <= hasta; i++) {
      const label = (tipo === 'mes') ? MESES[i - 1].substring(0, 3) : i;
      const $btn  = $('<button type="button">')
        .addClass('btn btn-sm btn-outline-primary btn-num-periodo mr-1 mb-1')
        .css('min-width', '52px')
        .text(label)
        .attr('data-val', i);

      if (bloquear) {
        $btn.removeClass('btn-outline-primary').addClass('btn-primary active').prop('disabled', true);
        $('#periodo_pago_val').val(i);
      }
      $grid.append($btn);
    }
  }

  function construirPeriodoDisplay() {
    const concepto   = $('#concepto').val();
    const periodoNum = $('#periodo_pago_val').val();
    const tipo       = $('#tipo_periodo_val').val();
    if (!periodoNum) return '';
    if (concepto === 'mensualidad') {
      return MESES[parseInt(periodoNum) - 1] + ' ' + new Date().getFullYear();
    }
    let txt = 'Periodo ' + periodoNum;
    if (tipo) txt += ' — ' + tipo;
    return txt;
  }

  // ── Fecha pago real → extrae mes automáticamente ────────────────
  $(document).on('change', '#fecha_pago_real', function () {
    const val = $(this).val();
    if (val) {
      $('#periodo_pago_val').val(parseInt(val.split('-')[1], 10));
    }
  });

  // ── Precarga: sugiere el siguiente periodo ───────────────────────
  function sugerirPeriodo() {
    const numControl = $('#num_control').val().trim();
    const concepto   = $('#concepto').val();

    if (!numControl || !concepto || concepto === 'inscripcion' || concepto === 'tramite') return;

    $.get(BASE_URL + 'pagos/ultimo-pago', { num_control: numControl, concepto: concepto })
      .done(function (res) {
        if (!res.found) return;

        if (concepto === 'mensualidad') {
          const mes   = String(res.sugerido).padStart(2, '0');
          const fecha = res.anio + '-' + mes + '-01';
          $('#fecha_pago_real').val(fecha).trigger('change');
          mostrarBadgeSugerido(MESES[res.sugerido - 1] + ' ' + res.anio);

        } else if (concepto === 'reinscripcion') {
          // Periodo
          const $btn = $('#btn-periodo-grid .btn-num-periodo[data-val="' + res.sugerido + '"]');
          if ($btn.length) {
            $btn.trigger('click');
            mostrarBadgeSugerido('Periodo ' + res.sugerido);
          }

          // Tipo Normal / Inter
          if (res.tipo_periodo) {
            $('.btn-tipo-periodo[data-val="' + res.tipo_periodo + '"]').trigger('click');
          }

          // Semestre / Cuatrimestre (solo prepa, guardado en modalidad)
          if (res.modalidad && $('#nivel').val() === 'prepa') {
            $('.btn-bach-tipo[data-val="' + res.modalidad + '"]').trigger('click');
          }
        }
      });
  }

  function mostrarBadgeSugerido(texto) {
    $('#badge-sugerido').remove();
    const $badge = $('<span id="badge-sugerido" class="badge badge-info ml-2">')
      .html('<i class="fas fa-magic mr-1"></i>Sugerido: ' + texto);
    $('#label-selector-periodo, #grupo-fecha-pago label').first().after($badge);
    setTimeout(function () { $badge.fadeOut(400, function () { $(this).remove(); }); }, 4000);
  }

  // ── Handlers botones dinámicos ──────────────────────────────────
  $(document).on('click', '.btn-num-periodo:not(:disabled)', function () {
    $('.btn-num-periodo').removeClass('btn-primary active').addClass('btn-outline-primary');
    $(this).removeClass('btn-outline-primary').addClass('btn-primary active');
    $('#periodo_pago_val').val($(this).data('val'));
  });

  $(document).on('click', '.btn-tipo-periodo', function () {
    $('.btn-tipo-periodo').removeClass('btn-primary active').addClass('btn-outline-primary');
    $(this).removeClass('btn-outline-primary').addClass('btn-primary active');
    $('#tipo_periodo_val').val($(this).data('val'));
  });

  $(document).on('click', '.btn-bach-tipo', function () {
    $('.btn-bach-tipo').removeClass('btn-primary active').addClass('btn-outline-secondary');
    $(this).removeClass('btn-outline-secondary').addClass('btn-primary active');
    $('#label-selector-periodo').html($(this).data('val') + ' <span class="text-danger">*</span>');
    if ($('#nivel').val() === 'prepa') {
      $('#modalidad_val').val($(this).data('val'));
    }
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
