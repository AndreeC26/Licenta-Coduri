<?php
session_start();

if(!isset($_SESSION['user'])){
    header("Location: main.php");
    exit;
}

include "db.php";



if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_update'])){

    $ID_Client = $_POST['ID_Client'];

    $Nume = $_POST['nume'];

    $Prenume = $_POST['prenume'];

    $Telefon = $_POST['telefon'];

    $Adresa = $_POST['adresa'];



    mysqli_query($conn,"
        UPDATE clienti
        SET
            Nume='$Nume',
            Prenume='$Prenume',
            Telefon='$Telefon',
            Adresa='$Adresa'
        WHERE ID_Client='$ID_Client'
    ");



    echo json_encode([
        'status' => 'success'
    ]);

    exit;
}


$query = "
SELECT *
FROM clienti
ORDER BY ID_Client ASC
";

$result = mysqli_query($conn,$query);

?>

<!DOCTYPE html>
<html lang="ro">

<head>

<meta charset="UTF-8">

<title>Lista Clienți</title>

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

        <h2>Lista Clienți</h2>

    </div>

</div>





<div class="search-container">

    <input
        type="text"
        id="search"
        placeholder="Caută client..."
    >

    <div class="button-group">

        <button
            onclick="window.location.href='adaugaclient.php'"
        >

            Adaugă client

        </button>

       <button
    onclick="window.location.href='<?php echo ($_SESSION['rol'] == 'Manager') ? 'manager.php' : 'vanzator.php'; ?>'"
>

    Cancel

</button>

    </div>

</div>





<div class="table-container">

<table id="clienti-table">

<thead>

<tr>

    <th>ID Client</th>

    <th>Nume</th>

    <th>Prenume</th>

    <th>Telefon</th>

    <th>Adresă</th>

    <th>Acțiuni</th>

</tr>

</thead>



<tbody>

<?php while($row = mysqli_fetch_assoc($result)){ ?>

<tr>

    <td class="id_client">
        <?php echo $row['ID_Client']; ?>
    </td>

    <td class="nume">
        <?php echo $row['Nume']; ?>
    </td>

    <td class="prenume">
        <?php echo $row['Prenume']; ?>
    </td>

    <td class="telefon">
        <?php echo $row['Telefon']; ?>
    </td>

    <td class="adresa">
        <?php echo $row['Adresa']; ?>
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

    const text =
    search.value.toLowerCase();

    const rows =
    document.querySelectorAll("#clienti-table tbody tr");

    rows.forEach(row=>{

        if(
            row.innerText.toLowerCase().includes(text)
        ){

            row.style.display = "";

        }else{

            row.style.display = "none";
        }

    });

});





document.addEventListener("click", function(e){

    const btn = e.target;



    if(btn.classList.contains("edit-btn")){

        const row =
        btn.closest("tr");

        const fields = [
            'nume',
            'prenume',
            'telefon',
            'adresa'
        ];



        fields.forEach(cls=>{

            const cell =
            row.querySelector(`.${cls}`);

            const value =
            cell.innerText;

            cell.innerHTML =
            `<input type="text" value="${value}">`;

        });



        btn.classList.remove("edit-btn");

        btn.classList.add("save-btn");

        btn.innerText = "Save";
    }



    else if(btn.classList.contains("save-btn")){

        const row =
        btn.closest("tr");

        const ID_Client =
        row.querySelector(".id_client").innerText;



        const data = {

            ajax_update: 1,

            ID_Client: ID_Client,

            nume:
            row.querySelector(".nume input").value,

            prenume:
            row.querySelector(".prenume input").value,

            telefon:
            row.querySelector(".telefon input").value,

            adresa:
            row.querySelector(".adresa input").value
        };



        fetch("listaclienti.php",{

            method: "POST",

            headers:{
                "Content-Type":
                "application/x-www-form-urlencoded"
            },

            body:
            new URLSearchParams(data)
        })

        .then(res=>res.json())

        .then(resp=>{

            if(resp.status === "success"){

                row.querySelector(".nume")
                .innerText = data.nume;

                row.querySelector(".prenume")
                .innerText = data.prenume;

                row.querySelector(".telefon")
                .innerText = data.telefon;

                row.querySelector(".adresa")
                .innerText = data.adresa;



                btn.classList.remove("save-btn");

                btn.classList.add("edit-btn");

                btn.innerText = "Edit";

            }else{

                alert("Eroare la salvare!");

            }

        });

    }

});

</script>

</body>
</html>