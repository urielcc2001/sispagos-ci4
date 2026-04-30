<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Estado de Cuenta<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-search-dollar mr-2 text-primary"></i>Estado de Cuenta</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Estado de Cuenta</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<section class="content">
  <div class="container-fluid">

    <!-- ── Formulario de búsqueda ──────────────────────────────── -->
    <div class="card card-outline card-primary">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-search mr-2"></i>Consultar Estado de Cuenta</h3>
      </div>
      <div class="card-body">
        <form method="GET" action="<?= base_url('admin/estado-cuenta') ?>">
          <div class="row align-items-end">
            <div class="col-md-3">
              <div class="form-group mb-0">
                <label>No. de Control <span class="text-danger">*</span></label>
                <input type="text" name="num_control" class="form-control"
                       value="<?= esc($num_control) ?>"
                       placeholder="Ej. 20230001" required>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group mb-0">
                <label>Nivel <span class="text-danger">*</span></label>
                <select name="nivel" class="form-control" required>
                  <option value="">— Seleccionar —</option>
                  <option value="prepa"    <?= $nivel === 'prepa'    ? 'selected' : '' ?>>Bachillerato</option>
                  <option value="uni"      <?= $nivel === 'uni'      ? 'selected' : '' ?>>Universidad</option>
                  <option value="posgrado" <?= $nivel === 'posgrado' ? 'selected' : '' ?>>Posgrado</option>
                </select>
              </div>
            </div>
            <div class="col-md-2">
              <div class="form-group mb-0">
                <label>Año</label>
                <select name="anio" class="form-control">
                  <?php for ($y = (int) date('Y'); $y >= (int) date('Y') - 4; $y--): ?>
                    <option value="<?= $y ?>" <?= $anio == $y ? 'selected' : '' ?>><?= $y ?></option>
                  <?php endfor; ?>
                </select>
              </div>
            </div>
            <div class="col-md-auto">
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-search mr-1"></i> Consultar
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <?php if ($num_control && $nivel): ?>

      <?php
        $nivelLabels    = ['uni' => 'Universidad', 'prepa' => 'Bachillerato', 'posgrado' => 'Posgrado'];
        $conceptoLabels = ['inscripcion' => 'Inscripción', 'reinscripcion' => 'Reinscripción', 'tramite' => 'Trámite'];
        $pagados    = count(array_filter($estado, fn($e) => $e['status'] === 'pagado'));
        $pendientes = count(array_filter($estado, fn($e) => $e['status'] === 'pendiente'));
      ?>

      <!-- ── Encabezado del alumno ──────────────────────────────── -->
      <div class="card card-outline <?= $pendientes > 0 ? 'card-danger' : 'card-success' ?>">
        <div class="card-header">
          <h3 class="card-title">
            <i class="fas fa-user-graduate mr-2"></i>
            <?php if ($info_alumno): ?>
              <?= esc($info_alumno['nombre_alumno']) ?>
              <small class="text-muted ml-2"><?= esc($num_control) ?></small>
            <?php else: ?>
              No. Control: <?= esc($num_control) ?>
            <?php endif; ?>
          </h3>
          <div class="card-tools">
            <span class="badge badge-info p-2 mr-1"><?= esc($nivelLabels[$nivel] ?? $nivel) ?></span>
            <?php if ($totales): ?>
              <span class="badge badge-dark p-2 mr-1">
                <i class="fas fa-dollar-sign mr-1"></i>Total histórico: $<?= number_format($totales['total'], 2) ?>
              </span>
            <?php endif; ?>
            <?php if ($pendientes > 0): ?>
              <span class="badge badge-danger p-2">
                <i class="fas fa-exclamation-triangle mr-1"></i><?= $pendientes ?> mes(es) pendiente(s) en <?= $anio ?>
              </span>
            <?php else: ?>
              <span class="badge badge-success p-2">
                <i class="fas fa-check mr-1"></i>Al corriente en <?= $anio ?>
              </span>
            <?php endif; ?>
          </div>
        </div>
        <?php if ($info_alumno && ($info_alumno['carrera'] || $info_alumno['modalidad'])): ?>
        <div class="card-body py-2">
          <small class="text-muted">
            <?php if ($info_alumno['carrera']): ?>
              <strong>Carrera:</strong> <?= esc($info_alumno['carrera']) ?>
            <?php endif; ?>
            <?php if ($info_alumno['modalidad']): ?>
              &nbsp;·&nbsp; <strong>Modalidad:</strong> <?= esc($info_alumno['modalidad']) ?>
            <?php endif; ?>
          </small>
        </div>
        <?php endif; ?>
      </div>

      <!-- ═══ BLOQUE A — Inscripciones / Reinscripciones ═══ -->
      <div class="card card-outline card-success">
        <div class="card-header">
          <h3 class="card-title">
            <i class="fas fa-clipboard-check mr-2 text-success"></i>
            Bloque A — Pagos Iniciales (Inscripciones / Reinscripciones)
          </h3>
          <?php if ($totales && $totales['inscripciones'] > 0): ?>
          <div class="card-tools">
            <span class="badge badge-success p-2">
              Subtotal: $<?= number_format($totales['inscripciones'], 2) ?>
            </span>
          </div>
          <?php endif; ?>
        </div>
        <div class="card-body p-0">
          <?php if (! empty($inscripciones)): ?>
          <table class="table table-sm table-striped mb-0">
            <thead class="thead-light">
              <tr>
                <th style="width:150px">Concepto</th>
                <th>Periodo</th>
                <th style="width:110px">Fecha de Pago</th>
                <th>Folio</th>
                <th class="text-right" style="width:120px">Monto</th>
                <th style="width:60px">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($inscripciones as $p): ?>
              <?php
                $mesRef = ! empty($p['mes_inicio_ciclo'])
                          ? (int) $p['mes_inicio_ciclo']
                          : (int) date('n', strtotime($p['created_at']));
                if ($mesRef <= 4)     $periodoLabel = 'Cuatrimestre 1 (Ene – Abr)';
                elseif ($mesRef <= 8) $periodoLabel = 'Cuatrimestre 2 (May – Ago)';
                else                  $periodoLabel = 'Cuatrimestre 3 (Sep – Dic)';
              ?>
              <tr>
                <td>
                  <?php if ($p['concepto'] === 'inscripcion'): ?>
                    <span class="badge badge-success px-2 py-1">
                      <i class="fas fa-star mr-1"></i>Inscripción
                    </span>
                  <?php else: ?>
                    <span class="badge badge-info px-2 py-1">
                      <i class="fas fa-redo mr-1"></i>Reinscripción
                    </span>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="text-sm">
                    <i class="fas fa-layer-group mr-1 text-muted"></i><?= $periodoLabel ?>
                  </span>
                </td>
                <td><?= date('d/m/Y', strtotime($p['created_at'])) ?></td>
                <td><code class="text-muted"><?= esc($p['folio_digital'] ?? '—') ?></code></td>
                <td class="text-right font-weight-bold text-success">
                  $<?= number_format((float) $p['monto'], 2) ?>
                </td>
                <td class="text-center">
                  <?php if (! empty($p['folio_digital'])): ?>
                  <a href="<?= base_url('pagos/comprobante/' . urlencode($p['folio_digital'])) ?>"
                     target="_blank"
                     class="btn btn-xs btn-default border"
                     title="Reimprimir comprobante">
                    <i class="fas fa-print"></i>
                  </a>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <?php else: ?>
          <div class="callout callout-warning m-3">
            <i class="fas fa-exclamation-circle mr-2"></i>
            No se encontró pago de inscripción o reinscripción para este alumno.
          </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- ═══ BLOQUE B — Mensualidades ═══ -->
      <div class="card card-outline card-primary">
        <div class="card-header">
          <h3 class="card-title">
            <i class="fas fa-calendar-alt mr-2 text-primary"></i>
            Bloque B — Mensualidades <?= $anio ?>
          </h3>
          <div class="card-tools">
            <span class="badge badge-success p-2 mr-1">
              <i class="fas fa-check mr-1"></i><?= $pagados ?> pagado(s)
            </span>
            <?php if ($pendientes > 0): ?>
            <span class="badge badge-danger p-2 mr-1">
              <i class="fas fa-times mr-1"></i><?= $pendientes ?> pendiente(s)
            </span>
            <?php endif; ?>
            <?php if ($totales && $totales['mensualidades'] > 0): ?>
            <span class="badge badge-primary p-2">
              Subtotal histórico: $<?= number_format($totales['mensualidades'], 2) ?>
            </span>
            <?php endif; ?>
          </div>
        </div>
        <div class="card-body">

          <?php if (! empty($anios)): ?>
          <div class="mb-4">
            <label class="text-muted text-uppercase font-weight-bold" style="font-size:.75rem">Año consultado</label>
            <div class="d-flex flex-wrap">
              <?php foreach ($anios as $y): ?>
                <a href="?num_control=<?= urlencode($num_control) ?>&nivel=<?= urlencode($nivel) ?>&anio=<?= $y ?>"
                   class="btn btn-sm <?= $y == $anio ? 'btn-primary' : 'btn-outline-secondary' ?> mr-1 mb-1">
                  <?= $y ?>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>

          <div class="row">
            <?php foreach ($estado as $e): ?>
              <?php
                switch ($e['status']) {
                    case 'pagado':
                        $cardClass = 'bg-success text-white';
                        $icon      = '<i class="fas fa-check-circle fa-2x"></i>';
                        break;
                    case 'pendiente':
                        $cardClass = 'bg-danger text-white';
                        $icon      = '<i class="fas fa-times-circle fa-2x"></i>';
                        break;
                    case 'na':
                        $cardClass = 'bg-white text-muted border';
                        $icon      = '<i class="fas fa-minus fa-2x text-muted" style="opacity:.35"></i>';
                        break;
                    default:
                        $cardClass = 'bg-light text-muted';
                        $icon      = '<i class="fas fa-clock fa-2x text-muted"></i>';
                }
              ?>
              <div class="col-md-2 col-sm-3 col-4 mb-3">
                <div class="card mb-0 shadow-sm <?= $cardClass ?>" style="border-radius:8px">
                  <div class="card-body text-center py-3 px-2">
                    <p class="mb-2 font-weight-bold" style="font-size:.85rem; letter-spacing:.03rem">
                      <?= $e['nombre'] ?>
                    </p>
                    <?= $icon ?>
                    <?php if ($e['status'] === 'pagado' && ! empty($e['folio_digital'])): ?>
                    <div class="mt-2">
                      <a href="<?= base_url('pagos/comprobante/' . urlencode($e['folio_digital'])) ?>"
                         target="_blank"
                         class="btn btn-xs btn-light text-dark"
                         title="Reimprimir comprobante">
                        <i class="fas fa-print"></i>
                      </a>
                    </div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="mt-3 pt-3 border-top">
            <span class="badge badge-success p-2 mr-2">
              <i class="fas fa-check-circle mr-1"></i>Pagado
            </span>
            <span class="badge badge-danger p-2 mr-2">
              <i class="fas fa-times-circle mr-1"></i>Pendiente
            </span>
            <span class="badge badge-secondary p-2 mr-2">
              <i class="fas fa-clock mr-1"></i>Próximo
            </span>
            <span class="badge badge-light border p-2 text-muted">
              <i class="fas fa-minus mr-1"></i>Antes de inscripción
            </span>
          </div>

        </div>
      </div>

      <!-- ═══ BLOQUE C — Otros Cargos y Pagos ═══ -->
      <?php if (! empty($pagos_otros)): ?>
      <div class="card card-outline card-warning">
        <div class="card-header">
          <h3 class="card-title">
            <i class="fas fa-file-invoice-dollar mr-2 text-warning"></i>
            Bloque C — Otros Cargos y Pagos
          </h3>
          <div class="card-tools">
            <span class="badge badge-warning p-2 text-dark">
              Subtotal: $<?= number_format($totales['otros'] ?? 0, 2) ?>
            </span>
          </div>
        </div>
        <div class="card-body p-0">
          <table class="table table-sm table-striped mb-0">
            <thead class="thead-light">
              <tr>
                <th style="width:130px">Tipo</th>
                <th>Detalle / Concepto</th>
                <th style="width:110px">Fecha</th>
                <th>Folio</th>
                <th class="text-right" style="width:120px">Monto</th>
                <th style="width:60px">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($pagos_otros as $p): ?>
              <tr>
                <td>
                  <span class="badge badge-warning text-dark px-2 py-1">
                    <i class="fas fa-file-alt mr-1"></i>
                    <?= esc($conceptoLabels[$p['concepto']] ?? ucfirst($p['concepto'])) ?>
                  </span>
                </td>
                <td><?= esc($p['detalle_tramite'] ?? '—') ?></td>
                <td><?= date('d/m/Y', strtotime($p['created_at'])) ?></td>
                <td><code class="text-muted"><?= esc($p['folio_digital'] ?? '—') ?></code></td>
                <td class="text-right font-weight-bold text-warning">
                  $<?= number_format((float) $p['monto'], 2) ?>
                </td>
                <td class="text-center">
                  <?php if (! empty($p['folio_digital'])): ?>
                  <a href="<?= base_url('pagos/comprobante/' . urlencode($p['folio_digital'])) ?>"
                     target="_blank"
                     class="btn btn-xs btn-default border"
                     title="Reimprimir comprobante">
                    <i class="fas fa-print"></i>
                  </a>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endif; ?>

      <!-- ═══ RESUMEN GENERAL ═══ -->
      <?php if ($totales): ?>
      <div class="card card-outline card-dark">
        <div class="card-header">
          <h3 class="card-title">
            <i class="fas fa-chart-pie mr-2"></i>Resumen General Histórico
          </h3>
        </div>
        <div class="card-body pb-0">
          <div class="row">
            <div class="col-md-3 col-sm-6 mb-3">
              <div class="info-box bg-success shadow-sm">
                <span class="info-box-icon"><i class="fas fa-clipboard-check"></i></span>
                <div class="info-box-content">
                  <span class="info-box-text">Inscripciones</span>
                  <span class="info-box-number">$<?= number_format($totales['inscripciones'], 2) ?></span>
                  <span class="progress-description"><?= count($inscripciones) ?> pago(s) registrado(s)</span>
                </div>
              </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
              <div class="info-box bg-primary shadow-sm">
                <span class="info-box-icon"><i class="fas fa-calendar-check"></i></span>
                <div class="info-box-content">
                  <span class="info-box-text">Mensualidades</span>
                  <span class="info-box-number">$<?= number_format($totales['mensualidades'], 2) ?></span>
                  <span class="progress-description"><?= $totales['mensualidades_cnt'] ?> mes(es) pagado(s)</span>
                </div>
              </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
              <div class="info-box bg-warning shadow-sm">
                <span class="info-box-icon"><i class="fas fa-file-invoice"></i></span>
                <div class="info-box-content">
                  <span class="info-box-text">Otros Pagos</span>
                  <span class="info-box-number">$<?= number_format($totales['otros'], 2) ?></span>
                  <span class="progress-description"><?= count($pagos_otros) ?> concepto(s) adicional(es)</span>
                </div>
              </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
              <div class="info-box bg-dark shadow-sm">
                <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                <div class="info-box-content">
                  <span class="info-box-text">Total General</span>
                  <span class="info-box-number">$<?= number_format($totales['total'], 2) ?></span>
                  <?php if ($pendientes > 0): ?>
                  <span class="progress-description" style="color:#ffc107">
                    <i class="fas fa-exclamation-triangle mr-1"></i><?= $pendientes ?> mes(es) sin pagar en <?= $anio ?>
                  </span>
                  <?php else: ?>
                  <span class="progress-description" style="color:#28a745">
                    <i class="fas fa-check mr-1"></i>Al corriente en <?= $anio ?>
                  </span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>

    <?php endif; ?>

  </div>
</section>

<?= $this->endSection() ?>