<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $valido ? 'Pago Auténtico' : 'Recibo no válido' ?></title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
        font-family: Helvetica, Arial, sans-serif;
        background: #f0f4f8;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
        padding: 20px;
    }
    .card {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        max-width: 440px;
        width: 100%;
        overflow: hidden;
    }
    .card-header {
        padding: 28px 30px 20px;
        text-align: center;
    }
    .card-header.valido  { background: #003087; color: #fff; }
    .card-header.invalido { background: #c0392b; color: #fff; }
    .icono {
        font-size: 48px;
        margin-bottom: 10px;
    }
    .card-header h1 {
        font-size: 22px;
        font-weight: bold;
        letter-spacing: 0.5px;
    }
    .card-header p {
        font-size: 13px;
        margin-top: 5px;
        opacity: 0.85;
    }
    .card-body {
        padding: 24px 30px;
    }
    .dato {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #eee;
        font-size: 14px;
    }
    .dato:last-child { border-bottom: none; }
    .dato .etiqueta { color: #666; }
    .dato .valor { font-weight: bold; color: #222; text-align: right; max-width: 55%; }
    .card-footer {
        padding: 14px 30px;
        background: #f8f9fa;
        text-align: center;
        font-size: 11px;
        color: #aaa;
        border-top: 1px solid #eee;
    }
</style>
</head>
<body>

<div class="card">
  <?php if ($valido): ?>

    <div class="card-header valido">
      <div class="icono">&#10003;</div>
      <h1>Pago Auténtico</h1>
      <p>Este comprobante es válido y está registrado en el sistema.</p>
    </div>

    <div class="card-body">
      <div class="dato">
        <span class="etiqueta">Alumno</span>
        <span class="valor"><?= esc($nombre_alumno) ?></span>
      </div>
      <div class="dato">
        <span class="etiqueta">Concepto</span>
        <span class="valor"><?= esc($concepto) ?></span>
      </div>
      <div class="dato">
        <span class="etiqueta">Monto</span>
        <span class="valor"><?= esc($monto) ?></span>
      </div>
      <div class="dato">
        <span class="etiqueta">Folio</span>
        <span class="valor"><?= esc($folio) ?></span>
      </div>
      <div class="dato">
        <span class="etiqueta">Fecha</span>
        <span class="valor"><?= esc($fecha) ?></span>
      </div>
    </div>

  <?php else: ?>

    <div class="card-header invalido">
      <div class="icono">&#10007;</div>
      <h1>Recibo no válido</h1>
      <p>Este comprobante no se encontró en el sistema.</p>
    </div>

    <div class="card-body">
      <p style="text-align:center; color:#888; font-size:14px; padding: 10px 0;">
        El sello digital no corresponde a ningún pago registrado.<br>
        Si cree que es un error, contacte a la institución.
      </p>
    </div>

  <?php endif; ?>

  <div class="card-footer">
    Sistema de Pagos &mdash; Verificación de autenticidad
  </div>
</div>

</body>
</html>
