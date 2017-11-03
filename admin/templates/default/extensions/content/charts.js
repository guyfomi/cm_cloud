Toc.DashboardPanel = function (config) {

    var that = this;
    var thisObj = this;

    config = config || {};
    config.region = 'center';
    config.title = 'Dashboard';
    config.started = false;
    config.layout = 'fit';
    config.loadMask = true;
    config.refresh = true;
    config.autoScroll = true;
    config.listeners = {
        activate: function (panel) {
            if(this.refresh)
            {
                that.combo_sensors1.getStore().load();
                that.combo_sensors2.getStore().load();
                this.refresh = false;
            }

            this.onRefresh();
        },
        show: function (panel) {
            //console.log('show');
            //this.buildItems(5000);
            //this.onRefresh();
        },
        render: function (panel) {
            //console.log('render');
            //this.buildItems(5000);
            //this.onRefresh();
        },
        enable: function (panel) {
            //console.log('enable');
            //this.onRefresh();
        },
        deactivate: function (panel) {
            //console.log('deactivate');
            this.onStop();
        },
        destroy: function (panel) {
            //console.log('destroy');
            this.onStop();
        },
        disable: function (panel) {
            //console.log('disable');
            this.onStop();
        },
        remove: function (container, panel) {
            //console.log('remove');
            this.onStop();
        },
        removed: function (container, panel) {
            //console.log('removed');
            this.onStop();
        },
        scope: this
    };

    config.combo_sensors1 = Toc.sensorsCombo({content_id: config.content_id, content_type: config.content_type});
    config.combo_sensors2 = Toc.sensorsCombo({content_id: config.content_id, content_type: config.content_type});
    //config.combo_freq = Toc.content.ContentManager.getFrequenceCombo();
    //config.combo_freq.enable();

    config.pbar = new Ext.ProgressBar({
        hidden: true,
        width: 150,
        hideLabel: true
    });

    config.tbar = [
        {
            text: '',
            iconCls: 'refresh',
            handler: this.onRefresh,
            scope: this
        },
        '->',
        config.pbar,
        '-',
        config.combo_sensors1,
        '-',
        config.combo_sensors2,
        '-',
        {
            //text: this.started ? 'Stop' : 'Start',
            text: '',
            disabled : true,
            iconCls: this.started ? 'stop' : 'play',
            handler: this.started ? this.onStop : this.onStart,
            scope: this
        }
    ];

    //config.combo_freq.getStore().load();
    config.combo_sensors1.getStore().on('beforeload', function (store, records, options) {
        thisObj.getEl().mask('Chargement Capteurs 1 ....');
    });

    config.combo_sensors1.getStore().on('load', function (store, records, options) {
        thisObj.onStop();
        //console.debug(records);
        if(records.length > 1)
        {
            thisObj.sensors_id1 = records[0].id;
            thisObj.sensors_id1_name = records[0].data.name;
            thisObj.combo_sensors1.setValue(thisObj.sensors_id1);
            //thisObj.freq = freq;
            if (thisObj.sensors_id1) {
                //thisObj.buildItems(5000);
            }
            else {
                //Ext.Msg.alert(TocLanguage.msgErrTitle, "Vous devez selectionner un Capteur !!!");
            }
        }
        else
        {
            thisObj.sensors_id1 = records[0].id;
            thisObj.sensors_id1_name = records[0].data.name;
            thisObj.combo_sensors1.setValue(thisObj.sensors_id1);
            //thisObj.freq = freq;
            if (thisObj.sensors_id1) {
                thisObj.buildItems(5000);
            }
            else {
                //Ext.Msg.alert(TocLanguage.msgErrTitle, "Vous devez selectionner un Capteur !!!");
            }
        }

        thisObj.getEl().unmask();
    });

    config.combo_sensors2.getStore().on('beforeload', function (store, records, options) {
        thisObj.getEl().mask('Chargement Capteurs 2 ....');
    });

    config.combo_sensors2.getStore().on('load', function (store, records, options) {
        thisObj.onStop();
        //console.debug(records);

        if(records.length > 1)
        {
            thisObj.sensors_id2 = records[0].id;
            thisObj.combo_sensors2.setValue(thisObj.sensors_id2);
            thisObj.sensors_id2_name = records[0].data.name;
            //thisObj.freq = freq;
            if (thisObj.sensors_id2) {
                //thisObj.buildItems(5000);
            }
            else {
                //Ext.Msg.alert(TocLanguage.msgErrTitle, "Vous devez selectionner un Capteur !!!");
            }
        }
        else
        {
            thisObj.sensors_id2 = records[0].id;
            thisObj.combo_sensors2.setValue(thisObj.sensors_id2);
            thisObj.sensors_id2_name = records[0].data.name;
            //thisObj.freq = freq;
            if (thisObj.sensors_id2) {
                thisObj.buildItems(5000);
            }
            else {
                //Ext.Msg.alert(TocLanguage.msgErrTitle, "Vous devez selectionner un Capteur !!!");
            }
        }

        thisObj.getEl().unmask();
    });

    config.combo_sensors1.on('select', function (combo, record, index) {
      thisObj.onStop();
        thisObj.sensors_id1 = record.id;
        thisObj.sensors_id1_name = record.data.name;
      thisObj.buildItems(5000);
    });

    config.combo_sensors2.on('select', function (combo, record, index) {
        thisObj.onStop();
        thisObj.sensors_id2 = record.id;
        thisObj.sensors_id2_name = record.data.name;
        thisObj.buildItems(5000);
    });

    Toc.DashboardPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.DashboardPanel, Ext.Panel, {

    onRefresh: function () {
        this.buildItems(this.freq);
    },

    buildItems: function (freq) {
        if (this.sensors_id1 && this.sensors_id2) {
            this.topToolbar.items.items[8].enable();
            this.onStop();

            if (this.items) {
                this.removeAll(true);
            }

            //this.getEl().mask('Chargement');

            var conf = {};
            conf.owner = this.owner;
            conf.mainPanel = this.mainPanel;
            conf.width = "100%";
            conf.action = "list_charts1";
            //conf.content_type = this.content_type;
            conf.content_type = "sensor";
            conf.content_name = this.content_name;
            conf.content_id = this.sensors_id1;
            conf.sensors_id1 = this.sensors_id1;
            conf.sensors_id2 = this.sensors_id2;
            conf.sensors_id1_name = this.sensors_id1_name;
            conf.sensors_id2_name = this.sensors_id2_name;
            conf.pbar = this.pbar;

            //var panel = new Toc.StockCharts(conf);
            var panel = new Toc.Charts(conf);

            this.add(panel);
            //panel.buildItems(db);
            this.doLayout();

            //this.onStart();
            //this.getEl().unmask();
            this.onStart();
        }
        else {
            //Ext.Msg.alert(TocLanguage.msgErrTitle, "Vous devez selectionner un Capteur !!!");
            this.topToolbar.items.items[8].disable();
        }
    },

    onStop: function () {
        var items = this.items.items;
        //console.debug(items);
        var i = 0;
        while (i < items.length) {
            var panel = items[i];
            //console.debug(panel);
            if (panel && panel.stop) {
                panel.stop();
            }
            i++;
        }

        this.started = false;
        this.topToolbar.items.items[8].setHandler(this.onStart, this);
        this.topToolbar.items.items[8].setIconClass('play');
    },

    onStart: function () {
        this.getEl().mask('Demarrage ....');
        //var freq = this.combo_freq.getValue();
        var freq = 10000;
        var items = this.items.items;
        //console.debug(items);
        var i = 0;
        while (i < items.length) {
            var panel = items[i];
            //console.debug(panel);
            if (panel && panel.start) {
                panel.freq = freq;
                panel.start();
            }
            i++;
        }

        this.started = true;
        this.topToolbar.items.items[8].setHandler(this.onStop, this);
        this.topToolbar.items.items[8].setIconClass('stop');
        this.getEl().unmask();
    }
});

Toc.Charts = function (config) {
    //console.debug(config);
    this.params = config;
    var that = this;
    config = config || {};
    config.region = 'center';
    config.width = this.params.width;
    config.count = 0;
    config.reqs = 0;
    config.header = true;
    //config.bodyStyle = 'height:150px';
    //config.height = '22%';
    config.layout = 'fit';
    config.title = config.label;

    var thisObj = this;

    config.listeners = {
        render: function (comp) {

            var configChart = function () {
                thisObj.getEl().mask('Initialisation Chart ....');

                thisObj.data = [];

                var chart = AmCharts.makeChart(thisObj.body.id, {
                    "type": "serial",
                    "theme": "light",
                    marginTop: 5,
                    listeners: [
                        {"event": "animationFinished", "method": thisObj.onAnimationFinished},
                        {"event": "buildStarted", "method": thisObj.onBuildStarted},
                        {"event": "dataUpdated", "method": thisObj.onDataUpdated},
                        {"event": "drawn", "method": thisObj.onDrawn},
                        {"event": "init", "method": thisObj.onInit},
                        {"event": "rendered", "method": thisObj.onRendered}
                    ],
                    "legend": {
                        //"useGraphSettings": true,
                        "markerType": "circle",
                        marginLeft: 5,
                        marginRight: 5,
                        spacing: 2,
                        valueWidth: 50,
                        "position": "bottom",
                        listeners: [
                            {"event": "clickLabel", "method": thisObj.onClickLabel},
                            {"event": "clickMarker", "method": thisObj.onclickMarker},
                            {"event": "hideItem", "method": thisObj.onhideItem},
                            {"event": "rollOutItem", "method": thisObj.onrollOutItem},
                            {"event": "clickGraph", "method": thisObj.onclickGraph},
                            {"event": "clickGraphItem", "method": thisObj.onclickGraphItem},
                            {"event": "rollOverItem", "method": thisObj.onrollOverItem},
                            {"event": "showItem", "method": thisObj.onshowItem},
                            {"event": "rollOverMarke", "method": thisObj.onrollOverMarke}
                        ]
                    },
                    "dataProvider": [],
                    "synchronizeGrid": true,
                    dataDateFormat: "YYYY-MM-DD HH:NN:SS",
                    "valueAxes": [
                        {
                            "id": "v_small",
                            "axisColor": "#FF6600",
                            "axisThickness": 2,
                            "axisAlpha": 1,
                            "position": "left"
                        },
                        {
                            "id": "v_medium",
                            "axisColor": "#FCD202",
                            "axisThickness": 2,
                            "axisAlpha": 1,
                            "position": "right"
                        },
                        {
                            "id": "v_big",
                            "axisColor": "#B0DE09",
                            "axisThickness": 2,
                            "gridAlpha": 0,
                            "offset": 50,
                            "axisAlpha": 1,
                            "position": "left"
                        }
                    ],
                    "graphs": [
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#f44242",
                            "bullet": "round",
                            bulletSize: 1,
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            balloonText : thisObj.sensors_id1_name + ' : ' + "[[value]]",
                            "title": "mp_acrms",
                            "titre": "mp_acrms",
                            hidden: true,
                            "valueField": thisObj.sensors_id1 + "_mp_acrms_value",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#f47a41",
                            "bullet": "round",
                            bulletSize: 1,
                            "bulletBorderThickness": 1,
                            balloonText : thisObj.sensors_id1_name + ' : ' + "[[value]]",
                            //"hideBulletsCount": 20,
                            hidden: true,
                            "title": "mp_lfrms",
                            "titre": "mp_lfrms",
                            "valueField": thisObj.sensors_id1 + "_mp_lfrms_value",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#f4ac41",
                            //"bullet": "triangleUp",
                            hidden: true,
                            bulletSize: 1,
                            "bullet": "round",
                            "bulletBorderThickness": 1,
                            balloonText : thisObj.sensors_id1_name + ' : ' + "[[value]]",
                            //"hideBulletsCount": 20,
                            "title": "mp_isorms",
                            "titre": "mp_isorms",
                            "valueField": thisObj.sensors_id1 + "_mp_isorms_value",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#f4e241",
                            "bullet": "round",
                            bulletSize: 1,
                            "bulletBorderThickness": 1,
                            balloonText : thisObj.sensors_id1_name + ' : ' + "[[value]]",
                            //"hideBulletsCount": 20,
                            hidden: true,
                            "title": "mp_hfrms",
                            "titre": "mp_hfrms",
                            "valueField": thisObj.sensors_id1 + "_mp_hfrms_value",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#bef441",
                            "bullet": "round",
                            bulletSize: 1,
                            "bulletBorderThickness": 1,
                            balloonText : thisObj.sensors_id1_name + ' : ' + "[[value]]",
                            //"hideBulletsCount": 20,
                            "title": "mp_acpeak",
                            "titre": "mp_acpeak",
                            hidden: true,
                            "valueField": thisObj.sensors_id1 + "_mp_acpeak_value",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_medium",
                            //"lineColor": "#55f441",
                            "bullet": "round",
                            bulletSize: 1,
                            "bulletBorderThickness": 1,
                            balloonText : thisObj.sensors_id1_name + ' : ' + "[[value]]",
                            //"hideBulletsCount": 20,
                            "title": "mp_accrest",
                            "titre": "mp_accrest",
                            "valueField": thisObj.sensors_id1 + "_mp_accrest_value",
                            hidden: true,
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#41f4a9",
                            "bullet": "round",
                            bulletSize: 1,
                            "bulletBorderThickness": 1,
                            balloonText : thisObj.sensors_id1_name + ' : ' + "[[value]]",
                            //"hideBulletsCount": 20,
                            "title": "mp_mean",
                            "titre": "mp_mean",
                            "valueField": thisObj.sensors_id1 + "_mp_mean_value",
                            hidden: false,
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#41dcf4",
                            "bullet": "round",
                            bulletSize: 1,
                            "bulletBorderThickness": 1,
                            balloonText : thisObj.sensors_id1_name + ' : ' + "[[value]]",
                            //"hideBulletsCount": 20,
                            "title": "mp_peak2peak",
                            "titre": "mp_peak2peak",
                            hidden: true,
                            "valueField": thisObj.sensors_id1 + "_mp_peak2peak_value",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#416df4",
                            "bullet": "round",
                            "bulletBorderThickness": 1,
                            balloonText : thisObj.sensors_id1_name + ' : ' + "[[value]]",
                            //"hideBulletsCount": 20,
                            bulletSize: 1,
                            "title": "mp_kurtosis",
                            "titre": "mp_kurtosis",
                            "valueField": thisObj.sensors_id1 + "_mp_kurtosis_value",
                            hidden: true,
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#9741f4",
                            "bullet": "round",
                            "bulletBorderThickness": 1,
                            balloonText : thisObj.sensors_id1_name + ' : ' + "[[value]]",
                            //"hideBulletsCount": 20,
                            "title": "mp_smax",
                            "titre": "mp_smax",
                            hidden: true,
                            bulletSize: 1,
                            "valueField": thisObj.sensors_id1 + "_mp_smax_value",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#332428",
                            "bullet": "round",
                            "bulletBorderThickness": 1,
                            balloonText : thisObj.sensors_id1_name + ' : ' + "[[value]]",
                            //"hideBulletsCount": 20,
                            hidden: true,
                            bulletSize: 1,
                            "title": "tp_lfrms",
                            "titre": "tp_lfrms",
                            "valueField": thisObj.sensors_id1 + "_lfrms",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#332428",
                            "bullet": "round",
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            hidden: true,
                            balloonText : thisObj.sensors_id1_name + ' : ' + "[[value]]",
                            bulletSize: 1,
                            "title": "tp_isorms",
                            "titre": "tp_isorms",
                            "valueField": thisObj.sensors_id1 + "_isorms",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#332428",
                            "bullet": "round",
                            balloonText : thisObj.sensors_id1_name + ' : ' + "[[value]]",
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            hidden: true,
                            bulletSize: 1,
                            "title": "tp_hfrms",
                            "titre": "tp_hfrms",
                            "valueField": thisObj.sensors_id1 + "_hfrms",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_medium",
                            //"lineColor": "#332428",
                            "bullet": "round",
                            balloonText : thisObj.sensors_id1_name + ' : ' + "[[value]]",
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            bulletSize: 1,
                            hidden: true,
                            "title": "tp_crest",
                            "titre": "tp_crest",
                            "valueField": thisObj.sensors_id1 + "_crest",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#332428",
                            "bullet": "round",
                            balloonText : thisObj.sensors_id1_name + ' : ' + "[[value]]",
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            hidden: true,
                            bulletSize: 1,
                            "title": "tp_peak",
                            "titre": "tp_peak",
                            "valueField": thisObj.sensors_id1 + "_peak",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#332428",
                            "bullet": "round",
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            hidden: true,
                            balloonText : thisObj.sensors_id1_name + ' : ' + "[[value]]",
                            bulletSize: 1,
                            "title": "tp_rms",
                            "titre": "tp_rms",
                            "valueField": thisObj.sensors_id1 + "_rms",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#332428",
                            "bullet": "round",
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            hidden: true,
                            balloonText : thisObj.sensors_id1_name + ' : ' + "[[value]]",
                            bulletSize: 1,
                            "title": "tp_max",
                            "titre": "tp_max",
                            "valueField": thisObj.sensors_id1 + "_max",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#332428",
                            "bullet": "round",
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            hidden: true,
                            balloonText : thisObj.sensors_id1_name + ' : ' + "[[value]]",
                            bulletSize: 1,
                            "title": "tp_min",
                            "titre": "tp_min",
                            "valueField": thisObj.sensors_id1 + "_min",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#332428",
                            "bullet": "round",
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            hidden: true,
                            balloonText : thisObj.sensors_id1_name + ' : ' + "[[value]]",
                            bulletSize: 1,
                            "title": "tp_std",
                            "titre": "tp_std",
                            "valueField": thisObj.sensors_id1 + "_std",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#332428",
                            "bullet": "round",
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            hidden: true,
                            balloonText : thisObj.sensors_id1_name + ' : ' + "[[value]]",
                            bulletSize: 1,
                            "title": "tp_peak2peak",
                            "titre": "tp_peak2peak",
                            "valueField": thisObj.sensors_id1 + "_peak2peak",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#332428",
                            "bullet": "round",
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            hidden: true,
                            balloonText : thisObj.sensors_id1_name + ' : ' + "[[value]]",
                            bulletSize: 1,
                            "title": "tp_mean",
                            "titre": "tp_mean",
                            "valueField": thisObj.sensors_id1 + "_mean",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#332428",
                            "bullet": "round",
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            hidden: true,
                            balloonText : thisObj.sensors_id1_name + ' : ' + "[[value]]",
                            bulletSize: 1,
                            "title": "tp_kurtosis",
                            "titre": "tp_kurtosis",
                            "valueField": thisObj.sensors_id1 + "_kurtosis",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#332428",
                            "bullet": "round",
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            hidden: true,
                            balloonText : thisObj.sensors_id1_name + ' : ' + "[[value]]",
                            bulletSize: 1,
                            "title": "tp_skewness",
                            "titre": "tp_skewness",
                            "valueField": thisObj.sensors_id1 + "_skewness",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#a56b7b",
                            "bullet": "round",
                            balloonText : thisObj.sensors_id1_name + ' : ' + "[[value]]",
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            bulletSize: 1,
                            hidden: true,
                            balloonText : thisObj.sensors_id1_name + ' : ' + "[[value]]",
                            "title": "tp_smax",
                            "titre": "tp_smax",
                            "valueField": thisObj.sensors_id1 + "_smax",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_big",
                            //"lineColor": "#f7b7c8",
                            //"bullet": "bubble",
                            hidden: true,
                            "bullet": "round",
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            "title": "tp_histo",
                            "titre": "tp_histo",
                            balloonText : thisObj.sensors_id1_name + ' : ' + "[[value]]",
                            bulletSize: 1,
                            "valueField": thisObj.sensors_id1 + "_histo",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#f9dee5",
                            //"bullet": "diamond",
                            hidden: true,
                            "bullet": "round",
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            bulletSize: 1,
                            balloonText : thisObj.sensors_id1_name + ' : ' + "[[value]]",
                            "title": "a1x",
                            "titre": "a1x",
                            "valueField": thisObj.sensors_id1 + "_a1x",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#B0DE09",
                            //"bullet": "round",
                            hidden: true,
                            "bullet": "round",
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            bulletSize: 1,
                            balloonText : thisObj.sensors_id1_name + ' : ' + "[[value]]",
                            "title": "p1x",
                            "titre": "p1x",
                            "valueField": thisObj.sensors_id1 + "_p1x",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#B0DE09",
                            //"bullet": "square",
                            "bullet": "round",
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            bulletSize: 1,
                            hidden: true,
                            balloonText : thisObj.sensors_id1_name + ' : ' + "[[value]]",
                            "title": "a2x",
                            "titre": "a2x",
                            "valueField": thisObj.sensors_id1 + "_a2x",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#B0DE09",
                            "bullet": "round",
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            bulletSize: 1,
                            hidden: true,
                            "title": "p2x",
                            balloonText : thisObj.sensors_id1_name + ' : ' + "[[value]]",
                            "titre": "p2x",
                            "valueField": thisObj.sensors_id1 + "_p2x",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#B0DE09",
                            "bullet": "round",
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            bulletSize: 1,
                            hidden: true,
                            "title": "a3x",
                            balloonText : thisObj.sensors_id1_name + ' : ' + "[[value]]",
                            "titre": "a3x",
                            "valueField": thisObj.sensors_id1 + "_a3x",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_big",
                            //"lineColor": "#B0DE09",
                            "bullet": "round",
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            bulletSize: 1,
                            hidden: true,
                            "title": "op1",
                            balloonText : thisObj.sensors_id1_name + ' : ' + "[[value]]",
                            "titre": "op1",
                            "valueField": thisObj.sensors_id1 + "_op1",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_big",
                            //"lineColor": "#B0DE09",
                            "bullet": "round",
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            bulletSize: 1,
                            hidden: true,
                            "title": "op2",
                            balloonText : thisObj.sensors_id1_name + ' : ' + "[[value]]",
                            "titre": "op2",
                            "valueField": thisObj.sensors_id1 + "_op2",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_big",
                            //"lineColor": "#B0DE09",
                            "bullet": "round",
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            bulletSize: 1,
                            balloonText : thisObj.sensors_id1_name + ' : ' + "[[value]]",
                            hidden: true,
                            "title": "op3",
                            "titre": "op3",
                            "valueField": thisObj.sensors_id1 + "_op3",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_big",
                            //"lineColor": "#B0DE09",
                            "bullet": "round",
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            bulletSize: 1,
                            hidden: true,
                            "title": "op4",
                            balloonText : thisObj.sensors_id1_name + ' : ' + "[[value]]",
                            "titre": "op4",
                            "valueField": thisObj.sensors_id1 + "_op4",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_big",
                            //"lineColor": "#B0DE09",
                            "bullet": "round",
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            bulletSize: 1,
                            hidden: true,
                            "title": "op5",
                            "titre": "op5",
                            balloonText : thisObj.sensors_id1_name + ' : ' + "[[value]]",
                            "valueField": thisObj.sensors_id1 + "_op5",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_big",
                            //"lineColor": "#B0DE09",
                            //"bullet": "triangleDown",
                            "bulletBorderThickness": 1,
                            "hideBulletsCount": 20,
                            bulletSize: 1,
                            hidden: true,
                            "title": "op6",
                            "titre": "op6",
                            balloonText : thisObj.sensors_id1_name + ' : ' + "[[value]]",
                            "valueField": thisObj.sensors_id1 + "_op6",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_big",
                            //"lineColor": "#B0DE09",
                            "bullet": "round",
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            bulletSize: 1,
                            hidden: true,
                            "title": "op7",
                            balloonText : thisObj.sensors_id1_name + ' : ' + "[[value]]",
                            "titre": "op7",
                            "valueField": thisObj.sensors_id1 + "_op7",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_big",
                            //"lineColor": "#B0DE09",
                            "bullet": "round",
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            bulletSize: 1,
                            hidden: true,
                            "title": "op8",
                            "titre": "op8",
                            balloonText : thisObj.sensors_id1_name + ' : ' + "[[value]]",
                            "valueField": thisObj.sensors_id1 + "_op8",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_big",
                            //"lineColor": "#B0DE09",
                            "bullet": "round",
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            bulletSize: 1,
                            balloonText : thisObj.sensors_id1_name + ' : ' + "[[value]]",
                            hidden: true,
                            "title": "op9",
                            "titre": "op9",
                            "valueField": thisObj.sensors_id1 + "_op9",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_big",
                            //"lineColor": "#B0DE09",
                            "bullet": "round",
                            bulletSize: 1,
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            hidden: true,
                            balloonText : thisObj.sensors_id1_name + ' : ' + "[[value]]",
                            "title": "op10",
                            "titre": "op10",
                            "valueField": thisObj.sensors_id1 + "_op10",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#f44242",
                            "bullet": "round",
                            bulletSize: 1,
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            visibleInLegend: false,
                            "titre": "mp_acrms",
                            balloonText : thisObj.sensors_id2_name + ' : ' + "[[value]]",
                            "title": thisObj.sensors_id2 + "_mp_acrms",
                            hidden: true,
                            "valueField": thisObj.sensors_id2 + "_mp_acrms_value",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#f47a41",
                            "bullet": "round",
                            bulletSize: 1,
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            balloonText : thisObj.sensors_id2_name + ' : ' + "[[value]]",
                            visibleInLegend: false,
                            hidden: true,
                            "titre": "mp_lfrms",
                            "title": thisObj.sensors_id2 + "_mp_lfrms",
                            "valueField": thisObj.sensors_id2 + "_mp_lfrms_value",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#f4ac41",
                            //"bullet": "triangleUp",
                            hidden: true,
                            bulletSize: 1,
                            balloonText : thisObj.sensors_id2_name + ' : ' + "[[value]]",
                            "bullet": "round",
                            visibleInLegend: false,
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            "titre": "mp_isorms",
                            "title": thisObj.sensors_id2 + "_mp_isorms",
                            "valueField": thisObj.sensors_id2 + "_mp_isorms_value",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#f4e241",
                            "bullet": "round",
                            bulletSize: 1,
                            "bulletBorderThickness": 1,
                            balloonText : thisObj.sensors_id2_name + ' : ' + "[[value]]",
                            //"hideBulletsCount": 20,
                            hidden: true,
                            visibleInLegend: false,
                            "titre": "mp_hfrms",
                            "title": thisObj.sensors_id2 + "_mp_hfrms",
                            "valueField": thisObj.sensors_id2 + "_mp_hfrms_value",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#bef441",
                            "bullet": "round",
                            bulletSize: 1,
                            balloonText : thisObj.sensors_id2_name + ' : ' + "[[value]]",
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            visibleInLegend: false,
                            "titre": "mp_acpeak",
                            "title": thisObj.sensors_id2 + "_mp_acpeak",
                            hidden: true,
                            "valueField": thisObj.sensors_id2 + "_mp_acpeak_value",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_medium",
                            //"lineColor": "#55f441",
                            "bullet": "round",
                            balloonText : thisObj.sensors_id2_name + ' : ' + "[[value]]",
                            bulletSize: 1,
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            "titre": "mp_accrest",
                            visibleInLegend: false,
                            "title": thisObj.sensors_id2 + "_mp_accrest",
                            "valueField": thisObj.sensors_id2 + "_mp_accrest_value",
                            hidden: true,
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#41f4a9",
                            "bullet": "round",
                            visibleInLegend: false,
                            bulletSize: 1,
                            balloonText : thisObj.sensors_id2_name + ' : ' + "[[value]]",
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            "titre": "mp_mean",
                            "title": thisObj.sensors_id2 + "_mp_mean",
                            "valueField": thisObj.sensors_id2 + "_mp_mean_value",
                            hidden: false,
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#41dcf4",
                            "bullet": "round",
                            visibleInLegend: false,
                            bulletSize: 1,
                            balloonText : thisObj.sensors_id2_name + ' : ' + "[[value]]",
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            "titre": "mp_peak2peak",
                            "title": thisObj.sensors_id2 + "_mp_peak2peak",
                            hidden: true,
                            "valueField": thisObj.sensors_id2 + "_mp_peak2peak_value",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#416df4",
                            "bullet": "round",
                            visibleInLegend: false,
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            balloonText : thisObj.sensors_id2_name + ' : ' + "[[value]]",
                            bulletSize: 1,
                            "titre": "mp_kurtosis",
                            "title": thisObj.sensors_id2 + "_mp_kurtosis",
                            "valueField": thisObj.sensors_id2 + "_mp_kurtosis_value",
                            hidden: true,
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#9741f4",
                            "bullet": "round",
                            visibleInLegend: false,
                            "bulletBorderThickness": 1,
                            balloonText : thisObj.sensors_id2_name + ' : ' + "[[value]]",
                            //"hideBulletsCount": 20,
                            "titre": "mp_smax",
                            "title": thisObj.sensors_id2 + "_mp_smax",
                            hidden: true,
                            bulletSize: 1,
                            "valueField": thisObj.sensors_id2 + "_mp_smax_value",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#332428",
                            "bullet": "round",
                            visibleInLegend: false,
                            balloonText : thisObj.sensors_id2_name + ' : ' + "[[value]]",
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            hidden: true,
                            bulletSize: 1,
                            "titre": "tp_lfrms",
                            "title": thisObj.sensors_id2 + "_tp_lfrms",
                            "valueField": thisObj.sensors_id2 + "_lfrms",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#332428",
                            "bullet": "round",
                            visibleInLegend: false,
                            "bulletBorderThickness": 1,
                            balloonText : thisObj.sensors_id2_name + ' : ' + "[[value]]",
                            //"hideBulletsCount": 20,
                            hidden: true,
                            bulletSize: 1,
                            "titre": "tp_isorms",
                            "title": thisObj.sensors_id2 + "_tp_isorms",
                            "valueField": thisObj.sensors_id2 + "_isorms",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#332428",
                            "bullet": "round",
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            hidden: true,
                            visibleInLegend: false,
                            balloonText : thisObj.sensors_id2_name + ' : ' + "[[value]]",
                            bulletSize: 1,
                            "titre": "tp_hfrms",
                            "title": thisObj.sensors_id2 + "_tp_hfrms",
                            "valueField": thisObj.sensors_id2 + "_hfrms",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_medium",
                            //"lineColor": "#332428",
                            "bullet": "round",
                            visibleInLegend: false,
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            bulletSize: 1,
                            balloonText : thisObj.sensors_id2_name + ' : ' + "[[value]]",
                            hidden: true,
                            "titre": "tp_crest",
                            "title": thisObj.sensors_id2 + "_tp_crest",
                            "valueField": thisObj.sensors_id2 + "_crest",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#332428",
                            "bullet": "round",
                            visibleInLegend: false,
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            hidden: true,
                            balloonText : thisObj.sensors_id2_name + ' : ' + "[[value]]",
                            bulletSize: 1,
                            "titre": "tp_peak",
                            "title": thisObj.sensors_id2 + "_tp_peak",
                            "valueField": thisObj.sensors_id12 + "_peak",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#332428",
                            "bullet": "round",
                            visibleInLegend: false,
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            balloonText : thisObj.sensors_id2_name + ' : ' + "[[value]]",
                            hidden: true,
                            bulletSize: 1,
                            "titre": "tp_rms",
                            "title": thisObj.sensors_id2 + "_tp_rms",
                            "valueField": thisObj.sensors_id2 + "_rms",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#332428",
                            "bullet": "round",
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            visibleInLegend: false,
                            hidden: true,
                            bulletSize: 1,
                            "titre": "tp_max",
                            balloonText : thisObj.sensors_id2_name + ' : ' + "[[value]]",
                            "title": thisObj.sensors_id2 + "_tp_max",
                            "valueField": thisObj.sensors_id2 + "_max",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#332428",
                            "bullet": "round",
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            hidden: true,
                            bulletSize: 1,
                            visibleInLegend: false,
                            "titre": "tp_min",
                            "title": thisObj.sensors_id2 + "_tp_min",
                            "valueField": thisObj.sensors_id2 + "_min",
                            balloonText : thisObj.sensors_id2_name + ' : ' + "[[value]]",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#332428",
                            "bullet": "round",
                            visibleInLegend: false,
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            hidden: true,
                            bulletSize: 1,
                            "titre": "tp_std",
                            "title": thisObj.sensors_id2 + "_tp_std",
                            balloonText : thisObj.sensors_id2_name + ' : ' + "[[value]]",
                            "valueField": thisObj.sensors_id2 + "_std",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#332428",
                            "bullet": "round",
                            visibleInLegend: false,
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            hidden: true,
                            bulletSize: 1,
                            "titre": "tp_peak2peak",
                            "title": thisObj.sensors_id2 + "_tp_peak2peak",
                            balloonText : thisObj.sensors_id2_name + ' : ' + "[[value]]",
                            "valueField": thisObj.sensors_id2 + "_peak2peak",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#332428",
                            "bullet": "round",
                            visibleInLegend: false,
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            hidden: true,
                            bulletSize: 1,
                            "titre": "tp_mean",
                            "title": thisObj.sensors_id2 + "_tp_mean",
                            balloonText : thisObj.sensors_id2_name + ' : ' + "[[value]]",
                            "valueField": thisObj.sensors_id2 + "_mean",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#332428",
                            "bullet": "round",
                            visibleInLegend: false,
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            hidden: true,
                            bulletSize: 1,
                            "titre": "tp_kurtosis",
                            "title": thisObj.sensors_id2 + "_tp_kurtosis",
                            balloonText : thisObj.sensors_id2_name + ' : ' + "[[value]]",
                            "valueField": thisObj.sensors_id2 + "_kurtosis",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#332428",
                            "bullet": "round",
                            visibleInLegend: false,
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            hidden: true,
                            bulletSize: 1,
                            "titre": "tp_skewness",
                            "title": thisObj.sensors_id2 + "_tp_skewness",
                            balloonText : thisObj.sensors_id2_name + ' : ' + "[[value]]",
                            "valueField": thisObj.sensors_id2 + "_skewness",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#a56b7b",
                            "bullet": "round",
                            visibleInLegend: false,
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            bulletSize: 1,
                            hidden: true,
                            balloonText : thisObj.sensors_id2_name + ' : ' + "[[value]]",
                            "titre": "tp_smax",
                            "title": thisObj.sensors_id2 + "_tp_smax",
                            "valueField": thisObj.sensors_id2 + "_smax",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_big",
                            //"lineColor": "#f7b7c8",
                            //"bullet": "bubble",
                            hidden: true,
                            visibleInLegend: false,
                            "bullet": "round",
                            "bulletBorderThickness": 1,
                            balloonText : thisObj.sensors_id2_name + ' : ' + "[[value]]",
                            //"hideBulletsCount": 20,
                            "titre": "tp_histo",
                            "title": thisObj.sensors_id2 + "_tp_histo",
                            bulletSize: 1,
                            "valueField": thisObj.sensors_id2 + "_histo",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#f9dee5",
                            //"bullet": "diamond",
                            hidden: true,
                            visibleInLegend: false,
                            balloonText : thisObj.sensors_id2_name + ' : ' + "[[value]]",
                            "bullet": "round",
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            bulletSize: 1,
                            "titre": "a1x",
                            "title": thisObj.sensors_id2 + "_a1x",
                            "valueField": thisObj.sensors_id2 + "_a1x",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#B0DE09",
                            //"bullet": "round",
                            hidden: true,
                            visibleInLegend: false,
                            balloonText : thisObj.sensors_id2_name + ' : ' + "[[value]]",
                            "bullet": "round",
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            bulletSize: 1,
                            "titre": "p1x",
                            "title": thisObj.sensors_id2 + "_p1x",
                            "valueField": thisObj.sensors_id2 + "_p1x",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#B0DE09",
                            //"bullet": "square",
                            "bullet": "round",
                            "bulletBorderThickness": 1,
                            balloonText : thisObj.sensors_id2_name + ' : ' + "[[value]]",
                            //"hideBulletsCount": 20,
                            bulletSize: 1,
                            visibleInLegend: false,
                            hidden: true,
                            "titre": "a2x",
                            "title": thisObj.sensors_id2 + "_a2x",
                            "valueField": thisObj.sensors_id2 + "_a2x",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#B0DE09",
                            "bullet": "round",
                            "bulletBorderThickness": 1,
                            balloonText : thisObj.sensors_id2_name + ' : ' + "[[value]]",
                            //"hideBulletsCount": 20,
                            bulletSize: 1,
                            hidden: true,
                            visibleInLegend: false,
                            "titre": "p2x",
                            "title": thisObj.sensors_id2 + "_p2x",
                            "valueField": thisObj.sensors_id2 + "_p2x",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_small",
                            //"lineColor": "#B0DE09",
                            "bullet": "round",
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            balloonText : thisObj.sensors_id2_name + ' : ' + "[[value]]",
                            bulletSize: 1,
                            visibleInLegend: false,
                            hidden: true,
                            "titre": "a3x",
                            "title": thisObj.sensors_id2 + "_a3x",
                            "valueField": thisObj.sensors_id2 + "_a3x",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_big",
                            //"lineColor": "#B0DE09",
                            "bullet": "round",
                            balloonText : thisObj.sensors_id2_name + ' : ' + "[[value]]",
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            bulletSize: 1,
                            visibleInLegend: false,
                            hidden: true,
                            "titre": "op1",
                            "title": thisObj.sensors_id2 + "_op1",
                            "valueField": thisObj.sensors_id2 + "_op1",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_big",
                            //"lineColor": "#B0DE09",
                            "bullet": "round",
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            balloonText : thisObj.sensors_id2_name + ' : ' + "[[value]]",
                            bulletSize: 1,
                            hidden: true,
                            visibleInLegend: false,
                            "titre": "op2",
                            "title": thisObj.sensors_id2 + "_op2",
                            "valueField": thisObj.sensors_id2 + "_op2",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_big",
                            //"lineColor": "#B0DE09",
                            "bullet": "round",
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            balloonText : thisObj.sensors_id2_name + ' : ' + "[[value]]",
                            bulletSize: 1,
                            hidden: true,
                            visibleInLegend: false,
                            "titre": "op3",
                            "title": thisObj.sensors_id2 + "_op3",
                            "valueField": thisObj.sensors_id2 + "_op3",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_big",
                            //"lineColor": "#B0DE09",
                            "bullet": "round",
                            "bulletBorderThickness": 1,
                            balloonText : thisObj.sensors_id2_name + ' : ' + "[[value]]",
                            //"hideBulletsCount": 20,
                            bulletSize: 1,
                            hidden: true,
                            visibleInLegend: false,
                            "titre": "op4",
                            "title": thisObj.sensors_id2 + "_op4",
                            "valueField": thisObj.sensors_id2 + "_op4",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_big",
                            //"lineColor": "#B0DE09",
                            "bullet": "round",
                            balloonText : thisObj.sensors_id2_name + ' : ' + "[[value]]",
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            bulletSize: 1,
                            hidden: true,
                            visibleInLegend: false,
                            "titre": "op5",
                            "title": thisObj.sensors_id2 + "_op5",
                            "valueField": thisObj.sensors_id2 + "_op5",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_big",
                            //"lineColor": "#B0DE09",
                            //"bullet": "triangleDown",
                            "bulletBorderThickness": 1,
                            "hideBulletsCount": 20,
                            bulletSize: 1,
                            balloonText : thisObj.sensors_id2_name + ' : ' + "[[value]]",
                            visibleInLegend: false,
                            hidden: true,
                            "titre": "op6",
                            "title": thisObj.sensors_id2 + "_op6",
                            "valueField": thisObj.sensors_id2 + "_op6",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_big",
                            //"lineColor": "#B0DE09",
                            "bullet": "round",
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            bulletSize: 1,
                            visibleInLegend: false,
                            balloonText : thisObj.sensors_id2_name + ' : ' + "[[value]]",
                            hidden: true,
                            "titre": "op7",
                            "title": thisObj.sensors_id2 + "_op7",
                            "valueField": thisObj.sensors_id2 + "_op7",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_big",
                            //"lineColor": "#B0DE09",
                            "bullet": "round",
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            bulletSize: 1,
                            balloonText : thisObj.sensors_id2_name + ' : ' + "[[value]]",
                            visibleInLegend: false,
                            hidden: true,
                            "titre": "op8",
                            "title": thisObj.sensors_id2 + "_op8",
                            "valueField": thisObj.sensors_id2 + "_op8",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_big",
                            //"lineColor": "#B0DE09",
                            "bullet": "round",
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            bulletSize: 1,
                            balloonText : thisObj.sensors_id2_name + ' : ' + "[[value]]",
                            visibleInLegend: false,
                            hidden: true,
                            "titre": "op9",
                            "title": thisObj.sensors_id2 + "_op9",
                            "valueField": thisObj.sensors_id2 + "_op9",
                            "fillAlphas": 0
                        },
                        {
                            "valueAxis": "v_big",
                            //"lineColor": "#B0DE09",
                            "bullet": "round",
                            balloonText : thisObj.sensors_id2_name + ' : ' + "[[value]]",
                            bulletSize: 1,
                            "bulletBorderThickness": 1,
                            //"hideBulletsCount": 20,
                            hidden: true,
                            visibleInLegend: false,
                            "titre": "op10",
                            "title": thisObj.sensors_id2 + "_op10",
                            "valueField": thisObj.sensors_id2 + "_op10",
                            "fillAlphas": 0
                        }
                    ],
                    "chartScrollbar": {},
                    "chartCursor": {
                        "cursorPosition": "mouse",
                        scrollbarHeight: 10
                    },
                    "categoryField": "date",
                    "categoryAxis": {
                        "parseDates": true,
                        minPeriod: "mm",
                        "axisColor": "#DADADA",
                        "minorGridEnabled": true
                    },
                    "export": {
                        "enabled": false,
                        "position": "bottom-right"
                    }
                });

                chart.eventdate = "01/01/2000";

                thisObj.chart = chart;

                chart.validateData();

                thisObj.getEl().unmask();
            };
            if (AmCharts.isReady) {
                configChart();
            } else {
                AmCharts.ready(configChart);
            }
        },
        resize: function (Component, adjWidth, adjHeight, rawWidth, rawHeight) {
            Component.body.dom.style.height = config.body_height;
        },
        scope: this
    };

    config.tools = [
    ];

    Toc.Charts.superclass.constructor.call(this, config);
};

Ext.extend(Toc.Charts, Ext.Panel, {
    onClickLabel: function (dataItem) {
        //console.log('onClickLabel');
        //console.debug(dataItem);
    },

    onclickMarker: function (dataItem) {
        //console.log('onclickMarker');
        //console.debug(dataItem);
    },

    onhideItem: function (dataItem) {
        //console.log('onhideItem');
        //console.debug(dataItem);
//        this.pbar.reset();
//        this.pbar.updateProgress(0,"",true);
//        this.pbar.val = 0;
//        this.pbar.count = 0;
//        this.pbar.show();
//        var count = dataItem.chart.graphs.length - 1;
//        var step = 1/count;

        var i = 0;

        while(i < dataItem.chart.graphs.length)
        {
            var graph = dataItem.chart.graphs[i];
            if(dataItem.dataItem.titre == graph.titre)
            {
                //console.log('graph found ... ' + i);
                //console.debug(graph);
                dataItem.chart.hideGraph(graph);

//                this.pbar.val = this.pBar.val + step;
//                this.pbar.count = pbar.count + 1;
//                this.pbar.updateProgress(this.pBar.val,'',true);
//
//                if(this.pbar.count >= count)
//                {
//                    this.pbar.reset();
//                    this.pbar.hide();
//                }
            }
            i++;
        }
    },

    onrollOutItem: function (dataItem) {
        //console.log('onrollOutItem');
        //console.debug(dataItem);
    },

    onclickGraph: function (graph, chart, event) {
        console.log('onclickGraph');
        console.log('graph');
        console.debug(graph);
    },

    onclickGraphItem: function (graph, item, index, chart, event) {
        console.log('onclickGraphItem');
        console.log('graph');
        console.debug(graph);
        console.log('item');
        console.debug(item);
        console.log('index');
        console.debug(index);
    },

    onrollOverItem: function (dataItem) {
        //console.log('onrollOverItem');
        //console.debug(dataItem);
    },

    onshowItem: function (dataItem) {
        //console.log('onshowItem');
        //console.debug(dataItem);

//        this.pbar.reset();
//        this.pbar.updateProgress(0,"",true);
//        this.pbar.val = 0;
//        this.pbar.count = 0;
//        this.pbar.show();
//        var count = dataItem.chart.graphs.length - 1;
//        var step = 1/count;

        var i = 0;

        while(i < dataItem.chart.graphs.length)
        {
            var graph = dataItem.chart.graphs[i];
            if(dataItem.dataItem.titre == graph.titre)
            {
                //console.log('graph found ... ' + i);
                //console.debug(graph);
                dataItem.chart.showGraph(graph);

//                this.pbar.val = this.pBar.val + step;
//                this.pbar.count = pbar.count + 1;
//                this.pbar.updateProgress(this.pBar.val,'',true);
//
//                if(this.pbar.count >= count)
//                {
//                    this.pbar.reset();
//                    this.pbar.hide();
//                }
            }
            i++;
        }
    },

    onrollOverMarke: function (dataItem) {
        //console.log('onrollOverMarke');
        //console.debug(dataItem);
    },

    onAnimationFinished: function (chart) {
        //console.log('onAnimationFinished');
        //console.debug(chart);
    },

    onBuildStarted: function (chart) {
        //console.log('onBuildStarted');
        //console.debug(chart);
    },

    onDataUpdated: function (chart) {
        //console.log('onDataUpdated');
        //console.debug(chart);
    },

    onDrawn: function (chart) {
        //console.log('onDrawn');
        //console.debug(chart);
    },

    onInit: function (chart) {
        //console.log('onInit');
        //console.debug(chart);
    },

    onRendered: function (chart) {
        //console.log('onRendered');
        //console.debug(chart);
    },

    refreshData: function (scope) {
        var chart = this.chart;
        if (scope && scope.started) {
            if (scope.reqs == 0) {
                //console.log('reqs++');
                scope.reqs++;

                if (scope.count == 0) {
                    this.getEl().mask('Chargement donnees');
                }

                Ext.Ajax.request({
                    url: Toc.CONF.CONN_URL,
                    params: {
                        module: 'categories',
                        action: this.action,
                        eventdate: chart.eventdate,
                        sensors_id1: this.sensors_id1,
                        sensors_id2: this.sensors_id2,
                        content_type: this.content_type,
                        content_name: this.content_name,
                        content_id: this.content_id,
                        start_date: this.mainPanel['start_date'],
                        end_date : this.mainPanel['end_date'],
                        measurement_state : this.mainPanel['measurement_state'],
                        event_trigger_type : this.mainPanel['event_trigger_type'],
                        monitoring_status : this.mainPanel['monitoring_status'],
                        operating_class : this.mainPanel['operating_class'],
                        xmlfile : this.mainPanel['xmlfile']
                    },
                    callback: function (options, success, response) {
                        //console.log('reqs--');
                        scope.reqs--;

                        var valueAxis = chart.valueAxes[0];
                        var index = 0;

                        if (success) {
                            var json = Ext.decode(response.responseText);
                            chart.eventdate = json.eventdate;

                            for (var i = 0; i < json.records.length; i++) {
                                //chart.dataProvider.push(record);
                                chart.dataProvider.push(json.records[i]);
                            }

                            if(json.records.length > 0)
                            {
                                chart.validateData();
                            }

                            if (valueAxis) {
                                valueAxis.titleColor = "blue";
                                valueAxis.title = "";
                            }
                        }
                        else {
                            if (valueAxis) {
                                valueAxis.titleColor = "red";
                                valueAxis.title = "Timeout";
                            }
                        }

                        if (scope.count == 0) {
                            this.getEl().unmask();
                        }

                        if (scope.count == 0) {
                            var interval = setInterval(function () {
                                scope.refreshData(scope);
                            }, scope.freq || 5000);
                            //setTimeout(that.refreshData, that.freq || 10000);
                            scope.count++;
                            scope.interval = interval;
                        }
                        else {
                            //console.log('that.count .... ' + scope.count);
                        }
                    },
                    scope: this
                });
            }
        }

        //chart.validateData();
    },

    onRefresh: function () {
        this.getStore().reload();
    },

    start: function () {
        //console.log('starting ...');
        this.count = 0;
        this.started = true;
        this.refreshData(this);
        //console.log('started ...')
    },

    stop: function () {
        //console.log('stopping ...');
        this.started = false;
        this.count = 10;
        this.refreshData(this);

        if (this.interval) {
            clearInterval(this.interval);
        }
        else {
            //Ext.MessageBox.alert(TocLanguage.msgErrTitle,"No job defined !!!");
        }

        //console.log('stopped ...');
    }
});

Toc.Map = function (config) {
    //console.debug(config);
    this.params = config;
    var that = this;
    config = config || {};
    config.region = 'center';
    config.width = "100%";
    config.started = false;
    //config.bodyStyle = 'height:150px';
    //config.height = '22%';
    config.layout = 'fit';

    var thisObj = this;

    config.listeners = {
        activate: function (comp) {
            console.log('activate');

            comp.data = [];

            var configChart = function () {

                var map;

                map = AmCharts.makeChart(thisObj.body.id, {
                    "type": "map",
                    "theme": "light",
                    //"getAreasFromMap": true,
                    showObjectsAfterZoom: true,
                    "projection": "miller",

                    "imagesSettings": {
                        "rollOverColor": "#089282",
                        "rollOverScale": 3,
                        "selectedScale": 3,
                        "selectedColor": "#089282",
                        "color": "#13564e"
                    },

                    "areasSettings": {
                        "unlistedAreasColor": "#15A892"
                    },

                    "dataProvider": {
                        "map": "worldLow",
                        bringForwardOnHover: true,
                        "images": comp.data
                    }
                });

                comp.map = map;

                map.dataGenerated = true;
                map.validateNow();

                comp.start();
            };
            if (AmCharts.isReady) {
                configChart();
            } else {
                AmCharts.ready(configChart);
            }
        },
        show: function (comp) {
            console.log('show');
        },
        enable: function (panel) {
            console.log('enable');
            //this.onRefresh();
        },
        deactivate: function (panel) {
            console.log('deactivate');
            panel.stop();
        },
        destroy: function (panel) {
            console.log('destroy');
            panel.stop();
        },
        disable: function (panel) {
            console.log('disable');
            panel.stop();
        },
        remove: function (container, panel) {
            console.log('remove');
            panel.stop();
        },
        removed: function (container, panel) {
            console.log('removed');
            panel.stop();
        },
        render: function (comp) {
            console.log('render');
        },
        resize: function (Component, adjWidth, adjHeight, rawWidth, rawHeight) {
            Component.body.dom.style.height = config.body_height;
        },
        scope: this
    };

    config.tbar = [
        {
            //text: this.started ? 'Stop' : 'Start',
            text: '',
            iconCls: this.started ? 'stop' : 'play',
            handler: this.started ? this.stop : this.start,
            scope: this
        }
    ];

    Toc.Map.superclass.constructor.call(this, config);
};

Ext.extend(Toc.Map, Ext.Panel, {
    refreshData: function (scope) {
        var map = scope.map;
        scope.data = [];

        if (scope && scope.started) {
            if (scope.reqs == 0) {
                console.log('reqs++');
                scope.reqs++;

                Ext.Ajax.request({
                    url: Toc.CONF.CONN_URL,
                    params: {
                        module: 'categories',
                        action: 'list_map',
                        customers_id: this.customers_id
                    },
                    callback: function (options, success, response) {
                        console.log('reqs--');
                        scope.reqs--;

                        if (success) {
                            var json = Ext.decode(response.responseText);

                            for (var i = 0; i < json.records.length; i++) {
                                var record = {
                                    "zoomLevel": 5,
                                    "scale": 0.5,
                                    imageURL: json.records[i].url,
                                    "title": json.records[i].title,
                                    "latitude": json.records[i].latitude,
                                    "longitude": json.records[i].longitude
                                };

                                scope.data.push(record);

                                map.dataProvider.images = scope.data;
                                map.dataGenerated = true;
                                map.validateNow();
                            }
                        }
                        else {
                        }

                        if (scope.count == 0) {
                            var interval = setInterval(function () {
                                scope.refreshData(scope)
                            }, scope.freq || 10000);
                            //setTimeout(that.refreshData, that.freq || 10000);
                            scope.count++;
                            this.interval = interval;
                        }
                        else {
                            console.log('that.count .... ' + scope.count);
                        }
                    },
                    scope: this
                });
            }
        }
    },

    start: function () {
        console.log('starting ...');
        this.count = 0;
        this.reqs = 0;
        this.started = true;
        this.refreshData(this);
        console.log('started ...');

        console.debug(this);
        this.topToolbar.items.items[0].setHandler(this.stop, this);
        this.topToolbar.items.items[0].setIconClass('stop');
    },

    stop: function () {
        console.log('stopping ...');
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

        console.log('stopped ...');
        console.debug(this);

        this.topToolbar.items.items[0].setHandler(this.start, this);
        this.topToolbar.items.items[0].setIconClass('play');
    }
});

Toc.PieChart = function (config) {
    //console.debug(config);
    this.params = config;
    var that = this;
    config = config || {};
    //config.region = 'center';
    //config.width = '100%';
    config.count = 0;
    config.reqs = 0;
    config.header = true;
    //config.autoHeight = true;
    //config.autoWidth = true;
    config.bodyStyle = 'height:200px';
    //config.height = '200px';
    //config.layout = 'fit';
    //config.title = config.label;

    var thisObj = this;

    config.listeners = {
        activate: function (panel) {
            console.log('PieChart activate');
        },
        show: function (panel) {
            console.log('PieChart show');

            //this.onStart();
            //this.onRefresh();
        },
        enable: function (panel) {
            console.log('PieChart enable');
            //this.onRefresh();
        },
        deactivate: function (panel) {
            console.log('PieChart deactivate');
            this.onStop();
        },
        destroy: function (panel) {
            console.log('PieChart destroy');
            this.onStop();
        },
        disable: function (panel) {
            console.log('PieChart disable');
            this.onStop();
        },
        remove: function (container, panel) {
            console.log('PieChart remove');
            this.onStop();
        },
        removed: function (container, panel) {
            console.log('PieChart removed');
            this.onStop();
        },
        hide: function (container, panel) {
            console.log('PieChart hide');
            this.onStop();
        },
        render: function (comp) {
            console.log('render pie charts ...');

            var configChart = function () {
                thisObj.data = [];

                thisObj.chart = AmCharts.makeChart(thisObj.body.id, {
                    "type": "pie",
                    "theme": "none",
                    labelsEnabled : false,
                    "dataProvider": [],
                    "valueField": "nbre",
                    colorField : 'color',
                    "titleField": "status",
                    "balloon": {
                        "fixedPosition": true
                    },
                    "export": {
                        "enabled": false
                    }
                });

                thisObj.chart.validateData();

                thisObj.onStart();
            };

            if (AmCharts.isReady) {
                configChart();
            } else {
                AmCharts.ready(configChart);
            }
        },
        resize: function (Component, adjWidth, adjHeight, rawWidth, rawHeight) {
            //Component.body.dom.style.height = config.body_height;
        },
        scope: this
    };

    config.tools = [
    ];

    Toc.PieChart.superclass.constructor.call(this, config);
};

Ext.extend(Toc.PieChart, Ext.Panel, {
    refreshData: function (scope) {
        var chart = this.chart;
        if (scope && scope.started) {
            if (scope.reqs == 0) {
                //console.log('reqs++');
                console.log('refreshData ...');
                console.debug(scope);
                scope.reqs++;
                //this.getEl().mask('loading pie ....');

                Ext.Ajax.request({
                    url: Toc.CONF.CONN_URL,
                    params: {
                        module: 'categories',
                        action: this.action,
                        content_type: this.content_type,
                        customers_id: this.customers_id
                    },
                    callback: function (options, success, response) {
                        //console.log('reqs--');
                        //this.getEl().unmask();
                        scope.reqs--;

                        if (success) {
                            var json = Ext.decode(response.responseText);

                            chart.dataProvider = [];

                            if(chart)
                            {
                                for (var i = 0; i < json.records.length; i++) {
                                    chart.dataProvider.push(json.records[i]);
                                }

                                if(json.records.length > 0)
                                {
                                    chart.validateData();
                                }
                            }
                            else {
                                Ext.Msg.alert(TocLanguage.msgErrTitle, "Aucun chart defini !!!");
                            }
                        }
                        else {
                            Ext.Msg.alert(TocLanguage.msgErrTitle, "Erreur chargement Stats !!!");
                        }

                        if (scope.count == 0) {
                            var interval = setInterval(function () {
                                scope.refreshData(scope)
                            }, scope.freq || 15000);
                            //setTimeout(that.refreshData, that.freq || 10000);
                            scope.count++;
                            scope.interval = interval;
                        }
                        else {
                            //console.log('that.count .... ' + scope.count);
                        }
                    },
                    scope: this
                });
            }
        }
    },

    onRefresh: function () {
        this.getStore().reload();
    },

    onStart: function () {
        console.log('starting PIE CHARTS...');
        this.count = 0;
        this.reqs = 0;
        this.started = true;
        this.refreshData(this);
        //console.log('started ...');
    },

    onStop: function () {
        console.log('stopping PIE CHARTS ...');
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

        //console.log('stopped ...');
    }
});

Toc.Map = function (config) {
    //console.debug(config);
    this.params = config;
    var that = this;
    config = config || {};
    config.region = 'center';
    config.width = "100%";
    config.started = false;
    //config.bodyStyle = 'height:150px';
    //config.height = '22%';
    config.layout = 'fit';

    var thisObj = this;

    config.listeners = {
        activate: function (comp) {
            //console.log('activate');

            comp.data = [];

            var configChart = function () {

                var map;

                map = AmCharts.makeChart(thisObj.body.id, {
                    "type": "map",
                    "theme": "light",
                    //"getAreasFromMap": true,
                    showObjectsAfterZoom: true,
                    "projection": "miller",

                    "imagesSettings": {
                        "rollOverColor": "#089282",
                        "rollOverScale": 3,
                        "selectedScale": 3,
                        "selectedColor": "#089282",
                        "color": "#13564e"
                    },

                    "areasSettings": {
                        "unlistedAreasColor": "#15A892"
                    },

                    "dataProvider": {
                        "map": "worldLow",
                        bringForwardOnHover: true,
                        "images": comp.data
                    }
                });

                comp.map = map;

                map.dataGenerated = true;
                map.validateNow();

                comp.start();
            };
            if (AmCharts.isReady) {
                configChart();
            } else {
                AmCharts.ready(configChart);
            }
        },
        show: function (comp) {
            //console.log('show');
        },
        enable: function (panel) {
            //console.log('enable');
            //this.onRefresh();
        },
        deactivate: function (panel) {
            //console.log('deactivate');
            panel.stop();
        },
        destroy: function (panel) {
            //console.log('destroy');
            panel.stop();
        },
        disable: function (panel) {
            //console.log('disable');
            panel.stop();
        },
        remove: function (container, panel) {
            //console.log('remove');
            panel.stop();
        },
        removed: function (container, panel) {
            //console.log('removed');
            panel.stop();
        },
        render: function (comp) {
            //console.log('render');
        },
        resize: function (Component, adjWidth, adjHeight, rawWidth, rawHeight) {
            Component.body.dom.style.height = config.body_height;
        },
        scope: this
    };

    config.tbar = [
        {
            //text: this.started ? 'Stop' : 'Start',
            text: '',
            iconCls: this.started ? 'stop' : 'play',
            handler: this.started ? this.stop : this.start,
            scope: this
        }
    ];

    Toc.Map.superclass.constructor.call(this, config);
};

Ext.extend(Toc.Map, Ext.Panel, {
    refreshData: function (scope) {
        var map = scope.map;
        scope.data = [];

        if (scope && scope.started) {
            if (scope.reqs == 0) {
                //console.log('reqs++');
                scope.reqs++;

                Ext.Ajax.request({
                    url: Toc.CONF.CONN_URL,
                    params: {
                        module: 'categories',
                        action: 'list_map',
                        customers_id: this.customers_id
                    },
                    callback: function (options, success, response) {
                        //console.log('reqs--');
                        scope.reqs--;

                        if (success) {
                            var json = Ext.decode(response.responseText);

                            for (var i = 0; i < json.records.length; i++) {
                                var record = {
                                    "zoomLevel": 5,
                                    "scale": 0.5,
                                    imageURL: json.records[i].url,
                                    "title": json.records[i].title,
                                    "latitude": json.records[i].latitude,
                                    "longitude": json.records[i].longitude
                                };

                                scope.data.push(record);

                                map.dataProvider.images = scope.data;
                                map.dataGenerated = true;
                                map.validateNow();
                            }
                        }
                        else {
                        }

                        if (scope.count == 0) {
                            var interval = setInterval(function () {
                                scope.refreshData(scope)
                            }, scope.freq || 10000);
                            //setTimeout(that.refreshData, that.freq || 10000);
                            scope.count++;
                            this.interval = interval;
                        }
                        else {
                            //console.log('that.count .... ' + scope.count);
                        }
                    },
                    scope: this
                });
            }
        }
    },

    start: function () {
        //console.log('starting ...');
        this.count = 0;
        this.reqs = 0;
        this.started = true;
        this.refreshData(this);
        console.log('started ...');

        //console.debug(this);
        this.topToolbar.items.items[0].setHandler(this.stop, this);
        this.topToolbar.items.items[0].setIconClass('stop');
    },

    stop: function () {
        //console.log('stopping ...');
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

        //console.log('stopped ...');
        //console.debug(this);

        this.topToolbar.items.items[0].setHandler(this.start, this);
        this.topToolbar.items.items[0].setIconClass('play');
    }
});