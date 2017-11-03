Toc.DeleteComponent = function (component_id, caller) {
    Ext.MessageBox.confirm(
        TocLanguage.msgWarningTitle,
        "Voulez-vous vraiment supprimer ce Component ? Tous les sous elements seront egalement supprimés",
        function (btn) {
            if (btn == 'yes') {
                Ext.Ajax.request({
                    url: Toc.CONF.CONN_URL,
                    params: {
                        module: 'categories',
                        action: 'delete_component',
                        component_id: component_id
                    },
                    callback: function (options, success, response) {
                        var result = Ext.decode(response.responseText);

                        if (result.success == true) {
                            //this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: result.feedback});
                            if (caller && caller.refresh) {
                                caller.refresh();
                            }
                        } else {
                            Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
                        }
                    },
                    scope: this
                });
            }
        },
        this
    );
};

Toc.ComponentPanel = function (config) {
    config = config || {};

    //config.title = 'General';
    config.layout = 'fit';
    config.deferredRender = false;
    config.items = this.getDataPanel();
    config.autoScroll = true;

    Toc.ComponentPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.ComponentPanel, Ext.Panel, {

    getDataPanel: function () {
        this.name = new Ext.form.TextField({fieldLabel: 'Name', name: 'name', allowBlank: false});
        this.model = new Ext.form.TextField({fieldLabel: 'Model', name: 'model', allowBlank: true});
        this.serial = new Ext.form.TextField({fieldLabel: 'Serial Number', name: 'serial', allowBlank: true});
        this.function = new Ext.form.TextField({fieldLabel: 'Function', name: 'function', allowBlank: true});
        this.FirstNaturalFrequency = new Ext.form.TextField({fieldLabel: 'FirstNaturalFrequency', name: 'firstnaturalfrequency', allowBlank: true});
        this.SecondNaturalFrequency = new Ext.form.TextField({fieldLabel: 'SecondNaturalFrequency', name: 'secondnaturalfrequency', allowBlank: true});
        this.ThirdNaturalFrequency = new Ext.form.TextField({fieldLabel: 'ThirdNaturalFrequency', name: 'thirdnaturalfrequency', allowBlank: true});
        this.RollingBearing = new Ext.form.Checkbox({fieldLabel: 'RollingBearing', name: 'rollingbearing', allowBlank: true});
        this.RollingBearingWidth_m = new Ext.form.NumberField({fieldLabel: 'RollingBearingWidth_m', name: 'rollingbearingwidth_m', allowBlank: true});
        this.RollingBearingDiameter_m = new Ext.form.NumberField({fieldLabel: 'RollingBearingDiameter_m', name: 'rollingbearingdiameter_m', allowBlank: true});
        this.NumberRollingElements = new Ext.form.NumberField({fieldLabel: 'NumberRollingElements', name: 'numberrollingelements', allowBlank: true});
        this.RollingBearingContactAngle_Grad = new Ext.form.NumberField({fieldLabel: 'RollingBearingContactAngle_Grad', name: 'rollingbearingcontactangle_grad', allowBlank: true});
        this.OuterRingFrequency = new Ext.form.TextField({fieldLabel: 'OuterRingFrequency', name: 'outerringfrequency', allowBlank: true});
        this.InnerRingFrequency = new Ext.form.TextField({fieldLabel: 'InnerRingFrequency', name: 'innerringfrequency', allowBlank: true});
        this.CageFrequency = new Ext.form.TextField({fieldLabel: 'CageFrequency', name: 'cagefrequency', allowBlank: true});
        this.RollingElementRotationFrequency = new Ext.form.TextField({fieldLabel: 'RollingElementRotationFrequency', name: 'rollingelementrotationfrequency', allowBlank: true});
        this.RollingElementContactFrequency = new Ext.form.TextField({fieldLabel: 'RollingElementContactFrequency', name: 'rollingelementcontactfrequency', allowBlank: true});
        this.JournalBearing = new Ext.form.Checkbox({fieldLabel: 'JournalBearing', name: 'journalbearing', allowBlank: true});
        this.JournalBearingFluidType = new Ext.form.TextField({fieldLabel: 'JournalBearingFluidType', name: 'journalbearingfluidtype', allowBlank: true});
        this.JournalBearingGap_um = new Ext.form.NumberField({fieldLabel: 'JournalBearingGap_µm', name: 'journalbearinggap_um', allowBlank: true});
        this.OilWhirlMinOrder = new Ext.form.NumberField({fieldLabel: 'OilWhirlMinOrder', name: 'oilwhirlminorder', allowBlank: true});
        this.OilWhirlMaxOrder = new Ext.form.NumberField({fieldLabel: 'OilWhirlMaxOrder', name: 'oilwhirlmaxorder', allowBlank: true});
        this.MinFluidTemperature_C = new Ext.form.NumberField({fieldLabel: 'MinFluidTemperature_°C', name: 'minfluidtemperature_c', allowBlank: true});
        this.MaxFluidTemperature_C = new Ext.form.NumberField({fieldLabel: 'MaxFluidTemperature_°C', name: 'maxfluidtemperature_c', allowBlank: true});
        this.MinFluidPressure_bar = new Ext.form.NumberField({fieldLabel: 'MinFluidPressure_bar', name: 'minfluidpressure_bar', allowBlank: true});
        this.Turbomachinery = new Ext.form.Checkbox({fieldLabel: 'Turbomachinery', name: 'turbomachinery', allowBlank: true});
        this.BladesNumber = new Ext.form.NumberField({fieldLabel: 'BladesNumber', name: 'bladesnumber', allowBlank: true});
        this.VanesNumber = new Ext.form.NumberField({fieldLabel: 'VanesNumber', name: 'vanesnumber', allowBlank: true});
        this.BladeLength_m = new Ext.form.NumberField({fieldLabel: 'BladeLength_m', name: 'bladelength_m', allowBlank: true});
        this.BladePassFrequency = new Ext.form.TextField({fieldLabel: 'BladePassFrequency', name: 'bladepassfrequency', allowBlank: true});
        this.BladeTipFrequency = new Ext.form.TextField({fieldLabel: 'BladeTipFrequency', name: 'bladetipfrequency', allowBlank: true});
        this.VanePassingFrequency = new Ext.form.TextField({fieldLabel: 'VanePassingFrequency', name: 'vanepassingfrequency', allowBlank: true});
        this.BladeVanePassingFrequency = new Ext.form.TextField({fieldLabel: 'BladeVanePassingFrequency', name: 'bladevanepassingfrequency', allowBlank: true});
        this.Gear = new Ext.form.Checkbox({fieldLabel: 'Gear', name: 'gear', allowBlank: true});
        this.Geartype = new Toc.GeartypeCombo({fieldLabel: 'Geartype', name: 'geartype', allowBlank: true});
        this.GearRatio = new Ext.form.TextField({fieldLabel: 'GearRatio', name: 'gearratio', allowBlank: true});
        this.GearNumberStages = new Ext.form.NumberField({fieldLabel: 'GearNumberStages', name: 'gearnumberstages', allowBlank: true});
        this.GearLowSpeedShaftTeethNumber = new Ext.form.NumberField({fieldLabel: 'GearLowSpeedShaftTeethNumber', name: 'gearlowspeedshaftteethnumber', allowBlank: true});
        this.GearFastSpeedShaftTeethNumber = new Ext.form.NumberField({fieldLabel: 'GearFastSpeedShaftTeethNumber', name: 'gearfastspeedshaftteethnumber', allowBlank: true});
        this.GearRingTeethNumber = new Ext.form.NumberField({fieldLabel: 'GearRingTeethNumber', name: 'gearringteethnumber', allowBlank: true});
        this.GearPlanetTeethNumber = new Ext.form.NumberField({fieldLabel: 'GearPlanetTeethNumber', name: 'gearplanetteethnumber', allowBlank: true});
        this.GearPlanetaryCarrierTeethNumber = new Ext.form.NumberField({fieldLabel: 'GearPlanetaryCarrierTeethNumber', name: 'gearplanetarycarrierteethnumber', allowBlank: true});
        this.GearFixedComponent = new Ext.form.TextField({fieldLabel: 'GearFixedComponent', name: 'gearfixedcomponent', allowBlank: true});
        this.GearSunFrequency = new Ext.form.TextField({fieldLabel: 'GearSunFrequency', name: 'gearsunfrequency', allowBlank: true});
        this.GearRingFrequency = new Ext.form.TextField({fieldLabel: 'GearRingFrequency', name: 'gearringfrequency', allowBlank: true});
        this.GearPlanetFrequency = new Ext.form.TextField({fieldLabel: 'GearPlanetFrequency', name: 'gearplanetfrequency', allowBlank: true});
        this.GearMeshFrequency = new Ext.form.TextField({fieldLabel: 'GearMeshFrequency', name: 'gearmeshfrequency', allowBlank: true});
        this.GearTeethCommonFactor = new Ext.form.TextField({fieldLabel: 'GearTeethCommonFactor', name: 'gearteethcommonfactor', allowBlank: true});
        this.GearHuntingToothFrequency = new Ext.form.TextField({fieldLabel: 'GearHuntingToothFrequency', name: 'gearhuntingtoothfrequency', allowBlank: true});
        this.GearAssemblyPhase = new Ext.form.TextField({fieldLabel: 'GearAssemblyPhase', name: 'gearassemblyphase', allowBlank: true});
        this.GearGhostFrequency = new Ext.form.TextField({fieldLabel: 'GearGhostFrequency', name: 'gearghostfrequency', allowBlank: true});
        this.Belt = new Ext.form.Checkbox({fieldLabel: 'Belt', name: 'belt', allowBlank: true});
        this.BeltDiameterD1_m = new Ext.form.NumberField({fieldLabel: 'BeltDiameterD1_m', name: 'beltdiameterd1_m', allowBlank: true});
        this.BeltDiameterD2_m = new Ext.form.NumberField({fieldLabel: 'BeltDiameterD2_m', name: 'beltdiameterd2_m', allowBlank: true});
        this.BeltAxialGap_m = new Ext.form.NumberField({fieldLabel: 'BeltAxialGap_m', name: 'beltaxialgap_m', allowBlank: true});
        this.BeltTeethNumberZ1 = new Ext.form.NumberField({fieldLabel: 'BeltTeethNumberZ1', name: 'beltteethnumberz1', allowBlank: true});
        this.BeltTeethNumberZ2 = new Ext.form.NumberField({fieldLabel: 'BeltTeethNumberZ2', name: 'beltteethnumberz2', allowBlank: true});
        this.BeltLength_m = new Ext.form.NumberField({fieldLabel: 'BeltLength_m', name: 'beltlength_m', allowBlank: true});
        this.BeltSpeedN1_rpm = new Ext.form.NumberField({fieldLabel: 'BeltSpeedN1_rpm', name: 'beltspeedn1_rpm', allowBlank: true});
        this.BeltSpeedN2_rpm = new Ext.form.NumberField({fieldLabel: 'BeltSpeedN2_rpm', name: 'beltspeedn2_rpm', allowBlank: true});
        this.BeltFrequency = new Ext.form.TextField({fieldLabel: 'BeltFrequency', name: 'beltfrequency', allowBlank: true});
        this.TimingBeltFrequency = new Ext.form.TextField({fieldLabel: 'TimingBeltFrequency', name: 'timingbeltfrequency', allowBlank: true});
        this.Motor_Generator = new Ext.form.TextField({fieldLabel: 'Motor_Generator', name: 'motor_generator', allowBlank: true});
        this.MotorEfficiency = new Ext.form.TextField({fieldLabel: 'MotorEfficiency', name: 'motorefficiency', allowBlank: true});
        this.MotorPolePairs = new Ext.form.TextField({fieldLabel: 'MotorPolePairs', name: 'motorpolepairs', allowBlank: true});
        this.MotorRotorBars = new Ext.form.TextField({fieldLabel: 'MotorRotorBars', name: 'motorrotorbars', allowBlank: true});
        this.MotorStatorPoles = new Ext.form.TextField({fieldLabel: 'MotorStatorPoles', name: 'motorstatorpoles', allowBlank: true});
        this.MotorStatorSlots = new Ext.form.TextField({fieldLabel: 'MotorStatorSlots', name: 'motorstatorslots', allowBlank: true});
        this.MotorCoilsPerPole = new Ext.form.TextField({fieldLabel: 'MotorCoilsPerPole', name: 'motorcoilsperpole', allowBlank: true});
        this.MotorLineOfFrequency = new Ext.form.TextField({fieldLabel: 'MotorLineOfFrequency', name: 'motorlineoffrequency', allowBlank: true});
        this.MotorSynchronuousSpeedFrequency = new Ext.form.TextField({fieldLabel: 'MotorSynchronuousSpeedFrequency', name: 'motorsynchronuousspeedfrequency', allowBlank: true});
        this.MotorRunningSpeedFrequency = new Ext.form.TextField({fieldLabel: 'MotorRunningSpeedFrequency', name: 'motorrunningspeedfrequency', allowBlank: true});
        this.MotorSlipFrequency = new Ext.form.TextField({fieldLabel: 'MotorSlipFrequency', name: 'motorslipfrequency', allowBlank: true});
        this.MotorSlipRatio = new Ext.form.TextField({fieldLabel: 'MotorSlipRatio', name: 'motorslipratio', allowBlank: true});
        this.MotorPolePassFrequency = new Ext.form.TextField({fieldLabel: 'MotorPolePassFrequency', name: 'motorpolepassfrequency', allowBlank: true});
        this.MotorSlotPassFrequency = new Ext.form.TextField({fieldLabel: 'MotorSlotPassFrequency', name: 'motorslotpassfrequency', allowBlank: true});
        this.MotorRotorBarFrequency = new Ext.form.TextField({fieldLabel: 'MotorRotorBarFrequency', name: 'motorrotorbarfrequency', allowBlank: true});
        this.MotorStatorSlotFrequency = new Ext.form.TextField({fieldLabel: 'MotorStatorSlotFrequency', name: 'motorstatorslotfrequency', allowBlank: true});
        this.MotorCommutatorFrequency = new Ext.form.TextField({fieldLabel: 'MotorCommutatorFrequency', name: 'motorcommutatorfrequency', allowBlank: true});
        this.MotorStaticEccentricityFrequency = new Ext.form.TextField({fieldLabel: 'MotorStaticEccentricityFrequency', name: 'motorstaticeccentricityfrequency', allowBlank: true});
        this.MotorDynamicEccentricity = new Ext.form.TextField({fieldLabel: 'MotorDynamicEccentricity', name: 'motordynamiceccentricity', allowBlank: true});
        this.MotorStatorMechanicalDamageFrequency = new Ext.form.TextField({fieldLabel: 'MotorStatorMechanicalDamageFrequency', name: 'motorstatormechanicaldamagefrequency', allowBlank: true});
        this.MotorRotorDefectFrequency = new Ext.form.TextField({fieldLabel: 'MotorRotorDefectFrequency', name: 'motorrotordefectfrequency', allowBlank: true});
        this.MotorLooseStatorCoilFrequency = new Ext.form.TextField({fieldLabel: 'MotorLooseStatorCoilFrequency', name: 'motorloosestatorcoilfrequency', allowBlank: true});

        this.pnlData = new Ext.Panel({
            layout: 'column',
            items: [
                {
                    columnWidth: .33,
                    layout: 'form',
                    border: false,
                    autoScroll : true,
                    labelWidth: 160,
                    defaults: {
                        anchor: '97%'
                    },
                    autoHeight: true,
                    style: 'padding: 2px',
                    items: [
                        this.name,
                        this.model,
                        this.serial,
                        this.function,
                        this.FirstNaturalFrequency,
                        this.SecondNaturalFrequency,
                        this.ThirdNaturalFrequency,
                        this.RollingBearing,
                        this.RollingBearingWidth_m,
                        this.RollingBearingDiameter_m,
                        this.NumberRollingElements,
                        this.RollingBearingContactAngle_Grad,
                        this.OuterRingFrequency,
                        this.InnerRingFrequency,
                        this.CageFrequency,
                        this.RollingElementRotationFrequency,
                        this.RollingElementContactFrequency,
                        this.JournalBearing,
                        this.JournalBearingFluidType,
                        this.JournalBearingGap_um,
                        this.OilWhirlMinOrder,
                        this.OilWhirlMaxOrder,
                        this.MinFluidTemperature_C,
                        this.MaxFluidTemperature_C,
                        this.MinFluidPressure_bar,
                        this.Turbomachinery,
                        this.BladesNumber,
                        this.VanesNumber
                    ]
                },
                {
                    columnWidth: .33,
                    layout: 'form',
                    border: false,
                    autoScroll : true,
                    labelWidth: 160,
                    defaults: {
                        anchor: '97%'
                    },
                    autoHeight: true,
                    style: 'padding: 2px',
                    items: [
                        this.BladeLength_m,
                        this.BladePassFrequency,
                        this.BladeTipFrequency,
                        this.VanePassingFrequency,
                        this.BladeVanePassingFrequency,
                        this.Gear,
                        this.Geartype,
                        this.GearRatio,
                        this.GearNumberStages,
                        this.GearLowSpeedShaftTeethNumber,
                        this.GearFastSpeedShaftTeethNumber,
                        this.GearRingTeethNumber,
                        this.GearPlanetTeethNumber,
                        this.GearPlanetaryCarrierTeethNumber,
                        this.GearFixedComponent,
                        this.GearSunFrequency,
                        this.GearRingFrequency,
                        this.GearPlanetFrequency,
                        this.GearMeshFrequency,
                        this.GearTeethCommonFactor,
                        this.GearHuntingToothFrequency,
                        this.GearAssemblyPhase,
                        this.GearGhostFrequency,
                        this.Belt,
                        this.BeltDiameterD1_m,
                        this.BeltDiameterD2_m,
                        this.BeltAxialGap_m,
                        this.BeltTeethNumberZ1
                    ]
                },
                {
                    columnWidth: .34,
                    layout: 'form',
                    border: false,
                    autoScroll : true,
                    labelWidth: 160,
                    defaults: {
                        anchor: '97%'
                    },
                    autoHeight: true,
                    style: 'padding: 2px',
                    items: [
                        this.BeltTeethNumberZ2,
                        this.BeltLength_m,
                        this.BeltSpeedN1_rpm,
                        this.BeltSpeedN2_rpm,
                        this.BeltFrequency,
                        this.TimingBeltFrequency,
                        this.Motor_Generator,
                        this.MotorEfficiency,
                        this.MotorPolePairs,
                        this.MotorRotorBars,
                        this.MotorStatorPoles,
                        this.MotorStatorSlots,
                        this.MotorCoilsPerPole,
                        this.MotorLineOfFrequency,
                        this.MotorSynchronuousSpeedFrequency,
                        this.MotorRunningSpeedFrequency,
                        this.MotorSlipFrequency,
                        this.MotorSlipRatio,
                        this.MotorPolePassFrequency,
                        this.MotorSlotPassFrequency,
                        this.MotorRotorBarFrequency,
                        this.MotorStatorSlotFrequency,
                        this.MotorCommutatorFrequency,
                        this.MotorStaticEccentricityFrequency,
                        this.MotorDynamicEccentricity,
                        this.MotorStatorMechanicalDamageFrequency,
                        this.MotorRotorDefectFrequency,
                        this.MotorLooseStatorCoilFrequency
                    ]
                }
            ]
        });

        return this.pnlData;
    }
});

Toc.ComponentDialog = function (config) {
    config = config || {};

    config.id = 'component-dialog-win';
    config.title = 'New Component';
    config.layout = 'fit';
    config.width = 1000;
    config.height = 625;
    config.modal = true;
    config.iconCls = 'icon-component-win';
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

    Toc.ComponentDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.ComponentDialog, Ext.Window, {

    show: function () {
        if (!this.asset_id) {
            Ext.Msg.alert(TocLanguage.msgErrTitle, "Asset invalide !!!");
        }
        else {
            this.frmLine.form.reset();
            this.frmLine.form.baseParams['asset_id'] = this.asset_id;

            Toc.ComponentDialog.superclass.show.call(this);

            if (this.component_id) {
                this.loadComponent(this.pnlGeneral);
            }
            else
            {
                this.pnlGeneral.Geartype.getStore().load();
            }
        }
    },

    loadComponent: function (panel) {
        if (this.component_id) {
            this.frmLine.form.baseParams['component_id'] = this.component_id;
            if (panel) {
                panel.getEl().mask('Chargement Component en cours....');
            }
            this.frmLine.load({
                    url: Toc.CONF.CONN_URL,
                    params: {
                        action: 'load_component'
                    },
                    success: function (form, action) {
                        if (panel) {
                            this.pnlGeneral.Geartype.getStore().on('load', function () {
                                this.pnlGeneral.Geartype.setValue(action.result.data.geartype);
                            }, this);
                            this.pnlGeneral.Geartype.getStore().load();

                            this.pnlPermissions = new Toc.content.PermissionsPanel({content_id: this.component_id, content_type: 'component', owner: this.owner});
                            this.pnlDocuments = new Toc.content.DocumentsPanel({content_id: this.component_id, content_type: 'component', owner: Toc.content.ContentManager});
                            this.tablayout.add(this.pnlDocuments);
                            this.tablayout.add(this.pnlPermissions);

                            panel.getEl().unmask();
                        }
                    },
                    failure: function (form, action) {
                        Ext.Msg.alert(TocLanguage.msgErrTitle, action.result.feedback);
                        if (panel) {

                            panel.getEl().unmask();
                        }

                        this.close();
                    },
                    scope: this
                },
                this
            );
        }
        else {
            Ext.Msg.alert(TocLanguage.msgErrTitle, "Aucun Component selectionnée");
        }
    },

    buildForm: function () {
        this.pnlGeneral = new Toc.ComponentPanel({component_id: this.component_id});

        this.tablayout = new Ext.TabPanel({
            activeTab: 0,
            defaults: {
                hideMode: 'offsets'
            },
            deferredRender: false,
            items: [this.pnlGeneral]
        });

        this.frmLine = new Ext.form.FormPanel({
            id: 'form-layout',
            layout: 'fit',
            fileUpload: true,
            labelWidth: 120,
            url: Toc.CONF.CONN_URL,
            baseParams: {
                module: 'categories',
                action: 'save_component'
            },
            scope: this,
            items: this.tablayout
        });

        return this.frmLine;
    },

    submitForm: function () {
        this.frmLine.form.submit({
            waitMsg: TocLanguage.formSubmitWaitMsg,
            success: function (form, action) {
                if (action.result.success) {
                    this.fireEvent('saveSuccess', action.result.feedback);
                    this.close();
                }
                else {
                    Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
                }
            },
            failure: function (form, action) {
                if (action.failureType != 'client') {
                    Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
                }
            },
            scope: this
        });
    }
});