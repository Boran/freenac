object fmKeyGeneration: TfmKeyGeneration
  Left = 1019
  Top = 53
  BorderStyle = bsDialog
  Caption = 'vmps.xml key generation'
  ClientHeight = 185
  ClientWidth = 238
  Color = clBtnFace
  ParentFont = True
  OldCreateOrder = True
  Position = poScreenCenter
  PixelsPerInch = 96
  TextHeight = 13
  object Label1: TLabel
    Left = 8
    Top = 9
    Width = 77
    Height = 13
    Caption = 'Enter username:'
  end
  object Label2: TLabel
    Left = 8
    Top = 57
    Width = 76
    Height = 13
    Caption = 'Enter password:'
  end
  object eKey: TLabel
    Left = 8
    Top = 137
    Width = 74
    Height = 13
    Caption = 'Generated Key:'
  end
  object eUsername: TEdit
    Left = 8
    Top = 27
    Width = 121
    Height = 21
    TabOrder = 0
  end
  object OKBtn: TButton
    Left = 14
    Top = 107
    Width = 75
    Height = 25
    Caption = 'Generate Key'
    Default = True
    TabOrder = 1
    OnClick = OKBtnClick
  end
  object eUserpassword: TEdit
    Left = 8
    Top = 75
    Width = 121
    Height = 21
    PasswordChar = '*'
    TabOrder = 2
  end
  object eGeneratedKey: TEdit
    Left = 8
    Top = 155
    Width = 217
    Height = 21
    TabOrder = 3
  end
  object Button1: TButton
    Left = 150
    Top = 107
    Width = 75
    Height = 25
    Caption = 'Quit'
    Default = True
    ModalResult = 1
    TabOrder = 4
    OnClick = OKBtnClick
  end
end
