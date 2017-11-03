<?php

    require ('includes/classes/customers.php');
    require ('includes/classes/currencies.php');

    class toC_Json_Customers
    {
        function listCustomers()
        {
            global $toC_Json, $osC_Database, $osC_Language;

            $osC_Currencies = new osC_Currencies_Admin ();

            $start = empty ($_REQUEST ['start']) ? 0 : $_REQUEST ['start'];
            $limit = empty ($_REQUEST ['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST ['limit'];

            //$query = 'select c.customers_id,c.customers_telephone, c.customers_credits, c.customers_gender, c.customers_lastname, c.customers_firstname, c.customers_email_address, c.customers_status, c.customers_ip_address, c.date_account_created, c.number_of_logons, c.date_last_logon,CONCAT(c.customers_firstname,c.customers_lastname) AS customers_name, cgd.customers_groups_name from :table_customers c left join :table_customers_groups_description cgd on (c.customers_groups_id = cgd.customers_groups_id and cgd.language_id = :language_id) where 1 = 1';

            if (!empty ($_REQUEST ['categoryId'])) {
                $Qcustomers = $osC_Database->query('SELECT c.*,cgd.customers_groups_name FROM (:table_customers_to_groups ctg INNER JOIN :table_customers_groups_description cgd ON (ctg.customers_groups_id = cgd.customers_groups_id)) INNER JOIN :table_customers1 c ON (ctg.customers_id = c.customers_id) where ctg.customers_groups_id = :categoryId');
            }
            else
            {
                $Qcustomers = $osC_Database->query('SELECT c.*,cgd.customers_groups_name FROM (:table_customers_to_groups ctg INNER JOIN :table_customers_groups_description cgd ON (ctg.customers_groups_id = cgd.customers_groups_id)) INNER JOIN :table_customers1 c ON (ctg.customers_id = c.customers_id)');
            }

            if (!empty ($_REQUEST ['categoryId'])) {
                $Qcustomers->bindInt(':categoryId', $_REQUEST ['categoryId']);
            }

            $Qcustomers->bindTable(':table_customers_to_groups', TABLE_CUSTOMERS_TO_GROUPS);
            $Qcustomers->bindTable(':table_customers1', TABLE_CUSTOMERS);
            $Qcustomers->bindTable(':table_customers_groups_description', TABLE_CUSTOMERS_GROUPS_DESCRIPTION);
            $Qcustomers->bindValue(':dblspace',' ');

            if (isset ($_REQUEST ['search']) && !empty ($_REQUEST ['search'])) {
                $Qcustomers->appendQuery('and c.customers_lastname like :customers_lastname or c.customers_firstname like :customers_firstname or c.customers_email_address like :customers_email_address');
                $Qcustomers->bindValue(':customers_lastname', '%' . $_REQUEST ['search'] . '%');
                $Qcustomers->bindValue(':customers_firstname', '%' . $_REQUEST ['search'] . '%');
                $Qcustomers->bindValue(':customers_email_address', '%' . $_REQUEST ['search'] . '%');
            }

            $Qcustomers->appendQuery('order by c.customers_firstname');
            $Qcustomers->setExtBatchLimit($start, $limit);
            $Qcustomers->execute();

            require_once ('includes/classes/geoip.php');
            $osC_GeoIP = osC_GeoIP_Admin::load();

            if ($osC_GeoIP->isInstalled()) {
                $osC_GeoIP->activate();
            }

            $records = array();
            while ($Qcustomers->next()) {
                $geoip = '';
                $iso_code_2 = $osC_GeoIP->getCountryISOCode2($Qcustomers->value('customers_ip_address'));

                if ($osC_GeoIP->isActive() && $osC_GeoIP->isValid($Qcustomers->value('customers_ip_address')) && !empty ($iso_code_2)) {
                    $geoip = osc_image('../images/worldflags/' . $iso_code_2 . '.png', $country . ', ' . $Qcustomers->value('customers_ip_address'), 18, 12) . '&nbsp;' . $Qcustomers->value('customers_ip_address');
                } else {
                    $geoip = $Qcustomers->value('customers_ip_address');
                }

                $customers_info = '<table width="100%" cellspacing="5">' . '<tbody>' . '<tr>
                <td width="150">' . $osC_Language->get('contact') . '</td>
                <td>' . ($Qcustomers->value('customers_gender') == 'm' ? $osC_Language->get('gender_male')
                        : $osC_Language->get('gender_female')) . ' ' . $Qcustomers->value('customers_firstname') . ' ' . $Qcustomers->value('customers_lastname') . '</td>
              </tr>' . '<tr>
                <td>' . $osC_Language->get('field_email_address') . '</td>
                <td>' . $Qcustomers->value('customers_email_address') . '</td>
              </tr>' . '<tr>
                <td>' . $osC_Language->get('field_customers_group') . '</td>
                <td>' . $Qcustomers->value('customers_groups_name') . '</td>
              </tr>' . '<tr>
                <td>' . $osC_Language->get('field_number_of_logons') . '</td>
                <td>' . $Qcustomers->valueInt('number_of_logons') . '</td>
              </tr>' . '<tr>
                <td>' . $osC_Language->get('field_date_last_logon') . '</td>
                <td>' . osC_DateTime::getShort($Qcustomers->value('date_last_logon')) . '</td>
              </tr>' . '</tbody>' . '</table>';

                $records [] = array('customers_id' => $Qcustomers->valueInt('customers_id'), 'customers_email_address' => $Qcustomers->value('customers_email_address'), 'customers_telephone' => $Qcustomers->value('customers_telephone'), 'customers_name' => $Qcustomers->value('customers_surname'), 'customers_lastname' => $Qcustomers->value('customers_lastname'),'customers_surname' => $Qcustomers->value('customers_surname'), 'customers_firstname' => $Qcustomers->value('customers_firstname'), 'customers_credits' => $osC_Currencies->format($Qcustomers->value('customers_credits')), 'date_account_created' => osC_DateTime::getShort($Qcustomers->value('date_account_created')), 'customers_status' => $Qcustomers->valueInt('customers_status'), 'customers_info' => $customers_info);
            }
            $Qcustomers->freeResult();

            $Qtotal = $osC_Database->query('SELECT count(ctg.customers_id) as count FROM :table_customers_to_groups ctg where ctg.customers_groups_id = :categoryId');
            $Qtotal->bindTable(':table_customers_to_groups', TABLE_CUSTOMERS_TO_GROUPS);
            $Qtotal->bindInt(':categoryId', $_REQUEST ['categoryId']);
            $Qtotal->execute();

            $total = $Qtotal->valueInt('count');

            $response = array(EXT_JSON_READER_TOTAL => $total, EXT_JSON_READER_ROOT => $records);

            echo $toC_Json->encode($response);
        }

        function getCustomers()
        {
            global $toC_Json, $osC_Database;

            $total = 0;
            $records = array();

            if(isset($_SESSION['admin']))
            {
                $id = $_SESSION['admin']['id'];
                $query = "select c.customers_id,c.customers_surname from :table_customers c where c.administrators_id = :administrators_id";

                if (!empty ($_REQUEST ['customers_id']) && $_REQUEST ['customers_id'] != '-1') {
                    $query = $query . " and c.customers_id = :customers_id";
                    $Qcustomers = $osC_Database->query($query);
                    $Qcustomers->bindInt(':customers_id', $_REQUEST ['customers_id']);
                }
                else
                {
                    $Qcustomers = $osC_Database->query($query);
                }

                $Qcustomers->bindTable(':table_customers', TABLE_CUSTOMERS);
                $Qcustomers->bindInt(':administrators_id',$id);
                $Qcustomers->execute();

                while ($Qcustomers->next()) {
                    $records [] = array('customers_id' => $Qcustomers->valueInt('customers_id'),'customers_surname' => $Qcustomers->value('customers_surname'));
                    $total++;
                }
                $Qcustomers->freeResult();
            }
            else
            {
                $records [] = array('customers_id' => -1,'customers_surname' => "Session expirée");
                $total++;
            }

            $response = array(EXT_JSON_READER_TOTAL => $total, EXT_JSON_READER_ROOT => $records);

            echo $toC_Json->encode($response);
        }

        function listLastCustomers()
        {
            global $toC_Json, $osC_Database, $osC_Language;

            $query = 'select c.customers_id, c.customers_credits, c.customers_gender, c.customers_lastname, c.customers_firstname, c.customers_email_address, c.customers_status, c.customers_ip_address, c.date_account_created, c.number_of_logons, c.date_last_logon, cgd.customers_groups_name from :table_customers c left join :table_customers_groups_description cgd on (c.customers_groups_id = cgd.customers_groups_id and cgd.language_id = :language_id) order by c.customers_id desc limit 1';

            $Qcustomers = $osC_Database->query($query);
            $Qcustomers->bindTable(':table_customers', TABLE_CUSTOMERS);
            $Qcustomers->bindTable(':table_customers_groups_description', TABLE_CUSTOMERS_GROUPS_DESCRIPTION);
            $Qcustomers->bindInt(':language_id', $osC_Language->getID());
            $Qcustomers->execute();

            $records = array();
            while ($Qcustomers->next()) {
                $records [] = array('customers_id' => $Qcustomers->valueInt('customers_id'), 'customers_lastname' => $Qcustomers->value('customers_lastname'), 'customers_firstname' => $Qcustomers->value('customers_firstname'));
            }
            $Qcustomers->freeResult();

            $response = array(EXT_JSON_READER_TOTAL => $Qcustomers->getBatchSize(), EXT_JSON_READER_ROOT => $records);

            echo $toC_Json->encode($response);
        }

        function listStoreCredits()
        {
            global $toC_Json, $osC_Database, $osC_Language;

            $osC_Currencies = new osC_Currencies_Admin ();

            $Qcredits = $osC_Database->query('select customers_credits_history_id, date_added, action_type, amount, comments from :table_customers_credit where customers_id = :customers_id');
            $Qcredits->bindTable(':table_customers_credit', TABLE_CUSTOMERS_CREDITS_HISTORY);
            $Qcredits->bindInt(':customers_id', $_REQUEST ['customers_id']);
            $Qcredits->execute();

            $records = array();
            while ($Qcredits->next()) {
                if ($Qcredits->valueInt('action_type') == STORE_CREDIT_ACTION_TYPE_ORDER_PURCHASE) {
                    $actionType = $osC_Language->get('store_credits_action_purchase');
                } else if ($Qcredits->valueInt('action_type') == STORE_CREDIT_ACTION_TYPE_ORDER_REFUNDED) {
                    $actionType = $osC_Language->get('store_credits_action_refund');
                } else if ($Qcredits->valueInt('action_type') == STORE_CREDIT_ACTION_TYPE_ADMIN) {
                    $actionType = $osC_Language->get('store_credits_action_admin');
                }

                $records [] = array('customers_credits_history_id' => $Qcredits->valueInt('customers_credits_history_id'), 'date_added' => osC_DateTime::getShort($Qcredits->value('date_added')), 'action_type' => $actionType, 'amount' => $osC_Currencies->format($Qcredits->value('amount')), 'comments' => $Qcredits->value('comments'));
            }

            $response = array(EXT_JSON_READER_TOTAL => $Qcredits->getBatchSize(), EXT_JSON_READER_ROOT => $records);

            echo $toC_Json->encode($response);
        }

        function saveBlance()
        {
            global $toC_Json, $osC_Language, $osC_Database;

            $osC_Currencies = new osC_Currencies_Admin ();

            $data = array('amount' => $_REQUEST ['amount'], 'comments' => $_REQUEST ['comments'], 'customers_id' => $_REQUEST ['customers_id'], 'notify' => (isset ($_REQUEST ['notify']) && ($_REQUEST ['notify'] == 'on')
                    ? '1' : '0'));

            if (osC_Customers_Admin::saveBlance($data)) {
                $data = osC_Customers_Admin::getData($_REQUEST ['customers_id']);

                $response = array('success' => true, 'customers_credits' => $osC_Currencies->format($data ['customers_credits']), 'feedback' => $osC_Language->get('ms_success_action_performed'));
            } else {
                $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
            }

            echo $toC_Json->encode($response);
        }

        function saveCustomer()
        {
            global $toC_Json, $osC_Language, $osC_Database;

            if(isset($_SESSION['admin']['username']))
            {
                $data = array('gender' => (isset($_REQUEST['customers_gender']) ? $_REQUEST['customers_gender'] : ''),
                    'firstname' => $_REQUEST['customers_firstname'],
                    'lastname' => $_REQUEST['customers_lastname'],
                    'telephone' => $_REQUEST['customers_telephone'],
                    'email_address' => $_REQUEST['customers_email_address'],
                    'password' => $_REQUEST['customers_password'],
                    'newsletter' => '1',
                    'status' => (isset($_REQUEST['customers_status']) && ($_REQUEST['customers_status'] == 'on')
                        ? '1' : '0'),
                    'customers_groups_id' => $_REQUEST['customers_groups_id'],
                    'customers_surname' => $_REQUEST['customers_surname'],
                    'company' => $_REQUEST['customers_surname'],
                    'street_address' => $_REQUEST['street_address'],
                    'suburb' => (isset($_REQUEST['suburb']) ? $_REQUEST['suburb'] : ''),
                    'postcode' => (isset($_REQUEST['postcode']) ? $_REQUEST['postcode'] : ''),
                    'city' => $_REQUEST['city'],
                    'state' => $_REQUEST['z_code'],
                    'zone_id' => $_REQUEST['zone_id'], //set blow
                    'country_id' => $_REQUEST['country_id'],
                    'roles_id' => $_REQUEST['roles_id'],
                    'administrators_id' => $_REQUEST['administrators_id'],
                    'fax' => (isset($_REQUEST['fax_number']) ? $_REQUEST['fax_number'] : ''),
                    'primary' => true);

                $error = false;
                $feedback = array();
                $feedback [0] = 'erreur generique';

                if (($data ['gender'] != 'm') && ($data ['gender'] != 'f')) {
                    $error = true;
                    $feedback [0] = $osC_Language->get('ms_error_gender');
                }

                if (strlen(trim($data ['firstname'])) < 2) {
                    $error = true;
                    $feedback [0] = sprintf($osC_Language->get('ms_error_first_name'), 2);
                }

                if (strlen(trim($data ['lastname'])) < 2) {
                    $error = true;
                    $feedback [0] = sprintf($osC_Language->get('ms_error_last_name'), 2);
                }

                if (strlen(trim($data ['email_address'])) < 5) {
                    $error = true;
                    $feedback [0] = sprintf($osC_Language->get('ms_error_email_address'), 5);
                } elseif (!osc_validate_email_address($data ['email_address'])) {
                    $error = true;
                    $feedback [0] = $osC_Language->get('ms_error_email_address_invalid');
                } else {
                    $Qcheck = $osC_Database->query('select customers_id from :table_customers where customers_email_address = :customers_email_address');

                    if (isset ($_REQUEST ['customers_id']) && is_numeric($_REQUEST ['customers_id'])) {
                        $Qcheck->appendQuery('and customers_id != :customers_id');
                        $Qcheck->bindInt(':customers_id', $_REQUEST ['customers_id']);
                    }

                    $Qcheck->appendQuery('limit 1');
                    $Qcheck->bindTable(':table_customers', TABLE_CUSTOMERS);
                    $Qcheck->bindValue(':customers_email_address', $data ['email_address']);
                    $Qcheck->execute();

                    if ($Qcheck->numberOfRows() > 0) {
                        $error = true;
                        $feedback [0] = $Qcheck->value('customers_id');
                    }

                    $Qcheck->freeResult();
                }

                if (strlen(trim($data['company'])) < 2) {
                    $error = true;
                    $feedback[] = sprintf($osC_Language->get('ms_error_company'), 2);
                }

                if (strlen(trim($data['city'])) < 0) {
                    $error = true;
                    $feedback[] = sprintf($osC_Language->get('ms_error_city'), 0);
                }

                if (-1 > 0) {
                    $Qcheck = $osC_Database->query('select zone_id from :table_zones where zone_country_id = :zone_country_id limit 1');
                    $Qcheck->bindTable(':table_zones', TABLE_ZONES);
                    $Qcheck->bindInt(':zone_country_id', $data['country_id']);
                    $Qcheck->execute();

                    $entry_state_has_zones = ($Qcheck->numberOfRows() > 0);

                    $Qcheck->freeResult();

                    if ($entry_state_has_zones === true) {
                        $Qzone = $osC_Database->query('select zone_id from :table_zones where zone_country_id = :zone_country_id and zone_code = :zone_code');
                        $Qzone->bindTable(':table_zones', TABLE_ZONES);
                        $Qzone->bindInt(':zone_country_id', $data['country_id']);
                        $Qzone->bindValue(':zone_code', strtoupper($data['state']));
                        $Qzone->execute();

                        if ($Qzone->numberOfRows() === 1) {
                            $data['zone_id'] = $Qzone->valueInt('zone_id');
                        } else {
                            $Qzone = $osC_Database->query('select zone_id from :table_zones where zone_country_id = :zone_country_id and zone_name like :zone_name');
                            $Qzone->bindTable(':table_zones', TABLE_ZONES);
                            $Qzone->bindInt(':zone_country_id', $data['country_id']);
                            $Qzone->bindValue(':zone_name', $data['state'] . '%');
                            $Qzone->execute();

                            if ($Qzone->numberOfRows() === 1) {
                                $data['zone_id'] = $Qzone->valueInt('zone_id');
                            } else {
                                $error = true;
                                $feedback[] = $osC_Language->get('ms_warning_state_select_from_list');
                            }
                        }

                        $Qzone->freeResult();
                    } else {
                        if (strlen(trim($data['state'])) < 0) {
                            $error = true;
                            $feedback[] = sprintf($osC_Language->get('ms_error_state'), 0);
                        }
                    }
                }

                if (!is_numeric($data['country_id']) || ($data['country_id'] < 1)) {
                    $error = true;
                    $feedback[] = $osC_Language->get('ms_error_country');
                }

                if (strlen(trim($data['telephone'])) < 0) {
                    $error = true;
                    $feedback[] = sprintf($osC_Language->get('ms_error_telephone_number'), 0);
                }

                if (strlen(trim($data['fax'])) < 0) {
                    $error = true;
                    $feedback[] = sprintf($osC_Language->get('ms_error_fax_number'), 0);
                }

                if ($error === false) {

                    $answer = osC_Customers_Admin::save((isset ($_REQUEST ['customers_id']) && is_numeric($_REQUEST ['customers_id'])
                        ? $_REQUEST ['customers_id'] : null), $data);
                    if ($answer == 1) {
                        $response = array('success' => true, 'feedback' => 'OK');
                    } else {
                        $response = array('success' => false, 'feedback' => $answer);
                    }
                } else {
                    //var_dump($feedback);
                    if (is_int($feedback [0])) {
                        $response = array('success' => true, 'feedback' => $feedback [0]);
                    } else {
                        $response = array('success' => false, 'feedback' => $feedback [0]);
                    }
                }
            }
            else {
                $response = array('success' => false, 'feedback' => "Votre session est expiree, vous devez vous reconnecter pour effectuer cette operation");
            }

            echo $toC_Json->encode($response);
        }

        function listAddressBooks()
        {
            global $toC_Json, $osC_Language;

            $osC_ObjectInfo = new osC_ObjectInfo (osC_Customers_Admin::getData($_REQUEST ['customers_id']));
            $Qaddresses = osC_Customers_Admin::getAddressBookData($_REQUEST ['customers_id']);

            $records = array();
            while ($Qaddresses->next()) {
                $address = osC_Address::format($Qaddresses->toArray(), '<br/>');

                if ($osC_ObjectInfo->get('customers_default_address_id') == $Qaddresses->valueInt('address_book_id')) {
                    $address .= '&nbsp;<i>(' . $osC_Language->get('primary_address') . ')</i>';
                }

                $records [] = array('address_book_id' => $Qaddresses->value('address_book_id'), 'address_html' => $address);
            }

            $Qaddresses->freeResult();

            $response = array(EXT_JSON_READER_ROOT => $records);

            echo $toC_Json->encode($response);
        }

        function loadGroupTree()
        {
            global $osC_Database, $toC_Json;

            $Qcategories = $osC_Database->query('SELECT cg.customers_groups_id AS categoryId, COUNT(ctg.customers_id) AS count, cgd.customers_groups_name AS categories_name FROM (:table_customers_groups cg left JOIN :table_customers_to_groups ctg ON (cg.customers_groups_id = ctg.customers_groups_id)) left JOIN :table_customers_groups_description cgd ON (cg.customers_groups_id = cgd.customers_groups_id) GROUP BY cg.customers_groups_id, cgd.customers_groups_name ORDER BY cgd.customers_groups_name ASC');
            $Qcategories->bindTable(':table_customers_groups', TABLE_CUSTOMERS_GROUPS);
            $Qcategories->bindTable(':table_customers_groups_description', TABLE_CUSTOMERS_GROUPS_DESCRIPTION);
            $Qcategories->bindTable(':table_customers_to_groups', TABLE_CUSTOMERS_TO_GROUPS);
            $Qcategories->execute();

            $records = array();

            while ($Qcategories->next()) {
                $records [] = array('id' => $Qcategories->value('categoryId'), 'text' => $Qcategories->value('categories_name') . ' ( ' . $Qcategories->value('count') . ' )', 'icon' => 'templates/default/images/icons/16x16/whos_online.png', 'cls' => 'x-tree-node-collapsed', 'leaf' => true);
            }

            $Qcategories->freeResult();

            echo $toC_Json->encode($records);
        }

        function loadCustomersTree()
        {
            global $osC_Database, $toC_Json;

            $query = "SELECT c.customers_id,c.customers_surname,(select count(eventid) from :table_imports where lower(plant) in (select lower(d.categories_name) from :table_layout_description d where d.categories_id in (select l.categories_id from delta_layout l where l.parent_id = c.customers_id))) count FROM :table_customers c;";

            $Qcategories = $osC_Database->query($query);
            $Qcategories->bindTable(':table_imports', TABLE_PARAMETERS);
            $Qcategories->bindTable(':table_layout_description', TABLE_LAYOUT_DESCRIPTION);
            $Qcategories->bindTable(':table_customers', TABLE_CUSTOMERS);
            $Qcategories->execute();

            $records = array();

            while ($Qcategories->next()) {
                $records [] = array('id' => $Qcategories->value('customers_id'), 'text' => $Qcategories->value('customers_surname') . ' ( ' . $Qcategories->value('count') . ' )', 'icon' => 'templates/default/images/icons/16x16/company.png', 'cls' => 'x-tree-node-collapsed', 'leaf' => true);
            }

            $Qcategories->freeResult();

            echo $toC_Json->encode($records);
        }

        function getCustomersGroups()
        {
            global $toC_Json, $osC_Database;

            $Qgroups = $osC_Database->query('select cg.customers_groups_id, cg.is_default, cgd.customers_groups_name from :table_customers_groups cg, :table_customers_groups_description cgd where cg.customers_groups_id = cgd.customers_groups_id order by cgd.customers_groups_name ASC');
            $Qgroups->bindTable(':table_customers_groups', TABLE_CUSTOMERS_GROUPS);
            $Qgroups->bindTable(':table_customers_groups_description', TABLE_CUSTOMERS_GROUPS_DESCRIPTION);
            $Qgroups->execute();

            $records = array();
            while ($Qgroups->next()) {
                $records [] = array('id' => $Qgroups->valueInt('customers_groups_id'), 'text' => $Qgroups->value('customers_groups_name'));
            }

            $response = array(EXT_JSON_READER_ROOT => $records);

            echo $toC_Json->encode($response);
        }

        function getCountries()
        {
            global $toC_Json;

            $records = array();
            foreach (osC_Address::getCountries() as $country) {
                $records [] = array('country_id' => $country ['id'], 'country_title' => $country ['name']);
            }

            echo $toC_Json->encode(array(EXT_JSON_READER_ROOT => $records));
        }

        function getZones()
        {
            global $toC_Json;

            $country_id = isset ($_REQUEST ['country_id']) ? $_REQUEST ['country_id'] : null;

            $records = array();
            foreach (osC_Address::getZones($country_id) as $zone) {
                $records [] = array('zone_id' => $zone ['id'],'zone_code' => $zone ['code'], 'zone_name' => $zone ['name']);
            }

            echo $toC_Json->encode(array(EXT_JSON_READER_ROOT => $records));
        }

        function loadCustomer()
        {
            global $toC_Json;

            $data = osC_Customers_Admin::getData($_REQUEST ['customers_id']);

            $response = array('success' => true, 'data' => $data);

            echo $toC_Json->encode($response);
        }

        function loadAddressBook()
        {
            global $toC_Json;

            $osC_ObjectInfo = new osC_ObjectInfo (osC_Customers_Admin::getData($_REQUEST ['customers_id']));
            $data = osC_Customers_Admin::getAddressBookData($_REQUEST ['customers_id'], $_REQUEST ['address_book_id']);

            if ($osC_ObjectInfo->get('customers_default_address_id') == $_REQUEST ['address_book_id']) {
                $data ['primary'] = true;
            } else {
                $data ['primary'] = false;
            }

            $response = array('success' => true, 'data' => $data);

            echo $toC_Json->encode($response);
        }

        function saveAddressBook()
        {
            global $toC_Json, $osC_Language, $osC_Database;

            $data = array('customer_id' => $_REQUEST ['customers_id'], 'gender' => (isset ($_REQUEST ['gender'])
                    ? $_REQUEST ['gender']
                    : ''), 'firstname' => $_REQUEST ['firstname'], 'lastname' => $_REQUEST ['lastname'], 'company' => (isset ($_REQUEST ['company'])
                    ? $_REQUEST ['company']
                    : ''), 'street_address' => $_REQUEST ['street_address'], 'suburb' => (isset ($_REQUEST ['suburb'])
                    ? $_REQUEST ['suburb'] : ''), 'postcode' => (isset ($_REQUEST ['postcode']) ? $_REQUEST ['postcode']
                    : ''), 'city' => $_REQUEST ['city'], 'state' => (isset ($_REQUEST ['z_code']) ? $_REQUEST ['z_code']
                    : ''), 'zone_id' => '0', //set blow
                          'country_id' => $_REQUEST ['country_id'], 'telephone' => (isset ($_REQUEST ['telephone_number'])
                        ? $_REQUEST ['telephone_number'] : ''), 'fax' => (isset ($_REQUEST ['fax_number'])
                        ? $_REQUEST ['fax_number']
                        : ''), 'primary' => (isset ($_REQUEST ['primary']) && ($_REQUEST ['primary'] == 'on') ? true
                        : false));

            $error = false;
            $feedback = array();

            if (ACCOUNT_GENDER > 0) {
                if (($data ['gender'] != 'm') && ($data ['gender'] != 'f')) {
                    $error = true;
                    $feedback [] = $osC_Language->get('ms_error_gender');
                }
            }

            if (strlen(trim($data ['firstname'])) < ACCOUNT_FIRST_NAME) {
                $error = true;
                $feedback [] = sprintf($osC_Language->get('ms_error_first_name'), ACCOUNT_FIRST_NAME);
            }

            if (strlen(trim($data ['lastname'])) < ACCOUNT_LAST_NAME) {
                $error = true;
                $feedback [] = sprintf($osC_Language->get('ms_error_last_name'), ACCOUNT_LAST_NAME);
            }

            if (ACCOUNT_COMPANY > 0) {
                if (strlen(trim($data ['company'])) < ACCOUNT_COMPANY) {
                    $error = true;
                    $feedback [] = sprintf($osC_Language->get('ms_error_company'), ACCOUNT_COMPANY);
                }
            }

            if (strlen(trim($data ['street_address'])) < ACCOUNT_STREET_ADDRESS) {
                $error = true;
                $feedback [] = sprintf($osC_Language->get('ms_error_street_address'), ACCOUNT_STREET_ADDRESS);
            }

            if (ACCOUNT_SUBURB > 0) {
                if (strlen(trim($data ['suburb'])) < ACCOUNT_SUBURB) {
                    $error = true;
                    $feedback [] = sprintf($osC_Language->get('ms_error_suburb'), ACCOUNT_SUBURB);
                }
            }

            if (ACCOUNT_POST_CODE > 0) {
                if (strlen(trim($data ['postcode'])) < ACCOUNT_POST_CODE) {
                    $error = true;
                    $feedback [] = sprintf($osC_Language->get('entry_post_code'), ACCOUNT_POST_CODE);
                }
            }

            if (strlen(trim($data ['city'])) < ACCOUNT_CITY) {
                $error = true;
                $feedback [] = sprintf($osC_Language->get('ms_error_city'), ACCOUNT_CITY);
            }

            if (ACCOUNT_STATE > 0) {
                $Qcheck = $osC_Database->query('select zone_id from :table_zones where zone_country_id = :zone_country_id limit 1');
                $Qcheck->bindTable(':table_zones', TABLE_ZONES);
                $Qcheck->bindInt(':zone_country_id', $data ['country_id']);
                $Qcheck->execute();

                $entry_state_has_zones = ($Qcheck->numberOfRows() > 0);

                $Qcheck->freeResult();

                if ($entry_state_has_zones === true) {
                    $Qzone = $osC_Database->query('select zone_id from :table_zones where zone_country_id = :zone_country_id and zone_code = :zone_code');
                    $Qzone->bindTable(':table_zones', TABLE_ZONES);
                    $Qzone->bindInt(':zone_country_id', $data ['country_id']);
                    $Qzone->bindValue(':zone_code', strtoupper($data ['state']));
                    $Qzone->execute();

                    if ($Qzone->numberOfRows() === 1) {
                        $data ['zone_id'] = $Qzone->valueInt('zone_id');
                    } else {
                        $Qzone = $osC_Database->query('select zone_id from :table_zones where zone_country_id = :zone_country_id and zone_name like :zone_name');
                        $Qzone->bindTable(':table_zones', TABLE_ZONES);
                        $Qzone->bindInt(':zone_country_id', $data ['country_id']);
                        $Qzone->bindValue(':zone_name', $data ['state'] . '%');
                        $Qzone->execute();

                        if ($Qzone->numberOfRows() === 1) {
                            $data ['zone_id'] = $Qzone->valueInt('zone_id');
                        } else {
                            $error = true;
                            $feedback [] = $osC_Language->get('ms_warning_state_select_from_list');
                        }
                    }

                    $Qzone->freeResult();
                } else {
                    if (strlen(trim($data ['state'])) < ACCOUNT_STATE) {
                        $error = true;
                        $feedback [] = sprintf($osC_Language->get('ms_error_state'), ACCOUNT_STATE);
                    }
                }
            }

            if (!is_numeric($data ['country_id']) || ($data ['country_id'] < 1)) {
                $error = true;
                $feedback [] = $osC_Language->get('ms_error_country');
            }

            if (ACCOUNT_TELEPHONE > 0) {
                if (strlen(trim($data ['telephone'])) < ACCOUNT_TELEPHONE) {
                    $error = true;
                    $feedback [] = sprintf($osC_Language->get('ms_error_telephone_number'), ACCOUNT_TELEPHONE);
                }
            }

            if (ACCOUNT_FAX > 0) {
                if (strlen(trim($data ['fax'])) < ACCOUNT_FAX) {
                    $error = true;
                    $feedback [] = sprintf($osC_Language->get('ms_error_fax_number'), ACCOUNT_FAX);
                }
            }

            if ($error === false) {
                if (osC_Customers_Admin::saveAddress((isset ($_REQUEST ['address_book_id']) && is_numeric($_REQUEST ['address_book_id']))
                                                             ? $_REQUEST ['address_book_id'] : null, $data)
                ) {
                    $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
                } else {
                    $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
                }
            } else {
                $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed') . '<br />' . implode('<br />', $feedback));
            }

            echo $toC_Json->encode($response);
        }

        function deleteCustomer()
        {
            global $toC_Json, $osC_Language;

            if(isset($_SESSION['admin']['username']))
            {
                if (osC_Customers_Admin::delete($_REQUEST ['customers_id'])) {

                    $event = array();
                    $event['content_id'] = $_REQUEST ['customers_id'];
                    $event['content_type'] = "customer";
                    $event['type'] = "succes";
                    $event['event_date'] = date('Y-m-d H:i:s');
                    $event['source'] = "cloud";
                    $event['user'] = $_SESSION['admin']['username'];
                    $event['category'] = "customer";
                    $event['description'] = "Le compte du client " . $_REQUEST ['customers_surname'] . " a ete supprime par : " . $_SESSION['admin']['username'];

                    osC_Categories_Admin::logEvent($event);

                    $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
                } else {

                    $event = array();
                    $event['content_id'] = $_REQUEST ['customers_id'];
                    $event['content_type'] = "customer";
                    $event['type'] = "error";
                    $event['event_date'] = date('Y-m-d H:i:s');
                    $event['source'] = "cloud";
                    $event['user'] = $_SESSION['admin']['username'];
                    $event['category'] = "customer";
                    $event['description'] = "Le compte du client " . $_REQUEST ['customers_surname'] . " n'a pas pu etre supprime par : " . $_SESSION['admin']['username'];

                    osC_Categories_Admin::logEvent($event);

                    $response = array('success' => false, 'feedback' => "Le compte du client " . $_REQUEST ['customers_surname'] . " n'a pas pu etre supprime");
                }
            }
            else
            {
                $response = array('success' => false, 'feedback' => "Votre session est expiree, vous devez vous reconnecter pour effectuer cette operation");
            }

            echo $toC_Json->encode($response);
        }

        function deleteAddressBook()
        {
            global $toC_Json, $osC_Language;

            $address_book_id = isset ($_REQUEST ['address_book_id']) ? $_REQUEST ['address_book_id'] : null;

            $osC_ObjectInfo_Customer = new osC_ObjectInfo (osC_Customers_Admin::getData($_REQUEST ['customers_id']));

            $error = false;
            $feedback = array();

            if ($osC_ObjectInfo_Customer->get('customers_default_address_id') == $address_book_id) {
                $error = true;
                $feedback [] = $osC_Language->get('delete_warning_primary_address_book_entry');
            }

            if ($error === false) {
                if (osC_Customers_Admin::deleteAddress($address_book_id)) {
                    $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
                } else {
                    $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
                }
            } else {
                $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed') . '<br />' . implode('<br />', $feedback));
            }

            echo $toC_Json->encode($response);
        }

        function deleteAddressBooks()
        {
            global $toC_Json, $osC_Language;

            $osC_ObjectInfo_Customer = new osC_ObjectInfo (osC_Customers_Admin::getData($_REQUEST ['customers_id']));

            $error = false;
            $feedback = array();

            $batch = explode(',', $_REQUEST ['batch']);
            foreach ($batch as $id) {
                if ($osC_ObjectInfo_Customer->get('customers_default_address_id') == $id) {
                    $error = true;
                    $feedback [] = $osC_Language->get('delete_warning_primary_address_book_entry');
                }
            }

            if ($error === false) {
                foreach ($batch as $id) {
                    if (!osC_Customers_Admin::deleteAddress($id)) {
                        $error = true;
                        break;
                    }
                }

                if ($error === false) {
                    $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
                } else {
                    $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
                }
            } else {
                $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed') . '<br />' . implode('<br />', $feedback));
            }

            echo $toC_Json->encode($response);
        }

        function listWishlists()
        {
            global $toC_Json, $osC_Language, $osC_Database;

            $customers_id = isset ($_REQUEST ['customers_id']) ? $_REQUEST ['customers_id'] : null;

            if (is_numeric($customers_id)) {
                $Qwishlists = $osC_Database->query('select wp.wishlists_products_id, wp.products_id, wp.date_added, wp.comments from :table_wishlists w, :table_wishlists_products wp where w.wishlists_id = wp.wishlists_id and w.customers_id = :customers_id');
                $Qwishlists->bindTable(':table_wishlists', TABLE_WISHLISTS);
                $Qwishlists->bindTable(':table_wishlists_products', TABLE_WISHLISTS_PRODUCTS);
                $Qwishlists->bindInt(':customers_id', $customers_id);
                $Qwishlists->execute();

                $records = array();
                while ($Qwishlists->next()) {
                    $products_id = osc_get_product_id($Qwishlists->value('products_id'));
                    $variants = osc_parse_variants_from_id_string($Qwishlists->value('products_id'));

                    $Qname = $osC_Database->query('select products_name from :table_products_description where products_id = :products_id and language_id = :language_id');
                    $Qname->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
                    $Qname->bindInt(':products_id', $products_id);
                    $Qname->bindInt(':language_id', $osC_Language->getID());
                    $Qname->execute();

                    $products_name = $Qname->value('products_name');

                    if (!empty ($variants)) {
                        $variants_name = array();
                        foreach ($variants as $groups_id => $values_id) {
                            $Qvariants = $osC_Database->query('select pvg.products_variants_groups_name, pvv.products_variants_values_name from :table_products_variants pv, :table_products_variants_entries pve, :table_products_variants_groups pvg, :table_products_variants_values pvv where pv.products_id = :products_id and pv.products_variants_id = pve.products_variants_id and pve.products_variants_groups_id = :groups_id and pve.products_variants_values_id = :variants_values_id and pve.products_variants_groups_id = pvg.products_variants_groups_id and pve.products_variants_values_id = pvv.products_variants_values_id and pvg.language_id = :pvg_language_id and pvv.language_id = :pvv_language_id');
                            $Qvariants->bindTable(':table_products_variants', TABLE_PRODUCTS_VARIANTS);
                            $Qvariants->bindTable(':table_products_variants_entries', TABLE_PRODUCTS_VARIANTS_ENTRIES);
                            $Qvariants->bindTable(':table_products_variants_groups', TABLE_PRODUCTS_VARIANTS_GROUPS);
                            $Qvariants->bindTable(':table_products_variants_values', TABLE_PRODUCTS_VARIANTS_VALUES);
                            $Qvariants->bindInt(':products_id', $products_id);
                            $Qvariants->bindInt(':groups_id', $groups_id);
                            $Qvariants->bindInt(':variants_values_id', $values_id);
                            $Qvariants->bindInt(':pvg_language_id', $osC_Language->getID());
                            $Qvariants->bindInt(':pvv_language_id', $osC_Language->getID());
                            $Qvariants->execute();

                            $variants_name [] = $Qvariants->value('products_variants_groups_name') . ' : ' . $Qvariants->value('products_variants_values_name');
                        }
                        $products_name .= '<br />' . implode('<br />', $variants_name);
                    }

                    $records [] = array('wishlists_products_id' => $Qwishlists->value('wishlists_products_id'), 'products_name' => $products_name, 'date_added' => osC_DateTime::getShort($Qwishlists->value('date_added')), 'comments' => $Qwishlists->value('comments'));
                }
                $Qwishlists->freeResult();

                $response = array(EXT_JSON_READER_ROOT => $records);

                echo $toC_Json->encode($response);
            }
        }

        function setStatus()
        {
            global $toC_Json, $osC_Language;

            $flag = $_REQUEST ['flag'];
            $customers_id = $_REQUEST ['customers_id'];

            if (osC_Customers_Admin::setStatus($customers_id, $flag)) {
                $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
            } else {
                $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
            }

            echo $toC_Json->encode($response);
        }

        function saveCustomerToGroup()
        {
            global $toC_Json, $osC_Language;

            $groups_id = $_REQUEST ['customers_groups_id'];
            $customers_id = $_REQUEST ['customers_id'];

            if (osC_Customers_Admin::addCustomerToGroup($customers_id, $groups_id)) {
                $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
            } else {
                $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
            }

            echo $toC_Json->encode($response);
        }
    }

?>
