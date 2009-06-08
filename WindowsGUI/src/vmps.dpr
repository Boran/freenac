program vmps;

uses
  Forms,
  fmAboutInvent in 'fmAboutInvent.pas' {fmAboutInventory},
  Main in 'Main.pas' {fmInventory},
  fmKeygen in 'fmKeygen.pas' {fmKeyGeneration},
  fmStaticInv in 'fmStaticInv.pas' {fmStaticInventory},
  dm in 'dm.pas' {dm0: TDataModule};

{$R *.RES}

begin
  Application.Initialize;
  Application.Title := 'FreeNAC Administration';
  Application.CreateForm(TfmInventory, fmInventory);
  Application.CreateForm(TfmAboutInventory, fmAboutInventory);
  Application.CreateForm(TfmKeyGeneration, fmKeyGeneration);
  Application.CreateForm(TfmStaticInventory, fmStaticInventory);
  Application.CreateForm(Tdm0, dm0);
  Application.Run;
end.
