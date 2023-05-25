# Happy Horizon Patches

A collection of patches for e-commerce platforms, and a Slim-PHP application to host them.

**To be used with cweagans/composer-patches**

# Setup
Add cweagans/composer-patches to your project
```
composer require cweagans/composer-patches
```

Add the following snippet to the `extra` node in your project's `composer.json`
```
...
    "extra": {
        "composer-exit-on-patch-failure": true,
        "patches-file": "composer.patches.json"
    },
...
```

Create the file `composer.patches.json`, create the contents as described in [Creating composer.patches.json](#creating-composerpatchesjson)

Update the composer.lock hashes:
```
composer update --lock
```

All defined patches will now be applied every time the applicable modules are installed

# Patch file structure:

All patch files are grouped as
* platform (for example `magento`) 
    * version (for example `2.4.5`)
        * module (for example `module-catalog`)

Patched versions such as Magento 2.4.5-p1 are considered different versions, since some patches for 2.4.5 may not apply.

Every version should contain all applicable patches, even if this results in file duplication.
This way, the version folder also serves as comprehensive documentation for all known patches for that version.

# Browsing patches:

Patches will be hosted at https://patches.experius.nl/

# Creating composer.patches.json

On version pages (for example https://patches.experius.nl/patches/magento/2.4.6), it will be possible to automatically generate a `composer.patches.json` file for that version.

Sample output:
```
{
    "patches": {
        "framework": {
            "#0": "https://patches.experius.nl/patches/magento/2.4.6/framework/MDVA_framework_bundle.patch",
            "#1": "https://patches.experius.nl/patches/magento/2.4.6/framework/Magento_framework_db_schema_index.patch"
        },
        "module-catalog": {
            "#0": "https://patches.experius.nl/patches/magento/2.4.6/module-catalog/fix_category_page_pagination.patch"
        },
        "module-checkout": {
            "#0": "https://patches.experius.nl/patches/magento/2.4.6/module-checkout/onestepcheckout_show_payment_methods_for_multi_address_customers.patch"
        },
        "module-sales": {
            "#0": "https://patches.experius.nl/patches/magento/2.4.6/module-sales/fix_partial_creditmemo_subtotal.patch",
            "#1": "https://patches.experius.nl/patches/magento/2.4.6/module-sales/multiselect_attribute_source_model_creation.patch"
        }
    }
}
```

# External patches:

Patches can also be applied with `composer.patches.json` from other public urls and/or (relative) file path.

# Creating Patches:

## Patch from open changes:

Create a copy of the file to patch as `<filename>.original`. Apply changes to the orignal (without the '.original' suffix).

Create a patch file:
```
git diff -U10 --no-index path/to/file.original path/to/file > fixes_problem.patch
```
The -U10 is to create enough lines of context for the patch to be apply-able.

Notes:
- When patching code at the end of a file, make sure to remove `\No newline at end of file` from the context.
- When creating a file never exceed the character limit of 100 for the file name itself.

## Use PhpStorm to create a patch from open changes. This works even for files not being tracked by GIT

- Make the changes to the file you want to create the patch for.
- Right click the file in your directory tree. Select `Local History` -> `Show History`.
- Select the original version of the file and check if only changes you want to include in your patch are shown.
- Press the `Create Patch` button in the top left. This creates a patch file in the root of your project.
- Rename the patch as needed.

## Patch from Git commit:

### Command Line:

- To get commit hash, use `git log`.
- Create patch:
```
git show 36a34960acf10e1be8e62f3c43d9d2efab90b680 -U10 > fixes_problem.patch
```

### GitHub

Create a patch from a commit url by adding `.patch`

Before:
```
https://github.com/magento/magento2/commit/8a4e2d43f272c8f238eeb410c42b3756a021b8d0
```
After:
```
https://github.com/magento/magento2/commit/8a4e2d43f272c8f238eeb410c42b3756a021b8d0.patch
```

### Bitbucket

Create a patch from a commit url by adding `/raw`

Before:
```
https://bitbucket.org/experius/mage2-module-experius-patcher/commits/23ef51dcb529caa4b663c308fa042c923bde1ce2
```
After:
```
https://bitbucket.org/experius/mage2-module-experius-patcher/commits/23ef51dcb529caa4b663c308fa042c923bde1ce2/raw
```

**Note(!):**
Magento commits use app/code in their repository for the filepatch, change this to the appropriate vendor folder in your patch

# Contributer notes:

* To add a new patch, place it in the appropriate folder. If the folder for that version or module does not exist yet, please add it.
* If a new version is released, make a copy of the folder of the previous version, rework all patches that no longer apply or remove them. Check that the .install.flag is present in the new version folder.