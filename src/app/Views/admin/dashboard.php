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
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" aria-expanded="false">
          <i class="fas fa-user-shield mr-1"></i>
          <?= esc(service('session')->get('nombre') ?? '') ?>
          <span class="badge badge-warning ml-1">Admin</span>
        </a>
        <div class="dropdown-menu dropdown-menu-right shadow">
          <a class="dropdown-item" href="<?= base_url('configuracion/usuarios') ?>">
            <i class="fas fa-users-cog mr-2 text-primary"></i> Gestión de Usuarios
          </a>
          <a class="dropdown-item" href="<?= base_url('configuracion/password') ?>">
            <i class="fas fa-key mr-2 text-secondary"></i> Cambiar Contraseña
          </a>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item text-danger" href="<?= base_url('auth/login') ?>">
            <i class="fas fa-sign-out-alt mr-2"></i> Salir
          </a>
        </div>
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
          <li class="nav-item">
            <a href="<?= base_url('pagos-externos') ?>" class="nav-link">
              <i class="nav-icon fas fa-user-tag"></i>
              <p>Pagos Externos</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?= base_url('admin/reportes') ?>" class="nav-link">
              <i class="nav-icon fas fa-chart-bar"></i>
              <p>Reportes</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?= base_url('admin/conceptos') ?>" class="nav-link">
              <i class="nav-icon fas fa-cogs"></i>
              <p>Conceptos</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?= base_url('admin/estado-cuenta') ?>" class="nav-link">
              <i class="nav-icon fas fa-search-dollar"></i>
              <p>Estado de Cuenta</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?= base_url('admin/morosos') ?>" class="nav-link">
              <i class="nav-icon fas fa-user-clock"></i>
              <p>Morosos</p>
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
                  <colgroup>
                    <col style="width:17%">
                    <col>
                    <col style="width:12%">
                    <col style="width:9%">
                    <col style="width:10%">
                    <col style="width:11%">
                    <col style="width:9%">
                  </colgroup>
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
                      <td class="text-center text-nowrap">
                        <?php if (! empty($p['folio_digital'])): ?>
                          <a href="<?= base_url('pagos/comprobante/' . esc($p['folio_digital'])) ?>"
                             target="_blank"
                             class="btn btn-xs btn-outline-secondary"
                             title="Reimprimir comprobante">
                            <i class="fas fa-print"></i>
                          </a>
                        <?php endif; ?>
                        <a href="<?= base_url('admin/pagos/' . $p['id'] . '/editar') ?>"
                           class="btn btn-xs btn-outline-warning"
                           title="Editar pago">
                          <i class="fas fa-pencil-alt"></i>
                        </a>
                        <button type="button"
                                class="btn btn-xs btn-outline-danger btn-eliminar"
                                data-id="<?= $p['id'] ?>"
                                data-folio="<?= esc($p['folio_digital'] ?? $p['id']) ?>"
                                title="Eliminar pago">
                          <i class="fas fa-trash-alt"></i>
                        </button>
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

        <!-- ── Tabla de Pagos Externos Recientes ────────────────── -->
        <div class="row mt-2">
          <div class="col-12">
            <div class="card card-outline card-success">
              <div class="card-header">
                <h3 class="card-title">
                  <i class="fas fa-user-tag mr-2"></i>Últimos Pagos Externos / Aspirantes
                </h3>
              </div>
              <div class="card-body p-0">
                <table class="table table-striped table-hover table-sm mb-0">
                  <colgroup>
                    <col style="width:17%">
                    <col>
                    <col style="width:12%">
                    <col style="width:9%">
                    <col style="width:10%">
                    <col style="width:11%">
                    <col style="width:9%">
                  </colgroup>
                  <thead class="thead-light">
                    <tr>
                      <th>Folio</th>
                      <th>Cliente / Aspirante</th>
                      <th>Concepto</th>
                      <th class="text-right">Monto</th>
                      <th>Cajero</th>
                      <th>Fecha / Hora</th>
                      <th class="text-center">Acción</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($externosRecientes)): ?>
                      <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                          <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                          No hay pagos externos registrados aún.
                        </td>
                      </tr>
                    <?php else: ?>
                    <?php foreach ($externosRecientes as $e): ?>
                    <tr>
                      <td>
                        <code class="text-primary" style="font-size:0.78rem;">
                          <?= esc($e['folio_digital'] ?? '—') ?>
                        </code>
                      </td>
                      <td><?= esc($e['nombre_cliente']) ?></td>
                      <td><?= esc($e['concepto']) ?></td>
                      <td class="text-right font-weight-bold">
                        $<?= number_format((float) $e['monto'], 2) ?>
                      </td>
                      <td><?= esc($e['nombre_cajero'] ?? 'N/D') ?></td>
                      <td class="text-nowrap text-muted" style="font-size:0.82rem;">
                        <?= date('d/m/Y H:i', strtotime($e['created_at'])) ?>
                      </td>
                      <td class="text-center text-nowrap">
                        <?php if (! empty($e['folio_digital'])): ?>
                          <a href="<?= base_url('pagos-externos/comprobante/' . esc($e['folio_digital'])) ?>"
                             target="_blank"
                             class="btn btn-xs btn-outline-secondary"
                             title="Reimprimir recibo">
                            <i class="fas fa-print"></i>
                          </a>
                        <?php endif; ?>
                        <a href="<?= base_url('admin/pagos-externos/' . $e['id'] . '/editar') ?>"
                           class="btn btn-xs btn-outline-warning"
                           title="Editar">
                          <i class="fas fa-pencil-alt"></i>
                        </a>
                        <button type="button"
                                class="btn btn-xs btn-outline-danger btn-eliminar-externo"
                                data-id="<?= $e['id'] ?>"
                                data-folio="<?= esc($e['folio_digital'] ?? $e['id']) ?>"
                                title="Eliminar">
                          <i class="fas fa-trash-alt"></i>
                        </button>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        <!-- /tabla externos -->

      </div>
    </section>
  </div><!-- /.content-wrapper -->

  <footer class="main-footer text-sm">
    <strong>SistemaPagos</strong> &copy; <?= date('Y') ?>
  </footer>

</div><!-- /.wrapper -->

<!-- Modal Eliminar Pago Regular -->
<div class="modal fade" id="modal-eliminar" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title text-white">
          <i class="fas fa-exclamation-triangle mr-2"></i>Confirmar Eliminación
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>¿Estás seguro de eliminar el pago <strong id="folio-a-eliminar"></strong>?</p>
        <p class="text-danger mb-0"><i class="fas fa-exclamation-circle mr-1"></i>Esta acción no se puede deshacer.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">
          <i class="fas fa-times mr-1"></i> Cancelar
        </button>
        <form id="form-eliminar" method="post" style="display:inline">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-danger">
            <i class="fas fa-trash-alt mr-1"></i> Sí, eliminar
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal Eliminar Pago Externo -->
<div class="modal fade" id="modal-eliminar-externo" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title text-white">
          <i class="fas fa-exclamation-triangle mr-2"></i>Confirmar Eliminación
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>¿Estás seguro de eliminar el pago externo <strong id="folio-externo-a-eliminar"></strong>?</p>
        <p class="text-danger mb-0"><i class="fas fa-exclamation-circle mr-1"></i>Esta acción no se puede deshacer.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">
          <i class="fas fa-times mr-1"></i> Cancelar
        </button>
        <form id="form-eliminar-externo" method="post" style="display:inline">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-danger">
            <i class="fas fa-trash-alt mr-1"></i> Sí, eliminar
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script>
const BASE_URL = '<?= base_url() ?>';
$(function () {
  $(document).on('click', '.btn-eliminar', function () {
    const id    = $(this).data('id');
    const folio = $(this).data('folio');
    $('#folio-a-eliminar').text(folio);
    $('#form-eliminar').attr('action', BASE_URL + 'admin/pagos/' + id + '/eliminar');
    $('#modal-eliminar').modal('show');
  });

  $(document).on('click', '.btn-eliminar-externo', function () {
    const id    = $(this).data('id');
    const folio = $(this).data('folio');
    $('#folio-externo-a-eliminar').text(folio);
    $('#form-eliminar-externo').attr('action', BASE_URL + 'admin/pagos-externos/' + id + '/eliminar');
    $('#modal-eliminar-externo').modal('show');
  });
});
</script>
</body>
</html>
