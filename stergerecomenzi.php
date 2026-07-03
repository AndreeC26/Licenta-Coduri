<?php
session_start();

if(!isset($_SESSION['user'])){
    header("Location: main.php");
    exit;
}

include "db.php";


if(isset($_POST['delete']) && isset($_POST['Nr_Comanda'])){

    foreach($_POST['Nr_Comanda'] as $Nr_Comanda){
        $checkStatus = mysqli_query($conn,"
    SELECT Status
    FROM comenzi
    WHERE Nr_Comanda='$Nr_Comanda'
");

$statusData = mysqli_fetch_assoc($checkStatus);

if($statusData['Status'] == 'Terminata'){
    continue;
}

        $q = mysqli_query($conn,"
            SELECT Cod_Produs, Nr_Placi
            FROM comenzi
            WHERE Nr_Comanda='$Nr_Comanda'
        ");

        $cmd = mysqli_fetch_assoc($q);

        if($cmd){

            $cod = $cmd['Cod_Produs'];
            $plati = $cmd['Nr_Placi'];

            $q2 = mysqli_query($conn,"
                SELECT Cod_Material
                FROM produse
                WHERE Cod_Produs='$cod'
            ");

            $p = mysqli_fetch_assoc($q2);

            if($p){

                $codMat = $p['Cod_Material'];

                mysqli_query($conn,"
                    UPDATE materiale
                    SET Stoc = Stoc + $plati
                    WHERE Cod_Material='$codMat'
                ");

                mysqli_query($conn,"
    DELETE FROM facturi
    WHERE Nr_Comanda='$Nr_Comanda'
");
            }
        }

        mysqli_query($conn,"
            DELETE FROM comenzi
            WHERE Nr_Comanda='$Nr_Comanda'
        ");
    }

    header("Location: stergerecomenzi.php");
    exit;
}



$query = "
SELECT
Nr_Comanda,
Cod_Produs,
Nr_Bucati,
Nr_Placi,
Dimensiune,
Pret_Material,
Pret_Manopera,
Pret_Produs,
Data,
Status
FROM comenzi
ORDER BY Nr_Comanda DESC
";

$result = mysqli_query($conn,$query);
?>

<!DOCTYPE html>
<html lang="ro">

<head>
<meta charset="UTF-8">
<title>Ștergere Comenzi</title>
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
        <h2>Ștergere Comenzi</h2>
    </div>

</div>


<div class="form-container">

<form method="POST">

<div class="table-container delete-table">

<table class="delete-orders-table">

<thead>
<tr>
    <th>Select</th>
    <th>Nr Comandă</th>
    <th>Cod Produs</th>
    <th>Bucăți</th>
    <th>Plăci</th>
    <th>Dimensiune</th>
    <th>Preț Material</th>
    <th>Preț Manoperă</th>
    <th>Total</th>
    <th>Data</th>
    <th>Status</th>
</tr>
</thead>

<tbody>

<?php while($row = mysqli_fetch_assoc($result)){ ?>

<tr>

   <td>

<?php if($row['Status'] == 'Terminata'){ ?>

    <span class="finished-order">
        Finalizată
    </span>

<?php } else { ?>

    <input 
        type="checkbox" 
        name="Nr_Comanda[]" 
        value="<?php echo $row['Nr_Comanda']; ?>"
    >

<?php } ?>

</td>

    <td><?php echo $row['Nr_Comanda']; ?></td>
    <td><?php echo $row['Cod_Produs']; ?></td>
    <td><?php echo $row['Nr_Bucati']; ?></td>
    <td><?php echo $row['Nr_Placi']; ?></td>
    <td><?php echo $row['Dimensiune']; ?></td>
    <td><?php echo $row['Pret_Material']; ?> lei</td>
    <td><?php echo $row['Pret_Manopera']; ?> lei</td>
    <td><?php echo $row['Pret_Produs']; ?> lei</td>
    <td><?php echo $row['Data']; ?></td>
    <td><?php echo $row['Status']; ?></td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

<div class="actions-container delete-actions">

    <button
        type="submit"
        name="delete"
        class="delete-btn"
        onclick="return confirm('Sigur vrei să ștergi comenzile selectate?');"
    >
        Șterge
    </button>

    <button
    type="button"
    class="cancel-btn"
    onclick="window.location.href='<?php echo ($_SESSION['rol'] == 'Manager') ? 'manager.php' : 'vanzator.php'; ?>'"
>
    Cancel
</button>

</div>

</form>

</div>

</body>
</html>