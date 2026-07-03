<?php
session_start();

if(!isset($_SESSION['user'])){
    header("Location: main.php");
    exit;
}

include "db.php";

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajax_update'])){

    $Nr_Comanda = $_POST['Nr_Comanda'];

    $Cod_Produs = $_POST['Cod_Produs'];

    $Nr_Bucati = intval($_POST['Nr_Bucati']);

    $Dimensiune = $_POST['Dimensiune'];

    $Status = $_POST['Status'];


    $old = mysqli_query($conn,"
        SELECT
            Nr_Placi,
            Cod_Produs
        FROM comenzi
        WHERE Nr_Comanda='$Nr_Comanda'
    ");

    $oldData = mysqli_fetch_assoc($old);

    $placi_vechi =
        intval($oldData['Nr_Placi']);



    $qOldMat = mysqli_query($conn,"
        SELECT Cod_Material
        FROM produse
        WHERE Cod_Produs='".$oldData['Cod_Produs']."'
    ");

    $oldMat =
        mysqli_fetch_assoc($qOldMat);

    $Cod_Material_Vechi =
        $oldMat['Cod_Material'];



    mysqli_query($conn,"
        UPDATE materiale
        SET Stoc = Stoc + $placi_vechi
        WHERE Cod_Material='$Cod_Material_Vechi'
    ");



    $parts = explode("x",$Dimensiune);

    $lungime = floatval($parts[0]);

    $latime = floatval($parts[1]);



    $mp = $lungime * $latime;

    $ml = $lungime + $latime;



    $suprafata_placa = 1.6 * 0.6;

    $Nr_Placi = ceil(
        ($mp * $Nr_Bucati)
        / $suprafata_placa
    );


    $qMat = mysqli_query($conn,"
        SELECT Cod_Material
        FROM produse
        WHERE Cod_Produs='$Cod_Produs'
    ");

    $mat =
        mysqli_fetch_assoc($qMat);

    $Cod_Material =
        $mat['Cod_Material'];


    mysqli_query($conn,"
        UPDATE materiale
        SET Stoc = Stoc - $Nr_Placi
        WHERE Cod_Material='$Cod_Material'
    ");

    $q = mysqli_query($conn,"
        SELECT
            m.Pret,
            m.Manopera
        FROM produse p
        JOIN materiale m
        ON p.Cod_Material = m.Cod_Material
        WHERE p.Cod_Produs='$Cod_Produs'
    ");

    $preturi =
        mysqli_fetch_assoc($q);



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


    mysqli_query($conn,"
        UPDATE comenzi
        SET
            Cod_Produs='$Cod_Produs',
            Nr_Bucati='$Nr_Bucati',
            Dimensiune='$Dimensiune',
            Nr_Placi='$Nr_Placi',
            Pret_Material='$Pret_Material',
            Pret_Manopera='$Pret_Manopera',
            Pret_Produs='$Pret_Produs',
            Status='$Status'
        WHERE Nr_Comanda='$Nr_Comanda'
    ");



    echo json_encode([

        'status' => 'success',

        'Nr_Placi' => $Nr_Placi,

        'Pret_Material' =>
            number_format($Pret_Material,2),

        'Pret_Manopera' =>
            number_format($Pret_Manopera,2),

        'Pret_Produs' =>
            number_format($Pret_Produs,2)
    ]);

    exit;
}



$query = mysqli_query($conn,"
    SELECT *
    FROM comenzi
    ORDER BY Nr_Comanda DESC
");
?>

<!DOCTYPE html>
<html lang="ro">

<head>

<meta charset="UTF-8">

<title>Comenzi Existente</title>

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

        <h2>Comenzi Existente</h2>

    </div>

</div>


<div class="search-container">

    <input
        type="text"
        id="search"
        placeholder="Caută comandă..."
    >

    <div class="button-group">

       <button
    onclick="window.location.href='<?php echo ($_SESSION['rol'] == 'Manager') ? 'manager.php' : 'vanzator.php'; ?>'"
>
    Back
</button>

    </div>

</div>



<div class="table-container">

<table id="orders-table">

<thead>

<tr>

    <th>Nr_Comanda</th>

    <th>Cod_Produs</th>

    <th>Nr_Bucati</th>

    <th>Dimensiune</th>

    <th>Nr_Placi</th>

    <th>Pret_Material</th>

    <th>Pret_Manopera</th>

    <th>Pret_Produs</th>

    <th>Data</th>

    <th>Status</th>

    <th>ID_Client</th>

    <th>Actiuni</th>

</tr>

</thead>



<tbody>

<?php while($row = mysqli_fetch_assoc($query)){ ?>

<tr>

<td class="Nr_Comanda">
    <?php echo $row['Nr_Comanda']; ?>
</td>

<td class="Cod_Produs">
    <?php echo $row['Cod_Produs']; ?>
</td>

<td class="Nr_Bucati">
    <?php echo $row['Nr_Bucati']; ?>
</td>

<td class="Dimensiune">
    <?php echo $row['Dimensiune']; ?>
</td>

<td class="Nr_Placi">
    <?php echo $row['Nr_Placi']; ?>
</td>

<td class="Pret_Material">
    <?php echo $row['Pret_Material']; ?>
</td>

<td class="Pret_Manopera">
    <?php echo $row['Pret_Manopera']; ?>
</td>

<td class="Pret_Produs">
    <?php echo $row['Pret_Produs']; ?>
</td>

<td>
    <?php echo $row['Data']; ?>
</td>

<td class="Status">
    <?php echo $row['Status']; ?>
</td>

<td>
    <?php echo $row['ID_Client']; ?>
</td>

<td class="actions">

<button
    type="button"
    class="edit-btn"
>
    Edit
</button>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>





<script>


const search =
document.getElementById("search");

search.addEventListener("keyup", ()=>{

    const value =
    search.value.toLowerCase();

    const rows =
    document.querySelectorAll("#orders-table tbody tr");

    rows.forEach(row=>{

        row.style.display =
        row.innerText.toLowerCase().includes(value)
        ? ""
        : "none";
    });
});


document.addEventListener("click", function(e){

    const btn = e.target;


    if(btn.classList.contains("edit-btn")){

        const row =
        btn.closest("tr");

        const fields = [

            'Cod_Produs',
            'Nr_Bucati',
            'Dimensiune',
            'Status'
        ];



        fields.forEach(cls=>{

            const cell =
            row.querySelector(`.${cls}`);

            const value =
            cell.innerText;

            cell.innerHTML =
            `<input type="text" value="${value}">`;
        });



        btn.innerText = "Save";

        btn.classList.remove("edit-btn");

        btn.classList.add("save-btn");
    }



    else if(btn.classList.contains("save-btn")){

        const row =
        btn.closest("tr");



        const data = {

            ajax_update:1,

            Nr_Comanda:
            row.querySelector(".Nr_Comanda").innerText,

            Cod_Produs:
            row.querySelector(".Cod_Produs input").value,

            Nr_Bucati:
            row.querySelector(".Nr_Bucati input").value,

            Dimensiune:
            row.querySelector(".Dimensiune input").value,

            Status:
            row.querySelector(".Status input").value
        };



        fetch("comenziexistente.php",{

            method:"POST",

            headers:{
                "Content-Type":
                "application/x-www-form-urlencoded"
            },

            body:new URLSearchParams(data)
        })

        .then(res=>res.json())

        .then(resp=>{

            if(resp.status == 'success'){

                row.querySelector('.Cod_Produs')
                .innerText = data.Cod_Produs;

                row.querySelector('.Nr_Bucati')
                .innerText = data.Nr_Bucati;

                row.querySelector('.Dimensiune')
                .innerText = data.Dimensiune;

                row.querySelector('.Status')
                .innerText = data.Status;

                row.querySelector('.Nr_Placi')
                .innerText = resp.Nr_Placi;

                row.querySelector('.Pret_Material')
                .innerText = resp.Pret_Material;

                row.querySelector('.Pret_Manopera')
                .innerText = resp.Pret_Manopera;

                row.querySelector('.Pret_Produs')
                .innerText = resp.Pret_Produs;



                btn.innerText = "Edit";

                btn.classList.remove("save-btn");

                btn.classList.add("edit-btn");
            }
        });
    }

});

</script>

</body>
</html>