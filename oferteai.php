<?php
session_start();

if(!isset($_SESSION['user'])){
    header("Location: main.php");
    exit;
}

if(!isset($_SESSION['oferte_ai'])){
    $_SESSION['oferte_ai'] = [];
}

if(!isset($_SESSION['oferte_trimise'])){
    $_SESSION['oferte_trimise'] = [];
}

include "db.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';



$openai_api_key = "apykey";


function genereazaOfertaAI($apiKey, $client, $produs){

    $prompt = "Genereaza o oferta comerciala eleganta in limba romana pentru clientul ".$client.". Clientul a cumparat produsul ".$produs." de la Marmurar de Ruschita. Recomanda servicii premium si produse complementare. Oferta trebuie sa fie scurta, profesionala si potrivita pentru email. Nu folosi markdown, nu folosi stelute, nu scrie [Date de contact]. La final scrie exact:

Cu deosebită considerație,
Echipa Marmurar de Ruschita
Telefon: 0789 569 245
Email: marmurarderuschita@gmail.com";

    $data = array(
        "model" => "gpt-4.1-mini",
        "input" => $prompt
    );

    $jsonData = json_encode($data);

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "https://api.openai.com/v1/responses");

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);

    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Content-Type: application/json",
        "Authorization: Bearer ".$apiKey
    ));

    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);

    if(curl_errno($ch)){

        $eroare = curl_error($ch);

        curl_close($ch);

        return "Eroare CURL: ".$eroare;
    }

    curl_close($ch);

    $result = json_decode($response, true);

    if(isset($result['output'][0]['content'][0]['text'])){

        return $result['output'][0]['content'][0]['text'];
    }

    if(isset($result['error']['message'])){

        return "Eroare AI: ".$result['error']['message'];
    }

    return "Răspuns API: ".$response;
}


if(isset($_POST['genereaza_oferta'])){

    $Nr_Comanda = $_POST['Nr_Comanda'];

    $Client = $_POST['Client'];

    $Produs = $_POST['Produs'];

    $_SESSION['oferte_ai'][$Nr_Comanda] =
    genereazaOfertaAI(
        $openai_api_key,
        $Client,
        $Produs
    );
}



if(isset($_POST['trimite_oferta'])){

    $Nr_Comanda = $_POST['Nr_Comanda'];

    $email = $_POST['Email'];

    $oferta = $_POST['Oferta'];

    $mail = new PHPMailer(true);

    try{

        $mail->isSMTP();

        $mail->Host = 'smtp.gmail.com';

        $mail->SMTPAuth = true;

        $mail->Username = 'marmurarderuschita@gmail.com';

        $mail->Password = 'apppassword';

        $mail->SMTPSecure =
        PHPMailer::ENCRYPTION_STARTTLS;

        $mail->Port = 587;

        $mail->CharSet = 'UTF-8';

        $mail->setFrom(
            'marmurarderuschita@gmail.com',
            'Marmurar de Ruschita'
        );

        $mail->addAddress($email);

        $mail->isHTML(true);

        $mail->Subject =
        'Ofertă personalizată - Marmurar de Ruschita';

        $mail->Body = nl2br($oferta);

        $mail->send();

        $_SESSION['oferte_trimise'][$Nr_Comanda] = true;

        echo "
        <script>
            alert('Oferta a fost trimisă!');
            window.location.href='oferteai.php';
        </script>
        ";

        exit;

    }catch(Exception $e){

        $eroareMail =
        addslashes($mail->ErrorInfo);

        echo "
        <script>
            alert('Oferta nu a putut fi trimisă: $eroareMail');
            window.location.href='oferteai.php';
        </script>
        ";

        exit;
    }
}




$query = "
SELECT 
    co.Nr_Comanda,
    co.Cod_Produs,
    c.Nume,
    c.Prenume,
    c.Adresa_Mail
FROM comenzi co
JOIN clienti c
ON co.ID_Client = c.ID_Client
ORDER BY co.Nr_Comanda DESC
";

$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>

<html lang='ro'>

<head>

<meta charset='UTF-8'>

<title>Oferte AI</title>

<link rel='stylesheet' href='style.css'>

</head>

<body class='admin-page'>


<div class='admin-top'>

    <div class='admin-brand'>

        <div class='admin-logo-row'>

            <img
                src='logo.png'
                class='admin-logo'
            >

            <div>

                <h1>Marmurar de Ruschita</h1>

                <p>
                    Eleganță naturală,
                    sculptată în piatră
                </p>

            </div>

        </div>

    </div>

    <div class='admin-title'>

        <h2>Oferte AI</h2>

    </div>

</div>



<div class='table-container'>

<table id='oferte-table'>

<thead>

<tr>

    <th>Client</th>

    <th>Produs cumpărat</th>

    <th>Recomandare AI</th>

    <th>Email</th>

    <th>Acțiune</th>

</tr>

</thead>


<tbody>

<?php while($row = mysqli_fetch_assoc($result)){ ?>


<?php

$client =
$row['Nume']." ".$row['Prenume'];

$produs =
$row['Cod_Produs'];

$email =
$row['Adresa_Mail'];

$nr =
$row['Nr_Comanda'];

$oferta =
isset($_SESSION['oferte_ai'][$nr])
? $_SESSION['oferte_ai'][$nr]
: "";

?>


<tr>

<td>

    <?php echo $client; ?>

</td>


<td class='produs-ai'>

    <?php echo $produs; ?>

</td>


<td>

<?php if($oferta != ""){ ?>

    <div class='ai-offer-box'>

        <?php echo nl2br($oferta); ?>

    </div>

<?php } else { ?>

    Oferta nu este generată încă.

<?php } ?>

</td>


<td>

    <?php echo $email; ?>

</td>



<td class='ai-actions'>


<?php if(isset($_SESSION['oferte_trimise'][$nr])){ ?>

    <div class='oferta-trimisa'>

        Ofertă trimisă

    </div>

<?php } else { ?>


<form method='POST'>

    <input
        type='hidden'
        name='Nr_Comanda'
        value='<?php echo $nr; ?>'
    >

    <input
        type='hidden'
        name='Client'
        value='<?php echo $client; ?>'
    >

    <input
        type='hidden'
        name='Produs'
        value='<?php echo $produs; ?>'
    >

    <button
        type='submit'
        name='genereaza_oferta'
        class='ai-btn'
    >
        Generează
    </button>

</form>


<?php if($oferta != "" && !empty($email)){ ?>


<form method='POST'>

    <input
        type='hidden'
        name='Nr_Comanda'
        value='<?php echo $nr; ?>'
    >

    <input
        type='hidden'
        name='Email'
        value='<?php echo $email; ?>'
    >

    <input
        type='hidden'
        name='Oferta'
        value='<?php echo htmlspecialchars($oferta); ?>'
    >

    <button
        type='submit'
        name='trimite_oferta'
        class='ai-btn'
    >
        Trimite mail
    </button>

</form>

<?php } ?>


<?php } ?>


</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>


<a href='manager.php' class='cancel-btn'>

    Cancel

</a>


</body>
</html>