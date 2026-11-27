import { useEffect, useRef, useState } from 'react';
import { getGameAsset } from '../assets';
import { PetGameAPI } from '../api';
import Icon from './Icon';
import AvatarArtwork from './AvatarArtwork';
import useLiveRefresh from '../hooks/useLiveRefresh';
import AnimatedNumber from './AnimatedNumber';
import { playSoundCue } from '../sound';
import { COSMETIC_CATALOG } from '../cosmetics/catalog';
import { AVATAR_COSMETIC_CATALOG } from '../cosmetics/avatarCatalog';

const AVATAR_RENDER_CATALOG = [...COSMETIC_CATALOG, ...AVATAR_COSMETIC_CATALOG];
const DEFAULT_AVATAR_PARTS = { body: 'cream', outfit: 'none', eyes: 'round', hair: 'none', accessory: 'none', baseColor: '#FEFEFE', hairColor: '#394347' };
const DEFAULT_AVATAR_COSMETICS = { Clothes: 'avatar-clothes-1', Eyes: 'avatar-eyes-1', Hair: 'avatar-hair-1' };

export default function LeaderboardScreen({ soundEnabled = true }) {
  const [entries, setEntries] = useState([]);
  const [currentAvatarParts, setCurrentAvatarParts] = useState(DEFAULT_AVATAR_PARTS);
  const [currentAvatarCosmetics, setCurrentAvatarCosmetics] = useState(DEFAULT_AVATAR_COSMETICS);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const previousRanksRef = useRef(new Map());
  const hasScheduledInitialRiseRef = useRef(false);
  const [rankMovements, setRankMovements] = useState({});
  const [showInitialUserRise, setShowInitialUserRise] = useState(true);
  const movementTimeoutRef = useRef(null);
  const initialRiseTimeoutRef = useRef(null);
  const rankSoundTimeoutsRef = useRef([]);
  const soundEnabledRef = useRef(soundEnabled);
  soundEnabledRef.current = soundEnabled;

  function playRankGainSounds(positionCount) {
    rankSoundTimeoutsRef.current.forEach(window.clearTimeout);
    rankSoundTimeoutsRef.current = Array.from(
      { length: positionCount },
      (_, index) => window.setTimeout(
        () => playSoundCue('rank-pop', soundEnabledRef.current),
        index * 145,
      ),
    );
  }

  async function loadLeaderboard({ quiet = false } = {}) {
    if (!quiet && entries.length === 0) setLoading(true);
    try {
      const [result, petState] = await Promise.all([
        PetGameAPI.getLeaderboard(),
        PetGameAPI.getPetState().catch(() => null),
      ]);
      if (petState?.avatar_parts && typeof petState.avatar_parts === 'object') {
        const { shop_items: savedCosmetics, ...savedAvatarParts } = petState.avatar_parts;
        setCurrentAvatarParts({ ...DEFAULT_AVATAR_PARTS, ...savedAvatarParts });
        setCurrentAvatarCosmetics({ ...DEFAULT_AVATAR_COSMETICS, ...(savedCosmetics && typeof savedCosmetics === 'object' ? savedCosmetics : {}) });
      }
      const displayedEntries = (Array.isArray(result.entries) ? result.entries : []).filter((entry) => Number(entry.rank) <= 30 || entry.is_current_user);
      const nextMovements = {};
      const currentUser = displayedEntries.find((entry) => entry.is_current_user);
      const previousOwnRank = currentUser
        ? previousRanksRef.current.get(currentUser.user_id)
        : undefined;
      if (currentUser && previousOwnRank === undefined && !hasScheduledInitialRiseRef.current) {
        hasScheduledInitialRiseRef.current = true;
        playRankGainSounds(Math.max(0, Math.min(30, Math.ceil(currentUser.rank / 10) * 10) - currentUser.rank));
      } else if (previousOwnRank !== undefined && currentUser && currentUser.rank < previousOwnRank) {
        playRankGainSounds(previousOwnRank - currentUser.rank);
      }
      displayedEntries.forEach((entry) => {
        const previousRank = previousRanksRef.current.get(entry.user_id);
        if (previousRank !== undefined && previousRank !== entry.rank) {
          nextMovements[entry.user_id] = entry.rank < previousRank ? 'rank-up' : 'rank-down';
        }
      });
      previousRanksRef.current = new Map(displayedEntries.map((entry) => [entry.user_id, entry.rank]));
      setEntries(displayedEntries);
      if (showInitialUserRise && displayedEntries.some((entry) => entry.is_current_user)) {
        window.clearTimeout(initialRiseTimeoutRef.current);
        initialRiseTimeoutRef.current = window.setTimeout(() => setShowInitialUserRise(false), 1200);
      }
      if (Object.keys(nextMovements).length > 0) {
        setRankMovements(nextMovements);
        window.clearTimeout(movementTimeoutRef.current);
        movementTimeoutRef.current = window.setTimeout(() => setRankMovements({}), 900);
      }
      setError(null);
    } catch (loadError) {
      setError(loadError.message);
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    loadLeaderboard();
    return () => {
      window.clearTimeout(movementTimeoutRef.current);
      window.clearTimeout(initialRiseTimeoutRef.current);
      rankSoundTimeoutsRef.current.forEach(window.clearTimeout);
    };
  }, []);

  useLiveRefresh(loadLeaderboard, 30000);

  const topThree = [2, 1, 3]
    .map((rank) => entries.find((entry) => entry.rank === rank))
    .filter(Boolean);

  return (
    <section className="petgame-rank-view" aria-labelledby="petgame-rank-title">
      <div className="petgame-section-heading">
        <div>
          <span className="petgame-eyebrow">THE COMMUNITY</span>
          <h1 id="petgame-rank-title">Leaderboard</h1>
        </div>
        <span className="petgame-rank-icon"><Icon name="rank" size={23} /></span>
      </div>

      <div
        className="petgame-rank-banner"
        style={getGameAsset('leaderboardBanner') ? { backgroundImage: `url("${getGameAsset('leaderboardBanner')}")` } : undefined}
      >
        <div>
          <span className="petgame-eyebrow">A LITTLE FRIENDLY COMPETITION</span>
          <strong>Check your leaderboard!</strong>
          <span>Every point brings you one step closer.</span>
        </div>
        {getGameAsset('leaderboardMascot') ? (
          <img className="petgame-banner-mascot" src={getGameAsset('leaderboardMascot')} alt="" />
        ) : (
          <div className="petgame-banner-art" aria-hidden="true">
            <Icon name="rank" size={38} />
          </div>
        )}
        <div className="petgame-rank-cheer" aria-hidden="true">
          <Icon name="rank" size={15} />
          <span>Keep showing up<br /><strong>you&apos;re doing great</strong></span>
        </div>
      </div>

      {error && (
        <div className="petgame-banner-error" role="alert">
          {error}
          <button type="button" onClick={loadLeaderboard}>Try again</button>
        </div>
      )}

      {loading ? (
        <div className="petgame-inline-loading">Loading the leaderboard…</div>
      ) : entries.length === 0 ? (
        <div className="petgame-setup-note">
          <span className="petgame-setup-icon"><Icon name="rank" size={21} /></span>
          <div>
            <strong>The leaderboard is getting started</strong>
            <p>Earn points from attendance to appear here.</p>
          </div>
        </div>
      ) : (
        <>
          <div className="petgame-rank-board">
            <div className="petgame-podium" aria-label="Top three">
              {topThree.map((entry) => (
                <div
                  className={`petgame-podium-player podium-${entry.rank}${entry.is_current_user ? ' is-you' : ''}${rankMovements[entry.user_id] ? ` ${rankMovements[entry.user_id]}` : ''}`}
                  key={entry.user_id}
                >
                  {entry.rank === 1 && (
                    <span className="petgame-podium-medal"><Icon name="rank" size={16} /></span>
                  )}
                  <div className={`petgame-player-avatar${entry.is_current_user ? ' is-custom-avatar' : ''}`} aria-hidden="true">
                    {entry.is_current_user ? (
                      <AvatarArtwork mood="neutral" parts={currentAvatarParts} cosmetics={currentAvatarCosmetics} cosmeticItems={AVATAR_RENDER_CATALOG} />
                    ) : getGameAsset('leaderboardPlayerAvatar') ? (
                      <img src={getGameAsset('leaderboardPlayerAvatar')} alt="" />
                    ) : <Icon name="rank" size={19} />}
                  </div>
                  <strong>{entry.name}</strong>
                  <span><AnimatedNumber value={entry.points} /> pts</span>
                  <div className="petgame-podium-block">{entry.rank}</div>
                </div>
              ))}
            </div>

            <div className="petgame-position-heading">
              <span>POSITION</span>
              <span>PLAYER</span>
              <span>POINTS</span>
            </div>
            <ol className="petgame-rank-list">
              {entries.map((entry, index) => (
                <li
                  style={entry.is_current_user && showInitialUserRise ? { '--rank-rise-distance': `${(Math.max(0, Math.min(30, Math.ceil(entry.rank / 10) * 10) - entry.rank) + 1) * 48}px` } : undefined}
                  className={`petgame-rank-row${entry.is_current_user ? ' is-you' : ''}${entry.rank > 1 && (entry.rank - 1) % 10 === 0 ? ' has-rank-divider' : ''}${entry.is_current_user && showInitialUserRise ? ' rank-intro' : ''}${rankMovements[entry.user_id] ? ` ${rankMovements[entry.user_id]}` : ''}`}
                  data-divider-label={entry.rank > 1 && (entry.rank - 1) % 10 === 0 ? `TOP ${entry.rank - 1}` : undefined}
                  key={entry.user_id}
                >
                  <span className="petgame-rank-number">{entry.rank}</span>
                  <span className="petgame-rank-player">
                    <span className={`petgame-rank-player-icon${entry.is_current_user ? ' is-custom-avatar' : ''}`}>
                      {entry.is_current_user ? (
                        <AvatarArtwork mood="neutral" parts={currentAvatarParts} cosmetics={currentAvatarCosmetics} cosmeticItems={AVATAR_RENDER_CATALOG} />
                      ) : getGameAsset('leaderboardPlayerAvatar') ? (
                        <img src={getGameAsset('leaderboardPlayerAvatar')} alt="" />
                      ) : (
                        <Icon name="rank" size={16} />
                      )}
                    </span>
                    <strong>{entry.name}</strong>
                  </span>
                  <span className="petgame-rank-points"><AnimatedNumber value={entry.earned_points ?? entry.points} /> <small>earned</small><small className="petgame-rank-balance">{Number(entry.available_lp ?? 0).toLocaleString()} LP available</small></span>
                </li>
              ))}
            </ol>
            <p className="petgame-rank-caption">Ranked by total LP earned · Available LP is shown separately.</p>
          </div>
        </>
      )}
    </section>
  );
}
