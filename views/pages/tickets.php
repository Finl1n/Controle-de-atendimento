<section class="section-card">
    <div class="section-title">
        <div>
            <h2>Abrir Chamado</h2>
            <p>Cadastre o setor, o nível de prioridade e o tempo estimado do atendimento em uma única etapa.</p>
        </div>
        <div class="pill"><?= count($tickets) ?> itens</div>
    </div>

    <div class="section-grid">
        <form class="panel" method="post">
            <input type="hidden" name="action" value="create_ticket">

            <div class="field-group">
                <div class="field-title"><strong>Solicitante</strong></div>
                <input id="requester_name" name="requester_name" placeholder="Ex.: Gustavo Martins" required>
            </div>

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
                <div class="field-title"><strong>Nível de prioridade</strong></div>
                <select id="priority_name" name="priority_name" required data-priority-select>
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
