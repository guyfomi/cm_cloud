Toc.PlantPanel = function (config) {
    config = config || {};

    //config.title = 'General';
    config.deferredRender = false;
    config.layout = 'fit';
    config.items = this.getDataPanel();

    Toc.PlantPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.PlantPanel, Ext.Panel, {

    getDataPanel: function () {
        this.categories_name = new Ext.form.TextField({fieldLabel: 'Nom', name: 'categories_name', allowBlank: false});
        this.code = new Ext.form.TextField({fieldLabel: 'Code', name: 'code', allowBlank: false});
        this.location = new Ext.form.TextField({fieldLabel: 'Location', name: 'location', allowBlank: false});
        this.manufacturer = new Ext.form.TextField({fieldLabel: 'Manufacturer', name: 'manufacturer', allowBlank: false});
        this.model = new Ext.form.TextField({fieldLabel: 'Model', name: 'model', allowBlank: false});
        this.serial_number = new Ext.form.TextField({fieldLabel: 'Serial Number', name: 'serial_number', allowBlank: false});
        this.operator = new Ext.form.TextField({fieldLabel: 'Operator', name: 'operator', allowBlank: false});
        this.comments = new Ext.form.TextField({fieldLabel: 'Comments', name: 'comments', allowBlank: true});

        this.pnlData = new Ext.Panel({
            layout: 'form',
            border: false,
            labelWidth: 150,
            defaults: {
                anchor: '97%'
            },
            autoHeight: true,
            style: 'padding: 6px',
            items: [
                this.categories_name,
                this.code,
                this.location,
                this.manufacturer,
                this.model,
                this.serial_number,
                this.operator,
                this.comments
            ]
        });

        return this.pnlData;
    }
});

Toc.PlantAdressePanel = function (config) {
    config = config || {};

    config.title = 'Adresse';
    config.deferredRender = false;

    config.listeners = {
        activate: function (panel) {
            //console.log('activate');
        },
        show: function (panel) {
            //console.log('show');
        },
        render: function (panel) {
            //console.log('render');
        },
        enable: function (panel) {
            //console.log('enable');
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
        remove: function (container, panel) {
            //console.log('remove');
        },
        removed: function (container, panel) {
            //console.log('removed');
        },
        scope: this
    };

    config.items = this.getDataPanel();

    Toc.PlantAdressePanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.PlantAdressePanel, Ext.Panel, {

    getDataPanel: function () {
        this.cboCountries = Toc.content.ContentManager.getCountriesCombo();
        this.adresse = new Ext.form.TextField({fieldLabel: 'Ville', name: 'adresse', allowBlank: false, width: 520});
        this.email = new Ext.form.TextField({fieldLabel: 'Email', name: 'email', allowBlank: true, width: 520});
        this.phone = new Ext.form.TextField({fieldLabel: 'Phone', name: 'phone', allowBlank: true, width: 520});
        this.mobile = new Ext.form.TextField({fieldLabel: 'Mobile', name: 'mobile', allowBlank: true, width: 520});
        this.fax = new Ext.form.TextField({fieldLabel: 'Fax', name: 'fax', allowBlank: true, width: 520});
        this.url = new Ext.form.TextField({fieldLabel: 'Internet', name: 'url', allowBlank: true, width: 520});
        this.longitude = new Ext.form.Hidden({fieldLabel: 'longitude', name: 'longitude', allowBlank: false, width: 520});
        this.latitude = new Ext.form.Hidden({fieldLabel: 'latitude', name: 'latitude', allowBlank: false, width: 520});

        var that = this;
        this.adresse.on('blur',function(field)
        {
            that.checkAdresse(field);
        });

        this.pnlData = new Ext.Panel({
            layout: 'form',
            border: false,
            labelWidth: 150,
            defaults: {
                anchor: '97%'
            },
            autoHeight: true,
            style: 'padding: 6px',
            items: [
                this.cboCountries,
                this.adresse,
                this.email,
                this.phone,
                this.mobile,
                this.fax,
                this.url,
                this.longitude,
                this.latitude
            ]
        });

        return this.pnlData;
    },
    checkAdresse: function (field) {
        this.pnlData.getEl().mask('Verification adresse ....');
        Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
                module: 'categories',
                action: 'get_map',
                adresse: this.cboCountries.lastSelectionText + "," + this.adresse.getValue()
            },
            callback: function (options, success, response) {
                this.pnlData.getEl().unmask();
                var result = Ext.decode(response.responseText);

                if (result.success == true) {
                    this.latitude.setValue(result.latitude);
                    this.longitude.setValue(result.longitude);
                    field.setValue(result.formatted_address);
                } else {
                    //Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
                    if(field && field.markInvalid)
                    {
                        field.markInvalid(result.feedback);
                        field.focus();
                    }
                }
            },
            scope: this
        });
    }
});

Toc.PlantDialog = function (config) {
    config = config || {};

    config.id = 'plant-dialog-win';
    config.title = 'Nouvelle Usine';
    config.layout = 'fit';
    config.width = 730;
    config.height = 390;
    config.modal = true;
    config.iconCls = 'icon-plant-win';
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

    Toc.PlantDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.PlantDialog, Ext.Window, {

    show: function () {
        if (this.customers_id == -1) {
            Ext.Msg.alert(TocLanguage.msgErrTitle, "Client invalide !!!");
            return;
        }
        else {
            this.frmPlant.form.reset();
            this.frmPlant.form.baseParams['customers_id'] = this.customers_id;

            //this.tablayout.add(this.pnlAdresse);

            Toc.PlantDialog.superclass.show.call(this);

            if (this.plants_id) {
                this.loadPlant(this.pnlGeneral);
            }
            else {
                this.pnlAdresse.cboCountries.getStore().load();
            }
        }
    },

    loadPlant: function (panel) {
        if (this.plants_id) {
            this.frmPlant.form.baseParams['plants_id'] = this.plants_id;
            if (panel) {
                panel.getEl().mask('Chargement Plant en cours....');
            }
            this.frmPlant.load({
                    url: Toc.CONF.CONN_URL,
                    params: {
                        action: 'load_plant'
                    },
                    success: function (form, action) {
                        if (panel) {
                            this.pnlAdresse.cboCountries.getStore().on('load', function () {
                                this.pnlAdresse.cboCountries.setValue(action.result.data.country_id);
                            }, this);

                            this.pnlAdresse.cboCountries.getStore().load();

                            this.pnlPermissions = new Toc.content.PermissionsPanel({content_id: this.plants_id, content_type: 'plant', owner: this.owner});
                            this.pnlDocuments = new Toc.content.DocumentsPanel({content_id: this.plants_id, content_type: 'plant', owner: Toc.content.ContentManager});
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
            Ext.Msg.alert(TocLanguage.msgErrTitle, "Aucune Usine selectionnée");
        }
    },

    buildForm: function () {
        var that = this;
        this.pnlGeneral = new Toc.PlantPanel({owner: this.owner, customers_id: this.customers_id, title: 'General'});
        this.pnlAdresse = new Toc.PlantAdressePanel({owner: this.owner, customers_id: this.customers_id, title: 'Adresse'});

        this.pnlGeneral.addListener('activate', function (panel) {
            that.setHeight(370);
            that.center();
        });

        this.pnlAdresse.addListener('activate', function (panel) {
            that.setHeight(300);
            that.center();
        });

        this.tablayout = new Ext.TabPanel({
            activeTab: 0,
            defaults: {
                hideMode: 'offsets'
            },
            deferredRender: false,
            items: [this.pnlGeneral, this.pnlAdresse]
        });

        this.frmPlant = new Ext.form.FormPanel({
            id: 'form-layout',
            layout: 'fit',
            fileUpload: true,
            labelWidth: 120,
            url: Toc.CONF.CONN_URL,
            baseParams: {
                module: 'categories',
                action: 'save_plant'
            },
            scope: this,
            items: this.tablayout
        });

        return this.frmPlant;
    },

    submitForm: function () {
        this.frmPlant.form.submit({
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

Toc.DeletePlant = function (plants_id, caller) {
    Ext.MessageBox.confirm(
        TocLanguage.msgWarningTitle,
        "Voulez-vous vraiment supprimer cette usine ? Tous les sous elements seront egalement supprimés",
        function (btn) {
            if (btn == 'yes') {
                Ext.Ajax.request({
                    url: Toc.CONF.CONN_URL,
                    params: {
                        module: 'categories',
                        action: 'delete_plant',
                        plants_id: plants_id
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