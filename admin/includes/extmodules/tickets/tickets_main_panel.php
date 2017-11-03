<?php
?>

Toc.tickets.mainPanel = function(config) {
  config = config || {};
   
  config.layout = 'border';

  //config.pnlCustomersTree = new Toc.CustomersTreePanel({owner: config.owner, parent: this});
  config.grdtickets = new Toc.TicketsGrid({owner: config.owner,mainPanel: this});
  
  config.grdtickets.on('selectchange', this.onGrdticketsSelectChange, this);
  config.grdtickets.getStore().on('load', this.onGrdticketsLoad, this);
  //config.pnlCustomersTree.on('selectchange', this.onPnlGroupTreeNodeSelectChange, this);
  
  config.items = [config.grdtickets];
    
  Toc.tickets.mainPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.tickets.mainPanel, Ext.Panel, {

  onGrdticketsLoad: function() {
    if (this.grdtickets.getStore().getCount() > 0) {
      this.grdtickets.getSelectionModel().selectFirstRow();
      record = this.grdtickets.getStore().getAt(0);
      
      this.onGrdticketsSelectChange(record);
    } else {
    }
  },
  onPnlGroupTreeNodeSelectChange: function(customers_id) {
    this.grdtickets.refreshGrid(customers_id);
  },
  
  getGroupTree: function() {
    return this.pnlCustomersTree;
  },

  onGrdticketsSelectChange: function(record) {
  }
});