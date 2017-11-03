<?php


  class osC_Access_Imports extends osC_Access {
    var $_module = 'imports',
        $_group = 'articles',
        $_icon = 'folder_sent.png',
        $_title,
        $_sort_order = 120;

    function osC_Access_Imports() {
      $this->_title = 'Import Monitor';
    }
  }
?>
