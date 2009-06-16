(**
 * Long description for file:
 * Windows GUI for Freenac - About dialog
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
unit fmAboutInvent;

interface

uses
  Windows, Messages, SysUtils, Variants, Classes, Graphics, Controls, Forms,
  Dialogs, StdCtrls, ExtCtrls, VersInfo, cxControls, cxContainer, cxEdit,
  cxLabel, jpeg;

type
  TfmAboutInventory = class(TForm)
    Panel1: TPanel;
    Info: TMemo;
    lAuthor: TLabel;
    OKButton: TButton;
    VersionInfoResource1: TVersionInfoResource;
    Image1: TImage;
    procedure FormActivate(Sender: TObject);
    procedure cxLabel1Click(Sender: TObject);
    procedure Image1Click(Sender: TObject);
  private
    { Private declarations }
  public
    { Public declarations }
  end;

var
  fmAboutInventory: TfmAboutInventory;

implementation

uses ShellAPI;

{$R *.dfm}

procedure TfmAboutInventory.FormActivate(Sender: TObject);
const
  FLAG_STRING: array[TFixedFileInfoFlag] of string = (
      'Debug', 'Info Inferred', 'Patched', 'Pre-Release', 'Private Build', 'Special Build');
  OS_STRING: array[TVersionOperatingSystemFlag] of string = (
      'Unknown', 'DOS', 'OS2 16-bit', 'OS2 32-bit', 'Windows NT','Windows 16-bit',
      'Presentation Manager 16-bit', 'Presentation Manager 32-bit', 'Windows 32-bit');
  FILETYPE_STRING: array[TVersionFileType] of string = (
      'Unknown', 'Application', 'DLL', 'Driver', 'Font', 'VXD', 'StaticLib');
var
  AFlag: TFixedFileInfoFlag;
  OSFlag: TVersionOperatingSystemFlag;
  TempStr: string;

begin
  with VersionInfoResource1 do
  begin
    Info.Lines.Clear;
    //Info.Lines.Add('InternalName = ' + InternalName);
    Info.Lines.Add('Copyright: ' + LegalCopyright );
    //Info.Lines.Add('LegalTrademarks = ' + LegalTrademarks);
    //Info.Lines.Add('Comments = ' + Comments);
    //Info.Lines.Add('Product Name = ' + ProductName);
    //Info.Lines.Add('Version = ' + ProductVersion);
    //Info.Lines.Add('FileVersion = ' + FileVersion);
    Info.Lines.Add('Build Version:' +FileVersion +', ' +Comments);
  end;

end;

procedure TfmAboutInventory.cxLabel1Click(Sender: TObject);
begin
  ShellExecute(0, 'open', PChar('http://www.freenac.net'), '', '', SW_SHOW)
end;

procedure TfmAboutInventory.Image1Click(Sender: TObject);
begin
  ShellExecute(0, 'open', PChar('http://www.freenac.net'), '', '', SW_SHOW)
end;

end.
