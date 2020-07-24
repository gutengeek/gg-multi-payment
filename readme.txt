=== GG Multiple Payment Routing for WooCommerce - Split and manage PayPal, Stripe accounts ===
Contributors: gutengeek, ndoublehwp
Tags: multiple payment routing, woocommerce paypal, woocommerce stripe, paypal, stripe, split payment account, gutengeek
Requires at least: 5.2
Tested up to: 5.4.2
Requires PHP: 7.0
Stable tag: 1.0.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

GG Multiple Payment Routing for WooCommerce helps you create additional payment accounts (PayPal, Stripe) and auto-select them based on rules.

== Description ==

[Plugin Document](https://wpdocs.gitbook.io/gg-multi-payment-routing-for-woocommerce/) | [Free Support](https://themelexus.ticksy.com/)  | [More Plugin](https://gutengeek.com/gutenberg-plugins/)

**GG Multiple Payment Routing for WooCommerce** helps you create additional payment accounts (PayPal, Stripe) and auto select them base on rules.
Some payment gateways will be limited money per day. You need to receive money from more than one account.
This plugin allows you to config additional accounts with rules.
Example: Condition: account A just can be receive maximum $200 per day, account B just can be receive maximum $300 per day.
The plugin will choose one of two accounts automatically in the checkout page.

## Features
* Create additional payment accounts for Paypal or Stripe
* Switch payment accounts automatically based on rules
* Show reports for each account

## Payment gateways supported:
* Paypal via plugin: [WooCommerce PayPal Checkout Payment Gateway](https://wordpress.org/plugins/woocommerce-gateway-paypal-express-checkout/ "WooCommerce PayPal Checkout Payment Gateway")
* Stripe via plugin: [WooCommerce Stripe Payment Gateway](https://wordpress.org/plugins/woocommerce-gateway-stripe/ "WooCommerce Stripe Payment Gateway")
Notice: Please install and activate above plugins first.

## Settings
* Visit **Dashboard > Payment Routing > Settings** and enable payment gateways. Notice: Install WooCommerce payment gateway add-ons before use.
* Visit **Dashboard > Payment Routing > PayPal Accounts or Stripe Accounts** to create additional accounts then set Account informations and **Limit money per day** for them.
* Now, when customers checkout in the checkout page, an account will be selected automatically base on current deposited money and **Limit money per day**

== Installation ==

= Minimum Requirements =

* PHP 7.2 or greater is recommended
* MySQL 5.6 or greater is recommended
* WooCommerce version 3.6.

== Documentation & Support ==

* Detailed guide to install and customize: [Documentation](https://wpdocs.gitbook.io/gg-multi-payment-routing-for-woocommerce/ "Visit the Plugin docs")
* System tickets support 24/7 available : [Free support](https://themelexus.ticksy.com/ "Visit the Plugin support forum")

== Changelog ==

= 1.0 =
* Release
