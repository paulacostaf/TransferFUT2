<?php
require_once("conexao.php");
include("topo.php");

$conexao->set_charset('utf8mb4');
$jogador = $_POST['jogador'] ?? '';

$imagem       = 'img/cristiano_ronaldo.png';
$gols         = $assistencias = $partidas = $ca = $cv = '--';
$valorMercado = 0;

if (!empty($jogador)) {
    $busca = "%{$jogador}%";

    $sql = "
      SELECT 
        c.num_gols_media, 
        c.num_assist_media, 
        c.num_jogos_media, 
        c.num_ca_media, 
        c.num_cv_media, 
        c.valor_mercado,
        j.imagem
      FROM Carreira c
      INNER JOIN Jogador j ON c.id_jogador = j.id
      WHERE j.nome_jogador COLLATE utf8mb4_unicode_ci LIKE ?
      LIMIT 1
    ";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("s", $busca);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $dados = $resultado->fetch_assoc();

    if ($dados) {
        $gols         = $dados['num_gols_media'];
        $assistencias = $dados['num_assist_media'];
        $partidas     = $dados['num_jogos_media'];
        $ca           = $dados['num_ca_media'];
        $cv           = $dados['num_cv_media'];
        $valorMercado = $dados['valor_mercado'];

        $arquivoImg = trim($dados['imagem']);
        $caminhoAbsoluto = __DIR__ . "/img/{$arquivoImg}";
        if (file_exists($caminhoAbsoluto)) {
            $imagem = "img/{$arquivoImg}";
        }
    }
}

// Gerar lista de jogadores para autocomplete
$jogadores = [];
$res = $conexao->query("SELECT DISTINCT nome_jogador FROM Jogador ORDER BY nome_jogador");
while ($row = $res->fetch_assoc()) {
    $jogadores[] = $row['nome_jogador'];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>TransferFut - Performance</title>
  <link rel="stylesheet" href="style.css" />
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
</head>
<body class="pagina-performance">
  <div class="layout-principal">
    <div class="performance-container">

      <div class="performance-img">
        <img
          src="<?= htmlspecialchars($imagem, ENT_QUOTES) ?>"
          alt="Jogador Selecionado"
          class="imagem-jogador"
        />
      </div>

      <div class="performance-dados">
        <h2>Desempenho Médio do Jogador</h2>
        <p style="color: #bbb; font-size: 0.9rem; margin-bottom: 20px;">
          Estatísticas médias dos últimos 5 anos de carreira.
        </p>

        <form class="form-performance" method="POST">
          <label for="jogador">Nome do Jogador:</label>
          <input
            list="lista-jogadores"
            id="jogador"
            name="jogador"
            placeholder="Ex: Cristiano Ronaldo"
            value="<?= htmlspecialchars($jogador, ENT_QUOTES) ?>"
            required
          />
          <datalist id="lista-jogadores">
            <?php foreach ($jogadores as $nome): 
              $plain = iconv('UTF-8', 'ASCII//TRANSLIT', $nome);
              $plain = preg_replace('/[^A-Za-z0-9 ]+/', '', $plain);
            ?>
              <option value="<?= htmlspecialchars($nome, ENT_QUOTES) ?>">
              <?php if (mb_strtolower($plain) !== mb_strtolower($nome)): ?>
                <option value="<?= htmlspecialchars($plain, ENT_QUOTES) ?>">
              <?php endif; ?>
            <?php endforeach; ?>
          </datalist>
          <button type="submit">Buscar</button>
        </form>

        <div class="resultado-performance">
          <h3>Estatísticas (Média Anual):</h3>
          <ul>
            <li>Partidas: <span><?= htmlspecialchars($partidas) ?></span></li>
            <li>Gols: <span><?= htmlspecialchars($gols) ?></span></li>
            <li>Assistências: <span><?= htmlspecialchars($assistencias) ?></span></li>
            <li>Cartões Amarelos: <span><?= htmlspecialchars($ca) ?></span></li>
            <li>Cartões Vermelhos: <span><?= htmlspecialchars($cv) ?></span></li>
            <li>Valor de Mercado: <span>€ <?= number_format($valorMercado, 2, ',', '.') ?></span></li>
          </ul>
        </div>

      </div>
    </div>
  </div>
</body>
</html>
