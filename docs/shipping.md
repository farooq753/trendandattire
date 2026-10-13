# Shipping and tax

Zones contain countries and optional regions. Methods are flat, weight-based, or a percent of order value, with an optional free-shipping threshold. Quotes are estimates. The order snapshot says they are not guarantees.

DHL, FedEx, and UPS can be typed as the carrier and tracking number on a manual shipment. The application does not call their rate or tracking APIs.

Cash on delivery is a flag on the method plus the store COD rules. International sample methods in the seeder do not allow COD.

## Duties

Duties are not calculated. Copy in the footer and the shipping page says the recipient may have to pay charges on delivery. Do not describe any shipment as duty-free.

## Tax

`tax_rules` are rows by country, optional region, rate, inclusive or exclusive, and whether the rate applies to products, shipping, or both. The most specific active rule wins. If no rule matches, tax is zero and the checkout still does not claim that no tax is legally due. These rows are not a filing integration with any tax authority.
