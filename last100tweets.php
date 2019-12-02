<?php
getJsonTweets("%23farina",100);
                    
function getJsonTweets($query,$num_tweets){
    ini_set('display_errors', 1);
    require_once('vendor/TwitterAPIExchange.php');
 
    /** Set access tokens here - see: https://dev.twitter.com/apps/ **/
    $settings = array(
        'oauth_access_token' => "1199721960676167680-z4PujLqBi0zQ3fG9mTzaV06GsIVQrS",
        'oauth_access_token_secret' => "0QLP8GKXIzxGEudyDDsf9xKaa3fSHyKo8sR3UuPl49c6P",
        'consumer_key' => "PfUcGAhRpTChEJ2dslvWZYe1y",
        'consumer_secret' => "waf24VRwbMwWcugdOqNMBHfT87CGJC28yn9APr5oiuHhIub0gL"
    );
        
       
    $url = 'https://api.twitter.com/1.1/search/tweets.json';
    $getfield = '?q='.$query.'&count='.$num_tweets.'&result_type=recent';
    
 
    $requestMethod = 'GET';
    $twitter = new TwitterAPIExchange($settings);
    $json =  $twitter->setGetfield($getfield)
                     ->buildOauth($url, $requestMethod)
                     ->performRequest();

    $json = json_decode($json);
    //obtenemos un array con las filas, es decir con cada tweet.
    $rows = $json->statuses;

    //Iteramos los tweets, extraemos la información y la almacenamos en la base de datos.
    for($i=0;$i<count($rows);$i++){
        $id_tweet = $rows[$i]->id_str;
        $tweet = $rows[$i]->text;
        $rts = $rows[$i]->retweet_count;
        $favs = $rows[$i]->favorite_count;
        $fecha_creacion = $rows[$i]->created_at;
        $usuario = $rows[0]->user->screen_name;
        $url_imagen = $rows[0]->user->profile_image_url;
        $followers = $rows[0]->user->followers_count;
        $following = $rows[0]->user->friends_count;
        $num_tweets = $rows[0]->user->statuses_count; 
                    
        insertarTweetInfo($id_tweet,$tweet,$rts,$favs,$fecha_creacion,$usuario,$url_imagen,$followers,$following,$num_tweets);        
    }          
}

//Método para insertar tweet en la base de datos
function insertarTweetInfo(
    $id_tweet,
    $tweet,
    $rts,
    $favs,
    $fecha_creacion,
    $usuario,
    $url_imagen,
    $followers,
    $followings,
    $num_tweets
    ){

    //Creamos la conexión a la base de datos
    $conexion = mysqli_connect("localhost", "root", "", "twitterdata");
    //Comprobamos laconexión
    if($conexion){
    }else{
        die('Ha sucedido un error inexprerado en la conexion de la base de datos<br>');
    }
    //Codificación de la base de datos en utf8
    mysqli_query ($conexion,"SET NAMES 'utf8'");
    mysqli_set_charset($conexion, "utf8");

    //$tweet = str_replace("'", "", $tweet); 
    $tweet = str_replace("'", '\\\'', $tweet);
    var_dump($tweet);
    //Creamos la sentencia SQL para insertar los datos de entrada
    $sql = "insert into tweets (id_tweet,tweet,rts,favs,fecha_creacion,usuario,url_imagen,followers,followings,num_tweets) 
            values (".$id_tweet.",'".$tweet."',".$rts.",".$favs.",'".$fecha_creacion."','".$usuario."','".$url_imagen."',".$followers.",".$followings.",".$num_tweets.");";
            $consulta = mysqli_query($conexion,$sql);
    //Comprobamos si la consulta ha tenido éxito
    if($consulta){
    }else{
        die("No se ha podido insertar en la base de datos<br><br>".mysqli_error($conexion));
    }

    //Cerramos la conexión de la base de datos
    $close = mysqli_close($conexion);
    if($close){
    }else{
        Die('Ha sucedido un error inexperado en la desconexion de la base de datos<br>');
    }	
}
