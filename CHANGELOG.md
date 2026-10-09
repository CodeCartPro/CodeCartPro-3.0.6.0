# Changelog

# CodeCart PRO 3.0.6.0 — Production Build 2.0.8 (2026-10-09)

## Public documents: simplified product-first workflow

- Public document editor now contains only document titles and the file upload. Titles are shown for all currently **active** languages in the OpenCart/ocStore language settings, with matching language flags, like paid digital download names. Original PDF/DOC/DOCX/XLS/XLSX/image/TXT/ZIP rules (25 MiB max) remain unchanged.
- Document type, placement, numeric product/category ID, sorting and publication controls are removed from the public document **form**. Newly saved documents are published automatically; editing an existing document retains its file, descriptions, counters, prior category affiliation and sorting.
- Product form has a new **Documents** admin tab with an autocomplete search of previously uploaded public documents. Multiple documents can be associated with one product; the same document can be associated with multiple products. Removing an association never deletes the physical document.
- The old single-product association format from Build 2.0.7 is migrated safely into the new mapping table on first authorized admin use. Previously linked category documents remain linked, and all public download statistics remain unchanged. The operation is idempotent; it does not rewrite product content or touch paid product downloads.
- Public documents list displays filename, totals, 30-day downloads and download date, without redundant type/placement/status columns. Product and category storefront output is preserved for CodeCart Theme and the default theme. Auto-rendering in third-party commercial themes such as UniShop2 has not been verified.
- Adds `codecart_document_to_product` (InnoDB, DB_PREFIX) to the clean-install SQL, the core schema helper, and installer upgrade script 3054. Existing-store overlay upgrades never require running the installer; migration activates after the first authorized admin visit.
- Paid digital files, order-status entitlements, checkout, paid download counts and storage directories are unchanged. The store is not opened to unauthenticated paid downloads.

## Deployment and limitations

Before installation or upgrade, make a verified backup of site files and database. Install the full Production archive only on a new store. For an existing Build 2.0.7 use the small Existing_Store_Update package to overlay the upload tree (not the installer). Purge Twig/OCMOD caches; open Catalog → Public Documents or an authorized product edit page to activate the new mapping table, then test selecting, saving, editing and removing a document and the storefront tab.

Static tests: PHP 8.4 token parser over all included PHP, PHP lint for changed files, Twig parse for document and product templates, documented SQL/schema inspection, archive and checksum verification. No end-to-end MySQL/MariaDB integration or runtime browser session against a production site has been performed.

# CodeCart PRO 3.0.6.0 — Production Build 2.0.7 (2026-10-09)

## New: public document library

- Separate admin entry **Catalog → Public Documents** next to the built-in paid Downloads section.
- Upload public manuals, certificates, catalogs and technical drawings with names in all active store languages (25 MiB maximum per file; PDF, DOCX/DOC, XLSX/XLS, JPG/PNG/WEBP, TXT and ZIP).
- Public documents are disabled by default, stored in `DIR_STORAGE/public_documents/`, outside the public web root, and always served via an explicit downloadable route with a randomized physical filename and safe attachment headers.
- Link to a product or category via the typeahead lookup (or by numeric ID); CodeCart and default storefront templates show the file automatically when published.
- Separate download statistics for public documents and paid digital files: total, previous 30 calendar days, and last download. Counting starts after this build is installed; old download history is not recoverable from existing aggregate sales records. A count represents a successful streaming attempt, not proof that a remote user kept a full copy.
- Paid digital download authorization is unchanged: only signed-in purchasers whose order is in a configured completed status can retrieve their files. Public document access never reads or bypasses the paid-entitlement tables.
- Fresh-install completed statuses default to **Complete (ID 5)** rather than Complete + Shipped/Delivered. Existing stores retain their configured statuses without changes. This prevents shipping-only status from automatically unlocking paid digital downloads on new stores.
- Adds five independent InnoDB tables using the configured DB_PREFIX, with a fresh-install schema and idempotent upgrade migration (`3053.php`). Overlay upgrades safely initialize the new tables on first authorized visit to Catalog → Public Documents.
- Compatible with the independent OpenCart download records: replacing public documents does not delete previous physical files, and public documents cannot be accessed when their record or feature is disabled.

## Existing release fixes retained

Installer text padding, single chevron category navigation, clean demo banners, consolidated OCMOD diagnostics, status counters and restored OCMOD log tab from 2.0.5/2.0.6 remain unchanged.

## Security and deployment

**Back up the database and website files before installing or upgrading.** Existing-store update packages intentionally omit `install/`, demo data, credentials, config.php, vendor libraries and mutable storefront assets. When uploading public documents, storage must be outside the public web root. The administrator must have access/modify rights to either `catalog/public_document` or the existing `catalog/download` route.

No ionCube dependency is added. Protected commercial themes such as UniShop2 require the appropriate ionCube Loader enabled for their own PHP-FPM runtime. Do not edit CodeCart core to mask third-party missing controller classes.

**Tests performed:** PHP 8.4 token parser (all supplied PHP), modified PHP `php -l`, Twig parser for affected templates, SQL statement parser, table schema mock and SHA-256 checks. Functional installation, SQL execution against live MySQL/MariaDB, payment provider integration and end-to-end browser checks on user sites were not run and must be validated on staging.

## 3.0.6.0 — Production Build 2.0.6 (2026-10-09)

- Installer: add reliable, responsive horizontal padding to the administration login notice at the end of installation.
- CodeCart Theme: use symmetrical single-chevron previous/next buttons on the Popular Categories carousel.
- OpenCart demonstration slideshow: replace the last two banners (Samsung and Hewlett-Packard) with clean product-only photos on a plain white background. No branded background graphics or promotional overlays.
- Retain all OCMOD fixes from Build 2.0.5 and verified runtime dependencies. No UniShop2 source code or ionCube loader is included.
- Update the build constants and SHA-256 file-integrity manifest to match this production package.

Validation: PHP syntax/static checks, Twig parser, manifest validation and ZIP CRC. Live install and storefront regressions must be verified on a staging deployment before production rollout.

## 3.0.6.0 — Production Build 2.0.5 (2026-10-09)

Maintenance release integrating confirmed OpenCart Modification (OCMOD) fixes:

- Restore visible “Modifications” and “OCMOD log” tabs on existing upgraded stores by clearing Bootstrap layout floats.
- Account for system-level XML OCMOD sources during compatibility checks, including full rollbacks and skipped optional operations, without treating them as DB-installed extensions.
- Keep system-level XML out of the editable extensions table; log and aggregate diagnostics remain accessible.
- Keep modification action buttons compact on one line and allow horizontal table scrolling on narrow screens.
- Distinguish informational diagnostics from blocking modification failures; preserve both light and dark admin styling.
- Update the file integrity manifest to the exact verified contents of Build 2.0.5.
- Include the root GPLv3 LICENSE and align Composer license metadata, while preserving third-party license notices.

Site-specific notes: the legacy `mms.multiselectimage.ocmod.xml` targeting `.tpl` belongs to the installed store and is not part of the CodeCart core release; disable it only after testing the newer multiple-image workflow. UniShop2 ionCube Loader is a PHP-FPM server setting and is NOT bundled in CodeCart.

Validation: archive structure, all manifest file digests, PHP 8.4 syntax, Twig parsing, archive checksums. Live installation/checkout/payment regression tests have not been executed for this release.

## 3.0.6.0 — Production Build 2.0.4

This entry identifies the supplied production archive `CodeCart-3.0.6-production-Build-2.0.4.zip`.

- OpenCart/ocStore-based CodeCart core and bundled CodeCart Theme.
- Installation, upgrade, compatibility, nginx and demo documentation.
- Bundled PHP Composer runtime dependencies for the production archive.

A change-by-change comparison against a previous published CodeCart build has not been supplied and has not been verified. Avoid claiming individual fixes, migrations or performance improvements without a tested comparison.

The recommended extension-compatibility target stated in the supplied documentation is PHP 8.1–8.3; the core describes support for PHP 8.4/8.5. Individual third-party components must be tested separately.
