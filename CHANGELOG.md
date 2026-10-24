# Changelog

This changelog documents only the two most recent published GitHub releases. Each entry describes the changes between its release tag and the immediately preceding published release. Changes made later on `master` are excluded.

## 1.1.6 - 2026-10-15

### Changed

- Updated the GitHub Actions CI PostgreSQL service from version 16 to 17.
- Updated the MariaDB CI service from version 10 to 11.4, including its startup environment variables and health-check configuration.
- Added the plugin icon image at `pix/icon.png` and reorganized the SVG logo asset as `pix/monologo.svg`.
- Updated the plugin release identifier from `1.1.5` to `1.1.6` and its build number from `2026101200` to `2026101500`.

No changes to Exit Ticket activity behavior were identified between the published `1.1.5` and `1.1.6` tags.

[Compare 1.1.5...1.1.6](https://github.com/EduardoKrausME/moodle-mod_exiticket/compare/1.1.5...1.1.6)

## 1.1.5 - 2026-10-12

### Tests

- Added `tests/generator/lib.php` with a `mod_exiticket_generator` class extending Moodle's `testing_module_generator`. Its `create_instance()` method supplies default settings for three question prompts, closing time, and whether editing is allowed, before delegating activity creation to the parent generator.
- Adjusted coding style in `tests/manager_test.php` and added a `@covers \mod_exiticket\manager` annotation to the regression test class for report access control with separate groups.

### Changed

- Updated the PHP 8.3 CI matrix entry from `MOODLE_502_STABLE` (Moodle 5.2) to `MOODLE_503_STABLE` (Moodle 5.3), retaining the Moodle 4.5 entry.
- Updated the plugin release identifier from `1.1.4` to `1.1.5` and its build number from `2026100800` to `2026101200`.

No changes to the learner-facing Exit Ticket activity were identified between the published `1.1.4` and `1.1.5` tags. The changes to the test suite are documented without implying the tests were executed or passed.

[Compare 1.1.4...1.1.5](https://github.com/EduardoKrausME/moodle-mod_exiticket/compare/1.1.4...1.1.5)
