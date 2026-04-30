<div class="modal" id="cancelModal" hidden role="dialog" aria-modal="true" aria-labelledby="cancelModalTitle">
    <form method="post" class="modal-card">
        <input type="hidden" name="action" value="cancel_ticket">
        <input type="hidden" name="ticket_id" id="cancelModalTicketId">

        <div class="modal-head">
            <div>
                <p class="eyebrow">Cancelamento do chamado</p>
                <h3 id="cancelModalTitle">Registrar o cancelamento</h3>
            </div>
            <button type="button" class="modal-close" data-close-modal>&times;</button>
        </div>

        <div class="field-group">
            <div class="field-title"><strong>Quem cancelou</strong></div>
            <input id="cancelModalCanceledBy" name="canceled_by" placeholder="Ex.: Maria Silva" required>
        </div>

        <div class="field-group">
            <div class="field-title"><strong>Motivo do cancelamento</strong></div>
            <textarea id="cancelModalReason" name="cancel_reason" required></textarea>
        </div>

        <button type="submit" class="danger">Cancelar chamado</button>
    </form>
</div>
