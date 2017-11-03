Toc.ParametersGrid = function (config) {
    config = config || {};

    config.region = 'center';
    config.border = false;
    config.refresh = true;
    config.title = 'Parameters';
    config.loadMask = true;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords,forceFit : true};

    config.listeners = {
        activate: function (panel) {
            if(this.refresh)
            {
                this.onRefresh();
            }
        },
        show: function (panel) {
        },
        render: function (panel) {
            //console.log('render');
            //this.buildItems(5000);
            //this.onRefresh();
        },
        enable: function (panel) {
            //this.onRefresh();
        },
        deactivate: function (panel) {
            //console.log('deactivate');
        },
        destroy: function (panel) {
            //console.log('destroy');
        },
        disable: function (panel) {
            //console.log('disable');
        },
        remove: function (container,panel) {
            //console.log('remove');
        },
        removed: function (container,panel) {
            //console.log('removed');
        },
        scope: this
    };

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'categories',
            action: 'list_parameters',
            where : config.where || '',
            content_type : config.content_type,
            content_name : config.content_name,
            content_id : config.content_id,
            plant : config.plant || '',
            asset : config.asset || '',
            line : config.line || '',
            component : config.component || null
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'eventid'
        }, [
            'eventid',
            'eventdate',
            'plant',
            'line',
            'asset',
            'channel',
            'file',
            'measurement_state',
            'event_trigger_type',
            'monitoring_status',
            'operating_class',
            'xmlfile',
            'parameters_info'
        ]),
        autoLoad: false
    });

    config.rowActions = new Ext.ux.grid.RowActions({
        actions: [
        ],
        widthIntercept: Ext.isSafari ? 4 : 2
    });
    config.rowActions.on('action', this.onRowAction, this);

    config.plugins = [config.rowActions];

    config.cm = new Ext.grid.ColumnModel([
        {
            id: 'eventdate',
            header: 'Date',
            dataIndex: 'eventdate',
            width: 10
        },
        {
            id: 'plant',
            header: 'Plant',
            align : 'center',
            dataIndex: 'plant',
            width: 4
        },
        {
            id: 'line',
            header: 'Line',
            align : 'center',
            dataIndex: 'line',
            width: 4
        },
        {
            id: 'asset',
            header: 'Asset',
            align : 'center',
            dataIndex: 'asset',
            width: 8
        },
        {
            id: 'channel',
            header: 'Channel',
            align : 'center',
            dataIndex: 'channel',
            width: 5
        },
        {
            id: 'file',
            header: 'Fichier',
            dataIndex: 'file',
            width: 20
        },
        {
            id: 'status',
            header: 'Status',
            align : 'center',
            dataIndex: 'monitoring_status',
            width: 5
        },
        {
            id: 'trigger',
            header: 'Trigger',
            align : 'center',
            dataIndex: 'event_trigger_type',
            width: 5
        },
        {
            id: 'measurement_state',
            header: 'MeasurementState',
            align : 'center',
            dataIndex: 'measurement_state',
            width: 10
        },
        {
            id: 'operating_class',
            header: 'OperatingClass',
            align : 'center',
            dataIndex: 'operating_class',
            width: 8
        },
        {
            id: 'xmlfile',
            header: 'XML-File',
            align : 'center',
            dataIndex: 'xmlfile',
            width: 20
        }
    ]);
    config.selModel = new Ext.grid.RowSelectionModel({singleSelect: true});
    config.autoExpandColumn = 'eventdate';

    config.start_date = new Ext.form.DateField({fieldLabel: 'Debut', name: 'start_date', allowBlank: false});
    config.end_date = new Ext.form.DateField({fieldLabel: 'Fin', name: 'end_date', allowBlank: false});
    config.measurement_state = new Toc.MeasurementStateCombo({content_type : config.content_type,content_id : config.content_id});
    config.event_trigger_type = new Toc.EventTriggerTypeCombo({content_type : config.content_type,content_id : config.content_id});
    config.monitoring_status = new Toc.MonitoringStatusCombo({content_type : config.content_type,content_id : config.content_id});
    config.operating_class = new Toc.OperatingClassCombo({content_type : config.content_type,content_id : config.content_id});
    config.xmlfile = new Toc.XmlFileCombo({content_type : config.content_type,content_id : config.content_id});

    var thisObj = this;

    config.tbar = [
        {
            text: '',
            iconCls: 'refresh',
            handler: this.onRefresh,
            scope: this
        },
        '-',
        config.start_date,
        '-',
        config.end_date,
        '-',
        config.measurement_state,
        '-',
        config.event_trigger_type,
        '-',
        config.monitoring_status,
        '-',
        config.operating_class,
        '-',
        config.xmlfile,
        '->',
        {
            iconCls: 'search',
            handler: this.onSearch,
            scope: this
        }
    ];

    config.bbar = new Ext.PagingToolbar({
        pageSize: Toc.CONF.GRID_PAGE_SIZE,
        store: config.ds,
        iconCls: 'icon-grid',
        steps: Toc.CONF.GRID_STEPS,
        displayInfo: true,
        displayMsg: TocLanguage.displayMsg,
        emptyMsg: TocLanguage.emptyMsg
    });

    this.addEvents({'selectchange': true});

    Toc.ParametersGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.ParametersGrid, Ext.grid.GridPanel, {
    onRefresh: function () {
        var that = this;
        this.getStore().reload();

        this.measurement_state.getStore().on('beforeload', function () {
            that.getEl().mask('Chargement measurement_state....');
        }, this);

        this.measurement_state.getStore().on('load', function (store, records, options) {
            that.measurement_state.setValue(that.measurement_state.store.getAt(0).get(that.measurement_state.valueField));
            that.getEl().unmask();
        }, this);

        this.event_trigger_type.getStore().on('beforeload', function () {
            that.getEl().mask('Chargement event_trigger_type....');
        }, this);

        this.event_trigger_type.getStore().on('load', function (store, records, options) {
            that.event_trigger_type.setValue(that.event_trigger_type.store.getAt(0).get(that.event_trigger_type.valueField));
            that.getEl().unmask();
        }, this);

        this.monitoring_status.getStore().on('beforeload', function () {
            that.getEl().mask('Chargement monitoring_status....');
        }, this);

        this.monitoring_status.getStore().on('load', function (store, records, options) {
            that.monitoring_status.setValue(that.monitoring_status.store.getAt(0).get(that.monitoring_status.valueField));
            that.getEl().unmask();
        }, this);

        this.operating_class.getStore().on('beforeload', function () {
            that.getEl().mask('Chargement operating_class....');
        }, this);

        this.operating_class.getStore().on('load', function (store, records, options) {
            that.operating_class.setValue(that.operating_class.store.getAt(0).get(that.operating_class.valueField));
            that.getEl().unmask();
        }, this);

        this.xmlfile.getStore().on('beforeload', function () {
            that.getEl().mask('Chargement xmlfile....');
        }, this);

        this.xmlfile.getStore().on('load', function (store, records, options) {
            that.xmlfile.setValue(that.xmlfile.store.getAt(0).get(that.xmlfile.valueField));
            that.getEl().unmask();
        }, this);

        this.measurement_state.getStore().load();
        this.event_trigger_type.getStore().load();
        this.monitoring_status.getStore().load();
        this.operating_class.getStore().load();
        this.xmlfile.getStore().load();
        this.refresh = false;
    },

    refreshGrid: function (categoryId) {
        if (categoryId > 0) {
            var store = this.getStore();

            store.baseParams['categoryId'] = categoryId;
            store.reload();
        }
    },

    onSearch: function () {
        var store = this.getStore();

        store.baseParams['start_date'] = this.start_date.getValue();
        store.baseParams['end_date'] = this.end_date.getValue();
        store.baseParams['measurement_state'] = this.measurement_state.getValue();
        store.baseParams['event_trigger_type'] = this.event_trigger_type.getValue();
        store.baseParams['monitoring_status'] = this.monitoring_status.getValue();
        store.baseParams['operating_class'] = this.operating_class.getValue();
        store.baseParams['xmlfile'] = this.xmlfile.getValue();

        this.mainPanel.filter = true;
        this.mainPanel.win.setTitle('Asset Explorer (Filtre actif)');
        //this.mainPanel.owner.title = 'Asset Explorer (Filtre actif)';
        //this.mainPanel.owner.doLayout();

        this.mainPanel['start_date'] = this.start_date.getValue();
        this.mainPanel['end_date'] = this.end_date.getValue();
        this.mainPanel['measurement_state'] = this.measurement_state.getValue();
        this.mainPanel['event_trigger_type'] = this.event_trigger_type.getValue();
        this.mainPanel['monitoring_status'] = this.monitoring_status.getValue();
        this.mainPanel['operating_class'] = this.operating_class.getValue();
        this.mainPanel['xmlfile'] = this.xmlfile.getValue();
        //console.debug(this.mainPanel);

        store.reload();
    }
});

Toc.ExportCustomer = function (customers_id,customers_name, caller) {
    if(this.getEl)
    {
        this.getEl().mask();
    }
    Ext.Ajax.request({
        url: Toc.CONF.CONN_URL,
        params: {
            module: 'categories',
            action: 'export_customer',
            customers_id: customers_id,
            customers_name: customers_name
        },
        callback: function (options, success, response) {
            var result = Ext.decode(response.responseText);

            if (result.success == true) {
                if(this.getEl)
                {
                    this.getEl().unmask();
                }

                url = result.file_name;
                params = "height=300px,width=340px,top=50px,left=165px,status=yes,toolbar=no,menubar=no,location=no,scrollbars=yes";
                window.open(url, "", params);
            } else {
                Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
            }
        },
        scope: this
    });
};

Toc.InfosGrid = function (config) {
    //console.debug(config);
    var that = this;
    config = config || {};
    config.started = false;
    //config.region = 'south';
    config.loadMask = false;
    config.header = false;
    config.layout = 'fit';
    config.border = true;
    config.count = 0;
    config.reqs = 0;
    config.hideHeaders = true;
    //config.columnLines = false;
    //config.hideHeaders = true;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords, forceFit: true};

    config.listeners = {
        activate: function (panel) {
            //console.log('InfosGrid activate');
        },
        show: function (panel) {
            //console.log('InfosGrid show');
            this.onStart();
            //this.onRefresh();
        },
        render: function (panel) {
            //console.log('InfosGrid render');
            //this.buildItems(5000);
            //this.onRefresh();
        },
        enable: function (panel) {
            //console.log('InfosGrid enable');
            //this.onRefresh();
        },
        deactivate: function (panel) {
            //console.log('InfosGrid deactivate');
            this.onStop();
        },
        destroy: function (panel) {
            //console.log('InfosGrid destroy');
            this.onStop();
        },
        disable: function (panel) {
            //console.log('InfosGrid disable');
            this.onStop();
        },
        remove: function (container, panel) {
            //console.log('InfosGrid remove');
            this.onStop();
        },
        removed: function (container, panel) {
            //console.log('InfosGrid removed');
            this.onStop();
        },
        hide: function (container, panel) {
            //console.log('InfosGrid hide');
            this.onStop();
        },
        scope: this
    };

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'categories',
            action: this.action,
            content_type: this.content_type,
            content_name: this.content_name,
            plant: this.plant,
            line: this.line,
            component: this.component,
            asset: this.asset
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'var'
        }, [
            'var',
            'icon',
            'val'
        ]),
        listeners: {
            load: function (store, records, opt) {
                that.reqs--;
                if(that && that.started)
                {
                    if(that.count == 0)
                    {
                        var interval = setInterval(function(){
                            store.load();
                        }, that.freq || 2000);

                        //setTimeout(that.refreshData, that.freq || 10000);
                        that.count++;
                        that.interval = interval;
                    }
                    else
                    {
                    }
                }
            },
            beforeload: function (store, opt) {
                if(that.reqs == 0)
                {
                    if(that.started)
                    {
                        that.reqs++;
                    }
                }
                else
                {
                    return false;
                }
                return that.started;
            }, scope: that
        },
        autoLoad: false
    });

    render = function (row) {
        return '<div style = "white-space : normal">' + row + '</div>';
    };

    config.cm = new Ext.grid.ColumnModel([
        { id: 'icon', header: '', dataIndex: 'icon', width: 5},
        { id: 'var', header: '', dataIndex: 'var', width: 50},
        { id: 'val', header: '', dataIndex: 'val', width: 45}
    ]);
    config.autoExpandColumn = 'var';

    var thisObj = this;

    config.tbar = [
        {
            text: '',
            iconCls: 'refresh',
            handler: this.onRefresh,
            scope: this
        }
    ];

    Toc.InfosGrid.superclass.constructor.call(this, config);
    //this.getView().scrollOffset = 0;
};

Ext.extend(Toc.InfosGrid, Ext.grid.GridPanel, {
    refreshData: function (scope) {
        if (scope) {
            var store = this.getStore();
            store.load();
        }
    },
    onStart: function () {
        this.count = 0;
        this.reqs = 0;
        this.started = true;
        this.refreshData(this);
    },
    onStop: function () {
        this.started = false;
        this.count = 10;
        this.reqs = 10;
        this.refreshData(this);

        if (this.interval) {
            clearInterval(this.interval);
        }
        else {
            //Ext.MessageBox.alert(TocLanguage.msgErrTitle,"No job defined !!!");
        }
    },
    onRefresh: function () {
        this.onStart();
        this.onStop();
    }
});

Toc.sensorsCombo = function (config) {

    var dsCombo = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'categories',
            action: 'list_sensors',
            content_type : config.content_type,
            content_id : config.content_id
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'sensors_id'
        }, [
            'sensors_id',
            'name'
        ]),
        autoLoad: false
    });

    return new Ext.form.ComboBox({
        fieldLabel: 'Sensor',
        store: dsCombo,
        displayField: 'name',
        valueField: 'sensors_id',
        hiddenName: 'sensors_id',
        name: 'sensors',
        mode: 'local',
        width: 350,
        //value : 5000,
        disabled: false,
        readOnly: true,
        triggerAction: 'all',
        forceSelection: true,
        allowBlank: false
    });
};

Toc.ParameterInfosGrid = function (config) {
    var that = this;
    config = config || {};
    config.started = false;
    //config.region = 'south';
    config.loadMask = true;
    config.header = false;
    config.layout = 'fit';
    config.border = true;
    config.hideHeaders = true;
    //config.columnLines = false;
    //config.hideHeaders = true;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords, forceFit: true};

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'categories',
            action: 'load_parameter',
            content_id : config.content_id
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'var'
        }, [
            'var',
            'icon',
            'val'
        ]),
        listeners: {
            scope: that
        },
        autoLoad: true
    });

    render = function (row) {
        return '<div style = "white-space : normal">' + row + '</div>';
    };

    config.cm = new Ext.grid.ColumnModel([
        { id: 'icon', header: '', dataIndex: 'icon', width: 5},
        { id: 'var', header: '', dataIndex: 'var', width: 50},
        { id: 'val', header: '', dataIndex: 'val', width: 45}
    ]);
    config.autoExpandColumn = 'var';

    Toc.ParameterInfosGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.ParameterInfosGrid, Ext.grid.GridPanel, {

});