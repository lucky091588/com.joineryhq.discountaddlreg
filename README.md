# CiviCRM: Event Sponsor Discounts
# com.joineryhq.discountaddlreg

If an event registrant selects a properly configured price option, the configured 
discounts are applied to the configured number of additional participants.

For example:

* Configure the "Gold Sponsorship" option to allow three free additional participants.
* Configure the "Silver Sponsorship" option to allow up to two additional participants
  to be discounted by up to $150 each.

The extension is licensed under [GPL-3.0](LICENSE.txt).

## Requirements

* PHP v7.0+
* CiviCRM 5.24

## Usage

* For price sets used for CiviEvent, edit any price option on a price field to 
  configure it for discount functionality.
* "Discounts for additional participants" settings are at the bottom of the price 
  option edit form.
* Define the maximum amount to discount, and the maximum number of additional 
  participants who can receive a discount. A discount will be a applied to each 
  eligible participant, up to the configured amount, but never exceeding that 
  participant's total event fees. Discounts will be applied to the configured 
  number of participants, starting with the first Additional Participant.
* Multiple options can be configured like this, thus allowing several different
  options to provide "additional participant" discounts on the same event.

## Support

This extension is developed and maintained by [Joinery](https://joineryhq.com). 
For community-based support on an as-available goodwill basis, please report issues
on the public issue queue at https://github.com/twomice/com.joineryhq.discountaddlreg/issues
For professional paid support, appropriate for mission-critical improvements and
time-sensitive delivery, please contact us directly via https://joineryhq.com.
