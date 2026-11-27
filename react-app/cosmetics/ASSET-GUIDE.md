# Let'sPlay character and cosmetic SVG guide

This project uses a shared cosmetic catalog for the Shop, item detail preview, purchase-success card, customization inventory, and live pet/avatar artwork.

## 1. Where files go

Put SVG files under `react-app/public/cosmetics/`:

```text
react-app/public/cosmetics/
  pet/hats/  pet/accessories/  pet/clothes/  pet/eyes/  pet/patterns/
  avatar/hair/  avatar/accessories/  avatar/clothes/  avatar/eyes/  avatar/body/
  templates/
```

Vite copies `public/cosmetics/` into `assets/build/cosmetics/` when you run `npm run build`.

## 2. SVG canvas and alignment

The character components use a `1524 × 1524` SVG canvas. For easiest alignment, export each wearable as a transparent SVG with:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1524 1524">
  <!-- artwork positioned on the shared character canvas -->
</svg>
```

Keep head/body anchor points consistent across layers. An overlay should contain only the item artwork and transparent space everywhere else. Do not include a white background. A sample is at `react-app/public/cosmetics/templates/item-template.svg`.

## 3. Register an item

Open `react-app/src/cosmetics/catalog.js` and add an object to `COSMETIC_CATALOG`:

```js
{
  id: 'pet-berry-cap',             // stable ID; do not change after purchase
  name: 'Berry Cap',
  description: 'A soft berry-colored cap.',
  target: 'pet',                   // 'pet', 'avatar', or 'both'
  category: 'Hats',                // Hats, Accessories, Clothes, Eyes, Corak
  slot: 'headwear',                // see slot list below
  asset: 'pet/hats/berry-cap.svg', // relative to public/cosmetics/
  price: 40,
  currency: 'lp',                  // 'lp' or 'attendance'
  rarity: 'common',                // 'common', 'rare', or 'special'
},
```

Available render slots: `back`, `body`, `pattern` (pet), `outfit`, `eyes`, `hair`, `headwear`, `accessory`, and `front`. If `slot` is omitted, `Hats` defaults to `headwear` for pets and `hair` for avatars; `Accessories` to `accessory`; `Clothes` to `outfit`; `Eyes` to `eyes`; and `Corak` to `pattern` for pets or `body` for avatars. For an avatar hat that sits above the hair, set `slot: 'headwear'` explicitly.

To replace a current demo placeholder instead of adding another tile, reuse an existing ID, such as `shop-hats-1`, `shop-accessories-1`, or `shop-clothes-1`. Otherwise a new unique ID adds a new item to that Shop section.

## 4. Layer order

- `back`: behind the character.
- `body`: after the base body and before clothes.
- `pattern`: over the pet body and under the outfit.
- `outfit`: over base clothing/body.
- `eyes`: over default eyes.
- `hair`: over the head/face (leave transparent space around facial features).
- `headwear`: above the head/hair.
- `accessory`: near the front (glasses, bows, badges).
- `front`: final topmost overlay; use sparingly.

Overlays render on the full 1524 × 1524 canvas, preserving your SVG's exact alignment. For glasses or items that must sit in front of the face, `accessory` is normally correct.

## 5. Pet vs avatar separation

Every item has a `target`. `pet` items only appear for the pet; `avatar` items only appear for the avatar; `both` items can be used by either character. Pet and avatar equipped-item selections are saved separately. Avatar hair changes do not change the pet. The avatar's built-in Body/Outfit/Eyes/Hair/Accessory choices remain available alongside owned SVG cosmetics.

## 6. Replace the base pet or avatar artwork

The existing artwork components support a complete replacement image and individual named layers through the WordPress `petgame_assets` filter. Add the files to the plugin, then register their public URLs in `letsplay-pet-game.php` after `PETGAME_PLUGIN_URL` is defined:

```php
add_filter( 'petgame_assets', function ( $assets ) {
    $base = PETGAME_PLUGIN_URL . 'assets/characters/';

    // Complete base artwork (optional; replaces the built-in inline SVG).
    $assets['petArtwork'] = $base . 'pet/base.svg';
    $assets['avatarArtwork'] = $base . 'avatar/base.svg';

    // Or provide separately replaceable base layers.
    $assets['petLayers']['body']['cream'] = $base . 'pet/body/cream.svg';
    $assets['petLayers']['outfit']['berry'] = $base . 'pet/outfit/berry.svg';
    $assets['petLayers']['eyes']['round'] = $base . 'pet/eyes/round.svg';
    $assets['petLayers']['hair']['tuft'] = $base . 'pet/hair/tuft.svg';
    $assets['petLayers']['accessory']['flower'] = $base . 'pet/accessories/flower.svg';
    $assets['avatarLayers']['body']['cream'] = $base . 'avatar/body/cream.svg';
    $assets['avatarLayers']['outfit']['berry'] = $base . 'avatar/outfit/berry.svg';
    $assets['avatarLayers']['hair']['tuft'] = $base . 'avatar/hair/tuft.svg';

    return $assets;
} );
```

Use the individual `petLayers` / `avatarLayers` keys to replace one built-in layer at a time. Use `petArtwork` / `avatarArtwork` only when you intend to replace the entire base illustration. Both character renderers treat a configured body layer as the base body/head shape; that SVG needs to include the head silhouette as well as the body, or the normal built-in head silhouette is suppressed. The avatar renderer still draws its eyes/mouth over it. Use `petArtwork` / `avatarArtwork` when the SVG already contains the complete character face and body.

## 7. Purchase and ownership

An item appears in customization only after its ID is in the user's `owned_item_ids`. The existing purchase API records ownership. The SVG appears in the Shop tile, item detail preview, successful-purchase card, customization choice, and equipped character preview.

The catalog is front-end display metadata. For a production shop, the WordPress purchase endpoint's price and item catalog should also be controlled server-side; the existing endpoint accepts the price sent by the client, so do not treat client-side prices as secure payment validation.

## 8. Build and test

From `react-app/` run `npm run dev` for local testing or `npm run build` to copy assets into `assets/build/` for the WordPress plugin. Deploy the source catalog and built `assets/build/cosmetics/` files together.


## 9. Avatar asset preparation

See `public/cosmetics/avatar/AVATAR-ASSET-PREPARATION.md` for the dedicated avatar folder layout and export checklist.
