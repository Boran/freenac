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

