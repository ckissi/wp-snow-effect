# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Key commands and workflows

All plugin source lives under `trunk/` in classic WordPress.org Subversion layout (`assets/`, `branches/`, `tags/`, `trunk/`). Most development work should happen inside `trunk/`.

### Navigating and basic checks

- Change into the main plugin directory:
  - `cd trunk`
- PHP syntax check for all plugin files (no dedicated linter config exists in this repo):
  - `cd trunk && find . -name "*.php" -print0 | xargs -0 -n1 php -l`
- Quick check that the main plugin file parses correctly:
  - `cd trunk && php -l wp-snow-effect.php`

### Packaging the plugin

There is no build pipeline; assets are already compiled/minified and tracked.

- Create a distributable ZIP of the current `trunk/` (similar to the existing `Archive.zip`):
  - `cd trunk && zip -r ../wp-snow-effect-1.1.15.zip . -x "*.DS_Store" ".git/*" ".svn/*"`

Adjust the ZIP name/version as needed when preparing new releases.

### Tests

There is no automated test suite or configuration (no `phpunit.xml`, `package.json`, or CI config). Any testing is done manually in a WordPress environment by activating the plugin and exercising the UI.

## High-level architecture

### Repository layout

- `trunk/` — main plugin code used by WordPress.
  - `wp-snow-effect.php` — plugin bootstrap and header (plugin metadata, activation/deactivation hooks, and entrypoint).
  - `includes/` — core plugin classes and shared infrastructure.
  - `admin/` — admin-only behavior and settings UI.
  - `public/` — front-end behavior and asset enqueues.
  - `languages/` — translation template (`wp-snow-effect.pot`).
  - `uninstall.php` — uninstall stub (guards against direct access but does not remove data).
- `assets/`, `branches/`, `tags/` — standard WordPress.org plugin SVN layout (not used directly by the plugin runtime, but relevant for release management).

### Plugin bootstrap and lifecycle

- `trunk/wp-snow-effect.php` contains the WordPress plugin header (name, description, version `1.1.15`, author, text-domain `wp-snow-effect`).
- It registers activation and deactivation callbacks:
  - `activate_wp_snow_effect()` → `includes/class-wp-snow-effect-activator.php`
  - `deactivate_wp_snow_effect()` → `includes/class-wp-snow-effect-deactivator.php`
- After registering hooks, it requires `includes/class-wp-snow-effect.php`, instantiates `Wp_Snow_Effect`, and calls `$plugin->run()` to register all actions/filters.

Activation behavior (`Wp_Snow_Effect_Activator::activate()`):
- Adds an admin notice prompting the user to configure the plugin.
- Stores an activation timestamp (`wp_snow_effect_activation_date`) used later for review prompts.

Deactivation behavior (`Wp_Snow_Effect_Deactivator::deactivate()`):
- Currently a no-op placeholder (reserved for future cleanup logic).

### Core plugin orchestration

#### `includes/class-wp-snow-effect.php` (core class)

Central responsibilities:
- Stores plugin slug (`wp-snow-effect`) and internal version (`1.0.0` used for asset versioning).
- Loads dependencies in `load_dependencies()`:
  - `includes/class-wp-snow-effect-loader.php` — hook orchestration.
  - `includes/class-wp-snow-effect-i18n.php` — localization.
  - `admin/class-wp-snow-effect-admin.php` — admin area hooks.
  - `public/class-wp-snow-effect-public.php` — front-end hooks.
  - `includes/wp-settings-framework.php` and initializes `WordPressSettingsFramework` with `admin/settings/settings.php` and option group `snoweffect`.
- Stores the `WordPressSettingsFramework` instance on `$this->wpsf` for reuse by admin code.
- Initializes a `Wp_Snow_Effect_Loader` instance and uses it to register WordPress actions.

It defines three key setup methods:
- `set_locale()` — creates `Wp_Snow_Effect_i18n`, sets the text domain to the plugin name, and hooks `plugins_loaded` to `load_plugin_textdomain()`.
- `define_admin_hooks()` — creates `Wp_Snow_Effect_Admin`, injects `$this->wpsf` and the loader, and registers admin-only hooks.
- `define_public_hooks()` — creates `Wp_Snow_Effect_Public` and registers front-end hooks.

#### `includes/class-wp-snow-effect-loader.php` (hook registry)

- Maintains two internal arrays: `$actions` and `$filters`.
- Provides `add_action()` and `add_filter()` methods which record hook metadata (hook name, component instance, callback, priority, accepted args).
- `run()` loops through all registered hooks and calls `add_action()` / `add_filter()` from WordPress, centralizing registration in one place.

This pattern allows the core class to declare what should happen without directly calling global WordPress APIs everywhere.

### Internationalization

- `includes/class-wp-snow-effect-i18n.php` holds a `$domain` and exposes:
  - `set_domain( $domain )` — used by the core class to set the text-domain.
  - `load_plugin_textdomain()` — called on `plugins_loaded`, loads translations from `languages/` using `load_plugin_textdomain()`.
- `languages/wp-snow-effect.pot` is the base translation template.

### Admin area and settings

#### `admin/class-wp-snow-effect-admin.php`

Key responsibilities:
- Stores `$plugin_name` and `$version` passed from the core class.
- On construction, reads options via `wpsf_get_settings('snoweffect')` (provided by `WordPressSettingsFramework`), storing them on `$this->settings`.
- `enqueue_styles()` — enqueues `admin/css/wp-snow-effect-admin.css` under the plugin handle for all admin pages where it's needed.
- `enqueue_scripts()` — enqueues `admin/js/wp-snow-effect-admin.js` with `jquery` as a dependency.
- `init_settings()` — registers a settings page with the WordPress Settings Framework under `Settings → WP Snow Effect`.
- `admin_notices()` — outputs any strings stored in the `wp_snow_effect_admin_notices` option, then clears them.
- `wp_snow_effect_check_installation_date()` — after one week of usage, pushes a “please review this plugin” notice into the notice queue.
- `wp_snow_effect_set_no_bug()` — handles the `?wpsenobug=1` query param to opt users out of future review nags and removes the corresponding notice from the queue.

#### `admin/settings/settings.php` (settings schema)

- Hooks `wpsf_register_settings_snoweffect` to `snoweffect_settings()` to define all plugin options.
- Declares a single section `settings` with fields describing snow behavior and display rules, including:
  - `flakes_num`, `falling_speed_min`, `falling_speed_max` — control flake count and motion.
  - `flake_min_size`, `flake_max_size`, `vertical_size` — control size and vertical area.
  - `fade_away` — checkbox for fade-out behavior.
  - `show_on` — checkbox group controlling where the effect appears (home, pages, posts, archives, mobile).
  - `on_spec_page` — text field for specifying pages (the UI indicates this is effectively a PRO-only feature).
  - `flake_type` — select of different Unicode snowflake glyphs, used for front-end rendering.
  - `flake_zindex`, `flake_color` — control stacking context and color, useful for theme compatibility.

These definitions are consumed by `WordPressSettingsFramework` to render the settings page and store structured options under the `snoweffect` option group.

#### `includes/wp-settings-framework.php`

- Third-party library (`WordPressSettingsFramework`) embedded in the plugin.
- Handles:
  - Reading settings definitions via the `wpsf_register_settings_{$option_group}` filter.
  - Registering the underlying WordPress options (`{$option_group}_settings`).
  - Generating settings pages (tabs, sections, fields) and managing save/validation logic.

You generally do not need to modify this file unless upgrading the embedded library.

### Public-facing behavior

#### `public/class-wp-snow-effect-public.php`

Construction and settings:
- Stores `$plugin_name` and `$version` and reads options via `wpsf_get_settings('snoweffect')`.
- If options are missing, falls back to a serialized default settings string embedded in the constructor.

Hooks:
- `enqueue_styles()` — enqueues `public/css/wp-snow-effect-public.css` under the plugin handle.
- `enqueue_scripts()`:
  - Enqueues `public/js/jsnow.js` (jQuery-based snow animation library) with handle `jsnow`.
  - Enqueues `public/js/wp-snow-effect-public.js` under the plugin handle.
  - Determines whether to show the snow effect on the current request based on the stored settings and WordPress conditional tags (`wp_is_mobile()`, `is_home()`, `is_page()`, `is_single()`, `is_archive()`).
  - Calls `wp_localize_script()` with handle `$this->plugin_name` to expose a `snoweffect` JS object containing:
    - `show` (bool), `flakes_num`, `falling_speed_min`, `falling_speed_max`, `flake_min_size`, `flake_max_size`, `vertical_size`, `flake_color`, `flake_zindex`, `flake_type`, `fade_away`.

Front-end JS (`public/js/jsnow.js` and `public/js/wp-snow-effect-public.js`) consumes these values to initialize the jSnow effect on the page without requiring images.

### Uninstall behavior

- `trunk/uninstall.php` guards against direct access (`WP_UNINSTALL_PLUGIN` must be defined) but does not currently remove any options or data.
- If you later add uninstall cleanup (removing `snoweffect` options or notices), it should be implemented here, keeping in mind multisite considerations noted in the boilerplate comments.

### When modifying or extending the plugin

- To add new admin settings, extend the `fields` array in `admin/settings/settings.php`, then consume the new option(s) from `Wp_Snow_Effect_Public` or `Wp_Snow_Effect_Admin` as needed.
- To wire new behavior to WordPress hooks, prefer adding them via `Wp_Snow_Effect_Loader` in the core class rather than calling global `add_action`/`add_filter` directly in random files. This keeps the lifecycle centralized and easier to reason about for future changes.
