<?php
    header('Content-Type: text/plain');
    // login data and link info
    $username = 'EMAIL-OF-VERSIO-ACCOUNT';
    $password = 'PASSWORD-OF-VERSIO-ACCOUNT';
// use as https://YOURWEBSITE/downloaddns.php?sld=DOMAINNAMEFOREXPORT
    $sld=htmlspecialchars($_GET["sld"]);
    if ($sld=="") {
        echo "no domain given";
        exit() ;
    }
    $requesttype = 'GET';
    // for each item in $sld or over link with ?sld=DOMAIN
    // this is still one file per DOMAIN
    $endpoint = 'https://www.versio.nl/testapi/v1/domains/'.$sld.'?show_dns_records=true';
    // get DNS records
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_USERNAME, $username );
    curl_setopt($ch, CURLOPT_PASSWORD, $password );
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CRLF, true);
    $result = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    // decoding the array we got from Versio
    $response = json_decode($result, 1);
    $record_type = 'records';
    $i = 0;
    $dns_zone = array();
    $sortedDNSarray= array();
    foreach($response['domainInfo']['dns_records'] as $records)
    {
        $dns_zone[$record_type][$i]['name']     = $records['name'];
        $dns_zone[$record_type][$i]['type']     = $records['type'];
        $dns_zone[$record_type][$i]['value']    = $records['value'];
        $dns_zone[$record_type][$i]['priority'] = $records['prio'];
        $dns_zone[$record_type][$i]['ttl'] = $records['ttl'];
        $recordName=$records['name'];
        $recordValue=$records['value'];
            // check for DKIM record , OVH needs () around it
            if (strpos($recordValue, 'DKIM1') !== false) {
            $recordValue="(".$recordValue.")";
            // export in chunks of 255 characters else you get error on import
            $recordValue= implode("\" \"", str_split($recordValue, 255));
            }
        $recordType=$records['type'];
        $recordTTL=$records['ttl'];
        $recordPrio=$records['prio'];
        // only MX has prio ? anyway if 0 leave out, gave me error on import if not removed
        if ($recordPrio=="0"){
            $recordPrio="";
        } 
        // print out all records in browser window
        echo $recordName." ".$recordTTL." IN ".$recordType." ".$recordPrio." ".$recordValue."\n";
        $i++;
    }
    ?>
