<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Iniciar Sesión — SistemaPagos</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700&display=fallback">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <style>
    *, *::before, *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(145deg, #001f5c 0%, #003087 45%, #1055a8 100%);
      font-family: 'Source Sans Pro', sans-serif;
      padding: 20px;
    }

    .login-wrapper {
      width: 100%;
      max-width: 430px;
    }

    .login-card {
      background: #ffffff;
      border-radius: 18px;
      box-shadow:
        0 24px 64px rgba(0, 0, 0, 0.28),
        0 6px 18px rgba(0, 48, 135, 0.18);
      overflow: hidden;
    }

    /* ── Banner / imagen superior ── */
    .login-banner {
      width: 100%;
      max-height: 210px;
      object-fit: cover;
      object-position: center;
      display: block;
    }

    /* ── Cuerpo del formulario ── */
    .login-body {
      padding: 36px 40px 40px;
    }

    .login-title {
      text-align: center;
      font-size: 1.45rem;
      font-weight: 700;
      color: #003087;
      letter-spacing: 0.3px;
      margin-bottom: 4px;
    }

    .login-subtitle {
      text-align: center;
      color: #8a94a6;
      font-size: 0.9rem;
      margin-bottom: 28px;
    }

    /* ── Alerta de error ── */
    .alert-error {
      display: flex;
      align-items: center;
      gap: 10px;
      background: #fff5f5;
      border: 1px solid #f5c6c6;
      color: #c0392b;
      border-radius: 10px;
      padding: 11px 14px;
      font-size: 0.88rem;
      margin-bottom: 22px;
    }

    /* ── Grupos de input ── */
    .field-group {
      display: flex;
      align-items: center;
      border: 1.5px solid #d0d7e3;
      border-radius: 11px;
      overflow: hidden;
      margin-bottom: 16px;
      transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .field-group:focus-within {
      border-color: #003087;
      box-shadow: 0 0 0 3px rgba(0, 48, 135, 0.12);
    }

    .field-icon {
      width: 48px;
      height: 50px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #f4f7fc;
      border-right: 1.5px solid #d0d7e3;
      color: #9aa3b2;
      flex-shrink: 0;
      transition: color 0.2s ease;
    }

    .field-group:focus-within .field-icon {
      color: #003087;
    }

    .field-group input {
      flex: 1;
      height: 50px;
      border: none;
      outline: none;
      padding: 0 16px;
      font-size: 0.96rem;
      font-family: inherit;
      color: #1a1a2e;
      background: transparent;
    }

    .field-group input::placeholder {
      color: #b0b9c8;
    }

    /* ── Botón ── */
    .btn-login {
      width: 100%;
      height: 50px;
      margin-top: 8px;
      background: #003087;
      color: #fff;
      border: none;
      border-radius: 11px;
      font-size: 1rem;
      font-weight: 600;
      font-family: inherit;
      letter-spacing: 0.4px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      transition: background 0.2s ease, box-shadow 0.2s ease, transform 0.15s ease;
    }

    .btn-login:hover {
      background: #00236b;
      box-shadow: 0 8px 24px rgba(0, 48, 135, 0.38);
      transform: translateY(-2px);
    }

    .btn-login:active {
      transform: translateY(0);
      box-shadow: none;
    }

    /* ── Pie del card ── */
    .login-footer {
      text-align: center;
      padding: 13px 16px;
      font-size: 0.78rem;
      color: #b0b9c8;
      border-top: 1px solid #f0f2f8;
      letter-spacing: 0.2px;
    }

    /* ── Responsive ── */
    @media (max-width: 480px) {
      .login-body {
        padding: 28px 24px 32px;
      }
    }
  </style>
</head>
<body>

<div class="login-wrapper">
  <div class="login-card">

    <img
      src="<?= base_url('assets/img/inicio.jpeg') ?>"
      alt="Sistema de Pagos"
      class="login-banner"
    >

    <div class="login-body">
      <h1 class="login-title">Bienvenido</h1>
      <p class="login-subtitle">Ingresa tus credenciales para continuar</p>

      <?php if (isset($error)): ?>
        <div class="alert-error">
          <i class="fas fa-exclamation-circle fa-sm"></i>
          <?= esc($error) ?>
        </div>
      <?php endif; ?>

      <form action="<?= base_url('auth/login') ?>" method="post">
        <?= csrf_field() ?>

        <div class="field-group">
          <span class="field-icon"><i class="fas fa-user fa-sm"></i></span>
          <input
            type="text"
            name="usuario"
            placeholder="Usuario"
            required
            autocomplete="username"
          >
        </div>

        <div class="field-group">
          <span class="field-icon"><i class="fas fa-lock fa-sm"></i></span>
          <input
            type="password"
            name="password"
            placeholder="Contraseña"
            required
            autocomplete="current-password"
          >
        </div>

        <button type="submit" class="btn-login">
          <i class="fas fa-sign-in-alt"></i> Entrar
        </button>
      </form>
    </div>

    <div class="login-footer">
      SistemaPagos &copy; <?= date('Y') ?>
    </div>

  </div>
</div>

</body>
</html>
