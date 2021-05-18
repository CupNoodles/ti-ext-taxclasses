## TI Tax Classes Extension

Create multiple tax classes, in case your locality taxes different items at different rates. 

## Installation

Copy this into your extension folder as `/extensions/cupnoodles/taxclasses`. 

## Usage

By default, the extension does not create any new tax classes. Navigate to `/admin/cupnoodles/taxclasses/taxclasses` to create however many groups are needed. For instance, for New York City you may want to create a tax class for 'Grocery' at 0%, and another for 'Food Sales Tax' at 8.875%.

Delivery fees will be taxed at the cumulative rate for all tax classes marked as 'Apply to Delivery'. 

You'll then need to navigate to your respective menu items and modify tax classes as needed. 

Note that the store-wide tax rate isn't disabled by this extention, and can remain in use if needed. If not, disable the 'tax' cart condition `admin/extensions/edit/igniter/cart/settings`.


