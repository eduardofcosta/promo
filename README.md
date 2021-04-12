<h3>consulta.php</h3>

Exibe as informações:

As 30 viagens mais longas, em KM’s, no formato de grid/tabela, contendo a duração e a aeronave de cada uma;
Qual o estado com o maior número de aeroportos;
Uma tabela com todos os aeroportos de origem e qual o destino mais distante e o mais próximo a ele (com voos disponíveis).

<h3>funcoes.php</h3>

Realiza os Calculos e Persiste os dados no banco: 

A url da API mockup de voos;
A distância entre os dois aeroportos;
O valor mais baixo encontrado para essa rota;
O modelo de aeronave que opera a opção mais barata.

<h3>importadadosapi.php</h3>

Realiza a chama a API ( API mockup de aeroportos domésticos )

Chamada a AP1 realizada poderá ser realizada de 2 formas :

Exemplo:

<ul>
<li>importadadosapi.php?data=2019-12-01</li>
<li>importadadosapi.php (sem informar a data, considera data atual)</li>
</ul>


promo.sql

Script para criação das tabelas (MySql).

