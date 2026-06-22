# plan2 — Remaining UI pages, upload, download counting, dead-code removal

## Context

`plan.md` delivered the foundation: uifw vendored into `src/app/`, the user model merged
(`users` table gains nuget columns + `apiKey`), the bridge (`src/app/lib/nugetbridge.php`:
`nugetPackages()`, `nugetUserByApiKey()`), and the **Packages list** vertical slice
(`src/app/api/packages.php` → grid, `src/app/ui/packages.html` + `js/packages.js`). The nuget
OData protocol under `src/api/**`, `src/lib/rest`, `src/upload` is untouched and stays that way.

plan2 finishes the human UI and closes the two functional holes, in this order of dependency:

1. **Download counting** — `NugetDownloads::incrementDownloads` is a stub; wire it on MySQL and
   hook it into the download path. (No UI; unblocks accurate counts everywhere.)
2. **Upload pipeline + Upload UI** — `src/upload/index.php` still `new NugetManager()` /
   `new UploadUtils()`, classes that exist **only in `src.old`** (see the `//TODO Porting` at the
   top). Port nuspec parse→persist into `lib\nuget`, then build the uifw upload page.
3. **Package detail page** — versions, metadata, dependencies, download counts, install snippet.
4. **Profile / my-packages** — the logged-in user's owned packages + API-key (token) management.
5. **Users-admin nuget specifics** — `users.html`/`users_edit` already do CRUD; add the nuget
   `apiKey`/token field + role↔admin affordances.
6. **Remove dead flat-file DB code** — the file backend and the separate nuget `users` table are
   no longer a deployment path; delete them and their tests.

All human UI is built with the actual uifw framework (global `Form`/`Grid`/`Fetcher` widgets,
`BaseApis` subclasses returning `{items, labels, permissions}`, `commons/head|header|menu`
includes, `bootstrap({...})`, `initPage(registry)`), following the existing `packages.js` and the
`uifw_sample` templates (`api/accounts.php`, `ui/js/account_show.js`). Nuget-specific concerns
(API tokens, nupkg upload, OData install URLs) get explicit handling and are called out per item.

## Conventions reused (do not re-derive)

- **Bridge only**: any new UI API that touches package data `require`s `lib/nugetbridge.php` and
  goes through `nugetPackages()` / a new helper there — never `lib\nuget` directly, never raw SQL
  in an API file. New cross-world SQL (download UPDATE, owner filter, apiKey ops) lives as a
  function in `nugetbridge.php` using the shared `mysqli` from `nugetBridgeBootstrap()`.
- **Permissions**: every `BaseApis` subclass loads its module in the constructor
  (`$this->auth->loadPermissions('packages')`), gates with `canReadThrow()` / `canWriteThrow()`,
  and ships `permissions->value()` in the envelope so the JS hides disallowed actions.
- **Page scaffold**: copy an existing `src/app/ui/*.html` (e.g. `packages.html`) for head/header/menu
  + `bootstrap({PAGE-TITLE, LOAD-PERMISSIONS, PAGE-MODULE})`; logic in `src/app/ui/js/<page>.js`.
- **i18n**: add new labels to `src/app/translations/it.ini` (+ any en file present); never hardcode
  user-facing strings — use `translate(...)`.

---

## 1. Download counting (MySQL)

**Why first:** the detail / my-packages / list pages all display counts; get the source of truth
right before building views over it.

- **Implement** `src/lib/nuget/NugetDownloads.php::incrementDownloads($id, $version)` on MySQL,
  mirroring `src.old/inc/downloadcount.php::incrementDownload`:
  - `UPDATE nugetdb_pkg SET versiondownloadcount = versiondownloadcount + 1 WHERE Id=? AND Version=?`
  - `UPDATE nugetdb_pkg SET downloadcount = downloadcount + 1 WHERE Id=?`
  - Use a **prepared statement** on the mysqli already held by `OminousFactory` /
    `nugetBridgeBootstrap()` (legacy concatenated SQL is an injection bug — do not copy it).
    Inject the storage/mysqli through the constructor or `OminousFactory::getObject('mysqli')`;
    confirm the real downloads table/column names against `src/lib/nuget/fields/mysql/`
    (`mysqlfunctions.sql` / `NugetPackageConverter`) — `nugetdb_pkg` + `downloadcount` /
    `versiondownloadcount` are the legacy names; use whatever the ported schema actually created.
- **Hook it into the download path.** Find where a `.nupkg` is served and call
  `incrementDownloads` there (not on metadata reads):
  - `src/api/index.php:113` (`readfile($path)` after building `Id.Version.nupkg`), and/or
  - `src/lib/rest/ui/Packages.php::dodownload($request)`.
  Skip the increment for **symbol** (`.snupkg`) requests — match the existing `$isSymbol` checks.
- **Verification:** `nuget install`/curl a package twice → `downloadcount` +2, the row's
  `versiondownloadcount` +2; a `.snupkg` fetch does not bump counts; the list/detail pages reflect
  the new numbers.

## 2. Upload pipeline + Upload UI

### 2a. Finish porting the upload backend (prerequisite — it does not work today)

`src/upload/index.php` references `NugetManager`, `UploadUtils`, `Settings`, `HttpUtils` —
only `HttpUtils` exists namespaced; the rest live in `src.old`. The user lookup-by-apiKey is
already ported (good). Remaining:

- **Parsing** is already available: `src/lib/nuget/NugetFileParser.php` (`loadNupkg`, `loadXml`,
  dependencies/references). Use it instead of `NugetManager::LoadNuspecFromFile`.
- **File receipt**: replace `UploadUtils` with the namespaced `lib\http\UploadManager`
  (`getFile`/`hasFile`) + a save to `Settings::$PackagesRoot` equivalent (the packages root from
  `properties.json`). Keep the `.nupkg`/`.snupkg` + `.symbols.` symbol detection from the legacy
  `uploadnupkg.php`.
- **Persistence**: `lib\nuget\NugetPackages` currently has only `query`/`getByKey` — **no insert**.
  Add a `save(NugetPackage $pkg)` (upsert by `Id`+`Version`) on `NugetPackages` (or a dedicated
  writer) that the bridge/upload calls, replacing legacy `NugetManager::SaveNuspec`. Set `UserId`
  to the resolved uploader. Handle the symbol case (store the snupkg, do not create a new package
  row — match legacy behavior).
- Drop the `//TODO Porting` once parse→save runs end-to-end; keep the existing apiKey/`locked`
  guard and `201 Created` contract so `dotnet nuget push` keeps working unchanged.

### 2b. Upload UI page (uifw)

- **Page** `src/app/ui/upload.html` + `js/upload.js` (`LOAD-PERMISSIONS:'packages'`, gated on
  write permission). A uifw `Form` with a file field (drag-drop nupkg) + submit, posting the file
  to the **nuget upload endpoint** (`/upload`) with the user's `apiKey` header — reuse the same
  endpoint `nuget push` uses, so there is one code path. Show parse result (Id/Version) or the
  error message on the callback (the legacy page used an iframe + `packagesUploadControllerCallback`;
  the uifw version uses a `Fetcher` POST with progress + `showError`).
- **Nuget-specific:** the page must surface the user's API key (read from session/profile) and the
  push command snippet so a user can also push from CLI. Symbol (`.snupkg`) uploads allowed.
- **Menu**: add an Upload entry, permission-filtered (write on `packages`).

## 3. Package detail page

Pattern: `uifw_sample/ui/js/account_show.js` (read-only detail + child grid) + the existing
`packages.js`.

- **API** — extend `src/app/api/packages.php` with a detail action (`apiCallGet` by `?id=` or
  `apiCallPostDetail`), returning, via a new `nugetbridge` helper / `nugetPackages()->query`:
  - the package metadata for the latest version (Title, Description, Authors, Tags, ProjectUrl,
    LicenseUrl, IconUrl, published date, total + per-version download counts);
  - **all versions** of the `Id` (`query("Id eq '<id>'" ... orderBy Version desc)`), each with its
    `versiondownloadcount`;
  - dependencies (from `NugetFileParser`/stored fields) for display.
  Wrap in the `{items, labels, permissions}` envelope.
- **Page** `src/app/ui/packages_show.html` + `js/packages_show.js`: read-only `Form` for metadata,
  a **versions `Grid`** (version, published, downloads, with a download link to the OData
  `package/<id>/<version>` URL), a dependencies list, and a **nuget-specific install snippet**
  (`Install-Package <Id> -Version <ver>` / `dotnet add package`) built from `properties.json`
  `siteRoot` so users can copy the feed/command. A Back button to `packages.html`.
- **Wire navigation**: in `packages.js`, the grid row `detail-show` handler →
  `packages_show.html?id=<Id>` (the list grid currently has no detail nav — add it).

## 4. Profile / my-packages

- **API** `src/app/api/profile.php` → `class ProfileApi extends BaseApis` (or extend
  `packages.php` with a `mine` action). Resolve the current user via the uifw session
  (`auth` session user id, as `accounts.php::sessionUserId()` does) — **not** by apiKey.
  - `apiCallPostMine`: `nugetPackages()->query` filtered by `UserId eq <sessionUserId>` (add a
    `nugetPackagesByOwner($userId, $sq)` helper in `nugetbridge.php`), grid envelope.
  - **API-key (token) management — nuget-specific:** `apiCallGetApiKey` returns the current
    `apiKey` (masked unless just generated); `apiCallPostRegenerateApiKey` generates a new random
    token (e.g. `bin2hex(random_bytes(20))`, uppercased to match the upload `strtoupper` lookup),
    `UPDATE users SET apiKey=? WHERE id=<session user>` via a `nugetbridge` helper, and returns it
    once. This is the user's `nuget push` credential — treat it like a secret (show-once,
    copy-button, confirm before regenerating since it invalidates the old key).
- **Page** `src/app/ui/profile.html` + `js/profile.js`: a "My API key" panel (show/copy/regenerate
  + the configured feed URL + `nuget setapikey` snippet) and a "My packages" `Grid` (reusing the
  list columns) whose rows link to `packages_show.html`. Menu entry visible to any logged-in user.

## 5. Users-admin nuget specifics

`src/app/ui/users.html` + `users_edit` already do uifw user CRUD. Add the nuget bits only:

- In `users_edit` (`src/app/ui/js/users_edit.js` + `UsersModel`/`users.php` API), expose the
  **`apiKey`** column: show it read-only with an admin **regenerate** action (reuse the profile
  regenerate helper, targeting the edited user id). Never let it be free-typed.
- Confirm the **role↔admin** mapping is presented (SuperAdmin/Admin = nuget admin, per plan.md
  item 3); ensure the `packages` permission module (seeded in `setup.sql`) appears in the
  role/functionality editor so admins can grant package read/write.
- No new page — extend the existing ones; keep changes minimal since users CRUD "mostly works".

## 6. Remove dead flat-file DB code

The deployment is single-MySQL (plan.md). Remove the now-unused file backend and the separate
nuget `users` table so they stop being a maintenance/confusion surface:

- **Code**: delete the file DB storage + flat-file download/user paths:
  - `src/lib/db/file/*` (file `DbStorage` impl), any `FileDbStorage` wiring in
    `src/lib/OminousFactory.php`, and the `dbType=file` branch in settings/properties handling.
  - `src/lib/nuget/NugetUsers.php` and its `OminousFactory` registration + the references in
    `src/lib/rest/commons/ApiRoot.php` and `src/lib/rest/ui/Packages.php` (identity now comes from
    the merged `users` table via the bridge — re-point those reads to `nugetUserByApiKey`/session).
  - Legacy `src/data/db` flat-file directories if unreferenced after the above.
- **Tests**: delete `tests/lib/db/file/*` (FileDbStorage CRUD/GroupBy/Order/Select + TestUtils).
  Keep all mysql + queryparser + nuget tests. Run the suite after deletion to confirm nothing
  green depended on the file backend.
- **Guard**: grep for `DBFILE`/`dbtype.*file`/`FileDbStorage`/`NugetUsers` before deleting; if the
  OData protocol path still reads `NugetUsers`, port that read to the bridge **in the same change**
  so the protocol keeps working (it is in-scope here precisely because removal breaks it otherwise).

---

## Critical files

- **New**: `src/app/ui/{upload,packages_show,profile}.html` + matching `ui/js/*.js`;
  `src/app/api/profile.php`; new helpers in `src/app/lib/nugetbridge.php`
  (`nugetPackagesByOwner`, `nugetPackageVersions`, apiKey regenerate, download-count read).
- **Modified**: `src/lib/nuget/NugetDownloads.php` (implement), `src/lib/nuget/NugetPackages.php`
  (add `save`), `src/upload/index.php` (port to `NugetFileParser` + `UploadManager` +
  `NugetPackages::save`), `src/app/api/packages.php` (detail action + grid detail nav),
  `src/api/index.php` / `src/lib/rest/ui/Packages.php` (download increment hook),
  `src/app/ui/js/users_edit.js` + `users.php`/`UsersModel` (apiKey field),
  `src/app/utils/globalRegistry.php` bootstrap/menu (Upload, My Packages, Detail entries),
  `src/app/translations/it.ini` (labels).
- **Deleted**: `src/lib/db/file/*`, `src/lib/nuget/NugetUsers.php`, `tests/lib/db/file/*`,
  related `OminousFactory`/settings wiring.
- **Reused as templates**: `uifw_sample/ui/js/account_show.js` (detail), `users_edit.js`
  (admin field), existing `src/app/ui/js/packages.js` (grid), `src.old/inc/downloadcount.php`
  (count logic, sanitized) and `src.old/uploadnupkg.php` (upload flow, modernized).

## Sequencing & verification

Do items in order 1→6 (each builds on the prior; 6 last so removal can't break an in-progress
port). After each item:

1. **Downloads**: fetch a package twice via OData → counts +2; `.snupkg` fetch does not bump.
2. **Upload**: `dotnet nuget push` with an apiKey **and** the new Upload page both create/update a
   `packages` row with correct `UserId`; symbol upload stores snupkg without a phantom row.
3. **Detail**: open a package from the list → all versions, deps, counts, and a copy-paste install
   snippet render; read-only user sees no edit/delete actions (permission envelope).
4. **Profile**: a non-admin sees only their own packages; regenerate API key → old key rejected by
   `/upload`, new key accepted; feed/setapikey snippet shown.
5. **Users-admin**: admin can view/regenerate any user's apiKey and grant `packages` read/write via
   roles.
6. **Cleanup**: full PHPUnit suite green with `tests/lib/db/file/*` gone; OData endpoints
   (`tests/integration/ApiRootTest.php`) still pass — protocol identity now resolves via the merged
   `users` table, not `NugetUsers`.
</content>
</invoke>
