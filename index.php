<?php
// Exibir erros para depuração (remova em produção)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configuração de diretório e arquivo de denúncias
$videoDir = "videos/";
$reportsFile = "reports.json"; // Arquivo para armazenar denúncias (JSON)

// Carregar denúncias existentes ou criar arquivo
$reports = file_exists($reportsFile) ? json_decode(file_get_contents($reportsFile), true) : [];

// Processar denúncia de vídeo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_video'])) {
    $videoName = $_POST['video_name'];
    $reason = $_POST['reason'];

    if (!empty($videoName) && !empty($reason)) {
        $reports[] = [
            'video' => $videoName,
            'reason' => $reason,
            'timestamp' => date('Y-m-d H:i:s') // Data e hora da denúncia
        ];
        file_put_contents($reportsFile, json_encode($reports, JSON_PRETTY_PRINT));
        echo "<script>alert('Denúncia registrada com sucesso!');</script>";
    } else {
        echo "<script>alert('Por favor, preencha todos os campos da denúncia.');</script>";
    }
}

// Lista vídeos existentes
$videos = array_diff(scandir($videoDir), array('..', '.'));
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VideoSite Simples</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Menu de Navegação -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">VideoSite</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Início</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#videos">Vídeos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">Sobre</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <h1 class="text-center mb-4">Envie seu Vídeo</h1>
                <!-- Formulário de Envio -->
                <form action="index.php" method="post" enctype="multipart/form-data" class="card p-4 shadow-sm">
                    <div class="mb-3">
                        <label for="video" class="form-label">Escolha um vídeo para enviar</label>
                        <input type="file" name="video" id="video" accept="video/*" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Enviar</button>
                </form>
            </div>
        </div>

        <div class="row mt-5" id="videos">
            <div class="col-12">
                <h2 class="text-center mb-4">Vídeos Enviados</h2>

                <!-- Lista de Vídeos -->
                <?php if (!empty($videos)): ?>
                    <div class="row">
                        <?php foreach ($videos as $video): ?>
                            <div class="col-md-4 mb-4">
                                <div class="card shadow-sm">
                                    <video class="card-img-top" controls>
                                        <source src="<?php echo $videoDir . $video; ?>" type="video/mp4">
                                        Seu navegador não suporta o elemento de vídeo.
                                    </video>
                                    <div class="card-body">
                                        <h5 class="card-title text-truncate"><?php echo $video; ?></h5>
                                        <div class="d-flex justify-content-between">
                                            <!-- Botão de Denunciar -->
                                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#reportModal" data-video="<?php echo $video; ?>">Denunciar</button>
                                            <!-- Botão de Compartilhar -->
                                            <button class="btn btn-secondary btn-sm" onclick="shareVideo('<?php echo $videoDir . $video; ?>')">Compartilhar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-center text-secondary">Nenhum vídeo enviado ainda.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal de Denúncia -->
    <div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="index.php">
                    <div class="modal-header">
                        <h5 class="modal-title" id="reportModalLabel">Denunciar Vídeo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="video_name" id="videoName">
                        <div class="mb-3">
                            <label for="reason" class="form-label">Motivo da Denúncia</label>
                            <textarea name="reason" id="reason" class="form-control" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="report_video" class="btn btn-danger">Enviar Denúncia</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Scripts -->
    <script>
        // Script para preencher o nome do vídeo no modal
        const reportModal = document.getElementById('reportModal');
        reportModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget; // Botão que acionou o modal
            const videoName = button.getAttribute('data-video'); // Nome do vídeo
            const modalInput = reportModal.querySelector('#videoName');
            modalInput.value = videoName;
        });

        // Função para compartilhar vídeos
        function shareVideo(videoUrl) {
            if (navigator.share) {
                navigator.share({
                    title: 'Veja este vídeo!',
                    text: 'Confira este vídeo incrível:',
                    url: videoUrl
                }).then(() => {
                    console.log('Vídeo compartilhado com sucesso.');
                }).catch((error) => {
                    console.error('Erro ao compartilhar:', error);
                });
            } else {
                alert('A API de compartilhamento não é suportada neste navegador.');
            }
        }
    </script>
</body>
</html>