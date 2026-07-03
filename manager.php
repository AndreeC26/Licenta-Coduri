<?php
session_start();

if(!isset($_SESSION['user'])){
    header("Location: main.php");
    exit;
}

include "db.php";


$sort  = isset($_GET['sort']) ? $_GET['sort'] : 'Cod_Produs';
$order = isset($_GET['order']) ? $_GET['order'] : 'ASC';

$allowed = [
    'Cod_Produs',
    'Tip_Produs',
    'Cod_Material',
    'Material',
    'Denumire',
    'Culoare',
    'Pret',
    'Dimensiune',
    'Stoc',
    'Manopera'
];

if(!in_array($sort, $allowed)){
    $sort = 'Cod_Produs';
}

if($order != 'ASC' && $order != 'DESC'){
    $order = 'ASC';
}


if(isset($_GET['live_search'])){

    $q = isset($_GET['q']) ? trim($_GET['q']) : '';

    $material = isset($_GET['material'])
        ? trim($_GET['material'])
        : '';

    $type = isset($_GET['type'])
        ? trim($_GET['type'])
        : '';

    $color = isset($_GET['color'])
        ? trim($_GET['color'])
        : '';

    $price = isset($_GET['price'])
        ? trim($_GET['price'])
        : '';


    $sql = "
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

    WHERE 1=1
    ";



    if($q != ''){

        $sql .= "
        AND (
               produse.Cod_Produs LIKE '%$q%'
            OR produse.Tip_Produs LIKE '%$q%'
            OR produse.Cod_Material LIKE '%$q%'
            OR materiale.Material LIKE '%$q%'
            OR materiale.Denumire LIKE '%$q%'
            OR materiale.Culoare LIKE '%$q%'
        )
        ";
    }



    if($material != ''){
        $sql .= " AND materiale.Material = '$material'";
    }

    if($type != ''){
        $sql .= " AND produse.Tip_Produs = '$type'";
    }

    if($color != ''){
        $sql .= " AND materiale.Culoare = '$color'";
    }

    if($price != ''){
        $sql .= " AND materiale.Pret <= '$price'";
    }


    $sql .= " ORDER BY $sort $order";


    $result = mysqli_query($conn, $sql);


    while($row = mysqli_fetch_assoc($result)){

        $stockClass = "";

        if($row['Stoc'] < 5){
            $stockClass = "low-stock";
        }

        echo "
        <tr class='$stockClass'>

            <td>{$row['Cod_Produs']}</td>
            <td>{$row['Tip_Produs']}</td>
            <td>{$row['Cod_Material']}</td>
            <td>{$row['Material']}</td>
            <td>{$row['Denumire']}</td>
            <td>{$row['Culoare']}</td>
            <td>{$row['Pret']}</td>
            <td>{$row['Dimensiune']}</td>
            <td>{$row['Stoc']}</td>
            <td>{$row['Manopera']}</td>

        </tr>
        ";
    }

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

ORDER BY $sort $order
";

$result = mysqli_query($conn, $query);

$tipuri = mysqli_query($conn, "
    SELECT DISTINCT Tip_Produs
    FROM produse
    ORDER BY Tip_Produs ASC
");

$materiale = mysqli_query($conn, "
    SELECT DISTINCT Material
    FROM materiale
    ORDER BY Material ASC
");

$culori = mysqli_query($conn, "
    SELECT DISTINCT Culoare
    FROM materiale
    ORDER BY Culoare ASC
");

?>
<!DOCTYPE html>
<html lang="ro">

<head>

<meta charset="UTF-8">

<title>Panou Manager</title>

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

        <h2>Lista Produse</h2>

    </div>

</div>





<div class="search-container">

    <div class="seller-menu">



        <div class="dropdown">

            <button class="menu-btn">

                Produse

            </button>

            <div class="dropdown-content">

                <a href="#" id="open-filter">

                    Filtrare produse

                </a>

                <a href="stocredus.php">

                    Produse stoc redus

                </a>

            </div>

        </div>

<div class="dropdown">

    <button class="menu-btn">

        Comenzi

    </button>

    <div class="dropdown-content">

        <a href="comandanoua.php">
            Comandă nouă
        </a>

        <a href="comenziexistente.php">
            Comenzi existente
        </a>

        <a href="stergerecomenzi.php">
            Ștergere comandă
        </a>

    </div>

</div>

<div class="dropdown">

    <button class="menu-btn">

        Clienți

    </button>

    <div class="dropdown-content">

        <a href="adaugaclient.php">
            Înregistrare client
        </a>

        <a href="listaclienti.php">
            Listă clienți
        </a>

         <a href="stergereclienti.php">
            Stergere Clienti
        </a>
    </div>

</div>



<div class="dropdown">

    <button class="menu-btn">

        Facturi

    </button>

    <div class="dropdown-content">

        <a href="facturiexistente.php">
            Facturi existente
        </a>

        <a href="stergerefactura.php">
            Stergere Factura
        </a>

    </div>

</div>

<div class="dropdown">

    <button class="menu-btn">

        Mesaje

    </button>

    <div class="dropdown-content">

        <a href="mesajeprimite.php">
            Mesaje primite
        </a>

        <a href="oferteai.php">
            Oferte Generate
        </a>
    </div>
</div>

<div class="dropdown">

    <button class="menu-btn">
        Statistici
    </button>

    <div class="dropdown-content">

        <a href="statisticiproduse.php">
            Top produse vândute
        </a>

        <a href="statisticivenituri.php">
            Venituri
        </a>

        <a href="statisticimateriale.php">
            Analiză materiale
        </a>

    </div>

</div>

        <div class="dropdown">

            <button
                class="menu-btn"
                onclick="window.location.href='logout.php'"
            >

                Logout

            </button>

        </div>

    </div>



    <div class="search-box">

        <input
            type="text"
            id="search"
            placeholder="Caută produs..."
        >

    </div>

</div>





<div class="filter-panel" id="filter-panel">

    <div class="filter-row">



      <select id="filter-type">

    <option value="">
        Tip produs
    </option>

    <?php while($t = mysqli_fetch_assoc($tipuri)){ ?>

        <option value="<?php echo $t['Tip_Produs']; ?>">

            <?php echo $t['Tip_Produs']; ?>

        </option>

    <?php } ?>

</select>



       <select id="filter-material">

    <option value="">
        Materiale
    </option>

    <?php while($m = mysqli_fetch_assoc($materiale)){ ?>

        <option value="<?php echo $m['Material']; ?>">

            <?php echo $m['Material']; ?>

        </option>

    <?php } ?>

</select>



      <select id="filter-color">

    <option value="">
        Culori
    </option>

    <?php while($c = mysqli_fetch_assoc($culori)){ ?>

        <option value="<?php echo $c['Culoare']; ?>">

            <?php echo $c['Culoare']; ?>

        </option>

    <?php } ?>

</select>



        <input
            type="number"
            id="filter-price"
            placeholder="Pret maxim"
        >



        <button id="apply-filter">

            Aplică filtre

        </button>

    </div>

</div>






<div class="table-container">

<table id="products-table">

<thead>

<tr>

    <th>Cod Produs</th>

    <th>Tip Produs</th>

    <th>Cod Material</th>

    <th>Material</th>

    <th>Denumire</th>

    <th>Culoare</th>

    <th>Pret</th>

    <th>Dimensiune</th>

    <th>Stoc</th>

    <th>Manopera</th>

</tr>

</thead>



<tbody>

<?php while($row = mysqli_fetch_assoc($result)){ ?>

<?php

$stockClass = "";

if($row['Stoc'] < 5){
    $stockClass = "low-stock";
}

?>

<tr class="<?php echo $stockClass; ?>">

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





<script>

const search =
document.getElementById("search");

const tbody =
document.querySelector("#products-table tbody");

const filterPanel =
document.getElementById("filter-panel");



document
.getElementById("open-filter")
.addEventListener("click", function(e){

    e.preventDefault();

    if(filterPanel.style.display === "block"){

        filterPanel.style.display = "none";

    }else{

        filterPanel.style.display = "block";

    }

});



function loadProducts(){

    const text =
    document.getElementById("search").value;

    const material =
    document.getElementById("filter-material").value;

    const type =
    document.getElementById("filter-type").value;

    const color =
    document.getElementById("filter-color").value;

    const price =
    document.getElementById("filter-price").value;



fetch(

`manager.php?live_search=1
&q=${encodeURIComponent(text)}
&material=${encodeURIComponent(material)}
&type=${encodeURIComponent(type)}
&color=${encodeURIComponent(color)}
&price=${encodeURIComponent(price)}
&sort=<?php echo $sort; ?>
&order=<?php echo $order; ?>`

)

.then(response => response.text())

.then(data => tbody.innerHTML = data);

}



search.addEventListener("keyup", loadProducts);


document
.getElementById("apply-filter")
.addEventListener("click", loadProducts);

</script>

</body>
</html>