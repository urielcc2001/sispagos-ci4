<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard — SistemaPagos</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button">
          <i class="fas fa-bars"></i>
        </a>
      </li>
    </ul>
    <ul class="navbar-nav ml-auto">
      <li class="nav-item">
        <span class="nav-link text-muted">
          <i class="fas fa-user-shield mr-1"></i>
          <?= esc(service('session')->get('nombre') ?? '') ?>
          <span class="badge badge-warning ml-1">Admin</span>
        </span>
      </li>
      <li class="nav-item">
        <a href="<?= base_url('auth/login') ?>" class="nav-link text-danger">
          <i class="fas fa-sign-out-alt"></i> Salir
        </a>
      </li>
    </ul>
  </nav>

  <!-- Sidebar -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="<?= base_url('dashboard') ?>" class="brand-link px-3">
      <span class="brand-text font-weight-light"><b>Sistema</b>Pagos</span>
    </a>
    <div class="sidebar">
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
          <li class="nav-item">
            <a href="<?= base_url('dashboard') ?>" class="nav-link active">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Dashboard</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?= base_url('pagos') ?>" class="nav-link">
              <i class="nav-icon fas fa-money-bill-wave"></i>
              <p>Registrar Pago</p>
            </a>
          </li>
        </ul>
      </nav>
    </div>
  </aside>

  <!-- Content Wrapper -->
  <div class="content-wrapper">

    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Dashboard</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item active">
                <i class="fas fa-calendar-day mr-1"></i>
                <?= date('d/m/Y') ?>
              </li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid">

        <!-- ── Widgets KPI ───────────────────────────────────── -->
        <div class="row">

          <div class="col-lg-4 col-6">
            <div class="small-box bg-success">
              <div class="inner">
                <h3>$<?= number_format($totalHoy, 2) ?></h3>
                <p>Total Recaudado Hoy</p>
              </div>
              <div class="icon">
                <i class="fas fa-dollar-sign"></i>
              </div>
            </div>
          </div>

          <div class="col-lg-4 col-6">
            <div class="small-box bg-info">
              <div class="inner">
                <h3><?= $pagosHoy ?></h3>
                <p>Pagos Realizados Hoy</p>
              </div>
              <div class="icon">
                <i class="fas fa-file-invoice-dollar"></i>
              </div>
            </div>
          </div>

          <div class="col-lg-4 col-6">
            <div class="small-box bg-warning">
              <div class="inner">
                <h3><?= $alumnosHoy ?></h3>
                <p>Alumnos Atendidos Hoy</p>
              </div>
              <div class="icon">
                <i class="fas fa-user-graduate"></i>
              </div>
            </div>
          </div>

        </div>
        <!-- /widgets -->

        <!-- ── Tabla de Pagos Recientes ──────────────────────── -->
        <div class="row">
          <div class="col-12">
            <div class="card card-outline card-primary">
              <div class="card-header">
                <h3 class="card-title">
                  <i class="fas fa-history mr-2"></i>Últimos 10 Pagos
                </h3>
              </div>
              <div class="card-body p-0">
                <table class="table table-striped table-hover table-sm mb-0">
                  <thead class="thead-light">
                    <tr>
                      <th>Folio</th>
                      <th>Alumno</th>
                      <th>Concepto</th>
                      <th class="text-right">Monto</th>
                      <th>Cajero</th>
                      <th>Fecha / Hora</th>
                      <th class="text-center">Acción</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($pagosRecientes)): ?>
                      <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                          <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                          No hay pagos registrados aún.
                        </td>
                      </tr>
                    <?php else: ?>

                    <?php
                    $conceptoLabels = [
                        'inscripcion'   => 'Inscripción',
                        'reinscripcion' => 'Reinscripción',
                        'mensualidad'   => 'Mensualidad',
                        'tramite'       => 'Trámite',
                    ];
                    $detalleLabels = [
                        'constancia'     => 'Constancia',
                        'constancia_ext' => 'Constancia Ext.',
                        'historial'      => 'Historial',
                        'gafete'         => 'Gafete',
                    ];
                    ?>

                    <?php foreach ($pagosRecientes as $p): ?>
                    <?php
                        $concepto = $conceptoLabels[$p['concepto']] ?? $p['concepto'];
                        if ($p['concepto'] === 'tramite' && ! empty($p['detalle_tramite'])) {
                            $concepto .= ' / ' . ($detalleLabels[$p['detalle_tramite']] ?? $p['detalle_tramite']);
                        }
                    ?>
                    <tr>
                      <td>
                        <code class="text-primary" style="font-size:0.78rem;">
                          <?= esc($p['folio_digital'] ?? '—') ?>
                        </code>
                      </td>
                      <td><?= esc($p['nombre_alumno']) ?></td>
                      <td><?= esc($concepto) ?></td>
                      <td class="text-right font-weight-bold">
                        $<?= number_format((float) $p['monto'], 2) ?>
                      </td>
                      <td><?= esc($p['nombre_cajero'] ?? 'N/D') ?></td>
                      <td class="text-nowrap text-muted" style="font-size:0.82rem;">
                        <?= date('d/m/Y H:i', strtotime($p['created_at'])) ?>
                      </td>
                      <td class="text-center">
                        <?php if (! empty($p['folio_digital'])): ?>
                          <a href="<?= base_url('pagos/comprobante/' . esc($p['folio_digital'])) ?>"
                             target="_blank"
                             class="btn btn-xs btn-outline-secondary"
                             title="Reimprimir comprobante">
                            <i class="fas fa-print mr-1"></i>Reimprimir
                          </a>
                        <?php else: ?>
                          <span class="text-muted">—</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div><!-- /.card-body -->
            </div><!-- /.card -->
          </div>
        </div>
        <!-- /tabla -->

      </div>
    </section>
  </div><!-- /.content-wrapper -->

  <footer class="main-footer text-sm">
    <strong>SistemaPagos</strong> &copy; <?= date('Y') ?>
  </footer>

</div><!-- /.wrapper -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>
