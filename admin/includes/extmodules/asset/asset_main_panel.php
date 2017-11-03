<?php
?>
Toc.asset.mainPanel = function(config) {
  config = config || {};
  
  config.layout = 'border';
  config.border = false;
  config.filter = false;

  config.pnlAssetTree = new Toc.LayoutTreePanel({owner: config.owner, parent: this,can_edit : false,autoRefresh : true});
  //config.explorerPanel = new Toc.AssetExplorerPanel({owner: config.owner, mainPanel: this});
  config.explorerPanel = new Toc.DetailPanel({owner: config.owner, mainPanel: this,can_edit:false});
  //config.ChInfo = new Toc.InfosGrid({owner: config.owner, mainPanel: this,title: 'ChInfo'});
  config.MP_Status = new Toc.InfosGrid({owner: config.owner, mainPanel: this,title : 'MP_Status',action : 'channel_status',content_type : 'sensor'});
  config.west_container = new Ext.Panel({
    layout: 'fit',
    region : 'east',
    hidden : true,
    autoScroll :true,
    split : true,
    width : 200,
    items: [config.MP_Status]
  });

  config.pnlAssetTree.on('selectchange', this.onPnlAssetTreeNodeSelectChange, this);
  
  config.items = [config.pnlAssetTree,config.explorerPanel,config.west_container];
  //config.items = [config.pnlAssetTree,config.explorerPanel];

  Toc.asset.mainPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.asset.mainPanel, Ext.Panel, {

onPnlAssetTreeNodeSelectChange: function(node) {
      this.explorerPanel.removeAll();
      //this.explorerPanel.topToolbar.items.items[0].disable();
      //this.explorerPanel.topToolbar.items.items[2].disable();

      if(node.attributes)
      {
         Toc.exploreAsset(node,this.explorerPanel);
      }
  },
  
  getAssetTree: function() {
    return this.pnlAssetTree;
  },
  getWestContainer: function() {
    return this.west_container;
  },
  hideInfos(){
     console.log('hideInfos');
     //this.ChInfo.hide();
     this.west_container.removeAll(true);
     //this.west_container.add(this.MP_Status);
     this.west_container.doLayout();
     //this.MP_Status.hide();
     this.west_container.hide();
  },
  showInfos(content_id,content_name,plant,line,component,asset){
     //console.log('showInfos');

     this.MP_Status = new Toc.InfosGrid({owner: this.owner, mainPanel: this,title : 'MP_Status',action : 'channel_status',content_type : 'sensor'});
     this.MP_Status.getStore().baseParams['content_name'] = content_name;
     this.MP_Status.getStore().baseParams['content_id'] = content_id;
     this.MP_Status.getStore().baseParams['plant'] = plant;
     this.MP_Status.getStore().baseParams['line'] = line;
     this.MP_Status.getStore().baseParams['component'] = component;
     this.MP_Status.getStore().baseParams['asset'] = asset;
     this.MP_Status.getStore().baseParams['action'] = 'channel_status';
     this.MP_Status.getStore().baseParams['content_type'] = 'sensor';

     this.west_container.removeAll(true);
     this.west_container.add(this.MP_Status);

     //this.ChInfo.show();
     //this.MP_Status.show();
     this.west_container.show();
     this.west_container.doLayout();
     this.MP_Status.onStart();
  },
  showAssetInfos(content_id,content_name,plant,line,component,asset){
     //console.log('showInfos');

     this.MP_Status = new Toc.InfosGrid({owner: this.owner, mainPanel: this,title : 'MP_Status',action : 'asset_status',content_type : 'asset'});
     this.MP_Status.getStore().baseParams['content_name'] = content_name;
     this.MP_Status.getStore().baseParams['content_id'] = content_id;
     this.MP_Status.getStore().baseParams['plant'] = plant;
     this.MP_Status.getStore().baseParams['line'] = line;
     this.MP_Status.getStore().baseParams['component'] = component;
     this.MP_Status.getStore().baseParams['asset'] = asset;
     this.MP_Status.getStore().baseParams['action'] = 'asset_status';
     this.MP_Status.getStore().baseParams['content_type'] = 'asset';

     this.west_container.removeAll(true);
     this.west_container.add(this.MP_Status);

     //this.ChInfo.show();
     //this.MP_Status.show();
     this.west_container.show();
     this.west_container.doLayout();
     this.MP_Status.onStart();
  },
  showParameterInfos(record){
     //console.debug(record);

     this.MP_Status = new Toc.ParameterInfosGrid({owner: this.owner, mainPanel: this,title : 'Parameter Infos',action : 'load_parameter',content_id : record.data.eventid});

     this.west_container.removeAll(true);
     this.west_container.add(this.MP_Status);

     this.west_container.show();
     this.west_container.doLayout();
  },
  showCustomerInfos(node){
     var pnlPieSensors = new Toc.PieChart({content_type: 'sensor', customers_id: node.id, title: 'Stats Capteurs', action: 'list_stats'});
     var pnlPieTickets = new Toc.PieChart({content_type: 'ticket', customers_id: node.id, title: 'Stats Tickets', action: 'list_stats'});

     this.west_container.removeAll(true);
     this.west_container.add(pnlPieTickets);
     this.west_container.add(pnlPieSensors);

     this.west_container.show();
     this.west_container.doLayout();
  }
});
