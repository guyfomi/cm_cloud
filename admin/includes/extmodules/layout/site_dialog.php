<?php

?>
Toc.layout.layoutDialog = function (config) {
  config = config || {};
  
  config.id = 'layout-dialog-win';
  config.title = 'Nouvelle page';
  config.layout = 'fit';
  config.width = 730;
  config.height = 380;
  config.modal = true;
  config.iconCls = 'icon-layout-win';
  config.items = this.buildForm();
  
  config.buttons = [
    {
      text: TocLanguage.btnSave,
      handler: function () {
        this.submitForm();
      },
      scope: this
    }, 
    {
      text: TocLanguage.btnClose,
      handler: function () {
        this.close();
      },
      scope: this
    }
  ];
    
  this.addEvents({'saveSuccess': true});
  
  Toc.layout.layoutDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.layout.layoutDialog, Ext.Window, {
  
  show: function (id, pId) {
    this.layoutId = id || null;
    var parentId = pId || 0;
    
    this.frmlayout.form.reset();
    this.frmlayout.form.baseParams['layout_id'] = this.layoutId;

    Toc.layout.layoutDialog.superclass.show.call(this);
        
    this.pnlGeneral.cboParentlayout.getStore().on('load', function() {
      this.pnlGeneral.cboParentlayout.setValue(parentId);
    }, this);

    if(this.layoutId == -1)
    {
        this.tablayout.removeAll(false);
        this.pnlPermissions = new Toc.content.PermissionsPanel({content_id : this.layoutId,content_type : 'pages',owner : this.owner});
        this.tablayout.add(this.pnlPermissions);
        this.buttons[0].disable();
        this.tablayout.activate(this.pnlPermissions);
        return;
    }

    if (this.layoutId && this.layoutId > 0) {
        this.pnlPermissions = new Toc.content.PermissionsPanel({content_id : this.layoutId,content_type : 'pages',owner : this.owner});
        this.pnlComments =  new Toc.content.CommentsPanel({content_id : this.layoutId,content_type : 'pages',owner : Toc.content.ContentManager});
        this.tablayout.add(this.pnlPermissions);
        this.tablayout.add(this.pnlComments);

        this.loadCategory(this.pnlGeneral);
    }
    else
    {
        this.tablayout.activate(this.pnlGeneral);
    }
  },

  loadCategory : function(panel){

    if (this.layoutId && this.layoutId > 0) {
      if(panel)
      {
        panel.getEl().mask('Chargement en cours....');
      }
      this.frmlayout.load({
        url: Toc.CONF.CONN_URL,
        params: {
          action: 'load_category'
        },
        success: function (form, action) {
          if(panel)
          {
             panel.getEl().unmask();
          }

          var img = action.result.data.layout_image;

          if (img) {
            var html = '<img src ="../images/layout/' + img + '"  style = "margin-left: 170px; width: 70px; height:70px" /><br/><span style = "padding-left: 170px;">/images/layout/' + img + '</span>';
            this.frmlayout.findById('layout_image_panel').body.update(html);
          }
        },
        failure: function (form, action) {
          Ext.Msg.alert(TocLanguage.msgErrTitle, action.result.feedback);
          if(panel)
          {
             panel.getEl().unmask();
          }

          this.close();
        },
        scope: this
      },
        this
      );

      return;
    }
  },
  
  buildForm: function () {
    this.pnlGeneral = new Toc.layout.GeneralPanel();
    this.pnlMetaInfo = new Toc.content.MetaInfoPanel();

    this.tablayout = new Ext.TabPanel({
      activeTab: 0,
      defaults:{
        hideMode:'offsets'
      },
      deferredRender: false,
      items: [this.pnlGeneral,this.pnlMetaInfo]
    });
    
    this.frmlayout = new Ext.form.FormPanel({
      id: 'form-layout',
      layout: 'fit',
      fileUpload: true,
      labelWidth: 120,
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'layout',
        action: 'save_category'
      },
      scope: this,
      items: this.tablayout
    });
    
    return this.frmlayout;
  },
  
  submitForm: function () {        
    var status = this.pnlGeneral.findById('status').findByType('radio');
    status = status[0].getGroupValue();
    
    if(status == 0) {
      this.frmlayout.form.baseParams['product_flag'] = 1;
    
      Ext.MessageBox.confirm(
        TocLanguage.msgWarningTitle, 
        TocLanguage.msgDisableProducts, 
        function (btn) {
          if (btn == 'no') {
            this.frmlayout.form.baseParams['product_flag'] = 0;

				    this.frmlayout.form.submit({
				      waitMsg: TocLanguage.formSubmitWaitMsg,
				      success: function (form, action) {
				        this.fireEvent('saveSuccess', action.result.feedback);
				        this.close();
				      },
				      failure: function (form, action) {
				        if (action.failureType != 'client') {
				          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
				        }
				      },
				      scope: this
				    });

          } else{
				    this.frmlayout.form.submit({
				      waitMsg: TocLanguage.formSubmitWaitMsg,
				      success: function (form, action) {
				        this.fireEvent('saveSuccess', action.result.feedback, action.result.layout_id, action.result.text);
				        this.close();
				      },
				      failure: function (form, action) {
				        if (action.failureType != 'client') {
				          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
				        }
				      },
				      scope: this
				    });

          }
        }, 
        this
      );       
    } else {
	    this.frmlayout.form.submit({
	      waitMsg: TocLanguage.formSubmitWaitMsg,
	      success: function (form, action) {
	        this.fireEvent('saveSuccess', action.result.feedback, action.result.layout_id, action.result.text);
	        this.close();
	      },
	      failure: function (form, action) {
	        if (action.failureType != 'client') {
	          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
	        }
	      },
	      scope: this
	    });
    }
  }
});