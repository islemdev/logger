<?php 
namespace App\Empower\Logger;

class Logger{
    public static function log($input, $output, $method="unknown",$canal="system"){
        try {
            date_default_timezone_set ("Europe/Paris");
            $file_log = storage_path(date("Y-M-d").".log");

            if( !($fp = fopen($file_log, 'a')) ) return; 

            fwrite( $fp, "--- call $method at ".date("Y-M-d H:i:s")." ---\n" ); 
            
            fwrite( $fp, "Request : " . print_r($input, true) . "\n" );
            
            fwrite( $fp, "Response : " . print_r($output, true) . "\n");
            
            fclose($fp);
        } catch (\Exception $e) {
            //echo 'Exception reçue : ',  $e->getMessage(), "\n";
        }
    }
}