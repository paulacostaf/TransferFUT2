<?php
require_once("../conexao.php");
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// Buscando dados para popular todos os dropdowns da página
$paises = $conexao->query("SELECT Cod_Pais, Nome_Pais FROM Pais ORDER BY Nome_Pais")->fetch_all(MYSQLI_ASSOC);
$agentes = $conexao->query("SELECT id, nome_agente FROM Agente ORDER BY nome_agente")->fetch_all(MYSQLI_ASSOC);
$times = $conexao->query("SELECT id, nome_time FROM Time ORDER BY nome_time")->fetch_all(MYSQLI_ASSOC);
$posicoes = $conexao->query("SELECT Cod_Posicao, Nome_Posicao FROM Posicao ORDER BY Nome_Posicao")->fetch_all(MYSQLI_ASSOC);
$jogadores = $conexao->query("SELECT id, nome_jogador FROM Jogador ORDER BY nome_jogador")->fetch_all(MYSQLI_ASSOC);
$transferencias = $conexao->query("SELECT id FROM Transferencia ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
$rumores = $conexao->query("SELECT id FROM Rumor ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);

// Variável para a mensagem de feedback imediato
$mensagem = "";

// Início do processamento do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Alterar jogador
    if (isset($_POST['alterar_jogador'])) {
        $id_jogador = $_POST['id_jogador'];
        $campo = $_POST['campo_jogador'];

        // Lógica para cada campo do jogador
        if ($campo === 'nome_jogador' && !empty($_POST['nome_novo'])) {
            $novo_valor = $_POST['nome_novo'];
            $stmt = $conexao->prepare("UPDATE Jogador SET nome_jogador = ? WHERE id = ?");
            $stmt->bind_param("si", $novo_valor, $id_jogador);
        } elseif ($campo === 'data_nascimento' && !empty($_POST['data_nasc_nova'])) {
            $novo_valor = $_POST['data_nasc_nova'];
            $stmt = $conexao->prepare("UPDATE Jogador SET data_nascimento = ? WHERE id = ?");
            $stmt->bind_param("si", $novo_valor, $id_jogador);
        } elseif ($campo === 'altura' && !empty($_POST['altura_nova'])) {
            $novo_valor = floatval(str_replace(',', '.', $_POST['altura_nova']));
            $stmt = $conexao->prepare("UPDATE Jogador SET altura = ? WHERE id = ?");
            $stmt->bind_param("di", $novo_valor, $id_jogador);
        } elseif ($campo === 'cod_pais' && !empty($_POST['pais_novo'])) {
            $novo_valor = $_POST['pais_novo'];
            $stmt = $conexao->prepare("UPDATE Jogador SET cod_pais = ? WHERE id = ?");
            $stmt->bind_param("si", $novo_valor, $id_jogador);
        } elseif ($campo === 'id_agente' && !empty($_POST['agente_novo'])) {
            $novo_valor = $_POST['agente_novo'];
            $stmt = $conexao->prepare("UPDATE Jogador SET id_agente = ? WHERE id = ?");
            $stmt->bind_param("ii", $novo_valor, $id_jogador);
        } elseif ($campo === 'cod_Posicao' && !empty($_POST['posicao_nova'])) {
            $novo_valor = $_POST['posicao_nova'];
            $stmt = $conexao->prepare("UPDATE Jogador SET cod_Posicao = ? WHERE id = ?");
            $stmt->bind_param("si", $novo_valor, $id_jogador);
        } elseif ($campo === 'id_time_atual' && !empty($_POST['time_novo'])) {
            $novo_valor = $_POST['time_novo'];
            $stmt = $conexao->prepare("UPDATE Jogador SET id_time_atual = ? WHERE id = ?");
            $stmt->bind_param("ii", $novo_valor, $id_jogador);
        } elseif ($campo === 'imagem' && !empty($_FILES['imagem_nova']['name'])) {
            $imagem = $_FILES['imagem_nova'];
            $nomeImagem = basename($imagem['name']);
            $caminhoDestino = '../img/' . $nomeImagem;
            if (!is_dir('../img')) mkdir('../img', 0755, true);
            if (move_uploaded_file($imagem['tmp_name'], $caminhoDestino)) {
                $stmt = $conexao->prepare("UPDATE Jogador SET imagem = ? WHERE id = ?");
                $stmt->bind_param("si", $nomeImagem, $id_jogador);
            } else {
                $mensagem = "❌ Falha ao enviar nova imagem.";
            }
        }

        if (isset($stmt)) {
            if ($stmt->execute()) {
                $mensagem = "✅ Jogador alterado com sucesso!";
            } else {
                $mensagem = "❌ Erro ao alterar jogador: {$stmt->error}";
            }
        }
    
    // Alterar agente
    } elseif (isset($_POST['alterar_agente'])) {
        $id_agente = $_POST['id_agente'];
        $novo_nome = $_POST['nome_novo_agente'];
        $stmt = $conexao->prepare("UPDATE Agente SET nome_agente = ? WHERE id = ?");
        $stmt->bind_param("si", $novo_nome, $id_agente);
        $mensagem = $stmt->execute() ? "✅ Agente alterado com sucesso!" : "❌ Erro: {$stmt->error}";
    
    // Alterar time
    } elseif (isset($_POST['alterar_time'])) {
        $id = $_POST['id_time'];
        if (!empty($_POST['nome_novo_time'])) {
            $stmt_nome = $conexao->prepare("UPDATE Time SET nome_time = ? WHERE id = ?");
            $stmt_nome->bind_param("si", $_POST['nome_novo_time'], $id);
            $stmt_nome->execute();
        }
        if (!empty($_POST['pais_novo_time'])) {
            $stmt_pais = $conexao->prepare("UPDATE Time SET cod_pais = ? WHERE id = ?");
            $stmt_pais->bind_param("si", $_POST['pais_novo_time'], $id);
            $stmt_pais->execute();
        }
        if (!empty($_FILES['escudo_novo']['name'])) {
            $escudo = $_FILES['escudo_novo'];
            $nomeEscudo = basename($escudo['name']);
            $caminhoEscudo = '../img/escudos/' . $nomeEscudo;
            if (!is_dir('../img/escudos')) mkdir('../img/escudos', 0755, true);
            if (move_uploaded_file($escudo['tmp_name'], $caminhoEscudo)) {
                $stmt_escudo = $conexao->prepare("UPDATE Time SET escudo = ? WHERE id = ?");
                $stmt_escudo->bind_param("si", $nomeEscudo, $id);
                $stmt_escudo->execute();
            }
        }
        $mensagem = "✅ Time atualizado com sucesso!";
    
    // Alterar carreira
    } elseif (isset($_POST['alterar_carreira'])) {
        $id = $_POST['id_jogador'];
        $campo = $_POST['campo_carreira'];

        if ($campo === 'valor') {
            $novo_valor = floatval(str_replace(',', '.', $_POST['valor_novo']));
            $stmt = $conexao->prepare("CALL atualizar_valor_mercado_jogador(?, ?)");
            $stmt->bind_param("id", $id, $novo_valor);
            $mensagem = $stmt->execute() ? "✅ Valor de mercado atualizado!" : "❌ Erro: {$stmt->error}";
        } else {
            $valor = match($campo) {
                'jogos'  => intval($_POST['jogos_novo']), 'gols'   => intval($_POST['gols_novo']),
                'assist' => intval($_POST['assist_novo']),'ca'     => intval($_POST['ca_novo']),
                'cv'     => intval($_POST['cv_novo']),   default  => null
            };
            $campo_bd = ['jogos' => 'num_jogos_media', 'gols' => 'num_gols_media', 'assist' => 'num_assist_media',
                         'ca' => 'num_ca_media', 'cv' => 'num_cv_media'
            ][$campo] ?? null;
            if ($valor !== null && $campo_bd !== null) {
                $stmt = $conexao->prepare("UPDATE Carreira SET $campo_bd = ? WHERE id_jogador = ?");
                $stmt->bind_param("ii", $valor, $id);
                $mensagem = $stmt->execute() ? "✅ Carreira atualizada!" : "❌ Erro: {$stmt->error}";
            } else {
                $mensagem = "❌ Campo ou valor inválido para alterar a carreira.";
            }
        }
    }

    // Alterar transferencia
elseif (isset($_POST['alterar_transferencia'])) {
    $id_transferencia = $_POST['id_transferencia'];
    $campo_selecionado = $_POST['campo_transferencia'];
    $stmt = null;

    switch ($campo_selecionado) {
        case 'data_transf':
            if (!empty($_POST['nova_data'])) {
                $stmt = $conexao->prepare("UPDATE Transferencia SET data_transf = ? WHERE id = ?");
                $stmt->bind_param("si", $_POST['nova_data'], $id_transferencia);
            }
            break;
        case 'valor_transf':
            if (!empty($_POST['novo_valor'])) {
                $novo_valor = floatval(str_replace(',', '.', $_POST['novo_valor']));
                $stmt = $conexao->prepare("UPDATE Transferencia SET valor_transf = ? WHERE id = ?");
                $stmt->bind_param("di", $novo_valor, $id_transferencia);
            }
            break;
        case 'status_jog':
            if (!empty($_POST['novo_status'])) {
                $stmt = $conexao->prepare("UPDATE Transferencia SET status_jog = ? WHERE id = ?");
                $stmt->bind_param("si", $_POST['novo_status'], $id_transferencia);
            }
            break;
        case 'id_jogador':
            if (!empty($_POST['novo_jogador'])) {
                $stmt = $conexao->prepare("UPDATE Transferencia SET id_jogador = ? WHERE id = ?");
                $stmt->bind_param("ii", $_POST['novo_jogador'], $id_transferencia);
            }
            break;
        case 'id_time_antigo':
            if (!empty($_POST['novo_time_antigo'])) {
                $stmt = $conexao->prepare("UPDATE Transferencia SET id_time_antigo = ? WHERE id = ?");
                $stmt->bind_param("ii", $_POST['novo_time_antigo'], $id_transferencia);
            }
            break;
        case 'id_time_novo':
            if (!empty($_POST['novo_time_novo'])) {
                $stmt = $conexao->prepare("UPDATE Transferencia SET id_time_novo = ? WHERE id = ?");
                $stmt->bind_param("ii", $_POST['novo_time_novo'], $id_transferencia);
            }
            break;
    }

    if (isset($stmt)) {
        if ($stmt->execute()) {
            $mensagem = "✅ Transferência alterada com sucesso!";
        } else {
            $mensagem = "❌ Erro ao alterar transferência: " . $stmt->error;
        }
    } else {
        $mensagem = "❌ Nenhum valor fornecido para a alteração.";
    }
}

// Alterar rumor
elseif (isset($_POST['alterar_rumor'])) {
    $id_rumor = $_POST['id_rumor'];
    $campo_selecionado = $_POST['campo_rumor'];
    $stmt = null;

    switch ($campo_selecionado) {
        case 'id_jogador':
            if (!empty($_POST['novo_id_jogador'])) {
                $stmt = $conexao->prepare("UPDATE Rumor SET id_jogador = ? WHERE id = ?");
                $stmt->bind_param("ii", $_POST['novo_id_jogador'], $id_rumor);
            }
            break;
        case 'id_time_destino':
            if (!empty($_POST['novo_id_time_destino'])) {
                $stmt = $conexao->prepare("UPDATE Rumor SET id_time_destino = ? WHERE id = ?");
                $stmt->bind_param("ii", $_POST['novo_id_time_destino'], $id_rumor);
            }
            break;
        case 'data_rumor':
            if (!empty($_POST['nova_data_rumor'])) {
                $stmt = $conexao->prepare("UPDATE Rumor SET data_rumor = ? WHERE id = ?");
                $stmt->bind_param("si", $_POST['nova_data_rumor'], $id_rumor);
            }
            break;
        case 'grau_confianca':
            if (!empty($_POST['novo_grau_confianca'])) {
                $stmt = $conexao->prepare("UPDATE Rumor SET grau_confianca = ? WHERE id = ?");
                $stmt->bind_param("si", $_POST['novo_grau_confianca'], $id_rumor);
            }
            break;
    }

    if (isset($stmt)) {
        if ($stmt->execute()) {
            $mensagem = "✅ Rumor alterado com sucesso!";
        } else {
            $mensagem = "❌ Erro ao alterar rumor: " . $stmt->error;
        }
    } else {
        $mensagem = "❌ Nenhum valor fornecido para a alteração.";
    }
}
}



?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Alterar Dados</title>
</head>
<body>

<?php if (!empty($mensagem)): ?>
    <?php $corDeFundo = str_contains($mensagem, '✅') ? '#28a745' : '#dc3545'; ?>
    <div style="background-color: <?= $corDeFundo ?>; color: white; padding: 12px; border-radius: 6px; margin: 20px 0; text-align: center;">
        <?= htmlspecialchars($mensagem) ?>
    </div>
<?php endif; ?>

<label for="entidade-alterar">Escolha o tipo de dado para alterar:</label>
<select id="entidade-alterar" onchange="mostrarFormularioAlterar()">
    <option value="">-- Selecione --</option>
    <option value="jogador">Jogador</option>
    <option value="agente">Agente</option>
    <option value="time">Time</option>
    <option value="carreira">Carreira</option>
    <option value="transferencia">Transferência</option>
    <option value="rumor">Rumor</option>
</select>

<form method="POST" enctype="multipart/form-data" id="form-alterar-jogador" style="display:none;">
    <h3>Alterar Jogador</h3>
    <select name="id_jogador" required>
        <option value="">Selecione o Jogador</option>
        <?php foreach ($jogadores as $j): ?>
            <option value="<?= $j['id'] ?>"><?= $j['nome_jogador'] ?></option>
        <?php endforeach; ?>
    </select>
    <select name="campo_jogador" id="campo_jogador" onchange="mostrarCampo('jogador')" required>
        <option value="">-- Campo a alterar --</option>
        <option value="nome_jogador">Nome</option>
        <option value="data_nascimento">Data de Nascimento</option>
        <option value="altura">Altura</option>
        <option value="cod_pais">País</option>
        <option value="id_agente">Agente</option>
        <option value="cod_Posicao">Posição</option>
        <option value="id_time_atual">Time</option>
        <option value="imagem">Imagem</option>
    </select>
    <div id="campo_jogador_nome_jogador" style="display:none;"><input type="text" name="nome_novo" placeholder="Novo Nome"></div>
    <div id="campo_jogador_data_nascimento" style="display:none;"><input type="date" name="data_nasc_nova"></div>
    <div id="campo_jogador_altura" style="display:none;"><input type="text" name="altura_nova" placeholder="Nova Altura (ex: 1,85)"></div>
    <div id="campo_jogador_cod_pais" style="display:none;">
        <select name="pais_novo"><?php foreach ($paises as $p) echo "<option value='{$p['Cod_Pais']}'>{$p['Nome_Pais']}</option>"; ?></select>
    </div>
    <div id="campo_jogador_id_agente" style="display:none;">
        <select name="agente_novo"><?php foreach ($agentes as $a) echo "<option value='{$a['id']}'>{$a['nome_agente']}</option>"; ?></select>
    </div>
    <div id="campo_jogador_cod_Posicao" style="display:none;">
        <select name="posicao_nova"><?php foreach ($posicoes as $p) echo "<option value='{$p['Cod_Posicao']}'>{$p['Nome_Posicao']}</option>"; ?></select>
    </div>
    <div id="campo_jogador_id_time_atual" style="display:none;">
        <select name="time_novo"><?php foreach ($times as $t) echo "<option value='{$t['id']}'>{$t['nome_time']}</option>"; ?></select>
    </div>
    <div id="campo_jogador_imagem" style="display:none;"><input type="file" name="imagem_nova"></div>
    <button type="submit" name="alterar_jogador">Salvar Jogador</button>
</form>

<form method="POST" id="form-alterar-agente" style="display:none;">
    <h3>Alterar Agente</h3>
    <select name="id_agente" required>
        <option value="">Selecione o Agente</option>
        <?php foreach ($agentes as $a): ?>
            <option value="<?= $a['id'] ?>"><?= $a['nome_agente'] ?></option>
        <?php endforeach; ?>
    </select>
    <input type="text" name="nome_novo_agente" placeholder="Novo Nome do Agente" required>
    <button type="submit" name="alterar_agente">Salvar Agente</button>
</form>

<form method="POST" enctype="multipart/form-data" id="form-alterar-time" style="display:none;">
    <h3>Alterar Time</h3>
    <select name="id_time" required>
        <option value="">Selecione o Time</option>
        <?php foreach ($times as $t): ?>
            <option value="<?= $t['id'] ?>"><?= $t['nome_time'] ?></option>
        <?php endforeach; ?>
    </select>
    <select name="campo_time" id="campo_time" onchange="mostrarCampo('time')" required>
        <option value="">-- Campo a alterar --</option>
        <option value="nome">Nome</option>
        <option value="pais">País</option>
        <option value="escudo">Escudo</option>
    </select>
    <div id="campo_time_nome" style="display:none;"><input type="text" name="nome_novo_time" placeholder="Novo Nome do Time"></div>
    <div id="campo_time_pais" style="display:none;">
        <select name="pais_novo_time"><?php foreach ($paises as $p) echo "<option value='{$p['Cod_Pais']}'>{$p['Nome_Pais']}</option>"; ?></select>
    </div>
    <div id="campo_time_escudo" style="display:none;"><input type="file" name="escudo_novo" accept="image/*"></div>
    <button type="submit" name="alterar_time">Salvar Time</button>
</form>

<form method="POST" id="form-alterar-carreira" style="display:none;">
    <h3>Alterar Carreira</h3>
    <select name="id_jogador" required>
        <option value="">Selecione o Jogador</option>
        <?php foreach ($jogadores as $j): ?>
            <option value="<?= $j['id'] ?>"><?= $j['nome_jogador'] ?></option>
        <?php endforeach; ?>
    </select>
    <select name="campo_carreira" id="campo_carreira" onchange="mostrarCampo('carreira')" required>
        <option value="">-- Campo a alterar --</option>
        <option value="jogos">Jogos</option>
        <option value="gols">Gols</option>
        <option value="assist">Assistências</option>
        <option value="ca">Cartões Amarelos</option>
        <option value="cv">Cartões Vermelhos</option>
        <option value="valor">Valor de Mercado</option>
    </select>
    <div id="campo_carreira_jogos" style="display:none;"><input type="number" name="jogos_novo" placeholder="Novo nº de Jogos"></div>
    <div id="campo_carreira_gols" style="display:none;"><input type="number" name="gols_novo" placeholder="Novo nº de Gols"></div>
    <div id="campo_carreira_assist" style="display:none;"><input type="number" name="assist_novo" placeholder="Novo nº de Assistências"></div>
    <div id="campo_carreira_ca" style="display:none;"><input type="number" name="ca_novo" placeholder="Novo nº de Cartões Amarelos"></div>
    <div id="campo_carreira_cv" style="display:none;"><input type="number" name="cv_novo" placeholder="Novo nº de Cartões Vermelhos"></div>
    <div id="campo_carreira_valor" style="display:none;"><input type="text" name="valor_novo" placeholder="Novo Valor de Mercado (ex: 2500000.00)"></div>
    <button type="submit" name="alterar_carreira">Salvar Carreira</button>
</form>

<!-- Formulário: Alterar Transferência -->
<form method="POST" id="form-alterar-transferencia" style="display:none;">
    <h3>Alterar Transferência</h3>

    <select name="id_transferencia" required>
        <option value="">Selecione a Transferência (ID)</option>
        <?php foreach ($transferencias as $t): ?>
            <option value="<?= $t['id'] ?>">Transferência #<?= $t['id'] ?></option>
        <?php endforeach; ?>
    </select>

    <select name="campo_transferencia" id="campo_transferencia" onchange="mostrarCampo('transferencia')" required>
        <option value="">-- Campo a alterar --</option>
        <option value="data_transf">Data</option>
        <option value="valor_transf">Valor</option>
        <option value="status_jog">Status</option>
        <option value="id_jogador">Jogador</option>
        <option value="id_time_antigo">Time Antigo</option>
        <option value="id_time_novo">Time Novo</option>
    </select>

    <div id="campo_transferencia_data_transf" style="display:none;">
        <input type="date" name="nova_data">
    </div>
    <div id="campo_transferencia_valor_transf" style="display:none;">
        <input type="text" name="novo_valor" placeholder="Novo Valor (ex: 10000000.00)">
    </div>
    <div id="campo_transferencia_status_jog" style="display:none;">
        <select name="novo_status">
            <option value="">Novo Status</option>
            <option value="Contratado">Contratado</option>
            <option value="Emprestimo">Empréstimo</option>
        </select>
    </div>
    <div id="campo_transferencia_id_jogador" style="display:none;">
        <select name="novo_jogador">
            <option value="">Novo Jogador</option>
            <?php foreach ($jogadores as $j): ?>
                <option value="<?= $j['id'] ?>"><?= $j['nome_jogador'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div id="campo_transferencia_id_time_antigo" style="display:none;">
        <select name="novo_time_antigo">
            <option value="">Novo Time Antigo</option>
            <?php foreach ($times as $t): ?>
                <option value="<?= $t['id'] ?>"><?= $t['nome_time'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div id="campo_transferencia_id_time_novo" style="display:none;">
        <select name="novo_time_novo">
            <option value="">Novo Time Novo</option>
            <?php foreach ($times as $t): ?>
                <option value="<?= $t['id'] ?>"><?= $t['nome_time'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <button type="submit" name="alterar_transferencia">Salvar Transferência</button>
</form>

<form method="POST" id="form-alterar-rumor" style="display:none;">
    <h3>Alterar Rumor</h3>

    <select name="id_rumor" required>
        <option value="">Selecione o Rumor (ID)</option>
        <?php foreach ($rumores as $r): ?>
            <option value="<?= $r['id'] ?>">Rumor #<?= $r['id'] ?></option>
        <?php endforeach; ?>
    </select>

    <select name="campo_rumor" id="campo_rumor" onchange="mostrarCampo('rumor')" required>
        <option value="">-- Campo a alterar --</option>
        <option value="id_jogador">Jogador</option>
        <option value="id_time_destino">Time de Destino</option>
        <option value="data_rumor">Data do Rumor</option>
        <option value="grau_confianca">Grau de Confiança</option>
    </select>

    <div id="campo_rumor_id_jogador" style="display:none;">
        <select name="novo_id_jogador">
            <option value="">Novo Jogador</option>
            <?php foreach ($jogadores as $j): ?>
                <option value="<?= $j['id'] ?>"><?= $j['nome_jogador'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div id="campo_rumor_id_time_destino" style="display:none;">
        <select name="novo_id_time_destino">
            <option value="">Novo Time de Destino</option>
            <?php foreach ($times as $t): ?>
                <option value="<?= $t['id'] ?>"><?= $t['nome_time'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div id="campo_rumor_data_rumor" style="display:none;">
        <input type="date" name="nova_data_rumor">
    </div>
    <div id="campo_rumor_grau_confianca" style="display:none;">
        <select name="novo_grau_confianca">
            <option value="">Selecione o novo grau</option>
            <option value="Alta">Alta</option>
            <option value="Média">Média</option>
            <option value="Baixa">Baixa</option>
        </select>
    </div>

    <button type="submit" name="alterar_rumor">Salvar Rumor</button>
</form>

<script>
// Função genérica para mostrar o formulário principal
function mostrarFormularioAlterar() {
    const entidade = document.getElementById('entidade-alterar').value;
    const formularios = ['jogador', 'agente', 'time', 'carreira', 'transferencia', 'rumor'];
    formularios.forEach(id => {
        const form = document.getElementById('form-alterar-' + id);
        if (form) {
            form.style.display = (id === entidade ? 'flex' : 'none');
        }
    });
}


// Função genérica para mostrar campos específicos dentro de um formulário
function mostrarCampo(prefixo) {
    const select = document.getElementById('campo_' + prefixo);
    if (!select) return; // Guarda de segurança
    
    const campoSelecionado = select.value;
    
    // Esconde todos os campos de input daquele formulário
    const todosOsCampos = document.querySelectorAll(`[id^="campo_${prefixo}_"]`);
    todosOsCampos.forEach(c => c.style.display = 'none');

    // Mostra apenas o campo de input correspondente à seleção
    if (campoSelecionado) {
        const campoParaMostrar = document.getElementById(`campo_${prefixo}_${campoSelecionado}`);
        if (campoParaMostrar) {
            campoParaMostrar.style.display = 'block';
        }
    }
}
</script>
</body>
</html>