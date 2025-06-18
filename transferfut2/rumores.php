<?php
require_once("conexao.php");
include("topo.php");

$grauConfiancaSelecionado = $_POST['grau_confianca'] ?? '';

$grausConfiancaDisponiveis = [
    'Alta',
    'Média',
    'Baixa'
];

if (isset($_POST['limpar_filtros']) && $_POST['limpar_filtros'] == '1') {
    $grauConfiancaSelecionado = '';
}


$condicoes_where = [];
$params = [];
$types = "";

if (!empty($grauConfiancaSelecionado)) {
    $condicoes_where[] = "grau_confianca = ?";
    $params[] = $grauConfiancaSelecionado;
    $types .= "s"; 
}

$query = "SELECT * FROM view_rumores_em_aberto";

if (!empty($condicoes_where)) {
    $query .= " WHERE " . implode(" AND ", $condicoes_where);
}


$query .= " ORDER BY data_rumor DESC";


$stmt = $conexao->prepare($query);

if ($stmt === false) {
    die("Erro na preparação da consulta de rumores: " . $conexao->error);
}

if (!empty($params)) {
    if (version_compare(PHP_VERSION, '5.6.0', '>=')) {
        $stmt->bind_param($types, ...$params);
    } else {
        // Fallback para PHP < 5.6
        $refs = [];
        foreach($params as $key => $value) $refs[$key] = &$params[$key];
        call_user_func_array([$stmt, 'bind_param'], array_merge([$types], $refs));
    }
}

$stmt->execute();
$resultado = $stmt->get_result();
$rumores = $resultado->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>TransferFut - Rumores</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php // include("topo.php"); ?>

    <div class="container-rumores">
        <h2>Rumores de Transferência</h2>

        <form method="POST" class="form-filtros-rumores"> <label for="grau_confianca">Grau de Confiança:</label>
            <select name="grau_confianca" id="grau_confianca">
                <option value="">-- Todos os graus --</option>
                <?php foreach ($grausConfiancaDisponiveis as $grau): ?>
                    <option value="<?= htmlspecialchars($grau) ?>" <?= $grauConfiancaSelecionado == $grau ? 'selected' : '' ?>>
                        <?= htmlspecialchars($grau) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Buscar</button>
            <button type="submit" name="limpar_filtros" value="1">Limpar Filtros</button>
        </form>

        <?php if (!empty($rumores)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Jogador</th>
                        <th>Destino</th>
                        <th>Data</th>
                        <th>Confiança</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rumores as $r): ?>
                        <tr>
                            <td>
                                <img src="img/<?= htmlspecialchars($r['imagem_jogador']) ?>" alt="<?= htmlspecialchars($r['nome_jogador']) ?>" class="img-jogador">
                                <?= htmlspecialchars($r['nome_jogador']) ?>
                            </td>
                            <td>
                                <img src="img/escudos/<?= htmlspecialchars($r['escudo_destino']) ?>" alt="<?= htmlspecialchars($r['time_destino']) ?>" class="escudo-time">
                                <?= htmlspecialchars($r['time_destino']) ?>
                            </td>
                            <td><?= date('d/m/Y', strtotime($r['data_rumor'])) ?></td>
                            <td><?= htmlspecialchars($r['grau_confianca']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align:center; color:#ccc;">Nenhum rumor encontrado com os filtros selecionados.</p>
        <?php endif; ?>
    </div>
</body>
</html>