<?php
session_start();

if(!isset($_SESSION['user'])){
    header("Location: main.php");
    exit;
}

include "db.php";


if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['trimite_notificare'])){

    $Cod_Produs = $_POST['Cod_Produs'];

    $check = mysqli_query($conn,"
        SELECT ID_Notificare
        FROM notificari
        WHERE Cod_Produs='$Cod_Produs'
        AND ID_Client IS NULL
        AND Status='Necitita'
        LIMIT 1
    ");

    if(mysqli_num_rows($check) > 0){

        echo "
        <script>
            alert('Există deja o notificare necitită pentru acest produs!');
            window.location.href='stocredus.php';
        </script>
        ";

        exit;
    }

    $insert = mysqli_query($conn,"
        INSERT INTO notificari
        (
            ID_Client,
            Cod_Produs,
            Mesaj,
            Status,
            Data_Notificare
        )
        VALUES
        (
            NULL,
            '$Cod_Produs',
            'Stoc redus pentru produsul $Cod_Produs. Se recomandă actualizarea stocului.',
            'Necitita',
            NOW()
        )
    ");

    if(!$insert){
        die(mysqli_error($conn));
    }

    echo "
    <script>
        alert('Notificarea a fost trimisă managerului!');
        window.location.href='stocredus.php';
    </script>
    ";

    exit;
}


$query = "
SELECT produse.Cod_Produs,
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
WHERE materiale.Stoc < 5
ORDER BY materiale.Stoc ASC
";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="ro">

<head>
<meta charset="UTF-8">
<title>Produse Stoc Redus</title>
<link rel="stylesheet" href="style.css">
</head>

<body class="admin-page">

<div class="admin-top">

    <div class="admin-brand">

        <div class="admin-logo-row">

            <img src="logo.png" class="admin-logo">

            <div>
                <h1>Marmurar de Ruschita</h1>
                <p>Eleganță naturală, sculptată în piatră</p>
            </div>

        </div>

    </div>

    <div class="admin-title">
        <h2>Produse Stoc Redus</h2>
    </div>

</div>


<div class="table-container">

<table>

<thead>
<tr>
    <th>Cod Produs</th>
    <th>Tip</th>
    <th>Material</th>
    <th>Denumire</th>
    <th>Culoare</th>
    <th>Preț</th>
    <th>Dimensiune</th>
    <th>Stoc</th>
    <th>Manoperă</th>
    <th>Acțiuni</th>
</tr>
</thead>

<tbody>

<?php while($row = mysqli_fetch_assoc($result)){ ?>

<tr class="low-stock">

    <td><?php echo $row['Cod_Produs']; ?></td>
    <td><?php echo $row['Tip_Produs']; ?></td>
    <td><?php echo $row['Material']; ?></td>
    <td><?php echo $row['Denumire']; ?></td>
    <td><?php echo $row['Culoare']; ?></td>
    <td><?php echo $row['Pret']; ?></td>
    <td><?php echo $row['Dimensiune']; ?></td>
    <td><?php echo $row['Stoc']; ?></td>
    <td><?php echo $row['Manopera']; ?></td>

    <td>

        <form method="POST">

            <input
                type="hidden"
                name="Cod_Produs"
                value="<?php echo $row['Cod_Produs']; ?>"
            >

            <button
                type="submit"
                name="trimite_notificare"
                class="notify-btn"
            >
                Trimite notificare
            </button>

        </form>

    </td>

</tr>

<?php } ?>

</tbody>

</table>

</div>


<a
href="<?php echo ($_SESSION['rol'] == 'Manager') ? 'manager.php' : 'vanzator.php'; ?>"
class="cancel-btn"
>
    Cancel
</a>

</body>
</html>