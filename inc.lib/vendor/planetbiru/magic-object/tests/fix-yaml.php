<?php
function formatText($text) {
    $lines = explode("\n", $text);
    $formattedText = "";

    foreach ($lines as $line) {
        if (preg_match('/^- [^\s]+:/', ltrim($line))) { // Cek apakah baris diawali dengan '- ' dan diikuti kata tanpa spasi lalu ':'
            $pad = stripos($line, '- ');
            $formattedText .= substr($line, 0, $pad+1)."\n".substr($line, 0, $pad).substr($line, $pad-2, 2).substr($line, $pad+2)."\n";
            // Potong dan tambahkan indentasi
        } else {
            $formattedText .= $line . "\n";
        }
    }

    return $formattedText;
}

$yamlText = <<<EOT
menu:
  - title: "Home"
    icon: "fas fa-home"
    href: "./"
    submenu: []
  - title: "Master"
    icon: "fas fa-folder"
    href: "#submenu1"
    submenu:
      - title: "Application"
        icon: "fas fa-microchip"
        href: "application.php"
      - title: "Application Group"
        icon: "fas fa-microchip"
        href: "application-group.php"
      - title: "Workspace"
        icon: "fas fa-building"
        href: "workspace.php"
      - title: "Admin"
        icon: "fas fa-user"
        href: "admin.php"
  - title: "Role"
    icon: "fas fa-folder"
    href: "#submenu2"
    submenu:
      - title: "Admin Workspace"
        icon: "fas fa-user-check"
        href: "admin-workspace.php"
      - title: "Application Group Member"
        icon: "fas fa-user-check"
        href: "application-group-member.php"
  - title: "Reference"
    icon: "fas fa-folder"
    href: "#submenu3"
    submenu:
      - title: "Administrator Level"
        icon: "fas fa-user-gear"
        href: "admin-level.php"
  - title: "Message"
    icon: "fas fa-folder"
    href: "#submenu4"
    submenu:  
      - title: "Message"
        icon: "fas fa-message"
        href: "message.php"
      - title: "Notification"
        icon: "fas fa-message"
        href: "notification.php"
  - title: "Cache"
    icon: "fas fa-folder"
    href: "#submenu5"
    submenu:
      - title: "Error Cache"
        icon: "fas fa-hdd"
        href: "error-cache.php"
  - title: "MagicAppBuilder"
    icon: "fas fa-desktop"
    href: "../"
    submenu: []
  - title: "Database"
    icon: "fas fa-database"
    href: "../magic-database/"
    submenu: []
    target: "_blank"
EOT;

$formattedText = formatText($yamlText);
echo "<pre>$formattedText</pre>";
