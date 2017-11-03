<?php


  class osC_Access_Tickets extends osC_Access {
    var $_module = 'tickets',
        $_group = 'articles',
        $_icon = 'tickets.png',
        $_title,
        $_sort_order = 120;

    function osC_Access_Tickets() {
      $this->_title = 'Tickets';
    }
  }
?>
