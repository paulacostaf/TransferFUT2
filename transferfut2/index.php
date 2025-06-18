<?php
require_once("conexao.php");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>TransferFut - Início</title>
    <link rel="stylesheet" href="style.css" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>

<header class="topo">
    <a href="login.php" class="botao-login">LOGIN ADMINISTRADOR</a>
    <img src="img/logo.png" alt="TransferFut Logo" class="logo-header">
</header>

<nav class="menu-nav">
    <ul>
        <li><a href="index.php">INÍCIO</a></li>
        <li><a href="transferencias.php">TRANSFERÊNCIAS</a></li>
        <li><a href="rumores.php">RUMORES</a></li>
        <li><a href="jogadores.php">JOGADORES</a></li>
        <li><a href="performance.php">PERFORMANCE</a></li>
    </ul>
</nav>

<div class="layout-principal">

    <main class="transferencias">
        <h2>Últimas Transferências</h2>

        <?php
        $sql = "CALL obter_ultimas_transferencias()";
        $resultado = $conexao->query($sql);


        while ($row = $resultado->fetch_assoc()) {
            $jogador = $row['nome_jogador'];
            $imagemJogador = $row['imagem'];
            $valor = number_format($row['valor_transf'] / 1000000, 2, ',', '.'); 
            $timeAntigo = strtolower(str_replace(' ', '_', $row['time_antigo']));
            $timeNovo = strtolower(str_replace(' ', '_', $row['time_novo']));

            $nomeSeparado = explode(' ', trim($jogador)); 
            $primeiroNomeExibicao = '';
            $ultimoNomeExibicao = '';

            if (count($nomeSeparado) > 1) {
                $primeiroNomeExibicao = strtoupper($nomeSeparado[0]);
                $ultimoNomeExibicao = strtoupper(implode(' ', array_slice($nomeSeparado, 1))); 
            } else {
                $primeiroNomeExibicao = strtoupper($jogador);
                $ultimoNomeExibicao = ''; 
            }

            echo "
            <div class='transfer-card'>
                <img src='img/escudos/{$timeAntigo}.png' class='escudo' alt='{$row['time_antigo']}'>
                <span class='seta'>>>></span>
                <div class='jogador-box-lateral'>
                    <img class='jogador-img' src='img/{$imagemJogador}' alt='{$jogador}'>
                    <div class='nome-lateral'>
                        <span class='nome-topo'>{$primeiroNomeExibicao}</span>
                        <span class='nome-baixo'>{$ultimoNomeExibicao}</span> <span class='valor-transferencia'>{$valor} mi. €</span>
                    </div>
                </div>
                <span class='seta'>>>></span>
                <img src='img/escudos/{$timeNovo}.png' class='escudo' alt='{$row['time_novo']}'>
            </div>";
        }
        ?>

    </main>

    <aside class="noticias">
        <h2>MUNDO DA BOLA:</h2>

        <div class="noticia-card">
            <small>Flamengo</small>
            <div class="conteudo-noticia">
                <p>Perto do Flamengo, Jorginho se despede do Arsenal</p>
                <img src="img/jorginho.png" alt="Jorginho">
            </div>
            <div class="info-extra">🕒 28 de mai. de 2025 | 💬 0</div>
        </div>

        <div class="noticia-card">
            <small>Especial & Opinião</small>
            <div class="conteudo-noticia">
                <p>Sterling, Chiesa e as contratações decepcionantes da PL 24/25</p>
                <img src="img/sterling.png" alt="Sterling e Chiesa">
            </div>
            <div class="info-extra">🕒 26 de mai. de 2025 | 💬 0</div>
        </div>

        <div class="noticia-card">
            <small>Al-Ittihad</small>
            <div class="conteudo-noticia">
                <p>Al Ittihad está interessado em Bruno Guimarães; Al Hilal monitora</p>
                <img src="img/bruno_guimaraes.png" alt="Bruno Guimarães">
            </div>
            <div class="info-extra">🕒 20 de mai. de 2025 | 💬 0</div>
        </div>
    </aside>
</div>

</body>
</html>