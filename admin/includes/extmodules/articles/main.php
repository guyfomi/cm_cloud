<?php
/*
  $Id: main.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

    echo 'Ext.namespace("Toc.articles");';

    include('articles_data_panel.php');
    include('articles_main_panel.php');
    include('articles_dialog.php');
    include('articles_grid.php');
?>

Ext.override(TocDesktop.ArticlesWindow, {

createWindow: function() {
var desktop = this.app.getDesktop();
var win = desktop.getWindow('articles-win');

if (!win) {
var pnl = new Toc.articles.mainPanel({owner: this});

win = desktop.createWindow({
id: 'articles-win',
title: '<?php echo $osC_Language->get('heading_articles_title'); ?>',
width: 800,
height: 400,
iconCls: 'icon-articles-win',
layout: 'fit',
items: pnl
});
}

win.show();
},

createArticlesDialog: function() {
var desktop = this.app.getDesktop();
var dlg = desktop.getWindow('articles-dialog-win');

if (!dlg) {
dlg = desktop.createWindow({}, Toc.articles.ArticlesDialog);

dlg.on('saveSuccess', function(feedback) {
this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
}, this);
}

return dlg;
}
});