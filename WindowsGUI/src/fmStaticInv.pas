(**
 * Long description for file:
 * Windows GUI for Freenac - static inventory report
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
unit fmStaticInv;

interface

uses
  Windows, Messages, SysUtils, Variants, Classes, Graphics, Controls, Forms,
  Dialogs, Grids, DBGrids, CRGrid, ExtCtrls, StdCtrls, Buttons;

type
  TfmStaticInventory = class(TForm)
    Panel1: TPanel;
    Panel2: TPanel;
    gridStaticInv: TCRDBGrid;
    bbOK: TBitBtn;
    BitBtn2: TBitBtn;
    procedure bbOKClick(Sender: TObject);
  private
    { Private declarations }
  public
    { Public declarations }
  end;

var
  fmStaticInventory: TfmStaticInventory;

implementation

uses Main, dm;

{$R *.dfm}

procedure TfmStaticInventory.bbOKClick(Sender: TObject);
var
  str1,str2: string;
begin
  with dm0 do begin

  str1:='The following values will be save to the Device '
   +quSystems.FieldByName('name').AsString +' [MAC address '
   +quSystems.FieldByName('mac').AsString +'].'
   +' Owner ='   +quStaticInv.FieldByName('owner').AsString
   +' InvNr ='   +quStaticInv.FieldByName('InvNr').AsString
   +' Comment =' +quStaticInv.FieldByName('Label').AsString
   +', '         +quStaticInv.FieldByName('Tag').AsString;
  str2:='Confirm your changes to Device '
     +quSystems.FieldByName('name').AsString +' [MAC ' +quSystems.FieldByName('mac').AsString +']';

  if MessageDlg(str1, mtWarning, [mbYes, mbNo], 0) = mrNo then
    exit;   // do nothing

  // Write to quSystems
  quSystems.Open;
  quSystems.Edit;
  quSystems.FieldByName('user_name').AsString:=quStaticInv.FieldByName('owner').AsString;
  quSystems.FieldByName('inventory').AsString:=quStaticInv.FieldByName('InvNr').AsString;
  quSystems.FieldByName('Comment').AsString  :=quStaticInv.FieldByName('Label').AsString
             +', '+quStaticInv.FieldByName('Tag').AsString;
  quSystems.Post;
  quSystems.Edit;

  // then set VMPSRead=true and VMPSReadDate   (TBD)
  with MSQuery1 do begin
    SQL.Clear;
    //SQL.Add('UPDATE InvToVMPSChanges set VMPSRead=1  WHERE ID='''
    //  +quStaticInv.FieldByName('ID').AsString      +''''  );
    SQL.Add('UPDATE InvToVMPSChanges SET VMPSRead=1, VMPSReadDate=current_timestamp  WHERE ID='''
      +quStaticInv.FieldByName('ID').AsString      +''''  );
    //ShowMessage(SQL.Text);
    Execute;
    Close;
  end;


  end;
end;

end.
