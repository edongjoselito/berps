; BERPS Windows installer (Inno Setup)
; Build: iscc /DAppVersion=1.0.0 packaging\BERPS.iss
; Produces dist\BERPS-Setup.exe

#ifndef AppVersion
  #define AppVersion "0.0.0"
#endif

[Setup]
AppId={{8F3A2C1E-5B7D-4E9F-A6C3-2D8E1F4B7A9C}}
AppName=BERPS
AppVersion={#AppVersion}
AppPublisher=SoftTech Services
AppPublisherURL=https://berps.online
DefaultDirName={autopf}\BERPS
DefaultGroupName=BERPS
OutputDir=..\dist
OutputBaseFilename=BERPS-Setup
Compression=lzma2
SolidCompression=yes
ArchitecturesAllowed=x64compatible
ArchitecturesInstallIn64BitMode=x64compatible
WizardStyle=modern
UninstallDisplayIcon={app}\BERPS.exe

[Tasks]
Name: "desktopicon"; Description: "Create a desktop shortcut"; GroupDescription: "Additional icons:"

[Files]
Source: "..\build\windows\x64\runner\Release\*"; DestDir: "{app}"; Flags: recursesubdirs createallsubdirs ignoreversion

[Icons]
Name: "{group}\BERPS"; Filename: "{app}\BERPS.exe"
Name: "{autodesktop}\BERPS"; Filename: "{app}\BERPS.exe"; Tasks: desktopicon

[Run]
Filename: "{app}\BERPS.exe"; Description: "Launch BERPS"; Flags: postinstall nowait skipifsilent
