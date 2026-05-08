<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
@page {
    size: letter portrait;
    margin: 0;
}
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}
body {
    font-family: Helvetica, Arial, sans-serif;
    font-size: 10pt;
    color: #222;
}
.seccion {
    width: 100%;
    padding: 0.18in 0.30in 0.10in 0.30in;
}
.separador {
    border-top: 2px dashed #777;
    margin: 0 0.30in;
    padding: 2pt 0;
    text-align: center;
    font-size: 7pt;
    color: #888;
    letter-spacing: 1pt;
}
.enc-table {
    width: 100%;
    border-collapse: collapse;
    border-bottom: 2.5pt solid #003087;
    padding-bottom: 3pt;
    margin-bottom: 5pt;
}
.enc-logo-td {
    text-align: center;
    padding-bottom: 3pt;
}
.enc-titulo-td { vertical-align: middle; padding-top: 2pt; }
.enc-copia-td {
    width: 150pt;
    vertical-align: middle;
    text-align: right;
    padding-top: 2pt;
    padding-right: 21pt;
}
.titulo-doc {
    font-size: 13pt;
    font-weight: bold;
    color: #003087;
    letter-spacing: 0.5pt;
}
.subtitulo-doc { font-size: 9pt; color: #555; margin-top: 1pt; }
.copia-badge {
    display: inline-block;
    background: #003087;
    color: white;
    font-size: 7pt;
    font-weight: bold;
    padding: 3pt 6pt;
    border-radius: 2pt;
    letter-spacing: 0.5pt;
}
.datos-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 4pt;
}
.datos-table td {
    padding: 3pt 6pt;
    vertical-align: top;
    border: 0.5pt solid #cdd5e0;
    font-size: 9pt;
    line-height: 1.2;
}
.datos-table td.lbl {
    background: #eef2f9;
    font-weight: bold;
    color: #003087;
    width: 22%;
    white-space: nowrap;
}
.datos-table td.val { width: 28%; }
.meses-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 3pt;
    border: 0.5pt solid #cdd5e0;
}
.meses-table th {
    background: #eef2f9;
    font-weight: bold;
    color: #003087;
    font-size: 8.5pt;
    padding: 3pt 6pt;
    text-align: left;
    border: 0.5pt solid #cdd5e0;
}
.meses-table td {
    font-size: 8.5pt;
    padding: 3pt 6pt;
    border: 0.5pt solid #cdd5e0;
}
.monto-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 4pt;
    border: 1.5pt solid #003087;
    background: #eef2f9;
}
.monto-table td { padding: 2pt 8pt; vertical-align: middle; }
.monto-num { font-size: 16pt; font-weight: bold; color: #003087; }
.monto-letras { font-size: 8.5pt; color: #444; margin-top: 1pt; }
.pie-table { width: 100%; border-collapse: collapse; margin-top: 3pt; }
.pie-table td { font-size: 7.5pt; color: #888; vertical-align: bottom; padding-bottom: 0; }
.firma-linea {
    border-top: 0.75pt solid #aaa;
    width: 120pt;
    margin-top: 18pt;
    margin-bottom: 2pt;
}
.sello-digital {
    margin-top: 4pt;
    padding-top: 3pt;
    border-top: 0.5pt solid #ddd;
    font-size: 6pt;
    color: #bbb;
    word-wrap: break-word;
    word-break: break-all;
    line-height: 1.4;
}
</style>
</head>
<body>

<?php
$mesesNombres = ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
                 'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

$copias = ['COPIA PARA EL ALUMNO', 'COPIA PARA EL CAJERO'];
foreach ($copias as $copia):
?>

<div class="seccion">

  <!-- Encabezado -->
  <table class="enc-table">
    <tr>
      <td class="enc-logo-td" colspan="2">
        <?php if (! empty($logoBase64)): ?>
          <img src="<?= $logoBase64 ?>" style="width:90%; height:auto; max-height:52pt; display:block; margin:0 auto;">
        <?php else: ?>
          <div style="width:90%; height:38pt; background:#003087; color:white; text-align:center; font-size:12pt; font-weight:bold; padding-top:10pt; border-radius:3pt; margin:0 auto;">
            <?= esc(strtoupper($nivelLabel)) ?>
          </div>
        <?php endif; ?>
      </td>
    </tr>
    <tr>
      <td class="enc-titulo-td">
        <div class="titulo-doc">COMPROBANTE DE PAGO</div>
        <div class="subtitulo-doc"><?= esc($nivelLabel) ?> — Mensualidades</div>
      </td>
      <td class="enc-copia-td">
        <div class="copia-badge"><?= $copia ?></div>
      </td>
    </tr>
  </table>

  <!-- Datos del alumno y transacción -->
  <table class="datos-table">
    <tr>
      <td class="lbl">Folio de Lote</td>
      <td class="val" style="font-weight:bold;"><?= esc($folio_lote) ?></td>
      <td class="lbl">Fecha y Hora</td>
      <td class="val"><?= esc($fechaHora) ?></td>
    </tr>
    <tr>
      <td class="lbl">No. de Control</td>
      <td class="val"><?= esc($pago['num_control']) ?></td>
      <td class="lbl">Nivel</td>
      <td class="val"><?= esc($nivelLabel) ?></td>
    </tr>
    <tr>
      <td class="lbl">Nombre del Alumno</td>
      <?php if ($pago['nivel'] === 'uni' || $pago['nivel'] === 'posgrado'): ?>
        <td class="val"><?= esc($pago['nombre_alumno']) ?></td>
        <td class="lbl"><?= $pago['nivel'] === 'posgrado' ? 'Grado' : 'Carrera' ?></td>
        <td class="val"><?= esc($pago['nivel'] === 'posgrado' ? ($pago['modalidad'] ?? '—') : ($pago['carrera'] ?? '—')) ?></td>
      <?php else: ?>
        <td class="val" colspan="3"><?= esc($pago['nombre_alumno']) ?></td>
      <?php endif; ?>
    </tr>
    <tr>
      <td class="lbl">Concepto</td>
      <td class="val">Mensualidades</td>
      <td class="lbl">Cajero</td>
      <td class="val"><?= esc($nombreCajero) ?></td>
    </tr>
  </table>

  <!-- Detalle de meses pagados (horizontal) -->
  <table class="meses-table" style="margin-top:4pt;">
    <thead>
      <tr>
        <?php foreach ($pagos as $p):
          $mesNum = (int)$p['periodo_pago'];
          $mesAb  = substr($mesesNombres[$mesNum - 1] ?? "M{$mesNum}", 0, 3);
          $anioP  = $p['anio_mensualidad'] ?? date('Y', strtotime($p['created_at']));
        ?>
        <th style="text-align:center; font-size:8.5pt; width:1%; white-space:nowrap;">
          <?= esc($mesAb) ?> <?= $anioP ?>
          <?php if (!empty($p['num_abono'])): ?>
            <br><span style="font-size:6.5pt; font-weight:normal; color:#555;">Ab.<?= (int)$p['num_abono'] ?></span>
          <?php endif; ?>
        </th>
        <?php endforeach; ?>
      </tr>
    </thead>
    <tbody>
      <tr>
        <?php foreach ($pagos as $p): ?>
        <td style="text-align:center; font-size:8.5pt; width:1%; white-space:nowrap;">
          $<?= number_format((float)$p['monto'], 2) ?>
        </td>
        <?php endforeach; ?>
      </tr>
    </tbody>
  </table>

  <!-- Total -->
  <table class="monto-table">
    <tr>
      <td>
        <div class="monto-num"><?= esc($montoFormato) ?></div>
        <div class="monto-letras"><?= esc($montoLetras) ?></div>
      </td>
    </tr>
  </table>

  <!-- Pie: firma + QR al mismo nivel -->
  <?php $urlValidacion = base_url('validar-pago/' . ($pagos[0]['sello_digital'] ?? '')); ?>
  <table class="pie-table" style="margin-top:4pt;">
    <tr>
      <td style="vertical-align:bottom; text-align:left; width:33%;">
        <div class="firma-linea"></div>
        Firma y Sello
      </td>
      <td style="vertical-align:middle; text-align:center; width:34%;">
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=<?= urlencode($urlValidacion) ?>"
             style="width:58pt; height:58pt; border:0.5pt solid #ccc; padding:1pt;">
        <div style="font-size:5.5pt; color:#888; margin-top:2pt; line-height:1.2;">VERIFICAR AUTENTICIDAD</div>
      </td>
      <td style="width:33%;"></td>
    </tr>
  </table>

  <div class="sello-digital">
    <strong>LOTE:</strong> <?= esc($folio_lote) ?>
  </div>

</div>

<?php if ($copia === 'COPIA PARA EL ALUMNO'): ?>
<div class="separador"></div>
<?php endif; ?>

<?php endforeach; ?>
</body>
</html>
