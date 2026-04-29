<section class="section-card">
    <div class="section-title">
        <div>
            <h2>Abrir Chamado</h2>
            <p>Criação de um novo chamado de atendimento.</p>
        </div>
        <div class="pill"><?= count($tickets) ?> itens</div>
    </div>

    <div class="section-grid">
        <form class="panel" method="post">
            <input type="hidden" name="action" value="create_ticket">

            <div class="field-group">
                <div class="field-title"><strong>Setor</strong></div>
                <select id="ticket_sector" name="sector_id" required>
                    <option value="">Selecione</option>
                    <?php foreach ($sectors as $sector): ?>
                        <option value="<?= (int) $sector['id'] ?>"><?= Formatter::e($sector['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field-group">
                <div class="field-title"><strong>Prioridade</strong></div>
                <select id="ticket_priority" name="priority_id" required>
                    <option value="">Selecione</option>
                    <?php foreach ($priorities as $priority): ?>
                        <option value="<?= (int) $priority['id'] ?>"><?= Formatter::e($priority['name']) ?> (<?= (int) $priority['estimated_hours'] ?>h)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field-group">
                <div class="field-title"><strong>Título</strong></div>
                <input id="ticket_title" name="title" placeholder="Ex.: Email corporativo indisponível" required>
            </div>

            <div class="field-group">
                <div class="field-title"><strong>Descrição</strong></div>
                <textarea id="ticket_description" name="description" placeholder="Descreva o problema com clareza."></textarea>
            </div>

            <button type="submit">Criar chamado</button>
        </form>
    </div>
</section>
