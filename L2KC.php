<?php


class L2KC

{


    function __construct() {

        $this->conn = $this->getConnection(false);

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


        return $query->num_rows;

    }

     

    private function wpIdExistsOnDatabase($wpId)
    {

      $SQL = "SELECT * FROM assinaturas  a WHERE a.wp_id_novo = $wpId ";

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

          $db = "u555412982_api_literatour";

          $servername = "localhost";

          $username = "u555412982_api_literatour";

          $password = "Seila123";
          

        }


        $connection =  new mysqli($servername, $username, $password, $db);


        if ($connection->connect_errno) {

            echo "Falha ao se conectar com o MySQL: " . $mysqli->connect_error;

            exit();

          }

          if (!mysqli_select_db($connection, $db)) {

            die("Não foi possível se conectar ao banco de dados $db <br>");

        }

          return $connection;

    }


    public function sendSubToL2KC($subscription,$email)
    {
  
      if ($this->alreadyCollectedAtDate()) return;
    

      $wpIdSubscription = $subscription->wpIdSubscription;
        //sem uso atualmente
      //$CustomerId = $subscription->CustomerId;

      $PrimaryCategory = $subscription->PrimaryCategory;

      $quinzenaEnvio = $subscription->quinzena;

      $SecondaryCategory = $subscription->SecondaryCategory;

      $SubscriptionType = $subscription->SubscriptionType;

      if(is_null($SecondaryCategory)){

        $SecondaryCategory = $PrimaryCategory;

      }

    $userId = 0;

    $getUserIdSQL = "SELECT id FROM usuarios WHERE email = '$email'";
    //talvez aqui verificar duplicata(Aparentemente não está dando problema, ja que o bug é pior a partir de 2 renovacoes)

    $query = $this->conn->query($getUserIdSQL);

    while ($row = $query->fetch_assoc()){

      $user_id = $row['id'];

    }

    
      $avoidDuplicatesSQL = "SELECT * FROM assinaturas WHERE (wp_id > 0 AND wp_id = '$wpIdSubscription')  OR wp_id_novo = '$wpIdSubscription' ";

      $query2 = $this->conn->query($avoidDuplicatesSQL);



      if(!$query2->num_rows > 0)
      {

        //Estudar restricao desse IF
            $SQL = "INSERT INTO assinaturas (wp_id_novo, dataInicio, idUsuario, categoriaPrimaria, categoriaSecundaria, tipoAssinatura, quinzenaEnvio, ativo, pendencia) VALUES ('$wpIdSubscription',CURDATE(),$user_id,$PrimaryCategory,$SecondaryCategory,$SubscriptionType,$quinzenaEnvio,1,1)";            

          if ($this->conn->query($SQL) === TRUE)

          {

            echo "Assinatura " . $wpIdSubscription . " inserida com sucesso na base de dados <br>\n";

          } else

          {

            echo "Erro ao inserir nova assinatura: " . $SQL . "<br>\n" . $this->conn->error;

          }
          
      }else{

        echo 'Erro ao cadastrar assinatura'. $wpIdSubscription.' Usuário já possui <b>'.$query2->num_rows.'<b> Assinaturas com este wp_id!';


      }

      
    }


    public function sendToL2KC($user)

    {

      if ($this->alreadyCollectedAtDate()) return;

        echo "Iniciando envios de usuários do WooCommerce para a base da API Literatour(L2KC)...<br>\n";

        $userId = $user->wpId;

        $nome =  $user->nomeCompleto;

        $email = $user->email;

        $idSkoob =   $user->idSkoob ? $user->idSkoob : 0;

        $dataNascimento =   $user->dataNascimento ? $user->dataNascimento : 0 ;

        $tipoAssinatura = $user->plano;

        $categoriaPrimaria = $user->categoriaPrimaria;

        $categoriaSecundaria =  $user->categoriaSecundaria ? $user->categoriaSecundaria : null;

        $aceita18 = $user->aceita18;

        $cidadeId = $user->cidadeId;

        $quinzena = $user->quinzena;
        
        $obs = $user->cupomUsado;


        $verifySQL = "SELECT * FROM usuarios WHERE email = '$email' ";

        $query = $this->conn->query($verifySQL);

      
        //Usuario não existe na base

        if (!$query || !$query->num_rows)

        {


            $SQL = "INSERT INTO usuarios (userId, nome, email, idSkoob, dataNascimento, tipoAssinatura, categoriaPrimaria, categoriaSecundaria, aceita18, cidadeId, quinzenaEnvio, obs,	dataUltimaAtualizacao, ativo, statusRenovacao)

            VALUES ($userId,'$nome','$email', $idSkoob, '$dataNascimento', $tipoAssinatura, '$categoriaPrimaria', '$categoriaSecundaria', $aceita18, $cidadeId,$quinzena,'$obs',  CURDATE(), 'S', 0)";



        
          if ($this->conn->query($SQL) === TRUE)

          {

            echo "Usuário " . $user->nomeCompleto . "(cupom " . $obs . " )inserido com sucesso <br>\n";
            $this->collectOrder($user);

          } else

          {

            echo "Erro ao inserir novo usuário: " . $SQL . "<br>\n" . $this->conn->error;

          }


        }else

        {
          
          echo "Cliente antigo  " . $user->nomeCompleto . "fez um novo pedido pelo site <br>\n";

            $this->updateOnL2KC($user,true);

          }
            


    }
    
    
     public function collectOrder($user)
    {

      if ($this->alreadyCollectedAtDate()) return;

        echo "Iniciando envios de pedidos do WooCommerce para a base da API Literatour(L2KC)...<br>\n";


        $email = $user->email;

        $tipoAssinatura = $user->plano;

        $verifySQL = "SELECT id FROM usuarios WHERE email = '$email' ";

        $query = $this->conn->query($verifySQL);




        if ($query && $query->num_rows)

        {
           $row = $query->fetch_assoc();
             $userId = $row['id'];
             $dataExpiracao = (strpos($user->planoDetalhado, 'Anual') !== FALSE) ? date('Y-m-d', strtotime('+1 year')) : null;

             if(empty($dataExpiracao))
             {
              $SQL = "INSERT INTO pedidos (id_usuario, tipo_caixa, atendido)

              VALUES ($userId, $tipoAssinatura, 0)";
             }else
             { $SQL = "INSERT INTO pedidos (id_usuario, tipo_caixa, atendido,data_expiracao)

              VALUES ($userId, $tipoAssinatura, 0,' $dataExpiracao' )";

              echo "Assinante do anual sendo enviado para a base da API Literatour(L2KC) com data de expiração $dataExpiracao.<br>\n";


             }


        
          if ($this->conn->query($SQL) === TRUE)

          {

            echo "Pedido do cliente " . $user->nomeCompleto ." registrado  com sucesso <br>\n";

          } else
          {

            echo "Erro ao registrar pedido de  usuário: " . $SQL . "<br>\n" . $this->conn->error;

          }


        }else
        {
          echo "[ERRO] Não foi possível localizar usuário do cliente " . $user->nomeCompleto  ." com o email $email   <br>\n";

        }

    }
    
    
    
// Método para verificar pedidos com data de cadastro igual ao dia atual e criar novos pedidos se necessário
function verificaPedidosAnuais() {

  if ($this->alreadyCollectedAtDate()) return;

  $hoje = date('Y-m-d');
  $diaHoje = date('d');

  echo "Verificando assinaturais anuais com aniversário dia $diaHoje ...<br>\n";


  $query = "SELECT * FROM pedidos WHERE DAY(data_cadastro) = $diaHoje  AND data_expiracao > NOW()";
  
  $result = $this->conn->query($query);

  while ($pedido = $result->fetch_assoc()) {
      if ($pedido['data_expiracao'] >= $hoje) {
          $novoPedidoQuery = "INSERT INTO pedidos (id_usuario, tipo_caixa, data_cadastro)
                              VALUES (" . $pedido['id_usuario'] . ", " . $pedido['tipo_caixa'] . " ,NOW())";


                    if ($this->conn->query($novoPedidoQuery) === TRUE)
                    {
                      echo "Novo pedido criado para assinante do anual com id de usuário " . $pedido['id_usuario'] .  "<br>\n";

                    }else
                    {
                      echo "[ERRO] Não foi possível gerar o pedido do mês para assinatura anual de usuário com ID " . $pedido['id_usuario']   ."   <br>\n";

                    }


      }
  }
}
    
    
    

/**
 * TODO: Alterar chamadas de wp_id_novo para wp_id quando possivel, ou seja, a maioria dos assinantes tiver o valor do wp_id neste campo
 */

public function updateSubOnL2KC($subscription,$email,$updateCategories = false)
{
    

  $wpIdSubscription = $subscription->wpIdSubscription;

  $PrimaryCategory = $subscription->PrimaryCategory;

  $quinzenaEnvio = $subscription->quinzena;

  $SecondaryCategory = $subscription->SecondaryCategory;

  $SubscriptionType = $subscription->SubscriptionType;


    if(is_null($SecondaryCategory)){

      $SecondaryCategory = $PrimaryCategory;

    }

  $SQL = "";


    $WHERE = " WHERE (u.email = '$email') AND u.id = a.idUsuario and a.ativo = 1 ";

    //Caso o wpIdSubscription já tenha sido coletado anteriormente, use o como chave para atualizar a assinatura
    if(!empty($wpIdSubscription) && $this->wpIdExistsOnDatabase($wpIdSubscription))
    
    {
      $WHERE = "WHERE a.wp_id_novo = '$wpIdSubscription' ";

    }
    
  //TODO: Remover esse codigo fixo(ESTAVA ATIVO EM PRODUCAO)

    //$WHERE = " WHERE (u.email = '$email') AND u.id = a.idUsuario ";


  if($updateCategories)

  {    echo "<br>Iniciando renovacao da assinaatura do usuario com $email...(updateSubOnL2KC) <br>\n";


    $SQL = "UPDATE `assinaturas` AS a INNER JOIN usuarios u ON u.id = a.idusuario 

    SET a.categoriaPrimaria= $PrimaryCategory, a.categoriaSecundaria= $SecondaryCategory,a.tipoAssinatura= $SubscriptionType,a.ativo= 1, a.pendencia = 0, a.dataUltimaAtualizacao= CURDATE()  $WHERE ";
    

  }else{

    $SQL = "UPDATE `assinaturas` AS a INNER JOIN usuarios u ON u.id = a.idusuario

    SET a.tipoAssinatura= $SubscriptionType,a.ativo= 1, a.pendencia = 0, a.dataUltimaAtualizacao= CURDATE()  $WHERE ";
    

    
  }

    echo "<br>INICIANDO RENOVACAO ASSINATURA DO USUARIO COM O EMAIL $email...(updateSubOnL2KC) <br>\n";
    //echo "QUERY EXECUTADA -> <br> \n $SQL <br> <\n";

 
  if ($this->conn->query($SQL) === TRUE)

  {

      echo "Assinatura " . $wpIdSubscription . " atualizada com sucesso na base de dados <br>\n";

  } else

  {

      echo "Erro ao atualizar usuário: " . $SQL . "<br>" . $this->conn->error;

  }
  

}


public function updateOnL2KC($user, $updateCategories = false)

{

  $email = $user->email;

  $categoriaPrimaria = $user->categoriaPrimaria;

  $categoriaSecundaria =  $user->categoriaSecundaria ? $user->categoriaSecundaria : null;

  $tipoAssinatura = $user->plano;

  $idSkoob =   $user->idSkoob ? $user->idSkoob : 0;

  $dataNascimento = $user->dataNascimento ? $user->dataNascimento : 0;

  $SQL = "";



if($updateCategories)

{
  
    $SQL = "UPDATE usuarios SET statusRenovacao =  0, ativo = 'S', categoriaPrimaria = '$categoriaPrimaria', dataNascimento = '$dataNascimento', idSkoob = $idSkoob,  categoriaSecundaria = '$categoriaSecundaria', tipoAssinatura = $tipoAssinatura, dataUltimaAtualizacao = CURDATE()

    WHERE email = '$email' AND statusRenovacao = 1   ";
 
}else

{

    $SQL = "UPDATE usuarios SET statusRenovacao =  0, ativo = 'S', tipoAssinatura = $tipoAssinatura, dataNascimento = '$dataNascimento', idSkoob = $idSkoob,  dataUltimaAtualizacao = CURDATE()

    WHERE email = '$email' AND statusRenovacao = 1  AND tipoAssinatura = $tipoAssinatura ";    

}


  if ($this->conn->query($SQL) === TRUE)

  {

      echo "Usuário " . $user->nomeCompleto . " atualizado com sucesso na base de dados <br>\n";
      $this->collectOrder($user);

  } else

  {

      echo "Erro ao atualizar usuário: " . $SQL . "<br>" . $this->conn->error;

  }



}



    public function cancelOnL2KC($user)

    {

        echo "sincronizando cancelamentos de usuários do WooCommerce com a base da API Literatour(L2KC)...<br>\n";

        $email =  $user->email;





        $SQL = "UPDATE usuarios SET ativo = 'N', dataUltimaAtualizacao = CURDATE() WHERE email = '$email' ";



        if ($this->conn->query($SQL) === TRUE)

        {

          echo "Usuário " . $user->nomeCompleto . " sincronizado com sucesso na base de dados <br>\n";

        } else

        {

          echo "Erro ao cancelar usuário na base da API: " . $SQL . "<br>" . $this->conn->error;

        }



    }




}