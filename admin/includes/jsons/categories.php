<?php

require('includes/classes/categories.php');
require('includes/classes/category_tree.php');
require('includes/classes/layout_tree.php');
require('includes/classes/image.php');
require('includes/classes/products.php');

class toC_Json_Categories
{

    function listCategoriesAll()
    {
        global $toC_Json, $osC_Language, $osC_Database;

        $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start'];
        $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit'];

        $Qcategories = $osC_Database->query('select c.categories_id, cd.categories_name, c.categories_image, c.parent_id, c.sort_order, c.categories_status, c.date_added, c.last_modified from :table_categories c, :table_categories_description cd where c.categories_id = cd.categories_id and cd.language_id = :language_id');
        //$Qcategories->appendQuery('and c.parent_id = :parent_id');

        if (isset($_REQUEST['date_added']) && !empty($_REQUEST['date_added'])) {
            $Qcategories->appendQuery('and c.date_added > :date_added');
            $Qcategories->bindValue(':date_added', $_REQUEST['date_added']);
        }

        if (isset($_REQUEST['search']) && !empty($_REQUEST['search'])) {
            $Qcategories->appendQuery('and cd.categories_name like :categories_name');
            $Qcategories->bindValue(':categories_name', $_REQUEST['search']);
        }

        $Qcategories->appendQuery('order by c.sort_order, cd.categories_name');
        $Qcategories->bindTable(':table_categories', TABLE_CATEGORIES);
        $Qcategories->bindTable(':table_categories_description', TABLE_CATEGORIES_DESCRIPTION);
        $Qcategories->bindInt(':language_id', $osC_Language->getID());
        $Qcategories->setExtBatchLimit($start, $limit);
        $Qcategories->execute();

        $records = array();
        $osC_CategoryTree = new osC_CategoryTree();
        while ($Qcategories->next()) {
            $records[] = array('categories_id' => $Qcategories->value('categories_id'),
                'categories_name' => $Qcategories->value('categories_name'),
                'status' => $Qcategories->valueInt('categories_status'),
                'path' => $osC_CategoryTree->buildBreadcrumb($Qcategories->valueInt('categories_id')));
        }

        $user_categories_array = array();

        $roles = $_SESSION[admin][roles];
        $count = 0;

        foreach ($records as $category) {
            $permissions = osC_Categories_Admin::getCategoriesPermissions($category['categories_id']);
            $can_read_permissions = $permissions['can_read'];
            $can_write_permissions = $permissions['can_write'];
            $can_modify_permissions = $permissions['can_modify'];
            $can_publish_permissions = $permissions['can_publish'];

            $can_see = false;

            foreach ($roles as $role) {
                if (in_array($role, $can_read_permissions) || in_array($role, $can_write_permissions) || in_array($role, $can_modify_permissions) || in_array($role, $can_publish_permissions)) {
                    $can_see = true;
                }
            }

            if ($can_see) {
                $user_categories_array[] = $category;
                $count++;
            }
        }

        $response = array(EXT_JSON_READER_TOTAL => $count,
            EXT_JSON_READER_ROOT => $user_categories_array);

        //echo "<pre>", print_r($_REQUEST, true), "</pre>";
        echo $toC_Json->encode($response);
    }

    function listParameters()
    {
        global $toC_Json, $osC_Database;

        $start = empty ($_REQUEST ['start']) ? 0 : $_REQUEST ['start'];
        $limit = empty ($_REQUEST ['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST ['limit'];

        $query = 'select p.* from :table_parameters p where 1 = 1 ';

        switch ($_REQUEST ['content_type']) {
            case "plant":
                $query = $query . " and plants_id = " . $_REQUEST ['content_id'];
                break;

            case "line":
                $query = $query . " and lines_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(line) = '" . $_REQUEST ['content_name'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "' ";
                break;

            case "asset":
                $query = $query . " and asset_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(asset) = '" . $_REQUEST ['content_name'] . "' and lower(line) = '" . $_REQUEST ['line'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "'";
                break;

            case "component":
                $query = $query . " and component_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(asset) = '" . $_REQUEST ['asset'] . "' and lower(line) = '" . $_REQUEST ['line'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "' and lower(channel) in (select lower(channel) from delta_sensor where component_id = " . $_REQUEST ['component'] . ")";
                break;

            case "sensor":
                $query = $query . " and sensors_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(asset) = '" . $_REQUEST ['asset'] . "' and lower(line) = '" . $_REQUEST ['line'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "' and lower(channel) = (select lower(channel) from delta_sensor where component_id = " . $_REQUEST ['component'] . " and lower(code) = lower('" . $_REQUEST ['content_name'] . "'))";
                break;
        }

        if(isset($_REQUEST ['start_date']) && !empty($_REQUEST ['start_date']))
        {
            $query = $query . " and p.eventdate >= '" . $_REQUEST ['start_date'] . "'";
        }

        if(isset($_REQUEST ['end_date']) && !empty($_REQUEST ['end_date']))
        {
            $query = $query . " and p.eventdate <= '" . $_REQUEST ['end_date'] . "'";
        }

        if(isset($_REQUEST ['measurement_state']) && $_REQUEST ['measurement_state'] != '-1')
        {
            $query = $query . " and p.measurement_state = '" . $_REQUEST ['measurement_state'] . "'";
        }

        if(isset($_REQUEST ['event_trigger_type']) && $_REQUEST ['event_trigger_type'] != '-1')
        {
            $query = $query . " and p.event_trigger_type = '" . $_REQUEST ['event_trigger_type'] . "'";
        }

        if(isset($_REQUEST ['monitoring_status']) && $_REQUEST ['monitoring_status'] != '-1')
        {
            $query = $query . " and p.monitoring_status = '" . $_REQUEST ['monitoring_status'] . "'";
        }

        if(isset($_REQUEST ['operating_class']) && $_REQUEST ['operating_class'] != '-1')
        {
            $query = $query . " and p.operating_class = '" . $_REQUEST ['operating_class'] . "'";
        }

        if(isset($_REQUEST ['xmlfile']) && $_REQUEST ['xmlfile'] != '-1')
        {
            $query = $query . " and p.xmlfile = '" . $_REQUEST ['xmlfile'] . "'";
        }

        $query = $query . " order by p.eventdate desc";

        $QParameters = $osC_Database->query($query);

        $QParameters->bindTable(':table_parameters', TABLE_PARAMETERS);
        $QParameters->setExtBatchLimit($start, $limit);
        $QParameters->execute();

        $records = array();
        while ($QParameters->next()) {
            $records [] = array('eventid' => $QParameters->valueInt('eventid'), 'eventdate' => $QParameters->value('eventdate'), 'plant' => $QParameters->value('plant'), 'line' => $QParameters->value('line'), 'asset' => $QParameters->value('asset'), 'file' => $QParameters->value('file'), 'monitoring_status' => $QParameters->value('monitoring_status'), 'xmlfile' => $QParameters->value('xmlfile'), 'operating_class' => $QParameters->value('operating_class'), 'measurement_state' => $QParameters->value('measurement_state'), 'event_trigger_type' => $QParameters->value('event_trigger_type'), 'channel' => $QParameters->value('channel'));
        }
        $QParameters->freeResult();

        $query = "SELECT count(p.eventid) as count FROM :table_parameters p where 1 = 1 ";

        switch ($_REQUEST ['content_type']) {
            case "plant":
                $query = $query . " and plants_id = " . $_REQUEST ['content_id'];
                break;

            case "line":
                $query = $query . " and lines_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(line) = '" . $_REQUEST ['content_name'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "' ";
                break;

            case "asset":
                $query = $query . " and asset_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(asset) = '" . $_REQUEST ['content_name'] . "' and lower(line) = '" . $_REQUEST ['line'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "'";
                break;

            case "component":
                $query = $query . " and component_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(asset) = '" . $_REQUEST ['asset'] . "' and lower(line) = '" . $_REQUEST ['line'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "' and lower(channel) in (select lower(channel) from delta_sensor where component_id = " . $_REQUEST ['component'] . ")";
                break;

            case "sensor":
                $query = $query . " and sensors_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(asset) = '" . $_REQUEST ['asset'] . "' and lower(line) = '" . $_REQUEST ['line'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "' and lower(channel) = (select lower(channel) from delta_sensor where component_id = " . $_REQUEST ['component'] . " and lower(code) = lower('" . $_REQUEST ['content_name'] . "'))";
                break;
        }

        if(isset($_REQUEST ['start_date']) && !empty($_REQUEST ['start_date']))
        {
            $query = $query . " and p.eventdate >= '" . $_REQUEST ['start_date'] . "'";
        }

        if(isset($_REQUEST ['end_date']) && !empty($_REQUEST ['end_date']))
        {
            $query = $query . " and p.eventdate <= '" . $_REQUEST ['end_date'] . "'";
        }

        if(isset($_REQUEST ['measurement_state']) && $_REQUEST ['measurement_state'] != '-1')
        {
            $query = $query . " and p.measurement_state = '" . $_REQUEST ['measurement_state'] . "'";
        }

        if(isset($_REQUEST ['event_trigger_type']) && $_REQUEST ['event_trigger_type'] != '-1')
        {
            $query = $query . " and p.event_trigger_type = '" . $_REQUEST ['event_trigger_type'] . "'";
        }

        if(isset($_REQUEST ['monitoring_status']) && $_REQUEST ['monitoring_status'] != '-1')
        {
            $query = $query . " and p.monitoring_status = '" . $_REQUEST ['monitoring_status'] . "'";
        }

        if(isset($_REQUEST ['operating_class']) && $_REQUEST ['operating_class'] != '-1')
        {
            $query = $query . " and p.operating_class = '" . $_REQUEST ['operating_class'] . "'";
        }

        if(isset($_REQUEST ['xmlfile']) && $_REQUEST ['xmlfile'] != '-1')
        {
            $query = $query . " and p.xmlfile = '" . $_REQUEST ['xmlfile'] . "'";
        }

        $Qtotal = $osC_Database->query($query);
        $Qtotal->bindTable(':table_parameters', TABLE_PARAMETERS);
        $Qtotal->execute();

        $total = $Qtotal->valueInt('count');

        $response = array(EXT_JSON_READER_TOTAL => $total, EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function indexParameters()
    {
        global $osC_Database;

        $query = 'select p.* from :table_parameters p where indexed = 0 ';
        $query = $query . " order by p.eventdate desc";

        $QParameters = $osC_Database->query($query);

        $QParameters->bindTable(':table_parameters', TABLE_PARAMETERS);
        $QParameters->setExtBatchLimit(0, 100);
        $QParameters->execute();

        //var_dump($QParameters);

        while ($QParameters->next()) {
            $index = 'cpms';
            $doc_type = 'parameter';
            $parameter = $QParameters->toArray();

            $doc = osC_Categories_Admin::getParameterDocument($parameter);

            //var_dump($doc);

            osC_Categories_Admin::indexDocument($index, $doc_type, $doc);

            //echo $output;
        }
    }

    function listSensors()
    {
        global $toC_Json, $osC_Database;

        //$query = "select eventid,eventdate,mp_acrms_value from :table_parameters p where eventdate > :eventdate";

        switch ($_REQUEST ['content_type']) {
            case "customer":
                $query = "select distinct concat(p.plant,' > ',p.line,' > ',p.asset,' > ',(select categories_name from delta_layout_description where categories_id = p.component_id),' > ',(select categories_name from delta_layout_description where categories_id = p.sensors_id)) as name,(select categories_name from delta_layout_description where categories_id = p.sensors_id) as label,sensors_id from :table_parameters p where 1 = 1 ";
                $query = $query . " and customers_id = " . $_REQUEST ['content_id'];
                break;

            case "plant":
                $query = "select distinct concat(p.line,' > ',p.asset,' > ',(select categories_name from delta_layout_description where categories_id = p.component_id),' > ',(select categories_name from delta_layout_description where categories_id = p.sensors_id)) as name,(select categories_name from delta_layout_description where categories_id = p.sensors_id) as label,sensors_id from :table_parameters p where 1 = 1 ";
                $query = $query . " and plants_id = " . $_REQUEST ['content_id'];
                break;

            case "line":
                $query = "select distinct concat(p.asset,' > ',(select categories_name from delta_layout_description where categories_id = p.component_id),' > ',(select categories_name from delta_layout_description where categories_id = p.sensors_id)) as name,(select categories_name from delta_layout_description where categories_id = p.sensors_id) as label,sensors_id from :table_parameters p where 1 = 1 ";
                $query = $query . " and lines_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(line) = '" . $_REQUEST ['content_name'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "' ";
                break;

            case "asset":
                $query = "select distinct concat((select categories_name from delta_layout_description where categories_id = p.component_id),' > ',(select categories_name from delta_layout_description where categories_id = p.sensors_id)) as name,(select categories_name from delta_layout_description where categories_id = p.sensors_id) as label,(select categories_name from delta_layout_description where categories_id = p.sensors_id) as label,sensors_id from :table_parameters p where 1 = 1 ";
                $query = $query . " and asset_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(asset) = '" . $_REQUEST ['content_name'] . "' and lower(line) = '" . $_REQUEST ['line'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "'";
                break;

            case "component":
                $query = "select distinct (select categories_name from delta_layout_description where categories_id = p.sensors_id) as name,(select categories_name from delta_layout_description where categories_id = p.sensors_id) as label,sensors_id from :table_parameters p where 1 = 1 ";
                $query = $query . " and component_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(asset) = '" . $_REQUEST ['asset'] . "' and lower(line) = '" . $_REQUEST ['line'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "' and lower(channel) in (select lower(channel) from delta_sensor where component_id = " . $_REQUEST ['component'] . ")";
                break;

            case "sensor":
                $query = "select distinct (select categories_name from delta_layout_description where categories_id = p.sensors_id) as name,(select categories_name from delta_layout_description where categories_id = p.sensors_id) as label,sensors_id from :table_parameters p where 1 = 1 ";
                $query = $query . " and sensors_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(asset) = '" . $_REQUEST ['asset'] . "' and lower(line) = '" . $_REQUEST ['line'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "' and lower(channel) = (select lower(channel) from delta_sensor where component_id = " . $_REQUEST ['component'] . " and lower(code) = lower('" . $_REQUEST ['content_name'] . "'))";
                break;
        }

        $QParameters = $osC_Database->query($query);

        $QParameters->bindTable(':table_parameters', TABLE_PARAMETERS);
        //$QParameters->bindTable(':table_layout_description', TABLE_LAYOUT_DESCRIPTION);
        $QParameters->execute();

        //var_dump($QParameters);

        $records = array();
        $total = 0;

        while ($QParameters->next()) {
            $name = $QParameters->value('name');

            if (!empty($name)) {
                $records [] = array(
                    'sensors_id' => $QParameters->valueInt('sensors_id'),
                    'name' => $name,
                    'label' => $QParameters->value('label')
                );
            }


            //$records [] = array('eventid' => $QParameters->valueInt('eventid'), 'date' => $QParameters->value('eventdate'), 'value' => $QParameters->valueInt('mp_acrms_value'), 'val' => $QParameters->value('mp_acrms_value'));
            $total++;
        }

        $QParameters->freeResult();

        $response = array(EXT_JSON_READER_TOTAL => $total, EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listCharts()
    {
        global $toC_Json, $osC_Database;

        $col = strtolower($_REQUEST ['column']);

        $query = "select eventid,eventdate,mp_acrms_value, mp_lfrms_value, mp_isorms_value, mp_hfrms_value, mp_acpeak_value, mp_accrest_value, mp_mean_value, mp_peak2peak_value, mp_kurtosis_value, mp_smax_value, lfrms, isorms, hfrms, crest, peak, rms, max, min, peak2peak, mean, std, kurtosis, skewness, smax, histo, a1x, p1x, a2x, p2x, a3x, p3x, file,op1, op2, op3, op4, op5, op6, op7, op8, op9, op10 from :table_parameters p where eventdate > :eventdate";
        //$query = "select eventid,eventdate,mp_acrms_value from :table_parameters p where eventdate > :eventdate";

        switch ($_REQUEST ['content_type']) {
            case "plant":
                $query = $query . " and plants_id = " . $_REQUEST ['content_id'];
                break;

            case "line":
                $query = $query . " and lines_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(line) = '" . $_REQUEST ['content_name'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "' ";
                break;

            case "asset":
                $query = $query . " and asset_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(asset) = '" . $_REQUEST ['content_name'] . "' and lower(line) = '" . $_REQUEST ['line'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "'";
                break;

            case "component":
                $query = $query . " and component_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(asset) = '" . $_REQUEST ['asset'] . "' and lower(line) = '" . $_REQUEST ['line'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "' and lower(channel) in (select lower(channel) from delta_sensor where component_id = " . $_REQUEST ['component'] . ")";
                break;

            case "sensor":
                $query = $query . " and sensors_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(asset) = '" . $_REQUEST ['asset'] . "' and lower(line) = '" . $_REQUEST ['line'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "' and lower(channel) = (select lower(channel) from delta_sensor where component_id = " . $_REQUEST ['component'] . " and lower(code) = lower('" . $_REQUEST ['content_name'] . "'))";
                break;
        }

        $query = $query . " order by p.eventdate";

        $QParameters = $osC_Database->query($query);

        $QParameters->bindTable(':table_parameters', TABLE_PARAMETERS);
        $QParameters->bindValue(':eventdate', $_REQUEST ['eventdate']);
        $QParameters->execute();

        //var_dump($QParameters);

        $eventdate = $_REQUEST ['eventdate'];
        $total = 0;
        $records = array();

        while ($QParameters->next()) {

            $records [] = array('eventid' => $QParameters->valueInt('eventid'), 'date' => $QParameters->value('eventdate'),
                'mp_acrms_value' => str_replace(",", ".", $QParameters->value('mp_acrms_value')),
                'mp_lfrms_value' => str_replace(",", ".", $QParameters->value('mp_lfrms_value')),
                'mp_isorms_value' => str_replace(",", ".", $QParameters->value('mp_isorms_value')),
                'mp_hfrms_value' => str_replace(",", ".", $QParameters->value('mp_hfrms_value')),
                'mp_acpeak_value' => str_replace(",", ".", $QParameters->value('mp_acpeak_value')),
                'mp_accrest_value' => str_replace(",", ".", $QParameters->value('mp_accrest_value')),
                'mp_mean_value' => str_replace(",", ".", $QParameters->value('mp_mean_value')),
                'mp_peak2peak_value' => str_replace(",", ".", $QParameters->value('mp_peak2peak_value')),
                'mp_kurtosis_value' => str_replace(",", ".", $QParameters->value('mp_kurtosis_value')),
                'mp_smax_value' => str_replace(",", ".", $QParameters->value('mp_smax_value')),
                'lfrms' => str_replace(",", ".", $QParameters->value('lfrms')),
                'isorms' => str_replace(",", ".", $QParameters->value('isorms')),
                'hfrms' => str_replace(",", ".", $QParameters->value('hfrms')),
                'crest' => str_replace(",", ".", $QParameters->value('crest')),
                'peak' => str_replace(",", ".", $QParameters->value('peak')),
                'rms' => str_replace(",", ".", $QParameters->value('rms')),
                'max' => str_replace(",", ".", $QParameters->value('max')),
                'min' => str_replace(",", ".", $QParameters->value('min')),
                'peak2peak' => str_replace(",", ".", $QParameters->value('peak2peak')),
                'mean' => str_replace(",", ".", $QParameters->value('mean')),
                'std' => str_replace(",", ".", $QParameters->value('std')),
                'kurtosis' => str_replace(",", ".", $QParameters->value('kurtosis')),
                'skewness' => str_replace(",", ".", $QParameters->value('skewness')),
                'smax' => str_replace(",", ".", $QParameters->value('smax')),
                'histo' => str_replace(",", ".", $QParameters->value('histo')),
                'a1x' => str_replace(",", ".", $QParameters->value('a1x')),
                'p1x' => str_replace(",", ".", $QParameters->value('p1x')),
                'a2x' => str_replace(",", ".", $QParameters->value('a2x')),
                'p2x' => str_replace(",", ".", $QParameters->value('p2x')),
                'a3x' => str_replace(",", ".", $QParameters->value('a3x')),
                'p3x' => str_replace(",", ".", $QParameters->value('p3x')),
                'op1' => str_replace(",", ".", $QParameters->value('op1')),
                'op2' => str_replace(",", ".", $QParameters->value('op2')),
                'op3' => str_replace(",", ".", $QParameters->value('op3')),
                'op4' => str_replace(",", ".", $QParameters->value('op4')),
                'op5' => str_replace(",", ".", $QParameters->value('op5')),
                'op6' => str_replace(",", ".", $QParameters->value('op6')),
                'op7' => str_replace(",", ".", $QParameters->value('op7')),
                'op8' => str_replace(",", ".", $QParameters->value('op8')),
                'op9' => str_replace(",", ".", $QParameters->value('op9')),
                'op10' => str_replace(",", ".", $QParameters->value('op10'))
            );

            //$records [] = array('eventid' => $QParameters->valueInt('eventid'), 'date' => $QParameters->value('eventdate'), 'value' => $QParameters->valueInt('mp_acrms_value'), 'val' => $QParameters->value('mp_acrms_value'));
            $total++;

            if ($QParameters->value('eventdate') > $eventdate) {
                $eventdate = $QParameters->value('eventdate');
            }
        }

        $QParameters->freeResult();

        $response = array(EXT_JSON_READER_TOTAL => $total, EXT_JSON_READER_ROOT => $records, 'eventdate' => $eventdate);

        echo $toC_Json->encode($response);
    }

    function listCharts1()
    {
        global $toC_Json, $osC_Database;

        $query = "select sensors_id,eventid,eventdate,mp_acrms_value, mp_lfrms_value, mp_isorms_value, mp_hfrms_value, mp_acpeak_value, mp_accrest_value, mp_mean_value, mp_peak2peak_value, mp_kurtosis_value, mp_smax_value, lfrms, isorms, hfrms, crest, peak, rms, max, min, peak2peak, mean, std, kurtosis, skewness, smax, histo, a1x, p1x, a2x, p2x, a3x, p3x, file,op1, op2, op3, op4, op5, op6, op7, op8, op9, op10 from :table_parameters p where eventdate > :eventdate";
        //$query = "select eventid,eventdate,mp_acrms_value from :table_parameters p where eventdate > :eventdate";

        switch ($_REQUEST ['content_type']) {
            case "plant":
                $query = $query . " and plants_id = " . $_REQUEST ['content_id'];
                break;

            case "line":
                $query = $query . " and lines_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(line) = '" . $_REQUEST ['content_name'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "' ";
                break;

            case "asset":
                $query = $query . " and asset_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(asset) = '" . $_REQUEST ['content_name'] . "' and lower(line) = '" . $_REQUEST ['line'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "'";
                break;

            case "component":
                $query = $query . " and component_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(asset) = '" . $_REQUEST ['asset'] . "' and lower(line) = '" . $_REQUEST ['line'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "' and lower(channel) in (select lower(channel) from delta_sensor where component_id = " . $_REQUEST ['component'] . ")";
                break;

            case "sensor":
                $query = $query . " and sensors_id in (" . $_REQUEST ['sensors_id1'] . ',' . $_REQUEST ['sensors_id2'] . ")";
                //$query = $query . " and lower(asset) = '" . $_REQUEST ['asset'] . "' and lower(line) = '" . $_REQUEST ['line'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "' and lower(channel) = (select lower(channel) from delta_sensor where component_id = " . $_REQUEST ['component'] . " and lower(code) = lower('" . $_REQUEST ['content_name'] . "'))";
                break;
        }

        if(isset($_REQUEST ['start_date']) && !empty($_REQUEST ['start_date']))
        {
            $query = $query . " and p.eventdate >= '" . $_REQUEST ['start_date'] . "'";
        }

        if(isset($_REQUEST ['end_date']) && !empty($_REQUEST ['end_date']))
        {
            $query = $query . " and p.eventdate <= '" . $_REQUEST ['end_date'] . "'";
        }

        if(isset($_REQUEST ['measurement_state']) && $_REQUEST ['measurement_state'] != '-1')
        {
            $query = $query . " and p.measurement_state = '" . $_REQUEST ['measurement_state'] . "'";
        }

        if(isset($_REQUEST ['event_trigger_type']) && $_REQUEST ['event_trigger_type'] != '-1')
        {
            $query = $query . " and p.event_trigger_type = '" . $_REQUEST ['event_trigger_type'] . "'";
        }

        if(isset($_REQUEST ['monitoring_status']) && $_REQUEST ['monitoring_status'] != '-1')
        {
            $query = $query . " and p.monitoring_status = '" . $_REQUEST ['monitoring_status'] . "'";
        }

        if(isset($_REQUEST ['operating_class']) && $_REQUEST ['operating_class'] != '-1')
        {
            $query = $query . " and p.operating_class = '" . $_REQUEST ['operating_class'] . "'";
        }

        if(isset($_REQUEST ['xmlfile']) && $_REQUEST ['xmlfile'] != '-1')
        {
            $query = $query . " and p.xmlfile = '" . $_REQUEST ['xmlfile'] . "'";
        }

        $query = $query . " order by p.eventdate";

        $QParameters = $osC_Database->query($query);

        $QParameters->bindTable(':table_parameters', TABLE_PARAMETERS);
        $QParameters->bindValue(':eventdate', $_REQUEST ['eventdate']);
        $QParameters->execute();

        //var_dump($QParameters);

        $eventdate = $_REQUEST ['eventdate'];
        $total = 0;
        $records = array();

        while ($QParameters->next()) {

            $records [] = array(
                'sensors_id' => $QParameters->valueInt('sensors_id'),
                'eventid' => $QParameters->valueInt('eventid'),
                'date' => $QParameters->value('eventdate'),
                $QParameters->valueInt('sensors_id') . '_mp_acrms_value' => str_replace(",", ".", $QParameters->value('mp_acrms_value')),
                $QParameters->valueInt('sensors_id') . '_mp_lfrms_value' => str_replace(",", ".", $QParameters->value('mp_lfrms_value')),
                $QParameters->valueInt('sensors_id') . '_mp_isorms_value' => str_replace(",", ".", $QParameters->value('mp_isorms_value')),
                $QParameters->valueInt('sensors_id') . '_mp_hfrms_value' => str_replace(",", ".", $QParameters->value('mp_hfrms_value')),
                $QParameters->valueInt('sensors_id') . '_mp_acpeak_value' => str_replace(",", ".", $QParameters->value('mp_acpeak_value')),
                $QParameters->valueInt('sensors_id') . '_mp_accrest_value' => str_replace(",", ".", $QParameters->value('mp_accrest_value')),
                $QParameters->valueInt('sensors_id') . '_mp_mean_value' => str_replace(",", ".", $QParameters->value('mp_mean_value')),
                $QParameters->valueInt('sensors_id') . '_mp_peak2peak_value' => str_replace(",", ".", $QParameters->value('mp_peak2peak_value')),
                $QParameters->valueInt('sensors_id') . '_mp_kurtosis_value' => str_replace(",", ".", $QParameters->value('mp_kurtosis_value')),
                $QParameters->valueInt('sensors_id') . '_mp_smax_value' => str_replace(",", ".", $QParameters->value('mp_smax_value')),
                $QParameters->valueInt('sensors_id') . '_lfrms' => str_replace(",", ".", $QParameters->value('lfrms')),
                $QParameters->valueInt('sensors_id') . '_isorms' => str_replace(",", ".", $QParameters->value('isorms')),
                $QParameters->valueInt('sensors_id') . '_hfrms' => str_replace(",", ".", $QParameters->value('hfrms')),
                $QParameters->valueInt('sensors_id') . '_crest' => str_replace(",", ".", $QParameters->value('crest')),
                $QParameters->valueInt('sensors_id') . '_peak' => str_replace(",", ".", $QParameters->value('peak')),
                $QParameters->valueInt('sensors_id') . '_rms' => str_replace(",", ".", $QParameters->value('rms')),
                $QParameters->valueInt('sensors_id') . '_max' => str_replace(",", ".", $QParameters->value('max')),
                $QParameters->valueInt('sensors_id') . '_min' => str_replace(",", ".", $QParameters->value('min')),
                $QParameters->valueInt('sensors_id') . '_peak2peak' => str_replace(",", ".", $QParameters->value('peak2peak')),
                $QParameters->valueInt('sensors_id') . '_mean' => str_replace(",", ".", $QParameters->value('mean')),
                $QParameters->valueInt('sensors_id') . '_std' => str_replace(",", ".", $QParameters->value('std')),
                $QParameters->valueInt('sensors_id') . '_kurtosis' => str_replace(",", ".", $QParameters->value('kurtosis')),
                $QParameters->valueInt('sensors_id') . '_skewness' => str_replace(",", ".", $QParameters->value('skewness')),
                $QParameters->valueInt('sensors_id') . '_smax' => str_replace(",", ".", $QParameters->value('smax')),
                $QParameters->valueInt('sensors_id') . '_histo' => str_replace(",", ".", $QParameters->value('histo')),
                $QParameters->valueInt('sensors_id') . '_a1x' => str_replace(",", ".", $QParameters->value('a1x')),
                $QParameters->valueInt('sensors_id') . '_p1x' => str_replace(",", ".", $QParameters->value('p1x')),
                $QParameters->valueInt('sensors_id') . '_a2x' => str_replace(",", ".", $QParameters->value('a2x')),
                $QParameters->valueInt('sensors_id') . '_p2x' => str_replace(",", ".", $QParameters->value('p2x')),
                $QParameters->valueInt('sensors_id') . '_a3x' => str_replace(",", ".", $QParameters->value('a3x')),
                $QParameters->valueInt('sensors_id') . '_p3x' => str_replace(",", ".", $QParameters->value('p3x')),
                $QParameters->valueInt('sensors_id') . '_op1' => str_replace(",", ".", $QParameters->value('op1')),
                $QParameters->valueInt('sensors_id') . '_op2' => str_replace(",", ".", $QParameters->value('op2')),
                $QParameters->valueInt('sensors_id') . '_op3' => str_replace(",", ".", $QParameters->value('op3')),
                $QParameters->valueInt('sensors_id') . '_op4' => str_replace(",", ".", $QParameters->value('op4')),
                $QParameters->valueInt('sensors_id') . '_op5' => str_replace(",", ".", $QParameters->value('op5')),
                $QParameters->valueInt('sensors_id') . '_op6' => str_replace(",", ".", $QParameters->value('op6')),
                $QParameters->valueInt('sensors_id') . '_op7' => str_replace(",", ".", $QParameters->value('op7')),
                $QParameters->valueInt('sensors_id') . '_op8' => str_replace(",", ".", $QParameters->value('op8')),
                $QParameters->valueInt('sensors_id') . '_op9' => str_replace(",", ".", $QParameters->value('op9')),
                $QParameters->valueInt('sensors_id') . '_op10' => str_replace(",", ".", $QParameters->value('op10'))
            );

            //$records [] = array('eventid' => $QParameters->valueInt('eventid'), 'date' => $QParameters->value('eventdate'), 'value' => $QParameters->valueInt('mp_acrms_value'), 'val' => $QParameters->value('mp_acrms_value'));
            $total++;

            if ($QParameters->value('eventdate') > $eventdate) {
                $eventdate = $QParameters->value('eventdate');
            }
        }

        $QParameters->freeResult();

        $response = array(EXT_JSON_READER_TOTAL => $total, EXT_JSON_READER_ROOT => $records, 'eventdate' => $eventdate);

        echo $toC_Json->encode($response);
    }

    function listStats()
    {
        global $toC_Json, $osC_Database;

        $total = 0;
        $records = array();

        switch ($_REQUEST ['content_type']) {
            case 'sensor':
                $query = "select status,count(*) as nbre from delta_status where 1 = 1 ";
                $query = $query . " and customers_id = " . $_REQUEST ['customers_id'];
                $query = $query . " and content_type = '" . $_REQUEST ['content_type'] . "'";
                $query = $query . " group by status";

                $QParameters = $osC_Database->query($query);
                $QParameters->execute();

                //var_dump($QParameters);

                while ($QParameters->next()) {
                    $color = "green";

                    switch (strtolower($QParameters->value('status'))) {
                        case "ok";
                            $color = "green";
                            break;

                        case "error";
                            $color = "gray";
                            break;

                        case "ral";
                            $color = "yellow";
                            break;

                        case "pal";
                            $color = "orange";
                            break;

                        case "al";
                            $color = "red";
                            break;
                    }

                    $records [] = array(
                        'status' => $QParameters->value('status'),
                        'color' => $color,
                        'nbre' => $QParameters->valueInt('nbre')
                    );

                    $total++;
                }

                $QParameters->freeResult();
                break;

            case 'ticket':
                //$query = "select status_id as status,count(*) as nbre from delta_status where 1 = 1 ";
                $query = "SELECT status_id , COUNT( * ) AS nbre FROM delta_tickets WHERE 1 = 1 ";
                $query = $query . " and customers_id = " . $_REQUEST ['customers_id'];
                $query = $query . " GROUP BY status_id";

                $QParameters = $osC_Database->query($query);
                $QParameters->execute();

                //var_dump($QParameters);

                while ($QParameters->next()) {
                    $color = "green";
                    $status = 'Open';

                    switch (strtolower($QParameters->value('status_id'))) {
                        case "1";
                            $color = "red";
                            $status = 'Ouvert';
                            break;

                        case "2";
                            $color = "green";
                            $status = 'Resolu';
                            break;

                        case "3";
                            $color = "green";
                            $status = 'Cloture';
                            break;

                        case "4";
                            $color = "#76e557";
                            $status = 'Archive';
                            break;

                        case "5";
                            $color = "gray";
                            $status = 'Supprime';
                            break;
                    }

                    $records [] = array(
                        'status' => $status,
                        'color' => $color,
                        'nbre' => $QParameters->valueInt('nbre')
                    );

                    $total++;
                }

                $QParameters->freeResult();
                break;
        }

        $response = array(EXT_JSON_READER_TOTAL => $total, EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listStockCharts()
    {
        global $toC_Json, $osC_Database;

        switch ($_REQUEST ['content_type']) {
            case "plant":
                $query = "select distinct concat(p.line,' > ',p.asset,' > ',(select categories_name from delta_layout_description where categories_id = p.component_id),' > ',(select categories_name from delta_layout_description where categories_id = p.sensors_id)) as name,sensors_id from :table_parameters p where 1 = 1 ";
                $query = $query . " and plants_id = " . $_REQUEST ['content_id'];
                break;

            case "line":
                $query = "select distinct concat(p.asset,' > ',(select categories_name from delta_layout_description where categories_id = p.component_id),' > ',(select categories_name from delta_layout_description where categories_id = p.sensors_id)) as name,sensors_id from :table_parameters p where 1 = 1 ";
                $query = $query . " and lines_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(line) = '" . $_REQUEST ['content_name'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "' ";
                break;

            case "asset":
                $query = "select distinct concat((select categories_name from delta_layout_description where categories_id = p.component_id),' > ',(select categories_name from delta_layout_description where categories_id = p.sensors_id)) as name,sensors_id from :table_parameters p where 1 = 1 ";
                $query = $query . " and asset_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(asset) = '" . $_REQUEST ['content_name'] . "' and lower(line) = '" . $_REQUEST ['line'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "'";
                break;

            case "component":
                $query = "select distinct (select categories_name from delta_layout_description where categories_id = p.sensors_id) as name,sensors_id from :table_parameters p where 1 = 1 ";
                $query = $query . " and component_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(asset) = '" . $_REQUEST ['asset'] . "' and lower(line) = '" . $_REQUEST ['line'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "' and lower(channel) in (select lower(channel) from delta_sensor where component_id = " . $_REQUEST ['component'] . ")";
                break;

            case "sensor":
                $query = "select distinct (select categories_name from delta_layout_description where categories_id = p.sensors_id) as name,sensors_id from :table_parameters p where 1 = 1 ";
                $query = $query . " and sensors_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(asset) = '" . $_REQUEST ['asset'] . "' and lower(line) = '" . $_REQUEST ['line'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "' and lower(channel) = (select lower(channel) from delta_sensor where component_id = " . $_REQUEST ['component'] . " and lower(code) = lower('" . $_REQUEST ['content_name'] . "'))";
                break;
        }

        $QParameters = $osC_Database->query($query);

        $QParameters->bindTable(':table_parameters', TABLE_PARAMETERS);
        //$QParameters->bindTable(':table_layout_description', TABLE_LAYOUT_DESCRIPTION);
        $QParameters->execute();

        //var_dump($QParameters);

        $sensors = array();
        $total = 0;

        while ($QParameters->next()) {

            $sensors [] = array(
                'sensors_id' => $QParameters->valueInt('sensors_id'),
                'title' => $QParameters->value('name')
            );

            //$records [] = array('eventid' => $QParameters->valueInt('eventid'), 'date' => $QParameters->value('eventdate'), 'value' => $QParameters->valueInt('mp_acrms_value'), 'val' => $QParameters->value('mp_acrms_value'));
            $total++;
        }

        $QParameters->freeResult();

        $datasets = array();

        foreach ($sensors as $key => $sensor) {
            //echo $key;
            $col = strtolower($_REQUEST ['column']);

            $query = "select eventid,eventdate,mp_acrms_value, mp_lfrms_value, mp_isorms_value, mp_hfrms_value, mp_acpeak_value, mp_accrest_value, mp_mean_value, mp_peak2peak_value, mp_kurtosis_value, mp_smax_value, lfrms, isorms, hfrms, crest, peak, rms, max, min, peak2peak, mean, std, kurtosis, skewness, smax, histo, a1x, p1x, a2x, p2x, a3x, p3x, file,op1, op2, op3, op4, op5, op6, op7, op8, op9, op10 from :table_parameters p where eventdate > :eventdate";
            $query = $query . " and sensors_id = " . $sensor ['sensors_id'];
            //$query = "select eventid,eventdate,mp_acrms_value from :table_parameters p where eventdate > :eventdate";

            $query = $query . " order by p.eventdate";

            $QParameters = $osC_Database->query($query);

            $QParameters->bindTable(':table_parameters', TABLE_PARAMETERS);
            $QParameters->bindValue(':eventdate', $_REQUEST ['eventdate']);
            $QParameters->execute();

            //var_dump($QParameters);

            $eventdate = $_REQUEST ['eventdate'];
            //$total = 0;
            $records = array();

            while ($QParameters->next()) {

                $records [] = array('eventid' => $QParameters->valueInt('eventid'), 'date' => $QParameters->value('eventdate'),
                    'mp_acrms_value' => str_replace(",", ".", $QParameters->value('mp_acrms_value')),
                    'mp_lfrms_value' => str_replace(",", ".", $QParameters->value('mp_lfrms_value')),
                    'mp_isorms_value' => str_replace(",", ".", $QParameters->value('mp_isorms_value')),
                    'mp_hfrms_value' => str_replace(",", ".", $QParameters->value('mp_hfrms_value')),
                    'mp_acpeak_value' => str_replace(",", ".", $QParameters->value('mp_acpeak_value')),
                    'mp_accrest_value' => str_replace(",", ".", $QParameters->value('mp_accrest_value')),
                    'mp_mean_value' => str_replace(",", ".", $QParameters->value('mp_mean_value')),
                    'mp_peak2peak_value' => str_replace(",", ".", $QParameters->value('mp_peak2peak_value')),
                    'mp_kurtosis_value' => str_replace(",", ".", $QParameters->value('mp_kurtosis_value')),
                    'mp_smax_value' => str_replace(",", ".", $QParameters->value('mp_smax_value')),
                    'lfrms' => str_replace(",", ".", $QParameters->value('lfrms')),
                    'isorms' => str_replace(",", ".", $QParameters->value('isorms')),
                    'hfrms' => str_replace(",", ".", $QParameters->value('hfrms')),
                    'crest' => str_replace(",", ".", $QParameters->value('crest')),
                    'peak' => str_replace(",", ".", $QParameters->value('peak')),
                    'rms' => str_replace(",", ".", $QParameters->value('rms')),
                    'max' => str_replace(",", ".", $QParameters->value('max')),
                    'min' => str_replace(",", ".", $QParameters->value('min')),
                    'peak2peak' => str_replace(",", ".", $QParameters->value('peak2peak')),
                    'mean' => str_replace(",", ".", $QParameters->value('mean')),
                    'std' => str_replace(",", ".", $QParameters->value('std')),
                    'kurtosis' => str_replace(",", ".", $QParameters->value('kurtosis')),
                    'skewness' => str_replace(",", ".", $QParameters->value('skewness')),
                    'smax' => str_replace(",", ".", $QParameters->value('smax')),
                    'histo' => str_replace(",", ".", $QParameters->value('histo')),
                    'a1x' => str_replace(",", ".", $QParameters->value('a1x')),
                    'p1x' => str_replace(",", ".", $QParameters->value('p1x')),
                    'a2x' => str_replace(",", ".", $QParameters->value('a2x')),
                    'p2x' => str_replace(",", ".", $QParameters->value('p2x')),
                    'a3x' => str_replace(",", ".", $QParameters->value('a3x')),
                    'p3x' => str_replace(",", ".", $QParameters->value('p3x')),
                    'op1' => str_replace(",", ".", $QParameters->value('op1')),
                    'op2' => str_replace(",", ".", $QParameters->value('op2')),
                    'op3' => str_replace(",", ".", $QParameters->value('op3')),
                    'op4' => str_replace(",", ".", $QParameters->value('op4')),
                    'op5' => str_replace(",", ".", $QParameters->value('op5')),
                    'op6' => str_replace(",", ".", $QParameters->value('op6')),
                    'op7' => str_replace(",", ".", $QParameters->value('op7')),
                    'op8' => str_replace(",", ".", $QParameters->value('op8')),
                    'op9' => str_replace(",", ".", $QParameters->value('op9')),
                    'op10' => str_replace(",", ".", $QParameters->value('op10'))
                );

                //$records [] = array('eventid' => $QParameters->valueInt('eventid'), 'date' => $QParameters->value('eventdate'), 'value' => $QParameters->valueInt('mp_acrms_value'), 'val' => $QParameters->value('mp_acrms_value'));
                //$total++;

                if ($QParameters->value('eventdate') > $eventdate) {
                    $eventdate = $QParameters->value('eventdate');
                }
            }

            $QParameters->freeResult();

            $datasets[] = array(
                'sensors_id' => $sensor['sensors_id'],
                'title' => $sensor['title'],
                'dataprovider' => $records
            );
        }

        $response = array(EXT_JSON_READER_TOTAL => $total, EXT_JSON_READER_ROOT => $datasets, 'eventdate' => $eventdate);

        echo $toC_Json->encode($response);
    }

    function channelStatus()
    {
        global $toC_Json, $osC_Database;

        $query = "select chname,chunit,chstatus,acrms_status,lfrms_status,isorms_status,hfrms_status,acpeak_status,accrest_status,mean_status,peak2peak_status,kurtosis_status,smax_status,mp_acrms_value, mp_lfrms_value, mp_isorms_value, mp_hfrms_value, mp_acpeak_value, mp_accrest_value, mp_mean_value, mp_peak2peak_value, mp_kurtosis_value, mp_smax_value, lfrms, isorms, hfrms, crest, peak, rms, max, min, peak2peak, mean, std, kurtosis, skewness, smax, histo, a1x, p1x, a2x, p2x, a3x, p3x, file, channel, chname, chunit, chstatus, op1, op2, op3, op4, op5, op6, op7, op8, op9, op10 from :table_parameters p where 1 = 1 ";
        //$query = "select eventid,eventdate,mp_acrms_value from :table_parameters p where eventdate > :eventdate";

        switch ($_REQUEST ['content_type']) {
            case "plant":
                $query = $query . " and plants_id = " . $_REQUEST ['content_id'];
                break;

            case "line":
                $query = $query . " and lines_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(line) = '" . $_REQUEST ['content_name'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "' ";
                break;

            case "asset":
                $query = $query . " and asset_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(asset) = '" . $_REQUEST ['content_name'] . "' and lower(line) = '" . $_REQUEST ['line'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "'";
                break;

            case "component":
                $query = $query . " and component_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(asset) = '" . $_REQUEST ['asset'] . "' and lower(line) = '" . $_REQUEST ['line'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "' and lower(channel) in (select lower(channel) from delta_sensor where component_id = " . $_REQUEST ['component'] . ")";
                break;

            case "sensor":
                $query = $query . " and sensors_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(asset) = '" . $_REQUEST ['asset'] . "' and lower(line) = '" . $_REQUEST ['line'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "' and lower(channel) = (select lower(channel) from delta_sensor where component_id = " . $_REQUEST ['component'] . " and lower(code) = lower('" . $_REQUEST ['content_name'] . "'))";
                break;
        }

        $query = $query . " order by p.eventdate desc limit 1";

        $QParameters = $osC_Database->query($query);

        $QParameters->bindTable(':table_parameters', TABLE_PARAMETERS);
        $QParameters->execute();

        //var_dump($QParameters);

        $records = array();

        while ($QParameters->next()) {
            $file = "xxxxxxx.info";
            $entry_icon = osc_icon_from_filename($file);

            $records [] = array('icon' => $entry_icon, 'var' => "Channel Name", 'val' => $QParameters->value('chname'));
            $records [] = array('icon' => $entry_icon, 'var' => "Channel Unit", 'val' => $QParameters->value('chunit'));

            switch (strtolower($QParameters->value('chstatus'))) {
                case "ok":
                    $file = "xxxxxxx.success";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Channel Status", 'val' => "<div style='text-align: center; background: darkgreen; color: white;'><strong>" . $QParameters->value('chstatus') . "</strong></div>");
                    break;

                case "ral":
                    $file = "xxxxxxx.warning";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Channel Status", 'val' => "<div style='text-align: center; background: yellow; color: black;'><strong>" . $QParameters->value('chstatus') . "</strong></div>");
                    break;

                case "pal":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Channel Status", 'val' => "<div style='text-align: center; background: orange; color: black;'><strong>" . $QParameters->value('chstatus') . "</strong></div>");
                    break;

                case "al":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Channel Status", 'val' => "<div style='text-align: center; background: red; color: white;'><strong>" . $QParameters->value('chstatus') . "</strong></div>");
                    break;

                case "error":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Channel Status", 'val' => "<div style='text-align: center; background: gray; color: white;'><strong>" . $QParameters->value('chstatus') . "</strong></div>");
                    break;

                default :
                    $file = "xxxxxxx.info";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Channel Status", 'val' => $QParameters->value('chstatus'));
            }

            switch (strtolower($QParameters->value('acrms_status'))) {
                case "ok":
                    $file = "xxxxxxx.success";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Acrms Status", 'val' => "<div style='text-align: center; background: darkgreen; color: white;'><strong>" . $QParameters->value('acrms_status') . "</strong></div>");
                    break;

                case "ral":
                    $file = "xxxxxxx.warning";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Acrms Status", 'val' => "<div style='text-align: center; background: yellow; color: black;'><strong>" . $QParameters->value('acrms_status') . "</strong></div>");
                    break;

                case "pal":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Acrms Status", 'val' => "<div style='text-align: center; background: orange; color: black;'><strong>" . $QParameters->value('acrms_status') . "</strong></div>");
                    break;

                case "al":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Acrms Status", 'val' => "<div style='text-align: center; background: red; color: white;'><strong>" . $QParameters->value('acrms_status') . "</strong></div>");
                    break;

                case "error":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Acrms Status", 'val' => "<div style='text-align: center; background: gray; color: white;'><strong>" . $QParameters->value('acrms_status') . "</strong></div>");
                    break;

                default :
                    $file = "xxxxxxx.info";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Acrms Status", 'val' => $QParameters->value('acrms_status'));
            }

            switch (strtolower($QParameters->value('lfrms_status'))) {
                case "ok":
                    $file = "xxxxxxx.success";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Lfrms Status", 'val' => "<div style='text-align: center; background: darkgreen; color: white;'><strong>" . $QParameters->value('lfrms_status') . "</strong></div>");
                    break;

                case "ral":
                    $file = "xxxxxxx.warning";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Lfrms Status", 'val' => "<div style='text-align: center; background: yellow; color: black;'><strong>" . $QParameters->value('lfrms_status') . "</strong></div>");
                    break;

                case "pal":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Lfrms Status", 'val' => "<div style='text-align: center; background: orange; color: black;'><strong>" . $QParameters->value('lfrms_status') . "</strong></div>");
                    break;

                case "al":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Lfrms Status", 'val' => "<div style='text-align: center; background: red; color: white;'><strong>" . $QParameters->value('lfrms_status') . "</strong></div>");
                    break;

                case "error":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Lfrms Status", 'val' => "<div style='text-align: center; background: gray; color: white;'><strong>" . $QParameters->value('lfrms_status') . "</strong></div>");
                    break;

                default :
                    $file = "xxxxxxx.info";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Lfrms Status", 'val' => $QParameters->value('lfrms_status'));
            }

            switch (strtolower($QParameters->value('isorms_status'))) {
                case "ok":
                    $file = "xxxxxxx.success";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "isorms Status", 'val' => "<div style='text-align: center; background: darkgreen; color: white;'><strong>" . $QParameters->value('isorms_status') . "</strong></div>");
                    break;

                case "ral":
                    $file = "xxxxxxx.warning";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "isorms Status", 'val' => "<div style='text-align: center; background: yellow; color: black;'><strong>" . $QParameters->value('isorms_status') . "</strong></div>");
                    break;

                case "pal":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "isorms Status", 'val' => "<div style='text-align: center; background: orange; color: black;'><strong>" . $QParameters->value('isorms_status') . "</strong></div>");
                    break;

                case "al":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "isorms Status", 'val' => "<div style='text-align: center; background: red; color: white;'><strong>" . $QParameters->value('isorms_status') . "</strong></div>");
                    break;

                case "error":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "isorms Status", 'val' => "<div style='text-align: center; background: gray; color: white;'><strong>" . $QParameters->value('isorms_status') . "</strong></div>");
                    break;

                default :
                    $file = "xxxxxxx.info";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "isorms Status", 'val' => $QParameters->value('isorms_status'));
            }

            switch (strtolower($QParameters->value('hfrms_status'))) {
                case "ok":
                    $file = "xxxxxxx.success";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "hfrms Status", 'val' => "<div style='text-align: center; background: darkgreen; color: white;'><strong>" . $QParameters->value('hfrms_status') . "</strong></div>");
                    break;

                case "ral":
                    $file = "xxxxxxx.warning";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "hfrms Status", 'val' => "<div style='text-align: center; background: yellow; color: black;'><strong>" . $QParameters->value('hfrms_status') . "</strong></div>");
                    break;

                case "pal":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "hfrms Status", 'val' => "<div style='text-align: center; background: orange; color: black;'><strong>" . $QParameters->value('hfrms_status') . "</strong></div>");
                    break;

                case "al":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "hfrms Status", 'val' => "<div style='text-align: center; background: red; color: white;'><strong>" . $QParameters->value('hfrms_status') . "</strong></div>");
                    break;

                case "error":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "hfrms Status", 'val' => "<div style='text-align: center; background: gray; color: white;'><strong>" . $QParameters->value('hfrms_status') . "</strong></div>");
                    break;

                default :
                    $file = "xxxxxxx.info";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "hfrms Status", 'val' => $QParameters->value('hfrms_status'));
            }

            switch (strtolower($QParameters->value('acpeak_status'))) {
                case "ok":
                    $file = "xxxxxxx.success";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "acpeak Status", 'val' => "<div style='text-align: center; background: darkgreen; color: white;'><strong>" . $QParameters->value('acpeak_status') . "</strong></div>");
                    break;

                case "ral":
                    $file = "xxxxxxx.warning";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "acpeak Status", 'val' => "<div style='text-align: center; background: yellow; color: black;'><strong>" . $QParameters->value('acpeak_status') . "</strong></div>");
                    break;

                case "pal":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "acpeak Status", 'val' => "<div style='text-align: center; background: orange; color: black;'><strong>" . $QParameters->value('acpeak_status') . "</strong></div>");
                    break;

                case "al":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "acpeak Status", 'val' => "<div style='text-align: center; background: red; color: white;'><strong>" . $QParameters->value('acpeak_status') . "</strong></div>");
                    break;

                case "error":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "acpeak Status", 'val' => "<div style='text-align: center; background: gray; color: white;'><strong>" . $QParameters->value('acpeak_status') . "</strong></div>");
                    break;

                default :
                    $file = "xxxxxxx.info";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "acpeak Status", 'val' => $QParameters->value('acpeak_status'));
            }

            switch (strtolower($QParameters->value('accrest_status'))) {
                case "ok":
                    $file = "xxxxxxx.success";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "accrest Status", 'val' => "<div style='text-align: center; background: darkgreen; color: white;'><strong>" . $QParameters->value('accrest_status') . "</strong></div>");
                    break;

                case "ral":
                    $file = "xxxxxxx.warning";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "accrest Status", 'val' => "<div style='text-align: center; background: yellow; color: black;'><strong>" . $QParameters->value('accrest_status') . "</strong></div>");
                    break;

                case "pal":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "accrest Status", 'val' => "<div style='text-align: center; background: orange; color: black;'><strong>" . $QParameters->value('accrest_status') . "</strong></div>");
                    break;

                case "al":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "accrest Status", 'val' => "<div style='text-align: center; background: red; color: white;'><strong>" . $QParameters->value('accrest_status') . "</strong></div>");
                    break;

                case "error":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "accrest Status", 'val' => "<div style='text-align: center; background: gray; color: white;'><strong>" . $QParameters->value('accrest_status') . "</strong></div>");
                    break;

                default :
                    $file = "xxxxxxx.info";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "accrest Status", 'val' => $QParameters->value('accrest_status'));
            }

            switch (strtolower($QParameters->value('mean_status'))) {
                case "ok":
                    $file = "xxxxxxx.success";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "mean Status", 'val' => "<div style='text-align: center; background: darkgreen; color: white;'><strong>" . $QParameters->value('mean_status') . "</strong></div>");
                    break;

                case "ral":
                    $file = "xxxxxxx.warning";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "mean Status", 'val' => "<div style='text-align: center; background: yellow; color: black;'><strong>" . $QParameters->value('mean_status') . "</strong></div>");
                    break;

                case "pal":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "mean Status", 'val' => "<div style='text-align: center; background: orange; color: black;'><strong>" . $QParameters->value('mean_status') . "</strong></div>");
                    break;

                case "al":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "mean Status", 'val' => "<div style='text-align: center; background: red; color: white;'><strong>" . $QParameters->value('mean_status') . "</strong></div>");
                    break;

                case "error":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "mean Status", 'val' => "<div style='text-align: center; background: gray; color: white;'><strong>" . $QParameters->value('mean_status') . "</strong></div>");
                    break;

                default :
                    $file = "xxxxxxx.info";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "mean Status", 'val' => $QParameters->value('mean_status'));
            }

            switch (strtolower($QParameters->value('peak2peak_status'))) {
                case "ok":
                    $file = "xxxxxxx.success";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "peak2peak Status", 'val' => "<div style='text-align: center; background: darkgreen; color: white;'><strong>" . $QParameters->value('peak2peak_status') . "</strong></div>");
                    break;

                case "ral":
                    $file = "xxxxxxx.warning";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "peak2peak Status", 'val' => "<div style='text-align: center; background: yellow; color: black;'><strong>" . $QParameters->value('peak2peak_status') . "</strong></div>");
                    break;

                case "pal":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "peak2peak Status", 'val' => "<div style='text-align: center; background: orange; color: black;'><strong>" . $QParameters->value('peak2peak_status') . "</strong></div>");
                    break;

                case "al":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "peak2peak Status", 'val' => "<div style='text-align: center; background: red; color: white;'><strong>" . $QParameters->value('peak2peak_status') . "</strong></div>");
                    break;

                case "error":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "peak2peak Status", 'val' => "<div style='text-align: center; background: gray; color: white;'><strong>" . $QParameters->value('peak2peak_status') . "</strong></div>");
                    break;

                default :
                    $file = "xxxxxxx.info";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "peak2peak Status", 'val' => $QParameters->value('peak2peak_status'));
            }

            switch (strtolower($QParameters->value('kurtosis_status'))) {
                case "ok":
                    $file = "xxxxxxx.success";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "kurtosis Status", 'val' => "<div style='text-align: center; background: darkgreen; color: white;'><strong>" . $QParameters->value('kurtosis_status') . "</strong></div>");
                    break;

                case "ral":
                    $file = "xxxxxxx.warning";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "kurtosis Status", 'val' => "<div style='text-align: center; background: yellow; color: black;'><strong>" . $QParameters->value('kurtosis_status') . "</strong></div>");
                    break;

                case "pal":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "kurtosis Status", 'val' => "<div style='text-align: center; background: orange; color: black;'><strong>" . $QParameters->value('kurtosis_status') . "</strong></div>");
                    break;

                case "al":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "kurtosis Status", 'val' => "<div style='text-align: center; background: red; color: white;'><strong>" . $QParameters->value('kurtosis_status') . "</strong></div>");
                    break;

                case "error":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "kurtosis Status", 'val' => "<div style='text-align: center; background: gray; color: white;'><strong>" . $QParameters->value('kurtosis_status') . "</strong></div>");
                    break;

                default :
                    $file = "xxxxxxx.info";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "kurtosis Status", 'val' => $QParameters->value('kurtosis_status'));
            }

            switch (strtolower($QParameters->value('smax_status'))) {
                case "ok":
                    $file = "xxxxxxx.success";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "smax Status", 'val' => "<div style='text-align: center; background: darkgreen; color: white;'><strong>" . $QParameters->value('smax_status') . "</strong></div>");
                    break;

                case "ral":
                    $file = "xxxxxxx.warning";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "smax Status", 'val' => "<div style='text-align: center; background: yellow; color: black;'><strong>" . $QParameters->value('smax_status') . "</strong></div>");
                    break;

                case "pal":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "smax Status", 'val' => "<div style='text-align: center; background: orange; color: black;'><strong>" . $QParameters->value('smax_status') . "</strong></div>");
                    break;

                case "al":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "smax Status", 'val' => "<div style='text-align: center; background: red; color: white;'><strong>" . $QParameters->value('smax_status') . "</strong></div>");
                    break;

                case "error":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "smax Status", 'val' => "<div style='text-align: center; background: gray; color: white;'><strong>" . $QParameters->value('smax_status') . "</strong></div>");
                    break;

                default :
                    $file = "xxxxxxx.info";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "smax Status", 'val' => $QParameters->value('smax_status'));
            }

            $file = "xxxxxxx.info";
            $entry_icon = osc_icon_from_filename($file);

            $records [] = array('icon' => $entry_icon, 'var' => "mp_acrms_value", 'val' => $QParameters->value('mp_acrms_value'));
            $records [] = array('icon' => $entry_icon, 'var' => "mp_lfrms_value", 'val' => $QParameters->value('mp_lfrms_value'));
            $records [] = array('icon' => $entry_icon, 'var' => "mp_isorms_value", 'val' => $QParameters->value('mp_isorms_value'));
            $records [] = array('icon' => $entry_icon, 'var' => "mp_hfrms_value", 'val' => $QParameters->value('mp_hfrms_value'));
            $records [] = array('icon' => $entry_icon, 'var' => "mp_acpeak_value", 'val' => $QParameters->value('mp_acpeak_value'));
            $records [] = array('icon' => $entry_icon, 'var' => "mp_accrest_value", 'val' => $QParameters->value('mp_accrest_value'));
            $records [] = array('icon' => $entry_icon, 'var' => "mp_mean_value", 'val' => $QParameters->value('mp_mean_value'));
            $records [] = array('icon' => $entry_icon, 'var' => "mp_peak2peak_value", 'val' => $QParameters->value('mp_peak2peak_value'));
            $records [] = array('icon' => $entry_icon, 'var' => "mp_kurtosis_value", 'val' => $QParameters->value('mp_kurtosis_value'));
            $records [] = array('icon' => $entry_icon, 'var' => "mp_smax_value", 'val' => $QParameters->value('mp_smax_value'));
            $records [] = array('icon' => $entry_icon, 'var' => "lfrms", 'val' => $QParameters->value('lfrms'));
            $records [] = array('icon' => $entry_icon, 'var' => "isorms", 'val' => $QParameters->value('isorms'));
            $records [] = array('icon' => $entry_icon, 'var' => "hfrms", 'val' => $QParameters->value('hfrms'));
            $records [] = array('icon' => $entry_icon, 'var' => "crest", 'val' => $QParameters->value('crest'));
            $records [] = array('icon' => $entry_icon, 'var' => "peak", 'val' => $QParameters->value('peak'));
            $records [] = array('icon' => $entry_icon, 'var' => "rms", 'val' => $QParameters->value('rms'));
            $records [] = array('icon' => $entry_icon, 'var' => "max", 'val' => $QParameters->value('max'));
            $records [] = array('icon' => $entry_icon, 'var' => "min", 'val' => $QParameters->value('min'));
            $records [] = array('icon' => $entry_icon, 'var' => "peak2peak", 'val' => $QParameters->value('peak2peak'));
            $records [] = array('icon' => $entry_icon, 'var' => "mean", 'val' => $QParameters->value('mean'));
            $records [] = array('icon' => $entry_icon, 'var' => "std", 'val' => $QParameters->value('std'));
            $records [] = array('icon' => $entry_icon, 'var' => "kurtosis", 'val' => $QParameters->value('kurtosis'));
            $records [] = array('icon' => $entry_icon, 'var' => "skewness", 'val' => $QParameters->value('skewness'));
            $records [] = array('icon' => $entry_icon, 'var' => "smax", 'val' => $QParameters->value('smax'));
            $records [] = array('icon' => $entry_icon, 'var' => "histo", 'val' => $QParameters->value('histo'));
            $records [] = array('icon' => $entry_icon, 'var' => "a1x", 'val' => $QParameters->value('a1x'));
            $records [] = array('icon' => $entry_icon, 'var' => "p1x", 'val' => $QParameters->value('p1x'));
            $records [] = array('icon' => $entry_icon, 'var' => "a2x", 'val' => $QParameters->value('a2x'));
            $records [] = array('icon' => $entry_icon, 'var' => "p2x", 'val' => $QParameters->value('p2x'));
            $records [] = array('icon' => $entry_icon, 'var' => "a3x", 'val' => $QParameters->value('a3x'));
            $records [] = array('icon' => $entry_icon, 'var' => "p3x", 'val' => $QParameters->value('p3x'));
            $records [] = array('icon' => $entry_icon, 'var' => "op1", 'val' => $QParameters->value('op1'));
            $records [] = array('icon' => $entry_icon, 'var' => "op2", 'val' => $QParameters->value('op2'));
            $records [] = array('icon' => $entry_icon, 'var' => "op3", 'val' => $QParameters->value('op3'));
            $records [] = array('icon' => $entry_icon, 'var' => "op4", 'val' => $QParameters->value('op4'));
            $records [] = array('icon' => $entry_icon, 'var' => "op5", 'val' => $QParameters->value('op5'));
            $records [] = array('icon' => $entry_icon, 'var' => "op6", 'val' => $QParameters->value('op6'));
            $records [] = array('icon' => $entry_icon, 'var' => "op7", 'val' => $QParameters->value('op7'));
            $records [] = array('icon' => $entry_icon, 'var' => "op8", 'val' => $QParameters->value('op8'));
            $records [] = array('icon' => $entry_icon, 'var' => "op9", 'val' => $QParameters->value('op9'));
            $records [] = array('icon' => $entry_icon, 'var' => "op10", 'val' => $QParameters->value('op10'));
        }

        $QParameters->freeResult();

        $response = array(EXT_JSON_READER_TOTAL => 53, EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function assetStatus()
    {
        global $toC_Json, $osC_Database;

        $query = "select cpms_ip,cnt,datetime,equipmentstatus,operatingclass,measuringstate from delta_watchdog p where 1 = 1 ";
        $query = $query . " and cpms_ip = (select cpms_ip from delta_asset where asset_id = " . $_REQUEST ['content_id'] . ')';
        //$query = "select eventid,eventdate,mp_acrms_value from :table_parameters p where eventdate > :eventdate";

        $QParameters = $osC_Database->query($query);
        $QParameters->execute();

        //var_dump($QParameters);

        $records = array();

        while ($QParameters->next()) {
            $file = "xxxxxxx.info";
            $entry_icon = osc_icon_from_filename($file);

            $records [] = array('icon' => $entry_icon, 'var' => "CPMS IP", 'val' => $QParameters->value('cpms_ip'));
            $records [] = array('icon' => $entry_icon, 'var' => "Asset", 'val' => $_REQUEST ['content_name']);
            $records [] = array('icon' => $entry_icon, 'var' => "Date Time", 'val' => $QParameters->value('datetime'));

            switch (strtolower($QParameters->value('equipmentstatus'))) {
                case "ok":
                    $file = "xxxxxxx.success";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Equipment Status", 'val' => "<div style='text-align: center; background: darkgreen; color: white;'><strong>" . $QParameters->value('equipmentstatus') . "</strong></div>");
                    break;

                case "ral":
                    $file = "xxxxxxx.warning";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Equipment Status", 'val' => "<div style='text-align: center; background: yellow; color: black;'><strong>" . $QParameters->value('equipmentstatus') . "</strong></div>");
                    break;

                case "pal":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Equipment Status", 'val' => "<div style='text-align: center; background: orange; color: black;'><strong>" . $QParameters->value('equipmentstatus') . "</strong></div>");
                    break;

                case "al":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Equipment Status", 'val' => "<div style='text-align: center; background: red; color: white;'><strong>" . $QParameters->value('equipmentstatus') . "</strong></div>");
                    break;

                case "error":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Equipment Status", 'val' => "<div style='text-align: center; background: gray; color: white;'><strong>" . $QParameters->value('equipmentstatus') . "</strong></div>");
                    break;

                default :
                    $file = "xxxxxxx.info";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Equipment Status", 'val' => $QParameters->value('equipmentstatus'));
            }

            $file = $QParameters->value('cnt') > 1 ? "xxxxxxx.error" : "xxxxxxx.info";
            $status = $QParameters->value('cnt') > 1 ? "Offline" : "Online";
            $entry_icon = osc_icon_from_filename($file);

            switch ($status) {
                case "Offline":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Online Status", 'val' => "<div style='text-align: center; background: gray; color: white;'><strong>" . $status . "</strong></div>");
                    break;

                case "Online":
                    $file = "xxxxxxx.info";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Online Status", 'val' => "<div style='text-align: center; background: darkgreen; color: white;'><strong>" . $status . "</strong></div>");
                    break;

                default :
                    $file = "xxxxxxx.info";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Online Status", 'val' => "<div style='text-align: center; background: darkgreen; color: white;'><strong>" . $status . "</strong></div>");
            }

            $file = "xxxxxxx.info";
            $entry_icon = osc_icon_from_filename($file);
            $records [] = array('icon' => $entry_icon, 'var' => "Operating Class", 'val' => $QParameters->value('operatingclass'));
            $records [] = array('icon' => $entry_icon, 'var' => "Measuring State", 'val' => $QParameters->value('measuringstate'));
        }

        $QParameters->freeResult();

        $response = array(EXT_JSON_READER_TOTAL => 6, EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function loadParameter()
    {
        global $toC_Json, $osC_Database;

        $query = "select chname,chunit,monitoring_status,chstatus,acrms_status,lfrms_status,isorms_status,hfrms_status,acpeak_status,accrest_status,mean_status,peak2peak_status,kurtosis_status,smax_status,mp_acrms_value, mp_lfrms_value, mp_isorms_value, mp_hfrms_value, mp_acpeak_value, mp_accrest_value, mp_mean_value, mp_peak2peak_value, mp_kurtosis_value, mp_smax_value, lfrms, isorms, hfrms, crest, peak, rms, max, min, peak2peak, mean, std, kurtosis, skewness, smax, histo, a1x, p1x, a2x, p2x, a3x, p3x, file, channel, chname, chunit, chstatus, op1, op2, op3, op4, op5, op6, op7, op8, op9, op10 from :table_parameters p where 1 = 1 ";
        $query = $query . " and eventid = '" . $_REQUEST ['content_id'] . "'";

        $QParameters = $osC_Database->query($query);

        $QParameters->bindTable(':table_parameters', TABLE_PARAMETERS);
        $QParameters->execute();

        $records = array();

        while ($QParameters->next()) {
            $file = "xxxxxxx.info";
            $entry_icon = osc_icon_from_filename($file);

            $records [] = array('icon' => $entry_icon, 'var' => "Channel Name", 'val' => $QParameters->value('chname'));
            $records [] = array('icon' => $entry_icon, 'var' => "Channel Unit", 'val' => $QParameters->value('chunit'));

            switch (strtolower($QParameters->value('monitoring_status'))) {
                case "ok":
                    $file = "xxxxxxx.success";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Monitoring Status", 'val' => "<div style='text-align: center; background: darkgreen; color: white;'><strong>" . $QParameters->value('monitoring_status') . "</strong></div>");
                    break;

                case "ral":
                    $file = "xxxxxxx.warning";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Monitoring Status", 'val' => "<div style='text-align: center; background: yellow; color: black;'><strong>" . $QParameters->value('monitoring_status') . "</strong></div>");
                    break;

                case "pal":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Monitoring Status", 'val' => "<div style='text-align: center; background: orange; color: black;'><strong>" . $QParameters->value('monitoring_status') . "</strong></div>");
                    break;

                case "al":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Monitoring Status", 'val' => "<div style='text-align: center; background: red; color: white;'><strong>" . $QParameters->value('monitoring_status') . "</strong></div>");
                    break;

                case "error":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Monitoring Status", 'val' => "<div style='text-align: center; background: gray; color: white;'><strong>" . $QParameters->value('monitoring_status') . "</strong></div>");
                    break;

                default :
                    $file = "xxxxxxx.info";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Monitoring Status", 'val' => $QParameters->value('monitoring_status'));
            }

            switch (strtolower($QParameters->value('chstatus'))) {
                case "ok":
                    $file = "xxxxxxx.success";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Channel Status", 'val' => "<div style='text-align: center; background: darkgreen; color: white;'><strong>" . $QParameters->value('chstatus') . "</strong></div>");
                    break;

                case "ral":
                    $file = "xxxxxxx.warning";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Channel Status", 'val' => "<div style='text-align: center; background: yellow; color: black;'><strong>" . $QParameters->value('chstatus') . "</strong></div>");
                    break;

                case "pal":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Channel Status", 'val' => "<div style='text-align: center; background: orange; color: black;'><strong>" . $QParameters->value('chstatus') . "</strong></div>");
                    break;

                case "al":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Channel Status", 'val' => "<div style='text-align: center; background: red; color: white;'><strong>" . $QParameters->value('chstatus') . "</strong></div>");
                    break;

                case "error":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Channel Status", 'val' => "<div style='text-align: center; background: gray; color: white;'><strong>" . $QParameters->value('chstatus') . "</strong></div>");
                    break;

                default :
                    $file = "xxxxxxx.info";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Channel Status", 'val' => $QParameters->value('chstatus'));
            }

            switch (strtolower($QParameters->value('acrms_status'))) {
                case "ok":
                    $file = "xxxxxxx.success";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Acrms Status", 'val' => "<div style='text-align: center; background: darkgreen; color: white;'><strong>" . $QParameters->value('acrms_status') . "</strong></div>");
                    break;

                case "ral":
                    $file = "xxxxxxx.warning";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Acrms Status", 'val' => "<div style='text-align: center; background: yellow; color: black;'><strong>" . $QParameters->value('acrms_status') . "</strong></div>");
                    break;

                case "pal":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Acrms Status", 'val' => "<div style='text-align: center; background: orange; color: black;'><strong>" . $QParameters->value('acrms_status') . "</strong></div>");
                    break;

                case "al":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Acrms Status", 'val' => "<div style='text-align: center; background: red; color: white;'><strong>" . $QParameters->value('acrms_status') . "</strong></div>");
                    break;

                case "error":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Acrms Status", 'val' => "<div style='text-align: center; background: gray; color: white;'><strong>" . $QParameters->value('acrms_status') . "</strong></div>");
                    break;

                default :
                    $file = "xxxxxxx.info";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Acrms Status", 'val' => $QParameters->value('acrms_status'));
            }

            switch (strtolower($QParameters->value('lfrms_status'))) {
                case "ok":
                    $file = "xxxxxxx.success";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Lfrms Status", 'val' => "<div style='text-align: center; background: darkgreen; color: white;'><strong>" . $QParameters->value('lfrms_status') . "</strong></div>");
                    break;

                case "ral":
                    $file = "xxxxxxx.warning";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Lfrms Status", 'val' => "<div style='text-align: center; background: yellow; color: black;'><strong>" . $QParameters->value('lfrms_status') . "</strong></div>");
                    break;

                case "pal":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Lfrms Status", 'val' => "<div style='text-align: center; background: orange; color: black;'><strong>" . $QParameters->value('lfrms_status') . "</strong></div>");
                    break;

                case "al":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Lfrms Status", 'val' => "<div style='text-align: center; background: red; color: white;'><strong>" . $QParameters->value('lfrms_status') . "</strong></div>");
                    break;

                case "error":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Lfrms Status", 'val' => "<div style='text-align: center; background: gray; color: white;'><strong>" . $QParameters->value('lfrms_status') . "</strong></div>");
                    break;

                default :
                    $file = "xxxxxxx.info";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "Lfrms Status", 'val' => $QParameters->value('lfrms_status'));
            }

            switch (strtolower($QParameters->value('isorms_status'))) {
                case "ok":
                    $file = "xxxxxxx.success";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "isorms Status", 'val' => "<div style='text-align: center; background: darkgreen; color: white;'><strong>" . $QParameters->value('isorms_status') . "</strong></div>");
                    break;

                case "ral":
                    $file = "xxxxxxx.warning";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "isorms Status", 'val' => "<div style='text-align: center; background: yellow; color: black;'><strong>" . $QParameters->value('isorms_status') . "</strong></div>");
                    break;

                case "pal":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "isorms Status", 'val' => "<div style='text-align: center; background: orange; color: black;'><strong>" . $QParameters->value('isorms_status') . "</strong></div>");
                    break;

                case "al":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "isorms Status", 'val' => "<div style='text-align: center; background: red; color: white;'><strong>" . $QParameters->value('isorms_status') . "</strong></div>");
                    break;

                case "error":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "isorms Status", 'val' => "<div style='text-align: center; background: gray; color: white;'><strong>" . $QParameters->value('isorms_status') . "</strong></div>");
                    break;

                default :
                    $file = "xxxxxxx.info";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "isorms Status", 'val' => $QParameters->value('isorms_status'));
            }

            switch (strtolower($QParameters->value('hfrms_status'))) {
                case "ok":
                    $file = "xxxxxxx.success";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "hfrms Status", 'val' => "<div style='text-align: center; background: darkgreen; color: white;'><strong>" . $QParameters->value('hfrms_status') . "</strong></div>");
                    break;

                case "ral":
                    $file = "xxxxxxx.warning";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "hfrms Status", 'val' => "<div style='text-align: center; background: yellow; color: black;'><strong>" . $QParameters->value('hfrms_status') . "</strong></div>");
                    break;

                case "pal":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "hfrms Status", 'val' => "<div style='text-align: center; background: orange; color: black;'><strong>" . $QParameters->value('hfrms_status') . "</strong></div>");
                    break;

                case "al":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "hfrms Status", 'val' => "<div style='text-align: center; background: red; color: white;'><strong>" . $QParameters->value('hfrms_status') . "</strong></div>");
                    break;

                case "error":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "hfrms Status", 'val' => "<div style='text-align: center; background: gray; color: white;'><strong>" . $QParameters->value('hfrms_status') . "</strong></div>");
                    break;

                default :
                    $file = "xxxxxxx.info";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "hfrms Status", 'val' => $QParameters->value('hfrms_status'));
            }

            switch (strtolower($QParameters->value('acpeak_status'))) {
                case "ok":
                    $file = "xxxxxxx.success";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "acpeak Status", 'val' => "<div style='text-align: center; background: darkgreen; color: white;'><strong>" . $QParameters->value('acpeak_status') . "</strong></div>");
                    break;

                case "ral":
                    $file = "xxxxxxx.warning";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "acpeak Status", 'val' => "<div style='text-align: center; background: yellow; color: black;'><strong>" . $QParameters->value('acpeak_status') . "</strong></div>");
                    break;

                case "pal":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "acpeak Status", 'val' => "<div style='text-align: center; background: orange; color: black;'><strong>" . $QParameters->value('acpeak_status') . "</strong></div>");
                    break;

                case "al":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "acpeak Status", 'val' => "<div style='text-align: center; background: red; color: white;'><strong>" . $QParameters->value('acpeak_status') . "</strong></div>");
                    break;

                case "error":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "acpeak Status", 'val' => "<div style='text-align: center; background: gray; color: white;'><strong>" . $QParameters->value('acpeak_status') . "</strong></div>");
                    break;

                default :
                    $file = "xxxxxxx.info";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "acpeak Status", 'val' => $QParameters->value('acpeak_status'));
            }

            switch (strtolower($QParameters->value('accrest_status'))) {
                case "ok":
                    $file = "xxxxxxx.success";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "accrest Status", 'val' => "<div style='text-align: center; background: darkgreen; color: white;'><strong>" . $QParameters->value('accrest_status') . "</strong></div>");
                    break;

                case "ral":
                    $file = "xxxxxxx.warning";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "accrest Status", 'val' => "<div style='text-align: center; background: yellow; color: black;'><strong>" . $QParameters->value('accrest_status') . "</strong></div>");
                    break;

                case "pal":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "accrest Status", 'val' => "<div style='text-align: center; background: orange; color: black;'><strong>" . $QParameters->value('accrest_status') . "</strong></div>");
                    break;

                case "al":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "accrest Status", 'val' => "<div style='text-align: center; background: red; color: white;'><strong>" . $QParameters->value('accrest_status') . "</strong></div>");
                    break;

                case "error":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "accrest Status", 'val' => "<div style='text-align: center; background: gray; color: white;'><strong>" . $QParameters->value('accrest_status') . "</strong></div>");
                    break;

                default :
                    $file = "xxxxxxx.info";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "accrest Status", 'val' => $QParameters->value('accrest_status'));
            }

            switch (strtolower($QParameters->value('mean_status'))) {
                case "ok":
                    $file = "xxxxxxx.success";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "mean Status", 'val' => "<div style='text-align: center; background: darkgreen; color: white;'><strong>" . $QParameters->value('mean_status') . "</strong></div>");
                    break;

                case "ral":
                    $file = "xxxxxxx.warning";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "mean Status", 'val' => "<div style='text-align: center; background: yellow; color: black;'><strong>" . $QParameters->value('mean_status') . "</strong></div>");
                    break;

                case "pal":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "mean Status", 'val' => "<div style='text-align: center; background: orange; color: black;'><strong>" . $QParameters->value('mean_status') . "</strong></div>");
                    break;

                case "al":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "mean Status", 'val' => "<div style='text-align: center; background: red; color: white;'><strong>" . $QParameters->value('mean_status') . "</strong></div>");
                    break;

                case "error":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "mean Status", 'val' => "<div style='text-align: center; background: gray; color: white;'><strong>" . $QParameters->value('mean_status') . "</strong></div>");
                    break;

                default :
                    $file = "xxxxxxx.info";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "mean Status", 'val' => $QParameters->value('mean_status'));
            }

            switch (strtolower($QParameters->value('peak2peak_status'))) {
                case "ok":
                    $file = "xxxxxxx.success";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "peak2peak Status", 'val' => "<div style='text-align: center; background: darkgreen; color: white;'><strong>" . $QParameters->value('peak2peak_status') . "</strong></div>");
                    break;

                case "ral":
                    $file = "xxxxxxx.warning";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "peak2peak Status", 'val' => "<div style='text-align: center; background: yellow; color: black;'><strong>" . $QParameters->value('peak2peak_status') . "</strong></div>");
                    break;

                case "pal":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "peak2peak Status", 'val' => "<div style='text-align: center; background: orange; color: black;'><strong>" . $QParameters->value('peak2peak_status') . "</strong></div>");
                    break;

                case "al":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "peak2peak Status", 'val' => "<div style='text-align: center; background: red; color: white;'><strong>" . $QParameters->value('peak2peak_status') . "</strong></div>");
                    break;

                case "error":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "peak2peak Status", 'val' => "<div style='text-align: center; background: gray; color: white;'><strong>" . $QParameters->value('peak2peak_status') . "</strong></div>");
                    break;

                default :
                    $file = "xxxxxxx.info";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "peak2peak Status", 'val' => $QParameters->value('peak2peak_status'));
            }

            switch (strtolower($QParameters->value('kurtosis_status'))) {
                case "ok":
                    $file = "xxxxxxx.success";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "kurtosis Status", 'val' => "<div style='text-align: center; background: darkgreen; color: white;'><strong>" . $QParameters->value('kurtosis_status') . "</strong></div>");
                    break;

                case "ral":
                    $file = "xxxxxxx.warning";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "kurtosis Status", 'val' => "<div style='text-align: center; background: yellow; color: black;'><strong>" . $QParameters->value('kurtosis_status') . "</strong></div>");
                    break;

                case "pal":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "kurtosis Status", 'val' => "<div style='text-align: center; background: orange; color: black;'><strong>" . $QParameters->value('kurtosis_status') . "</strong></div>");
                    break;

                case "al":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "kurtosis Status", 'val' => "<div style='text-align: center; background: red; color: white;'><strong>" . $QParameters->value('kurtosis_status') . "</strong></div>");
                    break;

                case "error":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "kurtosis Status", 'val' => "<div style='text-align: center; background: gray; color: white;'><strong>" . $QParameters->value('kurtosis_status') . "</strong></div>");
                    break;

                default :
                    $file = "xxxxxxx.info";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "kurtosis Status", 'val' => $QParameters->value('kurtosis_status'));
            }

            switch (strtolower($QParameters->value('smax_status'))) {
                case "ok":
                    $file = "xxxxxxx.success";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "smax Status", 'val' => "<div style='text-align: center; background: darkgreen; color: white;'><strong>" . $QParameters->value('smax_status') . "</strong></div>");
                    break;

                case "ral":
                    $file = "xxxxxxx.warning";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "smax Status", 'val' => "<div style='text-align: center; background: yellow; color: black;'><strong>" . $QParameters->value('smax_status') . "</strong></div>");
                    break;

                case "pal":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "smax Status", 'val' => "<div style='text-align: center; background: orange; color: black;'><strong>" . $QParameters->value('smax_status') . "</strong></div>");
                    break;

                case "al":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "smax Status", 'val' => "<div style='text-align: center; background: red; color: white;'><strong>" . $QParameters->value('smax_status') . "</strong></div>");
                    break;

                case "error":
                    $file = "xxxxxxx.error";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "smax Status", 'val' => "<div style='text-align: center; background: gray; color: white;'><strong>" . $QParameters->value('smax_status') . "</strong></div>");
                    break;

                default :
                    $file = "xxxxxxx.info";
                    $entry_icon = osc_icon_from_filename($file);
                    $records [] = array('icon' => $entry_icon, 'var' => "smax Status", 'val' => $QParameters->value('smax_status'));
            }

            $file = "xxxxxxx.info";
            $entry_icon = osc_icon_from_filename($file);

            $records [] = array('icon' => $entry_icon, 'var' => "mp_acrms_value", 'val' => $QParameters->value('mp_acrms_value'));
            $records [] = array('icon' => $entry_icon, 'var' => "mp_lfrms_value", 'val' => $QParameters->value('mp_lfrms_value'));
            $records [] = array('icon' => $entry_icon, 'var' => "mp_isorms_value", 'val' => $QParameters->value('mp_isorms_value'));
            $records [] = array('icon' => $entry_icon, 'var' => "mp_hfrms_value", 'val' => $QParameters->value('mp_hfrms_value'));
            $records [] = array('icon' => $entry_icon, 'var' => "mp_acpeak_value", 'val' => $QParameters->value('mp_acpeak_value'));
            $records [] = array('icon' => $entry_icon, 'var' => "mp_accrest_value", 'val' => $QParameters->value('mp_accrest_value'));
            $records [] = array('icon' => $entry_icon, 'var' => "mp_mean_value", 'val' => $QParameters->value('mp_mean_value'));
            $records [] = array('icon' => $entry_icon, 'var' => "mp_peak2peak_value", 'val' => $QParameters->value('mp_peak2peak_value'));
            $records [] = array('icon' => $entry_icon, 'var' => "mp_kurtosis_value", 'val' => $QParameters->value('mp_kurtosis_value'));
            $records [] = array('icon' => $entry_icon, 'var' => "mp_smax_value", 'val' => $QParameters->value('mp_smax_value'));
            $records [] = array('icon' => $entry_icon, 'var' => "lfrms", 'val' => $QParameters->value('lfrms'));
            $records [] = array('icon' => $entry_icon, 'var' => "isorms", 'val' => $QParameters->value('isorms'));
            $records [] = array('icon' => $entry_icon, 'var' => "hfrms", 'val' => $QParameters->value('hfrms'));
            $records [] = array('icon' => $entry_icon, 'var' => "crest", 'val' => $QParameters->value('crest'));
            $records [] = array('icon' => $entry_icon, 'var' => "peak", 'val' => $QParameters->value('peak'));
            $records [] = array('icon' => $entry_icon, 'var' => "rms", 'val' => $QParameters->value('rms'));
            $records [] = array('icon' => $entry_icon, 'var' => "max", 'val' => $QParameters->value('max'));
            $records [] = array('icon' => $entry_icon, 'var' => "min", 'val' => $QParameters->value('min'));
            $records [] = array('icon' => $entry_icon, 'var' => "peak2peak", 'val' => $QParameters->value('peak2peak'));
            $records [] = array('icon' => $entry_icon, 'var' => "mean", 'val' => $QParameters->value('mean'));
            $records [] = array('icon' => $entry_icon, 'var' => "std", 'val' => $QParameters->value('std'));
            $records [] = array('icon' => $entry_icon, 'var' => "kurtosis", 'val' => $QParameters->value('kurtosis'));
            $records [] = array('icon' => $entry_icon, 'var' => "skewness", 'val' => $QParameters->value('skewness'));
            $records [] = array('icon' => $entry_icon, 'var' => "smax", 'val' => $QParameters->value('smax'));
            $records [] = array('icon' => $entry_icon, 'var' => "histo", 'val' => $QParameters->value('histo'));
            $records [] = array('icon' => $entry_icon, 'var' => "a1x", 'val' => $QParameters->value('a1x'));
            $records [] = array('icon' => $entry_icon, 'var' => "p1x", 'val' => $QParameters->value('p1x'));
            $records [] = array('icon' => $entry_icon, 'var' => "a2x", 'val' => $QParameters->value('a2x'));
            $records [] = array('icon' => $entry_icon, 'var' => "p2x", 'val' => $QParameters->value('p2x'));
            $records [] = array('icon' => $entry_icon, 'var' => "a3x", 'val' => $QParameters->value('a3x'));
            $records [] = array('icon' => $entry_icon, 'var' => "p3x", 'val' => $QParameters->value('p3x'));
            $records [] = array('icon' => $entry_icon, 'var' => "op1", 'val' => $QParameters->value('op1'));
            $records [] = array('icon' => $entry_icon, 'var' => "op2", 'val' => $QParameters->value('op2'));
            $records [] = array('icon' => $entry_icon, 'var' => "op3", 'val' => $QParameters->value('op3'));
            $records [] = array('icon' => $entry_icon, 'var' => "op4", 'val' => $QParameters->value('op4'));
            $records [] = array('icon' => $entry_icon, 'var' => "op5", 'val' => $QParameters->value('op5'));
            $records [] = array('icon' => $entry_icon, 'var' => "op6", 'val' => $QParameters->value('op6'));
            $records [] = array('icon' => $entry_icon, 'var' => "op7", 'val' => $QParameters->value('op7'));
            $records [] = array('icon' => $entry_icon, 'var' => "op8", 'val' => $QParameters->value('op8'));
            $records [] = array('icon' => $entry_icon, 'var' => "op9", 'val' => $QParameters->value('op9'));
            $records [] = array('icon' => $entry_icon, 'var' => "op10", 'val' => $QParameters->value('op10'));
        }

        $QParameters->freeResult();

        $response = array(EXT_JSON_READER_TOTAL => 53, EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function channelInfos()
    {
        global $toC_Json, $osC_Database;

        $query = "select chname,chunit,chstatus from :table_parameters p where 1 = 1 ";
        //$query = "select eventid,eventdate,mp_acrms_value from :table_parameters p where eventdate > :eventdate";

        switch ($_REQUEST ['content_type']) {
            case "plant":
                $query = $query . " and plants_id = " . $_REQUEST ['content_id'];
                break;

            case "line":
                $query = $query . " and lines_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(line) = '" . $_REQUEST ['content_name'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "' ";
                break;

            case "asset":
                $query = $query . " and asset_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(asset) = '" . $_REQUEST ['content_name'] . "' and lower(line) = '" . $_REQUEST ['line'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "'";
                break;

            case "component":
                $query = $query . " and component_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(asset) = '" . $_REQUEST ['asset'] . "' and lower(line) = '" . $_REQUEST ['line'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "' and lower(channel) in (select lower(channel) from delta_sensor where component_id = " . $_REQUEST ['component'] . ")";
                break;

            case "sensor":
                $query = $query . " and sensors_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(asset) = '" . $_REQUEST ['asset'] . "' and lower(line) = '" . $_REQUEST ['line'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "' and lower(channel) = (select lower(channel) from delta_sensor where component_id = " . $_REQUEST ['component'] . " and lower(code) = lower('" . $_REQUEST ['content_name'] . "'))";
                break;
        }

        $query = $query . " order by p.eventdate desc limit 1";

        $QParameters = $osC_Database->query($query);

        $QParameters->bindTable(':table_parameters', TABLE_PARAMETERS);
        $QParameters->execute();

        //var_dump($QParameters);

        $file = "xxxxxxx.info";
        $entry_icon = osc_icon_from_filename($file);

        $records = array();

        while ($QParameters->next()) {

            $records [] = array('icon' => $entry_icon, 'var' => "Channel Name", 'val' => $QParameters->value('chname'));
            $records [] = array('icon' => $entry_icon, 'var' => "Channel Unit", 'val' => $QParameters->value('chunit'));
            $records [] = array('icon' => $entry_icon, 'var' => "Channel Status", 'val' => $QParameters->value('chstatus'));
        }

        $QParameters->freeResult();

        $response = array(EXT_JSON_READER_TOTAL => 3, EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listCategories()
    {
        global $toC_Json, $osC_Language, $osC_Database;

        $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start'];
        $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit'];

        $Qcategories = $osC_Database->query('select c.categories_id, cd.categories_name, c.categories_image, c.parent_id, c.sort_order, c.categories_status, c.date_added, c.last_modified from :table_categories c, :table_categories_description cd where c.categories_id = cd.categories_id and cd.language_id = :language_id');
        $Qcategories->appendQuery('and c.parent_id = :parent_id');

        if (isset($_REQUEST['categories_id']) && !empty($_REQUEST['categories_id'])) {
            $Qcategories->bindInt(':parent_id', $_REQUEST['categories_id']);
        } else {
            $Qcategories->bindInt(':parent_id', 0);
        }

        if (isset($_REQUEST['date_added']) && !empty($_REQUEST['date_added'])) {
            $Qcategories->appendQuery('and c.date_added > :date_added');
            $Qcategories->bindValue(':date_added', $_REQUEST['date_added']);
        }

        if (isset($_REQUEST['search']) && !empty($_REQUEST['search'])) {
            $Qcategories->appendQuery('and cd.categories_name like :categories_name');
            $Qcategories->bindValue(':categories_name', $_REQUEST['search']);
        }

        $count = 0;
        $Qcategories->appendQuery('order by c.sort_order, cd.categories_name');
        $Qcategories->bindTable(':table_categories', TABLE_CATEGORIES);
        $Qcategories->bindTable(':table_categories_description', TABLE_CATEGORIES_DESCRIPTION);
        $Qcategories->bindInt(':language_id', $osC_Language->getID());
        $Qcategories->setExtBatchLimit($start, $limit);
        $Qcategories->execute();

        $records = array();

        $records[] = array('categories_id' => -1,
            'categories_name' => 'Accueil',
            'status' => '1',
            'path' => '');

        $osC_CategoryTree = new osC_CategoryTree();
        while ($Qcategories->next()) {
            $records[] = array('categories_id' => $Qcategories->value('categories_id'),
                'categories_name' => $Qcategories->value('categories_name'),
                'status' => $Qcategories->valueInt('categories_status'),
                'path' => $osC_CategoryTree->buildBreadcrumb($Qcategories->valueInt('categories_id')));
            $count++;
        }

        $user_categories_array = array();

        if ($_SESSION[admin][username] == 'admin') {
            $user_categories_array = $records;
        } else {
            $roles = $_SESSION[admin][roles];
            $count = 0;

            foreach ($records as $category) {
                $permissions = content::getContentPermissions($category['categories_id'], 'pages');
                $can_read_permissions = explode(';', $permissions['can_read']);
                $can_write_permissions = explode(';', $permissions['can_write']);
                $can_modify_permissions = explode(';', $permissions['can_modify']);
                $can_publish_permissions = explode(';', $permissions['can_publish']);

                $can_see = false;

                foreach ($roles as $role) {
                    if (in_array($role, $can_read_permissions) || in_array($role, $can_write_permissions) || in_array($role, $can_modify_permissions) || in_array($role, $can_publish_permissions)) {
                        $can_see = true;
                    }

                    if (in_array(-1, $can_read_permissions) || in_array(-1, $can_write_permissions) || in_array(-1, $can_modify_permissions) || in_array(-1, $can_publish_permissions)) {
                        $can_see = true;
                    }
                }

                if ($can_see) {
                    $user_categories_array[] = $category;
                    $count++;
                }
            }
        }

        $response = array(EXT_JSON_READER_TOTAL => $count,
            EXT_JSON_READER_ROOT => $user_categories_array);

        //echo "<pre>", print_r($_REQUEST, true), "</pre>";
        echo $toC_Json->encode($response);
    }

    function listMeasurementstate()
    {
        global $toC_Json, $osC_Database;

        $query = "SELECT DISTINCT measurement_state FROM delta_parameters where 1 = 1 ";

        switch ($_REQUEST ['content_type']) {
            case "plant":
                $query = $query . " and plants_id = " . $_REQUEST ['content_id'];
                break;

            case "line":
                $query = $query . " and lines_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(line) = '" . $_REQUEST ['content_name'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "' ";
                break;

            case "asset":
                $query = $query . " and asset_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(asset) = '" . $_REQUEST ['content_name'] . "' and lower(line) = '" . $_REQUEST ['line'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "'";
                break;

            case "component":
                $query = $query . " and component_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(asset) = '" . $_REQUEST ['asset'] . "' and lower(line) = '" . $_REQUEST ['line'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "' and lower(channel) in (select lower(channel) from delta_sensor where component_id = " . $_REQUEST ['component'] . ")";
                break;

            case "sensor":
                $query = $query . " and sensors_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(asset) = '" . $_REQUEST ['asset'] . "' and lower(line) = '" . $_REQUEST ['line'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "' and lower(channel) = (select lower(channel) from delta_sensor where component_id = " . $_REQUEST ['component'] . " and lower(code) = lower('" . $_REQUEST ['content_name'] . "'))";
                break;
        }

        $Qstate = $osC_Database->query($query);
        $Qstate->execute();

        $records = array();
        $count = 0;

        $records[] = array('id' => -1,
            'label' => 'Measurement State');

        while ($Qstate->next()) {

            $records[] = array('id' => $Qstate->value('measurement_state'),
                'label' => $Qstate->value('measurement_state'));

            $count++;
        }

        $response = array(EXT_JSON_READER_TOTAL => $count,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listEventtriggertype()
    {
        global $toC_Json, $osC_Database;

        $query = "SELECT DISTINCT event_trigger_type FROM delta_parameters where 1 = 1 ";

        switch ($_REQUEST ['content_type']) {
            case "plant":
                $query = $query . " and plants_id = " . $_REQUEST ['content_id'];
                break;

            case "line":
                $query = $query . " and lines_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(line) = '" . $_REQUEST ['content_name'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "' ";
                break;

            case "asset":
                $query = $query . " and asset_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(asset) = '" . $_REQUEST ['content_name'] . "' and lower(line) = '" . $_REQUEST ['line'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "'";
                break;

            case "component":
                $query = $query . " and component_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(asset) = '" . $_REQUEST ['asset'] . "' and lower(line) = '" . $_REQUEST ['line'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "' and lower(channel) in (select lower(channel) from delta_sensor where component_id = " . $_REQUEST ['component'] . ")";
                break;

            case "sensor":
                $query = $query . " and sensors_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(asset) = '" . $_REQUEST ['asset'] . "' and lower(line) = '" . $_REQUEST ['line'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "' and lower(channel) = (select lower(channel) from delta_sensor where component_id = " . $_REQUEST ['component'] . " and lower(code) = lower('" . $_REQUEST ['content_name'] . "'))";
                break;
        }

        $Qstate = $osC_Database->query($query);
        $Qstate->execute();

        $records = array();
        $count = 0;

        $records[] = array('id' => -1,
            'label' => 'Event Trigger Type');

        while ($Qstate->next()) {

            $records[] = array('id' => $Qstate->value('event_trigger_type'),
                'label' => $Qstate->value('event_trigger_type'));

            $count++;
        }

        $response = array(EXT_JSON_READER_TOTAL => $count,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listMonitoringstatus()
    {
        global $toC_Json, $osC_Database;

        $query = "SELECT DISTINCT monitoring_status FROM delta_parameters where 1 = 1 ";

        switch ($_REQUEST ['content_type']) {
            case "plant":
                $query = $query . " and plants_id = " . $_REQUEST ['content_id'];
                break;

            case "line":
                $query = $query . " and lines_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(line) = '" . $_REQUEST ['content_name'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "' ";
                break;

            case "asset":
                $query = $query . " and asset_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(asset) = '" . $_REQUEST ['content_name'] . "' and lower(line) = '" . $_REQUEST ['line'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "'";
                break;

            case "component":
                $query = $query . " and component_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(asset) = '" . $_REQUEST ['asset'] . "' and lower(line) = '" . $_REQUEST ['line'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "' and lower(channel) in (select lower(channel) from delta_sensor where component_id = " . $_REQUEST ['component'] . ")";
                break;

            case "sensor":
                $query = $query . " and sensors_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(asset) = '" . $_REQUEST ['asset'] . "' and lower(line) = '" . $_REQUEST ['line'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "' and lower(channel) = (select lower(channel) from delta_sensor where component_id = " . $_REQUEST ['component'] . " and lower(code) = lower('" . $_REQUEST ['content_name'] . "'))";
                break;
        }

        $Qstate = $osC_Database->query($query);
        $Qstate->execute();

        $records = array();
        $count = 0;

        $records[] = array('id' => -1,
            'label' => 'Monitoring Status');

        while ($Qstate->next()) {

            $records[] = array('id' => $Qstate->value('monitoring_status'),
                'label' => $Qstate->value('monitoring_status'));

            $count++;
        }

        $response = array(EXT_JSON_READER_TOTAL => $count,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listOperatingclass()
    {
        global $toC_Json, $osC_Database;

        $query = "SELECT DISTINCT operating_class FROM delta_parameters where 1 = 1 ";

        switch ($_REQUEST ['content_type']) {
            case "plant":
                $query = $query . " and plants_id = " . $_REQUEST ['content_id'];
                break;

            case "line":
                $query = $query . " and lines_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(line) = '" . $_REQUEST ['content_name'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "' ";
                break;

            case "asset":
                $query = $query . " and asset_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(asset) = '" . $_REQUEST ['content_name'] . "' and lower(line) = '" . $_REQUEST ['line'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "'";
                break;

            case "component":
                $query = $query . " and component_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(asset) = '" . $_REQUEST ['asset'] . "' and lower(line) = '" . $_REQUEST ['line'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "' and lower(channel) in (select lower(channel) from delta_sensor where component_id = " . $_REQUEST ['component'] . ")";
                break;

            case "sensor":
                $query = $query . " and sensors_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(asset) = '" . $_REQUEST ['asset'] . "' and lower(line) = '" . $_REQUEST ['line'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "' and lower(channel) = (select lower(channel) from delta_sensor where component_id = " . $_REQUEST ['component'] . " and lower(code) = lower('" . $_REQUEST ['content_name'] . "'))";
                break;
        }

        $Qstate = $osC_Database->query($query);
        $Qstate->execute();

        $records = array();
        $count = 0;

        $records[] = array('id' => -1,
            'label' => 'Operating Class');

        while ($Qstate->next()) {

            $records[] = array('id' => $Qstate->value('operating_class'),
                'label' => $Qstate->value('operating_class'));

            $count++;
        }

        $response = array(EXT_JSON_READER_TOTAL => $count,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listXmlfile()
    {
        global $toC_Json, $osC_Database;

        $query = "SELECT DISTINCT xmlfile FROM delta_parameters where 1 = 1 ";

        switch ($_REQUEST ['content_type']) {
            case "plant":
                $query = $query . " and plants_id = " . $_REQUEST ['content_id'];
                break;

            case "line":
                $query = $query . " and lines_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(line) = '" . $_REQUEST ['content_name'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "' ";
                break;

            case "asset":
                $query = $query . " and asset_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(asset) = '" . $_REQUEST ['content_name'] . "' and lower(line) = '" . $_REQUEST ['line'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "'";
                break;

            case "component":
                $query = $query . " and component_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(asset) = '" . $_REQUEST ['asset'] . "' and lower(line) = '" . $_REQUEST ['line'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "' and lower(channel) in (select lower(channel) from delta_sensor where component_id = " . $_REQUEST ['component'] . ")";
                break;

            case "sensor":
                $query = $query . " and sensors_id = " . $_REQUEST ['content_id'];
                //$query = $query . " and lower(asset) = '" . $_REQUEST ['asset'] . "' and lower(line) = '" . $_REQUEST ['line'] . "' and lower(plant) = '" . $_REQUEST ['plant'] . "' and lower(channel) = (select lower(channel) from delta_sensor where component_id = " . $_REQUEST ['component'] . " and lower(code) = lower('" . $_REQUEST ['content_name'] . "'))";
                break;
        }

        $Qstate = $osC_Database->query($query);
        $Qstate->execute();

        $records = array();
        $count = 0;

        $records[] = array('id' => -1,'label' => 'XML File');

        while ($Qstate->next()) {

            $records[] = array('id' => $Qstate->value('xmlfile'),
                'label' => $Qstate->value('xmlfile'));

            $count++;
        }

        $response = array(EXT_JSON_READER_TOTAL => $count,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listMap()
    {
        global $toC_Json, $osC_Database;

        if ($_REQUEST['customers_id'] != "0") {
            $query = "SELECT c.customers_surname,cd.categories_name, p.latitude, p.longitude,(select status from :table_status where content_type = 'plant' and content_id = p.plants_id ) as monitoring_status FROM :table_categories_description cd INNER JOIN :table_plants p ON ( cd.categories_id = p.plants_id ) inner join :table_customers c on (p.customers_id = c.customers_id) WHERE p.customers_id = :customers_id";
            $Qmap = $osC_Database->query($query);
            $Qmap->bindInt(':customers_id', $_REQUEST['customers_id']);
        } else {
            $query = "SELECT c.customers_surname,cd.categories_name, p.latitude, p.longitude,p.plants_id,(select status from :table_status where content_type = 'plant' and content_id = p.plants_id) as monitoring_status FROM :table_categories_description cd INNER JOIN :table_plants p ON ( cd.categories_id = p.plants_id ) inner join :table_customers c on (p.customers_id = c.customers_id)";
            $Qmap = $osC_Database->query($query);
        }

        $Qmap->bindTable(':table_categories_description', TABLE_LAYOUT_DESCRIPTION);
        $Qmap->bindTable(':table_plants', TABLE_PLANTS);
        $Qmap->bindTable(':table_status', TABLE_STATUS);
        $Qmap->bindTable(':table_customers', TABLE_CUSTOMERS);
        $Qmap->execute();

        $records = array();
        $count = 0;

        while ($Qmap->next()) {

            $url = $Qmap->value('monitoring_status') ? "templates/default/images/icons/16x16/status_" . strtolower($Qmap->value('monitoring_status')) . ".png" : "templates/default/images/icons/16x16/status_error.png";

            $records[] = array('title' => $Qmap->value('customers_surname') . " > " . $Qmap->value('categories_name'),
                'url' => $url,
                'latitude' => $Qmap->value('latitude'),
                'plants_id' => $Qmap->valueInt('plants_id'),
                'monitoring_status' => $Qmap->value('monitoring_status'),
                'longitude' => $Qmap->value('longitude'));

            $count++;
        }

        $response = array(EXT_JSON_READER_TOTAL => $count,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function deleteCategory()
    {
        global $toC_Json, $osC_Language, $osC_Image, $osC_CategoryTree;

        $osC_Image = new osC_Image_Admin();
        $osC_CategoryTree = new osC_CategoryTree_Admin();

        if (isset($_REQUEST['categories_id']) && is_numeric($_REQUEST['categories_id']) && osC_Categories_Admin::delete($_REQUEST['categories_id'])) {
            $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
            $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        }

        echo $toC_Json->encode($response);
    }

    function deleteComponent()
    {
        global $toC_Json, $osC_Language;

        if (!isset($_REQUEST['component_id'])) {
            $response = array('success' => false, 'feedback' => "Vous devez selectionner un Component");
            echo $toC_Json->encode($response);
        }
        {
            if (isset($_REQUEST['component_id']) && is_numeric($_REQUEST['component_id']) && osC_Categories_Admin::deleteComponent($_REQUEST['component_id'])) {
                $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
            } else {
                $response = array('success' => false, 'feedback' => $_SESSION['error']);
            }

            echo $toC_Json->encode($response);
        }
    }

    function deletePlant()
    {
        global $toC_Json, $osC_Language;

        if (!isset($_REQUEST['plants_id'])) {
            $response = array('success' => false, 'feedback' => "Vous devez selectionner une Usine");
            echo $toC_Json->encode($response);
        }
        {
            if (isset($_REQUEST['plants_id']) && is_numeric($_REQUEST['plants_id']) && osC_Categories_Admin::deletePlan($_REQUEST['plants_id'])) {
                $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
            } else {
                $response = array('success' => false, 'feedback' => $_SESSION['error']);
            }

            echo $toC_Json->encode($response);
        }
    }

    function deleteSensor()
    {
        global $toC_Json, $osC_Language;

        if (!isset($_REQUEST['sensors_id'])) {
            $response = array('success' => false, 'feedback' => "Vous devez selectionner un Sensor");
            echo $toC_Json->encode($response);
        }
        {
            if (isset($_REQUEST['sensors_id']) && is_numeric($_REQUEST['sensors_id']) && osC_Categories_Admin::deleteSensor($_REQUEST['sensors_id'])) {
                $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
            } else {
                $response = array('success' => false, 'feedback' => $_SESSION['error']);
            }

            echo $toC_Json->encode($response);
        }
    }

    function listCpmstypes()
    {
        global $toC_Json;

        $records = array();

        $records[] = array('types_id' => '0', 'label' => 'cRIO');
        $records[] = array('types_id' => '1', 'label' => 'cDAQ');

        $response = array(EXT_JSON_READER_TOTAL => 2,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listCpmsSlot()
    {
        global $toC_Json;

        $records = array();

        $records[] = array('slot_id' => '0', 'label' => 'Mod1');
        $records[] = array('slot_id' => '1', 'label' => 'Mod2');
        $records[] = array('slot_id' => '2', 'label' => 'Mod3');
        $records[] = array('slot_id' => '3', 'label' => 'Mod4');
        $records[] = array('slot_id' => '4', 'label' => 'Mod5');
        $records[] = array('slot_id' => '5', 'label' => 'Mod6');
        $records[] = array('slot_id' => '6', 'label' => 'Mod7');
        $records[] = array('slot_id' => '7', 'label' => 'Mod8');
        $records[] = array('slot_id' => '8', 'label' => 'Mod9');
        $records[] = array('slot_id' => '9', 'label' => 'Mod10');
        $records[] = array('slot_id' => '10', 'label' => 'Mod11');
        $records[] = array('slot_id' => '11', 'label' => 'Mod12');
        $records[] = array('slot_id' => '12', 'label' => 'Mod13');
        $records[] = array('slot_id' => '13', 'label' => 'Mod14');

        $response = array(EXT_JSON_READER_TOTAL => 14,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listSampling()
    {
        global $toC_Json;

        $records = array();

        $records[] = array('id' => '0', 'label' => '102400');
        $records[] = array('id' => '1', 'label' => '51200');
        $records[] = array('id' => '2', 'label' => '25600');
        $records[] = array('id' => '3', 'label' => '12800');
        $records[] = array('id' => '4', 'label' => '10240');
        $records[] = array('id' => '5', 'label' => '5120');
        $records[] = array('id' => '6', 'label' => '2560');

        $response = array(EXT_JSON_READER_TOTAL => 7,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listEquipmentype()
    {
        global $toC_Json;

        $records = array();

        $records[] = array('types_id' => '0', 'label' => 'Pumpe');
        $records[] = array('types_id' => '1', 'label' => 'Motor');
        $records[] = array('types_id' => '2', 'label' => 'Gasturbine');
        $records[] = array('types_id' => '3', 'label' => 'Dampfturbine');
        $records[] = array('types_id' => '4', 'label' => 'Generator');
        $records[] = array('types_id' => '5', 'label' => 'Compressor');
        $records[] = array('types_id' => '6', 'label' => 'Fan');
        $records[] = array('types_id' => '7', 'label' => 'WEA');
        $records[] = array('types_id' => '8', 'label' => 'Autre (à completer...)');

        $response = array(EXT_JSON_READER_TOTAL => 9,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listConfiguration()
    {
        global $toC_Json;

        $records = array();

        $records[] = array('configuration_id' => '0', 'label' => 'Direct');
        $records[] = array('configuration_id' => '1', 'label' => 'Belt');
        $records[] = array('configuration_id' => '2', 'label' => 'Shaft');
        $records[] = array('configuration_id' => '3', 'label' => 'Driven');

        $response = array(EXT_JSON_READER_TOTAL => 4,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listRotdir()
    {
        global $toC_Json;

        $records = array();

        $records[] = array('index' => '1', 'label' => 'CW');
        $records[] = array('index' => '2', 'label' => 'CCW');

        $response = array(EXT_JSON_READER_TOTAL => 2,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listPercent()
    {
        global $toC_Json;

        $records = array();

        $i = 1;

        while ($i <= 100) {
            $records[] = array('index' => $i, 'label' => $i . '%');
            $i++;
        }

        $response = array(EXT_JSON_READER_TOTAL => 100,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listPower()
    {
        global $toC_Json;

        $records = array();

        $records[] = array('sources_id' => '0', 'label' => 'electric');
        $records[] = array('sources_id' => '1', 'label' => 'steam');
        $records[] = array('sources_id' => '2', 'label' => 'gas');
        $records[] = array('sources_id' => '3', 'label' => 'reciprocating');
        $records[] = array('sources_id' => '4', 'label' => 'internal combustion');
        $records[] = array('sources_id' => '5', 'label' => 'diesel');
        $records[] = array('sources_id' => '6', 'label' => 'hydraulic');

        $response = array(EXT_JSON_READER_TOTAL => 7,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listReclength()
    {
        global $toC_Json;

        $records = array();

        $records[] = array('id' => '4', 'label' => '4');
        $records[] = array('id' => '8', 'label' => '8');
        $records[] = array('id' => '12', 'label' => '12');
        $records[] = array('id' => '16', 'label' => '16');
        $records[] = array('id' => '20', 'label' => '20');
        $records[] = array('id' => '24', 'label' => '24');
        $records[] = array('id' => '28', 'label' => '28');

        $response = array(EXT_JSON_READER_TOTAL => 7,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listGeartype()
    {
        global $toC_Json;

        $records = array();

        $records[] = array('id' => 'helical', 'label' => 'helical');
        $records[] = array('id' => 'helical', 'label' => 'planetary');

        $response = array(EXT_JSON_READER_TOTAL => 2,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listEu()
    {
        global $toC_Json;

        $records = array();

        $records[] = array('id' => '0', 'label' => 'µm');
        $records[] = array('id' => '1', 'label' => 'mm');
        $records[] = array('id' => '2', 'label' => 'm');
        $records[] = array('id' => '3', 'label' => 'g');
        $records[] = array('id' => '4', 'label' => 'm/s^2');
        $records[] = array('id' => '5', 'label' => 'mm/s');
        $records[] = array('id' => '6', 'label' => '°C');
        $records[] = array('id' => '7', 'label' => 'm^3/s');

        $response = array(EXT_JSON_READER_TOTAL => 8,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listWindowlength()
    {
        global $toC_Json;

        $records = array();

        $records[] = array('id' => '0', 'label' => '4');
        $records[] = array('id' => '1', 'label' => '8');
        $records[] = array('id' => '2', 'label' => '12');
        $records[] = array('id' => '3', 'label' => '16');
        $records[] = array('id' => '4', 'label' => '20');

        $response = array(EXT_JSON_READER_TOTAL => 5,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listWindowfft()
    {
        global $toC_Json;

        $records = array();

        $records[] = array('id' => '0', 'label' => 'Rechteck');
        $records[] = array('id' => '1', 'label' => 'Hanning');
        $records[] = array('id' => '2', 'label' => 'Flat Top');

        $response = array(EXT_JSON_READER_TOTAL => 3,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listOverlap()
    {
        global $toC_Json;

        $records = array();

        $records[] = array('id' => '0', 'label' => '0%');
        $records[] = array('id' => '1', 'label' => '25%');
        $records[] = array('id' => '2', 'label' => '50%');
        $records[] = array('id' => '3', 'label' => '75%');
        $records[] = array('id' => '4', 'label' => '90%');

        $response = array(EXT_JSON_READER_TOTAL => 5,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listAverage()
    {
        global $toC_Json;

        $records = array();

        $records[] = array('id' => '0', 'label' => 'None');
        $records[] = array('id' => '1', 'label' => 'Linear');
        $records[] = array('id' => '2', 'label' => 'PeakHold');
        $records[] = array('id' => '3', 'label' => 'RMS');

        $response = array(EXT_JSON_READER_TOTAL => 4,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listSample()
    {
        global $toC_Json;

        $records = array();

        $records[] = array('id' => '0', 'label' => '512');
        $records[] = array('id' => '1', 'label' => '1024');
        $records[] = array('id' => '2', 'label' => '2048');
        $records[] = array('id' => '3', 'label' => '4096');
        $records[] = array('id' => '4', 'label' => '8192');
        $records[] = array('id' => '5', 'label' => '16384');

        $response = array(EXT_JSON_READER_TOTAL => 6,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listEnvelope()
    {
        global $toC_Json;

        $records = array();

        $records[] = array('id' => '0', 'label' => 'Integrated');
        $records[] = array('id' => '1', 'label' => 'Gleichrichtung');
        $records[] = array('id' => '2', 'label' => 'Hilbert');

        $response = array(EXT_JSON_READER_TOTAL => 3,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listEsa()
    {
        global $toC_Json;

        $records = array();

        $records[] = array('id' => '0', 'label' => 'None');
        $records[] = array('id' => '1', 'label' => 'U1');
        $records[] = array('id' => '2', 'label' => 'U2');
        $records[] = array('id' => '3', 'label' => 'U3');
        $records[] = array('id' => '4', 'label' => 'I1');
        $records[] = array('id' => '5', 'label' => 'I2');
        $records[] = array('id' => '6', 'label' => 'I3');

        $response = array(EXT_JSON_READER_TOTAL => 4,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listTacho()
    {
        global $toC_Json;

        $records = array();

        $records[] = array('channel' => 'None', 'label' => 'None');
        $records[] = array('channel' => 'Ch000', 'label' => 'Ch000');
        $records[] = array('channel' => 'Ch001', 'label' => 'Ch001');
        $records[] = array('channel' => 'Ch002', 'label' => 'Ch002');
        $records[] = array('channel' => 'Ch003', 'label' => 'Ch003');
        $records[] = array('channel' => 'Ch004', 'label' => 'Ch004');
        $records[] = array('channel' => 'Ch005', 'label' => 'Ch005');
        $records[] = array('channel' => 'Ch006', 'label' => 'Ch006');
        $records[] = array('channel' => 'Ch007', 'label' => 'Ch007');
        $records[] = array('channel' => 'Ch008', 'label' => 'Ch008');
        $records[] = array('channel' => 'Ch009', 'label' => 'Ch009');
        $records[] = array('channel' => 'Ch010', 'label' => 'Ch010');
        $records[] = array('channel' => 'Ch011', 'label' => 'Ch011');
        $records[] = array('channel' => 'Ch012', 'label' => 'Ch012');
        $records[] = array('channel' => 'Ch013', 'label' => 'Ch013');
        $records[] = array('channel' => 'Ch014', 'label' => 'Ch014');
        $records[] = array('channel' => 'Ch015', 'label' => 'Ch015');
        $records[] = array('channel' => 'Ch016', 'label' => 'Ch016');
        $records[] = array('channel' => 'Ch017', 'label' => 'Ch017');
        $records[] = array('channel' => 'Ch018', 'label' => 'Ch018');
        $records[] = array('channel' => 'Ch019', 'label' => 'Ch019');
        $records[] = array('channel' => 'Ch020', 'label' => 'Ch020');
        $records[] = array('channel' => 'Ch021', 'label' => 'Ch021');
        $records[] = array('channel' => 'Ch022', 'label' => 'Ch022');
        $records[] = array('channel' => 'Ch023', 'label' => 'Ch023');
        $records[] = array('channel' => 'Ch024', 'label' => 'Ch024');
        $records[] = array('channel' => 'Ch025', 'label' => 'Ch025');
        $records[] = array('channel' => 'Ch026', 'label' => 'Ch026');
        $records[] = array('channel' => 'Ch027', 'label' => 'Ch027');
        $records[] = array('channel' => 'Ch028', 'label' => 'Ch028');
        $records[] = array('channel' => 'Ch029', 'label' => 'Ch029');
        $records[] = array('channel' => 'Ch030', 'label' => 'Ch030');
        $records[] = array('channel' => 'Ch031', 'label' => 'Ch031');
        $records[] = array('channel' => 'Ch032', 'label' => 'Ch032');
        $records[] = array('channel' => 'Ch033', 'label' => 'Ch033');
        $records[] = array('channel' => 'Ch034', 'label' => 'Ch034');
        $records[] = array('channel' => 'Ch035', 'label' => 'Ch035');
        $records[] = array('channel' => 'Ch036', 'label' => 'Ch036');
        $records[] = array('channel' => 'Ch037', 'label' => 'Ch037');
        $records[] = array('channel' => 'Ch038', 'label' => 'Ch038');
        $records[] = array('channel' => 'Ch039', 'label' => 'Ch039');
        $records[] = array('channel' => 'Ch040', 'label' => 'Ch040');
        $records[] = array('channel' => 'Ch041', 'label' => 'Ch041');
        $records[] = array('channel' => 'Ch042', 'label' => 'Ch042');
        $records[] = array('channel' => 'Ch043', 'label' => 'Ch043');
        $records[] = array('channel' => 'Ch044', 'label' => 'Ch044');
        $records[] = array('channel' => 'Ch045', 'label' => 'Ch045');
        $records[] = array('channel' => 'Ch046', 'label' => 'Ch046');
        $records[] = array('channel' => 'Ch047', 'label' => 'Ch047');
        $records[] = array('channel' => 'Ch048', 'label' => 'Ch048');
        $records[] = array('channel' => 'Ch049', 'label' => 'Ch049');
        $records[] = array('channel' => 'Ch050', 'label' => 'Ch050');
        $records[] = array('channel' => 'Ch051', 'label' => 'Ch051');
        $records[] = array('channel' => 'Ch052', 'label' => 'Ch052');
        $records[] = array('channel' => 'Ch053', 'label' => 'Ch053');
        $records[] = array('channel' => 'Ch054', 'label' => 'Ch054');
        $records[] = array('channel' => 'Ch055', 'label' => 'Ch055');
        $records[] = array('channel' => 'Ch056', 'label' => 'Ch056');
        $records[] = array('channel' => 'Ch057', 'label' => 'Ch057');
        $records[] = array('channel' => 'Ch058', 'label' => 'Ch058');
        $records[] = array('channel' => 'Ch059', 'label' => 'Ch059');
        $records[] = array('channel' => 'Ch060', 'label' => 'Ch060');
        $records[] = array('channel' => 'Ch061', 'label' => 'Ch061');
        $records[] = array('channel' => 'Ch062', 'label' => 'Ch062');
        $records[] = array('channel' => 'Ch063', 'label' => 'Ch063');

        $response = array(EXT_JSON_READER_TOTAL => 64,
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function deleteLine()
    {
        global $toC_Json, $osC_Language;

        if (!isset($_REQUEST['lines_id'])) {
            $response = array('success' => false, 'feedback' => "Vous devez selectionner une Ligne");
            echo $toC_Json->encode($response);
        }
        {
            if (isset($_REQUEST['lines_id']) && is_numeric($_REQUEST['lines_id']) && osC_Categories_Admin::deleteLine($_REQUEST['lines_id'])) {
                $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
            } else {
                $response = array('success' => false, 'feedback' => $_SESSION['error']);
            }

            echo $toC_Json->encode($response);
        }
    }

    function deleteAsset()
    {
        global $toC_Json, $osC_Language;

        if (!isset($_REQUEST['asset_id'])) {
            $response = array('success' => false, 'feedback' => "Vous devez selectionner un Asset");
            echo $toC_Json->encode($response);
        }
        {
            if (isset($_REQUEST['asset_id']) && is_numeric($_REQUEST['asset_id']) && osC_Categories_Admin::deleteAsset($_REQUEST['asset_id'])) {
                $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
            } else {
                $response = array('success' => false, 'feedback' => $_SESSION['error']);
            }

            echo $toC_Json->encode($response);
        }
    }

    function exportAsset()
    {
        global $toC_Json, $osC_Database;

        if (!isset($_REQUEST['asset_id'])) {
            $response = array('success' => false, 'feedback' => "Vous devez selectionner un Asset");
            echo $toC_Json->encode($response);
        } else {
            if (!isset($_REQUEST['asset_name'])) {
                $response = array('success' => false, 'feedback' => "Nom Asset non specifié !!!");
                echo $toC_Json->encode($response);
            } else {
                $csv_export = "";
                $csv_filename = 'export_' . $_REQUEST['asset_name'] . '_' . date('Y-m-d') . '.csv';
                $csv_export = $csv_export . "SensorCode;SigName;CPMSChannel;CPMSSlot;CPMS_IP;Sampling Freq Hz;Record Length s;Sensitivity;SensitivityUnit;engineering_unit;Offset EU;timeanalysis;orbitanalysis;fftanalysis;orderanalysis;envelopeanalysis;windowlength;windowfft;overlap;average;samples_per_rev;Orbit_ChY;FRF_ChY;Env_Type;BPFu_Env;BPFo_Env;TPF_Env;ESA_Channel;Measurement range EU;Frequency range;Temperature range;Impdeance;Calibration date;Manufacturer;Serial No.;Component;Location;Code;Angle;Orientation;Motion;Attachment method;JunctionBox;AcquisitionStation" . "\n";
                //$csv_export .= '';

                $query = "select code,signalname,channel,cpmsslot,cpms_ip,sampling_freq,record_length,sensitivity,sensitivity_unit,engineering_unit,offset,time_analysis,orbit_analysis,fftanalysis,order_analysis,envelope_analysis,window_length_s,window_fft,overlap,average,sample_rev,orbit_channel_y,frfchannel_y,envelopetype,bpfu_env,bpfo_env,tpf_env,esa_channel,measurement_range,frequency_range,temperaturerange,impedance,calibration_date,manufacturer,serial_number,component,'' as location,sensortypecode,angle,orientation,motion,attachment_method,jonction_box,acquisitionstation from :table_sensor where component_id in (select component_id from :table_components where asset_id = :asset_id)" . "\n";

                $Qexport = $osC_Database->query($query);
                $Qexport->bindTable(':table_sensor', TABLE_SENSOR);
                $Qexport->bindTable(':table_components', TABLE_COMPONENT);
                $Qexport->bindInt(':asset_id', $_REQUEST['asset_id']);
                $Qexport->execute();

                while ($Qexport->next()) {
                    $csv_export = $csv_export . $Qexport->value('code') . ";" . $Qexport->value('signalname') . ";" . $Qexport->value('channel') . ";" . $Qexport->value('cpmsslot') . ";" . $Qexport->value('cpms_ip') . ";" . $Qexport->value('sampling_freq') . ";" . $Qexport->value('record_length') . ";" . $Qexport->value('sensitivity') . ";" . $Qexport->value('sensitivity_unit') . ";" . $Qexport->value('engineering_unit') . ";" . $Qexport->value('offset') . ";" . $Qexport->value('time_analysis') . ";" . $Qexport->value('orbit_analysis') . ";" . $Qexport->value('fftanalysis') . ";" . $Qexport->value('order_analysis') . ";" . $Qexport->value('envelope_analysis') . ";" . $Qexport->value('window_length_s') . ";" . $Qexport->value('window_fft') . ";" . $Qexport->value('overlap') . ";" . $Qexport->value('average') . ";" . $Qexport->value('sample_rev') . ";" . $Qexport->value('orbit_channel_y') . ";" . $Qexport->value('frfchannel_y') . ";" . $Qexport->value('envelopetype') . ";" . $Qexport->value('bpfu_env') . ";" . $Qexport->value('bpfo_env') . ";" . $Qexport->value('tpf_env') . ";" . $Qexport->value('esa_channel') . ";" . $Qexport->value('measurement_range') . ";" . $Qexport->value('frequency_range') . ";" . $Qexport->value('temperaturerange') . ";" . $Qexport->value('impedance') . ";" . $Qexport->value('calibration_date') . ";" . $Qexport->value('manufacturer') . ";" . $Qexport->value('serial_number') . ";" . $Qexport->value('component') . ";" . $Qexport->value('location') . ";" . $Qexport->value('sensortypecode') . ";" . $Qexport->value('angle') . ";" . $Qexport->value('orientation') . ";" . $Qexport->value('motion') . ";" . $Qexport->value('attachment_method') . ";" . $Qexport->value('jonction_box') . ";" . $Qexport->value('acquisitionstation') . "\n";

                    //$csv_export .= '';
                }

                $file_name = DIR_FS_CATALOG . '/exports/' . $csv_filename;

                //print_r($file_name);

                $b = file_put_contents($file_name, $csv_export);

                if ($b > 0) {
                    $event['content_id'] = $_REQUEST['asset_id'];
                    $event['content_type'] = "asset";
                    $event['event_date'] = date('Y-m-d H:i:s');
                    $event['type'] = "info";
                    $event['source'] = "cloud";
                    $event['user'] = $_SESSION['admin']['username'];
                    $event['category'] = "asset";
                    $event['description'] = "L'asset " . $_REQUEST['asset_name'] . " a ete exporte par . " . $_SESSION['admin']['username'];

                    osC_Categories_Admin::logEvent($event);

                    $response = array('success' => true, 'file_name' => HTTP_SERVER . '/exports/' . $csv_filename);
                } else {

                    $event['content_id'] = $_REQUEST['asset_id'];
                    $event['content_type'] = "asset";
                    $event['event_date'] = date('Y-m-d H:i:s');
                    $event['type'] = "error";
                    $event['source'] = "cloud";
                    $event['user'] = $_SESSION['admin']['username'];
                    $event['category'] = "asset";
                    $event['description'] = "L'asset " . $_REQUEST['asset_name'] . " n'a pas pu etre exporte par . " . $_SESSION['admin']['username'];

                    osC_Categories_Admin::logEvent($event);

                    $response = array('success' => false, 'file_name' => '', 'feedback' => "Impossible de creer le fichier d'export !!! Veuillez contacter l'administrateur");
                }
            }
        }
        echo $toC_Json->encode($response);
    }

    function exportCustomer()
    {
        global $toC_Json, $osC_Database;

        if (!isset($_REQUEST['customers_id'])) {
            $response = array('success' => false, 'feedback' => "Vous devez selectionner un Client");
            echo $toC_Json->encode($response);
        } else {
            if (!isset($_REQUEST['customers_name'])) {
                $response = array('success' => false, 'feedback' => "Nom Client non specifié !!!");
                echo $toC_Json->encode($response);
            } else {
                $csv_export = "";
                $csv_filename = 'export_' . $_REQUEST['customers_name'] . '_' . date('Y-m-d') . '.csv';
                $csv_export = $csv_export . "PlantName;LineName;AssetName;CPMS_IP" . "\n";

                $query = "select cd.categories_name as AssetName,a.cpms_ip,(select name from :table_lines where lines_id = a.lines_id) as LineName,(select categories_name from :table_layout_description where categories_id = (select parent_id from delta_layout where categories_id = a.lines_id)) as PlantName from delta_layout c left join delta_layout_description cd on c.categories_id = cd.categories_id inner join :table_asset a on c.categories_id = a.asset_id where a.lines_id in (select categories_id from delta_layout where parent_id in (select categories_id from delta_layout where parent_id = :customers_id))";

                $Qexport = $osC_Database->query($query);
                $Qexport->bindTable(':table_layout', TABLE_LAYOUT);
                $Qexport->bindTable(':table_asset', TABLE_ASSET);
                $Qexport->bindTable(':table_layout_description', TABLE_LAYOUT_DESCRIPTION);
                $Qexport->bindTable(':table_lines', TABLE_LINES);
                $Qexport->bindInt(':customers_id', $_REQUEST['customers_id']);
                $Qexport->execute();

                //var_dump($Qexport);

                while ($Qexport->next()) {
                    $csv_export = $csv_export . $Qexport->value('PlantName') . ";" . $Qexport->value('LineName') . ";" . $Qexport->value('AssetName') . ";" . $Qexport->value('cpms_ip') . "\n";
                }

                $file_name = DIR_FS_CATALOG . '/exports/' . $csv_filename;

                //print_r($file_name);

                $b = file_put_contents($file_name, $csv_export);

                if ($b > 0) {
                    $event['content_id'] = $_REQUEST['customers_id'];
                    $event['content_type'] = "customers";
                    $event['event_date'] = date('Y-m-d H:i:s');
                    $event['type'] = "info";
                    $event['source'] = "cloud";
                    $event['user'] = $_SESSION['admin']['username'];
                    $event['category'] = "asset";
                    $event['description'] = "Les donnees du client " . $_REQUEST['customers_name'] . " ont ete exportes par . " . $_SESSION['admin']['username'];

                    osC_Categories_Admin::logEvent($event);

                    $response = array('success' => true, 'file_name' => HTTP_SERVER . '/exports/' . $csv_filename);
                } else {
                    $event['content_id'] = $_REQUEST['customers_id'];
                    $event['content_type'] = "customers";
                    $event['event_date'] = date('Y-m-d H:i:s');
                    $event['type'] = "error";
                    $event['source'] = "cloud";
                    $event['user'] = $_SESSION['admin']['username'];
                    $event['category'] = "asset";
                    $event['description'] = "Les donnees du client " . $_REQUEST['customers_name'] . " n'ont pas pu etre exportes par . " . $_SESSION['admin']['username'];

                    osC_Categories_Admin::logEvent($event);

                    $response = array('success' => false, 'file_name' => '', 'feedback' => "Impossible de creer le fichier d'export !!! Veuillez contacter l'administrateur");
                }
            }
        }
        echo $toC_Json->encode($response);
    }

    function deleteCategories()
    {
        global $toC_Json, $osC_Language, $osC_Image, $osC_CategoryTree;

        $osC_Image = new osC_Image_Admin();
        $osC_CategoryTree = new osC_CategoryTree_Admin();

        $error = false;

        $batch = explode(',', $_REQUEST['batch']);
        foreach ($batch as $id) {
            if (!osC_Categories_Admin::delete($id)) {
                $error = true;
                break;
            }
        }

        if ($error === false) {
            $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
            $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        }

        echo $toC_Json->encode($response);
    }

    function getMap()
    {
        global $toC_Json;

        $address = $_REQUEST['adresse'];
        if (!empty($address)) {
            //Formatted address
            $formattedAddr = str_replace(' ', '+', $address);
            //Send request and receive json data by address
            $geocodeFromAddr = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address=' . $formattedAddr . '&sensor=false&key=AIzaSyC8R26NYmYWExN4y0lcGSlaKmFA5vi_VJ0');

            $output = json_decode($geocodeFromAddr);
            //Get latitude and longitute from json data
            //var_dump($output);
            if (isset($output->results[0])) {

                $data['latitude'] = $output->results[0]->geometry->location->lat;
                $data['longitude'] = $output->results[0]->geometry->location->lng;
                $data['formatted_address'] = $output->results[0]->formatted_address;
                //Return latitude and longitude of the given address
                $response = array('success' => true, 'feedback' => "", 'formatted_address' => $data['formatted_address'], 'latitude' => $data['latitude'], 'longitude' => $data['longitude']);
            } else {
                $response = array('success' => false, 'feedback' => "La position geographique de l'adresse " . $formattedAddr . " n'a pu etre determinee");
            }
        } else {
            $response = array('success' => false, 'feedback' => "Veuillez renseigner une adresse afin de determiner la position geographique");
        }

        echo $toC_Json->encode($response);
    }

    function getStatus()
    {
        global $osC_Database, $toC_Json;

        $cls = 'templates/default/images/icons/16x16/' . strtolower($_REQUEST['content_type']) . '_error.png';

        $response = array('success' => true, 'feedback' => "", 'cls' => $cls);

        $query = "select status from :table_status where content_type = :content_type and content_id = :content_id";

        $Qstatus = $osC_Database->query($query);
        $Qstatus->bindTable(':table_status', TABLE_STATUS);
        $Qstatus->bindInt(':content_id', $_REQUEST['content_id']);
        $Qstatus->bindValue(':content_type', $_REQUEST['content_type']);
        $Qstatus->execute();

        if ($osC_Database->isError()) {
            $response = array('success' => false, 'feedback' => $osC_Database->getError(), 'cls' => '');
        } else {
            while ($Qstatus->next()) {
                $status = $Qstatus->value('status');

                $cls = 'templates/default/images/icons/16x16/' . strtolower($_REQUEST['content_type']) . '_' . strtolower($status) . '.png';

                $response = array('success' => true, 'feedback' => $Qstatus->value('status'), 'cls' => $cls);
            }
        }

        //$Qstatus->freeResult();

        echo $toC_Json->encode($response);
    }

    function importParameters()
    {
        $error = false;

        $year = date("Y");
        $month = date('m');
        $t = date('d-m-Y');
        $day = strtolower(date("d", strtotime($t)));

        $dir = realpath(DIR_FS_PARAMETERS);
        $arch = realpath(DIR_FS_ARCHIVES) . "/" . $year . "/" . $month . "/" . $day . "/";

        if (!file_exists($arch)) {
            mkdir($arch, 0777, true);
        }

        $err = realpath(DIR_FS_ERRORS) . "/" . $year . "/" . $month . "/" . $day . "/";
        if (!file_exists($arch)) {
            mkdir($err, 0777, true);
        }

        $cdir = scandir($dir);
        //print_r($cdir);

        $arr = array();
        foreach ($cdir as $filename) {
            if ($filename != '.' && $filename != '..' && $filename != '.htaccess') {
                //if (filemtime($dir . "/" . $filename) === false) return false;
                $dat = date("YmdHis", filemtime($dir . "/" . $filename));
                //print_r($dat);
                $arr[$dat] = $filename;
            }
        }
        ksort($arr);

        //print_r($arr);

        foreach ($arr as $key => $value) {
            if (!in_array($value, array(".", "..", ".htaccess"))) {
                $file = $dir . "/" . $value;
                print_r($file);
                $row = 0;
                if (($handle = fopen($file, "r")) !== FALSE) {
                    while (($data = fgetcsv($handle, 0, "\t")) !== FALSE) {
                        if ($row > 0) {
                            //print_r($file);
                            //print_r($data);

                            $parameter = array();

                            $parameter['eventdate'] = $data[0];
                            $parameter['speedtype'] = $data[1];
                            $parameter['constspeedhz'] = $data[2];
                            $parameter['dopspeedchannel'] = $data[3];
                            $parameter['tachochannel'] = $data[4];
                            $parameter['tachotrigger'] = $data[5];
                            $parameter['pulseperrev'] = $data[6];
                            $parameter['rotdirection'] = $data[7];
                            $parameter['pretriglength'] = $data[8];
                            $parameter['recordlength'] = $data[9];
                            $parameter['cyclicevent'] = $data[10];
                            $parameter['statuschangeevent'] = $data[11];
                            $parameter['oschangeevent'] = $data[12];
                            $parameter['cyclictime'] = $data[13];
                            $parameter['cyclictimebyal'] = $data[14];
                            $parameter['dailytrigtime_hh'] = $data[15];
                            $parameter['dailytrigtime_mm'] = $data[16];
                            $parameter['dailytrigtime_ss'] = $data[17];
                            $parameter['plant'] = $data[18];
                            $parameter['line'] = $data[19];
                            $parameter['asset'] = $data[20];
                            $parameter['cpms_ip'] = $data[21];
                            $parameter['configurator'] = $data[22];
                            $parameter['monitoring'] = $data[23];
                            $parameter['monitoringtime_ms'] = $data[24];
                            $parameter['chfifosize'] = $data[25];
                            $parameter['totalfifosize'] = $data[26];
                            $parameter['samplingrate'] = $data[27];
                            $parameter['staticchannels'] = $data[28];
                            $parameter['sampeperch'] = $data[29];
                            $parameter['dynamicchannels'] = $data[30];
                            $parameter['record_date_time'] = $data[31];
                            $parameter['event_date_time'] = $data[32];
                            $parameter['event_trigger_type'] = $data[33];
                            $parameter['monitoring_status'] = $data[34];
                            $parameter['measurement_state'] = $data[35];
                            $parameter['operating_class'] = $data[36];
                            $parameter['xmlfile'] = $data[37];
                            $parameter['acrms_status'] = $data[38];
                            $parameter['lfrms_status'] = $data[39];
                            $parameter['isorms_status'] = $data[40];
                            $parameter['hfrms_status'] = $data[41];
                            $parameter['acpeak_status'] = $data[42];
                            $parameter['accrest_status'] = $data[43];
                            $parameter['mean_status'] = $data[44];
                            $parameter['peak2peak_status'] = $data[45];
                            $parameter['kurtosis_status'] = $data[46];
                            $parameter['smax_status'] = $data[47];
                            $parameter['mp_acrms_value'] = $data[48];
                            $parameter['mp_lfrms_value'] = $data[49];
                            $parameter['mp_isorms_value'] = $data[50];
                            $parameter['mp_hfrms_value'] = $data[51];
                            $parameter['mp_acpeak_value'] = $data[52];
                            $parameter['mp_accrest_value'] = $data[53];
                            $parameter['mp_mean_value'] = $data[54];
                            $parameter['mp_peak2peak_value'] = $data[55];
                            $parameter['mp_kurtosis_value'] = $data[56];
                            $parameter['mp_smax_value'] = $data[57];
                            $parameter['lfrms'] = $data[58];
                            $parameter['isorms'] = $data[58];
                            $parameter['speed'] = $data[59];
                            $parameter['hfrms'] = $data[60];
                            $parameter['crest'] = $data[61];
                            $parameter['peak'] = $data[62];
                            $parameter['rms'] = $data[63];
                            $parameter['max'] = $data[64];
                            $parameter['min'] = $data[65];
                            $parameter['peak2peak'] = $data[66];
                            $parameter['mean'] = $data[67];
                            $parameter['std'] = $data[68];
                            $parameter['kurtosis'] = $data[69];
                            $parameter['skewness'] = $data[70];
                            $parameter['smax'] = $data[71];
                            $parameter['histo'] = $data[72];
                            $parameter['a1x'] = $data[73];
                            $parameter['p1x'] = $data[74];
                            $parameter['a2x'] = $data[75];
                            $parameter['p2x'] = $data[76];
                            $parameter['a3x'] = $data[77];
                            $parameter['p3x'] = $data[78];
                            $parameter['chname'] = $data[79];
                            $parameter['chunit'] = $data[80];
                            $parameter['chstatus'] = $data[81];
                            $parameter['op1'] = $data[82];
                            $parameter['op2'] = $data[83];
                            $parameter['op3'] = isset($data[84]) ? $data[84] : '';
                            $parameter['op4'] = isset($data[85]) ? $data[85] : '';
                            $parameter['op5'] = isset($data[86]) ? $data[86] : '';
                            $parameter['op6'] = isset($data[87]) ? $data[87] : '';
                            $parameter['op7'] = isset($data[88]) ? $data[88] : '';
                            $parameter['op8'] = isset($data[89]) ? $data[89] : '';
                            $parameter['op9'] = isset($data[90]) ? $data[90] : '';
                            $parameter['op10'] = isset($data[91]) ? $data[91] : '';
                            $parameter['file'] = $value;
                            $parameter['id'] = hash_file('md5', $value);

                            $info = explode('_', $value);

                            $parameter['channel'] = $info[5];

                            $config = osC_Categories_Admin::getLayout($parameter['plant'], $parameter['line'], $parameter['asset'], $parameter['channel']);
                            if ($config) {
                                $config['channel'] = $info[5];
                                $config['plant'] = $parameter['plant'];
                                $config['line'] = $parameter['line'];
                                $config['asset'] = $parameter['asset'];
                                $config['cpms_ip'] = $parameter['cpms_ip'];

                                if (!osC_Categories_Admin::saveParameter($parameter, $config)) {
                                    $error = true;
                                    //echo "erreur : " . $_SESSION['error'] . "\n";
                                    //echo "error while importing file " . $file . " Raison : " . $_SESSION['error'];
                                    //print_r($parameter);
                                    //print_r($data);
                                    rename($file, $err . "/" . $value);
                                    unlink($file);
                                    $parameter['status'] = 0;
                                    $parameter['comments'] = $_SESSION['error'];
                                    osC_Categories_Admin::logImport($parameter);
                                } else {
                                    //echo "file " . $file . " Imported...";
                                    $error = false;
                                    echo "succes" . "\n";
                                    rename($file, $arch . "/" . $value);
                                    unlink($file);
                                    $parameter['status'] = 1;
                                    $parameter['comments'] = "";
                                    osC_Categories_Admin::logImport($parameter);
                                    if (!osC_Categories_Admin::updateStatus($config, $parameter['chstatus'], $parameter)) {
                                        //send alert
                                        echo $_SESSION['error'];
                                    }
                                }
                            } else {
                                //echo $_SESSION['error'];
                            }
                            //var_dump($config)
                        }
                        $row++;
                    }

                    fclose($handle);
                } else {
                    echo "no handle for " . $file . "\n";
                }
            }
        }
    }

    function importParams()
    {
        $error = false;

        $year = date("Y");
        $month = date('m');
        $t = date('d-m-Y');
        $day = strtolower(date("d", strtotime($t)));

        $dir = realpath(DIR_FS_PARAMETERS);
        $arch = realpath(DIR_FS_ARCHIVES) . "/" . $year . "/" . $month . "/" . $day . "/";

        if (!file_exists($arch)) {
            mkdir($arch, 0777, true);
        }

        $err = realpath(DIR_FS_ERRORS) . "/" . $year . "/" . $month . "/" . $day . "/";
        if (!file_exists($arch)) {
            mkdir($err, 0777, true);
        }

        $cdir = scandir($dir);
        //print_r($cdir);

        $arr = array();
        foreach ($cdir as $filename) {
            if ($filename != '.' && $filename != '..' && $filename != '.htaccess') {
                //if (filemtime($dir . "/" . $filename) === false) return false;
                $dat = date("YmdHis", filemtime($dir . "/" . $filename));
                //print_r($dat);
                $arr[$dat] = $filename;
            }
        }
        ksort($arr);

        //print_r($arr);

        foreach ($arr as $key => $value) {
            if (!in_array($value, array(".", "..", ".htaccess"))) {
                $file = $dir . "/" . $value;
                $row = 0;
                if (($handle = fopen($file, "r")) !== FALSE) {
                    while (($data = fgetcsv($handle, 0, "\t")) !== FALSE) {
                        if ($row > 0) {
                            //print_r($file);
                            //print_r($data);

                            $parameter = array();

                            $parameter['eventdate'] = $data[0];
                            $parameter['speedtype'] = $data[1];
                            $parameter['constspeedhz'] = $data[2];
                            $parameter['dopspeedchannel'] = $data[3];
                            $parameter['tachochannel'] = $data[4];
                            $parameter['tachotrigger'] = $data[5];
                            $parameter['pulseperrev'] = $data[6];
                            $parameter['rotdirection'] = $data[7];
                            $parameter['pretriglength'] = $data[8];
                            $parameter['recordlength'] = $data[9];
                            $parameter['cyclicevent'] = $data[10];
                            $parameter['statuschangeevent'] = $data[11];
                            $parameter['oschangeevent'] = $data[12];
                            $parameter['cyclictime'] = $data[13];
                            $parameter['cyclictimebyal'] = $data[14];
                            $parameter['dailytrigtime_hh'] = $data[15];
                            $parameter['dailytrigtime_mm'] = $data[16];
                            $parameter['dailytrigtime_ss'] = $data[17];
                            $parameter['plant'] = $data[18];
                            $parameter['line'] = $data[19];
                            $parameter['asset'] = $data[20];
                            $parameter['cpms_ip'] = $data[21];
                            $parameter['configurator'] = $data[22];
                            $parameter['monitoring'] = $data[23];
                            $parameter['monitoringtime_ms'] = $data[24];
                            $parameter['chfifosize'] = $data[25];
                            $parameter['totalfifosize'] = $data[26];
                            $parameter['samplingrate'] = $data[27];
                            $parameter['staticchannels'] = $data[28];
                            $parameter['sampeperch'] = $data[29];
                            $parameter['dynamicchannels'] = $data[30];
                            $parameter['record_date_time'] = $data[31];
                            $parameter['event_date_time'] = $data[32];
                            $parameter['event_trigger_type'] = $data[33];
                            $parameter['monitoring_status'] = $data[34];
                            $parameter['measurement_state'] = $data[35];
                            $parameter['operating_class'] = $data[36];
                            $parameter['xmlfile'] = $data[37];
                            $parameter['acrms_status'] = $data[38];
                            $parameter['lfrms_status'] = $data[39];
                            $parameter['isorms_status'] = $data[40];
                            $parameter['hfrms_status'] = $data[41];
                            $parameter['acpeak_status'] = $data[42];
                            $parameter['accrest_status'] = $data[43];
                            $parameter['mean_status'] = $data[44];
                            $parameter['peak2peak_status'] = $data[45];
                            $parameter['kurtosis_status'] = $data[46];
                            $parameter['smax_status'] = $data[47];
                            $parameter['mp_acrms_value'] = $data[48];
                            $parameter['mp_lfrms_value'] = $data[49];
                            $parameter['mp_isorms_value'] = $data[50];
                            $parameter['mp_hfrms_value'] = $data[51];
                            $parameter['mp_acpeak_value'] = $data[52];
                            $parameter['mp_accrest_value'] = $data[53];
                            $parameter['mp_mean_value'] = $data[54];
                            $parameter['mp_peak2peak_value'] = $data[55];
                            $parameter['mp_kurtosis_value'] = $data[56];
                            $parameter['mp_smax_value'] = $data[57];
                            $parameter['lfrms'] = $data[58];
                            $parameter['isorms'] = $data[58];
                            $parameter['speed'] = $data[59];
                            $parameter['hfrms'] = $data[60];
                            $parameter['crest'] = $data[61];
                            $parameter['peak'] = $data[62];
                            $parameter['rms'] = $data[63];
                            $parameter['max'] = $data[64];
                            $parameter['min'] = $data[65];
                            $parameter['peak2peak'] = $data[66];
                            $parameter['mean'] = $data[67];
                            $parameter['std'] = $data[68];
                            $parameter['kurtosis'] = $data[69];
                            $parameter['skewness'] = $data[70];
                            $parameter['smax'] = $data[71];
                            $parameter['histo'] = $data[72];
                            $parameter['a1x'] = $data[73];
                            $parameter['p1x'] = $data[74];
                            $parameter['a2x'] = $data[75];
                            $parameter['p2x'] = $data[76];
                            $parameter['a3x'] = $data[77];
                            $parameter['p3x'] = $data[78];
                            $parameter['chname'] = $data[79];
                            $parameter['chunit'] = $data[80];
                            $parameter['chstatus'] = $data[81];

                            $parameter['op1'] = isset($data[82]) ? $data[82] : '0';
                            $parameter['op2'] = isset($data[83]) ? $data[83] : '0';
                            $parameter['op3'] = isset($data[84]) ? $data[84] : '0';
                            $parameter['op4'] = isset($data[85]) ? $data[85] : '0';
                            $parameter['op5'] = isset($data[86]) ? $data[86] : '0';
                            $parameter['op6'] = isset($data[87]) ? $data[87] : '0';
                            $parameter['op7'] = isset($data[88]) ? $data[88] : '0';
                            $parameter['op8'] = isset($data[89]) ? $data[89] : '0';
                            $parameter['op9'] = isset($data[90]) ? $data[90] : '0';
                            $parameter['op10'] = isset($data[91]) ? $data[91] : '0';
                            $parameter['file'] = $value;
                            $parameter['id'] = hash_file('md5', $value);

                            $info = explode('_', $value);

                            $parameter['channel'] = $info[5];

                            $index = 'cpms';
                            $doc_type = 'parameter';

                            $doc = osC_Categories_Admin::getParameterDocument($parameter);

                            var_dump($doc);

                            $output = osC_Categories_Admin::indexDocument($index, $doc_type, $doc);

                            //var_dump($output);
                        }
                        $row++;
                    }

                    fclose($handle);
                } else {
                    echo "no handle for " . $file . "\n";
                }
            }
        }
    }

    function importWatchdog()
    {
        global $osC_Database;

        $year = date("Y");
        $month = date('m');
        $t = date('d-m-Y');
        $day = strtolower(date("d", strtotime($t)));
        $hour = strtolower(date("G", strtotime($t)));

        $dir = realpath(DIR_FS_WATCHDOG);

        $arch = realpath(DIR_FS_WATCHDOG) . "/archives/" . $year . "/" . $month . "/" . $day . "/" . $hour . "/";

        if (!file_exists($arch)) {
            mkdir($arch, 0777, true);
        }

        $err = realpath(DIR_FS_ERRORS) . "/" . $year . "/" . $month . "/" . $day . "/";
        if (!file_exists($arch)) {
            mkdir($err, 0777, true);
        }

        $cdir = scandir($dir);
        //print_r($cdir);

        $arr = array();
        foreach ($cdir as $filename) {
            if ($filename != '.' && $filename != '..' && $filename != '.htaccess') {
                //if (filemtime($dir . "/" . $filename) === false) return false;
                $dat = date("YmdHis", filemtime($dir . "/" . $filename));
                //print_r($dat);
                $arr[$dat] = $filename;
            }
        }
        ksort($arr);

        //print_r($arr);

        foreach ($arr as $key => $value) {
            if (!in_array($value, array(".", "..", ".htaccess"))) {
                $file = $dir . "/" . $value;
                print_r($file);
                $row = 0;
                if (($handle = fopen($file, "r")) !== FALSE) {
                    while (($data = fgetcsv($handle, 0, "\t")) !== FALSE) {
                        if ($row > 0) {
                            //print_r($file);
                            //print_r($data);

                            $parameter = array();

                            $parameter['datetime'] = $data[0];
                            $parameter['equipmentstatus'] = $data[1];
                            $parameter['operatingclass'] = $data[2];
                            $parameter['measuringstate'] = $data[3];
                            $parameter['hash'] = hash_file('md5', $file);
                            $parameter['filectime'] = date("F d Y H:i:s.", filectime($file));

                            $info = explode('_', $value);

                            $parameter['cpms_ip'] = $info[1];
                            //var_dump($parameter);

                            $config = osC_Categories_Admin::getCpms($parameter);

                            if ($config) {
                                $query = "SELECT * FROM delta_parameters WHERE cpms_ip = :cpms_ip ORDER BY eventdate DESC LIMIT 1";

                                $Qparameters = $osC_Database->query($query);
                                $Qparameters->bindValue(':cpms_ip', $parameter['cpms_ip']);
                                $Qparameters->execute();

                                $params = $Qparameters->toArray();

                                $Qparameters->freeResult();

                                $params['channel'] = $params['chname'];

                                $conf = osC_Categories_Admin::getLayout($params['plant'], $params['line'], $params['asset'], $params['channel']);
                                if ($conf) {
                                    //var_dump($conf);
                                    $status = $config['cpms_status'] == 'error' ? $config['cpms_status'] : $params['chstatus'];

                                    $conf['channel'] = $params['chname'];
                                    $conf['plant'] = $params['plant'];
                                    $conf['line'] = $params['line'];
                                    $conf['asset'] = $params['asset'];
                                    $conf['cpms_ip'] = $params['cpms_ip'];

                                    if (!osC_Categories_Admin::updateStatus($conf, $status, $params)) {
                                        //send alert
                                        echo $_SESSION['error'];
                                    }
                                } else {
                                    echo $_SESSION['error'];
                                }
                            } else {
                                echo $_SESSION['error'];
                            }
                            //var_dump($config)
                            //copy($file, $arch . "/" . hash_file('md5', $file) . '_' . $value);
                        }
                        $row++;
                    }

                    fclose($handle);
                } else {
                    echo "no handle for " . $file . "\n";
                }
            }
        }
    }

    function moveCategories()
    {
        global $toC_Json, $osC_Language;

        $error = false;
        $batch = explode(',', $_REQUEST['categories_ids']);

        foreach ($batch as $id) {
            if (!osC_Categories_Admin::move($id, $_REQUEST['parent_category_id'])) {
                $error = true;
                break;
            }
        }

        if ($error === false) {
            $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
            $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        }

        echo $toC_Json->encode($response);
    }

    function loadCategory()
    {
        global $toC_Json, $osC_Language, $osC_Database, $osC_CategoryTree;

        $osC_CategoryTree = new osC_CategoryTree();

        $data = osC_Categories_Admin::getData($_REQUEST['categories_id']);

        $Qcategories = $osC_Database->query('select c.*, cd.* from :table_categories c left join :table_categories_description cd on c.categories_id = cd.categories_id where c.categories_id = :categories_id  ');
        $Qcategories->bindTable(':table_categories', TABLE_CATEGORIES);
        $Qcategories->bindTable(':table_categories_description', TABLE_CATEGORIES_DESCRIPTION);
        $Qcategories->bindInt(':categories_id', $_REQUEST['categories_id']);
        $Qcategories->execute();

        while ($Qcategories->next()) {
            $data['categories_name[' . $Qcategories->ValueInt('language_id') . ']'] = $Qcategories->Value('categories_name');
            $data['content_url[' . $Qcategories->ValueInt('language_id') . ']'] = $Qcategories->Value('categories_url');
            $data['page_title[' . $Qcategories->ValueInt('language_id') . ']'] = $Qcategories->Value('categories_page_title');
            $data['meta_keywords[' . $Qcategories->ValueInt('language_id') . ']'] = $Qcategories->Value('categories_meta_keywords');
            $data['meta_descriptions[' . $Qcategories->ValueInt('language_id') . ']'] = $Qcategories->Value('categories_meta_description');
        }
        $Qcategories->freeResult();

        $response = array('success' => true, 'data' => $data);

        echo $toC_Json->encode($response);
    }

    function loadPlant()
    {
        global $toC_Json;

        $data = array();

        if (!isset($_REQUEST['plants_id'])) {
            $response = array('success' => false, 'feedback' => "Code Usine non defini !!!");
        } else {
            if (!is_numeric($_REQUEST['plants_id'])) {
                $response = array('success' => false, 'feedback' => "Code Usine invalide !!!");
            } else {
                $data = osC_Categories_Admin::getPlant($_REQUEST['plants_id']);
                $response = array('success' => true, 'data' => $data);
            }
        }

        echo $toC_Json->encode($response);
    }

    function loadLine()
    {
        global $toC_Json;

        $data = array();

        if (!isset($_REQUEST['lines_id'])) {
            $response = array('success' => false, 'feedback' => "Code Ligne non defini !!!", 'data' => $data);
        } else {
            if (!is_numeric($_REQUEST['lines_id'])) {
                $response = array('success' => false, 'feedback' => "Code Ligne invalide !!!", 'data' => $data);
            } else {
                $data = osC_Categories_Admin::getLine($_REQUEST['lines_id']);
                $response = array('success' => true, 'feedback' => "OK !!!", 'data' => $data);
            }
        }


        echo $toC_Json->encode($response);
    }

    function loadAsset()
    {
        global $toC_Json;

        $data = null;

        if (!isset($_REQUEST['asset_id'])) {
            $response = array('success' => false, 'feedback' => "Code Asset non defini !!!");
        } else {
            if (!is_numeric($_REQUEST['asset_id'])) {
                $response = array('success' => false, 'feedback' => "Code Asset invalide !!!");
            } else {
                $data = osC_Categories_Admin::getAsset($_REQUEST['asset_id']);
                $response = array('success' => true, 'data' => $data);
            }
        }

        echo $toC_Json->encode($response);
    }

    function loadSensor()
    {
        global $toC_Json;

        $data = null;

        if (!isset($_REQUEST['sensors_id'])) {
            $response = array('success' => false, 'feedback' => "ID Sensor non defini !!!");
        } else {
            if (!is_numeric($_REQUEST['sensors_id'])) {
                $response = array('success' => false, 'feedback' => "ID Sensor invalide !!!");
            } else {
                $data = osC_Categories_Admin::getSensor($_REQUEST['sensors_id']);
                if ($data) {
                    $response = array('success' => true, 'data' => $data, 'feedback' => "OK");
                } else {
                    $response = array('success' => false, 'feedback' => $_SESSION['error']);
                }
            }
        }

        echo $toC_Json->encode($response);
    }

    function loadComponent()
    {
        global $toC_Json;

        $data = null;

        if (!isset($_REQUEST['component_id'])) {
            $response = array('success' => false, 'feedback' => "ID Component non defini !!!");
        } else {
            if (!is_numeric($_REQUEST['component_id'])) {
                $response = array('success' => false, 'feedback' => "ID Component invalide !!!");
            } else {
                $data = osC_Categories_Admin::getComponent($_REQUEST['component_id']);
                $response = array('success' => true, 'data' => $data);
            }
        }

        echo $toC_Json->encode($response);
    }

    function saveCategory()
    {
        global $toC_Json, $osC_Database, $osC_Language;

        $parent_id = isset($_REQUEST['parent_category_id']) ? end(explode('_', $_REQUEST['parent_category_id']))
            : 0;

        //search engine friendly urls
        $formatted_urls = array();
        $urls = $_REQUEST['content_url'];
        if (is_array($urls) && !empty($urls)) {
            foreach ($urls as $languages_id => $url) {
                $url = toc_format_friendly_url($url);
                if (empty($url)) {
                    $url = toc_format_friendly_url($_REQUEST['categories_name'][$languages_id]);
                }

                $formatted_urls[$languages_id] = $url;
            }
        }

        $data = array('parent_id' => $parent_id,
            'sort_order' => $_REQUEST['sort_order'],
            'image' => $_FILES['image'],
            'categories_status' => $_REQUEST['categories_status'],
            'name' => $_REQUEST['categories_name'],
            'url' => $formatted_urls,
            'page_title' => $_REQUEST['page_title'],
            'meta_keywords' => $_REQUEST['meta_keywords'],
            'meta_description' => $_REQUEST['meta_descriptions'],
            'flag' => (isset($_REQUEST['product_flag'])) ? $_REQUEST['product_flag'] : 0,
            'ratings' => $_REQUEST['ratings']);

        $category_id = osC_Categories_Admin::save((isset($_REQUEST['categories_id']) && is_numeric($_REQUEST['categories_id'])
            ? $_REQUEST['categories_id'] : null), $data);
        if ($category_id > 0) {
            if (content::setPermission($category_id, 'pages', 'can_modify', -1, '1')) {
                $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
            } else {
                $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
            }
        } else {
            $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        }

        header('Content-Type: text/html');
        echo $toC_Json->encode($response);
    }

    function savePlant()
    {
        global $toC_Json, $osC_Language;

        if (!isset($_REQUEST['customers_id'])) {
            $response = array('success' => false, 'feedback' => "Code Client non defini !!!");
        } else {
            if (!is_numeric($_REQUEST['customers_id'])) {
                $response = array('success' => false, 'feedback' => "Code Client invalide !!!");
            } else {
                if (is_null($_REQUEST['latitude']) || is_null($_REQUEST['longitude'])) {
                    $response = array('success' => false, 'feedback' => "La position geographique de cette usine est invalide !!!");
                } else {
                    $data = $_REQUEST;

                    $category_id = osC_Categories_Admin::savePlant((isset($_REQUEST['plants_id']) && is_numeric($_REQUEST['plants_id'])
                        ? $_REQUEST['plants_id'] : null), $data);
                    if ($category_id > 0) {
                        if (content::setPermission($category_id, 'plant', 'can_modify', -1, '1')) {
                            $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
                        } else {
                            $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
                        }
                    } else {
                        $response = array('success' => false, 'feedback' => $_SESSION['error']);
                    }
                }
            }
        }

        header('Content-Type: text/html');
        echo $toC_Json->encode($response);
    }

    function saveLine()
    {
        global $toC_Json, $osC_Language;

        if (!isset($_REQUEST['plants_id'])) {
            $response = array('success' => false, 'feedback' => "Code Usine non defini !!!");
        } else {
            if (!is_numeric($_REQUEST['plants_id'])) {
                $response = array('success' => false, 'feedback' => "Code Usine invalide !!!");
            } else {
                $data = $_REQUEST;

                $category_id = osC_Categories_Admin::saveLine((isset($_REQUEST['lines_id']) && is_numeric($_REQUEST['lines_id'])
                    ? $_REQUEST['lines_id'] : null), $data);
                if ($category_id > 0) {
                    if (content::setPermission($category_id, 'line', 'can_modify', -1, '1')) {
                        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
                    } else {
                        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
                    }
                } else {
                    $response = array('success' => false, 'feedback' => $_SESSION['error']);
                }
            }
        }

        header('Content-Type: text/html');
        echo $toC_Json->encode($response);
    }

    function saveComponent()
    {
        global $toC_Json, $osC_Language;

        if (!isset($_REQUEST['asset_id'])) {
            $response = array('success' => false, 'feedback' => "Code Asset non defini !!!");
        } else {
            if (!is_numeric($_REQUEST['asset_id'])) {
                $response = array('success' => false, 'feedback' => "Code Asset invalide !!!");
            } else {
                $data = $_REQUEST;

                $category_id = osC_Categories_Admin::saveComponent((isset($_REQUEST['component_id']) && is_numeric($_REQUEST['component_id'])
                    ? $_REQUEST['component_id'] : null), $data);
                if ($category_id > 0) {
                    if (content::setPermission($category_id, 'component', 'can_modify', -1, '1')) {
                        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
                    } else {
                        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
                    }
                } else {
                    $response = array('success' => false, 'feedback' => $_SESSION['error']);
                }
            }
        }

        header('Content-Type: text/html');
        echo $toC_Json->encode($response);
    }

    function saveAsset()
    {
        global $toC_Json, $osC_Language;

        if (!isset($_REQUEST['lines_id'])) {
            $response = array('success' => false, 'feedback' => "ID Ligne non defini !!!");
        } else {
            if (!is_numeric($_REQUEST['lines_id'])) {
                $response = array('success' => false, 'feedback' => "ID Asset invalide !!!");
            } else {
                $data = $_REQUEST;

                $category_id = osC_Categories_Admin::saveAsset((isset($_REQUEST['asset_id']) && is_numeric($_REQUEST['asset_id'])
                    ? $_REQUEST['asset_id'] : null), $data);
                if ($category_id > 0) {
                    if (content::setPermission($category_id, 'asset', 'can_modify', -1, '1')) {
                        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
                    } else {
                        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
                    }
                } else {
                    $response = array('success' => false, 'feedback' => $_SESSION['error']);
                }
            }
        }

        header('Content-Type: text/html');
        echo $toC_Json->encode($response);
    }

    function saveSensor()
    {
        global $toC_Json, $osC_Language;

        if (!isset($_REQUEST['component_id'])) {
            $response = array('success' => false, 'feedback' => "ID component non defini !!!");
        } else {
            if (!is_numeric($_REQUEST['component_id'])) {
                $response = array('success' => false, 'feedback' => "ID component invalide !!!");
            } else {
                $data = $_REQUEST;

                $category_id = osC_Categories_Admin::saveSensor((isset($_REQUEST['sensors_id']) && is_numeric($_REQUEST['sensors_id'])
                    ? $_REQUEST['sensors_id'] : null), $data);
                if ($category_id > 0) {
                    if (content::setPermission($category_id, 'sensor', 'can_modify', -1, '1')) {
                        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
                    } else {
                        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
                    }
                } else {
                    $response = array('success' => false, 'feedback' => $_SESSION['error']);
                }
            }
        }

        header('Content-Type: text/html');
        echo $toC_Json->encode($response);
    }

    function copySensor()
    {

    }

    function listFreq()
    {
        global $toC_Json;

        $records = array();

        $i = 0;

        while ($i < 120) {
            $records[] = array('index' => $i, 'value' => ($i + 1) * 1000, 'display' => ($i + 1) . ' sec');
            $i++;
        }

        $response = array(EXT_JSON_READER_TOTAL => count($records),
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listParentCategory()
    {
        global $toC_Json, $osC_Language;

        $osC_CategoryTree = new osC_CategoryTree_Admin();

        $records = array(array('id' => '0',
            'text' => $osC_Language->get('top_category')));

        foreach ($osC_CategoryTree->getTree() as $value) {
            $records[] = array('id' => $value['id'],
                'text' => $value['title']);
        }

        $response = array(EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listParentArticleCategory()
    {
        global $toC_Json, $osC_Language;

        $osC_CategoryTree = new osC_CategoryTree_Admin();

        foreach ($osC_CategoryTree->getTree() as $value) {
            $records[] = array('id' => $value['id'],
                'text' => $value['title']);
        }

        $response = array(EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function loadCategoriesTree()
    {
        global $toC_Json;

        $include_custom_pages = isset($_REQUEST['filter']) && $_REQUEST['filter'] != '-1' ? true : false;
        $show_home = isset($_REQUEST['sh']) && $_REQUEST['sh'] == '1' ? true : false;
        $check_permissions = isset($_REQUEST['cp']) && $_REQUEST['cp'] == '0' ? false : false;
        $osC_CategoryTree = new osC_CategoryTree();
        $osC_CategoryTree->setShowCategoryProductCount(isset($_REQUEST['scc']) && $_REQUEST['scc'] == '1' ? true : false);

        $categories_array = $osC_CategoryTree->buildExtJsonTreeArrayForUser(0, '', $include_custom_pages, $show_home, $check_permissions);

        echo $toC_Json->encode($categories_array);
    }

    function listImports()
    {
        global $toC_Json, $osC_Database, $osC_Language;

        $records = array();

        $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start'];
        $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit'];

        $customers_id = empty($_REQUEST['customers_id']) ? 0 : $_REQUEST['customers_id'];

        $Qdocuments = $osC_Database->query("select i.* from :table_imports i where 1 = 1 ");

        $Qdocuments->appendQuery(' and lower(i.plant) in (select lower(d.categories_name) from :table_layout_description d where d.categories_id in (select l.categories_id from :table_layout l where l.parent_id = :customers_id))');
        $Qdocuments->bindTable(':table_layout_description', TABLE_LAYOUT_DESCRIPTION);
        $Qdocuments->bindTable(':table_layout', TABLE_LAYOUT);
        $Qdocuments->bindInt(':customers_id', $customers_id);

        if (isset($_REQUEST['start_date'])) {
            $Qdocuments->appendQuery(' and i.when >= :start_date');
            $Qdocuments->bindValue(':start_date', $_REQUEST['start_date']);
        }

        if (isset($_REQUEST['end_date'])) {
            //$Qdocuments->appendQuery(' and i.when <= :end_date');
            //$Qdocuments->bindValue(':end_date', "DATE_ADD('" . $_REQUEST['end_date'] . "', INTERVAL 1 DAY)");
        }

        $Qdocuments->appendQuery(' order by imports_id desc');

        $Qdocuments->bindTable(':table_imports', TABLE_IMPORTS);
        $Qdocuments->setExtBatchLimit($start, $limit);
        $Qdocuments->execute();

        //var_dump($Qdocuments);

        while ($Qdocuments->next()) {
            $file = $Qdocuments->value('status') == true ? "xxxxxxx.success" : "xxxxxxx.error";
            $entry_icon = osc_icon_from_filename($file);
            $url = $Qdocuments->value('status') == true ? "../documents/archives/" . $Qdocuments->value('file') : "../documents/errors/" . $Qdocuments->value('file');
            //$url = '../cache/documents/' . $Qdocuments->value('cache_filename');

            $action = array(
                array('class' => 'icon-download-record', 'qtip' => $osC_Language->get('icon_download')),
                array('class' => 'icon-delete-record', 'qtip' => $osC_Language->get('icon_trash')));

            $records[] = array('imports_id' => $Qdocuments->valueInt('imports_id'),
                'icon' => $entry_icon,
                'action' => $action,
                'url' => $url,
                'status' => $Qdocuments->value('status'),
                'when' => $Qdocuments->value('when'),
                'file' => $Qdocuments->value('file'),
                'comments' => $Qdocuments->value('comments'),
                'plant' => $Qdocuments->value('plant'));
        }

        $response = array(EXT_JSON_READER_TOTAL => $Qdocuments->getBatchSize(),
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function listEventlog()
    {
        global $toC_Json, $osC_Database, $osC_Language;

        $records = array();

        $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start'];
        $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit'];

        $customers_id = empty($_REQUEST['customers_id']) ? 0 : $_REQUEST['customers_id'];

        $Qdocuments = $osC_Database->query("select i.* from delta_eventlog i where 1 = 1 ");

        if (isset($_REQUEST['start_date'])) {
            //$Qdocuments->appendQuery(' and i.event_date >= :start_date');
            //$Qdocuments->bindValue(':start_date', $_REQUEST['start_date']);
        }

        if (isset($_REQUEST['end_date'])) {
            //$Qdocuments->appendQuery(' and i.event_date <= :end_date');
            //$Qdocuments->bindValue(':end_date', "DATE_ADD('" . $_REQUEST['end_date'] . "', INTERVAL 1 DAY)");
        }

        if (isset($_REQUEST['content_id']) && $_REQUEST['content_id'] != '-1') {
            $Qdocuments->appendQuery(' and i.content_id = :content_id');
            $Qdocuments->bindValue(':content_id', $_REQUEST['content_id']);
        }

        if (isset($_REQUEST['content_type']) && $_REQUEST['content_type'] != 'xx') {
            $Qdocuments->appendQuery(' and i.content_type = :content_type');
            $Qdocuments->bindValue(':content_type', $_REQUEST['content_type']);
        }

        $Qdocuments->appendQuery(' order by events_id desc');
        $Qdocuments->setExtBatchLimit($start, $limit);
        $Qdocuments->execute();

        //var_dump($Qdocuments);

        while ($Qdocuments->next()) {
            $file = "xxxxxxx.success";

            switch ($Qdocuments->value('type')) {
                case "error":
                    $file = "xxxxxxx.error";
                    break;
                case "info":
                    $file = "xxxxxxx.info";
                    break;
                case "succes":
                    $file = "xxxxxxx.success";
                    break;
                case "warning":
                    $file = "xxxxxxx.warning";
                    break;
            }

            $entry_icon = osc_icon_from_filename($file);

            $records[] = array('events_id' => $Qdocuments->valueInt('events_id'),
                'icon' => $entry_icon,
                'event_date' => $Qdocuments->value('event_date'),
                'content_id' => $Qdocuments->valueInt('content_id'),
                'content_type' => $Qdocuments->value('content_type'),
                'type' => $Qdocuments->value('type'),
                'user' => $Qdocuments->value('user'),
                'category' => $Qdocuments->value('category'),
                'description' => $Qdocuments->value('description'),
                'source' => $Qdocuments->value('source'));
        }

        $response = array(EXT_JSON_READER_TOTAL => $Qdocuments->getBatchSize(),
            EXT_JSON_READER_ROOT => $records);

        echo $toC_Json->encode($response);
    }

    function loadLayoutTree()
    {
        global $toC_Json;

        $include_custom_pages = isset($_REQUEST['filter']) && $_REQUEST['filter'] != '-1' ? true : false;
        $show_home = isset($_REQUEST['sh']) && $_REQUEST['sh'] == '1' ? true : false;
        $check_permissions = true;
        $osC_LayoutTree = new osC_LayoutTree();
        $osC_LayoutTree->setShowCategoryProductCount(false);

        $categories_array = $osC_LayoutTree->buildExtJsonTreeArrayForUser(0, '', $include_custom_pages, $show_home, $check_permissions);

        echo $toC_Json->encode($categories_array);
    }

    function listRolePermissions()
    {
        $categories_array = array();

        if (isset($_REQUEST['content_id']) && !empty($_REQUEST['content_id'])) {
            global $toC_Json;

            $osC_CategoryTree = new osC_CategoryTree();

            $categories_array = $osC_CategoryTree->buildExtJsonTreeArrayWithPermissions(0, 0, $_REQUEST['content_id']);
        }

        $response = array(EXT_JSON_READER_TOTAL => count($categories_array),
            EXT_JSON_READER_ROOT => $categories_array);

        echo $toC_Json->encode($response);
    }

    function setStatus()
    {
        global $toC_Json, $osC_Language;

        if (isset($_REQUEST['categories_id']) && osC_Categories_Admin::setStatus($_REQUEST['categories_id'], (isset($_REQUEST['flag'])
            ? $_REQUEST['flag'] : 1), (isset($_REQUEST['product_flag']) ? $_REQUEST['product_flag'] : 0))
        ) {
            $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
            $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        }

        echo $toC_Json->encode($response);
    }

    function setPermission()
    {
        global $toC_Json, $osC_Language;

        if (!isset($_REQUEST['categories_id'])) {
            $response = array('success' => false, 'feedback' => 'Veuillez specifier une Categorie');
            echo $toC_Json->encode($response);
            return;
        }

        if (!isset($_REQUEST['roles_id'])) {
            $response = array('success' => false, 'feedback' => 'Veuillez specifier un Role');
            echo $toC_Json->encode($response);
            return;
        }

        if (!isset($_REQUEST['permission'])) {
            $response = array('success' => false, 'feedback' => 'Veuillez specifier une permission pour ce Role');
            echo $toC_Json->encode($response);
            return;
        }

        $data = array('categories_id' => $_REQUEST['categories_id'], 'roles_id' => $_REQUEST['roles_id'], 'permission' => $_REQUEST['permission'], 'flag' => $_REQUEST['flag']);

        if (content::setPermission($data['categories_id'], 'pages', $data['permission'], $data['roles_id'], $data['flag'])) {
            $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
        } else {
            $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
        }

        echo $toC_Json->encode($response);
    }
}

?>
