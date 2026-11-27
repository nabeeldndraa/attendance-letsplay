# Avatar SVG asset preparation (ready for the next asset upload)

The avatar asset system is deliberately kept separate from the pet asset system.
Do not put avatar artwork in `cosmetics/pet/`, and do not reuse pet equipment IDs.

## Folder layout

Place the supplied SVGs in the matching folders below. Keep the exact source filenames;
we will map them to catalog entries after reviewing your ZIP.

```text
react-app/public/cosmetics/avatar/
  base/          # complete avatar base only, if supplied
  body/          # body/base layers
  eyes/          # eye styles
  mouth/         # mouth and expression layers
  hair/          # hairstyles
  clothes/       # clothing/outfits
  accessories/   # glasses, jewelry, face accessories
  headwear/      # hats, caps, crowns
  front/         # foreground overlays, if needed
```

## Export requirements

- Use one shared square artboard for every layer (recommended: 1524 × 1524, matching the current pet asset set).
- Keep the same `viewBox` on every SVG in the set: `0 0 1524 1524`.
- Export the full page/artboard, not Selection, so transparent alignment space is retained.
- Use a transparent background; do not add a white rectangle.
- Export only the artwork belonging to that layer. For example, an eyes SVG should not contain the face/body.
- Keep the same character position and scale across all layers.
- Preserve the original filenames in the ZIP.

## Suggested ZIP layout

You can either preserve the folders above or put all SVGs in one folder. If they are in one folder,
include clear filenames such as `avatar-body.svg`, `avatar-eyes-1.svg`, `avatar-mouth-normal.svg`,
`avatar-hair-1.svg`, `avatar-clothes-1.svg`, and `avatar-accessory-1.svg`.

## What I will do when the avatar ZIP arrives

1. Inspect every SVG's XML, dimensions, `viewBox`, and visible artwork bounds.
2. Identify the avatar base, face parts, hair, clothing, and accessories from the actual files.
3. Map each file to an avatar-only catalog item and slot.
4. Set any requested default-look assets as unlocked by default.
5. Connect avatar item previews, inventory, shop, and equipped rendering.
6. Verify avatar equipment cannot affect pet equipment or pet appearance.

No avatar SVGs have been invented or added in this preparation step.
