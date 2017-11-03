<?php

require_once('includes/classes/categories.php');

if (!class_exists('osC_Roles_Admin')) {
    include('includes/classes/roles.php');
}

class osC_Ticket_Admin
{
    function saveDepartment($id = null,$data)
    {
        global $osC_Database;

        $event = array();

        $osC_Database->selectDatabase(DB_TICKET_DATABASE);

        $department_id = -1;

        $Qdel = $osC_Database->query("delete from ost0t_department where name = '" . $data['roles_name'] . "'");
        $Qdel->bindValue(':name', $data['roles_name']);
        $Qdel->execute();

        if ($osC_Database->isError()) {
            var_dump($Qdel);
            $Qdel->freeResult();

            $osC_Database->selectDatabase(DB_DATABASE);

            $_SESSION['error'] = $osC_Database->getError();
            return $department_id;
        }

        $Qdept = $osC_Database->query('INSERT INTO ost0t_department(name,flags) VALUES (:name,1)');
        $Qdept->bindValue(':name', $data['roles_name']);
        $Qdept->execute();

        //var_dump($Qdept);

        if ($osC_Database->isError()) {
            var_dump($Qdept);
            $Qdept->freeResult();

            $osC_Database->selectDatabase(DB_DATABASE);

            $_SESSION['error'] = $osC_Database->getError();
            return $department_id;
        }

        $Qdept->freeResult();

        $query = "select id from ost0t_department where name = '" . $data['roles_name'] . "'";

        $Qcheck = $osC_Database->query($query);
        $Qcheck->execute();

        if ($osC_Database->isError()) {

            var_dump($Qcheck);
            $Qcheck->freeResult();

            $osC_Database->selectDatabase(DB_DATABASE);

            $_SESSION['error'] = $osC_Database->getError();
            return $department_id;
        }

        while ($Qcheck->next()) {
            $department_id = $Qcheck->valueInt('id');
        }

        $Qcheck->freeResult();

        if($department_id == -1 || $department_id ==0)
        {
            $osC_Database->selectDatabase(DB_DATABASE);
            return $department_id;
        }

        $event['content_id'] = $department_id;
        $event['content_type'] = "customer";
        $event['type'] = "succes";
        $event['event_date'] = date('Y-m-d H:i:s');
        $event['source'] = "cloud";
        $event['user'] = $_SESSION['admin']['username'];
        $event['category'] = "debug";
        $event['description'] = "Un departement a ete cree pour le Groupe " . $data['roles_name'] . " par " . $_SESSION['admin']['username'];;

        osC_Categories_Admin::logEvent($event);
        $osC_Database->selectDatabase(DB_DATABASE);
        return $department_id;
    }

    function deleteAgent($id,$username)
    {
        global $osC_Database;

        $osC_Database->selectDatabase(DB_TICKET_DATABASE);

        $osC_Database->startTransaction();

        if(isset($id) && is_numeric($id) && $id != 0)
        {
            $Qdel = $osC_Database->query('delete from ost0t_staff where staff_id = :id');
            $Qdel->bindInt(':id', $id);
            $Qdel->execute();

            if (!$osC_Database->isError()) {
                $Qdel = $osC_Database->query('delete from ost0t_staff_dept_access where staff_id = :id');
                $Qdel->bindInt(':id', $id);
                $Qdel->execute();
            }
        }

        if (!$osC_Database->isError()) {
            $Qdel = $osC_Database->query('delete from ost0t_staff where username = :username');
            $Qdel->bindValue(':username', $username);
            $Qdel->execute();
        }

        if (!$osC_Database->isError()) {
            $osC_Database->commitTransaction();

            $osC_Database->selectDatabase(DB_DATABASE);
            return true;
        }

        $_SESSION['error'] = $osC_Database->isError();

        $osC_Database->rollbackTransaction();

        $osC_Database->selectDatabase(DB_DATABASE);
        return false;
    }

    function saveAgentPrimary($data,$roles_id)
    {
        global $osC_Database;

        $role = osC_Roles_Admin::getRole($roles_id);

        if($role)
        {
            if(!isset($role['department_id']) || !is_numeric($role['department_id']) || $role['department_id'] == 0)
            {
                $department_id = osC_Ticket_Admin::saveDepartment(null,$role);
            }
            else
            {
                $department_id = $role['department_id'];
            }

            $event = array();

            $osC_Database->selectDatabase(DB_TICKET_DATABASE);

            $agent_id = -1;

            $Qagent = $osC_Database->query("INSERT INTO ost0t_staff(dept_id, role_id, username, firstname, lastname, email, phone, phone_ext, mobile,isactive, isadmin, isvisible, onvacation, assigned_only, show_assigned_tickets, change_passwd, max_page_size, auto_refresh_rate, default_signature_type, default_paper_size, extra) VALUES
            (:dept_id,1, :username, :firstname,' ', :email, :phone, :phone_ext, :mobile,1,0,1,0,1,0,0,0,0,'none','Letter','{\"def_assn_role\":true}')");

            $Qagent->bindValue(':username', $data['user_name']);
            $Qagent->bindInt(':dept_id', $department_id);
            $Qagent->bindValue(':firstname', $data['user_name']);
            $Qagent->bindValue(':email', $data['email_address']);
            $Qagent->bindValue(':phone',' ');
            $Qagent->bindValue(':phone_ext',' ');
            $Qagent->bindValue(':mobile',' ');
            $Qagent->execute();

            //var_dump($Qdept);

            if ($osC_Database->isError()) {
                var_dump($Qagent);
                $Qagent->freeResult();

                $osC_Database->selectDatabase(DB_DATABASE);

                $_SESSION['error'] = $osC_Database->getError();
                return $agent_id;
            }

            $Qagent->freeResult();

            $query = "select staff_id from ost0t_staff where username = '" . $data['user_name'] . "'";

            $Qcheck = $osC_Database->query($query);
            $Qcheck->execute();

            if ($osC_Database->isError()) {

                var_dump($Qcheck);
                $Qcheck->freeResult();

                $osC_Database->selectDatabase(DB_DATABASE);

                $_SESSION['error'] = $osC_Database->getError();
                return $agent_id;
            }

            while ($Qcheck->next()) {
                $agent_id = $Qcheck->valueInt('staff_id');
            }

            $Qcheck->freeResult();

            if($agent_id == -1 || $agent_id ==0)
            {
                $osC_Database->selectDatabase(DB_DATABASE);
                return $agent_id;
            }

            $event['content_id'] = $agent_id;
            $event['content_type'] = "agent";
            $event['type'] = "succes";
            $event['event_date'] = date('Y-m-d H:i:s');
            $event['source'] = "cloud";
            $event['user'] = $_SESSION['admin']['username'];
            $event['category'] = "debug";
            $event['description'] = "L'agent du compte " . $data['user_name'] . " a ete cree par " . $_SESSION['admin']['username'];;

            osC_Categories_Admin::logEvent($event);
            $osC_Database->selectDatabase(DB_DATABASE);
            return $agent_id;
        }

        $_SESSION['error'] = "Impossible de charger ce role !!!";
        return -1;
    }

    function saveAgentExtended($data,$roles_id)
    {
        global $osC_Database;

        $role = osC_Roles_Admin::getRole($roles_id);

        $osC_Database->selectDatabase(DB_TICKET_DATABASE);

        $Qagent = $osC_Database->query("INSERT INTO ost0t_staff_dept_access(staff_id, dept_id, role_id, flags) VALUES
            (:staff_id, :dept_id,1,1)");

        $Qagent->bindValue(':staff_id', $data['staff_id']);
        $Qagent->bindInt(':dept_id', $role['department_id']);
        $Qagent->execute();

        //var_dump($Qdept);

        if ($osC_Database->isError()) {
            var_dump($Qagent);
            $Qagent->freeResult();

            $osC_Database->selectDatabase(DB_DATABASE);

            $_SESSION['error'] = $osC_Database->getError();
            return false;
        }

        $Qagent->freeResult();

        return true;
    }

    function update_department($data)
    {
        global $osC_Database;

        $ua = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13';

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "http://www.ticket.centre-albert-einstein.com/scp/departments.php?id=" . $data['department_id']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "__CSRFToken__=766e0b99252480f04f0bbec5106400d786e9f9f4&do=update&a=&id=" . $data['department_id'] . "&pid=&name=" . $data['customers_surname'] . "&ispublic=1&sla_id=0&manager_id=0&assign_members_only=on&email_id=0&tpl_id=0&autoresp_email_id=0&group_membership=0&signature=&members%\"5B\"%\"5D=1&member_role\"%\"5B1\"%\"5D=1&submit=Save+Changes\"");
        curl_setopt($ch, CURLOPT_POST, 1);
        //curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
        curl_setopt($ch, CURLOPT_USERAGENT,$ua);

        $headers = array();
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response  = curl_exec($ch);

        //var_dump($response);

        $osC_Database->selectDatabase(DB_TICKET_DATABASE);

        $query = "select id from ost0t_department where name = '" . $data['customers_surname'] . "'";

        $error = true;
        $id = -1;

        $Qcategories = $osC_Database->query($query);
        $Qcategories->execute();

        var_dump($Qcategories);

        if ($osC_Database->isError()) {
            $error = true;

            $event = array();
            $event['content_id'] = $data['customers_id'];
            $event['content_type'] = "customer";
            $event['type'] = "error";
            $event['event_date'] = date('Y-m-d H:i:s');
            $event['source'] = "cloud";
            $event['user'] = $_SESSION['admin']['username'];
            $event['category'] = "debug";
            $event['description'] = "Impossible de mettre à jour le departement du client " . $data['customers_surname'] . " Raison : " . $osC_Database->getError();

            osC_Categories_Admin::logEvent($event);
            $Qcategories->freeResult();
            curl_close ($ch);
            $osC_Database->selectDatabase(DB_DATABASE);
            return $id;
        }

        while ($Qcategories->next()) {
            $id = $Qcategories->valueInt('id');
        }

        $Qcategories->freeResult();

        if($id == -1 || $id ==0)
        {
            $error = true;

            $event = array();
            $event['content_id'] = $data['customers_id'];
            $event['content_type'] = "customer";
            $event['type'] = "error";
            $event['event_date'] = date('Y-m-d H:i:s');
            $event['source'] = "cloud";
            $event['user'] = $_SESSION['admin']['username'];
            $event['category'] = "debug";
            $event['description'] = "Impossible de modifier le departement du client " . $data['customers_surname'] . " Raison : " . $id;

            osC_Categories_Admin::logEvent($event);
            curl_close ($ch);
            $osC_Database->selectDatabase(DB_DATABASE);
            return $id;
        }

        $event = array();
        $event['content_id'] = $data['customers_id'];
        $event['content_type'] = "customer";
        $event['type'] = "succes";
        $event['event_date'] = date('Y-m-d H:i:s');
        $event['source'] = "cloud";
        $event['user'] = $_SESSION['admin']['username'];
        $event['category'] = "debug";
        $event['description'] = "Le departement du client " . $data['customers_surname'] . " a ete modifie par " . $_SESSION['admin']['username'];

        osC_Categories_Admin::logEvent($event);
        curl_close ($ch);
        $osC_Database->selectDatabase(DB_DATABASE);
        return $id;
    }

    function delete_department($id,$customers_id)
    {
        global $osC_Database;

        $ua = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13';

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "http://www.ticket.centre-albert-einstein.com/scp/departments.php");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "__CSRFToken__=766e0b99252480f04f0bbec5106400d786e9f9f4&do=mass_process&a=delete&ids%\"5B\"%\"5D=" . $id . "\"");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_USERAGENT,$ua);

        $headers = array();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $query = "select count(id) as nbre from ost0t_department where id = " . $id;

        $error = true;
        $id = -1;

        $osC_Database->selectDatabase(DB_TICKET_DATABASE);

        $Qcategories = $osC_Database->query($query);
        $Qcategories->execute();

        //var_dump($Qcategories);

        if ($osC_Database->isError()) {
            $error = true;

            $event = array();
            $event['content_id'] = $customers_id;
            $event['content_type'] = "customer";
            $event['type'] = "error";
            $event['event_date'] = date('Y-m-d H:i:s');
            $event['source'] = "cloud";
            $event['user'] = $_SESSION['admin']['username'];
            $event['category'] = "debug";
            $event['description'] = "Impossible de mettre à jour le departement du client : " . $osC_Database->getError();

            osC_Categories_Admin::logEvent($event);
            $Qcategories->freeResult();
            curl_close ($ch);
            $osC_Database->selectDatabase(DB_DATABASE);
            return $id;
        }

        $count = 0;

        while ($Qcategories->next()) {
            $count = $Qcategories->valueInt('nbre');
        }

        $Qcategories->freeResult();

        if($count == 0)
        {
            $error = true;

            $event = array();
            $event['content_id'] = $customers_id;
            $event['content_type'] = "customer";
            $event['type'] = "error";
            $event['event_date'] = date('Y-m-d H:i:s');
            $event['source'] = "cloud";
            $event['user'] = $_SESSION['admin']['username'];
            $event['category'] = "debug";
            $event['description'] = "Impossible de supprimer departement du client ";

            osC_Categories_Admin::logEvent($event);
            curl_close ($ch);
            $osC_Database->selectDatabase(DB_DATABASE);
            return $id;
        }

        $event = array();
        $event['content_id'] = $customers_id;
        $event['content_type'] = "customer";
        $event['type'] = "succes";
        $event['event_date'] = date('Y-m-d H:i:s');
        $event['source'] = "cloud";
        $event['user'] = $_SESSION['admin']['username'];
        $event['category'] = "debug";
        $event['description'] = "Le departement du client a ete supprime";

        osC_Categories_Admin::logEvent($event);
        curl_close ($ch);
        $osC_Database->selectDatabase(DB_DATABASE);
        return $id;
    }
}

?>