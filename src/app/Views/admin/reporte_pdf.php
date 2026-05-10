<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
@page {
    size: letter landscape;
    margin: 0;
}
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: Helvetica, Arial, sans-serif;
    font-size: 8pt;
    color: #1a1a1a;
    padding: 24pt 42pt 38pt;
}

.header-wrap {
    width: 100%;
    border-bottom: 2.5pt solid #003087;
    padding-bottom: 5pt;
    margin-bottom: 7pt;
}
.titulo {
    font-size: 11pt;
    font-weight: bold;
    color: #003087;
    text-transform: uppercase;
    letter-spacing: 0.5pt;
}
.subtitulo { font-size: 8.5pt; color: #555; margin-top: 1pt; }
.meta      { font-size: 7pt; color: #999; margin-top: 2pt; }

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
tbody td.r { text-align: right; }

td.folio {
    font-size: 6pt;
    color: #003087;
    font-weight: bold;
    font-family: Courier, monospace;
    word-break: break-all;
}
td.tipo-alumno {
    font-size: 6.5pt;
    font-weight: bold;
    color: #fff;
    background: #003087;
    text-align: center;
    white-space: nowrap;
}
td.tipo-externo {
    font-size: 6.5pt;
    font-weight: bold;
    color: #fff;
    background: #b45309;
    text-align: center;
    white-space: nowrap;
}
td.nombre {
    font-weight: bold;
    color: #333;
}
td.monto {
    font-weight: bold;
    color: #1a4a1a;
    text-align: right;
}
td.hora {
    font-size: 6pt;
    color: #aaa;
}
td.obs {
    font-size: 6.5pt;
    color: #555;
    font-style: italic;
}

.subtotal-row td {
    background: #d6e4f7;
    color: #002060;
    font-weight: bold;
    font-size: 7.5pt;
    padding: 3.5pt 6pt;
    border: none;
}
.subtotal-row td.r { text-align: right; }

tfoot tr td {
    background: #002060;
    color: white;
    font-weight: bold;
    font-size: 8.5pt;
    padding: 5pt 6pt;
    border: none;
}
tfoot tr td.r { text-align: right; }

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
    'inscripcion'   => 'Inscripcion',
    'reinscripcion' => 'Reinscripcion',
    'mensualidad'   => 'Mensualidad',
    'tramite'       => 'Tramite',
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

$origen = $filtros['origen'] ?? '';
if ($origen === 'alumnos')       $origenLabel = ' - Solo Alumnos';
elseif ($origen === 'externos')  $origenLabel = ' - Solo Externos';
else                             $origenLabel = '';

$numRegistros           = count($pagos);
$totalEfectivoPdf       = (float) ($totalEfectivo       ?? 0);
$totalTransferenciaPdf  = (float) ($totalTransferencia  ?? 0);
$totalDepositoPdf       = (float) ($totalDeposito       ?? 0);
$totalTarjetaDebitoPdf  = (float) ($totalTarjetaDebito  ?? 0);
$totalTarjetaCreditoPdf = (float) ($totalTarjetaCredito ?? 0);
$subtotalesPdf = array_filter([
    'Efectivo'          => $totalEfectivoPdf,
    'Transferencia'     => $totalTransferenciaPdf,
    'Dep. Bancario'     => $totalDepositoPdf,
    'T. Débito'         => $totalTarjetaDebitoPdf,
    'T. Crédito'        => $totalTarjetaCreditoPdf,
], fn($v) => $v > 0);
$hayVariosMetodosPdf = count($subtotalesPdf) > 1;
?>

<div class="header-wrap">
  <div class="titulo">Reporte Financiero Integral</div>
  <div class="subtitulo">
    <?= esc($rangoLabel) ?><?= esc($origenLabel) ?>
    &nbsp;·&nbsp;
    <?= $numRegistros ?> registro<?= $numRegistros !== 1 ? 's' : '' ?>
  </div>
  <div class="meta">Generado el <?= date('d/m/Y \a \l\a\s H:i') ?></div>
</div>

<table>
  <thead>
    <tr>
      <th style="width:11%">Folio</th>
      <th style="width:4%">Tipo</th>
      <th style="width:6%">Fecha</th>
      <th style="width:13%">Nombre</th>
      <th style="width:11%">Concepto</th>
      <th style="width:5%">Nivel</th>
      <th style="width:7%">Cajero</th>
      <th class="r" style="width:7%">Efectivo</th>
      <th class="r" style="width:7%">Transf.</th>
      <th class="r" style="width:7%">Dep. Ban.</th>
      <th class="r" style="width:7%">T. Déb.</th>
      <th class="r" style="width:7%">T. Cré.</th>
      <th style="width:8%">Observaciones</th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($pagos)): ?>
    <tr>
      <td colspan="13" style="text-align:center; padding:14pt; color:#aaa;">
        No se encontraron registros con los filtros aplicados.
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
                $concepto .= ' - ' . $p['detalle_tramite'];
            }
        } else {
            $concepto = $p['concepto'];
        }
        $nivelLabel = ! empty($p['nivel']) ? ($nivelLabels[$p['nivel']] ?? $p['nivel']) : '---';
    ?>
    <tr>
      <td class="folio"><?= esc($p['folio_digital'] ?? '---') ?></td>
      <td class="<?= $p['tipo_pago'] === 'alumno' ? 'tipo-alumno' : 'tipo-externo' ?>">
        <?= $p['tipo_pago'] === 'alumno' ? 'ALUMNO' : 'EXTERNO' ?>
      </td>
      <td>
        <?= date('d/m/Y', strtotime($p['created_at'])) ?>
        <br><span class="hora"><?= date('H:i', strtotime($p['created_at'])) ?></span>
      </td>
      <td class="nombre"><?= esc($p['nombre'] ?? '---') ?></td>
      <td><?= esc($concepto) ?></td>
      <td><?= esc($nivelLabel) ?></td>
      <td><?= esc($p['nombre_cajero'] ?? 'N/D') ?></td>
      <?php $__m = $p['metodo_pago'] ?? 'Efectivo'; ?>
      <td class="monto"><?= in_array($__m, ['Efectivo', ''])    ? '$' . number_format((float) $p['monto'], 2) : '—' ?></td>
      <td class="monto"><?= $__m === 'Transferencia'            ? '$' . number_format((float) $p['monto'], 2) : '—' ?></td>
      <td class="monto"><?= $__m === 'Depósito bancario'        ? '$' . number_format((float) $p['monto'], 2) : '—' ?></td>
      <td class="monto"><?= $__m === 'Tarjeta de débito'        ? '$' . number_format((float) $p['monto'], 2) : '—' ?></td>
      <td class="monto"><?= $__m === 'Tarjeta de crédito'       ? '$' . number_format((float) $p['monto'], 2) : '—' ?></td>
      <td class="obs"><?= ! empty($p['observaciones']) ? esc($p['observaciones']) : '—' ?></td>
    </tr>
    <?php endforeach; ?>

    <?php if ($hayVariosMetodosPdf): ?>
    <?php foreach ($subtotalesPdf as $__label => $__sub): ?>
    <tr class="subtotal-row">
      <td colspan="7" class="r" style="padding-right:8pt;">Total <?= esc($__label) ?></td>
      <td class="r"><?= $__label === 'Efectivo'      ? '$' . number_format($__sub, 2) : '—' ?></td>
      <td class="r"><?= $__label === 'Transferencia' ? '$' . number_format($__sub, 2) : '—' ?></td>
      <td class="r"><?= $__label === 'Dep. Bancario' ? '$' . number_format($__sub, 2) : '—' ?></td>
      <td class="r"><?= $__label === 'T. Débito'     ? '$' . number_format($__sub, 2) : '—' ?></td>
      <td class="r"><?= $__label === 'T. Crédito'    ? '$' . number_format($__sub, 2) : '—' ?></td>
      <td></td>
    </tr>
    <?php endforeach; ?>
    <?php endif; ?>

    <?php endif; ?>
  </tbody>
  <tfoot>
    <tr>
      <td colspan="7" class="r" style="padding-right:8pt; letter-spacing:0.3pt;">TOTAL</td>
      <td colspan="6" class="r" style="font-size:9pt;">$<?= number_format((float) $totalGeneral, 2) ?></td>
    </tr>
  </tfoot>
</table>

<div class="footer">
  SistemaPagos &copy; <?= date('Y') ?> — Documento generado automaticamente. No requiere firma.
</div>

</body>
</html>