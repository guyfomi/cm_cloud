Toc.SensorPanel = function (config) {
    config = config || {};

    config.layout = 'fit';
    config.deferredRender = false;
    config.items = this.getDataPanel();

    Toc.SensorPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.SensorPanel, Ext.Panel, {

    getDataPanel: function () {
        this.code = new Ext.form.TextField({fieldLabel: 'Code', name: 'code', allowBlank: true});
        this.signalname = new Ext.form.TextField({fieldLabel: 'Signal Name', name: 'signalname', allowBlank: false});
        //this.channel = new Ext.form.TextField({fieldLabel: 'CPMSChannel', name: 'channel', allowBlank: true});
        this.channel = new Toc.TachoChannelCombo({fieldLabel: 'CPMSChannel', name: 'channel', allowBlank: true});
        //this.cpmsslot = new Ext.form.TextField({fieldLabel: 'CPMSSlot', name: 'cpmsslot', allowBlank: true});
        this.cpmsslot = new Toc.CpmsSlotCombo({fieldLabel: 'CPMSSlot', name: 'cpmsslot', allowBlank: true});
        this.cpms_ip = new Ext.form.TextField({fieldLabel: 'CPMS_IP', name: 'cpms_ip', allowBlank: true});
        this.sampling_freq = new Toc.SamplingFrequencyCombo({fieldLabel: 'SamplingFrequency_hz', name: 'sampling_freq', allowBlank: true});
        this.record_length = new Toc.RecordLengthCombo({fieldLabel: 'RecordLength_s', name: 'record_length', allowBlank: true});
        this.sensitivity = new Ext.form.NumberField({fieldLabel: 'Sensitivity', name: 'sensitivity', allowBlank: true});
        this.sensitivity_unit = new Ext.form.TextField({fieldLabel: 'SensitivityUnit', name: 'sensitivity_unit', allowBlank: true});
        this.engineering_unit = new Toc.EngineeringUnitCombo({fieldLabel: 'EngineeringUnit', name: 'engineering_unit', allowBlank: true});
        this.ofset = new Ext.form.NumberField({fieldLabel: 'Offset EU', name: 'offset', allowBlank: true});
        this.time_analysis = new Ext.form.Checkbox({fieldLabel: 'TimeAnalysis', name: 'time_analysis', allowBlank: true});
        this.orbit_analysis = new Ext.form.Checkbox({fieldLabel: 'OrbitAnalysis', name: 'orbit_analysis', allowBlank: true});
        this.fftanalysis = new Ext.form.Checkbox({fieldLabel: 'FFTAnalysis', name: 'fftanalysis', allowBlank: true});
        this.order_analysis = new Ext.form.Checkbox({fieldLabel: 'OrderAnalysis', name: 'order_analysis', allowBlank: false});
        this.envelope_analysis = new Ext.form.Checkbox({fieldLabel: 'EnvelopeAnalysis', name: 'envelope_analysis', allowBlank: true});
        this.window_length_s = new Toc.WindowLengthCombo({fieldLabel: 'WindowLength_s', name: 'window_length_s', allowBlank: true});
        this.window_fft = new Toc.WindowFFTCombo({fieldLabel: 'WindowFFT', name: 'window_fft', allowBlank: true});

        this.overlap = new Toc.OverlapCombo({fieldLabel: 'Overlap', name: 'overlap', allowBlank: true});
        this.average = new Toc.AverageCombo({fieldLabel: 'Average', name: 'average', allowBlank: true});
        this.sample_rev = new Toc.SamplePerRevCombo({fieldLabel: 'SamplePerRev', name: 'sample_rev', allowBlank: true});
        this.orbit_channel_y = new Toc.TachoChannelCombo({fieldLabel: 'OrbitChannelY', name: 'orbit_channel_y', allowBlank: true});
        this.frfchannel_y = new Toc.TachoChannelCombo({fieldLabel: 'FRFChannelY', name: 'frfchannel_y', allowBlank: true});
        this.envelopetype = new Toc.EnvelopeTypeCombo({fieldLabel: 'EnvelopeType', name: 'envelopetype', allowBlank: true});
        this.bpfu_env = new Ext.form.NumberField({fieldLabel: 'BPFu_Env', name: 'bpfu_env', allowBlank: true});
        this.bpfo_env = new Ext.form.NumberField({fieldLabel: 'BPFo_Env', name: 'bpfo_env', allowBlank: true});
        this.tpf_env = new Ext.form.NumberField({fieldLabel: 'TPF_Env', name: 'tpf_env', allowBlank: true});
        this.esa_channel = new Ext.form.TextField({fieldLabel: 'ESA_Channel', name: 'esa_channel', allowBlank: true});

        this.measurement_range = new Ext.form.TextField({fieldLabel: 'MeasurementRange', name: 'measurement_range', allowBlank: true});
        this.frequency_range = new Ext.form.TextField({fieldLabel: 'FrequencyRange', name: 'frequency_range', allowBlank: true});
        this.temperaturerange = new Ext.form.TextField({fieldLabel: 'TemperatureRange', name: 'temperaturerange', allowBlank: true});
        this.impedance = new Ext.form.TextField({fieldLabel: 'Impedance', name: 'impedance', allowBlank: true});
        this.calibration_date = new Ext.form.DateField({fieldLabel: 'CalibrationDate', name: 'calibration_date', allowBlank: true});
        this.manufacturer = new Ext.form.TextField({fieldLabel: 'Manufacturer', name: 'manufacturer', allowBlank: true});
        this.serial_number = new Ext.form.TextField({fieldLabel: 'SerialNumber', name: 'serial_number', allowBlank: true});
        this.component = new Ext.form.TextField({fieldLabel: 'Component', name: 'component', allowBlank: true});
        this.sensortypecode = new Ext.form.TextField({fieldLabel: 'SensorTypeCode', name: 'sensortypecode', allowBlank: true});
        this.angle = new Ext.form.TextField({fieldLabel: 'Angle', name: 'angle', allowBlank: true});
        this.orientation = new Ext.form.TextField({fieldLabel: 'Orientation', name: 'orientation', allowBlank: true});
        this.motio = new Ext.form.TextField({fieldLabel: 'Motion', name: 'motion', allowBlank: true});
        this.attachment_method = new Ext.form.TextField({fieldLabel: 'AttachmentMethod', name: 'attachment_method', allowBlank: true});
        this.jonction_box = new Ext.form.TextField({fieldLabel: 'JonctionBox', name: 'jonction_box', allowBlank: true});
        this.acquisitionstation = new Ext.form.TextField({fieldLabel: 'AcquisitionStation', name: 'acquisitionstation', allowBlank: true});

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
                        this.signalname,
                        this.channel,
                        this.cpmsslot,
                        this.cpms_ip,
                        this.manufacturer,
                        this.sampling_freq,
                        this.record_length,
                        this.sensitivity,
                        this.sensitivity_unit,
                        this.engineering_unit,
                        this.ofset,
                        this.time_analysis,
                        this.orbit_analysis
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
                        this.fftanalysis,
                        this.order_analysis,
                        this.envelope_analysis,
                        this.window_length_s,
                        this.window_fft,
                        this.overlap,
                        this.average,
                        this.sample_rev,
                        this.orbit_channel_y,
                        this.frfchannel_y,
                        this.envelopetype,
                        this.bpfu_env,
                        this.bpfo_env,
                        this.tpf_env
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
                        this.measurement_range,
                        this.frequency_range,
                        this.temperaturerange,
                        this.impedance,
                        this.calibration_date,
                        this.serial_number,
                        this.component,
                        this.sensortypecode,
                        this.angle,
                        this.orientation,
                        this.motio,
                        this.attachment_method,
                        this.jonction_box,
                        this.acquisitionstation
                    ]
                }
            ]
        });

        return this.pnlData;
    }
});

Toc.SensorDialog = function (config) {
    config = config || {};

    config.id = 'sensor-dialog-win';
    config.title = 'New Sensor';
    config.layout = 'fit';
    config.width = 900;
    config.height = 500;
    config.modal = true;
    config.iconCls = 'icon-sensor-win';
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

    Toc.SensorDialog.superclass.constructor.call(this, config);
};

Ext.extend(Toc.SensorDialog, Ext.Window, {

    show: function () {
        //this.customers_id = id;
        if (this.component_id == -1) {
            Ext.Msg.alert(TocLanguage.msgErrTitle, "Component invalide !!!");
        }
        else {
            this.frmSensor.form.reset();
            this.frmSensor.form.baseParams['component_id'] = this.component_id;

            //this.pnlPermissions = new Toc.content.PermissionsPanel({content_id : this.layoutId,content_type : 'pages',owner : this.owner});
            //this.pnlComments =  new Toc.content.CommentsPanel({content_id : this.layoutId,content_type : 'pages',owner : Toc.content.ContentManager});
            //this.tablayout.add(this.pnlPermissions);
            //this.tablayout.add(this.pnlComments);
            //this.tablayout.add(this.pnlAdresse);

            Toc.SensorDialog.superclass.show.call(this);

            if (this.sensors_id) {
                this.loadSensor(this.pnlGeneral);
            }
            else
            {
                this.pnlGeneral.envelopetype.getStore().load();
                this.pnlGeneral.frfchannel_y.getStore().load();
                this.pnlGeneral.orbit_channel_y.getStore().load();
                this.pnlGeneral.sample_rev.getStore().load();
                this.pnlGeneral.average.getStore().load();
                this.pnlGeneral.overlap.getStore().load();
                this.pnlGeneral.window_fft.getStore().load();
                this.pnlGeneral.window_length_s.getStore().load();
                this.pnlGeneral.channel.getStore().load();
                this.pnlGeneral.cpmsslot.getStore().load();
                this.pnlGeneral.sampling_freq.getStore().load();
                this.pnlGeneral.record_length.getStore().load();
                this.pnlGeneral.engineering_unit.getStore().load();
            }
        }
    },

    loadSensor: function (panel) {
        if (this.sensors_id) {
            this.frmSensor.form.baseParams['sensors_id'] = this.sensors_id;
            if (panel) {
                panel.getEl().mask('Chargement Sensor en cours....');
            }
            this.frmSensor.load({
                    url: Toc.CONF.CONN_URL,
                    params: {
                        action: 'load_sensor'
                    },
                    success: function (form, action) {
                        this.pnlGeneral.envelopetype.getStore().on('load', function () {
                            this.pnlGeneral.envelopetype.setValue(action.result.data.envelopetype);
                        }, this);

                        this.pnlGeneral.envelopetype.getStore().load();

                        this.pnlGeneral.frfchannel_y.getStore().on('load', function () {
                            this.pnlGeneral.frfchannel_y.setValue(action.result.data.frfchannel_y);
                        }, this);

                        this.pnlGeneral.frfchannel_y.getStore().load();

                        this.pnlGeneral.orbit_channel_y.getStore().on('load', function () {
                            this.pnlGeneral.orbit_channel_y.setValue(action.result.data.orbit_channel_y);
                        }, this);

                        this.pnlGeneral.orbit_channel_y.getStore().load();

                        this.pnlGeneral.sample_rev.getStore().on('load', function () {
                            this.pnlGeneral.sample_rev.setValue(action.result.data.sample_rev);
                        }, this);

                        this.pnlGeneral.sample_rev.getStore().load();

                        this.pnlGeneral.average.getStore().on('load', function () {
                            this.pnlGeneral.average.setValue(action.result.data.average);
                        }, this);

                        this.pnlGeneral.average.getStore().load();

                        this.pnlGeneral.overlap.getStore().on('load', function () {
                            this.pnlGeneral.overlap.setValue(action.result.data.overlap);
                        }, this);

                        this.pnlGeneral.overlap.getStore().load();

                        this.pnlGeneral.window_fft.getStore().on('load', function () {
                            this.pnlGeneral.window_fft.setValue(action.result.data.window_fft);
                        }, this);

                        this.pnlGeneral.window_fft.getStore().load();

                        this.pnlGeneral.window_length_s.getStore().on('load', function () {
                            this.pnlGeneral.window_length_s.setValue(action.result.data.window_length_s);
                        }, this);

                        this.pnlGeneral.window_length_s.getStore().load();

                        this.pnlGeneral.channel.getStore().on('load', function () {
                            this.pnlGeneral.channel.setValue(action.result.data.channel);
                        }, this);

                        this.pnlGeneral.channel.getStore().load();

                        this.pnlGeneral.cpmsslot.getStore().on('load', function () {
                            this.pnlGeneral.cpmsslot.setValue(action.result.data.cpmsslot);
                        }, this);

                        this.pnlGeneral.cpmsslot.getStore().load();

                        this.pnlGeneral.sampling_freq.getStore().on('load', function () {
                            this.pnlGeneral.sampling_freq.setValue(action.result.data.sampling_freq);
                        }, this);

                        this.pnlGeneral.sampling_freq.getStore().load();

                        this.pnlGeneral.record_length.getStore().on('load', function () {
                            this.pnlGeneral.record_length.setValue(action.result.data.record_length);
                        }, this);

                        this.pnlGeneral.record_length.getStore().load();

                        this.pnlGeneral.engineering_unit.getStore().on('load', function () {
                            this.pnlGeneral.engineering_unit.setValue(action.result.data.engineering_unit);
                        }, this);

                        this.pnlGeneral.engineering_unit.getStore().load();

                        if (panel) {
                            panel.getEl().unmask();
                        }
                    },
                    failure: function (form, action) {
                        Ext.Msg.alert(TocLanguage.msgErrTitle, action.result.feedback);
                        if (panel) {
                            this.pnlPermissions = new Toc.content.PermissionsPanel({content_id : this.sensors_id,content_type : 'sensor',owner : this.owner});
                            this.pnlDocuments =  new Toc.content.DocumentsPanel({content_id : this.sensors_id,content_type : 'sensor',owner : Toc.content.ContentManager});
                            this.tablayout.add(this.pnlDocuments);
                            this.tablayout.add(this.pnlPermissions);
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
            //Ext.Msg.alert(TocLanguage.msgErrTitle, "Aucun Sensor selectionné !!!");
            this.pnlGeneral.envelopetype.getStore().load();
            this.pnlGeneral.frfchannel_y.getStore().load();
            this.pnlGeneral.orbit_channel_y.getStore().load();
            this.pnlGeneral.sample_rev.getStore().load();
            this.pnlGeneral.average.getStore().load();
            this.pnlGeneral.overlap.getStore().load();
            this.pnlGeneral.window_fft.getStore().load();
            this.pnlGeneral.window_length_s.getStore().load();
            this.pnlGeneral.channel.getStore().load();
            this.pnlGeneral.cpmsslot.getStore().load();
            this.pnlGeneral.sampling_freq.getStore().load();
            this.pnlGeneral.record_length.getStore().load();
            this.pnlGeneral.engineering_unit.getStore().load();
        }
    },

    buildForm: function () {
        this.pnlGeneral = new Toc.SensorPanel({asset_id: this.asset_id, title: 'General'});

        this.tablayout = new Ext.TabPanel({
            activeTab: 0,
            defaults: {
                hideMode: 'offsets'
            },
            deferredRender: false,
            items: [this.pnlGeneral]
        });

        this.frmSensor = new Ext.form.FormPanel({
            layout: 'fit',
            fileUpload: true,
            labelWidth: 120,
            url: Toc.CONF.CONN_URL,
            baseParams: {
                module: 'categories',
                action: 'save_sensor'
            },
            scope: this,
            items: this.tablayout
        });

        return this.frmSensor;
    },

    submitForm: function () {
        this.frmSensor.form.submit({
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

Toc.DeleteSensor = function (sensors_id, caller) {
    Ext.MessageBox.confirm(
        TocLanguage.msgWarningTitle,
        "Voulez-vous vraiment supprimer ce Sensor ?",
        function (btn) {
            if (btn == 'yes') {
                Ext.Ajax.request({
                    url: Toc.CONF.CONN_URL,
                    params: {
                        module: 'categories',
                        action: 'delete_sensor',
                        sensors_id: sensors_id
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