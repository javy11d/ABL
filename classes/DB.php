<?php 
class DB {

    private static function connect(){
        $pdo = new PDO('mysql:host=abldev.ctmhfyttdbql.us-east-2.rds.amazonaws.com;dbname=socialnetwork;charset=utf8', 'user','');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;

    }

    public static function query($query, $params = array()){
        $statement = self::connect()->prepare($query);
        $statement->execute($params);

        if(explode(' ', $query)[0] == 'SELECT'){
            $data = $statement->fetchAll();
            return $data;
        }

    }
 
} 
?>