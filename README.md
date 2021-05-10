# Email Address Extractor

Extract the email address from all the `.eml` files inside a given folder

## Why?

Most email clients (GMail, Outlook, Thunderbird, ...) do not allow to export a list of email addresses in a simple way from an email folder.

On the other hand, there is always a way to export the messages as `.eml` files.

This little tool recursively scans the .eml files in a directory and extracts from them all the email addresses.

## Installation

Install with Composer

```cmd
composer require migliori/email-address-extractor
```

or clone or download the package to a location where your PHP server can access it

## Usage

- Open extractor.php in your browser from a running PHP server
- Paste in the form the path to the folder on your hard disk where the `.eml` files are located
- Choose your options
- Click the `Find email addresses` button
- The `Email Address Extractor` will analyze your folder and show the results in a textarea.

**Remarks**:

The `.eml` files must be on the same disk as `Email Address Extractor`. The tool is not designed to download remote `.eml` files.

The `Email Address Extractor` analyzes your folder recursively, which means that it will also analyze the subfolders.

## Available options

The options allow to choose the email addresses:

- of the senders ('**From:**')
- of the recipients ('**To:**')
- from the contents of the emails

You can check several options at the same time.

## Screenshot

![Email Address Extractor](https://bit.ly/3euHai6?raw=true "Extract email addresses from .eml files")

## Authors

- [@Migliori](https://github.com/migliori)

## License

This project is under [GNU GENERAL PUBLIC](LICENSE.md) license
