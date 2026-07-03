<?php
include "db.php";

if(isset($_POST['delete']) && isset($_POST['Cod_Produs'])){
    $produse = $_POST['Cod_Produs'];

    foreach($produse as $Cod_Produs){
        mysqli_query($conn,"DELETE FROM produse WHERE Cod_Produs='$Cod_Produs'");
    }

    header("Location: admin.php");
    exit;
}

$query = "
SELECT 
produse.Cod_Produs,
produse.Tip_Produs,
produse.Cod_Material,
materiale.Material,
materiale.Denumire,
materiale.Culoare,
materiale.Pret,
materiale.Dimensiune,
materiale.Stoc,
materiale.Manopera
FROM produse
LEFT JOIN materiale
ON produse.Cod_Material = materiale.Cod_Material
";

$result = mysqli_query($conn,$query);
?>

<!DOCTYPE html>
<html lang="ro">
<head>
<meta charset="UTF-8">
<title>Șterge produse</title>
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
        <h2>Șterge produse</h2>
    </div>

</div>

<div class="form-container">

<form method="POST">

<div class="table-container delete-table">
<table>
    <thead>
        <tr>
            <th>Select</th>
            <th>Cod Produs</th>
            <th>Tip Produs</th>
            <th>Cod Material</th>
            <th>Material</th>
            <th>Denumire</th>
            <th>Culoare</th>
            <th>Preț</th>
            <th>Dimensiune</th>
            <th>Stoc</th>
            <th>Manoperă</th>
        </tr>
    </thead>

    <tbody>
        <?php while($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td>
                <input type="checkbox" name="Cod_Produs[]" value="<?php echo $row['Cod_Produs']; ?>">
            </td>
            <td><?php echo $row['Cod_Produs']; ?></td>
            <td><?php echo $row['Tip_Produs']; ?></td>
            <td><?php echo $row['Cod_Material']; ?></td>
            <td><?php echo $row['Material']; ?></td>
            <td><?php echo $row['Denumire']; ?></td>
            <td><?php echo $row['Culoare']; ?></td>
            <td><?php echo $row['Pret']; ?></td>
            <td><?php echo $row['Dimensiune']; ?></td>
            <td><?php echo $row['Stoc']; ?></td>
            <td><?php echo $row['Manopera']; ?></td>
        </tr>
        <?php } ?>
    </tbody>
</table>
</div>

<div class="actions-container delete-actions">

    <button type="submit" name="delete"
        onclick="return confirm('Ești sigur că vrei să ștergi produsele selectate?');">
        Delete
    </button>

    <a href="admin.php">
        <button type="button" class="cancel-btn">
            Cancel
        </button>
    </a>

</div>

</form>

</div>

</body>
</html>