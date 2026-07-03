<?php
session_start();

if(!isset($_SESSION['user'])){
    header("Location: main.php");
    exit;
}

include "db.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';


if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aprovizionare'])){

    $ID_Notificare = $_POST['ID_Notificare'];
    $Cod_Produs = $_POST['Cod_Produs'];
    $Cantitate = 10;

    $getMaterial = mysqli_query($conn,"
        SELECT Cod_Material
        FROM produse
        WHERE Cod_Produs='$Cod_Produs'
    ");

    $material = mysqli_fetch_assoc($getMaterial);

    if($material){

        mysqli_query($conn,"
            UPDATE materiale
            SET Stoc = Stoc + $Cantitate
            WHERE Cod_Material='{$material['Cod_Material']}'
        ");

        mysqli_query($conn,"
            UPDATE notificari
            SET Status='Stoc Actualizat'
            WHERE ID_Notificare='$ID_Notificare'
        ");

        $getClient = mysqli_query($conn,"
            SELECT 
                n.Cod_Produs,
                n.ID_Client,
                c.Nume,
                c.Prenume,
                c.Adresa_Mail
            FROM notificari n
            LEFT JOIN clienti c
            ON n.ID_Client = c.ID_Client
            WHERE n.ID_Notificare='$ID_Notificare'
        ");

        $client = mysqli_fetch_assoc($getClient);

        if($client && !empty($client['Adresa_Mail'])){

            $mail = new PHPMailer(true);

            try{

                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;

                $mail->Username = 'marmurarderuschita@gmail.com';
                $mail->Password = 'key';

                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->CharSet = 'UTF-8';

                $mail->setFrom(
                    'marmurarderuschita@gmail.com',
                    'Marmurar de Ruschita'
                );

                $mail->addAddress($client['Adresa_Mail']);

                $mail->isHTML(true);

                $mail->Subject = 'Produs disponibil din nou - Marmurar de Ruschita';

                $mail->Body = "
                <div style='font-family:Arial,sans-serif; color:#333;'>

                    <h2 style='color:#0f47af;'>
                        Marmurar de Ruschita
                    </h2>

                    <p>
                        Bună ziua,
                        <b>{$client['Nume']} {$client['Prenume']}</b>,
                    </p>

                    <p>
                        Vă informăm că produsul
                        <b>{$client['Cod_Produs']}</b>
                        este din nou disponibil în stoc.
                    </p>

                    <p>
                        Puteți reveni pentru finalizarea comenzii.
                    </p>

                    <br>

                    <p>
                        Cu respect,<br>
                        <b>Marmurar de Ruschita</b><br>
                        <i>Eleganță naturală, sculptată în piatră</i>
                    </p>

                </div>
                ";

                $mail->AltBody =
                "Buna ziua, {$client['Nume']} {$client['Prenume']}. Produsul {$client['Cod_Produs']} este din nou disponibil in stoc. Puteti reveni pentru finalizarea comenzii. Marmurar de Ruschita.";

                $mail->send();

            }catch(Exception $e){

                $eroareMail = addslashes($mail->ErrorInfo);

                echo "
                <script>
                    alert('Stocul a fost actualizat, dar emailul nu a putut fi trimis: $eroareMail');
                    window.location.href='mesajeprimite.php';
                </script>
                ";

                exit;
            }
        }

        echo "
        <script>
            alert('Stocul a fost actualizat cu 10 placi! Clientul a fost notificat daca are email salvat.');
            window.location.href='mesajeprimite.php';
        </script>
        ";

        exit;

    }else{

        echo "
        <script>
            alert('Produsul nu a fost gasit!');
            window.location.href='mesajeprimite.php';
        </script>
        ";

        exit;
    }
}


$query = "
SELECT 
    n.ID_Notificare,
    n.ID_Client,
    n.Cod_Produs,
    n.Mesaj,
    n.Status,
    n.Data_Notificare,
    c.Nume,
    c.Prenume
FROM notificari n
LEFT JOIN clienti c
ON n.ID_Client = c.ID_Client
ORDER BY n.Data_Notificare DESC
";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="ro">

<head>
<meta charset="UTF-8">
<title>Mesaje Primite</title>
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
        <h2>Mesaje Primite</h2>
    </div>

</div>


<div class="search-container">

    <input
        type="text"
        id="search"
        placeholder="Caută mesaj..."
    >

    <div class="button-group">
        <button onclick="window.location.href='manager.php'">
            Cancel
        </button>
    </div>

</div>


<div class="table-container">

<table id="mesaje-table">

<thead>
<tr>
    <th>Tip</th>
    <th>Produs</th>
    <th>Client</th>
    <th>Data</th>
    <th>Status</th>
    <th>Acțiuni</th>
</tr>
</thead>

<tbody>

<?php while($row = mysqli_fetch_assoc($result)){ ?>

<?php
$tip = ($row['ID_Client']) ? "Stoc insuficient" : "Stoc redus";
$client = ($row['ID_Client']) ? $row['Nume']." ".$row['Prenume'] : "-";
?>

<tr title="<?php echo $row['Mesaj']; ?>">

    <td><?php echo $tip; ?></td>

    <td><?php echo $row['Cod_Produs']; ?></td>

    <td><?php echo $client; ?></td>

    <td><?php echo $row['Data_Notificare']; ?></td>

    <td class="status_mesaj">
        <?php echo $row['Status']; ?>
    </td>

    <td class="actions-cell">

        <?php if($row['Status'] != 'Stoc Actualizat'){ ?>

            <form method="POST">

                <input
                    type="hidden"
                    name="ID_Notificare"
                    value="<?php echo $row['ID_Notificare']; ?>"
                >

                <input
                    type="hidden"
                    name="Cod_Produs"
                    value="<?php echo $row['Cod_Produs']; ?>"
                >

                <button
                    type="submit"
                    name="aprovizionare"
                    class="stock-btn"
                >
                    +10 Stoc
                </button>

            </form>

        <?php } else { ?>

            <span class="stock-done">
                Aprovizionat
            </span>

        <?php } ?>

    </td>

</tr>

<?php } ?>

</tbody>

</table>

</div>


<script>
const search = document.getElementById("search");

search.addEventListener("keyup", ()=>{

    const text = search.value.toLowerCase();
    const rows = document.querySelectorAll("#mesaje-table tbody tr");

    rows.forEach(row=>{

        if(row.innerText.toLowerCase().includes(text)){
            row.style.display = "";
        }else{
            row.style.display = "none";
        }

    });

});
</script>

</body>
</html>