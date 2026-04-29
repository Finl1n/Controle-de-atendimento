<aside class="sidebar">
    <div class="brand">
        <div class="brand-mark">+</div>
        <div>
            <div class="brand-name">W5i</div>
            <div class="brand-subtitle">Atendimento interno</div>
        </div>
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
    </div>
</aside>
