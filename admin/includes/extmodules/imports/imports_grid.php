<?php
?>
Toc.imports.importsGrid = function(config) {
  var that = this;
  config = config || {};

  config.region = 'center';
  config.started = false;
  config.border = false;
  config.loadMask = true;
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};

  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'categories',
      action: 'list_imports',
      customers_id : 0
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'imports_id'
    },[
      'imports_id',
      'when',
      'icon',
      'action',
      'status',
      'comments',
      'plant',
      'file'
    ]),
    listeners: {
            load: function (store, records, opt) {
                //console.log('load ...');
                //setTimeout(that.refreshData(that), 10000);
            },
            beforeload: function (store, opt) {
                //console.log('beforeload ...');
                //return that.started;
            }, scope: that
        },
    autoLoad: false
  });

  config.rowActions = new Ext.ux.grid.RowActions({
    tpl: new Ext.XTemplate(
      '<div class="ux-row-action">'
      +'<tpl for="action">'
      +'<div class="ux-row-action-item {class}" qtip="{qtip}"></div>'
      +'</tpl>'
      +'</div>'
    ),
    actions:['','',''],
    widthIntercept: Ext.isSafari ? 4 : 2
  });
  config.rowActions.on('action', this.onRowAction, this);

  config.sm = new Ext.grid.CheckboxSelectionModel();
  config.plugins = config.rowActions;

  config.cm = new Ext.grid.ColumnModel([
    config.sm,
    {header: '', dataIndex: 'icon', width : 24},
    {header: 'Date', dataIndex: 'when', width : 115},
    {id: 'file', header: 'Fichier', dataIndex: 'file', width : 400},
    {id : 'comments',header: 'Commentaires', dataIndex: 'comments'},
    config.rowActions
  ]);
  config.autoExpandColumn = 'comments';
  config.rowActions.on('action', this.onRowAction, this);

  config.customersCombo = Toc.content.ContentManager.getCustomersCombo({autoLoad : true,all : true});
  config.search = new Ext.form.TextField({name: 'search', width: 130});
  config.start_date = new Ext.form.DateField({fieldLabel: 'Debut', name: 'start_date', allowBlank: false});
  config.end_date = new Ext.form.DateField({fieldLabel: 'Fin', name: 'end_date', allowBlank: false});

  config.tbar = [
    {
      text: '',
      iconCls: 'refresh',
      handler: this.onRefresh,
      scope: this
    },
    '-',
    {
      text: '',
      iconCls: this.started ? 'stop' : 'play',
      handler: this.started ? this.onStop : this.onStart,
      scope: this
    },
    '->',
    config.customersCombo,
    '-',
    config.start_date,
    '-',
    config.end_date,
    '-',
    config.search,
    '',
    {
      iconCls: 'search',
      handler: this.onSearch,
      scope: this
   }];

  config.customersCombo.getStore().on('load', function (store, opt) {
//    that.onRefresh();
  });

  config.start_date.setValue(new Date());
  config.end_date.setValue(new Date());

  config.customersCombo.getStore().load();

  config.customersCombo.on('select', function (combo, record, index) {
    that.customers_id = record.data.customers_id;
    //that.refreshGrid(customers_id);
  });

  config.bbar = new Ext.PagingToolbar({
    pageSize: Toc.CONF.GRID_PAGE_SIZE,
    store: config.ds,
    iconCls: 'icon-grid',
    displayInfo: true,
    displayMsg: TocLanguage.displayMsg,
    emptyMsg: TocLanguage.emptyMsg
  });

  this.addEvents({'selectchange' : true});

  Toc.imports.importsGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.imports.importsGrid, Ext.grid.GridPanel, {

  onDelete: function(record) {
    var importsId = record.get('imports_id');

    Ext.Msg.confirm(
      TocLanguage.msgWarningTitle,
      TocLanguage.msgDeleteConfirm,
      function(btn) {
        if (btn == 'yes') {
          Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'categories',
              action: 'delete_parameter',
              imports_id: importsId
            },
            callback: function(options, success, response) {
              result = Ext.decode(response.responseText);

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
      }, this);
  },

  onDownload: function (record) {
    var url = record.get('url');
console.log(url);
    params = "height=300px,width=340px,top=50px,left=165px,status=yes,toolbar=no,menubar=no,location=no,scrollbars=yes";
    //console.log('downloading file....url : ' + url);
    window.open(url, "",params);
  },

  onRefresh: function() {
    var store = this.getStore();
    store.reload();
  },

  refreshGrid: function (customers_id) {
    if(customers_id)
    {
        var store = this.getStore();

        store.baseParams['customers_id'] = customers_id;
        store.reload();
    }
  },

  onSearch: function () {
    var store = this.getStore();

    if(this.customers_id)
    {
      store.baseParams['customers_id'] = this.customers_id;
      store.baseParams['start_date'] = this.start_date.getValue();
      store.baseParams['end_date'] = this.end_date.getValue();
      store.reload();
    }
    else
    {
      Ext.Msg.alert(TocLanguage.msgErrTitle,"Vous devez selectionner un Client");
    }
  },

  onRowAction: function(grid, record, action, row, col) {
    switch(action) {
      case 'icon-delete-record':
      this.onDelete(record);
      break;

      case 'icon-download-record':
        this.onDownload(record);
        break;
    }
  },

  refreshData: function (scope) {
     if (scope) {
       var store = this.getStore();
       store.load();
     }
  },
  onStart: function () {
        var that = this;

        this.started = true;
        this.refreshData(this);
        this.topToolbar.items.items[2].setHandler(this.onStop, this);
        this.topToolbar.items.items[2].setIconClass('stop');
    },
    onStop: function () {
        this.started = false;
        this.refreshData(this);
        this.topToolbar.items.items[2].setHandler(this.onStart, this);
        this.topToolbar.items.items[2].setIconClass('play');
    }
});