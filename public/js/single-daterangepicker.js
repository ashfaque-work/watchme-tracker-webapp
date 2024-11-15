$(function () {
    let startDate = moment().subtract(7, 'days').format('YYYY-MM-DD');
    let endDate = moment().format('YYYY-MM-DD');

    $('input[name="daterange"]').daterangepicker({
        opens: 'center',
        startDate: startDate,
        endDate: endDate,
        maxDate: endDate,
        locale: {
            format: 'YYYY-MM-DD',
        },
    });

    //hide right calender and adjust position
    $('.drp-calendar.right').hide();
    $('.drp-calendar.left').addClass('single');

    // add functional next arrow to single calender
    //select the target node
    let target = document.querySelector('.calendar-table');

    // Create an observer instance
    let observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            // Check if the mutation involves the addition of nodes
            if (mutation.addedNodes.length > 0) {
                // Your logic to add the class and append the span
                let el = $(".prev.available").parent().children().last();
                if (el.hasClass('next available')) {
                    return;
                }
                el.addClass('next available');
                el.append('<span></span>');
            }
        });
    });

    // Configuration of the observer
    let config = {
        childList: true,
        subtree: true
    };

    // Start observing the target node for configured mutations
    observer.observe(target, config);
});
