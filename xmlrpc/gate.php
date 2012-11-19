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

// This should be moved to a secure location...
include 'config.inc';

global $gwDebugFile, $gwDbHost, $gwDbUser, $gwDbPass, $gwDbName, $gwDbDriver;

$method = $_SERVER["PATH_INFO"];

if ($method == "/RegisterChannel/") {

    $msg = $HTTP_RAW_POST_DATA;
    $start = strpos($msg, "?>");

    if( $GWDEBUG == true ) { 
        $tfile = fopen ($gwDebugFile,"a");
        fwrite($tfile, "[REGISTER]: Starting...\n");
    }

    if ($start == -1) {

        if( $GWDEBUG == true ) { 
            fwrite($tfile, "[REGISTER]: Invalid Message!\n");
            fclose($tfile);
        }

        die('<?xml version=\"1.0\" encoding=\"utf-8\"?><boolean>false</boolean>');
    }

    try {

        $conn = new PDO( $gwDbDriver.":host=".$gwDbHost.";dbname=".$gwDbName, 
                            $gwDbUser, 
                            $gwDbPass);

    } catch (PDOException $e) {

        if( $GWDEBUG == true ) {

            fwrite($tfile, "[REGISTER]: Database connection error:".
                            $e->getMessage()."\n");
            fclose($tfile);
         }

        die('<?xml version=\"1.0\" encoding=\"utf-8\"?><boolean>false</boolean>');

    }

    $start+=2;
    $msg = substr($msg, $start);
    $parts = split("[<>]", $msg);

    $channel = $parts[6];
    $uri = $parts[12];

    if( $GWDEBUG == true ) { 
        fwrite($tfile,"Channel: ".$channel."\n");
        fwrite($tfile,"URI: ".$uri."\n");
    }

    try {

        $query = "INSERT INTO channels (channel, uri)
                    VALUES (:rpc_channel, :rpc_uri)
                    ON DUPLICATE KEY UPDATE uri = :rpc_uri";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':rpc_channel', $channel);
        $stmt->bindValue(':rpc_uri', $uri);

        $result = $stmt->execute();
        
    } catch (PDOException $e) {

        if( $GWDEBUG == true ) {

            fwrite($tfile, "[REGISTER]: Database error:".
                            $e->getMessage()."\n");
            fclose($tfile);
         }

        die('<?xml version=\"1.0\" encoding=\"utf-8\"?><boolean>false</boolean>');

    }

    if( $GWDEBUG == true ) { 
        fclose($tfile);
    }

    echo "<?xml version=\"1.0\" encoding=\"utf-8\"?><boolean>true</boolean>";
}

if ($method == "/RemoveChannel/") {

    $msg = $HTTP_RAW_POST_DATA;
        $start = strpos($msg, "?>");

    if( $GWDEBUG == true ) { 
        $tfile = fopen ($gwDebugFile,"a");
    }

    if ($start == -1) {

        if( $GWDEBUG == true ) { 
            fwrite($tfile, "[UNREGISTER]: Invalid Message!\n");
            fclose($tfile);
         }

        die('<?xml version=\"1.0\" encoding=\"utf-8\"?><boolean>false</boolean>');
    }


    try {

        $conn = new PDO(
                    $Driver.":host=".$gwDbHost.";dbname=".$gwDbName, 
                    $gwDbUser, 
                    $gwDbPass);

    } catch (PDOException $e) {

        if( $GWDEBUG == true ) {

            fwrite($tfile, "[REGISTER]: Database connection error:".
                            $e->getMessage()."\n");
            fclose($tfile);
         }

        die('<?xml version=\"1.0\" encoding=\"utf-8\"?><boolean>false</boolean>');

    }

    $start+=2;
    $msg = substr($msg, $start);
    $parts = split("[<>]", $msg);

    if( $GWDEBUG == true ) { 
        fwrite($tfile,'Remove Channel: '.$msg.'\n');
        
        fwrite($tfile,"Channel: ".$parts[4]."\n");
        fwrite($tfile,"URI: ".$parts[8]."\n");
    }

    try {

        $query = "DELETE FROM channels
                    WHERE channel = :rpc_channel";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':rpc_channel', $parts[4]);

        $result = $stmt->execute();

    } catch (PDOException $e) {

        if( $GWDEBUG == true ) {

            fwrite($tfile, "[REGISTER]: Database error:".
                            $e->getMessage()."\n");
            fclose($tfile);
         }

        die('<?xml version=\"1.0\" encoding=\"utf-8\"?><boolean>false</boolean>');
    }

    if( $GWDEBUG == true ) { 
        fclose($tfile);
    }

    echo "<?xml version=\"1.0\" encoding=\"utf-8\"?><boolean>true</boolean>";
}

?>
