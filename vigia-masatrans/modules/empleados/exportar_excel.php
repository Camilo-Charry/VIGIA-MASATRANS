<?php

include("../../config/database.php");

header("Content-Type: application/xls");
header("Content-Disposition: attachment; filename=empleados.xls");

?>

<table border="1">

    <thead>

        <tr style="background:#2563eb;color:white;">

            <th>ID</th>
            <th>Nombres</th>
            <th>Apellidos</th>
            <th>Cédula</th>
            <th>Cargo</th>
            <th>Celular</th>
            <th>Correo</th>
            <th>Ciudad</th>
            <th>Departamento</th>
            <th>EPS</th>
            <th>ARL</th>
            <th>Pensión</th>
            <th>Salario Base</th>

        </tr>

    </thead>

    <tbody>

    <?php

    $query = mysqli_query($conn,"
    SELECT * FROM empleados
    ORDER BY id DESC
    ");

    while($empleado = mysqli_fetch_assoc($query)){

    ?>

        <tr>

            <td>
                <?= $empleado['id'] ?>
            </td>

            <td>
                <?= $empleado['nombres'] ?>
            </td>

            <td>
                <?= $empleado['apellidos'] ?>
            </td>

            <td>
                <?= $empleado['cedula'] ?>
            </td>

            <td>
                <?= $empleado['cargo'] ?>
            </td>

            <td>
                <?= $empleado['celular'] ?>
            </td>

            <td>
                <?= $empleado['correo'] ?>
            </td>

            <td>
                <?= $empleado['ciudad'] ?>
            </td>

            <td>
                <?= $empleado['departamento'] ?>
            </td>

            <td>
                <?= $empleado['eps'] ?>
            </td>

            <td>
                <?= $empleado['arl'] ?>
            </td>

            <td>
                <?= $empleado['pension'] ?>
            </td>

            <td>
                <?= $empleado['salario_base'] ?>
            </td>

        </tr>

    <?php } ?>

    </tbody>

</table>