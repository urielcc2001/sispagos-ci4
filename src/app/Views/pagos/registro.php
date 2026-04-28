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
            <a href="<?= base_url('pagos') ?>" class="nav-link active">
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

            <!-- Banner: adeudos de mensualidad detectados -->
            <div id="alerta-adeudos" class="alert alert-warning mx-3 mt-3 mb-0" style="display:none">
              <button type="button" class="close" onclick="$('#alerta-adeudos').hide()">&times;</button>
              <i class="fas fa-exclamation-triangle mr-2"></i>
              <strong>¡Atención!</strong> El alumno presenta mensualidades pendientes en:
              <strong id="lista-adeudos"></strong>
            </div>

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

                <!-- Solo visible cuando nivel = prepa -->
                <div class="col-md-2" id="grupo-modalidad-prepa" style="display:none">
                  <div class="form-group">
                    <label for="sel-modalidad-prepa">Modalidad</label>
                    <select id="sel-modalidad-prepa" class="form-control">
                      <option value="">— Selecciona —</option>
                      <option value="Escolarizado">Escolarizado</option>
                      <option value="Sabatino">Sabatino</option>
                      <option value="Dominical">Dominical</option>
                      <option value="Nocturno">Nocturno</option>
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
                    <select id="detalle_tramite" class="form-control">
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

                  <!-- Normal / Inter (inscripción y reinscripción) -->
                  <div class="col-md-auto" id="grupo-tipo-periodo" style="display:none">
                    <div class="form-group mb-0">
                      <label class="d-block">Tipo de Periodo <span class="text-danger">*</span></label>
                      <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-primary btn-tipo-periodo" data-val="Normal">Normal</button>
                        <button type="button" class="btn btn-outline-primary btn-tipo-periodo" data-val="Inter">Inter</button>
                      </div>
                    </div>
                  </div>

                  <!-- Plan de estudios (solo bachillerato, inscripción/reinscripción) -->
                  <div class="col-md-auto" id="grupo-bach-tipo" style="display:none">
                    <div class="form-group mb-0">
                      <label class="d-block">Plan de Estudios</label>
                      <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-secondary btn-bach-tipo active" data-val="Semestral">Escolarizado 3 años</button>
                        <button type="button" class="btn btn-outline-secondary btn-bach-tipo" data-val="Cuatrimestral">Escolarizado 2 años</button>
                      </div>
                    </div>
                  </div>

                  <!-- Mes de inicio de ciclo (solo inscripción) -->
                  <div class="col-md-3" id="grupo-mes-inicio" style="display:none">
                    <div class="form-group mb-0">
                      <label for="sel-mes-inicio">Mes de Inicio de Ciclo <span class="text-danger">*</span></label>
                      <select id="sel-mes-inicio" class="form-control form-control-sm">
                        <option value="1">Enero</option>
                        <option value="2">Febrero</option>
                        <option value="3">Marzo</option>
                        <option value="4">Abril</option>
                        <option value="5">Mayo</option>
                        <option value="6">Junio</option>
                        <option value="7">Julio</option>
                        <option value="8">Agosto</option>
                        <option value="9">Septiembre</option>
                        <option value="10">Octubre</option>
                        <option value="11">Noviembre</option>
                        <option value="12">Diciembre</option>
                      </select>
                    </div>
                  </div>

                </div>

                <!-- Rejilla de meses — mensualidad no-posgrado -->
                <div id="grupo-meses-mensualidad" class="mt-3" style="display:none">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="font-weight-bold mb-0">
                      Selecciona el Mes a Pagar <span class="text-danger">*</span>
                    </label>
                    <span id="badge-mes-seleccionado" class="badge badge-primary px-2"
                          style="display:none; font-size:.85rem"></span>
                  </div>
                  <div id="grid-meses-mensualidad" class="mb-3">
                    <span class="text-muted small">Ingresa el número de control para ver el estado de los meses.</span>
                  </div>
                  <div class="row">
                    <div class="col-md-3">
                      <div class="form-group mb-0">
                        <label for="fecha_pago_real">Fecha de Pago <span class="text-danger">*</span></label>
                        <input type="date" name="fecha_pago_real" id="fecha_pago_real"
                               class="form-control form-control-sm">
                        <small class="text-muted">Para cálculo de recargos</small>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Materia a pagar (solo posgrado + mensualidad) -->
                <div id="grupo-materia-posgrado" style="display:none">
                  <div class="row mt-2">
                    <div class="col-md-8">
                      <div class="form-group mb-0">
                        <label for="sel-materia-posgrado">Materia a Pagar <span class="text-danger">*</span></label>
                        <select id="sel-materia-posgrado" class="form-control">
                          <option value="">— Selecciona Maestría o Doctorado primero —</option>
                        </select>
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
              <input type="hidden" name="detalle_tramite" id="detalle_tramite_val">
              <input type="hidden" name="mes_inicio_ciclo" id="mes_inicio_ciclo_val">

              <!-- ── Campos administrativos ──────────────────────────────── -->
              <div class="row mt-3">
                <div class="col-md-3">
                  <div class="form-group mb-0">
                    <label for="metodo_pago">Método de Pago</label>
                    <select name="metodo_pago" id="metodo_pago" class="form-control">
                      <option value="Efectivo" selected>Efectivo</option>
                      <option value="Transferencia">Transferencia</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-9">
                  <div class="form-group mb-0">
                    <label for="observaciones">Observaciones</label>
                    <textarea name="observaciones" id="observaciones" class="form-control"
                              rows="2" placeholder="Notas adicionales (opcional)"></textarea>
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
    $('#alerta-adeudos').hide();

    const esUni      = nivel === 'uni';
    const esPosgrado = nivel === 'posgrado';
    const esPrepa    = nivel === 'prepa';

    $('#nombre_alumno').prop('readonly', !esPosgrado);

    $('#grupo-modalidad-sel').toggle(esPosgrado);
    $('#grupo-modalidad-prepa').toggle(esPrepa);
    $('#grupo-carrera').toggle(esUni);
    $('#grupo-modalidad-txt').toggle(esUni);

    $('#concepto option[value="mensualidad"]').text(esPosgrado ? 'Materia' : 'Mensualidad');
    $('#sel-modalidad').val('');
    $('#sel-modalidad-prepa').val('');
    $('#modalidad_val').val('');
    $('#detalle_tramite_val').val('');
    actualizarPeriodo();
    if ($('#concepto').val() === 'tramite') {
      cargarTramites();
    }
  });

  // ── Modalidad posgrado → campo oculto + actualizar lista materias ─
  $('#sel-modalidad').on('change', function () {
    $('#modalidad_val').val($(this).val());
    if ($('#nivel').val() === 'posgrado' && $('#concepto').val() === 'mensualidad') {
      actualizarListaMaterias();
    }
  });

  // ── Modalidad prepa → campo oculto ─────────────────────────────
  $('#sel-modalidad-prepa').on('change', function () {
    $('#modalidad_val').val($(this).val());
  });

  // ── Buscar alumno por AJAX ──────────────────────────────────────
  $('#num_control').on('blur', function () {
    const numControl = $(this).val().trim();
    const nivel      = $('#nivel').val();

    if (!numControl || !nivel) return;

    $('#spinner-buscar').show();
    $('#msg-error-alumno').hide();
    if (nivel !== 'posgrado') {
      $('#nombre_alumno').val('').removeClass('is-valid is-invalid');
    }

    $.get(BASE_URL + 'pagos/buscar-alumno', { num_control: numControl, nivel: nivel })
      .done(function (res) {
        if (res.found) {
          $('#nombre_alumno').val(res.nombre).addClass('is-valid');
          $('#carrera').val(res.carrera ?? '');
          if (nivel === 'posgrado') {
            // nombre pre-filled but still editable; grado from #sel-modalidad
          } else if (nivel !== 'prepa') {
            $('#txt-modalidad').val(res.modalidad ?? '');
            $('#modalidad_val').val(res.modalidad ?? '');
          } else {
            $.get(BASE_URL + 'pagos/ultimo-pago', { num_control: numControl, concepto: 'reinscripcion' })
              .done(function (u) {
                const mod = u.found && u.modalidad ? u.modalidad : null;
                if (mod) {
                  $('#sel-modalidad-prepa').val(mod).trigger('change');
                }
              });
          }
          sugerirPeriodo();
          verificarAdeudos(numControl, nivel);
          if ($('#concepto').val() === 'mensualidad' && nivel !== 'posgrado') {
            cargarMesesMensualidad();
          }
        } else if (nivel === 'posgrado') {
          $('#msg-error-alumno')
            .removeClass('text-danger').addClass('text-info')
            .html('<i class="fas fa-info-circle"></i> No encontrado en BD Universitaria. Capture el nombre.')
            .show();
        } else {
          $('#alerta-adeudos').hide();
          $('#nombre_alumno').addClass('is-invalid');
          $('#msg-error-alumno')
            .removeClass('text-info').addClass('text-danger')
            .html('<i class="fas fa-exclamation-circle"></i> Alumno no encontrado.')
            .show();
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

  // ── Detalle trámite → sincronizar hidden + sugerir precio ───────
  $('#detalle_tramite').on('change', function () {
    $('#detalle_tramite_val').val($(this).val());
    const precio = $(this).find(':selected').data('precio');
    if (precio) $('#monto').val(parseFloat(precio).toFixed(2));
  });

  // ── Reset form ──────────────────────────────────────────────────
  $('#form-pago').on('reset', function () {
    setTimeout(function () {
      resetAlumnoFields();
      $('#grupo-modalidad-sel, #grupo-modalidad-prepa, #grupo-carrera, #grupo-modalidad-txt, #grupo-tramite').hide();
      $('#grupo-dinamico, #grupo-tipo-periodo, #grupo-bach-tipo, #grupo-selector-periodo, #grupo-materia-posgrado, #grupo-meses-mensualidad').hide();
      $('#nombre_alumno').prop('readonly', true).removeClass('is-valid is-invalid');
      $('#periodo_pago_val, #tipo_periodo_val, #detalle_tramite_val, #mes_inicio_ciclo_val').val('');
      $('#sel-modalidad-prepa, #sel-materia-posgrado').val('');
      $('#btn-periodo-grid').empty();
      $('.btn-tipo-periodo').removeClass('btn-primary active').addClass('btn-outline-primary');
      $('#grid-meses-mensualidad').html('<span class="text-muted small">Ingresa el número de control para ver el estado de los meses.</span>');
      $('#badge-mes-seleccionado').hide();
    }, 10);
  });

  // ── Interceptar submit → Modal de confirmación ─────────────────
  $('#form-pago').on('submit', function (e) {
    e.preventDefault();

    const conceptoVal       = $('#concepto').val();
    const nivelVal          = $('#nivel').val();
    const esPosgradoMateria = nivelVal === 'posgrado' && conceptoVal === 'mensualidad';

    if (esPosgradoMateria) {
      if (!$('#detalle_tramite_val').val()) {
        Swal.fire({ icon: 'warning', title: 'Falta la materia', text: 'Selecciona la materia a pagar.' });
        return;
      }
    } else if (conceptoVal && conceptoVal !== 'tramite') {
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
    } else if (esPosgradoMateria && $('#detalle_tramite_val').val()) {
      conceptoDisplay += ' — ' + $('#detalle_tramite_val').val();
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

  const MATERIAS_MAESTRIA = [
    'PROBLEMAS POLÍTICOS Y SOCIOECONÓMICOS EN EDUCACIÓN',
    'CORRIENTES PEDAGÓGICAS ACTUALES',
    'PROCESOS DE ENSEÑANZA APRENDIZAJE',
    'ADMINISTRACION EDUCATIVA',
    'PLANEACION ESTRETEGICA',
    'METODOLOGIA DE LA INVESTIGACION EDUCATIVA',
    'SOFTWARE EDUCATIVO',
    'LEGISLACION EDUCATIVA',
    'GESTION DE PROYECTOS',
    'PLANEACION Y DESARROLLO CURRICULAR',
    'EVALUACION EDUCATIVA',
    'DIRECCION DE INSTITUCIONES EDUCATIVAS',
    'MODELOS PARA LA TOMA DE DECISIONES',
    'ADMINISTRACIÓN DE PERSONAL',
    'PROMOCIÓN E IMAGEN INSTITUCIONAL',
    'MODELOS DE EVALUACIÓN INSTITUCIONAL',
    'GESTIÓN DE LA CALIDAD EN LA EDUCACIÓN',
    'SEMINARIO DE INVESTIGACIÓN EDUCATIVA',
  ];

  const MATERIAS_DOCTORADO = [
    'SEMINARIO DE INVESTIGACIÓN',
    'GESTIÓN EDUCATIVA',
    'TALLER DE COMUNICACIÓN EDUCATIVA',
    'INVESTIGACIÓN EDUCATIVA',
    'TECNOLOGÍA EN LA EDUCACIÓN',
    'SEMINARIO DE TESIS DOCTORAL I',
    'FORMACIÓN DOCENTE',
    'ANÁLISIS Y DISEÑO CURRICULAR',
    'SEMINARIO DE TESIS DOCTORAL II',
    'TEORÍA DEL APRENDIZAJE',
    'PLANEACIÓN ESTRATÉGICA Y GESTIÓN EDUCATIVA',
    'SEMINARIO DE TESIS DOCTORAL III',
  ];

  // ── Mes inicio ciclo change → hidden field ───────────────────────
  $('#sel-mes-inicio').on('change', function () {
    $('#mes_inicio_ciclo_val').val($(this).val());
  });

  // ── Carga estado de meses para mensualidad ───────────────────────
  function cargarMesesMensualidad() {
    const numControl = $('#num_control').val().trim();
    const nivel      = $('#nivel').val();
    const $grid      = $('#grid-meses-mensualidad');

    $('#periodo_pago_val').val('');
    $('#badge-mes-seleccionado').hide();

    if (!numControl || !nivel) {
      $grid.html('<span class="text-muted small">Ingresa el número de control para ver el estado de los meses.</span>');
      return;
    }

    $grid.html('<span class="text-muted"><i class="fas fa-spinner fa-spin mr-1"></i> Cargando...</span>');

    $.get(BASE_URL + 'pagos/estado-mensualidades', { num_control: numControl, nivel: nivel })
      .done(function (res) {
        if (!res.meses || res.meses.length === 0) {
          $grid.html('<span class="text-warning small"><i class="fas fa-exclamation-triangle mr-1"></i> El alumno no tiene inscripción registrada.</span>');
          return;
        }
        renderizarMesesGrid(res.meses);
      })
      .fail(function () {
        $grid.html('<span class="text-danger small">Error al cargar el estado de los meses.</span>');
      });
  }

  function renderizarMesesGrid(meses) {
    const $grid = $('#grid-meses-mensualidad');
    $grid.empty();

    meses.forEach(function (m) {
      let cls, icon, disabled;

      if (m.status === 'pagado') {
        cls      = 'btn btn-success btn-sm mr-1 mb-1';
        icon     = '<i class="fas fa-check mr-1"></i>';
        disabled = true;
      } else if (m.status === 'pendiente') {
        cls      = 'btn btn-danger btn-sm mr-1 mb-1 btn-mes-mensualidad';
        icon     = '';
        disabled = false;
      } else {
        cls      = 'btn btn-secondary btn-sm mr-1 mb-1';
        icon     = '';
        disabled = true;
      }

      const $btn = $('<button type="button">')
        .addClass(cls)
        .css('min-width', '68px')
        .html(icon + m.nombre.substring(0, 3))
        .attr('data-mes', m.mes)
        .attr('data-nombre', m.nombre)
        .prop('disabled', disabled);

      $grid.append($btn);
    });
  }

  // ── Materia posgrado change → hidden field ──────────────────────
  $(document).on('change', '#sel-materia-posgrado', function () {
    $('#detalle_tramite_val').val($(this).val());
  });

  function actualizarListaMaterias() {
    const grado = $('#sel-modalidad').val();
    const $sel  = $('#sel-materia-posgrado');
    $sel.empty().append('<option value="">— Selecciona materia —</option>');
    const lista = grado === 'Maestría' ? MATERIAS_MAESTRIA :
                  grado === 'Doctorado' ? MATERIAS_DOCTORADO : [];
    lista.forEach(function (m) {
      $sel.append('<option value="' + m + '">' + m + '</option>');
    });
    $('#detalle_tramite_val').val('');
  }

  // ── Periodo de pago ─────────────────────────────────────────────
  function actualizarPeriodo() {
    const nivel    = $('#nivel').val();
    const concepto = $('#concepto').val();

    $('#grupo-dinamico').hide();
    $('#grupo-tipo-periodo, #grupo-bach-tipo, #grupo-selector-periodo, #grupo-materia-posgrado, #grupo-mes-inicio, #grupo-meses-mensualidad').hide();
    $('#periodo_pago_val, #tipo_periodo_val, #detalle_tramite_val').val('');
    $('#sel-materia-posgrado').val('');
    $('#btn-periodo-grid').empty();
    $('.btn-tipo-periodo').removeClass('btn-primary active').addClass('btn-outline-primary');

    if (!nivel || !concepto || concepto === 'tramite') return;

    $('#grupo-dinamico').show();

    if (concepto === 'mensualidad') {
      if (nivel === 'posgrado') {
        actualizarListaMaterias();
        $('#grupo-materia-posgrado').show();
        return;
      }
      const hoy = new Date().toISOString().split('T')[0];
      $('#fecha_pago_real').val(hoy);
      $('#grupo-meses-mensualidad').show();
      cargarMesesMensualidad();

    } else if (concepto === 'inscripcion') {
      $('#grupo-tipo-periodo').show();

      if (nivel === 'prepa') {
        $('.btn-bach-tipo').first().addClass('active');
        $('#grupo-bach-tipo').show();
        $('#detalle_tramite_val').val('Semestral');
        $('#label-selector-periodo').html('Semestral <span class="text-danger">*</span>');
      } else {
        $('#label-selector-periodo').html('Periodo <span class="text-danger">*</span>');
      }

      // Inicializar mes de inicio con el mes actual y mostrarlo
      const mesActual = new Date().getMonth() + 1;
      $('#sel-mes-inicio').val(mesActual);
      $('#mes_inicio_ciclo_val').val(mesActual);
      $('#grupo-mes-inicio').show();

      generarBotonesGrid(1, 1, 'num');
      $('#grupo-selector-periodo').show();

    } else if (concepto === 'reinscripcion') {
      $('#grupo-tipo-periodo').show();

      if (nivel === 'prepa') {
        $('.btn-bach-tipo').first().addClass('active');
        $('#grupo-bach-tipo').show();
        $('#detalle_tramite_val').val('Semestral');
        $('#label-selector-periodo').html('Semestral <span class="text-danger">*</span>');
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
    const nivelVal   = $('#nivel').val();
    const periodoNum = $('#periodo_pago_val').val();
    const tipo       = $('#tipo_periodo_val').val();
    if (nivelVal === 'posgrado' && concepto === 'mensualidad') return '';
    if (!periodoNum) return '';
    if (concepto === 'mensualidad') {
      return MESES[parseInt(periodoNum) - 1] + ' ' + new Date().getFullYear();
    }
    let txt = 'Periodo ' + periodoNum;
    if (tipo) txt += ' — ' + tipo;
    return txt;
  }

  // ── Precarga: sugiere el siguiente periodo ───────────────────────
  function sugerirPeriodo() {
    const numControl = $('#num_control').val().trim();
    const concepto   = $('#concepto').val();

    if (!numControl || !concepto || concepto === 'inscripcion' || concepto === 'tramite' || concepto === 'mensualidad') return;

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

          // Plan de estudios (solo prepa, guardado en detalle_tramite)
          if (res.plan_bach && $('#nivel').val() === 'prepa') {
            $('.btn-bach-tipo[data-val="' + res.plan_bach + '"]').trigger('click');
          }
          // Modalidad de horario (solo prepa)
          if (res.modalidad && $('#nivel').val() === 'prepa') {
            $('#sel-modalidad-prepa').val(res.modalidad).trigger('change');
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

  $(document).on('click', '.btn-mes-mensualidad:not(:disabled)', function () {
    const mesNum    = $(this).data('mes');
    const mesNombre = $(this).data('nombre');

    if ($(this).hasClass('btn-primary')) {
      $(this).removeClass('btn-primary').addClass('btn-danger');
      $('#periodo_pago_val').val('');
      $('#badge-mes-seleccionado').hide();
      return;
    }

    $('.btn-mes-mensualidad.btn-primary').removeClass('btn-primary').addClass('btn-danger');
    $(this).removeClass('btn-danger').addClass('btn-primary');
    $('#periodo_pago_val').val(mesNum);
    $('#badge-mes-seleccionado').text(mesNombre + ' ' + new Date().getFullYear()).show();
  });

  $(document).on('click', '.btn-bach-tipo', function () {
    $('.btn-bach-tipo').removeClass('btn-primary active').addClass('btn-outline-secondary');
    $(this).removeClass('btn-outline-secondary').addClass('btn-primary active');
    $('#label-selector-periodo').html($(this).data('val') + ' <span class="text-danger">*</span>');
    $('#detalle_tramite_val').val($(this).data('val'));
  });

  // ── Helpers ─────────────────────────────────────────────────────
  function resetAlumnoFields() {
    $('#num_control, #nombre_alumno, #carrera, #txt-modalidad, #modalidad_val').val('');
    $('#sel-modalidad-prepa').val('');
    $('#msg-error-alumno').hide();
    $('#alerta-adeudos').hide();
    $('#nombre_alumno').removeClass('is-valid is-invalid');
  }

  // ── Verificar adeudos de mensualidad ────────────────────────────
  function verificarAdeudos(numControl, nivel) {
    $('#alerta-adeudos').hide();
    if (!numControl || !nivel || nivel === 'posgrado') return;

    $.get(BASE_URL + 'pagos/verificar-adeudos', { num_control: numControl, nivel: nivel })
      .done(function (res) {
        if (res.adeudos && res.adeudos.length > 0) {
          $('#lista-adeudos').text(res.adeudos.join(', ') + '.');
          $('#alerta-adeudos').show();
        }
      });
  }
});
</script>
</body>
</html>
