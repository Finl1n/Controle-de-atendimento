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
                                    <strong>#<?= Formatter::e(ticketReference($ticket)) ?> · <?= Formatter::e($ticket['title']) ?></strong>
                                    <span class="pill <?= ticketIsOverdue($ticket) ? 'pill-alert' : statusClass($ticket['status']) ?>">
                                        <?= Formatter::e(ticketStatusLabel($ticket)) ?>
                                    </span>
                                </div>
                                <p><?= Formatter::e($ticket['sector_name']) ?> · <?= Formatter::e($ticket['priority_name']) ?> · Solicitante: <?= Formatter::e($ticket['requester_name'] ?? 'Não informado') ?></p>
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

        <div class="panel panel-side-panel">
            <div class="panel-head">
                <div>
                    <h3>Fechamentos recentes</h3>
                    <p>Últimos chamados encerrados com solução registrada.</p>
                </div>
                <span class="pill"><?= count($recentClosedTickets) ?> itens</span>
            </div>

            <div class="list-stack">
                <?php if (count($recentClosedTickets) === 0): ?>
                    <div class="empty-state">Nenhum chamado finalizado ainda.</div>
                <?php else: ?>
                    <?php foreach ($recentClosedTickets as $ticket): ?>
                        <article class="closed-card">
                            <div class="closed-card__head">
                                <strong>#<?= Formatter::e(ticketReference($ticket)) ?> · <?= Formatter::e($ticket['title']) ?></strong>
                                <span class="pill <?= statusClass($ticket['status']) ?>"><?= Formatter::e($ticket['status']) ?></span>
                            </div>
                            <div class="closed-card__meta">
                                <span><?= Formatter::e($ticket['sector_name']) ?> · <?= Formatter::e($ticket['priority_name']) ?> · Solicitante: <?= Formatter::e($ticket['requester_name'] ?? 'Não informado') ?></span>
                                <span><?= Formatter::period($ticket['started_at'] ?? $ticket['created_at'], $ticket['ended_at'] ?? $ticket['canceled_at']) ?></span>
                                <span><?= $ticket['status'] === 'Cancelado'
                                    ? 'Cancelado por: ' . Formatter::e($ticket['canceled_by'] ?? 'Não informado')
                                    : 'Responsável: ' . Formatter::e($ticket['responder_name'] ?? 'Não informado') ?></span>
                            </div>
                            <?php if ($ticket['status'] === 'Finalizado' && !empty($ticket['solution'])): ?>
                                <div class="solution-card solution-card--compact solution-card--solution">
                                    <div class="solution-card__hero">
                                        <div>
                                            <div class="solution-card__label">Solução registrada</div>
                                            <div class="solution-card__title">Fechamento do atendimento</div>
                                        </div>
                                        <span class="pill status-finished">Finalizado</span>
                                    </div>
                                    <div class="solution-card__meta">
                                        <span>Setor: <?= Formatter::e($ticket['sector_name']) ?></span>
                                        <span>Prioridade: <?= Formatter::e($ticket['priority_name']) ?></span>
                                        <span>Responsável: <?= Formatter::e($ticket['responder_name'] ?? 'Não informado') ?></span>
                                    </div>
                                    <?php if (!empty($ticket['delay_reason'])): ?>
                                        <div class="solution-card__delay">
                                            <div class="solution-card__label">Atraso registrado</div>
                                            <div class="solution-card__body"><?= Formatter::multiline($ticket['delay_reason']) ?></div>
                                        </div>
                                    <?php endif; ?>
                                    <div class="solution-card__body"><?= Formatter::multiline($ticket['solution']) ?></div>
                                </div>
                            <?php elseif ($ticket['status'] === 'Cancelado'): ?>
                                <div class="solution-card solution-card--compact solution-card--solution">
                                    <div class="solution-card__hero">
                                        <div>
                                            <div class="solution-card__label">Chamado cancelado</div>
                                            <div class="solution-card__title">Encerramento sem conclusão operacional</div>
                                        </div>
                                        <span class="pill status-canceled">Cancelado</span>
                                    </div>
                                    <div class="solution-card__meta">
                                        <span>Setor: <?= Formatter::e($ticket['sector_name']) ?></span>
                                        <span>Prioridade: <?= Formatter::e($ticket['priority_name']) ?></span>
                                        <span>Cancelado por: <?= Formatter::e($ticket['canceled_by'] ?? 'Não informado') ?></span>
                                    </div>
                                    <div class="solution-card__body"><?= Formatter::multiline($ticket['cancel_reason'] ?? '') ?></div>
                                </div>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
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
