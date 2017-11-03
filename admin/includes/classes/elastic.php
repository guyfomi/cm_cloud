<?php

include('includes/modules/httpful/httpful.phar');

class osC_Elastic_Admin
{
    function getParameterDocument($parameter)
    {
        $doc = array(
            'eventdate' => $parameter['eventdate'] . ".000000",
            'constspeedhz' => $parameter['constspeedhz'],
            'dopspeedchannel' => $parameter['dopspeedchannel'],
            'rotdirection' => $parameter['rotdirection'],
            'pretriglength' => $parameter['pretriglength'],
            'recordlength' => $parameter['recordlength'],
            'statuschangeevent' => $parameter['statuschangeevent'],
            'oschangeevent' => $parameter['oschangeevent'],
            'cyclictime' => $parameter['cyclictime'],
            'cyclictimebyal' => $parameter['cyclictimebyal'],
            'dailytrigtime_hh' => $parameter['dailytrigtime_hh'],
            'dailytrigtime_ss' => $parameter['dailytrigtime_ss'],
            'plant' => $parameter['plant'],
            'line' => $parameter['line'],
            'asset' => $parameter['asset'],
            'cpms_ip' => $parameter['cpms_ip'],
            'configurator' => $parameter['configurator'],
            'monitoring' => $parameter['monitoring'],
            'monitoringtime_ms' => $parameter['monitoringtime_ms'],
            'chfifosize' => $parameter['chfifosize'],
            'totalfifosize' => $parameter['totalfifosize'],
            'samplingrate' => $parameter['samplingrate'],
            'staticchannels' => $parameter['staticchannels'],
            'sampeperch' => $parameter['sampeperch'],
            'dynamicchannels' => $parameter['dynamicchannels'],
            'monitoring_status' => $parameter['monitoring_status'],
            'measurement_state' => $parameter['measurement_state'],
            'operating_class' => $parameter['operating_class'],
            'acrms_status' => $parameter['acrms_status'],
            'lfrms_status' => $parameter['lfrms_status'],
            'isorms_status' => $parameter['isorms_status'],
            'hfrms_status' => $parameter['hfrms_status'],
            'acpeak_status' => $parameter['acpeak_status'],
            'accrest_status' => $parameter['accrest_status'],
            'mean_status' => $parameter['mean_status'],
            'peak2peak_status' => $parameter['peak2peak_status'],
            'kurtosis_status' => $parameter['kurtosis_status'],
            'smax_status' => $parameter['smax_status'],
            'mp_acpeak_value' => str_replace(",", ".", $parameter['mp_acpeak_value']),
            'mp_acrms_value' => str_replace(",", ".", $parameter['mp_acrms_value']),
            'mp_lfrms_value' => str_replace(",", ".", $parameter['mp_lfrms_value']),
            'mp_isorms_value' => str_replace(",", ".", $parameter['mp_isorms_value']),
            'mp_hfrms_value' => str_replace(",", ".", $parameter['mp_hfrms_value']),
            'mp_accrest_value' => str_replace(",", ".", $parameter['mp_accrest_value']),
            'mp_mean_value' => str_replace(",", ".", $parameter['mp_mean_value']),
            'mp_peak2peak_value' => str_replace(",", ".", $parameter['mp_peak2peak_value']),
            'mp_kurtosis_value' => str_replace(",", ".", $parameter['mp_kurtosis_value']),
            'mp_smax_value' => str_replace(",", ".", $parameter['mp_smax_value']),
            'lfrms' => str_replace(",", ".", $parameter['lfrms']),
            'isorms' => str_replace(",", ".", $parameter['isorms']),
            'hfrms' => str_replace(",", ".", $parameter['hfrms']),
            'crest' => str_replace(",", ".", $parameter['crest']),
            'peak' => str_replace(",", ".", $parameter['peak']),
            'rms' => str_replace(",", ".", $parameter['rms']),
            'max' => str_replace(",", ".", $parameter['max']),
            'min' => str_replace(",", ".", $parameter['min']),
            'peak2peak' => str_replace(",", ".", $parameter['peak2peak']),
            'std' => str_replace(",", ".", $parameter['std']),
            'kurtosis' => str_replace(",", ".", $parameter['kurtosis']),
            'skewness' => str_replace(",", ".", $parameter['skewness']),
            'smax' => str_replace(",", ".", $parameter['smax']),
            'histo' => str_replace(",", ".", $parameter['histo']),
            'a1x' => str_replace(",", ".", $parameter['a1x']),
            'p1x' => str_replace(",", ".", $parameter['p1x']),
            'a2x' => str_replace(",", ".", $parameter['a2x']),
            'p2x' => str_replace(",", ".", $parameter['p2x']),
            'p3x' => str_replace(",", ".", $parameter['p3x']),
            'op1' => str_replace(",", ".", $parameter['op1']),
            'op2' => str_replace(",", ".", $parameter['op2']),
            'op3' => str_replace(",", ".", $parameter['op3']),
            'op4' => str_replace(",", ".", $parameter['op4']),
            'op5' => str_replace(",", ".", $parameter['op5']),
            'op6' => str_replace(",", ".", $parameter['op6']),
            'op7' => str_replace(",", ".", $parameter['op7']),
            'op8' => str_replace(",", ".", $parameter['op8']),
            'op9' => str_replace(",", ".", $parameter['op9']),
            'op10' => str_replace(",", ".", $parameter['op10']),
            'chname' => $parameter['chname'],
            'chunit' => $parameter['chunit'],
            'chstatus' => $parameter['chstatus'],
            'sensors_id' => $parameter['sensors_id'],
            'component_id' => $parameter['component_id'],
            'asset_id' => $parameter['asset_id'],
            'lines_id' => $parameter['lines_id'],
            'plants_id' => $parameter['plants_id'],
            'customers_id' => $parameter['customers_id'],
            'id' => $parameter['eventid']);

        return $doc;
    }

    function getTicketDocument($parameter)
    {
        $doc = array(
            'created' => $parameter['created'] . ".000000",
            'description' => $parameter['description'],
            'user' => $parameter['user'],
            'status' => $parameter['status'],
            'customer' => $parameter['customer'],
            'plant' => $parameter['plant'],
            'line' => $parameter['line'],
            'asset' => $parameter['asset'],
            'component' => $parameter['component'],
            'sensor' => $parameter['sensor'],
            'location' => array(
                'lat' => $parameter['latitude'],
                'lon' => $parameter['longitude']
            ),
            'id' => $parameter['tickets_id']);

        return $doc;
    }

    function indexDocument($index, $doc_type, $data)
    {
        global $osC_Database;
        $search_host = '193.70.0.228';
        //$search_port = '9200';

        $json_doc = json_encode($data);

//        $request = \Httpful\Request::post('http://'.$search_host.'/'.$index.'/'.$doc_type.'/')
//            //->addOnCurlOption(CURLOPT_COOKIEFILE, $this->cookieFile)
//            //->addOnCurlOption(CURLOPT_COOKIEJAR, $this->cookieFile)
//            ->addOnCurlOption(CURLOPT_TIMEOUT, 50000)
//            ->addOnCurlOption(CURLOPT_CONNECTTIMEOUT, 0)
//            //->authenticateWithBasic(REPORT_USER,REPORT_PASS)
//            ->addHeader('accept', 'application/json')
//            ->body($json_doc)
//            ->sendsJson()
//            ->send();
//
//        return $request;

        $baseUri = 'http://' . $search_host . '/' . $index . '/' . $doc_type . '/' . $data['id'];

        $ci = curl_init();
        curl_setopt($ci, CURLOPT_URL, $baseUri);
        //curl_setopt($ci, CURLOPT_PORT, $search_port);
        curl_setopt($ci, CURLOPT_TIMEOUT, 5000);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ci, CURLOPT_FORBID_REUSE, 0);
        curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ci, CURLOPT_POSTFIELDS, $json_doc);
        $response = curl_exec($ci);
        curl_close($ci);

        $json = json_decode($response, true);

        if(isset($json["_index"]))
        {
            $query = 'update delta_parameters set indexed = 1 where eventid = :eventid';
            $Qupdate = $osC_Database->query($query);
            $Qupdate->bindInt(':eventid', $data['id']);
            $Qupdate->execute();

            if ($osC_Database->isError()) {
                $osC_Database->rollbackTransaction();

                $config = array();
                $config['to'] = 'guyfomi@gmail.com';
                $config['body'] = $osC_Database->getError();
                $config['subject'] = "Impossible d'indexer le document " . $data['id'] . " de type " . $index . '/' . $doc_type;
                osC_Categories_Admin::sendMail($config);
            }
        }
        else
        {
            $config = array();
            $config['to'] = 'guyfomi@gmail.com';
            $config['body'] = $response;
            $config['subject'] = "Impossible d'indexer le document " . $data['id'] . " de type " . $index . '/' . $doc_type;
            osC_Categories_Admin::sendMail($config);
        }

        echo $response;
    }
}

?> 