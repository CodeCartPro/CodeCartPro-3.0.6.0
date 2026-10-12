CodeCart PRO 3.0.6.0 — Extension list sorting

The Extensions page supports sorting by name, enabled status (where available), installation status and recorded installation date. Click a table heading to change sort order; clicking the same heading again reverses the order. Nested module and store rows move with their parent extension. The current sort for each extension type remains active when an AJAX action refreshes the list.

The base OpenCart oc_extension table does not have an installation timestamp. Extension upload history is not the same as individual module installation history. For this reason, the initial dates of already-installed extensions cannot be reconstructed reliably and are shown as unknown. Starting with this build, the standard ModelSettingExtension::install() records the timestamp in the existing oc_setting table and ModelSettingExtension::uninstall() removes that entry. Reinstallation records a new timestamp. No database schema migration is required.

The three UI locales (uk-ua, ru-ru, en-gb) use translated headers and status labels. Sorting is client-side after the existing per-type AJAX rendering; it does not modify extension configuration or the installed/enabled states.

Regression check: php tools/extension-sort-check.php

## Upload date versus installation date

The **Upload date** is the timestamp of the latest installer package upload recorded by CodeCart's built-in extension installer. It is retrieved from `extension_install.date_added`, matched to an exact extension controller file in `extension_path`. It does not use file modification times or attempt to guess from the ZIP filename.

The **Installation date** is when the extension was enabled through the Extensions list (recorded separately in `setting` for new installations). Uploading a ZIP does not imply the extension was installed or enabled. Re-uploading a ZIP changes only the upload history; reinstalling a module changes its recorded installation date.

Legacy/built-in modules, FTP uploads and packages without an exact controller path have an unknown upload date. Old module installations from before tracking also have unknown installation dates. Unknown dates always sort after recorded dates.
