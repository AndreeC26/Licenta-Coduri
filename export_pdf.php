<?php

require('fpdf/fpdf.php');
include "db.php";

class PDF extends FPDF {

    function Header() {

    
        $this->Image('logo.png', 10, 8, 26);

    
        $this->SetFont('Arial','B',16);
        $this->SetTextColor(15,71,175);
        $this->Cell(0,8,'Marmurar de Ruschita',0,1,'C');
        $this->SetFont('Arial','I',10);
        $this->SetTextColor(120,120,120);
        $this->Cell(0,6,'Eleganta naturala, sculptata in piatra',0,1,'C');
        $this->Ln(3);
        $this->SetDrawColor(220,220,220);
        $this->Line(10, 32, 200, 32);


        $this->Ln(8);
        $this->SetFont('Arial','B',15);
        $this->SetTextColor(0,0,0);
        $this->Cell(0,10,'FACTURA FISCALA',0,1,'C');

        $this->SetFont('Arial','I',10);
        $this->SetTextColor(90,90,90);
        $this->Cell(0,6,'Document emis din sistemul Marmurar de Ruschita',0,1,'C');

        $this->Ln(5);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->SetTextColor(150,150,150);
        $this->Cell(0,10,'Marmurar de Ruschita - Sistem Facturare | Pagina '.$this->PageNo(),0,0,'C');
    }
}


$nr = $_GET['factura'];


$query = mysqli_query($conn,"
SELECT 
    f.*,
    c.Nume,
    c.Prenume,
    c.Telefon,
    c.Adresa,
    co.Cod_Produs,
    co.Nr_Bucati,
    co.Dimensiune
FROM facturi f
JOIN comenzi co ON f.Nr_Comanda = co.Nr_Comanda
JOIN clienti c ON co.ID_Client = c.ID_Client
WHERE f.Nr_Factura='$nr'
");

$data = mysqli_fetch_assoc($query);


$pdf = new PDF();
$pdf->AddPage();


$pdf->SetFont('Arial','B',11);
$pdf->SetFillColor(240,240,240);

$pdf->Cell(95,8,'DETALII FACTURA',1,0,'L',true);
$pdf->Cell(95,8,'DATE CLIENT',1,1,'L',true);

$pdf->SetFont('Arial','',10);

$pdf->Cell(95,8,'Nr Factura: '.$data['Nr_Factura'],1,0);
$pdf->Cell(95,8,$data['Nume'].' '.$data['Prenume'],1,1);

$pdf->Cell(95,8,'Nr Comanda: '.$data['Nr_Comanda'],1,0);
$pdf->Cell(95,8,'Telefon: '.$data['Telefon'],1,1);

$pdf->Cell(95,8,'Data: '.$data['Data_Factura'],1,0);
$pdf->Cell(95,8,'Adresa: '.$data['Adresa'],1,1);

$pdf->Cell(95,8,'Metoda Plata: '.$data['Metoda_Plata'],1,0);
$pdf->Cell(95,8,'',1,1);

$pdf->Ln(8);

$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,10,'DETALII COMANDA',0,1);

$pdf->SetFont('Arial','B',11);
$pdf->SetFillColor(220,220,220);

$pdf->Cell(60,10,'Produs',1,0,'C',true);
$pdf->Cell(30,10,'Bucati',1,0,'C',true);
$pdf->Cell(50,10,'Dimensiune',1,0,'C',true);
$pdf->Cell(50,10,'Total',1,1,'C',true);


$pdf->SetFont('Arial','',10);

$pdf->Cell(60,10,$data['Cod_Produs'],1,0,'C');
$pdf->Cell(30,10,$data['Nr_Bucati'],1,0,'C');
$pdf->Cell(50,10,$data['Dimensiune'],1,0,'C');
$pdf->Cell(50,10,$data['Total_Factura'].' lei',1,1,'C');


$pdf->Ln(10);

$pdf->SetFont('Arial','B',13);
$pdf->SetFillColor(255,245,200);

$pdf->Cell(140,10,'TOTAL FACTURA',1,0,'R',true);
$pdf->Cell(50,10,$data['Total_Factura'].' LEI',1,1,'C');


$pdf->Ln(10);
$pdf->SetFont('Arial','I',10);
$pdf->SetTextColor(80,80,80);
$pdf->Cell(0,6,'Va multumim pentru alegerea serviciilor noastre!',0,1,'C');

$pdf->Ln(2);
$pdf->SetFont('Arial','',9);
$pdf->SetTextColor(120,120,120);
$pdf->Cell(0,6,'Marmurar de Ruschita - eleganta naturala in fiecare detaliu.',0,1,'C');



$pdf->Output();

?>