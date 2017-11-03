<?php

?>

Toc.customers.DataPanel = function(config) {
  config = config || {};    
  
  config.title = 'General';
  config.deferredRender = false;
  config.items = this.getDataPanel();

  Toc.customers.DataPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.customers.DataPanel, Ext.Panel, {

  getDataPanel: function() {
    this.cboCustomersGroups = Toc.content.ContentManager.getCustomersGroupsCombo();
    this.cboUsersGroups = Toc.content.ContentManager.getUsersGroupsCombo(this);
    this.cboUsers = Toc.content.ContentManager.getUsersCombo({autoLoad : false,fieldLabel : 'Administrateur'});

      this.pnlData = new Ext.Panel({
      layout : 'form',
      border: false,
      labelWidth : 185,
      defaults: {
         anchor: '97%'
      },
      autoHeight: true,
      style: 'padding: 6px',
      items: [
        {xtype: 'checkbox',anchor: '', fieldLabel: 'Actif', name: 'customers_status'},
        {xtype: 'textfield', fieldLabel: 'Intitule Client', name: 'customers_surname', allowBlank: false},
        this.cboCustomersGroups,
        this.cboUsersGroups,
        this.cboUsers
      ]
    });

    return this.pnlData;
  },
  onCboUsersGroupsSelect: function(combo, record, index) {
    this.cboUsers.enable();
    this.cboUsers.reset();
    this.cboUsers.getStore().baseParams['roles_id'] = record.get('roles_id');
    this.cboUsers.getStore().load();
  }
});