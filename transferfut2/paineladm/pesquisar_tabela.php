<?php
require_once("../conexao.php");


function refValues($arr){
    if (strnatcmp(phpversion(),'5.3') >= 0) 
    {
        $refs = array();
        foreach($arr as $key => $value)
            $refs[$key] = &$arr[$key];
        return $refs;
    }
    return $arr;
}

$tabelasDisponiveis = ["Jogador", "Pais", "Agente", "Posicao", "Time", "Carreira", "Transferencia", "Rumor"];
$tabelaSelecionada = $_POST['tabela'] ?? 'Jogador'; 

$idSelecionado = $_POST['idSelecionado'] ?? ''; 

$resultadoTabela = [];
$dadosParaDropdown = []; 

$colunasDropdown = [
    'Jogador' => ['id_col' => 'id', 'nome_col' => 'nome_jogador'],
    'Pais' => ['id_col' => 'Cod_Pais', 'nome_col' => 'Nome_Pais'],
    'Agente' => ['id_col' => 'id', 'nome_col' => 'nome_agente'],
    'Time' => ['id_col' => 'id', 'nome_col' => 'nome_time'],
    'Carreira' => ['id_col' => 'id_jogador', 'nome_col' => 'id_jogador'],
    'Transferencia' => ['id_col' => 'id', 'nome_col' => 'id'], 
    'Rumor' => ['id_col' => 'id', 'nome_col' => 'id']];


    if (isset($colunasDropdown[$tabelaSelecionada]) || $tabelaSelecionada === 'Carreira') {
        if ($tabelaSelecionada === 'Transferencia') {
            $stmtDropdown = $conexao->query("SELECT T.id AS id, CONCAT(J.nome_jogador, ' - €', FORMAT(T.valor_transf, 2, 'de_DE')) AS nome_exibicao FROM Transferencia T JOIN Jogador J ON T.id_jogador = J.id ORDER BY T.data_transf DESC");
        } elseif ($tabelaSelecionada === 'Rumor') {
            $stmtDropdown = $conexao->query("SELECT R.id AS id, CONCAT(J.nome_jogador, ' -> ', Ti.nome_time) AS nome_exibicao FROM Rumor R JOIN Jogador J ON R.id_jogador = J.id JOIN Time Ti ON R.id_time_destino = Ti.id ORDER BY R.data_rumor DESC");
        } elseif ($tabelaSelecionada === 'Carreira') {
            $stmtDropdown = $conexao->query("SELECT C.id_jogador AS id, J.nome_jogador AS nome_exibicao FROM Carreira C JOIN Jogador J ON C.id_jogador = J.id ORDER BY J.nome_jogador");
        } else {
            $colId = $colunasDropdown[$tabelaSelecionada]['id_col'];
            $colNome = $colunasDropdown[$tabelaSelecionada]['nome_col'];
            $orderBy = ($colId === $colNome) ? $colId : $colNome;
            $stmtDropdown = $conexao->query("SELECT $colId AS id, $colNome AS nome_exibicao FROM $tabelaSelecionada ORDER BY $orderBy");
        }
    
        if ($stmtDropdown) {
            $dadosParaDropdown = $stmtDropdown->fetch_all(MYSQLI_ASSOC);
        }
    
} elseif ($tabelaSelecionada === 'Carreira') { 
    $stmtDropdown = $conexao->query("SELECT C.id_jogador AS id, J.nome_jogador AS nome_exibicao FROM Carreira C JOIN Jogador J ON C.id_jogador = J.id ORDER BY J.nome_jogador");
    
    if ($stmtDropdown) {
        $dadosParaDropdown = $stmtDropdown->fetch_all(MYSQLI_ASSOC);
    }
}
if (in_array($tabelaSelecionada, $tabelasDisponiveis)) {
    $sql = "SELECT * FROM $tabelaSelecionada";
    $params = [];
    $types = "";

    if (isset($idSelecionado) && $idSelecionado !== '' && isset($colunasDropdown[$tabelaSelecionada])) {
        $pkColumnName = $colunasDropdown[$tabelaSelecionada]['id_col']; 
        $sql .= " WHERE $pkColumnName = ?"; 
        
        // Define o tipo de dado com base na tabela
        if ($tabelaSelecionada === 'Pais') {
            $types .= "s"; // CHAR(3)
        } else {
            $types .= "i"; // INT para outras tabelas
        }
    
        $params[] = $idSelecionado;
    }
    

    $stmt = $conexao->prepare($sql);

    if ($stmt === false) {
        die("Erro na preparação da consulta para $tabelaSelecionada: " . $conexao->error . " SQL: " . $sql);
    }

    if (!empty($params) && !empty($types)) {
        $stmt->bind_param($types, ...$params); 
    }
    

    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado) {
        $resultadoTabela = $resultado->fetch_all(MYSQLI_ASSOC);
    }
    $stmt->close();
}
?>

<form method="POST" class="form-select-tabela">
    <label>Selecione a Tabela:</label>
    <select name="tabela" id="selectTabela" required onchange="this.form.submit()">
        <?php foreach ($tabelasDisponiveis as $t): ?>
            <option value="<?= $t ?>" <?= $t === $tabelaSelecionada ? 'selected' : '' ?>><?= $t ?></option>
        <?php endforeach; ?>
    </select>

    <?php 
    $rotuloMap = [
        'Jogador' => 'Jogador',
        'Pais' => 'País',
        'Agente' => 'Agente',
        'Posicao' => 'Posição',
        'Time' => 'Time',
        'Transferencia' => 'Transferência',
        'Rumor' => 'Rumor'
    ];
    $rotuloDropdown = $rotuloMap[$tabelaSelecionada] ?? $tabelaSelecionada;
    
    if (isset($colunasDropdown[$tabelaSelecionada]) || $tabelaSelecionada === 'Carreira'):

    ?>
        <div class="form-pesquisa">
            <label for="selecionaItem">Selecionar <?= htmlspecialchars($rotuloDropdown) ?>:</label>
            <select name="idSelecionado" id="selecionaItem">
                <option value="">-- Todos os <?= htmlspecialchars($tabelaSelecionada) ?> --</option>
                <?php foreach ($dadosParaDropdown as $item): ?>
                    <option value="<?= htmlspecialchars($item['id']) ?>"
                        <?= (string)$item['id'] === (string)($idSelecionado ?? '') ? 'selected' : '' ?>>
                        <?= htmlspecialchars($item['nome_exibicao']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="botao-filtrar">Buscar <?= htmlspecialchars($rotuloDropdown) ?></button>
        </div>
    <?php endif; ?>
</form>

<div class="tabela-container" id="tabelaResultado">
    <?php if (!empty($resultadoTabela)): ?>
        <table class="tabela-dinamica">
            <thead>
                <tr>
                    <?php foreach (array_keys($resultadoTabela[0]) as $coluna): ?>
                        <th><?= htmlspecialchars($coluna) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resultadoTabela as $linha): ?>
                    <tr>
                        <?php foreach ($linha as $valor): ?>
                            <td><?= htmlspecialchars($valor) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Nenhum dado encontrado para a tabela selecionada.</p>
    <?php endif; ?>
</div>