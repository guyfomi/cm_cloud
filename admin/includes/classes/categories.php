<?php

if (!class_exists('osC_Roles_Admin')) {
    include('includes/classes/roles.php');
}
if (!class_exists('content')) {
    include('includes/classes/content.php');
}

if (!class_exists('toC_Email_Account')) {
    include('includes/classes/email_account.php');
}

if (!class_exists('toC_Email_Accounts_Admin')) {
    require('includes/classes/email_accounts.php');
}

include('includes/modules/httpful/httpful.phar');

class osC_Categories_Admin
{
    function getData($id, $language_id = null)
    {
        global $osC_Database, $osC_Language, $osC_CategoryTree;

        if (empty($language_id)) {
            $language_id = $osC_Language->getID();
        }

        $Qcategories = $osC_Database->query('select c.*, cd.* from :table_categories c left join :table_categories_description cd on c.categories_id = cd.categories_id where c.categories_id = :categories_id and cd.language_id = :language_id ');
        $Qcategories->bindTable(':table_categories', TABLE_CATEGORIES);
        $Qcategories->bindTable(':table_categories_description', TABLE_CATEGORIES_DESCRIPTION);
        $Qcategories->bindInt(':categories_id', $id);
        $Qcategories->bindInt(':language_id', $language_id);
        $Qcategories->execute();

        $data = $Qcategories->toArray();

        $data['childs_count'] = sizeof($osC_CategoryTree->getChildren($Qcategories->valueInt('categories_id'), $dummy = array()));
        $data['products_count'] = $osC_CategoryTree->getNumberOfProducts($Qcategories->valueInt('categories_id'));

        $cPath = explode('_', $osC_CategoryTree->getFullcPath($Qcategories->valueInt('categories_id')));
        array_pop($cPath);
        $data['parent_category_id'] = implode('_', $cPath);

        $Qcategories->freeResult();

        $Qcategories_ratings = $osC_Database->query('select ratings_id from toc_categories_ratings where categories_id = :categories_id');
        $Qcategories_ratings->bindTable(':toc_categories_ratings', TABLE_CATEGORIES_RATINGS);
        $Qcategories_ratings->bindInt(':categories_id', $id);
        $Qcategories_ratings->execute();

        $ratings = array();
        while ($Qcategories_ratings->next()) {
            $ratings[] = $Qcategories_ratings->ValueInt('ratings_id');
        }
        $data['ratings'] = $ratings;

        $Qcategories_ratings->freeResult();

        return $data;
    }

    function getPlant($id)
    {
        global $osC_Database;

        $Qcategories = $osC_Database->query('select c.*, cd.*,p.* from :table_layout c left join :table_layout_description cd on c.categories_id = cd.categories_id inner join :table_plant p on p.plants_id =c.categories_id where c.categories_id = :categories_id ');
        $Qcategories->bindTable(':table_layout', TABLE_LAYOUT);
        $Qcategories->bindTable(':table_plant', TABLE_PLANTS);
        $Qcategories->bindTable(':table_layout_description', TABLE_LAYOUT_DESCRIPTION);
        $Qcategories->bindInt(':categories_id', $id);
        $Qcategories->execute();

        $data = $Qcategories->toArray();

        //$data['childs_count'] = sizeof($osC_LayoutTree->getChildren($Qcategories->valueInt('categories_id'), $dummy = array()));
        //$data['products_count'] = $osC_CategoryTree->getNumberOfProducts($Qcategories->valueInt('categories_id'));

        //$cPath = explode('_', $osC_LayoutTree->getFullcPath($Qcategories->valueInt('categories_id')));
        //array_pop($cPath);
        //$data['parent_category_id'] = implode('_', $cPath);

        $Qcategories->freeResult();
        $data['url'] = $data['categories_url'];

        return $data;
    }

    function getLine($id)
    {
        global $osC_Database;

        $Qcategories = $osC_Database->query('select c.*, cd.*,l.* from :table_layout c left join :table_layout_description cd on c.categories_id = cd.categories_id inner join :table_lines l on l.lines_id = c.categories_id where c.categories_id = :categories_id ');
        $Qcategories->bindTable(':table_layout', TABLE_LAYOUT);
        $Qcategories->bindTable(':table_lines', TABLE_LINES);
        $Qcategories->bindTable(':table_layout_description', TABLE_LAYOUT_DESCRIPTION);
        $Qcategories->bindInt(':categories_id', $id);
        $Qcategories->execute();

        $data = $Qcategories->toArray();

        $Qcategories->freeResult();

        return $data;
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
                $config['subject'] = "Impossible d'indexer le fichier de parameter " . $data['id'];
                osC_Categories_Admin::sendMail($config);
            }
        }
        else
        {
            $config = array();
            $config['to'] = 'guyfomi@gmail.com';
            $config['body'] = $response;
            $config['subject'] = "Impossible d'indexer le fichier de parameter " . $data['id'];
            osC_Categories_Admin::sendMail($config);
        }

        echo $response;
    }

    function getAsset($id)
    {
        global $osC_Database;

        $Qcategories = $osC_Database->query('select c.*, cd.*,a.* from :table_layout c left join :table_layout_description cd on c.categories_id = cd.categories_id inner join :table_asset a on c.categories_id = a.asset_id where c.categories_id = :categories_id ');
        $Qcategories->bindTable(':table_layout', TABLE_LAYOUT);
        $Qcategories->bindTable(':table_asset', TABLE_ASSET);
        $Qcategories->bindTable(':table_layout_description', TABLE_LAYOUT_DESCRIPTION);
        $Qcategories->bindInt(':categories_id', $id);
        $Qcategories->execute();

        $data = $Qcategories->toArray();

        //$data['childs_count'] = sizeof($osC_LayoutTree->getChildren($Qcategories->valueInt('categories_id'), $dummy = array()));
        //$data['products_count'] = $osC_CategoryTree->getNumberOfProducts($Qcategories->valueInt('categories_id'));

        //$cPath = explode('_', $osC_LayoutTree->getFullcPath($Qcategories->valueInt('categories_id')));
        //array_pop($cPath);
        //$data['parent_category_id'] = implode('_', $cPath);

        $Qcategories->freeResult();

        $data['name'] = $data['categories_name'];
        return $data;
    }

    function getSensor($id)
    {
        global $osC_Database;

        $Qcategories = $osC_Database->query('select c.*, cd.*,s.* from :table_layout c left join :table_layout_description cd on c.categories_id = cd.categories_id inner join :table_sensor s on c.categories_id = s.sensors_id where c.categories_id = :categories_id ');
        $Qcategories->bindTable(':table_layout', TABLE_LAYOUT);
        $Qcategories->bindTable(':table_sensor', TABLE_SENSOR);
        $Qcategories->bindTable(':table_layout_description', TABLE_LAYOUT_DESCRIPTION);
        $Qcategories->bindInt(':categories_id', $id);
        $Qcategories->execute();

        if ($osC_Database->isError()) {
            $_SESSION['error'] = $osC_Database->getError();
            return false;
        }

        //var_dump($Qcategories);

        $data = $Qcategories->toArray();

        $Qcategories->freeResult();

        $data['code'] = $data['categories_name'];
        return $data;
    }

    function getLayout($plant, $line, $asset, $channel)
    {
        global $osC_Database;

        $data = array();

        //$query = "(select l.parent_id from :table_layout l where l.categories_id = (select categories_id from :table_layout_description where lower(categories_name) = lower('" . $plant . "'))) as customers_id,(select categories_id from :table_layout_description where lower(categories_name) = lower('" . $plant . "')) as plants_id,(select l.categories_id from :table_layout l where l.parent_id = (select categories_id from :table_layout_description where lower(categories_name) = lower('" . $plant . "'))) as lines_id,(select categories_id from :table_layout_description where lower(categories_name) = lower('" . $line . "')) as asset_id";
        $query = "select l.parent_id,(select status from :table_status where content_id = l.parent_id) status,(select customers_surname from :table_customers where customers_id = l.parent_id) customers_name from :table_layout l where l.content_type = 'plant' and l.categories_id = (select categories_id from :table_layout_description where lower(categories_name) = lower('" . $plant . "'))";

        $Qcategories = $osC_Database->query($query);
        $Qcategories->bindTable(':table_layout', TABLE_LAYOUT);
        $Qcategories->bindTable(':table_status', TABLE_STATUS);
        $Qcategories->bindTable(':table_customers', TABLE_CUSTOMERS);
        $Qcategories->bindTable(':table_layout_description', TABLE_LAYOUT_DESCRIPTION);
        $Qcategories->execute();

        if (!$osC_Database->isError()) {
            while ($Qcategories->next()) {
                $data['customers_id'] = $Qcategories->valueInt('parent_id');
                $data['customers_current_status'] = $Qcategories->value('status');
                $data['customer'] = $Qcategories->value('customers_name');
            }

            $Qcategories->freeResult();

            if (isset($data['customers_id'])) {
                $query = "select l.categories_id,(select status from :table_status where content_id = l.categories_id) status from :table_layout l where l.content_type = 'plant' and l.parent_id = :customers_id and categories_id = (select categories_id from :table_layout_description where lower(categories_name) = lower('" . $plant . "'))";

                $Qcategories = $osC_Database->query($query);
                $Qcategories->bindTable(':table_layout', TABLE_LAYOUT);
                $Qcategories->bindTable(':table_layout_description', TABLE_LAYOUT_DESCRIPTION);
                $Qcategories->bindTable(':table_status', TABLE_STATUS);
                $Qcategories->bindInt(':customers_id', $data['customers_id']);
                $Qcategories->execute();

                if (!$osC_Database->isError()) {
                    while ($Qcategories->next()) {
                        $data['plants_id'] = $Qcategories->valueInt('categories_id');
                        $data['plants_current_status'] = $Qcategories->value('status');
                    }

                    $Qcategories->freeResult();

                    if (isset($data['plants_id'])) {
                        $query = "select l.categories_id,(select status from :table_status where content_id = l.categories_id) status from :table_layout l where l.content_type = 'line' and l.parent_id = :plants_id and categories_id = (select categories_id from :table_layout_description where lower(categories_name) = lower('" . $line . "'))";

                        $Qcategories = $osC_Database->query($query);
                        $Qcategories->bindTable(':table_layout', TABLE_LAYOUT);
                        $Qcategories->bindTable(':table_layout_description', TABLE_LAYOUT_DESCRIPTION);
                        $Qcategories->bindTable(':table_status', TABLE_STATUS);
                        $Qcategories->bindInt(':plants_id', $data['plants_id']);
                        $Qcategories->execute();

                        if (!$osC_Database->isError()) {
                            while ($Qcategories->next()) {
                                $data['lines_id'] = $Qcategories->valueInt('categories_id');
                                $data['line_current_status'] = $Qcategories->value('status');
                            }

                            $Qcategories->freeResult();

                            if (isset($data['lines_id'])) {
                                $query = "select l.categories_id,(select status from :table_status where content_id = l.categories_id) status,(select administrators_id from delta_asset where asset_id = l.categories_id) administrators_id from :table_layout l where l.content_type = 'asset' and l.parent_id = :lines_id and categories_id = (select categories_id from :table_layout_description where lower(categories_name) = lower('" . $asset . "'))";

                                $Qcategories = $osC_Database->query($query);
                                $Qcategories->bindTable(':table_layout', TABLE_LAYOUT);
                                $Qcategories->bindTable(':table_layout_description', TABLE_LAYOUT_DESCRIPTION);
                                $Qcategories->bindTable(':table_status', TABLE_STATUS);
                                $Qcategories->bindInt(':lines_id', $data['lines_id']);
                                $Qcategories->execute();

                                if (!$osC_Database->isError()) {
                                    while ($Qcategories->next()) {
                                        $data['asset_id'] = $Qcategories->valueInt('categories_id');
                                        $data['administrators_id'] = $Qcategories->valueInt('administrators_id');
                                        $data['asset_current_status'] = $Qcategories->value('status');
                                    }

                                    $Qcategories->freeResult();

                                    if (isset($data['asset_id'])) {
                                        $query = "select l.categories_id,(select status from :table_status where content_id = l.categories_id) status,(select categories_name from :table_layout_desc where categories_id = l.categories_id) component from delta_layout l where l.parent_id = :asset_id and l.content_type = 'component' and l.categories_id = (select component_id from :table_sensors where component_id = l.categories_id and lower(channel) = lower('" . $channel . "'))";

                                        $Qcategories = $osC_Database->query($query);
                                        //$Qcategories->bindTable(':table_layout', TABLE_LAYOUT);
                                        $Qcategories->bindTable(':table_sensors', TABLE_SENSOR);
                                        $Qcategories->bindTable(':table_layout_description', TABLE_LAYOUT_DESCRIPTION);
                                        $Qcategories->bindTable(':table_layout_desc', TABLE_LAYOUT_DESCRIPTION);
                                        $Qcategories->bindTable(':table_status', TABLE_STATUS);
                                        $Qcategories->bindInt(':asset_id', $data['asset_id']);
                                        $Qcategories->execute();

                                        if (!$osC_Database->isError()) {
                                            while ($Qcategories->next()) {
                                                $data['component_id'] = $Qcategories->valueInt('categories_id');
                                                $data['component_current_status'] = $Qcategories->value('status');
                                                $data['component'] = $Qcategories->value('component');
                                            }

                                            $Qcategories->freeResult();

                                            if (isset($data['component_id'])) {
                                                $query = "select l.categories_id,(select status from :table_status where content_id = l.categories_id) status,(select categories_name from :table_layout_desc where categories_id = l.categories_id) sensor from delta_layout l where l.parent_id = :component_id and l.content_type = 'sensor' and l.categories_id = (select sensors_id from :table_sensors where component_id = :component_id1 and lower(channel) = lower('" . $channel . "'))";

                                                $Qcategories = $osC_Database->query($query);
                                                //$Qcategories->bindTable(':table_layout', TABLE_LAYOUT);
                                                $Qcategories->bindTable(':table_sensors', TABLE_SENSOR);
                                                $Qcategories->bindTable(':table_layout_description', TABLE_LAYOUT_DESCRIPTION);
                                                $Qcategories->bindTable(':table_layout_desc', TABLE_LAYOUT_DESCRIPTION);
                                                $Qcategories->bindTable(':table_status', TABLE_STATUS);
                                                $Qcategories->bindInt(':component_id', $data['component_id']);
                                                $Qcategories->bindInt(':component_id1', $data['component_id']);
                                                $Qcategories->execute();

                                                if (!$osC_Database->isError()) {
                                                    while ($Qcategories->next()) {
                                                        $data['sensors_id'] = $Qcategories->valueInt('categories_id');
                                                        $data['sensors_current_status'] = $Qcategories->value('status');
                                                        $data['sensor'] = $Qcategories->value('sensor');
                                                    }

                                                    $Qcategories->freeResult();

                                                    if (isset($data['sensors_id'])) {
                                                        return $data;
                                                    } else {
                                                        $_SESSION['error'] = "Impossible de determiner le code du capteur ...";
                                                        var_dump($Qcategories);
                                                        $Qcategories->freeResult();
                                                        return false;
                                                    }
                                                } else {
                                                    $_SESSION['error'] = "Impossible de recueillir les infos sur le composant ...";
                                                    var_dump($Qcategories);
                                                    $Qcategories->freeResult();
                                                    return false;
                                                }
                                            } else {
                                                $_SESSION['error'] = "Impossible de determiner le code du composant composant ...";
                                                var_dump($Qcategories);
                                                $Qcategories->freeResult();
                                                return false;
                                            }
                                        } else {
                                            $_SESSION['error'] = "Impossible de recueillir les infos sur le composant ...";
                                            var_dump($Qcategories);
                                            $Qcategories->freeResult();
                                            return false;
                                        }
                                    } else {
                                        $_SESSION['error'] = "Impossible de determiner le code de l'asset ...";
                                        var_dump($Qcategories);
                                        $Qcategories->freeResult();
                                        return false;
                                    }
                                } else {
                                    $_SESSION['error'] = "Impossible de recueillir les infos sur l'asset ...";
                                    var_dump($Qcategories);
                                    $Qcategories->freeResult();
                                    return false;
                                }
                            } else {
                                $_SESSION['error'] = "Impossible de determiner le code de la ligne ...";
                                var_dump($Qcategories);
                                $Qcategories->freeResult();
                                return false;
                            }
                        } else {
                            $_SESSION['error'] = "Impossible de recueillir les infos sur la ligne ...";
                            var_dump($Qcategories);
                            $Qcategories->freeResult();
                            return false;
                        }
                    } else {
                        $_SESSION['error'] = "Impossible de determiner le code du Plant ...";
                        var_dump($Qcategories);
                        $Qcategories->freeResult();
                        return false;
                    }
                } else {
                    $_SESSION['error'] = "Impossible de recueillir les infos sur le plant ...";
                    var_dump($Qcategories);
                    $Qcategories->freeResult();
                    return false;
                }
            } else {
                $_SESSION['error'] = "Impossible de determiner le code du client ...";
                var_dump($Qcategories);
                $Qcategories->freeResult();
                return false;
            }
        } else {
            $_SESSION['error'] = "Impossible de recueillir les infos sur le client...";
            var_dump($Qcategories);
            $Qcategories->freeResult();
            return false;
        }
    }

    function getCpms($parameter)
    {
        global $osC_Database;

        $conf = array();
        $event = array();
        $count = 0;

        $query = "select a.asset_id,a.lines_id,(select parent_id from :table_layout where categories_id = a.lines_id) as plants_id from delta_asset a where a.cpms_ip = :cpms_ip";

        $Qcategories = $osC_Database->query($query);
        $Qcategories->bindTable(':table_layout', TABLE_LAYOUT);
        $Qcategories->bindValue(':cpms_ip', $parameter['cpms_ip']);
        $Qcategories->execute();

        if (!$osC_Database->isError()) {
            while ($Qcategories->next()) {
                $conf['asset_id'] = $Qcategories->valueInt('asset_id');
                $conf['lines_id'] = $Qcategories->valueInt('lines_id');
                $conf['plants_id'] = $Qcategories->valueInt('plants_id');
                $count++;
            }

            $Qcategories->freeResult();

            if ($count == 0) {
                $_SESSION['error'] = "Le CPMS IP : " . $parameter['cpms_ip'] . " n'est pas configuré dans le Cloud";

                $event['content_type'] = "cpms";
                $event['type'] = "error";
                $event['event_date'] = date('Y-m-d H:i:s');
                $event['source'] = "cloud";
                $event['user'] = "Importer";
                $event['category'] = "cpms";
                $event['description'] = "Le CPMS IP : " . $parameter['cpms_ip'] . " n'est pas configuré dans le Cloud";

                osC_Categories_Admin::logEvent($event);

                return false;
            }

            $query = "select parent_id from :table_layout where categories_id = :plants_id";

            $Qcategories = $osC_Database->query($query);
            $Qcategories->bindTable(':table_layout', TABLE_LAYOUT);
            $Qcategories->bindInt(':plants_id', $conf['plants_id']);
            $Qcategories->execute();

            if (!$osC_Database->isError()) {
                while ($Qcategories->next()) {
                    $conf['customers_id'] = $Qcategories->valueInt('parent_id');
                }

                $Qcategories->freeResult();

                $conf['cpms_status'] = 'OK';
                $count = 0;

                $query = "select hash,cnt,filectime,datetime,TIMESTAMPDIFF(MINUTE,datetime,:time2) as diff,TIMESTAMPDIFF(MINUTE,filectime,now()) as diff_creation from delta_watchdog where cpms_ip = :cpms_ip";

                $Qcategories = $osC_Database->query($query);
                $Qcategories->bindValue(':cpms_ip', $parameter['cpms_ip']);
                $Qcategories->bindValue(':time2', $parameter['datetime']);
                $Qcategories->execute();

                //var_dump($Qcategories);
                $cnt = 0;

                if (!$osC_Database->isError()) {
                    while ($Qcategories->next()) {
                        //echo $Qcategories->valueInt('diff');
                        //$conf['cpms_status'] = $Qcategories->valueInt('diff') < 1 ? 'error' : 'OK';
                        //if($Qcategories->valueInt('diff_creation') > 5)
                        if ($Qcategories->value('hash') == $parameter['hash']) {
                            if ($Qcategories->valueInt('cnt') > 2) {
                                $conf['cpms_status'] = 'error';
                            } else {
                                $conf['cpms_status'] = 'OK';
                            }

                            $cnt = $Qcategories->valueInt('cnt') + 1;
                        } else {
                            $conf['cpms_status'] = 'OK';
                        }

                        $count++;
                    }

                    $Qcategories->freeResult();

                    if ($count == 0) {
                        $conf['cpms_status'] = 'error';
                    }

                    $query = "delete from delta_watchdog where cpms_ip = :cpms_ip";

                    $Qupdate = $osC_Database->query($query);
                    $Qupdate->bindValue(":cpms_ip", $parameter['cpms_ip']);
                    $Qupdate->execute();

                    if ($osC_Database->isError()) {
                        var_dump($Qupdate);
                        $osC_Database->rollbackTransaction();
                        $_SESSION['error'] = $osC_Database->getError();
                        return false;
                    }

                    $query = "INSERT INTO delta_watchdog (datetime,equipmentstatus,operatingclass,measuringstate,cpms_ip,filectime,hash,cnt) VALUES (:datetime,:equipmentstatus,:operatingclass,:measuringstate,:cpms_ip,:filectime,:hash,:cnt)";

                    $QParameter = $osC_Database->query($query);

                    $QParameter->bindValue(':datetime', $parameter['datetime']);
                    $QParameter->bindValue(':equipmentstatus', $parameter['equipmentstatus']);
                    $QParameter->bindValue(':operatingclass', $parameter['operatingclass']);
                    $QParameter->bindValue(':measuringstate', $parameter['measuringstate']);
                    $QParameter->bindValue(':filectime', $parameter['filectime']);
                    $QParameter->bindValue(':cpms_ip', $parameter['cpms_ip']);
                    $QParameter->bindValue(':hash', $parameter['hash']);
                    $QParameter->bindValue(':cnt', $cnt);

                    $QParameter->execute();

                    return $conf;
                } else {
                    $_SESSION['error'] = "Impossible de determiner le status du cpms ...";
                    var_dump($Qcategories);
                    $Qcategories->freeResult();
                    return false;
                }
            } else {
                $_SESSION['error'] = "Impossible de recueillir les infos sur le customer ...";
                var_dump($Qcategories);
                $Qcategories->freeResult();
                return false;
            }
        } else {
            $_SESSION['error'] = "Impossible de recueillir les infos sur le cpms...";
            var_dump($Qcategories);
            $Qcategories->freeResult();
            return false;
        }
    }

    function getComponent($id)
    {
        global $osC_Database;

        $Qcategories = $osC_Database->query('select c.*, cd.*,s.* from :table_layout c left join :table_layout_description cd on c.categories_id = cd.categories_id inner join :table_component s on c.categories_id = s.component_id where c.categories_id = :categories_id ');
        $Qcategories->bindTable(':table_layout', TABLE_LAYOUT);
        $Qcategories->bindTable(':table_component', TABLE_COMPONENT);
        $Qcategories->bindTable(':table_layout_description', TABLE_LAYOUT_DESCRIPTION);
        $Qcategories->bindInt(':categories_id', $id);
        $Qcategories->execute();

        //var_dump($Qcategories);

        $data = $Qcategories->toArray();

        $Qcategories->freeResult();

        //$data['name'] = $data['categories_name'];
        return $data;
    }

    function save($id = null, $data)
    {
        global $osC_Database, $osC_Language;

        $category_id = '';
        $error = false;

        $osC_Database->startTransaction();

        if (is_numeric($id)) {
            $Qcat = $osC_Database->query('update :table_categories set categories_status = :categories_status, sort_order = :sort_order, last_modified = now(),parent_id = :parent_id where categories_id = :categories_id');
            $Qcat->bindInt(':categories_id', $id);
        } else {
            $Qcat = $osC_Database->query('insert into :table_categories (parent_id, categories_status, sort_order, date_added) values (:parent_id, :categories_status, :sort_order, now())');
        }

        $Qcat->bindTable(':table_categories', TABLE_CATEGORIES);
        $Qcat->bindInt(':parent_id', $data['parent_id']);
        $Qcat->bindInt(':sort_order', $data['sort_order']);
        $Qcat->bindInt(':categories_status', $data['categories_status']);
        $Qcat->setLogging($_SESSION['module'], $id);
        $Qcat->execute();

        if (!$osC_Database->isError()) {
            $category_id = (is_numeric($id)) ? $id : $osC_Database->nextID();

            if (is_numeric($id)) {
                if ($data['categories_status']) {
                    $Qpstatus = $osC_Database->query('update :table_products set products_status = 1 where products_id in (select products_id from :table_products_to_categories where categories_id = :categories_id)');
                    $Qpstatus->bindTable(':table_products', TABLE_PRODUCTS);
                    $Qpstatus->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
                    $Qpstatus->bindInt(":categories_id", $id);
                    $Qpstatus->execute();
                } else {
                    if ($data['flag']) {
                        $Qpstatus = $osC_Database->query('update :table_products set products_status = 0 where products_id in (select products_id from :table_products_to_categories where categories_id = :categories_id)');
                        $Qpstatus->bindTable(':table_products', TABLE_PRODUCTS);
                        $Qpstatus->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
                        $Qpstatus->bindInt(":categories_id", $id);
                        $Qpstatus->execute();
                    }
                }
            }

            if ($osC_Database->isError()) {
                $error = true;
            }

            foreach ($osC_Language->getAll() as $l) {
                if (is_numeric($id)) {
                    $Qcd = $osC_Database->query('update :table_categories_description set categories_name = :categories_name, categories_url = :categories_url, categories_page_title = :categories_page_title, categories_meta_keywords = :categories_meta_keywords, categories_meta_description = :categories_meta_description where categories_id = :categories_id and language_id = :language_id');
                } else {
                    $Qcd = $osC_Database->query('insert into :table_categories_description (categories_id, language_id, categories_name, categories_url, categories_page_title, categories_meta_keywords, categories_meta_description) values (:categories_id, :language_id, :categories_name, :categories_url, :categories_page_title, :categories_meta_keywords, :categories_meta_description)');
                }

                $Qcd->bindTable(':table_categories_description', TABLE_CATEGORIES_DESCRIPTION);
                $Qcd->bindInt(':categories_id', $category_id);
                $Qcd->bindInt(':language_id', $l['id']);
                $Qcd->bindValue(':categories_name', $data['name'][$l['id']]);
                $Qcd->bindValue(':categories_url', ($data['url'][$l['id']] == '') ? $data['name'][$l['id']]
                    : $data['url'][$l['id']]);
                $Qcd->bindValue(':categories_page_title', $data['page_title'][$l['id']]);
                $Qcd->bindValue(':categories_meta_keywords', $data['meta_keywords'][$l['id']]);
                $Qcd->bindValue(':categories_meta_description', $data['meta_description'][$l['id']]);
                $Qcd->setLogging($_SESSION['module'], $category_id);
                $Qcd->execute();

                if ($osC_Database->isError()) {
                    $error = true;
                    break;
                }
            }

            $Qdelete = $osC_Database->query('delete from :toc_categories_ratings where categories_id = :categories_id');
            $Qdelete->bindTable(':toc_categories_ratings', TABLE_CATEGORIES_RATINGS);
            $Qdelete->bindInt(':categories_id', $category_id);
            $Qdelete->execute();

            if (!empty($data['ratings'])) {
                $ratings = explode(',', $data['ratings']);

                foreach ($ratings as $ratings_id) {
                    $Qinsert = $osC_Database->query('insert into :toc_categories_ratings (categories_id, ratings_id) values (:categories_id, :ratings_id)');
                    $Qinsert->bindTable(':toc_categories_ratings', TABLE_CATEGORIES_RATINGS);
                    $Qinsert->bindInt(':categories_id', $category_id);
                    $Qinsert->bindInt(':ratings_id', $ratings_id);
                    $Qinsert->execute();

                    if ($osC_Database->isError()) {
                        $error = true;
                        break;
                    }
                }
            }

            if ($error === false) {
                $categories_image = new upload($data['image'], realpath('../' . DIR_WS_IMAGES . 'categories'));

                if ($categories_image->exists() && $categories_image->parse() && $categories_image->save()) {

                    $Qimage = $osC_Database->query('select categories_image from :table_categories where categories_id = :categories_id');
                    $Qimage->bindTable(':table_categories', TABLE_CATEGORIES);
                    $Qimage->bindInt(':categories_id', $category_id);
                    $Qimage->execute();

                    $old_image = $Qimage->value('categories_image');

                    if (!empty($old_image)) {
                        $Qcheck = $osC_Database->query('select count(*) as image_count from :table_categories where categories_image = :categories_image');
                        $Qcheck->bindTable(':table_categories', TABLE_CATEGORIES);
                        $Qcheck->bindValue(':categories_image', $old_image);
                        $Qcheck->execute();

                        if ($Qcheck->valueInt('image_count') == 1) {
                            $path = realpath('../' . DIR_WS_IMAGES . 'categories') . '/' . $old_image;
                            unlink($path);
                        }
                    }

                    $Qcf = $osC_Database->query('update :table_categories set categories_image = :categories_image where categories_id = :categories_id');
                    $Qcf->bindTable(':table_categories', TABLE_CATEGORIES);
                    $Qcf->bindValue(':categories_image', $categories_image->filename);
                    $Qcf->bindInt(':categories_id', $category_id);
                    $Qcf->setLogging($_SESSION['module'], $category_id);
                    $Qcf->execute();

                    if ($osC_Database->isError()) {
                        $error = true;
                    }
                }
            }
        }

        if ($error === false) {
            $osC_Database->commitTransaction();

            osC_Cache::clear('categories');
            osC_Cache::clear('category_tree');
            osC_Cache::clear('also_purchased');

            return $category_id;
        }

        $osC_Database->rollbackTransaction();

        return false;
    }

    function savePlant($id = null, $data)
    {
        global $osC_Database;

        $category_id = '';
        $error = false;

        $osC_Database->startTransaction();

        if (is_numeric($id)) {
            $Qcat = $osC_Database->query('update :table_layout set categories_status = :categories_status, sort_order = :sort_order, last_modified = now(),parent_id = :parent_id where categories_id = :categories_id');
            $Qcat->bindInt(':categories_id', $id);
        } else {
            $Qcat = $osC_Database->query('insert into :table_layout (parent_id, categories_status, sort_order, date_added,content_type) values (:parent_id, :categories_status, :sort_order, now(),:content_type)');
        }

        $Qcat->bindTable(':table_layout', TABLE_LAYOUT);
        $Qcat->bindInt(':parent_id', $data['customers_id']);
        $Qcat->bindInt(':sort_order', 0);
        $Qcat->bindInt(':categories_status', 1);
        $Qcat->bindValue(':content_type', 'plant');
        $Qcat->setLogging($_SESSION['module'], $id);
        $Qcat->execute();

        if (!$osC_Database->isError()) {
            $category_id = (is_numeric($id)) ? $id : $osC_Database->nextID();

            if (is_numeric($id)) {
                if ($data['categories_status']) {
                    $Qpstatus = $osC_Database->query('update :table_products set products_status = 1 where products_id in (select products_id from :table_products_to_categories where categories_id = :categories_id)');
                    $Qpstatus->bindTable(':table_products', TABLE_PRODUCTS);
                    $Qpstatus->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
                    $Qpstatus->bindInt(":categories_id", $id);
                    $Qpstatus->execute();
                } else {
                    if ($data['flag']) {
                        $Qpstatus = $osC_Database->query('update :table_products set products_status = 0 where products_id in (select products_id from :table_products_to_categories where categories_id = :categories_id)');
                        $Qpstatus->bindTable(':table_products', TABLE_PRODUCTS);
                        $Qpstatus->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
                        $Qpstatus->bindInt(":categories_id", $id);
                        $Qpstatus->execute();
                    }
                }
            }

            if ($osC_Database->isError()) {
                $error = true;
            }

            if ($data['image']) {
                if ($error === false) {
                    $categories_image = new upload($data['image'], realpath('../' . DIR_WS_IMAGES . 'categories'));

                    if ($categories_image->exists() && $categories_image->parse() && $categories_image->save()) {

                        $Qimage = $osC_Database->query('select categories_image from :table_categories where categories_id = :categories_id');
                        $Qimage->bindTable(':table_categories', TABLE_CATEGORIES);
                        $Qimage->bindInt(':categories_id', $category_id);
                        $Qimage->execute();

                        $old_image = $Qimage->value('categories_image');

                        if (!empty($old_image)) {
                            $Qcheck = $osC_Database->query('select count(*) as image_count from :table_categories where categories_image = :categories_image');
                            $Qcheck->bindTable(':table_categories', TABLE_CATEGORIES);
                            $Qcheck->bindValue(':categories_image', $old_image);
                            $Qcheck->execute();

                            if ($Qcheck->valueInt('image_count') == 1) {
                                $path = realpath('../' . DIR_WS_IMAGES . 'categories') . '/' . $old_image;
                                unlink($path);
                            }
                        }

                        $Qcf = $osC_Database->query('update :table_categories set categories_image = :categories_image where categories_id = :categories_id');
                        $Qcf->bindTable(':table_categories', TABLE_CATEGORIES);
                        $Qcf->bindValue(':categories_image', $categories_image->filename);
                        $Qcf->bindInt(':categories_id', $category_id);
                        $Qcf->setLogging($_SESSION['module'], $category_id);
                        $Qcf->execute();

                        if ($osC_Database->isError()) {
                            $error = true;
                        }
                    }
                }
            }
        } else {
            $error = true;
            $_SESSION['error'] = $osC_Database->error;
        }

        if (is_numeric($id)) {
            $Qcd = $osC_Database->query('update :table_layout_description set categories_name = :categories_name, categories_url = :categories_url, categories_page_title = :categories_page_title, categories_meta_keywords = :categories_meta_keywords, categories_meta_description = :categories_meta_description where categories_id = :categories_id ');
        } else {
            $Qcd = $osC_Database->query('insert into :table_layout_description (categories_id, categories_name, categories_url, categories_page_title, categories_meta_keywords, categories_meta_description) values (:categories_id, :categories_name, :categories_url, :categories_page_title, :categories_meta_keywords, :categories_meta_description)');
        }

        $Qcd->bindTable(':table_layout_description', TABLE_LAYOUT_DESCRIPTION);
        $Qcd->bindInt(':categories_id', $category_id);
        $Qcd->bindValue(':categories_name', $data['categories_name']);
        $Qcd->bindValue(':categories_url', $data['url']);
        $Qcd->bindValue(':categories_page_title', '');
        $Qcd->bindValue(':categories_meta_keywords', '');
        $Qcd->bindValue(':categories_meta_description', '');
        $Qcd->setLogging($_SESSION['module'], $category_id);
        $Qcd->execute();

        if ($osC_Database->isError()) {
            $error = true;
            $_SESSION['error'] = $osC_Database->error;
        }

        if (is_numeric($id)) {
            $Qplants = $osC_Database->query('update :table_plants set code = :code, location = :location, manufacturer = :manufacturer, model = :model, serial_number = :serial_number,operator=:operator,adresse=:adresse,country_id=:country_id,email=:email,phone=:phone,mobile=:mobile,fax=:fax,comments=:comments,customers_id=:customers_id,longitude = :longitude,latitude = :latitude where plants_id = :plants_id ');
        } else {
            $Qplants = $osC_Database->query('insert into :table_plants (plants_id,code, location, manufacturer, model, serial_number, operator,adresse,country_id,email,phone,mobile,fax,comments,customers_id,longitude,latitude) values (:plants_id,:code, :location, :manufacturer, :model, :serial_number, :operator,:adresse,:country_id,:email,:phone,:mobile,:fax,:comments,:customers_id,:longitude,:latitude)');
        }

        $Qplants->bindTable(':table_plants', TABLE_PLANTS);
        $Qplants->bindInt(':plants_id', $category_id);
        $Qplants->bindValue(':code', $data['code']);
        $Qplants->bindValue(':location', $data['location']);
        $Qplants->bindValue(':manufacturer', $data['location']);
        $Qplants->bindValue(':model', $data['model']);
        $Qplants->bindValue(':serial_number', $data['serial_number']);
        $Qplants->bindValue(':adresse', $data['adresse']);
        $Qplants->bindInt(':country_id', $data['country_id']);
        $Qplants->bindValue(':email', $data['email']);
        $Qplants->bindValue(':operator', $data['operator']);
        $Qplants->bindValue(':phone', $data['phone']);
        $Qplants->bindValue(':mobile', $data['mobile']);
        $Qplants->bindValue(':fax', $data['fax']);
        $Qplants->bindValue(':longitude', $data['longitude']);
        $Qplants->bindValue(':latitude', $data['latitude']);
        $Qplants->bindValue(':comments', $data['comments']);
        $Qplants->bindInt(':customers_id', $data['customers_id']);
        $Qplants->setLogging($_SESSION['module'], $category_id);
        $Qplants->execute();

        //var_dump($osC_Database);

        if ($osC_Database->isError()) {
            $error = true;
            $_SESSION['error'] = $osC_Database->error;

            $event['content_id'] = $category_id;
            $event['content_type'] = "plant";
            $event['event_date'] = date('Y-m-d H:i:s');
            $event['type'] = "error";
            $event['source'] = "cloud";
            $event['user'] = $_SESSION['admin']['username'];
            $event['category'] = "plant";

            if (is_numeric($id)) {
                $event['description'] = "Le Plant " . $data['categories_name'] . " n'a pas pu etre modifie par . " . $_SESSION['admin']['username'] . " ... raison : " . $osC_Database->error;
            } else {
                $event['description'] = "Le Plant " . $data['categories_name'] . " n'a pas pu etre ajoute par . " . $_SESSION['admin']['username'] . " ... raison : " . $osC_Database->error;
            }

            osC_Categories_Admin::logEvent($event);
        }

        if ($error === false) {
            $osC_Database->commitTransaction();

            osC_Cache::clear('layout');
            osC_Cache::clear('layout_tree');

            $event['content_id'] = $category_id;
            $event['content_type'] = "plant";
            $event['event_date'] = date('Y-m-d H:i:s');
            $event['type'] = "succes";
            $event['source'] = "cloud";
            $event['user'] = $_SESSION['admin']['username'];
            $event['category'] = "plant";

            if (is_numeric($id)) {
                $event['description'] = "Le Plant " . $data['categories_name'] . " a ete modifie par . " . $_SESSION['admin']['username'];
            } else {
                $event['description'] = "Le Plant " . $data['categories_name'] . " a ete ajoute par . " . $_SESSION['admin']['username'];
            }

            osC_Categories_Admin::logEvent($event);

            return $category_id;
        }

        $osC_Database->rollbackTransaction();

        return false;
    }

    function saveLine($id = null, $data)
    {
        global $osC_Database;

        $category_id = '';
        $error = false;

        $osC_Database->startTransaction();

        if (is_numeric($id)) {
            $Qcat = $osC_Database->query('update :table_layout set categories_status = :categories_status, sort_order = :sort_order, last_modified = now(),parent_id = :parent_id where categories_id = :categories_id');
            $Qcat->bindInt(':categories_id', $id);
        } else {
            $Qcat = $osC_Database->query('insert into :table_layout (parent_id, categories_status, sort_order, date_added,content_type) values (:parent_id, :categories_status, :sort_order, now(),:content_type)');
        }

        $Qcat->bindTable(':table_layout', TABLE_LAYOUT);
        $Qcat->bindInt(':parent_id', $data['plants_id']);
        $Qcat->bindInt(':sort_order', 0);
        $Qcat->bindInt(':categories_status', 1);
        $Qcat->bindValue(':content_type', 'line');
        $Qcat->setLogging($_SESSION['module'], $id);
        $Qcat->execute();

        if (!$osC_Database->isError()) {
            $category_id = (is_numeric($id)) ? $id : $osC_Database->nextID();

            if (is_numeric($id)) {
                if ($data['categories_status']) {
                    $Qpstatus = $osC_Database->query('update :table_products set products_status = 1 where products_id in (select products_id from :table_products_to_categories where categories_id = :categories_id)');
                    $Qpstatus->bindTable(':table_products', TABLE_PRODUCTS);
                    $Qpstatus->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
                    $Qpstatus->bindInt(":categories_id", $id);
                    $Qpstatus->execute();
                } else {
                    if ($data['flag']) {
                        $Qpstatus = $osC_Database->query('update :table_products set products_status = 0 where products_id in (select products_id from :table_products_to_categories where categories_id = :categories_id)');
                        $Qpstatus->bindTable(':table_products', TABLE_PRODUCTS);
                        $Qpstatus->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
                        $Qpstatus->bindInt(":categories_id", $id);
                        $Qpstatus->execute();
                    }
                }
            }

            if ($osC_Database->isError()) {
                $error = true;
            }

            if ($data['image']) {
                if ($error === false) {
                    $categories_image = new upload($data['image'], realpath('../' . DIR_WS_IMAGES . 'categories'));

                    if ($categories_image->exists() && $categories_image->parse() && $categories_image->save()) {

                        $Qimage = $osC_Database->query('select categories_image from :table_categories where categories_id = :categories_id');
                        $Qimage->bindTable(':table_categories', TABLE_CATEGORIES);
                        $Qimage->bindInt(':categories_id', $category_id);
                        $Qimage->execute();

                        $old_image = $Qimage->value('categories_image');

                        if (!empty($old_image)) {
                            $Qcheck = $osC_Database->query('select count(*) as image_count from :table_categories where categories_image = :categories_image');
                            $Qcheck->bindTable(':table_categories', TABLE_CATEGORIES);
                            $Qcheck->bindValue(':categories_image', $old_image);
                            $Qcheck->execute();

                            if ($Qcheck->valueInt('image_count') == 1) {
                                $path = realpath('../' . DIR_WS_IMAGES . 'categories') . '/' . $old_image;
                                unlink($path);
                            }
                        }

                        $Qcf = $osC_Database->query('update :table_categories set categories_image = :categories_image where categories_id = :categories_id');
                        $Qcf->bindTable(':table_categories', TABLE_CATEGORIES);
                        $Qcf->bindValue(':categories_image', $categories_image->filename);
                        $Qcf->bindInt(':categories_id', $category_id);
                        $Qcf->setLogging($_SESSION['module'], $category_id);
                        $Qcf->execute();

                        if ($osC_Database->isError()) {
                            $error = true;
                        }
                    }
                }
            }
        } else {
            $error = true;
            $_SESSION['error'] = $osC_Database->error;
        }

        if (is_numeric($id)) {
            $Qcd = $osC_Database->query('update :table_layout_description set categories_name = :categories_name, categories_url = :categories_url, categories_page_title = :categories_page_title, categories_meta_keywords = :categories_meta_keywords, categories_meta_description = :categories_meta_description where categories_id = :categories_id ');
        } else {
            $Qcd = $osC_Database->query('insert into :table_layout_description (categories_id, categories_name, categories_url, categories_page_title, categories_meta_keywords, categories_meta_description) values (:categories_id, :categories_name, :categories_url, :categories_page_title, :categories_meta_keywords, :categories_meta_description)');
        }

        $Qcd->bindTable(':table_layout_description', TABLE_LAYOUT_DESCRIPTION);
        $Qcd->bindInt(':categories_id', $category_id);
        $Qcd->bindValue(':categories_name', $data['name']);
        $Qcd->bindValue(':categories_url', '');
        $Qcd->bindValue(':categories_page_title', '');
        $Qcd->bindValue(':categories_meta_keywords', '');
        $Qcd->bindValue(':categories_meta_description', '');
        $Qcd->setLogging($_SESSION['module'], $category_id);
        $Qcd->execute();

        if ($osC_Database->isError()) {
            $error = true;
            $_SESSION['error'] = $osC_Database->error;
        }

        if (is_numeric($id)) {
            $Qplants = $osC_Database->query('update :table_lines set plants_id = :plants_id, code = :code,name = :name, unit = :unit, building = :building, operator = :operator where lines_id = :lines_id ');
        } else {
            $Qplants = $osC_Database->query('insert into :table_lines (lines_id,plants_id,code,name, unit, building, operator) values (:lines_id,:plants_id,:code,:name, :unit, :building, :operator)');
        }

        $Qplants->bindTable(':table_lines', TABLE_LINES);
        $Qplants->bindInt(':lines_id', $category_id);
        $Qplants->bindValue(':code', $data['code']);
        $Qplants->bindValue(':name', $data['name']);
        $Qplants->bindValue(':unit', $data['unit']);
        $Qplants->bindValue(':building', $data['building']);
        $Qplants->bindValue(':operator', $data['operator']);
        $Qplants->bindInt(':plants_id', $data['plants_id']);
        $Qplants->setLogging($_SESSION['module'], $category_id);
        $Qplants->execute();

        //var_dump($osC_Database);

        if ($osC_Database->isError()) {
            $error = true;
            $_SESSION['error'] = $osC_Database->error;

            $event['content_id'] = $category_id;
            $event['content_type'] = "line";
            $event['event_date'] = date('Y-m-d H:i:s');
            $event['type'] = "error";
            $event['source'] = "cloud";
            $event['user'] = $_SESSION['admin']['username'];
            $event['category'] = "line";

            if (is_numeric($id)) {
                $event['description'] = "La ligne " . $data['name'] . " n'a pas pu etre modifiee par . " . $_SESSION['admin']['username'] . " ... raison : " . $osC_Database->error;
            } else {
                $event['description'] = "La ligne " . $data['name'] . " n'a pas pu etre ajoutee par . " . $_SESSION['admin']['username'] . " ... raison : " . $osC_Database->error;
            }

            osC_Categories_Admin::logEvent($event);
        }

        if ($error === false) {
            $osC_Database->commitTransaction();

            osC_Cache::clear('layout');
            osC_Cache::clear('layout_tree');

            $event['content_id'] = $category_id;
            $event['content_type'] = "line";
            $event['event_date'] = date('Y-m-d H:i:s');
            $event['type'] = "succes";
            $event['source'] = "cloud";
            $event['user'] = $_SESSION['admin']['username'];
            $event['category'] = "line";

            if (is_numeric($id)) {
                $event['description'] = "La ligne " . $data['name'] . " a ete modifiee par . " . $_SESSION['admin']['username'];
            } else {
                $event['description'] = "La ligne " . $data['name'] . " a ete ajoutee par . " . $_SESSION['admin']['username'];
            }

            osC_Categories_Admin::logEvent($event);

            return $category_id;
        }

        $osC_Database->rollbackTransaction();

        return false;
    }

    function logImport($data)
    {
        global $osC_Database;

        $error = false;

        $osC_Database->startTransaction();

        $query = "INSERT INTO :table_imports (status, comments, plant, file) VALUES (:status, :comments, :plant, :file)";

        $Qplog = $osC_Database->query($query);
        $Qplog->bindTable(':table_imports', TABLE_IMPORTS);
        $Qplog->bindValue(':status', $data['status']);
        $Qplog->bindValue(':comments', $data['comments']);
        $Qplog->bindValue(':plant', $data['plant']);
        $Qplog->bindValue(':file', $data['file']);
        $Qplog->execute();

        if ($osC_Database->isError()) {
            $error = true;
            $_SESSION['error'] = $osC_Database->error;
        }

        if ($error === false) {
            $osC_Database->commitTransaction();

            return true;
        }

        $osC_Database->rollbackTransaction();

        return false;
    }

    function logEvent($event)
    {
        global $osC_Database;

        if (isset($event['content_id'])) {
            $osC_Database->selectDatabase(DB_DATABASE);

            $error = false;

            $query = "INSERT INTO delta_eventlog (event_date, content_id, content_type, type, source, user, category, description) VALUES (:event_date, :content_id, :content_type, :type, :source, :user, :category, :description)";

            $osC_Database->startTransaction();

            $Qplog = $osC_Database->query($query);
            //$Qplog->bindTable(':table_eventlog', TABLE_EVENTLOG);
            $Qplog->bindValue(':event_date', $event['event_date']);
            $Qplog->bindInt(':content_id', $event['content_id']);
            $Qplog->bindValue(':content_type', $event['content_type']);
            $Qplog->bindValue(':type', $event['type']);
            $Qplog->bindValue(':source', $event['source']);
            $Qplog->bindValue(':user', $event['user']);
            $Qplog->bindValue(':category', $event['category']);
            $Qplog->bindValue(':description', $event['description']);
            $Qplog->execute();

            if ($osC_Database->isError()) {
                $error = true;
                $_SESSION['error'] = $osC_Database->error;
            }

            if ($error === false) {
                $osC_Database->commitTransaction();

                return true;
            }

            $osC_Database->rollbackTransaction();
        }

        return false;
    }

    function saveComponent($id = null, $data)
    {
        global $osC_Database;

        $category_id = '';
        $error = false;

        $osC_Database->startTransaction();

        if (is_numeric($id)) {
            $Qcat = $osC_Database->query('update :table_layout set categories_status = :categories_status, sort_order = :sort_order, last_modified = now(),parent_id = :parent_id where categories_id = :categories_id');
            $Qcat->bindInt(':categories_id', $id);
        } else {
            $Qcat = $osC_Database->query('insert into :table_layout (parent_id, categories_status, sort_order, date_added,content_type) values (:parent_id, :categories_status, :sort_order, now(),:content_type)');
        }

        $Qcat->bindTable(':table_layout', TABLE_LAYOUT);
        $Qcat->bindInt(':parent_id', $data['asset_id']);
        $Qcat->bindInt(':sort_order', 0);
        $Qcat->bindInt(':categories_status', 1);
        $Qcat->bindValue(':content_type', 'component');
        $Qcat->setLogging($_SESSION['module'], $id);
        $Qcat->execute();

        if (!$osC_Database->isError()) {
            $category_id = (is_numeric($id)) ? $id : $osC_Database->nextID();

            if (is_numeric($id)) {
                if (isset($data['categories_status']) && !empty($data['categories_status'])) {
                    $Qpstatus = $osC_Database->query('update :table_products set products_status = 1 where products_id in (select products_id from :table_products_to_categories where categories_id = :categories_id)');
                    $Qpstatus->bindTable(':table_products', TABLE_PRODUCTS);
                    $Qpstatus->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
                    $Qpstatus->bindInt(":categories_id", $id);
                    $Qpstatus->execute();
                } else {
                    if (isset($data['flag']) && !empty($data['flag'])) {
                        $Qpstatus = $osC_Database->query('update :table_products set products_status = 0 where products_id in (select products_id from :table_products_to_categories where categories_id = :categories_id)');
                        $Qpstatus->bindTable(':table_products', TABLE_PRODUCTS);
                        $Qpstatus->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
                        $Qpstatus->bindInt(":categories_id", $id);
                        $Qpstatus->execute();
                    }
                }
            }

            if ($osC_Database->isError()) {
                $error = true;
            }

            if (isset($data['image']) && !empty($data['image'])) {
                if ($error === false) {
                    $categories_image = new upload($data['image'], realpath('../' . DIR_WS_IMAGES . 'categories'));

                    if ($categories_image->exists() && $categories_image->parse() && $categories_image->save()) {

                        $Qimage = $osC_Database->query('select categories_image from :table_categories where categories_id = :categories_id');
                        $Qimage->bindTable(':table_categories', TABLE_CATEGORIES);
                        $Qimage->bindInt(':categories_id', $category_id);
                        $Qimage->execute();

                        $old_image = $Qimage->value('categories_image');

                        if (!empty($old_image)) {
                            $Qcheck = $osC_Database->query('select count(*) as image_count from :table_categories where categories_image = :categories_image');
                            $Qcheck->bindTable(':table_categories', TABLE_CATEGORIES);
                            $Qcheck->bindValue(':categories_image', $old_image);
                            $Qcheck->execute();

                            if ($Qcheck->valueInt('image_count') == 1) {
                                $path = realpath('../' . DIR_WS_IMAGES . 'categories') . '/' . $old_image;
                                unlink($path);
                            }
                        }

                        $Qcf = $osC_Database->query('update :table_categories set categories_image = :categories_image where categories_id = :categories_id');
                        $Qcf->bindTable(':table_categories', TABLE_CATEGORIES);
                        $Qcf->bindValue(':categories_image', $categories_image->filename);
                        $Qcf->bindInt(':categories_id', $category_id);
                        $Qcf->setLogging($_SESSION['module'], $category_id);
                        $Qcf->execute();

                        if ($osC_Database->isError()) {
                            $error = true;
                        }
                    }
                }
            }
        } else {
            $error = true;
            $_SESSION['error'] = $osC_Database->error;
        }

        if (is_numeric($id)) {
            $Qcd = $osC_Database->query('update :table_layout_description set categories_name = :categories_name, categories_url = :categories_url, categories_page_title = :categories_page_title, categories_meta_keywords = :categories_meta_keywords, categories_meta_description = :categories_meta_description where categories_id = :categories_id ');
        } else {
            $Qcd = $osC_Database->query('insert into :table_layout_description (categories_id, categories_name, categories_url, categories_page_title, categories_meta_keywords, categories_meta_description) values (:categories_id, :categories_name, :categories_url, :categories_page_title, :categories_meta_keywords, :categories_meta_description)');
        }

        $Qcd->bindTable(':table_layout_description', TABLE_LAYOUT_DESCRIPTION);
        $Qcd->bindInt(':categories_id', $category_id);
        $Qcd->bindValue(':categories_name', $data['name']);
        $Qcd->bindValue(':categories_url', '');
        $Qcd->bindValue(':categories_page_title', '');
        $Qcd->bindValue(':categories_meta_keywords', '');
        $Qcd->bindValue(':categories_meta_description', '');
        $Qcd->setLogging($_SESSION['module'], $category_id);
        $Qcd->execute();

        if ($osC_Database->isError()) {
            $error = true;
            $_SESSION['error'] = $osC_Database->error;
        }

        if (is_numeric($id)) {
            $QComponent = $osC_Database->query('UPDATE delta_component SET asset_id=:asset_id,serial=:serial,model=:model,name=:name,function=:function,firstnaturalfrequency=:firstnaturalfrequency,secondnaturalfrequency=:secondnaturalfrequency,thirdnaturalfrequency=:thirdnaturalfrequency,rollingbearing=:rollingbearing,rollingbearingwidth_m=:rollingbearingwidth_m,rollingbearingdiameter_m=:rollingbearingdiameter_m,numberrollingelements=:numberrollingelements,rollingbearingcontactangle_grad=:rollingbearingcontactangle_grad,outerringfrequency=:outerringfrequency,innerringfrequency=:innerringfrequency,cagefrequency=:cagefrequency,rollingelementrotationfrequency=:rollingelementrotationfrequency,rollingelementcontactfrequency=:rollingelementcontactfrequency,journalbearing=:journalbearing,journalbearingfluidtype=:journalbearingfluidtype,journalbearinggap_um=:journalbearinggap_um,oilwhirlminorder=:oilwhirlminorder,oilwhirlmaxorder=:oilwhirlmaxorder,minfluidtemperature_c=:minfluidtemperature_c,maxfluidtemperature_c=:maxfluidtemperature_c,minfluidpressure_bar=:minfluidpressure_bar,turbomachinery=:turbomachinery,bladesnumber=:bladesnumber,vanesnumber=:vanesnumber,bladelength_m=:bladelength_m,bladepassfrequency=:bladepassfrequency,bladetipfrequency=:bladetipfrequency,vanepassingfrequency=:vanepassingfrequency,bladevanepassingfrequency=:bladevanepassingfrequency,gear=:gear,geartype=:geartype,gearratio=:gearratio,gearnumberstages=:gearnumberstages,gearlowspeedshaftteethnumber=:gearlowspeedshaftteethnumber,gearfastspeedshaftteethnumber=:gearfastspeedshaftteethnumber,gearringteethnumber=:gearringteethnumber,gearplanetteethnumber=:gearplanetteethnumber,gearplanetarycarrierteethnumber=:gearplanetarycarrierteethnumber,gearfixedcomponent=:gearfixedcomponent,gearsunfrequency=:gearsunfrequency,gearringfrequency=:gearringfrequency,gearplanetfrequency=:gearplanetfrequency,gearmeshfrequency=:gearmeshfrequency,gearteethcommonfactor=:gearteethcommonfactor,gearhuntingtoothfrequency=:gearhuntingtoothfrequency,gearassemblyphase=:gearassemblyphase,gearghostfrequency=:gearghostfrequency,belt=:belt,beltdiameterd1_m=:beltdiameterd1_m,beltdiameterd2_m=:beltdiameterd2_m,beltaxialgap_m=:beltaxialgap_m,beltteethnumberz1=:beltteethnumberz1,beltteethnumberz2=:beltteethnumberz2,beltlength_m=:beltlength_m,beltspeedn1_rpm=:beltspeedn1_rpm,beltspeedn2_rpm=:beltspeedn2_rpm,beltfrequency=:beltfrequency,timingbeltfrequency=:timingbeltfrequency,motor_generator=:motor_generator,motorpolepairs=:motorpolepairs,motorrotorbars=:motorrotorbars,motorstatorpoles=:motorstatorpoles,motorstatorslots=:motorstatorslots,motorcoilsperpole=:motorcoilsperpole,motorlineoffrequency=:motorlineoffrequency,motorsynchronuousspeedfrequency=:motorsynchronuousspeedfrequency,motorrunningspeedfrequency=:motorrunningspeedfrequency,motorslipfrequency=:motorslipfrequency,motorslipratio=:motorslipratio,motorpolepassfrequency=:motorpolepassfrequency,motorslotpassfrequency=:motorslotpassfrequency,motorrotorbarfrequency=:motorrotorbarfrequency,motorstatorslotfrequency=:motorstatorslotfrequency,motorcommutatorfrequency=:motorcommutatorfrequency,motorstaticeccentricityfrequency=:motorstaticeccentricityfrequency,motordynamiceccentricity=:motordynamiceccentricity,motorstatormechanicaldamagefrequency=:motorstatormechanicaldamagefrequency,motorrotordefectfrequency=:motorrotordefectfrequency,motorloosestatorcoilfrequency=:motorloosestatorcoilfrequency where component_id = :component_id ');
        } else {
            $QComponent = $osC_Database->query('INSERT INTO delta_component(component_id, asset_id, serial, model, name, function, firstnaturalfrequency, secondnaturalfrequency, thirdnaturalfrequency, rollingbearing, rollingbearingwidth_m, rollingbearingdiameter_m, numberrollingelements, rollingbearingcontactangle_grad, outerringfrequency, innerringfrequency, cagefrequency, rollingelementrotationfrequency, rollingelementcontactfrequency, journalbearing, journalbearingfluidtype, journalbearinggap_um, oilwhirlminorder, oilwhirlmaxorder, minfluidtemperature_c, maxfluidtemperature_c, minfluidpressure_bar, turbomachinery, bladesnumber, vanesnumber, bladelength_m, bladepassfrequency, bladetipfrequency, vanepassingfrequency, bladevanepassingfrequency, gear, geartype, gearratio, gearnumberstages, gearlowspeedshaftteethnumber, gearfastspeedshaftteethnumber, gearringteethnumber, gearplanetteethnumber, gearplanetarycarrierteethnumber, gearfixedcomponent, gearsunfrequency, gearringfrequency, gearplanetfrequency, gearmeshfrequency, gearteethcommonfactor, gearhuntingtoothfrequency, gearassemblyphase, gearghostfrequency, belt, beltdiameterd1_m, beltdiameterd2_m, beltaxialgap_m, beltteethnumberz1, beltteethnumberz2, beltlength_m, beltspeedn1_rpm, beltspeedn2_rpm, beltfrequency, timingbeltfrequency, motor_generator, motorpolepairs, motorrotorbars, motorstatorpoles, motorstatorslots, motorcoilsperpole, motorlineoffrequency, motorsynchronuousspeedfrequency, motorrunningspeedfrequency, motorslipfrequency, motorslipratio, motorpolepassfrequency, motorslotpassfrequency, motorrotorbarfrequency, motorstatorslotfrequency, motorcommutatorfrequency, motorstaticeccentricityfrequency, motordynamiceccentricity, motorstatormechanicaldamagefrequency, motorrotordefectfrequency, motorloosestatorcoilfrequency) VALUES (:component_id, :asset_id, :serial, :model, :name, :function, :firstnaturalfrequency, :secondnaturalfrequency, :thirdnaturalfrequency, :rollingbearing, :rollingbearingwidth_m, :rollingbearingdiameter_m, :numberrollingelements, :rollingbearingcontactangle_grad, :outerringfrequency, :innerringfrequency, :cagefrequency, :rollingelementrotationfrequency, :rollingelementcontactfrequency, :journalbearing, :journalbearingfluidtype, :journalbearinggap_um, :oilwhirlminorder, :oilwhirlmaxorder, :minfluidtemperature_c, :maxfluidtemperature_c, :minfluidpressure_bar, :turbomachinery, :bladesnumber, :vanesnumber, :bladelength_m, :bladepassfrequency, :bladetipfrequency, :vanepassingfrequency, :bladevanepassingfrequency, :gear, :geartype, :gearratio, :gearnumberstages, :gearlowspeedshaftteethnumber, :gearfastspeedshaftteethnumber, :gearringteethnumber, :gearplanetteethnumber, :gearplanetarycarrierteethnumber, :gearfixedcomponent, :gearsunfrequency, :gearringfrequency, :gearplanetfrequency, :gearmeshfrequency, :gearteethcommonfactor, :gearhuntingtoothfrequency, :gearassemblyphase, :gearghostfrequency, :belt, :beltdiameterd1_m, :beltdiameterd2_m, :beltaxialgap_m, :beltteethnumberz1, :beltteethnumberz2, :beltlength_m, :beltspeedn1_rpm, :beltspeedn2_rpm, :beltfrequency, :timingbeltfrequency, :motor_generator, :motorpolepairs, :motorrotorbars, :motorstatorpoles, :motorstatorslots, :motorcoilsperpole, :motorlineoffrequency, :motorsynchronuousspeedfrequency, :motorrunningspeedfrequency, :motorslipfrequency, :motorslipratio, :motorpolepassfrequency, :motorslotpassfrequency, :motorrotorbarfrequency, :motorstatorslotfrequency, :motorcommutatorfrequency, :motorstaticeccentricityfrequency, :motordynamiceccentricity, :motorstatormechanicaldamagefrequency, :motorrotordefectfrequency, :motorloosestatorcoilfrequency)');
        }

        //$QComponent->bindTable(':table_component', TABLE_COMPONENT);
        $QComponent->bindInt(':component_id', $category_id);
        $QComponent->bindInt(':asset_id', $data['asset_id']);
        $QComponent->bindValue(':serial', $data['serial']);
        $QComponent->bindValue(':name', $data['name']);
        $QComponent->bindValue(':model', $data['model']);
        $QComponent->bindValue(':function', $data['function']);
        $QComponent->bindValue(':firstnaturalfrequency', $data['firstnaturalfrequency']);
        $QComponent->bindValue(':secondnaturalfrequency', $data['secondnaturalfrequency']);
        $QComponent->bindValue(':thirdnaturalfrequency', $data['thirdnaturalfrequency']);
        $QComponent->bindValue(':rollingbearing', $data['rollingbearing']);
        $QComponent->bindInt(':rollingbearingwidth_m', $data['rollingbearingwidth_m']);
        $QComponent->bindInt(':rollingbearingdiameter_m', $data['rollingbearingdiameter_m']);
        $QComponent->bindInt(':numberrollingelements', $data['numberrollingelements']);
        $QComponent->bindInt(':rollingbearingcontactangle_grad', $data['rollingbearingcontactangle_grad']);
        $QComponent->bindValue(':outerringfrequency', $data['outerringfrequency']);
        $QComponent->bindValue(':innerringfrequency', $data['innerringfrequency']);
        $QComponent->bindValue(':cagefrequency', $data['cagefrequency']);
        $QComponent->bindValue(':rollingelementrotationfrequency', $data['rollingelementrotationfrequency']);
        $QComponent->bindValue(':rollingelementcontactfrequency', $data['rollingelementcontactfrequency']);
        $QComponent->bindValue(':journalbearing', $data['journalbearing']);
        $QComponent->bindValue(':journalbearingfluidtype', $data['journalbearingfluidtype']);
        $QComponent->bindInt(':journalbearinggap_um', $data['journalbearinggap_um']);
        $QComponent->bindInt(':oilwhirlminorder', $data['oilwhirlminorder']);
        $QComponent->bindInt(':oilwhirlmaxorder', $data['oilwhirlmaxorder']);
        $QComponent->bindInt(':minfluidtemperature_c', $data['minfluidtemperature_c']);
        $QComponent->bindInt(':maxfluidtemperature_c', $data['maxfluidtemperature_c']);
        $QComponent->bindInt(':minfluidpressure_bar', $data['minfluidpressure_bar']);
        $QComponent->bindValue(':turbomachinery', $data['turbomachinery']);
        $QComponent->bindInt(':bladesnumber', $data['bladesnumber']);
        $QComponent->bindInt(':vanesnumber', $data['vanesnumber']);
        $QComponent->bindInt(':bladelength_m', $data['bladelength_m']);
        $QComponent->bindValue(':bladepassfrequency', $data['bladepassfrequency']);
        $QComponent->bindValue(':bladetipfrequency', $data['bladetipfrequency']);
        $QComponent->bindValue(':vanepassingfrequency', $data['vanepassingfrequency']);
        $QComponent->bindValue(':bladevanepassingfrequency', $data['bladevanepassingfrequency']);
        $QComponent->bindValue(':gear', $data['gear']);
        $QComponent->bindValue(':geartype', $data['geartype']);
        $QComponent->bindValue(':gearratio', $data['gearratio']);
        $QComponent->bindInt(':gearnumberstages', $data['gearnumberstages']);
        $QComponent->bindInt(':gearlowspeedshaftteethnumber', $data['gearlowspeedshaftteethnumber']);
        $QComponent->bindInt(':gearfastspeedshaftteethnumber', $data['gearfastspeedshaftteethnumber']);
        $QComponent->bindInt(':gearringteethnumber', $data['gearringteethnumber']);
        $QComponent->bindInt(':gearplanetteethnumber', $data['gearplanetteethnumber']);
        $QComponent->bindInt(':gearplanetarycarrierteethnumber', $data['gearplanetarycarrierteethnumber']);
        $QComponent->bindValue(':gearfixedcomponent', $data['gearfixedcomponent']);
        $QComponent->bindValue(':gearsunfrequency', $data['gearsunfrequency']);
        $QComponent->bindValue(':gearringfrequency', $data['gearringfrequency']);
        $QComponent->bindValue(':gearplanetfrequency', $data['gearplanetfrequency']);
        $QComponent->bindValue(':gearmeshfrequency', $data['gearmeshfrequency']);
        $QComponent->bindValue(':gearteethcommonfactor', $data['gearteethcommonfactor']);
        $QComponent->bindValue(':gearhuntingtoothfrequency', $data['gearhuntingtoothfrequency']);
        $QComponent->bindValue(':gearassemblyphase', $data['gearassemblyphase']);
        $QComponent->bindValue(':gearghostfrequency', $data['gearghostfrequency']);
        $QComponent->bindInt(':belt', $data['belt']);
        $QComponent->bindInt(':beltdiameterd1_m', $data['beltdiameterd1_m']);
        $QComponent->bindInt(':beltdiameterd2_m', $data['beltdiameterd2_m']);
        $QComponent->bindInt(':beltaxialgap_m', $data['beltaxialgap_m']);
        $QComponent->bindInt(':beltteethnumberz1', $data['beltteethnumberz1']);
        $QComponent->bindInt(':beltteethnumberz2', $data['beltteethnumberz2']);
        $QComponent->bindInt(':beltlength_m', $data['beltlength_m']);
        $QComponent->bindInt(':beltspeedn1_rpm', $data['beltspeedn1_rpm']);
        $QComponent->bindInt(':beltspeedn2_rpm', $data['beltspeedn2_rpm']);
        $QComponent->bindValue(':beltfrequency', $data['beltfrequency']);
        $QComponent->bindValue(':timingbeltfrequency', $data['timingbeltfrequency']);
        $QComponent->bindValue(':motor_generator', $data['motor_generator']);
        $QComponent->bindValue(':motorpolepairs', $data['motorpolepairs']);
        $QComponent->bindValue(':motorrotorbars', $data['motorrotorbars']);
        $QComponent->bindValue(':motorstatorpoles', $data['motorstatorpoles']);
        $QComponent->bindValue(':motorstatorslots', $data['motorstatorslots']);
        $QComponent->bindValue(':motorcoilsperpole', $data['motorcoilsperpole']);
        $QComponent->bindValue(':motorlineoffrequency', $data['motorlineoffrequency']);
        $QComponent->bindValue(':motorsynchronuousspeedfrequency', $data['motorsynchronuousspeedfrequency']);
        $QComponent->bindValue(':motorrunningspeedfrequency', $data['motorrunningspeedfrequency']);
        $QComponent->bindValue(':motorslipfrequency', $data['motorslipfrequency']);
        $QComponent->bindValue(':motorslipratio', $data['motorslipratio']);
        $QComponent->bindValue(':motorpolepassfrequency', $data['motorpolepassfrequency']);
        $QComponent->bindValue(':motorslotpassfrequency', $data['motorslotpassfrequency']);
        $QComponent->bindValue(':motorrotorbarfrequency', $data['motorrotorbarfrequency']);
        $QComponent->bindValue(':motorstatorslotfrequency', $data['motorstatorslotfrequency']);
        $QComponent->bindValue(':motorcommutatorfrequency', $data['motorcommutatorfrequency']);
        $QComponent->bindValue(':motorstaticeccentricityfrequency', $data['motorstaticeccentricityfrequency']);
        $QComponent->bindValue(':motordynamiceccentricity', $data['motordynamiceccentricity']);
        $QComponent->bindValue(':motorstatormechanicaldamagefrequency', $data['motorstatormechanicaldamagefrequency']);
        $QComponent->bindValue(':motorrotordefectfrequency', $data['motorrotordefectfrequency']);
        $QComponent->bindValue(':motorloosestatorcoilfrequency', $data['motorloosestatorcoilfrequency']);
        $QComponent->setLogging($_SESSION['module'], $category_id);
        $QComponent->execute();

        //var_dump($osC_Database);

        if ($osC_Database->isError()) {
            $error = true;
            $_SESSION['error'] = $osC_Database->error;

            $event['content_id'] = $category_id;
            $event['content_type'] = "component";
            $event['event_date'] = date('Y-m-d H:i:s');
            $event['type'] = "error";
            $event['source'] = "cloud";
            $event['user'] = $_SESSION['admin']['username'];
            $event['category'] = "component";

            if (is_numeric($id)) {
                $event['description'] = "Le composant " . $data['name'] . " n'a pas pu etre modifie par . " . $_SESSION['admin']['username'] . " ... raison : " . $osC_Database->error;
            } else {
                $event['description'] = "Le composant " . $data['name'] . " n'a pas pu etre ajoute par . " . $_SESSION['admin']['username'] . " ... raison : " . $osC_Database->error;
            }

            osC_Categories_Admin::logEvent($event);
        }

        if ($error === false) {
            $osC_Database->commitTransaction();

            osC_Cache::clear('layout');
            osC_Cache::clear('layout_tree');

            $event['content_id'] = $category_id;
            $event['content_type'] = "component";
            $event['event_date'] = date('Y-m-d H:i:s');
            $event['type'] = "succes";
            $event['source'] = "cloud";
            $event['user'] = $_SESSION['admin']['username'];
            $event['category'] = "component";

            if (is_numeric($id)) {
                $event['description'] = "Le composant " . $data['name'] . " a ete modifie par . " . $_SESSION['admin']['username'];
            } else {
                $event['description'] = "Le composant " . $data['name'] . " a ete ajoute par . " . $_SESSION['admin']['username'];
            }

            osC_Categories_Admin::logEvent($event);

            return $category_id;
        }

        $osC_Database->rollbackTransaction();

        return false;
    }

    function saveAsset($id = null, $data)
    {
        global $osC_Database;

        $category_id = '';
        $error = false;

        $osC_Database->startTransaction();

        if (is_numeric($id)) {
            $Qcat = $osC_Database->query('update :table_layout set categories_status = :categories_status, sort_order = :sort_order, last_modified = now(),parent_id = :parent_id where categories_id = :categories_id');
            $Qcat->bindInt(':categories_id', $id);
        } else {
            $Qcat = $osC_Database->query('insert into :table_layout (parent_id, categories_status, sort_order, date_added,content_type) values (:parent_id, :categories_status, :sort_order, now(),:content_type)');
        }

        $Qcat->bindTable(':table_layout', TABLE_LAYOUT);
        $Qcat->bindInt(':parent_id', $data['lines_id']);
        $Qcat->bindInt(':sort_order', 0);
        $Qcat->bindInt(':categories_status', 1);
        $Qcat->bindValue(':content_type', 'asset');
        $Qcat->setLogging($_SESSION['module'], $id);
        $Qcat->execute();

        if (!$osC_Database->isError()) {
            $category_id = (is_numeric($id)) ? $id : $osC_Database->nextID();

            if (is_numeric($id)) {
                if ($data['categories_status']) {
                    $Qpstatus = $osC_Database->query('update :table_products set products_status = 1 where products_id in (select products_id from :table_products_to_categories where categories_id = :categories_id)');
                    $Qpstatus->bindTable(':table_products', TABLE_PRODUCTS);
                    $Qpstatus->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
                    $Qpstatus->bindInt(":categories_id", $id);
                    $Qpstatus->execute();
                } else {
                    if ($data['flag']) {
                        $Qpstatus = $osC_Database->query('update :table_products set products_status = 0 where products_id in (select products_id from :table_products_to_categories where categories_id = :categories_id)');
                        $Qpstatus->bindTable(':table_products', TABLE_PRODUCTS);
                        $Qpstatus->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
                        $Qpstatus->bindInt(":categories_id", $id);
                        $Qpstatus->execute();
                    }
                }
            }

            if ($osC_Database->isError()) {
                $error = true;
            }
        } else {
            $error = true;
            $_SESSION['error'] = $osC_Database->error;
        }

        if (is_numeric($id)) {
            $Qcd = $osC_Database->query('update :table_layout_description set categories_name = :categories_name, categories_url = :categories_url, categories_page_title = :categories_page_title, categories_meta_keywords = :categories_meta_keywords, categories_meta_description = :categories_meta_description where categories_id = :categories_id ');
        } else {
            $Qcd = $osC_Database->query('insert into :table_layout_description (categories_id, categories_name, categories_url, categories_page_title, categories_meta_keywords, categories_meta_description) values (:categories_id, :categories_name, :categories_url, :categories_page_title, :categories_meta_keywords, :categories_meta_description)');
        }

        $Qcd->bindTable(':table_layout_description', TABLE_LAYOUT_DESCRIPTION);
        $Qcd->bindInt(':categories_id', $category_id);
        $Qcd->bindValue(':categories_name', $data['name']);
        $Qcd->bindValue(':categories_url', '');
        $Qcd->bindValue(':categories_page_title', '');
        $Qcd->bindValue(':categories_meta_keywords', '');
        $Qcd->bindValue(':categories_meta_description', '');
        $Qcd->setLogging($_SESSION['module'], $category_id);
        $Qcd->execute();

        if ($osC_Database->isError()) {
            $error = true;
            $_SESSION['error'] = $osC_Database->error;
        }

        if (is_numeric($id)) {
            $Qplants = $osC_Database->query('UPDATE :table_asset SET lines_id=:lines_id,code=:code,cpms_ip=:cpms_ip,cpms_mac=:cpms_mac,cpms_type=:cpms_type,cpms_slotnumber=:cpms_slotnumber,cpms_controller=:cpms_controller,location = :location,manufacturer=:manufacturer,model=:model,equipmentype=:equipmentype,administrators_id=:administrators_id,configuration=:configuration,fonction=:fonction,norms=:norms,powersource=:powersource,support=:support,coupling=:coupling,ratedpower_w=:ratedpower_w,ratedspeed_rpm=:ratedspeed_rpm,ratedtorque_nm=:ratedtorque_nm,ratedvoltage_v=:ratedvoltage_v,ratedcurrent_a=:ratedcurrent_a,minspeed_rpm=:minspeed_rpm,maxspeed_rpm=:maxspeed_rpm,tachochannel=:tachochannel,pulse_per_rev=:pulse_per_rev,triggerlevel=:triggerlevel,rotdir=:rotdir,op1_name=:op1_name,op2_name=:op2_name,op3_name=:op3_name,op4_name=:op4_name,op5_name=:op5_name,op6_name=:op6_name,op7_name=:op7_name,op8_name=:op8_name,op9_name=:op9_name,op10_name=:op10_name,deltaprozent=:deltaprozent,kmean=:kmean,kmin=:kmin,kmax=:kmax,kstd=:kstd,kral=:kral,kal=:kal,refwindow=:refwindow,movingwindow=:movingwindow,movingdeltaanalyse=:movingdeltaanalyse,severitylimit1=:severitylimit1,severitylimit2=:severitylimit2,severitylimit3=:severitylimit3,severitylimit4=:severitylimit4 where asset_id = :asset_id');
        } else {
            $Qplants = $osC_Database->query('INSERT INTO :table_asset (asset_id, lines_id, code, cpms_ip, cpms_mac, cpms_type, cpms_slotnumber, cpms_controller,location, manufacturer, model, equipmentype, administrators_id, configuration, fonction, norms, powersource, support, coupling, ratedpower_w, ratedspeed_rpm, ratedtorque_nm, ratedvoltage_v, ratedcurrent_a, minspeed_rpm, maxspeed_rpm, tachochannel, pulse_per_rev, triggerlevel, rotdir, op1_name, op2_name,op3_name,op4_name,op5_name,op6_name,op7_name,op8_name,op9_name,op10_name, deltaprozent, kmean, kmin, kmax, kstd, kral, kal, refwindow, movingwindow, movingdeltaanalyse, severitylimit1, severitylimit2, severitylimit3, severitylimit4) VALUES (:asset_id, :lines_id, :code, :cpms_ip, :cpms_mac, :cpms_type, :cpms_slotnumber, :cpms_controller,:location, :manufacturer, :model, :equipmentype, :administrators_id, :configuration, :fonction, :norms, :powersource, :support, :coupling, :ratedpower_w, :ratedspeed_rpm, :ratedtorque_nm, :ratedvoltage_v, :ratedcurrent_a, :minspeed_rpm, :maxspeed_rpm, :tachochannel, :pulse_per_rev, :triggerlevel, :rotdir,:op1_name, :op2_name,:op3_name,:op4_name,:op5_name,:op6_name,:op7_name,:op8_name,:op9_name,:op10_name, :deltaprozent, :kmean, :kmin, :kmax, :kstd, :kral, :kal, :refwindow, :movingwindow, :movingdeltaanalyse, :severitylimit1, :severitylimit2, :severitylimit3, :severitylimit4)');
        }

        $Qplants->bindTable(':table_asset', TABLE_ASSET);
        $Qplants->bindInt(':lines_id', $data['lines_id']);
        $Qplants->bindInt(':asset_id', $category_id);
        $Qplants->bindValue(':code', $data['code']);
        $Qplants->bindValue(':name', $data['name']);
        $Qplants->bindValue(':cpms_ip', $data['cpms_ip']);
        $Qplants->bindValue(':cpms_mac', $data['cpms_mac']);
        $Qplants->bindInt(':cpms_type', $data['cpms_type']);
        $Qplants->bindInt(':cpms_controller', $data['cpms_controller']);
        $Qplants->bindValue(':cpms_slotnumber', $data['cpms_slotnumber']);
        $Qplants->bindValue(':location', $data['location']);
        $Qplants->bindValue(':manufacturer', $data['manufacturer']);
        $Qplants->bindValue(':model', $data['model']);
        $Qplants->bindInt(':equipmentype', $data['equipmentype']);
        $Qplants->bindInt(':administrators_id', $data['administrators_id']);
        $Qplants->bindValue(':configuration', $data['configuration']);
        $Qplants->bindValue(':fonction', $data['fonction']);
        $Qplants->bindValue(':norms', $data['norms']);
        $Qplants->bindInt(':powersource', $data['powersource']);
        $Qplants->bindValue(':support', $data['support']);
        $Qplants->bindValue(':coupling', $data['coupling']);
        $Qplants->bindValue(':ratedpower_w', $data['ratedpower_w']);
        $Qplants->bindInt(':ratedspeed_rpm', $data['ratedspeed_rpm']);
        $Qplants->bindInt(':ratedtorque_nm', $data['ratedtorque_nm']);
        $Qplants->bindInt(':ratedvoltage_v', $data['ratedvoltage_v']);
        $Qplants->bindInt(':ratedcurrent_a', $data['ratedcurrent_a']);
        $Qplants->bindInt(':minspeed_rpm', $data['minspeed_rpm']);
        $Qplants->bindInt(':maxspeed_rpm', $data['maxspeed_rpm']);
        $Qplants->bindValue(':tachochannel', $data['tachochannel']);
        $Qplants->bindInt(':pulse_per_rev', $data['pulse_per_rev']);
        $Qplants->bindValue(':triggerlevel', $data['triggerlevel']);
        $Qplants->bindValue(':rotdir', $data['rotdir']);
        $Qplants->bindValue(':op1_name', $data['op1_name']);
        $Qplants->bindValue(':op2_name', $data['op2_name']);
        $Qplants->bindValue(':op3_name', $data['op3_name']);
        $Qplants->bindValue(':op4_name', $data['op4_name']);
        $Qplants->bindValue(':op5_name', $data['op5_name']);
        $Qplants->bindValue(':op6_name', $data['op6_name']);
        $Qplants->bindValue(':op7_name', $data['op7_name']);
        $Qplants->bindValue(':op8_name', $data['op8_name']);
        $Qplants->bindValue(':op9_name', $data['op9_name']);
        $Qplants->bindValue(':op10_name', $data['op10_name']);
        $Qplants->bindInt(':deltaprozent', $data['deltaprozent']);
        $Qplants->bindValue(':kmean', $data['kmean']);
        $Qplants->bindValue(':kmin', $data['kmin']);
        $Qplants->bindValue(':kmax', $data['kmax']);
        $Qplants->bindValue(':kstd', $data['kstd']);
        $Qplants->bindValue(':kral', $data['kral']);
        $Qplants->bindValue(':kal', $data['kal']);
        $Qplants->bindValue(':refwindow', $data['refwindow']);
        $Qplants->bindValue(':movingwindow', $data['movingwindow']);
        $Qplants->bindValue(':movingdeltaanalyse', $data['movingdeltaanalyse']);
        $Qplants->bindValue(':severitylimit1', $data['severitylimit1']);
        $Qplants->bindValue(':severitylimit2', $data['severitylimit2']);
        $Qplants->bindValue(':severitylimit3', $data['severitylimit3']);
        $Qplants->bindValue(':severitylimit4', $data['severitylimit4']);
        $Qplants->setLogging($_SESSION['module'], $category_id);
        $Qplants->execute();

        //var_dump($osC_Database);

        if ($osC_Database->isError()) {
            $error = true;
            $_SESSION['error'] = $osC_Database->error;

            $event['content_id'] = $category_id;
            $event['content_type'] = "asset";
            $event['event_date'] = date('Y-m-d H:i:s');
            $event['type'] = "error";
            $event['source'] = "cloud";
            $event['user'] = $_SESSION['admin']['username'];
            $event['category'] = "asset";

            if (is_numeric($id)) {
                $event['description'] = "L'asset " . $data['name'] . " n'a pas pu etre modifie par . " . $_SESSION['admin']['username'] . " ... raison : " . $osC_Database->error;
            } else {
                $event['description'] = "L'asset " . $data['name'] . " n'a pas pu etre ajoute par . " . $_SESSION['admin']['username'] . " ... raison : " . $osC_Database->error;
            }

            osC_Categories_Admin::logEvent($event);
        }

        if ($error === false) {
            $osC_Database->commitTransaction();

            osC_Cache::clear('layout');
            osC_Cache::clear('layout_tree');

            $event['content_id'] = $category_id;
            $event['content_type'] = "asset";
            $event['event_date'] = date('Y-m-d H:i:s');
            $event['type'] = "succes";
            $event['source'] = "cloud";
            $event['user'] = $_SESSION['admin']['username'];
            $event['category'] = "asset";

            if (is_numeric($id)) {
                $event['description'] = "L'asset " . $data['name'] . " a ete modifie par . " . $_SESSION['admin']['username'];
            } else {
                $event['description'] = "L'asset " . $data['name'] . " a ete ajouté par . " . $_SESSION['admin']['username'];
            }

            osC_Categories_Admin::logEvent($event);

            return $category_id;
        }

        $osC_Database->rollbackTransaction();

        return false;
    }

    function saveSensor($id = null, $data)
    {
        global $osC_Database;

        $category_id = '';
        $error = false;

        $osC_Database->startTransaction();

        if (is_numeric($id)) {
            $Qcat = $osC_Database->query('update :table_layout set categories_status = :categories_status, sort_order = :sort_order, last_modified = now(),parent_id = :parent_id where categories_id = :categories_id');
            $Qcat->bindInt(':categories_id', $id);
        } else {
            $Qcat = $osC_Database->query('insert into :table_layout (parent_id, categories_status, sort_order, date_added,content_type) values (:parent_id, :categories_status, :sort_order, now(),:content_type)');
        }

        $Qcat->bindTable(':table_layout', TABLE_LAYOUT);
        $Qcat->bindInt(':parent_id', $data['component_id']);
        $Qcat->bindInt(':sort_order', 0);
        $Qcat->bindInt(':categories_status', 1);
        $Qcat->bindValue(':content_type', 'sensor');
        $Qcat->setLogging($_SESSION['module'], $id);
        $Qcat->execute();

        if (!$osC_Database->isError()) {
            $category_id = (is_numeric($id)) ? $id : $osC_Database->nextID();

            if (is_numeric($id)) {
                if ($data['categories_status']) {
                    $Qpstatus = $osC_Database->query('update :table_products set products_status = 1 where products_id in (select products_id from :table_products_to_categories where categories_id = :categories_id)');
                    $Qpstatus->bindTable(':table_products', TABLE_PRODUCTS);
                    $Qpstatus->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
                    $Qpstatus->bindInt(":categories_id", $id);
                    $Qpstatus->execute();
                } else {
                    if ($data['flag']) {
                        $Qpstatus = $osC_Database->query('update :table_products set products_status = 0 where products_id in (select products_id from :table_products_to_categories where categories_id = :categories_id)');
                        $Qpstatus->bindTable(':table_products', TABLE_PRODUCTS);
                        $Qpstatus->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
                        $Qpstatus->bindInt(":categories_id", $id);
                        $Qpstatus->execute();
                    }
                }
            }

            if ($osC_Database->isError()) {
                $error = true;
            }
        } else {
            $error = true;
            $_SESSION['error'] = $osC_Database->error;
        }

        if (is_numeric($id)) {
            $Qcd = $osC_Database->query('update :table_layout_description set categories_name = :categories_name, categories_url = :categories_url, categories_page_title = :categories_page_title, categories_meta_keywords = :categories_meta_keywords, categories_meta_description = :categories_meta_description where categories_id = :categories_id ');
        } else {
            $Qcd = $osC_Database->query('insert into :table_layout_description (categories_id, categories_name, categories_url, categories_page_title, categories_meta_keywords, categories_meta_description) values (:categories_id, :categories_name, :categories_url, :categories_page_title, :categories_meta_keywords, :categories_meta_description)');
        }

        $Qcd->bindTable(':table_layout_description', TABLE_LAYOUT_DESCRIPTION);
        $Qcd->bindInt(':categories_id', $category_id);
        $Qcd->bindValue(':categories_name', $data['code']);
        $Qcd->bindValue(':categories_url', '');
        $Qcd->bindValue(':categories_page_title', '');
        $Qcd->bindValue(':categories_meta_keywords', '');
        $Qcd->bindValue(':categories_meta_description', '');
        $Qcd->setLogging($_SESSION['module'], $category_id);
        $Qcd->execute();

        if ($osC_Database->isError()) {
            $error = true;
            $_SESSION['error'] = $osC_Database->error;
        }

        if (is_numeric($id)) {
            $Qsensor = $osC_Database->query('UPDATE :table_sensor SET component_id=:component_id,code=:code,manufacturer = :manufacturer,signalname=:signalname,channel=:channel,cpmsslot = :cpmsslot,cpms_ip = :cpms_ip,sampling_freq=:sampling_freq,record_length=:record_length,sensitivity=:sensitivity,sensitivity_unit=:sensitivity_unit,engineering_unit=:engineering_unit,offset=:offset,time_analysis=:time_analysis,orbit_analysis=:orbit_analysis,fftanalysis=:fftanalysis,order_analysis=:order_analysis,envelope_analysis=:envelope_analysis,window_length_s=:window_length_s,window_fft=:window_fft,overlap=:overlap,average=:average,sample_rev=:sample_rev,orbit_channel_y=:orbit_channel_y,frfchannel_y=:frfchannel_y,envelopetype=:envelopetype,bpfu_env=:bpfu_env,bpfo_env=:bpfo_env,tpf_env=:tpf_env,esa_channel=:esa_channel,measurement_range=:measurement_range,frequency_range=:frequency_range,temperaturerange=:temperaturerange,impedance=:impedance,calibration_date=:calibration_date,serial_number=:serial_number,component=:component,sensortypecode=:sensortypecode,angle=:angle,orientation=:orientation,motion=:motion,attachment_method=:attachment_method,jonction_box=:jonction_box,acquisitionstation = :acquisitionstation where sensors_id=:sensors_id');
        } else {
            $Qsensor = $osC_Database->query('INSERT INTO :table_sensor (sensors_id, component_id, code,manufacturer, signalname, channel,cpmsslot,cpms_ip, sampling_freq, record_length, sensitivity, sensitivity_unit, engineering_unit, offset, time_analysis, orbit_analysis, fftanalysis, order_analysis, envelope_analysis, window_length_s, window_fft, overlap, average, sample_rev, orbit_channel_y, frfchannel_y, envelopetype, bpfu_env, bpfo_env, tpf_env, esa_channel,measurement_range, frequency_range, temperaturerange, impedance, calibration_date, serial_number, component, sensortypecode, angle, orientation, motion, attachment_method, jonction_box,acquisitionstation) VALUES (:sensors_id, :component_id, :code,:manufacturer, :signalname, :channel,:cpmsslot,:cpms_ip,:sampling_freq, :record_length, :sensitivity, :sensitivity_unit, :engineering_unit, :offset, :time_analysis, :orbit_analysis, :fftanalysis, :order_analysis, :envelope_analysis, :window_length_s, :window_fft, :overlap, :average, :sample_rev, :orbit_channel_y, :frfchannel_y, :envelopetype, :bpfu_env, :bpfo_env, :tpf_env, :esa_channel,:measurement_range, :frequency_range, :temperaturerange, :impedance, :calibration_date, :serial_number, :component, :sensortypecode, :angle, :orientation, :motion, :attachment_method, :jonction_box,:acquisitionstation)');
        }

        $Qsensor->bindTable(':table_sensor', TABLE_SENSOR);
        $Qsensor->bindInt(':component_id', $data['component_id']);
        $Qsensor->bindInt(':sensors_id', $category_id);
        $Qsensor->bindValue(':code', $data['code']);
        $Qsensor->bindValue(':manufacturer', $data['manufacturer']);
        $Qsensor->bindValue(':signalname', $data['signalname']);
        $Qsensor->bindValue(':channel', $data['channel']);
        $Qsensor->bindValue(':cpmsslot', $data['cpmsslot']);
        $Qsensor->bindValue(':cpms_ip', $data['cpms_ip']);
        $Qsensor->bindValue(':manufacturer', $data['manufacturer']);
        $Qsensor->bindInt(':sampling_freq', $data['sampling_freq']);
        $Qsensor->bindInt(':record_length', $data['record_length']);
        $Qsensor->bindInt(':sensitivity', $data['sensitivity']);
        $Qsensor->bindValue(':sensitivity_unit', $data['sensitivity_unit']);
        $Qsensor->bindValue(':engineering_unit', $data['engineering_unit']);
        $Qsensor->bindValue(':offset', $data['offset']);
        $Qsensor->bindValue(':time_analysis', $data['time_analysis']);
        $Qsensor->bindValue(':orbit_analysis', $data['orbit_analysis']);
        $Qsensor->bindValue(':fftanalysis', $data['fftanalysis']);
        $Qsensor->bindValue(':order_analysis', $data['order_analysis']);
        $Qsensor->bindValue(':envelope_analysis', $data['envelope_analysis']);
        $Qsensor->bindInt(':window_length_s', $data['window_length_s']);
        $Qsensor->bindValue(':window_fft', $data['window_fft']);
        $Qsensor->bindInt(':overlap', $data['overlap']);
        $Qsensor->bindValue(':average', $data['average']);
        $Qsensor->bindValue(':sample_rev', $data['sample_rev']);
        $Qsensor->bindValue(':orbit_channel_y', $data['orbit_channel_y']);
        $Qsensor->bindValue(':frfchannel_y', $data['frfchannel_y']);
        $Qsensor->bindValue(':envelopetype', $data['envelopetype']);
        $Qsensor->bindValue(':bpfu_env', $data['bpfu_env']);
        $Qsensor->bindValue(':bpfo_env', $data['bpfo_env']);
        $Qsensor->bindValue(':tpf_env', $data['tpf_env']);
        $Qsensor->bindValue(':esa_channel', $data['esa_channel']);
        $Qsensor->bindValue(':measurement_range', $data['measurement_range']);
        $Qsensor->bindValue(':frequency_range', $data['frequency_range']);
        $Qsensor->bindValue(':temperaturerange', $data['temperaturerange']);
        $Qsensor->bindValue(':impedance', $data['impedance']);
        $Qsensor->bindValue(':calibration_date', $data['calibration_date']);
        $Qsensor->bindValue(':serial_number', $data['serial_number']);
        $Qsensor->bindValue(':component', $data['component']);
        $Qsensor->bindValue(':sensortypecode', $data['sensortypecode']);
        $Qsensor->bindValue(':angle', $data['angle']);
        $Qsensor->bindValue(':orientation', $data['orientation']);
        $Qsensor->bindValue(':motion', $data['motion']);
        $Qsensor->bindValue(':attachment_method', $data['attachment_method']);
        $Qsensor->bindValue(':jonction_box', $data['jonction_box']);
        $Qsensor->bindValue(':acquisitionstation', $data['acquisitionstation']);
        $Qsensor->setLogging($_SESSION['module'], $category_id);
        $Qsensor->execute();

        //var_dump($osC_Database);

        if ($osC_Database->isError()) {
            $error = true;
            $_SESSION['error'] = $osC_Database->error;

            $event['content_id'] = $category_id;
            $event['content_type'] = "sensor";
            $event['event_date'] = date('Y-m-d H:i:s');
            $event['type'] = "error";
            $event['source'] = "cloud";
            $event['user'] = $_SESSION['admin']['username'];
            $event['category'] = "sensor";

            if (is_numeric($id)) {
                $event['description'] = "Le Capteur " . $data['code'] . " n'a pas pu etre modifie par . " . $_SESSION['admin']['username'] . " ... raison : " . $osC_Database->error;
            } else {
                $event['description'] = "Le Capteur " . $data['code'] . " n'a pas pu etre ajoute par . " . $_SESSION['admin']['username'] . " ... raison : " . $osC_Database->error;
            }

            osC_Categories_Admin::logEvent($event);
        }

        if ($error === false) {
            $osC_Database->commitTransaction();

            osC_Cache::clear('layout');
            osC_Cache::clear('layout_tree');

            $event['content_id'] = $category_id;
            $event['content_type'] = "sensor";
            $event['event_date'] = date('Y-m-d H:i:s');
            $event['type'] = "succes";
            $event['source'] = "cloud";
            $event['user'] = $_SESSION['admin']['username'];
            $event['category'] = "sensor";

            if (is_numeric($id)) {
                $event['description'] = "Le Capteur " . $data['code'] . " a ete modifie par . " . $_SESSION['admin']['username'];
            } else {
                $event['description'] = "Le Capteur " . $data['code'] . " a ete ajoute par . " . $_SESSION['admin']['username'];
            }

            osC_Categories_Admin::logEvent($event);

            return $category_id;
        }

        $osC_Database->rollbackTransaction();

        return false;
    }

    function saveParameter($data, $config)
    {
        global $osC_Database;

        $error = false;

        $osC_Database->startTransaction();

        $query = "INSERT INTO :table_parameters (eventdate, speedtype, constspeedhz, dopspeedchannel, tachochannel, tachotrigger, pulseperrev, rotdirection, pretriglength, recordlength, cyclicevent, statuschangeevent, oschangeevent, cyclictime, cyclictimebyal, dailytrigtime_hh, dailytrigtime_mm, dailytrigtime_ss, plant, line, asset, cpms_ip, configurator, monitoring, monitoringtime_ms, chfifosize, totalfifosize, samplingrate, staticchannels, sampeperch, dynamicchannels, record_date_time, event_date_time, event_trigger_type, monitoring_status, measurement_state, operating_class, xmlfile, acrms_status, lfrms_status, isorms_status, hfrms_status, acpeak_status, accrest_status, mean_status, peak2peak_status, kurtosis_status, smax_status, mp_acrms_value, mp_lfrms_value, mp_isorms_value, mp_hfrms_value, mp_acpeak_value, mp_accrest_value, mp_mean_value, mp_peak2peak_value, mp_kurtosis_value, mp_smax_value,lfrms, isorms, hfrms, crest, peak, rms, max, min, peak2peak, mean, std, kurtosis, skewness, smax, histo, a1x, p1x, a2x, p2x, a3x, p3x, file,channel,chname,chunit,chstatus,op1,op2,op3,op4,op5,op6,op7,op8,op9,op10,customers_id,plants_id,lines_id,asset_id,component_id,sensors_id) VALUES (:eventdate, :speedtype, :constspeedhz, :dopspeedchannel, :tachochannel, :tachotrigger, :pulseperrev, :rotdirection, :pretriglength, :recordlength, :cyclicevent, :statuschangeevent, :oschangeevent, :cyclictime, :cyclictimebyal, :dailytrigtime_hh, :dailytrigtime_mm, :dailytrigtime_ss, :plant, :line, :asset, :cpms_ip, :configurator, :monitoring, :monitoringtime_ms, :chfifosize, :totalfifosize, :samplingrate, :staticchannels, :sampeperch, :dynamicchannels, :record_date_time, :event_date_time, :event_trigger_type, :monitoring_status, :measurement_state, :operating_class, :xmlfile, :acrms_status, :lfrms_status, :isorms_status, :hfrms_status, :acpeak_status, :accrest_status, :mean_status, :peak2peak_status, :kurtosis_status, :smax_status, :mp_acrms_value, :mp_lfrms_value, :mp_isorms_value, :mp_hfrms_value, :mp_acpeak_value, :mp_accrest_value,:mp_mean_value, :mp_peak2peak_value, :mp_kurtosis_value, :mp_smax_value, :lfrms, :isorms, :hfrms, :crest, :peak, :rms, :max, :min, :peak2peak, :mean, :std, :kurtosis, :skewness, :smax, :histo, :a1x, :p1x, :a2x, :p2x, :a3x, :p3x, :file,:channel,:chname,:chunit,:chstatus,:op1,:op2,:op3,:op4,:op5,:op6,:op7,:op8,:op9,:op10,:customers_id,:plants_id,:lines_id,:asset_id,:component_id,:sensors_id)";

        $QParameter = $osC_Database->query($query);

        $QParameter->bindTable(':table_parameters', TABLE_PARAMETERS);
        $QParameter->bindValue(':eventdate', $data['eventdate']);
        $QParameter->bindValue(':speedtype', $data['speedtype']);
        $QParameter->bindInt(':constspeedhz', $data['constspeedhz']);
        $QParameter->bindInt(':dopspeedchannel', $data['dopspeedchannel']);
        $QParameter->bindInt(':tachochannel', $data['tachochannel']);
        $QParameter->bindInt(':tachotrigger', $data['tachotrigger']);
        $QParameter->bindInt(':pulseperrev', $data['pulseperrev']);
        $QParameter->bindValue(':rotdirection', $data['rotdirection']);
        $QParameter->bindInt(':pretriglength', $data['pretriglength']);
        $QParameter->bindInt(':recordlength', $data['recordlength']);
        $QParameter->bindInt(':cyclicevent', $data['cyclicevent']);
        $QParameter->bindInt(':statuschangeevent', $data['statuschangeevent']);
        $QParameter->bindInt(':oschangeevent', $data['oschangeevent']);
        $QParameter->bindInt(':cyclictime', $data['cyclictime']);
        $QParameter->bindInt(':cyclictimebyal', $data['cyclictimebyal']);
        $QParameter->bindInt(':dailytrigtime_hh', $data['dailytrigtime_hh']);
        $QParameter->bindInt(':dailytrigtime_mm', $data['dailytrigtime_mm']);
        $QParameter->bindInt(':dailytrigtime_ss', $data['dailytrigtime_ss']);
        $QParameter->bindValue(':plant', $data['plant']);
        $QParameter->bindValue(':line', $data['line']);
        $QParameter->bindValue(':asset', $data['asset']);
        $QParameter->bindValue(':cpms_ip', $data['cpms_ip']);
        $QParameter->bindValue(':configurator', $data['configurator']);
        $QParameter->bindInt(':monitoring', $data['monitoring']);
        $QParameter->bindInt(':monitoringtime_ms', $data['monitoringtime_ms']);
        $QParameter->bindInt(':chfifosize', $data['chfifosize']);
        $QParameter->bindInt(':totalfifosize', $data['totalfifosize']);
        $QParameter->bindInt(':samplingrate', $data['samplingrate']);
        $QParameter->bindInt(':staticchannels', $data['staticchannels']);
        $QParameter->bindInt(':sampeperch', $data['sampeperch']);
        $QParameter->bindInt(':dynamicchannels', $data['dynamicchannels']);
        $QParameter->bindValue(':record_date_time', $data['record_date_time']);
        $QParameter->bindValue(':event_date_time', $data['event_date_time']);
        $QParameter->bindValue(':event_trigger_type', $data['event_trigger_type']);
        $QParameter->bindValue(':monitoring_status', $data['monitoring_status']);
        $QParameter->bindValue(':measurement_state', $data['measurement_state']);
        $QParameter->bindValue(':operating_class', $data['operating_class']);
        $QParameter->bindValue(':xmlfile', $data['xmlfile']);
        $QParameter->bindValue(':acrms_status', $data['acrms_status']);
        $QParameter->bindValue(':lfrms_status', $data['lfrms_status']);
        $QParameter->bindValue(':isorms_status', $data['isorms_status']);
        $QParameter->bindValue(':hfrms_status', $data['hfrms_status']);
        $QParameter->bindValue(':acpeak_status', $data['acpeak_status']);
        $QParameter->bindValue(':accrest_status', $data['accrest_status']);
        $QParameter->bindValue(':mean_status', $data['mean_status']);
        $QParameter->bindValue(':peak2peak_status', $data['peak2peak_status']);
        $QParameter->bindValue(':kurtosis_status', $data['kurtosis_status']);
        $QParameter->bindValue(':smax_status', $data['smax_status']);
        $QParameter->bindValue(':mp_acrms_value', $data['mp_acrms_value']);
        $QParameter->bindValue(':mp_lfrms_value', $data['mp_lfrms_value']);
        $QParameter->bindValue(':mp_isorms_value', $data['mp_isorms_value']);
        $QParameter->bindValue(':mp_hfrms_value', $data['mp_hfrms_value']);
        $QParameter->bindValue(':mp_acpeak_value', $data['mp_acpeak_value']);
        $QParameter->bindValue(':mp_accrest_value', $data['mp_accrest_value']);
        $QParameter->bindValue(':mp_mean_value', $data['mp_mean_value']);
        $QParameter->bindValue(':mp_peak2peak_value', $data['mp_peak2peak_value']);
        $QParameter->bindValue(':mp_kurtosis_value', $data['mp_kurtosis_value']);
        $QParameter->bindValue(':mp_smax_value', $data['mp_smax_value']);
        $QParameter->bindValue(':lfrms', $data['lfrms']);
        $QParameter->bindValue(':isorms', $data['isorms']);
        $QParameter->bindValue(':hfrms', $data['hfrms']);
        $QParameter->bindValue(':crest', $data['crest']);
        $QParameter->bindValue(':peak', $data['peak']);
        $QParameter->bindValue(':rms', $data['rms']);
        $QParameter->bindValue(':max', $data['max']);
        $QParameter->bindValue(':min', $data['min']);
        $QParameter->bindValue(':peak2peak', $data['peak2peak']);
        $QParameter->bindValue(':mean', $data['mean']);
        $QParameter->bindValue(':std', $data['std']);
        $QParameter->bindValue(':kurtosis', $data['kurtosis']);
        $QParameter->bindValue(':skewness', $data['skewness']);
        $QParameter->bindValue(':smax', $data['smax']);
        $QParameter->bindValue(':histo', $data['histo']);
        $QParameter->bindValue(':a1x', $data['a1x']);
        $QParameter->bindValue(':p1x', $data['p1x']);
        $QParameter->bindValue(':a2x', $data['a2x']);
        $QParameter->bindValue(':p2x', $data['p2x']);
        $QParameter->bindValue(':a3x', $data['a3x']);
        $QParameter->bindValue(':p3x', $data['p3x']);
        $QParameter->bindValue(':file', $data['file']);
        $QParameter->bindValue(':channel', $data['channel']);
        $QParameter->bindValue(':chname', $data['chname']);
        $QParameter->bindValue(':chunit', $data['chunit']);
        $QParameter->bindValue(':chstatus', $data['chstatus']);
        $QParameter->bindValue(':op1', $data['op1']);
        $QParameter->bindValue(':op2', $data['op2']);
        $QParameter->bindValue(':op3', $data['op3']);
        $QParameter->bindValue(':op4', $data['op4']);
        $QParameter->bindValue(':op5', $data['op5']);
        $QParameter->bindValue(':op6', $data['op6']);
        $QParameter->bindValue(':op7', $data['op7']);
        $QParameter->bindValue(':op8', $data['op8']);
        $QParameter->bindValue(':op9', $data['op9']);
        $QParameter->bindValue(':op10', $data['op10']);
        $QParameter->bindInt(':customers_id', $config['customers_id']);
        $QParameter->bindInt(':plants_id', $config['plants_id']);
        $QParameter->bindInt(':lines_id', $config['lines_id']);
        $QParameter->bindInt(':asset_id', $config['asset_id']);
        $QParameter->bindInt(':component_id', $config['component_id']);
        $QParameter->bindInt(':sensors_id', $config['sensors_id']);

        $QParameter->execute();

        if ($osC_Database->isError()) {
            $error = true;
            $_SESSION['error'] = $osC_Database->getError();
        }

        if ($error === false) {
            $osC_Database->commitTransaction();

            return true;
        }

        $osC_Database->rollbackTransaction();

        return false;
    }

    function saveCpms($data, $config)
    {
        global $osC_Database;

        $error = false;

        $osC_Database->startTransaction();


        //$query = "delete from :table_status where content_id = :customers_id or content_id = :plants_id or content_id = :lines_id or content_id = :asset_id";
        $query = "delete from :table_status where content_id = :customers_id or content_id = :plants_id or content_id = :lines_id or content_id = :asset_id";

        $Qupdate = $osC_Database->query($query);
        $Qupdate->bindTable(':table_status', TABLE_STATUS);
        $Qupdate->bindInt(":customers_id", $config['customers_id']);
        $Qupdate->bindInt(":plants_id", $config['plants_id']);
        $Qupdate->bindInt(":lines_id", $config['lines_id']);
        $Qupdate->bindInt(":asset_id", $config['asset_id']);
        $Qupdate->execute();

        if ($osC_Database->isError()) {
            $osC_Database->rollbackTransaction();
            $_SESSION['error'] = $osC_Database->getError();
            return false;
        }

        if (!$osC_Database->isError()) {
            $query = "insert into :table_status (content_id, content_type, status,customers_id) VALUES (:content_id, :content_type, :status,:customers_id)";

            $Qupdate = $osC_Database->query($query);
            $Qupdate->bindTable(':table_status', TABLE_STATUS);
            $Qupdate->bindInt(":content_id", $config['plants_id']);
            $Qupdate->bindInt(":customers_id", $config['customers_id']);
            $Qupdate->bindValue(":content_type", 'plant');
            $Qupdate->bindValue(":status", 'error');
            $Qupdate->execute();

            if (!$osC_Database->isError()) {
                $query = "insert into :table_status (content_id, content_type, status,customers_id) VALUES (:content_id, :content_type, :status,:customers_id)";

                $Qupdate = $osC_Database->query($query);
                $Qupdate->bindTable(':table_status', TABLE_STATUS);
                $Qupdate->bindInt(":content_id", $config['lines_id']);
                $Qupdate->bindInt(":customers_id", $config['customers_id']);
                $Qupdate->bindValue(":content_type", 'line');
                $Qupdate->bindValue(":status", 'error');
                $Qupdate->execute();

                if (!$osC_Database->isError()) {
                    $query = "insert into :table_status (content_id, content_type, status,customers_id) VALUES (:content_id, :content_type, :status,:customers_id)";

                    $Qupdate = $osC_Database->query($query);
                    $Qupdate->bindTable(':table_status', TABLE_STATUS);
                    $Qupdate->bindInt(":content_id", $config['asset_id']);
                    $Qupdate->bindValue(":content_type", 'asset');
                    $Qupdate->bindValue(":status", 'error');
                    $Qupdate->execute();
                } else {
                    $_SESSION['error'] = "Impossible de mettre à jour le status du line " . $config['lines_id'] . " ... Raison : " . $osC_Database->getError();

                    $event['content_id'] = $config['lines_id'];
                    $event['content_type'] = "line";
                    $event['type'] = "error";
                    $event['event_date'] = date('Y-m-d H:i:s');
                    $event['source'] = "cloud";
                    $event['user'] = "Importer";
                    $event['category'] = "debug";
                    $event['description'] = "Impossible de mettre à jour le status du line " . $config['lines_id'] . " ... Raison : " . $osC_Database->getError();

                    $osC_Database->rollbackTransaction();
                    return false;
                }
            } else {
                $_SESSION['error'] = "Impossible de mettre à jour le status du plant " . $config['plants_id'] . " ... Raison : " . $osC_Database->getError();

                $event['content_id'] = $config['plants_id'];
                $event['content_type'] = "plants";
                $event['type'] = "error";
                $event['event_date'] = date('Y-m-d H:i:s');
                $event['source'] = "cloud";
                $event['user'] = "Importer";
                $event['category'] = "debug";
                $event['description'] = "Impossible de mettre à jour le status du plant " . $config['plants_id'] . " ... Raison : " . $osC_Database->getError();

                $osC_Database->rollbackTransaction();

                return false;
            }
        }

        $osC_Database->commitTransaction();

        return true;
    }

    function saveFromRobot($id = null, $data)
    {
        global $osC_Database, $osC_Language;

        $category_id = '';
        $error = false;

        $osC_Database->startTransaction();

        if (is_numeric($id)) {
            $Qcat = $osC_Database->query('update :table_categories set categories_status = :categories_status, sort_order = :sort_order, last_modified = now() where categories_id = :categories_id');
            $Qcat->bindInt(':categories_id', $id);
        } else {
            $Qcat = $osC_Database->query('insert into :table_categories (parent_id, categories_status, sort_order, date_added) values (:parent_id, :categories_status, :sort_order, now())');
            $Qcat->bindInt(':parent_id', $data['parent_id']);
        }

        $Qcat->bindTable(':table_categories', TABLE_CATEGORIES);
        $Qcat->bindInt(':sort_order', $data['sort_order']);
        $Qcat->bindInt(':categories_status', $data['categories_status']);
        $Qcat->setLogging($_SESSION['module'], $id);
        $Qcat->execute();

        if (!$osC_Database->isError()) {
            $category_id = (is_numeric($id)) ? $id : $osC_Database->nextID();

            if (is_numeric($id)) {
                if ($data['categories_status']) {
                    $Qpstatus = $osC_Database->query('update :table_products set products_status = 1 where products_id in (select products_id from :table_products_to_categories where categories_id = :categories_id)');
                    $Qpstatus->bindTable(':table_products', TABLE_PRODUCTS);
                    $Qpstatus->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
                    $Qpstatus->bindInt(":categories_id", $id);
                    $Qpstatus->execute();
                } else {
                    if ($data['flag']) {
                        $Qpstatus = $osC_Database->query('update :table_products set products_status = 0 where products_id in (select products_id from :table_products_to_categories where categories_id = :categories_id)');
                        $Qpstatus->bindTable(':table_products', TABLE_PRODUCTS);
                        $Qpstatus->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
                        $Qpstatus->bindInt(":categories_id", $id);
                        $Qpstatus->execute();
                    }
                }
            }

            if ($osC_Database->isError()) {
                $error = true;
            }

            foreach ($osC_Language->getAll() as $l) {
                if (is_numeric($id)) {
                    $Qcd = $osC_Database->query('update :table_categories_description set categories_name = :categories_name, categories_url = :categories_url, categories_page_title = :categories_page_title, categories_meta_keywords = :categories_meta_keywords, categories_meta_description = :categories_meta_description where categories_id = :categories_id and language_id = :language_id');
                } else {
                    $Qcd = $osC_Database->query('insert into :table_categories_description (categories_id, language_id, categories_name, categories_url, categories_page_title, categories_meta_keywords, categories_meta_description) values (:categories_id, :language_id, :categories_name, :categories_url, :categories_page_title, :categories_meta_keywords, :categories_meta_description)');
                }

                $Qcd->bindTable(':table_categories_description', TABLE_CATEGORIES_DESCRIPTION);
                $Qcd->bindInt(':categories_id', $category_id);
                $Qcd->bindInt(':language_id', $l['id']);
                $Qcd->bindValue(':categories_name', $data['name'][$l['id']]);
                $Qcd->bindValue(':categories_url', ($data['url'][$l['id']] == '') ? $data['name'][$l['id']]
                    : $data['url'][$l['id']]);
                $Qcd->bindValue(':categories_page_title', $data['page_title'][$l['id']]);
                $Qcd->bindValue(':categories_meta_keywords', $data['meta_keywords'][$l['id']]);
                $Qcd->bindValue(':categories_meta_description', $data['meta_description'][$l['id']]);
                $Qcd->setLogging($_SESSION['module'], $category_id);
                $Qcd->execute();

                if ($osC_Database->isError()) {
                    $error = true;
                    break;
                }
            }

            $Qdelete = $osC_Database->query('delete from :toc_categories_ratings where categories_id = :categories_id');
            $Qdelete->bindTable(':toc_categories_ratings', TABLE_CATEGORIES_RATINGS);
            $Qdelete->bindInt(':categories_id', $category_id);
            $Qdelete->execute();

            if (!empty($data['ratings'])) {
                $ratings = explode(',', $data['ratings']);

                foreach ($ratings as $ratings_id) {
                    $Qinsert = $osC_Database->query('insert into :toc_categories_ratings (categories_id, ratings_id) values (:categories_id, :ratings_id)');
                    $Qinsert->bindTable(':toc_categories_ratings', TABLE_CATEGORIES_RATINGS);
                    $Qinsert->bindInt(':categories_id', $category_id);
                    $Qinsert->bindInt(':ratings_id', $ratings_id);
                    $Qinsert->execute();

                    if ($osC_Database->isError()) {
                        $error = true;
                        break;
                    }
                }
            }

            if ($error === false) {
                //$categories_image = new upload($data['image'], realpath('../' . DIR_WS_IMAGES . 'categories'));

                $Qimage = $osC_Database->query('select categories_image from :table_categories where categories_id = :categories_id');
                $Qimage->bindTable(':table_categories', TABLE_CATEGORIES);
                $Qimage->bindInt(':categories_id', $category_id);
                $Qimage->execute();

                $old_image = $Qimage->value('categories_image');

                if (!empty($old_image)) {
                    $Qcheck = $osC_Database->query('select count(*) as image_count from :table_categories where categories_image = :categories_image');
                    $Qcheck->bindTable(':table_categories', TABLE_CATEGORIES);
                    $Qcheck->bindValue(':categories_image', $old_image);
                    $Qcheck->execute();

                    if ($Qcheck->valueInt('image_count') == 1) {
                        $path = realpath('../' . DIR_WS_IMAGES . 'categories') . '/' . $old_image;
                        unlink($path);
                    }
                }

                $Qcf = $osC_Database->query('update :table_categories set categories_image = :categories_image where categories_id = :categories_id');
                $Qcf->bindTable(':table_categories', TABLE_CATEGORIES);
                $Qcf->bindValue(':categories_image', '../' . DIR_WS_IMAGES . 'categories' . '/' . $data['image']);
                $Qcf->bindInt(':categories_id', $category_id);
                $Qcf->setLogging($_SESSION['module'], $category_id);
                $Qcf->execute();

                if ($osC_Database->isError()) {
                    $error = true;
                }
            }
        }

        if ($error === false) {
            $osC_Database->commitTransaction();

            osC_Cache::clear('categories');
            osC_Cache::clear('category_tree');
            osC_Cache::clear('also_purchased');

            return $category_id;
        }

        $osC_Database->rollbackTransaction();

        return false;
    }

    function delete($id)
    {
        global $osC_Database, $osC_CategoryTree;

        $error = false;

        if (is_numeric($id)) {
            $osC_CategoryTree->setBreadcrumbUsage(false);

            $categories = array_merge(array(array('id' => $id, 'text' => '')), $osC_CategoryTree->getTree($id));
            $products = array();
            $products_delete = array();

            foreach ($categories as $c_entry) {
                $query = 'select products_id from :table_products_to_categories where categories_id = :categories_id';
                $Qproducts = $osC_Database->query($query);
                $Qproducts->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
                $Qproducts->bindInt(':categories_id', $c_entry['id']);
                $Qproducts->execute();

                while ($Qproducts->next()) {
                    $products[$Qproducts->valueInt('products_id')]['categories'][] = $c_entry['id'];
                }
            }

            foreach ($products as $key => $value) {
                $Qcheck = $osC_Database->query('select count(*) as total from :table_products_to_categories where products_id = :products_id and categories_id not in :categories_id');
                $Qcheck->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
                $Qcheck->bindInt(':products_id', $key);
                $Qcheck->bindRaw(':categories_id', '("' . implode('", "', $value['categories']) . '")');
                $Qcheck->execute();

                if ($Qcheck->valueInt('total') < 1) {
                    $products_delete[$key] = $key;
                }
            }

            osc_set_time_limit(0);

            foreach ($categories as $c_entry) {
                $osC_Database->startTransaction();

                $Qimage = $osC_Database->query('select categories_image from :table_categories where categories_id = :categories_id');
                $Qimage->bindTable(':table_categories', TABLE_CATEGORIES);
                $Qimage->bindInt(':categories_id', $c_entry['id']);
                $Qimage->execute();

                $image = $Qimage->value('categories_image');

                if (!empty($image)) {
                    $Qcheck = $osC_Database->query('select count(*) as image_count from :table_categories where categories_image = :categories_image');
                    $Qcheck->bindTable(':table_categories', TABLE_CATEGORIES);
                    $Qcheck->bindValue(':categories_image', $image);
                    $Qcheck->execute();

                    if ($Qcheck->valueInt('image_count') == 1) {
                        $path = realpath('../' . DIR_WS_IMAGES . 'categories') . '\\' . $image;
                        if (file_exists($path)) {
                            unlink($path);
                        }
                    }
                }

                $Qc = $osC_Database->query('delete from :table_categories where categories_id = :categories_id');
                $Qc->bindTable(':table_categories', TABLE_CATEGORIES);
                $Qc->bindInt(':categories_id', $c_entry['id']);
                $Qc->setLogging($_SESSION['module'], $id);
                $Qc->execute();

                if ($osC_Database->isError()) {
                    $error = true;
                }

                if ($error === false) {
                    $Qratings = $osC_Database->query('delete from :table_categories_ratings where categories_id = :categories_id');
                    $Qratings->bindTable(':table_categories_ratings', TABLE_CATEGORIES_RATINGS);
                    $Qratings->bindInt(':categories_id', $id);
                    $Qratings->setLogging($_SESSION['module'], $id);
                    $Qratings->execute();

                    if ($osC_Database->isError()) {
                        $error = true;
                    }
                }

                if ($error === false) {
                    $Qcd = $osC_Database->query('delete from :table_categories_description where categories_id = :categories_id');
                    $Qcd->bindTable(':table_categories_description', TABLE_CATEGORIES_DESCRIPTION);
                    $Qcd->bindInt(':categories_id', $c_entry['id']);
                    $Qcd->setLogging($_SESSION['module'], $id);
                    $Qcd->execute();

                    if (!$osC_Database->isError()) {
                        $Qp2c = $osC_Database->query('delete from :table_products_to_categories where categories_id = :categories_id');
                        $Qp2c->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
                        $Qp2c->bindInt(':categories_id', $c_entry['id']);
                        $Qp2c->setLogging($_SESSION['module'], $id);
                        $Qp2c->execute();

                        if (!$osC_Database->isError()) {
                            $osC_Database->commitTransaction();

                            osC_Cache::clear('categories');
                            osC_Cache::clear('category_tree');
                            osC_Cache::clear('also_purchased');
                            osC_Cache::clear('sefu-products');
                            osC_Cache::clear('new_products');

                            if (!osc_empty($Qimage->value('categories_image'))) {
                                $Qcheck = $osC_Database->query('select count(*) as total from :table_categories where categories_image = :categories_image');
                                $Qcheck->bindTable(':table_categories', TABLE_CATEGORIES);
                                $Qcheck->bindValue(':categories_image', $Qimage->value('categories_image'));
                                $Qcheck->execute();

                                if ($Qcheck->numberOfRows() === 0) {
                                    if (file_exists(realpath('../' . DIR_WS_IMAGES . 'categories/' . $Qimage->value('categories_image')))) {
                                        @unlink(realpath('../' . DIR_WS_IMAGES . 'categories/' . $Qimage->value('categories_image')));
                                    }
                                }
                            }
                        } else {
                            $osC_Database->rollbackTransaction();
                        }
                    } else {
                        $osC_Database->rollbackTransaction();
                    }
                } else {
                    $osC_Database->rollbackTransaction();
                }
            }

            foreach ($products_delete as $id) {
                osC_Products_Admin::delete($id);
            }

            osC_Cache::clear('categories');
            osC_Cache::clear('category_tree');
            osC_Cache::clear('also_purchased');
            osC_Cache::clear('sefu-products');
            osC_Cache::clear('new_products');

            return true;
        }

        return false;
    }

    function deletePlan($id)
    {
        global $osC_Database;

        $error = false;

        $lines = array();
        $lines_delete = array();

        if (is_numeric($id)) {
            $Qlines = $osC_Database->query('select lines_id from :table_lines where plants_id = :categories_id');
            $Qlines->bindTable(':table_lines', TABLE_LINES);
            $Qlines->bindInt(':plants_id', $id);
            $Qlines->execute();

            while ($Qlines->next()) {
                $lines[$Qlines->valueInt('lines_id')]['categories'][] = $id;
            }

            foreach ($lines as $key => $value) {
                $lines_delete[$key] = $key;
            }

            osc_set_time_limit(0);

            $osC_Database->startTransaction();

            $Qimage = $osC_Database->query('select categories_image from :table_layout where categories_id = :categories_id');
            $Qimage->bindTable(':table_layout', TABLE_LAYOUT);
            $Qimage->bindInt(':categories_id', $id);
            $Qimage->execute();

            $image = $Qimage->value('categories_image');

            if (!empty($image)) {
                $Qcheck = $osC_Database->query('select count(*) as image_count from :table_layout where categories_image = :categories_image');
                $Qcheck->bindTable(':table_layout', TABLE_LAYOUT);
                $Qcheck->bindValue(':categories_image', $image);
                $Qcheck->execute();

                if ($Qcheck->valueInt('image_count') == 1) {
                    $path = realpath('../' . DIR_WS_IMAGES . 'categories') . '\\' . $image;
                    if (file_exists($path)) {
                        unlink($path);
                    }
                }
            }

            $Qc = $osC_Database->query('delete from :table_layout where categories_id = :categories_id');
            $Qc->bindTable(':table_layout', TABLE_LAYOUT);
            $Qc->bindInt(':categories_id', $id);
            $Qc->setLogging($_SESSION['module'], $id);
            $Qc->execute();

            if ($osC_Database->isError()) {
                $error = true;
                $_SESSION['error'] = $osC_Database->error;
            }

            if ($error === false) {
                $Qcd = $osC_Database->query('delete from :table_layout_description where categories_id = :categories_id');
                $Qcd->bindTable(':table_layout_description', TABLE_LAYOUT_DESCRIPTION);
                $Qcd->bindInt(':categories_id', $id);
                $Qcd->setLogging($_SESSION['module'], $id);
                $Qcd->execute();

                if (!$osC_Database->isError()) {
                    $Qp2c = $osC_Database->query('delete from :table_plants where plants_id = :categories_id');
                    $Qp2c->bindTable(':table_plants', TABLE_PLANTS);
                    $Qp2c->bindInt(':categories_id', $id);
                    $Qp2c->setLogging($_SESSION['module'], $id);
                    $Qp2c->execute();

                    if (!$osC_Database->isError()) {
                        $osC_Database->commitTransaction();

                        osC_Cache::clear('categories');
                        osC_Cache::clear('layout_tree');

                        if (!osc_empty($Qimage->value('categories_image'))) {
                            $Qcheck = $osC_Database->query('select count(*) as total from :table_layout where categories_image = :categories_image');
                            $Qcheck->bindTable(':table_layout', TABLE_LAYOUT);
                            $Qcheck->bindValue(':categories_image', $Qimage->value('categories_image'));
                            $Qcheck->execute();

                            if ($Qcheck->numberOfRows() === 0) {
                                if (file_exists(realpath('../' . DIR_WS_IMAGES . 'categories/' . $Qimage->value('categories_image')))) {
                                    @unlink(realpath('../' . DIR_WS_IMAGES . 'categories/' . $Qimage->value('categories_image')));
                                }
                            }
                        }
                    } else {
                        $osC_Database->rollbackTransaction();
                    }
                } else {
                    $osC_Database->rollbackTransaction();
                }
            } else {
                $osC_Database->rollbackTransaction();
            }

            foreach ($lines_delete as $id) {
                osC_Products_Admin::delete($id);
            }

            osC_Cache::clear('plants');
            osC_Cache::clear('layout_tree');

            return true;
        }

        return false;
    }

    function deleteComponent($id)
    {
        global $osC_Database;

        $error = false;

        $sensors = array();
        $sensors_delete = array();

        if (is_numeric($id)) {
            $Qcomponent = $osC_Database->query('select sensors_id from :table_sensor where component_id = :component_id');
            $Qcomponent->bindTable(':table_sensor', TABLE_SENSOR);
            $Qcomponent->bindInt(':component_id', $id);
            $Qcomponent->execute();

            while ($Qcomponent->next()) {
                $sensors[$Qcomponent->valueInt('sensors_id')]['categories'][] = $id;
            }

            foreach ($sensors as $key => $value) {
                $sensors_delete[$key] = $key;
            }

            osc_set_time_limit(0);

            $osC_Database->startTransaction();

            $Qimage = $osC_Database->query('select categories_image from :table_layout where categories_id = :categories_id');
            $Qimage->bindTable(':table_layout', TABLE_LAYOUT);
            $Qimage->bindInt(':categories_id', $id);
            $Qimage->execute();

            $image = $Qimage->value('categories_image');

            if (!empty($image)) {
                $Qcheck = $osC_Database->query('select count(*) as image_count from :table_layout where categories_image = :categories_image');
                $Qcheck->bindTable(':table_layout', TABLE_LAYOUT);
                $Qcheck->bindValue(':categories_image', $image);
                $Qcheck->execute();

                if ($Qcheck->valueInt('image_count') == 1) {
                    $path = realpath('../' . DIR_WS_IMAGES . 'categories') . '\\' . $image;
                    if (file_exists($path)) {
                        unlink($path);
                    }
                }
            }

            $Qc = $osC_Database->query('delete from :table_layout where categories_id = :categories_id');
            $Qc->bindTable(':table_layout', TABLE_LAYOUT);
            $Qc->bindInt(':categories_id', $id);
            $Qc->setLogging($_SESSION['module'], $id);
            $Qc->execute();

            if ($osC_Database->isError()) {
                $error = true;
                $_SESSION['error'] = $osC_Database->error;
            }

            if ($error === false) {
                $Qcd = $osC_Database->query('delete from :table_layout_description where categories_id = :categories_id');
                $Qcd->bindTable(':table_layout_description', TABLE_LAYOUT_DESCRIPTION);
                $Qcd->bindInt(':categories_id', $id);
                $Qcd->setLogging($_SESSION['module'], $id);
                $Qcd->execute();

                if (!$osC_Database->isError()) {
                    $Qp2c = $osC_Database->query('delete from :table_component where component_id = :categories_id');
                    $Qp2c->bindTable(':table_component', TABLE_COMPONENT);
                    $Qp2c->bindInt(':categories_id', $id);
                    $Qp2c->setLogging($_SESSION['module'], $id);
                    $Qp2c->execute();

                    if (!$osC_Database->isError()) {
                        $osC_Database->commitTransaction();

                        osC_Cache::clear('categories');
                        osC_Cache::clear('layout_tree');

                        if (!osc_empty($Qimage->value('categories_image'))) {
                            $Qcheck = $osC_Database->query('select count(*) as total from :table_layout where categories_image = :categories_image');
                            $Qcheck->bindTable(':table_layout', TABLE_LAYOUT);
                            $Qcheck->bindValue(':categories_image', $Qimage->value('categories_image'));
                            $Qcheck->execute();

                            if ($Qcheck->numberOfRows() === 0) {
                                if (file_exists(realpath('../' . DIR_WS_IMAGES . 'categories/' . $Qimage->value('categories_image')))) {
                                    @unlink(realpath('../' . DIR_WS_IMAGES . 'categories/' . $Qimage->value('categories_image')));
                                }
                            }
                        }
                    } else {
                        $osC_Database->rollbackTransaction();
                    }
                } else {
                    $osC_Database->rollbackTransaction();
                }
            } else {
                $osC_Database->rollbackTransaction();
            }

            foreach ($sensors_delete as $id) {
                osC_Products_Admin::delete($id);
            }

            osC_Cache::clear('component');
            osC_Cache::clear('layout_tree');

            return true;
        }

        return false;
    }

    function deleteSensor($id)
    {
        global $osC_Database;

        $error = false;

        if (is_numeric($id)) {
            $osC_Database->startTransaction();

            $Qimage = $osC_Database->query('select categories_image from :table_layout where categories_id = :categories_id');
            $Qimage->bindTable(':table_layout', TABLE_LAYOUT);
            $Qimage->bindInt(':categories_id', $id);
            $Qimage->execute();

            $image = $Qimage->value('categories_image');

            if (!empty($image)) {
                $Qcheck = $osC_Database->query('select count(*) as image_count from :table_layout where categories_image = :categories_image');
                $Qcheck->bindTable(':table_layout', TABLE_LAYOUT);
                $Qcheck->bindValue(':categories_image', $image);
                $Qcheck->execute();

                if ($Qcheck->valueInt('image_count') == 1) {
                    $path = realpath('../' . DIR_WS_IMAGES . 'categories') . '\\' . $image;
                    if (file_exists($path)) {
                        unlink($path);
                    }
                }
            }

            $Qc = $osC_Database->query('delete from :table_layout where categories_id = :categories_id');
            $Qc->bindTable(':table_layout', TABLE_LAYOUT);
            $Qc->bindInt(':categories_id', $id);
            $Qc->setLogging($_SESSION['module'], $id);
            $Qc->execute();

            if ($osC_Database->isError()) {
                $error = true;
                $_SESSION['error'] = $osC_Database->error;
            }

            if ($error === false) {
                $Qcd = $osC_Database->query('delete from :table_layout_description where categories_id = :categories_id');
                $Qcd->bindTable(':table_layout_description', TABLE_LAYOUT_DESCRIPTION);
                $Qcd->bindInt(':categories_id', $id);
                $Qcd->setLogging($_SESSION['module'], $id);
                $Qcd->execute();

                if (!$osC_Database->isError()) {
                    $Qp2c = $osC_Database->query('delete from :table_sensor where sensors_id = :categories_id');
                    $Qp2c->bindTable(':table_sensor', TABLE_SENSOR);
                    $Qp2c->bindInt(':categories_id', $id);
                    $Qp2c->setLogging($_SESSION['module'], $id);
                    $Qp2c->execute();

                    if (!$osC_Database->isError()) {
                        $osC_Database->commitTransaction();

                        osC_Cache::clear('categories');
                        osC_Cache::clear('layout_tree');

                        if (!osc_empty($Qimage->value('categories_image'))) {
                            $Qcheck = $osC_Database->query('select count(*) as total from :table_layout where categories_image = :categories_image');
                            $Qcheck->bindTable(':table_layout', TABLE_LAYOUT);
                            $Qcheck->bindValue(':categories_image', $Qimage->value('categories_image'));
                            $Qcheck->execute();

                            if ($Qcheck->numberOfRows() === 0) {
                                if (file_exists(realpath('../' . DIR_WS_IMAGES . 'categories/' . $Qimage->value('categories_image')))) {
                                    @unlink(realpath('../' . DIR_WS_IMAGES . 'categories/' . $Qimage->value('categories_image')));
                                }
                            }
                        }
                    } else {
                        $osC_Database->rollbackTransaction();
                    }
                } else {
                    $osC_Database->rollbackTransaction();
                }
            } else {
                $osC_Database->rollbackTransaction();
            }

            osC_Cache::clear('sensor');
            osC_Cache::clear('layout_tree');

            return true;
        }

        return false;
    }

    function deleteLine($id)
    {
        global $osC_Database;

        $error = false;

        $asset = array();
        $asset_delete = array();

        if (is_numeric($id)) {
            $Qasset = $osC_Database->query('select asset_id from :table_asset where lines_id = :lines_id');
            $Qasset->bindTable(':table_asset', TABLE_ASSET);
            $Qasset->bindInt(':lines_id', $id);
            $Qasset->execute();

            while ($Qasset->next()) {
                $asset[$Qasset->valueInt('asset_id')]['categories'][] = $id;
            }

            foreach ($asset as $key => $value) {
                $asset_delete[$key] = $key;
            }

            osc_set_time_limit(0);

            $osC_Database->startTransaction();

            $Qimage = $osC_Database->query('select categories_image from :table_layout where categories_id = :categories_id');
            $Qimage->bindTable(':table_layout', TABLE_LAYOUT);
            $Qimage->bindInt(':categories_id', $id);
            $Qimage->execute();

            $image = $Qimage->value('categories_image');

            if (!empty($image)) {
                $Qcheck = $osC_Database->query('select count(*) as image_count from :table_layout where categories_image = :categories_image');
                $Qcheck->bindTable(':table_layout', TABLE_LAYOUT);
                $Qcheck->bindValue(':categories_image', $image);
                $Qcheck->execute();

                if ($Qcheck->valueInt('image_count') == 1) {
                    $path = realpath('../' . DIR_WS_IMAGES . 'categories') . '\\' . $image;
                    if (file_exists($path)) {
                        unlink($path);
                    }
                }
            }

            $Qc = $osC_Database->query('delete from :table_layout where categories_id = :categories_id');
            $Qc->bindTable(':table_layout', TABLE_LAYOUT);
            $Qc->bindInt(':categories_id', $id);
            $Qc->setLogging($_SESSION['module'], $id);
            $Qc->execute();

            if ($osC_Database->isError()) {
                $error = true;
                $_SESSION['error'] = $osC_Database->error;
            }

            if ($error === false) {
                $Qcd = $osC_Database->query('delete from :table_layout_description where categories_id = :categories_id');
                $Qcd->bindTable(':table_layout_description', TABLE_LAYOUT_DESCRIPTION);
                $Qcd->bindInt(':categories_id', $id);
                $Qcd->setLogging($_SESSION['module'], $id);
                $Qcd->execute();

                if (!$osC_Database->isError()) {
                    $Qp2c = $osC_Database->query('delete from :table_lines where lines_id = :categories_id');
                    $Qp2c->bindTable(':table_lines', TABLE_LINES);
                    $Qp2c->bindInt(':categories_id', $id);
                    $Qp2c->setLogging($_SESSION['module'], $id);
                    $Qp2c->execute();

                    if (!$osC_Database->isError()) {
                        $osC_Database->commitTransaction();

                        osC_Cache::clear('categories');
                        osC_Cache::clear('layout_tree');

                        if (!osc_empty($Qimage->value('categories_image'))) {
                            $Qcheck = $osC_Database->query('select count(*) as total from :table_layout where categories_image = :categories_image');
                            $Qcheck->bindTable(':table_layout', TABLE_LAYOUT);
                            $Qcheck->bindValue(':categories_image', $Qimage->value('categories_image'));
                            $Qcheck->execute();

                            if ($Qcheck->numberOfRows() === 0) {
                                if (file_exists(realpath('../' . DIR_WS_IMAGES . 'categories/' . $Qimage->value('categories_image')))) {
                                    @unlink(realpath('../' . DIR_WS_IMAGES . 'categories/' . $Qimage->value('categories_image')));
                                }
                            }
                        }
                    } else {
                        $osC_Database->rollbackTransaction();
                    }
                } else {
                    $osC_Database->rollbackTransaction();
                }
            } else {
                $osC_Database->rollbackTransaction();
            }

            foreach ($asset_delete as $id) {
                osC_Products_Admin::delete($id);
            }

            osC_Cache::clear('lines');
            osC_Cache::clear('plants');
            osC_Cache::clear('layout_tree');

            return true;
        }

        return false;
    }

    function deleteAsset($id)
    {
        global $osC_Database;

        $error = false;

        $sensor = array();
        $sensor_delete = array();

        if (is_numeric($id)) {
            $Qasset = $osC_Database->query('select sensor_id from :table_sensor where asset_id = :asset_id');
            $Qasset->bindTable(':table_sensor', TABLE_ASSET);
            $Qasset->bindInt(':asset_id', $id);
            $Qasset->execute();

            while ($Qasset->next()) {
                $sensor[$Qasset->valueInt('sensor_id')]['categories'][] = $id;
            }

            foreach ($sensor as $key => $value) {
                $sensor_delete[$key] = $key;
            }

            osc_set_time_limit(0);

            $osC_Database->startTransaction();

            $Qimage = $osC_Database->query('select categories_image from :table_layout where categories_id = :categories_id');
            $Qimage->bindTable(':table_layout', TABLE_LAYOUT);
            $Qimage->bindInt(':categories_id', $id);
            $Qimage->execute();

            $image = $Qimage->value('categories_image');

            if (!empty($image)) {
                $Qcheck = $osC_Database->query('select count(*) as image_count from :table_layout where categories_image = :categories_image');
                $Qcheck->bindTable(':table_layout', TABLE_LAYOUT);
                $Qcheck->bindValue(':categories_image', $image);
                $Qcheck->execute();

                if ($Qcheck->valueInt('image_count') == 1) {
                    $path = realpath('../' . DIR_WS_IMAGES . 'categories') . '\\' . $image;
                    if (file_exists($path)) {
                        unlink($path);
                    }
                }
            }

            $Qc = $osC_Database->query('delete from :table_layout where categories_id = :categories_id');
            $Qc->bindTable(':table_layout', TABLE_LAYOUT);
            $Qc->bindInt(':categories_id', $id);
            $Qc->setLogging($_SESSION['module'], $id);
            $Qc->execute();

            if ($osC_Database->isError()) {
                $error = true;
                $_SESSION['error'] = $osC_Database->error;
            }

            if ($error === false) {
                $Qcd = $osC_Database->query('delete from :table_layout_description where categories_id = :categories_id');
                $Qcd->bindTable(':table_layout_description', TABLE_LAYOUT_DESCRIPTION);
                $Qcd->bindInt(':categories_id', $id);
                $Qcd->setLogging($_SESSION['module'], $id);
                $Qcd->execute();

                if (!$osC_Database->isError()) {
                    $Qp2c = $osC_Database->query('delete from :table_asset where asset_id = :categories_id');
                    $Qp2c->bindTable(':table_asset', TABLE_ASSET);
                    $Qp2c->bindInt(':categories_id', $id);
                    $Qp2c->setLogging($_SESSION['module'], $id);
                    $Qp2c->execute();

                    if (!$osC_Database->isError()) {
                        $osC_Database->commitTransaction();

                        osC_Cache::clear('categories');
                        osC_Cache::clear('layout_tree');

                        if (!osc_empty($Qimage->value('categories_image'))) {
                            $Qcheck = $osC_Database->query('select count(*) as total from :table_layout where categories_image = :categories_image');
                            $Qcheck->bindTable(':table_layout', TABLE_LAYOUT);
                            $Qcheck->bindValue(':categories_image', $Qimage->value('categories_image'));
                            $Qcheck->execute();

                            if ($Qcheck->numberOfRows() === 0) {
                                if (file_exists(realpath('../' . DIR_WS_IMAGES . 'categories/' . $Qimage->value('categories_image')))) {
                                    @unlink(realpath('../' . DIR_WS_IMAGES . 'categories/' . $Qimage->value('categories_image')));
                                }
                            }
                        }
                    } else {
                        $osC_Database->rollbackTransaction();
                    }
                } else {
                    $osC_Database->rollbackTransaction();
                }
            } else {
                $osC_Database->rollbackTransaction();
            }

            foreach ($sensor_delete as $id) {
                osC_Products_Admin::delete($id);
            }

            osC_Cache::clear('lines');
            osC_Cache::clear('plants');
            osC_Cache::clear('layout_tree');

            return true;
        }

        return false;
    }

    function move($id, $new_id)
    {
        global $osC_Database;

        $category_array = explode('_', $new_id);

        if (in_array($id, $category_array)) {
            return false;
        }

        $Qupdate = $osC_Database->query('update :table_layout set parent_id = :parent_id, last_modified = now() where categories_id = :categories_id');
        $Qupdate->bindTable(':table_layout', TABLE_LAYOUT);
        $Qupdate->bindInt(':parent_id', end($category_array));
        $Qupdate->bindInt(':categories_id', $id);
        $Qupdate->setLogging($_SESSION['module'], $id);
        $Qupdate->execute();

        osC_Cache::clear('layout');
        osC_Cache::clear('layout_tree');

        return true;
    }

    function getPermissions($categories_id, $roles_id = null)
    {
        global $osC_Database;
        $Qpermissions = $osC_Database->query('select p.* from :table_permissions p where content_id = :categories_id and content_type = "pages"');
        $Qpermissions->bindTable(':table_permissions', TABLE_CONTENT_PERMISSIONS);
        $Qpermissions->bindInt(':categories_id', $categories_id);
        $Qpermissions->execute();

        $records = array();
        while ($Qpermissions->next()) {
            $records[] = array(
                'can_read' => $Qpermissions->value('can_read'),
                'can_write' => $Qpermissions->value('can_write'),
                'can_modify' => $Qpermissions->value('can_modify'),
                'can_publish' => $Qpermissions->value('can_publish')
            );
        }
        $Qpermissions->freeResult();

        $recs = array();
        $roles = array();

        if ($roles_id != null) {
            $roles[] = osC_Roles_Admin::getRole($roles_id);
        } else {
            $Qroles = $osC_Database->query('select r.*,a.* from :table_roles r INNER JOIN :table_administrators a ON (r.administrators_id = a.id) order by r.roles_name');
            $Qroles->bindTable(':table_roles', TABLE_ROLES);
            $Qroles->bindTable(':table_administrators', TABLE_ADMINISTRATORS);
            $Qroles->execute();

            $roles[] = array(
                'roles_id' => '-1',
                'user_name' => 'everyone',
                'email_address' => 'everyone',
                'roles_name' => 'Tout le monde',
                'roles_description' => 'Tout le monde',
                'icon' => osc_icon('folder_account.png')
            );

            while ($Qroles->next()) {
                $roles[] = array(
                    'roles_id' => $Qroles->valueInt('roles_id'),
                    'user_name' => $Qroles->value('user_name'),
                    'email_address' => $Qroles->value('email_address'),
                    'roles_name' => $Qroles->value('roles_name'),
                    'roles_description' => $Qroles->value('roles_description'),
                    'icon' => osc_icon('folder_account.png')
                );
            }
            $Qroles->freeResult();
        }

        if (count($records) > 0) {
            $permissions = $records[0];
        } else {
            $permissions = array(
                'can_read' => '',
                'can_write' => '',
                'can_modify' => '',
                'can_publish' => ''
            );
        }
        if (is_array($permissions)) {
            $read_permissions = explode(';', $permissions['can_read']);
            $write_permissions = explode(';', $permissions['can_write']);
            $modify_permissions = explode(';', $permissions['can_modify']);
            $publish_permissions = explode(';', $permissions['can_publish']);

            foreach ($roles as $role) {
                if (is_array($read_permissions) && in_array($role['roles_id'], $read_permissions)) {
                    $role['can_read'] = '1';
                } else {
                    $role['can_read'] = '0';
                }

                if (is_array($write_permissions) && in_array($role['roles_id'], $write_permissions)) {
                    $role['can_write'] = '1';
                } else {
                    $role['can_write'] = '0';
                }

                if (is_array($modify_permissions) && in_array($role['roles_id'], $modify_permissions)) {
                    $role['can_modify'] = '1';
                } else {
                    $role['can_modify'] = '0';
                }

                if (is_array($publish_permissions) && in_array($role['roles_id'], $publish_permissions)) {
                    $role['can_publish'] = '1';
                } else {
                    $role['can_publish'] = '0';
                }

                $role['categories_id'] = $_REQUEST['categories_id'];
                $recs[] = $role;
            }
        }

        return $recs;
    }

    function getCategoriesPermissions($categories_id)
    {
        global $osC_Database;

        $Qpermissions = $osC_Database->query('select p.* from :table_permissions p where categories_id = :categories_id');
        $Qpermissions->bindTable(':table_permissions', TABLE_CATEGORIES_PERMISSIONS);
        $Qpermissions->bindInt(':categories_id', $categories_id);
        $Qpermissions->execute();

        $records = array();
        while ($Qpermissions->next()) {
            $records[] = array(
                'can_read' => $Qpermissions->value('can_read'),
                'can_write' => $Qpermissions->value('can_write'),
                'can_modify' => $Qpermissions->value('can_modify'),
                'can_publish' => $Qpermissions->value('can_publish'),
                'is_set' => true
            );
        }
        $Qpermissions->freeResult();

        if (count($records) > 0) {
            $permissions = $records[0];
        } else {
            $permissions = array(
                'can_read' => '',
                'can_write' => '',
                'can_modify' => '',
                'can_publish' => '',
                'is_set' => false
            );
        }

        return $permissions;
    }

    function setPermission($categories_id, $permission, $roles_id, $flag)
    {
        global $osC_Database;

        $permissions = content::getContentPermissions($categories_id, 'pages');

        if (array_key_exists($permission, $permissions)) {
            $roles = explode(';', $permissions[$permission]);
            $new_roles = $permissions[$permission];
            if (in_array($roles_id, $roles) && $flag == '1') {
                //nothing to do....
            }

            if (in_array($roles_id, $roles) && $flag == '0') {
                $new_roles = '';
                foreach ($roles as $role) {
                    if ($role != $roles_id) {
                        $new_roles = $new_roles . ';' . $role;
                    }
                }
            }

            if (!in_array($roles_id, $roles) && $flag == '1') {
                $new_roles = $new_roles . $roles_id . ';';
            }

            if (!in_array($roles_id, $roles) && $flag == '0') {
                //nothing to do....
            }

            if ($permissions['is_set'] == true) {
                $Qpermission = $osC_Database->query('update :table_categories_permissions set :permission = :roles where categories_id = :categories_id');
            } else {
                $Qpermission = $osC_Database->query('insert into :table_categories_permissions (categories_id,:permission) values (:categories_id,:roles)');
            }

            $roles = explode(';', $new_roles);
            $new_roles = '';
            $set_roles = array();

            foreach ($roles as $id) {
                if ($id != '' || $id == '-1') {
                    if (!in_array($id, $set_roles)) {
                        $new_roles = $new_roles . $id . ';';
                        $set_roles[] = $id;
                    }
                }
            }

            $Qpermission->bindTable(':table_categories_permissions', TABLE_CATEGORIES_PERMISSIONS);
            $Qpermission->bindInt(":categories_id", $categories_id);
            $Qpermission->bindTable(":permission", $permission);
            $Qpermission->bindValue(":roles", $new_roles);
            $Qpermission->execute();

            if (!$osC_Database->isError()) {
                osC_Cache::clear('categories');
                osC_Cache::clear('category_tree');

                return true;
            }
        }

        return false;
    }

    function setStatus($id, $flag, $product_flag)
    {
        global $osC_Database;

        $error = false;

        $Qstatus = $osC_Database->query('update :table_categories set categories_status = :categories_status where categories_id = :categories_id');
        $Qstatus->bindTable(':table_categories', TABLE_CATEGORIES);
        $Qstatus->bindInt(":categories_id", $id);
        $Qstatus->bindValue(":categories_status", $flag);
        $Qstatus->execute();

        if (!$osC_Database->isError()) {
            if (($flag == 0) && ($product_flag == 1)) {
                $Qupdate = $osC_Database->query('update :table_products set products_status = 0 where products_id in (select products_id from :table_products_to_categories where categories_id = :categories_id)');
                $Qupdate->bindTable(':table_products', TABLE_PRODUCTS);
                $Qupdate->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
                $Qupdate->bindInt(":categories_id", $id);
                $Qupdate->execute();
            }
        }

        if (!$osC_Database->isError()) {
            osC_Cache::clear('categories');
            osC_Cache::clear('category_tree');
            osC_Cache::clear('also_purchased');
            osC_Cache::clear('sefu-products');
            osC_Cache::clear('new_products');

            return true;
        }

        return false;
    }

    function updateStatus($config, $status, $parameter)
    {
        global $osC_Database;

        $event = array();

        $query = "delete from :table_status where content_id = :customers_id or content_id = :plants_id or content_id = :lines_id or content_id = :asset_id or content_id = :component_id or content_id = :sensors_id";

        $Qupdate = $osC_Database->query($query);
        $Qupdate->bindTable(':table_status', TABLE_STATUS);
        $Qupdate->bindInt(":customers_id", $config['customers_id']);
        $Qupdate->bindInt(":plants_id", $config['plants_id']);
        $Qupdate->bindInt(":lines_id", $config['lines_id']);
        $Qupdate->bindInt(":asset_id", $config['asset_id']);
        $Qupdate->bindInt(":component_id", $config['component_id']);
        $Qupdate->bindInt(":sensors_id", $config['sensors_id']);
        $Qupdate->execute();

        $sensors_status = "error";
        $query = "select chstatus from :table_parameters p where 1 = 1 ";
        $query = $query . " and sensors_id = " . $config['sensors_id'];
        $query = $query . " order by p.eventdate desc limit 1";

        $QParameters = $osC_Database->query($query);
        $QParameters->bindTable(':table_parameters', TABLE_PARAMETERS);
        $QParameters->execute();

        while ($QParameters->next()) {
            $sensors_status = $QParameters->value('chstatus');
        }

        $component_status = "error";
        $query = "select chstatus from :table_parameters p where 1 = 1 ";
        $query = $query . " and component_id = " . $config['component_id'];
        $query = $query . " order by p.eventdate desc limit 1";

        $QParameters = $osC_Database->query($query);
        $QParameters->bindTable(':table_parameters', TABLE_PARAMETERS);
        $QParameters->execute();

        while ($QParameters->next()) {
            $component_status = $QParameters->value('chstatus');
        }

        $asset_status = $status == "error" ? $status : $component_status;

        $query = "select status from :table_status where content_id in (select categories_id from :table_layout where content_type = 'component' and parent_id = :asset_id)";
        $Qupdate = $osC_Database->query($query);
        $Qupdate->bindTable(':table_status', TABLE_STATUS);
        $Qupdate->bindTable(':table_layout', TABLE_LAYOUT);
        $Qupdate->bindInt(":asset_id", $config['asset_id']);
        $Qupdate->execute();

        while ($Qupdate->next()) {
            switch ($Qupdate->value('status')) {
                case "OK":
                    if ($asset_status == "OK") {
                        $asset_status = $Qupdate->value('status');
                    }
                    break;
                case "rAL":
                    if ($asset_status == "OK" || $asset_status == "rAL") {
                        $asset_status = $Qupdate->value('status');
                    }
                    break;
                case "pAL":
                    if ($asset_status == "OK" || $asset_status == "rAL" || $asset_status == "pAL") {
                        $asset_status = $Qupdate->value('status');
                    }
                    break;
                case "AL":
                    if ($asset_status == "OK" || $asset_status == "rAL" || $asset_status == "pAL" || $asset_status == "AL") {
                        $asset_status = $Qupdate->value('status');
                    }
                    break;
                case "error":
                    $asset_status = $Qupdate->value('status');
                    break;
                default:
                    $asset_status = $Qupdate->value('status');
            }
        }

        $line_status = $asset_status;

        $query = "select status from :table_status where content_id in (select categories_id from :table_layout where content_type = 'asset' and parent_id = :lines_id)";
        $Qupdate = $osC_Database->query($query);
        $Qupdate->bindTable(':table_status', TABLE_STATUS);
        $Qupdate->bindTable(':table_layout', TABLE_LAYOUT);
        $Qupdate->bindInt(":lines_id", $config['lines_id']);
        $Qupdate->execute();

        while ($Qupdate->next()) {
            switch ($Qupdate->value('status')) {
                case "OK":
                    if ($line_status == "OK") {
                        $line_status = $Qupdate->value('status');
                    }
                    break;
                case "rAL":
                    if ($line_status == "OK" || $line_status == "rAL") {
                        $line_status = $Qupdate->value('status');
                    }
                    break;
                case "pAL":
                    if ($line_status == "OK" || $line_status == "rAL" || $line_status == "pAL") {
                        $line_status = $Qupdate->value('status');
                    }
                    break;
                case "AL":
                    if ($line_status == "OK" || $line_status == "rAL" || $line_status == "pAL" || $line_status == "AL") {
                        $line_status = $Qupdate->value('status');
                    }
                    break;
                case "error":
                    $line_status = $Qupdate->value('status');
                    break;
                default:
                    $line_status = $Qupdate->value('status');
            }
        }

        $plant_status = $line_status;

        $query = "select status from :table_status where content_id in (select categories_id from :table_layout where content_type = 'line' and parent_id = :plants_id)";
        $Qupdate = $osC_Database->query($query);
        $Qupdate->bindTable(':table_status', TABLE_STATUS);
        $Qupdate->bindTable(':table_layout', TABLE_LAYOUT);
        $Qupdate->bindInt(":plants_id", $config['plants_id']);
        $Qupdate->execute();

        while ($Qupdate->next()) {
            switch ($Qupdate->value('status')) {
                case "OK":
                    if ($plant_status == "OK") {
                        $plant_status = $Qupdate->value('status');
                    }
                    break;
                case "rAL":
                    if ($plant_status == "OK" || $plant_status == "rAL") {
                        $plant_status = $Qupdate->value('status');
                    }
                    break;
                case "pAL":
                    if ($plant_status == "OK" || $plant_status == "rAL" || $plant_status == "pAL") {
                        $plant_status = $Qupdate->value('status');
                    }
                    break;
                case "AL":
                    if ($plant_status == "OK" || $plant_status == "rAL" || $plant_status == "pAL" || $plant_status == "AL") {
                        $plant_status = $Qupdate->value('status');
                    }
                    break;
                case "error":
                    $plant_status = $Qupdate->value('status');
                    break;
                default:
                    $plant_status = $Qupdate->value('status');
            }
        }

        $customers_status = $plant_status;

        $query = "select status from :table_status where content_id in (select categories_id from :table_layout where content_type = 'plant' and parent_id = :customers_id)";
        $Qupdate = $osC_Database->query($query);
        $Qupdate->bindTable(':table_status', TABLE_STATUS);
        $Qupdate->bindTable(':table_layout', TABLE_LAYOUT);
        $Qupdate->bindInt(":customers_id", $config['customers_id']);
        $Qupdate->execute();

        while ($Qupdate->next()) {
            switch ($Qupdate->value('status')) {
                case "OK":
                    if ($customers_status == "OK") {
                        $customers_status = $Qupdate->value('status');
                    }
                    break;
                case "rAL":
                    if ($customers_status == "OK" || $customers_status == "rAL") {
                        $customers_status = $Qupdate->value('status');
                    }
                    break;
                case "pAL":
                    if ($customers_status == "OK" || $customers_status == "rAL" || $customers_status == "pAL") {
                        $customers_status = $Qupdate->value('status');
                    }
                    break;
                case "AL":
                    if ($customers_status == "OK" || $customers_status == "rAL" || $customers_status == "pAL" || $customers_status == "AL") {
                        $customers_status = $Qupdate->value('status');
                    }
                    break;
                case "error":
                    $customers_status = $Qupdate->value('status');
                    break;
                default:
                    $customers_status = $Qupdate->value('status');
            }
        }

        if (!$osC_Database->isError()) {
            $query = "insert into :table_status (content_id, content_type, status,customers_id) VALUES (:content_id, :content_type, :status,:customers_id)";

            $Qupdate = $osC_Database->query($query);
            $Qupdate->bindTable(':table_status', TABLE_STATUS);
            $Qupdate->bindInt(":content_id", $config['customers_id']);
            $Qupdate->bindInt(":customers_id", $config['customers_id']);
            $Qupdate->bindValue(":content_type", 'customer');
            $Qupdate->bindValue(":status", $customers_status);
            $Qupdate->execute();

            if (!$osC_Database->isError()) {
                $query = "insert into :table_status (content_id, content_type, status,customers_id) VALUES (:content_id, :content_type, :status,:customers_id)";

                $Qupdate = $osC_Database->query($query);
                $Qupdate->bindTable(':table_status', TABLE_STATUS);
                $Qupdate->bindInt(":content_id", $config['plants_id']);
                $Qupdate->bindInt(":customers_id", $config['customers_id']);
                $Qupdate->bindValue(":content_type", 'plant');
                $Qupdate->bindValue(":status", $plant_status);
                $Qupdate->execute();

                if (!$osC_Database->isError()) {
                    $query = "insert into :table_status (content_id, content_type, status,customers_id) VALUES (:content_id, :content_type, :status,:customers_id)";

                    $Qupdate = $osC_Database->query($query);
                    $Qupdate->bindTable(':table_status', TABLE_STATUS);
                    $Qupdate->bindInt(":content_id", $config['lines_id']);
                    $Qupdate->bindInt(":customers_id", $config['customers_id']);
                    $Qupdate->bindValue(":content_type", 'line');
                    $Qupdate->bindValue(":status", $line_status);
                    $Qupdate->execute();

                    if (!$osC_Database->isError()) {
                        $query = "insert into :table_status (content_id, content_type, status,customers_id) VALUES (:content_id, :content_type, :status,:customers_id)";

                        $Qupdate = $osC_Database->query($query);
                        $Qupdate->bindTable(':table_status', TABLE_STATUS);
                        $Qupdate->bindInt(":content_id", $config['asset_id']);
                        $Qupdate->bindInt(":customers_id", $config['customers_id']);
                        $Qupdate->bindValue(":content_type", 'asset');
                        $Qupdate->bindValue(":status", $asset_status);
                        $Qupdate->execute();

                        if (!$osC_Database->isError()) {
                            $query = "insert into :table_status (content_id, content_type, status,customers_id) VALUES (:content_id, :content_type, :status,:customers_id)";

                            $Qupdate = $osC_Database->query($query);
                            $Qupdate->bindTable(':table_status', TABLE_STATUS);
                            $Qupdate->bindInt(":content_id", $config['component_id']);
                            $Qupdate->bindInt(":customers_id", $config['customers_id']);
                            $Qupdate->bindValue(":content_type", 'component');
                            $Qupdate->bindValue(":status", $component_status);
                            $Qupdate->execute();

                            if (!$osC_Database->isError()) {
                                $query = "insert into :table_status (content_id, content_type, status,customers_id) VALUES (:content_id, :content_type, :status,:customers_id)";

                                $Qupdate = $osC_Database->query($query);
                                $Qupdate->bindTable(':table_status', TABLE_STATUS);
                                $Qupdate->bindInt(":content_id", $config['sensors_id']);
                                $Qupdate->bindInt(":customers_id", $config['customers_id']);
                                $Qupdate->bindValue(":content_type", 'sensor');
                                $Qupdate->bindValue(":status", $sensors_status);
                                $Qupdate->execute();

                                //var_dump($Qupdate);

                                if ($osC_Database->isError()) {
                                    $_SESSION['error'] = "Impossible de mettre à jour le status du sensor " . $config['sensors_id'] . " ... Raison : " . $osC_Database->getError();

                                    $event['content_id'] = $config['sensors_id'];
                                    $event['content_type'] = "sensor";
                                    $event['type'] = "error";
                                    $event['event_date'] = date('Y-m-d H:i:s');
                                    $event['source'] = "cloud";
                                    $event['user'] = "Importer";
                                    $event['category'] = "debug";
                                    $event['description'] = "Impossible de mettre à jour le status du sensor " . $config['sensors_id'] . " ... Raison : " . $osC_Database->getError();;

                                    osC_Categories_Admin::logEvent($event);
                                    return false;
                                }
                            } else {
                                $_SESSION['error'] = "Impossible de mettre à jour le status du component " . $config['component_id'] . " ... Raison : " . $osC_Database->getError();

                                $event['content_id'] = $config['component_id'];
                                $event['content_type'] = "component";
                                $event['type'] = "error";
                                $event['event_date'] = date('Y-m-d H:i:s');
                                $event['source'] = "cloud";
                                $event['user'] = "Importer";
                                $event['category'] = "debug";
                                $event['description'] = "Impossible de mettre à jour le status du component " . $config['component_id'] . " ... Raison : " . $osC_Database->getError();

                                osC_Categories_Admin::logEvent($event);
                                return false;
                            }
                        } else {
                            $_SESSION['error'] = "Impossible de mettre à jour le status du asset " . $config['asset_id'] . " ... Raison : " . $osC_Database->getError();

                            $event['content_id'] = $config['asset_id'];
                            $event['content_type'] = "asset";
                            $event['type'] = "error";
                            $event['event_date'] = date('Y-m-d H:i:s');
                            $event['source'] = "cloud";
                            $event['user'] = "Importer";
                            $event['category'] = "debug";
                            $event['description'] = "Impossible de mettre à jour le status du asset " . $config['asset_id'] . " ... Raison : " . $osC_Database->getError();

                            osC_Categories_Admin::logEvent($event);
                            return false;
                        }
                    } else {
                        $_SESSION['error'] = "Impossible de mettre à jour le status du line " . $config['lines_id'] . " ... Raison : " . $osC_Database->getError();

                        $event['content_id'] = $config['lines_id'];
                        $event['content_type'] = "line";
                        $event['type'] = "error";
                        $event['event_date'] = date('Y-m-d H:i:s');
                        $event['source'] = "cloud";
                        $event['user'] = "Importer";
                        $event['category'] = "debug";
                        $event['description'] = "Impossible de mettre à jour le status du line " . $config['lines_id'] . " ... Raison : " . $osC_Database->getError();

                        osC_Categories_Admin::logEvent($event);
                        return false;
                    }
                } else {
                    $_SESSION['error'] = "Impossible de mettre à jour le status du plant " . $config['plants_id'] . " ... Raison : " . $osC_Database->getError();

                    $event['content_id'] = $config['plants_id'];
                    $event['content_type'] = "plants";
                    $event['type'] = "error";
                    $event['event_date'] = date('Y-m-d H:i:s');
                    $event['source'] = "cloud";
                    $event['user'] = "Importer";
                    $event['category'] = "debug";
                    $event['description'] = "Impossible de mettre à jour le status du plant " . $config['plants_id'] . " ... Raison : " . $osC_Database->getError();

                    osC_Categories_Admin::logEvent($event);
                    return false;
                }
            } else {
                $_SESSION['error'] = "Impossible de mettre à jour le status du client " . $config['customers_id'] . " ... Raison : " . $osC_Database->getError();

                $event['content_id'] = $config['customers_id'];
                $event['content_type'] = "customer";
                $event['type'] = "error";
                $event['event_date'] = date('Y-m-d H:i:s');
                $event['source'] = "cloud";
                $event['user'] = "Importer";
                $event['category'] = "debug";
                $event['description'] = "Impossible de mettre à jour le status du client " . $config['customers_id'] . " ... Raison : " . $osC_Database->getError();

                osC_Categories_Admin::logEvent($event);

                return false;
            }
        } else {
            $_SESSION['error'] = "Impossible de supprimer les status : " . $osC_Database->getError();

            $event['content_id'] = $config['customers_id'];
            $event['content_type'] = "customer";
            $event['type'] = "error";
            $event['event_date'] = date('Y-m-d H:i:s');
            $event['source'] = "cloud";
            $event['user'] = "Importer";
            $event['category'] = "debug";
            $event['description'] = "Impossible de supprimer les status : " . $osC_Database->getError();

            osC_Categories_Admin::logEvent($event);

            return false;
        }

        if ($sensors_status != $config['sensors_current_status']) {
            $event['content_id'] = $config['sensors_id'];
            $event['content_type'] = "sensor";
            $event['type'] = $sensors_status == "OK" ? "succes" : "error";
            $event['event_date'] = $parameter['eventdate'];
            $event['source'] = "cloud";
            $event['user'] = "Importer";
            $event['category'] = "sensor";
            $event['description'] = "Le Capteur " . $config['customer'] . " > " . $config['plant'] . " > " . $config['line'] . " > " . $config['asset'] . " > " . $config['component'] . " > " . $config['sensor'] . " est passé au status " . $sensors_status;

            osC_Categories_Admin::logEvent($event);

            $event_type = osC_Categories_Admin::getEventType($sensors_status);
            $event_action = osC_Categories_Admin::getEventAction($sensors_status);
            $event_ort = $config['customer'] . " > " . $config['plant'] . " > " . $config['line'] . " > " . $config['asset'] . " > " . $config['component'] . " > " . $config['sensor'];

            $body = "<html><head><title></title></head><body><table border='0' cellpadding='1' cellspacing='1' style='width: 100%;'><tbody><tr><td style='text-align: center; vertical-align: middle; background-color: rgb(204, 204, 204);'>";
            $body = $body . "<span style='color:#000080;'><strong>Date evenement</strong></span></td></tr><tr><td style='text-align: center;'><strong><span style='color:#800000;'>" . $event['event_date'] . "</span></strong></td></tr><tr><td style='text-align: center; vertical-align: middle; background-color: rgb(204, 204, 204);'>";
            $body = $body . "<span style='color:#000080;'><strong>Type evenement</strong></span></td></tr><tr><td style='text-align: center;'><strong><span style='color:#800000;'>" . $event_type . "</span></strong></td></tr><tr><td style='text-align: center; vertical-align: middle; background-color: rgb(204, 204, 204);'>";
            $body = $body . "<span style='color:#000080;'><strong>Lieu evenement</strong></span></td></tr><tr><td style='text-align: center;'><strong><span style='color:#800000;'>" . $event_ort . "</span></strong></td></tr><tr><td style='text-align: center; background-color: rgb(204, 204, 204);'>";
            $body = $body . "<span style='color:#000080;'><strong>Action</strong></span></td></tr><tr><td style='text-align: center;'><strong><span style='color:#800000;'>" . $event_action . "</span></strong></td></tr></tbody></table><p>&nbsp;</p></body></html>";

            content::sendNotification($event['content_id'], $event['content_type'], strtolower($config['sensors_current_status'] . '_' . $sensors_status), $event['description'], $body);
        }

        if ($component_status != $config['component_current_status']) {
            $event['content_id'] = $config['component_id'];
            $event['content_type'] = "component";
            $event['type'] = $component_status == "OK" ? "succes" : "error";
            $event['event_date'] = date('Y-m-d H:i:s');
            $event['source'] = "cloud";
            $event['user'] = "Importer";
            $event['category'] = "component";
            $event['description'] = "Le Composant " . $config['customer'] . " > " . $config['plant'] . " > " . $config['line'] . " > " . $config['asset'] . " > " . $config['component'] . " est passé au status " . $component_status;

            osC_Categories_Admin::logEvent($event);

            $event_type = osC_Categories_Admin::getEventType($component_status);
            $event_action = osC_Categories_Admin::getEventAction($component_status);
            $event_ort = $config['customer'] . " > " . $config['plant'] . " > " . $config['line'] . " > " . $config['asset'] . " > " . $config['component'];

            $body = "<html><head><title></title></head><body><table border='0' cellpadding='1' cellspacing='1' style='width: 100%;'><tbody><tr><td style='text-align: center; vertical-align: middle; background-color: rgb(204, 204, 204);'>";
            $body = $body . "<span style='color:#000080;'><strong>Date evenement</strong></span></td></tr><tr><td style='text-align: center;'><strong><span style='color:#800000;'>" . $event['event_date'] . "</span></strong></td></tr><tr><td style='text-align: center; vertical-align: middle; background-color: rgb(204, 204, 204);'>";
            $body = $body . "<span style='color:#000080;'><strong>Type evenement</strong></span></td></tr><tr><td style='text-align: center;'><strong><span style='color:#800000;'>" . $event_type . "</span></strong></td></tr><tr><td style='text-align: center; vertical-align: middle; background-color: rgb(204, 204, 204);'>";
            $body = $body . "<span style='color:#000080;'><strong>Lieu evenement</strong></span></td></tr><tr><td style='text-align: center;'><strong><span style='color:#800000;'>" . $event_ort . "</span></strong></td></tr><tr><td style='text-align: center; background-color: rgb(204, 204, 204);'>";
            $body = $body . "<span style='color:#000080;'><strong>Action</strong></span></td></tr><tr><td style='text-align: center;'><strong><span style='color:#800000;'>" . $event_action . "</span></strong></td></tr></tbody></table><p>&nbsp;</p></body></html>";

            content::sendNotification($event['content_id'], $event['content_type'], strtolower($config['component_current_status'] . '_' . $component_status), $event['description'], $body);
        }

        if ($asset_status != $config['asset_current_status']) {
            $event['content_id'] = $config['asset_id'];
            $event['content_type'] = "asset";
            $event['type'] = $asset_status == "OK" ? "succes" : "error";
            $event['event_date'] = date('Y-m-d H:i:s');
            $event['source'] = "cloud";
            $event['user'] = "Importer";
            $event['category'] = "asset";
            $event['description'] = "L'Asset " . $config['customer'] . " > " . $config['plant'] . " > " . $config['line'] . " > " . $config['asset'] . " est passé au status " . $asset_status;

            osC_Categories_Admin::logEvent($event);

            $event_type = osC_Categories_Admin::getEventType($asset_status);
            $event_action = osC_Categories_Admin::getEventAction($asset_status);
            $event_ort = $config['customer'] . " > " . $config['plant'] . " > " . $config['line'] . " > " . $config['asset'];

            $body = "<html><head><title></title></head><body><table border='0' cellpadding='1' cellspacing='1' style='width: 100%;'><tbody><tr><td style='text-align: center; vertical-align: middle; background-color: rgb(204, 204, 204);'>";
            $body = $body . "<span style='color:#000080;'><strong>Date evenement</strong></span></td></tr><tr><td style='text-align: center;'><strong><span style='color:#800000;'>" . $event['event_date'] . "</span></strong></td></tr><tr><td style='text-align: center; vertical-align: middle; background-color: rgb(204, 204, 204);'>";
            $body = $body . "<span style='color:#000080;'><strong>Type evenement</strong></span></td></tr><tr><td style='text-align: center;'><strong><span style='color:#800000;'>" . $event_type . "</span></strong></td></tr><tr><td style='text-align: center; vertical-align: middle; background-color: rgb(204, 204, 204);'>";
            $body = $body . "<span style='color:#000080;'><strong>Lieu evenement</strong></span></td></tr><tr><td style='text-align: center;'><strong><span style='color:#800000;'>" . $event_ort . "</span></strong></td></tr><tr><td style='text-align: center; background-color: rgb(204, 204, 204);'>";
            $body = $body . "<span style='color:#000080;'><strong>Action</strong></span></td></tr><tr><td style='text-align: center;'><strong><span style='color:#800000;'>" . $event_action . "</span></strong></td></tr></tbody></table><p>&nbsp;</p></body></html>";

            content::sendNotification($event['content_id'], $event['content_type'], strtolower($config['asset_current_status'] . '_' . $asset_status), $event['description'], $body);

            if ($asset_status != "OK") {
                $config['description'] = $event['description'];
                content::saveTicket(null, $config);
            }
        }

        if ($line_status != $config['line_current_status']) {
            $event['content_id'] = $config['lines_id'];
            $event['content_type'] = "line";
            $event['type'] = $line_status == "OK" ? "succes" : "error";
            $event['event_date'] = date('Y-m-d H:i:s');
            $event['source'] = "cloud";
            $event['user'] = "Importer";
            $event['category'] = "line";
            $event['description'] = "La Ligne " . $config['customer'] . " > " . $config['plant'] . " > " . $config['line'] . " est passé au status " . $line_status;

            osC_Categories_Admin::logEvent($event);

            $event_type = osC_Categories_Admin::getEventType($line_status);
            $event_action = osC_Categories_Admin::getEventAction($line_status);
            $event_ort = $config['customer'] . " > " . $config['plant'] . " > " . $config['line'];

            $body = "<html><head><title></title></head><body><table border='0' cellpadding='1' cellspacing='1' style='width: 100%;'><tbody><tr><td style='text-align: center; vertical-align: middle; background-color: rgb(204, 204, 204);'>";
            $body = $body . "<span style='color:#000080;'><strong>Date evenement</strong></span></td></tr><tr><td style='text-align: center;'><strong><span style='color:#800000;'>" . $event['event_date'] . "</span></strong></td></tr><tr><td style='text-align: center; vertical-align: middle; background-color: rgb(204, 204, 204);'>";
            $body = $body . "<span style='color:#000080;'><strong>Type evenement</strong></span></td></tr><tr><td style='text-align: center;'><strong><span style='color:#800000;'>" . $event_type . "</span></strong></td></tr><tr><td style='text-align: center; vertical-align: middle; background-color: rgb(204, 204, 204);'>";
            $body = $body . "<span style='color:#000080;'><strong>Lieu evenement</strong></span></td></tr><tr><td style='text-align: center;'><strong><span style='color:#800000;'>" . $event_ort . "</span></strong></td></tr><tr><td style='text-align: center; background-color: rgb(204, 204, 204);'>";
            $body = $body . "<span style='color:#000080;'><strong>Action</strong></span></td></tr><tr><td style='text-align: center;'><strong><span style='color:#800000;'>" . $event_action . "</span></strong></td></tr></tbody></table><p>&nbsp;</p></body></html>";

            content::sendNotification($event['content_id'], $event['content_type'], strtolower($config['line_current_status'] . '_' . $line_status), $event['description'], $body);
        }

        if ($plant_status != $config['plants_current_status']) {
            $event['content_id'] = $config['plants_id'];
            $event['content_type'] = "plant";
            $event['type'] = $plant_status == "OK" ? "succes" : "error";
            $event['event_date'] = date('Y-m-d H:i:s');
            $event['source'] = "cloud";
            $event['user'] = "Importer";
            $event['category'] = "plant";
            $event['description'] = "Le Plant " . $config['customer'] . " > " . $config['plant'] . " est passé au status " . $plant_status;

            osC_Categories_Admin::logEvent($event);

            $event_type = osC_Categories_Admin::getEventType($plant_status);
            $event_action = osC_Categories_Admin::getEventAction($plant_status);
            $event_ort = $config['customer'] . " > " . $config['plant'];

            $body = "<html><head><title></title></head><body><table border='0' cellpadding='1' cellspacing='1' style='width: 100%;'><tbody><tr><td style='text-align: center; vertical-align: middle; background-color: rgb(204, 204, 204);'>";
            $body = $body . "<span style='color:#000080;'><strong>Date evenement</strong></span></td></tr><tr><td style='text-align: center;'><strong><span style='color:#800000;'>" . $event['event_date'] . "</span></strong></td></tr><tr><td style='text-align: center; vertical-align: middle; background-color: rgb(204, 204, 204);'>";
            $body = $body . "<span style='color:#000080;'><strong>Type evenement</strong></span></td></tr><tr><td style='text-align: center;'><strong><span style='color:#800000;'>" . $event_type . "</span></strong></td></tr><tr><td style='text-align: center; vertical-align: middle; background-color: rgb(204, 204, 204);'>";
            $body = $body . "<span style='color:#000080;'><strong>Lieu evenement</strong></span></td></tr><tr><td style='text-align: center;'><strong><span style='color:#800000;'>" . $event_ort . "</span></strong></td></tr><tr><td style='text-align: center; background-color: rgb(204, 204, 204);'>";
            $body = $body . "<span style='color:#000080;'><strong>Action</strong></span></td></tr><tr><td style='text-align: center;'><strong><span style='color:#800000;'>" . $event_action . "</span></strong></td></tr></tbody></table><p>&nbsp;</p></body></html>";

            content::sendNotification($event['content_id'], $event['content_type'], strtolower($config['plants_current_status'] . '_' . $plant_status), $event['description'], $body);
        }

        if ($customers_status != $config['customers_current_status']) {
            $event['content_id'] = $config['customers_id'];
            $event['content_type'] = "customer";
            $event['type'] = $customers_status == "OK" ? "succes" : "error";
            $event['event_date'] = date('Y-m-d H:i:s');
            $event['source'] = "cloud";
            $event['user'] = "Importer";
            $event['category'] = "customer";
            $event['description'] = "Le Client " . $config['customer'] . " est passé au status " . $customers_status;

            osC_Categories_Admin::logEvent($event);

            $event_type = osC_Categories_Admin::getEventType($customers_status);
            $event_action = osC_Categories_Admin::getEventAction($customers_status);
            $event_ort = $config['customer'];

            $body = "<html><head><title></title></head><body><table border='0' cellpadding='1' cellspacing='1' style='width: 100%;'><tbody><tr><td style='text-align: center; vertical-align: middle; background-color: rgb(204, 204, 204);'>";
            $body = $body . "<span style='color:#000080;'><strong>Date evenement</strong></span></td></tr><tr><td style='text-align: center;'><strong><span style='color:#800000;'>" . $event['event_date'] . "</span></strong></td></tr><tr><td style='text-align: center; vertical-align: middle; background-color: rgb(204, 204, 204);'>";
            $body = $body . "<span style='color:#000080;'><strong>Type evenement</strong></span></td></tr><tr><td style='text-align: center;'><strong><span style='color:#800000;'>" . $event_type . "</span></strong></td></tr><tr><td style='text-align: center; vertical-align: middle; background-color: rgb(204, 204, 204);'>";
            $body = $body . "<span style='color:#000080;'><strong>Lieu evenement</strong></span></td></tr><tr><td style='text-align: center;'><strong><span style='color:#800000;'>" . $event_ort . "</span></strong></td></tr><tr><td style='text-align: center; background-color: rgb(204, 204, 204);'>";
            $body = $body . "<span style='color:#000080;'><strong>Action</strong></span></td></tr><tr><td style='text-align: center;'><strong><span style='color:#800000;'>" . $event_action . "</span></strong></td></tr></tbody></table><p>&nbsp;</p></body></html>";

            content::sendNotification($event['content_id'], $event['content_type'], strtolower($config['customers_current_status'] . '_' . $customers_status), $event['description'], $body);
        }

        return true;
    }

    function getEventAction($status)
    {
        switch (strtolower($status)) {
            case "error":
                return "Reparation immediate du Systeme";
            case "pal":
                return "Analyse des données pour identification de la cause et de la gravite";
            case "ral":
                return "Analyse des données pour identification de la cause et de la gravite";
            case "al":
                return "Analyse des données pour identification de la cause et de la gravite";
            case "ok":
                return "Aucune";

        }

        return "Aucune Action (" . $status . ")";
    }

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

    function getEventType($status)
    {
        switch (strtolower($status)) {
            case "error":
                return "System Error";
            case "pal":
                return "Monitoring Pre Alarm";
            case "ral":
                return "Monitoring Pre Alarm";
            case "al":
                return "Monitoring Alarm";
            case "ok":
                return "OK";

        }

        return "Evenement inconnu (" . $status . ")";
    }

    function sendMail($data)
    {
        global $toC_Json, $osC_Language;

        $to = array();
        $emails = explode(';', $data['to']);
        foreach ($emails as $email) {
            if (!empty($email)) {
                $to[] = osC_Mail::parseEmail($email);
            }
        }

        $cc = array();
        if (isset($data['cc']) && !empty($data['cc'])) {
            $emails = explode(';', $data['cc']);

            foreach ($emails as $email) {
                if (!empty($email)) {
                    $cc[] = osC_Mail::parseEmail($email);
                }
            }
        }

        $bcc = array();
        if (isset($data['bcc']) && !empty($data['bcc'])) {
            $emails = explode(';', $data['bcc']);

            foreach ($emails as $email) {
                if (!empty($email)) {
                    $bcc[] = osC_Mail::parseEmail($email);
                }
            }
        }

        $attachments = array();
        if (isset($data['attachments']) && !empty($data['attachments'])) {
            $attachments = explode(';', $data['attachments']);
        }

        $toC_Email_Account = new toC_Email_Account(1);

        $data = array('accounts_id' => $toC_Email_Account->getAccountId(),
            'id' => 1,
            'to' => $to,
            'cc' => $cc,
            'bcc' => $bcc,
            'from' => $toC_Email_Account->getAccountName(),
            'sender' => $toC_Email_Account->getAccountEmail(),
            'subject' => $data['subject'],
            'reply_to' => $toC_Email_Account->getAccountEmail(),
            'full_from' => $toC_Email_Account->getAccountName() . ' <' . $toC_Email_Account->getAccountEmail() . '>',
            'body' => $data['body'],
            'priority' => 1,
            'content_type' => 'html',
            'notification' => isset($data['notification']) && !empty($data['notification']) ? ($data['notification']) : "",
            'udate' => time(),
            'date' => date('m/d/Y H:i:s'),
            'fetch_timestamp' => time(),
            'messages_flag' => EMAIL_MESSAGE_DRAFT,
            'attachments' => $attachments);

        if ($toC_Email_Account->sendMail($data)) {
            $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
            $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        }

        echo $toC_Json->encode($response);
    }
}

?> 