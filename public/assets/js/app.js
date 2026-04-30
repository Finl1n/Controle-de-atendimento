(function () {
    const prioritySelect = document.querySelector('[data-priority-select]');
    const hoursInput = document.getElementById('estimated_hours');
    const stepButtons = document.querySelectorAll('[data-stepper]');

    const finishModal = document.getElementById('finishModal');
    const finishModalTicketId = document.getElementById('modalTicketId');
    const finishModalResponderName = document.getElementById('modalResponderName');
    const finishModalWhatHappened = document.getElementById('modalWhatHappened');
    const finishModalHowSolved = document.getElementById('modalHowSolved');
    const finishModalDelayReason = document.getElementById('modalDelayReason');
    const finishModalDelayGroup = document.querySelector('[data-delay-group]');

    const cancelModal = document.getElementById('cancelModal');
    const cancelModalTicketId = document.getElementById('cancelModalTicketId');
    const cancelModalCanceledBy = document.getElementById('cancelModalCanceledBy');
    const cancelModalReason = document.getElementById('cancelModalReason');

    const openFinishButtons = document.querySelectorAll('[data-open-finish]');
    const openCancelButtons = document.querySelectorAll('[data-open-cancel]');

    const presets = {
        Baixa: 72,
        Média: 24,
        Alta: 8,
    };

    if (prioritySelect && hoursInput) {
        prioritySelect.addEventListener('change', function () {
            const preset = presets[this.value];
            if (preset) {
                hoursInput.value = preset;
            }
        });
    }

    if (hoursInput) {
        stepButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                const current = parseInt(hoursInput.value || '1', 10);
                const next = this.dataset.stepper === 'up' ? current + 1 : Math.max(1, current - 1);
                hoursInput.value = String(next);
            });
        });
    }

    function bindModal(modal, buttons, onOpen) {
        if (!modal || buttons.length === 0) {
            return;
        }

        buttons.forEach(function (button) {
            button.addEventListener('click', function () {
                onOpen(button, modal);
            });
        });

        const closeButton = modal.querySelector('[data-close-modal]');
        if (closeButton) {
            closeButton.addEventListener('click', function () {
                modal.hidden = true;
            });
        }

        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                modal.hidden = true;
            }
        });
    }

    bindModal(finishModal, openFinishButtons, function (button, modal) {
        if (!finishModalTicketId || !finishModalResponderName || !finishModalWhatHappened || !finishModalHowSolved || !finishModalDelayReason || !finishModalDelayGroup) {
            return;
        }

        finishModalTicketId.value = button.dataset.ticketId || '';
        const overdue = button.dataset.ticketOverdue === '1';

        finishModalResponderName.value = '';
        finishModalWhatHappened.value = '';
        finishModalHowSolved.value = '';
        finishModalDelayReason.value = '';
        finishModalDelayReason.required = overdue;
        finishModalDelayGroup.hidden = !overdue;
        finishModalDelayGroup.classList.toggle('field-group--required', overdue);

        modal.hidden = false;
        finishModalWhatHappened.focus();
    });

    bindModal(cancelModal, openCancelButtons, function (button, modal) {
        if (!cancelModalTicketId || !cancelModalCanceledBy || !cancelModalReason) {
            return;
        }

        cancelModalTicketId.value = button.dataset.ticketId || '';
        cancelModalCanceledBy.value = '';
        cancelModalReason.value = '';

        modal.hidden = false;
        cancelModalCanceledBy.focus();
    });
})();
