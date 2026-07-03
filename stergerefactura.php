<?php
session_start();

if(!isset($_SESSION['user'])){
    header("Location: main.php");
    exit;
}

include "db.php";


if(isset($_POST['delete']) && isset($_POST['Nr_Factura'])){

    $facturi = $_POST['Nr_Factura'];

    foreach($facturi as $Nr_Factura){

        mysqli_query($conn,"
            DELETE FROM facturi
            WHERE Nr_Factura='$Nr_Factura'
        ");
    }

    header("Location: facturiexistente.php");
    exit;
}



$query = "
SELECT
    Nr_Factura,
    Nr_Comanda,
    Data_Factura,
    Total_Factura,
    Metoda_Plata
FROM facturi
ORDER BY Nr_Factura DESC
";

$result = mysqli_query($conn,$query);

?>

<!DOCTYPE html>
<html lang="ro">

<head>

<meta charset="UTF-8">

<title>Ștergere facturi</title>

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

        <h2>Ștergere facturi</h2>

    </div>

</div>




<div class="form-container">

<form method="POST">



<div class="table-container full-width-table">

<table>

    <thead>

        <tr>

            <th>Select</th>

            <th>Nr Factură</th>

            <th>Nr Comandă</th>

            <th>Data Factură</th>

            <th>Total Factură</th>

            <th>Metodă Plată</th>

        </tr>

    </thead>



    <tbody>

    <?php while($row = mysqli_fetch_assoc($result)){ ?>

        <tr>

            <td class="checkbox-col">

                <input
                    type="checkbox"
                    name="Nr_Factura[]"
                    value="<?php echo $row['Nr_Factura']; ?>"
                >

            </td>

            <td>
                <?php echo $row['Nr_Factura']; ?>
            </td>

            <td>
                <?php echo $row['Nr_Comanda']; ?>
            </td>

            <td>
                <?php echo $row['Data_Factura']; ?>
            </td>

            <td>
                <?php echo $row['Total_Factura']; ?> lei
            </td>

            <td>
                <?php echo $row['Metoda_Plata']; ?>
            </td>

        </tr>

    <?php } ?>

    </tbody>

</table>

</div>





<div class="actions-container delete-actions">

    <button
        type="submit"
        name="delete"
        class="btn btn-danger"
        onclick="return confirm('Sigur vrei să ștergi facturile selectate?');"
    >

        Delete

    </button>

    <button
        type="button"
        class="btn btn-secondary"
        onclick="window.location.href='facturiexistente.php'"
    >

        Cancel

    </button>

</div>

</form>

</div>

</body>
</html>