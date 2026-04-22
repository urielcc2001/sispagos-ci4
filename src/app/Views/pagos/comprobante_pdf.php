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

/* ── Cada mitad de hoja ─────────────────────── */
.seccion {
    width: 100%;
    padding: 0.28in 0.35in 0.18in 0.35in;
}

/* ── Separador central ──────────────────────── */
.separador {
    border-top: 2px dashed #777;
    margin: 0 0.35in;
    padding: 3pt 0;
    text-align: center;
    font-size: 7pt;
    color: #888;
    letter-spacing: 1pt;
}

/* ── Encabezado ─────────────────────────────── */
.enc-table {
    width: 100%;
    border-collapse: collapse;
    border-bottom: 2.5pt solid #003087;
    padding-bottom: 5pt;
    margin-bottom: 8pt;
}
.enc-logo-td {
    text-align: center;
    padding-bottom: 5pt;
}
.enc-titulo-td {
    vertical-align: middle;
    padding-top: 4pt;
}
.enc-copia-td {
    width: 150pt; 
    vertical-align: middle;
    text-align: right;
    padding-top: 4pt;
    padding-right: 21pt; 
}
.logo-placeholder {
    width: 90%;
    height: 54pt;
    background: #003087;
    color: white;
    text-align: center;
    font-size: 14pt;
    font-weight: bold;
    padding-top: 16pt;
    border-radius: 3pt;
}
.titulo-doc {
    font-size: 13pt;
    font-weight: bold;
    color: #003087;
    letter-spacing: 0.5pt;
}
.subtitulo-doc {
    font-size: 9pt;
    color: #555;
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

/* ── Tabla de datos ─────────────────────────── */
.datos-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 6pt;
}
.datos-table td {
    padding: 4pt 7pt;
    vertical-align: top;
    border: 0.5pt solid #cdd5e0;
    font-size: 9pt;
    line-height: 1.3;
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

/* ── Bloque de monto ────────────────────────── */
.monto-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 8pt;
    border: 1.5pt solid #003087;
    background: #eef2f9;
}
.monto-table td {
    padding: 5pt 10pt;
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
    margin-top: 2pt;
}

/* ── Firma / pie ────────────────────────────── */
.pie-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10pt;
}
.pie-table td {
    font-size: 7.5pt;
    color: #888;
    vertical-align: bottom;
}
.firma-linea {
    border-top: 0.75pt solid #aaa;
    width: 130pt;
    margin-top: 22pt;
    margin-bottom: 2pt;
}

/* ── Sello digital ──────────────────────────── */
.sello-digital {
    margin-top: 7pt;
    padding-top: 5pt;
    border-top: 0.5pt solid #ddd;
    font-size: 6pt;
    color: #bbb;
    word-wrap: break-word;
    word-break: break-all;
    line-height: 1.5;
}
</style>
</head>
<body>

<?php
$copias = ['COPIA PARA EL ALUMNO', 'COPIA PARA EL CAJERO'];
foreach ($copias as $copia):
?>

<!-- ════════════════════════════════════════════
     SECCIÓN: <?= $copia ?>
     ════════════════════════════════════════════ -->
<div class="seccion">

  <!-- Encabezado -->
  <table class="enc-table">
    <!-- Fila 1: Logo institucional a todo el ancho -->
    <tr>
      <td class="enc-logo-td" colspan="2">
        <?php if (! empty($logoBase64)): ?>
          <img src="<?= $logoBase64 ?>" style="width:90%; height:auto; max-height:80pt; display:block; margin:0 auto;">
        <?php else: ?>
          <div class="logo-placeholder"><?= esc(strtoupper($nivelLabel)) ?></div>
        <?php endif; ?>
      </td>
    </tr>
    <!-- Fila 2: Título del documento + etiqueta de copia -->
    <tr>
      <td class="enc-titulo-td">
        <div class="titulo-doc">COMPROBANTE DE PAGO</div>
        <div class="subtitulo-doc"><?= esc($nivelLabel) ?></div>
      </td>
      <td class="enc-copia-td">
        <div class="copia-badge"><?= $copia ?></div>
      </td>
    </tr>
  </table>

  <!-- Datos del pago -->
  <table class="datos-table">
    <tr>
      <td class="lbl">Folio Digital</td>
      <td class="val" style="font-weight:bold;"><?= esc($pago['folio_digital']) ?></td>
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
      <td class="val" colspan="3"><?= esc($pago['nombre_alumno']) ?></td>
    </tr>
    <tr>
      <td class="lbl">Concepto</td>
      <td class="val"><?= esc($conceptoLabel) ?></td>
      <td class="lbl">Modalidad</td>
      <td class="val"><?= esc($pago['modalidad'] ?? '—') ?></td>
    </tr>
    <tr>
      <td class="lbl">Periodo</td>
      <td class="val" style="font-weight:bold;">
        <?= !empty($pago['periodo_pago']) ? esc($pago['periodo_pago']) : '—' ?>
      </td>
      <td class="lbl">Cajero</td>
      <td class="val">
        <?= esc($nombreCajero) ?>
      </td>
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

  <!-- Pie: firma + nota -->
  <table class="pie-table">
    <tr>
      <td style="width:50%; text-align:left;">
        <div class="firma-linea"></div>
        Firma y Sello
      </td>
      <td style="text-align:right; color:#aaa; padding-right: 29pt;">
        Generado el <?= date('d/m/Y H:i') ?><br>
        Folio: <?= esc($pago['folio_digital']) ?>
      </td>
    </tr>
  </table>

  <!-- Sello Digital de Seguridad -->
  <div class="sello-digital">
    <strong>SELLO DIGITAL:</strong> <?= esc($selloDigital) ?>
  </div>

</div>
<!-- fin sección -->

<?php if ($copia === 'COPIA PARA EL ALUMNO'): ?>
<div class="separador">
  ✂ &nbsp;&nbsp; — — — — — — — — — — — — — — — — — — — — — — — — — — — — — — — — — &nbsp;&nbsp; ✂
</div>
<?php endif; ?>

<?php endforeach; ?>
</body>
</html>
