<?php
/*
  $Id: customers.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Access_Partenaires extends osC_Access {
    var $_module = 'partenaires',
        $_group = 'definitions',
        $_icon = 'personal.png',
        $_title,
        $_sort_order = 100;

    function osC_Access_Partenaires() {
      global $osC_Language;

      $this->_title = 'Partenaires';
    }
  }
?>
