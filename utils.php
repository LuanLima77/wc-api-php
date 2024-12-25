<?php


function startsWith ($string, $startString) 
{ 

    $len = strlen($startString); 

    return (substr($string, 0, $len) === $startString); 

} 




function contains ($string, $keyword) 
{

    $contain = false;

   if (strpos($string, $keyword) !== false) {

       $contain = true;

   }

    return $contain;

} 



 function getPlano($plano)
{
    if(startsWith($plano,"Kit básico"))
    {

        if(contains($plano, '2 livros'))

        {

           return 9;     

        }else

        {
             return 1; 

        }

    }


    if(startsWith($plano,"Kit Básico"))
    {
        if(contains($plano, '2 livros'))

        {

           return 9;     

        }else

        {
             return 1; 

        } 

    }

    if(startsWith($plano,"Kit Standard"))

    {
        return 7;

    }


    if(startsWith($plano,"Kit Extra"))
    {
          if(contains($plano, '2 livros'))

        {
        return 10;

        }else

        {

        return 8;

        }

    }


    if(startsWith($plano,"Kit Premium"))

    {

      return 2;

    }


    if(startsWith($plano,"Kit Grandes Nomes"))

    {

     return 3;

    }

    return 1;


}

 function getCategoria($categoria)
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


 function getQuinzenaAtual()

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