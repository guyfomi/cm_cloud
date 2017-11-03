<?php
    echo 'Ext.namespace("Toc.tickets");';
    include('tickets_main_panel.php');
?>

Ext.override(TocDesktop.TicketsWindow, {
createWindow : function() {
var desktop = this.app.getDesktop();
var win = desktop.getWindow('tickets-win');

if (!win) {
var pnl = new Toc.tickets.mainPanel({owner: this});

win = desktop.createWindow({
id: 'tickets-win',
title: "Tickets",
width: 850,
height: 400,
iconCls: 'icon-tickets-win',
layout: 'fit',
items: pnl
});
}

win.show();
win.maximize();
}
});