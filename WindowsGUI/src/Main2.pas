unit Main2;

interface

uses
  SysUtils, Windows, Messages, Classes, Graphics, Controls,
  Forms, Dialogs, StdCtrls, Buttons, ExtCtrls, Menus, ComCtrls,
  DASQLMonitor, MySQLMonitor, DB, MemDS, DBAccess, MyAccess, Mask, DBCtrls,
  dbcgrids, Grids, DBGrids, CRGrid, XPMan, XMLdoc, XMLIntf;

type
  TfmInventory = class(TForm)
    MainMenu: TMainMenu;
    FileNewItem: TMenuItem;
    FileOpenItem: TMenuItem;
    FileSaveItem: TMenuItem;
    FileSaveAsItem: TMenuItem;
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
    MyConnection1: TMyConnection;
    taSystems: TMyTable;
    dsSystem: TDataSource;
    MySQLMonitor1: TMySQLMonitor;
    taStatus: TMyTable;
    taVlan: TMyTable;
    dsStatus: TDataSource;
    dsVlan: TDataSource;
    taSystemsVlanLookup: TStringField;
    taSystemsStatusLookup: TStringField;
    Panel1: TPanel;
    quSystems: TMyQuery;
    XPManifest1: TXPManifest;
    taSystemsvlan: TIntegerField;
    taSystemsstatus: TSmallintField;
    taHistory: TMyTable;
    dsHistory: TDataSource;
    taSystemsname: TStringField;
    taSystemsdescription: TStringField;
    taSystemsmac: TStringField;
    taSystemscomment: TStringField;
    taSystemsChangeDate: TStringField;
    taSystemsChangeUser: TStringField;
    taStatusid: TLargeintField;
    taStatusvalue: TStringField;
    taVlanid: TIntegerField;
    taVlanvalue: TStringField;
    Page: TPageControl;
    tsOverview: TTabSheet;
    tsMonitoring: TTabSheet;
    tsEdit: TTabSheet;
    tsHistory: TTabSheet;
    taHistorywho: TStringField;
    taHistorypriority: TStringField;
    taHistoryhost: TStringField;
    taHistorywhat: TStringField;
    taHistorydatetime: TDateTimeField;
    taOper: TMyTable;
    dsOper: TDataSource;
    taOpervalue: TStringField;
    taOperdatetime: TDateTimeField;
    taOpercomment: TStringField;
    Panel2: TPanel;
    gridSystems: TCRDBGrid;
    Panel3: TPanel;
    Panel4: TPanel;
    gridHistory: TCRDBGrid;
    Panel5: TPanel;
    DBNavigator3: TDBNavigator;
    taSystemsLastSeen: TStringField;
    quSystemsname: TStringField;
    quSystemsdescription: TStringField;
    quSystemsmac: TStringField;
    quSystemscomment: TStringField;
    quSystemsChangeDate: TStringField;
    quSystemsChangeUser: TStringField;
    quSystemsmac_1: TStringField;
    quSystemsvalue: TStringField;
    quSystemsvalue_1: TStringField;
    MyQuery1: TMyQuery;
    Label1: TLabel;
    deMachine: TDBEdit;
    Label2: TLabel;
    DBEdit2: TDBEdit;
    Label3: TLabel;
    DBEdit3: TDBEdit;
    Label4: TLabel;
    DBLookupComboBox1: TDBLookupComboBox;
    Label5: TLabel;
    DBLookupComboBox2: TDBLookupComboBox;
    Label6: TLabel;
    DBEdit4: TDBEdit;
    DBNavigator4: TDBNavigator;
    DBNavigator5: TDBNavigator;
    bbConnect: TButton;
    bbDisconnect: TButton;
    tsEthernetVendors: TTabSheet;
    Memo1: TMemo;
    taSystemsbuilding: TStringField;
    taSystemsoffice: TStringField;
    taSystemsport: TStringField;
    taSystemsswitch: TStringField;
    Label9: TLabel;
    DBEdit5: TDBEdit;
    Label10: TLabel;
    DBEdit6: TDBEdit;
    Label11: TLabel;
    tsVmpslog: TTabSheet;
    taVmpslog: TMyTable;
    StringField1: TStringField;
    StringField2: TStringField;
    StringField3: TStringField;
    StringField4: TStringField;
    DateTimeField1: TDateTimeField;
    dsVmpslog: TDataSource;
    tsPorts: TTabSheet;
    taSwitch: TMyTable;
    taSwitchip: TStringField;
    taSwitchname: TStringField;
    taSwitchlocation: TStringField;
    taSwitchcomment: TStringField;
    dsSwitch: TDataSource;
    taPort: TMyTable;
    dsPort: TDataSource;
    taPortSwitchLookup: TStringField;
    taPortswitch: TStringField;
    taPortname: TStringField;
    taPortlocation: TStringField;
    taSystemsSwitchNameLookup: TStringField;
    taSystemsPortLocationLookup: TStringField;
    taSystemsLastVlan: TStringField;
    tsSwitches: TTabSheet;
    Panel6: TPanel;
    Panel7: TPanel;
    DBNavigator7: TDBNavigator;
    CRDBGrid2: TCRDBGrid;
    Panel8: TPanel;
    Panel9: TPanel;
    CRDBGrid3: TCRDBGrid;
    DBNavigator1: TDBNavigator;
    DBNavigator6: TDBNavigator;
    Panel10: TPanel;
    CRDBGrid5: TCRDBGrid;
    Panel11: TPanel;
    DBNavigator8: TDBNavigator;
    Panel12: TPanel;
    Panel13: TPanel;
    CRDBGrid1: TCRDBGrid;
    Button1: TButton;
    DBNavigator9: TDBNavigator;
    taSystemshistory: TMemoField;
    DBMemo1: TDBMemo;
    taPortcomment: TStringField;
    DBNavigator2: TDBNavigator;
    taEthernet: TMyTable;
    taEthernetvendor: TStringField;
    taEthernetmac: TStringField;
    tsVendor: TTabSheet;
    dsEthernet: TDataSource;
    Panel14: TPanel;
    CRDBGrid4: TCRDBGrid;
    Panel15: TPanel;
    DBNavigator10: TDBNavigator;
    taUsers: TMyTable;
    taUsersAssocNtAccount: TStringField;
    taUsersSurname: TStringField;
    taUsersGivenName: TStringField;
    taUsersDepartment: TStringField;
    taUsersrfc822mailbox: TStringField;
    taUsersHouseIdentifier: TStringField;
    taUsersPhysicalDeliveryOfficeName: TStringField;
    taUsersTelephoneNumber: TStringField;
    taUsersMobile: TStringField;
    dsUsers: TDataSource;
    taSystemsUserSurnameLookup: TStringField;
    x: TStringField;
    xDept: TStringField;
    UserMailLookup: TStringField;
    UserHouseLookup: TStringField;
    UserTelLookup: TStringField;
    UserPhyLookup: TStringField;
    UserMobLookup: TStringField;
    GroupBox1: TGroupBox;
    DBText2: TDBText;
    DBText1: TDBText;
    Label12: TLabel;
    Label13: TLabel;
    DBText11: TDBText;
    DBText9: TDBText;
    DBText8: TDBText;
    DBText7: TDBText;
    DBText10: TDBText;
    Label7: TLabel;
    deUserLookup: TDBLookupComboBox;
    DBEdit7: TDBEdit;
    DBText12: TDBText;
    taUsersLastSeenDirex: TDateField;
    taSystemsLastSeenDirexLookup: TStringField;
    GroupBox2: TGroupBox;
    Label8: TLabel;
    DBText4: TDBText;
    DBText5: TDBText;
    Label14: TLabel;
    DBText6: TDBText;
    DBText3: TDBText;
    taSystemsPortCommentLookup: TStringField;
    DBText13: TDBText;
    taSystemsSwitchDetails: TStringField;
    DBText14: TDBText;
    lDns: TLabel;  { &Contents }
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
    procedure Button2Click(Sender: TObject);
    procedure MyConnection1BeforeConnect(Sender: TObject);
    procedure DBNavigator1BeforeAction(Sender: TObject;
      Button: TNavigateBtn);
    procedure taSystemsAfterPost(DataSet: TDataSet);
    procedure bbDisconnectClick(Sender: TObject);
    procedure bbUpdateClick(Sender: TObject);
    procedure taSystemsNewRecord(DataSet: TDataSet);
    procedure taSystemsBeforePost(DataSet: TDataSet);
    procedure gridSystemsDblClick(Sender: TObject);
    procedure gridSystemsCellClick(Column: TColumn);
    procedure gridSystemsTitleClick(Column: TColumn);
    procedure taPortAfterPost(DataSet: TDataSet);
    procedure taSwitchAfterPost(DataSet: TDataSet);
    procedure taSystemsAfterDelete(DataSet: TDataSet);
    procedure taSystemsAfterScroll(DataSet: TDataSet);
    procedure tsEditShow(Sender: TObject);
  end;

var
  fmInventory: TfmInventory;
  Username, Hostname: string;
  promptforpassword, confirmpost: boolean;

implementation

uses fmAboutInvent, WinSock;

{$r *.dfm}


function GetDNSIP(IPAddress:String):String;
var
 WSAData: TWSAData;
 Socket: TSockAddrIn;
begin
 result:=''; // default is empty
 if WSAStartup(MAKEWORD(2,0),WSAData)=0 then
 begin
  Socket.sin_addr.S_addr := inet_addr(PChar(IPAddress));
  result:=StrPas(gethostbyAddr(@Socket.sin_addr.S_addr,SizeOf(Socket.sin_addr.S_addr),AF_INET)^.h_name);
  WSACleanup;
 end;
end;

function GetDNSName(host :String):String;
 type PPInAddr= ^PInAddr;
var
 WSAData: TWSAData;
 HostInfo : PHostEnt;
 Addr     : PPInAddr;
begin
 result:=''; // default is empty
 if WSAStartup(MAKEWORD(2,0),WSAData)=0 then begin
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
  end;
 end;
end;

procedure UpdateDNS;
begin
  // Update DNS label
  //
 (* with fmInventory do
  if Length(deMachine.Text)>0 then begin
    lDns.Caption:=GetDNSName(deMachine.Text);
  end else begin
    lDns.Caption:='';
  end;  *)
end;

/// ------------------- end of functions --------


procedure TfmInventory.ShowHint(Sender: TObject);
begin
  StatusLine.SimpleText := Application.Hint;
end;


procedure TFormCreate(Sender: TObject);
begin
  Application.OnHint := ShowHint;

  // update history
  with taHistory do begin
    Open;
    Insert;
    FieldByName('datetime').Value:=now;
    FieldByName('host').Value:=Hostname;
    FieldByName('who').Value:=Username;
    FieldByName('what').AsString:='VMPS GUI started';
    Post;
    Refresh;
  end;

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


procedure initialise;
var
  User : Array[0..200] of Char;
  NameLen : LongWord;    // integer
  fxml: TextFile;
  xmlfile : string;
  settings: IXMLDocument;
  cNode : IXMLNode;      // node2

begin


  // ---- vmps.xml: Load settings--------
      xmlfile:=ChangeFileExt(ParamStr(0),'.xml');
      if  FileExists(xmlfile) then begin

        settings := TXMLDocument.Create(nil);
        settings.LoadFromFile(xmlfile);
        settings.active:=true;
        if (settings.DocumentElement.GetNodeName <> 'vmps') then begin
          ShowMessage('vmps.xml syntax incorrect');
          exit;
        end;
        cNode:=settings.DocumentElement.ChildNodes.FindNode('mysql');
        if (cNode<>nil) then begin
        //repeat                      // should only be One node under company?
          with fmInventory.MyConnection1 do begin
            Username:=cNode.GetAttribute('username');
           Password:=cNode.GetAttribute('password');
            Server  :=cNode.GetAttribute('server');
            Database:=cNode.GetAttribute('database');
          end;
          //cNode:=cNode.NextSibling;
        //until cNode= nil;
        end;
        cNode:=settings.DocumentElement.ChildNodes.FindNode('settings');
        if (cNode<>nil) then begin
        //repeat                      // should only be One node under company?
          with fmInventory.MyConnection1 do begin
            confirmpost      :=StrToBool(cNode.GetAttribute('confirmpost'));
            promptforpassword:=StrToBool(cNode.GetAttribute('promptforpassword'));
          end;
          //cNode:=cNode.NextSibling;
        //until cNode= nil;
        end;
        settings:=nil;
      end;

(*  if LogToSyslog then begin
        dmJob.IdSysLog1.Active:=true
  end else begin
        dmJob.IdSysLog1.Active:=false;
  end; *)


  fmInventory.taSystems.FieldByname('mac').ValidChars:=
     ['0'..'9', 'A'..'F', 'a'..'f', '.'];

  NameLen:=sizeof(User);
  If (not GetComputerName(User, NameLen)) then   // WinAPI call
    Strcopy(User, 'UNKNOWN');
  Hostname:=User;                    // char[] to string conversion
  If (not GetUserName(User, NameLen)) then   // WinAPI call
    Strcopy(User, 'UNKNOWN');
  Username:=User;                    // char[] to string conversion
  //ShowMessage('Welcome ' +Username +' on ' +Hostname);

  fmInventory.bbDisconnect.Enabled:=false;
  fmInventory.Page.ActivePage:=fmInventory.tsOverview;
  //fmInventory.teOrder.Text:='name';
  //fmInventory.taSystems.OrderFields:=fmInventory.teOrder.Text;
end;

procedure TfmInventory.bbConnectClick(Sender: TObject);
begin
  initialise;
  MyConnection1.Connect;
  taSystems.Open;
  taVlan.Open;
  taStatus.Open;
  taHistory.Open;
  taOper.Open;
  taVmpslog.Open;
  taSwitch.Open;
  taPort.Open;
  taEthernet.Open;  
  if MyConnection1.Connected then begin
    bbConnect.Enabled:=false;
    bbDisconnect.Enabled:=true;
  end;
end;



procedure TfmInventory.Button2Click(Sender: TObject);
begin
 (* me1.Clear;
  me1.Lines.Add('! Update of Active MAC table on ' +DateToStr(now));
  me1.Lines.Add('! ');
      with taSystems do begin
        //Close; Open;               // run query
        //DisableControls;
        First;
        while not (EOF) do begin   // search lines
          if (FieldByName('Status').AsInteger = 1) then begin
            me1.Lines.Add('address ' +FieldByName('MAC address').AsString
              +' vlan-name ' +FieldByName('VlanLookup').AsString);
          end;
          next;
        end;                // while
        //close;              // details
        //EnableControls;
      end;                  // with details
  me1.Lines.Add('! ');
  me1.Lines.Add('! End of List'); *)
end;

procedure TfmInventory.MyConnection1BeforeConnect(Sender: TObject);
begin
  initialise;
end;

procedure TfmInventory.DBNavigator1BeforeAction(Sender: TObject;
  Button: TNavigateBtn);
begin
  UpdateDNS;
  if Button = nbDelete then begin
      //Cancel, Delete, First, Insert, Last, Next
    //ShowMessage('Entry is going to be deleted!');
  end;
end;

procedure TfmInventory.taSystemsAfterPost(DataSet: TDataSet);
begin
  // update history
  with taHistory do begin
    Open;
    Insert;
    FieldByName('datetime').Value:=now;
    FieldByName('host').Value:=Hostname;
    FieldByName('who').Value:=Username;
    FieldByName('what').AsString:='Systems table updated from GUI';
    Post;
    Refresh;
  end;
  //taSystems.Refresh;
end;

procedure TfmInventory.bbDisconnectClick(Sender: TObject);
begin
  MyConnection1.Disconnect;
  if not MyConnection1.Connected then begin
    bbConnect.Enabled:=true;
    bbDisconnect.Enabled:=false;
  end;
end;

procedure TfmInventory.bbUpdateClick(Sender: TObject);
begin
  with MyQuery1 do begin
    Close;
    SQL.Clear;
    SQL.Add('UPDATE oper SET datetime=now() WHERE value="guiupdate"');
    Execute;
    Close;

  end;
  // update history
  with taHistory do begin
    Open;
    Insert;
    FieldByName('datetime').Value:=now;
    FieldByName('host').Value:=Hostname;
    FieldByName('who').Value:=Username;
    FieldByName('what').AsString:='Server update requested';
    Post;
    Refresh;
  end;

  ShowMessage('An update to the VMPS server is now pending');
  taOper.Refresh;

end;

procedure TfmInventory.taSystemsNewRecord(DataSet: TDataSet);
begin
  // defaults
  Dataset.FieldByName('name').AsString:='INOxxxyyy';
  Dataset.FieldByName('mac').AsString:='0000.0000.0000';
  Dataset.FieldByName('vlan').Value:=13;
  Dataset.FieldByName('status').Value:=1;
end;

procedure TfmInventory.taSystemsBeforePost(DataSet: TDataSet);
var
  c: char;
  i: integer;
begin
  // make sure there are no spaces in the MAC!
  if ( AnsiPos(' ', Dataset.FieldByName('mac').AsString) >0) then begin
    ShowMessage('MAC address contains spaces! It must be a hex number in the format xxxx.xxxx.xxxx');
    Abort;
  end;

  for i:=1 to 16 do begin
    c:=Dataset.FieldByName('mac').AsString[i];
 (*   if i in [5,9] then begin
      if ( c <>'.') then begin
        ShowMessage('MAC address must have a "." not a "' +c  +'" in the ' +intToStr(i) +' position');
        Abort;
      end;
    end else if (c not in ['0','1','2','3','4','5','6','7','8','9','a','b','c','d']) then begin
      ShowMessage('MAC address must have a hex digit not a "' +c  +'" in the ' +intToStr(i) +' position');
      Abort;
    end;    *)
  end;

end;

procedure TfmInventory.gridSystemsDblClick(Sender: TObject);
begin
//  with TDBGrid(Sender).DataSource.DataSet do begin
    //ShowMessage(FieldByName('name').AsString);
//  end;
  //ShowMessage(TDBGrid(Sender).SelectedField.Displaytext);
  //TDBGrid(Sender).Hint:=TDBGrid(Sender).SelectedField.Displaytext;
  Page.ActivePage:=tsEdit;
end;

procedure TfmInventory.gridSystemsCellClick(Column: TColumn);
begin
  //ShowMessage(Column.Field.Displayname);
  // or (Column.Field.Displayname='comment')
  if (Column.Field.Displayname='LastSeen') then
    ShowMessage(Column.Field.Displaytext);
end;

procedure TfmInventory.gridSystemsTitleClick(Column: TColumn);
begin
  // TBD: Set sort order for Lookup Columns, doesn't work...
(*  if Column.FieldName='StatusLookup' then begin
    ShowMessage('change sort');
    taSystems.OrderFields:='status';
  end else if Column.FieldName='VlanLookup' then begin
    taSystems.OrderFields:='vlan';
  end else if Column.FieldName='SwitchNameLookup' then begin
    taSystems.OrderFields:='switch';
  end; *)
end;



procedure TfmInventory.taPortAfterPost(DataSet: TDataSet);
begin
  // update history
  with taHistory do begin
    Open;
    Insert;
    FieldByName('datetime').Value:=now;
    FieldByName('host').Value:=Hostname;
    FieldByName('who').Value:=Username;
    FieldByName('what').AsString:='Ports table updated from GUI';
    Post;
    Refresh;
  end;
end;

procedure TfmInventory.taSwitchAfterPost(DataSet: TDataSet);
begin
  // update history
  with taHistory do begin
    Open;
    Insert;
    FieldByName('datetime').Value:=now;
    FieldByName('host').Value:=Hostname;
    FieldByName('who').Value:=Username;
    FieldByName('what').AsString:='Switch table updated from GUI';
    Post;
    Refresh;
  end;
end;

procedure TfmInventory.taSystemsAfterDelete(DataSet: TDataSet);
begin
  with taHistory do begin
    Open;
    Insert;
    FieldByName('datetime').Value:=now;
    FieldByName('host').Value:=Hostname;
    FieldByName('who').Value:=Username;
    FieldByName('what').AsString:='Systems table: entry deleted';
    Post;
    Refresh;
  end;
end;

procedure TfmInventory.taSystemsAfterScroll(DataSet: TDataSet);
var
  err : string;
begin

end;



procedure TfmInventory.tsEditShow(Sender: TObject);
begin
  UpdateDNS;
end;

end.
