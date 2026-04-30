<?php if (!empty($currentRole)): ?>
    <div class="profile-chip dashboard-badge <?= $currentRole === 'solicitante' ? 'profile-chip--solicitante' : 'profile-chip--responsavel' ?>">
        <span><?= Formatter::e(roleLabel($currentRole)) ?> ativo</span>
        <strong><?= Formatter::e($currentUserName ?? '') ?></strong>
        <small>
            <?php if ($currentRole === 'solicitante'): ?>
                Seus chamados estão filtrados por este nome.
            <?php else: ?>
                Você está operando a fila completa de atendimento.
            <?php endif; ?>
        </small>
    </div>
<?php endif; ?>

<section class="hero-panel">
    <div class="hero-copy">
        <h2>Resumo dos chamados</h2>
        <p>Visão rápida da operação com aberturas, andamento, encerramentos e alertas de SLA.</p>
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
                    <article class="activity-item <?= priorityClass($ticket['priority_name']) ?> <?= $flag === 'overdue' ? 'overdue' : '' ?>">
                        <div class="activity-main">
                            <div class="activity-title-row">
                                <strong>#<?= Formatter::e(ticketReference($ticket)) ?> · <?= Formatter::e($ticket['title']) ?></strong>
                                <span class="pill ticket-status-pill"><?= Formatter::e(ticketStatusLabel($ticket)) ?></span>
                            </div>
                            <p>
                                <?= Formatter::e($ticket['sector_name']) ?> ·
                                Solicitante: <?= Formatter::e($ticket['requester_name'] ?? 'Não informado') ?>
                            </p>
                        </div>
                        <div class="activity-meta">
                            <span>Aberto em <?= Formatter::dateTime($ticket['created_at']) ?></span>
                            <span>
                                Prioridade:
                                <span class="priority-pill <?= priorityClass($ticket['priority_name']) ?>">
                                    <?= Formatter::e($ticket['priority_name']) ?>
                                </span>
                            </span>
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
                        <span>
                            <?= Formatter::e($ticket['sector_name']) ?> ·
                            <span class="priority-pill <?= priorityClass($ticket['priority_name']) ?>">
                                <?= Formatter::e($ticket['priority_name']) ?>
                            </span>
                        </span>
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
                        <article class="closed-card <?= priorityClass($ticket['priority_name']) ?>">
                            <div class="closed-card__head">
                                <strong>#<?= Formatter::e(ticketReference($ticket)) ?> · <?= Formatter::e($ticket['title']) ?></strong>
                                <span class="pill ticket-status-pill"><?= Formatter::e($ticket['status']) ?></span>
                            </div>
                            <div class="closed-card__meta">
                                <span><?= Formatter::e($ticket['sector_name']) ?> · Solicitante: <?= Formatter::e($ticket['requester_name'] ?? 'Não informado') ?></span>
                                <span>
                                    Prioridade:
                                    <span class="priority-pill <?= priorityClass($ticket['priority_name']) ?>">
                                        <?= Formatter::e($ticket['priority_name']) ?>
                                    </span>
                                </span>
                                <span><?= Formatter::period($ticket['started_at'] ?? $ticket['created_at'], $ticket['ended_at']) ?></span>
                                <span>Responsável: <?= Formatter::e($ticket['responder_name'] ?? 'Não informado') ?></span>
                            </div>
                            <div class="solution-card solution-card--compact solution-card--solution">
                                <div class="solution-card__hero">
                                    <div>
                                        <div class="solution-card__label">Solução registrada</div>
                                        <div class="solution-card__title">Fechamento do atendimento</div>
                                    </div>
                                    <span class="pill ticket-status-pill">Finalizado</span>
                                </div>
                                <div class="solution-card__meta">
                                    <span>Protocolo: #<?= Formatter::e(ticketReference($ticket)) ?></span>
                                    <span>Setor: <?= Formatter::e($ticket['sector_name']) ?></span>
                                    <span>Responsável: <?= Formatter::e($ticket['responder_name'] ?? 'Não informado') ?></span>
                                </div>
                                <?php if (ticketIsOverdue($ticket) && !empty($ticket['delay_reason'])): ?>
                                    <div class="solution-card__delay">
                                        <div class="solution-card__label">Motivo do atraso</div>
                                        <div class="solution-card__body"><?= Formatter::multiline($ticket['delay_reason']) ?></div>
                                    </div>
                                <?php endif; ?>
                                <div class="solution-card__body"><?= Formatter::multiline($ticket['solution']) ?></div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </aside>
</section>
