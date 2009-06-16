(**
 * Long description for file:
 * Windows GUI for Freenac - keygen dialog
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
unit fmKeygen;

interface

uses Windows, SysUtils, Classes, Graphics, Forms, Controls, StdCtrls, 
  Buttons;

type
  TfmKeyGeneration = class(TForm)
    Label1: TLabel;
    eUsername: TEdit;
    OKBtn: TButton;
    Label2: TLabel;
    eUserpassword: TEdit;
    eGeneratedKey: TEdit;
    eKey: TLabel;
    Button1: TButton;
    procedure OKBtnClick(Sender: TObject);
  private
    { Private declarations }
  public
    { Public declarations }
  end;

var
  fmKeyGeneration: TfmKeyGeneration;

implementation

uses DCPcrypt2, DCPblockciphers, DCPrijndael, DCPsha1, Main;

{$R *.dfm}

procedure TfmKeyGeneration.OKBtnClick(Sender: TObject);
var
    Cipher: TDCP_rijndael;
    KeyStr: string;
begin
  (*Cipher:= TDCP_rijndael.Create(nil);
  Cipher.InitStr('it6zbh  lö$ oitiotiuo iouiouiiuoozuioiuozuio',TDCP_sha1);   // initialize the cipher with a hash of the passphrase
  eGeneratedKey.Text:=Cipher.EncryptString(eUsername.Text +':' +eUserpassword.Text);
  Cipher.Burn;
  Cipher.Free;    *)

  eGeneratedKey.Text:=fmInventory.enc(eUsername.Text +':' +eUserpassword.Text);

end;

end.

