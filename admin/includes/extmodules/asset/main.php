<?php
  echo 'Ext.namespace("Toc.asset");';

  //include('element_grid.php');
  //include('element_general_panel.php');
  //include('site_dialog.php');
  //include('parameters_grid.php');
  include('asset_main_panel.php');
?>

Ext.override(TocDesktop.AssetWindow, {

  createWindow: function(){
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('asset-win');
     
    if(!win){
      var pnl = new Toc.asset.mainPanel({owner: this});
      
      win = desktop.createWindow({
        id: 'asset-win',
        title: 'Asset Explorer',
        width: 870,
        height: 400,
        iconCls: 'icon-report-win',
        layout: 'fit',
        items: pnl
      });

      pnl.win = win;
    }
    
    win.show();
    win.maximize();
  }
});