<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
@page { size: letter portrait; margin: 0.45in 0.4in; }
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: Helvetica, Arial, sans-serif; font-size: 8pt; color: #1a1a1a; }

/* ── Encabezado ── */
.header { border-bottom: 2pt solid #003087; margin-bottom: 8pt; padding-bottom: 6pt; }
.header-title {
    font-size: 13pt; font-weight: bold; color: #003087;
    text-transform: uppercase; letter-spacing: 0.5pt;
}
.header-sub { font-size: 9pt; color: #555; margin-top: 2pt; }
.header-meta { font-size: 7.5pt; color: #888; margin-top: 4pt; }

/* ── Tabla de datos ── */
table { width: 100%; border-collapse: collapse; margin-top: 8pt; }
thead tr { background: #003087; color: white; }
thead th { padding: 4pt 5pt; font-size: 7.5pt; text-align: left; font-weight: bold; }
thead th.num { text-align: right; }
tbody tr:nth-child(even) { background: #f4f7fb; }
tbody td { padding: 3pt 5pt; font-size: 7.5pt; border-bottom: 0.3pt solid #d0d8e8; vertical-align: top; }
tbody td.num { text-align: right; font-variant-numeric: tabular-nums; }
tbody td.folio { font-size: 6.5pt; color: #003087; font-weight: bold; }

/* ── Fila de total ── */
.fila-total td {
    background: #003087; color: white;
    font-weight: bold; font-size: 8pt;
    padding: 4pt 5pt; border: none;
}
.fila-total td.num { text-align: right; }

/* ── Pie ── */
.footer { margin-top: 10pt; font-size: 6.5pt; color: #aaa; text-align: right; }
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
?>

<div class="header">
  <div class="header-title">Reporte de Ingresos</div>
  <div class="header-sub"><?= esc($rangoLabel) ?></div>
</div>

<table>
  <thead>
    <tr>
      <th style="width:14%">Folio</th>
      <th style="width:10%">Fecha</th>
      <th style="width:25%">Alumno</th>
      <th style="width:17%">Concepto</th>
      <th style="width:9%">Nivel</th>
      <th style="width:17%">Cajero</th>
      <th class="num" style="width:8%">Monto</th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($pagos)): ?>
    <tr>
      <td colspan="8" style="text-align:center; padding:12pt; color:#888;">
        No se encontraron registros con los filtros aplicados.
      </td>
    </tr>
    <?php else: ?>
    <?php foreach ($pagos as $p): ?>
    <?php
      $concepto = $conceptoLabels[$p['concepto']] ?? $p['concepto'];
      if ($p['concepto'] === 'tramite' && ! empty($p['detalle_tramite'])) {
          $concepto .= "\n" . $p['detalle_tramite'];
      }
    ?>
    <tr>
      <td class="folio"><?= esc($p['folio_digital'] ?? '—') ?></td>
      <td><?= date('d/m/Y', strtotime($p['created_at'])) ?><br>
          <span style="color:#999;font-size:6pt;"><?= date('H:i', strtotime($p['created_at'])) ?></span>
      </td>
      <td><?= esc($p['nombre_alumno']) ?></td>
      <td><?= nl2br(esc($concepto)) ?></td>
      <td><?= esc($nivelLabels[$p['nivel']] ?? $p['nivel']) ?></td>
      <td><?= esc($p['nombre_cajero'] ?? 'N/D') ?></td>
      <td class="num">$<?= number_format((float) $p['monto'], 2) ?></td>
    </tr>
    <?php endforeach; ?>
    <?php endif; ?>
  </tbody>
  <tfoot>
    <tr class="fila-total">
      <td colspan="6" style="text-align:right; padding-right:8pt;">TOTAL GENERAL</td>
      <td class="num">$<?= number_format((float) $totalGeneral, 2) ?></td>
    </tr>
  </tfoot>
</table>

<div class="footer">
  SistemaPagos &copy; <?= date('Y') ?> — Documento generado automáticamente. No requiere firma.
</div>

</body>
</html>
