<?php
session_start();
error_reporting(0);
include('includes/config.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
?>

<link rel="stylesheet" type="text/css" href="assets/js/DataTables/datatables.min.css" />
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- ========== TOP NAVBAR ========== -->
<?php include('includes/topbar.php'); ?>
<!-- ========== WRAPPER FOR BOTH SIDEBARS & MAIN CONTENT ========== -->
<div class="content-wrapper">
    <div class="content-container">
        <?php include('includes/leftbar.php'); ?>

        <div class="main-page">
            <div class="container-fluid">
                <div class="row page-title-div">
                    <div class="col-md-6">
                        <h2 class="title">Gestionar Resultados</h2>

                    </div>

                    <!-- /.col-md-6 text-right -->
                </div>
                <!-- /.row -->
                <div class="row breadcrumb-div">
                    <div class="col-md-6">
                        <ul class="breadcrumb">
                            <li><a href="dashboard.php"><i class="fa fa-home"></i> Inicio</a></li>
                            <li> Resultados</li>
                            <li class="active">Gestionar Resultados</li>
                        </ul>
                    </div>

                </div>
                <!-- /.row -->
            </div>
            <!-- /.container-fluid -->

            <section class="section">
                <div class="container-fluid">



                    <div class="row">
                        <div class="col-md-12">

                            <div class="panel">
                                <div class="panel-heading">
                                    <div class="panel-title">
                                        <h5>Ver Información de Resultados</h5>
                                    </div>
                                </div>
                                <?php if ($msg) { ?>
                                    <div class="alert alert-success left-icon-alert" role="alert">
                                        <strong>Proceso Correcto! </strong><?php echo htmlentities($msg); ?>
                                    </div><?php } else if ($error) { ?>
                                    <div class="alert alert-danger left-icon-alert" role="alert">
                                        <strong>Algo salió mal! </strong> <?php echo htmlentities($error); ?>
                                    </div>
                                <?php } ?>
                                <div class="panel-body p-20">

                                    <?php
                                    // Obtener todos los años distintos
                                    $sql = "SELECT DISTINCT ClassName FROM tblclasses";
                                    $query = $dbh->prepare($sql);
                                    $query->execute();
                                    $years = $query->fetchAll(PDO::FETCH_COLUMN);

                                    // Iterar sobre cada año
                                    foreach ($years as $year) {
                                        echo "<h3>" . $year . "</h3>";
                                        echo "<table id='example' class='display table table-striped table-bordered' cellspacing='0' width='100%'>";
                                        echo "<thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Nombre de Estudiante</th>
                                                    <th>ID Roll</th>
                                                    <th>Año</th>
                                                    <th>Fecha de Registro</th>
                                                    <th>Estado</th>";

                                        // Obtener las materias para este año
                                        $sqlSubjects = "SELECT tblsubjects.SubjectName 
                                                        FROM tblsubjects 
                                                        JOIN tblsubjectcombination ON tblsubjects.id = tblsubjectcombination.SubjectId
                                                        JOIN tblclasses ON tblsubjectcombination.ClassId = tblclasses.id
                                                        WHERE tblclasses.ClassName = :year";
                                        $querySubjects = $dbh->prepare($sqlSubjects);
                                        $querySubjects->bindParam(':year', $year);
                                        $querySubjects->execute();
                                        $subjects = $querySubjects->fetchAll(PDO::FETCH_COLUMN);

                                        // Agregar encabezados de materias
                                        foreach ($subjects as $subject) {
                                            echo "<th>" . $subject . "</th>";
                                        }

                                        echo "</tr>
                                            </thead>
                                            <tbody>";

                                        // Obtener los estudiantes para este año
                                        $sqlStudents = "SELECT  tblstudents.StudentName,tblstudents.RollId,tblstudents.RegDate,tblstudents.StudentId,tblstudents.Status,tblclasses.ClassName,tblclasses.Section 
                                                        FROM tblstudents 
                                                        JOIN tblclasses ON tblclasses.id = tblstudents.ClassId
                                                        WHERE tblclasses.ClassName = :year";
                                        $queryStudents = $dbh->prepare($sqlStudents);
                                        $queryStudents->bindParam(':year', $year);
                                        $queryStudents->execute();
                                        $students = $queryStudents->fetchAll(PDO::FETCH_OBJ);
                                        $cnt = 1;

                                        // Iterar sobre cada estudiante
                                        foreach ($students as $student) {
                                            echo "<tr>
                                                    <td>" . htmlentities($cnt) . "</td>
                                                    <td>" . htmlentities($student->StudentName) . "</td>
                                                    <td>" . htmlentities($student->RollId) . "</td>
                                                    <td>" . htmlentities($student->ClassName) . "(" . htmlentities($student->Section) . ")</td>
                                                    <td>" . htmlentities($student->RegDate) . "</td>
                                                    <td>";
                                            if ($student->Status == 1) {
                                                echo htmlentities('Activo');
                                            } else {
                                                echo htmlentities('Bloqueado');
                                            }
                                            echo "</td>";

                                            // Obtener las notas del estudiante para cada materia
                                            foreach ($subjects as $subject) {
                                                $sqlMarks = "SELECT tblresult.marks 
                                                             FROM tblresult 
                                                             JOIN tblstudents ON tblstudents.StudentId = tblresult.StudentId
                                                             JOIN tblclasses ON tblclasses.id = tblresult.ClassId
                                                             JOIN tblsubjects ON tblsubjects.id = tblresult.SubjectId
                                                             WHERE tblstudents.StudentId = :studentId 
                                                             AND tblclasses.ClassName = :year
                                                             AND tblsubjects.SubjectName = :subject";
                                                $queryMarks = $dbh->prepare($sqlMarks);
                                                $queryMarks->bindParam(':studentId', $student->StudentId);
                                                $queryMarks->bindParam(':year', $year);
                                                $queryMarks->bindParam(':subject', $subject);
                                                $queryMarks->execute();
                                                $marks = $queryMarks->fetchColumn();

                                                echo "<td>" . htmlentities($marks) . "</td>";
                                            }

                                            echo "</tr>";
                                            $cnt = $cnt + 1;
                                        }

                                        echo "</tbody></table>";
                                    }
                                    ?>


                                    <!-- /.col-md-12 -->
                                </div>
                            </div>
                        </div>
                        <!-- /.col-md-6 -->


                    </div>
                    <!-- /.col-md-12 -->
                </div>
        </div>
        <!-- /.panel -->
    </div>
    <!-- /.col-md-6 -->

</div>
<!-- /.row -->

</div>
<!-- /.container-fluid -->
</section>
<!-- /.section -->

</div>
<!-- /.main-page -->



</div>
<!-- /.content-container -->
</div>
<!-- /.content-wrapper -->

<?php include('includes/footer.php'); ?>



<?php } ?>