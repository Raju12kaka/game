<?php

error_reporting(E_ALL & ~E_NOTICE);
/** Database configuration **/
include ('common.php');
include ('define.php');

/**
 * Class for Getting Blocked Cards data from Avenue pay db and inserting in to local db
 * This file will run for every one minute by using cron setup
 *
 */

class BlockedCards {

    var $root_path;

    function __construct(){
        $this->root_path = dirname(__DIR__).'/logs/cron.log';
    }

    function connection_DB(){
        try {
            //echo 'mysql:host='.MYSQL_HOST.';dbname='.MYSQL_DB, MYSQL_USER, MYSQL_PASS;
            $db = new PDO('mysql:host='.MYSQL_HOST.';dbname='.MYSQL_DB, MYSQL_USER, MYSQL_PASS);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $db;
        } catch(PDOException $ex) {
            return false;
        }

        return false;
    }

    function getBlockedCards(){
        $dbConn = $this->connection_DB();
        if(!$dbConn){
            echo " <div>  DB Connection Error. </div>";
            return false;
        }

        echo " <div> DB Connected Successfully....</div>";


        /**
         * Blocked Cards List API
         *
         * @param  Max id value of CardDetails table
         *
         */

        $SQL = " select id from card_details  order by id desc limit 1 ";
        $PRE = $dbConn->prepare($SQL);
        $PRE->execute();
        $RES = $PRE->fetchObject();

        $id=$RES->id==''?0:$RES->id;

        /******************************
        Submitting parameters by using curl and get returned XML parameters

         ****************************/
        $arr = array(
            'id'		  => $id
        );

        $data =  http_build_query($arr);

        $url  = CALLCARDBLOCKURL;

        //===============================
        $curl = curl_init($url);
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl,CURLOPT_HEADER, 0 ); // Colate HTTP header
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);// Show the output
        curl_setopt($curl,CURLOPT_POST,true); // Transmit datas by using POST
        curl_setopt($curl,CURLOPT_POSTFIELDS,$data);//Transmit datas by using POST
        $jsonrs = curl_exec($curl);
        curl_close ($curl);

        $result = json_decode($jsonrs,TRUE);

        //insert log
        $logmessage  = "/**************************************************************************/"."\n";
        $logmessage .= date('Y-m-d H:i:s')." Get Card Blocked Data : \n";
        $logmessage .= " Response ".$jsonrs."\n";
        $logmessage .= "/**************************************************************************/"."\n";
        file_put_contents($this->root_path, $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);

        //echo " $url Hello Hello $id  <pre>"; print_r($result); echo "</pre>";

        if(count($result)>0){

            foreach($result['success']['rows'] as $val){

                try {

                    $cardnumber=$val['card_num'];
                    $casino_id=$val['casino_id'];
                    $player_id=$val['player_id'];
                    $created_date=$val['created_date'];

                    //echo " <div>  Card Number -$cardnumber , Casino Id - $casino_id , Player id -- $player_id   </div> ";


                    $SQL  = " INSERT INTO card_details(card_number,casino_id,player_id,created_date) values( ENCODE(".$cardnumber.", 'xxfgtmnjidppbmyews@00910426#@$*'), '$casino_id', $player_id, '$created_date' )";
                    $PRE = $dbConn->prepare($SQL);
                    $PRE->execute();

                }catch(PDOException $ex) {
                    return false;
                }

            }
        }


        //insert log
        $logmessage  = "/****************cron end*****************/";
        file_put_contents($this->root_path, $logmessage.PHP_EOL , FILE_APPEND | LOCK_EX);

    }


}

$process = new BlockedCards();
$process->getBlockedCards();

?>
