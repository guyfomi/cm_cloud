<?php
?>

Toc.imports.mainPanel = function(config) {
  config = config || {};
   
  config.layout = 'border';

  //config.pnlCustomersTree = new Toc.CustomersTreePanel({owner: config.owner, parent: this});
  config.grdImports = new Toc.imports.importsGrid({owner: config.owner,mainPanel: this});
  
  config.grdImports.on('selectchange', this.onGrdimportsSelectChange, this);
  config.grdImports.getStore().on('load', this.onGrdimportsLoad, this);
  //config.pnlCustomersTree.on('selectchange', this.onPnlGroupTreeNodeSelectChange, this);
  
  config.items = [config.grdImports];
    
  Toc.imports.mainPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.imports.mainPanel, Ext.Panel, {

  onGrdimportsLoad: function() {
    if (this.grdImports.getStore().getCount() > 0) {
      this.grdImports.getSelectionModel().selectFirstRow();
      record = this.grdImports.getStore().getAt(0);
      
      this.onGrdimportsSelectChange(record);
    } else {
    }
  },
  onPnlGroupTreeNodeSelectChange: function(customers_id) {
    this.grdImports.refreshGrid(customers_id);
  },
  
  getGroupTree: function() {
    return this.pnlCustomersTree;
  },

  onGrdimportsSelectChange: function(record) {
  }
});