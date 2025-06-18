<?php
require_once("../conexao.php");

$entidadeExcluir = $_POST['entidade_excluir'] ?? '';
$mensagem = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ocultar transferência via Procedure
    if (isset($_POST['excluir_transferencia'])) {
        $id = intval($_POST['id_transferencia']);
        
        $stmt = $conexao->prepare("CALL ocultar_transferencia(?)");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $mensagem = "<p style='color:lime;'>✅ Transferência ocultada com sucesso.</p>";
        } else {
            $mensagem = "<p style='color:red;'>❌ Erro ao ocultar transferência: {$stmt->error}</p>";
        }
    }

    // Excluir rumor via Procedure
    if (isset($_POST['excluir_rumor'])) {
        $id = intval($_POST['id_rumor']);
        
        $stmt = $conexao->prepare("CALL excluir_rumor(?)");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $mensagem = "<p style='color:lime;'>✅ Rumor excluído com sucesso.</p>";
        } else {
            // A procedure já retorna um erro customizado se o rumor não for encontrado
            $mensagem = "<p style='color:red;'>❌ Erro ao excluir rumor: {$stmt->error}</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Excluir Dados</title>
    <style>
        form {
            display: flex;
            flex-direction: column;
            gap: 12px;
            width: 100%;
            max-width: 400px;
            margin: 20px auto;
        }

        select, button {
            padding: 10px;
            font-size: 1rem;
            border-radius: 6px;
            border: none;
        }

        button {
            background-color: #ff3c1f;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background-color: #e02d10;
        }

        h3 {
            color: #ff3c1f;
            text-align: center;
        }

        label {
            font-weight: bold;
        }

        p {
            text-align: center;
            font-size: 1rem;
        }
    </style>
</head>
<body>

<?= $mensagem ?>

<form method="POST">
    <label for="entidade_excluir">Escolha o tipo de dado para excluir:</label>
    <select name="entidade_excluir" id="entidade_excluir" onchange="this.form.submit()" required>
        <option value="">-- Selecione --</option>
        <option value="transferencia" <?= $entidadeExcluir == 'transferencia' ? 'selected' : '' ?>>Transferência</option>
        <option value="rumor" <?= $entidadeExcluir == 'rumor' ? 'selected' : '' ?>>Rumor</option>
    </select>
</form>

<?php if ($entidadeExcluir === 'transferencia'): ?>
    <form method="POST" onsubmit="return confirmarOcultacao()">
        <h3>Ocultar Transferência</h3>
        <input type="hidden" name="entidade_excluir" value="transferencia">
        <label for="id_transferencia">Selecione a Transferência (ID):</label>
        <select name="id_transferencia" required>
            <option value="">-- Escolha --</option>
            <?php
            // A consulta para popular o dropdown continua a mesma
            $transferencias = $conexao->query("SELECT id FROM Transferencia WHERE visivel = TRUE ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
            foreach ($transferencias as $t) {
                echo "<option value='{$t['id']}'>Transferência #{$t['id']}</option>";
            }
            ?>
        </select>
        <button type="submit" name="excluir_transferencia">Ocultar</button>
    </form>
<?php endif; ?>

<?php if ($entidadeExcluir === 'rumor'): ?>
    <form method="POST" onsubmit="return confirmarExclusaoRumor()">
        <h3>Excluir Rumor</h3>
        <input type="hidden" name="entidade_excluir" value="rumor">
        <label for="id_rumor">Selecione o Rumor (ID):</label>
        <select name="id_rumor" required>
            <option value="">-- Escolha --</option>
            <?php
            // A consulta para popular o dropdown continua a mesma
            $rumores = $conexao->query("SELECT id FROM Rumor ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
            foreach ($rumores as $r) {
                echo "<option value='{$r['id']}'>Rumor #{$r['id']}</option>";
            }
            ?>
        </select>
        <button type="submit" name="excluir_rumor">Excluir</button>
    </form>
<?php endif; ?>

<script>
function confirmarOcultacao() {
    return confirm("Tem certeza que deseja ocultar esta transferência?\nEla será removida da exibição pública, mas continuará salva no banco de dados.");
}

function confirmarExclusaoRumor() {
    return confirm("Deseja realmente excluir este rumor?\nEssa ação é irreversível.");
}
</script>

</body>
</html>