<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0">Dashboard</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item active">
            <i class="fas fa-calendar-day mr-1"></i>
            <?= date('d/m/Y') ?>
          </li>
        </ol>
      </div>
    </div>
  </div>
</div>

<section class="content">
  <div class="container-fluid">

    <!-- ── Widgets KPI ───────────────────────────────────── -->
    <div class="row">

      <div class="col-lg-4 col-6">
        <div class="small-box bg-success">
          <div class="inner">
            <h3>$<?= number_format($totalHoy, 2) ?></h3>
            <p>
              <?= $rol === 'admin' ? 'Total Recaudado Hoy' : 'Mi Recaudación Hoy' ?>
              <small class="d-block" style="font-size:.75rem; opacity:.8">
                <?= $rol === 'admin' ? 'Global' : 'Mis registros' ?>
              </small>
            </p>
          </div>
          <div class="icon">
            <i class="fas fa-dollar-sign"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-4 col-6">
        <div class="small-box bg-info">
          <div class="inner">
            <h3><?= $pagosHoy ?></h3>
            <p>
              <?= $rol === 'admin' ? 'Pagos Realizados Hoy' : 'Mis Pagos Hoy' ?>
              <small class="d-block" style="font-size:.75rem; opacity:.8">
                <?= $rol === 'admin' ? 'Global' : 'Mis registros' ?>
              </small>
            </p>
          </div>
          <div class="icon">
            <i class="fas fa-file-invoice-dollar"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-4 col-6">
        <div class="small-box bg-warning">
          <div class="inner">
            <h3><?= $personasHoy ?></h3>
            <p>
              Personas Atendidas Hoy
              <small class="d-block" style="font-size:.75rem; opacity:.8">
                <?= $rol === 'admin' ? 'Global' : 'Mis registros' ?>
              </small>
            </p>
          </div>
          <div class="icon">
            <i class="fas fa-user-graduate"></i>
          </div>
        </div>
      </div>

    </div>
    <!-- /widgets -->

    <!-- ── Actividad Reciente ────────────────────────────── -->
    <?php if ($rol === 'cajero'): ?>

    <div class="row">
      <div class="col-12">
        <div class="card card-outline card-primary">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-history mr-2"></i>Mi Actividad Reciente
            </h3>
            <div class="card-tools">
              <span class="badge badge-info p-2">Tus últimos 15 registros</span>
            </div>
          </div>
          <div class="card-body p-0">
            <table class="table table-striped table-hover table-sm mb-0">
              <thead class="thead-light">
                <tr>
                  <th style="width:16%">Folio</th>
                  <th style="width:11%">Fecha / Hora</th>
                  <th>Nombre</th>
                  <th style="width:14%">Concepto</th>
                  <th class="text-right" style="width:7%">Efectivo</th>
                  <th class="text-right" style="width:7%">Transfer.</th>
                  <th class="text-right" style="width:7%">Depósito</th>
                  <th class="text-right" style="width:7%">T. Débito</th>
                  <th class="text-right" style="width:7%">T. Crédito</th>
                  <th class="text-center" style="width:6%">Tipo</th>
                  <th class="text-center" style="width:6%">Acción</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($actividadReciente)): ?>
                <tr>
                  <td colspan="11" class="text-center text-muted py-4">
                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                    No has registrado pagos aún.
                  </td>
                </tr>
                <?php else: ?>
                <?php
                $conceptoLabels = [
                    'inscripcion'   => 'Inscripción',
                    'reinscripcion' => 'Reinscripción',
                    'mensualidad'   => 'Mensualidad',
                    'tramite'       => 'Trámite',
                ];
                ?>
                <?php foreach ($actividadReciente as $a): ?>
                <?php
                    if ($a['tipo_pago'] === 'alumno') {
                        $concepto = $conceptoLabels[$a['concepto']] ?? $a['concepto'];
                        if (($a['nivel'] ?? '') === 'posgrado' && $a['concepto'] === 'mensualidad') {
                            $concepto = mb_stripos($a['modalidad'] ?? '', 'doctor') !== false ? 'Materia D' : 'Materia M';
                        } elseif ($a['concepto'] === 'tramite' && ! empty($a['detalle_tramite'])) {
                            $concepto .= ' / ' . $a['detalle_tramite'];
                        }
                        $comprobanteUrl = base_url('pagos/comprobante/' . urlencode($a['folio_digital'] ?? ''));
                    } else {
                        $concepto       = $a['concepto'];
                        $comprobanteUrl = base_url('pagos-externos/comprobante/' . urlencode($a['folio_digital'] ?? ''));
                    }
                ?>
                <tr>
                  <td>
                    <code class="text-primary" style="font-size:0.78rem;">
                      <?= esc($a['folio_digital'] ?? '—') ?>
                    </code>
                  </td>
                  <td class="text-nowrap text-muted" style="font-size:0.82rem;">
                    <?= date('d/m/Y H:i', strtotime($a['created_at'])) ?>
                  </td>
                  <td><?= esc($a['nombre'] ?? '—') ?></td>
                  <td><?= esc($concepto) ?></td>
                  <?php $__m = $a['metodo_pago'] ?? 'Efectivo'; ?>
                  <td class="text-right font-weight-bold">
                    <?= in_array($__m, ['Efectivo', ''])    ? '$' . number_format((float) $a['monto'], 2) : '<span class="text-muted">—</span>' ?>
                  </td>
                  <td class="text-right font-weight-bold">
                    <?= $__m === 'Transferencia'            ? '$' . number_format((float) $a['monto'], 2) : '<span class="text-muted">—</span>' ?>
                  </td>
                  <td class="text-right font-weight-bold">
                    <?= $__m === 'Depósito bancario'        ? '$' . number_format((float) $a['monto'], 2) : '<span class="text-muted">—</span>' ?>
                  </td>
                  <td class="text-right font-weight-bold">
                    <?= $__m === 'Tarjeta de débito'        ? '$' . number_format((float) $a['monto'], 2) : '<span class="text-muted">—</span>' ?>
                  </td>
                  <td class="text-right font-weight-bold">
                    <?= $__m === 'Tarjeta de crédito'       ? '$' . number_format((float) $a['monto'], 2) : '<span class="text-muted">—</span>' ?>
                  </td>
                  <td class="text-center">
                    <?php if ($a['tipo_pago'] === 'alumno'): ?>
                      <span class="badge badge-primary" style="font-size:0.7rem;">Alumno</span>
                    <?php else: ?>
                      <span class="badge badge-warning text-dark" style="font-size:0.7rem;">Externo</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-center">
                    <?php if (! empty($a['folio_digital'])): ?>
                    <a href="<?= $comprobanteUrl ?>"
                       target="_blank"
                       class="btn btn-xs btn-outline-secondary"
                       title="Reimprimir comprobante">
                      <i class="fas fa-print"></i>
                    </a>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <?php else: /* admin — dos tablas separadas */ ?>

    <!-- ── Tabla de Pagos Recientes ──────────────────────── -->
    <div class="row">
      <div class="col-12">
        <div class="card card-outline card-primary">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-history mr-2"></i>Últimos 10 Pagos
            </h3>
          </div>
          <div class="card-body p-0">
            <table class="table table-striped table-hover table-sm mb-0">
              <thead class="thead-light">
                <tr>
                  <th>Folio</th>
                  <th>Alumno</th>
                  <th>Concepto</th>
                  <th class="text-right">Efectivo</th>
                  <th class="text-right">Transfer.</th>
                  <th class="text-right">Depósito</th>
                  <th class="text-right">T. Débito</th>
                  <th class="text-right">T. Crédito</th>
                  <th>Cajero</th>
                  <th>Fecha / Hora</th>
                  <th class="text-center">Acción</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($pagosRecientes)): ?>
                  <tr>
                    <td colspan="11" class="text-center text-muted py-4">
                      <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                      No hay pagos registrados aún.
                    </td>
                  </tr>
                <?php else: ?>

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
                ?>

                <?php foreach ($pagosRecientes as $p): ?>
                <?php
                    $concepto = $conceptoLabels[$p['concepto']] ?? $p['concepto'];
                    if (($p['nivel'] ?? '') === 'posgrado' && $p['concepto'] === 'mensualidad') {
                        $concepto = mb_stripos($p['modalidad'] ?? '', 'doctor') !== false ? 'Materia D' : 'Materia M';
                    } elseif ($p['concepto'] === 'tramite' && ! empty($p['detalle_tramite'])) {
                        $concepto .= ' / ' . ($detalleLabels[$p['detalle_tramite']] ?? $p['detalle_tramite']);
                    }
                ?>
                <tr>
                  <td>
                    <code class="text-primary" style="font-size:0.78rem;">
                      <?= esc($p['folio_digital'] ?? '—') ?>
                    </code>
                  </td>
                  <td><?= esc($p['nombre_alumno']) ?></td>
                  <td><?= esc($concepto) ?></td>
                  <?php $__m = $p['metodo_pago'] ?? 'Efectivo'; ?>
                  <td class="text-right font-weight-bold">
                    <?= in_array($__m, ['Efectivo', ''])    ? '$' . number_format((float) $p['monto'], 2) : '<span class="text-muted">—</span>' ?>
                  </td>
                  <td class="text-right font-weight-bold">
                    <?= $__m === 'Transferencia'            ? '$' . number_format((float) $p['monto'], 2) : '<span class="text-muted">—</span>' ?>
                  </td>
                  <td class="text-right font-weight-bold">
                    <?= $__m === 'Depósito bancario'        ? '$' . number_format((float) $p['monto'], 2) : '<span class="text-muted">—</span>' ?>
                  </td>
                  <td class="text-right font-weight-bold">
                    <?= $__m === 'Tarjeta de débito'        ? '$' . number_format((float) $p['monto'], 2) : '<span class="text-muted">—</span>' ?>
                  </td>
                  <td class="text-right font-weight-bold">
                    <?= $__m === 'Tarjeta de crédito'       ? '$' . number_format((float) $p['monto'], 2) : '<span class="text-muted">—</span>' ?>
                  </td>
                  <td><?= esc($p['nombre_cajero'] ?? 'N/D') ?></td>
                  <td class="text-nowrap text-muted" style="font-size:0.82rem;">
                    <?= date('d/m/Y H:i', strtotime($p['created_at'])) ?>
                  </td>
                  <td class="text-center text-nowrap">
                    <?php if (! empty($p['folio_digital'])): ?>
                      <a href="<?= base_url('pagos/comprobante/' . esc($p['folio_digital'])) ?>"
                         target="_blank"
                         class="btn btn-xs btn-outline-secondary"
                         title="Reimprimir comprobante">
                        <i class="fas fa-print"></i>
                      </a>
                    <?php endif; ?>
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
                  </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div><!-- /.card-body -->
        </div><!-- /.card -->
      </div>
    </div>
    <!-- /tabla -->

    <!-- ── Tabla de Pagos Externos Recientes ────────────────── -->
    <div class="row mt-2">
      <div class="col-12">
        <div class="card card-outline card-success">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-user-tag mr-2"></i>Últimos Pagos Externos / Aspirantes
            </h3>
          </div>
          <div class="card-body p-0">
            <table class="table table-striped table-hover table-sm mb-0">
              <thead class="thead-light">
                <tr>
                  <th>Folio</th>
                  <th>Cliente / Aspirante</th>
                  <th>Concepto</th>
                  <th class="text-right">Efectivo</th>
                  <th class="text-right">Transfer.</th>
                  <th class="text-right">Depósito</th>
                  <th class="text-right">T. Débito</th>
                  <th class="text-right">T. Crédito</th>
                  <th>Cajero</th>
                  <th>Fecha / Hora</th>
                  <th class="text-center">Acción</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($externosRecientes)): ?>
                  <tr>
                    <td colspan="11" class="text-center text-muted py-4">
                      <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                      No hay pagos externos registrados aún.
                    </td>
                  </tr>
                <?php else: ?>
                <?php foreach ($externosRecientes as $e): ?>
                <tr>
                  <td>
                    <code class="text-primary" style="font-size:0.78rem;">
                      <?= esc($e['folio_digital'] ?? '—') ?>
                    </code>
                  </td>
                  <td><?= esc($e['nombre_cliente']) ?></td>
                  <td><?= esc($e['concepto']) ?></td>
                  <?php $__m = $e['metodo_pago'] ?? 'Efectivo'; ?>
                  <td class="text-right font-weight-bold">
                    <?= in_array($__m, ['Efectivo', ''])    ? '$' . number_format((float) $e['monto'], 2) : '<span class="text-muted">—</span>' ?>
                  </td>
                  <td class="text-right font-weight-bold">
                    <?= $__m === 'Transferencia'            ? '$' . number_format((float) $e['monto'], 2) : '<span class="text-muted">—</span>' ?>
                  </td>
                  <td class="text-right font-weight-bold">
                    <?= $__m === 'Depósito bancario'        ? '$' . number_format((float) $e['monto'], 2) : '<span class="text-muted">—</span>' ?>
                  </td>
                  <td class="text-right font-weight-bold">
                    <?= $__m === 'Tarjeta de débito'        ? '$' . number_format((float) $e['monto'], 2) : '<span class="text-muted">—</span>' ?>
                  </td>
                  <td class="text-right font-weight-bold">
                    <?= $__m === 'Tarjeta de crédito'       ? '$' . number_format((float) $e['monto'], 2) : '<span class="text-muted">—</span>' ?>
                  </td>
                  <td><?= esc($e['nombre_cajero'] ?? 'N/D') ?></td>
                  <td class="text-nowrap text-muted" style="font-size:0.82rem;">
                    <?= date('d/m/Y H:i', strtotime($e['created_at'])) ?>
                  </td>
                  <td class="text-center text-nowrap">
                    <?php if (! empty($e['folio_digital'])): ?>
                      <a href="<?= base_url('pagos-externos/comprobante/' . esc($e['folio_digital'])) ?>"
                         target="_blank"
                         class="btn btn-xs btn-outline-secondary"
                         title="Reimprimir recibo">
                        <i class="fas fa-print"></i>
                      </a>
                    <?php endif; ?>
                    <a href="<?= base_url('admin/pagos-externos/' . $e['id'] . '/editar') ?>"
                       class="btn btn-xs btn-outline-warning"
                       title="Editar">
                      <i class="fas fa-pencil-alt"></i>
                    </a>
                    <button type="button"
                            class="btn btn-xs btn-outline-danger btn-eliminar-externo"
                            data-id="<?= $e['id'] ?>"
                            data-folio="<?= esc($e['folio_digital'] ?? $e['id']) ?>"
                            title="Eliminar">
                      <i class="fas fa-trash-alt"></i>
                    </button>
                  </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <!-- /tabla externos -->

    <?php endif; /* fin admin vs cajero */ ?>

  </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('modals') ?>

<!-- Modal Eliminar Pago Regular -->
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