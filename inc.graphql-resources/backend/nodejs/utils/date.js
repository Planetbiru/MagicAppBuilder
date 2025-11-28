function padTo2Digits(num) {
    return num.toString().padStart(2, '0');
}

const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
const dayNames = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];

/**
 * Formats a Date object into a specified string format.
 *
 * **Format Specifiers:**
 * - `YYYY`: 4-digit year (e.g., 2024)
 * - `YY`: 2-digit year (e.g., 24)
 * - `MMMM`: Full month name (e.g., January)
 * - `MMM`: Short month name (e.g., Jan)
 * - `MM`: 2-digit month (e.g., 01)
 * - `M`: Month (e.g., 1)
 * - `DDDD`: Full day name (e.g., Sunday)
 * - `DDD`: Short day name (e.g., Sun)
 * - `DD`: 2-digit day of month (e.g., 09)
 * - `D`: Day of month (e.g., 9)
 * - `d`: Day of week (0 for Sunday, 6 for Saturday)
 * - `HH`: 2-digit hour (24-hour format, e.g., 15)
 * - `H`: Hour (24-hour format, e.g., 15)
 * - `hh`: 2-digit hour (12-hour format, e.g., 03)
 * - `h`: Hour (12-hour format, e.g., 3)
 * - `mm`: 2-digit minute (e.g., 05)
 * - `m`: Minute (e.g., 5)
 * - `ss`: 2-digit second (e.g., 01)
 * - `s`: Second (e.g., 1)
 * - `A`: AM/PM
 * - `a`: am/pm
 *
 * @param {Date} date The date object to format.
 * @param {string} format The format string. Defaults to 'YYYY-MM-DD HH:mm:ss'.
 * @returns {string} The formatted date string.
 */
function formatDate(date, format = 'YYYY-MM-DD HH:mm:ss') {
    if (!(date instanceof Date) || Number.isNaN(date.getTime())) {
        return '';
    }

    const year = date.getFullYear();
    const month = date.getMonth();
    const dayOfMonth = date.getDate();
    const dayOfWeek = date.getDay();
    const hour = date.getHours();
    const minute = date.getMinutes();
    const second = date.getSeconds();

    // Regex to find all format specifiers, ordered from longest to shortest
    const regex = /YYYY|YY|MMMM|MMM|MM|M|DDDD|DDD|DD|D|d|HH|H|hh|h|mm|m|ss|s|A|a/g;

    return format.replace(regex, (match) => {
        switch (match) {
            case 'YYYY': return year;
            case 'YY': return String(year).slice(-2);
            case 'MMMM': return monthNames[month];
            case 'MMM': return monthNames[month].substring(0, 3);
            case 'MM': return padTo2Digits(month + 1);
            case 'M': return month + 1;
            case 'DDDD': return dayNames[dayOfWeek];
            case 'DDD': return dayNames[dayOfWeek].substring(0, 3);
            case 'DD': return padTo2Digits(dayOfMonth);
            case 'D': return dayOfMonth;
            case 'd': return dayOfWeek;
            case 'HH': return padTo2Digits(hour);
            case 'H': return hour;
            case 'hh': return padTo2Digits(hour % 12 || 12);
            case 'h': return hour % 12 || 12;
            case 'mm': return padTo2Digits(minute);
            case 'm': return minute;
            case 'ss': return padTo2Digits(second);
            case 's': return second;
            case 'A': return hour < 12 ? 'AM' : 'PM';
            case 'a': return hour < 12 ? 'am' : 'pm';
            default: return match;
        }
    });
}

/**
 * Converts a Date object to a MySQL DATETIME format string.
 * @param {Date} [date=new Date()] The date to convert.
 * @returns {string} The formatted date string (YYYY-MM-DD HH:mm:ss).
 */
function toMySqlDateTime(date = new Date()) {
    return formatDate(date, 'YYYY-MM-DD HH:mm:ss');
}

module.exports = { toMySqlDateTime, formatDate };
