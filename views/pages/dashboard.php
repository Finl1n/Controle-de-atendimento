<section class="hero-panel">
    <div class="hero-copy">
        <p class="eyebrow">Painel clínico</p>
        <h2>Histórico e fluxo dos chamados</h2>
        <p>Resumo operacional do dia com foco em abertura, andamento, finalização e alertas de SLA.</p>
    </div>

    <div class="hero-grid">
        <?php foreach ($summaryCards as $card): ?>
            <div class="summary-card tone-<?= Formatter::e($card['tone']) ?>">
                <span class="meta"><?= Formatter::e($card['label']) ?></span>
                <strong><?= (int) $card['value'] ?></strong>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="content-grid">
    <div class="panel panel-wide">
        <div class="panel-head">
            <div>
                <h3>Histórico recente</h3>
                <p>Últimos chamados registrados na operação.</p>
            </div>
            <span class="pill"><?= count($recentTickets) ?> registros</span>
        </div>

        <div class="activity-list">
            <?php if (count($recentTickets) === 0): ?>
                <div class="empty-state">Nenhum chamado cadastrado ainda.</div>
            <?php else: ?>
                <?php foreach ($recentTickets as $ticket): ?>
                    <?php [$durationMinutes, $flag] = computeTicketDuration($ticket); ?>
                    <article class="activity-item <?= $flag === 'overdue' ? 'overdue' : '' ?>">
                        <div class="activity-main">
                            <div class="activity-title-row">
                                <strong><?= Formatter::e($ticket['title']) ?></strong>
                                <span class="pill <?= statusClass($ticket['status']) ?>"><?= Formatter::e($ticket['status']) ?></span>
                            </div>
                            <p><?= Formatter::e($ticket['sector_name']) ?> · <?= Formatter::e($ticket['priority_name']) ?></p>
                        </div>
                        <div class="activity-meta">
                            <span>Aberto em <?= Formatter::dateTime($ticket['created_at']) ?></span>
                            <span>Tempo <?= $ticket['started_at'] === null ? '-' : Formatter::durationFromMinutes($durationMinutes) ?></span>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <aside class="panel panel-side">
        <div class="panel-head">
            <div>
                <h3>Chamados em atraso</h3>
                <p>Itens que ultrapassaram o SLA da prioridade.</p>
            </div>
            <span class="pill danger"><?= count($overdueTickets) ?></span>
        </div>

        <div class="list-stack">
            <?php if (count($overdueTickets) === 0): ?>
                <div class="empty-state">Nenhum chamado fora do prazo no momento.</div>
            <?php else: ?>
                <?php foreach (array_slice($overdueTickets, 0, 4) as $ticket): ?>
                    <div class="list-item alert-item">
                        <strong><?= Formatter::e($ticket['title']) ?></strong>
                        <span><?= Formatter::e($ticket['sector_name']) ?> · SLA <?= (int) $ticket['estimated_hours'] ?>h</span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="status-board">
            <div class="status-stat status-open">
                <span>Abertos</span>
                <strong><?= (int) $summary['aberto'] ?></strong>
            </div>
            <div class="status-stat status-progress">
                <span>Em atendimento</span>
                <strong><?= (int) $summary['em_atendimento'] ?></strong>
            </div>
            <div class="status-stat status-finished">
                <span>Finalizados</span>
                <strong><?= (int) $summary['finalizado'] ?></strong>
            </div>
        </div>
    </aside>
</section>
