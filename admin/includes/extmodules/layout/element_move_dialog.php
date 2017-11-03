<?php
/*
  $Id: layout_move_dialog.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>
Toc.layout.layoutMoveDialog = function (config) {
  config = config || {};
  
  config.id = 'layout-move-dialog-win';
  config.title = '<?php echo $osC_Language->get("action_heading_batch_move_layout"); ?>';
  config.layout = 'fit';
  config.width = 400;
  config.autoHeight = true;
  config.modal = true;
  config.iconCls = 'icon-layout-win';
  config.items = this.buildForm();
  
  config.buttons = [
    {
      text: TocLanguage.btnSave,
      handler: function () {
        this.submitForm();
        this.disable();
      }, 
      scope: this
    }, 
    {
      text: TocLanguage.btnClose,
      handler: function () {
        this.close();
      }, 
      scope: this
    }
  ];
  
  this.addEvents({'saveSuccess': true});
  
  Toc.layout.layoutMoveDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.layout.layoutMoveDialog, Ext.Window, {

  show: function (layoutId, cPath) {
    layoutId = layoutId || null;
    this.cPath = cPath || null;

    this.frmlayout.form.reset();
    this.frmlayout.form.baseParams['layout_ids'] = layoutId;

    Toc.layout.layoutMoveDialog.superclass.show.call(this);
    this.pnlGeneral.cboParentlayout.getStore().on('load', function() {
      this.pnlGeneral.cboParentlayout.setValue(parentId);
    }, this);
  },
  
  buildForm: function () {

    this.cboParentlayout = Toc.content.ContentManager.getlayoutCombo();
    
    this.frmlayout = new Ext.form.FormPanel({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'layout',
        action: 'move_layout'
      },
      border: false,
      frame: false,
      autoHeight: true,      
      labelAlign: 'top',
      defaults: {anchor: '97%'},
      layoutConfig: { labelSeparator: '' },
      labelWidth: 160,
      items: this.cboParentlayout
    });
    
    return this.frmlayout;
  },
    
  submitForm: function () {
    this.frmlayout.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function (form, action) {
        this.fireEvent('saveSuccess', action.result.feedback);
        this.close();
      },
      failure: function (form, action) {
        if (action.failureType != 'client') {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
        }
      },
      scope: this
    });
  }
});