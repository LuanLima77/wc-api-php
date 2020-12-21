<?php

class L2KC
{

    function __construct() {
        $this->conn = $this->getConnection(true);
    }

    function __destruct()
    { 

        if($this->conn)
        {
            $this->registerCollectSuccessfull();
            mysqli_close($this->conn);

        }
    }
    
    private function registerCollectSuccessfull()
    {
        $SQL = "INSERT INTO coleta_woocommerce(executado)values(true)";


        
        if ($this->conn->query($SQL) === TRUE) 
        {
          echo "Coleta registrada com sucesso na base de dados <br>\n";
        } else 
        {
          echo "Erro ao registrar coleta " . $SQL . "<br>\n" . $this->conn->error;
        }

    }

        
    private function alreadyCollectedAtDate()
    {
        $SQL = "SELECT * FROM coleta_woocommerce WHERE dateExecution > CURDATE()   ORDER BY ID DESC LIMIT 1";

        $query = $this->conn->query($SQL);

        //var_dump($query->num_rows);

        return $query->num_rows;

     
    }
     private function checkIfAlreadyInactiveSubscriber($email)
    {
      $SQL = "SELECT * FROM usuarios u WHERE  u.ativo = 'N' and u.email = '$email'  LIMIT 1 ";
      $query = $this->conn->query($SQL);
      return $query->num_rows;
    }
    
    
    private function getConnection($prd = false)
    {
        if(!$prd)
        {
          $db = "plane321_api_literatour";
          $servername = "localhost:3306/$db";
          $username = "root";
          $password = "";
  
        }else
        {
          $db = "plane321_api_literatour";
          $servername = "216.172.172.230:3306/$db";
          $username = "plane321_admin";
          $password = "Literatour2019";
  
        }
      

        $connection =  new mysqli($servername, $username, $password);

        if ($connection->connect_errno) {
            echo "Falha ao se conectar com o MySQL: " . $mysqli->connect_error;
            exit();
          }
          if (!mysqli_select_db($connection, $db)) {
            die("Não foi possível se conectar ao banco de dados $db <br>");
        }

          return $connection;
        
    }
    
    public function sendToL2KC($user)
    {


      if ($this->alreadyCollectedAtDate()) return;
      
        echo "Iniciando envios de usuários do WooCommerce para a base da API Literatour(L2KC)...<br>";
        $userId = $user->wpId;
        $nome =  $user->nomeCompleto;
        $email = $user->email;
        $idSkoob =   $user->idSkoob ? $user->idSkoob : null;
        $tipoAssinatura = $user->plano;
        $categoriaPrimaria = $user->categoriaPrimaria;
        $categoriaSecundaria =  $user->categoriaSecundaria ? $user->categoriaSecundaria : null;
        $aceita18 = $user->aceita18;
        $cidadeId = $user->cidadeId;
        $quinzena = $user->quinzena;

        $verifySQL = "SELECT * FROM usuarios WHERE email = '$email' ";
        $query = $this->conn->query($verifySQL);

        //Usuario não existe na base
        if (!$query || !$query->num_rows)
        {

          if(!empty($idSkoob))
          {
  
            $SQL = "INSERT INTO usuarios (userId, nome, email, idSkoob, tipoAssinatura, categoriaPrimaria, categoriaSecundaria, aceita18, cidadeId, quinzenaEnvio, obs)
            VALUES ($userId,'$nome','$email', $idSkoob, $tipoAssinatura, '$categoriaPrimaria', '$categoriaSecundaria', $aceita18, $cidadeId,$quinzena,'')";
          }else
          {
            $SQL = "INSERT INTO usuarios (userId, nome, email, tipoAssinatura, categoriaPrimaria, categoriaSecundaria, aceita18, cidadeId, quinzenaEnvio,obs)
            VALUES ($userId,'$nome','$email', $tipoAssinatura, '$categoriaPrimaria', '$categoriaSecundaria', $aceita18, $cidadeId,$quinzena,'')";
          }
  
          
          if ($this->conn->query($SQL) === TRUE) 
          {
            echo "Usuário " . $user->nomeCompleto . " inserido com sucesso na base de dados <br>\n";
          } else 
          {
            echo "Erro ao inserir novo usuário: " . $SQL . "<br>\n" . $this->conn->error;
          }
        }elseif($this->checkIfAlreadyInactiveSubscriber($email))
        {
            $this->updateOnL2KC($user);

          }




        
    
    }

public function updateOnL2KC($user)
{

  $email = $user->email;
  $categoriaPrimaria = $user->categoriaPrimaria;
  $categoriaSecundaria =  $user->categoriaSecundaria ? $user->categoriaSecundaria : null;
  $tipoAssinatura = $user->plano;
  $idSkoob =   $user->idSkoob ? $user->idSkoob : null;
  $SQL = "";

  echo "<br>INICIANDO RENOVACAO USUARIO $email <br>";


if(!isset($idSkoob))
{
  $SQL = "UPDATE usuarios SET statusRenovacao =  0, categoriaPrimaria = '$categoriaPrimaria', categoriaSecundaria = '$categoriaSecundaria', tipoAssinatura = $tipoAssinatura
  WHERE email = '$email' AND statusRenovacao = 1  AND tipoAssinatura = $tipoAssinatura LIMIT 1";
}elseif(is_numeric($idSkoob))
{
  $SQL = "UPDATE usuarios SET statusRenovacao =  0, categoriaPrimaria = '$categoriaPrimaria', categoriaSecundaria = '$categoriaSecundaria', tipoAssinatura = $tipoAssinatura, idSkoob = $idSkoob
  WHERE email = '$email' AND statusRenovacao = 1  AND tipoAssinatura = $tipoAssinatura LIMIT 1";
}


  echo "RENOVACAO USUARIO $email <br>";

  

  if ($this->conn->query($SQL) === TRUE) 
  {
      echo "Usuário " . $user->nomeCompleto . " atualizado com sucesso na base de dados <br>\n";
  } else 
  {
      echo "Erro ao atualizar usuário: " . $SQL . "<br>" . $this->conn->error;
  }

  

}

    public function cancelOnL2KC($user)
    {
        echo "sincronizando cancelamentos de usuários do WooCommerce com a base da API Literatour(L2KC)...<br>\n";
        $email =  $user->email;
      

        $SQL = "UPDATE usuarios SET ativo = 'N' WHERE email = '$email' LIMIT 1";
        //return;

        
        if ($this->conn->query($SQL) === TRUE) 
        {
          echo "Usuário " . $user->nomeCompleto . " sincronizado com sucesso na base de dados <br>\n";
        } else 
        {
          echo "Erro ao cancelar usuário na base da API: " . $SQL . "<br>" . $this->conn->error;
        }

    
    
    }



}
 


