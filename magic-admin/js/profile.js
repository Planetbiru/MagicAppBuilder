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

jQuery(function(){
    initDateTimePicker();
})