(function () {
    const select = document.querySelector('[data-priority-select]');
    const hoursInput = document.getElementById('estimated_hours');
    const stepButtons = document.querySelectorAll('[data-stepper]');
    const modal = document.getElementById('finishModal');
    const modalTicketId = document.getElementById('modalTicketId');
    const modalResponderName = document.getElementById('modalResponderName');
    const modalWhatHappened = document.getElementById('modalWhatHappened');
    const modalHowSolved = document.getElementById('modalHowSolved');
    const modalDelayReason = document.getElementById('modalDelayReason');
    const modalDelayGroup = document.querySelector('[data-delay-group]');
    const openFinishButtons = document.querySelectorAll('[data-open-finish]');
    const closeModalButton = document.querySelector('[data-close-modal]');

    const presets = {
        Baixa: 72,
        Média: 24,
        Alta: 8,
    };

    if (select && hoursInput) {
        select.addEventListener('change', function () {
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
                hoursInput.value = next;
            });
        });
    }

    if (modal && modalTicketId && modalResponderName && modalWhatHappened && modalHowSolved && modalDelayReason && modalDelayGroup) {
        const defaultResponderName = modalResponderName.dataset.defaultValue || '';

        openFinishButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                modalTicketId.value = this.dataset.ticketId || '';
                const overdue = this.dataset.ticketOverdue === '1';

                modalResponderName.value = defaultResponderName;
                modalWhatHappened.value = '';
                modalHowSolved.value = '';
                modalDelayReason.value = '';
                modalDelayReason.required = overdue;
                modalDelayGroup.hidden = !overdue;
                modalDelayGroup.style.display = overdue ? '' : 'none';
                modalDelayGroup.classList.toggle('field-group--required', overdue);

                modal.showModal();
                modalWhatHappened.focus();
            });
        });
    }

    if (modal && closeModalButton) {
        closeModalButton.addEventListener('click', function () {
            modalDelayGroup.hidden = true;
            modalDelayGroup.style.display = 'none';
            modal.close();
        });

        modal.addEventListener('click', function (event) {
            const rect = modal.getBoundingClientRect();
            const clickedOutside = (
                event.clientX < rect.left ||
                event.clientX > rect.right ||
                event.clientY < rect.top ||
                event.clientY > rect.bottom
            );

            if (clickedOutside) {
                modalDelayGroup.hidden = true;
                modalDelayGroup.style.display = 'none';
                modal.close();
            }
        });
    }
})();
