/**
 * DBFParser - A class for reading and parsing .DBF (dBase) files in the browser.
 * Supports common field types: Character (C), Numeric (N), Float (F), Date (D), and Logical (L).
 */
class DBFParser {
  /**
   * @param {ArrayBuffer} arrayBuffer - Binary buffer of the DBF file.
   */
  constructor(arrayBuffer) {
    /** @type {DataView} Binary view of the buffer */
    this.view = new DataView(arrayBuffer);

    /** @type {ArrayBuffer} Raw binary buffer */
    this.buffer = arrayBuffer;

    /** @type {Array<{ name: string, type: string, length: number, decimalCount: number }>} List of field definitions */
    this.fields = [];

    /** @type {Array<Object>} Parsed records */
    this.records = [];
  }

  /**
   * Parses the DBF file: header, fields, and records.
   * @returns {Array<Object>} Array of parsed record objects.
   */
  parse() {
    this._parseHeader();
    this._parseFields();
    this._parseRecords();
    return this.records;
  }

  /**
   * Parses the DBF file header to extract the number of records,
   * header length, and length of each record.
   * @private
   */
  _parseHeader() {
    this.recordCount = this.view.getUint32(4, true);
    this.headerLength = this.view.getUint16(8, true);
    this.recordLength = this.view.getUint16(10, true);
  }

  /**
   * Parses the field (column) definitions from the DBF file.
   * @private
   */
  _parseFields() {
    let offset = 32;
    while (this.view.getUint8(offset) !== 0x0D) {
      const nameBytes = new Uint8Array(this.buffer, offset, 11);
      const name = new TextDecoder().decode(nameBytes).replace(/\0/g, '').trim();
      const type = String.fromCharCode(this.view.getUint8(offset + 11));
      const length = this.view.getUint8(offset + 16);
      const decimalCount = this.view.getUint8(offset + 17);

      this.fields.push({ name, type, length, decimalCount });
      offset += 32;
    }
  }

  /**
   * Parses all data records based on the field definitions.
   * Skips deleted records (marked with '*').
   * @private
   */
  _parseRecords() {
    const decoder = new TextDecoder();
    let offset = this.headerLength;

    for (let i = 0; i < this.recordCount; i++) {
      const deletedFlag = String.fromCharCode(this.view.getUint8(offset));
      if (deletedFlag !== '*') {
        const record = {};
        let recordOffset = offset + 1;

        for (const field of this.fields) {
          const raw = new Uint8Array(this.buffer, recordOffset, field.length);
          const rawStr = decoder.decode(raw).trim();
          record[field.name] = this._parseValue(rawStr, field);
          recordOffset += field.length;
        }

        this.records.push(record);
      }
      offset += this.recordLength;
    }
  }

  /**
   * Converts raw field string values into appropriate JavaScript data types.
   *
   * @param {string} value - Raw value string from the DBF field.
   * @param {{ type: string }} field - Field metadata including its type.
   * @returns {any} The parsed value (string, number, Date, boolean, or null).
   * @private
   */
  _parseValue(value, field) {
    switch (field.type) {
      case 'N':
      case 'F':
        return value ? parseFloat(value) : null;
      case 'D':
        return value.length === 8
          ? new Date(`${value.slice(0, 4)}-${value.slice(4, 6)}-${value.slice(6, 8)}`)
          : null;
      case 'L':
        return /^[YyTt]$/.test(value);
      case 'C':
      default:
        return value;
    }
  }
}
