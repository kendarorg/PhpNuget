# plan3 — Port the remaining `src/assets/views` functionality into the uifw app

## Context

`plan.md` delivered the foundation (uifw vendored into `src/app/`, merged user model with
`users.apiKey`, the `src/app/lib/nugetbridge.php` bridge, and the **Packages list** slice:
`api/packages.php` + `ui/packages.html` + `ui/js/packages.js`).

`plan2.md` was only **partially applied**. What actually landed:
- **Download counting works**: `src/lib/nuget/NugetDownloads::incrementDownloads` is implemented on
  MySQL (prepared statements, `packages.DownloadCount` / `packages.VersionDownloadCount`) and is
  already hooked into the download path at `src/lib/rest/commons/ApiRoot.php:119`.
- Supporting libs already exist namespaced: `src/lib/nuget/NugetFileParser.php`,
  `src/lib/http/UploadManager.php`.
- Schema is ready: `setup.sql` has `users.apiKey` (unique) and a `packages` table with
  `DownloadCount`, `VersionDownloadCount`, `Listed`, etc.

**Still NOT ported** (the subject of this plan) — the functionality that lives in the legacy
Angular views under `src/assets/views/**`, none of which has a uifw equivalent yet:

| Legacy view | Functionality | uifw status |
|---|---|---|
| `profile/profile.packages.detail.php` (+ `module.js`) | package **edit** form (Title/Authors/Summary/Description/Copyright/IconUrl/ProjectUrl/LicenseUrl/ReleaseNotes/Tags + Listed/RequireLicenseAcceptance), **save**, **delete**, version switcher | **missing** — `packages.js` already links to `package_detail.html` which does not exist |
| `profile/profile.upload.php` | web **upload** form, `SetApiKey`/`Push` CLI snippets, admin "load from another repo" (pull by Id/Version from an external feed), "refresh packages db from directory" | **missing**; upload **backend** also unported (`src/upload/index.php` still `//TODO Porting`, `NugetManager`/`UploadUtils`) |
| `profile/profile.packages.list.php` | the logged-in user's **own** packages list | **missing** |
| `profile/profile.user.php` | **profile** edit (Name/Company/Email/password change) + **Token** (apiKey) show/regenerate | **missing** |
| `users/add.php`, `users/detail.php` (+ `module.js`) | admin user CRUD incl. **Token** show/regenerate + Packages | uifw `users_edit` exists but has **no apiKey/Token** field |
| `home/home.html` | "What is NuGet?" intro copy | trivial — fold into `dashboard.js` |

The nuget OData protocol (`src/api/**`, `src/lib/rest`, `src/upload` once ported) keeps its
contract. Everything is built with the real uifw framework (global `Form`/`Grid`/`Fetcher`,
`BaseApis` subclasses returning `{items, labels, permissions}`, `commons/head|header|menu`
includes, `bootstrap({...})`, `initPage(registry)`), following the existing `packages.js`.

## Conventions reused (do not re-derive)

- **Bridge only**: any UI API touching package or apiKey data `require`s `lib/nugetbridge.php` and
  goes through `nugetPackages()` / a new helper there — never `lib\nuget` directly, never raw SQL
  in an API file. New cross-world SQL lives as a function in `nugetbridge.php` using the shared
  `mysqli` from `nugetBridgeBootstrap()`.
- **Permissions**: every `BaseApis` subclass loads its module in the constructor
  (`$this->auth->loadPermissions('packages')`), gates with `canReadThrow()`/`canWriteThrow()`, and
  ships `permissions->value()` in the envelope so the JS hides disallowed actions. Profile pages
  (a user editing their own row) only require `userMustBeLoggedIn()`.
- **Page scaffold**: copy `src/app/ui/packages.html` for head/header/menu +
  `bootstrap({PAGE-TITLE, LOAD-PERMISSIONS, PAGE-MODULE})`; logic in `src/app/ui/js/<page>.js`.
- **Menu**: add entries to the array in `src/app/api/globalRegistry.php` (around line 49–53),
  permission-filtered, **not** by editing `menu.html`.
- **i18n**: add user-facing labels to `src/app/translations/it.ini`; use `translate(...)` in JS —
  never hardcode strings.

---

## 1. Package write path in the data layer (prerequisite)

The detail/edit, delete, and upload pages all need writes that `NugetPackages` does not yet
expose (it has only `query`/`getByKey`/`queryAndCount`).

- Add **`save(NugetPackage $pkg)`** to `src/lib/nuget/NugetPackages.php` — upsert keyed on
  `Id`+`Version` (prepared statement on the `OminousFactory` mysqli; map fields via the existing
  `NugetPackageConverter` / `src/lib/nuget/fields/mysql/`). Used by both edit-save and upload.
- Add **`delete($id, $version)`** — delete one version row; keep the `.nupkg`/`.snupkg` file
  removal from `Settings::$PackagesRoot` (mirror legacy delete in `src.old`).
- Bridge helpers in `nugetbridge.php`:
  - `nugetPackageSave($fields)` → builds a `NugetPackage`, calls `save`.
  - `nugetPackageDelete($id, $version)`.
  - `nugetPackageVersions($id)` → `query("Id eq '<id>'")` ordered by Version desc (version switcher).
  - `nugetPackagesByOwner($userId, $sq)` → owner-filtered grid (My Packages).
  - `nugetUserApiKeyRegenerate($userId)` → `UPDATE users SET apiKey=? WHERE id=?` with
    `strtoupper(bin2hex(random_bytes(20)))` (uppercase to match the upload lookup), returns the key.

## 2. Package detail / edit page  (`profile.packages.detail.php`)

- **API** — extend `src/app/api/packages.php`:
  - `apiCallGet()` already lists; add `apiCallGetDetail()` (by `?id=&version=`) returning the full
    editable field set + the version list (`nugetPackageVersions`) + download counts, in the
    `{items, labels, permissions}` envelope.
  - `apiCallPostSave()` — `canWriteThrow()`, read posted fields, `nugetPackageSave(...)`.
  - `apiCallPostDelete()` — `canWriteThrow()` (gate behind delete permission /
    `__ALLOWPACKAGESDELETE__` equivalent), `nugetPackageDelete(...)`.
- **Page** `src/app/ui/package_detail.html` + `ui/js/package_detail.js` (the URL `packages.js`
  already navigates to). A uifw `Form` with: read-only Id/Version; editable Title, Author, Summary,
  Description, Copyright, IconUrl, ProjectUrl, LicenseUrl, ReleaseNotes, Tags; checkboxes Listed /
  RequireLicenseAcceptance (IsPreRelease read-only). A **versions** panel/grid linking to the same
  page with a different `version`. Save + Delete buttons gated on `perms.can_write`/`can_delete`. A
  **nuget install snippet** (`Install-Package <Id> -Version <ver>` + feed URL from
  `properties.json` siteRoot) — new vs. the legacy view, requested in plan2.
- **Wire**: `packages.js` row `detail-show` already points here — keep.

## 3. Upload backend port + Upload page  (`profile.upload.php`)

### 3a. Finish the upload backend (it does not work today)
`src/upload/index.php` still `//TODO Porting` with `NugetManager` / `UploadUtils` (src.old-only).
- Replace `UploadUtils` file receipt with `lib\http\UploadManager` (`getFile`/`hasFile`) saving to
  the packages root from `properties.json`; keep `.nupkg`/`.snupkg` + `.symbols.` symbol detection.
- Replace `NugetManager::LoadNuspecFromFile` parsing with `lib\nuget\NugetFileParser`
  (`loadNupkg`/`loadXml`, deps/references).
- Persist via `NugetPackages::save` (from item 1), `UserId` = the apiKey-resolved uploader
  (`nugetUserByApiKey`, already ported). Symbol uploads store the snupkg without creating a package
  row (legacy behavior).
- Keep the existing apiKey/`locked` guard and `201 Created` contract so `dotnet nuget push` is
  unchanged; drop the `//TODO Porting`.

### 3b. Upload page
- `src/app/ui/upload.html` + `ui/js/upload.js`, `LOAD-PERMISSIONS:'packages'`, gated on write.
  - A `Form`/`Fetcher` file field posting the nupkg to **`/upload`** with the user's `apiKey`
    header (one code path with `nuget push`); show parse result (Id/Version) or error.
  - Surface the user's **API key** + `NuGet SetApiKey … -Source <feed>` and `NuGet Push …` snippets
    (built from session apiKey + `properties.json` siteRoot), replacing the legacy `<pre>` blocks.
  - **Admin-only** "Load from another repository" panel (Url template + Id + Version) → a new
    `apiCallPostPull` on `packages.php` that fetches the external `.nupkg`, runs it through the same
    parse→`save` path. (Legacy `download`/`packages?method=download`.)
  - **Admin-only** "Refresh packages DB from directory" → `apiCallPostRefresh` that rescans the
    packages root and upserts rows (legacy `refreshpackages`/`countpackagestorefresh`, batched).
- **Menu**: add `upload` entry, write-gated.

## 4. Profile / My Packages  (`profile.home.php` tabs, `profile.user.php`, `profile.packages.list.php`)

- **API** `src/app/api/profile.php` → `class ProfileApi extends BaseApis`. Resolve the current user
  from the uifw **session** (not apiKey).
  - `apiCallGet()` — current user profile (Name/Company/Email/role/Enabled, masked apiKey).
  - `apiCallPostSave()` — update own Name/Company/Email and optional password change
    (current + new + confirm), via `UsersModel` (reuse uifw password hashing/validation).
  - `apiCallGetMine()` / `apiCallPostMine` — `nugetPackagesByOwner(sessionUserId, sq)` grid.
  - `apiCallPostRegenerateApiKey()` — `nugetUserApiKeyRegenerate(sessionUserId)`, returns the new
    key **once** (show-once; this is the `nuget push` credential — confirm before regenerating since
    it invalidates the old key).
- **Page** `src/app/ui/profile.html` + `ui/js/profile.js`: a profile `Form` (Name/Company/Email +
  password fields), a "My API key" panel (show/copy/regenerate + feed URL + `setapikey` snippet),
  and a "My packages" `Grid` (reusing the list columns) whose rows link to `package_detail.html`.
  Menu entry visible to any logged-in user. (Folds the three legacy profile tabs into one page.)

## 5. Users-admin: apiKey/Token field  (`users/add.php`, `users/detail.php`)

uifw `users.html` + `users_edit` already do user CRUD. Add only the nuget bits:
- In `src/app/ui/js/users_edit.js` (+ `app/api/users.php` / `UsersModel`), expose the **`apiKey`**
  read-only with an admin **Regenerate** action (reuse `nugetUserApiKeyRegenerate`, targeting the
  edited user id). Never free-typed.
- Surface the role↔admin mapping already seeded; ensure the `packages` permission module appears in
  the role/functionality editor so admins can grant package read/write.

## 6. Home copy  (`home/home.html`)

- Render the "What is NuGet?" intro inside `src/app/ui/js/dashboard.js` (the dashboard already loads
  it). Trivial; no new page.

---

## Critical files

- **New**: `src/app/ui/{package_detail,upload,profile}.html` + matching `ui/js/*.js`;
  `src/app/api/profile.php`; new helpers in `src/app/lib/nugetbridge.php` (save/delete/versions/
  byOwner/apiKey-regenerate).
- **Modified**: `src/lib/nuget/NugetPackages.php` (`save`, `delete`); `src/upload/index.php` (port to
  `NugetFileParser` + `UploadManager` + `NugetPackages::save`, drop `//TODO Porting`);
  `src/app/api/packages.php` (detail/save/delete/pull/refresh actions); `src/app/ui/js/packages.js`
  (keep detail nav); `src/app/ui/js/users_edit.js` + `app/api/users.php`/`UsersModel.php` (apiKey
  field); `src/app/api/globalRegistry.php` (menu: upload, profile/my-packages); `src/app/ui/js/
  dashboard.js` (home copy); `src/app/translations/it.ini` (labels).
- **Reused as-is**: `setup.sql` (apiKey/packages schema already present), `NugetDownloads` (done),
  `lib/http/UploadManager`, `lib/nuget/NugetFileParser`, `nugetUserByApiKey`.
- **Source templates (legacy, to modernize, not copy)**: `src/assets/views/profile/*.php` +
  `module.js`, `src/assets/views/users/*.php` + `module.js`, `src.old/uploadnupkg.php`.

## Sequencing & verification

Do **1 → 6** (1 unblocks 2/3/4; 5/6 are independent). After each:

1. **Write path**: unit/manual — `NugetPackages::save` upserts a row; second save same Id+Version
   updates not duplicates; `delete` removes the row and the file.
2. **Detail/edit**: open a package from the list → all fields + versions render; edit + Save
   persists; a read-only user sees no Save/Delete (envelope); Delete removes the version.
3. **Upload**: `dotnet nuget push` with an apiKey **and** the web Upload page both create/update a
   `packages` row with the correct `UserId`; `.snupkg` stores without a phantom row; admin
   pull-from-repo and refresh-from-directory populate rows.
4. **Profile**: a non-admin sees only their own packages; profile save updates Name/Email and
   password; regenerate apiKey → old key rejected by `/upload`, new key accepted; setapikey snippet
   shown.
5. **Users-admin**: admin can view/regenerate any user's apiKey; can grant `packages` read/write
   via roles.
6. **Regression**: OData endpoints (`tests/integration/ApiRootTest.php`) still pass; the download
   counter (`ApiRoot.php:119`) still bumps on `.nupkg` fetch and not on `.snupkg`.
</content>
</invoke>
