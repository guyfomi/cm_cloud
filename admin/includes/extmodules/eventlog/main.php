<?php
    echo 'Ext.namespace("Toc.eventlog");';
    include('eventlog_main_panel.php');
?>

Ext.override(TocDesktop.EventlogWindow, {
createWindow : function() {
var desktop = this.app.getDesktop();
var win = desktop.getWindow('eventlog-win');

if (!win) {
var pnl = new Toc.eventlog.mainPanel({owner: this});

win = desktop.createWindow({
id: 'eventlog-win',
title: "Event Log",
width: 850,
height: 400,
iconCls: 'icon-eventlog-win',
layout: 'fit',
items: pnl
});
}

win.show();
win.maximize();
}
});