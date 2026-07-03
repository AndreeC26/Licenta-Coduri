<?php
session_start();

if(!isset($_SESSION['user'])){
    header("Location: main.php");
    exit;
}

include "db.php";


if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_update'])){

    $Nr_Factura = $_POST['Nr_Factura'];

    $Nr_Comanda = $_POST['nr_comanda'];

    $Data_Factura = $_POST['data_factura'];

    $Total_Factura = $_POST['total_factura'];

    $Metoda_Plata = $_POST['metoda_plata'];



    mysqli_query($conn,"
        UPDATE facturi
        SET
            Nr_Comanda='$Nr_Comanda',
            Data_Factura='$Data_Factura',
            Total_Factura='$Total_Factura',
            Metoda_Plata='$Metoda_Plata'
        WHERE Nr_Factura='$Nr_Factura'
    ");



    echo json_encode([
        'status' => 'success'
    ]);

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

<title>Facturi Existente</title>

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

        <h2>Facturi Existente</h2>

    </div>

</div>





<div class="search-container">

    <input
        type="text"
        id="search"
        placeholder="Caută factură..."
    >

    <div class="button-group">

       <button
    onclick="window.location.href='<?php echo ($_SESSION['rol'] == 'Manager') ? 'manager.php' : 'vanzator.php'; ?>'"
>

    Cancel

</button>

    </div>

</div>





<div class="table-container">

<table id="facturi-table">

<thead>

<tr>

    <th>Nr Factură</th>

    <th>Nr Comandă</th>

    <th>Data Factură</th>

    <th>Total Factură</th>

    <th>Metodă Plată</th>

    <th>Acțiuni</th>

</tr>

</thead>



<tbody>

<?php while($row = mysqli_fetch_assoc($result)){ ?>

<tr>

    <td class="nr_factura">
        <?php echo $row['Nr_Factura']; ?>
    </td>

    <td class="nr_comanda">
        <?php echo $row['Nr_Comanda']; ?>
    </td>

    <td class="data_factura">
        <?php echo $row['Data_Factura']; ?>
    </td>

    <td class="total_factura">
        <?php echo $row['Total_Factura']; ?> lei
    </td>

    <td class="metoda_plata">
        <?php echo $row['Metoda_Plata']; ?>
    </td>

  <td class="actions-cell">

    <button
        type="button"
        class="edit-btn"
    >

        Edit

    </button>

    <a
        href="export_pdf.php?factura=<?php echo $row['Nr_Factura']; ?>"
        target="_blank"
        class="pdf-btn"
    >

        PDF

    </a>

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
    document.querySelectorAll("#facturi-table tbody tr");

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
            'nr_comanda',
            'data_factura',
            'total_factura',
            'metoda_plata'
        ];



        fields.forEach(cls=>{

            const cell =
            row.querySelector(`.${cls}`);

            let value =
            cell.innerText;

            value = value.replace(" lei","");

            cell.innerHTML =
            `<input type="text" value="${value}">`;

        });



        btn.classList.remove("edit-btn");

        btn.classList.add("save-btn");

        btn.innerText = "Save";
        const pdfBtn = row.querySelector(".pdf-btn");

if(pdfBtn){
    pdfBtn.style.display = "none";
}
    }



    else if(btn.classList.contains("save-btn")){

        const row =
        btn.closest("tr");

        const Nr_Factura =
        row.querySelector(".nr_factura").innerText;



        const data = {

            ajax_update: 1,

            Nr_Factura: Nr_Factura,

            nr_comanda:
            row.querySelector(".nr_comanda input").value,

            data_factura:
            row.querySelector(".data_factura input").value,

            total_factura:
            row.querySelector(".total_factura input").value,

            metoda_plata:
            row.querySelector(".metoda_plata input").value
        };



        fetch("facturiexistente.php",{

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

                row.querySelector(".nr_comanda")
                .innerText = data.nr_comanda;

                row.querySelector(".data_factura")
                .innerText = data.data_factura;

                row.querySelector(".total_factura")
                .innerText = data.total_factura + " lei";

                row.querySelector(".metoda_plata")
                .innerText = data.metoda_plata;



                btn.classList.remove("save-btn");

                btn.classList.add("edit-btn");

                btn.innerText = "Edit";
                const pdfBtn = row.querySelector(".pdf-btn");

if(pdfBtn){
    pdfBtn.style.display = "inline-flex";
}

            }else{

                alert("Eroare la salvare!");

            }

        });

    }

});

</script>

</body>
</html>