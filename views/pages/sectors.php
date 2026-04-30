<section class="section-card">
    <div class="section-title">
        <div>
            <h2>Setores</h2>
            <p>Cadastro e consulta das áreas responsáveis pelos chamados.</p>
        </div>
        <div class="pill"><?= count($sectors) ?> itens</div>
    </div>

    <div class="section-grid">
        <form class="panel" method="post">
            <input type="hidden" name="action" value="create_sector">
            <div class="field-group">
                <div class="field-title"><strong>Nome do setor</strong></div>
                <input id="sector_name" name="name" placeholder="Ex.: TI" required>
            </div>
            <button type="submit">Cadastrar setor</button>
        </form>

        <div class="panel">
            <div class="field-title"><strong>Setores cadastrados</strong></div>
            <div class="list-stack">
                <?php if (count($sectors) === 0): ?>
                    <div class="empty-state">Nenhum setor cadastrado ainda.</div>
                <?php else: ?>
                    <?php foreach ($sectors as $sector): ?>
                        <div class="list-item list-row">
                            <span><?= Formatter::e($sector['name']) ?></span>
                            <form method="post" onsubmit="return confirm('Excluir este setor?');">
                                <input type="hidden" name="action" value="delete_sector">
                                <input type="hidden" name="sector_id" value="<?= (int) $sector['id'] ?>">
                                <button type="submit" class="danger">Excluir</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
