import { useEffect, useRef, useState } from 'react';
import { createPortal } from 'react-dom';
import { PetGameAPI } from '../api';
import { getGameAsset } from '../assets';
import AvatarArtwork from './AvatarArtwork';
import Icon from './Icon';
import PetArtwork from './PetArtwork';
import { playSoundCue } from '../sound';
import useLiveRefresh from '../hooks/useLiveRefresh';
import AnimatedNumber from './AnimatedNumber';
import { COSMETIC_CATALOG, getCosmeticAssetUrl, getCosmeticSlot, getDefaultOwnedCosmeticIds, mergeCosmeticCatalog } from '../cosmetics/catalog';
import { AVATAR_COSMETIC_CATALOG, AVATAR_CATEGORIES } from '../cosmetics/avatarCatalog';

const MOOD_LABEL = {
  sad: 'Not good',
  neutral: 'Feeling better',
  happy: 'Feeling good',
};

const CARE_ACTIONS = [
  { action: 'feed', label: 'Feed', icon: 'feed' },
  { action: 'play', label: 'Play', icon: 'play' },
  { action: 'clean', label: 'Clean', icon: 'clean' },
];

const TRIBUTE_CARD_TEXT = `In memory of Istivano - CEO - Game Designer — \"We'll Make You Proud, Mas\" from Team Poli J`;
const DEV_MODE = !window.petgameConfig || window.petgameConfig.apiRoot.includes('mock');
const PET_IDLE_RECOVERY_MS = 30000;

const PET_DEFAULT_PARTS = {
  body: 'cream',
  outfit: 'none',
  baseColor: '#FEFEFE',
  eyes: 'round',
  hair: 'tuft',
  accessory: 'none',
};

// Avatar defaults are intentionally separate from pet defaults. Saved avatar
// hair and accessories must never be applied to the pet's own customization.
const AVATAR_DEFAULT_PARTS = { ...PET_DEFAULT_PARTS, hair: 'none', baseColor: '#FEFEFE', hairColor: '#394347' };
const AVATAR_DEFAULT_SELECTIONS = { Clothes: 'avatar-clothes-1', Eyes: 'avatar-eyes-1', Hair: 'avatar-hair-1', 'Skin Color': '' };
const PART_KEYS = ['body', 'outfit', 'eyes', 'hair', 'accessory', 'baseColor'];
const AVATAR_PART_KEYS = [...PART_KEYS, 'hairColor'];

function normalItemsBeforeSpecial(items) {
  const normal = [];
  const special = [];
  (Array.isArray(items) ? items : []).forEach((item) => {
    if (item?.rarity === 'special' || item?.rarity === 'rare') special.push(item);
    else normal.push(item);
  });
  return [...normal, ...special];
}

const SHOP_CATEGORIES = ['Hats', 'Accessories', 'Clothes', 'Eyes', 'Corak'];
const SHOP_CATEGORY_ICONS = {
  Hats: 'hat', Accessories: 'accessory', Clothes: 'outfit', Eyes: 'eyes', Corak: 'pattern',
  'Base Color': 'palette', Hair: 'hair', 'Skin Color': 'palette',
};
const CATEGORY_ICON_ASSETS = {
  Hats: 'hat.svg', Accessories: 'accessories.svg', Clothes: 'clothes.svg', Eyes: 'eyes.svg',
  Corak: 'pattern.svg', 'Base Color': 'base-color.svg', 'Skin Color': 'base-color.svg',
};
const CATEGORY_ICON_BASE = window.petgameConfig?.cosmeticsBaseUrl
  ? window.petgameConfig.cosmeticsBaseUrl.replace(/cosmetics\/?$/, 'ui/category-icons/')
  : `${import.meta.env.BASE_URL}ui/category-icons/`;
const categoryIconSrc = (category) => {
  const key = Object.keys(CATEGORY_ICON_ASSETS).find((name) => name.toLowerCase() === String(category || '').toLowerCase());
  return key ? `${CATEGORY_ICON_BASE}${CATEGORY_ICON_ASSETS[key]}` : null;
};
const PET_STUDIO_LAYERS = [
  ...SHOP_CATEGORIES.map((label) => ({ id: `shop:${label}`, label })),
  { id: 'base-color', label: 'Base Color' },
];
const AVATAR_SKIN_COLORS = [
  ['Porcelain Rose', '#F9E3DC'], ['Ivory Neutral', '#F6DFCE'], ['Warm Alabaster', '#F4DEC2'],
  ['Soft Peach', '#EBC3B0'], ['Natural Beige', '#E5C2A8'], ['Light Honey', '#E3BE8D'],
  ['Rosewood', '#D19D84'], ['Warm Sand', '#CCA07D'], ['Golden Ochre', '#CE9B6B'],
  ['Terracotta', '#B3785B'], ['Caramel', '#AD7A55'], ['Warm Bronze', '#AC7547'],
  ['Mahogany', '#854F38'], ['Deep Cocoa', '#4B3022'], ['Rich Ebony', '#462B1B'],
];
const BASE_COLORS = [
  ['Blush Pink','#FADADD'],['Cream White','#FFFDF9'],['Rose Pink','#FF8FA3'],['Soft Sage','#D8E2DC'],['Warm Milk','#F8EDEB'],['Mint Green','#95D5B2'],['Pastel Lilac','#E2D1F9'],['Pure White','#FFFFFF'],['Periwinkle','#B892FF'],['Peach Cream','#FFD6BA'],['Ivory','#FFF1E6'],['Apricot','#FF9F68'],['Milk Chocolate','#8D6E63'],['Oat Milk','#EFEBE9'],['Dusty Pink','#F4ACB7'],['Warm Cream','#F5EBE0'],['Light Tan','#D5BDAF'],['Coffee Brown','#6F4E37'],['Golden Honey','#E6B86A'],['Buttercream','#FFF8E7'],['Caramel','#8C5319'],['Warm Terracotta','#D97742'],['Vanilla','#FAF0CA'],['Espresso','#582F0E'],['Deep Navy','#1B1F3B'],['Royal Indigo','#4A55A2'],['Star Gold','#FFE66D'],['Ice Blue','#CAF0F8'],['Snow','#FFFFFF'],['Electric Cyan','#00B4D8'],['Dark Plum','#3C096C'],['Violet','#7B2CBF'],['Nebula Pink','#FF70A6'],['Seafoam Teal','#84A59D'],['Pearl Sand','#F7EDE2'],['Coral','#F28482'],['Obsidian','#1A1A2E'],['Midnight Slate','#16213E'],['Neon Mint','#00F5D4'],['Jet Black','#18181B'],['Charcoal','#3F3F46'],['Crimson Red','#E11D48'],['Dark Violet-Gray','#22223B'],['Muted Slate','#4A4E69'],['Acid Lime','#AACC00'],['Matte Black','#121212'],['Metallic Gold','#F59E0B'],
];
function colorMetrics(hex) { const n=parseInt(hex.slice(1),16), r=((n>>16)&255)/255,g=((n>>8)&255)/255,b=(n&255)/255, max=Math.max(r,g,b),min=Math.min(r,g,b),d=max-min; let h=0; if(d){if(max===r)h=((g-b)/d)%6;else if(max===g)h=(b-r)/d+2;else h=(r-g)/d+4;} return {h:(h*60+360)%360,l:(max+min)/2}; }
const SORTED_BASE_COLORS = [...BASE_COLORS].sort((a,b)=>colorMetrics(a[1]).h-colorMetrics(b[1]).h || colorMetrics(b[1]).l-colorMetrics(a[1]).l);
function normalizePartSet(defaults, savedParts, keys = PART_KEYS) {
  const safeSavedParts = savedParts && typeof savedParts === 'object' ? savedParts : {};
  return keys.reduce((parts, key) => {
    const value = safeSavedParts[key];
    parts[key] = typeof value === 'string' ? value : defaults[key];
    return parts;
  }, { ...defaults });
}

function normalizeAvatarSelections(savedSelections) {
  const normalized = { ...AVATAR_DEFAULT_SELECTIONS };
  if (!savedSelections || typeof savedSelections !== 'object') return normalized;
  const allowedIds = new Set(AVATAR_COSMETIC_CATALOG.map((item) => item.id));
  AVATAR_CATEGORIES.forEach(({ label }) => {
    if (Object.prototype.hasOwnProperty.call(savedSelections, label)) {
      const savedId = savedSelections[label];
      normalized[label] = savedId === '' || allowedIds.has(savedId) ? savedId : normalized[label];
    }
  });
  return normalized;
}



export default function PetScreen({ soundEnabled = true }) {
  const [pet, setPet] = useState(null);
  const [loading, setLoading] = useState(true);
  const [actionPending, setActionPending] = useState(null);
  const [cardResult, setCardResult] = useState(null);
  const [cardStage, setCardStage] = useState(null);
  const [selectedItem, setSelectedItem] = useState(null);
  const [purchasePending, setPurchasePending] = useState(false);
  const [purchaseSuccess, setPurchaseSuccess] = useState(false);
  const [purchaseSuccessClosing, setPurchaseSuccessClosing] = useState(false);
  const [successItem, setSuccessItem] = useState(null);
  const [purchaseConfirmOpen, setPurchaseConfirmOpen] = useState(false);
  const [pointsNotice, setPointsNotice] = useState(null);
  const [pointsNoticeClosing, setPointsNoticeClosing] = useState(false);
  const [activeTab, setActiveTab] = useState('shop');
  const [centerOpen, setCenterOpen] = useState(false);
  const [pointsOnlyOpen, setPointsOnlyOpen] = useState(false);
  const [drawerDragY, setDrawerDragY] = useState(0);
  const [drawerTop, setDrawerTop] = useState(null);
  const [drawerDragging, setDrawerDragging] = useState(false);
  const [customizationTarget, setCustomizationTarget] = useState(null);
  const [customizeMenuOpen, setCustomizeMenuOpen] = useState(false);
  const [actionButtonStyle, setActionButtonStyle] = useState('square');
  const [activeLayer, setActiveLayer] = useState('body');
  const [petParts, setPetParts] = useState(PET_DEFAULT_PARTS);
  const [petItemSelections, setPetItemSelections] = useState({});
  const [avatarParts, setAvatarParts] = useState(AVATAR_DEFAULT_PARTS);
  const [avatarItemSelections, setAvatarItemSelections] = useState({});
  const [customizationSaved, setCustomizationSaved] = useState(false);
  const [petAction, setPetAction] = useState(null);
  const [error, setError] = useState(null);
  const drawTimerRef = useRef(null);
  const drawerDragRef = useRef(null);
  const ignoreDrawerClickRef = useRef(false);
  const customizationMenuRef = useRef(null);
  const customizationMenuTriggerRef = useRef(null);
  const statusRef = useRef(null);
  const drawerTopFrameRef = useRef(null);
  const pointsNoticeTimerRef = useRef(null);
  const idleRecoveryTimerRef = useRef(null);

  useEffect(() => () => {
    window.clearTimeout(idleRecoveryTimerRef.current);
  }, []);

  function schedulePetIdleRecovery() {
    window.clearTimeout(idleRecoveryTimerRef.current);
    idleRecoveryTimerRef.current = window.setTimeout(() => {
      // Recovery clears the over-care lock only; it must never raise mood.
      PetGameAPI.restPet().catch(() => {});
    }, PET_IDLE_RECOVERY_MS);
  }

  function requestPurchase() {
    if (!selectedItem || purchasePending) return;
    setPurchaseConfirmOpen(true);
  }

  function toggleCenterDrawer() {
    setCenterOpen((open) => !open);
    playSoundCue('drawer', soundEnabled);
  }

  function handleDrawerPointerDown(event) {
    drawerDragRef.current = { pointerId: event.pointerId, startY: event.clientY, moved: false };
    event.currentTarget.setPointerCapture(event.pointerId);
  }

  function handleDrawerPointerMove(event) {
    const drag = drawerDragRef.current;
    if (drag?.pointerId !== event.pointerId) return;
    const delta = event.clientY - drag.startY;
    if (Math.abs(delta) > 10) {
      drag.moved = true;
      setDrawerDragging(true);
    }
    const limit = centerOpen ? 170 : 90;
    const clamped = centerOpen
      ? Math.max(0, Math.min(limit, delta))
      : Math.min(0, Math.max(-limit, delta));
    setDrawerDragY(clamped);
  }

  function handleDrawerPointerUp(event) {
    const drag = drawerDragRef.current;
    if (drag?.pointerId !== event.pointerId) return;
    const delta = event.clientY - drag.startY;
    if (drag.moved) {
      const nextOpen = centerOpen ? delta < 44 : delta < -44;
      if (nextOpen !== centerOpen) playSoundCue('drawer', soundEnabled);
      setCenterOpen(nextOpen);
      ignoreDrawerClickRef.current = true;
    }
    drawerDragRef.current = null;
    setDrawerDragging(false);
    setDrawerDragY(0);
  }

  function handleDrawerPointerCancel() {
    drawerDragRef.current = null;
    setDrawerDragging(false);
    setDrawerDragY(0);
  }

  function selectCenterTab(tab) {
    setActiveTab(tab);
    playSoundCue('tab', soundEnabled);
  }

  useEffect(() => {
    if (!centerOpen || customizationTarget) {
      setDrawerTop(null);
      return undefined;
    }

    const statusNode = statusRef.current;
    if (!statusNode) return undefined;

    const updateDrawerTop = () => {
      if (drawerTopFrameRef.current) window.cancelAnimationFrame(drawerTopFrameRef.current);
      drawerTopFrameRef.current = window.requestAnimationFrame(() => {
        const statusRect = statusNode.getBoundingClientRect();
        // Keep a breathing gap below the expanded points row. Never clamp
        // the drawer upward over points; its bottom remains pinned above nav.
        setDrawerTop(Math.round(Math.max(88, statusRect.bottom + 14)));
      });
    };

    updateDrawerTop();
    const observer = typeof ResizeObserver !== 'undefined' ? new ResizeObserver(updateDrawerTop) : null;
    observer?.observe(statusNode);
    window.addEventListener('resize', updateDrawerTop);
    window.addEventListener('orientationchange', updateDrawerTop);
    return () => {
      if (drawerTopFrameRef.current) window.cancelAnimationFrame(drawerTopFrameRef.current);
      observer?.disconnect();
      window.removeEventListener('resize', updateDrawerTop);
      window.removeEventListener('orientationchange', updateDrawerTop);
    };
  }, [centerOpen, customizationTarget]);

  useEffect(() => {
    if (!pointsNotice) return undefined;
    window.clearTimeout(pointsNoticeTimerRef.current);
    setPointsNoticeClosing(false);
    pointsNoticeTimerRef.current = window.setTimeout(() => {
      setPointsNoticeClosing(true);
      pointsNoticeTimerRef.current = window.setTimeout(() => {
        setPointsNotice(null);
        setPointsNoticeClosing(false);
      }, 420);
    }, 3700);
    return () => window.clearTimeout(pointsNoticeTimerRef.current);
  }, [pointsNotice]);

  // Mood must change only through explicit care actions. Do not call restPet
  // on an idle timer: the REST endpoint increases mood and caused passive mood gain.


  useEffect(() => {
    if (centerOpen) setCustomizeMenuOpen(false);
  }, [centerOpen]);

  useEffect(() => {
    if (!customizeMenuOpen) return undefined;

    function closeMenu(event) {
      if (event.type === 'keydown' && event.key === 'Escape') {
        setCustomizeMenuOpen(false);
        customizationMenuTriggerRef.current?.focus();
      } else if (
        event.type === 'pointerdown' &&
        !customizationMenuRef.current?.contains(event.target)
      ) {
        setCustomizeMenuOpen(false);
      }
    }

    document.addEventListener('pointerdown', closeMenu);
    document.addEventListener('keydown', closeMenu);
    return () => {
      document.removeEventListener('pointerdown', closeMenu);
      document.removeEventListener('keydown', closeMenu);
    };
  }, [customizeMenuOpen]);

  useEffect(() => {
    PetGameAPI.getPetState()
      .then((result) => {
        setPet(result);
        setPetParts(normalizePartSet(PET_DEFAULT_PARTS, result.custom_parts));
        setPetItemSelections(
          result.custom_parts?.shop_items && typeof result.custom_parts.shop_items === 'object'
            ? result.custom_parts.shop_items
            : {},
        );
        setAvatarParts(normalizePartSet(AVATAR_DEFAULT_PARTS, result.avatar_parts, AVATAR_PART_KEYS));
        setAvatarItemSelections(normalizeAvatarSelections(result.avatar_parts?.shop_items));
        if (result.card_drawn_today && result.todays_card) {
          setCardResult(result.todays_card);
        }
      })
      .catch((e) => setError(e.message))
      .finally(() => setLoading(false));

    return () => window.clearTimeout(drawTimerRef.current);
  }, []);

  useLiveRefresh(async () => {
    try {
      const result = await PetGameAPI.getPetState();
      setPet(result);
      setError(null);
    } catch (refreshError) {
      setError(refreshError.message);
    }
  });

  async function handleCareAction(action) {
    window.clearTimeout(idleRecoveryTimerRef.current);
    setActionPending(action);
    setPetAction(action);
    setError(null);
    playSoundCue('care', soundEnabled);
    try {
      const result = await PetGameAPI.doCareAction(action);
      setPet((prev) => ({ ...prev, ...result }));
      schedulePetIdleRecovery();
      if (result.overcare_penalty) {
        setPetAction('overcare');
        setPointsNotice(`-${result.overcare_penalty} LP · -${result.overcare_penalty} Attendance`);
        window.setTimeout(() => setPetAction(null), 900);
      } else {
        window.setTimeout(() => setPetAction(null), 650);
      }

    } catch (e) {
      const exhausted = /not enough points/i.test(e.message);
      setPointsNotice(exhausted ? 'You ran out of points. Make sure you work and play harder to gain more points.' : null);
      setError(exhausted ? null : e.message);
      setPetAction(null);
    } finally {
      setActionPending(null);
    }
  }

  async function handleDevPoints() {
    if (!DEV_MODE) return;
    try {
      const result = await PetGameAPI.addDevPoints(100);
      setPet((previous) => ({ ...previous, ...result }));
      playSoundCue('points-count-up', soundEnabled);
    } catch (devError) {
      setError(devError.message);
    }
  }

  async function handleDevReset() {
    if (!DEV_MODE) return;
    try {
      const result = await PetGameAPI.resetDevState();
      setPet((previous) => ({ ...previous, ...result }));
      setCardResult(null);
      setCardStage(null);
      setSelectedItem(null);
      setPurchaseConfirmOpen(false);
      setError(null);
      window.dispatchEvent(new CustomEvent('petgame-dev-state-reset'));
    } catch (devError) {
      setError(devError.message);
    }
  }

  function handleDevCardPreview(cardType) {
    if (!DEV_MODE) return;
    const previews = {
      quote: {
        card_type: 'quote',
        points_awarded: 0,
        quote: 'Keep going — your next win is close.',
      },
      bonus: {
        card_type: 'bonus',
        points_awarded: 20,
        quote: null,
      },
      tribute: {
        card_type: 'tribute',
        points_awarded: 0,
        quote: TRIBUTE_CARD_TEXT,
      },
    };
    window.clearTimeout(drawTimerRef.current);
    setCardResult(previews[cardType]);
    setCardStage('pulling');
    drawTimerRef.current = window.setTimeout(() => setCardStage('revealed'), 1200);
  }

  function handleOpenCard() {
    if (pet.card_drawn_today || actionPending) return;
    setCardStage('ready');
  }

  async function handleDrawCard() {
    if (pet.card_drawn_today || actionPending) return;
    setActionPending('card');
    setError(null);
    setCardStage('pulling');
    playSoundCue('card-start', soundEnabled);
    try {
      const [result] = await Promise.all([
        PetGameAPI.drawCard(),
        new Promise((resolve) => window.setTimeout(resolve, 1800)),
      ]);
      setCardResult(result);
      setPet((prev) => ({
        ...prev,
        lifetime_points: result.lifetime_points,
        card_drawn_today: true,
      }));
      drawTimerRef.current = window.setTimeout(() => {
        setCardStage('revealed');
        playSoundCue(
          result.card_type === 'tribute'
            ? 'card-reveal-tribute'
            : result.card_type === 'bonus'
              ? 'card-reveal-bonus'
              : 'card-reveal-quote',
          soundEnabled,
        );
      }, 180);
    } catch (e) {
      setError(e.message);
      setCardStage(null);
    } finally {
      setActionPending(null);
    }

  }

  async function handlePurchaseItem() {
    if (!selectedItem || purchasePending) return;
    setPurchasePending(true);
    setError(null);
    try {
      const result = await PetGameAPI.purchaseItem(selectedItem.id, selectedItem.price, selectedItem.currency);
      setPet((previous) => ({ ...previous, ...result }));
      setSuccessItem(selectedItem);
      setSelectedItem(null);
      setPurchaseSuccessClosing(false);
      setPurchaseSuccess(true);
      window.setTimeout(() => {
        setPurchaseSuccessClosing(true);
        window.setTimeout(() => {
          setPurchaseSuccess(false);
          setPurchaseSuccessClosing(false);
          setSuccessItem(null);
        }, 1050);
      }, 2400);
    } catch (purchaseError) {
      const exhausted = /not enough/i.test(purchaseError.message);
      setPointsNotice(exhausted ? 'You ran out of points. Make sure you work and play harder to gain more points.' : null);
      setError(exhausted ? null : purchaseError.message);
    } finally {
      setPurchasePending(false);
    }
  }

  async function handleSaveCustomization() {
    setActionPending('customize');
    setError(null);
    try {
      const parts = customizationTarget === 'avatar'
        ? { ...avatarParts, shop_items: { ...avatarItemSelections } }
        : { ...petParts, shop_items: { ...petItemSelections } };
      const result = await PetGameAPI.saveCustomization(customizationTarget, parts);
      if (customizationTarget === 'avatar') {
        setPet((previous) => ({ ...previous, avatar_parts: result.avatar_parts }));
        setAvatarItemSelections(result.avatar_parts?.shop_items || avatarItemSelections);
      } else {
        setPet((previous) => ({ ...previous, ...result }));
      }
      setCustomizationSaved(true);
      playSoundCue('save', soundEnabled);
      // Return to the pet room as soon as the selected look is safely saved.
      setCustomizationTarget(null);
      setCustomizationSaved(false);
    } catch (e) {
      setError(e.message);
    } finally {
      setActionPending(null);
    }
  }

  async function handleSavePetName(petName) {
    setActionPending('pet-name');
    setError(null);
    try {
      const result = await PetGameAPI.savePetName(petName);
      setPet((previous) => ({ ...previous, pet_name: result.pet_name }));
      return true;
    } catch (saveError) {
      setError(saveError.message);
      return false;
    } finally {
      setActionPending(null);
    }
  }

  if (loading) return <div className="petgame-loading">Loading your pet…</div>;
  if (!pet) return <div className="petgame-error">Couldn&apos;t load pet data. {error}</div>;

  const moodMeter = Math.max(0, Math.min(100, Number(pet.mood_meter) || 0));
  const moodState = moodMeter < 30 ? 'sad' : moodMeter < 80 ? 'neutral' : 'happy';
  const moodLabel = MOOD_LABEL[moodState];
  const baseShopItems = Array.isArray(pet.shop_items) ? pet.shop_items : [];
  // Keep the sample SVG available in Vite's local mock preview only. Real
  // catalog entries (without devOnly: true) remain available in production.
  const availableCosmeticCatalog = DEV_MODE
    ? COSMETIC_CATALOG
    : COSMETIC_CATALOG.filter((item) => !item.devOnly);
  // Keep avatar inventory separate by target while sharing the asset resolver.
  const shopItems = mergeCosmeticCatalog(
    baseShopItems,
    [...availableCosmeticCatalog, ...AVATAR_COSMETIC_CATALOG],
  );
  const goodieItems = Array.isArray(pet.goodie_items) ? pet.goodie_items : [];
  const ownedItemIds = Array.from(new Set([
    ...(Array.isArray(pet.owned_item_ids) ? pet.owned_item_ids : []),
    ...getDefaultOwnedCosmeticIds('pet'),
    ...getDefaultOwnedCosmeticIds('avatar'),
    ...AVATAR_COSMETIC_CATALOG.filter((item) => item.defaultUnlocked).map((item) => item.id),
  ]));
  const openItem = (item) => {
    setPurchaseConfirmOpen(false);
    setSelectedItem(item);
  };
  const renderItem = (item, index, type) => {
    const owned = ownedItemIds.includes(item.id);
    const isSpecial = item.rarity === 'special' || item.rarity === 'rare';
    const assetName = type === 'shop' ? 'shopItems' : 'goodieItems';
    return (
      <button
        className={`petgame-${type === 'shop' ? 'shop-item' : 'goodie-slot'}${owned ? ' is-owned' : ''}${isSpecial ? ' is-special' : ''}`}
        key={item.id ?? index}
        type="button"
        aria-label={`${owned ? 'View' : 'Unlock'} ${item.name || `${type} slot ${index + 1}`}`}
        onClick={() => openItem(item)}
      >
        {getCosmeticAssetUrl(item) ? <img src={getCosmeticAssetUrl(item)} alt={item.name || ''} /> : getGameAsset(assetName, item.assetIndex ?? index)
          ? <img src={getGameAsset(assetName, item.assetIndex ?? index)} alt="" />
          : <Icon name="star" size={17} />}
        {!owned && <span className="petgame-item-lock"><Icon name="lock" size={14} /></span>}
        {owned && <span className="petgame-item-owned" aria-label="Purchased"><Icon name="check" size={13} /></span>}
        {isSpecial && <span className="petgame-item-rarity">Special</span>}
      </button>
    );
  };

  function renderCenterDrawer() {
    return (
      <section
        className={`petgame-center${centerOpen ? ' is-open' : ''} is-portaled${drawerDragging ? ' is-dragging' : ''}`}
        id="petgame-shop-center"
        style={{
          top: centerOpen
            ? `${Math.max(0, (drawerTop ?? 220) + (drawerDragging ? drawerDragY : 0))}px`
            : `calc(100dvh - var(--petgame-bottom-nav-height, 64px) - env(safe-area-inset-bottom, 0px) - var(--petgame-drawer-closed-height, 84px)${drawerDragY ? ` ${drawerDragY < 0 ? '-' : '+'} ${Math.abs(drawerDragY)}px` : ''})`,
          transform: 'translateX(-50%)',
        }}
        aria-label="Shop and Goodie Center"
      >
        <button
          className="petgame-drawer-grab"
          type="button"
          aria-expanded={centerOpen}
          aria-label={centerOpen ? 'Close Shop and Goodie Center' : 'Open Shop and Goodie Center'}
          onClick={() => {
            if (ignoreDrawerClickRef.current) {
              ignoreDrawerClickRef.current = false;
              return;
            }
            toggleCenterDrawer();
          }}
          onPointerDown={handleDrawerPointerDown}
          onPointerMove={handleDrawerPointerMove}
          onPointerUp={handleDrawerPointerUp}
          onPointerCancel={handleDrawerPointerCancel}
        >
          <span className="petgame-drawer-handle" aria-hidden="true" />
        </button>
        <button
          className="petgame-center-drawer-toggle"
          type="button"
          aria-expanded={centerOpen}
          onClick={() => {
            if (ignoreDrawerClickRef.current) {
              ignoreDrawerClickRef.current = false;
              return;
            }
            toggleCenterDrawer();
          }}
          onPointerDown={handleDrawerPointerDown}
          onPointerMove={handleDrawerPointerMove}
          onPointerUp={handleDrawerPointerUp}
          onPointerCancel={handleDrawerPointerCancel}
        >
          <span className="petgame-center-drawer-title">
            <strong>Shop &amp; Goodie Center</strong>
            <small>{centerOpen ? 'Swipe down or tap to close' : 'Swipe up or tap to explore'}</small>
          </span>
          <Icon name="arrow" size={18} />
        </button>

        <div className="petgame-center-drawer" aria-hidden={!centerOpen}>
            <div className="petgame-center-tabs" role="tablist" aria-label="Pet center">
              <button
                id="petgame-tab-shop"
                className={activeTab === 'shop' ? 'active' : ''}
                type="button"
                role="tab"
                aria-selected={activeTab === 'shop'}
                aria-controls="petgame-center-panel"
                onClick={() => selectCenterTab('shop')}
              >
                Shop
              </button>
              <span className="petgame-tab-divider" aria-hidden="true" />
              <button
                id="petgame-tab-goodies"
                className={activeTab === 'goodies' ? 'active' : ''}
                type="button"
                role="tab"
                aria-selected={activeTab === 'goodies'}
                aria-controls="petgame-center-panel"
                onClick={() => selectCenterTab('goodies')}
              >
                Goodie Center
              </button>
            </div>

            <div
              className="petgame-center-panel"
              id="petgame-center-panel"
              role="tabpanel"
              aria-labelledby={activeTab === 'shop' ? 'petgame-tab-shop' : 'petgame-tab-goodies'}
            >
              {activeTab === 'shop' ? (
                <>
                  <p className="petgame-center-hint">A little something for your buddy</p>
                  <div className="petgame-shop-grid" aria-label="Shop items coming soon">
                    {SHOP_CATEGORIES.map((category) => (
                      <section className="petgame-goodie-section petgame-shop-section" key={category} aria-labelledby={`shop-category-${category}`}>
                        <div className="petgame-goodie-section-heading">
                          <h3 id={`shop-category-${category}`}>{category}</h3>
                          <span>Customize your companion</span>
                        </div>
                        <div className="petgame-shop-category-grid">
                          {normalItemsBeforeSpecial(shopItems.filter((item) => String(item.category || '').toLowerCase() === category.toLowerCase() && (item.target === 'pet' || item.target === 'both' || !item.target)))
                            .map((item, index) => renderItem(item, index, 'shop'))}
                          {!shopItems.some((item) => String(item.category || '').toLowerCase() === category.toLowerCase() && (item.target === 'pet' || item.target === 'both' || !item.target) && (item.rarity === 'special' || item.rarity === 'rare')) && (
                            <div className="petgame-shop-item is-special is-preview-only" aria-label={`${category} special tier preview`} title="Special tier preview">
                              <span className="petgame-item-rarity">Special</span>
                              <Icon name="star" size={22} />
                            </div>
                          )}
                        </div>
                      </section>
                    ))}
                  </div>
                  <p className="petgame-coming-soon">Cosmetics and rewards are being prepared.</p>
                </>
              ) : (
                <div className="petgame-goodie-sections">
                  <section className="petgame-goodie-section" aria-labelledby="petgame-cards-heading">
                    <div className="petgame-goodie-section-heading">
                      <h3 id="petgame-cards-heading">Cards</h3>
                      <span>Test your luck</span>
                    </div>
                    <div className={`petgame-goodie-card${cardResult ? ` has-card-result is-${cardResult.card_type}` : ''}`}>
                      <div className="petgame-goodie-art"><Icon name="card" size={24} /></div>
                      <div className="petgame-goodie-copy">
                        <strong>Today&apos;s card</strong>
                        <span>
                          {pet.card_unlocked
                            ? 'A small daily surprise, just for you.'
                            : 'Keep your attendance streak going to unlock a daily card.'}
                        </span>
                      </div>
                      {cardResult ? (
                        <div className={`petgame-card-result is-${cardResult.card_type}`} role="status">
                          <span className="petgame-card-result-icon"><Icon name={cardResult.card_type === 'bonus' ? 'points' : cardResult.card_type === 'tribute' ? 'star' : 'card'} size={16} /></span>
                          <div className="petgame-card-result-copy">
                            <strong>{cardResult.card_type === 'tribute' ? 'Tribute card' : cardResult.card_type === 'bonus' ? 'Special bonus' : 'Daily quote'}</strong>
                            <span>{cardResult.card_type === 'tribute'
                              ? 'In memory of Istivano — from Team Poli J'
                              : cardResult.card_type === 'bonus'
                                ? `Bonus card · +${cardResult.points_awarded} points`
                                : (cardResult.quote || 'A little reminder: small steps add up.')}</span>
                            <small>Drawn for today · {cardResult.card_type === 'bonus' ? 'Pet reward added' : cardResult.card_type === 'tribute' ? 'A special message' : 'Keep going'}</small>
                          </div>
                        </div>
                      ) : (
                        <button
                          className="petgame-draw-button"
                          type="button"
                          disabled={!pet.card_unlocked || Boolean(actionPending) || pet.card_drawn_today}
                          onClick={handleOpenCard}
                        >
                          {pet.card_drawn_today
                            ? 'Your draw is complete today'
                            : pet.card_unlocked
                              ? 'Reveal today’s card'
                              : 'Unlocks at day 3 streak'}
                        </button>
                      )}
                    </div>
                  </section>
                  <section className="petgame-goodie-section" aria-labelledby="petgame-goodies-heading">
                    <div className="petgame-goodie-section-heading">
                      <h3 id="petgame-goodies-heading">Goodies</h3>
                      <span>For you to enjoy</span>
                    </div>
                    <div className="petgame-goodie-slots" aria-label="Goodies coming soon">
                      {normalItemsBeforeSpecial(goodieItems).map((item, index) => renderItem(item, index, 'goodie'))}
                      {goodieItems.length === 0 && (
                        <div className="petgame-goodie-slot is-preview-only" aria-label="Goodie item preview" title="Goodie preview">
                          <Icon name="gift" size={22} />
                          <span className="petgame-preview-item-label">Goodie</span>
                        </div>
                      )}
                    </div>
                    <p className="petgame-coming-soon">Little treats are being prepared.</p>
                  </section>
                </div>
              )}
            </div>
        </div>
      </section>
    );
  }

  if (customizationTarget) {
    const editingAvatar = customizationTarget === 'avatar';
    return (
      <AvatarStudioScreen
        pet={pet}
        mood={moodState}
        target={customizationTarget}
        parts={editingAvatar ? avatarParts : petParts}
        shopItems={shopItems}
        ownedItemIds={ownedItemIds}
        selectedShopItems={editingAvatar ? avatarItemSelections : petItemSelections}
        cosmeticSelections={editingAvatar ? avatarItemSelections : petItemSelections}
        onCosmeticChange={(item) => {
          const target = editingAvatar ? 'avatar' : 'pet';
          const slot = getCosmeticSlot(item, target);
          const setter = editingAvatar ? setAvatarItemSelections : setPetItemSelections;
          setter((previous) => {
            const next = { ...previous };
            Object.entries(next).forEach(([category, itemId]) => {
              const existing = shopItems.find((candidate) => candidate.id === itemId);
              if (existing && getCosmeticSlot(existing, target) === slot) delete next[category];
            });
            if (item) next[item.category] = item.id;
            return next;
          });
          setCustomizationSaved(false);
          playSoundCue('select', soundEnabled);
        }}
        activeLayer={activeLayer}
        actionPending={actionPending}
        customizationSaved={customizationSaved}
        error={error}
        soundEnabled={soundEnabled}
        onSavePetName={handleSavePetName}
        onBack={() => setCustomizationTarget(null)}
        onLayerChange={(layer) => {
          setActiveLayer(layer);
          setCustomizationSaved(false);
          playSoundCue('select', soundEnabled);
        }}
        onPartChange={(value) => {
          if (!editingAvatar && value && typeof value === 'object' && value.baseColor) {
            setPetParts((previous) => ({ ...previous, baseColor: value.baseColor }));
          } else if (editingAvatar && value && typeof value === 'object' && (value.baseColor || value.hairColor)) {
            setAvatarParts((previous) => ({ ...previous, ...(value.baseColor ? { baseColor: value.baseColor } : {}), ...(value.hairColor ? { hairColor: value.hairColor } : {}) }));
          } else if (!editingAvatar && activeLayer.startsWith('shop:')) {
            const category = activeLayer.slice(5);
            setPetItemSelections((previous) => {
              const next = { ...previous };
              if (value) next[category] = value;
              else delete next[category];
              return next;
            });
          } else if (editingAvatar) {
            const avatarLayer = AVATAR_CATEGORIES.find((layer) => `avatar:${layer.label}` === activeLayer);
            setAvatarItemSelections((previous) => {
              const next = { ...previous };
              Object.entries(next).forEach(([category, itemId]) => {
                const existing = shopItems.find((candidate) => candidate.id === itemId);
                if (existing && avatarLayer && getCosmeticSlot(existing, 'avatar') === avatarLayer.slot) next[category] = '';
              });
              if (avatarLayer) next[avatarLayer.label] = '';
              return next;
            });
          } else {
            setPetParts((previous) => ({ ...previous, [activeLayer]: value }));
          }
          setCustomizationSaved(false);
          playSoundCue('select', soundEnabled);
        }}
        onSave={handleSaveCustomization}
      />
    );
  }

  return (
    <div className={`petgame-pet-view${centerOpen ? ' drawer-open' : ''} action-buttons-${actionButtonStyle}`}>
      {error && (
        <div className="petgame-banner-error" role="alert">
          {error}
        </div>
      )}
      {pointsNotice && (
        <div className={`petgame-points-notice${petAction === 'overcare' ? ' is-penalty' : ''}${pointsNoticeClosing ? ' is-closing' : ''}`} role="status" aria-live="polite">
          <Icon name={petAction === 'overcare' ? 'close' : 'points'} size={18} />
          <span>{pointsNotice}</span>
          <button type="button" aria-label="Dismiss points notification" onClick={() => setPointsNotice(null)}>×</button>
        </div>
      )}

      <section className={`petgame-pet-card${petAction ? ` is-reacting action-${petAction}` : ''}`} aria-label={`${pet.pet_name}, your pet`}>
        <div className="petgame-pet-card-header">
          <div className="petgame-pet-name">{pet.pet_name}</div>
          <div className="petgame-pet-header-actions">
            <button
              className="petgame-points-toggle"
              type="button"
              aria-label="View your points"
              title="View your points"
              aria-expanded={pointsOnlyOpen}
              onClick={() => {
                playSoundCue('points-count-up', soundEnabled);
                setPointsOnlyOpen(true);
              }}
            >
              <Icon name="points" size={19} />
            </button>
            <div className="petgame-customization-menu" ref={customizationMenuRef}>
              <button
                ref={customizationMenuTriggerRef}
                className="petgame-customization-menu-trigger"
                type="button"
                aria-label="Customize"
                aria-expanded={customizeMenuOpen}
                aria-controls="petgame-customization-menu"
                onClick={() => setCustomizeMenuOpen((open) => !open)}
              >
                <Icon name="hanger" size={19} />
              </button>
              {customizeMenuOpen && (
                <div
                  className="petgame-customization-menu-popover"
                  id="petgame-customization-menu"
                  role="group"
                  aria-label="Customization options"
                >
                  <button
                    className="petgame-customization-shortcut"
                    type="button"
                    onClick={() => {
                      setCustomizeMenuOpen(false);
                      setCustomizationTarget('pet');
                      setActiveLayer('shop:Hats');
                      setCustomizationSaved(false);
                      playSoundCue('select', soundEnabled);
                    }}
                  >
                    <Icon name="pet" size={19} />
                    <span>Customize pet</span>
                  </button>
                  <button
                    className="petgame-customization-shortcut"
                    type="button"
                    onClick={() => {
                      setCustomizeMenuOpen(false);
                      setCustomizationTarget('avatar');
                      setActiveLayer('avatar:Hats');
                      setCustomizationSaved(false);
                      playSoundCue('select', soundEnabled);
                    }}
                  >
                    <Icon name="body" size={19} />
                    <span>Customize avatar</span>
                  </button>
                  <button
                    className="petgame-action-style-toggle"
                    type="button"
                    aria-pressed={actionButtonStyle === 'circle'}
                    onClick={() => setActionButtonStyle((style) => style === 'square' ? 'circle' : 'square')}
                  >
                    <span className={`petgame-action-style-preview is-${actionButtonStyle}`} aria-hidden="true" />
                    <span>{actionButtonStyle === 'square' ? 'Use circle arcade buttons' : 'Use square arcade buttons'}</span>
                  </button>
                </div>
              )}
            </div>
          </div>
        </div>
        <section className="petgame-mood" aria-label={`Pet mood: ${moodLabel}`}>
          <div className="petgame-mood-heading">
            <span className={`petgame-mood-icon mood-${moodState}`} aria-hidden="true" />
            <span>{moodLabel}</span>
            <strong>{moodMeter}%</strong>
          </div>
          <div
            className="petgame-mood-track"
            role="progressbar"
            aria-label="Pet mood"
            aria-valuemin="0"
            aria-valuemax="100"
            aria-valuenow={moodMeter}
          >
            <div className="petgame-mood-fill" style={{ width: `${moodMeter}%` }} />
          </div>
        </section>
        <div
          className={`petgame-pet-stage mood-${moodState}`}
          style={getGameAsset('petBackground') ? { backgroundImage: `url("${getGameAsset('petBackground')}")` } : undefined}
        >

          <section ref={statusRef} className="petgame-status" aria-label="Your game points">
            <div className="petgame-status-card petgame-points-card">
              <span className="petgame-status-icon"><Icon name="points" size={17} /></span>
              <span className="petgame-status-copy">
                <span className="petgame-status-label">LP points</span>
                <strong><AnimatedNumber value={Number(pet.lifetime_points)} duration={950} /></strong>
              </span>
            </div>
            <div className="petgame-status-card petgame-attendance-points-card">
              <span className="petgame-status-icon"><Icon name="check" size={17} /></span>
              <span className="petgame-status-copy">
                <span className="petgame-status-label">Attendance pts</span>
                <strong><AnimatedNumber value={Number(pet.attendance_points || 0)} duration={950} /></strong>
                <small>{pet.streak_count} {pet.streak_count === 1 ? 'day' : 'days'} in a row</small>
              </span>
            </div>
          </section>

          <div className="petgame-sunshine" aria-hidden="true" />
          <PetArtwork mood={moodState} parts={petParts} cosmetics={petItemSelections} cosmeticItems={shopItems} />
          <span className="petgame-pet-caption">
            {pet.pet_type === 'mascot' ? 'Your mascot companion' : 'Your custom companion'}
          </span>
        </div>
      </section>

      <section className="petgame-care" aria-label="Take care of your pet">
        <h2 className="petgame-care-heading">Actions</h2>
        {CARE_ACTIONS.map(({ action, label, icon }) => (
          <button
            key={action}
            className="petgame-care-button"
            type="button"
            disabled={Boolean(actionPending)}
            onClick={() => handleCareAction(action)}
            aria-label={`${label} your pet`}
          >
            <span className="petgame-care-icon" aria-hidden="true">
              {getGameAsset('icons', `care-${icon}`) ? (
                <img src={getGameAsset('icons', `care-${icon}`)} alt="" />
              ) : (
                <CareIcon name={icon} />
              )}
            </span>
            <span>{actionPending === action ? '…' : label}</span>
          </button>
        ))}
      </section>

      {createPortal(renderCenterDrawer(), document.body)}

      {pointsOnlyOpen && (
        <PetPointsOverview pet={pet} onBack={() => setPointsOnlyOpen(false)} />
      )}

      {selectedItem && (
        <ItemPreviewOverlay
          item={selectedItem}
          owned={ownedItemIds.includes(selectedItem.id)}
          purchasePending={purchasePending}
          purchaseConfirmOpen={purchaseConfirmOpen}
          availablePoints={selectedItem.currency === 'attendance' ? Number(pet.attendance_points || 0) : Number(pet.lifetime_points || 0)}
          onPurchase={requestPurchase}
          onConfirmPurchase={async () => {
            setPurchaseConfirmOpen(false);
            await handlePurchaseItem();
          }}
          onCancelPurchase={() => setPurchaseConfirmOpen(false)}
          onClose={() => {
            if (!purchasePending) {
              setPurchaseConfirmOpen(false);
              setSelectedItem(null);
            }
          }}
        />
      )}
      {purchaseSuccess && successItem && (
        <div className={`petgame-purchase-success-overlay${purchaseSuccessClosing ? ' is-closing' : ''}`} role="status" aria-live="polite">
          <section className="petgame-purchase-success">
            <span className="petgame-purchase-success-mark"><Icon name="check" size={48} /></span>
            <div className="petgame-purchase-success-preview petgame-draw-card-front petgame-purchase-success-icon-card">
              {categoryIconSrc(successItem.category) ? <img src={categoryIconSrc(successItem.category)} alt="" /> : <Icon name={successItem.currency === 'attendance' ? 'gift' : 'star'} size={76} />}
              <span className="petgame-card-reward-ribbon">ITEM UNLOCKED</span>
            </div>
            <strong>{successItem.name}</strong>
            <h2>Purchased<br />Successfully</h2>
          </section>
        </div>
      )}

      {cardStage && (
        <div className="petgame-card-overlay" role="dialog" aria-modal="true" aria-labelledby="petgame-card-title">
          <div className={`petgame-card-scene stage-${cardStage}`}>
            <button
              className="petgame-card-close"
              type="button"
              aria-label="Close card reveal"
              disabled={cardStage === 'pulling'}
              onClick={() => setCardStage(null)}
            >
              <Icon name="close" size={20} />
            </button>
            <span className="petgame-eyebrow">A LITTLE MOMENT FOR YOU</span>
            <h2 id="petgame-card-title">
              {cardStage === 'revealed'
                ? 'Your card is here.'
                : cardStage === 'ready'
                  ? 'Your daily card is waiting.'
                  : 'A surprise is on its way.'}
            </h2>
            {cardStage === 'ready' ? (
              <button
                className="petgame-draw-card-trigger"
                type="button"
                aria-label="Tap your daily card to draw it"
                disabled={Boolean(actionPending)}
                onClick={handleDrawCard}
              >
                <DrawCard />
                <span>Tap your card to reveal it</span>
              </button>
            ) : (
              <>
                <DrawCard cardResult={cardResult} revealed={cardStage === 'revealed'} />
                {cardStage === 'pulling' && <span className="petgame-card-pull-caption">Hold on to the moment…</span>}
              </>
            )}
            {cardStage === 'revealed' && (
              <button className="petgame-card-done" type="button" onClick={() => setCardStage(null)}>
                Nice
                <Icon name="check" size={17} />
              </button>
            )}
          </div>
        </div>
      )}
      {DEV_MODE && (
        <div className="petgame-dev-controls" aria-label="Developer testing controls">
          <button type="button" onClick={handleDevPoints}>+100 LP</button>
          <button type="button" onClick={handleDevReset}>Reset test state</button>
          <button type="button" onClick={() => handleDevCardPreview('quote')}>Test Common</button>
          <button type="button" onClick={() => handleDevCardPreview('bonus')}>Test Special</button>
          <button type="button" onClick={() => handleDevCardPreview('tribute')}>Test Tribute</button>
        </div>
      )}
    </div>
  );
}

function ItemPreviewOverlay({ item, owned, purchasePending, purchaseConfirmOpen, availablePoints, onPurchase, onConfirmPurchase, onCancelPurchase, onClose }) {
  const currencyLabel = item.currency === 'attendance' ? 'Attendance' : 'LP';
  const canAfford = availablePoints >= Number(item.price || 0);
  return (
    <div className="petgame-item-overlay" role="dialog" aria-modal="true" aria-labelledby="petgame-item-title">
      <section className={`petgame-item-modal${item.rarity === 'special' || item.rarity === 'rare' ? ' is-special' : ''}${item.currency === 'attendance' ? ' is-goodie' : ''}`}>
          {(item.rarity === 'special' || item.rarity === 'rare') && item.currency !== 'attendance' && (
            <div className="petgame-item-card-pile" aria-hidden="true">
              <span className="petgame-item-card-pile-back" />
              <span className="petgame-item-card-pile-front" />
            </div>
          )}
          {(item.rarity === 'special' || item.rarity === 'rare') && item.currency !== 'attendance' && (
            <div className="petgame-item-front-surface" aria-hidden="true" />
          )}
          <button className="petgame-item-close" type="button" aria-label="Close item preview" onClick={onClose}>
            <Icon name="close" size={20} />
          </button>
          <div className={`petgame-item-preview${item.rarity === 'special' || item.rarity === 'rare' ? ' is-rare' : ''}`}>
            {getCosmeticAssetUrl(item) ? <img src={getCosmeticAssetUrl(item)} alt="" /> : getGameAsset(item.currency === 'attendance' ? 'goodieItems' : 'shopItems', item.assetIndex)
              ? <img src={getGameAsset(item.currency === 'attendance' ? 'goodieItems' : 'shopItems', item.assetIndex)} alt="" />
              : <Icon name="star" size={78} />}
          </div>
          <div className="petgame-item-details">
            <span className="petgame-eyebrow">{owned ? 'YOUR ITEM' : 'ITEM PREVIEW'}</span>
            <h2 id="petgame-item-title">{item.name}</h2>
            {(item.rarity === 'special' || item.rarity === 'rare') && <span className="petgame-item-rarity-label is-rare">special</span>}
            <p>{item.description}</p>
            {!owned && (
              <>
                <div className="petgame-item-price"><Icon name="points" size={19} /><strong>{item.price}</strong><span>{currencyLabel} points</span></div>
                <button className="petgame-item-buy" type="button" disabled={purchasePending || !canAfford} onClick={onPurchase}>
                  {purchasePending ? 'Unlocking…' : canAfford ? `Buy with ${currencyLabel} points` : `Not enough ${currencyLabel} points`}
                </button>
              </>
            )}
          </div>
          {purchaseConfirmOpen && (
            <div className="petgame-item-confirmation-overlay">
              <section className="petgame-item-confirmation" role="alertdialog" aria-modal="true" aria-labelledby="petgame-item-confirm-title">
                <span className="petgame-location-confirmation-mark"><Icon name="star" size={22} /></span>
                <span className="petgame-eyebrow">ITEM CHECK</span>
                <h2 id="petgame-item-confirm-title">Unlock this item?</h2>
                <p>You are about to spend <strong>{item.price} {currencyLabel} points</strong> on {item.name}.</p>
                <div className="petgame-location-confirmation-actions">
                  <button className="petgame-location-confirmation-positive" type="button" onClick={onConfirmPurchase}>Yes, unlock</button>
                  <button className="petgame-location-confirmation-back" type="button" onClick={onCancelPurchase}>Go back</button>
                </div>
              </section>
            </div>
          )}
        </section>
    </div>
  );
}


function PetPointsOverview({ pet, onBack }) {
  return (
    <div className="petgame-points-overlay" role="presentation" onClick={(event) => { if (event.target === event.currentTarget) onBack(); }}>
      <section className="petgame-points-modal" role="dialog" aria-modal="true" aria-labelledby="petgame-points-overview-title">
        <button className="petgame-points-modal-close" type="button" aria-label="Close points" onClick={onBack}>
          <Icon name="close" size={19} />
        </button>
        <header className="petgame-points-overview-header">
          <span className="petgame-eyebrow">YOUR PROGRESS</span>
          <h1 id="petgame-points-overview-title">Pet points</h1>
          <p>Every little step adds up.</p>
        </header>
        <div className="petgame-points-overview-grid">
          <article className="petgame-points-overview-card">
            <span className="petgame-points-overview-icon"><Icon name="points" size={29} /></span>
            <span className="petgame-points-overview-label">LP points</span>
            <strong><AnimatedNumber value={Number(pet.lifetime_points || 0)} duration={1100} /></strong>
          </article>
          <article className="petgame-points-overview-card">
            <span className="petgame-points-overview-icon"><Icon name="check" size={29} /></span>
            <span className="petgame-points-overview-label">Attendance points</span>
            <strong><AnimatedNumber value={Number(pet.attendance_points || 0)} duration={1100} /></strong>
            <small>{Number(pet.streak_count || 0)} {Number(pet.streak_count || 0) === 1 ? 'day' : 'days'} in a row</small>
          </article>
        </div>
      </section>
    </div>
  );
}

function AvatarStudioScreen({
  pet,
  mood,
  target,
  parts,
  activeLayer,
  actionPending,
  customizationSaved,
  error,
  onSavePetName,
  onBack,
  onLayerChange,
  onPartChange,
  onSave,
  shopItems = [],
  ownedItemIds = [],
  selectedShopItems = {},
  cosmeticSelections = {},
  onCosmeticChange = () => {},
}) {
  const [isEditingPetName, setIsEditingPetName] = useState(false);
  const [petNameDraft, setPetNameDraft] = useState(pet.pet_name);
  const [petNameSaved, setPetNameSaved] = useState(false);
  const editingAvatar = target === 'avatar';
  const petItemLayers = SHOP_CATEGORIES.map((category) => ({
    id: `shop:${category}`,
    label: category,
    items: shopItems.filter((item) => String(item.category || '').toLowerCase() === category.toLowerCase() && (item.target === 'pet' || item.target === 'both' || !item.target)),
  }));
  const avatarItemLayers = AVATAR_CATEGORIES.map((category) => ({
    id: `avatar:${category.label}`,
    label: category.label,
    slot: category.slot,
    items: shopItems.filter((item) => item.target === 'avatar' && item.category === category.label),
  }));
  const activePetLayer = petItemLayers.find((layer) => layer.id === activeLayer) || petItemLayers[0];
  const activeAvatarLayer = avatarItemLayers.find((layer) => layer.id === activeLayer) || avatarItemLayers[0];
  const activeLayerCosmetics = shopItems.filter((item) => (
    ownedItemIds.includes(item.id)
    && (item.target === (editingAvatar ? 'avatar' : 'pet') || item.target === 'both' || !item.target)
    && getCosmeticSlot(item, editingAvatar ? 'avatar' : 'pet') === (editingAvatar ? activeAvatarLayer.slot : getCosmeticSlot({ category: activePetLayer.label }, 'pet'))
    && Boolean(getCosmeticAssetUrl(item))
  ));
  const activeLayerOptions = editingAvatar
    ? [
      { id: `default:${activeAvatarLayer.label}`, name: 'None', category: activeAvatarLayer.label, isDefault: true },
      ...activeLayerCosmetics,
    ]
    : activeLayer === 'base-color'
      ? []
      : [
        { id: `default:${activePetLayer.label}`, name: 'None', category: activePetLayer.label, isDefault: true },
        ...activePetLayer.items.filter((item) => ownedItemIds.includes(item.id) && (item.target === 'pet' || item.target === 'both' || !item.target)),
      ];
  const previewName = editingAvatar ? 'Ilham' : pet.pet_name;

  async function savePetName() {
    if (!petNameDraft.trim()) return;
    const saved = await onSavePetName(petNameDraft.trim());
    if (saved) {
      setIsEditingPetName(false);
      setPetNameSaved(true);
    }
  }

  return (
    <section className={`petgame-customization-view ${editingAvatar ? 'is-avatar-studio' : 'is-pet-studio'}`} aria-labelledby="petgame-customization-title">
      <header className="petgame-customization-header">
        <button className="petgame-customization-back" type="button" onClick={onBack}>
          <Icon name="back" size={18} />
          <span>Back to pet room</span>
        </button>
        <div>
          <span className="petgame-eyebrow">{editingAvatar ? 'AVATAR STUDIO' : 'PET STUDIO'}</span>
          <h1 id="petgame-customization-title">{editingAvatar ? 'Customize your avatar' : `Customize ${pet.pet_name}`}</h1>
        </div>
      </header>

      {error && <div className="petgame-banner-error" role="alert">{error}</div>}

      {editingAvatar ? (
        <div className="petgame-name-field is-static" aria-label="Player name">
          <span className="petgame-name-text">Ilham</span>
        </div>
      ) : (
        <div className={`petgame-name-field${isEditingPetName ? ' is-editing' : ''}`}>
          {isEditingPetName ? (
            <form
              className="petgame-name-editor-form"
              onSubmit={(event) => {
                event.preventDefault();
                savePetName();
              }}
            >
              <input
                autoFocus
                maxLength={50}
                aria-label="New pet name"
                value={petNameDraft}
                onChange={(event) => {
                  setPetNameDraft(event.target.value);
                  setPetNameSaved(false);
                }}
                onKeyDown={(event) => {
                  if (event.key === 'Escape') {
                    setPetNameDraft(pet.pet_name);
                    setIsEditingPetName(false);
                  }
                }}
              />
              <button
                className="petgame-name-commit"
                type="button"
                aria-label="Save pet name"
                disabled={actionPending === 'pet-name'}
                onClick={savePetName}
              >
                <Icon name={actionPending === 'pet-name' ? 'clock' : 'check'} size={18} />
              </button>
            </form>
          ) : (
            <>
              <span className="petgame-name-text">{pet.pet_name}{petNameSaved ? ' · Saved' : ''}</span>
              <button
                className="petgame-name-commit"
                type="button"
                aria-label="Edit pet name"
                onClick={() => {
                  setPetNameDraft(pet.pet_name);
                  setPetNameSaved(false);
                  setIsEditingPetName(true);
                }}
              >
                <Icon name="edit" size={17} />
              </button>
            </>
          )}
        </div>
      )}

      <div className="petgame-customization-preview" aria-label={`Live ${editingAvatar ? 'avatar' : 'pet'} preview`}>
        {editingAvatar
          ? <AvatarArtwork mood={mood} parts={parts} cosmetics={cosmeticSelections} cosmeticItems={shopItems} />
          : <PetArtwork mood={mood} parts={parts} cosmetics={cosmeticSelections} cosmeticItems={shopItems} />}
        <span className="petgame-customization-preview-caption">Changes appear as you customize</span>
      </div>

      <div className="petgame-customizer">
        <div className="petgame-customizer-heading">
          <div>
            <span className="petgame-eyebrow">{editingAvatar ? 'YOUR AVATAR' : 'YOUR PET'}</span>
            <h2>{editingAvatar ? 'Make it feel like you.' : 'Build your pet’s look.'}</h2>
          </div>
          <span className="petgame-customizer-layer-count">{editingAvatar ? `${AVATAR_CATEGORIES.length} CATEGORIES` : `${PET_STUDIO_LAYERS.length} CATEGORIES`}</span>
        </div>
        <div className="petgame-layer-tabs" role="tablist" aria-label={`${editingAvatar ? 'Avatar' : 'Pet'} layers`}>
          {(editingAvatar ? avatarItemLayers : PET_STUDIO_LAYERS).map((layer) => (
            <button
              className={activeLayer === layer.id ? 'active' : ''}
              key={layer.id}
              type="button"
              role="tab"
              aria-selected={activeLayer === layer.id}
              onClick={() => onLayerChange(layer.id)}
            >
              {categoryIconSrc(layer.label) ? <img className="petgame-category-icon-art" src={categoryIconSrc(layer.label)} alt="" /> : <Icon name={SHOP_CATEGORY_ICONS[layer.label] || 'sparkles'} size={17} />}
              <span>{layer.label}</span>
            </button>
          ))}
        </div>
        {((!editingAvatar && activeLayer === 'base-color') || (editingAvatar && activeLayer === 'avatar:Skin Color')) ? (
          <section className="petgame-base-color-picker is-tab" aria-label={editingAvatar ? 'Avatar skin color' : 'Pet base color'}>
            <h3>{editingAvatar ? 'Choose your skin color' : 'Choose your pet&apos;s base color'}</h3>
            <div className="petgame-base-color-swatches">
              {(editingAvatar ? AVATAR_SKIN_COLORS : SORTED_BASE_COLORS).map(([name, color]) => {
                const selectedColor = (parts.baseColor || '#FEFEFE').toUpperCase();
                return <button key={`${name}-${color}`} type="button" className={`petgame-base-color-swatch${selectedColor === color ? ' selected' : ''}`} style={{'--swatch-color': color}} aria-label={`${name} ${color}`} title={name} aria-pressed={selectedColor === color} onClick={() => onPartChange({ baseColor: color })}><span /></button>;
              })}
            </div>
          </section>
        ) : (
          <>
            <div className="petgame-layer-options" role="group" aria-label={`Choose ${activeLayer}`}>
              {activeLayerOptions.map((item) => {
                const selected = item.isDefault
                  ? !activeLayerCosmetics.some((candidate) => cosmeticSelections[candidate.category] === candidate.id)
                  : cosmeticSelections[item.category] === item.id;
                const asset = getCosmeticAssetUrl(item);
                return (
                  <button
                    className={`petgame-layer-option petgame-shop-customization-option${selected ? ' selected' : ''}`}
                    key={item.id}
                    type="button"
                    aria-pressed={selected}
                    aria-label={item.isDefault ? `Remove ${editingAvatar ? activeAvatarLayer.label : activePetLayer.label}` : `Equip ${item.name}`}
                    onClick={() => item.isDefault ? onPartChange(null) : onCosmeticChange(item)}
                  >
                    <span className="petgame-option-swatch swatch-shop-item" aria-hidden="true">
                      {asset && <img src={asset} alt="" />}
                      {!asset && <Icon name="minus" size={18} />}
                    </span>
                    <span>{item.isDefault ? 'None' : item.name}</span>
                  </button>
                );
              })}
            </div>
            {editingAvatar && activeLayer === 'avatar:Hair' && (
              <section className="petgame-base-color-picker petgame-avatar-hair-color-picker" aria-label="Avatar hair color">
                <h3>Hair Color</h3>
                <div className="petgame-base-color-swatches">
                  {SORTED_BASE_COLORS.map(([name, color]) => {
                    const selectedColor = (parts.hairColor || '#394347').toUpperCase();
                    return <button key={`${name}-${color}`} type="button" className={`petgame-base-color-swatch${selectedColor === color ? ' selected' : ''}`} style={{'--swatch-color': color}} aria-label={`${name} ${color}`} title={name} aria-pressed={selectedColor === color} onClick={() => onPartChange({ hairColor: color })}><span /></button>;
                  })}
                </div>
              </section>
            )}
          </>
        )}
        <div className="petgame-customizer-footer">
          <span>{customizationSaved ? 'Your look is saved.' : `Preview updates instantly; save your ${editingAvatar ? 'avatar' : 'pet'} when it feels right.`}</span>
          <button
            className="petgame-customizer-save"
            type="button"
            disabled={actionPending === 'customize'}
            onClick={onSave}
          >
            {actionPending === 'customize' ? 'Saving…' : `Save ${editingAvatar ? 'avatar' : 'pet'}`}
            {actionPending !== 'customize' && <Icon name="check" size={16} />}
          </button>
        </div>
      </div>
    </section>
  );
}

function DrawCard({ cardResult, revealed = false }) {
  const rewardType = cardResult?.card_type || 'quote';
  return (
    <span className={`petgame-draw-card${revealed ? ' is-revealed' : ''} is-${rewardType}`}>
      <span className="petgame-draw-card-back">
        <span className="petgame-card-back-mark"><Icon name="star" size={35} /></span>
        <span>LET&apos;S PLAY</span>
        <small>DAILY CARD</small>
      </span>
      <span className="petgame-draw-card-front">
        <span className="petgame-card-reward-ribbon">{rewardType === 'bonus' ? 'SPECIAL BONUS' : rewardType === 'tribute' ? 'TRIBUTE' : 'DAILY QUOTE'}</span>
        <Icon name={rewardType === 'bonus' ? 'points' : rewardType === 'tribute' ? 'star' : 'card'} size={34} />
        {rewardType === 'tribute' ? (
          <>
            <strong>In memory of Istivano - CEO - Game Designer</strong>
            <span>“We'll Make You Proud, Mas”<br />from Team Poli J</span>
          </>
        ) : (
          <>
            <strong>{rewardType === 'bonus' ? `+${cardResult.points_awarded} points` : (cardResult?.quote || 'Small steps add up.')}</strong>
            <span>{rewardType === 'bonus' ? 'A little bonus for your journey.' : 'Consistency looks good on you.'}</span>
          </>
        )}
      </span>
      <span className="petgame-card-sheen" aria-hidden="true" />
    </span>
  );
}

function LayerPreview({ layer, value }) {
  return (
    <svg viewBox="0 0 48 48" aria-hidden="true">
      {layer === 'body' && <circle cx="24" cy="24" r="15" />}
      {layer === 'outfit' && <path d="m14 15-7 5 5 7 4-2v13h16V25l4 2 5-7-7-5-5 3h-10l-5-3Z" />}
      {layer === 'eyes' && <><circle cx="17" cy="22" r="3" /><circle cx="31" cy="22" r="3" /><path d={value === 'sleepy' ? 'M13 20q4-4 8 0m7 0q4-4 8 0' : 'M19 31h10'} /></>}
      {layer === 'hair' && (value === 'none' ? <path d="M11 28a13 13 0 0 1 26 0" /> : <path d="M10 28q-3-17 7-19 2 8 7 8 3-11 12-6 7 7 2 20-8-2-12-12-5 9-16 9Z" />)}
      {layer === 'accessory' && (value === 'none' ? <path d="M12 35h24" /> : <path d="m24 7 5 10 11 2-8 8 2 11-10-5-10 5 2-11-8-8 11-2 5-10Z" />)}
    </svg>
  );
}

function CareIcon({ name }) {
  if (name === 'feed') {
    return (
      <svg viewBox="0 0 32 32" focusable="false">
        <path d="M6 17h20l-2 8H8l-2-8Z" />
        <path d="M4 15h24M11 12c0-2 2-2 2-4m5 4c0-2 2-2 2-4" />
      </svg>
    );
  }
  if (name === 'play') {
    return (
      <svg viewBox="0 0 32 32" focusable="false">
        <path d="M16 27c-2.6 0-9-5.1-9-10.5a5 5 0 0 1 9-3 5 5 0 0 1 9 3C25 21.9 18.6 27 16 27Z" />
        <path d="M10 7h.01M16 5h.01M22 7h.01" />
      </svg>
    );
  }
  return (
    <svg viewBox="0 0 32 32" focusable="false">
      <path d="M10 25V14a2 2 0 0 1 4 0v5-9a2 2 0 0 1 4 0v9-7a2 2 0 0 1 4 0v8-4a2 2 0 0 1 4 0v6c0 4-3 6-7 6h-3c-3 0-5-1-6-3l-3-5a2 2 0 0 1 3-2l2 2" />
      <path d="M7 8a4 4 0 0 1 4-4m14 4a4 4 0 0 0-4-4" />
    </svg>
  );
}
