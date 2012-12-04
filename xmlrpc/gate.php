<?php

// This should be moved to a secure location...
include 'config.inc';

global $gwDebugFile, $gwDbHost, $gwDbUser, $gwDbPass, $gwDbName, $gwDbDriver;

$method = $_SERVER["PATH_INFO"];
$msg = $HTTP_RAW_POST_DATA;

try {
  $xml = new SimpleXMLElement($msg);
} catch (Exception $e) {

  die('<?xml version=\"1.0\" encoding=\"utf-8\"?><boolean>false</boolean>');

}

$item = $xml->item->Guid;
$channel = $xml->channel->Guid;
$uri = $xml->uri;

if ($method == "/RegisterChannel/") {

    if( $GWDEBUG == true ) { 
        $tfile = fopen ($gwDebugFile,"a");
        fwrite($tfile, "[REGISTER]: Starting...\n");
        fwrite($tfile, $msg."\n***************************\n");

	fwrite($tfile, "Script ID: ".$item."\n");
	fwrite($tfile, "Channel: ".$channel."\n");
	fwrite($tfile, "URI: ".$uri."\n");
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

    try {

        $query = "INSERT INTO channels (item, channel, uri)
                    VALUES (:rpc_item, :rpc_channel, :rpc_uri)
                    ON DUPLICATE KEY UPDATE uri = :rpc_uri, channel = :rpc_channel";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':rpc_item', $item);
        $stmt->bindValue(':rpc_channel', $channel);
        $stmt->bindValue(':rpc_uri', $uri);

        $result = $stmt->execute();

        if( $GWDEBUG == true ) {
            fwrite($tfile, "[REGISTER]: Database result:".
		$result."\n");
        }
        
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

    if( $GWDEBUG == true ) { 
        $tfile = fopen ($gwDebugFile,"a");
        fwrite($tfile, "[Unregister]***************************\n");
        fwrite($tfile, $msg."\n***************************\n");
    }

    try {

        $conn = new PDO(
                    $gwDbDriver.":host=".$gwDbHost.";dbname=".$gwDbName, 
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

    if( $GWDEBUG == true ) { 
        fwrite($tfile,'Remove Channel: '.$msg.'\n');
        
        fwrite($tfile,"Channel: ".$channel."\n");
        fwrite($tfile,"Script ID: ".$item."\n");
        fwrite($tfile,"URI: ".$uri."\n");
    }

    try {

        $query = "DELETE FROM channels
                    WHERE channel = :rpc_channel";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':rpc_channel', $channel);

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
