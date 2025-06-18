<?php

require_once("../conexao.php");

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

$paises = $conexao->query("SELECT Cod_Pais, Nome_Pais FROM Pais ORDER BY Nome_Pais")->fetch_all(MYSQLI_ASSOC);
$agentes = $conexao->query("SELECT id, nome_agente FROM Agente ORDER BY nome_agente")->fetch_all(MYSQLI_ASSOC);
$posicoes = $conexao->query("SELECT Cod_Posicao, Nome_Posicao FROM Posicao ORDER BY Nome_Posicao")->fetch_all(MYSQLI_ASSOC);
$times = $conexao->query("SELECT id, nome_time FROM Time ORDER BY nome_time")->fetch_all(MYSQLI_ASSOC);
$jogadores = $conexao->query("SELECT id, nome_jogador FROM Jogador ORDER BY nome_jogador")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['adicionar'])) {
        $nome = $_POST['nome'];
        $data_nasc = $_POST['data_nasc'];
        $altura = floatval(str_replace(',', '.', $_POST['altura']));
        $pais = $_POST['pais'];
        $agente = $_POST['agente'];
        $posicao = $_POST['posicao'];
        $time = $_POST['time'];
        $imagem = $_FILES['imagem'];

        $nomeImagem = basename($imagem['name']);
        $caminhoDestino = '../img/' . $nomeImagem;
        if (!is_dir('../img')) mkdir('../img', 0755, true);

        if (move_uploaded_file($imagem['tmp_name'], $caminhoDestino)) {
            $stmt = $conexao->prepare("INSERT INTO Jogador (nome_jogador, data_nascimento, altura, cod_pais, id_agente,  cod_Posicao, id_time_atual, imagem) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdsisis", $nome, $data_nasc, $altura, $pais, $agente, $posicao, $time, $nomeImagem);
            $executou = $stmt->execute();
            $_SESSION['mensagem'] = $executou ? "✅ Jogador adicionado!" : "❌ Erro: {$stmt->error}";
            $_SESSION['tipo_mensagem'] = $executou ? "success" : "error";
        } else {
            $_SESSION['mensagem'] = "❌ Falha ao enviar imagem.";
            $_SESSION['tipo_mensagem'] = "error";
        }

        exit;
    }

    if (isset($_POST['adicionar_agente'])) {
        $nome = $_POST['nome_agente'];

        try {
            // Tenta executar a inserção
            $stmt = $conexao->prepare("INSERT INTO Agente (nome_agente) VALUES (?)");
            $stmt->bind_param("s", $nome);
            $stmt->execute();

            // Se o código chegou até aqui, significa que deu tudo certo
            $_SESSION['mensagem'] = "✅ Agente adicionado!";
            $_SESSION['tipo_mensagem'] = "success";

        } catch (mysqli_sql_exception $e) {
            // Se uma exceção (erro) do banco de dados foi capturada, o código pula para cá
            if ($e->getCode() === 1062) { // 1062 é o código exato para "entrada duplicada"
                $_SESSION['mensagem'] = "❌ Erro: O agente '{$nome}' já está cadastrado.";
            } else {
                // Para qualquer outro tipo de erro no banco de dados
                $_SESSION['mensagem'] = "❌ Erro no banco de dados: " . $e->getMessage();
            }
            $_SESSION['tipo_mensagem'] = "error";
        }
        
        // O redirecionamento acontece após o try...catch, garantindo que a mensagem seja definida
        header('Location: ' . $_SERVER['PHP_SELF'] . '?pagina=adicionar');
        exit;
    }
    if (isset($_POST['adicionar_time'])) {
        $nome = $_POST['nome_time'];
        $escudo = $_FILES['escudo'];
        $nomeEscudo = basename($escudo['name']);
        $pais = $_POST['pais_time'];
        $caminhoEscudo = '../img/escudos/' . $nomeEscudo;

        if (!is_dir('../img/escudos')) mkdir('../img/escudos', 0755, true);

        if (move_uploaded_file($escudo['tmp_name'], $caminhoEscudo)) {
            $stmt = $conexao->prepare("INSERT INTO Time (nome_time, escudo, cod_pais) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nome, $nomeEscudo, $pais);
            $executou = $stmt->execute();
            $_SESSION['mensagem'] = $executou ? "✅ Time adicionado com sucesso!" : "❌ Erro: {$stmt->error}";
            $_SESSION['tipo_mensagem'] = $executou ? "success" : "error";
        } else {
            $_SESSION['mensagem'] = "❌ Falha ao enviar o escudo do time.";
            $_SESSION['tipo_mensagem'] = "error";
        }

        exit;
    }

    if (isset($_POST['adicionar_carreira'])) {
        $id_jogador = $_POST['id_jogador'];
        $jogos = floatval(str_replace(',', '.', $_POST['num_jogos_media']));
        $gols = floatval(str_replace(',', '.', $_POST['num_gols_media']));
        $assist = floatval(str_replace(',', '.', $_POST['num_assist_media']));
        $ca = floatval(str_replace(',', '.', $_POST['num_ca_media']));
        $cv = floatval(str_replace(',', '.', $_POST['num_cv_media']));
        $valor = floatval(str_replace(',', '.', $_POST['valor_mercado']));

        $stmt = $conexao->prepare("INSERT INTO Carreira (id_jogador, num_jogos_media, num_gols_media, num_assist_media, num_ca_media, num_cv_media, valor_mercado) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("idddddd", $id_jogador, $jogos, $gols, $assist, $ca, $cv, $valor);
        $executou = $stmt->execute();
        $_SESSION['mensagem'] = $executou ? "✅ Carreira adicionada com sucesso!" : "❌ Erro: {$stmt->error}";
        $_SESSION['tipo_mensagem'] = $executou ? "success" : "error";
        exit;
    }

    if (isset($_POST['adicionar_transferencia'])) {
        $data = $_POST['data_transf'];
        $valor = floatval(str_replace(',', '.', $_POST['valor_transf']));
        $status = $_POST['status_jog'];
        $id_jogador = $_POST['id_jogador'];
        $time_novo = $_POST['id_time_novo'];
    
        $stmt = $conexao->prepare("CALL RegistrarNovaTransferencia(?, ?, ?, ?, ?)");
        $stmt->bind_param("iidss", $id_jogador, $time_novo, $valor, $data, $status);
    
        $executou = $stmt->execute();
        $_SESSION['mensagem'] = $executou ? "✅ Transferência registrada com sucesso!" : "❌ Erro: {$stmt->error}";
        $_SESSION['tipo_mensagem'] = $executou ? "success" : "error";
        exit;
    }

    if (isset($_POST['adicionar_rumor'])) {
        $id_jogador = $_POST['id_jogador'];
        $id_time_destino = $_POST['id_time_destino'];
        $data_rumor = $_POST['data_rumor'];
        $grau_confianca = $_POST['grau_confianca'];

        $stmt = $conexao->prepare("INSERT INTO Rumor (id_jogador, id_time_destino, data_rumor, grau_confianca) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $id_jogador, $id_time_destino, $data_rumor, $grau_confianca);
        $executou = $stmt->execute();
        $_SESSION['mensagem'] = $executou ? "✅ Rumor registrado com sucesso!" : "❌ Erro: {$stmt->error}";
        $_SESSION['tipo_mensagem'] = $executou ? "success" : "error";
        exit;
    }
}
?>

<?php if (!empty($_SESSION['mensagem'])): ?>

    <div style="background-color: <?= $_SESSION['tipo_mensagem'] === 'success' ? '#28a745' : '#dc3545' ?>;
                color: white;
                padding: 12px;
                border-radius: 6px;
                margin-top: 20px;
                text-align: center;">
        <?= $_SESSION['mensagem'] ?>
    </div>
    
    <?php unset($_SESSION['mensagem'], $_SESSION['tipo_mensagem']); ?>
<?php endif; ?>

<label for="entidade">Escolha o tipo de dado:</label>

<select id="entidade" onchange="mostrarFormulario()">
    <option value="">-- Selecione --</option>
    <option value="jogador">Jogador</option>
    <option value="agente">Agente</option>
    <option value="time">Time</option>
    <option value="carreira">Carreira</option>
    <option value="transferencia">Transferência</option>
    <option value="rumor">Rumor</option>
</select>

<form method="POST" enctype="multipart/form-data" id="form-jogador" style="display:none;">

    <h3>Adicionar Jogador</h3>

    <input type="text" name="nome" placeholder="Nome" required>
    <input type="date" name="data_nasc" required>
    <input type="text" name="altura" placeholder="Altura" required>

    <select name="pais" required>
        <option value="">Selecione País</option>
        <?php foreach ($paises as $p): ?>
            <option value="<?= $p['Cod_Pais'] ?>"><?= $p['Nome_Pais'] ?></option>
        <?php endforeach; ?>
</select>

    <select name="agente" required>
        <option value="">Selecione Agente</option>
        <?php foreach ($agentes as $a): ?>
            <option value="<?= $a['id'] ?>"><?= $a['nome_agente'] ?></option>
        <?php endforeach; ?>
</select>

    <select name="posicao" required>
    <option value="">Selecione a Posição</option>
    <?php foreach ($posicoes as $p): ?>
        <option value="<?= $p['Cod_Posicao'] ?>"><?= $p['Nome_Posicao'] ?></option>
    <?php endforeach; ?>
</select>


    <select name="time" required>
        <option value="">Selecione Time</option>
        <?php foreach ($times as $t): ?>
            <option value="<?= $t['id'] ?>"><?= $t['nome_time'] ?></option>
        <?php endforeach; ?>
    </select>

    <input type="file" name="imagem" required>

    <button type="submit" name="adicionar">Salvar</button>
</form>

<form method="POST" id="form-agente" style="display:none;">
    <h3>Adicionar Agente</h3>
    <input type="text" name="nome_agente" placeholder="Nome do agente" required>
    <button type="submit" name="adicionar_agente">Salvar</button>
</form>

<form method="POST" enctype="multipart/form-data" id="form-time" style="display:none;">
    <h3>Adicionar Time</h3>
    <input type="text" name="nome_time" placeholder="Nome do time" required>

    <select name="pais_time" required>
        <option value="">Selecione o País do Time</option>
        <?php foreach ($paises as $p): ?>
            <option value="<?= $p['Cod_Pais'] ?>"><?= $p['Nome_Pais'] ?></option>
        <?php endforeach; ?>
    </select>

    <input type="file" name="escudo" accept="image/*" required>
    <button type="submit" name="adicionar_time">Salvar</button>
</form>

<form method="POST" id="form-carreira" style="display:none;">

    <h3>Adicionar Carreira</h3>

    <select name="id_jogador" required>
        <option value="">Selecione o Jogador</option>
        <?php foreach ($jogadores as $j): ?>
            <option value="<?= $j['id'] ?>"><?= $j['nome_jogador'] ?></option>
        <?php endforeach; ?>
    </select>

    <input type="number" step="any" name="num_jogos_media" placeholder="Média de Jogos (ex: 40.4)" required>
    <input type="number" step="any" name="num_gols_media" placeholder="Média de Gols (ex: 5.2)" required>
    <input type="number" step="any" name="num_assist_media" placeholder="Média de Assistências (ex: 4.2)" required>
    <input type="number" step="any" name="num_ca_media" placeholder="Média de Cartões Amarelos (ex: 2.2)" required>
    <input type="number" step="any" name="num_cv_media" placeholder="Média de Cartões Vermelhos (ex: 0.0)" required>
    
    <input type="number" step="any" name="valor_mercado" placeholder="Valor de Mercado (ex: 140000000.00)" required>

    <button type="submit" name="adicionar_carreira">Salvar</button>

</form>

<form method="POST" id="form-transferencia" style="display:none;">

<h3>Registrar Transferência</h3>

<select name="id_jogador" required>
    <option value="">Selecione o Jogador</option>
    <?php foreach ($jogadores as $j): ?>
        <option value="<?= $j['id'] ?>"><?= $j['nome_jogador'] ?></option>
    <?php endforeach; ?>
</select>



<select name="id_time_novo" required>
    <option value="">Time Novo</option>
    <?php foreach ($times as $t): ?>
        <option value="<?= $t['id'] ?>"><?= $t['nome_time'] ?></option>
    <?php endforeach; ?>
</select>

<input type="date" name="data_transf" required>
<input type="text" name="valor_transf" placeholder="Valor da Transferência (ex: 5000000.00)" required>

<select name="status_jog" required>
    <option value="">Status</option>
    <option value="Contratado">Contratado</option>
    <option value="Emprestimo">Empréstimo</option>
</select>

<button type="submit" name="adicionar_transferencia">Salvar</button>

</form>

<form method="POST" id="form-rumor" style="display:none;">

    <h3>Registrar Rumor</h3>

    <select name="id_jogador" required>
        <option value="">Selecione o Jogador</option>
        <?php foreach ($jogadores as $j): ?>
            <option value="<?= $j['id'] ?>"><?= $j['nome_jogador'] ?></option>
        <?php endforeach; ?>
    </select>

    <select name="id_time_destino" required>
        <option value="">Time de Destino</option>
        <?php foreach ($times as $t): ?>
            <option value="<?= $t['id'] ?>"><?= $t['nome_time'] ?></option>
        <?php endforeach; ?>
    </select>

    <input type="date" name="data_rumor" required>
    
    <select name="grau_confianca" required>
        <option value="">Grau de Confiança</option>
        <option value="Alta">Alta</option>
        <option value="Média">Média</option>
        <option value="Baixa">Baixa</option>
        
    </select>

    <button type="submit" name="adicionar_rumor">Salvar</button>

</form>

<script>
function mostrarFormulario() {
    const entidade = document.getElementById('entidade').value;
    const formularios = ['jogador', 'agente', 'time', 'carreira', 'transferencia', 'rumor'];

    formularios.forEach(id => {
        const form = document.getElementById('form-' + id);
        if (form) form.style.display = (id === entidade ? 'flex' : 'none');
    });
}
</script>