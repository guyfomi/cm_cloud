<?php
/*
  $Id: modules_payment.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Access_Modules_payment extends osC_Access {
    var $_module = 'modules_payment',
        $_group = 'modules',
        $_icon = 'payment.png',
        $_title,
        $_sort_order = 100;

    function osC_Access_Modules_payment() {
      global $osC_Language;

      $this->_title = $osC_Language->get('access_modules_payment_title');
    }
  }
?>
