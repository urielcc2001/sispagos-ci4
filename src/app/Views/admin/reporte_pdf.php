<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
@page {
    size: letter portrait;
    margin: 0;
}
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: Helvetica, Arial, sans-serif;
    font-size: 8pt;
    color: #1a1a1a;
    padding: 38pt 42pt;
}

/* ── Encabezado ──────────────────────────────────────────────── */
.header-wrap {
    width: 100%;
    border-bottom: 2.5pt solid #003087;
    padding-bottom: 7pt;
    margin-bottom: 10pt;
}
.titulo {
    font-size: 14pt;
    font-weight: bold;
    color: #003087;
    text-transform: uppercase;
    letter-spacing: 0.5pt;
}
.subtitulo { font-size: 8.5pt; color: #555; margin-top: 2pt; }
.meta      { font-size: 7pt; color: #999; margin-top: 4pt; }

/* ── Tabla ───────────────────────────────────────────────────── */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 0;
}

thead tr {
    background: #003087;
    color: white;
}
thead th {
    padding: 5pt 6pt;
    font-size: 7.5pt;
    font-weight: bold;
    text-align: left;
    letter-spacing: 0.2pt;
    white-space: nowrap;
}
thead th.r { text-align: right; }

tbody tr:nth-child(odd)  { background: #ffffff; }
tbody tr:nth-child(even) { background: #f0f4fb; }

tbody td {
    padding: 3.5pt 6pt;
    font-size: 7.5pt;
    border-bottom: 0.3pt solid #dde4f0;
    vertical-align: middle;
}
tbody td.r { text-align: right; font-variant-numeric: tabular-nums; }

td.folio {
    font-size: 6pt;
    color: #003087;
    font-weight: bold;
    font-family: Courier, monospace;
    word-break: break-all;
}
td.control {
    font-weight: bold;
    color: #333;
}
td.monto {
    font-weight: bold;
    color: #1a4a1a;
    text-align: right;
    font-variant-numeric: tabular-nums;
}
td.hora {
    font-size: 6pt;
    color: #aaa;
}

/* ── Fila de total ───────────────────────────────────────────── */
tfoot tr td {
    background: #002060;
    color: white;
    font-weight: bold;
    font-size: 8.5pt;
    padding: 5pt 6pt;
    border: none;
}
tfoot tr td.r { text-align: right; }

/* ── Pie ─────────────────────────────────────────────────────── */
.footer {
    margin-top: 8pt;
    font-size: 6pt;
    color: #bbb;
    text-align: right;
}
</style>
</head>
<body>

<?php
$conceptoLabels = [
    'inscripcion'   => 'Inscripción',
    'reinscripcion' => 'Reinscripción',
    'mensualidad'   => 'Mensualidad',
    'tramite'       => 'Trámite',
];
$nivelLabels = ['uni' => 'Universidad', 'prepa' => 'Bachillerato', 'posgrado' => 'Posgrado'];

$fi = $filtros['fechaInicio'] ?? null;
$ff = $filtros['fechaFin']    ?? null;

if ($fi && $ff && $fi === $ff) {
    $rangoLabel = 'Fecha: ' . date('d/m/Y', strtotime($fi));
} elseif ($fi && $ff) {
    $rangoLabel = 'Del ' . date('d/m/Y', strtotime($fi)) . ' al ' . date('d/m/Y', strtotime($ff));
} elseif ($fi) {
    $rangoLabel = 'Desde ' . date('d/m/Y', strtotime($fi));
} elseif ($ff) {
    $rangoLabel = 'Hasta ' . date('d/m/Y', strtotime($ff));
} else {
    $rangoLabel = 'Todos los registros';
}

$numRegistros = count($pagos);
?>

<!-- Encabezado -->
<div class="header-wrap">
  <div class="titulo">Reporte de Ingresos</div>
  <div class="subtitulo"><?= esc($rangoLabel) ?> &nbsp;·&nbsp; <?= $numRegistros ?> registro<?= $numRegistros !== 1 ? 's' : '' ?></div>
  <div class="meta">Generado el <?= date('d/m/Y \a \l\a\s H:i') ?></div>
</div>

<!-- Tabla de registros -->
<table>
  <thead>
    <tr>
      <th style="width:22%">Folio</th>
      <th style="width:11%">Fecha</th>
      <th style="width:11%">No. Control</th>
      <th style="width:18%">Concepto</th>
      <th style="width:12%">Nivel</th>
      <th style="width:15%">Cajero</th>
      <th class="r" style="width:11%">Monto</th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($pagos)): ?>
    <tr>
      <td colspan="7" style="text-align:center; padding:14pt; color:#aaa;">
        No se encontraron registros con los filtros aplicados.
      </td>
    </tr>
    <?php else: ?>
    <?php foreach ($pagos as $p): ?>
    <?php
        $concepto = $conceptoLabels[$p['concepto']] ?? $p['concepto'];
        if ($p['concepto'] === 'tramite' && ! empty($p['detalle_tramite'])) {
            $concepto .= ' — ' . $p['detalle_tramite'];
        }
    ?>
    <tr>
      <td class="folio"><?= esc($p['folio_digital'] ?? '—') ?></td>
      <td>
        <?= date('d/m/Y', strtotime($p['created_at'])) ?>
        <br><span class="hora"><?= date('H:i', strtotime($p['created_at'])) ?></span>
      </td>
      <td class="control"><?= esc($p['num_control'] ?? '—') ?></td>
      <td><?= esc($concepto) ?></td>
      <td><?= esc($nivelLabels[$p['nivel']] ?? $p['nivel']) ?></td>
      <td><?= esc($p['nombre_cajero'] ?? 'N/D') ?></td>
      <td class="monto">$<?= number_format((float) $p['monto'], 2) ?></td>
    </tr>
    <?php endforeach; ?>
    <?php endif; ?>
  </tbody>
  <tfoot>
    <tr>
      <td colspan="6" class="r" style="padding-right:8pt; letter-spacing:0.3pt;">TOTAL GENERAL</td>
      <td class="r">$<?= number_format((float) $totalGeneral, 2) ?></td>
    </tr>
  </tfoot>
</table>

<div class="footer">
  SistemaPagos &copy; <?= date('Y') ?> — Documento generado automáticamente. No requiere firma.
</div>

</body>
</html>
