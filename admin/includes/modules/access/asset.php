<?php


  class osC_Access_Asset extends osC_Access {
    var $_module = 'asset',
        $_group = 'articles',
        $_icon = 'folder_red.png',
        $_title,
        $_sort_order = 110;

    function osC_Access_Asset() {
      $this->_title = 'Asset Explorer';
    }
  }
?>
