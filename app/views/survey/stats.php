<?php
namespace octopus\app\views\survey;
use octopus\core\Controller;
use octopus\core\Router;
use octopus\core\utils\JSONConvertor;

?>
<script src="<?=Router::root('js/canvasjs.min.js');?>"></script>
<script>
    jQuery.fn.renderPie = function( datas, exportName ){
        var id = $(this).selector;

        var chart = new CanvasJS.Chart(id,
            {
                title:{
                    text: ""
                },
                exportFileName: exportName,
                exportEnabled: true,
                legend:{
                    verticalAlign: "bottom",
                    horizontalAlign: "center"
                },
                data: [
                    {
                        type: "pie",
                        showInLegend: true,
                        toolTipContent: "{legendText}: <strong>{y}%</strong>",
                        indexLabel: "{label} {y}%",
                        dataPoints: datas
                    }
                ]
            });
        chart.render();
    };
    jQuery.fn.renderHist = function( datas, exportName ){
        var id = $(this).selector;

        var chart = new CanvasJS.Chart(id,
            {
                title:{
                    text: ""
                },
                exportFileName: exportName,
                exportEnabled: true,
                data: [

                    {
                        dataPoints: datas
                    }
                ]
            });

        chart.render();
    };
</script>
<?php

function questionNumericView( $data, $sondageTitle ) {
    $stats = $data['stats'];
    $data = $data['question'];
    $values = explode(';', $data->criteres);
    $min = $values[0];
    $max = $values[1];
    $interval = "$min et $max";
    ?>
    <div id="question_<?= $data->orderNum;?>" class="row question" data-order="<?= $data->order;?>">
        <div class="login-form">
            <div class="container-fluid">
                <div class="row">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-11">
                                <p><b><?= $data->orderNum;?></b> <?= $data->text;?></p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group has-error">
                                <div class="row">
                                    <div id="chart<?=$data->orderNum;?>" class="chart" style="height: 300px; width: 100%;"></div>
                                    <script>
                                        <?php
                                            $dataJSON = array();
                                            $delta = ($max - $min) / 10;
                                            for ($i = $min; $i < $max; $i += $delta ) {
                                                $somme = 0;
                                                foreach($stats['votes'] as $k => $v) {
                                                    if ( $i <= $k && $k <= $i + $delta ) {
                                                        $somme += $v;
                                                    }
                                                }
                                                $dataJSON[] = array(
                                                    "x" => $i,
                                                    "y" => $somme,
                                                    "label" => "$i"
                                                );
                                            }

                                        ?>
                                        $("chart<?=$data->orderNum;?>").renderHist(<?=JSONConvertor::JSONToText($dataJSON);?>, "<?="$sondageTitle - Q{$data->orderNum} {$data->text}";?>");
                                    </script>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php }

function questionChoiceView( $data, $sondageTitle ) {
    $stats = $data['stats'];
    $data = $data['question'];
    $answers = explode(';', $data->criteres);
    ?>
    <div id="question_<?= $data->orderNum;?>" class="row question" data-order="<?= $data->order;?>">
        <div class="login-form">
            <div class="container-fluid">
                <div class="row">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-11">
                                <p><b><?= $data->orderNum;?></b> <?= $data->text;?></p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group has-error">

                            </div>
                        </div>
                        <div class="row">
                            <div id="chart<?=$data->orderNum;?>" class="chart" style="height: 300px; width: 100%;"></div>
                            <script>
                                <?php
                                    $dataJSON = array();
                                    foreach( $stats['votes'] as $k => $a ){
                                        $dataJSON[] = array(
                                            "y" => number_format($a * 100 / $stats['total'], 2),
                                            "legendText" => $k,
                                            "label" => $k
                                        );
                                    }
                                ?>
                                $("chart<?=$data->orderNum;?>").renderPie(<?=JSONConvertor::JSONToText($dataJSON);?>, "<?="$sondageTitle - Q{$data->orderNum} {$data->text}";?>");
                            </script>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php }
?>
<div class="row">
    <div class="login">
        <div class="login-screen">
            <div class="login-icon" style="position: fixed; top: 120px;">
                <img src="<?= Router::generate( 'img/icons/svg/mail.svg' );?>" alt="Welcome to Mail App">
                <h4><?= $appname?><small>Statistiques</small></h4>
            </div>

            <p class="lead"><?= $sondageTitle; ?></p>
            <div id="questions" class="container-fluid manage">

                <?php foreach( $questionsStats as $q ){
                    switch( $q['question']->type ) {
                        case 'choice':
                            questionChoiceView( $q, $sondageTitle );
                            break;
                        case 'numeric':
                            questionNumericView( $q, $sondageTitle );
                            break;
                        default:
                            // rien à faire
                    }
                }?>
            </div>
        </div>
    </div>
</div>


<script>
    $(document).ready(function(){
        $(':radio').radiocheck('uncheck');
        $('.input-interval').each(function(){
            $(this).keyup(function(){
                var min = $(this).attr( 'data-min' );
                var max = $(this).attr( 'data-max' );
                var val = parseInt( $(this).val() );
                if ( min <= val && val <= max ) {
                    $(this).parent().removeClass( 'has-error' );
                    $(this).parent().addClass( 'has-success' );
                } else {
                    $(this).parent().addClass( 'has-error' );
                    $(this).parent().removeClass( 'has-success' );
                }
            });
        });
    });
</script>