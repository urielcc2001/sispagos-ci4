<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Reportes<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-chart-bar mr-2 text-primary"></i>Reportes Financieros</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Reportes</li>
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

    <?php if ($rol === 'cajero'): ?>
      <div class="alert alert-info">
        <i class="fas fa-lock mr-2"></i>
        Estás viendo únicamente tus propios registros.
      </div>
    <?php endif; ?>

    <?php
    $conceptoLabels = [
        'inscripcion'   => 'Inscripción',
        'reinscripcion' => 'Reinscripción',
        'mensualidad'   => 'Mensualidad',
        'tramite'       => 'Trámite',
    ];
    $detalleLabels = [
        'constancia'     => 'Constancia',
        'constancia_ext' => 'Constancia Ext.',
        'historial'      => 'Historial',
        'gafete'         => 'Gafete',
    ];
    $nivelLabels = [
        'uni'      => 'Universidad',
        'prepa'    => 'Bachillerato',
        'posgrado' => 'Posgrado',
    ];

    $totalAlumnos  = array_sum(array_column(array_filter($pagos, fn($p) => $p['tipo_pago'] === 'alumno'), 'monto'));
    $totalExternos = array_sum(array_column(array_filter($pagos, fn($p) => $p['tipo_pago'] === 'externo'), 'monto'));
    $cntAlumnos    = count(array_filter($pagos, fn($p) => $p['tipo_pago'] === 'alumno'));
    $cntExternos   = count(array_filter($pagos, fn($p) => $p['tipo_pago'] === 'externo'));
    $origenActual  = $filtros['origen'] ?? '';
    ?>

    <!-- ── Barra de Filtros ──────────────────────────────────── -->
    <div class="card card-outline card-primary">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter mr-2"></i>Filtros</h3>
      </div>
      <div class="card-body">
        <form method="get" action="<?= base_url('admin/reportes') ?>">

          <div class="row align-items-end">

            <div class="col-md-3">
              <div class="form-group mb-0">
                <label class="text-xs text-uppercase text-muted font-weight-bold">Periodo rápido</label>
                <div class="btn-group d-flex" role="group">
                  <a href="<?= base_url('admin/reportes?periodo=hoy') ?>"
                     class="btn btn-sm <?= ($filtros['periodo'] ?? '') === 'hoy' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                    Hoy
                  </a>
                  <a href="<?= base_url('admin/reportes?periodo=semana') ?>"
                     class="btn btn-sm <?= ($filtros['periodo'] ?? '') === 'semana' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                    Semana
                  </a>
                  <a href="<?= base_url('admin/reportes?periodo=mes') ?>"
                     class="btn btn-sm <?= ($filtros['periodo'] ?? '') === 'mes' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                    Mes
                  </a>
                </div>
              </div>
            </div>

            <div class="col-md-2">
              <div class="form-group mb-0">
                <label class="text-xs text-uppercase text-muted font-weight-bold">Fecha inicio</label>
                <input type="date" name="fecha_inicio" class="form-control form-control-sm"
                       value="<?= esc($filtros['fechaInicio'] ?? '') ?>">
              </div>
            </div>
            <div class="col-md-2">
              <div class="form-group mb-0">
                <label class="text-xs text-uppercase text-muted font-weight-bold">Fecha fin</label>
                <input type="date" name="fecha_fin" class="form-control form-control-sm"
                       value="<?= esc($filtros['fechaFin'] ?? '') ?>">
              </div>
            </div>

            <?php if ($rol === 'admin'): ?>
            <div class="col-md-2">
              <div class="form-group mb-0">
                <label class="text-xs text-uppercase text-muted font-weight-bold">Cajero</label>
                <select name="id_cajero" class="form-control form-control-sm">
                  <option value="">— Todos —</option>
                  <?php foreach ($cajeros as $c): ?>
                    <option value="<?= $c['id'] ?>"
                      <?= ($filtros['idCajero'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                      <?= esc($c['nombre']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <?php endif; ?>

            <div class="col-md-2">
              <div class="form-group mb-0">
                <label class="text-xs text-uppercase text-muted font-weight-bold">Nivel</label>
                <select name="nivel" class="form-control form-control-sm">
                  <option value="">— Todos —</option>
                  <option value="uni"      <?= ($filtros['nivel'] ?? '') === 'uni'      ? 'selected' : '' ?>>Universidad</option>
                  <option value="prepa"    <?= ($filtros['nivel'] ?? '') === 'prepa'    ? 'selected' : '' ?>>Bachillerato</option>
                  <option value="posgrado" <?= ($filtros['nivel'] ?? '') === 'posgrado' ? 'selected' : '' ?>>Posgrado</option>
                </select>
              </div>
            </div>

            <div class="col-md-2">
              <div class="form-group mb-0">
                <label class="text-xs text-uppercase text-muted font-weight-bold">Método de Pago</label>
                <select name="metodo_pago" class="form-control form-control-sm">
                  <option value="">— Todos —</option>
                  <option value="Efectivo"      <?= ($filtros['metodoPago'] ?? '') === 'Efectivo'      ? 'selected' : '' ?>>Efectivo</option>
                  <option value="Transferencia" <?= ($filtros['metodoPago'] ?? '') === 'Transferencia' ? 'selected' : '' ?>>Transferencia</option>
                </select>
              </div>
            </div>

            <div class="col-md-1">
              <button type="submit" class="btn btn-primary btn-sm btn-block">
                <i class="fas fa-search"></i>
              </button>
            </div>

          </div><!-- /.row -->

          <div class="row mt-2">
            <div class="col-md-4">
              <div class="form-group mb-0">
                <label class="text-xs text-uppercase text-muted font-weight-bold">
                  <i class="fas fa-filter mr-1"></i>Origen de Pago
                </label>
                <select name="origen" class="form-control form-control-sm">
                  <option value="" <?= $origenActual === '' ? 'selected' : '' ?>>
                    Todos (Alumnos + Externos / Aspirantes)
                  </option>
                  <option value="alumnos" <?= $origenActual === 'alumnos' ? 'selected' : '' ?>>
                    Solo Alumnos
                  </option>
                  <option value="externos" <?= $origenActual === 'externos' ? 'selected' : '' ?>>
                    Solo Externos / Aspirantes
                  </option>
                </select>
              </div>
            </div>
          </div>

        </form>
      </div>
    </div>
    <!-- /filtros -->

    <!-- ── Resumen Rápido ─────────────────────────────────────── -->
    <div class="row">
      <div class="col-lg-4 col-md-6">
        <div class="small-box bg-success">
          <div class="inner">
            <h3 style="font-size:2rem;">$<?= number_format((float) $totalGeneral, 2) ?></h3>
            <p>
              Total Recaudado en el Periodo
              <?php if ($totalGeneral > 0): ?>
                <br><small style="font-size:0.75rem; opacity:0.85;">
                  Efectivo: $<?= number_format((float) $totalEfectivo, 2) ?>
                  &nbsp;|&nbsp;
                  Transferencia: $<?= number_format((float) $totalTransferencia, 2) ?>
                </small>
              <?php endif; ?>
            </p>
          </div>
          <div class="icon"><i class="fas fa-dollar-sign"></i></div>
        </div>
      </div>

      <div class="col-lg-4 col-md-6">
        <div class="small-box bg-info">
          <div class="inner">
            <h3><?= count($pagos) ?></h3>
            <p>
              Pagos Encontrados
              <?php if ($origenActual === '' && ($cntAlumnos > 0 || $cntExternos > 0)): ?>
                <br><small style="font-size:0.75rem; opacity:0.85;">
                  Alumnos: <?= $cntAlumnos ?>
                  &nbsp;|&nbsp;
                  Externos: <?= $cntExternos ?>
                </small>
              <?php endif; ?>
            </p>
          </div>
          <div class="icon"><i class="fas fa-file-invoice-dollar"></i></div>
        </div>
      </div>

      <div class="col-lg-4 col-md-12 d-flex align-items-center justify-content-end">
        <?php
        $exportParams = array_filter([
            'fecha_inicio' => $filtros['fechaInicio'] ?? '',
            'fecha_fin'    => $filtros['fechaFin']    ?? '',
            'id_cajero'    => $rol === 'admin' ? ($filtros['idCajero'] ?? '') : '',
            'nivel'        => $filtros['nivel']       ?? '',
            'origen'       => $filtros['origen']      ?? '',
            'metodo_pago'  => $filtros['metodoPago']  ?? '',
        ]);
        $exportQuery = $exportParams ? '?' . http_build_query($exportParams) : '';
        ?>
        <div>
          <a href="<?= base_url('admin/exportar/csv') . $exportQuery ?>"
             class="btn btn-success btn-lg"
             title="Descargar CSV (Excel)">
            <i class="fas fa-file-csv mr-2"></i>Descargar CSV
          </a>
          <a href="<?= base_url('admin/exportar/pdf') . $exportQuery ?>"
             class="btn btn-danger btn-lg ml-2"
             title="Descargar PDF">
            <i class="fas fa-file-pdf mr-2"></i>Descargar PDF
          </a>
        </div>
      </div>
    </div>
    <!-- /resumen -->

    <!-- ── Tabla de Resultados ──────────────────────────────── -->
    <div class="card card-outline card-primary">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-table mr-2"></i>Resultados
        </h3>
      </div>
      <div class="card-body p-0">
        <table class="table table-striped table-hover table-sm mb-0">
          <thead class="thead-light">
            <tr>
              <th>Folio</th>
              <th>Tipo</th>
              <th>Fecha / Hora</th>
              <th>Nombre</th>
              <th>Nivel</th>
              <th>Concepto</th>
              <th>Cajero</th>
              <th class="text-right">Efectivo</th>
              <th class="text-right">Transferencia</th>
              <th class="text-center">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($pagos)): ?>
              <tr>
                <td colspan="10" class="text-center text-muted py-5">
                  <i class="fas fa-search fa-2x mb-2 d-block"></i>
                  No se encontraron pagos con los filtros seleccionados.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($pagos as $p): ?>
              <?php
                if ($p['tipo_pago'] === 'alumno') {
                    $concepto = $conceptoLabels[$p['concepto']] ?? $p['concepto'];
                    if (($p['nivel'] ?? '') === 'posgrado' && $p['concepto'] === 'mensualidad') {
                        $concepto = mb_stripos($p['modalidad'] ?? '', 'doctor') !== false ? 'Materia D' : 'Materia M';
                    } elseif ($p['concepto'] === 'tramite' && ! empty($p['detalle_tramite'])) {
                        $concepto .= ' / ' . ($detalleLabels[$p['detalle_tramite']] ?? $p['detalle_tramite']);
                    }
                } else {
                    $concepto = $p['concepto'];
                }
                $nivelLabel = ! empty($p['nivel']) ? ($nivelLabels[$p['nivel']] ?? $p['nivel']) : '—';
                $nivelBadge = $p['nivel'] === 'uni' ? 'primary' : ($p['nivel'] === 'prepa' ? 'warning' : 'secondary');
              ?>
              <tr>
                <td>
                  <code class="text-primary" style="font-size:0.78rem;">
                    <?= esc($p['folio_digital'] ?? '—') ?>
                  </code>
                </td>
                <td>
                  <?php if ($p['tipo_pago'] === 'alumno'): ?>
                    <span class="badge badge-primary">Alumno</span>
                  <?php else: ?>
                    <span class="badge badge-warning">Externo</span>
                  <?php endif; ?>
                </td>
                <td class="text-nowrap text-muted" style="font-size:0.82rem;">
                  <?= date('d/m/Y H:i', strtotime($p['created_at'])) ?>
                </td>
                <td><?= esc($p['nombre'] ?? '—') ?></td>
                <td>
                  <?php if (! empty($p['nivel'])): ?>
                    <span class="badge badge-<?= $nivelBadge ?>">
                      <?= esc($nivelLabel) ?>
                    </span>
                  <?php else: ?>
                    <span class="text-muted">—</span>
                  <?php endif; ?>
                </td>
                <td><?= esc($concepto) ?></td>
                <td><?= esc($p['nombre_cajero'] ?? 'N/D') ?></td>
                <td class="text-right font-weight-bold">
                  <?= ($p['metodo_pago'] ?? '') !== 'Transferencia' ? '$' . number_format((float) $p['monto'], 2) : '<span class="text-muted">—</span>' ?>
                </td>
                <td class="text-right font-weight-bold">
                  <?= ($p['metodo_pago'] ?? '') === 'Transferencia' ? '$' . number_format((float) $p['monto'], 2) : '<span class="text-muted">—</span>' ?>
                </td>
                <td class="text-center text-nowrap">
                  <?php if ($p['tipo_pago'] === 'alumno'): ?>
                    <?php if (! empty($p['folio_digital'])): ?>
                      <a href="<?= base_url('pagos/comprobante/' . esc($p['folio_digital'])) ?>"
                         target="_blank"
                         class="btn btn-xs btn-outline-secondary"
                         title="Reimprimir comprobante">
                        <i class="fas fa-print"></i>
                      </a>
                    <?php endif; ?>
                    <?php if ($rol === 'admin'): ?>
                    <a href="<?= base_url('admin/pagos/' . $p['id'] . '/editar') ?>"
                       class="btn btn-xs btn-outline-warning"
                       title="Editar pago">
                      <i class="fas fa-pencil-alt"></i>
                    </a>
                    <button type="button"
                            class="btn btn-xs btn-outline-danger btn-eliminar"
                            data-id="<?= $p['id'] ?>"
                            data-folio="<?= esc($p['folio_digital'] ?? $p['id']) ?>"
                            title="Eliminar pago">
                      <i class="fas fa-trash-alt"></i>
                    </button>
                    <?php endif; ?>
                  <?php else: ?>
                    <?php if (! empty($p['folio_digital'])): ?>
                      <a href="<?= base_url('pagos-externos/comprobante/' . esc($p['folio_digital'])) ?>"
                         target="_blank"
                         class="btn btn-xs btn-outline-secondary"
                         title="Reimprimir recibo">
                        <i class="fas fa-print"></i>
                      </a>
                    <?php endif; ?>
                    <?php if ($rol === 'admin'): ?>
                    <a href="<?= base_url('admin/pagos-externos/' . $p['id'] . '/editar') ?>"
                       class="btn btn-xs btn-outline-warning"
                       title="Editar">
                      <i class="fas fa-pencil-alt"></i>
                    </a>
                    <button type="button"
                            class="btn btn-xs btn-outline-danger btn-eliminar-externo"
                            data-id="<?= $p['id'] ?>"
                            data-folio="<?= esc($p['folio_digital'] ?? $p['id']) ?>"
                            title="Eliminar">
                      <i class="fas fa-trash-alt"></i>
                    </button>
                    <?php endif; ?>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
              <tr class="font-weight-bold" style="background:#d4edda;">
                <td colspan="7" class="text-right">Total Efectivo:</td>
                <td class="text-right text-success">$<?= number_format((float) $totalEfectivo, 2) ?></td>
                <td class="text-right text-muted">—</td>
                <td></td>
              </tr>
              <tr class="font-weight-bold" style="background:#d1ecf1;">
                <td colspan="7" class="text-right">Total Transferencia:</td>
                <td class="text-right text-muted">—</td>
                <td class="text-right text-info">$<?= number_format((float) $totalTransferencia, 2) ?></td>
                <td></td>
              </tr>
              <tr class="font-weight-bold" style="background:#003087; color:white;">
                <td colspan="7" class="text-right">TOTAL:</td>
                <td colspan="2" class="text-right" style="font-size:1.05rem;">$<?= number_format((float) $totalGeneral, 2) ?></td>
                <td></td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <!-- /tabla -->

  </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('modals') ?>

<!-- Modal Eliminar Pago Alumno -->
<div class="modal fade" id="modal-eliminar" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title text-white">
          <i class="fas fa-exclamation-triangle mr-2"></i>Confirmar Eliminación
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>¿Estás seguro de eliminar el pago <strong id="folio-a-eliminar"></strong>?</p>
        <p class="text-danger mb-0"><i class="fas fa-exclamation-circle mr-1"></i>Esta acción no se puede deshacer.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">
          <i class="fas fa-times mr-1"></i> Cancelar
        </button>
        <form id="form-eliminar" method="post" style="display:inline">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-danger">
            <i class="fas fa-trash-alt mr-1"></i> Sí, eliminar
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal Eliminar Pago Externo -->
<div class="modal fade" id="modal-eliminar-externo" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title text-white">
          <i class="fas fa-exclamation-triangle mr-2"></i>Confirmar Eliminación
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>¿Estás seguro de eliminar el pago externo <strong id="folio-externo-a-eliminar"></strong>?</p>
        <p class="text-danger mb-0"><i class="fas fa-exclamation-circle mr-1"></i>Esta acción no se puede deshacer.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">
          <i class="fas fa-times mr-1"></i> Cancelar
        </button>
        <form id="form-eliminar-externo" method="post" style="display:inline">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-danger">
            <i class="fas fa-trash-alt mr-1"></i> Sí, eliminar
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const BASE_URL = '<?= base_url() ?>';
$(function () {
  $(document).on('click', '.btn-eliminar', function () {
    const id    = $(this).data('id');
    const folio = $(this).data('folio');
    $('#folio-a-eliminar').text(folio);
    $('#form-eliminar').attr('action', BASE_URL + 'admin/pagos/' + id + '/eliminar');
    $('#modal-eliminar').modal('show');
  });

  $(document).on('click', '.btn-eliminar-externo', function () {
    const id    = $(this).data('id');
    const folio = $(this).data('folio');
    $('#folio-externo-a-eliminar').text(folio);
    $('#form-eliminar-externo').attr('action', BASE_URL + 'admin/pagos-externos/' + id + '/eliminar');
    $('#modal-eliminar-externo').modal('show');
  });
});
</script>
<?= $this->endSection() ?>