<?php
/*
  $Id: categories.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

    class osC_Boxes_visits_summary extends osC_Modules
    {
        var $_title,
        $_code = 'visits_summary',
        $_author_name = 'Guy Fomi',
        $_author_www = 'http://www.mefobemarket.com',
        $_group = 'boxes';

        function osC_Boxes_visits_summary()
        {
            $this->_title = 'Resume des visites';
        }

        function initialize()
        {
            $this->_content = '<div id="widgetIframe"><iframe width="100%" height="230" src="http://localhost/piwik/index.php?module=Widgetize&action=iframe&columns[]=nb_visits&moduleToWidgetize=VisitsSummary&actionToWidgetize=getEvolutionGraph&idSite=1&period=day&date=yesterday&disableLink=1&widget=1" scrolling="no" frameborder="0" marginheight="0" marginwidth="0"></iframe></div>';
        }

        function install()
        {
            parent::install();
        }

        function getKeys()
        {
            if (!isset($this->_keys)) {
                $this->_keys = array();
            }

            return $this->_keys;
        }
    }

?>