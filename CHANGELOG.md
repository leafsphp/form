# Changelog

## Unreleased

### Changed

- Validation treats `0`, `"0"`, and `false` as present values. Required fields now pass these values on to their validation rules, and optional fields validate them when supplied. For example, `optional|email` now rejects `false` as an invalid email address. Missing fields, `null`, empty strings, and empty arrays still count as absent.
