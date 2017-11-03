<?php
?>

Toc.eventlog.mainPanel = function(config) {
  config = config || {};
   
  config.layout = 'border';

  //config.pnlCustomersTree = new Toc.CustomersTreePanel({owner: config.owner, parent: this});
  config.grdeventlog = new Toc.eventlogGrid({owner: config.owner,mainPanel: this});
  
  config.grdeventlog.on('selectchange', this.onGrdeventlogSelectChange, this);
  config.grdeventlog.getStore().on('load', this.onGrdeventlogLoad, this);
  //config.pnlCustomersTree.on('selectchange', this.onPnlGroupTreeNodeSelectChange, this);
  
  config.items = [config.grdeventlog];
    
  Toc.eventlog.mainPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.eventlog.mainPanel, Ext.Panel, {

  onGrdeventlogLoad: function() {
    if (this.grdeventlog.getStore().getCount() > 0) {
      this.grdeventlog.getSelectionModel().selectFirstRow();
      record = this.grdeventlog.getStore().getAt(0);
      
      this.onGrdeventlogSelectChange(record);
    } else {
    }
  },
  onPnlGroupTreeNodeSelectChange: function(customers_id) {
    this.grdeventlog.refreshGrid(customers_id);
  },
  
  getGroupTree: function() {
    return this.pnlCustomersTree;
  },

  onGrdeventlogSelectChange: function(record) {
  }
});