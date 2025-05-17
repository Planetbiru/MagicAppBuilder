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
    let map = {'date':'date', 'time':'time', 'datetime':'date-time', 'datetime-local':'date-time'};
    let cls = map[type];
    if(obj.attr("data-multiple-input") === undefined)
    {
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
    $('.date-picker').each(function() {
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
    $('.date-time-picker').each(function() {
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


/**
 * Initializes table sorting functionality based on query parameters.
 * - Allows sorting columns by clicking on headers.
 * - Modifies the URL with updated sorting parameters.
 */
function initSortTable() {
  const tables = document.querySelectorAll("table.table-sort-by-column");
  
  tables.forEach(function (thisTable) {
    let originalURL = document.location.toString();
    let arr0 = originalURL.split("#");
    originalURL = arr0[0];
    let arr1 = originalURL.split("?");
    originalURL = arr1[0];
    let args = arr1[1] || "";
    let argArray = args.split("&");
    let queryObject = {};

    argArray.forEach(function(param) {
      let arr2 = param.split("=");
      if (arr2[0] !== "") {
        queryObject[arr2[0]] = arr2[1];
      }
    });

    let currentOrderMethod = queryObject.ordertype || "";
    let lastColumn = queryObject.orderby || "";

    const cells = thisTable.querySelectorAll("td.order-controll");

    cells.forEach(function (thisCel) {
      let columnName = thisCel.getAttribute("data-col-name");

      if (lastColumn === columnName) {
        if (currentOrderMethod === "asc") {
          queryObject.ordertype = "desc";
          thisCel.setAttribute("data-order-method", "asc");
        } else {
          queryObject.ordertype = "asc";
          thisCel.setAttribute("data-order-method", "desc");
        }
      } else {
        queryObject.ordertype = "asc";
      }

      queryObject.orderby = columnName;

      let arr3 = [];
      for (let key in queryObject) {
        if (queryObject.hasOwnProperty(key)) {
          arr3.push(key + "=" + queryObject[key]);
        }
      }

      let args3 = arr3.join("&");
      let finalURL = originalURL + "?" + args3;
      let link = thisCel.querySelector("a");
      
      if (link) {
        link.setAttribute("href", finalURL);
      }
    });
  });
}

/**
 * Initializes sortable rows for tables with manual sorting.
 * - Allows drag-and-drop sorting of rows.
 */
function initSortData()
{
  document.querySelectorAll("tbody.data-table-manual-sort").forEach(function(dataToSort) {
    Sortable.create(dataToSort, {
      animation: 150,
      scroll: true,
      handle: ".data-sort-handler",
      onEnd: function () {
        // do nothing
        updateNumber($(dataToSort));
      },
    });
  });
}

/**
 * Initializes the "Check All" functionality for checkboxes.
 * - Selects or deselects all checkboxes when the master checkbox is toggled.
 */
function initCheckAll() {
  const masterCheckbox = document.querySelectorAll(".check-master");  
  masterCheckbox.forEach(function(masterCheckbox) {
    
    if (masterCheckbox) {
      masterCheckbox.addEventListener("change", function() {
        const checked = masterCheckbox.checked;
        const selector = masterCheckbox.dataset.selector;
        
        // Find all checkboxes matching the selector
        document.querySelectorAll(".check-slave" + selector).forEach(function(slaveCheckbox) {
          slaveCheckbox.checked = checked;
        });
      });
    }
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
 * Initializes AJAX support for form submissions.
 * - Attaches a confirmation prompt for delete actions if the element has data-ajax-support="true".
 */
function initAjaxSupport() {
  // Check if there are elements with the attribute data-ajax-support="true"
  if (document.querySelectorAll('[data-ajax-support="true"]').length === 0) {
    // Attach a click event listener to the submit buttons with specific attributes
    document.addEventListener("click", function(e2) {
      const target = e2.target;

      if (target && target.type === "submit" && target.name === "user_action" && target.value === "delete") {
        // Check for confirmation before proceeding with the action
        const confirmationMessage = target.getAttribute("data-onclik-message");
        if (!confirm(confirmationMessage)) {
          e2.preventDefault();
          e2.stopPropagation();
        }
      }
    });
  }
}

/**
 * Initializes multiple select dropdowns using the MultiSelect library.
 * - Allows for selecting multiple options in a dropdown.
 */
function initMultipleSelect()
{
  document.querySelectorAll('select[multiple]').forEach(select => new MultiSelect(select));
}

/**
 * Initializes hidden input fields for sorting parameters in the URL.
 * - Adds hidden inputs to the form to reflect the current sort order and column.
 * @param {string} queryString The query string from the current URL.
 */
function initOrderUrl(queryString)
{
  const urlParams = new URLSearchParams(queryString);
  const orderby = urlParams.get('orderby');
  const ordertype = urlParams.get('ordertype');

  if(typeof orderby != 'undefined')
  {
    let orderbyInput = $('<input />');
    orderbyInput.attr('type', 'hidden');
    orderbyInput.attr('name', 'orderby');
    orderbyInput.attr('value', orderby);

    if($('form.filter-form [name="orderby"]').length)
    {
      $('form.filter-form [name="orderby"]').remove();
    }
    $('form.filter-form').append(orderbyInput);
  }

  if(typeof ordertype != 'undefined')
  {
    let ordertypeInput = $('<input />');
    ordertypeInput.attr('type', 'hidden');
    ordertypeInput.attr('name', 'ordertype');
    ordertypeInput.attr('value', ordertype);
    if($('form.filter-form [name="ordertype"]').length)
    {
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
  if(typeof notifications.data != 'undefined')
  {
    notifications.data.forEach(notification => {
      const a = document.createElement('a');
      a.className = 'dropdown-item';
      a.href = notification.link;
      a.innerHTML = `${notification.title} <br><small class="text-muted">${notification.time}</small>`;
      a.dataset.id = notification.id; // Adding ID to the notification item
      notificationMenu.appendChild(a);
    });
    let badge = '';
    if(notifications.totalData > 99)
    {
      badge = '99+';
    }
    else if(notifications.totalData > 0 && notifications.totalData <= 99)
    {
      badge = notifications.totalData;
    }
    notificationMenu.closest('li.nav-item').setAttribute('data-badge', badge);

    if(notifications.data.length > 0)
    {
      let div = document.createElement('div');
      div.classList.add('menu-separator');
      notificationMenu.appendChild(div);
    }
    
  }
  else
  {
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
  if(typeof messages.data != 'undefined')
  {
    messages.data.forEach(message => {
      let a = document.createElement('a');
      a.className = 'dropdown-item';
      a.href = message.link;
      a.innerHTML = `${message.title} <br><small class="text-muted">${message.time}</small>`;
      a.dataset.id = message.id; // Adding ID to the message item
      messageMenu.appendChild(a);
    });
    let badge = '';
    if(messages.totalData > 99)
    {
      badge = '99+';
    }
    else if(messages.totalData > 0 && messages.totalData <= 99)
    {
      badge = messages.totalData;
    }
    messageMenu.closest('li.nav-item').setAttribute('data-badge', badge);

    if(messages.data.length > 0)
    {
      let div = document.createElement('div');
      div.classList.add('menu-separator');
      messageMenu.appendChild(div);
    }

  }
  else
  {
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
function initPage()
{
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
      if(document.body.classList.contains('dark-mode'))
      {
        colorMode = 'dark-mode';
      }
      else
      {
        colorMode = 'light-mode';
      }
      window.localStorage.setItem('MagicAppBuilder.colorMode', colorMode);
  });
}

document.addEventListener('DOMContentLoaded', () => {
  initPage();
  initCheckAll();
  initAjaxSupport();
  initMultipleInput();
  initDateTimePicker();
  initMultipleSelect();
  initSortTable();
  initSortData();
  initOrderUrl(window.location.search);
});