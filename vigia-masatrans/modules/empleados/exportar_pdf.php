<?php

require('../../fpdf/fpdf.php');

include("../../config/database.php");

$pdf = new FPDF('L','mm','A4');

$pdf->AddPage();

$pdf->SetTitle('Reporte Empleados');

$pdf->SetFont('Arial','B',18);

$pdf->Cell(
    0,
    12,
    utf8_decode('REPORTE GENERAL DE EMPLEADOS - VIGIA MASATRANS'),
    0,
    1,
    'C'
);

$pdf->Ln(5);

$pdf->SetFont('Arial','',10);

$pdf->Cell(
    0,
    8,
    'Fecha de generacion: '.date('d/m/Y H:i:s'),
    0,
    1,
    'R'
);

$pdf->Ln(5);

$pdf->SetFillColor(37,99,235);

$pdf->SetTextColor(255,255,255);

$pdf->SetFont('Arial','B',10);

$pdf->Cell(15,10,'ID',1,0,'C',true);
$pdf->Cell(50,10,'Empleado',1,0,'C',true);
$pdf->Cell(30,10,'Cedula',1,0,'C',true);
$pdf->Cell(45,10,'Cargo',1,0,'C',true);
$pdf->Cell(30,10,'Celular',1,0,'C',true);
$pdf->Cell(55,10,'Correo',1,0,'C',true);
$pdf->Cell(35,10,'Ciudad',1,0,'C',true);
$pdf->Cell(25,10,'Estado',1,1,'C',true);

$pdf->SetTextColor(0,0,0);

$pdf->SetFont('Arial','',9);

$query = mysqli_query($conn,"
SELECT * FROM empleados
ORDER BY id DESC
");

while($empleado = mysqli_fetch_assoc($query)){

    $pdf->Cell(
        15,
        10,
        $empleado['id'],
        1
    );

    $pdf->Cell(
        50,
        10,
        utf8_decode($empleado['nombres'].' '.$empleado['apellidos']),
        1
    );

    $pdf->Cell(
        30,
        10,
        $empleado['cedula'],
        1
    );

    $pdf->Cell(
        45,
        10,
        utf8_decode($empleado['cargo']),
        1
    );

    $pdf->Cell(
        30,
        10,
        $empleado['celular'],
        1
    );

    $pdf->Cell(
        55,
        10,
        utf8_decode($empleado['correo']),
        1
    );

    $pdf->Cell(
        35,
        10,
        utf8_decode($empleado['ciudad']),
        1
    );

    $pdf->Cell(
        25,
        10,
        'ACTIVO',
        1
    );

    $pdf->Ln();

}

$pdf->Output();

?>