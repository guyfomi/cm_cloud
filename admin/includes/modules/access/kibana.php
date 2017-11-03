<?php


  class osC_Access_Kibana extends osC_Access {
    var $_module = 'kibana',
        $_group = 'reports',
        $_icon = 'polls.png',
        $_title,
        $_sort_order = 1;

    function osC_Access_Kibana() {
      $this->_title = 'Visualization';
    }
  }
?>
