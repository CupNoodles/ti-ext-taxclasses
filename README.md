# Tax Classes for TastyIgniter

Charge different taxes on different menu items, for places where food, groceries, drinks or
delivery are taxed differently. For example, in New York City you might have "Sales Tax" at 8.875%
on prepared food and nothing on groceries. With VAT, you might have 20% and 5% included in prices.

- Any number of tax classes, each with its own rate, to 4 decimal places (eg. 8.875%)
- **Several classes per menu item and per category.** Items without classes of their own get their
  categories' classes.
- Taxes **added on top** of prices, or **included in prices** (VAT style). Several included taxes
  on one item are worked out together.
- Optionally tax the **delivery charge** and **payment fees**
- Limit a tax to some **order types**, eg. delivery only
- Cart-wide **coupons** reduce the tax: it's charged on what the customer actually pays
- Each class is its own line in the cart, checkout, order emails and admin order totals

TastyIgniter's own tax setting has one rate for everything, in whole percentages.

> Using TastyIgniter v3? Use the [`v3` branch](https://github.com/CupNoodles/ti-ext-taxclasses/tree/v3).

## Requirements

- TastyIgniter v4.5 or later, with the Cart and Local extensions (installed by default)

## Installation

### From GitHub (git clone)

From your TastyIgniter root folder, clone the extension into `extensions/cupnoodles/taxclasses`. The
folder name matters: TastyIgniter finds extensions at `extensions/<vendor>/<name>`.

```bash
git clone https://github.com/CupNoodles/ti-ext-taxclasses.git extensions/cupnoodles/taxclasses
php artisan igniter:up
```

To update later:

```bash
cd extensions/cupnoodles/taxclasses
git pull
cd ../../..
php artisan igniter:up
```

### With Composer, from GitHub

```bash
composer config repositories.cupnoodles-taxclasses vcs https://github.com/CupNoodles/ti-ext-taxclasses
composer require cupnoodles/ti-ext-taxclasses:dev-master
php artisan igniter:up
```

Once a version is tagged, you can require `^4.0` instead of `dev-master`.

### Upgrading from v3

Replace the v3 folder with this version and run `php artisan igniter:up`. Your tax classes and the
menu items in them are kept. A class that had **Apply to previous cart conditions** turned on gets
both **Also tax the delivery charge** and **Also tax payment fees**; check those, since v3 also
taxed any other charge listed above it.

## Setting up

1. **Turn off TastyIgniter's own tax**, or customers are taxed twice: in **System > Settings >
   Order**, on the **Taxation** tab, turn **Tax Mode** off.
2. Go to **Restaurant > Tax Classes** and click **New** for each tax:
   - **Name** for staff, and the **Rate** as a percentage (8.875, not 0.08875)
   - **Label**: the line customers see. Leave blank for the name and rate, eg. "Sales Tax 8.875%".
   - **Included in menu prices**: for VAT-style pricing. The tax is shown, eg. "VAT 20% (included)",
     but not added to the total.
   - **Only for these order types**: leave all unselected to charge it on every order.
   - **Also tax the delivery charge** / **Also tax payment fees**
3. Put items in tax classes:
   - On a **category** (**Restaurant > Menus**, then **Categories**), tick its tax classes. Every item in the
     category is charged them.
   - On a **menu item**, tick tax classes to override its categories', eg. a tax-free item in a taxed
     category. Leave them all unticked to use its categories' classes. The form shows which classes
     the item's categories give it.

   An item in several categories gets the classes of all of them. An item with no classes, in no
   taxed category, isn't taxed.

The tax classes list shows how many items and categories each class is assigned to.

### Order of the totals

Each tax class is a **cart condition**, listed in **System > Settings > Cart Settings > Cart
Conditions** (also the **Cart Conditions** button on the tax classes list). Keep the tax classes
**below Delivery, Coupon and Payment Fee** there, as they are by default: taxes are worked out from
the charges and discounts listed above them. Tips are never taxed.

You can also turn a tax class off there without deleting it.

## How tax is worked out

- Each item's tax is its price including options, times the rates of its classes. Coupons that
  apply to certain items reduce those items' prices first.
- A coupon on the whole cart is spread over all items in proportion to their prices.
- Included taxes are taken out of the price: a 12.00 item with 20% VAT included contains 2.00 of
  VAT. If an item has several included taxes, they're taken out together. Taxes added on top are
  charged on the price without its included taxes.
- Each class's tax is rounded once, to your currency's decimal places, after adding up all items.

## License

MIT. See [LICENSE](LICENSE).
