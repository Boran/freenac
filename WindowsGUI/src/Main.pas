(**
 * Long description for file:
 * Windows GUI for Freenac - main module
 *
 * FUNCTION:
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation.
 *
 * @package		FreeNAC
 * @author		Sean Boran (FreeNAC Core Team)
 * @copyright		2009 FreeNAC
 * @license		http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 3
 * @version		SVN: $Id$
 * @link		http://www.freenac.net
 *
*)
unit Main;

interface

uses
  SysUtils, Windows, Messages, Classes, Graphics, Controls,
  Forms, Dialogs, StdCtrls, Buttons, ExtCtrls, Menus, ComCtrls,
  DASQLMonitor, MySQLMonitor, DB, MemDS, DBAccess, MyAccess, Mask, DBCtrls,
  dbcgrids, Grids, DBGrids, CRGrid, XPMan, XMLdoc, XMLIntf, StrUtils, Variants,
  InvokeRegistry, Rio, SOAPHTTPClient, DCPcrypt2, DCPblockciphers,
  DCPrijndael, DCPsha1, OleServer, Excel97, BaseGrid, AdvGrid, DBAdvGrd,
  Provider, DBClient, MSAccess, cxGraphics, cxControls, cxContainer,
  cxEdit, cxTextEdit, cxMaskEdit, cxDropDownEdit, cxLookupEdit,
  cxDBLookupEdit, cxDBLookupComboBox, cxStyles, cxCustomData, cxFilter,
  cxData, cxDataStorage, cxDBData, cxGridCustomTableView, cxGridTableView,
  cxGridDBTableView, cxGridLevel, cxClasses, cxGridCustomView, cxGrid,
  cxNavigator, cxDBNavigator, cxSpinEdit, cxGridCardView,
  cxGridCustomPopupMenu, cxGridPopupMenu, cxPC, cxButtonEdit, cxCheckBox,
  OleCtrls, SHDocVw, cxLabel, cxGroupBox, jpeg, MyNetWorks, MyCall,
  cxRadioGroup, cxDBEdit, cxCalendar, cxLookAndFeelPainters, cxButtons,
  cxCheckGroup, cxColorComboBox, cxDBEditRepository, cxPropertiesStore,
  cxEditRepositoryItems, dxPSGlbl, dxPSUtl, dxPSEngn, dxPrnPg, dxBkgnd,
  dxWrap, dxPrnDev, dxPSCompsProvider, dxPSFillPatterns, dxPSEdgePatterns,
  dxPSCore, dxPSContainerLnk, dxPScxCommon, dxPScxGrid6Lnk,
  Registry;

type
  TfmInventory = class(TForm)
    MainMenu: TMainMenu;
    FilePrintItem: TMenuItem;
    FilePrintSetupItem: TMenuItem;
    FileExitItem: TMenuItem;
    EditUndoItem: TMenuItem;
    EditCutItem: TMenuItem;
    EditCopyItem: TMenuItem;
    EditPasteItem: TMenuItem;
    HelpContentsItem: TMenuItem;
    HelpSearchItem: TMenuItem;
    HelpHowToUseItem: TMenuItem;
    HelpAboutItem: TMenuItem;
    StatusLine: TStatusBar;
    OpenDialog: TOpenDialog;
    SaveDialog: TSaveDialog;
    PrintDialog: TPrintDialog;
    PrintSetupDialog: TPrinterSetupDialog;
    Panel1: TPanel;
    XPManifest1: TXPManifest;
    Page: TPageControl;
    tsMonitoring: TTabSheet;
    tsEdit: TTabSheet;
    tsHistory: TTabSheet;
    Panel4: TPanel;
    Panel5: TPanel;
    deMachine: TDBEdit;
    Label2: TLabel;
    deMac: TDBEdit;
    lVlan: TLabel;
    Label6: TLabel;
    deComment: TDBEdit;
    NavEdit1: TDBNavigator;
    NavEdit2: TDBNavigator;
    bbConnect: TButton;
    bbDisconnect: TButton;
    Label10: TLabel;
    tsVmpslog: TTabSheet;
    Panel10: TPanel;
    Panel11: TPanel;
    Panel12: TPanel;
    Panel13: TPanel;
    gridMonitoring: TCRDBGrid;
    Button1: TButton;
    DBNavigator9: TDBNavigator;
    gbUser: TGroupBox;
    deVorname: TDBText;
    DBText1: TDBText;
    Label12: TLabel;
    Label13: TLabel;
    deTelMob: TDBText;
    deTel: TDBText;
    DBText8: TDBText;
    deName: TDBText;
    deLoc1: TDBText;
    Label7: TLabel;
    DBEdit7: TDBEdit;
    deLastSeenDirex: TDBText;
    lDns: TLabel;
    Label15: TLabel;
    lMacVendor: TLabel;
    lVlanColour: TLabel;
    XLApp: TExcelApplication;
    Label18: TLabel;
    bbPrevious500: TButton;
    bbNext500: TButton;
    lHistoryOffset: TLabel;
    Label29: TLabel;
    bbServerLogPrev: TButton;
    bbServerLogNext: TButton;
    lServerLogOffset: TLabel;
    dbCategory: TDBLookupComboBox;
    Label33: TLabel;
    Timer1: TTimer;
    Label35: TLabel;
    DBEdit1: TDBEdit;
    deMachineIP: TDBText;
    lDns2: TLabel;
    SpeedButton1: TSpeedButton;
    Encryptuser1: TMenuItem;
    page2: TPageControl;
    p2History: TTabSheet;
    deMemo: TDBMemo;
    p2Updates: TTabSheet;
    Label4: TLabel;
    DBText19: TDBText;
    DBText20: TDBText;
    p2LastSeen: TTabSheet;
    Label8: TLabel;
    Label14: TLabel;
    DBText5: TDBText;
    DBText4: TDBText;
    DBText14: TDBText;
    bbRestartPort: TButton;
    DBText13: TDBText;
    dePatchCableDetails: TDBText;
    Label17: TLabel;
    DBText18: TDBText;
    p2OperatingSystem: TTabSheet;
    DBLookupComboBox1: TDBLookupComboBox;
    Label16: TLabel;
    llepo: TLabel;
    Label38: TLabel;
    lnmap: TLabel;
    p2Inventory: TTabSheet;
    cbClass2: TDBLookupComboBox;
    lClassification: TLabel;
    p2Nmap: TTabSheet;
    lepo: TLabel;
    Label40: TLabel;
    lPatchCable: TLabel;
    DBText7: TDBText;
    Label42: TLabel;
    meNmap: TMemo;
    meInventory: TMemo;
    bbNmap: TButton;
    bbInv: TButton;
    p2AntiVirus: TTabSheet;
    Label43: TLabel;
    lav1: TLabel;
    lav2: TLabel;
    lav3: TLabel;
    rgStatus: TDBRadioGroup;
    tsQuery2: TTabSheet;
    cxUsers: TcxDBLookupComboBox;
    gridServerLog2: TcxGrid;
    gridServerLog2DBTableView1: TcxGridDBTableView;
    gridServerLog2DBTableView1datetime: TcxGridDBColumn;
    gridServerLog2DBTableView1priority: TcxGridDBColumn;
    gridServerLog: TcxGridDBColumn;
    gridServerLog2Level1: TcxGridLevel;
    gridChangeLog: TcxGrid;
    gridChangeLogDBTableView1: TcxGridDBTableView;
    gridChangeLogDBTableView1host: TcxGridDBColumn;
    gridChangeLogDBTableView1priority: TcxGridDBColumn;
    gridChangeLogDBTableView1WhoLookup: TcxGridDBColumn;
    gridChangeLogDBTableView1what: TcxGridDBColumn;
    gridChangeLogLevel1: TcxGridLevel;
    navServerLog1: TcxDBNavigator;
    navChangeLog1: TcxDBNavigator;
    UsersInOffice: TLabel;
    bbCopyDnsToNname: TSpeedButton;
    tsSwitches2: TTabSheet;
    Panel19: TPanel;
    navSwitch2: TDBNavigator;
    Panel20: TPanel;
    gridSwitches: TcxGrid;
    cxGridDBTableView1: TcxGridDBTableView;
    gridSwitches1: TcxGridLevel;
    tsPorts2: TTabSheet;
    Panel6: TPanel;
    Panel7: TPanel;
    DBNavigator8: TDBNavigator;
    DBNavigator10: TDBNavigator;
    gridPortsDBTableView1: TcxGridDBTableView;
    gridPortsLevel1: TcxGridLevel;
    gridPorts: TcxGrid;
    gridPortsDBTableView1SwitchName: TcxGridDBColumn;
    gridPortsDBTableView1PatchOfficeList: TcxGridDBColumn;
    gridPortsDBTableView1comment: TcxGridDBColumn;
    gridPortsDBTableView1default_vlan: TcxGridDBColumn;
    gridPortsDBTableView1name: TcxGridDBColumn;
    cxGridDBTableView1id: TcxGridDBColumn;
    cxGridDBTableView1ip: TcxGridDBColumn;
    cxGridDBTableView1name: TcxGridDBColumn;
    cxGridDBTableView1location: TcxGridDBColumn;
    cxGridDBTableView1swgroup: TcxGridDBColumn;
    cxGridDBTableView1notify: TcxGridDBColumn;
    cxGridDBTableView1Comment: TcxGridDBColumn;
    gridPortsDBTableView1LastActivity: TcxGridDBColumn;
    tsPatchCables2: TTabSheet;
    Panel8: TPanel;
    navPatches: TDBNavigator;
    Button3: TButton;
    ComboBox1: TComboBox;
    Panel9: TPanel;
    gridPatchesDBTableView1: TcxGridDBTableView;
    gridPatchesLevel1: TcxGridLevel;
    gridPatches: TcxGrid;
    gridPatchesDBTableView1rack: TcxGridDBColumn;
    gridPatchesDBTableView1rack_location: TcxGridDBColumn;
    gridPatchesDBTableView1outlet: TcxGridDBColumn;
    gridPatchesDBTableView1office: TcxGridDBColumn;
    gridPatchesDBTableView1type: TcxGridDBColumn;
    gridPatchesDBTableView1switchport: TcxGridDBColumn;
    gridPatchesDBTableView1rack_outlet: TcxGridDBColumn;
    gridPatchesDBTableView1comment: TcxGridDBColumn;
    gridPatchesDBTableView1lastchange: TcxGridDBColumn;
    gridPatchesDBTableView1ChangedBy: TcxGridDBColumn;
    gridPatchesDBTableView1expiry: TcxGridDBColumn;
    gridPatchesDBTableView1UsersInOffice: TcxGridDBColumn;
    DBText2: TDBText;
    gridPatchesDBTableView1OfficeIndex: TcxGridDBColumn;
    gridPatchesDBTableView1TypeIndex: TcxGridDBColumn;
    DBLookupComboBox2: TDBLookupComboBox;
    DBLookupComboBox3: TDBLookupComboBox;
    DBLookupComboBox4: TDBLookupComboBox;
    cxSystemsLocationCombo: TcxDBLookupComboBox;
    DBText9: TDBText;
    gridChangeLogDBTableView1Column1: TcxGridDBColumn;
    DBEdit2: TDBEdit;
    popupPorts: TPopupMenu;
    ShowPatchCable: TMenuItem;
    deLastUpdatedUsername: TDBText;
    SaveDialog1: TSaveDialog;
    cbVlanLookup: TcxDBLookupComboBox;
    cxGridDBTableView1Firmware: TcxGridDBColumn;
    cxGridDBTableView1Hw: TcxGridDBColumn;
    cxGridDBTableView1Column1: TcxGridDBColumn;
    tsWelcome: TTabSheet;
    cxGroupBox1: TcxGroupBox;
    cxLabel1: TcxLabel;
    Image1: TImage;
    cxLabel2: TcxLabel;
    cxLabel3: TcxLabel;
    cbShowUsers: TCheckBox;
    Label30: TLabel;
    cxDBDateEdit1: TcxDBDateEdit;
    patop: TPanel;
    cxTabControl1: TcxTabControl;
    gridQuery2: TcxGrid;
    gridQuery2DBTableView1: TcxGridDBTableView;
    gridQuery2Level1: TcxGridLevel;
    pamiddle: TPanel;
    Q2ExpiredUsers: TButton;
    bbExpiredSysetms: TButton;
    bbUnusedSystems: TButton;
    bbReportAV1: TButton;
    bbAllSystems: TButton;
    bbDNSnaming: TButton;
    DBText6: TDBText;
    lVlanDesc: TDBText;
    DBText10: TDBText;
    DBText11: TDBText;
    bbExportReport: TcxButton;
    bbExportServerLog: TcxButton;
    bbExportChangeLog: TcxButton;
    bbExportPorts: TcxButton;
    bbExportSwitches: TcxButton;
    bbExportPatchCables: TcxButton;
    cbShowPatchDetails: TCheckBox;
    gridPortsDBTableView1VlanAuth: TcxGridDBColumn;
    gridPortsDBTableView1LastVlanName: TcxGridDBColumn;
    cxStyleRepository1: TcxStyleRepository;
    cxStyle1: TcxStyle;
    cxStyle2: TcxStyle;
    tsConfig: TTabSheet;
    paConfigTop: TPanel;
    pageConfig: TPageControl;
    tsConfig2: TTabSheet;
    cxConfigGrid: TcxGrid;
    cxGridConfigView: TcxGridDBTableView;
    cxGridConfigViewtype: TcxGridDBColumn;
    cxGridConfigViewname: TcxGridDBColumn;
    cxGridConfigViewvalue: TcxGridDBColumn;
    cxGridConfigViewcomment: TcxGridDBColumn;
    cxGridConfigViewLastChange: TcxGridDBColumn;
    cxGridConfigViewuser: TcxGridDBColumn;
    cxGridLevel8: TcxGridLevel;
    tsNmapSubnets: TTabSheet;
    cxGridNmap: TcxGrid;
    cxGridNmapView9: TcxGridDBTableView;
    cxGridLevel9: TcxGridLevel;
    cxGridNmapView9id: TcxGridDBColumn;
    cxGridNmapView9ip_address: TcxGridDBColumn;
    cxGridNmapView9ip_netmask: TcxGridDBColumn;
    cxGridNmapView9scan: TcxGridDBColumn;
    cxGridNmapView9dontscan: TcxGridDBColumn;
    tsLocations: TTabSheet;
    gridBuildings: TcxGrid;
    gridBuildingsDBTableView1: TcxGridDBTableView;
    gridBuildingsDBTableView1id: TcxGridDBColumn;
    gridBuildingsDBTableView1name: TcxGridDBColumn;
    gridBuildingsLevel1: TcxGridLevel;
    gridLocations: TcxGrid;
    gridLocationsDBTableView1: TcxGridDBTableView;
    gridLocationsDBTableView1LocationName: TcxGridDBColumn;
    gridLocationsDBTableView1BuildingName: TcxGridDBColumn;
    gridLocationsDBTableView1id: TcxGridDBColumn;
    gridLocationsCardView1: TcxGridCardView;
    gridLocationsCardView1Row1: TcxGridCardViewRow;
    gridLocationsCardView1Row2: TcxGridCardViewRow;
    gridLocationsLevel1: TcxGridLevel;
    Label5: TLabel;
    Label44: TLabel;
    DBNavigator5: TDBNavigator;
    DBNavigator4: TDBNavigator;
    tsDeviceTypes: TTabSheet;
    Label31: TLabel;
    Label32: TLabel;
    gridSys_os2: TcxGrid;
    cxGridDBTableView3: TcxGridDBTableView;
    cxGridDBColumn1: TcxGridDBColumn;
    cxGridDBColumn2: TcxGridDBColumn;
    cxGridLevel3: TcxGridLevel;
    gridSys_class: TcxGrid;
    cxGridDBTableView2: TcxGridDBTableView;
    cxGridDBTableView2id: TcxGridDBColumn;
    cxGridDBTableView2value: TcxGridDBColumn;
    cxGridLevel2: TcxGridLevel;
    DBNavigator11: TDBNavigator;
    DBNavigator12: TDBNavigator;
    tsOperatingSystem: TTabSheet;
    Label11: TLabel;
    Label45: TLabel;
    Label46: TLabel;
    Label47: TLabel;
    gridOS3: TcxGrid;
    cxGridDBTableView7: TcxGridDBTableView;
    cxGridDBColumn7: TcxGridDBColumn;
    cxGridDBColumn8: TcxGridDBColumn;
    cxGridLevel7: TcxGridLevel;
    gridOS2: TcxGrid;
    cxGridDBTableView6: TcxGridDBTableView;
    cxGridDBColumn5: TcxGridDBColumn;
    cxGridDBColumn6: TcxGridDBColumn;
    cxGridLevel6: TcxGridLevel;
    gridOs1: TcxGrid;
    cxGridDBTableView5: TcxGridDBTableView;
    cxGridDBTableView5id: TcxGridDBColumn;
    cxGridDBTableView5value: TcxGridDBColumn;
    cxGridLevel5: TcxGridLevel;
    gridOS: TcxGrid;
    cxGridDBTableView4: TcxGridDBTableView;
    cxGridDBTableView4id: TcxGridDBColumn;
    cxGridDBTableView4value: TcxGridDBColumn;
    cxGridLevel4: TcxGridLevel;
    navOS: TDBNavigator;
    navOS1: TDBNavigator;
    navOS2: TDBNavigator;
    navOS3: TDBNavigator;
    tsVlans: TTabSheet;
    Panel15: TPanel;
    Panel14: TPanel;
    gridVlan1: TcxGrid;
    gridVlan1View1: TcxGridDBTableView;
    gridVlan1View1default_name: TcxGridDBColumn;
    gridVlan1View1vlan_group: TcxGridDBColumn;
    gridVlan1View1vlan_description: TcxGridDBColumn;
    gridVlan1View1default_id: TcxGridDBColumn;
    gridVlan1View1id: TcxGridDBColumn;
    gridVlan1Level1: TcxGridLevel;
    gridVlan2: TcxGrid;
    gridVlan2DBTableView1: TcxGridDBTableView;
    gridVlan2DBTableView1SwitchLookup: TcxGridDBColumn;
    gridVlan2DBTableView1DefVlanLookup: TcxGridDBColumn;
    gridVlan2DBTableView1vlan_name: TcxGridDBColumn;
    gridVlan2DBTableView1vid: TcxGridDBColumn;
    gridVlan2Level1: TcxGridLevel;
    Label48: TLabel;
    NavVlans: TDBNavigator;
    bbVlanEx: TcxButton;
    Label49: TLabel;
    navVlanswitch: TDBNavigator;
    tsUsers2: TTabSheet;
    gbUsers: TGroupBox;
    Label19: TLabel;
    Label21: TLabel;
    Label20: TLabel;
    Label22: TLabel;
    Label23: TLabel;
    Label24: TLabel;
    Label25: TLabel;
    Label26: TLabel;
    Label27: TLabel;
    DBEdit9: TDBEdit;
    DBEdit10: TDBEdit;
    DBEdit11: TDBEdit;
    DBEdit13: TDBEdit;
    DBEdit12: TDBEdit;
    DBEdit14: TDBEdit;
    DBEdit15: TDBEdit;
    DBEdit16: TDBEdit;
    DBEdit17: TDBEdit;
    comboUserLocationLookup: TDBLookupComboBox;
    DBText21: TDBText;
    rgRights: TDBRadioGroup;
    Label3: TLabel;
    deGuiVlanRights: TDBEdit;
    cbManualSync: TDBCheckBox;
    gbUserQueries: TGroupBox;
    bbGetAdmins: TButton;
    meUserQuery: TMemo;
    bbUserSupers: TButton;
    bbUserReadonly: TButton;
    bbUserManualSync: TButton;
    bbExpiredUsers: TButton;
    Label34: TLabel;
    Label28: TLabel;
    DBEdit8: TDBEdit;
    DBNavigator6: TDBNavigator;
    DBNavigator7: TDBNavigator;
    p2Wsus: TTabSheet;
    lwsus: TLabel;
    llwsus: TLabel;
    meWsus: TMemo;
    bbReportWsus: TButton;
    lav4: TLabel;
    lav5: TLabel;
    lav6: TLabel;
    lav7: TLabel;
    lav0: TLabel;
    Label1: TLabel;
    Label9: TLabel;
    deMachineDNS: TDBText;
    Label36: TLabel;
    Label37: TLabel;
    lav8: TLabel;
    lwsus8: TLabel;
    cxGridDBTableView1last_monitored: TcxGridDBColumn;
    cxGridDBTableView1up: TcxGridDBColumn;
    gridPortsDBTableView1last_monitored: TcxGridDBColumn;
    gridPortsDBTableView1shutdown: TcxGridDBColumn;
    Label39: TLabel;
    DBText12: TDBText;
    tsDNS: TTabSheet;
    Label51: TLabel;
    Label52: TLabel;
    DBText15: TDBText;
    DBText16: TDBText;
    lepo_name: TLabel;
    lav9: TLabel;
    dedhcp_ip: TDBEdit;
    Label55: TLabel;
    DBEdit3: TDBEdit;
    Label54: TLabel;
    DBText22: TDBText;
    DBText23: TDBText;
    Label56: TLabel;
    DBEdit4: TDBEdit;
    gridPortsDBTableView1id: TcxGridDBColumn;
    gridPortsDBTableView1staticvlan: TcxGridDBColumn;
    gridPortsDBTableView1SetAuthProfile: TcxGridDBColumn;
    gridPortsDBTableView1switchip: TcxGridDBColumn;
    gridPortsDBTableView1restart_now: TcxGridDBColumn;
    Showenddevices1: TMenuItem;
    gridPortsDBTableView1Up1: TcxGridDBColumn;
    tsOverview2: TTabSheet;
    cxStyle3: TcxStyle;
    Panel16: TPanel;
    navSystems21: TDBNavigator;
    navSystems22: TDBNavigator;
    bbExportExcelSystems: TcxButton;
    gridSystems2: TcxGrid;
    gridSystemsView1: TcxGridDBTableView;
    gridSystems2Level1: TcxGridLevel;
    gridSystemsView1statusname: TcxGridDBColumn;
    gridSystemsView1name: TcxGridDBColumn;
    gridSystemsView1comment: TcxGridDBColumn;
    gridSystemsView1mac: TcxGridDBColumn;
    gridSystemsView1PortLocation: TcxGridDBColumn;
    gridSystemsView1SwitchName: TcxGridDBColumn;
    gridSystemsView1LastVlan: TcxGridDBColumn;
    gridSystemsView1UserSurname: TcxGridDBColumn;
    gridSystemsView1UserForename: TcxGridDBColumn;
    gridSystemsView1LastSeen: TcxGridDBColumn;
    gridSystemsView1port: TcxGridDBColumn;
    gridSystemsView1r_ip: TcxGridDBColumn;
    gridSystemsView1r_timestamp: TcxGridDBColumn;
    gridSystemsView1inventory: TcxGridDBColumn;
    gridSystemsView1expiry: TcxGridDBColumn;
    gridSystemsView1SwitchComment: TcxGridDBColumn;
    gridSystemsView1vlan_description: TcxGridDBColumn;
    gridSystemsView1last_hostname: TcxGridDBColumn;
    gridSystemsView1HealthStatus: TcxGridDBColumn;
    sbAll2: TSpeedButton;
    gridSwitchesLevel1: TcxGridLevel;
    gridSwitchesPortsView1: TcxGridDBTableView;
    gridSwitchesPortsView1comment: TcxGridDBColumn;
    gridSwitchesPortsView1restart_now: TcxGridDBColumn;
    gridSwitchesPortsView1name: TcxGridDBColumn;
    gridSwitchesPortsView1LastVlanName: TcxGridDBColumn;
    gridSwitchesPortsView1last_activity: TcxGridDBColumn;
    gridSwitchesPortsView1VlanAuth: TcxGridDBColumn;
    gridSwitchesPortsView1def_lanLookup: TcxGridDBColumn;
    gridSwitchesPortsView1last_monitored: TcxGridDBColumn;
    gridSwitchesPortsView1up: TcxGridDBColumn;
    gridSwitchesPortsView1shutdown: TcxGridDBColumn;
    gridSwitchesPortsView1staticvlan: TcxGridDBColumn;
    gridSwitchesPortsView1auth_profile: TcxGridDBColumn;
    cxStyle4: TcxStyle;
    cxStyle5: TcxStyle;
    gridSystemsView1swgroup: TcxGridDBColumn;
    PopupOverview: TPopupMenu;
    MenuItem1: TMenuItem;
    cxEditRep1: TcxEditRepository;
    cxRep1LookupVlan1: TcxEditRepositoryLookupComboBoxItem;
    gridSwitchesPortsView1SwitchName: TcxGridDBColumn;
    tsServerControl: TTabSheet;
    cxGridRep1: TcxGridViewRepository;
    cxGridRep1TableView1: TcxGridTableView;
    cxPropertiesStore1: TcxPropertiesStore;
    cxGroupBox2: TcxGroupBox;
    bbRestartMaster: TcxButton;
    lav10: TLabel;
    sbUnknownsToday: TSpeedButton;
    cxGridDBTableView1vlan_id: TcxGridDBColumn;
    gridSystemsView1PortComment: TcxGridDBColumn;
    gridSystemsView1SwitchLocation: TcxGridDBColumn;
    gridSystemsView1r_ping_timestamp: TcxGridDBColumn;
    gridSystemsView1last_nbtname: TcxGridDBColumn;
    gridSystemsView1email_on_connect: TcxGridDBColumn;
    gridSystemsView1dhcp_fix: TcxGridDBColumn;
    gridSystemsView1dhcp_ip: TcxGridDBColumn;
    gridSystemsView1dns_alias: TcxGridDBColumn;
    gridSystemsView1PatchCable: TcxGridDBColumn;
    cxTextItem1: TcxEditRepositoryTextItem;
    cbOverviewPatchDetails: TCheckBox;
    dxComponentPrinter1: TdxComponentPrinter;
    OverviewReportLink: TdxCustomContainerReportLink;
    bbPrintOverview: TcxButton;
    gridSystemsView1Link: TdxGridReportLink;
    bbPrintPatches: TcxButton;
    dxPatchesLink1: TdxGridReportLink;
    cxdhcpfix: TcxDBCheckBox;
    gridSystemsView1ChangeDate: TcxGridDBColumn;
    gridSystemsView1ChangeUser: TcxGridDBColumn;
    gridSystemsView1building: TcxGridDBColumn;
    gridSystemsView1UserDept: TcxGridDBColumn;
    gridSystemsView1UserEmail: TcxGridDBColumn;
    gridSystemsView1UserHouse: TcxGridDBColumn;
    gridSystemsView1UserOffice: TcxGridDBColumn;
    gridSystemsView1UserMobileTel: TcxGridDBColumn;
    gridSystemsView1OsName: TcxGridDBColumn;
    gridSystemsView1ClassName: TcxGridDBColumn;
    gridSystemsView1Class2Name: TcxGridDBColumn;
    gridSystemsView1os4: TcxGridDBColumn;
    gridSystemsView1os1name: TcxGridDBColumn;
    gridSystemsView1os2name: TcxGridDBColumn;
    gridSystemsView1os3name: TcxGridDBColumn;
    gridSystemsView1user_name: TcxGridDBColumn;
    gridSystemsView1ChangeUserFullName: TcxGridDBColumn;
    cxLabel4: TcxLabel;
    cxLabel5: TcxLabel;
    bbConnect2: TButton;
    Label41: TLabel;
    GroupBox1: TGroupBox;
    deUserSearch: TEdit;
    bbUserSearch: TButton;
    Label50: TLabel;
    deNameSearch: TEdit;
    bbNamerSearch: TButton;
    Label53: TLabel;
    cxGridDBTableView1scan3: TcxGridDBColumn;
    bbHubs: TButton;
    LoadGridLayout1: TMenuItem;
    SaveGridLayout1: TMenuItem;
    bbHelpdesk: TButton;
    cxGridDBTableView1switch_type: TcxGridDBColumn;
    gridSystemsView1Vlanname: TcxGridDBColumn;  { &Contents }
    procedure LoadGridLayouts(file1: string);
    procedure FormCreate(Sender: TObject);
    procedure ShowHint(Sender: TObject);
    procedure FileNew(Sender: TObject);
    procedure FileOpen(Sender: TObject);
    procedure FileSave(Sender: TObject);
    procedure FileSaveAs(Sender: TObject);
    procedure FilePrint(Sender: TObject);
    procedure FilePrintSetup(Sender: TObject);
    procedure FileExit(Sender: TObject);
    procedure EditUndo(Sender: TObject);
    procedure EditCut(Sender: TObject);
    procedure EditCopy(Sender: TObject);
    procedure EditPaste(Sender: TObject);
    procedure HelpContents(Sender: TObject);
    procedure HelpSearch(Sender: TObject);
    procedure HelpHowToUse(Sender: TObject);
    procedure HelpAbout(Sender: TObject);
    procedure bbConnectClick(Sender: TObject);
    procedure bbReload2Click(Sender: TObject);
    procedure navPatchesBeforeAction(Sender: TObject;
      Button: TNavigateBtn);
    procedure bbDisconnectClick(Sender: TObject);
    procedure bbUpdateClick(Sender: TObject);
    procedure gridSystemsDblClick(Sender: TObject);
    procedure gridSystemsCellClick(Column: TColumn);
    procedure tsEditShow(Sender: TObject);
    procedure deMachineChange(Sender: TObject);
    procedure PageChange(Sender: TObject);
    procedure NavEdit1Click(Sender: TObject; Button: TNavigateBtn);
    procedure gridPortsDblClick(Sender: TObject);
    procedure gridSwitchesDblClick(Sender: TObject);
    procedure gbUserClick(Sender: TObject);
    procedure bbClearFIlter1Click(Sender: TObject);
    procedure taPortOfficeLookupGetText(Sender: TField; var Text: String;
      DisplayText: Boolean);
    procedure gridCableGetCellParams(Sender: TObject; Field: TField;
      AFont: TFont; var Background: TColor; State: TGridDrawState;
      StateEx: TGridDrawStateEx);
    procedure gridSystemsGetCellParams(Sender: TObject; Field: TField;
      AFont: TFont; var Background: TColor; State: TGridDrawState;
      StateEx: TGridDrawStateEx);
    procedure taPortPatchLookupGetText(Sender: TField; var Text: String;
      DisplayText: Boolean);
    procedure taCableUserFromDirexGetText(Sender: TField; var Text: String;
      DisplayText: Boolean);
    procedure bbPatchToExcelClick(Sender: TObject);
    procedure cbVlanCloseUp(Sender: TObject);
    procedure cbStatusCloseUp(Sender: TObject);
    procedure cbByuserChange(Sender: TObject);
    procedure bbByLastHourClick(Sender: TObject);
    procedure bbPrevious500Click(Sender: TObject);
    procedure bbNext500Click(Sender: TObject);
    procedure bbUserSearchClick(Sender: TObject);
    procedure bbNamerSearchClick(Sender: TObject);
    procedure bbGetAdminsClick(Sender: TObject);
    procedure bbUserSupersClick(Sender: TObject);
    procedure bbUserReadonlyClick(Sender: TObject);
    procedure bbUserManualSyncClick(Sender: TObject);
    procedure bbServerLogPrevClick(Sender: TObject);
    procedure bbServerLogNextClick(Sender: TObject);
    procedure bbUnknownClick(Sender: TObject);
    procedure bbByTodayClick(Sender: TObject);
    procedure bbExpiredUsersClick(Sender: TObject);
    procedure bbRestartPortClick(Sender: TObject);
    procedure Timer1Timer(Sender: TObject);
    procedure bbExpiredSystemsClick(Sender: TObject);
    procedure bbReloadClick(Sender: TObject);
    procedure SpeedButton1Click(Sender: TObject);
    procedure lDns2Click(Sender: TObject);
    procedure Encryptuser1Click(Sender: TObject);
    function  enc(const str: String): string;
    procedure page2Change(Sender: TObject);
    procedure update_queries;
    procedure p2OperatingSystemShow(Sender: TObject);
    procedure p2NmapShow(Sender: TObject);
    procedure gridOverview3DblClick(Sender: TObject);
    procedure bbNmapClick(Sender: TObject);
    procedure bbInvClick(Sender: TObject);
    procedure NavEdit2Click(Sender: TObject; Button: TNavigateBtn);
    procedure cbStatus_enabledClick(Sender: TObject);
    procedure deUserLookupCloseUp(Sender: TObject);
    procedure bbExpiredUsersOnlyClick(Sender: TObject);
    procedure bbOldSystemsClick(Sender: TObject);
    procedure bbExportSystemsToExcelClick(Sender: TObject);
    procedure bbAllSystemsQueryClick(Sender: TObject);
    procedure cbOnChange(Sender: TObject);
    procedure bbClearPortFilterClick(Sender: TObject);
    procedure p2LastSeenShow(Sender: TObject);
    procedure gridPatchesDBTableView1UsersInOfficeGetDataText(
      Sender: TcxCustomGridTableItem; ARecordIndex: Integer;
      var AText: String);
    procedure gridPatchesDBTableView1CustomDrawCell(
      Sender: TcxCustomGridTableView; ACanvas: TcxCanvas;
      AViewInfo: TcxGridTableDataCellViewInfo; var ADone: Boolean);
    procedure Button4Click(Sender: TObject);
    procedure Button5Click(Sender: TObject);
    procedure Button2Click(Sender: TObject);
    procedure Q2ExpiredUsersClick(Sender: TObject);
    procedure Button7Click(Sender: TObject);
    procedure gridPortsDBTableView1PatchOfficeListGetDataText(
      Sender: TcxCustomGridTableItem; ARecordIndex: Integer;
      var AText: String);
    procedure cxUsersPropertiesChange(Sender: TObject);
    procedure bbExpiredSysetmsClick(Sender: TObject);
    procedure bbUnusedSystemsClick(Sender: TObject);
    procedure bbReportAV1Click(Sender: TObject);
    procedure bbAllSystemsClick(Sender: TObject);
    procedure gridPortsDBTableView1CellDblClick(
      Sender: TcxCustomGridTableView;
      ACellViewInfo: TcxGridTableDataCellViewInfo; AButton: TMouseButton;
      AShift: TShiftState; var AHandled: Boolean);
    procedure cxGridPopupMenu1PopupMenus0Popup(ASenderMenu: TComponent;
      AHitTest: TcxCustomGridHitTest; X, Y: Integer);
    procedure popupPortsPopup(Sender: TObject);
    procedure popupPortsChange(Sender: TObject; Source: TMenuItem;
      Rebuild: Boolean);
    procedure ShowPatchCableClick(Sender: TObject);
    procedure cxLabel1Click(Sender: TObject);
    procedure cxLabel2Click(Sender: TObject);
    procedure cxLabel3Click(Sender: TObject);
    procedure bbDNSnamingClick(Sender: TObject);
    procedure bbExportVlansToExcelClick(Sender: TObject);
    procedure bbVlanExClick(Sender: TObject);
    procedure bbExportReportClick(Sender: TObject);
    procedure bbExportServerLogClick(Sender: TObject);
    procedure bbExportChangeLogClick(Sender: TObject);
    procedure bbExportPortsClick(Sender: TObject);
    procedure bbExportSwitchesClick(Sender: TObject);
    procedure bbExportPatchCablesClick(Sender: TObject);
    procedure tsNmapSubnetsShow(Sender: TObject);
    procedure p2WsusShow(Sender: TObject);
    procedure bbReportWsusClick(Sender: TObject);
    procedure gridPortsDBTableView1SetAuthProfilePropertiesChange(
      Sender: TObject);
    procedure Showenddevices1Click(Sender: TObject);
    procedure gridPortsDBTableView1upGetDataText(
      Sender: TcxCustomGridTableItem; ARecordIndex: Integer;
      var AText: String);
    procedure gridPortsDBTableView1Up1GetDataText(
      Sender: TcxCustomGridTableItem; ARecordIndex: Integer;
      var AText: String);
    procedure cxGridDBTableView1upGetDataText(
      Sender: TcxCustomGridTableItem; ARecordIndex: Integer;
      var AText: String);
    procedure bbExportExcelSystemsClick(Sender: TObject);
    procedure gridSwitchesPortsView1upGetDataText(
      Sender: TcxCustomGridTableItem; ARecordIndex: Integer;
      var AText: String);
    procedure MenuItem1Click(Sender: TObject);
    procedure sbAll2Click(Sender: TObject);
    procedure bbRestartMasterClick(Sender: TObject);
    procedure sbUnknownsTodayClick(Sender: TObject);
    procedure gridSystemsView1macGetPropertiesForEdit(
      Sender: TcxCustomGridTableItem; ARecord: TcxCustomGridRecord;
      var AProperties: TcxCustomEditProperties);
    procedure cbOverviewPatchDetailsClick(Sender: TObject);
    procedure bbPrintOverviewClick(Sender: TObject);
    procedure bbPrintPatchesClick(Sender: TObject);
    procedure cxSystemsLocationComboPropertiesChange(Sender: TObject);
    procedure cxLabel4Click(Sender: TObject);
    procedure cxLabel5Click(Sender: TObject);
    procedure Image1Click(Sender: TObject);
    procedure bbConnect2Click(Sender: TObject);
    procedure bbHubsClick(Sender: TObject);
    procedure SaveGridLayout1Click(Sender: TObject);
    procedure LoadGridLayout1Click(Sender: TObject);
    procedure bbHelpdeskClick(Sender: TObject);
    procedure FormClose(Sender: TObject; var Action: TCloseAction);
  private
    procedure DoColumnGetDisplayText1(Sender: TcxCustomGridTableItem;
       ARecordIndex: Integer; var AText: string);
    procedure DoColumnGetDisplayText2(Sender: TcxCustomGridTableItem;
       ARecordIndex: Integer; var AText: string);
    procedure PopulateColumnValues1(AColumn: TcxGridDBColumn);
    procedure PopulateColumnValues2(AColumn: TcxGridDBColumn);
  public
    procedure initialise;
    procedure Disconnect;

  end;

var
  fmInventory: TfmInventory;
  UserID: integer;
  Username, UserFullName, Hostname, MyDomain, Company: string;
  StaticInvEnabled, NmapEnabled, AntiVirusEnabled, PatchCableEnabled: boolean;   // Boolean. Features enabled?
  wsus_enabled, DemoMode, vlan_by_switch_location: boolean;
  promptforpassword, confirmpost, can_admin, can_readonly, can_edit, use_server2: boolean;
  rights, HistoryOffset: integer;
  dSettings:  TFormatSettings;
  Server2, gui_disable_ports_list: string;

implementation


uses fmAboutInvent, WinSock, DateUtils, fmKeygen, fmStaticInv, cxGridExportLink, cxExport, ShellAPI,
  dm;

{$r *.dfm}


// Win 32 API
function NetUserGetGroups( Srv: PWideChar; Usr: PWideChar; Level: dword; var Buffer: Pointer; PrefMaxLen: DWORD;
  var EntriesRead, TotalEntries ): Longint; stdcall; external 'netapi32.dll';
function NetApiBufferFree(pBuffer: PByte): Longint; stdcall; external 'netapi32.dll';


procedure OpenMyTable(Table: TMyTable); overload;
begin
  if (Table.State = dsInactive) then begin
    //debug1('OpenMyTable: ' +Table.TableName);
    Table.Open;
  end else begin
    //debug2('OpenMyTable: ' +Table.TableName +' already open.');
  end;
end;
procedure OpenMyTable(Query: TMyQuery);  overload;
begin
  // TBD: how do we know if a query is already open?
  //if (Query.State = dsInactive) then begin
  //  debug1('OpenMyTable: ' +Query.TableName);
    Query.Open;
  //end else begin
    //debug2('OpenMyTable: ' +Query.TableName +' already open.');
  //end;
end;


procedure PostMyTable(Table: TMyTable);  overload
begin
  if (Table.State = dsInactive) then begin
    //debug2('PostMyTable already closed so open: ' +Table.TableName);
    Table.Open;
  end else if (Table.State in [dsBrowse,dsSetKey]) then begin
    //debug2('PostMyTable, in dsBrowse, ignore: ' +Table.TableName);
  end else if (Table.State in [dsEdit,dsInsert]) then begin
    try
      //debug2('PostMyTable: ' +Table.TableName);
      //Table.Close;
      //debug2('PostMyTable Post ' +Table.TableName);
      Table.Post;
    except
      //log_it('PostMyTable cannot post ' +Table.TableName);
      //ShowMessage('PostMyTable cannot post ' +Table.TableName);
    end;

  end else begin
    //log_it('PostMyTable in unexpected state: ' +Table.TableName);
    //ShowMessage('PostMyTable in unexpected state: ' +Table.TableName);
  end;          
End;

procedure PostMyTable(Table: TMyQuery);  overload
begin
  if (Table.State = dsInactive) then begin
    //debug2('PostMyTable already closed so open: ' +Table.Name);
    Table.Open;
  end else if (Table.State in [dsBrowse,dsSetKey]) then begin
    //debug2('PostMyTable, in dsBrowse, ignore: ' +Table.Name);
  end else if (Table.State in [dsEdit,dsInsert]) then begin
    try
      //debug2('PostMyTable: ' +Table.Name);
      //Table.Close;
      //debug2('PostMyTable Post ' +Table.Name);
      Table.Post;
    except
      //log_it('PostMyTable cannot post ' +Table.Name);
      //ShowMessage('PostMyTable cannot post ' +Table.Name);
    end;

  end else begin
    //log_it('PostMyTable in unexpected state: ' +Table.Name);
    //ShowMessage('PostMyTable in unexpected state: ' +Table.Name);
  end;
End;


// get a DNS name, given the IP address: i.e. reverse lookup
function GetDNSAddr(IPAddr: string): string;
var
  SockAddrIn: TSockAddrIn;
  HostEnt: PHostEnt;
  WSAData: TWSAData;
begin
  //WSAStartup($101, WSAData);
  if ( WSAStartup(MAKEWORD(2,0),WSAData)=0 ) and (Length(IPAddr)>6) then begin
    SockAddrIn.sin_addr.s_addr:=inet_addr(PChar(IPAddr));
    HostEnt:= GetHostByAddr(@SockAddrIn.sin_addr.S_addr, 4, AF_INET);
    if HostEnt<>nil then begin
      Result:=(StrPas(Hostent^.h_name));
    end else begin
      Result:='';
    end;
  end;
end;

function GetDNSIP(IPAddress: String) :String;
var
 WSAData: TWSAData;
 Socket: TSockAddrIn;
begin
 ShowMessage('lookup DNS name for: ' +IPAddress);
 result:=''; // default is empty
 if ( WSAStartup(MAKEWORD(2,0),WSAData)=0 ) and (Length(IPAddress)>6) then begin
   Socket.sin_addr.S_addr := inet_addr(PChar(IPAddress));
   result:=StrPas(gethostbyAddr(@Socket.sin_addr.S_addr, SizeOf(Socket.sin_addr.S_addr), AF_INET)^.h_name);
   WSACleanup;
  end else begin
    Result:='';
 end;
end;

function GetDNSName(host :String): String;
 type PPInAddr= ^PInAddr;
var
 WSAData: TWSAData;
 Socket: TSockAddrIn;
 HostInfo : PHostEnt;
 Addr     : PPInAddr;
begin
 //ShowMessage('lookup DNS Ip for: ' +host);
 result:=''; // default is empty
 //result:='unknown'; // default is empty
 if ( WSAStartup(MAKEWORD(2,0),WSAData)=0 ) and (Length(host)>0) then begin
 try
    HostInfo:=gethostbyName(PChar(host));
    if HostInfo=nil then Exit;
    Addr:=Pointer(HostInfo^.h_addr_list);
    if (Addr=nil) or (Addr^=nil) then Exit;
    Result:=StrPas(inet_ntoa(Addr^^));
    inc(Addr);
    while Addr^ <> nil do begin
      Result:=Result+^M^J+StrPas(inet_ntoa(Addr^^));
      inc(Addr);
    end;
  finally
    WSACleanup;
    // Stop it returning localhost when unknown
    if Result='127.0.0.1' then Result:='';
  end;
 end;
end;




// update history
procedure update_history(msg: string);
begin
  with dm0.MyQuery1 do begin
    SQL.Clear;
    SQL.Add('INSERT into guilog (datetime,host,who,what) values (NOW(),:host,:who,:what)'  );
    ParamByName('host').AsString := hostname;
    ParamByName('who').AsString := IntToStr(UserId);
    ParamByName('what').AsString := 'WinGUI: ' +msg;
    //ShowMessage(SQL.Text);
    Execute;
  end;
  //fmInventory.taHistory.Refresh;

end;


procedure UpdateDNS;
var str: string;
begin
  // Update DNS label
  //

  with fmInventory do begin
  if Length(deMachine.Text)>1 then begin
    //ShowMessage('UpdateDNS ' +deMachine.Text);
    lDns.Caption:=GetDNSName(deMachine.Text);
  end else begin
    lDns.Caption:='';
  end;

  if Length(dm0.quSystems.FieldByName('r_ip').AsString)>1 then begin
    //ShowMessage('UpdateDNS ' +dm0.quSystems.FieldByName('r_ip').AsString);
    lDns2.Caption:=GetDNSAddr(dm0.quSystems.FieldByName('r_ip').AsString);
  end else begin
    lDns2.Caption:='';
  end;
  end;
end;

procedure UpdateMACLookup;
var str: string;
begin
  fmInventory.lMacVendor.Caption:='';
    if (Length(dm0.quSystems.FieldByName('mac').AsString) > 6) then
      str:=dm0.quSystems.FieldByName('mac').AsString[1]
      +dm0.quSystems.FieldByName('mac').AsString[2]
      +dm0.quSystems.FieldByName('mac').AsString[3]
      +dm0.quSystems.FieldByName('mac').AsString[4]
      +dm0.quSystems.FieldByName('mac').AsString[6]
      +dm0.quSystems.FieldByName('mac').AsString[7]
    else
      exit;

    //TBD: do a query
    OpenMyTable(dm0.taEthernet);
    if (dm0.taEthernet.Locate('mac', str, [loPartialKey,loCaseInsensitive])) then begin
      //ShowMessage('found mac');
      fmInventory.lMacVendor.Caption:=dm0.taEthernet.FieldByName('vendor').AsString;
    end;
end;


procedure TfmInventory.Disconnect;
begin
  Page.Enabled:=false;
  Page.Visible:=false;
  Timer1.Enabled:=false;        // we've lost connectivity, so avoid more popups

  dm0.MyConnection1.Disconnect;
  if not dm0.MyConnection1.Connected then begin
    fmInventory.bbConnect.Enabled:=true;
    fmInventory.bbConnect2.Enabled:=true;    
    fmInventory.bbDisconnect.Enabled:=false;
  end;
end;

procedure ROTable(Table: TMyTable);
begin
  //debug2('ROTable '+Table.Name);
  try
    //Table.Close;
    Table.ReadOnly:=True;
    //Table.UniDirectional:=False;
  finally
    OpenMyTable(Table);
  end;
end;

procedure RWTable(Table: TMyTable);
begin
  //debug2('RWTable '+Table.Name);
  try
    //Table.Close;
    Table.ReadOnly:=false;
    //Table.UniDirectional:=False;
  finally
    OpenMyTable(Table);
  end;
end;


function check_group( User: string; Srv: string='' ): TThreadLIst;   // User, server
var
   wc, wc1:                                 PWideChar;
   Buf:                                     Pointer;
   Ret:                                     dword;
   pGUI:                                    PGroupUserInfo1;
   pMyGUI:                                  PMyGroupUserInfo;
   EntriesRead, TotalEntries:               DWORD;
   tmpS:                                    string;
   mmm,i:                                   cardinal;
begin
   Result := TThreadList.Create; wc := nil; wc1 := nil;
   try
      if Srv <> '' then begin
         GetMem( wc, length(Srv)*2+1 );
         StringToWideChar(Srv, wc, length(Srv)*2+1);
      end;
      if User <> '' then begin
         GetMem( wc1, length(User)*2+1 );
         StringToWideChar(User, wc1, length(User)*2+1);
      end;

      GetMem( Buf, sizeof(TGroupUserInfo1)*100 );
      EntriesRead := 0; TotalEntries :=0;
      Ret := ERROR_MORE_DATA;
      while Ret = ERROR_MORE_DATA do begin
         Ret := NetUserGetGroups( wc, wc1, 1, Buf, sizeof(TGroupUserInfo1)*100, EntriesRead, TotalEntries );
         if ( (Ret = NERR_Success) or (Ret = ERROR_MORE_DATA) ) then begin
            pGUI := Buf;
            for i := 0 to  EntriesRead - 1 do begin
               GetMem( pMyGUI, sizeof(TMyGroupUserInfo) );

               tmpS := WideCharToString( pGUI^.grui1_name );
               if tmpS <> '' then begin
                  if length( tmpS ) > sizeof(pMyGUI^.Name) then
                     mmm := sizeof(pMyGUI^.Name)
                  else
                     mmm := length( tmpS );
                  pMyGUI^.Name := Copy( tmpS, 1, mmm );
               end;
               pMyGUI^.Attr := pGUI^.grui1_attributes;

               // Adding to list
               with Result.LockList do
                  try
                     Add( pMyGUI );
                  finally
                     Result.UnlockList;
                  end;
               inc( pGUI );
            end;
         end;
      end;
   finally
      if Assigned( Buf ) then
         NetApiBufferFree( Buf );
      if Assigned( wc ) then
         FreeMem( wc );
      if Assigned( wc1 ) then
         FreeMem( wc1 );
   end;
end;


function get_config1(varname:string): string;
begin
  result:='';
  with dm0.MyQuery1 do begin
    SQL.Clear;
    SQL.Add('SELECT value from config WHERE name=''' +varname +''' LIMIT 1');  // first entry only
    Open;
    while not EOF do begin
      if (Length(FieldByName('value').AsString) > 0) then begin
        result:=FieldByName('value').AsString;
      end;
      next;   // there should only be one
    end;

  //ShowMessage('get_config: ' +varname +'Result=' +result);
  end;
end;

function get_config_bool(varname:string): boolean;
begin
  result:=false;
  with dm0.MyQuery1 do begin
    SQL.Clear;
    SQL.Add('SELECT value from config WHERE name=''' +varname +''' LIMIT 1');  // first entry only
    Open;
    while not EOF do begin
      if (Length(FieldByName('value').AsString) > 0) then begin
        if (FieldByName('value').AsString='true') or (FieldByName('value').AsString='1') then begin
          result:=true;
        end;
      end;
      next;   // there should only be one
    end;

  //ShowMessage('get_config_bool: ' +varname +'Result=' +result);
  end;
end;

// get_config_bool2: as above but allow the configuration variable
//     to have two names for backward compatibility
function get_config_bool2(varname:string; varname2:string): boolean;
begin
  result:=false;      // default: setting not enabled, if not present
  with dm0.MyQuery1 do begin
    SQL.Clear;
    SQL.Add('SELECT value from config WHERE name=''' +varname +''' LIMIT 1');  // first entry only
    Open;
    while not EOF do begin
      if (Length(FieldByName('value').AsString) > 0) then begin
        if (FieldByName('value').AsString='true') or (FieldByName('value').AsString='1') then begin
          result:=true;
        end;
      end;
      next;   // there should only be one
    end;

    SQL.Clear;
    SQL.Add('SELECT value from config WHERE name=''' +varname2 +''' LIMIT 1');  // first entry only
    Open;
    while not EOF do begin
      if (Length(FieldByName('value').AsString) > 0) then begin
        if (FieldByName('value').AsString='true') or (FieldByName('value').AsString='1') then begin
          result:=true;
        end;
      end;
      next;   // there should only be one
    end;

  //ShowMessage('get_config_bool: ' +varname +'Result=' +result);
  end;
end;




procedure   parse_guivlanrights(str: string);
var
  i, PosComma: integer;  // count of fields, comma position
  field, sql: string;
  found: boolean;
  //fields: array [1..100] of integer;
begin
  sql:='SELECT vlan_description,default_name,default_id,id FROM vlan ';
  found:=false;

  // get rid of blanks
  str := StringReplace(str, ' ', '', [rfReplaceAll]);
  i := 0;    // first column of string grid
  repeat
    PosComma := Pos(',', str);
    if PosComma > 0 then       // is there another field?
      field := Copy(str, 1, PosComma - 1)
    else
      field := str;    // only one field left

    // We have a field now, so build the SQL
    //
    if (Length(field)>0) then begin
      //ShowMessage('Vlan=' +field);
      //fields[i] := StrToInt(field);
      //ShowMessage('found vlan field=' +field +', number=' +IntTostr(fields[i]));
      if (i=0) then
        sql:=sql +' WHERE id=' +field     // first field
      else
        sql:=sql +' OR id=' +field;
    end;
    if PosComma > 0 then begin
      Delete(str, 1, PosComma);  // remove the field we read
      i := i + 1;   // next column
    end;
  until PosComma = 0;   // next field

  sql:=sql +' ORDER BY vlan_description';
  // now set the SQL for the list of Vlans in the edit tab.
  dm0.quVlanList.SQL.Clear;
  dm0.quVlanList.SQL.Add(sql);
  //ShowMessage(sql);
end;


function check_rights: boolean;
var
  ServerDomain,GuiVlanRights : string;
  allow_exceptions, verify_domain: boolean;

begin
  result:=true;
  allow_exceptions:=false;   // production
  //allow_exceptions:=true;    // test: Exceptions for those not in the domain, and problem users
  //verify_domain:=false;      // test: Should the Domain membership be verified?  How can we verify membership or more than one domain?
  verify_domain:=true;      // Should the Domain membership be verified?  How can we verify membership or more than one domain?

  if verify_domain then ServerDomain:=get_config1('guidomain');
  DemoMode    :=get_config_bool('DemoMode');

  with dm0.MyQuery1 do begin
    SQL.Clear;
    SQL.Add('SELECT id,username,GivenName,Surname,GuiVlanRights,nac_rights FROM users WHERE username='''
      +username +'''');
    Open;
    UserID:=FieldByName('id').AsInteger;    // lookup & store the UID
    UserFullName:=FieldByName('GivenName').AsString +' ' +FieldByName('Surname').AsString  +'(' +FieldByName('username').AsString +')';
    GuiVlanRights:=FieldByName('GuiVlanRights').AsString;    // lookup & store

    if ((hostname='INOSSMSEANLAP') or (hostname='INOSSMBORAN10') or (hostname='INOTGDBOSE1')
        or (hostname='INOTGDBOSE1-02'))
        and (username='BORAN') and allow_exceptions then begin
        //can_admin:=true;
        //can_edit:=true;               // test super-user rights
        can_readonly:=true;           // test read-only rights

    // ____ Demo: allow admins access if DemoMode is set in the config table _____
    end else if ((Company='DEMO') and (DemoMode)) then begin
        can_admin:=true;
    end else if ((Company='DEMO') and (not DemoMode)) then begin
        ShowMessage('Access Denied: The company is set to DEMO in vmps.xml, but DemoMode is not enabled in the config table.');
        result:=false;  // exit

      // Alois Suter
    end else if (Company='INO') and (hostname='SISTGDSUAL1-L1') and (username='ALOIS') and allow_exceptions then begin
        can_admin:=true;
    end else if (Company='INO') and (hostname='SISTGDSTWO1-L1') and (username='TGDSTWO1')  then begin
        can_admin:=true;

      // Accarda  /Bertsch
    //end else if (Company='BRU') and (hostname='COC001') and (username='UNKNOWN') and allow_exceptions then begin
    //    can_admin:=true;


    end else begin   // Normal situations

      //if ( (ServerDomain<>MyDomain) and verify_domain and (Length(ServerDomain)>0) ) then begin
      if ( (UpperCase(ServerDomain)<>UpperCase(MyDomain)) and verify_domain and (Length(ServerDomain)>0) ) then begin
        // a domain is configured on the server side, and it is not the same
        ShowMessage('Username ' +username +' on workstation ' +hostname
          +' is not authorised to use NAC, the Windows Domain is <' +MyDomain
          +'> and must be <' +ServerDomain +'>');
        result:=false;

      end else begin     // the domain is OK

        // What user-groups are we in?         //MyNet.GetGroupUserInfo
        // DOES NOT YET WORK!!
        // TBD: read ad groups from guirights table
        (*ShowMessage('List AD groups?');
        d:=check_group(Username);
           if Assigned( d ) then
              with d.LockList do begin
                 if Count > 0 then
                    for i := 0 to Count - 1 do begin
                      ShowMessage( PMyGroupUserInfo( Items[i] )^.Name );
                    end;
              end;
        *)

        if FieldByName('nac_rights').AsInteger > 0 then begin     // a right is assigned
          //ShowMessage('Rights: ' +FieldByName('nac_rights').AsString);
          if FieldByName('nac_rights').AsInteger = 99 then can_admin:=true;
          if FieldByName('nac_rights').AsInteger = 2  then can_edit:=true;
          if FieldByName('nac_rights').AsInteger = 1  then can_readonly:=true;

        end else begin             // Not in User table or no rights
          ShowMessage('Username ' +username +' on workstation ' +hostname +' is not authorised to use NAC');
          result:=false;
        end;

      end;

    end;   // if  allow_exceptions


    if (result) then begin
      if Length(FieldByName('nac_rights').AsString)>0 then begin

        update_history('Logon successful: ' +UserFullName
          +', ' +MyDomain +', rights=' +FieldByName('nac_rights').AsString);

        // Debug:
        update_history('Logon Debug: UserFullName=' +UserFullName
        +', MyDomain=' +MyDomain +', ServerDomain=' +ServerDomain
        +', hostname=' +hostname
        +', Company=' +Company
        //+', rights='  +FieldByName('nac_rights').AsString
        );
      end else begin
        update_history('Logon successful: ' +UserFullName  +' ' +username +', ' +MyDomain);
      end;
      fmInventory.StatusLine.SimpleText:='';

    end else begin
      update_history('Logon failed: UserFullName=' +UserFullName
        +', MyDomain=' +MyDomain +', ServerDomain=' +ServerDomain
        +', hostname=' +hostname
        +', Company=' +Company    +', rights=' +FieldByName('nac_rights').AsString);
      fmInventory.StatusLine.SimpleText:='Access Denied';
      exit;
    end;

    Close;
  end;


  OpenMyTable(dm0.quConfig);

  with fmInventory do begin
    //cbVlan.Readonly:=true;
    tsEdit.Visible:=true;
    tsConfig.TabVisible:=false;
    cbManualSync.ReadOnly:=true;
    rgRights.ReadOnly:=true;
    deGuiVlanRights.ReadOnly:=true;
    navPatches.VisibleButtons  :=[nbFirst,nbLast,nbRefresh];
    //navChangeLog.VisibleButtons:=[nbFirst,nbLast,nbRefresh];
    //navServerLog.VisibleButtons:=[nbFirst,nbLast,nbRefresh];
    navServerLog1.Visible:=false;
    Encryptuser1.Visible:=false;

    gridPortsDBTableView1default_vlan.Options.Editing:=false;
    gridVlan1View1.OptionsData.Editing:=false;
    gridVlan2DBTableView1.OptionsData.Editing:=false;
    cxGridDBTableView2.OptionsData.Editing:=false;  // dev type1
    cxGridDBTableView3.OptionsData.Editing:=false;  // dev type2
    cxGridDBTableView4.OptionsData.Editing:=false;  // OS
    cxGridDBTableView5.OptionsData.Editing:=false;  // OS1
    cxGridDBTableView6.OptionsData.Editing:=false;  // OS2
    cxGridDBTableView7.OptionsData.Editing:=false;  // OS3


    if can_admin then begin
      // Sysadmin: allow editing Users/Setting rights, Vlan description,
      //fmInventory.cbVlan.Readonly:=false;
      fmInventory.Caption:= username +': ' +Company
        +' NAC Management System - Administrator access';
      tsConfig.TabVisible:=true;
      cbManualSync.ReadOnly:=false;
      rgRights.ReadOnly:=false;
      deGuiVlanRights.ReadOnly:=false;
      // allow admin to delete patches
      navPatches.VisibleButtons  :=[nbFirst,nbLast,nbInsert,nbDelete,nbPost,nbCancel,nbRefresh];
      //navChangeLog.VisibleButtons:=[nbFirst,nbLast,nbInsert,nbPost,nbCancel,nbRefresh];
      //navServerLog.VisibleButtons:=[nbFirst,nbLast,nbInsert,nbPost,nbCancel,nbRefresh];
      navServerLog1.Visible:=true;
      Encryptuser1.Visible:=true;
      gridVlan1View1.OptionsData.Editing:=true;
      gridVlan2DBTableView1.OptionsData.Editing:=true;      
      gridPortsDBTableView1default_vlan.Options.Editing:=true;
      cxGridDBTableView2.OptionsData.Editing:=true;  // dev type1
      cxGridDBTableView3.OptionsData.Editing:=true;  // dev type2
      cxGridDBTableView4.OptionsData.Editing:=true;  // OS
      cxGridDBTableView5.OptionsData.Editing:=true;  // OS1
      cxGridDBTableView6.OptionsData.Editing:=true;  // OS2
      cxGridDBTableView7.OptionsData.Editing:=true;  // OS3

    end else if can_edit then begin
      //fmInventory.cbVlan.Readonly:=true;
      navPatches.VisibleButtons  :=[nbFirst,nbLast,nbInsert,nbDelete,nbPost,nbCancel,nbRefresh];
      fmInventory.Caption:=username +': ' +Company
        +' NAC Management System - SuperUser access';
      dm0.quConfig.ReadOnly:=true;      tsConfig.TabVisible:=true;
      dm0.quVlan.Readonly:=true;
      OpenMyTable(dm0.quUsers);
      ROTable(dm0.taVmpslog);
      //Table(taSwitch);
      ROTable(dm0.taSys_class);
      ROTable(dm0.taSys_class2);
      ROTable(dm0.taSys_os);
      dm0.quSwitch.Readonly:=true;
      dm0.quAuth_profile.Readonly:=true;
      dm0.quPorts.Readonly:=true;

    end else if can_readonly then begin
      fmInventory.Caption:=username +': ' +Company
        +' NAC Management System - Read-Only access';
      navSystems22.Visible:=false;

      tsConfig.TabVisible:=false;
      dm0.quVlan.Readonly:=true;
      ROTable(dm0.taStatus);
      dm0.quGuilog.ReadOnly:=true;
      //ROTable(taOper);
      ROTable(dm0.taVmpslog);
      dm0.quCable.ReadOnly:=true;
      ROTable(dm0.taSys_class);
      ROTable(dm0.taSys_class2);
      ROTable(dm0.taSys_os);
      dm0.quUsers.ReadOnly:=true;
      OpenMyTable(dm0.quSystems);
      dm0.quSwitch.Readonly:=true;
      dm0.quAuth_profile.Readonly:=true;
      dm0.quPorts.Readonly:=true;
      dm0.quSystems.ReadOnly:=true;

    end;

  end; // with


  // Build a list of vlans to be visible in edit mode
  parse_guivlanrights(GuiVlanRights);

end;

/// ------------------- end of functions -------


procedure TfmInventory.FormCreate(Sender: TObject);
begin
  Application.OnHint := ShowHint;

  // Init GUI
  bbDisconnect.Enabled:=false;
  Page.Enabled:=false;
  Page.ActivePage:=fmInventory.tsOverview2;
  //Page.ActivePage:=fmInventory.tsWelcome;
  page2.ActivePage:=fmInventory.p2LastSeen;
  Page.Visible:=false;
  HistoryOffset:=0;
  use_server2:=false;
  bbConnect2.Visible:=false;

  // leave it visible for now
  //fmInventory.Encryptuser1.Visible:=false;

  with fmInventory do begin
    Height:=480;        // 428 450
    Top:=82;
    Width:=824
  end;

  // http://www.devexpress.com/Support/Center/p/Q90514.aspx
  with cxTextItem1.Properties do
    ValidChars := ValidChars + ['*'];

end;

procedure TfmInventory.ShowHint(Sender: TObject);
begin
  StatusLine.SimpleText := Application.Hint;
end;

procedure TfmInventory.FileNew(Sender: TObject);
begin
  { Add code to create a new file }
end;

procedure TfmInventory.FileOpen(Sender: TObject);
begin
  if OpenDialog.Execute then
  begin
    { Add code to open OpenDialog.FileName }
  end;
end;

procedure TfmInventory.FileSave(Sender: TObject);
begin
   { Add code to save current file under current name }
end;

procedure TfmInventory.FileSaveAs(Sender: TObject);
begin
  if SaveDialog.Execute then
  begin
    { Add code to save current file under SaveDialog.FileName }
  end;
end;

procedure TfmInventory.FilePrint(Sender: TObject);
begin
  if PrintDialog.Execute then
  begin
    { Add code to print current file }
  end;
end;

procedure TfmInventory.FilePrintSetup(Sender: TObject);
begin
  PrintSetupDialog.Execute;
end;

procedure TfmInventory.FileExit(Sender: TObject);
begin
  Close;
end;

procedure TfmInventory.EditUndo(Sender: TObject);
begin
  { Add code to perform Edit Undo }
end;

procedure TfmInventory.EditCut(Sender: TObject);
begin
  { Add code to perform Edit Cut }
end;

procedure TfmInventory.EditCopy(Sender: TObject);
begin
  { Add code to perform Edit Copy }
end;

procedure TfmInventory.EditPaste(Sender: TObject);
begin
  { Add code to perform Edit Paste }
end;

procedure TfmInventory.HelpContents(Sender: TObject);
begin
  Application.HelpCommand(HELP_CONTENTS, 0);
end;

procedure TfmInventory.HelpSearch(Sender: TObject);
const
  EmptyString: PChar = '';
begin
  Application.HelpCommand(HELP_PARTIALKEY, Longint(EmptyString));
end;

procedure TfmInventory.HelpHowToUse(Sender: TObject);
begin
  Application.HelpCommand(HELP_HELPONHELP, 0);
end;

procedure TfmInventory.HelpAbout(Sender: TObject);
begin
  { Add code to show program's About Box }
  fmAboutInventory.ShowModal;
end;


function TfmInventory.enc(const str: string): string;
var
    i: integer;
    Cipher: TDCP_rijndael;
    KeyStr: string;
begin
  Cipher:= TDCP_rijndael.Create(nil);
  Cipher.InitStr('hfoie rrewüerjw$ ee m e oieiu we',TDCP_sha1);   // initialize the cipher with a hash of the passphrase
  result:=Cipher.EncryptString(str);
  Cipher.Burn;
  Cipher.Free;
end;

function decrypt(str: string): string;
var
  i: integer;
  Cipher: TDCP_rijndael;
  KeyStr: string;
begin
  Cipher:= TDCP_rijndael.Create(nil);
  Cipher.InitStr('hfoie rrewüerjw$ ee m e oieiu we',TDCP_sha1);   // initialize the cipher with a hash of the passphrase
  result:=Cipher.DecryptString(str);
  Cipher.Burn;
  Cipher.Free;
end;

function GetLastErrorText(): string;
var
  dwSize: DWORD;
  lpszTemp: PAnsiChar;
begin
  dwSize := 512;
  lpszTemp := nil;
  try
    GetMem(lpszTemp, dwSize);
    FormatMessage(FORMAT_MESSAGE_FROM_SYSTEM or FORMAT_MESSAGE_ARGUMENT_ARRAY,
      nil,
      GetLastError(),
      LANG_NEUTRAL,
      lpszTemp,
      dwSize,
      nil)
  finally
    Result := lpszTemp;
    FreeMem(lpszTemp)
  end
end;


procedure FilterToday(Sender: TObject; clear_state: boolean=false);
{$J+}
const   state_down: boolean=false; // hack for local 'static' variable
{$J-}
begin         (*
  //ShowMessage(BooltoStr(state_down));
  with fmInventory do
  if clear_state then begin
    state_down:=false;
    (Sender as TSpeedbutton).Down:=state_down;

  end else begin                   // toggle current state;
  if state_down then begin       // pressed, so remove filter and "unpress"
   // ShowMessage('Down->up');
    gridSystems.Columns[10].FilterExpression:='';

    gridSystems.ApplyFilter;
    state_down:=false;
    (Sender as TSpeedbutton).Down:=state_down;

  end else begin
    //ShowMessage('Up->Down' +DateTimeToStr(now-1, d));
    //ShowMessage(DateTimeToStr(now-1, dSettings));
    //ShowMessage(FormatDateTime('dd.mm.yyy HH:MM', now-1));
    //gridSystems.Columns[10].FilterExpression:='>' +FormatDateTime('dd.mm.yyyy HH:MM', now-1);
    gridSystems.Columns[10].FilterExpression:='>' +DateTimeToStr(now-1, dSettings);
    gridSystems.ApplyFilter;
    state_down:=true;
    (Sender as TSpeedbutton).Down:=state_down;
  end;
  end;
  //ShowMessage(BooltoStr(state_down));       *)
end;


procedure FilterUnknowns(Sender: TObject; clear_state: boolean=false);
{$J+}
const   state_down: boolean=false; // hack for local 'static' variable
{$J-}
begin       (*
  with fmInventory do
  if clear_state then begin
    //ShowMessage('state_down=true');
    state_down:=false;
    (Sender as TSpeedbutton).Down:=state_down;

  end else begin                   // toggle current state;

    if state_down then begin       // pressed, so remove filter and "unpress"
      //ShowMessage('Down->up');
      gridSystems.Columns[2].FilterExpression:='';
      gridSystems.ApplyFilter;
      state_down:=false;
      (Sender as TSpeedbutton).Down:=state_down;

    end else begin
      gridSystems.Columns[2].FilterExpression:='unknown';      
      gridSystems.ApplyFilter;
      //ShowMessage('Up->Down, column0=' +gridSystems.Columns[0].FilterExpression);
      state_down:=true;
      (Sender as TSpeedbutton).Down:=state_down;
    end;
  end;           *)
end;


function GetNTDomainName: string;      // from registry, but in fact returns computer name
var 
  hReg: TRegistry;
begin 
   hReg := TRegistry.Create; 
   hReg.RootKey := HKEY_LOCAL_MACHINE; 
   hReg.OpenKey('SOFTWARE\Microsoft\Windows NT\CurrentVersion\Winlogon', false );
   Result := hReg.ReadString( 'DefaultDomainName' ); 
   hReg.CloseKey; 
   hReg.Destroy;
end;

function GetSysDomainName : string;
{gets current user's domain name via WinAPI}
var
  IdentSize, DomainStrSize : DWORD;
  SecurityDesc : PSecurityDescriptor;
  SUserNameType : SID_Name_Use;
begin
  IdentSize := 0;
  DomainStrSize := 0;
  SecurityDesc := nil;
  SUserNameType := SIDTypeUser;
  {get domain name + security desc size}
  LookUpAccountName(nil, PChar(Username), SecurityDesc, IdentSize, nil, DomainStrSize, SUserNameType);
  SetLength(Result, DomainStrSize);
  SecurityDesc := AllocMem(IdentSize);
  try
    {get domain name}
    if LookUpAccountName(nil, PChar(Username), SecurityDesc, IdentSize, PChar(result), DomainStrSize, SUserNameType) then
      SetLength(result, StrLen(PChar(result)))
    else begin
      {error handling}
      (*FErrorCode := GetLastError;
      FErrorMsg := SysErrorMessage(FErrorCode);
      result := Format('Error %d - %s', [FErrorCode, FErrorMsg]);*)
      end; {else begin..}
  finally
    FreeMem(SecurityDesc);
    end; {try.. finally..}
end;



procedure TfmInventory.initialise;
var
  User          : Array[0..255] of Char;
  NameLen       : LongWord;    // integer
  NameSize,i      : cardinal;
  fxml          : TextFile;
  xmlfile, auth,StaticInvEnabled1 : string;
  settings      : IXMLDocument;
  cNode         : IXMLNode;      // node2
  offset,ErrCode: integer;
  MyNet         : TMyNetWorks;   // MyNetwork component  http://www.sura.ru/alex/
  d:            TThreadList;
  var1:      variant;

begin
  StaticInvEnabled1:='0';
  StaticInvEnabled:=false; NmapEnabled:=false; AntiVirusEnabled:=false; PatchCableEnabled:=false;
  DemoMode:=false; wsus_enabled:=false;  vlan_by_switch_location:=false;

  //--- get user's login name --------
  (*NameLen:=sizeof(User);
  NameSize:=sizeof(User);
  If (not GetComputerName(User, NameLen)) then   // WinAPI call
    Strcopy(User, 'UNKNOWN');
  Hostname:=User;                    // char[] to string conversion
  If ( WNetGetUser(nil, User, NameSize) <> 0) then begin  // WinAPI call
    ShowMessage('Unable to get your login name from Windows: ' +GetLastErrorText());
    Strcopy(User, 'UNKNOWN');
    //WTSQuerySessionInformation  WNetGetUser RasEnumConnections and RasGetCredentials
  end;
  Username:=UpperCase(User);        // char[] to string conversion    *)

  MyNet:=TMyNetWorks.Create(nil);
  Username:=UpperCase(MyNet.MyUserName);
  Hostname:=MyNet.MyComputerName;
  UserId:=0;   // set default
  //ShowMessage('Welcome ' +Username +' on ' +Hostname);  
  if Length(Username)<1 then begin
    ShowMessage('Error: unable to detect the logged on username');
    Application.Terminate;
  end;

  // There are several mothods to get the DOMAIN name, I'm listing several for future reference,
  // just in case.
  //ShowMessage('GetSysDomainName=' +GetSysDomainName +', GetNTDomainName=' +GetNTDomainName);
  // What Windows domain are we in?
  (*MyDomain:='';
  d:=MyNet.GetDomains;     // TMyNetWorks.GetDomains: TThreadList;
      if Assigned( d ) then
         with d.LockList do begin
            //MyDomain:=PMyNetResource( Items[0] )^.RemoteName;
            if Count > 0 then
               for i := 0 to Count - 1 do begin
                 //ShowMessage(IntToStr(i) +'- PMyNetResource.RemoteName=' +PMyNetResource( Items[i] )^.RemoteName
                 //     +' (Comment: '  +PMyNetResource( Items[i] )^.Comment
                 //     +') (Provider: '    +PMyNetResource( Items[i] )^.Provider+')' );
                 MyDomain:=PMyNetResource( Items[i] )^.RemoteName;
               end;
         end;
  *)
  MyDomain:=GetSysDomainName;

  //fmInventory.StatusLine.SimpleText:='Setting date locale ...';
  GetLocaleFormatSettings(0, dSettings);
  //d.DateSeparator := '-';
  //d.ShortDateFormat := 'yyyy/mm/dd';
  //dSettings.DateSeparator := '.';
  //dSettings.ShortDateFormat := 'dd/mm/yyyy';
  //dSettings.ShortTimeFormat := 'HH:MM';  


  // ---- vmps.xml: Load settings--------
      xmlfile:=ChangeFileExt(ParamStr(0),'.xml');
      fmInventory.StatusLine.SimpleText:='Loading ' +xmlfile;
      if  FileExists(xmlfile) then begin

        settings:=TXMLDocument.Create(nil);
        settings.LoadFromFile(xmlfile);
        settings.active:=true;
        if (settings.DocumentElement.GetNodeName <> 'vmps') then begin
          ShowMessage('vmps.xml syntax incorrect');
          exit;
        end;

        fmInventory.StatusLine.SimpleText:='Loading ' +xmlfile +' : settings';
        cNode:=settings.DocumentElement.ChildNodes.FindNode('settings');
        if (cNode<>nil) then begin
          with dm0.MyConnection1 do begin
            //StaticInvEnabled1:=cNode.GetAttribute('StaticInvEnabled');
            //if (StaticInvEnabled1='1') then StaticInvEnabled:=true;
            StaticInvEnabled:=false;
            var1  :=cNode.GetAttribute('StaticInvEnabled');
            if (not VarIsNull(var1)) and (var1='1') then begin  // is StaticInvEnabled=1 ?
              StaticInvEnabled:=true;
            end;

            Company:='';
            // Depending on the Company we customise the GUI
            Company:=cNode.GetAttribute('company');
            fmInventory.Caption:=Company +'- NAC Management System';
          end;
        end;

        // Get Mysql User/password
        fmInventory.StatusLine.SimpleText:='Loading ' +xmlfile +': mysql';
        cNode:=settings.DocumentElement.ChildNodes.FindNode('mysql');
        if (cNode<>nil) then begin
        //repeat
        // should only be One node
        with dm0.MyConnection1 do begin

            auth:=decrypt(cNode.GetAttribute('auth'));
            //ShowMessage(auth);
            // to make username/password guessing more difficult (especially when both
            // contain common characters, we parse an auth string of USER:PASSWORD
            // and seperate into two pieces.
            // Only the 'auth' is used from the XML file.
            offset:=AnsiPos(':',auth);
            username:=AnsiMidStr(auth, 1, offset-1);
            Password:=AnsiMidStr(auth, offset+1, 30);
            //ShowMessage(Username +' ' +password);

            Server  :=cNode.GetAttribute('server');
            Database:=cNode.GetAttribute('database');
            Port:=cNode.GetAttribute('port');

            var1  :=cNode.GetAttribute('server2');
            //ShowMessage('Secondary server2-var1: ' +var1);
            if VarIsNull(var1) then begin
              // no Server2 entry exists, just ignore
              Server2:='';
              //ShowMessage('No Secondary server, disabling button. ');
              bbConnect2.Hint:='no server2 configured in vmps.xml';
              bbConnect2.Visible:=false;
            end else begin
              Server2:=var1;
              bbConnect2.Visible:=true;
              if use_server2 then Server:=Server2;  // connect to secondary now
              bbConnect2.Hint:='Connect to secondary: ' +Server2;
              //ShowMessage('Secondary server: ' +Server2);
            end;

          end;
          //cNode:=cNode.NextSibling;



        //until cNode= nil;
        end;

        fmInventory.StatusLine.SimpleText:='Database ' +dm0.MyConnection1.Database
          +' on ' +dm0.MyConnection1.Server;

        if (StaticInvEnabled) then begin     // enable links to static inv
          //fmInventory.StatusLine.SimpleText:='Loading ' +xmlfile +': mssql-inv';
          cNode:=settings.DocumentElement.ChildNodes.FindNode('mssql-inv');
          if (cNode<>nil) then begin
             with dm0.MSConnection1 do begin
                Server:=cNode.GetAttribute('server');
                Database:=cNode.GetAttribute('database');
                auth:=decrypt(cNode.GetAttribute('auth'));
                offset:=AnsiPos(':',auth);
                Username:=AnsiMidStr(auth, 1, offset-1);
                Password:=AnsiMidStr(auth, offset+1, 30);
                //ShowMessage('user=' +Username +', password=' +Password);
              end;
          end;
        end;


        settings:=nil;   // xml structure no longer needed
      end;

  // We don't do any syslog'ing yet
(*  if LogToSyslog then begin
        dmJob.IdSysLog1.Active:=true
  end else begin
        dmJob.IdSysLog1.Active:=false;
  end; *)

  dm0.quSystems.FieldByname('mac').ValidChars:= ['0'..'9', 'A'..'F', 'a'..'f', '.'];

  can_admin:=false;
  can_edit:=false;
  can_readonly:=false;
  fmInventory.Timer1.Enabled:=true;     // Allow updates to run in the background
end;




procedure enable_modules;
begin
  fmInventory.StatusLine.SimpleText:='Loading settings from config table';

  // Load module settings from the server config table
  StaticInvEnabled  :=get_config_bool2('static_inv',         'StaticInvEnabled');
  NmapEnabled       :=get_config_bool2('nmap_enabled',       'NmapEnabled');
  PatchCableEnabled :=get_config_bool2('patchcable_lookups', 'PatchCableEnabled');
  AntiVirusEnabled  :=get_config_bool('epo_enabled');
  wsus_enabled      :=get_config_bool('wsus_enabled');
  vlan_by_switch_location :=get_config_bool('vlan_by_switch_location');
  DemoMode          :=get_config_bool('DemoMode');


  // enabled/disable relevant parts of the GUI

        fmInventory.gridVlan2.Enabled:=vlan_by_switch_location;

        if (not StaticInvEnabled) then begin
          fmInventory.bbInv.Enabled:=false;
          fmInventory.p2Inventory.TabVisible:=false;
        end;

        if (not PatchCableEnabled) then begin
          //ShowMessage('Disabling PatchCable features');
          fmInventory.tsPatchCables2.TabVisible:=false;
          fmInventory.tsPatchCables2.Visible:=false;
          //fmInventory.gridPorts.Columns[6].Visible:=false;          // PatchCableEnabled="1"
          fmInventory.gridPortsDBTableView1PatchOfficeList.Visible:=false;
          fmInventory.lPatchCable.Enabled:=false;
          fmInventory.dePatchCableDetails.Enabled:=false;
        end else begin
          //ShowMessage('Enabling PatchCable features');
        end;

        if (not AntiVirusEnabled) then begin
          fmInventory.lepo.Enabled:=false;
          fmInventory.llepo.Enabled:=false;
          fmInventory.lepo_name.Enabled:=false;
          fmInventory.lav9.Enabled:=false;
          fmInventory.p2AntiVirus.TabVisible:=false;
        end else begin
          //ShowMessage('AntiVirusEnabled features');
        end;
        if (not wsus_enabled) then begin
          fmInventory.lwsus.Enabled:=false;
          fmInventory.lwsus.Enabled:=false;
          fmInventory.p2Wsus.TabVisible:=false;
        end else begin
          //ShowMessage('WSUS features');
        end;

        if (not NmapEnabled) then begin
          fmInventory.bbNmap.Enabled:=false;
          fmInventory.lnmap.Enabled:=false;
          fmInventory.p2Nmap.TabVisible:=false;
        end;

end;



procedure open_tables;
begin
with fmInventory do begin
  //StatusLine.SimpleText:='Connecting to Database ' +dm0.MyConnection1.Database +' on ' +dm0.MyConnection1.Server;
  StatusLine.SimpleText:='Connecting to Database Server';
  dm0.MyConnection1.Connect;

  StatusLine.SimpleText:='Opening lookup tables: users ...';
  OpenMyTable(dm0.quUsers);
  if not check_rights() then  begin
    Disconnect;
    Exit;
  end;

  enable_modules();

  StatusLine.SimpleText:='Opening lookup tables: vlan, status, switch, port ...';
  OpenMyTable(dm0.quVlan);
  OpenMyTable(dm0.quVlanList);
  OpenMyTable(dm0.quVlanswitch);
  OpenMyTable(dm0.taStatus);
  //taOper.Open;
  //dm0.quSwitch.Open;         // delay for speed
  //dm0.quPorts.Open;
  if PatchCableEnabled then begin
    StatusLine.SimpleText:='Opening lookup tables: cables ...';
    //dm0.quCable.Open;        // delay for speed
  end;
  StatusLine.SimpleText:='Opening lookup tables: class, os, location ...';
  OpenMyTable(dm0.taSys_class);
  OpenMyTable(dm0.taSys_class2);
  OpenMyTable(dm0.taSys_os);
  OpenMyTable(dm0.taSys_os1);
  OpenMyTable(dm0.taSys_os2);
  OpenMyTable(dm0.taSys_os3);
  OpenMyTable(dm0.quLocation);
  OpenMyTable(dm0.taBuilding);
  //StatusLine.SimpleText:='Opening guilog & naclog tables...';
  //dm0.quGuilog.Open;         // delay for speed
  //dm0.taVmpslog.Open;

  StatusLine.SimpleText:='Opening systems query...';
  //ShowMessage(StatusLine.SimpleText);
  OpenMyTable(dm0.quSystems);
  //StatusLine.SimpleText:='Opening systems view2...';
  //taV_invent1.Open;

  if dm0.MyConnection1.Connected then begin
    bbConnect.Enabled:=false;
    bbConnect2.Enabled:=false;    
    bbDisconnect.Enabled:=true;
  end;

  StatusLine.SimpleText:='Select default page';
  Page.ActivePage:=fmInventory.tsEdit;         // hack to switch fwd/back
  Page.ActivePage:=fmInventory.tsOverview2;
  StatusLine.SimpleText:='ready';

  Page.Refresh;
  Page.Enabled:=true;
  Page.Visible:=true;
end;
end;


procedure call_connect;
begin
  //initialise;
  open_tables;

  // TBD: Patch cables: only show First floor (keep initial query quick)
  //fmInventory.gridCable.Columns[0].FilterExpression:='01*';
  //fmInventory.gridCable.ApplyFilter;

  // Filter: Overview Tab, show Unknowns Today (for speed)
  // 9.11.07
  //FilterUnknowns(fmInventory.bbUnknown);
  //FilterToday(fmInventory.bbByToday);

  gui_disable_ports_list:=get_config1('gui_disable_ports_list');

  // enable layout menus, and load layout now
  fmInventory.LoadGridLayout1.enabled:=true;
  fmInventory.SaveGridLayout1.enabled:=true;
  fmInventory.LoadGridLayouts(ExtractFileDir(ParamStr(0)) +'\' +Username +'.ini')   // load layouts from vmps.ini
end;


procedure TfmInventory.bbConnectClick(Sender: TObject);
begin
  call_connect;
end;

procedure tsPatchcableUpdate;
begin

end;

procedure tsEditUpdate;
begin
with fmInventory do begin
   lDns.Caption:='';
   lDns2.Caption:='';

   //ShowMessage('colour depending on Vlan and status');
   if ((rgStatus.Value='0') or (rgStatus.Value='7') )then begin // unknown or killed
     rgStatus.Font.Color:=clRed;
   end else begin
     rgStatus.Font.Color:=clWindowText;
   end;

   // Is this User expired? If yes, mark him/her red!
   //
   (*
   if ((Length(fmInventory.quSystems.FieldByName('UserLastSeenDirex').AsString)>0 )
       and
      ( DaysBetween(fmInventory.quSystems.FieldByName('UserLastSeenDirex').AsDateTime, Now)>30)
      ) then begin // Old users
     deLastSeenDirex.Font.Color:=clRed;
     deName.Font.Color:=clRed;
     deVorname.Font.Color:=clRed;
   end else begin
     deLastSeenDirex.Font.Color:=clWindowText;
     deName.Font.Color:=clWindowText;
     deVorname.Font.Color:=clWindowText;
   end;
   *)

   // Disable the Vlan drop down list if unmanaged
   if (dm0.quSystems.FieldByName('STATUS').AsInteger=3 ) then begin // unmanaged
     cbVlanLookup.Enabled:=false;
     lVlanDesc.Hint:='Unmanaged: vlan cannot be changed';
     // Why are these two ignore?
     cbVlanLookup.Hint:='Unmanaged end-device: vlan cannot be assigned/changed';
     cbVlanLookup.Text:='Unmanaged: vlan cannot be changed';
   end else begin
     cbVlanLookup.Enabled:=true;
     lVlanDesc.Hint:='A descriptive name of the vlan to be assigned';     
     cbVlanLookup.Hint:='Select the vlan to be assigned to this end-device';
   end;

   lVlanColour.Caption:='';  lVlanColour.Font.Color:= clBtnFace;
   if (Company='INO') then begin
     cbClass2.Enabled:=false;    // not needed yet
     lClassification.Enabled:=false;
     if (Length(dm0.quSystems.FieldByName('VlanGroup').AsString)>0) then
     case dm0.quSystems.FieldByName('VlanGroup').AsInteger of
        1: begin
          lVlanColour.Caption:='Insecure';
          lVlanColour.Font.Color:= TColor($0167E5);     // Orange
        end;
        //2: begin
        //  lVlanColour.Caption:='Secure';
        //  lVlanColour.Font.Color:= clOlive;
        //end;
     end;
     //bbDNSnaming.Visible:=true;

   end else begin
   //  bbDNSnaming.Visible:=false;    // only designed for INO as its a bit Beta
   end; // If INO
   bbDNSnaming.Visible:=true;

   // ________ Lookup and display username details: on the Edit Window____________
   if ((Page.ActivePage=tsEdit) and (dm0.quSystems.FieldByName('uid').AsInteger >0)) then begin
   with  dm0.quUserLookup do begin
     Close;
     SQL.Clear;
     SQL.Add('SELECT LastSeenDirectory, Surname, GivenName, Department, '
        +' rfc822mailbox, HouseIdentifier, PhysicalDeliveryOfficeName, TelephoneNumber,Mobile, '
        +' location,comment,username  '
        +' FROM users WHERE id='''
        +dm0.quSystems.FieldByName('uid').AsString +'''');
     //ShowMessage(SQL.text);
     Open;
     while not EOF do begin
       //ShowMessage('Got ' +FieldByName('Surname').AsString);
       next;
     end;
   end;

   deLastUpdatedUsername.Update; //  .Refresh;
   end

end; // with
end;


procedure TfmInventory.bbReload2Click(Sender: TObject);
begin
  dm0.quSystems.Refresh;
end;


procedure TfmInventory.navPatchesBeforeAction(Sender: TObject;
  Button: TNavigateBtn);
begin
  if Button = nbDelete then begin
      //Cancel, Delete, First, Insert, Last, Next
    //ShowMessage('Entry is going to be deleted!');
  end;
end;



procedure TfmInventory.bbDisconnectClick(Sender: TObject);
begin
  //Page.Enabled:=false;
  //Page.Visible:=false;
  //Timer1.Enabled:=false;        // we've lost connectivity, so avoid more popups
  Disconnect;
end;

procedure TfmInventory.bbUpdateClick(Sender: TObject);
begin
  ShowMessage('Updates are immediate since version 2, this button will be removed in a future version');
(*
  with MyQuery1 do begin
    Close;
    SQL.Clear;
    SQL.Add('UPDATE oper SET datetime=now() WHERE value="guiupdate"');
    Execute;
    Close;
  end;
  update_history('Server update requested');
  ShowMessage('An update to the VMPS server is now pending');
  taOper.Refresh;    *)
end;





procedure TfmInventory.gridSystemsDblClick(Sender: TObject);
begin
  Page.ActivePage:=tsEdit;
  UpdateMACLookup;
  tsEditUpdate;
  update_queries;
end;

procedure TfmInventory.gridSystemsCellClick(Column: TColumn);
begin
  //ShowMessage(Column.Field.Displayname);
  // or (Column.Field.Displayname='comment')
  //if (Column.Field.Displayname='LastSeen') then
  //  ShowMessage(Column.Field.Displaytext);
end;

procedure TfmInventory.deMachineChange(Sender: TObject);
begin
   lDns.Caption:='';
   lDns2.Caption:='';
   UpdateMACLookup;
end;

procedure TfmInventory.PageChange(Sender: TObject);
begin
  //open_tables;
  //ShowMessage('Changed to page ' +IntToStr((Sender as TPageControl).TabIndex) );
  fmInventory.StatusLine.SimpleText:='';

  case Integer((Sender as TPageControl).TabIndex)  of
  1: begin          // Edit
    UpdateMACLookup;
    tsEditUpdate;
    update_queries;
    end;
  2: begin
    OpenMyTable(dm0.quSwitch);
    dm0.quSwitch.Refresh;
    OpenMyTable(dm0.quAuth_profile);
    OpenMyTable(dm0.quPorts);
    //dm0.quPort1.Open;
    end;
  3: begin
    OpenMyTable(dm0.quAuth_profile);
    OpenMyTable(dm0.quPorts);
    dm0.quPorts.Refresh;
    end;
  4: begin
    OpenMyTable(dm0.quCable);
    OpenMyTable(dm0.taCableType);    
    //dm0.quCable.Refresh;    // Too slow? Table can be really big
    end;
  5: begin
    OpenMyTable(dm0.quGuilog);
    dm0.quGuilog.Refresh;
    end;
  6: begin
    OpenMyTable(dm0.taVmpslog);
    dm0.taVmpslog.Refresh;
    end;
  7: begin
    OpenMyTable(dm0.quConfig);
    dm0.quConfig.Refresh;
    end;
  end;

end;


procedure Refresh_UsersInPortLocation;
begin
  with fmInventory do begin
    UsersInOffice.Caption:='';
    try
      if dm0.quSystems.FieldByName('office').AsInteger>1 then begin
        dm0.MyQuery1.SQL.Clear;
        dm0.MyQuery1.SQL.Add('SELECT    GROUP_CONCAT(DISTINCT Surname) as UserList from users WHERE location='''
          +VarToStr(cxSystemsLocationCombo.EditValue)
          //+dm0.quSystems.FieldByName('office').AsString
          +''' ');
          // Note how we cur the current location from the lookup list not the DB.
        dm0.MyQuery1.Open;
        UsersInOffice.Caption:=AnsiLeftStr(dm0.MyQuery1.FieldByName('UserList').AsString,40); // only first 40 characters
      end;
    except
      on E: EConvertError do
        UsersInOffice.Caption:='';
    end;
  end;
end;




procedure TfmInventory.NavEdit1Click(Sender: TObject;
  Button: TNavigateBtn);
begin
  UpdateMACLookup;
  tsEditUpdate;
  update_queries;
end;

procedure TfmInventory.gridPortsDblClick(Sender: TObject);
begin
  // Switch to overview screen and filter all systems on this port

  //Refresh_UsersInPortTab;
  if (dm0.quPorts.FieldByName('location').AsInteger>0) then
  with dm0 do begin
    MyQuery1.SQL.Clear;
    MyQuery1.SQL.Add('SELECT GROUP_CONCAT(DISTINCT  Surname) as UserList from users WHERE location='''
      +quPorts.FieldByName('location').AsString
      +''' ');
    MyQuery1.Open;
    ShowMessage(quPorts.FieldByName('location').AsString +'= ' +MyQuery1.FieldByName('UserList').AsString);
  end;
end;

procedure TfmInventory.gridSwitchesDblClick(Sender: TObject);
begin
(*  // Switch to overview screen and filter all systems on this Switch
  fmInventory.gridSystems.Columns[9].FilterExpression:='';
  fmInventory.gridSystems.Columns[12].FilterExpression:=
    fmInventory.taSwitch.FieldbyName('ip').AsString;
  fmInventory.gridSystems.ApplyFilter;     *)
  //Page.ActivePage:=tsOverview;
end;

procedure TfmInventory.gbUserClick(Sender: TObject);
begin     (*
  // Switch to overview screen and filter all systems for this user
  fmInventory.gridSystems.Columns[8].FilterExpression:=
  dm0.quSystems.FieldbyName('description').AsString;
  fmInventory.gridSystems.ApplyFilter;
  //Page.ActivePage:=tsOverview;      *)
end;

procedure TfmInventory.bbClearFIlter1Click(Sender: TObject);
begin     (*
  With fmInventory do begin
    // remove all filters
    gridSystems.ClearFilters;
    gridSystems.ApplyFilter;
    dm0.quSystems.Filtered:=false;
    // reset toggle buttons
    FilterUnknowns(fmInventory.bbUnknown, true);
    FilterToday(fmInventory.bbByToday, true);
    // reset Lists
    cbByUser.Text  :='By User';
    cbBySwitch.Text:='By Switch';
    cbByGroup.Text :='By Group';
    cbByVlan.Text  :='By Vlan';
  end;    *)
end;



// Colour rows to aide user
//
// See also http://homepages.borland.com/efg2lab/Library/Delphi/Graphics/Color.htm

procedure TfmInventory.gridCableGetCellParams(Sender: TObject;
  Field: TField; AFont: TFont; var Background: TColor;
  State: TGridDrawState; StateEx: TGridDrawStateEx);
begin
  if dm0.quCable.FieldByName('type').AsString='Tf.' then begin
    AFont.Color := TColor($0000FF00);;  // A bright green clGreen
  end else if dm0.quCable.FieldByName('type').AsString='Infnet' then begin
    AFont.Color := clGray;
  end else if dm0.quCable.FieldByName('type').AsString='Spez.Net' then begin
    //Background := clBlue;
    AFont.Color:=clBlue;
  end else if dm0.quCable.FieldByName('type').AsString='Adsl' then begin
    AFont.Color:=clBlue;
  end else begin
    //Background := clYellow;
  end;
end;


procedure TfmInventory.gridSystemsGetCellParams(Sender: TObject;
  Field: TField; AFont: TFont; var Background: TColor;
  State: TGridDrawState; StateEx: TGridDrawStateEx);
begin
  if Company='INO' then begin
   if (  Length(dm0.quSystems.FieldByName('Vlangroup').AsString)>0 ) then begin
     case dm0.quSystems.FieldByName('Vlangroup').AsInteger of
        1: AFont.Color:= TColor($0167E5);     // Orange
        //2: AFont.Color:= clOlive;
     end;
    end;
  end;

  if dm0.quSystems.FieldByName('status').AsInteger=0 then begin
    //Background := clGray;
    AFont.Color:=clGray;
  end;
end;




// -------- display several values in one cell
procedure TfmInventory.taPortOfficeLookupGetText(Sender: TField;
  var Text: String; DisplayText: Boolean);
begin
  if Length(dm0.quPorts.FieldByName('location').AsString)>0 then
  with dm0.MyQuery1 do begin
    SQL.Clear;
    SQL.Add('SELECT SurName from users WHERE HouseIdentifier='''
      +dm0.quPorts.FieldByName('location').AsString +'''');
    Open;
    if Length(FieldByName('SurName').AsString)>0 then
      ShowMessage(FieldByName('SurName').AsString);
    Close;
  end;
end;

procedure TfmInventory.taPortPatchLookupGetText(Sender: TField;
  var Text: String; DisplayText: Boolean);
begin
  if Length(Sender.AsString)>0 then
  with dm0.quCable do begin
    Text:=Sender.AsString;
    if (Locate('outlet',Sender.AsString, [loCaseInsensitive])) then begin
      Text:=Text +' '
        +', ' +FieldByName('office').AsString
        +', ' +FieldByName('comment').AsString
        +', Switch ' +FieldByName('switch').AsString
        +', Port ' +FieldByName('port').AsString
      ;
    end;
  end;
end;

procedure TfmInventory.tsEditShow(Sender: TObject);
begin
  deLastUpdatedUsername.Caption:='';   // sometimes wrong
  UpdateMACLookup;
  tsEditUpdate;
  update_queries;
end;

procedure TfmInventory.taCableUserFromDirexGetText(Sender: TField;
  var Text: String; DisplayText: Boolean);
begin
  // Query direx for this Office & Display.
  with dm0.MyQuery1 do begin
    SQL.Clear;
    SQL.Add('SELECT GROUP_CONCAT(DISTINCT Surname) as Surname from users WHERE PhysicalDeliveryOfficeName='''
      +dm0.quCable.FieldByName('office').AsString +'''');
    Open;
    if Length(FieldByName('Surname').AsString)>0 then
      Text:=FieldByName('Surname').AsString;
      //ShowMessage(FieldByName('SurName').AsString);
    Close;
  end;
end;

procedure TfmInventory.bbPatchToExcelClick(Sender: TObject);
var
 WorkBk : _WorkBook; //  Define a WorkBook
 WorkSheet : _WorkSheet;
 I, J, K, Rows, Cols : Integer;
 IIndex,OleRange : OleVariant;
 TabGrid : Variant;
begin

  Rows:=0; Cols:=0;
  with dm0.MyQuery1 do begin
    SQL.Clear;
    SQL.Add('SELECT COUNT(*) as numrows FROM patchcable');
    Open;
    if Length(FieldByName('numrows').AsString)>0 then
      Rows:=FieldByName('numrows').AsInteger;
    Close;
  end;

  with dm0.quCable do begin
     DisableControls;     //  Faster: don't scroll
     Open;
     Cols:=FieldCount;
     //ShowMessage('Rows, Cols=' +IntToStr(Rows) +' ' +IntToStr(Cols));
     TabGrid := VarArrayCreate([0, Rows, 0 , Cols-1],VarOleStr);

     I:=0;
     for j:=0 to Cols-1 do begin             // Column Titles
       TabGrid[I,J]:=Fields[J].DisplayName;
     end;

     First;  I:=I+1;                         // Field data
     while not EOF do begin
       for j:=0 to Cols-1 do begin
         TabGrid[I,J]:=Fields[J].AsString;
       end;
       I:=I+1; next;
     end;
      EnableControls;
   end;

   XLApp.Connect;                             // Connect to the server TExcelApplication
   XLApp.WorkBooks.Add(xlWBatWorkSheet,0);    // Add WorkBooks
   WorkBk := XLApp.WorkBooks.Item[1];         // Select the first WorkBook   IIndex
   WorkSheet := WorkBk.WorkSheets.Get_Item(1) as _WorkSheet;      // Define the first WorkSheet

   // Copy data to Excel
   Worksheet.Range['A1',Worksheet.Cells.Item[Rows,Cols]].Value := TabGrid;

   // Excel formatting: general
   WorkSheet.Name := 'Patch Cables';
   Worksheet.Columns.Font.Bold := False;
   Worksheet.Columns.HorizontalAlignment := xlCenter;
   Worksheet.Columns.Columns.Range['J1','O' +IntToStr(Rows)].HorizontalAlignment := xlLeft;
   //WorkSheet.Columns.ColumnWidth := 12;


   // Highlight first row  ('A'=65)
   WorkSheet.Range['A1', Chr(65+Cols) +'1'].Font.Bold := True;
   WorkSheet.Range['A1', Chr(65+Cols) +'1'].Font.Color:= clRed;

   WorkSheet.Columns.Autofit;
   Worksheet.Columns.Columns.Range['M1','M' +IntToStr(Cols)].ColumnWidth:=9;
   // Hide the following colums, don't want them printed
   Worksheet.Columns.Columns.Range['G1','G' +IntToStr(Cols)].ColumnWidth:=0;
   Worksheet.Columns.Columns.Range['J1','J' +IntToStr(Cols)].ColumnWidth:=0;
   Worksheet.Columns.Columns.Range['N1','N' +IntToStr(Cols)].ColumnWidth:=0;
   Worksheet.Columns.Columns.Range['P1','P' +IntToStr(Cols)].ColumnWidth:=0;

   // Enable a (cool) autofilter drop-down menu in the Excel title.
   OleRange:=Worksheet.Columns.Columns.Range['A1','P1'];
   OleRange.AutoFilter;

   XLApp.Visible[0]:=true;             // Show it
   XLApp.UserControl:=true;

   //SHowMessage('Press OK');
   //XLApp.Quit;
   XLApp.Disconnect;
   TabGrid := Unassigned;
end;



procedure TfmInventory.cbVlanCloseUp(Sender: TObject);
begin
  //tsEditUpdate;
end;

procedure TfmInventory.cbStatusCloseUp(Sender: TObject);
begin
  tsEditUpdate;
end;

procedure TfmInventory.cbByuserChange(Sender: TObject);
var
  firstname,surname: string;
begin        (*
  //ShowMessage((Sender as TCombobox).Text);
  // extract the name/surname from the list and paste it into the Filter section
  firstname:=Copy((Sender as TCombobox).Text, 1, Pos(';', (Sender as TCombobox).Text)-1);
  surname  :=Copy((Sender as TCombobox).Text, Pos(';',    (Sender as TCombobox).Text)+1, 255 );
  gridSystems.Columns[6].FilterExpression:=firstname;
  gridSystems.Columns[5].FilterExpression:=surname;
  gridSystems.ApplyFilter;       *)
end;



procedure TfmInventory.bbByLastHourClick(Sender: TObject);
begin   (*
  //DateTimeToString(str,now(),d);        //DateTimeToStr(now, d);
  gridSystems.Columns[10].FilterExpression:='>' +DateTimeToStr(now-1, dSettings);
  gridSystems.ApplyFilter;    *)
end;

procedure TfmInventory.bbUserSearchClick(Sender: TObject);
begin
  if not dm0.quUsers.Locate('username',deUserSearch.Text, [loPartialKey,loCaseInsensitive]) then begin
    ShowMessage('Unable to find user with an User account name starting with '
      +deUserSearch.Text);
  end else begin
    deUserSearch.Text:='';
    deNameSearch.Text:='';    
  end;
end;

procedure TfmInventory.bbNamerSearchClick(Sender: TObject);
begin
  if not dm0.quUsers.Locate('Surname',deNameSearch.Text, [loPartialKey,loCaseInsensitive]) then begin
    ShowMessage('Unable to find user with an Family Name starting with '
      +deNameSearch.Text);
  end else begin
    deUserSearch.Text:='';
    deNameSearch.Text:='';
  end;
end;

procedure TfmInventory.bbGetAdminsClick(Sender: TObject);
begin
  with dm0.MyQuery1 do begin
    SQL.Clear;
    SQL.Add('SELECT CONCAT(GivenName, '' '', Surname, '', '', username) AS Name  from users WHERE nac_rights=99');
    Open;
    meUserQuery.Lines.Clear;
    while not EOF do begin
      meUserQuery.Lines.Add(FieldByName('Name').AsString);
      next;
    end;
    Close;
  end;
end;

procedure TfmInventory.bbUserSupersClick(Sender: TObject);
begin
  with dm0.MyQuery1 do begin
    SQL.Clear;
    SQL.Add('SELECT CONCAT(GivenName, '' '', Surname, '', '', username) AS Name  from users WHERE nac_rights=2');
    Open;
    meUserQuery.Lines.Clear;
    while not EOF do begin
      meUserQuery.Lines.Add(FieldByName('Name').AsString);
      next;
    end;
    Close;
  end;
end;

procedure TfmInventory.bbUserReadonlyClick(Sender: TObject);
begin
  with dm0.MyQuery1 do begin
    SQL.Clear;
    SQL.Add('SELECT CONCAT(GivenName, '' '', Surname, '', '', username) AS Name  from users WHERE nac_rights=1');
    Open;
    meUserQuery.Lines.Clear;
    while not EOF do begin
      meUserQuery.Lines.Add(FieldByName('Name').AsString);
      next;
    end;
    Close;
  end;
end;

procedure TfmInventory.bbUserManualSyncClick(Sender: TObject);
begin
  with dm0.MyQuery1 do begin
    SQL.Clear;
    SQL.Add('SELECT CONCAT(GivenName, '' '', Surname, '', '', username) AS Name  from users WHERE manual_direx_sync=1');
    Open;
    meUserQuery.Lines.Clear;
    while not EOF do begin
      meUserQuery.Lines.Add(FieldByName('Name').AsString);
      next;
    end;
    Close;
  end;
end;


procedure TfmInventory.bbPrevious500Click(Sender: TObject);
begin
  with dm0.quGuilog do begin
    //Close;
    //Offset:=Offset-500;
    //if Offset<0 then Offset:=0;
    //lHistoryOffset.Caption:=IntToStr(Offset);
    //Open;
  end;
end;

procedure TfmInventory.bbNext500Click(Sender: TObject);
begin
 (* with quGuilog do begin
    Close;
    Offset:=Offset+500;
    lHistoryOffset.Caption:=IntToStr(Offset);
    Open;
  end;  *)
end;


procedure TfmInventory.bbServerLogPrevClick(Sender: TObject);
begin
  with dm0.taVmpslog do begin
    Close;
    Offset:=Offset-500;
    if Offset<0 then Offset:=0;
    lServerLogOffset.Caption:=IntToStr(Offset);
    Open;
  end;
end;

procedure TfmInventory.bbServerLogNextClick(Sender: TObject);
begin
  with dm0.taVmpslog do begin
    Close;
    Offset:=Offset+500;
    lServerLogOffset.Caption:=IntToStr(Offset);
    Open;
  end;
end;


procedure TfmInventory.bbUnknownClick(Sender: TObject);
begin
  FilterUnknowns(Sender);
end;

procedure TfmInventory.bbByTodayClick(Sender: TObject);
begin
  FilterToday(Sender);
end;

procedure TfmInventory.bbExpiredUsersClick(Sender: TObject);
begin
  with dm0.MyQuery1 do begin
    SQL.Clear;
    SQL.Add('SELECT GivenName,Surname,username,LastSeenDirectory from users where (TO_DAYS(LastSeenDirectory) < TO_DAYS(NOW())-14) ORDER BY LastSeenDirectory');
    Open;
    meUserQuery.Lines.Clear;
    while not EOF do begin
      meUserQuery.Lines.Add(FieldByName('Surname').AsString +' '
        +FieldByName('GivenName').AsString +', '
        +FieldByName('username').AsString +' '
        +FieldByName('LastSeenDirectory').AsString
      );
      next;
    end;
    Close;
  end;
end;



procedure TfmInventory.bbRestartPortClick(Sender: TObject);
begin

  // clear mac
  if (get_config1('check_clear_mac')='true') and (dm0.quSystems.FieldbyName('switch_type').AsInteger=1) then begin

    update_history('clear_mac requested for system '
     +dm0.quSystems.FieldbyName('name').Asstring
     +', mac '
     +dm0.quSystems.FieldbyName('mac').Asstring
    );

    with dm0.MyQuery1 do begin
      SQL.Clear;
      SQL.Add('UPDATE systems set clear_mac=1 WHERE id='''
        +dm0.quSystems.FieldbyName('id').Asstring
        +''' LIMIT 1'
      );
      //ShowMessage(SQL.Text);
      ShowMessage('Mac ' +dm0.quSystems.FieldbyName('mac').Asstring
        +' will be cleared within the next minute.');
      Execute;
      Close;
    end;

  end else begin
    //ShowMessage('bbRestartPortClick: check_clear_mac=' +get_config1('check_clear_mac')
    //  +', switch_type=' +dm0.quSystems.FieldbyName('switch_type').AsString);
  end;


  // PORT restart
  with dm0.MyQuery1 do begin
    SQL.Clear;
    SQL.Add('UPDATE port set restart_now=1 WHERE id='''
      +dm0.quSystems.FieldbyName('portid').Asstring
      +''' LIMIT 1'
    );
    //ShowMessage(SQL.Text);
    ShowMessage('Port ' +dm0.quSystems.FieldbyName('port').Asstring
      +' will be restarted within the next minute (verify by refreshing the "Server Log" tab).');
    Execute;
    Close;
  end;
end;


procedure TfmInventory.Timer1Timer(Sender: TObject);
begin
  //ShowMessage('Timer1Timer');
  // 30.407: don't refresh most, since it causes havox if people are creating new records.

  if (Page.ActivePage=tsEdit) then begin
    //PostMyTable(quSystems);
    //quSystems.RefreshRecord
  end else if (Page.ActivePage=tsSwitches2) then begin
    //PostMyTable(quSwitch);
    //quSwitch.RefreshRecord;
  end else if (Page.ActivePage=tsPorts2) then begin
    //PostMyTable(quPorts);
    //quPorts.RefreshRecord
  //end else if (Page.ActivePage=tsPatchCables2) then begin
    //PostMyTable(quCable);
    //quCable.RefreshRecord;
  end else if (Page.ActivePage=tsHistory) then begin
    // PostMyTable(quHistory);
    // Ignore, it keeps giving a 'Refresh Failed found 500 records'
    dm0.quGuilog.RefreshRecord;
  // 10.7.07/sb. disable since Delphi is generatng an invalid query:
  // SELECT * FROM naclog where naclog.id=144391 order by id desc limit 0.500;
  //end else if (Page.ActivePage=tsVmpslog) then begin
    //PostMyTable(taVmpsLog);
  //  dm0.taVmpsLog.RefreshRecord;
  //end else if (Page.ActivePage=tsUsers) then begin
    //PostMyTable(quUsers);
    //quUsers.RefreshRecord;
  //end else if (Page.ActivePage=tsVlan) then begin
    //PostMyTable(quVlan);
    //quVlan.RefreshRecord;
  //end else if (Page.ActivePage=tsLookups) then begin
    //PostMyTable(taSys_os);
    //taSys_os1.RefreshRecord;
    //taSys_os2.RefreshRecord;
    //taSys_os.RefreshRecord;
    //taSys_os3.RefreshRecord;
  end;
end;


procedure TfmInventory.bbExpiredSystemsClick(Sender: TObject);
var
  sep: string;
begin
end;


procedure TfmInventory.bbReloadClick(Sender: TObject);
begin
  dm0.quSystems.Refresh;
end;




procedure TfmInventory.SpeedButton1Click(Sender: TObject);
begin
   UpdateDNS;
end;

procedure TfmInventory.lDns2Click(Sender: TObject);
var
  newname: string;
begin
  //if (deMachine.Text='unknown') then begin
  with dm0.quSystems do
  if Length(lDns2.Caption)>0 then begin
    // strip Domain part of the name
    newname:=AnsiLowerCase(Copy(lDns2.Caption, 1, Pos('.', lDns2.Caption)-1) );
      Edit;
      FieldbyName('name').AsString:=newname;
      Post;
    update_history('Changed system name from ' +FieldbyName('name').AsString +' to ' +newname);
    ShowMessage('Changed system name from ' +FieldbyName('name').AsString +' to ' +newname);

  end else begin
    ShowMessage('There is no DNS name: please query DNS first');
  end;
end;




procedure TfmInventory.Encryptuser1Click(Sender: TObject);
begin
  //ShowMessage(Username +' ' +password +' ==> ' +encrypt(Username)   +' ==> ' +encrypt(password) );
  fmKeyGeneration.ShowModal;
end;


procedure TfmInventory.update_queries;
var
  id: integer;
  str1, str2: string;
begin
  lnmap.Caption:='';
  lepo.Caption:='';
  meNmap.Clear;
  meInventory.Clear;
  meWsus.Clear;

  if not (Page.ActivePage=tsEdit) then
     exit;

  //  First Operating System Tab
  //
  // ---------------- Query nmap OS scanned ------------------------
  //ShowMessage('Query OS values');
  with dm0.MyQuery1 do begin
    lnmap.Caption:='';
    lepo.Caption:='';

  if (NmapEnabled) then begin
    SQL.Clear;
    SQL.Add('SELECT CONCAT(os, '' '', timestamp) as os_nmap from nac_hostscanned WHERE sid='''
      +dm0.quSystems.FieldByName('id').AsString
      +'''');
    //ShowMessage(SQL.Text);
    Open;
    lnmap.Caption:=FieldByName('os_nmap').AsString;
    Close;

    meNmap.Clear;
      SQL.Clear;
      SQL.Add('SELECT CONCAT_WS(''\t'', o.timestamp, p.name, s.port, o.banner)  '
        +' AS line from nac_openports o  inner join services s on o.service=s.id '
        +' inner join protocols p on s.protocol=p.protocol WHERE o.sid='''
        +dm0.quSystems.FieldByName('id').AsString
        +'''');
      //ShowMessage(SQL.Text);
      Open;
      while not EOF do begin
        meNmap.Lines.Add(FieldByName('line').AsString);
        next;
      end;
      Close;
  end;

  if (AntiVirusEnabled) then begin
    SQL.Clear;
    //  Not needed: OSVersion
    (*SQL.Add('SELECT CONCAT_WS('',  '', OSType, OSServicePackVer, OSBuildNum, LastUpdate) as '
      +'os_version from EpoComputerProperties WHERE sid='''
      +dm0.quSystems.FieldByName('id').AsString    +'''');
    *)
    SQL.Add('SELECT CONCAT_WS('',  '', ostype, osservicepackver, osbuildnum, lastepocontact) as '
      +'os_version from epo_systems WHERE sid='''
      +dm0.quSystems.FieldByName('id').AsString  +'''');
    //ShowMessage(SQL.Text);
    Open;
    lepo.Caption:=FieldByName('os_version').AsString;
    Close;
  end;    



  if (StaticInvEnabled) then begin
    // ---------------- Query Inventory ------------------------
    //
    meInventory.Clear;
    id:=0;
    if Length(dm0.quSystems.FieldByName('inventory').AsString) > 0 then
      id:=dm0.quSystems.FieldByName('inventory').AsInteger;

    if (id>0) then begin      // did we find that host?
      SQL.Clear;
      SQL.Add('SELECT Label, Description, Owner, SapNr, tag '
        +' from ino_InvDaten WHERE InvNr=''' +IntToStr(id)
        +'''');
      //ShowMessage(SQL.Text);
      Open;
      while not EOF do begin
        meInventory.Lines.Add('Description= ' +FieldByName('Label').AsString +', '
          +FieldByName('Description').AsString );
        meInventory.Lines.Add('Owner= ' +FieldByName('Owner').AsString);
        meInventory.Lines.Add('SAP Nr= ' +FieldByName('SapNr').AsString +', Tag= '
          +FieldByName('tag').AsString );
        next;
      end;
      Close;
    end;
  end;


  if (AntiVirusEnabled) then begin

    SQL.Clear;
    SQL.Add('SELECT Version FROM epo_versions WHERE Product=''ENGINE'' ');
    //ShowMessage(SQL.Text);
    Open;
    str1:=FieldByName('Version').AsString;
    Close;
    SQL.Clear;
    SQL.Add('SELECT Version FROM epo_versions WHERE Product=''DAT'' ');
    //ShowMessage(SQL.Text);
    Open;
    str2:=FieldByName('Version').AsString;
    Close;

    // ----------- Query Anti-virus status ------------
    lav1.Caption:='Last epo contact: no information';
    lav1.Caption:='Agent Version: no information';
    lav2.Caption:='DAT Version: no information';
    lav3.Caption:='Last Synced: no information';
      lav4.Caption:='Computer Name: no information';
      lav5.Caption:='DNS/IP Name: no information';
      lav6.Caption:='IP Address: no information';
      lav7.Caption:='Last User: no information';
      lav8.Caption:='from ePO: no information';
      lav9.Caption:='no information';
      lav10.Caption:='';
    SQL.Clear;
    //SQL.Add('SELECT LastUpdate,AgentVersion,LastDATUpdate,DATVersion,ComputerName,IPAddress,UserName,IPHostName from EpoComputerProperties WHERE sid='''
    //  +dm0.quSystems.FieldByName('id').AsString   +'''');
    SQL.Add('SELECT * from epo_systems WHERE sid='''
      +dm0.quSystems.FieldByName('id').AsString   +'''');
    //ShowMessage(SQL.Text);
    Open;
    while not EOF do begin
    (*
      lav0.Caption:='Last epo contact: '+FieldByName('LastUpdate').AsString;
      lav1.Caption:='Agent Version: ' +FieldByName('AgentVersion').AsString;
      lav2.Caption:='DAT Version: '   +FieldByName('DATVersion').AsString;
      lav3.Caption:='Updated: '       +FieldByName('LastDATUpdate').AsString;
      lav4.Caption:='Computer Name: ' +FieldByName('ComputerName').AsString;
      lav5.Caption:='DNS/IP Name: '   +FieldByName('IPHostName').AsString;
      lav6.Caption:='IP Address: '    +FieldByName('IPAddress').AsString;
      lav7.Caption:='Last User: '     +FieldByName('UserName').AsString;
      lav8.Caption:='from ePO: '      +FieldByName('UserName').AsString;
      lav9.Caption:=FieldByName('IPHostName').AsString +', ' +FieldByName('IPAddress').AsString; *)

      lav3.Caption:='Last epo contact: '+FieldByName('lastepocontact').AsString;
      lav1.Caption:='Engine Version: ' +FieldByName('virusenginever').AsString +' (newest=' +str1 +')';
      lav2.Caption:='DAT Version:    '   +FieldByName('virusdatver').AsString +' (newest=' +str2 +')';
      lav0.Caption:='Last FreeNAC/Epo sync: ' +FieldByName('lastsync').AsString;
      lav4.Caption:='Computer Name: ' +FieldByName('nodename').AsString;
      lav5.Caption:='DNS/IP Name:   ' +FieldByName('domainname').AsString;
      lav6.Caption:='IP Address:    ' +FieldByName('ip').AsString;
      lav7.Caption:='Last User:     ' +FieldByName('username').AsString;
      lav10.Caption:='MB free disk: ' +FieldByName('freediskspace').AsString;

      lav8.Caption:='from ePO: '      +FieldByName('username').AsString;
      lav9.Caption:=FieldByName('nodename').AsString +', ' +FieldByName('ip').AsString;

      next;
    end;
    Close;
  end else begin
    lav1.Caption:='Agent Version: feature not enabled';
    lav2.Caption:='DAT Version:   feature not enabled';
    lav3.Caption:='Updated:       feature not enabled';
    lav8.Caption:='from ePO: module not enabled';
  end;

  if (wsus_enabled) then begin
    // ----------- Query WSUS status ------------
    SQL.Clear;
    //SQL.Add('select ct.ipaddress, ct.fulldomainname, ct.LastSyncTime, ct.datetime, om.OSLongName, om.OSBuildNumber, ct.OsLocale as language, ct.ComputerMake, ct.ComputerModel, ct.BiosName ');
    //SQL.Add(' from nac_wsuscomputertarget ct inner join nac_wsusosmap om on om.OSid=ct.OSid inner join systems s on ct.sid=s.id and s.id='''
    //  +dm0.quSystems.FieldByName('id').AsString  +'''');
    SQL.Add('SELECT * FROM wsus_systems WHERE sid=''' +dm0.quSystems.FieldByName('id').AsString  +'''');
    //ShowMessage(SQL.Text);
    Open;
    while not EOF do begin
      meWsus.Lines.Add('IP address: ' +FieldByName('ip').AsString
        +', Computer: ' +FieldByName('computermake').AsString
        +', ' +FieldByName('computermodel').AsString
        //+', Bios: ' +FieldByName('BiosName').AsString
      );
      meWsus.Lines.Add('Last Wsus contact: ' +FieldByName('lastwsuscontact').AsString);
      meWsus.Lines.Add('Updates not downloaded: ' +FieldByName('notinstalled').AsString);
      meWsus.Lines.Add('Updates downloaded but not installed: ' +FieldByName('downloaded').AsString);
      meWsus.Lines.Add('Updates installed but not activated (reboot required): '
        +FieldByName('installedpendingreboot').AsString);
      meWsus.Lines.Add('Updates failed: ' +FieldByName('failed').AsString);

      (*meWsus.Lines.Add('Operating System: ' +FieldByName('OSLongName').AsString);
      llwsus.caption:=FieldByName('OSLongName').AsString
        +', ' +FieldByName('OSBuildNumber').AsString
        +', ' +FieldByName('LastSyncTime').AsString  *)
      ;

      next;
    end;

    meWsus.Lines.Add(' ');
    meWsus.Lines.Add('Windows patches pending:');
    meWsus.Lines.Add('(title, severity, creation/receive dates:');
    meWsus.Lines.Add('______________________________________________________');
    SQL.Clear;
    SQL.Add('SELECT title,u.description, u.msrcseverity, u.creationdate, u.receiveddate ');
    SQL.Add('from wsus_systemToUpdates s left join wsus_neededUpdates u ');
    SQL.Add('on s.localupdateid = u.localupdateid  WHERE s.sid='''
      +dm0.quSystems.FieldByName('id').AsString +'''');
    //ShowMessage(SQL.Text);
    Open;
    while not EOF do begin
      meWsus.Lines.Add(
             FieldByName('title').AsString
      +', ' +FieldByName('description').AsString
      +', ' +FieldByName('msrcseverity').AsString
      +', ' +FieldByName('creationdate').AsString
      +', ' +FieldByName('receiveddate').AsString
      );
      meWsus.Lines.Add(' ');
      next;
    end;

    Close;


  end else begin
    //
  end;

  end;

  Refresh_UsersInPortLocation;

end;



procedure TfmInventory.page2Change(Sender: TObject);
begin
  update_queries;
end;

procedure TfmInventory.p2OperatingSystemShow(Sender: TObject);
begin
  update_queries;
end;

procedure TfmInventory.p2NmapShow(Sender: TObject);
begin
  update_queries;
end;

procedure TfmInventory.gridOverview3DblClick(Sender: TObject);
begin
  //fmInventory.quSystems.Locate('mac', fmInventory.taV_invent1.FieldByName('mac').AsString, [loPartialKey,loCaseInsensitive]);
  //ShowMessage(fmInventory.taV_invent1.FieldByName('mac').AsString);
  Page.ActivePage:=tsEdit;
  UpdateMACLookup;
  tsEditUpdate;
  update_queries;
end;

procedure TfmInventory.bbNmapClick(Sender: TObject);
begin
  if (not NmapEnabled) then begin
    ShowMessage('Sorry, this feature is not enabled.');
    exit;
  end;

  with dm0.MyQuery1 do begin
    if Length(dm0.quSystems.FieldbyName('r_ip').Asstring) = 0 then begin
      ShowMessage('An IP address is not available for this system, a scan is not possible');
    end  ;

    SQL.Clear;
    SQL.Add('UPDATE systems set scannow=1 WHERE r_ip='''
      +dm0.quSystems.FieldbyName('r_ip').Asstring
      +''' LIMIT 1'
    );
    //ShowMessage(SQL.Text);
    ShowMessage('The IP address  ' +dm0.quSystems.FieldbyName('r_ip').Asstring
      +' will be scanned within the next five minutes.');
    Execute;
    Close;
  end;
end;

procedure TfmInventory.bbInvClick(Sender: TObject);
begin
  if (not StaticInvEnabled) then begin     // enable links to static inv
    ShowMessage('This feature is not enabled, sorry.');
    exit;
  end;

  with dm0.MSConnection1 do begin
    Connected:=true;
  end;
  with dm0.quStaticInv do begin
    //SQL.Clear;
    //SQL.Add('SELECT Updated, InvNr, owner, Description, Label, Tag, Visum from InvToVMPSChanges');
    // WHERE ChangeType='new'
    //ShowMessage(SQL.Text);
    Execute;
    fmStaticInventory.ShowModal;
    Close;
  end;

end;

procedure TfmInventory.NavEdit2Click(Sender: TObject;
  Button: TNavigateBtn);
begin
  tsEditUpdate;
end;

procedure TfmInventory.cbStatus_enabledClick(Sender: TObject);
begin
  tsEditUpdate;
end;

procedure TfmInventory.deUserLookupCloseUp(Sender: TObject);
begin
  tsEditUpdate;
end;

procedure TfmInventory.bbExpiredUsersOnlyClick(Sender: TObject);
var
  sep: string;
begin
end;


procedure TfmInventory.bbOldSystemsClick(Sender: TObject);
var
  sep: string;
begin
end;

procedure TfmInventory.bbExportSystemsToExcelClick(Sender: TObject);
var
 WorkBk : _WorkBook; //  Define a WorkBook
 WorkSheet : _WorkSheet;
 I, J, K, Rows, Cols : Integer;
 IIndex,OleRange : OleVariant;
 TabGrid : Variant;
 query: string;
begin

  Rows:=0; Cols:=0;
  with dm0.MyQuery1 do begin
    SQL.Clear;
    SQL.Add('SELECT COUNT(*) as numrows FROM systems');
    Open;
    if Length(FieldByName('numrows').AsString)>0 then
      Rows:=FieldByName('numrows').AsInteger;
    Close;
  end;
  if Rows > 4000 then Rows:=4000;     // TBD; realy only 4k?

  ShowMessage('The entire systems table (' +IntToStr(Rows)
   +') will be exported to an excel file. '
   +' Note: this will take ~ one minute, and max. 4000 extries can be exported (Excel limitation)');


  query:='SELECT name, comment, LastSeen, description as useraccount, '
    +' (SELECT Surname         from users WHERE systems.description=users.username) as UserSurname,  '
    +' (SELECT GivenName       from users WHERE systems.description=users.username) as UserForename, '
    +' (SELECT Department      from users WHERE systems.description=users.username) as UserDept,     '
    +' (SELECT LastSeenDirectory from users WHERE systems.description=users.username) as UserLastSeenDirex, '
    +'(SELECT value from vlan WHERE vlan.id=vlan)      as vlanname, LastVlan, '
    +'(SELECT vlan_group from vlan WHERE vlan.id=vlan) as vlangroup, '
    +' (SELECT value from vstatus WHERE vstatus.id=status) as statusname, '
    +' inventory,  mac, '
    +' office, '
    +' (SELECT name from switch WHERE systems.switch=switch.ip) as SwitchName, '
    +'  os1, os2, (SELECT value from sys_os WHERE id=os) as OsName  '
    +' FROM systems ORDER BY LastSeen LIMIT ' +IntToStr(Rows);

   with dm0.MyQuery1 do begin
     SQL.Clear;
     SQL.Add(query);
     Open;
     Cols:=FieldCount;
     //ShowMessage('Rows, Cols=' +IntToStr(Rows) +' ' +IntToStr(Cols));
     TabGrid := VarArrayCreate([0, Rows, 0 , Cols-1],VarOleStr);

     I:=0;
     for j:=0 to Cols-1 do begin             // Column Titles
       TabGrid[I,J]:=Fields[J].DisplayName;
     end;

     First;  I:=I+1;                         // Field data
     while not EOF do begin
       for j:=0 to Cols-1 do begin
         TabGrid[I,J]:=Fields[J].AsString;
       end;
       I:=I+1; next;
     end;
      EnableControls;
   end;

   XLApp.Connect;                             // Connect to the server TExcelApplication
   XLApp.WorkBooks.Add(xlWBatWorkSheet,0);    // Add WorkBooks
   WorkBk := XLApp.WorkBooks.Item[1];         // Select the first WorkBook   IIndex
   WorkSheet := WorkBk.WorkSheets.Get_Item(1) as _WorkSheet;      // Define the first WorkSheet

   // Copy data to Excel
   Worksheet.Range['A1',Worksheet.Cells.Item[Rows,Cols]].Value := TabGrid;

   // Excel formatting: general
   WorkSheet.Name := 'Vmps Systems list';
   Worksheet.Columns.Font.Bold := False;
   Worksheet.Columns.HorizontalAlignment := xlLeft;
   //Worksheet.Columns.HorizontalAlignment := xlCenter;
   //Worksheet.Columns.Columns.Range['J1','O' +IntToStr(Rows)].HorizontalAlignment := xlLeft;
   //WorkSheet.Columns.ColumnWidth := 12;

   // Highlight first row  ('A'=65)
   WorkSheet.Range['A1', Chr(65+Cols) +'1'].Font.Bold := True;
   WorkSheet.Range['A1', Chr(65+Cols) +'1'].Font.Color:= clRed;

   WorkSheet.Columns.Autofit;
   //Worksheet.Columns.Columns.Range['M1','M' +IntToStr(Cols)].ColumnWidth:=9;

   // Enable a (cool) autofilter drop-down menu in the Excel title.
   OleRange:=Worksheet.Columns.Columns.Range['A1','P1'];
   OleRange.AutoFilter;

   XLApp.Visible[0]:=true;             // Show it
   XLApp.UserControl:=true;

   //SHowMessage('Press OK');
   //XLApp.Quit;
   XLApp.Disconnect;
   TabGrid := Unassigned;
end;


procedure TfmInventory.bbAllSystemsQueryClick(Sender: TObject);
var
  sep: string;
begin
end;

procedure TfmInventory.cbOnChange(Sender: TObject);
var str: string;
begin
  (*str:=(Sender as TcxComboBox).Text;
  if Length(str)>0 then begin
    gridPorts.Columns[0].FilterExpression:=str;
    gridPorts.ApplyFilter;
  end; *)
end;


procedure TfmInventory.bbClearPortFilterClick(Sender: TObject);
begin
  (*gridPorts.ClearFilters;
  gridPorts.ApplyFilter;
  cbBySwitch2.ItemIndex:=-1;  *)
end;



procedure TfmInventory.p2LastSeenShow(Sender: TObject);
begin
  Refresh_UsersInPortLocation;
end;

procedure TfmInventory.gridPatchesDBTableView1UsersInOfficeGetDataText(
  Sender: TcxCustomGridTableItem; ARecordIndex: Integer;
  var AText: String);
var
  office: String;
begin
  if cbShowUsers.Checked then        // only lookup if enabled, cos its slow
  with Sender.GridView do begin
    AText:='';
    office:=Variants.VarToStr(DataController.Values[ARecordIndex,gridPatchesDBTableView1OfficeIndex.Index]);
    if StrToIntDef(office, 0)>1 then begin
      //ShowMessage(office);
      dm0.MyQuery1.SQL.Clear;
      dm0.MyQuery1.SQL.Add('SELECT GROUP_CONCAT(DISTINCT Surname)  as UserList from users WHERE location='''
        +office
        +''' ');
      dm0.MyQuery1.Open;
      AText:=AnsiLeftStr(dm0.MyQuery1.FieldByName('UserList').AsString, 60);   //  only first 60
      //ShowMessage('Office=' +office +', ' +AText); *)
    end;
  end;
end;

procedure TfmInventory.gridPatchesDBTableView1CustomDrawCell(
  Sender: TcxCustomGridTableView; ACanvas: TcxCanvas;
  AViewInfo: TcxGridTableDataCellViewInfo; var ADone: Boolean);
var
  AColumnIndex, patchtype: integer;
begin
  with Sender do begin                  
    // column names
    //ShowMessage(TcxGridDBColumn(AViewInfo.Item).DataBinding.FieldName);
    if (TcxGridDBColumn(AViewInfo.Item).DataBinding.FieldName='type') then begin
    
      //AViewInfo.Item.Index=gridPatchesDBTableView1TypeIndex.Index
      //ShowMessage('AViewInfo.Text=' +AViewInfo.Text);
      if VarIsNull(AViewInfo.Value) then
        patchtype:=0
      else
        patchtype:=AViewInfo.Value;

      //ACanvas.Canvas.Brush.Color :=clred;  // background colour
      ACanvas.canvas.Font.Color := clblack;  // default
      if (patchtype>0) then begin
        //ShowMessage('patchtype=' +IntToStr(patchtype));
        if patchtype=8 then begin  // phone
          ACanvas.canvas.Font.Color := TColor($0000FF00);;  // A bright green clGreen
        end else if patchtype=10 then begin       // Infnet
          ACanvas.canvas.Font.Color := clGray;
        end else if patchtype=6 then begin        // pointtopoint
          ACanvas.canvas.Font.Color:=clBlue;
        end else if patchtype=4 then begin
          ACanvas.canvas.Font.Color:=clBlue;
        end;
(*
|  2 | static       |
|  3 | dynamic      |
|  4 | adsl         |
|  6 | pointtopoint |
|  7 | telco        |
|  8 | phone        |
|  9 | free         |
| 10 | Infnet       |
| 11 | Spez.Net     |*)
      end;
    end;
  end; 
end;

procedure TfmInventory.Button4Click(Sender: TObject);
begin
  SaveDialog1.FileName:='NACswitches.xls';
  SaveDialog1.Execute;
  ExportGridToExcel(SaveDialog1.FileName, gridSwitches);
  //ShowMessage(SaveDialog1.FileName);
end;

procedure TfmInventory.Button5Click(Sender: TObject);
begin
  SaveDialog1.FileName:='NACports.xls';
  SaveDialog1.Execute;
  ExportGridToExcel(SaveDialog1.FileName, gridSwitches);
end;

procedure TfmInventory.Button2Click(Sender: TObject);
begin
  SaveDialog1.FileName:='NACpatchcables.xls';
  SaveDialog1.Execute;
  ExportGridToExcel(SaveDialog1.FileName, gridPatches);
end;


procedure TfmInventory.Q2ExpiredUsersClick(Sender: TObject);
begin
  gridQuery2DBTableView1.ClearItems; 
  with dm0.MyQuery1 do begin
    Close;
    SQL.Clear;
    SQL.Add('SELECT users.Surname, users.GivenName, users.Department, users.LastSeenDirectory FROM users '
      +' WHERE (TO_DAYS(users.LastSeenDirectory) < TO_DAYS(NOW())-14)');
    Open;

    gridQuery2DBTableView1.DataController.DataSource:=dm0.dsMyQuery1;
    gridQuery2DBTableView1.DataController.CreateAllItems();
  end;
end;

procedure TfmInventory.Button7Click(Sender: TObject);
begin
  SaveDialog1.FileName:='NACquery.xls';
  SaveDialog1.Execute;
  ExportGridToExcel(SaveDialog1.FileName, gridQuery2);
end;

procedure TfmInventory.gridPortsDBTableView1PatchOfficeListGetDataText(
  Sender: TcxCustomGridTableItem; ARecordIndex: Integer;
  var AText: String);
var
  port:  integer;
begin
  if cbShowPatchDetails.Checked then        // only lookup if enabled, cos its slow
  with Sender.GridView do begin
    AText:='';  port:=0;
    if VarIsNull(DataController.Values[ARecordIndex,gridPortsDBTableView1id.Index]) then
      exit;
    port:=DataController.Values[ARecordIndex,gridPortsDBTableView1id.Index];
    if port>0 then begin
      //ShowMessage(office);
      dm0.MyQuery1.SQL.Clear;
      dm0.MyQuery1.SQL.Add('SELECT CONCAT(outlet, '' '', location.name, '' '', p.comment) as PatchLookup '
        +' from patchcable p LEFT JOIN location ON p.office = location.id WHERE p.port='''
        +inttostr(port)
        +''' ');
      dm0.MyQuery1.Open;
      AText:=AnsiLeftStr(dm0.MyQuery1.FieldByName('PatchLookup').AsString, 60);   //  only first 60
    end;
  end;
end;

procedure TfmInventory.cxUsersPropertiesChange(Sender: TObject);
begin
  tsEditUpdate;
end;

procedure TfmInventory.bbExpiredSysetmsClick(Sender: TObject);
begin
  gridQuery2DBTableView1.ClearItems;
  with dm0.MyQuery1 do begin
    Close;
    SQL.Clear;
    SQL.Add('SELECT systems.name, users.Surname, users.GivenName, users.Department,  '
      +' users.LastSeenDirectory, systems.mac, systems.comment, vstatus.value, '
      +' systems.LastSeen, systems.class, systems.os FROM systems '
      +' LEFT JOIN vstatus on status = vstatus.id '
      +' LEFT JOIN users   on uid = users.id '
      +' WHERE (TO_DAYS(users.LastSeenDirectory) < TO_DAYS(NOW())-14) '
      +' ORDER BY systems.LastSeen DESC');
    Open;

    gridQuery2DBTableView1.DataController.DataSource:=dm0.dsMyQuery1;
    gridQuery2DBTableView1.DataController.CreateAllItems();
  end;
end;

procedure TfmInventory.bbUnusedSystemsClick(Sender: TObject);
begin
  gridQuery2DBTableView1.ClearItems;
  with dm0.MyQuery1 do begin
    Close;
    SQL.Clear;
    SQL.Add('SELECT systems.name, users.Surname, users.GivenName, users.Department,  '
      +' users.LastSeenDirectory, systems.mac, systems.comment, vstatus.value, '
      +' systems.LastSeen, systems.class, systems.os FROM systems  '
      +' LEFT JOIN vstatus on status = vstatus.id '
      +' LEFT JOIN users   on uid = users.id '
      +' WHERE (TO_DAYS(systems.LastSeen) < TO_DAYS(NOW())-30) '
      +' ORDER BY systems.LastSeen DESC');
    Open;

    gridQuery2DBTableView1.DataController.DataSource:=dm0.dsMyQuery1;
    gridQuery2DBTableView1.DataController.CreateAllItems();
  end;
end;

procedure TfmInventory.bbReportAV1Click(Sender: TObject);
begin
  gridQuery2DBTableView1.ClearItems;
  with dm0.MyQuery1 do begin
    Close;
    SQL.Clear;

    (*SQL.Add('select ComputerName,UserName,OSType,LastUpdate,LastDATUpdate,DATVersion,  '
      +' (SELECT LastSeen from systems where systems.id=sid) AS LastSeen '
      +' from EpoComputerProperties WHERE (TO_DAYS(LastUpdate) < TO_DAYS(NOW())-7) order by LastSeen DESC');  *)

     SQL.Add('SELECT nodename as ''Hostname'', domainname as ''Domain'', ip as ''IP Address'', ');
     SQL.Add('lastepocontact as ''Last EPO contact'', agentversion as ''Agent Version'', virusver as ''Virus Scanner Version'', ');
     SQL.Add('virushotfix as ''Virus Scanner Hotfix'', virusenginever as ''Engine Version'', virusdatver as ''DAT Version'', ');
     SQL.Add('concat_ws('','', ostype, osversion, osservicepackver, osbuildnum) as ''Operating System'',  ');
     SQL.Add('freediskspace as ''Mbyte left on root device'',  username as ''Last User'',  ');
     SQL.Add('(SELECT LastSeen from systems where systems.id=sid) AS ''LastSeen'' from epo_systems ');
     //SQL.Add('WHERE (TO_DAYS(lastepocontact) < TO_DAYS(NOW())-7) order by LastSeen DESC');
     SQL.Add('order by LastSeen DESC');
    //ShowMessage(SQL.text);
    Open;

    gridQuery2DBTableView1.DataController.DataSource:=dm0.dsMyQuery1;
    gridQuery2DBTableView1.DataController.CreateAllItems();
  end;
end;





procedure TfmInventory.bbAllSystemsClick(Sender: TObject);
begin
  gridQuery2DBTableView1.ClearItems;
  with dm0.MyQuery1 do begin
    Close;
    SQL.Clear;
    SQL.Add(
    // l2.name as PortLocation,
 'SELECT s.name, s.inventory, s.comment, v1.id as vlanid,  v2.default_name as LastVlan,'
+'  v1.default_name as vlanname, vstatus.value as statusname,'
+'  s.mac, ChangeDate, LastSeen as LastSeenL2, building.name as building,'
+'  port.name as LastPort, port.comment as PortComment,'
+'  switch.name as SwitchName, l3.name as SwitchLocation,'
+' (SELECT CONCAT(patchcable.outlet, '', '', patchcable.comment) from patchcable WHERE patchcable.port = port.id LIMIT 1)  as PatchCable,'
+'  users1.username as user_name,      users1.TelephoneNumber as UserTel,'
+'  users1.Surname as UserSurname,     users1.GivenName as UserForename,    users1.Department as UserDept,'
+'  users1.rfc822mailbox as UserEmail, users1.HouseIdentifier as UserHouse, users1.PhysicalDeliveryOfficeName as UserOffice,'
+'  users2.username as ChangeUser, '
+'  os.value as OsName, os1.value as os1name, os2.value as os2name, os3.value as os3name, s.os4,'
+'  cl1.value as ClassName, cl2.value as Class2Name, s.expiry, r_ip as Last_IP, r_timestamp as LastSeenL3, '
+'  s.history'
+'  FROM systems s'
+'  LEFT JOIN vlan v1      on s.vlan = v1.id '
+'  LEFT JOIN vlan v2      on s.LastVlan = v2.id   '
+'  LEFT JOIN vstatus      on s.status = vstatus.id'
+'  LEFT JOIN users users1 on s.uid = users1.id  '
+'  LEFT JOIN users users2 on s.ChangeUser = users2.id'
+'  LEFT JOIN location l1  ON s.office = l1.id '
+'  LEFT JOIN building     ON l1.building_id = building.id'
+'  LEFT JOIN port         ON s.LastPort=port.id  '
+'  LEFT JOIN switch       ON port.switch = switch.id'
+'  LEFT JOIN location l3  ON switch.location = l3.id'
+'  LEFT JOIN sys_os   os  ON s.os = os.id '
+'  LEFT JOIN sys_os1  os1 ON s.os1 = os1.id '
+'  LEFT JOIN sys_os2  os2 ON s.os2 = os2.id'
+'  LEFT JOIN sys_os3  os3 ON s.os3 = os3.id '
+'  LEFT JOIN sys_class  cl1 ON s.class  = cl1.id'
+'  LEFT JOIN sys_class2 cl2 ON s.class2 = cl2.id  '
+' ');
//+'  LEFT JOIN patchcable   ON patchcable.port = port.id'
//+'  LEFT JOIN location l2  ON patchcable.office = l2.id'
    Open;

    gridQuery2DBTableView1.DataController.DataSource:=dm0.dsMyQuery1;
    gridQuery2DBTableView1.DataController.DataModeController.SmartRefresh := True;
    gridQuery2DBTableView1.DataController.CreateAllItems();
  end;
end;



procedure TfmInventory.gridPortsDBTableView1CellDblClick(
  Sender: TcxCustomGridTableView;
  ACellViewInfo: TcxGridTableDataCellViewInfo; AButton: TMouseButton;
  AShift: TShiftState; var AHandled: Boolean);
var
   AFieldName, ABandCaption: string;
  AColumnIndex: integer;
begin
(*
   AFieldName := TcxGridDBBandedColumn(ACellViewInfo.Item).DataBinding.FieldName;
   ABandCaption := TcxGridDBBandedColumn(ACellViewInfo.Item).Position.Band.Caption;
   *)
   //AColumnIndex := gridPortsDBTableView1TypeIndex.Index;
   //ACellViewInfo.Item.DataBinding.DataController.Values[AViewInfo.GridRecord.Index,
   //  AColumnIndex]
end;

procedure TfmInventory.cxGridPopupMenu1PopupMenus0Popup(
  ASenderMenu: TComponent; AHitTest: TcxCustomGridHitTest; X, Y: Integer);
begin
  ShowMessage('cxGridPopupMenu1PopupMenus0Popup');
end;

procedure TfmInventory.popupPortsPopup(Sender: TObject);
begin
  //ShowMessage('popupPortsPopup');
  // Don't need to do anything, yet, before starting the menu
end;

procedure TfmInventory.popupPortsChange(Sender: TObject; Source: TMenuItem;
  Rebuild: Boolean);
begin
  //ShowMessage('PopupMenu1Change');
end;



procedure TfmInventory.cxLabel1Click(Sender: TObject);
begin
  ShellExecute(0, 'open', PChar('http://freenac.net'), '', '', SW_SHOW)
end;

procedure TfmInventory.cxLabel2Click(Sender: TObject);
begin
  ShellExecute(0, 'open', PChar('http://freenac.net/phpBB2/'), '', '', SW_SHOW)
end;

procedure TfmInventory.cxLabel3Click(Sender: TObject);
begin
  ShellExecute(0, 'open', PChar('http://freenac.net/en/usersguide'), '', '', SW_SHOW)
end;



// first attempt: but causes too many DNS queries
procedure TfmInventory.DoColumnGetDisplayText1(Sender: TcxCustomGridTableItem;
  ARecordIndex: Integer; var AText: string);
begin
  // hostname is first field, look up DNS value
  fmInventory.StatusLine.SimpleText:='DNS lookup ' +VarToStr(Sender.GridView.DataController.Values[ARecordIndex, 0]);
  AText := GetDNSName( VarToStr(Sender.GridView.DataController.Values[ARecordIndex, 0]) );
  fmInventory.StatusLine.SimpleText:='';
end;

procedure TfmInventory.DoColumnGetDisplayText2(Sender: TcxCustomGridTableItem;
  ARecordIndex: Integer; var AText: string);
begin
  fmInventory.StatusLine.SimpleText:='DNS IP lookup' +VarToStr(Sender.GridView.DataController.Values[ARecordIndex, 20]);
  AText := GetDNSAddr(VarToStr(Sender.GridView.DataController.Values[ARecordIndex, 10]));
  fmInventory.StatusLine.SimpleText:='';
end;


// 2nd attempt:
procedure TfmInventory.PopulateColumnValues1(AColumn: TcxGridDBColumn);
var
  I: Integer;
begin
  with gridQuery2DBTableView1.DataController do
  begin
    //ShowMessage('PopulateColumnValues1 - column ' +IntToStr(AColumn.Index) +' for ' +IntToStr(RecordCount) +' records');
    BeginUpdate;
    try
      for i := 0 to RecordCount -1 do begin
        if VarIsNull(Values[i, 3]) then begin
          Values[i, AColumn.Index] :='';
        end else begin
          fmInventory.StatusLine.SimpleText:='DNS lookup of ' +VarToStr(Values[i, 3])
            +' - row ' +IntToStr(i) +' of ' +IntToStr(RecordCount) +' rows' ;
          Values[i, AColumn.Index] := GetDNSAddr(VarToStr(Values[i, 3]));
        end;
        //ShowMessage('wait');
      end;
    finally
      EndUpdate;
      fmInventory.StatusLine.SimpleText:='';
    end;
  end;
end;

procedure TfmInventory.PopulateColumnValues2(AColumn: TcxGridDBColumn);
var
  I: Integer;
begin
  with gridQuery2DBTableView1.DataController do
  begin
    //ShowMessage('PopulateColumnValues1 - column ' +IntToStr(AColumn.Index) +' for ' +IntToStr(RecordCount) +' records');
    BeginUpdate;
    try
      for i := 0 to RecordCount -1 do begin
        if VarIsNull(Values[i, 1]) then begin
          Values[i, AColumn.Index] :='';
        end else begin
          fmInventory.StatusLine.SimpleText:='DNS lookup of ' +VarToStr(Values[i, 1])
            +' - row ' +IntToStr(i) +' of ' +IntToStr(RecordCount) +' rows' ;
          Values[i, AColumn.Index] := GetDNSName(VarToStr(Values[i, 1]));
        end;
      end;

    finally
      EndUpdate;
      fmInventory.StatusLine.SimpleText:='';
    end;
  end;
end;

procedure TfmInventory.bbDNSnamingClick(Sender: TObject);
var
  AColumn: TcxGridDBColumn;
begin
  if MessageDlg('This report of DNS name accuracy for devices seen in the last week may be very slow as many DNS lookups are done in real time.'
    +' Check the Status Bar below for progress. Do you wish to continue?',
    mtConfirmation, [mbYes, mbNo], 0) = mrNo then  begin
    exit;
  end;

  gridQuery2DBTableView1.ClearItems;
  with dm0.MyQuery1 do begin
    Close;
    SQL.Clear;
    SQL.Add(
 'SELECT s.name as Nac_name, s.last_hostname, s.last_nbtname, r_ip as Last_IP, '
+'  v1.default_name as vlanname, v2.default_name as LastVlan,'
+'  vstatus.value as statusname,'
+'  s.mac, building.name as Building,'
+'  port.name as LastPort, port.comment as PortComment,'
+'  switch.name as SwitchName, l3.name as SwitchLocation,'
+'  users1.username as UserName,      users1.TelephoneNumber as UserTel,'
+'  users1.Surname as UserSurname,     users1.GivenName as UserForename,    users1.Department as UserDept,'
+'  s.expiry, LastSeen as LastSeen_L2, '
+'  r_timestamp as LastSeen_L3'
+'  FROM systems s'
+'  LEFT JOIN vlan v1      on s.vlan = v1.id '
+'  LEFT JOIN vlan v2      on s.LastVlan = v2.id   '
+'  LEFT JOIN vstatus      on s.status = vstatus.id'
+'  LEFT JOIN users users1 on s.uid = users1.id  '
+'  LEFT JOIN users users2 on s.ChangeUser = users2.id'
+'  LEFT JOIN location l1  ON s.office = l1.id '
+'  LEFT JOIN building     ON l1.building_id = building.id'
+'  LEFT JOIN port         ON s.LastPort=port.id  '
+'  LEFT JOIN switch       ON port.switch = switch.id'
+'  LEFT JOIN location l3  ON switch.location = l3.id'
+'  LEFT JOIN sys_os   os  ON s.os = os.id '
+'  LEFT JOIN sys_os1  os1 ON s.os1 = os1.id '
+'  LEFT JOIN sys_os2  os2 ON s.os2 = os2.id'
+'  LEFT JOIN sys_os3  os3 ON s.os3 = os3.id '
+'  LEFT JOIN sys_class  cl1 ON s.class  = cl1.id'
+'  LEFT JOIN sys_class2 cl2 ON s.class2 = cl2.id  '
+'  WHERE (TO_DAYS(s.LastSeen) > TO_DAYS(NOW())-70)  ');

    Open;

    gridQuery2DBTableView1.DataController.DataSource:=dm0.dsMyQuery1;
    gridQuery2DBTableView1.DataController.CreateAllItems();
    //gridQuery2DBTableView1.DataController.DataModeController.GridMode:=true; // speed
    //gridQuery2DBTableView1.DataController.DataModeController.GridModeBufferCount:=1000;
    //gridQuery2DBTableView1.DataController.DataModeController.SmartRefresh:=true;
    //gridQuery2DBTableView1.OptionsView.ColumnAutoWidth:=true;

    // hide a few minor fields by default
    gridQuery2DBTableView1.GetColumnByFieldName('building').Visible:=false;
    gridQuery2DBTableView1.GetColumnByFieldName('UserName').Visible:=false;
    gridQuery2DBTableView1.GetColumnByFieldName('UserTel').Visible:=false;
    gridQuery2DBTableView1.GetColumnByFieldName('expiry').Visible:=false;
    gridQuery2DBTableView1.GetColumnByFieldName('last_nbtname').Visible:=false;
    gridQuery2DBTableView1.GetColumnByFieldName('UserDept').Visible:=false;
    gridQuery2DBTableView1.GetColumnByFieldName('PortComment').Visible:=false;

    AColumn := gridQuery2DBTableView1.CreateColumn;
    AColumn.Caption := 'DNS Last_IP lookup';
    AColumn.DataBinding.ValueTypeClass := TcxStringValueType;
    AColumn.Options.Editing := False;
    //AColumn.MinWidth  //AColumn.IsMostLeft
    PopulateColumnValues1(AColumn);

    gridQuery2DBTableView1.ApplyBestFit;

    AColumn := gridQuery2DBTableView1.CreateColumn;
    AColumn.Caption := 'DNS NAC_name lookup';
    AColumn.DataBinding.ValueTypeClass := TcxStringValueType;
    AColumn.Options.Editing := False;
    //AColumn.OnGetDataText := DoColumnGetDisplayText2;
    PopulateColumnValues2(AColumn);
  end;
end;
procedure TfmInventory.bbExportVlansToExcelClick(Sender: TObject);
begin
  SaveDialog1.FileName:='NACvlans.xls';
  SaveDialog1.Execute;
  ExportGridToExcel(SaveDialog1.FileName, gridVlan1);
end;

procedure TfmInventory.bbVlanExClick(Sender: TObject);
begin
  SaveDialog1.FileName:='NACvlans.xls';
  SaveDialog1.Execute;
  ExportGridToExcel(SaveDialog1.FileName, gridVlan1);
end;

procedure TfmInventory.bbExportReportClick(Sender: TObject);
begin
  SaveDialog1.FileName:='NACquery.xls';
  SaveDialog1.Execute;
  ExportGridToExcel(SaveDialog1.FileName, gridQuery2);
end;

procedure TfmInventory.bbExportServerLogClick(Sender: TObject);
begin
  SaveDialog1.FileName:='NAC_server_log.xls';
  SaveDialog1.Execute;
  ExportGridToExcel(SaveDialog1.FileName, gridServerLog2);
end;

procedure TfmInventory.bbExportChangeLogClick(Sender: TObject);
begin
  SaveDialog1.FileName:='NAC_change_log.xls';
  SaveDialog1.Execute;
  ExportGridToExcel(SaveDialog1.FileName, gridChangeLog);
end;

procedure TfmInventory.bbExportPortsClick(Sender: TObject);
begin
  SaveDialog1.FileName:='NACports.xls';
  SaveDialog1.Execute;
  ExportGridToExcel(SaveDialog1.FileName, gridPorts);
end;

procedure TfmInventory.bbExportSwitchesClick(Sender: TObject);
begin
  SaveDialog1.FileName:='NACswitches.xls';
  SaveDialog1.Execute;
  ExportGridToExcel(SaveDialog1.FileName, gridSwitches);
end;

procedure TfmInventory.bbExportPatchCablesClick(Sender: TObject);
begin
  SaveDialog1.FileName:='NAC_patchcables.xls';
  SaveDialog1.Execute;
  ExportGridToExcel(SaveDialog1.FileName, gridPatches);
end;

procedure TfmInventory.tsNmapSubnetsShow(Sender: TObject);
begin
    OpenMyTable(dm0.quSubnets);
    dm0.quSubnets.Refresh;
end;

procedure TfmInventory.p2WsusShow(Sender: TObject);
begin
    // scroll back to the top, but does not work?
    meWsus.SelStart:=0;
    meWsus.SelLength:=0;
    //ShowMessage('p2WsusShow');
    //meWsus.SetFocus;
end;

procedure TfmInventory.bbReportWsusClick(Sender: TObject);
begin
  gridQuery2DBTableView1.ClearItems;
  with dm0.MyQuery1 do begin
    Close;
    SQL.Clear;

    // End-devices not check by Wsus for more than 7 days, buut that were on the network in the last month.
    //select ct.fulldomainname, ct.LastSyncTime, s.LastSeen as LastSeenLayer2, ct.ipaddress, om.OSLongName, ct.ComputerMake, ct.ComputerModel  from nac_wsuscomputertarget ct inner join nac_wsusosmap om on om.OSid=ct.OSid inner join systems s on ct.sid=s.id WHERE (TO_DAYS(LastSyncTime) < TO_DAYS(NOW())-7) AND (TO_DAYS(LastSeen) > TO_DAYS(NOW())-30) ORDER BY LastSyncTime
    //SQL.Add('select ct.fulldomainname, ct.LastSyncTime as LastWsusCheck, s.LastSeen as LastSeenLayer2, ct.ipaddress, om.OSLongName, ct.ComputerMake, ct.ComputerModel  '
    //  +' from nac_wsuscomputertarget ct inner join nac_wsusosmap om on om.OSid=ct.OSid inner join systems s on ct.sid=s.id '
    //  +' WHERE (TO_DAYS(LastSyncTime) < TO_DAYS(NOW())-7) AND (TO_DAYS(LastSeen) > TO_DAYS(NOW())-30) ORDER BY LastSyncTime');
    SQL.Add('SELECT hostname as ''Hostname'', ip as ''IP Address'',  ');
    SQL.Add('lastwsuscontact as ''Last WSUS contact'', os as ''Operating System'', ');
    SQL.Add('computermake as Manufacturer, computermodel as Model, notinstalled as ''Updates not downloaded'', ');
    SQL.Add('downloaded as ''Updates downloaded, not installed'', ');
    SQL.Add('installedpendingreboot as ''Needed updates installed but not activated (reboot required)'', ');
    SQL.Add('failed as ''Needed updates with failed installation''     ');
    SQL.Add('from wsus_systems ');

    Open;
    gridQuery2DBTableView1.DataController.DataSource:=dm0.dsMyQuery1;
    gridQuery2DBTableView1.DataController.CreateAllItems();
  end;
end;

procedure TfmInventory.gridPortsDBTableView1SetAuthProfilePropertiesChange(
  Sender: TObject);
var
  AColumnIndex, i: integer;
begin
  (*
  // set the restart flag, to indicate a change to the server side
  with Sender as TcxCustomEdit do begin
    AColumnIndex := gridPortsDBTableView1id.Index;
    i:=DataController.Values[AViewInfo.GridRecord.Index,AColumnIndex];
      ShowMessage('gridPortsDBTableView1SetAuthProfilePropertiesChange '
        +'index=' +IntToStr(AColumnIndex));
  end; *)

end;


procedure TfmInventory.ShowPatchCableClick(Sender: TObject);
var
  AView, AView2: TcxCustomGridTableView;
  str: string;
begin
  ShowMessage('ShowPatchCableClick: feature not yet available');
  exit;
  (*gridPortsDBTableView1ID.Index;
  //idIndex.Index;                    cxGridTableDataCellViewInfo
  gridPortsDBTableView1.ViewInfo.Gri
  ShowMEssage(
  gridPortsDBTableView1.DataController.Values[AViewInfo.GridRecord.Index,
  gridPortsDBTableView1idIndex.Index]); *)

  AView := TcxCustomGridTableView(gridPorts.FocusedView);
  str:=AView.Controller.FocusedRecord.DisplayTexts[0]
      +AView.Controller.FocusedRecord.DisplayTexts[1];

  AView2:= TcxCustomGridTableView(gridPatches.FocusedView);
  with AView2.DataController.Filter.Root do begin
    Clear;
    //BoolOperatorKind := fboOr;        // fboAnd
    AddItem(gridPatchesDBTableView1switchport, foEqual, str, str);

  //with AView.Controller.FocusedRecord do begin
    //ShowMessage(DisplayTexts[0] +DisplayTexts[1]);
    // index: ShowMessage(FocusedRecord.DisplayTexts[FocusedRecord.ValueCount-1]);
    (*gridSystems.ClearFilters;
    gridSystems.Columns[8].FilterExpression:=DisplayTexts[0];  // switch
    gridSystems.Columns[9].FilterExpression:=DisplayTexts[1];  // port
    gridSystems.ApplyFilter;
    Page.ActivePage:=fmInventory.tsOverview;  *)
  end;
end;

procedure TfmInventory.Showenddevices1Click(Sender: TObject);
var
  AView, AView2: TcxCustomGridTableView;
  str: string;
begin
  //ShowMessage('Showenddevices1Click');
  AView := TcxCustomGridTableView(gridPorts.FocusedView);
  //AView := TcxCustomGridTableView((Sender AS TcxGrid).FocusedView);
  //AView := TcxCustomGridTableView(gridSwitches.FocusedView);

  AView2:= TcxCustomGridTableView(gridSystems2.FocusedView);
  with AView2.DataController.Filter.Root do begin
    Clear;
    //BoolOperatorKind := fboOr;        // fboAnd  fboOr
    // FocusedRecord.DisplayTexts or Values
    // assume switch & port as first two fields
    str:=AView.Controller.FocusedRecord.DisplayTexts [0];  // switch name
    AddItem(gridSystemsView1SwitchName, foEqual, str, str);
    str:=AView.Controller.FocusedRecord.Values[1];         // port value
    AddItem(gridSystemsView1port, foEqual, str, str);
    Page.ActivePage:=fmInventory.tsOverview2;
  end;

  //AView.Controller.SelectedRecords

  (*
  gridSystems.ClearFilters;
  with AView.Controller.FocusedRecord do begin
    //ShowMessage(DisplayTexts[0] +DisplayTexts[1]);
    // index: ShowMessage(FocusedRecord.DisplayTexts[FocusedRecord.ValueCount-1]);
    gridSystems.Columns[8].FilterExpression:=DisplayTexts[0];  // switch
    gridSystems.Columns[9].FilterExpression:=DisplayTexts[1];  // port
    gridSystems.ApplyFilter;
    Page.ActivePage:=fmInventory.tsOverview;
  end; *)
end;

procedure TfmInventory.gridPortsDBTableView1upGetDataText(
  Sender: TcxCustomGridTableItem; ARecordIndex: Integer;
  var AText: String);
begin
  //ShowMessage('gridPortsDBTableView1upGetDataText ARecordIndex=' +IntToStr(ARecordIndex)
  //  +', AText=' +AText);
  if (AText = '1') then begin
    Atext:='up';
  end;
end;

procedure TfmInventory.gridPortsDBTableView1Up1GetDataText(
  Sender: TcxCustomGridTableItem; ARecordIndex: Integer;
  var AText: String);
begin
  if (AText = '1') then begin
    Atext:='up';
    //fmInventory.gridPortsDBTableView1Up1.Styles.Content:=cxStyle2;
  end else if (AText = '2') then begin
    Atext:='DOWN';
    //fmInventory.gridPortsDBTableView1Up1.Styles.Content:=cxStyle3;
  end;
end;

procedure TfmInventory.cxGridDBTableView1upGetDataText(
  Sender: TcxCustomGridTableItem; ARecordIndex: Integer;
  var AText: String);
begin
  if (AText = '1') then begin
    Atext:='up';
    //fmInventory.cxGridDBTableView1up.Styles.Content:=cxStyle2;
  end else if (AText = '2') then begin
    Atext:='DOWN';
    //fmInventory.cxGridDBTableView1up.Styles.Content:=cxStyle3;
  end;
end;

procedure TfmInventory.bbExportExcelSystemsClick(Sender: TObject);
begin
  SaveDialog1.FileName:='EndDevices.xls';
  SaveDialog1.Execute;
  ExportGridToExcel(SaveDialog1.FileName, gridSystems2);
end;

procedure TfmInventory.gridSwitchesPortsView1upGetDataText(
  Sender: TcxCustomGridTableItem; ARecordIndex: Integer;
  var AText: String);
begin
  if (AText = '1') then begin
    Atext:='up';
  end else if (AText = '2') then begin
    Atext:='DOWN';
  end;
end;

procedure TfmInventory.MenuItem1Click(Sender: TObject);
begin
  Page.ActivePage:=fmInventory.tsEdit;
  UpdateMACLookup;
  tsEditUpdate;
  update_queries;
end;

procedure TfmInventory.sbAll2Click(Sender: TObject);
begin
  gridSystems2.FocusedView.DataController.Filter.Root.Clear;
end;

procedure TfmInventory.bbRestartMasterClick(Sender: TObject);
begin
  with dm0.MyQuery1 do begin
    Close;
    SQL.Clear;
    SQL.Add('UPDATE config SET value=1, LastChange=now(), who='
     +IntToStr(UserID) +' WHERE name="restart_daemons"');
    Execute;
    Close;
  end;
  update_history('Server daemon restart requested');
  ShowMessage('A restart of the NAC master server daemons is now pending, watch the Server log for confirmation.');
  dm0.quConfig.Refresh;
end;

procedure TfmInventory.sbUnknownsTodayClick(Sender: TObject);
var
  AView, AView2: TcxCustomGridTableView;
  str: string;
begin
  //ShowMessage('sbUnknownsTodayClick');

  AView2:= TcxCustomGridTableView(gridSystems2.FocusedView);
  with AView2.DataController.Filter.Root do begin
    Clear;
    //BoolOperatorKind := fboOr;        // fboAnd  fboOr
    AddItem(gridSystemsView1statusname, foEqual, 'unknown', 'unknown');
    // TBD: how do we filter for > last 24 hours?
    //AddItem(gridSystemsView1LastSeen, foEqual, 'TODAY', DateTimeToStr(now-1));
    //AddItem(gridSystemsView1LastSeen, foEqual, 0, DateTimeToStr(now-1, dSettings));
  end;

  // TBD: sort by date reverse              soAscending
  // How to we use the column name, not the index?
  AView2.DataController.ChangeSorting(7, soDescending);
end;

procedure TfmInventory.gridSystemsView1macGetPropertiesForEdit(
  Sender: TcxCustomGridTableItem; ARecord: TcxCustomGridRecord;
  var AProperties: TcxCustomEditProperties);
begin
  // http://www.devexpress.com/Support/Center/p/Q90514.aspx
  if ARecord is TcxGridFilterRow then
    AProperties := cxTextItem1.Properties;
end;

procedure TfmInventory.cbOverviewPatchDetailsClick(Sender: TObject);
begin
  if cbOverviewPatchDetails.Checked then begin       // only lookup if enabled, cos its slow
    gridSystemsView1PatchCable.Visible:=true;
    gridSystemsView1PortLocation.Visible:=true;
  end else begin
    gridSystemsView1PatchCable.Visible:=false;
    gridSystemsView1PortLocation.Visible:=false;     
  end;
end;

procedure TfmInventory.bbPrintOverviewClick(Sender: TObject);
begin
  gridSystemsView1Link.PrinterPage.Orientation:=poLandscape;
  gridSystemsView1Link.OptionsSize.AutoWidth:=true;
  dxComponentPrinter1.Preview(True, gridSystemsView1Link);
end;

procedure TfmInventory.bbPrintPatchesClick(Sender: TObject);
begin
  dxPatchesLink1.PrinterPage.Orientation:=poLandscape;
  dxPatchesLink1.OptionsSize.AutoWidth:=true;
  dxComponentPrinter1.Preview(True, dxPatchesLink1);
end;

procedure TfmInventory.cxSystemsLocationComboPropertiesChange(
  Sender: TObject);
begin
  Refresh_UsersInPortLocation;
end;

procedure TfmInventory.cxLabel4Click(Sender: TObject);
begin
  ShellExecute(0, 'open', PChar('http://freenac.net/en/techguide'), '', '', SW_SHOW)
end;

procedure TfmInventory.cxLabel5Click(Sender: TObject);
begin
  ShellExecute(0, 'open', PChar('http://freenac.net/en/installguide'), '', '', SW_SHOW)
end;

procedure TfmInventory.Image1Click(Sender: TObject);
begin
  ShellExecute(0, 'open', PChar('http://freenac.net'), '', '', SW_SHOW)
end;

procedure TfmInventory.bbConnect2Click(Sender: TObject);
begin
  use_server2:=true;
  dm0.MyConnection1.Server:=Server2;
  ShowMessage('Connecting to the Secondary NAC server ' +Server2);
  call_connect;
end;

procedure TfmInventory.bbHubsClick(Sender: TObject);
var
  web_lastdays: string;
begin
  gridQuery2DBTableView1.ClearItems;
  web_lastdays:=get_config1('web_lastdays');
  if Length(web_lastdays) < 1 then begin
    ShowMessage('Error: The configuration variable web_lastdays is not set');
    exit;
  end;

  with dm0.MyQuery1 do begin
    Close;
    SQL.Clear;

    SQL.Add(
     'SELECT switch.name AS Switch, port.name AS Port, count(*) Count,'
     +'GROUP_CONCAT(systems.name, '' '', systems.LastSeen ORDER BY systems.LastSeen DESC SEPARATOR  '', '') AS ''End-Device list''    '
     +' FROM systems '
      +'  LEFT JOIN port ON systems.LastPort = port.id                                     '
      +'  LEFT JOIN switch ON port.switch = switch.id                                      '
      +'  WHERE (TO_DAYS(LastSeen)>=TO_DAYS(CURDATE())-'
           +web_lastdays +') GROUP BY LastPort HAVING count(*) > 1 '
    );
    //ShowMessage(SQL.text);
    Open;

    gridQuery2DBTableView1.DataController.DataSource:=dm0.dsMyQuery1;
    gridQuery2DBTableView1.DataController.DataModeController.SmartRefresh := True;
    gridQuery2DBTableView1.DataController.CreateAllItems();
  end;
end;

procedure TfmInventory.SaveGridLayout1Click(Sender: TObject);
begin
  //SaveDialog1.FileName:=ChangeFileExt(ParamStr(0),'.ini');
  SaveDialog1.FileName:=ExtractFileDir(ParamStr(0)) +'\' +Username +'.ini';

  if (SaveDialog1.Execute) then begin
    gridSystemsView1.StoreToIniFile(SaveDialog1.FileName, true, [gsoUseFilter,gsoUseSummary], 'gridSystems2');

    cxGridDBTableView1.StoreToIniFile(SaveDialog1.FileName, false, [gsoUseFilter,gsoUseSummary], 'gridSwitches');
    gridPortsDBTableView1.StoreToIniFile(SaveDialog1.FileName, false, [gsoUseFilter,gsoUseSummary], 'gridPorts');
    gridPatchesDBTableView1.StoreToIniFile(SaveDialog1.FileName, false, [gsoUseFilter,gsoUseSummary], 'gridPatches');
    gridChangeLogDBTableView1.StoreToIniFile(SaveDialog1.FileName, false, [gsoUseFilter,gsoUseSummary], 'gridChangeLog');
    gridServerLog2DBTableView1.StoreToIniFile(SaveDialog1.FileName, false, [gsoUseFilter,gsoUseSummary], 'gridServerLog2');
    cxGridConfigView.StoreToIniFile(SaveDialog1.FileName, false, [gsoUseFilter,gsoUseSummary], 'cxConfigGrid');
    cxGridNmapView9.StoreToIniFile(SaveDialog1.FileName, false, [gsoUseFilter,gsoUseSummary], 'cxGridNmap');
    gridVlan1View1.StoreToIniFile(SaveDialog1.FileName, false, [gsoUseFilter,gsoUseSummary], 'gridVlan1');

    ShowMessage('Grid Layouts saved to ' + SaveDialog1.FileName);
  end;
end;

procedure TfmInventory.LoadGridLayouts(file1: string);
begin
  //ShowMessage('LoadGridLayouts() ' +file1);
  with fmInventory do
  if FileExists(file1) then begin
    // RestoreFromIniFile(const AStorageName: string; AChildrenCreating: Boolean = True; AChildrenDeleting: Boolean = False; AOptions: TcxGridStorageOptions = [gsoUseFilter, gsoUseSummary]; const ARestoreViewName: string = '');
    gridSystemsView1.RestoreFromIniFile(file1, true, false, [gsoUseFilter,gsoUseSummary], 'gridSystems2');
    cxGridDBTableView1.RestoreFromIniFile(file1, true, false, [gsoUseFilter,gsoUseSummary], 'gridSwitches');
    gridPortsDBTableView1.RestoreFromIniFile(file1, true, false, [gsoUseFilter,gsoUseSummary], 'gridPorts');
    gridPatchesDBTableView1.RestoreFromIniFile(file1, true, false, [gsoUseFilter,gsoUseSummary], 'gridPatches');
    gridChangeLogDBTableView1.RestoreFromIniFile(file1, true, false, [gsoUseFilter,gsoUseSummary], 'gridChangeLog');
    gridServerLog2DBTableView1.RestoreFromIniFile(file1, true, false, [gsoUseFilter,gsoUseSummary], 'gridServerLog2');
    cxGridConfigView.RestoreFromIniFile(file1, true, false, [gsoUseFilter,gsoUseSummary], 'cxConfigGrid');
    cxGridNmapView9.RestoreFromIniFile(file1, true, false, [gsoUseFilter,gsoUseSummary], 'cxGridNmap');
    gridVlan1View1.RestoreFromIniFile(file1, true, false, [gsoUseFilter,gsoUseSummary], 'gridVlan1');
  end;
end;

procedure TfmInventory.LoadGridLayout1Click(Sender: TObject);
begin
  //OpenDialog.DefaultExt:='ini';
  OpenDialog.FileName:=ChangeFileExt(ParamStr(0),'.ini');
  if (OpenDialog.Execute) then begin
    LoadGridLayouts(OpenDialog.FileName);
    // RestoreFromIniFile(const AStorageName: string; AChildrenCreating: Boolean = True; AChildrenDeleting: Boolean = False; AOptions: TcxGridStorageOptions = [gsoUseFilter, gsoUseSummary]; const ARestoreViewName: string = '');
  (*  gridSystemsView1.RestoreFromIniFile(OpenDialog.FileName, true, false, [gsoUseFilter,gsoUseSummary], 'gridSystems2');
    cxGridDBTableView1.RestoreFromIniFile(OpenDialog.FileName, true, false, [gsoUseFilter,gsoUseSummary], 'gridSwitches');
    gridPortsDBTableView1.RestoreFromIniFile(OpenDialog.FileName, true, false, [gsoUseFilter,gsoUseSummary], 'gridPorts');
    gridPatchesDBTableView1.RestoreFromIniFile(OpenDialog.FileName, true, false, [gsoUseFilter,gsoUseSummary], 'gridPatches');
    gridChangeLogDBTableView1.RestoreFromIniFile(OpenDialog.FileName, true, false, [gsoUseFilter,gsoUseSummary], 'gridChangeLog');
    gridServerLog2DBTableView1.RestoreFromIniFile(OpenDialog.FileName, true, false, [gsoUseFilter,gsoUseSummary], 'gridServerLog2');
    cxGridConfigView.RestoreFromIniFile(OpenDialog.FileName, true, false, [gsoUseFilter,gsoUseSummary], 'cxConfigGrid');
    cxGridNmapView9.RestoreFromIniFile(OpenDialog.FileName, true, false, [gsoUseFilter,gsoUseSummary], 'cxGridNmap');
    gridVlan1View1.RestoreFromIniFile(OpenDialog.FileName, true, false, [gsoUseFilter,gsoUseSummary], 'gridVlan1');  *)

  end;
end;

procedure TfmInventory.bbHelpdeskClick(Sender: TObject);
begin
  with dm0.MyQuery1 do begin
    SQL.Clear;
    SQL.Add('SELECT CONCAT(GivenName, '' '', Surname, '', '', username) AS Name  from users WHERE nac_rights=4');
    Open;
    meUserQuery.Lines.Clear;
    while not EOF do begin
      meUserQuery.Lines.Add(FieldByName('Name').AsString);
      next;
    end;
    Close;
  end;
end;

procedure TfmInventory.FormClose(Sender: TObject;
  var Action: TCloseAction);
begin
  Disconnect;
end;

end.

