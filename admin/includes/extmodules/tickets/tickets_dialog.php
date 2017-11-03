<?php
?>

Toc.TicketDialog = function(config) {
  config = config || {};
  
  config.id = 'customers-dialog-win';
  config.title = 'Nouveau contact';
  config.modal = true;
  config.layout = 'fit';
  config.width = 800;
  config.iconCls = 'icon-customers-win';
  config.items = this.buildForm();
    
  config.buttons = [
    {
      text: TocLanguage.btnSave,
      handler: function() {
        this.submitForm();
      },
      scope: this
    },
    {
      text: TocLanguage.btnClose,
      handler: function() { 
        this.close();
      },
      scope: this
    }
  ];

  this.addEvents({'saveSuccess' : true});
  
  Toc.customers.CustomersDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.customers.CustomersDialog, Ext.Window, {
  
  show: function (id) {
    this.tabCustomers.activate(this.pnlData);
    this.customersId = id || null;
    
    this.frmCustomers.form.reset();
    this.frmCustomers.form.baseParams['customers_id'] = this.customersId;

    Toc.customers.CustomersDialog.superclass.show.call(this);
    this.loadCustomer(this.pnlData);
  },

  loadCustomer : function(panel){
     if (this.customersId && this.customersId > 0) {
      if(panel)
      {
        panel.getEl().mask('Chargement en cours....');
      }

      var that = this;

      this.frmCustomers.load({
        url: Toc.CONF.CONN_URL,
        params:{
          module: 'customers',
          action: 'load_customer'
        },
        success: function(form, action) {
          if(panel)
          {

          this.pnlData.cboCustomersGroups.getStore().on('load', function () {
              this.pnlData.cboCustomersGroups.setValue(action.result.data.customers_groups_id);
          }, this);

          this.pnlData.cboCustomersGroups.getStore().load();

          this.pnlData.cboUsers.getStore().on('load', function () {
             this.pnlData.cboUsers.setValue(action.result.data.administrators_id);
          }, this);

          this.pnlData.cboUsersGroups.getStore().on('load', function () {
              this.pnlData.cboUsersGroups.setValue(action.result.data.roles_id);

              this.pnlData.cboUsers.enable();
              this.pnlData.cboUsers.reset();
              this.pnlData.cboUsers.getStore().baseParams['roles_id'] = action.result.data.roles_id;
              this.pnlData.cboUsers.getStore().load();
          }, this);

          this.pnlData.cboUsersGroups.getStore().load();

          this.pnlAdress.cboZones.getStore().on('load', function () {
              this.pnlAdress.cboZones.setValue(action.result.data.zone_id);
          }, this);

          this.pnlAdress.cboCountries.purgeListeners();

          this.pnlAdress.cboCountries.getStore().on('load', function () {
              this.pnlAdress.cboCountries.setValue(action.result.data.country_id);
               this.pnlAdress.cboZones.enable();
               this.pnlAdress.cboZones.reset();
               this.pnlAdress.cboZones.getStore().baseParams['country_id'] = action.result.data.country_id;
               this.pnlAdress.cboZones.getStore().load();
          }, this);

          this.pnlAdress.cboCountries.getStore().load();

          this.pnlImages =  new Toc.content.ImagesPanel({content_id : this.customersId,content_type : 'customers',owner : Toc.content.ContentManager});
          this.pnlDocuments =  new Toc.content.DocumentsPanel({content_id : this.customersId,content_type : 'customers',owner : Toc.content.ContentManager});
          this.pnlLinks =  new Toc.content.LinksPanel({content_id : this.customersId,content_type : 'customers',owner : Toc.content.ContentManager});
          this.pnlComments =  new Toc.content.CommentsPanel({content_id : this.customersId,content_type : 'customers',owner : Toc.content.ContentManager});

          this.pnlImages.addListener('activate',function(panel){
             that.setHeight(515);
             that.center();
          });

          this.pnlDocuments.addListener('activate',function(panel){
             that.setHeight(515);
             that.center();
          });

          this.pnlLinks.addListener('activate',function(panel){
             that.setHeight(515);
             that.center();
          });

          this.pnlComments.addListener('activate',function(panel){
             that.setHeight(515);
             that.center();
          });

          this.tabCustomers.add(this.pnlImages);
          this.tabCustomers.add(this.pnlDocuments);
          this.tabCustomers.add(this.pnlLinks);
          this.tabCustomers.add(this.pnlComments);
             panel.getEl().unmask();
          }
        },
        failure: function(form, action) {
          Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData);
          if(panel)
          {
             panel.getEl().unmask();
          }

          this.close();
        },
        scope: this
      });
    }
    else {
       this.pnlData.cboCustomersGroups.getStore().load();
       this.pnlData.cboUsersGroups.getStore().load();
       this.pnlData.cboUsers.getStore().load();
       this.pnlAdress.cboCountries.getStore().load();
     }
  },

  getContentPanel: function() {
    var that = this;
    this.pnlData = new Toc.customers.DataPanel({parent : this});
    this.pnlAdress = new Toc.customers.AddressPanel();

    this.pnlData.addListener('activate',function(panel){
        that.setHeight(250);
        that.center();
    });

    this.pnlAdress.addListener('activate',function(panel){
        that.setHeight(460);
        that.center();
    });

    this.tabCustomers = new Ext.TabPanel({
      activeTab: 1,
      region: 'center',
      defaults:{
        hideMode:'offsets'
      },
      deferredRender: false,
      items: [
        this.pnlData,
        this.pnlAdress
      ]
    });

    return this.tabCustomers;
  },
      
  buildForm: function() {
    this.frmCustomers = new Ext.form.FormPanel({ 
      fileUpload: true,
      layout: 'border',
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'customers',
        action: 'save_customer'
      },
      deferredRender: false,
      items: [this.getContentPanel()]
    });
    
    return this.frmCustomers;
  },

  submitForm : function() {
    this.frmCustomers.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function(form, action) {
         this.fireEvent('saveSuccess', action.result.feedback);
         this.close();  
      },    
      failure: function(form, action) {
        if (action.failureType != 'client') {
          Ext.Msg.alert(TocLanguage.msgErrTitle, action.result.feedback);
        }
      },  
      scope: this
    });   
  }
});