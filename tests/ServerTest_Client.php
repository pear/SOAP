<?php
require_once '../Client.php';

class SOAP_ServerTestClient extends SOAP_Client {
    public function __call($method, $args) {
        #print "call: $method\n";
        #print_r($args);
        $port = $this->wsdl->getPortForOperation($method);
        $this->endpoint = $this->wsdl->getPortEndpoint($port);
        
        /* necessary for result name */
        $operation = $this->wsdl->getOperation($method);
        $opdata = $this->wsdl->getOperationData($operation);
        $output = $opdata->getElementsByTagNameNS(Namespace_Registry::$namespaces['wsdl'],'output');
        $opNS = new QName($output[0]->getAttribute('message'));
        $resultName = $opNS->name;
        
        $url = parse_url($this->endpoint);

        $request = SOAP_Envelope::request($this->wsdl);
        $request->addMethod($method, $args);
        $data = $request->saveXML();
        
        $headers = "User-Agent: PEAR-SOAP 0.7.2-devel\r\n".
            "Content-Type: text/xml; charset={$this->_encoding}\r\n".
            "Content-Length: ".strlen($data)."\r\n".
            "SOAPAction: \"{$request->soapAction}\"\r\n";

        $opts = array(
          $url['scheme'] => array(
            'method' => 'POST',
            'header' => $headers,
            'content' => $data
          )
        );

        #print "Send to {$this->endpoint}\n";
        #print_r($opts);
        
        // XXX using domdocument::load here causes dom to crash
        $result = SOAP_Envelope::response($this->endpoint, $opts);
        return $result->deserializeBody();
        #print $result->saveXML();
    }
}

?>