<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Cambiar Contraseña<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="content-header">
  <div class="container-fluid">
    <h1 class="m-0">Cambiar Contraseña</h1>
  </div>
</div>

<section class="content">
  <div class="container-fluid">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card card-primary card-outline">
          <div class="card-header">
            <h3 class="card-title"><i class="fas fa-lock mr-2"></i>Actualizar contraseña</h3>
          </div>

          <?php if ($error): ?>
          <div class="alert alert-danger alert-dismissible mx-3 mt-3 mb-0">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-exclamation-triangle mr-1"></i> <?= esc($error) ?>
          </div>
          <?php endif; ?>

          <?php if ($success): ?>
          <div class="alert alert-success alert-dismissible mx-3 mt-3 mb-0">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle mr-1"></i> <?= esc($success) ?>
          </div>
          <?php endif; ?>

          <form action="<?= base_url('configuracion/password') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="card-body">

              <div class="form-group">
                <label for="password_actual">Contraseña actual</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                  </div>
                  <input type="password" id="password_actual" name="password_actual"
                         class="form-control" placeholder="Contraseña actual" required>
                  <div class="input-group-append">
                    <button type="button" class="btn btn-outline-secondary toggle-pwd" data-target="password_actual">
                      <i class="fas fa-eye"></i>
                    </button>
                  </div>
                </div>
              </div>

              <div class="form-group">
                <label for="password_nueva">Nueva contraseña</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                  </div>
                  <input type="password" id="password_nueva" name="password_nueva"
                         class="form-control" placeholder="Mínimo 6 caracteres" required minlength="6">
                  <div class="input-group-append">
                    <button type="button" class="btn btn-outline-secondary toggle-pwd" data-target="password_nueva">
                      <i class="fas fa-eye"></i>
                    </button>
                  </div>
                </div>
              </div>

              <div class="form-group">
                <label for="password_confirmar">Confirmar nueva contraseña</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                  </div>
                  <input type="password" id="password_confirmar" name="password_confirmar"
                         class="form-control" placeholder="Repite la nueva contraseña" required minlength="6">
                  <div class="input-group-append">
                    <button type="button" class="btn btn-outline-secondary toggle-pwd" data-target="password_confirmar">
                      <i class="fas fa-eye"></i>
                    </button>
                  </div>
                </div>
              </div>

            </div>
            <div class="card-footer">
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> Actualizar contraseña
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).on('click', '.toggle-pwd', function () {
  var target = $('#' + $(this).data('target'));
  var icon   = $(this).find('i');
  if (target.attr('type') === 'password') {
    target.attr('type', 'text');
    icon.removeClass('fa-eye').addClass('fa-eye-slash');
  } else {
    target.attr('type', 'password');
    icon.removeClass('fa-eye-slash').addClass('fa-eye');
  }
});
</script>
<?= $this->endSection() ?>