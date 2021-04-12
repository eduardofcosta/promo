<?php
include_once('funcoes.php');

        //Use uma data 40 dias a partir da data da execução. 
        $data = (!empty($_GET['data'])) ? date('Y-m-d', strtotime('40 days', strtotime($_GET['data']))) : date('Y-m-d', strtotime('+40 days'));
   
        // url para acesso a API
        $url_aeroporto = 'http://stub.2xt.com.br/air/airports/pKavbfx6uujWwbbpp3q9Dp7WpMSrDamj';

        echo "Iniciando a importação ..... " ;   

        // busca informação dos aeroportos
        $aeroportos = AcessaAPI($url_aeroporto);
        
        if(empty(consultaTotalRegistros('tb_aeroporto'))){
        
                // salva as informações retornadas da api no banco de dados apenas na primeira vez
                salvaAeroporto($aeroportos);

                echo "<br> Total de registros aeroporto: " .consultaTotalRegistros('tb_aeroporto');

        }

        // combinação airport x airport - 20x20
        GeraCombinacaoVoo($aeroportos, $data);

        echo "<br> Total de registros voo: " .consultaTotalRegistros('tb_voo');        
        echo "<br> Total de registros voo detalhe: " .consultaTotalRegistros('tb_voo_detalhe'); 

?>








