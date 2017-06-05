# wooOUp
Integration between Woocommerce and Edisoftware OndaIQ to get realtime product quantities using Laravel Apis as middleware backend.
(https://www.edisoftware.it - Require an Api)

For every product you want to control via api, enable "stock management" options in product or variation option ad fill the correct sku in product or variation(this must match OndaIQ product id).
The plugin automatically update stock via api everytime product page is loaded.
