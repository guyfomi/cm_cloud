<?php
/*
  $Id: newsletters.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Access_Newsletters extends osC_Access {
    var $_module = 'newsletters',
        $_group = 'tools',
        $_icon = 'email_send.png',
        $_title,
        $_sort_order = 1100;

    function osC_Access_Newsletters() {
      global $osC_Language;

      $this->_title = $osC_Language->get('access_newsletters_title');
    }
  }
?>
