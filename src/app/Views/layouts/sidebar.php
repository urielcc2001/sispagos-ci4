<?php
$rol  = session()->get('rol');
$ruta = uri_string();
?>
<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <a href="<?= base_url('dashboard') ?>" class="brand-link px-3">
    <span class="brand-text font-weight-light"><b>Sistema</b>Pagos</span>
  </a>
  <div class="sidebar">
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">

        <li class="nav-item">
          <a href="<?= base_url('dashboard') ?>"
             class="nav-link <?= $ruta === 'dashboard' ? 'active' : '' ?>">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p><?= $rol === 'cajero' ? 'Inicio' : 'Dashboard' ?></p>
          </a>
        </li>

        <li class="nav-item">
          <a href="<?= base_url('pagos') ?>"
             class="nav-link <?= $ruta === 'pagos' ? 'active' : '' ?>">
            <i class="nav-icon fas fa-money-bill-wave"></i>
            <p>Registrar Pago</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="<?= base_url('pagos-externos') ?>"
             class="nav-link <?= $ruta === 'pagos-externos' ? 'active' : '' ?>">
            <i class="nav-icon fas fa-user-tag"></i>
            <p>Pagos Externos</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="<?= base_url('admin/reportes') ?>"
             class="nav-link <?= (strpos($ruta, 'admin/reportes') === 0 || strpos($ruta, 'admin/exportar') === 0) ? 'active' : '' ?>">
            <i class="nav-icon fas fa-chart-bar"></i>
            <p><?= $rol === 'cajero' ? 'Mis Reportes' : 'Reportes' ?></p>
          </a>
        </li>

        <?php if ($rol === 'admin'): ?>
        <li class="nav-item">
          <a href="<?= base_url('admin/conceptos') ?>"
             class="nav-link <?= strpos($ruta, 'admin/conceptos') === 0 ? 'active' : '' ?>">
            <i class="nav-icon fas fa-cogs"></i>
            <p>Conceptos</p>
          </a>
        </li>
        <?php endif; ?>

        <li class="nav-item">
          <a href="<?= base_url('admin/estado-cuenta') ?>"
             class="nav-link <?= strpos($ruta, 'admin/estado-cuenta') === 0 ? 'active' : '' ?>">
            <i class="nav-icon fas fa-search-dollar"></i>
            <p>Estado de Cuenta</p>
          </a>
        </li>

        <?php if ($rol === 'admin'): ?>
        <li class="nav-item">
          <a href="<?= base_url('admin/morosos') ?>"
             class="nav-link <?= strpos($ruta, 'admin/morosos') === 0 ? 'active' : '' ?>">
            <i class="nav-icon fas fa-user-clock"></i>
            <p>Morosos</p>
          </a>
        </li>
        <?php endif; ?>

      </ul>
    </nav>
  </div>
</aside>