<div class="section-head"><div><h2>Crear rol</h2><p>Configura un nuevo perfil de acceso.</p></div><a class="btn secondary" href="<?= App::url('/roles') ?>">Volver</a></div>
<?php $role = $old ?? ['name'=>'','slug'=>'','description'=>'']; require App::root('app/Views/roles/_form.php'); ?>
