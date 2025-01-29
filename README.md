# WT Multicategories Joomla plugin
Allows you to add articles or contacts from other categories to categories if they are specified in the custom field.

This plugin adds the ability to specify more than one category for Joomla articles or contacts. To do this, create a custom field in which the ID of the additional category will be specified. In the plugin settings, you need to specify which field will be used as the data source. Articles and contacts from additional categories will be added to the general list and will be displayed among the articles/contacts of the main category. 

In terms of meaning, this resembles the functionality of tags. But the data for the tags is stored in a separate database table, and additional layout redefinitions need to be made for them (if necessary) in the template. Using the Joomla multicategories plugin, you can avoid additional work. 

For the convenience of specifying additional Joomla categories, you can use the **WT Category custom field plugin**:
- [site](https://web-tolk.ru/en/dev/joomla-plugins/wt-category-plagin-polzovatelskogo-polya-joomla)
- [Joomla Extensions Directory](https://extensions.joomla.org/extension/authoring-a-content/custom-fields/wt-category/)
- [GitHub](https://github.com/WebTolk/WT-Category-Joomla-custom-field-plugin-for-categories-selection)

## For developers
This plugin overrides Joomla core models:
- `Joomla\Component\Content\Site\Model\ArticlesModel` - articles list model
- `Joomla\Component\Contact\Site\Model\CategoryModel` - contacts list model

In both cases, the `getListQuery()` method is changed. The `JOIN` is added to `$query`.

