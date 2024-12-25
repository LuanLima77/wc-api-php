<?php

require_once  "utils.php";

class Usuario


{

    public $wpId;

    public $nomeCompleto;

    public $email;

    public $plano;

    public $planoDetalhado;

    public $cidadeId;

    public $estado;

    public $categoriaPrimaria;

    public $categoriaSecundaria;

    public $aceita18;

    public $idSkoob;

    public $quinzena;

    public $dataNascimento;
    
    public $cupomUsado;



    function __construct($wpId, $nomeCompleto,$email,$plano,$cep,$estado,$aceita18,$categoriaPrimaria,$categoriaSecundaria = null,$idSkoob=null,$dataNascimento=null, $cupomUsado = null) 

    {

        $this->wpId = $wpId;

        $this->nomeCompleto = $nomeCompleto;

        $this->email = $email;

        $this->plano = getPlano($plano);
        
        $this->planoDetalhado = $plano;

        $this->cidadeId = $this->getCodigoIBGE($cep);

        $this->estado = $estado;

        $this->aceita18 = $aceita18 == "aceito" ? 1 : 0;

        $this->categoriaPrimaria = getCategoria($categoriaPrimaria);

        $this->categoriaSecundaria = getCategoria($categoriaSecundaria);

        $this->idSkoob = $idSkoob;

        $this->dataNascimento = $dataNascimento;

        $this->quinzena = getQuinzenaAtual();
        
        $this->cupomUsado = $cupomUsado;

       

    }



    public function getCodigoIBGE($cep)

    {

      //echo "Buscando cidadeID para o CEP $cep <br>\n";

      $response = file_get_contents("http://viacep.com.br/ws/$cep/json/");

      $jsonResponse  = json_decode($response);



      $codigoIbge = null;

      if(!empty($jsonResponse->ibge))

      {

        $codigoIbge = $jsonResponse->ibge;

      }else

      {

          $codigoIbge = 0;

      }

       return $codigoIbge;



    }



}