# Primary Entry Field

- Version: v1.0.0
- Date: Sept 25 2018
- Requirements: Symphony 2.6.x, PHP 5.6 or greater
- [Release notes](https://github.com/pointybeard/primaryentryfield/blob/master/CHANGELOG.md)
- [GitHub repository](https://github.com/pointybeard/primaryentryfield)

Checkbox that can only be "checked" for a single entry at a time. Will either toggle all other entries to unchecked or display an error depending on selected setting.

## Installation and Setup

1. Upload the 'primaryentryfield' folder to your Symphony 'extensions' folder or include it as a submodule in your install of Symphony (`git submodule add https://github.com/pointybeard/primaryentryfield.git extensions/primaryentryfiel`)

2. Run `composer update` from within the 'primaryentryfield' extension folder to install required packages

3. Install it by selecting "Primary Entry Field", choose Install from the `with-selected` menu, then click Apply


## Usage

This field Behaves identically to a normal checkbox, however, it will only ever allow a single entry in that section to have the field checked.

"Auto Toggle" setting allows you to choose the behaviour of this field when creating or editing entries. When Auto Toggle is off, an error will be thrown ("A primary entry already exists..."). If Auto Toggle is on for the field, all other existing entries in the section to unchecked when you save an entry with the Primary Entry field checked.

## Support

If you believe you have found a bug, please report it using the [GitHub issue tracker](https://github.com/pointybeard/primaryentryfield/issues),
or better yet, fork the library and submit a pull request.

## Contributing

We encourage you to contribute to this project. Please check out the [Contributing documentation](https://github.com/pointybeard/primaryentryfield/blob/master/CONTRIBUTING.md) for guidelines about how to get involved.

## License

"Cron" is released under the [MIT License](http://www.opensource.org/licenses/MIT).
