<?php

namespace osticket;
//include('../admin/includes/modules/FirePHPCore/fb.php');
//include('../admin/includes/modules/httpful/httpful.phar');
//use FirePHP;

class osticket
{
    private $host;
    private $user;
    private $pass;

    private $rest;

    private $staticFolder = './serve/';
    private $cookieFile = "/home/bragu/public_html/innovics/oscookie.txt";


    public function __construct($host = null, $user = null, $pass = null)
    {
        spl_autoload_register(function ($class) {
            @include_once(__DIR__ . '/' . str_replace(__NAMESPACE__ . '\\', '', $class) . '.php');
            if (!class_exists($class)) {
                //throw new \Exception($class . ' class not available');
            }
        });

        $this->user = $user == null ? $this->user : $user;
        $this->pass = $pass == null ? $this->pass : $pass;
        $this->host = $host == null ? $this->host : $host;

        try {
            $this->rest = new osticketRest($this->host);

            // Do login if user and password are passed to the constructor
            if ($user !== null && $pass !== null) {
                return $this->login();
            }
        } catch (\Exception $e) {
            var_dump($e);
            throw $e;
        }
    }

    public function getHost()
    {
        return $this->host;
    }

    public function login($user = null, $pass = null)
    {
        $this->user = $user == null ? $this->user : $user;
        $this->pass = $pass == null ? $this->pass : $pass;


// Generated by curl-to-PHP: http://incarnate.github.io/curl-to-php/
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "http://www.ticket.centre-albert-einstein.com/scp/login.php");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

        $headers = array();
        $headers[] = "Accept-Encoding: gzip, deflate, sdch";
        $headers[] = "Accept-Language: en,de;q=0.8";
        $headers[] = "Upgrade-Insecure-Requests: 1";
        $headers[] = "User-Agent: Mozilla/5.0 (Windows NT 6.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.112 Safari/537.36";
        $headers[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8";
        $headers[] = "Cache-Control: max-age=0";
        $headers[] = "Cookie: __unam=7639673-15a74f91fa1-23affc4b-1; OSTSESSID=v5mco86geo8m4qrip8udvavrk2";
        $headers[] = "Connection: keep-alive";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);

        /* Use internal libxml errors -- turn on in production, off for debugging */
        libxml_use_internal_errors(true);
        /* Createa a new DomDocument object */
        $dom = new \DOMDocument();
        /* Load the HTML */

        $dom->loadHTML($result);

        /* Create a new XPath object */
        $xpath = new \DomXPath($dom);

        $nodes = $dom->getElementsByTagName('input');

        $hidden_inputs = array();
        // 2. loop through elements
        foreach($nodes as $node) {
            if($node->hasAttributes()) {
                foreach($node->attributes as $attribute) {
                    if($attribute->nodeName == 'type' && $attribute->nodeValue == 'hidden') {
                        $hidden_inputs[] = $node;
                    }
                }
            }
        } unset($node);

        // 3. loop through hidden inputs and print HTML
        foreach($hidden_inputs as $node) {
            //echo "<pre>" . htmlspecialchars($dom->saveHTML($node)) . "</pre>";
            print_r($node);
        } unset($node);

        //var_dump($nodes);
        /* Set HTTP response header to plain text for debugging output */
        header("Content-type: text/plain");
        /* Traverse the DOMNodeList object to output each DomNode's nodeValue */
        foreach ($nodes as $i => $node) {
            echo "Node($i): ", $node->nodeValue, "\n";
        }

        try {
            $resp = $this->rest->postRequest(osticketHelper::url("/scp/login.php") . "?&__CSRFToken_=1c936f655480f5d98bfa9d72a91b237507861643&submit=&do=scplogin&userid={$this->user}&passwd={$this->pass}");
            var_dump($resp);
        } catch (\Exception $e) {
            var_dump($e);
            throw $e;
            //return false;
        }
        return true;
    }


    public function getServerInfo()
    {
        try {
            $resp = $this->rest->get(JasperHelper::url("/jasperserver/rest_v2/serverInfo"));
        } catch (\Exception $e) {
            throw $e;
        }
        return new \SimpleXMLElement($resp['body']);
    }


    public function getFolder($resource)
    {
        // You can pass JasperResourceDescriptor objects or a plain uriString to this method
        if ($resource instanceof JasperResourceDescriptor) {
            if ($resource->getWsType() != 'folder') {
                throw new JasperException("resource is not typeof 'folder', ('{$resource->getWsType()}' given) in Jasper::getFolder();");
            }
        } else {
            $resource = new JasperResourceDescriptor($resource);
        }
        try {
            $resp = $this->rest->get(JasperHelper::url("/jasperserver/rest/resources/{$resource->getUriString()}"));
            $list = new \SimpleXMLElement($resp['body']);
        } catch (\Exception $e) {
            throw $e;
        }

        $collection = array();
        foreach ($list->resourceDescriptor as $resource) {
            $descriptor = new JasperResourceDescriptor();
            $collection[] = $descriptor->fromXml($resource);
            $descriptor = null;
        }
        return $collection;
    }


    public function getResourceDescriptor($resource)
    {
        // You can pass JasperResourceDescritpor objects or a plain uriString to this method
        if (!($resource instanceof JasperResourceDescriptor)) {
            $resource = new JasperResourceDescriptor($resource);
        }
        try {
            $resp = $this->rest->get(JasperHelper::url("/jasperserver/rest/resource/{$resource->getUriString()}"));
            $resource = $resource->fromXml(new \SimpleXMLElement($resp['body']));
        } catch (\Exception $e) {
            throw $e;
        }
        return $resource;
    }

    public function getInputControls($report)
    {
        if (!($report instanceof JasperResourceDescriptor)) {
            $report = new JasperResourceDescriptor($report);
        }

        $url = "/jasperserver/rest_v2/reports/{$report->getUriString()}/inputControls/";
        try {
            $resp = $this->rest->get(JasperHelper::url($url));
        } catch (\Exception $e) {
            throw $e;
        }
        return $resp['body'];
    }

    public function search($type, $uri)
    {
        //$url = JasperHelper::url("/jasperserver/rest_v2/resources?type=" . $type . "&folderUri=" . $uri . "&recursive=true");
        try {
            $resp = $this->rest->get(JasperHelper::url("/jasperserver/rest_v2/resources?type=" . $type . "&folderUri=" . $uri . "&recursive=true"));
        } catch (\Exception $e) {
            throw $e;
            //$resp = $e->getMessage();
        }
        return $resp['body'];
    }

    public function getResourceContents($resource)
    {
        // You can pass JasperResourceDescritpor objects or a plain uriString to this method
        if (!($resource instanceof JasperResourceDescriptor)) {
            $resource = new JasperResourceDescriptor($resource);
        }
        try {
            $resp = $this->rest->get(JasperHelper::url("/jasperserver/rest/resource/{$resource->getUriString()}?fileData=true"));
            $content = $resp['body'];
        } catch (\Exception $e) {
            throw $e;
        }
        return $content;
    }

    public function getReport($resource, $format, Array $params = null)
    {
        // You can pass JasperResourceDescriptor objects or a plain uriString to this method
        if ($resource instanceof JasperResourceDescriptor) {
            if ($resource->getWsType() != 'reportUnit') {
                throw new JasperException("resource is not typeof 'reportUnit' ('{$resource->getWsType()}' given) in Jasper::getReport();");
            }
        } else {
            $resource = new JasperResourceDescriptor($resource);
        }
        try {
            $paramStr = '';
            if (is_array($params) && sizeof($params) > 0) {
                $paramStr .= '?';
                foreach ($params as $param => $val) {
                    // Might need to be urlencoded
                    $paramStr .= $param . '=' . $val . '&';
                }
            }
            $resp = $this->rest->get(JasperHelper::url("/jasperserver/rest_v2/reports/{$resource->getUriString()}.{$format}{$paramStr}"));
        } catch (\Exception $e) {
            throw $e;
        }

        // Replace static content URL
        if ($format == 'html') {
            return str_replace('/jasperserver/scripts/jquery/js/', $this->staticFolder, $resp['body']);
        }
        return $resp['body'];
    }

    public function createRequest($data)
    {
        global $toC_Json;
        $response = array('success' => false, 'request' => '', 'msg' => 'creation de la requete');

        $customerSpace = '/reports/' . $data['owner'] . '/' . $data['reports_id'];
        //$report = new JasperReportUnit($customerSpace . '/report_unit');

        $xml = new JasperSimpleXml("<reportExecutionRequest></reportExecutionRequest>");
        $xml->addChild('reportUnitUri', $customerSpace . '/report_unit');
        $xml->addChild('async', 'true');
        $xml->addChild('freshData', 'true');
        $xml->addChild('saveDataSnapshot', 'false');
        $xml->addChild('outputFormat', $data['format']);
        $xml->addChild('interactive', 'true');
        $xml->addChild('ignorePagination', 'false');
        $parameters = new JasperSimpleXml("<parameters></parameters>");
//        fb($_GET, '$_GET', FirePHP::INFO);
//        fb($_POST, '$_POST', FirePHP::INFO);

        if (count($_POST) > 0) {
            foreach ($_POST as $key => $val) {
                if (substr($key, 0, 6) == 'PARAM_') {
                    $cle = substr($key, 6);
                    //fb($cle, '$cle', FirePHP::INFO);
                    $para = $parameters->addChild('reportParameter');
                    $para->addAttribute('name', $cle);
                    $para->addChild('value')->addCData($val);
                }
            }
        } else {
            foreach ($_GET as $key => $val) {
                if (substr($key, 0, 6) == 'PARAM_') {
                    $cle = substr($key, 6);
                    //fb($cle, '$cle', FirePHP::INFO);
                    $para = $parameters->addChild('reportParameter');
                    $para->addAttribute('name', $cle);
                    $para->addChild('value')->addCData($val);
                }
            }
        }

        $xml->addXmlObject($parameters);
        $req = $xml->saveXml(true);

        $res = array();
        foreach ($xml->attributes() as $name => $attr) {
            $res[$name] = $attr;
        }

        $request = \Httpful\Request::post("http://" . REPORT_SERVER . "/jasperserver/rest_v2/reportExecutions")
            ->addOnCurlOption(CURLOPT_COOKIEFILE, $this->cookieFile)
            ->addOnCurlOption(CURLOPT_COOKIEJAR, $this->cookieFile)
            ->authenticateWithBasic(REPORT_USER,REPORT_PASS)
            ->addHeader('accept', 'application/json')
            ->body($req)
            ->sendsXml()
            ->send();

        $requete = $toC_Json->decode($request);

        fb($requete, '$requete', FirePHP::INFO);

        $errorCode = (string)$requete->errorCode;

        if (!empty($errorCode)) {
            $msg = (string)$requete->message;

            $i = 0;

            foreach ($requete->parameters as $param) {
                $msg = $msg . '  ' . $param;
                $i++;
            }

            $response = array('success' => false, 'request' => null, 'msg' => $msg);
        } else {
            $response = array('succes' => true, 'request' => $toC_Json->decode($request), 'msg' => '1');
        }

        return $response;
    }

    public function createRequestJob($data)
    {
        global $toC_Json;
        $response = array('success' => false, 'request' => '', 'msg' => 'creation de la requete');

        $customerSpace = '/reports/' . $data['owner'] . '/' . $data['reports_id'];
        //$report = new JasperReportUnit($customerSpace . '/report_unit');

        $xml = new JasperSimpleXml("<reportExecutionRequest></reportExecutionRequest>");
        $xml->addChild('reportUnitUri', $customerSpace . '/report_unit');
        $xml->addChild('async', 'true');
        $xml->addChild('freshData', 'true');
        $xml->addChild('saveDataSnapshot', 'false');
        $xml->addChild('outputFormat', $data['format']);
        $xml->addChild('interactive', 'true');
        $xml->addChild('ignorePagination', 'false');
        $parameters = new JasperSimpleXml("<parameters></parameters>");
//        fb($_GET, '$_GET', FirePHP::INFO);
//        fb($_POST, '$_POST', FirePHP::INFO);

        $params = explode("&", $data['query']);

        foreach ($params as $param) {
            $parameter = explode("=",$param);
            $key = $parameter[0];
            $val = $parameter[1];
            if (substr($key, 0, 6) == 'PARAM_') {
                $cle = substr($key, 6);
                //fb($cle, '$cle', FirePHP::INFO);
                $para = $parameters->addChild('reportParameter');
                $para->addAttribute('name', rawurldecode($cle));
                $para->addChild('value')->addCData(rawurldecode($val));
            }
        }

        if (count($_POST) > 0) {
            foreach ($_POST as $key => $val) {
                if (substr($key, 0, 6) == 'PARAM_') {
                    $cle = substr($key, 6);
                    //fb($cle, '$cle', FirePHP::INFO);
                    $para = $parameters->addChild('reportParameter');
                    $para->addAttribute('name', $cle);
                    $para->addChild('value')->addCData($val);
                }
            }
        } else {
            foreach ($_GET as $key => $val) {
                if (substr($key, 0, 6) == 'PARAM_') {
                    $cle = substr($key, 6);
                    //fb($cle, '$cle', FirePHP::INFO);
                    $para = $parameters->addChild('reportParameter');
                    $para->addAttribute('name', $cle);
                    $para->addChild('value')->addCData($val);
                }
            }
        }

        $xml->addXmlObject($parameters);

        //fb($xml, '$xml', FirePHP::INFO);

        $req = $xml->saveXml(true);

        fb($req, '$req', FirePHP::INFO);

        $res = array();
        foreach ($xml->attributes() as $name => $attr) {
            $res[$name] = $attr;
        }

        //echo 'before reportExecutions';
        $request = \Httpful\Request::post("http://" . REPORT_SERVER . "/jasperserver/rest_v2/reportExecutions")
            ->addOnCurlOption(CURLOPT_COOKIEFILE, $this->cookieFile)
            ->addOnCurlOption(CURLOPT_COOKIEJAR, $this->cookieFile)
            ->addOnCurlOption(CURLOPT_TIMEOUT, 50000)
            ->addOnCurlOption(CURLOPT_CONNECTTIMEOUT, 0)
            ->authenticateWithBasic(REPORT_USER,REPORT_PASS)
            ->addHeader('accept', 'application/xml')
            ->body($req)
            ->sendsXml()
            ->send();

        $requete = new \SimpleXMLElement($request);

        $errorCode = (string)$requete->errorCode;

        if (!empty($errorCode)) {
            $msg = (string)$requete->message;

            $i = 0;

            foreach ($requete->parameters as $param) {
                $msg = $msg . '  ' . $param;
                $i++;
            }

            $response = array('success' => false, 'request' => null, 'msg' => $msg);
        } else {
            $response = array('succes' => true, 'request' => $request, 'msg' => '1');
        }

        return $response;
    }

    public function createSchedule($data,$subscriptions_id,$schedule,$start,$end,$query)
    {
        global $toC_Json;
        $state = empty($data['state']) ? 'ENABLED' : $data['state'];

        $response = array('success' => false, 'request' => '', 'msg' => 'creation de la tache');
        $nick = $_REQUEST['content_name'] . "_" . $subscriptions_id;

        $req = "{" .
            "\"jobClass\": \"com.carfey.ops.job.script.ScriptFileJob\"," .
            "\"nickname\": \"$nick\"," .
            "\"pickupBufferMinutes\": 30," .
            "\"recoveryType\": \"LAST\"," .
            "\"state\":  \"$state\"," .
            "\"schedule\": \"$schedule\"," .
            "\"effectiveDate\": \"$start\"," .
            "\"endDate\": \"$end\"," .
            "\"hosts\": []," .
            "\"minExecutionDuration\": \"0s\"," .
            "\"maxExecutionDuration\": \"10h\"," .
            "\"chainAll\" : false," .
            "\"autoRetryCount\": 10," .
            "\"autoRetryInterval\": 5," .
            "\"autoRetryIntervalExponent\": true," .
            "\"customCalendarId\": null," .
            "\"parameters\" : [ {" .
            "\"name\" : \"Copy Obsidian Process' Environment\"," .
            "\"type\" : \"BOOLEAN\"," .
            "\"allowMultiple\" : false," .
            "\"required\" : true," .
            "\"value\" : \"true\"," .
            "\"defaultValue\" : \"true\"," .
            "\"defined\" : true" .
            "},{" .
            "\"name\" : \"Script With Arguments\"," .
            "\"type\" : \"STRING\"," .
            "\"allowMultiple\" : true," .
            "\"required\" : true," .
            "\"value\" : \"curl\"," .
            "\"defined\" : true" .
            "},{" .
            "\"name\" : \"Script With Arguments\"," .
            "\"type\" : \"STRING\"," .
            "\"allowMultiple\" : true," .
            "\"required\" : true," .
            "\"value\" : \"" . HTTP_SERVER . HTTP_COOKIE_PATH . "admin/json.php\"," .
            "\"defined\" : true" .
            "},{" .
            "\"name\" : \"Script With Arguments\"," .
            "\"type\" : \"STRING\"," .
            "\"allowMultiple\" : true," .
            "\"required\" : true," .
            "\"value\" : \"--data\"," .
            "\"defined\" : true" .
            "},{" .
            "\"name\" : \"Script With Arguments\"," .
            "\"type\" : \"STRING\"," .
            "\"allowMultiple\" : true," .
            "\"required\" : true," .
            "\"value\" : \"$query\"," .
            "\"defined\" : true" .
            "},{" .
            "\"name\" : \"Success Exit Code\"," .
            "\"type\" : \"INTEGER\"," .
            "\"allowMultiple\" : false," .
            "\"required\" : false," .
            "\"value\" : \"0\"," .
            "\"defaultValue\" : \"0\"," .
            "\"defined\" : true" .
            " }, {" .
            "\"name\" : \"Working Directory\"," .
            "\"type\" : \"STRING\"," .
            "\"allowMultiple\" : false," .
            "\"required\" : true," .
            "\"value\" : \"/home/guyfomi\"," .
            "\"defined\" : true" .
            "} ]" .
            "}";

        //fb($req, '$req', FirePHP::INFO);

        $ch = curl_init('http://' . JOB_SERVER . '/obsidian/rest/jobs');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_USERPWD, JOB_USER . ":" . JOB_PASS);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
        //curl_setopt($ch, CURLOPT_NOBODY , 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($req))
        );

        $request = curl_exec($ch);

        $requete = $toC_Json->decode($request);
        fb($requete, '$requete', FirePHP::INFO);
        $errors = $requete->errors;

        //fb($errors, '$errors', FirePHP::INFO);
        $count = count($errors);

        if ($count > 0) {
            $i = 0;
            $msg = "";

            while($i < $count)
            {
                $msg = $msg . " " . $errors[$i];
                $i++;
            }

            $response = array('success' => false, 'msg' => $msg);
        } else {
            $job = $requete->job;
            $response = array('succes' => true, 'msg' => '1','feedback' => 'Tache planifiee avec succes','jobId'=> $job->jobId,'schedule'=>$schedule,'date_debut'=>$data['start_date'] . " " . $data['start_time'],'date_fin'=>$data['end_date'] . " " . $data['end_time']);
        }

        return $response;
    }

    public function getDetail($id)
    {
        $ch = curl_init('http://' . JOB_SERVER . '/obsidian/rest/jobs/' . $id);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_USERPWD,JOB_USER . ":" . JOB_PASS);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $request = curl_exec($ch);
        return $request;
    }

    public function deleteJob($job_id)
    {
        $ch = curl_init('http://' . JOB_SERVER . '/obsidian/rest/jobs/' . $job_id . '?cascade=true');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_USERPWD, JOB_USER . ":" . JOB_PASS);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $request = curl_exec($ch);
        return $request;
    }

    public function getStatus($id, $accept)
    {
        $response = \Httpful\Request::get("http://" . REPORT_SERVER . "/jasperserver/rest_v2/reportExecutions/" . $id)
            ->addOnCurlOption(CURLOPT_COOKIEFILE, $this->cookieFile)
            ->addOnCurlOption(CURLOPT_COOKIEJAR, $this->cookieFile)
            ->addHeader('accept', 'application/' . $accept)
            ->authenticateWithBasic(REPORT_USER,REPORT_PASS)
            ->send();
        return $response;
    }

    public function download($job)
    {
        $response = \Httpful\Request::get("http://" . REPORT_SERVER . "/jasperserver/rest_v2/reportExecutions/" . $job['id'] . "/exports/" . $job['format'] . "/outputResource")
            ->addOnCurlOption(CURLOPT_COOKIEFILE, $this->cookieFile)
            ->addOnCurlOption(CURLOPT_COOKIEJAR, $this->cookieFile)
            ->authenticateWithBasic(REPORT_USER,REPORT_PASS)
            ->send();
        return $response;
    }

    public function createFolder($resource)
    {
        // You can pass JasperResourceDescriptor objects or a plain uriString to this method
        if (!($resource instanceof JasperResourceDescriptor)) {
            $resource = new JasperFolder($resource);
        } else {
            if ($resource->getWsType() != 'folder') {
                $resp = "resource is not typeof 'folder' ('{$resource->getWsType()}' given) in Jasper::createFolder();";
                //throw new JasperException("resource is not typeof 'folder' ('{$resource->getWsType()}' given) in Jasper::createFolder();");
            }
        }
        $resource->setPropHasData('false');
        try {
            $resp = $this->rest->put(JasperHelper::url("/jasperserver/rest/resource/{$resource->getUriString()}"), $resource->getXml());
        } catch (\Exception $e) {
            $resp = $e->getMessage();
            //throw $e;
        }
        return $resp;
    }


    public function createDepartment($data)
    {
        try {
            $resp = $this->rest->postRequest(osticketHelper::url("/scp/departments.php?a=add"),"do=create&a=add&id=&pid=&name=" . urlencode($data['customers_surname']) . "&ispublic=1&sla_id=0&manager_id=0&assign_members_only=on&email_id=0&tpl_id=0&autoresp_email_id=0&group_membership=1&signature=&submit=Create+Dept");
        } catch (\Exception $e) {
            var_dump($e);
            throw $e;
            //$resp = $e;
        }
        return $resp;
    }


    public function createContent(JasperResourceDescriptor $resource, $content)
    {
        $resource->setPropHasData('true');
        try {
            $resp = $this->rest->multiput(JasperHelper::url("/jasperserver/rest/resource/{$resource->getUriString()}"), $resource->getXml(), $resource->getUriString(), $content);
        } catch (\Exception $e) {
            $resp = $e;
        }
        return $resp;
    }


    public function deleteResource($resource)
    {
        // You can pass JasperResourceDescriptor objects or a plain uriString to this method
        if (!($resource instanceof JasperResourceDescriptor)) {
            $resource = new JasperResourceDescriptor($resource);
        }
        if (($resource->getUriString() == '/'
            || $resource->getUriString() == '/reports'
            || $resource->getUriString() == '/reports/')
            && $this->customerMode === true
        ) {
            throw new JasperException("cannot delete root folder {JasperHelper::url($resource->getUriString())}");
        }
        try {
            $resp = $this->rest->delete(JasperHelper::url("/jasperserver/rest/resource/{$resource->getUriString()}"));
        } catch (\ Exception $e) {
            //throw $e;
            return false;
        }
        return true;
    }
}

