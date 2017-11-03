var that = this;

Toc.content.ContentManager = {
};

Toc.content.ContentManager.createDocumentsDialog = function() {
    var dlg = TocDesktop.desktop.getWindow('documents-dialog-win');

    if (!dlg) {
        dlg = TocDesktop.desktop.createWindow({}, Toc.content.DocumentsDialog);

        dlg.on('saveSuccess', function(feedback) {
            TocDesktop.desktop.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
        }, this);
    }

    return dlg;
};

Toc.content.ContentManager.getFrequenceCombo = function (config) {

    var dsCombo = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'categories',
            action: 'list_freq'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            id: 'index'
        }, [
            'index',
            'value',
            'display'
        ]),
        autoLoad: false
    });

    return new Ext.form.ComboBox({
        fieldLabel: 'Frequence',
        store: dsCombo,
        displayField: 'display',
        valueField: 'value',
        hiddenName: 'frequence',
        name: 'hfrequence',
        mode: 'local',
        width: 100,
        //value : 5000,
        disabled: true,
        readOnly: true,
        triggerAction: 'all',
        forceSelection: true,
        allowBlank: false
    });
};

Toc.content.ContentManager.createImagesGalleryDialog = function() {
    var dlg = TocDesktop.desktop.getWindow('images_gallery-dialog-win');

    if (!dlg) {
        dlg = TocDesktop.desktop.createWindow({}, Toc.content.ImagesGalleryDialog);

        dlg.on('saveSuccess', function(feedback) {
            TocDesktop.desktop.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
        }, this);
    }

    return dlg;
};

Toc.content.ContentManager.createLinksDialog = function() {
    var dlg = TocDesktop.desktop.getWindow('links_links_dialog-win');

    if (!dlg) {
        dlg = TocDesktop.desktop.createWindow({}, Toc.content.LinksDialog);

        dlg.on('saveSuccess', function(feedback) {
            TocDesktop.desktop.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
        }, this);
    }

    return dlg;
};

Toc.content.ContentManager.createCommentDialog = function() {
    var dlg = TocDesktop.desktop.getWindow('comment_comment_dialog-win');

    if (!dlg) {
        dlg = TocDesktop.desktop.createWindow({}, Toc.content.CommentDialog);

        dlg.on('saveSuccess', function(feedback) {
            TocDesktop.desktop.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
        }, this);
    }

    return dlg;
};

Toc.content.ContentManager.getCategoriesCombo = function() {
    var dsParentCategories = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'categories',
            action: 'list_parent_category'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
            fields: [
                'id',
                'text'
            ]
        }),
        autoLoad: true
    });

    var cboParentCategories = new Toc.CategoriesComboBox({
        store: dsParentCategories,
        displayField: 'text',
        fieldLabel: 'Parent',
        valueField: 'id',
        name: 'parent_category_id',
        hiddenName: 'parent_category_id',
        triggerAction: 'all'
    });

    return cboParentCategories;
};

Toc.content.ContentManager.getCountriesCombo = function(caller) {
    var dsCountries = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'customers',
            action: 'get_countries'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            fields: ['country_id', 'country_title']
        }),
        autoLoad: false
    });

    var cboCountries = new Ext.form.ComboBox({
        fieldLabel: 'Pays',
        store: dsCountries,
        displayField: 'country_title',
        valueField: 'country_id',
        name: 'country',
        hiddenName: 'country_id',
        mode: 'local',
        readOnly: true,
        width : 520,
        triggerAction: 'all',
        forceSelection: true,
        allowBlank: false,
        listeners : caller ? {
            select: caller.onCboCountriesSelect,
            scope: caller
        } : null
    });

    return cboCountries;
};

Toc.content.ContentManager.getZonesCombo = function() {
    var dsZone = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'customers',
            action: 'get_zones'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            fields: ['zone_id', 'zone_name']
        }),
        autoLoad: false
    });

    var cboZones = new Ext.form.ComboBox({
        store: dsZone,
        mode: 'local',
        fieldLabel: 'Province',
        disabled: true,
        displayField: 'zone_name',
        valueField: 'zone_id',
        name: 'zone',
        hiddenName: 'zone_id',
        triggerAction: 'all',
        editable: false
    });

    return cboZones;
};

Toc.content.ContentManager.getCustomersGroupsCombo = function() {
    var dsCustomersGroups = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'customers',
            action: 'get_customers_groups'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            fields: ['id', 'text']
        }),
        autoLoad: false
    });

    var cboCustomersGroups = new Ext.form.ComboBox({
        fieldLabel: 'Groupe Clients',
        store: dsCustomersGroups,
        displayField: 'text',
        valueField: 'id',
        name: 'customers_groups',
        hiddenName: 'customers_groups_id',
        readOnly: true,
        forceSelection: true,
        mode: 'local',
        emptyText: 'Aucun',
        triggerAction: 'all'
    });

    return cboCustomersGroups;
};

Toc.content.ContentManager.getUsersGroupsCombo = function(caller) {
    var dsUsersGroups = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'roles',
            action: 'get_roles'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            fields: ['roles_id', 'roles_name']
        }),
        autoLoad: false
    });

    var cboUsersGroups = new Ext.form.ComboBox({
        fieldLabel: 'Groupe Utilisateurs',
        store: dsUsersGroups,
        displayField: 'roles_name',
        valueField: 'roles_id',
        name: 'users_groups',
        hiddenName: 'roles_id',
        readOnly: true,
        forceSelection: true,
        mode: 'local',
        emptyText: 'Aucun',
        triggerAction: 'all',
        listeners :{
            select: caller.onCboUsersGroupsSelect,
            scope: caller
        }
    });

    return cboUsersGroups;
};

Toc.content.ContentManager.getUsersCombo = function(config) {
    var dsUsers = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'users',
            action: 'get_users'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            fields: ['administrators_id', 'user_name']
        }),
        autoLoad: config.autoLoad || false
    });

    var cboUsers = new Ext.form.ComboBox({
        fieldLabel: config.fieldLabel || 'Administrateur',
        store: dsUsers,
        displayField: 'user_name',
        valueField: 'administrators_id',
        name: 'admin_group',
        hiddenName: 'administrators_id',
        mode: 'local',
        emptyText: 'Aucun',
        readOnly: true,
        triggerAction: 'all',
        forceSelection: true,
        allowBlank: false
    });

    return cboUsers;
};

Toc.content.ContentManager.getEventsCombo = function(config) {
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

    var comboEvents = new Ext.form.ComboBox({
        typeAhead: true,
        name: 'event',
        fieldLabel: 'Evenement',
        width: 400,
        triggerAction: 'all',
        mode: 'local',
        emptyText: '',
        store: eventds,
        editable: false,
        valueField: 'event',
        displayField: 'label'
    });

    return comboEvents;
};

Toc.content.ContentManager.getCustomersCombo = function(config) {
    var dsCustomers = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'customers',
            action: 'get_customers'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            fields: ['customers_id', 'customers_surname']
        }),
        autoLoad: config.autoLoad || false
    });

    var cboCustomers = new Ext.form.ComboBox({
        fieldLabel: 'Client',
        store: dsCustomers,
        displayField: 'customers_surname',
        valueField: 'customers_id',
        name: 'customers_id',
        hiddenName: 'customers_id',
        mode: 'local',
        emptyText: 'Selectionner un Client',
        readOnly: true,
        triggerAction: 'all',
        forceSelection: true,
        allowBlank: false
    });

    return cboCustomers;
};

Toc.content.ContentManager.getTicketStatusCombo = function(config) {
    var dsStatus = new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
            module: 'content',
            action: 'list_status'
        },
        reader: new Ext.data.JsonReader({
            root: Toc.CONF.JSON_READER_ROOT,
            fields: ['id','state', 'name']
        }),
        autoLoad: config.autoLoad || false
    });

    var cboStatus = new Ext.form.ComboBox({
        fieldLabel: 'Status',
        store: dsStatus,
        displayField: 'name',
        valueField: 'id',
        name: 'tickets_id',
        hiddenName: 'tickets_id',
        mode: 'local',
        emptyText: 'Specifier un Status',
        readOnly: true,
        triggerAction: 'all',
        forceSelection: true,
        allowBlank: false
    });

    return cboStatus;
};

Toc.content.ContentManager.getTinyEditor = function(name, height) {
    return {
        xtype: 'tinymce',
        fieldLabel: 'Description',
        name: 'content_description[' + name + ']',
        height: height || 350,
        tinymceSettings: {
            theme : "advanced",
            relative_urls: false, remove_script_host: false,
            plugins: "pagebreak,style,advhr,emotions,safari,advimage,preview,media,insertdatetime,print,contextmenu,paste,directionality",
            theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,,styleselect,formatselect,fontselect,fontsizeselect",
            theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
            theme_advanced_buttons3 : "hr,removeformat,visualaid,|,sub,sup,|,charmap,media,|,ltr,rtl,|,print,|,advhr,|,emotions,|,pagebreak",
            theme_advanced_toolbar_location : "top",
            theme_advanced_toolbar_align : "left",
            theme_advanced_statusbar_location : "bottom",
            theme_advanced_resizing : false,
            extended_valid_elements : "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]",
            template_external_list_url : "example_template_list.js"
        }
    };
};

Toc.content.ContentManager.getHtmlEditor = function(config) {
    return {
        xtype: 'tinymce',
        fieldLabel: config.fieldLabel || 'Description',
        name: config.name,
        height: config.height || 350,
        tinymceSettings: {
            theme : "advanced",
            relative_urls: false, remove_script_host: false,
            plugins: "pagebreak,style,advhr,emotions,safari,advimage,preview,media,insertdatetime,print,contextmenu,paste,directionality",
            theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,,styleselect,formatselect,fontselect,fontsizeselect",
            theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
            theme_advanced_buttons3 : "hr,removeformat,visualaid,|,sub,sup,|,charmap,media,|,ltr,rtl,|,print,|,advhr,|,emotions,|,pagebreak",
            theme_advanced_toolbar_location : "top",
            theme_advanced_toolbar_align : "left",
            theme_advanced_statusbar_location : "bottom",
            theme_advanced_resizing : false,
            extended_valid_elements : "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]",
            template_external_list_url : "example_template_list.js"
        }
    };
};

Toc.content.ContentManager.getContentStatusFields = function(width) {
    return {
        layout: 'column',
        border
            :
            false,
        items
            :
            [
                {
                    layout: 'form',
                    border: false,
                    labelSeparator: ' ',
                    width: width || 200,
                    items: [
                        {
                            fieldLabel: 'Publie',
                            xtype:'radio',
                            name: 'content_status',
                            inputValue: '1',
                            checked: true,
                            boxLabel: 'Oui'
                        }
                    ]
                },
                {
                    layout: 'form',
                    border: false,
                    width: width || 200,
                    items: [
                        {
                            hideLabel: true,
                            xtype:'radio',
                            inputValue: '0',
                            name: 'content_status',
                            boxLabel: 'Non'
                        }
                    ]
                }
            ]
    }
};