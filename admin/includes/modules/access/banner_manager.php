<?php
/*
  $Id: banner_manager.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Access_Banner_manager extends osC_Access {
    var $_module = 'banner_manager',
        $_group = 'tools',
        $_icon = 'database_save.png',
        $_title,
        $_sort_order = 300;

    function osC_Access_Banner_manager() {
      global $osC_Language;

      $this->_title = $osC_Language->get('access_banner_manager_title');
    }
  }
?>
