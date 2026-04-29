(function () {
    const select = document.querySelector('[data-priority-select]');
    const hoursInput = document.getElementById('estimated_hours');
    const stepButtons = document.querySelectorAll('[data-stepper]');
    const modal = document.getElementById('finishModal');
    const modalTicketId = document.getElementById('modalTicketId');
    const modalWhatHappened = document.getElementById('modalWhatHappened');
    const modalHowSolved = document.getElementById('modalHowSolved');
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

    if (modal && modalTicketId && modalWhatHappened && modalHowSolved) {
        openFinishButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                modalTicketId.value = this.dataset.ticketId || '';
                modalWhatHappened.value = '';
                modalHowSolved.value = '';
                modal.showModal();
                modalWhatHappened.focus();
            });
        });
    }

    if (modal && closeModalButton) {
        closeModalButton.addEventListener('click', function () {
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
                modal.close();
            }
        });
    }
})();
