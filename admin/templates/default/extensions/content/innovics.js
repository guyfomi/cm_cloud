Toc.DetailPanel = function (config) {
    config = config || {};

    config.labelWidth = 120;
    config.url = Toc.CONF.CONN_URL;
    config.baseParams = {
    };

    if (config.can_edit) {
        config.tbar = [
            {
                text: '',
                disabled: true,
                iconCls: 'save',
                handler: function () {
                    this.submitForm();
                },
                scope: this
            },
            '-',
            {
                text: '',
                iconCls: 'refresh',
                disabled: true,
                handler: function () {
                    this.refresh();
                },
                scope: this
            }
        ];
    }

    config.region = 'center';
    config.fileUpload = true;
    config.layout = 'fit';
    config.items = [];

    this.addEvents({'saveSuccess': true});

    Toc.DetailPanel.superclass.constructor.call(this, config);
}

Ext.extend(Toc.DetailPanel, Ext.form.FormPanel, {

    submitForm: function () {
        this.form.submit({
            waitMsg: TocLanguage.formSubmitWaitMsg,
            success: function (form, action) {
                if (action.result.success) {
                    this.fireEvent('saveSuccess', action.result.feedback);
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
    },

    refresh: function () {
        if (this.mainPanel) {
            if (this.node) {
                this.mainPanel.getlayoutTree().setCategoryId(this.node.id);
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

Toc.CpmsTypeCombo = function () {
    var CpmsTypeCombo = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'categories',
            action: 'list_cpmstypes'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            fields: ['types_id', 'label']
        }),
        autoLoad: false
    });

    var cboCpmsType = new Ext.form.ComboBox({
        fieldLabel: 'CPMS_Type',
        store: CpmsTypeCombo,
        displayField: 'label',
        valueField: 'types_id',
        name: 'cpms_type',
        hiddenName: 'cpms_type',
        mode: 'local',
        readOnly: true,
        //width : 520,
        triggerAction: 'all',
        forceSelection: true,
        allowBlank: false
    });

    return cboCpmsType;
};

Toc.MeasurementStateCombo = function (content_type,content_id) {
    var dsMeasurementState = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'categories',
            action: 'list_measurementstate',
            content_id : content_id,
            content_type : content_type
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            fields: ['id', 'label']
        }),
        autoLoad: false
    });

    var cboMeasurementState = new Ext.form.ComboBox({
        fieldLabel: '',
        autoSelect : true,
        store: dsMeasurementState,
        displayField: 'label',
        valueField: 'id',
        name: 'measurement_state',
        hiddenName: 'measurement_state',
        mode: 'local',
        readOnly: true,
        width : 150,
        triggerAction: 'all',
        forceSelection: true,
        allowBlank: false
    });

    return cboMeasurementState;
};

Toc.EventTriggerTypeCombo = function (content_type,content_id) {
    var dsEventTriggerType = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'categories',
            action: 'list_eventtriggertype',
            content_id : content_id,
            content_type : content_type
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            fields: ['id', 'label']
        }),
        autoLoad: false
    });

    var cboEventTriggerType = new Ext.form.ComboBox({
        fieldLabel: '',
        autoSelect : true,
        store: dsEventTriggerType,
        displayField: 'label',
        valueField: 'id',
        name: 'event_trigger_type',
        hiddenName: 'event_trigger_type',
        mode: 'local',
        readOnly: true,
        width : 120,
        triggerAction: 'all',
        forceSelection: true,
        allowBlank: false
    });

    return cboEventTriggerType;
};

Toc.MonitoringStatusCombo = function (content_type,content_id) {
    var dsMonitoringStatus = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'categories',
            action: 'list_monitoringstatus',
            content_id : content_id,
            content_type : content_type
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            fields: ['id', 'label']
        }),
        autoLoad: false
    });

    var cboMonitoringStatus = new Ext.form.ComboBox({
        fieldLabel: '',
        autoSelect : true,
        store: dsMonitoringStatus,
        displayField: 'label',
        valueField: 'id',
        name: 'monitoring_status',
        hiddenName: 'monitoring_status',
        mode: 'local',
        readOnly: true,
        width : 150,
        triggerAction: 'all',
        forceSelection: true,
        allowBlank: false
    });

    return cboMonitoringStatus;
};

Toc.OperatingClassCombo = function (content_type,content_id) {
    var dsOperatingClass = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'categories',
            action: 'list_operatingclass',
            content_id : content_id,
            content_type : content_type
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            fields: ['id', 'label']
        }),
        autoLoad: false
    });

    var cboOperatingClass = new Ext.form.ComboBox({
        fieldLabel: '',
        autoSelect : true,
        store: dsOperatingClass,
        displayField: 'label',
        valueField: 'id',
        name: 'operating_class',
        hiddenName: 'operating_class',
        mode: 'local',
        readOnly: true,
        width : 120,
        triggerAction: 'all',
        forceSelection: true,
        allowBlank: false
    });

    return cboOperatingClass;
};

Toc.XmlFileCombo = function (content_type,content_id) {
    var dsXmlFile = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'categories',
            action: 'list_xmlfile',
            content_id : content_id,
            content_type : content_type
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            fields: ['id', 'label']
        }),
        autoLoad: false
    });

    var cboXmlFile = new Ext.form.ComboBox({
        fieldLabel: '',
        autoSelect : true,
        store: dsXmlFile,
        displayField: 'label',
        valueField: 'id',
        name: 'xmlfile',
        hiddenName: 'xmlfile',
        mode: 'local',
        readOnly: true,
        width : 250,
        triggerAction: 'all',
        forceSelection: true,
        allowBlank: false
    });

    return cboXmlFile;
};

Toc.CpmsSlotCombo = function (config) {
    var CpmsSlotCombo = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'categories',
            action: 'list_cpmsslot'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            fields: ['slot_id', 'label']
        }),
        autoLoad: false
    });

    var cboCpmsSlot = new Ext.form.ComboBox({
        fieldLabel: config.fieldLabel || 'Slot Number',
        store: CpmsSlotCombo,
        displayField: 'label',
        valueField: 'slot_id',
        name: config.name || 'cpms_slotnumber',
        hiddenName: config.name || 'cpms_slotnumber',
        mode: 'local',
        readOnly: true,
        //width : 520,
        triggerAction: 'all',
        forceSelection: true,
        allowBlank: false
    });

    return cboCpmsSlot;
};

Toc.SamplingFrequencyCombo = function (config) {
    var dsSamplingFreq = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'categories',
            action: 'list_sampling'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            fields: ['id', 'label']
        }),
        autoLoad: false
    });

    var cboSamplingFreq = new Ext.form.ComboBox({
        fieldLabel: config.fieldLabel || 'SamplingFrequency_hz',
        store: dsSamplingFreq,
        displayField: 'label',
        valueField: 'id',
        name: config.name || 'sampling_freq',
        hiddenName: config.name || 'sampling_freq',
        mode: 'local',
        readOnly: true,
        //width : 520,
        triggerAction: 'all',
        forceSelection: true,
        allowBlank: false
    });

    return cboSamplingFreq;
};

Toc.RecordLengthCombo = function (config) {
    var dsRecordLength = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'categories',
            action: 'list_reclength'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            fields: ['id', 'label']
        }),
        autoLoad: false
    });

    var cboRecordLength = new Ext.form.ComboBox({
        fieldLabel: config.fieldLabel || 'RecordLength_s',
        store: dsRecordLength,
        displayField: 'label',
        valueField: 'id',
        name: config.name || 'record_length',
        hiddenName: config.name || 'record_length',
        mode: 'local',
        readOnly: true,
        //width : 520,
        triggerAction: 'all',
        forceSelection: true,
        allowBlank: false
    });

    return cboRecordLength;
};

Toc.EngineeringUnitCombo = function (config) {
    var dsEngineeringUnit = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'categories',
            action: 'list_eu'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            fields: ['id', 'label']
        }),
        autoLoad: false
    });

    var cboEngineeringUnit = new Ext.form.ComboBox({
        fieldLabel: config.fieldLabel || 'EngineeringUnit',
        store: dsEngineeringUnit,
        displayField: 'label',
        valueField: 'id',
        name: config.name || 'engineering_unit',
        hiddenName: config.name || 'engineering_unit',
        mode: 'local',
        readOnly: true,
        //width : 520,
        triggerAction: 'all',
        forceSelection: true,
        allowBlank: false
    });

    return cboEngineeringUnit;
};

Toc.WindowLengthCombo = function (config) {
    var dsWindowLength = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'categories',
            action: 'list_windowlength'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            fields: ['id', 'label']
        }),
        autoLoad: false
    });

    var cboWindowLength = new Ext.form.ComboBox({
        fieldLabel: config.fieldLabel || 'WindowLength_s',
        store: dsWindowLength,
        displayField: 'label',
        valueField: 'id',
        name: config.name || 'window_length_s',
        hiddenName: config.name || 'window_length_s',
        mode: 'local',
        readOnly: true,
        //width : 520,
        triggerAction: 'all',
        forceSelection: true,
        allowBlank: false
    });

    return cboWindowLength;
};

Toc.GeartypeCombo = function (config) {
    var dsGeartype = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'categories',
            action: 'list_geartype'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            fields: ['id', 'label']
        }),
        autoLoad: false
    });

    var cboGeartype = new Ext.form.ComboBox({
        fieldLabel: config.fieldLabel || 'Geartype',
        store: dsGeartype,
        displayField: 'label',
        valueField: 'id',
        name: config.name || 'geartype',
        hiddenName: config.name || 'geartype',
        mode: 'local',
        readOnly: true,
        //width : 520,
        triggerAction: 'all',
        forceSelection: true,
        allowBlank: false
    });

    return cboGeartype;
};

Toc.WindowFFTCombo = function (config) {
    var dsWindowFFT = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'categories',
            action: 'list_windowfft'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            fields: ['id', 'label']
        }),
        autoLoad: false
    });

    var cboWindowFFT = new Ext.form.ComboBox({
        fieldLabel: config.fieldLabel || 'WindowFFT',
        store: dsWindowFFT,
        displayField: 'label',
        valueField: 'id',
        name: config.name || 'window_fft',
        hiddenName: config.name || 'window_fft',
        mode: 'local',
        readOnly: true,
        //width : 520,
        triggerAction: 'all',
        forceSelection: true,
        allowBlank: false
    });

    return cboWindowFFT;
};

Toc.OverlapCombo = function (config) {
    var dsOverlap = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'categories',
            action: 'list_overlap'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            fields: ['id', 'label']
        }),
        autoLoad: false
    });

    var cboOverlap = new Ext.form.ComboBox({
        fieldLabel: config.fieldLabel || 'Overlap',
        store: dsOverlap,
        displayField: 'label',
        valueField: 'id',
        name: config.name || 'overlap',
        hiddenName: config.name || 'overlap',
        mode: 'local',
        readOnly: true,
        //width : 520,
        triggerAction: 'all',
        forceSelection: true,
        allowBlank: false
    });

    return cboOverlap;
};

Toc.AverageCombo = function (config) {
    var dsAverage = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'categories',
            action: 'list_average'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            fields: ['id', 'label']
        }),
        autoLoad: false
    });

    var cboAverage = new Ext.form.ComboBox({
        fieldLabel: config.fieldLabel || 'Average',
        store: dsAverage,
        displayField: 'label',
        valueField: 'id',
        name: config.name || 'average',
        hiddenName: config.name || 'average',
        mode: 'local',
        readOnly: true,
        //width : 520,
        triggerAction: 'all',
        forceSelection: true,
        allowBlank: false
    });

    return cboAverage;
};

Toc.SamplePerRevCombo = function (config) {
    var dsSamplePerRev = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'categories',
            action: 'list_sample'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            fields: ['id', 'label']
        }),
        autoLoad: false
    });

    var cboSamplePerRev = new Ext.form.ComboBox({
        fieldLabel: config.fieldLabel || 'SamplePerRev',
        store: dsSamplePerRev,
        displayField: 'label',
        valueField: 'id',
        name: config.name || 'sample_rev',
        hiddenName: config.name || 'sample_rev',
        mode: 'local',
        readOnly: true,
        //width : 520,
        triggerAction: 'all',
        forceSelection: true,
        allowBlank: false
    });

    return cboSamplePerRev;
};

Toc.EquipmentypeCombo = function () {
    var dsEquipmentype = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'categories',
            action: 'list_equipmentype'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            fields: ['types_id', 'label']
        }),
        autoLoad: false
    });

    var cboEquipmentype = new Ext.form.ComboBox({
        fieldLabel: 'Equipment Type',
        store: dsEquipmentype,
        displayField: 'label',
        valueField: 'types_id',
        name: 'equipmentype',
        hiddenName: 'equipmentype',
        mode: 'local',
        readOnly: true,
        //width : 520,
        triggerAction: 'all',
        forceSelection: true,
        allowBlank: false
    });

    return cboEquipmentype;
};

Toc.ConfigurationCombo = function () {
    var dsConfiguration = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'categories',
            action: 'list_configuration'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            fields: ['configuration_id', 'label']
        }),
        autoLoad: false
    });

    var cboConfiguration = new Ext.form.ComboBox({
        fieldLabel: 'Configuration',
        store: dsConfiguration,
        displayField: 'label',
        valueField: 'configuration_id',
        name: 'configuration',
        hiddenName: 'configuration',
        mode: 'local',
        readOnly: true,
        //width : 520,
        triggerAction: 'all',
        forceSelection: true,
        allowBlank: false
    });

    return cboConfiguration;
};

Toc.PowerSourceCombo = function () {
    var dsPowerSource = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'categories',
            action: 'list_power'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            fields: ['sources_id', 'label']
        }),
        autoLoad: false
    });

    var cboPowerSource = new Ext.form.ComboBox({
        fieldLabel: 'Power Source',
        store: dsPowerSource,
        displayField: 'label',
        valueField: 'sources_id',
        name: 'powersource',
        hiddenName: 'powersource',
        mode: 'local',
        readOnly: true,
        //width : 520,
        triggerAction: 'all',
        forceSelection: true,
        allowBlank: false
    });

    return cboPowerSource;
};

Toc.TachoChannelCombo = function (config) {
    var dsTachoChannel = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'categories',
            action: 'list_tacho'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            fields: ['channel', 'label']
        }),
        autoLoad: false
    });

    var cboTachoChannel = new Ext.form.ComboBox({
        fieldLabel: config.fieldLabel || 'Tachochannel',
        store: dsTachoChannel,
        displayField: 'label',
        valueField: 'channel',
        name: config.name || 'tachochannel',
        hiddenName: config.name || 'tachochannel',
        mode: 'local',
        readOnly: true,
        //width : 520,
        triggerAction: 'all',
        forceSelection: true,
        allowBlank: false
    });

    return cboTachoChannel;
};

Toc.EnvelopeTypeCombo = function (config) {
    var dsEnvelopeType = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'categories',
            action: 'list_envelope'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            fields: ['id', 'label']
        }),
        autoLoad: false
    });

    var cboEnvelopeType = new Ext.form.ComboBox({
        fieldLabel: config.fieldLabel || 'EnvelopeType',
        store: dsEnvelopeType,
        displayField: 'label',
        valueField: 'id',
        name: config.name || 'envelopetype',
        hiddenName: config.name || 'envelopetype',
        mode: 'local',
        readOnly: true,
        //width : 520,
        triggerAction: 'all',
        forceSelection: true,
        allowBlank: false
    });

    return cboEnvelopeType;
};

Toc.ESA_ChannelCombo = function (config) {
    var dsESA_ChannelType = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'categories',
            action: 'list_esa'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            fields: ['id', 'label']
        }),
        autoLoad: false
    });

    var cboESA_Channel = new Ext.form.ComboBox({
        fieldLabel: config.fieldLabel || 'ESA_Channel',
        store: dsESA_Channel,
        displayField: 'label',
        valueField: 'id',
        name: config.name || 'esa_channel',
        hiddenName: config.name || 'esa_channel',
        mode: 'local',
        readOnly: true,
        //width : 520,
        triggerAction: 'all',
        forceSelection: true,
        allowBlank: false
    });

    return cboESA_Channel;
};

Toc.RotDirCombo = function () {
    var dsRotDir = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'categories',
            action: 'list_rotdir'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            fields: ['index', 'label']
        }),
        autoLoad: false
    });

    var cboRotDir = new Ext.form.ComboBox({
        fieldLabel: 'RotatingDirection',
        store: dsRotDir,
        displayField: 'label',
        valueField: 'index',
        name: 'rotdir',
        hiddenName: 'rotdir',
        mode: 'local',
        readOnly: true,
        //width : 520,
        triggerAction: 'all',
        forceSelection: true,
        allowBlank: false
    });

    return cboRotDir;
};

Toc.PercentCombo = function (config) {
    var dsPercent = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'categories',
            action: 'list_percent'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            fields: ['index', 'label']
        }),
        autoLoad: false
    });

    var cboPercent = new Ext.form.ComboBox({
        fieldLabel: config.fieldLabel,
        store: dsPercent,
        displayField: 'label',
        valueField: 'index',
        name: config.name,
        hiddenName: config.hiddenName,
        mode: 'local',
        readOnly: true,
        //width : 520,
        triggerAction: 'all',
        forceSelection: true,
        allowBlank: false
    });

    return cboPercent;
};

Toc.showDetails = function (node, panel) {
    if (node.customers_id) {
        var pnl = null;
        var pnlAdresse = null;
        var pnlPermissions = null;
        var pnlDocuments = null;
        var pnlImages = null;
        var tab = null;
        var pnlNotifications = null;

        if (node) {
            panel.node = node;
        }
        else {
            Ext.Msg.alert(TocLanguage.msgErrTitle, "Aucun element selectionne !!!");
            return false;
        }

        switch (node.attributes.content_type) {
            case 'sensor':
                pnl = new Toc.SensorPanel({component_id: node.parentNode.id, title: node.text});

                pnlPermissions = new Toc.content.PermissionsPanel({customers_id: node.customers_id, content_id: node.id, content_type: 'sensor', owner: this.owner});
                pnlNotifications = new Toc.NotificationsGrid({customers_id: node.customers_id, content_id: node.id, content_type: 'sensor', owner: this.owner});
                pnlDocuments = new Toc.content.DocumentsPanel({content_id: node.id, content_type: 'sensor', owner: Toc.content.ContentManager, can_edit: true});
                pnlImages = new Toc.content.ImagesPanel({content_id: node.id, content_type: 'sensor', owner: Toc.content.ContentManager});

                tab = new Ext.TabPanel({
                    activeTab: 0,
                    defaults: {
                        hideMode: 'offsets'
                    },
                    deferredRender: false,
                    items: [pnl, pnlPermissions, pnlDocuments, pnlImages, pnlNotifications]
                });

                panel.add(tab);
                panel.doLayout();
                panel.form.reset();
                panel.form.baseParams['sensors_id'] = node.id;
                panel.form.baseParams['component_id'] = node.parentNode.id;
                panel.form.baseParams['module'] = 'categories';
                panel.form.baseParams['action'] = 'save_sensor';

                panel.on('saveSuccess', function () {
                    if (pnl && node) {
                        node.setText(pnl.code.getValue());
                    }
                });

                if (tab) {
                    tab.getEl().mask('Chargement Sensor en cours....');
                }

                panel.load({
                        url: Toc.CONF.CONN_URL,
                        params: {
                            action: 'load_sensor'
                        },
                        success: function (form, action) {
                            if (tab && pnl) {
                                pnl.code.setValue(action.result.data.code);
                                pnl.signalname.setValue(action.result.data.signalname);
                                pnl.cpms_ip.setValue(action.result.data.cpms_ip);
                                pnl.manufacturer.setValue(action.result.data.manufacturer);
                                pnl.sensitivity.setValue(action.result.data.sensitivity);
                                pnl.sensitivity_unit.setValue(action.result.data.sensitivity_unit);
                                pnl.ofset.setValue(action.result.data.offset);
                                pnl.time_analysis.setValue(action.result.data.time_analysis);
                                pnl.orbit_analysis.setValue(action.result.data.orbit_analysis);
                                pnl.fftanalysis.setValue(action.result.data.fftanalysis);
                                pnl.order_analysis.setValue(action.result.data.order_analysis);
                                pnl.envelope_analysis.setValue(action.result.data.envelope_analysis);

                                pnl.bpfu_env.setValue(action.result.data.bpfu_env);
                                pnl.bpfo_env.setValue(action.result.data.bpfo_env);
                                pnl.tpf_env.setValue(action.result.data.tpf_env);

                                pnl.measurement_range.setValue(action.result.data.measurement_range);
                                pnl.frequency_range.setValue(action.result.data.frequency_range);
                                pnl.temperaturerange.setValue(action.result.data.temperaturerange);
                                pnl.impedance.setValue(action.result.data.impedance);
                                pnl.calibration_date.setValue(action.result.data.calibration_date);
                                pnl.serial_number.setValue(action.result.data.serial_number);
                                pnl.component.setValue(action.result.data.component);
                                pnl.sensortypecode.setValue(action.result.data.sensortypecode);
                                pnl.angle.setValue(action.result.data.angle);
                                pnl.orientation.setValue(action.result.data.orientation);
                                pnl.motio.setValue(action.result.data.motion);
                                pnl.attachment_method.setValue(action.result.data.attachment_method);
                                pnl.jonction_box.setValue(action.result.data.jonction_box);
                                pnl.acquisitionstation.setValue(action.result.data.acquisitionstation);

                                pnl.envelopetype.getStore().on('beforeload', function () {
                                    pnl.getEl().mask('Chargement envelopetype....');
                                }, this);

                                pnl.envelopetype.getStore().on('load', function () {
                                    pnl.envelopetype.setValue(action.result.data.envelopetype);
                                    pnl.getEl().unmask();
                                }, this);

                                pnl.envelopetype.getStore().load();

                                pnl.frfchannel_y.getStore().on('beforeload', function () {
                                    pnl.getEl().mask('Chargement frfchannel_y....');
                                }, this);

                                pnl.frfchannel_y.getStore().on('load', function () {
                                    pnl.frfchannel_y.setValue(action.result.data.frfchannel_y);
                                    pnl.getEl().unmask();
                                }, this);

                                pnl.frfchannel_y.getStore().load();

                                pnl.orbit_channel_y.getStore().on('beforeload', function () {
                                    pnl.getEl().mask('Chargement orbit_channel_y....');
                                }, this);

                                pnl.orbit_channel_y.getStore().on('load', function () {
                                    pnl.orbit_channel_y.setValue(action.result.data.orbit_channel_y);
                                    pnl.getEl().unmask();
                                }, this);

                                pnl.orbit_channel_y.getStore().load();

                                pnl.sample_rev.getStore().on('beforeload', function () {
                                    pnl.getEl().mask('Chargement sample_rev....');
                                }, this);

                                pnl.sample_rev.getStore().on('load', function () {
                                    pnl.sample_rev.setValue(action.result.data.sample_rev);
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
                                    pnl.getEl().unmask();
                                }, this);

                                pnl.overlap.getStore().load();

                                pnl.window_fft.getStore().on('beforeload', function () {
                                    pnl.getEl().mask('Chargement window_fft....');
                                }, this);

                                pnl.window_fft.getStore().on('load', function () {
                                    pnl.window_fft.setValue(action.result.data.window_fft);
                                    pnl.getEl().unmask();
                                }, this);

                                pnl.window_fft.getStore().load();

                                pnl.window_length_s.getStore().on('beforeload', function () {
                                    pnl.getEl().mask('Chargement window_length_s....');
                                }, this);

                                pnl.window_length_s.getStore().on('load', function () {
                                    pnl.window_length_s.setValue(action.result.data.window_length_s);
                                    pnl.getEl().unmask();
                                }, this);

                                pnl.window_length_s.getStore().load();

                                pnl.channel.getStore().on('beforeload', function () {
                                    pnl.getEl().mask('Chargement channel....');
                                }, this);

                                pnl.channel.getStore().on('load', function () {
                                    pnl.channel.setValue(action.result.data.channel);
                                    pnl.getEl().unmask();
                                }, this);

                                pnl.channel.getStore().load();

                                pnl.cpmsslot.getStore().on('beforeload', function () {
                                    pnl.getEl().mask('Chargement cpmsslot....');
                                }, this);

                                pnl.cpmsslot.getStore().on('load', function () {
                                    pnl.cpmsslot.setValue(action.result.data.cpmsslot);
                                    pnl.getEl().unmask();
                                }, this);

                                pnl.cpmsslot.getStore().load();

                                pnl.sampling_freq.getStore().on('beforeload', function () {
                                    pnl.getEl().mask('Chargement sampling_freq....');
                                }, this);

                                pnl.sampling_freq.getStore().on('load', function () {
                                    pnl.sampling_freq.setValue(action.result.data.sampling_freq);
                                    pnl.getEl().unmask();
                                }, this);

                                pnl.sampling_freq.getStore().load();

                                pnl.record_length.getStore().on('beforeload', function () {
                                    pnl.getEl().mask('Chargement record_length....');
                                }, this);

                                pnl.record_length.getStore().on('load', function () {
                                    pnl.record_length.setValue(action.result.data.record_length);
                                    pnl.getEl().unmask();
                                }, this);

                                pnl.record_length.getStore().load();

                                pnl.engineering_unit.getStore().on('beforeload', function () {
                                    pnl.getEl().mask('Chargement engineering_unit....');
                                }, this);

                                pnl.engineering_unit.getStore().on('load', function () {
                                    pnl.engineering_unit.setValue(action.result.data.engineering_unit);
                                    pnl.getEl().unmask();
                                }, this);

                                pnl.engineering_unit.getStore().load();

                                tab.getEl().unmask();
                            }

                            panel.topToolbar.items.items[0].enable();
                            panel.topToolbar.items.items[2].enable();
                            panel.form.baseParams['action'] = 'save_sensor';

                            //panel.setValues(action.result.data);
                        },
                        failure: function (form, action) {
                            Ext.Msg.alert(TocLanguage.msgErrTitle, "Un probleme est survenu lors du chargement ...");
                            if (tab) {
                                tab.getEl().unmask();
                            }

                            panel.topToolbar.items.items[0].disable();
                            panel.topToolbar.items.items[2].disable();

                            panel.form.baseParams['action'] = 'save_sensor';
                        },
                        scope: this
                    },
                    this
                );

                break;

            case 'asset':
                pnl = new Toc.AssetPanel({lines_id: node.parentNode.id, title: node.text});
                pnlPermissions = new Toc.content.PermissionsPanel({customers_id: node.customers_id, content_id: node.id, content_type: 'asset', owner: this.owner});
                pnlNotifications = new Toc.NotificationsGrid({customers_id: node.customers_id, content_id: node.id, content_type: 'asset', owner: this.owner});
                pnlDocuments = new Toc.content.DocumentsPanel({content_id: node.id, content_type: 'asset', owner: Toc.content.ContentManager, can_edit: true});
                pnlImages = new Toc.content.ImagesPanel({content_id: node.id, content_type: 'asset', owner: Toc.content.ContentManager});

                tab = new Ext.TabPanel({
                    activeTab: 0,
                    defaults: {
                        hideMode: 'offsets'
                    },
                    deferredRender: false,
                    items: [pnl, pnlPermissions, pnlDocuments, pnlImages, pnlNotifications]
                });

                panel.add(tab);
                panel.doLayout();
                panel.form.reset();
                panel.form.baseParams['asset_id'] = node.id;
                panel.form.baseParams['lines_id'] = node.parentNode.id;
                panel.form.baseParams['module'] = 'categories';
                panel.form.baseParams['action'] = 'save_asset';

                panel.on('saveSuccess', function () {
                    if (pnl && node) {
                        node.setText(pnl.name.getValue());
                    }
                });

                if (tab) {
                    tab.getEl().mask('Chargement Asset en cours....');
                }

                panel.load({
                        url: Toc.CONF.CONN_URL,
                        params: {
                            action: 'load_asset'
                        },
                        success: function (form, action) {
                            if (tab && pnl) {
                                pnl.code.setValue(action.result.data.code);
                                pnl.name.setValue(action.result.data.name);
                                pnl.cpms_ip.setValue(action.result.data.cpms_ip);
                                pnl.cpms_mac.setValue(action.result.data.cpms_mac);
                                pnl.cpms_controller.setValue(action.result.data.cpms_controller);
                                //pnl.cpms_configurator.setValue(action.result.data.cpms_configurator);
                                pnl.location.setValue(action.result.data.location);
                                pnl.manufacturer.setValue(action.result.data.manufacturer);
                                pnl.model.setValue(action.result.data.model);
                                pnl.fonction.setValue(action.result.data.fonction);
                                pnl.norms.setValue(action.result.data.norms);

                                pnl.CPMS_TypeCombo.getStore().on('load', function () {
                                    pnl.CPMS_TypeCombo.setValue(action.result.data.cpms_type);
                                }, this);

                                pnl.CPMS_TypeCombo.getStore().load();

                                pnl.CpmsSlotCombo.getStore().on('load', function () {
                                    pnl.CpmsSlotCombo.setValue(action.result.data.cpms_slotnumber);
                                }, this);

                                pnl.CpmsSlotCombo.getStore().load();

                                pnl.EquipmentypeCombo.getStore().on('load', function () {
                                    pnl.EquipmentypeCombo.setValue(action.result.data.equipmentype);
                                }, this);

                                pnl.EquipmentypeCombo.getStore().load();

                                pnl.UsersCombo.getStore().on('load', function () {
                                    pnl.UsersCombo.setValue(action.result.data.administrators_id);
                                }, this);

                                pnl.UsersCombo.getStore().load();

                                pnl.ConfigurationCombo.getStore().on('load', function () {
                                    pnl.ConfigurationCombo.setValue(action.result.data.configuration);
                                }, this);

                                pnl.ConfigurationCombo.getStore().load();

                                pnl.PowerSourceCombo.getStore().on('load', function () {
                                    pnl.PowerSourceCombo.setValue(action.result.data.powersource);
                                }, this);

                                pnl.PowerSourceCombo.getStore().load();

                                pnl.deltaprozent.getStore().on('load', function () {
                                    pnl.deltaprozent.setValue(action.result.data.deltaprozent);
                                }, this);

                                pnl.deltaprozent.getStore().load();

                                pnl.rotdir.getStore().on('load', function () {
                                    pnl.rotdir.setValue(action.result.data.rotdir);
                                }, this);

                                pnl.rotdir.getStore().load();

                                pnl.tachochannel.getStore().on('load', function () {
                                    pnl.tachochannel.setValue(action.result.data.tachochannel);
                                }, this);

                                pnl.tachochannel.getStore().load();

                                pnl.norms.setValue(action.result.data.norms);
                                pnl.support.setValue(action.result.data.support);
                                pnl.coupling.setValue(action.result.data.coupling);
                                pnl.ratedpower_w.setValue(action.result.data.ratedpower_w);
                                pnl.ratedspeed_rpm.setValue(action.result.data.ratedspeed_rpm);
                                pnl.ratedtorque_nm.setValue(action.result.data.ratedtorque_nm);
                                pnl.ratedvoltage_v.setValue(action.result.data.ratedvoltage_v);
                                pnl.ratedcurrent_a.setValue(action.result.data.ratedcurrent_a);
                                pnl.minspeed_rpm.setValue(action.result.data.minspeed_rpm);
                                pnl.maxspeed_rpm.setValue(action.result.data.maxspeed_rpm);
                                //pnl.tachochannel.setValue(action.result.data.tachochannel);
                                pnl.pulse_per_rev.setValue(action.result.data.pulse_per_rev);
                                pnl.triggerlevel.setValue(action.result.data.triggerlevel);
                                //pnl.rotdir.setValue(action.result.data.rotdir);
                                pnl.op1_name.setValue(action.result.data.op1_name);
                                pnl.op2_name.setValue(action.result.data.op2_name);
                                pnl.op3_name.setValue(action.result.data.op3_name);
                                pnl.op4_name.setValue(action.result.data.op4_name);
                                pnl.op5_name.setValue(action.result.data.op5_name);
                                pnl.op6_name.setValue(action.result.data.op6_name);
                                pnl.op7_name.setValue(action.result.data.op7_name);
                                pnl.op8_name.setValue(action.result.data.op8_name);
                                pnl.op9_name.setValue(action.result.data.op9_name);
                                pnl.op10_name.setValue(action.result.data.op10_name);
                                //pnl.op1_index.setValue(action.result.data.op1_index);
                                //pnl.op2_index.setValue(action.result.data.op2_index);

                                //pnl.deltaprozent.setValue(action.result.data.deltaprozent);
                                pnl.kmean.setValue(action.result.data.kmean);
                                pnl.kmin.setValue(action.result.data.kmin);
                                pnl.kmax.setValue(action.result.data.kmax);
                                pnl.kstd.setValue(action.result.data.kstd);
                                pnl.kral.setValue(action.result.data.kral);
                                pnl.kal.setValue(action.result.data.kal);
                                pnl.refwindow.setValue(action.result.data.refwindow);
                                pnl.movingwindow.setValue(action.result.data.movingwindow);
                                pnl.movingdeltaanalyse.setValue(action.result.data.movingdeltaanalyse);
                                pnl.severitylimit1.setValue(action.result.data.severitylimit1);
                                pnl.severitylimit2.setValue(action.result.data.severitylimit2);
                                pnl.severitylimit3.setValue(action.result.data.severitylimit3);
                                pnl.severitylimit4.setValue(action.result.data.severitylimit4);

                                tab.getEl().unmask();
                            }

                            panel.topToolbar.items.items[0].enable();
                            panel.topToolbar.items.items[2].enable();
                            panel.form.baseParams['action'] = 'save_asset';

                            //panel.setValues(action.result.data);
                        },
                        failure: function (form, action) {
                            Ext.Msg.alert(TocLanguage.msgErrTitle, action.result.feedback);
                            if (tab) {
                                tab.getEl().unmask();
                            }

                            panel.topToolbar.items.items[0].disable();
                            panel.topToolbar.items.items[2].disable();

                            panel.form.baseParams['action'] = 'save_asset';
                        },
                        scope: this
                    },
                    this
                );

                break;

            case 'line':
                pnl = new Toc.LinePanel({plants_id: node.parentNode.id, title: node.text});
                pnlPermissions = new Toc.content.PermissionsPanel({customers_id: node.customers_id, content_id: node.id, content_type: 'line', owner: this.owner});
                pnlNotifications = new Toc.NotificationsGrid({customers_id: node.customers_id, content_id: node.id, content_type: 'line', owner: this.owner});
                pnlDocuments = new Toc.content.DocumentsPanel({content_id: node.id, content_type: 'line', owner: Toc.content.ContentManager, can_edit: true});
                pnlImages = new Toc.content.ImagesPanel({content_id: node.id, content_type: 'line', owner: Toc.content.ContentManager});

                tab = new Ext.TabPanel({
                    activeTab: 0,
                    defaults: {
                        hideMode: 'offsets'
                    },
                    deferredRender: false,
                    items: [pnl, pnlPermissions, pnlDocuments, pnlImages, pnlNotifications]
                });

                panel.add(tab);
                panel.doLayout();
                panel.form.reset();
                panel.form.baseParams['lines_id'] = node.id;
                panel.form.baseParams['plants_id'] = node.parentNode.id;
                panel.form.baseParams['module'] = 'categories';
                panel.form.baseParams['action'] = 'save_line';

                panel.on('saveSuccess', function () {
                    if (pnl && node) {
                        node.setText(pnl.name.getValue());
                    }
                });

                if (tab) {
                    tab.getEl().mask('Chargement Ligne en cours....');
                }

                panel.load({
                        url: Toc.CONF.CONN_URL,
                        params: {
                            action: 'load_line'
                        },
                        success: function (form, action) {
                            if (pnl) {
                                pnl.code.setValue(action.result.data.code);
                                pnl.name.setValue(action.result.data.name);
                                pnl.unit.setValue(action.result.data.unit);
                                pnl.building.setValue(action.result.data.building);
                                pnl.operator.setValue(action.result.data.operator);
                            }

                            if (tab) {
                                tab.getEl().unmask();
                            }

                            panel.topToolbar.items.items[0].enable();
                            panel.topToolbar.items.items[2].enable();
                            panel.form.baseParams['action'] = 'save_line';
                        },
                        failure: function (form, action) {
                            Ext.Msg.alert(TocLanguage.msgErrTitle, action.result.feedback);
                            if (tab) {
                                tab.getEl().unmask();
                            }

                            panel.topToolbar.items.items[0].disable();
                            panel.topToolbar.items.items[2].disable();

                            panel.form.baseParams['action'] = 'save_line';
                        },
                        scope: this
                    },
                    this
                );

                break;

            case 'component':
                pnl = new Toc.ComponentPanel({asset_id: node.parentNode.id, title: node.text});
                pnlPermissions = new Toc.content.PermissionsPanel({customers_id: node.customers_id, content_id: node.id, content_type: 'component', owner: this.owner});
                pnlNotifications = new Toc.NotificationsGrid({customers_id: node.customers_id, content_id: node.id, content_type: 'component', owner: this.owner});
                pnlDocuments = new Toc.content.DocumentsPanel({content_id: node.id, content_type: 'component', owner: Toc.content.ContentManager, can_edit: true});
                pnlImages = new Toc.content.ImagesPanel({content_id: node.id, content_type: 'component', owner: Toc.content.ContentManager});

                tab = new Ext.TabPanel({
                    activeTab: 0,
                    defaults: {
                        hideMode: 'offsets'
                    },
                    deferredRender: false,
                    items: [pnl, pnlPermissions, pnlDocuments, pnlImages, pnlNotifications]
                });

                panel.add(tab);
                panel.doLayout();
                panel.form.reset();
                panel.form.baseParams['component_id'] = node.id;
                panel.form.baseParams['asset_id'] = node.parentNode.id;
                panel.form.baseParams['module'] = 'categories';
                panel.form.baseParams['action'] = 'save_component';

                panel.on('saveSuccess', function () {
                    if (pnl && node) {
                        node.setText(pnl.name.getValue());
                    }
                });

                if (tab) {
                    tab.getEl().mask('Chargement Ligne en cours....');
                }

                panel.load({
                        url: Toc.CONF.CONN_URL,
                        params: {
                            action: 'load_component'
                        },
                        success: function (form, action) {
                            if (pnl) {
                                pnl.serial.setValue(action.result.data.serial);
                                pnl.model.setValue(action.result.data.model);
                                pnl.name.setValue(action.result.data.name);
                                pnl.function.setValue(action.result.data.function);
                                pnl.FirstNaturalFrequency.setValue(action.result.data.firstnaturalfrequency);
                                pnl.SecondNaturalFrequency.setValue(action.result.data.secondnaturalfrequency);
                                pnl.ThirdNaturalFrequency.setValue(action.result.data.thirdnaturalfrequency);
                                pnl.RollingBearing.setValue(action.result.data.rollingbearing);
                                pnl.RollingBearingWidth_m.setValue(action.result.data.rollingbearingwidth_m);
                                pnl.RollingBearingDiameter_m.setValue(action.result.data.rollingbearingdiameter_m);
                                pnl.NumberRollingElements.setValue(action.result.data.numberrollingelements);
                                pnl.RollingBearingContactAngle_Grad.setValue(action.result.data.rollingbearingcontactangle_grad);
                                pnl.OuterRingFrequency.setValue(action.result.data.outerringfrequency);
                                pnl.InnerRingFrequency.setValue(action.result.data.innerringfrequency);
                                pnl.CageFrequency.setValue(action.result.data.cagefrequency);
                                pnl.RollingElementRotationFrequency.setValue(action.result.data.rollingelementrotationfrequency);
                                pnl.RollingElementContactFrequency.setValue(action.result.data.rollingelementcontactfrequency);
                                pnl.JournalBearing.setValue(action.result.data.journalbearing);
                                pnl.JournalBearingFluidType.setValue(action.result.data.journalbearingfluidtype);
                                pnl.JournalBearingGap_um.setValue(action.result.data.journalbearinggap_um);
                                pnl.OilWhirlMinOrder.setValue(action.result.data.oilwhirlminorder);
                                pnl.OilWhirlMaxOrder.setValue(action.result.data.oilwhirlmaxorder);
                                pnl.MinFluidTemperature_C.setValue(action.result.data.minfluidtemperature_c);
                                pnl.MaxFluidTemperature_C.setValue(action.result.data.maxfluidtemperature_c);
                                pnl.MinFluidPressure_bar.setValue(action.result.data.minfluidpressure_bar);
                                pnl.Turbomachinery.setValue(action.result.data.turbomachinery);
                                pnl.BladesNumber.setValue(action.result.data.bladesnumber);
                                pnl.VanesNumber.setValue(action.result.data.vanesnumber);
                                pnl.BladeLength_m.setValue(action.result.data.bladelength_m);
                                pnl.BladePassFrequency.setValue(action.result.data.bladepassfrequency);
                                pnl.BladeTipFrequency.setValue(action.result.data.bladetipfrequency);
                                pnl.VanePassingFrequency.setValue(action.result.data.vanepassingfrequency);
                                pnl.BladeVanePassingFrequency.setValue(action.result.data.bladevanepassingfrequency);
                                pnl.Gear.setValue(action.result.data.gear);

                                pnl.Geartype.getStore().on('load', function () {
                                    pnl.Geartype.setValue(action.result.data.geartype);
                                }, this);

                                pnl.Geartype.getStore().load();

                                pnl.GearRatio.setValue(action.result.data.gearratio);
                                pnl.GearNumberStages.setValue(action.result.data.gearnumberstages);
                                pnl.GearLowSpeedShaftTeethNumber.setValue(action.result.data.gearlowspeedshaftteethnumber);
                                pnl.GearFastSpeedShaftTeethNumber.setValue(action.result.data.gearfastspeedshaftteethnumber);
                                pnl.GearRingTeethNumber.setValue(action.result.data.gearringteethnumber);
                                pnl.GearPlanetTeethNumber.setValue(action.result.data.gearplanetteethnumber);
                                pnl.GearPlanetaryCarrierTeethNumber.setValue(action.result.data.gearplanetarycarrierteethnumber);
                                pnl.GearFixedComponent.setValue(action.result.data.gearfixedcomponent);
                                pnl.GearSunFrequency.setValue(action.result.data.gearsunfrequency);
                                pnl.GearRingFrequency.setValue(action.result.data.gearringfrequency);
                                pnl.GearPlanetFrequency.setValue(action.result.data.gearplanetfrequency);
                                pnl.GearMeshFrequency.setValue(action.result.data.gearmeshfrequency);
                                pnl.GearTeethCommonFactor.setValue(action.result.data.gearteethcommonfactor);
                                pnl.GearHuntingToothFrequency.setValue(action.result.data.gearhuntingtoothfrequency);
                                pnl.GearAssemblyPhase.setValue(action.result.data.gearassemblyphase);
                                pnl.GearGhostFrequency.setValue(action.result.data.gearghostfrequency);
                                pnl.Belt.setValue(action.result.data.belt);
                                pnl.BeltDiameterD1_m.setValue(action.result.data.beltdiameterd1_m);
                                pnl.BeltDiameterD2_m.setValue(action.result.data.beltdiameterd2_m);
                                pnl.BeltAxialGap_m.setValue(action.result.data.beltaxialgap_m);
                                pnl.BeltTeethNumberZ1.setValue(action.result.data.beltteethnumberz1);
                                pnl.BeltTeethNumberZ2.setValue(action.result.data.beltteethnumberz2);
                                pnl.BeltLength_m.setValue(action.result.data.beltlength_m);
                                pnl.BeltSpeedN1_rpm.setValue(action.result.data.beltspeedn1_rpm);
                                pnl.BeltSpeedN2_rpm.setValue(action.result.data.beltspeedn2_rpm);
                                pnl.BeltFrequency.setValue(action.result.data.beltfrequency);
                                pnl.TimingBeltFrequency.setValue(action.result.data.timingbeltfrequency);
                                pnl.Motor_Generator.setValue(action.result.data.motor_generator);
                                pnl.MotorEfficiency.setValue(action.result.data.motorefficiency);
                                pnl.MotorPolePairs.setValue(action.result.data.motorpolepairs);
                                pnl.MotorRotorBars.setValue(action.result.data.motorrotorbars);
                                pnl.MotorStatorPoles.setValue(action.result.data.motorstatorpoles);
                                pnl.MotorStatorSlots.setValue(action.result.data.motorstatorslots);
                                pnl.MotorCoilsPerPole.setValue(action.result.data.motorcoilsperpole);
                                pnl.MotorLineOfFrequency.setValue(action.result.data.motorlineoffrequency);
                                pnl.MotorSynchronuousSpeedFrequency.setValue(action.result.data.motorsynchronuousspeedfrequency);
                                pnl.MotorRunningSpeedFrequency.setValue(action.result.data.motorrunningspeedfrequency);
                                pnl.MotorSlipFrequency.setValue(action.result.data.motorslipfrequency);
                                pnl.MotorSlipRatio.setValue(action.result.data.motorslipratio);
                                pnl.MotorPolePassFrequency.setValue(action.result.data.motorpolepassfrequency);
                                pnl.MotorSlotPassFrequency.setValue(action.result.data.motorslotpassfrequency);
                                pnl.MotorRotorBarFrequency.setValue(action.result.data.motorrotorbarfrequency);
                                pnl.MotorStatorSlotFrequency.setValue(action.result.data.motorstatorslotfrequency);
                                pnl.MotorCommutatorFrequency.setValue(action.result.data.motorcommutatorfrequency);
                                pnl.MotorStaticEccentricityFrequency.setValue(action.result.data.motorstaticeccentricityfrequency);
                                pnl.MotorDynamicEccentricity.setValue(action.result.data.motordynamiceccentricity);
                                pnl.MotorStatorMechanicalDamageFrequency.setValue(action.result.data.motorstatormechanicaldamagefrequency);
                                pnl.MotorRotorDefectFrequency.setValue(action.result.data.motorrotordefectfrequency);
                                pnl.MotorLooseStatorCoilFrequency.setValue(action.result.data.motorloosestatorcoilfrequency);
                            }

                            if (tab) {
                                tab.getEl().unmask();
                            }

                            panel.topToolbar.items.items[0].enable();
                            panel.topToolbar.items.items[2].enable();
                            panel.form.baseParams['action'] = 'save_component';
                        },
                        failure: function (form, action) {
                            Ext.Msg.alert(TocLanguage.msgErrTitle, action.result.feedback);
                            if (tab) {
                                tab.getEl().unmask();
                            }

                            panel.topToolbar.items.items[0].disable();
                            panel.topToolbar.items.items[2].disable();

                            panel.form.baseParams['action'] = 'save_component';
                        },
                        scope: this
                    },
                    this
                );

                break;

            case 'customer':
                pnlPermissions = new Toc.content.PermissionsPanel({customers_id: node.customers_id, content_id: node.id, content_type: 'customer', owner: this.owner});
                pnlNotifications = new Toc.NotificationsGrid({customers_id: node.customers_id, content_id: node.id, content_type: 'customer', owner: this.owner});
                pnlDocuments = new Toc.content.DocumentsPanel({content_id: node.id, content_type: 'customer', owner: Toc.content.ContentManager, can_edit: true});
                pnlImages = new Toc.content.ImagesPanel({content_id: node.id, content_type: 'customer', owner: Toc.content.ContentManager});

                tab = new Ext.TabPanel({
                    activeTab: 0,
                    defaults: {
                        hideMode: 'offsets'
                    },
                    deferredRender: false,
                    items: [pnlPermissions, pnlDocuments, pnlImages, pnlNotifications]
                });

                panel.add(tab);
                panel.doLayout();

                break;

            case 'plant':
                pnl = new Toc.PlantPanel({customers_id: node.parentNode.id, title: node.text, can_edit: true});
                pnlAdresse = new Toc.PlantAdressePanel({owner: this.owner, customers_id: node.parentNode.id, title: 'Adresse'});
                pnlPermissions = new Toc.content.PermissionsPanel({customers_id: node.customers_id, content_id: node.id, content_type: 'plant', owner: this.owner});
                pnlNotifications = new Toc.NotificationsGrid({customers_id: node.customers_id, content_id: node.id, content_type: 'plant', owner: this.owner});
                pnlDocuments = new Toc.content.DocumentsPanel({content_id: node.id, content_type: 'plant', owner: Toc.content.ContentManager, can_edit: true});
                pnlImages = new Toc.content.ImagesPanel({content_id: node.id, content_type: 'plant', owner: Toc.content.ContentManager});

                tab = new Ext.TabPanel({
                    activeTab: 0,
                    defaults: {
                        hideMode: 'offsets'
                    },
                    deferredRender: false,
                    items: [pnl, pnlAdresse, pnlPermissions, pnlDocuments, pnlImages, pnlNotifications]
                });

                panel.add(tab);
                panel.doLayout();
                panel.form.reset();
                panel.form.baseParams['plants_id'] = node.id;
                panel.form.baseParams['customers_id'] = node.parentNode.id;
                panel.form.baseParams['module'] = 'categories';
                panel.form.baseParams['action'] = 'save_plant';

                panel.on('saveSuccess', function () {
                    if (pnl && node) {
                        node.setText(pnl.categories_name.getValue());
                    }
                });

                if (tab) {
                    tab.getEl().mask('Chargement Usine en cours....');
                }

                panel.load({
                        url: Toc.CONF.CONN_URL,
                        params: {
                            action: 'load_plant'
                        },
                        success: function (form, action) {
                            if (pnl) {
                                pnl.code.setValue(action.result.data.code);
                                pnl.categories_name.setValue(action.result.data.categories_name);
                                pnl.location.setValue(action.result.data.location);
                                pnl.manufacturer.setValue(action.result.data.manufacturer);
                                pnl.model.setValue(action.result.data.model);
                                pnl.serial_number.setValue(action.result.data.serial_number);
                                pnl.operator.setValue(action.result.data.operator);
                                pnl.comments.setValue(action.result.data.comments);
                            }
                            else {
                                Ext.Msg.alert(TocLanguage.msgErrTitle, 'pnl not defined !!!');
                            }

                            if (pnlAdresse) {
                                pnlAdresse.adresse.setValue(action.result.data.adresse);
                                pnlAdresse.email.setValue(action.result.data.email);
                                pnlAdresse.phone.setValue(action.result.data.phone);
                                pnlAdresse.mobile.setValue(action.result.data.mobile);
                                pnlAdresse.fax.setValue(action.result.data.fax);
                                pnlAdresse.url.setValue(action.result.data.url);

                                //pnlAdresse.cboCountries.purgeListeners();

                                pnlAdresse.cboCountries.getStore().on('load', function () {
                                    pnlAdresse.cboCountries.setValue(action.result.data.country_id);
                                }, this);

                                pnlAdresse.cboCountries.getStore().load();
                            }
                            else {
                                Ext.Msg.alert(TocLanguage.msgErrTitle, 'pnlAdresse not defined !!!');
                            }

                            if (tab) {
                                tab.getEl().unmask();
                            }

                            panel.topToolbar.items.items[0].enable();
                            panel.topToolbar.items.items[2].enable();
                            panel.form.baseParams['action'] = 'save_plant';
                        },
                        failure: function (form, action) {
                            Ext.Msg.alert(TocLanguage.msgErrTitle, action.result.feedback);
                            if (tab) {
                                tab.getEl().unmask();
                            }

                            panel.topToolbar.items.items[0].disable();
                            panel.topToolbar.items.items[2].disable();

                            panel.form.baseParams['action'] = 'save_plant';
                        },
                        scope: this
                    },
                    this
                );

                break;
        }
    }

    return true;
};

Toc.eventlogGrid = function (config) {
    var that = this;
    config = config || {};

    config.region = 'center';
    config.started = false;
    config.border = false;
    config.loadMask = true;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords, forceFit: true};

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'categories',
            action: 'list_eventlog',
            customers_id: -1,
            content_id: config.content_id || -1,
            content_type: config.content_type || 'xx'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'events_id'
        }, [
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

    config.listeners = {
        show: function () {
            console.log('activate ...');
            that.onRefresh();
        }, scope: that
    };

    config.sm = new Ext.grid.CheckboxSelectionModel();
    config.plugins = config.rowActions;

    config.cm = new Ext.grid.ColumnModel([
        config.sm,
        {header: '', dataIndex: 'icon', width: 3, align: 'center'},
        {header: 'Date', dataIndex: 'event_date', width: 10, align: 'center'},
        {id: 'description', header: 'Description', dataIndex: 'description', width: 75},
        {id: 'user', header: 'Utilisateur', dataIndex: 'user', width: 10, align: 'center'}
    ]);
    config.autoExpandColumn = 'description';

    //config.customersCombo = Toc.content.ContentManager.getCustomersCombo({autoLoad : true,all : true});
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
        }
    ];

    //config.customersCombo.getStore().on('load', function (store, opt) {
//    that.onRefresh();
    //});

    config.start_date.setValue(new Date());
    config.end_date.setValue(new Date());

    //config.customersCombo.getStore().load();

    //config.customersCombo.on('select', function (combo, record, index) {
    //   that.customers_id = record.data.customers_id;
    //that.refreshGrid(customers_id);
    //});

    config.bbar = new Ext.PagingToolbar({
        pageSize: Toc.CONF.GRID_PAGE_SIZE,
        store: config.ds,
        iconCls: 'icon-grid',
        displayInfo: true,
        displayMsg: TocLanguage.displayMsg,
        emptyMsg: TocLanguage.emptyMsg
    });

    this.addEvents({'selectchange': true});

    Toc.eventlogGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.eventlogGrid, Ext.grid.GridPanel, {

    onRefresh: function () {
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

    onRowAction: function (grid, record, action, row, col) {
        switch (action) {
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

Toc.NotificationsGrid = function (config) {
    console.log('notificationsGrid');
    var that = this;
    config = config || {};
    //config.region = 'center';
    config.loadMask = true;
    config.title = ' Notifications';
    config.border = true;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords};

    render = function (status) {
        if (status == 1) {
            return '<img class="img-button" src="images/icon_status_green.gif" />&nbsp;<img class="img-button btn-status-off" style="cursor: pointer" src="images/icon_status_red_light.gif" />';
        } else {
            return '<img class="img-button btn-status-on" style="cursor: pointer" src="images/icon_status_green_light.gif" />&nbsp;<img class="img-button" src= "images/icon_status_red.gif" />';
        }
    };

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'content',
            action: 'list_notifications',
            content_id: config.content_id,
            customers_id: config.customers_id || '',
            content_type: config.content_type || ''
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'roles_id'
        }, [
            'roles_id',
            'user_name',
            'notify',
            'event',
            'icon',
            'roles_description',
            'email_address'
        ]),
        autoLoad: false
    });

    render = function (status) {
        if (status == 1) {
            return '<img class="img-button" src="images/icon_status_green.gif" />&nbsp;<img class="img-button btn-status-off" style="cursor: pointer" src="images/icon_status_red_light.gif" />';
        } else {
            return '<img class="img-button btn-status-on" style="cursor: pointer" src="images/icon_status_green_light.gif" />&nbsp;<img class="img-button" src= "images/icon_status_red.gif" />';
        }
    };

    config.cm = new Ext.grid.ColumnModel([
        {header: '', dataIndex: 'icon', width: 24},
        {
            id: 'name',
            header: 'Nom',
            dataIndex: 'user_name'
        },
        { id: 'email', header: 'Email', align: 'center', dataIndex: 'email_address'},
        { header: 'Notifier', align: 'center', renderer: render, dataIndex: 'notify'}
    ]);
    config.autoExpandColumn = 'email';
    config.stripeRows = true;

    var eventds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'content',
            action: 'list_events'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'event'
        }, [
            'event',
            'label'
        ]),
        autoLoad: true
    });

    config.comboEvents = new Ext.form.ComboBox({
        typeAhead: true,
        name: 'event',
        fieldLabel: 'Evenement',
        width: 400,
        triggerAction: 'all',
        mode: 'local',
        store: eventds,
        emptyText: 'Choisir un event',
        editable: false,
        valueField: 'event',
        displayField: 'label'
    });

    var thisObj = this;

    config.tbar = [
        {
            text: '',
            disabled: true,
            iconCls: 'refresh',
            handler: this.onRefresh,
            scope: this
        },
        '->',
        config.comboEvents
    ];

    config.comboEvents.on('select', function (combo, record, index) {
        thisObj.event = record.data.event;
        var store = thisObj.getStore();

        thisObj.topToolbar.items.items[0].enable();

        store.baseParams['event'] = thisObj.event;
        store.reload();
    });

    Toc.NotificationsGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.NotificationsGrid, Ext.grid.GridPanel, {

    onRefresh: function () {
        var store = this.getStore();
        store.reload();
    },

    refreshGrid: function (content_id, content_type) {
        this.content_type = content_type || this.content_type;

        if (content_id && this.content_type) {
            var store = this.getStore();

            store.baseParams['content_id'] = content_id;
            store.baseParams['content_type'] = content_type;
            store.load();
        }
    },

    onClick: function (e, target) {
        var t = e.getTarget();
        var v = this.view;
        var row = v.findRowIndex(t);
        var col = v.findCellIndex(t);
        var action = false;

        if (row !== false) {
            if (col > 0) {
                var record = this.getStore().getAt(row);
                var flagName = this.getColumnModel().getDataIndex(col);
                this.fireEvent('selectchange', record);
            }

            var btn = e.getTarget(".img-button");

            if (btn) {
                var field_id = this.getStore().getAt(row).get(this.id_field);
                action = btn.className.replace(/img-button btn-/, '').trim();
                var content_id = this.content_id;
                var content_type = this.content_type;
                var roles_id = this.getStore().getAt(row).get('roles_id');

                switch (action) {
                    case 'status-off':
                    case 'status-on':
                        flag = (action == 'status-on') ? 1 : 0;
                        this.setNotification(content_id, content_type, roles_id, flag);
                        break;
                }
            }
        }
    },

    setNotification: function (content_id, content_type, roles_id, flag) {
        if (this.event) {
            var params = {
                module: 'content',
                action: 'set_notification',
                content_id: content_id,
                content_type: content_type,
                event: this.event,
                roles_id: roles_id,
                flag: flag,
                permission: 'notify'
            };

            //params[this.id_field] = field_id;

            Ext.Ajax.request({
                url: Toc.CONF.CONN_URL,
                params: params,
                callback: function (options, success, response) {
                    var result = Ext.decode(response.responseText);

                    if (result.success == true) {
                        var store = this.getStore();
                        store.getById(roles_id).set('notify', flag);
                        store.commitChanges();
                    }
                },
                scope: this
            });
        }
        else {
            Ext.Msg.alert(TocLanguage.msgErrTitle, "Aucun event selectionne !!!");
        }
    }
});

Toc.TicketsGrid = function (config) {
    var that = this;
    config = config || {};

    config.region = 'center';
    config.title = 'Tickets';
    config.started = false;
    config.border = false;
    config.loadMask = true;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords, forceFit: true};

    config.ds = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'content',
            action: 'list_tickets',
            content_id: config.content_id || -1,
            content_type: config.content_type || 'xx',
            customers_id: 0
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'tickets_id'
        }, [
            'created',
            'tickets_id',
            'responsable',
            'status',
            'action',
            'description',
            'icon'
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
        autoLoad: true
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

    renderDescription = function(description) {
        return '<div style = "white-space : normal">' + description + '</div>';
    };

    config.plugins = [config.rowActions];

    config.cm = new Ext.grid.ColumnModel([
        {header: '', dataIndex: 'icon', width: 3, align: 'center'},
        {header: 'Date', dataIndex: 'created', width: 12, align: 'center'},
        {header: 'Status', dataIndex: 'status', width: 10, align: 'center'},
        {id: 'description', header: 'Description', dataIndex: 'description',renderer:renderDescription, width: 60},
        {id: 'user', header: 'Utilisateur', dataIndex: 'responsable', width: 10, align: 'center'},
        config.rowActions
    ]);
    config.autoExpandColumn = 'description';

    config.customersCombo = Toc.content.ContentManager.getCustomersCombo({autoLoad: true, all: true});
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
        }
    ];

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

    this.addEvents({'selectchange': true});

    Toc.TicketsGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.TicketsGrid, Ext.grid.GridPanel, {

    onEdit: function(record) {
        var dlg = new Toc.TicketDialog({edit : true,content_id : record.get('tickets_id')});

        dlg.on('saveSuccess', function() {
            this.onRefresh();
        }, this);

        dlg.show();
    },
    onClose: function(record) {
        var dlg = new Toc.TicketDialog({edit : true,content_id : record.get('tickets_id')});

        dlg.setTitle("Cloturer le Ticket : " + record.get('tickets_id'));

        dlg.on('saveSuccess', function() {
            this.onRefresh();
        }, this);

        dlg.show('close_ticket');
    },
    onArchive: function(record) {
        var dlg = new Toc.TicketDialog({edit : true,content_id : record.get('tickets_id')});

        dlg.setTitle("Archiver le Ticket : " + record.get('tickets_id'));

        dlg.on('saveSuccess', function() {
            this.onRefresh();
        }, this);

        dlg.show();
    },
    onDelete: function(record) {
        var dlg = new Toc.TicketDialog({edit : true,content_id : record.get('tickets_id')});

        dlg.setTitle("Supprimer le Ticket : " + record.get('tickets_id'));

        dlg.on('saveSuccess', function() {
            this.onRefresh();
        }, this);

        dlg.show();
    },
    onReopen: function(record) {
        var dlg = new Toc.TicketDialog({edit : true,content_id : record.get('tickets_id')});

        dlg.setTitle("ReOuvrir le Ticket : " + record.get('tickets_id'));

        dlg.on('saveSuccess', function() {
            this.onRefresh();
        }, this);

        dlg.show('reopen_ticket');
    },
    onAdd: function(record) {
        var dlg = new Toc.TicketDialog({edit : true,content_id : record.get('tickets_id')});

        dlg.setTitle("Ajouter un suivi au Ticket : " + record.get('tickets_id'));

        dlg.on('saveSuccess', function() {
            this.onRefresh();
        }, this);

        dlg.show('suivi_ticket');
    },
    onLog: function(record) {
        var dlg = new Toc.TicketDialog({edit : true,content_id : record.get('tickets_id')});

        dlg.setTitle("Journal du Ticket : " + record.get('tickets_id'));

        dlg.on('saveSuccess', function() {
            this.onRefresh();
        }, this);

        dlg.show('log_ticket');
    },
    onRefresh: function () {
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

    onRowAction: function (grid, record, action, row, col) {
        switch (action) {
            case 'icon-close-record':
                this.onClose(record);
                break;

            case 'icon-add-record':
                this.onAdd(record);
                break;

            case 'icon-reopen-record':
                this.onReopen(record);
                break;

            case 'icon-archive-record':
                this.onArchive(record);
                break;

            case 'icon-delete-record':
                this.onDelete(record);
                break;

            case 'icon-log-record':
                this.onLog(record);
                break;
        }
    },

    refreshData: function (scope) {
        if (scope) {
            var store = this.getStore();
            store.load();
        }
    },
    onClick: function (e, target) {
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

                switch (action) {
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

Toc.TicketDialog = function(config) {
    config = config || {};

    config.id = 'ticket-dialog-win';
    config.modal = true;
    config.layout = 'fit';
    config.width = 800;
    config.height = 400;
    config.iconCls = 'icon-tickets-win';
    config.items = this.buildForm();

    config.buttons = [
        {
            text: TocLanguage.btnSave,
            handler: function() {
                this.submitForm();
            },
            scope: this
        },
        {
            text: TocLanguage.btnClose,
            handler: function() {
                this.close();
            },
            scope: this
        }
    ];

    this.addEvents({'saveSuccess' : true});

    Toc.TicketDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.TicketDialog, Ext.Window, {
    show: function (action) {
        if(action)
        {
            if(action == 'log_ticket')
            {
                this.pnlTicket.removeAll();
                this.frmTicket.form.reset();
                this.frmTicket.form.baseParams['content_type'] = 'ticket';
                this.frmTicket.form.baseParams['content_id'] = this.content_id;
                this.pnlComments = new Toc.content.CommentsPanel({title : 'Journal',autoLoad : false,content_id: this.content_id, content_type: 'ticket', owner: Toc.content.ContentManager});

                this.pnlTicket.add(this.pnlComments);
                this.pnlTicket.doLayout();
                this.pnlComments.getStore().load();
            }
            else
            {
                this.frmTicket.form.reset();
                this.frmTicket.form.baseParams['tickets_id'] = this.content_id;
                this.frmTicket.form.baseParams['content_id'] = this.content_id;
                this.frmTicket.form.baseParams['action'] = action;
            }

            Toc.TicketDialog.superclass.show.call(this);
        }
        else
        {
            Ext.Msg.alert(TocLanguage.msgErrTitle,"Aucune Action definie !!!");
            this.close();
        }
    },
    getContentPanel: function() {
        var that = this;

        this.editor = new Toc.content.ContentManager.getHtmlEditor({fieldLabel : "Raison",name : 'comments_description',height : 300});

        this.pnlTicket = new Ext.Panel({
            border: false,
            layout: 'fit',
            defaults: {
                anchor: '96%'
            },
            items: [
                this.editor
            ]
        });

        return this.pnlTicket;
    },

    buildForm: function() {
        this.frmTicket = new Ext.form.FormPanel({
            fileUpload: true,
            layout : 'fit',
            url: Toc.CONF.CONN_URL,
            baseParams: {
                module: 'content',
                content_type : 'ticket'
            },
            deferredRender: false,
            items: [this.getContentPanel()]
        });

        return this.frmTicket;
    },

    submitForm : function() {
        this.frmTicket.form.submit({
            waitMsg: TocLanguage.formSubmitWaitMsg,
            success: function(form, action) {
                this.fireEvent('saveSuccess', action.result.feedback);
                this.close();
            },
            failure: function(form, action) {
                if (action.failureType != 'client') {
                    Ext.Msg.alert(TocLanguage.msgErrTitle, action.result.feedback);
                }
            },
            scope: this
        });
    }
});