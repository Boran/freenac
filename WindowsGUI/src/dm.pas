(**
 * Long description for file:
 * Windows GUI for Freenac - data module
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

unit dm;

interface

uses
  SysUtils, Classes, DB, MemDS, DBAccess, MyAccess, MSAccess, DASQLMonitor,
  MyDacMonitor, MySQLMonitor, MyCall, MemData,
  Messages, Dialogs, Variants, StrUtils, cxCheckBox ;

type
  Tdm0 = class(TDataModule)
    quVlanList: TMyQuery;
    dsVlanList: TDataSource;
    quVlanListvlan_description: TStringField;
    quVlanListdefault_name: TStringField;
    quVlanListdefault_id: TIntegerField;
    quVlanListid: TIntegerField;
    taVmpslog: TMyTable;
    taVmpslogwho: TIntegerField;
    taVmpslogdatetime: TDateTimeField;
    taVmpslogpriority: TStringField;
    taVmpslogwhat: TStringField;
    taVmpsloghost: TStringField;
    taVmpslogid: TLargeintField;
    dsVmpslog: TDataSource;
    quGuilog: TMyQuery;
    StringField2: TStringField;
    DateTimeField1: TDateTimeField;
    StringField4: TStringField;
    StringField8: TStringField;
    StringField9: TStringField;
    IntegerField1: TIntegerField;
    quGuilogid: TLargeintField;
    quGuilogwho_1: TIntegerField;
    dsGuilog: TDataSource;
    dsSwitch: TDataSource;
    quSwitch: TMyQuery;
    quSwitchid: TIntegerField;
    quSwitchip: TStringField;
    quSwitchname: TStringField;
    quSwitchswgroup: TStringField;
    quSwitchnotify: TStringField;
    quSwitchscan: TBooleanField;
    quSwitchbuilding: TStringField;
    locLookupComp: TStringField;
    quSwitchcomment: TStringField;
    quSwitchlocation: TIntegerField;
    quSwitchLocationName: TStringField;
    builLookupComponent: TStringField;
    quSwitchhw: TStringField;
    quSwitchsw: TStringField;
    quLocation: TMyQuery;
    quLocationid: TIntegerField;
    quLocationbuilding_id: TIntegerField;
    quLocationBuildingName: TStringField;
    quLocationLocationName: TStringField;
    BlookupComp: TStringField;
    quLocationLocationAndBuilding: TStringField;
    dsLocations: TDataSource;
    taBuilding: TMyTable;
    dsBuilding: TDataSource;
    quUsers: TMyQuery;
    quUsersid: TIntegerField;
    quUsersGivenName: TStringField;
    quUsersDepartment: TStringField;
    quUsersrfc822mailbox: TStringField;
    quUsersHouseIdentifier: TStringField;
    quUsersPhysicalDeliveryOfficeName: TStringField;
    quUsersTelephoneNumber: TStringField;
    quUsersMobile: TStringField;
    quUsersmanual_direx_sync: TIntegerField;
    quUserscomment: TStringField;
    quUsersGuiVlanRights: TStringField;
    quUsersSurname: TStringField;
    quUsersGivenNameSurname: TStringField;
    quUserslocation: TIntegerField;
    UserLocationLookupComp: TStringField;
    quUsersnac_rights: TIntegerField;
    quUsersusername: TStringField;
    quUsersLastSeenDirectory: TDateField;
    dsUsers: TDataSource;
    dsUserLookup: TDataSource;
    quUserLookup: TMyQuery;
    quUserLookupSurname: TStringField;
    quUserLookupGivenName: TStringField;
    quUserLookupDepartment: TStringField;
    quUserLookuprfc822mailbox: TStringField;
    quUserLookupHouseIdentifier: TStringField;
    quUserLookupPhysicalDeliveryOfficeName: TStringField;
    quUserLookupTelephoneNumber: TStringField;
    quUserLookupMobile: TStringField;
    quUserLookupcomment: TStringField;
    quUserLookuplocation: TIntegerField;
    UserLookLocationLookup: TStringField;
    quUserLookupLastSeenDirectory: TDateField;
    quUserLookupusername: TStringField;
    quVlan: TMyQuery;
    quVlanid: TIntegerField;
    quVlandefault_id: TIntegerField;
    quVlandefault_name: TStringField;
    quVlanvlan_description: TStringField;
    quVlanvlan_group: TIntegerField;
    dsVlan: TDataSource;
    dsVlanswitch: TDataSource;
    quVlanswitch: TMyQuery;
    quVlanswitchvid: TIntegerField;
    quVlanswitchswid: TIntegerField;
    quVlanswitchvlan_name: TStringField;
    VlanswitchSwitchLookup: TStringField;
    VlanswitchDefVlanLookup: TStringField;
    quSystems: TMyQuery;
    quSystemsvlan: TIntegerField;
    quSystemsvlanname: TStringField;
    quSystemsstatusname: TStringField;
    quSystemsname: TStringField;
    quSystemscomment: TStringField;
    quSystemsmac: TStringField;
    quSystemsChangeDate: TStringField;
    quSystemsbuilding: TStringField;
    quSystemsPortComment: TStringField;
    quSystemsSwitchName: TStringField;
    quSystemsSwitchLocation: TStringField;
    quSystemsLastVlan: TStringField;
    quSystemshistory: TMemoField;
    quSystemsUserSurname: TStringField;
    quSystemsUserForename: TStringField;
    quSystemsUserDept: TStringField;
    quSystemsUserEmail: TStringField;
    quSystemsUserHouse: TStringField;
    quSystemsUserOffice: TStringField;
    quSystemsUserMobileTel: TStringField;
    quSystemsOsName: TStringField;
    quSystemsClassName: TStringField;
    quSystemsClass2Name: TStringField;
    quSystemsscannow: TSmallintField;
    quSystemsLastSeen: TDateTimeField;
    quSystemsUserTel: TStringField;
    quSystemsport: TStringField;
    quSystemsr_ip: TStringField;
    quSystemsr_timestamp: TDateTimeField;
    quSystemsr_ping_timestamp: TDateTimeField;
    quSystemsSTATUS: TLargeintField;
    quSystemsUserLastSeenDirex: TDateField;
    quSystemsos: TLargeintField;
    quSystemsclass: TLargeintField;
    quSystemsclass2: TLargeintField;
    quSystemsvlangroup: TIntegerField;
    quSystemsid: TIntegerField;
    quSystemsvlanid: TIntegerField;
    quSystemsportid: TIntegerField;
    quSystemsos4: TStringField;
    quSystemsswitch: TStringField;
    quSystemsuid: TIntegerField;
    quSystemsinventory: TStringField;
    quSystemsoffice: TIntegerField;
    quSystemsos1: TLargeintField;
    quSystemsos1name: TStringField;
    quSystemsos2: TLargeintField;
    quSystemsos2name: TStringField;
    quSystemsos3: TLargeintField;
    quSystemsos3name: TStringField;
    quSystemsuser_name: TStringField;
    quSystemsChangeUserFullName: TStringField;
    quSystemsexpiry: TDateTimeField;
    quSystemsSwitchComment: TStringField;
    quSystemsvlan_description: TStringField;
    dsSystems2: TDataSource;
    quCable: TMyQuery;
    quCableid: TIntegerField;
    quCablerack: TStringField;
    quCablerack_location: TStringField;
    quCableoutlet: TStringField;
    quCablerack_outlet: TStringField;
    quCablecomment: TStringField;
    quCablelastchange: TDateTimeField;
    quCableChangedBy: TStringField;
    quCableexpiry: TDateField;
    quCableoffice: TIntegerField;
    quCableOfficeLocation: TStringField;
    PatchOfficeLookupComp: TStringField;
    quCablemodifiedby: TIntegerField;
    PatchmodifiedbyLookup: TStringField;
    quCableport: TIntegerField;
    PatchportlookupComp: TStringField;
    quCablecable_name: TStringField;
    quCabletype: TIntegerField;
    dsCable: TDataSource;
    quPorts: TMyQuery;
    quPortsid: TIntegerField;
    quPortsSwitchName: TStringField;
    quPortsswitchport: TStringField;
    quPortscomment: TStringField;
    quPortsrestart_now: TIntegerField;
    quPortsdefault_vlan: TIntegerField;
    quPortsname: TStringField;
    quPortslast_vlan: TIntegerField;
    quPortslast_activity: TDateTimeField;
    Portsdef_lanLookupComp: TStringField;
    quPortsswitchip: TStringField;
    quPortsswitch: TIntegerField;
    dsPort2: TDataSource;
    taEthernet: TMyTable;
    taEthernetvendor: TStringField;
    taEthernetmac: TStringField;
    dsEthernet: TDataSource;
    taStatus: TMyTable;
    taStatusid: TLargeintField;
    taStatusvalue: TStringField;
    taSys_class: TMyTable;
    taSys_classid: TLargeintField;
    taSys_classvalue: TStringField;
    taSys_class2: TMyTable;
    LargeintField2: TLargeintField;
    StringField6: TStringField;
    dsStatus: TDataSource;
    dsSys_class: TDataSource;
    dsSys_class2: TDataSource;
    taSys_os: TMyTable;
    LargeintField1: TLargeintField;
    StringField5: TStringField;
    taSys_os1: TMyTable;
    LargeintField3: TLargeintField;
    StringField1: TStringField;
    taSys_os2: TMyTable;
    LargeintField4: TLargeintField;
    StringField3: TStringField;
    dsSys_os: TDataSource;
    dsSys_os1: TDataSource;
    dsSys_os2: TDataSource;
    dsSys_os3: TDataSource;
    taSys_os3: TMyTable;
    LargeintField5: TLargeintField;
    StringField7: TStringField;
    MyQuery1: TMyQuery;
    dsMyQuery1: TDataSource;
    MySQLMonitor1: TMySQLMonitor;
    MyConnection1: TMyConnection;
    MSConnection1: TMSConnection;
    dsStaticInv: TDataSource;
    quStaticInv: TMSQuery;
    MSQuery1: TMSQuery;
    quPortsVlanAuth: TStringField;
    quPortsLastVlanName: TStringField;
    quConfig: TMyQuery;
    quConfigid: TIntegerField;
    quConfigtype: TStringField;
    quConfigname: TStringField;
    quConfigvalue: TStringField;
    quConfigcomment: TStringField;
    quConfigLastChange: TDateTimeField;
    quConfigwho: TIntegerField;
    dsConfig: TDataSource;
    quConfiguser: TStringField;
    quSubnets: TMyQuery;
    quSubnetsid: TIntegerField;
    quSubnetsip_address: TStringField;
    quSubnetsip_netmask: TIntegerField;
    quSubnetsscan: TSmallintField;
    quSubnetsdontscan: TStringField;
    dsSubnets: TDataSource;
    quSystemslast_hostname: TStringField;
    quSwitchlast_monitored: TDateTimeField;
    quSwitchup: TIntegerField;
    quPortslast_monitored: TDateTimeField;
    quPortsup: TIntegerField;
    quPortsshutdown: TIntegerField;
    quSystemsHealthStatus: TStringField;
    quSystemslast_nbtname: TStringField;
    quSystemslast_uid: TIntegerField;
    quSystemsemail_on_connect: TStringField;
    quSystemsdhcp_fix: TSmallintField;
    quSystemsdhcp_ip: TStringField;
    quSystemsdns_alias: TStringField;
    quPortsstaticvlan: TIntegerField;
    quPortsauth_profile: TLargeintField;
    dsAuth_profile: TDataSource;
    quAuth_profile: TMyQuery;
    quAuth_profileid: TLargeintField;
    quAuth_profilemethod: TStringField;
    quSystemsPatchCable: TStringField;
    quPort1: TMyQuery;
    IntegerField2: TIntegerField;
    StringField10: TStringField;
    StringField11: TStringField;
    StringField12: TStringField;
    IntegerField3: TIntegerField;
    IntegerField4: TIntegerField;
    StringField13: TStringField;
    IntegerField5: TIntegerField;
    StringField14: TStringField;
    DateTimeField2: TDateTimeField;
    StringField15: TStringField;
    StringField16: TStringField;
    StringField17: TStringField;
    IntegerField6: TIntegerField;
    DateTimeField3: TDateTimeField;
    IntegerField7: TIntegerField;
    IntegerField8: TIntegerField;
    IntegerField9: TIntegerField;
    LargeintField6: TLargeintField;
    dsPort1: TDataSource;
    quSystemsswgroup: TStringField;
    taCableType: TMyTable;
    taCableTypeid: TIntegerField;
    taCableTypename: TStringField;
    dsCableType: TDataSource;
    quSwitchvlan_id: TIntegerField;
    quSystemsPortLocation: TStringField;
    quSwitchscan3: TBooleanField;
    quSystemsChangeUserName: TStringField;
    quSystemsChangeUser: TIntegerField;
    quSwitchswitch_type: TWordField;
    quSystemsswitch_type: TWordField;
    quPortsswitch_type: TWordField;
    MyConnection2: TMyConnection;
    quSmschallenge: TMyQuery;
    dsSmschallenge: TDataSource;
    quSmschallengevpn_allowed: TLargeintField;
    quSmschallengelast_login: TDateTimeField;
    quSmschallengelast_change: TDateTimeField;
    procedure MyConnection1BeforeConnect(Sender: TObject);
    procedure MyConnection1Error(Sender: TObject; E: EDAError;
      var Fail: Boolean);
    procedure quConfigBeforePost(DataSet: TDataSet);
    procedure quSystemsBeforePost(DataSet: TDataSet);
    procedure quSystemsNewRecord(DataSet: TDataSet);
    procedure quSystemsAfterPost(DataSet: TDataSet);
    procedure quSystemsBeforeDelete(DataSet: TDataSet);
    procedure quUsersBeforeDelete(DataSet: TDataSet);
    procedure quCableBeforeDelete(DataSet: TDataSet);
    procedure quVlanBeforeDelete(DataSet: TDataSet);
    procedure quCableBeforePost(DataSet: TDataSet);
    procedure quUsersAfterPost(DataSet: TDataSet);
    procedure quSwitchAfterPost(DataSet: TDataSet);
    procedure quVlanAfterPost(DataSet: TDataSet);
    procedure quCableAfterPost(DataSet: TDataSet);
    procedure quVlanswitchBeforeDelete(DataSet: TDataSet);
    procedure quVlanswitchAfterPost(DataSet: TDataSet);
    procedure quPortsAfterPost(DataSet: TDataSet);
    procedure taSys_osAfterPost(DataSet: TDataSet);
    procedure taSys_classAfterPost(DataSet: TDataSet);
    procedure taSys_class2AfterPost(DataSet: TDataSet);
    procedure taSys_os1AfterPost(DataSet: TDataSet);
    procedure taSys_os2AfterPost(DataSet: TDataSet);
    procedure taSys_os3AfterPost(DataSet: TDataSet);
    procedure quSubnetsAfterPost(DataSet: TDataSet);
    procedure quSubnetsBeforeDelete(DataSet: TDataSet);
    procedure quPortsauth_profileChange(Sender: TField);
    procedure quPortsBeforePost(DataSet: TDataSet);
    procedure MyConnection1ConnectionLost(Sender: TObject;
      Component: TComponent; ConnLostCause: TConnLostCause;
      var RetryMode: TRetryMode);
    procedure dsVlanDataChange(Sender: TObject; Field: TField);
    procedure quSmschallengeBeforeUpdateExecute(Sender: TCustomMyDataSet;
      StatementTypes: TStatementTypes; Params: TDAParams);
    procedure quSmschallengeBeforeRefresh(DataSet: TDataSet);
  private
    { Private declarations }
  public
    { Public declarations }
  end;

var
  dm0: Tdm0;

implementation

uses Main;

{$R *.dfm}

// update log on GUI changes - guilog
procedure update_history(msg: string);
begin
  with dm0.MyQuery1 do begin
    SQL.Clear;
    SQL.Add('INSERT into guilog (datetime,host,who,what) values (NOW(),:host,:who,:what)'  );
    ParamByName('host').AsString := hostname;
    ParamByName('who').AsString := IntToStr(UserId);
    ParamByName('what').AsString := LeftStr(msg, 200);  // max. 200 chars
    //ShowMessage(SQL.Text);
    Execute;
  end;

end;

procedure Tdm0.MyConnection1BeforeConnect(Sender: TObject);
begin
  fmInventory.initialise;
end;

procedure Tdm0.MyConnection1Error(Sender: TObject; E: EDAError;
  var Fail: Boolean);
begin
  // Write an OnError event handler to respond to errors that arise with connection.
  // Check E parameter to get an error code. Set the Fail parameter to False to prevent
  // an error dialog from being displayed and to raise EAbort exception to cancel current
  // operation. The default value of Fail is True.

  if  (E.ErrorCode = CR_CONN_HOST_ERROR) or
      (E.ErrorCode = CR_SERVER_LOST) or
      (E.ErrorCode = CR_SERVER_GONE_ERROR) or
      (E.ErrorCode = ER_SERVER_SHUTDOWN)
      then begin

    ShowMessage('NAC Mysql connection problem: ' +#13#10
      +'  ' +E.Message +', Error Code ' +IntToStr(E.ErrorCode)   +#13#10  +#13#10
      +'Please reconnect');
    fmInventory.Disconnect;      // we've lost connectivity, so avoid more popups
    Fail:=false;

  end else begin           // inform, but do not disconnect for other errors

    ShowMessage('Mysql problem: ' +#13#10
      +'  ' +E.Message +', Error Code ' +IntToStr(E.ErrorCode)   +#13#10  +#13#10
      +'Please note the above when reporting to your IT Support staff.');
    Fail:=true;
  end;
end;



procedure Tdm0.quConfigBeforePost(DataSet: TDataSet);
begin
  if not Dataset.Modified then begin
    exit;              // no changes, no work needed
  end;

  // Some Fields are read-only
  if ( (Dataset.FieldByName('name').AsString = 'version_dbschema')
    or (Dataset.FieldByName('name').AsString = 'version_server')
    or (Dataset.FieldByName('name').AsString = 'suvadb_server1')
     ) then begin
    ShowMessage('Changes to the value of ' +Dataset.FieldByName('name').AsString +' are not allowed');
    Dataset.Cancel;
    SysUtils.Abort;
  end else begin
    Dataset.FieldByName('who').AsInteger := UserId;   // remember who made the change!
    update_history('config setting ' +Dataset.FieldByName('name').AsString
      +' changed to:' +Dataset.FieldByName('value').AsString
      +' comment: '   +Dataset.FieldByName('comment').AsString
    );
  end;
end;

procedure Tdm0.quSystemsBeforePost(DataSet: TDataSet);
var
  i, oldvlan, newvlan, vgroup: integer;
begin
  //ShowMessage('quSystemsBeforePost');
  if not Dataset.Modified then begin
    exit;              // no changes, no work needed
  end;

  // Get rid of uppercase names, spaces
  Dataset.FieldByName('name').AsString:=Lowercase(Dataset.FieldByName('name').AsString);
  Dataset.FieldByName('name').AsString:=Trim(Dataset.FieldByName('name').AsString);

  // Convert Inventory to a number
  //Dataset.FieldByName('inventory').AsString:=trim(Dataset.FieldByName('inventar').AsString);
  Dataset.FieldByName('inventory').AsString:=
     IntToStr( StrToIntDef(Dataset.FieldByName('inventory').AsString, 0) );
  //ShowMessage('Inventory=' +Dataset.FieldByName('inventory').AsString);

  // make sure there are no spaces in the MAC!
  // we should really do a rigorous testing of the format
  if ( (AnsiPos(' ', Dataset.FieldByName('mac').AsString) >0)
     or (AnsiPos('-', Dataset.FieldByName('mac').AsString) >0)
     or (AnsiPos(':', Dataset.FieldByName('mac').AsString) >0)
     ) then begin
    ShowMessage('MAC address contains  spaces or minus or : ! It must be a hex number in the format xxxx.xxxx.xxxx');
    Dataset.Cancel;
    SysUtils.Abort;
  end;

  if VarIsNull(Dataset.FieldByName('vlanid').OldValue) then
   oldvlan:=0
  else
    oldvlan:=Dataset.FieldByName('vlanid').OldValue;
  if VarIsNull(Dataset.FieldByName('vlanid').Value) then               // AsInteger could sometimes cause an exception
   newvlan:=0
  else
    newvlan:=Dataset.FieldByName('vlanid').Value;


  if (Company='INO') then begin
     if (oldvlan <> newvlan) then begin    // were there vlan changes?
     // find the vlan goup of the new vlan
     //vgroup :=Dataset.FieldByName('VlanGroup').AsInteger;
       with dm0.MyQuery1 do begin
         SQL.Clear;
         SQL.Add('SELECT vlan_group from vlan WHERE vlan.id='''
           +Dataset.FieldByName('vlanid').AsString +'''');
         Open;
         if Length(FieldByName('vlan_group').AsString)>0 then
           vgroup:=FieldByName('vlan_group').AsInteger;
         Close;
       end;

     // Admin can change all Vlans, Superuser INO-secure Vlans.
     //ShowMessage('INO: Change vlan from: ' +IntToStr(oldvlan)
     //  +' to ' +IntToStr(newvlan) +', group=' +IntToStr(vgroup)
     //);

     if (vgroup=2) then begin   // secure
       if (can_admin or can_edit)  then begin
         //ShowMessage('admin/super user: allowed to change secure vlan');
         ; // OK, allow super users & admins to Change Secure
       end else begin
         ShowMessage('You are not not allowed to assign this Vlan.');
         Dataset.Cancel; SysUtils.Abort;
       end;

     end else if (Dataset.FieldByName('VlanGroup').AsInteger=1) then begin  //InSecure
       if (can_admin)  then begin
         //ShowMessage('admin user: allowed to change insecure vlan');
            ; // OK, allow admins to Change Insecure
       end else begin
         ShowMessage('You are not allowed to Change INO-Secure Vlans, resetting.');
         Dataset.Cancel; SysUtils.Abort;
       end;

     end else begin                           // Other
       if (can_admin)  then begin
         ShowMessage('WARNING: you have selected a Special Vlan, think about the consequences.');
         ; // OK, allow admins to Change
       end else begin
         ShowMessage('You are not allowed to Change to these Vlans');
         Dataset.Cancel; SysUtils.Abort;
       end;
     end;
     end;               //if (oldvlan <> newvlan)
   //end else begin     // other companies
   end;                 // if INO


  //if ( (newvlan=0) or (Dataset.FieldByName('vlan').AsString='')) then begin
  //  ShowMessage('Warning: no valid Vlan has been set, this Device will be blocked.');
  //end;


   // log changes to this record
   DataSet.FieldByName('ChangeDate').Value:=now;
   //DataSet.FieldByName('ChangeUser').Value:=Username;
   //DataSet.FieldByName('ChangeUserName').Value:=Username;
   DataSet.FieldByName('ChangeUser').Value:=UserID;

   update_history('Pre-Post system: '   +DataSet.FieldByname('name').AsString
      + ', ' +DataSet.FieldByname('mac').AsString
      + ', uid:' +DataSet.FieldByname('uid').AsString
      + ', comment: ' +DataSet.FieldByname('comment').AsString
      + ', office: ' +DataSet.FieldByname('office').AsString
      + ', Port: ' +DataSet.FieldByname('port').AsString
      + ', ' +DataSet.FieldByname('switch').AsString
      + ', vlan' +DataSet.FieldByname('vlan').AsString
   );


end;

procedure Tdm0.quSystemsNewRecord(DataSet: TDataSet);
begin
  // defaults
  if Company='INO' then begin
    Dataset.FieldByName('name').AsString:='ENTER_PC-NAME_HERE';
    Dataset.FieldByName('mac').AsString:='0000.0000.0000';
    Dataset.FieldByName('vlan').Value:=13;
    Dataset.FieldByName('status').Value:=1;
  end else if Company='BW' then begin
    Dataset.FieldByName('name').AsString:='Uxxxyyy';
    Dataset.FieldByName('mac').AsString:='0000.0000.0000';
    Dataset.FieldByName('vlan').Value:=101;
    Dataset.FieldByName('status').Value:=1;
  end else begin
    Dataset.FieldByName('name').AsString:='xxxYYY';
    Dataset.FieldByName('mac').AsString:='0000.0000.0000';
    //Dataset.FieldByName('vlan').Value:=10;
    Dataset.FieldByName('status').Value:=1;
  end;
end;

procedure Tdm0.quSystemsAfterPost(DataSet: TDataSet);
begin
  update_history('Updated system: '   +DataSet.FieldByname('name').AsString
      + ', ' +DataSet.FieldByname('mac').AsString
      + ', Id: ' +DataSet.FieldByname('uid').AsString
      + ', ' +DataSet.FieldByname('comment').AsString
      + ', ' +DataSet.FieldByname('office').AsString
      + ', Port: ' +DataSet.FieldByname('port').AsString
      + ', ' +DataSet.FieldByname('SwitchName').AsString
      + ', vlan' +DataSet.FieldByname('vlan').AsString
  );
end;

procedure Tdm0.quSystemsBeforeDelete(DataSet: TDataSet);
begin
  update_history('Deleted system: '   +DataSet.FieldByname('name').AsString
      + ', ' +DataSet.FieldByname('mac').AsString
      + ', ' +DataSet.FieldByname('UserSurname').AsString  +' '
      +DataSet.FieldByname('UserForename').AsString  +' '
      +DataSet.FieldByname('user_name').AsString
      + ', vlan' +DataSet.FieldByname('vlan').AsString
  );
end;

procedure Tdm0.quUsersBeforeDelete(DataSet: TDataSet);
begin
  update_history('DELETE User: '   +DataSet.FieldByname('username').AsString
      +DataSet.FieldByname('Surname').AsString  +' '
      +DataSet.FieldByname('Givenname').AsString  +' '
      +DataSet.FieldByname('username').AsString
      +', Rights='   +DataSet.FieldByname('nac_rights').AsString
      +', Man.sync='   +DataSet.FieldByname('manual_direx_sync').AsString
  );
end;

procedure Tdm0.quCableBeforeDelete(DataSet: TDataSet);
begin
  update_history('Delete Patch: '   +DataSet.FieldByname('outlet').AsString
      + ', ' +DataSet.FieldByname('office').AsString
      + ', ' +DataSet.FieldByname('rack_location').AsString
      );
end;

procedure Tdm0.quVlanBeforeDelete(DataSet: TDataSet);
begin
  update_history('Deleted Vlan: '
      +DataSet.FieldByname('default_id').AsString
      + ', ' +DataSet.FieldByname('default_name').AsString
      + ', ' +DataSet.FieldByname('vlan_description').AsString
      + ', vlan group=' +DataSet.FieldByname('vlan_group').AsString
      + ', key=' +DataSet.FieldByname('id').AsString
  );
end;

procedure Tdm0.quCableBeforePost(DataSet: TDataSet);
begin
  DataSet.FieldByname('modifiedby').Value:=UserID;   // remember who made the change
  DataSet.FieldByname('lastchange').Value:=NOW();
  //ShowMEssage(IntToStr(UserID));         // lastchange
end;

procedure Tdm0.quUsersAfterPost(DataSet: TDataSet);
begin
  update_history('Updated User: '
      +DataSet.FieldByname('Surname').AsString  +' '
      +DataSet.FieldByname('Givenname').AsString  +' '
      +DataSet.FieldByname('username').AsString +', '
      +DataSet.FieldByname('Department').AsString
      +', Rights='   +DataSet.FieldByname('nac_rights').AsString
      +', VlanRights='   +DataSet.FieldByname('GuiVlanRights').AsString
      +', Man.sync='   +DataSet.FieldByname('manual_direx_sync').AsString
  );
end;

procedure Tdm0.quSwitchAfterPost(DataSet: TDataSet);
begin
  update_history('Update switch: '   +DataSet.FieldByname('name').AsString
      + ', IP='       +DataSet.FieldByname('ip').AsString
      + ', SwitchID=' +DataSet.FieldByname('id').AsString
      + ', comment='  +DataSet.FieldByname('comment').AsString
      + ', notify='   +DataSet.FieldByname('notify').AsString
      + ', group='   +DataSet.FieldByname('swgroup').AsString
      + ', scan allowed='   +DataSet.FieldByname('scan').AsString      
  );
end;

procedure Tdm0.quVlanAfterPost(DataSet: TDataSet);
begin
  update_history('Updated Vlan: '
      +DataSet.FieldByname('default_id').AsString
      + ', ' +DataSet.FieldByname('default_name').AsString
      + ', ' +DataSet.FieldByname('vlan_description').AsString
      + ', vlan group=' +DataSet.FieldByname('vlan_group').AsString
      + ', key=' +DataSet.FieldByname('id').AsString
  );
end;

procedure Tdm0.quCableAfterPost(DataSet: TDataSet);
begin
  update_history('Update Patch: '   +DataSet.FieldByname('outlet').AsString
      + ', ' +DataSet.FieldByname('office').AsString
      + ', ' +DataSet.FieldByname('rack_location').AsString);
end;

procedure Tdm0.quVlanswitchBeforeDelete(DataSet: TDataSet);
begin
  update_history('Deleted Vlanswitch: '
      +DataSet.FieldByname('vid').AsString
      + ', sw=' +DataSet.FieldByname('swid').AsString
      + ', vlan_name=' +DataSet.FieldByname('vlan_name').AsString
  );
end;

procedure Tdm0.quVlanswitchAfterPost(DataSet: TDataSet);
begin
  update_history('Updated Vlanswitch: '
      +DataSet.FieldByname('vid').AsString
      + ', sw=' +DataSet.FieldByname('swid').AsString
      + ', vlan_name=' +DataSet.FieldByname('vlan_name').AsString
  );
end;

procedure Tdm0.quPortsAfterPost(DataSet: TDataSet);
begin
  update_history('Update Port: '   +DataSet.FieldByname('name').AsString
      + ', ' +DataSet.FieldByname('comment').AsString
      + ', default_vlan=' +DataSet.FieldByname('default_vlan').AsString
      + ', restart=' +DataSet.FieldByname('restart_now').AsString
      + ', shutdown=' +DataSet.FieldByname('shutdown').AsString
      + ', staticvlan=' +DataSet.FieldByname('staticvlan').AsString
      + ', auth_profile=' +DataSet.FieldByname('auth_profile').AsString
      + ', SwitchID=' +DataSet.FieldByname('switch').AsString
  );
end;

procedure Tdm0.taSys_osAfterPost(DataSet: TDataSet);
begin
  update_history('Update Operating Systems sys_os: '   +DataSet.FieldByname('id').AsString
      + ', '      +DataSet.FieldByname('value').AsString);
end;

procedure Tdm0.taSys_classAfterPost(DataSet: TDataSet);
begin
  update_history('Update Device Types sys_class: '   +DataSet.FieldByname('id').AsString
      + ', '      +DataSet.FieldByname('value').AsString);
end;

procedure Tdm0.taSys_class2AfterPost(DataSet: TDataSet);
begin
  update_history('Update Classifications sys_class2: '   +DataSet.FieldByname('id').AsString
      + ', '      +DataSet.FieldByname('value').AsString
  );
end;

procedure Tdm0.taSys_os1AfterPost(DataSet: TDataSet);
begin
  update_history('Update sys_os1: '   +DataSet.FieldByname('id').AsString
      + ', '      +DataSet.FieldByname('value').AsString);
end;

procedure Tdm0.taSys_os2AfterPost(DataSet: TDataSet);
begin
  update_history('Update sys_os2: '   +DataSet.FieldByname('id').AsString
      + ', '      +DataSet.FieldByname('value').AsString);
end;

procedure Tdm0.taSys_os3AfterPost(DataSet: TDataSet);
begin
  update_history('Update sys_os3: '   +DataSet.FieldByname('id').AsString
      + ', '      +DataSet.FieldByname('value').AsString);
end;

procedure Tdm0.quSubnetsAfterPost(DataSet: TDataSet);
begin
  update_history('Updated nmap subnet: '   +DataSet.FieldByname('ip_address').AsString
      + ', ' +DataSet.FieldByname('ip_netmask').AsString
      + ', scan: ' +DataSet.FieldByname('scan').AsString
      + ', dontscan: ' +DataSet.FieldByname('dontscan').AsString
  );
end;

procedure Tdm0.quSubnetsBeforeDelete(DataSet: TDataSet);
begin
  update_history('Deleted nmap subnet: '   +DataSet.FieldByname('ip_address').AsString
      + ', ' +DataSet.FieldByname('ip_netmask').AsString
      + ', scan: ' +DataSet.FieldByname('scan').AsString
      + ', dontscan: ' +DataSet.FieldByname('dontscan').AsString
  );
end;

procedure Tdm0.quPortsauth_profileChange(Sender: TField);
begin
  //ShowMessage('quPortsauth_profileChange');
end;


procedure Tdm0.quPortsBeforePost(DataSet: TDataSet);
var
  oldcomment: string;
  strs: TStrings;
  i, count: integer;
begin
  if not Dataset.Modified then begin
    exit;              // no changes, no work needed
  end;
  //ShowMessage('quPortsBeforePost ');

  // Restrict editing of certain ports?
  //ShowMessage('Verify port restrictions: ' +gui_disable_ports_list);
  strs:=TStringList.Create;
  // Build the list of strings to be checked for: is the list >0 ?
  count:=ExtractStrings([','], [' '], PChar(gui_disable_ports_list),  strs);
  if count >0 then begin
    oldcomment:=AnsiLowerCase(Dataset.FieldByName('comment').OldValue);    // We look for the string here

    for i:=0 to  strs.count -1 do  begin
      //ShowMessage('Verify port restrictions: ' +strs[i] +', compare with ' +oldcomment);
      if Pos(AnsiLowerCase(strs[i]), oldcomment) > 0 then begin
        ShowMessage('You are not not allowed to Edit this Port, since it contains a comment matching:'
          +gui_disable_ports_list +']');
        Dataset.Cancel; SysUtils.Abort;     // abort the edit operation
      end;
    end;
  end else begin
    ShowMessage('no restrictions, count=' +IntToStr(count));
  end;
  strs.free;

  if ( (Dataset.FieldByName('shutdown').OldValue <> Dataset.FieldByName('shutdown').Value)
    or (Dataset.FieldByName('staticvlan').OldValue <> Dataset.FieldByName('staticvlan').Value)
    or (Dataset.FieldByName('auth_profile').OldValue <> Dataset.FieldByName('auth_profile').Value)
      ) then begin
    //ShowMessage('The port will be re-programmed in a few minutes, please check the server log');
    fmInventory.StatusLine.SimpleText:='The port will be re-programmed in a few minutes, please check the server log';
    Dataset.FieldByName('restart_now').Value := 1; // flag to server
  end;

end;

procedure Tdm0.MyConnection1ConnectionLost(Sender: TObject;
  Component: TComponent; ConnLostCause: TConnLostCause;
  var RetryMode: TRetryMode);
begin
  //ShowMessage('Error: Connection to the NAC MySQL server lost');
  RetryMode:=rmRaise;
  //fmInventory.Disconnect;
end;

procedure Tdm0.dsVlanDataChange(Sender: TObject; Field: TField);
begin
  // TBD: Refresh the EditDevices Query
  //dm0.quSystems.Refresh
end;

procedure Tdm0.quSmschallengeBeforeUpdateExecute(Sender: TCustomMyDataSet;
  StatementTypes: TStatementTypes; Params: TDAParams);
var vpn_allowed: integer;
begin
  //ShowMessage('quSmschallengeBeforeUpdateExecute ' +dm0.quUsers.FieldbyName('id').AsString
  //  +', sql=' +(Sender as TMyQuery).SQL.Text);
  Params.ParamByName('u_id').AsString := dm0.quUsers.FieldbyName('id').AsString;

  if fmInventory.cxSmsChallenge.State =cbsChecked then begin
     //ShowMessage('Enabled SmsChallenge');
     vpn_allowed:=1;
  end else if fmInventory.cxSmsChallenge.State =cbsUnChecked then begin
     //ShowMessage('Disabled SmsChallenge');
     vpn_allowed:=0;
  end else begin
     exit;  // quite this routine, do nothing
  end;

  if (StatementTypes = [stInsert]) or (StatementTypes = [stUpdate]) then begin
    Params.ParamByName('vpn_allowed').AsInteger := vpn_allowed;
    //ShowMessage('quSmschallengeBeforeUpdateExecute update uid=' +dm0.quUsers.FieldbyName('id').AsString
    //   +', vpn=' +Params.ParamByName('vpn_allowed').AsString
    //   +', sql=' +(Sender as TMyQuery).SQLUpdate.Text);
  end;
end;

procedure Tdm0.quSmschallengeBeforeRefresh(DataSet: TDataSet);
begin
  //ShowMEssage('quSmschallengeBeforeRefresh');
end;

end.
