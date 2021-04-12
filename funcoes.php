<?php

ini_set('max_execution_time', 0);


$link = mysqli_connect("localhost", "root", "", "promo");

if (mysqli_connect_errno()) {
    printf("Falha na conexão: %s\n", mysqli_connect_error());
    exit();
}

// acessa api buscando informações sobre o voo ou aeroporto dependendo da url passada.
function AcessaAPI($url)
{
    $curlHandler = curl_init();

    $userName = 'eduardofc';
    $password = 'sGLvbf';

    curl_setopt_array($curlHandler, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        CURLOPT_USERPWD => $userName . ':' . $password,
    ]);

    $response = curl_exec($curlHandler);
    curl_close($curlHandler);

    // echo "<pre>";
    // print_r(json_decode($response));

    return json_decode($response);
}

// combinação possível airport x airport: 
function GeraCombinacaoVoo($aeroportos, $data){

    $count1 = 1;
    $count2 = 1;

    
    foreach ($aeroportos as $key1 => $aeroporto1) {

        // Para a execução do FOREACH aeroporto1
        if ($count1 > 20) {
            break;
        }

        foreach ($aeroportos as $key2 => $aeroporto2) {

            // Para a execução do FOREACH aeroporto2
            if ($count2 > 10) {
                $count2 = 1;
                break;
            }

            // caso for o msm aeroporto não conta
            if ($key1 == $key2) {
                continue;
            }

            // Chama a API de VOOS passando as duas KEYs
            $url = "http://stub.2xt.com.br/air/search/pKavbfx6uujWwbbpp3q9Dp7WpMSrDamj/{$key1}/{$key2}/{$data}";

            // busca voo passando aeroportos e url api
            $voo = AcessaAPI($url);

            // Pega o valor mais baixo encontrado para essa rota e aeronave que opera a opção mais barata            
            $dadosMenorValor = menorValor($voo->options);
            //print_r($dadosMenorValor);

            $menorValor = $dadosMenorValor->fare_price;
            //print_r($menorValor . "<br/>");

            $modelo = $dadosMenorValor->aircraft->model;
            //print_r($modelo . "<br/>");

            $aeronave = $dadosMenorValor->aircraft->manufacturer;
            //print_r($aeronave . "<br/>");           

            // busca lat e lon - origem
            $latitude_origem  = $voo->summary->from->lat;
            $longitude_origem = $voo->summary->from->lon;

            // busca lat e lon - destino
            $latitude_destino  = $voo->summary->to->lat;
            $longitude_destino = $voo->summary->to->lon;

            
            // calcula a distancia
            $distancia = distanciaPercorida($latitude_origem,$longitude_origem,$latitude_destino,$longitude_destino);
            //print_r ("distancia = " . $distancia . "<br/>");

            $dataVoo = $voo->summary->departure_date;
            //print_r ("dataVoo = " . $dataVoo . "<br/>");
        
            // salva os dados no banco de dados voo
            $idVoo = salvarVoo($key1 , $key2, $distancia, $dataVoo, $menorValor, $modelo, $aeronave, $url );
            //print_r ("<br/> IdVoo = " . $idVoo . "<br/><br/>");
         
            foreach ($voo->options as $voo) {
                
                $aeronave = $voo->aircraft->manufacturer;
                $modelo = $voo->aircraft->model;
                $saida = $voo->departure_time;
                $chegada = $voo->arrival_time;      
                $valor  = $voo->fare_price;
                $custoTarifa = custoTarifa($distancia, $voo->fare_price);
                $tempoVoo = tempoVoo($voo->departure_time,  $voo->arrival_time);
                $minutos = horaParaMinutos(tempoVoo($voo->departure_time,  $voo->arrival_time));
                $velocidade = velocidadeVoo($minutos, $distancia);
                
                // salva os dados no banco de dados voo detalhe
                $idVooDetalhe = salvarVooDetalhe($idVoo, $aeronave, $modelo, $saida, $chegada, $valor, $velocidade, $tempoVoo, $custoTarifa);
                //print_r ("idVooDetalhe = " . $idVooDetalhe . "<br/>");
            } 
           
            $count2++;
        }
        $count1++;
    }
      
}


//salva informação das varias opções de voos para aquela região e data
function salvarVooDetalhe($idVoo, $aeronave, $modelo, $saida, $chegada, $valor, $velocidade, $tempoVoo, $custoTarifa ){

    global $link; 

        $sql = "INSERT INTO tb_voo_detalhe (voo_id, aeronave, modelo, saida, chegada, valor, velocidade, tempoVoo, custoTarifa) VALUE ('".$idVoo."' , '".$aeronave."', '".$modelo."', '".$saida."', '".$chegada."', '".$valor."', '".$velocidade."', '".$tempoVoo."', '".$custoTarifa."')";
        //echo "<br><br>" . $sql;
        if(mysqli_query($link, $sql)){   
            $last_id = mysqli_insert_id($link);
            return $last_id;
        } else{
            return "ERROR: o executar a query $sql. " . mysqli_error($link);
        }
     
        mysqli_close($link);

}



//salva informação dos voos
function salvarVoo($origem , $destino, $distancia, $dataVoo, $menorValor, $modelo, $aeronave, $url ){

    global $link;

        $dataVoo = date('Y-m-d', strtotime($dataVoo));

        $sql = "INSERT INTO tb_voo (origem, destino, distancia, menor_valor, data_voo, modelo, aeronave, url) VALUES ('".$origem."' , '".$destino."', '".$distancia."', '".$menorValor."', '".$dataVoo."', '".$modelo."', '".$aeronave."', '".$url."')";
        //echo "<br><br>" . $sql;
        if(mysqli_query($link, $sql)){   
            $last_id = mysqli_insert_id($link);
            return $last_id;
        } else{
            return "ERROR: o executar a query $sql. " . mysqli_error($link);
        }
     
        mysqli_close($link);

}


// salva dados aeroportos no banco de dados 
function salvaAeroporto($aeroportos)
{
   
        foreach ($aeroportos as $key1 => $aeroporto) {

            $descricao  =  $aeroporto->iata;
            $lat        =  $aeroporto->lat;
            $log        =  $aeroporto->lon;
            $estado     =  $aeroporto->state;
            $cidade     =  $aeroporto->city;

            adicionarAeroporto($descricao , $lat, $log, $estado, $cidade );
        }
        
        return true;
}

// persiste no banco de dados os aeroportos
function adicionarAeroporto($descricao , $lat, $lon, $estado, $cidade ){

    global $link;
       
        $sql = "INSERT INTO tb_aeroporto (iata , lat, log, estado, cidade) VALUES ('".$descricao."' , '".$lat."', '".$lon."', '".$estado."', '".$cidade."')";
        //echo "<br><br>" . $sql;
        if(mysqli_query($link, $sql)){   
            $last_id = mysqli_insert_id($link);
            return $last_id;
        } else{
            return "ERROR: o executar a query $sql. " . mysqli_error($link);
        }
     
        mysqli_close($link);

}


// funcao para montar combo generica
function MontaCombo($pNome = "", $campos="", $tabela="", $pSelected = "", $extra = ""){

	global $link;
	
    $sql = "select distinct $campos from $tabela order by $campos ";
    //print_r($sql);
					
	$result = mysqli_query($link,$sql); 
	
	if (!$result) {
		echo 'Ocorreu um erro ao executar a query: ' . mysql_error();
		exit;
	}
	
    if (mysqli_num_rows($result) > 0) { 	
	
			echo("<SELECT NAME='".$pNome."' ".$extra.">");
			echo("<OPTION VALUE=''");
			echo("></option>");

			//Realiza o loop em todos os elementos do array
			while ($row = mysqli_fetch_array($result, MYSQLI_BOTH)) {	   
				//Imprime a TAG OPTION usando a primeira coluna do array
				echo("<OPTION VALUE='".$row[0]."'");
				//Efetua a comparação para verificar se este é o ítem selecionado
				if( $row[0] == $pSelected ){
					//Caso a comparação seja verdadeira seleciona o ítem
					echo(" SELECTED");
				}
				//Imprime o nome por extenso do estado, equilavente a segunda coluna do array
				echo(">".formataData($row[0])."</option>");
			}
			//Finaliza a tag SELECT
			echo("</SELECT>");
	}		
}

// retorna Estado com mais Aeroporto
function retornaEstadoMaisAeroporto() {

	global $link;
	
    $sql = "SELECT estado, COUNT(*) FROM tb_aeroporto  GROUP BY estado ORDER BY 2 DESC LIMIT 1";
	//print_r($sql);

	$result = mysqli_query($link,$sql);
	$row = mysqli_fetch_array($result, MYSQLI_BOTH); 
	$row_cnt = mysqli_num_rows($result);


	//echo $row_cnt; exit;
	if ($row_cnt > 0){
		return "Estado de : <strong>" . $row[0] . "</strong> com <strong>" . $row[1] . "</strong> aeroportos";
	}else{
		return "Nenhum Aeroporto encontrado.";
	}
	
}

// busca destino para exibição na tabela
function buscaDestino($String, $distancia) {
	
    global $link;
      
    if (!empty($String) || !empty($distancia) ){
        
        $filtro = $distancia == "P" ? "MIN":"MAX";
                    
         $sql = "SELECT destino FROM tb_voo WHERE origem = '".$String."' AND distancia = (SELECT ".$filtro."(distancia) FROM tb_voo b WHERE origem = '".$String."')";
         
         $result = mysqli_query($link,$sql);          
         $row = mysqli_fetch_array($result, MYSQLI_BOTH); 
         return $row[0];
    }else{
        return null;
    }      
      
 }


 // consulta total de registros em tabela
 function BuscaDadosAeroporto($campo, $termo, $filtro) {
	
    global $link;      
                   
     $sql = "SELECT ".$campo." FROM tb_aeroporto where ".$termo." = '".$filtro."'";
     //print_r($sql);
     
     $result = mysqli_query($link,$sql);          
     $row = mysqli_fetch_array($result, MYSQLI_BOTH); 
     return $row[0];
   
  
}


 
// consulta total de registros em tabela
 function consultaTotalRegistros($tabela) {
	
        global $link;      
                       
         $sql = "SELECT count(*) FROM ".$tabela."";
         //print_r($sql);
         
         $result = mysqli_query($link,$sql);          
         $row = mysqli_fetch_array($result, MYSQLI_BOTH); 
         return $row[0];      
      
 }

// formata data dd/mm/yyyy
function formataData($data) {

	if (!empty($data)){
	
		$hora = substr($data,10,6);
		$data = substr($data,0,10);

		if (substr($data, 2, 1) == "/")
			return implode('-', array_reverse(explode('/', $data))).$hora;
		else
			return implode('/', array_reverse(explode('-', $data))).$hora;

	}else{
		return null;
	}	
}





// calcula a velocidade do voo
    function velocidadeVoo($tempo , $distancia )
    {

        if(empty($tempo)  || empty($distancia)){
            throw new Exception('distancia e/ou tarifas estão vazios.');
            return false;
        } else{
            $velocidade = ($distancia * 60) / $tempo; 
            $velocidade = round($velocidade, 0);
            return $velocidade;
        }   
    }


      // converte horas em minutos
    function horaParaMinutos($hora){

        if(empty($hora)){
            throw new Exception('hora estava vazia.');
            return false;
        } else{
            $partes = explode(":", $hora);
            $minutos = $partes[0]*60+$partes[1];
            return ($minutos);
        }       
    }


    // Utilize a distância para obter o custo de tarifa por km voado
    function custoTarifa($distancia , $valorTarifa ){

        if(empty($distancia)  || empty($valorTarifa)){
            throw new Exception('ditancia e/ou tarifa estão vazios.');
            return false;
        } else{
            return $distancia + $valorTarifa;
        }
      
       
   }

   // armazena menor valor entre aeroportos
   function menorValor($voos)
    {
        $aux = [];

        foreach ($voos as $key => $voo) {
        
            if ($key == 0) {
                $aux = $voo;
            }

            if (isset( $voo->fare_price) && ( $voo->fare_price <  $aux->fare_price)) {
                $aux = $voo;
            }
        }    

        return $aux;
    }
        

   // calcula o tempo de voo origem / destino
    function tempoVoo($saida, $chegada)
    {

        $datatime1 = new DateTime($saida);
        $datatime2 = new DateTime($chegada);
        
        $data1  = $datatime1->format('Y-m-d H:i:s');
        $data2  = $datatime2->format('Y-m-d H:i:s');          

        $dateDiff = $datatime1->diff($datatime2);
        $result = $dateDiff->h .":". $dateDiff->i; 

        return $result;

    }

    // calcula a distancia percorrida - Formula de Haversine
    function distanciaPercorida($lat1, $lon1, $lat2, $lon2) {

        $lat1 = deg2rad($lat1);
        $lat2 = deg2rad($lat2);
        $lon1 = deg2rad($lon1);
        $lon2 = deg2rad($lon2);
        
        $distancia = (6371 * acos( cos( $lat1 ) * cos( $lat2 ) * cos( $lon2 - $lon1 ) + sin( $lat1 ) * sin($lat2) ) );
        $distancia = number_format($distancia, 2, '.', '');
        return $distancia;
    }

    ?>