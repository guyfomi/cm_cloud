<?php
/*
  $Id: layout_grid.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  
?>
Toc.layout.layoutGrid = function (config) {
  config = config || {};
  
  config.region = 'center';
  config.loadMask = true;
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'layout',
      action: 'list_layout'
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'layout_id'
    }, [
      'layout_id',
      'layout_name',
      'status',
      'path'
    ])
  });
  
  config.rowActions = new Ext.ux.grid.RowActions({
    header: '<?php echo $osC_Language->get("table_heading_action"); ?>',
    actions: [
      {iconCls: 'icon-edit-record', qtip: TocLanguage.tipEdit}, 
      {iconCls: 'icon-move-record', qtip: TocLanguage.tipMove}, 
      {iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete}
    ],
    widthIntercept: Ext.isSafari ? 4: 2
  });
  
  config.rowActions.on('action', this.onRowAction, this);
  config.plugins = config.rowActions;
  
  var renderActive = function(status) {
    if(status == 1) {
      return '<img class="img-button" src="images/icon_status_green.gif" />&nbsp;<img class="img-button btn-status-off" style="cursor: pointer" src="images/icon_status_red_light.gif" />';
    }else {
      return '<img class="img-button btn-status-on" style="cursor: pointer" src="images/icon_status_green_light.gif" />&nbsp;<img class="img-button" src= "images/icon_status_red.gif" />';
    }
  }; 
  
  config.sm = new Ext.grid.CheckboxSelectionModel();
  config.cm = new Ext.grid.ColumnModel([
    config.sm,
    {id: 'products_layout_name', header: 'Page', dataIndex: 'layout_name'},
    { header: 'Status', dataIndex: 'status', align: 'center', renderer: renderActive},
    config.rowActions
  ]);
  config.autoExpandColumn = 'products_layout_name';
  
  config.listeners = {"rowdblclick": this.onGrdRowDbClick};
  config.search = new Ext.form.TextField({name: 'search', width: 150});
  
  config.tbar = [
    {
      text: TocLanguage.btnAdd,
      iconCls: 'add',
      handler: this.onAdd,
      scope: this
    },
    '-', 
    {
      text: TocLanguage.btnDelete,
      iconCls: 'remove',
      handler: this.onBatchDelete,
      scope: this
    },
    '-',
    {
      text: TocLanguage.btnMove,
      iconCls: 'icon-move-record',
      handler: this.onBathMove,
      scope: this
    }, 
    '-',
    {
      text: TocLanguage.btnRefresh,
      iconCls: 'refresh',
      handler: this.onSearch,
      scope: this
    }, 
    '->',
    config.search,
    '',
    {
      iconCls: 'search',
      handler: this.onSearch,
      scope: this
    }];
  
  var thisObj = this;
  config.bbar = new Ext.PageToolbar({
    pageSize: Toc.CONF.GRID_PAGE_SIZE,
    store: config.ds,
    steps: Toc.CONF.GRID_STEPS,
    btnsConfig:[
      {
        text: TocLanguage.btnAdd,
        iconCls:'add',
        handler: function() {thisObj.onAdd();}
      },
      {
        text: TocLanguage.btnDelete,
        iconCls:'remove',
        handler: function() {thisObj.onBatchDelete();}
      },
      {
        text: TocLanguage.btnMove,
        iconCls:'icon-move-record',
        handler: function() {thisObj.onBathMove();}
      }
    ],
    beforePageText: TocLanguage.beforePageText,
    firstText: TocLanguage.firstText,
    lastText: TocLanguage.lastText,
    nextText: TocLanguage.nextText,
    prevText: TocLanguage.prevText,
    afterPageText: TocLanguage.afterPageText,
    refreshText: TocLanguage.refreshText,
    displayInfo: true,
    displayMsg: TocLanguage.displayMsg,
    emptyMsg: TocLanguage.emptyMsg,
    prevStepText: TocLanguage.prevStepText,
    nextStepText: TocLanguage.nextStepText
  });
  Toc.layout.layoutGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.layout.layoutGrid, Ext.grid.GridPanel, {

  onAdd: function () {
    var dlg = this.owner.createlayoutDialog();
    
    dlg.on('saveSuccess', function() {
      this.mainPanel.getlayoutTree().refresh();
    }, this);
        
    dlg.show(null, this.mainPanel.getlayoutTree().getlayoutPath(null));
  },
  
  onEdit: function (record) {
    var dlg = this.owner.createlayoutDialog();
    var parent_id = this.mainPanel.getlayoutTree().getlayoutPath(null);
    dlg.setTitle(record.get('layout_name'));
    
    dlg.on('saveSuccess', function() {
      this.mainPanel.getlayoutTree().refresh();
    }, this);
    
    dlg.show(record.get('layout_id'),parent_id);
  },
  
  onMove: function (record) {
    var dlg = this.owner.createlayoutMoveDialog();
    dlg.setTitle('<?php echo $osC_Language->get("action_heading_batch_move_layout"); ?>');

    dlg.on('saveSuccess', function() {
      this.mainPanel.getlayoutTree().refresh();
    }, this);
    
    dlg.show(record.get('layout_id'), this.mainPanel.getlayoutTree().getlayoutPath());
  }, 
  
  onBathMove: function () {
    var keys = this.getSelectionModel().selections.keys;

    if (keys.length > 0) {
      var batch = keys.join(',');
      var dialog = this.owner.createlayoutMoveDialog();
      dialog.setTitle('<?php echo $osC_Language->get("action_heading_batch_move_layout"); ?>');

      dialog.on('saveSuccess', function() {
        this.mainPanel.getlayoutTree().refresh();
      }, this);
      
      dialog.show(batch);
    } else {
      Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
      }
  }, 
  
  onDelete: function (record) {
    var layoutId = record.get('layout_id');
    
    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle, 
      TocLanguage.msgDeleteConfirm, 
      function (btn) {
        if (btn == 'yes') {
          Ext.Ajax.request({
            waitMsg: TocLanguage.formSubmitWaitMsg,
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'layout',
              action: 'delete_category',
              layout_id: layoutId
            },
            callback: function (options, success, response) {
              var result = Ext.decode(response.responseText);
              
              if (result.success == true) {
                this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                
                this.mainPanel.getlayoutTree().refresh();
              } else {
                Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
              }
            },
            scope: this
          });
        }
      }, 
      this
    );
  },
  
  onBatchDelete: function () {
    var keys = this.getSelectionModel().selections.keys;
    if (keys.length > 0) {
      batch = keys.join(',');
      
      Ext.MessageBox.confirm(
        TocLanguage.msgWarningTitle, 
        TocLanguage.msgDeleteConfirm, 
        function (btn) {
          if (btn == 'yes') {
            Ext.Ajax.request({
              waitMsg: TocLanguage.formSubmitWaitMsg,
              url: Toc.CONF.CONN_URL,
              params: {
                module: 'layout',
                action: 'delete_layout',
                batch: batch
              },
              callback: function (options, success, response) {
                result = Ext.decode(response.responseText);
                
                if (result.success == true) {
                  this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                  
                  this.mainPanel.getlayoutTree().refresh();
                } else {
                  Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
                }
              },
              scope: this
            });
          }
        }, 
        this
      );
    } else {
      Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
    }
  },
    
  onRowAction: function (grid, record, action, row, col) {
    switch (action) {
      case 'icon-delete-record':
        this.onDelete(record);
        break;
      case 'icon-edit-record':
        this.onEdit(record);
        break;
      case 'icon-move-record':
        this.onMove(record);
        break;
    }
  },

  onSearch: function () {
    var filter = this.search.getValue() || null;
    var store = this.getStore();
    store.baseParams['search'] = filter;
    
    store.reload();
  },
  
  refreshGrid: function (layoutId) {
    var store = this.getStore();

    store.baseParams['layout_id'] = layoutId;
    store.load();
  },

  onGrdRowDbClick: function () {
    var layoutId = this.getSelectionModel().getSelected().get('layout_id');
    this.mainPanel.getlayoutTree().setCategoryId(layoutId);
  },
  
  onClick: function(e, target) {
    var t = e.getTarget();
    var v = this.view;
    var row = v.findRowIndex(t);
    var action = false;
  
    if (row !== false) {
      var btn = e.getTarget(".img-button");
      
      if (btn) {
        action = btn.className.replace(/img-button btn-/, '').trim();
      }

      if (action != 'img-button') {
        var layoutId = this.getStore().getAt(row).get('layout_id');
        var module = 'set_status';
        
        switch(action) {
          case 'status-off':
            flag = (action == 'status-on') ? 1 : 0;
            
            Ext.MessageBox.confirm(
              TocLanguage.msgWarningTitle, 
              TocLanguage.msgDisableProducts, 
              function (btn) {
                if (btn == 'no') {
                  this.onAction(module, layoutId, flag, 0);
                } else{
                  this.onAction(module, layoutId, flag, 1);
                }
              }, 
              this
            );  
            
            break;               
          case 'status-on':
            flag = (action == 'status-on') ? 1 : 0;
            
			      this.onAction(module, layoutId, flag, 0);
			      break;         
        }
      }
    }
  },
  
  onAction: function(action, layoutId, flag, product_flag) {
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'layout',
        action: action,
        layout_id: layoutId,
        flag: flag,
        product_flag: product_flag
      },
      callback: function(options, success, response) {
        var result = Ext.decode(response.responseText);
        
        if (result.success == true) {
          var store = this.getStore();
          store.getById(layoutId).set('status', flag);
          store.commitChanges();
          
          this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
        }
        else {
          this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
        }
      },
      scope: this
    });
  }
});
 