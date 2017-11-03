<?php
/*
  $Id: relances.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
require('includes/classes/sms.php');

class toC_Json_Sms
{

    function listSms()
    {
        global $toC_Json, $osC_Database, $osC_Language;

        $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start'];
        $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit'];

        $query = 'SELECT messages.ID AS sms_id, directions.Description AS direction, type.Description AS type, statusdetails.Description AS status,statusdetails.StatusDetails AS categoryId, messages.Subject AS objet, messages.Body AS message, messages.ToAddress AS no_phone FROM messages.messages INNER JOIN messages.directions ON messages.Direction = directions.Direction INNER JOIN messages.type ON messages.Type = type.Type INNER JOIN messages.statusdetails ON messages.StatusDetails = statusdetails.StatusDetails ORDER BY messages.ID DESC';

        if (isset($_REQUEST['categoryId'])) {
            $query = 'SELECT messages.ID AS sms_id, directions.Description AS direction, type.Description AS type, statusdetails.Description AS status,statusdetails.StatusDetails AS categoryId, messages.Subject AS objet, messages.Body AS message, messages.ToAddress AS no_phone FROM messages.messages INNER JOIN messages.directions ON messages.Direction = directions.Direction INNER JOIN messages.type ON messages.Type = type.Type INNER JOIN messages.statusdetails ON messages.StatusDetails = statusdetails.StatusDetails where statusdetails.StatusDetails =:categoryId ORDER BY messages.ID DESC';
            $QSms = $osC_Database->query($query);
            $QSms->bindInt(':categoryId', $_REQUEST['categoryId']);
        }
        else
        {
            $QSms = $osC_Database->query($query);
        }

        $QSms->setExtBatchLimit($start, $limit);
        $QSms->execute();

        $records = array();
        while ($QSms->next()) {
            $records[] = array(
                'sms_id' => $QSms->valueInt('sms_id'),
                'direction' => $QSms->value('direction'),
                'categoryId' => $QSms->valueInt('categoryId'),
                'type' => $QSms->value('type'),
                'status' => $QSms->value('status'),
                'objet' => $QSms->value('objet'),
                'message' => $QSms->value('message'),
                'no_phone' => $QSms->value('no_phone')
            );
        }

        $QSms->freeResult();

        $query = 'select count(ID) as total FROM messages.messages';

        if (isset($_REQUEST['categoryId'])) {
            $query = 'select count(ID) as total FROM messages.messages where StatusDetails =:categoryId';
            $QTotal = $osC_Database->query($query);
            $QTotal->bindInt(':categoryId', $_REQUEST['categoryId']);
        }
        else
        {
            $QTotal = $osC_Database->query($query);
        }

        $QTotal->execute();

        $total = $QTotal->value('total');

        $QTotal->freeResult();

        $response = array(EXT_JSON_READER_TOTAL => $total,
                          EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function deleteSms()
    {
        global $toC_Json, $osC_Language, $osC_Image;

        $answer = osC_Sms_Admin::delete($_REQUEST['batch']);
        if ($answer == "true") {
            $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
            $response = array('success' => false, 'feedback' => $answer);
        }

        echo $toC_Json->encode($response);
    }

    function loadSms()
    {
        global $toC_Json;

        $data = osC_Sms_Admin::getData($_REQUEST['sms_id']);

        $response = array('success' => true, 'data' => $data);

        echo $toC_Json->encode($response);
    }

    function startSmsService()
    {
        global $toC_Json;

        $answer = win32_start_service('AxMmSvc');

        if ($answer != WIN32_NO_ERROR) {
            $feedback = 'success';
        }
        else
        {
            $feedback = 'error : ' . $answer;
        }

        $response = array('success' => true, 'feedback' => $feedback);
        echo $toC_Json->encode($response);
    }

    function loadSmsTree()
    {
        global $osC_Database, $osC_Language, $toC_Json;

        $Qcategories = $osC_Database->query('SELECT statusdetails.StatusDetails AS categoryId,statusdetails.Description AS categories_name, COUNT(messages.ID) AS count FROM messages.statusdetails statusdetails LEFT OUTER JOIN messages.messages messages ON (statusdetails.StatusDetails = messages.StatusDetails) GROUP BY statusdetails.Description,statusdetails.StatusDetails ORDER BY statusdetails.Description asc');
        $Qcategories->execute();

        $records = array();

        while ($Qcategories->next()) {
            $records[] = array('id' => $Qcategories->value('categoryId'),
                               'text' => $Qcategories->value('categories_name') . ' ( ' . $Qcategories->value('count') . ' )',
                               'cls' => 'x-tree-node-collapsed',
                               'leaf' => true);
        }

        $Qcategories->freeResult();

        echo $toC_Json->encode($records);
    }

    function saveSms()
    {
        global $toC_Json, $osC_Language, $osC_Database;

        if (isset($_REQUEST['sms_id'])) {
            $QSms = $osC_Database->query('update :table_sms set Status = :status,Direction = :direction, Type = :type,ToAddress = :toaddress,Subject = :subject,Body = :body,ChannelID = :channelid,BodyFormat = :bodyformat,FromAddress = :fromaddress where ID = :sms_id');
            $QSms->bindInt(':sms_id', $_REQUEST['sms_id']);
        } else {
            $QSms = $osC_Database->query('insert into :table_sms (Status,Direction, Type,ToAddress,Subject,Body,ChannelID,BodyFormat,FromAddress) values (:status,:direction,:type,:toaddress,:subject,:body,:channelid,:bodyformat,:fromaddress)');
        }

        $QSms->bindTable(':table_sms', 'messages.messages');
        $QSms->bindInt(':direction', '2');
        $QSms->bindInt(':type', '1');
        $QSms->bindInt(':bodyformat', '0');
        $QSms->bindInt(':status', '1');
        $QSms->bindValue(':fromaddress', $_REQUEST['fromaddress']);
        $QSms->bindValue(':toaddress', $_REQUEST['ToAddress']);
        $QSms->bindValue(':channelid', '1001');
        $QSms->bindValue(':body', $_REQUEST['Body']);
        $QSms->bindValue(':subject', $_REQUEST['Subject']);
        $QSms->execute();

        if ($osC_Database->isError()) {
            $response = array('success' => false, 'feedback' => $osC_Database->getError());
            echo $toC_Json->encode($response);
            return;
        }

        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));

        echo $toC_Json->encode($response);
    }

    function getVariables()
    {
        global $toC_Json;

        $keywords = array(
            'COMPANY_NAME' => ('%%COMPANY_NAME%%'),
            'CONTACT_NAME' => ('%%CONTACT_NAME%%'),
            'CONTACT_TELEPHONE' => ('%%CONTACT_TELEPHONE%%'),
            'CONTACT_EMAIL' => ('%%CONTACT_EMAIL%%'),
            'CONTACT_BIRTHDATE' => ('%%CONTACT_BIRTHDATE%%'),
            'CONTACT_CREDIT_BALANCE' => ('%%CONTACT_CREDIT_BALANCE%%'),
            'CONTACT_FAX' => ('%%CONTACT_FAX%%')
        );

        $records = array();
        foreach ($keywords as $key => $value) {
            $records[] = array('id' => $key, 'value' => $value);
        }

        $response = array(EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function saveGroupesms()
    {
        global $toC_Json, $osC_Language, $osC_Database;

        $Qcustomers = $osC_Database->query('select c.* from :table_customers c where customers_groups_id = :id');
        $Qcustomers->bindTable(':table_customers', TABLE_CUSTOMERS);
        $Qcustomers->bindInt(':id', $_REQUEST['customers_groups_id']);

        $Qcustomers->appendQuery('order by c.customers_firstname');
        $Qcustomers->execute();

        if ($osC_Database->isError()) {
            $response = array('success' => false, 'feedback' => $osC_Database->getError());
            echo $toC_Json->encode($response);
            return;
        }

        while ($Qcustomers->next()) {
            $body = $_REQUEST['Body'];
            $body = str_replace('%%COMPANY_NAME%%', STORE_NAME, $body);
            $body = str_replace('%%CONTACT_NAME%%', $Qcustomers->value('customers_firstname') . ' ' . $Qcustomers->value('customers_lastname'), $body);
            $body = str_replace('%%CONTACT_TELEPHONE%%', $Qcustomers->value('customers_telephone'), $body);
            $body = str_replace('%%CONTACT_EMAIL%%', $Qcustomers->value('customers_email_address'), $body);
            $body = str_replace('%%CONTACT_BIRTHDATE%%', $Qcustomers->value('customers_dob'), $body);
            $body = str_replace('%%CONTACT_CREDIT_BALANCE%%', $Qcustomers->value('customers_credits'), $body);
            $body = str_replace('%%CONTACT_FAX%%', $Qcustomers->value('customers_fax'), $body);

            $toaddress = $Qcustomers->value('customers_telephone');
            $toaddress = str_replace(' ','', $toaddress);

            $QSms = $osC_Database->query('insert into :table_sms (Status,Direction, Type,ToAddress,Subject,Body,ChannelID,BodyFormat,FromAddress) values (:status,:direction,:type,:toaddress,:subject,:body,:channelid,:bodyformat,:fromaddress)');
            $QSms->bindTable(':table_sms', 'messages.messages');
            $QSms->bindInt(':direction', '2');
            $QSms->bindInt(':type', '1');
            $QSms->bindInt(':bodyformat', '0');
            $QSms->bindInt(':status', '1');
            $QSms->bindValue(':fromaddress', $_REQUEST['fromaddress']);
            $QSms->bindValue(':toaddress',$toaddress);
            $QSms->bindValue(':channelid', '1001');
            $QSms->bindValue(':body', $body);
            $QSms->bindValue(':subject', $_REQUEST['Subject']);
            $QSms->execute();

            if ($osC_Database->isError()) {
                $response = array('success' => false, 'feedback' => $osC_Database->getError());
                echo $toC_Json->encode($response);
                return;
            }
        }

        $Qcustomers->freeResult();

        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));

        echo $toC_Json->encode($response);
    }

    function saveRelance()
    {
        global $toC_Json, $osC_Language, $osC_Database;

        $QSms = $osC_Database->query('insert into :table_sms (CustomField1,CustomField2,Status,Direction, Type,ToAddress,Subject,Body,ChannelID,BodyFormat,FromAddress) values (:relances_id,:numepoli,:status,:direction,:type,:toaddress,:subject,:body,:channelid,:bodyformat,:fromaddress)');
        $QSms->bindTable(':table_sms', 'messages.messages');
        $QSms->bindInt(':direction', '2');
        $QSms->bindInt(':type', '1');
        $QSms->bindInt(':bodyformat', '0');
        $QSms->bindInt(':status', '1');
        $QSms->bindValue(':relances_id', $_REQUEST['relances_id']);
        $QSms->bindValue(':numepoli', $_REQUEST['numepoli']);
        $QSms->bindValue(':fromaddress', $_REQUEST['fromaddress']);
        $QSms->bindValue(':toaddress', $_REQUEST['toaddress']);
        $QSms->bindValue(':channelid', '1001');
        $QSms->bindValue(':body', $_REQUEST['body']);
        $QSms->bindValue(':subject', $_REQUEST['subject']);
        $QSms->execute();

        if ($osC_Database->isError()) {
            $response = array('success' => false, 'feedback' => $osC_Database->getError());
            echo $toC_Json->encode($response);
            return;
        }

        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));

        echo $toC_Json->encode($response);
    }
}

?>
