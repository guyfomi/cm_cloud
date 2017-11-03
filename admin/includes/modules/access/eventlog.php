<?php


  class osC_Access_Eventlog extends osC_Access {
    var $_module = 'eventlog',
        $_group = 'tools',
        $_icon = 'newsletters.png',
        $_title,
        $_sort_order = 120;

    function osC_Access_Eventlog() {
      $this->_title = 'Event Log';
    }
  }
?>
