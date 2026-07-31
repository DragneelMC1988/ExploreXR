# CODEX.md — ExploreXR Free

## Required Context

Read shared authority first:

```text
../../../../Core-History/README.md
../../../../Core-History/PATHS.md
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

## Free Rules

- Core entry: `explorexr/explorexr.php`.
- No license handler or local tier system.
- One active commercial add-on: AR, Animation, or Loading.
- Debug remains exempt.
- Use `ExploreXR_Addon_Manager::get_instance()`.
- Preserve shortcode, CPT, model meta, constants, and shared attribute filter.
- Model uploads must use `explorexr_sanitize_file_upload()`.
- Deactivation preserves data. Uninstall deletion requires explicit opt-in.
- Never globally load frontend assets or duplicate Model Viewer.
- Premium add-on copies are source of truth; keep three Free mirrors byte-identical.
- Preserve unrelated/dirty changes. Never reset worktree.

## Verification

- PHP 7.4 syntax.
- Non-vendor JS syntax.
- Focused WordPress security PHPCS.
- AR/Animation/Loading parity against Premium.
- `git diff --check`.
- Live WordPress/browser release gates from shared checklist.

