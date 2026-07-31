# CODEX.md — ExploreXR Free

## Workspace Tooling and Worktree Safety

- `rg` is unavailable. Use `find` plus `grep` for file and text searches.
- Free and Premium worktrees are extensively dirty. Every plan and edit must preserve all existing
  work, especially Loading Options page edits. Never reset, overwrite, or revert unrelated changes.

## Required Context

Read shared authority first:

```text
../../../../Core-History/README.md
../../../../Core-History/PATHS.md
../../../../Core-History/LOCAL-DEV.md
../../../../Core-History/ARCHITECTURE.md
../../../../Core-History/ADDONS.md
../../../../Core-History/SECURITY-LIFECYCLE.md
../../../../Core-History/IMPLEMENTATION-HISTORY.md
../../../../Core-History/RELEASE-CHECKLIST.md
```

Workspace:

```text
/mnt/tank/projects/ExploreXR/ExploreXR-Free/wp/wp-content/plugins
```

Premium peer:

```text
/mnt/tank/projects/ExploreXR/ExploreXR-Premium/wp/wp-content/plugins
```

## Local Dev Runtime

- TrueNAS app: `free-explorexr`.
- WordPress: `ix-free-explorexr-wordpress-1`, port `30041`, mount
  `/mnt/tank/projects/ExploreXR/ExploreXR-Free/wp` → `/var/www/html`.
- MariaDB: `ix-free-explorexr-mariadb-1`.
- Admin: `https://free.ayalothman.de/wp-admin/`.
- `admin.expoxr.de/login` is not local WordPress. `digital.expoxr.de` is shop/licensing only.

## Free Rules

- Core entry: `explorexr/explorexr.php`.
- No license handler or local tier system.
- One active commercial add-on: AR, Animation, or Loading.
- Debug remains exempt.
- Use `ExploreXR_Addon_Manager::get_instance()`.
- Preserve shortcode, CPT, model meta, constants, and shared attribute filter.
- Model uploads must use `explorexr_sanitize_file_upload()`.
- Use `WP_Filesystem`/`wp_delete_file()` for filesystem changes; no direct stream or `rmdir()` calls.
- Offset `file_get_contents()` is allowed only for bounded local-upload validation with a narrow,
  justified PHPCS annotation when `WP_Filesystem` cannot provide random access.
- Prefix every plugin-owned file-scope variable with `explorexr_`; keep WordPress globals unchanged.
- Deactivation preserves data. Uninstall deletion requires explicit opt-in.
- Never globally load frontend assets or duplicate Model Viewer.
- Premium add-on copies are source of truth; keep three Free mirrors byte-identical.
- Preserve unrelated/dirty changes. Never reset worktree.

## Verification

- PHP 7.4 syntax.
- Non-vendor JS syntax.
- Focused WordPress security PHPCS.
- `WordPress.WP.AlternativeFunctions` and `WordPress.NamingConventions.PrefixAllGlobals`.
- AR/Animation/Loading parity against Premium.
- `git diff --check`.
- Live WordPress/browser release gates from shared checklist.
