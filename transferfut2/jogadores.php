<?php
require_once("conexao.php");
include("topo.php"); // Mantém o include do topo (cabeçalho e menu)
$conexao->set_charset('utf8mb4');

// Buscar todos os nomes de jogadores para o datalist
$jogadores = [];
$res = $conexao->query("SELECT nome_jogador FROM Jogador ORDER BY nome_jogador");
while ($row = $res->fetch_assoc()) {
    $jogadores[] = $row['nome_jogador'];
}

// Lógica da busca
$busca = $_GET['busca'] ?? '';
$exibirPadrao = false;

if (empty($busca)) {
    $busca = 'Cristiano Ronaldo'; // Jogador padrão para exibição inicial
    $exibirPadrao = true;
}

// Prepara a consulta para buscar o jogador usando a procedure
$sql = "CALL consultar_dados_jogador(?)";

$buscaLike = "%{$busca}%"; // Para buscar por parte do nome
$stmt = $conexao->prepare($sql);

// Verifica se a preparação da query falhou
if ($stmt === false) {
    die("Erro na preparação da consulta de jogador: " . $conexao->error);
}

// Associa o parâmetro de busca à procedure
$stmt->bind_param("s", $buscaLike);

// Executa a procedure
$stmt->execute();
$resultado = $stmt->get_result();
$dados = $resultado->fetch_all(MYSQLI_ASSOC);
$stmt->close(); // Fecha o statement

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>TransferFut - Jogadores</title>
    <link rel="stylesheet" href="style.css" /> <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    </head>
<body>
    <?php
    // O conteúdo do topo.php será incluído aqui (se ele fornecer o cabeçalho/menu)
    // include("topo.php");
    ?>

    <div class="container-jogadores">
        <h2>Buscar Jogador</h2>

        <form class="busca-jogador" method="GET">
            <input type="text" name="busca" list="lista-jogadores" placeholder="Digite o nome do jogador" value="<?= htmlspecialchars($_GET['busca'] ?? '') ?>" />
            <datalist id="lista-jogadores">
                <?php foreach ($jogadores as $nome): 
                    // Sua lógica para tratar caracteres especiais para o datalist
                    $plain = iconv('UTF-8','ASCII//TRANSLIT',$nome);
                    $plain = preg_replace('/[^A-Za-z0-9 ]+/', '', $plain);
                ?>
                    <option value="<?= htmlspecialchars($nome) ?>">
                    <?php if ($plain !== $nome): ?>
                        <option value="<?= htmlspecialchars($plain) ?>">
                    <?php endif; ?>
                <?php endforeach; ?>
            </datalist>
            <button type="submit">🔍 Buscar</button>
        </form>

        <?php if (!empty($busca) || $exibirPadrao): // Exibe o card se houver busca ou for o padrão ?>
            <?php if (count($dados) === 0): ?>
                <p>Nenhum jogador encontrado com esse nome.</p>
            <?php else: ?>
                <?php foreach ($dados as $j): ?>
                    <?php
                        $dataNasc = new DateTime($j['data_nascimento']);
                        $hoje = new DateTime();
                        $idade = $hoje->diff($dataNasc)->y;
                    ?>
                    <div class="jogador-card">
                        <img src="img/<?= htmlspecialchars($j['imagem']) ?>" alt="<?= htmlspecialchars($j['nome_jogador']) ?>">
                        <div class="jogador-info">
                            <span class="nome-jogador-titulo"><?= htmlspecialchars($j['nome_jogador']) ?></span>

                            <span><strong>Data Nasc.:</strong> <?= $dataNasc->format('d/m/Y') ?> (<?= $idade ?> anos)</span>
                            <span><strong>Altura:</strong> <?= number_format($j['altura'], 2, ',', '.') ?> m</span>
                            <span><strong>País:</strong> <?= htmlspecialchars($j['pais']) ?></span>
                            <span><strong>Posição:</strong> <?= htmlspecialchars($j['posicao']) ?></span>
                            <span><strong>Agente:</strong> <?= htmlspecialchars($j['agente']) ?></span>
                            <span><strong>Time Atual:</strong> <?= htmlspecialchars($j['time']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>