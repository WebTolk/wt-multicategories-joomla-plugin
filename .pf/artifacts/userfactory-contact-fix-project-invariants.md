# Project Invariants

- WT Multicategories must not alter Joomla component services unless the corresponding multicategory feature is configured.
- A configured contact field must continue to replace only the Contact category site model.
- A configured content field must continue to replace the Content articles model for the active client.
- Administrator Content replacement requires both a configured content field and `work_in_admin`.
- The custom MVC factory must retain the complete Joomla dependency set, including `UserFactoryInterface`.
- Existing mapping save/delete behavior and extension parameters remain unchanged.
