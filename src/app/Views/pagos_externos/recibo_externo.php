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
    padding: 0.22in 0.30in 0.12in 0.30in;
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
.enc-titulo-td {
    vertical-align: middle;
    padding-top: 2pt;
}
.enc-copia-td {
    width: 150pt;
    vertical-align: middle;
    text-align: right;
    padding-top: 2pt;
    padding-right: 21pt;
}
.logo-placeholder {
    width: 90%;
    height: 38pt;
    background: #003087;
    color: white;
    text-align: center;
    font-size: 12pt;
    font-weight: bold;
    padding-top: 10pt;
    border-radius: 3pt;
}
.titulo-doc {
    font-size: 13pt;
    font-weight: bold;
    color: #003087;
    letter-spacing: 0.5pt;
}
.subtitulo-doc {
    font-size: 8.5pt;
    color: #555;
    margin-top: 1pt;
}
.tag-externo {
    display: inline-block;
    background: #e67e22;
    color: white;
    font-size: 6.5pt;
    font-weight: bold;
    padding: 2pt 5pt;
    border-radius: 2pt;
    letter-spacing: 0.5pt;
    margin-top: 2pt;
}
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
.datos-table td.val {
    width: 28%;
}

.monto-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 4pt;
    border: 1.5pt solid #003087;
    background: #eef2f9;
}
.monto-table td {
    padding: 3pt 8pt;
    vertical-align: middle;
}
.monto-num {
    font-size: 16pt;
    font-weight: bold;
    color: #003087;
}
.monto-letras {
    font-size: 8.5pt;
    color: #444;
    margin-top: 1pt;
}

.pie-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 6pt;
}
.pie-table td {
    font-size: 7.5pt;
    color: #888;
    vertical-align: bottom;
    padding-bottom: 0;
}
.firma-linea {
    border-top: 0.75pt solid #aaa;
    width: 120pt;
    margin-top: 18pt;
    margin-bottom: 2pt;
}
.qr-label {
    font-size: 5.5pt;
    color: #888;
    margin-top: 2pt;
    text-align: center;
    line-height: 1.2;
    letter-spacing: 0.3pt;
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
$copias = ['COPIA PARA EL CLIENTE', 'COPIA PARA EL CAJERO'];
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
          <div class="logo-placeholder"><?= esc(strtoupper($nivelLabel)) ?></div>
        <?php endif; ?>
      </td>
    </tr>
    <tr>
      <td class="enc-titulo-td">
        <div class="titulo-doc">RECIBO DE PAGO</div>
        <div class="subtitulo-doc"><?= esc($nivelLabel) ?></div>
        <div class="tag-externo">PAGO EXTERNO / ASPIRANTE</div>
      </td>
      <td class="enc-copia-td">
        <div class="copia-badge"><?= $copia ?></div>
      </td>
    </tr>
  </table>

  <!-- Datos del recibo -->
  <table class="datos-table">
    <tr>
      <td class="lbl">Folio</td>
      <td class="val" style="font-weight:bold;"><?= esc($pago['folio_digital']) ?></td>
      <td class="lbl">Fecha y Hora</td>
      <td class="val"><?= esc($fechaHora) ?></td>
    </tr>
    <tr>
      <td class="lbl">Cliente / Aspirante</td>
      <td class="val" colspan="3" style="font-weight:bold;"><?= esc($pago['nombre_cliente']) ?></td>
    </tr>
    <tr>
      <td class="lbl">Nivel</td>
      <td class="val"><?= esc($nivelLabel) ?></td>
      <td class="lbl">Modalidad</td>
      <td class="val"><?= esc($pago['modalidad'] ?? '—') ?></td>
    </tr>
    <tr>
      <td class="lbl">Concepto</td>
      <td class="val" colspan="3" style="font-weight:bold;"><?= esc($pago['concepto']) ?></td>
    </tr>
    <tr>
      <td class="lbl">Método de Pago</td>
      <td class="val"><?= esc($pago['metodo_pago']) ?></td>
      <td class="lbl">Cajero</td>
      <td class="val"><?= esc($nombreCajero) ?></td>
    </tr>
  </table>

  <!-- Monto -->
  <table class="monto-table">
    <tr>
      <td>
        <div class="monto-num"><?= esc($montoFormato) ?></div>
        <div class="monto-letras"><?= esc($montoLetras) ?></div>
      </td>
    </tr>
  </table>

  <!-- Firma + QR -->
  <?php $urlValidacion = base_url('pagos-externos/validar/' . ($pago['sello_digital'] ?? '')); ?>
  <table class="pie-table">
    <tr>
      <td style="vertical-align:bottom; text-align:left;">
        <div class="firma-linea"></div>
        Firma y Sello
      </td>
    </tr>
  </table>
  <div style="text-align:center; margin-top:-2pt;">
    <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=<?= urlencode($urlValidacion) ?>"
         style="width:60pt; height:60pt; display:block; margin:0 auto; border:0.5pt solid #ccc; padding:1pt;">
    <div class="qr-label">VERIFICAR AUTENTICIDAD</div>
  </div>

  <!-- Sello Digital de Seguridad -->
  <div class="sello-digital">
    <strong>SELLO DIGITAL:</strong> <?= esc($selloDigital) ?>
  </div>

</div>

<?php if ($copia === 'COPIA PARA EL CLIENTE'): ?>
<div class="separador">
  ✂ &nbsp;&nbsp; — — — — — — — — — — — — — — — — — — — — — — — — — — — — — — — — — &nbsp;&nbsp; ✂
</div>
<?php endif; ?>

<?php endforeach; ?>
</body>
</html>
