<?php
session_start();

if(!isset($_SESSION['user'])){
    header("Location: main.php");
    exit;
}

include "db.php";


if(isset($_POST['save'])){

    $Nume        = trim($_POST['Nume']);
    $Prenume     = trim($_POST['Prenume']);
    $Telefon     = trim($_POST['Telefon']);
    $Adresa_Mail = trim($_POST['Adresa_Mail']);
    $Adresa      = trim($_POST['Adresa']);



    if(
        empty($Nume) ||
        empty($Prenume) ||
        empty($Telefon) ||
        empty($Adresa_Mail) ||
        empty($Adresa)
    ){

        die("Completează toate câmpurile!");
    }



    $check = mysqli_query($conn,"
        SELECT *
        FROM clienti
        WHERE Telefon='$Telefon'
        LIMIT 1
    ");

    if(mysqli_num_rows($check) > 0){

        echo "
        <script>

            alert('Există deja un client cu acest număr de telefon!');

            window.location.href='adaugaclient.php';

        </script>
        ";

        exit;
    }



    $insert = mysqli_query($conn,"
        INSERT INTO clienti
        (
            Nume,
            Prenume,
            Telefon,
            Adresa_Mail,
            Adresa
        )

        VALUES
        (
            '$Nume',
            '$Prenume',
            '$Telefon',
            '$Adresa_Mail',
            '$Adresa'
        )
    ");


    if(!$insert){

        die(mysqli_error($conn));
    }



    echo "
    <script>

        alert('Client adăugat cu succes!');

        window.location.href='" . (($_SESSION['rol'] == 'Manager') ? 'manager.php' : 'vanzator.php') . "';

    </script>
    ";

    exit;
}
?>

<!DOCTYPE html>
<html lang="ro">

<head>

<meta charset="UTF-8">

<title>Adaugă Client</title>

<link rel="stylesheet" href="style.css">

</head>

<body class="admin-page">



<div class="admin-top">

    <div class="admin-logo-row">

        <img src="logo.png" class="admin-logo">

        <div class="admin-brand">

            <h1>Marmurar de Ruschita</h1>

            <p>Eleganță naturală, sculptată în piatră</p>

        </div>

    </div>

    <div class="admin-title">

        <h2>Adaugă Client</h2>

    </div>

</div>





<div class="form-container">

<form method="POST">

<div class="box">

    <h3>Date Client</h3>

    <input
        type="text"
        name="Nume"
        placeholder="Nume"
        required
    >

    <input
        type="text"
        name="Prenume"
        placeholder="Prenume"
        required
    >

    <input
        type="text"
        name="Telefon"
        placeholder="Telefon"
        required
    >

    <input
        type="email"
        name="Adresa_Mail"
        placeholder="Adresă e-mail"
        required
    >

    <input
        type="text"
        name="Adresa"
        placeholder="Adresă"
        required
    >

</div>





<div class="actions-container">

    <button
        type="submit"
        name="save"
        class="btn btn-primary"
    >

        Save

    </button>

   <button
    type="button"
    class="btn btn-secondary"
    onclick="window.location.href='<?php echo ($_SESSION['rol'] == 'Manager') ? 'manager.php' : 'vanzator.php'; ?>'"
>

    Cancel

</button>

</div>

</form>

</div>

</body>
</html>