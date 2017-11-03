<?php
?>
Toc.tickets.ticketsGrid = function(config) {
  var that = this;
  config = config || {};

  config.region = 'center';
  config.started = false;
  config.border = false;
  config.loadMask = true;
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords,forceFit : true};

  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'content',
      action: 'list_tickets',
      customers_id : 0
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'events_id'
    },[
      'icon',
      'events_id',
      'event_date',
      'content_id',
      'content_type',
      'type',
      'source',
      'user',
      'description',
      'category'
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

  var expander = new Ext.grid.RowExpander({
    tpl : new Ext.Template('{tickets_info}')
  });

  config.sm = new Ext.grid.CheckboxSelectionModel();
  config.plugins = [config.rowActions, expander];

  config.cm = new Ext.grid.ColumnModel([
    expander,
    config.sm,
    {header: '', dataIndex: 'icon', width : 3,align : 'center'},
    {header: 'Date', dataIndex: 'event_date', width : 10,align : 'center'},
    {id: 'description', header: 'Description', dataIndex: 'description', width : 75},
    {id : 'user',header: 'Utilisateur', dataIndex: 'user', width : 10,align : 'center'}
  ]);
  config.autoExpandColumn = 'description';

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

  Toc.tickets.ticketsGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.tickets.ticketsGrid, Ext.grid.GridPanel, {

  onRefresh: function() {
    var store = this.getStore();
    store.reload();
  },

  refreshGrid: function (customers_id) {
    var store = this.getStore();

    store.baseParams['customers_id'] = customers_id || -1;
    store.reload();
  },

  onSearch: function () {
    var store = this.getStore();

    store.baseParams['customers_id'] = this.customers_id || -1;
    store.baseParams['start_date'] = this.start_date.getValue();
    store.baseParams['end_date'] = this.end_date.getValue();
    store.reload();
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
  },
  onClick: function(e, target) {
    var t = e.getTarget();
    var v = this.view;
    var row = v.findRowIndex(t);
    var col = v.findCellIndex(t);
    var action = false;

    if (row !== false) {
      var expander = e.getTarget(".x-grid3-row-body");

      if (col > 0 || (col == false && expander != null)) {
        var record = this.getStore().getAt(row);
        this.fireEvent('selectchange', record);
      }

      var btn = e.getTarget(".img-button");

      if (btn) {
        action = btn.className.replace(/img-button btn-/, '').trim();
        var customersId = this.getStore().getAt(row).get('customers_id');

        switch(action) {
          case 'status-off':
          case 'status-on':
            flag = (action == 'status-on') ? 1 : 0;
            this.setStatus(customersId, flag);
            break;
        }
      }
    }
  }
});