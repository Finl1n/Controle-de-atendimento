<div class="role-screen">
    <section class="role-card">
        <?php if ($flash !== null): ?>
            <?php require __DIR__ . '/../partials/flash.php'; ?>
        <?php endif; ?>

        <div class="role-hero">
            <p class="eyebrow">Controle de Atendimentos</p>
            <h1>Escolha seu perfil</h1>
            <p>Selecione como deseja usar o sistema. O nome informado será usado nos chamados e nos fechamentos.</p>
        </div>

        <div class="role-notice">
            <strong>Importante</strong>
            <p>Se entrar como solicitante, use sempre o mesmo nome para conseguir ver os chamados salvos depois. Como responsável, o nome pode variar sem afetar o acesso ao atendimento.</p>
        </div>

        <div class="role-grid">
            <form class="role-option" method="post">
                <input type="hidden" name="action" value="select_role">
                <input type="hidden" name="role" value="solicitante">
                <span class="role-badge role-badge--blue">Solicitante</span>
                <h2>Solicitante</h2>
                <p>Abre chamados, vê o próprio histórico e acompanha o status.</p>
                <div class="field-group">
                    <div class="field-title"><strong>Seu nome</strong></div>
                    <input name="name" placeholder="Digite seu nome" required>
                </div>
                <ul class="role-list">
                    <li>Abrir chamado</li>
                    <li>Ver chamados criados</li>
                    <li>Acompanhar andamento</li>
                </ul>
                <p class="role-tip">Use o mesmo nome sempre que quiser reencontrar seus chamados.</p>
                <button type="submit">Entrar como solicitante</button>
            </form>

            <form class="role-option role-option--accent" method="post">
                <input type="hidden" name="action" value="select_role">
                <input type="hidden" name="role" value="responsavel">
                <span class="role-badge role-badge--green">Responsável</span>
                <h2>Responsável</h2>
                <p>Acompanha chamados, inicia atendimento e registra o fechamento.</p>
                <div class="field-group">
                    <div class="field-title"><strong>Seu nome</strong></div>
                    <input name="name" placeholder="Digite seu nome" required>
                </div>
                <ul class="role-list">
                    <li>Acompanhar chamados</li>
                    <li>Iniciar atendimento</li>
                    <li>Finalizar e justificar atrasos</li>
                </ul>
                <p class="role-tip">O nome serve para registrar quem respondeu o chamado.</p>
                <button type="submit" class="secondary">Entrar como responsável</button>
            </form>
        </div>
    </section>
</div>
