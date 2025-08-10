<?php
// Define a list of commonly used date/time formats with their preview values.
// These formats cover ISO, Indonesian, US, and technical formats.
$formats = [
    // Full date-time formats (ISO, technical)
    'Y-m-d H:i:s'     => date('Y-m-d H:i:s'),
    'Y-m-d\TH:i:s'    => date('Y-m-d\TH:i:s'),     // ISO 8601 without timezone
    'c'               => date('c'),                // ISO 8601 with timezone
    'r'               => date('r'),                // RFC 2822 format

    // Indonesian date formats
    'd/m/Y H:i'       => date('d/m/Y H:i'),
    'd-m-Y H:i'       => date('d-m-Y H:i'),
    'd/m/Y'           => date('d/m/Y'),
    'd-m-Y'           => date('d-m-Y'),
    'd F Y'           => date('d F Y'),            // 30 July 2025
    'j F Y'           => date('j F Y'),            // 30 July 2025
    'j F Y H:i'       => date('j F Y H:i'),        // 30 July 2025 03:30
    'j F Y H:i:s'     => date('j F Y H:i:s'),        // 30 July 2025 03:30:30
    'l, j F Y'        => date('l, j F Y'),         // Wednesday, 30 July 2025
    'H:i d-m-Y'       => date('H:i d-m-Y'),

    // US/international formats
    'm/d/Y'           => date('m/d/Y'),
    'm/d/Y h:i A'     => date('m/d/Y h:i A'),      // 12-hour format with AM/PM
    'D, M j, Y'       => date('D, M j, Y'),        // Wed, Jul 30, 2025
    'l, F j, Y h:i A' => date('l, F j, Y h:i A'),  // Wednesday, July 30, 2025 03:30 PM

    // Short and compact technical formats
    'YmdHis'          => date('YmdHis'),           // 20250730153000
    'Y-m'             => date('Y-m'),
    'Y'               => date('Y'),
    'H:i'             => date('H:i'),
    'h:i A'           => date('h:i A'),
    'U'               => date('U'),                // Unix timestamp

    // Miscellaneous / alternative formats
    'j/n/y'           => date('j/n/y'),
    'Y/m/d H:i'       => date('Y/m/d H:i'),
    'M Y'             => date('M Y'),              // Jul 2025
    'F Y'             => date('F Y'),              // July 2025
];
?><?php foreach ($formats as $format => $preview): ?>
  <option value="<?= htmlspecialchars($format) ?>">
    <?= htmlspecialchars("$format ($preview)") ?>
  </option>
<?php endforeach; ?>