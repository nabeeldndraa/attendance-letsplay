// Every call from the React app to WordPress goes through this file.
// Notice there's no "login" step here — the user is already logged into
// WordPress (that's how they reached this page at all), and the nonce
// WordPress handed us via window.petgameConfig is what proves that to
// the REST API on every request.

const MOCK_MODE = !window.petgameConfig || window.petgameConfig.apiRoot.includes('mock');
const MOCK_STORAGE_KEY = 'petgame-dev-state-v7';
const TRIBUTE_CARD_TEXT = `In memory of Istivano - CEO - Game Designer — "We'll Make You Proud, Mas" from Team Poli J`;
const DAILY_CARD_QUOTES = [
  'Small steps add up.',
  'Consistency looks good on you.',
  'A little care goes a long way.',
  'You showed up for your buddy today.',
  'Keep going — your next win is close.',
];

function getLocalDateKey() {
  const date = new Date();
  return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
}
function getLocalWeekKey() {
  const date = new Date();
  const daysSinceMonday = (date.getDay() + 6) % 7;
  date.setDate(date.getDate() - daysSinceMonday);
  return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
}
const MOCK_DEFAULT_STATE = {
  pet_name: 'Momo',
  pet_type: 'mascot',
  mascot_id: 'default',
  custom_parts: null,
  avatar_parts: null,
  mood_meter: 48,
  mood_state: 'neutral',
  streak_count: 3,
  weekly_steps: 3,
  week_key: getLocalWeekKey(),
  lifetime_points: 1240,
  attendance_points: 426,
  card_unlocked: true,
  card_drawn_today: false,
  todays_card: null,
  card_drawn_date: null,
  today_checkin: null,
  owned_item_ids: [],
  good_mood_actions: 0,
  last_care_at: 0,
  rapid_care_actions: 0,
  pet_tired: false,
};

// --- Mock data, used only when running `npm run dev` without a real
// WordPress backend behind it. Mirrors the exact shape the real PHP
// endpoints return (see includes/class-petgame-rest-api.php).
let mockState = MOCK_MODE ? loadMockState() : { ...MOCK_DEFAULT_STATE };
const mockWeeklySteps = 3;
function loadMockState() {
  const stored = window.localStorage.getItem(MOCK_STORAGE_KEY);
  if (!stored) return { ...MOCK_DEFAULT_STATE };

  try {
    const saved = JSON.parse(stored);
    const hasDrawnToday = saved.card_drawn_date === getLocalDateKey();
    const savedLifetimePoints = Number(saved.lifetime_points);
    const safeSavedLifetimePoints = Number.isFinite(savedLifetimePoints) && savedLifetimePoints >= 0
      ? savedLifetimePoints
      : MOCK_DEFAULT_STATE.lifetime_points;
    return {
      ...MOCK_DEFAULT_STATE,
      ...saved,
      lifetime_points: safeSavedLifetimePoints,
      weekly_steps: saved.week_key ? (saved.week_key === getLocalWeekKey() ? Number(saved.weekly_steps || 0) : 0) : Number(saved.weekly_steps ?? MOCK_DEFAULT_STATE.weekly_steps),
      week_key: getLocalWeekKey(),
      mood_meter: Math.max(0, Math.min(100, Number(saved.mood_meter ?? MOCK_DEFAULT_STATE.mood_meter))),
      mood_state: saved.mood_state || MOCK_DEFAULT_STATE.mood_state,
      card_drawn_today: hasDrawnToday,
      todays_card: hasDrawnToday ? saved.todays_card : null,
      card_drawn_date: hasDrawnToday ? saved.card_drawn_date : null,
      today_checkin: saved.today_checkin?.checkin_date === getLocalDateKey() ? saved.today_checkin : null,
      owned_item_ids: Array.isArray(saved.owned_item_ids) ? saved.owned_item_ids : [],
      good_mood_actions: Number(saved.good_mood_actions) || 0,
      last_care_at: Number(saved.last_care_at) || 0,
      rapid_care_actions: Number(saved.rapid_care_actions) || 0,
      pet_tired: Boolean(saved.pet_tired),
    };
  } catch (error) {
    console.warn('Ignoring invalid saved Pet Game preview state.', error);
    return { ...MOCK_DEFAULT_STATE };
  }
}

function persistMockState() {
  window.localStorage.setItem(MOCK_STORAGE_KEY, JSON.stringify(mockState));
}

async function request(path, options = {}) {
  if (MOCK_MODE) {
    return mockRequest(path, options);
  }

  const res = await fetch(window.petgameConfig.apiRoot + path, {
    ...options,
    headers: {
      'Content-Type': 'application/json',
      'X-WP-Nonce': window.petgameConfig.nonce, // <-- this is the auth
      ...(options.headers || {}),
    },
  });

  if (!res.ok) {
    const body = await res.json().catch(() => ({}));
    throw new Error(body.message || `Request failed: ${res.status}`);
  }
  return res.json();
}

// Simulates the PHP logic client-side so the UI is fully clickable in
// local dev. This duplication is intentional and fine for a mock — it
// should NEVER be trusted as the real rules; includes/petgame-formulas.php
// is the single source of truth once this is wired to WordPress for real.
function mockRequest(path, options) {
  return new Promise((resolve, reject) => {
    setTimeout(() => {
      if (path === 'pet' && (!options.method || options.method === 'GET')) {
        return resolve({ ...mockState });
      }

      if (path === 'game/summary') {
        return resolve({
          lifetime_points: mockState.lifetime_points,
          weekly_steps: Number(mockState.weekly_steps ?? mockWeeklySteps),
          max_steps: 6,
          today_checkin: mockState.today_checkin || null,
          locations: [
            { id: 'kolektiv-space', name: 'Kolektiv Space', category: 'wfo' },
            { id: 'mcc', name: 'MCC', category: 'wfo' },
            { id: 'wfh', name: 'WFH', category: 'wfh' },
            { id: 'off-site', name: 'Off Site', category: 'offsite' },
            { id: 'leave', name: 'Sick or Other Leave', category: 'leave' },
          ],
        });
      }

      if (path === 'leaderboard') {
        return resolve({
          entries: [
            { rank: 1, user_id: 7, name: 'Alya', points: 1560, is_current_user: false },
            { rank: 2, user_id: 9, name: 'Rafi', points: 1410, is_current_user: false },
            { rank: 3, user_id: 1, name: 'You', points: mockState.lifetime_points, is_current_user: true },
            { rank: 4, user_id: 4, name: 'Nadia', points: 990, is_current_user: false },
            { rank: 5, user_id: 6, name: 'Dimas', points: 840, is_current_user: false },
            { rank: 6, user_id: 3, name: 'Mira', points: 720, is_current_user: false },
            { rank: 7, user_id: 8, name: 'Bima', points: 640, is_current_user: false },
            { rank: 8, user_id: 5, name: 'Tara', points: 580, is_current_user: false },
            { rank: 9, user_id: 10, name: 'Nino', points: 540, is_current_user: false },
            { rank: 10, user_id: 11, name: 'Salsa', points: 510, is_current_user: false },
            { rank: 11, user_id: 12, name: 'Fahri', points: 490, is_current_user: false },
            { rank: 12, user_id: 13, name: 'Citra', points: 470, is_current_user: false },
            { rank: 13, user_id: 14, name: 'Bagas', points: 450, is_current_user: false },
            { rank: 14, user_id: 15, name: 'Intan', points: 430, is_current_user: false },
            { rank: 15, user_id: 16, name: 'Reno', points: 410, is_current_user: false },
            { rank: 16, user_id: 17, name: 'Putri', points: 390, is_current_user: false },
            { rank: 17, user_id: 18, name: 'Gilang', points: 370, is_current_user: false },
            { rank: 18, user_id: 19, name: 'Laras', points: 350, is_current_user: false },
            { rank: 19, user_id: 20, name: 'Aditya', points: 330, is_current_user: false },
            { rank: 20, user_id: 21, name: 'Vina', points: 310, is_current_user: false },
            { rank: 21, user_id: 31, name: 'Faris', points: 290, is_current_user: false },
            { rank: 22, user_id: 32, name: 'Siti', points: 275, is_current_user: false },
            { rank: 23, user_id: 33, name: 'Adit', points: 260, is_current_user: false },
            { rank: 24, user_id: 34, name: 'Dewi', points: 245, is_current_user: false },
            { rank: 25, user_id: 35, name: 'Raka', points: 230, is_current_user: false },
            { rank: 26, user_id: 36, name: 'Naya', points: 215, is_current_user: false },
            { rank: 27, user_id: 37, name: 'Arman', points: 200, is_current_user: false },
            { rank: 28, user_id: 38, name: 'Lia', points: 185, is_current_user: false },
            { rank: 29, user_id: 39, name: 'Budi', points: 170, is_current_user: false },
            { rank: 30, user_id: 40, name: 'Tia', points: 155, is_current_user: false },
          ],
        });
      }

      if (path === 'pet/name') {
        const { pet_name } = JSON.parse(options.body);
        const cleanName = String(pet_name || '').trim().slice(0, 50);
        if (!cleanName) return reject(new Error('Enter a pet name before saving.'));
        mockState.pet_name = cleanName;
        persistMockState();
        return resolve({ pet_name: cleanName });
      }

      if (path === 'pet/customize') {
        const { target = 'pet', custom_parts } = JSON.parse(options.body);
        if (target === 'avatar') {
          mockState.avatar_parts = custom_parts;
          persistMockState();
          return resolve({ avatar_parts: custom_parts });
        }
        mockState.pet_type = 'custom';
        mockState.mascot_id = null;
        mockState.custom_parts = custom_parts;
        persistMockState();
        return resolve({
          pet_type: 'custom',
          mascot_id: null,
          custom_parts,
        });
      }

      if (path === 'pet/purchase') {
        const { item_id, cost = 0, currency = 'lp' } = JSON.parse(options.body);
        if (mockState.owned_item_ids.includes(item_id)) return resolve({ owned_item_ids: mockState.owned_item_ids, lifetime_points: mockState.lifetime_points, attendance_points: mockState.attendance_points });
        const balanceKey = currency === 'attendance' ? 'attendance_points' : 'lifetime_points';
        if (mockState[balanceKey] < cost) return reject(new Error(`Not enough ${currency === 'attendance' ? 'Attendance' : 'LP'} points for this item.`));
        mockState[balanceKey] -= cost;
        mockState.owned_item_ids = [...mockState.owned_item_ids, item_id];
        persistMockState();
        return resolve({ owned_item_ids: mockState.owned_item_ids, lifetime_points: mockState.lifetime_points, attendance_points: mockState.attendance_points });
      }

      if (path === 'pet/care') {
        const { action } = JSON.parse(options.body);
        const cost = { feed: 15, play: 10, clean: 5 }[action] ?? 0;
        if (mockState.lifetime_points < cost) {
          return reject(new Error('Not enough points for this action.'));
        }

        const now = Date.now();
        const previousMood = mockState.mood_meter;
        const secondsSinceLastAction = mockState.last_care_at ? (now - mockState.last_care_at) / 1000 : Infinity;
        const idleRecoveryReached = secondsSinceLastAction >= 30;
        if (idleRecoveryReached) {
          mockState.pet_tired = false;
          mockState.rapid_care_actions = 0;
        }
        mockState.rapid_care_actions = secondsSinceLastAction <= 1.1
          ? mockState.rapid_care_actions + 1
          : 0;
        mockState.last_care_at = now;
        const spamTriggered = mockState.rapid_care_actions >= 3;
        const isTired = (!idleRecoveryReached && mockState.pet_tired) || spamTriggered;
        if (spamTriggered) mockState.pet_tired = true;
        mockState.mood_meter = isTired
          ? (spamTriggered && previousMood >= 95 ? 60 : Math.max(0, previousMood - 8))
          : Math.min(100, mockState.mood_meter + 12);
        const lpReward = previousMood < 80 && mockState.mood_meter >= 80 ? 5 : 0;
        const overcare = isTired;
        if (lpReward) mockState.lifetime_points += lpReward;
        if (overcare) {
          mockState.lifetime_points = Math.max(0, mockState.lifetime_points - 5);
          mockState.attendance_points = Math.max(0, mockState.attendance_points - 5);
        }
        mockState.lifetime_points -= cost;
        mockState.mood_state =
          mockState.mood_meter <= 33 ? 'sad' : mockState.mood_meter <= 66 ? 'neutral' : 'happy';
        persistMockState();
        return resolve({
          mood_meter: mockState.mood_meter,
          mood_state: mockState.mood_state,
          lifetime_points: mockState.lifetime_points,
          attendance_points: mockState.attendance_points,
          lp_points_awarded: lpReward,
          overcare_penalty: overcare ? 5 : 0,
        });
      }

      if (path === 'pet/rest') {
        // Idle recovery clears over-care state without changing the mood meter.
        mockState.good_mood_actions = 0;
        mockState.last_care_at = 0;
        mockState.rapid_care_actions = 0;
        mockState.pet_tired = false;
        mockState.mood_state =
          mockState.mood_meter <= 33 ? 'sad' : mockState.mood_meter <= 66 ? 'neutral' : 'happy';
        persistMockState();
        return resolve({
          mood_meter: mockState.mood_meter,
          mood_state: mockState.mood_state,
        });
      }

      if (path === 'card/draw') {
        if (mockState.card_drawn_today) return reject(new Error('Come back tomorrow for your next draw.'));
        const unlocked = mockState.streak_count >= 3;
        const roll = Math.random();
        const isTribute = unlocked && roll < 0.01;
        const isBonus = unlocked && !isTribute && Math.random() < 0.15;
        const points = isBonus ? 20 : 0;
        const cardType = isTribute ? 'tribute' : isBonus ? 'bonus' : 'quote';
        const quote = isTribute
          ? TRIBUTE_CARD_TEXT
          : cardType === 'quote'
            ? DAILY_CARD_QUOTES[Math.floor(Math.random() * DAILY_CARD_QUOTES.length)]
            : null;
        mockState.lifetime_points += points;
        mockState.card_drawn_today = true;
        mockState.card_drawn_date = getLocalDateKey();
        mockState.todays_card = {
          card_type: cardType,
          points_awarded: points,
          quote,
        };
        persistMockState();
        return resolve({
          card_type: cardType,
          points_awarded: points,
          quote,
          lifetime_points: mockState.lifetime_points,
        });
      }

      if (path === 'dev/add-points') {
        const amount = Math.max(0, Number(JSON.parse(options.body || '{}').amount) || 0);
        mockState.lifetime_points += amount;
        persistMockState();
        return resolve({ lifetime_points: mockState.lifetime_points, attendance_points: mockState.attendance_points });
      }

      if (path === 'dev/reset-state') {
        mockState = { ...MOCK_DEFAULT_STATE };
        persistMockState();
        return resolve({
          ...mockState,
          shop_items: [],
          goodie_items: [],
          owned_item_ids: [],
        });
      }

      reject(new Error(`No mock handler for ${path}`));
    }, 300); // small delay so loading states are visible in dev too
  });
}

export const PetGameAPI = {
  getPetState: () => request('pet'),
  savePetName: (pet_name) => request('pet/name', { method: 'POST', body: JSON.stringify({ pet_name }) }),
  saveCustomization: (target, custom_parts) =>
    request('pet/customize', { method: 'POST', body: JSON.stringify({ target, custom_parts }) }),
  getGameSummary: () => request('game/summary'),
  getLeaderboard: () => request('leaderboard'),
  doCareAction: (action) =>
    request('pet/care', { method: 'POST', body: JSON.stringify({ action }) }),
  restPet: () => request('pet/rest', { method: 'POST' }),
  drawCard: () => request('card/draw', { method: 'POST' }),
  addDevPoints: (amount) =>
    request('dev/add-points', { method: 'POST', body: JSON.stringify({ amount }) }),
  resetDevState: () =>
    request('dev/reset-state', { method: 'POST' }),
  purchaseItem: (item_id, cost, currency = 'lp') =>
    request('pet/purchase', { method: 'POST', body: JSON.stringify({ item_id, cost, currency }) }),
  recordDemoAttendance: (checkin) => {
    if (!MOCK_MODE) return null;
    const isNewCheckinToday = Boolean(checkin?.checkin_date)
      && mockState.today_checkin?.checkin_date !== checkin.checkin_date;
    if (isNewCheckinToday) {
      if (mockState.week_key !== getLocalWeekKey()) {
        mockState.week_key = getLocalWeekKey();
        mockState.weekly_steps = 0;
      }
      const points = Math.max(0, Number(checkin.points_awarded) || 0);
      mockState.lifetime_points += points;
      mockState.attendance_points += points;
      if (checkin.attendance_type !== 'leave') {
        mockState.weekly_steps = Math.min(6, Number(mockState.weekly_steps ?? mockWeeklySteps) + 1);
      }
      mockState.today_checkin = checkin;
      mockState.today_checkin = { ...checkin, lp_points_awarded: lpReward };
      persistMockState();
    }
    return {
      lifetime_points: mockState.lifetime_points,
      attendance_points: mockState.attendance_points,
      weekly_steps: Number(mockState.weekly_steps ?? mockWeeklySteps),
      today_checkin: mockState.today_checkin,
      lp_points_awarded: Number(mockState.today_checkin?.lp_points_awarded || 0),
    };
  },
};
