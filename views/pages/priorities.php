<section class="section-card">
    <div class="section-title">
        <div>
            <h2>Prioridades</h2>
            <p>Defina o SLA de atendimento em horas.</p>
        </div>
        <div class="pill"><?= count($priorities) ?> itens</div>
    </div>

    <div class="section-grid">
        <form class="panel" method="post">
            <input type="hidden" name="action" value="create_priority">
            <div class="field-group">
                <div class="field-title"><strong>Nome da prioridade</strong></div>
                <select id="priority_name" name="name" required data-priority-select>
                    <option value="">Selecione</option>
                    <option value="Baixa">Baixa</option>
                    <option value="Média">Média</option>
                    <option value="Alta">Alta</option>
                </select>
            </div>
            <div class="field-group">
                <div class="field-title">
                    <strong>Tempo estimado</strong>
                    <span class="pill">em horas</span>
                </div>
                <div class="stepper">
                    <button type="button" data-stepper="down" aria-label="Diminuir tempo estimado">-</button>
                    <input id="estimated_hours" name="estimated_hours" type="number" min="1" step="1" value="24" required>
                    <button type="button" data-stepper="up" aria-label="Aumentar tempo estimado">+</button>
                </div>
            </div>
            <button type="submit">Cadastrar prioridade</button>
        </form>

        <div class="panel">
            <div class="field-title"><strong>Prioridades cadastradas</strong></div>
            <div class="list-stack">
                <?php if (count($priorities) === 0): ?>
                    <div class="empty-state">Nenhuma prioridade cadastrada ainda.</div>
                <?php else: ?>
                    <?php foreach ($priorities as $priority): ?>
                        <div class="list-item list-row <?= priorityClass($priority['name']) ?>">
                            <span><?= Formatter::e($priority['name']) ?> · <?= (int) $priority['estimated_hours'] ?>h</span>
                            <form method="post" onsubmit="return confirm('Excluir esta prioridade?');">
                                <input type="hidden" name="action" value="delete_priority">
                                <input type="hidden" name="priority_id" value="<?= (int) $priority['id'] ?>">
                                <button type="submit" class="danger">Excluir</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
