<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Editar Pago Externo<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0">
          <i class="fas fa-edit mr-2 text-warning"></i>Editar Pago Externo
        </h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Editar Externo</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-lg-8">

        <div class="card card-outline card-secondary">
          <div class="card-header">
            <h3 class="card-title text-muted">
              <i class="fas fa-lock mr-2"></i>Datos del Recibo (solo lectura)
            </h3>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-5">
                <label class="text-xs text-uppercase text-muted font-weight-bold">Folio Digital</label>
                <p class="font-weight-bold text-success"><?= esc($pago['folio_digital'] ?? '—') ?></p>
              </div>
              <div class="col-md-4">
                <label class="text-xs text-uppercase text-muted font-weight-bold">Fecha de Registro</label>
                <p><?= date('d/m/Y H:i', strtotime($pago['created_at'])) ?></p>
              </div>
              <div class="col-md-3">
                <label class="text-xs text-uppercase text-muted font-weight-bold">Cajero</label>
                <p><?= esc($pago['nombre_cajero'] ?? 'N/D') ?></p>
              </div>
            </div>
          </div>
        </div>

        <div class="card card-outline card-warning">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-pencil-alt mr-2"></i>Campos Editables
            </h3>
          </div>
          <form action="<?= base_url('admin/pagos-externos/' . $pago['id'] . '/actualizar') ?>" method="post">
            <?= csrf_field() ?>
            <div class="card-body">

              <div class="form-group">
                <label for="nombre_cliente">Cliente / Aspirante <span class="text-danger">*</span></label>
                <input type="text" name="nombre_cliente" id="nombre_cliente"
                       class="form-control" required
                       value="<?= esc($pago['nombre_cliente']) ?>">
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="nivel">Nivel <span class="text-danger">*</span></label>
                    <select name="nivel" id="nivel" class="form-control" required>
                      <option value="prepa"    <?= $pago['nivel'] === 'prepa'    ? 'selected' : '' ?>>Preparatoria</option>
                      <option value="uni"      <?= $pago['nivel'] === 'uni'      ? 'selected' : '' ?>>Universidad</option>
                      <option value="posgrado" <?= $pago['nivel'] === 'posgrado' ? 'selected' : '' ?>>Posgrado</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="modalidad">Modalidad</label>
                    <select name="modalidad" id="modalidad" class="form-control">
                      <option value="">— Ninguna —</option>
                      <?php foreach (['Escolarizado','Sabatino','En línea','Mixto','Ejecutivo'] as $mod): ?>
                        <option value="<?= $mod ?>" <?= ($pago['modalidad'] ?? '') === $mod ? 'selected' : '' ?>>
                          <?= $mod ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-8">
                  <div class="form-group">
                    <label for="concepto">Concepto <span class="text-danger">*</span></label>
                    <input type="text" name="concepto" id="concepto"
                           class="form-control" required
                           value="<?= esc($pago['concepto']) ?>">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="monto">Monto <span class="text-danger">*</span></label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text">$</span>
                      </div>
                      <input type="number" name="monto" id="monto"
                             class="form-control" step="0.01" min="0.01" required
                             value="<?= esc($pago['monto']) ?>">
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-md-4 px-0">
                <div class="form-group">
                  <label for="metodo_pago">Método de Pago</label>
                  <select name="metodo_pago" id="metodo_pago" class="form-control">
                    <option value="Efectivo"           <?= ($pago['metodo_pago'] ?? '') === 'Efectivo'           ? 'selected' : '' ?>>Efectivo</option>
                    <option value="Transferencia"      <?= ($pago['metodo_pago'] ?? '') === 'Transferencia'      ? 'selected' : '' ?>>Transferencia</option>
                    <option value="Depósito bancario"  <?= ($pago['metodo_pago'] ?? '') === 'Depósito bancario'  ? 'selected' : '' ?>>Depósito bancario</option>
                    <option value="Tarjeta de débito"  <?= ($pago['metodo_pago'] ?? '') === 'Tarjeta de débito'  ? 'selected' : '' ?>>Tarjeta de débito</option>
                    <option value="Tarjeta de crédito" <?= ($pago['metodo_pago'] ?? '') === 'Tarjeta de crédito' ? 'selected' : '' ?>>Tarjeta de crédito</option>
                  </select>
                </div>
              </div>

              <div class="callout callout-warning mt-2 mb-0">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                El sello digital del recibo original <strong>no se regenera</strong> al editar.
              </div>

            </div>
            <div class="card-footer">
              <button type="submit" class="btn btn-warning">
                <i class="fas fa-save mr-1"></i> Guardar Cambios
              </button>
              <a href="<?= base_url('dashboard') ?>" class="btn btn-default ml-2">
                <i class="fas fa-times mr-1"></i> Cancelar
              </a>
            </div>
          </form>
        </div>

      </div>
    </div>
  </div>
</section>

<?= $this->endSection() ?>