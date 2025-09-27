INSERT INTO license (
    license_id, name, license_type, description, url,
    allow_commercial_use, allow_modification,
    sort_order, admin_create, time_create, active
) VALUES
-- MIT License
('mit', 'MIT License', 'Permissive',
 'A short and simple permissive license with conditions only requiring preservation of copyright and license notices. 
 Licensed works, modifications, and larger works may be distributed under different terms and without source code.',
 'https://opensource.org/licenses/MIT',
 1, 1, 1, 'system', CURRENT_TIMESTAMP, 1),

-- Apache License 2.0
('apache-2.0', 'Apache License 2.0', 'Permissive',
 'A permissive license similar to the MIT License, but also provides an express grant of patent rights from contributors to users.',
 'https://www.apache.org/licenses/LICENSE-2.0',
 1, 1, 2, 'system', CURRENT_TIMESTAMP, 1),

-- GNU General Public License v3.0
('gpl-3.0', 'GNU General Public License v3.0', 'Copyleft',
 'A strong copyleft license that requires modified versions to be also open source and distributed under the same license.',
 'https://www.gnu.org/licenses/gpl-3.0.html',
 1, 1, 3, 'system', CURRENT_TIMESTAMP, 1),

-- GNU Lesser General Public License v3.0
('lgpl-3.0', 'GNU Lesser General Public License v3.0', 'Weak Copyleft',
 'A weak copyleft license that allows linking to non-(L)GPL licensed software, 
 commonly used for libraries.',
 'https://www.gnu.org/licenses/lgpl-3.0.html',
 1, 1, 4, 'system', CURRENT_TIMESTAMP, 1),

-- Mozilla Public License 2.0
('mpl-2.0', 'Mozilla Public License 2.0', 'Weak Copyleft',
 'A weak copyleft license that allows redistribution under different terms, 
 but modifications to MPL-covered files must remain under MPL.',
 'https://www.mozilla.org/en-US/MPL/2.0/',
 1, 1, 5, 'system', CURRENT_TIMESTAMP, 1),

-- BSD 2-Clause License
('bsd-2-clause', 'BSD 2-Clause "Simplified" License', 'Permissive',
 'A permissive license with minimal requirements: redistribution must retain copyright and license notices.',
 'https://opensource.org/licenses/BSD-2-Clause',
 1, 1, 6, 'system', CURRENT_TIMESTAMP, 1),

-- BSD 3-Clause License
('bsd-3-clause', 'BSD 3-Clause "New" or "Revised" License', 'Permissive',
 'Like the BSD 2-Clause License but with an additional non-endorsement clause.',
 'https://opensource.org/licenses/BSD-3-Clause',
 1, 1, 7, 'system', CURRENT_TIMESTAMP, 1),

-- Eclipse Public License 2.0
('epl-2.0', 'Eclipse Public License 2.0', 'Weak Copyleft',
 'A weak copyleft license often used by Eclipse Foundation projects, 
 allows linking with other licenses but requires source disclosure for EPL components.',
 'https://www.eclipse.org/legal/epl-2.0/',
 1, 1, 8, 'system', CURRENT_TIMESTAMP, 1),

-- Creative Commons Zero v1.0 Universal
('cc0-1.0', 'Creative Commons Zero v1.0 Universal', 'Public Domain Dedication',
 'A license that dedicates works to the public domain, relinquishing as many rights as legally possible.',
 'https://creativecommons.org/publicdomain/zero/1.0/',
 1, 1, 9, 'system', CURRENT_TIMESTAMP, 1),

-- Unlicense
('unlicense', 'The Unlicense', 'Public Domain Dedication',
 'A license that dedicates works to the public domain with a fallback permissive license for jurisdictions 
 where public domain dedication is not possible.',
 'https://unlicense.org/',
 1, 1, 10, 'system', CURRENT_TIMESTAMP, 1);
