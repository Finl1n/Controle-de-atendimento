<?php $title = $meta['title']; ?>
<header class="topbar">
    <div class="topbar-title">
        <h1><?= Formatter::e($title) ?></h1>
        <p><?= Formatter::e($meta['description']) ?></p>
    </div>
    <div class="topbar-meta">
        <div class="profile-chip <?= $currentRole === 'solicitante' ? 'profile-chip--solicitante' : 'profile-chip--responsavel' ?>">
            <span><?= Formatter::e(roleLabel($currentRole ?? null)) ?> ativo</span>
            <strong><?= Formatter::e($currentUserName !== '' ? $currentUserName : 'Sem nome') ?></strong>
        </div>
        <span class="chip">Chamados: <?= (int)$summary['total'] ?></span>
        <span class="chip">Atualizado em tempo real</span>
    </div>
</header>
