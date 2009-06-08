object fmStaticInventory: TfmStaticInventory
  Left = 504
  Top = 78
  Width = 829
  Height = 267
  Caption = 'Static Inventory: pending items'
  Color = clBtnFace
  Font.Charset = DEFAULT_CHARSET
  Font.Color = clWindowText
  Font.Height = -11
  Font.Name = 'MS Sans Serif'
  Font.Style = []
  OldCreateOrder = False
  PixelsPerInch = 96
  TextHeight = 13
  object Panel1: TPanel
    Left = 0
    Top = 0
    Width = 821
    Height = 199
    Align = alClient
    TabOrder = 0
    object gridStaticInv: TCRDBGrid
      Left = 1
      Top = 1
      Width = 819
      Height = 197
      Hint = 'Select the Device to copy the Inventory Number, Owner and Label'
      Align = alClient
      DataSource = dm0.dsStaticInv
      ReadOnly = True
      TabOrder = 0
      TitleFont.Charset = DEFAULT_CHARSET
      TitleFont.Color = clWindowText
      TitleFont.Height = -11
      TitleFont.Name = 'MS Sans Serif'
      TitleFont.Style = []
      Columns = <
        item
          Expanded = False
          FieldName = 'Updated'
          Width = 118
          Visible = True
        end
        item
          Expanded = False
          FieldName = 'InvNr'
          Width = 57
          Visible = True
        end
        item
          Expanded = False
          FieldName = 'owner'
          Width = 75
          Visible = True
        end
        item
          Expanded = False
          FieldName = 'Description'
          Width = 138
          Visible = True
        end
        item
          Expanded = False
          FieldName = 'Label'
          Width = 201
          Visible = True
        end
        item
          Expanded = False
          FieldName = 'Tag'
          Width = 57
          Visible = True
        end
        item
          Expanded = False
          FieldName = 'Visum'
          Width = 72
          Visible = True
        end>
    end
  end
  object Panel2: TPanel
    Left = 0
    Top = 199
    Width = 821
    Height = 41
    Align = alBottom
    TabOrder = 1
    object bbOK: TBitBtn
      Left = 344
      Top = 8
      Width = 75
      Height = 25
      TabOrder = 0
      OnClick = bbOKClick
      Kind = bkOK
    end
    object BitBtn2: TBitBtn
      Left = 456
      Top = 8
      Width = 75
      Height = 25
      TabOrder = 1
      Kind = bkCancel
    end
  end
end
