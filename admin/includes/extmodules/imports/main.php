<?php
    echo 'Ext.namespace("Toc.imports");';

    include('imports_grid.php');
    include('imports_main_panel.php');
?>

Ext.override(TocDesktop.ImportsWindow, {
createWindow : function() {
var desktop = this.app.getDesktop();
var win = desktop.getWindow('imports-win');

if (!win) {
var pnl = new Toc.imports.mainPanel({owner: this});

win = desktop.createWindow({
id: 'imports-win',
title: "Import Monitor",
width: 850,
height: 400,
iconCls: 'icon-imports-win',
layout: 'fit',
items: pnl
});
}

win.show();
win.maximize();
}
});