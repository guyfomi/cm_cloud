<?php
/*
  $Id: tasks_grid.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.tasks.TasksGrid = function(config) {
  var that = this;
  config = config || {};
  config.region = 'center';
  config.loadMask = true;
  config.border = false;
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};
  
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'tasks',
      action: 'list_tasks'
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'tasks_id'
    }, [
      'tasks_id',
      'tasks_status',
      'tasks_order',
      'tasks_name',
      'can_modify',
      'can_publish'
    ]),
    autoLoad: true
  }); 
  
  config.rowActions = new Ext.ux.grid.RowActions({
    actions:[
      {iconCls: 'icon-edit-record', qtip: TocLanguage.tipEdit,hideIndex : 'can_modify'},
      {iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete,hideIndex : 'can_modify'}],
    widthIntercept: Ext.isSafari ? 4 : 2
  });
  config.rowActions.on('action', this.onRowAction, this);    
  config.plugins = config.rowActions;
  
  renderPublish = function(status) {
<!--    console.log('............this');-->
<!--    console.dir(this);-->
<!--    console.log('............that');-->
<!--    console.dir(that);-->
    var currentRow = that.store.data.items[0];
    var json = currentRow.json;
    switch(json.can_publish)
    {
        case undefined:
        case 'undefined':
        case '0':
        case 0:
        if(status == 1) {
           return '<img src="images/icon_status_green.gif"/>&nbsp;<img src="images/icon_status_red_light.gif"/>';
        }else {
           return '<img src="images/icon_status_green_light.gif"/>&nbsp;<img src="images/icon_status_red.gif"/>';
        }
        case '1':
        case 1:
           if(status == 1) {
               return '<img class="img-button" src="images/icon_status_green.gif"/>&nbsp;<img class="img-button btn-status-off" style="cursor: pointer"src="images/icon_status_red_light.gif"/>';
           }else {
               console.log('c bon...............');
               return '<img class="img-button btn-status-on" style="cursor: pointer" src="images/icon_status_green_light.gif"/>&nbsp;<img class="img-button" src="images/icon_status_red.gif"/>';
        }
    }
  };
  
  config.sm = new Ext.grid.CheckboxSelectionModel();
  config.cm = new Ext.grid.ColumnModel([
    config.sm,
    { id: 'tasks_name', header: '<?php echo $osC_Language->get('table_heading_tasks'); ?>', dataIndex: 'tasks_name', sortable: true},
    { header: '<?php echo $osC_Language->get('table_heading_publish'); ?>', align: 'center', renderer: renderPublish, dataIndex: 'tasks_status'},
    { header: '<?php echo $osC_Language->get('table_heading_sort_order'); ?>', align: 'center', dataIndex: 'tasks_order', sortable: true},
    config.rowActions
  ]);
  config.autoExpandColumn = 'tasks_name';
  
  config.txtSearch = new Ext.form.TextField({
    width: 100,
    hideLabel: true
  });

  config.tbar = [
    {
      text: TocLanguage.btnAdd,
      iconCls:'add',
      handler: this.onAdd,
      scope: this
    },
    '-', 
    {
      text: TocLanguage.btnDelete,
      iconCls:'remove',
      handler: this.onBatchDelete,
      scope: this
    },
    '-',
    { 
      text: TocLanguage.btnRefresh,
      iconCls: 'refresh',
      handler: this.onRefresh,
      scope: this
    }, 
    '->', 
    config.txtSearch, 
    ' ',     
    { 
      text: '',
      iconCls: 'search',
      handler: this.onSearch,
      scope: this
    }
  ];
  
  var thisObj = this;
  config.bbar = new Ext.PageToolbar({
    pageSize: Toc.CONF.GRID_PAGE_SIZE,
    store: config.ds,
    steps: Toc.CONF.GRID_STEPS,
    btnsConfig:[
      {
        text: TocLanguage.btnAdd,
        iconCls:'add',
        handler: function() {
          thisObj.onAdd();
        }
      },
      {
        text: TocLanguage.btnDelete,
        iconCls:'remove',
        handler: function() {
          thisObj.onBatchDelete();
        }
      }
    ],
    beforePageText : TocLanguage.beforePageText,
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
  
  Toc.tasks.TasksGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.tasks.TasksGrid, Ext.grid.GridPanel, {
  
  onAdd: function() {
    var dlg = this.owner.createTasksDialog();
    var path = this.mainPanel.getCategoryPath();
    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);
    
    dlg.show(null, path);
  },

  setPermissions: function(permissions) {    
    this.bottomToolbar.items.items[0].disable();
    this.bottomToolbar.items.items[2].disable();

    this.topToolbar.items.items[0].disable();
    this.topToolbar.items.items[2].disable();
    if(permissions)
    {
        if(permissions.can_write == 1 || permissions.can_modify == '')
        {
            this.bottomToolbar.items.items[0].enable();
            this.topToolbar.items.items[0].enable();
        }
        if(permissions.can_modify == '')
        {
            this.bottomToolbar.items.items[2].enable();
            this.topToolbar.items.items[2].enable();
        }
    }
  },

  onEdit: function(record) {
    var dlg = this.owner.createTasksDialog();
    var path = this.mainPanel.getCategoryPath();
    dlg.setTitle(record.get("tasks_name"));
    
    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);
    
    dlg.show(record.get("tasks_id"),path);
  },
  
  onDelete: function(record) {
    var tasksId = record.get('tasks_id');
    
    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle, 
      TocLanguage.msgDeleteConfirm,
      function(btn) {
        if (btn == 'yes') {
          Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'tasks',
              action: 'delete_article',
              tasks_id: tasksId
            },
            callback: function(options, success, response) {
              var result = Ext.decode(response.responseText);
              
              if (result.success == true) {
                this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                this.getStore().reload();
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

  onBatchDelete: function() {
    var keys = this.selModel.selections.keys;
    
    if (keys.length > 0) {
      var batch = keys.join(',');
      
      Ext.MessageBox.confirm(
        TocLanguage.msgWarningTitle, 
        TocLanguage.msgDeleteConfirm,
        function(btn) {
          if (btn == 'yes') {
            Ext.Ajax.request({
              url: Toc.CONF.CONN_URL,
              params: {
                module: 'tasks',
                action: 'delete_tasks',
                batch: batch
              },
              callback: function(options, success, response) {
                var result = Ext.decode(response.responseText);
                
                if (result.success == true) {
                  this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                  this.getStore().reload();
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
    
  onRefresh: function() {
    this.getStore().reload();
  },

  refreshGrid: function (categoriesId) {
    var permissions = this.mainPanel.getCategoryPermissions();
    var store = this.getStore();

    store.baseParams['permissions'] = permissions.can_read + ',' + permissions.can_write + ',' + permissions.can_modify + ',' + permissions.can_publish;
    store.baseParams['categories_id'] = categoriesId;
    this.categoriesId = categoriesId;
    store.load();
  },

  onSearch: function() {
    var categoriesId = this.cboCategories.getValue() || null;
    var filter = this.txtSearch.getValue() || null;
    var store = this.getStore();
    
    store.baseParams['current_category_id'] = categoriesId;
    store.baseParams['search'] = filter;
    store.reload();
  },
  
  onRowAction: function(grid, record, action, row, col) {
    switch(action) {
      case 'icon-delete-record':
        this.onDelete(record);
        break;
      
      case 'icon-edit-record':
        this.onEdit(record);
        break;
    }
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
        var tasksId = this.getStore().getAt(row).get('tasks_id');
        var module = 'setStatus';
        
        switch(action) {
          case 'status-off':
          case 'status-on':
            flag = (action == 'status-on') ? 1 : 0;
            this.onAction(module, tasksId, flag);

            break;
        }
      }
    }
  },
  
  onAction: function(action, tasksId, flag) {
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'tasks',
        action: action,
        tasks_id: tasksId,
        flag: flag
      },
      callback: function(options, success, response) {
        var result = Ext.decode(response.responseText);
        
        if (result.success == true) {
          var store = this.getStore();
          store.getById(tasksId).set('tasks_status', flag);
          store.commitChanges();
          
          this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
        }
        else
          this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
      },
      scope: this
    });
  }  
});