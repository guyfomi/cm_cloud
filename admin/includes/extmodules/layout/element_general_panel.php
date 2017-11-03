<?php
/*
  $Id: layout_general_panel.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.layout.GeneralPanel = function(config) {
  config = config || {};

  config.title = '<?php echo $osC_Language->get('section_general'); ?>';
  config.layout = 'form';
  config.layoutConfig = {labelSeparator: ''};
  config.defaults = {anchor: '97%'};
  config.labelWidth = 160;
  config.items = this.buildForm();

  Toc.layout.GeneralPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.layout.GeneralPanel, Ext.Panel, {

  buildForm: function() {
    var items = [];

    this.cboParentlayout = Toc.content.ContentManager.getlayoutCombo();

    items.push(this.cboParentlayout);

    <?php
      $i = 1;

      foreach ( $osC_Language->getAll() as $l ) {
        echo 'var lang' . $l['id'] . ' = new Ext.form.TextField({name: "layout_name[' . $l['id'] . ']",';
        if ($i != 1 )
          echo ' fieldLabel:"&nbsp;", ';
        else
          echo ' fieldLabel:"' . $osC_Language->get('field_name') . '", ';
        echo "labelStyle: 'background: url(../images/worldflags/" . $l['country_iso'] . ".png) no-repeat right center !important;',";
        echo 'allowBlank: false});';
        echo 'items.push(lang' . $l['id'] . ');';
        $i++;
      }
    ?>

    items.push({xtype: 'fileuploadfield', fieldLabel: '&nbsp;<?php echo $osC_Language->get("field_image"); ?>', name: 'image'});
    items.push({xtype: 'panel', name: 'layout_image', id: 'layout_image_panel', border: false});
    items.push({
      layout: 'column',
      border: false,
      items:[{
        id: 'status',
        layout: 'form',
        labelSeparator: ' ',
        border: false,
        items:[{fieldLabel: '&nbsp;<?php echo $osC_Language->get('field_status'); ?>', xtype:'radio', name: 'layout_status', boxLabel: 'Actif', xtype:'radio', inputValue: '1', checked: true}]
      },{
        layout: 'form',
        border: false,
        items: [{fieldLabel: '&nbsp;<?php echo $osC_Language->get('status_disabled'); ?>', boxLabel: 'Inactif', xtype:'radio', name: 'layout_status', hideLabel: true, inputValue: '0'}]
      }]});

    items.push({xtype: 'numberfield', fieldLabel: '&nbsp;<?php echo $osC_Language->get("field_sort_order"); ?>', name: 'sort_order'});

    return items;
  },

   setCategoryId : function(layoutId)
  {
     layoutId = layoutId == 0 ? -1 : layoutId;
     this.cboParentlayout.getStore().on('load', function() {
     this.cboParentlayout.setValue(layoutId);
  }, this);
  }
});