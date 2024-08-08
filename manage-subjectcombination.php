<?php
session_start();
error_reporting(0);
include('includes/config.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
    // for activate Subject   	
    if (isset($_GET['acid'])) {
        $acid = intval($_GET['acid']);
        $status = 1;
        $sql = "update tblsubjectcombination set status=:status where id=:acid ";
        $query = $dbh->prepare($sql);
        $query->bindParam(':acid', $acid, PDO::PARAM_STR);
        $query->bindParam(':status', $status, PDO::PARAM_STR);
        $query->execute();
        $msg = " Materia activada Correctamente";
    }

    // for Deactivate Subject
    if (isset($_GET['did'])) {
        $did = intval($_GET['did']);
        $status = 0;
        $sql = "update tblsubjectcombination set status=:status where id=:did ";
        $query = $dbh->prepare($sql);
        $query->bindParam(':did', $did, PDO::PARAM_STR);
        $query->bindParam(':status', $status, PDO::PARAM_STR);
        $query->execute();
        $msg = " Materia Desactivada Correctamente";
    }
?>

    <link rel="stylesheet" type="text/css" href="assets/js/DataTables/datatables.min.css" />
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
                            <h2 class="title">Gestionar la Relación de Materia</h2>
                        </div>
                    </div>
                    <!-- /.row -->
                    <div class="row breadcrumb-div">
                        <div class="col-md-6">
                            <ul class="breadcrumb">
                                <li><a href="dashboard.php"><i class="fa fa-home"></i> Inicio</a></li>
                                <li> Materia</li>
                                <li class="active">Gestionar Relación de Materia</li>
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
                                            <h5>Ver Información de Relación de Materia</h5>
                                        </div>
                                    </div>
                                    <?php if ($msg) { ?>
                                        <div class="alert alert-success left-icon-alert" role="alert">
                                            <strong>Bien hecho!</strong><?php echo htmlentities($msg); ?>
                                        </div><?php } else if ($error) { ?>
                                        <div class="alert alert-danger left-icon-alert" role="alert">
                                            <strong>Hubo inconvenientes!</strong> <?php echo htmlentities($error); ?>
                                        </div>
                                    <?php } ?>
                                    <div class="panel-body p-20">

                                        <!-- Formulario para seleccionar el año y la sección -->
                                        <form method="post" action="">
                                            <div class="form-group">
                                                <label for="class">Seleccionar Año</label>
                                                <select name="class" class="form-control" id="class" required="required">
                                                    <option value="">Seleccionar Año</option>
                                                    <?php
                                                    $sql = "SELECT * from tblclasses";
                                                    $query = $dbh->prepare($sql);
                                                    $query->execute();
                                                    $results = $query->fetchAll(PDO::FETCH_OBJ);
                                                    if ($query->rowCount() > 0) {
                                                        foreach ($results as $result) { ?>
                                                            <option value="<?php echo htmlentities($result->id); ?>"><?php echo htmlentities($result->ClassName); ?> &nbsp; Sección-<?php echo htmlentities($result->Section); ?></option>
                                                    <?php }
                                                    } ?>
                                                </select>
                                            </div>
                                            <button type="submit" name="filter" class="btn btn-primary">Filtrar</button>
                                        </form>

                                        <table id="example" class="display table table-striped table-bordered" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Materia y Sección</th>
                                                    <th>Materia</th>
                                                    <th>Estado</th>
                                                    <th>Acción</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if (isset($_POST['filter'])) {
                                                    $classid = $_POST['class'];
                                                    $sql = "SELECT tblclasses.ClassName, tblclasses.Section, tblsubjects.SubjectName, tblsubjectcombination.id as scid, tblsubjectcombination.status FROM tblsubjectcombination JOIN tblclasses ON tblclasses.id=tblsubjectcombination.ClassId JOIN tblsubjects ON tblsubjects.id=tblsubjectcombination.SubjectId WHERE tblsubjectcombination.ClassId=:classid";
                                                    $query = $dbh->prepare($sql);
                                                    $query->bindParam(':classid', $classid, PDO::PARAM_STR);
                                                } else {
                                                    $sql = "SELECT tblclasses.ClassName, tblclasses.Section, tblsubjects.SubjectName, tblsubjectcombination.id as scid, tblsubjectcombination.status FROM tblsubjectcombination JOIN tblclasses ON tblclasses.id=tblsubjectcombination.ClassId JOIN tblsubjects ON tblsubjects.id=tblsubjectcombination.SubjectId";
                                                    $query = $dbh->prepare($sql);
                                                }
                                                $query->execute();
                                                $results = $query->fetchAll(PDO::FETCH_OBJ);
                                                $cnt = 1;
                                                if ($query->rowCount() > 0) {
                                                    foreach ($results as $result) { ?>
                                                        <tr>
                                                            <td><?php echo htmlentities($cnt); ?></td>
                                                            <td><?php echo htmlentities($result->ClassName); ?> &nbsp; Sección-<?php echo htmlentities($result->Section); ?></td>
                                                            <td><?php echo htmlentities($result->SubjectName); ?></td>
                                                            <td><?php $stts = $result->status;
                                                                if ($stts == '0') {
                                                                    echo htmlentities('Inactive');
                                                                } else {
                                                                    echo htmlentities('Active');
                                                                } ?>
                                                            </td>
                                                            <td>
                                                                <?php if ($stts == '0') { ?>
                                                                    <a href="manage-subjectcombination.php?acid=<?php echo htmlentities($result->scid); ?>" onclick="return confirm('Deseas activar esta materia?');" class="btn btn-success"><i class="fa fa-check" title="Acticvate Record"></i> </a><?php } else { ?>
                                                                    <a href="manage-subjectcombination.php?did=<?php echo htmlentities($result->scid); ?>" class="btn btn-danger" onclick="return confirm('Deseas desactivar esta materia?');"><i class="fa fa-times" title="Deactivate Record"></i> </a>
                                                                <?php } ?>
                                                            </td>
                                                        </tr>
                                                <?php $cnt = $cnt + 1;
                                                    }
                                                } ?>
                                            </tbody>
                                        </table>

                                        <!-- /.col-md-12 -->
                                    </div>
                                </div>
                            </div>
                            <!-- /.col-md-6 -->
                        </div>
                        <!-- /.col-md-12 -->
                    </div>
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