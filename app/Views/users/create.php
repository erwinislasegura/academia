<div class="section-head"><div><h2>Crear usuario</h2><p>Registra un nuevo acceso administrativo.</p></div><a class="btn secondary" href="<?= App::url('/users') ?>">Volver</a></div>
<?php $user = $old ?? ['name'=>'','email'=>'','role_id'=>'','status'=>'active']; require App::root('app/Views/users/_form.php'); ?>
