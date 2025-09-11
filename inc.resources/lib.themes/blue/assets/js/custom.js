
/**
 * Builds a URL with query parameters from a form and its FormData.
 *
 * @param {HTMLFormElement} form - The form element containing the base URL (via `action` attribute).
 * @param {FormData} formData - The FormData object containing key-value pairs to append to the URL.
 * @returns {string} A complete URL with query parameters.
 */
function getFormUrl(form, formData) {
  const params = new URLSearchParams();
  for (const [key, value] of formData.entries()) {
    params.append(key, value);
  }

  const baseUrl = form.getAttribute('action') || window.location.pathname;
  return `${baseUrl}?${params.toString()}`;
}

/**
 * Performs a GET request via AJAX to the specified URL and updates the content of a given section.
 *
 * This function:
 * - Fetches HTML content from the URL.
 * - Replaces the inner HTML of the target section.
 * - Updates the browser's URL using `history.pushState`.
 * - Initializes sortable table behavior if a table is found.
 *
 * @param {string} url - The URL to fetch content from.
 * @param {HTMLElement} section - The DOM element to be updated with the response HTML.
 */
function fetchUrl(url, section) {
  fetch(url, {
    method: 'GET',
    headers: {
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
    .then(response => {
      if (response.status === 401) {
        // Hapus modal lama kalau ada
        const oldForm = document.querySelector('.ajax-login-form');
        if (oldForm) {
          oldForm.remove();
        }

        // Ambil isi response (form login) dan tampilkan modal
        return response.text().then(content => {
          console.log(content);
          const wrapper = document.createElement('div');
          wrapper.classList.add('ajax-login-form');
          wrapper.innerHTML = content;
          document.body.appendChild(wrapper);

          // Cari modal di dalam konten baru
          const loginModal = wrapper.querySelector('.loginModal');
          if (loginModal) {
            $(loginModal).modal({
              backdrop: 'static',
              keyboard: false,
              show: true
            });
          } else {
            console.warn('Login modal element not found in response HTML');
          }

          return null; // hentikan chain
        });
      }
      return response.text();
    })
    .then(html => {
      if (!html) return; // stop jika sudah handle 401

      section.innerHTML = html;

      // Update browser URL tanpa reload
      history.pushState(null, '', url);

      // Re-init sorting
      const table = section.querySelector('table');
      if (table) {
        makeTableSortable(table);
      }

      const tbody = section.querySelector('table tbody');
      if (tbody) {
        sortDataByDrag(tbody);
      }
    })
    .catch(error => {
      console.error('AJAX form error:', error);
    });
}

/**
 * Sends an AJAX login request to the server using Fetch API.
 *
 * This function:
 * - Retrieves username and password from the login modal input fields.
 * - Sends credentials to `login.php` via a POST request with `application/x-www-form-urlencoded` encoding.
 * - Handles the response:
 *    - On success (`200`, `201`, `204`, etc.), the login modal is hidden.
 *    - On unauthorized (`401`), an error message inside the modal is displayed.
 *    - On unexpected status codes, an error message is displayed and a warning is logged.
 * - Handles network or fetch-related errors by showing the error message as well.
 *
 * @function loginAjax
 * @returns {void}
 */
function loginAjax() {
  // Get username and password values from modal input fields
  let username = document.querySelector('.loginModal input[name="username"]').value;
  let password = document.querySelector('.loginModal input[name="password"]').value;

  // Send AJAX request to login.php
  fetch('login.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
      'X-Requested-With': 'XMLHttpRequest'
    },
    body: `username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`
  })
    .then(response => {
      if (response.ok) {
        // ✅ Success: Hide login modal if login is successful
        $('.loginModal').modal('hide');
      } else if (response.status === 401) {
        // ❌ Unauthorized: Show login error message
        const errorBox = document.querySelector('.loginModal .login-error');
        if (errorBox) errorBox.style.display = 'block';
      } else {
        // ⚠️ Unexpected status code: Still show error message and log warning
        const errorBox = document.querySelector('.loginModal .login-error');
        if (errorBox) errorBox.style.display = 'block';
        console.warn('Unexpected login response status:', response.status);
      }
    })
    .catch(error => {
      // 🚨 Network or fetch error: Show error message and log error
      const errorBox = document.querySelector('.loginModal .login-error');
      if (errorBox) errorBox.style.display = 'block';
      console.error('Login AJAX error:', error);
    });
}


/**
 * Initializes datetime pickers for various input fields (date, time, datetime-local).
 * - Converts the input types to text and applies respective classes.
 * - Initializes datetime pickers with configurable min and max date options.
 */
function initDateTimePicker() {
  let debugDatetimePicker = false;

  // Change input type from date to text and add class for date-picker
  $('input[type="date"], input[type="time"], input[type="datetime"], input[type="datetime-local"]').each(function (index, element) {
    let obj = $(this);
    let type = obj.attr('type');
    let map = { 'date': 'date', 'time': 'time', 'datetime': 'date-time', 'datetime-local': 'date-time' };
    let cls = map[type];
    if (obj.attr("data-multiple-input") === undefined) {
      obj.attr('type', 'text');
      obj.addClass(`${cls}-picker`);
      let html = obj[0].outerHTML;
      let html2 =
        `<div class="input-datetime-wrapper ${cls}">
        ${html}
        </div>`;
      obj.replaceWith(html2);
    }
  });

  // Initialize date-picker if there are inputs with the class 'date-picker'
  if ($('.date-picker').length) {
    $('.date-picker').each(function () {
      let minDate = $(this).data('mindate') || false;
      let maxDate = $(this).data('maxdate') || false;
      $(this).datetimepicker({
        minDate: minDate,
        maxDate: maxDate,
        format: 'YYYY-MM-DD',
        debug: debugDatetimePicker
      }).on('dp.change', function (e) {
        $(this).datetimepicker('hide');
      });
    });
  }

  // Initialize time-picker if there are inputs with the class 'time-picker'
  if ($('.time-picker').length) {
    $('.time-picker').datetimepicker({
      format: 'HH:mm:ss',
      debug: debugDatetimePicker
    }).on('dp.change', function (e) {
      $(this).datetimepicker('hide');
    });
  }

  // Initialize date-time-picker if there are inputs with the class 'date-time-picker'
  if ($('.date-time-picker').length) {
    $('.date-time-picker').each(function () {
      let minDate = $(this).data('mindate') || false;
      let maxDate = $(this).data('maxdate') || false;
      $(this).datetimepicker({
        minDate: minDate,
        maxDate: maxDate,
        format: 'YYYY-MM-DD HH:mm:ss',
        useCurrent: 'day',
        debug: debugDatetimePicker
      }).on('dp.change', function (e) {
        $(this).datetimepicker('hide');
      });
    });
  }
}

let lastUrl = null;

/**
 * Initializes table sorting functionality based on query parameters.
 * - Allows sorting columns by clicking on headers.
 * - Modifies the URL with updated sorting parameters.
 */
function initSortTable(selector = "table.table-sort-by-column") {
  const tables = document.querySelectorAll(selector);

  tables.forEach(function (thisTable) {
    makeTableSortable(thisTable);
  });
}


/**
 * Adds sorting functionality to an HTML table header.
 *
 * This function updates the sort links in the table header based on the current URL state.
 *
 * @param {HTMLTableElement} thisTable - The table element to apply sorting functionality to.
 */
function makeTableSortable(thisTable) {
  const originalURL = document.location.toString();

  const head = thisTable.querySelector('thead tr');
  if (head) {
    const cells = head.querySelectorAll("td.order-controll");

    cells.forEach(function (thisCel) {
      let columnName = thisCel.getAttribute("data-col-name");

      if (columnName) {
        let sorter = new UrlSorter(originalURL);

        if (columnName === sorter.getOrderBy()) {
          // Toggle order type between asc and desc if already sorted by this column
          let newOrderType = sorter.getOrderType() === 'desc' ? 'asc' : 'desc';
          sorter.setOrderType(newOrderType);
          thisCel.setAttribute("data-order-method", sorter.getOrderType());
        } else {
          // Set sorting to ascending for a newly selected column
          sorter.setOrderBy(columnName);
          sorter.setOrderType('asc');
        }

        let link = thisCel.querySelector('a');
        if (link) {
          link.href = sorter.buildRelativeUrl();
        }
      }
    });
  }
}


/**
 * Initializes sortable rows for tables with manual sorting.
 *
 * This function selects all <tbody> elements with the class "data-table-manual-sort"
 * and applies drag-and-drop sorting behavior to them using Sortable.js.
 * Each row must include a handle element with class "data-sort-handler".
 * After sorting ends, the row numbers are updated via updateNumber().
 */
function initSortData() {
  document.querySelectorAll("tbody.data-table-manual-sort").forEach(function (dataToSort) {
    sortDataByDrag(dataToSort);
  });
}

/**
 * Applies drag-and-drop sorting behavior to a specific table body.
 *
 * @param {HTMLElement} dataToSort - The <tbody> element to make sortable.
 *
 * This function uses Sortable.js to allow dragging rows using the
 * element with class "data-sort-handler". When sorting ends, the
 * updateNumber() function is called to refresh the order numbers.
 */
function sortDataByDrag(dataToSort) {
  Sortable.create(dataToSort, {
    animation: 150,
    scroll: true,
    handle: ".data-sort-handler",
    onEnd: function () {
      // do nothing
      updateNumber($(dataToSort));
    },
  });
}

/**
 * Initializes the "Check All" functionality for checkboxes.
 * - Selects or deselects all checkboxes when the master checkbox is toggled.
 */
function initCheckAll() {
  document.addEventListener("change", function (e) {
    const masterCheckbox = e.target.closest(".check-master");
    if (!masterCheckbox) return;

    const checked = masterCheckbox.checked;
    const selector = masterCheckbox.dataset.selector;

    // Temukan semua slave checkbox sesuai selector
    document.querySelectorAll(".check-slave" + selector).forEach(function (slaveCheckbox) {
      slaveCheckbox.checked = checked;
    });
  });
}


/**
 * Initializes the multiple input feature for input fields with the "data-multiple-input" attribute.
 * This function:
 * - Sets up a **PicoTagEditor** for each applicable input field.
 * - Configures **date/time pickers** for input fields of type `date`, `time`, `datetime`, and `datetime-local`.
 */
function initMultipleInput() {

  let debugDatetimePicker = false;

  // Select all input elements that have the "data-multiple-input" attribute
  $('input[data-multi-input]').each(function (index, element) {
    let obj = $(this);
    /**
     * Determines if the input field is a date/time-related input type.
     * This is used to apply specific configurations.
     * 
     * @type {boolean}
     */
    let isDateType = obj.is('input[type="date"], input[type="time"], input[type="datetime"], input[type="datetime-local"]');

    /**
     * Configuration options for the PicoTagEditor instance.
     * - `maxHeight`: Limits the maximum height of the tag editor container.
     * - `trimInput`: Trims input values before adding them as tags (for date inputs).
     * - `debug`: Enables or disables debug mode.
     * - `minWidth`: Ensures a minimum width when used with date/time pickers.
     */
    let options = { maxHeight: 120, trimInput: isDateType, clearOnHide: true, debug: false };
    if (isDateType) {
      // Ensure the tag container is wider than the date-time picker.
      options.minWidth = 260;
    }

    /**
     * Initializes PicoTagEditor for the current input element.
     *
     * @param {HTMLElement} elem - The transformed input element.
     * @param {HTMLElement} container - The tag editor container.
     * @param {Object} editor - The PicoTagEditor instance.
     */
    let te = new PicoTagEditor(element, options, function (elem, container, editor) /*NOSONAR*/ {
      if (!isDateType) {
        return; // No need to initialize a date/time picker for non-date inputs.
      }

      let inpuElement = $(elem);

      /**
       * Maps input types to corresponding date/time picker classes.
       */
      let typeMap = { 'date': 'date', 'time': 'time', 'datetime': 'date-time', 'datetime-local': 'date-time' };
      let cls = typeMap[inpuElement.attr('type')] || '';

      // Change the input type to text (required for the date/time picker)
      inpuElement.attr('type', 'text').addClass(`${cls}-picker-multiple pico-tag-edit`);

      // Wrap the input element inside a div for better styling
      inpuElement.wrap(`<div class="input-datetime-wrapper ${cls}"></div>`);

      // Find the new input element inside the container
      inpuElement = $(container).find('.pico-tag-edit');

      // Store a reference to the input element in the editor
      editor.inputElement = inpuElement[0];

      // Retrieve the appropriate DateTimePicker options
      let dpOptions = getDatePickerOptions(inpuElement, debugDatetimePicker);
      if (dpOptions) {
        // Initialize the DateTimePicker with the retrieved options
        inpuElement.datetimepicker(dpOptions)
          .on('dp.change', () => inpuElement.datetimepicker('hide')) // Hide on change
          .on('dp.enter', () => { // Handle "Enter" key event
            let val = inpuElement.val();
            if (val.trim() !== '') {
              editor.addTag(val); // Add entered value as a tag
              inpuElement.val(''); // Clear input field
              if (!editor.settings.debug) {
                editor.waitingForHide(1500);
              }
            }
          });
      }

    });
  });
}

/**
* Retrieves DateTimePicker configuration options based on the input element's class.
* 
* @param {jQuery} inpuElement - The input element wrapped in jQuery.
* @param {boolean} debug - Whether to enable debug mode for the DateTimePicker.
* @returns {object|null} DateTimePicker options or null if the element does not require DateTimePicker.
*/
function getDatePickerOptions(inpuElement, debug) {
  let options = {};

  if (inpuElement.hasClass('date-picker-multiple')) {
    // Options for date picker
    options = {
      minDate: inpuElement.data('mindate') || false,
      maxDate: inpuElement.data('maxdate') || false,
      format: 'YYYY-MM-DD',
      debug
    };
  } else if (inpuElement.hasClass('time-picker-multiple')) {
    // Options for time picker
    options = { format: 'HH:mm:ss', debug };
  } else if (inpuElement.hasClass('date-time-picker-multiple')) {
    // Options for date-time picker
    options = {
      minDate: inpuElement.data('mindate') || false,
      maxDate: inpuElement.data('maxdate') || false,
      format: 'YYYY-MM-DD HH:mm:ss',
      useCurrent: 'day',
      debug
    };
  }

  // Return options if valid, otherwise return null
  return Object.keys(options).length ? options : null;
}

/**
 * Displays a non-blocking Bootstrap modal confirmation dialog.
 *
 * This function shows a modal with a custom title, message, and button labels.
 * It returns a Promise that resolves to:
 * - `true` if the user clicks OK
 * - `false` if the user clicks Cancel
 *
 * Requires:
 * - An HTML modal element with ID `customConfirmModal`
 * - Elements inside the modal:
 *   - `#customConfirmTitle`: for the modal title
 *   - `#customConfirmMessage`: for the confirmation message
 *   - `#customConfirmOk`: the OK button
 *   - `#customConfirmCancel`: the Cancel button
 * - Bootstrap's modal plugin (jQuery-based)
 *
 * @param {Object} options - Configuration options for the dialog.
 * @param {string} [options.title="Confirmation"] - The dialog title.
 * @param {string} [options.message="Are you sure?"] - The confirmation message.
 * @param {string} [options.okText="OK"] - Label for the OK button.
 * @param {string} [options.cancelText="Cancel"] - Label for the Cancel button.
 * @returns {Promise<boolean>} Resolves to true if confirmed, false if cancelled.
 */
function customConfirm({
  title = "Confirmation",
  message = "Are you sure?",
  okText = "OK",
  cancelText = "Cancel"
}) {
  return new Promise((resolve) => {
    document.getElementById('customConfirmTitle').innerText = title;
    document.getElementById('customConfirmMessage').innerText = message;
    document.getElementById('customConfirmOk').innerText = okText;
    document.getElementById('customConfirmCancel').innerText = cancelText;

    const okBtn = document.getElementById('customConfirmOk');
    const cancelBtn = document.getElementById('customConfirmCancel');

    const cleanup = () => {
      okBtn.removeEventListener('click', onOk);
      cancelBtn.removeEventListener('click', onCancel);
    };

    const onOk = () => {
      cleanup();
      $('#customConfirmModal').modal('hide');
      resolve(true);
    };

    const onCancel = () => {
      cleanup();
      $('#customConfirmModal').modal('hide');
      resolve(false);
    };

    okBtn.addEventListener('click', onOk);
    cancelBtn.addEventListener('click', onCancel);

    $('#customConfirmModal').modal('show');
  });
}

/**
 * Initializes AJAX support for form submissions, pagination, and confirmation dialogs.
 *
 * Features:
 * - Automatically intercepts <button type="submit"> inside forms.
 *   - If the button has `data-confirmation="true"`, a confirmation dialog will appear.
 *   - If the surrounding `.data-section` has `data-ajax-support="true"`, the form is submitted via AJAX.
 *   - Otherwise, it falls back to standard form submission.
 * - Adds support for AJAX pagination in `.pagination-number` inside `.data-section[data-ajax-support="true"]`.
 * - Uses `history.pushState()` to update the URL after AJAX pagination.
 * - Handles browser Back/Forward navigation via `popstate` and reloads content using AJAX.
 * - Dynamically injects the Bootstrap modal structure for confirmations if not already present.
 *
 * Requirements:
 * - Bootstrap modal (jQuery-based)
 * - `customConfirm()` function must be defined and handle modal display
 * - `sortDataByDrag()` must re-bind drag-sort functionality after AJAX reloads
 */
function initAjaxSupport() {
  // Inject the Bootstrap modal for confirmation if not present
  let modal = document.createElement('div');
  modal.innerHTML = `
  <div class="modal fade" id="customConfirmModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="customConfirmTitle">Confirm</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body" id="customConfirmMessage">Are you sure?</div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="customConfirmOk">OK</button>
          <button type="button" class="btn btn-secondary" id="customConfirmCancel">Cancel</button>
        </div>
      </div>
    </div>
  </div>
  `;
  document.querySelector('body').appendChild(modal);

  // Intercept all submit buttons
  document.addEventListener('click', function (e) {
    const dataSection = e.target.closest('.data-section');
    if (!dataSection) return;

    const target = e.target.closest('button[type="submit"]');
    if (!target) return;

    const form = target.closest('form');
    if (!form) return;

    const section = form.closest('.data-section');
    const isAjax = section && section.dataset.ajaxSupport === "true";
    const needsConfirmation = target.dataset.confirmation === 'true';

    // Encapsulated form submission logic (AJAX or standard)
    const submitForm = () => {
      if (isAjax) {
        const formData = new FormData(form);

        // Append clicked button name/value to FormData
        if (target.name) {
          formData.append(target.name, target.value);
        }

        fetch(window.location.href, {
          method: 'POST',
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: formData
        })
          .then(response => {
            if (response.status === 401) {
              // Hapus modal lama kalau ada
              const oldForm = document.querySelector('.ajax-login-form');
              if (oldForm) {
                oldForm.remove();
              }
              // Ambil isi response (form login) dan tampilkan modal
              return response.text().then(content => {
                const wrapper = document.createElement('div');
                wrapper.classList.add('ajax-login-form');
                wrapper.innerHTML = content;
                document.body.appendChild(wrapper);
                // Cari modal di dalam konten baru
                const loginModal = wrapper.querySelector('.loginModal');
                if (loginModal) {
                  $(loginModal).modal({
                    backdrop: 'static',
                    keyboard: false,
                    show: true
                  });
                } else {
                  console.warn('Login modal element not found in response HTML');
                }
                return null;
              });
            }
            return response.text();
          })
          .then(html => {
            if (!html) return;
            section.innerHTML = html;
            const table = section.querySelector('table');
            if (table) {
              makeTableSortable(table);
            }
            const tbody = section.querySelector('table tbody');
            if (tbody) {
              sortDataByDrag(tbody); // reinitialize drag-sort
            }
          })
          .catch(error => {
            console.error('AJAX form error:', error);
          });
      } else {
        // Submit form using hidden button
        const hiddenSubmit = document.createElement('input');
        hiddenSubmit.type = 'hidden';
        hiddenSubmit.classList.add('hidden-user-action');
        hiddenSubmit.style.display = 'none';
        hiddenSubmit.name = target.name;
        hiddenSubmit.value = target.value;
        form.appendChild(hiddenSubmit);
        form.submit();
        form.removeChild(hiddenSubmit);
      }
    };

    if (needsConfirmation) {
      e.preventDefault(); // Wait for confirmation

      // Read confirmation dialog attributes
      const title = target.dataset.onclikTitle || "Confirmation";
      const message = target.dataset.onclikMessage || "Are you sure?";
      const okText = target.dataset.okButtonLabel || "OK";
      const cancelText = target.dataset.cancelButtonLabel || "Cancel";

      customConfirm({ title, message, okText, cancelText }).then(result => {
        if (result) submitForm();
      });
    } else {
      e.preventDefault();
      submitForm();
    }
  });

  // Handle AJAX pagination
  document.addEventListener('click', function (e) {
    const dataSection = e.target.closest('.data-section');
    if (!dataSection) return;
    let link = e.target.closest('.pagination-number .page-selector a');
    if (!link) {
      link = e.target.closest('.order-controll a');
    }
    if (!link) return;

    const section = link.closest('.data-section');
    if (!section || section.dataset.ajaxSupport !== "true") return;

    e.preventDefault(); // Prevent default page reload

    const url = link.getAttribute('href');
    lastUrl = url;
    fetchUrl(url, section);
  });

  // Intercept filter form submission
  const getForm = document.querySelector('.filter-section form');
  if (getForm) {
    getForm.addEventListener('submit', function (e) {
      const page = e.target.closest('.page.page-list');
      const section = page ? page.querySelector('.data-section[data-ajax-support="true"]') : null;

      if (!section) return;
      const target = e.target;
      const form = target.closest('form');
      if (!form) return;

      const isAjax = section && section.dataset.ajaxSupport === "true";
      const needsConfirmation = target.dataset.confirmation === 'true';

      const submitForm = () => {

        if (isAjax) {
          const formData = new FormData(form);

          if (target.name && typeof target.value === 'string') {
            formData.append(target.name, target.value);
          }

          const params = new URLSearchParams();
          for (const [key, value] of formData.entries()) {
            params.append(key, value);
          }

          const url = `${window.location.pathname}?${params.toString()}`;

          fetchUrl(url, section);

        } else {
          if (target.name && !form.querySelector(`input[name="${target.name}"]`)) {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = target.name;
            hiddenInput.value = target.value;
            form.appendChild(hiddenInput);

          }

          form.submit();
        }
      };

      if (needsConfirmation) {
        e.preventDefault();

        const title = target.dataset.onclikTitle || "Confirmation";
        const message = target.dataset.onclikMessage || "Are you sure?";
        const okText = target.dataset.okButtonLabel || "OK";
        const cancelText = target.dataset.cancelButtonLabel || "Cancel";

        customConfirm({ title, message, okText, cancelText }).then(result => {
          if (result) submitForm();
        });
      } else {
        e.preventDefault();
        submitForm();
      }
    });
  }

  const page = document.querySelector('.page.page-list');
  const section = page ? page.querySelector('.data-section[data-ajax-support="true"]') : null;
  if (section) {
    const btn1 = document.querySelector('#show_require_approval_data');
    if (btn1) {
      btn1.addEventListener('click', function (e) {
        e.preventDefault();
        const formData = new FormData(e.target.form);
        formData.append(e.target.name, e.target.value);
        let url = getFormUrl(e.target.form, formData);
        fetchUrl(url, section);
      });
    }

    const btn2 = document.querySelector('#export_data');
    if (btn2) {
      btn2.addEventListener('click', function (e) {
        e.preventDefault();
        const formData = new FormData(e.target.form);
        formData.append(e.target.name, e.target.value);
        window.location = getFormUrl(e.target.form, formData);
      });
    }
  }

  // Handle back/forward navigation with AJAX
  window.addEventListener('popstate', function () {
    const section = document.querySelector('.data-section[data-ajax-support="true"]');
    if (!section) return;

    const url = window.location.href;

    fetchUrl(url, section);
  });
}


/**
 * Initializes multiple select dropdowns using the MultiSelect library.
 * - Allows for selecting multiple options in a dropdown.
 */
function initMultipleSelect() {
  document.querySelectorAll('select[multiple]').forEach(select => new MultiSelect(select));
}

/**
 * Initializes hidden input fields for sorting parameters in the URL.
 * - Adds hidden inputs to the form to reflect the current sort order and column.
 * @param {string} queryString The query string from the current URL.
 */
function initOrderUrl(queryString) {
  const urlParams = new URLSearchParams(queryString);
  const orderby = urlParams.get('orderby');
  const ordertype = urlParams.get('ordertype');

  if (typeof orderby != 'undefined') {
    let orderbyInput = $('<input />');
    orderbyInput.attr('type', 'hidden');
    orderbyInput.attr('name', 'orderby');
    orderbyInput.attr('value', orderby);

    if ($('form.filter-form [name="orderby"]').length) {
      $('form.filter-form [name="orderby"]').remove();
    }
    $('form.filter-form').append(orderbyInput);
  }

  if (typeof ordertype != 'undefined') {
    let ordertypeInput = $('<input />');
    ordertypeInput.attr('type', 'hidden');
    ordertypeInput.attr('name', 'ordertype');
    ordertypeInput.attr('value', ordertype);
    if ($('form.filter-form [name="ordertype"]').length) {
      $('form.filter-form [name="ordertype"]').remove();
    }
    $('form.filter-form').append(ordertypeInput);
  }
}

/**
 * Updates the order numbers for sorted rows in a form.
 * - Adds new order values as hidden inputs for each row.
 * @param {object} dataToSort The table or section being sorted.
 */
function updateNumber(dataToSort) {
  let frm = dataToSort.closest("form");
  if (frm.find("span.new-sort-order").length) {
    frm.find("span.new-sort-order").remove();
  }
  let span = $("<span />");
  span.addClass("new-sort-order");
  frm.append(span);
  let offset = parseInt(dataToSort.attr("data-offset"));
  let i = 0;
  dataToSort.find("tr").each(function (e) {
    let tr = $(this);
    i++;
    let order = offset + i;
    tr.find(".data-number").text(order);
    let pk = tr.attr("data-primary-key");
    let orderInput = $("<input />");
    orderInput.attr({
      type: "hidden",
      name: "new_order[]",
      value: JSON.stringify({ primary_key: pk, sort_order: order }),
    });
    span.append(orderInput);
  });
  dataToSort
    .closest("form")
    .find('button[name="user_action"][value="sort_order"]')
    .removeAttr("disabled");
}

/**
 * Placeholder for the saveOrder function.
 * This function can be implemented for saving the sorted order to the server.
 */
function saveOrder() {
  // Do nothing
}

/**
 * Splits a string by a delimiter and returns an array, keeping the remaining part as a tail.
 * @param {string} str The string to split.
 * @param {string} delimiter The delimiter to split by.
 * @param {number} count The number of parts to return before the tail.
 * @returns {Array} The array of split parts, with the remaining part as a tail.
 */
function splitWithTail(str, delimiter, count) {
  const parts = str.split(delimiter);
  const tail = parts.slice(count).join(delimiter);
  const result = parts.slice(0, count);
  result.push(tail);
  return result;
}


/**
 * Initializes and populates the notifications dropdown menu.
 *
 * This function appends a list of notification items to the specified dropdown menu element,
 * sets a data-badge attribute on the parent navigation item based on the number of notifications,
 * and adds a final link with a custom caption (e.g., "View All Notifications").
 *
 * @param {string} selector - CSS selector targeting the dropdown menu container.
 * @param {Object} notifications - Object containing notification data.
 * @param {Array} notifications.data - Array of notification objects.
 * @param {number} notifications.totalData - Total number of notifications.
 * @param {string} link - URL for the final dropdown item (e.g., "View All").
 * @param {string} caption - Text to display for the final dropdown item.
 */
function initNotifications(selector, notifications, link, caption) {
  const notificationMenu = document.querySelector(selector);
  if (typeof notifications.data != 'undefined') {
    notifications.data.forEach(notification => {
      const a = document.createElement('a');
      a.className = 'dropdown-item';
      a.href = notification.link;
      a.innerHTML = `${notification.title} <br><small class="text-muted">${notification.time}</small>`;
      a.dataset.id = notification.id; // Adding ID to the notification item
      notificationMenu.appendChild(a);
    });
    let badge = '';
    if (notifications.totalData > 99) {
      badge = '99+';
    }
    else if (notifications.totalData > 0 && notifications.totalData <= 99) {
      badge = notifications.totalData;
    }
    notificationMenu.closest('li.nav-item').setAttribute('data-badge', badge);

    if (notifications.data.length > 0) {
      let div = document.createElement('div');
      div.classList.add('menu-separator');
      notificationMenu.appendChild(div);
    }

  }
  else {
    notificationMenu.closest('li.nav-item').setAttribute('data-badge', '');
  }

  let a = document.createElement('a');
  a.className = 'dropdown-item';
  a.href = link;
  a.innerHTML = caption;
  notificationMenu.appendChild(a);
}

/**
 * Initializes and populates the messages dropdown menu.
 *
 * This function appends a list of message items to the specified dropdown menu element,
 * sets a data-badge attribute on the parent navigation item based on the number of messages,
 * and adds a final link with a custom caption (e.g., "View All Messages").
 *
 * @param {string} selector - CSS selector targeting the dropdown menu container.
 * @param {Object} messages - Object containing message data.
 * @param {Array} messages.data - Array of message objects.
 * @param {number} messages.totalData - Total number of messages.
 * @param {string} link - URL for the final dropdown item (e.g., "View All").
 * @param {string} caption - Text to display for the final dropdown item.
 */
function initMessages(selector, messages, link, caption) {
  const messageMenu = document.querySelector(selector);
  if (typeof messages.data != 'undefined') {
    messages.data.forEach(message => {
      let a = document.createElement('a');
      a.className = 'dropdown-item';
      a.href = message.link;
      a.innerHTML = `${message.title} <br><small class="text-muted">${message.time}</small>`;
      a.dataset.id = message.id; // Adding ID to the message item
      messageMenu.appendChild(a);
    });
    let badge = '';
    if (messages.totalData > 99) {
      badge = '99+';
    }
    else if (messages.totalData > 0 && messages.totalData <= 99) {
      badge = messages.totalData;
    }
    messageMenu.closest('li.nav-item').setAttribute('data-badge', badge);

    if (messages.data.length > 0) {
      let div = document.createElement('div');
      div.classList.add('menu-separator');
      messageMenu.appendChild(div);
    }

  }
  else {
    messageMenu.closest('li.nav-item').setAttribute('data-badge', '');
  }

  let a = document.createElement('a');
  a.className = 'dropdown-item';
  a.href = link;
  a.innerHTML = caption;
  messageMenu.appendChild(a);
}

/**
 * Initializes the page by setting up event listeners for sidebar toggle, dark/light mode toggle, 
 * and initializing other functions like notifications, messages, and form actions.
 */
function initPage() {
  // Toggle sidebar visibility
  // Select all elements with the class .toggle-sidebar
  document.querySelectorAll('.toggle-sidebar').forEach(toggleButton => {
    toggleButton.addEventListener('click', () => {
      let width = document.body.clientWidth;
      if (width >= 992) {
        document.body.classList.toggle('sidebar-hidden'); // Hide or show the sidebar for large screens
      } else {
        document.body.classList.toggle('sidebar-show'); // Hide or show the sidebar for small screens
      }
      let hidden = document.body.classList.contains('sidebar-hidden');
      window.localStorage.setItem('MagicAppBuilder.sidebarHidden', hidden ? 'true' : 'false');
    });
  });

  // Toggle between light and dark modes
  document.querySelector('.toggle-mode').addEventListener('click', () => {
    document.body.classList.toggle('dark-mode'); // Switch to dark mode
    document.body.classList.toggle('light-mode'); // Switch to light mode
    let colorMode = '';
    if (document.body.classList.contains('dark-mode')) {
      colorMode = 'dark-mode';
      document.querySelector('meta[name="theme-color"]').setAttribute('content', themeDark);
    }
    else {
      colorMode = 'light-mode';
      document.querySelector('meta[name="theme-color"]').setAttribute('content', themeLight);
    }
    window.localStorage.setItem('MagicAppBuilder.colorMode', colorMode);
  });
}

/**
 * Convert a camelCase string to snake_case.
 *
 * Useful for matching JavaScript variable names to backend naming conventions.
 *
 * @param {string} str - The camelCase string.
 * @returns {string} The converted snake_case string.
 */
function camelToSnake(str) {
  return str.replace(/([a-z0-9]|(?=[A-Z]))([A-Z])/g, '$1_$2').toLowerCase();
}

/**
 * Restore form fields with provided data and highlight the error field.
 *
 * This function delays execution until the DOM is ready, then delegates to `doRestoreFormData`.
 *
 * @param {Object} formData - An object where each key is the camelCase field name and the value is the field value.
 * @param {string} errorField - The camelCase name of the field that contains an error (will be converted to snake_case).
 * @param {string} formSelector - A CSS selector string used to identify the form element.
 */
function restoreFormData(formData, errorField, formSelector) {
  document.addEventListener('DOMContentLoaded', () => {
    doRestoreFormData(formData, errorField, formSelector);
  });
}

/**
 * Populate form fields with data and visually indicate which field contains an error.
 *
 * This function assumes the form uses snake_case for input names and handles various input types
 * including checkboxes, radios, selects, and standard text inputs.
 *
 * @param {Object} formData - Form data where keys are camelCase field names.
 * @param {string} errorField - The camelCase name of the field to highlight as invalid.
 * @param {string} formSelector - CSS selector pointing to the target form.
 */
function doRestoreFormData(formData, errorField, formSelector) {
  const form = document.querySelector(formSelector);
  if (!form) {
    console.warn("Form not found:", formSelector);
    return;
  }

  errorField = camelToSnake(errorField);

  for (let [camelName, value] of Object.entries(formData)) {
    const name = camelToSnake(camelName);
    const elements = form.querySelectorAll(`[name="${name}"]`);

    elements.forEach(element => {
      const type = element.type;

      if (type === 'checkbox') {
        element.checked = Array.isArray(value)
          ? value.includes(element.value)
          : Boolean(value);
      } else if (type === 'radio') {
        element.checked = element.value === value;
      } else if (element.tagName === 'SELECT') {
        Array.from(element.options).forEach(option => {
          option.selected = Array.isArray(value)
            ? value.includes(option.value)
            : option.value === value;
        });
      } else {
        element.value = value;
      }

      // Highlight error field
      if (name === errorField) {
        element.classList.add('is-invalid');
        element.scrollIntoView({ behavior: 'smooth', block: 'center' });
        element.focus();
      } else {
        element.classList.remove('is-invalid');
      }
    });
  }
}

document.addEventListener('DOMContentLoaded', () => {
  // Initialize general page setup (e.g., tabs, tooltips, etc.)
  initPage();

  // Enable "check all / uncheck all" checkbox functionality
  initCheckAll();

  // Enable AJAX support for form submissions, pagination, and back/forward navigation
  initAjaxSupport();

  // Activate support for dynamic/multiple input fields
  initMultipleInput();

  // Initialize date/time picker widgets (e.g., flatpickr or bootstrap-datepicker)
  initDateTimePicker();

  // Initialize multiple-select dropdowns (e.g., select2 or choices.js)
  initMultipleSelect();

  // Enable clickable table headers for sorting via query parameters
  initSortTable();

  // Enable drag-and-drop sorting for manually sortable tables
  initSortData();

  // Apply sorting state (highlighting, arrows, etc.) based on URL query params
  initOrderUrl(window.location.search);
});
