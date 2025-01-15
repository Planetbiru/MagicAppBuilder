/**
 * Initializes datetime pickers for various input fields (date, time, datetime-local).
 * - Converts the input types to text and applies respective classes.
 * - Initializes datetime pickers with configurable min and max date options.
 */
function initDateTimePicker() {
  let debugDatetimePicker = false;
  
  // Change input type from date to text and add class for date-picker
  $('input[type="date"]').each(function (index, element) {
    let obj = $(this);
    obj.attr('type', 'text');
    obj.addClass('date-picker');
    let html = obj[0].outerHTML;
    let html2 =
      '<div class="input-datetime-wrapper date">\r\n' +
      html + '\r\n' +
      '</div>\r\n';
    obj.replaceWith(html2);
  });

  // Change input type from time to text and add class for time-picker
  $('input[type="time"]').each(function (index, element) {
    let obj = $(this);
    obj.attr('type', 'text');
    obj.addClass('time-picker');
    let html = obj[0].outerHTML;
    let html2 =
      '<div class="input-datetime-wrapper time">\r\n' +
      html + '\r\n' +
      '</div>\r\n';
    obj.replaceWith(html2);
  });

  // Change input type from datetime-local to text and add class for date-time-picker
  $('input[type="datetime-local"]').each(function (index, element) {
    let obj = $(this);
    obj.attr('type', 'text');
    obj.addClass('date-time-picker');
    let html = obj[0].outerHTML;
    let html2 =
      '<div class="input-datetime-wrapper date-time">\r\n' +
      html + '\r\n' +
      '</div>\r\n';
    obj.replaceWith(html2);
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
    let self = thisTable.getAttribute("data-self-name");
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
 * Initializes the "Check All" functionality for tables with checkboxes.
 * - Selects or deselects all checkboxes in a table when the master checkbox is toggled.
 */
function initCheckAll() {
  // Find all tables
  document.querySelectorAll("table").forEach(function(table) {
    // Check if there's an element with the class "check-master" in the table
    const masterCheckbox = table.querySelector(".check-master");
    
    if (masterCheckbox) {
      masterCheckbox.addEventListener("change", function() {
        const checked = masterCheckbox.checked;
        const selector = masterCheckbox.getAttribute('data-selector');
        
        // Find all checkboxes in the table matching the selector
        table.querySelectorAll(".check-slave" + selector).forEach(function(slaveCheckbox) {
          slaveCheckbox.checked = checked;
        });
      });
    }
  });
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
 * Initializes notifications in the dropdown menu.
 * @param {Array} notifications Array of notification objects containing message, time, and link.
 */
function initNotifications(notifications) {
  const notificationMenu = document.querySelector('#notificationMenu');
  notifications.data.forEach(notification => {
      const a = document.createElement('a');
      a.className = 'dropdown-item';
      a.href = notification.link;
      a.innerHTML = `${notification.message} <small class="text-muted">${notification.time}</small>`;
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
}

/**
 * Initializes messages in the dropdown menu.
 * @param {Array} messages Array of message objects containing message, time, and link.
 */
function initMessages(messages) {
  const messageMenu = document.querySelector('#messageMenu');
  messages.data.forEach(message => {
      const a = document.createElement('a');
      a.className = 'dropdown-item';
      a.href = message.link;
      a.innerHTML = `${message.message} <small class="text-muted">${message.time}</small>`;
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
        window.localStorage.setItem('sidebarHidden', hidden ? 'true' : 'false');
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
      window.localStorage.setItem('colorMode', colorMode);
  });
}

document.addEventListener('DOMContentLoaded', () => {
  initPage();
  initNotifications(notifications);
  initMessages(messages);
  initCheckAll();
  initAjaxSupport();
  initDateTimePicker();
  initMultipleSelect();
  initSortTable();
  initSortData();
  initOrderUrl(window.location.search);
});