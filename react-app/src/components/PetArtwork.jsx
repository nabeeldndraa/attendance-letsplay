import { getGameAsset } from '../assets';
import basePetColorSvg from '../assets/pet/base-pet-base-color.svg?raw';
import { getCosmeticAssetUrl, getCosmeticSlot, getEquippedCosmeticItems } from '../cosmetics/catalog';

const DEFAULT_PARTS = {
  body: 'cream',
  outfit: 'none',
  baseColor: '#FEFEFE',
  eyes: 'round',
  hair: 'none',
  accessory: 'none',
};

function CosmeticLayers({ cosmetics, cosmeticItems, slot }) {
  return getEquippedCosmeticItems(cosmetics, cosmeticItems, 'pet')
    .filter((item) => (item.id === 'pet-accessories-5' ? slot === 'back' : getCosmeticSlot(item, 'pet') === slot))
    .map((item) => (
      <image
        key={item.id}
        className={`petgame-cosmetic-svg-layer cosmetic-slot-${slot}`}
        href={getCosmeticAssetUrl(item)}
        x="0" y="0" width="180" height="180"
        preserveAspectRatio="xMidYMid meet"
      />
    ));
}

export default function PetArtwork({ mood = 'neutral', parts = {}, cosmetics = {}, cosmeticItems = [] }) {
  const avatar = { ...DEFAULT_PARTS, ...parts };
  const customArtwork = getGameAsset('petArtwork') || getGameAsset('pet', 'artwork');
  const layerAsset = (layer) => getGameAsset('petLayers', layer, avatar[layer]);
  const petBaseAsset = getCosmeticAssetUrl({ asset: 'pet/base/base-pet-base-lineart.svg' });
  const recoloredBase = basePetColorSvg
    .replace(/fill:#FEFEFE/gi, `fill:${avatar.baseColor || '#FEFEFE'}`)
    .replace(/fill:white/gi, `fill:${avatar.baseColor || '#FEFEFE'}`);
  const bodyAsset = `data:image/svg+xml;charset=utf-8,${encodeURIComponent(recoloredBase)}`;
  const outfitAsset = avatar.outfit && avatar.outfit !== 'none' ? layerAsset('outfit') : null;
  const equippedPetItems = getEquippedCosmeticItems(cosmetics, cosmeticItems, 'pet');
  const equippedEyes = equippedPetItems.some((item) => getCosmeticSlot(item, 'pet') === 'eyes');
  const equippedOutfit = equippedPetItems.some((item) => getCosmeticSlot(item, 'pet') === 'outfit');
  const eyesAsset = equippedEyes ? null : getCosmeticAssetUrl({ asset: 'pet/eyes/eyes-pet-1.svg' }) || layerAsset('eyes');
  const mouthAsset = getCosmeticAssetUrl({ asset: mood === 'happy' ? 'pet/expressions/mouth-pet-happy.svg' : mood === 'sad' ? 'pet/expressions/mouth-pet-sad.svg' : 'pet/expressions/mouth-pet-normal.svg' });
  const hairAsset = avatar.hair !== 'none' ? layerAsset('hair') : null;
  const accessoryAsset = layerAsset('accessory');

  return (
    <div className={`petgame-artwork petgame-pet-artwork petgame-artwork-${mood}`}>
      {customArtwork && !petBaseAsset && (
        <>
          <img className="petgame-custom-artwork-asset" src={customArtwork} alt={`${mood} pet`} />
          <svg className="petgame-artwork-cosmetic-overlay" viewBox="0 0 180 180" aria-hidden="true">
            {getEquippedCosmeticItems(cosmetics, cosmeticItems, 'pet')
              .sort((a, b) => ['back', 'body', 'pattern', 'outfit', 'eyes', 'hair', 'headwear', 'accessory', 'front'].indexOf(getCosmeticSlot(a, 'pet')) - ['back', 'body', 'pattern', 'outfit', 'eyes', 'hair', 'headwear', 'accessory', 'front'].indexOf(getCosmeticSlot(b, 'pet')) )
              .map((item) => <image key={item.id} href={getCosmeticAssetUrl(item)} x="0" y="0" width="180" height="180" preserveAspectRatio="xMidYMid meet" />)}
          </svg>
        </>
      )}
      {(!customArtwork || petBaseAsset) && (
      <svg
        className="petgame-artwork-svg"
        viewBox="0 0 180 180"
        xmlns="http://www.w3.org/2000/svg"
        role="img"
        aria-label={`${mood} layered custom pet`}
      >
        <CosmeticLayers cosmetics={cosmetics} cosmeticItems={cosmeticItems} slot="back" />
        <ellipse className="petgame-artwork-shadow" cx="90" cy="158" rx="47" ry="8" />

        <g className={`petgame-avatar-layer petgame-avatar-body body-${avatar.body}`}>
          <image href={bodyAsset} x="0" y="0" width="180" height="180" preserveAspectRatio="xMidYMid meet" />
        </g>

        {!equippedOutfit && outfitAsset && (
          <g className={`petgame-avatar-layer petgame-avatar-outfit outfit-${avatar.outfit}`}>
            {outfitAsset ? (
              <image href={outfitAsset} x="0" y="0" width="180" height="180" preserveAspectRatio="xMidYMid meet" />
            ) : (
              <path d="M45 116Q90 94 135 116l16 41q-61 20-122 0l16-41Z" />
            )}
          </g>
        )}

        {petBaseAsset && <image className="petgame-pet-base-lineart" href={petBaseAsset} x="0" y="0" width="180" height="180" preserveAspectRatio="xMidYMid meet" />}
        <CosmeticLayers cosmetics={cosmetics} cosmeticItems={cosmeticItems} slot="body" />
        <CosmeticLayers cosmetics={cosmetics} cosmeticItems={cosmeticItems} slot="pattern" />

        {!bodyAsset && (
          <g className={`petgame-avatar-layer petgame-avatar-body body-${avatar.body}`}>
            <path className="petgame-head" d="M45 68 39 29l34 23q17-8 34 0l34-23-6 39q13 12 13 30 0 36-58 36T32 98q0-18 13-30Z" />
            <path className="petgame-ear-inner" d="m47 57-3-18 20 13m52 5 20-13-3 18" />
          </g>
        )}

        <CosmeticLayers cosmetics={cosmetics} cosmeticItems={cosmeticItems} slot="outfit" />

        <g className={`petgame-avatar-layer petgame-avatar-eyes eyes-${avatar.eyes}`}>
          {equippedEyes ? null : eyesAsset ? (
            <image href={eyesAsset} x="0" y="0" width="180" height="180" preserveAspectRatio="xMidYMid meet" />
          ) : avatar.eyes === 'sleepy' ? (
            <path d="M59 85q9-8 18 0m26 0q9-8 18 0" />
          ) : avatar.eyes === 'starry' ? (
            <>
              <path d="m68 74 3 6 7 1-5 5 1 7-6-3-6 3 1-7-5-5 7-1 3-6Zm44 0 3 6 7 1-5 5 1 7-6-3-6 3 1-7-5-5 7-1 3-6Z" />
            </>
          ) : (
            <>
              <ellipse cx="69" cy="83" rx={avatar.eyes === 'spark' ? 5 : 4} ry="6" />
              <ellipse cx="111" cy="83" rx={avatar.eyes === 'spark' ? 5 : 4} ry="6" />
              {avatar.eyes === 'spark' && <path className="petgame-eye-glint" d="M67 81h.1m42 0h.1" />}
            </>
          )}
        </g>

        <CosmeticLayers cosmetics={cosmetics} cosmeticItems={cosmeticItems} slot="eyes" />

        <g className="petgame-avatar-layer petgame-avatar-cheeks">
          <ellipse cx="54" cy="99" rx="8" ry="4" />
          <ellipse cx="126" cy="99" rx="8" ry="4" />
        </g>

        {mouthAsset ? (
          <>
            <image
              key={`mouth-image-${mood}`}
              className={`petgame-avatar-layer petgame-avatar-expression expression-${mood}`}
              href={mouthAsset}
              x="0" y="0" width="180" height="180"
              preserveAspectRatio="xMidYMid meet"
              onError={(event) => {
                event.currentTarget.style.display = 'none';
                event.currentTarget.nextElementSibling?.setAttribute('display', 'inline');
              }}
            />
            <g key={`mouth-fallback-${mood}`} className={`petgame-avatar-layer petgame-avatar-expression expression-${mood}`} display="none">
              {mood === 'sad' ? <path className="petgame-expression-frown" d="M80 106q10-11 20 0" /> : mood === 'happy' ? <path className="petgame-expression-smile" d="M81 100q9 9 18 0" /> : <path className="petgame-expression-neutral" d="M84 103h12" />}
            </g>
          </>
        ) : (
          <g className={`petgame-avatar-layer petgame-avatar-expression expression-${mood}`}>
            {mood === 'sad' ? (
              <path className="petgame-expression-frown" d="M80 106q10-11 20 0" />
            ) : mood === 'happy' ? (
              <path className="petgame-expression-smile" d="M81 100q9 9 18 0" />
            ) : (
              <path className="petgame-expression-neutral" d="M84 103h12" />
            )}
          </g>
        )}

        {hairAsset && <image href={hairAsset} x="0" y="0" width="180" height="180" preserveAspectRatio="xMidYMid meet" />}
        <CosmeticLayers cosmetics={cosmetics} cosmeticItems={cosmeticItems} slot="hair" />
        <CosmeticLayers cosmetics={cosmetics} cosmeticItems={cosmeticItems} slot="headwear" />

        {accessoryAsset ? (
          <image className="petgame-avatar-layer" href={accessoryAsset} x="0" y="0" width="180" height="180" preserveAspectRatio="xMidYMid meet" />
        ) : avatar.accessory !== 'none' && (
          <g className={`petgame-avatar-layer petgame-avatar-accessory accessory-${avatar.accessory}`}>
            {avatar.accessory === 'glasses' && (
              <g>
                <circle cx="69" cy="84" r="12" />
                <circle cx="111" cy="84" r="12" />
                <path d="M81 84h18m-34 0h-8m66 0h-8" />
              </g>
            )}
            {avatar.accessory === 'flower' && (
              <g>
                <circle cx="128" cy="51" r="5" />
                <circle cx="121" cy="51" r="5" />
                <circle cx="124" cy="45" r="5" />
                <circle cx="131" cy="45" r="5" />
                <circle cx="127" cy="47" r="2" />
              </g>
            )}
            {avatar.accessory === 'crown' && <path d="m55 52 7-21 17 14 11-18 12 18 17-14 6 21H55Z" />}
            {avatar.accessory === 'bow' && (
              <path d="M126 49c-9-10-18-5-14 4 3 7 10 6 14 2 4 4 11 5 14-2 4-9-5-14-14-4Z" />
            )}
          </g>
        )}
        <CosmeticLayers cosmetics={cosmetics} cosmeticItems={cosmeticItems} slot="accessory" />
        <CosmeticLayers cosmetics={cosmetics} cosmeticItems={cosmeticItems} slot="front" />
      </svg>
      )}
    </div>
  );
}
