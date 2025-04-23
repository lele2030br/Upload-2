<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: login.php');
    exit;
}

// Configuração de diretório e denúncias
$videoDir = "videos/";
$reportsFile = "reports.json"; // Arquivo para armazenar denúncias (JSON)

// Carrega denúncias
$reports = file_exists($reportsFile) ? json_decode(file_get_contents($reportsFile), true) : [];

// Filtro de vídeos
$searchQuery = isset($_GET['search']) ? strtolower(trim($_GET['search'])) : '';
$videos = array_diff(scandir($videoDir), array('..', '.'));
$filteredVideos = array_filter($videos, function ($video) use ($searchQuery) {
    return $searchQuery === '' || strpos(strtolower($video), $searchQuery) !== false;
});

// Excluir vídeo
if (isset($_POST['delete_video'])) {
    $videoToDelete = $_POST['video'];
    unlink($videoDir . $videoToDelete);
    header('Location: admin.php');
    exit;
}

// Excluir denúncia
if (isset($_POST['delete_report'])) {
    $reportToDelete = $_POST['report_index'];
    unset($reports[$reportToDelete]);
    file_put_contents($reportsFile, json_encode(array_values($reports)));
    header('Location: admin.php');
    exit;
}

// Alterar nome do vídeo
if (isset($_POST['rename_video'])) {
    $oldName = $_POST['old_name'];
    $newName = $_POST['new_name'];
    if (!empty($oldName) && !empty($newName)) {
        $oldPath = $videoDir . $oldName;
        $newPath = $videoDir . $newName;
        if (file_exists($oldPath)) {
            rename($oldPath, $newPath);
            echo "<script>alert('Nome do arquivo alterado com sucesso!');</script>";
        } else {
            echo "<script>alert('O arquivo original não foi encontrado.');</script>";
        }
    } else {
        echo "<script>alert('Por favor, preencha os dois campos para alterar o nome do vídeo.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Admin</a>
            <button class="btn btn-outline-light ms-auto" onclick="location.href='logout.php'">Sair</button>
        </div>
    </nav>
    <div class="container py-5">
        <h1 class="text-center mb-5">Painel Administrativo</h1>

        <!-- Menu -->
        <div class="mb-4">
            <ul class="nav nav-tabs" id="adminMenu">
                <li class="nav-item">
                    <a class="nav-link active" href="#videos" data-bs-toggle="tab">Gerenciar Vídeos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#reports" data-bs-toggle="tab">Gerenciar Denúncias</a>
                </li>
            </ul>
        </div>

        <!-- Conteúdo -->
        <div class="tab-content">
            <!-- Gerenciar Vídeos -->
            <div class="tab-pane fade show active" id="videos">
                <h3>Gerenciar Vídeos</h3>
                <form method="GET" class="mb-4">
                    <input type="text" name="search" class="form-control" placeholder="Buscar vídeos..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                </form>
                <ul class="list-group">
                    <?php foreach ($filteredVideos as $video): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><?php echo $video; ?></span>
                            <div>
                                <!-- Formulário para renomear vídeo -->
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#renameModal" data-video="<?php echo $video; ?>">Renomear</button>
                                <!-- Botão para excluir vídeo -->
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="video" value="<?php echo $video; ?>">
                                    <button type="submit" name="delete_video" class="btn btn-danger btn-sm">Excluir</button>
                                </form>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Gerenciar Denúncias -->
            <div class="tab-pane fade" id="reports">
                <h3>Gerenciar Denúncias</h3>
                <?php if (empty($reports)): ?>
                    <p>Nenhuma denúncia registrada.</p>
                <?php else: ?>
                    <ul class="list-group">
                        <?php foreach ($reports as $index => $report): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?php echo $report['video']; ?> - <?php echo $report['reason']; ?>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="report_index" value="<?php echo $index; ?>">
                                    <button type="submit" name="delete_report" class="btn btn-danger btn-sm">Excluir</button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal para Renomear Vídeo -->
    <div class="modal fade" id="renameModal" tabindex="-1" aria-labelledby="renameModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="admin.php">
                    <div class="modal-header">
                        <h5 class="modal-title" id="renameModalLabel">Renomear Vídeo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="old_name" id="oldName">
                        <div class="mb-3">
                            <label for="new_name" class="form-label">Novo Nome</label>
                            <input type="text" name="new_name" id="newName" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="rename_video" class="btn btn-primary">Salvar Alterações</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Script para preencher o nome do vídeo no modal de renomear
        const renameModal = document.getElementById('renameModal');
        renameModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget; // Botão que acionou o modal
            const videoName = button.getAttribute('data-video'); // Nome do vídeo
            const modalInput = renameModal.querySelector('#oldName');
            modalInput.value = videoName;
        });
    </script>
</body>
</html>