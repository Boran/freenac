object dm0: Tdm0
  OldCreateOrder = False
  Left = 629
  Top = 420
  Height = 413
  Width = 920
  object quVlanList: TMyQuery
    Connection = MyConnection1
    SQL.Strings = (
      'SELECT vlan_description,default_name,default_id,id'
      'FROM vlan ORDER BY vlan_description')
    Left = 512
    Top = 11
    object quVlanListvlan_description: TStringField
      FieldName = 'vlan_description'
      Origin = 'vlan.vlan_description'
      Size = 100
    end
    object quVlanListdefault_name: TStringField
      FieldName = 'default_name'
      Origin = 'vlan.default_name'
      Size = 30
    end
    object quVlanListdefault_id: TIntegerField
      FieldName = 'default_id'
      Origin = 'vlan.default_id'
    end
    object quVlanListid: TIntegerField
      AutoGenerateValue = arAutoInc
      FieldName = 'id'
      Origin = 'vlan.id'
    end
  end
  object dsVlanList: TDataSource
    DataSet = quVlanList
    Left = 512
    Top = 56
  end
  object taVmpslog: TMyTable
    TableName = 'naclog'
    OrderFields = 'id DESC'
    Limit = 500
    ReadOnly = True
    Connection = MyConnection1
    Debug = True
    FetchRows = 500
    FetchAll = False
    IndexFieldNames = 'id'
    Left = 16
    Top = 8
    object taVmpslogwho: TIntegerField
      FieldName = 'who'
      Origin = 'naclog.who'
    end
    object taVmpslogdatetime: TDateTimeField
      FieldName = 'datetime'
      Origin = 'naclog.datetime'
      DisplayFormat = 'yyyy-mm-dd hh:mm'
    end
    object taVmpslogpriority: TStringField
      FieldName = 'priority'
      Origin = 'naclog.priority'
      FixedChar = True
      Size = 4
    end
    object taVmpslogwhat: TStringField
      FieldName = 'what'
      Origin = 'naclog.what'
      Size = 200
    end
    object taVmpsloghost: TStringField
      FieldName = 'host'
      Origin = 'naclog.host'
      Size = 30
    end
    object taVmpslogid: TLargeintField
      AutoGenerateValue = arAutoInc
      FieldName = 'id'
      Origin = 'naclog.id'
    end
  end
  object dsVmpslog: TDataSource
    DataSet = taVmpslog
    Left = 16
    Top = 56
  end
  object quGuilog: TMyQuery
    SQLRefresh.Strings = (
      'SELECT'
      '  datetime, priority, what, host, who,'
      '  CONCAT(users.GivenName, '#39' '#39', users.Surname) as user'
      '  FROM guilog'
      '  LEFT JOIN users on guilog.who=users.id'
      '  ORDER BY datetime DESC LIMIT 0,500')
    Connection = MyConnection1
    SQL.Strings = (
      'SELECT'
      '  guilog.id, who, datetime, priority, what, host, who,'
      '  CONCAT(users.GivenName, '#39' '#39', users.Surname) as user'
      '  FROM guilog'
      '  LEFT JOIN users on guilog.who=users.id'
      '  ORDER BY datetime DESC LIMIT 0,500')
    ReadOnly = True
    Options.StrictUpdate = False
    FetchAll = False
    Left = 68
    Top = 8
    object StringField2: TStringField
      FieldName = 'user'
      Origin = '.`'#12#8'`'
      Size = 201
    end
    object DateTimeField1: TDateTimeField
      FieldName = 'datetime'
      Origin = 'guilog.datetime'
      DisplayFormat = 'yyyy-mm-dd hh:mm'
    end
    object StringField4: TStringField
      FieldName = 'priority'
      Origin = 'guilog.priority'
      FixedChar = True
      Size = 4
    end
    object StringField8: TStringField
      FieldName = 'what'
      Origin = 'guilog.what'
      Size = 200
    end
    object StringField9: TStringField
      FieldName = 'host'
      Origin = 'guilog.host'
    end
    object IntegerField1: TIntegerField
      FieldName = 'who'
      Origin = 'guilog.who'
    end
    object quGuilogid: TLargeintField
      AutoGenerateValue = arAutoInc
      FieldName = 'id'
      Origin = 'guilog.id'
    end
    object quGuilogwho_1: TIntegerField
      FieldName = 'who_1'
      Origin = 'guilog.who'
    end
  end
  object dsGuilog: TDataSource
    DataSet = quGuilog
    Left = 72
    Top = 56
  end
  object dsSwitch: TDataSource
    DataSet = quSwitch
    Left = 160
    Top = 56
  end
  object quSwitch: TMyQuery
    SQLInsert.Strings = (
      'INSERT INTO switch'
      '    (ip,name,location,swgroup,notify,scan,scan3,vlan_id)'
      '  VALUES'
      '    (:ip,:name,:location,:swgroup,:notify,:scan,:scan3,:vlan_id)')
    SQLDelete.Strings = (
      'DELETE FROM switch'
      '  WHERE id=:Old_id')
    SQLUpdate.Strings = (
      'UPDATE switch s'
      
        '  SET s.ip=:ip, s.name=:name, s.comment=:comment, s.swgroup=:swg' +
        'roup, s.notify=:notify, '
      
        '  s.location=:location, s.scan=:scan, s.scan3=:scan3, s.vlan_id=' +
        ':vlan_id, s.switch_type=:switch_type'
      '  WHERE s.id=:Old_id')
    Connection = MyConnection1
    SQL.Strings = (
      
        'SELECT switch.id, ip, switch.name, switch.switch_type, location,' +
        ' location.name as LocationName,'
      '  building.name as building, comment, swgroup, notify, vlan_id,'
      '  scan, scan3, hw, sw, last_monitored, up '
      '  FROM switch '
      '  LEFT JOIN location ON switch.location = location.id '
      '  LEFT JOIN building ON location.building_id = building.id')
    AfterPost = quSwitchAfterPost
    Left = 160
    Top = 3
    object quSwitchid: TIntegerField
      AutoGenerateValue = arAutoInc
      FieldName = 'id'
      Origin = 'switch.id'
    end
    object quSwitchip: TStringField
      FieldName = 'ip'
      Origin = 'switch.ip'
    end
    object quSwitchname: TStringField
      FieldName = 'name'
      Origin = 'switch.name'
    end
    object quSwitchswgroup: TStringField
      FieldName = 'swgroup'
      Origin = 'switch.swgroup'
    end
    object quSwitchnotify: TStringField
      FieldName = 'notify'
      Origin = 'switch.notify'
      Size = 200
    end
    object quSwitchscan: TBooleanField
      FieldName = 'scan'
      Origin = 'switch.scan'
    end
    object quSwitchbuilding: TStringField
      FieldName = 'building'
      Origin = 'building.name'
      Size = 64
    end
    object builLookupComponent: TStringField
      FieldKind = fkLookup
      FieldName = 'builLookup'
      LookupDataSet = quLocation
      LookupKeyFields = 'id'
      LookupResultField = 'BuildingName'
      KeyFields = 'location'
      Lookup = True
    end
    object quSwitchcomment: TStringField
      FieldName = 'comment'
      Origin = 'switch.comment'
      Size = 50
    end
    object quSwitchlocation: TIntegerField
      FieldName = 'location'
      Origin = 'switch.location'
    end
    object quSwitchLocationName: TStringField
      FieldName = 'LocationName'
      Origin = 'location.name'
      Size = 64
    end
    object locLookupComp: TStringField
      DisplayWidth = 40
      FieldKind = fkLookup
      FieldName = 'locLookup'
      LookupDataSet = quLocation
      LookupKeyFields = 'id'
      LookupResultField = 'LocationAndBuilding'
      KeyFields = 'location'
      Size = 40
      Lookup = True
    end
    object quSwitchhw: TStringField
      FieldName = 'hw'
      Origin = 'switch.hw'
      FixedChar = True
      Size = 64
    end
    object quSwitchsw: TStringField
      FieldName = 'sw'
      Origin = 'switch.sw'
      FixedChar = True
      Size = 64
    end
    object quSwitchlast_monitored: TDateTimeField
      FieldName = 'last_monitored'
      DisplayFormat = 'yyyy-mm-dd hh:mm'
    end
    object quSwitchup: TIntegerField
      FieldName = 'up'
    end
    object quSwitchvlan_id: TIntegerField
      FieldName = 'vlan_id'
    end
    object quSwitchscan3: TBooleanField
      FieldName = 'scan3'
    end
    object quSwitchswitch_type: TWordField
      FieldName = 'switch_type'
    end
  end
  object quLocation: TMyQuery
    SQLInsert.Strings = (
      'INSERT INTO location (name,building_id)'
      '  VALUES             (:LocationName,:building_id)  ')
    SQLDelete.Strings = (
      'DELETE FROM location'
      '  WHERE id = :Old_1  ')
    SQLUpdate.Strings = (
      'UPDATE location '
      
        '  SET building_id=:building_id, name=:LocationName  WHERE id=:Ol' +
        'd_id')
    SQLRefresh.Strings = (
      
        'SELECT l.id AS _0, l.building_id AS _1, building.name AS _2, l.n' +
        'ame AS _3'
      '  FROM location l'
      '  INNER JOIN building on l.building_id = building.id  '
      '  WHERE l.id = :Old_1 ')
    Connection = MyConnection1
    SQL.Strings = (
      
        'SELECT l.id, l.building_id, building.name as BuildingName, l.nam' +
        'e as LocationName,'
      '  CONCAT(building.name, '#39' '#39', l.name) as LocationAndBuilding'
      '  FROM location l'
      '  LEFT JOIN building on l.building_id = building.id  '
      '  ORDER BY building.name, l.name')
    FetchRows = 1
    MasterFields = 'id'
    DetailFields = 'building_id'
    Left = 12
    Top = 120
    object quLocationid: TIntegerField
      FieldName = 'id'
      Origin = 'location.id'
    end
    object quLocationbuilding_id: TIntegerField
      FieldName = 'building_id'
      Origin = 'location.building_id'
    end
    object quLocationBuildingName: TStringField
      FieldName = 'BuildingName'
      Origin = 'building.name'
      Size = 64
    end
    object quLocationLocationName: TStringField
      FieldName = 'LocationName'
      Origin = 'location.name'
      Size = 64
    end
    object BlookupComp: TStringField
      FieldKind = fkLookup
      FieldName = 'Blookup'
      LookupDataSet = taBuilding
      LookupKeyFields = 'id'
      LookupResultField = 'name'
      KeyFields = 'building_id'
      Size = 40
      Lookup = True
    end
    object quLocationLocationAndBuilding: TStringField
      FieldName = 'LocationAndBuilding'
      Origin = '.LocationAndBuilding'
      Size = 129
    end
  end
  object dsLocations: TDataSource
    DataSet = quLocation
    Left = 16
    Top = 168
  end
  object taBuilding: TMyTable
    TableName = 'building'
    Connection = MyConnection1
    Active = True
    Left = 76
    Top = 120
  end
  object dsBuilding: TDataSource
    DataSet = taBuilding
    Left = 80
    Top = 168
  end
  object quUsers: TMyQuery
    SQLInsert.Strings = (
      
        'INSERT INTO users    (username,GivenName,Surname,Department,rfc8' +
        '22mailbox,HouseIdentifier,PhysicalDeliveryOfficeName,TelephoneNu' +
        'mber,Mobile,nac_rights,manual_direx_sync,comment,location)'
      
        '  VALUES  (:username,:GivenName,:Surname,:Department,:rfc822mail' +
        'box,:HouseIdentifier,:PhysicalDeliveryOfficeName,:TelephoneNumbe' +
        'r,:Mobile,:nac_rights,:manual_direx_sync,:comment,:location)')
    SQLUpdate.Strings = (
      'UPDATE users'
      
        '  SET username=:username, GivenName=:GivenName, Surname=:Surname' +
        ', '
      '  Department=:Department, rfc822mailbox=:rfc822mailbox, '
      
        '  HouseIdentifier=:HouseIdentifier, PhysicalDeliveryOfficeName=:' +
        'PhysicalDeliveryOfficeName, '
      '  TelephoneNumber=:TelephoneNumber, Mobile=:Mobile, '
      '  nac_rights=:nac_rights, manual_direx_sync=:manual_direx_sync, '
      '  comment=:comment, location=:location,'
      '  GuiVlanRights=:GuiVlanRights'
      '  WHERE id=:Old_id ')
    SQLRefresh.Strings = (
      
        'SELECT users.id AS _0, username AS _1, users.GivenName AS _2, us' +
        'ers.Department AS _5, users.rfc822mailbox AS _6, users.HouseIden' +
        'tifier AS _7, users.PhysicalDeliveryOfficeName AS _8, users.Tele' +
        'phoneNumber AS _9, users.Mobile AS _10, users.nac_rights AS _12,' +
        ' users.manual_direx_sync AS _13, users.comment AS _14, users.Gui' +
        'VlanRights AS _15, users.Surname AS _3, users.LastSeenDirectory ' +
        'AS _11, users.location AS _16 FROM users'
      'WHERE users.id = :Old_1')
    Connection = MyConnection1
    SQL.Strings = (
      'SELECT id, username, GivenName, Surname, '
      
        '  CONCAT(Surname, '#39', '#39', GivenName, '#39', '#39', username, '#39', '#39', Departm' +
        'ent) as  GivenNameSurname,'
      '  Department, rfc822mailbox, '
      
        '  HouseIdentifier, PhysicalDeliveryOfficeName, TelephoneNumber, ' +
        'Mobile, '
      '  LastSeenDirectory, nac_rights, manual_direx_sync, '
      '  comment, GuiVlanRights, location'
      '  FROM users ORDER BY Surname, GivenName ')
    AfterPost = quUsersAfterPost
    BeforeDelete = quUsersBeforeDelete
    Left = 200
    Top = 123
    object quUsersid: TIntegerField
      AutoGenerateValue = arAutoInc
      FieldName = 'id'
      Origin = 'users.id'
    end
    object quUsersGivenName: TStringField
      FieldName = 'GivenName'
      Origin = 'users.GivenName'
      Size = 100
    end
    object quUsersDepartment: TStringField
      FieldName = 'Department'
      Origin = 'users.Department'
      Size = 100
    end
    object quUsersrfc822mailbox: TStringField
      FieldName = 'rfc822mailbox'
      Origin = 'users.rfc822mailbox'
      Size = 100
    end
    object quUsersHouseIdentifier: TStringField
      FieldName = 'HouseIdentifier'
      Origin = 'users.HouseIdentifier'
      Size = 100
    end
    object quUsersPhysicalDeliveryOfficeName: TStringField
      FieldName = 'PhysicalDeliveryOfficeName'
      Origin = 'users.PhysicalDeliveryOfficeName'
      Size = 100
    end
    object quUsersTelephoneNumber: TStringField
      FieldName = 'TelephoneNumber'
      Origin = 'users.TelephoneNumber'
      Size = 100
    end
    object quUsersMobile: TStringField
      FieldName = 'Mobile'
      Origin = 'users.Mobile'
      Size = 100
    end
    object quUsersmanual_direx_sync: TIntegerField
      FieldName = 'manual_direx_sync'
      Origin = 'users.manual_direx_sync'
    end
    object quUserscomment: TStringField
      FieldName = 'comment'
      Origin = 'users.comment'
      Size = 200
    end
    object quUsersGuiVlanRights: TStringField
      FieldName = 'GuiVlanRights'
      Origin = 'users.GuiVlanRights'
      Size = 255
    end
    object quUsersSurname: TStringField
      FieldName = 'Surname'
      Origin = 'users.Surname'
      Size = 100
    end
    object quUsersGivenNameSurname: TStringField
      FieldName = 'GivenNameSurname'
      Origin = '.`'#12#8'`'
      Size = 406
    end
    object quUserslocation: TIntegerField
      FieldName = 'location'
      Origin = 'users.location'
    end
    object UserLocationLookupComp: TStringField
      FieldKind = fkLookup
      FieldName = 'LocationLookup'
      LookupDataSet = quLocation
      LookupKeyFields = 'id'
      LookupResultField = 'LocationAndBuilding'
      KeyFields = 'location'
      Lookup = True
    end
    object quUsersnac_rights: TIntegerField
      FieldName = 'nac_rights'
      Origin = 'users.nac_rights'
    end
    object quUsersusername: TStringField
      FieldName = 'username'
      Origin = 'users.username'
      Size = 100
    end
    object quUsersLastSeenDirectory: TDateField
      FieldName = 'LastSeenDirectory'
      Origin = 'users.LastSeenDirectory'
    end
  end
  object dsUsers: TDataSource
    DataSet = quUsers
    Left = 200
    Top = 174
  end
  object dsUserLookup: TDataSource
    DataSet = quUserLookup
    Left = 144
    Top = 168
  end
  object quUserLookup: TMyQuery
    Connection = MyConnection1
    SQL.Strings = (
      'SELECT LastSeenDirectory, Surname, GivenName, Department, '
      
        '   rfc822mailbox, HouseIdentifier, PhysicalDeliveryOfficeName, T' +
        'elephoneNumber, '
      '   Mobile, comment, location, username from users ')
    ReadOnly = True
    UniDirectional = True
    FetchAll = False
    Left = 145
    Top = 127
    object quUserLookupSurname: TStringField
      FieldName = 'Surname'
      Origin = 'users.Surname'
      Size = 100
    end
    object quUserLookupGivenName: TStringField
      FieldName = 'GivenName'
      Origin = 'users.GivenName'
      Size = 100
    end
    object quUserLookupDepartment: TStringField
      FieldName = 'Department'
      Origin = 'users.Department'
      Size = 100
    end
    object quUserLookuprfc822mailbox: TStringField
      FieldName = 'rfc822mailbox'
      Origin = 'users.rfc822mailbox'
      Size = 100
    end
    object quUserLookupHouseIdentifier: TStringField
      FieldName = 'HouseIdentifier'
      Origin = 'users.HouseIdentifier'
      Size = 100
    end
    object quUserLookupPhysicalDeliveryOfficeName: TStringField
      FieldName = 'PhysicalDeliveryOfficeName'
      Origin = 'users.PhysicalDeliveryOfficeName'
      Size = 100
    end
    object quUserLookupTelephoneNumber: TStringField
      FieldName = 'TelephoneNumber'
      Origin = 'users.TelephoneNumber'
      Size = 100
    end
    object quUserLookupMobile: TStringField
      FieldName = 'Mobile'
      Origin = 'users.Mobile'
      Size = 100
    end
    object quUserLookupcomment: TStringField
      FieldName = 'comment'
      Origin = 'users.comment'
      Size = 200
    end
    object quUserLookuplocation: TIntegerField
      FieldName = 'location'
    end
    object UserLookLocationLookup: TStringField
      FieldKind = fkLookup
      FieldName = 'LocationLookup'
      LookupDataSet = quLocation
      LookupKeyFields = 'id'
      LookupResultField = 'LocationAndBuilding'
      KeyFields = 'location'
      Lookup = True
    end
    object quUserLookupLastSeenDirectory: TDateField
      FieldName = 'LastSeenDirectory'
    end
    object quUserLookupusername: TStringField
      FieldName = 'username'
      Size = 100
    end
  end
  object quVlan: TMyQuery
    Connection = MyConnection1
    SQL.Strings = (
      'SELECT id, default_id, default_name,   '
      
        '  vlan_description, vlan_group FROM vlan   ORDER BY default_name' +
        ' ')
    AfterPost = quVlanAfterPost
    BeforeDelete = quVlanBeforeDelete
    Left = 396
    Top = 8
    object quVlanid: TIntegerField
      AutoGenerateValue = arAutoInc
      FieldName = 'id'
      Origin = 'vlan.id'
    end
    object quVlandefault_id: TIntegerField
      FieldName = 'default_id'
      Origin = 'vlan.default_id'
    end
    object quVlandefault_name: TStringField
      FieldName = 'default_name'
      Origin = 'vlan.default_name'
      Size = 30
    end
    object quVlanvlan_description: TStringField
      FieldName = 'vlan_description'
      Origin = 'vlan.vlan_description'
      Size = 100
    end
    object quVlanvlan_group: TIntegerField
      FieldName = 'vlan_group'
      Origin = 'vlan.vlan_group'
    end
  end
  object dsVlan: TDataSource
    DataSet = quVlan
    OnDataChange = dsVlanDataChange
    Left = 392
    Top = 56
  end
  object dsVlanswitch: TDataSource
    DataSet = quVlanswitch
    Left = 448
    Top = 56
  end
  object quVlanswitch: TMyQuery
    Connection = MyConnection1
    SQL.Strings = (
      'SELECT '
      '  vid, swid, vlan_name'
      '  FROM vlanswitch ')
    AfterPost = quVlanswitchAfterPost
    BeforeDelete = quVlanswitchBeforeDelete
    Left = 452
    Top = 8
    object quVlanswitchvid: TIntegerField
      FieldName = 'vid'
      Origin = 'vlanswitch.vid'
    end
    object quVlanswitchswid: TIntegerField
      FieldName = 'swid'
      Origin = 'vlanswitch.swid'
    end
    object quVlanswitchvlan_name: TStringField
      FieldName = 'vlan_name'
      Origin = 'vlanswitch.vlan_name'
      Size = 100
    end
    object VlanswitchSwitchLookup: TStringField
      FieldKind = fkLookup
      FieldName = 'SwitchLookup'
      LookupDataSet = quSwitch
      LookupKeyFields = 'id'
      LookupResultField = 'name'
      KeyFields = 'swid'
      Lookup = True
    end
    object VlanswitchDefVlanLookup: TStringField
      FieldKind = fkLookup
      FieldName = 'DefVlanLookup'
      LookupDataSet = quVlan
      LookupKeyFields = 'id'
      LookupResultField = 'default_name'
      KeyFields = 'vid'
      Lookup = True
    end
  end
  object quSystems: TMyQuery
    SQLInsert.Strings = (
      'INSERT INTO systems'
      '  (name,mac,status)'
      'VALUES'
      '  (:name,:mac,:status)')
    SQLDelete.Strings = (
      'DELETE FROM systems'
      '  WHERE id = :Old_1')
    SQLUpdate.Strings = (
      'UPDATE `systems` SET'
      
        '  uid=:uid, vlan=:vlan, status=:status, name=:name, inventory=:i' +
        'nventory, comment=:comment,'
      
        '  mac=:mac, office=:office, history=:history, os=:os, os1=:os1, ' +
        'os2=:os2, os3=:os3, os4=:os4,'
      
        '  class=:class, expiry=:expiry, class2=:class, ChangeUser=:Chang' +
        'eUser, ChangeDate=:ChangeDate,'
      '  dhcp_ip=:dhcp_ip, dhcp_fix=:dhcp_fix, dns_alias=:dns_alias'
      'WHERE  '
      '  `id` = :Old_1')
    Connection = MyConnection1
    SQL.Strings = (
      
        'SELECT s.id, s.uid, vlan, s.last_hostname, last_nbtname, last_ui' +
        'd, email_on_connect,'
      '  health.value AS HealthStatus, dhcp_fix, dhcp_ip, dns_alias,'
      
        '  v1.default_id as vlan_default_id, v1.id as vlanid,  v2.default' +
        '_name as LastVlan,'
      
        '  v1.default_name as vlanname, v1.vlan_group as vlangroup, v1.vl' +
        'an_description,'
      '  s.status, vstatus.value as statusname,'
      '  s.name, s.inventory, s.comment, '
      '  s.mac, ChangeDate, LastSeen, building.name as building,'
      
        '  s.office, port.name as port, s.LastPort as portid, l2.name as ' +
        'PortLocation, port.comment as PortComment,'
      
        '  switch.ip as switch, switch.name as SwitchName, l3.name as Swi' +
        'tchLocation, '
      '  switch.swgroup, switch.comment as SwitchComment,'
      '  switch.switch_type,'
      
        '  CONCAT(patchcable.outlet, '#39', '#39', l2.name, '#39', '#39', patchcable.comm' +
        'ent) as PatchCable, '
      '  s.history,'
      
        '  users1.username as user_name,      users1.TelephoneNumber as U' +
        'serTel,  '
      
        '  users1.Surname as UserSurname,     users1.GivenName as UserFor' +
        'ename,    users1.Department as UserDept,'
      
        '  users1.rfc822mailbox as UserEmail, users1.HouseIdentifier as U' +
        'serHouse, users1.PhysicalDeliveryOfficeName as UserOffice,'
      
        '  users1.Mobile as UserMobileTel,    users1.LastSeenDirectory as' +
        ' UserLastSeenDirex, '
      '  ChangeUser, users2.username as ChangeUserName, '
      
        '  CONCAT(users2.Surname, '#39' '#39', users2.GivenName, '#39' '#39', users1.Depa' +
        'rtment) as ChangeUserFullName,'
      
        '  s.os, os.value as OsName, os1, os1.value as os1name, os2, os2.' +
        'value as os2name, '
      '  os3, os3.value as os3name, s.os4,'
      
        '  class, class2, cl1.value as ClassName, cl2.value as Class2Name' +
        ','
      '  scannow, s.expiry, r_ip, r_timestamp, r_ping_timestamp  '
      '    '
      '  FROM systems s'
      '  LEFT JOIN vlan v1      on s.vlan = v1.id '
      '  LEFT JOIN vlan v2      on s.LastVlan = v2.id   '
      '  LEFT JOIN vstatus      on s.status = vstatus.id'
      '  LEFT JOIN health       on s.health = health.id    '
      '  LEFT JOIN users users1 on s.uid = users1.id  '
      '  LEFT JOIN users users2 on s.ChangeUser = users2.id   '
      '  LEFT JOIN location l1  ON s.office = l1.id '
      '  LEFT JOIN building     ON l1.building_id = building.id  '
      '  LEFT JOIN port         ON s.LastPort=port.id  '
      '  LEFT JOIN patchcable   ON patchcable.port = port.id   '
      '  LEFT JOIN location l2  ON patchcable.office = l2.id    '
      '  LEFT JOIN switch       ON port.switch = switch.id'
      '  LEFT JOIN location l3  ON switch.location = l3.id  '
      '  LEFT JOIN sys_os   os  ON s.os = os.id '
      '  LEFT JOIN sys_os1  os1 ON s.os1 = os1.id   '
      '  LEFT JOIN sys_os2  os2 ON s.os2 = os2.id '
      '  LEFT JOIN sys_os3  os3 ON s.os3 = os3.id       '
      '  LEFT JOIN sys_class  cl1 ON s.class  = cl1.id        '
      '  LEFT JOIN sys_class2 cl2 ON s.class2 = cl2.id')
    Debug = True
    BeforePost = quSystemsBeforePost
    AfterPost = quSystemsAfterPost
    BeforeDelete = quSystemsBeforeDelete
    OnNewRecord = quSystemsNewRecord
    Options.QuoteNames = True
    Left = 712
    Top = 4
    object quSystemsvlan: TIntegerField
      FieldName = 'vlan'
      Origin = 'systems.vlan'
    end
    object quSystemsvlanname: TStringField
      FieldName = 'vlanname'
      Origin = 'vlan.vlanname'
      Size = 30
    end
    object quSystemsstatusname: TStringField
      FieldName = 'statusname'
      Origin = 'vstatus.value'
      Size = 30
    end
    object quSystemsname: TStringField
      FieldName = 'name'
      Origin = 'systems.name'
      Size = 30
    end
    object quSystemscomment: TStringField
      FieldName = 'comment'
      Origin = 'systems.comment'
      Size = 100
    end
    object quSystemsmac: TStringField
      FieldName = 'mac'
      Origin = 'systems.mac'
      Size = 30
    end
    object quSystemsChangeDate: TStringField
      FieldName = 'ChangeDate'
      Origin = 'systems.ChangeDate'
      Size = 100
    end
    object quSystemsChangeUserName: TStringField
      FieldName = 'ChangeUserName'
      Origin = 'users.ChangeUserName'
      Size = 100
    end
    object quSystemsbuilding: TStringField
      FieldName = 'building'
      Origin = 'building.name'
      Size = 64
    end
    object quSystemsPortComment: TStringField
      FieldName = 'PortComment'
      Origin = 'port.comment'
      Size = 255
    end
    object quSystemsSwitchName: TStringField
      FieldName = 'SwitchName'
      Origin = 'switch.name'
    end
    object quSystemsSwitchLocation: TStringField
      FieldName = 'SwitchLocation'
      Origin = 'location.SwitchLocation'
      Size = 64
    end
    object quSystemsswgroup: TStringField
      FieldName = 'swgroup'
      Origin = 'switch.swgroup'
    end
    object quSystemsLastVlan: TStringField
      FieldName = 'LastVlan'
      Origin = 'vlan.LastVlan'
      Size = 30
    end
    object quSystemshistory: TMemoField
      FieldName = 'history'
      Origin = 'systems.history'
      BlobType = ftMemo
    end
    object quSystemsUserSurname: TStringField
      FieldName = 'UserSurname'
      Origin = 'users.UserSurname'
      Size = 100
    end
    object quSystemsUserForename: TStringField
      FieldName = 'UserForename'
      Origin = 'users.UserForename'
      Size = 100
    end
    object quSystemsUserDept: TStringField
      FieldName = 'UserDept'
      Origin = 'users.UserDept'
      Size = 100
    end
    object quSystemsUserEmail: TStringField
      FieldName = 'UserEmail'
      Origin = 'users.UserEmail'
      Size = 100
    end
    object quSystemsUserHouse: TStringField
      FieldName = 'UserHouse'
      Origin = 'users.UserHouse'
      Size = 100
    end
    object quSystemsUserOffice: TStringField
      FieldName = 'UserOffice'
      Origin = 'users.UserOffice'
      Size = 100
    end
    object quSystemsUserMobileTel: TStringField
      FieldName = 'UserMobileTel'
      Origin = 'users.UserMobileTel'
      Size = 100
    end
    object quSystemsOsName: TStringField
      FieldName = 'OsName'
      Origin = 'sys_os.OsName'
      Size = 30
    end
    object quSystemsClassName: TStringField
      FieldName = 'ClassName'
      Origin = 'sys_class.ClassName'
      Size = 30
    end
    object quSystemsClass2Name: TStringField
      FieldName = 'Class2Name'
      Origin = 'sys_class2.Class2Name'
      Size = 30
    end
    object quSystemsscannow: TSmallintField
      FieldName = 'scannow'
      Origin = 'systems.scannow'
    end
    object quSystemsLastSeen: TDateTimeField
      FieldName = 'LastSeen'
      Origin = 'systems.LastSeen'
      DisplayFormat = 'yyyy-mm-dd hh:mm'
    end
    object quSystemsUserTel: TStringField
      FieldName = 'UserTel'
      Origin = 'users.UserTel'
      Size = 100
    end
    object quSystemsport: TStringField
      FieldName = 'port'
      Origin = 'port.name'
    end
    object quSystemsr_ip: TStringField
      FieldName = 'r_ip'
      Origin = 'systems.r_ip'
    end
    object quSystemsr_timestamp: TDateTimeField
      FieldName = 'r_timestamp'
      Origin = 'systems.r_timestamp'
      DisplayFormat = 'yyyy-mm-dd hh:mm'
    end
    object quSystemsr_ping_timestamp: TDateTimeField
      FieldName = 'r_ping_timestamp'
      Origin = 'systems.r_ping_timestamp'
      DisplayFormat = 'yyyy-mm-dd hh:mm'
    end
    object quSystemsSTATUS: TLargeintField
      FieldName = 'STATUS'
      Origin = 'systems.status'
    end
    object quSystemsUserLastSeenDirex: TDateField
      FieldName = 'UserLastSeenDirex'
      Origin = 'users.UserLastSeenDirex'
    end
    object quSystemsos: TLargeintField
      FieldName = 'os'
      Origin = 'systems.os'
    end
    object quSystemsclass: TLargeintField
      FieldName = 'class'
      Origin = 'systems.class'
    end
    object quSystemsclass2: TLargeintField
      FieldName = 'class2'
      Origin = 'systems.class2'
    end
    object quSystemsvlangroup: TIntegerField
      FieldName = 'vlangroup'
      Origin = 'vlan.vlangroup'
    end
    object quSystemsid: TIntegerField
      AutoGenerateValue = arAutoInc
      FieldName = 'id'
      Origin = 'systems.id'
    end
    object quSystemsvlanid: TIntegerField
      AutoGenerateValue = arAutoInc
      FieldName = 'vlanid'
      Origin = 'vlan.vlanid'
    end
    object quSystemsportid: TIntegerField
      FieldName = 'portid'
      Origin = 'systems.portid'
    end
    object quSystemsos4: TStringField
      FieldName = 'os4'
      Origin = 'systems.os4'
      Size = 64
    end
    object quSystemsswitch: TStringField
      FieldName = 'switch'
      Origin = 'switch.ip'
    end
    object quSystemsuid: TIntegerField
      FieldName = 'uid'
      Origin = 'systems.uid'
    end
    object quSystemsinventory: TStringField
      FieldName = 'inventory'
      Origin = 'systems.inventory'
    end
    object quSystemsoffice: TIntegerField
      FieldName = 'office'
      Origin = 'systems.office'
    end
    object quSystemsos1: TLargeintField
      FieldName = 'os1'
      Origin = 'systems.os1'
    end
    object quSystemsos1name: TStringField
      FieldName = 'os1name'
      Origin = 'sys_os1.os1name'
      Size = 30
    end
    object quSystemsos2: TLargeintField
      FieldName = 'os2'
      Origin = 'systems.os2'
    end
    object quSystemsos2name: TStringField
      FieldName = 'os2name'
      Origin = 'sys_os2.os2name'
      Size = 30
    end
    object quSystemsos3: TLargeintField
      FieldName = 'os3'
      Origin = 'systems.os3'
    end
    object quSystemsos3name: TStringField
      FieldName = 'os3name'
      Origin = 'sys_os3.os3name'
      Size = 30
    end
    object quSystemsuser_name: TStringField
      FieldName = 'user_name'
      Origin = 'users.user_name'
      Size = 100
    end
    object quSystemsChangeUserFullName: TStringField
      FieldName = 'ChangeUserFullName'
      Origin = 'ChangeUserFullName'
      Size = 302
    end
    object quSystemsexpiry: TDateTimeField
      FieldName = 'expiry'
      Origin = 'systems.expiry'
    end
    object quSystemsSwitchComment: TStringField
      FieldName = 'SwitchComment'
      Origin = 'switch.comment'
      Size = 50
    end
    object quSystemsvlan_description: TStringField
      FieldName = 'vlan_description'
      Origin = 'vlan.vlan_description'
      Size = 100
    end
    object quSystemslast_hostname: TStringField
      FieldName = 'last_hostname'
      Origin = 'systems.last_hostname'
      Size = 100
    end
    object quSystemsHealthStatus: TStringField
      FieldName = 'HealthStatus'
      Origin = 'health.value'
      Size = 30
    end
    object quSystemslast_nbtname: TStringField
      FieldName = 'last_nbtname'
      Origin = 'systems.last_nbtname'
      Size = 100
    end
    object quSystemslast_uid: TIntegerField
      FieldName = 'last_uid'
      Origin = 'systems.last_uid'
    end
    object quSystemsemail_on_connect: TStringField
      FieldName = 'email_on_connect'
      Origin = 'systems.email_on_connect'
      Size = 100
    end
    object quSystemsdhcp_fix: TSmallintField
      FieldName = 'dhcp_fix'
      Origin = 'systems.dhcp_fix'
    end
    object quSystemsdhcp_ip: TStringField
      DisplayLabel = 'Fixed IP'
      FieldName = 'dhcp_ip'
      Origin = 'systems.dhcp_ip'
      EditMask = '000\.000\.000\.000;1;'
    end
    object quSystemsdns_alias: TStringField
      FieldName = 'dns_alias'
      Origin = 'systems.dns_alias'
      Size = 200
    end
    object quSystemsPatchCable: TStringField
      FieldName = 'PatchCable'
      Origin = 'PatchCable'
      Size = 353
    end
    object quSystemsPortLocation: TStringField
      FieldName = 'PortLocation'
      Origin = 'location.PortLocation'
      Size = 64
    end
    object quSystemsChangeUser: TIntegerField
      FieldName = 'ChangeUser'
      Origin = 'systems.ChangeUser'
    end
    object quSystemsswitch_type: TWordField
      FieldName = 'switch_type'
      Origin = 'switch.switch_type'
    end
  end
  object dsSystems2: TDataSource
    DataSet = quSystems
    Left = 712
    Top = 56
  end
  object quCable: TMyQuery
    SQLUpdate.Strings = (
      'UPDATE patchcable'
      
        '  SET rack=:rack, rack_location=:rack_location, outlet=:outlet, ' +
        'port=:port, modifiedby=:modifiedby,'
      
        '  other=:rack_outlet, office=:office, type=:type, comment=:comme' +
        'nt, lastchange=:lastchange '
      '  WHERE id=:Old_id   '
      '   ')
    Connection = MyConnection1
    SQL.Strings = (
      'SELECT p.id, rack, rack_location, outlet, other as rack_outlet,'
      
        '  office, location.name as OfficeLocation, type, cabletype.name ' +
        'as cable_name,'
      '  port, p.comment, lastchange, expiry,'
      
        '  modifiedby, CONCAT(users.GivenName, '#39' '#39', users.Surname) as Cha' +
        'ngedBy'
      '  FROM patchcable p'
      '  LEFT JOIN cabletype ON p.type = cabletype.id    '
      '  LEFT JOIN users    ON p.modifiedby = users.id'
      '  LEFT JOIN location ON p.office = location.id'
      '  LEFT JOIN port     ON p.port=port.id'
      '  ORDER BY outlet')
    FetchRows = 100
    BeforePost = quCableBeforePost
    AfterPost = quCableAfterPost
    BeforeDelete = quCableBeforeDelete
    FetchAll = False
    Left = 452
    Top = 128
    object quCableid: TIntegerField
      AutoGenerateValue = arAutoInc
      FieldName = 'id'
      Origin = 'patchcable.id'
    end
    object quCablerack: TStringField
      FieldName = 'rack'
      Origin = 'patchcable.rack'
      Size = 30
    end
    object quCablerack_location: TStringField
      FieldName = 'rack_location'
      Origin = 'patchcable.rack_location'
      Size = 30
    end
    object quCableoutlet: TStringField
      FieldName = 'outlet'
      Origin = 'patchcable.outlet'
      Size = 30
    end
    object quCablerack_outlet: TStringField
      FieldName = 'rack_outlet'
      Origin = 'patchcable.other'
      Size = 30
    end
    object quCablecomment: TStringField
      FieldName = 'comment'
      Origin = 'patchcable.comment'
      Size = 255
    end
    object quCablelastchange: TDateTimeField
      FieldName = 'lastchange'
      Origin = 'patchcable.lastchange'
    end
    object quCableChangedBy: TStringField
      FieldName = 'ChangedBy'
      Origin = '.`'#12#8'`'
      Size = 201
    end
    object quCableexpiry: TDateField
      FieldName = 'expiry'
      Origin = 'patchcable.expiry'
    end
    object quCableoffice: TIntegerField
      DisplayWidth = 128
      FieldName = 'office'
      Origin = 'patchcable.office'
    end
    object quCableOfficeLocation: TStringField
      FieldName = 'OfficeLocation'
      Origin = 'location.name'
      Size = 64
    end
    object PatchOfficeLookupComp: TStringField
      DisplayWidth = 128
      FieldKind = fkLookup
      FieldName = 'officeLookup'
      LookupDataSet = quLocation
      LookupKeyFields = 'id'
      LookupResultField = 'LocationAndBuilding'
      KeyFields = 'office'
      Size = 128
      Lookup = True
    end
    object quCablemodifiedby: TIntegerField
      DisplayWidth = 50
      FieldName = 'modifiedby'
      Origin = 'patchcable.modifiedby'
    end
    object PatchmodifiedbyLookup: TStringField
      DisplayWidth = 50
      FieldKind = fkLookup
      FieldName = 'modifiedbyLookup'
      LookupDataSet = quUsers
      LookupKeyFields = 'id'
      LookupResultField = 'GivenNameSurname'
      KeyFields = 'modifiedby'
      Lookup = True
    end
    object quCableport: TIntegerField
      DisplayWidth = 50
      FieldName = 'port'
      Origin = 'patchcable.port'
    end
    object PatchportlookupComp: TStringField
      DisplayWidth = 50
      FieldKind = fkLookup
      FieldName = 'portlookup'
      LookupDataSet = quPorts
      LookupKeyFields = 'id'
      LookupResultField = 'switchport'
      KeyFields = 'port'
      Size = 50
      Lookup = True
    end
    object quCablecable_name: TStringField
      FieldName = 'cable_name'
      Origin = 'cabletype.name'
      Size = 64
    end
    object quCabletype: TIntegerField
      FieldName = 'type'
      Origin = 'patchcable.type'
    end
  end
  object dsCable: TDataSource
    DataSet = quCable
    Left = 448
    Top = 176
  end
  object quPorts: TMyQuery
    SQLInsert.Strings = (
      'INSERT INTO port'
      
        '    (switch,name,default_vlan,shutdown,restart_now,staticvlan,au' +
        'th_profile,comment)'
      '  VALUES'
      
        '    (:switch,:name,:default_vlan,:shutdown,:restart_now,:staticv' +
        'lan,:auth_profile,:comment)')
    SQLDelete.Strings = (
      'DELETE FROM port'
      'WHERE'
      '  id = :Old_1')
    SQLUpdate.Strings = (
      'UPDATE port'
      
        '  SET switch=:switch, default_vlan=:default_vlan,  name=:name, c' +
        'omment=:comment,'
      
        '      shutdown=:shutdown, restart_now=:restart_now, staticvlan=:' +
        'staticvlan,'
      '      auth_profile=:auth_profile'
      '  WHERE id=:Old_id')
    SQLRefresh.Strings = (
      
        'SELECT DISTINCT port.id AS _0, switch as _1, port.default_vlan A' +
        'S _4,'
      
        '  port.name AS _5, port.restart_now AS _6, port.comment AS _7, p' +
        'ort.last_vlan AS _8, port.last_activity AS _9 '
      '  FROM port'
      '  INNER JOIN switch     ON port.switch = switch.id '
      '  LEFT  JOIN patchcable ON patchcable.port = port.id '
      '  LEFT  JOIN location   ON patchcable.office = location.id    '
      '  WHERE port.id = :Old_1')
    Connection = MyConnection1
    SQL.Strings = (
      
        'SELECT DISTINCT port.id, switch, switch.ip as switchip, switch.n' +
        'ame as SwitchName, '
      
        '  default_vlan, switch.switch_type, last_vlan, v1.default_name a' +
        's LastVlanName, '
      '  port.name,  restart_now, port.comment, last_activity, '
      '  ap1.method as VlanAuth,'
      '  auth_profile, staticvlan, '
      '  port.last_monitored, port.up, port.shutdown,'
      '  CONCAT(switch.name, '#39' '#39', port.name) as switchport  '
      '  FROM port '
      '  INNER JOIN switch     ON port.switch = switch.id '
      '  LEFT  JOIN patchcable ON patchcable.port = port.id '
      '  LEFT  JOIN location   ON patchcable.office = location.id   '
      '  LEFT  JOIN auth_profile ap1 ON ap1.id = port.last_auth_profile'
      '  LEFT  JOIN vlan v1    ON port.last_vlan = v1.id'
      '  ORDER BY SwitchName, port.name')
    BeforePost = quPortsBeforePost
    AfterPost = quPortsAfterPost
    Left = 216
    Top = 3
    object quPortsid: TIntegerField
      AutoGenerateValue = arAutoInc
      FieldName = 'id'
      Origin = 'port.id'
    end
    object quPortsSwitchName: TStringField
      FieldName = 'SwitchName'
      Origin = 'switch.name'
    end
    object quPortsswitchport: TStringField
      DisplayWidth = 50
      FieldName = 'switchport'
      Origin = '.switchport'
      Size = 41
    end
    object quPortscomment: TStringField
      FieldName = 'comment'
      Origin = 'port.comment'
      Size = 255
    end
    object quPortsrestart_now: TIntegerField
      FieldName = 'restart_now'
      Origin = 'port.restart_now'
    end
    object quPortsdefault_vlan: TIntegerField
      FieldName = 'default_vlan'
      Origin = 'port.default_vlan'
    end
    object quPortsname: TStringField
      FieldName = 'name'
      Origin = 'port.name'
    end
    object quPortslast_vlan: TIntegerField
      FieldName = 'last_vlan'
      Origin = 'port.last_vlan'
    end
    object quPortsLastVlanName: TStringField
      FieldName = 'LastVlanName'
      Origin = 'v1.default_name'
      Size = 30
    end
    object quPortslast_activity: TDateTimeField
      FieldName = 'last_activity'
      Origin = 'port.last_activity'
      DisplayFormat = 'yyyy-mm-dd hh:mm'
    end
    object quPortsVlanAuth: TStringField
      FieldName = 'VlanAuth'
      Origin = 'auth_profile.method'
      Size = 16
    end
    object Portsdef_lanLookupComp: TStringField
      FieldKind = fkLookup
      FieldName = 'def_lanLookup'
      LookupDataSet = quVlan
      LookupKeyFields = 'id'
      LookupResultField = 'default_name'
      KeyFields = 'default_vlan'
      Lookup = True
    end
    object quPortsswitchip: TStringField
      FieldName = 'switchip'
      Origin = 'switch.ip'
    end
    object quPortsswitch: TIntegerField
      FieldName = 'switch'
      Origin = 'port.switch'
    end
    object quPortslast_monitored: TDateTimeField
      FieldName = 'last_monitored'
      DisplayFormat = 'yyyy-mm-dd hh:mm'
    end
    object quPortsup: TIntegerField
      FieldName = 'up'
    end
    object quPortsshutdown: TIntegerField
      FieldName = 'shutdown'
    end
    object quPortsstaticvlan: TIntegerField
      FieldName = 'staticvlan'
    end
    object quPortsauth_profile: TLargeintField
      FieldName = 'auth_profile'
      OnChange = quPortsauth_profileChange
    end
    object quPortsswitch_type: TWordField
      FieldName = 'switch_type'
    end
  end
  object dsPort2: TDataSource
    DataSet = quPorts
    Left = 216
    Top = 56
  end
  object taEthernet: TMyTable
    TableName = 'ethernet'
    Connection = MyConnection1
    Debug = True
    Left = 320
    Top = 8
    object taEthernetvendor: TStringField
      FieldName = 'vendor'
      Origin = 'ethernet.vendor'
      Size = 16
    end
    object taEthernetmac: TStringField
      FieldName = 'mac'
      Origin = 'ethernet.mac'
      Size = 6
    end
  end
  object dsEthernet: TDataSource
    DataSet = taEthernet
    Left = 320
    Top = 56
  end
  object taStatus: TMyTable
    TableName = 'vstatus'
    ReadOnly = True
    Connection = MyConnection1
    Debug = True
    UniDirectional = True
    Options.QuoteNames = True
    FetchAll = False
    Left = 264
    Top = 128
    object taStatusid: TLargeintField
      FieldName = 'id'
      Origin = 'vstatus.id'
    end
    object taStatusvalue: TStringField
      FieldName = 'value'
      Origin = 'vstatus.value'
      Size = 30
    end
  end
  object taSys_class: TMyTable
    TableName = 'sys_class'
    OrderFields = 'id'
    Connection = MyConnection1
    Debug = True
    AfterPost = taSys_classAfterPost
    Left = 320
    Top = 128
    object taSys_classid: TLargeintField
      AutoGenerateValue = arAutoInc
      FieldName = 'id'
      Origin = 'sys_class.id'
    end
    object taSys_classvalue: TStringField
      FieldName = 'value'
      Origin = 'sys_class.value'
      Size = 30
    end
  end
  object taSys_class2: TMyTable
    TableName = 'sys_class2'
    OrderFields = 'id'
    Connection = MyConnection1
    Debug = True
    AfterPost = taSys_class2AfterPost
    Left = 384
    Top = 128
    object LargeintField2: TLargeintField
      AutoGenerateValue = arAutoInc
      FieldName = 'id'
      Origin = 'sys_class2.id'
    end
    object StringField6: TStringField
      FieldName = 'value'
      Origin = 'sys_class2.value'
      Size = 30
    end
  end
  object dsStatus: TDataSource
    DataSet = taStatus
    Left = 264
    Top = 177
  end
  object dsSys_class: TDataSource
    DataSet = taSys_class
    Left = 320
    Top = 176
  end
  object dsSys_class2: TDataSource
    DataSet = taSys_class2
    Left = 384
    Top = 176
  end
  object taSys_os: TMyTable
    TableName = 'sys_os'
    OrderFields = 'id'
    Connection = MyConnection1
    Debug = True
    AfterPost = taSys_osAfterPost
    Left = 592
    Top = 128
    object LargeintField1: TLargeintField
      AutoGenerateValue = arAutoInc
      FieldName = 'id'
      Origin = 'sys_os.id'
    end
    object StringField5: TStringField
      FieldName = 'value'
      Origin = 'sys_os.value'
      Size = 30
    end
  end
  object taSys_os1: TMyTable
    TableName = 'sys_os1'
    OrderFields = 'id'
    Connection = MyConnection1
    Debug = True
    AfterPost = taSys_os1AfterPost
    Left = 640
    Top = 128
    object LargeintField3: TLargeintField
      AutoGenerateValue = arAutoInc
      FieldName = 'id'
      Origin = 'sys_os1.id'
    end
    object StringField1: TStringField
      FieldName = 'value'
      Origin = 'sys_os1.value'
      Size = 30
    end
  end
  object taSys_os2: TMyTable
    TableName = 'sys_os2'
    OrderFields = 'id'
    Connection = MyConnection1
    Debug = True
    AfterPost = taSys_os2AfterPost
    Left = 704
    Top = 128
    object LargeintField4: TLargeintField
      AutoGenerateValue = arAutoInc
      FieldName = 'id'
      Origin = 'sys_os2.id'
    end
    object StringField3: TStringField
      FieldName = 'value'
      Origin = 'sys_os2.value'
      Size = 30
    end
  end
  object dsSys_os: TDataSource
    DataSet = taSys_os
    Left = 592
    Top = 176
  end
  object dsSys_os1: TDataSource
    DataSet = taSys_os1
    Left = 640
    Top = 176
  end
  object dsSys_os2: TDataSource
    DataSet = taSys_os2
    Left = 704
    Top = 176
  end
  object dsSys_os3: TDataSource
    DataSet = taSys_os3
    Left = 760
    Top = 176
  end
  object taSys_os3: TMyTable
    TableName = 'sys_os3'
    OrderFields = 'id'
    Connection = MyConnection1
    Debug = True
    AfterPost = taSys_os3AfterPost
    Left = 760
    Top = 128
    object LargeintField5: TLargeintField
      AutoGenerateValue = arAutoInc
      FieldName = 'id'
      Origin = 'sys_os3.id'
    end
    object StringField7: TStringField
      FieldName = 'value'
      Origin = 'sys_os3.value'
      Size = 30
    end
  end
  object MyQuery1: TMyQuery
    Connection = MyConnection1
    Left = 848
    Top = 123
  end
  object dsMyQuery1: TDataSource
    DataSet = MyQuery1
    Left = 848
    Top = 176
  end
  object MySQLMonitor1: TMySQLMonitor
    TraceFlags = [tfQPrepare, tfQExecute, tfQFetch, tfError, tfConnect, tfTransact, tfMisc, tfParams]
    Left = 528
    Top = 264
  end
  object MyConnection1: TMyConnection
    Database = 'opennac'
    Options.KeepDesignConnected = False
    Options.LocalFailover = True
    PoolingOptions.ConnectionLifetime = 60
    Pooling = True
    Username = 'inventwrite'
    Password = 'ki54Fgr8d5'
    Server = 'nacserver.your.domain'
    Connected = True
    BeforeConnect = MyConnection1BeforeConnect
    OnError = MyConnection1Error
    OnConnectionLost = MyConnection1ConnectionLost
    Left = 440
    Top = 264
  end
  object MSConnection1: TMSConnection
    Database = 'inventory'
    Options.PersistSecurityInfo = True
    Options.Provider = prSQL
    Username = 'vmpsread'
    Server = 'INOCESSQL\INOWEB'
    Left = 24
    Top = 252
  end
  object dsStaticInv: TDataSource
    DataSet = quStaticInv
    Left = 96
    Top = 296
  end
  object quStaticInv: TMSQuery
    Connection = MSConnection1
    SQL.Strings = (
      
        'SELECT ID, Updated, InvNr, UPPER(owner) as owner, Description, L' +
        'abel, Tag, '
      '  UPPER(Visum)  as Visum, Updated'
      '  FROM InvToVMPSChanges'
      '  WHERE ChangeType='#39'new'#39' AND VMPSRead=0')
    Left = 96
    Top = 248
  end
  object MSQuery1: TMSQuery
    Connection = MSConnection1
    SQL.Strings = (
      '')
    Left = 152
    Top = 248
  end
  object quConfig: TMyQuery
    Connection = MyConnection1
    SQL.Strings = (
      'SELECT'
      '  config.id, config.type, config.name, config.value, '
      '  config.comment, config.LastChange, config.who,'
      '  CONCAT(users.GivenName, '#39' '#39', users.Surname) as user'
      '  FROM config'
      '  LEFT JOIN users on config.who=users.id'
      '  ORDER BY name')
    BeforePost = quConfigBeforePost
    Left = 592
    Top = 11
    object quConfigid: TIntegerField
      FieldName = 'id'
    end
    object quConfigtype: TStringField
      DisplayLabel = 'DB Type'
      FieldName = 'type'
      Size = 16
    end
    object quConfigname: TStringField
      DisplayLabel = 'Variable Name'
      FieldName = 'name'
      Size = 64
    end
    object quConfigvalue: TStringField
      DisplayLabel = 'Value'
      FieldName = 'value'
      Size = 255
    end
    object quConfigcomment: TStringField
      DisplayLabel = 'Comment / Explanation'
      FieldName = 'comment'
      Size = 255
    end
    object quConfigLastChange: TDateTimeField
      DisplayLabel = 'Last Change'
      FieldName = 'LastChange'
    end
    object quConfigwho: TIntegerField
      DisplayLabel = 'Changed By'
      FieldName = 'who'
    end
    object quConfiguser: TStringField
      DisplayLabel = 'Changed by'
      FieldName = 'user'
      Size = 201
    end
  end
  object dsConfig: TDataSource
    DataSet = quConfig
    Left = 592
    Top = 56
  end
  object quSubnets: TMyQuery
    Connection = MyConnection1
    SQL.Strings = (
      'SELECT id,ip_address,ip_netmask,scan,dontscan from subnets')
    Debug = True
    AfterPost = quSubnetsAfterPost
    BeforeDelete = quSubnetsBeforeDelete
    Options.QuoteNames = True
    Left = 856
    Top = 4
    object quSubnetsid: TIntegerField
      FieldName = 'id'
    end
    object quSubnetsip_address: TStringField
      FieldName = 'ip_address'
    end
    object quSubnetsip_netmask: TIntegerField
      FieldName = 'ip_netmask'
    end
    object quSubnetsscan: TSmallintField
      FieldName = 'scan'
    end
    object quSubnetsdontscan: TStringField
      FieldName = 'dontscan'
      Size = 128
    end
  end
  object dsSubnets: TDataSource
    DataSet = quSubnets
    Left = 856
    Top = 48
  end
  object dsAuth_profile: TDataSource
    DataSet = quAuth_profile
    Left = 232
    Top = 297
  end
  object quAuth_profile: TMyQuery
    Connection = MyConnection1
    SQL.Strings = (
      'SELECT'
      '  * FROM auth_profile')
    BeforePost = quConfigBeforePost
    Active = True
    Left = 232
    Top = 251
    object quAuth_profileid: TLargeintField
      AutoGenerateValue = arAutoInc
      FieldName = 'id'
      Origin = 'auth_profile.id'
    end
    object quAuth_profilemethod: TStringField
      FieldName = 'method'
      Origin = 'auth_profile.method'
      Size = 16
    end
  end
  object quPort1: TMyQuery
    SQLInsert.Strings = (
      'INSERT INTO port'
      
        '    (switch,name,default_vlan,shutdown,restart_now,staticvlan,au' +
        'th_profile,comment)'
      '  VALUES'
      
        '    (:switch,:name,:default_vlan,:shutdown,:restart_now,:staticv' +
        'lan,:auth_profile,:comment)')
    SQLDelete.Strings = (
      'DELETE FROM port'
      'WHERE'
      '  id = :Old_1')
    SQLUpdate.Strings = (
      'UPDATE port'
      
        '  SET switch=:switch, default_vlan=:default_vlan,  name=:name, c' +
        'omment=:comment,'
      
        '      shutdown=:shutdown, restart_now=:restart_now, staticvlan=:' +
        'staticvlan,'
      '      auth_profile=:auth_profile'
      '  WHERE id=:Old_id')
    SQLRefresh.Strings = (
      
        'SELECT DISTINCT port.id AS _0, switch as _1, port.default_vlan A' +
        'S _4,'
      
        '  port.name AS _5, port.restart_now AS _6, port.comment AS _7, p' +
        'ort.last_vlan AS _8, port.last_activity AS _9 '
      '  FROM port'
      '  INNER JOIN switch     ON port.switch = switch.id '
      '  LEFT  JOIN patchcable ON patchcable.port = port.id '
      '  LEFT  JOIN location   ON patchcable.office = location.id    '
      '  WHERE port.id = :Old_1')
    Connection = MyConnection1
    SQL.Strings = (
      
        'SELECT DISTINCT port.id, switch, switch.ip as switchip, switch.n' +
        'ame as SwitchName, '
      '  default_vlan, last_vlan, v1.default_name as LastVlanName, '
      '  port.name,  restart_now, port.comment, last_activity, '
      '  ap1.method as VlanAuth,'
      '  auth_profile, staticvlan, '
      '  port.last_monitored, port.up, port.shutdown,'
      '  CONCAT(switch.name, '#39' '#39', port.name) as switchport  '
      '  FROM port '
      '  INNER JOIN switch     ON port.switch = switch.id '
      '  LEFT  JOIN patchcable ON patchcable.port = port.id '
      '  LEFT  JOIN location   ON patchcable.office = location.id   '
      '  LEFT  JOIN auth_profile ap1 ON ap1.id = port.last_auth_profile'
      '  LEFT  JOIN vlan v1    ON port.last_vlan = v1.id'
      '  ORDER BY SwitchName, port.name'
      'WHERE port.id=:id')
    BeforePost = quPortsBeforePost
    AfterPost = quPortsAfterPost
    Left = 264
    Top = 3
    ParamData = <
      item
        DataType = ftInteger
        Name = 'id'
      end>
    object IntegerField2: TIntegerField
      AutoGenerateValue = arAutoInc
      FieldName = 'id'
      Origin = 'port.id'
    end
    object StringField10: TStringField
      FieldName = 'SwitchName'
      Origin = 'switch.name'
    end
    object StringField11: TStringField
      FieldName = 'switchport'
      Origin = '.switchport'
      Size = 41
    end
    object StringField12: TStringField
      FieldName = 'comment'
      Origin = 'port.comment'
      Size = 255
    end
    object IntegerField3: TIntegerField
      FieldName = 'restart_now'
      Origin = 'port.restart_now'
    end
    object IntegerField4: TIntegerField
      FieldName = 'default_vlan'
      Origin = 'port.default_vlan'
    end
    object StringField13: TStringField
      FieldName = 'name'
      Origin = 'port.name'
    end
    object IntegerField5: TIntegerField
      FieldName = 'last_vlan'
      Origin = 'port.last_vlan'
    end
    object StringField14: TStringField
      FieldName = 'LastVlanName'
      Origin = 'v1.default_name'
      Size = 30
    end
    object DateTimeField2: TDateTimeField
      FieldName = 'last_activity'
      Origin = 'port.last_activity'
      DisplayFormat = 'yyyy-mm-dd hh:mm'
    end
    object StringField15: TStringField
      FieldName = 'VlanAuth'
      Origin = 'auth_profile.method'
      Size = 16
    end
    object StringField16: TStringField
      FieldKind = fkLookup
      FieldName = 'def_lanLookup'
      LookupDataSet = quVlan
      LookupKeyFields = 'id'
      LookupResultField = 'default_name'
      KeyFields = 'default_vlan'
      Lookup = True
    end
    object StringField17: TStringField
      FieldName = 'switchip'
      Origin = 'switch.ip'
    end
    object IntegerField6: TIntegerField
      FieldName = 'switch'
      Origin = 'port.switch'
    end
    object DateTimeField3: TDateTimeField
      FieldName = 'last_monitored'
      DisplayFormat = 'yyyy-mm-dd hh:mm'
    end
    object IntegerField7: TIntegerField
      FieldName = 'up'
    end
    object IntegerField8: TIntegerField
      FieldName = 'shutdown'
    end
    object IntegerField9: TIntegerField
      FieldName = 'staticvlan'
    end
    object LargeintField6: TLargeintField
      FieldName = 'auth_profile'
      OnChange = quPortsauth_profileChange
    end
  end
  object dsPort1: TDataSource
    DataSet = quPorts
    Left = 264
    Top = 56
  end
  object taCableType: TMyTable
    TableName = 'cabletype'
    OrderFields = 'id'
    Connection = MyConnection1
    Debug = True
    AfterPost = taSys_class2AfterPost
    Left = 520
    Top = 128
    object taCableTypeid: TIntegerField
      FieldName = 'id'
    end
    object taCableTypename: TStringField
      FieldName = 'name'
      Size = 64
    end
  end
  object dsCableType: TDataSource
    DataSet = taCableType
    Left = 520
    Top = 176
  end
end
