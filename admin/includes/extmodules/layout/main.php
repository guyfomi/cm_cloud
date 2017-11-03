<?php
  echo 'Ext.namespace("Toc.layout");';

  include('element_grid.php');
  include('element_general_panel.php');
  include('site_dialog.php');
  include('element_move_dialog.php');
  include('layout_main_panel.php');
?>

Ext.override(TocDesktop.LayoutWindow, {

  createWindow: function(){
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('layout-win');
     
    if(!win){
      var pnl = new Toc.layout.mainPanel({owner: this});
      
      win = desktop.createWindow({
        id: 'layout-win',
        title: 'Asset Manager',
        width: 870,
        height: 400,
        iconCls: 'icon-layout-win',
        layout: 'fit',
        items: pnl
      });
    }
    
    win.show();
    win.maximize();
  },
  
  createlayoutDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('layout-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.layout.layoutDialog);
      
      dlg.on('saveSuccess', function (feedback, layoutId, text) {
        this.app.showNotification({
          title: TocLanguage.msgSuccessTitle,
          html: feedback
        });
      }, this);
    }

    return dlg;
  },
  
  createlayoutMoveDialog: function() {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('layout-move-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.layout.layoutMoveDialog);
      
      dlg.on('saveSuccess', function (feedback) {
        this.app.showNotification({
          title: TocLanguage.msgSuccessTitle,
          html: feedback
        });
      }, this);
    }
    
    return dlg;
  }
});