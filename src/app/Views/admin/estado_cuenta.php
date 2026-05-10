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
                  <?php
                    // Rango dinámico: si ya se consultó un alumno, usar sus años reales;
                    // si no, mostrar los últimos 5 años como default.
                    $opcionesAnio = ! empty($anios)
                        ? $anios
                        : array_map('strval', range((int) date('Y'), (int) date('Y') - 4));
                  ?>
                  <?php foreach ($opcionesAnio as $y): ?>
                    <option value="<?= $y ?>" <?= $anio == $y ? 'selected' : '' ?>><?= $y ?></option>
                  <?php endforeach; ?>
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
        if ($es_posgrado) {
            $pagados    = count(array_filter($materias_estado, fn($m) => $m['pagada']));
            $parciales  = 0;
            $pendientes = count(array_filter($materias_estado, fn($m) => ! $m['pagada']));
        } else {
            $pagados    = count(array_filter($estado, fn($e) => $e['status'] === 'pagado'));
            $parciales  = count(array_filter($estado, fn($e) => $e['status'] === 'parcial'));
            $pendientes = count(array_filter($estado, fn($e) => $e['status'] === 'pendiente'));
        }
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
            <?php if ($periodo_actual): ?>
              <span class="badge badge-secondary p-2 mr-1">
                <i class="fas fa-layer-group mr-1"></i><?= esc($periodo_actual) ?>
              </span>
            <?php endif; ?>
            <?php if ($totales): ?>
              <span class="badge badge-dark p-2 mr-1">
                <i class="fas fa-dollar-sign mr-1"></i>Total histórico: $<?= number_format($totales['total'], 2) ?>
              </span>
            <?php endif; ?>
            <?php if ($pendientes > 0): ?>
              <span class="badge badge-danger p-2">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                <?php if ($es_posgrado): ?>
                  <?= $pendientes ?> materia(s) pendiente(s)
                <?php else: ?>
                  <?= $pendientes ?> mes(es) pendiente(s) en <?= $anio ?>
                <?php endif; ?>
              </span>
            <?php else: ?>
              <span class="badge badge-success p-2">
                <i class="fas fa-check mr-1"></i>
                <?= $es_posgrado ? 'Materias al corriente' : 'Al corriente en ' . $anio ?>
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
          <?php
            // Agrupar por concepto + periodo_pago
            $gruposInsc = [];
            foreach ($inscripciones as $p) {
                $gKey = $p['concepto'] . '_' . ($p['periodo_pago'] ?? '0');
                $gruposInsc[$gKey][] = $p;
            }

            // Helper: calcular label de periodo
            $periodoLabelFn = function (array $p): string {
                $num    = ! empty($p['periodo_pago']) ? (int) $p['periodo_pago'] : null;
                $plan   = $p['detalle_tramite'] ?? '';
                $nivelP = $p['nivel'] ?? '';
                if ($num) {
                    if ($plan === 'Semestral') {
                        $r = ($num % 2 !== 0) ? 'Ago – Dic' : 'Feb – Jul';
                        return 'Semestre ' . $num . ' (' . $r . ')';
                    } elseif ($nivelP === 'uni' || $plan === 'Cuatrimestral') {
                        $mod = $num % 3;
                        $r   = $mod === 1 ? 'Ene – Abr' : ($mod === 2 ? 'May – Ago' : 'Sep – Dic');
                        return 'Cuatrimestre ' . $num . ' (' . $r . ')';
                    } else {
                        $label = 'Periodo ' . $num;
                        if (! empty($p['tipo_periodo'])) $label .= ' — ' . $p['tipo_periodo'];
                        return $label;
                    }
                }
                $mesRef = ! empty($p['mes_inicio_ciclo'])
                          ? (int) $p['mes_inicio_ciclo']
                          : (int) date('n', strtotime($p['created_at']));
                if ($mesRef <= 4)     return 'Cuatrimestre 1 (Ene – Abr)';
                elseif ($mesRef <= 8) return 'Cuatrimestre 2 (May – Ago)';
                else                  return 'Cuatrimestre 3 (Sep – Dic)';
            };
          ?>
          <table class="table table-sm mb-0">
            <thead class="thead-light">
              <tr>
                <th style="width:150px">Concepto</th>
                <th>Periodo</th>
                <th style="width:130px">Tipo de pago</th>
                <th style="width:110px">Fecha</th>
                <th>Folio</th>
                <th class="text-right" style="width:120px">Monto</th>
                <th style="width:60px"></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($gruposInsc as $pagosGrupo): ?>
              <?php
                $primerPago    = $pagosGrupo[0];
                $tieneCompleto = (bool) count(array_filter($pagosGrupo, fn($x) => $x['num_abono'] === null));
                $tieneAbonos   = (bool) count(array_filter($pagosGrupo, fn($x) => $x['num_abono'] !== null));
                $esMultiple    = count($pagosGrupo) > 1;
                $totalGrupo    = array_sum(array_column($pagosGrupo, 'monto'));
                $periodoLabel  = $periodoLabelFn($primerPago);
              ?>

              <?php if ($esMultiple): ?>
              <!-- Fila cabecera de grupo -->
              <tr class="table-light">
                <td>
                  <?php if ($primerPago['concepto'] === 'inscripcion'): ?>
                    <span class="badge badge-success px-2 py-1"><i class="fas fa-star mr-1"></i>Inscripción</span>
                  <?php else: ?>
                    <span class="badge badge-info px-2 py-1"><i class="fas fa-redo mr-1"></i>Reinscripción</span>
                  <?php endif; ?>
                </td>
                <td>
                  <i class="fas fa-layer-group mr-1 text-muted"></i><strong><?= $periodoLabel ?></strong>
                </td>
                <td>
                  <?php if ($tieneCompleto): ?>
                    <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i>Pagado completo</span>
                  <?php else: ?>
                    <span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-adjust mr-1"></i>Con abonos — pendiente</span>
                  <?php endif; ?>
                </td>
                <td colspan="2" class="text-muted small"><i class="fas fa-list-ul mr-1"></i><?= count($pagosGrupo) ?> registro(s)</td>
                <td class="text-right font-weight-bold <?= $tieneCompleto ? 'text-success' : 'text-warning' ?>">
                  $<?= number_format($totalGrupo, 2) ?>
                </td>
                <td></td>
              </tr>
              <?php endif; ?>

              <?php foreach ($pagosGrupo as $p): ?>
              <tr <?= $esMultiple ? 'class="table-sm"' : '' ?>>
                <td <?= $esMultiple ? 'class="pl-4"' : '' ?>>
                  <?php if (! $esMultiple): ?>
                    <?php if ($p['concepto'] === 'inscripcion'): ?>
                      <span class="badge badge-success px-2 py-1"><i class="fas fa-star mr-1"></i>Inscripción</span>
                    <?php else: ?>
                      <span class="badge badge-info px-2 py-1"><i class="fas fa-redo mr-1"></i>Reinscripción</span>
                    <?php endif; ?>
                  <?php else: ?>
                    <i class="fas fa-level-up-alt fa-rotate-90 text-muted ml-2"></i>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if (! $esMultiple): ?>
                    <i class="fas fa-layer-group mr-1 text-muted"></i><?= $periodoLabel ?>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($p['num_abono'] === null): ?>
                    <span class="badge badge-success px-2 py-1">
                      <i class="fas fa-check-circle mr-1"></i>Pago completo
                    </span>
                  <?php else: ?>
                    <span class="badge badge-warning text-dark px-2 py-1">
                      <i class="fas fa-adjust mr-1"></i>Abono <?= (int) $p['num_abono'] ?>
                    </span>
                  <?php endif; ?>
                </td>
                <td><?= date('d/m/Y', strtotime($p['created_at'])) ?></td>
                <td><code class="text-muted" style="font-size:.72rem"><?= esc($p['folio_digital'] ?? '—') ?></code></td>
                <td class="text-right font-weight-bold text-success">
                  $<?= number_format((float) $p['monto'], 2) ?>
                </td>
                <td class="text-center">
                  <?php if (! empty($p['folio_digital'])): ?>
                  <a href="<?= base_url('pagos/comprobante/' . urlencode($p['folio_digital'])) ?>"
                     target="_blank" class="btn btn-xs btn-default border" title="Reimprimir">
                    <i class="fas fa-print"></i>
                  </a>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>

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

      <!-- ═══ BLOQUE B — Mensualidades / Materias ═══ -->
      <div class="card card-outline card-primary">
        <div class="card-header">
          <h3 class="card-title">
            <?php if ($es_posgrado): ?>
              <i class="fas fa-book-open mr-2 text-primary"></i>
              Bloque B — Materias del Programa
            <?php else: ?>
              <i class="fas fa-calendar-alt mr-2 text-primary"></i>
              Bloque B — Mensualidades <?= $anio ?>
            <?php endif; ?>
          </h3>
          <div class="card-tools">
            <span class="badge badge-success p-2 mr-1">
              <i class="fas fa-check mr-1"></i><?= $pagados ?> pagado(s)
            </span>
            <?php if ($parciales > 0): ?>
            <span class="badge badge-warning p-2 mr-1 text-dark">
              <i class="fas fa-adjust mr-1"></i><?= $parciales ?> con abono(s)
            </span>
            <?php endif; ?>
            <?php if ($pendientes > 0): ?>
            <span class="badge badge-danger p-2 mr-1">
              <i class="fas fa-times mr-1"></i><?= $pendientes ?> pendiente(s)
            </span>
            <?php endif; ?>
            <?php if ($totales && $totales['mensualidades'] > 0): ?>
            <span class="badge badge-primary p-2">
              Subtotal: $<?= number_format($totales['mensualidades'], 2) ?>
            </span>
            <?php endif; ?>
          </div>
        </div>
        <div class="card-body">

          <?php if ($es_posgrado): ?>

            <!-- ── Malla de Materias (Posgrado) ── -->
            <?php if (empty($materias_estado)): ?>
            <div class="callout callout-warning">
              <i class="fas fa-exclamation-circle mr-2"></i>
              No se encontraron materias registradas para el programa de este alumno.
            </div>
            <?php else: ?>
            <div class="row">
              <?php foreach ($materias_estado as $m): ?>
              <div class="col-lg-3 col-md-4 col-sm-6 col-12 mb-3">
                <div class="card mb-0 shadow-sm h-100 <?= $m['pagada'] ? 'border-success' : 'border-secondary' ?>"
                     style="border-left-width:4px; border-left-style:solid; border-radius:6px">
                  <div class="card-body py-2 px-3">
                    <div class="d-flex align-items-start justify-content-between mb-1">
                      <span class="font-weight-bold" style="font-size:.78rem; line-height:1.3; flex:1">
                        <?= esc($m['nombre']) ?>
                      </span>
                      <?php if ($m['pagada']): ?>
                        <i class="fas fa-check-circle text-success ml-2 mt-1" style="font-size:1rem; flex-shrink:0"></i>
                      <?php else: ?>
                        <i class="fas fa-clock text-secondary ml-2 mt-1" style="font-size:1rem; opacity:.5; flex-shrink:0"></i>
                      <?php endif; ?>
                    </div>
                    <?php if ($m['clave']): ?>
                      <small class="text-muted d-block mb-1" style="font-size:.68rem"><?= esc($m['clave']) ?></small>
                    <?php endif; ?>
                    <?php if ($m['pagada']): ?>
                      <div class="mt-1 pt-1 border-top">
                        <div class="d-flex justify-content-between align-items-center">
                          <div>
                            <small class="text-muted d-block" style="font-size:.65rem"><?= $m['fecha'] ?></small>
                            <span class="text-success font-weight-bold" style="font-size:.8rem">
                              $<?= number_format($m['monto'], 2) ?>
                            </span>
                          </div>
                          <?php if ($m['folio']): ?>
                          <a href="<?= base_url('pagos/comprobante/' . urlencode($m['folio'])) ?>"
                             target="_blank"
                             class="btn btn-xs btn-outline-success"
                             title="Reimprimir comprobante">
                            <i class="fas fa-print"></i>
                          </a>
                          <?php endif; ?>
                        </div>
                        <small class="text-muted" style="font-size:.6rem; word-break:break-all">
                          <?= esc($m['folio'] ?? '') ?>
                        </small>
                      </div>
                    <?php else: ?>
                      <span class="badge badge-secondary mt-1" style="font-size:.65rem">Pendiente de pago</span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
            <div class="mt-2 pt-2 border-top d-flex flex-wrap align-items-center" style="gap:.4rem">
              <span class="badge badge-success p-2">
                <i class="fas fa-check-circle mr-1"></i>Pagada
              </span>
              <span class="badge badge-secondary p-2">
                <i class="fas fa-clock mr-1"></i>Pendiente
              </span>
            </div>
            <?php endif; ?>

          <?php else: ?>

            <!-- ── Calendario de Mensualidades (Licenciatura / Prepa) ── -->
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

            <?php if ($directa_anio): ?>
            <div class="callout callout-info mb-3">
              <i class="fas fa-info-circle mr-2"></i>
              <strong>Ciclo <?= $anio ?> iniciado mediante mensualidad directa.</strong>
              No se encontró inscripción ni reinscripción para este año.
              Los meses anteriores al primer pago registrado se muestran en gris
              (<i class="fas fa-minus"></i>) y <strong>no se contabilizan como adeudos</strong>,
              ya que podrían haberse liquidado fuera del sistema.
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
                      case 'parcial':
                          $cardClass = 'bg-warning text-dark';
                          $icon      = '<i class="fas fa-adjust fa-2x text-dark" style="opacity:.85"></i>';
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
                      <?php if ($e['status'] === 'parcial'): ?>
                      <div class="mt-1">
                        <span class="badge badge-dark" style="font-size:.65rem">
                          <?= $e['abonos'] ?> abono<?= $e['abonos'] !== 1 ? 's' : '' ?>
                        </span>
                      </div>
                      <?php endif; ?>
                      <?php
                        $mesKey = $anio . '-' . str_pad($e['mes'], 2, '0', STR_PAD_LEFT);
                        $tienePagos = ! empty($pagos_detalle_meses[$mesKey]);
                      ?>
                      <?php if (in_array($e['status'], ['pagado', 'parcial']) && $tienePagos): ?>
                      <div class="mt-2">
                        <button type="button"
                                class="btn btn-xs <?= $e['status'] === 'parcial' ? 'btn-dark' : 'btn-light text-dark' ?> btn-ver-pagos-mes"
                                data-mes-key="<?= $mesKey ?>"
                                data-mes-nombre="<?= esc($e['nombre']) ?> <?= $anio ?>"
                                title="Ver pagos del mes">
                          <i class="fas fa-print"></i>
                        </button>
                      </div>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>

            <div class="mt-3 pt-3 border-top d-flex flex-wrap align-items-center" style="gap:.4rem">
              <span class="badge badge-success p-2">
                <i class="fas fa-check-circle mr-1"></i>Pagado
              </span>
              <span class="badge badge-warning p-2 text-dark">
                <i class="fas fa-adjust mr-1"></i>Con abono(s) — pago parcial pendiente
              </span>
              <span class="badge badge-danger p-2">
                <i class="fas fa-times-circle mr-1"></i>Pendiente
              </span>
              <span class="badge badge-secondary p-2">
                <i class="fas fa-clock mr-1"></i>Próximo
              </span>
              <span class="badge badge-light border p-2 text-muted"
                    title="Meses anteriores al inicio del ciclo o al primer pago registrado. No se consideran adeudos.">
                <i class="fas fa-minus mr-1"></i>Sin registro / No aplica
              </span>
            </div>

          <?php endif; ?>

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
                <span class="info-box-icon">
                  <i class="fas <?= $es_posgrado ? 'fa-book-open' : 'fa-calendar-check' ?>"></i>
                </span>
                <div class="info-box-content">
                  <span class="info-box-text"><?= $es_posgrado ? 'Materias' : 'Mensualidades' ?></span>
                  <span class="info-box-number">$<?= number_format($totales['mensualidades'], 2) ?></span>
                  <?php if ($es_posgrado): ?>
                    <span class="progress-description"><?= $pagados ?> materia(s) pagada(s)</span>
                  <?php else: ?>
                    <span class="progress-description"><?= $totales['mensualidades_cnt'] ?> mes(es) pagado(s)</span>
                  <?php endif; ?>
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
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    <?= $es_posgrado
                        ? $pendientes . ' materia(s) sin pagar'
                        : $pendientes . ' mes(es) sin pagar en ' . $anio ?>
                  </span>
                  <?php else: ?>
                  <span class="progress-description" style="color:#28a745">
                    <i class="fas fa-check mr-1"></i>
                    <?= $es_posgrado ? 'Materias al corriente' : 'Al corriente en ' . $anio ?>
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

<!-- ── Modal: detalle de pagos del mes ──────────────────────────────────── -->
<div class="modal fade" id="modal-pagos-mes" tabindex="-1" role="dialog" aria-labelledby="modal-pagos-mes-titulo">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 class="modal-title text-white" id="modal-pagos-mes-titulo">
          <i class="fas fa-list-ul mr-2"></i>
          Pagos — <span id="modal-mes-nombre"></span>
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body p-0">
        <table class="table table-sm table-striped mb-0">
          <thead class="thead-light">
            <tr>
              <th style="width:130px">Tipo</th>
              <th style="width:100px">Fecha</th>
              <th style="width:120px">Método</th>
              <th class="text-right" style="width:110px">Monto</th>
              <th>Folio</th>
              <th style="width:50px"></th>
            </tr>
          </thead>
          <tbody id="modal-pagos-body"></tbody>
          <tfoot id="modal-pagos-foot"></tfoot>
        </table>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
  const pagosPorMes = <?= json_encode($pagos_detalle_meses) ?>;
  const baseUrl     = '<?= base_url() ?>';

  function fmtMonto(n) {
    return '$' + parseFloat(n).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
  }

  $(document).on('click', '.btn-ver-pagos-mes', function () {
    const key    = $(this).data('mes-key');
    const nombre = $(this).data('mes-nombre');
    const rows   = pagosPorMes[key] || [];

    $('#modal-mes-nombre').text(nombre);

    let html  = '';
    let total = 0;

    rows.forEach(function (p) {
      total += p.monto;

      const tipoBadge = p.num_abono === null
        ? '<span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i>Pago completo</span>'
        : '<span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-adjust mr-1"></i>Abono ' + p.num_abono + '</span>';

      const folioCorto = p.folio
        ? '<code class="text-muted" style="font-size:.7rem">' + p.folio.substring(0, 18) + '&hellip;</code>'
        : '<span class="text-muted">—</span>';

      const btnPrint = p.folio
        ? '<a href="' + baseUrl + 'pagos/comprobante/' + encodeURIComponent(p.folio) + '" target="_blank" class="btn btn-xs btn-outline-primary" title="Reimprimir"><i class="fas fa-print"></i></a>'
        : '';

      html += '<tr>' +
        '<td>' + tipoBadge + '</td>' +
        '<td>' + (p.fecha || '—') + '</td>' +
        '<td>' + (p.metodo_pago || 'Efectivo') + '</td>' +
        '<td class="text-right font-weight-bold">' + fmtMonto(p.monto) + '</td>' +
        '<td>' + folioCorto + '</td>' +
        '<td class="text-center">' + btnPrint + '</td>' +
        '</tr>';
    });

    if (!html) {
      html = '<tr><td colspan="6" class="text-center text-muted py-3">Sin registros</td></tr>';
    }

    const footHtml = '<tr class="font-weight-bold bg-light">' +
      '<td colspan="3" class="text-right">Total pagado:</td>' +
      '<td class="text-right text-primary">' + fmtMonto(total) + '</td>' +
      '<td colspan="2"></td>' +
      '</tr>';

    $('#modal-pagos-body').html(html);
    $('#modal-pagos-foot').html(footHtml);
    $('#modal-pagos-mes').modal('show');
  });
})();
</script>
<?= $this->endSection() ?>