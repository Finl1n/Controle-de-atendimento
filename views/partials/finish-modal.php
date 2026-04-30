<div class="modal" id="finishModal" hidden role="dialog" aria-modal="true" aria-labelledby="finishModalTitle">
    <form method="post" class="modal-card">
        <input type="hidden" name="action" value="finish_ticket">
        <input type="hidden" name="ticket_id" id="modalTicketId">

        <div class="modal-head">
            <div>
                <p class="eyebrow">Encerramento do atendimento</p>
                <h3 id="finishModalTitle">Registrar o check-out</h3>
            </div>
            <button type="button" class="modal-close" data-close-modal>&times;</button>
        </div>

        <div class="field-group">
            <div class="field-title"><strong>Quem finalizou</strong></div>
            <input id="modalResponderName" name="responder_name" placeholder="Ex.: Ana Souza" required>
        </div>

        <div class="field-group">
            <div class="field-title"><strong>O que aconteceu</strong></div>
            <textarea id="modalWhatHappened" name="what_happened" required></textarea>
        </div>

        <div class="field-group">
            <div class="field-title"><strong>Como resolveu</strong></div>
            <textarea id="modalHowSolved" name="how_solved" required></textarea>
        </div>

        <div class="field-group" data-delay-group hidden>
            <div class="field-title">
                <strong>Motivo do atraso</strong>
                <span class="pill">preencha se houve atraso</span>
            </div>
            <textarea id="modalDelayReason" name="delay_reason" placeholder="Explique o que causou o atraso, se aplicável."></textarea>
        </div>

        <button type="submit">Concluir atendimento</button>
    </form>
</div>
