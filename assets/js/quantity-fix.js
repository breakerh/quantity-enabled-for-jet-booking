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
        $bookingQty.on('change', (event) => {
            const dateField = document.querySelector('#jet_abaf_field');
            const date = dateField.value;
            const dateFormat = dateField.getAttribute('data-format');
            window.jetBookingState.bookingCalendars[1].data('dateRangePicker').redraw()
            if (date?.length > 1) {
                const [_startDate, _endDate] = date.split(' - ');
                window.jetBookingState.bookingCalendars[1].data('dateRangePicker').setStart(moment(_startDate, dateFormat).format('YYYY-MM-DD'));
                window.jetBookingState.bookingCalendars[1].data('dateRangePicker').setEnd(moment(_endDate, dateFormat).format('YYYY-MM-DD'));
            }
        })
        $bookingButton.on('click.JetWooBuilder', function(event) {
            event.preventDefault();
            const serialize = $bookingProductForm.closest('form').serialize();
            const pid = $bookingButton.val();
            $bookingButton.addClass("loading");
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
                        if(t.fragments?.notices_html)
                            jQuery(".woocommerce-notices-wrapper").html(t.fragments?.notices_html)
                    }
                }
            });
        })
    });
}());
