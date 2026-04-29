<?php $title = $meta['title']; ?>
<header class="topbar">
    <div class="topbar-title">
        <h1><?= Formatter::e($title) ?></h1>
        <p><?= Formatter::e($meta['description']) ?></p>
    </div>
    <div class="topbar-meta">
        <span class="chip">Chamados: <?= (int)$summary['total'] ?></span>
        <span class="chip">Atualizado em tempo real</span>
    </div>
</header>
