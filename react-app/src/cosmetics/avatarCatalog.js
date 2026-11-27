// Avatar-only cosmetic catalog. These assets and selections never enter the pet inventory.
export const AVATAR_CATEGORIES = [
  { label: 'Hats', slot: 'headwear' },
  { label: 'Accessories', slot: 'accessory' },
  { label: 'Clothes', slot: 'outfit' },
  { label: 'Eyes', slot: 'eyes' },
  { label: 'Hair', slot: 'hair' },
  { label: 'Skin Color', slot: 'skin' },
];

const makeItems = (category, slot, prefix, count, names = []) => Array.from({ length: count }, (_, index) => ({
  id: `avatar-${category.toLowerCase()}-${index + 1}`,
  name: names[index] || `${category} ${index + 1}`,
  description: `Avatar ${category.toLowerCase()} customization.`,
  target: 'avatar',
  category,
  slot,
  asset: `avatar/${category.toLowerCase()}/${prefix}-${index + 1}${category === 'Hair' && index === 2 ? '-hijab' : ''}.svg`,
  price: 0,
  currency: 'lp',
  rarity: 'common',
  defaultUnlocked: true,
}));

export const AVATAR_COSMETIC_CATALOG = [
  ...makeItems('Hats', 'headwear', 'hat-avatar', 5),
  ...makeItems('Accessories', 'accessory', 'accessory-avatar', 5),
  ...makeItems('Clothes', 'outfit', 'clothes-avatar', 5),
  ...makeItems('Eyes', 'eyes', 'eyes-avatar', 5),
  ...makeItems('Hair', 'hair', 'hair-avatar', 6, ['Hair 1', 'Hair 2', 'Hijab', 'Hair 4', 'Hair 5', 'Hair 6']),
];
