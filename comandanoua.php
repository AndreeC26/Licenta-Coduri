<?php
include "db.php";
session_start();

if(!isset($_SESSION['user'])){
    header("Location: main.php");
    exit;
}

$produse = mysqli_query($conn,"
SELECT 
    p.Cod_Produs,
    p.Tip_Calcul,
    m.Pret,
    m.Manopera,
    m.Stoc,
    COALESCE(d.Pret, mo.Pret, mz.Pret) AS Pret_Stas
FROM produse p
JOIN materiale m
ON p.Cod_Material = m.Cod_Material
LEFT JOIN decorative d
ON p.Cod_Produs = d.Cod_Produs
LEFT JOIN monumente mo
ON p.Cod_Produs = mo.Cod_Produs
LEFT JOIN `mozaice&praf` mz
ON p.Cod_Produs = mz.Cod_Produs
");

if(isset($_POST['save'])){

    $Cod_Produs = $_POST['Cod_Produs'];
    $Nume = trim($_POST['Nume_Client']);
    $Prenume = trim($_POST['Prenume_Client']);
    $Telefon = trim($_POST['Telefon']);
    $Adresa_Mail = trim($_POST['Adresa_Mail']);
    $Adresa = trim($_POST['Adresa']);
    $Nr_Bucati = intval($_POST['Nr_Bucati']);
    $Dimensiune = trim($_POST['Dimensiune']);
    $Metoda_Plata = trim($_POST['Metoda_Plata']);

    if(
        empty($Cod_Produs) ||
        empty($Nume) ||
        empty($Prenume) ||
        empty($Telefon) ||
        empty($Adresa_Mail) ||
        empty($Adresa) ||
        empty($Metoda_Plata)
    ){
        die("Completează toate câmpurile!");
    }

    $checkClient = mysqli_query($conn,"
        SELECT ID_Client
        FROM clienti
        WHERE Telefon='$Telefon'
        LIMIT 1
    ");

    $client = mysqli_fetch_assoc($checkClient);

    if(!$client){

        mysqli_query($conn,"
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

        $ID_Client = mysqli_insert_id($conn);

    }else{

        $ID_Client = $client['ID_Client'];

        mysqli_query($conn,"
            UPDATE clienti
            SET Adresa_Mail='$Adresa_Mail'
            WHERE ID_Client='$ID_Client'
        ");
    }

    $getPret = mysqli_query($conn,"
        SELECT 
            p.Tip_Calcul,
            m.Pret,
            m.Manopera,
            m.Stoc,
            m.Cod_Material,
            COALESCE(d.Pret, mo.Pret, mz.Pret) AS Pret_Stas
        FROM produse p
        JOIN materiale m
        ON p.Cod_Material = m.Cod_Material
        LEFT JOIN decorative d
        ON p.Cod_Produs = d.Cod_Produs
        LEFT JOIN monumente mo
        ON p.Cod_Produs = mo.Cod_Produs
        LEFT JOIN `mozaice&praf` mz
        ON p.Cod_Produs = mz.Cod_Produs
        WHERE p.Cod_Produs='$Cod_Produs'
    ");

    $preturi = mysqli_fetch_assoc($getPret);

    if(!$preturi){
        die("Produsul nu a fost găsit!");
    }

    $Tip_Calcul = $preturi['Tip_Calcul'];

    if($Tip_Calcul == 'Bucata'){

        $Dimensiune = '-';
        $Nr_Placi = $Nr_Bucati;

        $Pret_Material = 0;
        $Pret_Manopera = 0;

        $Pret_Produs =
            $preturi['Pret_Stas'] *
            $Nr_Bucati;

    }else{

        if(empty($Dimensiune)){
            die("Completează dimensiunea!");
        }

        $parts = explode("x",$Dimensiune);

        if(count($parts) != 2){
            die("Dimensiunea trebuie să fie ex: 3x0.8");
        }

        $lungime = floatval($parts[0]);
        $latime = floatval($parts[1]);

        $mp = $lungime * $latime;
        $ml = $lungime + $latime;

        $suprafataPlaca = 1.6 * 0.6;

        $Nr_Placi =
            ceil(
                ($mp * $Nr_Bucati)
                /
                $suprafataPlaca
            );

        $Pret_Material =
            $mp *
            $preturi['Pret'] *
            $Nr_Bucati;

        $Pret_Manopera =
            $ml *
            $preturi['Manopera'] *
            $Nr_Bucati;

        $Pret_Produs =
            $Pret_Material +
            $Pret_Manopera;
    }

    $StocNou =
        $preturi['Stoc'] -
        $Nr_Placi;

    if($StocNou < 0){

        mysqli_query($conn,"
            INSERT INTO notificari
            (
                Cod_Produs,
                ID_Client,
                Mesaj,
                Status
            )
            VALUES
            (
                '$Cod_Produs',
                '$ID_Client',
                'Stoc insuficient pentru produsul $Cod_Produs. Clientul $Nume $Prenume asteapta reaprovizionarea.',
                'Necitita'
            )
        ");

        echo "
        <script>
            alert('Nu exista suficient stoc! Clientul a fost salvat, iar managerul a fost notificat pentru actualizarea stocului.');
            window.location.href='comandanoua.php';
        </script>
        ";

        exit;
    }

    mysqli_query($conn,"
        UPDATE materiale
        SET Stoc='$StocNou'
        WHERE Cod_Material='{$preturi['Cod_Material']}'
    ");

    mysqli_query($conn,"
        INSERT INTO comenzi
        (
            Cod_Produs,
            Nr_Bucati,
            Dimensiune,
            Nr_Placi,
            Pret_Material,
            Pret_Manopera,
            Pret_Produs,
            Data,
            Status,
            ID_Client,
            Metoda_Plata
        )
        VALUES
        (
            '$Cod_Produs',
            '$Nr_Bucati',
            '$Dimensiune',
            '$Nr_Placi',
            '$Pret_Material',
            '$Pret_Manopera',
            '$Pret_Produs',
            CURDATE(),
            'In Curs',
            '$ID_Client',
            '$Metoda_Plata'
        )
    ");

    $Nr_Comanda = mysqli_insert_id($conn);

    $Nr_Factura =
        "FACT-" . date("Ymd") . "-" . $Nr_Comanda;

    mysqli_query($conn,"
        INSERT INTO facturi
        (
            Nr_Comanda,
            Nr_Factura,
            Data_Factura,
            Total_Factura,
            Metoda_Plata
        )
        VALUES
        (
            '$Nr_Comanda',
            '$Nr_Factura',
            CURDATE(),
            '$Pret_Produs',
            '$Metoda_Plata'
        )
    ");

    echo "
    <script>
        alert('Comanda și factura au fost generate cu succes!');
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
<title>Comandă Nouă</title>
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
        <h2>Comandă Nouă</h2>
    </div>

</div>

<div class="form-container">

<form method="POST">

<div class="box">

    <h3>Date client</h3>

    <input type="text" name="Nume_Client" placeholder="Nume" required>

    <input type="text" name="Prenume_Client" placeholder="Prenume" required>

    <input type="text" name="Telefon" placeholder="Telefon" required>

    <input type="email" name="Adresa_Mail" placeholder="Adresă e-mail" required>

    <input type="text" name="Adresa" placeholder="Adresă" required>

</div>

<div class="box">

    <h3>Detalii comandă</h3>

    <input
        type="text"
        id="search"
        placeholder="Caută produs..."
        autocomplete="off"
    >

    <div id="list"></div>

    <input type="hidden" name="Cod_Produs" id="Cod_Produs">

    <input
        type="number"
        name="Nr_Bucati"
        placeholder="Număr bucăți"
        required
    >

    <input
        type="text"
        name="Dimensiune"
        id="Dimensiune"
        placeholder="Ex: 3x0.8"
    >

    <select name="Metoda_Plata" class="payment-select" required>

        <option value="" disabled selected hidden>
            Metodă plată
        </option>

        <option value="Cash">Cash</option>

        <option value="Card">Card</option>

    </select>

</div>

<div class="actions-container">

    <button
        type="button"
        id="openPreview"
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

<div class="preview-overlay" id="previewBox">

    <div class="preview-card">

        <h2>Confirmare comandă</h2>

        <div id="previewContent"></div>

        <div class="preview-buttons">

            <button
                type="submit"
                name="save"
                class="btn btn-primary"
            >
                Trimite comandă
            </button>

            <button
                type="button"
                id="closePreview"
                class="btn btn-secondary"
            >
                Cancel
            </button>

        </div>

    </div>

</div>

</form>

</div>

<script>

const produse = [

<?php while($p = mysqli_fetch_assoc($produse)){ ?>

{
    cod:"<?php echo $p['Cod_Produs']; ?>",
    tip:"<?php echo $p['Tip_Calcul']; ?>",
    pret:<?php echo $p['Pret']; ?>,
    man:<?php echo $p['Manopera']; ?>,
    pretStas:<?php echo ($p['Pret_Stas'] != null) ? $p['Pret_Stas'] : 0; ?>
},

<?php } ?>

];

const input = document.getElementById("search");
const list = document.getElementById("list");
const hidden = document.getElementById("Cod_Produs");
const dimInput = document.getElementById("Dimensiune");

input.addEventListener("input", ()=>{

    list.innerHTML = "";

    produse.forEach(p=>{

        if(
            p.cod.toLowerCase()
            .includes(input.value.toLowerCase())
        ){

            let div = document.createElement("div");

            div.innerHTML = p.cod;

            div.classList.add("search-item");

            div.onclick = ()=>{

                input.value = p.cod;

                hidden.value = p.cod;

                list.innerHTML = "";

                if(p.tip === "Bucata"){
                    dimInput.value = "";
                    dimInput.style.display = "none";
                }else{
                    dimInput.style.display = "block";
                }
            };

            list.appendChild(div);
        }
    });
});

const openBtn = document.getElementById("openPreview");
const closeBtn = document.getElementById("closePreview");
const previewBox = document.getElementById("previewBox");

openBtn.addEventListener("click", ()=>{

    const cod = hidden.value;

    const buc = parseInt(
        document.querySelector("[name='Nr_Bucati']").value || 0
    );

    const dim = dimInput.value;

    if(cod === "" || buc === 0){
        alert("Completează produsul și numărul de bucăți!");
        return;
    }

    const prod = produse.find(p=>p.cod===cod);

    if(prod.tip !== "Bucata" && !dim.includes("x")){
        alert("Dimensiunea trebuie să fie ex: 3x0.8");
        return;
    }

    let pretMat = 0;
    let pretMan = 0;
    let total = 0;
    let nrPlaci = 0;
    let dimAfisare = dim;

    if(prod.tip === "Bucata"){

        dimAfisare = "-";
        nrPlaci = buc;

        pretMat = 0;
        pretMan = 0;

        total =
            prod.pretStas *
            buc;

    }else{

        const [l,w] =
            dim.split("x").map(Number);

        const mp = l * w;
        const ml = l + w;

        pretMat =
            mp *
            prod.pret *
            buc;

        pretMan =
            ml *
            prod.man *
            buc;

        total =
            pretMat +
            pretMan;

        const suprafataPlaca =
            1.6 * 0.6;

        nrPlaci =
            Math.ceil(
                (mp * buc)
                /
                suprafataPlaca
            );
    }

    document.getElementById("previewContent").innerHTML = `

        <div class="preview-info">

            <p><b>Produs:</b> ${cod}</p>

            <p><b>Bucăți:</b> ${buc}</p>

            <p><b>Dimensiune:</b> ${dimAfisare}</p>

            <p><b>Număr plăci:</b> ${nrPlaci}</p>

            <hr>

            <p><b>Preț material:</b> ${pretMat.toFixed(2)} lei</p>

            <p><b>Preț manoperă:</b> ${pretMan.toFixed(2)} lei</p>

            <h3>Total: ${total.toFixed(2)} lei</h3>

        </div>
    `;

    previewBox.style.display = "flex";
});

closeBtn.addEventListener("click", ()=>{
    previewBox.style.display = "none";
});

</script>

</body>
</html>