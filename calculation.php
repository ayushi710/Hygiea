<?php
session_start();
include 'connection.php';
include "function.php";

if(isset($_POST["create_pdf"]))
{
    require_once("tcpdf/tcpdf.php");
    $obj_pdf = new TCPDF("P", PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $obj_pdf->SetCreator(PDF_CREATOR);
    $obj_pdf->SetTitle("Download diet plan");
    $obj_pdf->SetHeaderData('', '',PDF_HEADER_TITLE,PDF_HEADER_STRING);
    $obj_pdf->setHeaderFont(Array(PDF_FONT_SIZE_MAIN,'', PDF_FONT_SIZE_MAIN));
    $obj_pdf->setFooterFont(Array(PDF_FONT_NAME_DATA,'', PDF_FONT_SIZE_DATA));
    $obj_pdf->SetDefaultMonospacedFont('helvetica');
    $obj_pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    $obj_pdf->SetMargins(PDF_MARGIN_LEFT,'5',PDF_MARGIN_RIGHT);
    $obj_pdf->setPrintHeader(false);
    $obj_pdf->setPrintFooter(false);
    $obj_pdf->SetAutoPageBreak(TRUE,10);
    $obj_pdf->SetFont('helvetica','',12);

    $content = '';
    $content .= ' <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Image</th>
                        <th>Calorie</th>
                        <th>amount</th>
                        <th>Scale</th>
                    </tr>

                    </thead>
                    <tbody>';

    $content .= '</tbody> </table> ';

    $obj_pdf->writeHTML($content);

    $obj_pdf->Output("sample.pdf", "I");
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Result page</title>
    <link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="font-awesome/css/font-awesome.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <style>
        .body_continer_inpuit{
            /*background-image: url("img/cal.jpeg");*/

            background-size: 100% 100%;
            min-height: 100vh;
            opacity: .9;

        }


        .form_start h1,h2,h3{
            color:blueviolet;
            font-family: "Lucida Grande", "Lucida Sans Unicode", Verdana, Arial, Helvetica, sans-serif;
            font-weight: bold;


        }

    </style>
</head>
<body>
<div class="body_continer_inpuit">
    <div class="container">
        <?php
        //include 'navigation_user.php';

        ?>
<div class="main_calculation" style="margin-top: 20px">
        <div class="row ">
            <div class="col-sm-6 col-sm-offset-3">
<!--                <h2 class="text-center">BEST DIET CHART</h2>-->
<!---->
<!--                <h3 class="text-center">GET YOUR FREE DIET ANALYSIS</h3>-->
                <?php



                ?>

        <div class="panel panel-primary ">
<!--            this is for the heading -->
            <?php
            $total = $_SESSION['sum_total'];
            $needed = intval($_SESSION['calorie']);
            $total_test = $needed;

            $sql_cal = "SELECT food_item.id as id,food_item.name as name,food_item.image as image,food_item.scale as scale,food_item.quantity as quantity ,food_item.calories as calories ,food_categories as category FROM food_item RIGHT JOIN choice ON choice.food_id = food_item.id ";
            $sql_cal_1 =$con->query($sql_cal);
            $array_limit = array();
            $array_not_limit = array();
            $count_loop = 0;

            while ($sql_cal_2 = $sql_cal_1->fetch_assoc()){

                $age = $_SESSION['age'];
                $category = strtolower($sql_cal_2['category']);
                $scale = strtolower($sql_cal_2['scale']);
                $limit_query = "SELECT $scale from limit_chart WHERE '$age' BETWEEN age_lower and age_upper AND category = '$category'";
                $limit_res = $con->query($limit_query)->fetch_assoc();
                $limit_scale = $limit_res[$scale];






                $back_total = convert_total($total,$sql_cal_2['calories']);
                $back_final = intval(convert_needed($back_total,$needed));
                $piece = (convert_scale($sql_cal_2['calories'],$sql_cal_2['quantity'],$back_final));
                $one_piece = $back_final / $piece;
                if ($piece > $limit_scale){

                    @$limit_piece = $limit_scale."<br>";
                    @$limit_calorie = $one_piece * $limit_piece."<br>";
                    @$total_test = $total_test - $limit_calorie."<br>";
                    @$array_limit[$sql_cal_2['id']] = $limit_calorie;






                }
                else{
                    $limit_piece = $piece;
                    $limit_calorie = $one_piece * $limit_piece;
                    @$total_test = $total_test - $limit_calorie."<br>";
                    @$array_not_limit[$sql_cal_2['id']] = $limit_calorie;

                }







                ?>


            <?php }



            $count = count($array_not_limit);
            if($total_test > 6 && $count > 0){
                $count = count($array_not_limit);
                @$distribute = $total_test/$count;
                foreach ($array_not_limit as $id => $calorie){
                    $query_category = "select food_categories,scale,calories from food_item WHERE  id = '$id'";
                    $result = $con->query($query_category)->fetch_assoc();
                    $category = $result['food_categories'];
                    $scale = strtolower($result['scale']);
                    $age = $_SESSION['age'];
                    $calorie_single = $result['calories'];


                    $limit_query = "SELECT $scale from limit_chart WHERE '$age' BETWEEN age_lower and age_upper AND category = '$category'";
                    $limit_res = $con->query($limit_query)->fetch_assoc();
                    $limit_scale = $limit_res[$scale];
                    $calorie_plus = ($calorie + $distribute)/$calorie_single;
                    if ($calorie_plus > $limit_scale){
                        @$array_limit[$id] = $limit_scale * $calorie_single;
                        @$total_test = $total_test - (($limit_scale * $calorie_single)-$calorie);
                        unset($array_not_limit[$id]);



                    }
                    else{
                        unset($array_not_limit[$id]);
                        @$array_not_limit[$id] = $calorie + $distribute;
                        @$total_test -= $distribute;
                    }



                }
            }
            //                    print_r($array_limit);
            //                    // unset($array_not_limit[36]);
            //                    print_r($array_not_limit);

            $count_loop = 1;

            ?>
            <?php

            $_SESSION['total_limit__not'] = array_sum($array_limit) + array_sum($array_not_limit);



            ?>

            <div class="panel-heading ">
                <h4 class="text-center " style="font-family: cursive">Your Needed Amount of Foods</h4>

                <div class="progress">
                    <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?php echo $_SESSION['total_limit__not']; ?>" aria-valuemin="0" aria-valuemax="<?php echo intval($_SESSION['calorie'])?>" style="width:<?php echo ceil(convert_total($_SESSION['calorie'],$_SESSION['total_limit__not'])); ?>%" >
                        <?php echo ceil(convert_total($_SESSION['calorie'],$_SESSION['total_limit__not'])); ?>
                    </div>
                </div>

            </div>
<!--            this is for the showing the items-->
            <div class="panel-body ">







                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Image</th>
                        <th>Calorie</th>
                        <th>amount</th>
                        <th>Scale</th>
                    </tr>

                    </thead>
                    <tbody>

                    <?php
                        foreach ($array_limit as $id => $calorie){
                                $last_query = "SELECT calories,scale,image,name,quantity from food_item WHERE  id = '$id'";
                                $result_ans = $con->query($last_query)->fetch_assoc();

                            ?>

                            <tr>
                                <td><?php echo $result_ans['name'];?></td>
                                <td><img src="image/<?php echo $result_ans['image']; ?>" class="img-responsive" height="100px" width="100px"></td>
                                <td><?php
                                    //$back_total = convert_total($total,$sql_cal_2['calories']);
                                    echo round($calorie,2);//$back_final = intval(convert_needed($back_total,$needed));
                                    ?></td>
                                <!--                        calories are work perfectly-->
                                <td><?php
                                    echo @round(($calorie/$result_ans['calories'] )* $result_ans['quantity'],1);//round(convert_scale($sql_cal_2['calories'],$sql_cal_2['quantity'],$back_final));
                                    ?></td>
                                <td><?php echo  $result_ans['scale'];?></td>
                            </tr>


                    <?php


                        }
                    if(count($array_not_limit) > 0){
                    foreach ($array_not_limit as $id => $calorie) {
                        $last_query = "SELECT calories,scale,image,name,quantity from food_item WHERE  id = '$id'";
                        $result_ans = $con->query($last_query)->fetch_assoc();

                        ?>

                        <tr>
                            <td><?php echo $result_ans['name']; ?></td>
                            <td><img src="image/<?php echo $result_ans['image']; ?>" class="img-responsive"
                                     height="100px" width="100px"></td>
                            <td><?php
                                //$back_total = convert_total($total,$sql_cal_2['calories']);
                                echo round($calorie, 2);//$back_final = intval(convert_needed($back_total,$needed));
                                ?></td>
                            <!--                        calories are work perfectly-->
                            <td><?php
                                echo round(($calorie / $result_ans['calories'])* $result_ans['quantity'], 1);//round(convert_scale($sql_cal_2['calories'],$sql_cal_2['quantity'],$back_final));
                                ?></td>
                            <td><?php echo $result_ans['scale']; ?></td>
                        </tr>
                        <?php
                    }




                    }


                    ?>
                    <tr>
                        <td style="color: red" > Calories Lacking:</td>
                        <td style="color: red">
                            <?php

                            if($total_test<6 && $total_test > 0)
                            {
                                $total_test=0;
                            }

                            echo round($total_test,2);

                            ?></td>

                    </tr>



                </table>
            </div>

        </div>


            </div>
<!--            this is for the end -->



        </div>
            </div>





    </div>
        </div>
</div>
</div>
</body>
</html>
