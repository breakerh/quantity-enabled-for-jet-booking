# Quantity Enabled for Jet Booking

| **Plugin name**       | Quantity Enabled for Jet Booking          |
|-----------------------|-------------------------------------------|
| **Contributors**      | breakerh                                  |
| **Donate link**       | https://paypal.me/bramhammer              |
| **Requires at least** | Wordpress 5.9                             |
| **Tested up to**      | Wordpress 6.5.2                           |
| **Stable tag**        | 1.2.3                                     |
| **Requires PHP**      | 8.0                                       |
| **License**           | GPLv3 or later                            |
| **License URI**       | https://www.gnu.org/licenses/gpl-3.0.html |

## Description

**🛠️ Are you tired of your customers having to add the same product multiple times just to order in bulk? I've got some game-changing news for you! 🎉**

### Here's why you'll want to integrate it into your webshop:

1. **Efficiency Boost:** Say goodbye to the hassle of adding products multiple times for bulk orders. With the plugin, your customers can now set the quantity they desire with ease. 
2. **Streamlined Workflow:** No more tedious repetition. Whether it's 5 lamps or 50, your customers can specify the quantity they need in just one go and the 'double calendar' always scrolls both months instead of a single one.
3. **Enhanced User Experience:** The plugin allows you to set multiple strings and is compatible with WPML and (almost?) all other multilanguage plugins!
4. **Customization Options:** Tailor the plugin to fit your specific needs with customizable cooldown and warmup periods, both globally and on a product level.
5. **Developer-Friendly Integration:** The plugin seamlessly integrates with Jet Booking, without altering their source code!

**Like the plugin? [Send a small donation that helps me convince my wife to keep doing open source :stuck_out_tongue_winking_eye:!](https://paypal.me/bramhammer)**

## Installation

1. **Install the plugin:** Download the plugin and install it on your WordPress website.
2. **Activate the plugin:** Once installed, activate the plugin.
3. **Setup (optional):** Navigate to `Bookings > Quantity Settings` and configure the settings to your liking.
4. **Enjoy the benefits:** Your customers can now set the quantity they desire with ease!

## Roadmap

- [ ] Possibility to set cooldown and warmup, show stock, and max quantity also on category level

## Changelog

### 1.2.3
* Issue #1 fixed: The quantity was not being saved in the cart or respected the product his max.
  And the calculation and checks were missing.

### 1.2.2
* Added the function to restrict the max quantity to match the number of units created

### 1.2.1
* Fixed a bug where the settings page was not saving
* Added cooldown and warmup on a global level and per product level

### 1.2.0
* Added settings page for variables and features

### 1.1.1
* Quantity pushed with add to cart instead of for loop

### 1.1.0
* Added enhanced add to cart for variations

### 1.0
* Initial release

## Upgrade Notice

### 1.2.2 & 1.2.3
Nothing to mention

### 1.2.1
Version 1.2.0 had default cooldown and warmup of 1 day. This has been reverted to 0. This means that the cooldown and warmup are disabled by default. If you want to enable them, you can do so in the settings page. 

### 1.2.0
Settings page added. Text can be added and features can be enabled/disabled

### 1.1.1 & 1.1.0
Nothing to mention

### 1.0
Initial release
