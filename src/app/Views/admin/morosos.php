<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Alumnos con Adeudos<?= $this->endSection() ?>

<?= $this->section('head_extra') ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/datatables.net-bs4@1.13.6/css/dataTables.bootstrap4.min.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-user-clock mr-2 text-danger"></i>Alumnos con Adeudos</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Morosos</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<section class="content">
  <div class="container-fluid">

    <div class="card card-outline card-danger mb-3">
      <div class="card-body py-2">
        <form method="GET" action="<?= base_url('admin/morosos') ?>" class="form-inline">
          <label class="mr-2 font-weight-bold">Filtrar por nivel:</label>
          <div class="btn-group btn-group-sm mr-3">
            <a href="<?= base_url('admin/morosos') ?>"
               class="btn <?= ! $nivel ? 'btn-danger' : 'btn-outline-danger' ?>">Todos</a>
            <a href="?nivel=prepa"
               class="btn <?= $nivel === 'prepa' ? 'btn-danger' : 'btn-outline-danger' ?>">Bachillerato</a>
            <a href="?nivel=uni"
               class="btn <?= $nivel === 'uni' ? 'btn-danger' : 'btn-outline-danger' ?>">Universidad</a>
            <a href="?nivel=posgrado"
               class="btn <?= $nivel === 'posgrado' ? 'btn-danger' : 'btn-outline-danger' ?>">Posgrado</a>
          </div>
          <span class="text-muted">
            Mostrando adeudos de mensualidades del año
            <strong><?= date('Y') ?></strong>
          </span>
        </form>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-list mr-2"></i>
          <?= count($morosos) ?> alumno<?= count($morosos) !== 1 ? 's' : '' ?> con pagos pendientes
        </h3>
      </div>
      <div class="card-body p-0">
        <?php if (empty($morosos)): ?>
          <div class="text-center py-5 text-muted">
            <i class="fas fa-check-circle fa-3x text-success mb-3 d-block"></i>
            <h5>¡Sin adeudos detectados!</h5>
            <p>Todos los alumnos están al corriente en sus mensualidades de <?= date('Y') ?>.</p>
          </div>
        <?php else: ?>
          <?php
            $nivelLabels = ['uni' => 'Universidad', 'prepa' => 'Bachillerato', 'posgrado' => 'Posgrado'];
          ?>
          <table id="tablamorosos" class="table table-bordered table-striped table-hover mb-0">
            <thead class="thead-dark">
              <tr>
                <th>No. Control</th>
                <th>Alumno</th>
                <th>Nivel</th>
                <th class="text-center">Meses Pendientes</th>
                <th>Periodos con Adeudo</th>
                <th class="text-center">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($morosos as $m): ?>
              <tr>
                <td><code><?= esc($m['num_control']) ?></code></td>
                <td><?= esc($m['nombre_alumno']) ?></td>
                <td>
                  <?php
                    $badge = match($m['nivel']) {
                        'uni'      => 'primary',
                        'prepa'    => 'warning',
                        'posgrado' => 'info',
                        default    => 'secondary',
                    };
                  ?>
                  <span class="badge badge-<?= $badge ?>">
                    <?= esc($nivelLabels[$m['nivel']] ?? $m['nivel']) ?>
                  </span>
                </td>
                <td class="text-center">
                  <span class="badge badge-danger badge-pill" style="font-size:.9rem; padding:.4em .7em">
                    <?= $m['total_adeudos'] ?>
                  </span>
                </td>
                <td>
                  <?php foreach ($m['adeudos'] as $adeudo): ?>
                    <span class="badge badge-light border border-danger text-danger mr-1 mb-1">
                      <i class="fas fa-times-circle mr-1"></i><?= esc($adeudo) ?>
                    </span>
                  <?php endforeach; ?>
                </td>
                <td class="text-center text-nowrap">
                  <a href="<?= base_url('admin/estado-cuenta') ?>?num_control=<?= urlencode($m['num_control']) ?>&nivel=<?= urlencode($m['nivel']) ?>"
                     class="btn btn-sm btn-outline-primary"
                     title="Ver estado de cuenta completo">
                    <i class="fas fa-search-dollar"></i>
                  </a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>

  </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/datatables.net@1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net-bs4@1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script>
$(function () {
  <?php if (! empty($morosos)): ?>
  $('#tablamorosos').DataTable({
    language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
    order: [[3, 'desc']],
    columnDefs: [{ orderable: false, targets: [4, 5] }]
  });
  <?php endif; ?>
});
</script>
<?= $this->endSection() ?>