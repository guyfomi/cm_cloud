<?php
/*
  $Id: articles.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
require('includes/classes/administrators.php');
require('includes/classes/categories.php');

class toC_Json_Login
{

    function login()
    {
        global $toC_Json, $osC_Language, $osC_Database;

        $event =array();

        $response = array();
        if (!empty($_REQUEST['user_name']) && !empty($_REQUEST['user_password'])) {
            if ($_REQUEST['user_name'] == "admin") {
                $Qadmin = $osC_Database->query('select id, user_name, user_password from :table_administrators where user_name = :user_name');
                $Qadmin->bindTable(':table_administrators', TABLE_ADMINISTRATORS);
                $Qadmin->bindValue(':user_name', $_REQUEST['user_name']);
                $Qadmin->execute();

                if ($Qadmin->numberOfRows() > 0) {
                    if (osc_validate_password($_REQUEST['user_password'], $Qadmin->value('user_password'))) {
                        $_SESSION['admin'] = array('id' => $Qadmin->valueInt('id'),
                            'username' => $Qadmin->value('user_name'),
                            'access' => osC_Access::getUserLevels($Qadmin->valueInt('id')),
                            'roles' => osC_Access::getUserRoles($Qadmin->valueInt('id'))
                        );

                        $response = array('success' => true, 'feedback' => 'OK');

                        echo $toC_Json->encode($response);
                        exit;
                    }
                } else {
                    $response = array('success' => false, 'feedback' => 'Compte ou mot de passe invalide');

                    $event['content_id'] = -1;
                    $event['content_type'] = "user";
                    $event['type'] = "error";
                    $event['event_date'] = date('Y-m-d H:i:s');
                    $event['source'] = "cloud";
                    $event['user'] = $_REQUEST['user_name'];
                    $event['category'] = "security";
                    $event['description'] = "Tentative d'ouverture de session echouee avec le compte " . $_REQUEST['user_name'];

                    osC_Categories_Admin::logEvent($event);

                    echo $toC_Json->encode($response);
                    exit;
                }
            } else {
                $query = "select a.id, a.user_name, a.user_password,u.status from :table_administrators a inner join :table_users u on (a.id = u.administrators_id) where a.user_name = :user_name";
                //var_dump($query);
                $Qadmin = $osC_Database->query($query);
                $Qadmin->bindTable(':table_administrators', TABLE_ADMINISTRATORS);
                $Qadmin->bindTable(':table_users', TABLE_USERS);
                $Qadmin->bindValue(':user_name', $_REQUEST['user_name']);
                $Qadmin->execute();

                if ($Qadmin->numberOfRows() > 0) {
                    if ($Qadmin->value('status') != 1) {
                        $response = array('success' => false, 'feedback' => "Vous n'avez pas le droit d'ouvrir une session, veuillez contacter votre administrateur !!!");
                        echo $toC_Json->encode($response);
                        exit;
                    } else {
                        if (osc_validate_password($_REQUEST['user_password'], $Qadmin->value('user_password'))) {
                            $_SESSION['admin'] = array('id' => $Qadmin->valueInt('id'),
                                'username' => $Qadmin->value('user_name'),
                                'access' => osC_Access::getUserLevels($Qadmin->valueInt('id')),
                                'roles' => osC_Access::getUserRoles($Qadmin->valueInt('id'))
                            );

                            $response['success'] = true;

                            $event['content_id'] = $Qadmin->valueInt('id');
                            $event['content_type'] = "user";
                            $event['event_date'] = date('Y-m-d H:i:s');
                            $event['type'] = "info";
                            $event['source'] = "cloud";
                            $event['user'] = $Qadmin->value('user_name');
                            $event['category'] = "security";
                            $event['description'] = "L'utilisateur " . $Qadmin->value('user_name') . " a ouvert une session";

                            osC_Categories_Admin::logEvent($event);

                            echo $toC_Json->encode($response);
                            exit;
                        }
                    }
                }
            }
        } else {
            $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_login_invalid'));

            $event['content_id'] = -1;
            $event['content_type'] = "user";
            $event['type'] = "error";
            $event['event_date'] = date('Y-m-d H:i:s');
            $event['source'] = "cloud";
            $event['user'] = $_REQUEST['user_name'];
            $event['category'] = "security";
            $event['description'] = "Tentative d'ouverture de session echouee avec le compte " . $_REQUEST['user_name'];

            osC_Categories_Admin::logEvent($event);

            echo $toC_Json->encode($response);
        }
    }

    function loginwin()
    {

        global $toC_Json, $osC_Language, $osC_Database;

        $response = array();
        if (!empty($_REQUEST['user_name']) && !empty($_REQUEST['user_password'])) {
            $Qadmin = $osC_Database->query('select id, user_name, user_password from :table_administrators where user_name = :user_name');
            $Qadmin->bindTable(':table_administrators', TABLE_ADMINISTRATORS);
            $Qadmin->bindValue(':user_name', $_REQUEST['user_name']);
            $Qadmin->execute();

            if ($Qadmin->numberOfRows() > 0) {
                if (osc_validate_password($_REQUEST['user_password'], $Qadmin->value('user_password'))) {
                    $_SESSION['admin'] = array('id' => $Qadmin->valueInt('id'),
                        'username' => $Qadmin->value('user_name'),
                        'access' => osC_Access::getUserLevels($Qadmin->valueInt('id')));

                    $token = toc_generate_token();
                    $response = array('success' => true, 'token' => $token);
                    echo $toC_Json->encode($response);
                    exit;
                }
            }
        }

        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_login_invalid'));
        echo $toC_Json->encode($response);
    }

    function logoff()
    {
        global $toC_Json, $osC_Language;
        $event = array();

        $event['content_id'] = $_SESSION['admin']['id'];
        $event['content_type'] = "user";
        $event['event_date'] = date('Y-m-d H:i:s');
        $event['type'] = "info";
        $event['source'] = "cloud";
        $event['user'] = $_SESSION['admin']['username'];
        $event['category'] = "security";
        $event['description'] = "L'utilisateur " . $_SESSION['admin']['username'] . " a ferme une session";

        osC_Categories_Admin::logEvent($event);

        unset($_SESSION['admin']);

        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_logged_out'));

        echo $toC_Json->encode($response);
    }

    function getPassword()
    {
        global $toC_Json, $osC_Language, $osC_Database;

        $error = false;
        $feedback = '';

        $email = $_REQUEST['email_address'];

        if (!osc_validate_email_address($email)) {
            $error = true;
            $feedback = $osC_Language->get('ms_error_wrong_email_address');
        } else if (!osC_Administrators_Admin::checkEmail($email)) {
            $error = true;
            $feedback = $osC_Language->get('ms_error_email_not_exist');
        }

        if ($error === false) {
            if (!osC_Administrators_Admin::generatePassword($email)) {
                $error = true;
                $feedback = $osC_Language->get('ms_error_email_send_failure');
            }
        }

        if ($error == false) {
            $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
            $response = array('success' => false, 'feedback' => $feedback);
        }

        echo $toC_Json->encode($response);
    }
}

?>