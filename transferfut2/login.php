<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $usuario = $_POST['usuario'] ?? '';
  $senha = $_POST['senha'] ?? '';

  if (!empty($usuario) && !empty($senha)) {
    // Aqui você pode adicionar validação real com banco de dados, se quiser
    header("Location: paineladm/painel.php"); // Redireciona para o painel novo
    exit;
  } else {
    $erro = "Preencha todos os campos.";
  }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>TransferFut - Login</title>
  <link rel="stylesheet" href="style.css" />
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
</head>
<body class="pagina-login">

  <!-- Botão voltar -->
  <a href="index.php" class="botao-voltar">&#8592; Voltar</a>

  <div class="container">
    <div class="login-box">
      <img src="img/logo.png" alt="Logo TransferFut" class="logo" />
      
      <form class="form" method="POST" action="login.php">
        <label for="usuario">USUÁRIO:</label>
        <input type="text" id="usuario" name="usuario" required />

        <label for="senha">SENHA:</label>
        <input type="password" id="senha" name="senha" required />

        <button type="submit">Entrar</button>

        <?php if (isset($erro)): ?>
          <p style="color: #ff3c1f; text-align: center; margin-top: 10px; font-weight: bold;">
            <?= $erro ?>
          </p>
        <?php endif; ?>

        <div class="link">
          <a href="#">Não possui uma conta? Crie aqui!</a>
        </div>
      </form>
    </div>

    <div class="image-side">
      <img src="img/jogadoreslogin.png" alt="Jogadores TransferFut" />
    </div>
  </div>
</body>
</html>
