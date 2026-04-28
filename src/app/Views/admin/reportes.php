<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reportes — SistemaPagos</title>
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
            <a href="<?= base_url('dashboard') ?>" class="nav-link">
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
            <a href="<?= base_url('admin/reportes') ?>" class="nav-link active">
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
            <h1 class="m-0"><i class="fas fa-chart-bar mr-2 text-primary"></i>Reportes de Pagos</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
              <li class="breadcrumb-item active">Reportes</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid">

        <?php if (session()->getFlashdata('success')): ?>
          <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle mr-1"></i>
            <?= esc(session()->getFlashdata('success')) ?>
          </div>
        <?php endif; ?>

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
        $nivelLabels = [
            'uni'      => 'Universidad',
            'prepa'    => 'Bachillerato',
            'posgrado' => 'Posgrado',
        ];
        ?>

        <!-- ── Barra de Filtros ──────────────────────────────────── -->
        <div class="card card-outline card-primary">
          <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter mr-2"></i>Filtros</h3>
          </div>
          <div class="card-body">
            <form method="get" action="<?= base_url('admin/reportes') ?>">
              <div class="row align-items-end">

                <!-- Periodo predefinido -->
                <div class="col-md-3">
                  <div class="form-group mb-0">
                    <label class="text-xs text-uppercase text-muted font-weight-bold">Periodo rápido</label>
                    <div class="btn-group d-flex" role="group">
                      <a href="<?= base_url('admin/reportes?periodo=hoy') ?>"
                         class="btn btn-sm <?= ($filtros['periodo'] ?? '') === 'hoy' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                        Hoy
                      </a>
                      <a href="<?= base_url('admin/reportes?periodo=semana') ?>"
                         class="btn btn-sm <?= ($filtros['periodo'] ?? '') === 'semana' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                        Semana
                      </a>
                      <a href="<?= base_url('admin/reportes?periodo=mes') ?>"
                         class="btn btn-sm <?= ($filtros['periodo'] ?? '') === 'mes' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                        Mes
                      </a>
                    </div>
                  </div>
                </div>

                <!-- Rango de fechas -->
                <div class="col-md-2">
                  <div class="form-group mb-0">
                    <label class="text-xs text-uppercase text-muted font-weight-bold">Fecha inicio</label>
                    <input type="date" name="fecha_inicio" class="form-control form-control-sm"
                           value="<?= esc($filtros['fechaInicio'] ?? '') ?>">
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group mb-0">
                    <label class="text-xs text-uppercase text-muted font-weight-bold">Fecha fin</label>
                    <input type="date" name="fecha_fin" class="form-control form-control-sm"
                           value="<?= esc($filtros['fechaFin'] ?? '') ?>">
                  </div>
                </div>

                <!-- Cajero -->
                <div class="col-md-2">
                  <div class="form-group mb-0">
                    <label class="text-xs text-uppercase text-muted font-weight-bold">Cajero</label>
                    <select name="id_cajero" class="form-control form-control-sm">
                      <option value="">— Todos —</option>
                      <?php foreach ($cajeros as $c): ?>
                        <option value="<?= $c['id'] ?>"
                          <?= ($filtros['idCajero'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                          <?= esc($c['nombre']) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>

                <!-- Nivel -->
                <div class="col-md-2">
                  <div class="form-group mb-0">
                    <label class="text-xs text-uppercase text-muted font-weight-bold">Nivel</label>
                    <select name="nivel" class="form-control form-control-sm">
                      <option value="">— Todos —</option>
                      <option value="uni"      <?= ($filtros['nivel'] ?? '') === 'uni'      ? 'selected' : '' ?>>Universidad</option>
                      <option value="prepa"    <?= ($filtros['nivel'] ?? '') === 'prepa'    ? 'selected' : '' ?>>Bachillerato</option>
                      <option value="posgrado" <?= ($filtros['nivel'] ?? '') === 'posgrado' ? 'selected' : '' ?>>Posgrado</option>
                    </select>
                  </div>
                </div>

                <!-- Botones -->
                <div class="col-md-1">
                  <button type="submit" class="btn btn-primary btn-sm btn-block">
                    <i class="fas fa-search"></i>
                  </button>
                </div>

              </div><!-- /.row -->
            </form>
          </div>
        </div>
        <!-- /filtros -->

        <!-- ── Resumen Rápido ─────────────────────────────────────── -->
        <div class="row">
          <div class="col-lg-4 col-md-6">
            <div class="small-box bg-success">
              <div class="inner">
                <h3 style="font-size:2rem;">$<?= number_format((float) $totalGeneral, 2) ?></h3>
                <p>Total Recaudado en el Periodo</p>
              </div>
              <div class="icon">
                <i class="fas fa-dollar-sign"></i>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6">
            <div class="small-box bg-info">
              <div class="inner">
                <h3><?= count($pagos) ?></h3>
                <p>Pagos encontrados</p>
              </div>
              <div class="icon">
                <i class="fas fa-file-invoice-dollar"></i>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-12 d-flex align-items-center justify-content-end">
            <?php
            $exportParams = array_filter([
                'fecha_inicio' => $filtros['fechaInicio'] ?? '',
                'fecha_fin'    => $filtros['fechaFin']    ?? '',
                'id_cajero'    => $filtros['idCajero']    ?? '',
                'nivel'        => $filtros['nivel']        ?? '',
            ]);
            $exportQuery = $exportParams ? '?' . http_build_query($exportParams) : '';
            ?>
            <div>
              <a href="<?= base_url('admin/exportar/csv') . $exportQuery ?>"
                 class="btn btn-success btn-lg"
                 title="Descargar los registros filtrados como CSV (Excel)">
                <i class="fas fa-file-csv mr-2"></i>Descargar CSV
              </a>
              <a href="<?= base_url('admin/exportar/pdf') . $exportQuery ?>"
                 class="btn btn-danger btn-lg ml-2"
                 title="Descargar los registros filtrados como PDF">
                <i class="fas fa-file-pdf mr-2"></i>Descargar PDF
              </a>
            </div>
          </div>
        </div>
        <!-- /resumen -->

        <!-- ── Tabla de Resultados ──────────────────────────────── -->
        <div class="card card-outline card-primary">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-table mr-2"></i>Resultados
            </h3>
          </div>
          <div class="card-body p-0">
            <table class="table table-striped table-hover table-sm mb-0">
              <thead class="thead-light">
                <tr>
                  <th>Folio</th>
                  <th>Fecha / Hora</th>
                  <th>Alumno</th>
                  <th>Nivel</th>
                  <th>Concepto</th>
                  <th>Cajero</th>
                  <th class="text-right">Monto</th>
                  <th class="text-center">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($pagos)): ?>
                  <tr>
                    <td colspan="8" class="text-center text-muted py-5">
                      <i class="fas fa-search fa-2x mb-2 d-block"></i>
                      No se encontraron pagos con los filtros seleccionados.
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($pagos as $p): ?>
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
                    <td class="text-nowrap text-muted" style="font-size:0.82rem;">
                      <?= date('d/m/Y H:i', strtotime($p['created_at'])) ?>
                    </td>
                    <td><?= esc($p['nombre_alumno']) ?></td>
                    <td>
                      <span class="badge badge-<?= $p['nivel'] === 'uni' ? 'primary' : ($p['nivel'] === 'prepa' ? 'warning' : 'secondary') ?>">
                        <?= esc($nivelLabels[$p['nivel']] ?? $p['nivel']) ?>
                      </span>
                    </td>
                    <td><?= esc($concepto) ?></td>
                    <td><?= esc($p['nombre_cajero'] ?? 'N/D') ?></td>
                    <td class="text-right font-weight-bold">
                      $<?= number_format((float) $p['monto'], 2) ?>
                    </td>
                    <td class="text-center text-nowrap">
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
                  <tr class="table-success font-weight-bold">
                    <td colspan="6" class="text-right">Total General:</td>
                    <td class="text-right">$<?= number_format((float) $totalGeneral, 2) ?></td>
                    <td></td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
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

<!-- Modal Confirmar Eliminación -->
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
});
</script>
</body>
</html>
