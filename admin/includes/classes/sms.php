<?php
/*
  $Id: newsletters.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

class osC_Sms_Admin
{
    function getData($id)
    {
        global $osC_Database;

        $QSms = $osC_Database->query('select * from :table_sms where ID = :sms_id');
        $QSms->bindTable(':table_sms', 'messages.messages');
        $QSms->bindInt(':sms_id', $id);
        $QSms->execute();

        $data = $QSms->toArray();

        $QSms->freeResult();

        return $data;
    }

    function save($id = null, $data)
    {
        global $osC_Database;

        if (is_numeric($id)) {
            $Qemail = $osC_Database->query('update :table_sms set title = :title, content = :content, nbre_days = :nbre_days where relances_id = :relances_id');
            $Qemail->bindInt(':relances_id', $id);
        } else {
            $Qemail = $osC_Database->query('insert into :table_sms (title, content, nbre_days, date_added, status) values (:title, :content, :nbre_days, now(), 0)');
        }

        $Qemail->bindTable(':table_sms', TABLE_RELANCES);
        $Qemail->bindValue(':title', $data['title']);
        $Qemail->bindValue(':content', $data['content']);
        $Qemail->bindValue(':nbre_days', $data['nbre_days']);
        $Qemail->setLogging($_SESSION['title'], $id);
        $Qemail->execute();

        if (!$osC_Database->isError()) {
            return true;
        }

        return false;
    }

    function delete($id)
    {
        global $osC_Database;

        $error = false;

        $ids = explode(',', $id);

        $osC_Database->startTransaction();

        foreach ($ids as $v) {
            $Qcheck = $osC_Database->query('select s.status,s.body from :table_sms s where ID = :ID');
            $Qcheck->bindTable(':table_sms', 'messages.messages');
            $Qcheck->bindInt(':ID', $v);
            $Qcheck->execute();

            if ($osC_Database->isError()) {
                $error = true;
            }

            if ($Qcheck->value('status') != '2') {
                $Qdelete = $osC_Database->query('delete from :table_sms where ID = :ID');
                $Qdelete->bindTable(':table_sms', 'messages.messages');
                $Qdelete->bindInt(':ID', $v);
                $Qdelete->execute();

                if ($osC_Database->isError()) {
                    $error = true;
                }
            }
            else
            {
                return "Impossible de supprimer le message " . $Qcheck->value('body') . '......il a deja ete envoye';
            }
        }

        if ($error === false) {
            $osC_Database->commitTransaction();

            return "true";
        }

        $osC_Database->rollbackTransaction();

        return $osC_Database->getError();
    }
}

?>
