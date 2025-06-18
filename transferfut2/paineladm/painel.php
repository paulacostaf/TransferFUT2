<?php
session_start();
$pagina = $_GET['pagina'] ?? '';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TransferFut - Painel Administrativo</title>
    <link rel="stylesheet" href="../style.css"> <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    </head>
<body class="pagina-login">
    <a href="../login.php" class="botao-voltar voltar-topo">&#8592; Voltar</a>

    <div class="painel-admin">
        <h1>Painel Administrativo</h1>
        <h2>Como deseja prosseguir?</h2>

        <div class="botoes-admin">
            <a href="?pagina=adicionar"><button>Adicionar</button></a>
            <a href="?pagina=pesquisar_tabela"><button>Pesquisar</button></a>
            <a href="?pagina=alterar"><button>Alterar</button></a>
            <a href="?pagina=excluir"><button>Excluir</button></a>

        </div>

        <div class="conteudo">
            <?php
                $arquivo = __DIR__ . "/$pagina.php";
                if ($pagina && file_exists($arquivo)) {
                    include($arquivo);
                } elseif ($pagina) {
                    echo "<p style='color:red;'>Página não encontrada.</p>";
                }
            ?>
        </div>
    </div>

    <div id="mensagem-popup"></div>

    <?php if (!empty($_SESSION['mensagem'])): ?>
        <script>
            window.onload = function () {
                const popup = document.getElementById("mensagem-popup");
                popup.className = "<?= $_SESSION['tipo_mensagem'] ?> show";
                popup.innerText = "<?= $_SESSION['mensagem'] ?>";

                setTimeout(() => {
                    popup.classList.remove("show");
                }, 4000);
            }
        </script>
        <?php unset($_SESSION['mensagem'], $_SESSION['tipo_mensagem']); ?>
    <?php endif; ?>
</body>
</html>