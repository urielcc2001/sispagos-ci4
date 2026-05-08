<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Registro de Pago<?= $this->endSection() ?>

<?= $this->section('head_extra') ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

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

    <?php if (isset($error) || session()->getFlashdata('error')): ?>
      <div class="alert alert-danger alert-dismissible fade show">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <?= esc((string) ($error ?? session()->getFlashdata('error') ?? '')) ?>
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

        <div id="alerta-adeudos" class="alert alert-warning mx-3 mt-3 mb-0" style="display:none">
          <button type="button" class="close" onclick="$('#alerta-adeudos').hide()">&times;</button>
          <i class="fas fa-exclamation-triangle mr-2"></i>
          <strong>¡Atención!</strong> El alumno presenta mensualidades pendientes en:
          <strong id="lista-adeudos"></strong>
        </div>

        <div class="card-body">

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

            <div class="col-md-2" id="grupo-modalidad-prepa" style="display:none">
              <div class="form-group">
                <label for="sel-modalidad-prepa">Modalidad</label>
                <select id="sel-modalidad-prepa" class="form-control">
                  <option value="">— Selecciona —</option>
                  <option value="Escolarizado 3 Años">Escolarizado 3 Años</option>
                  <option value="Escolarizado 2 Años">Escolarizado 2 Años</option>
                  <option value="Sabatino">Sabatino</option>
                  <option value="Dominical">Dominical</option>
                  <option value="Nocturno">Nocturno</option>
                </select>
              </div>
            </div>

            <div class="col-md-2" id="grupo-semestre-prepa" style="display:none">
              <div class="form-group">
                <label>Semestre Actual</label>
                <input type="text" id="txt-semestre-prepa" class="form-control bg-light" readonly placeholder="—">
              </div>
            </div>

            <div class="col-md-2" id="grupo-generacion-posgrado" style="display:none">
              <div class="form-group">
                <label>Generación</label>
                <input type="text" id="txt-generacion" class="form-control bg-light" readonly placeholder="—">
              </div>
            </div>

            <div class="col-md-2" id="grupo-cuatrisem-uni" style="display:none">
              <div class="form-group">
                <label>Cuatrimestre Actual</label>
                <input type="text" id="txt-cuatrisem-uni" class="form-control bg-light" readonly placeholder="—">
              </div>
            </div>

            <div class="col-md-2" id="grupo-generacion-uni" style="display:none">
              <div class="form-group">
                <label>Generación</label>
                <input type="text" id="txt-generacion-uni" class="form-control bg-light" readonly placeholder="—">
              </div>
            </div>

            <div class="col-md-2" id="grupo-inter-uni" style="display:none">
              <div class="form-group">
                <label>Tipo de Cuatrimestre</label>
                <input type="text" id="txt-inter-uni" class="form-control bg-light" readonly placeholder="—">
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

            <div class="col-md-4" id="grupo-carrera" style="display:none">
              <div class="form-group">
                <label>Carrera / Licenciatura</label>
                <input type="text" name="carrera" id="carrera"
                       class="form-control bg-light" readonly>
              </div>
            </div>

            <div class="col-md-2" id="grupo-modalidad-txt" style="display:none">
              <div class="form-group">
                <label>Modalidad</label>
                <select id="txt-modalidad" class="form-control bg-light" disabled>
                  <option value="">— Selecciona —</option>
                  <option value="Escolarizado">Escolarizado</option>
                  <option value="Sabatino">Sabatino</option>
                  <option value="Dominical">Dominical</option>
                  <option value="En Linea">En Linea</option>
                </select>
              </div>
            </div>

          </div>

          <input type="hidden" name="modalidad" id="modalidad_val">

          <hr>

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
                <label for="monto" id="label-monto">Monto <span class="text-danger">*</span></label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">$</span>
                  </div>
                  <input type="number" name="monto" id="monto"
                         class="form-control" step="0.01" min="0.01"
                         placeholder="0.00" required>
                </div>
                <small id="hint-monto-mes" class="text-muted" style="display:none; font-size:.75rem">
                  Precio por mensualidad (ajustable por mes abajo)
                </small>
              </div>
            </div>

          </div>

          <div id="grupo-dinamico" class="mt-3" style="display:none">

            <div class="row align-items-end">

              <div class="col-md-auto" id="grupo-tipo-periodo" style="display:none">
                <div class="form-group mb-0">
                  <label class="d-block">Tipo de Periodo <span class="text-danger">*</span></label>
                  <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-primary btn-tipo-periodo" data-val="Normal">Normal</button>
                    <button type="button" class="btn btn-outline-primary btn-tipo-periodo" data-val="Inter">Inter</button>
                  </div>
                </div>
              </div>

              <div class="col-md-auto" id="grupo-bach-tipo" style="display:none">
                <div class="form-group mb-0">
                  <label class="d-block">Plan de Estudios</label>
                  <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-secondary btn-bach-tipo active" data-val="Semestral">Escolarizado 3 años</button>
                    <button type="button" class="btn btn-outline-secondary btn-bach-tipo" data-val="Cuatrimestral">Escolarizado 2 años</button>
                  </div>
                </div>
              </div>

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

            <div id="grupo-meses-mensualidad" class="mt-3" style="display:none">

              <!-- Selector de año -->
              <div class="row mb-2">
                <div class="col-md-2">
                  <div class="form-group mb-0">
                    <label for="sel-anio-mensualidad" class="font-weight-bold">
                      Año <span class="text-danger">*</span>
                    </label>
                    <select name="anio_mensualidad" id="sel-anio-mensualidad" class="form-control form-control-sm">
                      <!-- Poblado por JS -->
                    </select>
                  </div>
                </div>
              </div>

              <!-- Cabecera de meses -->
              <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="font-weight-bold mb-0">
                  Selecciona el/los Mes(es) a Pagar <span class="text-danger">*</span>
                </label>
                <span id="badge-mes-seleccionado" class="badge badge-primary px-2"
                      style="display:none; font-size:.85rem"></span>
              </div>

              <!-- Grid de meses (checkboxes estilizados) -->
              <div id="grid-meses-mensualidad" class="mb-2">
                <span class="text-muted small">Ingresa el número de control para ver el estado de los meses.</span>
              </div>

              <!-- Leyenda de estados -->
              <div class="mb-3" style="font-size:.78rem">
                <span class="badge badge-success mr-2"><i class="fas fa-check mr-1"></i>Pagado</span>
                <span class="badge badge-danger mr-2">Pendiente</span>
                <span class="badge badge-warning text-dark mr-2"><i class="fas fa-exclamation-triangle mr-1"></i>Abonos parciales</span>
                <span class="badge badge-secondary">Futuro</span>
              </div>

              <!-- Montos individuales por mes (solo en selección múltiple) -->
              <div id="tabla-montos-meses" class="mb-3" style="display:none">
                <div class="d-flex align-items-center mb-1">
                  <span class="small font-weight-bold">Monto por mes:</span>
                  <span id="total-meses-display" class="ml-auto badge badge-primary" style="font-size:.82rem"></span>
                </div>
                <div id="grid-montos-meses"></div>
              </div>

              <!-- Selector de abono (solo aparece con 1 mes seleccionado) -->
              <div id="grupo-abono" class="border rounded p-2 mb-3 bg-light" style="display:none">
                <div id="resumen-abonos-previos" class="mb-2 px-2 py-1 rounded" style="display:none; background:#fff8e1; border-left:3px solid #ffc107;"></div>
                <div class="form-check mb-1">
                  <input class="form-check-input" type="checkbox" id="chk-es-abono">
                  <label class="form-check-label font-weight-bold" for="chk-es-abono">
                    ¿Es un pago parcial (abono)?
                  </label>
                </div>
                <div id="sel-num-abono-wrapper" class="ml-4" style="display:none">
                  <label for="sel-num-abono" class="small text-muted mb-1">Número de abono:</label>
                  <select name="num_abono" id="sel-num-abono" class="form-control form-control-sm" style="width:160px" disabled>
                    <option value="1">Abono 1</option>
                    <option value="2">Abono 2</option>
                    <option value="3">Abono 3</option>
                    <option value="4">Abono 4</option>
                  </select>
                  <div class="form-check mt-2" id="wrap-cierra-mes" style="display:none">
                    <input class="form-check-input" type="checkbox" id="chk-cierra-mes">
                    <label class="form-check-label font-weight-bold text-success" for="chk-cierra-mes">
                      <i class="fas fa-check-circle mr-1"></i>¿Este pago cierra el adeudo del mes?
                    </label>
                    <small class="d-block text-muted" style="font-size:.75rem">
                      El mes quedará marcado en verde (pagado completo).
                    </small>
                  </div>
                </div>
              </div>

              <!-- Fecha de pago -->
              <div class="row">
                <div class="col-md-3">
                  <div class="form-group mb-0">
                    <label for="fecha_pago_real">Fecha de Pago <span class="text-danger">*</span></label>
                    <input type="date" name="fecha_pago_real" id="fecha_pago_real"
                           class="form-control form-control-sm">
                    <small class="text-muted">Fecha real de recepción del pago</small>
                  </div>
                </div>
              </div>

            </div>

            <div id="grupo-materia-posgrado" style="display:none" class="mt-2">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="font-weight-bold mb-0">
                  Selecciona la Materia a Pagar <span class="text-danger">*</span>
                </label>
                <span id="badge-materia-seleccionada" class="badge badge-primary px-2"
                      style="display:none; font-size:.8rem; max-width:60%; white-space:normal; text-align:right"></span>
              </div>
              <div id="grid-materias-posgrado" class="mb-2">
                <span class="text-muted small">Busca al alumno para ver las materias del programa.</span>
              </div>
              <div class="mt-1">
                <span class="badge badge-success mr-2"><i class="fas fa-check mr-1"></i>Pagada</span>
                <span class="badge badge-secondary"><i class="fas fa-clock mr-1"></i>Pendiente</span>
              </div>
            </div>

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

<?= $this->endSection() ?>

<?= $this->section('modals') ?>
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
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
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

    $('#grupo-modalidad-prepa').toggle(esPrepa);
    $('#grupo-semestre-prepa').hide();
    $('#grupo-carrera').toggle(esUni || esPosgrado);
    $('#grupo-carrera label').text(esPosgrado ? 'Programa de Posgrado' : 'Carrera / Licenciatura');
    $('#grupo-modalidad-txt').toggle(esUni);
    $('#grupo-generacion-posgrado').toggle(esPosgrado);
    $('#grupo-cuatrisem-uni, #grupo-generacion-uni, #grupo-inter-uni').hide();

    $('#concepto option[value="mensualidad"]').text(esPosgrado ? 'Materia' : 'Mensualidad');
    $('#sel-modalidad-prepa').val('').prop('disabled', false).removeClass('bg-light');
    $('#txt-semestre-prepa').val('');
    $('#modalidad_val').val('');
    if (esPosgrado) {
      $('#carrera').val('');
      $('#txt-generacion').val('');
      window._posgradoMaterias = [];
  window._uniTipoPeriodo   = null;
    }
    if (esUni) {
      $('#carrera').val('').prop('readonly', true).addClass('bg-light');
      $('#txt-modalidad').val('').prop('disabled', true).addClass('bg-light');
      $('#modalidad_val').val('');
      $('#txt-cuatrisem-uni').val('');
      $('#txt-generacion-uni').val('');
      window._uniTipoPeriodo = null;
    }
    $('#detalle_tramite_val').val('');
    actualizarPeriodo();
    if ($('#concepto').val() === 'tramite') {
      cargarTramites();
    }
  });

  // ── Modalidad prepa → campo oculto ─────────────────────────────
  $('#sel-modalidad-prepa').on('change', function () {
    $('#modalidad_val').val($(this).val());
  });

  // ── Modalidad uni → campo oculto ────────────────────────────────
  $('#txt-modalidad').on('change', function () {
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
            $('#modalidad_val').val(res.modalidad ?? '');
            $('#txt-generacion').val(res.generacion ?? '');
            window._posgradoMaterias = res.materias || [];
            if ($('#concepto').val() === 'mensualidad') {
              actualizarListaMaterias();
            }
          } else if (nivel !== 'prepa') {
            $('#txt-modalidad').val(res.modalidad ?? '');
            $('#modalidad_val').val(res.modalidad ?? '');
            if (nivel === 'uni') {
              window._uniTipoPeriodo = res.tipo_periodo ?? null;
              if (res.editable) {
                // id_grupo = 0: usar valores del registro del alumno si existen
                $('#grupo-cuatrisem-uni, #grupo-generacion-uni, #grupo-inter-uni').hide();

                // Carrera: bloquear si viene del registro, editable si no
                if (res.carrera) {
                  $('#carrera').prop('readonly', true).addClass('bg-light');
                } else {
                  $('#carrera').prop('readonly', false).removeClass('bg-light');
                }

                // Modalidad: bloquear si viene del registro, habilitar selector si no
                const modUniBase = (res.modalidad ?? '').trim();
                const $optUniBase = modUniBase
                  ? $('#txt-modalidad option').filter(function () {
                      return $(this).val().toLowerCase() === modUniBase.toLowerCase();
                    })
                  : $();
                if ($optUniBase.length) {
                  $('#txt-modalidad').val($optUniBase.val()).prop('disabled', true).addClass('bg-light');
                  $('#modalidad_val').val($optUniBase.val());
                } else {
                  $('#txt-modalidad').val('').prop('disabled', false).removeClass('bg-light');
                  $('#modalidad_val').val('');
                }
              } else {
                // Estándar: datos de grupos_modalidad, campos bloqueados
                const modUni  = (res.modalidad ?? '').toLowerCase();
                const $optUni = $('#txt-modalidad option').filter(function () {
                  return $(this).val().toLowerCase() === modUni;
                });
                const modUniVal = $optUni.length ? $optUni.val() : (res.modalidad ?? '');
                $('#txt-modalidad').val(modUniVal).prop('disabled', true).addClass('bg-light');
                $('#modalidad_val').val(modUniVal);
                $('#carrera').prop('readonly', true).addClass('bg-light');
                $('#txt-cuatrisem-uni').val(res.cuatrisem ?? '');
                $('#txt-generacion-uni').val(res.generacion ?? '');
                $('#txt-inter-uni').val(res.inter_label ?? '');
                $('#grupo-cuatrisem-uni, #grupo-generacion-uni, #grupo-inter-uni').fadeIn(300);
                if (window._uniTipoPeriodo && $('.btn-tipo-periodo:visible').length) {
                  $('.btn-tipo-periodo[data-val="' + window._uniTipoPeriodo + '"]').trigger('click');
                }
              }
            }
          } else {
            // nivel === 'prepa'
            if (res.editable === false) {
              // Datos de grupos_modalidad: bloquear select y mostrar semestre
              const modDb  = (res.modalidad ?? '').toLowerCase();
              const $match = $('#sel-modalidad-prepa option').filter(function () {
                return $(this).val().toLowerCase() === modDb;
              });
              const modVal = $match.length ? $match.val() : (res.modalidad ?? '');
              $('#sel-modalidad-prepa').val(modVal).prop('disabled', true).addClass('bg-light');
              $('#modalidad_val').val(modVal);
              $('#txt-semestre-prepa').val(res.semestre ?? '');
              $('#grupo-semestre-prepa').fadeIn(300);
            } else {
              // id_grupo = 0: selección manual
              $('#sel-modalidad-prepa').prop('disabled', false).removeClass('bg-light');
              $('#grupo-semestre-prepa').hide();
              $.get(BASE_URL + 'pagos/ultimo-pago', { num_control: numControl, concepto: 'reinscripcion' })
                .done(function (u) {
                  const mod = u.found && u.modalidad ? u.modalidad : null;
                  if (mod) {
                    $('#sel-modalidad-prepa').val(mod).trigger('change');
                  }
                });
            }
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
      $('#aviso-mensualidad-directa').remove();
      $('#grupo-modalidad-prepa, #grupo-semestre-prepa, #grupo-carrera, #grupo-modalidad-txt, #grupo-tramite, #grupo-generacion-posgrado, #grupo-cuatrisem-uni, #grupo-generacion-uni, #grupo-inter-uni').hide();
      $('#carrera, #txt-generacion, #txt-cuatrisem-uni, #txt-generacion-uni, #txt-inter-uni, #txt-semestre-prepa').val('');
      $('#carrera').prop('readonly', true).addClass('bg-light');
      $('#txt-modalidad').val('').prop('disabled', true).addClass('bg-light');
      $('#sel-modalidad-prepa').prop('disabled', false).removeClass('bg-light');
      window._posgradoMaterias  = [];
      window._uniTipoPeriodo    = null;
      $('#grupo-dinamico, #grupo-tipo-periodo, #grupo-bach-tipo, #grupo-selector-periodo, #grupo-materia-posgrado, #grupo-meses-mensualidad').hide();
      $('#nombre_alumno').prop('readonly', true).removeClass('is-valid is-invalid');
      $('#periodo_pago_val, #tipo_periodo_val, #detalle_tramite_val, #mes_inicio_ciclo_val').val('');
      $('#sel-modalidad-prepa').val('');
      $('#btn-periodo-grid').empty();
      $('.btn-tipo-periodo').removeClass('btn-primary active').addClass('btn-outline-primary');
      resetMesesMensualidad();
      $('#grid-materias-posgrado').html('<span class="text-muted small">Busca al alumno para ver las materias del programa.</span>');
      $('#badge-materia-seleccionada').hide();
    }, 10);
  });

  // ── Interceptar submit → Modal de confirmación ─────────────────
  $('#form-pago').on('submit', function (e) {
    e.preventDefault();

    const conceptoVal       = $('#concepto').val();
    const nivelVal          = $('#nivel').val();
    const esPosgradoMateria = nivelVal === 'posgrado' && conceptoVal === 'mensualidad';
    const esMensualidad     = conceptoVal === 'mensualidad' && nivelVal !== 'posgrado';

    if (esPosgradoMateria) {
      if (!$('#detalle_tramite_val').val()) {
        Swal.fire({ icon: 'warning', title: 'Falta la materia', text: 'Selecciona la materia a pagar.' });
        return;
      }
    } else if (esMensualidad) {
      if (getSelectedMonths().length === 0) {
        Swal.fire({ icon: 'warning', title: 'Falta el mes', text: 'Selecciona al menos un mes a pagar.' });
        return;
      }
      if (!$('#fecha_pago_real').val()) {
        Swal.fire({ icon: 'warning', title: 'Falta la fecha', text: 'Selecciona la fecha de pago.' });
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
    }

    const nombre   = $('#nombre_alumno').val().trim();
    const nivel    = $('#nivel option:selected').text().trim();
    const concepto = $('#concepto option:selected').text().trim();
    const tramite  = $('#detalle_tramite option:selected').text().trim();

    let montoRaw;
    const $inputsMes = $('#grid-montos-meses .monto-mes-input');
    if (esMensualidad && $inputsMes.length > 0) {
      montoRaw = 0;
      $inputsMes.each(function () { montoRaw += parseFloat($(this).val()) || 0; });
    } else {
      montoRaw = parseFloat($('#monto').val()) || 0;
    }
    const monto = montoRaw.toLocaleString('es-MX', { style: 'currency', currency: 'MXN' });

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

    // Si "cierra el adeudo" está marcado, no enviar num_abono → se guarda como pago completo
    const cierraMes = $('#chk-cierra-mes').is(':checked');
    if (cierraMes) $('#sel-num-abono').prop('disabled', true);
    const formData = $('#form-pago').serialize();
    if (cierraMes) $('#sel-num-abono').prop('disabled', false);

    $.ajax({
      url    : BASE_URL + 'pagos/registrar',
      method : 'POST',
      data   : formData,
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      success: function (res) {
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

  window._posgradoMaterias = [];

  // ── Mes inicio ciclo change → hidden field ───────────────────────
  $('#sel-mes-inicio').on('change', function () {
    $('#mes_inicio_ciclo_val').val($(this).val());
  });

  // ── Helpers de meses ────────────────────────────────────────────
  function getSelectedMonths() {
    const result = [];
    $('input.chk-mes-mensualidad:checked').each(function () {
      result.push({ mes: parseInt($(this).val()), nombre: $(this).data('nombre') });
    });
    return result;
  }

  function actualizarTablaMontos() {
    const sel    = getSelectedMonths();
    const $wrap  = $('#tabla-montos-meses');
    const $grid  = $('#grid-montos-meses');
    const anio   = $('#sel-anio-mensualidad').val() || new Date().getFullYear();
    const precio = parseFloat($('#monto').val()) || 0;

    if (sel.length < 2) {
      $wrap.hide();
      $grid.empty();
      $('#label-monto').html('Monto <span class="text-danger">*</span>');
      $('#hint-monto-mes').hide();
      return;
    }

    $('#label-monto').html('Precio por mensualidad <span class="text-danger">*</span>');
    $('#hint-monto-mes').show();

    // Preservar valores ingresados manualmente
    const prevVals = {};
    $grid.find('.monto-mes-input[data-manual]').each(function () {
      prevVals[$(this).data('mes')] = $(this).val();
    });

    $grid.empty();
    const $tbl = $('<table>').addClass('table table-sm table-bordered mb-1').css('width', 'auto');
    sel.forEach(function (m) {
      const val = prevVals[m.mes] !== undefined ? prevVals[m.mes] : (precio > 0 ? precio.toFixed(2) : '');
      const $input = $('<input type="number">')
        .addClass('form-control form-control-sm monto-mes-input')
        .attr({ name: 'montos_pago[]', step: '0.01', min: '0.01', 'data-mes': m.mes })
        .val(val)
        .css('width', '110px');
      if (prevVals[m.mes] !== undefined) $input.attr('data-manual', '1');

      const $ig = $('<div>').addClass('input-group input-group-sm')
        .append($('<div>').addClass('input-group-prepend').append($('<span>').addClass('input-group-text').text('$')))
        .append($input);

      $tbl.append(
        $('<tr>')
          .append($('<td>').addClass('align-middle py-1 pr-3 small font-weight-bold').css('white-space','nowrap').text(MESES[m.mes - 1] + ' ' + anio))
          .append($('<td>').addClass('py-1').append($ig))
      );
    });
    $grid.append($tbl);
    $wrap.show();
    actualizarTotalMeses();
  }

  function actualizarTotalMeses() {
    const $inputs = $('#grid-montos-meses .monto-mes-input');
    if ($inputs.length === 0) return;
    let total = 0;
    $inputs.each(function () { total += parseFloat($(this).val()) || 0; });
    $('#total-meses-display').text('Total: ' + total.toLocaleString('es-MX', { style: 'currency', currency: 'MXN' }));
  }

  function actualizarEstadoMeses() {
    const seleccionados = getSelectedMonths();
    const count         = seleccionados.length;
    const anio          = $('#sel-anio-mensualidad').val() || new Date().getFullYear();

    if (count > 0) {
      const txt = seleccionados.map(function (m) {
        return MESES[m.mes - 1].substring(0, 3) + ' ' + anio;
      }).join(', ');
      $('#badge-mes-seleccionado').text(txt).show();
    } else {
      $('#badge-mes-seleccionado').hide();
    }

    actualizarTablaMontos();

    // Abono: solo disponible con exactamente 1 mes seleccionado
    $('#grupo-abono').toggle(count === 1);
    if (count !== 1) {
      $('#chk-es-abono').prop('checked', false);
      $('#sel-num-abono').prop('disabled', true);
      $('#sel-num-abono-wrapper').hide();
      $('#chk-cierra-mes').prop('checked', false);
      $('#wrap-cierra-mes').hide();
      $('#resumen-abonos-previos').hide().empty();
    } else {
      // Auto-activar abono si el mes tiene abonos parciales previos
      const $chkMes = $('input.chk-mes-mensualidad:checked');
      const status  = $chkMes.data('status');
      const abonos  = parseInt($chkMes.data('abonos')) || 0;
      if (status === 'parcial') {
        if (!$('#chk-es-abono').is(':checked')) {
          $('#chk-es-abono').prop('checked', true).trigger('change');
        }
        // Sugerir el siguiente número de abono
        const siguiente = Math.min(abonos + 1, 4);
        $('#sel-num-abono').val(siguiente);

        // Mostrar resumen de abonos previos
        const detalle = JSON.parse($chkMes.attr('data-detalle') || '[]');
        if (detalle.length > 0) {
          let totalPagado = 0;
          let html = '<p class="mb-1 small font-weight-bold" style="color:#856404"><i class="fas fa-history mr-1"></i>Abonos ya registrados:</p>';
          detalle.forEach(function (a) {
            totalPagado += a.monto;
            const label = a.num_abono !== null ? 'Abono ' + a.num_abono : 'Pago completo';
            html += '<div class="d-flex justify-content-between small">' +
              '<span class="text-muted">' + label + ' <small class="text-muted">(' + (a.fecha || '') + ')</small></span>' +
              '<span class="font-weight-bold">$' + a.monto.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',') + '</span>' +
              '</div>';
          });
          html += '<div class="d-flex justify-content-between small font-weight-bold border-top mt-1 pt-1">' +
            '<span>Total pagado hasta ahora:</span>' +
            '<span class="text-success">$' + totalPagado.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',') + '</span>' +
            '</div>';
          $('#resumen-abonos-previos').html(html).show();
        } else {
          $('#resumen-abonos-previos').hide().empty();
        }
      } else {
        $('#resumen-abonos-previos').hide().empty();
      }
    }
  }

  function resetMesesMensualidad() {
    $('#grid-meses-mensualidad').html('<span class="text-muted small">Ingresa el número de control para ver el estado de los meses.</span>');
    $('#badge-mes-seleccionado').hide();
    $('#grupo-abono').hide();
    $('#chk-es-abono').prop('checked', false);
    $('#sel-num-abono').prop('disabled', true);
    $('#sel-num-abono-wrapper').hide();
    $('#chk-cierra-mes').prop('checked', false);
    $('#wrap-cierra-mes').hide();
    $('#resumen-abonos-previos').hide().empty();
    $('#tabla-montos-meses').hide();
    $('#grid-montos-meses').empty();
    $('#label-monto').html('Monto <span class="text-danger">*</span>');
    $('#hint-monto-mes').hide();
    $('#aviso-mensualidad-directa').remove();
  }

  // Propagar precio global a inputs individuales no editados manualmente
  $('#monto').on('input', function () {
    const precio = parseFloat($(this).val()) || 0;
    $('.monto-mes-input:not([data-manual])').val(precio > 0 ? precio.toFixed(2) : '');
    actualizarTotalMeses();
  });

  // Marcar input individual como editado manualmente
  $(document).on('input', '.monto-mes-input', function () {
    $(this).attr('data-manual', '1');
    actualizarTotalMeses();
  });

  // ── Selector de año ──────────────────────────────────────────────
  function inicializarAnioMensualidad() {
    const $sel      = $('#sel-anio-mensualidad');
    const anioActual = new Date().getFullYear();
    $sel.empty();
    for (let y = anioActual; y >= anioActual - 5; y--) {
      $sel.append($('<option>').val(y).text(y));
    }
  }

  // Recargar meses al cambiar año
  $(document).on('change', '#sel-anio-mensualidad', function () {
    resetMesesMensualidad();
    cargarMesesMensualidad();
  });

  // Toggle abono
  $(document).on('change', '#chk-es-abono', function () {
    const checked = $(this).is(':checked');
    $('#sel-num-abono-wrapper').toggle(checked);
    $('#sel-num-abono').prop('disabled', !checked);
    if (!checked) {
      $('#sel-num-abono').val('1');
      $('#chk-cierra-mes').prop('checked', false);
      $('#wrap-cierra-mes').hide();
    } else {
      // "Cierra el adeudo" solo aplica cuando el mes ya tiene abonos previos
      const status = $('input.chk-mes-mensualidad:checked').data('status');
      if (status === 'parcial') {
        $('#wrap-cierra-mes').show();
      }
    }
  });

  // ── Carga estado de meses para mensualidad ───────────────────────
  function cargarMesesMensualidad() {
    const numControl = $('#num_control').val().trim();
    const nivel      = $('#nivel').val();
    const anio       = $('#sel-anio-mensualidad').val() || '';
    const $grid      = $('#grid-meses-mensualidad');

    if (!numControl || !nivel) {
      $grid.html('<span class="text-muted small">Ingresa el número de control para ver el estado de los meses.</span>');
      return;
    }

    $grid.html('<span class="text-muted"><i class="fas fa-spinner fa-spin mr-1"></i> Cargando...</span>');

    $.get(BASE_URL + 'pagos/estado-mensualidades', { num_control: numControl, nivel: nivel, anio: anio })
      .done(function (res) {
        $('#aviso-mensualidad-directa').remove();
        if (!res.meses || res.meses.length === 0) {
          $grid.html('<span class="text-warning small"><i class="fas fa-exclamation-triangle mr-1"></i> No se pudo cargar el estado de los meses.</span>');
          return;
        }
        if (res.directa) {
          $grid.before('<div id="aviso-mensualidad-directa" class="alert alert-info py-2 px-3 mb-2 small"><i class="fas fa-info-circle mr-1"></i> No se detecta pago inicial de ciclo. Se procederá al cobro de mensualidad directa.</div>');
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
      const abrev = m.nombre.substring(0, 3);

      if (m.status === 'pagado') {
        // Deshabilitado: pago completo
        $grid.append(
          $('<span>').addClass('btn btn-success btn-sm mr-1 mb-1')
            .css({'min-width':'68px', 'cursor':'not-allowed', 'opacity':'.85'})
            .html('<i class="fas fa-check mr-1"></i>' + abrev)
        );
        return;
      }

      if (m.status === 'na') {
        $grid.append(
          $('<span>').addClass('btn btn-outline-secondary btn-sm mr-1 mb-1')
            .css({'min-width':'68px', 'cursor':'not-allowed', 'opacity':'.5'})
            .html('<i class="fas fa-minus mr-1"></i>' + abrev)
        );
        return;
      }

      // Seleccionable: pendiente | parcial | futuro
      let btnCls, icon;
      if (m.status === 'pendiente') {
        btnCls = 'btn-danger';
        icon   = '';
      } else if (m.status === 'parcial') {
        btnCls = 'btn-warning';
        icon   = '<i class="fas fa-exclamation-triangle mr-1" style="font-size:.75em"></i>';
      } else {
        btnCls = 'btn-secondary';
        icon   = '';
      }

      const $label = $('<label>')
        .addClass('btn ' + btnCls + ' btn-sm mr-1 mb-1')
        .css({'min-width':'68px', 'cursor':'pointer', 'user-select':'none', 'margin-bottom':'4px'})
        .attr('title', m.nombre + (m.status === 'parcial' ? ' — tiene abonos parciales' : ''));

      const $chk = $('<input type="checkbox">')
        .addClass('chk-mes-mensualidad')
        .attr('name', 'meses_pago[]')
        .val(m.mes)
        .attr('data-nombre', m.nombre)
        .attr('data-original', btnCls)
        .attr('data-status', m.status)
        .attr('data-abonos', m.abonos || 0)
        .attr('data-detalle', JSON.stringify(m.abonos_detalle || []))
        .css({'position':'absolute','opacity':'0','width':'0','height':'0'});

      $label.append($chk).append(icon + abrev);
      $grid.append($label);
    });
  }

  // ── Malla de materias posgrado ──────────────────────────────────
  function actualizarListaMaterias() {
    const lista = window._posgradoMaterias || [];
    const $grid = $('#grid-materias-posgrado');
    $grid.empty();
    $('#detalle_tramite_val').val('');
    $('#badge-materia-seleccionada').hide();

    if (lista.length === 0) {
      $grid.css('display', '');
      $grid.html('<span class="text-muted small">Sin materias disponibles para este programa.</span>');
      return;
    }

    $grid.css({
      'display'              : 'grid',
      'grid-template-columns': 'repeat(6, 1fr)',
      'gap'                  : '8px',
    });

    lista.forEach(function (m) {
      const pagada = !!m.pagada;
      const $btn   = $('<button type="button">')
        .addClass('btn btn-sm btn-materia-posgrado' + (pagada ? ' btn-success' : ' btn-outline-secondary'))
        .css({
          'height'         : '76px',
          'width'          : '100%',
          'display'        : 'flex',
          'flex-direction' : 'column',
          'align-items'    : 'center',
          'justify-content': 'center',
          'padding'        : '6px 8px',
          'overflow'       : 'hidden',
        })
        .attr('data-nombre', m.nombre)
        .attr('data-clave', m.clave || '')
        .prop('disabled', pagada)
        .html(
          (pagada
            ? '<i class="fas fa-check-circle mb-1" style="font-size:.8rem"></i>'
            : '<i class="fas fa-clock mb-1" style="font-size:.8rem; opacity:.5"></i>') +
          '<span style="font-size:.68rem; font-weight:600; line-height:1.25; text-align:center;' +
                'display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">' +
            escHtml(m.nombre) + '</span>' +
          (m.clave
            ? '<span style="font-size:.63rem; opacity:.6; margin-top:2px; text-align:center">' +
                escHtml(m.clave) + '</span>'
            : '')
        );
      $grid.append($btn);
    });
  }

  function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  $(document).on('click', '.btn-materia-posgrado:not(:disabled)', function () {
    const nombre = $(this).data('nombre');
    const clave  = $(this).data('clave');
    const activo = $(this).hasClass('btn-primary');

    $('.btn-materia-posgrado:not(:disabled)').removeClass('btn-primary').addClass('btn-outline-secondary');

    if (activo) {
      $('#detalle_tramite_val').val('');
      $('#badge-materia-seleccionada').hide();
    } else {
      $(this).removeClass('btn-outline-secondary').addClass('btn-primary');
      $('#detalle_tramite_val').val(nombre);
      const label = clave ? nombre + ' (' + clave + ')' : nombre;
      $('#badge-materia-seleccionada').text(label).show();
    }
  });

  // ── Periodo de pago ─────────────────────────────────────────────
  function actualizarPeriodo() {
    const nivel    = $('#nivel').val();
    const concepto = $('#concepto').val();

    $('#grupo-dinamico').hide();
    $('#grupo-tipo-periodo, #grupo-bach-tipo, #grupo-selector-periodo, #grupo-materia-posgrado, #grupo-mes-inicio, #grupo-meses-mensualidad').hide();
    $('#periodo_pago_val, #tipo_periodo_val, #detalle_tramite_val').val('');
    $('#grid-materias-posgrado').html('<span class="text-muted small">Busca al alumno para ver las materias del programa.</span>');
    $('#badge-materia-seleccionada').hide();
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
      inicializarAnioMensualidad();
      resetMesesMensualidad();
      $('#grupo-meses-mensualidad').show();
      cargarMesesMensualidad();

    } else if (concepto === 'inscripcion') {
      $('#grupo-tipo-periodo').show();
      if (nivel === 'uni' && window._uniTipoPeriodo) {
        $('.btn-tipo-periodo[data-val="' + window._uniTipoPeriodo + '"]').trigger('click');
      }

      if (nivel === 'prepa') {
        $('.btn-bach-tipo').first().addClass('active');
        $('#grupo-bach-tipo').show();
        $('#detalle_tramite_val').val('Semestral');
        $('#label-selector-periodo').html('Semestral <span class="text-danger">*</span>');
      } else {
        $('#label-selector-periodo').html('Periodo <span class="text-danger">*</span>');
      }

      const mesActual = new Date().getMonth() + 1;
      $('#sel-mes-inicio').val(mesActual);
      $('#mes_inicio_ciclo_val').val(mesActual);
      $('#grupo-mes-inicio').show();

      generarBotonesGrid(1, 1, 'num');
      $('#grupo-selector-periodo').show();

    } else if (concepto === 'reinscripcion') {
      $('#grupo-tipo-periodo').show();
      if (nivel === 'uni' && window._uniTipoPeriodo) {
        $('.btn-tipo-periodo[data-val="' + window._uniTipoPeriodo + '"]').trigger('click');
      }

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

      const mesActualR = new Date().getMonth() + 1;
      $('#sel-mes-inicio').val(mesActualR);
      $('#mes_inicio_ciclo_val').val(mesActualR);
      $('#grupo-mes-inicio').show();

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
    const concepto = $('#concepto').val();
    const nivelVal = $('#nivel').val();

    if (nivelVal === 'posgrado' && concepto === 'mensualidad') return '';

    if (concepto === 'mensualidad') {
      const anio  = $('#sel-anio-mensualidad').val() || new Date().getFullYear();
      const meses = getSelectedMonths();
      if (meses.length === 0) return '';
      let txt = meses.map(function (m) { return MESES[m.mes - 1]; }).join(', ') + ' ' + anio;
      if ($('#chk-es-abono').is(':checked') && !$('#chk-cierra-mes').is(':checked') && $('#sel-num-abono').val()) {
        txt += ' — Abono ' + $('#sel-num-abono').val();
      }
      return txt;
    }

    const periodoNum = $('#periodo_pago_val').val();
    const tipo       = $('#tipo_periodo_val').val();
    if (!periodoNum) return '';
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
          const $btn = $('#btn-periodo-grid .btn-num-periodo[data-val="' + res.sugerido + '"]');
          if ($btn.length) {
            $btn.trigger('click');
            mostrarBadgeSugerido('Periodo ' + res.sugerido);
          }

          if (res.tipo_periodo) {
            $('.btn-tipo-periodo[data-val="' + res.tipo_periodo + '"]').trigger('click');
          }

          if (res.plan_bach && $('#nivel').val() === 'prepa') {
            $('.btn-bach-tipo[data-val="' + res.plan_bach + '"]').trigger('click');
          }
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

  // ── Checkboxes de meses ─────────────────────────────────────────
  $(document).on('change', '.chk-mes-mensualidad', function () {
    const $label   = $(this).parent();
    const original = $(this).data('original');
    if ($(this).is(':checked')) {
      $label.removeClass(original).addClass('btn-primary active');
    } else {
      $label.removeClass('btn-primary active').addClass(original);
    }
    actualizarEstadoMeses();
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
<?= $this->endSection() ?>