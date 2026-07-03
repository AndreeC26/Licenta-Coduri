<?php
include "db.php";

if(isset($_POST['save'])){

    $Cod_Produs = $_POST['Cod_Produs'];
    $Tip_Produs = $_POST['Tip_Produs'];
    $Cod_Material = $_POST['Cod_Material'];
    $Material = $_POST['Material'];
    $Denumire = $_POST['Denumire'];
    $Culoare = $_POST['Culoare'];
    $Pret = $_POST['Pret'];
    $Dimensiune = $_POST['Dimensiune'];
    $Stoc = $_POST['Stoc'];
    $Manopera = $_POST['Manopera'];

    $sqlMat = "INSERT INTO materiale
    (Cod_Material, Material, Denumire, Culoare, Pret, Dimensiune, Stoc, Manopera)
    VALUES
    ('$Cod_Material','$Material','$Denumire','$Culoare','$Pret','$Dimensiune','$Stoc','$Manopera')";
    mysqli_query($conn,$sqlMat);

    $sqlProd = "INSERT INTO produse
    (Cod_Produs, Tip_Produs, Cod_Material)
    VALUES
    ('$Cod_Produs','$Tip_Produs','$Cod_Material')";
    mysqli_query($conn,$sqlProd);

    header("Location: admin.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Adaugă produs nou</title>
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
        <h2>Adaugă produs nou</h2>
    </div>

</div>

<div class="form-container">

<form method="POST">

<table>

    <tr>
        <td>Cod Produs</td>
        <td><input type="text" name="Cod_Produs" required></td>
    </tr>

    <tr>
        <td>Tip Produs</td>
        <td><input type="text" name="Tip_Produs" required></td>
    </tr>

    <tr>
        <td>Cod Material</td>
        <td><input type="text" name="Cod_Material" required></td>
    </tr>

    <tr>
        <td>Material</td>
        <td><input type="text" name="Material" required></td>
    </tr>

    <tr>
        <td>Denumire</td>
        <td><input type="text" name="Denumire" required></td>
    </tr>

    <tr>
        <td>Culoare</td>
        <td><input type="text" name="Culoare" required></td>
    </tr>

    <tr>
        <td>Pret</td>
        <td><input type="text" name="Pret" required></td>
    </tr>

    <tr>
        <td>Dimensiune</td>
        <td><input type="text" name="Dimensiune" required></td>
    </tr>

    <tr>
        <td>Stoc</td>
        <td><input type="text" name="Stoc" required></td>
    </tr>

    <tr>
        <td>Manopera</td>
        <td><input type="text" name="Manopera" required></td>
    </tr>

</table>

<div class="actions-container">
    <button type="submit" name="save">Save</button>

    <a href="admin.php">
        <button type="button">Cancel</button>
    </a>
</div>

</form>

</div>

</body>
</html>