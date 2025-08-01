/**
 * ODSParser – JavaScript class to read and parse .ODS OpenDocument Spreadsheet in the browser.
 */
class ODSParser {
  /**
   * @param {ArrayBuffer} arrayBuffer - Binary file data.
   */
  constructor(arrayBuffer) {
    /** @type {ArrayBuffer} */
    this.buffer = arrayBuffer;
    /** @type {Object} workbook object */
    this.workbook = null;
  }

  /**
   * Parse the ODS buffer and extract data from the first sheet.
   * @returns {{ headers: string[], data: any[] }} Parsed header row and row data.
   */
  parse() {
    const workbook = XLSX.read(new Uint8Array(this.buffer), { type: 'array' });
    this.workbook = workbook;
    const firstSheetName = workbook.SheetNames[0];
    const worksheet = workbook.Sheets[firstSheetName];
    // Get all data as array of arrays
    const raw = XLSX.utils.sheet_to_json(worksheet, { header: 1, defval: "" });
    if (raw.length === 0) {
      return { headers: [], data: [] };
    }
    const headers = raw[0].map(cell => String(cell));
    const data = raw.slice(1).map(row => {
      const obj = {};
      headers.forEach((h, idx) => {
        obj[h] = row[idx] != null ? row[idx] : "";
      });
      return obj;
    });
    return { headers, data };
  }
}
