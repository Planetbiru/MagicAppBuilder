/**
 * Formats a JavaScript Date object into a MySQL-compatible DATETIME string.
 *
 * The output format is: "YYYY-MM-DD HH:mm:ss"
 * Example: "2025-11-27 21:05:42"
 *
 * @param {Date} [date=new Date()] - The Date instance to format. Defaults to the current date and time.
 * @returns {string} A string formatted as a MySQL DATETIME value.
 */
function toMySqlDateTime(date = new Date()) {
    const pad = (n) => String(n).padStart(2, '0');

    return (
        date.getFullYear() + '-' +
        pad(date.getMonth() + 1) + '-' +
        pad(date.getDate()) + ' ' +
        pad(date.getHours()) + ':' +
        pad(date.getMinutes()) + ':' +
        pad(date.getSeconds())
    );
}

module.exports = { toMySqlDateTime };
