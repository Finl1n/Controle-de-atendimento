<?php
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controle de Atendimentos</title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
    <div class="app-shell">
        <?php require __DIR__ . '/partials/sidebar.php'; ?>

        <main class="workspace">
            <?php require __DIR__ . '/partials/topbar.php'; ?>

            <?php if ($flash !== null): ?>
                <?php require __DIR__ . '/partials/flash.php'; ?>
            <?php endif; ?>

            <?php require __DIR__ . '/pages/' . $page . '.php'; ?>
        </main>
    </div>

    <?php require __DIR__ . '/partials/finish-modal.php'; ?>
    <?php require __DIR__ . '/partials/cancel-modal.php'; ?>

    <script src="/assets/js/app.js"></script>
</body>
</html>
