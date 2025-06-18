<?php
require_once("conexao.php");
include("topo.php");

$anoSelecionado = $_POST['ano'] ?? '';
$statusSelecionado = $_POST['status'] ?? ''; 
$transferencias = [];

$statusDisponiveis = [
    'Contratado',
    'Emprestimo',
];

$condicoes_where = ["t.visivel = TRUE"]; 
$params = [];
$types = "";

if (!empty($anoSelecionado)) {

    $condicoes_where[] = "t.data_transf >= ? AND t.data_transf < ?";
    
    $params[] = $anoSelecionado . "-01-01";
    $types .= "s";

    $params[] = ($anoSelecionado + 1) . "-01-01";
    $types .= "s";
}
if (!empty($statusSelecionado)) {
    $condicoes_where[] = "t.status_jog = ?";
    $params[] = $statusSelecionado;
    $types .= "s"; 
}

$sql = "SELECT
            t.id, t.data_transf, t.valor_transf, t.status_jog,
            j.nome_jogador, j.imagem AS imagem_jogador,
            ta.nome_time AS time_anterior, ta.escudo AS escudo_anterior,
            tn.nome_time AS time_novo, tn.escudo AS escudo_novo
        FROM Transferencia t
        INNER JOIN Jogador j ON t.id_jogador = j.id
        LEFT JOIN Time ta ON ta.id = t.id_time_antigo
        LEFT JOIN Time tn ON tn.id = t.id_time_novo";

if (!empty($condicoes_where)) {
    $sql .= " WHERE " . implode(" AND ", $condicoes_where);
}

$sql .= " ORDER BY t.data_transf DESC";

$stmt = $conexao->prepare($sql);

if ($stmt === false) {
    die("Erro na preparação da consulta: " . $conexao->error);
}

if (!empty($params)) {
    if (version_compare(PHP_VERSION, '5.6.0', '>=')) {
        $stmt->bind_param($types, ...$params);
    } else {
        $refs = [];
        foreach($params as $key => $value) $refs[$key] = &$params[$key];
        call_user_func_array([$stmt, 'bind_param'], array_merge([$types], $refs));
    }
}

$stmt->execute();
$resultado = $stmt->get_result();
$transferencias = $resultado->fetch_all(MYSQLI_ASSOC);
$stmt->close();

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TransferFut - Transferências</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php // include("topo.php"); ?>

    <div class="container-transf"> <h2>Transferências por Ano e Status</h2>
        
        <form method="POST" class="form-filtros-transferencias"> <label for="ano">Ano:</label>
            <select name="ano" id="ano">
                <option value="">-- Todos os anos --</option>
                <?php 
                $currentYear = date('Y');
                for ($y = $currentYear; $y >= 2000; $y--):
                ?>
                    <option value="<?= $y ?>" <?= $anoSelecionado == $y ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>

            <label for="status">Status:</label>
            <select name="status" id="status">
                <option value="">-- Todos os status --</option>
                <?php foreach ($statusDisponiveis as $status): ?>
                    <option value="<?= htmlspecialchars($status) ?>" <?= $statusSelecionado == $status ? 'selected' : '' ?>>
                        <?= htmlspecialchars($status) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Buscar</button>
            <button type="submit" name="limpar_filtros" value="1">Limpar Filtros</button>
        </form>

        <?php if (!empty($transferencias)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Jogador</th>
                        <th>De</th>
                        <th>Para</th>
                        <th>Valor</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transferencias as $t): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($t['data_transf'])) ?></td>
                            <td>
                                <img src="img/<?= htmlspecialchars($t['imagem_jogador']) ?>" alt="<?= htmlspecialchars($t['nome_jogador']) ?>" class="img-jogador">
                                <?= htmlspecialchars($t['nome_jogador']) ?>
                            </td>
                            <td>
                                <img src="img/escudos/<?= htmlspecialchars($t['escudo_anterior']) ?>" alt="<?= htmlspecialchars($t['time_anterior']) ?>" class="escudo-time">
                                <?= htmlspecialchars($t['time_anterior']) ?>
                            </td>
                            <td>
                                <img src="img/escudos/<?= htmlspecialchars($t['escudo_novo']) ?>" alt="<?= htmlspecialchars($t['time_novo']) ?>" class="escudo-time">
                                <?= htmlspecialchars($t['time_novo']) ?>
                            </td>
                            <td>€ <?= number_format($t['valor_transf'], 2, ',', '.') ?></td>
                            <td><?= htmlspecialchars($t['status_jog']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align:center; color: #ccc">Nenhuma transferência encontrada com os filtros selecionados.</p>
        <?php endif; ?>
    </div>
</body>
</html>