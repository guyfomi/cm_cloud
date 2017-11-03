<?php

require('includes/classes/elastic.php');

class toC_Json_Elastic
{
    function indexParameters()
    {
        global $osC_Database;

        $query = 'select p.* from :table_parameters p where indexed = 0 ';
        $query = $query . " order by p.eventdate desc";

        $QParameters = $osC_Database->query($query);

        $QParameters->bindTable(':table_parameters', TABLE_PARAMETERS);
        $QParameters->setExtBatchLimit(0,50);
        $QParameters->execute();

        //var_dump($QParameters);

        while ($QParameters->next()) {
            $index = 'cpms';
            $doc_type = 'parameter';
            $parameter = $QParameters->toArray();

            $doc = osC_Elastic_Admin::getParameterDocument($parameter);

            //var_dump($doc);

            osC_Elastic_Admin::indexDocument($index,$doc_type,$doc);

            //echo $output;
        }
    }

    function indexTickets()
    {
        global $osC_Database;

        $query = 'SELECT t.tickets_id,t.created,t.description,(select a.user_name from delta_administrators a where a.id = t.administrators_id) user,(select s.name from delta_ticket_status s where s.id = t.status_id) status,(select c.customers_surname from delta_customers c where c.customers_id = t.customers_id) customer,(select d.categories_name from delta_layout_description d where d.categories_id = t.plants_id) plant,(select d.categories_name from delta_layout_description d where d.categories_id = t.lines_id) line,(select d.categories_name from delta_layout_description d where d.categories_id = t.asset_id) asset,(select d.categories_name from delta_layout_description d where d.categories_id = t.component_id) component,(select d.categories_name from delta_layout_description d where d.categories_id = t.sensors_id) sensor,(select p.latitude from delta_plants p where p.plants_id = t.plants_id) latitude,(select p.longitude from delta_plants p where p.plants_id = t.plants_id) longitude FROM delta_tickets t where indexed = 0 ';
        $query = $query . " order by t.created desc";

        $QParameters = $osC_Database->query($query);

        //$QParameters->bindTable(':table_parameters', TABLE_PARAMETERS);
        $QParameters->setExtBatchLimit(0,50);
        $QParameters->execute();

        //var_dump($QParameters);

        while ($QParameters->next()) {
            $index = 'cpms';
            $doc_type = 'ticket';
            $parameter = $QParameters->toArray();

            $doc = osC_Elastic_Admin::getTicketDocument($parameter);

            //var_dump($doc);

            osC_Elastic_Admin::indexDocument($index,$doc_type,$doc);

            //echo $output;
        }
    }
}

?>
