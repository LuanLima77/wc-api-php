<?php
class Usuario

{
    public $wpId;
    public $nomeCompleto;
    public $email;
    public $plano;
    public $cidadeId;
    public $estado;
    public $categoriaPrimaria;
    public $categoriaSecundaria;
    public $aceita18;
    public $idSkoob;
    public $quinzena;

    function __construct($wpId, $nomeCompleto,$email,$plano,$cep,$estado,$aceita18,$categoriaPrimaria,$categoriaSecundaria = null,$idSkoob=null) 
    {
        $this->wpId = $wpId;
        $this->nomeCompleto = $nomeCompleto;
        $this->email = $email;
        $this->plano = $this->getPlano($plano);
        $this->cidadeId = $this->getCodigoIBGE($cep);
        $this->estado = $estado;
        $this->aceita18 = $aceita18 == "aceito" ? 1 : 0;
        $this->categoriaPrimaria = $this->getCategoria($categoriaPrimaria);
        $this->categoriaSecundaria = $this->getCategoria($categoriaSecundaria);
        $this->idSkoob = $idSkoob;
        $this->quinzena = $this->getQuinzenaAtual();
       
    }

    public function getQuinzenaAtual()
    {

      if(date("d") > 1 && date("d") < 15)
      {
       // echo "Usuário sendo incluidos na quinzena 1  <br>" ;
        return 2;

      }else
      {
        //echo "Usuário sendo incluidos na quinzena 2<br>";
        return 1; 

      }
    }

    public function getCodigoIBGE($cep)
    {
      echo "Buscando cidadeID para o CEP $cep <br>";
      $response = file_get_contents("http://viacep.com.br/ws/$cep/json/");
      $jsonResponse  = json_decode($response);

      $codigoIbge = null;
      if(!empty($jsonResponse->ibge))
      {
        $codigoIbge = $jsonResponse->ibge;
      }
       return $codigoIbge;


    }

    public function getPlano($plano)
    {
        if($this->startsWith($plano,"Kit Básico")) 
        {
            return 1;

        }else if($this->startsWith($plano,"Kit Standard"))
        {
            return 7;

        }else if($this->startsWith($plano,"Kit Extra"))
        {
          return 8;
        }else if($this->startsWith($plano,"Kit Premium"))
        {
          return 2;

        }else if($this->startsWith($plano,"Kit Grandes Nomes"))
        {
         return 3;

        }
        return null;

    }

    public function getCategoria($categoria)
    {
        switch($categoria)
        {
          case "dark":
            return "2";
          case "romance":
            return "1";   
          case "light":
            return "3";
          case "nerd":
            return "6";
          case "pessoas":
            return "4";
        case "surpresa":
            return 9;    
        }

    }

    public function startsWith ($string, $startString) 
{ 
    $len = strlen($startString); 
    return (substr($string, 0, $len) === $startString); 
} 
  

}