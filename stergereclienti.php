<?php
session_start();

if(!isset($_SESSION['user'])){
    header("Location: main.php");
    exit;
}

include "db.php";


if(isset($_POST['delete']) && isset($_POST['ID_Client'])){

    $clienti = $_POST['ID_Client'];

    foreach($clienti as $ID_Client){

        $check = mysqli_query($conn,"
            SELECT *
            FROM comenzi
            WHERE ID_Client='$ID_Client'
        ");

        if(mysqli_num_rows($check) > 0){

            echo "
            <script>
                alert('Clientul are comenzi asociate și nu poate fi șters!');
                window.location.href='deleteclienti.php';
            </script>
            ";

            exit;
        }

        mysqli_query($conn,"
            DELETE FROM clienti
            WHERE ID_Client='$ID_Client'
        ");
    }

    header("Location: listaclienti.php");
    exit;
}



$query = "
SELECT *
FROM clienti
ORDER BY ID_Client DESC
";

$result = mysqli_query($conn,$query);

?>

<!DOCTYPE html>
<html lang="ro">

<head>

<meta charset="UTF-8">

<title>Ștergere clienți</title>

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

        <h2>Ștergere clienți</h2>

    </div>

</div>





<div class="form-container">

<form method="POST">



<div class="table-container full-width-table">

<table>

    <thead>

        <tr>

            <th>Select</th>

            <th>ID Client</th>

            <th>Nume</th>

            <th>Prenume</th>

            <th>Telefon</th>

            <th>Adresă</th>

        </tr>

    </thead>



    <tbody>

    <?php while($row = mysqli_fetch_assoc($result)){ ?>

        <tr>

            <td class="checkbox-col">

                <input
                    type="checkbox"
                    name="ID_Client[]"
                    value="<?php echo $row['ID_Client']; ?>"
                >

            </td>

            <td>
                <?php echo $row['ID_Client']; ?>
            </td>

            <td>
                <?php echo $row['Nume']; ?>
            </td>

            <td>
                <?php echo $row['Prenume']; ?>
            </td>

            <td>
                <?php echo $row['Telefon']; ?>
            </td>

            <td>
                <?php echo $row['Adresa']; ?>
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
        onclick="return confirm('Sigur vrei să ștergi clienții selectați?');"
    >

        Delete

    </button>

    <button
        type="button"
        class="btn btn-secondary"
        onclick="window.location.href='listaclienti.php'"
    >

        Cancel

    </button>

</div>

</form>

</div>

</body>
</html>