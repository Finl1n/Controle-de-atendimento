<section class="section-card">
    <div class="section-title">
        <div>
            <h2>Acompanhamento</h2>
            <p>Visualize o ciclo dos chamados e execute check-in ou check-out.</p>
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
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Título</th>
                    <th>Setor</th>
                    <th>Prioridade</th>
                    <th>Status</th>
                    <th>Início</th>
                    <th>Término</th>
                    <th>Tempo total</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php $hasTickets = false; ?>
                <?php foreach ($tickets as $ticket): ?>
                    <?php $hasTickets = true; ?>
                    <?php [$durationMinutes, $flag] = computeTicketDuration($ticket); ?>
                    <tr class="<?= $flag === 'overdue' ? 'overdue' : '' ?>">
                        <td><?= (int) $ticket['id'] ?></td>
                        <td>
                            <strong><?= Formatter::e($ticket['title']) ?></strong>
                            <?php if (!empty($ticket['description'])): ?>
                                <div class="meta"><?= Formatter::e($ticket['description']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?= Formatter::e($ticket['sector_name']) ?></td>
                        <td>
                            <span class="pill <?= priorityClass($ticket['priority_name']) ?>">
                                <?= Formatter::e($ticket['priority_name']) ?>
                            </span>
                        </td>
                        <td><span class="pill <?= statusClass($ticket['status']) ?>"><?= Formatter::e($ticket['status']) ?></span></td>
                        <td><?= Formatter::dateTime($ticket['started_at']) ?></td>
                        <td><?= Formatter::dateTime($ticket['ended_at']) ?></td>
                        <td>
                            <?php if ($ticket['started_at'] === null): ?>
                                -
                            <?php elseif ($flag === 'overdue'): ?>
                                <span class="pill pill-alert">⚠ <?= Formatter::durationFromMinutes($durationMinutes) ?></span>
                            <?php else: ?>
                                <?= Formatter::durationFromMinutes($durationMinutes) ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="actions-inline">
                                <?php if (canStart($ticket)): ?>
                                    <form method="post">
                                        <input type="hidden" name="action" value="start_ticket">
                                        <input type="hidden" name="ticket_id" value="<?= (int) $ticket['id'] ?>">
                                        <button type="submit" class="secondary">Iniciar atendimento</button>
                                    </form>
                                <?php elseif (canFinish($ticket)): ?>
                                    <button type="button" class="secondary" data-open-finish data-ticket-id="<?= (int) $ticket['id'] ?>">Finalizar</button>
                                <?php else: ?>
                                    <span class="pill"><?= Formatter::e(ticketStatusLabel($ticket)) ?></span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$hasTickets): ?>
                    <tr>
                        <td colspan="9" class="empty-row">Nenhum chamado cadastrado ainda.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
