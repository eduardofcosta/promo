<?php
include_once('funcoes.php');
?>

<!doctype html>
<html lang="pt-br">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">
    <style>
    .container{
        margin-top: 50px;
    }
    </style>

    <title>Amo Promo</title>
  </head>
  <body>
    


    <div class="container">

    <h2>Consulta dados</h2>
    <br>

        <?php
        if(empty( consultaTotalRegistros('tb_voo'))){
        ?>
            <div class="alert alert-secondary" role="alert">
            Antes de realizar a consulta, faça a importação dos dados. 
            <br><br>

            <p>Exemplo:</p>
            <ul>
                <li>importadadosapi.php?data=2019-12-01  </li>
                <li>importadadosapi.php (considera data atual) </li>
            </ul>

            </div>                       

        <?php
        }else{
        ?> 

        
            <div class="card">
                <h6 class="card-header">As 30 viagens mais longas, em KM’s, no formato de grid/tabela, contendo a duração e a aeronave de cada uma</h6>
                <div class="card-body">

                <?php

                $sql = "SELECT DISTINCT v.origem, v.destino, v.distancia, d.aeronave, d.tempoVoo FROM tb_voo v, tb_voo_detalhe d WHERE v.voo_id = d.voo_id  ORDER BY 3 DESC LIMIT 30";
                // print_r($sql);

                $result = mysqli_query($link,$sql) or die("<b>Error:</b> ocorreu erro ao acessa o bd<br/>" . mysqli_error($link));

                ?>

                <table class="table table-bordered">
                <thead>
                    <tr>
                    <th scope="col">#</th>
                    <th scope="col">Origem <img src="plane_takeoff_13263.png" width="25" heigth="25px"></th>
                    <th scope="col">Estado</th>
                    <th scope="col">Cidade</th>
                    <th scope="col">Destino <img src="planelanding_avion_13264.png" width="25" heigth="25"></th>
                    <th scope="col">Estado</th>
                    <th scope="col">Cidade</th>
                    <th scope="col">Distancia (KM)</th>
                    <th scope="col">Aeronave</th>
                    <th scope="col">Tempo Voo</th>
                    </tr>
                </thead>
                <?php
                    $cont = 1;
                    while ($row = mysqli_fetch_assoc($result)) {                                                    
                    ?>  
                        <tr>
                            <td><?=$cont++;?></td>	
                            <td><?=$row["origem"];?></td>
                            <td><?=BuscaDadosAeroporto('estado','iata',$row["origem"]);?></td>
                            <td><?=BuscaDadosAeroporto('cidade','iata',$row["origem"]);?></td>
                            <td><?=$row["destino"];?></td>
                            <td><?=BuscaDadosAeroporto('estado','iata',$row["destino"]);?></td>
                            <td><?=BuscaDadosAeroporto('cidade','iata',$row["destino"]);?></td>						
                            <td><?=$row["distancia"] . " km";?></td>
                            <td><?=$row["aeronave"];?></td>
                            <td><?=$row["tempoVoo"];?></td>	                  
                        </tr>
                        <?php
                        }  
                    ?>
                    </tbody>  
                    </table> 
                
                    
                </div>
            </div>
            <br>

            <div class="card">
                <h6 class="card-header">Estado com o maior número de aeroportos</h6>
                <div class="card-body">
                <?=retornaEstadoMaisAeroporto();?>
                </div>
            </div>
            <br>

        
            <div class="card">
                <h6 class="card-header"> Uma tabela com todos os aeroportos de origem e qual o destino mais distante e o mais próximo a ele (com voos disponíveis).</h6>
                <div class="card-body">

                <?php

                    $sql = "SELECT origem,   
                    (SELECT  MIN(distancia) FROM tb_voo b WHERE b.origem = a.origem ) AS menor, 
                    (SELECT  MAX(distancia) FROM tb_voo c WHERE c.origem = a.origem ) AS maior 
                    FROM tb_voo a                    
                    GROUP BY origem;";
                    // print_r($sql);

                    $result = mysqli_query($link,$sql) or die("<b>Error:</b> ocorreu erro ao acessa o bd<br/>" . mysqli_error($link));

                    ?>

                    <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Origem <img src="plane_takeoff_13263.png" width="25" heigth="25"></th>
                            <th scope="col">Estado</th>                           
                            <th scope="col">Destino (Próximo)  <img src="planelanding_avion_13264.png" width="25" heigth="25"> </th>
                            <th scope="col">Estado</th>                           
                            <th scope="col">Distancia (KM)</th>
                            <th scope="col">Destino (Distante)  <img src="planelanding_avion_13264.png" width="25" heigth="25"> </th>
                            <th scope="col">Estado</th>                           
                            <th scope="col">Distancia (KM)</th>                        
                        </tr>
                    </thead>
                    <?php
                        $cont = 1;
                        while ($row = mysqli_fetch_assoc($result)) {                             
                        ?>  
                            <tr>
                                <td><?=$cont++;?></td>	
                                <td><?=$row["origem"];?></td>
                                <td><?=BuscaDadosAeroporto('estado','iata',$row["origem"]);?></td>                              
                                <td><?=buscaDestino($row["origem"],"P");?></td>
                                <td><?=BuscaDadosAeroporto('estado','iata',buscaDestino($row["origem"],"P"));?></td>                               								
                                <td><?=$row["menor"] . " km";?></td>
                                <td><?=buscaDestino($row["origem"],"L");?></td>	
                                <td><?=BuscaDadosAeroporto('estado','iata',buscaDestino($row["origem"],"L"));?></td>                              
                                <td><?=$row["maior"] . " km";?></td>	                  
                            </tr>
                            <?php
                            }  
                        ?>
                        </tbody>  
                        </table> 
                    
                </div>
            </div>
           

        <?php
        }
        ?>  
 
    </div>    

    <!-- Option 1: jQuery and Bootstrap Bundle (includes Popper) -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-Piv4xVNRyMGpqkS2by6br4gNJ7DXjqk09RmUpJ8jgGtD7zP9yug3goQfGII0yAns" crossorigin="anonymous"></script>
   
  </body>
</html>