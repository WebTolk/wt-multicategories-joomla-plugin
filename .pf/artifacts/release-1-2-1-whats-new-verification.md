# WT Multicategories 1.2.1 What's New Verification

## Updated Constants

- English and Russian `PLG_SYSTEM_WTMULTICATEGORIES_WHATS_NEW` constants now describe release `1.2.1`.
- The copy is written for extension users and avoids implementation details.
- It explains that Contact pages now work when the Contact additional-categories field is not selected and that Contact category compatibility with Joomla 6.1.3 was improved.

## Verification

- Both source INI files pass PHP INI parsing.
- Phing rebuilt `.packages/System - WT Multicategories_1.2.1.zip` successfully.
- Both updated constants are present in the ZIP.
- The rebuilt package installed successfully on `joomla.local`.
- Both installed administrator language files contain the `1.2.1` copy.
- Updated package SHA-256: `F0A3D0DF6232B4286A84E77E87228B555120D2C6D1D69D68B5E30874DB1F0EA2`.

## Result

Passed.
