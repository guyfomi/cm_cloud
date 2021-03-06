<?php
/*
  $Id: tasks_general_panel.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.tasks.GeneralPanel = function(config) {
  config = config || {};    
  
  config.title = 'Contenu';
  config.activeTab = 0;
  config.deferredRender = false;
  config.items = this.buildForm();
    
  Toc.tasks.GeneralPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.tasks.GeneralPanel, Ext.TabPanel, {

  buildForm: function() {
    var items = [];
    
    <?php 
      list($defaultLanguageCode) = split("_", $osC_Language->getCode());
      
      foreach ($osC_Language->getAll() as $l) {
      
        if(USE_WYSIWYG_TINYMCE_EDITOR == 1) {
          $editor = '
            {
              xtype: \'tinymce\',
              fieldLabel: \'' . $osC_Language->get('filed_article_description') . '\',
              name: \'tasks_description[' . $l['id'] . ']\',
              height: 350,
              tinymceSettings: {
                theme : "advanced",
                language: "' . $defaultLanguageCode . '", relative_urls: false, remove_script_host: false,
                plugins: "safari,advimage,preview,media,contextmenu,paste,directionality",
                theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,,styleselect,formatselect,fontselect,fontsizeselect",
                theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
                theme_advanced_buttons3 : "hr,removeformat,visualaid,|,sub,sup,|,charmap,media,|,ltr,rtl,|",
                theme_advanced_toolbar_location : "top",
                theme_advanced_toolbar_align : "left",
                theme_advanced_statusbar_location : "bottom",
                theme_advanced_resizing : false,
                extended_valid_elements : "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]",
                template_external_list_url : "example_template_list.js"
              }
            }';
        } else {
          $editor = '{xtype: \'htmleditor\', fieldLabel: \'' . $osC_Language->get('filed_article_description') . '\', name: \'tasks_description[' . $l['id'] . ']\', height: 230}';
        }
      
        echo 'var pnlLang' . $l['code'] . ' = new Ext.Panel({
          labelWidth: 100,
          title:\'' . $l['name'] . '\',
          iconCls: \'icon-' . $l['country_iso'] . '-win\',
          layout: \'form\',
          labelSeparator: \' \',
          style: \'padding: 6px\',
          defaults: {
            anchor: \'97%\'
          },
          items: [
            {xtype: \'textfield\', fieldLabel: \'' . $osC_Language->get('field_article_name') . '\', name: \'tasks_name[' . $l['id'] . ']\', allowBlank: false},
            ' . $editor . '
          ]
        });
        
        items.push(pnlLang' . $l['code'] . ');
        ';
      }
    ?>
    
    return items;
  } 
});