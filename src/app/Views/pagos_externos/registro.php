<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Pagos Externos<?= $this->endSection() ?>

<?= $this->section('head_extra') ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

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

              <div class="form-group">
                <label for="nombre_cliente">
                  Nombre del Aspirante / Cliente <span class="text-danger">*</span>
                </label>
                <input type="text" name="nombre_cliente" id="nombre_cliente"
                       class="form-control" placeholder="Nombre completo" required>
              </div>

              <div class="row">
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
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="modalidad">Modalidad</label>
                    <select name="modalidad" id="modalidad" class="form-control">
                      <option value="">— Opcional —</option>
                      <option value="Escolarizado">Escolarizado</option>
                      <option value="Sabatino">Sabatino</option>
                      <option value="Dominical">Dominical</option>
                      <option value="En línea">En línea</option>
                      <option value="Intercuatrimestral">Intercuatrimestral</option>
                      <option value="Nocturno">Nocturno</option>
                      <option value="Mixto">Mixto</option>
                    </select>
                  </div>
                </div>
              </div>

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

              <div class="form-group" id="grupo-concepto-otro" style="display:none">
                <label for="concepto_otro">Especifica el concepto <span class="text-danger">*</span></label>
                <input type="text" name="concepto_otro" id="concepto_otro"
                       class="form-control" placeholder="Ej: Derecho de examen extraordinario">
              </div>

              <div class="row">
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

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script>
const BASE_URL = '<?= base_url() ?>';

$('#concepto').on('change', function () {
  const esOtro = $(this).val() === 'otro';
  $('#grupo-concepto-otro').toggle(esOtro);
  $('#concepto_otro').prop('required', esOtro);
  if (!esOtro) $('#concepto_otro').val('');
});

$('#form-externo').on('submit', function (e) {
  e.preventDefault();

  const nombre      = $('#nombre_cliente').val().trim();
  const nivel       = $('#nivel').val();
  const concepto    = $('#concepto').val();
  const conceptoOtro = $('#concepto_otro').val().trim();
  const monto       = $('#monto').val();

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

  $.ajax({
    url : BASE_URL + 'pagos-externos/registrar',
    type: 'POST',
    data: $(this).serialize(),
    success: function (res) {
      if (res.success) {
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
<?= $this->endSection() ?>