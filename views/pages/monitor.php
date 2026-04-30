<section class="section-card monitor-card">
    <div class="section-title">
        <div>
            <h2>Acompanhamento</h2>
            <p>Visualize o ciclo dos chamados e acompanhe o histórico de atendimento.</p>
        </div>
        <div class="pill"><?= count($tickets) ?> itens</div>
    </div>

    <div class="summary-grid monitor-summary">
        <div class="summary-card tone-neutral">
            <span class="meta">Total</span>
            <strong><?= (int) $summary['total'] ?></strong>
        </div>
        <div class="summary-card tone-blue">
            <span class="meta">Abertos</span>
            <strong><?= (int) $summary['aberto'] ?></strong>
        </div>
        <div class="summary-card tone-amber">
            <span class="meta">Em atendimento</span>
            <strong><?= (int) $summary['em_atendimento'] ?></strong>
        </div>
        <div class="summary-card tone-green">
            <span class="meta">Finalizados</span>
            <strong><?= (int) $summary['finalizado'] ?></strong>
        </div>
        <div class="summary-card tone-rose">
            <span class="meta">Cancelados</span>
            <strong><?= (int) $summary['cancelado'] ?></strong>
        </div>
    </div>

    <div class="monitor-layout">
        <div class="panel panel-wide">
            <div class="panel-head">
                <div>
                    <h3>Histórico recente</h3>
                    <p>Últimos chamados registrados na operação.</p>
                </div>
                <span class="pill"><?= count($tickets) ?> registros</span>
            </div>

            <div class="ticket-stream">
                <?php if (count($tickets) === 0): ?>
                    <div class="empty-state">Nenhum chamado cadastrado ainda.</div>
                <?php else: ?>
                    <?php foreach ($tickets as $ticket): ?>
                        <?php [$durationMinutes, $flag] = computeTicketDuration($ticket); ?>
                        <?php $isOverdue = $flag === 'overdue'; ?>
                        <article class="ticket-card <?= $isOverdue ? 'ticket-card--overdue' : '' ?>">
                            <div class="ticket-card__header">
                                <div class="ticket-card__headline">
                                    <div class="ticket-card__status-row">
                                        <span class="pill">#<?= Formatter::e(ticketReference($ticket)) ?></span>
                                        <span class="pill <?= $isOverdue ? 'pill-alert' : statusClass($ticket['status']) ?>">
                                            <?= Formatter::e(ticketStatusLabel($ticket)) ?>
                                        </span>
                                        <span class="ticket-card__time">
                                            <?= Formatter::period($ticket['started_at'] ?? $ticket['created_at'], $ticket['ended_at'] ?? $ticket['canceled_at']) ?>
                                        </span>
                                    </div>
                                    <h4><?= Formatter::e($ticket['title']) ?></h4>
                                    <p><?= Formatter::e($ticket['sector_name']) ?> · <?= Formatter::e($ticket['priority_name']) ?> · Solicitante: <?= Formatter::e($ticket['requester_name'] ?? 'Não informado') ?></p>
                                </div>

                                <div class="ticket-card__badge">
                                    <?= $ticket['status'] === 'Cancelado'
                                        ? 'Cancelado'
                                        : ($isOverdue ? 'Pendente em atraso' : ($ticket['started_at'] === null ? 'Aguardando início' : Formatter::durationFromMinutes($durationMinutes))) ?>
                                </div>
                            </div>

                            <div class="ticket-card__details">
                                <div class="ticket-detail">
                                    <span>Setor</span>
                                    <strong><?= Formatter::e($ticket['sector_name']) ?></strong>
                                </div>
                                <div class="ticket-detail">
                                    <span>Prioridade</span>
                                    <strong><?= Formatter::e($ticket['priority_name']) ?> · SLA <?= (int) $ticket['estimated_hours'] ?>h</strong>
                                </div>
                                <div class="ticket-detail">
                                    <span>Tempo total</span>
                                    <strong>
                                        <?= $ticket['started_at'] === null
                                            ? Formatter::durationFromMinutes($durationMinutes)
                                            : Formatter::durationFromMinutes($durationMinutes) ?>
                                    </strong>
                                </div>
                            </div>

                            <?php if (!empty($ticket['description'])): ?>
                                <div class="ticket-card__description">
                                    <span>Descrição</span>
                                    <p><?= Formatter::e($ticket['description']) ?></p>
                                </div>
                            <?php endif; ?>

                            <?php if ($ticket['status'] === 'Finalizado' && !empty($ticket['solution'])): ?>
                                <div class="solution-block">
                                    <div class="solution-card solution-card--solution">
                                        <div class="solution-card__hero">
                                            <div>
                                                <div class="solution-card__label">Fechamento do atendimento</div>
                                                <div class="solution-card__title">Resumo da resolução</div>
                                            </div>
                                            <span class="pill status-finished">Finalizado</span>
                                        </div>
                                        <div class="solution-card__meta">
                                            <span>Protocolo: #<?= Formatter::e(ticketReference($ticket)) ?></span>
                                            <span>Setor: <?= Formatter::e($ticket['sector_name']) ?></span>
                                            <span>Prioridade: <?= Formatter::e($ticket['priority_name']) ?></span>
                                            <span>Responsável: <?= Formatter::e($ticket['responder_name'] ?? 'Não informado') ?></span>
                                            <span>Tempo: <?= Formatter::durationFromMinutes($durationMinutes) ?></span>
                                        </div>
                                        <?php if (!empty($ticket['delay_reason'])): ?>
                                            <div class="solution-card__delay">
                                                <div class="solution-card__label">Motivo do atraso</div>
                                                <div class="solution-card__body"><?= Formatter::multiline($ticket['delay_reason']) ?></div>
                                            </div>
                                        <?php endif; ?>
                                        <div class="solution-card__body"><?= Formatter::multiline($ticket['solution']) ?></div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="ticket-card__actions">
                                <?php if (canStart($ticket)): ?>
                                    <form method="post">
                                        <input type="hidden" name="action" value="start_ticket">
                                        <input type="hidden" name="ticket_id" value="<?= (int) $ticket['id'] ?>">
                                        <button type="submit" class="secondary">Iniciar atendimento</button>
                                    </form>
                                    <button type="button" class="danger" data-open-cancel data-ticket-id="<?= (int) $ticket['id'] ?>">Cancelar</button>
                                <?php elseif (canFinish($ticket)): ?>
                                    <button type="button" class="secondary" data-open-finish data-ticket-id="<?= (int) $ticket['id'] ?>" data-ticket-overdue="<?= $isOverdue ? '1' : '0' ?>">Finalizar</button>
                                    <button type="button" class="danger" data-open-cancel data-ticket-id="<?= (int) $ticket['id'] ?>">Cancelar</button>
                                <?php else: ?>
                                    <span class="pill"><?= Formatter::e(ticketStatusLabel($ticket)) ?></span>
                                <?php endif; ?>
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
                <div class="status-stat status-canceled">
                    <span>Cancelados</span>
                    <strong><?= (int) $summary['cancelado'] ?></strong>
                </div>
            </div>
        </aside>
    </div>
</section>
