<?php
/*
  $Id: main.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  echo 'Ext.namespace("Toc.modules_geoip");';
  
  include('modules_geoip_grid.php');
?>

Ext.override(TocDesktop.ModulesGeoipWindow, {

  createWindow : function(){
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('modules_geoip-win');
     
    if(!win){
      var grid = new Toc.modules_geoip.ModulesGeoIPGrid({owner: this});

      win = desktop.createWindow({
        id: 'modules_geoip-win',
        title:'<?php echo $osC_Language->get('heading_title'); ?>',
        width: 800,
        height: 400,
        iconCls: 'icon-modules_geoip-win',
        layout: 'fit',
        items: grid
      });
    }
    
    win.show();
  }
});