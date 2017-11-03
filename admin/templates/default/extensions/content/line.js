Toc.DeleteLine = function (lines_id, caller) {
    Ext.MessageBox.confirm(
        TocLanguage.msgWarningTitle,
        "Voulez-vous vraiment supprimer cette ligne ? Tous les sous elements seront egalement supprimés",
        function (btn) {
            if (btn == 'yes') {
                Ext.Ajax.request({
                    url: Toc.CONF.CONN_URL,
                    params: {
                        module: 'categories',
                        action: 'delete_line',
                        lines_id: lines_id
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

Toc.LinePanel = function (config) {
    config = config || {};

    //config.title = 'General';
    config.layout = 'fit';
    config.deferredRender = false;
    config.items = this.getDataPanel();

    Toc.LinePanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.LinePanel, Ext.Panel, {

    getDataPanel: function () {
        this.code = new Ext.form.TextField({fieldLabel: 'Code', name: 'code', allowBlank: false});
        this.name = new Ext.form.TextField({fieldLabel: 'Nom', name: 'name', allowBlank: false});
        this.unit = new Ext.form.TextField({fieldLabel: 'Unit', name: 'unit', allowBlank: true});
        this.building = new Ext.form.TextField({fieldLabel: 'Building', name: 'building', allowBlank: true});
        this.operator = new Ext.form.TextField({xtype: 'textfield', fieldLabel: 'Operator', name: 'operator', allowBlank: true});

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
                this.name,
                this.code,
                this.unit,
                this.building,
                this.operator
            ]
        });

        return this.pnlData;
    }
});

Toc.LineDialog = function (config) {
    config = config || {};

    config.id = 'line-dialog-win';
    config.title = 'Nouvelle Ligne';
    config.layout = 'fit';
    config.width = 600;
    config.height = 230;
    config.modal = true;
    config.iconCls = 'icon-line-win';
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

    Toc.LineDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.LineDialog, Ext.Window, {

    show: function () {
        if (this.plants_id == -1) {
            Ext.Msg.alert(TocLanguage.msgErrTitle, "Usine invalide !!!");
        }
        else {
            this.frmLine.form.reset();
            this.frmLine.form.baseParams['plants_id'] = this.plants_id;

            Toc.LineDialog.superclass.show.call(this);

            if (this.lines_id) {
                this.loadLine(this.pnlGeneral);
            }
        }
    },

    loadLine: function (panel) {
        if (this.lines_id) {
            this.frmLine.form.baseParams['lines_id'] = this.lines_id;
            if (panel) {
                panel.getEl().mask('Chargement Ligne en cours....');
            }
            this.frmLine.load({
                    url: Toc.CONF.CONN_URL,
                    params: {
                        action: 'load_line'
                    },
                    success: function (form, action) {
                        if (panel) {
                            this.pnlPermissions = new Toc.content.PermissionsPanel({content_id : this.lines_id,content_type : 'line',owner : this.owner});
                            this.pnlDocuments =  new Toc.content.DocumentsPanel({content_id : this.lines_id,content_type : 'line',owner : Toc.content.ContentManager});
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
            Ext.Msg.alert(TocLanguage.msgErrTitle, "Aucune Ligne selectionnée");
        }
    },

    buildForm: function () {
        this.pnlGeneral = new Toc.LinePanel({plants_id: this.plants_id});

        this.tablayout = new Ext.TabPanel({
            activeTab: 0,
            defaults: {
                hideMode: 'offsets'
            },
            deferredRender: false,
            items: [this.pnlGeneral]
        });

        this.frmLine = new Ext.form.FormPanel({
            id: 'form-layout',
            layout: 'fit',
            fileUpload: true,
            labelWidth: 120,
            url: Toc.CONF.CONN_URL,
            baseParams: {
                module: 'categories',
                action: 'save_line'
            },
            scope: this,
            items: this.tablayout
        });

        return this.frmLine;
    },

    submitForm: function () {
        this.frmLine.form.submit({
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