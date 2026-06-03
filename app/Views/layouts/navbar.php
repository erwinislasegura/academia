<header class="topbar">
    <button class="icon-button" id="sidebarToggle" type="button">☰</button>
    <div>
        <p class="eyebrow">Sistema Academiapp</p>
        <h1><?= h($title ?? 'Dashboard') ?></h1>
    </div>
    <div class="user-chip">
        <div class="avatar"><?= h(strtoupper(substr($user['name'] ?? 'AI', 0, 1))) ?></div>
        <div><strong><?= h($user['name'] ?? '') ?></strong><span><?= h($user['role_name'] ?? '') ?></span></div>
    </div>
</header>
