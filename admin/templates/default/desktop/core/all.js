Ext.app.App = function(a) {
	Ext.apply(this, a);
	this.addEvents({
		ready : true,
		beforeunload : true
	});
	Ext.onReady(this.initApp, this)
};
Ext.extend(Ext.app.App, Ext.util.Observable, {
	isReady : false,
	launchers : null,
	modules : null,
	styles : null,
	startConfig : null,
	gadgets : null,
	sidebaropened : false,
	sidebarcollapsed : false,
	loader : "load.php",
	requestQueue : [],
	startMenu : null,
	init : Ext.emptyFn,
	getModules : Ext.emptyFn,
	getLaunchers : Ext.emptyFn,
	getStyles : Ext.emptyFn,
	getStartConfig : Ext.emptyFn,
	getGadgets : Ext.emptyFn,
	isSidebarOpen : Ext.emptyFn,
	isWizardComplete : Ext.emptyFn,
	initApp : function() {
		Ext.BLANK_IMAGE_URL = "templates/default/desktop/images/default/s.gif";
		this.preventBackspace();
		this.modules = this.modules || this.getModules();
		this.startConfig = this.startConfig || this.getStartConfig();
		this.styles = this.styles || this.getStyles();
		this.launchers = this.launchers || this.getLaunchers();
		this.gadgets = this.gadgets || this.getGadgets();
		this.sidebaropened = this.sidebaropened || this.isSidebarOpen();
		this.desktop = new Ext.Desktop(this);
		this.startMenu = this.desktop.taskbar.startMenu;
		this.initModules();
		this.initStyles();
		this.initLaunchers();
		this.initGadgets();
		this.init();
		this.createTrayButton();
		Ext.EventManager.on(window, "beforeunload", this.onUnload, this);
		this.fireEvent("ready", this);
		this.isReady = true;
		this.onReady(function() {
			Ext.get("x-loading-mask").hide();
			Ext.get("x-loading-panel").hide();
			this.showLiveFeedNotification()
		}, this)
	},
	showLiveFeedNotification : function() {
		Ext.Ajax.request({
			waitMsg : TocLanguage.formSubmitWaitMsg,
			url : Toc.CONF.CONN_URL,
			params : {
				module : "system",
				action : "get_tomatcart_feeds"
			},
			callback : function(c, e, b) {
				var a = Ext.decode(b.responseText);
				if (a.success == true) {
					var d = this.showNotification({
						id : "live-feed",
						title : "MefobeCart Infos",
						iconCls : "icon-tomatocart-feeds",
						hideDelay : 7000,
						height : 500,
						html : a.feeds
					})
				}
			},
			scope : this
		})
	},
	initStyles : function() {
		var a = this.styles;
		if (!a) {
			return false
		}
		this.desktop.setBackgroundColor(a.backgroundcolor);
		this.desktop.setFontColor(a.fontcolor);
		this.desktop.setTheme(a.theme);
		this.desktop.setTransparency(a.transparency);
		this.desktop.setWallpaper(a.wallpaper);
		this.desktop.setWallpaperPosition(a.wallpaperposition);
		return true
	},
	initModules : function() {
		var a = this.modules;
		if (!a) {
			return false
		}
		for ( var f = 0, h = a.length; f < h; f++) {
			var b = a[f];
			b.app = this;
			if (b.appType == "group") {
				if (b.loaded === false && Ext.isEmpty(b.launcher.handler)) {
					b.launcher.handler = this.createWindow.createDelegate(this,
							[ b.id ])
				}
				var l = b.items;
				for ( var e = 0; e < l.length; e++) {
					var n = this.getModule(l[e]);
					if (n) {
						n.app = this;
						if (n.loaded === false
								&& Ext.isEmpty(n.launcher.handler)) {
							n.launcher.handler = this.createWindow
									.createDelegate(this, [ n.id ])
						}
						if (n.appType == "subgroup") {
							var c = n.items;
							for ( var d = 0; d < c.length; d++) {
								var g = this.getModule(c[d]);
								g.app = this;
								if (g.loaded === false
										&& Ext.isEmpty(g.launcher.handler)) {
									g.launcher.handler = this.createWindow
											.createDelegate(this, [ g.id ])
								}
								n.menu.add(g.launcher)
							}
						}
						b.menu.add(n.launcher)
					}
				}
				this.startMenu.add(b.launcher)
			}
		}
	},
	initLaunchers : function() {
		var a = this.launchers;
		if (!a) {
			return false
		}
		if (a.contextmenu) {
			this.initContextMenu(a.contextmenu)
		}
		if (a.quickstart) {
			this.initQuickStart(a.quickstart)
		}
		if (a.shortcut) {
			this.initShortcut(a.shortcut)
		}
		if (this.isWizardComplete() != true) {
			this.onReady(this.initAutoRun.createDelegate(this,
					[ [ "configuration_wizard-win" ] ]), this)
		} else {
			if (a.autorun) {
				this.onReady(this.initAutoRun.createDelegate(this,
						[ a.autorun ]), this)
			}
		}
		return true
	},
	initGadgets : function() {
		if (this.sidebaropened && !Ext.isEmpty(this.gadgets)) {
			if (this.gadgets.length > 0) {
				this.desktop.sidebar.addGadgets(this.gadgets, false)
			}
		}
	},
	showNotification : function(a) {
		var b = new Ext.ux.Notification(Ext.apply({
			animateTarget : Ext.get("ux-taskbar"),
			autoDestroy : true,
			hideDelay : 3000,
			html : "",
			iconCls : "x-icon-waiting",
			title : ""
		}, a));
		b.animShow();
		return b
	},
	initAutoRun : function(d) {
		if (d) {
			for ( var c = 0, b = d.length; c < b; c++) {
				var a = this.getModule(d[c]);
				if (a) {
					a.autorun = true;
					this.createWindow(d[c])
				}
			}
		}
	},
	initContextMenu : function(d) {
		if (d) {
			for ( var c = 0, b = d.length; c < b; c++) {
				var a = this.getModule(d[c]);
				if (a) {
					this.desktop.cmenu.add({
						id : a.id,
						iconCls : a.launcher.iconCls,
						text : a.launcher.text,
						scope : a.launcher.scope,
						handler : a.launcher.handler
					})
				}
			}
		}
	},
	initShortcut : function(c) {
		if (c) {
			for ( var b = 0, a = c.length; b < a; b++) {
				this.desktop.addShortcut(c[b], false)
			}
		}
	},
	initQuickStart : function(c) {
		if (c) {
			for ( var b = 0, a = c.length; b < a; b++) {
				this.desktop.addQuickStartButton(c[b], false)
			}
		}
	},
	onUnload : function(a) {
		if (this.fireEvent("beforeunload", this) === false) {
			a.stopEvent()
		}
	},
	getDesktop : function() {
		return this.desktop
	},
	getDesktopSettingWindow : function() {
		var a = new Toc.settings.SettingsDialog(this);
		return a
	},
	createTrayButton : function() {
		var a = new Ext.Button({
			text : "",
			id : "x-traypanel-setting-btn",
			tooltip : "setting your desktop",
			iconCls : "tray-setting",
			renderTo : "ux-systemtray-panel",
			handler : function() {
				var b = this.getDesktopSettingWindow();
				b.show();
				a.setDisabled(true)
			},
			scope : this
		})
	},
	createWindow : function(b) {
		if ((b.indexOf("grp") == -1) && (b.indexOf("subgroup") == -1)) {
			var a = this.requestModule(b, function(c) {
				if (c) {
					c.createWindow();
					if (this.sidebaropened) {
						var d = this.desktop.getWindow(c.getId());
						if (d) {
							d.center();
							d.on("maximize", function(e) {
								e.setWidth(Ext.lib.Dom.getViewWidth()
										- this.desktop.sidebar.pnlSidebar
												.getInnerWidth()
										- this.desktop.sidebar.splitWidth)
							}, this)
						}
					}
				}
			}, this)
		} else {
			return false
		}
	},
	callModuleFunc : function(a, e, d, c) {
		var b = a + "-win";
		this.requestModule(b, function(f) {
			var g = null;
			if (c && c.length > 0) {
				g = f[e].apply(f, c)
			} else {
				g = f[e]()
			}
			if (d) {
				d(g)
			}
		}, this)
	},
	requestModule : function(c, b, d) {
		var a = this.getModule(c);
		if (a) {
			if (a.loaded === true) {
				b.call(d, a)
			} else {
				if (b && d) {
					this.requestQueue.push({
						id : a.id,
						callback : b,
						scope : d
					});
					this.loadModule(a.id, a.launcher.text)
				}
			}
		}
	},
	loadModule : function(moduleId, moduleName) {
        var notif = this.desktop.showNotification({title: '' + moduleName, html: 'Chargement en cours veuillez patienter SVP...',autoDestroy : true});
		Ext.Ajax.request({
			url : Toc.CONF.LOAD_URL,
			params : {
				module : moduleId
			},
			success : function(o) {
                this.desktop.hideNotification(notif,1000);

				if (o.responseText !== "") {
					eval(o.responseText);
					this.loadModuleComplete(true, moduleId)
				} else {
					alert("An error occured on the server.")
				}
			},
			failure : function() {
				alert("Connection to the server failed!")
			},
			scope : this
		})
	},
	loadModuleComplete : function(h, d) {
		if (h === true && d) {
			var b = this.getModule(d);
			b.loaded = true;
			b.init();
			var f = this.requestQueue;
			var e = [];
			for ( var c = 0, a = f.length; c < a; c++) {
				if (f[c].id === d) {
					var g = f[c].callback.call(f[c].scope, b)
				} else {
					e.push(f[c])
				}
			}
			this.requestQueue = e
		}
		return g
	},
	getModule : function(b) {
		var c = this.modules;
		for ( var d = 0, a = c.length; d < a; d++) {
			if (c[d].id == b || c[d].moduleType == b) {
				return c[d]
			}
		}
		return null
	},
	registerModule : function(a) {
		if (!a) {
			return false
		}
		this.modules.push(a);
		a.launcher.handler = this.createWindow.createDelegate(this,
				[ a.moduleId ]);
		a.app = this
	},
	makeRequest : function(b, c) {
		if (b !== "" && c) {
			var a = this.requestModule(b, function(d) {
				if (d) {
					d.handleRequest(c)
				}
			}, this)
		}
	},
	onReady : function(b, a) {
		if (!this.isReady) {
			this.on("ready", b, a)
		} else {
			b.call(a, this)
		}
	},
	onBeforeUnload : function(a) {
		if (this.fireEvent("beforeunload", this) === false) {
			a.stopEvent()
		}
	},
	preventBackspace : function() {
		var a = new Ext.KeyMap(document, [ {
			key : Ext.EventObject.BACKSPACE,
			fn : function(c, d) {
				var b = d.target.tagName.toUpperCase();
				if (b != "INPUT" && b != "TEXTAREA") {
					d.stopEvent()
				}
			}
		} ])
	},
	showNotification : function(a) {
		var b = this.desktop.showNotification(a)
	}
});
Ext.ux.Sidebar = function(a) {
	var b = Ext.get("ux-taskbar");
	a = a || {};
	Ext.apply(this, a);
	this.codes = [];
	this.gadgets = [];
	this.gMargin = a.gMargin || 20;
	this.gHeight = a.gHeight || 163;
	this.topAndBottomMargin = a.topAndBottomMargin || 90;
	this.splitWidth = a.splitWidth || 5;
	this.sidebarWidth = a.sidebarWidth || 180;
	this.start = 0;
	this.pageSize = parseInt((Ext.lib.Dom.getViewHeight() - b.getHeight() - this.topAndBottomMargin)
			/ (this.gHeight + this.gMargin));
	this.buildSidebar();
	this.updatePageToolbar();
	this.desktopEl.setWidth(Ext.lib.Dom.getViewWidth()
			- this.pnlSidebar.getInnerWidth() - this.splitWidth);
    if(this.logoEl)
    {
        this.logoEl.hide();
    }
	this.app.sidebaropened = true;
	this.setBackgroundTransparency(this.app.styles.sidebartransparency);
	this.setBackgroundColor(this.app.styles.sidebarbackgroundcolor);
	Ext.EventManager.onWindowResize(this.onWindowResize, this);
	this.addEvents({
		gadgetload : true
	})
};
Ext
		.extend(
				Ext.ux.Sidebar,
				Ext.util.Observable,
				{
					buildSidebar : function() {
						this.pnlSidebar = new Ext.Panel(
								{
									width : 180,
									minSize : 175,
									maxSize : 175,
									border : false,
									cls : "sidebar",
									split : true,
									collapsible : true,
									collapseMode : "mini",
									region : "east",
									tbar : [
											{
												xtype : "panel",
												id : "sidebar-panel-site-logo",
												border : false,
												height : 50,
												items : {
													border : false,
													html : '<a href="http://www.mefobemarket.com" target="_blank"><img src="images/power_by_button.png" height=50 width=100 /></a>'
												}
											}, {
												cls : "sidebar-tbar-add",
												handler : this.configure,
												handleMouseEvents : false,
												scope : this
											},
											this.btnPrevious = new Ext.Button({
												cls : "sidebar-tbar-previous",
												handler : this.previous,
												handleMouseEvents : false,
												scope : this
											}), this.btnNext = new Ext.Button({
												cls : "sidebar-tbar-next",
												handler : this.next,
												handleMouseEvents : false,
												scope : this
											}), {
												cls : "sidebar-tbar-close",
												handler : this.hide,
												handleMouseEvents : false,
												scope : this
											} ],
									items : [ this.pnlGadgets = new Ext.Panel({
										baseCls : "sidebar-items-panel"
									}) ],
									listeners : {
										render : this.onSidebarRender,
										collapse : function() {
											this.collapse(true)
										},
										expand : function() {
											this.collapse(false)
										},
										scope : this
									}
								});
						this.pnlMain = new Ext.Panel({
							applyTo : this.sidebarEl,
							border : false,
							layout : "border",
							width : this.sidebarWidth,
							items : [ this.pnlSidebar, {
								xtype : "panel",
								border : false,
								region : "center"
							} ]
						});
						this.sidebarBgEl.setWidth(this.pnlSidebar
								.getInnerWidth());
						this.sidebarBgEl.setHeight(this.sidebarEl.getHeight())
					},
					onSidebarResized : function() {
						this.app.desktop.getManager().each(function(a) {
							if (a.maximized) {
								a.fireEvent("maximize", a)
							}
							a.center()
						})
					},
					hide : function() {
						Ext.TaskMgr.stopAll();
						this.pnlMain.hide();
						this.sidebarBgEl.hide();
						this.app.sidebaropened = false;
						this.desktopEl.setWidth(Ext.lib.Dom.getViewWidth());
                        if(this.logoEl)
                        {
                            this.logoEl.setVisible(true);
                        }

						this.onSidebarResized()
					},
					show : function() {
						this.pnlMain.setVisible(true);
						this.sidebarBgEl.setVisible(true);
						this.app.sidebaropened = true;
                        if(this.logoEl)
                        {
                            this.logoEl.hide();
                        }

						this.desktopEl.setWidth(Ext.lib.Dom.getViewWidth()
								- this.pnlSidebar.getInnerWidth()
								- this.splitWidth);
						this.startAllTasks();
						this.onSidebarResized()
					},
					collapse : function(a) {
						this.app.sidebarcollapsed = a;
						this.sidebarBgEl.setVisible(!a);
                        if(this.logoEl)
                        {
                            this.logoEl.setVisible(a);
                        }

						if (a == true) {
							this.desktopEl.setWidth(Ext.lib.Dom.getViewWidth()
									- this.splitWidth)
						} else {
							this.desktopEl.setWidth(Ext.lib.Dom.getViewWidth()
									- this.pnlSidebar.getInnerWidth()
									- this.splitWidth)
						}
						this.onSidebarResized()
					},
					startAllTasks : function() {
						if (this.gadgets.length > 0) {
							this.pnlGadgets.items.each(function(a) {
								if (a.autorun == true) {
									Ext.TaskMgr.start(a.runner)
								}
							})
						}
					},
					onWindowResize : function() {
						if ((this.app.sidebaropened == true)
								&& (this.app.sidebarcollapsed == false)) {
							var a = Ext.get("ux-taskbar");
							this.sidebarEl.setHeight(Ext.lib.Dom
									.getViewHeight()
									- a.getHeight());
							this.sidebarBgEl.setHeight(Ext.lib.Dom
									.getViewHeight()
									- a.getHeight());
							this.pnlMain.setHeight(Ext.lib.Dom.getViewHeight()
									- a.getHeight());
							this.pageSize = parseInt((Ext.lib.Dom
									.getViewHeight()
									- a.getHeight() - this.topAndBottomMargin)
									/ (this.gHeight + this.gMargin));
							this.start = 0;
							this.gotoPage(this.start)
						}
					},
					setBackgroundTransparency : function(a) {
						if (a >= 0 && a <= 100) {
							this.sidebarBgEl.addClass("sidebar-transparency");
							Ext.util.CSS.updateRule(".sidebar-transparency",
									"opacity", a / 100);
							Ext.util.CSS.updateRule(".sidebar-transparency",
									"-moz-opacity", a / 100);
							Ext.util.CSS.updateRule(".sidebar-transparency",
									"filter", "alpha(opacity=" + a + ")");
							this.app.styles.sidebartransparency = a
						}
					},
					setBackgroundColor : function(a) {
						if (a) {
							this.sidebarBgEl.setStyle("background-color", "#"
									+ a);
							this.app.styles.sidebarbackgroundcolor = a
						}
					},
					configure : function() {
						if (Ext.isEmpty(Ext.get("desktop-setting-win"))) {
							var a = this.app.getDesktopSettingWindow();
							a.show();
							a.activeSidebarPanel()
						} else {
							return false
						}
					},
					onSidebarRender : function() {
						var a = new Ext.dd.DropTarget(this.pnlSidebar.getEl(),
								{
									ddGroup : "GadgetsDD",
									copy : false,
									notifyDrop : function(b, d, c) {
										this.addGadget(c.record.get("code"),
												true);
										return true
									}.createDelegate(this)
								});
						this.pnlSidebar.getTopToolbar().addClass(
								"sidebar-top-bar")
					},
					updatePageToolbar : function() {
						if (this.gadgets.length > (this.pageSize * (this.start + 1))) {
							this.btnNext.enable();
							this.btnNext
									.removeClass("sidebar-tbar-next-disabled")
						} else {
							this.btnNext.disable();
							this.btnNext.addClass("sidebar-tbar-next-disabled")
						}
						if (this.start > 0) {
							this.btnPrevious.enable();
							this.btnPrevious
									.removeClass("sidebar-tbar-previous-disabled")
						} else {
							this.btnPrevious.disable();
							this.btnPrevious
									.addClass("sidebar-tbar-previous-disabled")
						}
					},
					hideGadget : function(a) {
						if (a.isVisible() == true) {
							a.hide();
							this.pnlGadgets.doLayout();
							if (a.autorun == true) {
								Ext.TaskMgr.stop(a.runner)
							}
						}
					},
					showGadget : function(a) {
						a.show();
						this.pnlGadgets.doLayout();
						if (a.autorun == true) {
							Ext.TaskMgr.start(a.runner)
						}
						this.addCloseButton(a);
						if (a.type == "flash") {
							this.renderFlash(a)
						}
					},
					gotoPage : function(c) {
						if (this.gadgets.length > 0) {
							var b = Math.min(
									(this.gadgets.length - this.pageSize * c),
									this.pageSize);
							var a = this.gadgets.slice(this.pageSize * c,
									(this.pageSize * c) + b);
							this.pnlGadgets.items.each(function(d) {
								this.hideGadget(d)
							}, this);
							Ext.each(a, function(d) {
								this.showGadget(d)
							}, this);
							this.updatePageToolbar()
						}
					},
					previous : function() {
						this.gotoPage(--this.start)
					},
					next : function() {
						this.gotoPage(++this.start)
					},
					contains : function(a) {
						for (i = 0; i < this.codes.length; i++) {
							if (a == this.codes[i]) {
								return true
							}
						}
						return false
					},
					loadGadget : function() {
						if (this.queue.length > 0) {
							var a = this.queue.shift();
							alert(a);
							var b = {
								action : "get_gadget",
								gadget : a
							};
							this.sendRequest(b, function(e, f, d) {
								var c = Ext.decode(d.responseText);
								if (c.success == true) {
									this.buildGadget(c.data, false)
								}
								this.loadGadget()
							}, this)
						}
					},
					addGadgets : function(a, d) {
						var c = a;
						if (c.length > 0) {
							var b = c.shift();
							this.addGadget(b, d);
							this.on("gadgetload", function() {
								if (c.length > 0) {
									var e = c.shift();
									this.addGadget(e, d)
								}
							}, this)
						}
					},
					addGadget : function(b, a) {
						if (!this.contains(b)) {
							var c = {
								action : "get_gadget",
								gadget : b
							};
							this.sendRequest(c, function(f, g, e) {
								var d = Ext.decode(e.responseText);
								if (d.success == true) {
									this.buildGadget(d.data, a)
								}
							}, this)
						}
					},
					sendRequest : function(b, c, a) {
						b.module = "desktop_settings";
						Ext.Ajax.request({
							url : Toc.CONF.CONN_URL,
							params : b,
							callback : c,
							scope : a
						})
					},
					buildGadget : function(b, a) {
						var c = {
							action : "get_gadget_view",
							gadget : b.code
						};
						this
								.sendRequest(
										c,
										function(f, h, e) {
											var d = Ext.decode(e.responseText);
											if (d.success == true) {
												b.plugins = new Ext.ux.Sidebar.GadgetCloseTool(
														this);
												b.app = this.app;
												if (b.type == "flash") {
													var g = new Ext.ux.Gadget(
															Ext
																	.applyIf(
																			b,
																			{
																				id : b.code
																						+ "-container",
																				code : b.code,
																				height : 200,
																				title : " ",
																				layout : "fit",
																				innerHeight : 148,
																				border : false
																			}));
													g.toolView = d.view
												} else {
													if (b.type == "grid") {
														var g = new Ext.ux.Gadget(
																Ext.applyIf(b,
																		d.view))
													}
												}
												if (g.autorun == true) {
													g.runner = {
														run : function() {
															g.task()
														},
														interval : g.interval
													}
												}
												this.pnlGadgets.add(g);
												this.pnlGadgets.doLayout();
												if ((this.start + 1)
														* this.pageSize > this.gadgets.length) {
													this.showGadget(g)
												} else {
													g.hide()
												}
												this.codes.push(g.code);
												this.gadgets.push(g);
												this.updatePageToolbar();
												if (a == true) {
													this.saveGadgets()
												}
												this.fireEvent("gadgetload")
											}
										}, this)
					},
					renderFlash : function(gadget) {
						gadget
								.getEl()
								.select(".x-panel-bwrap .x-panel-body")
								.each(
										function(toolView) {
											toolView
													.insertHtml(
															"beforeEnd",
															'<div id="tool-gadget-'
																	+ gadget.code
																	+ '" style="border:0;height:145px;" class="too-gadget"></div>');
											eval(gadget.toolView);
											toolView
													.select("div")
													.each(
															function(
																	toolViewContainer) {
																toolViewContainer
																		.remove()
															})
										})
					},
					addCloseButton : function(b) {
						var c = Ext
								.select("#" + b.getId() + " .x-panel-header");
						var a = Ext.get(b.getId());
						a.on({
							mouseover : {
								fn : function(d) {
									if (!d.within(a, true)) {
										c.setVisible(true, true)
									}
								}
							},
							mouseout : {
								fn : function(d) {
									if (!d.within(a, true)) {
										c.setVisible(false, true)
									}
								}
							}
						})
					},
					removeGadget : function(c) {
						var d = 0;
						for (i = 0; i < this.gadgets.length; i++) {
							if (c.getId() == this.gadgets[i].getId()) {
								d = i;
								break
							}
						}
						this.hideGadget(c);
						this.pnlGadgets.remove(c);
						var b = (parseInt(d / this.pageSize) + 1)
								* this.pageSize;
						if (b < this.gadgets.length) {
							var a = this.gadgets[b];
							this.showGadget(a)
						}
						this.codes.remove(c.code);
						this.gadgets.remove(c);
						this.updatePageToolbar();
						this.saveGadgets()
					},
					saveGadgets : function() {
						var a = {
							action : "save_gadgets",
							gadgets : Ext.encode(this.codes)
						};
						this.sendRequest(a, function(d, e, c) {
							var b = Ext.decode(c.responseText);
							if (b.success == false) {
								Ext.MessageBox.alert(TocLanguage.msgErrTitle,
										TocLanguage.connServerFailure)
							}
						}, this)
					}
				});
Ext.ux.Sidebar.GadgetCloseTool = function(a) {
	this.init = function(b) {
		b.tools = [ {
			id : "close",
			handler : function(f, d, c) {
				c.ownerCt.remove(c, true);
				a.removeGadget(b)
			}
		} ]
	}
};
Ext.namespace("Ext.ux");
Ext.ux.Gadget = function(a) {
	a = a || {};
	a.anchor = "100%";
	a.cls = "x-gadget";
	Ext.ux.Gadget.superclass.constructor.call(this, a)
};
Ext.extend(Ext.ux.Gadget, Ext.Panel, {
	task : Ext.emptyFn
});
Ext.reg("gadget", Ext.ux.Gadget);
Ext.DataView.DragSelector = function(g) {
	g = g || {};
	var j, d, i, l;
	var e, k, m = new Ext.lib.Region(0, 0, 0, 0);
	var b = g.dragSafe === true;
	this.init = function(q) {
		j = q;
		j.on("render", p)
	};
	function n() {
		e = [];
		j.all.each(function(q) {
			e[e.length] = q.getRegion()
		});
		k = j.el.getRegion()
	}
	function f() {
		return false
	}
	function h(q) {
		return !b || q.target == j.el.dom
	}
	function o(q) {
		j.on("containerclick", f, j, {
			single : true
		});
		if (!i) {
			i = j.el.createChild({
				cls : "x-view-selector"
			})
		} else {
			i.setDisplayed("block")
		}
		n();
		j.clearSelections()
	}
	function c(z) {
		var A = l.startXY;
		var E = l.getXY();
		var C = Math.min(A[0], E[0]);
		var B = Math.min(A[1], E[1]);
		var D = Math.abs(A[0] - E[0]);
		var u = Math.abs(A[1] - E[1]);
		m.left = C;
		m.top = B;
		m.right = C + D;
		m.bottom = B + u;
		m.constrainTo(k);
		i.setRegion(m);
		for ( var t = 0, v = e.length; t < v; t++) {
			var q = e[t], s = m.intersect(q);
			if (s && !q.selected) {
				q.selected = true;
				j.select(t, true)
			} else {
				if (!s && q.selected) {
					q.selected = false;
					j.deselect(t)
				}
			}
		}
	}
	function a(q) {
		if (i) {
			i.setDisplayed(false)
		}
	}
	function p(q) {
		l = new Ext.dd.DragTracker({
			onBeforeStart : h,
			onStart : o,
			onDrag : c,
			onEnd : a
		});
		l.initEl(q.el)
	}
};
ImageDragZone = function(a, b) {
	var b = b || {};
	this.view = a;
	ImageDragZone.superclass.constructor.call(this, a.getEl(), b)
};
Ext.extend(ImageDragZone, Ext.dd.DragZone, {
	getDragData : function(j) {
		var k = j.getTarget(".thumb-wrap");
		if (k) {
			var l = this.view;
			if (!l.isSelected(k)) {
				l.onClick(j)
			}
			var b = l.getSelectedNodes();
			var d = {
				nodes : b
			};
			if (b.length == 1) {
				d.ddel = k.cloneNode(true);
				d.ddel.id = Ext.id();
				d.single = true;
				var c = this.view.getSelectedRecords();
				d.record = c.pop();
				return d
			} else {
				var a = document.createElement("div");
				a.className = "multi-proxy";
				for ( var f = 0, h = b.length; f < h; f++) {
					a.appendChild(b[f].firstChild.firstChild.cloneNode(true));
					if ((f + 1) % 3 == 0) {
						a.appendChild(document.createElement("br"))
					}
				}
				var g = document.createElement("div");
				g.innerHTML = f + " images selected";
				a.appendChild(g);
				d.ddel = a;
				d.multi = true
			}
			return d
		}
		return false
	},
	beforeInvalidDrop : function(a) {
		this.hideProxy()
	}
});
Ext.Desktop = function(c) {
	var b = this;
	this.el = Ext.get("x-desktop");
	var p = this.el;
	this.taskbar = new Ext.ux.TaskBar(c);
	var i = this.taskbar;
	var f = Ext.get("ux-taskbar");
	this.shortcuts = new Ext.ux.Shortcuts({
		renderTo : "x-desktop",
		taskbarEl : f
	});
	if (c.sidebaropened) {
		n()
	}
	var a = this.sidebar;
	var j = Ext.get("ux-sidebar");
	var m = new Ext.WindowGroup();
	Ext.WindowMgr.zseed = 10000;
	var h;
	function g(r) {
		r.minimized = true;
		r.hide()
	}
	function e(r) {
		r.maximized = true;
		if (!Ext.isEmpty(r.footer)) {
			r.setHeight(Ext.lib.Dom.getViewHeight() - f.getHeight()
					- r.footer.getHeight())
		} else {
			r.setHeight(Ext.lib.Dom.getViewHeight() - f.getHeight())
		}
	}
	function o(r) {
		if (h && h != r) {
			q(h)
		}
		i.setActiveButton(r.taskButton);
		h = r;
		Ext.fly(r.taskButton.el).addClass("active-win");
		r.minimized = false
	}
	function q(r) {
		if (r == h) {
			h = null;
			Ext.fly(r.taskButton.el).removeClass("active-win")
		}
	}
	function d(r) {
		i.taskButtonPanel.remove(r.taskButton);
		k()
	}
	function n() {
		b.sidebar = new Ext.ux.Sidebar({
			desktopEl : Ext.get("x-desktop"),
			sidebarEl : Ext.get("ux-sidebar"),
			sidebarBgEl : Ext.get("ux-sidebar-background"),
			logoEl : Ext.get("tomatocart-logo"),
			app : c
		})
	}
	function l() {
		m.each(function(r) {
			r.center()
		})
	}
	function k() {
		p.setHeight(Ext.lib.Dom.getViewHeight() - f.getHeight());
		if (c.sidebaropened) {
			if (c.sidebarcollapsed == false) {
				p.setWidth(Ext.lib.Dom.getViewWidth() - j.getWidth())
			} else {
				p.setWidth(Ext.lib.Dom.getViewWidth() - b.sidebar.splitWidth)
			}
		} else {
			p.setWidth(Ext.lib.Dom.getViewWidth())
		}
	}
	Ext.EventManager.onWindowResize(k);
	this.layout = k;
	this.addSidebar = function() {
		n()
	};
	this.hideSidebar = function() {
		this.sidebar.hide();
		this.layout()
	};
	this.showSidebar = function() {
		if (Ext.isEmpty(b.sidebar)) {
			n()
		}
		b.sidebar.show();
		this.layout()
	};
	this.createWindow = function(s, r) {
		var t = new (r || Ext.Window)(Ext.applyIf(s || {}, {
			manager : m,
			minimizable : true,
			maximizable : true
		}));
		t.render(p);
		t.taskButton = i.taskButtonPanel.add(t);
		t.on("titlechange", function(u, v) {
			t.taskButton.setText(v)
		});
		t.cmenu = new Ext.menu.Menu({
			items : []
		});
		if ((Ext.isIE === false) && (Ext.isOpera === false)) {
		}
		t.on({
			activate : {
				fn : o
			},
			beforeshow : {
				fn : o
			},
			deactivate : {
				fn : q
			},
			minimize : {
				fn : g
			},
			maximize : {
				fn : e
			},
			close : {
				fn : d
			}
		});
		k();
		return t
	};
	this.getManager = function() {
		return m
	};
	this.getWindow = function(r) {
		return m.get(r)
	};
	this.getViewHeight = function() {
		return (Ext.lib.Dom.getViewHeight() - f.getHeight())
	};
	this.getViewWidth = function() {
		return Ext.lib.Dom.getViewWidth()
	};
	this.getWinWidth = function() {
		var r = Ext.lib.Dom.getViewWidth();
		return r < 200 ? 200 : r
	};
	this.getWinHeight = function() {
		var r = (Ext.lib.Dom.getViewHeight() - f.getHeight());
		return r < 100 ? 100 : r
	};
	this.getWinX = function(r) {
		return (Ext.lib.Dom.getViewWidth() - r) / 2
	};
	this.getWinY = function(r) {
		return (Ext.lib.Dom.getViewHeight() - f.getHeight() - r) / 2
	};
	this.getTaskbar = function() {
		return this.taskbar
	};
	this.setBackgroundColor = function(r) {
		if (r) {
			Ext.get(document.body).setStyle("background-color", "#" + r);
			c.styles.backgroundcolor = r
		}
	};
	this.setFontColor = function(r) {
		if (r) {
			Ext.util.CSS.updateRule(".ux-shortcut-btn-text", "color", "#" + r);
			c.styles.fontcolor = r
		}
	};
	this.setTheme = function(r) {
		if (r && r.code && r.path) {
			Ext.util.CSS.swapStyleSheet("theme", r.path);
			c.styles.theme = r.code
		}
	};
	this.setTransparency = function(r) {
		if (r >= 0 && r <= 100) {
			f.addClass("transparent");
			Ext.util.CSS.updateRule(".transparent", "opacity", r / 100);
			Ext.util.CSS.updateRule(".transparent", "-moz-opacity", r / 100);
			Ext.util.CSS.updateRule(".transparent", "filter", "alpha(opacity="
					+ r + ")");
			c.styles.transparency = r
		}
	};
	this.setWallpaper = function(u) {
		if (u && u.code) {
			var s = new Image();
			s.src = u.path;
			var r = new Ext.util.DelayedTask(t, this);
			r.delay(200);
			c.styles.wallpaper = u.code
		}
		function t() {
			if (s.complete) {
				r.cancel();
				document.body.background = s.src
			} else {
				r.delay(200)
			}
		}
	};
	this.setWallpaperPosition = function(s) {
		if (s) {
			if (s === "center") {
				var r = Ext.get(document.body);
				r.removeClass("wallpaper-tile");
				r.addClass("wallpaper-center")
			} else {
				if (s === "tile") {
					var r = Ext.get(document.body);
					r.removeClass("wallpaper-center");
					r.addClass("wallpaper-tile")
				}
			}
			c.styles.wallpaperposition = s
		}
	};
	this.showNotification = function(r) {
		var s = new Ext.ux.Notification(Ext.apply({
			animateTarget : f,
			autoDestroy : true,
			hideDelay : 3000,
			html : "",
			iconCls : "x-icon-waiting",
			title : ""
		}, r));
		s.show();
		return s
	};
	this.showDesktopSettingWin = function() {
		c.getDesktopSettingWindow().show()
	};
	this.hideNotification = function(s, r) {
		if (s) {
			(function() {
				s.animHide()
			}).defer(r || 3000)
		}
	};
	this.addAutoRun = function(t) {
		var r = c.getModule(t), s = c.launchers.autorun;
		if (r && !r.autorun) {
			r.autorun = true;
			s.push(t)
		}
	};
	this.removeAutoRun = function(u) {
		var r = c.getModule(u), t = c.launchers.autorun;
		if (r && r.autorun) {
			var s = 0;
			while (s < t.length) {
				if (t[s] == u) {
					t.splice(s, 1)
				} else {
					s++
				}
			}
			r.autorun = null
		}
	};
	this.addContextMenu = function(t, s) {
		var r = c.getModule(t);
		if (r && !r.contexmenu) {
			this.cmenu.add(r.launcher);
			if (s) {
				c.launchers.contextmenu.push(t)
			}
		}
	};
	this.removeContextMenu = function(w, u) {
		var r = c.getModule(w);
		if (r) {
			var t = this.cmenu.items.items;
			for ( var v = 0; v < t.length; v++) {
				if (t[v].iconCls == r.launcher.iconCls) {
					this.cmenu.remove(t[v])
				}
			}
			if (u) {
				var s = c.launchers.contextmenu;
				var v = 0;
				while (v < s.length) {
					if (s[v] == w) {
						s.splice(v, 1)
					} else {
						v++
					}
				}
			}
		}
	};
	this.addShortcut = function(u, s) {
		var r = c.getModule(u);
		if (r && !r.shortcut) {
			var t = r.launcher;
			r.shortcut = this.shortcuts.addShortcut({
				handler : t.handler,
				iconCls : t.shortcutIconCls,
				scope : t.scope,
				text : t.text,
				tooltip : t.tooltip || ""
			});
			if (s) {
				c.launchers.shortcut.push(u)
			}
		}
	};
	this.removeShortcut = function(v, s) {
		var r = c.getModule(v);
		if (r && r.shortcut) {
			this.shortcuts.removeShortcut(r.shortcut);
			r.shortcut = null;
			if (s) {
				var u = c.launchers.shortcut, t = 0;
				while (t < u.length) {
					if (u[t] == v) {
						u.splice(t, 1)
					} else {
						t++
					}
				}
			}
		}
	};
	this.addQuickStartButton = function(u, s) {
		var r = c.getModule(u);
		if (r && !r.quickStartButton) {
			var t = r.launcher;
			r.quickStartButton = this.taskbar.quickStartPanel.add({
				handler : t.handler,
				iconCls : t.iconCls,
				scope : t.scope,
				text : t.text,
				tooltip : t.tooltip || t.text
			});
			if (s) {
				c.launchers.quickstart.push(u)
			}
		}
	};
	this.removeQuickStartButton = function(v, t) {
		var s = c.getModule(v);
		if (s && s.quickStartButton) {
			this.taskbar.quickStartPanel.remove(s.quickStartButton);
			s.quickStartButton = null;
			if (t) {
				var r = c.launchers.quickstart, u = 0;
				while (u < r.length) {
					if (r[u] == v) {
						r.splice(u, 1)
					} else {
						u++
					}
				}
			}
		}
	};
	k();
	this.cmenu = new Ext.menu.Menu();
	p.on("contextmenu", function(s) {
		if (s.target.id === p.id) {
			s.stopEvent();
			if (c.launchers.contextmenu.length > 0) {
				if (!this.cmenu.el) {
					this.cmenu.render()
				}
				var r = s.getXY();
				r[1] -= this.cmenu.el.getHeight();
				this.cmenu.showAt(r)
			}
		}
	}, this)
};
Ext.namespace("Ext.ux.form");
Ext.ux.form.HexField = Ext
		.extend(
				Ext.form.TextField,
				{
					initEvents : function() {
						Ext.ux.form.HexField.superclass.initEvents.call(this);
						var a = function(i) {
							var b = i.getKey();
							if (!Ext.isIE
									&& (i.isSpecialKey() || b == i.BACKSPACE || b == i.DELETE)) {
								return
							}
							var j = "0123456789abcdefABCDEF";
							var g = new Ext.ux.form.HexField.Selection(document
									.getElementById(this.id));
							var f = g.create();
							var d = f.end - f.start;
							var l = i.getCharCode();
							var l = String.fromCharCode(l);
							var h = l + this.getValue();
							if (j.indexOf(l) === -1
									|| (h.length > 6 && d === 0)) {
								i.stopEvent()
							}
						};
						this.el.on("keypress", a, this)
					},
					validateValue : function(a) {
						if (!Ext.ux.form.HexField.superclass.validateValue
								.call(this, a)) {
							return false
						}
						if (!/^[0-9a-fA-F]{6}$/.test(a)) {
							return false
						}
						return true
					}
				});
Ext.reg("hexfield", Ext.ux.form.HexField);
Ext.ux.form.HexField.Selection = function(a) {
	this.element = a
};
Ext.ux.form.HexField.Selection.prototype.create = function() {
	if (document.selection != null && this.element.selectionStart == null) {
		return this._ieGetSelection()
	} else {
		return this._mozillaGetSelection()
	}
};
Ext.ux.form.HexField.Selection.prototype._mozillaGetSelection = function() {
	return {
		start : this.element.selectionStart,
		end : this.element.selectionEnd
	}
};
Ext.ux.form.HexField.Selection.prototype._ieGetSelection = function() {
	this.element.focus();
	var d = document.selection.createRange();
	var f = d.getBookmark();
	var g = this.element.value;
	var c = g;
	var b = this._createSelectionMarker();
	while (g.indexOf(b) != -1) {
		b = this._createSelectionMarker()
	}
	var e = d.parentElement();
	if (e == null || e.type != "text") {
		return {
			start : 0,
			end : 0
		}
	}
	d.text = b + d.text + b;
	g = this.element.value;
	var a = {};
	a.start = g.indexOf(b);
	g = g.replace(b, "");
	a.end = g.indexOf(b);
	this.element.value = c;
	d.moveToBookmark(f);
	d.select();
	return a
};
Ext.ux.form.HexField.Selection.prototype._createSelectionMarker = function() {
	return "##SELECTION_MARKER_" + Math.random() + "##"
};
Ext.namespace("Ext.ux");
Ext.ux.Shortcuts = function(a) {
	var j = Ext.get(a.renderTo), d = a.taskbarEl, e = 74, i = 64, c = 15, b = null, k = null, g = [];
	f();
	function f() {
		b = {
			index : 1,
			x : c
		};
		k = {
			index : 1,
			y : c
		}
	}
	function h(l) {
		if (l > (Ext.lib.Dom.getViewHeight() - d.getHeight())) {
			return true
		}
		return false
	}
	this.addShortcut = function(l) {
		var n = j.createChild({
			tag : "div",
			cls : "ux-shortcut-item"
		}), m = new Ext.ux.ShortcutButton(Ext.apply(l, {
			text : l.text
		}), n);
		g.push(m);
		this.setXY(m.container);
		return m
	};
	this.removeShortcut = function(m) {
		var p = document.getElementById(m.container.id);
		m.destroy();
		p.parentNode.removeChild(p);
		var o = [];
		for ( var n = 0, l = g.length; n < l; n++) {
			if (g[n] != m) {
				o.push(g[n])
			}
		}
		g = o;
		this.handleUpdate()
	};
	this.handleUpdate = function() {
		f();
		for ( var m = 0, l = g.length; m < l; m++) {
			this.setXY(g[m].container)
		}
	};
	this.setXY = function(m) {
		var l = k.y + e, n = h(k.y + e);
		if (n && l > (e + c)) {
			b = {
				index : b.index++,
				x : b.x + i + c
			};
			k = {
				index : 1,
				y : c
			}
		}
		m.setXY([ b.x, k.y ]);
		k.index++;
		k.y = k.y + e + c
	};
	Ext.EventManager.onWindowResize(this.handleUpdate, this, {
		delay : 500
	})
};
Ext.ux.ShortcutButton = function(a, b) {
	Ext.ux.ShortcutButton.superclass.constructor.call(this, Ext.apply(a, {
		renderTo : b,
		template : new Ext.Template('<div class="ux-shortcut-btn"><div>',
				'<img src="' + Ext.BLANK_IMAGE_URL + '" />',
				'<div class="ux-shortcut-btn-text">{0}</div>', "</div></div>")
	}))
};
Ext.extend(Ext.ux.ShortcutButton, Ext.Button, {
	buttonSelector : "div:first",
	initButtonEl : function(a, b) {
		Ext.ux.ShortcutButton.superclass.initButtonEl.apply(this, arguments);
		a.removeClass("x-btn");
		if (this.iconCls) {
			if (!this.cls) {
				a.removeClass(this.text ? "x-btn-text-icon" : "x-btn-icon")
			}
		}
	},
	autoWidth : function() {
	},
	setText : function(a) {
		this.text = a;
		if (this.el) {
			this.el.child("div.ux-shortcut-btn-text").update(a)
		}
	}
});
Ext.app.Module = function(a) {
	Ext.apply(this, a);
	Ext.app.Module.superclass.constructor.call(this);
	this.init()
};
Ext.extend(Ext.app.Module, Ext.util.Observable, {
	id : null,
	launcher : null,
	loaded : false,
	init : Ext.emptyFn,
	createWindow : Ext.emptyFn,
	handleRequest : Ext.emptyFn,
	getId : function() {
		return this.id
	}
});
Ext.namespace("Ext.ux");
Ext.ux.StartMenu = function(a) {
	Ext.ux.StartMenu.superclass.constructor.call(this, a);
	var b = this.toolItems;
	this.toolItems = new Ext.util.MixedCollection();
	if (b) {
		this.addTool.apply(this, b)
	}
};
Ext.extend(Ext.ux.StartMenu, Ext.menu.Menu, {
	height : 300,
	toolPanelWidth : 100,
	width : 300,
	render : function() {
		if (this.el) {
			return
		}
		var a = this.el = new Ext.Layer({
			cls : Ext.isIE8 ? "x-start-menu ux-start-menu"
					: "x-menu ux-start-menu",
			shadow : this.shadow,
			constrain : false,
			parentEl : this.parentEl || document.body,
			zindex : 15000
		});
		var e = a.createChild({
			tag : "div",
			cls : "x-window-header x-unselectable x-panel-icon " + this.iconCls
		});
		this.header = e;
		var f = e.createChild({
			tag : "span",
			cls : "x-window-header-text"
		});
		var o = e.wrap({
			cls : "ux-start-menu-tl"
		});
		var k = e.wrap({
			cls : "ux-start-menu-tr"
		});
		var c = e.wrap({
			cls : "ux-start-menu-tc"
		});
		this.menuBWrap = a.createChild({
			tag : "div",
			cls : "ux-start-menu-body x-border-layout-ct ux-start-menu-body"
		});
		var d = this.menuBWrap.wrap({
			cls : "ux-start-menu-ml"
		});
		var l = this.menuBWrap.wrap({
			cls : "ux-start-menu-mc ux-start-menu-bwrap"
		});
		this.menuPanel = this.menuBWrap.createChild({
			tag : "div",
			cls : "x-panel x-border-panel ux-start-menu-apps-panel opaque"
		});
		this.toolsPanel = this.menuBWrap.createChild({
			tag : "div",
			cls : "x-panel x-border-panel ux-start-menu-tools-panel"
		});
		var j = d.wrap({
			cls : "x-window-bwrap"
		});
		var i = j.createChild({
			tag : "div",
			cls : "ux-start-menu-bc"
		});
		var b = i.wrap({
			cls : "ux-start-menu-bl x-panel-nofooter"
		});
		var n = i.wrap({
			cls : "ux-start-menu-br"
		});
		i.setStyle({
			height : "0px",
			padding : "0 0 6px 0"
		});
		this.keyNav = new Ext.menu.MenuNav(this);
		if (this.plain) {
			a.addClass("x-menu-plain")
		}
		if (this.cls) {
			a.addClass(this.cls)
		}
		this.focusEl = a.createChild({
			tag : "a",
			cls : "x-menu-focus",
			href : "#",
			onclick : "return false;",
			tabIndex : "-1"
		});
		var h = this.menuPanel.createChild({
			tag : "ul",
			cls : "x-menu-list"
		});
		var m = this.toolsPanel.createChild({
			tag : "ul",
			cls : "x-menu-list"
		});
		var g = {
			click : {
				fn : this.onClick,
				scope : this
			},
			mouseover : {
				fn : this.onMouseOver,
				scope : this
			},
			mouseout : {
				fn : this.onMouseOut,
				scope : this
			}
		};
		h.on(g);
		this.items.each(function(q) {
			var p = document.createElement("li");
			p.className = "x-menu-list-item";
			h.dom.appendChild(p);
			q.render(p, this)
		}, this);
		this.ul = h;
		this.autoWidth();
		m.on(g);
		this.toolItems.each(function(q) {
			var p = document.createElement("li");
			p.className = "x-menu-list-item";
			m.dom.appendChild(p);
			q.render(p, this)
		}, this);
		this.toolsUl = m;
		this.autoWidth();
		this.menuBWrap.setStyle("position", "relative");
		this.menuBWrap.setHeight(this.height);
		this.menuPanel.setStyle({
			padding : "2px",
			position : "absolute",
			overflow : "auto"
		});
		this.toolsPanel.setStyle({
			padding : "2px 4px 2px 2px",
			position : "absolute",
			overflow : "auto"
		});
		this.setTitle(this.title)
	},
	findTargetItem : function(b) {
		var a = b.getTarget(".x-menu-list-item", this.ul, true);
		if (a && a.menuItemId) {
			if (this.items.get(a.menuItemId)) {
				return this.items.get(a.menuItemId)
			} else {
				return this.toolItems.get(a.menuItemId)
			}
		}
	},
	show : function(b, e, a) {
		this.parentMenu = a;
		if (!this.el) {
			this.render()
		}
		this.fireEvent("beforeshow", this);
		this.showAt(this.el.getAlignToXY(b, e || this.defaultAlign), a, false);
		var d = this.toolPanelWidth;
		var c = this.menuBWrap.getBox();
		this.menuPanel.setWidth(c.width - d);
		this.menuPanel.setHeight(c.height);
		this.toolsPanel.setWidth(d);
		this.toolsPanel.setX(c.x + c.width - d);
		this.toolsPanel.setHeight(c.height)
	},
	addTool : function() {
		var c = arguments, b = c.length, f;
		for ( var d = 0; d < b; d++) {
			var e = c[d];
			if (e.render) {
				f = this.addToolItem(e)
			} else {
				if (typeof e == "string") {
					if (e == "separator" || e == "-") {
						f = this.addToolSeparator()
					} else {
						f = this.addText(e)
					}
				} else {
					if (e.tagName || e.el) {
						f = this.addElement(e)
					} else {
						if (typeof e == "object") {
							f = this.addToolMenuItem(e)
						}
					}
				}
			}
		}
		return f
	},
	addToolSeparator : function() {
		return this.addToolItem(new Ext.menu.Separator({
			itemCls : "ux-toolmenu-sep"
		}))
	},
	addToolItem : function(b) {
		this.toolItems.add(b);
		if (this.toolsUl) {
			var a = document.createElement("li");
			a.className = "x-menu-list-item";
			this.toolsUl.dom.appendChild(a);
			b.render(a, this);
			this.delayAutoWidth()
		}
		return b
	},
	addToolMenuItem : function(a) {
		if (!(a instanceof Ext.menu.Item)) {
			if (typeof a.checked == "boolean") {
				a = new Ext.menu.CheckItem(a)
			} else {
				a = new Ext.menu.Item(a)
			}
		}
		return this.addToolItem(a)
	},
	setTitle : function(b, a) {
		this.title = b;
		if (this.header.child("span")) {
			this.header.child("span").update(b)
		}
		return this
	},
	getToolButton : function(a) {
		var b = new Ext.Button({
			handler : a.handler,
			minWidth : this.toolPanelWidth - 10,
			scope : a.scope,
			text : a.text
		});
		return b
	}
});
Ext.ux.NotificationMgr = {
	positions : []
};
Ext.ux.Notification = Ext.extend(Ext.Window, {
	initComponent : function() {
		Ext.apply(this, {
			iconCls : this.iconCls || "x-icon-information",
			width : 200,
			autoHeight : true,
			closable : true,
			plain : false,
			draggable : false,
			bodyStyle : "text-align:left;padding:10px;",
			resizable : false
		});
		if (this.autoDestroy) {
			this.task = new Ext.util.DelayedTask(this.animHide, this)
		} else {
			this.closable = true
		}
		Ext.ux.Notification.superclass.initComponent.call(this)
	},
	setMessage : function(a) {
		this.body.update(a)
	},
	setTitle : function(b, a) {
		Ext.ux.Notification.superclass.setTitle
				.call(this, b, a || this.iconCls)
	},
	onRender : function(b, a) {
		Ext.ux.Notification.superclass.onRender.call(this, b, a)
	},
	onDestroy : function() {
		Ext.ux.NotificationMgr.positions.remove(this.pos);
		Ext.ux.Notification.superclass.onDestroy.call(this)
	},
	afterShow : function() {
		Ext.ux.Notification.superclass.afterShow.call(this);
		this.on("move", function() {
			Ext.ux.NotificationMgr.positions.remove(this.pos);
			if (this.autoDestroy) {
				this.task.cancel()
			}
		}, this);
		if (this.autoDestroy) {
			this.task.delay(this.hideDelay || 5000)
		}
	},
	animShow : function() {
		this.pos = 0;
		while (Ext.ux.NotificationMgr.positions.indexOf(this.pos) > -1) {
			this.pos++
		}
		Ext.ux.NotificationMgr.positions.push(this.pos);
		this.setSize(200, 100);
		this.el.alignTo(this.animateTarget || document, "br-tr", [ -5,
				-1 - ((this.getSize().height + 10) * this.pos) ]);
		this.el.slideIn("b", {
			duration : 0.7,
			callback : function() {
				this.afterShow()
			},
			scope : this
		})
	},
	animHide : function() {
		Ext.ux.NotificationMgr.positions.remove(this.pos);
		if (Ext.isIE === false) {
			this.el.ghost("b", {
				duration : 1,
				remove : true
			})
		} else {
			Ext.ux.Notification.superclass.close.call(this)
		}
	}
});
Ext.namespace("Ext.ux");
Ext.ux.TaskBar = function(a) {
	this.app = a;
	this.init()
};
Ext.extend(Ext.ux.TaskBar, Ext.util.Observable, {
	init : function() {
		this.startMenu = new Ext.ux.StartMenu(Ext.apply({
			iconCls : "user",
			height : 300,
			shadow : true,
			title : "Todd Murdock",
			width : 300
		}, this.app.startConfig));
		this.startButton = new Ext.Button({
			text : TocLanguage.Start,
			id : "ux-startbutton",
			iconCls : "start",
			menu : this.startMenu,
			menuAlign : "bl-tl",
			renderTo : "ux-taskbar-start"
		});
		var a = Ext.get("ux-startbutton").getWidth() + 10;
		var d = new Ext.BoxComponent({
			el : "ux-taskbar-start",
			id : "TaskBarStart",
			minWidth : a,
			region : "west",
			split : false,
			width : a
		});
		this.quickStartPanel = new Ext.ux.QuickStartPanel({
			el : "ux-quickstart-panel",
			id : "TaskBarQuickStart",
			minWidth : 60,
			region : "west",
			split : true,
			width : 94
		});
		this.taskButtonPanel = new Ext.ux.TaskButtonsPanel({
			el : "ux-taskbuttons-panel",
			id : "TaskBarButtons",
			region : "center"
		});
		this.trayPanel = new Ext.ux.QuickStartPanel({
			el : "ux-systemtray-panel",
			id : "TaskBarSystemTray",
			minWidth : 100,
			region : "east",
			split : true,
			width : 100
		});
		var c = new Ext.Container({
			el : "ux-taskbar-panel-wrap",
			items : [ this.quickStartPanel, this.taskButtonPanel ],
			layout : "border",
			region : "center"
		});
		var b = new Ext.ux.TaskBarContainer({
			el : "ux-taskbar",
			layout : "border",
			items : [ d, c, this.trayPanel ]
		});
		this.el = b.el;
		return this
	},
	setActiveButton : function(a) {
		this.taskButtonPanel.setActiveButton(a)
	}
});
Ext.ux.TaskBarContainer = Ext.extend(Ext.Container, {
	initComponent : function() {
		Ext.ux.TaskBarContainer.superclass.initComponent.call(this);
		this.el = Ext.get(this.el) || Ext.getBody();
		this.el.setHeight = Ext.emptyFn;
		this.el.setWidth = Ext.emptyFn;
		this.el.setSize = Ext.emptyFn;
		this.el.setStyle({
			overflow : "hidden",
			margin : "0",
			border : "0 none"
		});
		this.el.dom.scroll = "no";
		this.allowDomMove = false;
		this.autoWidth = true;
		this.autoHeight = true;
		Ext.EventManager.onWindowResize(this.fireResize, this);
		this.renderTo = this.el
	},
	fireResize : function(a, b) {
		this.fireEvent("resize", this, a, b, a, b)
	}
});
Ext.ux.TaskButtonsPanel = Ext
		.extend(
				Ext.BoxComponent,
				{
					activeButton : null,
					enableScroll : true,
					scrollIncrement : 0,
					scrollRepeatInterval : 400,
					scrollDuration : 0.35,
					animScroll : true,
					resizeButtons : true,
					buttonWidth : 168,
					minButtonWidth : 118,
					buttonMargin : 2,
					buttonWidthSet : false,
					initComponent : function() {
						Ext.ux.TaskButtonsPanel.superclass.initComponent
								.call(this);
						this.on("resize", this.delegateUpdates);
						this.items = [];
						this.stripWrap = Ext.get(this.el).createChild({
							cls : "ux-taskbuttons-strip-wrap",
							cn : {
								tag : "ul",
								cls : "ux-taskbuttons-strip"
							}
						});
						this.stripSpacer = Ext.get(this.el).createChild({
							cls : "ux-taskbuttons-strip-spacer"
						});
						this.strip = new Ext.Element(
								this.stripWrap.dom.firstChild);
						this.edge = this.strip.createChild({
							tag : "li",
							cls : "ux-taskbuttons-edge"
						});
						this.strip.createChild({
							cls : "x-clear"
						})
					},
					add : function(c) {
						var a = this.strip.createChild({
							tag : "li"
						}, this.edge);
						var b = new Ext.ux.TaskBar.TaskButton(c, a);
						this.items.push(b);
						if (!this.buttonWidthSet) {
							this.lastButtonWidth = b.container.getWidth()
						}
						this.setActiveButton(b);
						return b
					},
					remove : function(d) {
						var b = document.getElementById(d.container.id);
						d.destroy();
						b.parentNode.removeChild(b);
						var e = [];
						for ( var c = 0, a = this.items.length; c < a; c++) {
							if (this.items[c] != d) {
								e.push(this.items[c])
							}
						}
						this.items = e;
						this.delegateUpdates()
					},
					setActiveButton : function(a) {
						this.activeButton = a;
						this.delegateUpdates()
					},
					delegateUpdates : function() {
						if (this.resizeButtons && this.rendered) {
							this.autoSize()
						}
						if (this.enableScroll && this.rendered) {
							this.autoScroll()
						}
					},
					autoSize : function() {
						var h = this.items.length;
						var c = this.el.dom.offsetWidth;
						var a = this.el.dom.clientWidth;
						if (!this.resizeButtons || h < 1 || !a) {
							return
						}
						var k = Math.max(Math.min(Math.floor((a - 4) / h)
								- this.buttonMargin, this.buttonWidth),
								this.minButtonWidth);
						var e = this.stripWrap.dom
								.getElementsByTagName("button");
						this.lastButtonWidth = Ext.get(e[0].id)
								.findParent("li").offsetWidth;
						for ( var f = 0, j = e.length; f < j; f++) {
							var b = e[f];
							var g = Ext.get(e[f].id).findParent("li").offsetWidth;
							var d = b.offsetWidth;
							b.style.width = (k - (g - d)) + "px"
						}
					},
					autoScroll : function() {
						var f = this.items.length;
						var d = this.el.dom.offsetWidth;
						var c = this.el.dom.clientWidth;
						var e = this.stripWrap;
						var b = e.dom.offsetWidth;
						var g = this.getScrollPos();
						var a = this.edge.getOffsetsTo(this.stripWrap)[0] + g;
						if (!this.enableScroll || f < 1 || b < 20) {
							return
						}
						e.setWidth(c);
						if (a <= c) {
							e.dom.scrollLeft = 0;
							if (this.scrolling) {
								this.scrolling = false;
								this.el.removeClass("x-taskbuttons-scrolling");
								this.scrollLeft.hide();
								this.scrollRight.hide()
							}
						} else {
							if (!this.scrolling) {
								this.el.addClass("x-taskbuttons-scrolling")
							}
							c -= e.getMargins("lr");
							e.setWidth(c > 20 ? c : 20);
							if (!this.scrolling) {
								if (!this.scrollLeft) {
									this.createScrollers()
								} else {
									this.scrollLeft.show();
									this.scrollRight.show()
								}
							}
							this.scrolling = true;
							if (g > (a - c)) {
								e.dom.scrollLeft = a - c
							} else {
								this.scrollToButton(this.activeButton, true)
							}
							this.updateScrollButtons()
						}
					},
					createScrollers : function() {
						var c = this.el.dom.offsetHeight;
						var a = this.el.insertFirst({
							cls : "ux-taskbuttons-scroller-left"
						});
						a.setHeight(c);
						a.addClassOnOver("ux-taskbuttons-scroller-left-over");
						this.leftRepeater = new Ext.util.ClickRepeater(a, {
							interval : this.scrollRepeatInterval,
							handler : this.onScrollLeft,
							scope : this
						});
						this.scrollLeft = a;
						var b = this.el.insertFirst({
							cls : "ux-taskbuttons-scroller-right"
						});
						b.setHeight(c);
						b.addClassOnOver("ux-taskbuttons-scroller-right-over");
						this.rightRepeater = new Ext.util.ClickRepeater(b, {
							interval : this.scrollRepeatInterval,
							handler : this.onScrollRight,
							scope : this
						});
						this.scrollRight = b
					},
					getScrollWidth : function() {
						return this.edge.getOffsetsTo(this.stripWrap)[0]
								+ this.getScrollPos()
					},
					getScrollPos : function() {
						return parseInt(this.stripWrap.dom.scrollLeft, 10) || 0
					},
					getScrollArea : function() {
						return parseInt(this.stripWrap.dom.clientWidth, 10) || 0
					},
					getScrollAnim : function() {
						return {
							duration : this.scrollDuration,
							callback : this.updateScrollButtons,
							scope : this
						}
					},
					getScrollIncrement : function() {
						return (this.scrollIncrement || this.lastButtonWidth + 2)
					},
					scrollToButton : function(e, a) {
						e = e.el.dom.parentNode;
						if (!e) {
							return
						}
						var c = e;
						var g = this.getScrollPos(), d = this.getScrollArea();
						var f = Ext.fly(c).getOffsetsTo(this.stripWrap)[0] + g;
						var b = f + c.offsetWidth;
						if (f < g) {
							this.scrollTo(f, a)
						} else {
							if (b > (g + d)) {
								this.scrollTo(b - d, a)
							}
						}
					},
					scrollTo : function(b, a) {
						this.stripWrap.scrollTo("left", b, a ? this
								.getScrollAnim() : false);
						if (!a) {
							this.updateScrollButtons()
						}
					},
					onScrollRight : function() {
						var a = this.getScrollWidth() - this.getScrollArea();
						var c = this.getScrollPos();
						var b = Math.min(a, c + this.getScrollIncrement());
						if (b != c) {
							this.scrollTo(b, this.animScroll)
						}
					},
					onScrollLeft : function() {
						var b = this.getScrollPos();
						var a = Math.max(0, b - this.getScrollIncrement());
						if (a != b) {
							this.scrollTo(a, this.animScroll)
						}
					},
					updateScrollButtons : function() {
						var a = this.getScrollPos();
						this.scrollLeft[a == 0 ? "addClass" : "removeClass"]
								("ux-taskbuttons-scroller-left-disabled");
						this.scrollRight[a >= (this.getScrollWidth() - this
								.getScrollArea()) ? "addClass" : "removeClass"]
								("ux-taskbuttons-scroller-right-disabled")
					}
				});
Ext.ux.TaskBar.TaskButton = function(b, a) {
	this.win = b;
	Ext.ux.TaskBar.TaskButton.superclass.constructor.call(this, {
		iconCls : b.iconCls,
		text : Ext.util.Format.ellipsis(b.title, 12),
		tooltip : b.taskbuttonTooltip || b.title,
		renderTo : a,
		handler : function() {
			if (b.minimized || b.hidden) {
				b.show()
			} else {
				if (b == b.manager.getActive()) {
					b.minimize()
				} else {
					b.toFront()
				}
			}
		},
		clickEvent : "mousedown"
	})
};
Ext.extend(Ext.ux.TaskBar.TaskButton, Ext.Button, {
	onRender : function() {
		Ext.ux.TaskBar.TaskButton.superclass.onRender.apply(this, arguments);
		this.cmenu = new Ext.menu.Menu({
			items : [ {
				id : "restore",
				text : "Restore",
				handler : function() {
					if (!this.win.isVisible()) {
						this.win.show()
					} else {
						this.win.restore()
					}
				},
				scope : this
			}, {
				id : "minimize",
				text : "Minimize",
				handler : this.win.minimize,
				scope : this.win
			}, {
				id : "maximize",
				text : "Maximize",
				handler : this.win.maximize,
				scope : this.win
			}, "-", {
				id : "close",
				text : "Close",
				handler : this.closeWin.createDelegate(this, this.win, true),
				scope : this.win
			} ]
		});
		this.cmenu.on("beforeshow", function() {
			var b = this.cmenu.items.items;
			var a = this.win;
			b[0].setDisabled(a.maximized !== true && a.hidden !== true);
			b[1].setDisabled(a.minimized === true);
			b[2].setDisabled(a.maximized === true || a.hidden === true);
			b[2].setDisabled(a.maximizable === false);
			b[3].setDisabled(a.closable === false)
		}, this);
		this.el.on("contextmenu", function(b) {
			b.stopEvent();
			if (!this.cmenu.el) {
				this.cmenu.render()
			}
			var a = b.getXY();
			a[1] -= this.cmenu.el.getHeight();
			this.cmenu.showAt(a)
		}, this)
	},
	closeWin : function(a, c, b) {
		if (!b.isVisible()) {
			b.show()
		} else {
			b.restore()
		}
		b.close()
	},
	setText : function(a) {
		if (a) {
			this.text = a;
			if (this.el) {
				this.el.child("td.x-btn-center " + this.buttonSelector).update(
						Ext.util.Format.ellipsis(a, 12))
			}
		}
	},
	setTooltip : function(b) {
		if (b) {
			this.tooltip = b;
			var a = this.el.child(this.buttonSelector);
			Ext.QuickTips.unregister(a.id);
			if (typeof this.tooltip == "object") {
				Ext.QuickTips.register(Ext.apply({
					target : a.id
				}, this.tooltip))
			} else {
				a.dom[this.tooltipType] = this.tooltip
			}
		}
	}
});
Ext.ux.QuickStartPanel = Ext.extend(Ext.BoxComponent, {
	enableMenu : true,
	initComponent : function() {
		Ext.ux.QuickStartPanel.superclass.initComponent.call(this);
		this.on("resize", this.delegateUpdates);
		this.menu = new Ext.menu.Menu();
		this.items = [];
		this.stripWrap = Ext.get(this.el).createChild({
			cls : "ux-quickstart-strip-wrap",
			cn : {
				tag : "ul",
				cls : "ux-quickstart-strip"
			}
		});
		this.stripSpacer = Ext.get(this.el).createChild({
			cls : "ux-quickstart-strip-spacer"
		});
		this.strip = new Ext.Element(this.stripWrap.dom.firstChild);
		this.edge = this.strip.createChild({
			tag : "li",
			cls : "ux-quickstart-edge"
		});
		this.strip.createChild({
			cls : "x-clear"
		})
	},
	add : function(b) {
		var a = this.strip.createChild({
			tag : "li"
		}, this.edge);
		var c = new Ext.Button(Ext.apply(b, {
			cls : "x-btn-icon",
			menuText : b.text,
			renderTo : a,
			text : ""
		}));
		this.items.push(c);
		this.delegateUpdates();
		return c
	},
	remove : function(d) {
		var b = document.getElementById(d.container.id);
		d.destroy();
		b.parentNode.removeChild(b);
		var e = [];
		for ( var c = 0, a = this.items.length; c < a; c++) {
			if (this.items[c] != d) {
				e.push(this.items[c])
			}
		}
		this.items = e;
		this.delegateUpdates()
	},
	menuAdd : function(a) {
		this.menu.add(a)
	},
	delegateUpdates : function() {
		if (this.enableMenu && this.rendered) {
			this.showButtons();
			this.clearMenu();
			this.autoMenu()
		}
	},
	showButtons : function() {
		var b = this.items.length;
		for ( var a = 0; a < b; a++) {
			this.items[a].show()
		}
	},
	clearMenu : function() {
		this.menu.removeAll()
	},
	autoMenu : function() {
		var k = this.items.length;
		var e = this.el.dom.offsetWidth;
		var j = this.el.dom.clientWidth;
		var b = this.stripWrap;
		var g = b.dom.offsetWidth;
		var f = this.edge.getOffsetsTo(this.stripWrap)[0];
		if (!this.enableMenu || k < 1 || g < 20) {
			return
		}
		b.setWidth(j);
		if (f <= j) {
			if (this.showingMenu) {
				this.showingMenu = false;
				this.menuButton.hide()
			}
		} else {
			j -= b.getMargins("lr");
			b.setWidth(j > 20 ? j : 20);
			if (!this.showingMenu) {
				if (!this.menuButton) {
					this.createMenuButton()
				} else {
					this.menuButton.show()
				}
			}
			mo = this.getMenuButtonPos();
			for ( var h = k - 1; h >= 0; h--) {
				var a = this.items[h].el.dom.offsetLeft
						+ this.items[h].el.dom.offsetWidth;
				if (a > mo) {
					this.items[h].hide();
					var c = this.items[h].initialConfig, d = {
						iconCls : c.iconCls,
						handler : c.handler,
						scope : c.scope,
						text : c.menuText
					};
					this.menuAdd(d)
				} else {
					this.items[h].show()
				}
			}
			this.showingMenu = true
		}
	},
	createMenuButton : function() {
		var b = this.el.dom.offsetHeight;
		var c = this.el.insertFirst({
			cls : "ux-quickstart-menubutton-wrap"
		});
		c.setHeight(b);
		var a = new Ext.Button({
			cls : "x-btn-icon",
			id : "ux-quickstart-menubutton",
			menu : this.menu,
			renderTo : c
		});
		c.setWidth(Ext.get("ux-quickstart-menubutton").getWidth());
		this.menuButton = c
	},
	getMenuButtonPos : function() {
		return this.menuButton.dom.offsetLeft
	}
});