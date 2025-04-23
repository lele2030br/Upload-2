<?php
// Exibir erros para depuração (remova em produção)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configuração de diretório para armazenar vídeos
$videoDir = "videos/";
if (!is_dir($videoDir)) {
    if (!mkdir($videoDir, 0777, true)) {
        die("Erro ao criar o diretório de vídeos.");
    }
}

// Função para gerar nomes de arquivos seguros
function gerarNomeSeguro($nome) {
    return preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $nome);
}

// Configuração para armazenar denúncias
$reportsFile = "reports.json";
$reports = file_exists($reportsFile) ? json_decode(file_get_contents($reportsFile), true) : [];

// Verifica se um arquivo foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['video'])) {
        $video = $_FILES['video'];
        $videoName = gerarNomeSeguro(basename($video['name']));
        $targetPath = $videoDir . $videoName;

        // Verifica erros no upload
        if ($video['error'] === UPLOAD_ERR_OK) {
            $fileMimeType = mime_content_type($video['tmp_name']);
            if (strpos($fileMimeType, 'video/') === 0) {
                if (move_uploaded_file($video['tmp_name'], $targetPath)) {
                    echo "<script>alert('Vídeo enviado com sucesso!');</script>";
                } else {
                    echo "<script>alert('Erro ao mover o arquivo para o diretório de destino.');</script>";
                }
            } else {
                echo "<script>alert('Erro: O arquivo enviado não é um vídeo válido.');</script>";
            }
        } else {
            echo "<script>alert('Erro ao enviar o vídeo.');</script>";
        }
    }

    // Processa denúncia
    if (isset($_POST['report_video'])) {
        $videoName = $_POST['video_name'];
        $reason = $_POST['reason'];

        if (!empty($videoName) && !empty($reason)) {
            $reports[] = [
                'video' => $videoName,
                'reason' => $reason,
                'timestamp' => date('Y-m-d H:i:s')
            ];
            file_put_contents($reportsFile, json_encode($reports, JSON_PRETTY_PRINT));
            echo "<script>alert('Denúncia registrada com sucesso!');</script>";
        } else {
            echo "<script>alert('Por favor, preencha todos os campos da denúncia.');</script>";
        }
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
        </div>
    </nav>

    <div class="container py-5">
        <!-- Envio de Vídeos -->
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <h1 class="text-center mb-4">Envie seu Vídeo</h1>
                <form action="index.php" method="post" enctype="multipart/form-data" class="card p-4 shadow-sm">
                    <div class="mb-3">
                        <label for="video" class="form-label">Escolha um vídeo para enviar</label>
                        <input type="file" name="video" id="video" accept="video/*" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Enviar</button>
                </form>
            </div>
        </div>

        <!-- Lista de Vídeos -->
        <div class="row mt-5">
            <div class="col-12">
                <h2 class="text-center mb-4">Vídeos Enviados</h2>
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
            const button = event.relatedTarget;
            const videoName = button.getAttribute('data-video');
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
