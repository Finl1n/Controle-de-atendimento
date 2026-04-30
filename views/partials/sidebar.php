<aside class="sidebar">
    <div class="brand">
        <div class="brand-mark">+</div>
        <div>
            <div class="brand-name">Controle de Atendimentos</div>
            <div class="brand-subtitle">Atendimento interno</div>
        </div>
    </div>

    <div class="sidebar-role">
        <span><?= Formatter::e(roleLabel($currentRole ?? null)) ?></span>
        <strong><?= Formatter::e($currentUserName ?? '') ?></strong>
    </div>

    <nav class="sidebar-nav" aria-label="Navegação principal">
        <?php foreach ($navItems as $item): ?>
            <a class="sidebar-link <?= $page === $item['page'] ? 'active' : '' ?>" href="?page=<?= Formatter::e($item['page']) ?>">
                <span class="sidebar-link-label"><?= Formatter::e($item['label']) ?></span>
                <small><?= Formatter::e($item['description']) ?></small>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-footer-card">
            <span>Data de hoje</span>
            <strong><?= date('d/m/Y') ?></strong>
        </div>
        <div class="sidebar-footer-card">
            <span>Fluxo ativo</span>
            <strong><?= count($tickets) ?> chamados</strong>
        </div>
        <form method="post">
            <input type="hidden" name="action" value="reset_role">
            <button type="submit" class="secondary">Trocar perfil</button>
        </form>
    </div>
</aside>
