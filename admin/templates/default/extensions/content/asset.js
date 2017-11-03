Toc.AssetPanel = function (config) {
    config = config || {};

    //config.title = 'General';
    config.layout = 'fit';
    config.deferredRender = false;
    config.items = this.getDataPanel();

    Toc.AssetPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.AssetPanel, Ext.Panel, {

    getDataPanel: function () {
        this.CPMS_TypeCombo = new Toc.CpmsTypeCombo({});
        this.CpmsSlotCombo = new Toc.CpmsSlotCombo({});
        this.EquipmentypeCombo = new Toc.EquipmentypeCombo({});
        this.UsersCombo = new Toc.content.ContentManager.getUsersCombo({fieldLabel: "Responsible", autoLoad: false});
        this.ConfigurationCombo = new Toc.ConfigurationCombo({});
        this.PowerSourceCombo = new Toc.PowerSourceCombo({});

        this.code = new Ext.form.TextField({fieldLabel: 'Code', name: 'code', allowBlank: false});
        this.name = new Ext.form.TextField({fieldLabel: 'Name', name: 'name', allowBlank: false});
        this.cpms_ip = new Ext.form.TextField({fieldLabel: 'CPMS_IP', name: 'cpms_ip', allowBlank: true});
        this.cpms_mac = new Ext.form.TextField({fieldLabel: 'CPMS_MAC', name: 'cpms_mac', allowBlank: true});

        this.cpms_controller = new Ext.form.TextField({fieldLabel: 'CPMS_Controller', name: 'cpms_controller', allowBlank: true});
        //this.cpms_configurator = new Ext.form.TextField({fieldLabel: 'CPMS_Configurator', name: 'cpms_configurator', allowBlank: true});
        this.location = new Ext.form.TextField({fieldLabel: 'Location', name: 'location', allowBlank: true});
        this.manufacturer = new Ext.form.TextField({fieldLabel: 'Manufacturer', name: 'manufacturer', allowBlank: true});
        this.model = new Ext.form.TextField({fieldLabel: 'Model', name: 'model', allowBlank: true});

        this.fonction = new Ext.form.TextField({fieldLabel: 'Function', name: 'fonction', allowBlank: true});
        this.norms = new Ext.form.TextField({fieldLabel: 'Norms', name: 'norms', allowBlank: true});

        this.support = new Ext.form.TextField({fieldLabel: 'Support', name: 'support', allowBlank: false});
        this.coupling = new Ext.form.TextField({fieldLabel: 'Coupling', name: 'coupling', allowBlank: true});
        this.ratedpower_w = new Ext.form.NumberField({fieldLabel: 'RatedPower_W', name: 'ratedpower_w', allowBlank: true});
        this.ratedspeed_rpm = new Ext.form.NumberField({fieldLabel: 'RatedSpeed_rpm', name: 'ratedspeed_rpm', allowBlank: true});
        this.ratedtorque_nm = new Ext.form.NumberField({fieldLabel: 'RatedTorque_Nm', name: 'ratedtorque_nm', allowBlank: true});
        this.ratedvoltage_v = new Ext.form.NumberField({fieldLabel: 'RatedVoltage_V', name: 'ratedvoltage_v', allowBlank: true});
        this.ratedcurrent_a = new Ext.form.NumberField({fieldLabel: 'RatedCurrent_A', name: 'ratedcurrent_a', allowBlank: true});
        this.minspeed_rpm = new Ext.form.NumberField({fieldLabel: 'MinSpeed_rpm', name: 'minspeed_rpm', allowBlank: true});
        this.maxspeed_rpm = new Ext.form.NumberField({fieldLabel: 'MaxSpeed_rpm', name: 'maxspeed_rpm', allowBlank: true});
        this.tachochannel = new Toc.TachoChannelCombo({fieldLabel: 'Tachochannel', name: 'tachochannel', allowBlank: true});
        this.pulse_per_rev = new Ext.form.NumberField({fieldLabel: 'Pulse_Per_Rev', name: 'pulse_per_rev', allowBlank: true});
        this.triggerlevel = new Ext.form.TextField({fieldLabel: 'triggerlevel', name: 'triggerlevel', allowBlank: true});
        this.rotdir = new Toc.RotDirCombo();
        this.op1_name = new Ext.form.TextField({fieldLabel: 'OP1_name', name: 'op1_name', allowBlank: false});
        this.op2_name = new Ext.form.TextField({fieldLabel: 'OP2_name', name: 'op2_name', allowBlank: false});
        this.op3_name = new Ext.form.TextField({fieldLabel: 'OP3_name', name: 'op3_name', allowBlank: false});
        this.op4_name = new Ext.form.TextField({fieldLabel: 'OP4_name', name: 'op4_name', allowBlank: false});
        this.op5_name = new Ext.form.TextField({fieldLabel: 'OP5_name', name: 'op5_name', allowBlank: false});
        this.op6_name = new Ext.form.TextField({fieldLabel: 'OP6_name', name: 'op6_name', allowBlank: false});
        this.op7_name = new Ext.form.TextField({fieldLabel: 'OP7_name', name: 'op7_name', allowBlank: false});
        this.op8_name = new Ext.form.TextField({fieldLabel: 'OP8_name', name: 'op8_name', allowBlank: false});
        this.op9_name = new Ext.form.TextField({fieldLabel: 'OP9_name', name: 'op9_name', allowBlank: false});
        this.op10_name = new Ext.form.TextField({fieldLabel: 'OP10_name', name: 'op10_name', allowBlank: false});
        //this.op1_index = new Ext.form.TextField({fieldLabel: 'OP1_index', name: 'op1_index', allowBlank: false});
        //this.op2_index = new Ext.form.TextField({fieldLabel: 'OP2_index', name: 'op2_index', allowBlank: false});

        this.deltaprozent = new Toc.PercentCombo({fieldLabel: 'Deltaprozent', name: 'deltaprozent', allowBlank: true, value: 70, hiddenName: 'deltaprozent'});
        this.kmean = new Ext.form.TextField({fieldLabel: 'Kmean', name: 'kmean', allowBlank: false});
        this.kmin = new Ext.form.TextField({fieldLabel: 'Kmin', name: 'kmin', allowBlank: true});
        this.kmax = new Ext.form.TextField({fieldLabel: 'Kmax', name: 'kmax', allowBlank: true});
        this.kstd = new Ext.form.TextField({fieldLabel: 'Kstd', name: 'kstd', allowBlank: true});
        this.kral = new Ext.form.TextField({fieldLabel: 'krAL', name: 'kral', allowBlank: true});
        this.kal = new Ext.form.TextField({fieldLabel: 'kAL', name: 'kal', allowBlank: true});
        this.refwindow = new Ext.form.TextField({fieldLabel: 'RefWindow', name: 'refwindow', allowBlank: true});
        this.movingwindow = new Ext.form.TextField({fieldLabel: 'MovingWindow', name: 'movingwindow', allowBlank: true});
        this.movingdeltaanalyse = new Ext.form.TextField({fieldLabel: 'MovingDeltaanalyse', name: 'movingdeltaanalyse', allowBlank: true});
        this.severitylimit1 = new Ext.form.TextField({fieldLabel: 'SeverityLimit1', name: 'severitylimit1', allowBlank: true});
        this.severitylimit2 = new Ext.form.TextField({fieldLabel: 'SeverityLimit2', name: 'severitylimit2', allowBlank: true});
        this.severitylimit3 = new Ext.form.TextField({fieldLabel: 'SeverityLimit3', name: 'severitylimit3', allowBlank: true});
        this.severitylimit4 = new Ext.form.TextField({fieldLabel: 'SeverityLimit4', name: 'severitylimit4', allowBlank: true});

        this.pnlData = new Ext.Panel({
            layout: 'column',
            items: [
                {
                    columnWidth: .33,
                    layout: 'form',
                    border: false,
                    labelWidth: 125,
                    defaults: {
                        anchor: '97%'
                    },
                    autoHeight: true,
                    style: 'padding: 2px',
                    items: [
                        this.code,
                        this.name,
                        this.cpms_ip,
                        this.cpms_mac,
                        this.CPMS_TypeCombo,
                        this.CpmsSlotCombo,
                        this.cpms_controller,
                        this.location,
                        this.manufacturer,
                        this.model,
                        this.EquipmentypeCombo,
                        this.UsersCombo,
                        this.ConfigurationCombo,
                        this.fonction,
                        this.norms,
                        this.PowerSourceCombo,
                        this.support,
                        this.coupling
                    ]
                },
                {
                    columnWidth: .33,
                    layout: 'form',
                    border: false,
                    labelWidth: 125,
                    defaults: {
                        anchor: '97%'
                    },
                    autoHeight: true,
                    style: 'padding: 2px',
                    items: [
                        this.ratedpower_w,
                        this.ratedspeed_rpm,
                        this.ratedtorque_nm,
                        this.ratedvoltage_v,
                        this.ratedcurrent_a,
                        this.minspeed_rpm,
                        this.maxspeed_rpm,
                        this.tachochannel,
                        this.pulse_per_rev,
                        this.triggerlevel,
                        this.rotdir,
                        this.deltaprozent,
                        this.kmean,
                        this.kmin,
                        this.kmax,
                        this.kstd,
                        this.kral,
                        this.kal
                    ]
                },
                {
                    columnWidth: .34,
                    layout: 'form',
                    border: false,
                    labelWidth: 125,
                    defaults: {
                        anchor: '97%'
                    },
                    autoHeight: true,
                    style: 'padding: 2px',
                    items: [
                        this.refwindow,
                        this.movingwindow,
                        this.movingdeltaanalyse,
                        this.severitylimit1,
                        this.severitylimit2,
                        this.severitylimit3,
                        this.severitylimit4,
                        this.op1_name,
                        this.op2_name,
                        this.op3_name,
                        this.op4_name,
                        this.op5_name,
                        this.op6_name,
                        this.op7_name,
                        this.op8_name,
                        this.op9_name,
                        this.op10_name
                    ]
                }
            ]
        });

        return this.pnlData;
    }
});

Toc.AssetDialog = function (config) {
    config = config || {};

    config.id = 'asset-dialog-win';
    config.title = 'New Asset';
    config.layout = 'fit';
    config.width = 900;
    config.height = 600;
    config.modal = true;
    config.iconCls = 'icon-asset-win';
    config.items = this.buildForm();

    config.buttons = [
        {
            text: TocLanguage.btnSave,
            handler: function () {
                this.submitForm();
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

    Toc.AssetDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.AssetDialog, Ext.Window, {

    show: function () {
        if (this.lines_id == -1) {
            Ext.Msg.alert(TocLanguage.msgErrTitle, "Ligne invalide !!!");
        }
        else {
            this.frmAsset.form.reset();
            this.frmAsset.form.baseParams['lines_id'] = this.lines_id;

            //this.pnlPermissions = new Toc.content.PermissionsPanel({content_id : this.layoutId,content_type : 'pages',owner : this.owner});
            //this.pnlComments =  new Toc.content.CommentsPanel({content_id : this.layoutId,content_type : 'pages',owner : Toc.content.ContentManager});
            //this.tablayout.add(this.pnlPermissions);
            //this.tablayout.add(this.pnlComments);
            //this.tablayout.add(this.pnlAdresse);

            Toc.AssetDialog.superclass.show.call(this);

            if (this.asset_id) {
                this.loadAsset(this.pnlGeneral);
            }
            else {
                this.pnlGeneral.CPMS_TypeCombo.getStore().load();
                this.pnlGeneral.CpmsSlotCombo.getStore().load();
                this.pnlGeneral.EquipmentypeCombo.getStore().load();
                this.pnlGeneral.UsersCombo.getStore().load();
                this.pnlGeneral.ConfigurationCombo.getStore().load();
                this.pnlGeneral.PowerSourceCombo.getStore().load();

                this.pnlGeneral.rotdir.getStore().load();
                this.pnlGeneral.deltaprozent.getStore().load();
                this.pnlGeneral.tachochannel.getStore().load();
            }
        }
    },

    loadAsset: function (panel) {
        if (this.asset_id) {
            this.frmAsset.form.baseParams['asset_id'] = this.asset_id;
            if (panel) {
                panel.getEl().mask('Chargement Asset en cours....');
            }
            this.frmAsset.load({
                    url: Toc.CONF.CONN_URL,
                    params: {
                        action: 'load_asset'
                    },
                    success: function (form, action) {
                        this.pnlGeneral.CPMS_TypeCombo.getStore().on('load', function () {
                            this.pnlGeneral.CPMS_TypeCombo.setValue(action.result.data.cpms_type);
                            this.getEl().unmask();
                        }, this);

                        this.pnlGeneral.CPMS_TypeCombo.getStore().load();

                        this.pnlGeneral.CpmsSlotCombo.getStore().on('load', function () {
                            this.pnlGeneral.CpmsSlotCombo.setValue(action.result.data.cpms_slotnumber);
                            this.getEl().unmask();
                        }, this);

                        this.pnlGeneral.CpmsSlotCombo.getStore().load();

                        this.pnlGeneral.EquipmentypeCombo.getStore().on('load', function () {
                            this.pnlGeneral.EquipmentypeCombo.setValue(action.result.data.equipmentype);
                            this.getEl().unmask();
                        }, this);

                        this.pnlGeneral.EquipmentypeCombo.getStore().load();

                        this.pnlGeneral.UsersCombo.getStore().on('load', function () {
                            this.pnlGeneral.UsersCombo.setValue(action.result.data.administrators_id);
                            this.getEl().unmask();
                        }, this);

                        this.pnlGeneral.UsersCombo.getStore().load();

                        this.pnlGeneral.ConfigurationCombo.getStore().on('load', function () {
                            this.pnlGeneral.ConfigurationCombo.setValue(action.result.data.configuration);
                            this.getEl().unmask();
                        }, this);

                        this.pnlGeneral.ConfigurationCombo.getStore().load();

                        this.pnlGeneral.PowerSourceCombo.getStore().on('load', function () {
                            this.pnlGeneral.PowerSourceCombo.setValue(action.result.data.powersource);
                            this.getEl().unmask();
                        }, this);

                        this.pnlGeneral.PowerSourceCombo.getStore().load();

                        if (panel) {
                            this.pnlPermissions = new Toc.content.PermissionsPanel({content_id: this.asset_id, content_type: 'asset', owner: this.owner});
                            this.pnlDocuments = new Toc.content.DocumentsPanel({content_id: this.asset_id, content_type: 'asset', owner: Toc.content.ContentManager});
                            this.tablayout.add(this.pnlDocuments);
                            this.tablayout.add(this.pnlPermissions);
                            panel.getEl().unmask();
                        }
                    },
                    failure: function (form, action) {
                        Ext.Msg.alert(TocLanguage.msgErrTitle, action.result.feedback);
                        if (panel) {
                            panel.getEl().unmask();
                        }

                        this.close();
                    },
                    scope: this
                },
                this
            );
        }
        else {
            Ext.Msg.alert(TocLanguage.msgErrTitle, "Aucun Asset selectionné !!!");
        }
    },

    buildForm: function () {
        this.pnlGeneral = new Toc.AssetPanel({lines_id: this.lines_id});

        this.tablayout = new Ext.TabPanel({
            activeTab: 0,
            defaults: {
                hideMode: 'offsets'
            },
            deferredRender: false,
            items: [this.pnlGeneral]
        });

        this.frmAsset = new Ext.form.FormPanel({
            layout: 'fit',
            fileUpload: true,
            labelWidth: 120,
            url: Toc.CONF.CONN_URL,
            baseParams: {
                module: 'categories',
                action: 'save_asset'
            },
            scope: this,
            items: this.tablayout
        });

        return this.frmAsset;
    },

    submitForm: function () {
        this.frmAsset.form.submit({
            waitMsg: TocLanguage.formSubmitWaitMsg,
            success: function (form, action) {
                if (action.result.success) {
                    this.fireEvent('saveSuccess', action.result.feedback);
                    this.close();
                }
                else {
                    Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
                }
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

Toc.DeleteAsset = function (asset_id, caller) {
    Ext.MessageBox.confirm(
        TocLanguage.msgWarningTitle,
        "Voulez-vous vraiment supprimer cet Asset ? Tous les sous elements seront egalement supprimés",
        function (btn) {
            if (btn == 'yes') {
                Ext.Ajax.request({
                    url: Toc.CONF.CONN_URL,
                    params: {
                        module: 'categories',
                        action: 'delete_asset',
                        asset_id: asset_id
                    },
                    callback: function (options, success, response) {
                        var result = Ext.decode(response.responseText);

                        if (result.success == true) {
                            //this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                            if (caller && caller.refresh) {
                                caller.refresh();
                            }
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
};

Toc.ExportAsset = function (asset_id, asset_name, caller) {
    if (this.getEl) {
        this.getEl().mask();
    }
    Ext.Ajax.request({
        url: Toc.CONF.CONN_URL,
        params: {
            module: 'categories',
            action: 'export_asset',
            asset_id: asset_id,
            asset_name: asset_name
        },
        callback: function (options, success, response) {
            var result = Ext.decode(response.responseText);

            if (result.success == true) {
                if (this.getEl) {
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

Toc.AssetExplorerPanel = function (config) {
    config = config || {};
    config.region = 'center';
    config.layout = 'fit';
    config.items = [];

    this.addEvents({'saveSuccess': true});

    Toc.AssetExplorerPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.AssetExplorerPanel, Ext.Panel, {

    refresh: function () {
        if (this.mainPanel) {
            if (this.node) {
                this.mainPanel.getAssetTree().setCategoryId(this.node.id);
            }
            else {
                Ext.Msg.alert(TocLanguage.msgErrTitle, "Aucun element selectionne !!!");
            }
        }
        else {
            Ext.Msg.alert(TocLanguage.msgErrTitle, "No mainPanel defined !!!");
        }
    }
});

Toc.exploreAsset = function (node, panel) {
    panel.mainPanel.win.setTitle('Asset Explorer');
    panel.mainPanel.filter = false;
    panel.mainPanel['start_date'] = null;
    panel.mainPanel['end_date'] = null;
    panel.mainPanel['measurement_state'] = null;
    panel.mainPanel['event_trigger_type'] = null;
    panel.mainPanel['monitoring_status'] = null;
    panel.mainPanel['operating_class'] = null;
    panel.mainPanel['xmlfile'] = null;

    panel.mainPanel.hideInfos();
    panel.removeAll();
    var pnlMap = null;
    var tab = null;

    if (node.id == 0) {
        pnlMap = new Toc.Map({customers_id: node.id, owner: Toc.content.ContentManager, title: 'Map'});

        tab = new Ext.TabPanel({
            activeTab: 0,
            defaults: {
                hideMode: 'offsets'
            },
            deferredRender: false,
            items: [pnlMap]
        });

        panel.add(tab);
        panel.doLayout();

        panel.mainPanel.hideInfos();
    }
    else {
        if (node.attributes.content_type == 'customer') {
            this.customers_id = node.id;
        }

        if (!this.customers_id) {
            var currentNode = node;
            while (currentNode) {
                if (currentNode.attributes.content_type == 'customer') {
                    this.customers_id = node.id;
                    currentNode = null;
                }
                else {
                    currentNode = currentNode.parentNode;
                }
            }
        }

        if (this.customers_id) {
            var pnlDashboard = null;
            var pnlParameters = null;
            var pnlDocuments = null;
            var grdImages = null;
            var parent = null;
            var line = null;
            var plant = null;
            var asset = null;
            var component = null;
            var pnl = null;
            var pnlAdresse = null;
            var grdeventlog = null;
            var grdTickets = null;

            if (node) {
                panel.node = node;
            }
            else {
                Ext.Msg.alert(TocLanguage.msgErrTitle, "Aucun element selectionne !!!");
                return false;
            }

            switch (node.attributes.content_type) {
                case 'sensor':
                    parent = node.parentNode;
                    component = parent.id;
                    asset = parent.parentNode.text.toLowerCase();
                    line = parent.parentNode.parentNode.text.toLowerCase();
                    plant = parent.parentNode.parentNode.parentNode.text.toLowerCase();

                    pnlDashboard = new Toc.DashboardPanel({mainPanel : panel.mainPanel,content_id: node.id, content_type: 'sensor', content_name: node.text.toLowerCase(), line: line, plant: plant, asset: asset, component: component});
                    pnlParameters = new Toc.ParametersGrid({mainPanel : panel.mainPanel,content_id: node.id, content_type: 'sensor', content_name: node.text.toLowerCase(), line: line, plant: plant, asset: asset, component: component});
                    pnlDocuments = new Toc.content.DocumentsPanel({content_id: node.id, content_type: 'sensor', owner: Toc.content.ContentManager, can_edit: false});
                    grdImages = new Toc.content.ImagesGrid({content_id: node.id, content_type: 'sensor', module: 'content', can_delete: false});
                    pnl = new Toc.SensorPanel({component_id: node.parentNode.id, title: 'Proprietes'});
                    grdeventlog = new Toc.eventlogGrid({owner: this.owner, mainPanel: panel.mainPanel, content_id: node.id, content_type: 'sensor', title: 'EventLog'});
                    grdTickets = new Toc.TicketsGrid({content_id: node.id, content_type: 'sensor', owner: this.owner, mainPanel: this});
                    //pnlComments = new Toc.content.CommentsPanel({content_id: node.id, content_type: 'sensor', owner: Toc.content.ContentManager});

                    tab = new Ext.TabPanel({
                        activeTab: 0,
                        defaults: {
                            hideMode: 'offsets'
                        },
                        deferredRender: false,
                        items: [pnlParameters, pnlDashboard, grdImages, pnl, pnlDocuments, grdeventlog, grdTickets]
                    });

                    pnlParameters.on('rowclick', function (grid, rowIndex, eventObject) {
                            var store = pnlParameters.getStore();
                            var record = store.getAt(rowIndex);
                            panel.mainPanel.hideInfos();
                            panel.mainPanel.showParameterInfos(record);
                            panel.mainPanel.doLayout();
                        }
                    );

                    panel.add(tab);
                    panel.doLayout();
                    panel.form.reset();
                    panel.form.baseParams['sensors_id'] = node.id;
                    panel.form.baseParams['component_id'] = node.parentNode.id;
                    panel.form.baseParams['module'] = 'categories';
                    panel.form.baseParams['action'] = 'save_sensor';

                    pnl.on('activate', function () {
                        pnl.getEl().mask('Chargement Sensor en cours....');

                        panel.load({
                                url: Toc.CONF.CONN_URL,
                                params: {
                                    action: 'load_sensor'
                                },
                                success: function (form, action) {
                                    if (tab && pnl) {
                                        pnl.code.setValue(action.result.data.code);
                                        pnl.code.disable();
                                        pnl.signalname.setValue(action.result.data.signalname);
                                        pnl.signalname.disable();
                                        pnl.cpms_ip.setValue(action.result.data.cpms_ip);
                                        pnl.cpms_ip.disable();
                                        pnl.manufacturer.setValue(action.result.data.manufacturer);
                                        pnl.manufacturer.disable();
                                        pnl.sensitivity.setValue(action.result.data.sensitivity);
                                        pnl.sensitivity.disable();
                                        pnl.sensitivity_unit.setValue(action.result.data.sensitivity_unit);
                                        pnl.sensitivity_unit.disable();
                                        pnl.ofset.setValue(action.result.data.offset);
                                        pnl.ofset.disable();
                                        pnl.time_analysis.setValue(action.result.data.time_analysis);
                                        pnl.time_analysis.disable();
                                        pnl.orbit_analysis.setValue(action.result.data.orbit_analysis);
                                        pnl.orbit_analysis.disable();
                                        pnl.fftanalysis.setValue(action.result.data.fftanalysis);
                                        pnl.fftanalysis.disable();
                                        pnl.order_analysis.setValue(action.result.data.order_analysis);
                                        pnl.order_analysis.disable();
                                        pnl.envelope_analysis.setValue(action.result.data.envelope_analysis);
                                        pnl.envelope_analysis.disable();

                                        pnl.overlap.setValue(action.result.data.overlap);
                                        pnl.overlap.disable();
                                        pnl.bpfu_env.setValue(action.result.data.bpfu_env);
                                        pnl.bpfu_env.disable();
                                        pnl.bpfo_env.setValue(action.result.data.bpfo_env);
                                        pnl.bpfo_env.disable();
                                        pnl.tpf_env.setValue(action.result.data.tpf_env);
                                        pnl.tpf_env.disable();

                                        pnl.measurement_range.setValue(action.result.data.measurement_range);
                                        pnl.measurement_range.disable();
                                        pnl.frequency_range.setValue(action.result.data.frequency_range);
                                        pnl.frequency_range.disable();
                                        pnl.temperaturerange.setValue(action.result.data.temperaturerange);
                                        pnl.temperaturerange.disable();
                                        pnl.impedance.setValue(action.result.data.impedance);
                                        pnl.impedance.disable();
                                        pnl.calibration_date.setValue(action.result.data.calibration_date);
                                        pnl.calibration_date.disable();
                                        pnl.serial_number.setValue(action.result.data.serial_number);
                                        pnl.serial_number.disable();
                                        pnl.component.setValue(action.result.data.component);
                                        pnl.component.disable();
                                        pnl.sensortypecode.setValue(action.result.data.sensortypecode);
                                        pnl.sensortypecode.disable();
                                        pnl.angle.setValue(action.result.data.angle);
                                        pnl.angle.disable();
                                        pnl.orientation.setValue(action.result.data.orientation);
                                        pnl.orientation.disable();
                                        pnl.motio.setValue(action.result.data.motion);
                                        pnl.motio.disable();
                                        pnl.attachment_method.setValue(action.result.data.attachment_method);
                                        pnl.attachment_method.disable();
                                        pnl.jonction_box.setValue(action.result.data.jonction_box);
                                        pnl.jonction_box.disable();
                                        pnl.acquisitionstation.setValue(action.result.data.acquisitionstation);
                                        pnl.acquisitionstation.disable();

                                        pnl.envelopetype.getStore().on('beforeload', function () {
                                            pnl.getEl().mask('Chargement envelopetype....');
                                        }, this);

                                        pnl.envelopetype.getStore().on('load', function () {
                                            pnl.envelopetype.setValue(action.result.data.envelopetype);
                                            pnl.envelopetype.disable();
                                            pnl.getEl().unmask();
                                        }, this);

                                        pnl.envelopetype.getStore().load();

                                        pnl.frfchannel_y.getStore().on('beforeload', function () {
                                            pnl.getEl().mask('Chargement frfchannel_y....');
                                        }, this);

                                        pnl.frfchannel_y.getStore().on('load', function () {
                                            pnl.frfchannel_y.setValue(action.result.data.frfchannel_y);
                                            pnl.frfchannel_y.disable();
                                            pnl.getEl().unmask();
                                        }, this);

                                        pnl.frfchannel_y.getStore().load();

                                        pnl.orbit_channel_y.getStore().on('beforeload', function () {
                                            pnl.getEl().mask('Chargement orbit_channel_y....');
                                        }, this);

                                        pnl.orbit_channel_y.getStore().on('load', function () {
                                            pnl.orbit_channel_y.setValue(action.result.data.orbit_channel_y);
                                            pnl.orbit_channel_y.disable();
                                            pnl.getEl().unmask();
                                        }, this);

                                        pnl.orbit_channel_y.getStore().load();

                                        pnl.sample_rev.getStore().on('beforeload', function () {
                                            pnl.getEl().mask('Chargement sample_rev....');
                                        }, this);

                                        pnl.sample_rev.getStore().on('load', function () {
                                            pnl.sample_rev.setValue(action.result.data.sample_rev);
                                            pnl.sample_rev.disable();
                                            pnl.getEl().unmask();
                                        }, this);

                                        pnl.sample_rev.getStore().load();

                                        pnl.average.getStore().on('beforeload', function () {
                                            pnl.getEl().mask('Chargement average....');
                                        }, this);

                                        pnl.average.getStore().on('load', function () {
                                            pnl.average.setValue(action.result.data.average);
                                            pnl.getEl().unmask();
                                        }, this);

                                        pnl.average.getStore().load();

                                        pnl.overlap.getStore().on('beforeload', function () {
                                            pnl.getEl().mask('Chargement overlap....');
                                        }, this);

                                        pnl.overlap.getStore().on('load', function () {
                                            pnl.overlap.setValue(action.result.data.overlap);
                                            pnl.average.disable();
                                            pnl.getEl().unmask();
                                        }, this);

                                        pnl.overlap.getStore().load();

                                        pnl.window_fft.getStore().on('beforeload', function () {
                                            pnl.getEl().mask('Chargement window_fft....');
                                        }, this);

                                        pnl.window_fft.getStore().on('load', function () {
                                            pnl.window_fft.setValue(action.result.data.window_fft);
                                            pnl.window_fft.disable();
                                            pnl.getEl().unmask();
                                        }, this);

                                        pnl.window_fft.getStore().load();

                                        pnl.window_length_s.getStore().on('beforeload', function () {
                                            pnl.getEl().mask('Chargement window_length_s....');
                                        }, this);

                                        pnl.window_length_s.getStore().on('load', function () {
                                            pnl.window_length_s.setValue(action.result.data.window_length_s);
                                            pnl.window_length_s.disable();
                                            pnl.getEl().unmask();
                                        }, this);

                                        pnl.window_length_s.getStore().load();

                                        pnl.channel.getStore().on('beforeload', function () {
                                            pnl.getEl().mask('Chargement channel....');
                                        }, this);

                                        pnl.channel.getStore().on('load', function () {
                                            pnl.channel.setValue(action.result.data.channel);
                                            pnl.channel.disable();
                                            pnl.getEl().unmask();
                                        }, this);

                                        pnl.channel.getStore().load();

                                        pnl.cpmsslot.getStore().on('beforeload', function () {
                                            pnl.getEl().mask('Chargement cpmsslot....');
                                        }, this);

                                        pnl.cpmsslot.getStore().on('load', function () {
                                            pnl.cpmsslot.setValue(action.result.data.cpmsslot);
                                            pnl.cpmsslot.disable();
                                            pnl.getEl().unmask();
                                        }, this);

                                        pnl.cpmsslot.getStore().load();

                                        pnl.sampling_freq.getStore().on('beforeload', function () {
                                            pnl.getEl().mask('Chargement sampling_freq....');
                                        }, this);

                                        pnl.sampling_freq.getStore().on('load', function () {
                                            pnl.sampling_freq.setValue(action.result.data.sampling_freq);
                                            pnl.sampling_freq.disable();
                                            pnl.getEl().unmask();
                                        }, this);

                                        pnl.sampling_freq.getStore().load();

                                        pnl.record_length.getStore().on('beforeload', function () {
                                            pnl.getEl().mask('Chargement record_length....');
                                        }, this);

                                        pnl.record_length.getStore().on('load', function () {
                                            pnl.record_length.setValue(action.result.data.record_length);
                                            pnl.record_length.disable();
                                            pnl.getEl().unmask();
                                        }, this);

                                        pnl.record_length.getStore().load();

                                        pnl.engineering_unit.getStore().on('beforeload', function () {
                                            pnl.getEl().mask('Chargement engineering_unit....');
                                        }, this);

                                        pnl.engineering_unit.getStore().on('load', function () {
                                            pnl.engineering_unit.setValue(action.result.data.engineering_unit);
                                            pnl.engineering_unit.disable();
                                            pnl.getEl().unmask();
                                        }, this);

                                        pnl.engineering_unit.getStore().load();

                                        pnl.getEl().unmask();
                                    }
                                },
                                failure: function (form, action) {
                                    Ext.Msg.alert(TocLanguage.msgErrTitle, action.result.feedback);
                                    pnl.getEl().unmask();
                                },
                                scope: this
                            },
                            this
                        );
                    });

                    panel.mainPanel.showInfos(node.id, node.text.toLowerCase(), plant, line, component, asset);
                    panel.mainPanel.doLayout();

                    break;

                case 'asset':
                    parent = node.parentNode;
                    line = parent.text.toLowerCase();
                    plant = parent.parentNode.text.toLowerCase();

                    pnlDashboard = new Toc.DashboardPanel({mainPanel : panel.mainPanel,content_id: node.id, content_type: 'asset', content_name: node.text.toLowerCase(), line: line, plant: plant});
                    pnlParameters = new Toc.ParametersGrid({mainPanel : panel.mainPanel,content_id: node.id, content_type: 'asset', content_name: node.text.toLowerCase(), line: line, plant: plant});
                    pnlDocuments = new Toc.content.DocumentsPanel({content_id: node.id, content_type: 'asset', owner: Toc.content.ContentManager, can_edit: false});
                    grdImages = new Toc.content.ImagesGrid({content_id: node.id, content_type: 'asset', module: 'content', can_delete: false});
                    pnl = new Toc.AssetPanel({lines_id: node.parentNode.id, title: 'Proprietes'});
                    grdeventlog = new Toc.eventlogGrid({owner: this.owner, mainPanel: panel.mainPanel, content_id: node.id, content_type: 'asset', title: 'EventLog'});
                    grdTickets = new Toc.TicketsGrid({content_id: node.id, content_type: 'asset', owner: this.owner, mainPanel: this});
                    //pnlComments = new Toc.content.CommentsPanel({content_id: node.id, content_type: 'asset', owner: Toc.content.ContentManager});

                    tab = new Ext.TabPanel({
                        activeTab: 0,
                        defaults: {
                            hideMode: 'offsets'
                        },
                        deferredRender: false,
                        items: [pnlParameters, pnlDashboard, grdImages, pnl, pnlDocuments, grdeventlog, grdTickets]
                    });

                    pnlParameters.on('rowclick', function (grid, rowIndex, eventObject) {
                            var store = pnlParameters.getStore();
                            var record = store.getAt(rowIndex);
                            panel.mainPanel.hideInfos();
                            panel.mainPanel.showParameterInfos(record);
                            panel.mainPanel.doLayout();
                        }
                    );

                    panel.add(tab);
                    panel.doLayout();

                    panel.form.reset();
                    panel.form.baseParams['asset_id'] = node.id;
                    panel.form.baseParams['lines_id'] = node.parentNode.id;
                    panel.form.baseParams['module'] = 'categories';
                    panel.form.baseParams['action'] = 'save_asset';

                    pnl.on('activate', function () {
                        pnl.getEl().mask('Chargement Asset en cours....');

                        panel.load({
                                url: Toc.CONF.CONN_URL,
                                params: {
                                    action: 'load_asset'
                                },
                                success: function (form, action) {
                                    if (tab && pnl) {
                                        pnl.code.setValue(action.result.data.code);
                                        pnl.code.disable();
                                        pnl.name.setValue(action.result.data.name);
                                        pnl.name.disable();
                                        pnl.cpms_ip.setValue(action.result.data.cpms_ip);
                                        pnl.cpms_ip.disable();
                                        pnl.cpms_mac.setValue(action.result.data.cpms_mac);
                                        pnl.cpms_mac.disable();
                                        pnl.cpms_controller.setValue(action.result.data.cpms_controller);
                                        pnl.cpms_controller.disable();
                                        //pnl.cpms_configurator.setValue(action.result.data.cpms_configurator);
                                        //pnl.cpms_configurator.disable();
                                        pnl.location.setValue(action.result.data.location);
                                        pnl.location.disable();
                                        pnl.manufacturer.setValue(action.result.data.manufacturer);
                                        pnl.manufacturer.disable();
                                        pnl.model.setValue(action.result.data.model);
                                        pnl.model.disable();
                                        pnl.fonction.setValue(action.result.data.fonction);
                                        pnl.fonction.disable();
                                        pnl.norms.setValue(action.result.data.norms);
                                        pnl.norms.disable();

                                        pnl.tachochannel.getStore().on('load', function () {
                                            pnl.tachochannel.setValue(action.result.data.tachochannel);
                                            pnl.tachochannel.disable();
                                        }, this);

                                        pnl.tachochannel.getStore().load();

                                        pnl.CPMS_TypeCombo.getStore().on('load', function () {
                                            pnl.CPMS_TypeCombo.setValue(action.result.data.cpms_type);
                                            pnl.CPMS_TypeCombo.disable();
                                        }, this);

                                        pnl.CPMS_TypeCombo.getStore().load();

                                        pnl.CpmsSlotCombo.getStore().on('load', function () {
                                            pnl.CpmsSlotCombo.setValue(action.result.data.cpms_slotnumber);
                                            pnl.CpmsSlotCombo.disable();
                                        }, this);

                                        pnl.CpmsSlotCombo.getStore().load();

                                        pnl.EquipmentypeCombo.getStore().on('load', function () {
                                            pnl.EquipmentypeCombo.setValue(action.result.data.equipmentype);
                                            pnl.EquipmentypeCombo.disable();
                                        }, this);

                                        pnl.EquipmentypeCombo.getStore().load();

                                        pnl.UsersCombo.getStore().on('load', function () {
                                            pnl.UsersCombo.setValue(action.result.data.administrators_id);
                                            pnl.UsersCombo.disable();
                                        }, this);

                                        pnl.UsersCombo.getStore().load();

                                        pnl.ConfigurationCombo.getStore().on('load', function () {
                                            pnl.ConfigurationCombo.setValue(action.result.data.configuration);
                                            pnl.ConfigurationCombo.disable();
                                        }, this);

                                        pnl.ConfigurationCombo.getStore().load();

                                        pnl.PowerSourceCombo.getStore().on('load', function () {
                                            pnl.PowerSourceCombo.setValue(action.result.data.powersource);
                                            pnl.PowerSourceCombo.disable();
                                        }, this);

                                        pnl.PowerSourceCombo.getStore().load();

                                        pnl.rotdir.getStore().on('load', function () {
                                            pnl.rotdir.setValue(action.result.data.rotdir);
                                            pnl.rotdir.disable();
                                        }, this);

                                        pnl.rotdir.getStore().load();

                                        pnl.deltaprozent.getStore().on('load', function () {
                                            pnl.deltaprozent.setValue(action.result.data.deltaprozent);
                                            pnl.deltaprozent.disable();
                                        }, this);

                                        pnl.deltaprozent.getStore().load();

                                        pnl.norms.setValue(action.result.data.norms);
                                        pnl.norms.disable();
                                        pnl.support.setValue(action.result.data.support);
                                        pnl.support.disable();
                                        pnl.coupling.setValue(action.result.data.coupling);
                                        pnl.coupling.disable();
                                        pnl.ratedpower_w.setValue(action.result.data.ratedpower_w);
                                        pnl.ratedpower_w.disable();
                                        pnl.ratedspeed_rpm.setValue(action.result.data.ratedspeed_rpm);
                                        pnl.ratedspeed_rpm.disable();
                                        pnl.ratedtorque_nm.setValue(action.result.data.ratedtorque_nm);
                                        pnl.ratedtorque_nm.disable();
                                        pnl.ratedvoltage_v.setValue(action.result.data.ratedvoltage_v);
                                        pnl.ratedvoltage_v.disable();
                                        pnl.ratedcurrent_a.setValue(action.result.data.ratedcurrent_a);
                                        pnl.ratedcurrent_a.disable();
                                        pnl.minspeed_rpm.setValue(action.result.data.minspeed_rpm);
                                        pnl.minspeed_rpm.disable();
                                        pnl.maxspeed_rpm.setValue(action.result.data.maxspeed_rpm);
                                        pnl.maxspeed_rpm.disable();
                                        pnl.pulse_per_rev.setValue(action.result.data.pulse_per_rev);
                                        pnl.pulse_per_rev.disable();
                                        pnl.triggerlevel.setValue(action.result.data.triggerlevel);
                                        pnl.triggerlevel.disable();
                                        pnl.op1_name.setValue(action.result.data.op1_name);
                                        pnl.op1_name.disable();
                                        pnl.op2_name.setValue(action.result.data.op2_name);
                                        pnl.op2_name.disable();
                                        pnl.op3_name.setValue(action.result.data.op2_name);
                                        pnl.op3_name.disable();
                                        pnl.op4_name.setValue(action.result.data.op2_name);
                                        pnl.op4_name.disable();
                                        pnl.op5_name.setValue(action.result.data.op2_name);
                                        pnl.op5_name.disable();
                                        pnl.op6_name.setValue(action.result.data.op2_name);
                                        pnl.op6_name.disable();
                                        pnl.op7_name.setValue(action.result.data.op2_name);
                                        pnl.op7_name.disable();
                                        pnl.op8_name.setValue(action.result.data.op2_name);
                                        pnl.op8_name.disable();
                                        pnl.op9_name.setValue(action.result.data.op2_name);
                                        pnl.op9_name.disable();
                                        pnl.op10_name.setValue(action.result.data.op2_name);
                                        pnl.op10_name.disable();

                                        pnl.kmean.setValue(action.result.data.kmean);
                                        pnl.kmean.disable();
                                        pnl.kmin.setValue(action.result.data.kmin);
                                        pnl.kmin.disable();
                                        pnl.kmax.setValue(action.result.data.kmax);
                                        pnl.kmax.disable();
                                        pnl.kstd.setValue(action.result.data.kstd);
                                        pnl.kstd.disable();
                                        pnl.kral.setValue(action.result.data.kral);
                                        pnl.kral.disable();
                                        pnl.kal.setValue(action.result.data.kal);
                                        pnl.kal.disable();
                                        pnl.refwindow.setValue(action.result.data.refwindow);
                                        pnl.refwindow.disable();
                                        pnl.movingwindow.setValue(action.result.data.movingwindow);
                                        pnl.movingwindow.disable();
                                        pnl.movingdeltaanalyse.setValue(action.result.data.movingdeltaanalyse);
                                        pnl.movingdeltaanalyse.disable();
                                        pnl.severitylimit1.setValue(action.result.data.severitylimit1);
                                        pnl.severitylimit1.disable();
                                        pnl.severitylimit2.setValue(action.result.data.severitylimit2);
                                        pnl.severitylimit2.disable();
                                        pnl.severitylimit3.setValue(action.result.data.severitylimit3);
                                        pnl.severitylimit3.disable();
                                        pnl.severitylimit4.setValue(action.result.data.severitylimit4);
                                        pnl.severitylimit4.disable();

                                        pnl.getEl().unmask();
                                    }
                                },
                                failure: function (form, action) {
                                    Ext.Msg.alert(TocLanguage.msgErrTitle, action.result.feedback);
                                    pnl.getEl().unmask();
                                },
                                scope: this
                            },
                            this
                        );
                    });

                    panel.mainPanel.hideInfos();
                    panel.mainPanel.showAssetInfos(node.id, node.text.toLowerCase(), plant, line, component, asset);
                    panel.mainPanel.doLayout();

                    break;

                case 'line':
                    pnlDashboard = new Toc.DashboardPanel({mainPanel : panel.mainPanel,content_id: node.id, content_type: 'line', content_name: node.text.toLowerCase(), plant: node.parentNode.text.toLowerCase()});
                    pnlParameters = new Toc.ParametersGrid({mainPanel : panel.mainPanel,content_id: node.id, content_type: 'line', content_name: node.text.toLowerCase(), plant: node.parentNode.text.toLowerCase()});
                    pnlDocuments = new Toc.content.DocumentsPanel({content_id: node.id, content_type: 'line', owner: Toc.content.ContentManager, can_edit: false});
                    grdImages = new Toc.content.ImagesGrid({content_id: node.id, content_type: 'line', module: 'content', can_delete: false});
                    pnl = new Toc.LinePanel({plants_id: node.parentNode.id, title: 'Proprietes'});
                    grdeventlog = new Toc.eventlogGrid({owner: this.owner, mainPanel: panel.mainPanel, content_id: node.id, content_type: 'line', title: 'EventLog'});
                    grdTickets = new Toc.TicketsGrid({content_id: node.id, content_type: 'line', owner: this.owner, mainPanel: this});
                    //pnlComments = new Toc.content.CommentsPanel({content_id: node.id, content_type: 'line', owner: Toc.content.ContentManager});

                    tab = new Ext.TabPanel({
                        activeTab: 0,
                        defaults: {
                            hideMode: 'offsets'
                        },
                        deferredRender: false,
                        items: [pnlParameters, pnlDashboard, grdImages, pnl, pnlDocuments, grdeventlog, grdTickets]
                    });

                    pnlParameters.on('rowclick', function (grid, rowIndex, eventObject) {
                            var store = pnlParameters.getStore();
                            var record = store.getAt(rowIndex);
                            panel.mainPanel.hideInfos();
                            panel.mainPanel.showParameterInfos(record);
                            panel.mainPanel.doLayout();
                        }
                    );

                    panel.add(tab);
                    panel.doLayout();

                    panel.form.reset();
                    panel.form.baseParams['lines_id'] = node.id;
                    panel.form.baseParams['plants_id'] = node.parentNode.id;
                    panel.form.baseParams['module'] = 'categories';
                    panel.form.baseParams['action'] = 'save_line';

                    pnl.on('activate', function () {
                        pnl.getEl().mask('Chargement Ligne en cours....');

                        panel.load({
                                url: Toc.CONF.CONN_URL,
                                params: {
                                    action: 'load_line'
                                },
                                success: function (form, action) {
                                    if (pnl) {
                                        pnl.code.setValue(action.result.data.code);
                                        pnl.code.disable();
                                        pnl.name.setValue(action.result.data.name);
                                        pnl.name.disable();
                                        pnl.unit.setValue(action.result.data.unit);
                                        pnl.unit.disable();
                                        pnl.building.setValue(action.result.data.building);
                                        pnl.building.disable();
                                        pnl.operator.setValue(action.result.data.operator);
                                        pnl.operator.disable();
                                    }

                                    pnl.getEl().unmask();
                                },
                                failure: function (form, action) {
                                    Ext.Msg.alert(TocLanguage.msgErrTitle, action.result.feedback);
                                    pnl.getEl().unmask();
                                },
                                scope: this
                            },
                            this
                        );
                    })

                    panel.mainPanel.hideInfos();

                    break;

                case 'component':
                    component = node.id;
                    parent = node.parentNode;
                    asset = parent.text.toLowerCase();
                    line = parent.parentNode.text.toLowerCase();
                    plant = parent.parentNode.parentNode.text.toLowerCase();

                    pnlDashboard = new Toc.DashboardPanel({mainPanel : panel.mainPanel,content_id: node.id, content_type: 'component', content_name: node.text.toLowerCase(), plant: plant, asset: asset, component: component, line: line});
                    pnlParameters = new Toc.ParametersGrid({mainPanel : panel.mainPanel,content_id: node.id, content_type: 'component', content_name: node.text.toLowerCase(), plant: plant, asset: asset, component: component, line: line});
                    pnlDocuments = new Toc.content.DocumentsPanel({content_id: node.id, content_type: 'component', owner: Toc.content.ContentManager, can_edit: false});
                    grdImages = new Toc.content.ImagesGrid({content_id: node.id, content_type: 'component', module: 'content', can_delete: false});
                    pnl = new Toc.ComponentPanel({asset_id: node.parentNode.id, title: 'Proprietes'});
                    grdeventlog = new Toc.eventlogGrid({owner: this.owner, mainPanel: panel.mainPanel, content_id: node.id, content_type: 'component', title: 'EventLog'});
                    grdTickets = new Toc.TicketsGrid({content_id: node.id, content_type: 'component', owner: this.owner, mainPanel: this});
                    //pnlComments = new Toc.content.CommentsPanel({content_id: node.id, content_type: 'component', owner: Toc.content.ContentManager});

                    tab = new Ext.TabPanel({
                        activeTab: 0,
                        defaults: {
                            hideMode: 'offsets'
                        },
                        deferredRender: false,
                        items: [pnlParameters, pnlDashboard, grdImages, pnl, pnlDocuments, grdeventlog, grdTickets]
                    });

                    pnlParameters.on('rowclick', function (grid, rowIndex, eventObject) {
                            var store = pnlParameters.getStore();
                            var record = store.getAt(rowIndex);
                            panel.mainPanel.hideInfos();
                            panel.mainPanel.showParameterInfos(record);
                            panel.mainPanel.doLayout();
                        }
                    );

                    panel.add(tab);
                    panel.doLayout();

                    panel.form.reset();
                    panel.form.baseParams['component_id'] = node.id;
                    panel.form.baseParams['asset_id'] = node.parentNode.id;
                    panel.form.baseParams['module'] = 'categories';
                    panel.form.baseParams['action'] = 'save_component';

                    pnl.on('activate', function () {
                        pnl.getEl().mask('Chargement Ligne en cours....');

                        panel.load({
                                url: Toc.CONF.CONN_URL,
                                params: {
                                    action: 'load_component'
                                },
                                success: function (form, action) {
                                    if (pnl) {
                                        pnl.Geartype.getStore().on('load', function () {
                                            pnl.Geartype.setValue(action.result.data.geartype);
                                            pnl.Geartype.disable();
                                        }, this);
                                        pnl.Geartype.getStore().load();

                                        pnl.serial.setValue(action.result.data.serial);
                                        pnl.serial.disable();
                                        pnl.model.setValue(action.result.data.model);
                                        pnl.model.disable();
                                        pnl.name.setValue(action.result.data.name);
                                        pnl.name.disable();
                                        pnl.function.setValue(action.result.data.function);
                                        pnl.function.disable();
                                        pnl.FirstNaturalFrequency.setValue(action.result.data.firstnaturalfrequency);
                                        pnl.FirstNaturalFrequency.disable();
                                        pnl.SecondNaturalFrequency.setValue(action.result.data.secondnaturalfrequency);
                                        pnl.SecondNaturalFrequency.disable();
                                        pnl.ThirdNaturalFrequency.setValue(action.result.data.thirdnaturalfrequency);
                                        pnl.ThirdNaturalFrequency.disable();
                                        pnl.RollingBearing.setValue(action.result.data.rollingbearing);
                                        pnl.RollingBearing.disable();
                                        pnl.RollingBearingWidth_m.setValue(action.result.data.rollingbearingwidth_m);
                                        pnl.RollingBearingWidth_m.disable();
                                        pnl.RollingBearingDiameter_m.setValue(action.result.data.rollingbearingdiameter_m);
                                        pnl.RollingBearingDiameter_m.disable();
                                        pnl.NumberRollingElements.setValue(action.result.data.numberrollingelements);
                                        pnl.NumberRollingElements.disable();
                                        pnl.RollingBearingContactAngle_Grad.setValue(action.result.data.rollingbearingcontactangle_grad);
                                        pnl.RollingBearingContactAngle_Grad.disable();
                                        pnl.OuterRingFrequency.setValue(action.result.data.outerringfrequency);
                                        pnl.OuterRingFrequency.disable();
                                        pnl.InnerRingFrequency.setValue(action.result.data.innerringfrequency);
                                        pnl.InnerRingFrequency.disable();
                                        pnl.CageFrequency.setValue(action.result.data.cagefrequency);
                                        pnl.CageFrequency.disable();
                                        pnl.RollingElementRotationFrequency.setValue(action.result.data.rollingelementrotationfrequency);
                                        pnl.RollingElementRotationFrequency.disable();
                                        pnl.RollingElementContactFrequency.setValue(action.result.data.rollingelementcontactfrequency);
                                        pnl.RollingElementContactFrequency.disable();
                                        pnl.JournalBearing.setValue(action.result.data.journalbearing);
                                        pnl.JournalBearing.disable();
                                        pnl.JournalBearingFluidType.setValue(action.result.data.journalbearingfluidtype);
                                        pnl.JournalBearingFluidType.disable();
                                        pnl.JournalBearingGap_um.setValue(action.result.data.journalbearinggap_um);
                                        pnl.JournalBearingGap_um.disable();
                                        pnl.OilWhirlMinOrder.setValue(action.result.data.oilwhirlminorder);
                                        pnl.OilWhirlMinOrder.disable();
                                        pnl.OilWhirlMaxOrder.setValue(action.result.data.oilwhirlmaxorder);
                                        pnl.OilWhirlMaxOrder.disable();
                                        pnl.MinFluidTemperature_C.setValue(action.result.data.minfluidtemperature_c);
                                        pnl.MinFluidTemperature_C.disable();
                                        pnl.MaxFluidTemperature_C.setValue(action.result.data.maxfluidtemperature_c);
                                        pnl.MaxFluidTemperature_C.disable();
                                        pnl.MinFluidPressure_bar.setValue(action.result.data.minfluidpressure_bar);
                                        pnl.MinFluidPressure_bar.disable();
                                        pnl.Turbomachinery.setValue(action.result.data.turbomachinery);
                                        pnl.Turbomachinery.disable();
                                        pnl.BladesNumber.setValue(action.result.data.bladesnumber);
                                        pnl.BladesNumber.disable();
                                        pnl.VanesNumber.setValue(action.result.data.vanesnumber);
                                        pnl.VanesNumber.disable();
                                        pnl.BladeLength_m.setValue(action.result.data.bladelength_m);
                                        pnl.BladeLength_m.disable();
                                        pnl.BladePassFrequency.setValue(action.result.data.bladepassfrequency);
                                        pnl.BladePassFrequency.disable();
                                        pnl.BladeTipFrequency.setValue(action.result.data.bladetipfrequency);
                                        pnl.BladeTipFrequency.disable();
                                        pnl.VanePassingFrequency.setValue(action.result.data.vanepassingfrequency);
                                        pnl.VanePassingFrequency.disable();
                                        pnl.BladeVanePassingFrequency.setValue(action.result.data.bladevanepassingfrequency);
                                        pnl.BladeVanePassingFrequency.disable();
                                        pnl.Gear.setValue(action.result.data.gear);
                                        pnl.Gear.disable();
                                        pnl.GearRatio.setValue(action.result.data.gearratio);
                                        pnl.GearRatio.disable();
                                        pnl.GearNumberStages.setValue(action.result.data.gearnumberstages);
                                        pnl.GearNumberStages.disable();
                                        pnl.GearLowSpeedShaftTeethNumber.setValue(action.result.data.gearlowspeedshaftteethnumber);
                                        pnl.GearLowSpeedShaftTeethNumber.disable();
                                        pnl.GearFastSpeedShaftTeethNumber.setValue(action.result.data.gearfastspeedshaftteethnumber);
                                        pnl.GearFastSpeedShaftTeethNumber.disable();
                                        pnl.GearRingTeethNumber.setValue(action.result.data.gearringteethnumber);
                                        pnl.GearRingTeethNumber.disable();
                                        pnl.GearPlanetTeethNumber.setValue(action.result.data.gearplanetteethnumber);
                                        pnl.GearPlanetTeethNumber.disable();
                                        pnl.GearPlanetaryCarrierTeethNumber.setValue(action.result.data.gearplanetarycarrierteethnumber);
                                        pnl.GearPlanetaryCarrierTeethNumber.disable();
                                        pnl.GearFixedComponent.setValue(action.result.data.gearfixedcomponent);
                                        pnl.GearFixedComponent.disable();
                                        pnl.GearSunFrequency.setValue(action.result.data.gearsunfrequency);
                                        pnl.GearSunFrequency.disable();
                                        pnl.GearRingFrequency.setValue(action.result.data.gearringfrequency);
                                        pnl.GearRingFrequency.disable();
                                        pnl.GearPlanetFrequency.setValue(action.result.data.gearplanetfrequency);
                                        pnl.GearPlanetFrequency.disable();
                                        pnl.GearMeshFrequency.setValue(action.result.data.gearmeshfrequency);
                                        pnl.GearMeshFrequency.disable();
                                        pnl.GearTeethCommonFactor.setValue(action.result.data.gearteethcommonfactor);
                                        pnl.GearTeethCommonFactor.disable();
                                        pnl.GearHuntingToothFrequency.setValue(action.result.data.gearhuntingtoothfrequency);
                                        pnl.GearHuntingToothFrequency.disable();
                                        pnl.GearAssemblyPhase.setValue(action.result.data.gearassemblyphase);
                                        pnl.GearAssemblyPhase.disable();
                                        pnl.GearGhostFrequency.setValue(action.result.data.gearghostfrequency);
                                        pnl.GearGhostFrequency.disable();
                                        pnl.Belt.setValue(action.result.data.belt);
                                        pnl.Belt.disable();
                                        pnl.BeltDiameterD1_m.setValue(action.result.data.beltdiameterd1_m);
                                        pnl.BeltDiameterD1_m.disable();
                                        pnl.BeltDiameterD2_m.setValue(action.result.data.beltdiameterd2_m);
                                        pnl.BeltDiameterD2_m.disable();
                                        pnl.BeltAxialGap_m.setValue(action.result.data.beltaxialgap_m);
                                        pnl.BeltAxialGap_m.disable();
                                        pnl.BeltTeethNumberZ1.setValue(action.result.data.beltteethnumberz1);
                                        pnl.BeltTeethNumberZ1.disable();
                                        pnl.BeltTeethNumberZ2.setValue(action.result.data.beltteethnumberz2);
                                        pnl.BeltTeethNumberZ2.disable();
                                        pnl.BeltLength_m.setValue(action.result.data.beltlength_m);
                                        pnl.BeltLength_m.disable();
                                        pnl.BeltSpeedN1_rpm.setValue(action.result.data.beltspeedn1_rpm);
                                        pnl.BeltSpeedN1_rpm.disable();
                                        pnl.BeltSpeedN2_rpm.setValue(action.result.data.beltspeedn2_rpm);
                                        pnl.BeltSpeedN2_rpm.disable();
                                        pnl.BeltFrequency.setValue(action.result.data.beltfrequency);
                                        pnl.BeltFrequency.disable();
                                        pnl.TimingBeltFrequency.setValue(action.result.data.timingbeltfrequency);
                                        pnl.TimingBeltFrequency.disable();
                                        pnl.Motor_Generator.setValue(action.result.data.motor_generator);
                                        pnl.Motor_Generator.disable();
                                        pnl.MotorEfficiency.setValue(action.result.data.motorefficiency);
                                        pnl.MotorEfficiency.disable();
                                        pnl.MotorPolePairs.setValue(action.result.data.motorpolepairs);
                                        pnl.MotorPolePairs.disable();
                                        pnl.MotorRotorBars.setValue(action.result.data.motorrotorbars);
                                        pnl.MotorRotorBars.disable();
                                        pnl.MotorStatorPoles.setValue(action.result.data.motorstatorpoles);
                                        pnl.MotorStatorPoles.disable();
                                        pnl.MotorStatorSlots.setValue(action.result.data.motorstatorslots);
                                        pnl.MotorStatorSlots.disable();
                                        pnl.MotorCoilsPerPole.setValue(action.result.data.motorcoilsperpole);
                                        pnl.MotorCoilsPerPole.disable();
                                        pnl.MotorLineOfFrequency.setValue(action.result.data.motorlineoffrequency);
                                        pnl.MotorLineOfFrequency.disable();
                                        pnl.MotorSynchronuousSpeedFrequency.setValue(action.result.data.motorsynchronuousspeedfrequency);
                                        pnl.MotorSynchronuousSpeedFrequency.disable();
                                        pnl.MotorRunningSpeedFrequency.setValue(action.result.data.motorrunningspeedfrequency);
                                        pnl.MotorRunningSpeedFrequency.disable();
                                        pnl.MotorSlipFrequency.setValue(action.result.data.motorslipfrequency);
                                        pnl.MotorSlipFrequency.disable();
                                        pnl.MotorSlipRatio.setValue(action.result.data.motorslipratio);
                                        pnl.MotorSlipRatio.disable();
                                        pnl.MotorPolePassFrequency.setValue(action.result.data.motorpolepassfrequency);
                                        pnl.MotorPolePassFrequency.disable();
                                        pnl.MotorSlotPassFrequency.setValue(action.result.data.motorslotpassfrequency);
                                        pnl.MotorSlotPassFrequency.disable();
                                        pnl.MotorRotorBarFrequency.setValue(action.result.data.motorrotorbarfrequency);
                                        pnl.MotorRotorBarFrequency.disable();
                                        pnl.MotorStatorSlotFrequency.setValue(action.result.data.motorstatorslotfrequency);
                                        pnl.MotorStatorSlotFrequency.disable();
                                        pnl.MotorCommutatorFrequency.setValue(action.result.data.motorcommutatorfrequency);
                                        pnl.MotorCommutatorFrequency.disable();
                                        pnl.MotorStaticEccentricityFrequency.setValue(action.result.data.motorstaticeccentricityfrequency);
                                        pnl.MotorStaticEccentricityFrequency.disable();
                                        pnl.MotorDynamicEccentricity.setValue(action.result.data.motordynamiceccentricity);
                                        pnl.MotorDynamicEccentricity.disable();
                                        pnl.MotorStatorMechanicalDamageFrequency.setValue(action.result.data.motorstatormechanicaldamagefrequency);
                                        pnl.MotorStatorMechanicalDamageFrequency.disable();
                                        pnl.MotorRotorDefectFrequency.setValue(action.result.data.motorrotordefectfrequency);
                                        pnl.MotorRotorDefectFrequency.disable();
                                        pnl.MotorLooseStatorCoilFrequency.setValue(action.result.data.motorloosestatorcoilfrequency);
                                        pnl.MotorRotorDefectFrequency.disable();
                                    }

                                    pnl.getEl().unmask();
                                },
                                failure: function (form, action) {
                                    Ext.Msg.alert(TocLanguage.msgErrTitle, action.result.feedback);
                                    pnl.getEl().unmask();
                                },
                                scope: this
                            },
                            this
                        );
                    });


                    panel.mainPanel.hideInfos();

                    break;

                case 'customer':

                    pnlMap = new Toc.Map({customers_id: node.id, owner: Toc.content.ContentManager, title: 'Map'});
                    pnlDashboard = new Toc.DashboardPanel({mainPanel : panel.mainPanel,content_id: node.id, content_type: 'customer', content_name: node.text.toLowerCase()});
                    pnlDocuments = new Toc.content.DocumentsPanel({content_id: node.id, content_type: 'customer', owner: Toc.content.ContentManager, can_edit: false});
                    grdImages = new Toc.content.ImagesGrid({content_id: node.id, content_type: 'customer', module: 'content', can_delete: false});
                    grdeventlog = new Toc.eventlogGrid({owner: this.owner, mainPanel: panel.mainPanel, content_id: node.id, content_type: 'customer', title: 'EventLog'});
                    grdTickets = new Toc.TicketsGrid({content_id: node.id, content_type: 'customer', owner: this.owner, mainPanel: this});
                    //pnlComments = new Toc.content.CommentsPanel({content_id: node.id, content_type: 'customer', owner: Toc.content.ContentManager});

                    tab = new Ext.TabPanel({
                        activeTab: 0,
                        defaults: {
                            hideMode: 'offsets'
                        },
                        deferredRender: false,
                        items: [pnlMap, pnlDashboard, pnlDocuments, grdImages, grdeventlog, grdTickets]
                    });

                    panel.add(tab);
                    panel.doLayout();

                    panel.mainPanel.hideInfos();
                    panel.mainPanel.showCustomerInfos(node);
                    panel.mainPanel.doLayout();

                    break;

                case 'plant':
                    pnlDashboard = new Toc.DashboardPanel({mainPanel : panel.mainPanel,content_id: node.id, content_type: 'plant', content_name: node.text.toLowerCase()});
                    pnlParameters = new Toc.ParametersGrid({mainPanel : panel.mainPanel,content_id: node.id, content_type: 'plant', content_name: node.text.toLowerCase()});
                    pnlDocuments = new Toc.content.DocumentsPanel({content_id: node.id, content_type: 'plant', owner: Toc.content.ContentManager, can_edit: false});
                    grdImages = new Toc.content.ImagesGrid({content_id: node.id, content_type: 'plant', module: 'content', can_delete: false});
                    pnl = new Toc.PlantPanel({customers_id: node.parentNode.id, title: "Proprietes", can_edit: false});
                    pnlAdresse = new Toc.PlantAdressePanel({owner: this.owner, customers_id: node.parentNode.id, title: 'Adresse'});
                    grdeventlog = new Toc.eventlogGrid({owner: this.owner, mainPanel: panel.mainPanel, content_id: node.id, content_type: 'plant', title: 'EventLog'});
                    grdTickets = new Toc.TicketsGrid({content_id: node.id, content_type: 'plant', owner: this.owner, mainPanel: this});
                    //pnlComments = new Toc.content.CommentsPanel({content_id: node.id, content_type: 'plant', owner: Toc.content.ContentManager});

                    tab = new Ext.TabPanel({
                        activeTab: 0,
                        defaults: {
                            hideMode: 'offsets'
                        },
                        deferredRender: false,
                        items: [pnlParameters, pnlDashboard, grdImages, pnl, pnlAdresse, pnlDocuments, grdeventlog, grdTickets]
                    });

                    pnlParameters.on('rowclick', function (grid, rowIndex, eventObject) {
                            var store = pnlParameters.getStore();
                            var record = store.getAt(rowIndex);
                            panel.mainPanel.hideInfos();
                            panel.mainPanel.showParameterInfos(record);
                            panel.mainPanel.doLayout();
                        }
                    );

                    panel.add(tab);
                    panel.doLayout();

                    panel.form.reset();
                    panel.form.baseParams['plants_id'] = node.id;
                    panel.form.baseParams['customers_id'] = node.parentNode.id;
                    panel.form.baseParams['module'] = 'categories';
                    panel.form.baseParams['action'] = 'save_plant';

                    pnl.on('activate', function () {
                        pnl.getEl().mask('Chargement Usine en cours....');

                        panel.load({
                                url: Toc.CONF.CONN_URL,
                                params: {
                                    action: 'load_plant'
                                },
                                success: function (form, action) {
                                    if (pnl) {
                                        pnl.code.setValue(action.result.data.code);
                                        pnl.code.disable();
                                        pnl.categories_name.setValue(action.result.data.categories_name);
                                        pnl.categories_name.disable();
                                        pnl.location.setValue(action.result.data.location);
                                        pnl.location.disable();
                                        pnl.manufacturer.setValue(action.result.data.manufacturer);
                                        pnl.manufacturer.disable();
                                        pnl.model.setValue(action.result.data.model);
                                        pnl.model.disable();
                                        pnl.serial_number.setValue(action.result.data.serial_number);
                                        pnl.serial_number.disable();
                                        pnl.operator.setValue(action.result.data.operator);
                                        pnl.operator.disable();
                                        pnl.comments.setValue(action.result.data.comments);
                                        pnl.comments.disable();
                                    }
                                    else {
                                        Ext.Msg.alert(TocLanguage.msgErrTitle, 'pnl not defined !!!');
                                    }

                                    pnl.getEl().unmask();
                                },
                                failure: function (form, action) {
                                    Ext.Msg.alert(TocLanguage.msgErrTitle, action.result.feedback);
                                    pnl.getEl().unmask();
                                },
                                scope: this
                            },
                            this
                        );

                    });

                    pnlAdresse.on('activate', function () {
                        pnlAdresse.getEl().mask('Chargement Adresse Usine en cours....');

                        panel.load({
                                url: Toc.CONF.CONN_URL,
                                params: {
                                    action: 'load_plant'
                                },
                                success: function (form, action) {
                                    if (pnlAdresse) {
                                        pnlAdresse.adresse.setValue(action.result.data.adresse);
                                        pnlAdresse.adresse.disable();
                                        pnlAdresse.email.setValue(action.result.data.email);
                                        pnlAdresse.email.disable();
                                        pnlAdresse.phone.setValue(action.result.data.phone);
                                        pnlAdresse.phone.disable();
                                        pnlAdresse.mobile.setValue(action.result.data.mobile);
                                        pnlAdresse.mobile.disable();
                                        pnlAdresse.fax.setValue(action.result.data.fax);
                                        pnlAdresse.fax.disable();
                                        pnlAdresse.url.setValue(action.result.data.url);
                                        pnlAdresse.url.disable();

                                        //pnlAdresse.cboCountries.purgeListeners();

                                        pnlAdresse.cboCountries.getStore().on('load', function () {
                                            pnlAdresse.cboCountries.setValue(action.result.data.country_id);
                                            pnlAdresse.cboCountries.disable();
                                        }, this);

                                        pnlAdresse.cboCountries.getStore().load();
                                    }
                                    else {
                                        Ext.Msg.alert(TocLanguage.msgErrTitle, 'pnlAdresse not defined !!!');
                                    }

                                    pnlAdresse.getEl().unmask();
                                },
                                failure: function (form, action) {
                                    Ext.Msg.alert(TocLanguage.msgErrTitle, action.result.feedback);
                                    pnlAdresse.getEl().unmask();
                                },
                                scope: this
                            },
                            this
                        );
                    });

                    panel.mainPanel.hideInfos();

                    break;

                default:
                    break;
            }
        }
        else {
            Ext.Msg.alert(TocLanguage.msgErrTitle, "Aucun Client selectionne !!!");
        }
    }

    panel.mainPanel.doLayout();

    return true;
};