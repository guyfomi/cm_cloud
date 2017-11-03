<?php
?>
Toc.layout.mainPanel = function(config) {
  config = config || {};
  
  config.layout = 'border';
  config.border = false;

  config.pnllayoutTree = new Toc.LayoutTreePanel({owner: config.owner, parent: this,can_edit : true});
  config.deTailPanel = new Toc.DetailPanel({owner: config.owner, mainPanel: this,can_edit:true});
  
  config.pnllayoutTree.on('selectchange', this.onPnllayoutTreeNodeSelectChange, this);
  
  config.items = [config.pnllayoutTree,config.deTailPanel];
  
  Toc.layout.mainPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.layout.mainPanel, Ext.Panel, {
  
  onPnllayoutTreeNodeSelectChange: function(node) {
      //console.log('onPnllayoutTreeNodeSelectChange');
      this.deTailPanel.removeAll();
      this.deTailPanel.topToolbar.items.items[0].disable();
      this.deTailPanel.topToolbar.items.items[2].disable();

      if(node.attributes.content_type)
      {
         Toc.showDetails(node,this.deTailPanel);
      }

<!--    this.grdlayout.refreshGrid(categoryId);-->
  },
  
  getlayoutTree: function() {
    return this.pnllayoutTree;
  }
});
