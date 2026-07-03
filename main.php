<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include "db.php";

if(isset($_POST['login'])){

    $cod = trim($_POST['cod_utilizator']);
    $parola = trim($_POST['parola']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE Cod_Utilizator=?");
    $stmt->bind_param("s", $cod);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if($user && password_verify($parola, $user['Parola'])){

        $_SESSION['user'] = $user['Cod_Utilizator'];
        $_SESSION['rol']  = $user['Rol'];

        if($user['Rol'] == 'Admin'){
            header("Location: admin.php");
        }
        else if($user['Rol'] == 'Vanzator'){
            header("Location: vanzator.php");
        }
        else if($user['Rol'] == 'Manager'){
            header("Location: manager.php");
        }
        else{
            header("Location: main.php");
        }

        exit;

    } else {
        $error = "Cod utilizator sau parola gresita!";
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
<meta charset="UTF-8">
<title>Marmurar de Ruschita</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="main-page">

<div class="overlay">

    <div class="center-text">
        <h1>Marmurar de Ruschita</h1>
        <div class="motto">Eleganță naturală, sculptată în piatră</div>
    </div>

    <div class="login-box">
        <form method="POST">
            <h2>Login</h2>

            <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>

            <input type="text" name="cod_utilizator" placeholder="Cod utilizator" required>
            <input type="password" name="parola" placeholder="Parola" required>

            <button type="submit" name="login">Login</button>
        </form>
    </div>

</div>

</body>
</html>
