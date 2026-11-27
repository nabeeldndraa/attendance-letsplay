import { useMemo } from 'react';
import { getCosmeticAssetUrl, getCosmeticSlot, getEquippedCosmeticItems } from '../cosmetics/catalog';
import baseColorSvg from '../assets/avatar/base-avatar-base-color.svg?raw';
import lineartSvg from '../assets/avatar/base-avatar-lineart.svg?raw';
import hair1 from '../assets/avatar/hair-avatar-1.svg?raw';
import hair2 from '../assets/avatar/hair-avatar-2.svg?raw';
import hair3 from '../assets/avatar/hair-avatar-3-hijab.svg?raw';
import hair4 from '../assets/avatar/hair-avatar-4.svg?raw';
import hair5 from '../assets/avatar/hair-avatar-5.svg?raw';
import hair6 from '../assets/avatar/hair-avatar-6.svg?raw';

const LAYER_ORDER = ['outfit', 'eyes', 'hair', 'accessory', 'headwear'];
const HAIR_SVGS = {
  'avatar-hair-1': hair1,
  'avatar-hair-2': hair2,
  'avatar-hair-3': hair3,
  'avatar-hair-4': hair4,
  'avatar-hair-5': hair5,
  'avatar-hair-6': hair6,
};

function svgData(svg) {
  const cleanSvg = svg.replace(/<\?xml[^>]*\?>/i, '').replace(/<!DOCTYPE[\s\S]*?>/i, '');
  return `data:image/svg+xml;charset=utf-8,${encodeURIComponent(cleanSvg)}`;
}

function lightenHex(hex, amount = 0.12) {
  const value = Number.parseInt(hex.slice(1), 16);
  const channels = [value >> 16, (value >> 8) & 255, value & 255].map((channel) => Math.round(channel + (255 - channel) * amount));
  return `#${channels.map((channel) => channel.toString(16).padStart(2, '0')).join('')}`.toUpperCase();
}

function tintBase(svg, color) {
  return svg.replace(/#FEFEFE/gi, color).replace(/#EEEEEF/gi, lightenHex(color));
}

function tintHair(svg, color, originalColor) {
  const escaped = originalColor.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
  return svg.replace(new RegExp(escaped, 'gi'), color);
}

function layerImage(item, index, hairColor) {
  let src = getCosmeticAssetUrl(item);
  if (getCosmeticSlot(item, 'avatar') === 'hair' && HAIR_SVGS[item.id]) {
    const original = item.id === 'avatar-hair-3' ? '#7C6FB0' : '#394347';
    src = svgData(tintHair(HAIR_SVGS[item.id], hairColor || '#394347', original));
  }
  if (!src) return null;
  return (
    <image
      key={`${item.id}-${index}`}
      className={`petgame-avatar-svg-layer avatar-slot-${getCosmeticSlot(item, 'avatar')}`}
      href={src}
      x="0" y="0" width="180" height="180"
      preserveAspectRatio="xMidYMid meet"
    />
  );
}

export default function AvatarArtwork({ mood = 'neutral', parts = {}, cosmetics = {}, cosmeticItems = [] }) {
  const equipped = getEquippedCosmeticItems(cosmetics, cosmeticItems, 'avatar');
  const baseColor = parts.baseColor || '#FEFEFE';
  const hairColor = parts.hairColor || '#394347';
  const base = useMemo(() => svgData(tintBase(baseColorSvg, baseColor)), [baseColor]);
  const lineart = useMemo(() => svgData(lineartSvg), []);
  return (
    <div className={`petgame-artwork petgame-avatar-artwork petgame-artwork-${mood}`}>
      <svg viewBox="0 0 180 180" role="img" aria-label="Customized player avatar" xmlns="http://www.w3.org/2000/svg">
        <image className="petgame-avatar-base-color" href={base} x="0" y="0" width="180" height="180" preserveAspectRatio="xMidYMid meet" />
        <image className="petgame-avatar-base-lineart" href={lineart} x="0" y="0" width="180" height="180" preserveAspectRatio="xMidYMid meet" />
        {LAYER_ORDER.map((slot) => equipped.filter((item) => getCosmeticSlot(item, 'avatar') === slot).map((item, index) => layerImage(item, index, hairColor)))}
      </svg>
    </div>
  );
}
