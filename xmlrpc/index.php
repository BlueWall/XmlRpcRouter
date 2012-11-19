<?php

/*
   Copyright (c) 2008-2012, BlueWall Information Technologies, LLC
  
   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.

*/


// move this file to a secure location and adjust path...
include 'config.inc';


define("GWDEBUG", $GWDEBUG);
define('DBHOST', $gwDbHost);
define('DBPASS', $gwDbPass);
define('DBUSER', $gwDbUser);
define('DBNAME', $gwDbName);
define('DBDRIVER', $gwDbDriver);
define('DEBUGFILE', $gwDebugFile);

if( GWDEBUG == true ) {
    $tfile = fopen (DEBUGFILE,"a");
    define ('TFILE', $tfile);
}


function return_error($error) {

    $error_response_xml = xmlrpc_encode(array(
        'success'      => False,
        'errorMessage' => "\n\n$error"));

    print $error_response_xml;

    exit;
}


function get_target_uri($channel) {

    try {

        $conn = new PDO(DBDRIVER.":host=".DBHOST.";
                                dbname=".DBNAME, DBUSER, DBPASS);

    } catch (PDOException $e) {
   
        if( GWDEBUG == true ) {
                fwrite(TFILE, "[GATE]: Database Connection Error: ".
                $e->getMessage()."\n");
        }

        return_error("Database Connection!");
    }

    $target_uri = '';

    try {

    $query = "SELECT uri from channels
                WHERE channel = :rpc_channel";

    $stmt = $conn->prepare($query);
    $stmt->bindValue(':rpc_channel', $channel);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $target_uri = $result['uri'];

    } catch (PDOException $e) {
        
        $message = $e->getMessage();

        if( GWDEBUG == true ) {
           fwrite(TFILE, "[GATE]: Database Error: ".
                  $message()."\n");
        }

        return_error($message);
    }

    if ($target_uri != '')
        return $target_uri;
    else
        return_error("Target Not Found");

}


function llRemoteData($xml, $rpc)
{
    if (!$channel = get_channel($rpc))
        return_error("Channel Error");

    if (!$target_uri = get_target_uri($channel))
        return_error("Target Error");


    $target_host = parse_url($target_uri, PHP_URL_HOST);
    $target_port = parse_url($target_uri, PHP_URL_PORT);

    if( GWDEBUG == true ) {
        fwrite(TFILE, "[GATE]: Working...\n".
                        "HOST: ".$target_host."\n".
		                "PORT: ".$target_port."\n");
    }

    // Can tune these
    $sockx = 5; // Socket timeout
    $strmx = 2; // Stream timeout

    $handle = fsockopen($target_host, $target_port, $errno, $errstr, $sockx);
    if ( !$handle )
    {
        return_error("Cannot connect to host!");

    } else {
        socket_set_blocking($handle, False);
        stream_set_timeout($handle, $strmx);
    }

    $xml = trim($xml, "\r\n");

    $out = "POST / HTTP/1.1\r\n";
    $out .= "Host: ".$target_host."\r\n";
    $out .= "Content-Type: application/xml\r\n";
    $out .= "Content-Length: ".strlen($xml)."\r\n";
    $out .= "Connection: Close\r\n\r\n";
    $out .= $xml;

    if( GWDEBUG == True ) {
        fwrite(TFILE, "[GATE]: Sending:\n".$out."\n");
    }

    fwrite($handle, $out);

    $headers = False;
    while (!feof($handle)) {

        $line = fgets($handle);
        if ($headers == True) {
            if( GWDEBUG == true ) {
                fwrite(TFILE,$line);
            }

            echo $line;

        } else {
            
            if (strstr($line, 'HTTP/1.')) {

                if (strstr($line, '200')) {

                    if( GWDEBUG == true ) {
                        fwrite(TFILE, "RESPONSE STATUS: OK ** ".$line);
                    }

                } else {

                    if( GWDEBUG == true ) {
                        fwrite(TFILE, "RESPONSE STATUS: ERROR\n".$line);
                    }
                   
                    fclose($handle);
                    return_error($line);
                }
            }

            if( GWDEBUG == true ) {
                fwrite(TFILE,$line);
            }
            
            if ($line == "\r\n")
                $headers = True;
        }

        if (strstr($line, '</methodResponse>')) {
            echo "\r\n";
            fclose($handle);
            return;
        }
    }
}


function get_channel($rpc) {

  foreach ($rpc->xpath('//member') as $member) {

    if ($member->name == 'Channel')
      return $member->value->string;

  }

  return false;

}


$xml = $HTTP_RAW_POST_DATA;
$rpc = new SimpleXMLElement($xml);
$funct = $rpc->methodName[0]."";
$funct($xml, $rpc);


?>
