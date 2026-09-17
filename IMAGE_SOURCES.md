# Sweet Crumbs Image Sources

The refreshed storefront uses free-to-use Unsplash photographs for the hero, category cards, and product catalogue. The image URLs are stored directly in `database/seeds.sql` so the PHP/HTML/CSS/JS project remains framework-free.

Source pages used for the photo set:

- https://unsplash.com/photos/a-chocolate-cake-with-strawberries-on-top-HmssZtWo_f8
- https://unsplash.com/photos/strawberry-cake-on-white-ceramic-plate-CkmpBcngUJU
- https://unsplash.com/photos/a-piece-of-chocolate-cake-on-a-blue-plate-k3rx7NK2z7Q
- https://unsplash.com/photos/cupcakes-h2Nh6OMFG9U
- https://unsplash.com/photos/cupcake-on-white-surface-OZfeMV4btPc
- https://unsplash.com/photos/banana-sundae-rITQq_QlOIc
- https://unsplash.com/photos/a-group-of-cookies-7QGrloNqx6w
- https://unsplash.com/photos/delicious-cheesecake-topped-with-fresh-strawberries-raspberries-and-chocolate-ISiYTLt1vrM
- https://unsplash.com/photos/brownies-in-brown-tray-EfmXH4t2k90
- https://unsplash.com/photos/brownies-in-close-up-photography-2hnVd9JJ3Uk
- https://unsplash.com/photos/brownies-on-a-white-plate-KPpU6rCIziQ

The site needs internet access while browsing because the photographs are loaded from Unsplash image URLs. If you later want a fully offline version, download appropriately licensed images and replace the URLs in `database/seeds.sql` with local paths under `assets/images/products/`.
