<div class="cx-vui-notices"><div></div></div>
<div class="jet-abaf-wrap">
    <h3 class="cx-vui-subtitle">Booking Settings</h3>
    <br>
    <div class="cx-vui-panel">
        <div class="cx-vui-tabs__content">
            <div class="cx-vui-tabs-panel">
                <div>
                    <div class="cx-vui-component cx-vui-component--equalwidth">
                        <div class="cx-vui-component__meta">
                            <label class="cx-vui-component__label">Hide datetime input field?</label>
                            <div class="cx-vui-component__desc">Hide the default datetime input field for customers and force them to use the agenda?</div>
                        </div>
                        <div class="cx-vui-component__control">
                            <div tabindex="0" class="cx-vui-switcher <?=(isset($settings['hide_check_in_out'])&&$settings['hide_check_in_out']==='on')?'cx-vui-switcher--on':'cx-vui-switcher--off'?>">
                                <input name="data[hide_check_in_out]" type="hidden" value="<?=$settings['hide_check_in_out']??'on'?>" class="cx-vui-switcher__input">
                                <div class="cx-vui-switcher__panel"></div>
                                <div class="cx-vui-switcher__trigger"></div>
                            </div>
                        </div>
                    </div>
                    <div class="cx-vui-component cx-vui-component--equalwidth">
                        <div class="cx-vui-component__meta">
                            <label class="cx-vui-component__label">Show current and available stock?</label>
                            <div class="cx-vui-component__desc"></div>
                        </div>
                        <div class="cx-vui-component__control">
                            <div tabindex="0" class="cx-vui-switcher <?=(isset($settings['show_stock'])&&$settings['show_stock']==='on')?'cx-vui-switcher--on':'cx-vui-switcher--off'?>">
                                <input name="data[show_stock]" type="hidden" value="<?=$settings['show_stock']??'off'?>" class="cx-vui-switcher__input">
                                <div class="cx-vui-switcher__panel"></div>
                                <div class="cx-vui-switcher__trigger"></div>
                            </div>
                        </div>
                    </div>
                    <div class="cx-vui-component cx-vui-component--equalwidth">
                        <div class="cx-vui-component__meta">
                            <label class="cx-vui-component__label" for="abaf-add_to_cart_text">Product view details label</label>
                            <div class="cx-vui-component__desc">
                                Button text for product view details.
                            </div>
                        </div>
                        <div class="cx-vui-component__control">
                            <input type="text" name="data[add_to_cart_text]" id="abaf-add_to_cart_text" placeholder="" value="<?=$settings['add_to_cart_text']??''?>" class="cx-vui-input size-fullwidth">
                        </div>
                    </div>
                    <div class="cx-vui-component cx-vui-component--equalwidth">
                        <div class="cx-vui-component__meta">
                            <label class="cx-vui-component__label" for="abaf-single_add_to_cart_text">Product Rent label</label>
                            <div class="cx-vui-component__desc">
                                Button text for the rent button
                            </div>
                        </div>
                        <div class="cx-vui-component__control">
                            <input type="text" name="data[single_add_to_cart_text]" id="abaf-single_add_to_cart_text" placeholder="" value="<?=$settings['single_add_to_cart_text']??''?>" class="cx-vui-input size-fullwidth">
                        </div>
                    </div>
                    <div class="cx-vui-component cx-vui-component--equalwidth">
                        <div class="cx-vui-component__meta">
                            <label class="cx-vui-component__label" for="abaf-show_totalstock">In stock message</label>
                            <div class="cx-vui-component__desc">
                                Use %s to display the available stock.
                            </div>
                        </div>
                        <div class="cx-vui-component__control">
                            <input type="text" name="data[show_totalstock]" id="abaf-show_totalstock" placeholder="%s total stock" value="<?=$settings['show_totalstock']??''?>" class="cx-vui-input size-fullwidth">
                        </div>
                    </div>
                    <div class="cx-vui-component cx-vui-component--equalwidth">
                        <div class="cx-vui-component__meta">
                            <label class="cx-vui-component__label" for="abaf-out_of_stock">Out of stock message</label>
                            <div class="cx-vui-component__desc">
                            </div>
                        </div>
                        <div class="cx-vui-component__control">
                            <input type="text" name="data[out_of_stock]" id="abaf-out_of_stock" placeholder="Currently not in stock, please contact us for options" value="<?=$settings['out_of_stock']??''?>" class="cx-vui-input size-fullwidth">
                        </div>
                    </div>
                    <div class="cx-vui-component cx-vui-component--equalwidth">
                        <div class="cx-vui-component__meta">
                            <label class="cx-vui-component__label">Cooldown time before bookings?</label>
                            <div class="cx-vui-component__desc">How many days do you block any new bookings before the actual booking?</div>
                        </div>
                        <div class="cx-vui-component__control">
                            <input type="number" name="data[cooldown]" id="abaf-cooldown" placeholder="0" value="<?=$settings['cooldown']??''?>" class="cx-vui-input size-fullwidth">
                        </div>
                    </div>
                    <div class="cx-vui-component cx-vui-component--equalwidth">
                        <div class="cx-vui-component__meta">
                            <label class="cx-vui-component__label">Warmup time after bookings?</label>
                            <div class="cx-vui-component__desc">How many days do you block any new bookings after the actual booking?</div>
                        </div>
                        <div class="cx-vui-component__control">
                            <input type="number" name="data[warmup]" id="abaf-warmup" placeholder="0" value="<?=$settings['warmup']??''?>" class="cx-vui-input size-fullwidth">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
