if (!String.prototype.format) {
    String.prototype.format = function() {
        var args = arguments;
        return this.replace(/{(\d+)}/g, function(match, number) {
            return typeof args[number] != 'undefined'
                ? args[number]
                : match
                ;
        });
    };
}
(function() {

    const $bookingProductForm = jQuery(document).find('.jet-booking-form');
    if (!$bookingProductForm.length) {
        return;
    }
    const $bookingButton = $bookingProductForm.find('.single_add_to_cart_button');
    const $bookingQty = $bookingProductForm.find('.quantity input.qty');
    window.JetPlugins.hooks.addFilter('jet-booking.calendar.config','jet-booking',config=>{ return{...config,stickyMonths:true}; })
    jQuery(document).on('jet-booking/init-calendar', () => {
        jQuery(document).unbind('change.JetBookingProduct');
        jQuery(document).unbind('click.JetWooBuilder');
        jQuery(document).on('change.JetBookingProduct', function(event) {
            let target = event.target;
            if (target?.id !== 'jet_abaf_field' && document.querySelector('#jet_abaf_field').value.length < 1)
                return;
            if (target.id !== 'jet_abaf_field')
                target = $bookingProductForm.find('#jet_abaf_field')[0];
            $bookingButton.removeClass('disabled').prop('disabled', false);
            jQuery.ajax({
                url: window.jetWooBuilderData.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'jet_booking_product_set_total_price',
                    total: JetBooking.getApartmentPrice(jQuery(target)) * $bookingQty.val()
                },
            }).done(function(response) {
                jQuery('.jet-abaf-product-total').html(response.data.html);
            }).fail(function(_, _2, errorThrown) {
                alert(errorThrown);
            });
        });
        const showQuantityError = (message) => {
            if(document.querySelector('.jet-abaf-product-quantity-error'))
                document.querySelector('.jet-abaf-product-quantity-error').remove();
            const errorWrapper = document.createElement('div');
            errorWrapper.classList.add('jet-abaf-product-quantity-error');
            const error = document.createElement('div');
            error.classList.add('jet-abaf-product-quantity-error-message');
            error.innerHTML = message;
            errorWrapper.appendChild(error);
            const errorClose = document.createElement('span');
            errorClose.classList.add('jet-abaf-product-quantity-error-close');
            errorClose.innerHTML = '&times;';
            errorClose.addEventListener('click', () => {
                errorWrapper.remove();
            });
            errorWrapper.appendChild(errorClose);
            const parent = document.querySelector('.jet-abaf-product-check-in-out');
            parent.parentNode.insertBefore(errorWrapper, parent);

            setTimeout(() => {
                errorWrapper.remove();
            }, 5000);
        }
        $bookingQty.on('change', (event) => {
            const dateField = document.querySelector('#jet_abaf_field');
            const date = dateField.value;
            const dateFormat = dateField.getAttribute('data-format');
            window.jetBookingState.bookingCalendars[1].data('dateRangePicker').redraw()
            if (date?.length > 1) {
                const [_startDate, _endDate] = date.split(' - ');
                const startBlock = moment(_startDate, dateFormat).format('YYYY-MM-DD');
                const endBlock = moment(_endDate, dateFormat).format('YYYY-MM-DD');
                if(typeof JetBookingAgendaBlocks !== 'undefined' && JetBookingAgendaBlocks['blocks']){
                    const blocks = JetBookingAgendaBlocks['blocks'];
                    const blockDates = Object.keys(blocks).map(block => moment(block, 'DD-MM-YYYY').format('YYYY-MM-DD'));
                    const blockDatesSet = new Set(blockDates);
                    const selectedDates = [];
                    let current = startBlock;
                    while(current <= endBlock){
                        selectedDates.push(current);
                        current = moment(current).add(1, 'days').format('YYYY-MM-DD');
                    }
                    const intersection = selectedDates.filter(date => blockDatesSet.has(date));
                    if(intersection.length) {
                        showQuantityError(jet_abaf_qefjb.date_not_available_qty.format($bookingQty.val()));
                        $bookingQty.val($bookingQty.val() - 1);
                    }
                }
                window.jetBookingState.bookingCalendars[1].data('dateRangePicker').setStart(startBlock);
                window.jetBookingState.bookingCalendars[1].data('dateRangePicker').setEnd(endBlock);
            }
        })
        $bookingButton.on('click.JetWooBuilder', function(event) {
            event.preventDefault();
            const serialize = $bookingProductForm.closest('form').serialize();
            const pid = $bookingButton.val();
            $bookingButton.addClass("loading");
            const dateField = document.querySelector('#jet_abaf_field');
            const date = dateField.value;
            const dateFormat = dateField.getAttribute('data-format');
            const [_startDate, _endDate] = date.split(' - ');
            const startBlock = moment(_startDate, dateFormat);
            const endBlock = moment(_endDate, dateFormat);
            jQuery.ajax({
                url: window.jetWooBuilderData.ajax_url,
                method: 'POST',
                async: false,
                data: "action=jet_booking_add_cart_single_product&product=" + pid + "&" + serialize,
                success: function(t) {
                    $bookingButton.addClass("added").removeClass("loading");
                    if(t){
                        jQuery(document.body).trigger("wc_fragment_refresh");
                        jQuery(document.body).trigger("added_to_cart", [t.fragments, t.cart_hash, $bookingButton]);
                        if(typeof JetBookingAgendaBlocks !== 'undefined' && JetBookingAgendaBlocks['blocks']){
                            const selectedDates = [];
                            let current = startBlock;

                            while(current.unix()<=endBlock.unix()){
                                selectedDates.push(current.format('DD-MM-YYYY'));
                                current.add(1,'d')
                            }
                            selectedDates.forEach(date => {
                                JetBookingAgendaBlocks['blocks'][date] = parseInt(jQuery(document).find('.jet-booking-form').find('.quantity input.qty').val());
                            })
                            window.jetBookingState.bookingCalendars[1].data('dateRangePicker').redraw()
                        }
                        if(t.fragments?.notices_html)
                            jQuery(".woocommerce-notices-wrapper").html(t.fragments?.notices_html)
                    }
                }
            });
        })
    });
}());
