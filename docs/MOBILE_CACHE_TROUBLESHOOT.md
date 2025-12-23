# Mobile Cache Troubleshooting

## Normal Behavior
A standard page refresh fetches the latest CSS/JS automatically—version strings update when files change.

## If Stuck on Old Styles

### iOS Safari / Brave
1. **Pull-to-refresh** (swipe down from top)
2. If still stuck: Settings → Safari/Brave → Clear History and Website Data

### Android Chrome
1. **Pull-to-refresh** or tap ⋮ → Reload
2. If still stuck: Settings → Privacy → Clear browsing data → Cached images and files

## For Developers
Asset versions are now `filemtime()`-based. Deploy = instant invalidation—no manual version bumps needed.
